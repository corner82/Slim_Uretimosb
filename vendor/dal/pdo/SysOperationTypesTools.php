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
 * created to be used by DAL MAnager for operation type tools operations
 * @author Mustafa Zeynel Dağlı
 * @since 11/02/2016
 */
class SysOperationTypesTools extends \DAL\DalSlim {

    /**
     * sys_operation_types_tools tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  11.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     */
    public function delete($params = array()) {
       try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE sys_operations_types_tools
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
     * @author Mustafa Zeynel DAĞLI
     * @ sys_operation_types tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  11.02.2016    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');

            $statement = $pdo->prepare("
                SELECT 
                    a.id, 
                    COALESCE(NULLIF(a.role_name, ''), a.role_name_eng) AS name, 
                    a.role_name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id,                     
                    a.op_user_id,
                    u.username ,
                    a.base_id
                FROM sys_operations_types_tools  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id                         
                ORDER BY a.role_name                  
                                 ");
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
     * @author Mustafa Zeynel DAĞLI
     * @ sys_operation_types tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  11.02.2016
     * @return array
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     * @since 11/02/2016
     */
    public function insert($params = array()) {
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
                INSERT INTO sys_operations_types_tools(
                         parent_id, 
                         role_name, 
                         role_name_eng, 
                         language_id, 
                         op_user_id, 
                         language_parent_id, 
                         language_code,
                         base_id)
                VALUES (
                        :parent_id,
                        :role_name, 
                        :role_name_eng,
                        :language_id,
                        :op_user_id,
                        :language_parent_id,                       
                        :language_code ,
                        :base_id                        
                                                ");
                $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':role_name', $params['operation_name'], \PDO::PARAM_STR);
                $statement->bindValue(':role_name_eng', $params['operation_name_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':base_id', $params['base_id'], \PDO::PARAM_INT);
                $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_operations_types_tools_id_seq');
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
     * sys_operation_types_tools tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller!!
     * @param type $params
     * @return array
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     * @since 11/02/2016
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
                UPDATE sys_operation_types_tools
                SET 
                     parent_id = :parent_id,
                     role_name = :role_name, 
                     role_name_eng = :role_name_eng,
                     language_id :language_id,
                     op_user_id = :op_user_id,
                     language_parent_id = :language_parent_id,                       
                     language_code = :language_code  
                WHERE base_id = :id");
                $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
                $statement->bindValue(':parent_id', $params['parent_id'], \PDO::PARAM_INT);
                $statement->bindValue(':role_name', $params['operation_name'], \PDO::PARAM_STR);
                $statement->bindValue(':role_name_eng', $params['operation_name_eng'], \PDO::PARAM_STR);
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
     * Gridi doldurmak için sys_operation_types_tools tablosundan kayıtları döndürür !!
     * @param array | null $args
     * @return array
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     * @since 11/02/2016
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
            $sort = "a.role_name";
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
                    COALESCE(NULLIF(a.role_name, ''), a.role_name_eng) AS name, 
                    a.role_name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                      
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id,                     
                    a.op_user_id,
                    u.username ,
                    a.base_id
                FROM sys_operations_types_tools  a
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
     * Gridi doldurmak için sys_operation_types_tools tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @param array | null $args
     * @return array
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     * @since 11/02/2016
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageIdValue = 647;
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT ,    
                FROM sys_operations_types_tools  a
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
     * returns operation type tools
     * @param array $params
     * @return array
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     * @since 11/02/2016
     */
    public function fillConsultantOperationsTools($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $addSql = " WHERE 
                    a.active =0 AND 
                    a.deleted = 0 AND 
                    a.language_parent_id = 0 AND
                    a.parent_id = 0 ";

            if (isset($params['main_group']) && $params['main_group'] != "") {
                $addSql .= " AND a.main_group = " . intval($params['main_group'])  ;
            } else {
                //$whereSql = "  a.main_group in (1,2) AND  " ; 
                $addSql .= " ";
            }

            $sql = "
                SELECT                    
                    a.base_id AS id, 	
                    COALESCE(NULLIF(sd.role_name, ''), a.role_name_eng) AS name,
                    a.role_name_eng AS name_eng                                 
                FROM sys_operations_types_tools a                     
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                
		LEFT JOIN sys_operations_types_tools sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id                         
                " . $addSql . "                    
                ORDER BY name                   
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
     * returns operation type tools
     * @param array $params
     * @return array
     * @throws \PDOException
     * @author Okan CIRAN
     * @since 11/02/2016
     */
    public function fillConsultantOperationsConfirmTools($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $addSql = "WHERE 
                    a.active =0 AND a.deleted = 0 AND 
                    a.language_parent_id=0 AND
                    a.parent_id = 0   ";
           
            if (isset($params['main_group']) && $params['main_group'] != "") {
                $addSql .= "  AND a.main_group = " . intval($params['main_group']);
            } else {
                //$addSql .= "  a.main_group in (1,2) AND  " ; 
                $addSql .= " ";
            }

            $sql = "
                SELECT                    
                    a.base_id AS id, 	
                    COALESCE(NULLIF(sd.role_name, ''), a.role_name_eng) AS name,
                    a.role_name_eng AS name_eng                                 
                FROM sys_operations_types_tools a  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                
		LEFT JOIN sys_operations_types_tools sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id                  
                " . $addSql . "                    
                ORDER BY name                
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
