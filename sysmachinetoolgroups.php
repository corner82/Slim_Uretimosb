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
 * @since 15-02-2016
 */
$app->get("/pkFillMachineToolGroups_sysMachineToolGroups/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }
     $vParentId = 0;
    if (isset($_GET['parent_id'])) {
        $stripper->offsetSet('parent_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent_id']));
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
     if (isset($_GET['machine'])) {
        $stripper->offsetSet('machine', 
                $stripChainerFactory->get(stripChainers::FILTER_ONLY_BOOLEAN_ALLOWED,
                        $app,
                        $_GET['machine']));
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
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('parent_id')) $vParentId = $stripper->offsetGet('parent_id')->getFilterValue();
    if($stripper->offsetExists('state')) $vState = $stripper->offsetGet('state')->getFilterValue();
    if($stripper->offsetExists('last_node')) $vLastNode = $stripper->offsetGet('last_node')->getFilterValue();
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();

    if (isset($_GET['parent_id'])) {
        $resCombobox = $BLL->fillMachineToolGroups(array('parent_id' => $vParentId,
                                                         'language_code' => $vLanguageCode, 
                                                         'state' => $vState,
                                                         'last_node' => $vLastNode,
                                                         'machine' => $vMachine,
                                                         'search' => $vsearch,
                                                                ));
    } else {
        $resCombobox = $BLL->fillMachineToolGroups(array('language_code' => $vLanguageCode));
    }

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => $flow["name"],
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
            "icon_class"=>$flow["icon_class"], 
            "attributes" => array("root" => $flow["root_type"], "active" => $flow["active"]
                ,"machine" => $flow["machine"],"last_node" => $flow["last_node"]),
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
 * @since 15-02-2016
 */
$app->get("/pkFillJustMachineToolGroups_sysMachineToolGroups/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
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
    $vsearch = null;
    if(isset($_GET['search'])) {
        $stripper->offsetSet('search', 
                $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                        $app,
                        $_GET['search']));
    }
    
    
    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('search')) $vsearch = $stripper->offsetGet('search')->getFilterValue();

    if (isset($_GET['id'])) {
        $resCombobox = $BLL->fillJustMachineToolGroups(array('parent_id' => $vParentId,
                                                         'language_code' => $vLanguageCode,                                                        
                                                         'search' => $vsearch,
                                                                ));
    } else {
        $resCombobox = $BLL->fillJustMachineToolGroups(array('language_code' => $vLanguageCode));
    }

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => $flow["name"],
            "state" => $flow["state_type"], //   'closed',
            "checked" => false,
            "icon_class"=>$flow["icon_class"], 
            "attributes" => array("root" => $flow["root_type"], "active" => $flow["active"]
                ,"machine" => $flow["machine"],"last_node" => $flow["last_node"]),
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
 * @since 29-02-2016
 */

$app->get("/pkFillMachineToolGroupsMachineProperties_sysMachineToolGroups/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }
     $vMachineId = 0;
    if (isset($_GET['machine_id'])) {
        $stripper->offsetSet('machine_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['machine_id']));
    }
    
    $stripper->strip();    
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('machine_id')) $vMachineId = $stripper->offsetGet('machine_id')->getFilterValue();
    
    if (isset($_GET['machine_id'])) {
        $resData = $BLL->fillMachineToolGroupsMachineProperties(array('machine_id' => $vMachineId,
                                                         'language_code' => $vLanguageCode, 
                                                        
                                                                ));
    } else {
        $resData = $BLL->fillMachineToolGroupsMachineProperties(array('language_code' => $vLanguageCode));
    }
     $flows = array();
    if (isset($resData['resultSet'][0]['machine_id'])) {      
        foreach ($resData['resultSet']  as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "machine_id" => $flow["machine_id"], 
                "machine_names" => $flow["machine_names"],                
                "property_names" => $flow["property_names"],
                "property_name_eng" => $flow["property_name_eng"],
                "property_value" => $flow["property_value"],
                "unit_id" => $flow["unit_id"],
                "unitcodes" => $flow["unitcodes"],             
                "attributes" => array("notroot" => true ),
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

    $app->response()->body(json_encode($resultArray));
    
});
 
/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkFillJustMachineToolGroupsBootstrap_sysMachineToolGroups/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];
     
  
    $componentType = 'bootstrap';
    
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
    $componentType = 'bootstrap';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }

    
     $stripper->strip();    
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();
    
    
    
    $resCombobox = $BLL->fillJustMachineToolGroupsBootstrap(array( 
                                                        'language_code' => $vLanguageCode,
                                                        'parent_id' => $vParentId,
        
                                                            ));
 
    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
     if ($componentType == 'bootstrap') {
        $menus = array();
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],       
                "text" => $menu["name"],
                "state" => $menu["state_type"],
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"] ,
                    "icon_class"=>$menu["icon_class"] ,"group_name_eng"=>$menu["group_name_eng"],
                    "machine"=>$menu["machine"] ,)
                
                                
                
            );
        }
    } else if ($componentType == 'ddslick') {
        
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["name"],
                "value" =>  intval($menu["id"]),
                "selected" => false,
                "description" => $menu["name_eng"],
             //   "imageSrc" => ""
            );
        }
    }
     

    $app->response()->header("Content-Type", "application/json");
 
    $app->response()->body(json_encode($menus));
});
 

/**
 *  * Okan CIRAN
 * @since 03-05-2016
 */
$app->get("/pkFillJustMachineToolGroupsNotInProperty_sysMachineToolGroups/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillJustMachineToolGroupsNotInProperty_sysMachineToolGroups" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];  
    $componentType = 'bootstrap';
    
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
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('id')) $vParentId = $stripper->offsetGet('id')->getFilterValue();
    if($stripper->offsetExists('property_id')) $vPropertyId = $stripper->offsetGet('property_id')->getFilterValue();
    
    $resCombobox = $BLL->fillJustMachineToolGroupsNotInProperty(array( 
                                                        'language_code' => $vLanguageCode,
                                                        'parent_id' => $vParentId,
                                                        'property_id' => $vPropertyId,      
        
                                                            ));
 
    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
     if ($componentType == 'bootstrap') {
        $menus = array();
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],       
                "text" => $menu["name"],
                "state" => $menu["state_type"],
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"] ,
                    "icon_class"=>$menu["icon_class"] ,"group_name_eng"=>$menu["group_name_eng"],
                    "machine"=>$menu["machine"] ,) 
            );
        }
    } else if ($componentType == 'ddslick') {        
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["name"],
                "value" =>  intval($menu["id"]),
                "selected" => false,
                "description" => $menu["name_eng"],
             //   "imageSrc" => ""
            );
        }
    }
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($menus));
});
 
/**x
 *  * Okan CIRAN
 * @since 31-03-2016
 */
$app->get("/pkUpdate_sysMachineToolGroups/", function () use ($app ) {
    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
    
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }       
    $vGroupName = NULL;
    if (isset($_GET['group_name'])) {
         $stripper->offsetSet('group_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['group_name']));
    }   
    $vGroupNameEng = NULL;
    if (isset($_GET['group_name_eng'])) {
         $stripper->offsetSet('group_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['group_name_eng']));
    }  
    $vIconClass = NULL;
    if (isset($_GET['icon_class'])) {
        $stripper->offsetSet('icon_class', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['icon_class']));
    }     
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }     
    if ($stripper->offsetExists('group_name')) {
        $vGroupName = $stripper->offsetGet('group_name')->getFilterValue();
    }   
    if ($stripper->offsetExists('group_name_eng')) {
        $vGroupNameEng = $stripper->offsetGet('group_name_eng')->getFilterValue();
    } 
    if ($stripper->offsetExists('icon_class')) {
        $vIconClass = $stripper->offsetGet('icon_class')->getFilterValue();
    } 

    $resData = $BLL->update(array(  
            'id' => $vId , 
            'group_name' => $vGroupName , 
            'group_name_eng' => $vGroupNameEng,                 
            'icon_class' => $vIconClass,
            'pk' => $Pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 


/**x
 *  * Okan CIRAN
 * @since 31-03-2016
 */
$app->get("/pkInsert_sysMachineToolGroups/", function () use ($app ) {
    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    } 
    $vParentId = 0;
    if (isset($_GET['parent_id'])) {
         $stripper->offsetSet('parent_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['parent_id']));
    }       
    $vGroupName = NULL;
    if (isset($_GET['group_name'])) {
         $stripper->offsetSet('group_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['group_name']));
    }   
    $vGroupNameEng = NULL;
    if (isset($_GET['group_name_eng'])) {
         $stripper->offsetSet('group_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['group_name_eng']));
    }  
    $vIconClass = NULL;
    if (isset($_GET['icon_class'])) {
        $stripper->offsetSet('icon_class', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['icon_class']));
    } 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }   
    if ($stripper->offsetExists('parent_id')) {
        $vParentId = $stripper->offsetGet('parent_id')->getFilterValue();
    }     
    if ($stripper->offsetExists('group_name')) {
        $vGroupName = $stripper->offsetGet('group_name')->getFilterValue();
    }   
    if ($stripper->offsetExists('group_name_eng')) {
        $vGroupNameEng = $stripper->offsetGet('group_name_eng')->getFilterValue();
    } 
    if ($stripper->offsetExists('icon_class')) {
        $vIconClass = $stripper->offsetGet('icon_class')->getFilterValue();
    } 

    $resData = $BLL->insert(array(  
            'parent_id' => $vParentId , 
            'group_name' => $vGroupName , 
            'group_name_eng' => $vGroupNameEng,                 
            'icon_class' => $vIconClass,
            'language_code' => $vLanguageCode,
            'pk' => $Pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 


/**x
 *  * Okan CIRAN
 * @since 25-02-2016
 */
$app->get("/pkDelete_sysMachineToolGroups/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMachineToolGroupsBLL');
 
   
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



$app->run();
