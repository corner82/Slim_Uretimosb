<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */

namespace Utill\Strip;

 class Strip extends AbstractStrip implements \Services\Filter\FilterChainInterface
                                              {
    
    public function __construct($params = null) {
        
        //if(empty($params))throw new Exception('strip class constructor parametre hatası');
        
        
    }
    
    public function strip($key = null) {
        $this->rewind();
        foreach ($this->stripStrategies as $key => $value) {
            if(method_exists($value, 'strip')) { 
                $value->strip($key);
            } else {
                throw new \Exception('invalid strip method for strip');
            }
        }
    }

    public function getFilterChain($name = null) {
        
    }

    public function setFilterChain(\Utill\Strip\Chain\AbstractStripChainer $filterChainer) {
        
    }

}

