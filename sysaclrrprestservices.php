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
 * @since 27-07-2016
 * rest servislere eklendi
 */ 
$app->get("/pkInsert_sysAclRrpRestservices/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysAclRrpRestservices" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vRestService = NULL;
    if (isset($_GET['restservices_id'])) {
         $stripper->offsetSet('restservices_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['restservices_id']));
    } 
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }    
    $vRrpId = NULL;
    if (isset($_GET['rrp_id'])) {
         $stripper->offsetSet('rrp_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rrp_id']));
    } 
    
    $stripper->strip();
    if($stripper->offsetExists('restservices_id')) $vRestService = $stripper->offsetGet('restservices_id')->getFilterValue();    
    if($stripper->offsetExists('description')) $vDescription = $stripper->offsetGet('description')->getFilterValue();    
    if($stripper->offsetExists('rrp_id')) $vRrpId = $stripper->offsetGet('rrp_id')->getFilterValue();
          
    $resDataInsert = $BLL->insert(array(
            'restservices_id' => $vRestService,  
            'rrp_id' => $vRrpId,           
            'description' => $vDescription,
            'pk' => $pk)); 
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);

/**
 *  * Okan CIRAN
 * @since 27-07-2016
 * rest servislere eklendi
 */ 
$app->get("/pkUpdate_sysAclRrpRestservices/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysAclRrpRestservices" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $vRestService = NULL;
    if (isset($_GET['restservice'])) {
         $stripper->offsetSet('restservice',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['restservice']));
    } 
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }    
    $vRrpId = NULL;
    if (isset($_GET['rrp_id'])) {
         $stripper->offsetSet('rrp_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rrp_id']));
    } 
    
    $stripper->strip();
    if($stripper->offsetExists('id')) $vId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('restservice')) $vRestService = $stripper->offsetGet('restservice')->getFilterValue();    
    if($stripper->offsetExists('description')) $vDescription = $stripper->offsetGet('description')->getFilterValue();    
    if($stripper->offsetExists('rrp_id')) $vRrpId = $stripper->offsetGet('rrp_id')->getFilterValue();
    
    $resDataInsert = $BLL->update(array(
            'id' => $vId,   
            'restservice' => $vRestService,  
            'rrp_id' => $vRrpId,           
            'description' => $vDescription,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);
 
/**
 *  * Okan CIRAN
 * @since 27-07-2016
 *  rest servislere eklendi
 */
$app->get("/pkDelete_sysAclRrpRestservices/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');   
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
 * @since 27-07-2016
 * bu servis kullanılmıyor qwerty
 */
$app->get("/pkFillRrpRestServicesList_sysAclRrpRestservices/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillRrpRestServicesList_sysAclRrpRestservices" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];

    $vRestService = NULL;
    if (isset($_GET['restservice'])) {
        $stripper->offsetSet('restservice', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['restservice']));
    }  
    $vActive = NULL;
    if (isset($_GET['active'])) {
        $stripper->offsetSet('active', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['active']));
    }
    $vRrpId = NULL;
    if (isset($_GET['rrp_id'])) {
        $stripper->offsetSet('rrp_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['rrp_id']));
    }
    $vResourceId = NULL;
    if (isset($_GET['resource_id'])) {
        $stripper->offsetSet('resource_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['resource_id']));
    }
    $vRoleId = NULL;
    if (isset($_GET['role_id'])) {
        $stripper->offsetSet('role_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['role_id']));
    }
    $vPrivilegeId = NULL;
    if (isset($_GET['privilege_id'])) {
        $stripper->offsetSet('privilege_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['privilege_id']));
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
    if ($stripper->offsetExists('restservice')) {
        $vRestService = $stripper->offsetGet('restservice')->getFilterValue();
    }   
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    }   
    if ($stripper->offsetExists('rrp_id')) {
        $vRrpId = $stripper->offsetGet('rrp_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('resource_id')) {
        $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
    }   
    if ($stripper->offsetExists('role_id')) {
        $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();
    }
    if ($stripper->offsetExists('privilege_id')) {
        $vPrivilegeId = $stripper->offsetGet('privilege_id')->getFilterValue();
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
    if ($stripper->offsetExists('filterRules')) {
        $filterRules = $stripper->offsetGet('filterRules')->getFilterValue();
    }
    
    $resDataGrid = $BLL->fillRrpRestServicesList(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'restservice' => $vRestService,       
        'active' => $vActive,       
        'rrp_id' => $vRrpId,
        'resource_id' => $vResourceId, 
        'role_id' => $vRoleId, 
        'privilege_id' => $vPrivilegeId, 
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillRrpRestServicesListRtc(array(
        'restservice' => $vRestService,       
        'active' => $vActive,       
        'rrp_id' => $vRrpId,
        'resource_id' => $vResourceId, 
        'role_id' => $vRoleId, 
        'privilege_id' => $vPrivilegeId, 
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "rrp_id" => $flow["rrp_id"],
            "restservice" => html_entity_decode($flow["restservice"]),
            "map_adi" => html_entity_decode($flow["map_adi"]),
            "resource_id" => $flow["resource_id"],
            "resource_name" => html_entity_decode($flow["resource_name"]),
            "role_id" => $flow["role_id"],                
            "role_name" => html_entity_decode($flow["role_name"]),            
            "privilege_id" => $flow["privilege_id"],
            "privilege_name" => html_entity_decode($flow["privilege_name"]),
            "description" => html_entity_decode($flow["description"]),
            "create_date" => $flow["create_date"],
            "description" => html_entity_decode($flow["description"]),
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
 
/**
 *  * Okan CIRAN
 * @since 28-07-2016
 *  rest servislere eklendi
 */
$app->get("/pkFillRestServicesOfPrivileges_sysAclRrpRestservices/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillRestServicesOfPrivileges_sysAclRrpRestservices" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
    
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }  
    
    $stripper->strip();    
    if($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    
    
    $resCombobox = $BLL->fillRestServicesOfPrivileges(array(
                                    'id' => $vId,                                    
                        ));    

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => html_entity_decode($flow["restservice_name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
            "icon_class"=>"icon_class", 
            "attributes" => array( "active" => $flow["active"],
                "rrp_id" =>$flow["rrp_id"], 
                "services_group_id" => $flow["services_group_id"],                
                "restservices_id" =>$flow["restservices_id"],       
            //    "resource_id" => $flow["resource_id"], 
            //    "role_id" =>$flow["role_id"], 
            //    "privilege_id" => $flow["privilege_id"],                
                "description" => html_entity_decode($flow["description"]),
                ),
        );
    }
     
    
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 27-07-2016
 *  rest servislere eklendi
 */
$app->get("/pkFillNotInRestServicesOfPrivileges_sysAclRrpRestservices/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillNotInRestServicesOfPrivileges_sysAclRrpRestservices" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];

   
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['id']));
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
     
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
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
    if ($stripper->offsetExists('filterRules')) {
        $filterRules = $stripper->offsetGet('filterRules')->getFilterValue();
    }
    
    $resDataGrid = $BLL->fillNotInRestServicesOfPrivileges(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,           
        'id' => $vId,
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillNotInRestServicesOfPrivilegesRtc(array(
        'id' => $vId,
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "restservice_name" => html_entity_decode($flow["restservice_name"]),
            "services_group_id" => $flow["services_group_id"],
            "services_group_name" => html_entity_decode($flow["services_group_name"]),            
            "description" => html_entity_decode($flow["description"]),            
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
 * @since 28-07-2016
 * rest servislere eklendi
 */
$app->get("/pkFillNotInRestServicesOfPrivilegesTree_sysAclRrpRestservices/", function () use ($app ) { 
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillNotInRestServicesOfPrivilegesTree_sysAclRrpRestservices" end point, X-Public variable not found');    
   // $pk = $headerParams['X-Public'];
    $vParentId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $vRrpId = 0;
    if (isset($_GET['rrp_id'])) {
        $stripper->offsetSet('rrp_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rrp_id']));
    } 
    
    $vsearch = null;
    if(isset($_GET['search'])) {
        $stripper->offsetSet('search', 
                $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                        $app,
                        $_GET['search']));
    }
     
    $stripper->strip();        
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();    
    if($stripper->offsetExists('rrp_id')) $vRrpId = $stripper->offsetGet('rrp_id')->getFilterValue();    
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();
 
   
    $resTree = $BLL->fillNotInRestServicesOfPrivilegesTree(array('parent_id' => $vParentId, 
                                              'rrp_id' =>  $vRrpId ,
                                              'search' => $vsearch,
                                                                ));
   
    $flows = array();
    foreach ($resTree as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => html_entity_decode($flow["name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
           // "icon_class"=>$flow["icon_class"], 
            "attributes" =>
            array(  "root" => $flow["root_type"], 
                    "active" => $flow["active"],
                    "services_group_id" => $flow["services_group_id"],
                    "service" => html_entity_decode($flow["service"]),
                    "description" => html_entity_decode($flow["description"]),
                    "last_node" => $flow["last_node"]
                    ),
        );
    }
    
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 28-07-2016
 * rest servislere eklendi
 */
$app->get("/pkFillRestServicesOfPrivilegesTree_sysAclRrpRestservices/", function () use ($app ) { 
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRrpRestservicesBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillRestServicesOfPrivilegesTree_sysAclRrpRestservices" end point, X-Public variable not found');    
   // $pk = $headerParams['X-Public'];
    $vParentId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    
    $vRoleId = 0;
    if (isset($_GET['role_id'])) {
        $stripper->offsetSet('role_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['role_id']));
    } 
    $vResourceId = 0;
    if (isset($_GET['resource_id'])) {
        $stripper->offsetSet('resource_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['resource_id']));
    } 
    
    $vsearch = null;
    if(isset($_GET['search'])) {
        $stripper->offsetSet('search', 
                $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                        $app,
                        $_GET['search']));
    }
     
    $stripper->strip();        
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();        
    if($stripper->offsetExists('role_id')) $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();        
    if($stripper->offsetExists('resource_id')) $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();        
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();
 
   
    $resTree = $BLL->fillRestServicesOfPrivilegesTree(array('parent_id' => $vParentId, 
                                                'role_id' => $vRoleId,
                                                'resource_id' => $vResourceId,
                                                'search' => $vsearch,
                                                                ));
   
    $flows = array();
    foreach ($resTree as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => html_entity_decode($flow["name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
           // "icon_class"=>$flow["icon_class"], 
            "attributes" =>
            array(  "root" => $flow["root_type"], 
                    "active" => $flow["active"],                
                    "rrp_restservice_id" => $flow["rrp_restservice_id"],
                    "services_group_id" => $flow["services_group_id"],
                    "service" => html_entity_decode($flow["service"]),
                    "description" => html_entity_decode($flow["description"]),
                    "last_node" => $flow["last_node"]
                    ),
        );
    }    
    
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($flows));
});





$app->run();
