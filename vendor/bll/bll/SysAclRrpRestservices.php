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
class SysAclRrpRestservices extends \BLL\BLLSlim {

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
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        return $DAL->insert($params);
    }

    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }
  
  
   /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillRrpRestServicesList ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillRrpRestServicesList($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillRrpRestServicesListRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillRrpRestServicesListRtc($params);  
        return $resultSet['resultSet'];
    }    
 
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillRestServicesOfPrivileges($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillRestServicesOfPrivileges($params);
        return $resultSet['resultSet'];
    }     
    
     /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillNotInRestServicesOfPrivileges ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillNotInRestServicesOfPrivileges($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillNotInRestServicesOfPrivilegesRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');
        $resultSet = $DAL->fillNotInRestServicesOfPrivilegesRtc($params);  
        return $resultSet['resultSet'];
    }    
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillNotInRestServicesOfPrivilegesTree($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');  
     // print_r($params);
         if (isset($params['parent_id']) && ($params['parent_id'] == 0))  { 
            $resultSet = $DAL->fillNotInServicesGroupsTree($params);
        } else {     
            $resultSet = $DAL->fillNotInRestServicesTree($params);
        }        
        return $resultSet['resultSet'];
    }
    
    
      /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillRestServicesOfPrivilegesTree($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysAclRrpRestservicesPDO');  
     // print_r($params);
         if (isset($params['parent_id']) && ($params['parent_id'] == 0))  { 
            $resultSet = $DAL->fillRestServicesGroupsOfPrivilegesTree($params);
        } else {     
            $resultSet = $DAL->fillRestServicesOfPrivilegesTree($params);
        }        
        return $resultSet['resultSet'];
    }
    

}
