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
 * @since 26-04-2016
 */
$app->get("/pkcpkInsert_infoFirmVerbal/", function () use ($app ) {  
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');    
  //  $BLLProfile = $app->getBLLManager()->get('infoFirmVerbalBLL');    
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkcpkInsert_infoFirmVerbal" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
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
    $vCpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['cpk']));
    }
    /*
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }     
    */
    $vAbout = NULL;
    if (isset($_GET['about'])) {
         $stripper->offsetSet('about',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['about']));
    }   
    $vAboutEng = NULL;
    if (isset($_GET['about_eng'])) {
         $stripper->offsetSet('about_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['about_eng']));
    }   
    $vVerbal1Title = NULL;
    if (isset($_GET['verbal1_title'])) {
         $stripper->offsetSet('verbal1_title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1_title']));
    }   
    $vVerbal1 = NULL;
    if (isset($_GET['verbal1'])) {
         $stripper->offsetSet('verbal1',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1']));
    }        
    $vVerbal2Title = NULL;
    if (isset($_GET['verbal2_title'])) {
         $stripper->offsetSet('verbal2_title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2_title']));
    }   
    $vVerbal2 = NULL;
    if (isset($_GET['verbal2'])) {
         $stripper->offsetSet('verbal2',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2']));
    }        
    $vVerbal3Title = NULL;
    if (isset($_GET['verbal3_title'])) {
         $stripper->offsetSet('verbal3_title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3_title']));
    }   
    $vVerbal3 = NULL;
    if (isset($_GET['verbal3'])) {
         $stripper->offsetSet('verbal3',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3']));
    }
    $vVerbal1TitleEng = NULL;
    if (isset($_GET['verbal1_title_eng'])) {
         $stripper->offsetSet('verbal1_title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1_title_eng']));
    }
    $vVerbal1Eng = NULL;
    if (isset($_GET['verbal1_eng'])) {
         $stripper->offsetSet('verbal1_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1_eng']));
    }
    $vVerbal2TitleEng = NULL;
    if (isset($_GET['verbal2_title_eng'])) {
         $stripper->offsetSet('verbal2_title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2_title_eng']));
    }
    $vVerbal2Eng = NULL;
    if (isset($_GET['verbal2_eng'])) {
         $stripper->offsetSet('verbal2_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2_eng']));
    }
    $vVerbal3TitleEng = NULL;
    if (isset($_GET['verbal3_title_eng'])) {
         $stripper->offsetSet('verbal3_title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3_title_eng']));
    }
    $vVerbal3Eng = NULL;
    if (isset($_GET['verbal3_eng'])) {
         $stripper->offsetSet('verbal3_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3_eng']));
    } 
    $vFoundationYearx = NULL;
    if (isset($_GET['foundation_yearx'])) {
        $stripper->offsetSet('foundation_yearx', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['foundation_yearx']));
    } 
    $vTaxOffice = NULL;
    if (isset($_GET['tax_office'])) {
         $stripper->offsetSet('tax_office',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['tax_office']));
    } 
    $vTaxNo = NULL;
    if (isset($_GET['tax_no'])) {
         $stripper->offsetSet('tax_no',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['tax_no']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
         $stripper->offsetSet('firm_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
         $stripper->offsetSet('firm_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name_eng']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
         $stripper->offsetSet('firm_name_short',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name_short']));
    }
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
         $stripper->offsetSet('firm_name_short_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name_short_eng']));
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
    $vDunsNumber = NULL;
    if (isset($_GET['duns_number'])) {
         $stripper->offsetSet('duns_number',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['duns_number']));
    }
    $vSgkSicilNo = NULL;
    if (isset($_GET['sgk_sicil_no'])) {
         $stripper->offsetSet('sgk_sicil_no',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sgk_sicil_no']));
    }
    $vWebAddress = NULL;
    if (isset($_GET['web_address'])) {
         $stripper->offsetSet('web_address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['web_address']));
    }
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
         $stripper->offsetSet('logo',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['logo']));
    }
     
    $stripper->strip();
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }  
    if ($stripper->offsetExists('cpk')) {
        $vCpk = $stripper->offsetGet('cpk')->getFilterValue();
    }   
    /*
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    }     
    */    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('about')) {
        $vAbout = $stripper->offsetGet('about')->getFilterValue();
    }
    if ($stripper->offsetExists('about_eng')) {
        $vAboutEng = $stripper->offsetGet('about_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1_title')) {
        $vVerbal1Title = $stripper->offsetGet('verbal1_title')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1')) {
        $vVerbal1 = $stripper->offsetGet('verbal1')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2_title')) {
        $vVerbal2Title = $stripper->offsetGet('verbal2_title')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2')) {
        $vVerbal2 = $stripper->offsetGet('verbal2')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3_title')) {
        $vVerbal3Title = $stripper->offsetGet('verbal3_title')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3')) {
        $vVerbal3 = $stripper->offsetGet('verbal3')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1_title_eng')) {
        $vVerbal1TitleEng = $stripper->offsetGet('verbal1_title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1_eng')) {
        $vVerbal1Eng = $stripper->offsetGet('verbal1_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2_title_eng')) {
        $vVerbal2TitleEng = $stripper->offsetGet('verbal2_title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2_eng')) {
        $vVerbal2Eng = $stripper->offsetGet('verbal2_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3_title_eng')) {
        $vVerbal3TitleEng = $stripper->offsetGet('verbal3_title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3_eng')) {
        $vVerbal3Eng = $stripper->offsetGet('verbal3_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_yearx')) {
        $vFoundationYearx = $stripper->offsetGet('foundation_yearx')->getFilterValue();
    }  
    if ($stripper->offsetExists('tax_office')) {
        $vTaxOffice = $stripper->offsetGet('tax_office')->getFilterValue();
    }  
    if ($stripper->offsetExists('tax_no')) {
        $vTaxNo = $stripper->offsetGet('tax_no')->getFilterValue();
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
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('duns_number')) {
        $vDunsNumber = $stripper->offsetGet('duns_number')->getFilterValue();
    }
    if ($stripper->offsetExists('sgk_sicil_no')) {
        $vDunsNumber = $stripper->offsetGet('sgk_sicil_no')->getFilterValue();
    }
    if ($stripper->offsetExists('web_address')) {
        $vWebAddress = $stripper->offsetGet('web_address')->getFilterValue();
    }
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    } 
   
    $resDataInsert = $BLL->insert(array(   
            'language_code' => $vLanguageCode,    
            'cpk'=> $vCpk,  
            'firm_name'=> $vFirmName, 
            'firm_name_eng'=> $vFirmNameEng, 
            'firm_name_short'=> $vFirmNameShort, 
            'firm_name_short_eng'=> $vFirmNameShortEng, 
            'duns_number'=> $vDunsNumber,
            'profile_public'=> $vProfilePublic,
            'country_id'=> $vCountryId,
            'description'=> $vDescription,
            'description_eng'=> $vDescriptionEng,        
            'about'=> $vAbout,
            'about_eng'=> $vAboutEng,
            'verbal1_title'=> $vVerbal1Title,
            'verbal1'=> $vVerbal1,
            'verbal2_title'=> $vVerbal2Title,
            'verbal2'=> $vVerbal2,
            'verbal3_title'=> $vVerbal3Title,
            'verbal3'=> $vVerbal3,            
            'verbal1_title_eng'=> $vVerbal1TitleEng,
            'verbal1_eng'=> $vVerbal1Eng,
            'verbal2_title_eng'=> $vVerbal2TitleEng,
            'verbal2_eng'=> $vVerbal2Eng,
            'verbal3_title_eng'=> $vVerbal3TitleEng,
            'verbal3_eng'=> $vVerbal3Eng,
            'foundation_yearx'=> $vFoundationYearx,
            'tax_office'=> $vTaxOffice,
            'tax_no'=> $vTaxNo,
            'sgk_sicil_no'=> $vSgkSicilNo,
            'web_address'=> $vWebAddress,
            'logo'=> $vLogo,
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 26-04-2016
 */
$app->get("/pkcpkUpdate_infoFirmVerbal/", function () use ($app ) {
   $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');    
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkcpkUpdate_infoFirmVerbal" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
  
    $vId = 0;
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
     $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
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
    $vAbout = NULL;
    if (isset($_GET['about'])) {
         $stripper->offsetSet('about',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['about']));
    }   
    $vAboutEng = NULL;
    if (isset($_GET['about_eng'])) {
         $stripper->offsetSet('about_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['about_eng']));
    }   
    $vVerbal1Title = NULL;
    if (isset($_GET['verbal1_title'])) {
         $stripper->offsetSet('verbal1_title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1_title']));
    }   
    $vVerbal1 = NULL;
    if (isset($_GET['verbal1'])) {
         $stripper->offsetSet('verbal1',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1']));
    }        
    $vVerbal2Title = NULL;
    if (isset($_GET['verbal2_title'])) {
         $stripper->offsetSet('verbal2_title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2_title']));
    }   
    $vVerbal2 = NULL;
    if (isset($_GET['verbal2'])) {
         $stripper->offsetSet('verbal2',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2']));
    }        
    $vVerbal3Title = NULL;
    if (isset($_GET['verbal3_title'])) {
         $stripper->offsetSet('verbal3_title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3_title']));
    }   
    $vVerbal3 = NULL;
    if (isset($_GET['verbal3'])) {
         $stripper->offsetSet('verbal3',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3']));
    }
    $vVerbal1TitleEng = NULL;
    if (isset($_GET['verbal1_title_eng'])) {
         $stripper->offsetSet('verbal1_title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1_title_eng']));
    }
    $vVerbal1Eng = NULL;
    if (isset($_GET['verbal1_eng'])) {
         $stripper->offsetSet('verbal1_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal1_eng']));
    }
    $vVerbal2TitleEng = NULL;
    if (isset($_GET['verbal2_title_eng'])) {
         $stripper->offsetSet('verbal2_title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2_title_eng']));
    }
    $vVerbal2Eng = NULL;
    if (isset($_GET['verbal2_eng'])) {
         $stripper->offsetSet('verbal2_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal2_eng']));
    }
    $vVerbal3TitleEng = NULL;
    if (isset($_GET['verbal3_title_eng'])) {
         $stripper->offsetSet('verbal3_title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3_title_eng']));
    }
    $vVerbal3Eng = NULL;
    if (isset($_GET['verbal3_eng'])) {
         $stripper->offsetSet('verbal3_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['verbal3_eng']));
    } 
    $vFoundationYearx = NULL;
    if (isset($_GET['foundation_yearx'])) {
        $stripper->offsetSet('foundation_yearx', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['foundation_yearx']));
    } 
    $vTaxOffice = NULL;
    if (isset($_GET['tax_office'])) {
         $stripper->offsetSet('tax_office',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['tax_office']));
    } 
    $vTaxNo = NULL;
    if (isset($_GET['tax_no'])) {
         $stripper->offsetSet('tax_no',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['tax_no']));
    }
    $vFirmName = NULL;
    if (isset($_GET['firm_name'])) {
         $stripper->offsetSet('firm_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name']));
    }
    $vFirmNameEng = NULL;
    if (isset($_GET['firm_name_eng'])) {
         $stripper->offsetSet('firm_name_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name_eng']));
    }
    $vFirmNameShort = NULL;
    if (isset($_GET['firm_name_short'])) {
         $stripper->offsetSet('firm_name_short',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name_short']));
    }
    $vFirmNameShortEng = NULL;
    if (isset($_GET['firm_name_short_eng'])) {
         $stripper->offsetSet('firm_name_short_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['firm_name_short_eng']));
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
    $vDunsNumber = NULL;
    if (isset($_GET['duns_number'])) {
         $stripper->offsetSet('duns_number',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['duns_number']));
    }
    $vSgkSicilNo = NULL;
    if (isset($_GET['sgk_sicil_no'])) {
         $stripper->offsetSet('sgk_sicil_no',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sgk_sicil_no']));
    }
    $vWebAddress = NULL;
    if (isset($_GET['web_address'])) {
         $stripper->offsetSet('web_address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['web_address']));
    }
    $vLogo = NULL;
    if (isset($_GET['logo'])) {
         $stripper->offsetSet('logo',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['logo']));
    }
     
    
    $stripper->strip();
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }
    if ($stripper->offsetExists('cpk')) {
        $vCpk = $stripper->offsetGet('cpk')->getFilterValue();
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }            
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    }
    if ($stripper->offsetExists('about')) {
        $vAbout = $stripper->offsetGet('about')->getFilterValue();
    }
    if ($stripper->offsetExists('about_eng')) {
        $vAboutEng = $stripper->offsetGet('about_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1_title')) {
        $vVerbal1Title = $stripper->offsetGet('verbal1_title')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1')) {
        $vVerbal1 = $stripper->offsetGet('verbal1')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2_title')) {
        $vVerbal2Title = $stripper->offsetGet('verbal2_title')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2')) {
        $vVerbal2 = $stripper->offsetGet('verbal2')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3_title')) {
        $vVerbal3Title = $stripper->offsetGet('verbal3_title')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3')) {
        $vVerbal3 = $stripper->offsetGet('verbal3')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1_title_eng')) {
        $vVerbal1TitleEng = $stripper->offsetGet('verbal1_title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal1_eng')) {
        $vVerbal1Eng = $stripper->offsetGet('verbal1_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2_title_eng')) {
        $vVerbal2TitleEng = $stripper->offsetGet('verbal2_title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal2_eng')) {
        $vVerbal2Eng = $stripper->offsetGet('verbal2_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3_title_eng')) {
        $vVerbal3TitleEng = $stripper->offsetGet('verbal3_title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('verbal3_eng')) {
        $vVerbal3Eng = $stripper->offsetGet('verbal3_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('foundation_yearx')) {
        $vFoundationYearx = $stripper->offsetGet('foundation_yearx')->getFilterValue();
    }  
    if ($stripper->offsetExists('tax_office')) {
        $vTaxOffice = $stripper->offsetGet('tax_office')->getFilterValue();
    }  
    if ($stripper->offsetExists('tax_no')) {
        $vTaxNo = $stripper->offsetGet('tax_no')->getFilterValue();
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
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    }
    if ($stripper->offsetExists('description')) {
        $vDescription = $stripper->offsetGet('description')->getFilterValue();
    }
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('duns_number')) {
        $vDunsNumber = $stripper->offsetGet('duns_number')->getFilterValue();
    }
    if ($stripper->offsetExists('sgk_sicil_no')) {
        $vDunsNumber = $stripper->offsetGet('sgk_sicil_no')->getFilterValue();
    }
    if ($stripper->offsetExists('web_address')) {
        $vWebAddress = $stripper->offsetGet('web_address')->getFilterValue();
    }
    if ($stripper->offsetExists('logo')) {
        $vLogo = $stripper->offsetGet('logo')->getFilterValue();
    }  
      
    $resDataInsert = $BLL->update(array( 
            'id' => $vId,
            'language_code' => $vLanguageCode,               
            'cpk'=> $vCpk,  
            'firm_name'=> $vFirmName, 
            'firm_name_eng'=> $vFirmNameEng, 
            'firm_name_short'=> $vFirmNameShort, 
            'firm_name_short_eng'=> $vFirmNameShortEng, 
            'duns_number'=> $vDunsNumber,
            'profile_public'=> $vProfilePublic,
            'country_id'=> $vCountryId,
            'description'=> $vDescription,
            'description_eng'=> $vDescriptionEng,        
            'about'=> $vAbout,
            'about_eng'=> $vAboutEng,
            'verbal1_title'=> $vVerbal1Title,
            'verbal1'=> $vVerbal1,
            'verbal2_title'=> $vVerbal2Title,
            'verbal2'=> $vVerbal2,
            'verbal3_title'=> $vVerbal3Title,
            'verbal3'=> $vVerbal3,            
            'verbal1_title_eng'=> $vVerbal1TitleEng,
            'verbal1_eng'=> $vVerbal1Eng,
            'verbal2_title_eng'=> $vVerbal2TitleEng,
            'verbal2_eng'=> $vVerbal2Eng,
            'verbal3_title_eng'=> $vVerbal3TitleEng,
            'verbal3_eng'=> $vVerbal3Eng,
            'foundation_yearx'=> $vFoundationYearx,
            'tax_office'=> $vTaxOffice,
            'tax_no'=> $vTaxNo,
            'sgk_sicil_no'=> $vSgkSicilNo,
            'web_address'=> $vWebAddress,
            'logo'=> $vLogo, 
            'pk' => $pk,     
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 

/**x
 *  * Okan CIRAN
 * @since 09-05-2016
 */
$app->get("/pkcpkDeletedAct_infoFirmVerbal/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkDeletedAct_infoFirmVerbal" end point, X-Public variable not found');
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
    if ($stripper->offsetExists('id')) 
        {$vId = $stripper->offsetGet('id')->getFilterValue(); }     
    if ($stripper->offsetExists('cpk')) 
        {$vCpk = $stripper->offsetGet('cpk')->getFilterValue(); }     
    
    $resDataDeleted = $BLL->DeletedAct(array(                  
            'id' => $vId ,  
            'cpk' => $vCpk ,    
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

 
/**
 *  * Okan CIRAN
 * @since 26-04-2016
 */
$app->get("/pkFillGrid_infoFirmVerbal/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');
    $headerParams = $app->request()->headers(); 
     if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillGrid_infoFirmVerbal" end point, X-Public variable not found');
    }
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
      
    $resDataGrid = $BLL->fillGrid(array(
        'language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,   
    ));
    $resTotalRowCount = $BLL->fillGridRowTotalCount(array(
        'language_code' => $vLanguageCode,
    ));
 
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
          //  "firm_id" => $flow["firm_id"],
            "firm_name" => html_entity_decode($flow["firm_name"]),
            "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
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
            
            "profile_public" => $flow["profile_public"],
            "state_profile_public" => $flow["state_profile_public"],                     
            "network_key" => $flow["network_key"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "consultant_id" => $flow["consultant_id"],
            "operation_type_id" => $flow["operation_type_id"],
            "operation_name" => html_entity_decode($flow["operation_name"]),
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "language_id" => $flow["language_id"],
            "language_name" => html_entity_decode($flow["language_name"]),
            "op_user_id" => $flow["op_user_id"],
            "op_user_name" => $flow["op_user_name"],
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
 * @since 26-04-2016
 */
$app->get("/pkFillUsersFirmVerbalNpk_infoFirmVerbal/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');
    $headerParams = $app->request()->headers(); 
     if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillUsersFirmVerbalNpk_infoFirmVerbal" end point, X-Public variable not found');
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
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $resDataGrid = $BLL->fillUsersFirmVerbalNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,  
        'pk'=> $pk,
    ));
     
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
           "id" => $flow["id"],
           // "firm_id" => $flow["firm_id"],
            "firm_name" => html_entity_decode($flow["firm_name"]),
            "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
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
            "language_id" => $flow["language_id"],
            "language_name" => html_entity_decode($flow["language_name"]),
            "logo" => $flow["logo"],  
            "attributes" => array("notroot" => true,  "userb" => $flow["userb"],),
        );
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();    
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

 /**
 *  * Okan CIRAN
 * @since 26-04-2016
 */
$app->get("/fillUsersFirmVerbalNpkGuest_infoFirmVerbal/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');
    $headerParams = $app->request()->headers();     
    
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
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('npk')) {
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();
    } 
    $resDataGrid = $BLL->fillUsersFirmVerbalNpkGuest(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
    ));
    
    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
          //  "firm_id" => $flow["firm_id"],
            "firm_name" => html_entity_decode($flow["firm_name"]),
            "firm_name_eng" => html_entity_decode($flow["firm_name_eng"]),
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
            "language_id" => $flow["language_id"],
            "language_name" => html_entity_decode($flow["language_name"]),
            "logo" => $flow["logo"],           
            "attributes" => array("notroot" => true,"userb" => $flow["userb"] ),
        );
    }

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();    
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

 
/**
 *  * Okan CIRAN
 * @since 23-05-2016
 */
$app->get("/pkcpkGetFirmVerbalConsultant_infoFirmVerbal/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');
    $headerParams = $app->request()->headers();     
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkcpkGetFirmVerbalConsultant_infoFirmVerbal" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vcpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['cpk']));
    }     

    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('cpk')) $vcpk = $stripper->offsetGet('cpk')->getFilterValue();    
 
    $result = $BLL->getFirmVerbalConsultant(array(
        'language_code' => $vLanguageCode,
        'cpk' => $vcpk,        
        'pk' => $pk,
        ));    
  
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(
           // "firm_id" => $flow["firm_id"],
            "consultant_id" => $flow["consultant_id"],  
            "name" => html_entity_decode($flow["name"]),   
            "surname" => html_entity_decode($flow["surname"]),
            "auth_email" => $flow["auth_email"],
        //    "communications_type_id" => $flow["communications_type_id"],
        //    "communications_type_name" => $flow["communications_type_name"],             
        //    "communications_no" => $flow["communications_no"],
            "cons_picture" => $flow["cons_picture"],
         //   "npk" => $flow["network_key"],             
            "attributes" => array(),
        );
    }
 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});



/**
 *  * Okan CIRAN
 * @since 23-05-2016
 */
$app->get("/sendMailConsultant_infoFirmVerbal/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmVerbalBLL');
     
    
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
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('npk')) $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue();    
 
     $result = $BLL->sendMailConsultant(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,        
      
        ));
    
  
    $flows = array();
    
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
});


$app->run();
