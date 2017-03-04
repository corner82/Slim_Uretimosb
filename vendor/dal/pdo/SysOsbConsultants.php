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
class SysOsbConsultants extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  08.02.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
       try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE sys_osb_consultants
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
                            
                $xc = $this->deleteConsultantUser(array('id' => $params['id'],                     
                     'op_user_id' => $opUserIdValue,
                 ));

                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);
                
                $xc = $this->deleteConsultantUserDetail(array('id' => $params['id'],                     
                     'op_user_id' => $opUserIdValue,
                 ));

                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);
                
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
     * @ info_users tablosundan danısman olarak  atanmış user ı siler. !!
     * @version v 1.0  09.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function deleteConsultantUser($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE info_users
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "
                WHERE 
                    id = (
                    SELECT a.user_id FROM sys_osb_consultants a
                    WHERE a.id = ".intval($params['id'])." )"
                    );
                //Execute our DELETE statement.
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);                
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);            
        } catch (\PDOException $e /* Exception $e */) {        
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    /**
     * @author Okan CIRAN
     * @ info_users_detail tablosundan danısman olarak  atanmış user ın detay bilgilerini siler. !!
     * @version v 1.0  09.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function deleteConsultantUserDetail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE info_users_detail
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "
                WHERE active=0 AND deleted =0 AND 
                    root_id = (
                    SELECT a.user_id FROM sys_osb_consultants a
                    WHERE a.id = ".intval($params['id'])." )"
                    );
                //Execute our DELETE statement.
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);                
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);            
        } catch (\PDOException $e /* Exception $e */) {        
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
     

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  08.02.2016  
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
                        u.name AS name,
                        u.surname AS name,
                        a.osb_id,
                        osb.name as osb_name,
                        a.country_id,
                        co.name as country, 		                   
                        a.deleted, 
                        sd.description as state_deleted,                 
                        a.active, 
                        sd1.description as state_active, 
                        a.op_user_id,
                        u1.username AS op_user_name     
                FROM sys_osb_consultants  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0 
                INNER JOIN info_users u1 ON u1.id = a.op_user_id 
                LEFT JOIN sys_osb osb ON osb.id = a.osb_id 
                LEFT JOIN sys_countrys co on co.id = a.country_id AND co.active =0 AND co.deleted =0                
                ORDER BY u.name                 
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
     * @ sys_osb_consultants tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  08.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }       
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                         $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                    }  

                    $sql = "
                INSERT INTO sys_osb_consultants(
                        osb_id, 
                        country_id, 
                        active, 
                        op_user_id, 
                        language_id,                        
                        op_user_id )
                VALUES (
                        :osb_id, 
                        :country_id, 
                        :active, 
                        :user_id, 
                        :language_id,                         
                        :op_user_id 
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':osb_id', $params['osb_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
                    $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);                    
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_osb_consultants_id_seq');
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
                    //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
                }
            } else {
                // 23505 	unique_violation
                $errorInfo = '23505'; // $kontrol ['resultSet'][0]['message'];  
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 15.01.2016
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
                CONCAT(u.name,' ',u.surname) AS name , 
                '" . $params['user_id'] . "' AS value , 
                a.op_user_id =" . intval($params['user_id']) . " AS control,
                CONCAT(u.name,' ',u.surname, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_osb_consultants  a              
            INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0                 
            WHERE a.user_id = " . intval($params['user_id']) . "
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
     * sys_osb_consultants tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  08.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk'], 'id' => $params['id']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }       
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                         $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                    }  

                    $sql = "
                UPDATE sys_osb_consultants
                SET   
                    osb_id= :osb_id, 
                    country_id= :country_id, 
                    active= :active, 
                    user_id = :user_id, 
                    language_id= :language_id, 
                    language_code= :language_code, 
                    op_user_id= :op_user_id 
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':osb_id', $params['osb_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':country_id', $params['country_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
                    $statement->bindValue(':user_id', $params['user_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_INT);
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
                    $errorInfo = '23505'; // $kontrol ['resultSet'][0]['message'];  
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
                }
            } else {
                // 23505 	unique_violation
                $errorInfo = '23505'; // $kontrol ['resultSet'][0]['message'];  
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_osb_consultants tablosundan kayıtları döndürür !!
     * @version v 1.0  08.02.2016
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
        $whereSQL = "";
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = "u.name";
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


        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                  SELECT 
                        a.id, 
                        u.name AS name,
                        u.surname AS name,
                        a.osb_id,
                        osb.name as osb_name,
                        a.country_id,
                        co.name as country, 		                   
                        a.deleted, 
                        sd.description as state_deleted,                 
                        a.active, 
                        sd1.description as state_active, 
                        a.op_user_id,
                        u1.username AS op_user_name     
                FROM sys_osb_consultants  a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0 
                INNER JOIN info_users u1 ON u1.id = a.op_user_id 
                LEFT JOIN sys_osb osb ON osb.id = a.osb_id 
                LEFT JOIN sys_countrys co on co.id = a.country_id AND co.active =0 AND co.deleted =0                                
                WHERE a.deleted =0  
                " . $whereSQL . "
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
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_osb_consultants tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSQL = '';
            $whereSQL1 = ' WHERE ax.deleted =0 ';
            $whereSQL2 = ' WHERE ay.deleted =1 ';

            $sql = "
               SELECT 
                    COUNT(a.id) AS COUNT ,
                    (SELECT COUNT(ax.id) FROM sys_osb_consultants ax  			
			INNER JOIN sys_specific_definitions sdx ON sdx.main_group = 15 AND sdx.first_group= ax.deleted AND sdx.language_code = 'tr' AND sdx.deleted = 0 AND sdx.active = 0
			INNER JOIN sys_specific_definitions sd1x ON sd1x.main_group = 16 AND sd1x.first_group= ax.active AND sd1x.language_code = 'tr' AND sd1x.deleted = 0 AND sd1x.active = 0                             
			INNER JOIN info_users_detail ux ON ux.root_id = ax.user_id AND ux.active = 0 AND ux.deleted = 0 
			INNER JOIN info_users u1x ON u1x.id = ax.op_user_id 
                     " . $whereSQL1 . " ) AS undeleted_count, 
                    (SELECT COUNT(ay.id) FROM sys_osb_consultants ay
			INNER JOIN sys_specific_definitions sdy ON sdy.main_group = 15 AND sdy.first_group= ay.deleted AND sdy.language_code = 'tr' AND sdy.deleted = 0 AND sdy.active = 0
			INNER JOIN sys_specific_definitions sd1y ON sd1y.main_group = 16 AND sd1y.first_group= ay.active AND sd1y.language_code = 'tr' AND sd1y.deleted = 0 AND sd1y.active = 0                             
			INNER JOIN info_users_detail uy ON uy.root_id = ay.user_id AND uy.active = 0 AND uy.deleted = 0 
			INNER JOIN info_users u1y ON u1y.id = ay.op_user_id 			
                      " . $whereSQL2 . ") AS deleted_count                        
                FROM sys_osb_consultants  a
		INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
		INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0 
		INNER JOIN info_users u1 ON u1.id = a.op_user_id 
                " . $whereSQL . "
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosundan osb_id si olan kayıtları döndürür !!
     * @version v 1.0  08.02.2016
     * @return array
     * @throws \PDOException
     */
    public function fillOsbConsultantList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');

            if (isset($params['osb_id']) && $params['osb_id'] != "") {
                $whereSql = " AND a.osb_id = " . intval($params['osb_id']) . " AND  ";
            } else {
                $whereSql = "  AND a.osb_id = 5  ";  // osbId = 5 uretim osb
            }

            $statement = $pdo->prepare("
              SELECT                    
                    a.id, 	
                    CONCAT(u.name,' ', u.surname) AS name,                  
                    a.active ,
                    0 AS state_type                
                FROM sys_osb_consultants  a                
                INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0                 
                INNER JOIN sys_osb osb ON osb.id = a.osb_id                 
                WHERE a.deleted =0 AND a.active = 0
                " . $whereSql . " 
                ORDER BY name              
                               ");
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosundan active kayıtları döndürür !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsultantList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $id = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $id = $params['id'];
            }
            $statement = $pdo->prepare("               
		SELECT                    
                    a.id, 	
                    CONCAT(u.name,' ', u.surname) AS name,                  
                    a.active ,
                    0 AS state_type                
                FROM sys_osb_consultants  a                
                INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0                                 
                WHERE a.deleted =0 AND a.active = 0  
                ORDER BY name                   
                                 ");
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            // $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosunda en az işi olan consultant id sini döndürür.   
     * @version v 1.0 15.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function consultantAssign($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }
            $sql = " 

            SELECT  
                CONCAT(u.name,' ',u.surname) AS name , 
                '" . $params['user_id'] . "' AS value , 
                a.user_id =" . intval($params['user_id']) . " AS control,
                CONCAT(u.name,' ',u.surname, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_osb_consultants  a              
            INNER JOIN info_users_detail u ON u.root_id = a.user_id AND u.active = 0 AND u.deleted = 0                 
            WHERE a.user_id = " . intval($params['user_id']) . "
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
     * @ Gridi doldurmak için sys_osb_consultants tablosundan kayıtları döndürür !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function consultantCompletedJobs($params = array()) {

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
            $sort = "u.name";
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

        $whereNameSQL = '';
        if (isset($params['search_name']) && $params['search_name'] != "") {
            $whereNameSQL = " AND LOWER(a.name) LIKE LOWER('%" . $params['search_name'] . "%') ";
        }

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT a.id, a.s_datetime, a.op_user_id, a.operation_type_id, a.language_id, a.language_code, 
                    a.service_name, a.table_name, a.about_id, a.s_date
                FROM sys_activation_report a
                INNER JOIN sys_operation_types opt ON opt.parent_id = 2 AND a.operation_type_id = opt.id 
                WHERE a.op_user_id IN 
                (SELECT DISTINCT id FROM info_users WHERE role_id = 2)                              
                WHERE a.deleted = 0  
                " . $whereNameSQL . "
                ORDER BY    " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";
            $statement = $pdo->prepare($sql);
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
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_osb_consultants tablosundan kayıtları döndürür !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsPendingFirmProfile($params = array()) {
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
            $sort = "fp.s_date ASC, fp.c_date";
        }

        if (isset($params['order']) && $params['order'] != "") {
            $order = trim($params['order']);
            $orderArr = explode(",", $order);
            if (count($orderArr) === 1)
                $order = trim($params['order']);
        } else {
            $order = "ASC";
        }

        // sql query dynamic for filter operations
        $sorguStr = null;
        if (isset($params['filterRules'])) {
            $filterRules = trim($params['filterRules']);
            //print_r(json_decode($filterRules));
            $jsonFilter = json_decode($filterRules, true);
            //print_r($jsonFilter[0]->field);
            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'username':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=' AND fpu.username' . $sorguExpression . ' ';
                            break;
                        case 'company_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=' AND fp.firm_name' . $sorguExpression . ' ';

                            break;
                        case 's_date':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.='AND  TO_CHAR(fp.s_date, \'DD/MM/YYYY\')' . $sorguExpression . ' ';

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
        //if($sorguStr!="") $sorguStr = "WHERE ".$sorguStr;          

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $sql = "
                SELECT 
                    fp.id AS id, 
                    fpu.s_date, 
                    fp.firm_name AS company_name, 
                    fpu.username AS username 
                FROM sys_osb_consultants a   
                LEFT JOIN info_firm_profile fp ON fp.consultant_id = a.user_id AND fp.deleted = 0 
                INNER JOIN info_users fpu ON fpu.id = fp.op_user_id    
                WHERE fpu.auth_allow_id = 0 AND 
                     a.user_id =" . intval($opUserIdValue) . "                                                
                " . $sorguStr . "
                ORDER BY    " . $sort . " "
                        . "" . $order . " "
                        . "LIMIT " . $pdo->quote($limit) . " "
                        . "OFFSET " . $pdo->quote($offset) . " ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();

                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
         //       $pdo->commit();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**  
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_osb_consultants tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsPendingFirmProfilertc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $sorguStr = " WHERE fpu.auth_allow_id = 0 AND a.user_id = " . intval($opUserIdValue);

                // sql query dynamic for filter operations
                //$sorguStr = null;
                if (isset($params['filterRules'])) {
                    $filterRules = trim($params['filterRules']);
                    //print_r(json_decode($filterRules));
                    $jsonFilter = json_decode($filterRules, true);
                    //print_r($jsonFilter[0]->field);
                    $sorguExpression = null;
                    foreach ($jsonFilter as $std) {
                        if ($std['value'] != null) {
                            switch (trim($std['field'])) {
                                case 'username':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                    $sorguStr.=' AND fpu.username' . $sorguExpression . ' ';
                                    break;
                                case 'company_name':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.=' AND fp.firm_name' . $sorguExpression . ' ';

                                    break;
                                case 's_date':
                                    $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                    $sorguStr.='AND TO_CHAR(fp.s_date, \'DD/MM/YYYY\')' . $sorguExpression . ' ';

                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                } else {
                 //   $sorguStr = null;
                    $filterRules = "";
                }

                $sorguStr = rtrim($sorguStr, "AND ");
                $sql = "
               SELECT  
                    COUNT(a.id) AS COUNT                           		  
		FROM sys_osb_consultants a                                
		LEFT JOIN info_firm_profile fp ON fp.consultant_id = a.user_id AND fp.deleted = 0 
                INNER JOIN info_users fpu ON fpu.id = fp.op_user_id

                " . $sorguStr . "                

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
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
                //  $pdo->commit();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * get consultant confirmation process details
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getConsConfirmationProcessDetails($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                //$opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                //$whereSQL = " WHERE a.user_id = " . intval($opUserIdValue);

                $sql = " 
                    
                SELECT  
                        a.id,                                                         
                        a.username,     
                        ff.firm_name, 
                        ff.sgk_sicil_no ,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_code,  
                       (
                        SELECT Concat (ax.address1,ax.address2, 
				    'Posta Kodu = ',ax.postal_code,                  
				    cox.name ,' ',
				    ctx.name ,' ',
				    box.name ,' ',
				    ax.city_name  )                    
				FROM info_users_addresses  ax                                                  									
				LEFT JOIN sys_countrys cox on cox.id = ax.country_id AND cox.deleted = 0 AND cox.active = 0 AND cox.language_code = ax.language_code                               
				LEFT JOIN sys_city ctx on ctx.id = ax.city_id AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_code = ax.language_code                               
				LEFT JOIN sys_borough box on box.id = ax.borough_id AND box.deleted = 0 AND box.active = 0 AND box.language_code = ax.language_code                 
				WHERE ax.deleted =0 AND ax.active =0 AND ax.address_type_id = 1 
				AND ax.user_id  =  a.id limit 1 
                        )                  
                        As iletisimadresi,
			(
                        SELECT Concat (ax.address1,ax.address2, 
				    'Posta Kodu = ',ax.postal_code,                  
				    cox.name ,' ',
				    ctx.name ,' ',
				    box.name ,' ',
				    ax.city_name   )                    
				FROM info_users_addresses  ax                                                  									
				LEFT JOIN sys_countrys cox on cox.id = ax.country_id AND cox.deleted = 0 AND cox.active = 0 AND cox.language_code = ax.language_code                               
				LEFT JOIN sys_city ctx on ctx.id = ax.city_id AND ctx.deleted = 0 AND ctx.active = 0 AND ctx.language_code = ax.language_code                               
				LEFT JOIN sys_borough box on box.id = ax.borough_id AND box.deleted = 0 AND box.active = 0 AND box.language_code = ax.language_code                 
				WHERE ax.deleted =0 AND ax.active =0 AND ax.address_type_id = 2 
				AND ax.user_id  =  a.id limit 1 
                        ) AS faturaadresi,
                        
                        (SELECT  
			        ay.communications_no
				FROM info_users_communications ay       				
				WHERE 
				    ay.active =0 AND ay.deleted = 0 AND ay.default_communication_id = 1 AND                   
				    ay.user_id =   a.id limit 1 
			 ) As irtibattel,

			 (SELECT  
			        ay.communications_no
				FROM info_users_communications ay       				
				WHERE 
				    ay.active =0 AND ay.deleted = 0 AND ay.communications_type_id = 2 AND                   
				    ay.user_id =   a.id limit 1 
			 ) As irtibatcep,
			a.s_date                        
                    FROM info_users a                  
                    LEFT JOIN info_firm_profile ff ON ff.op_user_id = a.id AND ff.active = 0 AND ff.deleted =0 
                    INNER JOIN sys_language l ON l.id =  a.language_id AND l.language_id = 647    
                    WHERE ff.id =:profile_id           

                    ";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':profile_id', $params['profile_id'], \PDO::PARAM_INT);
                //   echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
                //  $pdo->commit();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * parametre olarak gelen array deki 'id' li kaydın update ini yapar  !!
     * @author Okan CIRAN
     * @version v 1.0  10.02.2016     
     * @param array | null $args
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function setUserDetailOperationsTypeCons($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];

                $addSql = " op_user_id, ";
                $addSqlValue = intval($opUserIdValue) . ", ";

                if (isset($params['operation_type_id'])) {
                    $addSql .= " operation_type_id, ";
                    $addSqlValue .= intval($params['operation_type_id']) . ", ";
                }
                if (isset($params['cons_allow_id'])) {
                    $addSql .= " cons_allow_id, ";
                    $addSqlValue .= intval($params['cons_allow_id']) . ", ";
                }
                if (isset($params['role_id'])) {
                    $addSql .= " role_id, ";
                    $addSqlValue .= intval($params['role_id']) . ", ";
                }

                /*
                 *  parametre olarak gelen array deki 'id' li kaydın, info_users_details tablosundaki 
                 * active = 0 ve deleted = 0 olan kaydın active alanını 1 yapar  !!
                 */
                InfoUsers::setUserDetailsDisables(array('id' => $params['id']));

                $sql = " 
                    INSERT INTO info_users_detail(
                           profile_public, 
                           f_check,
                           " . $addSql . "                      
                           name, 
                           surname,                            
                           act_parent_id,                            
                           language_code, 
                           root_id,                            
                           language_id, 
                           password,
                           auth_allow_id,
                           auth_email
                            ) 
                           SELECT 
                                profile_public, 
                                f_check, 
                                " . $addSqlValue . "
                                name, 
                                surname,                            
                                act_parent_id,                            
                                language_code, 
                                root_id,                            
                                language_id, 
                                password,
                                auth_allow_id,
                                auth_email
                            FROM info_users_detail 
                            WHERE root_id  =" . intval($params['id']) . " 
                                AND active =0 AND deleted =0

 
                    ";
                $statementActInsert = $pdo->prepare($sql);
                //   echo debugPDO($sql, $params);
                $insertAct = $statementActInsert->execute();
                $insertID = $pdo->lastInsertId('info_users_detail_id_seq');
                $errorInfo = $statementActInsert->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "newId" => $insertID);
            } else {
                $errorInfo = '23502';  /// 23502 user_id not_null_violation
                $pdo->rollback();
                $result = $kontrol;
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ Gridi doldurmak için consultant ların yaptığı operasyon kayıtlarını döndürür !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsOpDetailedReviewForUser($params = array()) {
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
            $sort = "fp.s_date ASC, fp.c_date";
        }

        if (isset($params['order']) && $params['order'] != "") {
            $order = trim($params['order']);
            $orderArr = explode(",", $order);
            if (count($orderArr) === 1)
                $order = trim($params['order']);
        } else {
            $order = "ASC";
        }

        // sql query dynamic for filter operations
        $sorguStr = null;
        if (isset($params['filterRules'])) {
            $filterRules = trim($params['filterRules']);
            //print_r(json_decode($filterRules));
            $jsonFilter = json_decode($filterRules, true);
            //print_r($jsonFilter[0]->field);
            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'operation_type_id':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=' AND fpu.username' . $sorguExpression . ' ';
                            break;
                        case 'company_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=' AND fp.firm_name' . $sorguExpression . ' ';

                            break;
                        case 's_date':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.='AND TO_CHAR(fp.s_date, \'DD/MM/YYYY\')' . $sorguExpression . ' ';

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
        //if($sorguStr!="") $sorguStr = "WHERE ".$sorguStr;          

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $sql = "
                SELECT 
                    fp.id AS id, 
                    fpu.s_date, 
                    fp.firm_name AS company_name, 
                    fpu.username AS username 
                FROM sys_osb_consultants a   
                LEFT JOIN info_firm_profile fp ON fp.consultant_id = a.user_id AND fp.deleted = 0 
                INNER JOIN info_users fpu ON fpu.id = fp.op_user_id    
                WHERE fpu.auth_allow_id = 0 AND 
                
                     a.user_id =" . intval($opUserIdValue) . "                                                
                " . $sorguStr . "
                ORDER BY    " . $sort . " "
                        . "" . $order . " "
                        . "LIMIT " . $pdo->quote($limit) . " "
                        . "OFFSET " . $pdo->quote($offset) . " ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = $statement->errorInfo();

                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
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
     * info_users tablosunda üzerinde en az iş olan consultant id sini döndürür    !!
     * yeni kayıt edilen consultant varsa onu da işleme alır.
     * @version v 1.0  
     * @since 12.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function getConsultantIdForUsers($params = array()) {
        try {

            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "  ";

            if ((isset($params['category_id']) && $params['category_id'] != "")) {
                $addSql .= " AND cons.category_id = " . intval($params['category_id'])  ;
            }
            $addSql .= " AND cons.category_id = 0 "; 
            
            $sql = "               
                SELECT consultant_id, 1=1 AS control FROM ( 
                    SELECT 
                        cons.user_id AS consultant_id , 
                        count(iu.id) AS adet , 
                        MAX(iu.s_date) 
                    FROM sys_osb_consultants cons
                    LEFT JOIN info_users iu ON iu.consultant_id = cons.user_id AND iu.cons_allow_id = 0  
                    WHERE cons.active = 0 AND cons.deleted =0 AND cons.osb_id = 5 
                    GROUP BY cons.user_id
                    ORDER BY adet, max  
                    LIMIT 1 
                ) AS tempx                    
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
     * info_firm_profile tablosunda üzerinde en az iş olan consultant id sini döndürür    !!
     * yeni kayıt edilen consultant varsa onu da işleme alır.
     * @version v 1.0  
     * @since 12.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function getConsultantIdForCompany($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = " AND cons.category_id = 0 ";

            if ((isset($params['category_id']) && $params['category_id'] != "")) {
                $addSql = " AND cons.category_id = " . intval($params['category_id'])  ;
            }            
               $sql = "              
                SELECT consultant_id, 1=1 AS control FROM ( 
                    SELECT 
                        cons.user_id AS consultant_id , 
                        count(ifp.id) AS adet , 
                        MAX(ifp.s_date) 
                    FROM sys_osb_consultants cons
                    LEFT JOIN info_firm_profile ifp ON ifp.consultant_id = cons.user_id AND ifp.cons_allow_id = 0  
                    WHERE cons.active = 0 AND cons.deleted =0 AND cons.osb_id = 5 
                    " . $addSql . "
                    GROUP BY cons.user_id
                    ORDER BY adet, max  
                    LIMIT 1 
                ) AS tempx                    
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
     * @ Gridi doldurmak için consultant ların yaptığı operasyon kayıtlarını döndürür !!
     * @version v 1.0  08.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getAllFirmCons($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $getFirm = InfoFirmProfile :: getCheckIsThisFirmRegisteredUser(array('cpk' => $params['cpk'], 'op_user_id' => $opUserIdValue));
                if (\Utill\Dal\Helper::haveRecord($getFirm)) {
                    $getFirmIdValue = $getFirm ['resultSet'][0]['firm_id'];
                    
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }       
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                         $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                    }                     
                    
                    $sql = "                
                SELECT DISTINCT
                     a.act_parent_id AS firm_id,
                    u.id AS consultant_id,
                    iud.name, 
                    iud.surname,
                    iud.auth_email,                
                    CASE COALESCE(NULLIF(TRIM(iud.picture), ''),'-') 
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/' ,'image_not_found.png')
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.members_folder,'/' ,TRIM(iud.picture)) END AS cons_picture,
                    u.id = (SELECT consultant_id from  info_firm_profile azx where azx.act_parent_id = 94 AND azx.consultant_id =u.id limit 1 ) AS firm_consultant,
                    COALESCE(NULLIF(ifux.title, ''), ifux.title_eng) AS title,
                    ifu.title_eng,
                    COALESCE(NULLIF(soc.title, ''), soc.title_eng) AS osb_title,
                    soc.title_eng osb_title_eng,
                    ifk.network_key, 
                    (SELECT iucz.communications_no FROM info_users_communications iucz WHERE iucz.user_id = u.id AND iucz.language_parent_id =0 AND iucz.cons_allow_id = 2 limit 1) AS phone 
                FROM info_firm_profile a   
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0 
                INNER JOIN info_firm_keys ifk ON ifk.firm_id = a.act_parent_id 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND l.deleted =0 AND l.active =0 
		LEFT JOIN info_firm_profile ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.active =0 AND ax.deleted =0
                INNER JOIN info_users u ON u.role_id in (1,2,6) AND u.deleted =0  
                INNER JOIN sys_osb_consultants soc ON soc.active = 0 AND soc.deleted =0 AND soc.user_id = u.id
                INNER JOIN info_users_detail iud ON iud.root_id = u.id AND iud.cons_allow_id = 2
                INNER JOIN info_users_communications iuc ON iuc.user_id = u.id AND iuc.cons_allow_id = 2
                INNER JOIN info_firm_users ifu ON ifu.user_id = u.id AND ifu.firm_id = 1 AND ifu.cons_allow_id = 2
                LEFT JOIN info_firm_users ifux ON (ifux.id = ifu.id OR ifux.language_parent_id = ifu.id) AND ifu.cons_allow_id = 2 AND ifux.language_id = lx.id
                INNER JOIN sys_specific_definitions sd5 ON sd5.main_group = 5 AND sd5.first_group = iuc.communications_type_id AND sd5.deleted =0 AND sd5.active =0 AND l.id = sd5.language_id
		LEFT JOIN sys_specific_definitions sd5x ON (sd5x.id =sd5.id OR sd5x.language_parent_id = sd5.id) AND sd5x.deleted =0 AND sd5x.active =0 AND lx.id = sd5x.language_id
                WHERE
                    a.act_parent_id = " . intval($getFirmIdValue) . " AND                          
                        u.id IN (
                            SELECT DISTINCT consultant_id FROM info_firm_profile WHERE act_parent_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_academics WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_socialmedia WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_address WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_arge WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_building WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_certificate WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_clusters where firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_building WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_commercial_activity WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_communications WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_customers WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_energy_efficiency WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_fair WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_financial WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_green_energy_plant WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_language_info WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_machine_tool WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_machine_tool_work_schedule WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_membership WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_other_devices WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_personnel_info WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_potential WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_process WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_process_allocation WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_products WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_products_services WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_raw_materials WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_quality WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_recycling_plant WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_references WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_sectoral WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_university WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_user_desc_for_company WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_users WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_verbal WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_waste WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_work_safety WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_workflow_definition WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_workflow_materials WHERE firm_id = " . intval($getFirmIdValue) . " 
                            UNION
                            SELECT DISTINCT consultant_id FROM info_firm_workflow_process WHERE firm_id = " . intval($getFirmIdValue) . " 
                        )
                        ORDER BY firm_consultant DESC,iud.name,iud.surname 
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
                    $errorInfo = '23502';   // 23502  not_null_violation
                    $errorInfoColumn = 'npk';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
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
     * @ consultant bilgilerini grid formatında döndürür !!
     * filterRules aktif 
     * @version v 1.0  09.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOsbConsultantListGrid($params = array()) {
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
                $sort = " name,surname,username";
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
                            case 'surname':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND surname" . $sorguExpression . ' ';

                                break;
                            case 'username':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND username" . $sorguExpression . ' ';

                                break;     
                            case 'preferred_language_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND preferred_language_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'role_name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name_tr" . $sorguExpression . ' ';
                            
                                break;  
                            case 'osb_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND osb_name" . $sorguExpression . ' ';
                            
                                break; 
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';
                            
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
                    username ,
                    name ,
                    surname,
                    preferred_language,
                    preferred_language_name,
                    preferred_language_json,
                    role_id,
                    role_name,
                    role_name_tr,
                    osb_id,
                    osb_name,
                    active, 
                    state_active,
                    op_user_id,
                    op_user_name,
                    deleted
                FROM (
                   SELECT 
                        a.id, 
			ucons.username ,
                        iud.name ,
                        iud.surname,
                        a.language_id AS preferred_language,
                        l.language_local AS preferred_language_name,
                        a.preferred_language_json,
                        ucons.role_id as role_id,
                        sar.name AS role_name,
                        sar.name_tr AS role_name_tr,
			a.osb_id,
			COALESCE(NULLIF(osb.name, ''), osb.name_eng) AS osb_name,
                        a.active, 
                        sd16.description AS state_active,
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.deleted
                    FROM sys_osb_consultants a 
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                                         
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN info_users u ON u.id = a.op_user_id 
		    INNER JOIN info_users ucons ON ucons.id = a.user_id 
		    INNER JOIN info_users_detail iud ON iud.root_id = a.user_id  AND iud.active=0 AND iud.deleted=0 
		    INNER JOIN sys_acl_roles sar ON sar.id = ucons.role_id AND sar.active=0 AND sar.deleted=0
		    LEFT JOIN sys_osb osb ON osb.id = a.osb_id AND osb.active=0 AND osb.deleted =0 
                    WHERE a.deleted =0 
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
     * @ grid için consultant bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  09.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOsbConsultantListGridRtc($params = array()) {
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
                            case 'surname':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND surname" . $sorguExpression . ' ';

                                break;
                            case 'username':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND username" . $sorguExpression . ' ';

                                break;     
                            case 'preferred_language_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND preferred_language_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'role_name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name_tr" . $sorguExpression . ' ';
                            
                                break;  
                            case 'osb_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND osb_name" . $sorguExpression . ' ';
                            
                                break; 
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';
                            
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
                    username ,
                    name ,
                    surname,
                    preferred_language,
                    preferred_language_name,
                    preferred_language_json,
                    role_id,
                    role_name,
                    role_name_tr,
                    osb_id,
                    osb_name,
                    active, 
                    state_active,
                    op_user_id,
                    op_user_name,
                    deleted
                FROM (
                   SELECT 
                        a.id, 
			ucons.username ,
                        iud.name ,
                        iud.surname,
                        a.language_id AS preferred_language,
                        l.language_local AS preferred_language_name,
                        a.preferred_language_json,
                        ucons.role_id as role_id,
                        sar.name AS role_name,
                        sar.name_tr AS role_name_tr,
			a.osb_id,
			COALESCE(NULLIF(osb.name, ''), osb.name_eng) AS osb_name,
                        a.active, 
                        sd16.description AS state_active,
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.deleted
                    FROM sys_osb_consultants a 
                    INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                                         
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN info_users u ON u.id = a.op_user_id 
		    INNER JOIN info_users ucons ON ucons.id = a.user_id 
		    INNER JOIN info_users_detail iud ON iud.root_id = a.user_id  AND iud.active=0 AND iud.deleted=0 
		    INNER JOIN sys_acl_roles sar ON sar.id = ucons.role_id AND sar.active=0 AND sar.deleted=0
		    LEFT JOIN sys_osb osb ON osb.id = a.osb_id AND osb.active=0 AND osb.deleted =0 
                    WHERE a.deleted =0 
                    ) AS xtable WHERE deleted =0 
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

    /**
     * @author Okan CIRAN
     * @ sys_osb_consultants tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  13.06.2016
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
                UPDATE sys_osb_consultants
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_osb_consultants
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
                
                
                 $xc = $this->makeActiveOrPassiveInfoUsers(array('id' => $params['id'],                     
                     'op_user_id' => $opUserIdValue,
                 ));

                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);
                
                
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
     * @ sys_osb_consultants tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  13.06.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeActiveOrPassiveInfoUsers($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                            
            if (isset($params['id']) && $params['id'] != "") {
                $sql = "                 
                UPDATE info_users
                SET active = (  SELECT   
                                CASE xx.active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM info_users xx
                                WHERE xx.id = info_users.id
                ),
                op_user_id = " . intval($params['op_user_id']) . " 
                WHERE 
                    id = (
                    SELECT a.user_id FROM sys_osb_consultants a
                    WHERE a.id = " . intval($params['id']) . " )"
                ;
                $statement = $pdo->prepare($sql);
              //   echo debugPDO($sql, $params);
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
            }                            
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_firm_profile tablosunda üzerinde en az iş olan consultant id sini döndürür    !!
     * yeni kayıt edilen consultant varsa onu da işleme alır.
     * @version v 1.0  
     * @since 27.05.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function getConsultantIdForTableName($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "  AND sos.redirectmap IS NULL ";

            if ((isset($params['redirectmap']) && $params['redirectmap'] != "")) {
                $addSql = " AND sos.redirectmap = '" . $params['redirectmap']."'";                        
            }        
            
            $tableName = 'info_firm_profile';
            if ((isset($params['table_name']) && $params['table_name'] != "")) {
                $tableName = $params['table_name'];
                $addSql = " AND sos.table_name = '" . $tableName."'";  
                
            }
            if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                $addSql .= " AND sos.id = " . intval($params['operation_type_id']);                        
            }
                        
            $languageIdValue = 647;
            if ((isset($params['language_id']) && $params['language_id'] != "")) {                        
                    $languageIdValue = $params['language_id'];                        
            }

               $sql = "              
                SELECT consultant_id, 1=1 AS control FROM ( 
                    SELECT 
                        cons.user_id AS consultant_id, 
                        count(ifp.id) AS adet, 
                        MAX(ifp.s_date) 
                    FROM sys_osb_consultants cons
                    INNER JOIN sys_operation_types sos ON sos.active =0 AND sos.deleted =0 ".$addSql." 
                    LEFT JOIN ".$tableName." ifp ON ifp.consultant_id = cons.user_id AND ifp.cons_allow_id = 0  
                    WHERE cons.active = 0 AND cons.deleted =0 AND cons.osb_id = 5
                        AND sos.category_id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_array_elements(category_json))
                        AND ".intval($languageIdValue)." IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_array_elements(preferred_language_json))
                    GROUP BY cons.user_id
                    ORDER BY adet, max  
                    LIMIT 1 
                ) AS tempx
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
     * operation id ile ilişkili tablodan üzerinde en az iş olan consultant id sini döndürür    !!
     * yeni kayıt edilen consultant varsa onu da işleme alır.
     * @version v 1.0  
     * @since 15.10.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function getBeAssignedConsultant($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $operationTypeId = 0;
            if ((isset($params['operation_type_id']) && $params['operation_type_id'] != "")) {
                $operationTypeId = intval($params['operation_type_id']);
            }
            $languageIdValue = 647;
            if ((isset($params['language_id']) && $params['language_id'] != "")) {
                $languageIdValue = $params['language_id'];
            }
            $tableName = 'info_firm_profile';
            $getOperationTableNameParams = array('operation_type_id' => $operationTypeId,);            
            $getOperationTableName = $this->slimApp-> getBLLManager()->get('operationTableNameBLL');  
            $getOperationTableNameArray = $getOperationTableName->getOperationTableName($getOperationTableNameParams);
            if (!\Utill\Dal\Helper::haveRecord($getOperationTableNameArray)) {
                $tableName = $getOperationTableNameArray ['resultSet'][0]['table_name'];
            }

            $sql = "
                SELECT consultant_id, 1=1 AS control FROM ( 
                    SELECT 
                        cons.user_id AS consultant_id,   
                         count(ifp.id) AS adet, 
                         MAX(ifp.s_date) 
                    FROM sys_osb_consultants cons
                    INNER JOIN info_users iu ON iu.id = cons.user_id AND iu.active =0 and iu.deleted =0
                    INNER JOIN sys_acl_roles sar ON sar.id = iu.role_id AND sar.active =0 AND sar.deleted =0  
                    INNER JOIN sys_assign_definition_roles sadr ON sadr.role_id = iu.role_id AND sadr.active =0 AND sadr.deleted =0  
		    INNER JOIN sys_operation_types_rrp sos ON sos.active =0 AND sos.deleted =0 AND sos.assign_definition_id = sadr.assign_definition_id 
		    LEFT JOIN " . $tableName . " ifp ON ifp.consultant_id = cons.user_id AND ifp.cons_allow_id = 0  and ifp.active =0 and ifp.deleted =0
                    WHERE cons.active = 0 AND 
			cons.deleted =0 AND 
                        cons.user_id > 0 AND 
                        " . intval($languageIdValue) . " IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_array_elements(preferred_language_json))
                    GROUP BY cons.user_id
                    ORDER BY adet, max  
                    LIMIT 1 
                ) AS tempx
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
