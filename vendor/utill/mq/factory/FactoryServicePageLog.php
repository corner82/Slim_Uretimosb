<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/sanalfabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\MQ\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FactoryServicePageLog  implements FactoryInterface{

    public function createService(ServiceLocatorInterface $serviceLocator) {
        
        $serviceLogMQ = new \Utill\MQ\restEntryMQ();
        $slimApp = $serviceLocator->get('slimApp');
        $request = $slimApp->container['request'];
        $params = $request->params();
        
        $requestHeaderData = $request->headers();
        
        $base = $request->getRootUri();
        $path = $request->getResourceUri();
        $ip = $request->getIp();
        $method = $request->getMethod();
        
        /**
        * sends login info to message queue
        * @author Mustafa Zeynel Dağlı
        * @todo after tests ,  thif feature will be added as a service manager entity
        */
       $PageEntryLogMQ = new \Utill\MQ\PageEntryLogMQ();
       $PageEntryLogMQ->setChannelProperties(array('queue.name' => \Utill\MQ\abstractMQ::PAGE_ENTRY_LOG_QUEUE_NAME));
       $message = new \Utill\MQ\MessageMQ\MQMessagePageEntryLog();
       ;

       $message->setMessageBody(array('message' => 'Kullanıcı sayfa giris log servis!', 
                                      //'s_date'  => date('l jS \of F Y h:i:s A'),
                                      'log_datetime'  => date('Y-m-d G:i:s '),
                                      'pk' => $requestHeaderData['X-Public'],
                                      'url' => $base,
                                      'path' => $controller.'/'.$action,
                                      'method' => $method,
                                      'ip' => $remoteAddr,
                                      'params' => $params,
                                      'type_id' => \Utill\MQ\MessageMQ\MQMessagePageEntryLog::PAGE_ENTRY_OPERATIN,
                                      'logFormat' => 'database'));
       $message->setMessageProperties(array('delivery_mode' => 2,
                                            'content_type' => 'application/json'));
       $PageEntryLogMQ->setMessage($message->setMessage());
       $PageEntryLogMQ->basicPublish();
        
        return true;
        
        
    }

}
