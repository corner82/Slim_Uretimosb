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
 * @since 01.09.2016
 */
class InfoUsersSendingMail extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_users_sending_mail tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  01.09.2016
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
                UPDATE info_users_sending_mail
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
     * @ info_users_sending_mail tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  01.09.2016  
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
                    iud.root_id AS user_id,
                    iud.name,
                    iud.surname,
                    COALESCE(NULLIF(setex.name, ''),sete.name_eng) AS email_template_name,
                    sete.name_eng As email_template_name_eng,
                    a.auth_allow_id,
                    sete.link,
                    a.key,
                    a.s_date,
                    a.c_date,
                    a.e_date,                    
                    a.deleted,  
                    COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,                  
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,
                    a.op_user_id,
                    u.username AS op_user_name                    
                FROM info_users_sending_mail a
                INNER JOIN info_users_detail iud on iud.root_id = a.user_id AND iud.deleted =0 AND iud.active =0
                INNER JOIN sys_language l ON l.id = iud.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
		INNER JOIN sys_email_template sete ON sete.id = a.act_email_template_id AND sete.active=0 AND sete.deleted=0 AND l.id = sete.language_id
		LEFT JOIN sys_email_template setex ON (setex.id = sete.id OR setex.language_parent_id = sete.id) AND setex.language_id = lx.id AND setex.active =0 AND setex.deleted =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                WHERE a.deleted =0 AND iud.language_parent_id =0
                ORDER BY iud.root_id desc
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
     * @ info_users_sending_mail tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  01.09.2016
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
                $kontrol = $this->haveRecords(array(
                    'sys_socialmedia_id' => $params['sys_socialmedia_id'],
                    'user_link' => $params['user_link'],
                    'user_id' => $opUserIdValue));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {

                    $operationIdValue = -1;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 42, 'type_id' => 1,));
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
                    $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_users_sending_mail', 
                                                                                          'operation_type_id' => $operationIdValue, 
                                                                                          'language_id' => $languageIdValue, 
                                                                                            ));
                    if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                        $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                    }

                    $sql = "
                INSERT INTO info_users_sending_mail(                        
                        user_id, 
                        profile_public, 
                        op_user_id, 
                        operation_type_id, 
                        consultant_id, 
                        sys_socialmedia_id, 
                        act_parent_id, 
                        user_link
                         )
                VALUES (
                        :user_id, 
                        :profile_public, 
                        :op_user_id, 
                        :operation_type_id, 
                        :consultant_id, 
                        :sys_socialmedia_id, 
                        (SELECT last_value FROM info_users_sending_mail_id_seq), 
                        :user_link
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                    $statement->bindValue(':operation_type_id', $operationIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':consultant_id', $ConsultantId, \PDO::PARAM_INT);
                    $statement->bindValue(':sys_socialmedia_id', $params['sys_socialmedia_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':user_link', $params['user_link'], \PDO::PARAM_STR);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_users_sending_mail_id_seq');
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
                    $errorInfo = '23505';
                    $errorInfoColumn = 'sys_socialmedia_id';
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
     * @ info_users_sending_mail tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  01.09.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_users_sending_mail
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
     * @ info_users_sending_mail tablosundan parametre olarak gelen key kaydını gunceller !!
     * @version v 1.0  01.09.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function setClickOnTheLinks($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_users_sending_mail
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone)                     
                WHERE 
                    key = :key AND 
                    active =0 AND 
                    deleted =0 AND 
                    auth_allow_id =0 AND
                    c_date is null    
            ");
            $statement->bindValue(':key', $params['key'], \PDO::PARAM_STR);
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
     * @ info_users_sending_mail tablosundan parametre olarak gelen key kaydını siler !!
     * @version v 1.0  01.09.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function setDeletedOnTheLinks($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_users_sending_mail
                SET                         
                    c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone),
                    auth_allow_id = 1,
                    active= 1,
                    deleted = 1                     
                WHERE key = :key AND 
                    active =0 AND 
                    deleted =0 AND 
                    auth_allow_id =0 AND
                    c_date is not null ");
            $statement->bindValue(':key', $params['key'], \PDO::PARAM_STR);
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
     * info_users_sending_mail tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  01.09.2016
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
                    'sys_socialmedia_id' => $params['sys_socialmedia_id'],
                    'user_link' => $params['user_link'],
                    'user_id' => $opUserIdValue));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $this->makePassive(array('id' => $params['id']));
                    $operationIdValue = -2;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 42, 'type_id' => 2,));
                    if (\Utill\Dal\Helper::haveRecord($operationId)) {
                        $operationIdValue = $operationId ['resultSet'][0]['id'];
                    }

                    $sql = "  
                    INSERT INTO info_users_sending_mail(
                        user_id, 
                        profile_public, 
                        op_user_id, 
                        operation_type_id, 
                        consultant_id, 
                        sys_socialmedia_id, 
                        act_parent_id, 
                        user_link
                        )
                        SELECT  
                            user_id,
                            " . intval($params['profile_public']) . " AS profile_public,                           
                            " . intval($opUserIdValue) . " AS op_user_id,  
                            " . intval($operationIdValue) . " AS operation_type_id,  
                            consultant_id, 
                            " . intval($params['sys_socialmedia_id']) . " AS sys_socialmedia_id,
                            act_parent_id,                                                   
                            '" . $params['user_link'] . "' AS user_link
                        FROM info_users_sending_mail 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                    $statement_act_insert = $pdo->prepare($sql);
                    //   echo debugPDO($sql, $params);
                    $insert_act_insert = $statement_act_insert->execute();
                    $affectedRows = $statement_act_insert->rowCount();                    
                    $insertID = $pdo->lastInsertId('info_users_sending_mail_id_seq');                               
                    $errorInfo = $insert_act_insert->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    /*
                    * ufak bir trik var. 
                    * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                    * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                    * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                     * Not : data da language_id yoksa asagıdaki kullanılabilinir.
                    */
                    $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                               array('table_name' => 'info_users_sending_mail', 'id' => $params['id'],));
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
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
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
     * @ Gridi doldurmak için info_users_sending_mail tablosundan kayıtları döndürür !!
     * @version v 1.0  01.09.2016
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
            $sort = "iud.language_id, iud.root_id ";
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
                    iud.root_id AS user_id,
                    iud.name,
                    iud.surname,
                    COALESCE(NULLIF(smx.name, ''),sm.name_eng) AS socialmedia_name,
                    sm.name_eng As socialmedia_eng,
                    a.user_link,
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    sm.abbreviation,
                    a.act_parent_id,
                    a.s_date,
                    a.c_date
                FROM info_users_sending_mail a
                INNER JOIN info_users_detail iud on iud.root_id = a.user_id AND iud.deleted =0 AND iud.active =0 
		INNER JOIN info_firm_users ifu ON ifu.user_id = a.user_id AND ifu.active = 0 AND ifu.deleted = 0 AND ifu.language_parent_id =0   
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = ifu.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                 
                INNER JOIN info_firm_keys fk ON  ifu.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = iud.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id
		LEFT JOIN sys_socialmedia smx ON (smx.id = sm.id OR smx.language_parent_id = sm.id) AND smx.language_id = lx.id AND smx.active =0 AND smx.deleted =0                   
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0
                LEFT JOIN sys_operation_types opx ON (opx.id = a.operation_type_id OR opx.language_parent_id = a.operation_type_id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id    
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0                
                WHERE a.deleted =0 AND iud.language_parent_id =0			
           
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
     * @ Gridi doldurmak için info_users_sending_mail tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  01.09.2016
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
                FROM info_users_sending_mail a
                INNER JOIN info_users_detail iud on iud.root_id = a.user_id AND iud.deleted =0 AND iud.active =0 
		INNER JOIN info_firm_users ifu ON ifu.user_id = a.user_id AND ifu.active = 0 AND ifu.deleted = 0 AND ifu.language_parent_id =0   
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = ifu.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0                 
                INNER JOIN info_firm_keys fk ON  ifu.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = iud.language_id AND l.deleted =0 AND l.active = 0                 
                INNER JOIN sys_socialmedia sm ON sm.id = a.sys_socialmedia_id AND sm.deleted =0 AND sm.active =0 AND sm.language_id = l.id		
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id =l.id  AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id                    
                WHERE a.deleted =0 AND iud.language_parent_id =0              
                              
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
     * @ info_users_sending_mail tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  01.09.2016
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
                UPDATE info_users_sending_mail
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM info_users_sending_mail
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
     * delete olayında önce kaydın active özelliğini pasif e olarak değiştiriyoruz. 
     * daha sonra deleted= 1 ve active = 1 olan kaydı oluşturuyor. 
     * böylece tablo içerisinde loglama mekanizması için gerekli olan kayıt oluşuyor.
     * @version 01.09.2016 
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
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 42, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }

                $sql = "  
                    INSERT INTO info_users_sending_mail(
                        user_id, 
                        profile_public, 
                        op_user_id, 
                        operation_type_id, 
                        consultant_id, 
                        sys_socialmedia_id, 
                        act_parent_id, 
                        user_link,
                        active,
                        deleted
                        )
                        SELECT  
                            user_id,
                            profile_public,                           
                            " . intval($opUserIdValue) . " AS op_user_id,  
                            " . intval($operationIdValue) . " AS operation_type_id,  
                            consultant_id, 
                            sys_socialmedia_id,
                            act_parent_id,                                                   
                            user_link,
                            1,
                            1    
                        FROM info_users_sending_mail 
                        WHERE id =  " . intval($params['id']) . " 
                        ";
                $statement_act_insert = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $insert_act_insert = $statement_act_insert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_users_sending_mail_id_seq');
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_users_sending_mail', 'id' => $params['id'],));
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
     * @ parametre olarak verilen user a onay emaili gönderir. !!
     * @version v 1.0  01.09.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function sendMailUrgeNewPerson($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');

            $mailTemplate = new \Utill\Mail\Template\MailTemplate();
            $mailTemplate->setContentRetrieverStartegyClass(new \Utill\Mail\Template\ContentRetrieverFromFileStrategy);
            $mailTemplate->setTemplateContent(array('fileName' => 'kume_confirm'));            
            $message = $mailTemplate->replaceAndGetTemplateContent(array(
                        '{[herkimse]}' => $params['herkimse'],
                        '{[kume]}' =>  $params['kume'],
                        '{[rol]}' => $params['rol'],
                        '{[link]}' => 'https://zeynel.sanalfabrika.com/ostim/sanalfabrika/signupconfirmation?key='. $params['key']));

            $mail = new \Utill\Mail\PhpMailer\PhpMailInfoWrapper();
            $mail->setCharset('UTF-8');
            $mail->setSMTPServerHost('mail.ostimteknoloji.com');
            $mail->setSMTPServerUser('sanalfabrika@ostimteknoloji.com');
            $mail->setSMTPServerUserPassword('1q2w3e4r');
            $mail->setFromUserName('sanalfabrika@ostimteknoloji.com');
            $mail->setMessage($message);
            $params = ['subject' => 'Sanal Fabrika Küme Çalışanı Onay İşlemi',
                'info' => 'Sanal Fabrika Yöneticileri tarafından '
                . '              sisteme Küme Çalışanı olarak onaylanmanız amacıyla gönderilmiştir',
                'to' =>  '311corner82@gmail.com']; //$params['auth_email'] ];  // 311corner82@gmail.com
            $mail->sendInfoMailSMTP($params);
            $sql = "";
            $statement = $pdo->prepare($sql);
            //  echo debugPDO($sql, $params);                
            // $statement->execute();
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
     * sys_osb_person tablosundaki urgeci kaydı oluşturur  !!
     * @author Okan CIRAN
     * @version v 1.0  31.08.2016
     * @param array | null $args
     * @return array
     * @throws PDOException
     */
    public function insertSendingMail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
           
                $sql = " 
                INSERT INTO info_users_sending_mail(
                            user_id, 
                            auth_email, 
                            act_email_template_id, 
                            op_user_id, 
                            key
                            )
                VALUES (  
                            ". intval($params['user_id']).",
                            '". $params['auth_email']."',
                            ". intval($params['act_email_template_id']).", 
                            ". intval($params['op_user_id']).", 
                            '". $params['key']."'
                    )";                                
                $statement = $pdo->prepare($sql);                               
               // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_sending_mail_id_seq');                                
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);  
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    
}
