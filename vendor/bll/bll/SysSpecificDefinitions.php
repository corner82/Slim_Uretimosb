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
class SysSpecificDefinitions extends \BLL\BLLSlim {

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
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        return $DAL->insert($params);
    }

    /**
     * Data update function 
     * @param array $params
     * @return array
     */
    public function update( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        return $DAL->update(  $params);
    }

    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');     
        $resultSet =  $DAL->getAll($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }

    /**
     *  
     * @param array  $params
     * @return array
     */
    public function fillMainDefinitions($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillMainDefinitions($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillFullDefinitions($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillFullDefinitions($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillCommunicationsTypes($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillCommunicationsTypes($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillBuildingType($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillBuildingType($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillOwnershipType($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillOwnershipType($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillPersonnelTypes($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillPersonnelTypes($params);
        return $resultSet['resultSet'];
    }
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillAddressTypes($params = array()) {

        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillAddressTypes($params);
        return $resultSet['resultSet'];
    }
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillSexTypes($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSpecificDefinitionsPDO');
        $resultSet = $DAL->fillSexTypes($params);
        return $resultSet['resultSet'];
    }
    
    
    
    
}
