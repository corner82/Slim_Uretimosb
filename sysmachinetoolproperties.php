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
 * @since 17.02.2016
 */
$app->get("/pkInsert_sysMachineToolProperties/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');    
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysMachineToolProperties" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
        
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
    $vMachineToolPropertyDefinitionId = NULL;
    if (isset($_GET['property_id'])) {
         $stripper->offsetSet('property_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['property_id']));
    }
    $vUnitId = NULL;
    if (isset($_GET['unit_id'])) {
         $stripper->offsetSet('unit_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['unit_id']));
    }
    $vPropertyValue = NULL;
    if (isset($_GET['property_value'])) {
         $stripper->offsetSet('property_value',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['property_value']));
    } 
    
    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('machine_tool_id')) $vMachineToolId = $stripper->offsetGet('machine_tool_id')->getFilterValue();
    if($stripper->offsetExists('property_id')) $vMachineToolPropertyDefinitionId = $stripper->offsetGet('property_id')->getFilterValue();
    if($stripper->offsetExists('property_value')) $vPropertyValue = $stripper->offsetGet('property_value')->getFilterValue();
    if($stripper->offsetExists('unit_id')) $vUnitId = $stripper->offsetGet('unit_id')->getFilterValue();
     
    $resDataInsert = $BLL->insert(array(
            'language_code' => $vLanguageCode,
            'machine_tool_id' => $vMachineToolId,
            'machine_tool_property_definition_id' => $vMachineToolPropertyDefinitionId,
            'property_value' => $vPropertyValue,
            'unit_id' => $vUnitId,                
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
); 
 
/**
 *  * Okan CIRAN
 * @since 17.02.2016
 */
$app->get("/pkUpdate_sysMachineToolProperties/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');    
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysMachineToolProperties" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
        
  $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    } 
   /* $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    * 
    */  
    $vMachineToolId = NULL;
    if (isset($_GET['machine_tool_id'])) {
         $stripper->offsetSet('machine_tool_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['machine_tool_id']));
    } 
    $vMachineToolPropertyDefinitionId = NULL;
    if (isset($_GET['property_id'])) {
         $stripper->offsetSet('property_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['property_id']));
    }
    $vUnitId = NULL;
    if (isset($_GET['unit_id'])) {
         $stripper->offsetSet('unit_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['unit_id']));
    }
    $vPropertyValue = NULL;
    if (isset($_GET['property_value'])) {
         $stripper->offsetSet('property_value',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['property_value']));
    } 
    
    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    //if($stripper->offsetExists('id')) $vId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('machine_tool_id')) $vMachineToolId = $stripper->offsetGet('machine_tool_id')->getFilterValue();
    if($stripper->offsetExists('property_id')) $vMachineToolPropertyDefinitionId = $stripper->offsetGet('property_id')->getFilterValue();
    if($stripper->offsetExists('property_value')) $vPropertyValue = $stripper->offsetGet('property_value')->getFilterValue();
    if($stripper->offsetExists('unit_id')) $vUnitId = $stripper->offsetGet('unit_id')->getFilterValue();
     
    $resDataInsert = $BLL->update(array(
          //  'id' => $vId,  
            'language_code' => $vLanguageCode,
            'machine_tool_id' => $vMachineToolId,
            'machine_tool_property_definition_id' => $vMachineToolPropertyDefinitionId,
            'property_value' => $vPropertyValue,
            'unit_id' => $vUnitId,                
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 17-02-2016
 */
$app->get("/pkFillGrid_sysMachineToolProperties/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');
   // $headerParams = $app->request()->headers();
   // $vPk = $headerParams['X-Public'];   
      $vLanguageCode  = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
    }
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $fLanguageCode=$vLanguageCode;
    
    $resDataGrid = $BLL->fillGrid(array(              
            'language_code' => $fLanguageCode,
            ));
    
    $resTotalRowCount = $BLL->fillGridRowTotalCount(array(              
            'language_code' => $fLanguageCode,
            ));

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
             "id" => $flow["id"],
            "machine_tool_id" => $flow["machine_tool_id"],
            "machine_tool_names" => $flow["machine_tool_names"],
            "machine_tool_name_eng" => $flow["machine_tool_name_eng"],
            "machine_tool_property_definition_id" => $flow["machine_tool_property_definition_id"],
            "property_names" => $flow["property_names"],
            "property_name_eng" => $flow["property_name_eng"],
            "property_value" => $flow["property_value"],          
            "unit_id" => $flow["unit_id"],
            "unitcodes" => $flow["unitcodes"],
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

 

/**
 *  * Okan CIRAN
 * @since 26-02-2016
 */
$app->get("/pkFillMachineToolFullProperties_sysMachineToolProperties/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');
    
    $headerParams = $app->request()->headers();
    
    $componentType = 'bootstrap'; // 'easyui'
    
    
    
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

   
    $resDataGrid = $BLL->fillMachineToolFullProperties(array(
                                                        'language_code' => $vLanguageCode,
                                                        'pk' => $pk,
                                                        'id' => $vParentId,
                                                                ));
                                                               
    $resTotalRowCount = $BLL->fillMachineToolFullPropertiesRtc(array(
                                                        'language_code' => $vLanguageCode,
                                                        'pk' => $pk,
                                                        'id' => $vParentId,
                                                                ));
  
        $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {      
        foreach ($resDataGrid['resultSet']  as $flow) {    
            $flows[] = array(
                "id" => $flow["id"],
                "text" =>  $flow["property_names"],
                "state" => $flow["state_type"],
                "checked" => false,
                "attributes" => array ("notroot"=>true),               
                
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
    }else if($componentType == 'easyui'){
        $app->response()->body(json_encode($resultArray));
    }
      //  $app->response()->body(json_encode($resultArray));
        
 
});

/**
 *  * Okan CIRAN
 * @since 21-06-2016
 */
$app->get("/pkFillPropertyUnits_sysMachineToolProperties/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');    
    $headerParams = $app->request()->headers();    
    
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }   
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillPropertyUnits_sysMachineToolProperties" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vPropertyId = 0;
    if (isset($_GET['property_id'])) {
        $stripper->offsetSet('property_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['property_id']));
    }    
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('property_id')) {
        $vPropertyId = $stripper->offsetGet('property_id')->getFilterValue();
    }
    
    $resData = $BLL->fillPropertyUnits(array(
                                                'language_code' => $vLanguageCode,
                                                'pk' => $pk,
                                                'property_id' => $vPropertyId,
                                                        ));   
    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true,   "description" => "Lütfen Seçiniz",); 
  
    foreach ($resData as $menu) {
            $menus[] = array(
                "text" => $menu["unitcode"],
                "value" =>  intval($menu["id"]),
                "selected" => false,
                "description" => $menu["unitcode_eng"],
             //   "imageSrc" => ""
            );
        }
  
    
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
   // $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $menus;
    
    
        $app->response()->body(json_encode($menus));
 
      //  $app->response()->body(json_encode($resultArray));
        
 
});
 

/**
 *  * Okan CIRAN
 * @since 02-05-2016
 */
$app->get("/pkDeletePropertyMachine_sysMachineToolProperties/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');   
    $headerParams = $app->request()->headers();     
     if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkDeletePropertyMachine_sysMachineToolProperties" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
   
    $vMachineId = -1;
    if (isset($_GET['machine_id'])) {
         $stripper->offsetSet('machine_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['machine_id']));
    } 
     $vPropertyId = NULL;
    if (isset($_GET['property_id'])) {
         $stripper->offsetSet('property_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['property_id']));
    }      
    $stripper->strip(); 
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if ($stripper->offsetExists('property_id')) {
        $vPropertyId = $stripper->offsetGet('property_id')->getFilterValue();
    }
    
    
    $resData = $BLL->deletePropertyMachine(array(      
            'machine_id' => $vMachineId , 
            'property_id' => $vPropertyId ,
            'pk' => $pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 
/**
 *  * Okan CIRAN
 * @since 19-08-2016
 
 */
$app->get("/pkFillMachinePropertiesSubGridList_sysMachineToolProperties/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysMachineToolPropertiesBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillMachinePropertiesSubGridList_sysMachineToolProperties" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];


    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }
    $vMachineToolId = NULL;
    if (isset($_GET['machine_tool_id'])) {
        $stripper->offsetSet('machine_tool_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['machine_tool_id']));
    } 

    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_tool_id')) {
        $vMachineToolId = $stripper->offsetGet('machine_tool_id')->getFilterValue();
    }    
    $resDataGrid = $BLL->fillMachinePropertiesSubGridList(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'machine_tool_id' => $vMachineToolId,        
    ));

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
         //   "machine_tool_id" => $flow["machine_tool_id"],
        //    "machine_tool_name" => $flow["machine_tool_name"],
        //    "machine_tool_name_eng" => $flow["machine_tool_name_eng"],
        //    "machine_tool_property_definition_id" => $flow["machine_tool_property_definition_id"],
            "property_name" => html_entity_decode($flow["property_name"]),
            "property_name_eng" => html_entity_decode($flow["property_name_eng"]),
            "property_value" => $flow["property_value"],
       //     "unit_id" => $flow["unit_id"],
            "unitcode" => html_entity_decode($flow["unitcode"]),
            "unitcode_eng" => html_entity_decode($flow["unitcode_eng"]), 
            "attributes" => array(            
                     //   "active" => $flow["active"],                     
                ),
        );
    }                      
                
    $app->response()->header("Content-Type", "application/json");   
    $app->response()->body(json_encode($flows));
 
});


$app->run();
