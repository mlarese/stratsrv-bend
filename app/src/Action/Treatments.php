<?php

namespace App\Action;

use App\Entity\Privacy\Treatment;
use App\Entity\Privacy\TreatmentHistory;
use App\Resource\TermResource;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use function session_commit;
use Slim\Http\Request;
use Slim\Http\Response;

class Treatments extends AbstractAction{
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws ORMException
     * @throws \Doctrine\Common\Annotations\AnnotationException
     */
    public function getAllTreatments($request, $response, $args) {
        session_commit();
        $ownerId = $this->getOwnerId($request);

        /**
         * @var EntityManager $em
         */
        $em = $this->getEmPrivacy($ownerId);

        $term = null;
        try {
            $term =  $em->getRepository( Treatment::class)->findAll();
        } catch(\Exception $e) {
            echo $e->getMessage();
        }

        $js = $this->toJson($term);
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
    public function getTreatment($request, $response, $args) {
        session_commit();
        $ownerId = $this->getOwnerId($request);
        $treatmentCode = $args['code'];

        /**
         * @var EntityManager $em
         */
        $em = $this->getEmPrivacy($ownerId);

        $term = $em->find(Treatment::class, $treatmentCode);

        $js = $this->toJson($term);
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
    public function newTreatment($request, $response, $args) {
        $body = $request->getParsedBody();
        $ownerId = $this->getOwnerId($request);

        $em = $this->getEmPrivacy($ownerId);

        $nt = new Treatment();

        try {

            $nt
                ->setCode($this->getAttribute('code',$body, true))
                ->setName($this->getAttribute('name',$body, true))
                ->setCreator($this->getAttribute('creator',$body))
                ->setNote($this->getAttribute('note',$body))
                ->setCreated(new \DateTime())
                ->setDeleted(false)
            ;

            try {
                $em->persist($nt);

                $u = $this->getUserData($request);
                $tlog = new TreatmentHistory();
                $tlog
                    ->setCreated(new DateTime())
                    ->setType('treatment_created')
                    ->setTreatment( $this->toJson($nt))
                    ->setTreatmentCode( $nt->getCode())
                    ->setModifier(  $u->userId)
                    ->setDescription('Created treatment ' . $nt->getCode())
                ;
                $em->persist($tlog);



                $em->flush();
            } catch (OptimisticLockException $e) {
                echo ($e->getMessage());
                $response->withStatus(500, 'OptimisticLockException saving treatment');
            }

            return $response->withJson($this->success());
        } catch (\Exception $e) {
            $response->withStatus(500, 'Error saving treatment');
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
    public function updateTreatment($request, $response, $args) {
        $code = $args['code'];
        $body = $request->getParsedBody();
        $ownerId = $this->getOwnerId($request);

        $em = $this->getEmPrivacy($ownerId);

        try {
            $nt = $em->find(Treatment::class, $code);
            /** @var Treatment $oldNt */
            $oldNt = clone $nt;
            $nt
                ->setName($this->getAttribute('name',$body, true))
                ->setNote($this->getAttribute('note',$body))
            ;

            $em->merge($nt);

            $u = $this->getUserData($request);
            $tlog = new TreatmentHistory();
            $tlog
                ->setCreated(new DateTime())
                ->setType('treatment_update')
                ->setTreatment( $this->toJson($oldNt))
                ->setTreatmentCode( $oldNt->getCode())
                ->setModifier(  $u->userId)
                ->setDescription('Modified treatment ' . $oldNt->getCode())
            ;
            $em->persist($tlog);

            $em->flush();

            return $response->withJson($this->success( ));
        } catch (\Exception $e) {
            $response->withStatus(500, 'Error saving treatment');
        }
    }

}
