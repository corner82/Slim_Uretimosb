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
class InfoFirmMachineTool extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->insert($params);
    }
    
    /**
     * DAta insert function
     * @param array | null $params
     * @return array
     */
    public function insertCons($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->insertCons($params);
    } 
    
    /**
     * Check Data function
     * @param array | null $params
     * @return array
     */
    public function haveRecords($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->haveRecords($params);
    }
    
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function updateCons($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->updateCons($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->delete($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function deleteConsAct( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->deleteConsAct($params);
    }
    

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->deletedAct($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillSingularFirmMachineTools($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');     
        return $DAL->fillSingularFirmMachineTools($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillSingularFirmMachineToolsRtc($params = array()) {     
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->fillSingularFirmMachineToolsRtc($params);
    }
    
     /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
     public function fillUsersFirmMachines($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillUsersFirmMachines($params);  
        return $resultSet['resultSet'];
    }
     
         /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillUsersFirmMachineProperties($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');     
        return $DAL->fillUsersFirmMachineProperties($params);
    }
    
      /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillUsersFirmMachinesRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillUsersFirmMachinesRtc($params);  
        return $resultSet['resultSet'];
    }

          /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */ 
       public function fillFirmMachineGroupsCounts($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->fillFirmMachineGroupsCounts($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillUsersFirmMachinesNpk($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillUsersFirmMachinesNpk($params);  
        return $resultSet['resultSet'];
    }
    
        /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillUsersFirmMachinesNpkRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillUsersFirmMachinesNpkRtc($params);  
        return $resultSet['resultSet'];
    }
      /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillAllCompanyMachineLists($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillAllCompanyMachineLists($params);  
        return $resultSet['resultSet'];
    }
    
        /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillAllCompanyMachineListsRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillAllCompanyMachineListsRtc($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillConsCompanyMachineLists($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillConsCompanyMachineLists($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillConsCompanyMachineListsRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillConsCompanyMachineListsRtc($params);  
        return $resultSet['resultSet'];
    }
     
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->makeActiveOrPassive($params);
    }
    
     /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillUrgeCompanyMachineLists($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillUrgeCompanyMachineLists($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillUrgeCompanyMachineListsRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        $resultSet = $DAL->fillUrgeCompanyMachineListsRtc($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function getFirmMachineConsultant($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmMachineToolPDO');
        return $DAL->getFirmMachineConsultant($params);
    }
    
    
}

