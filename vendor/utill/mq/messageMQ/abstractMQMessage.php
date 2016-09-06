<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\MQ\MessageMQ;

/**
 * rabbitMQ message wrapper
 * @author Mustafa Zeynel Dağlı
 */
abstract class abstractMQMessage {
    
     /**
      * rabbitMQ message properties
      * @var array
      */
     protected $messageProperties = array('delivery_mode' => 2,# make message persistent, so it is not lost if server crashes or quits
                  'content_type' => 'application/json');
     
     /**
      * rabbitMQ message object
      * @var \PhpAmqpLib\Message\AMQPMessage 
      */
    protected $message;
    
    /**
     * rabbitMQ message body
     * @var string 
    */
    protected $messageBody;

    /**
      * creates a new rabbitMQ message body
      * will be overriden in extended classes
      * @var array  $messageBody 
      */
     abstract public function setMessageBody($messageBody);
     
     /**
      * get rabbitMQ message body
      * @return array | object | string
      */
     public function getMesssageBody() {
         return $this->messageBody;
     }

    /**
      * creates a new rabbitMQ message 
      * will be overriden in extended classes
      */
     abstract public function setMessage();
     
     /**
      * get rabbitMQ message object
      * @return \PhpAmqpLib\Message\AMQPMessage | null
      */
     public function getMessage() {
         return$this->message;
     }

     /**
      * creates a new rabbitMQ message properties
      * will be overriden in extended classes
      * @var array  $messageProperties 
      */
     abstract public function setMessageProperties(array $messageProperties = array());
     
     /**
      * get rabbitMQ message properties
      * @return array | null
      */
     public function getMessageProperties() {
         return $this->messageProperties;
     }
}

