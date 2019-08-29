<?php

namespace App\Action;

use App\DoctrineEncrypt\Encryptors\EncryptorInterface;
use App\Entity\Privacy\ActionHistory;
use App\Entity\Privacy\Privacy;
use App\Resource\DeferredPrivacyResource;
use App\Resource\MandatoryFieldMissingException;
use App\Resource\PrivacyResource;
use App\Service\DeferredPrivacyService;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Ramsey\Uuid\Uuid;
use function session_commit;
use Slim\Http\Request;
use Slim\Http\Response;

class DeferredPrivacies extends AbstractAction{

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function setVisited($request, $response, $args) {
        try {

            $body = $request->getParsedBody();

            $_k = $body['_k'];
            $_j = $body['_j'];

            /** @var EncryptorInterface $enc */
            $enc = $this->getContainer()->get('encryptor');
            $ownerId = $enc->decrypt( base64_decode( $_k )) * 1;
            $privacyUid = $enc->decrypt( base64_decode( $_j ));

            /*********** test ***********
            $privacyUid = '05c4d000-657a-11e8-a451-870c5a3d92f6';
            $ownerId=34;
            *********** test ***********/

            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);

            /** @var DeferredPrivacyService $srv */
            $srv = $this->getContainer()->get('deferred_privacy_service');

            $defRes = new DeferredPrivacyResource($em, $srv );

            $defRes->setVisited($privacyUid);

            $prRes = new PrivacyResource($em);
            /** @var Privacy $pr */
            $pr=$prRes->getPrivacy($privacyUid);

            $userEmail = $pr->getEmail();

            $acHistory = new ActionHistory();
                $acHistory->setType('deferred-visited')
                          ->setUserName( $pr->getEmail() )
                          ->setDescription("User visited deferred confirmation link email=$userEmail uid=".$pr->getId())
                          ->setDate(new DateTime())
                ;

                $em->merge($acHistory);
                $em->flush();


            return $response->withJson( $this->toJson($pr)  );

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception with privacy');
        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function setStatus($request, $response, $args)
    {
        $ownerId = $this->getOwnerId($request);
        $uid = $args['uid'];
        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);

            $body = $request->getParsedBody();
            $status = $body['status'];

            /** @var DeferredPrivacyService $srv */
            $srv = $this->context->get('deferred_privacy_service');
            $res = new DeferredPrivacyResource($em, $srv );

            $res->setStatus($uid,$status);

            $acHistory = new ActionHistory();
            $acHistory->setType('deferred-change-status')
                ->setUserName( 'service' )
                ->setDescription("deferred status   uid=$uid   $status=$status")
                ->setDate(new DateTime())
            ;

            $em->merge($acHistory);
            $em->flush();

            return $response->withJson($this->success());

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

    }



}
