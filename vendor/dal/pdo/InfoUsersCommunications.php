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
 * Class using Zend\ServiceManager\FactoryInterface
 * created to be used by DAL MAnager
 * @
 * @author Okan CIRAN
 */
class InfoUsersCommunications extends \DAL\DalSlim {

    /**

     * @author Okan CIRAN
     * @ info_users_communications tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  01.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
            try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE info_users_communications
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $userIdValue . "     
                WHERE id = :id");
                //Execute our DELETE statement.
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
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
     * @ info_users_communications tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  01.02.2016    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                 SELECT 
                    a.id,  
                    b.root_id as user_id,
		    b.name as name ,
		    b.surname as surname,        
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                    a.language_parent_id,
                    a.description,
                    a.description_eng,                   
                    a.op_user_id,                    
                    u.username as op_username  ,
                    b.operation_type_id,
                    op.operation_name ,
                    a.communications_type_id, 
                    sd6.description as comminication_type,   
                    a.communications_no,
                    a.profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    sd7.description as consultant_confirm_type,   
                    a.confirm_id,
                    a.default_communication_id,
                    CASE a.default_communication_id 
                        when 1 THEN 'Default' 
                    END as default_communication
                FROM info_users_communications  a
                inner join info_users_detail b on b.root_id = a.user_id and b.active = 0 and b.deleted = 0  
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_id = a.language_id AND sd6.deleted = 0 AND sd6.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id 
		INNER JOIN sys_specific_definitions as sd7 on sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
		INNER JOIN sys_operation_types op on op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id                            
                ORDER BY sd6.first_group              
                                 ");
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
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
     * @ info_users_communications tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  09.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');       
            $statement = $pdo->prepare(" 
                UPDATE info_users_communications
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
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {       
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users_communications tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  01.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {   
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();            
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (!\Utill\Dal\Helper::haveRecord($opUserId)) {
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
                $operationTypesValue = $operationTypes->getInsertOperationId($operationTypeParams);
                if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                    $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                    $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                     
                }  
                if ((isset($params['user_id']) && $params['user_id'] != "")) {
                    $userId = $params['user_id'];
                } else {
                    $userId = $opUserIdValue;
                }
              
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

                $statement = $pdo->prepare("
                        INSERT INTO info_users_communications (
                                user_id,
                                op_user_id,
                                language_id,
                                operation_type_id,                                
                                communications_type_id, 
                                communications_no, 
                                description, 
                                description_eng,
                                profile_public,
                                act_parent_id,
                                default_communication_id,
                                consultant_id
                                )
                        VALUES (                                
                                " . intval($userId) . ",
                                " . intval($opUserIdValue) . ",
                                " . intval($languageIdValue) . ",
                                " . intval($operationIdValue) . ",                                
                                :communications_type_id, 
                                :communications_no, 
                                :description, 
                                :description_eng,
                                :profile_public,
                                (SELECT last_value FROM info_users_communications_id_seq),
                                :default_communication_id,
                                :consultant_id
                                                ");
                
                $statement->bindValue(':communications_type_id', $params['communications_type_id'], \PDO::PARAM_INT);
                $statement->bindValue(':communications_no', $params['communications_no'], \PDO::PARAM_STR);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                $statement->bindValue(':default_communication_id', $params['default_communication_id'], \PDO::PARAM_INT);
                $statement->bindValue(':consultant_id', $ConsultantId , \PDO::PARAM_INT);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_communications_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]); 
                
                $consultantProcessSendParams = array(
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
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
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
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
     * @ info_users_communications tablosunda user_id & communications_type_id & communications_no sutununda daha önce oluşturulmuş mu? 
     * @todo su an için insert ve update  fonksiyonlarında aktif edilmedi. daha sonra aktif edilecek
     * @version v 1.0 01.02.2016
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
                communications_no AS communications_no , 
                '" . $params['communications_no'] . "' AS value , 
                communications_no ='" . $params['communications_no'] . "' AS control,
                CONCAT(communications_no , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_users_communications                
            WHERE user_id = " . intval($params['user_id']) . " AND 
                LOWER(TRIM(communications_no)) = LOWER(TRIM('" . $params['communications_no'] . "')) AND
                LOWER(TRIM(communications_type_id)) = LOWER(TRIM('" .  intval($params['communications_type_id']) . "')) 
                " . $addSql . "
                AND active =0
                AND deleted=0  
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
     * info_users_communications tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  01.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                
                $this->makePassive(array('id' => $params['id']));                            
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
                if ((isset($params['user_id']) && $params['user_id'] != "")) {
                    $userId = $params['user_id'];
                } else {
                    $userId = $opUserIdValue;
                } 
                $active  = 0 ;
                if ((isset($params['active']) && $params['active'] != "")) {
                    $active = $params['active'];
                } 
                $profilePublic  = 0 ;
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                    $profilePublic = $params['profile_public'];
                } 

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
               
                $statementInsert = $pdo->prepare("
                INSERT INTO info_users_communications (
                        active, 
                        op_user_id, 
                        operation_type_id,
                        communications_type_id, 
                        communications_no, 
                        description, 
                        description_eng,
                        profile_public,
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id, 
                        act_parent_id, 
                        language_parent_id,
                        user_id,
                        language_id,
                        act_parent_id,
                        default_communication_id
                        )  
                SELECT 
                    " . intval($active) . " AS active,
                    " . intval($opUserIdValue) . " AS op_user_id,  
                    " . intval($operationIdValue) . " AS operation_type_id,
                    " . intval($params['communications_type_id']) . " AS communications_type_id,
                    '" . $params['communications_no'] . "' AS communications_no,
                    '" . $params['description'] . "' AS description,
                    '" . $params['description_eng'] . "' AS description_eng,
                    " . intval($profilePublic) . " AS profile_public,                 
                    consultant_id, 
                    consultant_confirm_type_id, 
                    confirm_id, 
                    act_parent_id, 
                    language_parent_id,
                    " . intval($userId) . ",
                    " . intval($languageIdValue) . ",
                    act_parent_id,
                    " . intval($params['default_communication_id']) . " AS default_communication_id 
                FROM info_users_communications 
                WHERE id  =" . intval($params['id']) . "                  
                                                ");
                $result = $statementInsert->execute();
                $insertID = $pdo->lastInsertId('info_users_communications_id_seq');               
                $affectedRows = $statementInsert->rowCount();
                $errorInfo = $statementInsert->errorInfo();
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
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
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
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'user_id';
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
     * @ Gridi doldurmak için info_users_communications tablosundan kayıtları döndürür !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
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
        $whereSql = '';
        $sortArr = array();
        $orderArr = array();
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "sd6.first_group";
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
        $whereSql .= " AND a.language_id =  " . intval($languageIdValue);


        if (isset($args['search_name']) && $args['search_name'] != "") {
            $whereSql .= " AND LOWER(( TRIM(concat(b.name ,' ', b.surname)))) LIKE '%" . $args['search_name'] . "%' ";
        }
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                    a.id,  
                    b.root_id as user_id,
		    b.name as name ,
		    b.surname as surname,        
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                    a.language_parent_id,
                    a.description,
                    a.description_eng,                   
                    a.op_user_id,                    
                    u.username as op_username  ,
                    b.operation_type_id,
                    op.operation_name ,
                    a.communications_type_id, 
                    sd6.description as comminication_type,   
                    a.communications_no,
                    a.profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    sd7.description as consultant_confirm_type,   
                    a.confirm_id,
                    a.default_communication_id,
                    CASE a.default_communication_id 
                        when 1 THEN 'Default' 
                    END as default_communication
                FROM info_users_communications  a
                inner join info_users_detail b on b.root_id = a.user_id and b.active = 0 and b.deleted = 0  
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_id = a.language_id AND sd6.deleted = 0 AND sd6.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id 
		INNER JOIN sys_specific_definitions as sd7 on sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
		INNER JOIN sys_operation_types op on op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id              
                WHERE a.deleted =0  
                " . $whereSql . "
                ORDER BY    " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";
            $statement = $pdo->prepare($sql);
            $parameters = array(
                'sort' => $sort,
                'order' => $order,
                'limit' => $pdo->quote($limit),
                'offset' => $pdo->quote($offset),
            );
            //  echo debugPDO($sql, $parameters);

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
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_users_communications tablosundan kayıtları döndürür !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingular($args = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {                
                $opUserIdValue =  $opUserId ['resultSet'][0]['user_id'];
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

                $sql = "
                 SELECT 
                    a.id,  
                    b.root_id as user_id,
		    b.name as name ,
		    b.surname as surname,        
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                    a.language_parent_id,
                    a.description,
                    a.description_eng,                   
                    a.op_user_id,                    
                    u.username as op_username  ,
                    b.operation_type_id,
                    op.operation_name ,
                    a.communications_type_id, 
                    sd6.description as comminication_type,   
                    a.communications_no,
                    a.profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    sd7.description as consultant_confirm_type,   
                    a.confirm_id,
                    a.default_communication_id ,
                    CASE a.default_communication_id 
                        when 1 THEN 'Default' 
                    END as default_communication                    
                FROM info_users_communications  a
                inner join info_users_detail b ON b.root_id = a.user_id AND b.active = 0 and b.deleted = 0  
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_id = a.language_id AND sd6.deleted = 0 AND sd6.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id 
		INNER JOIN sys_specific_definitions AS sd7 ON sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
		INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id              
                WHERE a.deleted =0 AND a.active =0 AND 
                      b.user_id = " . intval($opUserIdValue)." AND 
                      a.language_id =  " . intval($languageIdValue)." 
                ORDER BY sd6.first_group 
                ";
                $statement = $pdo->prepare($sql);
                //  echo debugPDO($sql, $parameters);               
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'user_id';         
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**     
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_users_communications tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                
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
               
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                          

                $sql = "
                SELECT 
                        COUNT(a.id) AS COUNT
                FROM info_users_communications  a
                INNER JOIN info_users_detail b ON b.root_id = a.user_id AND b.active = 0 AND b.deleted = 0  
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_id = a.language_id AND sd6.deleted = 0 AND sd6.active = 0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id 
		INNER JOIN sys_specific_definitions AS sd7 ON sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
		INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id 
                WHERE a.language_id = " . intval($languageIdValue)." 
                    AND b.user_id = " . intval($opUserIdValue) ." 
                    ";
                $statement = $pdo->prepare($sql);
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
     * @ Gridi doldurmak için info_users_communications tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSql = " WHERE a.language_code = '" . $params['language_code'] . "'";
            $whereSql1 = " WHERE a1.deleted =0 AND a1.language_code = '" . $params['language_code'] . "' ";
            $whereSql2 = " WHERE a2.deleted =1 AND a2.language_code = '" . $params['language_code'] . "' ";
            if (isset($params['search_name']) && $params['search_name'] != "") {
                $whereSql .= " AND LOWER(( TRIM(concat(b.name ,' ', b.surname)))) LIKE '%" . $params['search_name'] . "%' ";
                $whereSql1 .= " AND LOWER(( TRIM(concat(b1.name ,' ', b1.surname)))) LIKE '%" . $params['search_name'] . "%' ";
                $whereSql2 .= " AND LOWER(( TRIM(concat(b2.name ,' ', b2.surname)))) LIKE '%" . $params['search_name'] . "%' ";
            }

            $sql = "
                SELECT 
                        COUNT(a.id) AS COUNT ,  
                        (SELECT COUNT(a1.id)  
                        FROM info_users_communications  a1
                        inner join info_users_detail b1 on b1.root_id = a1.user_id AND b1.deleted =0 and b1.active =0 
                        INNER JOIN sys_specific_definitions sdx ON sdx.main_group = 15 AND sdx.first_group= a1.deleted AND sdx.language_code = a1.language_code AND sdx.deleted = 0 AND sdx.active = 0
                        INNER JOIN sys_specific_definitions sd1x ON sd1x.main_group = 16 AND sd1x.first_group= a1.active AND sd1x.language_code = a1.language_code AND sd1x.deleted = 0 AND sd1x.active = 0                
                        INNER JOIN sys_specific_definitions sd6x ON sd6x.main_group = 5 AND sd6x.first_group= a1.communications_type_id AND sd6x.language_code = a1.language_code AND sd6x.deleted = 0 AND sd6x.active = 0
                        INNER JOIN sys_language lx ON lx.language_main_code = a1.language_code AND lx.deleted =0 AND lx.active = 0 
                        INNER JOIN info_users ux ON ux.id = a1.op_user_id 
                          " . $whereSql1 . " ),		
                        (SELECT COUNT(a2.id)  
                        FROM info_users_communications  a2
                        inner join info_users_detail b2 on b2.root_id = a2.user_id AND b2.deleted =0 and b2.active =0 
                        INNER JOIN sys_specific_definitions sdy ON sdy.main_group = 15 AND sdy.first_group= a2.deleted AND sdy.language_code = a2.language_code AND sdy.deleted = 0 AND sdy.active = 0
                        INNER JOIN sys_specific_definitions sd1y ON sd1y.main_group = 16 AND sd1y.first_group= a2.active AND sd1y.language_code = a2.language_code AND sd1y.deleted = 0 AND sd1y.active = 0                
                        INNER JOIN sys_specific_definitions sd6y ON sd6y.main_group = 5 AND sd6y.first_group= a2.communications_type_id AND sd6y.language_code = a2.language_code AND sd6y.deleted = 0 AND sd6y.active = 0
                        INNER JOIN sys_language ly ON ly.language_main_code = a2.language_code AND ly.deleted =0 AND ly.active = 0 
                        INNER JOIN info_users uy ON uy.id = a2.op_user_id 
                         " . $whereSql2 . " )		  
                FROM info_users_communications  a
                inner join info_users_detail b on b.root_id = a.user_id AND b.deleted =0 and b.active =0 
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_code = a.language_code AND sd6.deleted = 0 AND sd6.active = 0
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
                INNER JOIN info_users u ON u.id = a.op_user_id 
                " . $whereSql . "
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
     * @author Okan CIRAN
     * @ listbox ya da combobox doldurmak için info_users_communications tablosundan user_id nin iletişim tiplerini döndürür !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUserCommunicationsTypes($params = array()) {
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
                
                $statement = $pdo->prepare("
                SELECT                
                    a.id ,	
                    sd6.description AS name                                 
                FROM info_users_communications a       
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_id = a.language_id AND sd6.deleted = 0 AND sd6.active = 0                     
                WHERE 
                    a.active =0 AND a.deleted = 0 AND 
                    a.language_id = :language_id AND 
                    a.user_id = :user_id                    
                ORDER BY name                
                                 ");
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':user_id', $opUserIdValue, \PDO::PARAM_STR);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'pk';            
                $result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {      
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users_communications tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  01.02.2016
     * @todo Su an için aktif değil SQl in değişmesi lazım. 
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare(" 
                
                INSERT INTO info_users_communications(
                    name, name_eng, language_id, ordr, language_parent_id, 
                    description, description_eng, user_id, language_code)
                   
                SELECT    
                    name, name_eng, language_id, ordr, language_parent_id, 
                    description, description_eng, user_id, language_main_code
                FROM ( 
                       SELECT 
                            '' AS name,                             
                            COALESCE(NULLIF(c.name_eng, ''), c.name) AS name_eng, 
                            l.id as language_id, 
                            c.ordr,
                            c.id AS language_parent_id,    
                            '' AS description,
                            description_eng,
                            c.user_id, 		 
                            l.language_main_code
                        FROM info_users_communications c
                        LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                        WHERE c.id =  " . intval($params['id']) . "
                        ) AS xy   
                        WHERE xy.language_main_code NOT IN 
                            (SELECT DISTINCT language_code 
                            FROM info_users_communications cx 
                            WHERE 
                                (cx.language_parent_id = " . intval($params['id']) . "  OR
                                cx.id = " . intval($params['id']) . " ) AND
                                cx.deleted =0 AND 
                                cx.active =0)) 
                    ");
        
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_users_communications_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /** 
     * @author Okan CIRAN     
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
     * @todo Su an için aktif değil SQl in değişmesi lazım. 
     * @version v 1.0  06.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillTextLanguageTemplate($args = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                    SELECT 
                    a.id, 
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name, 
                    a.name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                    a.ordr as siralama,
                    a.language_parent_id,
                    a.description,
                    a.description_eng,                  
                    a.user_id,
                    u.username    
                FROM info_users_communications  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id 
                WHERE  
                        a.language_code = :language_code AND 
                        a.language_parent_id = :language_parent_id AND
                        a.active = 0 AND 
                        a.deleted = 0

                    ";

            $statement = $pdo->prepare($sql);
            /**
             * For debug purposes PDO statement sql
             * uses 'Panique' library located in vendor directory
             */
            $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_STR);
            $statement->bindValue(':language_parent_id', $args['id'], \PDO::PARAM_STR);
            //    echo debugPDO($sql, $parameters);
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
     * @author Okan CIRAN     
     * @ info_users_communications tablosundan parametre olarak  gelen id kaydın active alanını 1 yapar ve 
     * yeni yeni kayıt oluşturarak deleted ve active = 1 olarak  yeni kayıt yapar. ! 
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
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
                   
                $kontrol = $this->haveRecords(array(
                                        'user_id' => $opUserIdValue, 
                                        'communications_no' => $params['communications_no'], 
                                        'communications_type_id' => $params['communications_type_id'], 
                                            ));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                
                
                
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

                $this->makePassive(array('id' => $params['id']));

                $statementInsert = $pdo->prepare(" 
                    INSERT INTO info_users_communications (
                        user_id,
                        active, 
                        deleted,
                        op_user_id,
                        operation_type_id,
                        communications_type_id, 
                        communications_no, 
                        description, 
                        description_eng,
                        profile_public,
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id,
                        language_parent_id,                        
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        act_parent_id,
                        default_communication_id 
                        )
                    SELECT
                        user_id,
                        1 AS active,  
                        1 AS deleted, 
                        " . intval($opUserIdValue) . " AS op_user_id,
                        " . intval($operationIdValue) . ",
                        communications_type_id,
                        communications_no,
                        description,
                        description_eng,
                        profile_public,
                        consultant_id, 
                        consultant_confirm_type_id, 
                        confirm_id,
                        language_parent_id ,                        
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        act_parent_id,
                        default_communication_id 
                    FROM info_users_communications 
                    WHERE id  =" . intval($params['id']) . "    
                    )");

                $insertAct = $statementInsert->execute();
                $affectedRows = $statementInsert->rowCount();
                $insertID = $pdo->lastInsertId('info_users_communications_id_seq');
                $errorInfo = $statementInsert->errorInfo();
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
                    $assignDefinitionIdValue = $consIdAndLanguageId ['resultSet'][0]['assign_definition_id'];
                }
 
                $consultantProcessSendParams = array(
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
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
                    $errorInfo = '23505';
                    $errorInfoColumn = 'communications_no';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }                
            } else {
                $errorInfo = '23502';  /// 23502  not_null_violation
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
     * @ info_users_communications tablosuna pktemp için yeni bir kayıt oluşturur.  !!
     * @version v 1.0  01.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insertTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
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
                $operationTypesValue = $operationTypes->getInsertOperationId($operationTypeParams);
                if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                    $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                    $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];                    
                }  
                $userId = $opUserIdValue;   
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
                $sql = " 
                        INSERT INTO info_users_communications (
                                op_user_id,
                                user_id,  
                                language_id,
                                operation_type_id, 
                                communications_type_id, 
                                communications_no, 
                                description, 
                                description_eng,
                                profile_public,   
                                act_parent_id,
                                default_communication_id ,
                                consultant_id
                                )                        
                        VALUES (                                
                                " . intval($opUserIdValue) . ",
                                " . intval($userId) . ",
                                " . intval($languageIdValue) . ",
                                " . intval($operationIdValue) . ",
                                :communications_type_id, 
                                :communications_no, 
                                :description, 
                                :description_eng,
                                :profile_public,
                                (SELECT last_value FROM info_users_communications_id_seq),
                                :default_communication_id,
                                :consultant_id                    
                                              )  ";
                $statement = $pdo->prepare($sql);                
                $statement->bindValue(':communications_type_id', $params['communications_type_id'], \PDO::PARAM_INT);
                $statement->bindValue(':communications_no', $params['communications_no'], \PDO::PARAM_STR);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                $statement->bindValue(':default_communication_id', $params['default_communication_id'], \PDO::PARAM_INT);
                $statement->bindValue(':consultant_id',$ConsultantId, \PDO::PARAM_INT);
                // echo debugPDO($sql, $params);                
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_communications_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);                
                
                $consultantProcessSendParams = array(
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
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
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pktemp';
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
     * info_users_communications tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  01.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updateTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
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
                $operationTypesValue = $operationTypes->getUpdateOperationId($operationTypeParams);
                if (\Utill\Dal\Helper::haveRecord($operationTypesValue)) { 
                    $operationIdValue = $operationTypesValue ['resultSet'][0]['id']; 
                    $assignDefinitionIdValue = $operationTypesValue ['resultSet'][0]['assign_definition_id'];
                    if ($operationIdValue > 0) {
                        $url = null;
                    }
                }      
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
                $defaultCommunicationId = 0 ; 
                if ((isset($params['default_communication_id']) && $params['default_communication_id'] != "")) {                
                    $defaultCommunicationId =  $params['default_communication_id'] ;
                }  
                
                $communicationsTypeId = 0 ; 
                if ((isset($params['communications_type_id']) && $params['communications_type_id'] != "")) {                
                    $communicationsTypeId =  $params['communications_type_id'] ;
                }  
                $active = 0 ; 
                if ((isset($params['active']) && $params['active'] != "")) {                
                    $active =  $params['active'] ;
                }  
                $profilePublic = 0 ; 
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {                
                    $profilePublic =  $params['profile_public'] ;
                }     
                          
                $this->makePassive(array('id' => $params['id']));
                $statementInsert = $pdo->prepare("
                INSERT INTO info_users_communications (
                        user_id,                        
                        active, 
                        op_user_id, 
                        operation_type_id,                         
                        language_id,  
                        communications_type_id, 
                        communications_no, 
                        description, 
                        description_eng,                        
                        profile_public,                         
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id, 
                        act_parent_id, 
                        language_parent_id,                        
                        default_communication_id                        
                        )  
                SELECT
                    user_id,
                    " . intval($active) . " AS active,   
                    " . intval($opUserIdValue) . " AS op_user_id,  
                    " . intval($operationIdValue) . " AS operation_type_id,                    
                    " . intval($languageIdValue) . " AS language_id,    
                    " . intval($communicationsTypeId) . " AS communications_type_id,
                    '" . $params['communications_no'] . "' AS communications_no,
                    '" . $params['description'] . "' AS description,
                    '" . $params['description_eng'] . "' AS description_eng,
                     " . intval($profilePublic) . " AS profile_public,                                
                    consultant_id, 
                    consultant_confirm_type_id, 
                    confirm_id, 
                    act_parent_id, 
                    language_parent_id,                    
                    " . intval($defaultCommunicationId) . " AS default_communication_id                     
                FROM info_users_communications 
                WHERE id  =" . intval($params['id']) . "                     
                                                ");
                $result = $statementInsert->execute();
                $insertID = $pdo->lastInsertId('info_users_communications_id_seq');
                $affectedRows = $statementInsert->rowCount();
                $errorInfo = $statementInsert->errorInfo();
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
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
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
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pktemp';
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
     * @ Gridi doldurmak için info_users_communications tablosundan kayıtları döndürür !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
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
                    a.id,
                    a.active,
                    a.profile_public,
                    a.communications_type_id,
                    COALESCE(NULLIF(sd5x.description , ''), sd5.description_eng) AS comminication_type,
                    a.communications_no,
                    a.default_communication_id,
                    CASE a.default_communication_id 
                        WHEN 1 THEN 'Default' 
                    END AS default_communication
                FROM info_users_communications a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
                INNER JOIN sys_specific_definitions sd5 ON sd5.main_group = 5 AND sd5.first_group= a.communications_type_id AND sd5.language_id = a.language_id AND sd5.deleted = 0 AND sd5.active = 0
		LEFT JOIN sys_specific_definitions sd5x ON (sd5x.id = sd5.id OR sd5x.language_parent_id = sd5.id) AND sd5x.language_id = lx.id  AND sd5x.deleted = 0 AND sd5x.active = 0
                WHERE 
                    a.deleted =0 AND 
                    a.active =0 AND 
                    a.language_parent_id = 0 
                    a.user_id = " . intval($opUserId ['resultSet'][0]['user_id'])."                     
                ORDER BY sd5.first_group 
                ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $args);         
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pktemp';              
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**     
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_users_communications tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularRowTotalCountTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {     
                $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT
                FROM info_users_communications a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0
                WHERE 
                    a.deleted =0 AND 
                    a.active =0 AND 
                    a.language_parent_id = 0 
                    a.user_id = " . intval($opUserId ['resultSet'][0]['user_id'])."                     
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
     * @ listbox ya da combobox doldurmak için info_users_communications tablosundan user_id nin iletişim tiplerini döndürür !!
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUserCommunicationsTypesTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
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
                $statement = $pdo->prepare("
                SELECT                
                    a.id ,	
                    sd6.description AS name                                 
                FROM info_users_communications a       
                INNER JOIN sys_specific_definitions sd6 ON sd6.main_group = 5 AND sd6.first_group= a.communications_type_id AND sd6.language_id = a.language_id AND sd6.deleted = 0 AND sd6.active = 0                     
                WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND 
                    a.language_id = :language_id AND 
                    a.user_id = :user_id                    
                ORDER BY name                
                                 ");
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':user_id', $opUserIdValue, \PDO::PARAM_INT);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'pk';              
                //$result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {       
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN     
     * @ info_users_communications tablosundan parametre olarak  gelen id kaydın active alanını 1 yapar ve 
     * yeni yeni kayıt oluşturarak deleted ve active = 1 olarak  yeni kayıt yapar. ! 
     * @version v 1.0  01.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function deletedActTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pktemp' =>  $params['pktemp'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserIdTemp($opUserIdParams);
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
                $this->makePassive(array('id' => $params['id']));
                $statementInsert = $pdo->prepare(" 
                    INSERT INTO info_users_communications (
                        user_id,                        
                        active, 
                        deleted,
                        op_user_id,
                        operation_type_id,                         
                        communications_type_id, 
                        communications_no, 
                        description, 
                        description_eng,                        
                        profile_public,                         
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id,                        
                        language_parent_id ,                        
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        default_communication_id 
                        )                            
                    SELECT
                        user_id,
                        1 AS active,  
                        1 AS deleted, 
                        ". intval($opUserIdValue) . " AS op_user_id,                  
                        ". intval($operationIdValue) . ",                        
                        communications_type_id,
                        communications_no,
                        description,
                        description_eng,
                        profile_public,                         
                        consultant_id, 
                        consultant_confirm_type_id, 
                        confirm_id,                        
                        language_parent_id ,                        
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        default_communication_id                     
                    FROM info_users_communications 
                    WHERE id  =" . intval($params['id']) . "    
                    )");
                $insertAct = $statementInsert->execute();
                $affectedRows = $statementInsert->rowCount();
                $errorInfo = $statementInsert->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {
                $errorInfo = '23502';  /// 23502  not_null_violation
                $errorInfoColumn = 'pktemp';
                 $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

}
