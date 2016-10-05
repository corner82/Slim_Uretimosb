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
class SysMachineToolPropertyDefinition extends \BLL\BLLSlim {

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
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->insert($params);
    }
    /**
     * DAta insert function
     * @param array | null $params
     * @return array
     */
    public function insertPropertyUnit($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->insertPropertyUnit($params);
    }
    

    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }

  

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillMachineToolGroupPropertyDefinitions($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillMachineToolGroupPropertyDefinitions($params);
        return $resultSet['resultSet'];
    }
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->makeActiveOrPassive($params);
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillMachineGroupPropertyDefinitions($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillMachineGroupPropertyDefinitions($params);
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillMachineGroupNotInPropertyDefinitions($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillMachineGroupNotInPropertyDefinitions($params);
        return $resultSet['resultSet'];
    }
    
    
        /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function deletePropertyMachineGroup($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->deletePropertyMachineGroup($params);
    }
    
      /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillMachineGroupProperties($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillMachineGroupProperties($params);
        return $resultSet['resultSet'];
    } 
    
 /**
     * DAta insert function
     * @param array | null $params
     * @return array
     */
    public function transferPropertyMachineGroup($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        return $DAL->transferPropertyMachineGroup($params);
    }    
     /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillPropertieslist($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillPropertieslist($params);
        return $resultSet['resultSet'];
    }

       /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillPropertieslistRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysMachineToolPropertyDefinitionPDO');
        $resultSet = $DAL->fillPropertieslistRtc($params);
        return $resultSet['resultSet'];
    }

    
    
    
}
