<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 9/21/18
 * Time: 4:12 PM
 */

namespace App\Action;


use App\Entity\Config\Module;
use Slim\Http\Response;
use Slim\Http\Request;

class Modules extends AbstractAction
{
    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getAllModules(Request $request, Response $response, $args) {
        try {
            $em =$this->getEmConfig();
            $rep =$em->getRepository(Module::class);
            $module = $rep->findAll();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting module');
        }
        return $response->withJson($this->toJson($module));
    }

    public function getModule(Request $request, Response $response, $args) {
        try {

            $id = $args['id'];
            $module = $this->getEmConfig();
            $module->getRepository(Module::class)->find($id);


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting module');
        }
        return $response->withJson( $this->toJson($module));
    }

    public function updateModule(Request $request, Response $response, $args) {
        try {
            $id = $args['id'];
            /**
             * @var Module $module
             */
            $module = $this->getEmConfig()->find(Module::class, $id);
            $body = $request->getParsedBody();
            $module->setName($body['name']);
            $module->setOption($body['option']);
            $module->setDescription($body['description']);
            $module->setDeleted($body['deleted']);
            $module->setOwnerId($body['ownerId']);

            $this->getEmConfig()->persist($module);
            $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting module');
        }
        return $response->withJson($this->success());
    }

    public function insertModule(Request $request, Response $response, $args) {
        try {
            /**
             * @var Module $module
             */
            $module = new Module();
            $body = $request->getParsedBody();
            $module->setName($body['name']);
            $module->setOption($body['option']);
            $module->setDescription($body['description']);
            $module->setDeleted($body['deleted']);
            $module->setOwnerId($body['ownerId']);


            $this->getEmConfig()->persist($module);
            $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error inserting module');
        }
        return $response->withJson($this->success());
    }

    public function moduleDelete(Request $request, Response $response, $args) {
        try {
            $id = $args['id'];
            /**
             * @var Module $module
             */
             $module = $this->getEmConfig()->find(Module::class, $id);
             $this->getEmConfig()->remove($module);
             $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error module');
        }
        return $response->withJson($this->success());
    }

}
