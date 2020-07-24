<?php
/**
 * Created by PhpStorm.
 * Author：Duan Hao Yang
 * User: Acer
 * Date: 2020/7/23
 * Time: 19:13
 */

function Ip($type = 0)
{
    static $ip = null;
    if (null !== $ip) {
        return $ip[$type];
    }
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $pos = array_search('unknown', $arr);
        if (false !== $pos) {
            unset($arr[$pos]);
        }
        $ip = trim(current($arr));
    } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    // IP地址合法验证
    $long = sprintf("%u", ip2long($ip));
    $ip = $long ? [$ip, $long] : ['127.0.0.1', 0];
    return $ip[$type];
}


function LogConfig($filename)
{
    static $config = [];
    if (empty($config[$filename])) {
        $config[$filename] = require_once($filename);
    }
    return $config[$filename];
}
