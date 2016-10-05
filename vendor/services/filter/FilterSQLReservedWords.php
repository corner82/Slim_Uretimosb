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
 * service manager layer for database connection
 * @author Mustafa Zeynel Dağlı
 */
class FilterSQLReservedWords implements \Zend\ServiceManager\FactoryInterface {
    
    /**
     * service ceration via factory on zend service manager
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     * @return boolean|\PDO
     */
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        // Create a filter chain and filter for usage
        $filterChain = new \Zend\Filter\FilterChain();
        $filterChain->attach(new \Zend\Filter\PregReplace(array(
                            'pattern'=> array(//"/(;)|(%3b)/",
                                            //"/\/\*/",
                                            //"/\*\//",
                                            //"/\*/",
                                            "/@@/",
                                            //"/([^A-Za-z0-9])(@)([A-Za-z0-9])*/",
                                            "/nchar/i",
                                            //"/NCHAR/",
                                            "/nvarchar/i",
                                            //"/NVARCHAR/",
                                            "/varchar/i",
                                            //"/VARCHAR/",
                                            "/char/i",
                                            //"/CHAR/",
                                            "/(alter)(\s)*/i",
                                            //"/(ALTER)(\s)*/",
                                            "/(\s)+(begin)(\s)+/",
                                            //"/(\s)+(BEGIN)(\s)+/i",
                                            "/(\s)+(cast)(\s)+/",
                                            //"/(\s)+(CAST)(\s)+/",
                                            "/(\s)+(create)(\s)+/i",
                                            //"/(\s)+(CREATE)(\s)+/",
                                            "/(\s)+(cursor)(\s)+/i",
                                            //"/(\s)+(CURSOR)(\s)+/",
                                            "/(\s)+(declare)(\s)*/i",
                                            //"/(\s)+(DECLARE)(\s)*/",
                                            "/([^A-Za-z0-9_])(delete)([^A-Za-z0-9])*/i",
                                            //"/([^A-Za-z0-9])(DELETE)([^A-Za-z0-9])*/",
                                            "/([^A-Za-z0-9])(drop)([^A-Za-z0-9])*/i",
                                            //"/([^A-Za-z0-9])(DROP)([^A-Za-z0-9])*/",
                                            "/(\s)+(end)(\s)+/i",
                                            //"/(\s)+(END)(\s)+/",
                                            "/(\s)+(execute)(\s)+/","/(\s)+(EXECUTE)(\s)+/",
                                            "/(\s)+(exec)(\s)+/i",
                                            //"/(\s)+(EXEC)(\s)+/",
                                            "/fetch/i",
                                            //"/FETCH/",
                                            "/insert/i",
                                            //"/INSERT/",
                                            "/(\s)+(kill)(\s)+/i",
                                            //"/(\s)+(KILL)(\s)+/",
                                            "/(\s)+(open)(\s)+/i",
                                            //"/(\s)+(OPEN)(\s)+/",
                                            "/select/i",
                                            //"/([^A-Za-z0-9_])(select)([^A-Za-z0-9])+/",
                                            //"/SELECT/i",
                                            "/sysobjects/i",
                                            //"/SYSOBJECTS/",
                                            "/syscolumns/i",
                                            //"/SYSCOLUMNS/",
                                            "/(\s)+(sys)(\s)+/i",
                                            //"/(\s)+(SYS)(\s)+/i",
                                            "/table/i",
                                            //"/TABLE/",
                                            "/([^A-Za-z0-9_])(update)([^A-Za-z0-9])+/i",
                                            //,"/([^A-Za-z0-9])(UPDATE)([^A-Za-z0-9])+/",
                                            "/([^A-Za-z0-9])(or)([^A-Za-z0-9])+/i",
                                            //"/([^A-Za-z0-9])(OR)([^A-Za-z0-9])+/",
                                            //"/([^A-Za-z0-9])(UNION)([^A-Za-z0-9])+/",
                                            "/([^A-Za-z0-9])(union)([^A-Za-z0-9])+/i"),
                        'replacement' => '/*$0*/',
                    )));
        return $filterChain;

    }

}
