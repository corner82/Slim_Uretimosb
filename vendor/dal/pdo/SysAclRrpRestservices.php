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
class SysAclRrpRestservices extends \DAL\DalSlim {

    /**     
     * @author Okan CIRAN
     * @ sys_acl_rrp_restservices tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  27.07.2016
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
                $opId = $this->haveActionRecords(array('id' => $params['id']));
                if (!\Utill\Dal\Helper::haveRecord($opId)) {
                    $statement = $pdo->prepare(" 
                UPDATE sys_acl_rrp_restservices
                SET deleted= 1, active = 1,
                    op_user_id = " . intval($opUserIdValue) . "
                WHERE id = " . intval($params['id']));
                    $update = $statement->execute();
                    $afterRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
                } else {
                    $errorInfo = '23503';   // 23503  foreign_key_violation
                    $errorInfoColumn = 'restservices_id';
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                }
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
     * @ sys_acl_rrp_restservices tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  27.07.2016  
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
                a.rrp_id,
                a.restservice,
                concat( rr.name,' - ',  rs.name,' - ' ,  rp.name , ' map ') AS map_adi,
                rrp.role_id, 
                rr.name AS role_name,
                rrp.resource_id, 
                rs.name AS resource_name,
                rrp.privilege_id,
                rp.name AS privilege_name,		 
                a.c_date AS create_date,		    
                a.deleted, 
                sd.description AS state_deleted,                 
                a.active, 
                sd1.description AS state_active,  
                a.description,                                     
                a.op_user_id,
                u.username AS op_user_name
            FROM sys_acl_rrp_restservices  a
            INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0 
            INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = 647 AND sd.deleted = 0 AND sd.active = 0
            INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
            INNER JOIN info_users u ON u.id = a.op_user_id 
            INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
            INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
            INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0             
            WHERE a.deleted =0 AND a.active =0
            ORDER BY map_adi,a.restservice
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
     * @ sys_acl_rrp_restservices tablosunda role_id, resource_id ve privilege_id aynı kayıtta daha önce oluşturulmuş mu? 
     * @version v 1.0 27.07.2016
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
                restservices_id AS name, 
                '" . $params['restservices_id'] . "' AS value, 
                restservices_id ='" . $params['restservices_id'] . "' AS control,
                concat(restservices_id , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_acl_rrp_restservices
            WHERE restservices_id = " . intval($params['restservices_id']) . " 
                AND rrp_id = " . intval($params['rrp_id']) . " 
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
     * @ sys_acl_rrp_restservices tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  27.07.2016
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
                INSERT INTO sys_acl_rrp_restservices(
                       rrp_id, restservices_id, description,op_user_id)
                VALUES (
                        " . intval($params['rrp_id']) . ",
                        " . intval($params['restservices_id']) . ",
                        '" . $params['description'] . "',
                        " . intval($opUserIdValue) . " 
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_rrp_restservices_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'restservices_id';
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
     * sys_acl_rrp_restservices tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  27.07.2016
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
                UPDATE sys_acl_rrp_restservices
                SET  
                    rrp_id = " . intval($params['rrp_id']) . ",
                    restservices_id = " . intval($params['restservices_id']) . ",
                    description = '" . $params['description'] . "',
                    op_user_id = " . intval($opUserIdValue) . "
                WHERE id = " . intval($params['id']) ;
                    $statement = $pdo->prepare($sql);
                    //  echo debugPDO($sql, $params);          
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'restservice';
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
     * @ Gridi doldurmak için sys_acl_rrp_restservices tablosundan kayıtları döndürür !!
     * @version v 1.0  27.07.2016
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
            $sort = "rr.name,rs.name,rp.name,a.restservice";
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
                    a.rrp_id,
                    a.restservice,
                    concat( rr.name,' - ',  rs.name,' - ' ,  rp.name , ' map ') AS map_adi,
                    rrp.role_id, 
                    rr.name AS role_name,
                    rrp.resource_id, 
                    rs.name AS resource_name,
                    rrp.privilege_id,
                    rp.name AS privilege_name,		 
                    a.c_date AS create_date,		    
                    a.deleted, 
                    sd.description AS state_deleted,                 
                    a.active, 
                    sd1.description AS state_active,  
                    a.description,                                     
                    a.op_user_id,
                    u.username AS op_user_name
                FROM sys_acl_rrp_restservices  a
                INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0 
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = 647 AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
                INNER JOIN info_users u ON u.id = a.op_user_id 
                INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0             
                WHERE a.deleted =0 AND a.active =0
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
     * @ Gridi doldurmak için sys_acl_rrp_restservices tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  27.07.2016
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
                FROM sys_acl_rrp_restservices  a
                INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0 
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = 647 AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
                INNER JOIN info_users u ON u.id = a.op_user_id 
                INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0             
                WHERE a.deleted =0 AND a.active =0
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
     * @ sys_acl_rrp_restservices bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  27.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRrpRestServicesList($params = array()) {
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
                $sort = " map_adi,restservice";
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
                            case 'restservice':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND restservice" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;
                            case 'map_adi':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND map_adi" . $sorguExpression . ' ';

                                break;
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';

                                break;
                            case 'resource_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND resource_name" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

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
            
            $sorguStr2 = null;
            if (isset($params['restservice']) && $params['restservice'] != "") {
                $sorguStr2 .= " AND restservice Like '%" . $params['restservice'] . "%'";
            }               
            if (isset($params['active']) && $params['active'] != "") {
                $sorguStr2 .= " AND active = " . $params['active'] ;
            }
            if (isset($params['rrp_id']) && $params['rrp_id'] != "") {
                $sorguStr2 .= " AND rrp_id = " . $params['rrp_id'] ;
            }
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND role_id = " . $params['role_id'] ;
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND resource_id = " . $params['resource_id'] ;
            }
            if (isset($params['privilege_id']) && $params['privilege_id'] != "") {
                $sorguStr2 .= " AND privilege_id = " . $params['privilege_id'] ;
            }
            
            $sql = " 
                SELECT
                    id, 
                    rrp_id,
                    restservice,
                    map_adi,
                    role_id, 
                    role_name,
                    resource_id, 
                    resource_name,
                    privilege_id,
                    privilege_name,
                    create_date,
                    active, 
                    state_active,  
                    description,                                     
                    op_user_id,
                    op_user_name ,
                    deleted
                    FROM (
                        SELECT 
                            a.id,
                            a.rrp_id,
                            a.restservice,
                            concat( rr.name,' - ',  rs.name,' - ' ,  rp.name , ' map ') AS map_adi,
                            rrp.role_id, 
                            rr.name AS role_name,
                            rrp.resource_id, 
                            rs.name AS resource_name,
                            rrp.privilege_id,
                            rp.name AS privilege_name,
                            a.c_date AS create_date,
                            a.active, 
                            sd1.description AS state_active,  
                            a.description,
                            a.op_user_id,
                            u.username AS op_user_name,
                            a.deleted
                        FROM sys_acl_rrp_restservices a
                        INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                        INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                        INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
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
     * @ sys_acl_rrp_restservices bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  27.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                     
    public function fillRrpRestServicesListRtc($params = array()) {
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
                            case 'restservice':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND restservice" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;
                            case 'map_adi':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND map_adi" . $sorguExpression . ' ';

                                break;
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';

                                break;
                            case 'resource_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND resource_name" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

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
            
            $sorguStr2 = null;
            if (isset($params['restservice']) && $params['restservice'] != "") {
                $sorguStr2 .= " AND restservice Like '%" . $params['restservice'] . "%'";
            }               
            if (isset($params['active']) && $params['active'] != "") {
                $sorguStr2 .= " AND active = " . $params['active'] ;
            }
            if (isset($params['rrp_id']) && $params['rrp_id'] != "") {
                $sorguStr2 .= " AND rrp_id = " . $params['rrp_id'] ;
            }
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND role_id = " . $params['role_id'] ;
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND resource_id = " . $params['resource_id'] ;
            }
            if (isset($params['privilege_id']) && $params['privilege_id'] != "") {
                $sorguStr2 .= " AND privilege_id = " . $params['privilege_id'] ;
            }
            
            $sql = " SELECT count(id) FROM (
                SELECT
                    id, 
                    rrp_id,
                    restservice,
                    map_adi,
                    role_id, 
                    role_name,
                    resource_id, 
                    resource_name,
                    privilege_id,
                    privilege_name,
                    create_date,
                    active, 
                    state_active,  
                    description,                                     
                    op_user_id,
                    op_user_name,
                    deleted
                    FROM (
                        SELECT 
                            a.id,
                            a.rrp_id,
                            a.restservice,
                            concat( rr.name,' - ',  rs.name,' - ' ,  rp.name , ' map ') AS map_adi,
                            rrp.role_id, 
                            rr.name AS role_name,
                            rrp.resource_id, 
                            rs.name AS resource_name,
                            rrp.privilege_id,
                            rp.name AS privilege_name,
                            a.c_date AS create_date,
                            a.active, 
                            sd1.description AS state_active,  
                            a.description,
                            a.op_user_id,
                            u.username AS op_user_name,
                            a.deleted
                        FROM sys_acl_rrp_restservices a
                        INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                        INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                        INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
                        WHERE a.deleted =0 AND a.active =0
                    ) AS xtable WHERE deleted =0 
                    " . $sorguStr . "
                    " . $sorguStr2 . "
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
     * @ sys_acl_rrp_restservices tablosundan rrp_id si verilen kayıtları döndürür !! 
     * @version v 1.0  28.07.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRestServicesOfPrivileges($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $RrpId = 0;
            $whereSql = "  WHERE a.deleted =0 AND a.active =0  ";
            if (isset($params['id']) && $params['id'] != "") {
                $RrpId = $params['id'];
            }
            $whereSql .= " AND a.rrp_id  = " . $RrpId; 
                            
            $sql ="             
                SELECT 
                    a.id,
                    a.rrp_id,
                    a.restservices_id,
                    aclr.name AS restservice_name,
                    aclr.description,
                    rrp.role_id,
                    rrp.resource_id,
                    rrp.privilege_id,
                    a.active,
                    a.description,
                    'open' AS state_type,
                    aclr.services_group_id
                FROM sys_acl_rrp_restservices a
                INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
                INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
                INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
                " . $whereSql . "
                ORDER BY aclr.name
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
     * @ sys_acl_rrp_restservices tablosundan rrp_id si dısında kalan kayıtları döndürür !! 
     * @version v 1.0  28.07.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                      
    public function fillNotInRestServicesOfPrivileges($params = array()) {
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
                $sort = " services_group_name ,restservice_name ";
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
                            case 'restservice_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND restservice_name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break; 
                            case 'services_group_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND services_group_name" . $sorguExpression . ' ';

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
            if (isset($params['id']) && $params['id'] != "") {
                $sorguStr2 .= " AND a.rrp_id = " . $params['id'] ;
            } 
            
            $sql = " 
                SELECT
                    id,			    
                    restservice_name,
                    services_group_id,
                    services_group_name,
                    description,
                    active,
                    deleted
                    FROM (
                        SELECT 
                            a.id,			    
                            a.name AS restservice_name,
                            ssg.id AS services_group_id,
                            ssg.name AS services_group_name,
			    a.description,
                            a.active,
                            a.deleted
                        FROM sys_acl_restservices a
                        INNER JOIN sys_services_groups ssg ON ssg.id = a.services_group_id AND ssg.deleted = 0 AND ssg.active = 0 
                        WHERE a.deleted =0 AND a.active =0 AND
                              a.id not in (
				SELECT a.restservices_id
				FROM sys_acl_rrp_restservices a
				INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
				INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
				INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
				INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
				INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
				WHERE a.deleted =0 AND a.active =0
				" . $sorguStr2 . "
				)
                    ) AS xtable WHERE deleted=0  
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
     * @ sys_acl_rrp_restservices tablosundan rrp_id si dısında kalan kayıtları döndürür !! 
     * filterRules aktif 
     * @version v 1.0  28.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                      
    public function fillNotInRestServicesOfPrivilegesRtc($params = array()) {
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
                            case 'restservice_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND restservice_name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.description" . $sorguExpression . ' ';

                                break; 
                            case 'services_group_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND services_group_name" . $sorguExpression . ' ';

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
            if (isset($params['id']) && $params['id'] != "") {
                $sorguStr2 .= " AND a.rrp_id = " . $params['id'] ;
            } 
            
            $sql = " SELECT COUNT(id) AS count FROM (
                SELECT
                    id,			    
                    restservice_name,
                    services_group_id,
                    services_group_name,
                    description,
                    active,
                    deleted
                    FROM (
                        SELECT 
                            a.id,			    
                            a.name AS restservice_name,
                            ssg.id AS services_group_id,
                            ssg.name AS services_group_name,
			    a.description,
                            a.active,
                            a.deleted
                        FROM sys_acl_restservices a
                        INNER JOIN sys_services_groups ssg ON ssg.id = a.services_group_id AND ssg.deleted = 0 AND ssg.active = 0 
                        WHERE a.deleted =0 AND a.active =0 AND
                              a.id not in (
				SELECT a.restservices_id
				FROM sys_acl_rrp_restservices a
				INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
				INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
				INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
				INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
				INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
				WHERE a.deleted =0 AND a.active =0
				" . $sorguStr2 . "
				)
                    ) AS xtable WHERE deleted=0  
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
     * @ tree doldurmak için sys_acl_rrp_restservices tablosundan rrp_id si dısında kalan kayıtları döndürür !! 
     * @version v 1.0  28.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillNotInServicesGroupsTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            
            $sorguStr2 = null;
            if (isset($params['rrp_id']) && $params['rrp_id'] != "") {
                $sorguStr2 .= " AND a.rrp_id = " . $params['rrp_id'] ;
            } 
            
            $sql = "
                SELECT
                    sare.id,
                    sare.name,
                    sare.active,
                    CASE
                        (CASE 
                            (SELECT DISTINCT 1 state_type FROM sys_acl_restservices xz WHERE xz.services_group_id = sare.id AND xz.deleted = 0 AND xz.active=0 AND 
					xz.id not in (
							SELECT a.restservices_id
							FROM sys_acl_rrp_restservices a
							INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
							INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
							WHERE a.deleted =0 AND a.active =0
							" . $sorguStr2 . "
							)
                            )    
                             WHEN 1 THEN 'closed'
                             ELSE 'open'   
                             END ) 
                         WHEN 'open' THEN COALESCE(NULLIF((SELECT DISTINCT 'closed' FROM sys_acl_restservices mz WHERE mz.services_group_id =sare.id AND mz.deleted = 0 AND mz.active=0
                                        AND mz.id not in (
                                                        SELECT a.restservices_id
                                                        FROM sys_acl_rrp_restservices a
                                                        INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
                                                        INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0				
                                                        WHERE a.deleted =0 AND a.active =0
                                                        " . $sorguStr2 . "
                                                        )

                         ), ''), 'open')   
                    ELSE 'closed'
                    END AS state_type,
                    CASE
                        (SELECT DISTINCT 1 parent_id FROM sys_acl_restservices zz WHERE zz.services_group_id = sare.id AND zz.deleted = 0 AND zz.active =0 
                                        AND zz.id not in (
                                                SELECT a.restservices_id
                                                FROM sys_acl_rrp_restservices a
                                                INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
                                                INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0				
                                                WHERE a.deleted =0 AND a.active =0
                                                " . $sorguStr2 . "
                                                )
                        )    
                        WHEN 1 THEN 'true'
                    ELSE 'true'   
                    END AS root_type,             
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_restservices zx WHERE zx.services_group_id = sare.id AND zx.deleted = 0 AND zx.active =0 
                                        AND zx.id not in (
                                                SELECT a.restservices_id
                                                FROM sys_acl_rrp_restservices a
                                                INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
                                                INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0				
                                                WHERE a.deleted =0 AND a.active =0
                                                " . $sorguStr2 . "
                                                )

                        )    
                         WHEN 1 THEN 'false'			 
                    ELSE 'true'   
                    END AS last_node,
                    'false' AS service,
                    '' AS description,               
                    id AS services_group_id
                FROM sys_services_groups sare   
                WHERE                   
                    sare.active = 0 AND 
                    sare.deleted = 0  

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
     * @ tree doldurmak için sys_acl_rrp_restservices tablosundan rrp_id si dısında kalan kayıtların sayısını döndürür !! 
     * @version v 1.0  28.07.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillNotInRestServicesTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                            
           
            $sorguStr2 = null;            
            if (isset($params['rrp_id']) && $params['rrp_id'] != "") {
                $sorguStr2 .= " AND a.rrp_id = " . $params['rrp_id'] ;
            } 
            $servicesGroupId = 0 ;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
               $servicesGroupId = $params['parent_id'] ;
            } 
           
            $sql ="  
                SELECT
                    id,			    
                    name,
                    services_group_id,                    
                    description,
                    active,
                    deleted,
                    state_type,                                          
                    root_type,                    
                    last_node,
                    service
                    FROM (
                        SELECT 
                            a.id,			    
                            a.name  ,
                            ssg.id AS services_group_id,                            
			    a.description,
                            a.active,
                            a.deleted,
			    'open' AS state_type,                                          
			    'false' AS root_type,                    
			    'true' AS last_node,
			    'true' AS service
                        FROM sys_acl_restservices a
                        INNER JOIN sys_services_groups ssg ON ssg.id = a.services_group_id AND ssg.deleted = 0 AND ssg.active = 0 
                        WHERE a.deleted =0 AND a.active =0 AND
			      ssg.id = 	".intval($servicesGroupId)." AND
                              a.id not in (
				SELECT a.restservices_id
				FROM sys_acl_rrp_restservices a
				INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
				INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0
				INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
				INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
				INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
				WHERE a.deleted =0 AND a.active =0
				" . $sorguStr2 . "
				)
                    ) AS xtable WHERE deleted=0 
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
     * @ tree doldurmak için sys_acl_rrp_restservices tablosundan yetki ile ilişkili olan kayıtların servis gruplarını döndürür !! 
     * @version v 1.0 02.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                        
     public function fillRestServicesGroupsOfPrivilegesTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            
            $sorguStr2 = null;            
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND rrpx.role_id = " . $params['role_id'] ;
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND rrpx.resource_id = " . $params['resource_id'] ;
            }
                            
            
            $sql = " SELECT * FROM (
               SELECT
                    sare.id,
                    sare.name,
                    sare.active,
                    CASE
                        (CASE 
                            (SELECT DISTINCT 1 state_type FROM sys_acl_restservices xz WHERE xz.services_group_id = sare.id AND xz.deleted = 0 AND xz.active=0 AND 
					xz.id in (
							SELECT 
							    DISTINCT ax.restservices_id
							FROM sys_acl_rrp_restservices ax
							INNER JOIN sys_acl_restservices aclrx ON aclrx.id = ax.restservices_id AND aclrx.deleted = 0 AND aclrx.active = 0  
							INNER JOIN sys_acl_rrp rrpx ON rrpx.id = ax.rrp_id AND rrpx.deleted =0 AND rrpx.active =0 ".$sorguStr2."
							INNER JOIN sys_acl_roles rrx ON rrx.id = rrpx.role_id AND rrx.deleted = 0 AND rrx.active = 0 
							INNER JOIN sys_acl_resources rsx ON rsx.id = rrpx.resource_id AND rsx.deleted = 0 AND rsx.active = 0 
							INNER JOIN sys_acl_privilege rpx ON rpx.id = rrpx.privilege_id AND rpx.deleted = 0 AND rpx.active = 0
							WHERE ax.deleted =0 AND ax.active =0
						       
							)
                            )    
                             WHEN 1 THEN 'closed'
                             ELSE 'open'   
                             END ) 
                         WHEN 'open' THEN COALESCE(NULLIF((SELECT DISTINCT 'closed' FROM sys_acl_restservices mz WHERE mz.services_group_id =sare.id AND mz.deleted = 0 AND mz.active=0
                                        AND mz.id in (
                                                        SELECT 
							    DISTINCT ax.restservices_id
							FROM sys_acl_rrp_restservices ax
							INNER JOIN sys_acl_restservices aclrx ON aclrx.id = ax.restservices_id AND aclrx.deleted = 0 AND aclrx.active = 0  
							INNER JOIN sys_acl_rrp rrpx ON rrpx.id = ax.rrp_id AND rrpx.deleted =0 AND rrpx.active =0 ".$sorguStr2."
							INNER JOIN sys_acl_roles rrx ON rrx.id = rrpx.role_id AND rrx.deleted = 0 AND rrx.active = 0 
							INNER JOIN sys_acl_resources rsx ON rsx.id = rrpx.resource_id AND rsx.deleted = 0 AND rsx.active = 0 
							INNER JOIN sys_acl_privilege rpx ON rpx.id = rrpx.privilege_id AND rpx.deleted = 0 AND rpx.active = 0
							WHERE ax.deleted =0 AND ax.active =0
                                                        
                                                        )

                         ), ''), 'open')   
                    ELSE 'closed'
                    END AS state_type,
		   'true' AS root_type,             
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_restservices zx WHERE zx.services_group_id = sare.id AND zx.deleted = 0 AND zx.active =0 
                                        AND zx.id in (SELECT 
							    DISTINCT ax.restservices_id
							FROM sys_acl_rrp_restservices ax
							INNER JOIN sys_acl_restservices aclrx ON aclrx.id = ax.restservices_id AND aclrx.deleted = 0 AND aclrx.active = 0  
							INNER JOIN sys_acl_rrp rrpx ON rrpx.id = ax.rrp_id AND rrpx.deleted =0 AND rrpx.active =0 ".$sorguStr2."
							INNER JOIN sys_acl_roles rrx ON rrx.id = rrpx.role_id AND rrx.deleted = 0 AND rrx.active = 0 
							INNER JOIN sys_acl_resources rsx ON rsx.id = rrpx.resource_id AND rsx.deleted = 0 AND rsx.active = 0 
							INNER JOIN sys_acl_privilege rpx ON rpx.id = rrpx.privilege_id AND rpx.deleted = 0 AND rpx.active = 0
							WHERE ax.deleted =0 AND ax.active =0
                                                
                                                )
                        )    
                         WHEN 1 THEN 'false'			 
                    ELSE 'true'   
                    END AS last_node,
                    'false' AS service,
                    '' AS description,               
                    id AS services_group_id,
                    null as rrp_restservice_id
                FROM sys_services_groups sare   
                WHERE                   
                    sare.active = 0 AND 
                    sare.deleted = 0  ) AS xtable 
                    WHERE state_type ='closed'
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
     * @ tree doldurmak için sys_acl_rrp_restservices tablosundan rrp_id si dısında kalan kayıtların sayısını döndürür !! 
     * @version v 1.0  28.07.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillRestServicesOfPrivilegesTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                            
           
                            
            $servicesGroupId = 0 ;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
               $servicesGroupId = $params['parent_id'] ;
            } 
            $sorguStr2 = null;    
            $sorguStr3 = null;                
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND rrp.role_id = " . $params['role_id'] ;
                $sorguStr3 .= " AND rrpq.role_id = " . $params['role_id'] ;
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND rrp.resource_id = " . $params['resource_id'] ;
                $sorguStr3 .= " AND rrpq.resource_id = " . $params['resource_id'] ;
            }
           
            $sql ="  
                SELECT
                    id,
                    rrp_restservice_id,                    			    
                    name,
                    services_group_id,                    
                    description,
                    active,
                    deleted,
                    state_type,                                          
                    root_type,                    
                    last_node,
                    service
                    FROM (
                        SELECT 
			   (SELECT DISTINCT aq.id
				FROM sys_acl_rrp_restservices aq
				INNER JOIN sys_acl_restservices aclrq ON aclrq.id = aq.restservices_id AND aclrq.deleted = 0 AND aclrq.active = 0  
				INNER JOIN sys_acl_rrp rrpq ON rrpq.id = aq.rrp_id AND rrpq.deleted =0 AND rrpq.active =0  ".$sorguStr3."
				INNER JOIN sys_acl_roles rrq ON rrq.id = rrpq.role_id AND rrq.deleted = 0 AND rrq.active = 0 
				INNER JOIN sys_acl_resources rsq ON rsq.id = rrpq.resource_id AND rsq.deleted = 0 AND rsq.active = 0 
				INNER JOIN sys_acl_privilege rpq ON rpq.id = rrpq.privilege_id AND rpq.deleted = 0 AND rpq.active = 0
				where aq.deleted =0 and aq.active =0 and aclrq.id = a.id
			    ) AS rrp_restservice_id,
                            a.id ,			    
                            a.name  ,
                            ssg.id AS services_group_id,                            
			    a.description,
                            a.active,
                            a.deleted,
			    'open' AS state_type,                                          
			    'false' AS root_type,                    
			    'true' AS last_node,
			    'true' AS service
                        FROM sys_acl_restservices a
                        INNER JOIN sys_services_groups ssg ON ssg.id = a.services_group_id AND ssg.deleted = 0 AND ssg.active = 0 
                        WHERE a.deleted =0 AND a.active =0 AND
			       ssg.id =  ".intval($servicesGroupId)." AND
                              a.id in (
				SELECT DISTINCT a.restservices_id
				FROM sys_acl_rrp_restservices a
				INNER JOIN sys_acl_restservices aclr ON aclr.id = a.restservices_id AND aclr.deleted = 0 AND aclr.active = 0  
				INNER JOIN sys_acl_rrp rrp ON rrp.id = a.rrp_id AND rrp.deleted =0 AND rrp.active =0  ".$sorguStr2."
				INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
				INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
				INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
				WHERE a.deleted =0 AND a.active =0)
				
                    ) AS xtable WHERE deleted=0 
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
     * @ sys_assign_definition tablosunda restservices_id  daha önce kaydedilmiş mi ?  
     * @version v 1.0  08.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveActionRecords($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                             
            $sql = "             
            SELECT
                sarrr.restservices_id AS name,
                a.assign_definition_id = " . $params['id'] . " AS control,
                'Bu RestServis Yetki ile İlişkilendirilmiş. Lütfen Kontrol Ediniz !!!' AS message   
            FROM sys_operation_types_rrp  a 
            INNER JOIN sys_acl_rrp_restservices sarrr ON sarrr.id= a.rrp_restservice_id 
            WHERE sarrr.id = ".intval($params['id']). "	
                AND a.deleted =0    
            LIMIT 1
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
