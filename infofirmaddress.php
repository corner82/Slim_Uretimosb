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
 * @since 17-05-2016
 */
$app->get("/pkGetAll_infoFirmAddress/", function () use ($app ) {  
});
  
/**x
 *  * Okan CIRAN
 * @since 17-05-2016
 */
$app->get("/pkDeletedAct_infoFirmAddress/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmAddressBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmAddress" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                    $app, $_GET['language_code']));
    }   
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }     
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    $resDataDeleted = $BLL->DeletedAct(array( 
            'url' => $_GET['url'],  
            'language_code' => $vLanguageCode,
            'id' => $vId ,    
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 
  
/**x
 *  * Okan CIRAN
 * @since 17-05-2016
 */
$app->get("/pkcpkUpdate_infoFirmAddress/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmAddressBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkUpdate_infoFirmAddress" end point, X-Public variable not found');
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
    $vcpk = NULL;
    if (isset($_GET['cpk'])) {
         $stripper->offsetSet('cpk',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['cpk']));
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
    $vFirmBuildingTypeId= NULL;
    if (isset($_GET['firm_building_type_id'])) {
         $stripper->offsetSet('firm_building_type_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_building_type_id']));
    } 
    $vFirmBuildingName = NULL;
    if (isset($_GET['firm_building_name'])) {
         $stripper->offsetSet('firm_building_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_building_name']));
    }
    $vAddress = NULL;
    if (isset($_GET['address'])) {
         $stripper->offsetSet('address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['address']));
    }
    $vBoroughId = NULL;
    if (isset($_GET['borough_id'])) {
         $stripper->offsetSet('borough_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['borough_id']));
    }
    $vCityId = NULL;
    if (isset($_GET['city_id'])) {
         $stripper->offsetSet('city_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['city_id']));
    }
    $vCountryId = NULL;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
    }
    
    $vOsbId = NULL;
    if (isset($_GET['osb_id'])) {
         $stripper->offsetSet('osb_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['osb_id']));
    } 
    $vDescription = NULL;
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    } 
    $vTel = NULL;
    if (isset($_GET['tel'])) {
         $stripper->offsetSet('tel',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['tel']));
    } 
    $vFax = NULL;
    if (isset($_GET['fax'])) {
         $stripper->offsetSet('fax',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['fax']));
    } 
    $vEmail = NULL;
    if (isset($_GET['email'])) {
         $stripper->offsetSet('email',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['email']));
    } 
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('cpk')) {
        $vcpk = $stripper->offsetGet('cpk')->getFilterValue();
    } 
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_building_type_id')) {
        $vFirmBuildingTypeId = $stripper->offsetGet('firm_building_type_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_building_name')) {
        $vFirmBuildingName = $stripper->offsetGet('firm_building_name')->getFilterValue();
    }
    if ($stripper->offsetExists('address')) {
        $vAddress = $stripper->offsetGet('address')->getFilterValue();
    }
    if ($stripper->offsetExists('borough_id')) {
        $vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_id')) {
        $vCityId = $stripper->offsetGet('city_id')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('osb_id')) {
        $vOsbId = $stripper->offsetGet('osb_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }   
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    } 
    if ($stripper->offsetExists('tel')) {
        $vTel = $stripper->offsetGet('tel')->getFilterValue();
    } 
    if ($stripper->offsetExists('fax')) {
        $vFax = $stripper->offsetGet('fax')->getFilterValue();
    }
    if ($stripper->offsetExists('email')) {
        $vEmail= $stripper->offsetGet('email')->getFilterValue();
    } 
    $resData = $BLL->update(array(  
            'url' => $_GET['url'],  
            'id' => $vId,
            'cpk' => $vcpk,
            'active' => $vActive,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,
            'firm_building_type_id' => $vFirmBuildingTypeId,
            'firm_building_name' => $vFirmBuildingName,
            'address' => $vAddress,
            'borough_id' => $vBoroughId,
            'city_id' => $vCityId,
            'country_id' => $vCountryId,
            'osb_id' => $vOsbId,
            'description' => $vDescription,
            'description_eng' => $vDescriptionEng,
            'tel' => $vTel,
            'fax' => $vFax,
            'email' => $vEmail,        
            'pk' => $pk,        
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
);  

/**x
 *  * Okan CIRAN
 * @since 17-05-2016
 */
$app->get("/pkInsert_infoFirmAddress/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmAddressBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmAddress" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
    $vLanguageCode = 'tr'; 
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }   
    $vFirmBuildingTypeId= NULL;
    if (isset($_GET['firm_building_type_id'])) {
         $stripper->offsetSet('firm_building_type_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_building_type_id']));
    } 
    $vFirmBuildingName = NULL;
    if (isset($_GET['firm_building_name'])) {
         $stripper->offsetSet('firm_building_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_building_name']));
    }
    $vAddress = NULL;
    if (isset($_GET['address'])) {
         $stripper->offsetSet('address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['address']));
    }
    $vBoroughId = NULL;
    if (isset($_GET['borough_id'])) {
         $stripper->offsetSet('borough_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['borough_id']));
    }
    $vCityId = NULL;
    if (isset($_GET['city_id'])) {
         $stripper->offsetSet('city_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['city_id']));
    }
    $vCountryId = NULL;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
    }    
    $vOsbId = NULL;
    if (isset($_GET['osb_id'])) {
         $stripper->offsetSet('osb_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['osb_id']));
    } 
    $vDescription = NULL;
    if (isset($_GET['description'])) {
         $stripper->offsetSet('description',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
         $stripper->offsetSet('description_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    } 
    $vTel = NULL;
    if (isset($_GET['tel'])) {
         $stripper->offsetSet('tel',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['tel']));
    } 
    $vFax = NULL;
    if (isset($_GET['fax'])) {
         $stripper->offsetSet('fax',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['fax']));
    } 
    $vEmail = NULL;
    if (isset($_GET['email'])) {
         $stripper->offsetSet('email',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['email']));
    } 
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_building_type_id')) {
        $vFirmBuildingTypeId = $stripper->offsetGet('firm_building_type_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_building_name')) {
        $vFirmBuildingName = $stripper->offsetGet('firm_building_name')->getFilterValue();
    }
    if ($stripper->offsetExists('address')) {
        $vAddress = $stripper->offsetGet('address')->getFilterValue();
    }
    if ($stripper->offsetExists('borough_id')) {
        $vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_id')) {
        $vCityId = $stripper->offsetGet('city_id')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('osb_id')) {
        $vOsbId = $stripper->offsetGet('osb_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }   
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    } 
    if ($stripper->offsetExists('tel')) {
        $vTel = $stripper->offsetGet('tel')->getFilterValue();
    } 
    if ($stripper->offsetExists('fax')) {
        $vFax = $stripper->offsetGet('fax')->getFilterValue();
    } 
    if ($stripper->offsetExists('email')) {
        $vEmail = $stripper->offsetGet('email')->getFilterValue();
    } 
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $resData = $BLL->insert(array(   
            'url' => $_GET['url'],  
            'network_key' => $vNpk, 
            'language_code' => $vLanguageCode,    
            'profile_public' => $vProfilePublic,  
            'firm_building_type_id' => $vFirmBuildingTypeId,  
            'firm_building_name' => $vFirmBuildingName,  
            'address' => $vAddress,  
            'borough_id' => $vBoroughId,  
            'city_id' => $vCityId,  
            'country_id' => $vCountryId,  
            'osb_id' => $vOsbId,  
            'description' => $vDescription,  
            'description_eng' => $vDescriptionEng,
            'tel' => $vTel,  
            'fax' => $vFax,  
            'email' => $vEmail,          
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
$app->get("/pkFillUsersFirmAddressNpk_infoFirmAddress/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmAddressBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillUsersFirmAddressNpk_infoFirmAddress" end point, X-Public variable not found');
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
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    }
    if ($stripper->offsetExists('npk')){
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }
    $resDataGrid = $BLL->fillUsersFirmAddressNpk(array(
        'url' => $_GET['url'],  
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,
        'pk' => $pk,
    ));      
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "firm_id" => $flow["firm_id"],
                "firm_name" => html_entity_decode($flow["firm_name"]),
                "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
                "firm_building_type_id" => $flow["firm_building_type_id"],
                "firm_building_type" => html_entity_decode($flow["firm_building_type"]),
                "firm_building_name" => html_entity_decode($flow["firm_building_name"]),
                "firm_building_name_eng" => html_entity_decode($flow["firm_building_name_eng"]),
                "address" => html_entity_decode($flow["address"]),
                "country_id" => $flow["country_id"],
                "country_name" => html_entity_decode($flow["country_name"]),
                "city_id" => $flow["city_id"],
                "city_name" => html_entity_decode($flow["city_name"]),
                "borough_id" => $flow["borough_id"],
                "borough_name" => html_entity_decode($flow["borough_name"]),
                "osb_id" => $flow["osb_id"],
                "osb_name" => html_entity_decode($flow["osb_name"]),
                "network_key" => $flow["network_key"],
                "language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
                "email" => $flow["email"],
                "tel" => $flow["tel"],
                "fax" => $flow["fax"],
                "web_address" => html_entity_decode($flow["web_address"]),
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
 * @since 27-05-2016
 */
$app->get("/FillUsersFirmAddressNpkQuest_infoFirmAddress/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmAddressBLL');  
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
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    }
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }
    $resDataGrid = $BLL->fillUsersFirmAddressNpk(array(
        'url' => $_GET['url'],  
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,  
    ));      
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
               // "id" => $flow["id"],
                //"firm_id" => $flow["firm_id"],
                "firm_name" => html_entity_decode($flow["firm_name"]),
                "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
                //"firm_building_type_id" => $flow["firm_building_type_id"],
                "firm_building_type" => html_entity_decode($flow["firm_building_type"]),
                "firm_building_name" => html_entity_decode($flow["firm_building_name"]),
                "firm_building_name_eng" => html_entity_decode($flow["firm_building_name_eng"]),
                "address" => html_entity_decode($flow["address"]),
                //"country_id" => $flow["country_id"],
                "country_name" => html_entity_decode($flow["country_name"]),
                //"city_id" => $flow["city_id"],
                "city_name" => html_entity_decode($flow["city_name"]),
                //"borough_id" => $flow["borough_id"],
                "borough_name" => html_entity_decode($flow["borough_name"]),
                //"osb_id" => $flow["osb_id"],
                "osb_name" => html_entity_decode($flow["osb_name"]),
                "network_key" => $flow["network_key"],
                //"language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
                 "tel" => $flow["tel"],
                "fax" => $flow["fax"],
                "email" => $flow["email"],
                "web_address" => html_entity_decode($flow["web_address"]),
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
 * @since 20-05-2016
 */
$app->get("/pkFillSingularFirmAddress_infoFirmAddress/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmAddressBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillSingularFirmAddress_infoFirmAddress" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['npk']));
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
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();  
    } 
    if ($stripper->offsetExists('npk')){
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();        
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
    $resDataGrid = $BLL->fillSingularFirmAddress(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'network_key' => $vNpk,
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));   
    $resTotalRowCount = $BLL->fillSingularFirmAddressRtc(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'network_key' => $vNpk,
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));
    $counts=0;  
    $menu = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "firm_id" => $menu["firm_id"],
                "firm_name" => html_entity_decode($menu["firm_name"]),
                "firm_name_eng" => html_entity_decode($menu["firm_name_eng"]),
                "firm_building_type_id" => $menu["firm_building_type_id"],                
                "firm_building_type" => html_entity_decode($menu["firm_building_type"]),
                "firm_building_name" => html_entity_decode($menu["firm_building_name"]), 
                "firm_building_name_eng" => html_entity_decode($menu["firm_building_name_eng"]),
                "address" => html_entity_decode($menu["address"]), 
                "osb_id" => $menu["osb_id"],
                "osb_name" => html_entity_decode($menu["osb_name"]),
                "cons_allow" => html_entity_decode($menu["cons_allow"]),
                "country_id" => $menu["country_id"],
                "city_id" => $menu["city_id"],
                "borough_id" => $menu["borough_id"],
                "country_name" => html_entity_decode($menu["country_name"]),
                "city_name" => html_entity_decode($menu["city_name"]),
                "borough_name" => html_entity_decode($menu["borough_name"]),
                "state_deleted" => html_entity_decode($menu["state_deleted"]),
                "state_active" => html_entity_decode($menu["state_active"]),
                "language_name" => html_entity_decode($menu["language_name"]),
                "op_username" => $menu["op_user_name"],
                "operation_name" => html_entity_decode($menu["operation_name"]),  
                "s_date" => $menu["s_date"],
                "c_date" => $menu["c_date"],                
                "attributes" => array("notroot" => true,
                    "active" => $menu["active"],                     
                    "act_parent_id" => intval($menu["act_parent_id"]),                                        
                    "cons_allow_id" => $menu["cons_allow_id"],
                    "deleted" => $menu["deleted"],
                    "active" => $menu["active"],
                    "language_id" => $menu["language_id"],
                    "op_user_id" => $menu["op_user_id"],
                    "operation_type_id" => $menu["operation_type_id"],
                    ),
            );
        }
       $counts = $resTotalRowCount[0]['count'];
      } ELSE $menus = array(); 
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $menus;
    $app->response()->body(json_encode($resultArray));
});

$app->run();