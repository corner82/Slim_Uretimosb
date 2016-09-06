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
 * @since 23-08-2016
 */
$app->get("/pkDeletedAct_infoFirmConsultants/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmConsultantsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkDeletedAct_infoFirmConsultants" end point, X-Public variable not found');
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
 * @since 23-08-2016
 */
$app->get("/pkUpdate_infoFirmConsultants/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmConsultantsBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkUpdate_infoFirmConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
         
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }   
    $vFirmId = 0;
    if (isset($_GET['firm_id'])) {
         $stripper->offsetSet('firm_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }   
    $vUserId = 0;
    if (isset($_GET['user_id'])) {
         $stripper->offsetSet('user_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['user_id']));
    }   
    
    $stripper->strip(); 
     
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('user_id')) {
        $vUserId = $stripper->offsetGet('user_id')->getFilterValue();
    }  

    $resData = $BLL->update(array(  
            'id' => $vId,
            'url' => $_GET['url'],
            'user_id' => $vUserId,
            'firm_id' => $vFirmId,
            'pk' => $pk,
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
);  

/**x
 *  * Okan CIRAN
 * @since 23-08-2016
 */
$app->get("/pkInsert_infoFirmConsultants/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmConsultantsBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkInsert_infoFirmConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public']; 

    $vFirmId = 0;
    if (isset($_GET['firm_id'])) {
         $stripper->offsetSet('firm_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }   
    $vUserId = 0;
    if (isset($_GET['user_id'])) {
         $stripper->offsetSet('user_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['user_id']));
    }   
    
    $stripper->strip(); 
    if ($stripper->offsetExists('firm_id')) {
        $vFirmId = $stripper->offsetGet('firm_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('user_id')) {
        $vUserId = $stripper->offsetGet('user_id')->getFilterValue();
    }  

    $resData = $BLL->insert(array(              
            'url' => $_GET['url'],
            'user_id' => $vUserId,
            'firm_id' => $vFirmId,
            'pk' => $pk,
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
);  
 

$app->run();