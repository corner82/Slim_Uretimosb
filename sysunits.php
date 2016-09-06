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
$app->get("/pkGetUnits_sysUnits/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysUnitsBLL');

    $vLanguageCode  = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
    }
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    
    
    if (isset($_GET['main']) && $_GET['main'] != "") {
        $resCombobox = $BLL->getUnits(array(
                            'main' => $_GET ["main"],
                            'language_code' => $vLanguageCode,
                ));
    } else {
        $resCombobox = $BLL->getUnits(array(                             
                            'language_code' => $vLanguageCode,
                ));
    }

        $menus = array();
        $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
        
    if (isset($_GET['main']) && $_GET['main'] != "") {
         if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => $menu["unitcode"],
                "state" => 'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"]),
            );
        }
    } else if ($componentType == 'ddslick') {       
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["unitcode"],
                "value" => intval($menu["id"]),
                "selected" => false,
                "description" => $menu["units"],
               // "imageSrc" => ""
            );
        }
    }
    }   else { 
    if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => $menu["system"],
                "state" => 'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"]),
            );
        }
    } else if ($componentType == 'ddslick') {       
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["system"],
                "value" => intval($menu["id"]),
                "selected" => false,
                "description" => $menu["system_eng"],
             //   "imageSrc" => ""
            );
        }
    }
    }

    $app->response()->header("Content-Type", "application/json");

    $app->response()->body(json_encode($menus));
});
 

 
/**
 *  * Okan CIRAN
 * @since 26-02-2016
 */
$app->get("/pkFillUnitsTree_sysUnits/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnitsBLL');
    
    $headerParams = $app->request()->headers();
    
    $componentType = 'bootstrap'; // 'easyui'    
    if (isset($_GET['component_type'])) {
        $componentType = $_GET['component_type']; 
    }
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillUnitsTree_sysUnits" end point, X-Public variable not found');
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
    $vSystemId = NULL;
    if (isset($_GET['system_id'])) {
        $stripper->offsetSet('system_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['system_id']));
    }    
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('id')) {
        $vParentId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('system_id')) {
        $vSystemId = $stripper->offsetGet('system_id')->getFilterValue();
    }
   
    $resDataGrid = $BLL->fillUnitsTree(array(
                                            'language_code' => $vLanguageCode,
                                            'pk' => $pk,
                                            'id' => $vParentId,
                                            'system_id' => $vSystemId,
                                                    ));                                                    
                                                  
    $resTotalRowCount = $BLL->fillUnitsTreeRtc(array(
                                                        'language_code' => $vLanguageCode,
                                                        'pk' => $pk,
                                                        'id' => $vParentId,
                                                        'system_id' => $vSystemId,
                                                                ));                                                              
    
    $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {      
        foreach ($resDataGrid['resultSet']  as $flow) {    
            $flows[] = array(
                "id" => intval( $flow["id"]) ,
                "text" =>  $flow["unitcode"],
                "state" => $flow["state_type"],
                "checked" => false,
                "attributes" => array ("notroot"=>$flow["notroot"],
                                        "unitcode_eng"=>$flow["unitcode_eng"],
                                        "active" => intval($flow["active"]),
                                        "system_id" => intval($flow["system_id"]),
                                        "system" => $flow["system"],
                                        "system_eng" => $flow["system_eng"],
                                        "unit" => $flow["unit"],
                                        "unit_eng" => $flow["unit_eng"],                                        
                                        "abbreviation" => $flow["abbreviation"],
                                        "abbreviation_eng" => $flow["abbreviation_eng"],
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




/**
 *  * Okan CIRAN
 * @since 15-02-2016
 */
$app->get("/pkInsert_sysUnits/", function () use ($app ) {  
   $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnitsBLL');    
    $headerParams = $app->request()->headers();        
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkInsert_sysUnits" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vParentId = NULL;
    if (isset($_GET['parent_id'])) {
        $stripper->offsetSet('parent_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent_id']));
    }    
    $vSystemId = NULL;
    if (isset($_GET['system_id'])) {
        $stripper->offsetSet('system_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['system_id']));
    }   
    $vUnit = NULL;
    if (isset($_GET['unit'])) {
         $stripper->offsetSet('unit',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unit']));
    }   
    $vUnitCodeEng = NULL;
    if (isset($_GET['unitcode_eng'])) {
         $stripper->offsetSet('unitcode_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unitcode_eng']));
    }   
     $vUnitCode = NULL;
    if (isset($_GET['unitcode'])) {
         $stripper->offsetSet('unitcode',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unitcode']));
    }   
    $vUnitEng = NULL;
    if (isset($_GET['unit_eng'])) {
         $stripper->offsetSet('unit_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unit_eng']));
    }   
     $vAbbreviation = NULL;
    if (isset($_GET['abbreviation'])) {
         $stripper->offsetSet('abbreviation',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['abbreviation']));
    }   
    $vAbbreviationEng = NULL;
    if (isset($_GET['abbreviation_eng'])) {
         $stripper->offsetSet('abbreviation_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['abbreviation_eng']));
    }  
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('parent_id')) {
        $vParentId = $stripper->offsetGet('parent_id')->getFilterValue();
    }
    if ($stripper->offsetExists('system_id')) {
        $vSystemId = $stripper->offsetGet('system_id')->getFilterValue();
    }
    if ($stripper->offsetExists('unit')) {
        $vUnit = $stripper->offsetGet('unit')->getFilterValue();
    }
    if ($stripper->offsetExists('unit_eng')) {
        $vUnitEng = $stripper->offsetGet('unit_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('unitcode')) {
        $vUnitCode = $stripper->offsetGet('unitcode')->getFilterValue();
    }
    if ($stripper->offsetExists('unitcode_eng')) {
        $vUnitCodeEng = $stripper->offsetGet('unitcode_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('abbreviation')) {
        $vAbbreviation = $stripper->offsetGet('abbreviation')->getFilterValue();
    }
    if ($stripper->offsetExists('abbreviation_eng')) {
        $vAbbreviationEng = $stripper->offsetGet('abbreviation_eng')->getFilterValue();
    }
     
 
    
    $resDataInsert = $BLL->insert(array(   
            'language_code' => $vLanguageCode,
            'system_id'=> $vSystemId,  
            'parent_id'=> $vParentId,         
            'unit'=> $vUnit,
            'unit_eng'=> $vUnitEng,
            'unitcode'=> $vUnitCode,
            'unitcode_eng'=> $vUnitCodeEng,
            'abbreviation'=> $vAbbreviation,
            'abbreviation_eng'=> $vAbbreviationEng,         
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 15-02-2016
 */
$app->get("/pkUpdate_sysUnits/", function () use ($app ) {

   $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnitsBLL');    
    $headerParams = $app->request()->headers();        
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkInsert_sysUnits" end point, X-Public variable not found');
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
    $vSystemId = NULL;
    if (isset($_GET['system_id'])) {
        $stripper->offsetSet('system_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['system_id']));
    }   
    $vUnit = NULL;
    if (isset($_GET['unit'])) {
         $stripper->offsetSet('unit',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unit']));
    }
    $vUnitEng = NULL;
    if (isset($_GET['unit_eng'])) {
         $stripper->offsetSet('unit_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unit_eng']));
    }       
    $vUnitCode = NULL;
    if (isset($_GET['unitcode'])) {
         $stripper->offsetSet('unitcode',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unitcode']));
    }   
    $vUnitCodeEng = NULL;
    if (isset($_GET['unitcode_eng'])) {
         $stripper->offsetSet('unitcode_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['unitcode_eng']));
    }   
     $vAbbreviation = NULL;
    if (isset($_GET['abbreviation'])) {
         $stripper->offsetSet('abbreviation',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['abbreviation']));
    }   
    $vAbbreviationEng = NULL;
    if (isset($_GET['abbreviation_eng'])) {
         $stripper->offsetSet('abbreviation_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['abbreviation_eng']));
    }  
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('system_id')) {
        $vSystemId = $stripper->offsetGet('system_id')->getFilterValue();
    }
    if ($stripper->offsetExists('unit')) {
        $vUnit = $stripper->offsetGet('unit')->getFilterValue();
    }
    if ($stripper->offsetExists('unit_eng')) {
        $vUnitEng = $stripper->offsetGet('unit_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('unitcode')) {
        $vUnitCode = $stripper->offsetGet('unitcode')->getFilterValue();
    }
    if ($stripper->offsetExists('unitcode_eng')) {
        $vUnitCodeEng = $stripper->offsetGet('unitcode_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('abbreviation')) {
        $vAbbreviation = $stripper->offsetGet('abbreviation')->getFilterValue();
    }
    if ($stripper->offsetExists('abbreviation_eng')) {
        $vAbbreviationEng = $stripper->offsetGet('abbreviation_eng')->getFilterValue();
    }
     
 
    
    $resDataInsert = $BLL->update(array(   
            'language_code' => $vLanguageCode,
            'id'=> $vId,        
            'system_id'=> $vSystemId,    
            'unit'=> $vUnit,
            'unit_eng'=> $vUnitEng,
            'unitcode'=> $vUnitCode,
            'unitcode_eng'=> $vUnitCodeEng,
            'abbreviation'=> $vAbbreviation,
            'abbreviation_eng'=> $vAbbreviationEng,         
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataInsert));
}
); 


/**
 *  * Okan CIRAN
 * @since 15-02-2016
 */
$app->get("/pkFillGrid_sysUnits/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysUnitsBLL');
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
            
            "main" => $flow["main"],
            "sub" => $flow["sub"],
            "systems" => $flow["system"],
            "system_eng" => $flow["system_eng"],
            "abbreviations" => $flow["abbreviation"],
            "abbreviation_eng" => $flow["abbreviation_eng"],
            "unitcodes" => $flow["unitcode"],          
            "unitcode_eng" => $flow["unitcode_eng"],
            "units" => $flow["unit"],          
            "unit_eng" => $flow["unit_eng"], 
            
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

  
/**x
 *  * Okan CIRAN
 * @since 06-03-2016
 */
$app->get("/pkDelete_sysUnits/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnitsBLL'); 
    
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
 * @since 29-03-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysUnits/", function () use ($app ) {
 
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysUnitsBLL');
 
   
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
