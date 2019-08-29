<?php

namespace App\Action\Import;

use App\Action\AbstractAction;
use App\Service\AdvancedImporter as AdvancedImporterService;

class AdvancedImporter extends AbstractAction {

    /**
     * @param \Slim\Http\Request $request
     * @param \Slim\Http\Response $response
     * @param array $args
     * @return mixed
     */
    public function preset(
        $request,
        $response,
        $args
    ) {

        // Get request params
        /** @var \Slim\Http\UploadedFile $csv */
        $csv = $request->getUploadedFiles()['file'];
        foreach (explode('&', $request->getParams()['myData']) as $param) {
            list($name, $value) = explode('=', $param);
            switch ($name) {
                case 'columnSeparator':
                    $columnSeparator = trim($value);
                    break;
            }
        }

        try {
            $service = new AdvancedImporterService();
            ini_set('max_execution_time', 60*60*24);
            $mapping = $service->preset(
                $columnSeparator,
                $csv->file
            );
        } catch (\Exception $e) {
            ob_clean();
            return $response->withStatus(
                500,
                str_replace(["\n", "\r"], ' ', $e->getMessage())
            );
        }
        ob_clean();

        return $response->withStatus(201)
            ->withHeader("Content-Type", "application/json")
            ->write(json_encode(['result' => $mapping]));
    }

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
        try {
            $service = new AdvancedImporterService();
            ini_set('max_execution_time', 60*60*24);
            ini_set('memory_limit', '4G');

            // Get fields separator
            $columnSeparator = ';';
            if (isset($request->getParams()['columnSeparator'])) {
                $columnSeparator = $request->getParams()['columnSeparator'];
            }

            $result = $service->import(
                $request->getParams(),
                $columnSeparator
            );
        } catch (\Exception $e) {
            ob_clean();
            return $response->withStatus(
                500,
                str_replace(["\n", "\r"], ' ', $e->getMessage())
            );
        }
        ob_clean();
        $jsonResult = json_encode($result);
        if (!$jsonResult) {
            return $response->withStatus(500)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode(['result' => json_last_error_msg()]));
        } else {
            return $response->withStatus(201)
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode(['result' => $result]));
        }

    }
}