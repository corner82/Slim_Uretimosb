<?php

namespace Slim;

use Slim\Slim;



class SlimExtended extends Slim implements  \Utill\MQ\ImessagePublisher,
                                            \DAL\DalManagerInterface,
                                            \BLL\BLLManagerInterface,
                                            \Utill\MQ\Manager\MQManagerInterface{
    
    /**
     * exceptions and rabbitMQ configuration parameters
     * @author Mustafa Zeynel Dağlı
     */
    const LOG_RABBITMQ_DATABASE = 'database';
    const LOG_RABBITMQ_FILE = 'file';
    const EXCEPTIONS_RABBITMQ_QUEUE_NAME = 'exceptions_queue';
    
    /**
     * time zones decriptions
     * @author Mustafa Zeynel Dağlı
     */
    const TIME_ZONE_ISTANBUL = 'Europe/Istanbul';

    /**
     * service pk required or not
     * @var boolean
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3
     */
    public $isServicePkRequired = null;
    
    /**
     * service pk required or not
     * @var boolean
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3 27/01/2016
     */
    public $isServicePkTempRequired = null;
    
    /**
     * service company public key required or not
     * @var boolean
     * @author Mustafa Zeynel Dağlı
     * @since  10/06/2016
     */
    public $isServiceCpkRequired = null;
    
    /**
    * @var string
    */
    protected $publicHash;
    
    /**
    * @var string
    */
    protected $privateHash;
    
    /**
    * @var string
    */
    protected $securityContent;
    
    /**
     * encrypt class obj
     * @var \Encrypt\AbstractEncrypt
     * @author Mustafa Zeynel Dağlı
     */
    protected $encryptClass;
    
    /**
     * encrypt class key
     * @var string
     * @author Mustafa Zeynel Dağlı
     */
    protected $encryptKey = 'testKey';
    
    /**
     * Zend service manager instance in Slimm Application
     * @var Zend\ServiceManager\ServiceLocatorInterface
     */
    protected $serviceManager;
    
    /**
     * DAL service manager instance extended from zend service manager in Slimm Application
     * @var DAL\DalManager
     */
    protected $dalManager;
    
    /**
     * BLL service manager instance extended from zend service manager in Slimm Application
     * @var BLL\BLLManager
     */
    protected $BLLManager;
    
    /**
     * MQ service manager instance extended from zend service manager in Slimm Application
     * @var Utill\MQ\MQManager
     */
    protected $mqManager;

    public function __construct(array $userSettings = array()) {
        parent::__construct($userSettings);
    }
    
    /**
     * gets MQ manager instance extended from 
     * Zend service manager instance from Slimm Application
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     * @author Mustafa Zeynel Dağlı
     */
    public function getMQManager() {
        return $this->mqManager;
    }

    /**
     * gets MQ manager instance extended from 
     * Zend service manager instance from Slimm Application
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     * @author Mustafa Zeynel Dağlı
     */
    public function setMQManager(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->mqManager = $serviceManager;
    }
    
    /**
     * gets BLL manager instance extended from 
     * Zend service manager instance from Slimm Application
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     * @author Mustafa Zeynel Dağlı
     */
    public function getBLLManager() {
        return $this->BLLManager;
    }
    
    /**
     * injects BLL manager instance extended from Zend
     * service manager instance in Slimm Application
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     * @return \Slim\SlimExtended
     * @author Mustafa Zeynel Dağlı
     */
    public function setBLLManager(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        $this->BLLManager = $serviceManager;
    }
    
    /**
     * injects Dal manager instance extended from Zend
     * service manager instance in Slimm Application
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     * @return \Slim\SlimExtended
     * @author Mustafa Zeynel Dağlı
     */
    public function setDalManager(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        /*if ($this->serviceManager == null ) {
            $this->serviceManager = $serviceManager;
        }*/
        $this->dalManager = $serviceManager;
        return $this;
    }
    
    /**
     * gets Dal manager instance extended from 
     * Zend service manager instance from Slimm Application
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     * @author Mustafa Zeynel Dağlı
     */
    public function getDAlManager() {
        return $this->dalManager;
    }
    
    /**
     * injects Zend service manager instance in Slimm Application
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     * @return \Slim\SlimExtended
     * @author Mustafa Zeynel Dağlı
     */
    public function setServiceManager(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager) {
        /*if ($this->serviceManager == null ) {
            $this->serviceManager = $serviceManager;
        }*/
        $this->serviceManager = $serviceManager;
        return $this;
    }
    
    /**
     * gets Zend service manager instance from Slimm Application
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     * @author Mustafa Zeynel Dağlı
     */
    public function getServiceManager() {
        return $this->serviceManager;
    }


    /**
     * default settings extended for rabbitMQ and exceptions managing configuration
     * Get default application settings
     * @return array
     * @author Mustafa Zeynel Dağlı
     */
    public static function getDefaultSettings()
    {
        return array(
            // Application
            'mode' => 'development',
            // Debugging
            'debug' => true,
            // Logging
            'log.writer' => null,
            'log.level' => \Slim\Log::DEBUG,
            'log.enabled' => true,
            // View
            'templates.path' => './templates',
            'view' => '\Slim\View',
            // Cookies
            'cookies.encrypt' => false,
            'cookies.lifetime' => '20 minutes',
            'cookies.path' => '/',
            'cookies.domain' => null,
            'cookies.secure' => false,
            'cookies.httponly' => false,
            // Encryption
            'cookies.secret_key' => 'CHANGE_ME',
            'cookies.cipher' => MCRYPT_RIJNDAEL_256,
            'cookies.cipher_mode' => MCRYPT_MODE_CBC,
            // HTTP
            'http.version' => '1.1',
            // Routing
            'routes.case_sensitive' => true,
            //Exceptions / rabbitMQ management Zeynel Dağlı
            'exceptions.rabbitMQ.queue.name' => 'exceptions_queue',
            'exceptions.rabbitMQ' => true,
            'exceptions.rabbitMQ.logging' => self::LOG_RABBITMQ_FILE,
            // request serial for every request, this will be used for logging and message queue Zeynel Dağlı
            'request.serial' => \Utill\Serial\serialCreater::cretaSerial(),
            // Exceptions / HMAC authentication configurations Zeynel Dağlı
            'hmac.rabbitMQ.queue.name' => 'hmac_queue',
            'hmac.rabbitMQ.logging' => self::LOG_RABBITMQ_FILE,
            'hmac.rabbitMQ' => true,
            // Rest service call log message queue Zeynel Dağlı
            'restEntry.rabbitMQ.queue.name' => 'restEntry_queue',
            'restEntry.rabbitMQ.logging' => self::LOG_RABBITMQ_FILE,
            'restEntry.rabbitMQ' => true,
            // default time zone Zeynel Dağlı
            'time.zone' => self::TIME_ZONE_ISTANBUL
            
        );
    }
    
     /**
     * Run
     *
     * This method invokes the middleware stack, including the core Slim application;
     * the result is an array of HTTP status, header, and body. These three items
     * are returned to the HTTP client.
     */
    public function run()
    {
        /**
         * set time zone for log and message queue message body
         * @author Mustafa Zeynel Dağlı
         */
        date_default_timezone_set($this->container['settings']['time.zone']);
        
        /**
         * MQMAnager middle ware katmanından önce inject
         * ediliyor/ test amaçlı değiştirilecek
         */
        /*$MQManagerConfigObject = new \Utill\MQ\Manager\MQManagerConfig;
        $managerConfig = new \Zend\ServiceManager\Config($MQManagerConfigObject->getConfig());
        $MQManager = new \Utill\MQ\Manager\MQManager($managerConfig);
        $MQManager->setService('slimApp', $this);
        $this->setMQManager($MQManager);*/
        
        
        set_error_handler(array('\Slim\Slim', 'handleErrors'));
        /**
         * MQmanager exceptions has been tested  by changing error handler function
         * @author Zeynel Dağlı
         * @todo first tests did not work, after further tests if not work
         * this code can be removed
         */
        //set_error_handler(array($this, 'handleErrorsCustom'));

        //Apply final outer middleware layers
        if ($this->config('debug')  ) {
            //Apply pretty exceptions only in debug to avoid accidental information leakage in production
            $this->add(new \Slim\Middleware\PrettyExceptions());
        }
        
        /**
         * zeynel dağlı
         */
        if($this->container['settings']['log.level'] <= \Slim\Log::ERROR) {
            //print_r('--slim run kontrolor--');
            $this->add(new \Slim\Middleware\PrettyExceptions());
        }

        //Invoke middleware and application stack
        $this->middleware[0]->call();
        //print_r('--slim run kontrolor2--');
        
        /**
         * if rest service entry logging conf. true, publish to message queue
         * @since 07/12/2015 this functionality is being called from MQ manager
         * @author Mustafa Zeynel Dağlı
         */
        //if($this->container['settings']['restEntry.rabbitMQ'] == true) $this->publishMessage();
        //if($this->container['settings']['restEntry.rabbitMQ'] == true) $this->getMQManager()->get('MQRestCallLog');

        //Fetch status, header, and body
        list($status, $headers, $body) = $this->response->finalize();

        // Serialize cookies (with optional encryption)
        \Slim\Http\Util::serializeCookies($headers, $this->response->cookies, $this->settings);

        //Send headers
        if (headers_sent() === false) {
            //Send status
            if (strpos(PHP_SAPI, 'cgi') === 0) {
                header(sprintf('Status: %s', \Slim\Http\Response::getMessageForCode($status)));
            } else {
                header(sprintf('HTTP/%s %s', $this->config('http.version'), \Slim\Http\Response::getMessageForCode($status)));
            }

            //Send headers
            foreach ($headers as $name => $value) {
                $hValues = explode("\n", $value);
                foreach ($hValues as $hVal) {
                    header("$name: $hVal", false);
                }
            }
        }

        //Send body, but only if it isn't a HEAD request
        if (!$this->request->isHead()) {
            echo $body;
        }

        $this->applyHook('slim.after');

        restore_error_handler();
    }
    
    /**
     * message wrapper function
     * @param \Exception $e
     * @author Mustafa Zeynel Dağlı
     * @deprecated since version 1.0.1 rest call log has been removed to MQManager
     */
    public function publishMessage($e = null, array $params = array()) {
        $exceptionMQ = new \Utill\MQ\restEntryMQ();
        $exceptionMQ->setChannelProperties(array('queue.name' => $this->container['settings']['restEntry.rabbitMQ.queue.name']));
        $message = new \Utill\MQ\MessageMQ\MQMessage();
        ;
        //$message->setMessageBody(array('testmessage body' => 'test cevap'));
        //$message->setMessageBody($e);
       
        $message->setMessageBody(array('message' => 'Rest service has been used', 
                                       'time'  => date('l jS \of F Y h:i:s A'),
                                       'serial' => $this->container['settings']['request.serial'],
                                       'ip' => \Utill\Env\serverVariables::getClientIp(),
                                       'url' => $this->request()->getUrl(),
                                       'path' => $this->request()->getPath(),
                                       'method' => $this->request()->getMethod(),
                                       'params' => json_encode($this->request()->params()),
                                       'logFormat' => $this->container['settings']['restEntry.rabbitMQ.logging']));
        $message->setMessageProperties(array('delivery_mode' => 2,
                                             'content_type' => 'application/json'));
        $exceptionMQ->setMessage($message->setMessage());
        $exceptionMQ->basicPublish();
    }

    
    
    /**
     * set encrytion class obj
     * @param \Encrypt\EncryptManual $encryptClass
     * @author Mustafa Zeynel Dağlı
     */
    public function setEncryptClass(\Encrypt\EncryptManual $encryptClass = null) {
        try {
            if($encryptClass == null) {
                $this->encryptClass = new \Encrypt\EncryptManual($this->encryptKey);
            } else {
                $this->encryptClass = $encryptClass;
            }
            return $this->encryptClass;
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
        }
    }
    
    /**
     * get encrytion class obj
     * @return \Encrypt\EncryptManual
     * @author Mustafa Zeynel Dağlı
     */
    public function getEncryptClass() {
        if($this->encryptClass == null){
            $this->setEncryptClass();
        }else {
            return $this->encryptClass;
        }
    }
    
    
    /**
     * get HMAC security public hash
     * @author Mustafa Zeynel Dağlı
     * @since version 2.6.1
     * @return string
     */
    public function getPublicHash() {
        return $this->publicHash;
    }
    
    /**
     * set HMAC security public hash
     * @author Mustafa Zeynel Dağlı
     * @since version 2.6.1
     * @param type $publicHash
     */
    public function setPublicHash($publicHash) {
         $this->publicHash = $publicHash;
    }
    
    /**
     * get HMAC security public hash
     * @author Mustafa Zeynel Dağlı
     * @since version 2.6.1
     * @return string
     */
    public function getPrivateHash() {
        return $this->privateHash;
    }
    
    /**
     * set HMAC security private hash
     * @author Mustafa Zeynel Dağlı
     * @since version 2.6.1
     * @param type $privateHash
     */
    public function setPrivateHash($privateHash) {
         $this->privateHash = $privateHash;
    }
    
    /**
     * get HMAC security request content hash
     * @author Mustafa Zeynel Dağlı
     * @since version 2.6.1
     * @return string
     */
    public function getSecurityContent() {
        return $this->privateHash;
    }
    
    /**
     * set HMAC security request content hash
     * @author Mustafa Zeynel Dağlı
     * @since version 2.6.1
     * @param type $securityContent
     */
    public function setSecurityContent($securityContent) {
         $this->privateHash = $securityContent;
    }
    
    
    /**
     * Convert errors into ErrorException objects
     *
     * This method will be trialed to reach error exception objects
     * from MQ manager
     *
     * @param  int            $errno   The numeric type of the Error
     * @param  string         $errstr  The error message
     * @param  string         $errfile The absolute path to the affected file
     * @param  int            $errline The line number of the error in the affected file
     * @return bool
     * @throws \ErrorException
     * @todo First test with MQmanager did not work, for further test this functionis not removed
     * after further test if not work to to publis exceptions to message queue, this function should be
     * removed
     */
    public  function handleErrorsCustom($errno, $errstr = '', $errfile = '', $errline = '', $errcontext = '')
    {
        if (!($errno & error_reporting())) {
            return;
        }
        /**
         * Exception loglarını Message queue ve 
         * service manager üzerinden yönetmek için yazılmıştır.
         * @author Mustafa Zeynel Dağlı
         */
        $exceptionMQ = $this->getMQManager()->get('MQException');
        $exceptionMQ->getMessage()->setMessageBody(array('message' => $errstr, 
                                       'file' => $errfile,
                                       'line' => $errline,
                                       'trace' => $errcontext ,
                                       'time'  => date('l jS \of F Y h:i:s A'),
                                       'serial' => $this->container['settings']['request.serial'],
                                       'logFormat' => $this->container['settings']['exceptions.rabbitMQ.logging']));
        $exceptionMQ->basicPublish();
        //print_r('--handlecustomerror--');
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }


}

