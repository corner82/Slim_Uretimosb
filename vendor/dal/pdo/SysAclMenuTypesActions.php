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
class SysAclMenuTypesActions extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_acl_menu_types_actions tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  26.07.2016
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
                $sql = " 
                UPDATE sys_acl_menu_types_actions
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
     * @ sys_acl_menu_types_actions tablosundaki tüm kayıtları getirir.  !!
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
                    sam.id AS module_id,
                    sam.name AS module_name,
		    sac.id AS action_id,
                    sac.name AS action_name,
		    a.menu_types_id,
                    smt.name AS menu_type_name,
                    smt.description AS menu_type_description,
                    a.c_date AS create_date,                        
                    a.deleted,
                    sd.description AS state_deleted,
                    a.active,
                    sd1.description AS state_active,                    
                    a.op_user_id,
                    u.username AS op_user_name
                FROM sys_acl_menu_types_actions a                                
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0 
                INNER JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0 
                INNER JOIN sys_acl_actions sac ON sac.id = a.action_id AND sac.deleted = 0 AND sac.active = 0     
                INNER JOIN sys_acl_modules sam ON sam.id = sac.module_id AND sam.deleted = 0 AND sam.active = 0
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id          
                ORDER BY sam.name, sac.name, smt.name

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
     * @ sys_acl_menu_types_actions tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  26.07.2016
     * @param type $params
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
                INSERT INTO sys_acl_menu_types_actions(
                        menu_types_id, 
                        action_id,
                        op_user_id )
                VALUES (
                        " . intval( $params['menu_types_id']) . ",
                        " . intval( $params['action_id']) . ",
                        " . intval($opUserIdValue) . "                       
                                              )  ";
                    $statement = $pdo->prepare($sql);                    
                    //   echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_menu_types_actions_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'menu_types_id';
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
     * sys_acl_menu_types_actions tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  26.07.2016
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
                UPDATE sys_acl_menu_types_actions
                SET
                    menu_types_id = " . intval( $params['menu_types_id']) . ",
                    action_id = " . intval( $params['action_id']) . ",
                    op_user_id= " . intval($opUserIdValue) . "                    
                WHERE id = " . intval($params['id']) . "
                    ";
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
                menu_types_id as name , 
                " . $params['menu_types_id'] . " AS value , 
                menu_types_id =" . $params['menu_types_id'] . " AS control,
                concat(menu_types_id , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message                             
            FROM sys_acl_menu_types_actions                
            WHERE 
                action_id = ".intval($params['action_id'])." AND 
                menu_types_id = ".intval($params['menu_types_id'])."                          
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
     * @ Gridi doldurmak için sys_acl_menu_types_actions tablosundan kayıtları döndürür !!
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
            $sort = "sam.name, sac.name, smt.name";
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
                    sam.id AS module_id,
                    sam.name AS module_name,
		    sac.id AS action_id,
                    sac.name AS action_name,
		    a.menu_types_id,
                    smt.name AS menu_type_name,
                    smt.description AS menu_type_description,
                    a.c_date AS create_date,                        
                    a.deleted,
                    sd.description AS state_deleted,
                    a.active,
                    sd1.description AS state_active,                    
                    a.op_user_id,
                    u.username AS op_user_name
                FROM sys_acl_menu_types_actions a                                
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0 
                INNER JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0 
                INNER JOIN sys_acl_actions sac ON sac.id = a.action_id AND sac.deleted = 0 AND sac.active = 0     
                INNER JOIN sys_acl_modules sam ON sam.id = sac.module_id AND sam.deleted = 0 AND sam.active = 0
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
     * @ Gridi doldurmak için sys_acl_menu_types_actions tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  26.07.2016
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
                FROM sys_acl_menu_types_actions a                                
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0 
                INNER JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0 
                INNER JOIN sys_acl_actions sac ON sac.id = a.action_id AND sac.deleted = 0 AND sac.active = 0     
                INNER JOIN sys_acl_modules sam ON sam.id = sac.module_id AND sam.deleted = 0 AND sam.active = 0
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
     * @ resource bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMenuTypesActionList($params = array()) {
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
                $sort = "  sam.name, sac.name, smt.name";
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
                            case 'menu_type_description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;
                            case 'module_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sam.name" . $sorguExpression . ' ';

                                break;
                            case 'action_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sac.name" . $sorguExpression . ' ';

                                break;
                            case 'menu_type_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.name" . $sorguExpression . ' ';

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
            if (isset($params['action_id']) && $params['action_id'] != "") {
                $sorguStr2 .= " AND sac.id = " . $params['action_id'] ;
            }
            if (isset($params['menu_types_id']) && $params['menu_types_id'] != "") {
                $sorguStr2 .= " AND a.menu_types_id = " . $params['menu_types_id'] ;
            }
            
            $sql = "                 
		SELECT 
                    a.id,
                    sam.id AS module_id,
                    sam.name AS module_name,
		    sac.id AS action_id,
                    sac.name AS action_name,
		    a.menu_types_id,
                    smt.name AS menu_type_name,
                    smt.description AS menu_type_description,
                    a.c_date AS create_date,                        
                    a.deleted,
                    sd.description AS state_deleted,
                    a.active,
                    sd1.description AS state_active,                    
                    a.op_user_id,
                    u.username AS op_user_name
                FROM sys_acl_menu_types_actions a                                
                INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0 
                INNER JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0 
                INNER JOIN sys_acl_actions sac ON sac.id = a.action_id AND sac.deleted = 0 AND sac.active = 0     
                INNER JOIN sys_acl_modules sam ON sam.id = sac.module_id AND sam.deleted = 0 AND sam.active = 0
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
     * @ resource bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMenuTypesActionListRtc($params = array()) {
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
                            case 'menu_type_description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.description" . $sorguExpression . ' ';

                                break;
                            case 'state_active':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sd1.description" . $sorguExpression . ' ';

                                break;
                            case 'module_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sam.name" . $sorguExpression . ' ';

                                break;
                            case 'action_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sac.name" . $sorguExpression . ' ';

                                break;
                            case 'menu_type_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.name" . $sorguExpression . ' ';

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
            if (isset($params['action_id']) && $params['action_id'] != "") {
                $sorguStr2 .= " AND sac.id = " . $params['action_id'] ;
            }
            if (isset($params['menu_types_id']) && $params['menu_types_id'] != "") {
                $sorguStr2 .= " AND a.menu_types_id = " . $params['menu_types_id'] ;
            }
            $sql = "   
                SELECT COUNT(id) AS count 
                FROM (
                    SELECT id,name,deleted,active,description,state_deleted,state_active,module_id,module_name
                    FROM (
                        SELECT 
                            a.id,
                            sam.id AS module_id,
                            sam.name AS module_name,
                            sac.id AS action_id,
                            sac.name AS action_name,
                            a.menu_types_id,
                            smt.name AS menu_type_name,
                            smt.description AS menu_type_description,
                            a.c_date AS create_date,                        
                            a.deleted,
                            sd.description AS state_deleted,
                            a.active,
                            sd1.description AS state_active,                    
                            a.op_user_id,
                            u.username AS op_user_name
                        FROM sys_acl_menu_types_actions a                                
                        INNER JOIN sys_language l ON l.id = 647 AND l.deleted =0 AND l.active =0 
                        INNER JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0 
                        INNER JOIN sys_acl_actions sac ON sac.id = a.action_id AND sac.deleted = 0 AND sac.active = 0     
                        INNER JOIN sys_acl_modules sam ON sam.id = sac.module_id AND sam.deleted = 0 AND sam.active = 0
                        INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = l.id AND sd.deleted = 0 AND sd.active = 0
                        INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = l.id AND sd1.deleted = 0 AND sd1.active = 0
                        INNER JOIN info_users u ON u.id = a.op_user_id          
                        WHERE a.deleted =0 
                        " . $sorguStr . "
                        " . $sorguStr2 . "
                    ) AS xtable      
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
     * @ sys_acl_menu_types_actions tablosundan parametre olarak  gelen id kaydın aktifliğini
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
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                if (isset($params['id']) && $params['id'] != "") {

                    $sql = "                 
                UPDATE sys_acl_menu_types_actions
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_acl_menu_types_actions
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
     * @ sys_acl_menu_types_actions bilgilerini döndürür !!
     * filterRules aktif, LEFT JOIN özellikle kullanıldı. 
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMenuTypesActionLeftList($params = array()) {
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
                $sort = "  sam.name, sac.name ";
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
                            case 'menu_type_description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.description" . $sorguExpression . ' ';

                                break;                            
                            case 'module_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sam.name" . $sorguExpression . ' ';

                                break;
                            case 'action_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sac.name" . $sorguExpression . ' ';

                                break;
                            case 'menu_type_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.name" . $sorguExpression . ' ';

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
            
            /*
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
            if (isset($params['action_id']) && $params['action_id'] != "") {
                $sorguStr2 .= " AND sac.id = " . $params['action_id'] ;
            }
            if (isset($params['menu_types_id']) && $params['menu_types_id'] != "") {
                $sorguStr2 .= " AND a.menu_types_id = " . $params['menu_types_id'] ;
            }
             * 
             */
            
            $sql = " 
                        SELECT 
                            a.id,
                            sam.id AS module_id,
                            sam.name AS module_name,
                            sac.id AS action_id,
                            sac.name AS action_name,
                            a.menu_types_id,
                            smt.name AS menu_type_name,
                            smt.description AS menu_type_description,
                            a.c_date AS create_date,                        
                            a.deleted,                        
                            a.active,                        
                            a.op_user_id,
                            u.username AS op_user_name
                        FROM sys_acl_modules sam                                                        
                        LEFT JOIN sys_acl_actions sac ON sac.module_id = sam.id AND sac.deleted = 0 AND sac.active = 0     
                        LEFT JOIN sys_acl_menu_types_actions a ON a.action_id = sac.id AND a.deleted = 0 AND a.active = 0                        
                        LEFT JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0                         
                        LEFT JOIN info_users u ON u.id = a.op_user_id      
                        WHERE sam.deleted =0 
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
     * @ sys_acl_menu_types_actions bilgilerinin sayısını döndürür !!
     * filterRules aktif, LEFT JOIN özellikle kullanıldı. 
     * @version v 1.0  26.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMenuTypesActionLeftRtc($params = array()) {
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
                            case 'menu_type_description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.description" . $sorguExpression . ' ';

                                break;                            
                            case 'module_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sam.name" . $sorguExpression . ' ';

                                break;
                            case 'action_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND sac.name" . $sorguExpression . ' ';

                                break;
                            case 'menu_type_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND smt.name" . $sorguExpression . ' ';

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
            /*
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
            if (isset($params['action_id']) && $params['action_id'] != "") {
                $sorguStr2 .= " AND sac.id = " . $params['action_id'] ;
            }
            if (isset($params['menu_types_id']) && $params['menu_types_id'] != "") {
                $sorguStr2 .= " AND a.menu_types_id = " . $params['menu_types_id'] ;
            }
             * 
             */
            $sql = "   
                    SELECT COUNT(module_id) AS count 
                    FROM (
                        SELECT id,active,module_id,module_name,action_name,menu_type_name,menu_type_description
                        FROM (
                            SELECT 
                            a.id,
                            sam.id AS module_id,
                            sam.name AS module_name,
                            sac.id AS action_id,
                            sac.name AS action_name,
                            a.menu_types_id,
                            smt.name AS menu_type_name,
                            smt.description AS menu_type_description,
                            a.c_date AS create_date,                        
                            a.deleted,                        
                            a.active,                        
                            a.op_user_id,
                            u.username AS op_user_name
                        FROM sys_acl_modules sam                                                        
                        LEFT JOIN sys_acl_actions sac ON sac.module_id = sam.id AND sac.deleted = 0 AND sac.active = 0     
                        LEFT JOIN sys_acl_menu_types_actions a ON a.action_id = sac.id AND a.deleted = 0 AND a.active = 0                        
                        LEFT JOIN sys_menu_types smt ON smt.id = a.menu_types_id AND smt.deleted = 0 AND smt.active = 0                         
                        LEFT JOIN info_users u ON u.id = a.op_user_id  
                        WHERE sam.deleted =0 
                            " . $sorguStr . "                           
                            ) AS xtable      
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
                          
                            
    
    
}
