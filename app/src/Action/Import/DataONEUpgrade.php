<?php

namespace App\Action\Import;

use App\Action\AbstractAction;
use App\Service\DataOneUpgrade AS DataOneUpgradeService;

class DataONEUpgrade extends AbstractAction {

    /**
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param array $args
     * @return mixed
     */
    public function import (
        $request,
        $response,
        $args
    ) {

        // Get request params
        foreach ($request->getParams() as $param) {
            switch ($param['name']) {
                case 'ownerId':
                    $ownerId = $param['value'];
                    break;
                case 'termId':
                    $termId = $param['value'];
                    break;
                case 'host':
                    $hostname = $param['value'];
                    break;
                case 'dbname':
                    $dbName = $param['value'];
                    break;
                case 'user':
                    $username = $param['value'];
                    break;
                case 'password':
                    $password = $param['value'];
                    break;
                case 'domain':
                    $domain = $param['value'];
                    break;
            }
        }

        try {
            $service = new DataOneUpgradeService();
            ini_set('max_execution_time', 60*60*24);
            $service->import(
                $ownerId,
                $termId,
                $hostname,
                $dbName,
                $username,
                $password,
                $domain
            );
        } catch (\Exception $e) {
            ob_clean();
            return $response->withStatus(
                500,
                $e->getMessage()
            );
        }
        ob_clean();

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode(['result' => 'welcome']));
    }
}