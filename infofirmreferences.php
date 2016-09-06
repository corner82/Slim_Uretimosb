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
 * @since 11-03-2016
 */
$app->get("/pkGetAll_infoFirmReferences/", function () use ($app ) {
    $BLL = $app->getBLLManager()->get('infoFirmReferencesBLL');  
    $headerParams = $app->request()->headers();
    $pk = $headerParams['X-Public']  ;     
    $resDataMenu = $BLL->pkGetLeftMenu(array(
                                           'language_code' => $_GET['language_code'], 
                                           'pk' => $pk ,
                                           ) );   
 
    $menus = array();
    foreach ($resDataMenu as $menu){
        $menus[]  = array(
            "id" => $menu["id"],
             "firm_name" => $menu["firm_names"],
             "ref_name" => $menu["ref_names"],
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
             "consultant_id" => $menu["consultant_id"],
             "consultant_confirm_type_id" => $menu["consultant_confirm_type_id"],
             "operation_name" => $menu["consultant_confirm_type"],
             "confirm_id" => $menu["confirm_id"],
             "network_key" => $menu["Ref_network_key"],            
        );
    }
    
  $app->response()->header("Content-Type", "application/json");    
  $app->response()->body(json_encode($menus));
  
});

  
 
/**
 *  * Okan CIRAN
 * @since 11-03-2016
 */
$app->get("/pkFillWithReference_infoFirmReferences/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('infoFirmReferencesBLL');
 
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkGetConsConfirmationProcessDetails_sysOsbConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vFirmId = Null;
    if (isset($_GET['firm_id'])) {
        $stripper->offsetSet('firm_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['firm_id']));
    }
    
    $vPage = 1;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['page']));
    }
    $vRows = 10;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rows']));
    }
    
    $vfilterRules = null;
    if(isset($_GET['filterRules'])) {
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ALPHABETIC_ALLOWED ,
                                                $app,
                                                $_GET['filterRules']));
    }
    $vSort = null;
    if(isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sort']));
    }
    $vOrder = null;
    if(isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['order']));
    }
   
  
     $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('firm_id')) $vFirmId = $stripper->offsetGet('role_id')->getFilterValue();
    if($stripper->offsetExists('page')) $vPage = $stripper->offsetGet('page')->getFilterValue();
    if($stripper->offsetExists('rows')) $vRows = $stripper->offsetGet('rows')->getFilterValue();
    if($stripper->offsetExists('sort')) $vSort = $stripper->offsetGet('sort')->getFilterValue();
    if($stripper->offsetExists('order')) $vOrder = $stripper->offsetGet('order')->getFilterValue();
    if($stripper->offsetExists('filterRules')) $vfilterRules = $stripper->offsetGet('filterRules')->getFilterValue();
    
 

    $resDataGrid = $BLL->fillWithReference(array('language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder, 
        'firm_id' => $vFirmId,
        'pk' => $pk,        
        'filterRules' => $vfilterRules));    
 
    $resTotalRowCount = $BLL->fillWithReferenceRtc(array('pk' => $pk,
                                                'language_code' => $vLanguageCode,
                                                'firm_id' => $vFirmId,
                                                'filterRules' => $vfilterRules   ));
 
                                            
    $flows = array();
    foreach ($resDataGrid['resultSet'] as $flow) {
        $flows[] = array(
            "id" => intval($flow["id"]),
            "ref_firm_name" => $flow["ref_firm_names"],
            "ref_date" => $flow["ref_date"],
            "network_key" => $flow["Ref_network_key"],
            "url" => $flow["url"],            
            "attributes" => array("notroot" => true, "active" => intval($flow["active"]), ),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] =  $resTotalRowCount['resultSet'][0]['count'];
    $resultArray['rows'] = $flows;

    $app->response()->body(json_encode($resultArray));

});

 
/**
 *  * Okan CIRAN
 * @since 11-03-2016
 */
$app->get("/pkFillBeReferenced_infoFirmReferences/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory(); 
    $BLL = $app->getBLLManager()->get('infoFirmReferencesBLL');
 
    $headerParams = $app->request()->headers();
    if(!isset($headerParams['X-Public'])) throw new Exception ('rest api "pkGetConsConfirmationProcessDetails_sysOsbConsultants" end point, X-Public variable not found');
    $pk = $headerParams['X-Public'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }  
    $vRefFirmId = Null;
    if (isset($_GET['ref_firm_id'])) {
        $stripper->offsetSet('ref_firm_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['ref_firm_id']));
    }
    
    $vPage = 1;
    if (isset($_GET['page'])) {
        $stripper->offsetSet('page', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['page']));
    }
    $vRows = 10;
    if (isset($_GET['rows'])) {
        $stripper->offsetSet('rows', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['rows']));
    }
    
    $vfilterRules = null;
    if(isset($_GET['filterRules'])) {
        $stripper->offsetSet('filterRules', $stripChainerFactory->get(stripChainers::FILTER_ONLY_ALPHABETIC_ALLOWED ,
                                                $app,
                                                $_GET['filterRules']));
    }
    $vSort = null;
    if(isset($_GET['sort'])) {
        $stripper->offsetSet('sort', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['sort']));
    }
    $vOrder = null;
    if(isset($_GET['order'])) {
        $stripper->offsetSet('order', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['order']));
    }
   
  
     $stripper->strip();
    if($stripper->offsetExists('language_code')) $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    if($stripper->offsetExists('ref_firm_id')) $vRefFirmId = $stripper->offsetGet('role_id')->getFilterValue();
    if($stripper->offsetExists('page')) $vPage = $stripper->offsetGet('page')->getFilterValue();
    if($stripper->offsetExists('rows')) $vRows = $stripper->offsetGet('rows')->getFilterValue();
    if($stripper->offsetExists('sort')) $vSort = $stripper->offsetGet('sort')->getFilterValue();
    if($stripper->offsetExists('order')) $vOrder = $stripper->offsetGet('order')->getFilterValue();
    if($stripper->offsetExists('filterRules')) $vfilterRules = $stripper->offsetGet('filterRules')->getFilterValue();
    
 

    $resDataGrid = $BLL->fillBeReferenced(array('language_code' => $vLanguageCode,
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder, 
        'ref_firm_id' => $vRefFirmId,
        'pk' => $pk,        
        'filterRules' => $vfilterRules));    
 
    $resTotalRowCount = $BLL->fillBeReferencedRtc(array('pk' => $pk,
                                                'language_code' => $vLanguageCode,
                                                'ref_firm_id' => $vRefFirmId,
                                                'filterRules' => $vfilterRules   ));
 
                                            
    $flows = array();
    foreach ($resDataGrid['resultSet'] as $flow) {
        $flows[] = array(
            "id" => intval($flow["id"]),
            "firm_name" => $flow["firm_names"],
            "ref_date" => $flow["ref_date"],
            "network_key" => $flow["network_key"],
            "url" => $flow["url"],
            "attributes" => array("notroot" => true, "active" => intval($flow["active"]), ),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] =  $resTotalRowCount['resultSet'][0]['count'];
    $resultArray['rows'] = $flows;

    $app->response()->body(json_encode($resultArray));

});

  
/**x
 *  * Okan CIRAN
 * @since 11-03-2016
 */
$app->get("/pkDeletedAct_infoFirmReferences/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmReferencesBLL');
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
 * @since 11-03-2016
 */
$app->get("/pkUpdate_infoFirmReferences/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmReferencesBLL');   
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
 * @since 11-03-2016
 */
$app->get("/pkInsert_infoFirmReferences/", function () use ($app ) {    
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();   
    $BLL = $app->getBLLManager()->get('infoFirmReferencesBLL');   
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


$app->run();