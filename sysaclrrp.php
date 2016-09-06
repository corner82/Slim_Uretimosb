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
 * @since 15-07-2016
 */ 
$app->get("/pkInsert_sysAclRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysAclRrp" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vName = NULL;
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name']));
    }
    $vNameEng = '';
    if (isset($_GET['name_eng'])) {
         $stripper->offsetSet('name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name_eng']));
    }
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }    
    $vResourceId = NULL;
    if (isset($_GET['resource_id'])) {
         $stripper->offsetSet('resource_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['resource_id']));
    }
     
    $stripper->strip();
    if($stripper->offsetExists('name')) $vName = $stripper->offsetGet('name')->getFilterValue();
    if($stripper->offsetExists('name_eng')) $vNameEng = $stripper->offsetGet('name_eng')->getFilterValue();
    if($stripper->offsetExists('description')) $vDescription = $stripper->offsetGet('description')->getFilterValue();    
    if($stripper->offsetExists('resource_id')) $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
          
    $resDataInsert = $BLL->insert(array(
            'name' => $vName,      
            'name_eng' => $vNameEng, 
            'resource_id' => $vResourceId,           
            'description' => $vDescription,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);

/**
 *  * Okan CIRAN
 * @since 15-07-2016
 */ 
$app->get("/pkUpdate_sysAclRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysAclRrp" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
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
    $vNameEng = '';
    if (isset($_GET['name_eng'])) {
         $stripper->offsetSet('name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name_eng']));
    }
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }    
    $vResourceId = NULL;
    if (isset($_GET['resource_id'])) {
         $stripper->offsetSet('resource_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['resource_id']));
    }
     
    $stripper->strip();
    if($stripper->offsetExists('id')) $vId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('name')) $vName = $stripper->offsetGet('name')->getFilterValue();
    if($stripper->offsetExists('name_eng')) $vNameEng = $stripper->offsetGet('name_eng')->getFilterValue();
    if($stripper->offsetExists('description')) $vDescription = $stripper->offsetGet('description')->getFilterValue();    
    if($stripper->offsetExists('resource_id')) $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
          
    $resDataInsert = $BLL->update(array(
            'id' => $vId,   
            'name' => $vName,      
            'name_eng' => $vNameEng, 
            'resource_id' => $vResourceId,           
            'description' => $vDescription,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);
 
/**
 *  * Okan CIRAN
 * @since 15-07-2016
 */
$app->get("/pkDelete_sysAclRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');   
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
 * @since 15-07-2016
 */
$app->get("/pkFillPrivilegesList_sysAclRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillPrivilegesList_sysAclRrp" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];

    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['name']));
    }  
    $vNameEng = NULL;
    if (isset($_GET['name_eng'])) {
        $stripper->offsetSet('name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['name_eng']));
    }  
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description']));
    }            
    $vResourceName = NULL;
    if (isset($_GET['resource_name'])) {
        $stripper->offsetSet('resource_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['resource_name']));
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
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    }   
    if ($stripper->offsetExists('name_eng')) {
        $vNameEng = $stripper->offsetGet('name_eng')->getFilterValue();
    }   
    if ($stripper->offsetExists('resource_name')) {
        $vResourceName = $stripper->offsetGet('resource_name')->getFilterValue();
    }    
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
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
    
    $resDataGrid = $BLL->fillPrivilegesList(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'name' => $vName,       
        'name_eng' => $vName,       
        'resource_name' => $vResourceName,
        'description' => $vDescription,       
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillPrivilegesListRtc(array(
        'name' => $vName,          
        'name_eng' => $vName,       
        'resource_name' => $vResourceName,      
        'description' => $vDescription,
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "name" => html_entity_decode($flow["name"]),
            "name_eng" => html_entity_decode($flow["name_eng"]),
            "resource_id" => $flow["resource_id"],
            "resource_name" => html_entity_decode($flow["resource_name"]),
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

/**x
 *  * Okan CIRAN
 * @since 15-07-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysAclRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');
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


/**
 *  * Okan CIRAN
 * @since 15-07-2016
 */
$app->get("/pkFillResourceGroups_sysAclRrp/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');    
    $vParentId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $vState =NULL;
    if (isset($_GET['state'])) {
        $stripper->offsetSet('state', $stripChainerFactory->get(stripChainers::FILTER_ONLY_STATE_ALLOWED,
                                                $app,
                                                $_GET['state']));
    }
    $vLastNode =NULL;
    if (isset($_GET['last_node'])) {
        $stripper->offsetSet('last_node', 
                    $stripChainerFactory->get(stripChainers::FILTER_ONLY_BOOLEAN_ALLOWED,
                                                $app,
                                                $_GET['last_node']));  
    }
    $vMachine= NULL;
     if (isset($_GET['roles'])) {
        $stripper->offsetSet('roles', 
                $stripChainerFactory->get(stripChainers::FILTER_ONLY_BOOLEAN_ALLOWED,
                        $app,
                        $_GET['roles']));
    }
    
    $vsearch = null;
    if(isset($_GET['search'])) {
        $stripper->offsetSet('search', 
                $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                        $app,
                        $_GET['search']));
    }
    
    
    $stripper->strip();
    if($stripper->offsetExists('machine')) $vMachine = $stripper->offsetGet('machine')->getFilterValue();    
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('state')) $vState = $stripper->offsetGet('state')->getFilterValue();
    if($stripper->offsetExists('last_node')) $vLastNode = $stripper->offsetGet('last_node')->getFilterValue();
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();

    if (isset($_GET['id'])) {
        $resCombobox = $BLL->FillResourceGroups(array('parent_id' => $vParentId,
                                                         'state' => $vState,
                                                         'last_node' => $vLastNode,
                                                         'machine' => $vMachine,
                                                         'search' => $vsearch,
                                                                ));
    } else {
        $resCombobox = $BLL->FillResourceGroups( );
    }

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

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($flows));
});

 
/**
 *  * Okan CIRAN
 * @since 15-07-2016
 */
$app->get("/pkTransferRolesPrivilege_sysAclRrp/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysAclRrpBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public']; 
    $vRoleId = NULL;
    if (isset($_GET['role_id'])) {
         $stripper->offsetSet('role_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['role_id']));
    }  
    $vResourceId = NULL;
    if (isset($_GET['resource_id'])) {
         $stripper->offsetSet('resource_id',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['resource_id']));
    }   
    $vPrivilegeId = NULL;
    if (isset($_GET['privilege_id'])) {
         $stripper->offsetSet('privilege_id',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['privilege_id']));
    } 
    $stripper->strip();           
    if ($stripper->offsetExists('role_id')) {
        $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();
    }
    if ($stripper->offsetExists('resource_id')) {
        $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('privilege_id')) {
        $vPrivilegeId = $stripper->offsetGet('privilege_id')->getFilterValue();
    }
    $resData = $BLL->transferRolesPrivilege(array(              
            'role_id' => $vRoleId , 
            'resource_id' => $vResourceId ,
            'privilege_id' => $vPrivilegeId ,
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 


$app->run();
