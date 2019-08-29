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

    $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
        new \Doctrine\Common\Annotations\AnnotationReader(),
        new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_config']['encryption_key'])
    );
    $em = \Doctrine\ORM\EntityManager::create($settings['doctrine_config']['connection'], $config);
    $eventManager = $em->getEventManager();
    $eventManager->addEventSubscriber($subscriber);

    return $em;
};


$container['session'] = function ($container) {
    return new \Adbar\Session(
        $container->get('settings')['session']['namespace']
    );
};




$container['encryptor'] = function ($container) {
    $settings = $container->get('settings');
    $options = $settings['doctrine_privacy'];

    $enc = new OpenSslEncryptor($options['encryption_key']);
    return $enc;
};



$container['slim_app'] = function  ($container) use($app){
    return $app;
};


$container['action_handler'] = function ($container) {
    $actionHandler = new \App\Action\ActionHandler($container);
    return $actionHandler;
};

