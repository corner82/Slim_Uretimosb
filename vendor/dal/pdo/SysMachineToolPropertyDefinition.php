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
 * @since 17.02.2016
 */
class SysMachineToolPropertyDefinition extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_machine_tool_property_definition tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  17.02.2016
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
                UPDATE sys_machine_tool_property_definition
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id =  " . intval($params['id']));
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
     * @ sys_machine_tool_property_definition tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  17.02.2016  
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
                        a.property_name ,
                        a.property_name_eng,                                  
                        a.algorithmic_id,   
                        sd18.description AS state_algorithmic,                		                   
                        a.deleted, 
                        sd15.description AS state_deleted,                 
                        a.active, 
                        sd16.description AS state_active, 
                        a.language_code, 
                        a.language_id, 
			COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,               
			a.language_parent_id,                     
                        a.op_user_id,
                        u.username AS op_user_name     
                FROM sys_machine_tool_property_definition  a                                
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0                             
		INNER JOIN sys_specific_definitions sd18 ON sd18.main_group = 18 AND sd18.first_group= a.algorithmic_id AND sd18.language_id = a.language_id AND sd18.deleted = 0 AND sd18.active = 0                                             
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                INNER JOIN info_users u ON u.id = a.op_user_id                                              
                ORDER BY a.language_id, a.property_name
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
     * @ sys_machine_tool_property_definition tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  17.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $xc = NULL;
            $insertID = NULL;
            $errorInfo = ["00000", null, null];
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
                INSERT INTO sys_machine_tool_property_definition(                        
                         property_name,
                         property_name_eng,
                         language_id,
                         op_user_id
                         )
                VALUES (
                        :property_name,
                        :property_name_eng,
                        :language_id,
                        :op_user_id
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':property_name', $params['property_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':property_name_eng', $params['property_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tool_property_definition_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    if ((isset($params['unit_grup_id']) && $params['unit_grup_id'] != "")) {
                        $xc = $this->insertPropertyUnitGroup(array('property_id' => $insertID,
                            'unit_grup_id' => $params['unit_grup_id'],
                            'opUserIdValue' => $opUserIdValue,));
                    }
                } ELSE {
                    $insertID = $kontrol ['resultSet'][0]['id'];
                }

                if ((isset($params['machine_grup_id']) && $params['machine_grup_id'] != "")) {
                    $xc = $this->insertPropertyMachineGroup(array('property_id' => $insertID,
                        'machine_grup_id' => $params['machine_grup_id'],
                        'opUserIdValue' => $opUserIdValue,
                    ));
                }

                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);

                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                /*  } else {
                  $errorInfo = '23505';
                  $errorInfoColumn = 'property_name';
                  $pdo->rollback();
                  return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
                  }
                 * *
                 */
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
     * @ sys_machine_tool_property_definition tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  23.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertPropertyUnit($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $xc = NULL;
            $insertID = NULL;
            $errorInfo = ["00000", null, null];
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
                INSERT INTO sys_machine_tool_property_definition(                        
                         property_name,
                         property_name_eng,
                         language_id,
                         op_user_id
                         )
                VALUES (
                        :property_name,
                        :property_name_eng,
                        :language_id,
                        :op_user_id
                                             )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':property_name', $params['property_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':property_name_eng', $params['property_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_tool_property_definition_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);

                    if ((isset($params['unit_grup_id']) && $params['unit_grup_id'] != "")) {
                        $xc = $this->insertPropertyUnitGroup(array('property_id' => $insertID,
                            'unit_grup_id' => "'[" . $params['unit_grup_id'] . "]'",
                            'opUserIdValue' => $opUserIdValue,));
                    } 
                    if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                        throw new \PDOException($xc['errorInfo']);

                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'property_name';
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
     * @ sys_machine_tool_property_definition tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertPropertyMachineGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            // $pdo->beginTransaction();
            $opUserIdValue = intval($params['opUserIdValue']);
            $kontrol = $this->haveRecordsMachineGroup($params);
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $sql = "  
                INSERT INTO sys_machine_groups_property_definition(                        
                         property_id,
                         machine_grup_id,
                         op_user_id
                         )
                SELECT " . intval($params['property_id']) . ",  id AS machine_grup_id,  " . intval($opUserIdValue) . "
                FROM sys_machine_tool_groups 
                WHERE       
                    id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['machine_grup_id'] . "')) 
                                                ";                
                $statement = $pdo->prepare($sql);
               //  echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_machine_groups_property_definition_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //    $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {
                $errorInfo = '23505';
                $errorInfoColumn = 'machine_grup_id';
                //    $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //   $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_unit_groups_property_definition tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insertPropertyUnitGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();
            $opUserIdValue = intval($params['opUserIdValue']);            
            $kontrol = $this->haveRecordsUnitGroup($params);
            if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                $sql = "
                INSERT INTO sys_unit_groups_property_definition(                        
                         property_id,
                         unit_grup_id,
                         op_user_id
                         )
                SELECT " . intval($params['property_id']) . ",  id AS unit_grup_id,  " . intval($opUserIdValue) . "
                FROM sys_units 
                WHERE                            
                   id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each( '" . $params['unit_grup_id'] . "')) 
                                     
                                                ";
                $statement = $pdo->prepare($sql);
               // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_unit_groups_property_definition_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //    $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
            } else {
                $errorInfo = '23505';
                $errorInfoColumn = 'unit_grup_id';
                //   $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
                            
    /**
     * @author Okan CIRAN
     * @ sys_machine_tool_property_definition tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function transferPropertyMachineGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecordsMachineGroup(array('property_id' => $params['property_id'] , 
                    'machine_grup_id' => '{"0":'.$params['machine_grup_id'].'}' ,   // {"0":5}
                    ));
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $sql = "  
                INSERT INTO sys_machine_groups_property_definition(                        
                         property_id,
                         machine_grup_id,
                         op_user_id
                         ) VALUES (
                        " . intval($params['property_id']) . ",
                        " . intval($params['machine_grup_id']) . ",                        
                        " . intval($opUserIdValue) . ")  
                                 ";
                    $statement = $pdo->prepare($sql);
                   //  echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_machine_groups_property_definition_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'machine_grup_id';
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
     * @ sys_machine_tool_property_definition tablosunda property_name daha önce kaydedilmiş mi ?  
     * @version v 1.0 13.03.2016
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
               a.property_name AS name,
               '" . $params['property_name'] . "' AS value, 
                LOWER(a.property_name) = LOWER(TRIM('" . $params['property_name'] . "')) AS control,
                CONCAT(a.property_name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message,
                a.id
            FROM sys_machine_tool_property_definition  a                          
            WHERE 
               replace(LOWER(a.property_name),' ','') = replace(LOWER(TRIM('" . $params['property_name'] . "')),' ','')            
                  " . $addSql . " 
               AND a.deleted =0    
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
     * @ sys_machine_groups_property_definition tablosunda property_id ye  machine_grup_id daha önce kaydedilmiş mi ?  
     * @version v 1.0 14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecordsMachineGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }
            $sql = "
            SELECT  
                a.machine_grup_id AS name,
                machine_grup_id AS value, 
                machine_grup_id = machine_grup_id AS control,
                CONCAT(a.machine_grup_id, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_machine_groups_property_definition a
            WHERE 
                a.property_id = " . intval($params['property_id']) . " AND
                a.machine_grup_id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each('" . $params['machine_grup_id'] . "'))
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
     * @ sys_unit_groups_property_definition tablosunda property_id ye  machine_grup_id daha önce kaydedilmiş mi ?  
     * @version v 1.0 14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecordsUnitGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $addSql = "";
            if (isset($params['id'])) {
                $addSql = " AND a.id != " . intval($params['id']) . " ";
            }
             
            $sql = "
            SELECT  
                a.unit_grup_id AS name,
                a.unit_grup_id AS value, 
                a.unit_grup_id = a.unit_grup_id AS control,
                CONCAT(a.unit_grup_id, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_unit_groups_property_definition a
            WHERE 
                a.property_id =   " . intval($params['property_id']) . " AND
                a.unit_grup_id IN (SELECT CAST(CAST(VALUE AS text) AS integer) FROM json_each( '" . $params['unit_grup_id'] . "'))
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
     * sys_machine_tool_property_definition tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  17.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $xc =NULL;
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
                UPDATE sys_machine_tool_property_definition
                SET 
                       property_name = :property_name,
                       property_name_eng = :property_name_eng, 
                       language_id = :language_id,
                       op_user_id = :op_user_id
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':property_name', $params['property_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':property_name_eng', $params['property_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    
                    if ((isset($params['unit_grup_id']) && $params['unit_grup_id'] != "")) {
                        $xc = $this->updatePropertyUnitGroup(array('property_id' =>  intval($params['id']),
                            'unit_grup_id' => $params['unit_grup_id'],
                            'opUserIdValue' => $opUserIdValue,));
                      
                        if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                            throw new \PDOException($xc['errorInfo']);
                        
                    }                            

                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'property_name';
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
     * sys_machine_groups_property_definition tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updatePropertyMachineGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            // $pdo->beginTransaction();
            $opUserIdValue = intval($params['opUserIdValue']);
            $kontrol = $this->haveRecordsUnitGroup($params);
            if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                $sql = "
                UPDATE sys_machine_tool_property_definition
                SET 
                    machine_grup_id = :machine_grup_id,
                    op_user_id = :op_user_id                                                      
                WHERE id = " . intval($params['id']);
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':machine_grup_id', $params['machine_grup_id'], \PDO::PARAM_INT);
                $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //  $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
            } else {
                // 23505 	unique_violation
                $errorInfo = '23505';
                $errorInfoColumn = 'group_name';
                //    $pdo->rollback();
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            // $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * sys_unit_groups_property_definition tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  14.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function updatePropertyUnitGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //   $pdo->beginTransaction();
            $opUserIdValue = intval($params['opUserIdValue']);            
                $sql = "
                UPDATE sys_unit_groups_property_definition
                SET 
                    unit_grup_id =  " . intval($params['unit_grup_id']).",
                    op_user_id =  " . $opUserIdValue ."  
                WHERE id = ( 
                    SELECT 
                        id 
                    FROM sys_unit_groups_property_definition 
                    WHERE 
                        property_id = " . intval($params['property_id']). " AND 
                        deleted = 0)
                ";
                $statement = $pdo->prepare($sql);  
                // echo debugPDO($sql, $params);
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //     $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);            
        } catch (\PDOException $e /* Exception $e */) {
            // $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_machine_tool_property_definition tablosundan kayıtları döndürür !!
     * @version v 1.0  17.02.2016
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
            $sort = "a.language_id, a.property_name ";
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


        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
               SELECT 
                        a.id,                                                
                        COALESCE(NULLIF(su.property_name, ''), a.property_name_eng) AS property_name,
                        a.property_name_eng,                                  
                        a.algorithmic_id,                          
			COALESCE(NULLIF(sd18x.description, ''), sd18.description_eng) AS state_algorithmic,	
                        a.deleted,                       
			COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,              
                        a.active,                      
			COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,    
                        a.language_code,                         
			COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
			COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,               
			a.language_parent_id,                     
                        a.op_user_id,
                        u.username AS op_user_name     
                FROM sys_machine_tool_property_definition  a                                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0       
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0                             
		INNER JOIN sys_specific_definitions sd18 ON sd18.main_group = 18 AND sd18.first_group= a.algorithmic_id AND sd18.language_id = a.language_id AND sd18.deleted = 0 AND sd18.active = 0                                                             
                INNER JOIN info_users u ON u.id = a.op_user_id    
                LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= a.deleted AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_specific_definitions sd18x ON sd18x.main_group = 18 AND sd18x.first_group= a.algorithmic_id AND sd18x.language_id = lx.id  AND sd18x.deleted = 0 AND sd18x.active = 0
                LEFT JOIN sys_machine_tool_property_definition su ON (su.id = a.id  OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                          
                WHERE a.deleted =0 AND a.language_parent_id =0   
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
     * @ Gridi doldurmak için sys_machine_tool_property_definition tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  17.02.2016
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
                FROM sys_machine_tool_property_definition  a                                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0                 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0                             
		INNER JOIN sys_specific_definitions sd18 ON sd18.main_group = 18 AND sd18.first_group= a.algorithmic_id AND sd18.language_id = a.language_id AND sd18.deleted = 0 AND sd18.active = 0                                                             
                INNER JOIN info_users u ON u.id = a.op_user_id                    
                WHERE  a.deleted =0 AND a.language_parent_id =0 
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
     * @ tree doldurmak için sys_machine_tool_property_definition tablosundan machine_grup_id si ve/yada unit_grup_id si
     * verilen kayıtları döndürür !!  grup değerleri boş ise tüm kayıtları döndürür.
     * @version v 1.0  17.02.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineToolGroupPropertyDefinitions($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $machineToolGrupId = 0;
            $innerSql = NULL;
            $whereSql = "  WHERE a.deleted =0 AND a.language_parent_id =0 ";

            if (isset($params['machine_grup_id']) && $params['machine_grup_id'] != "") {
                $machineToolGrupId = $params['machine_grup_id'];
                $innerSql .=" INNER JOIN sys_machine_groups_property_definition mpd ON mpd.property_id = a.id AND mpd.active =0 AND mpd.deleted =0 ";
                $whereSql .= " AND mpd.machine_grup_id = " . $machineToolGrupId;
            }
            if (isset($params['unit_grup_id']) && $params['unit_grup_id'] != "") {
                $UnitGrupId = $params['unit_grup_id'];
                $innerSql .=" INNER JOIN sys_unit_groups_property_definition upd ON upd.property_id = a.id AND upd.active =0 AND upd.deleted =0  ";
                $whereSql .= " AND upd.unit_grup_id = " . $UnitGrupId;
            }

            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }


            $statement = $pdo->prepare("                
                SELECT
                    a.id,
                    mpd.machine_grup_id ,
                    COALESCE(NULLIF(su.property_name, ''), a.property_name_eng) AS property_name,            
                    a.property_name_eng,
                    a.active,
                    'open' AS state_type,
                    false AS root_type,
                    CASE 
                        (SELECT COUNT(DISTINCT property_id) FROM sys_machine_groups_property_definition WHERE property_id = a.id) 
                    WHEN 1 THEN true
                    ELSE false END AS machinegroup,
		    CASE
                        (SELECT COUNT(DISTINCT property_id) FROM sys_unit_groups_property_definition WHERE property_id = a.id) 
		    WHEN 1 THEN true
		    ELSE false END AS unitgroup
		FROM sys_machine_tool_property_definition a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                       
                " . $innerSql . "    
                LEFT JOIN sys_machine_tool_property_definition su ON (su.id = a.id  OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                          
                " . $whereSql . "
                ORDER BY property_name      
                           
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
     * @ sys_machine_tool_property_definition tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  13.04.2016
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
                UPDATE sys_machine_tool_property_definition
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_machine_tool_property_definition
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
     * @ sys_machine_tool_property_definition tablosundan machine_grup_id si 
     * verilen kayıtları döndürür !!  machine_grup_id boş ise tüm kayıtları döndürür.
     * @version v 1.0  22.04.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineGroupPropertyDefinitions($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $machineToolGrupId = 0;
            $UnitGrupId=0;
            $innerSql = "";
            $addSelect = "";
            $whereSql = "  WHERE a.deleted =0 AND a.language_parent_id =0 ";            

            if (isset($params['machine_grup_id']) && $params['machine_grup_id'] != "") {
                $machineToolGrupId = $params['machine_grup_id'];             
                $whereSql .= " AND mpd.machine_grup_id = " . $machineToolGrupId;
            }            
            $whereSql .= " AND mpd.machine_grup_id = " . $machineToolGrupId;
            
            if (isset($params['unit_grup_id']) && $params['unit_grup_id'] != "") {
                $UnitGrupId = $params['unit_grup_id'];
                $addSelect .=" upd.unit_grup_id ,";
                $innerSql .=" INNER JOIN sys_unit_groups_property_definition upd ON upd.property_id = a.id AND upd.active =0 AND upd.deleted =0  ";
                $whereSql .= " AND upd.unit_grup_id = " . $UnitGrupId;
            }
            $languageId = NULL; 
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }

            $sql ="             
                SELECT
                    a.id,
                    mpd.machine_grup_id ,
                    ".$addSelect."
                    COALESCE(NULLIF(su.property_name, ''), a.property_name_eng) AS property_name,
                    a.property_name_eng,
                    a.active,
                    'open' AS state_type,
                    false AS root_type,                   
		    CASE
                        (SELECT COUNT(DISTINCT property_id) FROM sys_unit_groups_property_definition WHERE property_id = a.id) 
		    WHEN 1 THEN true
		    ELSE false END AS unitgroup
		FROM sys_machine_tool_property_definition a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                INNER JOIN sys_machine_groups_property_definition mpd ON mpd.property_id = a.id AND mpd.active =0 AND mpd.deleted =0 
                " . $innerSql . "    
                LEFT JOIN sys_machine_tool_property_definition su ON (su.id = a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id
                " . $whereSql . "
                ORDER BY property_name 
                           
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
     * @ sys_machine_tool_property_definition tablosundan machine_grup_id si dısında kalan property leri 
     * döndürür !!  machine_grup_id boş ise tüm kayıtları döndürür.
     * @version v 1.0  22.06.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineGroupNotInPropertyDefinitions($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $machineToolGrupId = 0;
            $UnitGrupId=0;
            $innerSql = "";
            $addSelect = "";
            $whereSql = "  WHERE a.deleted =0 AND a.language_parent_id =0 ";            
            if (isset($params['machine_grup_id']) && $params['machine_grup_id'] != "") {
                $machineToolGrupId = $params['machine_grup_id'];
            }
            if (isset($params['unit_grup_id']) && $params['unit_grup_id'] != "") {
                $UnitGrupId = $params['unit_grup_id'];
                $addSelect .=" upd.unit_grup_id ,";
                $innerSql .=" INNER JOIN sys_unit_groups_property_definition upd ON upd.property_id = a.id AND upd.active =0 AND upd.deleted =0  ";
                $whereSql .= " AND upd.unit_grup_id = " . $UnitGrupId;
            }
            $languageId = NULL; 
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }

            $sql ="             
                SELECT DISTINCT
                    a.id,
                    0 AS machine_grup_id,
                    ".$addSelect."
                    COALESCE(NULLIF(su.property_name, ''), a.property_name_eng) AS property_name,
                    a.property_name_eng,
                    a.active,
                    'open' AS state_type,
                    false AS root_type,                   
		    CASE
                        (SELECT COUNT(DISTINCT property_id) FROM sys_unit_groups_property_definition WHERE property_id = a.id) 
		    WHEN 1 THEN true
		    ELSE false END AS unitgroup
		FROM sys_machine_tool_property_definition a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0               
                " . $innerSql . "    
                LEFT JOIN sys_machine_tool_property_definition su ON (su.id = a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id
                " . $whereSql . "
                    AND a.id NOT IN (  
                        SELECT 
                            DISTINCT mpd.property_id
                        FROM sys_machine_groups_property_definition mpd                           
                        WHERE 
                            mpd.deleted =0 AND 
                            mpd.machine_grup_id IN (".$machineToolGrupId." ) )
                ORDER BY property_name                            
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
     * sys_machine_groups_property_definition tablosuna parametre olarak gelen id deki kaydın bilgilerini siler   !!
     * @version v 1.0  02.05.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function deletePropertyMachineGroup($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
             $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];               
                $sql = "
                UPDATE sys_machine_groups_property_definition
                SET 
                    active = 1, 
                    deleted = 1,                    
                    op_user_id = " . intval($opUserIdValue)."    
                WHERE property_id = " . intval($params['property_id'])." AND  
                    machine_grup_id = " . intval($params['machine_grup_id'])." 
                    AND active = 0 AND  deleted = 0  
                " ;
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
     * @   sys_machine_tool_property_definition tablosundan machine_grup property lerinin 
     * kayıtları döndürür !! 
     * @version v 1.0  20.06.2016 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillMachineGroupProperties($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $machineToolGrupId = 0;
            $innerSql = NULL;
            $whereSql = "  WHERE a.deleted =0 AND a.language_parent_id =0 "; 
          
            if (isset($params['machine_grup_id']) && $params['machine_grup_id'] != "") {
                $machineToolGrupId = $params['machine_grup_id'];
                }    
            $innerSql .= " INNER JOIN sys_machine_groups_property_definition mpd ON mpd.property_id = a.id AND mpd.active =0 AND mpd.deleted =0 ";
            $whereSql .= " AND mpd.machine_grup_id = " . $machineToolGrupId;
            
            $ekSql = " sss.machine_tool_property_definition_id = a.id ";             
            if (isset($params['machine_id']) && $params['machine_id'] != "") {
                $MachineId = $params['machine_id'];
                $innerSql .=" INNER JOIN sys_machine_tools smt ON smt.machine_tool_grup_id = mpd.machine_grup_id AND smt.active =0 AND smt.deleted =0 AND smt.language_parent_id =0 
                            LEFT JOIN sys_machine_tools smtx ON (smtx.id =smt.id OR smtx.language_parent_id = smt.id) AND smtx.deleted =0 AND smtx.active =0 AND lx.id = smtx.language_id
                            ";
                $whereSql .= " AND smt.id = " . $MachineId; 
                $ekSql = " sss.machine_tool_id = smt.id ";              
            }
     
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }

            $sql = " 
                SELECT DISTINCT
                    a.id,
                    mpd.machine_grup_id,
                    smt.id AS machine_tool_id,
                    COALESCE(NULLIF(su.property_name, ''), a.property_name_eng) AS property_name,
                    a.property_name_eng,
                    'open' AS state_type,                    
                    CASE 
                        (SELECT COUNT(DISTINCT ssz.property_id) FROM sys_machine_groups_property_definition ssz WHERE ssz.property_id = a.id) 
                    WHEN 1 THEN true
                    ELSE false END AS machinegroup, 
                    CASE
                        (SELECT COUNT(DISTINCT sss.machine_tool_property_definition_id) FROM sys_machine_tool_properties sss WHERE sss.machine_tool_property_definition_id = a.id AND sss.language_parent_id =0 AND sss.machine_tool_id=smt.id  ) 
		    WHEN 1 THEN true 
		    ELSE false END AS checked ,
                    CASE
                        (SELECT COUNT(DISTINCT sss.machine_tool_property_definition_id) FROM sys_machine_tool_properties sss 
                                WHERE sss.active =0 AND 
                                    sss.deleted= 0 AND 
                                    sss.machine_tool_property_definition_id = a.id AND 
                                    sss.language_parent_id =0 AND 
                                    sss.machine_tool_id=smt.id) 
		    WHEN 1 THEN true 
		    ELSE false END AS active ,
                    smtp.unit_id ,
                    smtp.property_value,
                    smtp.property_string_value
 		FROM sys_machine_tool_property_definition a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                " . $innerSql . " 
                LEFT JOIN sys_machine_tool_property_definition su ON (su.id = a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id 
                LEFT JOIN sys_machine_tool_properties smtp ON smtp.machine_tool_id=smt.id AND smtp.machine_tool_property_definition_id = a.id AND smtp.active =0 AND smtp.deleted =0 
                " . $whereSql . " 
                ORDER BY property_name 
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
     * @ user in adres , cominication , ad soyad , networkkey bilgilerinin sayısını döndürür !!
     * varsa network_key, name, surname, email , communication_number parametrelerinin like ile arar
     * @version v 1.0  23.06.2016
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
                $sort = "property_name";
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

            $sorguStr = null;
            if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);

                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'property_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND COALESCE(NULLIF(smtpdx.property_name, ''), a.property_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'property_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.property_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'unitcode':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sux.unitcode, ''), su.unitcode_eng)" . $sorguExpression . ' ';

                                break;
                            case 'unitcode_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND su.unitcode_eng" . $sorguExpression . ' ';

                                break;                            
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                if (isset($params['property_name']) && $params['property_name'] != "") {
                    $sorguStr .= " AND COALESCE(NULLIF(smtpdx.property_name, ''), a.property_name_eng) Like '%" . $params['property_name'] . "%'";
                }
                if (isset($params['property_name_eng']) && $params['property_name_eng'] != "") {
                    $sorguStr .= " AND a.property_name_eng Like '%" . $params['property_name_eng'] . "%'";
                }
                if (isset($params['unitcode']) && $params['unitcode'] != "") {
                    $sorguStr .= " AND COALESCE(NULLIF(sux.unitcode, ''), su.unitcode_eng) Like '%" . $params['unitcode'] . "%'";
                }
                if (isset($params['unitcode_eng']) && $params['unitcode_eng'] != "") {
                    $sorguStr .= " AND su.unitcode_eng Like '%" . $params['unitcode_eng'] . "%'";
                }                            
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "                 
		SELECT 
                    a.id,                                                
                    COALESCE(NULLIF(smtpdx.property_name, ''), a.property_name_eng) AS property_name,
                    a.property_name_eng, 
                    COALESCE(NULLIF(sux.unitcode, ''), su.unitcode_eng) AS unitcode,
                    su.unitcode_eng,                    
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active ,
                    sugpd.unit_grup_id
                FROM sys_machine_tool_property_definition  a                                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                       
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_machine_tool_property_definition smtpdx ON (smtpdx.id = a.id  OR smtpdx.language_parent_id = a.id) AND smtpdx.deleted =0 AND smtpdx.active =0 AND lx.id = smtpdx.language_id 
                INNER JOIN sys_unit_groups_property_definition sugpd ON sugpd.property_id = a.id AND sugpd.active =0 AND sugpd.deleted =0 
		INNER JOIN sys_units su ON su.id = sugpd.unit_grup_id AND su.deleted =0 AND su.active =0 AND su.language_id = a.language_id 
		INNER JOIN sys_units sux ON sux.id = sugpd.unit_grup_id AND sux.deleted =0 AND sux.active =0 AND sux.language_id = lx.id 
                WHERE a.deleted =0 AND a.language_parent_id = 0 
                ".$sorguStr."
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
     * @ user in adres , cominication , ad soyad , networkkey bilgilerinin sayısını döndürür !!
     * varsa network_key, name, surname, email , communication_number parametrelerinin like ile arar
     * @version v 1.0  23.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
     public function fillPropertieslistRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($args['language_code']) && $args['language_code'] != "")) {
                $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                }
            }

            $sorguStr = null;
            if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
                $filterRules = trim($params['filterRules']);
                $jsonFilter = json_decode($filterRules, true);

                $sorguExpression = null;
                foreach ($jsonFilter as $std) {
                    if ($std['value'] != null) {
                        switch (trim($std['field'])) {
                            case 'property_name':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                                $sorguStr.=" AND COALESCE(NULLIF(smtpdx.property_name, ''), a.property_name_eng)" . $sorguExpression . ' ';

                                break;
                            case 'property_name_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND a.property_name_eng" . $sorguExpression . ' ';

                                break;
                            case 'unitcode':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND COALESCE(NULLIF(sux.unitcode, ''), su.unitcode_eng)" . $sorguExpression . ' ';

                                break;
                            case 'unitcode_eng':
                                $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                                $sorguStr.=" AND su.unitcode_eng" . $sorguExpression . ' ';

                                break;                            
                            default:
                                break;
                        }
                    }
                }
            } else {
                $sorguStr = null;
                $filterRules = "";
                if (isset($params['property_name']) && $params['property_name'] != "") {
                    $sorguStr .= " AND COALESCE(NULLIF(smtpdx.property_name, ''), a.property_name_eng) Like '%" . $params['property_name'] . "%'";
                }
                if (isset($params['property_name_eng']) && $params['property_name_eng'] != "") {
                    $sorguStr .= " AND a.property_name_eng Like '%" . $params['property_name_eng'] . "%'";
                }
                if (isset($params['unitcode']) && $params['unitcode'] != "") {
                    $sorguStr .= " AND COALESCE(NULLIF(sux.unitcode, ''), su.unitcode_eng) Like '%" . $params['unitcode'] . "%'";
                }
                if (isset($params['unitcode_eng']) && $params['unitcode_eng'] != "") {
                    $sorguStr .= " AND su.unitcode_eng Like '%" . $params['unitcode_eng'] . "%'";
                }                            
            }
            $sorguStr = rtrim($sorguStr, "AND ");
            $sql = "
                SELECT count(id) FROM (
		SELECT 
                    a.id,                                                
                    COALESCE(NULLIF(smtpdx.property_name, ''), a.property_name_eng) AS property_name,
                    a.property_name_eng, 
                    COALESCE(NULLIF(sux.unitcode, ''), su.unitcode_eng) AS unitcode,
                    su.unitcode_eng,                    
                    a.active,
                    COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active                                  
                FROM sys_machine_tool_property_definition  a                                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0                       
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                LEFT JOIN sys_machine_tool_property_definition smtpdx ON (smtpdx.id = a.id  OR smtpdx.language_parent_id = a.id) AND smtpdx.deleted =0 AND smtpdx.active =0 AND lx.id = smtpdx.language_id 
                INNER JOIN sys_unit_groups_property_definition sugpd ON sugpd.property_id = a.id AND sugpd.active =0 AND sugpd.deleted =0 
		INNER JOIN sys_units su ON su.id = sugpd.unit_grup_id AND su.deleted =0 AND su.active =0 AND su.language_id = a.language_id 
		INNER JOIN sys_units sux ON sux.id = sugpd.unit_grup_id AND sux.deleted =0 AND sux.active =0 AND sux.language_id = lx.id 
                WHERE a.deleted =0 AND a.language_parent_id = 0
                ".$sorguStr."
                ) as xtemp 
                    ";
            $statement = $pdo->prepare($sql);
                            
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

    
}
