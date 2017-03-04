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
 * @since 21.06.2016
 */
class SysOsbClustersFirms extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ sys_osb_clusters_firms tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  21.06.2016
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
     try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                
                $statement = $pdo->prepare(" 
                UPDATE sys_osb_clusters_firms
                SET deleted= 1, active = 1,
                     op_user_id = " . intval($opUserIdValue) . "     
                WHERE id = ".  intval($params['id'])  );            
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
     * @ sys_osb_clusters_firms tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  21.06.2016  
     * @param array $params
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            }  
            $statement = $pdo->prepare("              
                SELECT 
                        a.id,
                        soc.osb_id,
                        COALESCE(NULLIF(sox.name, ''), so.name_eng) AS osb,
                        COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS clusters, 
			COALESCE(NULLIF(ifpx.firm_name, ''), ifp.firm_name_eng) AS firm_name, 
                        a.deleted,
			COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,
                        a.active,
			COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
			COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
			COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,			 
                        a.op_user_id,
                        u.username AS op_user_name,
                        so.country_id,
                        co.name AS tr_country_name,
			so.city_id, 
			ct.name AS tr_city_name,
			so.borough_id, 
			bo.name AS tr_borough_name 			 
                FROM sys_osb_clusters_firms a                
                INNER JOIN sys_osb_clusters soc ON soc.id = a.osb_clusters_id AND soc.active =0 AND soc.deleted =0 
                INNER JOIN sys_osb so ON so.id = soc.osb_id AND so.deleted =0 AND so.active =0 AND so.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = so.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = so.language_id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = so.language_id AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id    
                LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= a.deleted AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0                
                LEFT JOIN sys_osb sox ON (sox.id = so.id OR sox.language_parent_id = so.id) AND sox.deleted =0 AND sox.active =0 AND lx.id = sox.language_id
		INNER JOIN sys_clusters sc ON sc.id = soc.clusters_id AND sc.deleted =0 AND sc.active =0 AND l.id = sc.language_id
                LEFT JOIN sys_clusters scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active =0 AND lx.id = scx.language_id
		INNER JOIN info_firm_profile ifp ON ifp.act_parent_id = a.firm_id AND ifp.language_parent_id =0 AND ifp.cons_allow_id =2 
		LEFT JOIN info_firm_profile ifpx ON (ifpx.id = ifp.id OR ifpx.language_parent_id = ifp.id) AND ifpx.language_id = lx.id AND ifpx.cons_allow_id =2 
                LEFT JOIN sys_countrys co ON co.id = so.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = so.language_id                               
		LEFT JOIN sys_city ct ON ct.id = so.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = so.language_id                               
		LEFT JOIN sys_borough bo ON bo.boroughs_id = so.borough_id AND bo.city_id = so.city_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = so.language_id                  
                ORDER BY osb,clusters,firm_name  
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
     * @ sys_osb_clusters_firms tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  21.06.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];                 
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {

                    $sql = "
                INSERT INTO sys_osb_clusters_firms(
                        osb_clusters_id, 
                        firm_id,
                        op_user_id
                        )
                VALUES (
                        :osb_clusters_id, 
                        :firm_id,
                        :op_user_id
                        )   ";
                    $statement = $pdo->prepare($sql);
                    $statement->bindValue(':osb_clusters_id', $params['osb_clusters_id'] , \PDO::PARAM_INT);                    
                    $statement->bindValue(':firm_id', $params['firm_id'] , \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    // echo debugPDO($sql, $params);
                    $result = $statement->execute();
                    $insertID = $pdo->lastInsertId('sys_osb_clusters_firms_id_seq');
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "lastInsertId" => $insertID);
                } else {
                    $errorInfo = '23505';
                    $errorInfoColumn = 'firm_id';
                    $pdo->rollback();
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
     * @ sys_osb_clusters_firms tablosunda property_name daha önce kaydedilmiş mi ?  
     * @version v 1.0 13.03.2016
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
                a.firm_id ,
                " . $params['firm_id'] . " AS value, 
                a.firm_id = " . intval($params['firm_id']) . " AS control,
                CONCAT('Bu Firma daha önce Bu clustera kayıt edilmiş. Lütfen Kontrol Ediniz !!!' ) AS message
            FROM sys_osb_clusters_firms a                          
            WHERE  
                a.osb_clusters_id =" . intval($params['osb_clusters_id']) . " AND
                a.firm_id =" . intval($params['firm_id']) . " 
                  " . $addSql . " 
               AND a.deleted =0    
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

    /**
     * @author Okan CIRAN
     * sys_osb_clusters_firms tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  21.06.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $kontrol = $this->haveRecords($params);
                if (!\Utill\Dal\Helper::haveRecord($kontrol)) {
                    $sql = "
                    UPDATE sys_osb_clusters_firms
                    SET 
                        osb_clusters_id= :osb_clusters_id , 
                        firm_id= :firm_id ,
                        op_user_id= :op_user_id                        
                    WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);                 
                    $statement->bindValue(':osb_clusters_id', $params['osb_clusters_id'] , \PDO::PARAM_INT);
                    $statement->bindValue(':firm_id', $params['firm_id'] , \PDO::PARAM_INT);
                    $statement->bindValue(':op_user_id', $opUserIdValue, \PDO::PARAM_INT);
                    //echo debugPDO($sql, $params);
                    $update = $statement->execute();
                    $affectedRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $affectedRows);
                } else {
                    // 23505 	unique_violation
                    $errorInfo = '23505';
                    $errorInfoColumn = 'firm_id';
                    $pdo->rollback();
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
     * @ Gridi doldurmak için sys_osb_clusters_firms tablosundan kayıtları döndürür !!
     * @version v 1.0  21.06.2016
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
            $sort = " osb,clusters,firm_name";
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

        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
        if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
            $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
        }  
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                        a.id,
                        soc.osb_id,
                        COALESCE(NULLIF(sox.name, ''), so.name_eng) AS osb,
                        COALESCE(NULLIF(scx.name, ''), sc.name_eng) AS clusters, 
			COALESCE(NULLIF(ifpx.firm_name, ''), ifp.firm_name_eng) AS firm_name, 
                        a.deleted,
			COALESCE(NULLIF(sd15x.description , ''), sd15.description_eng) AS state_deleted,
                        a.active,
			COALESCE(NULLIF(sd16x.description , ''), sd16.description_eng) AS state_active,
			COALESCE(NULLIF(lx.id, NULL), 385) AS language_id,
			COALESCE(NULLIF(lx.language, ''), l.language_eng) AS language_name,			 
                        a.op_user_id,
                        u.username AS op_user_name,
                        so.country_id,
                        co.name AS tr_country_name,
			so.city_id, 
			ct.name AS tr_city_name,
			so.borough_id, 
			bo.name AS tr_borough_name 			 
                FROM sys_osb_clusters_firms a                
                INNER JOIN sys_osb_clusters soc ON soc.id = a.osb_clusters_id AND soc.active =0 AND soc.deleted =0 
                INNER JOIN sys_osb so ON so.id = soc.osb_id AND so.deleted =0 AND so.active =0 AND so.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = so.language_id AND l.deleted =0 AND l.active = 0 
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = so.language_id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = so.language_id AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id    
                LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= a.deleted AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0                
                LEFT JOIN sys_osb sox ON (sox.id = so.id OR sox.language_parent_id = so.id) AND sox.deleted =0 AND sox.active =0 AND lx.id = sox.language_id
		INNER JOIN sys_clusters sc ON sc.id = soc.clusters_id AND sc.deleted =0 AND sc.active =0 AND l.id = sc.language_id
                LEFT JOIN sys_clusters scx ON (scx.id = sc.id OR scx.language_parent_id = sc.id) AND scx.deleted =0 AND scx.active =0 AND lx.id = scx.language_id
		INNER JOIN info_firm_profile ifp ON ifp.act_parent_id = a.firm_id AND ifp.language_parent_id =0 AND ifp.cons_allow_id =2 
		LEFT JOIN info_firm_profile ifpx ON (ifpx.id = ifp.id OR ifpx.language_parent_id = ifp.id) AND ifpx.language_id = lx.id AND ifpx.cons_allow_id =2 
                LEFT JOIN sys_countrys co ON co.id = so.country_id AND co.deleted = 0 AND co.active = 0 AND co.language_id = so.language_id                               
		LEFT JOIN sys_city ct ON ct.id = so.city_id AND ct.deleted = 0 AND ct.active = 0 AND ct.language_id = so.language_id                               
		LEFT JOIN sys_borough bo ON bo.boroughs_id = so.borough_id AND bo.city_id = so.city_id AND bo.deleted = 0 AND bo.active = 0 AND bo.language_id = so.language_id                                 
                WHERE a.deleted =0 AND a.language_parent_id =0  
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
     * @ Gridi doldurmak için sys_osb_clusters_firms tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  21.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
            $sql = "
                SELECT 
                     COUNT(a.id) AS COUNT  
                FROM sys_osb_clusters_firms a                
                INNER JOIN sys_osb_clusters soc ON soc.id = a.osb_clusters_id AND soc.active =0 AND soc.deleted =0 
                INNER JOIN sys_osb so ON so.id = soc.osb_id AND so.deleted =0 AND so.active =0 AND so.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = so.language_id AND l.deleted =0 AND l.active = 0                 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = so.language_id AND sd15.deleted = 0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = so.language_id AND sd16.deleted = 0
                INNER JOIN info_users u ON u.id = a.op_user_id    
                INNER JOIN sys_clusters sc ON sc.id = soc.clusters_id AND sc.deleted =0 AND sc.active =0 AND l.id = sc.language_id                
		INNER JOIN info_firm_profile ifp ON ifp.act_parent_id = a.firm_id AND ifp.language_parent_id =0 AND ifp.cons_allow_id =2 
		WHERE a.deleted =0 AND a.language_parent_id =0  
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
     * @ sosyal medya bilgilerini dropdown ya da tree ye doldurmak için sys_osb_clusters_firms tablosundan kayıtları döndürür !!
     * @version v 1.0  21.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException 
     */
    public function fillClustersFirmLists($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');         
            $languageCode = 'tr';
            $languageIdValue = 647;
            if (isset($params['language_code']) && $params['language_code'] != "") {
                $languageCode = $params['language_code'];
            }
            $languageCodeParams = array('language_code' => $languageCode,);
            $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
            $languageIdsArray = $languageId->getLanguageId($languageCodeParams);
            if (\Utill\Dal\Helper::haveRecord($languageIdsArray)) {
                $languageIdValue = $languageIdsArray ['resultSet'][0]['id'];
            }  
            $addSql=NULL; 
            if (isset($params['clusters_id']) && $params['clusters_id'] != "") {
                $addSql =" AND soc.clusters_id =". intval($params['clusters_id']);
            }
            $addSql=NULL; 
            if (isset($params['osb_id']) && $params['osb_id'] != "") {
                $addSql =" AND so.id =". intval($params['osb_id']);
            }
            $sql = "                
                SELECT
                    a.id,
                    COALESCE(NULLIF(ifpx.firm_name, ''), ifp.firm_name_eng) AS firm_name, 
		    ifp.firm_name_eng,
                    a.active,
                    'open' AS state_type
                FROM sys_osb_clusters_firms a
                INNER JOIN sys_osb_clusters soc ON soc.id = a.osb_clusters_id AND soc.active =0 AND soc.deleted =0 
                INNER JOIN sys_osb so ON so.id = soc.osb_id AND so.deleted =0 AND so.active =0 AND so.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = so.language_id AND l.deleted =0 AND l.active = 0
		LEFT JOIN sys_language lx ON lx.id = " . intval($languageIdValue). " AND lx.deleted =0 AND lx.active =0		
		INNER JOIN info_firm_profile ifp ON ifp.act_parent_id = a.firm_id AND ifp.language_parent_id =0 AND ifp.cons_allow_id =2
		LEFT JOIN info_firm_profile ifpx ON (ifpx.id = ifp.id OR ifpx.language_parent_id = ifp.id) AND ifpx.language_id = lx.id AND ifpx.cons_allow_id =2 
                INNER JOIN sys_clusters sc ON sc.id =soc.clusters_id AND sc.deleted =0 AND sc.active =0 AND l.id = sc.language_id
                WHERE
                    a.deleted = 0 AND
                    so.language_parent_id =0
                   ".$addSql."
                ORDER BY firm_name                
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
    
    /**

     * @author Okan CIRAN
     * @ sys_osb_clusters_firms tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  13.04.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeActiveOrPassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserIdParams = array('pk' =>  $params['pk'],);
            $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
            $opUserId = $opUserIdArray->getUserId($opUserIdParams); 
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                if (isset($params['id']) && $params['id'] != "") {

                    $sql = "                 
                UPDATE sys_osb_clusters_firms
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_osb_clusters_firms
                                WHERE id = " . intval($params['id']) . "
                ),
                op_user_id = " . intval($opUserIdValue) . "
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                    //  echo debugPDO($sql, $params);
                    $update = $statement->execute();
                    $afterRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                }
                $pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
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

}
