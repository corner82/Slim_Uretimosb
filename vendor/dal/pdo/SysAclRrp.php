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
class SysAclRrp extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_acl_rrp tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  15.07.2016
     * @param type $params
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
                UPDATE sys_acl_rrp
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id = " . intval($params['id'])
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
     * @ sys_acl_rrp tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  15.07.2016 
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
                    a.resource_id,    
                    sare.name AS resource_name,
                    a.role_id,
                    saro.name AS role_name,
                    saro.name_tr AS role_name_tr,                    
                    a.privilege_id,
                    sap.name AS privilege_name,
                    sap.name_eng AS privilege_name_eng,
                    a.c_date AS create_date,
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active,                          
                    a.op_user_id,
                    u.username                
                FROM sys_acl_rrp a
                INNER JOIN sys_acl_resources sare ON sare.id = a.resource_id 
		INNER JOIN sys_acl_roles saro ON saro.id = a.role_id 
		INNER JOIN sys_acl_privilege sap ON sap.id = a.privilege_id 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id    
                WHERE a.deleted =0  
                ORDER BY  sare.name, saro.name, sap.name                  
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
     * @ sys_acl_rrp tablosunda name sutununda daha önce oluşturulmuş mu? 
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
            $sql = " 
           SELECT  
                privilege_id as name , 
                '" . $params['privilege_id'] . "' as value , 
                privilege_id =" . intval($params['privilege_id']) . " as control,
                concat(privilege_id , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message                             
            FROM sys_acl_rrp        
            WHERE 
                role_id  = " . intval($params['role_id']) . " AND
                resource_id  = " . intval($params['resource_id']) . " AND
                privilege_id  = " . intval($params['privilege_id']) . " 
                " . $addSql . " 
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
     * @ sys_acl_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  15.07.2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $sql = "
                INSERT INTO sys_acl_rrp(
                        role_id, 
                        resource_id, 
                        privilege_id,
                        op_user_id
                        )
                VALUES (
                        " . intval($params['role_id']) . ", 
                        " . intval($params['resource_id']) . ",
                        " . intval($params['privilege_id']) . ",
                        " . intval($opUserIdValue) . "
                    )";
                    $statement = $pdo->prepare($sql);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_rrp_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';     // 23505 	unique_violation
                    $errorInfoColumn = 'privilege_id';
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
     * sys_acl_rrp tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  15.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $sql = "
                UPDATE sys_acl_rrp
                SET  
                    role_id= " . intval($params['role_id']) . ",
                    resource_id= " . intval($params['resource_id']) . ",
                    privilege_id= " . intval($params['privilege_id']) . ",
                    op_user_id= " . intval($opUserIdValue) . " 
                WHERE id = " . intval($params['id']
                    );
                    $statement = $pdo->prepare($sql);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    $errorInfo = '23505';     // 23505 	unique_violation
                    $errorInfoColumn = 'privilege_id';
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
     * @ Gridi doldurmak için sys_acl_rrp tablosundan kayıtları döndürür !!
     * @version v 1.0  15.07.2016
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
            $sort = "sare.name, saro.name, sap.name";
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
                        a.resource_id,    
                        sare.name AS resource_name,
                        a.role_id,
                        saro.name AS role_name,
                        saro.name_tr AS role_name_tr,                    
                        a.privilege_id,
                        sap.name AS privilege_name,
                        sap.name_eng AS privilege_name_eng,
                        a.c_date AS create_date,
                        a.deleted, 
                        sd15.description AS state_deleted,                 
                        a.active, 
                        sd16.description AS state_active,                          
                        a.op_user_id,
                        u.username                 
                FROM sys_acl_rrp a
                INNER JOIN sys_acl_resources sare ON sare.id = a.resource_id 
		INNER JOIN sys_acl_roles saro ON saro.id = a.role_id 
		INNER JOIN sys_acl_privilege sap ON sap.id = a.privilege_id 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
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
     * @ Gridi doldurmak için sys_acl_rrp tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  15.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $whereSQL = ' WHERE a.deleted =0 ';

            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_acl_rrp a
                INNER JOIN sys_acl_resources sare ON sare.id = a.resource_id 
		INNER JOIN sys_acl_roles saro ON saro.id = a.role_id 
		INNER JOIN sys_acl_privilege sap ON sap.id = a.privilege_id 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
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
     * @ sys_acl_rrp  bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  14.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRrpList($params = array()) {
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
                $sort = "resource_name,role_name,privilege_name";
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
                            case 'privilege_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND privilege_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';

                                break;
                            case 'role_name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name_tr" . $sorguExpression . ' ';

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
                if (isset($params['privilege_name']) && $params['privilege_name'] != "") {
                    $sorguStr .= " AND privilege_name Like '%" . $params['privilege_name'] . "%'";
                }
                if (isset($params['privilege_name_eng']) && $params['privilege_name_eng'] != "") {
                    $sorguStr .= " AND privilege_name_eng Like '%" . $params['privilege_name_eng'] . "%'";
                }
                if (isset($params['role_name']) && $params['role_name'] != "") {
                    $sorguStr .= " AND role_name Like '%" . $params['role_name'] . "%'";
                }
                if (isset($params['role_name_tr']) && $params['role_name_tr'] != "") {
                    $sorguStr .= " AND role_name_tr Like '%" . $params['role_name_tr'] . "%'";
                }
                if (isset($params['resource_name']) && $params['resource_name'] != "") {
                    $sorguStr .= " AND resource_name Like '%" . $params['resource_name'] . "%'";
                }
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "  
                SELECT  
                    id, 
                    resource_id,    
                    resource_name,
                    role_id,
                    role_name,
                    role_name_tr,                     
                    privilege_id,
                    privilege_name,
                    privilege_name_eng,
                    create_date,
                    deleted, 
                    state_deleted,                 
                    active, 
                    state_active,                          
                    op_user_id,
                    username      
                    FROM (
                        SELECT 
                            a.id, 
                            a.resource_id,    
                            sare.name AS resource_name,
                            a.role_id,
                            saro.name AS role_name,
                            saro.name_tr AS role_name_tr,                    
                            a.privilege_id,
                            sap.name AS privilege_name,
                            sap.name_eng AS privilege_name_eng,
                            a.c_date AS create_date,
                            a.deleted, 
                            sd15.description AS state_deleted,                 
                            a.active, 
                            sd16.description AS state_active,                          
                            a.op_user_id,
                            u.username                    
                        FROM sys_acl_rrp a
                        INNER JOIN sys_acl_resources sare ON sare.id = a.resource_id 
                        INNER JOIN sys_acl_roles saro ON saro.id = a.role_id 
                        INNER JOIN sys_acl_privilege sap ON sap.id = a.privilege_id 
                        INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        WHERE a.deleted =0 
                        ) AS xTable  
                        WHERE deleted =0 
                " . $sorguStr . "
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
     * @ sys_acl_rrp bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  14.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRrpListRtc($params = array()) {
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
                            case 'privilege_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND privilege_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';

                                break;
                            case 'role_name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name_tr" . $sorguExpression . ' ';

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
                if (isset($params['privilege_name']) && $params['privilege_name'] != "") {
                    $sorguStr .= " AND privilege_name Like '%" . $params['privilege_name'] . "%'";
                }
                if (isset($params['privilege_name_eng']) && $params['privilege_name_eng'] != "") {
                    $sorguStr .= " AND privilege_name_eng Like '%" . $params['privilege_name_eng'] . "%'";
                }
                if (isset($params['role_name']) && $params['role_name'] != "") {
                    $sorguStr .= " AND role_name Like '%" . $params['role_name'] . "%'";
                }
                if (isset($params['role_name_tr']) && $params['role_name_tr'] != "") {
                    $sorguStr .= " AND role_name_tr Like '%" . $params['role_name_tr'] . "%'";
                }
                if (isset($params['resource_name']) && $params['resource_name'] != "") {
                    $sorguStr .= " AND resource_name Like '%" . $params['resource_name'] . "%'";
                }
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "
                SELECT COUNT(id) AS count FROM (
                    SELECT  
                        id, 
                        resource_id,    
                        resource_name,
                        role_id,
                        role_name,
                        role_name_tr,                     
                        privilege_id,
                        privilege_name,
                        privilege_name_eng,
                        create_date,
                        deleted, 
                        state_deleted,                 
                        active, 
                        state_active,                          
                        op_user_id,
                        username      
                    FROM (
                            SELECT 
                                a.id, 
                                a.resource_id,    
                                sare.name AS resource_name,
                                a.role_id,
                                saro.name AS role_name,
                                saro.name_tr AS role_name_tr,                    
                                a.privilege_id,
                                sap.name AS privilege_name,
                                sap.name_eng AS privilege_name_eng,
                                a.c_date AS create_date,
                                a.deleted, 
                                sd15.description AS state_deleted,                 
                                a.active, 
                                sd16.description AS state_active,                          
                                a.op_user_id,
                                u.username                    
                            FROM sys_acl_rrp a
                            INNER JOIN sys_acl_resources sare ON sare.id = a.resource_id 
                            INNER JOIN sys_acl_roles saro ON saro.id = a.role_id 
                            INNER JOIN sys_acl_privilege sap ON sap.id = a.privilege_id 
                            INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                            INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                            INNER JOIN info_users u ON u.id = a.op_user_id 
                            WHERE a.deleted =0 
                        ) AS xTable 
                        WHERE deleted =0 
                        " . $sorguStr . "
                    ) AS xxtable 
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
     * @ sys_acl_rrp tablosundan parametre olarak  gelen id kaydın aktifliğini
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
                UPDATE sys_acl_rrp
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_acl_rrp
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
     * @ sys_acl_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  15.07.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function transferRolesPrivilege($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $sql = "  
               INSERT INTO sys_acl_rrp(
                        role_id, 
                        resource_id, 
                        privilege_id,
                        op_user_id
                        )
                VALUES (
                        " . intval($params['role_id']) . ", 
                        " . intval($params['resource_id']) . ",
                        " . intval($params['privilege_id']) . ",
                        " . intval($opUserIdValue) . "
                    )";
                    $statement = $pdo->prepare($sql);
                   //  echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_rrp_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'privilege_id';
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

    
    
}
