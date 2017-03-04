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
class InfoFirmWorkingPersonnel extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_working_personnel tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 18.07.2016
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
                UPDATE info_firm_working_personnel
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
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
     * @ info_firm_working_personnel tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  18.07.2016   
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
			a.name, 
			a.surname, 			 
			COALESCE(NULLIF(ax.positions, ''), a.positions_eng) AS positions,
			a.positions_eng,
			COALESCE(NULLIF(ax.title, ''), a.title_eng) AS title,
			a.title_eng,
			a.sex_id,		
			COALESCE(NULLIF(sd3x.description, ''), sd3.description_eng) AS sex_name,
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
                        u.username AS op_user_name,
			a.consultant_id, 
			a.consultant_confirm_type_id, 
			a.confirm_id,
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow
                    FROM info_firm_working_personnel a
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 3 AND sd3.first_group= a.sex_id AND sd3.deleted = 0 AND sd3.active = 0 AND sd3.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd3x ON sd3x.language_id = lx.id AND (sd3x.id = sd3.id OR sd3x.language_parent_id = sd3.id) AND sd3x.deleted = 0 AND sd3x.active = 0
                   
		    ORDER BY a.firm_id,a.name, a.surname
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
     * @ info_firm_working_personnel tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0  18.07.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = " AND a.deleted =0  ";
            if (isset($params['id'])) {
                $addSql .= " AND a.id != " . intval($params['id']);
            }
            $sql = " 
            SELECT  
                a.name AS name , 
                a.name AS value , 
                LOWER(a.name) = LOWER('" . $params['name'] . "') AS control,
                CONCAT(a.name, ' ' , a.surname, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_working_personnel a             
            WHERE a.firm_id = " . intval($params['firm_id']) . " AND
                LOWER(REPLACE(name,' ','')) = LOWER(REPLACE('" . $params['name'] . "',' ','')) AND 
                LOWER(REPLACE(surname,' ','')) = LOWER(REPLACE('" . $params['surname'] . "',' ','')) AND                     
                a.active = 0 AND
                a.deleted = 0     
                " . $addSql . "                  
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
     * @ info_firm_working_personnel tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  18.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_firm_working_personnel
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
     * @ info_firm_working_personnel tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18.07.2016
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
                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmId = $getFirm ['resultSet'][0]['firm_id'];
                    $kontrol = $this->haveRecords(array('firm_id' => $getFirmId, 'name' => $params['name'], 'surname' => $params['surname']));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
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
                            $profilePublic = $params['profile_public'];
                        }
                        $SexId = 0;
                        if ((isset($params['sex_id']) && $params['sex_id'] != "")) {
                            $SexId = $params['sex_id'];
                        }

                        $sql = " 
                        INSERT INTO info_firm_working_personnel(
                            firm_id, 
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id,                            
                            title, 
                            title_eng, 
                            name, 
                            surname, 
                            positions, 
                            positions_eng,
                            sex_id
                            )
                        VALUES (
                            " . intval($getFirmId) . ",
                            " . intval($ConsultantId) . ",
                            " . intval($operationIdValue) . ",                       
                            " . intval($languageIdValue) . ",
                            " . intval($opUserIdValue) . ",
                            " . intval($profilePublic) . ",                            
                            (SELECT last_value FROM info_firm_working_personnel_id_seq),                           
                            '" . $params['title'] . "', 
                            '" . $params['title_eng'] . "', 
                            '" . $params['name'] . "', 
                            '" . $params['surname'] . "', 
                            '" . $params['positions'] . "', 
                            '" . $params['positions_eng'] . "', 
                            " . intval($SexId) . "
                             )";
                        $statement = $pdo->prepare($sql);
                        //echo debugPDO($sql, $params);
                        $result = $statement->execute();
                        $insertID = $pdo->lastInsertId('info_firm_working_personnel_id_seq');
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
                        // 23505  unique_violation
                        $errorInfo = '23505';
                        $errorInfoColumn = 'name,surname';
                        $pdo->rollback();
                        // $result = $kontrol;
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                    }
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'cpk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
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
     * info_firm_working_personnel tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  18.07.2016
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
                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmId = $getFirm ['resultSet'][0]['firm_id'];
                    $kontrol = $this->haveRecords(array('id' => $params['id'],'firm_id' => $getFirmId, 'name' => $params['name'], 'surname' => $params['surname']));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
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
                            $profilePublic = $params['profile_public'];
                        }
                        $SexId = 0;
                        if ((isset($params['sex_id']) && $params['sex_id'] != "")) {
                            $SexId = $params['sex_id'];
                        }

                        $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_working_personnel(
                            firm_id, 
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id,
                            title, 
                            title_eng, 
                            name, 
                            surname, 
                            positions, 
                            positions_eng,
                            sex_id                          
                        )
                        SELECT  
                            firm_id,
                            consultant_id, 
                            " . intval($operationIdValue) . ",
                            " . intval($languageIdValue) . ",
                            " . intval($opUserIdValue) . ",
                            " . intval($profilePublic) . ",  
                            act_parent_id,
                            '" . $params['title'] . "', 
                            '" . $params['title_eng'] . "', 
                            '" . $params['name'] . "', 
                            '" . $params['surname'] . "', 
                            '" . $params['positions'] . "', 
                            '" . $params['positions_eng'] . "', 
                            " . intval($SexId) . "
                        FROM info_firm_working_personnel 
                        WHERE id =  " . intval($params['id']) . " 
                        ");
                        $insert_act_insert = $statement_act_insert->execute();
                        $insertID = $pdo->lastInsertId('info_firm_working_personnel_id_seq');
                        $affectedRows = $statement_act_insert->rowCount();
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
                        // 23505  unique_violation
                        $errorInfo = '23505';
                        $errorInfoColumn = 'name, surname';
                        $pdo->rollback();
                        $result = $kontrol;
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
                    }
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'cpk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
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
     * @ Gridi doldurmak için info_firm_working_personnel tablosundan kayıtları döndürür !!
     * @version v 1.0  18.07.2016
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
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "a.firm_id,a.name, a.surname";
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
			a.name, 
			a.surname, 			 
			COALESCE(NULLIF(ax.positions, ''), a.positions_eng) AS positions,
			a.positions_eng,
			COALESCE(NULLIF(ax.title, ''), a.title_eng) AS title,
			a.title_eng,
			a.sex_id,		
			COALESCE(NULLIF(sd3x.description, ''), sd3.description_eng) AS sex_name,
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
                        u.username AS op_user_name,
			a.consultant_id, 
			a.consultant_confirm_type_id, 
			a.confirm_id,
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow
                    FROM info_firm_working_personnel a
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 3 AND sd3.first_group= a.sex_id AND sd3.deleted = 0 AND sd3.active = 0 AND sd3.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd3x ON sd3x.language_id = lx.id AND (sd3x.id = sd3.id OR sd3x.language_parent_id = sd3.id) AND sd3x.deleted = 0 AND sd3x.active = 0
                    WHERE a.deletd =0 AND a.language_parent_id =0 
		 
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
     * @ Gridi doldurmak için info_firm_working_personnel tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $whereSQL = " WHERE a.language_parent_id = 0 AND a.deleted =0 "; 

            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT                 
                FROM info_firm_working_personnel a
                INNER JOIN info_users u ON u.id = a.op_user_id
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0                
                INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
                INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 3 AND sd3.first_group= a.sex_id AND sd3.deleted = 0 AND sd3.active = 0 AND sd3.language_parent_id =0                		   
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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 18.07.2016 
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
                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));                            
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    //$getFirmId = $getFirm ['resultSet'][0]['firm_id'];
                   
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
                    $sql = "                
                  INSERT INTO info_firm_working_personnel(
                            firm_id, 
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id,  
                            title, 
                            title_eng, 
                            name, 
                            surname, 
                            positions, 
                            positions_eng,
                            sex_id, 
                            language_parent_id,
                            active,
                            deleted 
                        )
                        SELECT  
                            firm_id,
                            consultant_id,                            
                            " . intval($operationIdValue) . ",    
                            language_id,    
                            " . intval($opUserIdValue) . ",
                            profile_public,
                            act_parent_id,
                           
                            title, 
                            title_eng, 
                            name, 
                            surname, 
                            positions, 
                            positions_eng,
                            sex_id, 
                            language_parent_id,
                            1,
                            1                            
                        FROM info_firm_working_personnel 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                    $statement_act_insert = $pdo->prepare($sql);
                    //  echo debugPDO($sql, $params);
                    $insert_act_insert = $statement_act_insert->execute();
                    $affectedRows = $statement_act_insert->rowCount();
                    $insertID = $pdo->lastInsertId('info_firm_working_personnel_id_seq');
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
                    $errorInfoColumn = 'cpk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
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
     * @ npk lı firmanın danısman tarafından onaylanmış kayıtlarını döndürür !!
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');         
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                // $opUserIdValue = $userId ['resultSet'][0]['user_id'];               
                $firmIdValue = NULL;              
                $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));                     
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
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
                    SELECT 
                        a.id,
                        a.firm_id,
			a.name, 
			a.surname,
			COALESCE(NULLIF(ax.title, ''), a.title_eng) AS title,
			a.title_eng,
                        COALESCE(NULLIF(ax.positions, ''), a.positions_eng) AS positions,
			a.positions_eng,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        ifk.network_key
                    FROM info_firm_working_personnel a
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id 
                    LEFT JOIN info_firm_working_personnel ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0
		    WHERE 
                        a.firm_id = " . intval($firmIdValue) . " AND
                        a.cons_allow_id =2 AND 
			a.profile_public=0 
		    ORDER BY a.name,a.surname
                ";
                    $statement = $pdo->prepare($sql);
                  //  echo debugPDO($sql, $params);
                    $statement->execute();
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'npk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
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
     * @ npk lı firmanın danısman tarafından onaylanmış kayıtların sayısını döndürür !!
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalNpkRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                // $opUserIdValue = $userId ['resultSet'][0]['user_id'];               
                $firmIdValue = NULL;
                $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                    $sql = " 
                    SELECT 
                        COUNT(a.id) AS count
                    FROM info_firm_working_personnel a
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id                     
		   WHERE 
                        a.firm_id = " . intval($firmIdValue) . " AND
                        a.cons_allow_id =2 AND                         
			a.profile_public=0 		  	                              
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
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'npk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
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
     * @ quest için npk lı firmanın danısman tarafından onaylanmış kayıtlarını döndürür !!
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalNpkQuest($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $firmIdValue = NULL;
            $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
            if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
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
                    SELECT 
                        a.id,
                        a.firm_id,
			a.name, 
			a.surname,
			COALESCE(NULLIF(ax.title, ''), a.title_eng) AS title,
			a.title_eng,
                        COALESCE(NULLIF(ax.positions, ''), a.positions_eng) AS positions,
			a.positions_eng,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name
                    FROM info_firm_working_personnel a
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id 
                    LEFT JOIN info_firm_working_personnel ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0
		    WHERE 
                        a.firm_id = " . intval($firmIdValue) . " AND
                        a.cons_allow_id =2 AND 
			a.profile_public=0 
		    ORDER BY a.name,a.surname		                    
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
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'npk';
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**      
     * @author Okan CIRAN
     * @ Quest için npk lı firmanın danısman tarafından onaylanmış kayıtların sayısını döndürür !!
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalNpkQuestRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $firmIdValue = NULL;
            $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
            if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                $sql = " 
                    SELECT 
                        COUNT(a.id) AS count 
                    FROM info_firm_working_personnel a                     
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id                     
		    WHERE
                        a.firm_id = " . intval($firmIdValue) . " AND
                        a.cons_allow_id =2 AND                         
			a.profile_public=0 		    		                              
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
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'npk';
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
 
    /**
     * @author Okan CIRAN
     * @ info_firm_working_personnel bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalListGrid($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            if (isset($params['page']) && $params['page'] != "" && isset($params['rows']) && $params['rows'] != "") {
                $offset = ((intval($params['page']) - 1) * intval($params['rows']));
                $limit = intval($params['rows']);
            } else {
                $limit = 10;
                $offset = 0;
            }

            $sortArr = array();
            $orderArr = array();
            if (isset($params['sort']) && $params['sort'] != "") {
                $sort = trim($params['sort']);
                $sortArr = explode(",", $sort);
                if (count($sortArr) === 1)
                    $sort = trim($params['sort']);
            } else {
                $sort = "firm_id,name,surname";
            }

            if (isset($params['order']) && $params['order'] != "") {
                $order = trim($params['order']);
                $orderArr = explode(",", $order);
                //print_r($orderArr);
                if (count($orderArr) === 1)
                    $order = trim($params['order']);
            } else {
                $order = "ASC";
            }

            $sorguStr = null;
            if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);

                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name" . $sorguExpression . ' ';

                                break;
                            case 'surname':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND surname" . $sorguExpression . ' ';

                                break;
                            case 'positions':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND positions" . $sorguExpression . ' ';

                                break;     
                            case 'positions_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND positions_eng" . $sorguExpression . ' ';
                            
                                break;  
                            case 'title':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND title" . $sorguExpression . ' ';
                            
                                break;  
                            case 'title_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND title_eng" . $sorguExpression . ' ';
                            
                                break; 
                            case 'sex_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sex_name" . $sorguExpression . ' ';
                            
                                break; 
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';
                            
                                break;
                            case 'state_profile_public':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND state_profile_public" . $sorguExpression . ' ';
                            
                                break; 
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                if (isset($params['name']) && $params['name'] != "") {
                    $sorguStr .= " AND a.name Like '%" . $params['name'] . "%'";
                }
                if (isset($params['surname']) && $params['surname'] != "") {
                    $sorguStr .= " AND surname Like '%" . $params['surname'] . "%'";
                }
                if (isset($params['positions']) && $params['positions'] != "") {
                    $sorguStr .= " AND positions Like '%" . $params['positions'] . "%'";
                }
                if (isset($params['positions_eng']) && $params['positions_eng'] != "") {
                    $sorguStr .= " AND positions_eng Like '%" . $params['positions_eng'] . "%'";
                }  
                if (isset($params['title']) && $params['title'] != "") {
                    $sorguStr .= " AND title Like '%" . $params['title'] . "%'";
                }  
                if (isset($params['title_eng']) && $params['title_eng'] != "") {
                    $sorguStr .= " AND title_eng Like '%" . $params['title_eng'] . "%'";
                }
                if (isset($params['sex_id']) && $params['sex_id'] != "") {
                    $sorguStr .= " AND sex_id = " . intval($params['sex_id']) ;
                }
                if (isset($params['profile_public']) && $params['profile_public'] != "") {
                    $sorguStr .= " AND profile_public = " . intval($params['profile_public']) ;
                }
                if (isset($params['active']) && $params['active'] != "") {
                    $sorguStr .= " AND active = " . intval($params['active']) ;
                }
                if (isset($params['op_user_name']) && $params['op_user_name'] != "") {
                    $sorguStr .= " AND op_user_name Like '%" . $params['op_user_name'] . "%'";
                }            
                
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            
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
                SELECT   
			id,
                        firm_id,				
			name, 
			surname, 			 
			positions,
			positions_eng,
			title,
			title_eng,
			sex_id,		
			sex_name,			
                        profile_public,
                        state_profile_public,                        
			act_parent_id,
                        language_id,
		        language_name,
                        active,
                        state_active,
                        deleted,
			state_deleted,
			op_user_id,
                        op_user_name,
			consultant_id, 
			consultant_confirm_type_id, 
			confirm_id,
                        cons_allow_id,
                        cons_allow
                         
                FROM (
                   SELECT 
                        a.id,
                        a.firm_id,				
			a.name, 
			a.surname, 			 
			COALESCE(NULLIF(ax.positions, ''), a.positions_eng) AS positions,
			a.positions_eng,
			COALESCE(NULLIF(ax.title, ''), a.title_eng) AS title,
			a.title_eng,
			a.sex_id,		
			COALESCE(NULLIF(sd3x.description, ''), sd3.description_eng) AS sex_name,
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
                        u.username AS op_user_name,
			a.consultant_id, 
			a.consultant_confirm_type_id, 
			a.confirm_id,
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow
                    FROM info_firm_working_personnel a
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 3 AND sd3.first_group= a.sex_id AND sd3.deleted = 0 AND sd3.active = 0 AND sd3.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd3x ON sd3x.language_id = lx.id AND (sd3x.id = sd3.id OR sd3x.language_parent_id = sd3.id) AND sd3x.deleted = 0 AND sd3x.active = 0
                    WHERE a.deleted =0 AND a.c_date IS NULL AND a.language_parent_id =0
                    ) AS xtable WHERE deleted =0 
                ".$sorguStr."
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
     * @ info_firm_working_personnel bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  18.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalListGridRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $sorguStr = null;
            if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);

                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name" . $sorguExpression . ' ';

                                break;
                            case 'surname':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND surname" . $sorguExpression . ' ';

                                break;
                            case 'positions':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND positions" . $sorguExpression . ' ';

                                break;     
                            case 'positions_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND positions_eng" . $sorguExpression . ' ';
                            
                                break;  
                            case 'title':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND title" . $sorguExpression . ' ';
                            
                                break;  
                            case 'title_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND title_eng" . $sorguExpression . ' ';
                            
                                break; 
                            case 'sex_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sex_name" . $sorguExpression . ' ';
                            
                                break; 
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';
                            
                                break;
                            case 'state_profile_public':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND state_profile_public" . $sorguExpression . ' ';
                            
                                break; 
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                if (isset($params['name']) && $params['name'] != "") {
                    $sorguStr .= " AND a.name Like '%" . $params['name'] . "%'";
                }
                if (isset($params['surname']) && $params['surname'] != "") {
                    $sorguStr .= " AND surname Like '%" . $params['surname'] . "%'";
                }
                if (isset($params['positions']) && $params['positions'] != "") {
                    $sorguStr .= " AND positions Like '%" . $params['positions'] . "%'";
                }
                if (isset($params['positions_eng']) && $params['positions_eng'] != "") {
                    $sorguStr .= " AND positions_eng Like '%" . $params['positions_eng'] . "%'";
                }  
                if (isset($params['title']) && $params['title'] != "") {
                    $sorguStr .= " AND title Like '%" . $params['title'] . "%'";
                }  
                if (isset($params['title_eng']) && $params['title_eng'] != "") {
                    $sorguStr .= " AND title_eng Like '%" . $params['title_eng'] . "%'";
                }
                if (isset($params['sex_id']) && $params['sex_id'] != "") {
                    $sorguStr .= " AND sex_id = " . intval($params['sex_id']) ;
                }
                if (isset($params['profile_public']) && $params['profile_public'] != "") {
                    $sorguStr .= " AND profile_public = " . intval($params['profile_public']) ;
                }
                if (isset($params['active']) && $params['active'] != "") {
                    $sorguStr .= " AND active = " . intval($params['active']) ;
                }
                if (isset($params['op_user_name']) && $params['op_user_name'] != "") {
                    $sorguStr .= " AND op_user_name Like '%" . $params['op_user_name'] . "%'";
                }         
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            
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
                SELECT COUNT(id) AS count 
                FROM (               
                   SELECT 
                        a.id,
                        a.firm_id,				
			a.name, 
			a.surname, 			 
			COALESCE(NULLIF(ax.positions, ''), a.positions_eng) AS positions,
			a.positions_eng,
			COALESCE(NULLIF(ax.title, ''), a.title_eng) AS title,
			a.title_eng,
			a.sex_id,		
			COALESCE(NULLIF(sd3x.description, ''), sd3.description_eng) AS sex_name,
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
                        u.username AS op_user_name,
			a.consultant_id, 
			a.consultant_confirm_type_id, 
			a.confirm_id,
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow
                    FROM info_firm_working_personnel a
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 3 AND sd3.first_group= a.sex_id AND sd3.deleted = 0 AND sd3.active = 0 AND sd3.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd3x ON sd3x.language_id = lx.id AND (sd3x.id = sd3.id OR sd3x.language_parent_id = sd3.id) AND sd3x.deleted = 0 AND sd3x.active = 0
                    WHERE a.deleted =0 AND a.c_date IS NULL AND a.language_parent_id =0
                    ) AS xtable WHERE deleted =0  
                        ".$sorguStr."  
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
