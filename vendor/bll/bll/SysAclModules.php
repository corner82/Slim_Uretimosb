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
class SysAclModules extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
     
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillComboBoxFullModules ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillComboBoxFullModules($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillModulesTree ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillModulesTree($params);  
        return $resultSet['resultSet'];
    }
    
   /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillModulesList ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillModulesList($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillModulesListRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillModulesListRtc($params);  
        return $resultSet['resultSet'];
    }    
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        return $DAL->makeActiveOrPassive($params);
    }
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillModulesDdList($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclModulesPDO');
        $resultSet = $DAL->fillModulesDdList($params);
        return $resultSet['resultSet'];
    }
  
}

