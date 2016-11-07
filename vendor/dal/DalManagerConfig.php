<?php

/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace DAL;

/**
 * class called for DAL manager config 
 * DAL manager uses Zend Service manager and 
 * config class is compliant zend service config structure
 * @author Mustafa Zeynel Dağlı
 */
class DalManagerConfig {

    /**
     * constructor
     */
    public function __construct() {
        
    }

    /**
     * config array for zend service manager config
     * @var array
     */
    protected $config = array(
        // Initial configuration with which to seed the ServiceManager.
        // Should be compatible with Zend\ServiceManager\Config.
        'service_manager' => array(
            'invokables' => array(
            //'test' => 'Utill\BLL\Test\Test'
            ),
            'factories' => [
                'reportConfigurationPDO' => 'DAL\Factory\PDO\ReportConfigurationFactory',
                'cmpnyEqpmntPDO' => 'DAL\Factory\PDO\CmpnyEqpmntFactory',
                'sysNavigationLeftPDO' => 'DAL\Factory\PDO\SysNavigationLeftFactory',
                'sysSectorsPDO' => 'DAL\Factory\PDO\SysSectorsFactory',
                'infoUsersPDO' => 'DAL\Factory\PDO\InfoUsersFactory',
                'sysCountrysPDO' => 'DAL\Factory\PDO\SysCountrysFactory',
                'sysCityPDO' => 'DAL\Factory\PDO\SysCityFactory',
                'sysLanguagePDO' => 'DAL\Factory\PDO\SysLanguageFactory',
                'sysBoroughPDO' => 'DAL\Factory\PDO\SysBoroughFactory',
                'sysVillagePDO' => 'DAL\Factory\PDO\SysVillageFactory',      
                'blLoginLogoutPDO' => 'DAL\Factory\PDO\BlLoginLogoutFactory',   
                'infoFirmProfilePDO' => 'DAL\Factory\PDO\InfoFirmProfileFactory',   
                'sysAclRolesPDO' => 'DAL\Factory\PDO\SysAclRolesFactory',   
                'sysAclResourcesPDO' => 'DAL\Factory\PDO\SysAclResourcesFactory',   
                'sysAclPrivilegePDO' => 'DAL\Factory\PDO\SysAclPrivilegeFactory',   
                'sysAclRrpMapPDO' => 'DAL\Factory\PDO\SysAclRrpMapFactory',  
                'sysSpecificDefinitionsPDO' => 'DAL\Factory\PDO\SysSpecificDefinitionsFactory', 
                'infoUsersCommunicationsPDO' => 'DAL\Factory\PDO\InfoUsersCommunicationsFactory', 
                'infoUsersAddressesPDO' => 'DAL\Factory\PDO\InfoUsersAddressesFactory', 
                'blActivationReportPDO' => 'DAL\Factory\PDO\BlActivationReportFactory', 
                'sysOsbConsultantsPDO' => 'DAL\Factory\PDO\SysOsbConsultantsFactory', 
                'sysOsbPDO' => 'DAL\Factory\PDO\SysOsbFactory', 
                'sysOperationTypesPDO' => 'DAL\Factory\PDO\SysOperationTypesFactory',
                'sysOperationTypesToolsPDO' => 'DAL\Factory\PDO\SysOperationTypesToolsFactory', 
                'infoErrorPDO' => 'DAL\Factory\PDO\InfoErrorFactory', 
                'sysMachineToolGroupsPDO' => 'DAL\Factory\PDO\SysMachineToolGroupsFactory', 
                'sysMachineToolsPDO' => 'DAL\Factory\PDO\SysMachineToolsFactory',
                'sysMachineToolPropertyDefinitionPDO' => 'DAL\Factory\PDO\SysMachineToolPropertyDefinitionFactory',
                'sysMachineToolPropertiesPDO' => 'DAL\Factory\PDO\SysMachineToolPropertiesFactory',
                'sysUnitsPDO' => 'DAL\Factory\PDO\SysUnitsFactory',
                'infoFirmMachineToolPDO' => 'DAL\Factory\PDO\InfoFirmMachineToolFactory',
                'sysNaceCodesPDO' => 'DAL\Factory\PDO\SysNaceCodesFactory',
                'hstryLoginPDO' => 'DAL\Factory\PDO\HstryLoginFactory',
                'blAdminActivationReportPDO' => 'DAL\Factory\PDO\BlAdminActivationReportFactory',
                'sysUnspscCodesPDO' => 'DAL\Factory\PDO\SysUnspscCodesFactory', 
                'infoFirmKeysPDO' => 'DAL\Factory\PDO\InfoFirmKeysFactory', 
                'sysCertificationsPDO' => 'DAL\Factory\PDO\SysCertificationsFactory', 
                'sysUnitSystemsPDO' => 'DAL\Factory\PDO\SysUnitSystemsFactory',
                'infoFirmReferencesPDO' => 'DAL\Factory\PDO\InfoFirmReferencesFactory',
                'sysProductionTypesPDO' => 'DAL\Factory\PDO\SysProductionTypesFactory',
                'infoFirmUsersPDO' => 'DAL\Factory\PDO\InfoFirmUsersFactory',
                'sysMachineToolDefinitionAttributePDO' => 'DAL\Factory\PDO\SysMachineToolDefinitionAttributeFactory',
                'infoUsersSocialmediaPDO' => 'DAL\Factory\PDO\InfoUsersSocialmediaFactory',
                'infoFirmVerbalPDO' => 'DAL\Factory\PDO\InfoFirmVerbalFactory',
                'infoFirmUserDescForCompanyPDO' => 'DAL\Factory\PDO\InfoFirmUserDescForCompanyFactory',
                'sysSocialMediaPDO' => 'DAL\Factory\PDO\SysSocialMediaFactory',
                'infoFirmSocialmediaPDO' => 'DAL\Factory\PDO\InfoFirmSocialmediaFactory',
                'infoFirmAddressPDO' => 'DAL\Factory\PDO\InfoFirmAddressFactory',
                'sysManufacturerPDO' => 'DAL\Factory\PDO\SysManufacturerFactory',
                'infoFirmProductsPDO' => 'DAL\Factory\PDO\InfoFirmProductsFactory',
                'sysMailServerPDO' => 'DAL\Factory\PDO\SysMailServerFactory',
                'infoFirmProductsServicesPDO' => 'DAL\Factory\PDO\InfoFirmProductsServicesFactory',
                'infoFirmCertificatePDO' => 'DAL\Factory\PDO\InfoFirmCertificateFactory',
                'infoFirmQualityPDO' => 'DAL\Factory\PDO\InfoFirmQualityFactory',
                'infoFirmSectoralPDO' => 'DAL\Factory\PDO\InfoFirmSectoralFactory',
                'infoFirmLanguageInfoPDO' => 'DAL\Factory\PDO\InfoFirmLanguageInfoFactory',
                'sysCustomerCriterionPDO' => 'DAL\Factory\PDO\SysCustomerCriterionFactory',
                'sysClustersPDO' => 'DAL\Factory\PDO\SysClustersFactory',
                'sysOsbClustersPDO' => 'DAL\Factory\PDO\SysOsbClustersFactory',
                'sysOsbClustersFirmsPDO' => 'DAL\Factory\PDO\SysOsbClustersFirmsFactory',                  
                'infoUsersVerbalPDO' => 'DAL\Factory\PDO\InfoUsersVerbalFactory',
                'infoUsersProductsServicesPDO' => 'DAL\Factory\PDO\InfoUsersProductsServicesFactory',
                'sysOsbClustersAlliancePDO' => 'DAL\Factory\PDO\SysOsbClustersAllianceFactory',
                'sysMembershipTypesPDO' => 'DAL\Factory\PDO\SysMembershipTypesFactory',
                'sysAclRrpPDO' => 'DAL\Factory\PDO\SysAclRrpFactory',
                'sysUniversitiesPDO' => 'DAL\Factory\PDO\SysUniversitiesFactory',
                'infoFirmWorkingPersonnelPDO' => 'DAL\Factory\PDO\InfoFirmWorkingPersonnelFactory',
                'infoFirmWorkingPersonnelEducationPDO' => 'DAL\Factory\PDO\InfoFirmWorkingPersonnelEducationFactory',
                'sysMenuTypesPDO' => 'DAL\Factory\PDO\SysMenuTypesFactory',
                'sysAclModulesPDO' => 'DAL\Factory\PDO\SysAclModulesFactory',
                'sysAclActionsPDO' => 'DAL\Factory\PDO\SysAclActionsFactory',
                'sysAclMenuTypesActionsPDO' => 'DAL\Factory\PDO\SysAclMenuTypesActionsFactory',
                'sysAclRrpRestservicesPDO' => 'DAL\Factory\PDO\SysAclRrpRestservicesFactory',
                'sysServicesGroupsPDO' => 'DAL\Factory\PDO\SysServicesGroupsFactory',
                'sysAclRestservicesPDO' => 'DAL\Factory\PDO\SysAclRestservicesFactory',
                'sysAssignDefinitionPDO' => 'DAL\Factory\PDO\SysAssignDefinitionFactory',   
                'sysAssignDefinitionRolesPDO' => 'DAL\Factory\PDO\SysAssignDefinitionRolesFactory',   
                'sysAclActionRrpPDO' => 'DAL\Factory\PDO\SysAclActionRrpFactory',   
                'sysAclActionRrpRestservicesPDO' => 'DAL\Factory\PDO\SysAclActionRrpRestservicesFactory',   
                'infoFirmConsultantsPDO' => 'DAL\Factory\PDO\InfoFirmConsultantsFactory',   
                'sysOsbPersonPDO' => 'DAL\Factory\PDO\SysOsbPersonFactory',   
                'infoUsersSendingMailPDO' => 'DAL\Factory\PDO\InfoUsersSendingMailFactory',
                'sysMachineToolModelMaterialsPDO' => 'DAL\Factory\PDO\SysMachineToolModelMaterialsFactory',
                
                
                
                
                                 
                'logConnectionPDO' => 'DAL\Factory\PDO\LogConnectionFactory',
                'logServicesPDO' => 'DAL\Factory\PDO\LogServicesFactory',
                'logConsultantPDO' => 'DAL\Factory\PDO\LogConsultantFactory',
                'logConsultantPDO' => 'DAL\Factory\PDO\LogConsultantFactory',
                'logAdminPDO' => 'DAL\Factory\PDO\LogAdminFactory',
                 
                
                'sysOperationTypesRrpPDO' => 'DAL\Factory\PDO\SysOperationTypesRrpFactory',   
                'pgClassPDO' => 'DAL\Factory\PDO\PgClassFactory',
                'actProcessConfirmPDO' => 'DAL\Factory\PDO\ActProcessConfirmFactory',
                 
                
                
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
