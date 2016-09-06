<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace BLL\BLL;

/**
 * Business Layer class for report Configuration entity
 */
class InfoFirmVerbal extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Check Data function
     * @param array | null $params
     * @return array
     */
    public function haveRecords($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->haveRecords($params);
    }    
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {     
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    
     /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->deletedAct($params);
    } 
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */     
    public function fillGridSingularNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        $resultSet = $DAL->fillGridSingularNpk($params);  
        return $resultSet['resultSet'];
    }

    /**
     * Data update function   
     * @param array $params
     * @return array
     */     
    public function fillUsersFirmVerbalNpkGuest ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        $resultSet = $DAL->fillUsersFirmVerbalNpkGuest($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */     
    public function fillUsersFirmVerbalNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        $resultSet = $DAL->fillUsersFirmVerbalNpk($params);  
        return $resultSet['resultSet'];
    }
 
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function getFirmVerbalConsultant($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->getFirmVerbalConsultant($params);
    }
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function sendMailConsultant($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmVerbalPDO');
        return $DAL->sendMailConsultant($params);
    }
      
    
    
}

