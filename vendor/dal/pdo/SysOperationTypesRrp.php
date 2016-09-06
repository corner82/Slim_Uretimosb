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
class SysOperationTypesRrp extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_operation_types_rrp tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  08.08.2016
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
                    UPDATE sys_operation_types_rrp
                    SET  deleted= 1 , active = 1,
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
     * @ sys_operation_types_rrp tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  08.08.2016  
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
            FROM sys_operation_types_rrp  a
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
     * @ sys_operation_types_rrp tablosunda role_id, resource_id ve privilege_id aynı kayıtta daha önce oluşturulmuş mu? 
     * @version v 1.0 08.08.2016
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
                rrp_restservice_id AS name, 
                '" . $params['rrp_restservice_id'] . "' AS value, 
                rrp_restservice_id ='" . $params['rrp_restservice_id'] . "' AS control,
                concat(rrp_restservice_id , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_operation_types_rrp
            WHERE   rrp_restservice_id= " . intval($params['rrp_restservice_id']) . " AND
                    table_oid= " . intval($params['table_oid']) . " AND 
                    assign_definition_id = " . intval($params['assign_definition_id']) . " 
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
     * @ sys_operation_types_rrp tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  08.08.2016
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
                    $languageId = NULL;
                    $languageIdValue = 647;
                    if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                        if (\Utill\Dal\Helper::haveRecord($languageId)) {
                            $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                        }
                    }  
                    
                    $sql = "
                INSERT INTO sys_operation_types_rrp(
                        operation_name, 
                        operation_name_eng,
                        rrp_restservice_id, 
                        table_oid, 
                        assign_definition_id, 
                        description, 
                        description_eng, 
                        language_id, 
                        op_user_id
                        )
                VALUES (
                        '" . $params['operation_name'] . "',
                        '" . $params['operation_name_eng'] . "',                        
                        " . intval($params['rrp_restservice_id']) . ",
                        " . intval($params['table_oid']) . ",
                        " . intval($params['assign_definition_id']) . ",
                        '" . $params['description'] . "',
                        '" . $params['description_eng'] . "',
                        " . intval($languageIdValue) . ", 
                        " . intval($opUserIdValue) . " 
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_operation_types_rrp_id_seq');
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
     * sys_operation_types_rrp tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  08.08.2016
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
                UPDATE sys_operation_types_rrp
                SET  
                    operation_name= '" . $params['operation_name'] . "', 
                    operation_name_eng= '" . $params['operation_name_eng'] . "',                         
                    rrp_restservice_id= " . intval($params['rrp_restservice_id']) . ", 
                    table_oid= " . intval($params['table_oid']) . ",
                    assign_definition_id= " . intval($params['assign_definition_id']) . ", 
                    description= '" . $params['description'] . "',
                    description_eng= '" . $params['description_eng'] . "',
                    language_id = " . intval($languageIdValue) . ", 
                    op_user_id = " . intval($opUserIdValue) . "
                WHERE id = " . intval($params['id']);
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
     * @ Gridi doldurmak için sys_operation_types_rrp tablosundan kayıtları döndürür !!
     * @version v 1.0  08.08.2016
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
                FROM sys_operation_types_rrp  a
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
     * @ Gridi doldurmak için sys_operation_types_rrp tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  08.08.2016
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
                FROM sys_operation_types_rrp  a
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
     * @ sys_operation_types_rrp bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  08.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOperationTypesRrpList($params = array()) {
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
                $sort = " operation_name";
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
                             case 'table_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND table_name" . $sorguExpression . ' ';

                                break;
                             case 'operation_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND operation_name" . $sorguExpression . ' ';

                                break;
                            case 'operation_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND operation_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';

                                break;
                            case 'role_name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND role_name_tr" . $sorguExpression . ' ';

                                break;
                            case 'resource_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND resource_name" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND state_active" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

                                break;
                            case 'services_group_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND service_group_name" . $sorguExpression . ' ';

                                break;
                            case 'restservice_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND restservice_name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

                                break;
                             case 'description_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description_eng" . $sorguExpression . ' ';

                                break;
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';

                                break;
                            case 'assignment_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND assignment_name" . $sorguExpression . ' ';

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
                    operation_name, 
                    operation_name_eng,
                    rrp_restservice_id, 
                    role_id, 
                    role_name, 
                    role_name_tr,
                    resource_id, 
                    resource_name,                            
                    privilege_id,
                    privilege_name,
                    services_group_id,
                    service_group_name,	
                    assign_definition_id,
                    assignment_name,
                    restservices_id,
                    restservice_name,
                    table_oid,
                    table_name,
                    description,
                    description_eng,                             
                    active, 
                    state_active,                              
                    op_user_id,
                    op_user_name,
                    deleted
                    FROM (
                        SELECT 
                            a.id,
                            a.operation_name, 
                            a.operation_name_eng,
                            a.rrp_restservice_id, 
                            rrp.role_id, 
                            rr.name AS role_name,  
                            rr.name_tr AS role_name_tr,
                            rrp.resource_id, 
                            rs.name AS resource_name,                            
                            rrp.privilege_id,
                            rp.name AS privilege_name,
			    sar.services_group_id,
			    ssg.name AS service_group_name,			    
			    sarrs.restservices_id,
			    sar.name AS restservice_name,
                            a.assign_definition_id,
                            sad.name AS assignment_name,
                            a.table_oid,
                            c.relname AS table_name,   
                            a.description,
                            a.description_eng,                             
                            a.active, 
                            sd1.description AS state_active,                              
                            a.op_user_id,
                            u.username AS op_user_name,
                            a.deleted
                        FROM sys_operation_types_rrp a
                        INNER JOIN sys_acl_rrp_restservices sarrs ON sarrs.id = a.rrp_restservice_id AND sarrs.active=0 AND sarrs.deleted =0
                        INNER JOIN sys_acl_rrp rrp ON rrp.id = sarrs.rrp_id AND rrp.deleted =0 AND rrp.active =0
                        INNER JOIN sys_acl_restservices sar ON sar.id = sarrs.restservices_id AND sar.active =0 AND sar.deleted =0 
                        INNER JOIN sys_services_groups ssg ON ssg.id = sar.services_group_id AND ssg.active =0 AND ssg.deleted =0 
                        INNER JOIN pg_catalog.pg_class c ON c.oid = a.table_oid
                        INNER JOIN sys_assign_definition sad ON sad.id = a.assign_definition_id AND sad.active=0 AND sad.deleted=0
                        
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                        INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                        INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
                        WHERE a.deleted =0 AND a.active =0
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
     * @ sys_operation_types_rrp bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  08.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillOperationTypesRrpListRtc($params = array()) {
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
                             case 'table_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND table_name" . $sorguExpression . ' ';

                                break;
                            case 'operation_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND operation_name" . $sorguExpression . ' ';

                                break;
                            case 'operation_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND operation_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'role_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND role_name" . $sorguExpression . ' ';

                                break;
                             case 'role_name_tr':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND role_name_tr" . $sorguExpression . ' ';

                                break;
                            case 'resource_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND resource_name" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND state_active" . $sorguExpression . ' ';

                                break;
                            case 'privilege_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND privilege_name" . $sorguExpression . ' ';

                                break;
                            case 'services_group_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND service_group_name" . $sorguExpression . ' ';

                                break;
                            case 'restservice_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND restservice_name" . $sorguExpression . ' ';

                                break;
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

                                break;
                             case 'description_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description_eng" . $sorguExpression . ' ';

                                break;
                            case 'op_user_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';

                                break;
                             case 'assignment_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND assignment_name" . $sorguExpression . ' ';

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
                SELECT count(id) FROM (
                SELECT
                    id,
                    operation_name, 
                    operation_name_eng,
                    rrp_restservice_id, 
                    role_id, 
                    role_name,  
                    role_name_tr,
                    resource_id, 
                    resource_name,                            
                    privilege_id,
                    privilege_name,
                    services_group_id,
                    service_group_name,			    
                    restservices_id,
                    restservice_name,
                    assign_definition_id,
                    assignment_name,
                    table_oid,
                    table_name,
                    description,
                    description_eng,                             
                    active, 
                    state_active,                              
                    op_user_id,
                    op_user_name,
                    deleted
                    FROM (
                        SELECT 
                            a.id,
                            a.operation_name, 
                            a.operation_name_eng,
                            a.rrp_restservice_id, 
                            rrp.role_id, 
                            rr.name AS role_name,
                            rr.name_tr AS role_name_tr,
                            rrp.resource_id, 
                            rs.name AS resource_name,                            
                            rrp.privilege_id,
                            rp.name AS privilege_name,
			    sar.services_group_id,
			    ssg.name AS service_group_name,			    
			    sarrs.restservices_id,
			    sar.name AS restservice_name,
                            a.assign_definition_id,
                            sad.name AS assignment_name,
                            a.table_oid,
                            c.relname AS table_name,
                            a.description,
                            a.description_eng,                             
                            a.active, 
                            sd1.description AS state_active,                              
                            a.op_user_id,
                            u.username AS op_user_name,
                            a.deleted
                        FROM sys_operation_types_rrp a
                        INNER JOIN sys_acl_rrp_restservices sarrs ON sarrs.id = a.rrp_restservice_id AND sarrs.active=0 AND sarrs.deleted =0
                        INNER JOIN sys_acl_rrp rrp ON rrp.id = sarrs.rrp_id AND rrp.deleted =0 AND rrp.active =0
                        INNER JOIN sys_acl_restservices sar ON sar.id = sarrs.restservices_id AND sar.active =0 AND sar.deleted =0 
                        INNER JOIN sys_services_groups ssg ON ssg.id = sar.services_group_id AND ssg.active =0 AND ssg.deleted =0 
                        INNER JOIN pg_catalog.pg_class c ON c.oid = a.table_oid                        
                        INNER JOIN sys_assign_definition sad ON sad.id = a.assign_definition_id AND sad.active=0 AND sad.deleted=0
                        
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = 647 AND sd1.deleted = 0 AND sd1.active = 0  
                        INNER JOIN info_users u ON u.id = a.op_user_id 
                        INNER JOIN sys_acl_roles rr ON rr.id = rrp.role_id AND rr.deleted = 0 AND rr.active = 0 
                        INNER JOIN sys_acl_resources rs ON rs.id = rrp.resource_id AND rs.deleted = 0 AND rs.active = 0 
                        INNER JOIN sys_acl_privilege rp ON rp.id = rrp.privilege_id AND rp.deleted = 0 AND rp.active = 0
                        WHERE a.deleted =0 AND a.active =0
                    ) AS xtable WHERE deleted =0 
                    " . $sorguStr . "               
                      ) AS xxtable  
                        
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
     * @ sys_operation_types_rrp bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  08.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsultantOperationsRrpList($params = array()) {
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
                $sorguStr2 .= " AND active = " . $params['active'];
            }
            if (isset($params['rrp_id']) && $params['rrp_id'] != "") {
                $sorguStr2 .= " AND rrp_id = " . $params['rrp_id'];
            }
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND role_id = " . $params['role_id'];
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND resource_id = " . $params['resource_id'];
            }
            if (isset($params['privilege_id']) && $params['privilege_id'] != "") {
                $sorguStr2 .= " AND privilege_id = " . $params['privilege_id'];
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
                        FROM sys_operation_types_rrp a
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
     * @ sys_operation_types_rrp bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  08.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillConsultantOperationsRrpListRtc($params = array()) {
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
                $sorguStr2 .= " AND active = " . $params['active'];
            }
            if (isset($params['rrp_id']) && $params['rrp_id'] != "") {
                $sorguStr2 .= " AND rrp_id = " . $params['rrp_id'];
            }
            if (isset($params['role_id']) && $params['role_id'] != "") {
                $sorguStr2 .= " AND role_id = " . $params['role_id'];
            }
            if (isset($params['resource_id']) && $params['resource_id'] != "") {
                $sorguStr2 .= " AND resource_id = " . $params['resource_id'];
            }
            if (isset($params['privilege_id']) && $params['privilege_id'] != "") {
                $sorguStr2 .= " AND privilege_id = " . $params['privilege_id'];
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
                        FROM sys_operation_types_rrp a
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
     * @ sys_operation_types_rrp tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  08.08.2016
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
                UPDATE sys_operation_types_rrp
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_operation_types_rrp
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
