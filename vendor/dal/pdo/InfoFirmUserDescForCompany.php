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
 * @author Okan CİRANĞ
 */
class InfoFirmUserDescForCompany extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_user_desc_for_company tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 25.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE info_firm_user_desc_for_company
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id = :id");
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
     * @ info_firm_user_desc_for_company tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  25.04.2016   
     * @param array | null $args
     * @return array
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
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            }
            $statement = $pdo->prepare("                    
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
                        a.user_id,
			ud.name, 
			ud.surname,
			COALESCE(NULLIF(ax.verbal1_title, ''), a.verbal1_title_eng) AS verbal1_title,
			a.verbal1_title_eng,
			COALESCE(NULLIF(ax.verbal1, ''), a.verbal1_eng) AS verbal1,
			a.verbal1_eng,			
			a.s_date,
                        a.c_date,
                        a.profile_public,
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                        a.operation_type_id,
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
			a.act_parent_id,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,		                                                                     
                        a.active,
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                        a.deleted,
			COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                        a.op_user_id,
                        u.username AS op_user,
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.language_parent_id,
                        ifk.network_key
                    FROM info_firm_user_desc_for_company a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_user_desc_for_company ax ON (ax.id = a.id OR ax.language_parent_id=a.id)  AND ax.active = 0 AND ax.deleted = 0 AND ax.language_id =lx.id  
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = fp.id OR fpx.language_parent_id=fp.id)  AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2 
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = l.id  AND a.cons_allow_id = sd14.first_group  AND sd14.deleted =0 AND sd14.active =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted =0 AND sd15.active =0 
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                    
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
		   ORDER BY a.language_id,firm_name,ud.name, ud.surname,a.s_date

                          ");
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_firm_user_desc_for_company tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
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
                UPDATE info_firm_user_desc_for_company
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
     * @ info_firm_user_desc_for_company tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
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
                $languageCode = 'tr';
                $languageIdValue = 647;
                if (isset($params['language_code']) && $params['language_code'] != "") {
                    $languageCode = $params['language_code'];
                }
                $languageCodeParams = array('language_code' => $languageCode,);
                $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
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
                $profilePublic = 0;
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                    $profilePublic = intval($params['profile_public']);
                }   

                $sql = " 
                        INSERT INTO info_firm_user_desc_for_company(
                            firm_id, 
                            consultant_id,
                            operation_type_id, 
                            language_id,
                            op_user_id,
                            profile_public,
                            act_parent_id,
                            verbal1_title, 
                            verbal1,
                            verbal1_title_eng, 
                            verbal1_eng
                            )
                        VALUES (
                            :firm_id, 
                            " . intval($ConsultantId) . ",
                            " . intval($operationIdValue) . ", 
                            " . intval($languageIdValue) . ", 
                            " . intval($opUserIdValue) . ", 
                            " . intval($profilePublic) . ",
                            (SELECT last_value FROM info_firm_user_desc_for_company_id_seq),                         
                            :verbal1_title, 
                            :verbal1,
                            :verbal1_title_eng, 
                            :verbal1_eng
                             )";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':firm_id', $getFirmId, \PDO::PARAM_INT);
                $statement->bindValue(':verbal1_title', $params['verbal1_title'], \PDO::PARAM_STR);
                $statement->bindValue(':verbal1', $params['verbal1'], \PDO::PARAM_STR);
                $statement->bindValue(':verbal1_title_eng', $params['verbal1_title_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':verbal1_eng', $params['verbal1_eng'], \PDO::PARAM_STR);
                //  echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_firm_user_desc_for_company_id_seq');
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
     * info_firm_user_desc_for_company tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
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
                $languageCode = 'tr';
                $languageIdValue = 647;
                if (isset($params['language_code']) && $params['language_code'] != "") {
                    $languageCode = $params['language_code'];
                }
                $languageCodeParams = array('language_code' => $languageCode,);
                $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
                if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                    $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
                }

                $profilePublic = 0;
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                    $profilePublic = intval($params['profile_public']);
                }
                $active = 0;
                if ((isset($params['active']) && $params['active'] != "")) {
                    $profilePublic = intval($params['active']);
                }

                $sql = " 
                        INSERT INTO info_firm_user_desc_for_company(
                            firm_id, 
                            consultant_id,
                            operation_type_id, 
                            language_id,
                            op_user_id,
                            profile_public,
                            act_parent_id,
                            verbal1_title, 
                            verbal1,
                            verbal1_title_eng, 
                            verbal1_eng,
                            language_parent_id,
                            active
                            )
                        SELECT 
                            firm_id, 
                            consultant_id,
                            " . intval($operationIdValue) . " AS operation_type_id,
                            " . intval($languageIdValue) . " AS language_id,   
                            " . intval($opUserIdValue) . " AS op_user_id, 
                            " . intval($profilePublic) . " AS profile_public, 
                            act_parent_id,
                            '" . $params['verbal1_title'] . "' AS verbal1_title,
                            '" . $params['verbal1'] . "' AS verbal1,                            
                            '" . $params['verbal1_title_eng'] . "' AS verbal1_title_eng,
                            '" . $params['verbal1_eng'] . "' AS verbal1_eng,                            
                            language_parent_id,
                            " . intval($active) . " AS active
                        FROM info_firm_user_desc_for_company 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_user_desc_for_company_id_seq');
                $errorInfo = $statement_act_insert->errorInfo();
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
     * @ Gridi doldurmak için info_firm_user_desc_for_company tablosundan kayıtları döndürür !!
     * @version v 1.0  25.04.2016
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

        $sortArr = array();
        $orderArr = array();
        $whereSql = "";
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = " a.language_id,firm_name,ud.name, ud.surname,a.s_date ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
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
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
        }

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "                      
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
                        a.user_id,
			ud.name, 
			ud.surname,
			COALESCE(NULLIF(ax.verbal1_title, ''), a.verbal1_title_eng) AS verbal1_title,
			a.verbal1_title_eng,
			COALESCE(NULLIF(ax.verbal1, ''), a.verbal1_eng) AS verbal1,
			a.verbal1_eng,			
			a.s_date,
                        a.c_date,
                        a.profile_public,
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                        a.operation_type_id,
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
			a.act_parent_id,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,		                                                                     
                        a.active,
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                        a.deleted,
			COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                        a.op_user_id,
                        u.username AS op_user,
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.language_parent_id,
                        ifk.network_key
                    FROM info_firm_user_desc_for_company a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_user_desc_for_company ax ON (ax.id = a.id OR ax.language_parent_id=a.id)  AND ax.active = 0 AND ax.deleted = 0 AND ax.language_id =lx.id  
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = fp.id OR fpx.language_parent_id=fp.id)  AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2 
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = l.id  AND a.cons_allow_id = sd14.first_group  AND sd14.deleted =0 AND sd14.active =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted =0 AND sd15.active =0 
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                    
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
             
		    WHERE a.deleted = 0 AND a.active =0 AND a.language_parent_id =0
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
     * @ Gridi doldurmak için info_firm_user_desc_for_company tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            }
            $whereSQL = " WHERE a.deleted = 0 AND a.active =0 AND a.language_parent_id =0 ";

            $sql = "
                 SELECT 
                    COUNT(a.id) AS COUNT
                    FROM info_firm_user_desc_for_company a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2 
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0                                        
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = l.id  AND a.cons_allow_id = sd14.first_group  AND sd14.deleted =0 AND sd14.active =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted =0 AND sd15.active =0 
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                    
              " . $whereSQL . "'
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
     * @ info_firm_user_desc_for_company tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  25.04.2016
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare("                 
                    
                    INSERT INTO info_firm_user_desc_for_company(
                        language_parent_id, firm_name,firm_name_eng, 
			profile_public, f_check, s_date, active, country_id, 
			operation_type_id,  web_address, tax_office, 
			tax_no, sgk_sicil_no, ownership_status_id, foundation_year,  
			act_parent_id, bagkur_sicil_no, deleted, 
			auth_allow_id, owner_user_id, firm_name_short ,op_user_id,   language_code)  
                    SELECT                          
			language_parent_id,  
                        firm_name,
                        firm_name_eng, 
			profile_public, 
                        f_check, 
                        s_date,                         
                        active, 
                        country_id, 
			operation_type_id,  
                        web_address, 
                        tax_office, 
			tax_no, 
                        sgk_sicil_no, 
                        ownership_status_id, 
                        foundation_year,  
			act_parent_id, 
                        bagkur_sicil_no, 
                        deleted, 
			auth_allow_id,  
                        owner_user_id, 
                        firm_name_short ,
                        op_user_id, 
                        language_main_code 
                    FROM ( 
                            SELECT 
				c.id AS language_parent_id,                                
				'' AS firm_name, 
                                c.firm_name_eng, 
                                c.profile_public, 
                                0 AS f_check, 
                                c.s_date,                                 
                                0 AS active, 
                                c.country_id, 
				1 AS operation_type_id,  
                                c.web_address, 
                                c.tax_office, 
				c.tax_no, 
                                c.sgk_sicil_no, 
                                c.ownership_status_id, 
                                c.foundation_year,  
				0 AS act_parent_id, 
                                c.bagkur_sicil_no, 
                                0 AS deleted, 
				c.auth_allow_id,  
                                c.owner_user_id, 
                                c.firm_name_short ,					 
                                c.op_user_id, 		                               
                                l.language_main_code
                            FROM info_firm_user_desc_for_company c
                            LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                            WHERE c.id = " . intval($params['id']) . "
                    ) AS xy  
                    WHERE xy.language_main_code NOT IN 
                        (SELECT 
                            DISTINCT language_code 
                         FROM info_firm_user_desc_for_company cx 
                         WHERE (cx.language_parent_id = " . intval($params['id']) . "
						OR cx.id = " . intval($params['id']) . "
					) AND cx.deleted =0 AND cx.active =0)

                            ");

            //   $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);

            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_firm_user_desc_for_company_id_seq');
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
     * @ text alanları doldurmak için info_firm_user_desc_for_company tablosundan tek kayıt döndürür !! 
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
     * @version v 1.0  25.04.2016
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
                        a.profile_public, 
                        a.f_check, 
                        a.s_date, 
                        a.c_date, 
                        a.operation_type_id,
                        op.operation_name, 
                        a.firm_name, 
                        a.web_address,                     
                        a.tax_office, 
                        a.tax_no, 
                        a.sgk_sicil_no,
			a.bagkur_sicil_no,
			a.ownership_status_id,
                        sd4.description AS owner_ship,
			a.foundation_year,			
			a.act_parent_id,  
                        a.language_code, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                        
                        a.active, 
                        sd3.description AS state_active,  
                        a.deleted,
			sd2.description AS state_deleted, 
                        a.op_user_id,
                        u.username,                    
                        a.auth_allow_id, 
                        sd.description AS auth_alow ,
                        a.cons_allow_id,
                        sd1.description AS cons_allow,
                        a.language_parent_id,
                        a.owner_user_id,
                        u1.name as firm_owner_name,
                        u1.surname as firm_owner_surname,
                        a.firm_name_eng, 
                        a.firm_name_short
                    FROM info_firm_user_desc_for_company a    
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id and  op.language_code = a.language_code  AND op.deleted =0 AND op.active =0
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 13 AND sd.language_code = a.language_code AND a.auth_allow_id = sd.first_group  AND sd.deleted =0 AND sd.active =0
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 14 AND  sd1.language_code = a.language_code AND a.cons_allow_id = sd1.first_group  AND sd1.deleted =0 AND sd1.active =0
                    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a.deleted AND sd2.language_code = a.language_code AND sd2.deleted =0 AND sd2.active =0 
                    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 16 AND sd3.first_group= a.active AND sd3.language_code = a.language_code AND sd3.deleted = 0 AND sd3.active = 0
                    LEFT JOIN sys_specific_definitions sd4 ON sd4.main_group = 1 AND sd4.first_group= a.active AND sd4.language_code = a.language_code AND sd4.deleted = 0 AND sd4.active = 0
                    INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id  
                    LEFT JOIN info_users u1 ON u1.id = a.owner_user_id  
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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 25.04.2016 
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
                $operationTypesValue = $operationTypes->getDeleteOperationId($operationTypeParams);
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
                $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
                if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                    $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
                }
                $sql = " 
                        INSERT INTO info_firm_user_desc_for_company(
                            firm_id, 
                            consultant_id,
                            operation_type_id, 
                            language_id,
                            op_user_id,
                            profile_public,                           
                            act_parent_id,                              
                            verbal1_title, 
                            verbal1,                             
                            verbal1_title_eng, 
                            verbal1_eng,                             
                            consultant_confirm_type_id, 
                            confirm_id,
                            language_parent_id,
                            cons_allow_id,
                            active,
                            deleted
                            )                        
                        SELECT 
                            firm_id, 
                            consultant_id,
                            " . intval($operationIdValue) . " AS operation_type_id,
                            language_id,   
                            " . intval($opUserIdValue) . " AS op_user_id, 
                            profile_public, 
                            act_parent_id,
                            verbal1_title,
                            verbal1,
                            verbal2_title,
                            verbal2,
                            verbal3_title,
                            verbal3,
                            verbal1_title_eng,
                            verbal1_eng,                            
                            consultant_confirm_type_id, 
                            confirm_id,
                            language_parent_id,
                            cons_allow_id,
                            1,
                            1
                        FROM info_firm_user_desc_for_company 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_user_desc_for_company_id_seq');
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
     * @ userin sectiği firmanın sözel kayıtlarını döndürür !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersDescForFirmVerbalNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            }
            $networkKey = "-1";
            if ((isset($params['network_key']) && $params['network_key'] != "")) {
                $networkKey = $params['network_key'];
            }
            $sql = "     
                SELECT * FROM (
                            SELECT  
                                CAST(random()*100-1 AS int) AS ccc,
                                a.user_id,
                                ud.name, 
                                ud.surname,
                                COALESCE(NULLIF(ifux.title, ''), ifu.title_eng) AS title,
                                ifu.title_eng,
                                a.firm_id,  
				COALESCE(NULLIF(ax.verbal1_title, ''), a.verbal1_title_eng) AS verbal1_title,
				a.verbal1_title_eng,
				COALESCE(NULLIF(ax.verbal1, ''), a.verbal1_eng) AS verbal1,
				a.verbal1_eng,
				COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
				COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                                CASE COALESCE(NULLIF(ud.picture, ''),'-')
                                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(CONCAT(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                                        ELSE
                                        CONCAT(ifks.folder_name ,'/',ifks.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture
                            FROM info_firm_user_desc_for_company a  
                            INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2                   
                            INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                    
                            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                            LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . "  AND lx.deleted =0 AND lx.active =0
                            LEFT JOIN info_firm_user_desc_for_company ax ON (ax.id = a.id OR ax.language_parent_id=a.id)  AND ax.active = 0 AND ax.deleted = 0 AND ax.language_id =lx.id AND ax.cons_allow_id =2
                            INNER JOIN info_firm_users ifu ON ifu.user_id  = a.user_id AND ifu.cons_allow_id=2 AND ifu.language_id =lx.id
                            LEFT JOIN info_firm_users ifux ON (ifux.id  = ifu.id OR ifux.language_parent_id = a.id) AND ifux.cons_allow_id=2 AND ifux.language_id =lx.id
                            INNER JOIN info_firm_keys ifks ON  ifks.firm_id =1 
                            WHERE 
                                a.cons_allow_id=2 AND 
                                a.language_parent_id =0 AND				 
                                a.firm_id = 1
                            ORDER BY ccc DESC
                            limit 2   
                        ) AS xtable 
                union 
                        (
                            SELECT 
                                CAST(random()*100-1 AS int) AS ccc,
                                a.user_id,
                                ud.name, 
                                ud.surname, 
                                COALESCE(NULLIF(ifux.title, ''), ifu.title_eng) AS title,
                                ifu.title_eng,
                                a.firm_id,
                                COALESCE(NULLIF(ax.verbal1_title, ''), a.verbal1_title_eng) AS verbal1_title,
                                a.verbal1_title_eng,
                                COALESCE(NULLIF(ax.verbal1, ''), a.verbal1_eng) AS verbal1,
                                a.verbal1_eng,
                                COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                                COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                                CASE COALESCE(NULLIF(ud.picture, ''),'-')
                                    WHEN '-' THEN CONCAT(COALESCE(NULLIF(CONCAT(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                                    ELSE CONCAT(ifk.folder_name ,'/',ifk.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture 
                            FROM info_firm_user_desc_for_company a 
                            INNER JOIN info_users_detail ud ON ud.root_id = a.user_id  AND ud.cons_allow_id = 2
                            INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                        
                            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                            LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                            LEFT JOIN info_firm_user_desc_for_company ax ON (ax.id = a.id OR ax.language_parent_id=a.id)  AND ax.active = 0 AND ax.deleted = 0 AND ax.language_id =lx.id
                            INNER JOIN info_firm_users ifu ON ifu.user_id  = a.user_id AND ifu.cons_allow_id=2 AND ifu.language_id =lx.id
                            LEFT JOIN info_firm_users ifux ON (ifux.id  = ifu.id OR ifux.language_parent_id = a.id) AND ifux.cons_allow_id=2 AND ifux.language_id =lx.id
                            INNER JOIN info_firm_keys ifk ON a.firm_id = ifk.firm_id
                            where
                                a.cons_allow_id = 2  AND 
                                a.language_parent_id =0 AND
                                a.profile_public =0 AND 
                                ifk.network_key = '" . $networkKey . "'
                            ORDER BY ccc DESC
                        ) 
                ORDER BY firm_id DESC
                limit 2                 
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
     * @ userin sectiği firmanın sözel kayıtlarını döndürür !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersDescForFirmVerbalNpkGuest($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            }
            $networkKey = "-1";
            if ((isset($params['network_key']) && $params['network_key'] != "")) {
                $networkKey = $params['network_key'];
            }
            $sql = "     
                SELECT * FROM (
                            SELECT  
                                CAST(random()*100-1 AS int) AS ccc,
                                 a.user_id,
                                ud.name, 
                                ud.surname, 
                                COALESCE(NULLIF(ifux.title, ''), ifu.title_eng) AS title,
                                ifu.title_eng,   
                                a.firm_id,
                                COALESCE(NULLIF(ax.verbal1_title, ''), a.verbal1_title_eng) AS verbal1_title,
                                a.verbal1_title_eng,
                                COALESCE(NULLIF(ax.verbal1, ''), a.verbal1_eng) AS verbal1,
                                a.verbal1_eng,
                                COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                                COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                                CASE COALESCE(NULLIF(ud.picture, ''),'-')
                                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(CONCAT(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                                        ELSE
                                        CONCAT(ifks.folder_name ,'/',ifks.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture
                            FROM info_firm_user_desc_for_company a                              
                            INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2
                            INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                    
                            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                            LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . "  AND lx.deleted =0 AND lx.active =0
                            LEFT JOIN info_firm_user_desc_for_company ax ON (ax.id = a.id OR ax.language_parent_id=a.id) AND ax.language_id =lx.id AND ax.cons_allow_id =2
                            INNER JOIN info_firm_users ifu ON ifu.user_id = a.user_id AND ifu.cons_allow_id = 2
                            LEFT JOIN info_firm_users ifux ON ifux.id = ifu.id AND ifu.cons_allow_id = 2
                            INNER JOIN info_firm_keys ifks ON ifks.firm_id =1 
                            WHERE 
                                a.cons_allow_id=2 AND 
                                a.language_parent_id =0 AND
                                a.firm_id = 1
                            ORDER BY ccc DESC
                            limit 2   
                        ) AS xtable 
                union 
                        (
                            SELECT 
                                CAST(random()*100-1 AS int) AS ccc,
                                a.user_id,
                                ud.name, 
                                ud.surname, 
                                COALESCE(NULLIF(ifux.title, ''), ifu.title_eng) AS title,
                                ifu.title_eng,
                                a.firm_id,
                                COALESCE(NULLIF(ax.verbal1_title, ''), a.verbal1_title_eng) AS verbal1_title,
                                a.verbal1_title_eng,
                                COALESCE(NULLIF(ax.verbal1, ''), a.verbal1_eng) AS verbal1,
                                a.verbal1_eng,
                                COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                                COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                                CASE COALESCE(NULLIF(ud.picture, ''),'-')
                                    WHEN '-' THEN CONCAT(COALESCE(NULLIF(CONCAT(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                                    ELSE CONCAT(ifk.folder_name ,'/',ifk.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture 
                            FROM info_firm_user_desc_for_company a 
                            INNER JOIN info_firm_users ifu ON ifu.user_id = a.user_id AND ifu.cons_allow_id = 2
                            LEFT JOIN info_firm_users ifux ON ifux.id = ifu.id AND ifu.cons_allow_id = 2
                            INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2
                            INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                            LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                            LEFT JOIN info_firm_user_desc_for_company ax ON (ax.id = a.id OR ax.language_parent_id=a.id) AND ax.language_id =lx.id AND ax.cons_allow_id =2
                            LEFT JOIN info_firm_users ifux ON ifux.id = ifu.id AND ifu.cons_allow_id = 2
                            INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.cons_allow_id = 2
                            INNER JOIN info_firm_keys ifk ON a.firm_id = ifk.firm_id
                            where
                                a.cons_allow_id = 2  AND 
                                a.language_parent_id =0 AND
                                a.profile_public =0 AND 
                                ifk.network_key = '" . $networkKey . "'
                            ORDER BY ccc DESC
                        ) 
                ORDER BY firm_id DESC
                limit 2       
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

}
