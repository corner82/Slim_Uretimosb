<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Mail;


interface AuthMailInterface  {

    public function sendAuthMail(array $params = null);
    public function sendAuthMailDebug(array $params = null);

}
