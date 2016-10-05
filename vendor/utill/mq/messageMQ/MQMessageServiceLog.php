<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\MQ\MessageMQ;

class MQMessageServiceLog extends \Utill\MQ\MessageMQ\MQMessage {
    
    
    const SERVICE_INSERT_OPERATION                 = 45;
    const SERVICE_DELETE_OPERATION                 = 46;
    const SERVICE_UPDATE_OPERATION                 = 47;
    


    public function __construct() {

    }
}

