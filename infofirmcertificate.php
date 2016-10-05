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
 * @since 30-05-2016
 */
$app->get("/pkGetAll_infoFirmCertificate/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmCertificateBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoFirmCertificate" end point, X-Public variable not found');
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
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'pk' => $pk,
            ));  
    $menus = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {
        foreach ($resDataMenu as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "firm_id" => $menu["firm_id"],
                "certificate_id" => $menu["certificate_id"],
                "certificate" => html_entity_decode($menu["certificate"]),
                "certificate_short" => html_entity_decode($menu["certificate_short"]),
                "certificate_short_eng" => html_entity_decode($menu["certificate_short_eng"]),
                "consultant_id" => $menu["consultant_id"],
                "cons_allow_id" => $menu["cons_allow_id"],
                "cons_allow" =>html_entity_decode( $menu["cons_allow"]), 
                "act_parent_id" => intval($menu["act_parent_id"]),
                "deleted" => $menu["deleted"],
                "state_deleted" => html_entity_decode($menu["state_deleted"]),
                "active" => $menu["active"],
                "state_active" => html_entity_decode($menu["state_active"]),
                "language_id" => $menu["language_id"],
                "language_name" => html_entity_decode($menu["language_names"]),
                "op_user_id" => $menu["op_user_id"],
                "op_username" => $menu["op_username"],
                "operation_type_id" => $menu["operation_type_id"],
                "operation_name" => html_entity_decode($menu["operation_name"]),
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
* @since 30-05-2016
 */
$app->get("/pkDeletedAct_infoFirmCertificate/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmCertificateBLL');
     $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmCertificate" end point, X-Public variable not found');
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
* @since 30-05-2016
 */
$app->get("/pkUpdate_infoFirmCertificate/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmCertificateBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoFirmCertificate" end point, X-Public variable not found');
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
    $vCertificateId = NULL;
    if (isset($_GET['certificate_id'])) {
         $stripper->offsetSet('certificate_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['certificate_id']));
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
    if ($stripper->offsetExists('certificate_id')) {
        $vCertificateId = $stripper->offsetGet('certificate_id')->getFilterValue();
    }  
    $resData = $BLL->update(array(  
            'url' => $_GET['url'],
            'id' => $vId , 
            'active' => $vActive,    
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'certificate_id' => $vCertificateId, 
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
* @since 30-05-2016
 */
$app->get("/pkInsert_infoFirmCertificate/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmCertificateBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmCertificate" end point, X-Public variable not found');
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
    $vCertificateId = NULL;
    if (isset($_GET['certificate_id'])) {
         $stripper->offsetSet('certificate_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['certificate_id']));
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
    if ($stripper->offsetExists('certificate_id')) {
        $vCertificateId = $stripper->offsetGet('certificate_id')->getFilterValue();
    } 
  

    $resData = $BLL->insert(array(  
            'url' => $_GET['url'],
            'network_key' => $vNetworkKey,             
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic ,                         
            'certificate_id' => $vCertificateId, 
            'pk' => $pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 


/**
 *  * Okan CIRAN
* @since 30-05-2016
 */
$app->get("/pkFillFirmCertificateNpk_infoFirmCertificate/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmCertificateBLL');

    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmCertificateNpk_infoFirmCertificate" end point, X-Public variable not found');
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

    $resDataGrid = $BLL->fillFirmCertificateNpk(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmCertificateNpkRtc(array(
        'url' => $_GET['url'],
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
                "certificate_id" => $flow["certificate_id"],
                "certificate" => html_entity_decode($flow["certificate"]),
                "certificate_eng" => html_entity_decode($flow["certificate_eng"]),
                "certificate_short" => html_entity_decode($flow["certificate_short_eng"]),
                "certificate_short_eng" => html_entity_decode($flow["certificate_short_eng"]),
                "act_parent_id" => $flow["act_parent_id"],
                "language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
                "network_key" => $flow["network_key"],
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
$app->get("/FillFirmCertificateNpkQuest_infoFirmCertificate/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmCertificateBLL');  

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

    $resDataGrid = $BLL->fillFirmCertificateNpkQuest(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
  
    ));    
  
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
               // "id" => intval($flow["id"]),                
                "firm_id" => intval($flow["firm_id"]),
                "certificate_id" => $flow["certificate_id"],
                "certificate" => html_entity_decode($flow["certificate"]),
                "certificate_eng" => html_entity_decode($flow["certificate_eng"]),
                "certificate_short" => html_entity_decode($flow["certificate_short_eng"]),
                "certificate_short_eng" => html_entity_decode($flow["certificate_short_eng"]),
                //"act_parent_id" => $flow["act_parent_id"],
               // "language_id" => $flow["language_id"],
               // "language_name" => $flow["language_name"],
               // "network_key" => $flow["network_key"],
                "attributes" => array("notroot" => true,),
            );
        }       
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();  
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});



$app->run();