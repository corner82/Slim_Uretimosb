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
class SysAclResources extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    /**
     *  
     * @param array  $params
     * @return array
     */
    public function fillComboBoxMainResources($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillComboBoxMainResources($params);  
        return $resultSet['resultSet'];
    }

     
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillComboBoxFullResources ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillComboBoxFullResources($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillResourcesTree ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillResourcesTree($params);  
        return $resultSet['resultSet'];
    }
    
   /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillPropertieslist ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillPropertieslist($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillPropertieslistRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillPropertieslistRtc($params);  
        return $resultSet['resultSet'];
    }    
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        return $DAL->makeActiveOrPassive($params);
    }
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillResourcesDdList($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');
        $resultSet = $DAL->fillResourcesDdList($params);
        return $resultSet['resultSet'];
    }
    
       
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillResourceGroups($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclResourcesPDO');  
     // print_r($params);
         if (isset($params['parent_id']) && ($params['parent_id'] == 0))  { 
            $resultSet = $DAL->fillResourceGroups($params);
        } else {        
            //if (isset($params['state']) && ($params['state'] == "closed") && 
            //    isset($params['last_node']) && ($params['last_node'] == "true") &&   
           //     isset($params['roles']) && $params['roles'] == "false" )  
           // {            
                $resultSet = $DAL->fillResourceGroupsRoles($params);
           // } else {                        
           //     $resultSet = $DAL->fillResourceGroups($params);                
           // }
        }        
        return $resultSet['resultSet'];
    }
    
    
}

