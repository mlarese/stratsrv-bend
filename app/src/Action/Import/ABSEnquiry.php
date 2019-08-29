<?php

namespace App\Action\Import;

use App\Action\AbstractAction;
use App\Service\ABSImport;

class ABSEnquiry extends AbstractAction {

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
        /** @var \Slim\Http\UploadedFile $csv */
        $csv = $request->getUploadedFiles()['file'];
        foreach (explode('&', $request->getParams()['myData']) as $param) {
            list($name, $value) = explode('=', $param);
            switch ($name) {
                case 'ownerId':
                    $ownerId = $value;
                    break;
                case 'termId':
                    $termId = $value;
                    break;
                case 'enquiryUrl':
                    $enquiryUrl = rawurldecode($value);
                    break;
            }
        }

        try {
            $service = new ABSImport();
            ini_set('max_execution_time', 60*60*24);
            $service->importEnquiry(
                $ownerId,
                $termId,
                $enquiryUrl,
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
            ->write(json_encode(['result' => 'welcome']));
    }
}