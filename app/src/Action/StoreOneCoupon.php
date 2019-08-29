<?php
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 02/05/18
 * Time: 14.45
 */

namespace App\Action;

use GuzzleHttp;
use App\Entity\Upgrade\SubscriberDomainPath;

class StoreOneCoupon
{

    protected $url = 'http://reservation.abs.tmp';

    protected $config = null;

    protected $parameters = null;
    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param null $config
     */
    public function setConfig($config): void
    {
        $this->config = $config;
    }

    /**
     * @param null $config
     */
    public function setParameters($config): void
    {
        $this->parameters = $config;
    }

    /**
     * @param $param SubscriberDomainPath
     */
    public function execute($param){

        if($this->config && $this->config!=''){
            $this->config = json_decode($this->config);
        }

        
        $client = new GuzzleHttp\Client(['base_uri' => $this->url]);

        try {

            $date = new \DateTime();
            if(property_exists($this->config,'validityYear')){
                $date->add(new \DateInterval("P" . $this->config->validityYear . "Y"));
            }
            if(property_exists($this->config,'validityMonth')){
                $date->add(new \DateInterval("P" . $this->config->validityMonth . "M"));
            }

            $res = $client->request('POST', '/RESTfulAPI/coupon',
                [
                    'allow_redirects' => false,
                    'headers' => [
                    'Accept-Encoding'=>'gzip',
                    'Accept'=>'application/json',
                    'Content-Type'=>'application/json',
                    'ShopId'=>$this->config->shopId,
                    'UserLanguageCode'=>$param->getLanguage(),
                    ] , 'json' => [
                        'promo' => $this->config->discountName,
                        'validity' =>$date->format('Y-m-d'),
                        'email' => $param->getEmail()
            ]]);




            if($res->getBody()){
              $body = $res->getBody()->getContents();

            }


        }
        catch (\Exception $e){

        }


        die;
    }
}