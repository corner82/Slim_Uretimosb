<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
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

