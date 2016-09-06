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
class SysAclActions extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_acl_actions tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0 26.07.2016
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
           //     $ModuleId = $this->haveMenuTypeRecords(array('id' => $params['id']));
           //     if (!\Utill\Dal\Helper::haveRecord($ModuleId)) {

                    $xAclPrivileges = $this->haveRecordsActionPrivilegRestServices(array('id' => $params['id'],));
                    if (!\Utill\Dal\Helper::haveRecord($xAclPrivileges)) {

                        $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                        $sql = " 
                UPDATE sys_acl_actions
                SET  deleted= 1, active = 1,
                     op_user_id = " . intval($opUserIdValue) . "
                WHERE id = " . intval($params['id'])
                        ;
                        $statement = $pdo->prepare($sql);
                        // echo debugPDO($sql, $params);                
                        $update = $statement->execute();
                        $afterRows = $statement->rowCount();
                        $errorInfo = $statement->errorInfo();
                        if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                            throw new \PDOException($errorInfo[0]);

                        $xc = $this->deletedAclPrivilege(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));

                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                        
                        
                        $xc = $this->deletedAclRrp(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));
                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                        
                            
                        
                        $xc = $this->deleteActionRoles(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));

                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);

                        $xc = $this->deleteActionResources(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));

                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                            
                           
                        $xc = $this->deleteActionRrp(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));
                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                              
                        
                        
                        $pdo->commit();
                        return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
                    } else {
                        $xAclPrivilegesCountValue = $xAclPrivileges['resultSet'][0]['adet'];

                        $errorInfo = '23503';   // 23503  foreign_key_violation
                        $errorInfoColumn = 'haveRecordsActionPrivilegRestServices';
                        $count = $xAclPrivilegesCountValue;
                        $pdo->rollback();
                        return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn, "errorInfoColumnCount" => $count);
                    }
             /*   } else {
                    $errorInfo = '23503';   // 23503  foreign_key_violation
                    $errorInfoColumn = 'MenuType';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
              * 
              */
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
     * @ sys_acl_actions_roles tablosundan parametre olarak  gelen action_id kayıtları siler. !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */                     
    public function deleteActionRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE sys_acl_actions_roles
                SET  deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "               
                WHERE 
                    action_id =  " . intval($params['action_id']). " AND   
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
     * @ sys_acl_action_resources tablosundan parametre olarak  gelen action_id kayıtları siler. !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */                     
    public function deleteActionResources($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                UPDATE sys_acl_action_resources
                SET  deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "               
                WHERE 
                    action_id =  " . intval($params['action_id']). " AND   
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
     * @ sys_acl_action_rrp tablosundan parametre olarak  gelen action_id kayıtları siler. !!
     * @version v 1.0 15.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */                     
    public function deleteActionRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $sql = " 
                UPDATE sys_acl_action_rrp
                SET  deleted= 1, active = 1,
                     op_user_id = " . intval($params['op_user_id']) . "               
                WHERE                 
                    deleted =0 AND 
                    resource_id = (                     
                        SELECT id AS resource_id
			FROM sys_acl_action_resources
			WHERE active =0 AND deleted =0 AND action_id = " . intval($params['action_id']). " 
                        LIMIT 1 )                    
                    ";
                $statement = $pdo->prepare($sql); 
               //  echo debugPDO($sql, $params);
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
     * @ sys_acl_actions_roles tablosundan parametre olarak  gelen action_id kayıtları siler. !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */                     
    public function getResourceName($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                SELECT 
                    name AS resource_name 
                FROM sys_acl_action_resources                
                WHERE 
                    action_id =  " . intval($params['action_id']). " AND   
                    deleted =0 
                    ");
                //Execute our DELETE statement.
                $update = $statement->execute();
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
     * @ sys_acl_actions_roles tablosundan parametre olarak  gelen action_id nin role_id lerinin json olarak  döner  !!
     * @version v 1.0  17.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */                     
    public function getActionRoleIds($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
                $statement = $pdo->prepare(" 
                 (SELECT array_to_json(COALESCE(NULLIF(cxx,'{}'),NULL)) AS role_ids FROM (
                    SELECT  
                        ARRAY(   
                            SELECT
                                axv.role_id                             
                            FROM sys_acl_actions_roles axv
                            LEFT join sys_acl_action_resources bb ON bb.id = axv.action_id AND bb.active=0 AND bb.deleted =0
                            WHERE axv.action_id = " . intval($params['action_id']). " AND axv.active =0 AND axv.deleted =0
                            ORDER BY axv.action_id) AS cxx
                            ) AS zxtable)  
                    ");                            
                $update = $statement->execute();
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
     * @ sys_acl_rrp tablosuna yeni kayıt oluşturur. 
     * Sadece Action role update edildiğinde çalıştırılması gerekli  !!
     * Eğer yeni role_ids içerisinde sys_acl_rrp de eski role_id ye ait kayıt varsa silme işlemi yapacak. 
     * @version v 1.0  17.08.2016
     * @return array
     * @throws \PDOException
     */
    public function getAclRrpControl($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = " 
                    SELECT   
                        a.role_id AS name , 
                        a.role_id AS value , 
                        a.role_id = a.role_id AS control,
                        CONCAT(a.role_id, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message,
                           (
                            SELECT 
                                count(rrpx.id) 
                            FROM sys_acl_rrp rrpx  
                            WHERE  rrpx.privilege_id = a.privilege_id AND 
                                   rrpx.resource_id = a.resource_id AND 
                                   rrpx.role_id = a.role_id AND 
                                   rrpx.active =0 AND
                                   rrpx.deleted =0 
                                   LIMIT 1
                            ) AS adet
                    FROM sys_acl_rrp a
                    WHERE  
                    a.resource_id = 24 AND 
                    a.privilege_id = (SELECT id FROM sys_acl_privilege 
                                    WHERE 
                                        resource_id = 24 AND 
                                        name ='" . $params['oldname'] . "' AND 
                                        resource_type_id = 1 AND 
                                        active=0 AND 
                                        deleted =0 LIMIT 1) AND
                    a.active= 0 AND 
                    a.deleted = 0 AND 
                    a.role_id IN ( 
                        SELECT
                            id AS role_id 
                        FROM sys_acl_roles
                        WHERE
                          
                            id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_array_elements('" . $params['oldrole_ids'] . "'))
                        )
                 ";
            //   id NOT IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['newrole_ids'] . "')) AND
            $statement = $pdo->prepare($sql);
           //echo debugPDO($sql, $params);
            $result = $statement->execute();          
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
     * @ sys_acl_actions tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  26.07.2016    
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
                    a.name AS name,   
                    sam.id AS module_id,
                    sam.name AS module_name,                     
                    a.c_date AS create_date,                        
                    a.deleted,
                    sd.description AS state_deleted,
                    a.active,
                    sd1.description AS state_active,
                    a.description,
                    a.op_user_id,
                    u.username
                FROM sys_acl_actions a                                
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0                
                INNER JOIN sys_acl_modules sam ON sam.id = a.module_id AND sam.deleted = 0 AND sam.active = 0
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id                
                ORDER BY sam.name, a.name
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
     * @ sys_acl_actions tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  26.07.2016
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
                            
                    $sql = "
                INSERT INTO sys_acl_actions(
                        name, 
                        module_id,
                        op_user_id, 
                        description 
                        )
                VALUES (
                        '".$params['name']."', 
                        " . intval( $params['module_id']) . ",
                        " . intval($opUserIdValue) . ",
                        '".$params['description']."'                        
                                              )  ";
                    $statement = $pdo->prepare($sql);                    
                   // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_actions_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    
                            
                    $xActionResource = $this->insertModulActionResource(array(
                                'op_user_id' => intval($opUserIdValue),
                                'action_id' => intval($insertID),
                                'role_ids' => $params['role_ids'],
                                    )
                    );
                    if ($xActionResource['errorInfo'][0] != "00000" && $xActionResource['errorInfo'][1] != NULL && $xActionResource['errorInfo'][2] != NULL)
                        throw new \PDOException($xActionResource['errorInfo']);
                    
                 
                    $xActionRoles = $this->insertActionRoles(array(
                                'op_user_id' => intval($opUserIdValue),
                                'action_id' => intval($insertID),
                                'role_ids' => $params['role_ids'],
                                    )
                    );
                    if ($xActionRoles['errorInfo'][0] != "00000" && $xActionRoles['errorInfo'][1] != NULL && $xActionRoles['errorInfo'][2] != NULL)
                        throw new \PDOException($xActionRoles['errorInfo']);
                        
                    
                            
                    $xActionResourceIdValue = $xActionResource['lastInsertId'];
                    $xActionPrivilege = $this->insertAclPrivilege(array(
                                'op_user_id' => intval($opUserIdValue),
                                'resource_id' => intval($xActionResourceIdValue),
                                'name' => $params['name'],                            
                                    )
                    );

                    if ($xActionPrivilege['errorInfo'][0] != "00000" && $xActionPrivilege['errorInfo'][1] != NULL && $xActionPrivilege['errorInfo'][2] != NULL)
                        throw new \PDOException($xActionPrivilege['errorInfo']);

                    
                    $xActionPrivilegeIdValue = $xActionPrivilege['lastInsertId'];
                    $xActionAclRrp = $this->insertAclRrp(array(
                                'op_user_id' => intval($opUserIdValue),
                                'resource_id' => intval($xActionResourceIdValue),
                                'privilege_id' => intval($xActionPrivilegeIdValue),
                                'role_ids' => $params['role_ids'],
                                    )
                    );

                    if ($xActionAclRrp['errorInfo'][0] != "00000" && $xActionAclRrp['errorInfo'][1] != NULL && $xActionAclRrp['errorInfo'][2] != NULL)
                        throw new \PDOException($xActionAclRrp['errorInfo']);

                         
                            
                    $xActionResourceIdValue = $xActionResource['lastInsertId'];
                    $xActionRrp = $this->insertActionRrp(array(
                                'op_user_id' => intval($opUserIdValue),
                                'resource_id' => intval($xActionResourceIdValue),
                                'role_ids' => $params['role_ids'],
                                    )
                    );

                    if ($xActionRrp['errorInfo'][0] != "00000" && $xActionRrp['errorInfo'][1] != NULL && $xActionRrp['errorInfo'][2] != NULL)
                        throw new \PDOException($xActionRrp['errorInfo']);
                            
                    
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
     * @ sys_acl_action_resources tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertModulActionResource($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');

            $sql = "
                INSERT INTO sys_acl_action_resources(
                        name, 
                        action_id,
                        op_user_id 
                        )
                VALUES (
                       (    SELECT REPLACE(CONCAT(b.name,'-' ,a.name ),' ','')
                            FROM sys_acl_actions a 
                            INNER JOIN sys_acl_modules b ON b.id = a.module_id AND b.active =0 AND b.deleted =0 
                            WHERE a.active =0 AND a.deleted =0 AND a.id = " . intval($params['action_id']) . " ), 
                        " . intval($params['action_id']) . ",
                        " . intval($params['op_user_id']) . "                        
                                              )  ";
            $statement = $pdo->prepare($sql);
           //  echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_acl_action_resources_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]); 
                            
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_privilege tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  12-08-2016
     * @return array
     * @throws \PDOException
     */
    public function insertAclPrivilege($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
                $sql = "
                INSERT INTO sys_acl_privilege(                         
                        name,
                        name_eng,
                        resource_id,
                        op_user_id,
                        resource_type_id
                        )
                VALUES (                        
                        (SELECT name FROM sys_acl_action_resources WHERE id =  ". intval($params['resource_id'])."),                           
                        (SELECT name FROM sys_acl_action_resources WHERE id =  ". intval($params['resource_id'])."),
                        24, 
                        ". intval($params['op_user_id']).",
                        1  
                        )";
                    $statement = $pdo->prepare($sql);
                   // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_privilege_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);                  
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);        
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_acl_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  12.08.2016
     * @return array
     * @throws \PDOException
     */
    public function insertAclRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                INSERT INTO sys_acl_rrp(
                        role_id, 
                        resource_id, 
                        privilege_id,
                        op_user_id
                        )   
                        SELECT
                            id AS role_id,
                            24,
                            " . intval($params['privilege_id']) . ",
                            " . intval($params['op_user_id']) . "
                        FROM sys_acl_roles
                        WHERE       
                            id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['role_ids'] . "')) 
  
                 ";
            $statement = $pdo->prepare($sql);
            // echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_acl_rrp_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_rrp tablosuna yeni kayıt oluşturur. 
     * Sadece Action role update edildiğinde çalıştırılması gerekli  !!
     * Eğer yeni role_ids içerisinde sys_acl_rrp de eski role_id ye ait kayıt varsa silme işlemi yapacak. 
     * @version v 1.0  17.08.2016
     * @return array
     * @throws \PDOException
     */
    public function UpdateDeletedAclRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                UPDATE sys_acl_rrp
                       SET  active= 1 ,
                            deleted =1 ,
                            op_user_id = " . intval($params['op_user_id']) . "
                        WHERE 
                        resource_id = 24  AND 
                        privilege_id = (SELECT id FROM sys_acl_privilege 
                                    WHERE 
                                        resource_id = 24 AND 
                                        name ='" . $params['oldname'] . "' AND 
                                        resource_type_id = 1 AND 
                                        active=0 AND 
                                        deleted =0 LIMIT 1) AND
                        active= 0 AND 
                        deleted = 0 AND 
                        role_id IN ( 
                            SELECT
                                id AS role_id                         
                            FROM sys_acl_roles
                            WHERE       
                                id NOT IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['newrole_ids'] . "')) AND
                                id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_array_elements('" . $params['oldrole_ids'] . "'))                     
                            )
                 ";
            $statement = $pdo->prepare($sql);
            // echo debugPDO($sql, $params);
            $result = $statement->execute();
          //  $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo ); //, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {           
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_acl_rrp tablosuna update edilen rollerde farklı olanlar varsa yeni kayıt oluşturur. !!
     * @version v 1.0 17.08.2016
     * @return array
     * @throws \PDOException
     */
    public function UpdateInsertAclRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                INSERT INTO sys_acl_rrp(
                        role_id, 
                        resource_id, 
                        privilege_id,
                        op_user_id
                        )  
                        SELECT
                            id AS role_id,
                            24,
                            (SELECT id FROM sys_acl_privilege 
                                    WHERE 
                                        resource_id = 24 AND 
                                        name ='" . $params['newname'] . "' AND 
                                        resource_type_id = 1 AND 
                                        active=0 AND 
                                        deleted =0 LIMIT 1 ),                            
                            " . intval($params['op_user_id']) . "
                        FROM sys_acl_roles
                        WHERE       
                           id IN ( 
                                SELECT
                                    id AS role_id                         
                                FROM sys_acl_roles
                                WHERE       
                                    id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['newrole_ids'] . "')) AND
                                    id NOT IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_array_elements('" . $params['oldrole_ids'] . "'))                     
                                )  
                 ";
            $statement = $pdo->prepare($sql);
           // echo debugPDO($sql, $params);
            $result = $statement->execute();
          //  $insertID = $pdo->lastInsertId('sys_acl_rrp_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo ); //, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }                            
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_action_resources tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertActionRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');

            $sql = "
                INSERT INTO sys_acl_actions_roles(
                    action_id,
                    role_id,
                    op_user_id 
                )          
                SELECT    
                    " . intval( $params['action_id']) . ",
                    id AS role_id,
                    " . intval( $params['op_user_id']) . "
                FROM sys_acl_roles 
                WHERE       
                     id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['role_ids'] . "')) 
                     ";
            /*
             (SELECT array_to_json(COALESCE(NULLIF(cxx,'{}'),NULL)) FROM (
                               SELECT  
                                   ARRAY(   
                                        SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('". $params['role_ids']."')) AS cxx
                                       ) AS zxtable )
             */
            
            $statement = $pdo->prepare($sql);
           //echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_acl_actions_roles_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_action_resources tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertActionRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                INSERT INTO sys_acl_action_rrp(
                    role_id, 
                    resource_id, 
                    privilege_id,
                    op_user_id 
                ) 
                SELECT    
                    a.id AS role_id,
                    " . intval( $params['resource_id']) . ",
                    b.id AS privilege_id,
                    " . intval( $params['op_user_id']) . "
                FROM sys_acl_roles a 
                INNER JOIN sys_action_privileges b ON b.default_type=1 
                WHERE
                     a.id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['role_ids'] . "')
                    order by a.id, b.id 
                    ) 
                     "; 
            $statement = $pdo->prepare($sql);
           //echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_acl_actions_roles_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_action_rrp tablosuna privilege lerin kayıtlarını oluşturur.  !!
     * @version v 1.0  18.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertActionPrivilegeRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                INSERT INTO sys_acl_action_rrp(
                    role_id, 
                    resource_id, 
                    privilege_id,
                    op_user_id 
                ) 
                SELECT   
                    a.id AS role_id,
                    saar.id,
                    b.id AS privilege_id,
                    " . intval( $params['op_user_id']) . "
                FROM sys_acl_roles a 
                INNER JOIN sys_action_privileges b ON b.default_type=1 and b.active =0 and b.deleted =0 
                INNER JOIN sys_acl_action_resources saar on saar.active = 0 and saar.deleted =0 and saar.action_id = " . intval( $params['action_id']) . " 
                WHERE
                     a.id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['role_ids'] . "')
                    order by saar.id, a.id, b.id )                


                     "; 
            $statement = $pdo->prepare($sql);
            //echo debugPDO($sql, $params);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_acl_actions_roles_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
       
    /**
     * @author Okan CIRAN
     * sys_acl_actions tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  26.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $xActionPrivilegesServices = $this->haveRecordsActionPrivilegRestServices(array('id' => $params['id'],));
            if (!\Utill\Dal\Helper::haveRecord($xActionPrivilegesServices)) {
                $xcOldResourceName = $this->getResourceName(array('action_id' => $params['id'],));
                if ($xcOldResourceName['errorInfo'][0] != "00000" && $xcOldResourceName['errorInfo'][1] != NULL && $xcOldResourceName['errorInfo'][2] != NULL)
                    throw new \PDOException($xcOldResourceName['errorInfo']);
                $xcOldActionRoleIds = $this->getActionRoleIds(array('action_id' => $params['id'],));
                if ($xcOldActionRoleIds['errorInfo'][0] != "00000" && $xcOldActionRoleIds['errorInfo'][1] != NULL && $xcOldActionRoleIds['errorInfo'][2] != NULL)
                    throw new \PDOException($xcOldActionRoleIds['errorInfo']);
             //   print_r( $xcOldActionRoleIds ['resultSet'][0]['role_ids']);
                $xAclPrivileges = $this->getAclRrpControl(array('id' => $params['id'],
                    'oldname' => $xcOldResourceName ['resultSet'][0]['resource_name'],
                    'oldrole_ids' => $xcOldActionRoleIds ['resultSet'][0]['role_ids'],
                //    'newrole_ids' => $params['role_ids'],
                ));

                if (\Utill\Dal\Helper::haveRecord($xAclPrivileges)) {
                    
                    $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));                            
                    if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                        $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                        $kontrol = $this->haveRecords($params);
                        if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                            $sql = "
                UPDATE sys_acl_actions
                SET
                    name = '" . $params['name'] . "',  
                    module_id = " . intval($params['module_id']) . ",
                    op_user_id= " . intval($opUserIdValue) . ",
                    description = '" . $params['description'] . "'                     
                WHERE id = " . intval($params['id']) . "
                    ";
                            $statement = $pdo->prepare($sql);
                            // echo debugPDO($sql, $params);
                            $update = $statement->execute();
                            $affectedRows = $statement->rowCount();
                            $errorInfo = $statement->errorInfo();
                            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                                throw new \PDOException($errorInfo[0]);
                            $xActionResource = $this->updateModulActionResource(array(
                                'op_user_id' => intval($opUserIdValue),
                                'name' => $params['name'],
                                'module_id' => intval($params['module_id']),
                                'action_id' => intval($params['id']),
                                    )
                            );
                            if ($xActionResource['errorInfo'][0] != "00000" && $xActionResource['errorInfo'][1] != NULL && $xActionResource['errorInfo'][2] != NULL)
                                throw new \PDOException($xActionResource['errorInfo']);

                         
                            
                            $xc = $this->deleteActionRoles(array('action_id' => $params['id'],
                                'op_user_id' => $opUserIdValue,
                            ));
                            if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                                throw new \PDOException($xc['errorInfo']);
                         
                            
                            $xActionRoles = $this->insertActionRoles(array(
                                'op_user_id' => intval($opUserIdValue),
                                'action_id' => intval($params['id']),
                                'role_ids' => $params['role_ids'],
                            ));
                            if ($xActionRoles['errorInfo'][0] != "00000" && $xActionRoles['errorInfo'][1] != NULL && $xActionRoles['errorInfo'][2] != NULL)
                                throw new \PDOException($xActionRoles['errorInfo']);
                            
                            
                            $xc = $this->deleteActionRrp(array('action_id' => $params['id'],
                                'op_user_id' => $opUserIdValue,
                            ));
                            if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                                throw new \PDOException($xc['errorInfo']);
                            
                            

                            
                            $xActionRrp = $this->insertActionPrivilegeRrp(array(
                                        'op_user_id' => intval($opUserIdValue),
                                        'action_id' => intval($params['id']),
                                        'role_ids' => $params['role_ids'],
                                        )
                            );

                            if ($xActionRrp['errorInfo'][0] != "00000" && $xActionRrp['errorInfo'][1] != NULL && $xActionRrp['errorInfo'][2] != NULL)
                                throw new \PDOException($xActionRrp['errorInfo']);

                            
                            $xcNewResourceName = $this->getResourceName(array('action_id' => $params['id'],));
                            if ($xcNewResourceName['errorInfo'][0] != "00000" && $xcNewResourceName['errorInfo'][1] != NULL && $xcNewResourceName['errorInfo'][2] != NULL)
                                throw new \PDOException($xcNewResourceName['errorInfo']);
                           
                            
                            

                    if ($xActionPrivilege['errorInfo'][0] != "00000" && $xActionPrivilege['errorInfo'][1] != NULL && $xActionPrivilege['errorInfo'][2] != NULL)
                        throw new \PDOException($xActionPrivilege['errorInfo']);

                            
                            if ($xcOldResourceName ['resultSet'][0]['resource_name'] != $xcNewResourceName ['resultSet'][0]['resource_name']) {
                                $xcx = $this->updateAclPrivilege(array(
                                    'oldname' => $xcOldResourceName ['resultSet'][0]['resource_name'],
                                    'newname' => $xcNewResourceName ['resultSet'][0]['resource_name'],
                                    'op_user_id' => $opUserIdValue,
                                ));
                                if ($xcx['errorInfo'][0] != "00000" && $xcx['errorInfo'][1] != NULL && $xcx['errorInfo'][2] != NULL)
                                    throw new \PDOException($xcx['errorInfo']);
                            };
                            // role değiştiyse eski roller ile yeni roller de aynı olanlar deleted yapılacak 
                            $xcx = $this->UpdateDeletedAclRrp(array(
                                'oldrole_ids' => $xcOldActionRoleIds ['resultSet'][0]['role_ids'],
                                'newrole_ids' => $params['role_ids'],
                                'oldname' => $xcOldResourceName ['resultSet'][0]['resource_name'],
                                'op_user_id' => $opUserIdValue,
                            ));

                            if ($xcx['errorInfo'][0] != "00000" && $xcx['errorInfo'][1] != NULL && $xcx['errorInfo'][2] != NULL)
                                throw new \PDOException($xcx['errorInfo']);


                            // role değiştiyse eski roller ile yeni roller de aynı olanlar deleted yapılacak 
                            $xcx = $this->UpdateInsertAclRrp(array(
                                'oldrole_ids' => $xcOldActionRoleIds ['resultSet'][0]['role_ids'],
                                'newrole_ids' => $params['role_ids'],
                                'newname' => $xcNewResourceName ['resultSet'][0]['resource_name'],
                                'op_user_id' => $opUserIdValue,
                            ));

                            if ($xcx['errorInfo'][0] != "00000" && $xcx['errorInfo'][1] != NULL && $xcx['errorInfo'][2] != NULL)
                                throw new \PDOException($xcx['errorInfo']);

                            $pdo->commit();
                            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
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
                } else {
                    $xAclPrivilegesValue = $xAclPrivileges['resultSet'][0]['adet'];

                    $errorInfo = '23503';   // 23503  foreign_key_violation
                    $errorInfoColumn = 'haveRecordsAclPrivileg';
                    $count = $xAclPrivilegesValue;
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn, "errorInfoColumnCount" => $count);
                }
            } else {
                            
            $xActionPrivilegesServicesValue = 0;
            if ((isset($xActionPrivilegesServices['resultSet'][0]['adet']) && $xActionPrivilegesServices['resultSet'][0]['adet'] != "")) {
                $xActionPrivilegesServicesValue = intval($xActionPrivilegesServices['resultSet'][0]['adet']);
            }
                
              //  $xActionPrivilegesServicesValue = $xActionPrivilegesServices['resultSet'][0]['adet'];

                $errorInfo = '23503';   // 23503  foreign_key_violation
                $errorInfoColumn = 'haveRecordsActionPrivilegRestServices';
                $count = $xActionPrivilegesServicesValue;
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn, "errorInfoColumnCount" => $count);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * sys_acl_actions tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  17.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updateAct($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
           
            $xAclPrivileges = $this->haveRecordsActionPrivilegRestServices(array('id' => $params['id'],));                            
            if (!\Utill\Dal\Helper::haveRecord($xAclPrivileges)) {
                            
                $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $kontrol = $this->haveRecords($params);
                    if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                            
                    $xcOldResourceName = $this->getResourceName(array('action_id' => $params['id'],));
                    if ($xcOldResourceName['errorInfo'][0] != "00000" && $xcOldResourceName['errorInfo'][1] != NULL && $xcOldResourceName['errorInfo'][2] != NULL)
                        throw new \PDOException($xcOldResourceName['errorInfo']);                        
                            
                    
                        $sql = "
                UPDATE sys_acl_actions
                SET
                    name = '" . $params['name'] . "',  
                    module_id = " . intval($params['module_id']) . ",
                    op_user_id= " . intval($opUserIdValue) . ",
                    description = '" . $params['description'] . "'                     
                WHERE id = " . intval($params['id']) . "
                    ";
                        $statement = $pdo->prepare($sql);
                        // echo debugPDO($sql, $params);
                        $update = $statement->execute();
                        $affectedRows = $statement->rowCount();
                        $errorInfo = $statement->errorInfo();
                        if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                            throw new \PDOException($errorInfo[0]);

                        $xActionResource = $this->updateModulActionResource(array(
                            'op_user_id' => intval($opUserIdValue),
                            'name' => $params['name'],
                            'module_id' => intval($params['module_id']),
                            'action_id' => intval($params['id']),
                                )
                        );
                            
                        if ($xActionResource['errorInfo'][0] != "00000" && $xActionResource['errorInfo'][1] != NULL && $xActionResource['errorInfo'][2] != NULL)
                            throw new \PDOException($xActionResource['errorInfo']);
                          
                        $xcOldActionRoleIds = $this->getActionRoleIds(array('action_id' => $params['id'],));
                        if ($xcOldActionRoleIds['errorInfo'][0] != "00000" && $xcOldActionRoleIds['errorInfo'][1] != NULL && $xcOldActionRoleIds['errorInfo'][2] != NULL)
                            throw new \PDOException($xcOldActionRoleIds['errorInfo']);    
                           
                            
                        $xc = $this->deleteActionRoles(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));
                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                           
                        
                        
                        $xActionRoles = $this->insertActionRoles(array(
                            'op_user_id' => intval($opUserIdValue),
                            'action_id' => intval($params['id']),
                            'role_ids' => $params['role_ids'],
                        ));
                        if ($xActionRoles['errorInfo'][0] != "00000" && $xActionRoles['errorInfo'][1] != NULL && $xActionRoles['errorInfo'][2] != NULL)
                            throw new \PDOException($xActionRoles['errorInfo']);

                            
                        
                        $xc = $this->deleteActionRrp(array('action_id' => $params['id'],
                            'op_user_id' => $opUserIdValue,
                        ));
                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                        
                        $xActionRrp = $this->insertActionPrivilegeRrp(array(
                                     'op_user_id' => intval($opUserIdValue),
                                     'action_id' => intval($params['id']),
                                     'role_ids' => $params['role_ids'],
                                     )
                         );

                        if ($xActionRrp['errorInfo'][0] != "00000" && $xActionRrp['errorInfo'][1] != NULL && $xActionRrp['errorInfo'][2] != NULL)
                            throw new \PDOException($xActionRrp['errorInfo']);
                         
                        
                        
                        $xcNewResourceName = $this->getResourceName(array('action_id' => $params['id'],));                        
                        if ($xcNewResourceName['errorInfo'][0] != "00000" && $xcNewResourceName['errorInfo'][1] != NULL && $xcNewResourceName['errorInfo'][2] != NULL)
                            throw new \PDOException($xcNewResourceName['errorInfo']);                              
                        if ($xcOldResourceName ['resultSet'][0]['resource_name'] != $xcNewResourceName ['resultSet'][0]['resource_name'] ) {                            
                            $xcx = $this->updateAclPrivilege(array(
                                'oldname' =>$xcOldResourceName ['resultSet'][0]['resource_name'],
                                'newname' =>$xcNewResourceName ['resultSet'][0]['resource_name'] ,
                                'op_user_id' => $opUserIdValue,
                            ));  
                            if ($xcx['errorInfo'][0] != "00000" && $xcx['errorInfo'][1] != NULL && $xcx['errorInfo'][2] != NULL)
                            throw new \PDOException($xcx['errorInfo']);
                        };
                            
                        
                        
                        // role değiştiyse eski roller ile yeni roller de aynı olanlar deleted yapılacak 
                        $xcx = $this->UpdateDeletedAclRrp(array(                               
                             'oldrole_ids' =>$xcOldActionRoleIds ['resultSet'][0]['role_ids'],
                             'newrole_ids' =>$params['role_ids'],
                             'oldname' =>$xcOldResourceName ['resultSet'][0]['resource_name'] ,
                             'op_user_id' => $opUserIdValue,
                         ));  

                         if ($xcx['errorInfo'][0] != "00000" && $xcx['errorInfo'][1] != NULL && $xcx['errorInfo'][2] != NULL)
                         throw new \PDOException($xcx['errorInfo']);

                          
                        // role değiştiyse eski roller ile yeni roller de aynı olanlar deleted yapılacak 
                        $xcx = $this->UpdateInsertAclRrp(array(                               
                             'oldrole_ids' =>$xcOldActionRoleIds ['resultSet'][0]['role_ids'],
                             'newrole_ids' =>$params['role_ids'],
                             'newname' =>$xcNewResourceName ['resultSet'][0]['resource_name'] ,
                             'op_user_id' => $opUserIdValue,
                         ));  

                         if ($xcx['errorInfo'][0] != "00000" && $xcx['errorInfo'][1] != NULL && $xcx['errorInfo'][2] != NULL)
                         throw new \PDOException($xcx['errorInfo']); 
                         
                         
                         
                        
                            
                        $pdo->commit();
                        return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
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
            } else {
                $xActionPrivilegesServicesValue = 0;
                if ((isset($xAclPrivileges['resultSet'][0]['adet']) && $xAclPrivileges['resultSet'][0]['adet'] != "")) {
                    $xActionPrivilegesServicesValue = intval($xAclPrivileges['resultSet'][0]['adet']);
                }
                
               // $xAclPrivilegesCountValue = $xAclPrivileges['resultSet'][0]['adet'];                
                $errorInfo = '23503';   // 23503  foreign_key_violation
                $errorInfoColumn = 'haveRecordsActionPrivilegRestServices';
                $count = $xActionPrivilegesServicesValue;
                $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn, "errorInfoColumnCount" => $count);
            }
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_action_resources tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  11.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updateModulActionResource($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                update sys_acl_action_resources
                SET name  = (   SELECT REPLACE(CONCAT(name,'-' ,'" .  $params['name'] . "' ),' ','')
                                FROM sys_acl_modules 
                                WHERE active =0 AND deleted =0 AND 
                                    id =  " . intval($params['module_id']) . "       
                                    ) ,
                    op_user_id = " . intval($params['op_user_id']) . "  
                WHERE 
                    action_id =  " . intval($params['action_id']) . " AND 
                    active =0 AND 
                    deleted =0                
                    ";
            $statement = $pdo->prepare($sql);
           // echo debugPDO($sql, $params);
            $result = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {                            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_privilege tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  15-08-2016
     * @return array
     * @throws \PDOException
     */
    public function updateAclPrivilege($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
                $sql = "
                UPDATE sys_acl_privilege
                SET 
                        name = '". $params['newname']."',
                        name_eng ='". $params['newname']."', 
                        op_user_id = ". intval($params['op_user_id'])."
                WHERE 
                        name = '". $params['oldname']."' AND 
                        resource_id = 24 AND                        
                        resource_type_id = 1 
                        ";
                    $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);                  
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);      
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_privilege tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18-08-2016
     * @return array
     * @throws \PDOException
     */
    public function deletedAclRrp($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
                $sql = "
                UPDATE sys_acl_rrp
                SET 
                    active = 1,
                    deleted = 1,
                    op_user_id = ". intval($params['op_user_id'])."
                WHERE 
                privilege_id = 
                    (SELECT id FROM sys_acl_privilege
                    WHERE 
                        name = (SELECT name FROM sys_acl_action_resources WHERE action_id = ". intval($params['action_id']).") AND
                        active =0 AND
                        deleted =0 AND
                        resource_id = 24 AND
                        resource_type_id = 1 LIMIT 1) AND 
                resource_id = 24 AND 
                active =0 AND 
                deleted =0 
                        ";
                    $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);                  
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);      
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    
    /**
     * @author Okan CIRAN
     * @ sys_acl_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  18-08-2016
     * @return array
     * @throws \PDOException
     */
    public function deletedAclPrivilege($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
                $sql = "
                UPDATE sys_acl_privilege
                SET 
                    active = 1,
                    deleted = 1,
                    op_user_id = ". intval($params['op_user_id'])."
                WHERE 
                id = 
                    (SELECT id FROM sys_acl_privilege
                    WHERE 
                        name = (SELECT name FROM sys_acl_action_resources WHERE action_id = ". intval($params['action_id']).") AND
                        active =0 AND
                        deleted =0 AND
                        resource_id = 24 AND
                        resource_type_id = 1 LIMIT 1) AND                 
                active =0 AND 
                deleted =0 
                        ";
                    $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);                  
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);      
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
  
    /**
     * @author Okan CIRAN
     * @ sys_acl_roles tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0  26.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecordsActionPrivilegRestServices($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $id =0;
            if (isset($params['id'])) {
                $id = intval($params['id']);
            }            
            $sql = "                 
            SELECT   
                saar.name AS name , 
                saar.name AS value , 
                saar.name = saar.name AS control,
                CONCAT(saar.name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message,
                (
                    SELECT  
                        count(rrpx.id)
                    FROM sys_acl_action_rrp rrpx  
                    INNER JOIN sys_action_privileges sapx ON sapx.id = rrpx.privilege_id AND   sapx.deleted =0 AND sapx.active =0  
                    INNER JOIN sys_acl_action_resources saarx ON saarx.action_id = saar.action_id AND saarx.deleted =0 AND saarx.active =0 AND rrpx.resource_id = saarx.id 
                    INNER JOIN sys_acl_action_rrp_restservices sarrx ON sarrx.rrp_id =rrpx.id AND sarrx.deleted =0 AND sarrx.active =0 
                    WHERE  
                     rrpx.deleted =0 
                ) AS adet
            FROM sys_acl_action_rrp rrp  
            INNER JOIN sys_action_privileges sap ON sap.id = rrp.privilege_id AND sap.deleted =0 AND sap.active =0  
            INNER JOIN sys_acl_action_resources saar ON saar.action_id = " . intval($id) . "  AND saar.deleted =0 AND saar.active =0 AND rrp.resource_id = saar.id 
            INNER JOIN sys_acl_action_rrp_restservices sarr ON sarr.rrp_id =rrp.id AND sarr.deleted =0 and sarr.active =0 
            WHERE 
		rrp.deleted =0
                LIMIT 1 
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
     * @ sys_acl_roles tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0  26.07.2016
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
                name as name , 
                '" . $params['name'] . "' as value , 
                name ='" . $params['name'] . "' as control,
                concat(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message                             
            FROM sys_acl_actions                
            WHERE LOWER(REPLACE(name,' ','')) = LOWER(REPLACE('" . $params['name'] . "',' ','')) AND
                module_id = ".intval($params['module_id'])."                      
                " . $addSql . " 
               AND deleted =0   
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
     * @ Gridi doldurmak için sys_acl_actions tablosundan kayıtları döndürür !!
     * @version v 1.0  26.07.2016
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
            $sort = "sam.name, a.name";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
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
                    a.name AS name,
                    sam.id AS module_id,
                    sam.name AS module_name,
                    a.c_date AS create_date,
                    a.deleted,
                    sd.description AS state_deleted,
                    a.active,
                    sd1.description AS state_active,
                    a.description,
                    a.op_user_id,
                    u.username
                FROM sys_acl_actions a
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0
                INNER JOIN sys_acl_modules sam ON sam.id = a.module_id AND sam.deleted = 0 AND sam.active = 0
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id
                WHERE a.deleted =0 
                ORDER BY " . $sort . " "
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
     * @ Gridi doldurmak için sys_acl_actions tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSQL = ' WHERE a. deleted =0 ';
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT
                FROM sys_acl_actions a                                
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0                
                INNER JOIN sys_acl_modules sam ON sam.id = a.module_id AND sam.deleted = 0 AND sam.active = 0
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id  
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
     * @ combobox doldurmak için sys_acl_actions tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  26.07.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function fillComboBoxFullAction($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT 
                    a.id,
                    a.name AS name,
                    'open' AS state_type,
                    a.active
                FROM sys_acl_actions a
                WHERE
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
     * @ tree doldurmak için sys_acl_actions tablosundan tüm kayıtları döndürür !!
      * @version v 1.0  26.07.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function fillActionTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $id = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $id = $params['id'];
            }
            $sql = " 
                SELECT
                    a.id,
                    a.name AS name,
                    'open' AS state_type,
                    a.active
                FROM sys_acl_actions a
                WHERE                    
                    a.deleted = 0
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
     * @ sys_acl_actions bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionList($params = array()) {
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
                $sort = " sam.name, a.name";
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
                                $sorguStr.=" AND a.name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;
                            case 'module_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sam.name" . $sorguExpression . ' ';

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
            
            $sorguStr2 = null;
            if (isset($params['name']) && $params['name'] != "") {
                $sorguStr2 .= " AND a.name Like '%" . $params['name'] . "%'";
            }
            if (isset($params['description']) && $params['description'] != "") {
                $sorguStr2 .= " AND a.description Like '%" . $params['description'] . "%'";
            }
            if (isset($params['active']) && $params['active'] != "") {
                $sorguStr2 .= " AND a.active = " . $params['active'] ;
            }
            if (isset($params['module_id']) && $params['module_id'] != "") {
                $sorguStr2 .= " AND sam.id = " . $params['module_id'] ;
            }
            
              $jsonSqlRoleIds = "  
                (SELECT array_to_json(COALESCE(NULLIF(cxx,'{}'),NULL)) FROM (
                    SELECT  
                        ARRAY(   
                            SELECT
                                axv.role_id                             
                            FROM sys_acl_actions_roles axv
                            LEFT join sys_acl_action_resources bb ON bb.id = axv.action_id AND bb.active=0 AND bb.deleted =0
                            WHERE axv.action_id = a.id AND axv.active =0 AND axv.deleted =0
                            ORDER BY axv.action_id) AS cxx
                            ) AS zxtable)
            ";
            
            $sql = "                 
		SELECT 
                    a.id,
                    a.name AS name,   
                    sam.id AS module_id,
                    sam.name AS module_name,
                    a.c_date AS create_date,
                    a.deleted,
                    sd.description AS state_deleted,
                    a.active,
                    sd1.description AS state_active,
                    a.description,
                    a.op_user_id,
                    u.username AS op_user_name,
                   ". $jsonSqlRoleIds." AS role_ids
                FROM sys_acl_actions a
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0
                INNER JOIN sys_acl_modules sam ON sam.id = a.module_id AND sam.deleted = 0 AND sam.active = 0
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id 
                WHERE a.deleted =0 
                " . $sorguStr . "
                " . $sorguStr2 . "
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
     * @ sys_acl_actions bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionListRtc($params = array()) {
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
                                $sorguStr.=" AND a.name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;
                            case 'module_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sam.name" . $sorguExpression . ' ';

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
             $sorguStr2 = null;
            if (isset($params['name']) && $params['name'] != "") {
                $sorguStr2 .= " AND a.name Like '%" . $params['name'] . "%'";
            }
            if (isset($params['description']) && $params['description'] != "") {
                $sorguStr2 .= " AND a.description Like '%" . $params['description'] . "%'";
            }
            if (isset($params['active']) && $params['active'] != "") {
                $sorguStr2 .= " AND a.active = " . $params['active'] ;
            }
            if (isset($params['module_id']) && $params['module_id'] != "") {
                $sorguStr2 .= " AND sam.id = " . $params['module_id'] ;
            }
            $sql = "   
                SELECT COUNT(id) AS count 
                FROM (
                    SELECT id,name,deleted,active,description,state_deleted,state_active,module_id,module_name
                    FROM (
                        SELECT 
                            a.id,
                            a.name AS name,   
                            sam.id AS module_id,
                            sam.name AS module_name,                     
                            a.c_date AS create_date,                        
                            a.deleted,
                            sd.description AS state_deleted,
                            a.active,
                            sd1.description AS state_active,
                            a.description,
                            a.op_user_id,
                            u.username AS op_user_name
                        FROM sys_acl_actions a                                
                        INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0                
                        INNER JOIN sys_acl_modules sam ON sam.id = a.module_id AND sam.deleted = 0 AND sam.active = 0
                        INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                        INNER JOIN info_users u ON u.id = a.op_user_id       
                        WHERE a.deleted =0 
                        " . $sorguStr . "
                        " . $sorguStr2 . "
                    ) as xtable      
                ) AS xxTable    
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
     * @ sys_acl_actions tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  26.07.2016
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
                UPDATE sys_acl_actions
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_acl_actions
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
     * @ sys_acl_actions tablosundan kayıtları döndürür !!
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillActionDdList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("        
               SELECT                    
                    a.id, 	
                    a.name,  
                    a.description,                                    
                    a.active,
                    'open' AS state_type  
	         FROM sys_acl_actions a    
                 WHERE                    
                    a.deleted = 0                    
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
     * @ sys_acl_menu_types_actions tablosunda action_id li menu_type_id daha önce kaydedilmiş mi ?  
     * @version v 1.0  26.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveMenuTypeRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                             
            $sql = " 
               SELECT  
                a.action_id AS name ,             
                a.action_id = " . $params['id'] . " AS control,
                'Bu Action Altında Menu Tipi Kaydı Bulunmakta. Lütfen Kontrol Ediniz !!!' AS message   
            FROM sys_acl_menu_types_actions  a                          
            WHERE a.action_id = ".$params['id']. "
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
 
                             
                            
    
    
}
