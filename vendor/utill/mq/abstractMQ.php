<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\MQ;

/**
 * RabbitMQ message queue wrapper abstract class
 * @author Mustafa Zeynel Dağlı
 */
abstract class abstractMQ  {
    
  const QUEUE_NAME = 'userLogin2_queue'; 
  const PAGE_ENTRY_LOG_QUEUE_NAME = 'userPageEntryLog2_queue'; 
  const SERVICE_ENTRY_LOG_QUEUE_NAME = 'serviceEntryLog2_queue'; 
  
 /**
 * rabbitMQ connection
  * @var PhpAmqpLib\Connection
 */
protected $connection;

/**
 * rabbitMQ connection server
 * @var string 
 */
//protected $server = 'localhost';
//protected $server = '10.18.2.206';
protected $server = 'localhost';   

/**
 * rabbitMQ connection port
 * @var int 
 */
protected $port = 5672;

/**
 * rabbitMQ user
 * @var string
 */
protected $user = 'guest';
//protected $user = 'test';

/**
 * rabbitMQ connection password
 * @var string
 */
protected $password = 'guest';
//protected $password = 'test';

/**
 * rabbitMQ queue name
 * @var string | null
 */
protected $queueName ;

/**
 * rabbitMQ channel 
 * @var \PhpAmqpLib\Channel
 */
protected $channel ;

/**
 * rabbitMQ message
 * @var \PhpAmqpLib\Message\AMQPMessage 
 */
protected $message;

/**
 * rabbitMQ channel properties
 * @var array
 */
protected $channelProperties = array(
        'queue.name' => 'exception_queue',
        'passive' => false,
        'durable' => true,
        'exclusive' => false,
        'auto_delete' => false
);

/**
* rabbitMQ basic publish wrapper function
* @return null
*/
public function basicPublish() {
   if($this->message == null) return null;
   $this->setConnection();

   try {
      $this->channel = $this->connection->channel();

       $this->channel->queue_declare(
       $this->channelProperties['queue.name'],    #queue - Queue names may be up to 255 bytes of UTF-8 characters
       $this->channelProperties['passive'],       #passive - can use this to check whether an exchange exists without modifying the server state
       $this->channelProperties['durable'],       #durable, make sure that RabbitMQ will never lose our queue if a crash occurs - the queue will survive a broker restart
       $this->channelProperties['exclusive'],     #exclusive - used by only one connection and the queue will be deleted when that connection closes
       //$this->channelProperties['auto_delete']    #auto delete - queue is deleted when last consumer unsubscribes
       false
       );

       $this->channel->basic_publish(
           $this->message,     #message 
           '',                 #exchange
           $this->channelProperties['queue.name']     #routing key (queue)
           );
       $this->channel->close();
       $this->connection->close(); 
   } catch (Exception $ex) {

   }

}

/**
 * set rabbitMQ channel properties
 * @param array $channelProperties
 */
public function setChannelProperties(array $channelProperties = array()) {
    $this->channelProperties = array_merge($this->channelProperties, $channelProperties);
}

/**
 * set rabbitMQ channel properties
 * @return array
 */
public function getChannelProperties() {
    return $this->channelProperties;
}

/**
 * set rabbitMQ message object
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
public function setMessage(\PhpAmqpLib\Message\AMQPMessage $message) {
    $this->message = $message;
}

/**
 * get rabbitMQ message object
 * @return \PhpAmqpLib\Message\AMQPMessage | null
 */
public function getMessage() {
    return $this->message;
}

/**
 * set rabbitMQ connection
 * @param \PhpAmqpLib\Connection $connection
 */
public function setConnection(\PhpAmqpLib\Connection $connection = null) {
    if($connection==null) {
        try {
            $this->connection = new \PhpAmqpLib\Connection\AMQPConnection($this->server, 
                                                                          $this->port, 
                                                                          $this->user, 
                                                                          $this->password);
        }catch (Exception $ex) {
            echo $ex->getMessage();
        }
        
    } else {
        $this->connection = $connection;
    }
}

/**
 * set rabbitMQ connection
 * @return \PhpAmqpLib\Connection
 */
public function getConnection() {
    return $this->connection;
}

/**
 * set rabbitMQ connection user
 * @param string | null $user
 */
public function setUser($user = null) {
    $this->user = $user;
}

/**
 * get rabbitMQ connection user
 * @return string | null
 */
public function getUser() {
    return $this->user;
}

/**
 * set rabbitMQ connection password
 * @param string | null $password
 */
public function setPassword($password = null) {
    $this->password = $password;
}

/**
 * get rabbitMQ connection password
 * @return string | null
 */
public function getPassword() {
    return $this->password;
}

/**
 * set rabbitMQ connection server
 * @param string | null $server
 */
public function setServer($server = null) {
    $this->server = $server;
}

/**
 * get rabbitMQ connection server
 * @return string | null
 */
public function getServer() {
    return $this->server;
}

/**
 * set rabbitMQ queue name
 * @param string | null $queueName
 */
public function setQueueName($queueName = null) {
    $this->queueName = $queueName;
}

/**
 * get rabbitMQ queue name
 * @return string | null
 */
public function getQueueName() {
    return $this->queueName;
}

/**
 * set rabbitMQ channel
 * @param \PhpAmqpLib\Channel $channel
 */
public function setChannel(\PhpAmqpLib\Channel $channel) {
    $this->channel = $this->connection->channel();
}

/**
 * get rabbitMQ channel
 * @return \PhpAmqpLib\Channel
 */
public function getChannel() {
    return $this->channel;
}



/**
 * rabbitMQ connection close
 */
public function closeConnection() {
    if($this->connection!=null) $this->connection->close();
}


}
