<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace DAL;

/**
 * DAL manager extended from Zend\ServiceManager\ServiceManager
 * @author Mustafa Zeynel Dağlı
 */
class DalManager extends \Zend\ServiceManager\ServiceManager {
    
    public function __construct(\Zend\ServiceManager\ConfigInterface $config = null) {
        parent::__construct($config);
    }
}
