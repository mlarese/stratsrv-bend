<?php
/**
 * Created by PhpStorm.
 * User: mauro.larese
 * Date: 21/08/2018
 * Time: 11:05
 */

namespace App\Traits;


use App\DoctrineEncrypt\Encryptors\EncryptorInterface;
use function base64_decode;
use Slim\Container;
use function urldecode;
use function urlencode;
use App\Helpers\Base32;

trait UrlHelpers {
    public function urlB32EncodeString(string $str, EncryptorInterface $encryptor = null) {
        if(isset($encryptor)) {
            $stre = $encryptor->encrypt($str);
        }
        return  Base32::encode ($stre);
    }

    public function urlB32DecodeString(string $str, EncryptorInterface $encryptor = null) {
        $res = Base32::decode($str);

        if(isset($encryptor)) {
            $res = $encryptor->decrypt($res);

        }

        return $res;
    }

    /**
     * @param string $str
     * @return array
     */
    public function urlB32DecodeToArray(string $str, EncryptorInterface $encryptor = null) {
        $res = [];

        if(isset($encryptor)) {
            $str =$this->urlB32DecodeString ($str, $encryptor);
        } else {
            $str =$this->urlB32DecodeString ($str);
        }

        parse_str($str,$res);
        return $res;
    }
    public function urlB64EncodeString(string $str) {
        return  base64_encode ($str);
    }

    public function urlB64DecodeString(string $str) {
        $res = base64_decode($str);

        if(isset($encryptor)) {
            $res = $encryptor->decrypt($res);

        }
        return $res;
    }

    /**
     * @param string $str
     * @return array
     */
    public function urlB64DecodeToArray(string $str) {
        $res = [];
        $str =$this->urlB64DecodeString ($str);
        parse_str($str,$res);
        return $res;
    }

}
