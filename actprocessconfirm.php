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
* @since 21.07.2016
* rest servislere eklendi
 */
$app->get("/pkGetConsultantJobs_actProcessConfirm/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('actProcessConfirmBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkGetConsultantJobs_actProcessConfirm" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];    
    $vPage = NULL;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['page']));
    }
    $vRows = NULL;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['rows']));
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
    
    $resDataGrid = $BLL->getConsultantJobs(array(    
        'pk' => $pk,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,        
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->getConsultantJobsRtc(array(    
        'pk' => $pk,
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name_eng" => html_entity_decode($flow["operation_name_eng"]),
            "category_id" => $flow["category_id"],
            "category" => html_entity_decode($flow["category"]),
            "category_eng" => html_entity_decode($flow["category_eng"]),
            "table_name" => html_entity_decode($flow["table_name"]),   
            "table_column_id" => $flow["table_column_id"],   
            "membership_types_id" => $flow["membership_types_id"],   
            "membership_types_name" => html_entity_decode($flow["membership_types_name"]),   
            "membership_types_name_eng" => html_entity_decode($flow["membership_types_name_eng"]),   
            "sys_membership_periods_id" => $flow["sys_membership_periods_id"],   
            "period_name" => html_entity_decode($flow["period_name"]),   
            "period_name_eng" => html_entity_decode($flow["period_name_eng"]),   
            "preferred_language_id" => $flow["preferred_language_id"],   
            "preferred_language" => html_entity_decode($flow["preferred_language"]),   
            "language_id" => $flow["language_id"],   
            "language_name" => html_entity_decode($flow["language_name"]),   
            "cons_id" => $flow["cons_id"],   
            "cons_name" => html_entity_decode($flow["cons_name"]),  
            "op_cons_id" => $flow["op_cons_id"],   
            "op_cons_name" => html_entity_decode($flow["op_cons_name"]), 
            "cons_operation_type_id" => $flow["cons_operation_type_id"],   
            "cons_operation_name" => html_entity_decode($flow["cons_operation_name"]), 
            "cons_operation_name_eng" => html_entity_decode($flow["cons_operation_name_eng"]),   
            "s_date" => $flow["s_date"], 
            "c_date" => $flow["c_date"],   
            "priority" => $flow["priority"], 
            "state_active" => html_entity_decode($flow["state_active"]),  
            "op_user_id" => $flow["op_user_id"],  
            "op_user_name" => html_entity_decode($flow["op_user_name"]),
            "attributes" => array(              
                "active" => $flow["active"], ) );
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
