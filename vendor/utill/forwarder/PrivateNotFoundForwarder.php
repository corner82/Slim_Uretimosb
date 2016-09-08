<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */
namespace Utill\Forwarder;

/**
 * hash control and redirection if necessary
 * @author Mustafa Zeynel Dağlı
 */
class PrivateNotFoundForwarder extends \Utill\Forwarder\AbstractForwarder {
    
    /**
     * constructor
     */
    public function __construct() {

    }
    
    /**
     * redirect
     */
    public  function redirect() {
        //ob_end_flush();
        /*ob_end_clean();
        $newURL = 'http://localhost/slim_redirect_test/index.php/hashNotMatch';
        header("Location: {$newURL}");*/
        
        ob_end_clean();
        //$ch = curl_init('http://slimRedirect.uretimosb.com/index.php/hashNotMatch');
        $ch = curl_init('http://localhost/Slim_Redirect_Uretimosb/index.php/privateNotFoundSlim');
        //curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        //curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        //curl_setopt($ch,CURLOPT_POSTFIELDS,$content);

        $result = curl_exec($ch);
        curl_close($ch);
        exit();
        
    }
}
