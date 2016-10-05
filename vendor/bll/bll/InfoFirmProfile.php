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
class InfoFirmProfile extends \BLL\BLLSlim {

    /**
     * constructor
     */
    public function __construct() {
        //parent::__construct();
    }

    /**
     * Data insert function
     * @param array | null $params
     * @return array
     */
    public function insert($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->insert($params);
    }
      /**
     * Data insert function
     * @param array | null $params
     * @return array
     */
    public function insertConsAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->insertConsAct($params);
    }

    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function update($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->update($params);
    }
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function updateConsAct($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->updateConsAct($params);
    }
    
    /**
     * Data update function
     * @param array | null $params
     * @return array
     */
    public function updateVerbal($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->updateVerbal($params);
    }

    /**
     * Data delete function
    * @param array | null $params
     * @return array
     */
    public function delete($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->delete($params);
    }

    /**
     * get all data
     * @param array | null $params
     * @return array
     */
    public function getAll($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->getAll($params);
    }

    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGrid($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillGrid($params);
        return $resultSet['resultSet'];
    }

    /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillGridRowTotalCount($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillGridRowTotalCount($params);
        return $resultSet['resultSet'];
    }

    /**
     * Data delete action function
     * @param array | null $params
     * @return array
     */
    public function deletedAct( $params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->deletedAct($params);
    }

    /**
     * Function to fill combobox on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillComboBox($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillComboBox($params);
        return $resultSet['resultSet'];
    }

    /**
     * Data insert function (active languages)
     * @param array | null $params
     * @return array
     */
    public function insertLanguageTemplate($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->insertLanguageTemplate($params);
        return $resultSet['resultSet'];
    }

   /**
     * Function to fill text on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillTextLanguageTemplate($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillTextLanguageTemplate($params);
        return $resultSet['resultSet'];
    }
    
    
     /**
     * Data insert function
     * @param array | null $params
     * @return array
     */
    public function insertTemp($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->insertTemp($params);
    }
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillCompanyListsGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillCompanyListsGuest($params);
        return $resultSet['resultSet'];
    }
    /**
     * Function to fill datagrid on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillCompanyLists($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillCompanyListsGuest($params);
        return $resultSet['resultSet'];
    }
    
       /**
     * Function to get datagrid row count on user interface layer
     * @param array | null $params
     * @return array
     */
    public function fillCompanyListsGuestRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillCompanyListsGuestRtc($params);
        return $resultSet['resultSet'];
    }
    
 
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoEmployeesGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoEmployeesGuest($params);
    }
    
            /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoEmployees($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoEmployeesGuest($params);
    }
    
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoSocialediaGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoSocialediaGuest($params);
    }
      /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoSocialedia($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoSocialediaGuest($params);
    }
    
    
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoReferencesGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoReferencesGuest($params);
    }
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoReferences($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoReferencesGuest($params);
    }
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoCustomersGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoCustomersGuest($params);
    }
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoCustomers($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoCustomersGuest($params);
    }
    
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoProductsGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoProductsGuest($params);
    }
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoProducts($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoProductsGuest($params);
    }
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoSectorsGuest($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoSectorsGuest($params);
    }
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoSectors($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoSectorsGuest($params);
    }
    
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillCompanyInfoBuildingNpk($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillCompanyInfoBuildingNpk($params);
    }
     /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function fillFirmFullVerbal($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->fillFirmFullVerbal($params);
    }
    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function getFirmEndOfId($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->getFirmEndOfId($params);
    }
     
             /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     */
    public function getFirmProfileConsultant($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->getFirmProfileConsultant($params);
    }
    
    /**
     * Function to fill text on user interface layer
     * @param array $params
     * @return array
     */
    public function fillConsultantAllowFirmListDds($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillConsultantAllowFirmListDds($params);
        return $resultSet['resultSet'];
    }
    
    
    
      /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillConsCompanyLists($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillConsCompanyLists($params);  
        return $resultSet['resultSet'];
    }
    
    /**
     * Data update function   
     * @param array $params
     * @return array
     */ 
    public function fillConsCompanyListsRtc($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        $resultSet = $DAL->fillConsCompanyListsRtc($params);  
        return $resultSet['resultSet'];
    }
     
    /**
     * public key / private key and value update function
     * @param array | null $params
     * @return array
     */
    public function makeActiveOrPassive($params = array()) {
        $DAL = $this->slimApp->getDALManager()->get('infoFirmProfilePDO');
        return $DAL->makeActiveOrPassive($params);
    }
    
    
}
