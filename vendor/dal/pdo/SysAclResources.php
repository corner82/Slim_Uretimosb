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
class SysAclResources extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_acl_resources tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  07.01.2016
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
                UPDATE sys_acl_resources
                SET  deleted= 1, active = 1,
                     op_user_id = " . intval($opUserIdValue) . "
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
     * @ sys_acl_resources tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  07.01.2016    
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
                        a.icon_class,
                        a.c_date AS create_date,
                        a.parent_id,
                        a.deleted,
                        sd.description AS state_deleted,
                        a.active,
                        sd1.description AS state_active,
                        a.description,
                        a.op_user_id,
                        u.username
                FROM sys_acl_resources a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0
                INNER JOIN info_users u ON u.id = a.op_user_id
                WHERE a.deleted =0 AND a.language_code = :language_code     
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
     * @ sys_acl_resources tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  07.01.2016
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

                    $ParentId = 0;
                    if ((isset($params['parent_id']) && $params['parent_id'] != "")) {
                        $ParentId = $params['parent_id'];
                    }

                    $sql = "
                INSERT INTO sys_acl_resources(
                        name, parent_id, op_user_id, description)
                VALUES (
                        :name,                               
                        " . intval($ParentId) . ",                      
                        " . intval($opUserIdValue) . ",
                        :description                      
                                              )  ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
                    $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                    //   echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_acl_resources_id_seq');
                    $errorInfo = $statement->errorInfo();
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
     * sys_acl_resources tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.01.2016
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
                    $ParentId = 0;
                    if ((isset($params['parent_id']) && $params['parent_id'] != "")) {
                        $ParentId = $params['parent_id'];
                    }

                    $sql = "
                UPDATE sys_acl_resources
                SET
                    name = '" . $params['name'] . "',
                    parent_id = " . intval($ParentId) . ",
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
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    $errorInfo = '23505';
                    $pdo->rollback();
                    $result = $kontrol;
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
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
                name as name , 
                '" . $params['name'] . "' as value , 
                name ='" . $params['name'] . "' as control,
                concat(name , ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) as message                             
            FROM sys_acl_resources                
            WHERE LOWER(REPLACE(name,' ','')) = LOWER(REPLACE('" . $params['name'] . "',' ',''))"
                    . $addSql . " 
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
     * @ Gridi doldurmak için sys_acl_resources tablosundan kayıtları döndürür !!
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
                        a.c_date AS create_date,		
                        a.parent_id,                   
                        a.deleted, 
                        sd.description AS state_deleted,                 
                        a.active, 
                        sd1.description AS state_active,  
                        a.description,                                     
                        a.op_user_id,
                        u.username                    
                FROM sys_acl_resources a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id    
                WHERE a.deleted =0  
                  " . $whereSQL . "
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
     * @ Gridi doldurmak için sys_acl_resources tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  07.01.2016
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
                FROM sys_acl_resources a
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0                             
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
     * @ combobox doldurmak için sys_acl_resources tablosundan parent ı 0 olan kayıtları (Ana grup) döndürür !!
     * @version v 1.0  07.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillComboBoxMainResources() {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
              SELECT                    
                  a.id, 	
                  a.name AS name                                 
              FROM sys_acl_resources a       
              WHERE a.active =0 AND a.deleted = 0 AND parent_id =0                 
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
     * @ combobox doldurmak için sys_acl_resources tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  07.01.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function fillComboBoxFullResources($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $id = 0;
            if (isset($params['id']) && $params['id'] != "") {
                $id = $params['id'];
            }
            $statement = $pdo->prepare("
                SELECT                    
                    a.id, 	
                    a.name AS name  ,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_resources WHERE parent_id = a.id AND deleted = 0)    
                    WHEN 1 THEN 'closed'
                    ELSE 'open'   
                    END AS state_type  
                FROM sys_acl_resources a       
                WHERE                    
                    a.parent_id = " . $id . " AND 
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
     * @ tree doldurmak için sys_acl_resources tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  12.08.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function fillResourcesTree($params = array()) {
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
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_resources z WHERE z.parent_id = a.id AND z.deleted = 0)
                    WHEN 1 THEN 'closed'
                    ELSE 'open' END AS state_type,
                    a.active
                FROM sys_acl_resources a
                WHERE
                    a.parent_id = " . $id . " AND 
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
     * @ resource bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  13.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillPropertieslist($params = array()) {
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
                $sort = "a.parent_id, a.name";
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
                            case 'parent_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF((SELECT z.name FROM sys_acl_resources z where z.id = a.parent_id), ''),'Root')" . $sorguExpression . ' ';

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
                if (isset($params['description']) && $params['description'] != "") {
                    $sorguStr .= " AND a.description Like '%" . $params['description'] . "%'";
                }
                if (isset($params['parent_name']) && $params['parent_name'] != "") {
                    $sorguStr .= " AND COALESCE(NULLIF((SELECT z.name FROM sys_acl_resources z where z.id = a.parent_id), ''),'Root') Like '%" . $params['parent_name'] . "%'";
                }
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "                 
		SELECT 
                        a.id,
                        a.name AS name,                        
                        a.parent_id,
                        COALESCE(NULLIF((SELECT z.name FROM sys_acl_resources z where z.id = a.parent_id), ''),'Root') AS parent_name,
                        a.deleted,
                        sd15.description AS state_deleted,
                        a.active,
                        sd16.description AS state_active,
                        a.description                     
                FROM sys_acl_resources a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0         
                WHERE a.deleted =0 
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
     * @ resource bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0  13.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillPropertieslistRtc($params = array()) {
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
                            case 'description':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND description" . $sorguExpression . ' ';

                                break;
                            case 'parent_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND parent_name" . $sorguExpression . ' ';

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
                if (isset($params['description']) && $params['description'] != "") {
                    $sorguStr .= " AND description Like '%" . $params['description'] . "%'";
                }
                if (isset($params['parent_name']) && $params['parent_name'] != "") {
                    $sorguStr .= " AND parent_name Like '%" . $params['parent_name'] . "%'";
                }
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "   
                SELECT COUNT(id) AS count 
                FROM (
                    SELECT id,name,parent_id,parent_name,deleted,active,description
                    FROM (
                        SELECT 
                                a.id,
                                a.name AS name,                        
                                a.parent_id,
                                COALESCE(NULLIF((SELECT z.name FROM sys_acl_resources z where z.id = a.parent_id), ''),'Root') AS parent_name,
                                a.deleted,
                                sd15.description AS state_deleted,
                                a.active,
                                sd16.description AS state_active,
                                a.description                     
                        FROM sys_acl_resources a
                        INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                        INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0         
                        WHERE a.deleted =0 
                    ) as xtable
                    WHERE deleted =0 
                    " . $sorguStr . "
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
     * @ sys_acl_resources tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  13.07.2016
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
                UPDATE sys_acl_resources
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_acl_resources
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
     * @ sys_acl_resources tablosundan kayıtları döndürür !!
     * @version v 1.0 13.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillResourcesDdList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("        
               SELECT                    
                    a.id, 	
                    a.name,  
                    a.description,                                    
                    a.active,
                    'open' AS state_type  
	         FROM sys_acl_resources a    
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
     * @ tree doldurmak için sys_acl_resources tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  14.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillResourceGroups($params = array()) {
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
                            (SELECT DISTINCT 1 state_type FROM sys_acl_resources xz WHERE xz.parent_id = sare.id AND xz.deleted = 0)    
                             WHEN 1 THEN 'closed'
                             ELSE 'open'   
                             END ) 
                         WHEN 'open' THEN COALESCE(NULLIF((SELECT DISTINCT 'closed' FROM sys_acl_resource_roles mz WHERE mz.resource_id =sare.id AND mz.deleted = 0), ''), 'open')   
                    ELSE 'closed'
                    END AS state_type,
                    CASE
                        (SELECT DISTINCT 1 parent_id FROM sys_acl_resources WHERE id = sare.id AND deleted = 0 AND parent_id =0 )    
                        WHEN 1 THEN 'true'
                    ELSE 'false'   
                    END AS root_type,             
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_acl_resources WHERE parent_id = sare.id AND deleted = 0)    
                         WHEN 1 THEN 'false'			 
                    ELSE 'true'   
                    END AS last_node,
                    'false' AS roles,
                    sare.id AS resource_id
                FROM sys_acl_resources sare  
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
     * @ tree doldurmak için sys_acl_privilege tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  14.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillResourceGroupsRoles($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                            
            $parentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $parentId = $params['parent_id'];
            } 
            $sql ="                
                SELECT
                    mt.id, 
                    COALESCE(NULLIF( (mt.name_tr), ''), mt.name) AS name,
                    -1 AS parent_id,
                    a.active,
                    'open' AS state_type,
                    'false' AS root_type,
                    Null AS icon_class,
                    'true' AS last_node,
                    'true' AS roles,
		    sarr.resource_id
                FROM sys_acl_resources a                 
		INNER JOIN sys_acl_resource_roles sarr ON sarr.resource_id = a.id AND sarr.active =0 AND sarr.deleted =0
		INNER JOIN sys_acl_roles mt ON mt.id = sarr.role_id AND mt.active =0 AND mt.deleted =0
                WHERE                    
                   a.id = " .intval($parentId) . " AND 
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

    
    
    
}
