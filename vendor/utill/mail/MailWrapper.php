<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Mail;

// require_once '../../../phpmailer/phpmailer/PhpMailerAutoload.php';

class MailWrapper extends AbstractMailWrapper {

 
    
    public function __construct() {
        
    } 

    
    public function mailServerConfig($params = array()) {
        try {
                $valueHostAddress = $this->getServer();
                $valuePort = $this->getPort();
                $valueUsername = $this->getUser();
                $valuePsswrd = $this->getPassword();
                $valueSetFrom = $this->getFrom();
                $valueSetFromName = $this->getFromName();
                $valueCharset = $this->getCharset();
                $valueSmtpAuth = $this->getSmtpAuth();
                $valueSmtpDebug = $this->getSmtpDebug();
                $valueSmtpSecure = $this->getSmtpSecure();
            
            $errorInfo = $statement->errorInfo();
            if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                throw new \PDOException($errorInfo[0]);
            return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    public function sendAuthorizingMail($params = array()) {
        try {
           $params1 = json_decode($params['recipients'],true);
           
            print_r($params1);
           // print_r($params1[0]['recipients']);
            
            if ((isset($params['recipients']) && $params['recipients'] != "")) { 
                $body = ' OSB İmalat  <b>OSB İmalat !</b>';
                if ((isset($params['body']) && $params['body'] != "")) {
                    $body = $params['body'];
                }
                $body = eregi_replace("[\]", '', $body);

                $valueSetFrom=NULL;
                if ((isset($params['set_from']) && $params['set_from'] != "")) {
                    $valueSetFrom = $params['set_from'];
                }
                $valueSetFromName=NULL;
                if ((isset($params['set_from_name']) && $params['set_from_name'] != "")) {
                    $valueSetFromName = $params['set_from_name'];
                }  
                $valueSuject = "Osbİmalat";
                if ((isset($params['subject']) && $params['subject'] != "")) {
                    $valueSuject = $params['subject'];
                } 
                $languageCode = NULL;
                if ((isset($params['language_code']) && $params['language_code'] != "")) {
                    $vLanguageCode = $params['language_code'];
                } 
                
                $paramsEmail =
                array(
                        'language_code' => $languageCode,
                        'set_from' => $valueSetFrom,
                        'set_from_name' => $valueSetFromName,
                        'subject' => $valueSuject,
                        'body' => $body,   
                    );
                //print_r($paramsEmail) ; 
                $recipients=NULL;
                if ((isset($params['recipients']) && $params['recipients'] != "")) { 
                $recipients = json_decode($params['recipients'], true);
                }
                $recipientsBcc=NULL;
                if ((isset($params['recipientsBcc']) && $params['recipientsBcc'] != "")) { 
                $recipientsBcc=  json_decode($params['recipientsBcc'], true);
                 }
                $recipientsCc=NULL;
                if ((isset($params['recipientsCc']) && $params['recipientsCc'] != "")) { 
                $recipientsCc=  json_decode($params['recipientsCc'], true);
                }
                $attachment=NULL;
                if ((isset($params['attachment']) && $params['attachment'] != "")) { 
                $attachment=  json_decode($params['attachment'], true);
                }
                
                
                $kontrol = AbstractMailWrapper::send_email(
                                        $paramsEmail,
                                        $recipients,
                                        $recipientsBcc,
                                        $recipientsCc,
                                        $attachment);
                 print_r($kontrol);
                $sql = "
                 
                ";
              //  $statement = $pdo->prepare($sql);
                //  echo debugPDO($sql, $params);                
                // $statement->execute();
                $result = $kontrol;//$statement->fetchAll(\PDO::FETCH_ASSOC);
                $errorInfo = 0 ; //$statement->errorInfo();
                if ($errorInfo[0] != "00000" && $errorInfo[1] != NULL && $errorInfo[2] != NULL)
                    throw new \phpmailerException($errorInfo[0]);
                return array("found" => true, "errorInfo" => $errorInfo, "resultSet" => $result);
            } else {
                $errorInfo = '23502';   // 23502  address not_null_violation
                $errorInfoColumn = 'recipients';
                return array("found" => false, "errorInfo" => $errorInfo, "resultSet" => '', "errorInfoColumn" => $errorInfoColumn);
            }
        } catch (\PDOException $e /* Exception $e */) {
            //$debugSQLParams = $statement->debugDumpParams();
            return array("found" => false, "errorInfo" => $e->getMessage()/* , 'debug' => $debugSQLParams */);
        }
    }

    public function sendUserProblemMail($params = array()) {
        
    }

    public function sendUserProblemMaila($params = array()) {
        
    }

   

}
