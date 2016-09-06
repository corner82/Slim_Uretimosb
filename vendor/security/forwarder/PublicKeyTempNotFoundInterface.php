<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Security\Forwarder;


interface PublicKeyTempNotFoundInterface {
    public function setPublicKeyTempNotFoundRedirect($boolean = null);
    public function getPublicKeyTempNotFoundRedirect();
    public function publicKeyTempNotFoundRedirect();
}