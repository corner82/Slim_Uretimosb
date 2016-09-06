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
 * @since 24.05.2016
 */
$app->get("/pkFillMailServerList_sysMailServer/", function () use ($app ) {    
    $BLL = $app->getBLLManager()->get('sysMailServerBLL');    
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillMailServerList_sysMailServer" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
       
    $resCombobox = $BLL->fillMailServerList(); 
    $flows = array();
 
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Please Choose",); 
    foreach ($resCombobox as $flow) {
        $flows[] = array(            
            "text" => $flow["host_name"],
            "value" =>  intval($flow["id"]),
            "selected" => false,
            "description" => $flow["host_address"],
           // "imageSrc"=>$flow["logo"],             
            "attributes" => array("active" => $flow["active"],
                ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 24.05.2016
 */
$app->get("/pkInsert_sysMailServer/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMailServerBLL');   
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkInsert_sysMailServer" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];  
              
    $vHostName = NULL;
    if (isset($_GET['host_name'])) {
         $stripper->offsetSet('host_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['host_name']));
    } 
    $vHostAddress = NULL;
    if (isset($_GET['host_address'])) {
         $stripper->offsetSet('host_address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['host_address']));
    }   
    $vSmtpAuth = TRUE;
    if (isset($_GET['smtp_auth'])) {
         $stripper->offsetSet('smtp_auth',$stripChainerFactory->get(stripChainers::FILTER_ONLY_BOOLEAN_ALLOWED,
                                                $app,
                                                $_GET['smtp_auth']));
    }  
    $vSmtpDebug = 2;
    if (isset($_GET['smtp_debug'])) {
         $stripper->offsetSet('smtp_debug',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['smtp_debug']));
    } 
    $vSmtpSecure = 'SSL';
    if (isset($_GET['smtp_secure'])) {
         $stripper->offsetSet('smtp_secure',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['smtp_secure']));
    } 
    $vPort = 587;
    if (isset($_GET['port'])) {
         $stripper->offsetSet('port',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['port']));
    }  
    $vUsername = NULL;
    if (isset($_GET['username'])) {
         $stripper->offsetSet('username',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['username']));
    }  
    $vPsswrd = NULL;
    if (isset($_GET['psswrd'])) {
         $stripper->offsetSet('psswrd',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['psswrd']));
    }  
    $vCharset = NULL;
    if (isset($_GET['charset'])) {
         $stripper->offsetSet('charset',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['charset']));
    }  
    $vHeaders = NULL;
    if (isset($_GET['headers'])) {
         $stripper->offsetSet('headers',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['headers']));
    } 
    $vBodyRoad = NULL;
    if (isset($_GET['body_road'])) {
         $stripper->offsetSet('body_road',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['body_road']));
    }  
    
    $stripper->strip();
    if ($stripper->offsetExists('host_name')) {
        $vHostName = $stripper->offsetGet('host_name')->getFilterValue();
    }  
    if ($stripper->offsetExists('host_address')) {
        $vHostAddress = $stripper->offsetGet('host_address')->getFilterValue();
    }
    if ($stripper->offsetExists('smtp_auth')) {
        $vSmtpAuth = $stripper->offsetGet('smtp_auth')->getFilterValue();
    }
    if ($stripper->offsetExists('smtp_debug')) {
        $vSmtpDebug= $stripper->offsetGet('smtp_debug')->getFilterValue();
    }
    if ($stripper->offsetExists('smtp_secure')) {
        $vSmtpSecure = $stripper->offsetGet('smtp_secure')->getFilterValue();
    }
    if ($stripper->offsetExists('port')) {
        $vPort = $stripper->offsetGet('port')->getFilterValue();
    }    
    if ($stripper->offsetExists('username')) {
        $vUsername = $stripper->offsetGet('username')->getFilterValue();
    }
    if ($stripper->offsetExists('psswrd')) {
        $vPsswrd = $stripper->offsetGet('psswrd')->getFilterValue();
    }
    if ($stripper->offsetExists('charset')) {
        $vCharset = $stripper->offsetGet('charset')->getFilterValue();
    }
    if ($stripper->offsetExists('headers')) {
        $vHeaders = $stripper->offsetGet('headers')->getFilterValue();
    }
    if ($stripper->offsetExists('body_road')) {
        $vBodyRoad = $stripper->offsetGet('body_road')->getFilterValue();
    }
    
    $resData = $BLL->insert(array(  
            'host_name' => $vHostName, 
            'host_address' => $vHostAddress,
            'smtp_auth'=> $vSmtpAuth, 
            'smtp_debug'=> $vSmtpDebug, 
            'smtp_secure' => $vSmtpSecure,
            'username' => $vUsername,
            'psswrd' => $vPsswrd,
            'charset' => $vCharset,
            'headers' => $vHeaders,
            'body_road' => $vBodyRoad,        
            'pk' => $pk,           
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 
/**
 *  * Okan CIRAN
 * @since 24.05.2016
 */
$app->get("/pkUpdate_sysMailServer/", function () use ($app ) {    
     $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMailServerBLL');   
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkUpdate_sysMailServer" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];  
 
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }   $vHostName = NULL;
    if (isset($_GET['host_name'])) {
         $stripper->offsetSet('host_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['host_name']));
    } 
    $vHostAddress = NULL;
    if (isset($_GET['host_address'])) {
         $stripper->offsetSet('host_address',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['host_address']));
    }   
    $vSmtpAuth = TRUE;
    if (isset($_GET['smtp_auth'])) {
         $stripper->offsetSet('smtp_auth',$stripChainerFactory->get(stripChainers::FILTER_ONLY_BOOLEAN_ALLOWED,
                                                $app,
                                                $_GET['smtp_auth']));
    }  
    $vSmtpDebug = 2;
    if (isset($_GET['smtp_debug'])) {
         $stripper->offsetSet('smtp_debug',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['smtp_debug']));
    } 
    $vSmtpSecure = 'SSL';
    if (isset($_GET['smtp_secure'])) {
         $stripper->offsetSet('smtp_secure',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['smtp_secure']));
    } 
    $vPort = 587;
    if (isset($_GET['port'])) {
         $stripper->offsetSet('port',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['port']));
    }  
    $vUsername = NULL;
    if (isset($_GET['username'])) {
         $stripper->offsetSet('username',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['username']));
    }  
    $vPsswrd = NULL;
    if (isset($_GET['psswrd'])) {
         $stripper->offsetSet('psswrd',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['psswrd']));
    }  
    $vCharset = NULL;
    if (isset($_GET['charset'])) {
         $stripper->offsetSet('charset',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['charset']));
    }  
    $vHeaders = NULL;
    if (isset($_GET['headers'])) {
         $stripper->offsetSet('headers',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['headers']));
    } 
    $vBodyRoad = NULL;
    if (isset($_GET['body_road'])) {
         $stripper->offsetSet('body_road',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_JASON_LVL1,
                                                $app,
                                                $_GET['body_road']));
    }  
    
    $stripper->strip();
    if ($stripper->offsetExists('host_name')) {
        $vHostName = $stripper->offsetGet('host_name')->getFilterValue();
    }  
    if ($stripper->offsetExists('host_address')) {
        $vHostAddress = $stripper->offsetGet('host_address')->getFilterValue();
    }
    if ($stripper->offsetExists('smtp_auth')) {
        $vSmtpAuth = $stripper->offsetGet('smtp_auth')->getFilterValue();
    }
    if ($stripper->offsetExists('smtp_debug')) {
        $vSmtpDebug= $stripper->offsetGet('smtp_debug')->getFilterValue();
    }
    if ($stripper->offsetExists('smtp_secure')) {
        $vSmtpSecure = $stripper->offsetGet('smtp_secure')->getFilterValue();
    }
    if ($stripper->offsetExists('port')) {
        $vPort = $stripper->offsetGet('port')->getFilterValue();
    }    
    if ($stripper->offsetExists('username')) {
        $vUsername = $stripper->offsetGet('username')->getFilterValue();
    }
    if ($stripper->offsetExists('psswrd')) {
        $vPsswrd = $stripper->offsetGet('psswrd')->getFilterValue();
    }
    if ($stripper->offsetExists('charset')) {
        $vCharset = $stripper->offsetGet('charset')->getFilterValue();
    }
    if ($stripper->offsetExists('headers')) {
        $vHeaders = $stripper->offsetGet('headers')->getFilterValue();
    }
    if ($stripper->offsetExists('body_road')) {
        $vBodyRoad = $stripper->offsetGet('body_road')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }     
       $resData = $BLL->update(array(  
            'id' => $vId, 
            'host_name' => $vHostName, 
            'host_address' => $vHostAddress,
            'smtp_auth'=> $vSmtpAuth, 
            'smtp_debug'=> $vSmtpDebug, 
            'smtp_secure' => $vSmtpSecure,
            'username' => $vUsername,
            'psswrd' => $vPsswrd,
            'charset' => $vCharset,
            'headers' => $vHeaders,
            'body_road' => $vBodyRoad,        
            'pk' => $pk,           
            )); 
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 
   
/**
 *  * Okan CIRAN
 * @since 24.05.2016
 */
$app->get("/pkFillGrid_sysMailServer/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('sysMailServerBLL');
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) 
        throw new Exception ('rest api "pkFillGrid_sysMailServer" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];      
      
    $resDataGrid = $BLL->fillGrid();    
    $resTotalRowCount = $BLL->fillGridRowTotalCount();

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],            
            'host_name' => $flow["host_name"],
            'host_address'=> $flow["host_address"],
            'smtp_auth' => $flow["smtp_auth"],
            'smtp_debug' => $flow["smtp_debug"],
            'smtp_secure' => $flow["smtp_secure"],            
            'port' => $flow["port"],
            'host_name' => $flow["host_name"],
            'psswrd' => $flow["psswrd"],
            'charset' => $flow["charset"],
            'headers' => $flow["headers"],
            'body_road' => $flow["body_road"],             
            "deleted" => $flow["deleted"],      
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],       
            "state_active" => $flow["state_active"],  
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

 /**x
 *  * Okan CIRAN
 * @since 24.05.2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysMailServer/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMailServerBLL');
    if(!isset($headerParams['X-Public'])) 
       throw new Exception ('rest api "pkUpdateMakeActiveOrPassive_sysMailServer" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];          
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {$vId = $stripper->offsetGet('id')->getFilterValue(); }
    $resData = $BLL->makeActiveOrPassive(array(                  
            'id' => $vId ,    
            'pk' => $pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 

/**x
 *  * Okan CIRAN
 * @since 24.05.2016
 */
$app->get("/pkDelete_sysMailServer/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysMailServerBLL');   
    $headerParams = $app->request()->headers();
    $Pk = $headerParams['X-Public'];  
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $stripper->strip(); 
    if ($stripper->offsetExists('id')) 
        {$vId = $stripper->offsetGet('id')->getFilterValue(); }  
        
    $resDataDeleted = $BLL->Delete(array(                  
            'id' => $vId ,    
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

$app->run();
