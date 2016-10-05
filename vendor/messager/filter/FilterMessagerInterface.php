<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Messager\Filter;

interface FilterMessagerInterface{
    public function setFilterMessage($filterMessage = null);
    public function getFilterMessage();
    public function addFilterMessage($filterMessage = null);
}

