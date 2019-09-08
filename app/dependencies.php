<?php
// DIC configuration
use App\Action\Emails\PlainTemplateBuilder;
use App\Batch\DeferredPrivacyBatch;
use App\Batch\EmailSender;
use App\Batch\EntityManagerBuilder;
use App\DoctrineEncrypt\Encryptors\OpenSslEncryptor;
use App\Helpers\StringTemplate\Engine;
use App\Service\AttachmentsService;
use App\Service\DeferredPrivacyService;
use GuzzleHttp\Client;
use Slim\App;

$container = $app->getContainer();

// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings');
    $logger = new \Monolog\Logger($settings['logger']['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['logger']['path'], \Monolog\Logger::DEBUG));
    return $logger;
};


// Doctrine
$container['em-config'] = function ($c) {
    $settings = $c->get('settings');
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['doctrine_config']['meta']['entity_path'],
        $settings['doctrine_config']['meta']['auto_generate_proxies'],
        $settings['doctrine_config']['meta']['proxy_dir'],
        $settings['doctrine_config']['meta']['cache'],
        false
    );

    $em = \Doctrine\ORM\EntityManager::create($settings['doctrine_config']['connection'], $config);

    return $em;
};



