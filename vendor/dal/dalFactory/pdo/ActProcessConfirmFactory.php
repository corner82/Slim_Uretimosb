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
 * @since 11/02/2016
 */
class ActProcessConfirmFactory  implements \Zend\ServiceManager\FactoryInterface{
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $actProcessConfirm  = new \DAL\PDO\ActProcessConfirm();   
        $slimapp = $serviceLocator->get('slimapp') ;            
        $actProcessConfirm -> setSlimApp($slimapp);        
        return $actProcessConfirm;      
    }
    
    
}