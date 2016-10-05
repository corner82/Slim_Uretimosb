<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Mail;


interface InfoMailInterface  {

    public function sendInfoMail(array $params = null);
    public function sendInfoMailSMTP(array $params = null);
    public function sendInfoMailSMTPDebug(array $params = null);

}
