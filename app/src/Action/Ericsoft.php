<?php

namespace App\Action;

use function explode;
use function json_encode;
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

        $msgId="";
        $body = $request->getParsedBody();
        $strJs = $body['payload'];
        $body = json_decode($body['payload'],true);

        if(isset($body['messageId']))
            $msgId=$body['messageId'];

        $date =date('Y-m-d H:i:s');

        $logger = \Logger::getLogger("main");

        $logger->info("--- operationResultWithHotelCode [$date] ----- message=$msgId ope=$ope hotel=$hcode  -------------");

        $logger->info(["type"=>"test body","Body"=>$strJs]);


        $resp = [
            "messageId"=>$msgId,
            "status"=>[[ "code"=>200, "description"=>"success"]]
        ];

        $logger->info(json_encode(["type"=>"test body","Body"=>$strJs,"resp"=>$resp]));

        $jsResponse = $response->withJson($resp);

        return $jsResponse;
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function updatePricesDispo(Request $request, Response $response, $args) {
        $logger = \Logger::getLogger("main");
        $body = $request->getParsedBody();

        $doPrezzi = "1";
        if ( isset($body['doPrezzi'])) $doPrezzi = $body['doPrezzi'];

        $doDispo = "1";
        if ( isset($body['doDispo'])) $doDispo = $body['doDispo'];

        $uaByKey = $body['ua'];
        $uaByKey=json_decode($uaByKey,true);


        $body['data'] = json_decode( $body['data'], true);
        $body['headers'] = json_decode( $body['headers'], true);
        $body['config'] = json_decode( $body['config'], true);

        $data=$body['data'];

        //commenta
        // $strJs = json_encode($body);

        //$logger->info("----- prezzi  -------------");

        //commenta
        // $logger->info($strJs);die('end');

        $headerRow = array_filter($body['headers'][0]);
        $autentication = $body['config'];


        //$logger->info(json_encode($uaByKey));


        $headerConfig = $this->updatePricesDispoCreateValidHeaders($uaByKey);

        //$logger->info(json_encode($uaByKey));
        //$logger->info(json_encode($headerConfig));


        $inventory = ["autentication"=>$autentication, "dates"=>[]];
        $prices = ["autentication"=>$autentication, "dates"=>[]];
//

        foreach($data as $row) {
            if($row[0] === 'Import') continue;


            $adate = explode('T',$row[1]);
            $rowDate =  $adate[0] ;

            try {
                $rowDate = date_create($rowDate);
                date_add($rowDate, date_interval_create_from_date_string('1 days'));
                $rowDate=$rowDate->format('Y-m-d');
            } catch (\Exception $e) {
                $logger->info('error ' .$e->getMessage());
            }


            $rowPrice = ["date"=>$rowDate,"rooms"=>[]];
            $rowInventory = ["date"=>$rowDate, "rooms"=>[]];

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

                            $curRoom['rateCode']= 'BB';
                            if(isset($colConfig["treatment"]))
                                $curRoom['rateCode']= $colConfig["treatment"];
                            $curRoom['price']= round($col);

                            // $logger->info($row[1] . ' - ' . $rowDate . ' - '. $curRoom['price']);

                            $curRoom['minStay']= 0;
                            $curRoom['maxStay']= 0;
                            $curRoom['cta']= false;
                            $curRoom['ctd']= false;

                            $rowPrice['rooms'][]=$curRoom;

                        } else if($colConfig["type"]=='a') {




                            $curRoomType = [];
                            $curRoomType['roomTypeCode'] = $colCode;
                            $curRoomType['quantity'] = $col;

                            $rowInventory['rooms'][]=$curRoomType;
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

        $strPrices = json_encode($prices);
        $strInv = json_encode($inventory);
        $logger->info("---------inv---------");
        $logger->info($strInv);
        $logger->info("----------prz--------");
        $logger->info($strPrices);
        // return $response->withJson(["result" => 'ok']); die;

        if($doDispo === "1") {
            $logger->info( '---- do dispo');
            $rpd = $this->postData(self::ERICSOFT_INVENTORY_URL,$strInv);
            //$logger->info(json_encode($rpd));
        }

        if($doPrezzi === "1") {
            $logger->info( '---- do prezzi');
            $rpp = $this->postData(self::ERICSOFT_PRICES_URL, $strPrices);
            // $logger->info(json_encode($rpp));
        }


        // $logger->info('------------------');

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

           // $validHeaders["${key}1-prez"] = ["type"=>"p","code"=>$key];
           // $validHeaders["${key}1HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
           // $validHeaders["${key}1FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];

           // $validHeaders["${key}2-prez"] = ["type"=>"p","code"=>$key];
           // $validHeaders["${key}2HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
           // $validHeaders["${key}2FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];

           // $validHeaders["${key}3-prez"] = ["type"=>"p","code"=>$key];
           // $validHeaders["${key}3HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
           // $validHeaders["${key}3FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];

           // $validHeaders["${key}4-prez"] = ["type"=>"p","code"=>$key];
           // $validHeaders["${key}4HB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"HB"];
           // $validHeaders["${key}4FB-prez"] = ["type"=>"p","code"=>$key,"treatment"=>"BB"];
        }

        return $validHeaders;
    }




}
