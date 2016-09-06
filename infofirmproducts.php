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
 * @since 20-05-2016
 */
$app->get("/pkGetAll_infoFirmProducts/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoFirmProducts" end point, X-Public variable not found');
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
                "product_name" => $menu["product_name"],
                "product_name_eng" => $menu["product_name_eng"],
                "product_description" => $menu["product_description"],
                "product_description_eng" => $menu["product_description_eng"],
                "gtip_no_id" => $menu["gtip_no_id"], 
                "cnkey" => $menu["cnkey"],
                "gtip" => $menu["gtip"],
                "gtip_eng" => $menu["gtip_eng"],
                "product_video_link" => $menu["product_video_link"],
                "production_types_id" => $menu["production_types_id"],                
                "picture" => $menu["picture"],
                
                
                
                "consultant_id" => $menu["consultant_id"],
                "consultant_confirm_type_id" => $menu["consultant_confirm_type_id"],
                "confirm_id" => $menu["confirm_id"],
                "cons_allow_id" => $menu["cons_allow_id"],
                "cons_allow" => $menu["cons_allow"],                
                "language_parent_id" => $menu["language_parent_id"], 
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
 * @since 20-05-2016
 */
$app->get("/pkDeletedAct_infoFirmProducts/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmProducts" end point, X-Public variable not found');
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
            'id' => $vId ,    
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

  
/**x
 *  * Okan CIRAN
 * @since 20-05-2016
 */
$app->get("/pkUpdate_infoFirmProducts/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoFirmProducts" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];          
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
    $vLanguageCode = 'tr'; 
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }       
    $vProfilePublic = 0;
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    } 
    $vGtipNoId= NULL;
    if (isset($_GET['gtip_no_id'])) {
         $stripper->offsetSet('gtip_no_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['gtip_no_id']));
    } 
    $vProductName = NULL;
    if (isset($_GET['product_name'])) {
         $stripper->offsetSet('product_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_name']));
    }
    $vProductDescription = NULL;
    if (isset($_GET['product_description'])) {
         $stripper->offsetSet('product_description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_description']));
    }     
    $vProductDescriptionEng = NULL;
    if (isset($_GET['product_description_eng'])) {
         $stripper->offsetSet('product_description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_description_eng']));
    }
    $vProductNameEng = NULL;
    if (isset($_GET['product_name_eng'])) {
         $stripper->offsetSet('product_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_name_eng']));
    } 
    $vProductVideoLink = NULL;
    if (isset($_GET['product_video_link'])) {
         $stripper->offsetSet('product_video_link',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_video_link']));
    } 
    $vPicture = NULL;
    if (isset($_GET['picture'])) {
         $stripper->offsetSet('picture',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['picture']));
    } 
    $vProductionTypesId = NULL;
    if (isset($_GET['production_types_id'])) {
         $stripper->offsetSet('production_types_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['production_types_id']));
    }     
    
    $stripper->strip();    
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    }     
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }  
    if ($stripper->offsetExists('npk')){
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('gtip_no_id')) {
        $vGtipNoId = $stripper->offsetGet('gtip_no_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('product_name')) {
        $vProductName = $stripper->offsetGet('product_name')->getFilterValue();
    }
    if ($stripper->offsetExists('product_description')) {
        $vProductDescription = $stripper->offsetGet('product_description')->getFilterValue();
    }    
    if ($stripper->offsetExists('product_description_eng')) {
        $vProductDescriptionEng = $stripper->offsetGet('product_description_eng')->getFilterValue();
    }   
    if ($stripper->offsetExists('product_name_eng')) {
        $vProductNameEng = $stripper->offsetGet('product_name_eng')->getFilterValue();
    } 
    if ($stripper->offsetExists('product_video_link')) {
        $vProductVideoLink = $stripper->offsetGet('product_video_link')->getFilterValue();
    } 
    if ($stripper->offsetExists('production_types_id')) {
        $vProductionTypesId = $stripper->offsetGet('production_types_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('picture')) {
        $vPicture = $stripper->offsetGet('picture')->getFilterValue();
    } 
     
    $resData = $BLL->update(array(  
            'id' => $vId,
            'active' => $vActive,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,            
            'product_name' => $vProductName,
            'product_name_eng' => $vProductNameEng,
            'product_description' => $vProductDescription,
            'product_description_eng' => $vProductDescriptionEng, 
            'gtip_no_id' => $vGtipNoId,
            'product_video_link' => $vProductVideoLink,
            'production_types_id' => $vProductionTypesId,
            'product_picture' => $vPicture, 
            'network_key' => $vNpk,                  
            'pk' => $pk,     
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
 * @since 20-05-2016
 */
$app->get("/pkInsert_infoFirmProducts/", function () use ($app ) {    
     $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmProducts" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
    $vLanguageCode = 'tr'; 
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }       
    $vProfilePublic = 0;
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    } 
    $vGtipNoId= NULL;
    if (isset($_GET['gtip_no_id'])) {
         $stripper->offsetSet('gtip_no_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['gtip_no_id']));
    } 
    $vProductName = NULL;
    if (isset($_GET['product_name'])) {
         $stripper->offsetSet('product_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_name']));
    }
    $vProductDescription = NULL;
    if (isset($_GET['product_description'])) {
         $stripper->offsetSet('product_description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_description']));
    }     
    $vProductDescriptionEng = NULL;
    if (isset($_GET['product_description_eng'])) {
         $stripper->offsetSet('product_description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_description_eng']));
    }
    $vProductNameEng = NULL;
    if (isset($_GET['product_name_eng'])) {
         $stripper->offsetSet('product_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_name_eng']));
    } 
    $vProductVideoLink = NULL;
    if (isset($_GET['product_video_link'])) {
         $stripper->offsetSet('product_video_link',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_video_link']));
    } 
    $vPicture = NULL;
    if (isset($_GET['picture'])) {
         $stripper->offsetSet('picture',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['picture']));
    } 
    $vProductionTypesId = NULL;
    if (isset($_GET['production_types_id'])) {
         $stripper->offsetSet('production_types_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['production_types_id']));
    }     
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }  
    if ($stripper->offsetExists('npk')){
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('gtip_no_id')) {
        $vGtipNoId = $stripper->offsetGet('gtip_no_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('product_name')) {
        $vProductName = $stripper->offsetGet('product_name')->getFilterValue();
    }
    if ($stripper->offsetExists('product_description')) {
        $vProductDescription = $stripper->offsetGet('product_description')->getFilterValue();
    }    
    if ($stripper->offsetExists('product_description_eng')) {
        $vProductDescriptionEng = $stripper->offsetGet('product_description_eng')->getFilterValue();
    }   
    if ($stripper->offsetExists('product_name_eng')) {
        $vProductNameEng = $stripper->offsetGet('product_name_eng')->getFilterValue();
    } 
    if ($stripper->offsetExists('product_video_link')) {
        $vProductVideoLink = $stripper->offsetGet('product_video_link')->getFilterValue();
    } 
    if ($stripper->offsetExists('production_types_id')) {
        $vProductionTypesId = $stripper->offsetGet('production_types_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('picture')) {
        $vPicture = $stripper->offsetGet('picture')->getFilterValue();
    } 
    

    $resData = $BLL->insert(array(              
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,            
            'product_name' => $vProductName,
            'product_name_eng' => $vProductNameEng,
            'product_description' => $vProductDescription,
            'product_description_eng' => $vProductDescriptionEng, 
            'gtip_no_id' => $vGtipNoId,
            'product_video_link' => $vProductVideoLink,
            'production_types_id' => $vProductionTypesId,
            'product_picture' => $vPicture, 
            'network_key' => $vNpk,                  
            'pk' => $pk,        
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
  
/**
 *  * Okan CIRAN
 * @since 20-05-2016
 */
$app->get("/pkFillFirmProductsNpk_infoFirmProducts/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmProductsNpk_infoFirmProducts" end point, X-Public variable not found');
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

    $resDataGrid = $BLL->fillFirmProductsNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmProductsNpkRtc(array(
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
                "product_name" => $flow["product_name"],
                "product_name_eng" => $flow["product_name_eng"],
                "product_description" => $flow["product_description"],
                "product_description_eng" => $flow["product_description_eng"],
                //"gtip_no_id" => intval($flow["gtip_no_id"]), 
                "cnkey" => $flow["cnkey"],
                "gtip" => $flow["gtip"],
                "gtip_eng" => $flow["gtip_eng"],
                "product_video_link" => $flow["product_video_link"],
                "production_types_id" => intval($flow["production_types_id"]),
                "picture" => $flow["picture"],
                
                "attributes" => array("notroot" => true,  ),
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

/**
 *  * Okan CIRAN
 * @since 20-05-2016
 */
$app->get("/pkFillFirmProductsNpkQuest_infoFirmProducts/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmProductsNpkQuest_infoFirmProducts" end point, X-Public variable not found');
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

    $resDataGrid = $BLL->fillFirmProductsNpkQuest(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmProductsNpkQuestRtc(array(
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
                "product_name" => $flow["product_name"],
                "product_name_eng" => $flow["product_name_eng"],
                "product_description" => $flow["product_description"],
                "product_description_eng" => $flow["product_description_eng"],
                //"gtip_no_id" => intval($flow["gtip_no_id"]), 
                "cnkey" => $flow["cnkey"],
                "gtip" => $flow["gtip"],
                "gtip_eng" => $flow["gtip_eng"],
                "product_video_link" => $flow["product_video_link"],
                "production_types_id" => intval($flow["production_types_id"]),
                "picture" => $flow["picture"],
                
                "attributes" => array("notroot" => true,"active" => intval($flow["active"]),  ),
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

/**
 *  * Okan CIRAN
 * @since 20-05-2016
 */
$app->get("/pkFillFirmProductsGtip_infoFirmProducts/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProductsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmProductsGtip_infoFirmProducts" end point, X-Public variable not found');
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
    $vGtipNoId= NULL;
    if (isset($_GET['gtip_no_id'])) {
         $stripper->offsetSet('gtip_no_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['gtip_no_id']));
    } 
    $vGtipKey = NULL;
    if (isset($_GET['gtip_key'])) {
         $stripper->offsetSet('gtip_key',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['gtip_key']));
    }
    $vGtip = NULL;
    if (isset($_GET['gtip'])) {
         $stripper->offsetSet('gtip',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['gtip']));
    }
     $vGtipEng = NULL;
    if (isset($_GET['gtip_eng'])) {
         $stripper->offsetSet('gtip_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['gtip_eng']));
    }
    $vProductName = NULL;
    if (isset($_GET['product_name'])) {
         $stripper->offsetSet('product_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['product_name']));
    } 
    
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if ($stripper->offsetExists('npk'))
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    if ($stripper->offsetExists('gtip_no_id'))
        $vGtipNoId = $stripper->offsetGet('gtip_no_id')->getFilterValue();
    if ($stripper->offsetExists('gtip_key'))
        $vGtipKey = $stripper->offsetGet('gtip_key')->getFilterValue();
    if ($stripper->offsetExists('gtip'))
        $vGtip = $stripper->offsetGet('gtip')->getFilterValue();
    if ($stripper->offsetExists('gtip_eng'))
        $vGtipEng = $stripper->offsetGet('gtip_eng')->getFilterValue();
    if ($stripper->offsetExists('product_name'))
        $vProductName = $stripper->offsetGet('product_name')->getFilterValue();

    $resDataGrid = $BLL->fillFirmProductsGtip(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'gtip_no_id' => $vGtipNoId,
        'gtip_key' => $vGtipKey,
        'gtip' => $vGtip,
        'gtip_eng' => $vGtipEng,
        'product_name' => $vProductName,
      
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmProductsGtipRtc(array(
        'network_key' => $vNpk,
        'gtip_no_id' => $vGtipNoId,
        'gtip_key' => $vGtipKey,
        'gtip' => $vGtip,
        'gtip_eng' => $vGtipEng,
        'product_name' => $vProductName,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "firm_id" => $flow["firm_id"],
                "product_name" => $flow["product_name"],
                "product_name_eng" => $flow["product_name_eng"],
                "product_description" => $flow["product_description"],
                "product_description_eng" => $flow["product_description_eng"],
                //"gtip_no_id" => intval($flow["gtip_no_id"]), 
                "cnkey" => $flow["cnkey"],
                "gtip" => $flow["gtip"],
                "gtip_eng" => $flow["gtip_eng"],
                "product_video_link" => $flow["product_video_link"],
                "production_types_id" => intval($flow["production_types_id"]),
                "picture" => $flow["picture"],
                
                "attributes" => array("notroot" => true,"active" => intval($flow["active"]),  ),
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