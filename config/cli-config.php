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
$settingsConfig = $settings['settings']['doctrine_config'];
$settingsPrivacy= $settings['settings']['doctrine_privacy'];
$settingsUpgrade= $settings['settings']['doctrine_upgrade'];

$doctrinConfig = Setup::createAnnotationMetadataConfiguration(
    $settingsConfig['meta']['entity_path'],
    $settingsConfig['meta']['auto_generate_proxies'],
    $settingsConfig['meta']['proxy_dir'],
    $settingsConfig['meta']['cache'],
    false
);

$doctrinPrivacy = Setup::createAnnotationMetadataConfiguration(
    $settingsPrivacy['meta']['entity_path'],
    $settingsPrivacy['meta']['auto_generate_proxies'],
    $settingsPrivacy['meta']['proxy_dir'],
    $settingsPrivacy['meta']['cache'],
    false
);

$doctrinUpgrade = Setup::createAnnotationMetadataConfiguration(
    $settingsUpgrade['meta']['entity_path'],
    $settingsUpgrade['meta']['auto_generate_proxies'],
    $settingsUpgrade['meta']['proxy_dir'],
    $settingsUpgrade['meta']['cache'],
    false
);


$emConfig = EntityManager::create($settingsConfig['connection'], $doctrinConfig);
$emPrivacy = EntityManager::create($settingsPrivacy['connection'], $doctrinPrivacy);
$emUpgrade = EntityManager::create($settingsUpgrade['connection'], $doctrinUpgrade);


 $subscriber = new DoctrineEncryptSubscriber(
     new AnnotationReader,
     new OpenSslEncryptor('jkkkkjjjjkkjkjkj')
 );

 $subscriber1 = new DoctrineEncryptSubscriber(
     new AnnotationReader,
     new OpenSslEncryptor('jkkkkjjjjkkjkjkj')
 );

 $eventManager = $emConfig->getEventManager();
 $eventManager->addEventSubscriber($subscriber);

 $eventManagerp = $emPrivacy->getEventManager();
 $eventManagerp->addEventSubscriber($subscriber1);

/***********************
 *  Config
 */
return ConsoleRunner::createHelperSet($emConfig);

 /***********************
 *  Privacy
 */
return ConsoleRunner::createHelperSet($emPrivacy);



//return ConsoleRunner::createHelperSet($emUpgrade);
