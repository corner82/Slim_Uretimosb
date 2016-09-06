<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Services\Filter;


/**
 * service manager layer for database connection
 * @author Mustafa Zeynel Dağlı
 * @deprecated since 15/01/2016
 */
class FilterHexadecimalBase implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain->attach(new \Zend\Filter\PregReplace(array(
                            'pattern'=> array("/&amp;/i",
                                                 "/&lt;/i",
                                                "/&gt;/i",
                                                "/&quot;/i",
                                                "/&#x27;/i",
                                                "/&#x2F;/i",
                                                "/&#42;/i",
                                                "/&#44;/i",
                                                "/&#45;/i",
                                                "/&#59;/i",
                                                "/&#61;/i",
                                                "/&#64;/i",
                                                "/&#91;/i",
                                                "/&#92;/i",
                                                "/&#93;/i",
                                                "/&#94;/i",
                                                "/&#95;/i",
                                                "/&#96;/i",
                                                "/&#123;/i",
                                                "/&#125;/i",
                                                "/&#124;/i",
                                                "/&#126;/i"),
                        'replacement' => '',
                    )));
        return $filterChain;

    }

}
