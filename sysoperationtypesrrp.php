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
 * @since 08.08.2016
 *  rest servislere eklendi
 */
$app->get("/pkInsert_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysOperationTypesRrp" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vRrpRestServiceId = NULL;
    if (isset($_GET['rrp_restservice_id'])) {
         $stripper->offsetSet('rrp_restservice_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rrp_restservice_id']));
    }
    $vTableOid = NULL;
    if (isset($_GET['table_oid'])) {
         $stripper->offsetSet('table_oid',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['table_oid']));
    }
    $vAssignDefinitionId = NULL;
    if (isset($_GET['assign_definition_id'])) {
         $stripper->offsetSet('assign_definition_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['assign_definition_id']));
    } 
    $vOperationName = NULL;
    if (isset($_GET['operation_name'])) {
         $stripper->offsetSet('operation_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['operation_name']));
    }
    $vOperationNameEng = NULL;
    if (isset($_GET['operation_name_eng'])) {
         $stripper->offsetSet('operation_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['operation_name_eng']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    }
    
    $stripper->strip();
    if($stripper->offsetExists('rrp_restservice_id')) 
        $vRrpRestServiceId = $stripper->offsetGet('rrp_restservice_id')->getFilterValue();
    if($stripper->offsetExists('table_oid')) 
        $vTableOid = $stripper->offsetGet('table_oid')->getFilterValue();
    if($stripper->offsetExists('assign_definition_id')) 
        $vAssignDefinitionId = $stripper->offsetGet('assign_definition_id')->getFilterValue();
   
    
    if($stripper->offsetExists('operation_name')) 
        $vOperationName = $stripper->offsetGet('operation_name')->getFilterValue();    
    if($stripper->offsetExists('operation_name_eng')) 
       $vOperationNameEng = $stripper->offsetGet('operation_name_eng')->getFilterValue();    
    
    if($stripper->offsetExists('description')) 
      $vDescription = $stripper->offsetGet('description')->getFilterValue();    
    if($stripper->offsetExists('description_eng')) 
     $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();    
      
    $resDataInsert = $BLL->insert(array(
            'rrp_restservice_id' => $vRrpRestServiceId,  
            'table_oid' => $vTableOid,  
            'assign_definition_id' => $vAssignDefinitionId,  
            'operation_name' => $vOperationName,
            'operation_name_eng' => $vOperationNameEng,
            'description' => $vDescription,
            'description_eng' => $vDescriptionEng,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);
 
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */
$app->get("/pkUpdate_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysOperationTypesRrp" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $vRrpRestServiceId = NULL;
    if (isset($_GET['rrp_restservice_id'])) {
         $stripper->offsetSet('rrp_restservice_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rrp_restservice_id']));
    }
    $vTableOid = NULL;
    if (isset($_GET['table_oid'])) {
         $stripper->offsetSet('table_oid',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['table_oid']));
    }
    $vAssignDefinitionId = NULL;
    if (isset($_GET['assign_definition_id'])) {
         $stripper->offsetSet('assign_definition_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['assign_definition_id']));
    } 
    $vOperationName = NULL;
    if (isset($_GET['operation_name'])) {
         $stripper->offsetSet('operation_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['operation_name']));
    }
    $vOperationNameEng = NULL;
    if (isset($_GET['operation_name_eng'])) {
         $stripper->offsetSet('operation_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['operation_name_eng']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    }
    
    $stripper->strip();
    if($stripper->offsetExists('id')) 
        $vId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('rrp_restservice_id')) 
        $vRrpRestServiceId = $stripper->offsetGet('rrp_restservice_id')->getFilterValue();
    if($stripper->offsetExists('table_oid')) 
        $vTableOid = $stripper->offsetGet('table_oid')->getFilterValue();
    if($stripper->offsetExists('assign_definition_id')) 
        $vAssignDefinitionId = $stripper->offsetGet('assign_definition_id')->getFilterValue();
   
    
    if($stripper->offsetExists('operation_name')) 
        $vOperationName = $stripper->offsetGet('operation_name')->getFilterValue();    
    if($stripper->offsetExists('operation_name_eng')) 
       $vOperationNameEng = $stripper->offsetGet('operation_name_eng')->getFilterValue();    
    
    if($stripper->offsetExists('description')) 
      $vDescription = $stripper->offsetGet('description')->getFilterValue();    
    if($stripper->offsetExists('description_eng')) 
     $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();    
      
    $resDataInsert = $BLL->update(array(
            'id' => $vId,  
            'rrp_restservice_id' => $vRrpRestServiceId,  
            'table_oid' => $vTableOid,  
            'assign_definition_id' => $vAssignDefinitionId,  
            'operation_name' => $vOperationName,
            'operation_name_eng' => $vOperationNameEng,
            'description' => $vDescription,
            'description_eng' => $vDescriptionEng,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));    
}
);
  
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */ 
$app->get("/pkDelete_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');   
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
 
  
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */
$app->get("/pkFillOperationTypesRrpList_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillOperationTypesRrpList_sysOperationTypesRrp" end point, X-Public variable not found');
    } 
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
    
    $resDataGrid = $BLL->fillOperationTypesRrpList(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillOperationTypesRrpListRtc(array(
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "rrp_restservice_id" => $flow["rrp_restservice_id"],
                
            "operation_name" => html_entity_decode($flow["operation_name"]), 
            "operation_name_eng" => html_entity_decode($flow["operation_name_eng"]), 
                
            "role_id" => $flow["role_id"],
            "role_name" => html_entity_decode($flow["role_name"]),  
            "role_name_tr" => html_entity_decode($flow["role_name_tr"]),  
                
            "resource_id" => $flow["resource_id"],
            "resource_name" => html_entity_decode($flow["resource_name"]),  
                
            "privilege_id" => $flow["privilege_id"],
            "privilege_name" => html_entity_decode($flow["privilege_name"]),  
                
            "services_group_id" => $flow["services_group_id"],
            "service_group_name" => html_entity_decode($flow["service_group_name"]),  
                
                
            "restservices_id" => $flow["restservices_id"],
            "restservice_name" => html_entity_decode($flow["restservice_name"]),      
              
            "table_oid" => $flow["table_oid"],
            "table_name" => html_entity_decode($flow["table_name"]),          
       
            "assign_definition_id" => $flow["assign_definition_id"],
            "assignment_name" => html_entity_decode($flow["assignment_name"]),          
   
            "description" => html_entity_decode($flow["description"]),  
            "description_eng" => html_entity_decode($flow["description_eng"]),  
            "state_active" => html_entity_decode($flow["state_active"]),  
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
  
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */
$app->get("/pkFillConsultantRolesTree_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');    
    $vsearch = null;
    if(isset($_GET['search'])) {
        $stripper->offsetSet('search', 
                $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                        $app,
                        $_GET['search']));
    }
     
    $stripper->strip();
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue(); 
   
    $resCombobox = $BLL->fillConsultantRolesTree( );
    
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => html_entity_decode($flow["name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
           // "icon_class"=>$flow["icon_class"], 
            "attributes" => array("root" => $flow["root_type"], "active" => $flow["active"]
                ,"roles" => html_entity_decode($flow["roles"]),"last_node" => $flow["last_node"]),
        );
    }

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */ 
$app->get("/pkFillAssignDefinitionOfRoles_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');    
    $vRoleId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $vsearch = null;
    if(isset($_GET['search'])) {
        $stripper->offsetSet('search', 
                $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                        $app,
                        $_GET['search']));
    }
    
    
    $stripper->strip();       
    if($stripper->offsetExists('id')) $vRoleId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();
 
    $resCombobox = $BLL->fillAssignDefinitionOfRoles(array('role_id' => $vRoleId,                                                        
                                                         'search' => $vsearch,
                                                                ));
  
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => html_entity_decode($flow["assign_definition_name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
           // "icon_class"=>$flow["icon_class"], 
            "attributes" => array("root" => $flow["root_type"], "active" => $flow["active"]
                 ,"last_node" => $flow["last_node"]
                ,"role_id" => $flow["role_id"] ,"assign_definition_id" => $flow["assign_definition_id"]                    
        ), 
        );
    } 

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */
$app->get("/pkFillNotInAssignDefinitionOfRoles_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillNotInAssignDefinitionOfRoles_sysOperationTypesRrp" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
    
    $vRoleId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    
    $stripper->strip();    
    if($stripper->offsetExists('id')) $vRoleId = $stripper->offsetGet('id')->getFilterValue();
    
    
    $resCombobox = $BLL->fillNotInAssignDefinitionOfRoles(array(
                                    'role_id' => $vRoleId, 
                        ));    

    $flows = array();
    foreach ($resCombobox as $flow) {
       $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => html_entity_decode($flow["assign_definition_name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
           // "icon_class"=>$flow["icon_class"], 
            "attributes" => array("root" => $flow["root_type"], "active" => $flow["active"]
                 ,"last_node" => $flow["last_node"]
                ,"role_id" => $flow["role_id"] ,"assign_definition_id" => $flow["assign_definition_id"]                    
        ), 
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
  
/**
 *  * Okan CIRAN
 * @since 08.08.2016
 */
$app->get("/pkFillAssignDefinitionRolesDdList_sysOperationTypesRrp/", function () use ($app ) {
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillAssignDefinitionRolesDdList_sysOperationTypesRrp" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
     
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }

    $resCombobox = $BLL->fillAssignDefinitionRolesDdList();

        $menus = array();
        $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
    if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => html_entity_decode($menu["name"]),
                "state" => $menu["state_type"], //   'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"]),
            );
        }
    } else if ($componentType == 'ddslick') {       
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => html_entity_decode($menu["name"]),
                "value" =>  intval($menu["id"]),
                "selected" => false,
                "description" => html_entity_decode($menu["description"]),
                "imageSrc" => ""
            );
        }
    }

    $app->response()->header("Content-Type", "application/json");

    $app->response()->body(json_encode($menus));
});
 
 /**x
 *  * Okan CIRAN
 * @since 08.08.2016
 
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysOperationTypesRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOperationTypesRrpBLL');
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
