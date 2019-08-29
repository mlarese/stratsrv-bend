<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use App\DoctrineEncrypt\Encryptors\OpenSslEncryptor;
use App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Annotations\AnnotationRegistry;

require 'vendor/autoload.php';
AnnotationRegistry::registerLoader('class_exists');

$settings = include 'app/settings.php';

$settings['settings']['doctrine_privacy']['connection']['dbname'] = "privacy_$currentOwner";
$settingsPrivacy= $settings['settings']['doctrine_privacy'];


$doctrinPrivacy = Setup::createAnnotationMetadataConfiguration(
    $settingsPrivacy['meta']['entity_path'],
    $settingsPrivacy['meta']['auto_generate_proxies'],
    $settingsPrivacy['meta']['proxy_dir'],
    $settingsPrivacy['meta']['cache'],
    false
);

echo "\n current db ".$settings['settings']['doctrine_privacy']['connection']['dbname'] ;

$emPrivacy = EntityManager::create($settingsPrivacy['connection'], $doctrinPrivacy);


 $subscriber = new DoctrineEncryptSubscriber(
     new AnnotationReader,
     new OpenSslEncryptor('jkkkkjjjjkkjkjkj')
 );

 $subscriber1 = new DoctrineEncryptSubscriber(
     new AnnotationReader,
     new OpenSslEncryptor('jkkkkjjjjkkjkjkj')
 );


 $eventManagerp = $emPrivacy->getEventManager();
 $eventManagerp->addEventSubscriber($subscriber1);


return ConsoleRunner::createHelperSet($emPrivacy);


