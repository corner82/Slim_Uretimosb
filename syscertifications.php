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
 * @since 30-03-2016
 */
$app->get("/pkFillGrid_sysCertifications/", function () use ($app ) {

    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();  
    $BLL = $app->getBLLManager()->get('sysCertificationsBLL');
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public']; 
    
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
     
    $resDataGrid = $BLL->fillGrid(array(              
            'page' => $vPage,
            'rows' => $vRows,
            'sort' => $vSort,
            'order' => $vOrder,          
            'language_code' => $vLanguageCode,      
            ));    
    $resTotalRowCount = $BLL->fillGridRowTotalCount(array(              
            'language_code' => $vLanguageCode,
            ));

    $flows = array();
    foreach ($resDataGrid as $flow) {         
        $flows[] = array(
            "id" => $flow["id"],
            "certificates" => $flow["certificates"],  
            "certificate_eng" => $flow["certificate_eng"],  
            "certificate_shorts" => $flow["certificate_shorts"],  
            "certificate_short_eng" => $flow["certificate_short_eng"], 
            "descriptions" => $flow["descriptions"],  
            "description_eng" => $flow["description_eng"],  
            "priority" => $flow["priority"],  
            "logo" => $flow["logo"],  
            
            "deleted" => $flow["deleted"],      
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],       
            "state_active" => $flow["state_active"], 
            "language_id" => $flow["language_id"],      
	    "language_name" => $flow["language_name"],
            "language_parent_id" => $flow["language_parent_id"],                
            "op_user_id" => $flow["op_user_id"],  
            "op_user_name" => $flow["op_user_name"],              
             
            "attributes" => array("notroot" => true, "active" => $flow["active"]),
        );
    }
     
    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resultArray));
});

  
/**x
 *  * Okan CIRAN
 * @since 30-03-2016
 */
$app->get("/pkDelete_sysCertifications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysCertificationsBLL'); 
   
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
    
    $resDataDeleted = $BLL->Delete(array(                  
            'id' => $vId ,    
            'pk' => $Pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

 

/**x
 *  * Okan CIRAN
 * @since 30-03-2016
 */
$app->get("/pkUpdate_sysCertifications/", function () use ($app ) {
    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysCertificationsBLL');
   
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
    $vCertificates = NULL;
    if (isset($_GET['certificates'])) {
         $stripper->offsetSet('certificates',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificates']));
    }   
    $vCertificateEng = NULL;
    if (isset($_GET['certificate_eng'])) {
        $stripper->offsetSet('certificate_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificate_eng']));
    } 
    $vCertificateShorts = NULL;
    if (isset($_GET['certificate_shorts'])) {
        $stripper->offsetSet('certificate_shorts', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificate_shorts']));
    } 
    $vCertificateShortsEng = NULL;
    if (isset($_GET['certificate_short_eng'])) {
        $stripper->offsetSet('certificate_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificate_short_eng']));
    }    
    $vDescriptions = NULL;
    if (isset($_GET['descriptions'])) {
        $stripper->offsetSet('descriptions', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['descriptions']));
    }    
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    }
    $vPriority = NULL;
    if (isset($_GET['priority'])) {
        $stripper->offsetSet('priority', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['priority']));
    }
 
    
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }  
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('certificates')) {
        $vCertificates = $stripper->offsetGet('certificates')->getFilterValue();
    }           
    if ($stripper->offsetExists('certificate_eng')) {
        $vCertificateEng = $stripper->offsetGet('certificate_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('certificate_shorts')) {
        $vCertificateShorts = $stripper->offsetGet('certificate_shorts')->getFilterValue();
    }    
    if ($stripper->offsetExists('certificate_short_eng')) {
        $vCertificateShortsEng = $stripper->offsetGet('certificate_short_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('descriptions')) {
        $vDescriptions = $stripper->offsetGet('descriptions')->getFilterValue();
    }           
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('priority')) {
        $vPriority = $stripper->offsetGet('priority')->getFilterValue();
    }
  

    $resData = $BLL->update(array(  
            "id" => $vId , 
            "language_code" => $vLanguageCode, 
            "certificates" => $vCertificates,  
            "certificate_eng" => $vCertificateEng,  
            "certificate_shorts" => $vCertificateShorts,  
            "certificate_short_eng" => $vCertificateShortsEng, 
            "descriptions" => $vDescriptions,  
            "description_eng" => $vDescriptionEng,  
            "priority" => $vPriority,            
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json");
 
    $app->response()->body(json_encode($resData));
}
); 

/**x
 *  * Okan CIRAN
 * @since 30-03-2016
 */
$app->get("/pkInsert_sysCertifications/", function () use ($app ) {
    
     $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysCertificationsBLL');
   
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
    $vCertificates = NULL;
    if (isset($_GET['certificates'])) {
         $stripper->offsetSet('certificates',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificates']));
    }   
    $vCertificateEng = NULL;
    if (isset($_GET['certificate_eng'])) {
        $stripper->offsetSet('certificate_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificate_eng']));
    } 
    $vCertificateShorts = NULL;
    if (isset($_GET['certificate_shorts'])) {
        $stripper->offsetSet('certificate_shorts', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificate_shorts']));
    } 
    $vCertificateShortsEng = NULL;
    if (isset($_GET['certificate_short_eng'])) {
        $stripper->offsetSet('certificate_short_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['certificate_short_eng']));
    }    
    $vDescriptions = NULL;
    if (isset($_GET['descriptions'])) {
        $stripper->offsetSet('descriptions', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['descriptions']));
    }    
    $vDescriptionEng = NULL;
    if (isset($_GET['description_eng'])) {
        $stripper->offsetSet('description_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['description_eng']));
    }
    $vPriority = NULL;
    if (isset($_GET['priority'])) {
        $stripper->offsetSet('priority', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['priority']));
    }
 
    
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }  
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('certificates')) {
        $vCertificates = $stripper->offsetGet('certificates')->getFilterValue();
    }           
    if ($stripper->offsetExists('certificate_eng')) {
        $vCertificateEng = $stripper->offsetGet('certificate_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('certificate_shorts')) {
        $vCertificateShorts = $stripper->offsetGet('certificate_shorts')->getFilterValue();
    }    
    if ($stripper->offsetExists('certificate_short_eng')) {
        $vCertificateShortsEng = $stripper->offsetGet('certificate_short_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('descriptions')) {
        $vDescriptions = $stripper->offsetGet('descriptions')->getFilterValue();
    }           
    if ($stripper->offsetExists('description_eng')) {
        $vDescriptionEng = $stripper->offsetGet('description_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('priority')) {
        $vPriority = $stripper->offsetGet('priority')->getFilterValue();
    }  

    $resData = $BLL->insert(array( 
            "language_code" => $vLanguageCode, 
            "certificates" => $vCertificates,  
            "certificate_eng" => $vCertificateEng,  
            "certificate_shorts" => $vCertificateShorts,  
            "certificate_short_eng" => $vCertificateShortsEng, 
            "descriptions" => $vDescriptions,  
            "description_eng" => $vDescriptionEng,  
            "priority" => $vPriority,   
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json");
 
    $app->response()->body(json_encode($resData));
}
); 
/**
 *  * Okan CIRAN
 * @since 25.07.2016
 */
$app->get("/pkFillCertificationsDdList_sysCertifications/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysCertificationsBLL');
    
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillCertificationsDdList_sysCertifications" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
        
    $resCombobox = $BLL->fillCertificationsDdList(array(                                   
                                    'language_code' => $vLanguageCode,
                        ));    

    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
    foreach ($resCombobox as $flow) {
        $flows[] = array(            
            "text" => $flow["certificate_name"],
            "value" =>  intval($flow["id"]),
            "selected" => false,
            "description" => $flow["certificate_name_eng"],
            "imageSrc"=>$flow["logo"],              
            "attributes" => array( 
                                    "active" => $flow["active"], 
                                    "certificate_shorts" => $flow["certificate_shorts"],
                                    "certificate_short_eng" => $flow["certificate_short_eng"],
                                    "descriptions" => $flow["descriptions"],
                ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
 


$app->run();
