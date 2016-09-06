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
 * example DAL layer class for test purposes
 * @author Mustafa Zeynel Dağlı
 */
class ReportConfiguration extends \DAL\DalSlim {
    
    /**
     * basic delete from database  example for PDO prepared
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
            $statement = $pdo->prepare("DELETE FROM t_activity WHERE id = :id");
            //Bind our value to the parameter :id.
            $statement->bindValue(':id', $id, \PDO::PARAM_INT); ;
 
            //Execute our DELETE statement.
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if($errorInfo[0]!="00000" && $errorInfo[1]!=NULL && $errorInfo[2]!=NULL ) throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found"=>true,"errorInfo"=>$errorInfo,"affectedRowsCount"=>$affectedRows);
            

        }catch(\PDOException $e /*Exception $e*/) {   
            $pdo->rollback(); 
            return array("found"=>false,"errorInfo"=>$e->getMessage());
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
     * @return type
     * @throws \PDOException
     */
    public function getAll() {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory'); 
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare(" SELECT * FROM t_activity"); 
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            /*while ($row = $statement->fetch()) {
                print_r($row);
            }*/
            $errorInfo = $statement->errorInfo();
            if($errorInfo[0]!="00000" && $errorInfo[1]!=NULL && $errorInfo[2]!=NULL ) throw new \PDOException($errorInfo[0]);
            return array("found"=>true,"errorInfo"=>$errorInfo,"resultSet"=>$result);

        }catch(\PDOException $e /*Exception $e*/) {   
            return array("found"=>false,"errorInfo"=>$e->getMessage());
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
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction(); 
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare(" INSERT INTO t_activity(
                  name,   
                  international_code, 
                  active)
                  VALUES (:name,
                          :international_code, 
                          :active)"); 
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);
            $statement->bindValue(':international_code', $params['international_code'], \PDO::PARAM_INT);
            $statement->bindValue(':active', $params['active'], \PDO::PARAM_INT);
            $result =$statement->execute();
            $insertID = $pdo->lastInsertId('t_activity_id_seq');
            $errorInfo = $statement->errorInfo();
            if($errorInfo[0]!="00000" && $errorInfo[1]!=NULL && $errorInfo[2]!=NULL ) throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found"=>true,"errorInfo"=>$errorInfo,"lastInsertId"=>$insertID);

        }catch(\PDOException $e /*Exception $e*/) {   
            $pdo->rollback();
            return array("found"=>false,"errorInfo"=>$e->getMessage());
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
     * @param type $id
     * @param type $params
     * @return array
     * @throws PDOException
     */
    public function update($id = null, $params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction(); 
            /**
             * table names and  column names will be changed for specific use
             */
            //Prepare our UPDATE SQL statement.
            $statement = $pdo->prepare("UPDATE t_activity SET name = :name"
                                            . "  WHERE id = :id");
            //Bind our value to the parameter :id.
            $statement->bindValue(':id', $id, \PDO::PARAM_INT);
            //Bind our :model parameter.
            $statement->bindValue(':name', $params['name'], \PDO::PARAM_STR);

            //Execute our UPDATE statement.
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if($errorInfo[0]!="00000" && $errorInfo[1]!=NULL && $errorInfo[2]!=NULL ) throw new \PDOException($errorInfo[0]);
            $pdo->commit();
            return array("found"=>true,"errorInfo"=>$errorInfo,"affectedRowsCount"=>$affectedRows);
            

        }catch(\PDOException $e /*Exception $e*/) {   
            $pdo->rollback(); 
            return array("found"=>false,"errorInfo"=>$e->getMessage());
        }
    }
    
    /**
     * Datagrid fill function used for testing
     * user interface datagrid fill operation
     * @param array | null $args
     * @return Array
     * @throws \PDOException
     */
    public function fillGrid($args = array()) {
        
        if(isset($args['page']) && $args['page']!="" && isset($args['rows']) && $args['rows']!="") {
            $offset = ((intval($args['page'])-1)* intval($args['rows']));
            $limit = intval($args['rows']);
        } else {
            $limit = 10;
            $offset = 0;
        }

        $sortArr = array();
        $orderArr = array();
        if(isset($args['sort']) && $args['sort']!="") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if(count($sortArr)===1)$sort = trim($args['sort']);
        } else {
            //$sort = "id";
            $sort = "r_date";
        }

        if(isset($args['order']) && $args['order']!="") {
            $order = trim($args['order']);
            $orderArr = explode(",", $order);
            //print_r($orderArr);
            if(count($orderArr)===1)$order = trim($args['order']);
        } else {
            //$order = "desc";
            $order = "ASC";
        }

        /*if(count($sortArr)===2 AND count($orderArr)===2) {
            $sort = $sortArr[0]. " ".$orderArr[0].", ";
            $order = $sortArr[1]. " ".$orderArr[1];
        } */
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
                        ORDER BY  ".$sort." "
                        . "".$order." "  
                        . "LIMIT ".$pdo->quote($limit)." "
                        . "OFFSET ".$pdo->quote($offset)." ";
            $statement = $pdo->prepare($sql);
            
            /**
             * For debug purposes PDO statement sql
             * uses 'Panique' library located in vendor directory
             */
            /*$parameters = array(
                'sort' => $sort,
                'order' => $order,
                'limit' => $pdo->quote($limit),
                'offset' => $pdo->quote($offset),
            );
            echo debugPDO($sql, $parameters);*/
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            /*while ($row = $statement->fetch()) {
                print_r($row);
            }*/
            $errorInfo = $statement->errorInfo();
            if($errorInfo[0]!="00000" && $errorInfo[1]!=NULL && $errorInfo[2]!=NULL ) throw new \PDOException($errorInfo[0]);
            return array("found"=>true,"errorInfo"=>$errorInfo,"resultSet"=>$result);
        }catch(\PDOException $e /*Exception $e*/) {  
           //$debugSQLParams = $statement->debugDumpParams();
           return array("found"=>false,"errorInfo"=>$e->getMessage()/*, 'debug' => $debugSQLParams*/);
        }
    }
    
    /**
     * user interface datagrid fill operation get row count for widget
     * @param array | null $params
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "SELECT 
                        count(rp.id) as toplam
                        FROM r_report_used_configurations rp
                        INNER JOIN t_user u ON rp.user_id=u.id
                        INNER JOIN t_cmpny c ON rp.company_id=c.id ";
            $statement = $pdo->prepare($sql);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

            $errorInfo = $statement->errorInfo();
            if($errorInfo[0]!="00000" && $errorInfo[1]!=NULL && $errorInfo[2]!=NULL ) throw new \PDOException($errorInfo[0]);
            return array("found"=>true,"errorInfo"=>$errorInfo,"resultSet"=>$result);
        }catch(\PDOException $e /*Exception $e*/) {  
           //$debugSQLParams = $statement->debugDumpParams();
           return array("found"=>false,"errorInfo"=>$e->getMessage()/*, 'debug' => $debugSQLParams*/);
        }
    }
    


    

}

