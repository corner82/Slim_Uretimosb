<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace BLL;

/**
 * Business LAyer MAnager config class
 * Uses zend Framework manager config infrastructure
 */
class BLLManagerConfig{
    
    /**
     * constructor
     */
    public function __construct() {
        
    }
    
    /**
     * config array for zend service manager config
     * @var array
     */
    protected $config= array(
        // Initial configuration with which to seed the ServiceManager.
        // Should be compatible with Zend\ServiceManager\Config.
         'service_manager' => array(
             'invokables' => array(
                 //'test' => 'Utill\BLL\Test\Test'
                 'reportConfigurationBLL' => 'BLL\BLL\ReportConfiguration',
                 'cmpnyEqpmntBLL' => 'BLL\BLL\CmpnyEqpmnt',
                 'sysNavigationLeftBLL' => 'BLL\BLL\SysNavigationLeft',
                 'sysSectorsBLL' => 'BLL\BLL\SysSectors',
                 'infoUsersBLL' => 'BLL\BLL\InfoUsers',
                 'sysCountrysBLL' => 'BLL\BLL\SysCountrys',
                 'sysCityBLL' => 'BLL\BLL\SysCity',
                 'sysLanguageBLL' => 'BLL\BLL\SysLanguage',
                 'sysBoroughBLL' => 'BLL\BLL\SysBorough',
                 'sysVillageBLL' => 'BLL\BLL\SysVillage',
                 'blLoginLogoutBLL' => 'BLL\BLL\BlLoginLogout',
                 'infoFirmProfileBLL' => 'BLL\BLL\InfoFirmProfile',
                 'sysAclRolesBLL' => 'BLL\BLL\SysAclRoles',
                 'sysAclResourcesBLL' => 'BLL\BLL\SysAclResources',
                 'sysAclPrivilegeBLL' => 'BLL\BLL\SysAclPrivilege',
                 'sysAclRrpMapBLL' => 'BLL\BLL\SysAclRrpMap',  
                 'sysSpecificDefinitionsBLL' => 'BLL\BLL\SysSpecificDefinitions',   
                 'infoUsersCommunicationsBLL' => 'BLL\BLL\InfoUsersCommunications',   
                 'infoUsersAddressesBLL' => 'BLL\BLL\InfoUsersAddresses',   
                 'blActivationReportBLL' => 'BLL\BLL\BlActivationReport',
                 'sysOsbConsultantsBLL' => 'BLL\BLL\SysOsbConsultants',                 
                 'sysOsbBLL' => 'BLL\BLL\SysOsb',
                 'sysOperationTypesBLL' => 'BLL\BLL\SysOperationTypes',
                 'sysOperationTypesToolsBLL' => 'BLL\BLL\SysOperationTypesTools',
                 'infoErrorBLL' => 'BLL\BLL\InfoError',
                 'sysMachineToolGroupsBLL' => 'BLL\BLL\SysMachineToolGroups',
                 'sysMachineToolsBLL' => 'BLL\BLL\SysMachineTools',
                 'sysMachineToolPropertyDefinitionBLL' => 'BLL\BLL\SysMachineToolPropertyDefinition',
                 'sysMachineToolPropertiesBLL' => 'BLL\BLL\SysMachineToolProperties',
                 'sysUnitsBLL' => 'BLL\BLL\SysUnits',
                 'infoFirmMachineToolBLL' => 'BLL\BLL\InfoFirmMachineTool',
                 'sysNaceCodesBLL' => 'BLL\BLL\SysNaceCodes',
                 'hstryLoginBLL' => 'BLL\BLL\HstryLogin',              
                 'blAdminActivationReportBLL' => 'BLL\BLL\BlAdminActivationReport',  
                 'sysUnspscCodesBLL' => 'BLL\BLL\SysUnspscCodes',
                 'infoFirmKeysBLL' => 'BLL\BLL\InfoFirmKeys',
                 'sysCertificationsBLL' => 'BLL\BLL\SysCertifications',
                 'sysUnitSystemsBLL' => 'BLL\BLL\SysUnitSystems',
                 'infoFirmReferencesBLL' => 'BLL\BLL\InfoFirmReferences',
                 'sysProductionTypesBLL' => 'BLL\BLL\SysProductionTypes',
                 'infoFirmUsersBLL' => 'BLL\BLL\InfoFirmUsers',
                 'sysMachineToolDefinitionAttributeBLL' => 'BLL\BLL\SysMachineToolDefinitionAttribute',
                 'infoUsersSocialmediaBLL' => 'BLL\BLL\InfoUsersSocialmedia',
                 'infoFirmVerbalBLL' => 'BLL\BLL\InfoFirmVerbal',
                 'infoFirmUserDescForCompanyBLL' => 'BLL\BLL\InfoFirmUserDescForCompany',
                 'sysSocialMediaBLL' => 'BLL\BLL\SysSocialMedia',
                 'infoFirmSocialmediaBLL' => 'BLL\BLL\InfoFirmSocialmedia',
                 'infoFirmAddressBLL' => 'BLL\BLL\InfoFirmAddress',
                 'sysManufacturerBLL' => 'BLL\BLL\SysManufacturer',
                 'infoFirmProductsBLL' => 'BLL\BLL\InfoFirmProducts',
                 'sysMailServerBLL' => 'BLL\BLL\SysMailServer',                 
                 'infoFirmProductsServicesBLL' => 'BLL\BLL\InfoFirmProductsServices',
                 'infoFirmCertificateBLL' => 'BLL\BLL\InfoFirmCertificate',
                 'infoFirmQualityBLL' => 'BLL\BLL\InfoFirmQuality',
                 'infoFirmSectoralBLL' => 'BLL\BLL\InfoFirmSectoral',
                 'infoFirmLanguageInfoBLL' => 'BLL\BLL\InfoFirmLanguageInfo',
                 'sysCustomerCriterionBLL' => 'BLL\BLL\SysCustomerCriterion',
                 'sysClustersBLL' => 'BLL\BLL\SysClusters',
                 'sysOsbClustersBLL' => 'BLL\BLL\SysOsbClusters',
                 'sysOsbClustersFirmsBLL' => 'BLL\BLL\SysOsbClustersFirms',
                 'infoUsersVerbalBLL' => 'BLL\BLL\InfoUsersVerbal',
                 'infoUsersProductsServicesBLL' => 'BLL\BLL\InfoUsersProductsServices',
                 'sysOsbClustersAllianceBLL' => 'BLL\BLL\SysOsbClustersAlliance',
                 'sysMembershipTypesBLL' => 'BLL\BLL\SysMembershipTypes',
                 'sysAclRrpBLL' => 'BLL\BLL\SysAclRrp',
                 'sysUniversitiesBLL' => 'BLL\BLL\SysUniversities',
                 'infoFirmWorkingPersonnelBLL' => 'BLL\BLL\InfoFirmWorkingPersonnel',
                 'infoFirmWorkingPersonnelEducationBLL' => 'BLL\BLL\InfoFirmWorkingPersonnelEducation',
                 'sysMenuTypesBLL' => 'BLL\BLL\SysMenuTypes',
                 'sysAclModulesBLL' => 'BLL\BLL\SysAclModules',
                 'sysAclActionsBLL' => 'BLL\BLL\SysAclActions',
                 'sysAclMenuTypesActionsBLL' => 'BLL\BLL\SysAclMenuTypesActions',
                 'sysAclRrpRestservicesBLL' => 'BLL\BLL\SysAclRrpRestservices',
                 'sysServicesGroupsBLL' => 'BLL\BLL\SysServicesGroups',
                 'sysAclRestservicesBLL' => 'BLL\BLL\SysAclRestservices',
                 'sysAssignDefinitionBLL' => 'BLL\BLL\SysAssignDefinition',
                 'sysAssignDefinitionRolesBLL' => 'BLL\BLL\SysAssignDefinitionRoles',
                 'sysAclActionRrpBLL' => 'BLL\BLL\SysAclActionRrp',
                 'sysAclActionRrpRestservicesBLL' => 'BLL\BLL\SysAclActionRrpRestservices',
                 'infoFirmConsultantsBLL' => 'BLL\BLL\InfoFirmConsultants',
                 'sysOsbPersonBLL' => 'BLL\BLL\SysOsbPerson',
                 'infoUsersSendingMailBLL' => 'BLL\BLL\InfoUsersSendingMail',
                 'sysMachineToolModelMaterialsBLL' => 'BLL\BLL\SysMachineToolModelMaterials',
               
                      
                 'logConnectionBLL' => 'BLL\BLL\LogConnection',  
                 'logServicesBLL' => 'BLL\BLL\LogServices',
                 'logConsultantBLL' => 'BLL\BLL\LogConsultant',
                 'logAdminBLL' => 'BLL\BLL\LogAdmin',
                 
                 'opUserIdBLL' => 'BLL\BLL\InfoUsers', 
                 'operationsTypesBLL' => 'BLL\BLL\SysOperationTypesRrp',  
                 'languageIdBLL' => 'BLL\BLL\SysLanguage',  
                 'beAssignedConsultantBLL' => 'BLL\BLL\SysOsbConsultants',  
                 'operationTableNameBLL' => 'BLL\BLL\PgClass',                   
                 'consultantProcessSendBLL' => 'BLL\BLL\ActProcessConfirm',  
                 
                 
                  
                 
                 
                'pgClassBLL' => 'BLL\BLL\PgClass',
                'sysOperationTypesRrpBLL' => 'BLL\BLL\SysOperationTypesRrp',
                'actProcessConfirmBLL' => 'BLL\BLL\ActProcessConfirm',
                 
               
                 
                  
                 
             ),
             'factories' => [
                 //'reportConfigurationPDO' => 'BLL\BLL\ReportConfiguration',
             ],  

         ),
     );
    
    /**
     * return config array for zend service manager config
     * @return array | null
     * @author Mustafa Zeynel Dağlı
     */
    public function getConfig() {
        return $this->config['service_manager'];
    }

}




