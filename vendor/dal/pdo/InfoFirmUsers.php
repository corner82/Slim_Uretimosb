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
class InfoFirmUsers extends \DAL\DalSlim {

    /**

     * @author Okan CIRAN
     * @ info_firm_users tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  19.04.2016
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
                UPDATE info_firm_users
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id = ". intval($params['id']));         
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
     * @ info_firm_users tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  19.04.2016   
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
	            a.user_id,                    
                    ud.name,
                    ud.surname,               
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.title, ''),a.title_eng), ''),  a.title) AS title,
		    COALESCE(NULLIF(COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng), ''), fp.firm_name) AS firm_names,		  		    
                    a.deleted, 		                
		    COALESCE(NULLIF(COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng), ''), sd15.description) AS state_deleted,		  
                    a.active, 		                          
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,                    
		    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_names,		                       
                    a.op_user_id,                    
                    u.username AS op_username,
                    a.operation_type_id,                    
                    COALESCE(NULLIF(COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng), ''), op.operation_name) AS operation_name,                    
                    a.s_date,
                    a.c_date,
                    a.consultant_id,                
                    a.confirm_id,
                    ifk.network_key AS Ref_network_key,
                    CASE COALESCE(NULLIF(ud.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture
                FROM info_firm_profile fp                 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                    
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0   
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN info_firm_users ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id = lx.id  
                INNER JOIN info_users u ON u.id = a.op_user_id  
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0                 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id                
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.id OR fpx.id=fp.id) AND fpx.active =0 AND fpx.deleted =0 AND fpx.language_id = lx.id                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0                
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id AND sd15x.deleted =0 --AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 --AND sd16x.active = 0
	        WHERE fp.language_parent_id = 0   			
			AND fp.active =0 AND fp.deleted =0    
			-- and ifk.network_key = 'TR39888230632752543'            
               ORDER BY firm_names, ud.name, ud.surname  
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
     * @ info_firm_users tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
     * @version v 1.0  19.04.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makePassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                UPDATE info_firm_users
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
     * @ kayıtlı kullanıcılar info_firm_users tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  19.04.2016
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
                    $operationIdValue = 1;
                    $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 31, 'type_id' => 1,));
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

                    $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_firm_users' , 
                                                                                        'operation_type_id' => $operationIdValue, 
                                                                                        'language_id' => $languageIdValue,  
                                                                                               ));
                    if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                        $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                    } else {
                        $ConsultantId = 1001;
                    }

                    $statement = $pdo->prepare("
                        INSERT INTO info_firm_users (
                                firm_id,    
                                user_id,                                 
                                description, 
                                description_eng,                                 
                                title,
                                title_eng,
                                language_id,  
                                operation_type_id, 
                                op_user_id,
                                consultant_id,
                                act_parent_id
                                )                        
                        VALUES (
                                :firm_id,    
                                :user_id,                                                        
                                :description, 
                                :description_eng,                                 
                                :title,
                                :title_eng,
                                " . intval($languageIdValue) . ",
                                " . intval($operationIdValue) . ",
                                " . intval($opUserIdValue) . ",
                                " . intval($ConsultantId) . ",                                
                                (SELECT last_value FROM info_firm_users_id_seq)
                                               ) ");
                    $statement->bindValue(':firm_id', $params['firm_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                    $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':title', $params['title'], \PDO::PARAM_STR);
                    $statement->bindValue(':title_eng', $params['title_eng'], \PDO::PARAM_STR);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('info_firm_users_id_seq');
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
     * basic have records control  
     * * returned result set example;
     * for success result  
     * usage     
     * @author Okan CIRAN
     * @ info_firm_users tablosunda ref_firm_id & firm_id sutununda daha önce oluşturulmuş mu?      
     * @version v 1.0 19.04.2016
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
                a.user_id AS user_id  , 
                a.user_id  AS value , 
                a.user_id  = " . $params['user_id'] . " AS control,                
                CONCAT('Bu Kullanıcı Daha Önce Firmanıza Kayıt Edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_users a
            WHERE a.user_id = " . $params['user_id'] . " AND 		
                a.firm_id =  " . $params['firm_id'] . "  		
                " . $addSql . "                 
                AND a.deleted=0  
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
     * info_firm_users tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  19.04.2016
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
                                    array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 31, 'type_id' => 2,));
                    if (\Utill\Dal\Helper::haveRecord($operationId)) {
                        $operationIdValue = $operationId ['resultSet'][0]['id'];
                    }
                    $profilePublic = 0;
                    if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                        $profilePublic = intval($params['profile_public']);
                    }

                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];
                        }
                    }

                    $FirmId = 0;
                    if ((isset($params['firm_id']) && $params['firm_id'] != "")) {
                        $active = intval($params['firm_id']);
                    }
                    

                    $statementInsert = $pdo->prepare("
                INSERT INTO info_firm_users (
                        active,
                        deleted,
                        profile_public,
                        firm_id,
                        user_id,
                        description, 
                        description_eng,
                        title,
                        title_eng,
                        language_id,  
                        operation_type_id, 
                        op_user_id,
                        consultant_id,
                        act_parent_id
                        )  
                SELECT
                    active, 
                    deleted,                    
                    " . intval($profilePublic) . " AS profile_public,
                    " . intval($params['firm_id']) . " AS firm_id,
                    " . intval($params['user_id']) . " AS user_id,
                    '" . $params['description'] . "' AS description,
                    '" . $params['description_eng']. "' AS description_eng,
                    '" . $params['title'] . "' AS title,
                    '" . $params['title_eng'] . "' AS title_eng,
                    " . intval($languageIdValue)." AS languageId,
                    " . intval($operationIdValue) . " AS operation_type_id,
                    " . intval($opUserIdValue) . " AS op_user_id,
                    consultant_id,                    
                    act_parent_id  
                FROM info_firm_users
                WHERE id  =" . intval($params['id']) . " 
                                                ");
                    $result = $statementInsert->execute();
                    $insertID = $pdo->lastInsertId('info_firm_users_id_seq');
                    $affectedRows = $statement->rowCount();
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
                                array('table_name' => 'info_firm_users', 'id' => $params['id'],));
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
                    $errorInfoColumn = 'user_id';
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
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_users tablosundan kayıtları döndürür !!
     * @version v 1.0  19.04.2016
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
            $sort = "firm_name, ud.name, ud.surname";
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
	            a.user_id,
                    ud.name,
                    ud.surname,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.title, ''),a.title_eng), ''),  a.title) AS title,
                    a.title_eng,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.description, ''),a.description_eng), ''),  a.description) AS description,
                    a.description_eng,
		    COALESCE(NULLIF(COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng), ''), fp.firm_name) AS firm_name,
                    a.deleted,
		    COALESCE(NULLIF(COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng), ''), sd15.description) AS state_deleted,
                    a.active,
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,
		    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                    a.op_user_id,                    
                    u.username AS op_user_name,
                    a.operation_type_id,                    
                    COALESCE(NULLIF(COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng), ''), op.operation_name) AS operation_name,                    
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.confirm_id,
                    ifk.network_key AS network_key,
                    CASE COALESCE(NULLIF(ud.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture
                FROM info_firm_profile fp 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0 
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0 
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN info_firm_users ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id = lx.id  
                INNER JOIN info_users u ON u.id = a.op_user_id  
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id 
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.id OR fpx.id=fp.id) AND fpx.active =0 AND fpx.deleted =0 AND fpx.language_id = lx.id 
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0 
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id AND sd15x.deleted =0  
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0  
	        WHERE fp.language_parent_id = 0 
			AND fp.active =0 AND fp.deleted =0 			           
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
     * @ user in ekledigi userları info_firm_users tablosundan döndürür !!
     * @version v 1.0  19.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingular($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $whereSql = " AND a.op_user_id = " . $userId ['resultSet'][0]['user_id'];
                 
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
                    a.id,                     
	            a.user_id,                    
                    ud.name,
                    ud.surname,               
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.title, ''),a.title_eng), ''),  a.title) AS title,
		    COALESCE(NULLIF(COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng), ''), fp.firm_name) AS firm_names,		  		    
                    a.deleted, 		                
		    COALESCE(NULLIF(COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng), ''), sd15.description) AS state_deleted,		  
                    a.active, 		                          
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,                    
		    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_names,		                       
                    a.op_user_id,                    
                    u.username AS op_username,
                    a.operation_type_id,                    
                    COALESCE(NULLIF(COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng), ''), op.operation_name) AS operation_name,                    
                    a.s_date,
                    a.c_date,
                    a.consultant_id,                
                    a.confirm_id,
                    ifk.network_key AS Ref_network_key,
                    CASE COALESCE(NULLIF(ud.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture
                FROM info_firm_profile fp                 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                    
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0   
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN info_firm_users ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id = lx.id  
                INNER JOIN info_users u ON u.id = a.op_user_id  
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0                 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id                
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.id OR fpx.id=fp.id) AND fpx.active =0 AND fpx.deleted =0 AND fpx.language_id = lx.id                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0                
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id AND sd15x.deleted =0 --AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 --AND sd16x.active = 0
	        WHERE fp.language_parent_id = 0   			
			AND fp.active =0 AND fp.deleted =0    			
                " . $whereSql . "
                ORDER BY firm_names, ud.name, ud.surname
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_users tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  19.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $whereSql = " AND a.op_user_id = " . $userIdValue;                                
                $sql = "                              
                SELECT 
                    COUNT(a.id) AS COUNT
                FROM info_firm_profile fp                 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                    
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0   
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                INNER JOIN info_users u ON u.id = a.op_user_id  
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0                 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
                WHERE fp.language_parent_id = 0  
                " . $whereSql . "
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_firm_users tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  19.04.2016
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
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0   
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                INNER JOIN info_users u ON u.id = a.op_user_id 
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id 
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
	        WHERE fp.language_parent_id = 0 
			AND fp.active =0 AND fp.deleted =0 
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
     * @ info_firm_users tablosundan parametre olarak  gelen id kaydın active alanını 1 yapar ve 
     * yeni yeni kayıt oluşturarak deleted ve active = 1 olarak  yeni kayıt yapar. ! 
     * @version v 1.0  19.04.2016
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
                $userIdValue = $userId ['resultSet'][0]['user_id'];         
                
                $operationIdValue = -3;  
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                            array('parent_id' =>3,'main_group' =>3,'sub_grup_id' =>27,'type_id'=>3, ));                
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                 
                $this->makePassive(array('id' => $params['id']));
                
                $statementInsert = $pdo->prepare(" 
                     INSERT INTO info_firm_users (
                        active,
                        deleted,
                        op_user_id, 
                        operation_type_id,                        
                        act_parent_id, 
                        consultant_id, 
                        consultant_confirm_type_id, 
                        confirm_id,   
                        firm_id,
                        ref_firm_id,
                        total_project,
                        continuing_project,
                        unsuccessful_project 
                        )  
                SELECT                 
                    1 AS active, 
                    1 as deleted,    
                    " . intval($userIdValue) . " AS op_user_id,  
                    " . intval($operationIdValue) . " AS operation_type_id,                                        
                    act_parent_id, 
                    consultant_id, 
                    consultant_confirm_type_id, 
                    confirm_id,   
                    firm_id,
                    ref_firm_id,
                    total_project,
                    continuing_project,
                    unsuccessful_project   
                FROM info_firm_users
                WHERE id  =" . intval($params['id']) . "  
                     ");

                $insertAct = $statementInsert->execute();
                $affectedRows = $statement_act_insert->rowCount();
                $insertID = $pdo->lastInsertId('info_firm_users_id_seq');
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_firm_users', 'id' => $params['id'],));
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
     * @ firma network_key ine ait userları info_firm_users tablosundan döndürür !!
     * @version v 1.0  20.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                if (isset($params['page']) && $params['page'] != "" && isset($params['rows']) && $params['rows'] != "") {
                    $offset = ((intval($params['page']) - 1) * intval($params['rows']));
                    $limit = intval($params['rows']);
                } else {
                    $limit = 10;
                    $offset = 0;
                }
                $whereSql = '';
                $sortArr = array();
                $orderArr = array();
                if (isset($params['sort']) && $params['sort'] != "") {
                    $sort = trim($params['sort']);
                    $sortArr = explode(",", $sort);
                    if (count($sortArr) === 1)
                        $sort = trim($params['sort']);
                } else {
                    $sort = "firm_name, ud.name, ud.surname";
                }

                if (isset($params['order']) && $params['order'] != "") {
                    $order = trim($params['order']);
                    $orderArr = explode(",", $order);

                    if (count($orderArr) === 1)
                        $order = trim($params['order']);
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
                $sql = "
                SELECT 
                    a.id,                     
	            a.user_id,                    
                    ud.name,
                    ud.surname,               
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.title, ''),a.title_eng), ''),  a.title) AS title,
                    a.title_eng,
                    COALESCE(NULLIF(COALESCE(NULLIF(ax.description, ''),a.description_eng), ''),  a.description) AS description,
                    a.description_eng,
		    COALESCE(NULLIF(COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng), ''), fp.firm_name) AS firm_name,		  		    
                    a.deleted, 		                
		    COALESCE(NULLIF(COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng), ''), sd15.description) AS state_deleted,		  
                    a.active, 		                          
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,                    
		    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_names,		                       
                    a.op_user_id,                    
                    u.username AS op_user_name,
                    a.operation_type_id,                    
                    COALESCE(NULLIF(COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng), ''), op.operation_name) AS operation_name,                    
                    a.s_date,
                    a.c_date,
                    a.consultant_id,                
                    a.confirm_id,
                    ifk.network_key AS network_key,
                    CASE COALESCE(NULLIF(ud.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/'  ,'image_not_found.png')
                        ELSE CONCAT(ifk.folder_name ,'/',ifk.members_folder,'/' ,COALESCE(NULLIF(ud.picture, ''),'image_not_found.png')) END AS picture
                FROM info_firm_profile fp                 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                    
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0   
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN info_firm_users ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id = lx.id  
                INNER JOIN info_users u ON u.id = a.op_user_id  
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0                 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id                
                LEFT JOIN info_firm_profile fpx ON (fpx.language_parent_id = fp.id OR fpx.id=fp.id) AND fpx.active =0 AND fpx.deleted =0 AND fpx.language_id = lx.id                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) AND opx.language_id = lx.id AND opx.deleted =0 AND opx.active =0                
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id =lx.id AND sd15x.deleted =0 --AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 --AND sd16x.active = 0
	        WHERE fp.language_parent_id = 0
                    AND fp.deleted =0
                    AND ifk.network_key = '" . $params['network_key'] . "'
                  ORDER BY    " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";        
              
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
     * @ firma network_key ine ait userları info_firm_users tablosundan döndürür !!
     * @version v 1.0  20.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularNpkRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {                  
                $sql = "
                SELECT 
                    count(a.id) as count
                FROM info_firm_profile fp                 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                                                    
                INNER JOIN info_firm_users a ON a.firm_id = fp.act_parent_id AND a.active =0 AND a.deleted =0 AND a.language_parent_id = 0   
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                 
                INNER JOIN info_users u ON u.id = a.op_user_id  
                INNER JOIN info_users_detail ud ON ud.root_id = a.user_id AND ud.deleted =0 AND ud.active =0                 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = fp.act_parent_id                                
                INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.language_id = l.id AND op.deleted =0 AND op.active =0                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
		WHERE fp.language_parent_id = 0
                    AND fp.deleted =0
                    AND ifk.network_key = '". $params['network_key']."'
                
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

    
    /*
     * @author Okan CIRAN
     * @ info_firm_users tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  20.04.2016
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
                UPDATE info_firm_users
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM info_firm_users
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
     * @ firma elemanlarının socialmedia bilgilerini kayıtlarını döndürür !!
     * @version v 1.0  21.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillCompanyUsersSocialMediaNpk($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];  
                $addSql="";
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }  
                
                if (isset($params['user_id']) && $params['user_id'] != "") {
                    $addSql .= " AND iud.root_id = ".  intval($params['user_id']) ;  
                }
                
                
                $sql = "                     
                SELECT 
                    a.id, 
                    iud.root_id AS user_id,
                    iud.name,
                    iud.surname,
                    COALESCE(NULLIF(smx.name, ''),sm.name_eng) AS socialmedia_name,
                    sm.name_eng AS socialmedia_eng,
                    a.user_link,                         
                    a.deleted,
                    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,			 
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.operation_type_id ,                        
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                   sm.abbreviation  
                FROM info_users_socialmedia a
                INNER JOIN info_users_detail iud on iud.root_id = a.user_id AND iud.deleted =0 AND iud.active =0 
		INNER JOIN info_firm_users ifu ON ifu.user_id = a.user_id AND ifu.active = 0 AND ifu.deleted = 0 AND ifu.language_parent_id =0   
                INNER JOIN info_firm_profile fp ON fp.act_parent_id = ifu.firm_id AND fp.active = 0 AND fp.deleted = 0 AND fp.language_parent_id =0
                INNER JOIN info_firm_keys fk ON  ifu.firm_id =  fk.firm_id  
                INNER JOIN sys_language l ON l.id = iud.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = 647 /*".intval($languageIdValue)." */  AND lx.deleted =0 AND lx.active =0
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
			AND fk.network_key = '" . $params['network_key'] . "'
                ".$addSql." 
                ORDER BY iud.language_id, iud.root_id
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

     

}
