<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */
namespace Utill\MQ\Factory;


/**
 * Class using Zend\ServiceManager\FactoryInterface
 * created to be used by DAL MAnager
 * @author Mustafa Zeynel Dağlı
 * @todo first test to publish exceptions by manager has failed
 * if further test do not work please erase 'FactoryServiceExceptionsMQ' function below 
 * and related entery in MQMAnager config class
 */
class FactoryServiceExceptions implements \Zend\ServiceManager\FactoryInterface {
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $exceptionMQ = new \Utill\MQ\exceptionMQ();
        $slimApp = $serviceLocator->get('slimApp');
        $exceptionMQ->setChannelProperties(array('queue.name' => $slimApp->container['settings']['exceptions.rabbitMQ.queue.name']));
        $message = new \Utill\MQ\MessageMQ\MQMessage();
        ;

        /*$message->setMessageBody(array('message' => $e->getMessage(), 
                                       'file' => $e->getFile(),
                                       'line' => $e->getLine(),
                                       'trace' => $e->getTraceAsString(),
                                       'time'  => date('l jS \of F Y h:i:s A'),
                                       'serial' => $this->app->container['settings']['request.serial'],
                                       'logFormat' => $this->app->container['settings']['exceptions.rabbitMQ.logging']));*/
        $message->setMessageProperties(array('delivery_mode' => 2,
                                             'content_type' => 'application/json'));
        $exceptionMQ->setMessage($message->setMessage());
        //$exceptionMQ->basicPublish();
        return $exceptionMQ;
        
    }

}

