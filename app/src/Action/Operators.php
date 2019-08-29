<?php

namespace App\Action;

use App\Entity\Config\User;
use App\Entity\Privacy\Operator;
use App\Resource\MandatoryFieldMissingException;
use App\Resource\OperatorResource;
use App\Resource\UserExistException;
use App\Resource\UserResource;
use DateTime;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\DBAL\ConnectionException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use function is_string;
use Slim\Http\Request;
use Slim\Http\Response;

class Operators extends AbstractAction
{
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getAllOperators($request, $response, $args) {
        $ownerId = $this->getOwnerId($request);

        $term = null;
        try {
            /**
             * @var EntityManager $em
             */
            $em = $this->getEmPrivacy($ownerId);
            $term =  $em->getRepository( Operator::class)->findAll();
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
     */
    public function getOperator($request, $response, $args) {

        try {
            $ownerId = $this->getOwnerId($request);
            $id = $args['id'];
            /**
             * @var EntityManager $em
             */
            $em = $this->getEmPrivacy($ownerId);
            $term = $em->find(Operator::class, $id);
            $js = $this->toJson($term);
            return $response->withJson($js);
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error loading operators');
        }
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     */
    public function updateOperator($request, $response, $args) {

        try {

            $ownerId = $this->getOwnerId($request);
            $id = $args['id'];
            $body = $request->getParsedBody();
            $prEm = $this->getEmPrivacy($ownerId);
            $operRes = new OperatorResource($prEm);

            $periodFrom =  $this->toDateTime( $this->getAttribute('periodFrom', $body));
            $periodTo = $this->toDateTime( $this->getAttribute('periodTo', $body));

            $operRes->update(
                $id,
                $this->getAttribute('email', $body, true),
                $this->getAttribute('role', $body, true),
                $this->getAttribute('name', $body, true),
                $this->getAttribute('surname', $body, true),
                $periodFrom,
                $periodTo,
                $this->getAttribute('telephone', $body),
                $this->getAttribute('zip', $body),
                $this->getAttribute('city', $body),
                $this->getAttribute('address', $body),
                $this->getAttribute('domains', $body),
                $this->getAttribute('active', $body)
            );
        } catch (MandatoryFieldMissingException $e) {
            return $response->withStatus(500, $e->getMessage());
        } catch (OptimisticLockException $e) {
            return $response->withStatus(500, $e->getMessage());
        } catch (TransactionRequiredException $e) {
            return $response->withStatus(500, $e->getMessage());
        } catch (ORMException $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error updating');
        }catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error generic');
        }

        return $response->withJson($this->success());
    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     *
     * @return mixed
     * @throws \Doctrine\ORM\ORMException
     * @throws ConnectionException
     */
    public function newOperator($request, $response, $args) {
        $ownerId = $this->getOwnerId($request);
        $prEm = $this->getEmPrivacy($ownerId);
        $userRes = new UserResource($this->getEmConfig());
        $operRes = new OperatorResource($prEm);

        $body = $request->getParsedBody();

        $this->getEmConfig()->getConnection()->beginTransaction();
        $prEm->getConnection()->beginTransaction();

        // create operator
        try {

            $pass = $this->getAttribute('password', $body, true);
            $user = $userRes->insert(
                 $this->getAttribute('user', $body, true),
                md5($pass),
                 'owners',
                $ownerId,
                $this->getAttribute('name', $body, true) . ' ' . $this->getAttribute('surname', $body, true)
            );

            $operRes->insert(
                $user->getId(),
                $this->getAttribute('role', $body, true),
                $this->getAttribute('periodFrom', $body),
                $this->getAttribute('email', $body, true),
                $this->getAttribute('name', $body, true),
                $this->getAttribute('surname', $body, true),

                $this->getAttribute('periodTo', $body),
                $this->getAttribute('telephone', $body),
                $this->getAttribute('zip', $body),
                $this->getAttribute('city', $body),
                $this->getAttribute('address', $body),
                $this->getAttribute('domains', $body),
                $this->getAttribute('active', $body)
            );

        } catch (MandatoryFieldMissingException $e) {
            $this->getEmConfig()->getConnection()->rollBack();
            $prEm->getConnection()->rollBack();
            return $response->withStatus(500, $e->getMessage());
        } catch (UserExistException $e) {
            $this->getEmConfig()->getConnection()->rollBack();
            $prEm->getConnection()->rollBack();
            return $response->withStatus(500, $e->getMessage());
        } catch (OptimisticLockException $e) {
            $this->getEmConfig()->getConnection()->rollBack();
            $prEm->getConnection()->rollBack();
            return $response->withStatus(500, 'Error creating user');
        } catch (Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error creating user - check response' );
        }

        $this->getEmConfig()->getConnection()->commit();
        $prEm->getConnection()->commit();

        return $response->withJson($this->success());

    }
}
