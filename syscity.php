<?php
// test commit for branch slim2
require 'vendor/autoload.php';

// Ğİ


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
 *  *  
  *  * zeynel dağlı
 * @since 11-09-2014
 */
$app->get("/fillComboBox_syscity/", function () use ($app ) {

    
    $BLL = $app->getBLLManager()->get('sysCityBLL'); 
    
    $languageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $languageCode = strtolower(trim($_GET['language_code']));
    }
    $componentType = 'ddslick';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }

    $resCombobox = $BLL->fillComboBox (array('country_id'=>$_GET['country_id'],
                                             'language_code'=>$languageCode));  
 
 
       $menus = array();
    if ($componentType == 'bootstrap') {
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "name" => $menu["name"],
               
            );
        }
    } else if ($componentType == 'ddslick') {
        $menus[] = array("text" => "Lütfen Seçiniz", "value" => 0, "selected" => true, "imageSrc" => "", "description" => "Lütfen Seçiniz",); 
        foreach ($resCombobox as $menu) {
            $menus[] = array(
                "text" => $menu["name"],
                "value" =>intval($menu["id"]),
                "selected" => false,
                "description" => $menu["name_eng"],
                "attributes" => array("boroughlist" => $menu["boroughlist"], "active" => $menu["active"],)
              //  "imageSrc" => ""
            );
        }
    }
    
    
    $app->response()->header("Content-Type", "application/json");
    
 
    $app->response()->body(json_encode($menus));
  
});




$app->run();