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
 * service manager layer for filter functions
 * @author Okan Ciran  ÇĞÜÖŞİ
 * @version 29.12.2015
 */
class FilterDefault implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain->attach(new \Zend\Filter\HtmlEntities(array('quotestyle' => ENT_QUOTES,'charset' => 'UTF-8',  'encoding' => 'UTF-8')))
                    ->attach(new \Zend\Filter\StripTags())
                    ->attach(new \Zend\Filter\StringTrim())
                    ->attach(new \Zend\Filter\StripNewlines())                    
                     ;
        return $filterChain;

    }

}
