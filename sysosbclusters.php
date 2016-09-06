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

 
/**
 *  * Okan CIRAN
 * @since 21-06-2016
 */
$app->get("/pk----FillOsbClusterLists_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');    
    $headerParams = $app->request()->headers();    
    
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }   
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillOsbClusterLists_sysOsbClusters" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vOsbId = NULL;
    if (isset($_GET['osb_id'])) {
        $stripper->offsetSet('osb_id', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['osb_id']));
    }
 
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if ($stripper->offsetExists('osb_id'))
        $vOsbId = $stripper->offsetGet('osb_id')->getFilterValue();
    
    $resData = $BLL->fillOsbClusterLists(array(
                                                'language_code' => $vLanguageCode,
                                                'pk' => $pk,
                                                'osb_id' => $vOsbId,
                                                        )); 
    
    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
     if ($componentType == 'bootstrap') {
        $menus = array();
        foreach ($resData as $menu) {
            $menus[] = array(
                "id" => $menu["id"],       
                "text" => $menu["cluster"],
                "state" => $menu["state_type"],
                "checked" => false,
                "attributes" => array("notroot" => true, 
                                    "active" => $menu["active"] ,
                                    "cluster_eng"=>$menu["cluster_eng"],
                )                
            );
        }
    } else if ($componentType == 'ddslick') {   
        foreach ($resData as $menu) {
            $menus[] = array(
                "text" => $menu["cluster"],
                "value" =>  intval($menu["id"]),
                "selected" => false,
                "description" => $menu["cluster_eng"],
             //   "imageSrc" => ""
            );
        }
    }
    
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
   // $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $menus;
    
     // $app->response()->body(json_encode($flows));
    if($componentType == 'bootstrap'){
        $app->response()->body(json_encode($menus));
    }else if($componentType == 'ddslick'){
        $app->response()->body(json_encode($resultArray));
    }
      //  $app->response()->body(json_encode($resultArray));
        
 
});
 
/**
 *  * Okan CIRAN
* @since 23-08-2016
 */
$app->get("/pkFillOsbClusterLists_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillOsbClusterLists_sysOsbClusters" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];
     
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
    
    $resDataGrid = $BLL->fillOsbClusterLists(array(     
        'url' => $_GET['url'],  
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,       
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillOsbClusterListsRtc(array(        
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "osb_id" => $flow["osb_id"],
                "osb_name" => html_entity_decode($flow["osb_name"]),
                "name" => html_entity_decode($flow["name"]),
                "name_eng" => html_entity_decode($flow["name_eng"]),
                "country_id" => $flow["country_id"],
                "country_name" => html_entity_decode($flow["country_name"]),     
                "city_id" => $flow["city_id"],
                "city_name" => html_entity_decode($flow["city_name"]), 
                "borough_id" => $flow["borough_id"],
                "borough_name" => html_entity_decode($flow["borough_name"]), 
                "op_user_id" => $flow["op_user_id"],
                "op_user_name" => html_entity_decode($flow["op_user_name"]),
                "state_active" => html_entity_decode($flow["state_active"]),     
                "description" => html_entity_decode($flow["description"]),
                "description_eng" => html_entity_decode($flow["description_eng"]),     
              
                
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
 * @since 21-06-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');
    
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdateMakeActiveOrPassive_sysOsbClusters" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
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
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 

/**x
 *  * Okan CIRAN
 * @since 21-06-2016
 */
$app->get("/pkDelete_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDelete_sysOsbClusters" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];   
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) 
        {$vId = $stripper->offsetGet('id')->getFilterValue(); }  
        
    $resDataDeleted = $BLL->Delete(array(                  
            'id' => $vId ,    
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 


/**
 *  * Okan CIRAN
 * @since 25-08-2016 
 */ 
$app->get("/pkInsert_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysOsbClusters" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vOsbId= 0;
    if (isset($_GET['osb_id'])) {
         $stripper->offsetSet('osb_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['osb_id']));
    }     
    $vName = '';
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['name']));
    }    
    $vNameEng = '';
    if (isset($_GET['name_eng'])) {
         $stripper->offsetSet('name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['name_eng']));
    }  
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['description']));
    }    
    $vDescriptionEng = '';
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['description_eng']));
    }   
    
    $stripper->strip();
    if($stripper->offsetExists('osb_id')) 
        {$vOsbId = $stripper->offsetGet('osb_id')->getFilterValue(); }
    if($stripper->offsetExists('name')) 
        {$vName = $stripper->offsetGet('name')->getFilterValue(); }
    if($stripper->offsetExists('name_eng')) 
        {$vNameEng = $stripper->offsetGet('name_eng')->getFilterValue(); }
    if($stripper->offsetExists('description')) 
        {$vDescription = $stripper->offsetGet('description')->getFilterValue();  }
    if($stripper->offsetExists('description_eng')) 
        {$vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();  }        
   
    $resDataInsert = $BLL->insert(array(
            'url' => $_GET['url'],  
            'osb_id' => $vOsbId,  
            'name' => $vName,
            'name_eng' => $vNameEng,            
            'description' => $vDescription,   
            'description_eng' => $vDescriptionEng,  
            'pk' => $pk)); 
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);

/**
 *  * Okan CIRAN
* @since 25-08-2016 
 */ 
$app->get("/pkUpdate_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysOsbClusters" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vId = 0;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $vOsbId= 0;
    if (isset($_GET['osb_id'])) {
         $stripper->offsetSet('osb_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['osb_id']));
    }     
    $vName = '';
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['name']));
    }    
    $vNameEng = '';
    if (isset($_GET['name_eng'])) {
         $stripper->offsetSet('name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['name_eng']));
    }  
    $vDescription = '';
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['description']));
    }    
    $vDescriptionEng = '';
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['description_eng']));
    }   
    $stripper->strip();
    if($stripper->offsetExists('id')) 
        {$vId = $stripper->offsetGet('id')->getFilterValue(); }
    if($stripper->offsetExists('osb_id')) 
        {$vOsbId = $stripper->offsetGet('osb_id')->getFilterValue(); }
    if($stripper->offsetExists('name')) 
        {$vName = $stripper->offsetGet('name')->getFilterValue(); }
    if($stripper->offsetExists('name_eng')) 
        {$vNameEng = $stripper->offsetGet('name_eng')->getFilterValue(); }
    if($stripper->offsetExists('description')) 
        {$vDescription = $stripper->offsetGet('description')->getFilterValue();  }
    if($stripper->offsetExists('description_eng')) 
        {$vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();  }        
          
    $resDataInsert = $BLL->update(array(
            'url' => $_GET['url'], 
            'id' => $vId,              
            'osb_id' => $vOsbId,  
            'name' => $vName,
            'name_eng' => $vNameEng,            
            'description' => $vDescription,   
            'description_eng' => $vDescriptionEng,  
            'pk' => $pk)); 
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));    
}
);
 
/**
 *  * Okan CIRAN
* @since 25-08-2016 
 */
$app->get("/pkDelete_sysOsbClusters/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');   
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
    $resDataDeleted = $BLL->delete(array(
            'url' => $_GET['url'],  
            'id' => $vId,
            'pk' => $Pk, 
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
);  

/**
 *  * Okan CIRAN
 * @since 25-08-2016
 
 */
$app->get("/pkFillOsbClustersDdlist_sysOsbClusters/", function () use ($app ) {  
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOsbClustersBLL');
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillOsbClustersDdlist_sysOsbClusters" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public']; 
   
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vCountryId = NULL;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['country_id']));
    }
    $vOsbId = NULL;
    if (isset($_GET['osb_id'])) {
        $stripper->offsetSet('osb_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['osb_id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('osb_id')) {
        $vOsbId = $stripper->offsetGet('osb_id')->getFilterValue();
    } 
    
    
    $resCombobox = $BLL->fillOsbClustersDdlist(array('language_code' => $vLanguageCode,
                                                    'country_id' => $vCountryId,
                                                    'osb_id' => $vOsbId,
        ));
    
    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["cluster_name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["osb_name"]),
            // "imageSrc"=>$flow["logo"],             
            "attributes" => array(                 
                    "active" => $flow["active"],
                    "osb_id" => $flow["osb_id"],
                    "text_eng" => html_entity_decode($flow["cluster_name_eng"]),
                    
            ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});

$app->run();
