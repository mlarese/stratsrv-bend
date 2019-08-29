<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 23/05/2018
 * Time: 18:31
 */

namespace App\Action;


use Slim\Http\Request;
use Slim\Http\Response;

class ActionSetter {
    /**
     * @param $request Request
     * @param $response Response
     * @param $args
     */
    public function setResponse($request, $response, $args) {
        header('P3P: CP="ALL IND DSP COR ADM CONo CUR CUSo IVAo IVDo PSA PSD TAI TELo OUR SAMo CNT COM INT NAV ONL PHY PRE PUR UNI"');
        header("Access-Control-Allow-Headers: ref, language, termid, content-type, authorization, if-match, if-unmodified-since, token, ownerid, domain, page");
    }
}
