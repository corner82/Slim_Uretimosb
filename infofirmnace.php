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

 
/**x
 *  * Okan CIRAN
 * @since 18-01-2017
 */
$app->get("/pkDeletedAct_infoFirmNace/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmNaceBLL');
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
 * @since 18-01-2017
 */
$app->get("/pkUpdate_infoFirmNace/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmNaceBLL');   
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
    $vFirmId = NULL;
    if (isset($_GET['firm_id'])) {
         $stripper->offsetSet('firm_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }       
    $vRefFirmId = NULL;
    if (isset($_GET['ref_firm_id'])) {
         $stripper->offsetSet('ref_firm_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['ref_firm_id']));
    } 
    $vTotalProject = NULL;
    if (isset($_GET['total_project'])) {
         $stripper->offsetSet('total_project',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['total_project']));
    }
    $vContinuingProject = NULL;
    if (isset($_GET['continuing_project'])) {
         $stripper->offsetSet('continuing_project',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['continuing_project']));
    }
    $vUnsuccessfulProject = NULL;
    if (isset($_GET['unsuccessful_project'])) {
         $stripper->offsetSet('unsuccessful_project',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['unsuccessful_project']));
    }    
    $vActive = 0;
    if (isset($_GET['active'])) {
         $stripper->offsetSet('active',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['active']));
    }
     
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('ref_firm_id')) {
        $vRefFirmId = $stripper->offsetGet('ref_firm_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('total_project')) {
        $vTotalProject = $stripper->offsetGet('total_project')->getFilterValue();
    } 
    if ($stripper->offsetExists('continuing_project')) {
        $vContinuingProject = $stripper->offsetGet('continuing_project')->getFilterValue();
    } 
    if ($stripper->offsetExists('unsuccessful_project')) {
        $vUnsuccessfulProject = $stripper->offsetGet('unsuccessful_project')->getFilterValue();
    }    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
    }
 

    $resData = $BLL->update(array(  
            'id' => $vId , 
            'language_code' => $vLanguageCode,    
            'firm_id' => $vFirmId ,                         
            'ref_firm_id' => $vRefFirmId, 
            'total_project' => $vTotalProject,
            'continuing_project' => $vContinuingProject,
            'unsuccessful_project' => $vUnsuccessfulProject , 
            'active' => $vActive ,                       
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
 * @since 18-01-2017
 */
$app->get("/pkInsert_infoFirmNace/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmNaceBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
   
    $vFirmId = NULL;
    if (isset($_GET['firm_id'])) {
         $stripper->offsetSet('firm_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }       
    $vRefFirmId = NULL;
    if (isset($_GET['ref_firm_id'])) {
         $stripper->offsetSet('ref_firm_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['ref_firm_id']));
    } 
    $vTotalProject = 0;
    if (isset($_GET['total_project'])) {
         $stripper->offsetSet('total_project',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['total_project']));
    }
    $vContinuingProject = 0;
    if (isset($_GET['continuing_project'])) {
         $stripper->offsetSet('continuing_project',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['continuing_project']));
    }
    $vUnsuccessfulProject = 0;
    if (isset($_GET['unsuccessful_project'])) {
         $stripper->offsetSet('unsuccessful_project',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['unsuccessful_project']));
    }    
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('ref_firm_id')) {
        $vRefFirmId = $stripper->offsetGet('ref_firm_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('total_project')) {
        $vTotalProject = $stripper->offsetGet('total_project')->getFilterValue();
    } 
    if ($stripper->offsetExists('continuing_project')) {
        $vContinuingProject = $stripper->offsetGet('continuing_project')->getFilterValue();
    } 
    if ($stripper->offsetExists('unsuccessful_project')) {
        $vUnsuccessfulProject = $stripper->offsetGet('unsuccessful_project')->getFilterValue();
    }    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    
    $resData = $BLL->insert(array(         
            'language_code' => $vLanguageCode,    
            'firm_id' => $vFirmId ,                         
            'ref_firm_id' => $vRefFirmId, 
            'total_project' => $vTotalProject,
            'continuing_project' => $vContinuingProject,
            'unsuccessful_project' => $vUnsuccessfulProject ,             
            'pk' => $Pk,        
            ));


    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 
 /**
 *  * Okan CIRAN
 * @since 18-01-2017
 */
$app->get("/fillFirmWhatWorksForNace_infoFirmNace/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmNaceBLL');    
    
    $vNpk = NULL;    
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    } 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    
    $stripper->strip(); 
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();         
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
      $result = $BLL->fillFirmWhatWorksForNace(array(
        'url' =>   $_GET['url'],
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,       
        ));                            
                   
    $flows = array();
    foreach ($result  as $flow) {
        $flows[] = array(
            "id" =>  $flow["id"],
            "nace_code" => $flow["nace_code"],  
            "nacecode_id" => $flow["nacecode_id"],
            "description" => html_entity_decode($flow["description"]),
            "attributes" => array(),
        );
    }
 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
}
); 
 
 /**
 *  * Okan CIRAN
 * @since 19-01-2017
 */
$app->get("/pkFillFirmWhatWorksForNace_infoFirmNace/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmNaceBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];        
    
    $vNpk = NULL;    
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    } 
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    
    $stripper->strip(); 
    if ($stripper->offsetExists('npk')) {
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();         
    }
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }
      $result = $BLL->fillFirmWhatWorksForNace(array(        
        'url' =>   $_GET['url'],
        'pk' =>    $Pk,
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,       
        ));                            
                   
    $flows = array();
    foreach ($result  as $flow) {
        $flows[] = array(
            "id" =>  $flow["id"],
            "nace_code" => $flow["nace_code"],  
            "nacecode_id" => $flow["nacecode_id"],
            "description" => html_entity_decode($flow["description"]),
            "attributes" => array(),
        );
    }
 
    $app->response()->header("Content-Type", "application/json");    
    $app->response()->body(json_encode($flows));
}
); 
$app->run();