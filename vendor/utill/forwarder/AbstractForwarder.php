<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */
namespace Utill\Forwarder;

/**
 * abstract forwarder class
 * @author Mustafa Zeynel Dağlı
 */
abstract class AbstractForwarder {
    
    protected $parameters = array();
    
    /**
     * redirectirection
     */
    abstract public function redirect();
    
    /**
     * to set log or specific prameters to redirect url
     * @param array | null $parameters
     * @author Mustafa Zeynel Dağlı
     * @since version 0.3 06/01/2016
     */
    public function setParameters($parameters = array()) {
        $this->parameters = $parameters;
    }
}

