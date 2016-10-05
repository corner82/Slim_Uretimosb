<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
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
  class MiddlewareSecurity extends \Slim\Middleware\MiddlewareHMAC implements \Security\Forwarder\PrivateKeyNotFoundInterface,
                                                                \Security\Forwarder\PrivateTempKeyNotFoundInterface,
                                                                \Security\Forwarder\PublicKeyRequiredInterface,
                                                                \Security\Forwarder\PublicKeyNotFoundInterface,
                                                                \Security\Forwarder\PublicKeyTempNotFoundInterface,
                                                                \Security\Forwarder\PublicKeyTempRequiredInterface,
                                                                \Security\Forwarder\UserNotRegisteredInterface
{
      
    /**
     * service pk temp required or not
     * @var mixed boolean | null
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3 27/01/2016
     */
    protected $isServicePkTempRequired = null;
      
    /**
     * determines what will be done if private temp key not found
     * @author Mustafa Zeynel Dağlı
     * @var boolean
     */
     protected $privateKeyTempNotFoundRedirect = true;
      
    /**
     * determine if public key not found
     * @var boolean | null
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3 27/01/2016
     */
    protected $isPublicTempKeyNotFoundRedirect = true;
    
    /**
     * determine if private key not found
     * @var boolean | null
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    protected $isPrivateKeyNotFoundRedirect = true;
    
    /**
     * determine if public key not found
     * @var boolean | null
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    protected $isPublicKeyNotFoundRedirect = true;
    
    /**
     * determine if company public key not found
     * @var boolean | null
     * @author Mustafa Zeynel Dağlı
     * @since  10/06/2016
     */
    protected $isPublicCompanyKeyNotFoundRedirect = true;
    
    /**
     * determine if user not registered
     * @var boolean | null
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    protected $isUserNotRegisteredRedirect = true;
      
    /**
     * Constructor
     * @param  array  $settings
     */
    public function __construct($settings = array())
    {
        parent::__construct();
    }
    
    /**
     * get if to redirect due to public key not found process
     * @return boolean 
     * @author Mustafa Zeynel Dağlı
     * @since  10/06/2016
     */
    public function getCompanyPublicKeyNotFoundRedirect() {
        return $this->isPublicCompanyKeyNotFoundRedirect;
    }
    
    /**
     * set if to redirect due to public key not found process
     * @param boolean | null $boolean 
     * @author Mustafa Zeynel Dağlı
     * @since  10/06/2016
     */
    public function setCompanyPublicKeyNotFoundRedirect($boolean = null) {
        $this->isPublicCompanyKeyNotFoundRedirect = $boolean;
    }

    /**
     * public key not found process is being evaluated here
     * @author Mustafa Zeynel Dağlı
     * @since  10/06/2016
     */
    public function companyPublicKeyNotFoundRedirect() { 
        if($this->app->isServiceCpkRequired && $this->isPublicCompanyKeyNotFoundRedirect) {
             $forwarder = new \Utill\Forwarder\PublicCompanyNotFoundForwarder();
             $forwarder->setParameters($this->getAppRequestParams());
             $forwarder->redirect();  
         } else {
             return true;
         }
    }
    
    /**
     * if user id and company id does not match , rest api forwarded here
     * inherit classes
     * @author Mustafa Zeynel Dağlı
     * @since version  10/06/2016
     */
    public function userNotBelongCompany() {
        $forwarder = new \Utill\Forwarder\UserNotBelongCompanyForwarder;
        $forwarder->redirect();
    }
    
    /**
     * get if to redirect due to user not registered  process
     * @return boolean
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function getUserNotRegisteredRedirect() {
        return $this->isUserNotRegisteredRedirect;
    }
    
    /**
     * set if to redirect due to user not registered  process
     * @param boolean $boolean
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function setUserNotRegisteredRedirect($boolean = null) {
        $this->isUserNotRegisteredRedirect = $boolean;
    }
    
    /**
     * user not registered process is being evaluated here
     * inherit classes
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function userNotRegisteredRedirect() {
        if($this->app->isServicePkRequired && $this->isUserNotRegisteredRedirect) {
            $forwarder = new \Utill\Forwarder\UserNotRegisteredForwarder();
            $forwarder->redirect();
        } else {
            return true;
        }
    }
    
    /**
     * get if to redirect due to public key not found process
     * @return boolean 
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function getPublicKeyNotFoundRedirect() {
        return $this->isPublicKeyNotFoundRedirect;
    }
    
    /**
     * set if to redirect due to public key not found process
     * @param boolean | null $boolean 
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function setPublicKeyNotFoundRedirect($boolean = null) {
        $this->isPublicKeyNotFoundRedirect = $boolean;
    }

    /**
     * public key not found process is being evaluated here
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function publicKeyNotFoundRedirect() {
        if($this->app->isServicePkRequired && $this->isPublicKeyNotFoundRedirect) {
             $forwarder = new \Utill\Forwarder\PublicNotFoundForwarder();
             $forwarder->setParameters($this->getAppRequestParams());
             $forwarder->redirect();  
         } else {
             return true;
         }
    }

    /**
     * get if to redirect due to private key not found process
     * @return type
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function getPrivateKeyNotFoundRedirect() {
        return $this->isPrivateKeyNotFoundRedirect;
    }
    
    /**
     * set if to redirect due to private key not found process
     * @param boolean $boolean
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function setPrivateKeyNotFoundRedirect($boolean = null) {
        $this->isPrivateKeyNotFoundRedirect = $boolean;
    }
    
    /**
     * private key not found process is being evaluated here
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function privateKeyNotFoundRedirect() {
        if($this->app->isServicePkRequired && $this->isPrivateKeyNotFoundRedirect) {
            $forwarder = new \Utill\Forwarder\PrivateNotFoundForwarder();
            $forwarder->redirect();
        } else {
            return true;
        }
    }

    /**
    * set if public / private key controler to be worked
    * @return boolean
    * @author Mustafa Zeynel Dağlı
    * @since version 0.3
    */
    public function servicePkRequired() {
        if($this->app->isServicePkRequired == null) {
             $params = $this->getAppRequestParams();
             //print_r($params);
             if(substr(trim($params['url']),0,2) == 'pk' && 
                     substr(trim($params['url']),0,6) != 'pktemp') {
                $this->app->isServicePkRequired = true;
                return $this->app->isServicePkRequired ;
             }
             $this->app->isServicePkRequired = false;
             return $this->app->isServicePkRequired;
         } else {
             return $this->app->isServicePkRequired;  
         }
    }
    
    /**
    * set if public / private key controler to be worked
    * @return boolean
    * @author Mustafa Zeynel Dağlı
    * @since  10/06/2016
    */
    public function serviceCpkRequired() {
        if($this->app->isServiceCpkRequired == null) {
             $params = $this->getAppRequestParams();
             if(substr(trim($params['url']),0,5) == 'pkcpk') {
                $this->app->isServiceCpkRequired = true;
                return $this->app->isServiceCpkRequired ;
             }
             $this->app->isServiceCpkRequired = false;
             return $this->app->isServiceCpkRequired;
         } else {
             return $this->app->isServiceCpkRequired;
         }
    }

    /**
     * Call
     */
    public function call()
    {
        $this->servicePkRequired();
        
        /**
         * determine if public key temp control to be done
         * @author Mustafa Zeynel Dağlı
         * @since 0.3 27/01/2016
         * @todo after detail test code description block will be removed
         */
        $this->servicePkTempRequired();
        
        /**
         * determine if company public key  control to be done
         * @author Mustafa Zeynel Dağlı
         * @since  10/06/2016
         * @todo after detail test code description block will be removed
         */
        $this->serviceCpkRequired();   
        
        $params = $this->getAppRequestParams();
        $requestHeaderParams = $this->getRequestHeaderData();
        
        /**
         * company public  key processes wrapper
         * @author Mustafa Zeynel Dağlı
         * @since  10/06/2016
         * @todo after detailed test code description block will be removed
         */
        $this->publicCompanyKeyProcessControler($requestHeaderParams, $params);
        
        /**
         * public  key processes wrapper
         * @author Mustafa Zeynel Dağlı
         * @since 0.3 27/01/2016
         * @todo after detailed test code description block will be removed
         */
        $this->publicKeyProcessControler($requestHeaderParams);
        
        /**
         * public  key temp processes wrapper
         * @author Mustafa Zeynel Dağlı
         * @since 0.3 27/01/2016
         * @todo after detailed test code description block will be removed
         */
        $this->publicKeyTempProcessControler($requestHeaderParams);
        
        $this->next->call();
    }
    
    /**
     * public key temp control processes has been wrapped
     * @param array $params
     * @return mixed array | null
     */
    private function publicKeyTempProcessControler($requestHeaderParams) {
        if($this->app->isServicePkTempRequired) {
            $resultSet;
            /**
            * controlling public key temp from request header
            * public key temp not found forwarder is in effect then making forward
            * @since version 0.3 27/01/2016
            */
           if((!isset($requestHeaderParams['X-Public-Temp']) || $requestHeaderParams['X-Public-Temp']==null)) {
               $this->publicKeyTempNotFoundRedirect();
           }
           
           /**
            * getting private key due to public key
            * @author Mustafa Zeynel Dağlı
            * @since 05/01/2016 version 0.3
            */
           if(isset($requestHeaderParams['X-Public-Temp'])) { 
               $resultSet = $this->app->getBLLManager()->get('blLoginLogoutBLL')->pkTempControl(array('pktemp'=>$requestHeaderParams['X-Public-Temp']));
               //print_r($resultSet);
               if($resultSet[0]['sf_private_key_value_temp'] == null) $this->privateKeyTempNotFoundRedirect();
           }
           return $resultSet;

        } else {
            return null;
        }
    }
    
    /**
     * public key control processes has been wrapped
     * @param array $requestHeaderParams
     * @return mixed array | null
     * @author Mustafa Zeynel Dağlı
     * @since 0.3 27/01/2016
     */
    private function publicKeyProcessControler($requestHeaderParams) {
        if($this->app->isServicePkRequired) {
            /**
            * controlling public key if public key is necessary for this service and
            * public key not found forwarder is in effect then making forward
            * @since version 0.3 06/01/2016
            */
           if((!isset($requestHeaderParams['X-Public']) || $requestHeaderParams['X-Public']==null)) {
               $this->publicKeyNotFoundRedirect();
           }

           /**
            * getting public key if user registered    
            * @author Mustafa Zeynel Dağlı
            * @since 06/01/2016 version 0.3
            */
           if(isset($requestHeaderParams['X-Public'])) {
               $resultSet = $this->app->getBLLManager()->get('blLoginLogoutBLL')->pkIsThere(array('pk' => $requestHeaderParams['X-Public']));
               //print_r($resultSet);
               if(!isset($resultSet[0]['?column?'])) $this->userNotRegisteredRedirect();
           }

           /**
            * getting private key due to public key
            * @author Mustafa Zeynel Dağlı
            * @since 05/01/2016 version 0.3
            */
           if(isset($requestHeaderParams['X-Public'])) { 
               $resultSet = $this->app->getBLLManager()->get('blLoginLogoutBLL')->pkControl(array('pk'=>$requestHeaderParams['X-Public']));
               //print_r($resultSet);
               if($resultSet[0]['sf_private_key_value'] == null) $this->privateKeyNotFoundRedirect();
           }
           return $resultSet;
        } else {
            return null;
        }
        
    }
    
    /**
     * company public key control processes has been wrapped
     * @param array $requestHeaderParams
     * @return mixed array | null
     * @author Mustafa Zeynel Dağlı
     * @since  10/06/2016
     */
    private function publicCompanyKeyProcessControler($requestHeaderParams, $params) {
        if($this->app->isServiceCpkRequired) {
            /**
            * controlling public key if public key is necessary for this service and
            * public key not found forwarder is in effect then making forward
            * @since  10/06/2016
            */
           if(!isset($params['cpk']) ) {
               $this->companyPublicKeyNotFoundRedirect();
           }

           /**
            * controlling user belongs to company   
            * @author Mustafa Zeynel Dağlı
            * @since 10/06/2016 
            */
           if(isset($requestHeaderParams['X-Public'])) {
               $resultSet = $this->app->getBLLManager()->get('blLoginLogoutBLL')->isUserBelongToCompany($requestHeaderParams,
                                                                                                        $params);
               //print_r($resultSet);
               if(empty($resultSet)) $this->userNotBelongCompany();
           } else {
               $this->publicKeyNotFoundRedirect();
           }
           return $resultSet;
        } else {
            return null;
        }
        
    }
    
    /**
     * public key temp not found process is being evaluated here
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public function publicKeyTempNotFoundRedirect() {
        if($this->isServicePkTempRequired && $this->isPublicTempKeyNotFoundRedirect) {
             $forwarder = new \Utill\Forwarder\PublicTempNotFoundForwarder();
             $forwarder->redirect();  
         } else {
             return true;
         }
    }

    /**
     * set variable for public key temp not found strategy
     * @param type $boolean
     * @author Mustafa Zeynel Dağlı
     * @since 27/01/2016  
     */
    public function getPublicKeyTempNotFoundRedirect() {
        return $this->isPublicTempKeyNotFoundRedirect;
    }
    
    /**
     * get variable for public key temp not found strategy
     * @return boolean
     * @author Mustafa Zeynel Dağlı
     * @since 27/01/2016
     */
    public function setPublicKeyTempNotFoundRedirect($boolean = null) {
        $this->isPublicTempKeyNotFoundRedirect = $boolean;
    }
    
    /**
     * public key temp not found process function, will be overridden by
     * inherit classes
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3 27/01/2016
     */
    public function privateKeyTempNotFoundRedirect() {
        if($this->isServicePkTempRequired && $this->privateKeyTempNotFoundRedirect) {
            $forwarder = new \Utill\Forwarder\PrivateTempNotFoundForwarder();
            $forwarder->redirect();
        } else {
            return true;
        }
    }

    /**
     * get variable for private key temp not found strategy
     * @return boolean
     * @author Mustafa Zeynel Dağlı
     * @since 27/01/2016
     */
    public function getPrivateKeyTempNotFoundRedirect() {
        return $this->privateKeyTempNotFoundRedirect;
    }

    /**
     * set variable for private key temp not found strategy
     * @param type $boolean
     * @author Mustafa Zeynel Dağlı
     * @since 27/01/2016  
     */
    public function setPrivateKeyTempNotFoundRedirect($boolean = null) {
        $this->privateKeyNotFoundRedirection = $boolean;
    }

    /**
      * 
      * @return boolean
      * @author Mustafa Zeynel Dağlı
      * @since version 0.3 27/01/2016
      */
    public function servicePkTempRequired() {
         if($this->app->isServicePkTempRequired == null) {
             $params = $this->getAppRequestParams();
             //print_r($params);
             if(substr(trim($params['url']),0,6) == 'pktemp') {
                $this->app->isServicePkTempRequired = true;
                return $this->app->isServicePkTempRequired ;
             }
             $this->app->isServicePkTempRequired = false;
             return $this->app->isServicePkTempRequired;
         } else {
             return $this->app->isServicePkTempRequired;
         }
    }

}