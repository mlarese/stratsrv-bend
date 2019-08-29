<?php

namespace App\Console\Command;

use Doctrine\ORM\EntityManager;
use App\Entity\Privacy\Privacy as PrivacyEntity;
use Console\Command\Base;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportStructureReservation extends Base
{
    protected function configure()
    {
        $this->setName('import:abs:structurereservation')
            ->setDescription('Import reservation from ABS BI reservations export')
            ->addArgument(
                'structureId',
                InputArgument::REQUIRED,
                'Structure ID'
            )
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
                'file',
                InputArgument::REQUIRED,
                'The CSV file path'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // validate arguments
        $structureId = $input->getArgument('structureId');
        $ownerId = $input->getArgument('owner');
        $termId = $input->getArgument('termId');
        $file = realpath($input->getArgument('file'));
        $this->validateArguments(
            $structureId,
            $ownerId,
            $termId,
            $file
        );

        // Read CSV file
        // @todo manca il controllo se è un file CSV
        $csv = fopen($file, 'r');
        $header = [];
        $body = [];
        while (($tmp = fgetcsv($csv, 10000, ';')) !== false) {
            if (count($header) == 0)  {
                $header = $tmp;
            } else {
                $body[] = $tmp;
            }
        }
        $header = array_flip($header);
        // @todo controllare se l'header cambia...

        /**
         * Get privacy
         *
         * @var $em EntityManager
         */
        $settings = require realpath(__DIR__ . '/../../../settings.php');
        $em = $this->getPrivacyDb($settings['settings'], $ownerId);
        /** @var \App\Entity\Privacy\Term $term */
        $term = $em->getRepository('App\Entity\Privacy\Term')->findOneByUid($termId);
        $termParagraphs = $term->getParagraphs();

        // Transform and insert data
        $now = new \DateTime('now');
        try {
            foreach ($body as $row) {

                // Terms flags
                $privacyTerm = (bool)$row[$header['Privacy Term']];
                $newsletterTerm = (bool)$row[$header['Newsletter']];

                // Bypass no minimum requirements
                // @todo creare un messaggio di warning
                if (!$privacyTerm) {
                    continue;
                } else {
                    $privacyEntity = new PrivacyEntity();
                }

                // Set email
                $privacyEntity->setEmail(trim($row[$header['Email']]));

                if(empty($privacyEntity->getEmail()))
                {
                    continue;
                }

                // Set IP address
                $privacyEntity->setIp(trim($row[$header['Ip Address']]));

                // @todo fare un validatore per il generatore dell'UID (max char length: 128 chars)
                // Generate and set the UID: structure ID + md5(email) + unixtime(checkin date) + ip address
                $uid = new \DateTime($row[$header['Data apertura']]);
                if (strpos($row[$header['Data apertura']], '00:00:00') === false) {
                    $uid = sprintf(
                        "%s-%s-%s-%s",
                        trim($structureId),
                        md5($privacyEntity->getEmail()),
                        $uid->format('U'),
                        str_replace('.', '', $privacyEntity->getIp())
                    );
                } else {
                    $uid = sprintf(
                        "%s-%s-%s-%s-%s",
                        trim($structureId),
                        md5($privacyEntity->getEmail()),
                        $uid->format('U'),
                        rand(10000, 99999),
                        str_replace('.', '', $privacyEntity->getIp())
                    );
                }
                $privacyEntity->setId($uid);
                unset($uid);

                // Set reference
                $privacyEntity->setRef(sprintf(
                    "import-console-abs-reservation-%s",
                    $now->format('YMDHi')
                ));

                // Set name
                $name = explode(' ', $row[$header['Ospite']]);
                $privacyEntity->setName(utf8_encode(trim($name[0])));
                unset($name);

                // Set surname
                $privacyEntity->setSurname(utf8_encode(trim(str_replace($privacyEntity->getName(), '', $row[$header['Ospite']]))));

                // Set referer
                $referer = $row[$header['Referer']];

                // Set language
                $lang = utf8_encode(trim($row[$header['Lingua']]));

                // Set form
                $privacyEntity->setForm([
                    'id' => $privacyEntity->getId(),
                    'email' => trim($privacyEntity->getEmail()),
                    'title' => utf8_encode($row[$header['Portale']]),
                    'name' => $privacyEntity->getName(),
                    'surname' => $privacyEntity->getSurname(),
                    'phone' => [],
                    'mobile' => [],
                    'fax' => [],
                    'city' => utf8_encode(trim($row[$header['Città']])),
                    'language' => $lang,
                    'zipcode' => [],
                    'nation' => utf8_encode($row[$header['Nazione']]),
                    'birth date' => [],
                    'ip' => $privacyEntity->getIp(),
                    'iso2language' => $lang,
                    'subscribeurl' => $referer,
                    'privacy' => (!empty($termParagraphs[0]['text'][$lang]) ? $termParagraphs[0]['text'][$lang] : $termParagraphs[0]['text']['en'])
                ]);
                $privacyEntity->setCryptedForm(json_encode($privacyEntity->getForm()));

                $codeDatiPersonali = 'dati_personali';
                $codeNewsletter = 'newsletters';

                if(isset($termParagraphs[0]['treatments'][0]['name']) && !empty($termParagraphs[0]['treatments'][0]['name']))
                {
                    $codeDatiPersonali = $termParagraphs[0]['treatments'][0]['name'];
                }

                if(isset($termParagraphs[0]['treatments'][1]['name']) && !empty($termParagraphs[0]['treatments'][1]['name']))
                {
                    $codeNewsletter = $termParagraphs[0]['treatments'][1]['name'];
                }

                // Set privacy
                $privacyEntity->setPrivacyFlags([
                    [
                        'code' => $codeDatiPersonali,
                        'selected' => $privacyTerm,
                        'mandatory' => true,
                        'text' => (!empty($termParagraphs[0]['treatments'][0]['text'][$lang]) ? $termParagraphs[0]['treatments'][0]['text'][$lang] : $termParagraphs[0]['treatments'][0]['text']['en'])
                    ], [
                        'code' => $codeNewsletter,
                        'selected' => $newsletterTerm,
                        'mandatory' => false,
                        'text' => (!empty($termParagraphs[0]['treatments'][1]['text'][$lang]) ? $termParagraphs[0]['treatments'][1]['text'][$lang] : $termParagraphs[0]['treatments'][1]['text']['en'])
                    ]
                ]);

                $privacyEntity->setPrivacy([
                    "referrer" => $referer,
                    "ownerId" => $ownerId,
                    "termId" => $termId,
                    "language" => $lang,
                    "name" => $term->getName(),
                    "paragraphs" => [
                        [
                            "text" => (!empty($termParagraphs[0]['text'][$lang]) ? $termParagraphs[0]['text'][$lang] : $termParagraphs[0]['text']['en']),
                            "title" => (!empty($termParagraphs[0]['title'][$lang]) ? $termParagraphs[0]['title'][$lang] : $termParagraphs[0]['title']['en']),
                            "treatments" => $privacyEntity->getPrivacyFlags()
                        ]
                    ],
                ]);

                // Set term ID
                $privacyEntity->setTermId($termId);

                // Set domain & site
                $referer = parse_url($referer);
                isset($referer['host']) ? $privacyEntity->setDomain($referer['host']) : $privacyEntity->setDomain('');
                isset($referer['path']) ? $privacyEntity->setSite($referer['path']) : $privacyEntity->setSite('');

                // Set created
                $created = new \DateTime($row[$header['Data apertura']]);
                $privacyEntity->setCreated($created);

                // Set deleted
                $privacyEntity->setDeleted(0);

                $em->persist($privacyEntity);
                echo('.');
                unset($referer, $lang, $created);
                //break; // @todo test!!!!!!!!!!!!!!!!!!!!!
            }
            $em->flush();
        } catch (\Exception $e) {
            // @todo aggiungere gestione errore in inserimento
            throw  $e;
        }
    }

    private function validateArguments($structureId, $ownerId, $termId, $file)
    {
        if(!is_integer($structureId))
        {
            $structureId = intval($structureId);
            if($structureId < 1)
            {
                throw new \Exception(sprintf(
                    "Wrong structure ID. the structure ID must be grater than 0"
                ));
            }
        }

        if(!is_integer($ownerId))
        {
            $ownerId = intval($ownerId);
            if($ownerId < 1)
            {
                throw new \Exception(sprintf(
                    "Wrong owner ID. the owner ID must be grater than 0"
                ));
            }
        }

        if (empty($termId)) {
            throw new \Exception(sprintf(
                "Wrong term ID"
            ));
        }

        if (empty($file) ||
            !file_exists($file)
        ) {
            throw new \Exception(sprintf(
                "The file `%s` not exists",
                $file
            ));
        }
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