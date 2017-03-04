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
 */
class SysOperationTypes extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_operation_types tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  10.02.2016
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
                UPDATE sys_operation_types
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
     * @ sys_operation_types tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  10.02.2016    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT 
                    a.id, 
                    COALESCE(NULLIF(a.operation_name, ''), a.operation_name_eng) AS name, 
                    a.operation_name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
                    a.language_id,
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id,                     
                    a.op_user_id,
                    u.username ,
                    a.base_id
                FROM sys_operation_types  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id                         
                ORDER BY a.operation_name                
                                 ");
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
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
     * @ sys_operation_types tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  10.02.2016
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
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                $statement = $pdo->prepare("
                INSERT INTO sys_operation_types(
                         parent_id, 
                         operation_name, 
                         operation_name_eng, 
                         language_id, 
                         op_user_id, 
                         language_parent_id, 
                         language_code,
                         base_id)
                VALUES (
                        :parent_id,
                        :operation_name, 
                        :operation_name_eng,
                        :language_id,
                        :op_user_id,
                        :language_parent_id,                       
                        :language_code ,
                        :base_id                        
                                                ");
                $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':operation_name', $params['operation_name'], \PDO::PARAM_STR);
                $statement->bindValue(':operation_name_eng', $params['operation_name_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':base_id', $params['base_id'], \PDO::PARAM_INT);
                $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_operation_types_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
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
     * sys_operation_types tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  10.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (!\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }
                $statement = $pdo->prepare("
                UPDATE sys_operation_types
                SET 
                     parent_id = :parent_id,
                     operation_name = :operation_name, 
                     operation_name_eng = :operation_name_eng,
                     language_id :language_id,
                     op_user_id = :op_user_id,
                     language_parent_id = :language_parent_id,                       
                     language_code = :language_code  
                WHERE base_id = :id");
                $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
                $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':operation_name', $params['operation_name'], \PDO::PARAM_STR);
                $statement->bindValue(':operation_name_eng', $params['operation_name_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
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
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_operation_types tablosundan kayıtları döndürür !!
     * @version v 1.0  10.02.2016
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
        if (isset($params['sort']) && $params['sort'] != "") {
            $sort = trim($params['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($params['sort']);
        } else {
            $sort = "a.operation_name ";
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

        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
        if (\Utill\Dal\Helper::haveRecord($languageId)) {
            $languageIdValue = $languageId ['resultSet'][0]['id'];
        } else {
            $languageIdValue = 647;
        }
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                    a.id, 
                    COALESCE(NULLIF(a.operation_name, ''), a.operation_name_eng) AS name, 
                    a.operation_name_eng, 
                    a.deleted, 
		    sd.description AS state_deleted,                 
                    a.active, 
		    sd1.description AS state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id,                     
                    a.op_user_id,
                    u.username,
                    a.base_id
                FROM sys_operation_types  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id   
                WHERE a.language_id = :language_id
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
            $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
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
     * @ Gridi doldurmak için sys_operation_types tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  10.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT ,    
                FROM sys_operation_types  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id   
                WHERE a.language_id = " . intval($languageIdValue) . "
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
     * @ danısmanların firma bilgisi için operasyonlarının listesi
     * @version v 1.0  10.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsultantOperations($params = array()) {
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
            $addSql = " WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND 
                    a.parent_id = 2 AND
                    a.language_parent_id = 0
                    ";
            
            if (isset($params['main_group']) && $params['main_group'] != "") {
                $addSql .= " AND a.main_group = " . intval($params['main_group'])  ;
            } else {
                $addSql .= " AND a.main_group in (1,2)   ";
            }
 
            $sql = "
              SELECT                    
                    a.base_id AS id, 	
                    COALESCE(NULLIF(sd.operation_name, ''), a.operation_name_eng) AS name,
                    a.operation_name_eng AS name_eng
                FROM sys_operation_types a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" . intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                
		LEFT JOIN sys_operation_types sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id                
                " . $addSql . "                
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
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**    
     * @author Okan CIRAN
     * @ sys_operation_types tablosundan type_id ye karsılık gelen base_id yi döndürür   !!
     * @ parent_id => 3 = kayıtlı kullanıcı, 4 = supervisor, 
        * @ 6 = Danışman Kayıt işlemleri,  7= Danışman Onay işlemleri   gösterir.  
        * @ danısman onay işlemleri ile ilgili operasyonların çalışmaları devam ediyor.
     * @ type _id => 1 = insert, 2 = update, 3 = delete kayıt işlemleri operasyonu oldugunu gösterir.       
     * @version v 1.0  08.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getTypeIdToGoOperationId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
              SELECT                    
                    a.base_id AS id, 
                    1=1 as control
                FROM sys_operation_types a                
                WHERE 
		    a.language_parent_id = 0 AND 
                    a.active = 0 AND 
                    a.deleted = 0 AND 
                    a.parent_id = ". intval($params['parent_id'])." AND 
                    a.main_group = ". intval($params['main_group'])." AND 
                    a.sub_grup_id = ". intval($params['sub_grup_id'])." AND 
                    a.type_id =". intval($params['type_id'])." 
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
    * @ sys_operation_types tablosundan type_id ye karsılık gelen base_id yi döndürür   !!
    * @ parent_id => 
    * @ 6 = Danışman Kayıt işlemleri,  7= Danışman Onay işlemleri gösterir.
    * @ main_group_id =>  Not istersek ayırabiliriz. su  an  için gerek yok 
    * @ 6 = Danışman Kayıt işlemleri,  7= Danışman Onay işlemleri gösterir.
     * @ type_id => danısmanın sectiği operasyon tipinin type_id sidir. tablo bazlı değişebilir. 
    * danısmanın karsısına o tabloda  hangi  operasyonlar olabilir bunun listesini döndürücez. 
    * sectiği operasyonun type_id si ni parametre olarak  göndericez. buradan da danısmanın 
    * yaptıgı operasyonun base_idsine ulasıcaz. 
    * @version v 1.0  20.07.2016
    * @param array | null $args
    * @return array
    * @throws \PDOException
    */
    public function getConsTypeIdToGoOperationId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
               SELECT                    
                    a.base_id AS id, 
                    1=1 as control
                FROM sys_operation_types a                
                WHERE 
		    a.language_parent_id = 0 AND 
                    a.active = 0 AND 
                    a.deleted = 0 AND 
                    a.table_name = '". $params['table_name'] ."' AND 
                    a.parent_id IN (6,7) AND 
                    a.main_group IN (6,7) AND                  
                    a.type_id = ". intval($params['type_id'])." 
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
     * @ operasyona atanmış danısman ve opareasyonu yapan kişinin dil bilgisi döndürür.
     * @version v 1.0 21.07.2016
     * @return array
     * @throws \PDOException
     */
    public function getConsIdAndLanguageId($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $Id = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $Id = intval($params['id']);
            } 
            
            $getOperationTableNameValue = 'info_firm_profile';
            $getOperationTableName = PgClass::getOperationTableName(
                                  array('operation_type_id' => $params['operation_type_id'],));
            if (\Utill\Dal\Helper::haveRecord($getOperationTableName)) {
                  $getOperationTableNameValue = $getOperationTableName ['resultSet'][0]['table_name'];
            } else  { $Id = -1001; } 
            
            $sql = "  
                SELECT 
                    language_id,
                    consultant_id,   
                    assign_definition_id , 
                    control 
                FROM (
                        SELECT 
                            iu.language_id,
                            a.consultant_id,
                            sotr.assign_definition_id , 
                            a.id  = " . intval($Id) . " AS control
                        FROM ".$getOperationTableNameValue." a
                        INNER JOIN info_users iu ON iu.id = a.op_user_id 
                        INNER JOIN info_users iuc ON iuc.id = a.consultant_id 
                        INNER JOIN sys_operation_types_rrp sotr ON sotr.id = a.operation_type_id
                        WHERE 
                            a.id =  " . intval($Id) . "
                    UNION 
                        SELECT 
                        647 AS language_id,
                        1001 AS consultant_id,
                        -1 AS assign_definition_id, 
                        true AS control
                )  as xxtable 
                ORDER BY assign_definition_id DESC 
                limit 1 
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
 
}
