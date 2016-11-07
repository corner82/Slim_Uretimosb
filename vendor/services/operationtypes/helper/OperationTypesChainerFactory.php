<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Services\OperationTypes\Helper;

/**
 * base factory class for filter chainer classes
 * @author Mustafa Zeynel Dağlı
 * @since 22/02/2016
 */
class OperationTypesChainerFactory extends \Utill\Factories\AbstractFactory {
    
    /**
     * constructor function 
     */
    public function __construct() {
        
    }

    public function get($helperName, $app, $value) {
        if(method_exists($this,$helperName)) {
          return  $this->$helperName($app, $value);
        }
    }

    protected function getUtility($identifier = null,
            $params = null) {
        
    }

}
