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
 * Interface for zend filter class implementations
 * @author Mustafa Zeynel Dağlı
 * @since 13/01/2016
 */
interface FilterInterface {
    public function setFilter($params = null);
    public function getFilter($name = null);
    public function setFilterValue($value);
    public function getFilterValue();
}