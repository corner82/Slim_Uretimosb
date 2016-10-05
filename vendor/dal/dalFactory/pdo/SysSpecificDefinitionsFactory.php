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
 * created date : 25.01.2016
 */
class SysSpecificDefinitionsFactory  implements \Zend\ServiceManager\FactoryInterface{
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $sysSpecificDefinitions  = new \DAL\PDO\SysSpecificDefinitions()   ;   
        $slimapp = $serviceLocator->get('slimapp') ;            
        $sysSpecificDefinitions -> setSlimApp($slimapp);
        
 
        
        return $sysSpecificDefinitions;
      
    }
    
    
}