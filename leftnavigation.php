<?php
// test commit for branch slim2
require 'vendor/autoload.php';


use \Services\Filter\Helper\FilterFactoryNames as stripChainers;

/*$app = new \Slim\Slim(array(
    'mode' => 'development',
    'debug' => true,
    'log.enabled' => true,
    ));*/

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
 *  * zeynel dağlı
 * @since 11-09-2014
 * rest servislere eklendi
 */
$app->get("/pkGetLeftMenu_leftnavigation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();        
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL'); 
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetLeftMenu_leftnavigation" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
    
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vParent = 0;
    if (isset($_GET['parent'])) {
        $stripper->offsetSet('parent', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['parent']));
    }
    $vM = NULL;
    if (isset($_GET['m'])) {
        $stripper->offsetSet('m', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['m']));
    }
    $vA = NULL;
    if (isset($_GET['a'])) {
        $stripper->offsetSet('a', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['a']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('parent')) 
        {$vParent = $stripper->offsetGet('parent')->getFilterValue(); }         
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue(); }  
    if ($stripper->offsetExists('m')) 
        {$vM = $stripper->offsetGet('m')->getFilterValue(); }     
    if ($stripper->offsetExists('a')) 
        {$vA = $stripper->offsetGet('a')->getFilterValue(); }     
    
    $resDataMenu = $BLL->pkGetLeftMenu(array('parent' => $vParent,
                                            'language_code' => $vLanguageCode,                                             
                                            'm' => $vM,
                                            'a' => $vA,
                                            'pk' => $pk ,
                                           ) ); 
    $menus = array();
    foreach ($resDataMenu as $menu){
        $menus[]  = array(
            "id" => $menu["id"],
            "menu_name" => $menu["menu_name"],
             "language_id" => $menu["language_id"],
             "menu_name_eng" => $menu["menu_name_eng"],
             "url" => $menu["url"],
             "parent" => $menu["parent"],
             "icon_class" => $menu["icon_class"],
             "page_state" => $menu["page_state"],
             "collapse" => $menu["collapse"],
             "active" => $menu["active"],
             "deleted" => $menu["deleted"],
             "state" => $menu["state"],
             "warning" => $menu["warning"],
             "warning_type" => $menu["warning_type"],
             "hint" => $menu["hint"],
             "z_index" => $menu["z_index"],
             "language_parent_id" => $menu["language_parent_id"],
             "hint_eng" => $menu["hint_eng"],
             "warning_class" => $menu["warning_class"],
             "acl_type" => $menu["acl_type"],
             "language_code" => $menu["language_code"],
             "active_control" => $menu["active_control"],
             "menu_types_id" => $menu["menu_types_id"],             
        );
    }
    
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($menus));
  
});

  
 
/**
 *  * Okan CIRAN
 * @since 28-03-2016
 * bu servis  kullanılmıyor qwerty
 */
$app->get("/pkFillGridForAdmin_leftnavigation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL');
 
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkGetConsConfirmationProcessDetails_sysOsbConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vRoleId = 1;
    if (isset($_GET['role_id'])) {
        $stripper->offsetSet('role_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['role_id']));
    }
    
    $vPage = 1;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['page']));
    }
    $vRows = 10;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rows']));
    }
    
    $vfilterRules = null;
    if(isset($_GET['filterRules'])) {
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1 ,
                                                $app,
                                                $_GET['filterRules']));
    }
    $vSort = null;
    if(isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sort']));
    }
    $vOrder = null;
    if(isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['order']));
    }
   
  
     $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('role_id')) $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();
    if($stripper->offsetExists('page')) $vPage = $stripper->offsetGet('page')->getFilterValue();
    if($stripper->offsetExists('rows')) $vRows = $stripper->offsetGet('rows')->getFilterValue();
    if($stripper->offsetExists('sort')) $vSort = $stripper->offsetGet('sort')->getFilterValue();
    if($stripper->offsetExists('order')) $vOrder = $stripper->offsetGet('order')->getFilterValue();
    if($stripper->offsetExists('filterRules')) $vfilterRules = $stripper->offsetGet('filterRules')->getFilterValue();
    
 

    $resDataGrid = $BLL->fillGridForAdmin(array('language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder, 
        'role_id' => $vRoleId,
        'pk' => $pk,        
        'filterRules' => $vfilterRules));    
 
    $resTotalRowCount = $BLL->fillGridForAdminRtc(array('pk' => $pk,
                                                'language_code' => $vLanguageCode,
                                                'role_id' => $vRoleId,
                                                'filterRules' => $vfilterRules   ));
 
                                            
    $flows = array();
    foreach ($resDataGrid['resultSet'] as $flow) {
        $flows[] = array(
            "id" => intval($flow["id"]),
            "menu_name" => $flow["menu_name"],
            "menu_name_eng" => $flow["menu_name_eng"],
            "url" => $flow["url"],
            "parent" => intval($flow["parent"]),
            "icon_class" => $flow["icon_class"],            
            
            "page_state" => $flow["page_state"], 
            "collapse" => intval($flow["collapse"]), 
            "deleted" => intval($flow["deleted"]), 
            "state_deleted" => $flow["state_deleted"], 
            "active" => intval($flow["active"]), 
            "state_active" => $flow["state_active"], 
            "warning" => $flow["warning"], 
            "warning_type" => $flow["warning_type"], 
            "warning_class" => $flow["warning_class"], 
            "hint" => $flow["hint"], 
            "hint_eng" => $flow["hint_eng"], 
            "z_index" => intval($flow["z_index"]), 
            "language_parent_id" => intval($flow["language_parent_id"]), 
            
            "active_control" => intval($flow["active_control"]), 
            "role_id" => intval($flow["role_id"]), 
            "role_name" => $flow["role_name"],  
            "attributes" => array("notroot" => true, "active" => intval($flow["active"]),"active_control" => intval($flow["active_control"])),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = 2; //$resTotalRowCount['resultSet'][0]['count'];
    $resultArray['rows'] = $flows;

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resultArray));

});

 
 
/**
 *  * Okan CIRAN
 * @since 17-03-2016 
 * rest servislere eklendi
 */
$app->get("/pkFillForAdminTree_leftnavigation/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL');
    
    $headerParams = $app->request()->headers();
    
    $componentType = 'bootstrap'; // 'easyui'    
    if (isset($_GET['component_type'])) {
        $componentType = $_GET['component_type']; 
    }
    
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillForAdminTree_leftnavigation" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

     $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vRoleId = 1;
    if (isset($_GET['role_id'])) {
        $stripper->offsetSet('role_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['role_id']));
    }
    $vParentId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $vMenuTypesId = NULL;
    if (isset($_GET['menu_types_id'])) {
        $stripper->offsetSet('menu_types_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['menu_types_id']));
    } 
    
     $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('role_id')) $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();
     if($stripper->offsetExists('menu_types_id')) $vMenuTypesId = $stripper->offsetGet('menu_types_id')->getFilterValue();
    
   
   
    $resDataGrid = $BLL->fillForAdminTree(array(
                                            'language_code' => $vLanguageCode,                                          
                                            'role_id' => $vRoleId,
                                            'menu_types_id' => $vMenuTypesId,
                                            'parent_id' => $vParentId,
                                            'pk' => $pk,
        
                                                    ));
                                                    
                                                  
 
                                                              
    
        $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {      
        foreach ($resDataGrid['resultSet']  as $flow) {    
            $flows[] = array(
                "id" => $flow["id"],
                "text" =>  $flow["menu_name"],
                "state" => $flow["state_type"],
                "checked" => false,
                "attributes" => array ("notroot"=>true,
                    "text_eng"=>$flow["menu_name_eng"],
                    "active" => $flow["active"],
                    "url"=>$flow["url"],
                    "icon_class"=>$flow["icon_class"],
                    "menu_types_id"=>$flow["menu_types_id"],
                    "role_id"=>$flow["role_id"], 
                    
                    
                    ),               
                
            );
        }        
    }
   
      
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
  //  $resultArray['total'] = $resTotalRowCount[0]['count'];
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

 
/**x
 *  * Okan CIRAN
 * @since 25-02-2016
 * rest servislere eklendi
 */
$app->get("/pkDelete_leftnavigation/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL');
 
   
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
 * rest servislere eklendi
 */
$app->get("/pkUpdateMakeActiveOrPassive_leftnavigation/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL');
 
   
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

/**x
 *  * Okan CIRAN
 * @since 29-03-2016
* rest servislere eklendi
 */
$app->get("/pkUpdate_leftnavigation/", function () use ($app ) {
    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL');
   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
   
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
    
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }   
    
    $vRoleId = NULL;
    if (isset($_GET['role_id'])) {
         $stripper->offsetSet('role_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['role_id']));
    }  
    $vMenuTypesId = NULL;
    if (isset($_GET['menu_types_id'])) {
         $stripper->offsetSet('menu_types_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['menu_types_id']));
    }   
      
    $vIconClass = NULL;
    if (isset($_GET['icon_class'])) {
        $stripper->offsetSet('icon_class', $stripChainerFactory->get(stripChainers::FILTER_DEFAULT,
                                                $app,
                                                $_GET['icon_class']));
    } 
    $vUrl = '#';
    if (isset($_GET['urlx'])) {
        $stripper->offsetSet('urlx', $stripChainerFactory->get(stripChainers::FILTER_DEFAULT,
                                                $app,
                                                $_GET['urlx']));
    } 
  /*  $vMenuName = "";
    if (isset($_GET['menu_name'])) {
        $stripper->offsetSet('menu_name', $stripChainerFactory->get(stripChainers::FILTER_TRIM,
                                                $app,
                                                $_GET['menu_name']));
    } 
    
    $vMenuNameEng = NULL;
    if (isset($_GET['menu_name_eng'])) {
        $stripper->offsetSet('menu_name_eng', $stripChainerFactory->get(stripChainers::FILTER_TRIM,
                                                $app,
                                                $_GET['menu_name_eng']));
    } 
    */
     if ($stripper->offsetExists('menu_types_id')) {
        $vMenuTypesId = $stripper->offsetGet('menu_types_id')->getFilterValue();
    }     
    if ($stripper->offsetExists('role_id')) {
        $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('icon_class')) {
        $vIconClass = $stripper->offsetGet('icon_class')->getFilterValue();
    }
    
    if ($stripper->offsetExists('urlx')) {
        $vUrl = $stripper->offsetGet('urlx')->getFilterValue();
    }
   // if ($stripper->offsetExists('menu_name')) {
        //$vMenuName = $stripper->offsetGet('menu_name')->getFilterValue();
          $vMenuName = $_GET['menu_name'];
   // }
    //if ($stripper->offsetExists('menu_name_eng')) {
        //$vMenuNameEng = $stripper->offsetGet('menu_name_eng')->getFilterValue();
        $vMenuNameEng = $_GET['menu_name_eng'];
    //}

    $resData = $BLL->update(array(  
            'id' => $vId , 
            'role_id' => $vRoleId ,          
            'menu_types_id' => $vMenuTypesId ,
            'language_code' => $vLanguageCode,                 
            'icon_class' => $vIconClass , 
            'url' => $vUrl , 
            'menu_name' => $vMenuName ,
            'menu_name_eng' => $vMenuNameEng ,            
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json");
 
    $app->response()->body(json_encode($resData));
}
); 

/**x
 *  * Okan CIRAN
 * @since 29-03-2016
 * rest servislere eklendi
 */
$app->get("/pkInsert_leftnavigation/", function () use ($app ) {
    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysNavigationLeftBLL');
   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
   
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
    
    $vRoleId = NULL;
    if (isset($_GET['role_id'])) {
         $stripper->offsetSet('role_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['role_id']));
    } 
    $vParent = 0;
    if (isset($_GET['parent'])) {
         $stripper->offsetSet('parent',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent']));
    } 
    $vZindex = 0;
    if (isset($_GET['z_index'])) {
         $stripper->offsetSet('z_index',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['z_index']));
    } 
    $vMenuTypesId= 0;
    if (isset($_GET['menu_types_id'])) {
         $stripper->offsetSet('menu_types_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['menu_types_id']));
    }  
    $vIconClass = NULL;
    if (isset($_GET['icon_class'])) {
        $stripper->offsetSet('icon_class', $stripChainerFactory->get(stripChainers::FILTER_DEFAULT,
                                                $app,
                                                $_GET['icon_class']));
    } 
    $vUrl = '#';
    if (isset($_GET['urlx'])) {
        $stripper->offsetSet('urlx', $stripChainerFactory->get(stripChainers::FILTER_DEFAULT,
                                                $app,
                                                $_GET['urlx']));
    } 
    
    if ($stripper->offsetExists('menu_types_id')) {
        $vMenuTypesId = $stripper->offsetGet('menu_types_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('role_id')) {
        $vRoleId = $stripper->offsetGet('role_id')->getFilterValue();
    }
    if ($stripper->offsetExists('parent')) {
        $vParent = $stripper->offsetGet('parent')->getFilterValue();
    }
    if ($stripper->offsetExists('z_index')) {
        $vZindex = $stripper->offsetGet('z_index')->getFilterValue();
    }    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('icon_class')) {
        $vIconClass = $stripper->offsetGet('icon_class')->getFilterValue();
    }
    
    if ($stripper->offsetExists('urlx')) {
        $vUrl = $stripper->offsetGet('urlx')->getFilterValue();
    }
   // if ($stripper->offsetExists('menu_name')) {
        //$vMenuName = $stripper->offsetGet('menu_name')->getFilterValue();
          $vMenuName = $_GET['menu_name'];
   // }
    //if ($stripper->offsetExists('menu_name_eng')) {
        //$vMenuNameEng = $stripper->offsetGet('menu_name_eng')->getFilterValue();
        $vMenuNameEng = $_GET['menu_name_eng'];
    //}

    $resData = $BLL->insert(array(  
            'language_code' => $vLanguageCode, 
            'menu_type' => $vRoleId ,
            'menu_types_id' => $vMenuTypesId ,        
            'parent'=> $vParent, 
            'icon_class' => $vIconClass , 
            'url' => $vUrl , 
            'menu_name' => $vMenuName ,
            'menu_name_eng' => $vMenuNameEng , 
            'z_index' => $vZindex ,
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json");
 
    $app->response()->body(json_encode($resData));
}
); 


$app->run();