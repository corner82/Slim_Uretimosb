<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */
namespace Utill\Validation\Chain;

class ZendValidationChainer extends AbstractValidationChainer  {
    
    /**   
     * value to be validated
     * @var mixed any type
     */
    protected $validationValue;
    
    protected $chainer = array();
    
    public function __construct($slimApp, $valueToValidate, $validatorChainer) {
        
        if(!$slimApp instanceof \Slim\Slim ) throw new \Exception ('no slim app found in ValidationChainer');
        $this->setSlimApp($slimApp);
        
        //if(empty($valueToValidate)) throw new \Exception ('no value to validate in ValidationChainer class');
        $this->setValidationValue($valueToValidate);
        
        if(empty($validatorChainer)) throw new \Exception ('validate class name is empty in ValidationChainer class');
        
        if(!$validatorChainer instanceof \Zend\Validator\ValidatorChain) throw new Exception ('invalid validationChainer class, expected \Zend\Validator\ValidatorChain in ZendValidationChainer class');
        $this->offsetSet($this->count(), $validatorChainer);
        
    }
    
    /**
     * set any type to determined offset  for arrayaccess interface method
     * @param mixed any type $offset
     * @param \Zend\Validator\ValidatorChain $value
     * @throws Exception
     */
    public function offsetSet($offset, $value) {
        if($value instanceof \Zend\Validator\ValidatorChain) {
            //$this->chainer[$offset] = $value;
            array_push($this->chainer, $value);
        } else {
            throw new Exception('invalid validation chainer class, waiting for \Zend\Validator\ValidatorChain !!');
        }
    }
    
    /**
     * excutes all filter operations
     * @throws \Exception
     */
    public function validate($baseKey = null) {
        $this->rewind();
        $validatorMessager = $this->slimApp->getServiceManager()->get('validatorMessager');
        $validatorMessager->setBaseKey($baseKey);
        foreach ($this->chainer as $key => $value) {
          //print_r('-key-'.$key.'--');
          //print_r('-validate-'.$value.'--');
          $oldValue = $this->validationValue;
          if(method_exists($value, 'isValid')) {
            $validationMessage = array();
            if ($this->validationValue = $value->isValid($this->validationValue)) {
                //  passed validation
            } else {
                // username failed validation; print reasons
                foreach ($value->getMessages() as $message) {
                    //echo "$message\n";
                    //$validationMessage.='[:::]message->'.$message.'[:]ip->'.$_SERVER['REMOTE_ADDR'];
                    array_push($validationMessage, $message);
                }
                $validatorMessager->addValidationMessage ($validationMessage);
            }
            } else {
                throw new \Exception('invalid isValid  method for \Zend\Validation\ValidatorChain');
            }
            //$filterValidatorMessager->compareValidatedValue($this->validationValue, $oldValue, $key);
            
        }
        //print_r($filterValidatorMessager->getValidationMessage());
        //print_r('--value filtered-->'.$this->validationValue);
    }

    /**
     * get filtered value
     * @return mixed
     */
    public function getValidationValue() {
        return $this->validationValue;
    }
    
    /**
     * set  value to be filtered
     * @param mixed $value
     */
    public function setValidationValue($validationValue = null) {
        $this->validationValue = $validationValue;
    }
    
    /**
     * get validation chainer  class \Zend\Validation\ValidationChainer
     * @param mixed \Zend\Filter\AbstractFilter | null $name
     */
    public function getValidationChain($key = null) {
        if($this->offsetExists($key)) {
            return $this->offsetGet($key);
        }
        return false;
    }

    /**
     * set \Zend\Validation\ValidationChainer type class
     * @param array $params
     * @return boolean
     */
    public function setValidationChain($validationChain = null) {
        $key = key($params);
        if(!$this->offsetExists($key)) {
            //print_r('--test--');
            $this->offsetSet(($key+1), $params[$key]);
            return true;
        }
        return false;
    }

}