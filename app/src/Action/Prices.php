<?php

namespace App\Action;

use function explode;
use MicroDB\Database;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;

class Prices
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
    public function updatePricesDispo(Request $request, Response $response, $args) {
        $body = $request->getParsedBody();
        $this->updatePricesDispoConfig = [];

        $ua = $body['ua'];
        $this->updatePricesDispoUaByKey =  array_combine($ua,$ua);
        $data = $body['data'];
        $header = array_filter($body['data'][0]);

        $rowsCounter = 0;
        $validHeaders = $this->updatePricesDispoCreateValidHeaders($this->updatePricesDispoUaByKey);
        $elaboratedData = [];

        foreach($data as $row) {
            if($row[0] === 'Import') continue;
            $elaboratedRow = $this->updatePricesDispoElaborateRow($header,$validHeaders, $row);
            $elaboratedData[] = $elaboratedRow;
        }

        $autentication = $body['config'];
        $this->updatePricesDispoSetup ($elaboratedData, $autentication);

        print_r($this->updatePricesDispoData);
die;

        return $response->withJson(["result" => 'ok', 'desc'=>$body['description']]);
    }

    public function updatePricesDispoCreateValidHeaders($ua) {
        $validHeaders = [];

        foreach ($ua as $key=>$value) {
            $validHeaders[$value] = ["type"=>"a","code"=>$value];

            $validHeaders["${value}-ms"] = ["type"=>"ms","code"=>$value];

            $validHeaders["${value}-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"BB"];
            $validHeaders["${value}HB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"HB"];
            $validHeaders["${value}FB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"BB"];

            $validHeaders["${value}1-prez"] = ["type"=>"p","code"=>$value];
            $validHeaders["${value}1HB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"HB"];
            $validHeaders["${value}1FB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"BB"];

            $validHeaders["${value}2-prez"] = ["type"=>"p","code"=>$value];
            $validHeaders["${value}2HB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"HB"];
            $validHeaders["${value}2FB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"BB"];

            $validHeaders["${value}3-prez"] = ["type"=>"p","code"=>$value];
            $validHeaders["${value}3HB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"HB"];
            $validHeaders["${value}3FB-prez"] = ["type"=>"p","code"=>$value,"treatment"=>"BB"];
        }

        return $validHeaders;
    }
    public function updatePricesDispoElaborateRow($header,$validHeaders, $row) {
        $adate = explode('T',$row[1]);
        $date = $adate[0] ;
        $elaboratedRow = [
            "date"=>$date,
            "avail"=>[],
            "prices"=>[]
        ];

        foreach($row as $k => $value ) {
            if($k<=2) continue;

            if(isset($header[$k])) {
                $code = $header[$k];
                // echo ($code.' ');
            }


        }

        return $elaboratedRow;
    }

    function updatePricesDispoSetup($data, $autentication) {

        $this->updatePricesDispoData = [
            "PriceRetrieval"=>[],
            "InventoryUpdate"=>[]
        ];

        $prices = [];

        if(isset($data['prices']))
            $prices = $data['prices'];

        $avail = [];

        if(isset($data['avail']))
            $avail = $data['avail'];

        $this->updatePricesDispoData['PriceRetrieval'] = [
            "autentication" => $autentication,
            "dates" => $prices
        ];

        $this->updatePricesDispoData['InventoryUpdate'] = [
            "autentication" => $autentication,
            "dates" => $avail
        ];

    }



}
