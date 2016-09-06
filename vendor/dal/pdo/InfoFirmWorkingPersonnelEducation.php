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
class InfoFirmWorkingPersonnelEducation extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_working_personnel_education tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 19.07.2016
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
                UPDATE info_firm_working_personnel_education
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
     * @ info_firm_working_personnel_education tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  19.07.2016   
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
                        fwp.firm_id,
			fwp.name, 
			fwp.surname, 
			a.diploma_name,	
			a.country_id,	
			COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS country_name,
			sc.name_eng AS country_name_eng,
			a.university_id,
			COALESCE(NULLIF(sux.name, ''), su.name_eng) AS university_name,
			su.name_eng AS university_name_eng,
			a.graduation_date,
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
                    FROM info_firm_working_personnel_education a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    LEFT JOIN sys_universities su ON su.id = a.university_id AND su.deleted =0 AND su.active=0 AND su.language_parent_id = 0 
		    LEFT JOIN sys_universities sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND sux.deleted =0 AND sux.active=0 AND sux.language_id = lx.id
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    LEFT JOIN sys_universities scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active=0 AND scx.language_id = lx.id
                    
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel_education ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0
                    ORDER BY  fwp.name, fwp.surname, a.diploma_name
 
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
     * @ info_firm_working_personnel_education tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0  19.07.2016 
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
                a.diploma_name AS name , 
                a.diploma_name AS value , 
                LOWER(a.diploma_name) = LOWER('" . $params['diploma_name'] . "') AS control,
                CONCAT(a.diploma_name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_working_personnel_education a             
            WHERE 
                a.working_personnel_id = " . intval($params['working_personnel_id']) . " AND
                a.country_id = " . intval($params['country_id']) . " AND
                a.university_id = " . intval($params['university_id']) . " AND
                a.graduation_date = " . intval($params['graduation_date']) . " AND
                LOWER(REPLACE(diploma_name,' ','')) = LOWER(REPLACE('" . $params['diploma_name'] . "',' ','')) AND 
               
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
     * @ info_firm_working_personnel_education tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  19.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_firm_working_personnel_education
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
     * @ info_firm_working_personnel_education tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  19.07.2016
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
                 //   $getFirmId = $getFirm ['resultSet'][0]['firm_id'];
                    
                    $kontrol = $this->haveRecords(array( 'working_personnel_id' =>  $params['working_personnel_id'], 'diploma_name' => $params['diploma_name'], ));
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                        $operationIdValue = -1;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                        array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 49, 'type_id' => 1,));
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
                        $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_firm_working_personnel_education',
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
                        $UniversityId = 0;
                        if ((isset($params['university_id']) && $params['university_id'] != "")) {
                            $UniversityId = $params['university_id'];
                        }

                        $sql = " 
                        INSERT INTO info_firm_working_personnel_education(                          
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id,                            
                            
                            working_personnel_id,
                            diploma_name, 
                            country_id, 
                            university_id, 
                            graduation_date
                            )
                        VALUES (                           
                            " . intval($ConsultantId) . ",
                            " . intval($operationIdValue) . ",                       
                            " . intval($languageIdValue) . ",
                            " . intval($opUserIdValue) . ",
                            " . intval($profilePublic) . ",                            
                            (SELECT last_value FROM info_firm_working_personnel_education_id_seq), 
                            " . intval($params['working_personnel_id']) . ", 
                            '" . $params['diploma_name'] . "', 
                            " . intval($params['country_id']) . ", 
                            " . intval($UniversityId) . ", 
                            " . intval($params['graduation_date']) . "                      
                             )";
                        $statement = $pdo->prepare($sql);
                     //  echo debugPDO($sql, $params);                             
                        $result = $statement->execute();
                        $insertID = $pdo->lastInsertId('info_firm_working_personnel_education_id_seq');
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
                        $errorInfoColumn = 'diploma_name';
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
     * info_firm_working_personnel_education tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  19.07.2016
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
                  //  $getFirmId = $getFirm ['resultSet'][0]['firm_id'];  
                    $Id = 0;
                    if ((isset($params['id']) && $params['id'] != "")) {
                        $Id = $params['id'];
                    }
                    $kontrol = $this->haveRecords($params);
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                        $this->makePassive(array('id' => $params['id']));
                        $operationIdValue = -2;
                        $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                        array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 49, 'type_id' => 2,));
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
                        $UniversityId = 0;
                        if ((isset($params['university_id']) && $params['university_id'] != "")) {
                            $UniversityId = $params['university_id'];
                        }

                        $sql = " 
                 INSERT INTO info_firm_working_personnel_education(                            
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id,
                           
                            working_personnel_id,
                            diploma_name, 
                            country_id, 
                            university_id, 
                            graduation_date                    
                        )
                        SELECT                              
                            consultant_id, 
                            " . intval($operationIdValue) . ",
                            " . intval($languageIdValue) . ",
                            " . intval($opUserIdValue) . ",
                            " . intval($profilePublic) . ",  
                            act_parent_id,
                            " . intval($params['working_personnel_id']) . ", 
                            '" . $params['diploma_name'] . "', 
                            " . intval($params['country_id']) . ", 
                            " . intval($UniversityId) . ", 
                            " . intval($params['graduation_date']) . "  
                        FROM info_firm_working_personnel_education 
                        WHERE id =  " . intval($Id) . " 
                        ";
                        $statement_act_insert = $pdo->prepare($sql);
                       // echo debugPDO($sql, $params);
                        $insert_act_insert = $statement_act_insert->execute();
                        $affectedRows = $statement_act_insert->rowCount();
                        $insertID = $pdo->lastInsertId('info_firm_working_personnel_education_id_seq');                    
                        $errorInfo = $statement_act_insert->errorInfo();
                        if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                            throw new \PDOException($errorInfo[0]);
                            
                        /*
                        * ufak bir trik var. 
                        * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                        * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                        * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                        */
                            
                     //   $Id= intval($params['working_personnel_id']) ;
                        
                            
                         $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                    array('table_name' => 'info_firm_working_personnel_education', 'id' => $Id,));
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
                        $errorInfoColumn = 'diploma_name';
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
     * @ Gridi doldurmak için info_firm_working_personnel_education tablosundan kayıtları döndürür !!
     * @version v 1.0  19.07.2016
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
            $sort = "fwp.name, fwp.surname, a.diploma_name";
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
                        fwp.firm_id,
			fwp.name, 
			fwp.surname, 
			a.diploma_name,	
			a.country_id,	
			COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS country_name,
			sc.name_eng AS country_name_eng,
			a.university_id,
			COALESCE(NULLIF(sux.name, ''), su.name_eng) AS university_name,
			su.name_eng AS university_name_eng,
			a.graduation_date,
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
                    FROM info_firm_working_personnel_education a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    LEFT JOIN sys_universities su ON su.id = a.university_id AND su.deleted =0 AND su.active=0 AND su.language_parent_id = 0 
		    LEFT JOIN sys_universities sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND sux.deleted =0 AND sux.active=0 AND sux.language_id = lx.id
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    LEFT JOIN sys_universities scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active=0 AND scx.language_id = lx.id
                    
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel_education ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0                    
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
     * @ Gridi doldurmak için info_firm_working_personnel_education tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  19.07.2016
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
                FROM info_firm_working_personnel_education a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                    
                INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                INNER JOIN info_users u ON u.id = a.op_user_id
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0
                INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0

                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
                INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 19.07.2016 
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
                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));                            
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    //$getFirmId = $getFirm ['resultSet'][0]['firm_id'];
                   
                    $this->makePassive(array('id' => $params['id']));
                    $operationIdValue = -3;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 49, 'type_id' => 3,));
                    if (\Utill\Dal\Helper::haveRecord($operationId)) {
                        $operationIdValue = $operationId ['resultSet'][0]['id'];
                    }
                    $sql = "                
                  INSERT INTO info_firm_working_personnel_education(                            
                            consultant_id,
                            operation_type_id,
                            language_id,
                            op_user_id, 
                            profile_public,
                            act_parent_id, 
                            
                            working_personnel_id,
                            diploma_name, 
                            country_id, 
                            university_id, 
                            graduation_date,
                            language_parent_id,
                            active,
                            deleted 
                        )
                        SELECT
                            consultant_id,
                            " . intval($operationIdValue) . ",
                            language_id,
                            " . intval($opUserIdValue) . ",
                            profile_public,
                            act_parent_id,                           
                            working_personnel_id,
                            diploma_name, 
                            country_id, 
                            university_id, 
                            graduation_date,
                            language_parent_id,
                            1,
                            1
                        FROM info_firm_working_personnel_education 
                        WHERE id =  " . intval($params['id']) . " 
                        "; 
                    $statement_act_insert = $pdo->prepare($sql);
                   //    echo debugPDO($sql, $params);
                    $insert_act_insert = $statement_act_insert->execute();
                    $affectedRows = $statement_act_insert->rowCount();
                    $insertID = $pdo->lastInsertId('info_firm_working_personnel_education_id_seq');
                    /*
                     * ufak bir trik var. 
                     * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                     * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                     * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                     */
                    $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                    array('table_name' => 'info_firm_working_personnel_education', 'id' => $params['id'],));
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
     * @version v 1.0  19.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalEducationNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                // $opUserIdValue = $userId ['resultSet'][0]['user_id'];               
                $firmIdValue = NULL;
                $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];
                        }
                    }
                    $WorkingPersonnelId = 0;
                    if ((isset($params['working_personnel_id']) && $params['working_personnel_id'] != "")) {
                        $WorkingPersonnelId = $params['working_personnel_id'];
                    }

                    $sql = " 
                    SELECT 
                        a.id,
                        a.working_personnel_id,
			COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS country_name,
			sc.name_eng AS country_name_eng,
			a.university_id,
			COALESCE(NULLIF(sux.name, ''), su.name_eng) AS university_name,
			su.name_eng AS university_name_eng,
			a.graduation_date,			
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        ifk.network_key
                    FROM info_firm_working_personnel_education a
		    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id 
                    
                    LEFT JOIN info_firm_working_personnel_education ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0
                    LEFT JOIN sys_universities su ON su.id = a.university_id AND su.deleted =0 AND su.active=0 AND su.language_parent_id = 0 
		    LEFT JOIN sys_universities sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND sux.deleted =0 AND sux.active=0 AND sux.language_id = lx.id
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    LEFT JOIN sys_universities scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active=0 AND scx.language_id = lx.id
		    WHERE 
                        fwp.firm_id = " . intval($firmIdValue) . " AND
                        a.working_personnel_id = " . intval($WorkingPersonnelId) . " AND
                        a.cons_allow_id =2 AND 
			a.profile_public=0 
		      ORDER BY  fwp.name, fwp.surname, a.graduation_date desc
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
     * @version v 1.0  19.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalEducationNpkRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                // $opUserIdValue = $userId ['resultSet'][0]['user_id'];               
                $firmIdValue = NULL;
                $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                    $WorkingPersonnelId = 0;
                    if ((isset($params['working_personnel_id']) && $params['working_personnel_id'] != "")) {
                        $WorkingPersonnelId = $params['working_personnel_id'];
                    }

                    $sql = " 
                    SELECT 
                        COUNT(a.id) AS count
                    FROM info_firm_working_personnel_education a
		    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0                    
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id                     
                    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 		    
		    WHERE 
                        fwp.firm_id = " . intval($firmIdValue) . " AND
                        a.working_personnel_id = " . intval($WorkingPersonnelId) . " AND
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
     * @version v 1.0  19.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalEducationNpkQuest($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $firmIdValue = NULL;
            $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
            if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }
                
                $WorkingPersonnelId = 0;
                if ((isset($params['working_personnel_id']) && $params['working_personnel_id'] != "")) {
                    $WorkingPersonnelId = $params['working_personnel_id'];
                }

                $sql = " 
                    SELECT 
                        a.id,                        
			COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS country_name,
			sc.name_eng AS country_name_eng,			
			COALESCE(NULLIF(sux.name, ''), su.name_eng) AS university_name,
			su.name_eng AS university_name_eng,
			a.graduation_date,			
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		        COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                        ifk.network_key
                    FROM info_firm_working_personnel_education a
		    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id 
                    
                    LEFT JOIN info_firm_working_personnel_education ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0
                    LEFT JOIN sys_universities su ON su.id = a.university_id AND su.deleted =0 AND su.active=0 AND su.language_parent_id = 0 
		    LEFT JOIN sys_universities sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND sux.deleted =0 AND sux.active=0 AND sux.language_id = lx.id
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    LEFT JOIN sys_universities scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active=0 AND scx.language_id = lx.id
                    WHERE 
                        fwp.firm_id = " . intval($firmIdValue) . " AND
                        a.working_personnel_id = " . intval($WorkingPersonnelId) . " AND
                        a.cons_allow_id =2 AND 
			a.profile_public=0 
                    ORDER BY  fwp.name, fwp.surname, a.diploma_name
                      
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
     * @version v 1.0  19.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalEducationNpkQuestRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $firmIdValue = NULL;
            $getFirm = InfoFirmProfile :: getFirmIdsForNetworkKey(array('network_key' => $params['network_key']));
            if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                $firmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                $WorkingPersonnelId = 0;
                if ((isset($params['working_personnel_id']) && $params['working_personnel_id'] != "")) {
                    $WorkingPersonnelId = $params['working_personnel_id'];
                }
                $sql = " 
                    SELECT 
                        COUNT(a.id) AS count 
                    FROM info_firm_working_personnel_education a
		    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id 
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    WHERE 
                        fwp.firm_id = " . intval($firmIdValue) . " AND
                        a.working_personnel_id = " . intval($WorkingPersonnelId) . " AND
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
     * @ info_firm_working_personnel_education bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  19.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalEducationListGrid($params = array()) {
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
                $sort = "name, surname, diploma_name";
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
            $sorguStr2 = null;
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
                            case 'diploma_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND diploma_name" . $sorguExpression . ' ';

                                break;     
                            case 'country_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND country_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'country_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND country_name_eng" . $sorguExpression . ' ';
                            
                                break;  
                            case 'university_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND university_name" . $sorguExpression . ' ';
                            
                                break; 
                            case 'university_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND university_name_eng" . $sorguExpression . ' ';
                            
                                break; 
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';
                            
                                break;
                            case 'state_profile_public':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND state_profile_public" . $sorguExpression . ' ';
                            
                                break; 
                            case 'operation_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND operation_name" . $sorguExpression . ' ';
                            
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
            
                            
            if (isset($params['diploma_name']) && $params['diploma_name'] != "") {
                $sorguStr2 .= " AND a.diploma_name Like '%" . $params['diploma_name'] . "%'";
            } 
            if (isset($params['country_id']) && $params['country_id'] != "") {
                $sorguStr2 .= " AND a.country_id = " . intval($params['country_id']) ;
            } 
            if (isset($params['university_id']) && $params['university_id'] != "") {
                $sorguStr2 .= " AND a.university_id = " . intval($params['university_id']) ;
            }
            if (isset($params['profile_public']) && $params['profile_public'] != "") {
                $sorguStr2 .= " AND a.profile_public = " . intval($params['profile_public']) ;
            }
            if (isset($params['active']) && $params['active'] != "") {
                $sorguStr2 .= " AND a.active = " . intval($params['active']) ;
            }
            if (isset($params['working_personnel_id']) && $params['working_personnel_id'] != "") {
                $sorguStr2 .= " AND a.working_personnel_id = " . intval($params['working_personnel_id']) ;

            }            
            
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            } 
        
            $sql = "  
                SELECT   
			id,
                        firm_id,
                        working_personnel_id,
			name, 
			surname, 
			diploma_name,	
			country_id,	
			country_name,
			country_name_eng,
			university_id,
			university_name,
			university_name_eng,
			graduation_date,
                        profile_public,
                        state_profile_public,
                        operation_type_id,
                        operation_name,
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
                        a.working_personnel_id,
                        fwp.firm_id,
			fwp.name, 
			fwp.surname, 
			a.diploma_name,	
			a.country_id,	
			COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS country_name,
			sc.name_eng AS country_name_eng,
			a.university_id,
			COALESCE(NULLIF(sux.name, ''), su.name_eng) AS university_name,
			su.name_eng AS university_name_eng,
			a.graduation_date,
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
                    FROM info_firm_working_personnel_education a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    LEFT JOIN sys_universities su ON su.id = a.university_id AND su.deleted =0 AND su.active=0 AND su.language_parent_id = 0 
		    LEFT JOIN sys_universities sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND sux.deleted =0 AND sux.active=0 AND sux.language_id = lx.id
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    LEFT JOIN sys_universities scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active=0 AND scx.language_id = lx.id
                    
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel_education ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0                    
                    WHERE a.deleted =0 AND a.language_parent_id =0 
                     ".$sorguStr2."
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
     * @ info_firm_working_personnel_education bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  19.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFirmWorkingPersonalEducationListGridRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
             $sorguStr = null;
             $sorguStr2 = null;
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
                            case 'diploma_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND diploma_name" . $sorguExpression . ' ';

                                break;     
                            case 'country_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND country_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'country_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND country_name_eng" . $sorguExpression . ' ';
                            
                                break;  
                            case 'university_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND university_name" . $sorguExpression . ' ';
                            
                                break; 
                            case 'university_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND university_name_eng" . $sorguExpression . ' ';
                            
                                break; 
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';
                            
                                break;
                            case 'state_profile_public':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND state_profile_public" . $sorguExpression . ' ';
                            
                                break; 
                            case 'operation_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND operation_name" . $sorguExpression . ' ';
                            
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
            
            
            if (isset($params['diploma_name']) && $params['diploma_name'] != "") {
                $sorguStr2 .= " AND a.diploma_name Like '%" . $params['diploma_name'] . "%'";
            } 
            if (isset($params['country_id']) && $params['country_id'] != "") {
                $sorguStr2 .= " AND a.country_id = " . intval($params['country_id']) ;
            } 
            if (isset($params['university_id']) && $params['university_id'] != "") {
                $sorguStr2 .= " AND a.university_id = " . intval($params['university_id']) ;
            }
            if (isset($params['profile_public']) && $params['profile_public'] != "") {
                $sorguStr2 .= " AND a.profile_public = " . intval($params['profile_public']) ;
            }
            if (isset($params['active']) && $params['active'] != "") {
                $sorguStr2 .= " AND a.active = " . intval($params['active']) ;
            }
            if (isset($params['working_personnel_id']) && $params['working_personnel_id'] != "") {
                $sorguStr2 .= " AND a.working_personnel_id = " . intval($params['working_personnel_id']) ;

            }            
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }
            
            $sql = " 
                SELECT COUNT(id) AS count 
                FROM (               
                   SELECT 
                         a.id,
                        a.working_personnel_id,
                        fwp.firm_id,
			fwp.name, 
			fwp.surname, 
			a.diploma_name,	
			a.country_id,	
			COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS country_name,
			sc.name_eng AS country_name_eng,
			a.university_id,
			COALESCE(NULLIF(sux.name, ''), su.name_eng) AS university_name,
			su.name_eng AS university_name_eng,
			a.graduation_date,
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
                    FROM info_firm_working_personnel_education a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                    INNER JOIN info_firm_working_personnel fwp ON fwp.id = a.working_personnel_id AND fwp.deleted=0 AND fwp.active =0 AND fwp.language_id = a.language_id 
                    INNER JOIN info_users u ON u.id = a.op_user_id
                    INNER JOIN info_firm_profile fp ON fp.act_parent_id = fwp.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                    LEFT JOIN sys_universities su ON su.id = a.university_id AND su.deleted =0 AND su.active=0 AND su.language_parent_id = 0 
		    LEFT JOIN sys_universities sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND sux.deleted =0 AND sux.active=0 AND sux.language_id = lx.id
		    INNER JOIN sys_countrys sc ON sc.id = a.country_id AND a.deleted=0 AND a.active =0 AND sc.language_parent_id =0 
		    LEFT JOIN sys_universities scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active=0 AND scx.language_id = lx.id
                    
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    LEFT JOIN info_firm_working_personnel_education ax ON (ax.id = a.id OR ax.language_parent_id = a.id) and ax.language_id =lx.id  AND ax.deleted =0 AND ax.active =0

		    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0 AND op.language_parent_id =0
                    LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                    
		    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND a.cons_allow_id = sd14.first_group AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.deleted =0 AND sd15.active =0 AND sd15.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.deleted = 0 AND sd16.active = 0 AND sd16.language_parent_id =0
		    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		    
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
                    LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0                    
                    WHERE a.deleted =0 AND a.language_parent_id =0
                    ".$sorguStr2."
                    ) AS xtable WHERE deleted =0  
                        ".$sorguStr."   
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

    
}
