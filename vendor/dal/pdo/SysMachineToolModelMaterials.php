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
 * @since 27.10.2016
 */
class SysMachineToolModelMaterials extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_machine_tool_model_materials tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  27.10.2016
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
                UPDATE sys_machine_tool_model_materials
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id = :id");
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
     * @ sys_machine_tool_model_materials tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  27.10.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }       
            $languageCodeParams = array('language_code' => $languageCode,);            
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                 $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
            }   
            $statement = $pdo->prepare("
                SELECT 
                    a.id, 
                    a.machine_tool_id, 
                    COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_names,  
                    mt.machine_tool_name_eng,             
                    a.material_id, 
                    COALESCE(NULLIF(srmx.name, ''), srm.name_eng) AS material_name,  
                    srm.name_eng AS material_name_eng,  
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,                    
                    l.id AS language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name
                FROM sys_machine_tool_model_materials a
		INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id AND mt.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = mt.language_id AND l.deleted =0 AND l.active = 0  
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_raw_materials srm ON srm.id = a.material_id AND srm.active=0 and srm.deleted =0 
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_raw_materials srmx ON (srmx.id= srm.id OR srmx.id= srm.language_parent_id) AND srmx.language_id = lx.id AND srmx.deleted = 0 AND srmx.active = 0
                ORDER BY machine_tool_names, material_name
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
     * @ sys_machine_tool_model_materials tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  27.10.2016
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
                     $machineToolId = 0;
                    if ((isset($params['machine_tool_id']) && $params['machine_tool_id'] != "")) {
                        $machineToolId = $params['machine_tool_id'];
                    }
                    $materialId = 0;
                    if ((isset($params['material_id']) && $params['material_id'] != "")) {
                        $materialId = $params['material_id'];
                    }

                    $sql = "
                INSERT INTO sys_machine_tool_model_materials(
                        machine_tool_id, 
                        material_id,
                        op_user_id                        
                        )
                VALUES (
                        :machine_tool_id, 
                        :material_id,
                        :op_user_id                        
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_id', $machineToolId, \PDO::PARAM_INT);
                    $statement->bindValue(':material_id', $materialId, \PDO::PARAM_INT);                    
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);                    
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tool_model_materials_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'material_id';
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
     * @ sys_machine_tool_model_materials tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 27.10.2016
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
                 a.material_id AS name , 
                 a.material_id AS value , 
                 1 =1 AS control,
                 CONCAT(' Bu materyal bu makinaya daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_machine_tool_model_materials a            
            WHERE a.machine_tool_id = " . intval($params['machine_tool_id']) . " AND
                  a.material_id =" . intval($params['material_id']) . "
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
     * sys_machine_tool_model_materials tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  27.10.2016
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
                $machineToolId = 0;
                if ((isset($params['machine_tool_id']) && $params['machine_tool_id'] != "")) {
                    $machineToolId = $params['machine_tool_id'];
                }
                $materialId = 0;
                if ((isset($params['material_id']) && $params['material_id'] != "")) {
                    $materialId = $params['material_id'];
                }

                $sql = "
                UPDATE sys_machine_tool_model_materials
                SET   
                    machine_tool_id = " . intval($machineToolId) . ", 
                    material_id = " . intval($materialId) . ",  
                    op_user_id = " . intval($opUserIdValue) . "   
                WHERE 
                    id = " . intval($params['id']) . "  
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
                // 23505 	unique_violation
                $errorInfo = '23505';
                $errorInfoColumn = 'material_id';
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
     * @ Gridi doldurmak için sys_machine_tool_model_materials tablosundan kayıtları döndürür !!
     * @version v 1.0  27.10.2016
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
            $sort = "machine_tool_names, material_name  ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
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
        $whereSql = " AND a.language_id = " . intval($languageIdValue);
  
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                    a.id, 
                    a.machine_tool_id, 
                    COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_names,  
                    mt.machine_tool_name_eng,             
                    a.material_id, 
                    COALESCE(NULLIF(srmx.name, ''), srm.name_eng) AS material_name,  
                    srm.name_eng AS material_name_eng,  
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,                    
                    l.id AS language_id, 
                    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name
                FROM sys_machine_tool_model_materials a
		INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id AND mt.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = mt.language_id AND l.deleted =0 AND l.active = 0  
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_raw_materials srm ON srm.id = a.material_id AND srm.active=0 and srm.deleted =0 
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_raw_materials srmx ON (srmx.id= srm.id OR srmx.id= srm.language_parent_id) AND srmx.language_id = lx.id AND srmx.deleted = 0 AND srmx.active = 0                   
                WHERE a.deleted =0 
                " . $whereSql . "
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
     * @ Gridi doldurmak için sys_machine_tool_model_materials tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  27.10.2016
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
            $whereSql = " WHERE a.deleted =0 ";
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_machine_tool_model_materials a
		INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id AND mt.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = mt.language_id AND l.deleted =0 AND l.active = 0  
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_raw_materials srm ON srm.id = a.material_id AND srm.active=0 and srm.deleted =0 
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_raw_materials srmx ON (srmx.id= srm.id OR srmx.id= srm.language_parent_id) AND srmx.language_id = lx.id AND srmx.deleted = 0 AND srmx.active = 0                   
                " . $whereSql . "               
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
     * @ sys_machine_tool_model_materials bilgilerini döndürür !!
     * filterRules aktif 
     * @version v 1.0  28.10.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolModelListGrid($params = array()) {
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
                $sort = " machine_tool_name, material_name";
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
                            case 'machine_tool_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND mt.machine_tool_name_eng" . $sorguExpression . ' ';

                                break;  
                            case 'material_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(srmx.name, ''), srm.name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'material_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND srm.name_eng" . $sorguExpression . ' ';

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
            $addSql=null;
            if (isset($params['machine_tool_id']) && $params['machine_tool_id'] != "") {
            $addSql = " AND a.machine_tool_id = " . intval($params['machine_tool_id']) ;
            }
            
            $sql = "  
                SELECT 
                    a.id, 
                    a.machine_tool_id, 
                    COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_name,  
                    mt.machine_tool_name_eng,
                    a.material_id, 
                    COALESCE(NULLIF(srmx.name, ''), srm.name_eng) AS material_name,  
                    srm.name_eng AS material_name_eng,  
                    a.deleted, 
                    sd15.description AS state_deleted,
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                    COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name
                FROM sys_machine_tool_model_materials a
                INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id AND mt.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = mt.language_id AND l.deleted =0 AND l.active = 0  
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_raw_materials srm ON srm.id = a.material_id AND srm.active=0 and srm.deleted =0 
                LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_raw_materials srmx ON (srmx.id= srm.id OR srmx.id= srm.language_parent_id) AND srmx.language_id = lx.id AND srmx.deleted = 0 AND srmx.active = 0                   
                WHERE a.deleted =0  
                    ". $sorguStr ."  
                    ". $addSql ."
                ORDER BY    " . $sort . " "
                    . "" . $order . "
                LIMIT " . $pdo->quote($limit) . "  
                OFFSET " . $pdo->quote($offset) . "                                                          
                        ";
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
     * @ sys_machine_tool_model_materials bilgilerinin sayısını döndürür !!
     * filterRules aktif 
     * @version v 1.0 28.10.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                       
    public function fillMachineToolModelListGridRtc($params = array()) {
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
                             case 'machine_tool_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'machine_tool_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND mt.machine_tool_name_eng" . $sorguExpression . ' ';

                                break;  
                            case 'material_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(srmx.name, ''), srm.name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'material_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND srm.name_eng" . $sorguExpression . ' ';

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
            
            $addSql=null;
            if (isset($params['machine_tool_id']) && $params['machine_tool_id'] != "") {
            $addSql = " AND a.machine_tool_id = " . intval($params['machine_tool_id']) ;
            }
            
            $sql = " 
                SELECT count(id) FROM (
                    SELECT 
                        a.id, 
                        a.machine_tool_id, 
                        COALESCE(NULLIF(mt.machine_tool_name, ''), mt.machine_tool_name_eng) AS machine_tool_name,  
                        mt.machine_tool_name_eng,
                        a.material_id, 
                        COALESCE(NULLIF(srmx.name, ''), srm.name_eng) AS material_name,  
                        srm.name_eng AS material_name_eng,  
                        a.deleted, 
                        sd15.description AS state_deleted,
                        a.active, 
                        sd16.description AS state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                        COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name
                    FROM sys_machine_tool_model_materials a
                    INNER JOIN sys_machine_tools mt ON mt.id = a.machine_tool_id AND mt.language_parent_id =0 
                    INNER JOIN sys_language l ON l.id = mt.language_id AND l.deleted =0 AND l.active = 0  
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active = 0 
                    INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = 647 AND sd15.deleted = 0 AND sd15.active = 0
                    INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = 647 AND sd16.deleted = 0 AND sd16.active = 0                             
                    INNER JOIN info_users u ON u.id = a.op_user_id   
                    INNER JOIN sys_raw_materials srm ON srm.id = a.material_id AND srm.active=0 and srm.deleted =0 
                    LEFT JOIN sys_specific_definitions sd15x ON (sd15x.id= sd15.id OR sd15x.id= sd15.language_parent_id) AND sd15x.language_id = lx.id AND sd15x.deleted = 0 AND sd15x.active = 0
                    LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id= sd16.id OR sd16x.id= sd16.language_parent_id) AND sd16x.language_id = lx.id AND sd16x.deleted = 0 AND sd16x.active = 0
                    LEFT JOIN sys_raw_materials srmx ON (srmx.id= srm.id OR srmx.id= srm.language_parent_id) AND srmx.language_id = lx.id AND srmx.deleted = 0 AND srmx.active = 0                   
                    WHERE a.deleted =0  
                        ". $sorguStr ."   
                        ". $addSql ." 
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
     * @ sys_machine_tool_model_materials tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  28.10.2016
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
                UPDATE sys_machine_tool_model_materials
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_machine_tool_model_materials
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
