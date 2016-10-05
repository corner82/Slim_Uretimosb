<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */
namespace Utill\Validation;

use Iterator;
use ArrayAccess;
use Countable;

abstract class AbstractValidator  implements Iterator, 
                                     Countable, 
                                     ArrayAccess,
                                     \Utill\Validation\ValidationInterface
                                     /*\Utill\Validation\ValidationChainInterface*/{
    
    /*const SQL_STRATEGY    = 'sql_strategy';
    const HEX_STRATEGY    = 'hex_strategy';
    const HEX_ADVANCED_STRATEGY  = 'hex_advanced_strategy';
    const CDATA_STRATEGY = 'cdata_strategy';
    const ALL_HTML_STRATEGY  = 'all_html_strategy';
    const BASE_STRATEGY  = 'base_strategy';*/
    
    protected $validationStrategies;
    
     /**
     * returns the count of countable interface method
     * @return integer
     */
    public function count($mode = 'COUNT_NORMAL') {
        return count($this->validationStrategies);
    }
    
    /**
     * returns the current object for iterator interface method
     * @return mixed any type 
     */
    public function current() {
        return current($this->validationStrategies);
    }
    
    /**
     * returns the object determined by ket value for iterator interface method
     * @return mixed any type 
     */
    public function key() {
        return key($this->validationStrategies);
    }
    
    /**
     * get next type for iterator interface method
     * @return mixed any type 
     */
    public function next() {
        return next($this->validationStrategies);
    }
    
    /**
     * reset current iteartor for iterator interface method
     */
    public function rewind() {
        reset($this->validationStrategies);
    }

    /**
     * is iterator type valid for iterator interface method
     * @return boolean
     */
    public function valid() {
        return (current($this->validationStrategies) !== false);
    }
    
    /**
     * control if offset element exists
     * @param mixed string | integer $offset
     * @return boolean
     * @author Mustafa Zeynel Dağlı
     * @since 12/01/2016
     */
    public function offsetExists($offset) {
        return (isset($this->validationStrategies[$offset])) ?  true : false;
    }
    
    /**
     * return offset element or null
     * @param type $offset
     * @return mixed Can return all value types or null
     * @author Mustafa Zeynel Dağlı
     * @since 12/01/2016
     */
    public function offsetGet($offset) {
        return (isset($this->validationStrategies[$offset])) ?  $this->validationStrategies[$offset] : null;
    }
    
    /**
     * unset any type due to offset for arrayaccess interface method
     * @param mixed any type $offset
     * @return \Utill\Strip\Chain\StripChainer
     */
     public function offsetUnset($offset) {
        unset($this->validationStrategies[$offset]);
        return $this;
    }
}