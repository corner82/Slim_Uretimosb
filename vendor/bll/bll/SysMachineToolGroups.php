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
class SysMachineToolGroups extends \BLL\BLLSlim {

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
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        return $DAL->insert($params);
    }

    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }

  

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillMachineToolGroups($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');        
         if (isset($params['parent_id']) && ($params['parent_id'] == 0))  { 
            $resultSet = $DAL->fillMachineToolGroups($params);
        } else {            
            if (isset($params['state']) && ($params['state'] == "closed") && 
                isset($params['last_node']) && ($params['last_node'] == "true") &&   
                isset($params['machine']) && $params['machine'] == "false" )  
            {            
                $resultSet = $DAL->fillMachineToolGroupsMachines($params);
            } else {                        
                $resultSet = $DAL->fillMachineToolGroups($params);                
            }
        }        
        return $resultSet['resultSet'];
    }
  /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillJustMachineToolGroups($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        $resultSet = $DAL->fillJustMachineToolGroups($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */      
    public function fillMachineToolGroupsMachineProperties($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');     
        return $DAL->fillMachineToolGroupsMachineProperties($params);
    }
 
    /**
     * Function to fill User Communications Types on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillJustMachineToolGroupsBootstrap ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        $resultSet = $DAL->fillJustMachineToolGroupsBootstrap($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill User Communications Types on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillJustMachineToolGroupsNotInProperty ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolGroupsPDO');
        $resultSet = $DAL->fillJustMachineToolGroupsNotInProperty($params);  
        return $resultSet['resultSet'];
    } 
}
