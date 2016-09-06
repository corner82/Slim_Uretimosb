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
class SysCountrys extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_countrys tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  07.12.2015
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
                UPDATE sys_countrys
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . intval($opUserIdValue) . "     
                WHERE id =  ". intval($params['id']) );
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
     * @ sys_countrys tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  07.12.2015    
     * @param type $params
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
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name, 
                    a.name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active, 		                      
                    a.language_code,  
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                     
                    a.language_parent_id,
                    a.flag_icon_road,
                    a.country_code2,   
                    a.country_code3,  
                    a.op_user_id, 
                    u.username,
                    a.priority                  
                FROM sys_countrys a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND 
			sd.language_code = a.language_code AND sd.active =0 AND sd.deleted = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND 
			sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
		INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id  
                WHERE a.deleted =0 AND a.language_id = ". intval($languageIdValue)."
                ORDER BY a.priority ASC, name
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
     * @author Okan CIRAN
     * @ sys_countrys tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 21.01.2016
     * @param type $params
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
                name AS name,
                '" . $params['name'] . "' AS value,
                name ='" . $params['name'] . "' AS control,
                concat(name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_countrys
            WHERE LOWER(name) = LOWER('" . $params['name'] . "')"
                    . $addSql . " 
               AND language_id =  ".  intval($languageIdValue)."
               AND deleted =0
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
     * @ sys_countrys tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  08.12.2015
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
                    $statement = $pdo->prepare("
                INSERT INTO sys_countrys(
                        name, 
                        name_eng, 
                        language_id,  
                        op_user_id, 
                        flag_icon_road, 
                        country_code2,
                        country_code3,   
                        priority)
                VALUES (
                        :name,
                        :name_eng, 
                        " . intval($languageIdValue) . ",
                        " . intval($opUserIdValue) . ",
                        :user_id,
                        :flag_icon_road,                       
                        :country_code2,
                        :country_code3,  
                        :priority 
                                              )  ");
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':flag_icon_road', $params['flag_icon_road'], \PDO::PARAM_STR);
                    $statement->bindValue(':country_code2', $params['country_code2'], \PDO::PARAM_STR);
                    $statement->bindValue(':country_code3', $params['country_code3'], \PDO::PARAM_STR);
                    $statement->bindValue(':priority', $params['priority'], \PDO::PARAM_INT);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_countrys_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
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
     * sys_countrys tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.12.2015
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
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
            } 
            $statement = $pdo->prepare("
                UPDATE sys_countrys
                SET             
                    name = :name, 
                    name_eng = :name_eng, 
                    language_id = ".intval($languageIdValue).",
                    language_parent_id = :language_parent_id,
                    user_id = :user_id,
                    flag_icon_road = :flag_icon_road,                       
                    country_code2 = :country_code2,
                    country_code3 = :country_code3,
                    priority = :priority ,
                    active = :active
                WHERE id = :id"); 
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
            $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);            
            $statement->bindValue(':language_parent_id', $params['language_parent_id'], \PDO::PARAM_INT);                       
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':flag_icon_road', $params['flag_icon_road'], \PDO::PARAM_STR);
            $statement->bindValue(':country_code2', $params['country_code2'], \PDO::PARAM_STR);
            $statement->bindValue(':country_code3', $params['country_code3'], \PDO::PARAM_STR);
            $statement->bindValue(':priority', $params['priority'], \PDO::PARAM_INT);     
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);  
            $update = $statement->execute(); 
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {    
                $errorInfo = '23505';  // 23505 unique_violation
                $pdo->rollback();
                $result= $kontrol;            
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
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
     * @ Gridi doldurmak için sys_countrys tablosundan kayıtları döndürür !!
     * @version v 1.0  08.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGrid($args = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
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
                $sort = "a.priority ASC, name";
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

            $sql = "
                SELECT 
                    a.id,                   
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name, 
                    a.name_eng, 
                    a.deleted, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active, 		                      
                    a.language_id,  
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                     
                    a.language_parent_id,
                    a.flag_icon_road,
                    a.country_code2,
                    a.country_code3,
                    a.user_id, 
                    u.username,
                    a.priority                  
                FROM sys_countrys a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND 
			sd.language_id = a.language_id AND sd.active =0 AND sd.deleted = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND 
			sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id
                WHERE a.deleted = 0 AND 
                      a.language_id = " . intval($languageIdValue) . "
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
            // echo debugPDO($sql, $parameters);           
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
     * @ Gridi doldurmak için sys_countrys tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  08.12.2015
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
            $whereSQL = " WHERE a.language_id = " . intval($languageIdValue) . " ";
            $whereSQL1 = " WHERE a1.language_id = " . intval($languageIdValue) . " AND a1.deleted =0 ";
            $whereSQL2 = " WHERE a2.language_id = " . intval($languageIdValue) . " AND a2.deleted = 1 ";            
            $sql = "
                 SELECT 
			COUNT(a.id) AS COUNT ,                  
			(SELECT COUNT(a1.id) AS COUNT FROM sys_countrys a1
			INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 15 AND sd1.first_group= a1.deleted AND 
				sd1.language_id = a1.language_id AND sd1.active =0 AND sd1.deleted = 0
			INNER JOIN sys_specific_definitions sd11 ON sd11.main_group = 16 AND sd11.first_group= a1.active AND 
				sd11.language_id = a1.language_id AND sd11.deleted = 0 AND sd11.active = 0
			INNER JOIN sys_language l1 ON l1.id = a1.language_id AND l1.deleted =0 
			 " . $whereSQL1 . ") AS undeleted_count,
			(SELECT COUNT(a2.id) AS COUNT FROM sys_countrys a2
			INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a2.deleted AND 
				sd2.language_id = a2.language_id AND sd2.active =0 AND sd2.deleted = 0
			INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group= a2.active AND 
				sd12.language_id = a2.language_id AND sd12.deleted = 0 AND sd12.active = 0
			INNER JOIN sys_language l2 ON l2.id = a2.language_id AND l2.deleted =0
			 " . $whereSQL2 . " ) AS deleted_count 
                FROM sys_countrys a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND 
			sd.language_id = a.language_id AND sd.active =0 AND sd.deleted = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND 
			sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 		 
                 " . $whereSQL . "
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ combobox ı doldurmak için sys_countrys tablosundan çekilen kayıtları döndürür   !!
     * @version v 1.0  17.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
     public function fillComboBox($params = array()) {
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
                    COALESCE(NULLIF(sd.name, ''), a.name_eng) AS name,                    
                    a.name_eng,
                    CASE (SELECT COUNT(z.id) FROM sys_city z WHERE z.country_id = a.id) 
			WHEN 0 THEN false
			ELSE true END AS citylist,
                    a.active    
                FROM sys_countrys a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                
		LEFT JOIN sys_countrys sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id
                WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id = 0
                ORDER BY a.priority, name                  
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
     * @ sys_countrys tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  08.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare("                 
                    INSERT INTO sys_countrys(
                        name, name_eng, language_id, language_parent_id, 
                        user_id, flag_icon_road, country_code3,language_code) 
                    SELECT 
                        name, name_eng, language_id, language_parent_id, 
                        user_id, flag_icon_road, country_code3 ,language_main_code
                    FROM ( 
                            SELECT 
                                '' AS name, 
                                c.name_eng, 
                                l.id AS language_id, 
                                (SELECT x.id FROM sys_countrys x WHERE x.id =" . intval($params['user_id']) . "  AND x.deleted =0 AND x.active =0 AND x.language_parent_id =0) AS language_parent_id,    
                                c.user_id, 		
                                c.flag_icon_road, 
                                l.country_code3,
                                l.language_main_code
                            FROM sys_countrys c
                            LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                            WHERE c.id = " . intval($params['id']) . " 
                    ) AS xy  
                    WHERE xy.language_main_code NOT IN 
                        (SELECT 
                            DISTINCT language_code 
                         FROM sys_countrys cx 
                         WHERE (cx.language_parent_id =" . intval($params['id']) . "  OR cx.id =" . intval($params['id']) . " ) AND cx.deleted =0 AND cx.active =0)
                                                ");  
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_countrys_id_seq');
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
     * @author Okan CIRAN
     * @ sys_countrys tablosundan id degerini getirir.  !!
     * @version v 1.0  17.03.2016    
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function getCountryCode($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = " 
                SELECT                    
                   a.country_code2 AS country_code,
                   a.id = " . intval($params['country_id']) . " AS control
                FROM sys_countrys a                                
                where a.deleted =0 AND a.active = 0 AND 
                    a.id = " . intval($params['country_id']) . "
                LIMIT 1                  
                ";
           //  echo debugPDO($sql, $params);   
            $statement = $pdo->prepare($sql);
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
