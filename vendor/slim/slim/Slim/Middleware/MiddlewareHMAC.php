<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Slim\Middleware;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
 
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
  class MiddlewareHMAC extends \Slim\Middleware implements \Slim\Interfaces\interfaceRequestParams, 
                                                            \Slim\Interfaces\interfaceRequest,
                                                            \Slim\Interfaces\interfaceRequestCustomHeaderData,
                                                            \Utill\MQ\ImessagePublisher
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var array
     */
    protected $messages;
    
    /**
     * request header data
     * @var array
     */
    protected $requestHeaderData;
    
    /**
     * App request parameters
     * @var array
     */
    protected $appRequestParams = array();
    
    /**
     * App request object
     * @var \Slim\Http\Request
     */
    protected $requestObj;
    
    /**
     * hmac object
     * @var \Hmac\Hmac
     */
    protected $hmacObj;
    
    /**
     * request expire time as seconds
     * @var int
     */
    protected $requestExpireTime = 60;

    /**
     * Constructor
     * @param  array  $settings
     */
    public function __construct($settings = array())
    {
        
    }
    
    /**
     * get request custom header info
     * @return array | null
     * @author Mustafa Zeynel Dağlı
     */
    public function getRequestHeaderData()  {
        if($this->requestHeaderData == null)   {
            $this->setRequestHeaderData();
            return $this->requestHeaderData;
        } else {
            return $this->requestHeaderData;
        }
    }
    
    /**
     * set request custom header info into array
     * @return array
     * @author Mustafa Zeynel Dağlı
     * @link http://php.net/manual/en/function.getallheaders.php
     */
    public function setRequestHeaderData($requestHeaderData = array())  {
        $requestObj = $this->getAppRequest();
        return $this->requestHeaderData = $requestObj->headers();
    }
    
    /**
     * Call
     */
    public function call()
    {
        //print_r('--middlewareHMAC call()--');
        //fopen('zeyn.txt');
        if($this->app->isServicePkRequired ) {
            $this->evaluateExpireTime();
            $this->evaluateHash();
        }
        if($this->app->isServicePkTempRequired ) {
            $this->evaluateExpireTime();
            $this->evaluateHashByTempKey();
        }
        
        $this->next->call();
    }
    
    /**
     * message wrapper function
     * @param \Exception $e
     * @author Mustafa Zeynel Dağlı
     */
    public function publishMessage($e = null, array $params = array()) {
        $exceptionMQ = new \Utill\MQ\hashMacMQ();
        //print_r('---------'.$this->app->container['settings']['hmac.rabbitMQ.queue.name'].'------');
        $exceptionMQ->setChannelProperties(array('queue.name' => $this->app->container['settings']['hmac.rabbitMQ.queue.name']));
        $message = new \Utill\MQ\MessageMQ\MQMessage();
        ;
        //$message->setMessageBody(array('testmessage body' => 'test cevap'));
        //$message->setMessageBody($e);
       
        $message->setMessageBody(array('message' => 'Hash not matched', 
                                       'time'  => date('l jS \of F Y h:i:s A'),
                                       'serial' => $this->app->container['settings']['request.serial'],
                                       'ip' => \Utill\Env\serverVariables::getClientIp(),
                                       'logFormat' => $this->app->container['settings']['hmac.rabbitMQ.logging']));
        $message->setMessageProperties(array('delivery_mode' => 2,
                                             'content_type' => 'application/json'));
        $exceptionMQ->setMessage($message->setMessage());
        $exceptionMQ->basicPublish();
    }
    
    protected function calcExpireTime() {
        
    }
    
     /**
     * get hmacObj
     * @author Okan Cıran
     */
      private function getHmacObj() {            
      if ($this->hmacObj == null) { 
            $this->setHmacObj();          
      } else {
            return $this->hmacObj;
      }       
    }  
    
    /**
     * set hmacObj
     * @author Okan Cıran
     */
     private function setHmacObj() {            
        $this->hmacObj = new \HMAC\Hmac();       
     }
     
     /**
     * get info to calculate HMAC security measures
     * @author Mustafa Zeynel Dağlı
     */
    private function evaluateHash() {
        $this->getHmacObj();
        $this->hmacObj->setRequestParams($this->getAppRequestParams());
        $this->hmacObj->setPublicKey($this->getRequestHeaderData()['X-Public']);
        $this->hmacObj->setNonce($this->getRequestHeaderData()['X-Nonce']);
        // bu private key kısmı veri tabanından alınır hale gelecek
        $BLLLogLogout = $this->app->getBLLManager()->get('blLoginLogoutBLL');
        
        /**
         * private key due to public key,
         * if public key not found request redirected
         * @author Mustafa Zeynel Dağlı
         * @since 05/01/2016
         */
        $resultset = $BLLLogLogout->pkControl(array('pk'=>$this->getRequestHeaderData()['X-Public']));
        //print_r($resultset);
        $publicNotFoundForwarder = new \Utill\Forwarder\publicNotFoundForwarder();
        if(empty($resultset[0])) $publicNotFoundForwarder->redirect();
        
        
        $this->hmacObj->setPrivateKey($resultset[0]['sf_private_key_value']);
        //$this->hmacObj->setPrivateKey('zze249c439ed7697df2a4b045d97d4b9b7e1854c3ff8dd668c779013653913572e');
        $this->hmacObj->makeHmac();
        //print_r($hmacObj->getHash()); 
        
        if($this->hmacObj->getHash() != $this->getRequestHeaderData()['X-Hash'])  {
            //print_r ('-----hash eşit değil----');
            $this->publishMessage();
            $hashNotMatchForwarder = new \Utill\Forwarder\hashNotMatchForwarder();
            $hashNotMatchForwarder->redirect();
            
        } else {
           //print_r ('-----hash eşit ----'); 
        }
    }
    
    /**
     * get info to calculate HMAC security measures
     * @author Mustafa Zeynel Dağlı
     * @since 0.3 27/01/2016
     */
    private function evaluateHashByTempKey() {
        $this->getHmacObj();
        $this->hmacObj->setRequestParams($this->getAppRequestParams());
        $this->hmacObj->setPublicKey($this->getRequestHeaderData()['X-Public-Temp']);
        $this->hmacObj->setNonce($this->getRequestHeaderData()['X-Nonce']);
        // bu private key kısmı veri tabanından alınır hale gelecek
        $BLLLogLogout = $this->app->getBLLManager()->get('blLoginLogoutBLL');
        
        /**
         * private key due to public key,
         * if public key not found request redirected
         * @author Mustafa Zeynel Dağlı
         * @since 27/01/2016
         */
        $resultset = $BLLLogLogout->pkTempControl(array('pktemp'=>$this->getRequestHeaderData()['X-Public-Temp']));
        //print_r($resultset);
        $publicTempNotFoundForwarder = new \Utill\Forwarder\PublicTempNotFoundForwarder();
        if(empty($resultset[0])) $publicTempNotFoundForwarder->redirect();
        
        $this->hmacObj->setPrivateKey($resultset[0]['sf_private_key_value_temp']);
        //$this->hmacObj->setPrivateKey('zze249c439ed7697df2a4b045d97d4b9b7e1854c3ff8dd668c779013653913572e');
        $this->hmacObj->makeHmac();

        if($this->hmacObj->getHash() != $this->getRequestHeaderData()['X-Hash-Temp'])  {
            //print_r ('-----hash eşit değil----');
            $this->publishMessage();
            $hashNotMatchForwarder = new \Utill\Forwarder\hashNotMatchForwarder();
            $hashNotMatchForwarder->redirect();
            
        } else {
           //print_r ('-----hash eşit ----'); 
        }
    }
    
    /**
     * get time difference
     * @author Okan Cıran
     */
    private function evaluateExpireTime() { 
        if(isset($this->getRequestHeaderData()['X-TimeStamp'])) {
            $this->getHmacObj();
            $encryptClass = $this->app->setEncryptClass();
            $this->hmacObj->setTimeStamp($encryptClass->decrypt($this->getRequestHeaderData()['X-TimeStamp']));
            $timeDiff = $this->hmacObj->timeStampDiff();
            //print_r('---'.$timeDiff.'---');      
            //print_r('zzz'.$this->getRequestHeaderData()['X-TimeStamp'].'zzz' );
            //print_r('zzz'.$encryptClass->decrypt($this->getRequestHeaderData()['X-TimeStamp']).'zzz' );

            if($timeDiff > $this->requestExpireTime)  {
                //print_r ('-----expire time exceeded----');
                $hashNotMatchForwarder = new \Utill\Forwarder\timeExpiredForwarder();
                $hashNotMatchForwarder->redirect();

            } else {
               //print_r ('-----expire time not exceeded----'); 
            }
            return null;
        } 
        //throw new Exception('Middleware evaluateExpireTime time stamp coul not find');
        $timeStampNotFoundForwarder = new \Utill\Forwarder\TimeStampNotFoundForwarder();
        $timeStampNotFoundForwarder->redirect();
        
    }

    public function getAppRequestParams() {
        if(empty($this->appRequestParams)) $this->appRequestParams = $this->setAppRequestParams();
        return $this->appRequestParams;
    }

    public function setAppRequestParams($appRequestParams = array()) {
        $requestHeaderData = [];
        $request = $this->app->container['request'];
        return $request->params();
        //return $this->app['request']->params();
    }

    /**
     * get Application request object
     * @return \Slim\Http\Request
     * @author Mustafa Zeynel Dağlı
     */
    public function getAppRequest() {
        if($this->requestObj == null) $this->requestObj = $this->setAppRequest();
        return $this->requestObj;
    }

    /**
     * set Application request object
     * @return \Slim\Http\Request
     * @author Mustafa Zeynel Dağlı
     */
    public function setAppRequest(\Slim\Http\Request $request = null) {
        return $this->app->container['request'];
        
    }

}