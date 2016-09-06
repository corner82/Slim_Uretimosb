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
 * @since 19.07.2016
 */
$app->get("/pkGetAll_infoFirmWorkingPersonnelEducation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkGetAll_infoFirmWorkingPersonnelEducation" end point, X-Public variable not found');
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
* @since 19.07.2016
 */
$app->get("/pkcpkDeletedAct_infoFirmWorkingPersonnelEducation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkcpkDeletedAct_infoFirmWorkingPersonnelEducation" end point, X-Public variable not found');
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
* @since 19.07.2016
 */
 
$app->get("/pkcpkUpdate_infoFirmWorkingPersonnelEducation/", function () use ($app ) {  
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');  
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkcpkUpdate_infoFirmWorkingPersonnelEducation" end point, X-Public variable not found');
    }
    $pk = $headerParams['X-Public'];
 
    $vCpk = NULL;
    if (isset($_GET['cpk'])) {
        $stripper->offsetSet('cpk', $stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2, 
                                                        $app, 
                                                        $_GET['cpk']));
    }
    $vId = 0;
    if (isset($_GET['id'])) {
         $stripper->offsetSet('id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['id']));
    }     
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
    $vDiplomaName = NULL;
    if (isset($_GET['diploma_name'])) {
         $stripper->offsetSet('diploma_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['diploma_name']));
    } 
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
    } 
    $vUniversityId = 0;
    if (isset($_GET['university_id'])) {
         $stripper->offsetSet('university_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['university_id']));
    } 
    $vGraduationDate = NULL;
    if (isset($_GET['graduation_date'])) {
         $stripper->offsetSet('graduation_date',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['graduation_date']));
    } 
    $vWorkingPersonnelId = 0;
    if (isset($_GET['working_personnel_id'])) {
         $stripper->offsetSet('working_personnel_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['working_personnel_id']));
    }        
            
       
    $stripper->strip(); 
     if ($stripper->offsetExists('id')) {
        $vId = $stripper->offsetGet('id')->getFilterValue();
    } 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('working_personnel_id')) {
        $vWorkingPersonnelId = $stripper->offsetGet('working_personnel_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('university_id')) {
        $vUniversityId = $stripper->offsetGet('university_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('graduation_date')) {
        $vGraduationDate = $stripper->offsetGet('graduation_date')->getFilterValue();
    }
    if ($stripper->offsetExists('diploma_name')) {
        $vDiplomaName = $stripper->offsetGet('diploma_name')->getFilterValue();
    }    
    if ($stripper->offsetExists('cpk')) {
        $vCpk = $stripper->offsetGet('cpk')->getFilterValue();
    }   
   
    $resDataInsert = $BLL->update(array( 
            'cpk'=> $vCpk,  
            'id'=> $vId,          
            'language_code' => $vLanguageCode,
            'working_personnel_id' => $vWorkingPersonnelId,
            'profile_public' => $vProfilePublic,
            'diploma_name' => $vDiplomaName,  
            'country_id' => $vCountryId,  
            'university_id' => $vUniversityId,  
            'graduation_date' => $vGraduationDate, 
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
);
 

/**x
 *  * Okan CIRAN
* @since 19.07.2016
 */ 
$app->get("/pkcpkInsert_infoFirmWorkingPersonnelEducation/", function () use ($app ) {  
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();    
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');  
    $headerParams = $app->request()->headers();  
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkcpkInsert_infoFirmWorkingPersonnelEducation" end point, X-Public variable not found');
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
    $vDiplomaName = NULL;
    if (isset($_GET['diploma_name'])) {
         $stripper->offsetSet('diploma_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['diploma_name']));
    } 
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
    } 
    $vUniversityId = 0;
    if (isset($_GET['university_id'])) {
         $stripper->offsetSet('university_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['university_id']));
    } 
    $vGraduationDate = NULL;
    if (isset($_GET['graduation_date'])) {
         $stripper->offsetSet('graduation_date',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['graduation_date']));
    } 
    $vWorkingPersonnelId = 0;
    if (isset($_GET['working_personnel_id'])) {
         $stripper->offsetSet('working_personnel_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['working_personnel_id']));
    }        
            
       
    $stripper->strip(); 
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('working_personnel_id')) {
        $vWorkingPersonnelId = $stripper->offsetGet('working_personnel_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('university_id')) {
        $vUniversityId = $stripper->offsetGet('university_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('graduation_date')) {
        $vGraduationDate = $stripper->offsetGet('graduation_date')->getFilterValue();
    }
    if ($stripper->offsetExists('diploma_name')) {
        $vDiplomaName = $stripper->offsetGet('diploma_name')->getFilterValue();
    }    
    if ($stripper->offsetExists('cpk')) {
        $vCpk = $stripper->offsetGet('cpk')->getFilterValue();
    }   
   
    $resDataInsert = $BLL->insert(array( 
            'cpk'=> $vCpk,  
            'language_code' => $vLanguageCode,
            'working_personnel_id' => $vWorkingPersonnelId,
            'profile_public' => $vProfilePublic,
            'diploma_name' => $vDiplomaName,  
            'country_id' => $vCountryId,  
            'university_id' => $vUniversityId,  
            'graduation_date' => $vGraduationDate, 
            'pk' => $pk,        
            ));

    $app->response()->header("Content-Type", "application/json"); 
    $app->response()->body(json_encode($resDataInsert));
}
); 


/**
 *  * Okan CIRAN
* @since 19.07.2016
 */
$app->get("/pkFillFirmWorkingPersonalEducationNpk_infoFirmWorkingPersonnelEducation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');
 
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public']))
        throw new Exception('rest api "pkFillFirmWorkingPersonalEducationNpk_infoFirmWorkingPersonnelEducation" end point, X-Public variable not found');
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
    $vWorkingPersonnelId = NULL;
    if (isset($_GET['working_personnel_id'])) {
        $stripper->offsetSet('working_personnel_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['working_personnel_id']));
    }
    
  
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNetworkKey = $stripper->offsetGet('npk')->getFilterValue(); 
    if ($stripper->offsetExists('working_personnel_id'))
        $vWorkingPersonnelId = $stripper->offsetGet('working_personnel_id')->getFilterValue(); 
    
    
    $resDataGrid = $BLL->fillFirmWorkingPersonalEducationNpk(array(
        'language_code' => $vLanguageCode,
        'network_key' => $vNetworkKey,
        'working_personnel_id' => $vWorkingPersonnelId,
        'pk' => $pk,
    ));
   
    $resTotalRowCount = $BLL->fillFirmWorkingPersonalEducationNpkRtc(array(
        'network_key' => $vNetworkKey,
        'working_personnel_id' => $vWorkingPersonnelId,
        'pk' => $pk,
    ));
    $counts=0;
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
                "id" => intval($flow["id"]),                 
                "working_personnel_id" => $flow["working_personnel_id"],
                "country_name" => html_entity_decode($flow["country_name"]),
                "country_name_eng" => html_entity_decode($flow["country_name_eng"]),
                "university_id" => $flow["university_id"],
                "university_name" => html_entity_decode($flow["university_name"]),
                "university_name_eng" => html_entity_decode($flow["university_name_eng"]),
                "graduation_date" => $flow["graduation_date"],
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
* @since 19.07.2016
 */
$app->get("/FillFirmWorkingPersonalEducationNpkQuest_infoFirmWorkingPersonnelEducation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');  

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
     $vWorkingPersonnelId = NULL;
    if (isset($_GET['working_personnel_id'])) {
        $stripper->offsetSet('working_personnel_id', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['working_personnel_id']));
    }
    $stripper->strip();
    if ($stripper->offsetExists('language_code'))
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();    
    if ($stripper->offsetExists('npk'))
        $vNpk = $stripper->offsetGet('npk')->getFilterValue();
    if ($stripper->offsetExists('working_personnel_id'))
        $vWorkingPersonnelId = $stripper->offsetGet('working_personnel_id')->getFilterValue(); 

    $resDataGrid = $BLL->fillFirmWorkingPersonalEducationNpkQuest(array(
        'language_code' => $vLanguageCode,
        'working_personnel_id' => $vWorkingPersonnelId,
        'network_key' => $vNpk,  
    ));    
    $resTotalRowCount = $BLL->fillFirmWorkingPersonalEducationNpkQuestRtc(array(
        'working_personnel_id' => $vWorkingPersonnelId,
        'network_key' => $vNpk,    
    ));
    $counts=0;  
    $flows = array();            
    if (isset($resDataGrid[0]['id'])) {      
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
              "id" => intval($flow["id"]),                 
                //"working_personnel_id" => $flow["working_personnel_id"],
                "country_name" => html_entity_decode($flow["country_name"]),
                "country_name_eng" => html_entity_decode($flow["country_name_eng"]),
              //  "university_id" => $flow["university_id"],
                "university_name" => html_entity_decode($flow["university_name"]),
                "university_name_eng" => html_entity_decode($flow["university_name_eng"]),
                "graduation_date" => $flow["graduation_date"],
              //  "language_id" => $flow["language_id"],
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
* @since 19.07.2016
 */
$app->get("/pkFillFirmWorkingPersonalEducationListGrid_infoFirmWorkingPersonnelEducation/", function () use ($app ) {
    $stripper = $app->getServiceManager()->get('filterChainerCustom');
    $stripChainerFactory = new \Services\Filter\Helper\FilterChainerFactory();
    $BLL = $app->getBLLManager()->get('infoFirmWorkingPersonnelEducationBLL');
    $headerParams = $app->request()->headers();
    if (!isset($headerParams['X-Public'])) {
        throw new Exception('rest api "pkFillFirmWorkingPersonalEducationListGrid_infoFirmWorkingPersonnelEducation" end point, X-Public variable not found');
    }
  //  $pk = $headerParams['X-Public'];
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
         $stripper->offsetSet('language_code',$stripChainerFactory->get(stripChainers::FILTER_ONLY_LANGUAGE_CODE,
                                                $app,
                                                $_GET['language_code']));
    }   
    $vDiplomaName = NULL;
    if (isset($_GET['diploma_name'])) {
         $stripper->offsetSet('diploma_name',$stripChainerFactory->get(stripChainers::FILTER_PARANOID_LEVEL2,
                                                $app,
                                                $_GET['diploma_name']));
    } 
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
         $stripper->offsetSet('profile_public',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['profile_public']));
    }       
    $vCountryId = 91;
    if (isset($_GET['country_id'])) {
         $stripper->offsetSet('country_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['country_id']));
    } 
    $vUniversityId = 0;
    if (isset($_GET['university_id'])) {
         $stripper->offsetSet('university_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['university_id']));
    }
    $vWorkingPersonnelId = 0;
    if (isset($_GET['working_personnel_id'])) {
         $stripper->offsetSet('working_personnel_id',$stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED,
                                                $app,
                                                $_GET['working_personnel_id']));
    }     
    $vActive = NULL;
    if (isset($_GET['active'])) {
        $stripper->offsetSet('active', $stripChainerFactory->get(stripChainers::FILTER_ONLY_NUMBER_ALLOWED, 
                $app, $_GET['active']));
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
    
    if ($stripper->offsetExists('language_code')) {
        $vLanguageCode = $stripper->offsetGet('language_code')->getFilterValue();
    }    
    if ($stripper->offsetExists('working_personnel_id')) {
        $vWorkingPersonnelId = $stripper->offsetGet('working_personnel_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('profile_public')) {
        $vProfilePublic = $stripper->offsetGet('profile_public')->getFilterValue();
    } 
    if ($stripper->offsetExists('country_id')) {
        $vCountryId = $stripper->offsetGet('country_id')->getFilterValue();
    } 
    if ($stripper->offsetExists('university_id')) {
        $vUniversityId = $stripper->offsetGet('university_id')->getFilterValue();
    }     
    if ($stripper->offsetExists('diploma_name')) {
        $vDiplomaName = $stripper->offsetGet('diploma_name')->getFilterValue();
    }        
    if ($stripper->offsetExists('active')) {
        $vActive = $stripper->offsetGet('active')->getFilterValue();
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
    
    $resDataGrid = $BLL->fillFirmWorkingPersonalEducationListGrid(array(        
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'language_code' => $vLanguageCode,
        'diploma_name' => $vDiplomaName,        
        'working_personnel_id' => $vWorkingPersonnelId,
        'profile_public' => $vProfilePublic,       
        'country_id' => $vCountryId, 
        'university_id' => $vUniversityId,
        'active' => $vActive, 
        'filterRules' => $filterRules,
    ));
    $resTotalRowCount = $BLL->fillFirmWorkingPersonalEducationListGridRtc(array(
        'page' => $vPage,
        'rows' => $vRows,
        'sort' => $vSort,
        'order' => $vOrder,
        'language_code' => $vLanguageCode,
        'diploma_name' => $vDiplomaName,        
        'working_personnel_id' => $vWorkingPersonnelId,
        'profile_public' => $vProfilePublic,       
        'country_id' => $vCountryId, 
        'university_id' => $vUniversityId,
        'active' => $vActive, 
        'filterRules' => $filterRules,
    ));
    $counts = 0;
    $flows = array();
    if (isset($resDataGrid[0]['id'])) {
        foreach ($resDataGrid as $flow) {
            $flows[] = array(
            "id" => $flow["id"],
            "working_personnel_id" => $flow["working_personnel_id"],
            "name" => html_entity_decode($flow["name"]),
            "surname" => html_entity_decode($flow["surname"]),
            "diploma_name" => html_entity_decode($flow["diploma_name"]),
            "country_id" => $flow["country_id"],    
            "country_name" => html_entity_decode($flow["country_name"]),
            "country_name_eng" => html_entity_decode($flow["country_name_eng"]),
            "university_id" => $flow["university_id"],
            "university_name" => html_entity_decode($flow["university_name"]),
            "university_name_eng" => html_entity_decode($flow["university_name_eng"]),
            "graduation_date" => $flow["graduation_date"],
            "operation_type_id" => $flow["operation_type_id"],  
            "operation_name" => html_entity_decode($flow["operation_name"]),    
            "profile_public" => $flow["profile_public"],  
            "state_profile_public" => html_entity_decode($flow["state_profile_public"]),   
            "act_parent_id" => $flow["act_parent_id"],   
            "language_id" => $flow["language_id"],   
            "language_name" => html_entity_decode($flow["language_name"]),   
            "state_active" => html_entity_decode($flow["state_active"]),     
            "op_user_id" => $flow["op_user_id"],  
            "op_user_name" => html_entity_decode($flow["op_user_name"]),  
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "cons_allow_id" => $flow["cons_allow_id"],  
            "cons_allow" => html_entity_decode($flow["cons_allow"]),   
            "attributes" => array(              
                "active" => $flow["active"], ) );
        }
        $counts = $resTotalRowCount[0]['count'];
    }   
    
    $app->response()->header("Content-Type", "application/json");
    $resultArray = array();
    $resultArray['total'] = $counts;
    $resultArray['rows'] = $flows;
    $app->response()->body(json_encode($resultArray));
});



$app->run();