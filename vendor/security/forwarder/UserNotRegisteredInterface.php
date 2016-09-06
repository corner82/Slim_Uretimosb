<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Security\Forwarder;


interface UserNotRegisteredInterface {
    public function setUserNotRegisteredRedirect($boolean = null);
    public function getUserNotRegisteredRedirect();
    public function userNotRegisteredRedirect();
}