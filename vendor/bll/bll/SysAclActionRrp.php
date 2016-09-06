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
class SysAclActionRrp extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    } 
     
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillActionPrivilegesList ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillActionPrivilegesList($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillActionPrivilegesListRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillActionPrivilegesListRtc($params);  
        return $resultSet['resultSet'];
    }   
     
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        return $DAL->makeActiveOrPassive($params);
    } 
     
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillActionResourceGroups($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');        
         if (isset($params['parent_id']) && ($params['parent_id'] == 0))  { 
            $resultSet = $DAL->fillActionResourceGroups($params);
        } else {        
                $resultSet = $DAL->fillActionResourceGroupsPrivileges($params);            
        }        
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillActionPrivilegesOfRoles($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillActionPrivilegesOfRoles($params);
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillNotInActionPrivilegesOfRoles($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillNotInActionPrivilegesOfRoles($params);
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillActionPrivilegesOfRolesDdList($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillActionPrivilegesOfRolesDdList($params);
        return $resultSet['resultSet'];
    }
     
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
    */
    public function fillActionPrivilegesOfRolesList($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillActionPrivilegesOfRolesList($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillActionPrivilegesOfRolesListRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        $resultSet = $DAL->fillActionPrivilegesOfRolesListRtc($params);  
        return $resultSet['resultSet'];
    }    
     
    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function transferRolesActionPrivilege($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclActionRrpPDO');
        return $DAL->transferRolesActionPrivilege($params);
    }
    
}

