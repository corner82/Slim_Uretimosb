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
class SysOsbConsultants extends \BLL\BLLSlim {

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
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->insert($params);
    }

    /**
     * Data update function
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->update($params);
    }

    /**
     * Data delete function
     * @param array $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }
 
 
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function getConsPendingFirmProfile($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->getConsPendingFirmProfile($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function getConsPendingFirmProfilertc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->getConsPendingFirmProfilertc($params);
    }
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function getConsConfirmationProcessDetails($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->getConsConfirmationProcessDetails($params);
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */
    public function getAllFirmCons($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->getAllFirmCons($params);
    } 
    
  /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillOsbConsultantListGrid ($params = array()) {        
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        $resultSet = $DAL->fillOsbConsultantListGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillOsbConsultantListGridRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        $resultSet = $DAL->fillOsbConsultantListGridRtc($params);  
        return $resultSet['resultSet'];
    }   
        /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysOsbConsultantsPDO');
        return $DAL->makeActiveOrPassive($params);
    } 
     
}
