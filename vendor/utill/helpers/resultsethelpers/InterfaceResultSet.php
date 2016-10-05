<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Helpers\ResultSetHelpers;

interface InterfaceResultSet {
    public function prepareResultSet($resultSet = null);
    public function checkResultSet($resultSet = null);
}