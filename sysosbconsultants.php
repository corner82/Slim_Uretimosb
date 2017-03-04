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
 * @since 07-01-2016
 */
$app->get("/pkGetConsPendingFirmProfile_sysOsbConsultants/", function () use ($app ) {
 
    $BLL = $app->getBLLManager()->get('sysOsbConsultantsBLL');

    $headerParams = $app->request()->headers();
    $sort = null;
    if(isset($_GET['sort'])) $sort = $_GET['sort'];
    
    $order = null;
    if(isset($_GET['order'])) $order = $_GET['order'];
    
    $rows = 10;
    if(isset($_GET['rows'])) $rows = $_GET['rows'];
    
    $page = 1;
    if(isset($_GET['page'])) $page = $_GET['page'];
    
    $filterRules = null;
    if(isset($_GET['filterRules'])) $filterRules = $_GET['filterRules'];
    
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkGetConsPendingFirmProfile_sysOsbConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $resDataGrid = $BLL->getConsPendingFirmProfile(array('page' => $page,
        'rows' => $rows,
        'sort' => $sort,
        'order' => $order,     
        'pk' => $pk,
        'filterRules' => $filterRules));    
 
    $resTotalRowCount = $BLL->getConsPendingFirmProfilertc(array('pk' => $pk ,'filterRules' => $filterRules));
    //print_r($resTotalRowCount);
    //print_r($resDataGrid['resultSet']);
    $flows = array();
    foreach ($resDataGrid['resultSet'] as $flow) {
        $flows[] = array(
//            "id" => $flow["id"],
 
  //          "c_date" => $flow["c_date"],
            "company_name" => $flow["company_name"],
            "username" => $flow["username"],
  //          "operation_name" => $flow["operation_name"],
  //          "cep" => $flow["cep"],
  //          "istel" => $flow["istel"],  
            "s_date" => $flow["s_date"],
            "id" => $flow["id"],
            
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount['resultSet'][0]['count'];
    $resultArray['rows'] = $flows;

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resultArray));

});


/**
 * getting user details for consultant confirmation process
 * @author Mustafa Zeynel Dağlı
 * @since 09/02/2016
 */
$app->get("/pkGetConsConfirmationProcessDetails_sysOsbConsultants/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('sysOsbConsultantsBLL');

    $headerParams = $app->request()->headers();

    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkGetConsConfirmationProcessDetails_sysOsbConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    $profileID;
    if (isset($_GET['profile_id'])) {
        $profileID = $_GET['profile_id'];
    }

    $result = $BLL->getConsConfirmationProcessDetails(array('profile_id' => $profileID,
                                                         'pk' => $pk));    
    //print_r($resDataGrid['$result']);
    $flows = array();
    foreach ($result['resultSet'] as $flow) {
        $flows[] = array(

 
            "id" => $flow["id"],
            "firmname" => $flow["firm_name"],
            "username" => $flow["username"],   
            "sgkno" => $flow["sgk_sicil_no"],
            "languagecode" => $flow["language_code"],
            "iletisimadresi" => $flow["iletisimadresi"],
            "faturaadresi" => $flow["faturaadresi"],
            "irtibattel" => $flow["irtibattel"],
            "irtibatcep" => $flow["irtibatcep"],
            "sdate" => $flow["s_date"],

            
        );
    }

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($flows));
  
    
    
});

 
 
/**
 *  * Okan CIRAN
 * @since 23-05-2016
 */
$app->get("/pkcpkGetAllFirmCons_sysOsbConsultants/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOsbConsultantsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkGetAllFirmCons_sysOsbConsultants" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vcpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, $app, $_GET['cpk']));
    }


    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if ($stripper->offsetExists('cpk'))
        $vcpk = $stripper->offsetGet('cpk')->getFilterValue();

    $result = $BLL->getAllFirmCons(array(
        'language_code' => $vLanguageCode,
        'cpk' => $vcpk,
        'pk' => $pk,
    ));


    $flows = array();
    if (isset($result[0]['consultant_id'])) {
        foreach ($result['resultSet'] as $flow) {
            $flows[] = array(
                //  "firm_id" => $flow["firm_id"],
                "consultant_id" => $flow["consultant_id"],
                "name" => $flow["name"],
                "surname" => $flow["surname"],
                "auth_email" => $flow["auth_email"],
                "title" => $flow["title"],
                "title_eng" => $flow["title_eng"],
                "cons_title" => $flow["cons_title"],
                "cons_title_eng" => $flow["cons_title_eng"],
                "cons_picture" => $flow["cons_picture"],
                "npk" => $flow["network_key"],
                "phone" => $flow["phone"],
                "attributes" => array("firm_consultant" => $flow["firm_consultant"],),
            );
        }
    }

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($flows));
});
 
/**
 *  * Okan CIRAN
 * @since 09-08-2016
 */
$app->get("/pkFillOsbConsultantListGrid_sysOsbConsultants/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('sysOsbConsultantsBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillOsbConsultantListGrid_sysOsbConsultants" end point, X-Public variable not found');
    } 

    $vName = NULL;
    if (isset($_GET['name'])) {
        $stripper->offsetSet('name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['name']));
    }     
    $vSurname = NULL;
    if (isset($_GET['surname'])) {
        $stripper->offsetSet('surname', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['surname']));
    }     
    $vUsername = NULL;
    if (isset($_GET['username'])) {
        $stripper->offsetSet('username', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['username']));
    }
    $vPreferredLanguageName = NULL;
    if (isset($_GET['preferred_language_name'])) {
        $stripper->offsetSet('preferred_language_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['preferred_language_name']));
    }
    $vRoleName = NULL;
    if (isset($_GET['role_name'])) {
        $stripper->offsetSet('role_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['role_name']));
    }
    $vRoleNameTr = NULL;
    if (isset($_GET['role_name_tr'])) {
        $stripper->offsetSet('role_name_tr', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['role_name_tr']));
    }  
    $vOsbName = NULL;
    if (isset($_GET['osb_name'])) {
        $stripper->offsetSet('osb_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['osb_name']));
    }
    $vOpUserName= NULL;
    if (isset($_GET['op_user_name'])) {
        $stripper->offsetSet('op_user_name', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['op_user_name']));
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
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    }
    if ($stripper->offsetExists('surname')) {
        $vSurname = $stripper->offsetGet('surname')->getFilterValue();
    }
    if ($stripper->offsetExists('username')) {
        $vUsername = $stripper->offsetGet('username')->getFilterValue();
    } 
    if ($stripper->offsetExists('preferred_language_name')) {
        $vPreferredLanguageName = $stripper->offsetGet('preferred_language_name')->getFilterValue();
    }
    if ($stripper->offsetExists('role_name')) {
        $vRoleName = $stripper->offsetGet('role_name')->getFilterValue();
    }
    if ($stripper->offsetExists('role_name_tr')) {
        $vRoleNameTr = $stripper->offsetGet('role_name_tr')->getFilterValue();
    }     
    if ($stripper->offsetExists('osb_name')) {
        $vOsbName = $stripper->offsetGet('osb_name')->getFilterValue();
    }  
    if ($stripper->offsetExists('op_user_name')) {
        $vOpUserName = $stripper->offsetGet('op_user_name')->getFilterValue();
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
    
    $resDataGrid = $BLL->fillOsbConsultantListGrid(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'name' => $vName,
        'surname' => $vSurname,
        'username' => $vUsername, 
        'preferred_language_name' => $vPreferredLanguageName,
        'role_name' => $vRoleName,
        'role_name_tr' => $vRoleNameTr,
        'osb_name' => $vOsbName,    
        'op_user_name' => $vOpUserName,       
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillOsbConsultantListGridRtc(array(
        'name' => $vName,
        'surname' => $vSurname,
        'username' => $vUsername, 
        'preferred_language_name' => $vPreferredLanguageName,
        'role_name' => $vRoleName,
        'role_name_tr' => $vRoleNameTr,
        'osb_name' => $vOsbName,    
        'op_user_name' => $vOpUserName,  
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "name" => html_entity_decode($flow["name"]),
            "surname" => html_entity_decode($flow["surname"]),
            "username" => html_entity_decode($flow["username"]),
                
            "preferred_language" => $flow["preferred_language"],
            "preferred_language_name" => html_entity_decode($flow["preferred_language_name"]),
            "preferred_language_json" =>  $flow["preferred_language_json"],
            "role_id" => $flow["role_id"],
            "role_name" => html_entity_decode($flow["role_name"]),
            "role_name_tr" => html_entity_decode($flow["role_name_tr"]),
            "osb_id" => $flow["osb_id"],
            "osb_name" => html_entity_decode($flow["osb_name"]),            
            "op_user_id" => $flow["op_user_id"],
            "op_user_name" => html_entity_decode($flow["op_user_name"]),
   
                "attributes" => array(              
                "active" => $flow["active"], ) );
        };
        $counts = $resTotalRowCount[0]['count'];
    }   
    
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

/**x
 *  * Okan CIRAN
 * @since 09-08-2016
 */
$app->get("/pkUpdateMakeActiveOrPassive_sysOsbConsultants/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbConsultantsBLL');
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
    $resData = $BLL->makeActiveOrPassive(array(                  
            'id' => $vId ,    
            'pk' => $Pk,        
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 


/**
 *  * Okan CIRAN
 * @since 09-08-2016
 */
$app->get("/pkDelete_sysOsbConsultants/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('sysOsbConsultantsBLL');   
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

$app->run();
