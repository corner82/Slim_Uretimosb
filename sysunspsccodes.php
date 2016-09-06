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
 * @since 17-03-2016
 */
$app->get("/pkGetUnspscCodes_sysUnspscCodes/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnspscCodesBLL');

 
    $componentType = 'bootstrap';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vParentId = 0;
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
 
    if (isset($_GET['id']) && $_GET['id'] != "") {
        $resCombobox = $BLL->getUnspscCodes(array(
                            'parent_id' =>$vParentId,
                            'language_code' => $vLanguageCode,
                ));
    } else {
        $resCombobox = $BLL->getUnspscCodes(array(                             
                            'language_code' => $vLanguageCode,
                ));
    }

    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
        
    if (isset($_GET['id']) && $_GET['id'] != "") {
         if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => $menu["unspsc_names"],
                "state" => 'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"],"text_eng"=>$menu["unspsc_name_eng"]),
            );
            }
        } else if ($componentType == 'ddslick') {       
            foreach ($resCombobox as $menu) {
                $menus[] = array(
                    "text" => $menu["unspsc_names"],
                    "value" => intval($menu["id"]),
                    "selected" => false,
                    "description" => $menu["unspsc_name_eng"],
                  //  "imageSrc" => ""
                );
            }
        }
    }   else { 
    if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => $menu["unspsc_names"],
                "state" => 'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"],"text_eng"=>$menu["unspsc_name_eng"]),
            );
        }
    } else if ($componentType == 'ddslick') {       
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["unspsc_names"],
                "value" => intval($menu["id"]),
                "selected" => false,
                "description" => $menu["unspsc_name_eng"],
              //  "imageSrc" => ""
            );
        }
    }
    }

    $app->response()->header("Content-Type", "application/json");

    $app->response()->body(json_encode($menus));
});
 

 
/**
 *  * Okan CIRAN
 * @since 17-03-2016 
 */
$app->get("/pkFillUnspscCodesTree_sysUnspscCodes/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnspscCodesBLL');
    
    $headerParams = $app->request()->headers();
    
    $componentType = 'bootstrap'; // 'easyui'    
    if (isset($_GET['component_type'])) {
        $componentType = $_GET['component_type']; 
    }
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillMachineToolFullProperties_sysMachineToolProperties" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vParentId = 0;
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

   
    $resDataGrid = $BLL->fillUnspscCodesTree(array(
                                            'language_code' => $vLanguageCode,                                          
                                            'parent_id' => $vParentId,
                                                    ));
                                                    
                                                  
    $resTotalRowCount = $BLL->fillUnspscCodesTreeRtc(array(
                                                        'language_code' => $vLanguageCode,                                                     
                                                        'parent_id' => $vParentId,
                                                                ));
                                                              
    
        $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {      
        foreach ($resDataGrid['resultSet']  as $flow) {    
            $flows[] = array(
                "id" => $flow["id"],
                "text" =>  $flow["unspsc_names"],
                "state" => $flow["state_type"],
                "checked" => false,
                "attributes" => array ("notroot"=>true,"text_eng"=>$flow["unspsc_name_eng"]),               
                
            );
        }        
    }
   
      
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;

    
     // $app->response()->body(json_encode($flows));
    if($componentType == 'bootstrap'){
        $app->response()->body(json_encode($flows));
    }else //if($componentType == 'easyui')
        {
        $app->response()->body(json_encode($resultArray));
        }
      //  $app->response()->body(json_encode($resultArray));
        
 
});

 

/**
 *  * Okan CIRAN
 * @since 17-03-2016
 */
$app->get("/pkFillGrid_sysUnspscCodes/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysUnspscCodesBLL');
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
 
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
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
            "unspsc_codes" => $flow["unspsc_codes"],  
            "unspsc_names" => $flow["unspsc_names"],  
            "unspsc_name_eng" => $flow["unspsc_name_eng"],  
            "version_year" => $flow["version_year"],   
            
            "deleted" => $flow["deleted"],      
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],       
            "state_active" => $flow["state_active"],              
            "language_code" => $flow["language_code"],                             
            "language_id" => $flow["language_id"],      
	    "language_name" => $flow["language_name"],
            "language_parent_id" => $flow["language_parent_id"],                
            "op_user_id" => $flow["op_user_id"],  
            "op_user_name" => $flow["op_user_name"],              
             
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
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
