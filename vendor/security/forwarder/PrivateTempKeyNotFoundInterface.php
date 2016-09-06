<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Security\Forwarder;

interface PrivateTempKeyNotFoundInterface {
    public function setPrivateKeyTempNotFoundRedirect($boolean = null);
    public function getPrivateKeyTempNotFoundRedirect();
    public function privateKeyTempNotFoundRedirect();
}