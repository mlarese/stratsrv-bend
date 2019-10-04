<?php

namespace App\Action;

use function explode;
use MicroDB\Database;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Ericsoft
{
    /** @var Container */
    private $container;
    private $updatePricesDispoConfig;
    private $updatePricesDispoData;
    private $updatePricesDispoUaByKey;



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
    public function operationResult(Request $request, Response $response, $args) {

        $body = $request->getParsedBody();
        $ope = "";

        $logger = \Logger::getLogger("main");
        $strJs = json_encode($body);
        $logger->info("----- operationResult $ope  -------------");

        $logger->info($strJs);


        $resp = [
            "status"=>[ "code"=>"200", "description"=>"success"]
        ];


        return $response->withJson($resp);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function operationResultWithHotelCode(Request $request, Response $response, $args) {

        $ope = $args['type'];
        $hcode = $args['hcode'];

        $body = $request->getParsedBody();

        $date =date('Y-m-d H:i:s');

        $logger = \Logger::getLogger("main");
        $strJs = json_encode($body);
        $logger->info("[$date] ----- $ope hotel $hcode  -------------");

        $logger->info($strJs);


        $resp = [
            "status"=>[ "timestamp"=>$date, "code"=>"200", "description"=>"success $ope $hcode"]
        ];


        return $response->withJson($resp);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function updatePricesDispo(Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $uaByKey = $body['ua'];

        $data = $body['data'];
        $headerRow = array_filter($body['data'][0]);
        $autentication = $body['config'];

        $headerConfig = $this->updatePricesDispoCreateValidHeaders($uaByKey);
        $inventory = ["autentication"=>$autentication, "dates"=>[]];
        $prices = ["autentication"=>$autentication, "dates"=>[]];


        foreach($data as $row) {
            if($row[0] === 'Import') continue;

            $adate = explode('T',$row[1]);
            $rowDate = $adate[0] ;

            $rowPrice = ["date"=>$rowDate,"rooms"=>[]];
            $rowInventory = ["date"=>$rowDate, "roomTypes"=>[]];

            foreach($row as $i => $col) {
                if(isset($headerRow[$i])) {
                    $headerCode = $headerRow[$i];

                    if(isset($headerConfig[$headerCode])) {
                        $colConfig = $headerConfig[$headerCode];
                        $colCode = $colConfig["code"];

                        // [type] => p    [code] => FAM   [treatment] => BB
                        if($colConfig["type"]=='p') {

                            $curRoom = [];
                            $curRoom['roomTypeCode']= $colCode;
                            $curRoom['rateCode']= $colConfig["treatment"];
                            $curRoom['price']= $col;
                            $curRoom['minStay']= 0;
                            $curRoom['maxStay']= 0;
                            $curRoom['cta']= false;
                            $curRoom['ctd']= false;

                            $rowPrice['rooms'][]=$curRoom;

                        } else if($colConfig["type"]=='a') {
                            $curRoomType = [];
                            $curRoomType['roomType'] = $colCode;
                            $curRoomType['roomTypeGroupCode'] = $colConfig["code"];
                            $curRoomType['physical'] = $uaByKey[$colCode]["physical"];
                            $curRoomType['availability'] = $col;
                            $curRoomType['currency'] = "EUR";
                            $curRoomType['total'] = 0;
                            $curRoomType['portalMaxAvailability'] = 0;
                            $curRoomType['occupancyPercentage'] = 0;
                            $curRoomType['sold'] = 0;
                            $curRoomType['outOfInventory'] = 0;
                            $curRoomType['outOfOrder'] = 0;

                            $rowInventory['roomTypes'][]=$curRoomType;
                        }

                    }

                }
                // print_r($rowPrice);

            }
            $inventory['dates'][] = $rowInventory;
            $prices['dates'][] = $rowPrice;
            // print_r($rowPrice);
        }

        //print_r($inventory);
        //print_r($prices);

        $logger = \Logger::getLogger("main");
        $strJs = json_encode($prices);
        $logger->info("----- prezzi  -------------");

        $logger->info($strJs);

        $this->postData(self::ERICSOFT_PRICES_URL,  json_encode($prices));
        // $this->postData(self::ERICSOFT_INVENTORY_URL,json_encode($inventory));

        return $response->withJson(["result" => 'ok']);
    }

    const ERICSOFT_PRICES_URL = "https://webservices.ericsoft.com/API/Revenue/Provider/Prices";
    const ERICSOFT_INVENTORY_URL = "https://webservices.ericsoft.com/API/Revenue/Provider/Availabilities";

    private function postData($url, $json) {

        $ch = curl_init($url);
        $payload = ['payload'=>$json];

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return ['result'=>$result,'info'=>$info];


    }
    public function updatePricesDispoCreateValidHeaders($ua) {
        $validHeaders = [];

        foreach ($ua as $key=>$value) {
            $validHeaders[$key] = ["type"=>"a","code"=>$key];

            $validHeaders["${key}-ms"] = ["type"=>"ms","code"=>$key];

            $validHeaders["${key}-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];
            $validHeaders["${key}HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
            $validHeaders["${key}FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];

            $validHeaders["${key}1-prez"] = ["type"=>"p","code"=>$key];
            $validHeaders["${key}1HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
            $validHeaders["${key}1FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];

            $validHeaders["${key}2-prez"] = ["type"=>"p","code"=>$key];
            $validHeaders["${key}2HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
            $validHeaders["${key}2FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];

            $validHeaders["${key}3-prez"] = ["type"=>"p","code"=>$key];
            $validHeaders["${key}3HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
            $validHeaders["${key}3FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];
        }

        return $validHeaders;
    }




}
