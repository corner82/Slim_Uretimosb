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
 * mail template content retrieve strategy interface
 * @author Mustafa Zeynel Dağlı
 * @since 29/06/2016
 */
interface ContentRetrieverStrategyInterface  {

    public function getContent(array $params = null);
    public function fillContent(array $params = null);

}
