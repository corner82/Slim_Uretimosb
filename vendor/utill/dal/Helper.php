<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */
namespace Utill\Dal;

final class Helper {
    
    
    public static function haveRecord($result = null) {
        if(isset($result['resultSet'][0]['control'])) return true;
        return false;
    }
}

