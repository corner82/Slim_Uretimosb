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
 * @since 18-02-2016
 */
$app->get("/pkFillSingularFirmMachineTools_infoFirmMachineTool/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');

    $headerParams = $app->request()->headers();
    $sort = null;
    if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
    }

    $order = null;
    if (isset($_GET['order'])) {
        $order = $_GET['order'];
    }

    $rows = 10;
    if (isset($_GET['rows'])) {
        $rows = $_GET['rows'];
    }

    $page = 1;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }

    $filterRules = null;
    if (isset($_GET['filterRules'])) {
        $filterRules = $_GET['filterRules'];
    }

    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillSingularFirmMachineTools_infoFirmMachineTool" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
    }

    $resDataGrid = $BLL->fillSingularFirmMachineTools(array('language_code' => $vLanguageCode,
        'page' => $page,
        'rows' => $rows,
        'sort' => $sort,
        'order' => $order,
        'pk' => $pk,
        'filterRules' => $filterRules));

    $resTotalRowCount = $BLL->fillSingularFirmMachineToolsRtc(array('pk' => $pk,
        'language_code' => $vLanguageCode));

    $flows = array();
    foreach ($resDataGrid['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "firm_id" => $flow["firm_id"],
            "firm_name" => $flow["firm_name"],
            "sys_machine_tool_id" => $flow["sys_machine_tool_id"],
            "machine_tool_names" => $flow["machine_tool_names"],
            "machine_tool_name_eng" => $flow["machine_tool_name_eng"],
            "profile_public" => $flow["profile_public"],
            "state_profile_public" => $flow["state_profile_public"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name" => $flow["operation_name"],
            "act_parent_id" => $flow["act_parent_id"],
            "language_parent_id" => $flow["language_parent_id"],
            "owner_id" => $flow["owner_id"],
            "owner_username" => $flow["owner_username"],
            "cons_allow_id" => $flow["cons_allow_id"],
            "cons_allow" => $flow["cons_allow"],
            "availability_id" => $flow["availability_id"],
            "state_availability" => $flow["state_availability"],
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
            "picture" => $flow["picture"],
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount['resultSet'][0]['count'];
    $resultArray['rows'] = $flows;

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resultArray));
});


/**
 *  * Okan CIRAN
 * @since 23-02-2016
 */
$app->get("/pkFillUsersFirmMachines_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillUsersFirmMachines_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }
    $vMachineId = NULL;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['machine_id']));
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
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
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

    
        $resDataGrid = $BLL->fillUsersFirmMachines(array(
            'language_code' => $vLanguageCode,
            'page' => $vPage,
            'rows' => $vRows,
            'sort' => $vSort,
            'order' => $vOrder,            
            'filterRules' => $filterRules,
            'pk' => $pk,
            'machine_id' => $vMachineId,
        ));
        $resTotalRowCount = $BLL->fillUsersFirmMachinesRtc(array(
            'language_code' => $vLanguageCode,
            'filterRules' => $filterRules,
            'pk' => $pk,
            'machine_id' => $vMachineId,
        ));
   

    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "machine_id" => $flow["machine_id"],
                "manufacturer_name" => html_entity_decode($flow["manufacturer_name"]),
                "machine_tool_grup_names" => html_entity_decode($flow["machine_tool_grup_names"]),
                "machine_tool_names" => html_entity_decode($flow["machine_tool_names"]),
                "model" => html_entity_decode($flow["model"]),
                "model_year" => $flow["model_year"],
                "act_parent_id" => $flow["act_parent_id"],
               // "picture" => $flow["picture"],
                "total" => $flow["total"],
                "active" => $flow["active"],
                "state_active" => html_entity_decode($flow["state_active"]),
                "profile_public" => $flow["profile_public"],
                "state_profile_public" => html_entity_decode($flow["state_profile_public"]),
                "cons_allow_id" => $flow["cons_allow_id"],
                "cons_allow" => html_entity_decode($flow["cons_allow"]),
                "availability_id" => $flow["availability_id"],
                "state_availability" => html_entity_decode($flow["state_availability"]),
                "attributes" => array("notroot" => true),
            );
        }
        $counts = $resTotalRowCount[0]['count'];
    }
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resultArray));
});


/**
 *  * Okan CIRAN
 * @since 23-02-2016
 */
$app->get("/pkFillUsersFirmMachineProperties_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillUsersFirmMachineProperties_infoFirmMachineTool" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vMachineId = 0;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if (isset($_GET['machine_id'])) {
        $resDataGrid = $BLL->fillUsersFirmMachineProperties(array(
            'language_code' => $vLanguageCode,
            'pk' => $pk,
            'machine_id' => $vMachineId,
        ));
    } else {
        $resDataGrid = $BLL->fillUsersFirmMachineProperties(array(
            'language_code' => $vLanguageCode,
            'pk' => $pk,
        ));
    }

    $flows = array();
    if (isset($resDataGrid['resultSet'][0]['machine_id'])) {
        foreach ($resDataGrid['resultSet'] as $flow) {
            $flows[] = array(
                "machine_id" => $flow["machine_id"],
                "id" => $flow["id"],
                "property_names" => html_entity_decode($flow["property_names"]),
                "property_name_eng" => html_entity_decode($flow["property_name_eng"]),
                "property_value" => $flow["property_value"],
                "property_string_value" => html_entity_decode($flow["property_string_value"]),
                "unit_id" => $flow["unit_id"],
                "unitcodes" => html_entity_decode($flow["unitcodes"]),
                "picture" => $flow["picture"],
                "attributes" => array("notroot" => true),
            );
        }
    }
    $resultArray = array();
    //  $resultArray['total'] = 2;//$resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */
    // if (isset($resDataGrid['resultSet']['machine_id'])) {
    //    $app->response()->body(json_encode($flows));
    // } else {
    $app->response()->body(json_encode($resultArray));
    //  }
});


/* * x
 *  * Okan CIRAN
 * @since 25-02-2016
 */
$app->get("/pkDeletedAct_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }    

    $resDataDeleted = $BLL->DeletedAct(array(
        'id' => $vId,        
        'pk' => $pk,
    ));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataDeleted));
}
);


/* * x
 *  * Okan CIRAN
 * @since 25-02-2016
 */
$app->get("/pkDeleteConsAct_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeleteConsAct_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }    

    $resDataDeleted = $BLL->deleteConsAct(array(
        'id' => $vId,        
        'pk' => $pk,
    ));

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataDeleted));
}
);
 
/* * x
 *  * Okan CIRAN
 * @since 25-02-2016
 */
$app->get("/pkInsert_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }   
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['profile_public']));
    }    
    $vAvailabilityId = 1;
    if (isset($_GET['availability_id'])) {
        $stripper->offsetSet('availability_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['availability_id']));
    }
    $vOwnerShipId = 1;
    if (isset($_GET['ownership_id'])) {
        $stripper->offsetSet('ownership_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['ownership_id']));
    }    
    $vMachineId = NULL;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_id']));
    }
    $vTotal = 0;
    if (isset($_GET['total'])) {
        $stripper->offsetSet('total', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['total']));
    }
    $vPicture = NULL;
    if (isset($_GET['picture'])) {
        $stripper->offsetSet('picture', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['picture']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_id')) {
        $vOwnerShipId = $stripper->offsetGet('ownership_id')->getFilterValue();
    }
    if ($stripper->offsetExists('total')) {
        $vTotal = $stripper->offsetGet('total')->getFilterValue();
    }
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('availability_id')) {
        $vAvailabilityId = $stripper->offsetGet('availability_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('picture')) {
        $vPicture = $stripper->offsetGet('picture')->getFilterValue();
    }

    $resDataInsert = $BLL->insert(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'profile_public' => $vProfilePublic,
        'machine_id' => $vMachineId,
        'ownership_id' => $vOwnerShipId,
        'total' => $vTotal,
        'availability_id' => $vAvailabilityId,        
        'picture' => $vPicture,
        'pk' => $pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);

/* * x
 *  * Okan CIRAN
 * @since 19-08-2016
 */
$app->get("/pkInsertCons_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsertCons_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }  
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    }    
    $vAvailabilityId = 1;
    if (isset($_GET['availability_id'])) {
        $stripper->offsetSet('availability_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['availability_id']));
    }
    $vOwnerShipId = 1;
    if (isset($_GET['ownership_id'])) {
        $stripper->offsetSet('ownership_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['ownership_id']));
    }    
    $vMachineId = NULL;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['machine_id']));
    }
    $vTotal = 0;
    if (isset($_GET['total'])) {
        $stripper->offsetSet('total', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['total']));
    }
    $vFirmId = 0;
    if (isset($_GET['firm_id'])) {
        $stripper->offsetSet('firm_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['firm_id']));
    }
   
    $stripper->strip();
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_id')) {
        $vOwnerShipId = $stripper->offsetGet('ownership_id')->getFilterValue();
    }
    if ($stripper->offsetExists('total')) {
        $vTotal = $stripper->offsetGet('total')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('availability_id')) {
        $vAvailabilityId = $stripper->offsetGet('availability_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    }
    

    $resDataInsert = $BLL->insertCons(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,        
        'profile_public' => $vProfilePublic,
        'machine_id' => $vMachineId,
        'ownership_id' => $vOwnerShipId,
        'total' => $vTotal,
        'availability_id' => $vAvailabilityId,        
        'firm_id' => $vFirmId,
        'pk' => $pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);

/* * x
 *  * Okan CIRAN
 * @since 19-08-2016
 */
$app->get("/pkUpdateCons_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdateCons_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }  
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['id']));
    }  
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    } 
    $vAvailabilityId = 1;
    if (isset($_GET['availability_id'])) {
        $stripper->offsetSet('availability_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['availability_id']));
    }
    $vOwnerShipId = 1;
    if (isset($_GET['ownership_id'])) {
        $stripper->offsetSet('ownership_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['ownership_id']));
    }  
    $vTotal = 0;
    if (isset($_GET['total'])) {
        $stripper->offsetSet('total', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['total']));
    }
    
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }       
    if ($stripper->offsetExists('ownership_id')) {
        $vOwnerShipId = $stripper->offsetGet('ownership_id')->getFilterValue();
    }
    if ($stripper->offsetExists('total')) {
        $vTotal = $stripper->offsetGet('total')->getFilterValue();
    }   
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('availability_id')) {
        $vAvailabilityId = $stripper->offsetGet('availability_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    $resDataInsert = $BLL->updateCons(array(
        'url' => $_GET['url'],
        'id' => $vId,  
        'language_code' => $vLanguageCode,
        'profile_public' => $vProfilePublic, 
        'ownership_id' => $vOwnerShipId,
        'availability_id' => $vAvailabilityId, 
        'total' => $vTotal, 
        'pk' => $pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);

/* * x
 *  * Okan CIRAN
 * @since 25-02-2016
 */
$app->get("/pkUpdate_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['id']));
    }
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['profile_public']));
    }    
    $vAvailabilityId = 1;
    if (isset($_GET['availability_id'])) {
        $stripper->offsetSet('availability_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['availability_id']));
    }
    $vOwnerShipId = 1;
    if (isset($_GET['ownership_id'])) {
        $stripper->offsetSet('ownership_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['ownership_id']));
    }    
    $vFirmId = NULL;
    if (isset($_GET['firm_id'])) {
        $stripper->offsetSet('firm_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['firm_id']));
    }
    $vMachineId = NULL;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_id']));
    }
    $vPicture = NULL;
    if (isset($_GET['picture'])) {
        $stripper->offsetSet('picture', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['picture']));
    }
    $vTotal = 0;
    if (isset($_GET['total'])) {
        $stripper->offsetSet('total', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['total']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_id')) {
        $vOwnerShipId = $stripper->offsetGet('ownership_id')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    }
    if ($stripper->offsetExists('availability_id')) {
        $vAvailabilityId = $stripper->offsetGet('availability_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('picture')) {
        $vPicture = $stripper->offsetGet('picture')->getFilterValue();
    }
    if ($stripper->offsetExists('total')) {
        $vTotal = $stripper->offsetGet('total')->getFilterValue();
    }

    $resDataInsert = $BLL->update(array(
        'id' => $vId,
        'network_key' => $vNpk,
        'language_code' => $vLanguageCode,
        'profile_public' => $vProfilePublic,
        'firm_id' => $vFirmId,
        'machine_id' => $vMachineId,
        'ownership_id' => $vOwnerShipId,        
        'total' => $vTotal,
        'availability_id' => $vAvailabilityId,        
        'picture' => $vPicture,
        'pk' => $pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);
 
/**
 *  * Okan CIRAN
 * @since 15-04-2016
 */
$app->get("/pkFillFirmMachineGroupsCounts_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmMachineGroupsCounts_infoFirmMachineTool" end point, X-Public variable not found');   
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['npk']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if ($stripper->offsetExists('npk'))
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();

    $result = $BLL->fillFirmMachineGroupsCounts(array('language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,
    ));

    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "machine_grup_id" => $flow["machine_grup_id"],
            "machine_count" => $flow["machine_count"],
            "group_name" => html_entity_decode($flow["group_name"]),
            "attributes" => array("notroot" => true,),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 20-04-2016
 */
$app->get("/pkFillUsersFirmMachinesNpkEski_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillUsersFirmMachinesNpk_infoFirmMachineTool" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vMachineId = NULL;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_id']));
    }
    $vMachineGrupId = NULL;
    if (isset($_GET['machine_grup_id'])) {
        $stripper->offsetSet('machine_grup_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_grup_id']));
    }
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['npk']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_grup_id')) {
        $vMachineGrupId = $stripper->offsetGet('machine_grup_id')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    }

    $resDataGrid = $BLL->FillUsersFirmMachinesNpk(array(
        'pk' => $pk,
        'language_code' => $vLanguageCode,
        'machine_grup_id' => $vMachineGrupId,
        'machine_id' => $vMachineId,
        'network_key' => $vNetworkKey,
    ));

    $resTotalRowCount = $BLL->fillUsersFirmMachinesNpkRtc(array(
        'pk' => $pk,
        'machine_grup_id' => $vMachineGrupId,
        'machine_id' => $vMachineId,
        'network_key' => $vNetworkKey,
    ));


    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "machine_id" => $flow["machine_id"],
                "manufacturer_name" => html_entity_decode($flow["manufacturer_name"]),
                "machine_tool_grup_names" => html_entity_decode($flow["machine_tool_grup_names"]),
                "machine_tool_names" => html_entity_decode($flow["machine_tool_names"]),
                "model" => html_entity_decode($flow["model"]),
                "model_year" => $flow["model_year"],
                "series" => html_entity_decode($flow["series"]),
                "firm_id" => $flow["firm_id"],
                "picture" => $flow["picture"],
                "total" => $flow["total"],
                "attributes" => array("notroot" => true),
            );
        }
    }
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resultArray));
});

/**
 *  * Okan CIRAN
 * @since 08-06-2016
 */
$app->get("/pkFillUsersFirmMachinesNpk_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];
     
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillUsersFirmMachinesNpk_infoFirmMachineTool" end point, X-Public variable not found');
    }
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vMachineId = NULL;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_id']));
    }
    $vMachineGrupId = NULL;
    if (isset($_GET['machine_grup_id'])) {
        $stripper->offsetSet('machine_grup_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['machine_grup_id']));
    }
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['npk']));
    }
       $vParentId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $componentType = 'bootstrap';    
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }

    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_grup_id')) {
        $vMachineGrupId = $stripper->offsetGet('machine_grup_id')->getFilterValue();
    }
    if ($stripper->offsetExists('machine_id')) {
        $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    }
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    }    
    if($stripper->offsetExists('id')) {
        $vParentId = $stripper->offsetGet('id')->getFilterValue();
    }
    
    $resDataGrid = $BLL->FillUsersFirmMachinesNpk(array(
        'pk' => $pk,
        'language_code' => $vLanguageCode,
        'machine_grup_id' => $vMachineGrupId,
        'machine_id' => $vMachineId,
        'network_key' => $vNetworkKey,
    ));

    /*
     * $resTotalRowCount = $BLL->fillUsersFirmMachinesNpkRtc(array(    
        'pk' => $pk,
        'machine_grup_id' => $vMachineGrupId,
        'machine_id' => $vMachineId,
        'network_key' => $vNetworkKey,
    ));
        */
    
    
    $menus = array();
    //$menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
     if ($componentType == 'bootstrap') {
        $menus = array();
        foreach ($resDataGrid as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "machine_id" => $menu["machine_id"],
                "manufacturer_name" => html_entity_decode($menu["manufacturer_name"]),
                "machine_tool_grup_names" => html_entity_decode($menu["machine_tool_grup_names"]),
                "machine_tool_names" => html_entity_decode($menu["machine_tool_names"]),
                "model" => html_entity_decode($menu["model"]),
                "model_year" => $menu["model_year"],
                "series" => html_entity_decode($menu["series"]),
                "firm_id" => $menu["firm_id"],
                "picture" => $menu["picture"],
                "total" => $menu["total"],
                "attributes" => array("notroot" => true,
                    ) 
                
            );
        }
    } 

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($menus));
});
  
/**
 *  * Okan CIRAN
 * @since 20-05-2016
 */
$app->get("/pkFillAllCompanyMachineLists_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
   $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillAllCompanyMachineLists_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vNpk = NULL ;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
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
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, $app, $_GET['filterRules']));
    }
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
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

    $resDataGrid = $BLL->fillAllCompanyMachineLists(array(
        'language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'network_key' => $vNpk,
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillAllCompanyMachineListsRtc(array(
        'language_code' => $vLanguageCode,        
        'network_key' => $vNpk,
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));
    $counts=0;
  
    $menu = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                
                "firm_name" => html_entity_decode($menu["firm_name"]),
                "firm_name_eng" => html_entity_decode($menu["firm_name_eng"]),
                "machine_id" => $menu["machine_id"],
                "manufacturer_name" => html_entity_decode($menu["manufacturer_name"]),
                "machine_tool_grup_names" => html_entity_decode($menu["machine_tool_grup_name"]),
                "machine_tool_names" => html_entity_decode($menu["machine_tool_name"]),
                "model" => html_entity_decode($menu["model"]),
                "model_year" => $menu["model_year"],                
                "picture" =>  $menu["picture"],
                "total" => $menu["total"],
                "attributes" => array("notroot" => true),
                   
            );
        }
       $counts = $resTotalRowCount[0]['count'];
      } ELSE  $menus = array();       

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $menus;
    $app->response()->body(json_encode($resultArray));
});
 
/**
 *  * Okan CIRAN
 * @since 22-08-2016
 */
$app->get("/pkFillConsCompanyMachineLists_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
   $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillConsCompanyMachineLists_infoFirmMachineTool" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
          
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

    $resDataGrid = $BLL->fillConsCompanyMachineLists(array(        
        'page' => $vPage,
        'url' => $_GET['url'],
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,        
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));
  
    $resTotalRowCount = $BLL->fillConsCompanyMachineListsRtc(array(        
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));
    $counts=0;
  
    $menu = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "firm_id" => $menu["firm_id"],
                
                "firm_name" => html_entity_decode($menu["firm_name"]),
                "firm_name_short" => html_entity_decode($menu["firm_name_short"]),
                "machine_tool_name_eng" => html_entity_decode($menu["machine_tool_name_eng"]),                 
                "machine_id" => $menu["sys_machine_tool_id"],
                "machine_tool_name" => html_entity_decode($menu["machine_tool_name"]),
                "machine_tool_name_eng" => html_entity_decode($menu["machine_tool_name_eng"]),
                "total" => $menu["total"],
                "machine_tool_grup_id" => $menu["machine_tool_grup_id"],
                "machine_tool_grup_name" => html_entity_decode($menu["machine_tool_grup_name"]), 
                "profile_public" =>  $menu["profile_public"],
                "state_profile_public" => html_entity_decode($menu["state_profile_public"]),
                "availability_id" => $menu["availability_id"],
                "state_availability" => html_entity_decode($menu["state_availability"]),
                "ownership_id" => $menu["ownership_id"],
                "state_ownership" => html_entity_decode($menu["state_ownership"]),
                "state_active" => html_entity_decode($menu["state_active"]), 
                "attributes" => array("active" => $menu["active"],   ),
                   
            );
        }
       $counts = $resTotalRowCount[0]['count'];
      } ELSE { $menus = array(); }   

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $menus;
    $app->response()->body(json_encode($resultArray));
});


 /**x
 *  * Okan CIRAN
 * @since 22-08-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_infoFirmMachineTool/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmMachineToolBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkUpdateMakeActiveOrPassive_infoFirmMachineTool" end point, X-Public variable not found');
    }
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
