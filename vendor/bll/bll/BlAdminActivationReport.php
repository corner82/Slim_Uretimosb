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
class BlAdminActivationReport extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        return $DAL->getAll($params);
    }
    
    
    /**
     *  
     * @param array$params
     * @return array
     */
    public function getConsultantOperation($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        $resultSet = $DAL->getConsultantOperation($params);  
        return $resultSet;
    }
    
       /**
     * 
     * @param array$params
     * @return array
     */
    public function getAllConsultantFirmCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        $resultSet = $DAL->getAllConsultantFirmCount($params);  
        return $resultSet;
    }
    
     /**
     *  
     * @param array$params
     * @return array
     */
    public function getUpDashBoardCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        $resultSet = $DAL->getUpDashBoardCount($params);  
        return $resultSet;
    }
    
        
     /**
     *  
     * @param array$params
     * @return array
     */
    public function getDashBoardHighCharts($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('blAdminActivationReportPDO');
        $resultSet = $DAL->getDashBoardHighCharts($params);  
        return $resultSet;
    }
    
   
    
    
    
    
}

