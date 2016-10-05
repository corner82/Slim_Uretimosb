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
 * @since 05.03.2016
 */
class SysUnitSystems extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_unit_systems tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  05.03.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $UnitSystem = $this->haveUnitSystemRecords (array('id' => $params['id']));
            if (!\Utill\Dal\Helper::haveRecord($UnitSystem)) {

                $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $statement = $pdo->prepare(" 
                UPDATE sys_unit_systems
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . intval($opUserIdValue) . "     
                WHERE id = " . intval($params['id'])
                    );
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
                $errorInfoColumn = 'System';
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
     * @ sys_unit_systems tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  05.03.2016  
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
                    COALESCE(NULLIF(a.system, ''), a.system_eng) AS systems,  
                    a.system_eng, 
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,              
                    a.language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id
                FROM sys_unit_systems a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                  
                ORDER BY  systems
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
     * @ sys_unit_systems tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  05.03.2016
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
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                        }
                    }  

                    $sql = "
                INSERT INTO sys_unit_systems(                       
                        system_eng,
                        system,
                        language_id, 
                        op_user_id
                        )
                VALUES (                        
                        :system_eng,
                        :system,
                        :language_id,                        
                        :op_user_id
                                             )   ";                    
                    $statement = $pdo->prepare($sql);                    
                    $statement->bindValue(':system_eng', $params['system_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':system', $params['system'], \PDO::PARAM_STR);                                        
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);                    
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_unit_systems_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'system';
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
     * @ sys_unit_systems tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0  05.03.2016
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
                 a.system  AS name , 
                 '" . $params['system'] . "' AS value , 
                 1 =1 AS control,
                 CONCAT(a.system ,' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_unit_systems  a            
            WHERE a.system=  '" . $params['system'] . "'
             " . $addSql . " 
            AND a.deleted =0    
                               ";
            $statement = $pdo->prepare($sql);
           //   echo debugPDO($sql, $params);
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
     * @ sys_units tablosunda system id ye sahip elemanlar var mı   ?  
     * @version v 1.0 06.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveUnitSystemRecords($params = array()) {
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
            $sql = " 
           SELECT  
                a.unitcode AS name ,             
                a.system_id  = " . $params['id'] . " 
                AS control,
                'Bu Sistem Altında Unit Kaydı Bulunmakta. Lütfen Kontrol Ediniz !!!' AS message   
            FROM sys_units  a  
            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
            LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "
            LEFT JOIN sys_units ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id
            WHERE a.system_id = ".$params['id']. "
                AND a.language_parent_id =0                  
                AND a.deleted =0    
            LIMIT 1                      
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
     * sys_unit_systems tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  05.03.2016
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
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                        }
                    }  

                    $sql = "
                UPDATE sys_unit_systems
                SET                     
                    system_eng  = :system_eng,                     
                    system  = :system,                                                             
                    op_user_id = :op_user_id                  
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);                    
                    $statement->bindValue(':system_eng', $params['system_eng'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':system', $params['system'], \PDO::PARAM_STR);                                        
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);   
                 //   echo debugPDO($sql, $params);
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
                    $errorInfoColumn = 'system';
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
     * @ Gridi doldurmak için sys_unit_systems tablosundan kayıtları döndürür !!
     * @version v 1.0  05.03.2016
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
            $sort = "system";
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
		    COALESCE(NULLIF(COALESCE(NULLIF(susx.system, ''), a.system_eng), ''), a.system) AS system ,
		    a.system_eng,
		    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,              
                    a.language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name                    
                FROM sys_unit_systems a  
                INNER JOIN info_users u ON u.id = a.op_user_id                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                LEFT JOIN sys_unit_systems susx ON (susx.language_parent_id = a.id OR susx.id=a.id ) AND susx.active =0 AND susx.deleted =0 AND susx.language_id = lx.id                
	        WHERE 
                    a.language_parent_id = 0 AND 
                    a.deleted =0                      
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
     * @ Gridi doldurmak için sys_unit_systems tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  05.03.2016
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
            $whereSql = " WHERE a.deleted =0 AND  a.language_parent_id = 0 ";
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT
                FROM sys_unit_systems a
                INNER JOIN info_users u ON u.id = a.op_user_id                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0 
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
     * @  sys_unit_systems tablosundan çekilen kayıtları döndürür   !!
     * @version v 1.0  05.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUnitSystems($params = array()) {
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
            $sql = "                
                SELECT 
		    a.id,
		    COALESCE(NULLIF(COALESCE(NULLIF(susx.system, ''), a.system_eng), ''), a.system) AS system ,
		    a.system_eng,
                    a.active
                FROM sys_unit_systems a  
                INNER JOIN info_users u ON u.id = a.op_user_id                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                LEFT JOIN sys_unit_systems susx ON (susx.language_parent_id = a.id OR susx.id=a.id ) AND susx.active =0 AND susx.deleted =0 AND susx.language_id = lx.id                
	        WHERE 
                    a.language_parent_id = 0 AND 
                    a.deleted =0 
                ORDER BY system
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
     * @  sys_unit_systems tablosundan çekilen kayıtları tree yapısında döndürür   !!
     * @version v 1.0  05.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUnitSystemsTree($params = array()) {
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

            $sql = "
                 SELECT 
		    a.id,
                    a.active,
		    COALESCE(NULLIF(COALESCE(NULLIF(susx.system, ''), a.system_eng), ''), a.system) AS system ,
		    a.system_eng,
                    'open' AS state_type
                FROM sys_unit_systems a  
                INNER JOIN info_users u ON u.id = a.op_user_id                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                LEFT JOIN sys_unit_systems susx ON (susx.language_parent_id = a.id OR susx.id=a.id ) AND susx.active =0 AND susx.deleted =0 AND susx.language_id = lx.id                
	        WHERE 
                    a.language_parent_id = 0 AND 
                    a.deleted =0   
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
