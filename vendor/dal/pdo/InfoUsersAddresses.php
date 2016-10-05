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
class InfoUsersAddresses extends \DAL\DalSlim {

    /**

     * @author Okan CIRAN
     * @ info_users_addresses tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  02.02.2016
     * @param type $params
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
                UPDATE info_users_addresses
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
     * @ info_users_addresses tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  02.02.2016    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT 
                   a.id,  
                    b.root_id AS user_id,
		    b.name AS name ,
		    b.surname AS surname,        
                    a.deleted, 
		    sd.description AS state_deleted,                 
                    a.active, 
		    sd1.description AS state_active,                      
                    a.language_code, 
                    a.language_id, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                    a.language_parent_id,                                   
                    a.op_user_id,                    
                    u.username AS op_username  ,
                    b.operation_type_id,
                    op.operation_name ,                                        
                    a.profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    sd7.description AS consultant_confirm_type,   
                    a.confirm_id,
                    a.address_type_id, 
                    sd8.description AS address_type,    
                    a.address1, 
                    a.address2, 
                    a.postal_code, 
                    a.country_id, 
                    co.name AS tr_country_name,
                    a.city_id, 
                    ct.name AS tr_city_name,
                    a.borough_id, 
                    bo.name AS tr_borough_name,
                    a.city_name, 
                    a.description, 
                    a.description_eng                      
                FROM info_users_addresses  a
                inner join info_users_detail b on b.root_id = a.user_id and b.active = 0 and b.deleted = 0  
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id 
		INNER JOIN sys_specific_definitions as sd7 on sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
		INNER JOIN sys_specific_definitions as sd8 on sd8.main_group =17 AND sd8.first_group = a.address_type_id AND sd8.deleted = 0 AND sd8.active = 0 AND sd8.language_id = a.language_id 
		INNER JOIN sys_operation_types op on op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id                               
                LEFT JOIN sys_countrys co on co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = a.language_id                               
		LEFT JOIN sys_city ct on ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = a.language_id                               
		LEFT JOIN sys_borough bo on bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = a.language_id  
               
                ORDER BY concat(b.name, b.surname) , sd8.description                
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
     * @ info_users_addresses tablosundan parametre olarak  gelen id kaydını aktifliğini 1 = pasif yapar. !!
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
                UPDATE info_users_addresses
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
     * @ info_users_addresses tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  02.02.2016
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
                
                if ((isset($params['user_id']) && $params['user_id'] != "")) {
                    $userId = $params['user_id'];
                } else {
                    $userId = $opUserIdValue;
                }               

                $active=0 ; 
                if ((isset($params['active']) && $params['active'] != "")) {                    
                    $active = intval($params['active']) ;                    
                }
                
                $operationIdValue = -1;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 38, 'type_id' => 1,));
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
                $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_users_addresses' , 
                                                                                        'operation_type_id' => $operationIdValue, 
                                                                                        'language_id' => $languageIdValue,  
                                                                                               ));
                 if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                     $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                 }  
                        
                $statement = $pdo->prepare("
                        INSERT INTO info_users_addresses (
                                op_user_id,
                                user_id,
                                consultant_id,
                                active,
                                language_id,
                                operation_type_id, 
                                address_type_id, 
                                address1, 
                                address2, 
                                postal_code, 
                                country_id, 
                                city_id, 
                                borough_id, 
                                city_name, 
                                description, 
                                description_eng,
                                profile_public,
                                act_parent_id
                                )                        
                        VALUES (                                 
                                " . intval($opUserIdValue) . ",
                                " . intval($userId) . ",
                                " . intval($ConsultantId) . ",
                                ". intval($active). ", 
                                ". intval($languageIdValue). ",
                                ". intval($operationIdValue). ",                                                                 
                                :address_type_id, 
                                :address1, 
                                :address2, 
                                :postal_code, 
                                :country_id, 
                                :city_id, 
                                :borough_id, 
                                :city_name, 
                                :description, 
                                :description_eng,
                                :profile_public,
                                (SELECT last_value FROM info_users_addresses_id_seq)
                                                ");                
                $statement->bindValue(':address_type_id', $params['address_type_id'], \PDO::PARAM_INT);
                $statement->bindValue(':address1', $params['address1'], \PDO::PARAM_STR);
                $statement->bindValue(':address2', $params['address2'], \PDO::PARAM_STR);
                $statement->bindValue(':postal_code', $params['postal_code'], \PDO::PARAM_STR);
                $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
                $statement->bindValue(':city_id', $params['city_id'], \PDO::PARAM_INT);
                $statement->bindValue(':borough_id', $params['borough_id'], \PDO::PARAM_INT);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
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
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**     
     * @author Okan CIRAN
     * @ info_users_addresses tablosunda user_id & communications_type_id & communications_no sutununda daha önce oluşturulmuş mu? 
     * @todo su an için insert ve update  fonksiyonlarında aktif edilmedi. daha sonra aktif edilecek
     * @version v 1.0 02.02.2016
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
                a.address_type_id AS communications_no , 
                sd8.description AS value , 
                address_type_id =" . intval($params['address_type_id']) . " AS control,
                CONCAT(sd8.description , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_users_addresses a
	    INNER JOIN sys_specific_definitions as sd8 on sd8.main_group =17 AND sd8.first_group = a.address_type_id AND sd8.deleted = 0 AND sd8.active = 0 AND sd8.language_code = a.language_code                
            WHERE   a.user_id = '" . $params['user_id'] . "' AND 
                a.address_type_id = " . intval($params['address_type_id']) . "))                  
                 " . $addSql . "
                AND a.active =0
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
     * info_users_addresses tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  02.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];               
                $this->makePassive(array('id' => $params['id']));             
                $operationIdValue = 2;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 38, 'type_id' => 2,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }                             

                $active=0 ; 
                if ((isset($params['active']) && $params['active'] != "")) {                    
                    $active = intval($params['active']) ;                    
                } 
                
                $profilePublic=0 ; 
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {                    
                    $profilePublic = intval($params['profile_public']) ;                    
                }
                
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
                }   
                
                $statementInsert = $pdo->prepare("
                INSERT INTO info_users_addresses (  
                        user_id,
                        active, 
                        op_user_id, 
                        operation_type_id, 
                        language_id,
                        address_type_id, 
                        address1, 
                        address2, 
                        postal_code, 
                        country_id, 
                        city_id, 
                        borough_id, 
                        city_name, 
                        description, 
                        description_eng                                          
                        profile_public,                         
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id, 
                        act_parent_id, 
                        language_parent_id
                        )  
                SELECT        
                    user_id,
                    " . intval($active) . " AS active,   
                    " . intval($opUserIdValue) . " AS op_user_id,  
                    " . intval($operationIdValue) . " AS operation_type_id,                    
                    ".  intval($languageIdValue)." AS language_id,    
                    " . intval($params['address_type_id']) . " AS address_type_id,    
                    '" . $params['address1'] . "' AS address1,
                    '" . $params['address2'] . "' AS address2,
                    '" . $params['postal_code'] . "' AS postal_code,
                    " . intval($params['country_id']) . " AS country_id,   
                    " . intval($params['city_id']) . " AS city_id, 
                    " . intval($params['borough_id']) . " AS borough_id, 
                    '" . $params['city_name'] . "' AS city_name, 
                    '" . $params['description'] . "' AS description,
                    '" . $params['description_eng'] . "' AS description_eng,
                    " . intval($profilePublic) . " AS profile_Public,                          
                    consultant_id, 
                    consultant_confirm_type_id, 
                    confirm_id, 
                    act_parent_id, 
                    language_parent_id                                   
                FROM info_users_addresses 
                WHERE id  =" . intval($params['id']) . "                  
                                                ");
                $result = $statementInsert->execute();
                $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
                $affectedRows = $statementInsert->rowCount();
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
                            array('table_name' => 'info_users_addresses', 'id' => $params['id'],));
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
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**     
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_users_addresses tablosundan kayıtları döndürür !!
     * @version v 1.0  02.02.2016
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
            $sort = "CONCAT(b.name, b.surname) , sd8.description";
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
        if ((isset($params['language_code']) && $params['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }  
        $whereSql .= " AND a.language_id =   ".  intval($languageIdValue);
 
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                    a.id,  
                    b.root_id AS user_id,
		    b.name AS name ,
		    b.surname AS surname,        
                    a.deleted, 
		    sd.description AS state_deleted,                 
                    a.active, 
		    sd1.description AS state_active,                      
                    a.language_code, 
                    a.language_id, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                    a.language_parent_id,                                   
                    a.op_user_id,                    
                    u.username AS op_username  ,
                    b.operation_type_id,
                    op.operation_name ,                                        
                    a.profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    sd7.description AS consultant_confirm_type,   
                    a.confirm_id,
                    a.address_type_id, 
                    sd8.description AS address_type,    
                    a.address1, 
                    a.address2, 
                    a.postal_code, 
                    a.country_id, 
                    co.name AS tr_country_name,
                    a.city_id, 
                    ct.name AS tr_city_name,
                    a.borough_id, 
                    bo.name AS tr_borough_name,
                    a.city_name, 
                    a.description, 
                    a.description_eng                     
                FROM info_users_addresses  a
                INNER JOIN info_users_detail b ON b.root_id = a.user_id AND b.active = 0 AND b.deleted = 0  
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id 
		INNER JOIN sys_specific_definitions AS sd7 on sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
		INNER JOIN sys_specific_definitions AS sd8 on sd8.main_group =17 AND sd8.first_group = a.address_type_id AND sd8.deleted = 0 AND sd8.active = 0 AND sd8.language_id = a.language_id 
		INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id               
                LEFT JOIN sys_countrys co on co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_code = a.language_code                               
		LEFT JOIN sys_city ct on ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_code = a.language_code                               
		LEFT JOIN sys_borough bo on bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_code = a.language_code  
                WHERE a.deleted =0                             
                " . $whereSql . "
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
     * @ Gridi doldurmak için info_users_addresses tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  02.02.2016
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
            $whereSql = " WHERE a.language_id = ".  intval($languageIdValue);             
        
            $sql = "
                    SELECT 
                        COUNT(a.id) AS COUNT		  
                    FROM info_users_addresses  a
                    INNER JOIN info_users_detail b on b.root_id = a.user_id and b.active = 0 and b.deleted = 0  
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                                
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                    INNER JOIN info_users u ON u.id = a.op_user_id 
                    INNER JOIN sys_specific_definitions as sd7 on sd7.main_group =14 AND sd7.first_group = a.consultant_confirm_type_id AND sd7.deleted = 0 AND sd7.active = 0 AND sd7.language_id = a.language_id 
                    INNER JOIN sys_operation_types op on op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id               
                    INNER JOIN sys_specific_definitions as sd8 on sd8.main_group =17 AND sd8.first_group = a.address_type_id AND sd8.deleted = 0 AND sd8.active = 0 AND sd8.language_id = a.language_id                
                    " . $whereSql . "
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
     * @ Gridi doldurmak için info_users_addresses tablosundan kayıtları döndürür !!
     * @version v 1.0  02.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingular($args = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $args['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
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
                    b.root_id AS user_id,
		    b.name AS name,
		    b.surname AS surname,
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
                    a.language_parent_id,
                    a.op_user_id,
                    u.username AS op_username  ,
                    b.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    a.profile_public,
                    COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    COALESCE(NULLIF(sd14x.description , ''), sd14.description_eng) AS consultant_confirm_type,   
                    a.confirm_id,
                    a.address_type_id,
                    COALESCE(NULLIF(sd17x.description , ''), sd17.description_eng) AS address_type,  
                    a.address1, 
                    a.address2, 
                    a.postal_code, 
                    a.country_id,
		    COALESCE(NULLIF(cox.name , ''), co.name_eng) AS country_name,
		    a.city_id,
		    COALESCE(NULLIF(ctx.name , ''), ct.name_eng) AS city_name,
		    a.borough_id,
		    COALESCE(NULLIF(box.name , ''), bo.name_eng) AS borough_name,
                    a.city_name, 
                    a.description, 
                    a.description_eng 
                FROM info_users_addresses a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                INNER JOIN info_users_detail b ON b.root_id = a.user_id AND b.active = 0 AND b.deleted = 0 and b.language_id = l.id 
                LEFT JOIN info_users_addresses ax ON (ax.id= a.id OR ax.id= ax.language_parent_id) AND ax.deleted = 0 and ax.language_id = l.id 

                INNER JOIN sys_specific_definitions AS sd14 ON sd14.main_group =14 AND sd14.first_group = a.consultant_confirm_type_id AND sd14.deleted = 0 AND sd14.active = 0 AND sd14.language_id = l.id 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
		INNER JOIN sys_specific_definitions AS sd17 ON sd17.main_group =17 AND sd17.first_group = a.address_type_id AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = l.id 
		INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		
		INNER JOIN info_users u ON u.id = a.op_user_id 		
		INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0

                LEFT JOIN sys_countrys co on co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = l.id
		LEFT JOIN sys_city ct on ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = l.id
		LEFT JOIN sys_borough bo on bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = l.id 

		LEFT JOIN sys_specific_definitions AS sd14x ON (sd14x.id= sd14.id OR sd14x.id= sd14.language_parent_id) AND sd14.deleted = 0 AND sd14.active = 0 AND sd14.language_id = lx.id 
		LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15.language_id = lx.id AND sd15.deleted = 0 AND sd15.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16.language_id = lx.id AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_specific_definitions AS sd17x ON (sd17x.id= sd17.id OR sd17x.id= sd17.language_parent_id) AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = lx.id 
		LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0

		LEFT JOIN sys_countrys cox on (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
	        LEFT JOIN sys_city ctx on (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
	        LEFT JOIN sys_borough box on (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 
		
                WHERE 
                    a.deleted =0 AND 
                    a.active =0  AND 
                    a.language_parent_id = 0 AND 
                    a.user_id =  ".intval($userIdValue)."
                ORDER BY address_type
                ";
                $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $args);
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
     * @author Okan CIRAN
     * @ Gridi doldurmak için info_users_addresses tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  02.02.2016
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
                $sql = "                              
                    SELECT 
                        COUNT(a.id) AS COUNT                        		  
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                 
                    INNER JOIN info_users_detail b ON b.root_id = a.user_id AND b.active = 0 AND b.deleted = 0 and b.language_id = l.id                
                    INNER JOIN sys_specific_definitions AS sd14 ON sd14.main_group =14 AND sd14.first_group = a.consultant_confirm_type_id AND sd14.deleted = 0 AND sd14.active = 0 AND sd14.language_id = l.id 
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN sys_specific_definitions AS sd17 ON sd17.main_group =17 AND sd17.first_group = a.address_type_id AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = l.id 
                    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0		
                    INNER JOIN info_users u ON u.id = a.op_user_id 		
                    INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id		
                    WHERE 
                        a.deleted =0 AND 
                        a.active =0  AND 
                        a.language_parent_id = 0 AND
                        a.user_id = " . intval($userIdValue) ." 
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }


    /**
     * @author Okan CIRAN
     * @ listbox ya da combobox doldurmak için info_users_addresses tablosundan user_id nin adres tiplerini döndürür !!
     * @version v 1.0  02.02.2016     
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUserAddressesTypes($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];                
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
                    a.id ,	
                    COALESCE(NULLIF(sd17x.description , ''), sd17.description_eng) AS name,   
                    sd17.description_eng AS name_eng    
                FROM info_users_addresses a     
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0   
                INNER JOIN sys_specific_definitions AS sd17 ON sd17.main_group =17 AND sd17.first_group = a.address_type_id AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = l.id 
		LEFT JOIN sys_specific_definitions AS sd17x ON (sd17x.id= sd17.id OR sd17x.id= sd17.language_parent_id) AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = lx.id                 
                WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND                   
                    a.user_id = ". intval($userIdValue)."
                ORDER BY name                
                                 ");                
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'pk'; 
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {        
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ info_users_addresses tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  02.02.2016
     * @todo Su an için aktif değil SQl in değişmesi lazım. 
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare(" 
                
                INSERT INTO info_users_addresses(
                    name, name_eng, language_id, ordr, language_parent_id, 
                    description, description_eng, user_id, language_code)
                   
                SELECT    
                    name, name_eng, language_id, ordr, language_parent_id, 
                    description, description_eng, user_id, language_main_code
                FROM ( 
                       SELECT 
                            '' AS name,                             
                            COALESCE(NULLIF(c.name_eng, ''), c.name) AS name_eng, 
                            l.id as language_id, 
                            c.ordr,
                            c.id AS language_parent_id,    
                            '' AS description,
                            description_eng,
                            c.user_id, 		 
                            l.language_main_code
                        FROM info_users_addresses c
                        LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                        WHERE c.id =  " . intval($params['id']) . "
                        ) AS xy   
                        WHERE xy.language_main_code NOT IN 
                            (SELECT DISTINCT language_code 
                            FROM info_users_addresses cx 
                            WHERE 
                                (cx.language_parent_id = " . intval($params['id']) . "  OR
                                cx.id = " . intval($params['id']) . " ) AND
                                cx.deleted =0 AND 
                                cx.active =0)) 
                    ");

            //$statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);   
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
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
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
     * @todo Su an için aktif değil SQl in değişmesi lazım. 
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
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name, 
                    a.name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                    a.ordr as siralama,
                    a.language_parent_id,
                    a.description,
                    a.description_eng,                  
                    a.user_id,
                    u.username    
                FROM info_users_addresses  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id 
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
     * @ info_users_addresses tablosundan parametre olarak  gelen id kaydın active alanını 1 yapar ve 
     * yeni yeni kayıt oluşturarak deleted ve active = 1 olarak  yeni kayıt yapar. ! 
     * @version v 1.0  02.02.2016
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
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 38, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                $this->makePassive(array('id' => $params['id']));
                
                $statementInsert = $pdo->prepare(" 
                    INSERT INTO info_users_addresses (
                        user_id,
                        active, 
                        deleted,
                        op_user_id, 
                        operation_type_id,
                        language_id,
                        address_type_id, 
                        address1, 
                        address2, 
                        postal_code, 
                        country_id, 
                        city_id, 
                        borough_id, 
                        city_name, 
                        description, 
                        description_eng,
                        profile_public,
                        consultant_id,
                        consultant_confirm_type_id, 
                        confirm_id,
                        language_parent_id,
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        act_parent_id
                        )                
                    SELECT
                        user_id,
                        1 AS active,  
                        1 AS deleted, 
                        " . intval($opUserIdValue) . " AS op_user_id,  
                        " . intval($operationIdValue) . ",
                        language_id,
                        address_type_id, 
                        address1, 
                        address2, 
                        postal_code, 
                        country_id, 
                        city_id, 
                        borough_id, 
                        city_name, 
                        description, 
                        description_eng,
                        profile_public,
                        consultant_id, 
                        consultant_confirm_type_id, 
                        confirm_id,
                        language_parent_id,
                        consultant_id,
                        consultant_confirm_type_id,
                        confirm_id,
                        act_parent_id
                    FROM info_users_addresses 
                    WHERE id  =" . intval($params['id']) . "    
                     ");
                $insertAct = $statementInsert->execute();
                $affectedRows = $statementInsert->rowCount();
                $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
                $errorInfo = $statementInsert->errorInfo();
                /*
                 * ufak bir trik var. 
                 * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                 * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                 * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                 */
                $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                array('table_name' => 'info_users_addresses', 'id' => $params['id'],));
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
     * @ info_users_addresses tablosuna pktemp için yeni bir kayıt oluşturur.  !!
     * @version v 1.0  03.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insertTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();                
            $opUserId = InfoUsers::getUserIdTemp(array('pktemp' => $params['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id']; 
                
                $operationIdValue = -1;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 38, 'type_id' => 1,));
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
                $getConsultant = SysOsbConsultants::getConsultantIdForTableName(array('table_name' => 'info_users_addresses' , 
                                                                                        'operation_type_id' => $operationIdValue, 
                                                                                        'language_id' => $languageIdValue,  
                                                                                               ));
                 if (\Utill\Dal\Helper::haveRecord($getConsultant)) {
                     $ConsultantId = $getConsultant ['resultSet'][0]['consultant_id'];
                 } 
              
                $sql = "                
                        INSERT INTO info_users_addresses (   
                                op_user_id,
                                user_id, 
                                consultant_id,
                                language_id,
                                operation_type_id,
                                address_type_id, 
                                address1, 
                                address2, 
                                postal_code, 
                                country_id, 
                                city_id, 
                                borough_id, 
                                city_name, 
                                description,                                 
                                profile_public,
                                act_parent_id
                                )                        
                        VALUES (                                
                                ".intval($opUserIdValue).",
                                ".intval($opUserIdValue).",
                                ".intval($ConsultantId).",
                                ".intval($languageIdValue).",
                                ".intval($operationIdValue).",                                    
                                :address_type_id, 
                                :address1, 
                                :address2, 
                                :postal_code, 
                                :country_id, 
                                :city_id, 
                                :borough_id, 
                                :city_name, 
                                :description,                                 
                                :profile_public,
                                (SELECT last_value FROM info_users_addresses_id_seq)
                                           )     ";                
                $statement = $pdo->prepare($sql);                
                $statement->bindValue(':address_type_id', $params['address_type_id'], \PDO::PARAM_INT);
                $statement->bindValue(':address1', $params['address1'], \PDO::PARAM_STR);
                $statement->bindValue(':address2', $params['address2'], \PDO::PARAM_STR);
                $statement->bindValue(':postal_code', $params['postal_code'], \PDO::PARAM_STR);
                $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
                $statement->bindValue(':city_id', $params['city_id'], \PDO::PARAM_INT);
                $statement->bindValue(':borough_id', $params['borough_id'], \PDO::PARAM_INT);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $statement->bindValue(':city_name', $params['city_name'], \PDO::PARAM_STR);
                $statement->bindValue(':profile_public', $params['profile_public'], \PDO::PARAM_INT);
              // echo debugPDO($sql, $params);              
                $result = $statement->execute();   
                $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
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
                $errorInfoColumn = 'pktemp';
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
     * info_users_addresses tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  03.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updateTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = InfoUsers::getUserIdTemp(array('pktemp' => $params['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                
                $this->makePassive(array('id' => $params['id']));
                               
                $operationIdValue = -2;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 38, 'type_id' => 2,));
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
                $profilePublic =0 ;
                if ((isset($params['profile_public']) && $params['profile_public'] != "")) {
                        $profilePublic = $params['profile_public'];                   
                }  
                
                $statementInsert = $pdo->prepare("
                INSERT INTO info_users_addresses (                                          
                        active, 
                        op_user_id,                        
                        language_id,
                        address_type_id, 
                        address1, 
                        address2, 
                        postal_code, 
                        country_id, 
                        city_id, 
                        borough_id, 
                        city_name, 
                        description,                                                           
                        profile_public, 
                        act_parent_id, 
                        language_parent_id,                     
                        user_id,
                        operation_type_id    
                        )  
                SELECT                 
                    0 AS active,   
                    " . intval($opUserIdValue) . " AS op_user_id,                    
                    " . intval($languageIdValue) . " AS language_id,   
                    " . intval($params['address_type_id']) . " AS address_type_id,    
                    '" . $params['address1'] . "' AS address1,
                    '" . $params['address2'] . "' AS address2,
                    '" . $params['postal_code'] . "' AS postal_code,
                    " . intval($params['country_id']) . " AS country_id,   
                    " . intval($params['city_id']) . " AS city_id, 
                    " . intval($params['borough_id']) . " AS borough_id, 
                    '" . $params['city_name'] . "' AS city_name, 
                    '" . $params['description'] . "' AS description,                    
                    " . intval($profilePublic) . " AS profile_public, 
                    act_parent_id, 
                    language_parent_id,
                    user_id,
                    " . intval($operationIdValue) . "                    
                FROM info_users_addresses 
                WHERE id  =" . intval($params['id']) . "                  
                               ");

                $result = $statementInsert->execute();
                $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
                $affectedRows = $statementInsert->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);

                /*
                   * ufak bir trik var. 
                   * işlem update oldugunda update işlemini yapan kişinin dil bilgisini kullanıcaz. 
                   * ancak delete işlemi oldugunda delete işlemini yapan user in dil bilgisini değil 
                   * silinen kaydı yapan kişinin dil bilgisini alıcaz.
                   */
                  $consIdAndLanguageId = SysOperationTypes::getConsIdAndLanguageId(
                                  array('table_name' => 'info_users_addresses', 'id' => $params['id'],));
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
                $errorInfoColumn = 'pktemp';
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
     * @ Gridi doldurmak için info_users_addresses tablosundan kayıtları döndürür !!
     * @version v 1.0  02.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularTemp($args = array()) {
        try {         
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserIdTemp(array('pktemp' => $args['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];                
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
                    b.root_id AS user_id,
		    b.name AS name,
		    b.surname AS surname,
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
                    a.language_parent_id,
                    a.op_user_id,
                    u.username AS op_username  ,
                    b.operation_type_id,
                    COALESCE(NULLIF(opx.operation_name, ''), op.operation_name_eng) AS operation_name,
                    a.profile_public,
                    COALESCE(NULLIF(sd19x.description, ''), sd19.description_eng) AS state_profile_public,
                    a.s_date,
                    a.c_date,
                    a.consultant_id,
                    a.consultant_confirm_type_id,
		    COALESCE(NULLIF(sd14x.description , ''), sd14.description_eng) AS consultant_confirm_type,   
                    a.confirm_id,
                    a.address_type_id,
                    COALESCE(NULLIF(sd17x.description , ''), sd17.description_eng) AS address_type,  
                    a.address1, 
                    a.address2, 
                    a.postal_code, 
                    a.country_id,
		    COALESCE(NULLIF(cox.name , ''), co.name_eng) AS country_name,
		    a.city_id,
		    COALESCE(NULLIF(ctx.name , ''), ct.name_eng) AS city_name,
		    a.borough_id,
		    COALESCE(NULLIF(box.name , ''), bo.name_eng) AS borough_name,
                    a.city_name, 
                    a.description, 
                    a.description_eng 
                FROM info_users_addresses a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                INNER JOIN info_users_detail b ON b.root_id = a.user_id AND b.active = 0 AND b.deleted = 0 and b.language_id = l.id 
                LEFT JOIN info_users_addresses ax ON (ax.id= a.id OR ax.id= ax.language_parent_id) AND ax.deleted = 0 and ax.language_id = l.id 

                INNER JOIN sys_specific_definitions AS sd14 ON sd14.main_group =14 AND sd14.first_group = a.consultant_confirm_type_id AND sd14.deleted = 0 AND sd14.active = 0 AND sd14.language_id = l.id 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
		INNER JOIN sys_specific_definitions AS sd17 ON sd17.main_group =17 AND sd17.first_group = a.address_type_id AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = l.id 
		INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
		
		INNER JOIN info_users u ON u.id = a.op_user_id 		
		INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id
		LEFT JOIN sys_operation_types opx ON (opx.id = op.id OR opx.language_parent_id = op.id) and opx.language_id =lx.id  AND opx.deleted =0 AND opx.active =0

                LEFT JOIN sys_countrys co on co.id = a.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = l.id
		LEFT JOIN sys_city ct on ct.id = a.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = l.id
		LEFT JOIN sys_borough bo on bo.id = a.borough_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = l.id 

		LEFT JOIN sys_specific_definitions AS sd14x ON (sd14x.id= sd14.id OR sd14x.id= sd14.language_parent_id) AND sd14.deleted = 0 AND sd14.active = 0 AND sd14.language_id = lx.id 
		LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15.language_id = lx.id AND sd15.deleted = 0 AND sd15.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16.language_id = lx.id AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_specific_definitions AS sd17x ON (sd17x.id= sd17.id OR sd17x.id= sd17.language_parent_id) AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = lx.id 
		LEFT JOIN sys_specific_definitions sd19x ON sd19x.language_id = lx.id AND (sd19x.id = sd19.id OR sd19x.language_parent_id = sd19.id) AND sd19x.deleted = 0 AND sd19x.active = 0

		LEFT JOIN sys_countrys cox on (cox.id = co.id OR cox.language_parent_id = co.id) AND cox.deleted = 0 AND cox.active = 0 AND cox.language_id = lx.id
	        LEFT JOIN sys_city ctx on (ctx.id = ct.id OR ctx.language_parent_id = ct.id) AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_id = lx.id
	        LEFT JOIN sys_borough box on (box.id = bo.id OR box.language_parent_id = bo.id) AND box.deleted = 0 AND box.active = 0 AND box.language_id = lx.id 		
                WHERE 
                    a.deleted =0 AND 
                    a.active =0  AND 
                    a.language_parent_id = 0 AND 
                    AND a.user_id =  ".intval($userIdValue)."
                ORDER BY address_type
                ";
                 
                $statement = $pdo->prepare($sql);
               //  echo debugPDO($sql, $args);                 
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
           
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pktemp';
                $pdo->rollback();
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
     * @ Gridi doldurmak için info_users_addresses tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  02.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridSingularRowTotalCountTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserIdTemp(array('pktemp' => $params['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {                
                $userIdValue = $userId ['resultSet'][0]['user_id'];
               
                $sql = "                              
                    SELECT 
                        COUNT(a.id) AS COUNT   		  
                    FROM info_users_addresses  a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                     
                    INNER JOIN info_users_detail b ON b.root_id = a.user_id AND b.active = 0 AND b.deleted = 0 and b.language_id = l.id 
                    INNER JOIN sys_specific_definitions AS sd14 ON sd14.main_group =14 AND sd14.first_group = a.consultant_confirm_type_id AND sd14.deleted = 0 AND sd14.active = 0 AND sd14.language_id = l.id 
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN sys_specific_definitions AS sd17 ON sd17.main_group =17 AND sd17.first_group = a.address_type_id AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = l.id 
                    INNER JOIN sys_specific_definitions sd19 ON sd19.main_group = 19 AND sd19.first_group= a.profile_public AND sd19.deleted = 0 AND sd19.active = 0 AND sd19.language_parent_id =0
                    INNER JOIN info_users u ON u.id = a.op_user_id 		
                    INNER JOIN sys_operation_types op ON op.id = b.operation_type_id AND op.deleted = 0 AND op.active = 0 AND op.language_id = a.language_id                    
                    WHERE 
                        a.deleted =0 AND 
                        a.active =0  AND 
                        a.language_parent_id = 0 AND 
                        a.user_id =  ".intval($userIdValue)."
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @author Okan CIRAN
     * @ listbox ya da combobox doldurmak için info_users_addresses tablosundan user_id nin adres tiplerini döndürür !!
     * @version v 1.0  02.02.2016     
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUserAddressesTypesTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $userId = InfoUsers::getUserIdTemp(array('pktemp' => $params['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];                
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
                    a.id ,	
                    COALESCE(NULLIF(sd17x.description , ''), sd17.description_eng) AS name,   
                    sd17.description_eng AS name_eng    
                FROM info_users_addresses a     
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0   
                INNER JOIN sys_specific_definitions AS sd17 ON sd17.main_group =17 AND sd17.first_group = a.address_type_id AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = l.id 
		LEFT JOIN sys_specific_definitions AS sd17x ON (sd17x.id= sd17.id OR sd17x.id= sd17.language_parent_id) AND sd17.deleted = 0 AND sd17.active = 0 AND sd17.language_id = lx.id                 
                WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND                   
                    a.user_id = ". intval($userIdValue)."
                ORDER BY name                       
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
                $errorInfo = '23505';   // 23505  unique_violation
                $errorInfoColumn = 'pk';       
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {        
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN     
     * @ info_users_addresses tablosundan parametre olarak  gelen id kaydın active alanını 1 yapar ve 
     * yeni yeni kayıt oluşturarak deleted ve active = 1 olarak  yeni kayıt yapar. ! 
     * @version v 1.0  02.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function deletedActTemp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = InfoUsers::getUserIdTemp(array('pktemp' => $params['pktemp']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
  
                $operationIdValue = -3;
                $operationId = SysOperationTypes::getTypeIdToGoOperationId(
                                array('parent_id' => 3, 'main_group' => 3, 'sub_grup_id' => 38, 'type_id' => 3,));
                if (\Utill\Dal\Helper::haveRecord($operationId)) {
                    $operationIdValue = $operationId ['resultSet'][0]['id'];
                }
                
                $this->makePassive(array('id' => $params['id']));
               
                $statementInsert = $pdo->prepare(" 
                    INSERT INTO info_users_addresses (
                        user_id,                        
                        active, 
                        deleted,
                        op_user_id,                        
                        operation_type_id,
                        act_parent_id,  
                        language_id,
                        address_type_id, 
                        address1, 
                        address2, 
                        postal_code, 
                        country_id, 
                        city_id, 
                        borough_id, 
                        city_name, 
                        description, 
                        description_eng,
                        profile_public,
                        consultant_id,                        
                        language_parent_id,                        
                        consultant_id, 
                        act_parent_id
                        )                            
                    SELECT
                        user_id,
                        1 AS active,  
                        1 AS deleted, 
                        " . intval($opUserIdValue) . " AS op_user_id,                    
                        ". intval($operationIdValue) . ",
                        act_parent_id, 
                        language_id,
                        address_type_id, 
                        address1, 
                        address2, 
                        postal_code, 
                        country_id, 
                        city_id, 
                        borough_id, 
                        city_name, 
                        description, 
                        description_eng,
                        profile_public,
                        confirm_id,                        
                        language_parent_id ,                        
                        consultant_id,                        
                        act_parent_id
                    FROM info_users_addresses 
                    WHERE id  =" . intval($params['id']) . "    
                    )");

                $insertAct = $statementInsert->execute();
                $affectedRows = $statementInsert->rowCount();
                $insertID = $pdo->lastInsertId('info_users_addresses_id_seq');
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
                                array('table_name' => 'info_users_addresses', 'id' => $params['id'],));
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
                $errorInfoColumn = 'pk / op_user_id';
                 $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    
    
    
    
    
    
    

}
