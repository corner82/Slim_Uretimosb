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
class SysSectors extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_sectors tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  07.12.2015
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
                UPDATE sys_sectors
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $userIdValue . "     
                WHERE id = :id");                
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
     * @ sys_sectors tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  07.12.2015    
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
                    a.active,
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                    a.deleted,
		    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                    a.ordr as siralama,
                    a.language_parent_id,
                    a.description,
                    a.description_eng,
                    a.op_user_id,
                    u.username
                FROM sys_sectors a
	        INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
	        LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
	        LEFT JOIN sys_sectors ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id = lx.id 
	        INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted =0 AND sd15.active =0 
	        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
	        LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
	        LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id 
                WHERE a.deleted =0                 
                ORDER BY a.name              
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
     * @ sys_sectors tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  08.12.2015
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
                if (!\Utill\Dal\Helper::haveRecord($params)) {
                    if (!isset($kontrol[0]['control'])) {                       
                        $languageId = NULL;
                        $languageIdValue = 647;
                        if ((isset($params['language_code']) && $params['language_code'] != "")) {
                            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                                $languageIdValue = $languageId ['resultSet'][0]['id'];
                            }
                        }

                        $statement = $pdo->prepare("
                        INSERT INTO sys_sectors(
                                name, 
                                name_eng, 
                                language_id, 
                                ordr,
                                description,
                                description_eng,
                                op_user_id                               
                                )
                        VALUES (
                                :name,
                                :name_eng, 
                                :language_id,
                                :ordr,
                                :description,
                                :description_eng,
                                :op_user_id                                
                                                ");
                        $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                        $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
                        $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                        $statement->bindValue(':ordr', $params['ordr'], \PDO::PARAM_INT);
                        $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                        $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                        $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                        $result = $statement->execute();
                        $insertID = $pdo->lastInsertId('sys_sectors_id_seq');
                        $errorInfo = $statement->errorInfo();
                        if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                            throw new \PDOException($errorInfo[0]);
                        $pdo->commit();
                        return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                    } else {
                        $pdo->rollback();
                    }
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'machine_id';
                    $pdo->rollback();
                    // $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';   // 23502  user_id not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_sectors tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 15.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = " AND a.deleted =0  ";
            if (isset($params['id'])) {
                $addSql .= " AND a.id != " . intval($params['id']);
            }
            $sql = " 
            SELECT  
                    name AS name,
                    '" . $params['name'] . "' AS value ,
                    name ='" . $params['name'] . "' AS control,
                    CONCAT(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message
            FROM sys_sectors
            WHERE name = '" . $params['name'] . "' 
            " . $addSql . " 
                               ";
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

    /** 
     * @author Okan CIRAN
     * sys_sectors tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.12.2015
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
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];
                        }
                    }

                    $statement = $pdo->prepare("
                    UPDATE sys_sectors
                    SET              
                        name = :name, 
                        name_eng = :name_eng, 
                        language_id = :language_id, 
                        ordr = :ordr,
                        description = :description,
                        description_eng = :description_eng,                       
                        op_user_id= :op_user_id  
                    WHERE id = :id");
                    $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':ordr', $params['ordr'], \PDO::PARAM_INT);
                    $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                    $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':op_user_id',$opUserIdValue, \PDO::PARAM_INT);               
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505  unique_violation
                    $errorInfoColumn = 'name';
                    $errorInfo = '23505';
                    $pdo->rollback();
                    $result = $kontrol;
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
     * @ Gridi doldurmak için sys_sectors tablosundan kayıtları döndürür !!
     * @version v 1.0  08.12.2015
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
            //$sort = "id";
            $sort = "name";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            //$order = "desc";
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
                    COALESCE(NULLIF(ax.name, ''), a.name_eng) AS name,
                    a.name_eng,
                    a.active,
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
                    a.deleted,
		    COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng) AS state_deleted,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
		    COALESCE(NULLIF(lx.language, ''), 'en') AS language_name,
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                    a.ordr as siralama,
                    a.language_parent_id,
                    a.description,
                    a.description_eng,
                    a.op_user_id,
                    u.username
                FROM sys_sectors a
	        INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
	        LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
	        LEFT JOIN sys_sectors ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id = lx.id 
	        INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted =0 AND sd15.active =0 
	        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
	        LEFT JOIN sys_specific_definitions sd15x ON sd15x.language_id =lx.id AND (sd15x.id = sd15.id OR sd15x.language_parent_id = sd15.id) AND sd15x.deleted =0 AND sd15x.active =0 
	        LEFT JOIN sys_specific_definitions sd16x ON sd16x.language_id = lx.id AND (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted = 0 AND sd16x.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id                                 
                WHERE a.deleted = 0 
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
            $statement->bindValue(':language_id',$languageIdValue, \PDO::PARAM_INT);  
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
     * @ Gridi doldurmak için sys_sectors tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
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
            $sql = "
                SELECT 
			COUNT(a.id) AS COUNT 
                FROM sys_sectors a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
	        LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0	        
	        INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = l.id AND sd15.deleted =0 AND sd15.active =0 
	        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id AND sd16.deleted = 0 AND sd16.active = 0
	        INNER JOIN info_users u ON u.id = a.op_user_id                      
                WHERE 
                    a.deleted = 0
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
     * @ Gridi doldurmak için sys_sectors tablosundan kayıtları döndürür !!
     * @version v 1.0  08.12.2015
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
                    COALESCE(NULLIF(sd.name, ''), a.name_eng) AS name                                 
		FROM sys_sectors a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".$languageIdValue." AND lx.deleted =0 AND lx.active =0                
		LEFT JOIN sys_sectors sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id
                WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id = 0  
                ORDER BY name               
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
     * @ sys_sectors tablosuna aktif olan diller için ,tek bir kaydın tabloda olmayan diğer dillerdeki kayıtlarını oluşturur   !!
     * @version v 1.0  29.12.2015
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
                
                INSERT INTO sys_sectors(
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
                        FROM sys_sectors c
                        LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                        WHERE c.id =  ".intval($params['id'])."
                        ) AS xy   
                        WHERE xy.language_main_code NOT IN 
                            (SELECT DISTINCT language_code 
                            FROM sys_sectors cx 
                            WHERE 
                                (cx.language_parent_id = ".intval($params['id'])."  OR
                                cx.id = ".intval($params['id'])." ) AND
                                cx.deleted =0 AND 
                                cx.active =0)) 
                    ");            
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_sectors_id_seq');
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
     * @ text alanları doldurmak için sys_sectors tablosundan tek kayıt döndürür !! 
     * insertLanguageTemplate fonksiyonu ile oluşturulmuş kayıtları 
     * combobox dan çağırmak için hazırlandı.
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
                FROM sys_sectors  a
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
     * @ Sektör isimlerini sys_sectors tablosundan döndürür !!
     * @version v 1.0  09.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */ 
    public function getSectors($params = array()) {
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
            $whereSql = " WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id =0 "; 
            $sql = "
                SELECT 
                    a.id, 
                    COALESCE(NULLIF(ax.name, ''), a.name_eng) AS name,
                    a.name_eng,
                    COALESCE(NULLIF(ax.description, ''), a.description_eng) AS description,
                    a.description_eng 
                FROM sys_sectors a
	        INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
	        LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
	        LEFT JOIN sys_sectors ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND ax.language_id =lx.id
                " . $whereSql . "
                ORDER BY a.name
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

 
