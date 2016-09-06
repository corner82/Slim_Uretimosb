<?php

namespace Slim\Interfaces;

interface interfaceRequest {
    public function getAppRequest();
    public function setAppRequest(\Slim\Http\Request $request = null);
}

