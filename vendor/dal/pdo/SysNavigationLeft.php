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
class SysNavigationLeft extends \DAL\DalSlim {

    /**  
     * @author Okan CIRAN
     * @ sys_navigation_left tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  14.12.2015
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function delete($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $Menu = $this->haveMenuRecords(array('id' => $params['id']));
            if (!\Utill\Dal\Helper::haveRecord($Menu)) {

                $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
                if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                    $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                    $sql = " 
                UPDATE sys_navigation_left
                SET  deleted= 1 , active = 1 ,
                     user_id = " . $opUserIdValue . "     
                WHERE id = " . intval($params['id']);
                    $statement = $pdo->prepare($sql);
                  //   echo debugPDO($sql, $params);
                    $update = $statement->execute();
                    $afterRows = $statement->rowCount();
                    $errorInfo = $statement->errorInfo();
                    if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                        throw new \PDOException($errorInfo[0]);
                    $this->setCollapseOpen();
                    $this->setCollapseClose();
                    $pdo->commit();
                    return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
                } else {
                    $errorInfo = '23502';  /// 23502  not_null_violation
                    $pdo->rollback();
                    return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '');
                }
            } else {
                $errorInfo = '23503';   // 23503  foreign_key_violation
                $errorInfoColumn = 'Unitcode';
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
     * @ sys_navigation_left tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  14.12.2015    
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            /**
             * table names and column names will be changed for specific use
             */
            $statement = $pdo->prepare("
              SELECT 
		    a.id, 		   
		    COALESCE(NULLIF(a.menu_name, ''), a.menu_name_eng) AS menu_name, 
		    a.menu_name_eng,  
		    a.url, 
		    a.parent, 
		    a.icon_class, 
		    a.page_state, 
		    a.collapse, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active, 
		    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,  		        
		    a.warning, 
		    a.warning_type, 
		    a.hint, 
		    a.z_index, 
		    a.language_parent_id, 
		    a.hint_eng, 
		    a.warning_class,
                    a.user_id,
                    u.username,
                    a.acl_type,
                     (select COALESCE(NULLIF(max(ax.active), 0),0)+COALESCE(NULLIF(max(bx.active), 0),0)+COALESCE(NULLIF(max(cx.active), 0),0)+
			COALESCE(NULLIF(max(dx.active), 0),0) +COALESCE(NULLIF(max(ex.active), 0),0)+ COALESCE(NULLIF(max(fx.active), 0),0)+
			COALESCE(NULLIF(max(gx.active), 0),0) 
			from sys_navigation_left ax 
			left join sys_navigation_left bx on ax.parent = bx.id
			left join sys_navigation_left cx on bx.parent = cx.id 
			left join sys_navigation_left dx on cx.parent = dx.id
			left join sys_navigation_left ex on dx.parent = ex.id
			left join sys_navigation_left fx on ex.parent = fx.id
			left join sys_navigation_left gx on fx.parent = gx.id
			where ax.id = a.id ) as active_control
		FROM sys_navigation_left a                 
		INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0		
		INNER JOIN sys_language l ON l.id = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  
                ORDER BY a.parent, a.z_index
                
                             
                                 ");
            $statement->execute();
            $result = $statement->fetcAll(\PDO::FETCH_ASSOC);
 
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
     * @author Okan CIRAN
     * @ sys_navigation_left tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  14.12.2015
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

                $Parent = 0;
                if ((isset($params['parent']) && $params['parent'] != "")) {
                    $Parent = $params ['parent'];
                }
                $Zindex = 0;
                if ((isset($params['z_index']) && $params['z_index'] != "")) {
                    $Zindex = $params ['z_index'];
                }
                $MenuTypesId = 0;
                if ((isset($params['menu_types_id']) && $params['menu_types_id'] != "")) {
                    $MenuTypesId = $params ['menu_types_id'];
                }                
                
                $languageId = NULL;
                $languageIdValue = 647;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                    $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                    if (\Utill\Dal\Helper::haveRecord($languageId)) {
                        $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                    }
                }  
                
                $sql = "
                INSERT INTO sys_navigation_left(
                    menu_name,                
                    menu_name_eng, 
                    url, 
                    parent, 
                    icon_class,  
                    z_index,                  
                    user_id,
                    menu_type,
                    menu_types_id,
                    language_id,
                    root_id,
                    root_json
                    )    
                VALUES (
                        :menu_name,                
                        :menu_name_eng, 
                        :url, 
                        :parent, 
                        :icon_class,  
                        :z_index,                  
                        :user_id,
                        :menu_type,
                        :menu_types_id,
                        :language_id,
                         (SELECT CASE
				WHEN (SELECT CASE 
					WHEN (SELECT COALESCE(NULLIF(z.root_id, NULL), 0) AS root FROM sys_navigation_left z WHERE z.id = ".intval($Parent)." limit 1) > 0 THEN 
						  (SELECT COALESCE(NULLIF(z.root_id, NULL), 0) AS root FROM sys_navigation_left z WHERE z.id = ".intval($Parent)." limit 1)		
					END  
				 ) > 0 THEN (SELECT COALESCE(NULLIF(z.root_id, NULL), 0) AS root FROM sys_navigation_left z WHERE z.id = ".intval($Parent)." limit 1) 	 
				ELSE (SELECT last_value AS root FROM sys_navigation_left_id_seq) 					  
				END) ,
                        CASE  
                            WHEN ".intval($Parent)." >0 THEN      
                              array_to_json(CONCAT('{',REPLACE(REPLACE(CAST((SELECT 
                                                                                z.root_json 
                                                                            FROM sys_navigation_left z 
                                                                            WHERE z.id = ".intval($Parent)." limit 1) 
                                                                        As text),'[',''),']',''),',',
                                                                        CAST((SELECT last_value FROM sys_navigation_left_id_seq) AS character varying(100)),'}' ) ::int[])    
                        ELSE 
                          array_to_json(CONCAT('{',CAST((SELECT last_value FROM sys_navigation_left_id_seq) AS character varying(100)),'}' ) ::int[])  
                        END  
                                             )  
                         
                                                ";
                $statement = $pdo->prepare($sql);
                $statement->bindValue(':menu_name', $params['menu_name'], \PDO::PARAM_STR);
                $statement->bindValue(':menu_name_eng', $params['menu_name_eng'], \PDO::PARAM_STR);
                $statement->bindValue(':url', $params['url'], \PDO::PARAM_STR);
                $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);
                $statement->bindValue(':parent', $Parent, \PDO::PARAM_INT);
                $statement->bindValue(':z_index', $Zindex, \PDO::PARAM_INT);
                $statement->bindValue(':user_id', $opUserIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':menu_type', $params['menu_type'], \PDO::PARAM_INT);
                $statement->bindValue(':language_id', $languageIdValue, \PDO::PARAM_INT);
                $statement->bindValue(':menu_types_id', $MenuTypesId, \PDO::PARAM_INT);
                
              // echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('sys_navigation_left_id_seq');
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);            
                $xc = $this->setCollapseOpen(array('id' => $insertID));             
                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);
                
                $xc = $this->setCollapseClose(array('id' => $insertID));                 
                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);

                
                
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
     * sys_navigation_left tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  14.12.2015
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
                if ((isset($params['role_id']) && $params['role_id'] != "")) {
                    $RoleId = $params ['role_id'];
                    $addSql =  " menu_type = ". intval($RoleId).","; 
                }
                $MenuTypesId = 0;
                if ((isset($params['menu_types_id']) && $params['menu_types_id'] != "")) {
                    $MenuTypesId = $params ['menu_types_id'];
                }
                 
                $sql = " 
                UPDATE sys_navigation_left
                SET                                  
                    language_id = ". intval($languageIdValue).", 
                    menu_types_id = ". intval($MenuTypesId).", 
                    menu_name = :menu_name, 
                    menu_name_eng = :menu_name_eng,
                    icon_class = :icon_class,                     
                    url = :url,
                    ". $addSql ."
                    user_id = ". intval($opUserIdValue)."                  
                WHERE id = :id";                
                 $statement = $pdo->prepare($sql);
                $statement->bindValue(':id', $params['id'], \PDO::PARAM_INT);                
                $statement->bindValue(':menu_name', $params['menu_name'], \PDO::PARAM_STR);                
                $statement->bindValue(':menu_name_eng', $params['menu_name_eng'], \PDO::PARAM_STR);                
                $statement->bindValue(':icon_class', $params['icon_class'], \PDO::PARAM_STR);                
                $statement->bindValue(':url', $params['url'], \PDO::PARAM_STR);  
               // echo debugPDO($sql, $params);
                $update = $statement->execute();
                $affectedRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                $xc = $this->setCollapseOpen(array('id' =>  $params['id']));             
                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);
                
                $xc = $this->setCollapseClose(array('id' =>  $params['id']));                 
                if ($xc['errorInfo'][0] != "00000" && $xc['errorInfo'][1] != NULL && $xc['errorInfo'][2] != NULL)
                    throw new \PDOException($xc['errorInfo']);

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
     * @ Gridi doldurmak için sys_navigation_left tablosundan kayıtları döndürür !!
     * @todo su  an aktif  kullanılmıyor. language code a göre değiştirilecek oki..
     * @version v 1.0  14.12.2015
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
            $sql = "
               SELECT 
		    a.id, 		   
		    COALESCE(NULLIF(a.menu_name, ''), a.menu_name_eng) AS menu_name, 
		    a.menu_name_eng,  
		    a.url, 
		    a.parent, 
		    a.icon_class, 
		    a.page_state, 
		    a.collapse, 
		    sd.description as state_deleted,                 
                    a.active, 
		    sd1.description as state_active, 
		    a.language_code, 
		    COALESCE(NULLIF(l.language_eng, ''), l.language) AS language_name,  		        
		    a.warning, 
		    a.warning_type, 
		    a.hint, 
		    a.z_index, 
		    a.language_parent_id, 
		    a.hint_eng, 
		    a.warning_class,
                    a.user_id,
                    u.username,
                    a.acl_type,
                     (select COALESCE(NULLIF(max(ax.active), 0),0)+COALESCE(NULLIF(max(bx.active), 0),0)+COALESCE(NULLIF(max(cx.active), 0),0)+
			COALESCE(NULLIF(max(dx.active), 0),0) +COALESCE(NULLIF(max(ex.active), 0),0)+ COALESCE(NULLIF(max(fx.active), 0),0)+
			COALESCE(NULLIF(max(gx.active), 0),0) 
			from sys_navigation_left ax 
			left join sys_navigation_left bx on ax.parent = bx.id
			left join sys_navigation_left cx on bx.parent = cx.id 
			left join sys_navigation_left dx on cx.parent = dx.id
			left join sys_navigation_left ex on dx.parent = ex.id
			left join sys_navigation_left fx on ex.parent = fx.id
			left join sys_navigation_left gx on fx.parent = gx.id
			where ax.id = a.id ) as active_control
		FROM sys_navigation_left a                 
		INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0		
		INNER JOIN sys_language l ON l.id = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  
                where a.language_code = :language_code 
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
            //   echo debugPDO($sql, $parameters);
            $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_INT);
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
     * @ Gridi doldurmak için sys_navigation_left tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  14.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridRowTotalCount($params = array()) {
        // su an kullanılmıyor. sql  language code gore ayarlanacak.. oki.. 
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                    SELECT 
			COUNT(a.id) AS COUNT , 
			(SELECT COUNT(a1.id) FROM sys_navigation_left a1 
			INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 15 AND sd1.first_group= a1.deleted AND sd1.language_code = a1.language_code AND sd1.deleted = 0 AND sd1.active = 0
			INNER JOIN sys_specific_definitions sd11 ON sd11.main_group = 16 AND sd11.first_group= a1.active AND sd11.language_code = a1.language_code AND sd11.deleted = 0 AND sd11.active = 0		
			INNER JOIN sys_language l1 ON l1.id = a1.language_code AND l1.deleted =0 AND l1.active = 0 
			INNER JOIN info_users u1 ON u1.id = a1.user_id  
			WHERE a1.language_code = :language_code AND a1.deleted =0) AS undeleted_count, 
			(SELECT COUNT(a2.id)
			FROM sys_navigation_left a2                 
			INNER JOIN sys_specific_definitions sd2 ON sd2.main_group = 15 AND sd2.first_group= a2.deleted AND sd2.language_code = a2.language_code AND sd2.deleted = 0 AND sd2.active = 0
			INNER JOIN sys_specific_definitions sd12 ON sd12.main_group = 16 AND sd12.first_group= a2.active AND sd12.language_code = a2.language_code AND sd12.deleted = 0 AND sd12.active = 0		
			INNER JOIN sys_language l2 ON l2.id = a2.language_code AND l2.deleted =0 AND l2.active = 0 
			INNER JOIN info_users u2 ON u2.id = a2.user_id  
			WHERE a2.language_code = :language_code AND a2.deleted =1) AS deleted_count  
		FROM sys_navigation_left a                 
		INNER JOIN sys_specific_definitions sd ON sd.main_group = 15 AND sd.first_group= a.deleted AND sd.language_code = a.language_code AND sd.deleted = 0 AND sd.active = 0
		INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 16 AND sd1.first_group= a.active AND sd1.language_code = a.language_code AND sd1.deleted = 0 AND sd1.active = 0		
		INNER JOIN sys_language l ON l.id = a.language_code AND l.deleted =0 AND l.active = 0 
		INNER JOIN info_users u ON u.id = a.user_id  		 
                WHERE a.language_code = '".$params['language_code']."'  
                    ";
            $statement = $pdo->prepare($sql);
          //  $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_INT);
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
     * @ sys_navigation_left tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  14.12.2015    
     * @return array
     * @throws \PDOException
     */
    public function pkGetLeftMenu($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $languageId = NULL;
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                $opUserRoleIdValue = $opUserId ['resultSet'][0]['role_id'];
            }
            
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                }
            }     
            
            $sql = "
                SELECT 
                    id, menu_name, language_id, menu_name_eng, url, parent, icon_class, page_state, 
                    collapse, active, deleted, state,warning,warning_type, hint, z_index, language_parent_id, 
                    hint_eng,warning_class,acl_type,language_code,active_control,menu_type,menu_types_id		
                FROM (                
                        SELECT a.id, 
                            COALESCE(NULLIF(axz.menu_name, ''), a.menu_name_eng) AS menu_name, 
                            a.language_id, 
                            a.menu_name_eng, 
                            a.url, 
                            a.parent, 
                            a.icon_class, 
                            a.page_state, 
                            a.collapse, 
                            a.active, 
                            a.deleted, 
                            CASE 
                                WHEN a.deleted = 0 THEN 'Aktif' 
                                WHEN a.deleted = 1 THEN 'Silinmiş' 
                            END AS state,    
                            a.warning, 
                            a.warning_type, 
                            COALESCE(NULLIF(axz.hint, ''), a.hint_eng) AS hint, 
                            a.z_index, 
                            a.language_parent_id, 
                            a.hint_eng, 
                            a.warning_class,
                            a.acl_type,
                            a.language_code,                        
                            0 AS active_control,
                            a.menu_type,
                            a.menu_types_id
                        FROM sys_navigation_left a 
                        INNER JOIN info_users iu ON iu.active =0 AND iu.deleted =0 and iu.id = ".intval($opUserIdValue)."
                        INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                        LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "
                        LEFT JOIN sys_navigation_left axz ON (axz.id = a.id OR axz.language_parent_id = a.id) AND axz.language_id = lx.id
                        WHERE a.language_parent_id = 0 AND
                            a.acl_type = 0 AND 
                            a.active = 0 AND 
                            a.deleted = 0 AND 
                            a.menu_types_id = 
                                            (   SELECT DISTINCT menu_types_id
                                                FROM sys_acl_menu_types_actions mt 
                                                WHERE mt.active=0 AND mt.deleted =0 AND 
                                                    mt.action_id IN (
                                                        SELECT DISTINCT c.id 
                                                        FROM sys_acl_actions c
                                                        WHERE   c.active =0 AND c.deleted =0 AND 
                                                                LOWER(c.name) = LOWER('".$params['a']."') AND 
                                                                c.module_id = 
                                                                    (
                                                                        SELECT DISTINCT m.id
                                                                        FROM sys_acl_modules m
                                                                        WHERE m.active= 0 AND m.deleted =0 AND
                                                                            LOWER(m.name) = LOWER('".$params['m']."') 
                                                                        LIMIT 1
                                                                    ) 
                                                        LIMIT 1 
                                                            )
                                                LIMIT 1 
                                            ) AND  
                            a.parent = ".intval($params['parent'])." AND
                            a.menu_type = ".intval($opUserRoleIdValue)." AND 
                            a.id IN 
                                    ( SELECT DISTINCT  mtxz.id FROM sys_navigation_left mtxz 
                                      WHERE  mtxz.id IN ( 
                                      SELECT DISTINCT dddz FROM (
                                              SELECT 
                                                      CAST( CAST (json_array_elements(abz.root_json) AS text) AS integer) AS dddz 
                                              FROM sys_navigation_left abz WHERE abz.id = a.id 				
                                              ) AS xtable 				
                                          ) AND mtxz.active =a.active AND mtxz.language_id = a.language_id AND mtxz.deleted = a.deleted  
                                      )     
                    ORDER BY a.parent, a.z_index
            ) AS xtable 
                WHERE 
                    active =0 
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
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }
    
    /**
     * 
     * @return type
     * @version bu  fonksiyon kullanılmıyor.
     */
    public function getLeftMenuFull() {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            /**
             * table names and column names will be changed for specific use
             */
            $sql = "SELECT a.id, 
                    COALESCE(NULLIF(a.menu_name, ''), a.menu_name_eng) AS menu_name, 
                    a.language_code, 
                    a.menu_name_eng, 
                    a.url, 
                    a.parent, 
                    a.icon_class, 
                    a.page_state, 
                    a.collapse, 
                    a.active, 
                    a.deleted, 
                    CASE 
                            WHEN a.deleted = 0 THEN 'Aktif' 
                            WHEN a.deleted = 1 THEN 'Silinmiş' 
                    END AS state,    
                    a.warning, 
                    a.warning_type, 
                    COALESCE(NULLIF(hint, ''), hint_eng) AS hint, 
                    a.z_index, 
                    a.language_parent_id, 
                    a.hint_eng, 
                    a.warning_class,
                    a.acl_type,
                     (select COALESCE(NULLIF(max(ax.active), 0),0)+COALESCE(NULLIF(max(bx.active), 0),0)+COALESCE(NULLIF(max(cx.active), 0),0)+
			COALESCE(NULLIF(max(dx.active), 0),0) +COALESCE(NULLIF(max(ex.active), 0),0)+ COALESCE(NULLIF(max(fx.active), 0),0)+
			COALESCE(NULLIF(max(gx.active), 0),0) 
			from sys_navigation_left ax 
			left join sys_navigation_left bx on ax.parent = bx.id
			left join sys_navigation_left cx on bx.parent = cx.id 
			left join sys_navigation_left dx on cx.parent = dx.id
			left join sys_navigation_left ex on dx.parent = ex.id
			left join sys_navigation_left fx on ex.parent = fx.id
			left join sys_navigation_left gx on fx.parent = gx.id
			where ax.id = a.id ) as active_control
              FROM sys_navigation_left a 
              WHERE a.language_code = 647
              AND acl_type = 0                
              ORDER BY a.parent, a.z_index 
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
     * @author Okan CIRAN
     * @ Gridi doldurmak için sys_navigation_left tablosundan kayıtları döndürür !!
     * @version v 1.0  28.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridForAdmin($params = array()) {
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
            $sort = "a.parent, a.z_index";
        }

        if (isset($params['order']) && $params['order'] != "") {
            $order = trim($params['order']);
            $orderArr = explode(",", $order);            
            if (count($orderArr) === 1)
                $order = trim($params['order']);
        } else {            
            $order = "ASC";
        }
        
        $RoleId = 1;                
        if ((isset($params['role_id']) && $params['role_id'] != "")) {                                 
            $RoleId = $params ['role_id']; 
        }  
        
        $languageId = NULL;
        $languageIdValue = 647;
        if ((isset($params['language_code']) && $params['language_code'] != "")) {                
            $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
            if (\Utill\Dal\Helper::haveRecord($languageId)) {
                $languageIdValue = $languageId ['resultSet'][0]['id'];                    
            }
        }  

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
              SELECT a.id, 
                    COALESCE(NULLIF(axz.menu_name, ''), a.menu_name_eng) AS menu_name, 
                    a.menu_name_eng,                     
                    a.url, 
                    a.parent, 
                    a.icon_class, 
                    a.page_state, 
                    a.collapse, 
                    a.deleted, 		                
		    COALESCE(NULLIF(COALESCE(NULLIF(sd15x.description, ''), sd15.description_eng), ''), sd15.description) AS state_deleted,		  
                    a.active, 		                          
		    COALESCE(NULLIF(COALESCE(NULLIF(sd16x.description, ''), sd16.description_eng), ''), sd16.description) AS state_active,                       
                    a.warning, 
                    a.warning_type, 
                    COALESCE(NULLIF(axz.hint, ''), a.hint_eng) AS hint, 
                    a.z_index, 
                    a.language_parent_id, 
                    a.hint_eng, 
                    a.warning_class,                                      
                    (   SELECT COALESCE(NULLIF(max(ax.active), 0),0)+COALESCE(NULLIF(max(bx.active), 0),0)+COALESCE(NULLIF(max(cx.active), 0),0)+
                            COALESCE(NULLIF(max(dx.active), 0),0) +COALESCE(NULLIF(max(ex.active), 0),0)+ COALESCE(NULLIF(max(fx.active), 0),0)+
                            COALESCE(NULLIF(max(gx.active), 0),0) 
                        FROM sys_navigation_left ax 
			LEFT JOIN sys_navigation_left bx ON ax.parent = bx.id
			LEFT JOIN sys_navigation_left cx ON bx.parent = cx.id 
			LEFT JOIN sys_navigation_left dx ON cx.parent = dx.id
			LEFT JOIN sys_navigation_left ex ON dx.parent = ex.id
			LEFT JOIN sys_navigation_left fx ON ex.parent = fx.id
			LEFT JOIN sys_navigation_left gx ON fx.parent = gx.id
			WHERE ax.id = a.id ) AS active_control,
			a.menu_type as role_id,
			sar.name as role_name 			
                FROM sys_navigation_left a  
                INNER JOIN sys_acl_roles sar on sar.id = a.menu_type 
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
                LEFT JOIN sys_navigation_left axz ON (axz.id = a.id OR axz.language_parent_id = a.id) AND axz.language_id = lx.id
                LEFT JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= a.deleted AND sd15x.language_id =lx.id  AND sd15x.deleted =0 AND sd15x.active =0 
                LEFT JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= a.active AND sd16x.language_id = lx.id  AND sd16x.deleted = 0 AND sd16x.active = 0
                WHERE a.language_parent_id = 0 AND 
                    a.active = 0 AND 
                    a.deleted = 0 AND
                    a.menu_type = ". intval($RoleId)."  
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
     * @ Gridi doldurmak için sys_navigation_left tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  14.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillGridForAdminRtc($params = array()) {       
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
             $RoleId = 1;                
            if ((isset($params['role_id']) && $params['role_id'] != "")) {                                 
                $RoleId = $params ['role_id']; 
            }  

            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                }
            }  
            $sql = "
                    SELECT 
			COUNT(a.id) AS COUNT , 
			(SELECT COUNT(ax.id) 
                        FROM sys_navigation_left ax  
                        INNER JOIN sys_acl_roles sarx ON sarx.id = ax.menu_type 
                        INNER JOIN sys_language lx ON lx.id = ax.language_id AND lx.deleted =0 AND lx.active =0                 
                        INNER JOIN sys_specific_definitions sd15x ON sd15x.main_group = 15 AND sd15x.first_group= ax.deleted AND sd15x.language_id = ax.language_id AND sd15x.deleted =0 AND sd15x.active =0 
                        INNER JOIN sys_specific_definitions sd16x ON sd16x.main_group = 16 AND sd16x.first_group= ax.active AND sd16x.language_id = ax.language_id AND sd16x.deleted = 0 AND sd16x.active = 0
			WHERE ax.language_parent_id = 0 AND ax.menu_type = ". intval($RoleId)." AND ax.deleted =0) AS undeleted_count, 
			(SELECT COUNT(ay.id)
			FROM sys_navigation_left ay  
                        INNER JOIN sys_acl_roles sary ON sary.id = ay.menu_type 
                        INNER JOIN sys_language ly ON ly.id = ay.language_id AND ly.deleted =0 AND ly.active =0                 
                        INNER JOIN sys_specific_definitions sd15y ON sd15y.main_group = 15 AND sd15y.first_group= ay.deleted AND sd15y.language_id = ay.language_id AND sd15y.deleted =0 AND sd15y.active =0 
                        INNER JOIN sys_specific_definitions sd16y ON sd16y.main_group = 16 AND sd16y.first_group= ay.active AND sd16y.language_id = ay.language_id AND sd16y.deleted = 0 AND sd16y.active = 0
			WHERE ay.language_parent_id = 0 AND ay.menu_type = ". intval($RoleId)." AND ay.deleted =1) AS deleted_count  
		FROM sys_navigation_left a  
                INNER JOIN sys_acl_roles sar on sar.id = a.menu_type 
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0                 
                INNER JOIN sys_specific_definitions sd15 ON sd15.main_group = 15 AND sd15.first_group= a.deleted AND sd15.language_id = a.language_id AND sd15.deleted =0 AND sd15.active =0 
                INNER JOIN sys_specific_definitions sd16 ON sd16.main_group = 16 AND sd16.first_group= a.active AND sd16.language_id = a.language_id AND sd16.deleted = 0 AND sd16.active = 0
                WHERE a.menu_type = ". intval($RoleId)." AND  
                    a.language_parent_id = 0 AND                                           
                    a.active = 0 AND 
                    a.deleted = 0 
                    
                
                    ";
            $statement = $pdo->prepare($sql);
          //  $statement->bindValue(':language_code', $args['language_code'], \PDO::PARAM_INT);
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
     * @ tree doldurmak için sys_navigation_left tablosundan çekilen kayıtları döndürür   !!
     * @version v 1.0  27.03.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function fillForAdminTree($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            
            $addSql ="";
            $RoleId = 1;                
            if ((isset($params['role_id']) && $params['role_id'] != "")) {                                 
                $RoleId = $params ['role_id']; 
            }  
            $ParentId = 0;
            if (isset($params['parent_id']) && $params['parent_id'] != "") {
                $ParentId = intval($params['parent_id']) ;                             
            }  
            $MenuTypesId = NULL;
            if (isset($params['menu_types_id']) && $params['menu_types_id'] != "") {
                $MenuTypesId = intval($params['menu_types_id']) ;   
                $addSql .=" AND a.menu_types_id = ". intval($MenuTypesId);
            }  
            $languageId = NULL;
            $languageIdValue = 647;
            if ((isset($params['language_code']) && $params['language_code'] != "")) {                
                $languageId = SysLanguage::getLanguageId(array('language_code' => $params['language_code']));
                if (\Utill\Dal\Helper::haveRecord($languageId)) {
                    $languageIdValue = $languageId ['resultSet'][0]['id'];                    
                }
            }  

            $sql = "
                SELECT a.id, 
                    COALESCE(NULLIF(axz.menu_name, ''), a.menu_name_eng) AS menu_name, 
                    a.menu_name_eng,                                         
                    a.active, 		                                   
                    CASE 
                        (SELECT DISTINCT 1 state_type FROM sys_navigation_left ax WHERE ax.parent = a.id AND ax.deleted = 0)    
                            WHEN 1 THEN 'closed'
                            ELSE 'open'   
                    END AS state_type,
                    a.url,
                    a.icon_class,
                    a.menu_types_id ,
                    a.menu_type AS role_id
                FROM sys_navigation_left a  
                INNER JOIN sys_acl_roles sar on sar.id = a.menu_type 
		INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
                LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "                
                LEFT JOIN sys_navigation_left axz ON (axz.id = a.id OR axz.language_parent_id = a.id) AND axz.language_id = lx.id
                WHERE a.language_parent_id = 0 AND    
                    a.deleted = 0 AND
                    a.parent =". intval($ParentId)." AND
                    a.menu_type = ". intval($RoleId)." 
                    ".$addSql."
                ORDER BY a.parent, a.z_index           
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
     * @ sys_osb tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  29.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insertLanguageTemplate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $statement = $pdo->prepare(" 
                INSERT INTO sys_osb(
                    country_id, name, name_eng, language_parent_id, 
                    language_id, user_id, priority, city_id, language_code)                  
               SELECT    
                    country_id, name, name_eng, language_id, language_parent_id, 
		    user_id, priority, city_id, language_main_code
               FROM ( 
                       SELECT c.country_id,
                            '' AS name,                            
                            COALESCE(NULLIF(c.name_eng, ''), c.name) as name_eng, 
                            l.id as language_id,  
                            (SELECT x.id FROM sys_osb x WHERE x.id =:id AND x.deleted =0 AND x.active =0 AND x.language_parent_id =0) AS language_parent_id,                            
                            c.user_id, 
			    c.priority,
			    city_id, 	 
                            l.language_main_code
                        FROM sys_osb c
                        LEFT JOIN sys_language l ON l.deleted =0 AND l.active =0 
                        WHERE c.id =" . intval($params['id']) . " 
                        ) AS xy   
                        WHERE xy.language_main_code NOT IN 
                           (SELECT distinct language_code 
                           FROM sys_osb cx 
                           WHERE (cx.language_parent_id =" . intval($params['id']) . "  OR cx.id =" . intval($params['id']) . " ) AND cx.deleted =0 AND cx.active =0)
                ");
            $result = $statement->execute();
            $insertID = $pdo->lastInsertId('sys_osb_id_seq');
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
     * @ sys_navigation_left tablosunda Collapse degerini 0 yapar. left menu deki '<' işaretinin kaldırır !!
     * @version v 1.0  29.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function setCollapseOpen($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();        
 
                 $sql  = " 
                    UPDATE sys_navigation_left 
                    SET collapse = 0 
                    WHERE id IN (
                        SELECT DISTINCT parent FROM sys_navigation_left 
                        WHERE active = 1 AND 
                        deleted = 1 AND
                        parent NOT IN (
                            SELECT DISTINCT parent FROM sys_navigation_left 
                            WHERE 
                                parent IN (SELECT DISTINCT parent FROM sys_navigation_left 
                                        WHERE 
                                            active = 1 AND 
                                            deleted = 1) AND
                                active = 0 AND 
                                deleted = 0 
                            GROUP BY parent  
                        )
                    ) AND 
                    collapse = 1 
                 ";
                $statement = $pdo->prepare($sql);
              //  echo debugPDO($sql, $params);                
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //$pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
         
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**
     * @author Okan CIRAN
     * @ sys_navigation_left tablosunda Collapse degerini 1 yapar. left menu deki '<' işaretini koyar !!
     * @version v 1.0  29.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function setCollapseClose($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();  
          
            $addSql= " abz.id =  xxc.id  ";
            if ((isset($params['id']) && $params['id'] != "")) {
                $addSql= "abz.id = ". intval($params['id']) ;
            }
          
 
            $sql  = " 
                UPDATE sys_navigation_left 
                SET collapse = 1 
                WHERE id IN (
                    SELECT DISTINCT id FROM sys_navigation_left  xxc
                        WHERE active = 0 AND 
                        deleted = 0 AND
                        collapse = 0 AND
                        id IN (
                            SELECT DISTINCT  mtxz.parent FROM sys_navigation_left mtxz 
                                      WHERE  mtxz.parent IN ( 
                                      SELECT DISTINCT dddz FROM (
                                              SELECT 
                                                      CAST( CAST (json_array_elements(abz.root_json) AS text) AS integer) AS dddz 
                                              FROM sys_navigation_left abz WHERE   ".$addSql."				
                                              ) AS xtable 				
                                          ) AND mtxz.active = xxc.active 
                                             AND mtxz.language_id = xxc.language_id AND mtxz.deleted = xxc.deleted  
                                )
                    )
                AND collapse = 0 
                " ;
                $statement = $pdo->prepare($sql);
             // echo debugPDO($sql, $params);
                $update = $statement->execute();
                $afterRows = $statement->rowCount();
                $errorInfo = $statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \PDOException($errorInfo[0]);
                //$pdo->commit();
                return array("found" => true, "errorInfo" => $errorInfo, "affectedRowsCount" => $afterRows);
          
        } catch (\PDOException $e /* Exception $e */) {
            //$pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }

    /**

     * @author Okan CIRAN
     * @ sys_navigation_left tablosundan parametre olarak  gelen id kaydın aktifliğini
     *  0(aktif) ise 1 , 1 (pasif) ise 0  yapar. !!
     * @version v 1.0  29.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function makeActiveOrPassiveSilinecek($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            if (isset($params['id']) && $params['id'] != "") {
                $sql = "                 
                UPDATE sys_navigation_left
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_navigation_left
                                WHERE id = " . intval($params['id']) . "
                )                                 
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
        } catch (\PDOException $e /* Exception $e */) {
            $pdo->rollback();
            return array("found" => false, "errorInfo" => $e->getMessage());
        }
    }
    public function makeActiveOrPassive($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $pdo->beginTransaction();
            $opUserId = InfoUsers::getUserId(array('pk' => $params['pk']));
            if (\Utill\Dal\Helper::haveRecord($opUserId)) {
                $opUserIdValue = $opUserId ['resultSet'][0]['user_id'];
                if (isset($params['id']) && $params['id'] != "") {

                    $sql = "                 
                UPDATE sys_navigation_left
                SET active = (  SELECT   
                                CASE active
                                    WHEN 0 THEN 1
                                    ELSE 0
                                END activex
                                FROM sys_navigation_left
                                WHERE id = " . intval($params['id']) . "
                ),
                user_id = " . intval($opUserIdValue) . "
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

          
   /**
     * @author Okan CIRAN
     * @ sys_navigation_left tablosunda parent id ye sahip alt elemanlar var mı   ?  
     * @version v 1.0 07.03.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function haveMenuRecords($params = array()) {
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
            $sql = "            
            SELECT  
                a.menu_name AS name ,             
                a.parent  = " . $params['id'] . " 
                AS control,
                'Bu Menu Altında Alt Menu Kaydı Bulunmakta. Lütfen Kontrol Ediniz !!!' AS message  
            FROM sys_navigation_left  a  
            INNER JOIN sys_language l ON l.id = a.language_id AND l.deleted =0 AND l.active =0 
            LEFT JOIN sys_language lx ON lx.deleted =0 AND lx.active =0 AND lx.id = " . intval($languageIdValue) . "
            LEFT JOIN sys_navigation_left ax ON (ax.id = a.id OR ax.language_parent_id = a.id) AND ax.language_id = lx.id
            WHERE a.parent = ".$params['id']. "
                AND a.language_parent_id =0                  
                AND a.deleted =0    
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
