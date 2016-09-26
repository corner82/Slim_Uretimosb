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



    
 

/**
 * Okan CIRAN
 * @since 18.07.2016
 */
$app->get("/pkGetAll_infoFirmWorkingPersonnel/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoFirmWorkingPersonnel" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

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

    $resDataMenu = $BLL->getAll(array(
        'language_code' => $vLanguageCode,
        'pk' => $pk,
            ));


    $menus = array();
    if (isset($resDataGrid['resultSet'][0]['id'])) {
        foreach ($resDataMenu as $menu) {
            $menus[] = array(
                "id" => $menu["id"],
                "firm_id" => $menu["firm_id"],
                "unspsc_codes_id" => $menu["unspsc_codes_id"],
                "unspsc_name" => $menu["unspsc_name"],
                "unspsc_name_eng" => $menu["unspsc_name_eng"],
                "consultant_id" => $menu["consultant_id"],
                "cons_allow_id" => $menu["cons_allow_id"],
                "cons_allow" => $menu["cons_allow"], 
                "act_parent_id" => intval($flow["act_parent_id"]),
                "deleted" => $menu["deleted"],
                "state_deleted" => $menu["state_deleted"],
                "active" => $menu["active"],
                "state_active" => $menu["state_active"],
                "language_id" => $menu["language_id"],
                "language_name" => $menu["language_names"],
                "op_user_id" => $menu["op_user_id"],
                "op_username" => $menu["op_username"],
                "operation_type_id" => $menu["operation_type_id"],
                "operation_name" => $menu["operation_name"],
                "s_date" => $menu["s_date"],
                "c_date" => $menu["c_date"],
            );
        }
    }

    $app->response()->header("Content-Type", "application/json");
    $app->response()->body(json_encode($menus));
});
  

/**x
 *  * Okan CIRAN
* @since 18.07.2016
 */
$app->get("/pkcpkDeletedAct_infoFirmWorkingPersonnel/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkDeletedAct_infoFirmWorkingPersonnel" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
    
    $vCpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['cpk']));
    }
    $vId = NULL;
    if (isset($_GET['id'])) {
        $stripper->offsetSet('id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 

    $stripper->strip(); 
    if ($stripper->offsetExists('id')) {$vId = $stripper->offsetGet('id')->getFilterValue(); }     
    if ($stripper->offsetExists('cpk')) {$vCpk = $stripper->offsetGet('cpk')->getFilterValue(); }     
    
    $resDataDeleted = $BLL->deletedAct(array(                  
            'id' => $vId ,    
            'pk' => $pk,        
            'cpk' => $vCpk,  
            ));
    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataDeleted));
}
); 

  
/**x
 *  * Okan CIRAN
* @since 18.07.2016
 */
$app->get("/pkcpkUpdate_infoFirmWorkingPersonnel/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');   
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkUpdate_infoFirmWorkingPersonnel" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];    
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
    $vId = NULL;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    } 
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vSexId = NULL;
    if (isset($_GET['sex_id'])) {
         $stripper->offsetSet('sex_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['sex_id']));
    } 
    $vTitle = NULL;
    if (isset($_GET['title'])) {
         $stripper->offsetSet('title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title']));
    } 
    $vTitleEng = NULL;
    if (isset($_GET['title_eng'])) {
         $stripper->offsetSet('title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title_eng']));
    } 
    $vName = NULL;
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name']));
    }
    $vSurname = NULL;
    if (isset($_GET['surname'])) {
         $stripper->offsetSet('surname',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['surname']));
    } 
    $vPositions = NULL;
    if (isset($_GET['positions'])) {
         $stripper->offsetSet('positions',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['positions']));
    } 
    $vPositionsEng = NULL;
    if (isset($_GET['positions_eng'])) {
         $stripper->offsetSet('positions_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['positions_eng']));
    } 
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    } 
    if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    }    
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('sex_id')) {
        $vSexId = $stripper->offsetGet('sex_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    } 
    if ($stripper->offsetExists('surname')) {
        $vSurname = $stripper->offsetGet('surname')->getFilterValue();
    }
    if ($stripper->offsetExists('title')) {
        $vTitle = $stripper->offsetGet('title')->getFilterValue();
    }
    if ($stripper->offsetExists('title_eng')) {
        $vTitleEng = $stripper->offsetGet('title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('positions')) {
        $vPositions = $stripper->offsetGet('positions')->getFilterValue();
    }
    if ($stripper->offsetExists('positions_eng')) {
        $vPositionsEng = $stripper->offsetGet('positions_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('cpk')) {
        $vCpk = $stripper->offsetGet('cpk')->getFilterValue();
    }
   
    $resData = $BLL->update(array( 
            'cpk'=> $vCpk,  
            'id' => $vId,
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,
            'name' => $vName,  
            'surname' => $vSurname,  
            'title' => $vTitle,  
            'title_eng' => $vTitleEng,  
            'positions' => $vPositionsEng,  
            'positions_eng' => $vPositionsEng,  
            'sex_id' => $vSexId, 
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resData));
}
); 
 

/**x
 *  * Okan CIRAN
* @since 18.07.2016
 */ 
$app->get("/pkcpkInsert_infoFirmWorkingPersonnel/", function () use ($app ) {  
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');  
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkcpkInsert_infoFirmWorkingPersonnel" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
 
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
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vSexId = NULL;
    if (isset($_GET['sex_id'])) {
         $stripper->offsetSet('sex_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['sex_id']));
    } 
    $vTitle = NULL;
    if (isset($_GET['title'])) {
         $stripper->offsetSet('title',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title']));
    } 
    $vTitleEng = NULL;
    if (isset($_GET['title_eng'])) {
         $stripper->offsetSet('title_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['title_eng']));
    } 
    $vName = NULL;
    if (isset($_GET['name'])) {
         $stripper->offsetSet('name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['name']));
    }
    $vSurname = NULL;
    if (isset($_GET['surname'])) {
         $stripper->offsetSet('surname',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['surname']));
    } 
    $vPositions = NULL;
    if (isset($_GET['positions'])) {
         $stripper->offsetSet('positions',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['positions']));
    } 
    $vPositionsEng = NULL;
    if (isset($_GET['positions_eng'])) {
         $stripper->offsetSet('positions_eng',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['positions_eng']));
    } 
    
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }     
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('sex_id')) {
        $vSexId = $stripper->offsetGet('sex_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('name')) {
        $vName = $stripper->offsetGet('name')->getFilterValue();
    } 
    if ($stripper->offsetExists('surname')) {
        $vSurname = $stripper->offsetGet('surname')->getFilterValue();
    }
    if ($stripper->offsetExists('title')) {
        $vTitle = $stripper->offsetGet('title')->getFilterValue();
    }
    if ($stripper->offsetExists('title_eng')) {
        $vTitleEng = $stripper->offsetGet('title_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('positions')) {
        $vPositions = $stripper->offsetGet('positions')->getFilterValue();
    }
    if ($stripper->offsetExists('positions_eng')) {
        $vPositionsEng = $stripper->offsetGet('positions_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('cpk')) {
        $vCpk = $stripper->offsetGet('cpk')->getFilterValue();
    }   
   
    $resDataInsert = $BLL->insert(array( 
            'cpk'=> $vCpk,  
            'language_code' => $vLanguageCode,
            'profile_public' => $vProfilePublic,
            'name' => $vName,  
            'surname' => $vSurname,  
            'title' => $vTitle,  
            'title_eng' => $vTitleEng,  
            'positions' => $vPositionsEng,  
            'positions_eng' => $vPositionsEng,  
            'sex_id' => $vSexId, 
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 


/**
 *  * Okan CIRAN
* @since 18.07.2016
 */
$app->get("/pkFillFirmWorkingPersonalNpk_infoFirmWorkingPersonnel/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');
 
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmWorkingPersonalNpk_infoFirmWorkingPersonnel" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vNetworkKey = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['npk']));
    }
  
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue(); 
    $resDataGrid = $BLL->fillFirmWorkingPersonalNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmWorkingPersonalNpkRtc(array(
        'network_key' => $vNetworkKey,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),                
                "name" => html_entity_decode($flow["name"]),
                "surname" => html_entity_decode($flow["surname"]),
                "title" => html_entity_decode($flow["title"]),
                "title_eng" => html_entity_decode($flow["title_eng"]),
                "positions" => html_entity_decode($flow["positions"]),
                "positions_eng" => html_entity_decode($flow["positions_eng"]),
                "language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
                "npk" => $flow["network_key"],  
                "attributes" => array("notroot" => true,),
            );
        }
       $counts = $resTotalRowCount[0]['count'];
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});


/**
 *  * Okan CIRAN
* @since 18.07.2016
 */
$app->get("/FillFirmWorkingPersonalNpkQuest_infoFirmWorkingPersonnel/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');  

    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $stripper->offsetSet('language_code', $stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE, $app, $_GET['language_code']));
    }
    $vNpk = NULL;
    if (isset($_GET['npk'])) {
        $stripper->offsetSet('npk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['npk']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();

    $resDataGrid = $BLL->fillFirmWorkingPersonalNpkQuest(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNpk,  
    ));    
    $resTotalRowCount = $BLL->fillFirmWorkingPersonalNpkQuestRtc(array(
        'network_key' => $vNpk,    
    ));
    $counts=0;  
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
              "id" => intval($flow["id"]),                
                "name" => html_entity_decode($flow["name"]),
                "surname" => html_entity_decode($flow["surname"]),
                "title" => html_entity_decode($flow["title"]),
                "title_eng" => html_entity_decode($flow["title_eng"]),
                "positions" => html_entity_decode($flow["positions"]),
                "positions_eng" => html_entity_decode($flow["positions_eng"]),
                //"language_id" => $flow["language_id"],
                "language_name" => html_entity_decode($flow["language_name"]),
               // "npk" => $flow["network_key"],  
                "attributes" => array("notroot" => true,),
            );
        }    
        $counts = $resTotalRowCount[0]['count'];
     }    

    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();  
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});

 
/**
 *  * Okan CIRAN
* @since 18.07.2016
 */
$app->get("/pkFillFirmWorkingPersonalListGrid_infoFirmWorkingPersonnel/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillFirmWorkingPersonalListGrid_infoFirmWorkingPersonnel" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];

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
    $vPositions = NULL;
    if (isset($_GET['positions'])) {
        $stripper->offsetSet('positions', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['positions']));
    }
    $vPositionsEng = NULL;
    if (isset($_GET['positions_eng'])) {
        $stripper->offsetSet('positions_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['positions_eng']));
    }
    $vTitle = NULL;
    if (isset($_GET['title'])) {
        $stripper->offsetSet('title', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['title']));
    }
    $vTitleEng = NULL;
    if (isset($_GET['title_eng'])) {
        $stripper->offsetSet('title_eng', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                $app, $_GET['title_eng']));
    } 
    $vSexId = NULL;
    if (isset($_GET['sex_id'])) {
        $stripper->offsetSet('sex_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['sex_id']));
    } 
    $vProfilePublic = NULL;
    if (isset($_GET['profile_public'])) {
        $stripper->offsetSet('profile_public', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['profile_public']));
    }
    $vActive = NULL;
    if (isset($_GET['active'])) {
        $stripper->offsetSet('active', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['active']));
    }
    $vOpUserName = NULL;
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
    if ($stripper->offsetExists('positions')) {
        $vPositions = $stripper->offsetGet('positions')->getFilterValue();
    } 
    if ($stripper->offsetExists('positions_eng')) {
        $vPositionsEng = $stripper->offsetGet('positions_eng')->getFilterValue();
    }
    if ($stripper->offsetExists('title')) {
        $vTitle = $stripper->offsetGet('title')->getFilterValue();
    }
    if ($stripper->offsetExists('title_eng')) {
        $vTitleEng = $stripper->offsetGet('title_eng')->getFilterValue();
    }    
    if ($stripper->offsetExists('sex_id')) {
        $vSexId = $stripper->offsetGet('sex_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
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
    
    $resDataGrid = $BLL->fillFirmWorkingPersonalListGrid(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'name' => $vName,
        'surname' => $vSurname,
        'positions' => $vPositions,        
        'positions_eng' => $vPositionsEng,
        'title' => $vTitle,
        'title_eng' => $vTitleEng,       
        'sex_id' => $vSexId,       
        'profile_public' => $vProfilePublic,       
        'active' => $vActive,       
        'op_user_name' => $vOpUserName,        
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillFirmWorkingPersonalListGridRtc(array(
        'name' => $vName,
        'surname' => $vSurname,
        'positions' => $vPositions,        
        'positions_eng' => $vPositionsEng,
        'title' => $vTitle,
        'title_eng' => $vTitleEng,       
        'sex_id' => $vSexId,       
        'profile_public' => $vProfilePublic,       
        'active' => $vActive,       
        'op_user_name' => $vOpUserName,        
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "positions" => $flow["positions"],
            "positions_eng" => $flow["positions_eng"],
            "title" => $flow["title"],
            "title_eng" => $flow["title_eng"],
            "sex_id" => $flow["sex_id"],
            "sex_name" => $flow["sex_name"],
            "profile_public" => $flow["profile_public"],  
            "state_profile_public" => $flow["state_profile_public"],   
            "act_parent_id" => $flow["act_parent_id"],   
            "language_id" => $flow["language_id"],   
            "language_name" => $flow["language_name"],   
            "state_active" => $flow["state_active"],  

            "op_user_id" => $flow["op_user_id"],  
            "op_user_name" => $flow["op_user_name"],  
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "cons_allow_id" => $flow["cons_allow_id"],  
            "cons_allow" => $flow["cons_allow"],   
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



$app->run();