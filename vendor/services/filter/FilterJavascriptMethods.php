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
 * service manager layer for filter functions for javascript method filtering
 * @author Mustafa Zeynel Dağlı
 * @version 15/01/2016
 */
class FilterJavascriptMethods implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain ->attach(new \Zend\Filter\PregReplace(array(
                        'pattern'     => array(
                                               "/javascript/i",
                                               "/([^A-Za-z0-9])*(document)([^A-Za-z0-9])+/i",
                                               "/([^A-Za-z0-9])*(onload)([^A-Za-z0-9])+/i",
                                               "/([^A-Za-z0-9])*(object)([^A-Za-z0-9])+/i",
                                               "/script/i",
                                               "/<EMBED/i",
                                               "/(#)|(%23)/",
                                               "/(\{)|(%7b)/",
                                               //"/(=)|(%3d)/",
                                               "/(!--)|(&#33;&#95;&#95;)/",
                                               "/fromCharCode/i",
                                               "/alert/i",
                                               "/.js/i",
                                               "/onreadystatechange/i",
                                               "/xmlhttprequest/i",
                                               "/([^A-Za-z0-9](eval))|((eval)[^A-Za-z0-9]+)/i",
                                               ),
                        'replacement' => '',
                    ), 200));
        return $filterChain;

    }

}
