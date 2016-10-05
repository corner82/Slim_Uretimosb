<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Validation;

 class ZendChainerValidator extends \Utill\Validation\AbstractValidator 
                                              {
    
    public function __construct($params = null) {
        
        //if(empty($params))throw new Exception('strip class constructor parametre hatası');
        
        
    }
    
    /**
     * set any type to determined offset  for arrayaccess interface method
     * @param mixed any type $offset
     * @param \Zend\Filter\AbstractFilter $value
     * @throws Exception
     */
    public function offsetSet($offset, $value) {
        if(!$this->offsetExists($offset)) {
            if($value instanceof \Utill\Validation\Chain\ZendValidationChainer) {
                $this->validationStrategies[$offset] = $value;
                return true;
            } else {
                throw new \Exception('invalid \Utill\Validation\Chain\ZendValidationChainer class!!');
            }
        }
        throw new \Exception('repeated  key in \Utill\Validation\Chain\ZendValidationChainer class!!');
        //return false;
    }
    
    public function validate($baseKey = null) {
        $this->rewind();
        foreach ($this->validationStrategies as $key => $value) {
            if(method_exists($value, 'validate')) { 
                $value->validate($key);
            } else {
                throw new \Exception('invalid validate method for validation process');
            }
        }
    }
}

