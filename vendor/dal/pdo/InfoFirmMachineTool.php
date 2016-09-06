<?php

/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace DAL\PDO;

/**
 * Class using Zend\ServiceManager\FactoryInterface
 * created to be used by DAL MAnager
 * @
 * @author Okan CİRANĞ
 */
class InfoFirmMachineTool extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_machine_tool tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];                            
                $statement = $pdo->prepare(" 
                UPDATE info_firm_machine_tool
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($opUserIdValue) . "  
                WHERE id = " . intval($params['id']));
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
     * @ danısman tarafından - info_firm_machine_tool tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                            
    public function deleteConsAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];               
                $operationIdValue = -3;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 41, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                $sql = "                
                  INSERT INTO info_firm_machine_tool(
                        firm_id,
                        op_user_id,
                        operation_type_id,
                        sys_machine_tool_id,
                        availability_id,                   
                        act_parent_id,
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        language_id,
                        profile_public,
                        cons_allow_id,
                        language_parent_id,
                        active,
                        deleted,
                        picture,
                        total,
                        ownership_id
                        )
                        SELECT  
                            firm_id,
                            " . intval($opUserIdValue) . ",
                            " . intval($operationIdValue) . ",    
                            sys_machine_tool_id,                          
                            availability_id,                             
                            act_parent_id,
                            consultant_id,
                            consultant_confirm_type_id,
                            confirm_id,
                            language_id,                         
                            profile_public,
                            0,
                            language_parent_id,
                            1,
                            1,
                            picture,
                            total,
                            ownership_id
                        FROM info_firm_machine_tool 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                //  echo debugPDO($sql, $params);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $errorInfo = $statement_act_insert->errorInfo();
                $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_firm_machine_tool', 'id' => $params['id'],));
                if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                    $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];
                    $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                       
                }

                $xjobs = ActProcessConfirm::insert(array(
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
                                )
                );

                if ($xjobs['errorInfo'][0] != "00000" && $xjobs['errorInfo'][1] != NULL && $xjobs['errorInfo'][2] != NULL)
                    throw new \PDOException($xjobs['errorInfo']);
                
                $this->makePassive(array('id' => $params['id']));
                $this->makeConsAllowZero(array('id' => $params['id']));
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
     * @ info_firm_machine_tool tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  18.02.2016   
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                }
            }  
            $statement = $pdo->prepare("                    
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
			a.s_date,
                        a.c_date,
			a.sys_machine_tool_id,
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.machine_tool_name_eng, 
                        a.profile_public,                         
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                        a.operation_type_id,                         
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
			a.act_parent_id,  
                        a.language_code, 
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        a.active,                         
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                        a.deleted,			
			COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                        a.op_user_id,
                        u.username AS op_user,  
                        fp.owner_user_id AS owner_id ,
                        own.username as owner_username,
                        a.cons_allow_id,                        
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.availability_id ,                        
                        COALESCE(NULLIF(sd119x.description, ''), sd119.description_eng) AS state_availability,
                        a.language_parent_id ,  
                        a.total,
                        CASE COALESCE(NULLIF(a.picture, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture                      
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = a.firm_id OR fpx.language_parent_id=a.firm_id) AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users own ON own.id = fp.owner_user_id                     
		    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0 		                        
                    INNER JOIN sys_machine_tools smt ON smt.id = sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = sys_machine_tool_id OR smtx.language_parent_id = a.sys_machine_tool_id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id		    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group= a.availability_id AND sd119.deleted = 0 AND sd119.active = 0 AND sd119.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd119x ON sd119x.main_group = 19 AND sd119x.language_id = lx.id AND (sd119x.id = sd19.id OR sd119x.language_parent_id = sd119.id) AND sd119x.deleted = 0 AND sd119x.active = 0
		                        
		    ORDER BY l.priority	  
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
     * @ info_firm_machine_tool tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 15.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = " ";
            if (isset($params['id'])) {
                $addSql .= " AND a.id != " . intval($params['id']);
            }
            $sql = " 
            SELECT  
                a.sys_machine_tool_id AS name , 
                a.sys_machine_tool_id AS value , 
                a.sys_machine_tool_id = " . intval($params['machine_id']) . " AS control,
                CONCAT(a.sys_machine_tool_id, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_machine_tool a             
            WHERE a.firm_id = " . intval($params['firm_id']) . "
                AND a.sys_machine_tool_id = " . intval($params['machine_id']) . "
                AND a.active = 0 
                AND a.deleted = 0     
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
     * @ info_firm_machine_tool tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
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
                UPDATE info_firm_machine_tool
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
                UPDATE info_firm_machine_tool
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
     * @ kullanıcı info_firm_machine_tool tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmId = $getFirm ['resultSet'][0]['firm_id'];

                    $kontrol = $this->haveRecords(array('firm_id' => $getFirmId,'machine_id' => $params['machine_id'],));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                        $addSql = " op_user_id, ";
                        $addSqlValue = " " . $opUserIdValue . ",";
                        
                        $operationIdValue = -1;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 41, 'type_id' => 1,));
                        if (\Utill\Dal\Helper::haveRecord($operationId)) {
                        $operationIdValue = $operationId ['resultSet'][0]['id'];
                        }
                        $languageId = NULL;
                        $languageIdValue = 647;
                        if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                            }
                        }  
                        $ConsultantId = 1001;
                        $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_firm_machine_tool' , 
                                                                                              'operation_type_id' => $operationIdValue, 
                                                                                              'language_id' => $languageIdValue,  
                                                                                               ));
                        if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                            $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                        }                                                 
                            
                        $profilePublic= 0;
                        if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                            $profilePublic = intval($params['profile_public']);
                        }
                        
                        $ownerShipId = 1;
                        if ((isset($params['ownership_id']) && $params['ownership_id'] != "")) {
                            $ownerShipId = intval($params['ownership_id']);
                        }
                        
                        $addSql .= " language_id, ";
                        $addSqlValue .= " " . $languageIdValue . ",";

                        $sql = " 
                        INSERT INTO info_firm_machine_tool(
                             firm_id, 
                             consultant_id,
                             operation_type_id, 
                             sys_machine_tool_id,                           
                             availability_id,
                             ownership_id,
                             profile_public,
                              " . $addSql . "
                             act_parent_id,
                             total,
                             picture
                             )
                        VALUES (
                             :firm_id, 
                             " . intval($ConsultantId) . ",
                             :operation_type_id, 
                             :sys_machine_tool_id,                        
                             :availability_id, 
                              " . intval($ownerShipId) . ",
                              " . intval($profilePublic) . ",
                              " . $addSqlValue . "
                             (SELECT last_value FROM info_firm_machine_tool_id_seq),
                             :total,
                             :picture

                             )";
                        $statement = $pdo->prepare($sql);
                        $statement->bindValue(':firm_id', $getFirmId, \PDO::PARAM_INT);
                        $statement->bindValue(':operation_type_id', $operationIdValue, \PDO::PARAM_INT);
                        $statement->bindValue(':sys_machine_tool_id', $params['machine_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':availability_id', $params['availability_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':total', $params['total'], \PDO::PARAM_INT);
                        $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                        $statement->bindValue(':picture', $params['picture'], \PDO::PARAM_STR);
                      //  echo debugPDO($sql, $params);
                        $result = $statement->execute();
                        $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
                        $errorInfo = $statement->errorInfo();
                        if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                            throw new \PDOException($errorInfo[0]);
                        
                         $xjobs = ActProcessConfirm::insert(array(
                                'op_user_id' => intval($opUserIdValue),
                                'operation_type_id' => intval($operationIdValue),
                                'table_column_id' => intval($insertID),
                                'cons_id' => intval($ConsultantId),
                                'preferred_language_id' => intval($languageIdValue),
                                    )
                        );
                         if ($xjobs['errorInfo'][0] != "00000" && $xjobs['errorInfo'][1] != NULL && $xjobs['errorInfo'][2] != NULL)
                        throw new \PDOException($xjobs['errorInfo']);
                        
                        $pdo->commit();

                        return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                    } else {
                        // 23505  unique_violation
                        $errorInfo = '23505';
                        $errorInfoColumn = 'machine_id';
                        $pdo->rollback();
                        // $result = $kontrol;
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                    }
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'npk';
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
     * @ danışman info_firm_machine_tool tablosuna yeni bir kayıt oluşturur. !!
     * @version v 1.0  19.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function insertCons($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                
                $machineId = 0;
                if ((isset($params['machine_id']) && $params['machine_id'] != "")) {
                    $machineId = intval($params['machine_id']);
                }
                $firmId = 0;
                if ((isset($params['firm_id']) && $params['firm_id'] != "")) {
                    $firmId = intval($params['firm_id']);
                } 

                $kontrol = $this->haveRecords(array('firm_id' => $firmId, 'machine_id' => $machineId,));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                            
                    $operationIdValue = -1;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 41, 'type_id' => 1,));
                    if (\Utill\Dal\Helper::haveRecord($operationId)) {
                        $operationIdValue = $operationId ['resultSet'][0]['id'];
                    }
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];
                        }
                    }
                    $ConsultantId = $opUserIdValue; 
                            
                    $profilePublic = 0;
                    if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                        $profilePublic = intval($params['profile_public']);
                    }

                    $ownerShipId = 1;
                    if ((isset($params['ownership_id']) && $params['ownership_id'] != "")) {
                        $ownerShipId = intval($params['ownership_id']);
                    }
                    $availabilityId = 1;
                    if ((isset($params['availability_id']) && $params['availability_id'] != "")) {
                        $availabilityId = intval($params['availability_id']);
                    }
                    $total = 0;
                    if ((isset($params['total']) && $params['total'] != "")) {
                        $total = intval($params['total']);
                    } 

                    $sql = " 
                        INSERT INTO info_firm_machine_tool(
                             firm_id, 
                             consultant_id,
                             operation_type_id, 
                             sys_machine_tool_id,                           
                             availability_id,
                             ownership_id,
                             profile_public,
                             op_user_id,                              
                             act_parent_id,
                             total,
                             language_id,
                             cons_allow_id
                             )
                        VALUES (
                             " . intval($firmId) . ",
                             " . intval($ConsultantId) . ",
                             " . intval($operationIdValue) . ", 
                             " . intval($machineId) . ", 
                              " . intval($availabilityId) . ", 
                              " . intval($ownerShipId) . ",
                              " . intval($profilePublic) . ",
                              " . intval($opUserIdValue) . ",                              
                             (SELECT last_value FROM info_firm_machine_tool_id_seq),
                              " . intval($total) . ",
                              " . intval($languageIdValue) . ",
                              2
                             )";
                    $statement = $pdo->prepare($sql);
                    //  echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]); 
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'machine_id';
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
     * kullanıcı - info_firm_machine_tool tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];

                $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmId = $getFirm ['resultSet'][0]['firm_id'];

                    $kontrol = $this->haveRecords(array('id' => $params['id'], 'firm_id' => $getFirmId, 'machine_id' => $params['sys_machine_tool_id'],));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                        $this->makePassive(array('id' => $params['id']));
                        $operationIdValue = -2;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                        array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 41, 'type_id' => 2,));
                        if (\Utill\Dal\Helper::haveRecord($operationId)) {
                            $operationIdValue = $operationId ['resultSet'][0]['id'];
                        }
                        $languageId = NULL;
                        $languageIdValue = 647;
                        if ((isset($params['language_code']) && $params['language_code'] != "")) {
                            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                                $languageIdValue = $languageId ['resultSet'][0]['id'];
                            }
                        }

                        $availabilityId = NULL;
                        if ((isset($params['availability_id']) && $params['availability_id'] != "")) {
                            $availabilityId = intval($params['availability_id']);
                        }
                        $sysMachineToolId = NULL;
                        if ((isset($params['sys_machine_tool_id']) && $params['sys_machine_tool_id'] != "")) {
                            $sysMachineToolId = intval($params['sys_machine_tool_id']);
                        }

                        $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_machine_tool(
                        profile_public, 
                        language_id,
                        op_user_id,
                        operation_type_id,
                        act_parent_id,   
                        firm_id, 
                        availability_id, 
                        sys_machine_tool_id, 
                        consultant_id, 
                        language_parent_id,
                        total,
                        picture
                        )
                        SELECT  
                            " . intval($params['profile_public']) . " AS profile_public, 
                            " . intval($languageIdValue) . ",   
                            " . intval($opUserIdValue) . ",
                            " . intval($operationIdValue) . ",    
                            act_parent_id,                       
                            firm_id, 
                            " . intval($availabilityId) . " AS availability_id,
                            " . intval($sysMachineToolId) . " AS sys_machine_tool_id,                            
                            consultant_id,
                            language_parent_id,
                            " . intval($params['total']) . " AS total,
                            '" . $params['picture'] . "' AS picture
                        FROM info_firm_machine_tool 
                        WHERE id =  " . intval($params['id']) . " 
                        ");
                        $insert_act_insert = $statement_act_insert->execute();
                        $affectedRows = $statement_act_insert->rowCount();
                        $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
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
                                        array('table_name' => 'info_firm_machine_tool', 'id' => $params['id'],));
                        if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                            $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];
                            // $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                       
                        }

                        $xjobs = ActProcessConfirm::insert(array(
                                    'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                                    'operation_type_id' => intval($operationIdValue), // operasyon 
                                    'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                                    'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                                    'preferred_language_id' => intval($languageIdValue), // dil bilgisi
                                        )
                        );

                        if ($xjobs['errorInfo'][0] != "00000" && $xjobs['errorInfo'][1] != NULL && $xjobs['errorInfo'][2] != NULL)
                            throw new \PDOException($xjobs['errorInfo']);
                        
                        $pdo->commit();
                        return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                    } else {
                        // 23505  unique_violation
                        $errorInfo = '23505';
                        $pdo->rollback();
                        $result = $kontrol;
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
                    }
                } else {
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'npk';
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
     * @ danışman - info_firm_machine_tool tablosundan secilmiş kaydı günceller. !!
     * @version v 1.0  19.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */   
    public function updateCons($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                            
                $operationIdValue = -2;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 41, 'type_id' => 2,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }                            
                $profilePublic = 0;
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                    $profilePublic = intval($params['profile_public']);
                }
                $ownerShipId = 1;
                if ((isset($params['ownership_id']) && $params['ownership_id'] != "")) {
                    $ownerShipId = intval($params['ownership_id']);
                }
                $availabilityId = 1;
                if ((isset($params['availability_id']) && $params['availability_id'] != "")) {
                    $availabilityId = intval($params['availability_id']);
                }
                $total = 0;
                if ((isset($params['total']) && $params['total'] != "")) {
                    $total = intval($params['total']);
                }
                $statement_act_insert = $pdo->prepare(" 
                 INSERT INTO info_firm_machine_tool(
                        firm_id, 
                        consultant_id,
                        operation_type_id, 
                        sys_machine_tool_id,
                        availability_id,
                        ownership_id,
                        profile_public,
                        op_user_id,
                        act_parent_id,
                        total,
                        language_id,
                        language_parent_id,
                        cons_allow_id,
                        picture
                        )
                        SELECT  
                            firm_id,
                            consultant_id,
                            " . intval($operationIdValue) . ", 
                            sys_machine_tool_id, 
                            " . intval($availabilityId) . ", 
                            " . intval($ownerShipId) . ",
                            " . intval($profilePublic) . ",
                            " . intval($opUserIdValue) . ",
                            act_parent_id,
                            " . intval($total) . ",
                            " . intval($languageIdValue) . ", 
                            language_parent_id, 
                            2,
                            picture
                        FROM info_firm_machine_tool 
                        WHERE id =  " . intval($params['id']) . " 
                        ");
                $insert_act_insert = $statement_act_insert->execute();
                $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
                $errorInfo = $statement_act_insert->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                
                $this->makePassive(array('id' => $params['id']));
                $this->makeConsAllowZero(array('id' => $params['id']));
                
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
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan kayıtları döndürür !!
     * @version v 1.0  18.02.2016
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
            $sort = "a.language_id,l.priority,fp.firm_name,a.s_date ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            $order = "ASC";
        }
        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($params['language_code']) && $params['language_code'] != "")) {
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            }
        } 

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "                      
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
			a.s_date,
                        a.c_date,
			a.sys_machine_tool_id,
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.machine_tool_name_eng, 
                        a.profile_public,                         
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                        a.operation_type_id,                         
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
			a.act_parent_id,  
                        a.language_code, 
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        a.active,                         
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                        a.deleted,			
			COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                        a.op_user_id,
                        u.username AS op_user,  
                        fp.owner_user_id AS owner_id ,
                        own.username as owner_username,
                        a.cons_allow_id,                        
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.availability_id ,                        
                        COALESCE(NULLIF(sd119x.description, ''), sd119.description_eng) AS state_availability,
                        a.language_parent_id ,  
                        a.total,
                        CASE COALESCE(NULLIF(a.picture, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture                      
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = a.firm_id OR fpx.language_parent_id=a.firm_id) AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users own ON own.id = fp.owner_user_id                     
		    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0 		                        
                    INNER JOIN sys_machine_tools smt ON smt.id = sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = sys_machine_tool_id OR smtx.language_parent_id = a.sys_machine_tool_id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group= a.availability_id AND sd119.deleted = 0 AND sd119.active = 0 AND sd119.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd119x ON sd119x.main_group = 19 AND sd119x.language_id = lx.id AND (sd119x.id = sd19.id OR sd119x.language_parent_id = sd119.id) AND sd119x.deleted = 0 AND sd119x.active = 0		     
		   
                    WHERE a.language_parent_id = 0 AND a.deleted =0
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
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');

            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                }
            }  
            $whereSQL = " WHERE a.language_parent_id = 0 AND a.deleted =0 "; 

            $sql = "
                 SELECT 
                    COUNT(a.id) AS COUNT                    
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users own ON own.id = fp.owner_user_id                     		                        
                    INNER JOIN sys_machine_tools smt ON smt.id = sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id                   
		    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group= a.availability_id AND sd119.deleted = 0 AND sd119.active = 0 AND sd119.language_parent_id =0
		   
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
     * @ info_firm_machine_tool tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  18.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare("                 
                    
                    INSERT INTO info_firm_machine_tool(
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
                            FROM info_firm_machine_tool c
                            LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                            WHERE c.id = " . intval($params['id']) . "
                    ) AS xy  
                    WHERE xy.language_main_code NOT IN 
                        (SELECT 
                            DISTINCT language_code 
                         FROM info_firm_machine_tool cx 
                         WHERE (cx.language_parent_id = " . intval($params['id']) . "
						OR cx.id = " . intval($params['id']) . "
					) AND cx.deleted =0 AND cx.active =0)

                            ");

            //   $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);

            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
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
     * 
     * @author Okan CIRAN
     * @ text alanları doldurmak için info_firm_machine_tool tablosundan tek kayıt döndürür !! 
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
     * @version v 1.0  18.02.2016
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
                    FROM info_firm_machine_tool a    
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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 18.02.2016 
     * @param type $id
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function deletedAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                            
                $this->makePassive(array('id' => $params['id']));              
                $operationIdValue = -3;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 41, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                $sql = "                
                  INSERT INTO info_firm_machine_tool(
                        firm_id,
                        op_user_id,
                        operation_type_id,
                        sys_machine_tool_id,
                        availability_id,                   
                        act_parent_id,
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        language_id,
                        language_code,
                        cons_allow_id,
                        language_parent_id,
                        active,
                        deleted,
                        picture,
                        total
                        )
                        SELECT  
                            firm_id,
                            " . intval($opUserIdValue) . ",
                            " . intval($operationIdValue) . ",    
                            sys_machine_tool_id,                          
                            availability_id,                             
                            act_parent_id,
                            consultant_id,
                            consultant_confirm_type_id,
                            confirm_id,
                            language_id,                         
                            language_code,
                            cons_allow_id,
                            language_parent_id,
                            1,
                            1,
                            picture,
                            total
                        FROM info_firm_machine_tool 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                //  echo debugPDO($sql, $params);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $errorInfo = $statement_act_insert->errorInfo();
                $insertID = $pdo->lastInsertId('info_firm_machine_tool_id_seq');
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_firm_machine_tool', 'id' => $params['id'],));
                if (\Utill\Dal\Helper::haveRecord($consIdAndLanguageId)) {
                    $ConsultantId = $consIdAndLanguageId ['resultSet'][0]['consultant_id'];
                    $languageIdValue = $consIdAndLanguageId ['resultSet'][0]['language_id'];                       
                }

                $xjobs = ActProcessConfirm::insert(array(
                            'op_user_id' => intval($opUserIdValue), // işlemi yapan user
                            'operation_type_id' => intval($operationIdValue), // operasyon 
                            'table_column_id' => intval($insertID), // işlem yapılan tablo id si
                            'cons_id' => intval($ConsultantId), // atanmış olan danısman 
                            'preferred_language_id' => intval($languageIdValue), // dil bilgisi
                                )
                );

                if ($xjobs['errorInfo'][0] != "00000" && $xjobs['errorInfo'][1] != NULL && $xjobs['errorInfo'][2] != NULL)
                    throw new \PDOException($xjobs['errorInfo']);
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
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan user in firmasının kayıtlarını döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmMachineTools($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }
                $firmIdValue=-1;
                $firmId = InfoUsers::getUserFirmId(array('user_id' =>$opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($firmId)) {
                    $firmIdValue = $firmId ['resultSet'][0]['firm_id'];
                } 
                $sql = "
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
			a.s_date,
                        a.c_date,
			a.sys_machine_tool_id,
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.machine_tool_name_eng, 
                        a.profile_public,                         
                        COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                        a.operation_type_id,                         
                        COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
			a.act_parent_id,  
                        a.language_code, 
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        a.active,                         
                        COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                        a.deleted,			
			COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                        a.op_user_id,
                        u.username AS op_user,  
                        fp.owner_user_id AS owner_id ,
                        own.username as owner_username,
                        a.cons_allow_id,                        
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.availability_id ,                        
                        COALESCE(NULLIF(sd119x.description, ''), sd119.description_eng) AS state_availability,
                        a.language_parent_id ,  
                        a.total,
                        CASE COALESCE(NULLIF(a.picture, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture                      
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = a.firm_id OR fpx.language_parent_id=a.firm_id) AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users own ON own.id = fp.owner_user_id                     
		    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0 		                        
                    INNER JOIN sys_machine_tools smt ON smt.id = sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = sys_machine_tool_id OR smtx.language_parent_id = a.sys_machine_tool_id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group= a.availability_id AND sd119.deleted = 0 AND sd119.active = 0 AND sd119.language_parent_id =0
                  
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.main_group = 19 AND sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    LEFT JOIN sys_specific_definitions sd119x ON sd119x.main_group = 19 AND sd119x.language_id = lx.id AND (sd119x.id = sd19.id OR sd119x.language_parent_id = sd119.id) AND sd119x.deleted = 0 AND sd119x.active = 0		     
		    WHERE a.language_parent_id = 0 AND
                        a.deleted =0 AND 
                        a.firm_id = ".  intval($firmIdValue)."
		    ORDER BY l.priority                       
              
                ";
                $statement = $pdo->prepare($sql);
                //  echo debugPDO($sql, $parameters);                
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                $affectedRows = $statement->rowCount();
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
     * @ Gridi doldurmak için info_firm_machine_tool tablosundan user in firmasının çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmMachineToolsRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];                
                $firmIdValue=-1;                
                $firmId = InfoUsers::getUserFirmId(array('user_id' =>$opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($firmId)) {
                    $firmIdValue = $firmId ['resultSet'][0]['firm_id'];
                }  
                
                $whereSQL = " WHERE a.language_parent_id = 0 AND
                                    a.deleted =0 AND 
                                    a.firm_id = ". intval($firmIdValue);              

                $sql = "
                    SELECT 
                       COUNT(a.id) AS COUNT 
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_users own ON own.id = fp.owner_user_id                     		                        
                    INNER JOIN sys_machine_tools smt ON smt.id = sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id                   
		    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd119 ON sd119.main_group = 19 AND sd119.first_group= a.availability_id AND sd119.deleted = 0 AND sd119.active = 0 AND sd119.language_parent_id =0
		    " . $whereSQL . " 
                    ";
                $statement = $pdo->prepare($sql);
                //   echo debugPDO($sql, $params);  
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
     * @ tree doldurmak için sys_machine_tool_groups tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  15.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmMachineToolGroups($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                }
            }  
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            }
            $statement = $pdo->prepare("    
                SELECT                    
                    a.id,                     
                    COALESCE(NULLIF(ax.group_name, ''), a.group_name_eng) as name ,
                    a.parent_id,
                    a.active ,
                    CASE
                        (CASE 
                            (SELECT DISTINCT 1 state_type FROM sys_machine_tool_groups WHERE parent_id = a.id AND deleted = 0)    
                             WHEN 1 THEN 'closed'
                             ELSE 'open'   
                             END ) 
                         WHEN 'open' THEN COALESCE(NULLIF((SELECT DISTINCT 'closed' FROM sys_machine_tools mz WHERE mz.machine_tool_grup_id =a.id AND mz.deleted = 0), ''), 'open')   
                    ELSE 'closed'
                    END AS state_type,
                    CASE
                        (SELECT DISTINCT 1 parent_id FROM sys_machine_tool_groups WHERE id = a.id AND deleted = 0 AND parent_id =0 )    
                        WHEN 1 THEN 'true'
                    ELSE 'false'   
                    END AS root_type,
                    a.icon_class,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_machine_tool_groups WHERE parent_id = a.id AND deleted = 0)    
                         WHEN 1 THEN 'false'
                    ELSE 'true'   
                    END AS last_node,
                    'false' as machine
                FROM sys_machine_tool_groups a  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted= 0 AND ax.active =0 AND ax.language_id = lx.id
                WHERE                    
                    a.parent_id = " . intval($parentId) . " AND 
                    a.deleted = 0 AND 
                    a.language_parent_id =0 
                ORDER BY name
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
     * @ userin elemanı oldugu firmanın makina kayıtlarını döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachines($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
           $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $addSql = "";
                $firmIdValue=-1;                
                $firmId = InfoUsers::getUserFirmId(array('user_id' =>$opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($firmId)) {
                    $firmIdValue = $firmId ['resultSet'][0]['firm_id'];
                }  
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
                }  

                if (isset($params['machine_id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['machine_id']) . " ";
                }

                $sql = " 
                    SELECT 
                        a.id,
                        cast(a.sys_machine_tool_id as text) AS machine_id,
                        m.name as manufacturer_name,
                        COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng) AS machine_tool_grup_names,
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.model,
                        cast(smt.model_year AS text) AS model_year,
                        fp.act_parent_id,
                        a.total,
                        CASE COALESCE(NULLIF(a.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                    
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id                      
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_parent_id =0
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    INNER JOIN sys_machine_tool_groups smtg ON smtg.active =0 AND smtg.deleted = 0 AND smtg.id = smt.machine_tool_grup_id AND smtg.language_parent_id =0
		    LEFT JOIN sys_machine_tool_groups smtgx ON smtgx.active =0 AND smtgx.deleted = 0 AND (smtgx.id = smtg.id OR smtgx.language_parent_id = smtg.id )AND smtgx.language_id = lx.id
                    INNER JOIN sys_manufacturer m ON m.id = smt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 AND m.language_parent_id =0
                    LEFT JOIN sys_manufacturer mx ON (mx.id = m.id OR mx.language_parent_id = m.id) AND mx.language_id = lx.id AND mx.deleted =0 AND mx.active =0        
                    WHERE a.language_parent_id = 0 AND
                        a.deleted =0 AND 
                        a.active =0 AND
                        a.firm_id = ".  intval($firmIdValue)."
                " . $addSql . "
                ORDER BY machine_tool_grup_names, manufacturer_name,machine_tool_names                
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
     * @ treeyi dolduran servisde sys_machine_tool tablosundan çekilen kayıt sayısını döndürür !!
     * @version v 1.0  25.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachinesRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $addSql = "";

                $firmIdValue=-1;                
                $firmId = InfoUsers::getUserFirmId(array('user_id' =>$opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($firmId)) {
                    $firmIdValue = $firmId ['resultSet'][0]['firm_id'];
                }                  

                if (isset($params['machine_id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['machine_id']) . " ";
                }

                $sql = " 
                    SELECT 
                         COUNT(a.id ) AS COUNT                      
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id                      
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id                    
		    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id		    
                    INNER JOIN sys_manufacturer m ON m.id = smt.manufactuer_id AND m.language_id = l.id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0                     
                    WHERE a.language_parent_id = 0 AND
                        a.deleted =0 AND 
                        a.active =0 AND
                        a.firm_id = ".  intval($firmIdValue)."
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
     * @ tree doldurmak için sys_machine_tool tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachineProperties($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $addSql = "";
                
                $firmIdValue=-1;                
                $firmId = InfoUsers::getUserFirmId(array('user_id' =>$opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($firmId)) {
                    $firmIdValue = $firmId ['resultSet'][0]['firm_id'];
                } 
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
                }  

                if (isset($params['machine_id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['machine_id']) . " ";
                }

                $sql = " 
                SELECT    
                    smtp.id,  
                    a.sys_machine_tool_id AS machine_id ,		   
                    COALESCE(NULLIF(pdx.property_name, ''), pd.property_name_eng) AS property_names,
                    pd.property_name_eng,
                    smtp.property_value, 
                    u.id AS unit_id,
                    COALESCE(NULLIF(u.unitcode,''), u.unitcode_eng) AS unitcodes,
                    CASE COALESCE(NULLIF(a.picture, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture
                FROM info_firm_machine_tool a
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  		
		LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0				
                INNER JOIN info_firm_profile ifp ON ifp.act_parent_id = a.firm_id AND ifp.active =0 AND ifp.deleted =0 AND ifp.language_id = l.id AND ifp.language_parent_id =0 
                INNER JOIN info_firm_keys ifk ON ifp.act_parent_id = ifk.firm_id                      
                INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.language_id = l.id AND smt.deleted =0 AND smt.active=0                
                INNER JOIN sys_machine_tool_properties smtp ON smtp.machine_tool_id = a.sys_machine_tool_id AND smtp.language_id = l.id                
                INNER JOIN sys_machine_tool_property_definition pd ON pd.id = smtp.machine_tool_property_definition_id AND pd.language_id = l.id AND pd.deleted =0 AND pd.active=0
                LEFT JOIN sys_machine_tool_property_definition pdx ON (pdx.id = pd.id OR pdx.language_parent_id = pd.id) AND pdx.active =0 AND pdx.deleted = 0 AND pdx.language_id = lx.id                
                LEFT JOIN sys_units u ON u.id = smtp.unit_id AND u.language_id = l.id AND u.deleted =0 AND u.active=0
                WHERE a.deleted =0 AND 
                    a.active =0 AND
                    a.language_parent_id =0 AND 
                    a.firm_id = ".  intval($firmIdValue)." 
                   " . $addSql . "
                ORDER BY a.sys_machine_tool_id  
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
     * @ Guest için, tree doldurmak için firma makina sayılarını ve gruplarını sys_machine_tool tablosundan döndürür !!
     * @version v 1.0  15.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmMachineGroupsCounts($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }

            $sql = " 
            SELECT machine_grup_id, sum(machine_count) AS machine_count ,group_name FROM (
                SELECT                     
                   mtg.id AS machine_grup_id ,
                   SUM(a.total) AS machine_count,
                   COALESCE(NULLIF(COALESCE(NULLIF(mtgx.group_name, ''),  mtg.group_name_eng), ''),  mtg.group_name) AS group_name
                FROM info_firm_machine_tool a
                INNER JOIN info_firm_keys fk ON fk.firm_id = a.firm_id 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0
                INNER JOIN sys_machine_tools mt ON mt.id = a.sys_machine_tool_id AND mt.deleted =0 AND mt.active =0 AND mt.language_parent_id =0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = mt.machine_tool_grup_id AND mtg.deleted =0 AND mtg.active =0 AND mt.language_parent_id =0 
                LEFT JOIN sys_machine_tool_groups mtgx ON (mtgx.id =  mtg.id  OR mtgx.language_parent_id = mtg.id) AND mtgx.deleted =0 AND mtgx.active =0 AND  lx.id = mtgx.language_id
                WHERE 
                    a.deleted =0 AND a.active =0 AND
                    a.profile_public =0 AND
                    fk.network_key = '".$params['network_key']."' AND
                    a.language_parent_id =0
                GROUP BY a.sys_machine_tool_id,mtg.id, mtg.group_name, mtgx.group_name, mtg.group_name_eng 
                ) as xtable 
            GROUP BY machine_grup_id,group_name
            ORDER BY group_name  
                                 ";
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
     * @ tree doldurmak için firma makina sayılarını ve gruplarını sys_machine_tool tablosundan döndürür !!
     * @version v 1.0  15.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmMachineGroupsCountsGuests($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }

            $sql = " 
            SELECT machine_grup_id, sum(machine_count) AS machine_count ,group_name FROM (
                SELECT                     
                   mtg.id AS machine_grup_id ,
                   SUM(a.total) AS machine_count,
                   COALESCE(NULLIF(COALESCE(NULLIF(mtgx.group_name, ''),  mtg.group_name_eng), ''),  mtg.group_name) AS group_name
                FROM info_firm_machine_tool a
                INNER JOIN info_firm_keys fk ON fk.firm_id = a.firm_id 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND l.deleted =0 AND l.active =0
                INNER JOIN sys_machine_tools mt ON mt.id = a.sys_machine_tool_id AND mt.deleted =0 AND mt.active =0 AND mt.language_parent_id =0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = mt.machine_tool_grup_id AND mtg.deleted =0 AND mtg.active =0 AND mt.language_parent_id =0 
                LEFT JOIN sys_machine_tool_groups mtgx ON (mtgx.id =  mtg.id  OR mtgx.language_parent_id = mtg.id) AND mtgx.deleted =0 AND mtgx.active =0 AND  lx.id = mtgx.language_id
                WHERE 
                    a.language_parent_id =0 AND 
                    a.profile_public =0 AND 
                    a.cons_allow_id =2 AND
                    fk.network_key = '".$params['network_key']."'                    
                GROUP BY a.sys_machine_tool_id,mtg.id, mtg.group_name, mtgx.group_name, mtg.group_name_eng 
                ) as xtable 
            GROUP BY machine_grup_id,group_name
            ORDER BY group_name  
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
     * @ userin elemanı oldugu firmanın makina kayıtlarını döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachinesNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
           $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {               
                $addSql = "";
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
                }  

                if (isset($params['machine_id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['machine_id']) . " ";
                }
                if (isset($params['machine_grup_id'])) {
                    $addSql .= " AND smt.machine_tool_grup_id = " . intval($params['machine_grup_id']) . " ";
                }

                $sql = "                     
                    SELECT 
                        a.id,
                        cast(a.sys_machine_tool_id AS text) AS machine_id,
                        m.name AS manufacturer_name,
                        COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng) AS machine_tool_grup_names,
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_names,
                        smt.model,
                        cast(smt.model_year AS text) AS model_year,
                        smt.machine_code AS series,
                        fp.act_parent_id AS firm_id,
                        a.total,
                        CASE COALESCE(NULLIF(a.picture, ''),'-')
                            WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(fk.folder_name ,'/',fk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                    
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN info_firm_keys fk ON fp.act_parent_id = fk.firm_id 
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id
		    LEFT JOIN sys_machine_tool_groups smtgx ON (smtgx.id = smtg.id OR smtg.language_parent_id = smtg.id )AND smtgx.language_id = lx.id
                    INNER JOIN sys_manufacturer m ON m.id = smt.manufactuer_id AND m.language_id = l.id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                    LEFT JOIN sys_manufacturer mx ON (mx.id = m.id OR mx.language_parent_id = m.id) AND mx.language_id = lx.id AND mx.deleted =0 AND mx.active =0        
                    WHERE 
                        a.deleted =0 AND a.active =0 AND
                        a.profile_public =0 AND
                        fk.network_key = '".$params['network_key']."' AND
                        a.language_parent_id =0                         
                " . $addSql . "
                ORDER BY machine_tool_grup_names, manufacturer_name,machine_tool_names     
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
     * @ userin elemanı oldugu firmanın makina kayıtları sayısını döndürür !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmMachinesNpkRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {               
                $addSql = "";

                 if (isset($params['machine_id'])) {
                    $addSql .= " AND a.sys_machine_tool_id = " . intval($params['machine_id']) . " ";
                }
                if (isset($params['machine_grup_id'])) {
                    $addSql .= " AND smt.machine_tool_grup_id = " . intval($params['machine_grup_id']) . " ";
                }

                $sql = " 
                    SELECT 
                         COUNT(a.id ) AS COUNT                      
                     FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = 647 AND lx.deleted =0 AND lx.active =0                    
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys fk ON fp.act_parent_id =  fk.firm_id                      
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id
		    LEFT JOIN sys_machine_tool_groups smtgx ON (smtgx.id = smtg.id OR smtg.language_parent_id = smtg.id )AND smtgx.language_id = lx.id
                    INNER JOIN sys_manufacturer m ON m.id = smt.manufactuer_id AND m.language_id = l.id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                    LEFT JOIN sys_manufacturer mx ON (mx.id = m.id OR mx.language_parent_id = m.id) AND mx.language_id = lx.id AND mx.deleted =0 AND mx.active =0        
                    WHERE 
                        a.deleted =0 AND a.active =0 AND
                        a.profile_public =0 AND
                        fk.network_key = '".$params['network_key']."' AND
                        a.language_parent_id =0 
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
     * @ Tüm firmaların makina parklarının kayıtlarını döndürür !!
     * @version v 1.0  06.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillAllCompanyMachineLists($params = array()) {
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
            $addSql = NULL;
            if (isset($params['sort']) && $params['sort'] != "") {
                $sort = trim($params['sort']);
                $sortArr = explode(",", $sort);
                if (count($sortArr) === 1)
                    $sort = trim($params['sort']);
            } else {
                $sort = " firm_name, machine_tool_grup_name, manufacturer_name, machine_tool_name ";
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
                            
                            
            if (isset($params['filterRules']) && $params['filterRules'] != "") {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);
              
                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'firm_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng)" . $sorguExpression . ' ';
                              
                                break;
                            case 'firm_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND fp.firm_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'manufacturer_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND m.manufacturer_name" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_grup_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'model':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.model" . $sorguExpression . ' ';

                                break;
                            case 'model_year':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND CAST(smt.model_year AS TEXT)" . $sorguExpression . ' ';

                                break;
                            case 'series':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.machine_code" . $sorguExpression . ' ';

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
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }
                            
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                  $addSql .= " AND fk.network_key = '".$params['network_key']."'";  
                }

                $sql = "
                    SELECT 
                        a.id,
			COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
			fp.firm_name_eng,
                        CAST(a.sys_machine_tool_id AS text) AS machine_id,
                        m.name AS manufacturer_name,
                        COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng) AS machine_tool_grup_name,
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_name,
                        smt.model,
                        smt.model_year,
                        smt.machine_code AS series,
                        fp.act_parent_id AS firm_id,
                        a.total,
                        CASE COALESCE(NULLIF(a.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(fk.folder_name ,'/',fk.machines_folder,'/' ,COALESCE(NULLIF(a.picture, ''),'image_not_found.png')) END AS picture,
                        fk.network_key                        
                    FROM info_firm_machine_tool a 
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                    
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id = 2 AND fp.language_parent_id =0                      
                    LEFT JOIN info_firm_profile fpx ON fpx.act_parent_id = fp.act_parent_id AND fpx.cons_allow_id = 2 AND fpx.language_id = lx.id
                    INNER JOIN info_firm_keys fk ON fp.act_parent_id = fk.firm_id                      
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id
		    LEFT JOIN sys_machine_tool_groups smtgx ON (smtgx.id = smtg.id OR smtg.language_parent_id = smtg.id )AND smtgx.language_id = lx.id
                    INNER JOIN sys_manufacturer m ON m.id = smt.manufactuer_id AND m.language_id = l.id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                    LEFT JOIN sys_manufacturer mx ON (mx.id = m.id OR mx.language_parent_id = m.id) AND mx.language_id = lx.id AND mx.deleted =0 AND mx.active =0        
                    WHERE 
                        a.deleted =0 AND 
                        a.active =0 AND
                        a.profile_public =0 AND
                        a.language_parent_id =0 AND 
                        a.cons_allow_id = 2
                " . $addSql . "
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
                //   echo debugPDO($sql, $parameters);                
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
     * @ Tüm firmaların makina parklarının kayıtlarının sayısını döndürür !!
     * @version v 1.0  06.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillAllCompanyMachineListsRtc($params = array()) {
        try {       
            $addSql = NULL;  
            $sorguStr = null;                            
            if (isset($params['filterRules']) && $params['filterRules'] != "") {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);
              
                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'firm_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng)" . $sorguExpression . ' ';
                              
                                break;
                            case 'firm_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND fp.firm_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'manufacturer_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND m.manufacturer_name" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_grup_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'model':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.model" . $sorguExpression . ' ';

                                break;
                            case 'model_year':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND CAST(smt.model_year AS TEXT)" . $sorguExpression . ' ';

                                break;
                            case 'series':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.machine_code" . $sorguExpression . ' ';

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
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }
                            
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                  $addSql .= " AND fk.network_key = '".$params['network_key']."'";  
                }

                $sql = "
                    SELECT 
                       COUNT(a.id) AS COUNT                    
                    FROM info_firm_machine_tool a                     
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                    
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id = 2 AND fp.language_parent_id =0                      
                    LEFT JOIN info_firm_profile fpx ON fpx.act_parent_id = fp.act_parent_id AND fpx.cons_allow_id = 2 AND fpx.language_id = lx.id
                    INNER JOIN info_firm_keys fk ON fp.act_parent_id = fk.firm_id                      
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
		    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id
		    LEFT JOIN sys_machine_tool_groups smtgx ON (smtgx.id = smtg.id OR smtg.language_parent_id = smtg.id )AND smtgx.language_id = lx.id
                    INNER JOIN sys_manufacturer m ON m.id = smt.manufactuer_id AND m.language_id = l.id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                    LEFT JOIN sys_manufacturer mx ON (mx.id = m.id OR mx.language_parent_id = m.id) AND mx.language_id = lx.id AND mx.deleted =0 AND mx.active =0        
                    WHERE 
                        a.deleted =0 AND 
                        a.active =0 AND
                        a.profile_public =0 AND
                        a.language_parent_id =0 AND 
                        a.cons_allow_id = 2
                " . $addSql . "
                " . $sorguStr . " 
                ";
                $statement = $pdo->prepare($sql);
                            
                $statement = $pdo->prepare($sql);
                //  echo debugPDO($sql, $parameters);                
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
     * @ danısman üzerindeki firmaların makina parklarının kayıtlarını döndürür !!
     * @version v 1.0  19.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsCompanyMachineLists($params = array()) {
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
                $sort = " firm_name ,machine_tool_name ";
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
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng)" . $sorguExpression . ' ';
                              
                                break;                            
                            
                            case 'machine_tool_grup_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smtg.group_name" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.machine_tool_name" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.machine_tool_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'state_availability':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd21.description" . $sorguExpression . ' ';

                                break;
                            case 'state_ownership':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd22.description" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND fp.firm_name_short" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND fp.firm_name_short_eng" . $sorguExpression . ' ';

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
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                            
                $sql = "
                    SELECT 
                        a.id,
                        a.firm_id,
                        fp.firm_name,
                        fp.firm_name_short,
                        fp.firm_name_short_eng,
			a.sys_machine_tool_id,
                        smt.machine_tool_name,
                        smt.machine_tool_name_eng,
                        a.total,
                        smt.machine_tool_grup_id,
                        smtg.group_name AS machine_tool_grup_name,                        
                        a.profile_public,
                        sd19.description AS state_profile_public,
                        a.availability_id,
                        sd21.description AS state_availability, 
			a.ownership_id,
                        sd22.description AS state_ownership,
                        a.active,
                        sd16.description AS state_active,
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.s_date,
                        a.c_date
                    FROM info_firm_profile fp
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    INNER JOIN info_firm_machine_tool a ON fp.act_parent_id = a.firm_id AND a.language_parent_id =0 AND a.cons_allow_id = 2 AND a.deleted =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id AND smtg.active =0 AND smtg.deleted =0 
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.first_group = a.cons_allow_id AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0		    
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd21 ON sd21.main_group = 21 AND sd21.first_group= a.availability_id AND sd21.deleted = 0 AND sd21.active = 0 AND sd21.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd22 ON sd22.main_group = 22 AND sd22.first_group= a.ownership_id AND sd22.deleted = 0 AND sd22.active = 0 AND sd22.language_parent_id =0                  
		    WHERE fp.language_parent_id = 0 AND
			  fp.cons_allow_id = 2 AND			  
			  a.consultant_id =  " . intval($opUserIdValue). "		
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
     * @ danısman üzerindeki firmaların makina parklarının kayıtlarının sayısını döndürür !!
     * @version v 1.0  19.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsCompanyMachineListsRtc($params = array()) {
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
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng)" . $sorguExpression . ' ';
                              
                                break;                            
                            
                            case 'machine_tool_grup_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smtg.group_name" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.machine_tool_name" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.machine_tool_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'state_availability':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd21.description" . $sorguExpression . ' ';

                                break;
                            case 'state_ownership':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd22.description" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND fp.firm_name_short" . $sorguExpression . ' ';

                                break;
                            case 'firm_name_short_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND fp.firm_name_short_eng" . $sorguExpression . ' ';

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
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                            
                $sql = "
                    SELECT COUNT(id) AS count FROM (
                    SELECT 
                        a.id,
                        a.firm_id,
                        fp.firm_name,
                        fp.firm_name_short,
                        fp.firm_name_short_eng,
			a.sys_machine_tool_id,
                        smt.machine_tool_name,
                        smt.machine_tool_name_eng,
                        a.total,
                        smt.machine_tool_grup_id,
                        smtg.group_name AS machine_tool_grup_name,                        
                        a.profile_public,
                        sd19.description AS state_profile_public,
                        a.availability_id,
                        sd21.description AS state_availability, 
			a.ownership_id,
                        sd22.description AS state_ownership,
                        a.active,
                        sd16.description AS state_active,
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.s_date,
                        a.c_date
                    FROM info_firm_profile fp
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    INNER JOIN info_firm_machine_tool a ON fp.act_parent_id = a.firm_id AND a.language_parent_id =0 AND a.cons_allow_id = 2 AND a.deleted =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id AND smtg.active =0 AND smtg.deleted =0 
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.first_group = a.cons_allow_id AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0		    
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd21 ON sd21.main_group = 21 AND sd21.first_group= a.availability_id AND sd21.deleted = 0 AND sd21.active = 0 AND sd21.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd22 ON sd22.main_group = 22 AND sd22.first_group= a.ownership_id AND sd22.deleted = 0 AND sd22.active = 0 AND sd22.language_parent_id =0                  
		    WHERE fp.language_parent_id = 0 AND
			  fp.cons_allow_id = 2 AND			  
			  a.consultant_id =  " . intval($opUserIdValue). "		
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
     * @ sys_machine_tools tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  16.05.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeActiveOrPassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                if (isset($params['id']) && $params['id'] != "") {
                $sql = "                 
                UPDATE info_firm_machine_tool
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM info_firm_machine_tool
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
