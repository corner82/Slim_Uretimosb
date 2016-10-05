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
 * @since 09.05.2016
 */
class InfoFirmSocialmedia extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_socialmedia tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  09.05.2016
     * @param array $params
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
                UPDATE info_firm_socialmedia
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($opUserIdValue) . "  
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
     * @ info_firm_socialmedia tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  09.05.2016  
     * @param array $params
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
                    fp.act_parent_id AS firm_id,
                    COALESCE(NULLIF(fpx.firm_name, ''),fpx.firm_name_eng) AS firm_name,
                    fpx.firm_name_eng,
		    COALESCE(NULLIF(smx.name, ''),sm.name_eng) AS socialmedia_name,
                    sm.name_eng As socialmedia_eng,
                    a.firm_link,
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.profile_public,
                    COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    fk.network_key,
                    a.act_parent_id,
                    a.s_date,
                    a.c_date,
                   CASE COALESCE(NULLIF(sm.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(sm.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(sm.logo, ''),'image_not_found.png')) END AS logo
                FROM info_firm_socialmedia a
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN info_firm_keys fk ON a.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                LEFT JOIN info_firm_profile fpx ON (fpx.id = fp.id OR fpx.language_parent_id = fp.id) AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id = l.id
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id
		LEFT JOIN sys_socialmedia smx ON (smx.id = sm.id OR smx.language_parent_id = sm.id) AND smx.language_id = lx.id AND smx.active =0 AND smx.deleted =0
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0
                LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id    
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_specific_definitions sd19x ON (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.language_id = lx.id AND  sd19x.deleted = 0 AND sd19x.active = 0
                WHERE a.deleted =0 AND fp.language_parent_id =0
                ORDER BY fp.language_id, fp.act_parent_id ,socialmedia_name
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
     * @ info_firm_socialmedia tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  09.05.2016
     * @param type $params
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
                $userfirmIdValue = -22;
                $userfirmId = InfoFirmProfile::getUserFirmIds(array('user_id' => $opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($userfirmId)) {
                    $userfirmIdValue = $userfirmId ['resultSet'][0]['firm_id'];
                }
                $firmIdValue = -11;
                $firmId = InfoFirmProfile::getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
                if (\Utill\Dal\Helper::haveRecord($firmId)) {
                    $firmIdValue = $firmId ['resultSet'][0]['firm_id'];
                }
 
                if ($firmIdValue == $userfirmIdValue) {
                    $kontrol = $this->haveRecords(array('firm_link' => $params['firm_link'],));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                        $operationIdValue = -1;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                        array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 29, 'type_id' => 1,));
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
                        $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_firm_socialmedia' , 
                                                                                        'operation_type_id' => $operationIdValue, 
                                                                                        'language_id' => $languageIdValue,  
                                                                                               )); 
                        if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                            $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                        } 
                        $sql = "
                INSERT INTO info_firm_socialmedia(                        
                        firm_id, 
                        profile_public, 
                        op_user_id, 
                        operation_type_id, 
                        consultant_id, 
                        sys_socialmedia_id, 
                        act_parent_id, 
                        firm_link
                         )
                VALUES (
                        :firm_id, 
                        :profile_public, 
                        :op_user_id, 
                        :operation_type_id, 
                        :consultant_id, 
                        :sys_socialmedia_id, 
                        (SELECT last_value FROM info_firm_socialmedia_id_seq), 
                        :firm_link
                                             )   ";
                        $statement = $pdo->prepare($sql);
                        $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                        $statement->bindValue(':operation_type_id', $operationIdValue, \PDO::PARAM_INT);
                        $statement->bindValue(':consultant_id', $ConsultantId, \PDO::PARAM_INT);
                        $statement->bindValue(':sys_socialmedia_id', $params['sys_socialmedia_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':firm_id', $firmIdValue, \PDO::PARAM_INT);
                        $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                        $statement->bindValue(':firm_link', $params['firm_link'], \PDO::PARAM_STR);
                       //  echo debugPDO($sql, $params);
                        $result = $statement->execute();
                        $insertID = $pdo->lastInsertId('info_firm_socialmedia_id_seq');
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
                    }
                    else {
                        $errorInfo = '23502';
                        $errorInfoColumn = 'firm_link';
                        $pdo->rollback();
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                    }
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'firm_id';
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
     * @ info_firm_socialmedia tablosunda property_name daha önce kaydedilmiş mi ?  
     * @version v 1.0 13.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }
            $sql = "                         
                SELECT  
                   a.firm_link ,
                   '" . $params['firm_link'] . "' AS value, 
                    firm_link = '" . $params['firm_link'] . "' AS control,
                    CONCAT(a.firm_link, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message,
                    a.firm_link
                FROM info_firm_socialmedia a
                WHERE
                    firm_link = '" . $params['firm_link'] . "' 
                    " . $addSql . " 
                    AND a.deleted =0 
                    AND a.active = 0
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
     * @ info_firm_socialmedia tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  09.05.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_firm_socialmedia
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
     * info_firm_socialmedia tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  09.05.2016
     * @param type $params
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
                $kontrol = $this->haveRecords(array('id' => $params['id'],                  
                                                    'firm_link' => $params['firm_link'], ));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $this->makePassive(array('id' => $params['id']));
                    $operationIdValue = -2;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 29, 'type_id' => 2,));
                    if (\Utill\Dal\Helper::haveRecord($operationId)) {
                        $operationIdValue = $operationId ['resultSet'][0]['id'];
                    }  

                    $sql = "  
                    INSERT INTO info_firm_socialmedia(
                        firm_id, 
                        profile_public, 
                        op_user_id, 
                        operation_type_id, 
                        consultant_id, 
                        sys_socialmedia_id, 
                        act_parent_id, 
                        firm_link
                        )
                        SELECT  
                            firm_id,
                            " . intval($params['profile_public']) . " AS profile_public,                           
                            " . intval($opUserIdValue) . " AS op_user_id,  
                            " . intval($operationIdValue) . " AS operation_type_id,  
                            consultant_id, 
                            " . intval($params['sys_socialmedia_id']) . " AS sys_socialmedia_id,
                            act_parent_id,                                                   
                            '" . $params['firm_link'] . "' AS firm_link
                        FROM info_firm_socialmedia 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                    $statement_act_insert = $pdo->prepare($sql);
                    //   echo debugPDO($sql, $params);
                    $insert_act_insert = $statement_act_insert->execute();
                    $affectedRows = $statement_act_insert->rowCount();
                    $insertID = $pdo->lastInsertId('info_firm_socialmedia_id_seq');
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
                                    array('table_name' => 'info_firm_socialmedia', 'id' => $params['id'],));
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
                    $errorInfoColumn = 'firm_link';
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
     * @ Gridi doldurmak için info_firm_socialmedia tablosundan kayıtları döndürür !!
     * @version v 1.0  09.05.2016
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
            $sort = "fp.language_id, fp.act_parent_id ,socialmedia_name ";
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
                    fp.act_parent_id AS firm_id,
                    COALESCE(NULLIF(fpx.firm_name, ''),fpx.firm_name_eng) AS firm_name,
                    fpx.firm_name_eng,
		    COALESCE(NULLIF(smx.name, ''),sm.name_eng) AS socialmedia_name,
                    sm.name_eng As socialmedia_eng,
                    a.firm_link,
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.profile_public,
                    COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    fk.network_key,
                    a.act_parent_id,
                    a.s_date,
                    a.c_date,
                    CASE COALESCE(NULLIF(sm.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(sm.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(sm.logo, ''),'image_not_found.png')) END AS logo
                FROM info_firm_socialmedia a
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id = 2 AND fp.language_parent_id =0
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN info_firm_keys fk ON a.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                LEFT JOIN info_firm_profile fpx ON (fpx.id = fp.id OR fpx.language_parent_id = fp.id) AND fpx.cons_allow_id = 2 AND fpx.language_id = l.id
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id
		LEFT JOIN sys_socialmedia smx ON (smx.id = sm.id OR smx.language_parent_id = sm.id) AND smx.language_id = lx.id AND smx.active =0 AND smx.deleted =0
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0
                LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_specific_definitions sd19x ON (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.language_id = lx.id AND  sd19x.deleted = 0 AND sd19x.active = 0
                WHERE fp.cons_allow_id = 2 AND fp.language_parent_id =0
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
            //   echo debugPDO($sql, $parameters);
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
     * @ Gridi doldurmak için info_firm_socialmedia tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0 09.05.2016
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
                FROM info_firm_socialmedia a
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id = 2 AND fp.language_parent_id =0
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN info_firm_keys fk ON a.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active = 0                 
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id		
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id                
                WHERE fp.cons_allow_id = 2 AND fp.language_parent_id =0                                           
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
     * @ info_firm_socialmedia tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  09.05.2016
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
                UPDATE info_firm_socialmedia
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM info_firm_socialmedia
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

    /**
     * @author Okan CIRAN
     * @ kullanıcının socialmedia bilgilerinin kayıtlarını döndürür !!
     * @version v 1.0  09.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmSocialMedia($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {                
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }
                $networkKeyValue = '-1';
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                }
                $sql = "                     
               SELECT 
                    a.id,
                    fp.act_parent_id AS firm_id,
                    COALESCE(NULLIF(fpx.firm_name, ''),fpx.firm_name_eng) AS firm_name,
                    fpx.firm_name_eng,
		    COALESCE(NULLIF(smx.name, ''),sm.name_eng) AS socialmedia_name,
                    sm.name_eng As socialmedia_eng,
                    a.firm_link,
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.profile_public,
                    COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    fk.network_key,
                    a.act_parent_id,
                    a.s_date,
                    a.c_date,
                    CASE COALESCE(NULLIF(sm.logo, ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(sm.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(sm.logo, ''),'image_not_found.png')) END AS logo
                FROM info_firm_socialmedia a
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id = 2 AND fp.language_parent_id =0
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN info_firm_keys fk ON a.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                LEFT JOIN info_firm_profile fpx ON (fpx.id = fp.id OR fpx.language_parent_id = fp.id) AND fpx.cons_allow_id = 2 AND fpx.language_id = l.id
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id
		LEFT JOIN sys_socialmedia smx ON (smx.id = sm.id OR smx.language_parent_id = sm.id) AND smx.language_id = lx.id AND smx.active =0 AND smx.deleted =0
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0
                LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_specific_definitions sd19x ON (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.language_id = lx.id AND  sd19x.deleted = 0 AND sd19x.active = 0
                WHERE a.deleted = 0 AND a.active =0 AND 
                    fp.cons_allow_id = 2 AND 
                    fp.language_parent_id =0 AND 
                    fk.network_key = '" . $networkKeyValue . "'
                ORDER BY fp.language_id, socialmedia_name
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
     * @ kullanıcının socialmedia bilgilerinin kayıtlarının sayısını döndürür !!
     * @version v 1.0  09.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmSocialMediaRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $networkKeyValue = '-1';
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                }

                $sql = "                     
                SELECT
                    count(a.id) AS COUNT 
                FROM info_firm_socialmedia a
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id = 2 AND fp.language_parent_id =0
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN info_firm_keys fk ON a.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active = 0                 
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id		
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.language_id = l.id AND sd19.deleted = 0 AND sd19.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id                
                WHERE a.deleted = 0 AND a.active =0 AND 
                    fp.cons_allow_id = 2 AND 
                    fp.language_parent_id =0 AND 
                    fk.network_key =  '" . $networkKeyValue . "'                        
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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 09.05.2016 
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
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 29, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                $sql = "  
                    INSERT INTO info_firm_socialmedia(
                        firm_id, 
                        profile_public, 
                        op_user_id, 
                        operation_type_id, 
                        consultant_id, 
                        sys_socialmedia_id, 
                        act_parent_id, 
                        firm_link,
                        active,
                        deleted
                        )
                        SELECT  
                            firm_id,
                            profile_public,                           
                            " . intval($opUserIdValue) . " AS op_user_id,  
                            " . intval($operationIdValue) . " AS operation_type_id,  
                            consultant_id, 
                            sys_socialmedia_id,
                            act_parent_id,                                                   
                            firm_link,
                            1,
                            1    
                        FROM info_firm_socialmedia 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_socialmedia_id_seq');
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_firm_socialmedia', 'id' => $params['id'],));
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

 
}
