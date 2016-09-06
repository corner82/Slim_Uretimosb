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
 * @author Okan CİRANĞ
 */
class BlAdminActivationReport extends \DAL\DalSlim {

    /**    
     * @author Okan CIRAN
     * @ sys_activation_report tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  08.03.2016
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
     * @version v 1.0  08.03.2016  
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
     * @version v 1.0  08.03.2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {             
        } catch (\PDOException $e /* Exception $e */) {
        }
    }

    /**
     * basic update database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific
     * @author Okan CIRAN
     * sys_activation_report tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  08.03.2016
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
     * @version v 1.0  08.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getConsultantOperation($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));            
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId['resultSet'][0]['user_id'];
              //// su anda kullanılmıyor.  
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
     * @ Danışmanların firma sayılarını döndürür  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getAllConsultantFirmCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');   
            $sql = "     
                 SELECT 
                    COUNT(a.id) AS adet ,
                    a.consultant_id,
                    iu.username,
                    'Firma Sayısı' AS aciklama
                FROM info_firm_profile a 
                LEFT JOIN info_users iu on iu.id = a.consultant_id
                WHERE a.deleted =0 AND a.active =0 AND a.language_parent_id =0  
                GROUP BY a.consultant_id, iu.username 
                ORDER BY adet 
                    ";  
            $statement = $pdo->prepare($sql);
           //   echo debugPDO($sql, $params);
            $statement->execute();       
            $result = $statement->fetchAll(\PDO::FETCH_CLASS);        
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            //return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            return json_encode($result);            
        } catch (\PDOException $e /* Exception $e */) {      
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
      /**
     * 
     * @author Okan CIRAN
     * @Admin dashboard üst bilgiler  !!
     * @version v 1.0  05.02.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getUpDashBoardCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');    
            $sql = "  
                
                    SELECT ids,aciklama,adet FROM  (
				SELECT ids, aciklama, adet from (
						SELECT      
						    1 as ids, 
						    cast('Danışman Sayısı' as character varying(50))  AS aciklama,                      
						     cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                          
						FROM sys_osb_consultants a
						WHERE a.deleted =0 AND a.active =0  AND a.language_parent_id =0
				) as dasda
                    UNION 
				SELECT   ids,  aciklama, adet from (
						SELECT 
						    2 as ids, 
						    cast('Firma Sayısı' as character varying(50))  AS aciklama,   
						      cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                    
						FROM info_firm_profile a						
						WHERE a.deleted =0 AND a.active =0 AND a.language_parent_id =0
				) as dasdb
                    UNION 
				SELECT  ids,   aciklama,    adet from (
						SELECT  3 as ids,
						 cast('Makina Sayısı' as character varying(50))  AS aciklama,                      
						        cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                     
						FROM sys_machine_tools a						
						WHERE a.active = 0 AND a.deleted = 0 AND a.language_parent_id =0
				) as dasc
                    UNION 
				SELECT  ids, aciklama, adet from (
						SELECT   4 as ids,  
						cast('Bekleyen Makina Önerileri' as character varying(50))  AS aciklama,                      
						    cast(COALESCE(count(a.id),0) as character varying(5))   AS adet                         
						FROM sys_machine_tools a						
						WHERE a.active = 1 AND a.deleted = 1 AND a.language_parent_id =0
						
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
    
    public function getDashBoardHighCharts($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');             
            $sql = "                   
                SELECT COUNT(id), CAST(s_date AS date) AS tt
                FROM hstry_login
                GROUP BY tt
                ORDER BY tt DESC  
                LIMIT 31
  
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
             
        } catch (\PDOException $e /* Exception $e */) {            
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

 
   
}
