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
 * class to get filter service name constants
 */
final class FilterServiceNames {
    
    const FILTER_DEFAULT = 'filterDefault';
    const FILTER_TRIM = 'filterTrim';
    
    const FILTER_HTML_TAGS_CUSTOM_BASE = 'filterHTMLTagsCustomBase';
    const FILTER_HTML_TAGS_CUSTOM_ADVANCED = 'filterHTMLTagsCustomAdvanced';
    
    const FILTER_HEXADECIMAL_ADVANCED = 'filterHexadecimalAdvanced';
     // const FILTER_HEXADECIMAL_BASE = 'filterHexadecimalBase';
    //const FILTER_PREG_REPLACE = 'filterPregReplace';
    
    const FILTER_ONLY_ALPHABETIC_ALLOWED = 'filterOnlyAlphabeticAllowed';
    const FILTER_ONLY_NUMBER_ALLOWED = 'filterOnlyNumberAllowed';
    const FILTER_ONLY_TRUE_ALLOWED = 'filterOnlyTrue';
    const FILTER_ONLY_FALSE_ALLOWED = 'filterOnlyFalse';
    const FILTER_ONLY_BOOLEAN_ALLOWED = 'filterOnlyBoolean';
    const FILTER_ONLY_STATE_ALLOWED = 'filterOnlyState';
    const FILTER_ONLY_LANGUAGE_CODE = 'filterOnlyLanguageCode';
    const FILTER_ONLY_ORDER = 'filterOnlyOrder';
    
    
    
    const FILTER_SQL_RESERVEDWORDS = 'filterSQLReservedWords';
       
    const FILTER_UPPER_CASE = 'filterUpperCase';
    const FILTER_LOWER_CASE = 'filterLowerCase';
    
    const FILTER_PARENTHESES = 'filterParentheses';
    const FILTER_TONULL = 'filterToNull';
    
    const FILTER_JAVASCRIPT_FUNCTIONS = 'filterJavascriptMethods';
    const FILTER_CDATA= 'filterCdata';
 
}

