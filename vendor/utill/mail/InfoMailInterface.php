<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Mail;


interface InfoMailInterface  {

    public function sendInfoMail(array $params = null);
    public function sendInfoMailSMTP(array $params = null);
    public function sendInfoMailSMTPDebug(array $params = null);

}
