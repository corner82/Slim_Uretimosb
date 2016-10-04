<?php

// test commit for branch slim2
require 'vendor/autoload.php';

use \Services\Filter\Helper\FilterFactoryNames as stripChainers;


/* $app = new \Slim\Slim(array(
  'mode' => 'development',
  'debug' => true,
  'log.enabled' => true,
  )); */

$app = new \Slim\SlimExtended(array(
    'mode' => 'development',
    'debug' => true,
    'log.enabled' => true,
    'log.level' => \Slim\Log::INFO,
    'exceptions.rabbitMQ' => true,
    'exceptions.rabbitMQ.logging' => \Slim\SlimExtended::LOG_RABBITMQ_FILE,
    'exceptions.rabbitMQ.queue.name' => \Slim\SlimExtended::EXCEPTIONS_RABBITMQ_QUEUE_NAME
        ));

/**
 * "Cross-origion resource sharing" kontrolüne izin verilmesi için eklenmiştir
 * @author Mustafa Zeynel Dağlı
 * @since 2.10.2015
 */
$res = $app->response();
$res->header('Access-Control-Allow-Origin', '*');
$res->header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");

$app->add(new \Slim\Middleware\MiddlewareInsertUpdateDeleteLog());
$app->add(new \Slim\Middleware\MiddlewareHMAC());
$app->add(new \Slim\Middleware\MiddlewareSecurity());
$app->add(new \Slim\Middleware\MiddlewareMQManager());
$app->add(new \Slim\Middleware\MiddlewareBLLManager());
$app->add(new \Slim\Middleware\MiddlewareDalManager());
$app->add(new \Slim\Middleware\MiddlewareServiceManager());
$app->add(new \Slim\Middleware\MiddlewareMQManager());



  
/**
 * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkFillGridSingular_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillGridSingular_infoUsersCommunications" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    $resDataGrid = $BLL->fillGridSingular(array(
        'url' => $_GET['url'],
        'pk' => $pk,
        'language_code' => $vLanguageCode
                                            ));
    $resTotalRowCount = $BLL->fillGridSingularRowTotalCount(array(
        'url' => $_GET['url'],
        'pk' => $pk,
        'language_code' =>$vLanguageCode
                                            ));
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "profile_public" => $flow["profile_public"],
            "user_id" => $flow["user_id"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "name" => html_entity_decode($flow["name"]),
            "surname" => $flow["surname"],
            "deleted" => $flow["deleted"],
            "state_deleted" => html_entity_decode($flow["state_deleted"]),
            "active" => $flow["active"],
            "state_active" => html_entity_decode($flow["state_active"]),
            "language_code" => $flow["language_code"],
            "language_name" => html_entity_decode($flow["language_name"]),
            "language_parent_id" => $flow["language_parent_id"],
            "description" => html_entity_decode($flow["description"]),
            "description_eng" => html_entity_decode($flow["description_eng"]),
            "op_user_id" => $flow["op_user_id"],
            "op_username" => $flow["op_username"],
            "communications_type_id" => $flow["communications_type_id"],
            "comminication_type" => html_entity_decode($flow["comminication_type"]),
            "communications_no" => $flow["communications_no"],  
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "consultant_confirm_type" => html_entity_decode($flow["consultant_confirm_type"]),
            "confirm_id" => $flow["confirm_id"],  
            "operation_type_id" => $flow["operation_type_id"],              
            "operation_name" => html_entity_decode($flow["operation_name"]),
            "default_communication_id" => $flow["default_communication_id"],            
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 * bu servis kullanılmıyor
 */
$app->get("/pkFillGrid_infoUsersCommunications/", function () use ($app ) {    
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkInsert_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoUsersCommunications" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['profile_public']));
    }
    $vCommunicationsTypeId = 0;
    if (isset($_GET['communications_type_id'])) {
        $stripper->offsetSet('communications_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['communications_type_id']));
    }
    $vCommunicationsNo = NULL;
    if (isset($_GET['communications_no'])) {
        $stripper->offsetSet('communications_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['communications_no']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description_eng']));
    }
    $vDefaultCommunicationId = 0;
    if (isset($_GET['default_communication_id'])) {
        $stripper->offsetSet('default_communication_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['default_communication_id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('communications_type_id')) {
        $vCommunicationsTypeId = $stripper->offsetGet('communications_type_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('communications_no')) {
        $vCommunicationsNo = $stripper->offsetGet('communications_no')->getFilterValue();
    } 
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('default_communication_id')) {
        $vDefaultCommunicationId = $stripper->offsetGet('default_communication_id')->getFilterValue();
    } 
    $resDataInsert = $BLL->insert(array(  
            'url' => $_GET['url'],
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,              
            'communications_type_id' => $vCommunicationsTypeId, 
            'communications_no' => $vCommunicationsNo,
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng ,  
            'default_communication_id' => $vDefaultCommunicationId,
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
 
$app->get("/pkUpdate_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoUsersCommunications" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['id']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['profile_public']));
    }
    $vCommunicationsTypeId = 0;
    if (isset($_GET['communications_type_id'])) {
        $stripper->offsetSet('communications_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['communications_type_id']));
    }
    $vCommunicationsNo = NULL;
    if (isset($_GET['communications_no'])) {
        $stripper->offsetSet('communications_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['communications_no']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description_eng']));
    }
    $vDefaultCommunicationId = 0;
    if (isset($_GET['default_communication_id'])) {
        $stripper->offsetSet('default_communication_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['default_communication_id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('communications_type_id')) {
        $vCommunicationsTypeId = $stripper->offsetGet('communications_type_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('communications_no')) {
        $vCommunicationsNo = $stripper->offsetGet('communications_no')->getFilterValue();
    } 
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('default_communication_id')) {
        $vDefaultCommunicationId = $stripper->offsetGet('default_communication_id')->getFilterValue();
    } 
    $resDataInsert = $BLL->update(array(  
            'url' => $_GET['url'],
            'id' => $vId,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,              
            'communications_type_id' => $vCommunicationsTypeId, 
            'communications_no' => $vCommunicationsNo,
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng ,  
            'default_communication_id' => $vDefaultCommunicationId,
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkDeletedAct_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoUsersCommunications" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }    
    $resDataUpdate = $BLL->deletedAct(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'id' => $vId,
        'pk' => $pk)); 
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataUpdate));
});
 
/**
 *  * Okan CIRAN
 * @since 25-01-2016
 * bu servis kullanılmıyor
 */
$app->get("/pkGetAll_infoUsersCommunications/", function () use ($app ) {   
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/fillUserCommunicationsTypes_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "fillUserCommunicationsTypes_infoUsersCommunications" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }      
    $resCombobox = $BLL->fillUserCommunicationsTypes(array(
        'url' => $_GET['url'],           
        'language_code' => $vLanguageCode,
        'pk' => $pk
                            ));
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],            
            "text" => html_entity_decode($flow["name"]),
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true,   ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
   
/**x
 * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillGridSingular_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempFillGridSingular_infoUsersCommunications" end point, X-Public variable not found');     
    $vPkTemp = $headerParams['X-Public-Temp'];     
    $componentType = 'bootstrap';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }      
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    $resDataGrid = $BLL->fillGridSingularTemp(array(
        'url' => $_GET['url'],
        'pktemp' => $vPkTemp ,
        'language_code' => $vLanguageCode 
            ));
    $resTotalRowCount = $BLL->fillGridSingularRowTotalCountTemp(array(
        'url' => $_GET['url'],
        'pktemp' => $vPkTemp,
        'language_code' => $vLanguageCode 
            ));
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "communications_type_id" => $flow["communications_type_id"],
            "comminication_type" => html_entity_decode($flow["comminication_type"]),
            "communications_no" => $flow["communications_no"],              
            "default_communication_id" => $flow["default_communication_id"],
            "default_communication" => html_entity_decode($flow["default_communication"]),
            "attributes" => array("notroot" => true, ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;    
    if($componentType == 'bootstrap'){
        $app->response()->body(json_encode($flows));
    }else if($componentType == 'easyui'){
        $app->response()->body(json_encode($resultArray));
    }
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */ 
$app->get("/pktempInsert_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempInsert_infoUsersCommunications" end point, X-Public variable not found');     
    $vPkTemp = $headerParams['X-Public-Temp'];        
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }        
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['profile_public']));
    }
    $vCommunicationsTypeId = 0;
    if (isset($_GET['communications_type_id'])) {
        $stripper->offsetSet('communications_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['communications_type_id']));
    }
    $vCommunicationsNo = 0;
    if (isset($_GET['communications_no'])) {
        $stripper->offsetSet('communications_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['communications_no']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                            $app, 
                            $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                            $app, 
                            $_GET['description_eng']));
    }
    $vDefaultCommunicationId = 0;
    if (isset($_GET['default_communication_id'])) {
        $stripper->offsetSet('default_communication_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['default_communication_id']));
    }    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }         
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('communications_type_id')) {
        $vCommunicationsTypeId = $stripper->offsetGet('communications_type_id')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('default_communication_id')) {
        $vDefaultCommunicationId = $stripper->offsetGet('default_communication_id')->getFilterValue();
    }         
    $resDataUpdate = $BLL->insertTemp(array(  
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'profile_public' => $vProfilePublic,        
        'communications_type_id' => $vCommunicationsTypeId, 
        'communications_no' => $vCommunicationsNo,
        'description' => $vDescription ,
        'description_eng' => $vDescriptionEng ,          
        'default_communication_id' => $vDefaultCommunicationId,
        'pktemp' => $vPkTemp,
         ));       
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataUpdate));
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempUpdate_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempUpdate_infoUsersCommunications" end point, X-Public variable not found');     
    $vPkTemp = $headerParams['X-Public-Temp'];         
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vID = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['id']));
    }
    $vActive = 0;
    if (isset($_GET['active'])) {
        $stripper->offsetSet('active', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['active']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['profile_public']));
    }
    $vCommunicationsTypeId = 0;
    if (isset($_GET['communications_type_id'])) {
        $stripper->offsetSet('communications_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['communications_type_id']));
    }
    $vCommunicationsNo = 0;
    if (isset($_GET['communications_no'])) {
        $stripper->offsetSet('communications_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['communications_no']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                            $app, 
                            $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                            $app, 
                            $_GET['description_eng']));
    }
    $vDefaultCommunicationId = 0;
    if (isset($_GET['default_communication_id'])) {
        $stripper->offsetSet('default_communication_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['default_communication_id']));
    }
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('id')) {
        $vID = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('communications_type_id')) {
        $vCommunicationsTypeId = $stripper->offsetGet('communications_type_id')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('default_communication_id')) {
        $vDefaultCommunicationId = $stripper->offsetGet('default_communication_id')->getFilterValue();
    }         
    $resDataUpdate = $BLL->updateTemp(array(
        'url' => $_GET['url'],
        'id' =>$vID, 
        'active' => $vActive,                
        'language_code' => $vLanguageCode,
        'profile_public' => $vProfilePublic,        
        'communications_type_id' => $vCommunicationsTypeId, 
        'communications_no' => $vCommunicationsNo,
        'description' => $vDescription ,
        'description_eng' => $vDescriptionEng ,          
        'default_communication_id' => $vDefaultCommunicationId,
        'pktemp' => $vPkTemp,
         ));   
   
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataUpdate));
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempDeletedAct_infoUsersCommunications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempDeletedAct_infoUsersCommunications" end point, X-Public variable not found');     
    $vPkTemp = $headerParams['X-Public-Temp'];     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vID = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, 
                            $_GET['id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {
        $vID = $stripper->offsetGet('id')->getFilterValue();
    }   
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    $resDataUpdate = $BLL->deletedActTemp(array(
        'url' => $_GET['url'],
        'id' => $vID,    
        'language_code' => $vLanguageCode,
        'pktemp' => $vPkTemp)); 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($resDataUpdate));
});
 
/** x 
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillUserCommunicationsTypes_infoUsersCommunications/", function () use ($app ) {
 $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempFillUserCommunicationsTypes_infoUsersCommunications" end point, X-Public variable not found');     
    $vPkTemp = $headerParams['X-Public-Temp'];       
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }    
    $stripper->strip();     
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    $resCombobox = $BLL->fillUserCommunicationsTypesTemp(array(
        'url' => $_GET['url'],
        'pktemp' => $vPkTemp,
        'language_code' => $vLanguageCode,
                            ));
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],            
            "text" => html_entity_decode($flow["name"]),
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true,   ),
        );
    }
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($flows));
});

 

$app->run();
