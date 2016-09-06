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
 * @since 29-02-2016
 */
$app->get("/pkFillNaceCodes_sysNaceCodes/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysNaceCodesBLL');
    
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillNaceCodes_sysNaceCodes" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vParentId = 0;
    if (isset($_GET['parent_id'])) {
        $stripper->offsetSet('parent_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent_id']));
    }
    
    
    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('parent_id')) $vMachineId = $stripper->offsetGet('parent_id')->getFilterValue();
 
    if (isset($_GET['parent_id'])) {
    $resDataGrid = $BLL->fillNaceCodes(array(
                                                        'language_code' => $vLanguageCode,
                                                        'pk' => $pk,
                                                        'parent_id' => $vParentId,
                                                                ));
    
    
    } else {
        $resDataGrid = $BLL->fillNaceCodes(array(
                                                        'language_code' => $vLanguageCode,
                                                        'pk' => $pk,                                                        
                                                                ));
  
    }
    
   
    $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {       
        foreach ($resDataGrid['resultSet'] as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "descriptions" => $flow["descriptions"],
                "description_engs" => $flow["description_engs"],
                "state_type" => $flow["state_type"],
               
                "attributes" => array("notroot" => true ),
            );
        }
        
    }
    $resultArray = array();
  //  $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */
    //if (isset($resDataGrid['resultSet']['machine_id'])) {
    //    $app->response()->body(json_encode($flows));
    //} else {
        $app->response()->body(json_encode($resultArray));
   // }
});

 
 
 


$app->run();
