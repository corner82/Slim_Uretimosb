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
 * @since 15.02.2016
 */
class SysMachineTools extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_machine_tools tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  15.02.2016
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
                UPDATE sys_machine_tools
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . intval($opUserIdValue) . "     
                WHERE id = " . intval($params['id']));
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
     * @ sys_machine_tools tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  15.02.2016  
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
                        mtg.group_name,
                        a.machine_tool_name, 
                        a.machine_tool_name_eng, 
                        a.machine_tool_grup_id, 
                        a.manufactuer_id, 
                        a.model,
                        a.model_year, 
                        a.procurement, 
                        a.qqm,
                        a.machine_code,
                        a.deleted,
                        sd.description as state_deleted,
                        a.active, 
                        sd1.description as state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.language_id,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,
                        a.language_code,                        
                        COALESCE(NULLIF(a.picture, ''), 'image_not_found.png') AS picture
                FROM sys_machine_tools a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = a.machine_tool_grup_id AND mtg.active = 0 AND mtg.deleted = 0 AND mtg.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                             
                LEFT JOIN info_users u ON u.id = a.op_user_id                              
                ORDER BY a.language_id, mtg.group_name, a.machine_tool_name
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
     * @ sys_machine_tools tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  15.02.2016
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
                INSERT INTO sys_machine_tools(
                        machine_tool_name, 
                        machine_tool_name_eng, 
                        machine_tool_grup_id, 
                        manufactuer_id, 
                        model, 
                        model_year,                     
                        machine_code, 
                        language_id, 
                        op_user_id,                    
                        picture
                        )
                VALUES (
                        replace(upper('".$params['machine_tool_name']."'),'ı','I'),
                        replace(upper('".$params['machine_tool_name_eng']."'),'ı','I'),                        
                        :machine_tool_grup_id, 
                        :manufactuer_id, 
                        :model, 
                        :model_year,                    
                        :machine_code, 
                        :language_id, 
                        :op_user_id,                    
                        :picture
                                             )  
                    ";
                    $statement = $pdo->prepare($sql);
                    
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':manufactuer_id', $params['manufactuer_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':model', $params['model'], \PDO::PARAM_STR);
                    $statement->bindValue(':model_year', $params['model_year'], \PDO::PARAM_INT);                    
                    $statement->bindValue(':machine_code', $params['machine_code'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_STR);                    
                    $statement->bindValue(':picture', $params['picture'], \PDO::PARAM_STR);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tools_id_seq');
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
     * @ sys_machine_tools tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
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
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }                        
            $sql = " 
                SELECT  
			CONCAT(a.machine_tool_name) AS name , 
			'" . $params['machine_tool_name'] . "' AS value , 
			a.machine_tool_name = replace(upper('".$params['machine_tool_name']."'),'ı','I') AS control,
                CONCAT(a.machine_tool_name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
		FROM sys_machine_tools  a  
		INNER JOIN info_users_detail u ON u.root_id = a.op_user_id AND u.active = 0 AND u.deleted = 0                 
		WHERE a.machine_tool_name =replace(upper('".$params['machine_tool_name']."'),'ı','I') 
                    AND a.machine_tool_grup_id = " . intval($params['machine_tool_grup_id']) . "
                    AND a.manufactuer_id = " . intval($params['manufactuer_id']) . "                        
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
     * sys_machine_tools tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  15.02.2016
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
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

                    $sql = "
                UPDATE sys_machine_tools
                SET         
                    machine_tool_name = replace(upper('".$params['machine_tool_name']."'),'ı','I'), 
                    machine_tool_name_eng =  replace(upper('".$params['machine_tool_name_eng']."'),'ı','I'), 
                    machine_tool_grup_id = :machine_tool_grup_id, 
                    manufactuer_id = :manufactuer_id, 
                    model = :model, 
                    model_year = :model_year,                    
                    machine_code = :machine_code, 
                    language_id = :language_id, 
                    op_user_id = :op_user_id,                  
                    picture = :picture
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':manufactuer_id', $params['manufactuer_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':model', $params['model'], \PDO::PARAM_STR);
                    $statement->bindValue(':model_year', $params['model_year'], \PDO::PARAM_INT);                    
                    $statement->bindValue(':machine_code', $params['machine_code'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_STR);                    
                    $statement->bindValue(':picture', $params['picture'], \PDO::PARAM_STR);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {                    
                    $errorInfo = '23505';  // 23505 	unique_violation
                    $pdo->rollback();                   
                    $errorInfoColumn = 'machine_tool_name';
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
     * @ Gridi doldurmak için sys_machine_tools tablosundan kayıtları döndürür !!
     * @version v 1.0  15.02.2016
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
            $sort = "a.language_id, mtg.group_name, a.machine_tool_name";
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

        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($params['language_code']) && $params['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }  
        $whereSQL .= "  AND a.language_id = " . intval($languageIdValue);

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                        a.id, 
                        mtg.group_name,
                        a.machine_tool_name, 
                        a.machine_tool_name_eng, 
                        a.machine_tool_grup_id, 
                        a.manufactuer_id, 
                        a.model, 
                        a.model_year, 
                        a.procurement, 
                        a.qqm, 
                        a.machine_code,	                   
                        a.deleted, 
                        sd.description AS state_deleted,                 
                        a.active, 
                        sd1.description AS state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.language_id,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                        a.language_code,
                        COALESCE(NULLIF(a.picture, ''), 'image_not_found.png') AS picture
                FROM sys_machine_tools a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = a.machine_tool_grup_id AND mtg.active = 0 AND mtg.deleted = 0 AND mtg.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_id = a.language_id AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_id = a.language_id AND sd1.deleted = 0 AND sd1.active = 0                             
                LEFT JOIN info_users u ON u.id = a.op_user_id  
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
     * user interface datagrid fill operation get row count for widget
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_machine_tools tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  15.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }
            $whereSQL = "  WHERE a.deleted =0 AND a.language_id = " . intval($languageIdValue) . ",";

            $sql = "
               SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_machine_tools a                  
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN sys_machine_tool_groups mtg ON mtg.id = a.machine_tool_grup_id AND mtg.active = 0 AND mtg.deleted = 0 AND mtg.language_id = a.language_id
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = 'tr' AND sd.deleted = 0 AND sd.active = 0
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = 'tr' AND sd1.deleted = 0 AND sd1.active = 0
                " . $whereSQL . "
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_machine_tools tablosundan kayıtları döndürür !!
     * @version v 1.0  16.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getMachineTools($args = array()) {
       
        if (isset($args['page']) && $args['page'] != "" && isset($args['rows']) && $args['rows'] != "") {
            $offset = ((intval($args['page']) - 1) * intval($args['rows']));
            $limit = intval($args['rows']);
        } else {
            $limit = 10;
            $offset = 0;
        }

        $sortArr = array();
        $orderArr = array();
        $addSql = NULL;
                        
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = " machine_tool_name, group_name, manufacturer_name";
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

        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($args['language_code']) && $args['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }                
        // sql query dynamic for filter operations
        $sorguStr = null;
        if (isset($args['filterRules'])) {
            $filterRules = trim($args['filterRules']);
            $jsonFilter = json_decode($filterRules, true);
            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'machine_tool_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng) )" . $sorguExpression . ' ';
                            
                            break;
                        case 'machine_tool_name_eng':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(mt.machine_tool_name_eng)" . $sorguExpression . ' ';

                            break;
                        case 'group_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng))" . $sorguExpression . ' ';

                            break;
                         case 'manufacturer_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF((m.name), ''), ' '))" . $sorguExpression . ' ';

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
        
                        
        if (isset($args['machine_tool_grup_id']) && $args['machine_tool_grup_id'] != "") {
            $addSql = " AND mt.machine_tool_grup_id = " . intval($args['machine_tool_grup_id']) ;
        }
                        
                        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                    id, 
                    machine_tool_name,
                    machine_tool_name_eng ,
                    group_name,
                    group_name_eng,
                    manufacturer_name,
                    active,
                    deleted,
                    machine_tool_grup_id, 
                    manufactuer_id,
                    model,
                    model_year,
                    machine_code,
                    language_id,
                    picture
                FROM (
                SELECT
                    mt.id, 
                    COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng) AS machine_tool_name,   
                    mt.machine_tool_name_eng,
                    COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng) AS group_name,   
                    a.group_name_eng,
                    COALESCE(NULLIF((m.name), ''), ' ') AS manufacturer_name,
                    mt.active,
                    mt.deleted,
                    mt.machine_tool_grup_id, 
                    mt.manufactuer_id,
                    COALESCE(NULLIF((mt.model), ''), ' ') AS model,
                    mt.model_year,
                    COALESCE(NULLIF((mt.machine_code), ''), ' ') AS machine_code,
                    mt.language_id,
                    CASE COALESCE(NULLIF(mt.picture, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(mt.picture, ''),'image_not_found.png'))
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(mt.picture, ''),'image_not_found.png')) END AS picture
                FROM sys_machine_tool_groups a 
		INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0   
		INNER JOIN sys_machine_tools mt ON mt.machine_tool_grup_id = a.id AND mt.language_id = l.id AND mt.deleted =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_machine_tools mtx ON (mtx.id = mt.id OR mtx.language_parent_id = mt.id) AND mtx.language_id = lx.id AND mtx.deleted =0 
		LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted =0  
		LEFT JOIN sys_manufacturer m ON m.id = mt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                WHERE 
                    a.deleted = 0 AND 
                    mt.language_parent_id =0 
                    ". $addSql ."
                    " . $sorguStr . "  
                    ORDER BY    " . $sort . " 
                    " . $order . " 
                    LIMIT " . $pdo->quote($limit) . " 
                    OFFSET " . $pdo->quote($offset) . "                     
                ) AS xtablee WHERE deleted =0  
                            ";
            $statement = $pdo->prepare($sql);
            $parameters = array(
                'sort' => $sort,
                'order' => $order,
                'limit' => $pdo->quote($limit),
                'offset' => $pdo->quote($offset),
            );
         // echo debugPDO($sql, $parameters);
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
     * @ Gridi doldurmak için sys_machine_tools tablosundan kayıtları döndürür !!
     * @version v 1.0  16.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getMachineToolsRtc($params = array()) {                           
        $addSql = NULL; 
        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($params['language_code']) && $params['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }                 
        // sql query dynamic for filter operations
        $sorguStr = null;
        if (isset($params['filterRules'])) {
            $filterRules = trim($params['filterRules']);
            $jsonFilter = json_decode($filterRules, true);
            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'machine_tool_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\') ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng) )" . $sorguExpression . ' ';
                            
                            break;
                        case 'machine_tool_name_eng':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(mt.machine_tool_name_eng)" . $sorguExpression . ' ';

                            break;
                        case 'group_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng))" . $sorguExpression . ' ';

                            break;
                         case 'manufacturer_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF((m.name), ''), ' '))" . $sorguExpression . ' ';

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
          
        if (isset($params['machine_tool_grup_id']) && $params['machine_tool_grup_id'] != "") {
            $addSql = " AND mt.machine_tool_grup_id = " . intval($params['machine_tool_grup_id']) ;
        }
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT                    
                    count(id) AS COUNT FROM 
                    (SELECT 
                        id, 
                        machine_tool_name,
                        machine_tool_name_eng ,
                        group_name,
                        group_name_eng,
                        manufacturer_name,
                        active,
                        deleted, 
                        language_id                      
                    FROM (
                        SELECT
                            mt.id, 
                            COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng) AS machine_tool_name,   
                            mt.machine_tool_name_eng,
                            COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng) AS group_name,   
                            a.group_name_eng,
                            COALESCE(NULLIF((m.name), ''), ' ') AS manufacturer_name,
                            mt.active,
                            mt.deleted,
                            mt.machine_tool_grup_id,                        
                            mt.language_id
                        FROM sys_machine_tool_groups a 
                        INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                        INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0   
                        INNER JOIN sys_machine_tools mt ON mt.machine_tool_grup_id = a.id AND mt.language_id = l.id AND mt.deleted =0 
                        LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                        LEFT JOIN sys_machine_tools mtx ON (mtx.id = mt.id OR mtx.language_parent_id = mt.id) AND mtx.language_id = lx.id AND mtx.deleted =0 
                        LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted =0  
                        LEFT JOIN sys_manufacturer m ON m.id = mt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                        WHERE 
                            a.deleted = 0 AND 
                            mt.language_parent_id =0  
                            ". $addSql ."
                            ". $sorguStr ."                        
                ) AS xtablee WHERE deleted =0
                ) AS xxtablee
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    /**

     * @author Okan CIRAN
     * @ sys_machine_tools tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  16.05.2016
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
                UPDATE sys_machine_tools
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_machine_tools
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
     * @ Gridi doldurmak için sys_machine_tools tablosundan kayıtları döndürür !!
     * @version v 1.0  16.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getMachineProperities($args = array()) {                        
        $addSql = NULL;

        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($args['language_code']) && $args['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }  
 
        if ((isset($args['machine_id']) && $args['machine_id'] != "")) {
            $addSql =  " AND mt.machine_tool_id = " .intval($args['machine_id']) ; 
        }     
        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT                    
                     mt.id, 
                     COALESCE(NULLIF( (mtpx.property_name), ''), mtp.property_name_eng) AS property_name,   
		     mtp.property_name_eng,
		     mt.property_value,
                     mt.property_string_value,
                     mt.unit_id ,                      
                     COALESCE(NULLIF((sux.unitcode), ''), su.unitcode_eng) AS unitcode,   
                     su.unitcode_eng,
                     mtp.active
                FROM sys_machine_tools a 
			INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0   		
		INNER JOIN sys_machine_tool_properties mt ON mt.machine_tool_id = a.id AND mt.deleted =0 AND mt.language_parent_id = 0 
		INNER JOIN sys_machine_tool_property_definition mtp ON mtp.id = mt.machine_tool_property_definition_id AND mtp.deleted =0  AND mtp.active =0  AND mtp.language_parent_id = 0 		
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN sys_machine_tool_property_definition mtpx ON mtp.id = mtpx.id AND mtpx.deleted =0  AND mtpx.active =0 AND mtpx.language_id = lx.id
		INNER JOIN sys_units su ON su.id =  mt.unit_id AND su.active =0 AND su.deleted =0 AND su.language_parent_id = 0 
		INNER JOIN sys_units sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND su.active =0 AND su.deleted =0 AND sux.language_id = lx.id
                WHERE 
                    a.deleted = 0 AND  
                    mt.language_parent_id =0 
                " . $addSql . "          
                ORDER BY property_name
                ";
            $statement = $pdo->prepare($sql);                        
           // echo debugPDO($sql, $parameters);
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
     * @ Danısman ekranı için -  sys_machine_tools tablosundan kayıtları grid formatında döndürür !!
     * @version v 1.0  18.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getMachineToolsGrid($args = array()) {
       
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
            $sort = " machine_tool_name, group_name, m.name";
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

        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($args['language_code']) && $args['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }               
                        
        // sql query dynamic for filter operations
        $sorguStr = null;
        if (isset($args['filterRules'])) {
            $filterRules = trim($args['filterRules']);
            $jsonFilter = json_decode($filterRules, true);
            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'machine_tool_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=" AND COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng)" . $sorguExpression . ' ';
                            
                            break;
                        case 'machine_tool_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND mt.machine_tool_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'group_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng)" . $sorguExpression . ' ';

                            break;
                         case 'manufacturer_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND m.name" . $sorguExpression . ' ';

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
                        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT
                    mt.id, 
                    COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng) AS machine_tool_name,   
                    mt.machine_tool_name_eng,
                    COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng) AS group_name,   
                    a.group_name_eng,
                    COALESCE(NULLIF((m.name), ''), ' ') AS manufacturer_name,
                    mt.active,
                    mt.machine_tool_grup_id, 
                    mt.manufactuer_id,
                    COALESCE(NULLIF((mt.model), ''), ' ') AS model,
                    mt.model_year,
                    COALESCE(NULLIF((mt.machine_code), ''), ' ') AS machine_code,
                    mt.language_id                    
                FROM sys_machine_tool_groups a 		
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0   
		INNER JOIN sys_machine_tools mt ON mt.machine_tool_grup_id = a.id AND mt.language_id = l.id AND mt.deleted =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_machine_tools mtx ON (mtx.id = mt.id OR mtx.language_parent_id = mt.id) AND mtx.language_id = lx.id AND mtx.deleted =0 
		LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted =0  
		LEFT JOIN sys_manufacturer m ON m.id = mt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                WHERE 
                    a.deleted = 0 AND 
                    mt.language_parent_id =0         
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
           // echo debugPDO($sql, $parameters);
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
     * @ Danısman ekranı için -  sys_machine_tools tablosundan kayıtların sayısını döndürür !!
     * @version v 1.0  18.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getMachineToolsGridRtc($params = array()) {     
        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($params['language_code']) && $params['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }        
                        
        // sql query dynamic for filter operations
        $sorguStr = null;
        if (isset($params['filterRules'])) {
            $filterRules = trim($params['filterRules']);
            $jsonFilter = json_decode($filterRules, true);
            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'machine_tool_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=" AND COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng)" . $sorguExpression . ' ';
                            
                            break;
                        case 'machine_tool_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND mt.machine_tool_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'group_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND COALESCE(NULLIF((ax.group_name), ''), a.group_name_eng)" . $sorguExpression . ' ';

                            break;
                         case 'manufacturer_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND m.name" . $sorguExpression . ' ';

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
                        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT                    
                    count(mt.id) AS COUNT                      
                FROM sys_machine_tool_groups a 
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0   
		INNER JOIN sys_machine_tools mt ON mt.machine_tool_grup_id = a.id AND mt.language_id = l.id AND mt.active =0 AND mt.deleted =0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_machine_tools mtx ON (mtx.id = mt.id OR mtx.language_parent_id =mt.id) AND mtx.language_id = lx.id AND mtx.deleted =0 AND mtx.active =0 
		LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id =a.id) AND ax.language_id = lx.id AND ax.deleted =0 AND ax.active =0 
		LEFT JOIN sys_manufacturer m ON m.id = mt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0                 
                WHERE            
                    a.deleted = 0 AND                    
                    mt.language_parent_id =0 
            
                ".$sorguStr;
            $statement = $pdo->prepare($sql);            
         //  echo debugPDO($sql, $params);
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

    
}
