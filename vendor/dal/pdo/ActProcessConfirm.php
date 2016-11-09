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
 * @since 28.06.2016
 */
class ActProcessConfirm extends \DAL\DalSlim {

    /**
     * @author Okan CIRAN
     * @ act_process_confirm tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  28.06.2016
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
                UPDATE act_process_confirm
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
     * @ act_process_confirm tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  28.06.2016  
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
            $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
            if (!\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
                 $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
            }  
            $statement = $pdo->prepare("              
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
                    a.s_date,
                    a.c_date
                FROM act_process_confirm a
                INNER JOIN sys_operation_types sot ON sot.base_id = a.operation_type_id AND sot.active =0 AND sot.deleted = 0 AND sot.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = sot.language_id AND l.deleted =0 AND l.active = 0
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
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
             
                ORDER BY smt.priority,  a.s_date,  membership_types_name
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
     * @ act_process_confirm tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  28.06.2016
     * @param type $params
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            //$pdo->beginTransaction();           
                $sql = "
                INSERT INTO act_process_confirm(                        
                        operation_type_id, 
                        table_column_id,                         
                        sys_membership_periods_id, 
                        op_user_id,
                        cons_id, 
                        preferred_language_id,
                        url
                        )
                VALUES (
                        " . intval($params['operation_type_id']) . ",
                        " . intval($params['table_column_id']) . ",                       
                        (   SELECT CASE
				WHEN (SELECT CASE 
					WHEN (SELECT COALESCE(NULLIF(z1.membership_periods_id, NULL), 0) AS membership_periods_id FROM info_users_membership_types z1 WHERE z1.active =0 AND z1.deleted=0 AND z1.user_id = " . intval($params['op_user_id']) . " limit 1) > 0 THEN 
						  (SELECT COALESCE(NULLIF(z2.membership_periods_id, NULL), 0) AS membership_periods_id FROM info_users_membership_types z2 WHERE z2.active =0 AND z2.deleted=0 AND z2.user_id = " . intval($params['op_user_id']) . " limit 1)		
					END  
				 ) > 0 THEN (SELECT COALESCE(NULLIF(z3.membership_periods_id, NULL), 0) AS membership_periods_id FROM info_users_membership_types z3 WHERE z3.active =0 AND z3.deleted=0 AND z3.user_id = " . intval($params['op_user_id']) . " limit 1) 	 
				ELSE 0
				END ),
                        " . intval($params['op_user_id']) . ",
                        " . intval($params['cons_id']) . ",
                        " . intval($params['preferred_language_id']) . ",
                        '" . $params['url'] ."' 
                                             )";
                $statement = $pdo->prepare($sql);
               //echo debugPDO($sql, $params);
                $result = $statement->execute();
                $insertID = $pdo->lastInsertId('act_process_confirm_id_seq');
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
     * @author Okan CIRAN
     * act_process_confirm tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  28.06.2016
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
            $opConsId = $opUserIdArray->getUserId($opUserIdParams);             
            if (\Utill\Dal\Helper::haveRecord($opConsId)) {
                $opConsIdValue = $opConsId ['resultSet'][0]['user_id'];
                $consOperationIdValue = -1;
                $consOperationId = SysOperationTypes::getConsTypeIdToGoOperationId(
                                array('table_name' => $params['table_name'], 'type_id' => $params['type_id'],));
                if (\Utill\Dal\Helper::haveRecord($consOperationId)) {
                    $consOperationIdValue = $consOperationId ['resultSet'][0]['id'];
                }

                $sql = "
                    UPDATE act_process_confirm
                    SET 
                        c_date =  timezone('Europe/Istanbul'::text, ('now'::text)::timestamp(0) with time zone),
                        op_cons_id = " . intval($opConsIdValue) . ", 
                        cons_operation_type_id " . intval($consOperationIdValue) . "
                    WHERE id = " . intval($params['id']);
                $statement = $pdo->prepare($sql);
                //echo debugPDO($sql, $params);
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
     * @ Gridi doldurmak için act_process_confirm tablosundan kayıtları döndürür !!
     * @version v 1.0  28.06.2016
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
            $sort = "smt.priority,  a.s_date,  membership_types_name ";
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
        $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
        if (!\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
             $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
        }  
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
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
                    a.s_date,
                    a.c_date
                FROM act_process_confirm a
                INNER JOIN sys_operation_types sot ON sot.base_id = a.operation_type_id AND sot.active =0 AND sot.deleted = 0 AND sot.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = sot.language_id AND l.deleted =0 AND l.active = 0
                LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
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
     * @ Gridi doldurmak için act_process_confirm tablosundan çekilen kayıtlarının kaç tane olduğunu döndürür   !!
     * @version v 1.0  28.06.2016
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
                FROM act_process_confirm a
                INNER JOIN sys_operation_types sot ON sot.base_id = a.operation_type_id AND sot.active =0 AND sot.deleted = 0 AND sot.language_parent_id =0 
                INNER JOIN sys_language l ON l.id = sot.language_id AND l.deleted =0 AND l.active = 0                
                INNER JOIN sys_language lp ON lp.id = a.preferred_language_id AND lp.deleted =0 AND lp.active = 0                
                INNER JOIN sys_osb_consultant_categories socc ON socc.id= sot.category_id AND socc.active =0 AND socc.deleted = 0 AND socc.language_parent_id =0 AND l.id = socc.language_id 
                INNER JOIN info_users uc ON uc.id = a.cons_id 
                INNER JOIN info_users opuc ON opuc.id = a.op_user_id                 
                LEFT JOIN sys_membership_periods smp ON smp.id = a.sys_membership_periods_id
                LEFT JOIN sys_membership_types smt ON smt.id = smp.mems_type_id AND smt.language_parent_id =0 AND l.id = smt.language_id                
                LEFT JOIN sys_periods sp ON sp.id = smp.period_id AND sp.language_parent_id =0 AND l.id = sp.language_id
                
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
     * @ danısmana atanmış operasyonların ana bilgilerinin döndürür !!     
     * @version v 1.0  29.06.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */                        
    public function getConsultantJobs($args = array()) {
        if (isset($args['page']) && $args['page'] != "" && isset($args['rows']) && $args['rows'] != "") {
            $offset = ((intval($args['page']) - 1) * intval($args['rows']));
            $limit = intval($args['rows']);
        } else {
            $limit = 10;
            $offset = 0;
        }

        $sortArr = array();
        $orderArr = array();
        $addSql = NULL;
        if (isset($args['sort']) && $args['sort'] != "") {
            $sort = trim($args['sort']);
            $sortArr = explode(",", $sort);
            if (count($sortArr) === 1)
                $sort = trim($args['sort']);
        } else {
            $sort = " priority, a.s_date, membership_types_name ";
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

        $sorguStr = null;
        if ((isset($args['filterRules']) && $args['filterRules'] != "")) {
            $filterRules = trim($args['filterRules']);
            $jsonFilter = json_decode($filterRules, true);

            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'operation_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=" AND operation_name" . $sorguExpression . ' ';

                            break;
                        case 'operation_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=" AND operation_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'category':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND category" . $sorguExpression . ' ';

                            break;
                        case 'category_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND category_eng" . $sorguExpression . ' ';

                            break;
                        case 'table_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND table_name" . $sorguExpression . ' ';

                            break;
                        case 'membership_types_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND membership_types_name" . $sorguExpression . ' ';

                            break;
                        case 'membership_types_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND membership_types_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'period_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND period_name" . $sorguExpression . ' ';

                            break;
                        case 'period_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND period_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'preferred_language':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND preferred_language" . $sorguExpression . ' ';

                            break;
                        case 'cons_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_name" . $sorguExpression . ' ';

                            break;
                        case 'cons_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_name" . $sorguExpression . ' ';

                            break;
                        case 'op_user_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';

                            break;
                        case 'op_cons_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND op_cons_name" . $sorguExpression . ' ';

                            break;
                        case 'cons_operation_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_operation_name" . $sorguExpression . ' ';

                            break;
                        case 'cons_operation_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_operation_name_eng" . $sorguExpression . ' ';

                            break;
                        default:
                            break;
                    }
                }
            }
        } else {
            $sorguStr = null;
            $filterRules = "";            
            if (isset($args['operation_type_id']) && $args['operation_type_id'] != "") {
                $sorguStr .= " AND operation_type_id = " . intval($args['operation_type_id']);
            }
            if (isset($args['category_id']) && $args['category_id'] != "") {
                $sorguStr .= " AND category_id = " . intval($args['category_id']);
            }
            if (isset($args['membership_types_id']) && $args['membership_types_id'] != "") {
                $sorguStr .= " AND membership_types_id = " . intval($args['membership_types_id']);
            }
            if (isset($args['sys_membership_periods_id']) && $args['sys_membership_periods_id'] != "") {
                $sorguStr .= " AND sys_membership_periods_id = " . intval($args['sys_membership_periods_id']);
            }
            if (isset($args['preferred_language_id']) && $args['preferred_language_id'] != "") {
                $sorguStr .= " AND preferred_language_id = " . intval($args['preferred_language_id']);
            }
            if (isset($args['cons_operation_type_id']) && $args['cons_operation_type_id'] != "") {
                $sorguStr .= " AND cons_operation_type_id = " . intval($args['cons_operation_type_id']);
            }            
                        
        }
        $sorguStr = rtrim($sorguStr, "AND ");

        /*
         * pk sahibi cons un işlerinin döndürücez
         */
        $opConsIdValue =0;        
        $opUserIdParams = array('pk' =>  $params['pk'],);
        $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
        $opConsId = $opUserIdArray->getUserId($opUserIdParams); 
        if (\Utill\Dal\Helper::haveRecord($opConsId)) {
            $opConsIdValue = $opConsId ['resultSet'][0]['user_id'];
        }        
                        
        /*
         * eger cons_id degeri varsa o cons un işlerinin döndürücez
         */
        if (isset($args['cons_id']) && $args['cons_id'] != "") {
            $opConsIdValue = intval($args['cons_id']); 
            } 
        $addSql = " WHERE a.cons_id = " . intval($opConsIdValue);

        /*
         * cons_id = -1 ise tüm cons ların işlemlerinin döndürücez.. 
         */
        if (isset($args['cons_id']) && $args['cons_id'] != -1) {
           $addSql = ""; 
        }
        
        $languageCode = 'tr';
        $languageIdValue = 647;
        if (isset($args['language_code']) && $args['language_code'] != "") {
            $languageCode = $args['language_code'];
        }       
        $languageCodeParams = array('language_code' => $languageCode,);
        $languageId = $this->slimApp-> getBLLManager()->get('languageIdBLL');  
        $languageIdsArray= $languageId->getLanguageId($languageCodeParams);
        if (!\Utill\Dal\Helper::haveRecord($languageIdsArray)) { 
             $languageIdValue = $languageIdsArray ['resultSet'][0]['id']; 
        } 

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT 
                    id,
                    operation_type_id,
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
                        a.s_date,
                        a.c_date,
                        smt.priority
                    FROM act_process_confirm a
                    INNER JOIN sys_operation_types sot ON sot.base_id = a.operation_type_id AND sot.active =0 AND sot.deleted = 0 AND sot.language_parent_id =0 
                    INNER JOIN sys_language l ON l.id = sot.language_id AND l.deleted =0 AND l.active = 0
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
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
                    ".$addSql." 
                ) AS xtable
                " . $sorguStr . " 
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
            // echo debugPDO($sql, $parameters);
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
     * @ danısmana atanmış operasyon kayıtlarının adedini döndürür !!
     * @version v 1.0  29.05.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */    
    public function getConsultantJobsRtc($args = array()) {                        
        $sorguStr = null;
        if ((isset($params['filterRules']) && $params['filterRules'] != "")) {
            $filterRules = trim($params['filterRules']);
            $jsonFilter = json_decode($filterRules, true);

            $sorguExpression = null;
            foreach ($jsonFilter as $std) {
                if ($std['value'] != null) {
                    switch (trim($std['field'])) {
                        case 'operation_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=" AND operation_name" . $sorguExpression . ' ';

                            break;
                        case 'operation_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\' ';
                            $sorguStr.=" AND operation_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'category':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND category" . $sorguExpression . ' ';

                            break;
                        case 'category_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND category_eng" . $sorguExpression . ' ';

                            break;
                        case 'table_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND table_name" . $sorguExpression . ' ';

                            break;
                        case 'membership_types_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND membership_types_name" . $sorguExpression . ' ';

                            break;
                        case 'membership_types_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND membership_types_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'period_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND period_name" . $sorguExpression . ' ';

                            break;
                        case 'period_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND period_name_eng" . $sorguExpression . ' ';

                            break;
                        case 'preferred_language':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND preferred_language" . $sorguExpression . ' ';

                            break;
                         case 'cons_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_name" . $sorguExpression . ' ';

                            break;
                        case 'op_user_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND op_user_name" . $sorguExpression . ' ';

                            break;
                        case 'op_cons_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND op_cons_name" . $sorguExpression . ' ';

                            break;
                        case 'cons_operation_name':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_operation_name" . $sorguExpression . ' ';

                            break;
                        case 'cons_operation_name_eng':
                            $sorguExpression = ' ILIKE \'%' . $std['value'] . '%\'  ';
                            $sorguStr.=" AND cons_operation_name_eng" . $sorguExpression . ' ';

                            break;
                        default:
                            break;
                    }
                }
            }
        } else {
            $sorguStr = null;
            $filterRules = "";            
            if (isset($params['operation_type_id']) && $params['operation_type_id'] != "") {
                $sorguStr .= " AND operation_type_id = " . intval($params['operation_type_id']);
            }
            if (isset($params['category_id']) && $params['category_id'] != "") {
                $sorguStr .= " AND category_id = " . intval($params['category_id']);
            }
            if (isset($params['membership_types_id']) && $params['membership_types_id'] != "") {
                $sorguStr .= " AND membership_types_id = " . intval($params['membership_types_id']);
            }
            if (isset($params['sys_membership_periods_id']) && $params['sys_membership_periods_id'] != "") {
                $sorguStr .= " AND sys_membership_periods_id = " . intval($params['sys_membership_periods_id']);
            }
            if (isset($params['preferred_language_id']) && $params['preferred_language_id'] != "") {
                $sorguStr .= " AND preferred_language_id = " . intval($params['preferred_language_id']);
            }
            if (isset($params['cons_operation_type_id']) && $params['cons_operation_type_id'] != "") {
                $sorguStr .= " AND cons_operation_type_id = " . intval($params['cons_operation_type_id']);
            }
            
            
        }
        $sorguStr = rtrim($sorguStr, "AND ");

         /*
         * pk sahibi cons un işlerinin döndürücez
         */
        $opConsIdValue =0;
        $opUserIdParams = array('pk' =>  $params['pk'],);
        $opUserIdArray = $this->slimApp-> getBLLManager()->get('opUserIdBLL');  
        $opConsId = $opUserIdArray->getUserId($opUserIdParams); 
        if (\Utill\Dal\Helper::haveRecord($opConsId)) {
            $opConsIdValue = $opConsId ['resultSet'][0]['user_id'];
        }        
                        
        /*
         * eger cons_id degeri varsa o cons un işlerinin döndürücez
         */
        if (isset($params['cons_id']) && $params['cons_id'] != "") {
            $opConsIdValue = intval($params['cons_id']); 
            } 
        $addSql = "  WHERE a.cons_id = " . intval($opConsIdValue);

        /*
         * cons_id = -1 ise tüm cons ların işlemlerinin döndürücez.. 
         */
        if (isset($params['cons_id']) && $params['cons_id'] != -1) {
           $addSql = ""; 
        }
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

        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "
                SELECT COUNT(id) AS count FROM (
                SELECT 
                    id,
                    operation_type_id,
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
                    s_date,
                    c_date
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
                        a.s_date,
                        a.c_date
                    FROM act_process_confirm a
                    INNER JOIN sys_operation_types sot ON sot.base_id = a.operation_type_id AND sot.active =0 AND sot.deleted = 0 AND sot.language_parent_id =0 
                    INNER JOIN sys_language l ON l.id = sot.language_id AND l.deleted =0 AND l.active = 0
                    LEFT JOIN sys_language lx ON lx.id = ".intval($languageIdValue)." AND lx.deleted =0 AND lx.active =0
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
                    ".$addSql."
                ) AS xtable
                " . $sorguStr . " 
                ) AS xxtable    
               ";
            $statement = $pdo->prepare($sql);                        
            // echo debugPDO($sql, $parameters);
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
