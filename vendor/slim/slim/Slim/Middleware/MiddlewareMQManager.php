<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Slim\Middleware;


 
 /**
  * Flash
  *
  * This is middleware for a Slim application that enables
  * RAbbitMQ messaging between HTTP requests. 
  *
  * @package    Slim
  * @author     Mustafa Zeynel Dağlı
  * @since      
  */
  class MiddlewareMQManager extends \Slim\Middleware 
{
   
    /**
     * Zend service manager Config instance in Slimm Application
     * @var Zend\ServiceManager\Config
     */
    protected $serviceManagerConfig;
    /**
     * Constructor
     * @param  array  $settings
     */
    public function __construct()
    {
    }

    /**
     * Call
     */
    public function call()
    {
        //print_r('--middlewareMQManager call()--');
        $MQManagerConfigObject = new \Utill\MQ\Manager\MQManagerConfig;
        $managerConfig = new \Zend\ServiceManager\Config($MQManagerConfigObject->getConfig());
        $MQManager = new \Utill\MQ\Manager\MQManager($managerConfig);
        $MQManager->setService('slimApp', $this->app);
        $this->app->setMQManager($MQManager);
        //$test = $BLLManager->get('reportConfigurationBLL');
        //print_r($test->getSlimApp());
        $this->next->call();
    }

 

}

