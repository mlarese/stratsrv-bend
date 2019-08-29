<?php

namespace App\Action;

use Ambta\DoctrineEncryptBundle\Encryptors\EncryptorInterface;
use App\Action\Emails\EmailHelpers;
use App\Entity\Config\ActionHistory;
use App\Entity\Config\CustomerCare;
use App\Entity\Config\Owner;
use App\Entity\Config\User;
use App\Entity\Config\UserLogin;
use App\Entity\Privacy\Operator;
use App\Entity\Privacy\Privacy;
use App\Resource\OperatorResource;
use App\Traits\UrlHelpers;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Firebase\JWT\JWT;
use function md5;
use function session_commit;
use Slim\Http\Request;
use Slim\Http\Response;
use function strtolower;
use Tuupola\Base62;

class Auth extends AbstractAction
{
    use EmailHelpers;
    use UrlHelpers;
    /**
     * @param       $request Request
     * @param       $user
     * @param array $scope
     *
     * @return mixed
     */
    private function defineJwtToken($request, $user, $scope = ["read", "write", "delete"])
    {
        $requested_scopes = $request->getParsedBody() ?: [];

        $settings = $this->getContainer()->get('settings');
        $auth = $settings->get('auth');

        $now = new DateTime();
        $future = new DateTime("+240 minutes");
        $server = $request->getServerParams();
        $jti = (new Base62())->encode(random_bytes(16));
        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $future->getTimeStamp(),
            "jti" => $jti,
            "scope" => $scope,
            "user" => $user,
            "sub" => $user['user']
        ];
        $secret = $auth['secret'];
        $token = JWT::encode($payload, $secret, "HS256");

        $data["token"] = $token;
        $data["expires"] = $future->getTimeStamp();

        return $data;
    }

    /**
     * @param $user
     * @param $pwd
     * @return User
     * @throws UserNotAuthorizedException
     */
    private function userHasAuth($user, $pwd)
    {
        /**
         * @var User $userEntity
         */

        $userEntity = $this->getEmConfig()
            ->getRepository(User::class)
            ->findOneBy(['user' => $user]);

        $valid = false;

        $msg = '';
        if (isset($userEntity)) {
            $msg = 'User found';
            if ($userEntity->getActive() && !$userEntity->getDeleted()) {
                $cfp = md5($pwd);
                $cfp = strtolower($cfp);

                $userPwd = strtolower($userEntity->getPassword());

                if ($userPwd === $cfp) {
                    $valid = true;
                }
            } else {
                $msg = 'User found but not active or deleted';
            }

        }

        if ($valid) {
            return $userEntity;

        }
        throw new UserNotAuthorizedException('User Not Authorized Exception ' . $msg);

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function login($request, $response, $args)
    {
        session_commit();
        $found = false;
        $user = $request->getParam('username');
        $password = $request->getParam('password');


        // echo 'here' ; die;
        /** @var User $ue */
        $ue = null;
        /** @var Operator $op */
        $op = null;
        try {
            $ue = $this->userHasAuth($user, $password);
            $found = true;

            $gdprRole = 'customercare';
            $gdprEmail = '';

            if ($ue->getOwnerId() > 0) {

                $opRes = new OperatorResource($this->getEmPrivacy($ue->getOwnerId()));

                $op = $opRes->findOperator($ue->getId());
                $gdprRole = $op->getRole();
                $gdprEmail = $op->getEmail();
            }

        } catch (UserNotAuthorizedException $e) {
            echo $e->getMessage();
            return $response->withStatus(401, 'User not authorized ');
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(401, 'Authentication error ');
        }

        $settings = $this->getContainer()->get('settings');
        $host = $settings["doctrine_config"]['connection']['host'];
        if ($found) {
            $userSpec = [
                "acl" => $this->getAcl($gdprRole),
                "options" => $this->getOptions($ue->getOwnerId()),
                "email" => $gdprEmail,
                "gdprRole" => $gdprRole,
                "userId" => $ue->getId(),
                "user" => $user,
                "userName" => $ue->getName(),
                "role" => $ue->getType(),
                "ownerId" => $ue->getOwnerId(),
                "source" => ($host === '127.0.0.1') ? 'local' : 'remote'
            ];
            $data = $this->defineJwtToken($request, $userSpec);


            $log = new UserLogin();
            $log->setIpAddress($this->getIp())
                ->setLoginDate(new DateTime())
                ->setUserId($ue->getId());

            $this->getEmConfig()->persist($log);
            $this->getEmConfig()->flush();

            return $response->withStatus(201)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        }
    }

    public static function getOptionsSt($ownerId) {
        $ret = [
            "isPortal" => false,
            "hasBi" => false,
            "hasQuery" => false,
            "hasPredictive" => false,
            "hasAdvMarketing" => false
        ];
        switch($ownerId) {
            case 9: // nettuno
            case 6:
            case 34:
                $ret["hasBi"] = true ;
                $ret["hasQuery"] = true ;
                $ret["hasPredictive"] = true ;
                break;
            case 15: // aba
                $ret["isPortal"] = true ;
                $ret["hasBi"] = true ;
                $ret["hasQuery"] = true ;
                $ret["hasPredictive"] = true ;
                break;
            case 53: // vidi
                $ret["hasBi"] = true ;
                $ret["hasQuery"] = true ;
                $ret["hasPredictive"] = false ;
                break;

        }
        return $ret;
    }

    private function getOptions($ownerId) {
        return self::getOptionsSt($ownerId);
    }
    public static function getAclSt($gdprRole)
    {
        /***************************************
        Roles
        ==========================
        owner
        incharge
        incharge_rw
        responsible
        dp
        ***************************************/

        return [
            "can-see-no-agreement" => ($gdprRole !== 'incharge' && $gdprRole !== 'incharge_rw' ),
            "can-save" => ($gdprRole !== 'incharge'),
            "can-add" => ($gdprRole !== 'incharge'),
            "can-add-user" => ($gdprRole !== 'incharge'),
            "can-share" => ($gdprRole !== 'incharge'),
            "can-export-user-data" => ($gdprRole === 'responsible'),
            "can-see-all-user-data" => ($gdprRole !== 'incharge' && $gdprRole !== 'incharge_rw'),
            "can-save-user-data" => ($gdprRole !== 'incharge'),
            "can-save-user-flags" => ($gdprRole !== 'incharge'),
            "can-see-user-attachments" => ($gdprRole !== 'incharge' && $gdprRole !== 'incharge_rw'),
            "can-manage-operators" => ($gdprRole !== 'incharge')
        ];
    }

    private function getAcl($gdprRole) {
        return self::getAclSt($gdprRole);
    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function logout($request, $response, $args)
    {
        session_commit();
        return $response->withJson(array("logout" => "ok"));
    }


    /**
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function resetPasswordEmail(Request $request, Response $response, $args)
    {
        try {
            $user = $args['user'];
            $cfgem = $this->getEmConfig();
            /** @var User $eUser */
            $eUser = $cfgem->getRepository(User::class)
                ->findOneBy(['user'=> $user, 'active'=> true, 'deleted'=>0] );


            if (!isset($eUser)){
                return $response->withStatus(401, 'User not found');

            }
            $email = null;


            if($eUser->getOwnerId()===0) {
                /** @var CustomerCare $cusc */
                $cusc = $cfgem->find(CustomerCare::class, $eUser->getId());
                if (!isset($cusc)){
                    return $response->withStatus(401, 'User not found');

                }
                $email = $cusc->getEmail();
            }else{
                $em = $this->getEmPrivacy($eUser->getOwnerId());

                /** @var Operator $oper */
                $oper = $em->find(Operator::class, $eUser->getId());

                if (!isset($oper)){
                    return $response->withStatus(401, 'User not found');

                }
                $email = $oper->getEmail();

            }

            $userId = $eUser->getId();
            $enc = $this->getContainer()->get('encryptor');
            $_k= $this->urlB32EncodeString("user=$user&userId=$userId", $enc);
            $link = "https://privacy.dataone.online/service/preset?_k=$_k&user=$user";
            $userType= $eUser->getType();

            $ah=new ActionHistory();
            $ah->setDate(new \DateTime())
                ->setType("${userType}_email_password_rest")
                ->setUserName($eUser->getUser())
                ->setDescription($userType .' sent reset password email user='.$eUser->getUser())
            ;

            $this->getEmConfig()->persist($ah);
            $this->getEmConfig()->flush();


            $data = ['email'=>$email, 'link'=>$link, 'user'=>$user];
            $this->sendGenericEmail(
                $this->getContainer(),
                $data,
                'password_reset',
                'it',
                $this->getCallCenterEmail($this->getContainer()),
                $email,
                'data_one_emails',
                'password reset'

            );

            return $response->withJson($this->success());

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(401, 'Password reset failed ');

        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function user($request, $response, $args)
    {
        session_commit();
        $token = $request->getAttribute("token");
        $ud = $this->getUserData($request);

        return $response->withJson(["user" => $token['user']]);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param          $args
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function resetPassword(Request $request, Response $response, $args){
        try {
            $body = $request->getParsedBody();
            $enc = $this->getContainer()->get('encryptor');
            $_k = $body['_k'];
            $props = $this->urlB32DecodeToArray($_k, $enc);

            if($body['user']!==$props['user']) {
                return $response->withStatus(403, 'Wrong user');
            }
            $user = $props['user'];
            $userId = $props['userId'];

            /** @var User $userObj */
            $userObj = $this->getEmConfig()->find(User::class, $userId );

            if(  !isset($userObj)) {
                return $response->withStatus(401, 'User not found');
            }

            $userType= $userObj->getType();

            $ah=new ActionHistory();
            $ah->setDate(new \DateTime())
                ->setType("${userType}_password_rest")
                ->setUserName($userObj->getUser())
                ->setDescription($userType .' reset password user='.$userObj->getUser())
            ;

            $this->getEmConfig()->persist($ah);
            $this->getEmConfig()->flush();



            $userObj->setPassword(      md5($body['password'])    );
            $this->getEmConfig()->merge($userObj);
            $this->getEmConfig()->flush();

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(401, 'Password reset failed ');
        }

        return $response->withJson($this->success());
    }

    private function resetPassword_errata_checkit($request, $response, $args) {
        session_commit();
        $found = false;
        $user = $request->getParam('username');
        $password = $request->getParam('password');


        // echo 'here' ; die;
        /** @var User $ue */
        $ue = null;
        /** @var Operator $op */
        $op = null;
        try {
            $ue = $this->userHasAuth($user, $password);
            $found = true ;

            $opRes = new OperatorResource($this->getEmPrivacy( $ue->getOwnerId() ));

            $op = $opRes->findOperator($ue->getId());

        } catch (UserNotAuthorizedException $e) {
            echo $e->getMessage();
            return $response->withStatus(401, 'User not authorized ' );
        }catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(401, 'Authentication error ' );
        }
        $gdprRole =  $op->getRole();
        $settings = $this->getContainer()->get('settings');
        $host = $settings["doctrine_config"]['connection']['host'];
        if($found) {
            $userSpec = [
                "acl" => $this->getAcl($gdprRole),
                "options" => $this->getOptions($ue->getOwnerId()),
                "email"=> $op->getEmail(),
                "gdprRole" => $gdprRole,
                "userId" => $ue->getId(),
                "user" => $user,
                "userName" => $ue->getName(),
                "role" => $ue->getType(),
                "ownerId" => $ue->getOwnerId(),
                "source" => ($host==='127.0.0.1' )?'local': 'remote'
            ];
            $data = $this->defineJwtToken($request, $userSpec);


            $log = new UserLogin();
            $log->setIpAddress( $this->getIp() )
                ->setLoginDate(new DateTime())
                ->setUserId($ue->getId());

            $this->getEmConfig()->persist($log);
            $this->getEmConfig()->flush();

            return $response->withStatus(201)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
}
