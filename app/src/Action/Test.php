<?php

namespace App\Action;

use Slim\Http\Request;
use Slim\Http\Response;

class Test
{


    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function welcome(Request $request, Response $response, $args) {

        return $response->withJson(["result" => "welcome"]);
    }


}
