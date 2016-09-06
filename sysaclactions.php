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
 * @since 26.07.2016
 */
$app->get("/pkFillComboBoxFullAction_sysAclActions/", function () use ($app ) {
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillComboBoxFullAction_sysAclActions" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
    $resCombobox = $BLL->fillComboBoxFullAction();
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "text" => html_entity_decode($flow["name"]),
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true, "active" => $flow["active"],
                "module_id" => $flow["module_id"],
                "module_name" => html_entity_decode($flow["module_name"]),),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});


/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkInsert_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_sysAclActions" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['name']));
    }
    $vModuleId = NULL;
    if (isset($_GET['module_id'])) {
        $stripper->offsetSet('module_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['module_id']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['description']));
    }
    $vRoleIds = NULL;
    if (isset($_GET['role_ids'])) {
        $stripper->offsetSet('role_ids', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, $app, $_GET['role_ids']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('name'))
        $vName = $stripper->offsetGet('name')->getFilterValue();
    if ($stripper->offsetExists('description'))
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    if ($stripper->offsetExists('module_id'))
        $vModuleId = $stripper->offsetGet('module_id')->getFilterValue();
    if ($stripper->offsetExists('role_ids'))
        $vRoleIds = $stripper->offsetGet('role_ids')->getFilterValue();


    $resDataInsert = $BLL->insert(array(
        'name' => $vName,
        'module_id' => $vModuleId,
        'description' => $vDescription,
        'role_ids' => $vRoleIds,
        'pk' => $pk));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);
/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkUpdate_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_sysAclActions" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['name']));
    }
    $vModuleId = NULL;
    if (isset($_GET['module_id'])) {
        $stripper->offsetSet('module_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['module_id']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['description']));
    }
    $vRoleIds = NULL;
    if (isset($_GET['role_ids'])) {
        $stripper->offsetSet('role_ids', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, $app, $_GET['role_ids']));
    }
    
    

    $stripper->strip();
    if ($stripper->offsetExists('id'))
        $vId = $stripper->offsetGet('id')->getFilterValue();
    if ($stripper->offsetExists('name'))
        $vName = $stripper->offsetGet('name')->getFilterValue();
    if ($stripper->offsetExists('description'))
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    if ($stripper->offsetExists('module_id'))
        $vModuleId = $stripper->offsetGet('module_id')->getFilterValue();
    if ($stripper->offsetExists('role_ids'))
        $vRoleIds = $stripper->offsetGet('role_ids')->getFilterValue();

    $resDataInsert = $BLL->update(array(
        'id' => $vId,
        'name' => $vName,
        'module_id' => $vModuleId,
        'role_ids' => $vRoleIds,
        'description' => $vDescription,
        'pk' => $pk));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);

/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkUpdateAct_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdateAct_sysAclActions" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['name']));
    }
    $vModuleId = NULL;
    if (isset($_GET['module_id'])) {
        $stripper->offsetSet('module_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['module_id']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['description']));
    }
    $vRoleIds = NULL;
    if (isset($_GET['role_ids'])) {
        $stripper->offsetSet('role_ids', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, $app, $_GET['role_ids']));
    }
    
    

    $stripper->strip();
    if ($stripper->offsetExists('id'))
        $vId = $stripper->offsetGet('id')->getFilterValue();
    if ($stripper->offsetExists('name'))
        $vName = $stripper->offsetGet('name')->getFilterValue();
    if ($stripper->offsetExists('description'))
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    if ($stripper->offsetExists('module_id'))
        $vModuleId = $stripper->offsetGet('module_id')->getFilterValue();
    if ($stripper->offsetExists('role_ids'))
        $vRoleIds = $stripper->offsetGet('role_ids')->getFilterValue();

    $resDataInsert = $BLL->updateAct(array(
        'id' => $vId,
        'name' => $vName,
        'module_id' => $vModuleId,
        'role_ids' => $vRoleIds,
        'description' => $vDescription,
        'pk' => $pk));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);
   
/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkDelete_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDelete_sysAclActions" end point, X-Public variable not found');
    $Pk = $headerParams['X-Public'];
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    $resDataDeleted = $BLL->Delete(array(
        'id' => $vId,
        'pk' => $Pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataDeleted));
}
);
 
/**
 *  * Okan CIRAN
 * @since 26.07.2016
 */
$app->get("/pkFillActionTree_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillActionTree_sysAclActions" end point, X-Public variable not found');

    $vsearch = null;
    if (isset($_GET['search'])) {
        $stripper->offsetSet('search', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['search']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('search'))
        $vsearch = $stripper->offsetGet('search')->getFilterValue();

    $resCombobox = $BLL->fillActionTree(array(
        'search' => $vsearch,));

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "text" => html_entity_decode($flow["name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
            // "icon_class"=>$flow["icon_class"], 
            "attributes" => array("active" => $flow["active"],
                "module_id" => $flow["module_id"],
                "module_name" => $flow["module_name"]
            ),
        );
    }

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 15-06-2016
 */
$app->get("/pkFillActionList_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillActionList_sysAclActions" end point, X-Public variable not found');
    }
    //  $pk = $headerParams['X-Public'];

    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['name']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['description']));
    }
    $vModuleId = NULL;
    if (isset($_GET['module_id'])) {
        $stripper->offsetSet('module_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['module_id']));
    }
    $vActive = NULL;
    if (isset($_GET['active'])) {
        $stripper->offsetSet('active', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['active']));
    }

    $vPage = NULL;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['page']));
    }
    $vRows = NULL;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['rows']));
    }
    $vSort = NULL;
    if (isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['sort']));
    }
    $vOrder = NULL;
    if (isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ORDER, $app, $_GET['order']));
    }
    $filterRules = null;
    if (isset($_GET['filterRules'])) {
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, $app, $_GET['filterRules']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    }
    if ($stripper->offsetExists('module_id')) {
        $vModuleId = $stripper->offsetGet('module_id')->getFilterValue();
    }
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
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

    $resDataGrid = $BLL->fillActionList(array(
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'name' => $vName,
        'module_id' => $vModuleId,
        'active' => $vActive,
        'description' => $vDescription,
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillActionListRtc(array(
        'name' => $vName,
        'module_id' => $vModuleId,
        'active' => $vActive,
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
                "module_id" => $flow["module_id"] ,
                "module_name" => html_entity_decode($flow["module_name"]),
                "description" => html_entity_decode($flow["description"]),
                "role_ids" => $flow["role_ids"] ,                
                "attributes" => array(                    
                    "active" => $flow["active"], 
                ));
        };
        $counts = $resTotalRowCount[0]['count'];
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

/* * x
 *  * Okan CIRAN
 * @since 26-07-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkUpdateMakeActiveOrPassive_sysAclActions" end point, X-Public variable not found');
    }
    $Pk = $headerParams['X-Public'];
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    $resData = $BLL->makeActiveOrPassive(array(
        'id' => $vId,
        'pk' => $Pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resData));
}
);
 
  
/**
 *  * Okan CIRAN
 * @since 26-07-2016
 */
$app->get("/pkFillActionDdList_sysAclActions/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclActionsBLL');

    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillActionDdList_sysAclActions" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public']; 
    $resCombobox = $BLL->fillActionDdList(array(
            //  'language_code' => $vLanguageCode,
    ));


    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["description"]),
            // "imageSrc"=>$flow["logo"],             
            "attributes" => array(                 
                    "active" => $flow["active"],
                    "module_id" => $flow["module_id"],
                    "module_name" => html_entity_decode($flow["module_name"])
            ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});




$app->run();
