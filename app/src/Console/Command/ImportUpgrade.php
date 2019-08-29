<?php

namespace Console\Command;

use App\Entity\Upgrade\SubscriberDomainPath;
use App\Resource\MailOneDirectExport;
use App\Resource\PrivacyResource;
use Doctrine\ORM\EntityManager;
use Console\Command\Base;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Console\Helper\Log as LogHelper;
use App\DoctrineEncrypt\Configuration\Encrypted;


require realpath(__DIR__.'/../../DoctrineEncrypt/Configuration/Encrypted.php');

class ImportUpgrade extends Base
{
    protected function configure()
    {
        $this->setName('import:privacy')
            ->setDescription('Import data from a domain')
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'select the domain'
            )->addArgument(
                'owner',
                InputArgument::REQUIRED,
                'select the destination owner'
            )->addArgument(
                'termId',
                InputArgument::REQUIRED,
                'Term Id'
            )->addArgument(
                'hostname',
                InputArgument::REQUIRED,
                'Db Hostname'
            )->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Db Username'
            )->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Db Password'
            )->addArgument(
                'dbname',
                InputArgument::REQUIRED,
                'Db Name'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $configFile = realpath(__DIR__ . '/../../../settings.php');

        $settings = require $configFile;


        $upgradedomain = $input->getArgument('domain');

        $ownerdomain = $input->getArgument('owner');

        $termId = $input->getArgument('termId');

        $hostName = $input->getArgument('hostname');
        $userName = $input->getArgument('username');
        $password = $input->getArgument('password');
        $dbName = $input->getArgument('dbname');

        if ($ownerdomain > 0 && $upgradedomain > 0 && $termId != '') {
            /**
             * @var $upgrade EntityManager
             */
            $upgrade = $this->getUpgradeDb($settings['settings']);

            $repository = $upgrade->getRepository(SubscriberDomainPath::class)->findBy(array('domainpath' => $upgradedomain, 'status' => 1));

            $emailList = array();
            /**
             * @var $value SubscriberDomainPath
             */
            foreach ($repository as $value) {
                try {
                    $json = json_decode($value->getDomainpath()->getPath(), true);
                    if (!$json) {
                        $json = $value->getDomainpath()->getPath();
                    } else {
                        $lg = $value->getLanguage();
                        $json = $json[$lg];
                    }
                } catch (\Exception $e) {
                    $json = $value->getDomainpath()->getPath();
                }
                try {
                    $jsonprivacy = json_decode($value->getPrivacydisclaimer()->getPrivacy(), true);
                    if (!$jsonprivacy) {
                        $jsonprivacy = $value->getPrivacydisclaimer()->getPrivacy();
                    } else {
                        $lg = $value->getLanguage();
                        $jsonprivacy = $jsonprivacy[$lg];
                    }
                } catch (\Exception $e) {
                    $jsonprivacy = $value->getPrivacydisclaimer()->getPrivacy();
                }


                $emailList[] = array('email' => $value->getEmail(), 'ip' => $value->getIp(), 'language' => $value->getLanguage(), 'url' => $json,'privacy'=>$jsonprivacy);

            }


            $repository = null;

            if ($emailList && !empty($emailList) ) {


                /**
                 * @var $config EntityManager
                 */
                $config = $this->getConfigDb($settings['settings']);


                $users = array();

                $objMailOne = new MailOneDirectExport();
                $objMailOne->setEntityManager($config);
                $objMailOne->setOwner($ownerdomain);

                $objMailOne->setAction("list");

                $config = new \Doctrine\DBAL\Configuration();

                $connectionParams = array(
                    'dbname' => $dbName,
                    'user' => $userName,
                    'password' => $password,
                    'host' => $hostName,
                    'driver' => 'pdo_mysql',
                );

                $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
                $conn->connect();

                $response = $objMailOne->export(-1);
                if ($response && is_array($response)) {
                    foreach ($emailList as $contact) {
                        $name = '';
                        $surname = '';
                        $phone = '';
                        $mobile = '';
                        $subscriberId = '';
                        $detail = array();

                        if($name == '' && $surname == '')
                        {
                            foreach ($response as $listVal) {
                                $query = 'SELECT * FROM email_list_subscribers 
                                          WHERE listid = '.$listVal['id'].' 
                                          AND emailaddress = \''.$contact['email'].'\' 
                                          AND unsubscribeconfirmed = 0';

                                $state = $conn->executeQuery($query);
                                $result = $state->fetchAll();
                                if(!empty($result)) {
                                    $subscriberId = $result[0]['subscriberid'];
                                    $queryData = "SELECT esd.*, ec.name as fieldName FROM email_subscribers_data as esd
                                            inner join email_list_subscribers as els on esd.subscriberid = els.subscriberid 
                                            inner join email_customfields as ec on ec.fieldid = esd.fieldid
                                            where els.subscriberid = ".$subscriberId." 
                                            and esd.data != '' 
                                            and ec.name in ('nome', 'name', 'cognome', 'surname', 'telefono', 'mobile', 'phone', 'telephone', 'cell phone'); ";

                                    $stateData = $conn->executeQuery($queryData);
                                    $resultData = $stateData->fetchAll();

                                    if($resultData && !empty($resultData))
                                    {
                                        foreach ($resultData as $subsciberData)
                                        {
                                            $subsciberData['fieldName'] = strtolower($subsciberData['fieldName']);
                                            if($subsciberData['fieldName'] == 'nome' || $subsciberData['fieldName'] == 'name')
                                            {
                                                $name = utf8_encode($subsciberData['data']);
                                            }
                                            elseif($subsciberData['fieldName'] == 'cognome' || $subsciberData['fieldName'] == 'surname')
                                            {
                                                $surname = utf8_encode($subsciberData['data']);
                                            }
                                            elseif ($subsciberData['fieldName'] == 'telefono' || $subsciberData['fieldName'] == 'phone' || $subsciberData['fieldName'] == 'telephone')
                                            {
                                                $phone = utf8_encode($subsciberData['data']);
                                            }
                                            elseif ($subsciberData['fieldName'] == 'mobile' || $subsciberData['fieldName'] == 'cell phone')
                                            {
                                                $mobile = utf8_encode($subsciberData['data']);
                                            }
                                        }
                                    }
                                }

                                if($name != '' && $surname != '')
                                {
                                    break;
                                }
                            }
                        }

                        $detail['id'] = $subscriberId;
                        $detail['email'] = $contact['email'];
                        $detail['name'] = $name;
                        $detail['surname'] = $surname;
                        $detail['phone'] = $phone;
                        $detail['mobile'] = $mobile;
                        $detail['ip'] = $contact['ip'];
                        $detail['iso2language'] = $contact['language'];
                        $detail['subscribeurl'] = $contact['url'];
                        $detail['privacy'] = $contact['privacy'];
                        $users[$contact['email']] = $detail;
                    }
                }

               /* if ($response && is_array($response)) {
                    foreach ($emailList as $contact) {
                        if (isset($response[$contact['email']])) {
                            $objMailOne->setAction("subscriber");
                            echo 1;
                            $detail = $objMailOne->export($response[$contact['email']]['id'], $response[$contact['email']]['list']);

                            print_r($detail);
                            die;
                            if ($detail && isset($detail['id'])) {
                                $detail['ip'] = $contact['ip'];
                                $detail['iso2language'] = $contact['language'];
                                $detail['subscribeurl'] = $contact['url'];
                                $detail['privacy'] = $contact['privacy'];

                                $users[$contact['email']] = $detail;

                            }
                        }
                    }
                }*/

                /**
                 * @var $privacy EntityManager
                 */
                $privacy = $this->getPrivacyDb($settings['settings'], $ownerdomain);

                $term = $privacy->getRepository('App\Entity\Privacy\Term')->findOneByUid($termId);
                $termParagraphs = $term->getParagraphs();
                $date = new \DateTime();
                $i = 0;
                foreach ($users as $detail) {
                    $userLang = strtolower($detail['iso2language']);
                    $prRes = new PrivacyResource($privacy);

                    if($i == 1000)
                    {
                        $prRes->EMClear();
                    }

                    $url = parse_url(  $detail['subscribeurl']);
                    if(!$url || !isset( $url['host'])) continue;

                    $form = [
                        'id' => $detail['id'],
                        'email' => $detail['email'],
                        'original_ip' => [],
                        'title' => [],
                        'name' => $detail['name'],
                        'surname' => $detail['surname'],
                        'phone' => $detail['mobile'],
                        'mobile' => $detail['phone'],
                        'fax' => [],
                        'city' => [],
                        'language' => $detail['iso2language'],
                        'zipcode' => [],
                        'nation' => [],
                        'birth date' => [],
                        'ip' => $detail['ip'],
                        'iso2language' => $detail['iso2language'],
                        'subscribeurl' => $detail['subscribeurl'],
                        'privacy' => $detail['privacy']
                    ];

                    $flags = array(
                        array(
                                'code' => 'dati_personali',
                                'selected' => true,
                                'mandatory' => true,
                                'text' => 'acconsento'
                        ),
                        array(
                            'code' => 'newsletter',
                            'selected' => true,
                            'mandatory' => false,
                            'text' => 'acconsento'
                        )
                    );

                    if(isset($termParagraphs[0]['treatments']) && !empty($termParagraphs[0]['treatments']))
                    {
                        $flags = array(
                            array(
                                'code' => $termParagraphs[0]['treatments'][0]['name'],
                                'selected' => true,
                                'mandatory' => $termParagraphs[0]['treatments'][0]['mandatory'],
                                'text' => (!empty($termParagraphs[0]['treatments'][0]['text'][$userLang]) ? $termParagraphs[0]['treatments'][0]['text'][$userLang] : $termParagraphs[0]['treatments'][0]['text']['en'])
                            ),
                            array(
                                'code' => $termParagraphs[0]['treatments'][1]['name'],
                                'selected' => true,
                                'mandatory' => $termParagraphs[0]['treatments'][1]['mandatory'],
                                'text' => (!empty($termParagraphs[0]['treatments'][1]['text'][$userLang]) ? $termParagraphs[0]['treatments'][1]['text'][$userLang] : $termParagraphs[0]['treatments'][1]['text']['en'])
                            )
                        );
                    }

                    $privacydata = array(
                        "referrer" => $detail['subscribeurl'],
                        "ownerId" =>$ownerdomain,
                        "termId" => $termId,
                        "language" => $userLang,
                        "name" => $term->getName(),
                        "paragraphs" => array(
                            array(
                                "text" => ($termParagraphs[0]['text'][$userLang] ? $termParagraphs[0]['text'][$userLang] : $termParagraphs[0]['text']['en']),
                                "title" => ($termParagraphs[0]['title'][$userLang] ? $termParagraphs[0]['title'][$userLang] : $termParagraphs[0]['title']['en']),
                                "treatments" => $flags
                            )),

                    );

                    try{
                        $pr = $prRes->savePrivacy(
                            $detail['ip'],
                            $form,
                            '',
                            $detail['name']===null?'':(is_array($detail['name'])?'':$detail['name']),
                            $detail['surname']===null?'':(is_array($detail['surname'])?'':$detail['surname']),
                            $termId,
                            $url['path'],
                            $privacydata,
                            $detail['id'].'-'.microtime(true),
                            'import-console-' . $date->format('YMDHm'),
                            $url['host'],
                            $detail['email'],
                            $flags,
                            isset($detail['mobile']) ? $detail['mobile'] : isset($detail['phone']) ? $detail['phone'] : '',
                            $detail['iso2language'],
                            null,
                            true
                        );

                        if($pr)
                        {
                            echo '.';
                        }
                        else
                        {
                            echo 'Entity manager connection close!!<br>';
                            break;
                        }
                    } catch (\Exception $e) {
                        echo "!";
                    }

                    $i++;
                }
            }
        }


    }

    private function getConfigDb($settings)
    {
        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            $settings['doctrine_config']['meta']['entity_path'],
            $settings['doctrine_config']['meta']['auto_generate_proxies'],
            $settings['doctrine_config']['meta']['proxy_dir'],
            $settings['doctrine_config']['meta']['cache'],
            false
        );

        $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_privacy']['encryption_key'])
        );
        $em = \Doctrine\ORM\EntityManager::create($settings['doctrine_config']['connection'], $config);
        $eventManager = $em->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        return $em;
    }

    // TODO: è stato creato l'helper App\Helpers\PrivacyHelper con questa funzione. Sostiture la chiamata a questa funzione in questa classe con l'helper e testare che il funzionamento sia lo stesso
    private function getPrivacyDb($settings, $ownerId)
    {

        $dynaDb = $settings['doctrine_privacy']['dynamic_db'];

        $db = $dynaDb['db'] . "_$ownerId";
        $user = $dynaDb['user'] . "_$ownerId";
        $password = md5($dynaDb['password'] . "Fx8k_${ownerId}_5tFg");


        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            $settings['doctrine_privacy']['meta']['entity_path'],
            $settings['doctrine_privacy']['meta']['auto_generate_proxies'],
            $settings['doctrine_privacy']['meta']['proxy_dir'],
            $settings['doctrine_privacy']['meta']['cache'],
            false
        );

        $connection = array(
            'driver' => $settings['doctrine_privacy']['connection']['driver'],
            'host' => $settings['doctrine_privacy']['connection']['host'],
            'dbname' => $db,
            'user' => $user,
            'password' => $password
        );


        $em = \Doctrine\ORM\EntityManager::create($connection, $config);

        $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_privacy']['encryption_key'])
        );

        $eventManager = $em->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        return $em;
    }

    private function getUpgradeDb($settings)
    {

        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            $settings['doctrine_upgrade']['meta']['entity_path'],
            $settings['doctrine_upgrade']['meta']['auto_generate_proxies'],
            $settings['doctrine_upgrade']['meta']['proxy_dir'],
            $settings['doctrine_upgrade']['meta']['cache'],
            false
        );

        $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_privacy']['encryption_key'])
        );
        $em = \Doctrine\ORM\EntityManager::create($settings['doctrine_upgrade']['connection'], $config);
        $eventManager = $em->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        return $em;
    }
}