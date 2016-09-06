<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\MQ\MessageMQ;

class MQMessage extends \Utill\MQ\MessageMQ\abstractMQMessage {
    
public function __construct() {
    
}

/**
 * set message properties
 * @param array $messageProperties
 */
public function setMessageProperties(array $messageProperties = array() ) {
    $this->messageProperties = array_merge($this->messageProperties, $messageProperties);
    //print_r($this->messageProperties);
}

/**
 * rabbitMQ message body
 * @param type $messageBody
 * @return array | object | string
 */
public function setMessageBody($messageBody) {
    if(is_array($messageBody)) {
        $this->messageBody = json_encode($messageBody);
        return;
    }
    
    if(is_object($messageBody)) {
        $this->messageBody = json_encode(serialize($messageBody));
        return;
    }
    
    if(is_string($messageBody)) {
        $this->messageBody = $messageBody;
        return;
    }
    return;
}

/**
 * set rabbitMQ message object
 * @return \PhpAmqpLib\Message\AMQPMessage | null
 */
public function setMessage() {
    if($this->messageBody == null) return;
    /**
    $msg = new \PhpAmqpLib\Message\AMQPMessage(  
        //2,
        //json_encode(serialize($connection)),
        //json_encode(serialize(array('test' => 'test cevap', 'test1' => 'test cevap', 'test2' => 'test cevap'))),
        json_encode(array('test' => 'test cevap', 'test1' => 'test cevap', 'test2' => 'test cevap')),
        array('delivery_mode' => 2,# make message persistent, so it is not lost if server crashes or quits
              'content_type' => 'application/json') 
        );
    */
        $this->message = new \PhpAmqpLib\Message\AMQPMessage(  
        $this->messageBody,
        $this->messageProperties
    );
        return $this->message;
    
}

}

