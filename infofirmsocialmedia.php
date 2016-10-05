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
 * @since 09-05-2016
 */
$app->get("/pkGetAll_infoFirmSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmSocialmediaBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoFirmSocialmedia" end point, X-Public variable not found');
  //  $pk = $headerParams['X-Public'];

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
            ));
    $menus = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {
        foreach ($resDataMenu as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "act_parent_id" => intval($flow["act_parent_id"]),
                "firm_id" => $menu["firm_id"],
                "firm_name" => $menu["firm_name"],
                "firm_name_eng" => $menu["firm_name_eng"],
                "socialmedia_name" => $menu["socialmedia_name"],
                "socialmedia_eng" => $menu["socialmedia_eng"],
                "firm_link" => $menu["user_link"],     
                "network_key" => $menu["network_key"],
                "logo" => $menu["logo"], 
                "deleted" => $menu["deleted"],
                "state_deleted" => $menu["state_deleted"],
                "active" => $menu["active"],
                "state_active" => $menu["state_active"],
                "language_id" => $menu["language_id"],
                "language_name" => $menu["language_name"],
                "op_user_id" => $menu["op_user_id"],
                "op_username" => $menu["op_user_name"],
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
 * @since 09-05-2016
 */
$app->get("/pkDeletedAct_infoFirmSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmSocialmediaBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmSocialmedia" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
 
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 

    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {$vId = $stripper->offsetGet('id')->getFilterValue(); }     
    
    $resDataDeleted = $BLL->DeletedAct(array(  
            'url' => $_GET['url'],  
            'id' => $vId ,    
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

  
/**x
 *  * Okan CIRAN
 * @since 09-05-2016
 */
$app->get("/pkUpdate_infoFirmSocialmedia/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmSocialmediaBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoFirmSocialmedia" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
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
    $vActive = 0;
    if (isset($_GET['active'])) {
         $stripper->offsetSet('active',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['active']));
    }   
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vSysSocialmediaId = NULL;
    if (isset($_GET['sys_socialmedia_id'])) {
         $stripper->offsetSet('sys_socialmedia_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['sys_socialmedia_id']));
    } 
    $vFirmLink = NULL;
    if (isset($_GET['firm_link'])) {
         $stripper->offsetSet('firm_link',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_link']));
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
    if ($stripper->offsetExists('sys_socialmedia_id')) {
        $vSysSocialmediaId = $stripper->offsetGet('sys_socialmedia_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_link')) {
        $vFirmLink = $stripper->offsetGet('firm_link')->getFilterValue();
    }      

    $resData = $BLL->update(array(  
            'url' => $_GET['url'],  
            'id' => $vId ,    
            'active' => $vActive ,  
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'sys_socialmedia_id' => $vSysSocialmediaId, 
            'firm_link' => $vFirmLink,                                
            'pk' => $pk,        
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
 * @since 09-05-2016
 */
$app->get("/pkInsert_infoFirmSocialmedia/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmSocialmediaBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmSocialmedia" end point, X-Public variable not found');
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
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vSysSocialmediaId = NULL;
    if (isset($_GET['sys_socialmedia_id'])) {
         $stripper->offsetSet('sys_socialmedia_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['sys_socialmedia_id']));
    } 
    $vFirmLink = NULL;
    if (isset($_GET['firm_link'])) {
         $stripper->offsetSet('firm_link',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_link']));
    }
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('sys_socialmedia_id')) {
        $vSysSocialmediaId = $stripper->offsetGet('sys_socialmedia_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_link')) {
        $vFirmLink = $stripper->offsetGet('firm_link')->getFilterValue();
    } 
      
    $resData = $BLL->insert(array(     
            'url' => $_GET['url'],  
            'language_code' => $vLanguageCode,    
            'network_key' => $vNetworkKey,
            'profile_public' => $vProfilePublic ,                         
            'sys_socialmedia_id' => $vSysSocialmediaId, 
            'firm_link' => $vFirmLink,                                
            'pk' => $pk,        
            ));
 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
  
/**
 *  * Okan CIRAN
 * @since 09-05-2016
 */
$app->get("/pkFillSingularFirmSocialMedia_infoFirmSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmSocialmediaBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillSingularFirmSocialMedia_infoFirmSocialmedia" end point, X-Public variable not found');
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

    $resDataGrid = $BLL->fillSingularFirmSocialMedia(array(
        'url' => $_GET['url'],  
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillSingularFirmSocialMediaRtc(array(
        'url' => $_GET['url'],  
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "firm_id" => $flow["firm_id"],
                "firm_name" => html_entity_decode($flow["firm_name"]),
                "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
                "socialmedia_name" => html_entity_decode($flow["socialmedia_name"]),
                "socialmedia_eng" => html_entity_decode($flow["socialmedia_eng"]),
                "firm_link" => html_entity_decode($flow["firm_link"]),     
                "network_key" => $flow["network_key"],
                "logo" => $flow["logo"],         
                "language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
                "attributes" => array("notroot" => true,"active" => $flow["active"],  ),
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