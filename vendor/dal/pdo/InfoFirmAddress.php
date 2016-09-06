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
class InfoFirmAddress extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_address tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 16.05.2016
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
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE info_firm_address
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
     * @ info_firm_address tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  16.05.2016   
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
                        a.firm_building_type_id,
                        COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng) AS firm_building_type,
                        COALESCE(NULLIF(ax.firm_building_name, ''), a.firm_building_name_eng) AS firm_building_name,
                        a.firm_building_name_eng,
                        a.address,
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
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.language_parent_id,  
                        a.country_id,
                        COALESCE(NULLIF(cox.name , ''), co.name_eng) AS country_name,
                        a.city_id,
                        COALESCE(NULLIF(ctx.name , ''), ct.name_eng) AS city_name,
                        a.borough_id,
                        COALESCE(NULLIF(box.name , ''), bo.name_eng) AS borough_name,
                        a.osb_id,
                        osb.name AS osb_name
                    FROM info_firm_address a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_address ax on (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted = 0 AND ax.active = 0 AND ax.language_id = lx.id
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = fp.id OR fpx.language_parent_id = fp.id) AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
		    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0

                    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0
                    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
                    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0

                    LEFT JOIN sys_specific_definitions sd4x ON  sd4x.language_id = lx.id AND (sd4x.id = sd4.id OR sd4x.language_parent_id = sd4.id) AND sd4x.deleted =0 AND sd4x.active =0
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
                    INNER JOIN sys_countrys co on co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    INNER JOIN sys_city ct on ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    INNER JOIN sys_borough bo on bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  

                    LEFT JOIN sys_countrys cox on (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
                    LEFT JOIN sys_city ctx on (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
                    LEFT JOIN sys_borough box on (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 	   	     
               
                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id
		    ORDER BY l.priority, a.firm_building_type_id, firm_building_name 
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
     * @ info_firm_address tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 15.01.2016
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
                a.firm_id AS name , 
                a.firm_building_type_id AS value , 
                a.firm_building_type_id = " . intval($params['firm_building_type_id']) . " AS control,
                CONCAT(a.firm_building_type_id, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_address a             
            WHERE a.firm_id = " . intval($params['firm_id']) . "
                AND a.firm_building_type_id = " . intval($params['firm_building_type_id']) . "
                AND a.active = 0               
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
     * @ info_firm_address tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
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
                UPDATE info_firm_address
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
     * @ info_firm_address tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  16.05.2016
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
                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmId = $getFirm ['resultSet'][0]['firm_id'];
                    $kontrol = $this->haveRecords(array('firm_id' => $getFirmId, 'firm_building_type_id' => $params['firm_building_type_id'],));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {

                        $operationIdValue = -1;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                        array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 2, 'type_id' => 1,));
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
                        $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_firm_address' , 
                                                                                              'operation_type_id' => $operationIdValue, 
                                                                                              'language_id' => $languageIdValue,  
                                                                                               ));
                        if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                            $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                        }

                        $profilePublic = 0;
                        if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                            $profilePublic = $params['profile_public'];
                        }

                        $osbId = 0;
                        if ((isset($params['osb_id']) && $params['osb_id'] != "")) {
                            $osbId = $params['osb_id'];
                        } 

                        $sql = " 
                        INSERT INTO info_firm_address(
                            firm_id, 
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id,  
                            firm_building_type_id,
                            firm_building_name, 
                            address, 
                            borough_id, 
                            city_id, 
                            country_id,
                            description,
                            description_eng,
                            osb_id,
                            tel,
                            fax,
                            email
                            )
                        VALUES (
                            :firm_id, 
                            " . intval($ConsultantId) . ",
                            " . intval($operationIdValue) . ",                       
                            " . intval($languageIdValue) . ",
                            " . intval($opUserIdValue) . ",
                            " . intval($profilePublic) . ",
                            (SELECT last_value FROM info_firm_address_id_seq),
                            :firm_building_type_id,
                            :firm_building_name, 
                            :address, 
                            :borough_id, 
                            :city_id, 
                            :country_id,
                            :description,
                            :description_eng,
                            :osb_id,
                            :tel,
                            :fax,
                            :email
                             )";
                        $statement = $pdo->prepare($sql);
                        $statement->bindValue(':firm_id', $getFirmId, \PDO::PARAM_INT);
                        $statement->bindValue(':firm_building_type_id', $params['firm_building_type_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':firm_building_name', $params['firm_building_name'], \PDO::PARAM_STR);
                        $statement->bindValue(':address', $params['address'], \PDO::PARAM_STR);
                        $statement->bindValue(':borough_id', $params['borough_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':city_id', $params['city_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                        $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                        $statement->bindValue(':osb_id', $params['osb_id'], \PDO::PARAM_INT);
                        $statement->bindValue(':tel', $params['tel'], \PDO::PARAM_STR);
                        $statement->bindValue(':fax', $params['fax'], \PDO::PARAM_STR);
                        $statement->bindValue(':email', $params['email'], \PDO::PARAM_STR);
                        //  echo debugPDO($sql, $params);
                        $result = $statement->execute();
                        $insertID = $pdo->lastInsertId('info_firm_address_id_seq');
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
                        $errorInfoColumn = 'firm_building_type_id';
                        $pdo->rollback();
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
     * info_firm_address tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  16.05.2016
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

                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmId = $getFirm ['resultSet'][0]['firm_id'];

                    $kontrol = $this->haveRecords(array('id' => $params['id'], 'firm_id' => $getFirmId, 'firm_building_type_id' => $params['firm_building_type_id'],));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                        $this->makePassive(array('id' => $params['id']));
                        $operationIdValue = -2;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                        array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 2, 'type_id' => 2,));
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
                            $profilePublic = $params['profile_public'];
                        }
                        $active = 0;
                        if ((isset($params['active']) && $params['active'] != "")) {
                            $active = $params['active'];
                        }

                        $osbId = 0;
                        if ((isset($params['osb_id']) && $params['osb_id'] != "")) {
                            $osbId = $params['osb_id'];
                        }

                        $sql = " 
                 INSERT INTO info_firm_address(
                        firm_id, 
                        consultant_id,
                        operation_type_id,
                        language_id,
                        op_user_id, 
                        profile_public,
                        act_parent_id,  
                        firm_building_type_id,
                        firm_building_name, 
                        address, 
                        borough_id, 
                        city_id, 
                        country_id,
                        description,
                        description_eng,
                        osb_id,
                        tel,
                        fax,
                        email,
                        active,
                        language_parent_id
                        )                      
                        SELECT                              
                            firm_id,
                            consultant_id,
                            " . intval($operationIdValue) . ",
                            " . intval($languageIdValue) . ",
                            " . intval($opUserIdValue) . ",
                            " . intval($profilePublic) . " AS profile_public, 
                            act_parent_id,                                
                            " . intval($params['firm_building_type_id']) . " AS firm_building_type_id,
                            '" . $params['firm_building_name'] . "' AS firm_building_name,
                            '" . $params['address'] . "' AS address,
                            " . intval($params['borough_id']) . " AS borough_id,  
                            " . intval($params['city_id']) . " AS city_id,  
                            " . intval($params['country_id']) . " AS country_id, 
                            '" . $params['description'] . "' AS description,
                            '" . $params['description_eng'] . "' AS description_eng,
                            " . intval($params['osb_id']) . " AS osb_id, 
                            '" . $params['tel'] . "' AS tel,
                            '" . $params['fax'] . "' AS fax,
                            '" . $params['email'] . "' AS email,
                            " . intval($active) . " AS active, 
                            language_parent_id
                        FROM info_firm_address 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                        $statement_act_insert = $pdo->prepare($sql); 
                      //  echo debugPDO($sql, $params);  
                        $insert_act_insert = $statement_act_insert->execute();
                        $affectedRows = $statement_act_insert->rowCount();
                        $insertID = $pdo->lastInsertId('info_firm_address_id_seq');
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
                                        array('table_name' => 'info_firm_address', 'id' => $params['id'],));
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
                        $errorInfoColumn = 'firm_building_type_id';
                        $pdo->rollback();
                        $result = $kontrol;
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
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_address tablosundan kayıtları döndürür !!
     * @version v 1.0  16.05.2016
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
            $sort = " a.firm_building_type_id, firm_building_name ";
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
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,                        
			a.s_date,
                        a.c_date,
                        a.firm_building_type_id,
                        COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng) AS firm_building_type,
                        COALESCE(NULLIF(ax.firm_building_name, ''), a.firm_building_name_eng) AS firm_building_name,
                        a.firm_building_name_eng,
                        a.address,
                        COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng) AS firm_building_type,                        
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
                        a.country_id,                     
                        COALESCE(NULLIF(cox.name , ''), co.name_eng) AS country_name,
                        a.city_id,                     
                        COALESCE(NULLIF(ctx.name , ''), ct.name_eng) AS city_name,
                        a.borough_id,
                        COALESCE(NULLIF(box.name , ''), bo.name_eng) AS borough_name,                    
                        a.osb_id,
                        osb.name AS osb_name
                    FROM info_firm_address a                    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_address ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted = 0 AND ax.active = 0 AND ax.language_id = lx.id
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = a.firm_id OR fpx.language_parent_id=a.firm_id) AND fpx.active = 0 AND fpx.deleted = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
		    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0

		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0

		    LEFT JOIN sys_specific_definitions sd4x ON  sd4x.language_id = lx.id AND (sd4x.id = sd4.id OR sd4x.language_parent_id = sd4.id) AND sd4x.deleted =0 AND sd4x.active =0
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
                    INNER JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    INNER JOIN sys_city ct ON ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    INNER JOIN sys_borough bo ON bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  

                    LEFT JOIN sys_countrys cox ON (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
                    LEFT JOIN sys_city ctx ON (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
                    LEFT JOIN sys_borough box ON (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 

                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_address tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  16.05.2016
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
                    FROM info_firm_address a                    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0		    
                    INNER JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    INNER JOIN sys_city ct ON ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    INNER JOIN sys_borough bo ON bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  
                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id                    
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
     * usage     
     * @author Okan CIRAN
     * @ info_firm_address tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  16.05.2016
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare("                 
                    
                    INSERT INTO info_firm_address(
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
                            FROM info_firm_address c
                            LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                            WHERE c.id = " . intval($params['id']) . "
                    ) AS xy  
                    WHERE xy.language_main_code NOT IN 
                        (SELECT 
                            DISTINCT language_code 
                         FROM info_firm_address cx 
                         WHERE (cx.language_parent_id = " . intval($params['id']) . "
						OR cx.id = " . intval($params['id']) . "
					) AND cx.deleted =0 AND cx.active =0)

                            ");

            //   $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);

            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_firm_address_id_seq');
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
     * @ text alanları doldurmak için info_firm_address tablosundan tek kayıt döndürür !! 
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
     * @version v 1.0  16.05.2016
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
                    FROM info_firm_address a    
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
     * @version 16.05.2016 
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
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 2, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                $sql = "                
                  INSERT INTO info_firm_address(
                        firm_id,                         
                        operation_type_id,
                        language_id,
                        op_user_id, 
                        profile_public,
                        act_parent_id,  
                        firm_building_type_id,
                        firm_building_name, 
                        address, 
                        borough_id, 
                        city_id, 
                        country_id,
                        description,
                        description_eng,
                        osb_id, 
                       
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        cons_allow_id,
                        language_parent_id,
                        active,
                        deleted
                        
                        )
                        SELECT  
                            firm_id,                            
                            " . intval($operationIdValue) . ",  
                            language_id,
                            " . intval($opUserIdValue) . ",
                            profile_public, 
                            act_parent_id,
                            firm_building_type_id,
                            firm_building_name, 
                            address, 
                            borough_id, 
                            city_id, 
                            country_id,
                            description,
                            description_eng,
                            osb_id, 
                            consultant_id,
                            consultant_confirm_type_id,
                            confirm_id,
                            cons_allow_id,
                            language_parent_id,
                         
                            1,
                            1                            
                        FROM info_firm_address 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                //  echo debugPDO($sql, $params);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_address_id_seq');
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_firm_address', 'id' => $params['id'],));
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
     * @ Gridi doldurmak için info_firm_address tablosundan firmanın kayıtlarını döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmAddress($params = array()) {
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
                $sort = " firm_building_type";
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
            if (isset($params['filterRules'])) {
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
                            case 'firm_building_type':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng)" . $sorguExpression . ' ';

                                break;
                            case 'address':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.address" . $sorguExpression . ' ';

                                break;
                            case 'cons_allow':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng)" . $sorguExpression . ' ';

                                break;
                            case 'cons_allow':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng)" . $sorguExpression . ' ';

                                break;
                            case 'osb_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND osb.name" . $sorguExpression . ' ';

                                break;
                            case 'country_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(cox.name , ''), co.name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'city_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(ctx.name , ''), ct.name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'borough_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(box.name , ''), bo.name_eng)" . $sorguExpression . ' ';

                                break;
                             case 'operation_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng)" . $sorguExpression . ' ';

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
                $networkKeyValue = '-1';
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                }

                $sql = "
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,                        
			a.s_date,
                        a.c_date,
                        a.firm_building_type_id,
                        COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng) AS firm_building_type,
                        COALESCE(NULLIF(ax.firm_building_name, ''), a.firm_building_name_eng) AS firm_building_name,
                        a.firm_building_name_eng,
                        a.address,                        
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
                        a.cons_allow_id,
                        COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng) AS cons_allow,
                        a.language_parent_id,  
                        a.country_id,                     
                        COALESCE(NULLIF(cox.name , ''), co.name_eng) AS country_name,
                        a.city_id,                     
                        COALESCE(NULLIF(ctx.name , ''), ct.name_eng) AS city_name,
                        a.borough_id,
                        COALESCE(NULLIF(box.name , ''), bo.name_eng) AS borough_name,                    
                        a.osb_id,
                        osb.name AS osb_name
                    FROM info_firm_address a                    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_address ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted = 0 AND ax.active = 0 AND ax.language_id = lx.id
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.deleted = 0 AND fp.active = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.act_parent_id = fp.act_parent_id OR fpx.language_parent_id= fp.act_parent_id) AND fpx.deleted = 0 AND fpx.active = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
		    LEFT JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0

		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0

		    LEFT JOIN sys_specific_definitions sd4x ON  sd4x.language_id = lx.id AND (sd4x.id = sd4.id OR sd4x.language_parent_id = sd4.id) AND sd4x.deleted =0 AND sd4x.active =0
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
                    INNER JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    INNER JOIN sys_city ct ON ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    INNER JOIN sys_borough bo ON bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  

                    LEFT JOIN sys_countrys cox ON (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
                    LEFT JOIN sys_city ctx ON (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
                    LEFT JOIN sys_borough box ON (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 

                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id                    
                    WHERE 
                        
                        a.active =0 AND                         
                        a.language_parent_id =0 AND 
                        ifk.network_key = '" . $networkKeyValue . "'  
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
     * @ Gridi doldurmak için info_firm_address tablosundan firmanın kayıtlarını döndürür !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillSingularFirmAddressRtc($params = array()) {
        try {              
            $addSql = NULL; 
            $sorguStr = null;
            if (isset($args['filterRules'])) {
                $filterRules = trim($args['filterRules']);
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
                            case 'firm_building_type':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng)" . $sorguExpression . ' ';

                                break;
                            case 'address':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.address" . $sorguExpression . ' ';

                                break;
                            case 'cons_allow':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng)" . $sorguExpression . ' ';

                                break;
                            case 'cons_allow':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sd14x.description, ''), sd14.description_eng)" . $sorguExpression . ' ';

                                break;
                            case 'osb_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND osb.name" . $sorguExpression . ' ';

                                break;
                            case 'country_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(cox.name , ''), co.name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'city_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(ctx.name , ''), ct.name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'borough_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(box.name , ''), bo.name_eng)" . $sorguExpression . ' ';

                                break;
                             case 'operation_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng)" . $sorguExpression . ' ';

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
                $networkKeyValue = '-1';
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                }

                $sql = "
                    SELECT 
                       count(a.id) AS COUNT  
                    FROM info_firm_address a                    
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_address ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted = 0 AND ax.active = 0 AND ax.language_id = lx.id
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.deleted = 0 AND fp.active = 0 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.act_parent_id = fp.act_parent_id OR fpx.language_parent_id= fp.act_parent_id) AND fpx.deleted = 0 AND fpx.active = 0 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0

		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0

		    LEFT JOIN sys_specific_definitions sd4x ON  sd4x.language_id = lx.id AND (sd4x.id = sd4.id OR sd4x.language_parent_id = sd4.id) AND sd4x.deleted =0 AND sd4x.active =0
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    
                    INNER JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    INNER JOIN sys_city ct ON ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    INNER JOIN sys_borough bo ON bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  

                    LEFT JOIN sys_countrys cox ON (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
                    LEFT JOIN sys_city ctx ON (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
                    LEFT JOIN sys_borough box ON (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 

                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id
                    WHERE 
                        a.deleted = 0 AND 
                        a.active =0 AND
                        a.language_parent_id =0 AND 
                        ifk.network_key = '" . $networkKeyValue . "'  
                " . $addSql . "
                " . $sorguStr ;                            
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
     * @ network_key verilen firmanın danısmanın onayladıgı adres kayıtlarını döndürür !!
     * @version v 1.0  19.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmAddressNpk($params = array()) {
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

                $networkKeyValue = '-1';
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                }

                $sql = "                     
                    SELECT 
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng,
                        a.firm_building_type_id,
                        COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng) AS firm_building_type,
                        COALESCE(NULLIF(ax.firm_building_name, ''), a.firm_building_name_eng) AS firm_building_name,
                        a.firm_building_name_eng,
			a.address,
                        COALESCE(NULLIF(sd4x.description, ''), sd4.description_eng) AS firm_building_type,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        a.country_id,
		        COALESCE(NULLIF(cox.name , ''), co.name_eng) AS country_name,
		        a.city_id,
		        COALESCE(NULLIF(ctx.name , ''), ct.name_eng) AS city_name,
		        a.borough_id,
		        COALESCE(NULLIF(box.name , ''), bo.name_eng) AS borough_name,
                        a.osb_id,
                        COALESCE(NULLIF(osb.name, ''),' ') AS osb_name,
                        ifk.network_key,
                        COALESCE(NULLIF(ifc.tel, ''),' ') AS tel,
                        COALESCE(NULLIF(ifc.fax, ''),' ') AS fax,
                        COALESCE(NULLIF(ifc.email, ''),' ') AS email,
                        COALESCE(NULLIF(fp.web_address, ''),' ') AS web_address
                    FROM info_firm_address a
                    INNER JOIN info_firm_keys fk ON a.firm_id =  fk.firm_id                      
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_address ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.cons_allow_id = 2 AND ax.language_id = lx.id
                    LEFT JOIN info_firm_communications ifc ON ifc.firm_id = a.firm_id AND ifc.cons_allow_id = 2 AND ifc.language_parent_id =0 AND ifc.building_id = a.firm_building_type_id 
                    
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = a.firm_id AND fp.cons_allow_id =2 AND fp.language_parent_id =0  
                    LEFT JOIN info_firm_profile fpx ON (fpx.id = a.firm_id OR fpx.language_parent_id=a.firm_id) AND fpx.cons_allow_id =2 AND fpx.language_id =lx.id  
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
		    
		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0		    
		    LEFT JOIN sys_specific_definitions sd4x ON sd4x.language_id = lx.id AND (sd4x.id = sd4.id OR sd4x.language_parent_id = sd4.id) AND sd4x.deleted =0 AND sd4x.active =0
                    
                    INNER JOIN sys_countrys co on co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    LEFT JOIN sys_city ct on ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    LEFT JOIN sys_borough bo on bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  

                    LEFT JOIN sys_countrys cox on (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
                    LEFT JOIN sys_city ctx on (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
                    LEFT JOIN sys_borough box on (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 
	   	      
                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id

                    WHERE ifk.network_key= '" . $params['network_key'] . "' AND  
                        a.deleted = 0 AND 
                        a.active =0 AND 
                        a.cons_allow_id = 2 AND 
                        a.language_parent_id =0  
                  
                ORDER BY a.firm_building_type_id, firm_building_name
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
     * @ userin elemanı oldugu firmanın makina kayıtları sayısını döndürür !!
     * @version v 1.0  25.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUsersFirmAddressNpkRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');           
                $networkKeyValue = '-1';
                if (isset($params['network_key']) && $params['network_key'] != "") {
                    $networkKeyValue = $params['network_key'];
                }

                $sql = " 
                    SELECT 
                         COUNT(a.id) AS COUNT                      
                    FROM info_firm_address a
                    INNER JOIN info_firm_keys fk ON a.firm_id = fk.firm_id                      
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.id = a.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                      
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id		    
		    INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 4 AND a.firm_building_type_id = sd4.first_group AND sd4.deleted =0 AND sd4.active =0 AND sd4.language_parent_id =0
                    INNER JOIN sys_countrys co ON co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id
                    INNER JOIN sys_city ct ON ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id 
                    INNER JOIN sys_borough bo ON bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  
                    LEFT JOIN sys_osb osb ON osb.id = a.osb_id
                    WHERE ifk.network_key= '" . $params['network_key'] . "' AND  
                        a.deleted = 0 AND 
                        a.active =0 AND 
                        a.cons_allow_id = 2 AND 
                        a.language_parent_id =0             
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
