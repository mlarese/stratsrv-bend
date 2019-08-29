<?php
namespace  App\Helpers;
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 01/05/18
 * Time: 17.34
 */

Class UrlUtils {

    public static function repair($arrayData,$skip='action') {
        $params = array();
        foreach ($arrayData as $k => $v){
            if($k != $skip)
            {
                $params[] = '&' . $k . '=';
            }
            $params[] = $v;
        }


        return implode($params);
    }



}