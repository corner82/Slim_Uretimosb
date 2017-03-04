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
class BlLoginLogout extends \DAL\DalSlim {

    /**     
     * @author Okan CIRAN
     * @ info_users tablosundan parametre olarak  gelen id kaydını siler. !!
     * @version v 1.0  30.12.2015
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
     * @ info_users tablosundaki tüm kayıtları getirir.  !!
     * @version v 1.0  30.12.2015  
     * @param array | null $args  
     * @return array
     * @throws \PDOException
     */
    public function getAll($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $statement = $pdo->prepare(" 
                          ");           
            //$statement->execute();
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
     * @ info_users tablosuna yeni bir kayıt oluşturur.  !!
     * @version v 1.0  30.12.2015
     * @return array
     * @throws \PDOException
     */
    public function insert($params = array()) {        
    }

    /**
     * basic update database example for PDO prepared
     * statements, table names are irrevelant and should be changed on specific
     * @author Okan CIRAN
     * info_users tablosuna parametre olarak gelen id deki kaydın bilgilerini günceller   !!
     * @version v 1.0  30.12.2015
     * @param array | null $args  
     * @return array
     * @throws \PDOException
     */
    public function update($params = array()) {
        
    }
    
    /**
     * 
     * @author Okan CIRAN
     * @ public key e ait bir private key li kullanıcı varsa True değeri döndürür.  !!
     * @version v 1.0  31.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function pkTempControl($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');            
            $sql = "     
                        SELECT id,pkey,sf_private_key_value_temp ,root_id FROM (
                            SELECT id, 	
                                CRYPT(sf_private_key_value_temp,CONCAT('_J9..',REPLACE('".$params['pktemp']."','*','/'))) = CONCAT('_J9..',REPLACE('".$params['pktemp']."','*','/')) AS pkey,	                                
                                sf_private_key_value_temp , root_id
                            FROM info_users WHERE active=0 AND deleted=0) AS logintable
                        WHERE pkey = TRUE
                    ";  
            $statement = $pdo->prepare($sql);
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
     * 
     * @author Okan CIRAN
     * @ public key e ait bir private key li kullanıcı varsa True değeri döndürür.  !!
     * @version v 1.0  31.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function pkControl($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "              
                    SELECT id,pkey,sf_private_key_value FROM (
                            SELECT COALESCE(NULLIF(root_id, 0),id) AS id, 	
                                CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('".$params['pk']."','*','/'))) = CONCAT('_J9..',REPLACE('".$params['pk']."','*','/')) AS pkey,	                                
                                sf_private_key_value
                            FROM info_users WHERE active=0 AND deleted=0) AS logintable
                        WHERE pkey = TRUE
                    "; 
            $statement = $pdo->prepare($sql);            
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
     * 
     * @author Okan CIRAN
     * @ login için info_users tablosundan çekilen kayıtları döndürür   !!
     * bu fonksiyon aktif olarak kullanılmıyor. ihtiyaç a göre aktifleştirilecek. public key algoritması farklı. 
     * @version v 1.0  31.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function pkLoginControl($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            $sql = "          
                SELECT 
                    a.id,
                    a.name, 
                    a.surname, 
                    a.username, 
                    a.auth_email, 
                    a.gender_id, 
                    sd4.description AS gender, 
                    a.active, 
                    a.auth_allow_id, 
                    sd.description AS auth_alow,
                    a.cons_allow_id,
                    sd1.description AS cons_allow,
                    a.language_code AS user_language,  
                    COALESCE(NULLIF(l.language_main_code,''),'en') AS language_main_code,
                    a.sf_private_key_value,
                    COALESCE(NULLIF( 
                    (SELECT CAST(MIN(bz.parent) AS varchar(5))
                            FROM sys_acl_roles az 
                            LEFT JOIN sys_acl_roles bz ON bz.parent = az.id   
                            WHERE az.id= sarmap.role_id),''), CAST(sar.parent AS varchar(5))) AS Menu_type,
                    root_id
                FROM info_users a              
                LEFT JOIN sys_language l ON l.language_main_code = a.language_code AND l.deleted =0 AND l.active =0 
                INNER JOIN sys_specific_definitions sd ON sd.main_group = 13 AND sd.language_code = COALESCE(NULLIF(l.language_main_code, ''), 'en') AND a.auth_allow_id = sd.first_group 
                INNER JOIN sys_specific_definitions sd1 ON sd1.main_group = 14 AND sd1.language_code = COALESCE(NULLIF(l.language_main_code, ''), 'en') AND a.cons_allow_id = sd1.first_group 
                INNER JOIN sys_specific_definitions sd3 ON sd3.main_group = 16 AND sd3.first_group= a.active AND sd3.language_code = COALESCE(NULLIF(l.language_main_code, ''),'en') AND sd3.deleted = 0 AND sd3.active = 0
                INNER JOIN sys_specific_definitions sd4 ON sd4.main_group = 3 AND sd4.first_group= a.active AND sd4.language_code = COALESCE(NULLIF(l.language_main_code, ''),'en') AND sd4.deleted = 0 AND sd4.active = 0
                
                
                INNER JOIN sys_acl_roles sar ON sar.id = a.role_id AND sar.active=0 AND sar.deleted=0 
                WHERE  
                    CRYPT(a.sf_private_key_value,CONCAT('_J9..',REPLACE('".$params['pk']."','*','/'))) = CONCAT('_J9..',REPLACE('".$params['pk']."','*','/')) 
                    ";
            $statement = $pdo->prepare($sql);            
      //      echo debugPDO($sql, $parameters);
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
     * 
     * @author Okan CIRAN
     * @ info_users tablosundan public key i döndürür   !!
     * @version v 1.0  31.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function getPK($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
      
            /**
             * @version kapatılmıs olan kısımdaki public key algoritması kullanılmıyor.
             */
            /*      $sql = "          
            SELECT                
                REPLACE(REPLACE(ARMOR(pgp_sym_encrypt(a.sf_private_key_value, 'Bahram Lotfi Sadigh', 'compress-algo=1, cipher-algo=bf'))
	,'-----BEGIN PGP MESSAGE-----

',''),'
-----END PGP MESSAGE-----
','') as public_key1     ,

                substring(ARMOR(pgp_sym_encrypt(a.sf_private_key_value, 'Bahram Lotfi Sadigh', 'compress-algo=1, cipher-algo=bf')),30,length( trim( sf_private_key))-62) as public_key2, 
        */      
            ///crypt(:password, gen_salt('bf', 8)); örnek bf komut
                  $sql = "   
                        
                SELECT       
                     REPLACE(TRIM(SUBSTRING(crypt(sf_private_key_value,gen_salt('xdes')),6,20)),'/','*') AS public_key 
                FROM info_users a              
                INNER JOIN sys_acl_roles sar ON sar.id = a.role_id AND sar.active=0 AND sar.deleted=0 
                WHERE a.username = :username 
                    AND a.password = :password   
                    AND a.deleted = 0 
                    AND a.active = 0 
                
                                 ";

            $statement = $pdo->prepare($sql);
            $statement->bindValue(':username', $params['username'], \PDO::PARAM_STR);
            $statement->bindValue(':password', $params['password'], \PDO::PARAM_STR);
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
     * 
     * @author Okan CIRAN
     * @ public key e ait bir private key li kullanıcı varsa True değeri döndürür.  !!
     * @version v 1.0  31.12.2015
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function pkSessionControl($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');
            /*
            $sql = "          
                SELECT * FROM (
                    SELECT id, sf_private_key_value = 
                    Pgp_sym_decrypt( 
                     DEARMOR(CONCAT( '-----BEGIN PGP MESSAGE-----

                    ',:pk,'
                    -----END PGP MESSAGE-----
                    '))
                    , 'Bahram Lotfi Sadigh', 'compress-algo=1, cipher-algo=bf') AS pkey
                    FROM info_users) AS logintable
                WHERE pkey = TRUE                
                                 ";             
             */
            $sql = "    
                    SELECT 
                        a.id, 
                        a.name, 
                        a.data, 
                        a.lifetime, 
                        a.c_date, 
                        a.modified, 
                        a.public_key, 
                        b.name AS u_name, 
                        b.surname AS u_surname, 
                        b.username,
                        b.sf_private_key_value,
                        b.root_id                        
                    FROM act_session a 
                    INNER JOIN info_users b ON CRYPT(b.sf_private_key_value,CONCAT('_J9..',REPLACE(a.public_key,'*','/'))) = CONCAT('_J9..',REPLACE(a.public_key,'*','/'))
                        AND b.active = 0 AND b.deleted = 0
                    WHERE a.public_key = :public_key 
                    ";  
            
            $statement = $pdo->prepare($sql);
            $statement->bindValue(':public_key', $params['pk'], \PDO::PARAM_STR);
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
     * 
     * @author Okan CIRAN
     * @ public key varsa True değeri döndürür.  !!
     * @version v 1.0  06.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function pkIsThere($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');          
            $sql = "              
                    SELECT a.public_key =  '".$params['pk']."'
                    FROM act_session a                  
                    WHERE a.public_key =   '".$params['pk']."'
                    ";                       
            $statement = $pdo->prepare($sql);            
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
     * get company public key due to user public key
     * @param type $publicKey
     * @return type
     * @throws \PDOException
     * @author Mustafa Zeynel Dağlı
     * @since 10/06/2016
     */
    public function isUserBelongToCompany($requestHeaderParams, $params) {
        try {
            $resultSet = $this->pkControl(array('pk' =>$requestHeaderParams['X-Public']));
            //print_r($resultSet); 
            
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');  
            
            $sql = "              
                    SELECT firm_id AS firm_id, 1=1 AS control FROM (
                            SELECT a.firm_id ,
                             CRYPT(sf_private_key_value,CONCAT('_J9..',REPLACE('".$params['cpk']."','*','/'))) = CONCAT('_J9..',REPLACE('".$params['cpk']."','*','/')) as cpk 
                            FROM info_firm_keys a                                                        
                INNER JOIN info_firm_profile ifp ON ifp.active =0 AND ifp.deleted =0 AND ifp.language_parent_id =0 AND a.firm_id = ifp.act_parent_id     
                INNER JOIN info_firm_users ifu ON ifu.user_id = " . intval($resultSet['resultSet'][0]['id']) . " AND ifu.language_parent_id =0 AND a.firm_id = ifu.firm_id
                ) AS xtable WHERE cpk = TRUE  limit 1
                    "; 
            
           // print_r($sql);
            
            $statement = $pdo->prepare($sql);  
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
           // print_r($result);

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
     * 
     * @author Okan CIRAN
     * @ parametre olarak gelen public key in private key inden üretilmiş aktif tüm public key leri döndürür.  !!     
     * @version v 1.0  06.01.2016
     * @param array | null $args
     * @return array
     * @throws \PDOException
     */
    public function pkAllPkGeneratedFromPrivate($params = array()) {
        try {
            $pdo = $this->slimApp->getServiceManager()->get('pgConnectFactory');          
            $sql = "  
                    SELECT ax.id, ax.name,ax.data,ax.lifetime,ax.c_date,ax.public_key FROM act_session ax 
                    WHERE 
                        CRYPT((SELECT b.sf_private_key_value
                                            FROM act_session a 
                                            INNER JOIN info_users b ON CRYPT(b.sf_private_key_value,CONCAT('_J9..',REPLACE(a.public_key,'*','/'))) = CONCAT('_J9..',REPLACE(a.public_key,'*','/'))
                                                AND b.active = 0 AND b.deleted = 0
                                            WHERE a.public_key = '".$params['pk']."'
                        ),CONCAT('_J9..',REPLACE(ax.public_key,'*','/'))) = CONCAT('_J9..',REPLACE(ax.public_key,'*','/')) 
                    ";                       
            $statement = $pdo->prepare($sql);                        
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
