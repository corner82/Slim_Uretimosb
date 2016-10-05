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
class InfoFirmQuality extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->insert($params);
    }
    
      
    /**
     * Check Data function
     * @param array | null $params
     * @return array
     */
    public function haveRecords($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->haveRecords($params);
    }
    
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
 
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    
     /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->deletedAct($params);
    }
     
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        return $DAL->makeActiveOrPassive($params);
    } 
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillFirmQualityCertificateNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        $resultSet = $DAL->fillFirmQualityCertificateNpk($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillFirmQualityCertificateNpkRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        $resultSet = $DAL->fillFirmQualityCertificateNpkRtc($params);  
        return $resultSet['resultSet'];
    }
        
      /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillFirmQualityCertificateNpkQuest ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        $resultSet = $DAL->fillFirmQualityCertificateNpkQuest($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillFirmQualityCertificateNpkQuestRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmQualityPDO');
        $resultSet = $DAL->fillFirmQualityCertificateNpkQuestRtc($params);  
        return $resultSet['resultSet'];
    }
    
   
    
}

