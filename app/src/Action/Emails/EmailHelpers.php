<?php
/**
 * Created by PhpStorm.
 * User: mauroadmin
 * Date: 26/08/18
 * Time: 16:13
 */

namespace App\Action\Emails;


use function fclose;
use function fwrite;
use GuzzleHttp\Client;
use function print_r;
use Slim\Container;
use function str_replace;
use function stream_get_meta_data;
use function tmpfile;

trait EmailHelpers
{
    public function buildGuzzleData ($from, $to, $subject, $body, $sender = 'dataone') {

        return [
            'allow_redirects' => false,
            'headers' => [
                'Accept-Encoding' => 'gzip',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'sender' => $sender,
                'from' => $from,
                'to' =>$to,
                'subject' => $subject,
                'body' => $body
            ]
        ];
    }

    public function getCallCenterEmail(Container $container) {
        $s = $container->get('settings');
        return $s['dataone_emails']['_options_']['callcenter_email'];
    }

    public function getFrontEndServer(Container $container, $env) {
        $s = $container->get('settings');
        return $s['dataone_emails']['_options_'][$env]['fe_address'];
    }

    public function extractLanguage($dictionary, $language, $defaultLanguage = 'en') {
        if(isset($dictionary[$language])) {
            return $dictionary[$language];
        }

        if(isset($dictionary[$defaultLanguage])) {
            return $dictionary[$defaultLanguage];
        }
        return $dictionary['it'];
    }
    public function sendGenericEmail(
        Container $container,
        array $templateData,
        string $templateName,
        string $language,
        string $from,
        string $to,
        $settingProp = 'dataone_emails',
		string $subject=null
    ) {
        /** @var Client $client */
        $client = $container['email_client'];


        /** @var PlainTemplateBuilder $bld */
        $bld=$container->get('template_builder');
            $bld->setTemplateName($templateName);
            $body=$bld->render($templateData, $language);


        // get subject from settings
        $settings =  $container->get('settings');
        $templateSettings = $settings[$settingProp][$templateName];
        $aEmailSubject =$templateSettings['all']['dictionary']['email_subject'];

        if (!isset($subject) || empty($subject)) {
	        $subject = $this->extractLanguage($aEmailSubject,$language);
        }

        $data = $this->buildGuzzleData($from,$to, $subject,$body  ) ;
        $client->request('POST', '', $data);
        return $body;
    }

    public function sendGenericEmailHtml(
        Container $container,
        array $templateData,
        string $templateName,
        string $language,
        string $from,
        string $to,
        $settingProp = 'dataone_emails',
        string $subject=null,
        string $html=null
    ) {
        /** @var Client $client */
        $client = $container['email_client'];


        /** @var PlainTemplateBuilder $bld */
        $bld=$container->get('template_builder');
        $bld->setTemplateName($templateName);
        $body=$bld->renderHtml($templateData, $html);

        // get subject from settings
        $settings =  $container->get('settings');
        $templateSettings = $settings[$settingProp][$templateName];
        $aEmailSubject =$templateSettings['all']['dictionary']['email_subject'];

        if (!isset($subject) || empty($subject)) {
            $subject = $this->extractLanguage($aEmailSubject,$language);
        }

        $data = $this->buildGuzzleData($from,$to, $subject,$body  ) ;
        $client->request('POST', '', $data);
        return $body;
    }
}
