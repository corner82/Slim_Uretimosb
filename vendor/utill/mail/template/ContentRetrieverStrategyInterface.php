<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/slim_test for the canonical source repository
 * @copyright Copyright (c) 2015 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
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
