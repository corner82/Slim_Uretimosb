<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Services\Filter;


/**
 *  
 * @author Okan CIRAN
 */
class FilterParentheses implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain->attach(new \Zend\Filter\PregReplace(array(
                            'pattern'=> array("/(\/)|(%2f)/",
                                              "/(\()|(&#40;)/",
                                              "/(\))|(&#41;)/",                                            
                                               ),
                        'replacement' => '',
                    ), 200));
        return $filterChain;

    }

}
