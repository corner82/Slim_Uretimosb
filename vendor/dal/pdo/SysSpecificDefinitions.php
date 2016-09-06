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
class SysSpecificDefinitions extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_specific_definitions tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  25.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
          try {            
        } catch (\PDOException $e /* Exception $e */) {            
        }
    } 

    /**     
     * @author Okan CIRAN
     * @ sys_specific_definitions tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  25.01.2016    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $statement = $pdo->prepare("
            SELECT 
                a.id, 
                a.main_group, 
                a.first_group, 
                a.second_group,  
                COALESCE(NULLIF(a.description, ''), a.description_eng) AS name,  
                a.deleted, 
                a.parent_id, 
                a.active, 
                a.user_id, 
                a.language_parent_id, 
                a.language_code,
                COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name, 
                sd.description AS state_deleted,  
                sd1.description AS state_active,  
                u.username
            FROM sys_specific_definitions a  
            INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0 
            INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
            INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted = 0 AND l.active = 0 
            INNER JOIN info_users u ON u.id = a.user_id 
            WHERE a.deleted =0 AND a.language_code = :language_code            
            ORDER BY a.id, a.parent_id                
                                 ");
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR); 
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
     * @ sys_specific_definitions tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  25.01.2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $kontrol = $this->haveRecords($params); 
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) { 
                $sql = "
                INSERT INTO sys_specific_definitions(
                        name, icon_class,  
                        parent, user_id, description, root )
                VALUES (
                        :name,
                        :icon_class,  
                        :parent,                       
                        :user_id,
                        :description,
                        :root
                                             )   ";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);                 
                $statement->bindValue(':parent', $params['parent'], \PDO::PARAM_INT);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
                $statement->bindValue(':root', $params['root'], \PDO::PARAM_INT);
               // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_specific_definitions_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {  
                $errorInfo = '23505'; 
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
     * @author Okan CIRAN
     * @ sys_specific_definitions tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 15.01.2016
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
                name as name , 
                '" . $params['name'] . "' AS value , 
                name ='" . $params['name'] . "' AS control,
                concat(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_specific_definitions                
            WHERE LOWER(name) = LOWER('" . $params['name'] . "')"
                    . $addSql . " 
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
     * sys_specific_definitions tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  25.01.2016
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
                $sql = "
                UPDATE sys_specific_definitions
                SET   
                    name = :name,
                    active = :active,
                    user_id = :user_id                    
                WHERE id = " . intval($params['id']);
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);  
                $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT); 
                $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT); 
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {                
                // 23505 	unique_violation
                $errorInfo = '23505';// $kontrol ['resultSet'][0]['message'];  
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
     * @author Okan CIRAN
     * sys_specific_definitions tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  25.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updateChild($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();           
            $sql = " 
            UPDATE sys_specific_definitions
                SET                     
                    active = :active,              
                    user_id= :user_id
                WHERE id IN (
                  SELECT id FROM sys_specific_definitions P WHERE p.root = (
                                  SELECT DISTINCT COALESCE(NULLIF(root, 0),id) FROM sys_specific_definitions WHERE deleted = 0 AND id=" . $params['id'] . " )
                  AND parent >=" . $params['id'] . " OR id = " . $params['id'] . " 
                  )
                ";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
            $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
          //  echo debugPDO($sql, $params);
            //Execute our UPDATE statement.
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  25.01.2016
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
            $sort = "id, parent_id";            
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
 
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
            SELECT 
                id, 
                main_group, 
                first_group, 
                second_group,  
                name,  
                deleted, 
                parent_id, 
                active, 
                user_id, 
                language_parent_id, 
                language_code,
                language_name, 
                state_deleted,  
                state_active,  
                username FROM (
                        SELECT 
                            a.id, 
                            a.main_group, 
                            a.first_group, 
                            a.second_group,  
                            COALESCE(NULLIF(a.description, ''), a.description_eng) AS name,  
                            a.deleted, 
                            a.parent_id, 
                            a.active, 
                            a.user_id, 
                            a.language_parent_id, 
                            a.language_code,
                            COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name, 
                            sd.description AS state_deleted,  
                            sd1.description AS state_active,  
                            u.username
                        FROM sys_specific_definitions a  
                        INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0 
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                        INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted = 0 AND l.active = 0 
                        INNER JOIN info_users u ON u.id = a.user_id 
                        WHERE a.deleted =0 AND language_code = '" . $params['language_code'] . "' ) AS asd               
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
     * @ Gridi doldurmak için sys_specific_definitions tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  25.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSQL = '';
            $whereSQL1 = ' WHERE a1.deleted =0 ';
            $whereSQL2 = ' WHERE a2.deleted =1 ';            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT ,
                    (SELECT COUNT(a1.id) FROM sys_specific_definitions a1  
                    INNER JOIN sys_specific_definitions sd1x ON sd1x.main_group = 15 AND sd1x.first_group= a1.deleted AND sd1x.language_code = 'tr' AND sd1x.deleted = 0 AND sd1x.active = 0
                    INNER JOIN sys_specific_definitions sd11 ON sd11.main_group = 16 AND sd11.first_group= a1.active AND sd11.language_code = 'tr' AND sd11.deleted = 0 AND sd11.active = 0                             
                    INNER JOIN info_users u1 ON u1.id = a1.user_id 
                     " . $whereSQL1 . " ) AS undeleted_count, 
                    (SELECT COUNT(a2.id) FROM sys_specific_definitions a2
                    INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a2.deleted AND sd2.language_code = 'tr' AND sd2.deleted = 0 AND sd2.active = 0
                    INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group= a2.active AND sd12.language_code = 'tr' AND sd12.deleted = 0 AND sd12.active = 0                             
                    INNER JOIN info_users u2 ON u2.id = a2.user_id 			
                      " . $whereSQL2 . " ) AS deleted_count                        
                FROM sys_specific_definitions a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.user_id 
                " . $whereSQL . "
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * Combobox fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ combobox doldurmak için sys_specific_definitions tablosundan parent ı 0 olan kayıtları (Ana grup) döndürür !!
     * @version v 1.0  25.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMainDefinitions() {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $statement = $pdo->prepare("
            SELECT      
		 a.first_group as id,              
                COALESCE(NULLIF(a.description, ''), a.description_eng) AS name, 
                a.description_eng as name_eng,
		a.active               
            FROM sys_specific_definitions a       
            WHERE a.main_group  =0 AND 
              a.deleted =0  AND language_code = '" . $params['language_code'] . "'             
            ORDER BY a.first_group                
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
     * Combobox fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ combobox doldurmak için sys_specific_definitions tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  25.01.2016
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function fillFullDefinitions($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $id = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $id = $params['id'];
            }
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.parent = " . $id . " AND                   
                    a.deleted = 0 AND                    
                ORDER BY a.id, a.parent_id   
                                                  
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
     * Fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ İletişim Tiplerini dropdown ya da tree ye doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  25.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillCommunicationsTypes($params = array()) {
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.main_group = 5 AND                    
                    a.deleted = 0 AND
                    a.language_parent_id =0 
                ORDER BY a.id, a.parent_id   
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
     * Fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ Bina Tiplerini dropdown ya da tree ye doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  25.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillBuildingType($params = array()) {
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.main_group = 4 AND                    
                    a.deleted = 0 AND
                    a.language_parent_id =0 
                ORDER BY a.id, a.parent_id   
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
     * Fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ Mülkiyet Tiplerini dropdown ya da tree ye doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  25.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillOwnershipType($params = array()) {
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.main_group = 1 AND                    
                    a.deleted = 0 AND
                    a.language_parent_id =0 
                ORDER BY a.id, a.parent_id   
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
     * Fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ Personel Tiplerini dropdown ya da tree ye doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  25.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillPersonnelTypes($params = array()) {
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" .intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.main_group = 10 AND                    
                    a.deleted = 0 AND
                    a.language_parent_id =0 
                ORDER BY a.id, a.parent_id   
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
     * Fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ İletişim adresleri dropdown ya da tree ye doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  03.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillAddressTypes($params = array()) {
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =" . intval($languageIdValue). " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.main_group = 17 AND                    
                    a.deleted = 0 AND
                    a.language_parent_id =0 
                ORDER BY a.id, a.parent_id   
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
     * Fill function used for testing
     * user interface combobox fill operation   
     * @author Okan CIRAN
     * @ İletişim adresleri dropdown ya da tree ye doldurmak için sys_specific_definitions tablosundan kayıtları döndürür !!
     * @version v 1.0  03.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillSexTypes($params = array()) {
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
                    a.first_group AS id, 	
                    COALESCE(NULLIF(sd.description, ''), a.description_eng) AS name,  
                    a.description_eng AS name_eng,
                    a.parent_id,
                    a.active,
                    CASE 
                    (SELECT DISTINCT 1 state_type FROM sys_specific_definitions WHERE parent_id = a.id AND deleted = 0)    
                     WHEN 1 THEN 'closed'
                     ELSE 'open'   
                     END AS state_type  
                FROM sys_specific_definitions a    
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue). " AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_specific_definitions sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
                WHERE                     
                    a.main_group = 3 AND                    
                    a.deleted = 0 AND
                    a.language_parent_id =0 
                ORDER BY a.id, a.parent_id   

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
    
    
    
}
