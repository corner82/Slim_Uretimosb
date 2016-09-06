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
  * Flash messaging between HTTP requests. This allows you
  * set Flash messages for the current request, for the next request,
  * or to retain messages from the previous request through to
  * the next request.
  *
  * @package    Slim
  * @author     Josh Lockhart
  * @since      1.6.0
  */
  class MiddlewareBLLManager extends \Slim\Middleware 
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
     * set request custom header info into array
     * @return array
     * @author Mustafa Zeynel Dağlı
     * @link http://php.net/manual/en/function.getallheaders.php
     */
    /*public function setRequestHeaderData($requestHeaderData = array())  {
        $requestObj = $this->getAppRequest();
        return $this->requestHeaderData = $requestObj->headers();
    }*/
    
    /**
     * Call
     */
    public function call()
    {
        //print_r('--middlewareDalManager call()--');
        $BLLManagerConfigObject = new \BLL\BLLManagerConfig;
        $managerConfig = new \Zend\ServiceManager\Config($BLLManagerConfigObject->getConfig());
        $BLLManager = new \BLL\BLLManager($managerConfig);
        $BLLManager->setService('slimApp', $this->app);
        $this->app->setBLLManager($BLLManager);
        //$test = $BLLManager->get('reportConfigurationBLL');
        //print_r($test->getSlimApp());
        $this->next->call();
    }

 

}

