<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Security\Forwarder;

/**
 * interface to determine if service needs public and private key interragation
 * @author Mustafa Zeynel Dağlı
 * @since version 0.3
 */
interface PublicKeyRequiredInterface {
    public function servicePkRequired();
}