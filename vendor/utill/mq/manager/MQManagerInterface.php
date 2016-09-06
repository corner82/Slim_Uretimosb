<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\MQ\Manager;

/**
 * interface to set MQ Manager
 * @author Mustafa Zeynel Dağlı
 */
interface MQManagerInterface {
    /**
     * injects MQ manager instance extended from Zend
     * service manager instance in Slimm Application
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceManager
     * @author Mustafa Zeynel Dağlı
     */
    public function setMQManager(\Zend\ServiceManager\ServiceLocatorInterface $serviceManager);
    
    /**
     * gets MQ manager instance extended from 
     * Zend service manager instance from Slimm Application
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     * @author Mustafa Zeynel Dağlı
     */
    public function getMQManager();
}



