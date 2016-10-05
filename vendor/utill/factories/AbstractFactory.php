<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Factories;

/**
 * base class for factory type operations
 * @author Mustafa Zeynel Dağlı
 * @since 11/02/2016
 */
abstract  class AbstractFactory{
    abstract protected function getUtility($identifier = null, $params = null);
}
