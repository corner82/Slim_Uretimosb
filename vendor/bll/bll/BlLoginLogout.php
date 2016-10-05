<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace BLL\BLL;

/**
 * Business Layer class for report Configuration entity
 */
class BlLoginLogout extends \BLL\BLLSlim{
    
    /**
     * constructor
     */
    public function __construct() {
        //parent::__construct();
    }
    
    /**
     * DAta insert function
     * @param array | null $params
     * @return array
     */
    public function insert($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        return $DAL->getAll($params);
    }
    
    
    /**
     * get private key  from public key
     * @param array$params
     * @return array
     */
    public function pkControl($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->pkControl($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * check if user belongs to specific company
     * @param array$params
     * @return array
     * @author Mustafa Zeynel Dağlı
     * @since 10/06/2016
     */
    public function isUserBelongToCompany($requestHeaderParams = array(), $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->isUserBelongToCompany($requestHeaderParams, $params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * get private key temp from public temp key
     * @param array$params
     * @return array
     * @author Mustafa Zeynel Dağlı
     * @since 0.3 27/01/2016
     */
    public function pkTempControl($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->pkTempControl($params);  
        return $resultSet['resultSet'];
    }

    
    public function pkLoginControl($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->pkLoginControl($params);  
        return $resultSet['resultSet'];
    }

    public function getPK($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->getPK($params);  
        return $resultSet['resultSet'];
    }

       
    public function pkSessionControl($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->pkSessionControl($params);  
        return $resultSet['resultSet'];
    }

     /**
     *  
     * @param array | null $params
     * @return array
     */
    public function pkIsThere($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->pkIsThere($params);  
        return $resultSet['resultSet'];
    }  
    
     /**
     *  
     * @param array | null $params
     * @return array
     */
    public function pkAllPkGeneratedFromPrivate($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blLoginLogoutPDO');
        $resultSet = $DAL->pkAllPkGeneratedFromPrivate($params);  
        return $resultSet['resultSet'];
    }   
    
    
    
    
    
    
}

