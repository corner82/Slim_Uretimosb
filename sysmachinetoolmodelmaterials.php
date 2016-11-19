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
 * @since 15-02-2016
 */
$app->get("/pkInsert_sysMachineToolModelMaterials/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolModelMaterialsBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_sysMachineToolModelMaterials" end point, X-Public variable not found');     
     
    $vLanguageCode = 'tr';    
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
    $vMachineToolId = NULL;
    if (isset($_GET['machine_tool_id'])) {
         $stripper->offsetSet('machine_tool_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['machine_tool_id']));
    } 
    $vMaterialId = NULL;
    if (isset($_GET['material_id'])) {
         $stripper->offsetSet('material_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['material_id']));
    }  
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }   
    if ($stripper->offsetExists('machine_tool_id')) {
        $vMachineToolId = $stripper->offsetGet('machine_tool_id')->getFilterValue();
    }
    if ($stripper->offsetExists('material_id')) {
        $vMaterialId = $stripper->offsetGet('material_id')->getFilterValue();
    } 
    $resData = $BLL->insert(array(  
            'url' => $_GET['url'],
            'language_code' => $vLanguageCode, 
            'machine_tool_id' => $vMachineToolId , 
            'material_id' => $vMaterialId ,
            'pk' => $Pk,        
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 

 
/**
 *  * Okan CIRAN
 * @since 15-02-2016
 */
$app->get("/pkUpdate_sysMachineToolModelMaterials/", function () use ($app ) { 
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolModelMaterialsBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];   
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_sysMachineToolModelMaterials" end point, X-Public variable not found');     
     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $vMachineToolId = NULL;
    if (isset($_GET['machine_tool_id'])) {
         $stripper->offsetSet('machine_tool_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['machine_tool_id']));
    } 
    $vMaterialId = NULL;
    if (isset($_GET['material_id'])) {
         $stripper->offsetSet('material_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['material_id']));
    }  
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }   
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_tool_id')) {
        $vMachineToolId = $stripper->offsetGet('machine_tool_id')->getFilterValue();
    }
    if ($stripper->offsetExists('material_id')) {
        $vMaterialId = $stripper->offsetGet('material_id')->getFilterValue();
    } 
    $resData = $BLL->update(array(  
            'url' => $_GET['url'],
            'language_code' => $vLanguageCode, 
            'id' => $vId , 
            'machine_tool_id' => $vMachineToolId , 
            'material_id' => $vMaterialId ,
            'pk' => $Pk,        
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 /**x
 *  * Okan CIRAN
 * @since 13-04-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysMachineToolModelMaterials/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMachineToolModelMaterialsBLL');
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public']; 
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdateMakeActiveOrPassive_sysMachineToolModelMaterials" end point, X-Public variable not found');     
     
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {$vId = $stripper->offsetGet('id')->getFilterValue(); }
    $resData = $BLL->makeActiveOrPassive(array(                  
            'id' => $vId ,    
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 

/**x
 *  * Okan CIRAN
 * @since 13-04-2016
 */
$app->get("/pkDelete_sysMachineToolModelMaterials/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMachineToolModelMaterialsBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDelete_sysMachineToolModelMaterials" end point, X-Public variable not found');          
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {$vId = $stripper->offsetGet('id')->getFilterValue(); }  
    $resDataDeleted = $BLL->Delete(array(                  
            'id' => $vId ,    
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 
 
  
/**
 *  * Okan CIRAN
 * @since 18-08-2016
 *  rest servislere eklendi
 */
$app->get("/pkFillMachineToolModelListGrid_sysMachineToolModelMaterials/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysMachineToolModelMaterialsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillMachineToolModelListGrid_sysMachineToolModelMaterials" end point, X-Public variable not found');
    }
    //$pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }
    $vMachineToolId = NULL;
    if (isset($_GET['machine_tool_id'])) {
        $stripper->offsetSet('machine_tool_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['machine_groups_id']));
    }    
    $vPage = NULL;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, 
                $_GET['page']));
    }
    $vRows = NULL;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, 
                $_GET['rows']));
    }
    $vSort = NULL;
    if (isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['sort']));
    }
    $vOrder = NULL;
    if (isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ORDER, 
                $app, $_GET['order']));
    }
    $filterRules = null;
    if (isset($_GET['filterRules'])) {
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, 
                $app, $_GET['filterRules']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_tool_id')) {
        $vMachineToolId = $stripper->offsetGet('machine_tool_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('page')) {
        $vPage = $stripper->offsetGet('page')->getFilterValue();
    }
    if ($stripper->offsetExists('rows')) {
        $vRows = $stripper->offsetGet('rows')->getFilterValue();
    }
    if ($stripper->offsetExists('sort')) {
        $vSort = $stripper->offsetGet('sort')->getFilterValue();
    }
    if ($stripper->offsetExists('order')) {
        $vOrder = $stripper->offsetGet('order')->getFilterValue();
    }
    if ($stripper->offsetExists('filterRules')) {
        $filterRules = $stripper->offsetGet('filterRules')->getFilterValue();
    }
    
    $resDataGrid = $BLL->fillMachineToolModelListGrid(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'machine_tool_id' => $vMachineToolId,
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillMachineToolModelListGridRtc(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'machine_tool_id' => $vMachineToolId,        
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "machine_tool_id" => $flow["machine_tool_id"],
                "machine_tool_name" => html_entity_decode($flow["machine_tool_name"]),
                "machine_tool_name_eng" => html_entity_decode($flow["machine_tool_name_eng"]),
                "material_id" => $flow["material_id"],
                "material_name" => html_entity_decode($flow["material_name"]),
                "material_name_eng" => html_entity_decode($flow["material_name_eng"]),
                "state_active" => html_entity_decode($flow["state_active"]),                
                "attributes" => array(
                    "active" => $flow["active"],
                    "language_id" => $flow["language_id"],
                ),
            );
        } $counts = $resTotalRowCount[0]['count'];
    } ELSE {
        $flows = array();
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});
  
$app->run();
