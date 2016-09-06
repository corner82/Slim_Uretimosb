<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Security\Forwarder;


interface PublicKeyNotFoundInterface {
    public function setPublicKeyNotFoundRedirect($boolean = null);
    public function getPublicKeyNotFoundRedirect();
    public function publicKeyNotFoundRedirect();
}