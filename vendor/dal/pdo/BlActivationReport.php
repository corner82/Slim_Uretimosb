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
 * @author Okan CİRANĞ
 */
class BlActivationReport extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_activation_report tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  04.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {           
        } catch (\PDOException $e /* Exception $e */) {           
        }
    }

    /**
     * basic select from database  example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific 
     * @author Okan CIRAN
     * @ sys_activation_report tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  04.02.2016  
     * @param array | null $args  
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                    SELECT 
                        a.id,                        
                        a.s_datetime,  
                        a.s_date,
                        a.operation_type_id,
                        op.operation_name,                         
			a.language_id, 			
                        a.language_code, 
                        COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,                                                
                        a.op_user_id,
                        u.username,
                        acl.name as role_name,
                        a.service_name,                         
                        a.table_name,
                        a.about_id
                    FROM sys_activation_report a    
                    INNER JOIN sys_operation_types op ON op.id = a.operation_type_id AND op.deleted =0 AND op.active =0
                    INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                    INNER JOIN info_users u ON u.id = a.op_user_id                      
                    INNER JOIN sys_acl_roles acl ON acl.id = u.role_id   
                    ORDER BY a.s_date desc ,op.operation_name  
                          ");            
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
           // $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * basic insert database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific 
     * @author Okan CIRAN
     * @ sys_activation_report tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  04.02.2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
           // $pdo->beginTransaction();         
            $statement = $pdo->prepare("
                INSERT INTO sys_activation_report(
                        op_user_id, 
                        operation_type_id,                         
                        language_id, 
                        language_code, 
                        service_name, 
                        table_name, 
                        about_id
                        )
                VALUES (
                        :op_user_id, 
                        :operation_type_id,                         
                        :language_id, 
                        :language_code, 
                        :service_name, 
                        :table_name, 
                        :about_id
                                                ");
            $statement->bindValue(':op_user_id', $params['op_user_id'], \PDO::PARAM_INT);
            $statement->bindValue(':operation_type_id', $params['operation_type_id'], \PDO::PARAM_INT);            
            $statement->bindValue(':language_id', $params['language_id'], \PDO::PARAM_INT);
            $statement->bindValue(':language_code', $params['language_code'], \PDO::PARAM_STR);
            $statement->bindValue(':service_name', $params['service_name'], \PDO::PARAM_STR);
            $statement->bindValue(':table_name', $params['table_name'], \PDO::PARAM_STR);
            $statement->bindValue(':about_id', $params['about_id'], \PDO::PARAM_INT);
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_activation_report_id_seq');
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //$pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * basic update database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific
     * @author Okan CIRAN
     * sys_activation_report tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  04.02.2016
     * @param array | null $args  
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
        } catch (\PDOException $e /* Exception $e */) {            
        }
    }
    
    /**
     * 
     * @author Okan CIRAN
     * @ public key e ait danışmanın gerçekleştirdiği operasyonları ve adetlerinin döndürür  !!
     * @version v 1.0  04.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantOperation($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "     
               SELECT count(a.id) AS adet , 
                    a.operation_type_id,
                    op.operation_name as aciklama
                FROM sys_activation_report a    
                INNER JOIN sys_operation_types op ON op.parent_id = 2 AND op.id = a.operation_type_id  AND op.deleted =0 AND op.active =0
                INNER JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                INNER JOIN info_users u ON u.id = a.op_user_id      
                INNER JOIN sys_acl_roles acl ON acl.id = u.role_id  
                WHERE 
                    a.op_user_id = ".intval($opUserIdValue)."
                GROUP BY a.operation_type_id, op.operation_name
                ORDER BY op.operation_name
                    ";  
            $statement = $pdo->prepare($sql);
            // echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';              
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
          //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
 
     
    /**
     * 
     * @author Okan CIRAN
     * @ Aktif firma sayısını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getAllFirmCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');  
            $sql = "     
                SELECT 
                    COUNT(id) AS adet ,
                    'Firma Sayısı' AS aciklama
                FROM info_firm_profile 
                WHERE deleted =0 AND active =0 AND language_parent_id =0               
                    ";  
            $statement = $pdo->prepare($sql);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);         
        } catch (\PDOException $e /* Exception $e */) {
          //  $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
     /**
     * 
     * @author Okan CIRAN
     * @ Aktif firma sayısını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantFirmCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "     
                SELECT 
                    COUNT(id) AS adet ,
                    'Firma Sayısı' AS aciklama
                FROM info_firm_profile 
                WHERE deleted =0 AND active =0 AND language_parent_id =0 AND                
                     consultant_id = ".intval($opUserIdValue)."
                
                    ";  
            $statement = $pdo->prepare($sql);
            //  echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';        
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {      
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
      /**
     * 
     * @author Okan CIRAN
     * @ Aktif firma sayısını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantUpDashBoardCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "  
                
                    SELECT ids,aciklama,adet FROM  (
				SELECT ids, aciklama, adet from (
						SELECT      
						    1 as ids, 
						    cast('Toplam Firma Sayısı' as character varying(50))  AS aciklama,                      
						     cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                          
						FROM info_firm_profile a
						WHERE deleted =0 AND active =0 AND language_parent_id =0 
				) as dasda
                    UNION 
				SELECT   ids,  aciklama, adet from (
						SELECT 
						    2 as ids, 
						    cast('Onaylanmış Firma Sayısı' as character varying(50))  AS aciklama,   
						      cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                    
						FROM info_firm_profile a
						INNER JOIN sys_operation_types op ON op.parent_id = 2 AND a.operation_type_id = op.id AND op.active = 0 AND op.deleted =0
						WHERE a.deleted =0 AND a.active =0 AND a.language_parent_id =0 AND
						      a.operation_type_id = 5
				) as dasdb
                    UNION 
				SELECT  ids,   aciklama,    adet from (
						SELECT  3 as ids,
						 cast('Danışmanın Firma Sayısı' as character varying(50))  AS aciklama,                      
						        cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                     
						FROM info_firm_profile a                                    
						INNER JOIN info_users u ON u.id = a.consultant_id      
						INNER JOIN sys_acl_roles acl ON acl.id = u.role_id  
						WHERE a.active = 0 AND a.deleted = 0 AND a.language_parent_id =0 AND
						    a.consultant_id = ".intval($opUserIdValue)." 
				) as dasc
                    UNION 
				SELECT  ids, aciklama, adet from (
						SELECT   4 as ids,  
						cast('Danışman Onayı Bekleyen Firma' as character varying(50))  AS aciklama,                      
						    cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                         
						FROM info_firm_profile a                                    
						INNER JOIN info_users u ON u.id = a.consultant_id      
						INNER JOIN sys_acl_roles acl ON acl.id = u.role_id  
						INNER JOIN sys_operation_types op ON op.parent_id = 1 AND a.operation_type_id = op.id AND op.active = 0 AND op.deleted =0
						WHERE a.language_parent_id =0 AND
						    a.consultant_id =".intval($opUserIdValue)." 
				 ) as dasdd
				 
		   ) AS ttemp
                ORDER BY ids 
                    ";  
            
            
            $statement = $pdo->prepare($sql);
            //  echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';        
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);            }
        } catch (\PDOException $e /* Exception $e */) {  
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

     /**
     * 
     * @author Okan CIRAN
     * @ Danışmanın onay bekleyen firmalarının bilgilerini döndürür  !!
     * @version v 1.0  05.02.2016
      * @version 21.07.2016 tarihinde yenisi  yazıldı 
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    
    public function getConsWaitingForConfirm_eskisi($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
                
            $sql = "                   
               SELECT 
                    aciklama , 
                    CASE
                        WHEN sure_int > 50000 THEN CAST(SUBSTRING(sure FROM 1 FOR POSITION(' ' IN sure )-1 ) AS integer)
                    ELSE 0 
                    END AS sure
                FROM (
                    SELECT a.id,
                        EXTRACT(EPOCH FROM age(a.s_date)) AS sure_int,  
			a.firm_name AS aciklama, 
                       CAST(CURRENT_TIMESTAMP - a.s_date AS VARCHAR(20)) AS sure
                    FROM  info_firm_profile a                 
                    INNER JOIN info_users u ON u.id = a.consultant_id   
                    INNER JOIN sys_operation_types op ON op.parent_id = 1 AND a.operation_type_id = op.id AND op.active = 0 AND op.deleted =0
                    WHERE a.language_parent_id =0 AND
                        a.consultant_id = ".intval($opUserIdValue)."                     
                    ) AS asdasd
                ORDER BY sure DESC
                LIMIT 6
  
                    ";  
            $statement = $pdo->prepare($sql);
          // echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';          
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
     /**
     * 
     * @author Okan CIRAN
     * @ Danışmanın onay bekleyen firmalarının bilgilerini döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    
    public function getConsWaitingForConfirm($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];

                $languageCode = 'tr';
                $languageIdValue = 647;
                if (isset($params['language_code']) && $params['language_code'] != "") {
                    $languageCode = $params['language_code'];
                }       
                $languageCodeParams = array('language_code' => $languageCode,);
                $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
                $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
                if (!\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                     $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
                } 

                $sql = "                   
                SELECT 
                    id,
                    operation_type_id,
                    operation_name AS aciklama,
                    operation_name,
                    operation_name_eng,	
                    category_id,
                    category,
                    category_eng,	 
                    table_name, 
                    table_column_id,   
                    membership_types_id,
                    membership_types_name,
                    membership_types_name_eng,
                    sys_membership_periods_id,   			
                    period_name,			
                    period_name_eng,
                    preferred_language_id,
                    preferred_language,
                    language_id,
                    language_name,
                    op_user_id,
                    op_user_name,
                    cons_id,
                    cons_name,						 
                    op_cons_id,
                    op_cons_name,
                    cons_operation_type_id,
                    cons_operation_name,
                    cons_operation_name_eng,
		    CASE
                        WHEN sure_int > 50000 THEN CAST(SUBSTRING(sure FROM 1 FOR POSITION(' ' IN sure )-1 ) AS integer)
                    ELSE 0 
                    END AS sure,
                    s_date,
                    c_date,
                    priority
                FROM ( 
                    SELECT 
                        a.id,
                        a.operation_type_id,
                        COALESCE(NULLIF(sotx.operation_name, ''), sot.operation_name_eng) AS operation_name,
                        sot.operation_name_eng,
                        sot.category_id,
                        COALESCE(NULLIF(soccx.category, ''), socc.category_eng) AS category,
                        socc.category_eng,
                        sot.table_name,
                        a.table_column_id,
                        smt.id AS membership_types_id,
                        COALESCE(NULLIF(smtx.mem_type, ''), smt.mem_type_eng) AS membership_types_name,
                        smt.mem_type_eng AS membership_types_name_eng,
                        a.sys_membership_periods_id,
                        COALESCE(NULLIF(spx.period_name, ''), sp.period_name_eng) AS period_name,
                        sp.period_name_eng,
                        a.preferred_language_id,
                        COALESCE(NULLIF(lpx.language, ''), lp.language_eng) AS preferred_language,
                        COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
                        COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,
                        a.op_user_id,
                        opuc.username AS op_user_name,
                        a.cons_id,
                        uc.username AS cons_name,
                        a.op_cons_id,
                        u.username AS op_cons_name,
                        a.cons_operation_type_id,
                        COALESCE(NULLIF(sotconsx.operation_name, ''), sotcons.operation_name_eng) AS cons_operation_name,
                        sotcons.operation_name_eng AS cons_operation_name_eng,
                        EXTRACT(EPOCH FROM age(a.s_date)) AS sure_int, 
                        CAST(CURRENT_TIMESTAMP - a.s_date AS VARCHAR(20)) AS sure,
                        a.s_date,
                        a.c_date,
                        smt.priority
                    FROM act_process_confirm a
                    INNER JOIN sys_operation_types sot ON sot.base_id = a.operation_type_id AND sot.active =0 AND sot.deleted = 0 AND sot.language_parent_id =0 
                    INNER JOIN sys_language l ON l.id = sot.language_id AND l.deleted =0 AND l.active = 0
                    LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue) . " AND lx.deleted =0 AND lx.active =0
                    INNER JOIN sys_language lp ON lp.id = a.preferred_language_id AND lp.deleted =0 AND lp.active = 0
                    LEFT JOIN sys_language lpx ON (lpx.id = lp.id OR lpx.language_parent_id = lp.id) AND lpx.deleted =0 AND lpx.active =0
                    INNER JOIN sys_osb_consultant_categories socc ON socc.id= sot.category_id AND socc.active =0 AND socc.deleted = 0 AND socc.language_parent_id =0 AND l.id = socc.language_id 
                    INNER JOIN info_users uc ON uc.id = a.cons_id 
                    INNER JOIN info_users opuc ON opuc.id = a.op_user_id 
                    LEFT JOIN info_users u ON u.id = a.op_cons_id 
                    LEFT JOIN sys_membership_periods smp ON smp.id = a.sys_membership_periods_id
                    LEFT JOIN sys_membership_types smt ON smt.id = smp.mems_type_id AND smt.language_parent_id =0 AND l.id = smt.language_id
                    LEFT JOIN sys_membership_types smtx ON (smtx.id = smt.id OR smtx.language_parent_id = smt.id) AND lx.id = smtx.language_id
                    LEFT JOIN sys_operation_types sotx ON (sotx.id = sot.id OR sotx.language_parent_id = sot.id) AND sotx.deleted =0 AND sotx.active =0 AND lx.id = sotx.language_id
                    LEFT JOIN sys_osb_consultant_categories soccx ON (soccx.id = socc.id OR soccx.language_parent_id = socc.id) AND soccx.deleted =0 AND soccx.active =0 AND lx.id = soccx.language_id
                    LEFT JOIN sys_operation_types sotcons ON sotcons.base_id = a.cons_operation_type_id AND sotcons.active =0 AND sotcons.deleted = 0 AND sotcons.language_parent_id =0
                    LEFT JOIN sys_operation_types sotconsx ON (sotconsx.id = sotcons.id OR sotconsx.language_parent_id = sotcons.id) AND sotconsx.deleted =0 AND sotconsx.active =0 AND lx.id = sotconsx.language_id
                    LEFT JOIN sys_periods sp ON sp.id = smp.period_id AND sp.language_parent_id =0 AND l.id = sp.language_id
                    LEFT JOIN sys_periods spx ON (spx.id = sp.id OR spx.language_parent_id = sp.id) AND spx.deleted =0 AND spx.active =0 AND lx.id = spx.language_id
                    WHERE a.cons_id = " . intval($opUserIdValue) . "
                        AND  a.c_date IS NULL
                ) AS xtable
                ORDER BY priority, s_date, membership_types_name , sure desc 
                
                LIMIT 6
  
                    ";
                $statement = $pdo->prepare($sql);
                // echo debugPDO($sql, $params);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_CLASS);
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
                return json_encode($result);
            } else {
                $errorInfo = '23502';   // 23502  not_null_violation
                $errorInfoColumn = 'pk';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

}
