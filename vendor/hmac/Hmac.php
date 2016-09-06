<?php
/**
 * Rest Api Proxy Library
 *
 * @author Zeynel Dağlı
 * @version 0.2
 * @todo Nonce parameter will be encrypted and decrypted in http request 'X-NONCE' parameter
 */
namespace Hmac;

/**
 * class for hash creation
 * @author Mustafa Zeynel Dağlı
 */
class Hmac {
    
    /**
     * hash value
     * @var string | null
     */
    protected $hash;
    
    /**
     * public key
     * @var string | null 
     */
    protected $publicKey;
    
    /**
     * private key
     * @var string | null 
     */
    protected $privateKey;
    
    /**
     * request params
     * @var array 
     */
    protected $requestParams = array();
    
    /**
     * nonce for hash
     * @var string | null
     */
    protected $nonce = null;
    
    /**
     * time value
     * @var string | null
     */
    protected $timeStamp = null;

    /**
     * constructor
     */
    public function __construct() {
        
    }
    
    /**
     * set hash value
     * @param string | null $hash
     */
    public function setHash($hash = null) {
        $this->hash = $hash;
    }
    
    /**
     * get hash value
     * @return string | null
     */
    public function getHash() {
        return $this->hash;
    }
    
    /**
     * set nonce value
     * @param string | null $nonce
     */
    public function setNonce($nonce = null) {
        if($nonce == null) {
            $this->nonce = md5(time().rand());
        } else {
            $this->nonce = $nonce;
        }        
    }
    
    /**
     * get nonce value
     * @return string or null
     */
    public function getNonce() {
        return $this->nonce;
    }
    
     /**
     * set timestamp 
     * framework
     * @param string $timeStamp
     * @author Okan Cıran
     * @version 0.0.1
     */
      public function setTimeStamp($timeStamp = null) {
           if($timeStamp == null) {
            $this->timeStamp = time();
         } else {
            $this->timeStamp = $timeStamp;
        } 
        return $this->timeStamp;
    }
    
     /**
     * get timestamp 
     * framework
     * 
     * @author Okan Cıran
     * @version 0.0.1
     */
    public function getTimeStamp() {
        return $this->timeStamp;
    }   
    
     /**
     * difference timestamp 
     * framework
     * @param string $timeStamp
     * @author Okan Cıran
     * @version 0.0.1
     */
      public function timeStampDiff() {
        if($this->timeStamp != null) {
            return time() - $this->timeStamp;
         } else {
            return null;
        } 
         
    } 
       
    /**
     * hash creation for request parameters
     * and sets variable to class 'hash' variable
     */
    public function makeHmac() {
        $this->hash = hash_hmac('sha256', hash_hmac('sha256', json_encode($this->requestParams),  $this->getNonce()), $this->privateKey);
        //print_r('++'.$this->hash.'++');
        //$this->hash = hash_hmac('sha256', json_encode($this->requestParams), $this->privateKey);
    }
    
    /**
     * Set public key for hash algorithm
     * @param string | null $publicKey
     */
    public function setPublicKey($publicKey = null) {
        $this->publicKey = $publicKey;
    } 
    
    /**
     * get public key for hash algorithm
     * @return string | null
     */
    public function getPublicKey() {
        return $this->publicKey;
    }
    
    /**
     * Set private key for hash algorithm
     * @param string | null $privateKey
     */
    public function setPrivateKey($privateKey = null) {
        $this->privateKey = $privateKey;
    }
    
    /**
     * get private key for hash algorithm
     * @return string | null
     */
    public function getPrivateKey() {
        return $this->privateKey;
    }
    
    /**
     * Set request params for hash usage
     * @param array | null $requestParams
     */
    public function setRequestParams($requestParams = null) {
        $this->requestParams = $requestParams;
    }
    
    /**
     * Get request params for hash usage
     * @return array | null
     */
    public function getRequestParams() {
        return $this->requestParams;
    }
}
