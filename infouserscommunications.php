<?php

// test commit for branch slim2
require 'vendor/autoload.php';




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
$app->add(new \Slim\Middleware\MiddlewareMQManager());



  
/**
 * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkFillGridSingular_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
    $fPk = $vPk ; 
    $vlanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $vlanguageCode = strtolower(trim($_GET['language_code']));
    }
    
    $resDataGrid = $BLL->fillGridSingular(array(
                                            'pk' => $fPk,
                                            'language_code' => $vlanguageCode
                                            ));

    $resTotalRowCount = $BLL->fillGridSingularRowTotalCount(array(
                                                                'pk' => $fPk,
                                                                'language_code' =>$vlanguageCode
                                                                 ));

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "profile_public" => $flow["profile_public"],
            "user_id" => $flow["user_id"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "language_code" => $flow["language_code"],
            "language_name" => $flow["language_name"],
            "language_parent_id" => $flow["language_parent_id"],
            "description" => $flow["description"],
            "description_eng" => $flow["description_eng"],
            "op_user_id" => $flow["op_user_id"],
            "op_username" => $flow["op_username"],
            "communications_type_id" => $flow["communications_type_id"],
            "comminication_type" => $flow["comminication_type"],
            "communications_no" => $flow["communications_no"],  
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "consultant_confirm_type" => $flow["consultant_confirm_type"],              
            "confirm_id" => $flow["confirm_id"],  
            "operation_type_id" => $flow["operation_type_id"],              
            "operation_name" => $flow["operation_name"],
            "default_communication_id" => $flow["default_communication_id"],
            
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

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkFillGrid_infoUsersCommunications/", function () use ($app ) {
 
    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
     
    $resDataGrid = $BLL->fillGrid(array('page' => $_GET['page'],
        'rows' => $_GET['rows'],
        'sort' => $_GET['sort'],
        'order' => $_GET['order'],
        'search_name' => $vSearchName,
        'pk' => $pk
                ));

    $resTotalRowCount = $BLL->fillGridRowTotalCount(array('search_name' => $vSearchName));

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
             "id" => $flow["id"],
            "profile_public" => $flow["profile_public"],
            "user_id" => $flow["user_id"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "language_code" => $flow["language_code"],
            "language_name" => $flow["language_name"],
            "language_parent_id" => $flow["language_parent_id"],
            "description" => $flow["description"],
            "description_eng" => $flow["description_eng"],
            "op_user_id" => $flow["op_user_id"],
            "op_username" => $flow["op_username"],
            "communications_type_id" => $flow["communications_type_id"],
            "comminication_type" => $flow["comminication_type"],
            "communications_no" => $flow["communications_no"],   
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "consultant_confirm_type" => $flow["consultant_confirm_type"],              
            "confirm_id" => $flow["confirm_id"],  
            "operation_type_id" => $flow["operation_type_id"],              
            "operation_name" => $flow["operation_name"],
            "default_communication_id" => $flow["default_communication_id"],
            
            
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

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkInsert_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
    
    $vProfilePublic = $_GET['profile_public'];    
    $vLanguageCode = $_GET['language_code'];
    $vCommunicationsTypeId = $_GET['communications_type_id'];
    $vCommunicationsNo = $_GET['communications_no'];
    $vDescription = $_GET['description'];   
    $vDescriptionEng = $_GET['description_eng'];   
    $vDefaultCommunicationId = $_GET['default_communication_id'];   
    
   
    $vActive =0; 
    if (isset($_GET['active'])) {
        $vActive = $_GET['active'];
    }    
   
    $vOperationTypeId = 1;
    if (isset($_GET['operation_type_id'])) {
        $vOperationTypeId = $_GET['operation_type_id'];
    }
    $vUserId = NULL;
    if (isset($_GET['user_id'])) {
        $vUserId = $_GET['user_id'];
    }      
    $vConsAllowId = 0;
    if (isset($_GET['cons_allow_id'])) {
        $vConsAllowId = $_GET['cons_allow_id'];
    }
    $vActParentId = 0;
    if (isset($_GET['act_parent_id'])) {
        $vActParentId = $_GET['act_parent_id'];
    }  
    $vConsultantId = 0;
    if (isset($_GET['consultant_id'])) {
        $vConsultantId = $_GET['consultant_id'];
    }
    $vConsultantConfirmTypeId = 0;
    if (isset($_GET['consultant_confirm_type_id'])) {
        $vConsultantConfirmTypeId = $_GET['consultant_confirm_type_id'];
    }
    $vConfirmId = 0;
    if (isset($_GET['confirm_id'])) {
        $vConsultantConfirmTypeId = $_GET['confirm_id'];
    } 

    
    $fUserId = $vUserId ; 
    $fOperationTypeId = $vOperationTypeId;    
    $fActive =$vActive;
    $fActParentId =$vActParentId;
    $fLanguageCode = $vLanguageCode;
    $fProfilePublic = $vProfilePublic;
    $fCommunicationsTypeId = $vCommunicationsTypeId; 
    $fCommunicationsNo = $vCommunicationsNo ; 
    $fDescription = $vDescription; 
    $fDescriptionEng = $vDescriptionEng ;
    $fConsAllowId = $vConsAllowId ; 
    $fConsultantId = $vConsultantId;
    $fConsultantConfirmTypeId = $vConsultantConfirmTypeId;
    $fConfirmId = $vConfirmId ; 
    $fDefaultCommunicationId = $vDefaultCommunicationId ; 
    $fpk = $vPk ; 
     
    
    $resDataInsert = $BLL->insert(array(  
            'user_id' =>$fUserId , 
            'operation_type_id' => $fOperationTypeId,
            'active' => $fActive,        
            'act_parent_id' => $fActParentId,
            'language_code' => $fLanguageCode,
            'profile_public' => $fProfilePublic,              
            'cons_allow_id' => $fConsAllowId,  
            'communications_type_id' => $fCommunicationsTypeId, 
            'communications_no' => $fCommunicationsNo,
            'description' => $fDescription ,
            'description_eng' => $fDescriptionEng ,  
            'consultant_id'  => $fConsultantId,
            'consultant_confirm_type_id' => $fConsultantConfirmTypeId,
            'confirm_id' =>  $fConfirmId,
            'default_communication_id' =>    $fDefaultCommunicationId,
            'pk' => $fpk,        
            ));

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataInsert));
}
); 

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkUpdate_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vpk = $headerParams['X-Public'];    
    $vID =$_GET['id'];    
    $vProfilePublic = $_GET['profile_public'];    
    $vLanguageCode = $_GET['language_code'];
    $vCommunicationsTypeId = $_GET['communications_type_id'];
    $vCommunicationsNo = $_GET['communications_no'];
    $vDescription = $_GET['description'];   
    $vDescriptionEng = $_GET['description_eng'];    
    $vDefaultCommunicationId = $_GET['default_communication_id'];   
     
    
    $vActive =0; 
    if (isset($_GET['active'])) {
        $vActive = $_GET['active'];
    }
    $vOperationTypeId = 1;
    if (isset($_GET['operation_type_id'])) {
        $vOperationTypeId = $_GET['operation_type_id'];
    }
    $vUserId = NULL;
    if (isset($_GET['user_id'])) {
        $vUserId = $_GET['user_id'];
    } 
    
    $vConsAllowId = 0;
    if (isset($_GET['cons_allow_id'])) {
        $vConsAllowId = $_GET['cons_allow_id'];
    } 
    $vActParentId = 0;
    if (isset($_GET['act_parent_id'])) {
        $vActParentId = $_GET['act_parent_id'];
    }  
    $vConsultantId = 0;
    if (isset($_GET['consultant_id'])) {
        $vConsultantId = $_GET['consultant_id'];
    } 
    
    $vConsultantConfirmTypeId = 0;
    if (isset($_GET['consultant_confirm_type_id'])) {
        $vConsultantConfirmTypeId = $_GET['consultant_confirm_type_id'];
    } 
    
    $vConfirmId = 0;
    if (isset($_GET['confirm_id'])) {
        $vConsultantConfirmTypeId = $_GET['confirm_id'];
    } 
    
   
    $validater = $app->getServiceManager()->get('validationChainerServiceForZendChainer');    
    $validatorChainUrl = new Zend\Validator\ValidatorChain();
    $validater->offsetSet(array_search($_GET['url'], $_GET), 
            new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                              $_GET['url'], 
                                                              $validatorChainUrl->attach(
                                                                        new Zend\Validator\StringLength(array('min' => 6,
                                                                                                              'max' => 50)))
                                                                              // ->attach(new Zend\I18n\Validator\Alnum())    
                    ) );
   
    $validatorChainLanguageCode = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('language_code', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vLanguageCode, 
                                                          $validatorChainLanguageCode->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 2,
                                                                                                          'max' => 2)))
                                                                          ->attach(new Zend\I18n\Validator\Alpha()) 
                                                                                 
                ) );
        
    $validatorChainId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vID, 
                                                          $validatorChainId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                         // ,'max' => 2
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
                                                                                 
                ) );
        
    $validatorChainOperationTypeId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('operation_type_id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vOperationTypeId, 
                                                          $validatorChainOperationTypeId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                         // ,'max' => 2
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
                                                                                 
                ) ); 
  
    $validatorChainActive = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('active', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vActive, 
                                                          $validatorChainActive->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
                                                                                 
                ) ); 
        
    $validatorChainProfilePublic = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('profile_public', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vProfilePublic, 
                                                          $validatorChainProfilePublic->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
         ) ); 
         
        
    $validatorChainConsAllowId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('cons_allow_id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vConsAllowId, 
                                                          $validatorChainConsAllowId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
         ) ); 
        
     
    $validatorChainActParentId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('act_parent_id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vActParentId, 
                                                          $validatorChainActParentId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
         ) );  
        
 
        
    $validater->validate();
    $messager = $app->getServiceManager()->get('validatorMessager');  
    print_r( $messager->getValidationMessage());
    
      
    $fID = $vID;   
    $fUserId = $vUserId ; 
    $fOperationTypeId = $vOperationTypeId;    
    $fActive =$vActive;
    $fActParentId =$vActParentId;
    $fLanguageCode = $vLanguageCode;
    $fProfilePublic = $vProfilePublic;
    $fCommunicationsTypeId = $vCommunicationsTypeId; 
    $fCommunicationsNo = $vCommunicationsNo ; 
    $fDescription = $vDescription; 
    $fDescriptionEng = $vDescriptionEng ;
    $fConsAllowId = $vConsAllowId ; 
    $fConsultantId = $vConsultantId;
    $fConsultantConfirmTypeId = $vConsultantConfirmTypeId;
    $fConfirmId = $vConfirmId ; 
    $fDefaultCommunicationId = $vDefaultCommunicationId ;                    
    $fpk = $vpk ; 
    
  
    
    /*
     * filtre işlemleri
     */
    
    $resDataUpdate = $BLL->update(array(
        'id' =>$fID,  
        'user_id' =>  $fUserId , 
        'operation_type_id' => $fOperationTypeId,
        'active' => $fActive,        
        'act_parent_id' => $fActParentId,
        'language_code' => $fLanguageCode,
        'profile_public' => $fProfilePublic,              
        'cons_allow_id' => $fConsAllowId,  
        'communications_type_id' => $fCommunicationsTypeId, 
        'communications_no' => $fCommunicationsNo,
        'description' => $fDescription ,
        'description_eng' => $fDescriptionEng ,  
        'consultant_id'  => $fConsultantId,
        'consultant_confirm_type_id' => $fConsultantConfirmTypeId,
        'confirm_id' =>  $fConfirmId,
        'default_communication_id' => $fDefaultCommunicationId,
        'pk' => $fpk,
         ));
    
   
    $app->response()->header("Content-Type", "application/json");


    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataUpdate));
});

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/pkDeletedAct_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vpk = $headerParams['X-Public'];
    $vID =$_GET['id'];  
    $vActParentId = 0;
    if (isset($_GET['act_parent_id'])) {
        $vActParentId = $_GET['act_parent_id'];
    }  
    $vOperationTypeId = 3;
    if (isset($_GET['operation_type_id'])) {
        $vOperationTypeId = $_GET['operation_type_id'];
    }
    
    $fpk = $vpk ; 
    $fID = $vID ; 
    $fActParentId = $vActParentId ; 
    $fOperationTypeId = $vOperationTypeId ; 
    
    
    $resDataUpdate = $BLL->deletedAct(array(
        'id' => $fID,        
        'operation_type_id' => $fActParentId,
        'act_parent_id' => $fOperationTypeId,
        'pk' => $fpk));
 
    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataUpdate));
});
 

/**
 *  * Okan CIRAN
 * @since 25-01-2016
 */
$app->get("/pkGetAll_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
    
    $fPk = $vPk ; 
    
    $resDataGrid = $BLL->getAll(array('pk' => $fPk));

    $resTotalRowCount = $BLL->fillGridRowTotalCount();

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "profile_public" => $flow["profile_public"],
            "user_id" => $flow["user_id"],
            "s_date" => $flow["s_date"],
            "c_date" => $flow["c_date"],
            "name" => $flow["name"],
            "surname" => $flow["surname"],
            "deleted" => $flow["deleted"],
            "state_deleted" => $flow["state_deleted"],
            "active" => $flow["active"],
            "state_active" => $flow["state_active"],
            "language_code" => $flow["language_code"],
            "language_name" => $flow["language_name"],
            "language_parent_id" => $flow["language_parent_id"],
            "description" => $flow["description"],
            "description_eng" => $flow["description_eng"],
            "op_user_id" => $flow["op_user_id"],
            "op_username" => $flow["op_username"],
            "communications_type_id" => $flow["communications_type_id"],
            "comminication_type" => $flow["comminication_type"],
            "communications_no" => $flow["communications_no"],  
            "consultant_id" => $flow["consultant_id"],  
            "consultant_confirm_type_id" => $flow["consultant_confirm_type_id"],  
            "consultant_confirm_type" => $flow["consultant_confirm_type"],              
            "confirm_id" => $flow["confirm_id"],  
            "operation_type_id" => $flow["operation_type_id"],              
            "operation_name" => $flow["operation_name"], 
            "default_communication_id" => $flow["default_communication_id"], 
            "default_communication" => $flow["default_communication"],             
   
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

/**
 *  * Okan CIRAN
 * @since 01-02-2016
 */
$app->get("/fillUserCommunicationsTypes_infoUsersCommunications/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    $headerParams = $app->request()->headers();
    $vPk = $headerParams['X-Public'];
    $fPk =$vPk ; 
    
    $resCombobox = $BLL->fillUserCommunicationsTypes(array('pk' => $fPk));

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => $flow["name"],
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true,   ),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($flows));
});







  
/**x
 * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillGridSingular_infoUsersCommunications/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];
    
    $fPkTemp = $vPkTemp ; 
    
    $languageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $languageCode = strtolower(trim($_GET['language_code']));
    }
     $componentType = 'bootstrap';
    if (isset($_GET['component_type'])) {
        $componentType = strtolower(trim($_GET['component_type']));
    }

    
    $resDataGrid = $BLL->fillGridSingularTemp(array('pktemp' => $fPkTemp ,'language_code' => $languageCode ));

    $resTotalRowCount = $BLL->fillGridSingularRowTotalCountTemp(array('pktemp' => $fPkTemp,'language_code' => $languageCode ));

    $flows = array();
    foreach ($resDataGrid as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            "communications_type_id" => $flow["communications_type_id"],
            "comminication_type" => $flow["comminication_type"],
            "communications_no" => $flow["communications_no"],              
            "default_communication_id" => $flow["default_communication_id"],
            "default_communication" => $flow["default_communication"],

            
            "attributes" => array("notroot" => true, ),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    $resultArray = array();
    $resultArray['total'] = $resTotalRowCount[0]['count'];
    $resultArray['rows'] = $flows;
 

   // $app->response()->body(json_encode($flows));
    if($componentType == 'bootstrap'){
        $app->response()->body(json_encode($flows));
    }else if($componentType == 'easyui'){
        $app->response()->body(json_encode($resultArray));
    }
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempInsert_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
    
    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
    }
     
    $vDescriptionEng = '';
    if (isset($_GET['description_eng'])) {
        $vDescriptionEng = strtolower(trim($_GET['description_eng']));
    }
    $vProfilePublic = 0;
    if (isset($_GET['profile_public'])) {
        $vProfilePublic = strtolower(trim($_GET['profile_public']));
    } 
    $vDefaultCommunicationId = 0;
    if (isset($_GET['default_communication_id'])) {
        $vDefaultCommunicationId = strtolower(trim($_GET['default_communication_id']));
    } 
    
    $vCommunicationsTypeId = $_GET['communications_type_id'];
    $vCommunicationsNo = $_GET['communications_no'];
    $vDescription = $_GET['description'];       
  
     
    $vOperationTypeId = 1;
    if (isset($_GET['operation_type_id'])) {
        $vOperationTypeId = $_GET['operation_type_id'];
    }
      
    $vConsAllowId = 0;
    if (isset($_GET['cons_allow_id'])) {
        $vConsAllowId = $_GET['cons_allow_id'];
    }
    $vActParentId = 0;
    if (isset($_GET['act_parent_id'])) {
        $vActParentId = $_GET['act_parent_id'];
    }  
    $vConsultantId = 0;
    if (isset($_GET['consultant_id'])) {
        $vConsultantId = $_GET['consultant_id'];
    }
    $vConsultantConfirmTypeId = 0;
    if (isset($_GET['consultant_confirm_type_id'])) {
        $vConsultantConfirmTypeId = $_GET['consultant_confirm_type_id'];
    }
    $vConfirmId = 0;
    if (isset($_GET['confirm_id'])) {
        $vConsultantConfirmTypeId = $_GET['confirm_id'];
    } 

    
 
    $fOperationTypeId = $vOperationTypeId;    
   
    $fActParentId =$vActParentId;
    $fLanguageCode = $vLanguageCode;
    $fProfilePublic = $vProfilePublic;
    $fCommunicationsTypeId = $vCommunicationsTypeId; 
    $fCommunicationsNo = $vCommunicationsNo ; 
    $fDescription = $vDescription; 
    $fDescriptionEng = $vDescriptionEng ;
    $fConsAllowId = $vConsAllowId ; 
    $fConsultantId = $vConsultantId;
    $fConsultantConfirmTypeId = $vConsultantConfirmTypeId;
    $fConfirmId = $vConfirmId ; 
    $fDefaultCommunicationId = $vDefaultCommunicationId  ; 
    $fPkTemp = $vPkTemp ; 
    
    
    
    $resDataInsert = $BLL->insertTemp(array(           
            'operation_type_id' => $fOperationTypeId,              
            'act_parent_id' => $fActParentId,
            'language_code' => $fLanguageCode,
            'profile_public' => $fProfilePublic,              
            'cons_allow_id' => $fConsAllowId,  
            'communications_type_id' => $fCommunicationsTypeId, 
            'communications_no' => $fCommunicationsNo,
            'description' => $fDescription ,
            'description_eng' => $fDescriptionEng ,  
            'consultant_id'  => $fConsultantId,
            'consultant_confirm_type_id' => $fConsultantConfirmTypeId,
            'confirm_id' =>  $fConfirmId,
            'default_communication_id' => $fDefaultCommunicationId,
            'pktemp' => $fPkTemp,        
            ));

    $app->response()->header("Content-Type", "application/json");
 

    $app->response()->body(json_encode($resDataInsert));
}
); 

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempUpdate_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];  
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
    }
    
    $vActive =0; 
    if (isset($_GET['active'])) {
        $vActive = $_GET['active'];
    }
    $vOperationTypeId = 2;
    if (isset($_GET['operation_type_id'])) {
        $vOperationTypeId = $_GET['operation_type_id'];
    }      
    $vConsAllowId = 0;
    if (isset($_GET['cons_allow_id'])) {
        $vConsAllowId = $_GET['cons_allow_id'];
    } 
    $vActParentId = 0;
    if (isset($_GET['act_parent_id'])) {
        $vActParentId = $_GET['act_parent_id'];
    }  
    $vConsultantId = 0;
    if (isset($_GET['consultant_id'])) {
        $vConsultantId = $_GET['consultant_id'];
    }     
    $vConsultantConfirmTypeId = 0;
    if (isset($_GET['consultant_confirm_type_id'])) {
        $vConsultantConfirmTypeId = $_GET['consultant_confirm_type_id'];
    }     
    $vConfirmId = 0;
    if (isset($_GET['confirm_id'])) {
        $vConsultantConfirmTypeId = $_GET['confirm_id'];
    } 
    
    $vID =$_GET['id'];    
    $vProfilePublic = $_GET['profile_public']; 
    $vCommunicationsTypeId = $_GET['communications_type_id'];
    $vCommunicationsNo = $_GET['communications_no'];
    $vDescription = $_GET['description'];   
    $vDescriptionEng = $_GET['description_eng'];   
    $vDefaultCommunicationId = $_GET['default_communication_id'];   
    
      
    
   
    $validater = $app->getServiceManager()->get('validationChainerServiceForZendChainer');    
    $validatorChainUrl = new Zend\Validator\ValidatorChain();
    $validater->offsetSet(array_search($_GET['url'], $_GET), 
            new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                              $_GET['url'], 
                                                              $validatorChainUrl->attach(
                                                                        new Zend\Validator\StringLength(array('min' => 6,
                                                                                                              'max' => 50)))
                                                                              // ->attach(new Zend\I18n\Validator\Alnum())    
                    ) );
   
    $validatorChainLanguageCode = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('language_code', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vLanguageCode, 
                                                          $validatorChainLanguageCode->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 2,
                                                                                                          'max' => 2)))
                                                                          ->attach(new Zend\I18n\Validator\Alpha()) 
                                                                                 
                ) );
        
    $validatorChainId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vID, 
                                                          $validatorChainId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                         // ,'max' => 2
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
                                                                                 
                ) );
        
    $validatorChainOperationTypeId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('operation_type_id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vOperationTypeId, 
                                                          $validatorChainOperationTypeId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                         // ,'max' => 2
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
                                                                                 
                ) ); 
  
    $validatorChainActive = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('active', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vActive, 
                                                          $validatorChainActive->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
                                                                                 
                ) ); 
        
    $validatorChainProfilePublic = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('profile_public', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vProfilePublic, 
                                                          $validatorChainProfilePublic->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
         ) ); 
         
        
    $validatorChainConsAllowId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('cons_allow_id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vConsAllowId, 
                                                          $validatorChainConsAllowId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
         ) ); 
        
     
    $validatorChainActParentId = new Zend\Validator\ValidatorChain();
    $validater->offsetSet('act_parent_id', 
    new \Utill\Validation\Chain\ZendValidationChainer($app, 
                                                          $vActParentId, 
                                                          $validatorChainActParentId->attach(
                                                                    new Zend\Validator\StringLength(array('min' => 1
                                                                                                          ,'max' => 1
                                                                        )))
                                                                          ->attach(new Zend\Validator\Digits()) 
         ) );  
        
 
        
    $validater->validate();
    $messager = $app->getServiceManager()->get('validatorMessager');  
    print_r( $messager->getValidationMessage());
    
      
    $fID = $vID;  
    $fOperationTypeId = $vOperationTypeId;    
    $fActive =$vActive;
    $fActParentId =$vActParentId;
    $fLanguageCode = $vLanguageCode;
    $fProfilePublic = $vProfilePublic;
    $fCommunicationsTypeId = $vCommunicationsTypeId; 
    $fCommunicationsNo = $vCommunicationsNo ; 
    $fDescription = $vDescription; 
    $fDescriptionEng = $vDescriptionEng ;
    $fConsAllowId = $vConsAllowId ; 
    $fConsultantId = $vConsultantId;
    $fConsultantConfirmTypeId = $vConsultantConfirmTypeId;
    $fConfirmId = $vConfirmId ; 
    $fDefaultCommunicationId = $vDefaultCommunicationId ; 
    $fPkTemp = $vPkTemp ; 
    
  
    
    /*
     * filtre işlemleri
     */
    
    $resDataUpdate = $BLL->updateTemp(array(
        'id' =>$fID, 
    
        'operation_type_id' => $fOperationTypeId,
        'active' => $fActive,        
        'act_parent_id' => $fActParentId,
        'language_code' => $fLanguageCode,
        'profile_public' => $fProfilePublic,              
        'cons_allow_id' => $fConsAllowId,  
        'communications_type_id' => $fCommunicationsTypeId, 
        'communications_no' => $fCommunicationsNo,
        'description' => $fDescription ,
        'description_eng' => $fDescriptionEng ,  
        'consultant_id'  => $fConsultantId,
        'consultant_confirm_type_id' => $fConsultantConfirmTypeId,
        'confirm_id' =>  $fConfirmId,
        'default_communication_id' => $fDefaultCommunicationId,
        'pktemp' => $fPkTemp,
         ));
    
   
    $app->response()->header("Content-Type", "application/json");


    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataUpdate));
});

/**x
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempDeletedAct_infoUsersCommunications/", function () use ($app ) {

    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');

    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];  
    
    $vID =$_GET['id'];  
    $vActParentId = 0;
    if (isset($_GET['act_parent_id'])) {
        $vActParentId = $_GET['act_parent_id'];
    }  
    $vOperationTypeId = 3;
    if (isset($_GET['operation_type_id'])) {
        $vOperationTypeId = $_GET['operation_type_id'];
    }
    
    $fPkTemp = $vPkTemp ; 
    $fID = $vID ; 
    $fActParentId = $vActParentId ; 
    $fOperationTypeId = $vOperationTypeId ; 
    
    
    $resDataUpdate = $BLL->deletedActTemp(array(
        'id' => $fID,        
        'operation_type_id' => $fActParentId,
        'act_parent_id' => $fOperationTypeId,
        'pktemp' => $fPkTemp));
 
    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($resDataUpdate));
});
 

/** x 
 *  * Okan CIRAN
 * @since 02-02-2016
 */
$app->get("/pktempFillUserCommunicationsTypes_infoUsersCommunications/", function () use ($app ) {


    $BLL = $app->getBLLManager()->get('infoUsersCommunicationsBLL');
 
    $headerParams = $app->request()->headers();
    $vPkTemp = $headerParams['X-Public-Temp'];      
    
    $vLanguageCode = 'tr';
    if (isset($_GET['language_code'])) {
        $vLanguageCode = strtolower(trim($_GET['language_code']));
    }
    $resCombobox = $BLL->fillUserCommunicationsTypesTemp(array('pktemp' => $vPkTemp,'language_code' => $vLanguageCode,));

    $flows = array();
    foreach ($resCombobox as $flow) {
        $flows[] = array(
            "id" => $flow["id"],
            //"text" => strtolower($flow["name"]),
            "text" => $flow["name"],
            "state" => 'open',
            "checked" => false,
            "attributes" => array("notroot" => true,   ),
        );
    }

    $app->response()->header("Content-Type", "application/json");

    /* $app->contentType('application/json');
      $app->halt(302, '{"error":"Something went wrong"}');
      $app->stop(); */

    $app->response()->body(json_encode($flows));
});




$app->run();
