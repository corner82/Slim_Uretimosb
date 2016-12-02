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
class InfoFirmProfile extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_profile tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  06.01.2016
     * @param array | null $args
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
                UPDATE info_firm_profile
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $userIdValue . "     
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
     * @ info_firm_profile tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  06.01.2016   
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
            $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                 $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
            }
         
            $statement = $pdo->prepare("
                SELECT 
                    a.id, 
                    a.profile_public, 
                    a.f_check, 
                    a.s_date, 
                    a.c_date, 
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_names,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name, ''), a.firm_name_eng), ''), a.firm_name) AS firm_names, 
                    a.web_address,
                    a.tax_office, 
                    a.tax_no, 
                    a.sgk_sicil_no,
                    a.ownership_status_id,
                    COALESCE(NULLIF(sd1x.description, ''), sd1.description_eng) AS owner_ships,
                    a.foundation_year,
                    a.act_parent_id,
                    a.language_code,
                    a.language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_names,
                    a.active,
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_actives,
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleteds,
                    a.op_user_id,
                    u.username,
                    a.auth_allow_id,
                    COALESCE(NULLIF(sd13x.description, ''), sd13.description_eng) AS auth_alows,
                    a.cons_allow_id,
                    COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allows, 
                    a.language_parent_id,
                    a.firm_name_short,
                    a.country_id,
		    COALESCE(NULLIF(cox.name, ''), co.name_eng) AS country_names,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.description, ''), a.description_eng), ''), a.description) AS descriptions,
                    a.duns_number,
                    ifk.network_key,
		    CASE COALESCE(NULLIF(a.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png')) END AS logo,
                    a.place_point
                FROM info_firm_profile a  
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0  
                LEFT JOIN info_firm_keys ifk ON ifk.firm_id = a.act_parent_id
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0 

                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = a.language_id AND op.deleted =0 AND op.active =0
		LEFT JOIN sys_operation_types opx ON opx.id = op.id AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0

                INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND sd13.language_id = a.language_id AND a.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = a.language_id AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_specific_definitions sd1 ON sd1.main_group = 1 AND sd1.first_group= a.ownership_status_id AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
                
                LEFT JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id  
                
                LEFT JOIN sys_countrys cox ON (cox.id = a.country_id OR cox.language_parent_id = a.country_id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id                		

                LEFT JOIN sys_specific_definitions sd13x ON (sd13x.id = sd13.id OR sd13x.language_parent_id = sd13.id) AND sd13x.language_id =lx.id  AND sd13x.deleted =0 AND sd13x.active =0 
                LEFT JOIN sys_specific_definitions sd14x ON (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.language_id = lx.id  AND sd14x.deleted = 0 AND sd14x.active = 0                
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0                
		LEFT JOIN sys_specific_definitions sd1x ON (sd1x.id = sd1.id OR sd1x.language_parent_id = sd1.id) AND sd1x.language_id = lx.id  AND sd1x.deleted = 0 AND sd1x.active = 0                
		LEFT JOIN info_firm_profile ax ON (ax.act_parent_id = a.act_parent_id OR ax.language_parent_id = a.act_parent_id) AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0                 
 
                ORDER BY a.firm_name   
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
     * @ info_firm_profile tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 15.01.2016
     * @param array | null $args
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
                firm_name AS name , 
                '" . $params['firm_name'] . "' AS value , 
                firm_name ='" . $params['firm_name'] . "' AS control,
                CONCAT(firm_name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_profile                
            WHERE 
                LOWER(REPLACE(firm_name,' ','')) = LOWER(REPLACE('" . $params['firm_name'] . "',' ',''))
                ". $addSql . " 
               AND deleted =0   
               AND active=0 
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
     * @ info_firm_profile tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
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
                UPDATE info_firm_profile
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
     * @ info_firm_profile tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  06.01.2016
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
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {                 
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

                    $foundationYear = NULL;
                    if ((isset($params['foundation_year']) && $params['foundation_year'] != "")) {
                        $foundationYear = $params['foundation_year'];
                    }
                    $countryId = 91;
                    if ((isset($params['country_id']) && $params['country_id'] != "")) {
                        $countryId = $params['country_id'];
                    }
                    $profilePublic = 0;
                    if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                        $profilePublic = intval($params['profile_public']);
                    }       
                    $statement = $pdo->prepare("
                   INSERT INTO info_firm_profile(
                        profile_public, 
                        country_id,
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no, 
                        ownership_status_id,                         
                        language_id,
                        consultant_id, 
                        operation_type_id,
                        op_user_id,  
                        foundation_year, 
                        firm_name_eng, 
                        firm_name_short,
                        act_parent_id,
                        description,
                        description_eng,
                        duns_number,
                        logo                     
                        )
                VALUES (
                        ". intval($profilePublic).",
                        ". intval($countryId).",
                        :firm_name, 
                        :web_address, 
                        :tax_office, 
                        :tax_no, 
                        :sgk_sicil_no, 
                        :ownership_status_id,                         
                        ". intval($languageIdValue).",
                        ". intval($ConsultantId).",
                        ". intval($operationIdValue).",    
                        ". intval($opUserIdValue).",
                        ". intval($foundationYear).",
                        :firm_name_eng, 
                        :firm_name_short,
                        (SELECT last_value FROM info_firm_profile_id_seq),                       
                        :description,
                        :description_eng,
                        :duns_number,
                        :logo                       
                                                ");                                        
                    $statement->bindValue(':firm_name', $params['firm_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':web_address', $params['web_address'], \PDO::PARAM_STR);
                    $statement->bindValue(':tax_office', $params['tax_office'], \PDO::PARAM_STR);
                    $statement->bindValue(':tax_no', $params['tax_no'], \PDO::PARAM_STR);
                    $statement->bindValue(':sgk_sicil_no', $params['sgk_sicil_no'], \PDO::PARAM_STR);
                    $statement->bindValue(':ownership_status_id', $params['ownership_status_id'], \PDO::PARAM_INT);                                                          
                    $statement->bindValue(':firm_name_eng', $params['firm_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':firm_name_short', $params['firm_name_short'], \PDO::PARAM_STR);
                    $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                    $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':duns_number', $params['duns_number'], \PDO::PARAM_STR);
                    $statement->bindValue(':logo', $params['logo'], \PDO::PARAM_STR);                    
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                                
                    InfoFirmKeys::insert(array('firm_id' => $insertID, 
                                              'country_id' => $params['country_id']));
                   
                    $this ->insertCompanyUser(array('firm_id' => $insertID, 
                                                'language_id' => $languageIdValue, 
                                                'consultant_id'=> $ConsultantId,  
                                                'user_id'=> $opUserIdValue, 
                                                'op_user_id'=> $opUserIdValue,   
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
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'firm_name';
                    $pdo->rollback();
                    // $result = $kontrol;
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
     * @ danışman tarafından - info_firm_profile tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  22.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function insertConsAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {               
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
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
                        if ($operationIdValue > 0) {
                            $url = null;
                        }
                    }  
                    $countryId = 91;
                    $languageIdValue = 647;
                    $ConsultantId = $opUserIdValue;
                    $profilePublic = 0;
                    $sql = "
                   INSERT INTO info_firm_profile(
                        profile_public,                         
                        firm_name, 
                        firm_name_eng, 
                        firm_name_short,
                        firm_name_short_eng,
                        country_id,
                        
                        language_id,
                        consultant_id, 
                        operation_type_id,
                        op_user_id,
                        act_parent_id,
                        cons_allow_id
                        )
                VALUES (
                        " . intval($profilePublic) . ",
                        '" . $params['firm_name'] . "' ,
                        '" . $params['firm_name_eng'] . "' ,
                        '" . $params['firm_name_short'] . "' ,
                        '" . $params['firm_name_short_eng'] . "' ,
                        " . intval($countryId) . ",
                            
                        " . intval($languageIdValue) . ",
                        " . intval($ConsultantId) . ",
                        " . intval($operationIdValue) . ",    
                        " . intval($opUserIdValue) . ",
                        (SELECT last_value FROM info_firm_profile_id_seq),
                        2
                        )";
                    $statement = $pdo->prepare($sql);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    InfoFirmKeys::insert(array('firm_id' => $insertID,
                        'country_id' => $countryId));

                    $xc = $this->insertCompanyClusters(array('firm_id' => $insertID,
                        'op_user_id' => $opUserIdValue,
                        'clusters_id' => $params['clusters_id'],
                    ));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);

                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'firm_name';
                    $pdo->rollback();
                    // $result = $kontrol;
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
     * @ danısşman tarafından info_firm_clusters tablosuna onaylanmış yeni kayıt oluşturur.  !!
     * @version v 1.0  25.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
    */
    public function insertCompanyClusters($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "0";
            $firmId = 0;
            if ((isset($params['firm_id']) && $params['firm_id'] != "")) {
                $firmId = $params['firm_id'];
                $addSql = " " . $firmId . " ";
            }
            $Id = 0;
            if ((isset($params['id']) && $params['id'] != "")) {
                $Id = $params['id'];
                $addSql = " (SELECT act_parent_id FROM info_firm_profile WHERE id = " . intval($Id) . ") ";
            }
            $sql = "              
                    INSERT INTO info_firm_clusters (
                        firm_id, 
                        osb_cluster_id,
                        op_user_id,
                        cons_allow_id,
                        act_parent_id
                        )
                        SELECT 
                            " . $addSql . ",
                            cast(id AS integer) AS cluster_id,
                            " . intval($params['op_user_id']) . ",
                            2,
                            (SELECT last_value FROM info_firm_clusters_id_seq) +  (row_number() over())
                        FROM sys_osb_clusters 
                        WHERE 
                            id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['clusters_id'] . "'))                  
                        ";
            $statement = $pdo->prepare($sql);          
          // echo debugPDO($sql, $params);
            $result = $statement->execute();          
            //$insertID = $pdo->lastInsertId('info_firm_clusters_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //$pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo,); // "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**   
     * @author Okan CIRAN
     * @ danısşman tarafından info_firm_clusters tablosundan kayıtları siler.  !!
     * @version v 1.0  25.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
    */
    public function deleteCompanyClusters($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');   
            $addSql ="0"; 
            $firmId = 0;
            if ((isset($params['firm_id']) && $params['firm_id'] != "")) {
                $firmId = $params['firm_id'];
                $addSql =" ".intval($firmId)." " ; 
            }
            $Id = 0;
            if ((isset($params['id']) && $params['id'] != "")) {
                $Id = $params['id'];
                $addSql ="(SELECT act_parent_id FROM info_firm_profile WHERE id = ".intval($Id).") " ; 
            }            
            $sql = "
            UPDATE info_firm_clusters
            SET active =1, 
                deleted =1,
                cons_allow_id =1, 
                op_user_id = " . intval( $params['op_user_id']) . "
            WHERE 
                firm_id = " .$addSql . "  AND 
                cons_allow_id =2 
                ";
                $statement = $pdo->prepare($sql);
              //echo debugPDO($sql, $params);
                $result = $statement->execute();
             //   $insertID = $pdo->lastInsertId('info_firm_clusters_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //$pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo,);// "lastInsertId" => $insertID);           
        } catch (\PDOException $e /* Exception $e */) {
          //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * info_firm_profile tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  06.01.2016
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
                /// update yapan kullanıcı bu firmanın elemanımı ? 
                $checkFirmUser = InfoFirmProfile::getCheckIsThisFirmRegisteredUser(array('op_user_id' => $opUserIdValue, 'cpk' => $params['cpk']));
                if (\Utill\Dal\Helper::haveRecord($checkFirmUser)) {
                    $kontrol = $this->haveRecords($params);
                    if (\Utill\Dal\Helper::haveRecord($kontrol)) {
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
                        $active = 0;
                        if ((isset($params['active']) && $params['active'] != "")) {
                            $active = intval($params['active']);
                        }
                        $countryId = 91;
                        if ((isset($params['country_id']) && $params['country_id'] != "")) {
                            $countryId = intval($params['country_id']);
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
                        
                        $foundationYearx =NULL;
                        if ((isset($params['foundation_yearx']) && $params['foundation_yearx'] != "")) {
                            $foundationYearx = $params['foundation_yearx'];
                        }

                        $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_profile(
                        profile_public, 
                        operation_type_id, 
                        active,
                        consultant_id,
                        op_user_id,                     
                        country_id,                        
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no,                             
                        foundation_yearx,     
                        firm_name_eng, 
                        firm_name_short,
                        firm_name_short_eng,
                        act_parent_id, 
                        auth_allow_id,
                        language_id,
                        description,
                        description_eng,
                        duns_number,
                        logo
                        )
                        SELECT  
                            " . intval($params['profile_public']) . " AS profile_public, 
                            " . intval($operationIdValue) . " AS operation_type_id,
                            " . intval($active) . " AS active,
                            consultant_id,
                            " . intval($opUserIdValue) . " AS op_user_id,                            
                            " . intval($countryId) . " AS country_id,                             
                            '" . $params['firm_name'] . "' AS firm_name, 
                            '" . $params['web_address'] . "' AS web_address, 
                            '" . $params['tax_office'] . "' AS tax_office, 
                            '" . $params['tax_no'] . "' AS tax_no, 
                            '" . $params['sgk_sicil_no'] . "' AS sgk_sicil_no,                                                         
                            " . intval($foundationYearx) . " AS foundation_yearx,
                            '" . $params['firm_name_eng'] . "' AS firm_name_eng, 
                            '" . $params['firm_name_short'] . "' AS firm_name_short,
                            '" . $params['firm_name_short_eng'] . "' AS firm_name_short_eng,
                            act_parent_id,  
                            auth_allow_id,
                            " . intval($languageIdValue) . " AS language_id,
                            '" . $params['description'] . "' AS description, 
                            '" . $params['description_eng'] . "' AS description_eng, 
                            '" . $params['duns_number'] . "' AS duns_number,
                            logo       
                        FROM info_firm_profile 
                        WHERE id =  " . intval($params['id']) . " 
                        ");
                        $insert_act_insert = $statement_act_insert->execute();
                        $affectedRows = $statement_act_insert->rowCount();
                        $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
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
                        $errorInfoColumn = 'id';
                        $pdo->rollback();
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                    }
                } else {
                    $errorInfo = '23502'; // 23502 not_null_violation
                    $errorInfoColumn = 'cpk pk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';   // 23502 not_null_violation
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
     * danısman tarafından - info_firm_profile tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  22.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function updateConsAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {                
                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {                     
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
                    $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_profile(
                        profile_public, 
                        operation_type_id, 
                        active,
                        consultant_id,
                        op_user_id,                     
                        country_id,                        
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no,                             
                        foundation_yearx,     
                        firm_name_eng, 
                        firm_name_short,
                        firm_name_short_eng,
                        act_parent_id, 
                        auth_allow_id,
                        language_id,
                        description,
                        description_eng,
                        duns_number,
                        logo,
                        cons_allow_id
                        )
                        SELECT  
                            profile_public, 
                            " . intval($operationIdValue) . " AS operation_type_id,
                            active,
                            consultant_id,
                            " . intval($opUserIdValue) . " AS op_user_id,                            
                            country_id,                             
                            '" . $params['firm_name'] . "' AS firm_name, 
                            web_address, 
                            tax_office, 
                            tax_no, 
                            sgk_sicil_no,                                                         
                            foundation_yearx,
                            '" . $params['firm_name_eng'] . "' AS firm_name_eng, 
                            '" . $params['firm_name_short'] . "' AS firm_name_short,
                            '" . $params['firm_name_short_eng'] . "' AS firm_name_short_eng,
                            act_parent_id,  
                            auth_allow_id,
                            " . intval($languageIdValue) . " AS language_id,
                            description, 
                            description_eng, 
                            duns_number,
                            logo,
                            2
                        FROM info_firm_profile 
                        WHERE id =  " . intval($params['id']) . " 
                        ");
                    $insert_act_insert = $statement_act_insert->execute();
                    $affectedRows = $statement_act_insert->rowCount();
                    $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
                    $errorInfo = $statement_act_insert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $this->makePassive(array('id' => $params['id']));
                    $this->makeConsAllowZero(array('id' => $params['id']));                          
                            
                    $xc = $this->deleteCompanyClusters(array('id' => $params['id'],                     
                                                'op_user_id' => $opUserIdValue,
                                                'clusters_id' => $params['clusters_id'],
                    ));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);       
                    
                    if ((isset($params['clusters_id']) && $params['clusters_id'] != "")) { 
                        $xc = $this->insertCompanyClusters(array('id' => $params['id'],                  
                                                    'op_user_id' => $opUserIdValue,
                                                    'clusters_id' => $params['clusters_id'],
                        ));
                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);  
                    }
                            
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'firm_name';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';   // 23502 not_null_violation
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
     * @ info_firm_machine_tool tablosundan parametre olarak  gelen id kaydını danısman onayını kaldırır. !!
     * @version v 1.0  19.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeConsAllowZero($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
            $statement = $pdo->prepare(" 
                UPDATE info_firm_profile
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone) ,                     
                    cons_allow_id = 1                    
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
     * info_firm_profile tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * bu fonksiyon sadece dal içerisinden cağırılıcaktır. $pdo lar bu yuzden kapalı. 
     * @version v 1.0  20.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function updateVerbal($params = array()) {
        try {   // burası  değişecek.  buyuk  ihtimal  yeni servis yazılacak o yuzden ellemiyorum Oki.. 
              // burası  değişecek.  buyuk  ihtimal  yeni servis yazılacak o yuzden ellemiyorum Oki.. 
              // burası  değişecek.  buyuk  ihtimal  yeni servis yazılacak o yuzden ellemiyorum Oki.. 
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //    $pdo->beginTransaction(); 
            $endOfIdValue = -1;            
       
            $endOfId = InfoFirmProfile::getFirmEndOfId(array('firm_id' => $params['firm_id']));
            if (\Utill\Dal\Helper::haveRecord($endOfId)) {
                $endOfIdValue = $endOfId ['resultSet'][0]['firm_id'];
            }
            InfoFirmProfile::makePassive(array('id' => $endOfIdValue));
             
            $operationIdValue = -2;
            $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                            array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 23, 'type_id' => 2,));
            if (\Utill\Dal\Helper::haveRecord($operationId)) {
                $operationIdValue = $operationId ['resultSet'][0]['id'];
            }
            $opUserIdValue = 0;
            if ((isset($params['op_user_id']) && $params['op_user_id'] != "")) {
                $opUserIdValue = $params['op_user_id'];
            }
            $countryId = 91;
            if ((isset($params['country_id']) && $params['country_id'] != "")) {
                $countryId = $params['country_id'];
            }

            $active = 0;
            if ((isset($params['active']) && $params['active'] != "")) {
                $countryId = $params['active'];
            }

            $languageIdValue = 647;
            if ((isset($params['language_id']) && $params['language_id'] != "")) {
                $languageIdValue = $params['language_id'];
            }

            $foundationYearx = NULL;
            if ((isset($params['foundation_yearx']) && $params['foundation_yearx'] != "")) {
                $foundationYearx = $params['foundation_yearx'];              
            }

            $sql = " 
                 INSERT INTO info_firm_profile(
                        profile_public, 
                        operation_type_id, 
                        active,
                        consultant_id,
                        op_user_id,
                        country_id,
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no,
                        foundation_yearx, 
                        firm_name_eng, 
                        firm_name_short,
                        firm_name_short_eng,
                        act_parent_id, 
                        auth_allow_id,
                        language_id,
                        description,
                        description_eng,
                        duns_number,
                        logo
                        )
                        SELECT  
                            " . intval($params['profile_public']) . " AS profile_public, 
                            " . intval($operationIdValue) . " AS operation_type_id,
                            " . intval($active) . " AS active,
                            consultant_id,
                            " . intval($opUserIdValue) . " AS op_user_id,
                            " . intval($params['country_id']) . " AS country_id,
                            '" . $params['firm_name'] . "' AS firm_name, 
                            '" . $params['web_address'] . "' AS web_address, 
                            '" . $params['tax_office'] . "' AS tax_office, 
                            '" . $params['tax_no'] . "' AS tax_no,
                            '" . $params['sgk_sicil_no'] . "' AS sgk_sicil_no,
                            " . intval($foundationYearx) . " AS foundation_yearx ,
                            '" . $params['firm_name_eng'] . "' AS firm_name_eng, 
                            '" . $params['firm_name_short'] . "' AS firm_name_short,
                            '" . $params['firm_name_short_eng'] . "' AS firm_name_short_eng,
                            act_parent_id,  
                            auth_allow_id,
                            " . intval($languageIdValue) . " AS language_id,
                            '" . $params['description'] . "' AS description,
                            '" . $params['description_eng'] . "' AS description_eng, 
                            '" . $params['duns_number'] . "' AS duns_number,
                            logo
                        FROM info_firm_profile 
                        WHERE id =  " . intval($endOfIdValue) . "
                        ";
            $statement_act_insert = $pdo->prepare($sql);
         //   echo debugPDO($sql, $params);
            $insert_act_insert = $statement_act_insert->execute();
            $affectedRows = $statement_act_insert->rowCount();
            $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
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
            //            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {
            //    $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /** 
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  06.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGrid($params = array()) {
        if (isset($params['page']) && $params['page'] != "" && isset($params['rows']) && $params['rows'] != "") {
            $offset = ((intval($params['page']) - 1) * intval($params['rows']));
            $limit = intval($params['rows']);
        } else {
            $limit = 10;
            $offset = 0;
        }

        $sortArr = array();
        $orderArr = array();
        $whereSql = "";
        if (isset($params['sort']) && $params['sort'] != "") {
            $sort = trim($params['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($params['sort']);
        } else {
            $sort = "a.firm_name";
        }

        if (isset($params['order']) && $params['order'] != "") {
            $order = trim($params['order']);
            $orderArr = explode(",", $order); 
            if (count($orderArr) === 1)
                $order = trim($params['order']);
        } else {
            $order = "ASC";
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
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_names,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name, ''), a.firm_name_eng), ''), a.firm_name) AS firm_names, 
                    a.web_address,
                    a.tax_office, 
                    a.tax_no, 
                    a.sgk_sicil_no,
                    a.ownership_status_id,
                    COALESCE(NULLIF(sd1x.description, ''), sd1.description_eng) AS owner_ships,
                    a.foundation_year,
                    a.act_parent_id,
                    a.language_code,
                    a.language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_names,
                    a.active,
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_actives,
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleteds,
                    a.op_user_id,
                    u.username,
                    a.auth_allow_id,
                    COALESCE(NULLIF(sd13x.description, ''), sd13.description_eng) AS auth_alows,
                    a.cons_allow_id,
                    COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allows, 
                    a.language_parent_id,
                    a.firm_name_short,
                    a.country_id,
		    COALESCE(NULLIF(cox.name, ''), co.name_eng) AS country_names,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.description, ''), a.description_eng), ''), a.description) AS descriptions,
                    a.duns_number,
                    ifk.network_key,
		    CASE COALESCE(NULLIF(a.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png')) END AS logo,
                    a.place_point
                FROM info_firm_profile a  
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0  
                LEFT JOIN info_firm_keys ifk ON ifk.firm_id = a.act_parent_id
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0 

                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = a.language_id AND op.deleted =0 AND op.active =0
		LEFT JOIN sys_operation_types opx ON opx.id = op.id AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0

                INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND sd13.language_id = a.language_id AND a.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = a.language_id AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_specific_definitions sd1 ON sd1.main_group = 1 AND sd1.first_group= a.ownership_status_id AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
                
                LEFT JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id  
                
                LEFT JOIN sys_countrys cox ON (cox.id = a.country_id OR cox.language_parent_id = a.country_id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id                		

                LEFT JOIN sys_specific_definitions sd13x ON (sd13x.id = sd13.id OR sd13x.language_parent_id = sd13.id) AND sd13x.language_id =lx.id  AND sd13x.deleted =0 AND sd13x.active =0 
                LEFT JOIN sys_specific_definitions sd14x ON (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.language_id = lx.id  AND sd14x.deleted = 0 AND sd14x.active = 0                
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0                
		LEFT JOIN sys_specific_definitions sd1x ON (sd1x.id = sd1.id OR sd1x.language_parent_id = sd1.id) AND sd1x.language_id = lx.id  AND sd1x.deleted = 0 AND sd1x.active = 0                
		LEFT JOIN info_firm_profile ax ON (ax.act_parent_id = a.act_parent_id OR ax.language_parent_id = a.act_parent_id) AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0                 

                WHERE a.deleted =0 AND a.active =0 AND a.language_parent_id =0
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
     * @ Gridi doldurmak için info_firm_profile tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  06.01.2016
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
            $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                 $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
            } 
            $whereSQL = " WHERE a.deleted =0 AND a.active =0 AND a.language_parent_id =0 " ;
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT , 
                FROM info_firm_profile a                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                 
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = a.language_id AND op.deleted =0 AND op.active =0		
                INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND sd13.language_id = a.language_id AND a.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = a.language_id AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0		
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
     * info_firm_users tablosunda firma elemanı kaydı olusturur.  !!
     * @author Okan CIRAN
     * @version v 1.0  18.03.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertCompanyUser($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $operationIdValue = -1;
            if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                $operationIdValue = $params['operation_type_id'];
            }
            $sql = " 
                INSERT INTO info_firm_users(                           
                            firm_id,                              
                            user_id, 
                            operation_type_id,                             
                            language_id, 
                            act_parent_id, 
                            op_user_id, 
                            consultant_id 
                            )
                VALUES (    
                            :firm_id,                              
                            :user_id, 
                            " . intval($operationIdValue) . ",
                            :language_id, 
                            (SELECT last_value FROM info_firm_users_id_seq), 
                            :op_user_id, 
                            :consultant_id                            
                    )";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':firm_id', $params['firm_id'], \PDO::PARAM_INT);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
            $statement->bindValue(':consultant_id', $params['consultant_id'], \PDO::PARAM_INT);
                            
            // echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_firm_users_id_seq');
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
     * @ seçilmiş olan user_id nin sahip oldugu firmaları combobox a doldurmak için kayıtları döndürür   !!
     * @version v 1.0  06.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBox($params = array()) {
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
                    a.id,                     
                    COALESCE(NULLIF(a.firm_name, ''), a.firm_name_eng) AS name
                FROM info_firm_profile  a               
                WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND 
                    a.language_id = " . intval($languageIdValue) . " AND 
                    a.owner_user_id = " . intval($opUserIdValue) . "             
                ORDER BY  name                
                                 ";
                $statement = $pdo->prepare($sql);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
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
     * @ info_firm_profile tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  06.01.2016
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare("                 
                    
                    INSERT INTO info_firm_profile(
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
                            FROM info_firm_profile c
                            LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                            WHERE c.id = " . intval($params['id']) . "
                    ) AS xy  
                    WHERE xy.language_main_code NOT IN 
                        (SELECT 
                            DISTINCT language_code 
                         FROM info_firm_profile cx 
                         WHERE (cx.language_parent_id = " . intval($params['id']) . "
						OR cx.id = " . intval($params['id']) . "
					) AND cx.deleted =0 AND cx.active =0)

                            ");

            //   $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);

            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
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
     * @ text alanları doldurmak için info_firm_profile tablosundan tek kayıt döndürür !! 
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
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
                        a.firm_name_short
                    FROM info_firm_profile a    
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
     * @version 06.01.2016 
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
            if (!\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];
                $this->makePassive(array('id' => $params['id'])); 
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
                
                $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_profile(
                        profile_public,
                        operation_type_id, 
                        active,
                        deleted, 
                        consultant_id, 
                        consultant_confirm_type_id, 
                        confirm_id,
                        op_user_id,
                        country_id,
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no, 
                        ownership_status_id, 
                        foundation_year, 
                        language_code,
                        firm_name_eng, 
                        firm_name_short,
                        act_parent_id,
                        auth_allow_id,
                        language_id,
                        description,
                        description_eng,
                        duns_number,
                        logo,
                        place_point
                        )
                        SELECT  
                            profile_public,                             
                            " . intval($operationIdValue) . ",
                            1,
                            1,
                            consultant_id,
                            consultant_confirm_type_id, 
                            confirm_id,
                            " . intval($opUserIdValue) . ",
                            country_id,
                            firm_name, 
                            web_address, 
                            tax_office, 
                            tax_no, 
                            sgk_sicil_no, 
                            ownership_status_id, 
                            foundation_year, 
                            language_code,
                            firm_name_eng, 
                            firm_name_short,
                            act_parent_id,  
                            auth_allow_id,
                            language_id,
                            description, 
                            description_eng, 
                            duns_number,
                            logo,
                            place_point
                        FROM info_firm_profile 
                        WHERE id =  " . intval($params['id']) . " 
                        ");

                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
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
     * @ userin yaptığı aktif kayıt bilgisini info_firm_profile tablosundan döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingular($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $whereSql = " AND a.op_user_id = " . $opUserId ['resultSet'][0]['user_id'];
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
                    a.profile_public, 
                    a.f_check, 
                    a.s_date, 
                    a.c_date, 
                    a.operation_type_id,                   
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_names,                   
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name, ''), a.firm_name_eng), ''), a.firm_name) AS firm_names,   
                    a.web_address,                     
                    a.tax_office, 
                    a.tax_no, 
                    a.sgk_sicil_no,                   
                    a.ownership_status_id,             
                    COALESCE(NULLIF(sd1x.description, ''), sd1.description_eng) AS owner_ships,   
                    a.foundation_year,			
                    a.act_parent_id,  
                    a.language_code, 
                    a.language_id, 
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_names,                        
                    a.active,                
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_actives,    
                    a.deleted,                  
                    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleteds,    
                    a.op_user_id,
                    u.username,                    
                    a.auth_allow_id,                    
                    COALESCE(NULLIF(sd13x.description, ''), sd13.description_eng) AS auth_alows,    
                    a.cons_allow_id,                   
                    COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allows,    
                    a.language_parent_id,  
                    a.firm_name_short,
                    a.country_id,                   
		    COALESCE(NULLIF(cox.name, ''), co.name_eng) AS country_names,                     
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.description, ''), a.description_eng), ''), a.description) AS descriptions,   
                    a.duns_number,
                    a.owner_user_id,
                    own.username AS owner_username ,
                    ifk.network_key,
                    a.logo,
                    a.place_point
                FROM info_firm_profile a   
                LEFT JOIN info_firm_keys ifk ON ifk.firm_id =  a.act_parent_id AND a.deleted = 0 AND a.active =0 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0 
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = a.language_id AND op.deleted =0 AND op.active =0
                INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND sd13.language_id = a.language_id AND a.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = a.language_id AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
                LEFT JOIN sys_specific_definitions sd1 ON sd1.main_group = 1 AND sd1.first_group= a.ownership_status_id AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
                
                LEFT JOIN info_users u ON u.id = a.op_user_id                      
                LEFT JOIN info_users own ON own.id = a.owner_user_id                                      
                LEFT JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id  
                
                LEFT JOIN sys_countrys cox ON (cox.id = a.country_id OR cox.language_parent_id = a.country_id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id                
		LEFT JOIN sys_operation_types opx ON opx.id = a.operation_type_id AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0
                LEFT JOIN sys_specific_definitions sd13x ON sd13x.main_group = 13 AND sd13x.language_id = lx.id  AND a.auth_allow_id = sd13x.first_group AND sd13x.deleted =0 AND sd13x.active =0
                LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND  sd14x.language_id = lx.id  AND a.cons_allow_id = sd14x.first_group AND sd14x.deleted =0 AND sd14x.active =0
                LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= a.deleted AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_specific_definitions sd1x ON sd1x.main_group = 1 AND sd1x.first_group= a.ownership_status_id AND sd1x.language_id =lx.id  AND sd1x.deleted = 0 AND sd1x.active = 0
		LEFT JOIN info_firm_profile ax on ax.language_parent_id = a.id AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0                 
                WHERE a.deleted =0 AND a.active =0 AND a.language_parent_id =0   
                ".$whereSql."
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
     * @ info_firm_profile tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  06.01.2016
     * @param array | null $args
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
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {                    
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

                    $foundationYearx=NULL;
                    if ((isset($params['foundation_yearx']) && $params['foundation_yearx'] != "")) {
                        $foundationYearx = $params['foundation_yearx'];                      
                    }
                    //to_timestamp(1451599200 ) 
                    
                    $sql = " 
                   INSERT INTO info_firm_profile(
                        profile_public, 
                        country_id,                    
                        firm_name, 
                        web_address, 
                        tax_office, 
                        tax_no, 
                        sgk_sicil_no, 
                        ownership_status_id,                         
                        language_code, 
                        language_id,
                        op_user_id, 
                        consultant_id,
                        operation_type_id,                      
                        foundation_yearx, 
                        firm_name_short,
                        act_parent_id,                      
                        description,
                        description_eng,
                        duns_number,
                        logo
                        )
                VALUES (
                        " . intval($params['profile_public']) . ", 
                        " . intval($params['country_id']) . ",
                        :firm_name, 
                        :web_address, 
                        :tax_office, 
                        :tax_no,
                        :sgk_sicil_no, 
                        " . intval($params['ownership_status_id']) . ",
                        :language_code,  
                        ". intval($languageIdValue) . ",
                        ". intval($opUserIdValue) .",
                        ". intval($ConsultantId).",
                        ". intval($operationIdValue).",
                        " .intval($foundationYearx) . " ,
                        :firm_name_short,
                        (SELECT last_value FROM info_firm_profile_id_seq),
                        :description,
                        :description_eng,
                        :duns_number,
                        :logo
                                            )    ";
                    $statementInsert = $pdo->prepare($sql);
                    $statementInsert->bindValue(':firm_name', $params['firm_name'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':web_address', $params['web_address'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':tax_office', $params['tax_office'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':tax_no', $params['tax_no'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':sgk_sicil_no', $params['sgk_sicil_no'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':firm_name_short', $params['firm_name_short'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':duns_number', $params['duns_number'], \PDO::PARAM_STR);
                    $statementInsert->bindValue(':logo', $params['logo'], \PDO::PARAM_STR);
                 // echo debugPDO($sql, $params);     
                    $result = $statementInsert->execute();
                    $insertID = $pdo->lastInsertId('info_firm_profile_id_seq');
                    $errorInfo = $statementInsert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                     
                    
                    InfoFirmKeys::insert(array('firm_id' => $insertID, 
                                              'country_id' => $params['country_id']));
                    
                    $this ->insertCompanyUser(array('firm_id' => $insertID, 
                                                'language_id' => $languageIdValue, 
                                                'consultant_id'=> $ConsultantId,  
                                                'user_id'=> $opUserIdValue, 
                                                'op_user_id'=> $opUserIdValue, 
                                                'operation_type_id'=> $operationIdValue, 
                                               )); 
                            
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
                    $errorInfo = '23505'; // 23505  unique_violation
                    $errorInfoColumn = 'firm_name';
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
     * @ userin firm id sini döndürür  !!
     * su an için sadece 1 firması varmış senaryosu için gecerli. 
     * @version v 1.0  29.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUserFirmIds($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            if (isset($params['user_id'])) {
                $user = $params['user_id'];  
                $sql = " 
                SELECT id AS firm_id, 1=1 AS control FROM (
                            SELECT ifp.id 
                            FROM info_users a
                            INNER JOIN info_firm_users ifu ON ifu.user_id = " . intval($user) . " AND ifu.language_parent_id =0 AND a.id = ifu.user_id                            
			    INNER JOIN info_firm_profile ifp ON ifp.active =0 AND ifp.deleted =0 AND ifp.language_parent_id =0 AND ifu.firm_id = ifp.act_parent_id     
                            WHERE a.active =0 AND a.deleted =0  
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
     * @ userin firm id sini döndürür  !!
     * su an için sadece 1 firması varmış senaryosu için gecerli. 
     * @version v 1.0  29.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getFirmIdForCPK($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            if (isset($params['cpk'])) {
                $cpk = $params['cpk'];  
                $sql = " 
                SELECT firm_id AS firm_id, 1=1 AS control FROM (
                            SELECT 
                                a.firm_id ,
                                CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('".$cpk."','*','/'))) = CONCAT('_J9..',REPLACE('".$cpk."','*','/')) AS cpk 
                            FROM info_firm_keys a
			    INNER JOIN info_firm_profile ifp ON ifp.active =0 AND ifp.deleted =0 AND ifp.language_parent_id =0 AND a.firm_id = ifp.act_parent_id 
                ) AS xtable WHERE cpk = TRUE  limit 1
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
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'cpk';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
      
    /**  
     * @author Okan CIRAN
     * @ user bu firmaya(cpk sı verilen) kayıtlı mı?   !! 
     * cpk ve user id parametre olarak  girilir.
     * @version v 1.0 01.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getCheckIsThisFirmRegisteredUser($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');         
            if (isset($params['cpk'])) {
                $cpk = $params['cpk'];  
                $user = $params['op_user_id'];                 
                $sql = " 
                SELECT firm_id AS firm_id, 1=1 AS control ,user_id FROM (
                            SELECT a.firm_id ,ifu.user_id ,
                             CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('".$cpk."','*','/'))) = CONCAT('_J9..',REPLACE('".$cpk."','*','/')) as cpk 
                            FROM info_firm_keys a                                                        
			    INNER JOIN info_firm_profile ifp ON ifp.active =0 AND ifp.deleted =0 AND ifp.language_parent_id =0 AND a.firm_id = ifp.act_parent_id     
			    INNER JOIN info_firm_users ifu ON ifu.user_id = " . intval($user) . " AND ifu.language_parent_id =0 AND a.firm_id = ifu.firm_id
                ) AS xtable WHERE cpk = TRUE  limit 1
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
                $errorInfoColumn = 'cpk';
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
    public function getFirmIdsForNetworkKey($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            if (isset($params['network_key'])) {
                $npk = $params['network_key'];  
                $sql = " 
                SELECT firm_id, 1=1 AS control FROM (
                            SELECT a.firm_id 
                            FROM info_firm_keys a                            			    
                            WHERE
                             a.network_key = '".$npk."'
                ) AS xtable limit 1                             
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
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  21.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyListsGuest($params = array()) {
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
                $whereSql = "";
                if (isset($params['sort']) && $params['sort'] != "") {
                    $sort = trim($params['sort']);
                    $sortArr = explode(",", $sort);
                    if (count($sortArr) === 1)
                        $sort = trim($params['sort']);
                } else {
                    $sort = " firm_names";
                }

                if (isset($params['order']) && $params['order'] != "") {
                    $order = trim($params['order']);
                    $orderArr = explode(",", $order); 
                    if (count($orderArr) === 1)
                        $order = trim($params['order']);
                } else {
                    $order = "ASC";
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
                $sorguStr = null;
                if (isset($params['filterRules'])) {
                    $filterRules = trim($params['filterRules']);
                    $jsonFilter = json_decode($filterRules, true);
                    $sorguExpression = null;
                    foreach ($jsonFilter as $std) {
                        if ($std['value'] != null) {
                            switch (trim($std['field'])) {
                                case 'firm_names':
                                    $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                    $sorguStr.=" AND LOWER(COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name, ''), a.firm_name_eng), ''), a.firm_name))" . $sorguExpression . ' ';

                                    break;
                                case 'web_address':
                                    $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                                    $sorguStr.=" AND LOWER(a.web_address)" . $sorguExpression . ' ';

                                    break;
                                case 'firm_name_short':
                                    $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                                    $sorguStr.=" AND LOWER(a.firm_name_short)" . $sorguExpression . ' ';

                                    break;
                                 case 'country_names':
                                    $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                                    $sorguStr.=" AND LOWER(COALESCE(NULLIF(cox.name, ''), co.name_eng))" . $sorguExpression . ' ';

                                    break;
                                 case 'npk':
                                    $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                                    $sorguStr.=" AND LOWER(k.network_key)" . $sorguExpression . ' ';

                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                } else {
                    $sorguStr = null;
                    $filterRules = "";
                }
                $sorguStr = rtrim($sorguStr, "AND ");    

                $sql = "                  
                SELECT 
                    k.network_key AS npk ,
                    LOWER(COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name, ''), a.firm_name_eng), ''), a.firm_name)) AS firm_names,   
                    LOWER(a.web_address) AS web_address,
                    LOWER(a.firm_name_short) AS firm_name_short,
                    a.country_id,
		    LOWER(COALESCE(NULLIF(cox.name, ''), co.name_eng)) AS country_names,
                    LOWER(COALESCE(NULLIF(COALESCE(NULLIF(ax.description, ''), a.description_eng), ''), a.description)) AS descriptions,
                      CASE COALESCE(NULLIF(a.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(k.folder_name ,'/',k.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png')) END AS logo, 
                    a.web_address
                FROM info_firm_profile a    
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0  
                INNER JOIN info_firm_keys k ON a.id = k.firm_id
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_parent_id = 0
                LEFT JOIN sys_countrys cox ON (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
		LEFT JOIN info_firm_profile ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0 AND ax.profile_public =0
                WHERE 
                    a.language_parent_id =0 AND 
                    a.profile_public =0 AND 
                    a.cons_allow_id = 2
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
             
               // echo debugPDO($sql, $params);                
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
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  21.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyListsGuestRtc($params = array()) {
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
                $sql = "
		SELECT 
                    count(a.id) AS count  
                FROM info_firm_profile a
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                    
                INNER JOIN info_firm_keys k on a.id = k.firm_id                   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                WHERE 
                    a.language_parent_id =0 AND 
                    a.profile_public =0 AND 
                    a.cons_allow_id = 2 
                 ";
                $statement = $pdo->prepare($sql);
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
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  21.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoEmployeesGuest($params = array()) {
        try {
                $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');               
                $sql = "                
                  SELECT
                    ifpi.number_of_employees, 
                    ifpi.number_of_worker, 
                    ifpi.number_of_technician, 
                    ifpi.number_of_engineer, 
                    ifpi.number_of_administrative_staff, 
                    ifpi.number_of_sales_staff, 
                    ifpi.number_of_foreign_trade_staff,                    
                    CASE COALESCE(NULLIF(a.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png')) END AS logo
                FROM info_firm_profile a
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                    
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = a.act_parent_id                 
                LEFT JOIN info_firm_personnel_info ifpi ON ifpi.firm_id = a.act_parent_id AND ifpi.profile_public =0 AND ifpi.cons_allow_id = 2                
                WHERE 
                    a.language_parent_id =0 AND 
                    a.profile_public =0 AND 
                    a.cons_allow_id =2 AND
                    ifk.network_key = '".$params['network_key']."'               
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
   
     * @author Okan CIRAN
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  23.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoReferencesGuest($params = array()) {
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

            $sql = "		 
                SELECT 
                    a.id, 
                    COALESCE(NULLIF(COALESCE(NULLIF(fprefx.firm_name, ''),fpref.firm_name_eng), ''), fpref.firm_name) AS ref_name, 
                    a.s_date as ref_date, 
                    ifk.network_key AS ref_network_key, 
                    a.active,
                    CASE COALESCE(NULLIF(fp.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(fp.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(ifkx.folder_name ,'/',ifkx.logos_folder,'/' ,COALESCE(NULLIF(fp.logo, ''),'image_not_found.png')) END AS ref_logo,
                    CASE COALESCE(NULLIF(fpref.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(fpref.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.logos_folder,'/' ,COALESCE(NULLIF(fpref.logo, ''),'image_not_found.png')) END AS firm_logo                   
                FROM info_firm_profile fp 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0 
                INNER JOIN info_firm_references a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0
                INNER JOIN info_firm_profile fpref ON fpref.id = a.ref_firm_id AND fpref.cons_allow_id = 2 AND fpref.language_parent_id = 0 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = a.ref_firm_id 
                INNER JOIN info_firm_keys ifkx ON ifkx.firm_id = a.firm_id 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.act_parent_id OR fpx.act_parent_id=fp.act_parent_id ) AND fpx.cons_allow_id = 2 AND fpx.language_id = lx.id 
                LEFT JOIN info_firm_profile fprefx ON (fprefx.language_parent_id = a.ref_firm_id OR fprefx.id = a.ref_firm_id) AND fprefx.cons_allow_id = 2 AND fprefx.language_id = lx.id 
                WHERE  
                    fp.cons_allow_id = 2 AND  
                    ifkx.network_key = '" . $params['network_key'] . "'
                ORDER BY ref_name
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /*  
     * @author Okan CIRAN
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  23.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoSocialediaGuest($params = array()) {
        try {
                $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');    
                $$languageCode = 'tr';
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
		    COALESCE(NULLIF(COALESCE(NULLIF(smx.name, ''), sm.name_eng), ''), sm.name) AS socialmedia,                   
                    fsm.firm_link,
                    sm.abbreviation
                FROM info_firm_profile a    
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                 
                INNER JOIN info_firm_keys fk ON fk.firm_id = a.act_parent_id 
                INNER JOIN info_firm_socialmedia fsm ON fsm.firm_id = a.act_parent_id AND fsm.cons_allow_id = 2 AND fsm.profile_public =0
                INNER JOIN sys_socialmedia sm ON sm.id = fsm.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 
		LEFT JOIN sys_socialmedia smx ON (smx.id = sm.id OR smx.language_parent_id = fsm.sys_socialmedia_id) AND smx.language_id = lx.id AND smx.active =0 AND smx.deleted =0                   
                WHERE 
                    a.language_parent_id =0 AND 
                    a.profile_public =0 AND 
                    a.cons_allow_id = 2 AND  
                    fk.network_key = '".$params['network_key']."' 
                    
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /*  
     * @author Okan CIRAN
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  23.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoCustomersGuest($params = array()) {
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

                $sql = "
                SELECT                
		    COALESCE(NULLIF(COALESCE(NULLIF(ifcx.customer_name, ''), ifc.customer_name_eng), ''), ifc.customer_name) AS customer_names                                     
                FROM info_firm_profile a    
                INNER JOIN info_firm_keys fk on fk.firm_id = a.id
                INNER JOIN info_firm_customers ifc on ifc.firm_id = a.act_parent_id AND ifc.cons_allow_id = 2 AND ifc.profile_public =0                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0                 
		LEFT JOIN info_firm_customers ifcx on (ifcx.id = ifc.act_parent_id OR ifcx.language_parent_id = ifc.act_parent_id) AND 
                                                                                    ifcx.language_id = lx.id AND ifcx.profile_public =0                    
                WHERE 
                    a.language_parent_id =0 AND 
                    a.profile_public =0 AND 
                    a.cons_allow_id = 2 AND  
                    fk.network_key = '".$params['network_key']."'                     
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }
    
    /*  
     * @author Okan CIRAN
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  15.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoProductsGuest($params = array()) {
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

                $sql = "                    
                SELECT 
                    ifp.id,      
                    COALESCE(NULLIF(COALESCE(NULLIF(ifpx.product_name, ''), ifp.product_name_eng), ''), ifp.product_name) AS product_name,   
                    COALESCE(NULLIF(COALESCE(NULLIF(ifpx.product_description, ''), ifp.product_description_eng), ''), ifp.product_description) AS product_description,   
                    ifp.gtip_no_id,
                    COALESCE(NULLIF(ifp.product_picture, ''), 'image_not_found.png') AS product_picture,
                    COALESCE(NULLIF(ifp.product_video_link, ''), 'video_not_found.png') AS product_video_link,                    
                    ifp.active
                FROM info_firm_products ifp
                INNER JOIN info_firm_profile a ON ifp.firm_id = a.act_parent_id AND a.language_parent_id =0 AND a.cons_allow_id = 2
                INNER JOIN info_firm_keys fk ON fk.firm_id = a.act_parent_id
                INNER JOIN sys_language l ON l.id = ifp.language_id AND l.deleted =0 AND l.active =0                 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0                 
                LEFT JOIN info_firm_products ifpx on (ifpx.id = ifp.id OR ifpx.language_parent_id = ifp.id) AND ifpx.cons_allow_id = 2 AND ifpx.language_id = lx.id                                		
                WHERE 
                    ifp.language_parent_id =0 AND 
                    ifp.profile_public =0 AND 
                    ifp.cons_allow_id = 2 AND                
                    fk.network_key = '".$params['network_key']."'  
                ORDER BY product_name
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }
 
    /*  
     * @author Okan CIRAN
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  15.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoSectorsGuest($params = array()) {
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

                $sql = "                    
                SELECT 
                   ifs.id,
                   COALESCE(NULLIF(COALESCE(NULLIF(ssx.name, ''), ss.name_eng), ''), ss.name) AS sector_name,                                    
                   CASE COALESCE(NULLIF(ss.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(ss.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(sps.folder_road ,'/',sps.logos_folder,'/' ,COALESCE(NULLIF(ss.logo, ''),'image_not_found.png')) END AS logo
                FROM info_firm_sectoral ifs 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                   
                INNER JOIN info_firm_profile a ON ifs.firm_id = a.act_parent_id AND a.language_parent_id =0 AND a.cons_allow_id = 2
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
                INNER JOIN info_firm_keys fk ON fk.firm_id = a.act_parent_id
                INNER JOIN sys_sectors ss ON ss.id = ifs.sector_id AND ss.language_parent_id =0 AND ss.deleted =0 AND ss.active =0
                LEFT JOIN sys_sectors ssx ON (ssx.id = ifs.sector_id OR ssx.language_parent_id = ifs.sector_id) AND ssx.id = ifs.sector_id AND ssx.language_parent_id =0 AND ssx.deleted =0 AND ssx.active =0
                WHERE                     
                    ifs.profile_public =0 AND 
                    ifs.cons_allow_id = 2  AND 
                    fk.network_key = '".$params['network_key']."'  
                ORDER BY sector_name
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }
    
    /*  
     * @author Okan CIRAN
     * @ quest kullanıcısı için,   info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  15.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyInfoBuildingNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
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
            if ((isset($params['building_type_id']) && $params['building_type_id'] != "")) {
                $buildingTypeIdValue = $params['building_type_id'];
                $addSql .= " AND ifs.firm_building_type_id = " . intval($buildingTypeIdValue);
            }
            $sql = " 
		SELECT 
                   firm_id,
                   id, 
                   building_type,
                   firm_building_name,
                   osb_name,
                   osb_name_eng,
                   CONCAT(address,' ', borough_name,'/',  city_name, ' ', country_name) AS building_address,
                   active 
                   FROM ( 
			SELECT 
			   a.act_parent_id as firm_id,
			   ifs.id, 
			   COALESCE(NULLIF(COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng), ''), sd4.description) AS building_type,                 
			   COALESCE(NULLIF(COALESCE(NULLIF(ifsx.firm_building_name, ''), ifs.firm_building_name_eng), ''), ifs.firm_building_name) AS firm_building_name,                   
			   COALESCE(NULLIF(sox.name, ''), so.name_eng) AS osb_name,
			   so.name_eng AS osb_name_eng,
			   ifs.address,
			   ifs.borough_id,                      
			   COALESCE(NULLIF(COALESCE(NULLIF(box.name, ''), bo.name_eng), ''), bo.name) AS borough_name,
			   ifs.city_id,                     
			   COALESCE(NULLIF(COALESCE(NULLIF(ctx.name, ''), ct.name_eng), ''), ct.name) AS city_name,
			   ifs.country_id,
			   COALESCE(NULLIF(COALESCE(NULLIF(cox.name, ''), co.name_eng), ''), co.name) AS country_name,
			   ifs.active
			FROM info_firm_address ifs
			INNER JOIN info_firm_profile a ON ifs.firm_id = a.act_parent_id   AND a.language_parent_id =0 AND a.deleted =0 AND a.active =0
			INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
			LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND l.deleted =0 AND l.active =0
			INNER JOIN info_firm_keys fk ON fk.firm_id = a.id
			INNER JOIN sys_osb so ON so.id = ifs.osb_id AND so.deleted =0 AND so.active =0 AND l.id = so.language_id
			LEFT JOIN sys_osb sox ON (sox.id = so.id OR sox.language_parent_id = so.id) AND sox.deleted =0 AND sox.active =0 AND lx.id = sox.language_id
			LEFT JOIN info_firm_address ifsx ON (ifsx.id = ifs.id OR ifsx.language_parent_id = ifs.id) AND ifsx.language_id = lx.id AND ifsx.deleted =0 AND ifsx.active =0
			INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND sd4.first_group= ifs.firm_building_type_id AND sd4.language_id = a.language_id AND sd4.deleted = 0 AND sd4.active = 0
			LEFT JOIN sys_specific_definitions sd4x ON sd4x.main_group = 4 AND sd4x.id= sd4.id AND sd4x.language_id = lx.id AND sd4x.deleted = 0 AND sd4x.active = 0
			LEFT JOIN sys_countrys co ON co.id = ifs.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = ifs.language_id
			LEFT JOIN sys_city ct ON ct.id = ifs.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = ifs.language_id
			LEFT JOIN sys_borough bo ON bo.id = ifs.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = ifs.language_id
			LEFT JOIN sys_countrys cox ON (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
			LEFT JOIN sys_city ctx ON (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
			LEFT JOIN sys_borough box ON (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 
			WHERE ifs.deleted =0 AND 
			      ifs.active =0 AND
			      ifs.profile_public =0 
			     AND fk.network_key = '" . $params['network_key'] . "'  
                             " . $addSql . " 
                 ) as xtable    
                ORDER BY id 
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**  
     * @author Okan CIRAN
     * @ firmanın en son kayıdının id sini döndürür  !!     
     * @version v 1.0  29.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getFirmEndOfId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            if (isset($params['firm_id'])) {
                $firmId = $params['firm_id'];  
                $sql = " 
                SELECT id AS firm_id, 1=1 AS control FROM (
                            SELECT MAX(a.id) AS id 
                            FROM info_firm_profile a
                            WHERE                                 
                                a.deleted =0 AND
                                act_parent_id =  " . intval($firmId) . " 
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
                $errorInfo = '23502';   // 23502  $firmId not_null_violation
                $errorInfoColumn = '$firmId';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /** 
     * @author Okan CIRAN
     * @ userin yaptığı aktif kayıt bilgisini info_firm_profile tablosundan döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmFullVerbal($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $getFirmId = -1;
            $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
            if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                $getFirmId = $getFirm ['resultSet'][0]['firm_id'];

                $endOfId = $this->getFirmEndOfId(array('firm_id' => $getFirmId));
                if (\Utill\Dal\Helper::haveRecord($endOfId)) {
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
                    
                    $CPKValue = NULL;                    
                    $CPK = InfoFirmKeys:: getCPK(array('network_key' => $params['network_key']));
                      if (\Utill\Dal\Helper::haveRecord($CPK)) {
                           $CPKValue =  $CPK ['resultSet'][0]['cpk']; 
                      }
                      
                    $sql = "
                SELECT 
                    ifv.id,                    
                    a.profile_public,                     
                    a.s_date, 
                    a.c_date,                     
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name, ''), a.firm_name_eng), ''), a.firm_name) AS firm_name,
                    a.firm_name_eng,
		    COALESCE(NULLIF(COALESCE(NULLIF(ax.firm_name_short, ''), a.firm_name_short_eng), ''), a.firm_name_short) AS firm_name_short,
		    a.firm_name_short_eng,
		    a.web_address,
                    a.country_id,                   
		    COALESCE(NULLIF(cox.name, ''), co.name_eng) AS country_name,
		    co.name_eng AS country_name_eng,
                    COALESCE(NULLIF(ifvx.about, ''), ifv.about_eng) AS about,
                    ifv.about_eng,
                    COALESCE(NULLIF(ifvx.verbal1_title, ''), ifv.verbal1_title_eng) AS verbal1_title,
                    ifv.verbal1_title_eng,
                    COALESCE(NULLIF(ifvx.verbal1, ''), ifv.verbal1_eng) AS verbal1,
                    ifv.verbal1_eng,
                    COALESCE(NULLIF(ifvx.verbal2_title, ''), ifv.verbal2_title_eng) AS verbal2_title,
                    ifv.verbal2_title_eng,
                    COALESCE(NULLIF(ifvx.verbal2, ''), ifv.verbal2_eng) AS verbal2,
                    ifv.verbal2_eng,
                    COALESCE(NULLIF(ifvx.verbal3_title, ''), ifv.verbal3_title_eng) AS verbal3_title,
                    ifv.verbal3_title_eng,
                    COALESCE(NULLIF(ifvx.verbal3, ''), ifv.verbal3_eng) AS verbal3,
                    ifv.verbal3_eng,
                    a.duns_number,                    
                    a.tax_office, 
                    a.tax_no, 
                    a.foundation_yearx,
                    a.act_parent_id,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,                    
                    a.operation_type_id,                   
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    op.operation_name_eng,
                    a.active,                
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,    
                    a.deleted,                  
                    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,    
                    a.op_user_id,
                    u.username,                    
                    a.auth_allow_id,                    
                    COALESCE(NULLIF(sd13x.description, ''), sd13.description_eng) AS auth_alow,    
                    a.cons_allow_id,                   
                    COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,    
                    a.language_parent_id,  
                    ifk.network_key,                  
                    CASE COALESCE(NULLIF(a.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png')) END AS logo,
                    a.place_point,
                    '".$CPKValue."' AS cpk
                FROM info_firm_profile a  
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0  
                INNER JOIN info_firm_keys ifk ON ifk.firm_id =  a.act_parent_id 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND l.deleted =0 AND l.active =0 
		LEFT JOIN info_firm_profile ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0
		LEFT JOIN info_firm_verbal ifv ON ifv.firm_id = ifk.firm_id AND ifv.deleted = 0 AND ifv.active =0 AND ifv.language_parent_id=0  
		LEFT JOIN info_firm_verbal ifvx ON (ifvx.id = ifv.id OR ifvx.language_parent_id = ifv.id) AND ifvx.deleted = 0 AND ifvx.active =0 AND ifvx.language_id = lx.id 
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = a.language_id AND op.deleted =0 AND op.active =0
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0
                INNER JOIN sys_specific_definitions sd13 ON sd13.main_group = 13 AND sd13.language_id = a.language_id AND a.auth_allow_id = sd13.first_group AND sd13.deleted =0 AND sd13.active =0
                LEFT JOIN sys_specific_definitions sd13x ON (sd13x.id = sd13.id OR sd13.language_parent_id = sd13.id) AND sd13x.language_id = lx.id AND sd13x.deleted =0 AND sd13x.active =0
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.language_id = a.language_id AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0
                LEFT JOIN sys_specific_definitions sd14x ON (sd14x.id = sd14.id OR sd14.language_parent_id = sd14.id) AND sd14x.language_id = lx.id AND sd14x.deleted =0 AND sd14x.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15.language_parent_id = sd15.id) AND sd15x.language_id = lx.id AND sd15x.deleted =0 AND sd15x.active =0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted =0 AND sd16x.active =0
                
                INNER JOIN info_users u ON u.id = a.op_user_id                                      
                INNER JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                LEFT JOIN sys_countrys cox ON (cox.id = a.country_id OR cox.language_parent_id = a.country_id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id		
                WHERE    
                    a.id = " . $endOfId ['resultSet'][0]['firm_id'] . "                 
                ";
                    $statement = $pdo->prepare($sql);
                 //    echo debugPDO($sql, $params);                
                    $statement->execute();
                    $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
                } else {
                    $errorInfo = '23502';   // 23502  user_id not_null_violation
                    $errorInfoColumn = 'firm_id';
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'network_key';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @author Okan CIRAN
     * @ firmanın danısman bilgisini döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getFirmProfileConsultant($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $getFirmIdValue = -1;
            $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
            if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                $getFirmIdValue = $getFirm ['resultSet'][0]['firm_id'];
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
                SELECT DISTINCT
                    a.id AS firm_id,
                    a.consultant_id,
                    iud.name, 
                    iud.surname,
                    iud.auth_email,
                    
		    
                    
                    ifk.network_key,
                    CASE COALESCE(NULLIF(TRIM(iud.picture), ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/' ,'image_not_found.png')
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/' ,TRIM(iud.picture)) END AS cons_picture
                FROM info_firm_profile a   
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = a.act_parent_id 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND l.deleted =0 AND l.active =0 
		LEFT JOIN info_firm_profile ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0
                INNER JOIN info_users u ON u.id = a.consultant_id AND u.role_id in (1,2,6)
                INNER JOIN info_users_detail iud ON iud.root_id = u.id AND iud.cons_allow_id = 2
                INNER JOIN info_users_communications iuc ON iuc.user_id = u.id AND iuc.cons_allow_id = 2
                INNER JOIN sys_specific_definitions sd5 ON sd5.main_group = 5 AND sd5.first_group = iuc.communications_type_id AND sd5.deleted =0 AND sd5.active =0 AND l.id = sd5.language_id
		LEFT JOIN sys_specific_definitions sd5x ON (sd5x.id =sd5.id OR sd5x.language_parent_id = sd5.id) AND sd5x.deleted =0 AND sd5x.active =0 AND lx.id = sd5x.language_id
                WHERE
                    a.act_parent_id = " . intval($getFirmIdValue) . "
                ORDER BY  iud.name, iud.surname 
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
                $errorInfoColumn = 'network_key';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @author Okan CIRAN
     * @ danısman a atanmış firma isimleri ddslick için info_firm_profile tablosundan kayıtları döndürür !!
     * @version v 1.0  19.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillConsultantAllowFirmListDds($params = array()) {
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
                    a.id,
		    cast( COALESCE(NULLIF( COALESCE(NULLIF(ax.firm_name_short	, ''), a.firm_name_short_eng) , '' ), ax.firm_name) as character varying(30)) AS name,  
                    cast( a.firm_name_short_eng as character varying(30)) AS name_eng,
                    a.active,
                    'closed' AS state_type  
                FROM info_firm_profile a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                LEFT JOIN info_firm_profile ax ON (ax.id =a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND lx.id = ax.language_id
                WHERE 
                    a.alliance_type_id >-1 and 
                    a.deleted = 0 AND
                    a.language_parent_id =0 and
                    a.active =0 and 
                    a.consultant_id = ".intval($opUserIdValue)." AND 
                    a.cons_allow_id =2
                                 ");
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ danısmanın yaptığı firma kayıtlarını döndürür !!
     * @version v 1.0  22.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsCompanyLists($params = array()) {
        try {             
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
                $sort = " fp.firm_name ";
            }

            if (isset($params['order']) && $params['order'] != "") {
                $order = trim($params['order']);
                $orderArr = explode(",", $order);
                if (count($orderArr) === 1)
                    $order = trim($params['order']);
            } else {
                $order = "ASC";
            }
            $sorguStr = null; 
                            
                            
            if (isset($params['filterRules']) && $params['filterRules'] != "") {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);
              
                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'firm_name':                              
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name)" . $sorguExpression . ' ';
                              
                                break;                            
                            
                            case 'firm_name_eng':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name_short)" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short_eng':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name_short_eng)" . $sorguExpression . ' ';

                                break; 
                            
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
            }
            $sorguStr = rtrim($sorguStr, "AND ");

            $jsonSqlOsbIds = "  
                 (  
                    SELECT DISTINCT 
                        axv.osb_id                             
                    FROM sys_osb_clusters axv
                    LEFT join info_firm_clusters bb ON bb.osb_cluster_id = axv.id AND bb.cons_allow_id=2
                    WHERE bb.firm_id = fp.act_parent_id AND axv.active =0 AND axv.deleted =0
                    LIMIT 1
                     )
            ";
            $jsonSqlClustersIds = "  
                (SELECT array_to_json(COALESCE(NULLIF(cxx,'{}'),NULL)) FROM (
                    SELECT  
                        ARRAY(   
                            SELECT DISTINCT
                                axv.id                             
                            FROM sys_osb_clusters axv
                            LEFT join info_firm_clusters bb ON bb.osb_cluster_id = axv.id AND bb.cons_allow_id=2  
                            WHERE bb.firm_id = fp.act_parent_id AND axv.active =0 AND axv.deleted =0 
                            ORDER BY axv.id) AS cxx
                            ) AS zxtable)
            ";

            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                            
                $sql = "
                    SELECT 
                        fp.id,
                        fp.act_parent_id AS firm_id ,
                        fp.firm_name,
			fp.firm_name_eng,
                        fp.firm_name_short,
                        fp.firm_name_short_eng,
                        fp.profile_public,
                        sd19.description AS state_profile_public,                        
                        fp.active,
                        sd16.description AS state_active,
                        fp.op_user_id,
                        u.username AS op_user_name,
                        fp.s_date,
                        fp.c_date,
                        ".$jsonSqlOsbIds." AS osb_id,
                        ".$jsonSqlClustersIds." AS cluster_ids    
                    FROM info_firm_profile fp
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_users u ON u.id = fp.op_user_id
                    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.first_group = fp.cons_allow_id AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0		    
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= fp.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= fp.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0    
		    WHERE fp.language_parent_id = 0 AND
			  fp.cons_allow_id = 2 AND 
                          
			  fp.consultant_id = " . intval($opUserIdValue). "
                " . $sorguStr . " 
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
            //    echo debugPDO($sql, $parameters);                
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                $affectedRows = $statement->rowCount();
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
     * @ danısmanın yaptığı firma kayıtların sayısını döndürür !!
     * @version v 1.0  22.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsCompanyListsRtc($params = array()) {
        try {                                         
            $sorguStr = null;                              
            if (isset($params['filterRules']) && $params['filterRules'] != "") {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);
              
                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'firm_name':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name)" . $sorguExpression . ' ';
                              
                                break;                            
                            
                            case 'firm_name_eng':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name_short)" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short_eng':
                                $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                                $sorguStr.=" AND LOWER(fp.firm_name_short_eng)" . $sorguExpression . ' ';

                                break; 
                            
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
            }
            $sorguStr = rtrim($sorguStr, "AND ");


            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                            
                $sql = "
                    SELECT COUNT(id) AS count FROM (                      
                        SELECT 
                        fp.id,
                        fp.act_parent_id AS firm_id ,
                        fp.firm_name,
			fp.firm_name_eng,
                        fp.firm_name_short,
                        fp.firm_name_short_eng,
                        fp.profile_public,
                        sd19.description AS state_profile_public,                        
                        fp.active,
                        sd16.description AS state_active,
                        fp.op_user_id,
                        u.username AS op_user_name,
                        fp.s_date,
                        fp.c_date
                    FROM info_firm_profile fp
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_users u ON u.id = fp.op_user_id
                    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.first_group = fp.cons_allow_id AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0		    
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= fp.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= fp.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    
		    WHERE fp.language_parent_id = 0 AND
			  fp.cons_allow_id = 2 AND
			  fp.consultant_id = " . intval($opUserIdValue). "		
                " . $sorguStr . " 
                    ) AS xtable 
                ";     
                $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);                
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                $affectedRows = $statement->rowCount();
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
     * @ info_firm_profile tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  22.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeActiveOrPassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams);
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                if (isset($params['id']) && $params['id'] != "") {
                $sql = "                 
                UPDATE info_firm_profile
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM info_firm_profile
                                WHERE id = " . intval($params['id']) . "
                ),
                op_user_id = " . intval($opUserIdValue) . "
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    //  echo debugPDO($sql, $params);
                    $update = $statement->execute();
                    $afterRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                }
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
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
                            
    
    
}
