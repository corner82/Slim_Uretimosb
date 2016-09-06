<?php
// test commit for branch slim2
require 'vendor/autoload.php';


use \Services\Filter\Helper\FilterFactoryNames as stripChainers;

/*$app = new \Slim\Slim(array(
    'mode' => 'development',
    'debug' => true,
    'log.enabled' => true,
    ));*/

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
 * @author Okan CIRAN Ğ
 * @since 05.01.2016
 */
$res = $app->response();
$res->header('Access-Control-Allow-Origin', '*');
$res->header("Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS");

//$app->add(new \Slim\Middleware\MiddlewareTest());
$app->add(new \Slim\Middleware\MiddlewareInsertUpdateDeleteLog());
$app->add(new \Slim\Middleware\MiddlewareHMAC());
$app->add(new \Slim\Middleware\MiddlewareSecurity());
$app->add(new \Slim\Middleware\MiddlewareMQManager());
$app->add(new \Slim\Middleware\MiddlewareBLLManager());
$app->add(new \Slim\Middleware\MiddlewareDalManager());
$app->add(new \Slim\Middleware\MiddlewareServiceManager());




 


/**
 *  * OKAN CIRAN
 * @since 05-02-2016
 */
$app->get("/pkGetConsultantOperation_blActivationReport/", function () use ($app ) {

    
    $BLL = $app->getBLLManager()->get('blActivationReportBLL'); 
  
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
  
    $resDataMenu = $BLL->getConsultantOperation(array('pk'=>$vPk));
  
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body($resDataMenu);
  
});
 

/**
 *  * OKAN CIRAN
 * @since 05-02-2016
 */
$app->get("/pkGetConsultantFirmCount_blActivationReport/", function () use ($app ) {

    
    $BLL = $app->getBLLManager()->get('blActivationReportBLL'); 
  
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
  
    $resDataMenu = $BLL->getConsultantFirmCount(array('pk'=>$vPk));
  
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body($resDataMenu);
  
});
 

/**
 *  * OKAN CIRAN
 * @since 05-02-2016
 */
$app->get("/getAllFirmCount_blActivationReport/", function () use ($app ) {

    
    $BLL = $app->getBLLManager()->get('blActivationReportBLL'); 
  
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
  
    $resDataMenu = $BLL->getAllFirmCount(array('pk'=>$vPk));
  
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body($resDataMenu);
  
});
 


/**
 *  * OKAN CIRAN
 * @since 05-02-2016
 */
$app->get("/pkGetConsultantUpDashBoardCount_blActivationReport/", function () use ($app ) {

    
    $BLL = $app->getBLLManager()->get('blActivationReportBLL'); 
  
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
  
    $resDataMenu = $BLL->getConsultantUpDashBoardCount(array('pk'=>$vPk));
  
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body($resDataMenu);
  
});
 

/**
 *  * OKAN CIRAN
 * @since 05-02-2016
 */
$app->get("/pkGetConsWaitingForConfirm_blActivationReport/", function () use ($app ) {
 $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('blActivationReportBLL'); 
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetConsWaitingForConfirm_blActivationReport" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
    
    
    
    
    
    
    
    
    
    
    
  
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
  
    $resDataMenu = $BLL->getConsWaitingForConfirm(array('pk'=>$vPk));
  
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body($resDataMenu);
  
});
 
/**
 *  * Okan CIRAN
* @since 18.07.2016
 */
$app->get("/pkGetConsWaitingForConfirmYenisi_blActivationReport/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('blActivationReportBLL');
 
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetConsWaitingForConfirm_blActivationReport" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    } 
  
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue(); 
    $resDataGrid = $BLL->getConsWaitingForConfirm(array(
        'language_code' => $vLanguageCode,        
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->getConsWaitingForConfirmRtc(array(  
         'language_code' => $vLanguageCode,
         'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),   
                "operation_type_id" => intval($flow["operation_type_id"]), 
                "aciklama" => html_entity_decode($flow["aciklama"]),
                "operation_name" => html_entity_decode($flow["operation_name"]),
                "operation_name_eng" => html_entity_decode($flow["operation_name_eng"]),
                 "category_id" => intval($flow["category_id"]),     
                "category" => html_entity_decode($flow["category"]),
                "category_eng" => html_entity_decode($flow["category_eng"]),
                "table_name" => html_entity_decode($flow["table_name"]),
                "table_column_id" => intval($flow["table_column_id"]), 
                "membership_types_id" => intval($flow["membership_types_id"]), 
                "membership_types_name" => html_entity_decode($flow["membership_types_name"]),
                "membership_types_name_eng" => html_entity_decode($flow["membership_types_name_eng"]),
                "sys_membership_periods_id" => intval($flow["sys_membership_periods_id"]), 
                "period_name" => html_entity_decode($flow["period_name"]),                
                "period_name_eng" => html_entity_decode($flow["period_name_eng"]),               
                "preferred_language_id" => intval($flow["preferred_language_id"]), 
                "preferred_language" => html_entity_decode($flow["preferred_language"]),
                "op_user_id" => intval($flow["op_user_id"]), 
                "op_user_name" => html_entity_decode($flow["op_user_name"]),
                "cons_id" => intval($flow["cons_id"]), 
                "cons_name" => html_entity_decode($flow["cons_name"]),
                "op_cons_id" => intval($flow["op_cons_id"]), 
                "op_cons_name" => html_entity_decode($flow["op_cons_name"]),
                "cons_operation_type_id" => intval($flow["cons_operation_type_id"]), 
                "cons_operation_name" => html_entity_decode($flow["cons_operation_name"]),   
                "cons_operation_name_eng" => html_entity_decode($flow["cons_operation_name_eng"]),
                "sure" => intval($flow["sure"]), 
                "s_date" =>  $flow["s_date"],
                "c_date" =>  $flow["c_date"],
                "priority" => intval($flow["priority"]), 
                
                "language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
                "attributes" => array("notroot" => true,),
            );
        }
       $counts = $resTotalRowCount[0]['count'];
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});







$app->run();