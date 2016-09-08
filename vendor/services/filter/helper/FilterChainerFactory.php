<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Services\Filter\Helper;

/**
 * base factory class for filter chainer classes
 * @author Mustafa Zeynel Dağlı
 * @since 22/02/2016
 */
class FilterChainerFactory extends \Utill\Factories\AbstractFactory {
    
    /**
     * constructor function 
     */
    public function __construct() {
        
    }

    public function get($helperName, $app, $value) {
        if(method_exists($this,$helperName)) {
          return  $this->$helperName($app, $value);
        }
    }
    
    protected function onlyDefault($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,
        ));
    }
    
    protected function onlyParanoidLevel1($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,   
                            \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_CUSTOM_BASE, 
        ));
    }
    
    protected function onlyParanoidLevel2($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,   
                            \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_CUSTOM_ADVANCED,
                            \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
        ));
    }
    
    protected function onlyParanoidLevel3($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,   
                            \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_CUSTOM_ADVANCED,
                            \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
                            \Services\Filter\FilterServiceNames::FILTER_HEXADECIMAL_ADVANCED,
                            \Services\Filter\FilterServiceNames::FILTER_CDATA,
        ));
    }  
    
    protected function onlyParanoidLevel4($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,   
                            \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_CUSTOM_ADVANCED,
                            \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
                            \Services\Filter\FilterServiceNames::FILTER_HEXADECIMAL_ADVANCED,
                            \Services\Filter\FilterServiceNames::FILTER_CDATA,
                            \Services\Filter\FilterServiceNames::FILTER_JAVASCRIPT_FUNCTIONS,
        ));
    } 
    protected function onlyParanoidJasonLvl1($app, $value) { 
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
                            \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,            
                            \Services\Filter\FilterServiceNames::FILTER_HEXADECIMAL_ADVANCED,                            
        ));
    }
    
    protected function onlyState($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_TRIM,
                            \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
                            \Services\Filter\FilterServiceNames::FILTER_ONLY_STATE_ALLOWED, 
        ));
    }
    
    protected function onlyLanguageCode($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_TRIM,
                            \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
                            \Services\Filter\FilterServiceNames::FILTER_ONLY_LANGUAGE_CODE, 
        ));
    }

    protected function onlyBoolean($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                            \Services\Filter\FilterServiceNames::FILTER_TRIM,
                            \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
                            \Services\Filter\FilterServiceNames::FILTER_ONLY_BOOLEAN_ALLOWED, 
        ));
    }
    
    protected function onlyAlphabetic($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                    \Services\Filter\FilterServiceNames::FILTER_TRIM,
                    \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
        ));
    }
    
    protected function onlyNumber($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                    \Services\Filter\FilterServiceNames::FILTER_TRIM,
                    \Services\Filter\FilterServiceNames::FILTER_ONLY_NUMBER_ALLOWED,                                                                                             
        ));
    }
    
    protected function onlyTrue($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                    \Services\Filter\FilterServiceNames::FILTER_TRIM,
                    \Services\Filter\FilterServiceNames::FILTER_ONLY_TRUE_ALLOWED,                                                                                             
        ));
    }
    
    protected function onlyFalse($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                    \Services\Filter\FilterServiceNames::FILTER_TRIM,
                    \Services\Filter\FilterServiceNames::FILTER_ONLY_FALSE_ALLOWED,                                                                                             
        ));
    }
    
     protected function onlyOrder($app, $value) {
        return new \Utill\Strip\Chain\StripChainer($app, $value, array(
                    \Services\Filter\FilterServiceNames::FILTER_TRIM,
                    \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
                    \Services\Filter\FilterServiceNames::FILTER_ONLY_ORDER,                                                                                             
        ));
    }
    
     

    protected function getUtility($identifier = null,
            $params = null) {
        
    }

}
