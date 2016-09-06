<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Strip;

use Iterator;
use ArrayAccess;
use Countable;

 class AbstractStrip  implements Iterator, Countable, ArrayAccess,
                                        \Utill\Strip\StripInterface{
    
    /*const SQL_STRATEGY    = 'sql_strategy';
    const HEX_STRATEGY    = 'hex_strategy';
    const HEX_ADVANCED_STRATEGY  = 'hex_advanced_strategy';
    const CDATA_STRATEGY = 'cdata_strategy';
    const ALL_HTML_STRATEGY  = 'all_html_strategy';
    const BASE_STRATEGY  = 'base_strategy';*/
    
    protected $stripStrategies =array();
    
     /**
     * returns the count of countable interface method
     * @return integer
     */
    public function count($mode = 'COUNT_NORMAL') {
        return count($this->stripStrategies);
    }
    
    /**
     * returns the current object for iterator interface method
     * @return mixed any type 
     */
    public function current() {
        return current($this->stripStrategies);
    }
    
    /**
     * returns the object determined by ket value for iterator interface method
     * @return mixed any type 
     */
    public function key() {
        return key($this->stripStrategies);
    }
    
    /**
     * get next type for iterator interface method
     * @return mixed any type 
     */
    public function next() {
        return next($this->stripStrategies);
    }
    
    /**
     * reset current iteartor for iterator interface method
     */
    public function rewind() {
        reset($this->stripStrategies);
    }

    /**
     * is iterator type valid for iterator interface method
     * @return boolean
     */
    public function valid() {
        return (current($this->stripStrategies) !== false);
    }
    
    /**
     * control if offset element exists
     * @param mixed string | integer $offset
     * @return boolean
     * @author Mustafa Zeynel Dağlı
     * @since 12/01/2016
     */
    public function offsetExists($offset) {
        return (isset($this->stripStrategies[$offset])) ?  true : false;
    }
    
    /**
     * return offset element or null
     * @param type $offset
     * @return mixed Can return all value types or null
     * @author Mustafa Zeynel Dağlı
     * @since 12/01/2016
     */
    public function offsetGet($offset) {
        if(!isset($this->stripStrategies[$offset])) throw new \Exception (' Strip class offsetGet undefined offset!!');
        return (isset($this->stripStrategies[$offset])) ?  $this->stripStrategies[$offset] : null;
    }
    
    /**
     * set any type to determined offset  for arrayaccess interface method
     * @param mixed any type $offset
     * @param \Zend\Filter\AbstractFilter $value
     * @throws Exception
     */
    public function offsetSet($offset, $value) {
        
        if(!$this->offsetExists($offset)) {
            if($value instanceof \Utill\Strip\Chain\AbstractStripChainer) {
                $this->stripStrategies[$offset] = $value;
                //print_r($this->stripStrategies[$offset]);
                return true;
            } else {
                throw new \Exception('invalid \Utill\Strip\Chain\AbstractStripChainer class!!');
            }
        }
        throw new \Exception('repeated  "'.$offset.'" -->key in strip class!!');
        //return false;
    }
    
    /**
     * unset any type due to offset for arrayaccess interface method
     * @param mixed any type $offset
     * @return \Utill\Strip\Chain\StripChainer
     */
     public function offsetUnset($offset) {
        unset($this->stripStrategies[$offset]);
        return $this;
    }
    
    /**
     * strip method for Utill\Strip\StripInterface
     * will be overridden in subclasses
     */
    public function strip($key = null) {
        
    }

}