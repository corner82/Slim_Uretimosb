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
class SysSectors extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        return $DAL->insert($params);
    }
    
    /**
     * Data update function
     * @param integer $id
     * @param array $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        return $DAL->update( $params);
    }
    
    /**
     * Data delete function
     * @param integer $id
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        return $DAL->delete($params = array());
    }

    /**
     * get all data
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    /**
     *  
     * @param array  $params
     * @return array
     */
     
     
    public function fillComboBox($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        $resultSet = $DAL->fillComboBox($params);  
        return $resultSet['resultSet'];
    }
     
    
    public function insertLanguageTemplate($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        $resultSet = $DAL->insertLanguageTemplate($params);  
        return $resultSet['resultSet'];
    }

     
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillTextLanguageTemplate ($params = array()) {
        
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        $resultSet = $DAL->fillTextLanguageTemplate($params);  
        return $resultSet['resultSet'];
    }
    
    public function getSectors($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('sysSectorsPDO');
        $resultSet = $DAL->getSectors($params);  
        return $resultSet['resultSet'];
    }
     
    
    
}

