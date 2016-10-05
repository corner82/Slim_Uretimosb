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
 * created to be used by DAL MAnager for operation type tools operations
 * @author Okan CIRAN
 * @since 17/03/2016
 */
class InfoFirmKeys extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ info_firm_keys tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  17/03/2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                DELETE FROM info_firm_keys 
                WHERE id = :id");
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
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
     * @author Okan CIRAN
     * @ info_firm_keys tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  17/03/2016    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare("
                SELECT 
                    a.id, 
                    a.s_date, 
		    a.firm_id, 
		    fp.firm_name,
		    a.network_key, 
		    a.sf_private_key, 
		    a.sf_private_key_value,
                    a.folder_name,
                    a.machines_folder, 
                    a.logos_folder, 
                    a.products_folder, 
                    a.members_folder, 
                    a.others_folder
                FROM info_firm_keys a 
                INNER JOIN info_firm_profile fp on fp.act_parent_id = a.firm_id AND fp.active=0 AND fp.deleted =0 AND fp.language_parent_id =0  
                ORDER BY fp.firm_name              
                                 ");
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
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
     * @ info_firm_keys tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  17/03/2016
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
          //  $pdo->beginTransaction();
            
            $CountryCode = NULL;
            $CountryCodeValue = 'TR';
            if ((isset($params['country_id']) && $params['country_id'] != "")) {              
                $CountryCode = SysCountrys::getCountryCode(array('country_id' => $params['country_id']));
                if (\Utill\Dal\Helper::haveRecord($CountryCode)) {
                    $CountryCodeValue = $CountryCode ['resultSet'][0]['country_code'];                    
                }
            } 
            
            $sql = "
                INSERT INTO info_firm_keys(   
                        firm_id,
                        network_key,
                        machines_folder, 
                        logos_folder, 
                        products_folder, 
                        members_folder, 
                        others_folder,
                        folder_name
                       )
                VALUES (
                        :firm_id, 
                        CONCAT('".$CountryCodeValue."',ostim_id_generator()),
                        'Machines',
                        'Logos',
                        'Products',
                        'Members',
                        'Others',
                        'x'
                                              )  ";
            $statement = $pdo->prepare($sql);       
            $statement->bindValue(':firm_id', $params['firm_id'], \PDO::PARAM_STR);           
          //  echo debugPDO($sql, $params);
            $result = $statement->execute();           
            $insertID = $pdo->lastInsertId('info_firm_keys_id_seq');         
            $errorInfo = $statement->errorInfo();
            
            InfoFirmKeys::setFirmPrivateKey(array('id' => $insertID));
            
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
          //  $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
        } catch (\PDOException $e /* Exception $e */) {
           // $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * info_firm_keys tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  17/03/2016
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
     * @ Gridi doldurmak için info_firm_keys tablosundan kayıtları döndürür !!
     * @version v 1.0  17/03/2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGrid($params = array()) {
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
            $sort = "fp.firm_name ";
        }

        if (isset($params['order']) && $params['order'] != "") {
            $order = trim($params['order']);
            $orderArr = explode(",", $order);       
            if (count($orderArr) === 1)
                $order = trim($params['order']);
        } else {
            $order = "ASC";
        }


        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                 SELECT 
                    a.id, 
                    a.s_date, 
		    a.firm_id, 
		    fp.firm_name,
		    a.network_key, 
		    a.sf_private_key, 
		    a.sf_private_key_value,
                    a.machines_folder, 
                    a.logos_folder, 
                    a.products_folder, 
                    a.members_folder, 
                    a.others_folder,
                    a.folder_name
                FROM info_firm_keys a 
                INNER JOIN info_firm_profile fp on fp.act_parent_id = a.firm_id AND fp.active=0 AND fp.deleted =0 AND fp.language_parent_id =0                  
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
            //  echo debugPDO($sql, $parameters);
            $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
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
     * @ Gridi doldurmak için info_firm_keys tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  17/03/2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                    COUNT(a.id) AS COUNT ,    
                FROM info_firm_keys a 
                INNER JOIN info_firm_profile fp on fp.act_parent_id = a.firm_id AND fp.active=0 AND fp.deleted =0 AND fp.language_parent_id =0                  
                
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

    /**
     *       
     * parametre olarak gelen array deki 'id' li kaydın, info_firm_keys tablosundaki private key ve value değerlerini oluşturur  !!
     * @author Okan CIRAN
     * @version v 1.0  17.03.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setFirmPrivateKey($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');                    
            $sql = "
                UPDATE info_firm_keys
                SET              
                    sf_private_key = armor( pgp_sym_encrypt (CAST(firm_id AS character varying(50)) , network_key, 'compress-algo=1, cipher-algo=bf'))                     
                WHERE                   
                    id = :id";
            $statement = $pdo->prepare($sql);  
            $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);
           // echo debugPDO($sql, $params);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            
            $sql2 = "
                UPDATE info_firm_keys
                SET              
                    sf_private_key_value = substring(sf_private_key,40,length( trim( sf_private_key))-140)                     
                WHERE                     
                    id = :id";           
            
            $statementValue = $pdo->prepare($sql2);  
            $statementValue->bindValue(':id', $params['id'], \PDO::PARAM_INT);
          // echo debugPDO($sql2, $params);
            $updateValue = $statementValue->execute();
            $affectedRows = $statementValue->rowCount();
            $errorInfo = $statementValue->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);         
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {         
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    /**
     *       
     * parametre olarak gelen array deki 'id' li kaydın, info_firm_keys tablosundaki private key ve value değerlerini oluşturur  !!
     * @author Okan CIRAN
     * @version v 1.0  17.03.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function setNetworkKey($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');    
            $pdo->beginTransaction();
            $CountryCode = NULL;
            $CountryCodeValue = 'TR';
            if ((isset($params['country_id']) && $params['country_id'] != "")) {              
                $CountryCode = SysCountrys::getCountryCode(array('country_id' => $params['country_id']));
                if (\Utill\Dal\Helper::haveRecord($CountryCode)) {
                    $CountryCodeValue = $CountryCode ['resultSet'][0]['country_code'];                    
                }
            }                 
            $statement = $pdo->prepare("
                UPDATE info_firm_keys
                SET              
                  network_key = CONCAT('".$CountryCodeValue."',ostim_id_generator())
                WHERE                   
                  firm_id = :firm_id");
            $statement->bindValue(':firm_id', $params['firm_id'], \PDO::PARAM_INT);
            $update = $statement->execute();
            $affectedRows = $statement->rowCount();
            $errorInfo = $statement->errorInfo();  
            $pdo->commit();
            return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
        } catch (\PDOException $e /* Exception $e */) {     
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * 
     * @author Okan CIRAN
     * @ info_firm_keys tablosundan firma public key i döndürür   !!
     * @version v 1.0  31.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getCPK($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
                  $sql = "                           
                SELECT       
                     REPLACE(TRIM(SUBSTRING(crypt(sf_private_key_value,gen_salt('xdes')),6,20)),'/','*') AS cpk,
                     1=1 AS control
                FROM info_firm_keys a                            
                WHERE a.network_key = :network_key  
                                 ";
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':network_key', $params['network_key'], \PDO::PARAM_STR);            
          //  echo debugPDO($sql, $parameters);
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
     * parametre olarak gelen array deki 'cpk' nın, info_firm_keys tablosundaki firm_id si değerini döndürür !!
     * @author Okan CIRAN
     * @version v 1.0  31.05.2016
     * @param array $params 
     * @return array
     * @throws \PDOException
     */
    public function getFirmIdCPK($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "  
                 SELECT firm_id AS firm_id, 1=1 AS control FROM (
                            SELECT firm_id , 	
                                CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('" . $params['cpk'] . "','*','/'))) = CONCAT('_J9..',REPLACE('" . $params['cpk'] . "','*','/')) AS cpk                                
                            FROM info_firm_keys) AS logintable
                        WHERE cpk = TRUE 
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
