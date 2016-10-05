<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */
namespace DAL\Factory\PDO;


/**
 * Class using Zend\ServiceManager\FactoryInterface
 * created to be used by DAL MAnager
 * @author Okan CIRAN
 * created date : 05.03.2016
 */
class SysUnitSystemsFactory  implements \Zend\ServiceManager\FactoryInterface{
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $sysUnitSystems  = new \DAL\PDO\SysUnitSystems();   
        $slimapp = $serviceLocator->get('slimapp') ;            
        $sysUnitSystems -> setSlimApp($slimapp); 
        
        return $sysUnitSystems;
      
    }
    
    
}