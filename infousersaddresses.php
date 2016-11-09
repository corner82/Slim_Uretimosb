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
 * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkFillGridSingular_infoUsersAddresses/", function () use ($app ) {
 $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillGridSingular_infoUsersAddresses" end point, X-Public variable not found');     
     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    } 
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }   
    $resDataGrid = $BLL->fillGridSingular(array(
        'url' => $_GET['url'],  
        'pk' => $Pk ,
        'language_code' => $vLanguageCode 
                                            ));
    $resTotalRowCount = $BLL->fillGridSingularRowTotalCount(array(
        'url' => $_GET['url'],  
        'pk' => $Pk ,
        'language_code' => $vLanguageCode 
                                            ));
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "user_id" => $flow["user_id"],
            "name" => html_entity_decode($flow["name"]),
            "surname" => html_entity_decode($flow["surname"]),
            "deleted" => $flow["deleted"],
            "state_deleted" => html_entity_decode($flow["state_deleted"]),
            "active" => $flow["active"],
            "state_active" => html_entity_decode($flow["state_active"]),
            "language_code" => $flow["language_code"],
            "language_name" => html_entity_decode($flow["language_name"]),
            "language_parent_id" => $flow["language_parent_id"], 
            "op_user_id" => $flow["op_user_id"],
            "op_username" => $flow["op_username"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name" => html_entity_decode($flow["operation_name"]),
            "profile_public" => $flow["profile_public"],
	    "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "consultant_confirm_type" => $flow["consultant_confirm_type"],
            "confirm_id" => $flow["confirm_id"],
              
            "address_type_id" => $flow["address_type_id"],
            "address_type" => html_entity_decode($flow["address_type"]),
            "address1" => html_entity_decode($flow["address1"]),     
            "address2" => html_entity_decode($flow["address2"]),  
            "postal_code" => html_entity_decode($flow["postal_code"]),  
            "country_id" => $flow["country_id"],  
            "city_id" => $flow["city_id"],  
            "borough_id" => $flow["borough_id"],  
            "city_name" => html_entity_decode($flow["city_name"]),
            "description" => html_entity_decode($flow["description"]),
            "description_eng" => html_entity_decode($flow["description_eng"]),
            "tr_country_name" => html_entity_decode($flow["tr_country_name"]),  
            "tr_city_name" => html_entity_decode($flow["tr_city_name"]),  
            "tr_borough_name" => html_entity_decode($flow["tr_borough_name"]),  
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows; 
    $app->response()->body(json_encode($resultArray));
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 * bu  servis  kullanılmıyor
 */
$app->get("/pkFillGrid_infoUsersAddresses/", function () use ($app ) {   
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */ 
$app->get("/pkInsert_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoUsersAddresses" end point, X-Public variable not found');     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }     
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description_eng']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description']));
    }
    $vAddressTypeId = 0;
    if (isset($_GET['address_type_id'])) {
        $stripper->offsetSet('address_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['address_type_id']));
    }
    $vAddress1 = NULL;
    if (isset($_GET['address1'])) {
        $stripper->offsetSet('address1', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address1']));
    }
    $vAddress2 = NULL;
    if (isset($_GET['address2'])) {
        $stripper->offsetSet('address2', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address2']));
    }
    $vPostalCode = NULL;
    if (isset($_GET['postal_code'])) {
        $stripper->offsetSet('postal_code', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['postal_code']));
    }
    $vCountryId = 0;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['country_id']));
    }
    $vCityId = 0;
    if (isset($_GET['city_id'])) {
        $stripper->offsetSet('city_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['city_id']));
    }
    $vBoroughId = 0;
    if (isset($_GET['borough_id'])) {
        $stripper->offsetSet('borough_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['borough_id']));
    }
    $vCityName = NULL;
    if (isset($_GET['city_name'])) {
        $stripper->offsetSet('city_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['city_name']));
    }
     
    $stripper->strip();    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('address_type_id')) {
        $vAddressTypeId = $stripper->offsetGet('address_type_id')->getFilterValue();
    }
    if ($stripper->offsetExists('address1')) {
        $vAddress1 = $stripper->offsetGet('address1')->getFilterValue();
    }
    if ($stripper->offsetExists('address2')) {
        $vAddress2 = $stripper->offsetGet('address2')->getFilterValue();
    }    
    if ($stripper->offsetExists('postal_code')) {
        $vPostalCode = $stripper->offsetGet('postal_code')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_id')) {
        $vCityId = $stripper->offsetGet('city_id')->getFilterValue();
    }
    if ($stripper->offsetExists('borough_id')) {
        $vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_name')) {
        $vCityName = $stripper->offsetGet('city_name')->getFilterValue();
    }      
    $resDataInsert = $BLL->insert(array(  
            'url' => $_GET['url'],              
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,  
            'address_type_id' => $vAddressTypeId , 
            'address1' => $vAddress1 , 
            'address2' => $vAddress2 ,
            'postal_code' => $vPostalCode , 
            'country_id' => $vCountryId, 
            'city_id' => $vCityId ,
            'borough_id' => $vBoroughId ,
            'city_name' => $vCityName ,        
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng ,
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */ 
$app->get("/pkUpdate_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoUsersAddresses" end point, X-Public variable not found');      
    
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['id']));
    } 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }     
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description_eng']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description']));
    }
    $vAddressTypeId = 0;
    if (isset($_GET['address_type_id'])) {
        $stripper->offsetSet('address_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['address_type_id']));
    }
    $vAddress1 = NULL;
    if (isset($_GET['address1'])) {
        $stripper->offsetSet('address1', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address1']));
    }
    $vAddress2 = NULL;
    if (isset($_GET['address2'])) {
        $stripper->offsetSet('address2', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address2']));
    }
    $vPostalCode = NULL;
    if (isset($_GET['postal_code'])) {
        $stripper->offsetSet('postal_code', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['postal_code']));
    }
    $vCountryId = 0;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['country_id']));
    }
    $vCityId = 0;
    if (isset($_GET['city_id'])) {
        $stripper->offsetSet('city_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['city_id']));
    }
    $vBoroughId = 0;
    if (isset($_GET['borough_id'])) {
        $stripper->offsetSet('borough_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['borough_id']));
    }
    $vCityName = NULL;
    if (isset($_GET['city_name'])) {
        $stripper->offsetSet('city_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['city_name']));
    }
     
    $stripper->strip();
     if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('address_type_id')) {
        $vAddressTypeId = $stripper->offsetGet('address_type_id')->getFilterValue();
    }
    if ($stripper->offsetExists('address1')) {
        $vAddress1 = $stripper->offsetGet('address1')->getFilterValue();
    }
    if ($stripper->offsetExists('address2')) {
        $vAddress2 = $stripper->offsetGet('address2')->getFilterValue();
    }
    
    if ($stripper->offsetExists('postal_code')) {
        $vPostalCode = $stripper->offsetGet('postal_code')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_id')) {
        $vCityId = $stripper->offsetGet('city_id')->getFilterValue();
    }
    if ($stripper->offsetExists('borough_id')) {
        $vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_name')) {
        $vCityName = $stripper->offsetGet('city_name')->getFilterValue();
    }      
    $resDataInsert = $BLL->update(array(  
            'url' => $_GET['url'],  
            'id' => $vId,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,  
            'address_type_id' => $vAddressTypeId , 
            'address1' => $vAddress1 , 
            'address2' => $vAddress2 ,
            'postal_code' => $vPostalCode , 
            'country_id' => $vCountryId, 
            'city_id' => $vCityId ,
            'borough_id' => $vBoroughId ,
            'city_name' => $vCityName ,        
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng ,
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);
/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkDeletedAct_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');
    $headerParams = $app->request()->headers();    
      if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoUsersAddresses" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];   
    $vId = -1;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                    $app, $_GET['id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    $resDataUpdate = $BLL->deletedAct(array(
        'url' => $_GET['url'],  
        'id' => $vId,       
        'pk' => $pk));  
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataUpdate));
});
 

/**
 *  * Okan CIRAN
 * @since 25-01-2016
 *  bu serbis kullanılmıyor
 */
$app->get("/pkGetAll_infoUsersAddresses/", function () use ($app ) {   
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkFillUserAddressesTypes_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');
    $headerParams = $app->request()->headers();    
    $Pk = $headerParams['X-Public'];
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillUserAddressesTypes_infoUsersAddresses" end point, X-Public variable not found');     
    $componentType = 'bootstrap';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    } 
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }   
    $resCombobox = $BLL->fillUserAddressesTypes(array(
        'url' => $_GET['url'],  
        'pk' => $Pk , 
        'language_code' => $vLanguageCode )); 
    $menus = array();
    $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
    
     if ($componentType == 'bootstrap') {
        $menus = array();
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],       
                "text" => html_entity_decode($menu["name"]),
                "state" => 'open',
                "checked" => false,
                "attributes" => array("notroot" => true,   ),
            );
        }
    } else if ($componentType == 'ddslick') {        
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => html_entity_decode($menu["name"]),
                "value" => intval($menu["id"]),
                "selected" => false,
                "description" => html_entity_decode($menu["name"]),
               // "imageSrc" => ""
            );
        }
    }
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($flows));
});
 
  
/**x
 * Okan CIRAN
 * @since 02-02-2016
 *  rest servislere eklendi
 */
$app->get("/pktempFillGridSingular_infoUsersAddresses/", function () use ($app ) {
   $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');    
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public-Temp'])) {
        throw new Exception('rest api "pktempFillGridSingular_infoUsersAddresses" end point, X-Public variable not found');
    }
    $vPkTemp = $headerParams['X-Public-Temp'];
    $componentType = 'bootstrap';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
   
    
    $resDataGrid = $BLL->fillGridSingularTemp(array('pktemp' => $vPkTemp,
                                                    'language_code' => $vLanguageCode,
                                                    
                                                    ));

    $resTotalRowCount = $BLL->fillGridSingularRowTotalCountTemp(array('pktemp' => $vPkTemp,
                                                                    'language_code' => $vLanguageCode,
                                                                     ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => $flow["id"],
                "user_id" => $flow["user_id"],
                "name" => html_entity_decode($flow["name"]),
                "surname" => html_entity_decode($flow["surname"]),
                "language_name" => html_entity_decode($flow["language_name"]),
                "s_date" => $flow["s_date"],
                "c_date" => $flow["c_date"],
                "address_type_id" => $flow["address_type_id"],
                "address_type" => html_entity_decode($flow["address_type"]),
                "address1" => html_entity_decode($flow["address1"]),
                "address2" => html_entity_decode($flow["address2"]),
                "postal_code" => html_entity_decode($flow["postal_code"]),
                "country_id" => $flow["country_id"],
                "country_name" => html_entity_decode($flow["country_name"]),
                "city_id" => $flow["city_id"],
                "city_names" => html_entity_decode($flow["city_names"]),
                "borough_id" => $flow["borough_id"],
                "borough_name" => html_entity_decode($flow["borough_name"]),
                "city_name" => html_entity_decode($flow["city_name"]),
                "description" => html_entity_decode($flow["description"]),
                "attributes" => array("notroot" => true,
                    "active" => $flow["active"],
                    "profile_public" => $flow["profile_public"],),
            );
        }
        $counts = $resTotalRowCount[0]['count'];
    }

    $app->response()->header("Content-Type", "application/json");
  
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
 
    if($componentType == 'bootstrap'){
        $app->response()->body(json_encode($flows));
    }else if($componentType == 'easyui'){
        $app->response()->body(json_encode($resultArray));
    }
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 *  rest servislere eklendi
 * operasyonlara eklendi
 */
$app->get("/pktempInsert_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');   
    $headerParams = $app->request()->headers();
    $PkTemp = $headerParams['X-Public-Temp'];
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempInsert_infoUsersAddresses" end point, X-Public variable not found');      
 
     
    $vM = NULL;
    if (isset($_GET['m'])) {
        $stripper->offsetSet('m', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['m']));
    }
    $vA = NULL;
    if (isset($_GET['a'])) {
        $stripper->offsetSet('a', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['a']));
    }    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }     
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description_eng']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description']));
    }
    $vAddressTypeId = 0;
    if (isset($_GET['address_type_id'])) {
        $stripper->offsetSet('address_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['address_type_id']));
    }
    $vAddress1 = NULL;
    if (isset($_GET['address1'])) {
        $stripper->offsetSet('address1', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address1']));
    }
    $vAddress2 = NULL;
    if (isset($_GET['address2'])) {
        $stripper->offsetSet('address2', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address2']));
    }
    $vPostalCode = NULL;
    if (isset($_GET['postal_code'])) {
        $stripper->offsetSet('postal_code', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['postal_code']));
    }
    $vCountryId = 0;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['country_id']));
    }
    $vCityId = 0;
    if (isset($_GET['city_id'])) {
        $stripper->offsetSet('city_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['city_id']));
    }
    $vBoroughId = 0;
    if (isset($_GET['borough_id'])) {
        $stripper->offsetSet('borough_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['borough_id']));
    }
    $vCityName = NULL;
    if (isset($_GET['city_name'])) {
        $stripper->offsetSet('city_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['city_name']));
    }
     
    $stripper->strip();     
    if ($stripper->offsetExists('m')) {
        $vM = $stripper->offsetGet('m')->getFilterValue();
    }
     if ($stripper->offsetExists('a')) {
        $vA = $stripper->offsetGet('a')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('address_type_id')) {
        $vAddressTypeId = $stripper->offsetGet('address_type_id')->getFilterValue();
    }
    if ($stripper->offsetExists('address1')) {
        $vAddress1 = $stripper->offsetGet('address1')->getFilterValue();
    }
    if ($stripper->offsetExists('address2')) {
        $vAddress2 = $stripper->offsetGet('address2')->getFilterValue();
    }    
    if ($stripper->offsetExists('postal_code')) {
        $vPostalCode = $stripper->offsetGet('postal_code')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_id')) {
        $vCityId = $stripper->offsetGet('city_id')->getFilterValue();
    }
    if ($stripper->offsetExists('borough_id')) {
        $vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_name')) {
        $vCityName = $stripper->offsetGet('city_name')->getFilterValue();
    }      
    $resDataInsert = $BLL->insertTemp(array(  
            'url' => $_GET['url'],                 
            'm' =>$vM,
            'a' => $vA,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,  
            'address_type_id' => $vAddressTypeId , 
            'address1' => $vAddress1 , 
            'address2' => $vAddress2 ,
            'postal_code' => $vPostalCode , 
            'country_id' => $vCountryId, 
            'city_id' => $vCityId ,
            'borough_id' => $vBoroughId ,
            'city_name' => $vCityName ,        
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng ,
            'pktemp' => $PkTemp,        
            ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 *  rest servislere eklendi
 * operasyonlara eklendi
 */
$app->get("/pktempUpdate_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');   
    $headerParams = $app->request()->headers();
    $PkTemp = $headerParams['X-Public-Temp'];
    if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempUpdate_infoUsersAddresses" end point, X-Public variable not found');      
    
    $vM = NULL;
    if (isset($_GET['m'])) {
        $stripper->offsetSet('m', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['m']));
    }
    $vA = NULL;
    if (isset($_GET['a'])) {
        $stripper->offsetSet('a', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['a']));
    }   
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['id']));
    } 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, $_GET['language_code']));
    }     
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description_eng']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['description']));
    }
    $vAddressTypeId = 0;
    if (isset($_GET['address_type_id'])) {
        $stripper->offsetSet('address_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['address_type_id']));
    }
    $vAddress1 = NULL;
    if (isset($_GET['address1'])) {
        $stripper->offsetSet('address1', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address1']));
    }
    $vAddress2 = NULL;
    if (isset($_GET['address2'])) {
        $stripper->offsetSet('address2', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['address2']));
    }
    $vPostalCode = NULL;
    if (isset($_GET['postal_code'])) {
        $stripper->offsetSet('postal_code', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['postal_code']));
    }
    $vCountryId = 0;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['country_id']));
    }
    $vCityId = 0;
    if (isset($_GET['city_id'])) {
        $stripper->offsetSet('city_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['city_id']));
    }
    $vBoroughId = 0;
    if (isset($_GET['borough_id'])) {
        $stripper->offsetSet('borough_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['borough_id']));
    }
    $vCityName = NULL;
    if (isset($_GET['city_name'])) {
        $stripper->offsetSet('city_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['city_name']));
    }
     
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('m')) {
        $vM = $stripper->offsetGet('m')->getFilterValue();
    }
    if ($stripper->offsetExists('a')) {
        $vA = $stripper->offsetGet('a')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('address_type_id')) {
        $vAddressTypeId = $stripper->offsetGet('address_type_id')->getFilterValue();
    }
    if ($stripper->offsetExists('address1')) {
        $vAddress1 = $stripper->offsetGet('address1')->getFilterValue();
    }
    if ($stripper->offsetExists('address2')) {
        $vAddress2 = $stripper->offsetGet('address2')->getFilterValue();
    }    
    if ($stripper->offsetExists('postal_code')) {
        $vPostalCode = $stripper->offsetGet('postal_code')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_id')) {
        $vCityId = $stripper->offsetGet('city_id')->getFilterValue();
    }
    if ($stripper->offsetExists('borough_id')) {
        $vBoroughId = $stripper->offsetGet('borough_id')->getFilterValue();
    }
    if ($stripper->offsetExists('city_name')) {
        $vCityName = $stripper->offsetGet('city_name')->getFilterValue();
    } 
     
    $resDataInsert = $BLL->updateTemp(array(  
            'url' => $_GET['url'],  
            'm' => $vM,
            'a' => $vA,
            'id' => $vId,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,  
            'address_type_id' => $vAddressTypeId , 
            'address1' => $vAddress1 , 
            'address2' => $vAddress2 ,
            'postal_code' => $vPostalCode , 
            'country_id' => $vCountryId, 
            'city_id' => $vCityId ,
            'borough_id' => $vBoroughId ,
            'city_name' => $vCityName ,        
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng ,
            'pktemp' => $PkTemp,        
            ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataInsert));
}
);
/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 *  rest servislere eklendi
 * operasyonlara eklendi
 */
$app->get("/pktempDeletedAct_infoUsersAddresses/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');
    $headerParams = $app->request()->headers();
     if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempDeletedAct_infoUsersAddresses" end point, X-Public variable not found');
    $PkTemp = $headerParams['X-Public-Temp'];    
    $vM = NULL;
    if (isset($_GET['m'])) {
        $stripper->offsetSet('m', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['m']));
    }
    $vA = NULL;
    if (isset($_GET['a'])) {
        $stripper->offsetSet('a', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['a']));
    }   
    $vId = -1;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                    $app, $_GET['id']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('m')) {
        $vM = $stripper->offsetGet('m')->getFilterValue();
    }
     if ($stripper->offsetExists('a')) {
        $vA = $stripper->offsetGet('a')->getFilterValue();
    }
    $resDataUpdate = $BLL->deletedActTemp(array(
        'url' => $_GET['url'],  
        'm' => $vM,       
        'a' => $vA,    
        'id' => $vId,    
        'pktemp' => $PkTemp)); 
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resDataUpdate));
});
 

/** x 
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillUserAddressesTypes_infoUsersAddresses/", function () use ($app ) {
$stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoUsersAddressesBLL');
    $headerParams = $app->request()->headers();
     if (!isset($headerParams['X-Public-Temp']))
        throw new Exception('rest api "pktempDeletedAct_infoUsersAddresses" end point, X-Public variable not found');
    $PkTemp = $headerParams['X-Public-Temp'];    
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                    $app, $_GET['language_code']));
    }    
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
   
    $resCombobox = $BLL->fillUserAddressesTypesTemp(array(
            'url' => $_GET['url'],  
            'pktemp' => $PkTemp , 
            'language_code' => $vLanguageCode ));
 
    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],         
            "text" => html_entity_decode($flow["name"]),
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true,   ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});






$app->run();
