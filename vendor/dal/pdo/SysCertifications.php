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
 * @since 29.03.2016
 */
class SysCertifications extends \DAL\DalSlim {

    /**  
     * @author Okan CIRAN
     * @ sys_certifications tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  14.12.2015
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
                $sql =" 
                UPDATE sys_certifications
                SET  deleted= 1 , active = 1 ,
                     op_user_id = " . $opUserIdValue . "     
                WHERE id = ". intval($params['id']) ;
                $statement = $pdo->prepare($sql) ; 
             //  echo debugPDO($sql, $params);
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
     * @ sys_certifications tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  29.03.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
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
            
            
            $statement = $pdo->prepare("               
                SELECT 
                    a.id,                                  
                    COALESCE(NULLIF(su.certificate, ''), a.certificate_eng) AS certificates,  
                    a.certificate_eng,
		    COALESCE(NULLIF(su.certificate_short, ''), a.certificate_short_eng) AS certificate_shorts,  
                    a.certificate_short_eng,
		    COALESCE(NULLIF(su.description, ''), a.description_eng) AS descriptions,  
                    a.description_eng,
                    a.deleted,
                    sd15.description AS state_deleted,                  
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,                   
                    su.language_id, 
                    COALESCE(NULLIF(lx.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id,
                    a.priority
                FROM sys_certifications a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)."  AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_certifications su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                  
                WHERE a.language_parent_id = 0 AND a.active =0 AND a.deleted =0 
                ORDER BY a.language_id ,a.priority              

 
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
     * @ sys_certifications tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  29.03.2016
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
              
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
                }  
                  $addSql = null;
                if ((isset($params['priority']) && $params['priority'] != "")) {
                    $Priority = $params ['priority'];
                    $addSql =  " priority,"; 
                    $addSqlValue =  " ". intval($Priority).","; 
                } 
                
                $statement = $pdo->prepare("
                        INSERT INTO sys_certifications (                           
                                certificate, 
                                certificate_short, 
                                description, 
                                language_id, 
                                certificate_eng, 
                                certificate_short_eng, 
                                description_eng, 
                                op_user_id, 
                                ".$addSql."
                                logo
                                
                                )                        
                        VALUES (
                                :certificate, 
                                :certificate_short, 
                                :description, 
                                :language_id, 
                                :certificate_eng, 
                                :certificate_short_eng, 
                                :description_eng, 
                                :op_user_id, 
                                ".$addSqlValue."
                                :logo
                               
                                                ");

                $statement->bindValue(':certificate', $params['certificate'], \PDO::PARAM_STR);                
                $statement->bindValue(':certificate_short', $params['certificate_short'], \PDO::PARAM_STR);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':certificate_eng', $params['certificate_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':certificate_short_eng', $params['certificate_short_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':logo', $params['logo'], \PDO::PARAM_STR);                
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('info_firm_references_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();

                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
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
     * @ sys_certifications tablosuna certificate daha önce kaydedilmiş mi ?  
     * @version v 1.0 29.03.2016
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
                a.certificate AS certificate  , 
                a.certificate  AS value , 
                a.certificate  =" . $params['certificate'] . " AS control,                
                CONCAT(a.certificate,' daha önce referans edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message                             
            FROM info_firm_references a
            WHERE a.certificate = '" . $params['certificate'] . "' AND 		
                a.firm_id = '" . $params['firm_id'] . "' AND 		
                " . $addSql . "
                AND a.active =0
                AND a.deleted=0  
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
     * sys_certifications tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  29.03.2016
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

                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];
                    }
                }
                
                $addSql = null;
                if ((isset($params['priority']) && $params['priority'] != "")) {
                    $Priority = $params ['priority'];
                    $addSql =  " priority = ". intval($Priority).","; 
                }
                 
                $sql = " 
                UPDATE sys_navigation_left
                SET                                  
                    language_id = ". intval($languageIdValue).", 
                    certificate = :certificate , 
                    certificate_short = :certificate_short, 
                    description = :description,                     
                    certificate_eng = :certificate_eng, 
                    certificate_short_eng = :certificate_short_eng, 
                    description_eng = :description_eng,  
                    logo = :logo,
                    ". $addSql ."
                    op_user_id = ". intval($opUserIdValue)."                  
                WHERE id = :id";                
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);                
                $statement->bindValue(':certificate', $params['certificate'], \PDO::PARAM_STR);                
                $statement->bindValue(':certificate_short', $params['certificate_short'], \PDO::PARAM_STR);
                $statement->bindValue(':description', $params['description'], \PDO::PARAM_STR);                
                $statement->bindValue(':certificate_eng', $params['certificate_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':certificate_short_eng', $params['certificate_short_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':description_eng', $params['description_eng'], \PDO::PARAM_STR);                
                $statement->bindValue(':logo', $params['logo'], \PDO::PARAM_STR);     
               // echo debugPDO($sql, $params);
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
     * @ Gridi doldurmak için sys_certifications tablosundan kayıtları döndürür !!
     * @version v 1.0  29.03.2016
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
            $sort = "a.language_id ,a.priority  ";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
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
                    COALESCE(NULLIF(su.certificate, ''), a.certificate_eng) AS certificates,  
                    a.certificate_eng,
		    COALESCE(NULLIF(su.certificate_short, ''), a.certificate_short_eng) AS certificate_shorts,  
                    a.certificate_short_eng,
		    COALESCE(NULLIF(su.description, ''), a.description_eng) AS descriptions,  
                    a.description_eng,
                    a.deleted,
                    sd15.description AS state_deleted,                  
                    a.active, 
                    sd16.description AS state_active, 
                    a.op_user_id,
                    u.username AS op_user_name,                   
                    su.language_id, 
                    COALESCE(NULLIF(lx.language_eng, ''), l.language) AS language_name,               
                    a.language_parent_id,
                    a.priority,
                    a.logo
                FROM sys_certifications a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)."  AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_certifications su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                  
                WHERE a.language_parent_id = 0 AND a.active =0 AND a.deleted =0 
                
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
     * @ Gridi doldurmak için sys_certifications tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  29.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
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
            $whereSql = " WHERE a.language_parent_id  = 0 AND a.active =0 AND a.deleted =0 ";
            
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT  
                FROM sys_certifications a
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_code = 'tr' AND sd15.deleted = 0 AND sd15.active = 0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_code = 'tr' AND sd16.deleted = 0 AND sd16.active = 0                             
                INNER JOIN info_users u ON u.id = a.op_user_id   
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ".  intval($languageIdValue)."  AND lx.deleted =0 AND lx.active =0                      		
                LEFT JOIN sys_certifications su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id                                  
                
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
     * @  dropdown ya da tree ye doldurmak için sys_language tablosundan kayıtları döndürür !!
     * @version v 1.0  25.07.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillCertificationsDdList($params = array()) {
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
            $statement = $pdo->prepare("
                SELECT 
                    a.id,
                    COALESCE(NULLIF(su.certificate, ''), a.certificate_eng) AS certificate_name,  
                    a.certificate_eng AS certificate_name_eng,
		    COALESCE(NULLIF(su.certificate_short, ''), a.certificate_short_eng) AS certificate_shorts,  
                    a.certificate_short_eng,
		    COALESCE(NULLIF(su.description, ''), a.description_eng) AS descriptions,  
                    a.description_eng,
                    a.active,
                    COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng) AS state_active,
		    CASE COALESCE(NULLIF(a.logo, ''),'-')
                        WHEN '-' THEN CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png'))
                        ELSE CONCAT(COALESCE(NULLIF(concat(sps.folder_road,'/'), '/'),''),sps.logos_folder,'/' ,COALESCE(NULLIF(a.logo, ''),'image_not_found.png')) END AS logo	
                FROM sys_certifications a
                INNER JOIN sys_project_settings sps ON sps.op_project_id = 1 AND sps.active =0 AND sps.deleted =0
                INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0  
		LEFT JOIN sys_language lx ON lx.id = ". intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = l.id  AND sd16.deleted = 0 AND sd16.active = 0
		LEFT JOIN sys_specific_definitions sd16x ON (sd16x.id = sd16.id OR sd16x.language_parent_id = sd16.id) AND sd16x.deleted =0 AND sd16x.active =0 AND lx.id = sd16x.language_id
                LEFT JOIN sys_certifications su ON (su.id =a.id OR su.language_parent_id = a.id) AND su.deleted =0 AND su.active =0 AND lx.id = su.language_id 
                WHERE a.language_parent_id = 0 AND a.active =0 AND a.deleted =0 
                ORDER BY a.priority, certificate_name

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
    
    
 
}
