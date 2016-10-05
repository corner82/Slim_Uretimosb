<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace BLL;

/**
 * abstract business layer class
 * extended from BLL\AbstractBLL
 * @author Mustafa Zeynel Dağlı
 */
abstract class BLLSlim extends \BLL\AbstractBLL implements 
                                            \Slim\SlimAppInterface, 
                                            \DAL\DalInterface{
    /**
     * constructor
     */
    public function __construct() {
        
    }
    
    /**
     * implemented method from \DAL\DalInterface has been overriden
     * @param type $params
     * @author Mustafa Zeynel Dağlı
     * @version 16/01/2016
     */
    public function haveRecords($params = array()){
        
    }

                                                                                                /**
     * Slim application instance
     * @var Slim\Slim
     */
    protected $slimApp;
    
    /**
     * return slim app
     * @return Slim\Slim
     */
    public function getSlimApp() {
        return $this->slimApp;
    }

    /**
     * sets slim app
     * @param \Slim\Slim $slimApp
     */
    public function setSlimApp(\Slim\Slim $slimApp) {
        $this->slimApp = $slimApp;
    }
}

