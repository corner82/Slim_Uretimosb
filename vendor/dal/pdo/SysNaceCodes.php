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
 * @since 29.02.2016
 */
class SysNaceCodes extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_nace_codes tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  29.02.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
       try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $userIdValue = $userId ['resultSet'][0]['user_id'];
                $statement = $pdo->prepare(" 
                UPDATE sys_nace_codes
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $userIdValue . "     
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
     * @ sys_nace_codes tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  29.02.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
             $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $statement = $pdo->prepare("
               SELECT 
                        a.id, 
	                COALESCE(NULLIF(sd.description, ''), a.description_eng) AS description,   
	                a.description_eng, 
	                a.nace_code, 
			a.kirilim, 
                        a.parent_id, 
                        a.main_group, 
                        a.first_group_code, 
                        a.second_group_code, 
                        a.third_group_code,
                        a.fourth_group_code, 
                        a.fifth_code, 
                        a.definition_year, 
                        a.language_id, 
                        a.rev_version, 
                        a.rev_description, 
                        a.rev_old_nace_code, 
                        a.rev_old_nace_code_id, 
                        a.language_code,
                        a.language_parent_id,
                        a.deleted, 
                        sd15.description as state_deleted,                 
                        a.active, 
                        sd16.description as state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.language_id ,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                        a.language_code 
                FROM sys_nace_codes a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".  intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_nace_codes sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
		LEFT JOIN sys_specific_definitions sd15 ON sd15.main_group =15 AND sd15.deleted =0 AND sd15.active =0 AND sd15.first_group= a.deleted AND lx.id = sd15.language_id   
		LEFT JOIN sys_specific_definitions sd16 ON sd16.main_group =16 AND sd16.deleted =0 AND sd16.active =0 AND sd16.first_group= a.active AND lx.id = sd16.language_id                   
                LEFT JOIN info_users u ON u.id = a.op_user_id                                                             
                ORDER BY a.nace_code 
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
     * @ sys_nace_codes tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  29.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];                
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

                    $sql = "
                INSERT INTO sys_nace_codes(
                        machine_tool_name, 
                        machine_tool_name_eng, 
                        machine_tool_grup_id, 
                        manufactuer_id, 
                        model, 
                        model_year, 
                        procurement, 
                        qqm, 
                        machine_code, 
                        language_id, 
                        op_user_id, 
                        language_code
                        )
                VALUES (
                        :machine_tool_name, 
                        :machine_tool_name_eng, 
                        :machine_tool_grup_id, 
                        :manufactuer_id, 
                        :model, 
                        :model_year, 
                        :procurement, 
                        :qqm, 
                        :machine_code, 
                        :language_id, 
                        :op_user_id, 
                        :language_code
                                             )  
                    ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_name', $params['machine_tool_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_name_eng', $params['machine_tool_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':manufactuer_id', $params['manufactuer_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':model', $params['model'], \PDO::PARAM_STR);
                    $statement->bindValue(':model_year', $params['model_year'], \PDO::PARAM_INT);
                    $statement->bindValue(':procurement', $params['procurement'], \PDO::PARAM_INT);
                    $statement->bindValue(':qqm', $params['qqm'], \PDO::PARAM_INT);
                    $statement->bindValue(':machine_code', $params['machine_code'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_STR);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_nace_codes_id_seq');
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
     * @ sys_nace_codes tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 29.02.2016
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
			a.machine_tool_name ='" . $params['machine_tool_name'] . "' AS control,
                CONCAT(a.machine_tool_name, ' daha önce kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
		FROM sys_nace_codes  a  
		INNER JOIN info_users_detail u ON u.root_id = a.op_user_id AND u.active = 0 AND u.deleted = 0                 
		WHERE a.machine_tool_name = '" . $params['machine_tool_name'] . "'
                    AND a.machine_tool_grup_id = " . intval($params['machine_tool_grup_id']) . "
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
     * sys_nace_codes tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  29.02.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $userId = $this->getUserId(array('pk' => $params['pk'], 'id' => $params['id']));
            if (\Utill\Dal\Helper::haveRecord($userId)) {
                $opUserIdValue = $userId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    } else {
                        $languageIdValue = 647;
                    }

                    $sql = "
                UPDATE sys_nace_codes
                SET   
                    machine_tool_name = :machine_tool_name, 
                    machine_tool_name_eng = :machine_tool_name_eng, 
                    machine_tool_grup_id = :machine_tool_grup_id, 
                    manufactuer_id = :manufactuer_id, 
                    model = :model, 
                    model_year = :model_year, 
                    procurement = :procurement, 
                    qqm = :qqm, 
                    machine_code = :machine_code, 
                    language_id = :language_id, 
                    op_user_id = :op_user_id, 
                    language_code = :language_code
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':machine_tool_name', $params['machine_tool_name'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_name_eng', $params['machine_tool_name_eng'], \PDO::PARAM_STR);
                    $statement->bindValue(':machine_tool_grup_id', $params['machine_tool_grup_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':manufactuer_id', $params['manufactuer_id'], \PDO::PARAM_INT);
                    $statement->bindValue(':model', $params['model'], \PDO::PARAM_STR);
                    $statement->bindValue(':model_year', $params['model_year'], \PDO::PARAM_INT);
                    $statement->bindValue(':procurement', $params['procurement'], \PDO::PARAM_INT);
                    $statement->bindValue(':qqm', $params['qqm'], \PDO::PARAM_INT);
                    $statement->bindValue(':machine_code', $params['machine_code'], \PDO::PARAM_STR);
                    $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_STR);
                    $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505'; // $kontrol ['resultSet'][0]['message'];  
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
     * @ Gridi doldurmak için sys_nace_codes tablosundan kayıtları döndürür !!
     * @version v 1.0  29.02.2016
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
            $sort = "a.nace_code ";
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

        $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
        if (\Utill\Dal\Helper::haveRecord($languageId)) {
            $languageIdValue = $languageId ['resultSet'][0]['id'];
        } else {
            $languageIdValue = 647;
        }
        
                
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                        a.id, 
	                COALESCE(NULLIF(sd.description, ''), a.description_eng) AS description,   
	                a.description_eng, 
	                a.nace_code, 
			a.kirilim, 
                        a.parent_id, 
                        a.main_group, 
                        a.first_group_code, 
                        a.second_group_code, 
                        a.third_group_code,
                        a.fourth_group_code, 
                        a.fifth_code, 
                        a.definition_year, 
                        a.language_id, 
                        a.rev_version, 
                        a.rev_description, 
                        a.rev_old_nace_code, 
                        a.rev_old_nace_code_id, 
                        a.language_code,
                        a.language_parent_id,
                        a.deleted, 
                        sd15.description as state_deleted,                 
                        a.active, 
                        sd16.description as state_active, 
                        a.op_user_id,
                        u.username AS op_user_name,
                        a.language_id ,
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                  
                        a.language_code 
                FROM sys_nace_codes a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =".  intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_nace_codes sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   
		LEFT JOIN sys_specific_definitions sd15 ON sd15.main_group =15 AND sd15.deleted =0 AND sd15.active =0 AND sd15.first_group= a.deleted AND lx.id = sd15.language_id   
		LEFT JOIN sys_specific_definitions sd16 ON sd16.main_group =16 AND sd16.deleted =0 AND sd16.active =0 AND sd16.first_group= a.active AND lx.id = sd16.language_id                   
                LEFT JOIN info_users u ON u.id = a.op_user_id                                                                        
                WHERE a.deleted =0             
                ORDER BY  " . $sort . " "
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
     * @ Gridi doldurmak için sys_nace_codes tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  29.02.2016
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
                FROM sys_nace_codes a                  
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
     * user interface fill operation   
     * @author Okan CIRAN
     * @ tree doldurmak için sys_machine_tool tablosundan tüm kayıtları döndürür !!
     * @version v 1.0  29.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillNaceCodes($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
                $addSql = "";

                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];
                } else {
                    $languageIdValue = 647;
                }

                $addSql = " WHERE a.active=0 AND a.deleted= 0 AND a.language_parent_id =0 ";
                
                if (isset($params['parent_id'])) {
                    $addSql .= " AND a.parent_id = " . intval($params['parent_id']) . " ";
                } else {
                    $addSql .= " AND a.parent_id = 0 ";
                }                

                $sql = " 
                SELECT 
                    a.id,
                    a.parent_id,
                    CONCAT(a.nace_code, ' - ', COALESCE(NULLIF(sd.description, ''), a.description_eng)) AS descriptions,   
                    CONCAT(a.nace_code, ' - ', a.description_eng) AS description_engs,		 
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_nace_codes ax WHERE ax.parent_id = a.id AND ax.deleted = 0 AND ax.active = 0 AND a.language_parent_id=0)    
                            WHEN 1 THEN 'closed'
                            ELSE 'open'   
                    END AS state_type  
                FROM sys_nace_codes a
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_nace_codes sd ON (sd.id =a.id OR sd.language_parent_id = a.id) AND sd.deleted =0 AND sd.active =0 AND lx.id = sd.language_id   		
                " . $addSql . "
                ORDER BY a.nace_code 
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

    
    
}
