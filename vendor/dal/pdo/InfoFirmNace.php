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
 * @author Okan CIRAN
 */
class InfoFirmNace extends \DAL\DalSlim {

    /**

     * @author Okan CIRAN
     * @ info_firm_nace tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  18.01.2017
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE info_firm_nace
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id = " . intval($params['id']));
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
     * @ info_firm_nace tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  18.01.2017   
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
		    COALESCE(NULLIF(COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng), ''), fp.firm_name) AS firm_name,
		    a.nacecode_id, 	
		    COALESCE(NULLIF(COALESCE(NULLIF(sncx.description, ''), snc.description_eng), ''), snc.description) AS nace_code,		    		                        
                    a.active,
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,
		    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_names,
                    a.op_user_id,
                    u.username AS op_username,
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,		    
                    a.confirm_id,
                    a.cons_allow_id,
                    COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow                    
                FROM info_firm_profile fp                                
                INNER JOIN info_firm_nace a ON a.firm_id = fp.act_parent_id AND a.cons_allow_id=2 
                INNER JOIN sys_nace_codes snc ON snc.id = a.nacecode_id AND snc.active =0 AND snc.deleted =0                 
                INNER JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . "  AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_nace_codes sncx ON (sncx.id = snc.id OR sncx.language_parent_id = snc.id) AND sncx.language_id = lx.id  AND sncx.deleted = 0 AND sncx.active = 0                
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.act_parent_id OR fpx.act_parent_id=fp.act_parent_id ) AND fpx.cons_allow_id=2 AND fpx.language_id = lx.id
                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = 647 AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0                
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_parent_id =0 AND sd16.deleted = 0 AND sd16.active = 0

		LEFT JOIN sys_operation_types opx ON opx.id = op.id AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0
		LEFT JOIN sys_specific_definitions sd14x ON (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.language_id = lx.id  AND sd14x.deleted = 0 AND sd14x.active = 0                
                
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0                
	        WHERE fp.language_parent_id = 0 AND
                    fp.active =0 and fp.deleted =0
                ORDER BY firm_name
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
     * @ info_firm_nace tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  18.01.2017
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_firm_nace
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
     * @ kayıtlı kullanıcılar info_firm_nace tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18.01.2017
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $kontrol = $this->haveRecords($params);
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));               
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $operationIdValue = -1;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 52, 'type_id' => 1,));
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
                            $profilePublic =$params['profile_public'];                       
                    }
                    $nacecodeId = 0;
                    if ((isset($params['nacecode_id']) && $params['nacecode_id'] != "")) {
                            $nacecodeId =$params['nacecode_id'];                       
                    }

                    $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_firm_nace',
                                'operation_type_id' => $operationIdValue,
                                'language_id' => $languageIdValue,
                    ));
                    if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                        $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                    } else {
                        $ConsultantId = 1001;
                    }

                    $statement = $pdo->prepare("
                        INSERT INTO info_firm_nace (
                                firm_id, 
                                profile_public, 
                                nacecode_id,
                                consultant_id,
                                operation_type_id,
                                op_user_id,
                                act_parent_id
                                )
                        VALUES (
                                :firm_id,
                                " . intval($profilePublic) . ",
                                " . intval($nacecodeId) . ",    
                                " . intval($ConsultantId) . ",
                                " . intval($operationIdValue) . ",
                                " . intval($opUserIdValue) . ",
                                (SELECT last_value FROM info_firm_nace_id_seq)
                                               ) ");
                    $statement->bindValue(':firm_id', $params['firm_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':ref_firm_id', $params['ref_firm_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':total_project', $params['total_project'], \PDO::PARAM_INT);
                    $statement->bindValue(':continuing_project', $params['continuing_project'], \PDO::PARAM_INT);
                    $statement->bindValue(':unsuccessful_project', $params['unsuccessful_project'], \PDO::PARAM_INT);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_firm_nace_id_seq');
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
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'pk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23505';
                $errorInfoColumn = 'firm_id';
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
     * @ info_firm_nace tablosunda ref_firm_id & firm_id sutununda daha önce oluşturulmuş mu?      
     * @version v 1.0 18.01.2017
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
                a.firm_id AS firm_id, 
                a.firm_id  AS value, 
                a.firm_id  = " . intval($params['firm_id']) . " AS control,                
                CONCAT('Bu NACE kodu daha önce kaydedilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM info_firm_nace a
            WHERE a.nacecode_id = " . intval($params['nacecode_id']) . " AND
                a.firm_id =  " . intval($params['firm_id']) . " AND
                " . $addSql . "
                a.active =0 AND
                a.deleted=0
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
     * info_firm_nace tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  18.01.2017
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $kontrol = $this->haveRecords($params);
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
                if (\Utill\Dal\Helper::haveRecord($userId)) {
                    $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                    $this->makePassive(array('id' => $params['id']));

                    $operationIdValue = -2;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 52, 'type_id' => 2,));
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
                            $profilePublic =$params['profile_public'];                       
                    }
                    $nacecodeId = 0;
                    if ((isset($params['nacecode_id']) && $params['nacecode_id'] != "")) {
                            $nacecodeId =$params['nacecode_id'];                       
                    }

                    $FirmId = 0;
                    if ((isset($params['firm_id']) && $params['firm_id'] != "")) {
                        $FirmId = intval($params['firm_id']);
                    }
                    

                    $statementInsert = $pdo->prepare("
                INSERT INTO info_firm_nace (
                        active,
                        deleted,
                        op_user_id, 
                        operation_type_id,
                        act_parent_id, 
                        firm_id, 
                        profile_public, 
                        nacecode_id,
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id
                        )  
                SELECT 
                    active, 
                    deleted,
                    " . intval($opUserIdValue) . " AS op_user_id,
                    " . intval($operationIdValue) . " AS operation_type_id,
                    act_parent_id,  
                    " . intval($FirmId) . " AS firm_id,
                    " . intval($profilePublic) . " AS profile_public,
                    " . intval($nacecodeId) . " AS nacecode_id,
                    consultant_id,
                    consultant_confirm_type_id, 
                    confirm_id    
                FROM info_firm_nace
                WHERE id  =" . intval($params['id']) . "
                                                ");
                    $result = $statementInsert->execute();
                    $affectedRows = $statementInsert->rowCount();
                    $insertID = $pdo->lastInsertId('info_firm_nace_id_seq');
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
                                    array('table_name' => 'info_firm_nace', 'id' => $params['id'],));
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
                    $errorInfo = '23502';   // 23502  user_id not_null_violation
                    $errorInfoColumn = 'pk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23505';
                $errorInfoColumn = 'ref_firm_id';
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
     * @ Gridi doldurmak için info_firm_nace tablosundan kayıtları döndürür !!
     * @version v 1.0  18.01.2017
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
            $sort = "firm_name";
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
        if ((isset($args['language_code']) && $args['language_code'] != "")) {
            $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            }
        }
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT
                    a.id,
		    COALESCE(NULLIF(COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng), ''), fp.firm_name) AS firm_name,
		    a.nacecode_id, 	
		    COALESCE(NULLIF(COALESCE(NULLIF(sncx.description, ''), snc.description_eng), ''), snc.description) AS nace_code,		    		                        
                    a.active,
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,
		    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_names,
                    a.op_user_id,
                    u.username AS op_username,
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,		    
                    a.confirm_id,
                    a.cons_allow_id,
                    COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow                    
                FROM info_firm_profile fp                                
                INNER JOIN info_firm_nace a ON a.firm_id = fp.act_parent_id AND a.cons_allow_id=2 
                INNER JOIN sys_nace_codes snc ON snc.id = a.nacecode_id AND snc.active =0 AND snc.deleted =0                 
                INNER JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . "  AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_nace_codes sncx ON (sncx.id = snc.id OR sncx.language_parent_id = snc.id) AND sncx.language_id = lx.id  AND sncx.deleted = 0 AND sncx.active = 0
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.act_parent_id OR fpx.act_parent_id=fp.act_parent_id ) AND fpx.cons_allow_id=2 AND fpx.language_id = lx.id
                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = 647 AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0                
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_parent_id =0 AND sd16.deleted = 0 AND sd16.active = 0

		LEFT JOIN sys_operation_types opx ON opx.id = op.id AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0
		LEFT JOIN sys_specific_definitions sd14x ON (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.language_id = lx.id  AND sd14x.deleted = 0 AND sd14x.active = 0                
                
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
              
	        WHERE fp.language_parent_id = 0 AND fp.cons_allow_id=2
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
     * @ Gridi doldurmak için info_firm_nace tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  18.01.2017
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                        COUNT(a.id) AS COUNT  
                FROM info_firm_profile fp                                
                INNER JOIN info_firm_nace a ON a.firm_id = fp.act_parent_id AND a.cons_allow_id=2 
                INNER JOIN sys_nace_codes snc ON snc.id = a.nacecode_id AND snc.active =0 AND snc.deleted =0                 
                INNER JOIN info_users u ON u.id = a.op_user_id                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = 647 AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0                
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_parent_id =0 AND sd16.deleted = 0 AND sd16.active = 0                
		WHERE 
                    fp.language_parent_id = 0 AND 
                    fp.cons_allow_id=2
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @author Okan CIRAN     
     * @ info_firm_nace tablosundan parametre olarak  gelen id kaydın active alanını 1 yapar ve 
     * yeni yeni kayıt oluşturarak deleted ve active = 1 olarak  yeni kayıt yapar. ! 
     * @version v 1.0  18.01.2017
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function deletedAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
 
                $operationIdValue = -3;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 52, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }

                $this->makePassive(array('id' => $params['id']));

                $statementInsert = $pdo->prepare(" 
                     INSERT INTO info_firm_nace (                        
                        active,
                        deleted,
                        op_user_id, 
                        operation_type_id,                        
                        act_parent_id, 
                        firm_id, 
                        profile_public, 
                        nacecode_id,
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id     
                        )  
                SELECT                 
                    1 AS active, 
                    1 AS deleted,    
                    " . intval($opUserIdValue) . " AS op_user_id,  
                    " . intval($operationIdValue) . " AS operation_type_id,                                        
                    act_parent_id, 
                    firm_id, 
                    profile_public, 
                    nacecode_id,                    
                    consultant_id, 
                    consultant_confirm_type_id, 
                    confirm_id
                FROM info_firm_nace
                WHERE id  =" . intval($params['id']) . "  
                     ");
                $insertAct = $statementInsert->execute();
                $affectedRows = $statementInsert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_nace_id_seq');
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
                                array('table_name' => 'info_firm_nace', 'id' => $params['id'],));
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
     * @ firmanın faaliyet gruplarınıdöndürür !!
     * hangi işleri yaptıklarının listesi
     * @version v 1.0  18.01.2017
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWhatWorksForNace($params = array()) {
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
                            
            $opUserIdValue = NULL;
            $opUserFirmIdValue = NULL;
            if ((isset($params['pk']) && $params['pk'] != "")) {
                $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $opUserFirmIdValue = $opUserId ['resultSet'][0]['user_firm_id'];
                }
            }
                         
            $networkKeyValue  ="-19";
            if (isset($params['network_key']) && $params['network_key'] != "") {
                $networkKeyValue = $params['network_key'];              
            }

            $sql = "            
                SELECT
                    a.id,
                    snc.nace_code,
		    a.nacecode_id, 	
		    COALESCE(NULLIF(COALESCE(NULLIF(sncx.description, ''), snc.description_eng), ''), snc.description) AS description
                FROM info_firm_profile fp                                
                INNER JOIN info_firm_nace a ON a.firm_id = fp.act_parent_id AND a.cons_allow_id=2 AND a.profile_public =0 
                INNER JOIN sys_nace_codes snc ON snc.id = a.nacecode_id AND snc.active =0 AND snc.deleted =0                 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_nace_codes sncx ON (sncx.id = snc.id OR sncx.language_parent_id = snc.id) AND sncx.language_id = lx.id  AND sncx.deleted = 0 AND sncx.active = 0                
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.act_parent_id OR fpx.act_parent_id=fp.act_parent_id ) AND fpx.cons_allow_id=2 AND fpx.language_id = lx.id                
                WHERE fp.language_parent_id = 0 AND
                    fp.cons_allow_id = 2 
               ORDER BY nace_code
                                 ";
            $statement = $pdo->prepare($sql);
         // echo debugPDO($sql, $params);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo(); 
            ActUsersActionStatistics::insert (
                    array('url' =>  $params['url'],
                        'opUserIdValue' => $opUserIdValue,
                        'npk' => $params['network_key'],
                        'unpk' => NULL,
                        'opUserFirmIdValue' => $opUserFirmIdValue,                           
                        'language_id' => $languageIdValue,
                    ));  
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

}
