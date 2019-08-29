<?php
/**
 * Created by PhpStorm.
 * User: mac
 * Date: 9/21/18
 * Time: 7:00 PM
 */

namespace App\Action;


use App\Entity\Config\Module;
use App\Entity\Config\Widget;
use Slim\Http\Response;
use Slim\Http\Request;

class Widgets extends AbstractAction
{

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getAllWidgets(Request $request, Response $response, $args) {
        try {
            $em =$this->getEmConfig();
            $rep =$em->getRepository(Widget::class);
            $widget = $rep->findAll();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting widget');
        }
        return $response->withJson($this->toJson($widget));
    }

    public function getWidget(Request $request, Response $response, $args) {
        try {

            $id = $args['id'];
            $widget = $this->getEmConfig();
            $widget->getRepository(Widget::class)->find($id);


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting widget');
        }
        return $response->withJson( $this->toJson($widget));
    }

    public function updateWidget(Request $request, Response $response, $args) {
        try {
            $id = $args['id'];
            /**
             * @var Widget $widget
             */
            $widget = $this->getEmConfig()->find(Widget::class, $id);
            $body = $request->getParsedBody();
            $widget->setName($body['name']);
            $widget->setConfiguration($body['configuration']);
            $widget->setDescription($body['description']);
            $widget->setDeleted($body['deleted']);
            $widget->setId($body['id']);

            $this->getEmConfig()->persist($widget);
            $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting widget');
        }
        return $response->withJson($this->success());
    }

    public function insertWidget(Request $request, Response $response, $args) {
        try {
            /**
             * @var Widget $widget
             */
            $widget = new Widget();
            $body = $request->getParsedBody();
            $widget->setName($body['name']);
            $widget->setConfiguration($body['configuration']);
            $widget->setDescription($body['description']);
            $widget->setDeleted($body['deleted']);
            $widget->setId($body['id']);

            $this->getEmConfig()->persist($widget);
            $this->getEmConfig()->flush();

        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error inserting widget');
        }
        return $response->withJson($this->success());
    }

    public function widgetDelete(Request $request, Response $response, $args) {
        try {

            $id = $args['id'];
            /**
             * @var Widget $widget
             */
            $widget = $this->getEmConfig()->find(Widget::class, $id);
            $this->getEmConfig()->remove($widget);
            $this->getEmConfig()->flush();


        } catch (\Exception $e) {
            echo $e->getMessage();
            return $response->withStatus(500, 'Error getting widget');
        }
        return $response->withJson($this->success());
    }

}
