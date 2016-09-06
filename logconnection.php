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
 * @since 10-03-2016
 */
$app->get("/pkFillGrid_logConnection/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('logConnectionBLL');

    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];

    $resDataGrid = $BLL->fillGrid(array('page' => $_GET['page'],
        'rows' => $_GET['rows'],
        'sort' => $_GET['sort'],
        'order' => $_GET['order'], 
         ));

    $resTotalRowCount = $BLL->fillGridRowTotalCount( );

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],            
            "s_date" => $flow["s_date"],
            "pk" => $flow["pk"],
            "type_id" => $flow["type_id"],
            "operation_name" => $flow["operation_name"],
            "user_id" => $flow["user_id"],
            "username" => $flow["username"],
            "log_datetime" => $flow["log_datetime"],
            "url" => $flow["url"],
            "path" => $flow["path"],
            "ip" => $flow["ip"],
            "params" => $flow["params"],
            "method" =>  $flow["method"],
            "request_info" =>  $flow["request_info"],            
            "attributes" => array("notroot" => true,  
                ),
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
 * @since 10-03-2016
 */
$app->get("/pkInsert_logConnection/", function () use ($app ) {
    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('logConnectionBLL');
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];
    
    $vTypeId = 0;
    if (isset($_GET['type_id'])) {
        $stripper->offsetSet('type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['type_id']));
    }  
    $vLogDatetime = '2016-02-22 04:00:00';
    if (isset($_GET['log_datetime'])) {
        $stripper->offsetSet('log_datetime', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['log_datetime']));
    } 
    $vUrl = NULL;
     if (isset($_GET['url'])) {
        $stripper->offsetSet('url', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['url']));
    } 
    
    $vPath = NULL;
     if (isset($_GET['path'])) {
        $stripper->offsetSet('path', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['path']));
    } 
    
    $vIp = NULL;
    if (isset($_GET['ip'])) {
        $stripper->offsetSet('ip', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['ip']));
    } 
    $vParams = NULL;
    if (isset($_GET['params'])) {
        $stripper->offsetSet('params', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['params']));
    } 
    
    $vMethod = NULL;
    if (isset($_GET['method'])) {
        $stripper->offsetSet('method', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['method']));
    } 
    $vRequestInfo = NULL;
    if (isset($_GET['request_info'])) {
        $vRequestInfo = $_GET['request_info'] ;
    } 
    
    
    $stripper->strip();
    if($stripper->offsetExists('type_id')) $vTypeId = $stripper->offsetGet('type_id')->getFilterValue();
    if($stripper->offsetExists('log_datetime')) $vLogDatetime = $stripper->offsetGet('log_datetime')->getFilterValue();
    if($stripper->offsetExists('url')) $vUrl = $stripper->offsetGet('url')->getFilterValue();
    if($stripper->offsetExists('path')) $vPath = $stripper->offsetGet('path')->getFilterValue();
    if($stripper->offsetExists('ip')) $vIp = $stripper->offsetGet('ip')->getFilterValue();
    if($stripper->offsetExists('params')) $vParams = $stripper->offsetGet('params')->getFilterValue();
    if($stripper->offsetExists('method')) $vMethod = $stripper->offsetGet('method')->getFilterValue();
    
    
    
    $resDataInsert = $BLL->insert(array(        
        'type_id' => $vTypeId,
        'log_datetime' => $vLogDatetime,
        'url' => $vUrl,
        'path' => $vPath,
        'ip' => $vIp,
        'params' => $vParams,
        'method' =>$vMethod,
        'request_info' => $vRequestInfo,
        'pk' => $Pk));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
);
 
  
/**
 *  * Okan CIRAN
 * @since 10-03-2016
 */
$app->get("/pkGetAll_logConnection/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('logConnectionBLL');
    $resDataGrid = $BLL->getAll();
    $resTotalRowCount = $BLL->fillGridRowTotalCount( );

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],            
            "s_date" => $flow["s_date"],
            "pk" => $flow["pk"],
            "type_id" => $flow["type_id"],
            "operation_name" => $flow["operation_name"],
            "user_id" => $flow["user_id"],
            "username" => $flow["username"],
            "log_datetime" => $flow["log_datetime"],
            "url" => $flow["url"],
            "path" => $flow["path"],
            "ip" => $flow["ip"],
            "params" => $flow["params"],
            "method" => $flow["method"],   
            "request_info" =>  $flow["request_info"],
            "attributes" => array("notroot" => true,  ),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resultArray));
});

$app->run();
