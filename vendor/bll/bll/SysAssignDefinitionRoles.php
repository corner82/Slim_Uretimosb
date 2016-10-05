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
class SysAssignDefinitionRoles extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        return $DAL->delete($params);
    }
    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function deleteAct( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        return $DAL->deleteAct($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    
    
   /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillAssignDefinitionRolesList ($params = array()) {       
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillAssignDefinitionRolesList($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillAssignDefinitionRolesListRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillAssignDefinitionRolesListRtc($params);  
        return $resultSet['resultSet'];
    }    
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        return $DAL->makeActiveOrPassive($params);
    }
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillAssignDefinitionRolesDdList($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillAssignDefinitionRolesDdList($params);
        return $resultSet['resultSet'];
    }
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillConsultantRolesTree ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillConsultantRolesTree($params);  
        return $resultSet['resultSet'];
    }
    
    
      /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillAssignDefinitionOfRoles($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillAssignDefinitionOfRoles($params);
        return $resultSet['resultSet'];
    }
        /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillNotInAssignDefinitionOfRoles($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAssignDefinitionRolesPDO');
        $resultSet = $DAL->fillNotInAssignDefinitionOfRoles($params);
        return $resultSet['resultSet'];
    }
  
}

