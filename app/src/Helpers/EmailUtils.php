<?php
namespace  App\Helpers;
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 01/05/18
 * Time: 17.34
 */

use GuzzleHttp\Client;

Class EmailUtils {

    public static function makeGlobalReplace($replaceString,$message) {
        foreach ( $replaceString as $key => $value ) {
            $message = str_replace ( $key, $value, $message );
        }

        return $message;
    }
}
