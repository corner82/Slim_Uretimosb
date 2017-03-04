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
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
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
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                        $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageCode = 'tr';
                    $languageIdValue = 647;
                    if (isset($params['language_code']) && $params['language_code'] != "") {
                        $languageCode = $params['language_code'];
                    }
                    $languageCodeParams = array('language_code' => $languageCode,);
                    $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                    $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
                    if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                        $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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

        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
                        
        $sort_default = NULL;                
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
             $sort_default = " ORDER BY mt.id desc ";
        //     $sort = " machine_tool_name, group_name, manufacturer_name";
             //$sort = NULL;             
        }
        
        if ($sort == 'id') {$sort_default = " ORDER BY mt.id desc ";}
        
        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            $order = "ASC";
        }

        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng)  )" . $sorguExpression . ' ';
                        
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
                        case 'model':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF((mt.model), ''), ' '))" . $sorguExpression . ' ';

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
              
		INNER JOIN sys_machine_tools mt ON mt.machine_tool_grup_id = a.id AND mt.language_parent_id = 0 AND mt.deleted =0 
                INNER JOIN sys_manufacturer m ON m.id = mt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
		LEFT JOIN sys_machine_tools mtx ON (mtx.id = mt.id OR mtx.language_parent_id = mt.id) AND mtx.language_id = lx.id AND mtx.deleted =0 AND mtx.active =0
                LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted =0 AND mtx.active =0
                WHERE 
                    a.deleted = 0 AND 
                    a.language_parent_id =0 
                    ". $addSql ."
                    " . $sorguStr . "  
                    ".$sort_default."
                    LIMIT " . $pdo->quote($limit) . " 
                    OFFSET " . $pdo->quote($offset) . "                     
                ) AS xtablee WHERE deleted =0  
                 ORDER BY    " . $sort . " "
                    . "" . $order . " "
                   ;                    
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
        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($params['language_code']) && $params['language_code'] != "") {
            $languageCode = $params['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF( (mtx.machine_tool_name), ''), mt.machine_tool_name_eng)  )" . $sorguExpression . ' ';
                            
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
                        case 'model':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(COALESCE(NULLIF((mt.model), ''), ' '))" . $sorguExpression . ' ';

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
                        FROM sys_machine_tool_groups a                         
                        
                        INNER JOIN sys_machine_tools mt ON mt.machine_tool_grup_id = a.id AND mt.language_parent_id = 0 AND mt.deleted =0 
                        INNER JOIN sys_manufacturer m ON m.id = mt.manufactuer_id AND m.deleted =0 AND m.active =0 AND m.language_parent_id = 0 
                        LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                        LEFT JOIN sys_machine_tools mtx ON (mtx.id = mt.id OR mtx.language_parent_id = mt.id) AND mtx.language_id = lx.id AND mtx.deleted =0 AND mtx.active =0
                        LEFT JOIN sys_machine_tool_groups ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id AND ax.deleted =0 AND mtx.active =0
                        WHERE 
                            a.deleted = 0 AND 
                            a.language_parent_id =0  
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
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
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

        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
        }        
 
        if ((isset($args['machine_id']) && $args['machine_id'] != "")) {
            $addSql =  " AND mt.machine_tool_id = " .intval($args['machine_id']) ; 
        }     
        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
            SELECT                    
                id,                 
		CASE
                    WHEN material_name IS NOT NULL THEN CONCAT(property_name,' (' ,material_name ,')' ) 
                    ELSE property_name END AS property_name,         
                CASE  
                    WHEN material_name_eng IS NOT NULL THEN CONCAT(property_name_eng,' (' ,material_name_eng ,')' ) 
                    ELSE property_name_eng END AS property_name_eng,		     
                REPLACE(COALESCE(NULLIF(COALESCE(NULLIF(property_value,'-1.000'),NULL),'-1'),NULL),'.000','') AS property_value, 
                property_string_value,
                unit_id ,                      
                unitcode,   
                unitcode_eng,
                active,
                model_materials_id,
                material_name,
                material_name_eng
            FROM (
                SELECT
                     mt.id, 
                     COALESCE(NULLIF((mtpx.property_name), ''), mtp.property_name_eng) AS property_name,   
		     mtp.property_name_eng,
		     CASE WHEN length(trim(mt.property_string_value))>0 THEN CAST(mt.property_string_value AS character varying(150)) 
			ELSE CAST(mt.property_value AS character varying(150))
			END AS property_value, 
                     mt.property_string_value,
                     mt.unit_id,
                     COALESCE(NULLIF((sux.unitcode), ''), su.unitcode_eng) AS unitcode,   
                     su.unitcode_eng,
                     mtp.active,
                     mt.model_materials_id,
                     COALESCE(NULLIF(srwx.name, ''), srw.name_eng) AS material_name,
                     srw.name_eng AS material_name_eng
                FROM sys_machine_tools a 
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0
		INNER JOIN sys_machine_tool_properties mt ON mt.machine_tool_id = a.id AND mt.deleted =0 AND mt.language_parent_id = 0 
		INNER JOIN sys_machine_tool_property_definition mtp ON mtp.id = mt.machine_tool_property_definition_id AND mtp.deleted =0  AND mtp.active =0  AND mtp.language_parent_id = 0
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0 
                LEFT JOIN sys_machine_tool_property_definition mtpx ON mtp.id = mtpx.id AND mtpx.deleted =0  AND mtpx.active =0 AND mtpx.language_id = lx.id
		INNER JOIN sys_units su ON su.id =  mt.unit_id AND su.active =0 AND su.deleted =0 AND su.language_parent_id = 0 
		INNER JOIN sys_units sux ON (sux.id = su.id OR sux.language_parent_id = su.id) AND su.active =0 AND su.deleted =0 AND sux.language_id = lx.id
                LEFT JOIN sys_raw_materials srw ON srw.id = mt.model_materials_id AND srw.active =0 AND srw.deleted =0  AND srw.language_parent_id = 0
                LEFT JOIN sys_raw_materials srwx ON (srwx.id = srw.id OR srwx.language_parent_id = srw.id) AND srwx.language_id = lx.id
                WHERE 
                    a.deleted = 0 AND  
                    mt.language_parent_id =0 
                " . $addSql . "
            ) AS xtable
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
        $sort_default = NULL;                
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
          //   $sort_default = " ORDER BY mt.id desc ";
             $sort = "mt.id";
             //$sort = NULL;
        }      
         if ($sort == 'id') {$sort = "mt.id";}     

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            $order = "ASC";
        }

        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
                            $sorguStr.=" AND COALESCE(NULLIF( (LOWER(mtx.machine_tool_name)), ''), LOWER(mt.machine_tool_name_eng))" . $sorguExpression . ' ';
                            
                            break;
                        case 'machine_tool_name_eng':
                            $sorguExpression = ' ILIKE LOWER( \'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(mt.machine_tool_name_eng)" . $sorguExpression . ' ';

                            break;
                        case 'group_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND COALESCE(NULLIF((LOWER(ax.group_name)), ''), a.group_name_eng)" . $sorguExpression . ' ';

                            break;
                        case 'manufacturer_name':
                            $sorguExpression = ' ILIKE LOWER( \'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(m.name)" . $sorguExpression . ' ';

                            break;
                        case 'model':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND COALESCE(NULLIF((LOWER(mt.model)), ''), ' ')" . $sorguExpression . ' ';

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
        $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($params['language_code']) && $params['language_code'] != "") {
            $languageCode = $params['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
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
                            $sorguStr.=" AND COALESCE(NULLIF( (LOWER(mtx.machine_tool_name)), ''), LOWER(mt.machine_tool_name_eng))" . $sorguExpression . ' ';
                            
                            break;
                        case 'machine_tool_name_eng':
                            $sorguExpression = ' ILIKE LOWER( \'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(mt.machine_tool_name_eng)" . $sorguExpression . ' ';

                            break;
                        case 'group_name':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND COALESCE(NULLIF((LOWER(ax.group_name)), ''), a.group_name_eng)" . $sorguExpression . ' ';

                            break;
                        case 'manufacturer_name':
                            $sorguExpression = ' ILIKE LOWER( \'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND LOWER(m.name)" . $sorguExpression . ' ';

                            break;
                        case 'model':
                            $sorguExpression = ' ILIKE LOWER(\'%' . $std['value'] . '%\')  ';
                            $sorguStr.=" AND COALESCE(NULLIF((LOWER(mt.model)), ''), ' ')" . $sorguExpression . ' ';

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

    /**
     * @author Okan CIRAN
     * @ SSM için hazırlanmış firma bulma servisi !! 
     * parametre olarak 
     *      machine_tool_grup_id , machine_id ; 
     *      machine_name ve machine_name_eng içinde like ile arama yapılacak karakterler ; 
     *      
     * @version v 1.0  05.12.2016     
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineAdvSearchSsm($params = array()) {
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
            $sort = " firm_name ";
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

            
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }
            
            $parentIdSql1 = "";
            $parentIdSql2 = "";
            if (isset($params['machine_group_id']) && $params['machine_group_id'] != "") {
                $parentIdSql1 = " abh.root_id = (SELECT DISTINCT axxh.root_id FROM sys_machine_tool_groups axxh WHERE axxh.id = ".intval($params['machine_group_id'])." limit 1 ) AND " ;                
                $parentIdSql2 = " WHERE ddd = ".intval($params['machine_group_id'])." " ;
                $parentIdSql3 = " smtg.root_id = (SELECT DISTINCT axxhz.root_id FROM sys_machine_tool_groups axxhz WHERE axxhz.id = ".intval($params['machine_group_id'])." limit 1 ) AND " ;
            }
            $machineToolIdSql = "" ;
            if (isset($params['machine_tool_id']) && $params['machine_tool_id'] != "") {
                $machineToolIdSql = " smt.id = ".  intval($params['machine_tool_id']) . " AND " ;
                //   mth.id = ".intval($machineToolId)." AND
            }
            $machineToolNameSql = "";
            if (isset($params['machine_tool_name']) && $params['machine_tool_name'] != "") {
                $machineToolNameSql =  " COALESCE(NULLIF( (LOWER(smtx.machine_tool_name)), ''), LOWER(smt.machine_tool_name_eng)) LIKE LOWER('%".$params['machine_tool_name']."%') AND " ;
            }
            $machineToolNameEngSql = "";
            if (isset($params['machine_tool_name_eng']) && $params['machine_tool_name_eng'] != "") {
                $machineToolNameEngSql =  " LOWER(smt.machine_tool_name_eng) LIKE LOWER('%".$params['machine_tool_name_eng']."%') AND " ;
            }
            
            $certificateSql = "";
            if (isset($params['certificate_id']) && $params['certificate_id'] != "") {
               //json_each('{"0":9,"1":12 ,"2":8}')  
                $certificateSql =  "
                INNER JOIN info_firm_certificate ifc ON ifc.firm_id = fp.act_parent_id AND ifc.cons_allow_id = 2 AND
				ifc.certificate_id IN (
                                             SELECT 
						CAST(CAST(VALUE AS text) AS integer) FROM json_each('". $params['certificate_id']."') 						
						)  " ;
            }
            
           
            $totalPersonBetweenSql = "";
            if (isset($params['totalPerson']) && $params['totalPerson'] != "") {
                if ($params['totalPerson'] > 0) {
                    $total1 = 101;
                    $total2 = 0;
                    switch ($params['totalPerson']) {
                        case 1:
                            $total1 = 0;
                            $total2 = 10;
                            break;
                        case 2:
                            $total1 = 11;
                            $total2 = 25;
                            break;
                        case 3:
                            $total1 = 26;
                            $total2 = 50;
                            break;
                        case 4:
                            $total1 = 51;
                            $total2 = 100;
                            break;
                        case 5:
                            $total1 = 101;
                            break;
                        default:
                            break;
                    }
                    if ($total1 < 101) {
                        $totalPersonBetweenSql = "  a.total between " . intval($total1) . " AND " . intval($total2) . " AND ";
                    } else {
                        $totalPersonBetweenSql = "  a.total >" . intval($total1) - 1 . " AND ";
                    }
                }
            }


            $sql =" 
                SELECT * FROM (
                    SELECT DISTINCT
                        a.id,
                        a.firm_id,
                        COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                        fp.firm_name_eng, 
                        COALESCE(NULLIF(fpx.firm_name_short, ''), fp.firm_name_short_eng) AS firm_name_short,
                        fp.firm_name_short_eng,
                        a.sys_machine_tool_id,                        
                        COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_name,
                        smt.machine_tool_name_eng,
                        a.total,
                        smt.machine_tool_grup_id,
                        COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng) AS machine_tool_grup_name,
                        smtg.group_name_eng AS machine_tool_grup_name_eng, 
                        a.availability_id,
                        COALESCE(NULLIF(sd21x.description, ''), sd21.description_eng) AS state_availability,                        
                        a.ownership_id,
                        COALESCE(NULLIF(sd22x.description, ''), sd22.description_eng) AS state_ownership,                        
                        CASE smt.picture_upload 
                        WHEN false  THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(sm.machine_not_found_picture, ''),'image_not_found.png'))
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png')) END AS picture 
                    FROM info_firm_profile fp
                    INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                    INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    LEFT JOIN info_firm_profile fpx ON fpx.act_parent_id = fp.act_parent_id AND fpx.cons_allow_id = 2 AND fpx.language_id = lx.id
                    INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                    INNER JOIN info_firm_machine_tool a ON fp.act_parent_id = a.firm_id AND a.language_parent_id =0 AND a.cons_allow_id = 2 

                    INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_parent_id=0
                    LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id

                    INNER JOIN sys_manufacturer sm ON sm.id = smt.manufactuer_id AND sm.active =0 and sm.deleted =0 
                    
                    INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_parent_id=0 AND smtg.active =0 AND smtg.deleted =0 
                    LEFT JOIN sys_machine_tool_groups smtgx ON smtgx.active =0 AND smtgx.deleted = 0 AND (smtgx.id = smtg.id OR smtgx.language_parent_id = smtg.id )AND smtgx.language_id = lx.id

                    INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.first_group = a.cons_allow_id AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0		    
                    INNER JOIN sys_specific_definitions sd21 ON sd21.main_group = 21 AND sd21.first_group= a.availability_id AND sd21.deleted = 0 AND sd21.active = 0 AND sd21.language_parent_id =0
                    INNER JOIN sys_specific_definitions sd22 ON sd22.main_group = 22 AND sd22.first_group= a.ownership_id AND sd22.deleted = 0 AND sd22.active = 0 AND sd22.language_parent_id =0                  
                    ".$certificateSql ." 
                    LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                    LEFT JOIN sys_specific_definitions sd21x ON sd21x.main_group = 21 AND sd21x.language_id =lx.id AND (sd21x.id = sd21.id OR sd21x.language_parent_id = sd21.id) AND sd21x.deleted =0 AND sd21x.active =0 
                    LEFT JOIN sys_specific_definitions sd22x ON sd22x.main_group = 22 AND sd22x.language_id = lx.id AND (sd22x.id = sd22.id OR sd22x.language_parent_id = sd22.id) AND sd22x.deleted = 0 AND sd22x.active = 0

                    WHERE 
                        fp.language_parent_id = 0 AND
                        fp.cons_allow_id = 2 AND 
                        ". $parentIdSql3 ."                        
                        ". $machineToolIdSql ." 
                        ". $machineToolNameSql ." 
                        ". $machineToolNameEngSql ."  
                        ". $totalPersonBetweenSql ."
                        smt.id IN
                            (
                            SELECT                    
                                DISTINCT mth.id 
                            FROM sys_machine_tool_groups ah                             
                            INNER join sys_machine_tools mth ON mth.machine_tool_grup_id = ah.id AND mth.language_parent_id=0 AND mth.active =0 AND mth.deleted =0
                            WHERE
                                ah.deleted = 0 AND
                                ah.active =0 AND 
                                ah.language_parent_id = 0 AND                               
                                ah.id IN (
                                    SELECT 
                                        DISTINCT machine_tool_grup_id 
                                    FROM sys_machine_tools mtxh
                                    WHERE  
                                        mtxh.language_parent_id =0 AND
                                        mtxh.machine_tool_grup_id IN 
                                            ( SELECT DISTINCT id FROM (
                                                SELECT abh.id, abh.root_json::json#>>'{1}', abh.root_json, 
                                                CAST( CAST (json_array_elements(abh.root_json) AS text) AS integer) AS ddd 
                                                FROM sys_machine_tool_groups abh 
                                                WHERE 
                                                    ". $parentIdSql1 ." 
                                                    abh.active =0 AND 
                                                    abh.deleted =0 AND
                                                    abh.language_parent_id=0 
                                                ) AS xtable 
                                                ". $parentIdSql2 ."
                                            ) AND mtxh.active =0 AND mtxh.deleted =0 ) 
                                        )
                                LIMIT " . $pdo->quote($limit) . " 
                                OFFSET " . $pdo->quote($offset) . " 
                        ) AS xxtable 
                        ORDER BY    " . $sort . " 
                        " . $order . "" 
                        ;
                                
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
     * @ listbox ya da combobox doldurmak için sys_machine_tool_groups tablosundan kayıtları döndürür !!
     * @version v 1.0  05.12.2016     
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineAdvSearchSsmRtc($params = array()) {
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
            
            $parentIdSql1 = "";
            $parentIdSql2 = "";
            if (isset($params['machine_group_id']) && $params['machine_group_id'] != "") {
                $parentIdSql1 = " abh.root_id = (SELECT DISTINCT axxh.root_id FROM sys_machine_tool_groups axxh WHERE axxh.id = ".intval($params['machine_group_id'])." limit 1 ) AND " ;
                $parentIdSql2 = "  WHERE ddd = ".intval($params['machine_group_id'])." " ;
            }
            $machineToolIdSql = "" ;
            if (isset($params['machine_tool_id']) && $params['machine_tool_id'] != "") {
                $machineToolIdSql = " mth.id = ".  intval($params['machine_tool_id']) . " AND " ;
                //   mth.id = ".intval($machineToolId)." AND
            }
            $machineToolNameSql = "";
            if (isset($params['machine_tool_name']) && $params['machine_tool_name'] != "") {
                $machineToolNameSql =  " COALESCE(NULLIF( (LOWER(mtxh.machine_tool_name)), ''), LOWER(mth.machine_tool_name_eng)) LIKE LOWER('%".$params['machine_tool_name']."%') AND " ;
            }
            $machineToolNameEngSql = "";
            if (isset($params['machine_tool_name_eng']) && $params['machine_tool_name_eng'] != "") {
                $machineToolNameEngSql =  " LOWER(smt.machine_tool_name_eng) LIKE LOWER('%".$params['machine_tool_name_eng']."%') AND " ;
            }
            
            $certificateSql = "";
            if (isset($params['certificate_id']) && $params['certificate_id'] != "") {
               //json_each('{"0":9,"1":12 ,"2":8}')  
                $certificateSql =  "
                INNER JOIN info_firm_certificate ifc ON ifc.firm_id = fp.act_parent_id AND ifc.cons_allow_id = 2 AND
				ifc.certificate_id IN (
                                             SELECT 
						CAST(CAST(VALUE AS text) AS integer) FROM json_each('". $params['certificate_id']."') 						
						)  " ;
            }
            $totalPersonBetweenSql = "";
            if (isset($params['totalPerson']) && $params['totalPerson'] != "") {
                if ($params['totalPerson'] > 0) {
                    $total1 = 101;
                    $total2 = 0;
                    switch ($params['totalPerson']) {
                        case 1:
                            $total1 = 0;
                            $total2 = 10;
                            break;
                        case 2:
                            $total1 = 11;
                            $total2 = 25;
                            break;
                        case 3:
                            $total1 = 26;
                            $total2 = 50;
                            break;
                        case 4:
                            $total1 = 51;
                            $total2 = 100;
                            break;
                        case 5:
                            $total1 = 101;
                            break;
                        default:
                            break;
                    }
                    if ($total1 < 101) {
                        $totalPersonBetweenSql = "  a.total between " . intval($total1) . " AND " . intval($total2) . " AND ";
                    } else {
                        $totalPersonBetweenSql = "  a.total >" . intval($total1) - 1 . " AND ";
                    }
                }
            }

            
            $sql =" SELECT COUNT(id) AS count FROM (
                SELECT DISTINCT
                    a.id,
                    a.firm_id,
                    COALESCE(NULLIF(fpx.firm_name, ''), fp.firm_name_eng) AS firm_name,
                    fp.firm_name_eng, 
                    COALESCE(NULLIF(fpx.firm_name_short, ''), fp.firm_name_short_eng) AS firm_name_short,
                    fp.firm_name_short_eng,
                    a.sys_machine_tool_id,                        
                    COALESCE(NULLIF(smtx.machine_tool_name, ''), smt.machine_tool_name_eng) AS machine_tool_name,
                    smt.machine_tool_name_eng,
                    a.total,
                    smt.machine_tool_grup_id,
                    COALESCE(NULLIF(smtgx.group_name, ''), smtg.group_name_eng) AS machine_tool_grup_name,
                    smtg.group_name_eng AS machine_tool_grup_name_eng, 
                    a.availability_id,
                    COALESCE(NULLIF(sd21x.description, ''), sd21.description_eng) AS state_availability,                        
                    a.ownership_id,
                    COALESCE(NULLIF(sd22x.description, ''), sd22.description_eng) AS state_ownership,                        
                    CASE smt.picture_upload 
                    WHEN false  THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(sm.machine_not_found_picture, ''),'image_not_found.png'))
                    ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.machines_folder,'/' ,COALESCE(NULLIF(smt.picture, ''),'image_not_found.png')) END AS picture 
                FROM info_firm_profile fp
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0                     
                INNER JOIN sys_language l ON l.id = fp.language_id AND l.deleted =0 AND l.active =0
                LEFT JOIN sys_language lx ON lx.id =  " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                LEFT JOIN info_firm_profile fpx ON fpx.act_parent_id = fp.act_parent_id AND fpx.cons_allow_id = 2 AND fpx.language_id = lx.id
                INNER JOIN info_firm_keys ifk ON fp.act_parent_id = ifk.firm_id  
                INNER JOIN info_firm_machine_tool a ON fp.act_parent_id = a.firm_id AND a.language_parent_id =0 AND a.cons_allow_id = 2 AND a.deleted =0 

                INNER JOIN sys_machine_tools smt ON smt.id = a.sys_machine_tool_id AND smt.active =0 AND smt.deleted = 0 AND smt.language_id = l.id
                LEFT JOIN sys_machine_tools smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND smtx.active =0 AND smtx.deleted = 0 AND smtx.language_id = lx.id
                INNER JOIN sys_manufacturer sm ON sm.id = smt.manufactuer_id AND sm.active =0 and sm.deleted =0 
                INNER JOIN sys_machine_tool_groups smtg ON smtg.id = smt.machine_tool_grup_id AND smtg.language_id = l.id AND smtg.active =0 AND smtg.deleted =0 
                LEFT JOIN sys_machine_tool_groups smtgx ON smtgx.active =0 AND smtgx.deleted = 0 AND (smtgx.id = smtg.id OR smtgx.language_parent_id = smtg.id )AND smtgx.language_id = lx.id
                
                INNER JOIN sys_specific_definitions sd14 ON sd14.main_group = 14 AND sd14.first_group = a.cons_allow_id AND sd14.deleted =0 AND sd14.active =0 AND sd14.language_parent_id =0		    
                INNER JOIN sys_specific_definitions sd21 ON sd21.main_group = 21 AND sd21.first_group= a.availability_id AND sd21.deleted = 0 AND sd21.active = 0 AND sd21.language_parent_id =0
                INNER JOIN sys_specific_definitions sd22 ON sd22.main_group = 22 AND sd22.first_group= a.ownership_id AND sd22.deleted = 0 AND sd22.active = 0 AND sd22.language_parent_id =0                  
                ".$certificateSql ." 
                LEFT JOIN sys_specific_definitions sd14x ON sd14x.main_group = 14 AND sd14x.language_id = lx.id AND (sd14x.id = sd14.id OR sd14x.language_parent_id = sd14.id) AND sd14x.deleted =0 AND sd14x.active =0
                LEFT JOIN sys_specific_definitions sd21x ON sd21x.main_group = 21 AND sd21x.language_id =lx.id AND (sd21x.id = sd21.id OR sd21x.language_parent_id = sd21.id) AND sd21x.deleted =0 AND sd21x.active =0 
                LEFT JOIN sys_specific_definitions sd22x ON sd22x.main_group = 22 AND sd22x.language_id = lx.id AND (sd22x.id = sd22.id OR sd22x.language_parent_id = sd22.id) AND sd22x.deleted = 0 AND sd22x.active = 0
		    
                WHERE 
                    fp.language_parent_id = 0 AND
                    fp.cons_allow_id = 2 AND 
                     ".$totalPersonBetweenSql."
                    smt.id IN
                        (
                        SELECT                    
                            DISTINCT mth.id 
                        FROM sys_machine_tool_groups ah 
                        INNER JOIN sys_language lh ON lh.id = ah.language_id AND lh.deleted =0 AND lh.active =0   
                        INNER join sys_machine_tools mth on mth.machine_tool_grup_id = ah.id AND mth.language_id = lh.id AND mth.active =0 AND mth.deleted =0 
                        LEFT JOIN sys_language lxh ON lxh.id = " . intval($languageIdValue) . " AND lxh.deleted =0 AND lxh.active =0 
                        LEFT join sys_machine_tools mtxh on mtxh.id = mth.id AND mtxh.language_id = lxh.id AND mtxh.deleted =0 AND mtxh.active =0 AND mtxh.language_parent_id =0                         
                        WHERE
                            ah.deleted = 0 AND
                            ah.active =0 and 
                            ah.language_parent_id = 0 AND
                            ". $machineToolIdSql ." 
                            ". $machineToolNameSql ." 
                            ". $machineToolNameEngSql ."  
                            ah.id IN (
                                SELECT 
                                    DISTINCT machine_tool_grup_id 
                                FROM sys_machine_tools mtxh
                                WHERE  
                                    mtxh.machine_tool_grup_id IN 
                                        ( SELECT DISTINCT id FROM (
                                            SELECT abh.id, abh.root_json::json#>>'{1}', abh.root_json, 
                                            CAST( CAST (json_array_elements(abh.root_json) AS text) AS integer) AS ddd 
                                            FROM sys_machine_tool_groups abh 
                                            WHERE 
                                                ". $parentIdSql1 ." 
                                                abh.active =0 AND 
                                                abh.deleted =0 
                                            ) AS xtable 
                                            ". $parentIdSql2 ."
                                        ) AND mtxh.active =0 AND mtxh.deleted =0 ) 
                                    ) ) AS xxxtable
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
