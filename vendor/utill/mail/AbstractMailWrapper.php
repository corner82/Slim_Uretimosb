<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Mail;


abstract class AbstractMailWrapper {

    /**
     * SMTP server connection port
     * TCP port to connect to
     * @var int 
     */
    protected $smtpServerPort = 587;



    /**
     * mail from user password
     * @var string | null
     */
    protected $fromMailUserPassword ;


    /**
     * mail from user name
     * @var type string | null
     */
    protected $fromUserName;
    
    /**
     * SMTP Server user 
     * @var type string | null
     */
    protected $smtpServerUser;
    
    /**
     * SMTP Server user password
     * @var type string | null
     */
    protected $smtpServerUserPassword;

    /**
     *  mail charset
     * @var string | null
     */
    protected $charset = 'UTF-8';
    
    /**
     * SMTP server host
     * @var string | null
     */
    protected $smtpServerHost;

    /**
     * SMTP server security protocol 
     * Enable TLS encryption, `ssl` also accepted
     * @var string | null
     */
    protected $smtpSecureProtocol = 'TLS';

    /**
     * mail message
     * @var string | null
     */
    protected $mailMessage;

    /**
     * PhpMailer subject
     * subject line
     * @var string | null
     */
    protected $subject;
    
    /**
     * set mail subject
     * @param string | null $Subject
     */
    public function setSubject($subject = null) {
        $this->subject = $subject;
    }

    /**
     * get mail subject
     * @return string | null
     */
    public function getSubject() {
        if(!isset($this->subject ))  throw new Exception('mail subject not found');
        return $this->subject;
    }
    
    /**
     * set mail charset
     * @param string | null $charset
     */
    public function setCharset($charset = null) {
        $this->charset = $charset;
    }

    /**
     * get mail charset
     * @return string | null
     */
    public function getCharset() {
        if(!isset($this->charset ))  throw new Exception('mail charset not found');
        return $this->charset;
    }
    
    /**
     * set SMTP server host
     * @param string | null $mailHost
     */
    public function setSMTPServerHost($smtpServerHost = null) {
        $this->smtpServerHost = $smtpServerHost;
    }

    /**
     * get SMTP server  host
     * @return string | null
     */
    public function getSMTPServerHost() {
        if(!isset($this->smtpServerHost ))  throw new Exception('SMTP server host not found');
        return $this->smtpServerHost;
    }
    
    /**
     * set mail from user name
     * @param string | null $user
     */
    public function setFromUserName($fromUserName = null) {
        $this->fromUserName = $fromUserName;
    }

    /**
     * get mail from user name
     * @return string | null
     */
    public function getFromUserName() {
        if(!isset($this->fromUserName ))  throw new Exception('from mail user not found');
        return $this->fromUserName;
    }
    
    /**
     * set mail from user password
     * @param string | null $password
     */
    public function setFromUserPassword($fromUserPassword = null) {
        $this->fromMailUserPassword = $fromUserPassword;
    }

    /**
     * mail from user password
     * @return string | null
     */
    public function getFromUserPassword() {
        return $this->fromMailUserPassword;
    }
    
    
    /**
     * set SMTP Server user
     * @param string | null $user
     */
    public function setSMTPServerUser($smtpServerUser = null) {
        $this->smtpServerUser = $smtpServerUser;
    }

    /**
     * get SMTP server user 
     * @return string | null
     */
    public function getSMTPServerUser() {
        if(!isset($this->smtpServerUser ))  throw new Exception('SMTP server user not found');
        return $this->smtpServerUser;
    }
    
    /**
     * set SMTP Server user password
     * @param string | null $smtpServerUserPassword
     */
    public function setSMTPServerUserPassword($smtpServerUserPassword = null) {
        $this->smtpServerUserPassword = $smtpServerUserPassword;
    }

    /**
     * get SMTP server user password 
     * @return string | null
     */
    public function getSMTPServerUserPassword() {
        if(!isset($this->smtpServerUserPassword ))  throw new Exception('SMTP user password not found');
        return $this->smtpServerUserPassword; 
    }
   
      /**
     * set SMTP server port
     * @param string | null $smtpServerPort
     */
    public function setSMTPServerPort($smtpServerPort = null) {
        $this->smtpServerPort = $smtpServerPort;
    }

    /**
     * get SMTP server port
     * @return int | null
     */
    public function getSMTPServerPort() {
        if(!isset($this->smtpServerPort ))  throw new Exception('SMTP user port not found');
        return $this->smtpServerPort;
    }


    /**
     * set mail message
     * @param string | null $message
     */
    public function setMessage($message = null) {
        $this->message = $message;
    }

    /**
     * get mail message
     * @return string | null
     */
    public function getMessage() {
        if(!isset($this->message ))  throw new Exception('mail message not found');
        return $this->message;
    }
    
      /**
     * set SMTP server security protocol
     * @param string | null $smtpServerSecureProtocol
     */
    public function setSMTPServerSecureProtocol($smtpServerSecureProtocol = null) {
        $this->smtpSecureProtocol = $smtpServerSecureProtocol;
    }

    /**
     * get SMTP server secure protocol
     * @return int | null
     */
    public function getSMTPServerSecureProtocol() {
        if(!isset($this->smtpSecureProtocol ))  throw new Exception('SMTP server secure protocol not found');
        return $this->smtpSecureProtocol;
    }

    

}
