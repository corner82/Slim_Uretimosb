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
class SysAclRoles extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_acl_roles tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  07.01.2016
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
                UPDATE sys_acl_roles
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
                            
                $xc = $this->deleteResourceRoles(array('role_id' => $params['id'],                     
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
     * @ sys_acl_resource_roles tablosundan role_id li resource ları siler. !!
     * @version v 1.0  04.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function deleteResourceRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE sys_acl_resource_roles
                SET  deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "               
                WHERE 
                    role_id =  " . intval($params['role_id']). " AND                             
                    deleted =0                        
                    ");
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
     * @ sys_acl_roles tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  07.01.2016  
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
                a.name AS name,
                a.name_tr,
                a.resource_id,
                sare.name AS resource_name,
                a.icon_class, 
                a.c_date as create_date,
                a.start_date,
                a.end_date,
                a.parent_id, 
                sar1.name AS parent_name,
                a.deleted, 
                sd15.description AS state_deleted,                 
                a.active, 
                sd16.description AS state_active,  
                a.description,                                     
                a.op_user_id,
                u.username,
                a.inherited,
                sar.name AS inherited_name                                            
            FROM sys_acl_roles a
            LEFT JOIN sys_acl_resources sare ON sare.id = a.resource_id 
            INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
            INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
            INNER JOIN info_users u ON u.id = a.op_user_id 
            LEFT JOIN sys_acl_roles sar ON a.inherited > 0 AND sar.id = a.inherited AND sar.active =0 AND sar.deleted =0 
            LEFT JOIN sys_acl_roles sar1 ON a.parent_id > 0 AND sar1.id = a.parent_id AND sar1.active =0 AND sar1.deleted =0 
            WHERE a.deleted =0  
            ORDER BY a.name             
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
     * @ sys_acl_roles tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  07.01.2016
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
                    $ParentId = 0;
                    if ((isset($params['parent_id']) && $params['parent_id'] != "")) {
                        $ParentId = $params['parent_id'];
                    }       
                    $Inherited = 0;
                    if ((isset($params['inherited']) && $params['inherited'] != "")) {
                        $ParentId = $params['inherited'];
                    }
                    $IconClass = '';
                    if ((isset($params['icon_class']) && $params['icon_class'] != "")) {
                        $ParentId = $params['icon_class'];
                    }    
                    
                    $sql = "
                    INSERT INTO sys_acl_roles(
                            name, 
                            name_tr,
                            icon_class,  
                            parent_id, 
                            op_user_id, 
                            description, 
                            inherited)
                    VALUES (
                            '" . $params['name'] . "', 
                            '" . $params['name_tr'] . "',                         
                            '" . $IconClass . "', 
                            " . intval($ParentId) . ",
                            " . intval($opUserIdValue) . ",
                            '" . $params['description'] . "',
                            " . intval($Inherited) . "
                            )   ";
                    $statement = $pdo->prepare($sql);                    
                  // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_roles_id_seq');
                    $errorInfo = $statement->errorInfo(); 
                 
                    $xc = $this->deleteResourceRoles(array('role_id' => $insertID,                     
                     'op_user_id' => $opUserIdValue,
                    ));
                 
                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                
                    if ((isset($params['resource_id']) && $params['resource_id'] != "")) {
                        $xc = $this->insertResourceRoles(array( 'role_id' => $insertID,
                                                                'resource_id' => $params['resource_id'],
                                                                'op_user_id' => $opUserIdValue,
                        ));
                    }
                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                    
                            
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
     * @ sys_acl_resource_roles tablosuna yeni kayıt oluşturur.  !!
     * @version v 1.0  03.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertResourceRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$kontrol = $this->haveRecordsResourceRoles($params);
           // if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $sql = "
                INSERT INTO sys_acl_resource_roles(
                        role_id, 
                        resource_id,
                        op_user_id
                        )          
                        SELECT    
                            " . intval( $params['role_id']) . ",
                            id AS resource_id,                            
                            " . intval( $params['op_user_id']) . "
                        FROM sys_acl_resources 
                        WHERE       
                            id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['resource_id'] . "')) 
                    ";
                $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_acl_resource_roles_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //$pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
           /* } else {
                $errorInfo = '23505';
                $errorInfoColumn = 'name';
               // $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
            * 
            */
        } catch (\PDOException $e /* Exception $e */) {
          //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**    
     * @author Okan CIRAN
     * @ sys_acl_roles tablosunda name sutununda daha önce oluşturulmuş mu? 
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
                $addSql = " AND id != " . intval($params['id']) . " ";
            }
            $sql = " 
            SELECT  
                name as name, 
                '" . $params['name'] . "' AS value, 
                name ='" . $params['name'] . "' AS control,
                concat(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_acl_roles
            WHERE LOWER(REPLACE(name,' ','')) = LOWER(REPLACE('" . $params['name'] . "',' ',''))"
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
     * @ sys_acl_resource_roles tablosunda role ile resource daha önce ilişkilendirilmiş mi? 
     * @version v 1.0 03.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecordsResourceRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }
            $sql = "
            SELECT  
                a.resource_id AS name,
                resource_id AS value, 
                resource_id = resource_id AS control,
                CONCAT(a.resource_id, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_acl_resource_roles a
            WHERE 
                    role_id = " . intval($params['role_id']) . " AND
                    resource_id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['resource_id'] . "'))
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
     * sys_acl_roles tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.01.2016
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
                    $ParentId = 0;
                    if ((isset($params['parent_id']) && $params['parent_id'] != "")) {
                        $ParentId = $params['parent_id'];
                    }
                    $Inherited = 0;
                    if ((isset($params['inherited']) && $params['inherited'] != "")) {
                        $Inherited = $params['inherited'];
                    }
                    $IconClass = '';
                    if ((isset($params['icon_class']) && $params['icon_class'] != "")) {
                        $IconClass = $params['icon_class'];
                    }        

                    $sql = "
                UPDATE sys_acl_roles
                SET 
                    name = '" . $params['name'] . "',
                    name_tr = '" . $params['name_tr'] . "',
                    icon_class =  '" . $IconClass . "',
                    parent_id = " . intval($ParentId) . ",                  
                    description =  '" . $params['description'] . "',
                    op_user_id = " . intval($opUserIdValue) . ",
                    inherited = " . intval($Inherited) . "
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    
                    
                    $xc = $this->deleteResourceRoles(array('role_id' => $params['id'],                     
                     'op_user_id' => $opUserIdValue,
                    ));

                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                    
                    if ((isset($params['resource_id']) && $params['resource_id'] != "")) {
                        $xc = $this->insertResourceRoles(array( 'role_id' => $params['id'],
                                                                'resource_id' => $params['resource_id'],
                                                                'op_user_id' => $opUserIdValue,
                        ));
                    }
                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);
                    
                            
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505'; // $kontrol ['resultSet'][0]['message'];  
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
     * sys_acl_roles tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updateChild($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();       
            $sql = " 
            UPDATE sys_acl_roles
                SET                     
                    active = " . intval($params['active']) . " ,              
                    op_user_id= " . intval($params['user_id']) . " 
                WHERE id IN (
                  SELECT id FROM sys_acl_roles P WHERE p.inherited = (
                                  SELECT DISTINCT COALESCE(NULLIF(inherited, 0),id) FROM sys_acl_roles WHERE deleted = 0 AND id=" . $params['id'] . " )
                  AND parent_id >=" . $params['id'] . " OR id = " . $params['id'] . " 
                  )
                ";
            $statement = $pdo->prepare($sql);
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
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_acl_roles tablosundan kayıtları döndürür !!
     * @version v 1.0  07.01.2016
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
            $sort = "a.name";
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
                a.name AS name,
                a.name_tr,
                a.resource_id,
                sare.name AS resource_name,
                a.icon_class, 
                a.c_date as create_date,
                a.start_date,
                a.end_date,
                a.parent_id, 
                sar1.name AS parent_name,
                a.deleted, 
                sd15.description AS state_deleted,                 
                a.active, 
                sd16.description AS state_active,  
                a.description,                                     
                a.op_user_id,
                u.username,
                a.inherited,
                sar.name AS inherited_name                                            
            FROM sys_acl_roles a
            LEFT JOIN sys_acl_resources sare ON sare.id = a.resource_id 
            INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
            INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
            INNER JOIN info_users u ON u.id = a.op_user_id 
            LEFT JOIN sys_acl_roles sar ON a.inherited > 0 AND sar.id = a.inherited AND sar.active =0 AND sar.deleted =0 
            LEFT JOIN sys_acl_roles sar1 ON a.parent_id > 0 AND sar1.id = a.parent_id AND sar1.active =0 AND sar1.deleted =0 
            WHERE a.deleted =0  
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
     * @ Gridi doldurmak için sys_acl_roles tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  07.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSQL = ' WHERE a.deleted =0';            
          
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT                                          
                FROM sys_acl_roles a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.user_id 
                " . $whereSQL . "
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
     * @ combobox doldurmak için sys_acl_roles tablosundan parent ı 0 olan kayıtları (Ana grup) döndürür !!
     * @version v 1.0  07.01.2016
     * @return array
     * @throws \PDOException
     */
    public function fillComboBoxMainRoles() {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $statement = $pdo->prepare("
              SELECT                    
                  a.id, 	
                  a.name AS name,
                  a.name_tr,
                  a.active                   
              FROM sys_acl_roles a       
              WHERE a.parent_id =0 AND 
              a.deleted =0               
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
     * @ combobox doldurmak için sys_acl_roles tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  07.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillFullRolesDdList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $statement = $pdo->prepare("
                SELECT                    
                    a.id, 	
                    a.name AS name,
                    a.name_tr, 
                    a.active   
                FROM sys_acl_roles a       
                WHERE  
                    a.deleted = 0 AND a.active =0     
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
     * @ combobox doldurmak için sys_acl_roles tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  28.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBoxRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');         
            $statement = $pdo->prepare("
                SELECT                    
                    a.id, 	
                    a.name AS name,
                    a.name_tr, 
                    a.parent_id,
                    a.active ,                    
                    'open' AS state_type  
                FROM sys_acl_roles a        
                WHERE                    
                    a.active = 0 AND 
                    a.deleted = 0     
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
     * @ tree doldurmak için sys_acl_roles tablosundan kayıtları döndürür !!
     * @version v 1.0  13.08.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function fillRolesTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            $addSqlResourceId ="";
            $id = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $id = $params['id'];
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $ResourceId = $params['resource_id'];
                $addSqlResourceId .= " AND sarr.resource_id = " .  intval($ResourceId);
            }
            
            $jsonSqlResourceIds = "  
                (SELECT array_to_json(COALESCE(NULLIF(xx,'{}'),NULL)) FROM (
                    SELECT  
                        ARRAY(   
                            SELECT
                                axv.resource_id                             
                            FROM sys_acl_resource_roles axv
                            LEFT join sys_acl_resources bb ON bb.id = axv.resource_id AND bb.active=0 AND bb.deleted =0
                            WHERE axv.role_id = a.id AND axv.active =0 AND axv.deleted =0
                            ORDER BY axv.resource_id) AS xx
                            ) AS xtable)
            ";
            $ResourceNameSql = "  
                            (SELECT  replace(replace(vv, '{',''), '}','') FROM (
                            SELECT  COALESCE(NULLIF(yy, '{}'),NULL) AS vv   FROM (  
                              SELECT ". 
                               ' replace(cast(yyz as text), \'xxxx\',\'"\') AS yy   FROM (
                                      SELECT replace(cast(zxx as text), \'"\',\'\') AS yyz FROM (
                                               SELECT  
                                              ARRAY(  		 
                                                      SELECT concat(\'xxxx\',b.name,\'xxxx\') '.  
                                                       " FROM sys_acl_resource_roles axc
                                                        INNER join sys_acl_resources b ON b.id = axc.resource_id AND b.active=0 AND b.deleted =0
                                                        where axc.role_id = 34 AND axc.active =0 AND axc.deleted =0
                                                        order by axc.resource_id ) AS zxx						      
                                              ) as zxtable
                                      ) AS zxtable1
                              ) AS zxc 
                              ) AS vvvx)

 
                          ";   
            
       //LEFT JOIN sys_acl_resource_roles sarr ON sarr.active =0 AND sarr.deleted =0 AND sarr.role_id = a.id ".$addSqlResourceId."     
            $sql = " 
                SELECT   
                    a.id,
                    a.name AS name,
                    a.name_tr,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_roles z WHERE z.parent_id = a.id AND z.deleted = 0)
                    WHEN 1 THEN 'closed'
                    ELSE 'open' END AS state_type,
                    a.active,
                    ".$jsonSqlResourceIds." AS resource_ids,
                    ".$ResourceNameSql." AS resource_names
                FROM sys_acl_roles a                
                
                WHERE
                    a.parent_id = " . $id . " AND 
                    a.deleted = 0
                    ".$addSql."
                ORDER BY name
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
     * @ role bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  13.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRolesPropertiesList($params = array()) {
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
                $sort = "parent_id, name";
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
                            case 'name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name_tr" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

                                break;     
                            case 'parent_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND parent_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'inherited_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND inherited_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'resource_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND resource_name" . $sorguExpression . ' ';
                            
                                break;  
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                if (isset($params['name']) && $params['name'] != "") {
                    $sorguStr .= " AND a.name Like '%" . $params['name'] . "%'";
                }
                if (isset($params['name_tr']) && $params['name_tr'] != "") {
                    $sorguStr .= " AND name_tr Like '%" . $params['name_tr'] . "%'";
                }
                if (isset($params['inherited_name']) && $params['inherited_name'] != "") {
                    $sorguStr .= " AND inherited_name Like '%" . $params['inherited_name'] . "%'";
                }
                if (isset($params['description']) && $params['description'] != "") {
                    $sorguStr .= " AND description Like '%" . $params['description'] . "%'";
                }  
                if (isset($params['parent_name']) && $params['parent_name'] != "") {
                    $sorguStr .= " AND parent_name Like '%" . $params['parent_name'] . "%'";
                }  
                if (isset($params['resource_name']) && $params['resource_name'] != "") {
                    $sorguStr .= " AND resource_name Like '%" . $params['resource_name'] . "%'";
                } 
            }
                            
            $sorguStr = rtrim($sorguStr, "AND ");            
                            
            $jsonSql = "  (SELECT CAST (COALESCE(NULLIF(yy, '{}'),NULL) AS json) FROM (  
				SELECT  ".   		
				' replace(cast(yyz as text), \'xxxx\',\'"\') AS yy   FROM (
					SELECT replace(cast(zxx as text), \'"\',\'\') AS yyz FROM ('.
						" SELECT  
						ARRAY(  		 
							SELECT concat('xxxx',b.name,'xxxx:',axc.resource_id)  
							  FROM sys_acl_resource_roles axc
							  INNER join sys_acl_resources b ON b.id = axc.resource_id AND b.active=0 AND b.deleted =0
							  where axc.role_id = a.id  AND axc.active =0 AND axc.deleted =0
							  order by axc.resource_id ) As zxx						      
						) as zxtable
					) AS zxtable1
				) AS zxc) 
		            ";   
            
            
            
           $jsonSqlResourceIds = "  
                (SELECT array_to_json(COALESCE(NULLIF(cxx,'{}'),NULL)) FROM (
                    SELECT  
                        ARRAY(   
                            SELECT
                                axv.resource_id                             
                            FROM sys_acl_resource_roles axv
                            LEFT join sys_acl_resources bb ON bb.id = axv.resource_id AND bb.active=0 AND bb.deleted =0
                            WHERE axv.role_id = a.id AND axv.active =0 AND axv.deleted =0
                            ORDER BY axv.resource_id) AS cxx
                            ) AS zxtable)
            ";
          
            $sql = "  
                SELECT
                    id,
                    name,
                    name_tr,  
                    parent_id, 
                    COALESCE(NULLIF(parent_name, ''),'Root') AS parent_name, 
                    deleted, 
                    state_deleted,
                    active, 
                    state_active,  
                    COALESCE(NULLIF(description, ''),' ') AS description,
                    op_user_id,
                    username,
                    inherited,
                    COALESCE(NULLIF(inherited_name, ''),'Root') AS inherited_name,
                    resource_json,
                    resource_ids
                FROM (
                    SELECT 
                        a.id, 
                        a.name ,
                        a.name_tr,
                        a.start_date,
                        a.end_date,
                        a.parent_id, 
                        sar1.name AS parent_name,
                        a.deleted, 
                        sd15.description AS state_deleted,
                        a.active, 
                        sd16.description AS state_active,  
                        a.description,
                        a.op_user_id,
                        u.username,
                        a.inherited,
                        sar.name AS inherited_name,
                        ".$jsonSql." AS resource_json,
                        ".$jsonSqlResourceIds." AS resource_ids                            
                    FROM sys_acl_roles a 
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0
                    INNER JOIN info_users u ON u.id = a.op_user_id 
                    LEFT JOIN sys_acl_roles sar ON a.inherited > 0 AND sar.id = a.inherited AND sar.active =0 AND sar.deleted =0 
                    LEFT JOIN sys_acl_roles sar1 ON a.parent_id > 0 AND sar1.id = a.parent_id AND sar1.active =0 AND sar1.deleted =0 
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
     * @ resource bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  13.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRolesPropertiesListRtc($params = array()) {
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
                            case 'name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name_tr" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

                                break;     
                            case 'parent_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND parent_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'inherited_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND inherited_name" . $sorguExpression . ' ';
                            
                                break;  
                            case 'resource_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND resource_name" . $sorguExpression . ' ';
                            
                                break; 
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                 if (isset($params['name']) && $params['name'] != "") {
                    $sorguStr .= " AND name Like '%" . $params['name'] . "%'";
                }
                if (isset($params['name_tr']) && $params['name_tr'] != "") {
                    $sorguStr .= " AND name_tr Like '%" . $params['name_tr'] . "%'";
                }
                if (isset($params['inherited_name']) && $params['inherited_name'] != "") {
                    $sorguStr .= " AND inherited_name Like '%" . $params['inherited_name'] . "%'";
                }
                if (isset($params['description']) && $params['description'] != "") {
                    $sorguStr .= " AND description Like '%" . $params['description'] . "%'";
                }  
                if (isset($params['parent_name']) && $params['parent_name'] != "") {
                    $sorguStr .= " AND parent_name Like '%" . $params['parent_name'] . "%'";
                }   
                if (isset($params['resource_name']) && $params['resource_name'] != "") {
                    $sorguStr .= " AND resource_name Like '%" . $params['resource_name'] . "%'";
                } 
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = " 
                SELECT COUNT(id) AS count 
                FROM (
                    SELECT   
                        id, 
                        name,
                        name_tr,                    
                        parent_id, 
                        COALESCE(NULLIF(parent_name, ''),'Root') AS parent_name, 
                        resource_id,
                        resource_name,
                        deleted, 
                        state_deleted,                 
                        active, 
                        state_active,  
                        description,                                     
                        op_user_id,
                        username,
                        inherited,
                        inherited_name    
                    FROM (
                        SELECT 
                            a.id, 
                            a.name ,
                            a.name_tr,
                            a.start_date,
                            a.end_date,
                            a.parent_id, 
                            sar1.name AS parent_name,
                            a.resource_id,
                            sare.name AS resource_name,
                            a.deleted, 
                            sd15.description AS state_deleted,                 
                            a.active, 
                            sd16.description AS state_active,  
                            a.description,                                     
                            a.op_user_id,
                            u.username,
                            a.inherited,
                            sar.name AS inherited_name                                            
                        FROM sys_acl_roles a
                        LEFT JOIN sys_acl_resources sare ON sare.id = a.resource_id 
                        INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0                             
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        LEFT JOIN sys_acl_roles sar ON a.inherited > 0 AND sar.id = a.inherited AND sar.active =0 AND sar.deleted =0 
                        LEFT JOIN sys_acl_roles sar1 ON a.parent_id > 0 AND sar1.id = a.parent_id AND sar1.active =0 AND sar1.deleted =0 
                        WHERE a.deleted =0 
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

    /**
     * @author Okan CIRAN
     * @ sys_acl_roles tablosundan parametre olarak  gelen id kaydın aktifliğini
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
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                if (isset($params['id']) && $params['id'] != "") {

                    $sql = "                 
                UPDATE sys_acl_roles
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_acl_roles
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
     * @ ddslick doldurmak için sys_acl_roles tablosundan danısman kayıtları döndürür !!
     * @version v 1.0 09.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsultantRolesDdlist($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');   
            $sql ="                
                SELECT
                    mt.id, 	
                    mt.name,
                    mt.name_tr, 
                    mt.active   
                FROM sys_acl_resources a
                INNER JOIN sys_acl_resource_roles sarr ON sarr.resource_id = a.id  AND sarr.deleted =0 AND sarr.active =0 
		INNER join sys_acl_roles mt ON mt.id = sarr.role_id AND mt.active =0 AND mt.deleted =0
                WHERE                    
                   a.id = 20 AND 
                   mt.deleted = 0 AND
                   mt.active =0 
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
     * @ ddslick doldurmak için sys_acl_roles tablosundan danısman kayıtları döndürür !!
     * @version v 1.0 09.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillClusterRolesDdlist($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');   
            $sql ="                
                SELECT
                    mt.id, 	
                    mt.name,
                    mt.name_tr, 
                    mt.active   
                FROM sys_acl_resources a
                INNER JOIN sys_acl_resource_roles sarr ON sarr.resource_id = a.id  AND sarr.deleted =0 AND sarr.active =0 
		INNER join sys_acl_roles mt ON mt.id = sarr.role_id AND mt.active =0 AND mt.deleted =0
                WHERE                    
                   a.id = 26 AND 
                   mt.deleted = 0 AND
                   mt.active =0 
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
     * @ ddslick doldurmak için sys_acl_roles tablosundan danısman kayıtları döndürür !!
     * @version v 1.0 09.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRolesDdlist($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');   
            $sql ="
                SELECT
                    a.id,
                    a.name,
                    a.name_tr, 
                    a.active   
                FROM sys_acl_roles a  
                WHERE    
                    a.deleted = 0 AND
                    a.active =0 
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
  
    
    
}
