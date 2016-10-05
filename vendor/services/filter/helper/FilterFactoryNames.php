<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Services\Filter\Helper;

/**
 * class to get filter service name constants
 */
final class FilterFactoryNames {
    
    const FILTER_PARANOID_LEVEL1 = 'onlyParanoidLevel1';
    const FILTER_PARANOID_LEVEL2 = 'onlyParanoidLevel2';
    const FILTER_PARANOID_LEVEL3 = 'onlyParanoidLevel3';
    const FILTER_PARANOID_LEVEL4 = 'onlyParanoidLevel4';
    
    const FILTER_PARANOID_JASON_LVL1 = 'onlyParanoidJasonLvl1'; 
    
    const FILTER_ONLY_ALPHABETIC_ALLOWED = 'onlyAlphabetic';
    const FILTER_ONLY_NUMBER_ALLOWED = 'onlyNumber';
    const FILTER_ONLY_TRUE_ALLOWED = 'onlyTrue';
    const FILTER_ONLY_FALSE_ALLOWED = 'onlyFalse';
    const FILTER_ONLY_BOOLEAN_ALLOWED = 'onlyBoolean';
    const FILTER_ONLY_STATE_ALLOWED = 'onlyState';
    const FILTER_ONLY_LANGUAGE_CODE = 'onlyLanguageCode';
    const FILTER_ONLY_ORDER = 'onlyOrder';
    
    
    
    
    const FILTER_DEFAULT = 'onlyDefault';
    const FILTER_TRIM = 'filterTrim';
    
    const FILTER_HTML_TAGS_CUSTOM_BASE = 'filterHTMLTagsCustomBase';
    const FILTER_HTML_TAGS_CUSTOM_ADVANCED = 'filterHTMLTagsCustomAdvanced';
    
    const FILTER_HEXADECIMAL_ADVANCED = 'filterHexadecimalAdvanced';
     // const FILTER_HEXADECIMAL_BASE = 'filterHexadecimalBase';
    //const FILTER_PREG_REPLACE = 'filterPregReplace';
    
    
    
    const FILTER_SQL_RESERVEDWORDS = 'filterSQLReservedWords';
       
    const FILTER_UPPER_CASE = 'filterUpperCase';
    const FILTER_LOWER_CASE = 'filterLowerCase';
    
    const FILTER_PARENTHESES = 'filterParentheses';
    const FILTER_TONULL = 'filterToNull';
    
    const FILTER_JAVASCRIPT_FUNCTIONS = 'filterJavascriptMethods';
    const FILTER_CDATA= 'filterCdata';
 
}

