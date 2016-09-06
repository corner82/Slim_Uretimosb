<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */
namespace DAL\Factory\PDO;


/**
 * Class using Zend\ServiceManager\FactoryInterface
 * created to be used by DAL MAnager
 * @author Mustafa Zeynel Dağlı
 */
class InfoUsersSocialmediaFactory implements \Zend\ServiceManager\FactoryInterface {
    
    public function createService(\Zend\ServiceManager\ServiceLocatorInterface $serviceLocator) {
        $infoUsersSocialmedia = new \DAL\PDO\InfoUsersSocialmedia();
        $slimApp = $serviceLocator->get('slimApp');
        $infoUsersSocialmedia->setSlimApp($slimApp);
        return $infoUsersSocialmedia;
    }

}
