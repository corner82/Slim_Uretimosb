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
 * @since 09.02.2016
 */ 
$app->get("/pkInsert_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmProfile" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description_eng']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['profile_public']));
    }
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['country_id']));
    }
    $vOwnershipStatusId = 0;
    if (isset($_GET['ownership_status_id'])) {
        $stripper->offsetSet('ownership_status_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['ownership_status_id']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
        $stripper->offsetSet('firm_name_short', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short']));
    }
    $vFoundationYear = NULL;
    if (isset($_GET['foundation_year'])) {
        $stripper->offsetSet('foundation_year', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_year']));
    }
    $vFoundationYearx = NULL;
    if (isset($_GET['foundation_yearx'])) {
        $stripper->offsetSet('foundation_yearx', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_yearx']));
    }
    $vDunsNumber = NULL;
    if (isset($_GET['duns_number'])) {
        $stripper->offsetSet('duns_number', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['duns_number']));
    }
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
        $stripper->offsetSet('logo', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['logo']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
        $stripper->offsetSet('firm_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
        $stripper->offsetSet('firm_name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_eng']));
    }
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
        $stripper->offsetSet('firm_name_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short_eng']));
    }
    $vTaxOffice = NULL;
    if (isset($_GET['tax_office'])) {
        $stripper->offsetSet('tax_office', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_office']));
    }
    $vTaxNo = NULL;
    if (isset($_GET['tax_no'])) {
        $stripper->offsetSet('tax_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_no']));
    }
    $vSgkSicilNo = NULL;
    if (isset($_GET['sgk_sicil_no'])) {
        $stripper->offsetSet('sgk_sicil_no', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['sgk_sicil_no']));
    }
    $vWebAddress = NULL;
    if (isset($_GET['web_address'])) {
        $stripper->offsetSet('web_address', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['web_address']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('web_address')) {
        $vWebAddress = $stripper->offsetGet('web_address')->getFilterValue();
    } 
    if ($stripper->offsetExists('sgk_sicil_no')) {
        $vSgkSicilNo = $stripper->offsetGet('sgk_sicil_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_no')) {
        $vTaxNo = $stripper->offsetGet('tax_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_office')) {
        $vTaxOffice = $stripper->offsetGet('tax_office')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short_eng')) {
        $vFirmNameShortEng = $stripper->offsetGet('firm_name_short_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_eng')) {
        $vFirmNameEng = $stripper->offsetGet('firm_name_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name')) {
        $vFirmName = $stripper->offsetGet('firm_name')->getFilterValue();
    }
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }
    if ($stripper->offsetExists('duns_number')) {
        $vDunsNumber = $stripper->offsetGet('duns_number')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_yearx')) {
        $vFoundationYearx = $stripper->offsetGet('foundation_yearx')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_year')) {
        $vFoundationYear = $stripper->offsetGet('foundation_year')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short')) {
        $vFirmNameShort = $stripper->offsetGet('firm_name_short')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_status_id')) {
        $vOwnershipStatusId = $stripper->offsetGet('ownership_status_id')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }     
    $resDataInsert = $BLL->insert(array(  
            'url' => $_GET['url'],
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,        
            'firm_name' => $vFirmName , 
            'firm_name_short'=> $vFirmNameShort,
            'firm_name_eng' => $vFirmNameEng , 
            'firm_name_short_eng'=> $vFirmNameShortEng,
        
            'tax_office' => $vTaxOffice , 
            'tax_no' => $vTaxNo ,
            'sgk_sicil_no' => $vSgkSicilNo , 
            'ownership_status_id' => $vOwnershipStatusId,
            'country_id' => $vCountryId, 
            'foundation_year' => $vFoundationYear ,      
            'foundation_yearx' => $vFoundationYearx,   
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng , 
            'web_address'=> $vWebAddress,            
            'duns_number'=>$vDunsNumber,
            'logo'=>$vLogo,
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 

/* * x
 *  * Okan CIRAN
 * @since 18-05-2016
 */
$app->get("/pkInsertConsAct_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsertConsAct_infoFirmProfile" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                    $app, $_GET['language_code']));
    }
    $vOsbId = '';
    if (isset($_GET['osb_id'])) {
        $stripper->offsetSet('osb_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                    $app, $_GET['osb_id']));
    }
    $vClustersId = '';
    if (isset($_GET['clusters_id'])) {
        $stripper->offsetSet('clusters_id', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, 
                    $app, $_GET['clusters_id']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
        $stripper->offsetSet('firm_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
        $stripper->offsetSet('firm_name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                    $app, $_GET['firm_name_eng']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
        $stripper->offsetSet('firm_name_short', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                    $app, $_GET['firm_name_short']));
    }
    
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
        $stripper->offsetSet('firm_name_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                    $app, $_GET['firm_name_short_eng']));
    }    

    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('clusters_id')) {
        $vClustersId = $stripper->offsetGet('clusters_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('osb_id')) {
        $vOsbId= $stripper->offsetGet('osb_id')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name')) {
        $vFirmName = $stripper->offsetGet('firm_name')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_eng')) {
        $vFirmNameEng = $stripper->offsetGet('firm_name_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short')) {
        $vFirmNameShort = $stripper->offsetGet('firm_name_short')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short_eng')) {
        $vFirmNameShortEng = $stripper->offsetGet('firm_name_short_eng')->getFilterValue();
    } 
    $resData = $BLL->insertConsAct(array(
        'url' => $_GET['url'],
        'language_code' => $vLanguageCode,
        'firm_name' => $vFirmName,
        'firm_name_eng' => $vFirmNameEng,
        'firm_name_short' => $vFirmNameShort,
        'firm_name_short_eng' => $vFirmNameShortEng,
        'osb_id' => $vOsbId,
        'clusters_id' => $vClustersId,
        'pk' => $pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resData));
}
);


/**
 *  * Okan CIRAN
 * @since 09.02.2016
 */ 
$app->get("/pkUpdate_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoFirmProfile" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['id']));
    }
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description_eng']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['profile_public']));
    }
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['country_id']));
    }
    $vOwnershipStatusId = 0;
    if (isset($_GET['ownership_status_id'])) {
        $stripper->offsetSet('ownership_status_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['ownership_status_id']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
        $stripper->offsetSet('firm_name_short', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short']));
    }
    $vFoundationYear = NULL;
    if (isset($_GET['foundation_year'])) {
        $stripper->offsetSet('foundation_year', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_year']));
    }
    $vFoundationYearx = NULL;
    if (isset($_GET['foundation_yearx'])) {
        $stripper->offsetSet('foundation_yearx', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_yearx']));
    }
    $vDunsNumber = NULL;
    if (isset($_GET['duns_number'])) {
        $stripper->offsetSet('duns_number', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['duns_number']));
    }
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
        $stripper->offsetSet('logo', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['logo']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
        $stripper->offsetSet('firm_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
        $stripper->offsetSet('firm_name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_eng']));
    }
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
        $stripper->offsetSet('firm_name_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short_eng']));
    }
    $vTaxOffice = NULL;
    if (isset($_GET['tax_office'])) {
        $stripper->offsetSet('tax_office', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_office']));
    }
    $vTaxNo = NULL;
    if (isset($_GET['tax_no'])) {
        $stripper->offsetSet('tax_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_no']));
    }
    $vSgkSicilNo = NULL;
    if (isset($_GET['sgk_sicil_no'])) {
        $stripper->offsetSet('sgk_sicil_no', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['sgk_sicil_no']));
    }
    $vWebAddress = NULL;
    if (isset($_GET['web_address'])) {
        $stripper->offsetSet('web_address', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['web_address']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('web_address')) {
        $vWebAddress = $stripper->offsetGet('web_address')->getFilterValue();
    } 
    if ($stripper->offsetExists('sgk_sicil_no')) {
        $vSgkSicilNo = $stripper->offsetGet('sgk_sicil_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_no')) {
        $vTaxNo = $stripper->offsetGet('tax_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_office')) {
        $vTaxOffice = $stripper->offsetGet('tax_office')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short_eng')) {
        $vFirmNameShortEng = $stripper->offsetGet('firm_name_short_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_eng')) {
        $vFirmNameEng = $stripper->offsetGet('firm_name_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name')) {
        $vFirmName = $stripper->offsetGet('firm_name')->getFilterValue();
    }
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }
    if ($stripper->offsetExists('duns_number')) {
        $vDunsNumber = $stripper->offsetGet('duns_number')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_yearx')) {
        $vFoundationYearx = $stripper->offsetGet('foundation_yearx')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_year')) {
        $vFoundationYear = $stripper->offsetGet('foundation_year')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short')) {
        $vFirmNameShort = $stripper->offsetGet('firm_name_short')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_status_id')) {
        $vOwnershipStatusId = $stripper->offsetGet('ownership_status_id')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }     
    $resDataInsert = $BLL->update(array(  
            'url' => $_GET['url'],
            'id' => $vId,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,        
            'firm_name' => $vFirmName , 
            'firm_name_short'=> $vFirmNameShort,
            'firm_name_eng' => $vFirmNameEng , 
            'firm_name_short_eng'=> $vFirmNameShortEng,
        
            'tax_office' => $vTaxOffice , 
            'tax_no' => $vTaxNo ,
            'sgk_sicil_no' => $vSgkSicilNo , 
            'ownership_status_id' => $vOwnershipStatusId,
            'country_id' => $vCountryId, 
            'foundation_year' => $vFoundationYear ,      
            'foundation_yearx' => $vFoundationYearx,   
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng , 
            'web_address'=> $vWebAddress,            
            'duns_number'=>$vDunsNumber,
            'logo'=>$vLogo,
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
);
/* * x
 *  * Okan CIRAN
 * @since 18-05-2016
 */
$app->get("/pkUpdateConsAct_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdateConsAct_infoFirmProfile" end point, X-Public variable not found');
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
    $vOsbId = '';
    if (isset($_GET['osb_id'])) {
        $stripper->offsetSet('osb_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                    $app, $_GET['osb_id']));
    }
    $vClustersId = '';
    if (isset($_GET['clusters_id'])) {
        $stripper->offsetSet('clusters_id', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1, 
                    $app, $_GET['clusters_id']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
        $stripper->offsetSet('firm_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1,
                                                $app,
                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
        $stripper->offsetSet('firm_name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                    $app, $_GET['firm_name_eng']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
        $stripper->offsetSet('firm_name_short', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                    $app, $_GET['firm_name_short']));
    }
    
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
        $stripper->offsetSet('firm_name_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                    $app, $_GET['firm_name_short_eng']));
    }
    

    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if ($stripper->offsetExists('clusters_id')) {
        $vClustersId = $stripper->offsetGet('clusters_id')->getFilterValue();
    }    
    if ($stripper->offsetExists('osb_id')) {
        $vOsbId= $stripper->offsetGet('osb_id')->getFilterValue();
    }
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name')) {
        $vFirmName = $stripper->offsetGet('firm_name')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_eng')) {
        $vFirmNameEng = $stripper->offsetGet('firm_name_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short')) {
        $vFirmNameShort = $stripper->offsetGet('firm_name_short')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short_eng')) {
        $vFirmNameShortEng = $stripper->offsetGet('firm_name_short_eng')->getFilterValue();
    } 
    $resData = $BLL->updateConsAct(array(
        'url' => $_GET['url'],
        'id' => $vId,
        'language_code' => $vLanguageCode,
        'firm_name' => $vFirmName,
        'firm_name_eng' => $vFirmNameEng,
        'firm_name_short' => $vFirmNameShort,
        'firm_name_short_eng' => $vFirmNameShortEng,
        'osb_id' => $vOsbId,
        'clusters_id' => $vClustersId,
        'pk' => $pk,
    ));
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($resData));
}
);
 

/**
 *  * Okan CIRAN
 * @since 09.02.2016
 */
$app->get("/pkDeletedAct_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmProfile" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];   
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
 */
$app->get("/pkGetAll_infoFirmProfile/", function () use ($app ) {   
});

/**
 *  * Okan CIRAN
 * @since 09.02.2016
 */
$app->get("/pkFillUserAddressesTypes_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillUserAddressesTypes_infoFirmProfile" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
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
        'pk' => $pk , 
        'language_code' => $vLanguageCode 
                                   ));
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
 
  
/**x
 * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillGridSingular_infoFirmProfile/", function () use ($app ) {
 $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();    
    $vPkTemp = $headerParams['X-Public-Temp'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                    $app, $_GET['language_code']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    $resDataGrid = $BLL->fillGridSingularTemp(array(
        'url' => $_GET['url'],
        'pktemp' => $vPkTemp,
        'language_code' => $vLanguageCode ));
    $resTotalRowCount = $BLL->fillGridSingularRowTotalCountTemp(array(
        'url' => $_GET['url'],
        'pktemp' => $vPkTemp,
        'language_code' => $vLanguageCode ));
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "profile_public" => $flow["profile_public"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_names" => html_entity_decode($flow["operation_names"]),
            "firm_names" => html_entity_decode($flow["firm_names"]),
            "web_address" => html_entity_decode($flow["web_address"]),
            "tax_office" => html_entity_decode($flow["tax_office"]),
            "tax_no" => html_entity_decode($flow["tax_no"]),
            "sgk_sicil_no" => html_entity_decode($flow["sgk_sicil_no"]),
            "ownership_status_id" => $flow["ownership_status_id"],
            "owner_ships" => html_entity_decode($flow["owner_ships"]),
            "foundation_year" => $flow["foundation_year"],
            "act_parent_id" => $flow["act_parent_id"],
            "language_code" => $flow["language_code"],
            "language_id" => $flow["language_id"],
            "language_names" => html_entity_decode($flow["language_names"]),
            "active" => $flow["active"],
            "state_actives" => html_entity_decode($flow["state_actives"]),
            "deleted" => $flow["deleted"],
            "state_deleteds" => html_entity_decode($flow["state_deleteds"]),
            "op_user_id" => $flow["op_user_id"],
            "username" => html_entity_decode($flow["username"]),
            "auth_allow_id" => $flow["auth_allow_id"],
            "auth_alows" => html_entity_decode($flow["auth_alows"]),
            "cons_allow_id" => $flow["cons_allow_id"],
            "cons_allows" => html_entity_decode($flow["cons_allows"]),
            "language_parent_id" => $flow["language_parent_id"],
            "firm_name_short" => html_entity_decode($flow["firm_name_short"]),
            "country_id" => $flow["country_id"],
            "country_names" => html_entity_decode($flow["country_names"]),
            "descriptions" => html_entity_decode($flow["descriptions"]),
            "duns_number" => html_entity_decode($flow["duns_number"]),
            "owner_user_id" => $flow["owner_user_id"],
            "owner_username" => html_entity_decode($flow["owner_username"]),
            "network_key" => $flow["network_key"],
            "logo" => $flow["logo"],            
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }  
    $app->response()->header("Content-Type", "application/json");   
    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempInsert_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');   
    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp']; 
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
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description_eng']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['profile_public']));
    }
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['country_id']));
    }
    $vOwnershipStatusId = 0;
    if (isset($_GET['ownership_status_id'])) {
        $stripper->offsetSet('ownership_status_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['ownership_status_id']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
        $stripper->offsetSet('firm_name_short', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short']));
    }
    $vFoundationYear = NULL;
    if (isset($_GET['foundation_year'])) {
        $stripper->offsetSet('foundation_year', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_year']));
    }
    $vFoundationYearx = NULL;
    if (isset($_GET['foundation_yearx'])) {
        $stripper->offsetSet('foundation_yearx', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_yearx']));
    }
    $vDunsNumber = NULL;
    if (isset($_GET['duns_number'])) {
        $stripper->offsetSet('duns_number', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['duns_number']));
    }
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
        $stripper->offsetSet('logo', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['logo']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
        $stripper->offsetSet('firm_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
        $stripper->offsetSet('firm_name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_eng']));
    }
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
        $stripper->offsetSet('firm_name_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short_eng']));
    }
    $vTaxOffice = NULL;
    if (isset($_GET['tax_office'])) {
        $stripper->offsetSet('tax_office', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_office']));
    }
    $vTaxNo = NULL;
    if (isset($_GET['tax_no'])) {
        $stripper->offsetSet('tax_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_no']));
    }
    $vSgkSicilNo = NULL;
    if (isset($_GET['sgk_sicil_no'])) {
        $stripper->offsetSet('sgk_sicil_no', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['sgk_sicil_no']));
    }
    $vWebAddress = NULL;
    if (isset($_GET['web_address'])) {
        $stripper->offsetSet('web_address', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['web_address']));
    }
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('m')) {
        $vM = $stripper->offsetGet('m')->getFilterValue();
    } 
    if ($stripper->offsetExists('a')) {
        $vA = $stripper->offsetGet('a')->getFilterValue();
    } 
    if ($stripper->offsetExists('web_address')) {
        $vWebAddress = $stripper->offsetGet('web_address')->getFilterValue();
    } 
    if ($stripper->offsetExists('sgk_sicil_no')) {
        $vSgkSicilNo = $stripper->offsetGet('sgk_sicil_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_no')) {
        $vTaxNo = $stripper->offsetGet('tax_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_office')) {
        $vTaxOffice = $stripper->offsetGet('tax_office')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short_eng')) {
        $vFirmNameShortEng = $stripper->offsetGet('firm_name_short_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_eng')) {
        $vFirmNameEng = $stripper->offsetGet('firm_name_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name')) {
        $vFirmName = $stripper->offsetGet('firm_name')->getFilterValue();
    }
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }
    if ($stripper->offsetExists('duns_number')) {
        $vDunsNumber = $stripper->offsetGet('duns_number')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_yearx')) {
        $vFoundationYearx = $stripper->offsetGet('foundation_yearx')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_year')) {
        $vFoundationYear = $stripper->offsetGet('foundation_year')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short')) {
        $vFirmNameShort = $stripper->offsetGet('firm_name_short')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_status_id')) {
        $vOwnershipStatusId = $stripper->offsetGet('ownership_status_id')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }     
    $resDataInsert = $BLL->insertTemp(array(  
            'url' => $_GET['url'],
            'm' => $vM,
            'a' => $vA,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,        
            'firm_name' => $vFirmName , 
            'firm_name_short'=> $vFirmNameShort,
            'firm_name_eng' => $vFirmNameEng , 
            'firm_name_short_eng'=> $vFirmNameShortEng,        
            'tax_office' => $vTaxOffice , 
            'tax_no' => $vTaxNo ,
            'sgk_sicil_no' => $vSgkSicilNo , 
            'ownership_status_id' => $vOwnershipStatusId,
            'country_id' => $vCountryId, 
            'foundation_year' => $vFoundationYear ,      
            'foundation_yearx' => $vFoundationYearx,   
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng , 
            'web_address'=> $vWebAddress,            
            'duns_number'=>$vDunsNumber,
            'logo'=>$vLogo,
            'pktemp' => $vPkTemp,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */ 
$app->get("/pktempUpdate_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');   
    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp']; 
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
                                                                $app, 
                                                                $_GET['language_code']));
    }
    $vId = 0;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['id']));
    }    
    $vDescription = NULL;
    if (isset($_GET['description'])) {
        $stripper->offsetSet('description', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description']));
    }
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['description_eng']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['profile_public']));
    }
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
        $stripper->offsetSet('country_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['country_id']));
    }
    $vOwnershipStatusId = 0;
    if (isset($_GET['ownership_status_id'])) {
        $stripper->offsetSet('ownership_status_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['ownership_status_id']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
        $stripper->offsetSet('firm_name_short', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short']));
    }
    $vFoundationYear = NULL;
    if (isset($_GET['foundation_year'])) {
        $stripper->offsetSet('foundation_year', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_year']));
    }
    $vFoundationYearx = NULL;
    if (isset($_GET['foundation_yearx'])) {
        $stripper->offsetSet('foundation_yearx', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                                $app, 
                                                                $_GET['foundation_yearx']));
    }
    $vDunsNumber = NULL;
    if (isset($_GET['duns_number'])) {
        $stripper->offsetSet('duns_number', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['duns_number']));
    }
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
        $stripper->offsetSet('logo', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['logo']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
        $stripper->offsetSet('firm_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
        $stripper->offsetSet('firm_name_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_eng']));
    }
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
        $stripper->offsetSet('firm_name_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL1, 
                                                                $app, 
                                                                $_GET['firm_name_short_eng']));
    }
    $vTaxOffice = NULL;
    if (isset($_GET['tax_office'])) {
        $stripper->offsetSet('tax_office', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_office']));
    }
    $vTaxNo = NULL;
    if (isset($_GET['tax_no'])) {
        $stripper->offsetSet('tax_no', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                                                                $app, 
                                                                $_GET['tax_no']));
    }
    $vSgkSicilNo = NULL;
    if (isset($_GET['sgk_sicil_no'])) {
        $stripper->offsetSet('sgk_sicil_no', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['sgk_sicil_no']));
    }
    $vWebAddress = NULL;
    if (isset($_GET['web_address'])) {
        $stripper->offsetSet('web_address', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                                $app, 
                                                                $_GET['web_address']));
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
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('web_address')) {
        $vWebAddress = $stripper->offsetGet('web_address')->getFilterValue();
    } 
    if ($stripper->offsetExists('sgk_sicil_no')) {
        $vSgkSicilNo = $stripper->offsetGet('sgk_sicil_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_no')) {
        $vTaxNo = $stripper->offsetGet('tax_no')->getFilterValue();
    }
    if ($stripper->offsetExists('tax_office')) {
        $vTaxOffice = $stripper->offsetGet('tax_office')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short_eng')) {
        $vFirmNameShortEng = $stripper->offsetGet('firm_name_short_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_eng')) {
        $vFirmNameEng = $stripper->offsetGet('firm_name_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name')) {
        $vFirmName = $stripper->offsetGet('firm_name')->getFilterValue();
    }
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }
    if ($stripper->offsetExists('duns_number')) {
        $vDunsNumber = $stripper->offsetGet('duns_number')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_yearx')) {
        $vFoundationYearx = $stripper->offsetGet('foundation_yearx')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_year')) {
        $vFoundationYear = $stripper->offsetGet('foundation_year')->getFilterValue();
    }
    if ($stripper->offsetExists('firm_name_short')) {
        $vFirmNameShort = $stripper->offsetGet('firm_name_short')->getFilterValue();
    }
    if ($stripper->offsetExists('ownership_status_id')) {
        $vOwnershipStatusId = $stripper->offsetGet('ownership_status_id')->getFilterValue();
    }
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }     
    $resDataInsert = $BLL->insertTemp(array(  
            'url' => $_GET['url'],
            'm' => $vM,
            'a' => $vA,
            'id' => $vId,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,        
            'firm_name' => $vFirmName , 
            'firm_name_short'=> $vFirmNameShort,
            'firm_name_eng' => $vFirmNameEng , 
            'firm_name_short_eng'=> $vFirmNameShortEng,        
            'tax_office' => $vTaxOffice , 
            'tax_no' => $vTaxNo ,
            'sgk_sicil_no' => $vSgkSicilNo , 
            'ownership_status_id' => $vOwnershipStatusId,
            'country_id' => $vCountryId, 
            'foundation_year' => $vFoundationYear ,      
            'foundation_yearx' => $vFoundationYearx,   
            'description' => $vDescription ,
            'description_eng' => $vDescriptionEng , 
            'web_address'=> $vWebAddress,            
            'duns_number'=>$vDunsNumber,
            'logo'=>$vLogo,
            'pktemp' => $vPkTemp,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 
/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempDeletedAct_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];      
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
                                                                $app, 
                                                                $_GET['id']));
    }        
    $stripper->strip();  
    if ($stripper->offsetExists('m')) {
        $vM = $stripper->offsetGet('m')->getFilterValue();
    }     
    if ($stripper->offsetExists('a')) {
        $vA = $stripper->offsetGet('a')->getFilterValue();
    } 
     if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    $resDataUpdate = $BLL->deletedActTemp(array(
        'url' => $_GET['url'],
        'm' => $vM,  
        'a' => $vA,  
        'id' => $vId,  
        'pktemp' => $vPkTemp)); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataUpdate));
});
 
/** x 
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillUserAddressesTypes_infoFirmProfile/", function () use ($app ) { 
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];      
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
    $resCombobox = $BLL->fillUserAddressesTypesTemp(array(
        'url' => $_GET['url'],
        'pktemp' => $vPkTemp , 
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
  
 /**
 *  * Okan CIRAN
 * @since 25-01-2016
 */
$app->get("/fillCompanyListsGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, 
                $app, 
                $_GET['language_code']));
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
    $resDataGrid = $BLL->fillCompanyListsGuest(array(
        'url' => $_GET['url'],
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'language_code' => $vLanguageCode,
        ));

    $resTotalRowCount = $BLL->fillCompanyListsGuestRtc(array(
        'url' => $_GET['url'],        
        'language_code' => $vLanguageCode,
        ));
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "npk" => $flow["npk"],
            "firm_names" => html_entity_decode($flow["firm_names"]),
            "web_address" => html_entity_decode($flow["web_address"]),
            "firm_name_short" => html_entity_decode($flow["firm_name_short"]),
            "country_id" => $flow["country_id"],
            "country_names" => html_entity_decode($flow["country_names"]),
            "descriptions" => html_entity_decode($flow["descriptions"]),
            "logo" => $flow["logo"], 
            "attributes" => array("notroot" => true, ),
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
 * @since 02-05-2016
 */
$app->get("/pkFillCompanyLists_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyLists_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
     $vPage = NULL;
    if (isset($_GET['page'])) {
         $stripper->offsetSet('page',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['page']));
    }       
    $vRows = NULL;
    if (isset($_GET['rows'])) {
         $stripper->offsetSet('rows',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rows']));
    }   
    $vSort = NULL;
    if (isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sort']));
    }
    $vOrder = NULL;
    if (isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ORDER,
                                                $app,
                                                $_GET['order']));
    }     
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
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
   
    $resDataGrid = $BLL->fillCompanyListsGuest(array( 
        'url' => $_GET['url'],
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,  
        'language_code' => $vLanguageCode,
        'pk' => $pk,
        ));
    $resTotalRowCount = $BLL->fillCompanyListsGuestRtc(array( 
        'url' => $_GET['url'],        
        'language_code' => $vLanguageCode,
        'pk' => $pk,
        ));
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "npk" => $flow["npk"],
            "firm_names" => html_entity_decode($flow["firm_names"]),
            "web_address" => html_entity_decode($flow["web_address"]),
            "firm_name_short" => html_entity_decode($flow["firm_name_short"]),
            "country_id" => $flow["country_id"],
            "country_names" => html_entity_decode($flow["country_names"]),
            "descriptions" => html_entity_decode($flow["descriptions"]),
            "logo" => $flow["logo"], 
            "attributes" => array("notroot" => true, ),
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
 * @since 23-03-2016
 */
$app->get("/fillCompanyInfoEmployeesGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoEmployeesGuest(array(
        'url' => $_GET['url'],  
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(            
            "logo" => $flow["logo"], 
            "number_of_employees" => $flow["number_of_employees"],
            "number_of_worker" => $flow["number_of_worker"], 
            "number_of_technician" => $flow["number_of_technician"], 
            "number_of_engineer" => $flow["number_of_engineer"], 
            "number_of_administrative_staff" => $flow["number_of_administrative_staff"], 
            "number_of_sales_staff" => $flow["number_of_sales_staff"], 
            "number_of_foreign_trade_staff" => $flow["number_of_foreign_trade_staff"],            
            "attributes" => array("notroot" => true, ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
/**
 *  * Okan CIRAN
* @since 02-05-2016
 */
$app->get("/pkFillCompanyInfoEmployees_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoEmployees_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoEmployeesGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,    
        'pk' => $pk,    
        ));     
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(            
            "logo" => $flow["logo"], 
            "number_of_employees" => $flow["number_of_employees"],
            "number_of_worker" => $flow["number_of_worker"], 
            "number_of_technician" => $flow["number_of_technician"], 
            "number_of_engineer" => $flow["number_of_engineer"], 
            "number_of_administrative_staff" => $flow["number_of_administrative_staff"], 
            "number_of_sales_staff" => $flow["number_of_sales_staff"], 
            "number_of_foreign_trade_staff" => $flow["number_of_foreign_trade_staff"],            
            "attributes" => array("notroot" => true, ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
  
/**
 *  * Okan CIRAN
 * @since 23-03-2016
 */
$app->get("/fillCompanyInfoSocialediaGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
     $result = $BLL->fillCompanyInfoSocialediaGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        ));      
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "socialmedia" => html_entity_decode($flow["socialmedia"]),
            "firm_link" => html_entity_decode($flow["firm_link"]),
            "abbreviation" => html_entity_decode($flow["abbreviation"]),
            "attributes" => array("notroot" => true, ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
* @since 02-05-2016
 */
$app->get("/pkFillCompanyInfoSocialedia_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers(); 
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoSocialedia_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoSocialediaGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,     
        'pk' => $pk,  
        ));      
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "socialmedia" => html_entity_decode($flow["socialmedia"]),
            "firm_link" => html_entity_decode($flow["firm_link"]),      
            "abbreviation" => html_entity_decode($flow["abbreviation"]),  
            "attributes" => array("notroot" => true, ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 23-03-2016
 */
$app->get("/fillCompanyInfoReferencesGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoReferencesGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        ));     
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "ref_name" => html_entity_decode($flow["ref_name"]),    
            "ref_date" => $flow["ref_date"],    
            "ref_network_key" => $flow["ref_network_key"],    
            "ref_logo" => $flow["ref_logo"],   
            "firm_logo" => $flow["firm_logo"],               
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
    /**
 *  * Okan CIRAN
* @since 02-05-2016
 */
$app->get("/pkFillCompanyInfoReferences_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers(); 
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoReferences_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }

    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoReferencesGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey, 
        'pk' => $pk,       
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "ref_name" => html_entity_decode($flow["ref_name"]),    
            "ref_date" => $flow["ref_date"],    
            "ref_network_key" => $flow["ref_network_key"],    
            "ref_logo" => $flow["ref_logo"],   
            "firm_logo" => $flow["firm_logo"],    
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 23-03-2016
 */
$app->get("/fillCompanyInfoCustomersGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoCustomersGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "customer_names" => html_entity_decode($flow["customer_names"]),            
            "attributes" => array("notroot" => true, ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
* @since 02-05-2016
 */
$app->get("/pkFillCompanyInfoCustomers_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers(); 
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoCustomers_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public']; 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoCustomersGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,    
        'pk' => $pk,  
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "customer_names" => html_entity_decode($flow["customer_names"]),            
            "attributes" => array("notroot" => true, ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
 * @since 15-04-2016
 */
$app->get("/fillCompanyInfoProductsGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();        
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue(); 
    }
     $result = $BLL->fillCompanyInfoProductsGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],    
            "product_name" => html_entity_decode($flow["product_name"]),    
            "product_description" => html_entity_decode($flow["product_description"]),    
            "gtip_no_id" => $flow["gtip_no_id"],   
            "product_picture" => $flow["product_picture"],  
            "product_video_link" => $flow["product_video_link"],              
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
/**
 *  * Okan CIRAN
* @since 02-05-2016
 */
$app->get("/pkFillCompanyInfoProducts_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers(); 
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoProducts_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoProductsGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,  
        'pk' => $pk,
        ));      
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],    
            "product_name" => html_entity_decode($flow["product_name"]),    
            "product_description" => html_entity_decode($flow["product_description"]),    
            "gtip_no_id" => $flow["gtip_no_id"],   
            "product_picture" => $flow["product_picture"],  
            "product_video_link" => $flow["product_video_link"],              
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 15-04-2016
 */
$app->get("/fillCompanyInfoSectorsGuest_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL'); 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoSectorsGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],    
            "sector_name" => html_entity_decode($flow["sector_name"]),  
            "logo" => $flow["logo"],              
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});

/**
 *  * Okan CIRAN
* @since 02-05-2016
 */
$app->get("/pkFillCompanyInfoSectors_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers(); 
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoSectors_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoSectorsGuest(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,  
        'pk' => $pk,
        ));      
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],    
            "sector_name" => html_entity_decode($flow["sector_name"]),  
            "logo" => $flow["logo"],              
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
  
/**
 *  * Okan CIRAN
 * @since 15-04-2016
 */
$app->get("/pkFillCompanyInfoBuildingNpk_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();     
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillCompanyInfoBuildingNpk_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $vBuildingTypeId = NULL;
    if (isset($_GET['building_type_id'])) {
        $stripper->offsetSet('building_type_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['building_type_id']));
    } 
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    }
    if($stripper->offsetExists('building_type_id')) {
        $vBuildingTypeId = $stripper->offsetGet('building_type_id')->getFilterValue();
    } 
    $result = $BLL->fillCompanyInfoBuildingNpk(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,
        'building_type_id' => $vBuildingTypeId,
        'pk' => $pk,
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],  
            "firm_id" => $flow["firm_id"],   
            "building_type" => html_entity_decode($flow["building_type"]),  
            "firm_building_name" => html_entity_decode($flow["firm_building_name"]),   
            "osb_name" => html_entity_decode($flow["osb_name"]),
            "building_address" => html_entity_decode($flow["building_address"]),
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
  
/**
 *  * Okan CIRAN
 * @since 23-05-2016
 */
$app->get("/pkFillFirmFullVerbal_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();     
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillFirmFullVerbal_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();    
    } 
    $result = $BLL->fillFirmFullVerbal(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        'pk' => $pk,
        ));
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "id" => $flow["id"],  
           // "verbal_id" => $flow["verbal_id"], 
            "cpk" => $flow["cpk"],   
            "profile_public" => $flow["profile_public"],  
            "s_date" => $flow["s_date"],   
            "c_date" => $flow["c_date"],
            "firm_name" => html_entity_decode($flow["firm_name"]),
            "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
            "firm_name_short" => html_entity_decode($flow["firm_name_short"]),
            "firm_name_short_eng" => html_entity_decode($flow["firm_name_short_eng"]),
            "web_address" => html_entity_decode($flow["web_address"]),
            "country_id" => $flow["country_id"],
            "country_name" => html_entity_decode($flow["country_name"]),
            "country_name_eng" => html_entity_decode($flow["country_name_eng"]),
            "about" => html_entity_decode($flow["about"]),
            "about_eng" => html_entity_decode($flow["about_eng"]),
            "verbal1_title" => html_entity_decode($flow["verbal1_title"]),
            "verbal1_title_eng" => html_entity_decode($flow["verbal1_title_eng"]),
            "verbal1" => html_entity_decode($flow["verbal1"]),
            "verbal1_eng" => html_entity_decode($flow["verbal1_eng"]),            
            "verbal2_title" => html_entity_decode($flow["verbal2_title"]),
            "verbal2_title_eng" => html_entity_decode($flow["verbal2_title_eng"]),
            "verbal2" => html_entity_decode($flow["verbal2"]),
            "verbal2_eng" => html_entity_decode($flow["verbal2_eng"]),
            "verbal3_title" => html_entity_decode($flow["verbal3_title"]),
            "verbal3_title_eng" => html_entity_decode($flow["verbal3_title_eng"]),
            "verbal3" => html_entity_decode($flow["verbal3"]),
            "verbal3_eng" => html_entity_decode($flow["verbal3_eng"]),
            "duns_number" => $flow["duns_number"],
            "tax_office" => html_entity_decode($flow["tax_office"]),
            "tax_no" => $flow["tax_no"],
            "foundation_yearx" => $flow["foundation_yearx"],            
            "language_id" => $flow["language_id"],
            "language_name" => html_entity_decode($flow["language_name"]),
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name" => html_entity_decode($flow["operation_name"]),
            "operation_name_eng" => html_entity_decode($flow["operation_name_eng"]),
            "active" => $flow["active"],
            "state_active" => html_entity_decode($flow["state_active"]),
            "deleted" => $flow["deleted"],
            "state_deleted" => html_entity_decode($flow["state_deleted"]),
            "op_user_id" => $flow["op_user_id"],
            "username" => $flow["username"],
            "auth_allow_id" => $flow["auth_allow_id"],
            "auth_alow" => html_entity_decode($flow["auth_alow"]),
            "cons_allow_id" => $flow["cons_allow_id"], 
            "cons_allow" => html_entity_decode($flow["cons_allow"]),
            "language_parent_id" => $flow["language_parent_id"],
            "network_key" => $flow["network_key"],
            "logo" => $flow["logo"],
            "place_point" => $flow["place_point"],              
            "attributes" => array("notroot" => true,"active" => $flow["active"], ),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});


/**
 *  * Okan CIRAN
 * @since 23-05-2016
 */
$app->get("/pkGetFirmProfileConsultant_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();     
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkgetFirmProfileConsultant_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }     
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
    if($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();    
    } 
    $result = $BLL->getFirmProfileConsultant(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
        'pk' => $pk,
        ));  
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
            "firm_id" => $flow["firm_id"],
            "consultant_id" => $flow["consultant_id"],  
            "name" => html_entity_decode($flow["name"]),   
            "surname" => html_entity_decode($flow["surname"]),
            "auth_email" => $flow["auth_email"],
            //"communications_type_id" => $flow["communications_type_id"],
           // "communications_type_name" => $flow["communications_type_name"],             
         //   "communications_no" => $flow["communications_no"],
            "cons_picture" => $flow["cons_picture"],
            "npk" => $flow["network_key"],  
            "attributes" => array(),
        );
    } 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});
  
/**
 *  * Okan CIRAN
 * @since 15-07-2016
 */
$app->get("/pkFillConsultantAllowFirmListDds_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();     
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillConsultantAllowFirmListDds_infoFirmProfile" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];  
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }       
    $stripper->strip();
    if($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')){
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
        }
    $resCombobox = $BLL->fillConsultantAllowFirmListDds(array(
        'url' => $_GET['url'], 
        'language_code' => $vLanguageCode,
        'pk' => $pk,
    ));
        $menus = array();
        $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
    if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "text" => html_entity_decode($menu["name"]),
                "state" => html_entity_decode($menu["state_type"]), //   'closed',
                "checked" => false,
                "attributes" => array("notroot" => true, "active" => $menu["active"]),
            );
        }
    } else if ($componentType == 'ddslick') {       
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => html_entity_decode($menu["name"]),
                "value" =>  intval($menu["id"]),
                "selected" => false,
                "description" => html_entity_decode($menu["name_eng"]),
                "imageSrc" => ""
            );
        }
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($menus));
});


/**
 *  * Okan CIRAN
 * @since 22-08-2016
 */
$app->get("/pkFillConsCompanyLists_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillConsCompanyLists_infoFirmProfile" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];          
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
    $resDataGrid = $BLL->fillConsCompanyLists(array( 
        'url' => $_GET['url'],
        'page' => $vPage,      
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,        
        'filterRules' => $filterRules,
        'pk' => $pk,
    ));  
    $resTotalRowCount = $BLL->fillConsCompanyListsRtc(array( 
        'url' => $_GET['url'],
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
                "firm_name_short" => html_entity_decode($menu["firm_name_short"]),
                "firm_name_short_eng" => html_entity_decode($menu["firm_name_short_eng"]),               
                "state_active" => html_entity_decode($menu["state_active"]),
                "osb_id" => $menu["osb_id"], 
                "cluster_ids" => $menu["cluster_ids"], 
                "attributes" => array("active" => $menu["active"],   ),                   
            );
        }
       $counts = $resTotalRowCount[0]['count'];
      } ELSE { $menus = array(); }   
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $menus;
    $app->response()->body(json_encode($resultArray));
});


 /**x
 *  * Okan CIRAN
 * @since 22-08-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_infoFirmProfile/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmProfileBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkUpdateMakeActiveOrPassive_infoFirmProfile" end point, X-Public variable not found');
    }
    $Pk = $headerParams['X-Public'];      
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
    $resData = $BLL->makeActiveOrPassive(array(     
            'url' => $_GET['url'],
            'id' => $vId ,    
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 



$app->run();
