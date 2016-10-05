<?php
/**
 * OSB İMALAT Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSB İMALAT (http://www.uretimosb.com)
 * @license   
 */

namespace Utill\Mail\Template;

/**
 * mail template content retriever concrete class
 * @author Mustafa Zeynel Dağlı
 * @since 29/06/2016
 */
class ContentRetrieverFromFileStrategy implements ContentRetrieverStrategyInterface {

/**
 * 
 * @param string $content
 */
protected $content;


public function fillContent(array $params = null) {
    //print_r(dirname(__FILE__)); 
    $this->content = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'contents'.DIRECTORY_SEPARATOR.$params['fileName'].'.html');
}

public function getContent(array $params = null) {
    
    if(!isset($this->content)) {
        $this->fillContent($params);
    }
    return $this->content;
}

}
