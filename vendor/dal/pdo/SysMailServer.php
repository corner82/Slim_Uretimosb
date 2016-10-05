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
 * @since 24.05.2016
 */
class SysMailServer extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_mail_server tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  24.05.2016
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
                UPDATE sys_mail_server
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($opUserIdValue) . "     
                WHERE id = ".  intval($params['id'])  );            
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
     * @ sys_mail_server tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  24.05.2016  
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
                    a.host_name,
                    a.host_address, 
                    a.smtp_auth, 
                    a.smtp_debug, 
                    a.smtp_secure, 
                    a.port, 
                    a.username, 
                    a.psswrd, 
                    a.charset, 
                    a.headers, 
                    a.body_road,                      
                    a.deleted,
                    sd15.description  AS state_deleted,
                    a.active,
                    sd16.description  AS state_active,			
                    a.op_user_id,
                    u.username AS op_user_name
                FROM sys_mail_server a                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id  
                ORDDER BY a.host_name
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
     * @ sys_mail_server tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  24.05.2016
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
                 
                $kontrol = $this->haveRecords(array( 'host_address' => $params['host_address']));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {

                    $sql = "
                INSERT INTO sys_mail_server(
                        host_name, 
                        host_address, 
                        smtp_auth, 
                        smtp_debug, 
                        smtp_secure,
                        port, 
                        username, 
                        psswrd, 
                        charset, 
                        headers, 
                        body_road,                         
                        op_user_id
                         )
                VALUES (
                        :host_name, 
                        :host_address, 
                        :smtp_auth, 
                        :smtp_debug, 
                        :smtp_secure,
                        :port, 
                        :username, 
                        :psswrd, 
                        :charset, 
                        :headers, 
                        :body_road,                         
                        :op_user_id
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':host_name', $params['host_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':host_address', $params['host_address'], \PDO::PARAM_STR);
                    $statement->bindValue(':smtp_auth', $params['smtp_auth'], \PDO::PARAM_BOOL);
                    $statement->bindValue(':smtp_debug', $params['smtp_debug'], \PDO::PARAM_INT);
                    $statement->bindValue(':smtp_secure', $params['smtp_secure'], \PDO::PARAM_STR);
                    $statement->bindValue(':port', $params['port'], \PDO::PARAM_INT);
                    $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                    $statement->bindValue(':psswrd', $params['psswrd'], \PDO::PARAM_STR);
                    $statement->bindValue(':charset', $params['charset'], \PDO::PARAM_STR);
                    $statement->bindValue(':headers', $params['headers'], \PDO::PARAM_STR);
                    $statement->bindValue(':body_road', $params['body_road'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_mail_server_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'host_address';
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
     * @ sys_mail_server tablosunda property_name daha önce kaydedilmiş mi ?  
     * @version v 1.0  24.05.2016  
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
                a.host_address ,
                '" . $params['host_address'] . "' AS value, 
                LOWER(a.host_address) = LOWER(TRIM('" . $params['host_address'] . "')) AS control,
                CONCAT(a.host_address, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_mail_server a                          
            WHERE 
                LOWER(TRIM(a.host_address)) = LOWER(TRIM('" . $params['host_address'] . "')) AND                
                " . $addSql . " 
               AND a.deleted =0    
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
     * sys_mail_server tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  24.05.2016  
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
                    $sql = "
                    UPDATE sys_mail_server
                    SET 
                        host_name =:host_name, 
                        host_address =:host_address, 
                        smtp_auth =:smtp_auth, 
                        smtp_debug =:smtp_debug, 
                        smtp_secure =:smtp_secure,
                        port =:port, 
                        username =:username, 
                        psswrd =:psswrd, 
                        charset =:charset, 
                        headers =:headers, 
                        body_road = :body_road,                         
                        op_user_id =:op_user_id                        
                    WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':host_name', $params['host_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':host_address', $params['host_address'], \PDO::PARAM_STR);
                    $statement->bindValue(':smtp_auth', $params['smtp_auth'], \PDO::PARAM_BOOL);
                    $statement->bindValue(':smtp_debug', $params['smtp_debug'], \PDO::PARAM_INT);
                    $statement->bindValue(':smtp_secure', $params['smtp_secure'], \PDO::PARAM_STR);
                    $statement->bindValue(':port', $params['port'], \PDO::PARAM_INT);
                    $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
                    $statement->bindValue(':psswrd', $params['psswrd'], \PDO::PARAM_STR);
                    $statement->bindValue(':charset', $params['charset'], \PDO::PARAM_STR);
                    $statement->bindValue(':headers', $params['headers'], \PDO::PARAM_STR);
                    $statement->bindValue(':body_road', $params['body_road'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    //echo debugPDO($sql, $params);
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
                    $errorInfoColumn = 'host_address';
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
     * @ Gridi doldurmak için sys_mail_server tablosundan kayıtları döndürür !!
     * @version v 1.0  24.05.2016
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
            $sort = " a.host_name ";
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
                    a.host_name,
                    a.host_address, 
                    a.smtp_auth, 
                    a.smtp_debug, 
                    a.smtp_secure, 
                    a.port, 
                    a.username, 
                    a.psswrd, 
                    a.charset, 
                    a.headers, 
                    a.body_road,                      
                    a.deleted,
                    sd15.description  AS state_deleted,
                    a.active,
                    sd16.description  AS state_active,			
                    a.op_user_id,
                    u.username AS op_user_name
                FROM sys_mail_server a                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id  
               
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
     * @ Gridi doldurmak için sys_mail_server tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  24.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
            $sql = "
                SELECT 
                     COUNT(a.id) AS COUNT  
                FROM sys_mail_server a                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id  
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
     * @ sosyal medya bilgilerini dropdown ya da tree ye doldurmak için sys_mail_server tablosundan kayıtları döndürür !!
     * @version v 1.0  24.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillMailServerList($params = array()) {
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
                    a.host_name,
                    a.host_address, 
                    a.active,
                    'open' AS state_type                        
                FROM sys_mail_server a                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id  
                WHERE  a.deleted = 0  
                ORDER BY a.host_name
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
     * @ sys_mail_server tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  24.05.2016  
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
                UPDATE sys_mail_server
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_mail_server
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

}
