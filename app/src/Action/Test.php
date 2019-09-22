<?php

namespace App\Action;

use MicroDB\Database;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Test
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
    public function welcome(Request $request, Response $response, $args) {

        /** @var Database $db */
        $db = $this->container->get('customers');
        $cust = $db->load('test');

        /** @var \Google_Client $gc */
        $gc = $this->container->get('gclient');

        $service = new \Google_Service_Sheets($gc);

        $spreadsheetId = '1ypdsXN9lrHJGZJZl3bA-IKmNdL2xkmLIN0zI0OMief8';
        $range = 'invoices!A2:E';

        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        $values = $response->getValues();

        return $response->withJson(["result" => $cust['title']]);
    }


}
