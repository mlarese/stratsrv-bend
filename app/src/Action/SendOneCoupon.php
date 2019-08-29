<?php
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 02/05/18
 * Time: 14.45
 */

namespace App\Action;

use App\Helpers\EmailUtils;
use GuzzleHttp;
use App\Entity\Upgrade\SubscriberDomainPath;

class SendOneCoupon
{

    protected $url = 'http://servicehub.abs-one.com';

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



            $bodyHtml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/emailtemplates/' . $this->config->path . '/' . $param->getLanguage() . '/template.php');

            if(!$bodyHtml || empty($bodyHtml)){
                $bodyHtml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/emailtemplates/' . $this->config->path . '/en/template.php');
            }
            elseif(!$bodyHtml || empty($bodyHtml)){
                $bodyHtml = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/emailtemplates/' . $this->config->path . '/it/template.php');
            }

            if($this->parameters && is_array($this->parameters)){
                $bodyHtml = EmailUtils::makeGlobalReplace($this->parameters,$bodyHtml);
            }

            $json = new \stdClass();
            $json->from = $this->config->from;
            $json->to = $param->getEmail();
            $json->ccn = '';
            $json->subject = $this->config->subject;

            $json->body = $bodyHtml;
            $json->sender = $this->config->sender;

            $res = $client->request('POST', '/ecommerce/send-email',
                [
                    'allow_redirects' => false,
                    'headers' => [
                    'Accept-Encoding'=>'gzip',
                    'Accept'=>'application/json',
                    'Content-Type'=>'application/json',
                    'Expect'=>'',
                    ],
                    'body' => json_encode($json)
            ]);




            if($res->getBody()){
              $body = $res->getBody()->getContents();

            }


        }
        catch (\Exception $e){


        }



    }
}