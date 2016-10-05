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
class InfoFirmUsers extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->insert($params);
    }
    
     
    
    /**
     * Check Data function
     * @param array | null $params
     * @return array
     */
    public function haveRecords($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->haveRecords($params);
    }
    
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    
     /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->deletedAct($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillGridSingular($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');     
        return $DAL->fillGridSingular($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillGridSingularRtc($params = array()) {     
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->fillGridSingularRtc($params);
    } 
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillGridSingularNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        $resultSet = $DAL->fillGridSingularNpk($params);  
        return $resultSet['resultSet'];
    }
    /**
     * Data update function   
     * @param array $params
     * @return array
     */   
    public function fillGridSingularNpkRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        $resultSet = $DAL->fillGridSingularNpkRtc($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        return $DAL->makeActiveOrPassive($params);
    } 
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillCompanyUsersSocialMediaNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmUsersPDO');
        $resultSet = $DAL->fillCompanyUsersSocialMediaNpk($params);  
        return $resultSet['resultSet'];
    }
    
    
    
}

