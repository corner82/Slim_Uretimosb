<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Mail\PhpMailer;

/**
 * phpmailer wrapper for auth emails
 * @author Mustafa Zeynel Dağlı
 * @since 01/09/2016
 */
class PhpMailAuthWrapper extends \Utill\Mail\PhpMailer\PhpMailWrapper implements \Utill\Mail\AuthMailInterface{

    /**
     * send auth mails
     * @param array $params
     */
    public function sendAuthMail(array $params = null) {
        
        $this->mailObj->CharSet='UTF-8'; 
        //$mail->headerLine($headers, $value);
        $this->mailObj->IsSMTP(); // telling the class to use SMTP 
        $this->mailObj->Host       = $this->getSMTPServerHost(); // SMTP server 
        
        $this->mailObj->SMTPAuth   = true;                  // enable SMTP authentication   
        $this->mailObj->SMTPSecure = $this->getSMTPServerSecureProtocol();   
        $this->mailObj->Port       = $this->getSMTPServerPort();                        // set the SMTP port for the GMAIL server
        $this->mailObj->Username   = $this->getSMTPServerUser(); // SMTP account username
        $this->mailObj->Password   = $this->getSMTPServerUserPassword();             // SMTP account password
        $this->mailObj->SetFrom($this->getFromUserName(), $params['info']);
        //$mail->AddReplyTo("311corner82@gmail.com","8.  deneme");
        $this->mailObj->Subject    = $params['subject'];

        //$mail->AltBody    = " ıı öö ğğ işş çç !"; // optional, comment out and test

        $this->mailObj->MsgHTML($this->getMessage());
        //$this->mailObj->MsgHTML($body);
        $address = $params['to'];
        //$mail->addCC('bahram.metu@gmail.com');
        //$mail->addBCC('311corner82@gmail.com'); 
        $this->mailObj->AddAddress($address, "");
        //$mail->AddAttachment("images/phpmailer.gif");      // attachment
        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
        if(!$this->mailObj->Send()) {
        echo "Mailer Error: " . $this->mailObj->ErrorInfo;
        } else {
        echo "Message sent!";
        } 
    }
    
    /**
     * send mail in debug mode
     * @param array $params
     * @author Mustafa Zeynel Dağlı
     * @since 01/*9/2016
     */
    public function sendAuthMailDebug(array $params = null) {
        $this->mailObj->CharSet='UTF-8'; 
        //$mail->headerLine($headers, $value);
        $this->mailObj->IsSMTP(); // telling the class to use SMTP 
        //$this->mailObj->Host       = "mail.ostimteknoloji.com"; // SMTP server 
        $this->mailObj->Host       = $this->getSMTPServerHost(); // SMTP server 
        $this->mailObj->SMTPDebug  = $this->getDebugMode(); // enables SMTP debug information (for testing) 
                                                    // 1 = errors and messages
                                                    // 2 = messages only
        $this->mailObj->SMTPAuth   = true;                  // enable SMTP authentication  
        $this->mailObj->SMTPSecure = $this->getSMTPServerSecureProtocol();   
        $this->mailObj->Port       = $this->getSMTPServerPort();                        // set the SMTP port for the GMAIL server
        $this->mailObj->Username   = $this->getSMTPServerUser(); // SMTP account username
        $this->mailObj->Password   = $this->getSMTPServerUserPassword();             // SMTP account password
        $this->mailObj->SetFrom($this->getFromUserName(), $params['info']);
        //$mail->AddReplyTo("311corner82@gmail.com","8.  deneme");
        $this->mailObj->Subject    = $params['subject'];

        //$mail->AltBody    = " ıı öö ğğ işş çç !"; // optional, comment out and test

        $this->mailObj->MsgHTML($this->getMessage());
        //$this->mailObj->MsgHTML($body);
        $address = $params['to'];
        //$mail->addCC('bahram.metu@gmail.com');
        //$mail->addBCC('311corner82@gmail.com'); 
        $this->mailObj->AddAddress($address, "");
        //$mail->AddAttachment("images/phpmailer.gif");      // attachment
        //$mail->AddAttachment("images/phpmailer_mini.gif"); // attachment
        if(!$this->mailObj->Send()) {
        echo "Mailer Error: " . $this->mailObj->ErrorInfo;
        } else {
        //echo "Message sent!";
        } 
    }

}
