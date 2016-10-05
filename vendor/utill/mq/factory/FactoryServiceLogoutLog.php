<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\MQ\Factory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class FactoryServiceLogoutLog  implements FactoryInterface{

    public function createService(ServiceLocatorInterface $serviceLocator) {
        
        $publicKey = $serviceLocator->get('servicePublicKeyReader'); 
        
        $request = $serviceLocator->get('Request');
        $uri = $request->getUri();
        $scheme = $uri->getScheme();
        $host = $uri->getHost();   
        $base = sprintf('%s://%s', $scheme, $host);

        $remoteAddr = $request->getServer('REMOTE_ADDR');
        $params = $request->getContent();
        $method = $request->getMethod();
        
        /**
        * sends login info to message queue
        * @author Mustafa Zeynel Dağlı
        * @todo after tests ,  thif feature will be added as a service manager entity
        */
        $PageEntryLogMQ = new \Utill\MQ\LoginLogoutMQ();
        $PageEntryLogMQ->setChannelProperties(array('queue.name' => \Utill\MQ\LoginLogoutMQ::QUEUE_NAME));
        $message = new \Utill\MQ\MessageMQ\MQMessageLoginLogout();
       ;

       $message->setMessageBody(array('message' => 'Kullanıcı authentication log out servis!', 
                                      //'s_date'  => date('l jS \of F Y h:i:s A'),
                                      'log_datetime'  => date('Y-m-d G:i:s '),
                                      'pk' => $publicKey,
                                      'url' => $base,
                                      'path' =>$host,
                                      'method' => $method,
                                      'ip' => $remoteAddr,
                                      'params' => json_encode($params, $options),
                                      'type_id' => \Utill\MQ\MessageMQ\MQMessageLoginLogout::LOGOUT_OPERATION,
                                      'logFormat' => 'database'));
       $message->setMessageProperties(array('delivery_mode' => 2,
                                            'content_type' => 'application/json'));
       $PageEntryLogMQ->setMessage($message->setMessage());
       $PageEntryLogMQ->basicPublish();
        
        return true;
        
        
    }

}
