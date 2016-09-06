<?php

namespace Utill\Env;

class serverVariables {
    public function __construct() {
        
    }
    
    /**
     * get client ip adress whereever it is located
     * @return strıng
     * @author Mustafa Zeynel Dağlı
     * @deprecated since 22/03/2016 slimm app request object has 'getIp()' function
     */
    public static function  getClientIp() {
    $ipaddress = '';
    //print_r(apache_request_headers());
    //print_r($_SERVER);
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset ($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset ($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset ($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset ($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
}

