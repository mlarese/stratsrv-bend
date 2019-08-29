<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 19/06/2018
 * Time: 09:39
 */

namespace App\Action;


use App\Resource\TermPageResource;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Exception;
use function session_commit;
use Slim\Http\Request;
use Slim\Http\Response;

class TermPages extends AbstractAction {
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function getTermsPages($request, $response, $args) {

        try {
            $ownerId = $this->getOwnerId($request);
            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $pagesRes = new TermPageResource($em);
            $res = $pagesRes->findAll();
            return $response->withJson($this->toJson($res));

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
     */
    public function getTermPages($request, $response, $args) {

        try {
            session_commit();
            $ownerId = $this->getOwnerId($request);
            $termId = $args['termId'];

            /** @var EntityManager $em */
            $em = $this->getEmPrivacy($ownerId);
            $pagesRes = new TermPageResource($em);


            $res = $pagesRes->findByTerm($termId);

            return $response->withJson($this->toJson($res));

        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error');
        }
    }
}
