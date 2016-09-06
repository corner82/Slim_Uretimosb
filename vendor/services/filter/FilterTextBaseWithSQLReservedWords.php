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
 * @deprecated 15/01/2016 version 0.2
 */
class FilterTextBaseWithSQLReservedWords implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain->attach(new \Zend\Filter\StripTags())
                    ->attach(new \Zend\Filter\StringTrim())
                    ->attach(new \Zend\Filter\HtmlEntities())
                    ->attach(new \Zend\Filter\StripNewlines())
                    ->attach(new \Zend\Filter\StringToLower(array('encoding' => 'UTF-8')))
                    ->attach(new \Zend\Filter\PregReplace(array(
                        'pattern'     => array("/javascript/i",
                                               "/([^A-Za-z0-9])*(document)([^A-Za-z0-9])+/i",
                                               "/([^A-Za-z0-9])*(onload)([^A-Za-z0-9])+/i",
                                               "/([^A-Za-z0-9])*(iframe)([^A-Za-z0-9])+/i",
                                               "/([^A-Za-z0-9])*(object)([^A-Za-z0-9])+/i",
                                               "/(SRC=)|(src =)|(src%3d)/i",
                                               "/(SRC=)|(src =)|(src%3d)/i",
                                               "/(href=)|(href =)|(href%3d)|(href)/i",
                                               "/script/i",
                                               "/SRC=/i",
                                               "/<EMBED/i",
                                               "/(#)|(%23)/",
                                               "/(\{)|(%7b)/",
                                               "/(=)|(%3d)/",
                                               "/(!--)|(&#33;&#95;&#95;)/",
                                               "/(<)[^A-Za-z0-9]*(img)/i",
                                               "/fromCharCode/i",
                                               "/alert/i",
                                               "/.js/i",
                                               "/onreadystatechange/i",
                                               "/xmlhttprequest/i",
                                               "/([^A-Za-z0-9](eval))|((eval)[^A-Za-z0-9]+)/i",
                                               /*"/HTTP-EQUIV/i",
                                               "/style/i",
                                               "/body/i",
                                               "/HTTP-EQUIV/i",
                                               "/background/i",
                                               "/XML/i",
                                               "/http/i",
                                               "/(<a)|(<\/a>)/i",*/
                                               ),
                        'replacement' => 'john',
                    ), 200));
        return $filterChain;

    }

}
