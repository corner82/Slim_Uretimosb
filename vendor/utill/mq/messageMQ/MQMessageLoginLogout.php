<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\MQ\MessageMQ;

class MQMessageLoginLogout extends \Utill\MQ\MessageMQ\MQMessage {
    
    
    const LOGIN_OPERATAION                 = 42;
    const LOGOUT_OPERATION                 = 43;


    public function __construct() {

    }
}

