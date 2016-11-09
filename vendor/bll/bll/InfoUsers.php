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
class InfoUsers extends \BLL\BLLSlim{
    
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
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->insert($params);
    }
    
       /**
     * Data insert function
     * @param array | null $params
     * @return array
     */
    public function insertTemp($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->insertTemp($params);
    }
        /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function updateTemp($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->updateTemp($params);
    }
    
      /**
     * Check Data function
     * @param array | null $params
     * @return array
     */
    public function haveRecords($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->haveRecords($params);
    }
    
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->update($params);
    }
    
    /**
     * Data delete function
     * @param array | null $params
     * @return array
     */
    public function delete( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->getAll($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        $resultSet = $DAL->fillGrid($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);  
        return $resultSet['resultSet'];
    }
    
     /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->deletedAct($params);
    }
    
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function setPrivateKey($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->setPrivateKey($params);
    }
     
    /**
     * get Public Key Temp
     * @param array $params
     * @return array
     */
    public function getPublicKeyTemp($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->getPublicKeyTemp($params);
    }
    
    /**
     * get User Id - pk
     * @param array $params
     * @return array
     */
    public function getUserId($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->getUserId($params);
    }

    /**
     * get User Id - pkTemp
     * @param array $params
     * @return array
     */
    public function getUserIdTemp($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->getUserIdTemp($params);
    }
    
    /**
     * New user RrpMap insert function 
     * @param array | null $params
     * @return array
     */
    public function setNewUserRrpMap($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->setNewUserRrpMap($params);
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillUsersListNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        $resultSet = $DAL->fillUsersListNpk($params);  
        return $resultSet['resultSet'];
    }  
    
    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillUsersListNpkRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        $resultSet = $DAL->fillUsersListNpkRtc($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillUsersInformationNpk ($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        $resultSet = $DAL->fillUsersInformationNpk($params);  
        return $resultSet['resultSet'];
    }  
      
    /**
     * DAta insert function
     * @param array | null $params
     * @return array
     */
    public function insertConsultant($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->insertConsultant($params);
    }
     /**
     * DAta insert function
     * @param array | null $params
     * @return array
     */
    public function insertUrgePerson($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->insertUrgePerson($params);
    }
    
    /**
     * New user RrpMap insert function 
     * @param array | null $params
     * @return array
     */
    public function setPersonPassword($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->setPersonPassword($params);
    }
    
        /**
     * New user RrpMap insert function 
     * @param array | null $params
     * @return array
     */
    public function updatePktempForSesionId($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoUsersPDO');
        return $DAL->updatePktempForSesionId($params);
    }
    
    
}

