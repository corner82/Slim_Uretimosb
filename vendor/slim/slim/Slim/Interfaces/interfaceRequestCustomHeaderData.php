<?php

namespace Slim\Interfaces;

interface interfaceRequestCustomHeaderData {
    public function getRequestHeaderData();
    public function setRequestHeaderData($requestHeaderData = array());
}

