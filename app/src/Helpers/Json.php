<?php

namespace App\Helpers;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;

class Json
{
    public  static function toJson($obj) {
        $on = new ObjectNormalizer();
        $on->setCircularReferenceLimit(1);
        $on->setCircularReferenceHandler(function ($object) { return $object->getId(); });
        $dtn = new DateTimeNormalizer('Y-m-d');
        $s = new Serializer(array($dtn, $on), array(new JsonEncoder()) );
        return $s->normalize($obj,'json');
    }

    public static function toEntity($json, $type) {
        $json=json_decode($json);
        $s = new Serializer(
            array(new DateTimeNormalizer(), new ObjectNormalizer() )
        );
        return $s->denormalize($json,$type);
    }
}