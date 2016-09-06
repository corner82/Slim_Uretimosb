<?php

// test commit for branch slim2
require 'vendor/autoload.php';




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


$pdo = new PDO('pgsql:dbname=ecoman_01_10;host=88.249.18.205;user=postgres;password=1q2w3e4r');

\Slim\Route::setDefaultConditions(array(
    'firstName' => '[a-zA-Z]{3,}',
    'page' => '[0-9]{1,}'
));


 
/**
 *  * Okan CIRAN
 * @since 18-01-2016
 */
$app->get("/pkFillRrpMap_sysAclRrpMap/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysAclRrpMapBLL');

    $resCombobox = $BLL->fillRrpMap();

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "text" => $flow["name"],
            "state" => $flow["state_type"],
            "checked" => false,
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
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
 * @since 18-01-2016
 */
$app->get("/pkFillGrid_sysAclRrpMap/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');


    $vSearchName = '';
    $stripper->offsetSet(array_search($_GET['url'], $_GET), new \Utill\Strip\Chain\StripChainer($app, $_GET['url'], array(
        \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
    //    \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_ADVANCED,
    )));

    if (isset($_GET['search_name']) && $_GET['search_name'] != "") { 
    /*    $stripper->offsetSet(array_search($_GET['search_name'], $_GET), new \Utill\Strip\Chain\StripChainer($app, $_GET['search_name'], array(
            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,
            \Services\Filter\FilterServiceNames::FILTER_PARENTHESES,
            //  \Services\Filter\FilterServiceNames::FILTER_TONULL,
            \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
            \Services\Filter\FilterServiceNames::FILTER_HEXADECIMAL_ADVANCED,
   //         \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_ADVANCED,
            \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
            \Services\Filter\FilterServiceNames::FILTER_PREG_REPLACE,
        )));
   */ }
/*
    $stripper->strip();
    $vUrl = $stripper->offsetGet(array_search($_GET['url'], $_GET))->getFilterValue();
    if (isset($_GET['search_name']) && $_GET['search_name'] != "") {
        $vSearchName = $stripper->offsetGet(array_search($_GET['search_name'], $_GET))->getFilterValue();
        print_r('>>>>'.$vSearchName.'<<<');
    }
*/
    $BLL = $app->getBLLManager()->get('sysAclRrpMapBLL');

    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];


    $resDataGrid = $BLL->fillGrid(array('page' => $_GET['page'],
        'rows' => $_GET['rows'],
        'sort' => $_GET['sort'],
        'order' => $_GET['order'],
        'search_name' => $_GET['search_name'],     
        'pk' => $pk));
  

    /**
     * BLL fillGridRowTotalCount örneği test edildi
     * datagrid için total row count döndürüyor
     * Okan CIRAN
     */
    $resTotalRowCount = $BLL->fillGridRowTotalCount(array('search_name' => $vSearchName));

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
                
            "id" => $flow["id"],
            "name" => $flow["name"],
            "role_id" => $flow["role_id"],
            "role_name" => $flow["role_name"],
            "resource_id" => $flow["resource_id"],
            "resource_name" => $flow["resource_name"],
            "privilege_id" => $flow["privilege_id"],
            "privilege_name" => $flow["privilege_name"],
            "create_date" => $flow["create_date"],
            "start_date" => $flow["start_date"],
            "state_deleted" => $flow["state_deleted"],
            "end_date" => $flow["end_date"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "description" => $flow["description"],
            "user_id" => $flow["user_id"],
            "username" => $flow["username"],
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
 * @since 18-01-2016
 */
$app->get("/pkInsert_sysAclRrpMap/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('sysAclRrpMapBLL');

    $vRole_id = $_GET['role_id'];
    $vResource_id = $_GET['resource_id'];
    $vPrivilege_id = $_GET['privilege_id'];
    $vUser_id = $_GET['user_id'];
    $vDescription = $_GET['description']; 
 
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];    
 

    $resDataInsert = $BLL->insert(array('name' => $vName,
        'role_id' => $vRole_id,
        'resource_id' => $vResource_id,
        'privilege_id' => $vPrivilege_id,     
        'user_id' => $vUserId,
        'description' => $vDescription,      
        'pk' => $vPk));
    // print_r($resDataInsert);    
 

    $app->response()->header("Content-Type", "application/json");
 

        /* $app->contentType('application/json');
          $app->halt(302, '{"error":"Something went wrong"}');
          $app->stop(); */

        $app->response()->body(json_encode($resDataInsert));
    }
 
);
/**
 *  * Okan CIRAN
 * @since 18-01-2016
 */
$app->get("/pkUpdate_sysAclRrpMap/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysAclRrpMapBLL');
 
    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];

    $resDataUpdate = $BLL->update($_GET['id'], array('name' => $_GET['name'],
        'role_id' => $_GET['role_id'],
        'resource_id' => $_GET['resource_id'],
        'privilege_id' => $_GET['privilege_id'],
        'user_id' => $_GET['user_id'],
        'description' => $_GET['description'],        
        'id',$_GET['id'],
        'pk' => $pk));
    
    //print_r($resDataGrid);    

    $app->response()->header("Content-Type", "application/json");



    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataUpdate));
});

 
/**
 *  * Okan CIRAN
 * @since 11-01-2016
 */
$app->get("/pkGetAll_sysAclRrpMap/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('sysAclRrpMapBLL');


    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];

    $resDataGrid = $BLL->getAll();

    /**
     * BLL fillGridRowTotalCount örneği test edildi
     * datagrid için total row count döndürüyor
     * Okan CIRAN
     */
    $resTotalRowCount = $BLL->fillGridRowTotalCount();

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "name" => $flow["name"],
            "role_id" => $flow["role_id"],
            "role_name" => $flow["role_name"],
            "resource_id" => $flow["resource_id"],
            "resource_name" => $flow["resource_name"],
            "privilege_id" => $flow["privilege_id"],
            "privilege_name" => $flow["privilege_name"],
            "create_date" => $flow["create_date"],
            "start_date" => $flow["start_date"],
            "state_deleted" => $flow["state_deleted"],
            "end_date" => $flow["end_date"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "description" => $flow["description"],
            "user_id" => $flow["user_id"],
            "username" => $flow["username"],
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
 * @since 18-01-2016
 */
$app->get("/pkDelete_sysAclRrpMap/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysAclRrpMapBLL');

    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];

    $resDataUpdate = $BLL->delete($_GET['id'], array(
        'user_id' => $_GET['user_id'],
        'pk' => $pk));

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataUpdate));
});

$app->run();
