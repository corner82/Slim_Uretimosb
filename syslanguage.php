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




   


/**
 *  *  
  *  * zeynel dağlı
 * @since 11-09-2014
 */
$app->get("/fillComboBox_syslanguage/", function () use ($app ) {
    
    $BLL = $app->getBLLManager()->get('sysLanguageBLL'); 
    
    $componentType = 'bootstrap'; 
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type'] ));
    }
   
    $resCombobox = $BLL->fillComboBox ();  
 
   
    if ($componentType == 'bootstrap') {
        $menus = array();
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "language" => $menu["language"],
                "language_eng" => $menu["language_eng"],
                "language_main_code" => $menu["language_main_code"],
            );
        }
    } else if ($componentType == 'ddslick') {
        $menus = array();
        $menus[] = array("text" => "Lütfen Bir Dil Seçiniz", "value" => -1, "selected" => true,);
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["language"],
                "value" => $menu["id"],
                "selected" => false,
                "description" => $menu["language_eng"],
                "imageSrc" => ""
            );
        }
    }


    $app->response()->header("Content-Type", "application/json");
    
   if($componentType == 'ddslick'){
        $app->response()->body(json_encode($menus));
    }else if($componentType == 'bootstrap'){
        $app->response()->body(json_encode($resCombobox));
    }
  
  
    
  //$app->response()->body(json_encode($menus));
  
});

 
/**
 *  * Okan CIRAN
 * @since 05.05.2016
 */
$app->get("/pkFillLanguageDdList_syslanguage/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('sysLanguageBLL');
    
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkFillLanguageDdList_syslanguage" end point, X-Public variable not found');
    //$pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }
    $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
        
    $resCombobox = $BLL->fillLanguageDdList(array(                                   
                                    'language_code' => $vLanguageCode,
                        ));    

    $flows = array();
    $flows[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
    foreach ($resCombobox as $flow) {
        $flows[] = array(            
            "text" => $flow["name"],
            "value" =>  intval($flow["id"]),
            "selected" => false,
            "description" => $flow["name_eng"],
            "imageSrc"=>"",              
            "attributes" => array( 
                                    "active" => $flow["active"], 
                   
                ),
        );
    }
    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
 

$app->run();