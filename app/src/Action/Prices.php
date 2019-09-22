<?php

namespace App\Action;

use MicroDB\Database;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Prices
{
    /** @var Container */
    private $container;

    public function __construct( $container) {
        $this->container = $container;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function updatePrices(Request $request, Response $response, $args) {
        return $response->withJson(["result" => 'ok']);
    }


}
