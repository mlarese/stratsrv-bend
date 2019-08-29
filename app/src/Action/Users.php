<?php

namespace App\Action;


use App\Action\Emails\EmailHelpers;
use App\Entity\Config\User;
use App\Entity\Privacy\Privacy;
use App\Entity\Privacy\PrivacyAttachment;
use App\Resource\OperatorResource;
use App\Resource\Privacy\EmptyFilter;
use App\Resource\Privacy\GroupByEmail;
//use App\Resource\PrivacyLogger;
use App\Resource\PrivacyLoggerResource;
use App\Resource\PrivacyResource;
use App\Service\AttachmentsService;
use App\Traits\UrlHelpers;
use Closure;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Exception;
use Interop\Container\Exception\ContainerException;
use function print_r;
use function session_commit;
use Slim\Http\Request;
use Slim\Http\Response;

class Users extends AbstractAction
{
    use EmailHelpers;
    use UrlHelpers;

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function saveAttachment($request, $response, $args){

        try {
            $ownerId = $args['ownerId'];
            $privacyId = $args['id'];
            /** @var \Slim\Http\UploadedFile[] $file */
            $files = $request->getUploadedFiles();
            $attSrv = $this->getContainer()->get('attachments_service');
            $attSrv->savePrivacyAttachment($files, $ownerId, $privacyId);
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, "Error saving attachments");
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function loadAttachments($request, $response, $args){

        try {
            $id = $args['id'];
            $ownerId = $this->getOwnerId($request);

            $em = $this->getEmPrivacy($ownerId);

            $att = $em->find(PrivacyAttachment::class, $id);

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, "Error loading attachments");
        }

        return $response->withJson($this->toJson($att));
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function saveAttachList($request, $response, $args){
        /** @var Closure $closure */
        $container = $this->getContainer();
        $closure = function ($post) use($args, $container){
            $attachList = $post['attachList'];
        };
        return $this->postActionPrototype($request, $response, $args, $closure);
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function search($request, $response, $args)
    {
        $ownerId = $this->getOwnerId($request);
        session_commit();
        ini_set('memory_limit', '1024M');
        set_time_limit ( 300 );

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $priRes = new PrivacyResource($em);
            $body  = $request->getParsedBody();

            $criteria = null;
            if(isset($body['filters']))
                $criteria = $body['filters'];

            $step = "- getting list";
            $list = $priRes->privacyListFw($criteria, new GroupByEmail());
            // $list = $priRes->privacyListLight($criteria, new GroupByEmail());



            // print_r($list);
            // die('fine');
            $export = [];
            $step.= "- creating records";
            foreach($list as $email => &$person){
                $newExport = [
                    // 'id' => $person['id'],
                    // '_counter_' => isset($person['_counter_'])?$person['_counter_']:0,
                    '_flags_' => isset($person['_flags_'])?$person['_flags_']:[],
                    'name'=>&$person['name'],
                    'surname'=>&$person['surname'],
                    'email'=>&$person['email'],
                    'termId'=>&$person['termId'],
                    'domain'=>&$person['domain'],
                    'site'=>&$person['site'],
                    'created'=>&$person['created'],
                    'language'=>&$person['language'],
                    'termName'=> &$person['termName']
                ];


                $export[] = $newExport;
            }

            unset($list);

            return $response->withJson($this->toJson($export));


        } catch (ORMException $e) {
            echo $e->getMessage();
            echo " $step";
            return $response->withStatus(500, 'ORMException searching user');
        } catch (\Exception $e) {
            echo $e->getMessage();
            echo " $step";
            return $response->withStatus(500, 'Exception searching user');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function searchToday($request, $response, $args)
    {
        session_commit();
        $ownerId = $this->getOwnerId($request);

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $priRes = new PrivacyResource($em);

            $criteria = [
                'created' => date('Y-m-d')
            ];

            $list = $priRes->privacyListFw($criteria, new GroupByEmail(), new EmptyFilter());

            $export = [];
            foreach($list as $email => $person){
                $newExport = [
                    'id' => $person['id'],
                    'name'=>$person['name'],
                    'surname'=>$person['surname'],
                    'email'=>$person['email'],
                    'created'=>$person['created'],
                    'language'=>$person['language']
                ];
                $export[] = $newExport;
            }

            return $response->withJson($this->toJson($export));


        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException saving privacy');
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws ORMException
     */
    public function privacyUser($request, $response, $args) {
        try {
            session_commit();
            $ownerId = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);

            $email = $args['email'];
            $email = base64_decode(urldecode($email)) ;


            // die('--- '.$email);

            $privacyRes = new PrivacyResource($em);
            $user = $privacyRes->privacyRecord($email);


            // print_r($user);die;

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }

        return $response->withJson( $user);
    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function changePasswordReset($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();
            $enc = $this->getContainer()->get('encryptor');
            $_k = $this->urlB32DecodeString($body['_k'], $enc);

            if(
                !isset($body['password']) ||
                !isset($body['repeatPassword'])
            ) {
                return $response->withStatus(401, 'Wrong request');
            }

            if(   strlen($body['password'])<8 ) {
                return $response->withStatus(401, 'At least 8 characters');
            }


            /** @var User $user */
            $user = $this->getEmConfig()->find(User::class, $body['userId']);

            if(  !isset($user)) {
                return $response->withStatus(401, 'User not found');
            }



            $user->setPassword(      md5($body['password'])    );
            $this->getEmConfig()->merge($user);
            $this->getEmConfig()->flush();

            $emprv = $this->getEmPrivacy( $user->getOwnerId() );

            $opRes = new OperatorResource($emprv);

            $op = $opRes->findOperator($user->getId());

            $this->sendGenericEmail(
                $this->getContainer(),
                ["name"=>$op->getName(),"surname"=>$op->getSurname()],
                'change_password',
                'it',
                $this->getCallCenterEmail( $this->getContainer()),
                $op->getEmail()
            );

            return $response->withJson($this->success());
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException changing password');
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception changing password');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function changePassword($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();

            if(
                !isset($body['userId']) ||
                !isset($body['password']) ||
                !isset($body['oldPassword']) ||
                !isset($body['repeatPassword'])
            ) {
                return $response->withStatus(401, 'Wrong request');
            }

            if(   strlen($body['password'])<8 ) {
                return $response->withStatus(401, 'At least 8 characters');
            }


            /** @var User $user */
            $user = $this->getEmConfig()->find(User::class, $body['userId']);

            if(  !isset($user)) {
                return $response->withStatus(401, 'User not found');
            }


            $oldPwdMd5 = md5($body['oldPassword']);
            if( $oldPwdMd5!==$user->getPassword()  ) {
                return $response->withStatus(401, 'Wrong old password');
            }


            $user->setPassword(      md5($body['password'])    );
            $this->getEmConfig()->merge($user);
            $this->getEmConfig()->flush();

            $emprv = $this->getEmPrivacy( $user->getOwnerId() );

            $opRes = new OperatorResource($emprv);

            $op = $opRes->findOperator($user->getId());

            $this->sendGenericEmail(
                $this->getContainer(),
                ["name"=>$op->getName(),"surname"=>$op->getSurname()],
                'change_password',
                'it',
                $this->getCallCenterEmail($this->getContainer()),
                $op->getEmail()
            );

            return $response->withJson($this->success());
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException changing password');
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception changing password');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function deleteUserSubscriptions($request, $response, $args)
    {

        try {
            $ownerId = $this->getOwnerId($request);
            $email = $args['email'];
            $email = urldecode(   base64_decode($email));

            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $priRes = new PrivacyResource($em);

            $priRes->deletePrivacyByEmail($email);
            return $response->withJson($this->success());

        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException saving privacy');
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function updateTerms($request, $response, $args)
    {


        try {
            $ownerId = $this->getOwnerId($request);
            $body = $request->getParsedBody();
            $userData = $this->getUserData($request);

            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $priRes = new PrivacyResource($em);
            $prilogRes = new PrivacyLoggerResource($em);

            foreach ($body as $privacy) {
                /** @var Privacy $p */
                $p = $em->find(Privacy::class, $privacy['id']);


                $props=$p->getProperties();

                if(!isset($props['history'])) {
                    $props['history'] = [];
                }


                $newF = $privacy['privacyFlags'];
                $oldF = $p->getPrivacyFlags();

                $oldFByCode = [];

                foreach ($oldF as $flag_prv) {
                    $oldFByCode [$flag_prv['code']]= $flag_prv['selected'];
                }
                $variations = [];
                foreach ($newF as $flag_prv) {
                    $oldFlag = $oldFByCode[$flag_prv['code']];
                    // echo $flag_prv['code'] . ' ' . $flag_prv['selected'] . '-' .$oldFlag;
                    if($flag_prv['selected'] !== $oldFlag) {
                        $variations[] = [
                            'action'=> $flag_prv['selected']?'granted':'revoked',
                            'flag'=> $flag_prv['code']
                        ];
                    }
                }

                $user = $userData->user;
                $role = $userData->role;
                // print_r($userData);die;
                $props['history'] []= [
                   'update' =>  new DateTime(),
                   'new_flags' =>  $privacy['privacyFlags'],
                   'old_flags' =>  $p->getPrivacyFlags(),
                   'variations' =>  $variations,
                   'user'=> $user
                ] ;

                // print_r($props); die;

                $p->setProperties($props);

                $p->setPrivacy($privacy['privacy'])
                    ->setPrivacyFlags($privacy['privacyFlags'])
                ;

                $em->merge($p);
                $prilogRes->operatorPrivacyLog($p,$userData,false);


            }

            $em->flush();



        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException saving privacy');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }
        return  $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function updateMainData($request, $response, $args)
    {
        try {
            $ownerId = $this->getOwnerId($request);
            $body= $request->getParsedBody();
            $id = $args['id'];


            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $plhr = new PrivacyLoggerResource($em);

            /** @var Privacy $pr */
            $pr = $em->find(Privacy::class, $id);

            $note = '';
            if(isset($body['note'])) {
                $note = $body['note'] ;
            }

            $pr
                ->setName( $body['name'] )
                ->setSurname( $body['surname'] )
                ->setEmail( $body['email'] )
                ->setTelephone( $body['telephone'] )
                ->setNote($note)
            ;

            $em->merge($pr);

            $userObj = $this->getUserData($request);
            $plhr->operatorPrivacyLog($pr,$userObj,false);
            $em->flush();

            return  $response->withJson($this->success());

        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException saving privacy');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

    }
}
