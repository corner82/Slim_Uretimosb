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
 * @since 16.03.2016
 */
class SysUnspscCodes extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_unspsc_codes tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  16.03.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try { 
        } catch (\PDOException $e /* Exception $e */) {
           
        }
    } 

    /**
     * @author Okan CIRAN
     * @ sys_unspsc_codes tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  16.03.2016  
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
                    a.unspsc_codes,                   
                    COALESCE(NULLIF(su.unspsc_name, ''), a.unspsc_name_eng) AS unspsc_names,  
                    a.unspsc_name_eng,
                    a.version_year,   
                    a.deleted,
                    sd15.description AS state_deleted,                  
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,
                    su.language_code, 
                    su.language_id, 
                    COALESCE(NULLIF(lx.language_eng, ''), l.language) AS language_name,               
                    su.language_parent_id
                FROM sys_unspsc_codes a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_unspsc_codes su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                  
                WHERE a.language_parent_id  = 0 AND a.active =0 AND a.deleted =0             
                ORDER BY a.language_id,  a.unspsc_codes 
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
     * @ sys_unspsc_codes tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  16.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try { 
        } catch (\PDOException $e /* Exception $e */) {            
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_unspsc_codes tablosunda user_id li consultant daha önce kaydedilmiş mi ?  
     * @version v 1.0 15.01.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
        try {            
        } catch (\PDOException $e /* Exception $e */) {
            
        }
    }

    /**
     * @author Okan CIRAN
     * sys_unspsc_codes tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  16.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {             
        } catch (\PDOException $e /* Exception $e */) {           
        }
    }

    /**
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_unspsc_codes tablosundan kayıtları döndürür !!
     * @version v 1.0  16.03.2016
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
            $sort = "a.unspsc_codes ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else { 
            $order = "ASC";
        }
        $languageId = SysLanguage::getLanguageId(array('language_code' => $args['language_code']));
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
                    a.unspsc_codes,                   
                    COALESCE(NULLIF(su.unspsc_name, ''), a.unspsc_name_eng) AS unspsc_names,  
                    a.unspsc_name_eng,
                    a.version_year,   
                    a.deleted, 
                    sd15.description AS state_deleted,                 
                    a.active, 
                    sd16.description AS state_active,  
                    a.op_user_id,
                    u.username AS op_user_name,
                    su.language_code, 
                    su.language_id, 
                    COALESCE(NULLIF(lx.language_eng, ''), l.language) AS language_name,               
                    su.language_parent_id
                FROM sys_unspsc_codes a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_unspsc_codes su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                  
                WHERE a.language_parent_id  = 0 AND a.active =0 AND a.deleted =0             
              
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
     * @ Gridi doldurmak için sys_unspsc_codes tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  16.03.2016
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
            $whereSql = " WHERE a.language_parent_id  = 0 AND a.active =0 AND a.deleted =0 ";
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_unspsc_codes a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_unspsc_codes su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                 
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

 
    public function getUnspscCodes($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id =0 "; 
            
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $whereSql .= " AND a.parent_id = " . intval($params['parent_id']);                             
            } else {
                $whereSql .= " AND a.parent_id = 0 ";
            }

            $sql = "
               SELECT 
                    a.id, 
                    a.unspsc_codes,                   
                    COALESCE(NULLIF(su.unspsc_name, ''), a.unspsc_name_eng) AS unspsc_names,  
                    a.unspsc_name_eng,
                    a.grup_id, 
                    a.grup_name,
                    a.active
                FROM sys_unspsc_codes a                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_unspsc_codes su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id 
                " . $whereSql . "                
                ORDER BY a.unspsc_codes
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

 
 
    public function fillUnspscCodesTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id  = 0  " ; 
            
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $whereSql .= " AND a.parent_id  = " . intval($params['parent_id']) ;
                             
            } else {
                $whereSql .= " AND a.parent_id = 0 ";
            }

            $sql = "
             SELECT 
                    a.id, 
                    a.unspsc_codes,                   
                    COALESCE(NULLIF(su.unspsc_name, ''), a.unspsc_name_eng) AS unspsc_names,  
                    a.unspsc_name_eng,
                    a.grup_id, 
                    a.grup_name,
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_unspsc_codes ax WHERE ax.parent_id = a.id AND ax.deleted = 0)    
                            WHEN 1 THEN 'closed'
                            ELSE 'open'   
                    END AS state_type  
                FROM sys_unspsc_codes a                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_unspsc_codes su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                    
                ".$whereSql."
                ORDER BY a.unspsc_codes            
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

    
    public function fillUnspscCodesTreeRtc($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];
            } else {
                $languageIdValue = 647;
            }

            $whereSql = " WHERE a.active =0 AND a.deleted = 0 AND a.language_parent_id  = 0  " ; 
            
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $whereSql .= " AND a.parent_id  = " . intval($params['parent_id']) ;
                             
            } else {
                $whereSql .= " AND a.parent_id = 0 ";
            }

            $sql = "
               SELECT 
                    COUNT(a.id ) as COUNT 
                FROM sys_unspsc_codes a                
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id =". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_unspsc_codes su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                
                ".$whereSql."              
                       
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
