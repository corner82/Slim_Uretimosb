<?php
// test commit for branch slim2
require 'vendor/autoload.php';
 
use \Services\Filter\Helper\FilterFactoryNames as stripChainers;



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
 * @since 09-08-2016
 
 */
$app->get("/pkFillOsbDdlist_sysOsb/", function () use ($app ) {  
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOsbBLL');
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillOsbDdlist_sysOsb" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public']; 
   
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    $resCombobox = $BLL->fillOsbDdlist(array('language_code' => $vLanguageCode,));
    
    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",);
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "text" => html_entity_decode($flow["name"]),
            "value" => intval($flow["id"]),
            "selected" => false,
            "description" => html_entity_decode($flow["name_eng"]),
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
 * @since 23-08-2016 
 */ 
$app->get("/pkInsert_sysOsb/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOsbBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysOsb" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
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
    $vCity = '';
    if (isset($_GET['city'])) {
         $stripper->offsetSet('city',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['city']));
    }   
    $vCityId = 0;
    if (isset($_GET['city_id'])) {
         $stripper->offsetSet('city_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['city_id']));
    } 
    $vBoroughId = 0;
    if (isset($_GET['borough_id'])) {
         $stripper->offsetSet('borough_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['borough_id']));
    } 
    $vAddress = '';
    if (isset($_GET['address'])) {
         $stripper->offsetSet('address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['address']));
    }   
    $vPostalCode = '';
    if (isset($_GET['postal_code'])) {
         $stripper->offsetSet('postal_code',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['postal_code']));
    }   
    
    $stripper->strip();
    if($stripper->offsetExists('country_id')) 
        {$vCountryId = $stripper->offsetGet('country_id')->getFilterValue(); }
    if($stripper->offsetExists('name')) 
        {$vName = $stripper->offsetGet('name')->getFilterValue(); }
    if($stripper->offsetExists('name_eng')) 
        {$vNameEng = $stripper->offsetGet('name_eng')->getFilterValue(); }
    if($stripper->offsetExists('city_id')) 
        {$vCityId = $stripper->offsetGet('city_id')->getFilterValue();  }
    if($stripper->offsetExists('city')) 
        {$vCity = $stripper->offsetGet('city')->getFilterValue();  }    
    if($stripper->offsetExists('borough_id')) 
        {$vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();  }   
    if($stripper->offsetExists('address')) 
        {$vAddress = $stripper->offsetGet('address')->getFilterValue(); }    
    if($stripper->offsetExists('postal_code')) 
        {$vPostalCode = $stripper->offsetGet('postal_code')->getFilterValue(); }        
   
    $resDataInsert = $BLL->insert(array(
            'url' => $_GET['url'],  
            'name' => $vName,
            'name_eng' => $vNameEng,
            'country_id' => $vCountryId,  
            'city_id' => $vCityId,   
            'borough_id' => $vBoroughId,  
            'city' => $vCity,
            'address' => $vAddress,
            'postal_code' => $vPostalCode,
            'pk' => $pk)); 
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
    
}
);

/**
 *  * Okan CIRAN
* @since 23-08-2016 
 */ 
$app->get("/pkUpdate_sysOsb/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysOsbBLL');  
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysOsb" end point, X-Public variable not found');    
    $pk = $headerParams['X-Public'];
    
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }
    $vCountryId = NULL;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
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
    $vCity = '';
    if (isset($_GET['city'])) {
         $stripper->offsetSet('city',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['city']));
    }   
    $vCityId = NULL;
    if (isset($_GET['city_id'])) {
         $stripper->offsetSet('city_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['city_id']));
    } 
    $vBoroughId = NULL;
    if (isset($_GET['borough_id'])) {
         $stripper->offsetSet('borough_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['borough_id']));
    } 
    $vAddress = '';
    if (isset($_GET['address'])) {
         $stripper->offsetSet('address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['address']));
    }   
    $vPostalCode = '';
    if (isset($_GET['postal_code'])) {
         $stripper->offsetSet('postal_code',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['postal_code']));
    }    
    $stripper->strip();
    if($stripper->offsetExists('id')) 
        {$vId = $stripper->offsetGet('id')->getFilterValue(); }
    if($stripper->offsetExists('country_id')) 
        {$vCountryId = $stripper->offsetGet('country_id')->getFilterValue(); }
    if($stripper->offsetExists('name')) 
        {$vName = $stripper->offsetGet('name')->getFilterValue(); }
    if($stripper->offsetExists('name_eng')) 
        {$vNameEng = $stripper->offsetGet('name_eng')->getFilterValue(); }
    if($stripper->offsetExists('city_id')) 
        {$vCityId = $stripper->offsetGet('city_id')->getFilterValue();  }
    if($stripper->offsetExists('city')) 
        {$vCity = $stripper->offsetGet('city')->getFilterValue();  }    
    if($stripper->offsetExists('borough_id')) 
        {$vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();  }   
    if($stripper->offsetExists('address')) 
        {$vAddress = $stripper->offsetGet('address')->getFilterValue(); }    
    if($stripper->offsetExists('postal_code')) 
        {$vPostalCode = $stripper->offsetGet('postal_code')->getFilterValue(); }        
          
    $resDataInsert = $BLL->update(array(
            'url' => $_GET['url'], 
            'id' => $vId,              
            'name' => $vName,
            'name_eng' => $vNameEng,
            'country_id' => $vCountryId,  
            'city_id' => $vCityId,   
            'borough_id' => $vBoroughId,  
            'city' => $vCity,
            'address' => $vAddress,
            'postal_code' => $vPostalCode,
            'pk' => $pk)); 
        
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));    
}
);
 
/**
 *  * Okan CIRAN
* @since 23-08-2016 
 */
$app->get("/pkDelete_sysOsb/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbBLL');   
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
* @since 23-08-2016
 */
$app->get("/pkFillOsbList_sysOsb/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOsbBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillOsbList_sysOsb" end point, X-Public variable not found');
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
    
    $resDataGrid = $BLL->fillOsbList(array(     
        'url' => $_GET['url'],  
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,       
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillOsbListRtc(array(        
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "country_id" => $flow["country_id"],
            "country_name" => html_entity_decode($flow["country_name"]),
            "name" => html_entity_decode($flow["name"]),
            "name_eng" => html_entity_decode($flow["name_eng"]),
            "city_id" => $flow["city_id"],
            "city_name" => html_entity_decode($flow["city_name"]), 
            "borough_id" => $flow["borough_id"],
            "borough_name" => html_entity_decode($flow["borough_name"]),
            "city" => html_entity_decode($flow["city"]),
            "state_active" => html_entity_decode($flow["state_active"]),
            "op_user_id" => $flow["op_user_id"],
            "op_user_name" => html_entity_decode($flow["op_user_name"]), 
            "address" => html_entity_decode($flow["address"]), 
            "postal_code" => html_entity_decode($flow["postal_code"]),                 
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
 * @since 23-08-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysOsb/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbBLL');
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