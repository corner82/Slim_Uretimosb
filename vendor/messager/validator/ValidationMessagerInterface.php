<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Messager\Validator;

interface ValidationMessagerInterface{
    public function setValidationMessage($validationMessage = null);
    public function getValidationMessage();
    public function addValidationMessage($validationMessage = null);
}

