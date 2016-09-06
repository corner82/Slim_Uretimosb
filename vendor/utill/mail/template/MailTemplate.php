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
 * mail template concrete class
 * @author Mustafa Zeynel Dağlı
 * @since 29/06/2016
 */
class MailTemplate extends AbstractMailTemplate {

    /**
     * abstract method overridden
     * @param type $variablesToBeReplaced
     * @return boolean
     */
    public function replaceTemplatePlaceHolders($variablesToBeReplaced=null) {
        if(!empty($variablesToBeReplaced)) {
            $content = $this->getTemplateContent();
            $content = str_replace(array_keys($variablesToBeReplaced), 
                        array_values($variablesToBeReplaced), 
                        $content);
            $this->templateContent = $content;
        }
        return false;  
    }
    
    /**
     * abstract method overriden
     * @param type $variablesToBeReplaced
     * @return type
     */
    public function replaceAndGetTemplateContent($variablesToBeReplaced=null) {
        $this->replaceTemplatePlaceHolders($variablesToBeReplaced);
        return $this->templateContent;
    }

    

}
