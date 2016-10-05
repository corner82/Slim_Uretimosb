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
 * @author Okan CİRAN Ğ
 */
class SysOsb extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_osb tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  09.02.2016
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
                UPDATE sys_osb
                SET  deleted= 1 , active = 1 ,
                      op_user_id = " . $opUserIdValue . "     
                WHERE id = ".intval($params['id']) 
                        );
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
     * @ sys_osb tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  09.02.2016  
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT 
                    a.id, 
                    a.country_id, 
		    COALESCE(NULLIF(c.name, ''), c.name_eng) AS country_name,  
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,    
                    a.name_eng, 
                    a.deleted, 
                    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,                   
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,  
                    a.op_user_id, 
		    u.username,                
                    a.city_id     
                FROM sys_osb  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_code = a.language_code AND c.deleted = 0 AND c.active = 0 
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.op_user_id                   
                ORDER BY name            
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
     * @ sys_osb tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  09.02.2016
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
                
               // print_r($params);
                    $sql = "
                INSERT INTO sys_osb(                        
                        name, 
                        name_eng, 
                        language_id,
                        country_id, 
                        city,                         
                        city_id, 
                        borough_id, 
                        address, 
                        postal_code,
                        op_user_id
                        )
                VALUES (
                        :name, 
                        :name_eng, 
                        ".intval($languageIdValue).",
                        :country_id, 
                        :city,                         
                        :city_id, 
                        :borough_id, 
                        :address, 
                        :postal_code,
                         ".intval($opUserIdValue)."
                                              )  ";
                            
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':city', $params['city'], \PDO::PARAM_STR);
                    $statement->bindValue(':city_id', $params['city_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':borough_id', $params['borough_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':address', $params['address'], \PDO::PARAM_STR);
                    $statement->bindValue(':postal_code', $params['postal_code'], \PDO::PARAM_STR);
                  // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_osb_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'name';
                    $pdo->rollback();
                    // $result = $kontrol;
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
     * sys_osb tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  09.02.2016
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
                UPDATE sys_osb
                SET    
                    name= '".$params['name']."',  
                    name_eng= '".$params['name_eng']."', 
                    language_id= ".intval($languageIdValue).",
                    country_id=  ".intval($params['country_id']).", 
                    city= '".$params['city']."', 
                    city_id= ".intval($params['city_id']).", 
                    borough_id= ".intval($params['borough_id']).", 
                    address= '".$params['address']."', 
                    postal_code= '".$params['postal_code']."', 
                    op_user_id= ".intval($opUserIdValue)."
                WHERE id =  ".intval($params['id'])
                            ;
                            
                    $statement = $pdo->prepare($sql);
                   // echo debugPDO($sql, $params);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505  unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'name';
                    $pdo->rollback();
                    // $result = $kontrol;
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
     * @ sys_osb tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 23.08.2016
     * @param array | null $args
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
                name AS name , 
                '" . $params['name'] . "' AS value , 
                name ='" . $params['name'] . "' AS control,
                CONCAT(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM sys_osb                
            WHERE 
                LOWER(REPLACE(name,' ','')) = LOWER(REPLACE('" . $params['name'] . "',' ',''))
                ". $addSql . " 
               AND deleted =0   
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
     * @ Gridi doldurmak için sys_osb tablosundan kayıtları döndürür !!
     * @version v 1.0  09.02.2016
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
            $sort = "a.priority ASC, name";            
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
                    a.id, 
                    a.country_id, 
		    COALESCE(NULLIF(c.name, ''), c.name_eng) AS country_name,  
                    COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,    
                    a.name_eng, 
                    a.deleted, 
                    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active,  
                    a.language_parent_id, 
                    a.language_code, 
		    COALESCE(NULLIF(l.language_eng,''), l.language) AS language_name,  
                    a.user_id, 
		    u.username,
                    a.priority, 
                    a.city_id     
                FROM sys_osb  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_code = a.language_code AND c.deleted = 0 AND c.active = 0 
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id 
                WHERE deleted = 0 AND a.language_code = :language_code     
                    " . $whereNameSQL . "
                    AND country_id = :country_id 
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
            $statement->bindValue(':country_id', $args['country_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_STR);             
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
     * @ Gridi doldurmak için sys_osb tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  09.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSQL = " WHERE a.language_code = '".$params['language_code']."' AND a.country_id =  ".intval($params['country_id']);
                            
            $sql = "
                    SELECT 
                        count(a.id) AS count  
                    FROM sys_osb a
                    INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
                    INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0
                    INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_code = a.language_code AND c.deleted = 0 AND c.active = 0 
                    INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active = 0 
                    INNER JOIN info_users u ON u.id = a.user_id  
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
     * @author Okan CIRAN
     * @ sys_osb tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  29.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                INSERT INTO sys_osb(
                    country_id, name, name_eng, language_parent_id, 
                    language_id, user_id, priority, city_id, language_code)                  
               SELECT    
                    country_id, name, name_eng, language_id, language_parent_id, 
		    user_id, priority, city_id, language_main_code
               FROM ( 
                       SELECT c.country_id,
                            '' AS name,                            
                            COALESCE(NULLIF(c.name_eng, ''), c.name) as name_eng, 
                            l.id as language_id,  
                            (SELECT x.id FROM sys_osb x WHERE x.id =:id AND x.deleted =0 AND x.active =0 AND x.language_parent_id =0) AS language_parent_id,                            
                            c.user_id, 
			    c.priority,
			    city_id, 	 
                            l.language_main_code
                        FROM sys_osb c
                        LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                        WHERE c.id =".intval($params['id'])." 
                        ) AS xy   
                        WHERE xy.language_main_code NOT IN 
                           (SELECT distinct language_code 
                           FROM sys_osb cx 
                           WHERE (cx.language_parent_id =".intval($params['id'])."  OR cx.id =".intval($params['id'])." ) AND cx.deleted =0 AND cx.active =0)
                ");           
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_osb_id_seq');
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
     * @ ddslick doldurmak için sys_osb tablosundan osb kayıtları döndürür !!
     * @version v 1.0 09.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOsbDdlist($params = array()) {
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
            $addSql ="" ;
            $countryId = 91;
            if (isset($params['country_id']) && $params['country_id'] != "") {
                $countryId = $params['country_id'];    
                $addSql .= " AND a.country_id = ".intval($countryId);
            }           
           
            
            if (isset($params['city_id']) && $params['city_id'] != "") {
                $cityId = $params['city_id'];
                $addSql .= " AND a.city_id = ".intval($cityId);
            }
            $sql ="                
                SELECT  
                    a.id,
                    COALESCE(NULLIF(ax.name, ''), a.name_eng) AS name,
                    a.name_eng, 
                    a.active 
                FROM sys_osb a 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
		LEFT JOIN sys_osb ax ON (ax.id =a.id OR ax.language_parent_id = a.id) AND ax.deleted =0 AND ax.active =0 AND lx.id = ax.language_id   
                WHERE 
                    a.active = 0 AND 
                    a.deleted = 0 AND 
                    a.language_parent_id =0 
                    ".$addSql."
                ORDER BY name  
                                 ";
             $statement = $pdo->prepare( $sql);
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
     * @ sys_osb tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  23.08.2016
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
                UPDATE sys_osb
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_osb
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
     * @ sys_osb bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  23.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOsbList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
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
                $sort = " name";
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

            $sorguStr = null;
            if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);

                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name" . $sorguExpression . ' ';

                                break;
                            case 'name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name_eng" . $sorguExpression . ' ';

                                break;
                            case 'country_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND country_name" . $sorguExpression . ' ';

                                break;    
                             case 'city_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND city_name" . $sorguExpression . ' ';

                                break;   
                            
                            case 'city':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND city" . $sorguExpression . ' ';
                            
                                break;  
                            case 'address':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND address" . $sorguExpression . ' ';
                            
                                break;  
                             case 'postal_code':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND postal_code" . $sorguExpression . ' ';
                            
                                break;  
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = ""; 
            }
                            
            $sorguStr = rtrim($sorguStr, "AND ");            
                            
            $sql = "  
                SELECT
                    id, 
                    country_id, 
                    country_name,  
                    name,  
                    name_eng,
                    active, 
                    state_active,
                    op_user_id, 
                    op_user_name,
                    city_id,
                    city_name,
                    city,
                    borough_id,
                    borough_name,
                    deleted,
                    address,
                    postal_code
                FROM (
                    SELECT 
                        a.id, 
                        a.country_id, 
                        COALESCE(NULLIF(c.name, ''), c.name_eng) AS country_name,  
                        COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,  
                        a.name_eng,
                        a.active, 
                        sd16.description as state_active,
                        a.op_user_id, 
                        u.username AS op_user_name,
                        a.city_id,
                        sc.name AS city_name,
                        a.city,
                        a.borough_id,
                        sb.name AS borough_name,
                        a.deleted,
                        a.address,
                        a.postal_code
                    FROM sys_osb  a
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id  AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_id = l.id AND c.deleted = 0 AND c.active = 0                 
                    LEFT JOIN sys_city sc ON sc.city_id = a.city_id AND sc.active=0 AND sc.deleted =0 AND sc.language_id = l.id 
                    LEFT JOIN sys_borough sb ON sb.boroughs_id = a.borough_id AND sb.city_id = a.city_id AND sb.active=0 AND sb.deleted =0 AND sb.language_id = l.id 
                    INNER JOIN info_users u ON u.id = a.op_user_id 
                    WHERE 
                        a.deleted = 0 AND 
                        a.language_parent_id =0 
                    ) AS xtable WHERE deleted =0 
                ".$sorguStr."
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
     * @ sys_osb bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  23.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOsbListRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $sorguStr = null;
            if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);

                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                             case 'name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name" . $sorguExpression . ' ';

                                break;
                            case 'name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name_eng" . $sorguExpression . ' ';

                                break;
                            case 'country_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND country_name" . $sorguExpression . ' ';

                                break;    
                             case 'city_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND city_name" . $sorguExpression . ' ';

                                break;   
                            
                            case 'city':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND city" . $sorguExpression . ' ';
                            
                                break;  
                            case 'borough_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND borough_name" . $sorguExpression . ' ';
                            
                                break;  
                              case 'address':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND address" . $sorguExpression . ' ';
                            
                                break;  
                             case 'postal_code':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND postal_code" . $sorguExpression . ' ';
                            
                                break;  
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                             
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = " 
                SELECT COUNT(id) AS count 
                FROM (
                    SELECT   
                        id, 
                        country_id, 
                        country_name,  
                        name,  
                        name_eng,
                        active, 
                        state_active,
                        op_user_id, 
                        op_user_name,
                        city_id,
                        city_name,
                        city,
                        borough_id,
                        borough_name,
                        deleted,
                        address,
                        postal_code
                    FROM (
                        SELECT 
                            a.id, 
                            a.country_id, 
                            COALESCE(NULLIF(c.name, ''), c.name_eng) AS country_name,  
                            COALESCE(NULLIF(a.name, ''), a.name_eng) AS name,  
                            a.name_eng,
                            a.active, 
                            sd16.description as state_active,
                            a.op_user_id, 
                            u.username AS op_user_name,
                            a.city_id,
                            sc.name AS city_name,
                            a.city,
                            a.borough_id,
                            sb.name AS borough_name,
                            a.deleted,
                            a.address,
                            a.postal_code
                        FROM sys_osb  a
                        INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
                        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id  AND sd16.deleted = 0 AND sd16.active = 0
                        INNER JOIN sys_countrys c ON c.id = a.country_id AND c.language_id = l.id AND c.deleted = 0 AND c.active = 0                 
                        LEFT JOIN sys_city sc ON sc.city_id = a.city_id AND sc.active=0 AND sc.deleted =0 AND sc.language_id = l.id 
                        LEFT JOIN sys_borough sb ON sb.boroughs_id = a.borough_id AND sb.city_id = a.city_id AND sb.active=0 AND sb.deleted =0 AND sb.language_id = l.id 
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        WHERE 
                            a.deleted = 0 AND 
                            a.language_parent_id =0 
                        ) AS xtable   
                        WHERE deleted =0  
                        ".$sorguStr." 
                    ) AS xxtable 
              
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
