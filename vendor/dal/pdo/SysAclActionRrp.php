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
class SysAclActionRrp extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_acl_action_rrp tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  11-08-2016
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
                UPDATE sys_acl_action_rrp
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
     * @ sys_acl_action_rrp tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  11-08-2016 
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
                    a.name_eng,
                    a.resource_id,
                    sare.name AS resource_name,
                    a.c_date AS create_date,
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active,  
                    a.description,                                     
                    a.op_user_id,
                    u.username                    
                FROM sys_acl_action_rrp a
                LEFT JOIN sys_acl_action_resources sare ON sare.id = a.resource_id 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
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
     * @ sys_acl_action_rrp tablosunda name sutununda daha önce oluşturulmuş mu? 
     * @version v 1.0 11.08.2016
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
            FROM sys_acl_action_rrp        
            WHERE LOWER(REPLACE(name,' ','')) = LOWER(REPLACE('" . $params['name'] . "',' ','')) 
                AND resource_id = ".intval($params['resource_id'])."
                ". $addSql . " 
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
     * @ sys_acl_action_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  11-08-2016
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
                INSERT INTO sys_acl_action_rrp(                         
                        name,
                        name_eng,
                        resource_id,
                        op_user_id, 
                        description
                        )
                VALUES (
                        :name, 
                        :name_eng,
                        :resource_id,
                        ". intval($opUserIdValue).",
                        :description                      
                                                )";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':resource_id', $params['resource_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                   // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_action_rrp_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';     // 23505 	unique_violation
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
     * sys_acl_action_rrp tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  11-08-2016
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
                    $statement = $pdo->prepare("
                UPDATE sys_acl_action_rrp
                SET   
                    name = :name, 
                    name_eng = :name_eng, 
                    resource_id = :resource_id,
                    op_user_id=   ". intval($opUserIdValue).", 
                    description = :description                                           
                WHERE id = :id");
                    $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
                    $statement->bindValue(':name_eng', $params['name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);                    
                    $statement->bindValue(':resource_id', $params['resource_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    $errorInfo = '23505';     // 23505 	unique_violation
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
     * @ Gridi doldurmak için sys_acl_action_rrp tablosundan kayıtları döndürür !!
     * @version v 1.0  11-08-2016
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
                    a.name_eng,
                    a.resource_id,
                    sare.name AS resource_name,
                    a.c_date AS create_date,
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active,  
                    a.description,                                     
                    a.op_user_id,
                    u.username                    
                FROM sys_acl_action_rrp a
                LEFT JOIN sys_acl_resources sare ON sare.id = a.resource_id 
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
     * @ Gridi doldurmak için sys_acl_action_rrp tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  11-08-2016
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
                FROM sys_acl_action_rrp a
                LEFT JOIN sys_acl_resources sare ON sare.id = a.resource_id 
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
     * @ privilege  bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionPrivilegesList($params = array()) {
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
                $sort = "name";
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
                            case 'name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name_eng" . $sorguExpression . ' ';

                                break;
                            case 'name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

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
                if (isset($params['name_eng']) && $params['name_eng'] != "") {
                    $sorguStr .= " AND name_eng Like '%" . $params['name_eng'] . "%'";
                }
                if (isset($params['name']) && $params['name'] != "") {
                    $sorguStr .= " AND name Like '%" . $params['name'] . "%'";
                }
                if (isset($params['description']) && $params['description'] != "") {
                    $sorguStr .= " AND description Like '%" . $params['description'] . "%'";
                }
                if (isset($params['resource_name']) && $params['resource_name'] != "") {
                    $sorguStr .= " AND resource_name Like '%" . $params['resource_name'] . "%'";
                }
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "  
                SELECT  
                    id,
                    name, 
                    name_eng,
                    resource_id,
                    resource_name,
                    deleted,
                    state_deleted,
                    active,
                    state_active,
                    COALESCE(NULLIF(description, ''),' ') AS description 
                    FROM (
                        SELECT 
                            a.id,                       
                            a.name AS name,
                            a.name_eng,
                            a.resource_id,
                            sare.name AS resource_name,
                            a.c_date AS create_date,                            
                            a.active, 
                            sd16.description AS state_active,  
                            a.description,                                     
                            a.op_user_id,
                            u.username                    
                        FROM sys_acl_action_rrp a
                        LEFT JOIN sys_acl_action_resources sare ON sare.id = a.resource_id                         
                        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                        INNER JOIN info_users u ON u.id = a.op_user_id    
                        WHERE a.deleted =0 
                        ) AS xTable  WHERE deleted =0 
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
     * @ privilege bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillPrivilegesListRtc($params = array()) {
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
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

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
                if (isset($params['name_eng']) && $params['name_eng'] != "") {
                    $sorguStr .= " AND name_eng Like '%" . $params['name_eng'] . "%'";
                }
                if (isset($params['description']) && $params['description'] != "") {
                    $sorguStr .= " AND description Like '%" . $params['description'] . "%'";
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
                        name, 
                        name_eng,
                        resource_id,
                        resource_name,
                        deleted,
                        state_deleted,
                        active,
                        state_active,
                        description FROM (
                             SELECT 
                                a.id,                       
                                a.name AS name,
                                a.name_eng,
                                a.resource_id,
                                sare.name AS resource_name,
                                a.c_date AS create_date,                            
                                a.active, 
                                sd16.description AS state_active,  
                                a.description,                                     
                                a.op_user_id,
                                u.username                    
                            FROM sys_acl_action_rrp a
                            LEFT JOIN sys_acl_action_resources sare ON sare.id = a.resource_id                         
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
     * @ tree doldurmak için sys_acl_action_rrp tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionResourceGroups($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
                            
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            }
            $sql = "                
               SELECT                    
                    sare.id,                     
                    sare.name ,
                    sare.parent_id,
                    sare.active ,
                    CASE
                        (CASE 
                            (SELECT DISTINCT 1 state_type FROM sys_acl_action_resources xz WHERE xz.parent_id = sare.id AND xz.deleted = 0)    
                             WHEN 1 THEN 'closed'
                             ELSE 'open'   
                             END ) 
                         WHEN 'open' THEN COALESCE(NULLIF((SELECT DISTINCT 'closed' FROM sys_acl_actions_roles mz WHERE mz.action_id =sare.action_id AND mz.deleted = 0), ''), 'open')   
                    ELSE 'closed'
                    END AS state_type,
                    CASE
                        (SELECT DISTINCT 1 parent_id FROM sys_acl_action_resources WHERE id = sare.id AND deleted = 0 AND parent_id =0 )    
                        WHEN 1 THEN 'true'
                    ELSE 'false'   
                    END AS root_type,             
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_action_resources WHERE parent_id = sare.id AND deleted = 0)    
                         WHEN 1 THEN 'false'			 
                    ELSE 'true'   
                    END AS last_node,
                    'false' AS roles,
                    sare.id AS resource_id
                FROM sys_acl_action_resources sare  
                WHERE                    
                    sare.parent_id = " .intval($parentId) . " AND                 
                    sare.deleted = 0  
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
     * @ tree doldurmak için sys_acl_action_rrp tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionResourceGroupsPrivileges($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                            
            $addSql = null;
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            }
            $roleId = 0;
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $roleId = $params['role_id'];
                $addSql = "  AND a.role_id = " .intval($roleId);
            }
            $sql =" 
                 SELECT                    
                    mt.role_id as id , 
                    COALESCE(NULLIF( (rol.name), ''), rol.name_tr) AS name,            
                    -1 AS parent_id,
                    a.active ,
                    'open' AS state_type,                                          
                    'false' AS root_type,
                    Null AS icon_class,
                    'true' AS last_node,
                    'true' AS privilege  ,
                    true AS roles,
                    a.id AS resource_id
                FROM sys_acl_action_resources a                 
		INNER join sys_acl_actions_roles mt ON mt.action_id = a.action_id AND mt.active =0 AND mt.deleted =0                 
		INNER join sys_acl_roles rol ON rol.id = mt.role_id AND rol.active =0 AND rol.deleted =0                 
                WHERE                    
                   a.id = " .intval($parentId) . " AND 
                   ".$addSql."
                   a.deleted = 0 AND
                   a.active =0 
                ORDER BY name    
                                 ";
             $statement = $pdo->prepare( $sql);
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
     * @version v 1.0  11.08.2016
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
                UPDATE sys_acl_action_rrp
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_acl_action_rrp
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
     * @ sys_acl_action_rrp tablosundan parametre ile
     *  istenen kayıtları döndürür !!  
     * @version v 1.0  11.08.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionPrivilegesOfRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $RoleId = 0;
            $whereSql = "  WHERE a.deleted =0 AND a.active =0 ";
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $RoleId = $params['role_id'];
            }
            $whereSql .= " AND rrp.role_id  = " . intval($RoleId); 
            
            $ResourceId = 0;
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $ResourceId = $params['resource_id'];               
            }
            $whereSql .= " AND rrp.resource_id = " . intval($ResourceId); 
                            
            $sql ="             
                SELECT
                    rrp.id,
                    rrp.resource_id, 
		    rrp.role_id AS role_id,
                    a.id AS privilege_id,
                    a.name AS privilege_name, 
                    a.active,
                    'open' AS state_type,
                    false AS root_type
		FROM sys_action_privileges a
                INNER JOIN sys_acl_action_resources sare ON sare.active =0 AND sare.deleted =0
                INNER JOIN sys_acl_actions_roles sarr ON sarr.active =0 AND sarr.deleted =0 AND sare.action_id = sarr.action_id
                INNER JOIN sys_acl_action_rrp rrp ON rrp.role_id = sarr.role_id AND rrp.resource_id= sare.id AND rrp.privilege_id = a.id AND rrp.active =0 AND rrp.deleted =0 
                " . $whereSql . "		
                ORDER BY privilege_name
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
     * @ sys_action_privileges tablosundan role_id si dısında kalan privilege leri 
     * döndürür !!   
     * @version v 1.0  11.08.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillNotInActionPrivilegesOfRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $RoleId = 0;    
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $RoleId = $params['role_id'];
            }    
            $ResourceId = 0;
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $ResourceId = $params['resource_id'];               
            }          

            $sql =" 
                SELECT DISTINCT 
                    NULL AS id ,
                    NULL AS resource_id, 
		    NULL AS role_id,
                    a.id AS privilege_id,
                    a.name AS privilege_name, 
                    a.active,
                    'open' AS state_type,
                    false AS root_type
		FROM sys_action_privileges a
                WHERE 
                    a.active =0 AND 
                    a.deleted =0 AND
                    id NOT IN ( 
                        SELECT DISTINCT     
                            a.id AS privilege_id  
                        FROM sys_action_privileges a
                        INNER JOIN sys_acl_action_resources sare ON sare.active =0 AND sare.deleted =0                 
                        INNER JOIN sys_acl_actions_roles sarr ON sarr.active =0 AND sarr.deleted =0		
                        INNER JOIN sys_acl_action_rrp rrp ON rrp.role_id = sarr.role_id AND rrp.resource_id= sare.id AND rrp.privilege_id = a.id AND rrp.active =0 AND rrp.deleted =0   
                        WHERE 
                            a.deleted =0 AND 
                            a.active =0 AND 
                            rrp.role_id =  ".intval($RoleId)." AND 
                            rrp.resource_id = " . intval($ResourceId)."    
                    )                
                ORDER BY privilege_name     
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
     * @ sys_acl_action_rrp tablosundan role_id si verilen kayıtları döndürür !!  
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillActionPrivilegesOfRolesDdList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $RoleId = 0;
            $whereSql = "  WHERE a.deleted =0 ";
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $RoleId = $params['role_id'];
            }
            $whereSql .= " AND saro.id  = " . $RoleId; 
            $statement = $pdo->prepare("        
               SELECT                    
                    a.id, 	
                    a.name,  
                    a.description,                                    
                    a.active,
                    'open' AS state_type  
	        FROM sys_acl_action_rrp a
                INNER JOIN sys_acl_resources sare ON sare.id = a.resource_id AND sare.active =0 AND sare.deleted =0                 
                INNER JOIN sys_acl_roles saro ON saro.resource_id = sare.id AND saro.active =0 AND saro.deleted =0
                INNER JOIN sys_acl_rrp rrp ON rrp.role_id = saro.id AND rrp.resource_id= sare.id AND rrp.privilege_id = a.id AND rrp.active =0 AND rrp.deleted =0
                " . $whereSql . "
                ORDER BY privilege_name
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
     * @ sys_acl_action_rrp bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillActionPrivilegesOfRolesList($params = array()) {
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
                $sort = " resource_name, role_name, privilege_name ";
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
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

                                break;                         
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;                            
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
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
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            
            $sorguStr2 = null;                            
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND role_id = " . $params['role_id'] ;
            }
                              
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND resource_id = " . $params['resource_id'] ;
            }
                            
            
            $sql = " 
                SELECT
                    id, 
                    privilege_id,
                    privilege_name,                    
                    resource_id, 
                    resource_name,
                    role_id,  
                    role_name,  
                    role_name_tr,
                    active ,
                    deleted,
                    id AS rrp_id
                    FROM (                        
                    SELECT
                           rrp.id,
                           rrp.resource_id,                            
                           a.id AS privilege_id,
                           a.name AS privilege_name, 
                           sare.name AS resource_name,
                           sarr.role_id AS role_id,  
                           saro.name AS role_name, 
                           saro.name_tr AS role_name_tr,
                           a.active,
                           a.deleted 
                       FROM sys_action_privileges a		
                       INNER JOIN sys_acl_action_resources sare ON sare.active =0 AND sare.deleted =0
                       INNER JOIN sys_acl_actions_roles sarr ON sarr.active =0 AND sarr.deleted =0 AND sare.action_id = sarr.action_id
                       INNER JOIN sys_acl_roles saro ON saro.id = sarr.role_id AND saro.active =0 AND saro.deleted =0
                       INNER JOIN sys_acl_action_rrp rrp ON rrp.role_id = sarr.role_id AND rrp.resource_id= sare.id AND rrp.privilege_id = a.id AND rrp.active =0 AND rrp.deleted =0 
                       WHERE a.deleted =0 AND a.active =0
                    ) AS xtable WHERE deleted=0  
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
     * @ sys_acl_action_rrp bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  11.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                       
    public function fillActionPrivilegesOfRolesListRtc($params = array()) {
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
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND privilege_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;                            
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
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
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            
            $sorguStr2 = null;                            
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND role_id = " . $params['role_id'] ;
            }                                   
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND resource_id = " . $params['resource_id'] ;
            }
            
            $sql = " SELECT count(id) FROM (
                SELECT
                    id, 
                    privilege_id,
                    privilege_name,                    
                    resource_id, 
                    resource_name,
                    role_id,  
                    role_name,  
                    role_name_tr,
                    active ,
                    deleted,
                    id AS rrp_id
                    FROM (
                         SELECT
                           rrp.id,
                           rrp.resource_id,                            
                           a.id AS privilege_id,
                           a.name AS privilege_name, 
                           sare.name AS resource_name,
                           sarr.role_id AS role_id,  
                           saro.name AS role_name, 
                           saro.name_tr AS role_name_tr,
                           a.active,
                           a.deleted 
                       FROM sys_action_privileges a		
                       INNER JOIN sys_acl_action_resources sare ON sare.active =0 AND sare.deleted =0
                       INNER JOIN sys_acl_actions_roles sarr ON sarr.active =0 AND sarr.deleted =0 AND sare.action_id = sarr.action_id
                       INNER JOIN sys_acl_roles saro ON saro.id = sarr.role_id AND saro.active =0 AND saro.deleted =0
                       INNER JOIN sys_acl_action_rrp rrp ON rrp.role_id = sarr.role_id AND rrp.resource_id= sare.id AND rrp.privilege_id = a.id AND rrp.active =0 AND rrp.deleted =0 
                       WHERE a.deleted =0 AND a.active =0
                    ) AS xtable WHERE deleted=0   
                    " . $sorguStr . "
                    " . $sorguStr2 . " 
                        ) AS xtable
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
     * @ sys_acl_action_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  13.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function transferRolesActionPrivilege($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                
                    $sql = "  
               INSERT INTO sys_acl_action_rrp(
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
                    $insertID = $pdo->lastInsertId('sys_acl_action_rrp_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                              
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
