<?php

namespace App\Action;

use App\Entity\Config\ActionHistory;
use App\Entity\Privacy\Privacy;
use App\Entity\Privacy\Term;
use App\Entity\Privacy\TermHistory;
use App\Resource\MandatoryFieldMissingException;
use App\Resource\TermPageResource;
use App\Resource\TermResource;
use App\Resource\Logs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use Ramsey\Uuid\Uuid;
use function session_commit;
use Slim\Http\Request;
use Slim\Http\Response;

class Terms extends AbstractAction{
    const ABS_DEFAULT_TERM_CODE = 'abs-default-term';
    const ABS_DEFAULT_TERM_NAME = 'Informativa da abs';


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function termCopy($request, $response, $args)
    {
        $ownerId = $this->getOwnerId($request);

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);

            $body = $request->getParsedBody();
            $uid = $body['uid'];

            /** @var Term $oldT */
            $oldT = $em->find(Term::class, $uid);
            if(!isset($oldT)) {
                return  $response->withStatus(500, 'Term not found');
            }

            $newUid = Uuid::uuid4();

            /** @var Term $newTerm */
            $newTerm = clone $oldT;
            $newTerm->setUid($newUid);
            $newTerm->setStatus('draft');
            $newTerm->setCreated(new \DateTime());
            $newTerm->setPublished(null);
            $newTerm->setModified(null);
            $newTerm->setSuspended(null);
            $newTerm->setOptions(null);

            $newTerm->setName(  'Copia di '.$oldT->getName());

            $em->persist($newTerm);

            $ud = $this->getUserData($request);
            $ah = Logs::actionLog( $ud, "term copy" ,"term copy" .$oldT->getUid()) ;
            $em->persist( $ah);

            $em->flush();
            return $response->withJson($this->success());

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

    }


    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function termDelete($request, $response, $args)
    {
        $ownerId = $this->getOwnerId($request);

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            /** @var Term $term */
            $term = $em->find(Term::class, $args['id']);
            if(!isset($term)) {
                return  $response->withStatus(500, 'Term not found');
            }


            $term->setDeleted(1);

            $em->merge($term);

            $ud = $this->getUserData($request);
            $ah = Logs::actionLog( $ud, "term delete  " ,"term delete " .$term->getUid()) ;
            $em->persist( $ah);

            $em->flush();


            return $response->withJson($this->success());

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception saving privacy');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getAllTerms($request, $response, $args) {
        session_commit();

        if(isset($args['ownerId'])) $ownerId = $args['ownerId'];
        else  $ownerId = $this->getOwnerId($request);

        /**
         * @var EntityManager $em
         */
        $em = $this->getEmPrivacy($ownerId);

        $terms = null;
        try {
            $terms =  $em->getRepository(Term::class)->findBy(["deleted" => 0]);

            // search if used

            /** @var Term $t */
            foreach ($terms as &$t) {
                $p = $em->getRepository(Privacy::class)->findOneBy(["deleted"=>0,"termId" => $t->getUid()]);
                if(isset($p)) {
                    $t->setDeletable(false);
                }
            }


        } catch(\Exception $e) {
            echo $e->getMessage();
        }

        $js = $this->toJson($terms);
        return $response->withJson( $js);
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
    public function getTerm($request, $response, $args) {
        $ownerId = $this->getOwnerId($request);
        $termId = $args['id'];

        if($termId === '_empty_') {
            $userData = get_object_vars($this->getUserData($request) );
            $js = $this->toJson( TermResource::emptyTerm($userData) );
            return $response->withJson( $js);
        }
        /** @var EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);

        /** @var Term $term */
        $term = $em->find(Term::class, $termId);
        $pagesRes = new TermPageResource($em);
        $term->setPages( $pagesRes->findAll($termId) );
        $js = $this->toJson($term);
        return $response->withJson( $js);
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws ORMException
     */
    public function updateTerm ($request, $response, $args) {
        $ownerId = $this->getOwnerId($request);
        $user = $this->getUserData($request);
        $userId = 0;
        if(isset($user)) {
            $userId = $user->userId;
        }
        $uid = $args['id'];
        /** @var EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);
        $body = $request->getParsedBody();

        try {
            $name = $this->getAttribute('name', $body, true);
            $deleted = $this->getAttribute('deleted', $body);

            $status = $this->getAttribute('status', $body );
            $paragraphs = $this->getAttribute('paragraphs', $body );
            $pages = $this->getAttribute('pages', $body, false, [] );
            $options = $this->getAttribute('options', $body );
        } catch (MandatoryFieldMissingException $e) {
            return $response->withStatus(500, 'Missing parameter ' . $e->getMessage());
        }

        $res = new TermResource($em);
        $pagRes = new TermPageResource($em);

        try {
            /** @var Term $term */
            $term = $res->update(
                $uid,
                $name,
                $deleted,
                $status,
                $paragraphs,
                $options);

            $pagRes->merge($pages);

            $termJson = $this->toJson($term);
            $res->saveLog('update',$termJson, $term->getUid(), $userId,'update term');

            return $response->withJson($this->success());
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'OptimisticLockException inserting term');
        } catch (TransactionRequiredException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'TransactionRequiredException inserting term');
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException inserting term');
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception inserting term');
        }

    }

    /**
     * $testo_new = htmlspecialchars_decode(htmlentities($testo_new, ENT_NOQUOTES, 'UTF-8'), ENT_NOQUOTES);
     */

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function insertTerm ($request, $response, $args) {
        $ownerId = $this->getOwnerId($request);
        $user = $this->getUserData($request);
        $userId = 0;

        if(isset($user)) {
            $userId = $user->userId;
        }

        /** @var EntityManager $em */
        $em = $this->getEmPrivacy($ownerId);

        $body = $request->getParsedBody();

        try {
            $uid = $this->getAttribute('uid', $body, true);
            $name = $this->getAttribute('name', $body, true);
            $status = $this->getAttribute('status', $body, false);
            $paragraphs = $this->getAttribute('paragraphs', $body, false);
            $options = $this->getAttribute('options', $body, false, [] );
            $pages = $this->getAttribute('pages', $body, false, [] );
        } catch (MandatoryFieldMissingException $e) {
            return $response->withStatus(500, 'Missing parameter ' . $e->getMessage());
        }

        try {
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $termRsc = new TermResource($em);
            $term = $termRsc->insert(
                $name,
                $options,
                $paragraphs,
                $status,
                $uid
            );

            $pagRes = new TermPageResource($em);
            $pagRes->merge($pages);


            $termJson = $this->toJson($term);
            $termRsc->saveLog('insert',$termJson, $term->getUid(), $userId,'insert new term');

            return $response->withJson($this->success());
        } catch (OptimisticLockException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'OptimisticLockException inserting term');
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException inserting term');
        }catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception inserting term');
        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     */
    public function termsAndTreatsFW($request, $response, $args)
    {
        try {
            $ownerId = $this->getOwnerId($request);
            /**
             * @var EntityManager $em
             */
            $em = $this->getEmPrivacy($ownerId);


            $termRes = new TermResource($em);
            $res = $termRes->termAndTreatmentsFlyWeight();
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'ORMException');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

        return $response->withJson( $this->toJson($res) );
    }

    public function upgradeUsers(Request $request, Response $response, $args) {
        try {

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error inserting new term');
        }
        return $response->withJson($this->success());
    }
    public function acceptUpdate(Request $request, Response $response, $args) {
        try {

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error accepting update ');
        }
        return $response->withJson($this->success());
    }


}
