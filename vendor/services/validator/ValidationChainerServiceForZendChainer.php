<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Services\Validator;

/**
 * service manager layer for validation chainer which is using \Zend\Validator\ValidatorChainer
 * @author Mustafa Zeynel Dağlı
 * @version 13/01/2016
 */
class ValidationChainerServiceForZendChainer implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return Utill\Strip\Strip
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a message broker for usage
        $zendChainerValidator = new \Utill\Validation\ZendChainerValidator();
        return $zendChainerValidator;

    }

}