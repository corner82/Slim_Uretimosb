<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\MQ\MessageMQ;

class MQMessageLoginLogout extends \Utill\MQ\MessageMQ\MQMessage {
    
    
    const LOGIN_OPERATAION                 = 42;
    const LOGOUT_OPERATION                 = 43;


    public function __construct() {

    }
}

