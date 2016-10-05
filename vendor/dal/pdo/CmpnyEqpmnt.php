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
class CmpnyEqpmnt extends \DAL\DalSlim {

    /**
     * basic delete from database  example for PDO prepared
     * statements, table names are irrelevant and should be changed on specific 
     * returned result set example;
     * for success result
     * Array
      (
      [found] => 1
      [errorInfo] => Array
      (
      [0] => 00000
      [1] =>
      [2] =>
      )

      [affectedRowsCount] => 1
      )
     * for error result
     * Array
      (
      [found] => 0
      [errorInfo] => 42P01
      )
     * usage
     * @author Okan CIRAN
     * @ CmpnyEqpmnt tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  07.12.2015
     * @param type $id
     * @return array
     * @throws \PDOException
     */
    public function delete($id = null) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            /**
             * table names and  column names will be changed for specific use
             */
            //Prepare our UPDATE SQL statement. 
            // $statement = $pdo->prepare(" Update t_cmpny_eqpmnt set deleted = 1 where id = :id");   
            $statement = $pdo->prepare(" Delete from t_cmpny_eqpmnt where id = :id");
            //Bind our value to the parameter :id.
            $statement->bindValue(':id', $id, \PDO::PARAM_INT);
            //Execute our DELETE statement.
            $update = $statement->execute();
            $afterRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();

            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * basic select from database  example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific 
     * returned result set example;
     * for success result
     * Array
        (
            [found] => 1
            [errorInfo] => Array
                (
                    [0] => 00000
                    [1] => 
                    [2] => 
                )

            [resultSet] => Array
                (
                    [0] => Array
                        (
                            [id] => 1
                            [name] => zeyn dag
                            [international_code] => 12
                            [active] => 1
                        )

                    [1] => Array
                        (
                            [id] => 4
                            [name] => zeyn dag
                            [international_code] => 12
                            [active] => 1
                        )

                    [2] => Array
                        (
                            [id] => 5
                            [name] => zeyn dag new
                            [international_code] => 25
                            [active] => 1
                        )

                    [3] => Array
                        (
                            [id] => 3
                            [name] => zeyn zeyn oldu şimdik
                            [international_code] => 12
                            [active] => 1
                        )

                )

        )
     * usage 
     * @author Okan CIRAN
     * @ CmpnyEqpmnt tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  07.12.2015
     * @param type $id
     * @return array
     * @throws \PDOException
     */
    
    public function getAll() {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare("SELECT 
                                            id, 
                                            cmpny_id, 
                                            eqpmnt_id, 
                                            eqpmnt_type_id, 
                                            eqpmnt_type_attrbt_id,
                                            eqpmnt_attrbt_val, 
                                            eqpmnt_attrbt_unit  
                                        FROM t_cmpny_eqpmnt 
                                 ");          
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
            /*while ($row = $statement->fetch()) {
                print_r($row);
            }*/
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
 
     /**
     * basic insert database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific 
     * * returned result set example;
     * for success result
     * Array
       (
           [found] => 1
           [errorInfo] => Array
               (
                   [0] => 00000
                   [1] => 
                   [2] => 
               )

           [lastInsertId] => 5
       ) 
     * for error result
     * Array
        (
            [found] => 0
            [errorInfo] => 42P01
        )
     * usage     
     * @author Okan CIRAN
     * @ CmpnyEqpmnt tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  07.12.2015
     * @param type $id
     * @return array
     * @throws \PDOException
     */
    
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare("INSERT INTO t_cmpny_eqpmnt(
                                cmpny_id, 
                                eqpmnt_id, 
                                eqpmnt_type_id, 
                                eqpmnt_type_attrbt_id, 
                                eqpmnt_attrbt_val, 
                                eqpmnt_attrbt_unit)
                                VALUES ( 
                                    :cmpny_id, 
                                    :eqpmnt_id, 
                                    :eqpmnt_type_id, 
                                    :eqpmnt_type_attrbt_id, 
                                    :eqpmnt_attrbt_val, 
                                    :eqpmnt_attrbt_unit )
                                                ");

            $statement->bindValue(':cmpny_id', $params['cmpny_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_id', $params['eqpmnt_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_type_id', $params['eqpmnt_type_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_type_attrbt_id', $params['eqpmnt_type_attrbt_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_attrbt_val', $params['eqpmnt_attrbt_val'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_attrbt_unit', $params['eqpmnt_attrbt_unit'], \PDO::PARAM_INT);
         
            $result = $statement->execute();

            $insertID = $pdo->lastInsertId('t_activity_id_seq');

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
     * basic update database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific
     * returned result set example;
     * for success result
     * Array
       (
           [found] => 1
           [errorInfo] => Array
               (
                   [0] => 00000
                   [1] => 
                   [2] => 
               )

           [affectedRowsCount] => 1
       ) 
     * for error result
     * Array
        (
            [found] => 0
            [errorInfo] => 42P01
        )
     * usage  
     * @author Okan CIRAN
     * CmpnyEqpmnt tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  07.12.2015
     * @param type $id
     * @return array
     * @throws \PDOException
     */
    public function update($id = null, $params = array()) {
        try {

            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            /**
             * table names and  column names will be changed for specific use
             */
            //Prepare our UPDATE SQL statement.            
            $statement = $pdo->prepare("UPDATE t_cmpny_eqpmnt
                                SET 
                                    cmpny_id = :cmpny_id, 
                                    eqpmnt_id = :eqpmnt_id, 
                                    eqpmnt_type_id = :eqpmnt_type_id, 
                                    eqpmnt_type_attrbt_id = :eqpmnt_type_attrbt_id, 
                                    eqpmnt_attrbt_val = :eqpmnt_attrbt_val, 
                                    eqpmnt_attrbt_unit = :eqpmnt_attrbt_unit 
                                WHERE id = :id");
            //Bind our value to the parameter :id.
            $statement->bindValue(':id', $id, \PDO::PARAM_INT);
            //Bind our :model parameter.
            $statement->bindValue(':cmpny_id', $params['cmpny_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_id', $params['eqpmnt_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_type_id', $params['eqpmnt_type_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_type_attrbt_id', $params['eqpmnt_type_attrbt_id'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_attrbt_val', $params['eqpmnt_attrbt_val'], \PDO::PARAM_INT);
            $statement->bindValue(':eqpmnt_attrbt_unit', $params['eqpmnt_attrbt_unit'], \PDO::PARAM_INT);
            //Execute our UPDATE statement.
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    } 
    
    
    /**
     * Datagrid fill function used for testing
     * user interface datagrid fill operation   
     * @author Okan CIRAN
     * @ Gridi doldurmak için CmpnyEqpmnt tablosundan kayıtları döndürür !!
     * @version v 1.0  07.12.2015
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
            //$sort = "id";
            $sort = "r_date";
        }

        if (isset($args['order']) && $args['order'] != "") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if (count($orderArr) === 1)
                $order = trim($args['order']);
        } else {
            //$order = "desc";
            $order = "ASC";
        }


        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "SELECT 
                        rp.id as id, 
                        rp.project_id as project_id, 
                        rp.user_id as user_id, 
                        rp.report_jasper_id as report_jasper_id, 
                        rp.report_type_id as report_type_id, 
                        rp.r_date as r_date, 
                        rp.report_name as report_name,
                        u.user_name as user_name,
                        u.surname as surname,
                        u.name as name,
                        c.name as company_name,
                        c.id as company_id
            FROM r_report_used_configurations rp
            INNER JOIN t_user u ON rp.user_id=u.id
            INNER JOIN t_cmpny c ON rp.company_id=c.id
            ORDER BY    " . $sort . " "
                    . "" . $order . " "
                    . "LIMIT " . $pdo->quote($limit) . " "
                    . "OFFSET " . $pdo->quote($offset) . " ";
            $statement = $pdo->prepare($sql);
            /**
             * For debug purposes PDO statement sql
             * uses 'Panique' library located in vendor directory
             */
             $parameters = array(
              'sort' => $sort,
              'order' => $order,
              'limit' => $pdo->quote($limit),
              'offset' => $pdo->quote($offset),
              );
              echo debugPDO($sql, $parameters);  
              
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
     * @ Gridi doldurmak için CmpnyEqpmnt tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  07.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */  
    
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                    count(id)  as toplam  
                 FROM t_cmpny_eqpmnt
                    ";
            $statement = $pdo->prepare($sql);
      
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
 
