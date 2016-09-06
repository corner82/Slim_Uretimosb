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
class SysOsbPerson extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_osb_person tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  29.08.2016
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
                $statement = $pdo->prepare(" 
                UPDATE sys_osb_person
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
                            
                $xc = $this->deleteOsbPerson(array('id' => $params['id'],                     
                     'op_user_id' => $opUserIdValue,
                 ));

                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);
                
                $xc = $this->deleteOsbPersonDetail(array('id' => $params['id'],                     
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
     * @version v 1.0  29.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function deleteOsbPerson($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE info_users
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "
                WHERE 
                    id = (
                    SELECT a.user_id FROM sys_osb_person a
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
     * @version v 1.0  29.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function deleteOsbPersonDetail($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE info_users_detail
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "
                WHERE active=0 AND deleted =0 AND 
                    root_id = (
                    SELECT a.user_id FROM sys_osb_person a
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
     * @ sys_osb_person tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  29.08.2016  
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
                FROM sys_osb_person  a
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
     * @ sys_osb_person tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  29.08.2016
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
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                            }
                    }

                    $sql = "
                INSERT INTO sys_osb_person(
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
                    $insertID = $pdo->lastInsertId('sys_osb_person_id_seq');
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
     * @ sys_osb_person tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 29.08.2016
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
                a.user_id =" . intval($params['user_id']) . " AS control,
                CONCAT(u.name,' ',u.surname, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_osb_person  a              
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
     * sys_osb_person tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  29.08.2016
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
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

                    $sql = "
                UPDATE sys_osb_person
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
     * @ Gridi doldurmak için sys_osb_person tablosundan kayıtları döndürür !!
     * @version v 1.0  29.08.2016
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
                FROM sys_osb_person  a
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
     * @ Gridi doldurmak için sys_osb_person tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  29.08.2016
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
                    (SELECT COUNT(ax.id) FROM sys_osb_person ax  			
			INNER JOIN sys_specific_definitions sdx ON sdx.main_group = 15 AND sdx.first_group= ax.deleted AND sdx.language_code = 'tr' AND sdx.deleted = 0 AND sdx.active = 0
			INNER JOIN sys_specific_definitions sd1x ON sd1x.main_group = 16 AND sd1x.first_group= ax.active AND sd1x.language_code = 'tr' AND sd1x.deleted = 0 AND sd1x.active = 0                             
			INNER JOIN info_users_detail ux ON ux.root_id = ax.user_id AND ux.active = 0 AND ux.deleted = 0 
			INNER JOIN info_users u1x ON u1x.id = ax.op_user_id 
                     " . $whereSQL1 . " ) AS undeleted_count, 
                    (SELECT COUNT(ay.id) FROM sys_osb_person ay
			INNER JOIN sys_specific_definitions sdy ON sdy.main_group = 15 AND sdy.first_group= ay.deleted AND sdy.language_code = 'tr' AND sdy.deleted = 0 AND sdy.active = 0
			INNER JOIN sys_specific_definitions sd1y ON sd1y.main_group = 16 AND sd1y.first_group= ay.active AND sd1y.language_code = 'tr' AND sd1y.deleted = 0 AND sd1y.active = 0                             
			INNER JOIN info_users_detail uy ON uy.root_id = ay.user_id AND uy.active = 0 AND uy.deleted = 0 
			INNER JOIN info_users u1y ON u1y.id = ay.op_user_id 			
                      " . $whereSQL2 . ") AS deleted_count                        
                FROM sys_osb_person  a
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
     * @ consultant bilgilerini grid formatında döndürür !!
     * filterRules aktif 
     * @version v 1.0  29.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUrgePersonListGrid($params = array()) {
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
                $sort = " name,surname,auth_email";
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
                            case 'auth_email':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND auth_email" . $sorguExpression . ' ';

                                break;     
                            case 'osb_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND osb_name" . $sorguExpression . ' ';
                            
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
                    user_id ,
                    auth_email,
                    name ,
                    surname,  
                    osb_id,
                    osb_name,
                    osb_cluster_id,
                    cluster_name,
                    role_id,
                    role_name,
                    role_name_tr,
                    active, 
                    state_active,
                    op_user_id,
                    op_user_name,
                    deleted
                FROM (
                   SELECT 
                        a.id, 
			a.user_id ,
			iud.auth_email,
                        iud.name ,
                        iud.surname,  
                        osb.id AS osb_id,
			COALESCE(NULLIF(osb.name, ''), osb.name_eng) AS osb_name,
			a.osb_cluster_id,
			soc.name AS cluster_name,
			urge.role_id as role_id,
                        sar.name AS role_name,
                        sar.name_tr AS role_name_tr,
                        a.active, 
                        sd16.description AS state_active,
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.deleted
                    FROM sys_osb_person a 
                    INNER JOIN info_users urge ON urge.id = a.user_id 
                    INNER JOIN sys_acl_roles sar ON sar.id = urge.role_id AND sar.active=0 AND sar.deleted= 0
                    INNER JOIN info_users_detail iud ON iud.root_id = urge.id AND iud.active=0 AND iud.deleted= 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN info_users u ON u.id = a.op_user_id
	            LEFT JOIN sys_osb_clusters soc ON soc.id = a.osb_cluster_id AND soc.active=0 AND soc.deleted =0 
		    LEFT JOIN sys_osb osb ON osb.id = soc.osb_id AND osb.active=0 AND osb.deleted =0 
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
     * @ grid için consultant bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  29.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillUrgePersonListGridRtc($params = array()) {
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
                            case 'auth_email':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND auth_email" . $sorguExpression . ' ';

                                break;     
                            case 'osb_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND osb_name" . $sorguExpression . ' ';
                            
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
                        user_id ,
                        auth_email,
                        name ,
                        surname,  
                        osb_id,
                        osb_name,
                        osb_cluster_id,
                        cluster_name,
                        role_id,
                        role_name,
                        role_name_tr,
                        active, 
                        state_active,
                        op_user_id,
                        op_user_name,
                        deleted
                    FROM (
                    SELECT 
                        a.id, 
			a.user_id ,
			iud.auth_email,
                        iud.name ,
                        iud.surname,  
                        osb.id AS osb_id,
			COALESCE(NULLIF(osb.name, ''), osb.name_eng) AS osb_name,
			a.osb_cluster_id,
			soc.name AS cluster_name,
			urge.role_id as role_id,
                        sar.name AS role_name,
                        sar.name_tr AS role_name_tr,
                        a.active, 
                        sd16.description AS state_active,
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.deleted
                    FROM sys_osb_person a 
                    INNER JOIN info_users urge ON urge.id = a.user_id 
                    INNER JOIN sys_acl_roles sar ON sar.id = urge.role_id AND sar.active=0 AND sar.deleted= 0
                    INNER JOIN info_users_detail iud ON iud.root_id = urge.id AND iud.active=0 AND iud.deleted= 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN info_users u ON u.id = a.op_user_id
	            LEFT JOIN sys_osb_clusters soc ON soc.id = a.osb_cluster_id AND soc.active=0 AND soc.deleted =0 
		    LEFT JOIN sys_osb osb ON osb.id = soc.osb_id AND osb.active=0 AND osb.deleted =0 
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
     * @ sys_osb_person tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  29.08.2016
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
                UPDATE sys_osb_person
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_osb_person
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
     * @ sys_osb_person tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  29.08.2016
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
                    SELECT a.user_id FROM sys_osb_person a
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

}
