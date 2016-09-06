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
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkInsert_sysAclMenuTypesActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclMenuTypesActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_sysAclMenuTypesActions" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vMenuTypesId = NULL;
    if (isset($_GET['menu_types_id'])) {
        $stripper->offsetSet('menu_types_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, $_GET['menu_types_id']));
    }
    $vActionId = NULL;
    if (isset($_GET['action_id'])) {
        $stripper->offsetSet('action_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, $_GET['action_id']));
    }
     
    $stripper->strip();
    if ($stripper->offsetExists('menu_types_id'))
        $vMenuTypesId = $stripper->offsetGet('menu_types_id')->getFilterValue();    
    if ($stripper->offsetExists('action_id'))
        $vActionId = $stripper->offsetGet('action_id')->getFilterValue();


    $resDataInsert = $BLL->insert(array(
        'action_id' => $vActionId,
        'menu_types_id' => $vMenuTypesId,        
        'pk' => $pk));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);
/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkUpdate_sysAclMenuTypesActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclMenuTypesActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_sysAclMenuTypesActions" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $vMenuTypesId = NULL;
    if (isset($_GET['menu_types_id'])) {
        $stripper->offsetSet('menu_types_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, $_GET['menu_types_id']));
    }
    $vActionId = NULL;
    if (isset($_GET['action_id'])) {
        $stripper->offsetSet('action_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                            $app, $_GET['action_id']));
    }
     
    $stripper->strip();
    if ($stripper->offsetExists('id'))
        $vId = $stripper->offsetGet('id')->getFilterValue();
    if ($stripper->offsetExists('menu_types_id'))
        $vMenuTypesId = $stripper->offsetGet('menu_types_id')->getFilterValue();    
    if ($stripper->offsetExists('action_id'))
        $vActionId = $stripper->offsetGet('action_id')->getFilterValue();
    

   
    $resDataInsert = $BLL->update(array(
        'id' => $vId,        
        'menu_types_id' => $vMenuTypesId,
        'action_id' => $vActionId,
        'pk' => $pk));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);

/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkDelete_sysAclMenuTypesActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclMenuTypesActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDelete_sysAclMenuTypesActions" end point, X-Public variable not found');
    $Pk = $headerParams['X-Public'];
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    $resDataDeleted = $BLL->Delete(array(
        'id' => $vId,
        'pk' => $Pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataDeleted));
}
);
 
/* * x
 *  * Okan CIRAN
 * @since 26-07-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysAclMenuTypesActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclMenuTypesActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkUpdateMakeActiveOrPassive_sysAclMenuTypesActions" end point, X-Public variable not found');
    }
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
 * @since 15-06-2016
 */
$app->get("/pkFillMenuTypesActionLeftList_sysAclMenuTypesActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclMenuTypesActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillMenuTypesActionLeftList_sysAclMenuTypesActions" end point, X-Public variable not found');
    }
    //  $pk = $headerParams['X-Public'];
 
    $vPage = NULL;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['page']));
    }
    $vRows = NULL;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['rows']));
    }
    $vSort = NULL;
    if (isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['sort']));
    }
    $vOrder = NULL;
    if (isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ORDER, $app, $_GET['order']));
    }
    $filterRules = null;
    if (isset($_GET['filterRules'])) {
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, $app, $_GET['filterRules']));
    }

    $stripper->strip();
    
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

    $resDataGrid = $BLL->fillMenuTypesActionLeftList(array(
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,        
        'filterRules' => $filterRules,
    ));
 
    $resTotalRowCount = $BLL->fillMenuTypesActionLeftRtc(array(        
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['module_id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "module_id" => $flow["module_id"] ,
                "module_name" => html_entity_decode($flow["module_name"]),             
                "action_id" => $flow["action_id"],
                "action_name" => html_entity_decode($flow["action_name"]),
                "menu_types_id" => $flow["menu_types_id"] ,
                "menu_type_name" => html_entity_decode($flow["menu_type_name"]),
              
                "active" => $flow["active"], 
                "attributes" => array(       
                   
                ));
        };
        $counts = $resTotalRowCount[0]['count'];
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});



$app->run();
