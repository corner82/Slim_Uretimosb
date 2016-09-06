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
 * service manager layer for custom html tags filter
 * @author Mustafa Zeynel Dağlı
 */
class FilterHtmlTagsCustomBase implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain->attach(new \Zend\Filter\PregReplace(array(
                            'pattern'=> array("/(\\\)|(%5c)/",
                                              "/(<)|(%3c)/",
                                              "/(>)|(%3e)/",
                                              /*"/(\/)|(%2f)/",
                                              "/(\()|(&#40;)/",
                                              "/(\))|(&#41;)/",*/
                                              "/&quot/",
                                              /*"/(&)|(%26)/"*/),
                        'replacement' => '',
                    ), 200));
        return $filterChain;

    }

}
