<?php
namespace App\Action;
/*
use App\Entity\Config\Domain;
use App\Entity\Config\Page;*/

use App\Action\AttachmentView;
use App\Entity\Config\Properties;
use App\Entity\Privacy\Privacy;
use App\Entity\Privacy\PrivacyDeferred;
use App\Entity\Privacy\Term;
use App\Entity\Privacy\TermPage;
use App\Helpers\UploadsManager;
use App\InternalImporters\PrivacyImporter;
use App\Resource\DomainResource;
use App\Resource\OwnerExistException;
use App\Resource\Privacy\GroupByEmailTerm;
use App\Resource\Privacy\PostFilter;
use App\Resource\PrivacyNotFoundException;
use App\Resource\PrivacyResource;
use App\Resource\PropertiesResource;
use App\Resource\PropertyNotFoundException;
use App\Resource\TermPageResource;
use App\Resource\TermResource;
use App\Service\DeferredPrivacyService;
use App\Service\FilesService;
use App\Traits\UrlHelpers;
use function base64_encode;
use DateTime;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use function fnmatch;
use Interop\Container\Exception\ContainerException;
use function json_decode;
use function json_encode;
use function md5;
use function move_uploaded_file;
use function pathinfo;
use function print_r;
use Ramsey\Uuid\Uuid;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;
use function substr;
use function toJson;

class PrivacyManager extends AbstractAction
{
    use UrlHelpers;

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getPrivacyiesByEmailOwnerDomain($request, $response, $args) {

        try {
            $_k = $request->getParam('_k');
            $encr = $this->getContainer()->get('encryptor');
            $params = $this->urlB32DecodeToArray($_k, $encr);
            $email = $params['email'];
            $ownerId = $params['ownerId'];

            $domain = null;
            if(isset($params['domain']))
            $domain = $params['domain'];


            $domain = null;
            if(isset($params['domain']))
                $domain = $params['domain'];

            $em = $this->getEmPrivacy($ownerId);
            $pres = new PrivacyResource($em);
            $privacies = $pres->privacyRecord($email, $domain);
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error finding privacies');
        }

        return $response->withJson(  $privacies);

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getPrivacy($request, $response, $args) {
        $id = $args['id'];
        $uid = substr($id,0, 36);
        $ownerHash = substr($id,37, strlen ($id));

        try {
            $ownerId = $this->findOwnerIdFromHash($ownerHash);
        } catch (OwnerExistException $e) {
            return $response->withStatus(500,$e->getMessage());
        }
        $em = $this->getEmPrivacy($ownerId);
        $pres = new PrivacyResource($em);

        try {
            /** @var Privacy $p */
            $p = $pres->getPrivacy($uid);

            $cForm = $p->getCryptedForm();
            $cForm = json_decode($cForm, true);

            $p->setCryptedForm($cForm);
        } catch (PrivacyNotFoundException $e) {
            echo $e->getMessage();
            return $response->withStatus(500,'PrivacyNotFoundException');
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            return $response->withStatus(500,'OptimisticLockException');
        } catch (TransactionRequiredException $e) {
            echo $e->getMessage();
            return $response->withStatus(500,'TransactionRequiredException');
        } catch (ORMException $e) {
            echo $e->getMessage();
            $response->withStatus(500,'ORMException');
        }catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500,'Exception');
        }

        return $response->withJson( $this->toJson($p));
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getPrivacyByEmail($request, $response, $args) {
        try {
            $_k = $request->getParam('_k');

            $params = $this->urlB64DecodeToArray($_k);
            $email = $params['email'];
            $ownerId = $params['ownerId'];

            $em = $this->getEmPrivacy($ownerId);
            $pres = new PrivacyResource($em);
            $user = $pres->privacyRecord($email);


        }catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500,'Exception');
        }

        return $response->withJson( $this->toJson($user));
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getWidgetTermById($request, $response, $args) {
        $id = $args['id'];
        $ownerId = $request->getHeader('OwnerId')[0];
        $em = $this->getEmPrivacy($ownerId);

        /** @var Privacy $pr */
        $pr = $em->find(Privacy::class,$id);
        $cr = $pr->getCryptedForm();
        echo  $cr ; die;
        print_r($pr);die;

    }

    /**
     * @param $ownerId
     * @param $lang
     * @param $pageName
     * @param $domainName
     * @param $ref
     * @param $termId
     *
     * @return array
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws PropertyNotFoundException
     * @throws TermNotFoundException
     * @throws TransactionRequiredException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function extractTermToSign ($ownerId, $lang, $termId, $pageName=null, $domainName=null ) {

        $httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

        /** @var EntityManager $em */
        $cem = $this->getEmConfig();

        /** @var EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);


        if(!isset($termId) || $termId===''){
            $termRes = new TermResource($em);
            $termPgRes = new TermPageResource($em);
            // $pages = $termPgRes->findByPage($domainName, $pageName);

            /** @var TermPages $termPage  */
            $termPage = $em
                ->getRepository(TermPage::class)
                ->findOneBy(array('domain' => $domainName, 'page' => $pageName, 'deleted'=>0));

            If(!isset($termPage)) {
                return $response->withStatus(403, "Page $domainName$pageName not found (owner $ownerId)");
            }

            $termId = $termPage->getTermUid();
        }

        $propRes = new PropertiesResource($this->getEmConfig());
        /** @var Term $term */
        $term = null;


        $term = $em->find(Term::class, $termId);


        if(!isset($term)) {
            throw new TermNotFoundException("Term not found");
        }

        $scrollText = '';

        $paragraphs = $term->getParagraphs();
        $termResponse = [];
        $requestLanguage = $lang;


        foreach($paragraphs as $p) {

            try {
                if (!isset($p['text'][$lang])) {
                    $lang = 'en';
                    if (!isset($p['text'][$lang])) {
                        $lang = 'it';
                    }
                }
                $title = "";
                if (isset($p['title'][$lang])) {
                    $title = $p['title'][$lang];
                }
                $text = $p['text'][$lang];
                $newP = array(
                    "text" => $text,
                    "treatments" => array(),
                    "scrolled" => false,
                    "title" => $title,
                    "scrollText" => ''
                );
                foreach ($p['treatments'] as $t) {
                    $tlang = '';
                    if (isset($t['text'][$lang])) {
                        $tlang = $t['text'][$lang];
                    }
                    $newT = array(
                        "code" => $t['name'],
                        "restrictive" => $t['restrictive'],
                        "mandatory" => $t['mandatory'],
                        "text" => $tlang,
                        "selected" => false
                    );


                    $newP['treatments'][] = $newT;
                }
                $termResponse[] = $newP;
            } catch (Exception $e) {
            }
        }

        return $termResponse;
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function toSuscribeTerm($request, $response, $args) {

        try {
            $ownerId = $this->getOwnerId($request);
            $language = $args['language'];
            $termId = $args['termId'];


            $termResponse = $this->extractTermToSign(
                $ownerId,
                $language,
                $termId);

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }

        return $response->withJson( $this->toJson($termResponse) );
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function selectWidgetTerm($request, $response, $args) {

        try {
            $_k = $request->getParam('_k');
            $params = base64_decode(urldecode($_k));
            $params = json_decode($params, true);
            $lang = $params['language'];
            $pageName = $params['page'];
            $domainName = $params['domain'];
            $ownerId = $params['ownerId'];
            $ref = $params['ref'];
            $termId = $params['termId'];
            $termResponse = $this->extractTermToSign($ownerId, $lang, $pageName, $domainName, $ref, $termId);
            $js = $this->toJson($termResponse);
            $this->addP3P($response);
            return $response->withJson(
                array(
                    "referrer" => $httpReferer,
                    "ownerId" => $ownerId,
                    "termId" => $termId,
                    "language" => $requestLanguage,
                    "name" => $term->getName(),
                    "paragraphs" => $js
                )
            );
        } catch (TermNotFoundException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'Term not found');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @deprecated   No longer used  use selectWidgetTerm
     */
    public function getWidgetTerm($request, $response, $args) {
        $_k=$request->getParam('_k');

        $params=base64_decode(  urldecode($_k) );
        //$params = $request->getHeader('Domain')[0];

        $params = json_decode($params, true);


        $lang = $params['language'];
        $pageName = $params['page'];
        $domainName = $params['domain'];
        $ownerId = $params['ownerId'];
        $ref = $params['ref'];
        $termId = $params['termId'];
        $httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;


        // die(" lang=$lang, pageName=$pageName, domainName=$domainName, ownerId=$ownerId, ref=$ref, termId=$termId");

        /** @var EntityManager $em */
        $cem = $this->getEmConfig();

        /** @var EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);

         $dmnRsc = new DomainResource($em);


        try {
            $dmnRsc->ownerHas($domainName);
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(403, $e->getMessage());
        }


        if($termId===''){
            $termRes = new TermResource($em);
            $termPgRes = new TermPageResource($em);
            // $pages = $termPgRes->findByPage($domainName, $pageName);

            /** @var TermPages $termPage  */
            $termPage = $em
                        ->getRepository(TermPage::class)
                        ->findOneBy(array('domain' => $domainName, 'page' => $pageName, 'deleted'=>0));

            If(!isset($termPage)) {
                return $response->withStatus(403, "Page $domainName$pageName not found (owner $ownerId)");
            }

            $termId = $termPage->getTermUid();
        }

        $propRes = new PropertiesResource($this->getEmConfig());
        /** @var Term $term */
        $term = null;

        try {
            $term = $em->find(Term::class, $termId);
            $scrollText = $propRes->widgetScrollText();
        } catch (PropertyNotFoundException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'PropertyNotFoundException finding term');
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'OptimisticLockException finding term');
        } catch (TransactionRequiredException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'TransactionRequiredException finding term');
        } catch (ORMException $e) {
            echo $e->getMessage().' '.$termId;
            return $response->withStatus(403, 'ORMException finding term with termids');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'Exception finding term');
        }


        If(!isset($term)) {
            return $response->withStatus(403, "Term not found [$termId]");
        }
        $paragraphs = $term->getParagraphs();
        $termResponse = array();

        $requestLanguage = $lang;
        foreach($paragraphs as $p) {
            if(!isset($p['text'][$lang])) {
                $lang = 'en';
                if(!isset($p['text'][$lang])) {
                    $lang = 'it';
                }
            }

            $title = "";
            if(isset($p['title'][$lang])) {
                $title = $p['title'][$lang];
            }

            if(isset($scrollText[$lang]))
                $curScrollText = $scrollText[$lang];
            else
                $curScrollText = $scrollText['en'];

            $newP = array(
              "text" => $p['text'][$lang],
              "treatments" => array(),
              "scrolled" => false,
              "title" => $title,
              "scrollText" => $curScrollText
            );

            foreach($p['treatments'] as $t) {
                $newT = array(
                   "code" => $t['name'],
                   "restrictive" => $t['restrictive'],
                   "mandatory" => $t['mandatory'],
                   "text" => $t['text'][$lang],
                   "selected" => false
                );

                $newP['treatments'][] = $newT;
            }
            $termResponse[] = $newP;

        }

        $js = $this->toJson($termResponse);
        $this->addP3P($response);

        return $response->withJson(
            array(
                "referrer" => $httpReferer,
                "ownerId" => $ownerId,
                "termId" => $termId,
                "language" => $requestLanguage,
                "name" => $term->getName(),
                "paragraphs" => $js
            )
        );
    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getWidgetRequest($request, $response, $args) {
        $_k=$request->getParam('_k');

        $params=base64_decode(  urldecode($_k) );
        //$params = $request->getHeader('Domain')[0];

        $params = json_decode($params, true);

        $lang = $params['language'];
        $pageName = $params['page'];
        $domainName = $params['domain'];
        $ownerId = $params['ownerId'];
        $ref = $params['ref'];
        $termId = $params['termId'];
        $httpReferer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

        // die(" lang=$lang, pageName=$pageName, domainName=$domainName, ownerId=$ownerId, ref=$ref, termId=$termId");

        /** @var EntityManager $em */
        $cem = $this->getEmConfig();

        /** @var EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);

        if($termId===''){
            $termRes = new TermResource($em);
            $termPgRes = new TermPageResource($em);
            // $pages = $termPgRes->findByPage($domainName, $pageName);

            /** @var TermPages $termPage  */
            $termPage = $em
                ->getRepository(TermPage::class)
                ->findOneBy(array('domain' => $domainName, 'page' => $pageName, 'deleted'=>0));

            If(!isset($termPage)) {
                return $response->withStatus(403, "Page $domainName$pageName not found (owner $ownerId)");
            }

            $termId = $termPage->getTermUid();
        }

        $propRes = new PropertiesResource($this->getEmConfig());
        /** @var Term $term */
        $term = null;

        try {
            $term = $em->find(Term::class, $termId);
            $reqText = $propRes->widgetSendRequestText();
            $capText = $propRes->widgetSendCaptionText();
            $successText = $propRes->widgetSendRequestSuccessText();

        } catch (PropertyNotFoundException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'PropertyNotFoundException finding term');
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'OptimisticLockException finding term');
        } catch (TransactionRequiredException $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'TransactionRequiredException finding term');
        } catch (ORMException $e) {
            echo $e->getMessage().' '.$termId;
            return $response->withStatus(403, 'ORMException finding term with termids');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(403, 'Exception finding term');
        }


        If(!isset($term)) {
            return $response->withStatus(403, "Term not found [$termId]");
        }
        $paragraphs = $term->getParagraphs();
        $termResponse = array();

        $requestLanguage = $lang;
        foreach($paragraphs as $p) {
            if(!isset($p['text'][$lang])) {
                $lang = 'en';
                if(!isset($p['text'][$lang])) {
                    $lang = 'it';
                }
            }

            $title = "";
            if(isset($p['title'][$lang])) {
                $title = $p['title'][$lang];
            }

            if(!isset($reqText[$lang])) {
                if(isset($reqText['en'])) $requestText = $reqText['en'];
                else $requestText = $reqText['it'];
            }else  $requestText = $reqText[$lang];

            if(!isset($successText[$lang])) {
                if(isset($successText['en'])) $successText= $successText['en'];
                else $successText= $successText['it'];
            }else  $successText= $successText[$lang];


            $newP = array(
                "text" => $p['text'][$lang],
                "treatments" => array(),
                "scrolled" => false,
                "title" => $title
            );

            foreach($p['treatments'] as $t) {
                $newT = array(
                    "code" => $t['name'],
                    "restrictive" => $t['restrictive'],
                    "mandatory" => $t['mandatory'],
                    "text" => $t['text'][$lang],
                    "selected" => false
                );

                $newP['treatments'][] = $newT;
            }
            $termResponse[] = $newP;

        }

        $js = $this->toJson($termResponse);
        $this->addP3P($response);

        $captionText = 'Send';
        if( isset($capText[$requestLanguage]) ) {
            $captionText = $capText[$requestLanguage];
        }
        return $response->withJson(
            array(
                "referrer" => $httpReferer,
                "ownerId" => $ownerId,
                "termId" => $termId,
                "language" => $requestLanguage,
                "name" => $term->getName(),
                "requestText" => $requestText,
                "successText" => $successText,
                "captionText" => $captionText,
                "paragraphs" => $js
            )
        );
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function uploadUserPrivacy($request, $response, $args) {

        try {
            $files = $request->getUploadedFiles();
            $ownerId = $this->getOwnerId($request);
            $privacyId = $args['uid'];
            /** @var UploadedFile $file */
            $file = $files['file'];
            $fsrv = new FilesService($this->getContainer());
            $fsrv->saveUserAttachment($file, $ownerId, $privacyId);
            return $response->withJson($this->success());
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'error saving attachment');
        }
    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function savePrivacy($request, $response, $args) {

        // return $this->savePlainPrivacy($request, $response, $args);
        // return $response->withStatus(500, 'debug breakpoint');

        $ownerId = $request->getHeader('OwnerId')[0];
        $body = $request->getParsedBody();

        $hasAttachments = false;
        try {
            $ip = $this->getIp();
            $domain = $body['domain'];
            $id = $body['id'];
            $email = $body['record']['email'];
            $name = $body['record']['name'];
            $surname = $body['record']['surname'];
            $telephone = $body['record']['telephone'];
            $site = $body['page'];

            if(isset($termId)) {
                $termId = $body['termId'];
            } else {
                // nessuna normativa associata
                $termId = 0 ;
            }

            $privacyFlags = $body['flags'];
            $privacy = $body['term'];
            $form = $body['form'];
            $cryptedForm = $body['cryptedForm'];
            $cryptedForm = json_encode($cryptedForm);// print_r($privacy); die;
            $ref = $body['ref'];
            if (!isset($ref)) {
                $ref = '';
            }
            /**
             * @var EntityManager $em
             */
            $em = $this->getEmPrivacy($ownerId);
            $prRes = new PrivacyResource($em);

            $pr=$prRes->savePrivacy(
                $ip,
                $form,
                $cryptedForm,
                $name,
                $surname,
                $termId,
                $site,
                $privacy,
                $id,
                $ref,
                $domain,
                $email,
                $privacyFlags,
                $telephone
            );

            $jsonPrivacy = $this->toJson($pr);
            $jsonPrivacy = json_encode($jsonPrivacy);
            $ph = $prRes->savePrivacyLog($id, $jsonPrivacy, 'save from website');

        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Orm Exception saving privacy');
        } catch (Exception $e) {
            return $response->withStatus(500, 'Exception saving privacy');
        }


        return $response->withJson($this->success()) ;
    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function downloadAttachment($request, $response, $args) {
        $ownerId = $this->getOwnerId($request);
        $privacyUid = $args['uid'];
        $fname = $args['fname'];
        $fname = $this->urlB64DecodeString($fname);
        $fname = md5($fname);

        /** @var AttachmentsView $av */
       $av = AttachmentsView::getInstance($this->getContainer());
       return $av->getById($request, $response, $args);


        // die('<br>download '.$ownerId . ' '.$fname);
    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function updatePrivacy($request, $response, $args) {
        $ownerId = $request->getHeader('OwnerId')[0];
        $body = $request->getParsedBody();
        $id = $args['id'] ;

        try {
            $email = $body['record']['email'];
            $name = $body['record']['name'];
            $surname = $body['record']['surname'];
            $telephone = $body['record']['telephone'];

            $privacyFlags = $body['flags'];
            $privacy = $body['term'];
            $form = $body['form'];
            $cryptedForm = $body['cryptedForm'];
            $cryptedForm = json_encode($cryptedForm);// print_r($privacy); die;

            /**
             * @var EntityManager $em
             */
            $em = $this->getEmPrivacy($ownerId);
            $prRes = new PrivacyResource($em);

            try {
                $pr = $prRes->updatePrivacy(
                    $id,
                    $form,
                    $cryptedForm,
                    $name,
                    $surname,
                    $privacy,
                    $email,
                    $privacyFlags,
                    $telephone
                );
                $jsonPrivacy = $this->toJson($pr);
                $jsonPrivacy = json_encode($jsonPrivacy);
                $prRes->savePrivacyLog($id,$jsonPrivacy, 'privacy update');
            } catch (PrivacyNotFoundException $e) {
                echo $e->getMessage();
                return $response->withStatus(500, 'Privacy Not Found Exception  saving privacy');
            } catch (OptimisticLockException $e) {
                echo $e->getMessage();
                return $response->withStatus(500, 'OptimisticLockExceptionsaving privacy');
            } catch (TransactionRequiredException $e) {
                echo $e->getMessage();
                return $response->withStatus(500, 'TransactionRequiredException privacy');
            } catch (ORMException $e) {
                echo $e->getMessage();
                return $response->withStatus(500, 'ORMException privacy');
            }

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
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
    public function searchPrivacyGrouped($request, $response, $args)
    {
        $ownerId = $this->getOwnerId($request);
        $list = [];

        $criteria = $request->getParsedBody();

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $priRes = new PrivacyResource($em);
            $list = $priRes->privacyList($criteria, new GroupByEmailTerm(), new PostFilter());
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException saving privacy');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

        return $response->withJson($list);
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function searchUsersFw($request, $response, $args)
    {
        $ownerId = $this->getOwnerId($request);
        $list = [];

        $criteria = $request->getParsedBody();

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $priRes = new PrivacyResource($em);
            $list = $priRes->privacyList($criteria, new GroupByEmailTerm(), new PostFilter());
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException saving privacy');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

        return $response->withJson($list);
    }


    /**
     * @param PrivacyResource       $privacyResource
     * @param array                 $data
     * @param integer               $ownerId
     * @param string                $ip
     * @throws Exception
     * @return Privacy
     */
    public static function savePlainPrivacyByAssoc($privacyResource,$data,$ownerId, $ip='') {

        $domain = $data['domain'];

        $email = "";
        if(isset($data['record']['email']))
            $email = $data['record']['email'];
        else if(isset($data['email']))
            $email = $data['email'];

        $name = '';
        if(isset($data['record']['name']))
            $name = $data['record']['name'];
        else if(isset($data['name']))
            $name = $data['name'];

        $surname = '';
        if(isset($data['record']['surname']))
            $surname = $data['record']['surname'];
        else if(isset($data['surname']))
            $surname = $data['surname'];

        $telephone = '';
        if(isset($data['record']['telephone']))
            $telephone = $data['record']['telephone'];
        else if(isset($data['telephone']))
            $telephone = $data['telephone'];

        $site = $data['page'];

        $page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

        $language=null;
        if(isset($data['language']))   $language = $data['language'];

        if(isset($data['id'])) {
            $id = $data['id'];
        } else {
            $id = Uuid::uuid4() ;
        }

        if(isset($data['termId'])) {
            $termId = $data['termId'];
        } else {
            // nessuna normativa associata
            $termId = 0 ;
        }



        if(isset($data['flags']))
            if($data['flags']!=='')
                $privacyFlags = $data['flags'];
            else
                $privacyFlags = [];
        else
            $privacyFlags = [];

        if(isset($data['term']))
            if($data['term']!=='')
                $privacy = $data['term'];
            else
                $privacy = [];
        else
            $privacy = [];

        if(isset($page)) {
            $privacy['referrer'] = $page;
        }

        if(isset($data['form']))
            if($data['form']!=='')
                $form = $data['form'];
            else
                $form = [];
        else
            $form = [];



        if(isset($data['cryptedForm'])) {
            if($data['cryptedForm']!=='')
                $cryptedForm = $data['cryptedForm'];
            else
                $cryptedForm = [];
;
        } else {
            $cryptedForm = $form;
        }

        $cryptedForm = json_encode($cryptedForm);

        $ref = "";

        if(isset($data['ref'])) {
            $ref = $data['ref'];
        }

        $deferred = DeferredPrivacyService::DEFERRED_TYPE_NO;
        if(isset($data['deferred'])) {
            $deferred = $data['deferred'];
        }


        /** @var Privacy $pr */
        $pr=$privacyResource->savePrivacy(
            $ip,
            $form,
            $cryptedForm,
            $name,
            $surname,
            $termId,
            $site,
            $privacy,
            $id,
            $ref,
            $domain,
            $email,
            $privacyFlags,
            $telephone,
            $language,
            $page,
            false,
            $deferred
        );

        return $pr ;
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function deferredVisited($request, $response, $args) {

        try {
            $body = $request->getParsedBody();
            $ref = $body['ref'];

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500,'System Error');
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function import($request, $response, $args) {

        try {
            $ownerId = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $prRes = new PrivacyResource($em);

            /** @var UploadedFile $f */
            $f = ($request->getUploadedFiles())['upload'];
            $fi = new UploadsManager($f->file);
            $pi = new PrivacyImporter($fi,$prRes,$ownerId);

            $res = $pi->fromCsv($fi->getCsv());

        } catch (Exception $e) {
            return $response->withStatus(500, 'Exception saving privacy');
        }
        return $response->withJson($this->success(["errors"=>$res])) ;
    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function savePlainPrivacy($request, $response, $args) {

        try {
            $rawbody = $request->getBody();
            $body = $rawbody->read($rawbody->getSize());

            $body = json_decode($body,true);
            $ownerId = $body['ownerId'];

            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $prRes = new PrivacyResource($em);
            $prRes->setContainer($this->getContainer());
            $prRes->setOwnerId($ownerId);

            $ip = $this->getIp();

            $pr = self::savePlainPrivacyByAssoc(
                $prRes,
                $body,
                $ownerId,
                $ip
            );

            $jsonPrivacy = $this->toJson($pr);
            $jsonPrivacy = json_encode($jsonPrivacy);
            $ph = $prRes->savePrivacyLog($pr->getId(), $jsonPrivacy, 'save from website');

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }


        return $response->withJson($this->success()) ;
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function savePrivacyJwt($request, $response, $args) {

        try {
            $body = $request->getParsedBody();
            $ownerId = $this->getOwnerId($request);

            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $prRes = new PrivacyResource($em);
            $prRes->setContainer($this->getContainer());
            $prRes->setOwnerId($ownerId);

            $ip = $this->getIp();

            $pr = self::savePlainPrivacyByAssoc(
                $prRes,
                $body,
                $ownerId,
                $ip
            );

            $jsonPrivacy = $this->toJson($pr);
            $jsonPrivacy = json_encode($jsonPrivacy);
            $ph = $prRes->savePrivacyLog($pr->getId(), $jsonPrivacy, 'save from website');

        } catch (Exception $e) {
            return $response->withStatus(500, 'Exception saving privacy');
        }


        return $response->withJson($this->success()) ;
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function privacyUsers($request, $response, $args) {
        return $response->withStatus(500, 'not implemented');
        try {
            $ownerId = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);

            $criteria = $request->getParsedBody();

            $privacyRes = new PrivacyResource($em);
            $users = $privacyRes->nativeSearchPrivacy($criteria);


        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }

        return $response->withJson( $users);
    }

}
