<?php

namespace App\Console\Command;

use App\Resource\PrivacyResource;
use Doctrine\ORM\EntityManager;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportNewsletter extends Base
{
    protected function configure()
    {
        $this->setName('import:newsletter')
            ->setDescription('Import newsletter data from a list')
            ->addArgument(
                'owner',
                InputArgument::REQUIRED,
                'Select the destination owner'
            )
            ->addArgument(
                'termId',
                InputArgument::REQUIRED,
                'Term Id'
            )
            ->addArgument(
                'hostname',
                InputArgument::REQUIRED,
                'Db Hostname'
            )
            ->addArgument(
                'username',
                InputArgument::REQUIRED,
                'Db Username'
            )
            ->addArgument(
                'password',
                InputArgument::REQUIRED,
                'Db Password'
            )
            ->addArgument(
                'dbname',
                InputArgument::REQUIRED,
                'Db Name'
            )
            ->addArgument(
                'listid',
                InputArgument::REQUIRED,
                    "List's id to be imported"
                //'List of ids of the distribution lists to be imported (values separated by a comma)'
            )
            ->addArgument(
                'domain',
                InputArgument::REQUIRED,
                'Site domain with protocol'
            )
            ->addArgument(
                'language',
                InputArgument::REQUIRED,
                'Distribution list language'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $ownerId = $input->getArgument('owner');
        $termId = $input->getArgument('termId');
        $hostname = $input->getArgument('hostname');
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $dbname = $input->getArgument('dbname');
        $listId = explode(',', $input->getArgument('listid'));
        $domain = $input->getArgument('domain');
        $lang = $input->getArgument('language');
        $configFile = realpath(__DIR__ . '/../../../settings.php');

        $validate = false;

        try{
            $validate = $this->validateArguments($ownerId, $termId, $hostname, $username, $password, $dbname, $listId, $domain, $lang);
        } catch (\Exception $e) {
            echo $e->getMessage();
        }

        if($validate)
        {
            $settings = require $configFile;

            $config = new \Doctrine\DBAL\Configuration();

            $connectionParams = array(
                'dbname' => $dbname,
                'user' => $username,
                'password' => $password,
                'host' => $hostname,
                'driver' => 'pdo_mysql',
            );

            $conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
            $conn->connect();

            if($conn->isConnected())
            {
                $limitDate = mktime(0, 0, 0, 05, 25, 2018);

                /**
                 * @var $privacy EntityManager
                 */
                $privacy = $this->getPrivacyDb($settings['settings'], $ownerId);

                $term = $privacy->getRepository('App\Entity\Privacy\Term')->findOneByUid($termId);
                $termParagraphs = $term->getParagraphs();
                // TODO: effettuare controlli sulla corretta esistenza di $termParagraphs (e che siano composti come ce li aspettiamo)

                $date = new \DateTime();

                $importEmailList = array();
                $alredyExistsEmail = array();
                $errorImport = array();

                foreach ($listId as $singleId)
                {
                    $prRes = new PrivacyResource($privacy);

                    $query = 'SELECT * FROM email_list_subscribers 
                              WHERE listid = '.$singleId.'                              
                              AND subscribedate < '.$limitDate.' 
                              AND unsubscribeconfirmed = 0  and bounced=0';

                    $state = $conn->executeQuery($query);
                    $result = $state->fetchAll();

                    if($result && !empty($result))
                    {
                        $i = 0;
                        $countImport = 0;
                        $countExists = 0;
                        $countError = 0;
                        $tmpError = array();

                        foreach ($result as $newsletteretails)
                        {
                            $email = $newsletteretails['emailaddress'];
                            $checkEmailExists = $privacy->getRepository('App\Entity\Privacy\Privacy')->findByEmail($email);

                            if($i == 1000)
                            {
                                $prRes->EMClear();
                            }

                            // Se non è già presente la mail nel DB aggiungo il record
                            if(empty($checkEmailExists))
                            {
                                $name = '';
                                $surname = '';
                                $userLang = $lang;
                                $phone = '';
                                $mobile = '';

                                $queryData = "SELECT esd.*, ec.name as fieldName FROM email_subscribers_data as esd
                                            inner join email_list_subscribers as els on esd.subscriberid = els.subscriberid 
                                            inner join email_customfields as ec on ec.fieldid = esd.fieldid
                                            where els.subscriberid = ".$newsletteretails['subscriberid']." 
                                            and esd.data != '' 
                                            and ec.name in ('lingua', 'language', 'lang', 'nome', 'name', 'cognome', 'surname', 'telefono', 'mobile', 'phone', 'telephone', 'cell phone'); ";

                                $stateData = $conn->executeQuery($queryData);
                                $resultData = $stateData->fetchAll();

                                if($resultData && !empty($resultData))
                                {
                                    foreach ($resultData as $subsciberData)
                                    {
                                        $subsciberData['fieldName'] = strtolower($subsciberData['fieldName']);
                                        if($subsciberData['fieldName'] == 'lingua' || $subsciberData['fieldName'] == 'language' || $subsciberData['fieldName'] == 'lang')
                                        {
                                            $userLang = strtolower($subsciberData['data']);
                                        }
                                        elseif($subsciberData['fieldName'] == 'nome' || $subsciberData['fieldName'] == 'name')
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

                                if(strlen($userLang) > 2)
                                {
                                    switch ($userLang) {
                                        case 'ita':
                                            $userLang = 'it';
                                            break;
                                        case 'eng':
                                            $userLang = 'en';
                                            break;
                                        case 'deu':
                                            $userLang = 'de';
                                            break;
                                        case 'fra':
                                            $userLang = 'fr';
                                            break;
                                        case 'rus':
                                            $userLang = 'ru';
                                            break;
                                        default:
                                            $userLang = 'en';
                                    }
                                }

                                $uid = $newsletteretails['subscriberid'].'-'.microtime(true);
                                $ref = 'import-nl-mailone-console-' . $date->format('YMDHm');
                                $ip = ($newsletteretails['requestip'] != '' && !is_null($newsletteretails['requestip'])) ? $newsletteretails['requestip'] : '';
                                $form = [
                                    'id' => $newsletteretails['subscriberid'],
                                    'email' => $email,
                                    'original_ip' => [],
                                    'title' => [],
                                    'name' => $name,
                                    'surname' => $surname,
                                    'phone' => $mobile,
                                    'mobile' => $phone,
                                    'fax' => [],
                                    'city' => [],
                                    'language' => $userLang,
                                    'zipcode' => [],
                                    'nation' => [],
                                    'birth date' => [],
                                    'ip' => $ip,
                                    'iso2language' => $userLang,
                                    'subscribeurl' => $domain,
                                    'privacy' => ($termParagraphs[0]['text'][$userLang] ? $termParagraphs[0]['text'][$userLang] : $termParagraphs[0]['text']['en'])
                                ];

                                $flags = array(
                                    array(
                                        'code' => 'dati_personali',
                                        'selected' => false,
                                        'mandatory' => false,
                                        'text' => 'acconsento'
                                    ),
                                    array(
                                        'code' => 'newsletter',
                                        'selected' => false,
                                        'mandatory' => false,
                                        'text' => 'acconsento'
                                    )
                                );

                                if(isset($termParagraphs[0]['treatments']) && !empty($termParagraphs[0]['treatments']))
                                {
                                    $flags = array(
                                        array(
                                            'code' => $termParagraphs[0]['treatments'][0]['name'],
                                            'selected' => $termParagraphs[0]['treatments'][0]['restrictive'],
                                            'mandatory' => $termParagraphs[0]['treatments'][0]['mandatory'],
                                            'text' => (!empty($termParagraphs[0]['treatments'][0]['text'][$userLang]) ? $termParagraphs[0]['treatments'][0]['text'][$userLang] : $termParagraphs[0]['treatments'][0]['text']['en'])
                                        ),
                                        array(
                                            'code' => $termParagraphs[0]['treatments'][1]['name'],
                                            'selected' => $termParagraphs[0]['treatments'][1]['restrictive'],
                                            'mandatory' => $termParagraphs[0]['treatments'][1]['mandatory'],
                                            'text' => (!empty($termParagraphs[0]['treatments'][1]['text'][$userLang]) ? $termParagraphs[0]['treatments'][1]['text'][$userLang] : $termParagraphs[0]['treatments'][1]['text']['en'])
                                        )
                                    );
                                }

                                $privacydata = array(
                                    "referrer" => $domain,
                                    "ownerId" =>$ownerId,
                                    "termId" => $termId,
                                    "language" => $userLang,
                                    "name" => $term->getName(),
                                    "paragraphs" => array(
                                        array(
                                            "text" => ($termParagraphs[0]['text'][$userLang] ? $termParagraphs[0]['text'][$userLang] : $termParagraphs[0]['text']['en']),
                                            "title" => ($termParagraphs[0]['title'][$userLang] ? $termParagraphs[0]['title'][$userLang] : $termParagraphs[0]['title']['en']),
                                            "treatments" => $flags
                                        )/*,
                                    array(
                                        "text" => ($termParagraphs[1]['text'][$userLang] ? $termParagraphs[1]['text'][$userLang] : $termParagraphs[1]['text']['en']),
                                        "title" => ($termParagraphs[1]['title'][$userLang] ? $termParagraphs[1]['title'][$userLang] : $termParagraphs[1]['title']['en']),
                                        "treatments" => $flags
                                    )*/ // NOTA: da scommentare nel caso si voglia mettere il paragrafo sia per la privacy sia per la newsletter
                                    ),
                                );
                                $site = '';
                                try{
                                    $pr = $prRes->savePrivacy(
                                        $ip,
                                        $form,
                                        '',
                                        $name,
                                        $surname,
                                        $termId,
                                        $site,
                                        $privacydata,
                                        $uid,
                                        $ref,
                                        str_replace(array('https://', 'http://'), '', $domain),
                                        $email,
                                        $flags,
                                        ($phone != '' ? $phone : ($mobile != '' ? $mobile : '')),
                                        $userLang,
                                        null,
                                        true
                                    );

                                    if($pr)
                                    {
                                        echo '.';
                                        $countImport++;
                                    }
                                    else
                                    {
                                        echo 'Entity manager connection close!!<br>';
                                        break;
                                    }
                                } catch (\Exception $e) {
                                    echo "!(".$e->getMessage().")";
                                    $countError++;
                                    $tmpError[] = $e->getMessage();
                                    //echo $e->getMessage(); die;
                                }
                            }
                            else
                            {
                                echo ':';
                                $countExists++;
                            }

                            $i++;
                        }
                    }
                    else
                    {
                        $message = 'List empty or not found!';
                        $qList = 'SELECT name FROM email_lists 
                              WHERE listid = '.$singleId;

                        $state = $conn->executeQuery($qList);
                        $result = $state->fetchAll();

                        if($result && !empty($result))
                        {
                            $message = "'".$result[0]['name']."' ".strtolower($message);
                        }
                        else
                        {
                            $message = 'List with listid '.$singleId.' empty or not found!';
                        }

                        echo " ** ".$message." ** ";
                    }

                    $importEmailList[] = " - ".$countImport." contacts imported for list ID ".$singleId." - ";
                    if($countExists > 0)
                    {
                        $alredyExistsEmail[] = " - ".$countExists." existing contacts in dataone for list ID ".$singleId." - ";
                    }
                    if(!empty($tmpError))
                    {
                        $errorImport[] = " - ".$countError." errors during contacts importation for list ID ".$singleId.": ".implode(", ", $tmpError)." - ";
                    }
                }

                echo implode(";<br>", $importEmailList);

                if(!empty($alredyExistsEmail))
                {
                    echo implode(";<br>", $alredyExistsEmail);
                }

                if(!empty($errorImport))
                {
                    echo implode(";<br>", $errorImport);
                }
            }
            else
            {
                echo $conn->errorInfo();
            }
            $conn->close();
        }
    }

    private function validateArguments($ownerId, $termId, $hostname, $username, $password, $dbname, $listId, $domain, $lang)
    {
        $error = array();

        if(!is_integer($ownerId))
        {
            $ownerId = intval($ownerId);
        }

        if($ownerId < 1)
        {
            $error[] = 'Incorrect OwnerId; the value must be greater than 0';
        }

        if(!is_string($termId) || $termId == '')
        {
            $error[] = 'TermId incorrect or empty; enter a correct value';
        }

        if(!is_string($hostname) || $hostname == '')
        {
            $error[] = 'Hostname incorrect or empty; enter a correct value';
        }

        if(!is_string($username) || $username == '')
        {
            $error[] = 'Username incorrect or empty; enter a correct value';
        }

        if(!is_string($password) || $password == '')
        {
            $error[] = 'Password incorrect or empty; enter a correct value';
        }

        if(!is_string($dbname) || $dbname == '')
        {
            $error[] = 'DB name incorrect or empty; enter a correct value';
        }

        if(is_array($listId) && !empty($listId))
        {
            $listError = false;
            foreach ($listId as $valId)
            {
                if(intval($valId) < 1)
                {
                    $listError = true;
                }
            }

            if($listError)
            {
                $error[] = 'the id of the list must be greater than 0';
            }
        }
        else
        {
            $error[] = 'At least one id must have been passed';
        }

        if(!is_string($domain) || $domain == '')
        {
            $error[] = 'Domain incorrect or empty; enter a correct value';
        }
        else
        {
            $checkDoamin = filter_var($domain, FILTER_VALIDATE_URL);

            if(!$checkDoamin)
            {
                $error[] = 'Mandatory protocol';
            }
        }

        if(!is_string($lang) || $lang == '')
        {
            $error[] = 'Language incorrect or empty; enter a correct value';
        }
        elseif(strlen($lang) != 2)
        {
            $error[] = 'The language must be in ISO2 format';
        }

        if(!empty($error))
        {
            $message = 'The past parameters are wrong: '.implode(', ', $error).'.';
            throw new \Exception($message);
        }

        return true;
    }

    // TODO: mettere questa funzione e quelle presenti in importUpgrade.php in una classe (Helper o Service) esterno
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
}