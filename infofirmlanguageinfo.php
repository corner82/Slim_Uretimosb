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
 * Okan CIRAN
 * @since 31.05.2016
 */
$app->get("/pkGetAll_infoFirmLanguageInfo/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoFirmLanguageInfo" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

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

    $resDataMenu = $BLL->getAll(array(
        'language_code' => $vLanguageCode,
        'pk' => $pk,
            ));


    $menus = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {
        foreach ($resDataMenu as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "firm_id" => $menu["firm_id"],
                "firm_language_id" => $menu["firm_language_id"],
                "language_main_code" => $menu["language_main_code"],
                "language" => $menu["language"], 
                "consultant_id" => $menu["consultant_id"],
                "cons_allow_id" => $menu["cons_allow_id"],
                "cons_allow" => $menu["cons_allow"], 
                "act_parent_id" => intval($flow["act_parent_id"]),
                "deleted" => $menu["deleted"],
                "state_deleted" => $menu["state_deleted"],
                "active" => $menu["active"],
                "state_active" => $menu["state_active"],
                "language_id" => $menu["language_id"],
                "language_name" => $menu["language_names"],
                "op_user_id" => $menu["op_user_id"],
                "op_username" => $menu["op_username"],
                "operation_type_id" => $menu["operation_type_id"],
                "operation_name" => $menu["operation_name"],
                "s_date" => $menu["s_date"],
                "c_date" => $menu["c_date"],
            );
        }
    }

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($menus));
});
  

/**x
 *  * Okan CIRAN
* @since 31.05.2016
 */
$app->get("/pkcpkDeletedAct_infoFirmLanguageInfo/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkDeletedAct_infoFirmLanguageInfo" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $vCpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['cpk']));
    }

    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {$vId = $stripper->offsetGet('id')->getFilterValue(); }     
    if ($stripper->offsetExists('cpk')) {$vCpk = $stripper->offsetGet('cpk')->getFilterValue(); } 
    
    $resDataDeleted = $BLL->DeletedAct(array(                  
            'id' => $vId ,    
            'pk' => $pk,  
            'cpk' => $vCpk, 
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

  
/**x
 *  * Okan CIRAN
* @since 31.05.2016
 */
$app->get("/pkcpkUpdate_infoFirmLanguageInfo/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkUpdate_infoFirmLanguageInfo" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];   
    $vCpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['cpk']));
    }
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
    $vActive = NULL;
    if (isset($_GET['active'])) {
         $stripper->offsetSet('active',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['active']));
    }   
    $vProfilePublic = NULL;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vFirmLanguageId = NULL;
    if (isset($_GET['firm_language_id'])) {
         $stripper->offsetSet('firm_language_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_language_id']));
    } 
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
     if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_language_id')) {
        $vFirmLanguageId = $stripper->offsetGet('firm_language_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('cpk'))
        {$vCpk = $stripper->offsetGet('cpk')->getFilterValue(); } 
  

    $resData = $BLL->update(array(  
            'id' => $vId , 
            'active' => $vActive,    
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'firm_language_id' => $vFirmLanguageId, 
            'pk' => $pk,  
            'cpk' => $vCpk, 
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
* @since 31.05.2016
 */
$app->get("/pkcpkInsert_infoFirmLanguageInfo/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkInsert_infoFirmLanguageInfo" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];          
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }       
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
         $stripper->offsetSet('npk',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    } 
    $vActive = NULL;
    if (isset($_GET['active'])) {
         $stripper->offsetSet('active',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['active']));
    }   
    $vProfilePublic = NULL;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vFirmLanguageId = NULL;
    if (isset($_GET['firm_language_id'])) {
         $stripper->offsetSet('firm_language_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_language_id']));
    } 
    $vCpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['cpk']));
    }
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
     if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_language_id')) {
        $vFirmLanguageId = $stripper->offsetGet('firm_language_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('cpk')) 
        {$vCpk = $stripper->offsetGet('cpk')->getFilterValue(); }     
  

    $resData = $BLL->insert(array(  
            'network_key' => $vNetworkKey,             
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'firm_language_id' => $vFirmLanguageId, 
            'pk' => $pk,   
            'cpk' => $vCpk,  
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 


/**
 *  * Okan CIRAN
* @since 31.05.2016
 */
$app->get("/pkFillFirmLanguageNpk_infoFirmLanguageInfo/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmLanguageNpk_infoFirmLanguageInfo" end point, X-Public variable not found');
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
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();

    $resDataGrid = $BLL->fillFirmLanguageNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmLanguageNpkRtc(array(
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),                
                "firm_id" => intval($flow["firm_id"]),
                "firm_language_id" => $flow["firm_language_id"],
                "language" => $flow["language"],
                "language_eng" => $flow["language_eng"],
                "language_main_code" => $flow["language_main_code"],
                "act_parent_id" => $flow["act_parent_id"],
                "language_id" => $flow["language_id"],
                "language_name" => $flow["language_name"],
              //  "network_key" => $flow["network_key"],
                "attributes" => array("notroot" => true,),
            );
        }
      // $counts = $resTotalRowCount[0]['count'];
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
  //  $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});


/**
 *  * Okan CIRAN
 * @since 27-05-2016
 */
$app->get("/FillFirmLanguageNpkQuest_infoFirmLanguageInfo/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');  

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
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();

    $resDataGrid = $BLL->fillFirmLanguageNpkQuest(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
  
    ));    
  
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
             // "id" => intval($flow["id"]),                
              //  "firm_id" => intval($flow["firm_id"]),
                "language" => $flow["language"],
                "language_eng" => $flow["language_eng"],
                "language_main_code" => $flow["language_main_code"],
              //  "act_parent_id" => $flow["act_parent_id"],
              //  "language_id" => $flow["language_id"],
              //  "language_name" => $flow["language_name"],
              //  "network_key" => $flow["network_key"],
                "attributes" => array("notroot" => true,),
            );
        }       
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();  
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});



/**
 *  * Okan CIRAN
* @since 31.05.2016
 */
$app->get("/pkFillFindFirmLanguageId_infoFirmLanguageInfo/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmLanguageInfoBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFindFirmLanguageId_infoFirmLanguageInfo" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vFirmLanguageId = NULL;
    if (isset($_GET['firm_language_id'])) {
         $stripper->offsetSet('firm_language_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_language_id']));
    }  
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('firm_language_id'))
        $vFirmLanguageId = $stripper->offsetGet('firm_language_id')->getFilterValue();

    $resDataGrid = $BLL->fillFindFirmLanguageId(array(
        'language_code' => $vLanguageCode,
        'firm_language_id' => $vFirmLanguageId,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFindFirmLanguageIdRtc(array(
        'firm_language_id' => $vFirmLanguageId,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),                
                "firm_id" => intval($flow["firm_id"]),
                "firm_name" => $flow["firm_name"],
                "firm_name_eng" => $flow["firm_name_eng"],
                "firm_name_short" => $flow["firm_name_short"],
                "firm_name_short_eng" => $flow["firm_name_short_eng"],
                "web_address" => $flow["web_address"],                
                "firm_language_id" => $flow["firm_language_id"],
                "language_main_code" => $flow["language_main_code"],
                "language" => $flow["language"],
                "language_eng" => $flow["language_eng"],
                "language_main_code" => $flow["language_main_code"],
                "language_name" => $flow["language_name"],
                "act_parent_id" => $flow["act_parent_id"], 
                "language_id" => $flow["language_id"],
                "language_name" => $flow["language_name"],
                "network_key" => $flow["network_key"],
                "place_point" => $flow["place_point"],
                "logo" => $flow["logo"], 
                "attributes" => array("notroot" => true,),
            );
        }
        $counts = $resTotalRowCount[0]['count'];
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

$app->run();