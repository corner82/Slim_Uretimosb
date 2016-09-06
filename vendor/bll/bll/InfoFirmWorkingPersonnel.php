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
class InfoFirmWorkingPersonnel extends \BLL\BLLSlim {

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
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->insert($params);
    }

    /**
     * Check Data function
     * @param array | null $params
     * @return array
     */
    public function haveRecords($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->haveRecords($params);
    }

    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }

    /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->deletedAct($params);
    }

    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        return $DAL->makeActiveOrPassive($params);
    }

    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillFirmWorkingPersonalNpk($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillFirmWorkingPersonalNpk($params);     
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillFirmWorkingPersonalNpkRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillFirmWorkingPersonalNpkRtc($params);
        return $resultSet['resultSet'];
    }

    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function fillFirmWorkingPersonalNpkQuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillFirmWorkingPersonalNpkQuest($params);    
        return $resultSet['resultSet'];         
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillFirmWorkingPersonalNpkQuestRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillFirmWorkingPersonalNpkQuestRtc($params);
        return $resultSet['resultSet'];
    }
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillFirmWorkingPersonalListGrid ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillFirmWorkingPersonalListGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillFirmWorkingPersonalListGridRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmWorkingPersonnelPDO');
        $resultSet = $DAL->fillFirmWorkingPersonalListGridRtc($params);  
        return $resultSet['resultSet'];
    }    
    
    

}
