<?php

namespace App\Helpers;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class DynDb
{

    public static function get($dbdata,$ownerId,$settings,$context)
    {



        $guestCredentials = self::getGuestDbCredentials($dbdata,$ownerId);


        $dbname = $guestCredentials['db'];

        $user = $guestCredentials['user'];

        $pwd = $guestCredentials['password'];


        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
            $settings[$context]['meta']['entity_path'],
            $settings[$context]['meta']['auto_generate_proxies'],
            $settings[$context]['meta']['proxy_dir'],
            $settings[$context]['meta']['cache'],
            false
        );


        $connection = array(
            'driver'   => $settings[$context]['connection']['driver'],
            'host'     => $settings[$context]['connection']['host'],
            'dbname'   => $dbname,
            'user'     => $user,
            'password' => $pwd
        );



        $em = \Doctrine\ORM\EntityManager::create($connection , $config);

        $subscriber = new \App\DoctrineEncrypt\Subscribers\DoctrineEncryptSubscriber(
            new \Doctrine\Common\Annotations\AnnotationReader(),
            new \App\DoctrineEncrypt\Encryptors\OpenSslEncryptor($settings['doctrine_privacy']['encryption_key'])
        );

        $eventManager = $em->getEventManager();
        $eventManager->addEventSubscriber($subscriber);

        return $em;
    }

    private static function getGuestDbCredentials($dynaDb,$ownerId) {



            return [
                "db" => $dynaDb['db'] . "_$ownerId",
                "user" => $dynaDb['user'] . "_$ownerId",
                "password" => md5($dynaDb['password'] . "Fx8k_${ownerId}_5tFg")
            ];


    }



}