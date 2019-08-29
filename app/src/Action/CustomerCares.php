<?php

namespace App\Action;

use App\Entity\Config\ActionHistory;
use App\Entity\Config\CustomerCare;
use App\Entity\Config\Owner;
use App\Entity\Config\User;
use App\Entity\Config\UserLogin;
use App\Entity\Privacy\Treatment;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Exception;
use Slim\Http\Request;
use Slim\Http\Response;

class CustomerCares extends AbstractAction
{
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getDblOptinList($request, $response, $args)
    {


            return $response->withStatus(500, 'Not implemented');


    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function postLoginLogs($request, $response, $args)
    {
        $em = $this->getEmConfig();
        $rep = $em->getRepository( UserLogin::class);

        $body = $request->getParsedBody();

        $dateFrom = $body['date_range'][0];
        $dateTo = $body['date_range'][1];

        $sql = "
        SELECT user,name,user.type,user_login.id, user_login.loginDate, user_login.ip_address, user_login.user_id 
        FROM privacy_config.user_login
        LEFT JOIN user on user_login.user_id = user.id 
        
        WHERE loginDate BETWEEN '$dateFrom' AND '$dateTo'
        
        ORDER BY loginDate desc
        ;
        
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('type', 'type', 'string');
        $rsm->addScalarResult('user', 'user', 'string');
        $rsm->addScalarResult('loginDate', 'loginDate', 'datetime');
        $rsm->addScalarResult('ip_address', 'ip_address', 'string');



        $query = $em->createNativeQuery($sql, $rsm);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $response->withJson($this->toJson($result));

    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getLoginLogs($request, $response, $args)
    {
        $em = $this->getEmConfig();
        $rep = $em->getRepository( UserLogin::class);

        $sql = "
        SELECT user,name,user.type,user_login.id, user_login.loginDate, user_login.ip_address, user_login.user_id 
        FROM privacy_config.user_login
        LEFT JOIN user on user_login.user_id = user.id 
        ORDER BY loginDate desc
        ;
        
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('name', 'name', 'string');
        $rsm->addScalarResult('type', 'type', 'string');
        $rsm->addScalarResult('user', 'user', 'string');
        $rsm->addScalarResult('loginDate', 'loginDate', 'datetime');
        $rsm->addScalarResult('ip_address', 'ip_address', 'string');



        $query = $em->createNativeQuery($sql, $rsm);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $response->withJson($this->toJson($result));

    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getActionHistory($request, $response, $args)
    {
        $em = $this->getEmConfig();
        $rep = $em->getRepository( ActionHistory::class);

        $sql = "
        SELECT id,  type,  description,  `date`,   history,    user_name
        FROM privacy_config.action_history
        ORDER BY date desc ;
        ";

        $rsm = new ResultSetMapping();
            $rsm->addScalarResult('id', 'id');
            $rsm->addScalarResult('type', 'type', 'string');
            $rsm->addScalarResult('description', 'description', 'string');
            $rsm->addScalarResult('date', 'date', 'datetime');
            $rsm->addScalarResult('user_name', 'user_name', 'string');
            $rsm->addScalarResult('history', 'history', 'json');



        $query = $em->createNativeQuery($sql, $rsm);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $response->withJson($this->toJson($result));


    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function postActionHistory($request, $response, $args)
    {
        $em = $this->getEmConfig();
        $rep = $em->getRepository( ActionHistory::class);

        $body = $request->getParsedBody();

        $body = $request->getParsedBody();

        $dateFrom = $body['date_range'][0];
        $dateTo = $body['date_range'][1];

        $sql = "
            SELECT id,type, description, `date`, history, user_name
            FROM privacy_config.action_history
            WHERE date BETWEEN '$dateFrom' AND '$dateTo'
            ORDER BY date desc ;
        ";

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('type', 'type', 'string');
        $rsm->addScalarResult('description', 'description', 'string');
        $rsm->addScalarResult('date', 'date', 'datetime');
        $rsm->addScalarResult('user_name', 'user_name', 'string');
        $rsm->addScalarResult('history', 'history', 'json');



        $query = $em->createNativeQuery($sql, $rsm);


        try {
            $result = $query->getResult();
        } catch (Exception $e) {
            die($e->getMessage());
        }

        return $response->withJson($this->toJson($result));


    }
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getOperators($request, $response, $args)
    {

        try {
            $rp = $this->getEmConfig()->getRepository(CustomerCare::class);

            $ccs = $rp->findBy(['deleted'=>0, 'active'=>1]);

            return $response->withJson(  $this->toJson($ccs));
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getOperator($request, $response, $args)
    {

        try {
            $id = $args['id'];
            $ccs = $this->getEmConfig()->find(CustomerCare::class,$id);


            return $response->withJson(  $this->toJson($ccs));
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function newOperator($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();

            if(
                !isset($body['user']) ||
                !isset($body['password'])
            ) {
                return $response->withStatus(500, 'Missing parameters');
            }

            $user = new User();
            $user->setName('Operatore '. $body['firstName']. ' ' .$body['lastName'])
                ->setActive(true)
                ->setDeleted(false)
                ->setPassword(md5($body['password']))
                ->setUser($body['user'])
                ->setType('operators')
                ->setOwnerId(0);

            $this->getEmConfig()->persist($user);
            $this->getEmConfig()->flush();

            $cc = new CustomerCare();
            $cc->setDeleted(false);
            $cc->setName($body['user']);
            $cc->setEmail($body['email']);
            $cc->setFirstName($body['firstName']);
            $cc->setLastName($body['lastName']);
            $cc->setActive(true);
            $cc->setId($user->getId());

            $this->getEmConfig()->persist($cc);
            $this->getEmConfig()->flush();


            $ud = $this->getUserData($request);

            $ah=new ActionHistory();
            $ah->setDate(new \DateTime())
                ->setType('operator creation')
                ->setUserName($ud->user)
                ->setDescription('operator '.$user->getUser())
            ;

            $this->getEmConfig()->persist($ah);
            $this->getEmConfig()->flush();

            return $response->withJson(  $this->toJson($this->success()));
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function updateOperator($request, $response, $args)
    {

        try {

            $body = $request->getParsedBody();
            $histField = [];

            /** @var CustomerCare $cc */
            $cc = $this->getEmConfig()->find(CustomerCare::class, $args['id']);

            $histField ['before']=$cc;
            $cc->setDeleted($body['deleted']);
            // $cc->setName($body['user']);
            $cc->setEmail($body['email']);
            $cc->setFirstName($body['firstName']);
            $cc->setLastName($body['lastName']);
            $cc->setActive($body['active']);

            $this->getEmConfig()->merge($cc);
            $this->getEmConfig()->flush();

            $ud = $this->getUserData($request);

            $histField ['after']=$cc;

            $objcc = $this->toJson($histField) ;

            $ah=new ActionHistory();
            $ah->setDate(new \DateTime())
                ->setType('operator update')
                ->setUserName($ud->user)
                ->setHistory($objcc)
                ->setDescription('operator '. $cc->getName())
            ;

            $this->getEmConfig()->persist($ah);
            $this->getEmConfig()->flush();

            return $response->withJson(  $this->toJson($this->success()));
        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getWidgets($request, $response, $args)
    {

        try {

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function getOwners($request, $response, $args)
    {

        try {
            $em = $this->getEmConfig();
            $rep = $em->getRepository(Owner::class);
            $owners = $rep->findBy(['deleted'=>false, 'active'=>true]);

            return $response->withJson($this->success($owners));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function deactivate($request, $response, $args)
    {

        try {
            $id = $args['id'];
            $owners = $this->getEmConfig()->find(Owner::class,$id);

            $body = $request->getParsedBody();
            $owners->setActive($body[0]);


            $this->getEmConfig()->persist($owners);
            $this->getEmConfig()->flush();

            return $response->withJson(  $this->toJson($owners));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }

    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     * @return mixed
     */
    public function activate($request, $response, $args)
    {

        try {
            $id = $args['id'];
            $owners = $this->getEmConfig()->find(Owner::class,$id);

            $body = $request->getParsedBody();
            $owners->setActive($body[1]);


            $this->getEmConfig()->persist($owners);
            $this->getEmConfig()->flush();

            return $response->withJson(  $this->toJson($owners));

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Exception');
        }

    }
}
