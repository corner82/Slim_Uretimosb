<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Messager;

interface MessageBrokerInterface{
    public function compareValue($valuenew = null, $valueold = null, $filterName = null, $baseKey = null);
}

