<?php

/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace DAL\PDO;

/**
 * example DAL layer class for test purposes
 * @author Mustafa Zeynel Dağlı
 */
class InfoUsers extends \DAL\DalSlim {

    /**
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();            
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserId = $this->slimApp->getServiceManager()->get('opUserIdBLL');
            $opUserIdArray= $opUserId->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserIdArray)) {
                $opUserIdValue = $opUserIdArray['resultSet'][0]['user_id'];
                $statement = $pdo->prepare("
                    UPDATE info_users 
                    SET deleted = 1, active =1,
                    user_id = " . $opUserIdValue . "                     
                    WHERE id = :id
                    ");
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {
                $errorInfo = '23502';  /// 23502  not_null_violation
                 $pdo->rollback();           
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @param array | null $args
     * @return type
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }       
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                 $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
            }  
            $statement = $pdo->prepare(" 
                    SELECT
                        a.id, 
                        ad.profile_public,                  
                        a.s_date, 
                        a.c_date, 
                        a.operation_type_id,                        
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                        ad.name, 
                        ad.surname, 
                        a.username, 
                        a.password, 
                        ad.auth_email,                   
                        ad.language_code, 
                        ad.language_id, 
                        l.language_eng as user_language,
			COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,                        
                        a.active,                         
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active, 
                        ad.deleted,
                        COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,  			
                        a.op_user_id,
                        u.username AS op_user_name,
                        ad.act_parent_id, 
                        ad.auth_allow_id,                         
                        COALESCE(NULLIF(sd13x.description, ''), sd13.description_eng) AS auth_alow, 
                        ad.cons_allow_id,                        
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,                   
                        ad.root_id,
                        a.consultant_id,
                        cons.name AS cons_name, 
                        cons.surname AS cons_surname,			 
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public                        
                    FROM info_users a    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                     
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                     
                    INNER JOIN info_users_detail ad ON ad.deleted =0 AND ad.active =0 AND ad.root_id = a.id AND ad.language_parent_id = 0 
                    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0 
		    
		    INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND ad.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0 AND sd13.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND ad.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= ad.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0

                    LEFT JOIN sys_specific_definitions sd13x ON sd13x.main_group = 13 AND sd13x.language_id = lx.id AND (sd13x.id = sd13.id OR sd13x.language_parent_id = sd13.id) AND sd13x.deleted =0 AND sd13x.active =0
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
                    INNER JOIN info_users u ON u.id = a.op_user_id                      
                    LEFT JOIN info_users_detail cons ON cons.root_id = a.consultant_id AND cons.cons_allow_id =1 
                
                    ORDER BY ad.name, ad.surname
                ");
             
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**

     * @author Okan CIRAN
     * @ info_users_details tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  09.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_users
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) ,                     
                    active = 1                    
                WHERE id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $afterRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //$pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif
     *  ve deleted 1 = silinmiş yapar. !!
     * @version v 1.0  09.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeUserDeleted($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');        
            $statement = $pdo->prepare(" 
                UPDATE info_users 
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) ,                     
                    active = 1 ,
                    deleted= 1 
                WHERE id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $afterRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);            
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 20.01.2016
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND id != " . intval($params['id']) . " ";
            }
            $sql = " 
            SELECT  
                username AS username , 
                '" . $params['username'] . "' AS value , 
                username ='" . $params['username'] . "' AS control,
                CONCAT(username , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_users                
            WHERE   
                LOWER(username) = LOWER('" . $params['username'] . "') "
                    . $addSql . " 
               AND active =0         
               AND deleted =0   
                               ";
            $statement = $pdo->prepare($sql);
          //echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 20.01.2016
     * @return array
     * @throws \PDOException
     */
    public function haveEmail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND id != " . intval($params['id']) . " ";
            }
            $sql = " 
            SELECT  
                auth_email AS auth_email , 
                '" . $params['auth_email'] . "' AS value , 
                auth_email ='" . $params['auth_email'] . "' AS control,
                CONCAT(auth_email , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_users_detail                
            WHERE   
                LOWER(auth_email) = LOWER('" . $params['auth_email'] . "') "
                    . $addSql . " 
               AND active =0         
               AND deleted =0   
                               ";
            $statement = $pdo->prepare($sql);
            //    echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_users tablosundaki kullanıcı kaydı oluşturur  !!     
     * @version v 1.0  26.01.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();            
            $kontrol = $this->haveRecords($params); // username kontrolu
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) { 
                $opUserIdParams = array('pk' =>  $params['pk'],);
                $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
                $opUserId = $opUserIdArray->getUserId($opUserIdParams);               
                if (!\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];  
                    /// languageid sini alalım                                 
                    $languageIdValue = 647;                    
                    if ((isset($params['preferred_language']) && $params['preferred_language'] != "")) {                                    
                        $languageIdValue = $params['preferred_language'];
                    }     
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);                        
                    $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                    $operationTypesValue = $operationTypes->getInsertOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                        
                    }  
               //uzerinde az iş olan consultantı alalım. 
                    $ConsultantId = 1001;
                    if ($operationIdValue > 0) {
                        $url = null;
                        $getConsultantParams = array('operation_type_id' => $operationIdValue, 'language_id' => $languageIdValue,);
                        $getConsultant = $this->slimApp->getBLLManager()->get('beAssignedConsultantBLL');
                        $getConsultantArray = $getConsultant->getBeAssignedConsultant($getConsultantParams);
                        if (\Utill\Dal\Helper::haveRecord($getConsultantArray)) {
                            $ConsultantId = $getConsultantArray ['resultSet'][0]['consultant_id'];
                        }
                    }

                    $CountryCode = NULL;
                    $CountryCodeValue = 'TR';
                    if ((isset($params['country_id']) && $params['country_id'] != "")) {              
                        $CountryCode = SysCountrys::getCountryCode(array('country_id' => $params['country_id']));
                        if (\Utill\Dal\Helper::haveRecord($CountryCode)) {
                            $CountryCodeValue = $CountryCode ['resultSet'][0]['country_code'];                    
                        }
                    } 

                    $sql = " 
                    INSERT INTO info_users(
                               operation_type_id, 
                               username, 
                               password, 
                               active,
                               language_id,
                               op_user_id,
                               role_id,
                               consultant_id,
                               network_key
                                )
                    VALUES (  :operation_type_id, 
                              :username,
                              :password,
                              :active,
                              ".intval($languageIdValue).",
                              ".intval($opUserIdValue).",
                              :role_id,
                              ". intval($ConsultantId).",
                              CONCAT('U','".$CountryCodeValue."',ostim_userid_generator())
                        )";

                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':operation_type_id', $operationIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                    $statement->bindValue(':password', md5($params['password']), \PDO::PARAM_STR);
                    $statement->bindValue(':role_id', $opUserRoleIdValue, \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);


                    /*
                     * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.  
                     * kullanıcı için gerekli olan private key temp ve value temp değerleri yaratılılacak.  
                     */
                    $this->setPrivateKey(array('id' => $insertID));
                   
                    /*
                     * kullanıcı bilgileri info_users_detail tablosuna kayıt edilecek.   
                     */
                    $this->insertDetail(
                            array(
                                'id' => $insertID,
                                'op_user_id' => $opUserIdValue,
                                'role_id' => $opUserRoleIdValue,
                                'active' => $params['active'],
                                'operation_type_id' => $params['operation_type_id'],
                                'language_id' => $params['preferred_language'],
                                'profile_public' => $params['profile_public'],
                                'f_check' => 0,
                                'name' => $params['name'],
                                'surname' => $params['surname'],
                                'auth_email' => $params['auth_email'],
                                'act_parent_id' => $params['act_parent_id'],
                                'auth_allow_id' => 0,
                                'cons_allow_id' => 0,
                                'root_id' => $insertID,
                                'consultant_id' => $ConsultantId,
                                'password' => $params['password'],
                                'operation_type_id'=> $operationIdValue, 
                    )); 
                    
                    $consultantProcessSendParams = array(
                            'op_user_id' => intval($opUserIdValue),
                            'operation_type_id' => intval($operationIdValue),
                            'table_column_id' => intval($insertID),
                            'cons_id' => intval($ConsultantId),
                            'preferred_language_id' => intval($languageIdValue),
                            'url' => $url, 
                            'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                    );                    
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);

                    $pdo->commit();
                    $logDbData = $this->getUsernamePrivateKey(array('id' => $insertID));
                    $this->insertLogUser(array('oid' => $insertID ,
                                               'username'=> $logDbData['resultSet'][0]['username'],  
                                               'sf_private_key_value'=> $logDbData['resultSet'][0]['sf_private_key_value'],  
                                               'sf_private_key_value_temp'=> $logDbData['resultSet'][0]['sf_private_key_value_temp']  
                            
                                                ));
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'pk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'username';
                 $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_users tablosundaki kullanıcı kaydı oluşturur  !!
     * @version v 1.0  26.01.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertDetail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                  
            $kontrol = $this->haveRecords($params);
            if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                $operationIdValue = -1;
                if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                    $operationIdValue = $params['operation_type_id'];
                }
                $sql = " 
                INSERT INTO info_users_detail(                           
                            profile_public,                             
                            operation_type_id, 
                            name, 
                            surname, 
                            auth_email,                             
                            act_parent_id,                              
                            language_id,                             
                            root_id, 
                            op_user_id,
                            password,
                            consultant_id)
                VALUES (    :profile_public,                               
                            ". intval($operationIdValue).", 
                            :name, 
                            :surname, 
                            :auth_email,                             
                            (SELECT last_value FROM info_users_detail_id_seq),                              
                            :language_id,                             
                            :root_id, 
                            :op_user_id ,
                            :password,
                            ". intval($params['consultant_id'])."
                    )";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                $statement->bindValue(':surname', $params['surname'], \PDO::PARAM_STR);
                $statement->bindValue(':auth_email', $params['auth_email'], \PDO::PARAM_STR);                
                $statement->bindValue(':password', md5($params['password']), \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
                $statement->bindValue(':root_id', $params['root_id'], \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
           //   echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);                 
                                
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {
                $errorInfo = '23502';   // 23502 info_users tablosunda bulunamadı. not_null_violation   
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * Kullanıcı ilk kayıt ta "pk" sız olarak  cagırılacak servis.
     * Kullanıcıyı kaydeder. pk, pktemp, privatekey degerlerinin olusturur.  
     * @version v 1.0 27.01.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction(); 
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {                    
                    $opUserRoleIdValue = 7 ;   
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $sessionId = null;
                    if (isset($params['sessionId']) && $params['sessionId'] != "") {
                        $sessionId = $params['sessionId'];
                    }   
                                
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);                        
                    $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                    $operationTypesValue = $operationTypes->getInsertOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                        
                    }   
                    $languageIdValue = 647;                    
                    if ((isset($params['preferred_language']) && $params['preferred_language'] != "")) {                                    
                        $languageIdValue = $params['preferred_language'];
                    }
                    $ConsultantId = 1001;
                    if ($operationIdValue > 0) {
                        $url = null;
                        $getConsultantParams = array('operation_type_id' => $operationIdValue, 'language_id' => $languageIdValue,);
                        $getConsultant = $this->slimApp->getBLLManager()->get('beAssignedConsultantBLL');
                        $getConsultantArray = $getConsultant->getBeAssignedConsultant($getConsultantParams);
                        if (\Utill\Dal\Helper::haveRecord($getConsultantArray)) {
                            $ConsultantId = $getConsultantArray ['resultSet'][0]['consultant_id'];
                        }
                    }
                    $CountryCode = NULL;
                    $CountryCodeValue = 'TR';
                    if ((isset($params['country_id']) && $params['country_id'] != "")) {              
                        $CountryCode = SysCountrys::getCountryCode(array('country_id' => $params['country_id']));
                        if (\Utill\Dal\Helper::haveRecord($CountryCode)) {
                            $CountryCodeValue = $CountryCode ['resultSet'][0]['country_code'];                    
                        }
                    } 
                    
                    $sql = " 
                INSERT INTO info_users(                           
                        operation_type_id, 
                        username, 
                        password,                         
                        op_user_id,                            
                        language_id, 
                        role_id,
                        consultant_id,
                        network_key
                            )      
                VALUES (".intval($operationIdValue).",
                        :username,
                        :password,                        
                        (SELECT last_value FROM info_users_id_seq),
                        ".intval($languageIdValue).", 
                        ".intval($opUserRoleIdValue).",
                        ".intval($ConsultantId).",
                        CONCAT('U','".$CountryCodeValue."',ostim_userid_generator())
                    )";
                    
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                    $statement->bindValue(':password', md5($params['password']), \PDO::PARAM_STR);
                  //echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                     * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.  
                     * kullanıcı için gerekli olan private key temp ve value temp değerleri yaratılılacak.  
                     */
                    $this->setPrivateKey(array('id' => $insertID));
                    /*
                     * kullanıcının public key temp değeri alınacak..  
                     */
                    $publicKeyTempValue = NULL;                   
                    $publicKeyTemp = $this->getPublicKeyTemp(array('id' => $insertID));             
                    if (\Utill\Dal\Helper::haveRecord($publicKeyTemp)) {
                        $publicKeyTempValue = $publicKeyTemp ['resultSet'][0]['pk_temp'];
                        
                        $pktempForSesionIdParams = array(
                                'pktemp' => $publicKeyTempValue,
                                'sesionId' => $sessionId,
                        );
                        $pktempForSesionId = $this->slimApp-> getBLLManager()->get('SesionIdBLL');  
                        $pktempForSesionIdArray= $pktempForSesionId->updatePktempForSesionId($pktempForSesionIdParams);
                        if ($pktempForSesionIdArray['errorInfo'][0] != "00000" &&
                                $pktempForSesionIdArray['errorInfo'][1] != NULL &&
                                $pktempForSesionIdArray['errorInfo'][2] != NULL)
                            throw new \PDOException($pktempForSesionIdArray['errorInfo']);    
                        
                    } 
                                
                    /* SesionIdBLL
                     * kullanıcı bilgileri info_users_detail tablosuna kayıt edilecek.   
                     */
                    $this->insertDetail(
                            array(
                                'op_user_id' => $insertID,
                                'role_id' => 5,                                
                                'language_id' => $params['preferred_language'],
                                'profile_public' => $params['profile_public'],                                
                                'name' => $params['name'],
                                'surname' => $params['surname'],
                                'username' => $params['username'],
                                'auth_email' => $params['auth_email'],                                                                 
                                'root_id' => $insertID,
                                'password' => $params['password'],
                                'consultant_id'=> $ConsultantId,
                                'operation_type_id'=> $operationIdValue, 
                    ));
                                
                    $consultantProcessSendParams = array(
                                'op_user_id' => intval($insertID),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);    
                       
                    
                    
                     /////////////////////////////////////////////////////////////////////////////
                    
                    $userInfo = $this->getTempUserInformation(array('id' => $insertID));
                    if (\Utill\Dal\Helper::haveRecord($userInfo)) {                        
                        $roleValue = $userInfo ['resultSet'][0]['role'];
                        $keyValue = $userInfo ['resultSet'][0]['key'];

                        $insertSendingMailParams = array(
                                    'user_id' => intval($insertID),
                                    'auth_email' => $params['auth_email'],
                                    'act_email_template_id' => 1,
                                    'op_user_id' => intval($insertID),
                                    'key' => $keyValue,
                        );
                        
                        $SendingMail = $this->slimApp-> getBLLManager()->get('infoUsersSendingMailBLL');  
                        $insertSendingMailArray= $SendingMail->insertSendingMail($insertSendingMailParams); 
                        
                        if ($insertSendingMailArray['errorInfo'][0] != "00000" && $insertSendingMailArray['errorInfo'][1] != NULL && $insertSendingMailArray['errorInfo'][2] != NULL)
                            throw new \PDOException($insertSendingMailArray['errorInfo']);

                        /*
                         * email gönderelim
                         */
                        
                        $sendMailTempUserRegistrationParams = array(
                                    'auth_email' => $params['auth_email'],
                                    'herkimse' => $params['name'] . ' ' . $params['surname'],
                                    'kume' => 'Sanal Fabrika',//<--- burası  degisescek  oki..
                                    'rol' => $roleValue,
                                    'key' => $keyValue,
                        );                        
                        
                        $sendMailTempUserRegistrationArray= $SendingMail->sendMailTempUserRegistration($sendMailTempUserRegistrationParams);
                    }
                                
                    //////////////////////////////////////////////////////////////////////////////
                                
                    $logDbData = $this->getUsernamePrivateKey(array('id' => $insertID));                    
                    $this->insertLogUser(array('oid' => $insertID ,
                                               'username'=> $logDbData['resultSet'][0]['username'],  
                                               'sf_private_key_value'=> $logDbData['resultSet'][0]['sf_private_key_value'],  
                                               'sf_private_key_value_temp'=> $logDbData['resultSet'][0]['sf_private_key_value_temp']  
                            
                                                ));
                    $pdo->commit();
                    
                    
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID, "pktemp" => $publicKeyTempValue);
                } else {
                    $errorInfo = '23505';   // 23505  unique_violation
                    $errorInfoColumn = 'username';
                     $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }             
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'id' li kaydın update ini yapar  !!
     * @version v 1.0  26.01.2016     
     * @param array | null $args
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();            
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {                
                $kontrol = $this->haveRecords($params);
                if ( \Utill\Dal\Helper::haveRecord($kontrol)) {                    
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];                 
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);
                    $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                    $operationTypesValue = $operationTypes->getUpdateOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                     
                        if ($operationIdValue > 0) {
                            $url = null;
                        }
                    }      
                    
                    $languageIdValue = 647;                    
                    if ((isset($params['preferred_language']) && $params['preferred_language'] != "")) {                                    
                        $languageIdValue = $params['preferred_language'];
                    } 
                    /*
                     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
                     * alanlarını update eder !! username update edilmez.  
                     */
                    $this->updateInfoUsers(array('id' => $opUserIdValue,
                        'op_user_id' => $opUserIdValue,
                        'active' => $params['active'],
                        'language_id' => $languageIdValue,
                        'password' => $params['password'],
                        'operation_type_id' => $operationIdValue,
                    ));
                    /*
                     *  parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
                     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
                     */
                    $this->setUserDetailsDisables(array('id' => $opUserIdValue));
                    $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,
                           operation_type_id,
                           name,
                           surname,
                           auth_email,                            
                           language_id,
                           op_user_id,      
                           root_id,
                           act_parent_id,
                           password,
                           auth_allow_id                           
                           ) 
                           SELECT 
                                " . intval($params['profile_public']) . " AS profile_public,
                                " . intval($operationIdValue) . " AS operation_type_id,
                                '" . $params['name'] . "' AS name, 
                                '" . $params['surname'] . "' AS surname,
                                '" . $params['auth_email'] . "' AS auth_email,   
                                " . intval($languageIdValue). " AS language_id,   
                                " . intval($opUserIdValue) . " AS user_id,
                                a.root_id AS root_id,
                                a.act_parent_id,
                                '" . md5($params['password']) . "' AS password ,
                                CASE
                                    (CASE 
                                        (SELECT (z.auth_email = '" . $params['auth_email'] . "') FROM info_users_detail z WHERE z.id = a.id)    
                                         WHEN true THEN 1
                                         ELSE 0  
                                         END ) 
                                     WHEN 1 THEN a.auth_allow_id
                                ELSE 0 END AS auth_allow_id
                            FROM info_users_detail a
                            WHERE a.root_id  =" . intval($params['id']) . " AND
                                a.active =1 AND a.deleted =0 AND 
                                a.c_date = (SELECT MAX(b.c_date)  
						FROM info_users_detail b WHERE b.root_id =a.root_id
						AND b.active =1 AND b.deleted =0)  
                    ";
                    $statementActInsert = $pdo->prepare($sql);
                    //   echo debugPDO($sql, $params);
                    $insertAct = $statementActInsert->execute();
                    $affectedRows = $statementActInsert->rowCount();
                    $insertID = $pdo->lastInsertId('info_users_detail_id_seq');                                      
                    $errorInfo = $statementActInsert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                    * ufak bir trik var. 
                    * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                    * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                    * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                    */
                                
                    $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                  array('operation_type_id' =>$operationIdValue, 'id' => $params['id'],));
                    if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                        $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];                       
                        // $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                       
                    } 
                                
                    $consultantProcessSendParams = array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);   
                    
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows, "newId" => $insertID);
                } else {
                    $errorInfo = '23505';  // 23505  unique_violation 
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
                }
            } else {
                $errorInfo = '23502';  /// 23502 user_id not_null_violation
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'id' li kaydın update ini yapar  !!
     * @version v 1.0  26.01.2016     
     * @param array | null $args
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function updateTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
          //  $opUserId = $this->getUserIdTemp(array('pktemp' => $params['pktemp']));
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                                
                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $active = 0;                 
                    if ((isset($params['active']) && $params['active'] != "")) {
                        $active= " " . intval($params['active']) ;                                             
                    }                    
                    
                    $languageIdValue = 647;                    
                    if ((isset($params['preferred_language']) && $params['preferred_language'] != "")) {                                    
                        $languageIdValue = $params['preferred_language'];
                    }                   
                                
                    $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];                 
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);
                    $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                    $operationTypesValue = $operationTypes->getUpdateOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];
                        if ($operationIdValue > 0) {
                            $url = null;
                        }
                    }      
                    
                    /*
                     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
                     * alanlarını update eder !! username update edilmez.  
                     */
                    $this->updateInfoUsers(array('id' => $opUserIdValue,
                        'op_user_id' => $opUserIdValue,                        
                        'active' => $active,
                        'operation_type_id' => $operationIdValue,
                        'language_id' => $languageIdValue,
                        'password' => $params['password'],  
                        'operation_type_id'=> $operationIdValue, 
                    ));

                    /*
                     *  parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
                     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
                     */
                    $this->setUserDetailsDisables(array('id' =>$opUserIdValue));

                    $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,  
                           operation_type_id,
                           active,
                           name,
                           surname,
                           auth_email,
                           language_id,
                           op_user_id,
                           root_id,
                           act_parent_id,
                           password 
                            ) 
                           SELECT 
                                " . intval($params['profile_public']) . " AS profile_public, 
                                " . intval($operationIdValue) . " AS operation_type_id,
                                " . intval($active) . " AS active,
                                '" . $params['name'] . "' AS name, 
                                '" . $params['surname'] . "' AS surname,
                                '" . $params['auth_email'] . "' AS auth_email,   
                                '" . $params['preferred_language'] . "' AS language_id,   
                                " . intval($opUserIdValue) . " AS user_id,
                                a.root_id,
                                a.act_parent_id,
                                '" . md5($params['password']) . "' AS password
                            FROM info_users_detail a
                            WHERE root_id  =" . intval($opUserIdValue) . "                               
                                AND active =1 AND deleted =0 and 
                                c_date = (SELECT MAX(c_date)  
						FROM info_users_detail WHERE root_id =a.root_id
						AND active =1 AND deleted =0) 
 
                    ";
                    $statementActInsert = $pdo->prepare($sql);
                   //  echo debugPDO($sql, $params);                                
                    $insertAct = $statementActInsert->execute();
                    $affectedRows = $statementActInsert->rowCount();
                    $insertID = $pdo->lastInsertId('info_users_detail_id_seq');                                      
                    $errorInfo = $statementActInsert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                    * ufak bir trik var. 
                    * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                    * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                    * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                    */
                     $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                 array('operation_type_id' =>$operationIdValue, 'id' => $params['id'],));
                    if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                        $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];                    
                        // $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                       
                    } 
                                
                    $consultantProcessSendParams = array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);    
                                
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    $errorInfo = '23505';  // 23505  unique_violation 
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
                }
            } else {
                $errorInfo = '23502';  /// 23502 user_id not_null_violation
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN    
     * parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!     
     * @version v 1.0  29.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setUserDetailsDisables($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            // $pdo->beginTransaction();           
            $sql = "
                UPDATE info_users_detail
                    SET
                        c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) , 
                        active = 1 
                    WHERE root_id = :id AND active = 0 AND deleted = 0 
                    ";
             $statement = $pdo->prepare($sql); 
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
          //  echo debugPDO($sql, $params);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);           
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /** 
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
     * alanlarını update eder !! username update edilmez.      
     * @version v 1.0  29.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function updateInfoUsers($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $operationIdValue = -2;
            if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                $operationIdValue = $params['operation_type_id'];
            } 
            
            $statement = $pdo->prepare("
                UPDATE info_users
                    SET
                        c_date = timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) ,                         
                        operation_type_id = :operation_type_id,
                        password = md5(:password), 
                        language_id = :language_id,                        
                        op_user_id = :op_user_id ,
                        active = :active
                    WHERE id = :id  
                    ");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);            
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);                        
            $statement->bindValue(':operation_type_id', $operationIdValue, \PDO::PARAM_INT);
            $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);            
            $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);         
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @param array | null $args
     * @return Array
     * @throws \PDOException
     */
    public function fillGrid($args = array()) {
        if (isset($args['page']) && $args['page'] != "" && isset($args['rows']) && $args['rows'] != "") {
            $offset = ((intval($args['page']) - 1) * intval($args['rows']));
            $limit = intval($args['rows']);
        } else {
            $limit = 10;
            $offset = 0;
        }

        $sortArr = array();
        $orderArr = array();
        $whereSql = "" ;
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "ad.name, ad.surname";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            $order = "ASC";
        }
        
        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }       
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
             $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
        }  
         
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "    
                    SELECT
                        a.id, 
                        ad.profile_public,                  
                        a.s_date, 
                        a.c_date, 
                        a.operation_type_id,                        
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                        ad.name, 
                        ad.surname, 
                        a.username, 
                        a.password, 
                        ad.auth_email,                   
                        ad.language_code, 
                        ad.language_id, 
                        l.language_eng as user_language,
			COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,                        
                        a.active,                         
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active, 
                        ad.deleted,
                        COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,  			
                        a.op_user_id,
                        u.username AS op_user_name,
                        ad.act_parent_id, 
                        ad.auth_allow_id,                         
                        COALESCE(NULLIF(sd13x.description, ''), sd13.description_eng) AS auth_alow, 
                        ad.cons_allow_id,                        
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,                   
                        ad.root_id,
                        a.consultant_id,
                        cons.name AS cons_name, 
                        cons.surname AS cons_surname,			 
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public                        
                    FROM info_users a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                     
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                     
                    INNER JOIN info_users_detail ad ON ad.deleted =0 AND ad.active =0 AND ad.root_id = a.id AND ad.language_parent_id = 0 
                    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0 
		    
		    INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND ad.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0 AND sd13.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND ad.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= ad.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0

                    LEFT JOIN sys_specific_definitions sd13x ON sd13x.main_group = 13 AND sd13x.language_id = lx.id AND (sd13x.id = sd13.id OR sd13x.language_parent_id = sd13.id) AND sd13x.deleted =0 AND sd13x.active =0
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
                    INNER JOIN info_users u ON u.id = a.op_user_id                        
                    LEFT JOIN info_users_detail cons ON cons.root_id = a.consultant_id AND cons.cons_allow_id =2 
                 
                    WHERE a.deleted =0  
                    ".$whereSql."                   
                    ORDER BY  " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";
             
            $statement = $pdo->prepare($sql);            
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /** 
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');    
             
            $sql = "
                   SELECT 
                        count(a.id) as count                                 
                    FROM info_users a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                                         
                    INNER JOIN info_users_detail ad ON ad.deleted =0 AND ad.active =0 AND ad.root_id = a.id AND ad.language_parent_id = 0 
                    INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND ad.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0 AND sd13.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND ad.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= ad.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
                    INNER JOIN info_users u ON u.id = a.op_user_id                      
                    WHERE a.deleted = 0 
                         ";             
            $statement = $pdo->prepare($sql);            
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @param type $id
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function deletedAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                 
                $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];
                            
                $url = null;
                if (isset($params['url']) && $params['url'] != "") {
                    $url = $params['url'];
                }    
                $m = null;
                if (isset($params['m']) && $params['m'] != "") {
                    $m = $params['m'];
                }  
                $a = null;
                if (isset($params['a']) && $params['a'] != "") {
                    $a = $params['a'];
                }  
                $operationIdValue =  0;
                $assignDefinitionIdValue = 0;
                $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);
                $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                $operationTypesValue = $operationTypes->getDeleteOperationId($operationTypeParams);
                if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                    $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                    $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];
                    if ($operationIdValue > 0) {
                            $url = null;
                        }
                }  
                $this->setUserDetailsDisables(array('id' => $opUserIdValue));
                $this->makeUserDeleted(array('id' => $opUserIdValue));
                $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,
                           s_date,
                           c_date,
                           operation_type_id, 
                           name,
                           surname,                                                                        
                           auth_email,  
                           act_parent_id,
                           auth_allow_id,
                           cons_allow_id,                           
                           language_id,
                           root_id,
                           op_user_id,
                           language_id,
                           password,                           
                           active,
                           deleted,
                            ) 
                           SELECT 
                                profile_public,
                                s_date,
                                timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) , 
                                " . intval($operationIdValue) . " AS operation_type_id,
                                name,
                                surname,
                                auth_email,  
                                act_parent_id,
                                auth_allow_id,
                                cons_allow_id,                                
                                language_id,
                                root_id,
                                " . intval($opUserIdValue) . " AS op_user_id,
                                language_id,
                                password,   
                               1,
                               1
                            FROM info_users_detail 
                            WHERE root_id  =" . intval($opUserIdValue) . " 
                                AND active =0 AND deleted =0  
                    "; 
                $statement_act_insert = $pdo->prepare($sql);   
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                $errorInfo = $statement_act_insert->errorInfo();
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                  array('operation_type_id' =>$operationIdValue, 'id' => $params['id'],));
                if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                    $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];
                    $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                    
                }
                                
                $consultantProcessSendParams = array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);   
                    
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {
                $errorInfo = '23502';  /// 23502  not_null_violation
                 $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki private key ve value değerlerini oluşturur  !!     
     * @version v 1.0  26.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setPrivateKey($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                    
            $statement = $pdo->prepare("
                UPDATE info_users
                SET              
                    sf_private_key = armor( pgp_sym_encrypt (username , oid, 'compress-algo=1, cipher-algo=bf')) ,
                    sf_private_key_temp = armor( pgp_sym_encrypt (username , oid_temp, 'compress-algo=1, cipher-algo=bf'))                   
                WHERE                   
                    id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $statementValue = $pdo->prepare("
                UPDATE info_users
                SET              
                    sf_private_key_value = substring(sf_private_key,40,length( trim( sf_private_key))-140)   ,
                    sf_private_key_value_temp = substring(sf_private_key_temp,40,length( trim( sf_private_key_temp))-140)  
                WHERE                     
                    id = :id");
            $statementValue->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $updateValue = $statementValue->execute();
            $affectedRows = $statementValue->rowCount();
            $errorInfo = $statementValue->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);         
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki  public key temp değerini döndürür !!
     * @version v 1.0  26.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getPublicKeyTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = " 
                SELECT 
                    REPLACE(TRIM(SUBSTRING(crypt(sf_private_key_value_temp,gen_salt('xdes')),6,20)),'/','*') as pk_temp ,              
                    id =" .intval( $params['id']) . " AS control
                FROM info_users 
                WHERE 
                     id =" .intval( $params['id']) . "
                ";
            $statement = $pdo->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'pk' nın, info_users tablosundaki user_id si değerini döndürür !!
     * param olarak  gelen pk in act_session tablosunda olması zorunludur.
     * @version v 1.0  26.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getUserId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = 
                 "  
                SELECT id AS user_id, 1=1 AS control ,role_id,language_id
                FROM (
                        SELECT a.id, a.role_id, a.language_id,
                        CRYPT(a.sf_private_key_value,CONCAT('_J9..',REPLACE('" . $params['pk'] . "','*','/'))) = CONCAT('_J9..',REPLACE(acts.public_key,'*','/')) AS pkey
                        FROM info_users a
                        INNER JOIN act_session acts ON acts.public_key is not null AND  
                            CRYPT(a.sf_private_key_value,CONCAT('_J9..',REPLACE('" . $params['pk'] . "','*','/'))) = CONCAT('_J9..',REPLACE(acts.public_key,'*','/'))
                        WHERE 
                            a.active =0 AND 
                            a.deleted =0 LIMIT 1) AS logintable 
                WHERE pkey = TRUE 
                "   ;
                 /*   
                    "  
                SELECT id AS user_id, 1=1 AS control,role_id FROM (
                            SELECT id, role_id	
                                CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('" . $params['pk'] . "','*','/'))) = CONCAT('_J9..',REPLACE('" . $params['pk'] . "','*','/')) as pkey                                
                            FROM info_users WHERE active =0 AND deleted =0) AS logintable
                        WHERE pkey = TRUE 
                ";
                  * 
                  */
            $statement = $pdo->prepare($sql);
           //  echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'pktemp' için info_users tablosundaki user_id si değerini döndürür !!
     * param olarak  gelen pktemp in act_session tablosunda olması zorunludur.     
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getUserIdTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "    
                SELECT id AS user_id, 1=1 AS control ,role_id ,language_id
                FROM (
                        SELECT a.id, a.role_id, a.language_id,
                        CRYPT(a.sf_private_key_value_Temp,CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/'))) = CONCAT('_J9..',REPLACE(acts.public_key,'*','/')) AS pkeytemp
                        FROM info_users a
                        INNER JOIN act_session acts ON acts.public_key is not null AND  
                            CRYPT(a.sf_private_key_value_Temp,CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/'))) = CONCAT('_J9..',REPLACE(acts.public_key,'*','/'))
                        WHERE 
                            a.active =0 AND 
                            a.deleted =0) AS logintable 
                WHERE pkeytemp = TRUE 
                ";            
            /*
             *  
              SELECT id AS user_id , 1=1 AS control FROM (
                            SELECT id, 	                              
                                CRYPT(sf_private_key_value_Temp,CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/'))) = CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/')) AS pkeytemp                                    
                            FROM info_users WHERE active =0 AND deleted =0) AS logintable
                        WHERE pkeytemp = TRUE 
             */
            $statement = $pdo->prepare($sql);
         //  echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'pktemp' için info_users tablosundaki user_id si değerini döndürür !!
     * param olarak  gelen pktemp in act_session tablosunda olması zorunludur.
     * @author Okan CIRAN
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getUserIdTempForPassword($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "    
                SELECT id AS user_id, 1=1 AS control ,role_id ,language_id
                FROM (
                        SELECT a.id, a.role_id, a.language_id, 
                                    CRYPT(sf_private_key_value_temp,CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/'))) = CONCAT('_J9..',REPLACE('" . $params['pktemp'] . "','*','/')) AS pkeytemp                                    
                        FROM info_users a                        
                        WHERE 
                            a.active =0 AND 
                            a.deleted =0) AS logintable 
                WHERE pkeytemp = TRUE 
                "; 
            $statement = $pdo->prepare($sql);
         // echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN     
     * parametre olarak gelen array deki 'id' li kaydın, act_users_rrpmap tablosunda "New User" rolu verilerek kaydı oluşturulur. !!
     * insertTemp ve insert fonksiyonlarında kullanılacak.  
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setNewUserRrpMap($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            // $pdo->beginTransaction();           
            $statement = $pdo->prepare("
                INSERT INTO act_users_rrpmap(
                    info_users_id, rrpmap_id, user_id)
                VALUES (
                    :id, 
                    8,
                    :user_id )
                    ");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            if ($params['user_id'] == 0)
                $statement->bindValue(':user_id', $params['id'], \PDO::PARAM_INT);
            else
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);     
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {  
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN       
     * parametre olarak gelen array deki 'id' li kaydın, act_users_rrpmap tablosunda "New User" rolu verilerek kaydı oluşturulur. !!
     * insertTemp ve insert fonksiyonlarında kullanılacak.  
     * @version v 1.0  27.01.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function updateNewUserRrpMap($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                  
            $statement = $pdo->prepare("
                INSERT INTO act_users_rrpmap(
                    info_users_id, rrpmap_id, user_id)
                VALUES (
                    :id, 
                    8,
                    :user_id )
                    ");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            if ($params['user_id'] == 0)
                $statement->bindValue(':user_id', $params['id'], \PDO::PARAM_INT);
            else
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {   
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
     
    /**
     * @author Okan CIRAN
     * log databasine yeni kullanıcı için kayıt ekler                
     * @version v 1.0  09.03.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function insertLogUser($params = array()) {
        try {         
            $pdoLog = $this->slimApp->getServiceManager()->get('pgConnectLogFactory');
            $pdoLog->beginTransaction();
                $sql = " 
                    INSERT INTO info_users(
                        username, 
                        sf_private_key_value, 
                        sf_private_key_value_temp,
                        oid
                        ) 
                    VALUES (
                        :username, 
                        :sf_private_key_value, 
                        :sf_private_key_value_temp,
                        :oid
                        )     
                    "; 
                $statement_log_insert = $pdoLog->prepare($sql);  
                $statement_log_insert->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                $statement_log_insert->bindValue(':sf_private_key_value', $params['sf_private_key_value'], \PDO::PARAM_STR);
                $statement_log_insert->bindValue(':sf_private_key_value_temp', $params['sf_private_key_value_temp'], \PDO::PARAM_STR);
                $statement_log_insert->bindValue(':oid', $params['oid'], \PDO::PARAM_INT);
               //  echo debugPDO($sql, $params);
                $insert_log = $statement_log_insert->execute();
                $affectedRows = $statement_log_insert->rowCount();
                $errorInfo = $statement_log_insert->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdoLog->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
           
        } catch (\PDOException $e /* Exception $e */) {
            $pdoLog->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki username ve private key değerlerini döndürür !!
     * @version v 1.0  09.03.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getUsernamePrivateKey($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = " 
                SELECT 
                    id,
                    username, 
                    sf_private_key_value, 
                    sf_private_key_value_temp
                FROM info_users 
                WHERE 
                     id =" .intval( $params['id']) . "
                ";
            $statement = $pdo->prepare($sql);
           // echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**     
     * @author Okan CIRAN
     * @ userin firm id sini döndürür döndürür !!
     * su an için sadece 1 firması varmış senaryosu için gecerli. 
     * @version v 1.0  29.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUserFirmId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            if (isset($params['user_id'])) {
                $userIdValue = $params['user_id'];
                $sql = " 
                SELECT                    
                   ifu.firm_id,
                   1=1 control
                FROM info_firm_machine_tool a		
		INNER JOIN info_firm_users ifu ON ifu.user_id = " . intval($userIdValue) . " AND ifu.language_parent_id = 0 AND ifu.firm_id = a.firm_id                
                WHERE a.deleted =0 AND 
                      a.active =0 AND
                      a.language_parent_id =0 
                limit 1
                                 ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ user in adres , cominication , ad soyad , networkkey bilgilerinin döndürür !!
     * varsa network_key, name, surname, email , communication_number parametrelerinin like ile arar
     * @version v 1.0  17.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersListNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                                 
                $languageCode = 'tr';
                $languageIdValue = 647;
                if (isset($params['language_code']) && $params['language_code'] != "") {
                    $languageCode = $params['language_code'];
                }       
                $languageCodeParams = array('language_code' => $languageCode,);
                $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                     $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                }  
                $sorguStr = null;    
                if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                    $filterRules = trim($params['filterRules']);
                    $jsonFilter = json_decode($filterRules, true);

                    $sorguExpression = null;
                    foreach ($jsonFilter as $std) {
                        if ($std['value'] != null) {
                            switch (trim($std['field'])) {
                                case 'network_key':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                    $sorguStr.=" AND network_key" . $sorguExpression . ' ';

                                    break;
                                case 'name':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND name" . $sorguExpression . ' ';

                                    break;
                                case 'surname':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND surname" . $sorguExpression . ' ';

                                    break;
                                case 'email':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND email" . $sorguExpression . ' ';

                                    break;
                                case 'communication_number':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND communication_number1" . $sorguExpression . ' ';
                                    $sorguStr.=" AND communication_number2" . $sorguExpression . ' ';
                                    break;                                
                                default:
                                    break;
                            }
                        }
                    }
                } else {
                    $sorguStr = null;
                    $filterRules = "";
                    if (isset($params['network_key']) && $params['network_key'] != "") {
                        $sorguStr .= " AND network_key Like '%" . $params['network_key'] . "%'";
                    }
                    if (isset($params['name']) && $params['name'] != "") {
                        $sorguStr .= " AND name Like '%" . $params['name'] . "%'";
                    }

                    if (isset($params['surname']) && $params['surname'] != "") {
                        $sorguStr .= " AND surname Like '%" . $params['surname'] . "%'";
                    }
                    if (isset($params['email']) && $params['email'] != "") {
                        $sorguStr .= " AND email Like '%" . $params['email'] . "%'";
                    }
                    if (isset($params['communication_number']) && $params['communication_number'] != "") {
                        $sorguStr .= " AND (communication_number1'%" . $params['communication_number'] . "%' 
                                     OR communication_number2'%" . $params['communication_number'] . "%')";
                    }
            }
                $sorguStr = rtrim($sorguStr, "AND ");  
                
                $sql = "                     
                SELECT                    
                    name,
                    surname,
                    email,
                    language_id,
                    language_name,
                    iletisimadresi, 
                    faturaadresi,
                    communication_number1,
                    communication_number2,
                    network_key
                FROM (
                    SELECT
                        a.id,
                        iud.name AS name,
                        iud.surname AS surname,
                        iud.auth_email AS email,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        (   SELECT Concat(ax.address1,ax.address2, 
				    ' Posta Kodu = ',ax.postal_code,' ',
				    cox.name ,' ',
				    ctx.name ,' ',
				    box.name ,' ',
				    ax.city_name)
				FROM info_users_addresses  ax                                                  									
				LEFT JOIN sys_countrys cox ON cox.id = ax.country_id AND cox.deleted = 0 AND cox.active = 0 AND cox.language_code = ax.language_code                               
				LEFT JOIN sys_city ctx ON ctx.id = ax.city_id AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_code = ax.language_code                               
				LEFT JOIN sys_borough box ON box.id = ax.borough_id AND box.deleted = 0 AND box.active = 0 AND box.language_code = ax.language_code                 
				WHERE ax.deleted =0 AND ax.active =0 AND ax.address_type_id = 1 
				AND ax.user_id = a.id AND ax.language_parent_id =0 limit 1 
                        )                  
                        As iletisimadresi,
			(   SELECT Concat(ax.address1,ax.address2, 
				    ' Posta Kodu = ',ax.postal_code,' ',
				    cox.name ,' ',
				    ctx.name ,' ',
				    box.name ,' ',
				    ax.city_name)
				FROM info_users_addresses ax
				LEFT JOIN sys_countrys cox ON cox.id = ax.country_id AND cox.deleted = 0 AND cox.active = 0 AND cox.language_code = ax.language_code 
				LEFT JOIN sys_city ctx ON ctx.id = ax.city_id AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_code = ax.language_code
				LEFT JOIN sys_borough box ON box.id = ax.borough_id AND box.deleted = 0 AND box.active = 0 AND box.language_code = ax.language_code
				WHERE ax.deleted =0 AND ax.active =0 AND ax.address_type_id = 2 
				AND ax.user_id = a.id AND ax.language_parent_id =0 limit 1 
                        ) AS faturaadresi,  
			(SELECT  
				ay.communications_no
				FROM info_users_communications ay
				WHERE 
				    ay.active =0 AND ay.deleted = 0 AND ay.default_communication_id = 1 AND 
				    ay.user_id = a.id AND ay.language_parent_id =0 limit 1 
			 ) As communication_number1,
			 (SELECT  
				ay.communications_no
				FROM info_users_communications ay       				
				WHERE 
				    ay.active =0 AND ay.deleted = 0 AND ay.communications_type_id = 2 AND
				    ay.user_id = a.id  AND ay.language_parent_id =0 limit 1 
			 ) As communication_number2,
                        a.network_key
                    FROM info_users a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_users_detail iud ON iud.root_id = a.id AND iud.active =0 AND iud.deleted =0  
                    WHERE
                        a.deleted = 0 AND 
                        a.active =0 
                     ) as tempp                     
                     WHERE 1=1 ".$sorguStr. "
               ORDER BY name, surname

                ";
                $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);            
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ user in adres , cominication , ad soyad , networkkey bilgilerinin sayısını döndürür !!
     * varsa network_key, name, surname, email , communication_number parametrelerinin like ile arar
     * @version v 1.0  17.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersListNpkRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
                                
                $languageCode = 'tr';
                $languageIdValue = 647;
                if (isset($params['language_code']) && $params['language_code'] != "") {
                    $languageCode = $params['language_code'];
                }       
                $languageCodeParams = array('language_code' => $languageCode,);
                $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                     $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                }  
                $sorguStr = null;                            
               if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                    $filterRules = trim($params['filterRules']);
                    $jsonFilter = json_decode($filterRules, true);

                    $sorguExpression = null;
                    foreach ($jsonFilter as $std) {
                        if ($std['value'] != null) {
                            switch (trim($std['field'])) {
                                case 'network_key':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                    $sorguStr.=" AND network_key" . $sorguExpression . ' ';

                                    break;
                                case 'name':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND name" . $sorguExpression . ' ';

                                    break;
                                case 'surname':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND surname" . $sorguExpression . ' ';

                                    break;
                                case 'email':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND email" . $sorguExpression . ' ';

                                    break;
                                case 'communication_number':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=" AND communication_number1" . $sorguExpression . ' ';
                                    $sorguStr.=" AND communication_number2" . $sorguExpression . ' ';
                                    break;                                
                                default:
                                    break;
                            }
                        }
                    }
                } else {
                    $sorguStr = null;
                    $filterRules = "";
                    if (isset($params['network_key']) && $params['network_key'] != "") {
                        $sorguStr .= " AND network_key Like '%" . $params['network_key'] . "%'";
                    }
                    if (isset($params['name']) && $params['name'] != "") {
                        $sorguStr .= " AND name Like '%" . $params['name'] . "%'";
                    }

                    if (isset($params['surname']) && $params['surname'] != "") {
                        $sorguStr .= " AND surname Like '%" . $params['surname'] . "%'";
                    }
                    if (isset($params['email']) && $params['email'] != "") {
                        $sorguStr .= " AND email Like '%" . $params['email'] . "%'";
                    }
                    if (isset($params['communication_number']) && $params['communication_number'] != "") {
                        $sorguStr .= " AND (communication_number1'%" . $params['communication_number'] . "%' 
                                     OR communication_number2'%" . $params['communication_number'] . "%')";
                    }
            }
                $sorguStr = rtrim($sorguStr, "AND ");  
                           
                
                $sql = " 
            SELECT COUNT(id) AS count FROM (    
                SELECT 
                    id,
                    name,
                    surname,
                    email,
                    language_id,
                    language_name,
                    iletisimadresi, 
                    faturaadresi,
                    communication_number1,
                    communication_number2,
                    network_key
                FROM (
                    SELECT
                        a.id,
                        iud.name AS name,
                        iud.surname AS surname,
                        iud.auth_email AS email,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        (   SELECT Concat(ax.address1,ax.address2, 
				    ' Posta Kodu = ',ax.postal_code,' ',
				    cox.name ,' ',
				    ctx.name ,' ',
				    box.name ,' ',
				    ax.city_name)
				FROM info_users_addresses  ax                                                  									
				LEFT JOIN sys_countrys cox ON cox.id = ax.country_id AND cox.deleted = 0 AND cox.active = 0 AND cox.language_code = ax.language_code                               
				LEFT JOIN sys_city ctx ON ctx.id = ax.city_id AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_code = ax.language_code                               
				LEFT JOIN sys_borough box ON box.id = ax.borough_id AND box.deleted = 0 AND box.active = 0 AND box.language_code = ax.language_code                 
				WHERE ax.deleted =0 AND ax.active =0 AND ax.address_type_id = 1 
				AND ax.user_id = a.id AND ax.language_parent_id =0 limit 1 
                        )                  
                        As iletisimadresi,
			(   SELECT Concat(ax.address1,ax.address2, 
				    ' Posta Kodu = ',ax.postal_code,' ',
				    cox.name ,' ',
				    ctx.name ,' ',
				    box.name ,' ',
				    ax.city_name)
				FROM info_users_addresses ax
				LEFT JOIN sys_countrys cox ON cox.id = ax.country_id AND cox.deleted = 0 AND cox.active = 0 AND cox.language_code = ax.language_code 
				LEFT JOIN sys_city ctx ON ctx.id = ax.city_id AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_code = ax.language_code
				LEFT JOIN sys_borough box ON box.id = ax.borough_id AND box.deleted = 0 AND box.active = 0 AND box.language_code = ax.language_code
				WHERE ax.deleted =0 AND ax.active =0 AND ax.address_type_id = 2 
				AND ax.user_id = a.id AND ax.language_parent_id =0 limit 1 
                        ) AS faturaadresi,  
			(SELECT  
				ay.communications_no
				FROM info_users_communications ay
				WHERE 
				    ay.active =0 AND ay.deleted = 0 AND ay.default_communication_id = 1 AND 
				    ay.user_id = a.id AND ay.language_parent_id =0 limit 1 
			 ) As communication_number1,
			 (SELECT  
				ay.communications_no
				FROM info_users_communications ay       				
				WHERE 
				    ay.active =0 AND ay.deleted = 0 AND ay.communications_type_id = 2 AND
				    ay.user_id = a.id  AND ay.language_parent_id =0 limit 1 
			 ) As communication_number2,
                        a.network_key
                    FROM info_users a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_users_detail iud ON iud.root_id = a.id AND iud.active =0 AND iud.deleted =0  
                    WHERE
                        a.deleted = 0 AND 
                        a.active =0 
                     ) as tempp                     
                     WHERE 1=1 ".$sorguStr. "
                    ) AS tempcount 
             

                ";
                $statement = $pdo->prepare($sql);
                //echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);            
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
        
    /** 
     * @param array | null $args
     * @return Array
     * @throws \PDOException
     */
    public function fillUsersInformationNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                
                $languageCode = 'tr';
                $languageIdValue = 647;
                if (isset($params['language_code']) && $params['language_code'] != "") {
                    $languageCode = $params['language_code'];
                }       
                $languageCodeParams = array('language_code' => $languageCode,);
                $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                     $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                }  
                $sql = "                        
                    SELECT
                        a.network_key as unpk,
                        a.s_date AS registration_date, 
                        ad.name, 
                        ad.surname,
                        ad.auth_email,  
                        ad.language_id, 
                        l.language_eng as user_language,
			COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,                        
                        ifk.network_key as npk,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
                        COALESCE(NULLIF(ifux.title, ''), ifu.title_eng) AS title,
                        ifu.title_eng,
                        ad.root_id  = u.id AS userb
                    FROM info_users a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_users_detail ad ON ad.deleted =0 AND ad.active =0 AND ad.root_id = a.id AND ad.language_parent_id = 0
                    INNER JOIN info_users u ON u.id = " . intval($opUserIdValue) . "
                    LEFT JOIN info_firm_users ifu ON ifu.user_id = a.id AND ifu.cons_allow_id =2 
                    LEFT JOIN info_firm_users ifux ON (ifux.language_parent_id = ifu.id OR ifux.id=ifu.id) AND ifux.cons_allow_id =2 AND ifux.language_id = lx.id                
                    LEFT JOIN info_firm_profile fp ON (fp.act_parent_id = ifu.firm_id) AND fp.cons_allow_id =2 AND fp.language_id = l.id  
                    LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.id OR fpx.id=fp.id) AND fpx.cons_allow_id =2 AND fpx.language_id = lx.id                
                    LEFT JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id                 
                    WHERE a.deleted =0  
                    and a.network_key = '" . $params['network_key'] . "'                    
                   ";
                $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**  
     * @author Okan CIRAN
     * @ network key den firm id sini döndürür  !!     
     * @version v 1.0  09.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUserIdsForNetworkKey($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                                
            if (isset($params['network_key'])) {                                
                $npk = $params['network_key'];  
                $sql = " 
                    SELECT user_id, 1=1 AS control FROM (
                            SELECT a.id AS user_id
                            FROM info_users a                            			    
                            WHERE
                             a.network_key = '".$npk."'
                ) AS xtable limit 1                             
                                 ";
                $statement = $pdo->prepare($sql);
               //echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  network_key not_null_violation
                $errorInfoColumn = 'network_key';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * info_users tablosundaki danışman kaydı oluşturur  !!
     * @version v 1.0  09.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertConsultant($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $kontrol = $this->haveRecords($params); // username kontrolu
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $opUserIdParams = array('pk' =>  $params['pk'],);
                $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
                $opUserId = $opUserIdArray->getUserId($opUserIdParams);                 
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];  
                    $roleId = 2;
                    if ((isset($params['role_id']) && $params['role_id'] != "")) {
                        $roleId = $params['role_id'];
                    }

                    $languageIdValue = 647;
                    if ((isset($params['preferred_language']) && $params['preferred_language'] != "")) {
                        $languageIdValue = $params['preferred_language'];
                    }           
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);                        
                    $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                    $operationTypesValue = $operationTypes->getInsertOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                     
                    }  
                    //uzerinde az iş olan consultantı alalım. 
                    $ConsultantId = 1001;
                    if ($operationIdValue > 0) {
                        $url = null;
                        $getConsultantParams = array('operation_type_id' => $operationIdValue, 'language_id' => $languageIdValue,);
                        $getConsultant = $this->slimApp->getBLLManager()->get('beAssignedConsultantBLL');
                        $getConsultantArray = $getConsultant->getBeAssignedConsultant($getConsultantParams);
                        if (\Utill\Dal\Helper::haveRecord($getConsultantArray)) {
                            $ConsultantId = $getConsultantArray ['resultSet'][0]['consultant_id'];
                        }
                    }

                    $CountryCode = NULL;
                    $CountryCodeValue = 'TR';
                    if ((isset($params['country_id']) && $params['country_id'] != "")) {
                        $CountryCode = SysCountrys::getCountryCode(array('country_id' => $params['country_id']));
                        if (\Utill\Dal\Helper::haveRecord($CountryCode)) {
                            $CountryCodeValue = $CountryCode ['resultSet'][0]['country_code'];
                        }
                    }

                    $password = 'qwerty';

                    $sql = " 
                    INSERT INTO info_users(
                               operation_type_id, 
                               username,
                               language_id,
                               op_user_id,
                               role_id,
                               password,
                               consultant_id,
                               network_key
                                )
                    VALUES (    " . intval($operationIdValue) . ", 
                                '" . $params['username'] . "',                               
                                " . intval($languageIdValue) . ",
                                " . intval($opUserIdValue) . ",
                                " . intval($roleId) . ",
                                '" . $password . "',       
                                " . intval($ConsultantId) . ",
                                CONCAT('U','" . $CountryCodeValue . "',ostim_userid_generator())
                        )";                                

                    $statement = $pdo->prepare($sql);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                     * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.                       
                     */
                    $xc = $this->setPrivateKey(array('id' => $insertID));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                                
                    /*
                     * kullanıcı bilgileri info_users_detail tablosuna kayıt edilecek.   
                     */
                    $xc = $this->insertConsultantDetail(
                            array(
                                'id' => $insertID,
                                'op_user_id' => $opUserIdValue,
                                'role_id' => $roleId,
                                'language_id' => $params['preferred_language'],
                                'name' => $params['name'],
                                'surname' => $params['surname'],
                                'auth_email' => $params['username'],
                                'root_id' => $insertID,
                                'consultant_id' => $ConsultantId,
                                'password' => $password,
                                'operation_type_id'=> $operationIdValue, 
                    ));
                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                                
                    $consultantProcessSendParams = array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);   
                                
                    $logDbData = $this->getUsernamePrivateKey(array('id' => $insertID));

                    if ($logDbData['errorInfo'][0] != "00000" && $logDbData['errorInfo'][1] != NULL && $logDbData['errorInfo'][2] != NULL)
                        throw new \PDOException($logDbData['errorInfo']);

                    $this->insertLogUser(array('oid' => $insertID,
                        'username' => $logDbData['resultSet'][0]['username'],
                        'sf_private_key_value' => $logDbData['resultSet'][0]['sf_private_key_value'],
                        'sf_private_key_value_temp' => $logDbData['resultSet'][0]['sf_private_key_value_temp']
                    ));
                                
                    /*
                     * danısman tablosuna kaydedelim.
                     */
                    $xc = $this->insertConsultantSysOsbConsultants(array(
                        'op_user_id' => intval($opUserIdValue),
                        'user_id' => intval($insertID),
                        'osb_id' => intval($params['osb_id']),
                        'language_id' => intval($languageIdValue),
                        'preferred_language_json' => $params['preferred_language_json'],
                        'title' => $params['title'],
                        'title_eng' => $params['title_eng'],
                    ));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);

                    $insertID = $xc['lastInsertId'] ;                                
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'pk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'username';
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_users_detail tablosunda danısman kaydı oluşturur  !!     
     * @version v 1.0  09.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertConsultantDetail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
                $operationIdValue = -1;
                if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                    $operationIdValue = $params['operation_type_id'];
                }
                $sql = " 
                INSERT INTO info_users_detail(
                            operation_type_id, 
                            name, 
                            surname, 
                            auth_email,
                            act_parent_id,
                            language_id,
                            root_id,
                            op_user_id,
                            password,
                            consultant_id)
                VALUES (    
                            ". intval($operationIdValue).", 
                            :name, 
                            :surname, 
                            :auth_email,
                            (SELECT last_value FROM info_users_detail_id_seq),
                            :language_id,
                            :root_id,
                            :op_user_id,
                            :password,
                            ". intval($params['consultant_id'])."
                    )";
                $statement = $pdo->prepare($sql);               
                $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                $statement->bindValue(':surname', $params['surname'], \PDO::PARAM_STR);
                $statement->bindValue(':auth_email', $params['auth_email'], \PDO::PARAM_STR);                
                $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
                $statement->bindValue(':root_id', $params['root_id'], \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
           //   echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]); 

                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);                                
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * sys_osb_consultants tablosundaki danısman kaydı oluşturur  !!     
     * @version v 1.0  09.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertConsultantSysOsbConsultants($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
               /*  
                * {"0":20,"1":23}
                */
                $sql = " 
                INSERT INTO sys_osb_consultants(
                            osb_id, 
                            country_id,                        
                            op_user_id, 
                            language_id,                        
                            user_id,
                            title,
                            title_eng,
                            preferred_language_json
                            )
                VALUES (    
                            ". intval($params['osb_id']).", 
                            91,                              
                            ". intval($params['op_user_id']).", 
                            ". intval($params['language_id']).", 
                            ". intval($params['user_id']).",
                            '". $params['title']."',
                            '". $params['title_eng']."',
                            (SELECT array_to_json(COALESCE(NULLIF(cxx,'{}'),NULL)) FROM (
                                    SELECT  
                                        ARRAY(   
                                             SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('". $params['preferred_language_json']."')) AS cxx
                                            ) AS zxtable )
                    )";         
                $statement = $pdo->prepare($sql);                               
                // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_osb_consultants_id_seq');                                
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);  

                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_users tablosundaki urge ci personel kaydı oluşturur  !!     
     * @version v 1.0  31.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertUrgePerson($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $kontrol = $this->haveRecords($params); // username kontrolu
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $opUserIdParams = array('pk' =>  $params['pk'],);
                $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
                $opUserId = $opUserIdArray->getUserId($opUserIdParams);                 
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];  

                    $roleId = 64;
                    if ((isset($params['role_id']) && $params['role_id'] != "")) {
                        $roleId = $params['role_id'];
                    }

                    $languageIdValue = 647;
                    if ((isset($params['preferred_language']) && $params['preferred_language'] != "")) {
                        $languageIdValue = $params['preferred_language'];
                    }           
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);                        
                    $operationTypes = $this->slimApp-> getBLLManager()->get('operationsTypesBLL');  
                    $operationTypesValue = $operationTypes->getInsertOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                        
                    }  
                                
                    $ConsultantId = 1001;
                    if ($operationIdValue > 0) {
                        $url = null;
                        $getConsultantParams = array('operation_type_id' => $operationIdValue, 'language_id' => $languageIdValue,);
                        $getConsultant = $this->slimApp->getBLLManager()->get('beAssignedConsultantBLL');
                        $getConsultantArray = $getConsultant->getBeAssignedConsultant($getConsultantParams);
                        if (\Utill\Dal\Helper::haveRecord($getConsultantArray)) {
                            $ConsultantId = $getConsultantArray ['resultSet'][0]['consultant_id'];
                        }
                    }

                    $CountryCode = NULL;
                    $CountryCodeValue = 'TR';
                    if ((isset($params['country_id']) && $params['country_id'] != "")) {
                        $CountryCode = SysCountrys::getCountryCode(array('country_id' => $params['country_id']));
                        if (\Utill\Dal\Helper::haveRecord($CountryCode)) {
                            $CountryCodeValue = $CountryCode ['resultSet'][0]['country_code'];
                        }
                    }

                    $password = 'qwerty';

                    $sql = " 
                    INSERT INTO info_users(
                               operation_type_id, 
                               username,
                               language_id,
                               op_user_id,
                               role_id,
                               password,
                               consultant_id,
                               network_key
                                )
                    VALUES (    " . intval($operationIdValue) . ", 
                                '" . $params['username'] . "',                               
                                " . intval($languageIdValue) . ",
                                " . intval($opUserIdValue) . ",
                                " . intval($roleId) . ",
                                '" . $password . "',       
                                " . intval($ConsultantId) . ",
                                CONCAT('U','" . $CountryCodeValue . "',ostim_userid_generator())
                        )";
                                

                    $statement = $pdo->prepare($sql);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                     * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.                       
                     */
                    $xc = $this->setPrivateKey(array('id' => $insertID));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                                
                    /*
                     * kullanıcı bilgileri info_users_detail tablosuna kayıt edilecek.   
                     */
                    $xc = $this->insertUrgePersonDetail(
                            array(
                                'id' => $insertID,
                                'op_user_id' => $opUserIdValue,
                                'role_id' => $roleId,
                                'language_id' => $languageIdValue,
                                'name' => $params['name'],
                                'surname' => $params['surname'],
                                'auth_email' => $params['auth_email'],
                                'root_id' => $insertID,
                                'consultant_id' => $ConsultantId,
                                'password' => $password,
                                'operation_type_id'=> $operationIdValue,
                    ));
                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);                                
                                
                    $consultantProcessSendParams = array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);   
                                                   
                    $logDbData = $this->getUsernamePrivateKey(array('id' => $insertID));
                    if ($logDbData['errorInfo'][0] != "00000" && $logDbData['errorInfo'][1] != NULL && $logDbData['errorInfo'][2] != NULL)
                        throw new \PDOException($logDbData['errorInfo']);

                    $this->insertLogUser(array('oid' => $insertID,
                        'username' => $logDbData['resultSet'][0]['username'],
                        'sf_private_key_value' => $logDbData['resultSet'][0]['sf_private_key_value'],
                        'sf_private_key_value_temp' => $logDbData['resultSet'][0]['sf_private_key_value_temp']
                    ));

                    /*
                     * danısman tablosuna kaydedelim.
                     */
                    $xc = $this->insertUrgePersonSysOsbPerson(array(
                        'op_user_id' => intval($opUserIdValue),
                        'user_id' => intval($insertID),
                        'osb_cluster_id' => intval($params['cluster_id']),
                    ));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                                
                    
                    $userInfo = $this->getUrgePersonRoleAndClusterInformation(array('id' => $insertID));                                 
                    if (\Utill\Dal\Helper::haveRecord($userInfo)) {
                        $kumeValue = $userInfo ['resultSet'][0]['clusters'];
                        $roleValue = $userInfo ['resultSet'][0]['role'];
                        $keyValue = $userInfo ['resultSet'][0]['key'];
    
                    
                    $xcSendingMail = InfoUsersSendingMail:: insertSendingMail(array(
                        'user_id' => intval($insertID),
                        'auth_email' =>   $params['auth_email'], 
                        'act_email_template_id' => 1,
                        'op_user_id' => intval($opUserIdValue),
                        'key' => $keyValue, 
                        ));
                    
                    if ($xcSendingMail['errorInfo'][0] != "00000" && $xcSendingMail['errorInfo'][1] != NULL && $xcSendingMail['errorInfo'][2] != NULL)
                        throw new \PDOException($xcSendingMail['errorInfo']);
                        
                    /*
                     * email gönderelim
                     */
                    $xcUserInfo = InfoUsersSendingMail:: sendMailUrgeNewPerson(array(
                        'auth_email' =>  $params['auth_email'], 
                        'herkimse' => $params['name'].' '. $params['surname'],
                        'kume' => $kumeValue,
                        'rol' => $roleValue,
                        'key' => $keyValue,
                    ));

                    if ($xcUserInfo['errorInfo'][0] != "00000" && $xcUserInfo['errorInfo'][1] != NULL && $xcUserInfo['errorInfo'][2] != NULL)
                        throw new \PDOException($xcUserInfo['errorInfo']);
                    
            }
                    $insertID = $xc['lastInsertId'];                                
                    $pdo->commit();

                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'pk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'username';
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_users_detail tablosunda urgeci kaydı oluşturur  !!
     * @version v 1.0  31.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertUrgePersonDetail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $operationIdValue = -1;
            if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                $operationIdValue = $params['operation_type_id'];
            }
            $sql = " 
                INSERT INTO info_users_detail(
                            operation_type_id, 
                            name, 
                            surname, 
                            auth_email,
                            act_parent_id,
                            language_id,
                            root_id,
                            op_user_id,
                            password,
                            consultant_id)
                VALUES (    
                            " . intval($operationIdValue) . ", 
                            :name, 
                            :surname, 
                            :auth_email,
                            (SELECT last_value FROM info_users_detail_id_seq),
                            :language_id,
                            :root_id,
                            :op_user_id,
                            :password,
                            " . intval($params['consultant_id']) . "
                    )";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
            $statement->bindValue(':surname', $params['surname'], \PDO::PARAM_STR);
            $statement->bindValue(':auth_email', $params['auth_email'], \PDO::PARAM_STR);
            $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
            $statement->bindValue(':root_id', $params['root_id'], \PDO::PARAM_INT);
            $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
            //   echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]); 
                                
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * sys_osb_person tablosundaki urgeci kaydı oluşturur  !!
     * @version v 1.0  31.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertUrgePersonSysOsbPerson($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
                $sql = " 
                INSERT INTO sys_osb_person(
                            osb_cluster_id, 
                            user_id, 
                            op_user_id
                            )
                VALUES (    
                            ". intval($params['osb_cluster_id']).",
                            ". intval($params['user_id']).",                             
                            ". intval($params['op_user_id'])." 
                    )";                                
                $statement = $pdo->prepare($sql);                               
                // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_osb_person_id_seq');                                
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);  
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**  
     * @author Okan CIRAN
     * @ network key den firm id sini döndürür  !!     
     * @version v 1.0  09.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUrgePersonRoleAndClusterInformation($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                                
            if (isset($params['id'])) {                                
                $userId = $params['id'];  
                $sql = " 
                    SELECT user_id, 1=1 AS control, role, clusters, key FROM (
                        SELECT 
                            a.user_id, 
                            sar.name AS role, 
                            soc.name AS clusters,
                            REPLACE(TRIM(SUBSTRING(crypt(iu.sf_private_key_value_temp,gen_salt('xdes')),6,20)),'/','*') AS key
                        FROM sys_osb_person a
                        INNER JOIN info_users iu ON iu.id = a.user_id
                        INNER JOIN sys_acl_roles sar ON sar.id = iu.role_id
                        INNER JOIN sys_osb_clusters soc ON soc.id = osb_cluster_id 
                        WHERE
                         a.user_id = ".intval($userId)."
                         LIMIT 1                             
                    ) AS xtable                              
                                 ";
                $statement = $pdo->prepare($sql);
              // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  network_key not_null_violation
                $errorInfoColumn = 'network_key';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
         
    /**  
     * @author Okan CIRAN
     * @ network key den firm id sini döndürür  !!     
     * @version v 1.0  09.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getTempUserInformation($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                                
            if (isset($params['id'])) {                                
                $userId = $params['id'];  
                $sql = " 
                    SELECT user_id, 1=1 AS control, role,  key FROM (
                        SELECT 
                            a.id as user_id,
                            concat(sar.name , ' (' ,sar.name_tr ,')' )  AS role,
                            REPLACE(TRIM(SUBSTRING(crypt(a.sf_private_key_value,gen_salt('xdes')),6,20)),'/','*') AS key
                        FROM info_users a
                        INNER JOIN sys_acl_roles sar ON sar.id = a.role_id 
                        WHERE
                         a.id = ".intval($userId)."
                         LIMIT 1
                    ) AS xtable
                                 ";
                $statement = $pdo->prepare($sql);
              // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  network_key not_null_violation
                $errorInfoColumn = 'network_key';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    
    /**
     * @author Okan CIRAN
     * parametre olarak gelen 'id' li kaydın password unu update yapar  !!     
     * @version v 1.0  02.09.2016     
     * @param array | null $args
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function setPersonPassword($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = $this->getUserIdTempForPassword(array('pktemp' => $params['key']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
              
                $languageIdValue = $opUserId ['resultSet'][0]['language_id']; 

                /*
                 * kullanıcı için gerekli olan private key ve value değerleri yaratılılacak.                       
                 */
                $xcDeletedOnTheLink = InfoUsersSendingMail::setDeletedOnTheLinks(array('key' => $params['key']));
                if ($xcDeletedOnTheLink['errorInfo'][0] != "00000" && $xcDeletedOnTheLink['errorInfo'][1] != NULL && $xcDeletedOnTheLink['errorInfo'][2] != NULL)
                    throw new \PDOException($xcDeletedOnTheLink['errorInfo']);

                $affectedRows = $xcDeletedOnTheLink ['affectedRowsCount'];
                                
                if ($affectedRows == 1) {                                
                    $active = 0;    
                    $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];         
                    $url = null;
                    if (isset($params['url']) && $params['url'] != "") {
                        $url = $params['url'];
                    }    
                    $m = null;
                    if (isset($params['m']) && $params['m'] != "") {
                        $m = $params['m'];
                    }  
                    $a = null;
                    if (isset($params['a']) && $params['a'] != "") {
                        $a = $params['a'];
                    }  
                    $operationIdValue =  0;
                    $assignDefinitionIdValue = 0;
                    $operationTypeParams = array('url' => $url, 'role_id' => $opUserRoleIdValue, 'm' => $m,'a' => $a,);
                    $operationTypes = $this->slimApp->getServiceManager()->get('operationsTypesBLL');
                    $operationTypesValue = $operationTypes->getUpdateOperationId($operationTypeParams);
                    if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                        $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                        $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];
                        if ($operationIdValue > 0) {
                            $url = null;
                        }
                    }       

                    /*
                     * parametre olarak gelen array deki 'id' li kaydın, info_users tablosundaki 
                     * alanlarını update eder !! username update edilmez.  
                     */
                    $this->updateInfoUsers(array('id' => $opUserIdValue,
                        'op_user_id' => $opUserIdValue,
                        'active' => $active,
                        'operation_type_id' => $operationIdValue,
                        'language_id' => $languageIdValue,
                        'password' => $params['password'],
                    ));

                    /*
                     *  parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
                     * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
                     */
                    $this->setUserDetailsDisables(array('id' => $opUserIdValue));

                    $operationIdValueDetail = $operationIdValue; 
                    $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public,  
                           operation_type_id,
                           active,
                           name,
                           surname,
                           auth_email,
                           language_id,
                           op_user_id,
                           root_id,
                           act_parent_id,
                           auth_allow_id,
                           password 
                            ) 
                           SELECT 
                                0 AS profile_public, 
                                " . intval($operationIdValueDetail) . " AS operation_type_id,
                                " . intval($active) . " AS active, 
                                name, 
                                surname,
                                auth_email,   
                                language_id,   
                                " . intval($opUserIdValue) . " AS op_user_id,
                                a.root_id,
                                a.act_parent_id,
                                1,
                                md5('" . $params['password'] . "') AS password
                            FROM info_users_detail a
                            WHERE root_id  =" . intval($opUserIdValue) . "                               
                                AND active =1 AND deleted =0 AND 
                                c_date = (SELECT MAX(c_date)  
						FROM info_users_detail WHERE root_id =a.root_id
						AND active =1 AND deleted =0)  
                    ";
                    $statementActInsert = $pdo->prepare($sql);
                   //  echo debugPDO($sql, $params);                                
                    $insertAct = $statementActInsert->execute();
                    $affectedRows = $statementActInsert->rowCount();
                    $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                    $errorInfo = $statementActInsert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                     * ufak bir trik var. 
                     * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                     * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                     * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                     */
                    $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                      array('operation_type_id' =>$operationIdValue, 'id' => $params['id'],));
                    if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                        $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];                       
                        // $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                       
                    }
                                
                    $consultantProcessSendParams = array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                'url' => $url, 
                                'assign_definition_id' => $assignDefinitionIdValue, // operasyon atama tipi
                        );
                    $setConsultantProcessSend = $this->slimApp-> getBLLManager()->get('consultantProcessSendBLL');  
                    $setConsultantProcessSendArray= $setConsultantProcessSend->insert($consultantProcessSendParams);
                    if ($setConsultantProcessSendArray['errorInfo'][0] != "00000" &&
                            $setConsultantProcessSendArray['errorInfo'][1] != NULL &&
                            $setConsultantProcessSendArray['errorInfo'][2] != NULL)
                        throw new \PDOException($setConsultantProcessSendArray['errorInfo']);   
                    
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    $errorInfo = '23502';  /// 23502 user_id not_null_violation
                    $errorInfoColumn = 'key';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';  /// 23502 user_id not_null_violation
                $errorInfoColumn = 'pk';
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * sesionId si gelen user in act_session tablosundaki kaydında bulunan 
     * public_key alanına pktemp i yazar.   !!
     * @version v 1.0  31.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function updatePktempForSesionId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $sql = " 
                UPDATE act_session
                SET public_key = :pktemp
                WHERE id = :sesionId
                    ";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':sesionId', $params['sesionId'], \PDO::PARAM_STR);
            $statement->bindValue(':pktemp', $params['pktemp'], \PDO::PARAM_STR);     
           // echo debugPDO($sql, $params);  
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {   
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    
    
    
}
