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
 * @author Okan CİRANĞ
 * created date : 08.12.2015
 */
class SysCustomerCriterionFactory  implements \Zend\ServiceManager\FactoryInterface{
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $sysCustomerCriterion  = new \DAL\PDO\SysCustomerCriterion();   
        $slimapp = $serviceLocator->get('slimapp') ;            
        $sysCustomerCriterion -> setSlimApp($slimapp); 
        return $sysCustomerCriterion;
      
    }
    
    
}