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
class PgClass extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
                            
    } 

    /**
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
                            
    }

    /**   
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
                            
    }

    /**    
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016  
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveRecords($params = array()) {
                            
    }

    /**
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
                            
    }
                            
    /**   
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGrid($args = array()) {
                            
    }

    /**
     * @author Okan CIRAN     
     * @version v 1.0  01.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
                            
    }

    /**
     * @author Okan CIRAN
     * @ info_ ve sys_ ile baslayan tabloların adlarını, oid numaralarını ve tablo comment alanı bilgilerini döndürür!!
     * @version v 1.0  01.08.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillInfoTablesDdList($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
            $statement = $pdo->prepare("   
             SELECT  distinct 
                c.oid AS id,
                c.relname AS name,
		obj_description(c.relname::regclass, 'pg_class') AS description,                     
                true AS active,
                'open' AS state_type 
            FROM pg_catalog.pg_class c
            LEFT JOIN pg_catalog.pg_namespace n ON n.oid = c.relnamespace
            LEFT join pg_catalog.pg_description pgd on (pgd.objoid=c.oid)
            WHERE pg_catalog.pg_table_is_visible(c.oid)
                    AND c.relkind = 'r'
                    AND (c.relname like 'info_%' OR c.relname like 'sys_%')
                    AND c.relname != 'info_firm_keys' 
                    AND Length(obj_description(c.relname::regclass, 'pg_class') )> 0  
            ORDER BY c.relname
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
     * @ parametre olarak gelen oid in table name ini döndürür. !!
     * @version v 1.0  13.10.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
   public function getOperationTableName($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $operationTypeId = 0;
            if (isset($params['operation_type_id']) && $params['operation_type_id'] != "") {
                $operationTypeId = intval($params['operation_type_id']);
            }
            print_r($operationTypeId);
            $sql = "  
        	SELECT                  
                    c.relname AS table_name                
                FROM pg_catalog.pg_class c
                INNER JOIN sys_operation_types_rrp sotr ON sotr.table_oid = c.oid
                WHERE sotr.id =  " . intval($operationTypeId) . "
                LIMIT 1 
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
             
    
}
