<?php
namespace App\Action;

use App\Action\Emails\EmailHelpers;
use App\Entity\Config\Owner;
use App\Entity\Config\OwnerUserRequest;
use App\Entity\Privacy\UserRequest;
use App\Resource\EmailResource;
use App\Resource\PrivacyResource;
use App\Service\EmailService;
use App\Service\SubscriptionService;
use App\Traits\UrlHelpers;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Ramsey\Uuid\Uuid;
use Slim\Http\Request;
use Slim\Http\Response;

class UsersRequests  extends AbstractAction{

    const STATUS_OPEN = 'open';
    const STATUS_COMPLETED = 'completed';
    const STATUS_PROGRESS = 'in_progress';

    const TYPE_SUBSCRIPTIONS_REQUEST = 'subscriptions_request';
    const TYPE_UNSUBSCRIBE_ALL_REQUEST = 'unsubscribe_all_request';
    const TYPE_UNSUBSCRIBE_NEWS_REQUEST = 'unsubscribe_news_request';

    use UrlHelpers;

    protected function addHistory ($action, $options, $status,$user='surfer') {
        return [
            'action' => $action,
            'user'=> $user,
            'options'=>$options,
            'status' => $status
        ];
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function insertUnsubscribeNewsRequest($request, $response, $args){

        try {
            $body = $request->getParsedBody();
            $_k = $body['_k'];

            $enc = $this->getContainer()->get('encryptor');
            $params = $this->urlB32DecodeToArray($_k, $enc);

            $ownerId = $params['ownerId'];
            $email = $params['email'];
            $domain = $params['domain'];
            $privacies = $body['privacies'];
            $em = $this->getEmPrivacy($ownerId);
            /** @var UserRequest $r */
            $r = new UserRequest();
            $uid = Uuid::uuid4();
            $pres = new  PrivacyResource($em);


            $emService = new  EmailService();
            $lastPrv = $pres->getLastPrivacyByEmail($email);
            if (!isset($lastPrv)) {
                return $response->withStatus(500, 'Error finding last privacy');
            }
            if (isset($body["language"])) {
                $language = $body["language"];
            } else {
                $language = $lastPrv->getLanguage();
            }
            $flow = [
                'privacies' => $privacies
            ];
            /** @var Owner $owner */
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);
            $hrec = $this->addHistory('insert', $flow, self::STATUS_OPEN);
            $h = [$hrec];
            $r->setUid($uid)
                ->setCreated(new \DateTime())
                ->setStatus(self::STATUS_COMPLETED)
                ->setType(self::TYPE_UNSUBSCRIBE_NEWS_REQUEST)
                ->setMail($email)
                ->setFlow($flow)
                ->setHistory($h)
                ->setNote('')
                ->setDomain($domain);
            $or = new OwnerUserRequest();
            $or->setUserRequestId($r->getUid())
                ->setOwnerId($ownerId);
            $em->merge($r);
            $this->getEmConfig()->merge($or);
            $emailRes = new EmailResource($em, $this->getEmConfig());
            $this->getEmConfig()->flush();
            $em->flush();

            $emService = new  EmailService();
            /** @var Owner $owner */
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);

            $subscriptionService = new SubscriptionService();
            $subscriptionService->unsubFromNewsletters($em, $privacies, 'surfer');

            $emService->notifyUnsubNewsletters(
                $this->getContainer(),
                $owner->getEmail(),
                $email,
                $language,
                $lastPrv->getName(),
                $lastPrv->getSurname()
            );
            return $response->withJson($this->success());


        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error on request');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function insertUnsubscribeAllRequest($request, $response, $args){

        try {
            $body = $request->getParsedBody();
            $_k = $body['_k'];

            $enc = $this->getContainer()->get('encryptor');
            $params = $this->urlB32DecodeToArray($_k, $enc);

            $ownerId = $params['ownerId'];
            $email = $params['email'];

            $domain = null;
            if(isset($params['domain']))
                $domain = $params['domain'];
            $privacies = $body['privacies'];
            $em = $this->getEmPrivacy($ownerId);
            /** @var UserRequest $r */
            $r = new UserRequest();
            $uid = Uuid::uuid4();
            $pres = new  PrivacyResource($em);


            $emService = new  EmailService();
            $lastPrv = $pres->getLastPrivacyByEmail($email);
            if (!isset($lastPrv)) {
                return $response->withStatus(500, 'Error finding last privacy');
            }
            if (isset($body["language"])) {
                $language = $body["language"];
            } else {
                $language = $lastPrv->getLanguage();
            }
            $flow = [
                'privacies' => $privacies
            ];
            /** @var Owner $owner */
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);
            $hrec = $this->addHistory('insert', $flow, self::STATUS_OPEN);
            $h = [$hrec];
            $r->setUid($uid)
                ->setCreated(new \DateTime())
                ->setStatus(self::STATUS_OPEN)
                ->setType(self::TYPE_UNSUBSCRIBE_ALL_REQUEST)
                ->setMail($email)
                ->setFlow($flow)
                ->setHistory($h)
                ->setNote('')
                ->setDomain($domain);
            $or = new OwnerUserRequest();
            $or->setUserRequestId($r->getUid())
                ->setOwnerId($ownerId);
            $em->merge($r);
            $this->getEmConfig()->merge($or);
            $emailRes = new EmailResource($em, $this->getEmConfig());
            $this->getEmConfig()->flush();
            $em->flush();

            $emService = new  EmailService();
            /** @var Owner $owner */
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);

            $emService->notifyModAccepted(
                $this->getContainer(),
                $owner->getEmail(),
                $email,
                $language,
                $lastPrv->getName(),
                $lastPrv->getSurname()
            );
            return $response->withJson($this->success());


        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error on request');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function insertSubscriptionRequest($request, $response, $args){
        try {

            $body = $request->getParsedBody();
            $mail = $body ['email'];
            $ownerId = $body ['ref'];
            $language = "it";
            $reqDomain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $type = self::TYPE_SUBSCRIPTIONS_REQUEST;
            if(isset($body["domain"])) {
                $reqDomain = $body["domain"];
            }

            if(isset($body["type"])) {
                $type = $body["type"];
            }

            $ownerId = $this->findOwnerIdFromHash($ownerId);

            $em = $this->getEmPrivacy($ownerId);


            /** @var UserRequest $r */
            $r = new UserRequest();

            $uid = Uuid::uuid4();

            $pres = new  PrivacyResource($em);
            $emService = new  EmailService();

            $lastPrv = $pres->getLastPrivacyByEmail($mail);


            if(!isset($lastPrv)) {
                return $response->withStatus(500, 'Error finding last privacy');
            }

            if(isset($body["language"])) {
                $language = $body["language"];
            } else {
                $language = $lastPrv->getLanguage();
            }

            /** @var Owner $owner */
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);


            $r->setUid($uid )
                ->setCreated(new \DateTime())
                ->setStatus(self::STATUS_COMPLETED)
                ->setType($type)
                ->setMail($mail)
                ->setNote('')
                ->setDomain($reqDomain)
            ;



            $or = new OwnerUserRequest();
            $or->setUserRequestId( $r->getUid())
                ->setOwnerId( $ownerId);

            $em->merge($r);
            $this->getEmConfig()->merge($or);

            $emailRes = new EmailResource($em, $this->getEmConfig());

            $this->getEmConfig()->flush();
            $em->flush();

            $emailRes->privacyRequest($language, $mail,$ownerId,$this->getContainer(), $reqDomain);



            return $response->withJson($this->success());
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error on request');
        }

    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function insertSubscriptionRequestFromLink($request, $response, $args){
        try {

            $body = [
                "email"=>$request->getParam('email'),
                "ownerId"=>$request->getParam('ownerId'),
                "domain"=>$request->getParam('domain'),
                "language"=>$request->getParam('language')
            ];
            $mail = $body ['email'];
            $ownerId = $body ['ownerId'];
            $language = "it";
            $reqDomain = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $type = self::TYPE_SUBSCRIPTIONS_REQUEST;
            if(isset($body["domain"])) {
                $reqDomain = $body["domain"];
            }


           $type = self::TYPE_SUBSCRIPTIONS_REQUEST;

            $em = $this->getEmPrivacy($ownerId);


            /** @var UserRequest $r */
            $r = new UserRequest();

            $uid = Uuid::uuid4();

            $pres = new  PrivacyResource($em);
            $emService = new  EmailService();


            $lastPrv = $pres->getLastPrivacyByEmail($mail);


            if(!isset($lastPrv)) {
                return $response->withStatus(500, 'Error finding last privacy');
            }

            if(isset($body["language"])) {
                $language = $body["language"];
            } else {
                $language = $lastPrv->getLanguage();
            }

            /** @var Owner $owner */
            $owner = $this->getEmConfig()->find(Owner::class, $ownerId);


            $r->setUid($uid )
                ->setCreated(new \DateTime())
                ->setStatus(self::STATUS_COMPLETED)
                ->setType($type)
                ->setMail($mail)
                ->setNote('')
                ->setDomain($reqDomain)
                ;



            $or = new OwnerUserRequest();
            $or->setUserRequestId( $r->getUid())
                ->setOwnerId( $ownerId);

            $em->merge($r);
            $this->getEmConfig()->merge($or);

            $emailRes = new EmailResource($em, $this->getEmConfig());

            $this->getEmConfig()->flush();
            $em->flush();

            $emailRes->privacyRequest($language, $mail,$ownerId,$this->getContainer(), $reqDomain);

            return $response->withRedirect("https://privacy.dataone.online/manager/surfer/soongetmail?language=$language");
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error on request');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function update($request, $response, $args){

        try {
            $owid = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($owid);
            $body = $request->getParsedBody();
            /** @var UserRequest $ur */
            $ur = $em->find(UserRequest::class, $args['id']);
            if (!isset($ur)) {
                return $response->withStatus(500, 'UserRequest not found');
            }
            if (isset($body['note'])) $ur->setNote($body['note']);
            if (isset($body['status'])) $ur->setStatus($body['status']);
            if (isset($body['history'])) $ur->setHistory($body['history']);
            if (isset($body['flow'])) $ur->setFlow($body['flow']);
            if (isset($body['domain'])) $ur->setDomain($body['domain']);
            if (isset($body['site'])) $ur->setSite($body['site']);
            $ur->setLastAccess(new \DateTime());
            $em->merge($ur);
            $em->flush();
        } catch (Exception $e) {
            echo $e->getMessage();
            return  $response->withStatus(500, 'Error');
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */

    public function closeRequest($request, $response, $args){

        try {
            $owid = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($owid);

            /** @var UserRequest $ur */
            $ur = $em->find(UserRequest::class, $args['id']);
            if (!isset($ur)) {
                return $response->withStatus(500, 'UserRequest not found');
            }


            $ur->setStatus(self::STATUS_COMPLETED);
            $ur->setLastAccess(new \DateTime());
            $em->merge($ur);
            $em->flush();
        } catch (Exception $e) {
            echo $e->getMessage();
            return  $response->withStatus(500, 'Error');
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */

    public function retrieve($request, $response, $args){

        try {

            $owid = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($owid);

            $r = $em->getRepository(UserRequest::class)->findAll();

            return $response->withJson($this->toJson($r));

        } catch (Exception $e) {
            echo $e->getMessage();
            return  $response->withStatus(500, 'Error');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */

    public function retrieveOpen($request, $response, $args){

        try {

            $owid = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($owid);

            $r = $em->getRepository(UserRequest::class)->findBy(
                ["status"=>"open"]
            );

            return $response->withJson($this->toJson($r));

        } catch (Exception $e) {
            echo $e->getMessage();
            return  $response->withStatus(500, 'Error');
        }

    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */

    public function retrieveOne($request, $response, $args){
        try {
            $owid = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($owid);
            $body = $request->getParsedBody();
            /** @var UserRequest $ur */
            $ur = $em->find(UserRequest::class, $args['id']);
            if (!isset($ur)) {
                return $response->withStatus(500, 'UserRequest not found');
            }
        } catch (Exception $e) {
            echo $e->getMessage();
            return  $response->withStatus(500, 'Error');
        }


    }

}
