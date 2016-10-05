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
 * @since 20.04.2016
 */
class SysProductionTypes extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_production_types tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  20.04.2016
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
                $sql = " 
                UPDATE sys_production_types
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
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_production_types tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  20.04.2016  
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
                    COALESCE(NULLIF(ax.name, ''), a.name_eng) AS name,  
                    a.name_eng,  
                    a.logo,
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                    a.language_parent_id
                FROM sys_production_types a                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0
                LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)." AND l.deleted =0 AND lx.active = 0
                LEFT JOIN sys_production_types ax ON (ax.id= a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted = 0 AND ax.active = 0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0                             
		LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0                
                INNER JOIN info_users u ON u.id = a.op_user_id   
                ORDER BY a.language_id, name 
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
     * @ sys_production_types tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  20.04.2016
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
                INSERT INTO sys_production_types(   
                        name,                        
                        name_eng, 
                        logo,                         
                        language_id,                         
                        op_user_id  
                        )
                VALUES (
                        :name,
                        :name_eng, 
                        :logo,                          
                        ". intval($languageIdValue).",                         
                        ". intval($opUserIdValue)." 
                                             )   ";                    
                    $statement = $pdo->prepare($sql);                    
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR); 
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR); 
                    $statement->bindValue(':logo', $params['logo'], \PDO::PARAM_STR);                    
                  // echo debugPDO($sql, $params);
                    $result = $statement->execute();                   
                    $insertID = $pdo->lastInsertId('sys_production_types_id_seq');                 
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'name';
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
     * @ sys_production_types tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 15.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
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
                 a.name AS name , 
                 '" . $params['name'] . "' AS value , 
                 1 =1 AS control,
                 CONCAT( a.name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_production_types  a                      
            WHERE a.name =  '" . $params['name'] . "' AND                
                  a.language_id = " . intval($languageIdValue). " AND 		   
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
     * @ sys_production_types tablosunda parent id ye sahip alt elemanlar var mı   ?  
     * @version v 1.0 06.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveUnitRecords($params = array()) {
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
                a.parent_id  = " . $params['id'] . " 
                AS control,
                'Bu Grup Altında Unit Kaydı Bulunmakta. Lütfen Kontrol Ediniz !!!' AS message   
            FROM sys_production_types  a  
            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
            WHERE a.parent_id = ".$params['id']. "
                AND a.language_parent_id =0                  
                AND a.deleted =0    
            LIMIT 1                      
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
     * sys_production_types tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  20.04.2016
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
                UPDATE sys_production_types
                SET                      
                    name = :name,                        
                    name_eng = :name_eng, 
                    logo = :logo,
                    language_id = :language_id,                    
                    op_user_id = :op_user_id                    
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);                                                                       
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':logo', $params['logo'], \PDO::PARAM_STR);                    
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
                    $errorInfoColumn = 'name';
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
     * @ Gridi doldurmak için sys_production_types tablosundan kayıtları döndürür !!
     * @version v 1.0  20.04.2016
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
            $sort = "a.language_id, name ";
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
                    COALESCE(NULLIF(ax.name, ''), a.name_eng) AS name,  
                    a.name_eng,  
                    a.logo,
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
                    a.language_parent_id
                FROM sys_production_types a                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0
                LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)." AND l.deleted =0 AND lx.active = 0
                LEFT JOIN sys_production_types ax ON (ax.id= a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted = 0 AND ax.active = 0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0                             
		LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0                
                INNER JOIN info_users u ON u.id = a.op_user_id   
                WHERE a.deleted =0 AND a.language_parent_id =0 
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
     * @ Gridi doldurmak için sys_production_types tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  20.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                 
            $whereSql = " WHERE a.deleted =0 AND a.language_parent_id =0 " ;
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                 FROM sys_production_types a                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                                
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
     * @ tree ve grid doldurmak için sys_production_types tablosundan unitleri döndürür   !!
     * @version v 1.0  20.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillProductionTypesTree($params = array()) {
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
            
            $whereSql = " WHERE a.deleted = 0 AND a.language_parent_id =0  " ;       
            $sql = "
             SELECT 
                    a.id, 
                    a.active,                 
		    COALESCE(NULLIF(su.name, ''), a.name_eng) AS name,  
                    a.name_eng,  
                    'open' AS state_type ,                     
                    true  AS notroot 
                FROM sys_production_types a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0       		
                LEFT JOIN sys_production_types su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND lx.id = su.language_id                 
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
     * @ sys_production_types tablosundan parametre olarak  gelen id kaydın aktifliğini
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
             $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
            if (isset($params['id']) && $params['id'] != "") {
                $sql = "                 
                UPDATE sys_production_types
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_production_types
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
     * @  bootsrap grid doldurmak için sys_production_types tablosundan unitlerin count unu döndürür !!
     * @version v 1.0  20.04.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillProductionTypesTreeRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSql = " WHERE a.deleted = 0 AND a.language_parent_id =0  " ;              

            $sql = "
                SELECT 
                    COUNT(a.id ) as COUNT 
                FROM sys_production_types a
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

