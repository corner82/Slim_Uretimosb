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
 * @since 21-04-2016
 */
$app->get("/pkGetAll_infoUsersSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersSocialmediaBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoUsersSocialmedia" end point, X-Public variable not found');
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
                "act_parent_id" => intval($flow["act_parent_id"]),
                "user_id" => $menu["user_id"],
                "name" => $menu["name"],
                "surname" => $menu["surname"],
                "socialmedia_name" => $menu["socialmedia_name"],
                "socialmedia_eng" => $menu["socialmedia_eng"],
                "user_link" => $menu["user_link"],
                "abbreviation" => $menu["abbreviation"],
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
 
/**
 *  * Okan CIRAN
* @since 21-04-2016
 */
$app->get("/pkFillSingularUsersSocialMedia_infoUsersSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersSocialmediaBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetConsConfirmationProcessDetails_sysOsbConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }

    $vUserId = 10;
    if (isset($_GET['user_id'])) {
        $stripper->offsetSet('user_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, $app, $_GET['user_id']));
    }

    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if ($stripper->offsetExists('user_id'))
        $vUserId = $stripper->offsetGet('user_id')->getFilterValue();


    $resDataGrid = $BLL->fillSingularUsersSocialMedia(array(
        'language_code' => $vLanguageCode,
        'user_id' => $vUserId,
        'pk' => $pk,
    ));
    $resTotalRowCount = $BLL->fillSingularUsersSocialMediaRtc(array(
        'user_id' => $vUserId,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {
        foreach ($resDataGrid['resultSet'] as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),
                "act_parent_id" => intval($flow["act_parent_id"]),
                "user_id" => intval($flow["user_id"]),
                "name" => $flow["name"],
                "surname" => $flow["surname"],
                "socialmedia_name" => $flow["socialmedia_name"],
                "socialmedia_name_eng" => $flow["socialmedia_name_eng"],
                "user_link" => $flow["user_link"],
                "language_name" => $flow["language_name"],
                "abbreviation" => $flow["abbreviation"],
                "s_date" => $flow["s_date"],
                "attributes" => array("notroot" => true, "active" => intval($flow["active"]),),
            );
        }
        $counts = $resTotalRowCount['resultSet'][0]['count'];
    }
    

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;

    $app->response()->body(json_encode($resultArray));
});


/**x
 *  * Okan CIRAN
* @since 21-04-2016
 */
$app->get("/pkDeletedAct_infoUsersSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoUsersSocialmediaBLL');
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
    
    $resDataDeleted = $BLL->DeletedAct(array(                  
            'id' => $vId ,    
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

  
/**x
 *  * Okan CIRAN
* @since 21-04-2016
 */
$app->get("/pkUpdate_infoUsersSocialmedia/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoUsersSocialmediaBLL');   
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
    $vProfilePublic = NULL;
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
    $vUserLink = NULL;
    if (isset($_GET['user_link'])) {
         $stripper->offsetSet('user_link',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['user_link']));
    }
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('sys_socialmedia_id')) {
        $vSysSocialmediaId = $stripper->offsetGet('sys_socialmedia_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('user_link')) {
        $vUserLink = $stripper->offsetGet('user_link')->getFilterValue();
    } 
     

    $resData = $BLL->update(array(  
            'id' => $vId , 
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'sys_socialmedia_id' => $vSysSocialmediaId, 
            'user_link' => $vUserLink,                                
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
* @since 21-04-2016
 */
$app->get("/pkInsert_infoUsersSocialmedia/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoUsersSocialmediaBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }              
    $vProfilePublic = NULL;
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
    $vUserLink = NULL;
    if (isset($_GET['user_link'])) {
         $stripper->offsetSet('user_link',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['user_link']));
    }
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('sys_socialmedia_id')) {
        $vSysSocialmediaId = $stripper->offsetGet('sys_socialmedia_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('user_link')) {
        $vUserLink = $stripper->offsetGet('user_link')->getFilterValue();
    } 
     

    $resData = $BLL->insert(array(          
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'sys_socialmedia_id' => $vSysSocialmediaId, 
            'user_link' => $vUserLink,                                
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 


/**
 *  * Okan CIRAN
* @since 21-04-2016
 */
$app->get("/pkFillCompanyUsersSocialMediaNpk_infoUsersSocialmedia/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersSocialmediaBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetConsConfirmationProcessDetails_sysOsbConsultants" end point, X-Public variable not found');
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

    $resDataGrid = $BLL->fillCompanyUsersSocialMediaNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillCompanyUsersSocialMediaNpkRtc(array(
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),                
                "user_id" => intval($flow["user_id"]),
                "name" => $flow["name"],
                "surname" => $flow["surname"],
                "socialmedia_name" => $flow["socialmedia_name"],
                "socialmedia_name_eng" => $flow["socialmedia_name_eng"],
                "user_link" => $flow["user_link"],
                "abbreviation" => $flow["abbreviation"],
                "network_key" => $flow["network_key"],
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