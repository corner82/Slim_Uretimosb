<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */
namespace Encrypt;

/**
 * abstract class for Encrypt classes
 */
abstract class AbstractEncrypt {
    
    protected $_key;
    protected $_base64=true;
    protected $_base32=false;
    protected $_base16=false;
    protected $_salt;
    
    /**
     * constructor
     */
    protected function __construct()
    {

    }

    /**
     * set salt parameter for encryption
     * @param string $salt
     */
    public function set_salt($salt="")
    {

    }
    
    /**
     * get salt parameter for encryption
     */
    public function get_salt()
    {

    }

    /**
     * has for encryption
     * @param string $value
     */
    public function hash_value($value)
    {

    }

    /**
    * URL ve COOKIE elemanları Şifrelerken dikkat edilmesi gereken karakterleri düzenler
    *
    * @param string $value
    * @return string
    */
    public static  function base64_url_encode($value="")
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); 
    }

    /**
    * URL ve COOKIE elemanlarını Şifrelerken dikkat edilmesi gereken karakterleri düzenler
    *
    * @param string $value
    * @return string
    */
    public static  function base64_url_decode($value="")
    {
        return base64_decode(str_pad(strtr($value, '-_', '+/'), strlen($value) % 4, '=', STR_PAD_RIGHT)); 	
    }

    /**
    * Şifreleme işleminde kullanılacak anahtar elemanı yerleştirir
    *
    * @param string $key
    * @return void
    */
    public function set_key($key="")
    {
        $this->_key=$key;
    }

    /**
    * Şifreleme işleminde kullanılacak anahtar elemanı döndürür
    *
    * @return string 
    */
    public function get_key()
    {
        return $this->_key;
    }

    /**
     * set base64 
     * @param bool $boolean
     */
    protected function set_base64($boolean)
    {
        $this->_base64=$boolean;
    }

    /**
     * if set base64 or not
     * @param bool $boolean
     * @return bool
     */
    protected function get_base64($boolean)
    {
        return $this->_base64;
    }

    /**
     * set base32 
     * @param bool $boolean
     */
    protected function set_base32($boolean)
    {
        $this->_base32=$boolean;
    }

    /**
     * if set base32 or not
     * @param bool $boolean
     * @return bool
     */
    protected function get_base32($boolean)
    {
        return $this->_base32;
    }

    /**
     * set base16
     * @param bool $boolean
     */
    protected function set_base16($boolean)
    {
        $this->_base16=$boolean;
    }

    /**
     * if set base 16 or not
     * @param bool $boolean
     * @return bool
     */
    protected function get_base16($boolean)
    {
        return $this->_base16;
    }
    
}
