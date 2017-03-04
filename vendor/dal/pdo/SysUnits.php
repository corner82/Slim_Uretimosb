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
 * @since 17.02.2016
 */
class SysUnits extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_units tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  17.02.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();

            $Unit = $this->haveUnitRecords(array('id' => $params['id']));
            if (!\Utill\Dal\Helper::haveRecord($Unit)) {
                $opUserIdParams = array('pk' =>  $params['pk'],);
                $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
                $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $sql = " 
                UPDATE sys_units
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . intval($opUserIdValue) . "      
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                     // echo debugPDO($sql, $params);
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
            } else {
                $errorInfo = '23503';   // 23503  foreign_key_violation
                $errorInfoColumn = 'Unitcode';
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
     * @ sys_units tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  17.02.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("               
                SELECT 
                    a.id,                   
                    a.system_id,
                    COALESCE(NULLIF(sus.system, ''), sus.system_eng) AS system,  
                    a.system_eng,  
		    COALESCE(NULLIF(a.abbreviation, ''), a.abbreviation_eng) AS abbreviation,  
                    a.abbreviation_eng,  
		    COALESCE(NULLIF(a.unitcode, ''), a.unitcode_eng) AS unitcode,  
                    a.unitcode_eng,  
                    COALESCE(NULLIF(a.unit, ''), a.unit_eng) AS unit,  
                    a.unit_eng,                 
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                  
                INNER JOIN sys_unit_systems sus ON sus.id = system_id AND sus.active = 0 AND sus.deleted =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                ORDER BY  a.language_id , system , a.unitcode
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
     * @ sys_units tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  17.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
           
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                        $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
                    } 

                    $sql = "
                INSERT INTO sys_units(   
                        system_id,
                        unit,
                        unit_eng, 
                        unitcode, 
                        unitcode_eng, 
                        abbreviation, 
                        abbreviation_eng, 
                        language_id, 
                        parent_id, 
                        op_user_id  
                        )
                VALUES (       
                        ". intval( $params['system_id']).", 
                        :unit,
                        :unit_eng, 
                        :unitcode, 
                        :unitcode_eng, 
                        :abbreviation, 
                        :abbreviation_eng, 
                        ". intval($languageIdValue).", 
                        ". intval( $params['parent_id']).", 
                        ". intval($opUserIdValue)." 
                                             )   ";                    
                    $statement = $pdo->prepare($sql);                    
                    $statement->bindValue(':unit', $params['unit'], \PDO::PARAM_STR); 
                    $statement->bindValue(':unit_eng', $params['unit_eng'], \PDO::PARAM_STR); 
                    $statement->bindValue(':unitcode', $params['unitcode'], \PDO::PARAM_STR);
                    $statement->bindValue(':unitcode_eng', $params['unitcode_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation', $params['abbreviation'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation_eng', $params['abbreviation_eng'], \PDO::PARAM_STR);                    
                  // echo debugPDO($sql, $params);
                    $result = $statement->execute();                   
                    $insertID = $pdo->lastInsertId('sys_unit_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'group_name';
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
     * @ sys_units tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 15.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');           
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            } 
            
            $addSql = " a.system_id =  " . intval($params['system_id']) . " AND" ;
            if (isset($params['id'])) {
                $addSql .= " a.id != " . intval($params['id']) . " AND ";
            } else 
            {
             //   $addSql = "a.system_id =  " . intval($params['system_id']) . " AND" ;
            }
            
            
            if (isset($params['parent_id'])) {
                $addSql .= " a.parent_id= " . intval($params['parent_id']) . " AND ";
            } else 
            {
              $addSql .= " a.parent_id = 0 AND  " ;              
            }
            
            $sql = " 
            SELECT  
                 a.unitcode AS name , 
                 '" . $params['unitcode'] . "' AS value , 
                 1 =1 AS control,
                 CONCAT( a.unitcode, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_units  a                      
            WHERE a.unitcode =  '" . $params['unitcode'] . "' AND                
                  a.language_id = " . intval($languageIdValue). " AND 
		  " . $addSql . " 
		  a.deleted =0       
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
     * @ sys_units tablosunda parent id ye sahip alt elemanlar var mı   ?  
     * @version v 1.0 06.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveUnitRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            } 
            $sql = " 
           SELECT  
                a.unitcode AS name ,             
                a.parent_id  = " . $params['id'] . " 
                AS control,
                'Bu Grup Altında Unit Kaydı Bulunmakta. Lütfen Kontrol Ediniz !!!' AS message   
            FROM sys_units  a  
            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
            WHERE a.parent_id = ".$params['id']. "
                AND a.language_parent_id =0                  
                AND a.deleted =0    
            LIMIT 1                      
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
     * sys_units tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  17.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {                  
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                        $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
                    } 
                    $systemIdValue =0 ;
                    if ((isset($params['system_id']) && $params['system_id'] != "")) {
                            $systemIdValue = $params['system_id']; 
                    }  
                    $sql = "
                    UPDATE sys_units
                    SET                      
                        system_id  = :system_id, 
                        unit  = :unit,                     
                        unit_eng = :unit_eng,                     
                        unitcode  = :unitcode, 
                        unitcode_eng  = :unitcode_eng, 
                        abbreviation  = :abbreviation, 
                        abbreviation_eng = :abbreviation_eng, 
                        language_id = :language_id,                    
                        op_user_id = :op_user_id                    
                    WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':system_id',$systemIdValue, \PDO::PARAM_INT);                                                            
                    $statement->bindValue(':unit', $params['unit'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':unit_eng', $params['unit_eng'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':unitcode', $params['unitcode'], \PDO::PARAM_STR);
                    $statement->bindValue(':unitcode_eng', $params['unitcode_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation', $params['abbreviation'], \PDO::PARAM_STR);
                    $statement->bindValue(':abbreviation_eng', $params['abbreviation_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);                       
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505'; 
                    $errorInfoColumn = 'unitcode';
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
     * @ Gridi doldurmak için sys_units tablosundan kayıtları döndürür !!
     * @version v 1.0  17.02.2016
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
            $sort = "a.language_id , system , a.unitcode";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else { 
            $order = "ASC";
        }
        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
        } 
        $whereSql = " AND a.language_id = " . intval($languageIdValue);
 
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                  SELECT 
                    a.id,                   
                    a.system_id,
                    COALESCE(NULLIF(sus.system, ''), sus.system_eng) AS system,  
                    a.system_eng,  
		    COALESCE(NULLIF(a.abbreviation, ''), a.abbreviation_eng) AS abbreviation,  
                    a.abbreviation_eng,  
		    COALESCE(NULLIF(a.unitcode, ''), a.unitcode_eng) AS unitcode,  
                    a.unitcode_eng,  
                    COALESCE(NULLIF(a.unit, ''), a.unit_eng) AS unit,  
                    a.unit_eng,                 
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    a.language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                  
                INNER JOIN sys_unit_systems sus ON sus.id = system_id AND sus.active = 0 AND sus.deleted =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                 
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_units tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');    
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            } 
            $whereSql = " WHERE a.deleted =0 AND a.language_id = " . intval($languageIdValue);
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                  
                INNER JOIN sys_unit_systems sus ON sus.id = system_id AND sus.active = 0 AND sus.deleted =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id 
                " . $whereSql . "               
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
     * @ sys_units tablosundan unitleri döndürür   !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUnits($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            } 

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id =0 "; 
            
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $whereSql .= " AND a.parent_id = " . intval($params['parent_id']);
            } else {
                $whereSql .= " AND a.parent_id =0  ";
            }

            $sql = "
               SELECT 
                    a.id,                     
		    COALESCE(NULLIF(su.abbreviation, ''), a.abbreviation_eng) AS abbreviation,  
                    a.abbreviation_eng,  
		    COALESCE(NULLIF(su.unitcode, ''), a.unitcode_eng) AS unitcode,  
                    a.unitcode_eng,  
                    COALESCE(NULLIF(su.unit, ''), a.unit_eng) AS units,  
                    a.unit_eng 
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                  
		LEFT JOIN sys_language lx ON lx.id = ".$languageIdValue." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_units su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                
                " . $whereSql . "                       
                ORDER BY unitcode            
                                 ";
            $statement = $pdo->prepare($sql);            
        //    echo debugPDO($sql, $params);
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
     * @ tree ve grid doldurmak için sys_units tablosundan unitleri döndürür   !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUnitsTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            } 
            
            $whereSql = " WHERE a.deleted = 0 AND a.language_parent_id =0  " ; 
            $addSql = " WHERE ax.parent_id = a.id AND ax.deleted = 0 AND ax.language_parent_id = 0";
            
            if (isset($params['system_id']) && $params['system_id'] != "") {
                $whereSql .= " AND a.system_id = " . intval($params['system_id']);
                $addSql .= " AND ax.system_id = " . intval($params['system_id'])  ;             
            }
            
            if (isset($params['id']) && $params['id'] != "") {
                $whereSql .= " AND a.parent_id  = " . intval($params['id']) ;                
            } else {
                $whereSql .= "  AND a.parent_id = 0 ";
            }

            $sql = "
               SELECT 
                    a.id, 
                    a.active,                 
                    COALESCE(NULLIF(a.system_id, NULL), 0) AS system_id,
		    COALESCE(NULLIF(susx.system_eng, ''), sus.system) AS system,
		    sus.system_eng,		    
                    COALESCE(NULLIF(su.unitcode, ''), a.unitcode_eng) AS unitcode,
                    a.unitcode_eng,
                    COALESCE(NULLIF(su.unit, ''), a.unit_eng) AS unit,
                    a.unit_eng,
                    COALESCE(NULLIF(su.abbreviation, ''), a.abbreviation_eng) AS abbreviation,
                    a.abbreviation_eng,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_units ax  ". $addSql ." )    
                            WHEN 1 THEN 'closed'
                            ELSE 'open'   
                    END AS state_type ,
                    CASE 
                         a.parent_id    
                            WHEN 0 THEN false
                            ELSE true   
                    END AS notroot 
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0       
		LEFT JOIN sys_unit_systems sus ON sus.id = a.system_id AND sus.active = 0 AND sus.deleted =0 
                LEFT JOIN sys_units su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id 
                LEFT JOIN sys_unit_systems susx ON susx.id = a.system_id AND susx.active = 0 AND susx.deleted =0 
                " . $whereSql . " 
                ORDER BY a.id   
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
    
    /*
         * @author Okan CIRAN
     * @ sys_units tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  07.04.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeActiveOrPassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
            if (isset($params['id']) && $params['id'] != "") {
                $sql = "                 
                UPDATE sys_units
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_units
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
     * @  bootsrap grid doldurmak için sys_units tablosundan unitlerin count unu döndürür !!
     * @version v 1.0  17.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUnitsTreeRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSql = " WHERE a.deleted = 0 AND a.language_parent_id =0  " ; 
             if (isset($params['id']) && $params['id'] != "") {
                $whereSql .= " AND a.system_id = " . intval($params['system_id'])." AND a.parent_id  = " . intval($params['id']) ;               
            } else {
                $whereSql .= "  AND a.parent_id = 0 ";
            }

            $sql = "
                SELECT 
                    COUNT(a.id ) as COUNT 
                FROM sys_units a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  		
                " . $whereSql . "                
                       
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

