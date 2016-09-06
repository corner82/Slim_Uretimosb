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

   
/**
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkInsert_infoFirmUsers/", function () use ($app ) {  
   $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmUsersBLL');    
    $headerParams = $app->request()->headers();        
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkInsert_infoFirmUsers" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vFirmId = NULL;
    if (isset($_GET['firm_id'])) {
        $stripper->offsetSet('firm_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }    
    $vUserId = NULL;
    if (isset($_GET['user_id'])) {
        $stripper->offsetSet('user_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['user_id']));
    }   
    $vDescription = NULL;
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }   
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    }   
     $vTitle = NULL;
    if (isset($_GET['title'])) {
         $stripper->offsetSet('title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title']));
    }   
    $vTitleEng = NULL;
    if (isset($_GET['title_eng'])) {
         $stripper->offsetSet('title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title_eng']));
    }        
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    }
    if ($stripper->offsetExists('user_id')) {
        $vUserId = $stripper->offsetGet('user_id')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('title')) {
        $vTitle = $stripper->offsetGet('title')->getFilterValue();
    }
    if ($stripper->offsetExists('title_eng')) {
        $vTitleEng = $stripper->offsetGet('title_eng')->getFilterValue();
    }
      
    $resDataInsert = $BLL->insert(array(   
            'language_code' => $vLanguageCode,
            'firm_id'=> $vFirmId,  
            'user_id'=> $vUserId,         
            'description'=> $vDescription,
            'description_eng'=> $vDescriptionEng,
            'title'=> $vTitle,
            'title_eng'=> $vTitleEng,             
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkUpdate_infoFirmUsers/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmUsersBLL');    
    $headerParams = $app->request()->headers();        
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkUpdate_infoFirmUsers" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }   
    $vFirmId = NULL;
    if (isset($_GET['firm_id'])) {
        $stripper->offsetSet('firm_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }    
    $vUserId = NULL;
    if (isset($_GET['user_id'])) {
        $stripper->offsetSet('user_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['user_id']));
    }   
    $vDescription = NULL;
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }   
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    }   
    $vTitle = NULL;
    if (isset($_GET['title'])) {
         $stripper->offsetSet('title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title']));
    }   
    $vTitleEng = NULL;
    if (isset($_GET['title_eng'])) {
         $stripper->offsetSet('title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title_eng']));
    }        
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    }
    if ($stripper->offsetExists('user_id')) {
        $vUserId = $stripper->offsetGet('user_id')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('title')) {
        $vTitle = $stripper->offsetGet('title')->getFilterValue();
    }
    if ($stripper->offsetExists('title_eng')) {
        $vTitleEng = $stripper->offsetGet('title_eng')->getFilterValue();
    }
      
    $resDataInsert = $BLL->insert(array(   
            'language_code' => $vLanguageCode,
            'id'=> $vId,  
            'firm_id'=> $vFirmId,  
            'user_id'=> $vUserId,         
            'description'=> $vDescription,
            'description_eng'=> $vDescriptionEng,
            'title'=> $vTitle,
            'title_eng'=> $vTitleEng,             
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 


/**
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkFillGrid_infoFirmUsers/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmUsersBLL');
    $headerParams = $app->request()->headers(); 
     if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillGrid_infoFirmUsers" end point, X-Public variable not found');
    }
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    $resDataGrid = $BLL->fillGrid(array(
        'language_code' => $vLanguageCode,
    ));
    $resTotalRowCount = $BLL->fillGridRowTotalCount(array(
        'language_code' => $vLanguageCode,
    ));

 
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "user_id" => $flow["user_id"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "title" => $flow["title"],
            "title_eng" => $flow["title_eng"],
            "description" => $flow["description"],
            "description_eng" => $flow["description_eng"],
            "firm_name" => $flow["firm_name"],
            "network_key" => $flow["network_key"],
            "picture" => $flow["picture"],
            
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "consultant_id" => $flow["consultant_id"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name" => $flow["operation_name"],            
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "language_id" => $flow["language_id"],
            "language_name" => $flow["language_name"],
            "op_user_id" => $flow["op_user_id"],
            "op_user_name" => $flow["op_user_name"],
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
 * @since 20-04-2016
 */
$app->get("/pkFillGridSingularNpk_infoFirmUsers/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmUsersBLL');
    $headerParams = $app->request()->headers(); 
     if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillGridSingularNpk_infoFirmUsers" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $resDataGrid = $BLL->fillGridSingularNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,  
        'pk'=> $pk,
    ));
    $resTotalRowCount = $BLL->fillGridSingularNpkRtc(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,   
        'pk'=> $pk,
    ));

    
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "user_id" => $flow["user_id"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "title" => $flow["title"],
            "title_eng" => $flow["title_eng"],
            "description" => $flow["description"],
            "description_eng" => $flow["description_eng"],
            "firm_name" => $flow["firm_name"],
            "network_key" => $flow["network_key"],
            "picture" => $flow["picture"],
            
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "consultant_id" => $flow["consultant_id"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name" => $flow["operation_name"],            
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "language_id" => $flow["language_id"],            
            "op_user_id" => $flow["op_user_id"],
            "op_user_name" => $flow["op_user_name"],          
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

/**x
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_infoFirmUsers/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmUsersBLL');
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    $resData = $BLL->makeActiveOrPassive(array(
        'id' => $vId,
        'pk' => $Pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resData));
}
);

 
/**
 *  * Okan CIRAN
 * @since 21-04-2016
 */
$app->get("/pkFillCompanyUsersSocialMediaNpk_infoFirmUsers/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmUsersBLL');
    $headerParams = $app->request()->headers(); 
     if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillGridSingularNpk_infoFirmUsers" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = '-1';
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }    
    $vUserId = NULL;
    if (isset($_GET['user_id'])) {
        $stripper->offsetSet('user_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['user_id']));
    } 
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    if ($stripper->offsetExists('user_id')) {
        $vUserId = $stripper->offsetGet('user_id')->getFilterValue();
    } 
    $resDataGrid = $BLL->fillCompanyUsersSocialMediaNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,  
        'user_id' => $vUserId,  
        'pk'=> $pk,
    ));
    
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "user_id" => $flow["user_id"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "socialmedia_name" => $flow["socialmedia_name"],
            "socialmedia_eng" => $flow["socialmedia_eng"],
            "user_link" => $flow["user_link"],
            "abbreviation" => $flow["abbreviation"], 
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();    
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

$app->run();
