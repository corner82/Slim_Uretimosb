<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Messager\Filter;

interface FilterMessagerInterface{
    public function setFilterMessage($filterMessage = null);
    public function getFilterMessage();
    public function addFilterMessage($filterMessage = null);
}

