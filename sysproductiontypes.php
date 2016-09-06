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
$app->get("/pkFillGrid_sysProductionTypes/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();  
    $BLL = $app->getBLLManager()->get('sysProductionTypesBLL');
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public']; 
    
  $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }       
    $vPage = NULL;
    if (isset($_GET['page'])) {
         $stripper->offsetSet('page',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['page']));
    }       
    $vRows = NULL;
    if (isset($_GET['rows'])) {
         $stripper->offsetSet('rows',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rows']));
    }   
    $vSort = NULL;
    if (isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sort']));
    }
    $vOrder = NULL;
    if (isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ORDER,
                                                $app,
                                                $_GET['order']));
    }    
 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
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
     
    $resDataGrid = $BLL->fillGrid(array(              
            'page' => $vPage,
            'rows' => $vRows,
            'sort' => $vSort,
            'order' => $vOrder,          
            'language_code' => $vLanguageCode,      
            ));    
    $resTotalRowCount = $BLL->fillGridRowTotalCount(array(              
            'language_code' => $vLanguageCode,
            ));

    $flows = array();
    foreach ($resDataGrid as $flow) {         
        $flows[] = array(
            "id" => $flow["id"],
            "name" => $flow["name"],  
            "name_eng" => $flow["name_eng"],  
            "logo" => $flow["logo"],  
            
            "deleted" => $flow["deleted"],      
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],       
            "state_active" => $flow["state_active"], 
            "language_id" => $flow["language_id"],      
	    "language_name" => $flow["language_name"],
            "language_parent_id" => $flow["language_parent_id"],                
            "op_user_id" => $flow["op_user_id"],  
            "op_user_name" => $flow["op_user_name"],              
             
            "attributes" => array("notroot" => false, "active" => $flow["active"]),
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

  
/**x
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkDelete_sysProductionTypes/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysProductionTypesBLL'); 
   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];     
          
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

/**x
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkUpdate_sysProductionTypes/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysProductionTypesBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
   
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
    $vName = NULL;
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name']));
    }   
    $vNameEng = NULL;
    if (isset($_GET['name_eng'])) {
        $stripper->offsetSet('name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name_eng']));
    } 
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
        $stripper->offsetSet('logo', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['logo']));
    }  
    
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }  
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    }           
    if ($stripper->offsetExists('name_eng')) {
        $vNameEng = $stripper->offsetGet('name_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }    

    $resData = $BLL->update(array(  
            "id" => $vId , 
            "language_code" => $vLanguageCode, 
            "name" => $vName,  
            "name_eng" => $vNameEng,  
            "logo" => $vLogo,                         
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json");
 
    $app->response()->body(json_encode($resData));
}
); 

/**x
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkInsert_sysProductionTypes/", function () use ($app ) {
$stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysProductionTypesBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
   
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }
    $vName = NULL;
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name']));
    }   
    $vNameEng = NULL;
    if (isset($_GET['name_eng'])) {
        $stripper->offsetSet('name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name_eng']));
    } 
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
        $stripper->offsetSet('logo', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['logo']));
    }      
    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    }           
    if ($stripper->offsetExists('name_eng')) {
        $vNameEng = $stripper->offsetGet('name_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }    

    $resData = $BLL->insert(array(              
            "language_code" => $vLanguageCode, 
            "name" => $vName,  
            "name_eng" => $vNameEng,  
            "logo" => $vLogo,                         
            'pk' => $Pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 


/**
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkFillProductionTypesTree_sysProductionTypes/", function () use ($app ) {
   
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysProductionTypesBLL');
    $headerParams = $app->request()->headers();    
    $componentType = 'bootstrap'; // 'easyui'    
    if (isset($_GET['component_type'])) {
        $componentType = $_GET['component_type']; 
    }
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillProductionTypesTree_sysProductionTypes" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vParentId = NULL;
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
        $vParentId = $stripper->offsetGet('id')->getFilterValue();
    }
    
    $resDataGrid = $BLL->fillProductionTypesTree(array(
                                            'language_code' => $vLanguageCode,
                                            'pk' => $pk,
                                                    ));
                                                  
    $resTotalRowCount = $BLL->fillProductionTypesTreeRtc(array(
                                                        'language_code' => $vLanguageCode,
                                                        'pk' => $pk,                                                                                                               
                                                                )); 
    
    $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {      
        foreach ($resDataGrid['resultSet']  as $flow) {    
            $flows[] = array(
                "id" => intval( $flow["id"]) ,
                "text" =>  $flow["name"],
                "state" => $flow["state_type"],
                "checked" => false,
                "attributes" => array ("notroot"=>$flow["notroot"],
                                        "name_eng"=>$flow["name_eng"],
                                        "active" => intval($flow["active"]),                                        
                    ),                                 
            );
        }
        
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
 * @since 20-04-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysProductionTypes/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysProductionTypesBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];     
          
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


$app->run();
