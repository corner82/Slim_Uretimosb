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
 */
class LogAdmin extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ admin_log tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  10.03.2016
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
     * @ admin_log tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  10.03.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectLogFactory');
            $statement = $pdo->prepare("
            SELECT 
		a.id, 
		a.s_date, 
		a.pk, 
		a.op_type_id, 
		op.operation_name,
		a.url, path, a.ip, 
		a.params,
		b.oid as user_id ,
		b.username,
                a.log_datetime
            FROM admin_log  a            
            INNER JOIN admin_log b ON CRYPT(b.sf_private_key_value,CONCAT('_J9..',REPLACE(a.pk,'*','/'))) = CONCAT('_J9..',REPLACE(a.pk,'*','/')) 
                Or CRYPT(b.sf_private_key_value_temp,CONCAT('_J9..',REPLACE(a.pk,'*','/'))) = CONCAT('_J9..',REPLACE(a.pk,'*','/'))  
            INNER JOIN sys_operation_types op ON op.id = a.op_type_id  
            ORDER BY a.s_date
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
     * @ admin_log tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  10.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {        
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectLogFactory');
            $pdo->beginTransaction();            
                $sql = "
                INSERT INTO admin_log(
                        pk, 
                        op_type_id, 
                        url, 
                        path, 
                        ip, 
                        params,
                        log_datetime)
                VALUES (
                        :pk, 
                        :op_type_id, 
                        :url, 
                        :path, 
                        :ip, 
                        :params,
                        :log_datetime
                                             )   ";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':pk', $params['pk'], \PDO::PARAM_STR);
                $statement->bindValue(':op_type_id', $params['op_type_id'], \PDO::PARAM_INT);                
                $statement->bindValue(':url', $params['url'], \PDO::PARAM_STR);
                $statement->bindValue(':path', $params['path'], \PDO::PARAM_STR);
                $statement->bindValue(':ip', $params['ip'], \PDO::PARAM_STR);
                $statement->bindValue(':params', $params['params'], \PDO::PARAM_STR);
                $statement->bindValue(':log_datetime', $params['log_datetime'], \PDO::PARAM_STR);
               // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('admin_log_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);            
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
 
    /**
     * @author Okan CIRAN
     * admin_log tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  10.03.2016
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
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için admin_log tablosundan kayıtları döndürür !!
     * @version v 1.0  10.03.2016
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
            $sort = "a.s_date";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {            
            $order = "DESC";
        }
       
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectLogFactory');
            $sql = "
             SELECT 
		a.id, 
		a.s_date, 
		a.pk, 
		a.op_type_id, 
		op.operation_name,
		a.url, path, a.ip, 
		a.params,
		b.oid as user_id ,
		b.username,
                a.log_datetime
            FROM admin_log  a            
            INNER JOIN admin_log b ON CRYPT(b.sf_private_key_value,CONCAT('_J9..',REPLACE(a.pk,'*','/'))) = CONCAT('_J9..',REPLACE(a.pk,'*','/')) 
                Or CRYPT(b.sf_private_key_value_temp,CONCAT('_J9..',REPLACE(a.pk,'*','/'))) = CONCAT('_J9..',REPLACE(a.pk,'*','/'))  
            INNER JOIN sys_operation_types op ON op.id = a.op_type_id              
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
     * @ Gridi doldurmak için admin_log tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  10.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectLogFactory');
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT                        
                FROM admin_log  a            
                INNER JOIN admin_log b ON CRYPT(b.sf_private_key_value,CONCAT('_J9..',REPLACE(a.pk,'*','/'))) = CONCAT('_J9..',REPLACE(a.pk,'*','/')) 
                    Or CRYPT(b.sf_private_key_value_temp,CONCAT('_J9..',REPLACE(a.pk,'*','/'))) = CONCAT('_J9..',REPLACE(a.pk,'*','/'))  
                INNER JOIN sys_operation_types op ON op.id = a.op_type_id              
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

 
}
