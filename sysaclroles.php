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


$pdo = new PDO('pgsql:dbname=ecoman_01_10;host=88.249.18.205;user=postgres;password=1q2w3e4r');

\Slim\Route::setDefaultConditions(array(
    'firstName' => '[a-zA-Z]{3,}',
    'page' => '[0-9]{1,}'
));



/**
 *  * Okan CIRAN
 * @since 07-01-2016
 */
$app->get("/pkFillComboBoxMainRoles_sysAclRoles/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

    //print_r('--****************get parent--' );  
    $resCombobox = $BLL->fillComboBoxMainRoles();
    //print_r($resDataMenu);


    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => $flow["name"],
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true, "active" => $flow["active"], "deleted" => $flow["deleted"]),
        );
    }
    //   print_r($flows);

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 07-01-2016
 * rest servislere eklendi
 */
$app->get("/pkFillFullRolesDdList_sysAclRoles/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillFullRolesDdList_sysAclRoles" end point, X-Public variable not found');    
  
    $resCombobox = $BLL->fillFullRolesDdList();
    
    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["name_tr"]),
            // "imageSrc"=>$flow["logo"],             
            "attributes" => array(                 
                    "active" => $flow["active"],
                   
            ),
        );
    }

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 07-01-2016
 */
$app->get("/pkFillGrid_sysAclRoles/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');


    $vSearchName = '';
    $stripper->offsetSet(array_search($_GET['url'], $_GET), new \Utill\Strip\Chain\StripChainer($app, $_GET['url'], array(
        \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
    //    \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_ADVANCED,
    )));

    if (isset($_GET['search_name']) && $_GET['search_name'] != "") {
        //    print_r($_GET['search_name']);
        $stripper->offsetSet(array_search($_GET['search_name'], $_GET), new \Utill\Strip\Chain\StripChainer($app, $_GET['search_name'], array(
            \Services\Filter\FilterServiceNames::FILTER_DEFAULT,
            \Services\Filter\FilterServiceNames::FILTER_PARENTHESES,
            //  \Services\Filter\FilterServiceNames::FILTER_TONULL,
            \Services\Filter\FilterServiceNames::FILTER_LOWER_CASE,
            \Services\Filter\FilterServiceNames::FILTER_HEXADECIMAL_ADVANCED,
   //         \Services\Filter\FilterServiceNames::FILTER_HTML_TAGS_ADVANCED,
            \Services\Filter\FilterServiceNames::FILTER_SQL_RESERVEDWORDS,
            \Services\Filter\FilterServiceNames::FILTER_PREG_REPLACE,
        )));
    }

    $stripper->strip();
    $vUrl = $stripper->offsetGet(array_search($_GET['url'], $_GET))->getFilterValue();
    if (isset($_GET['search_name']) && $_GET['search_name'] != "") {
        $vSearchName = $stripper->offsetGet(array_search($_GET['search_name'], $_GET))->getFilterValue();
        print_r('>>>>'.$vSearchName.'<<<');
    }

    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];


    $resDataGrid = $BLL->fillGrid(array('page' => $_GET['page'],
        'rows' => $_GET['rows'],
        'sort' => $_GET['sort'],
        'order' => $_GET['order'],
        'search_name' => $vSearchName,
        'pk' => $pk));
    //print_r($resDataGrid);

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
            "icon_class" => $flow["icon_class"],
            "create_date" => $flow["create_date"],
            "icon_class" => $flow["icon_class"],
            "create_date" => $flow["create_date"],
            "start_date" => $flow["start_date"],
            "end_date" => $flow["end_date"],
            "parent" => $flow["parent_id"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "description" => $flow["description"],
            "user_id" => $flow["user_id"],
            "username" => $flow["username"],
            "root_parent" => $flow["root_parent"],
            "root" => $flow["inherited"],
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
 * @since 07-01-2016
 */ 
$app->get("/pkInsert_sysAclRoles/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysAclRoles" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vName = NULL;
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name']));
    }
    $vNameTr = '';
    if (isset($_GET['name_tr'])) {
         $stripper->offsetSet('name_tr',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name_tr']));
    }
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }
    $vParent = NULL;
    if (isset($_GET['parent'])) {
         $stripper->offsetSet('parent',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent']));
    }
    $vResourceId = NULL;
    if (isset($_GET['resource_id'])) {
         $stripper->offsetSet('resource_id',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['resource_id']));
    }
    
    $vInherited = NULL;
    if (isset($_GET['inherited_id'])) {
         $stripper->offsetSet('inherited_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['inherited_id']));
    }
   
    $stripper->strip();
    if($stripper->offsetExists('name')) $vName = $stripper->offsetGet('name')->getFilterValue();
    if($stripper->offsetExists('name_tr')) $vNameTr = $stripper->offsetGet('name_tr')->getFilterValue();
    if($stripper->offsetExists('description')) $vDescription = $stripper->offsetGet('description')->getFilterValue();
    if($stripper->offsetExists('parent')) $vParent = $stripper->offsetGet('parent')->getFilterValue();
    if($stripper->offsetExists('resource_id')) $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
    if($stripper->offsetExists('inherited_id')) $vInherited = $stripper->offsetGet('inherited_id')->getFilterValue();
      
    $resDataInsert = $BLL->insert(array(
            'name' => $vName,      
            'name_tr' => $vNameTr,   
            'parent_id' => $vParent,    
            'resource_id' => $vResourceId,    
            'inherited' => $vInherited,  
            'description' => $vDescription,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);
/**
 *  * Okan CIRAN
 * @since 07-01-2016
 */ 
$app->get("/pkUpdate_sysAclRoles/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysAclRoles" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vId = 0;
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
    $vNameTr = '';
    if (isset($_GET['name_tr'])) {
         $stripper->offsetSet('name_tr',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name_tr']));
    }
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }
    $vParent = NULL;
    if (isset($_GET['parent'])) {
         $stripper->offsetSet('parent',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent']));
    }
    $vResourceId = NULL;
    if (isset($_GET['resource_id'])) {
         $stripper->offsetSet('resource_id',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['resource_id']));
    }
    $vInherited = NULL;
    if (isset($_GET['inherited_id'])) {
         $stripper->offsetSet('inherited_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['inherited_id']));
    }
   
    $stripper->strip();
    if($stripper->offsetExists('id')) $vId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('name')) $vName = $stripper->offsetGet('name')->getFilterValue();
    if($stripper->offsetExists('name_tr')) $vNameTr = $stripper->offsetGet('name_tr')->getFilterValue();
    if($stripper->offsetExists('description')) $vDescription = $stripper->offsetGet('description')->getFilterValue();
    if($stripper->offsetExists('parent')) $vParent = $stripper->offsetGet('parent')->getFilterValue();
    if($stripper->offsetExists('resource_id')) $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
    if($stripper->offsetExists('inherited_id')) $vInherited = $stripper->offsetGet('inherited_id')->getFilterValue();
      
    $resDataInsert = $BLL->update(array(
            'id' => $vId,
            'name' => $vName,      
            'name_tr' => $vNameTr,   
            'parent_id' => $vParent, 
            'resource_id' => $vResourceId,    
            'inherited' => $vInherited,  
            'description' => $vDescription,
            'pk' => $pk));
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);

/**
 *  * Okan CIRAN
 * @since 13-01-2016
 */
$app->get("/pkUpdateChild_sysAclRoles/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];

    $resDataUpdate = $BLL->updateChild(array(
        'active' => $_GET['active'],
        'user_id' => $_GET['user_id'],
        'id' => $_GET['id'],
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
$app->get("/pkGetAll_sysAclRoles/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');


    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public'];
    //print_r($resDataMenu);


    $resDataGrid = $BLL->getAll();
    //print_r($resDataGrid);

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
            "icon_class" => $flow["icon_class"],
            "create_date" => $flow["create_date"],
            "icon_class" => $flow["icon_class"],
            "create_date" => $flow["create_date"],
            "start_date" => $flow["start_date"],
            "end_date" => $flow["end_date"],
            "parent" => $flow["parent"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "description" => $flow["description"],
            "user_id" => $flow["user_id"],
            "username" => $flow["username"],
            "root_parent" => $flow["root_parent"],
            "root" => $flow["root"],
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
 * @since 07-01-2016
 */
$app->get("/pkDelete_sysAclRoles/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');   
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
 * @since 25-01-2016
 */
$app->get("/pkFillComboBoxRoles_sysAclRoles/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

 
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
 
    $resCombobox = $BLL->FillComboBoxRoles();
    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
 
    if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => $menu["name"],
                "state" => $menu["state_type"], //   'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"]),
            );
        }
    } else if ($componentType == 'ddslick') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["name"],
                "value" =>intval($menu["id"]),
                "selected" => false,
                "description" => $menu["name_tr"],
              //  "imageSrc" => ""
            );
        }
    }

    $app->response()->header("Content-Type", "application/json");


    $app->response()->body(json_encode($menus));

    //$app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 13-08-2016
 */
$app->get("/pkFillRolesTree_sysAclRoles/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');    
    $vParentId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $vResourceId = NULL;
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
    if($stripper->offsetExists('resource_id')) $vResourceId = $stripper->offsetGet('resource_id')->getFilterValue();
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();

    $resCombobox = $BLL->FillRolesTree(array('id' => $vParentId,  
                                            'resource_id' => $vResourceId, 
                                            'search' => $vsearch,
                                                                ));
 
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "text" => html_entity_decode($flow["name"]),
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,           
           // "icon_class"=>$flow["icon_class"], 
            "attributes" => array("active" => $flow["active"] ,
                                  "resource_ids"=>$flow["resource_ids"],
                                  "resource_names"=>$flow["resource_names"],
                                  "name_tr" => html_entity_decode($flow["name_tr"])
                 ),
        );
    }

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 13-08-2016
 */
$app->get("/pkFillRolesPropertiesList_sysAclRoles/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillRolesPropertiesList_sysAclRoles" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];

    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['name']));
    }     
    $vNameTr = NULL;
    if (isset($_GET['name_tr'])) {
        $stripper->offsetSet('name_tr', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['name_tr']));
    }     
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description']));
    }
    $vParentName = NULL;
    if (isset($_GET['parent_name'])) {
        $stripper->offsetSet('parent_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['parent_name']));
    }
    $vResourceName = NULL;
    if (isset($_GET['resource_name'])) {
        $stripper->offsetSet('resource_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['resource_name']));
    }
    $vInheritedName = NULL;
    if (isset($_GET['inherited_name'])) {
        $stripper->offsetSet('inherited_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['inherited_name']));
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
    if ($stripper->offsetExists('name_tr')) {
        $vNameTr = $stripper->offsetGet('name_tr')->getFilterValue();
    }
    if ($stripper->offsetExists('parent_name')) {
        $vParentName = $stripper->offsetGet('parent_name')->getFilterValue();
    } 
    if ($stripper->offsetExists('resource_name')) {
        $vResourceName = $stripper->offsetGet('resource_name')->getFilterValue();
    }
    if ($stripper->offsetExists('inherited_name')) {
        $vInheritedName = $stripper->offsetGet('inherited_name')->getFilterValue();
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
    
    $resDataGrid = $BLL->fillRolesPropertiesList(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'name' => $vName,
        'name_tr' => $vNameTr,
        'inherited_name' => $vInheritedName,        
        'parent_name' => $vParentName,
        'resource_name' => $vResourceName,
        'description' => $vDescription,       
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillRolesPropertiesListRtc(array(
        'name' => $vName,
        'name_tr' => $vNameTr,
        'inherited_name' => $vInheritedName,   
        'resource_name' => $vResourceName,
        'parent_name' => $vParentName,
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
            "name_tr" => html_entity_decode($flow["name_tr"]),
            "parent_id" => $flow["parent_id"],
            "parent_name" => html_entity_decode($flow["parent_name"]),
            "resource_ids" =>  $flow["resource_ids"],
            //"resource_name" => NULL,
            "resource_json" => html_entity_decode($flow["resource_json"]),
            "inherited" => $flow["inherited"],
            "inherited_name" => html_entity_decode($flow["inherited_name"]),
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
 * @since 13-08-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysAclRoles/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');
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
 * @since 09-08-2016
 
 */
$app->get("/pkFillConsultantRolesDdlist_sysAclRoles/", function () use ($app ) {   
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillConsultantRolesDdlist_sysAclRoles" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public']; 
    $resCombobox = $BLL->fillConsultantRolesDdlist();
 
    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["name_tr"]),
            // "imageSrc"=>$flow["logo"],             
            "attributes" => array(                 
                    "active" => $flow["active"],
                   
            ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 09-08-2016
 
 */
$app->get("/pkFillRolesDdlist_sysAclRoles/", function () use ($app ) {   
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillRolesDdlist_sysAclRoles" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public']; 
    $resCombobox = $BLL->fillRolesDdlist();
 
    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["name_tr"]),
            // "imageSrc"=>$flow["logo"],             
            "attributes" => array(                 
                    "active" => $flow["active"],
                   
            ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});

 
/**
 *  * Okan CIRAN
 * @since 31-08-2016
 
 */
$app->get("/pkFillClusterRolesDdlist_sysAclRoles/", function () use ($app ) {   
    $BLL = $app->getBLLManager()->get('sysAclRolesBLL');

    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillClusterRolesDdlist_sysAclRoles" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public']; 
    $resCombobox = $BLL->fillClusterRolesDdlist();
 
    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["name_tr"]),
            // "imageSrc"=>$flow["logo"],             
            "attributes" => array(                 
                    "active" => $flow["active"],
                   
            ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});


$app->run();
