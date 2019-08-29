<?php
namespace App\Action\Emails;
use App\Action\AbstractAction;
use App\Entity\Config\Owner;
use App\Entity\Privacy\Privacy;
use App\Entity\Privacy\UserRequest;
use App\Resource\EmailResource;

use App\Resource\PrivacyResource;
use App\Service\EmailService;
use App\Traits\Environment;
use App\Traits\UrlHelpers;
use function base64_encode;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Slim\Http\Request;
use Slim\Http\Response;
use function urlencode;


class Emails extends AbstractAction {

    use UrlHelpers;
    use Environment;
    use EmailHelpers;

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function generic($request, $response, $args) {

        print_r($_SERVER);
        die('generic');

        $esrv = new EmailService();
        try {
            $from = 'mlarese@email.it';
            $to = 'mauro.larese@gmail.com';

            $privres = new PrivacyResource( $this->getEmPrivacy(2));
            $lastPr = $privres->getLastPrivacyByEmail($to);

            $data = [
              "name"=>"nome " . $lastPr->getName(),
              "surname"=>"cognome " . $lastPr->getSurname()
            ];


            $esrv->sendGenericEmail(
                $this->getContainer(),
                $data,
                'notify_privacy_mod_executed',
                $lastPr->getLanguage(),
                $from,
                $to
            );
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error sending email');
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function notifyModAccepted($request, $response, $args) {

        try {
            $templateData = [

            ];
            $params = $this->urlB64DecodeToArray($request->getParam('_k'));
            $from = '';
            $to = $params['email'];
            $this->sendGenericEmail(
                $this->getContainer(),
                $templateData,
                'templateName',
                'language',
                $from,
                $to);
        } catch (Exception $e) {
            return $response->withStatus(500, 'Error sending email');
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Doctrine\DBAL\DBALException
     */
    public function notifyModExecuted($request, $response, $args) {
        try{

        }catch(Exception $e) {
            echo $e->getMessage();
            return  $response->withStatus(500, $e->getMessage());
        }

        return $response->withJson( $this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function unsubscribeEmail($request, $response, $args) {
        // _k=urlenc(base64(email=&owner=)) & l=language
        try {
            $lang = $request->getParam('l');
            $_k = $request->getParam('_k');

            $settings = $this->getContainer()->get('settings');
            $dataOneSettings = $settings['dataone'];

            $confirmLink = $dataOneSettings['_options_'][$this->detectEnvironment()]['fe_address'];

            $referrer = isset($_SERVER["REMOTE_ADDR"])?$_SERVER["REMOTE_ADDR"]:null ;
            $params = $this->urlB64DecodeToArray($_k);
            $email = $params['email'];
            $ownerId = $params['ownerId'];

            $from = null;
            $to=null;
            $em = $this->getEmPrivacy($ownerId);
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);


                $data=[
                    'enclink'=>"$confirmLink/surfer/flagspage?_k=$_k&l=$lang"
                ];

                $this->sendGenericEmail(
                    $this->getContainer(),
                    $data,
                    'news_unsub_email_notif',
                    $lang,
                    $from,
                    $to
                );


        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error ') ;
        }

        return $response->withJson($this->success()) ;
    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function privacyRequestTest($request, $response, $args) {
        try {
            $lang = 'de';
            $email = 'mauro.larese@gmail.com';
            $emailRes = new EmailResource($this->getEmPrivacy(2), $this->getEmConfig());
            $body = $emailRes->privacyRequest(
                $lang,
                $email,
                2,
                $this->getContainer(),
                'www.aloe-shop.it',
                'fe742340-6fe8-11e8-9267-c95d4c7b2492'
                );

            echo $body;

        } catch (GuzzleException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Guzzle Error ') ;
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error ') ;
        }

        // return $response->withJson($this->success()) ;
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function sendPrivaciesAfterRequest($request, $response, $args) {
        try {
            $email = $request->getParam('email');
            $reqId = $request->getParam('id');

            $ownerId = $this->getOwnerId($request);
            $em = $this->getEmPrivacy($ownerId);

            /** @var UserRequest $req */
            $req = $em->find(UserRequest::class, $reqId) ;
            $pres = new PrivacyResource($em);
            /** @var Privacy $lastPrv */
            $lastPrv = $pres->getLastPrivacyByEmail($email);
            $lang = $lastPrv->getLanguage();

            $requestDomain =$req->getDomain();
            if(isset($requestDomain))
                $reqDomain = $requestDomain;
            else
                $reqDomain = $lastPrv->getDomain();


            $emailRes = new EmailResource($em, $this->getEmConfig());
            $body = $emailRes->privacyRequest(
                $lang,
                $email,
                $ownerId,
                $this->getContainer(),
                $reqDomain
            );

        } catch (GuzzleException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Guzzle Error ') ;
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error ') ;
        }

        return $response->withJson($this->success()) ;
    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function doubleOptinConfirm($request, $response, $args) {
        try {
            $lang = 'it';

            $ownerId = 34;
            $prv = '9fde1360-88f0-11e8-82a7-a14515583fe0';

            $ownerId =  urlencode( base64_encode( $this->getEncryptor()->encrypt($ownerId) ) );
            $prv = urlencode( base64_encode( $this->getEncryptor()->encrypt($prv) ) );


            $d = [
                "enclink" => "http://zzz.com?_k=$ownerId&_j=$prv"
            ];

            echo ("s=1&_j=$prv&_k=$ownerId&=2");

            die;
            $tpl = new TemplateBuilder( 'double_optin', $d, $lang );
            $body = $tpl->render();

            $client = $this->getEmailClient();
            $data = $this->buildGuzzleData('mauro.larese@mm-one.com','mauro.larese@gmail.com', 'Test email',$body  ) ;
            $client->request('POST', '', $data);
        } catch (GuzzleException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Guzzle Error ') ;
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error ') ;
        }

        return $response->withJson($this->success()) ;
    }
}
