<?php

/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Service\Manager;

/**
 * config class for zend service manager
 */
class config {

    /**
     * constructor
     */
    public function __construct() {
        
    }

    /**
     * config array for zend service manager config
     * @var array
     */
    protected $config = array(
        // Initial configuration with which to seed the ServiceManager.
        // Should be compatible with Zend\ServiceManager\Config.
        'service_manager' => array(
            'invokables' => array(
                'test' => 'Utill\BLL\Test\Test'
            ),
            'factories' => [
                'pgConnectFactory' => 'Services\Database\Postgresql\PostgreSQLConnectPDO',
                'pgConnectLogFactory' => 'Services\Database\Postgresql\PostgreSQLConnectLogPDO',
                'filterDefault' => 'Services\Filter\FilterDefault',
                'filterHtmlTagsCustomAdvanced' => 'Services\Filter\filterHtmlTagsCustomAdvanced',
                'filterHtmlTagsCustomBase' => 'Services\Filter\filterHtmlTagsCustomBase',
                'filterHexadecimalAdvanced' => 'Services\Filter\FilterHexadecimalAdvanced',
                'filterLowerCase' => 'Services\Filter\FilterLowerCase',
                'filterPregReplace' => 'Services\Filter\FilterPregReplace',
                'filterOnlyNumberAllowed' => 'Services\Filter\filterOnlyNumberAllowed',
                'filterOnlyLanguageCode' => 'Services\Filter\FilterOnlyLanguageCode',
                'filterOnlyAlphabeticAllowed' => 'Services\Filter\filterOnlyAlphabeticAllowed',
                'filterOnlyState' => 'Services\Filter\filterOnlyState',
                'filterParentheses' => 'Services\Filter\filterParentheses',
                'filterSQLReservedWords' => 'Services\Filter\FilterSQLReservedWords',
                'filterToNull' => 'Services\Filter\FilterToNull',
                'filterUpperCase' => 'Services\Filter\FilterUpperCase',
                'filterChainerCustom' => 'Services\Filter\FilterChainerCustom',
                'validatorMessager' => 'Services\Messager\ServiceValidatorMessager',
                'filterMessager' => 'Services\Messager\ServiceFilterMessager',
                'filterParentheses' => 'Services\Messager\FilterParentheses',
                'filterJavascriptMethods' => 'Services\Filter\FilterJavascriptMethods',               
                'validationChainerServiceForZendChainer' => 'Services\Validator\ValidationChainerServiceForZendChainer',
                'filterOnlyTrue' => 'Services\Filter\FilterOnlyTrue',
                'filterOnlyFalse' => 'Services\Filter\FilterOnlyFalse',
                'filterOnlyBoolean' => 'Services\Filter\FilterOnlyBoolean',
                'filterTrim' => 'Services\Filter\FilterTrim',
                'filterOnlyOrder' => 'Services\Filter\FilterOnlyOrder',
                
                
            ],
        ),
    );

    /**
     * return config array for zend service manager config
     * @return array | null
     * @author Mustafa Zeynel Dağlı
     */
    public function getConfig() {
        return $this->config['service_manager'];
    }

}
