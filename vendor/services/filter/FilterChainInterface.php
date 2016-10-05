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
 * ınterface to filterchain operations on overall
 * @author Mustafa Zeynel Dağlı
 * @version 13/01/2016
 */
interface FilterChainInterface {
    public function setFilterChain(\Utill\Strip\Chain\AbstractStripChainer $filterChainer);
    public function getFilterChain($name = null);
}