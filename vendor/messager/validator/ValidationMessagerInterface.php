<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Messager\Validator;

interface ValidationMessagerInterface{
    public function setValidationMessage($validationMessage = null);
    public function getValidationMessage();
    public function addValidationMessage($validationMessage = null);
}

