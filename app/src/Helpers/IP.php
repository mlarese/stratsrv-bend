<?php
namespace  App\Helpers;
/**
 * Created by PhpStorm.
 * User: giuseppe.donato
 * Date: 01/05/18
 * Time: 17.34
 */

Class IP {

    public static function checkAbsIP($ip) {
        if (!empty($ip) && ip2long($ip)!=-1 && ip2long($ip)!=false) {

            $private_ips = array (
                array('10.0.0.0','10.255.255.255'),
                array('127.0.0.0','127.255.255.255'),
                array('172.16.0.0','172.31.255.255'),
                array('192.168.0.0','192.168.255.255')
            );

            foreach ($private_ips as $r) {
                $min = ip2long($r[0]);
                $max = ip2long($r[1]);
                if ((ip2long($ip) >= $min) && (ip2long($ip) <= $max)) return false;
            }
            return true;
        } else {
            return false;
        }
    }


    public static function determineHostIP() {
        if (isset($_SERVER["HTTP_CLIENT_IP"]) && self::checkAbsIP($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        }
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            foreach (explode(",", $_SERVER["HTTP_X_FORWARDED_FOR"]) as $ip) {
                if (self::checkAbsIP(trim($ip))) {
                    return $ip;
                }
            }
        }
        if (isset($_SERVER["HTTP_X_FORWARDED"]) && self::checkAbsIP($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"]) && self::checkAbsIP($_SERVER["HTTP_X_CLUSTER_CLIENT_IP"])) {
            return $_SERVER["HTTP_X_CLUSTER_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"]) && self::checkAbsIP($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"]) && self::checkAbsIP($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }


    public static function purgeIpAddress($ipAddress)
    {
        foreach (explode(",",$ipAddress) as $ip) {
            if (self::checkAbsIP(trim($ip))) {
                return $ip;
            }
        }

        return trim($ipAddress);

    }

}