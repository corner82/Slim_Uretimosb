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
 * mail template abstract class
 * @author Mustafa Zeynel Dağlı
 * @since 29/06/2016
 */
abstract class AbstractMailTemplate {

    /**
     * mail template name
     * @var \Utill\Mail\Template\ContentRetrieverStrategyInterface
     */
    protected $templateContentRetriever;
    
    /**
     * mail template name
     * @var string | null
     */
    protected $templateName;
    
    /**
     * mail template content
     * @var string
     */
    protected $templateContent;

     /**
     * set mail template name
     * @param string | null $templateName
     */
    public function setTemplateName($templateName = null) {
        $this->templateName = $templateName;
    }

    /**
     * get mail template name
     * @return int | null
     */
    public function getTemplateName() {
        if(!isset($this->templateName ))  throw new Exception('mail template name not found');
        return $this->templateName;
    }
    
    /**
     * set mail template content
     * @param string | null $templateName
     */
    public function setTemplateContent(array $params = null) {
        $this->templateContent = $this->templateContentRetriever->getContent($params);
    }

    /**
     * get mail template content
     * @return int | null
     */
    public function getTemplateContent() {
        if(!isset($this->templateContent ))  throw new \Exception('mail template content not found');
        return $this->templateContent;
    }
    
    /**
     * set mail template content retriver object
     * @param string | null $templateName
     */
    public function setContentRetrieverStartegyClass(\Utill\Mail\Template\ContentRetrieverStrategyInterface $contentRetriever) {
        $this->templateContentRetriever = $contentRetriever;
    }

    /**
     * get mail template content retriver object
     * @return int | null
     */
    public function getContentRetrieverStartegyClass() {
        if(!isset($this->templateContentRetriever ))  throw new Exception('mail template content retriever object not found');
        return $this->templateContentRetriever;
    }
    
    /**
     * abstract method replace template content variables
     * @param type array || null
     * @author Mustafa Zeynel Dağlı
     * @since 10/08/2016
     */
    abstract public function replaceTemplatePlaceHolders($variablesToBeReplaced=null);
    
    /**
     * abstract method to replace and ger template content
     * @param type array || null
     * @author Mustafa Zeynel Dağlı
     * @since 10/08/2016
     */
    abstract public function replaceAndGetTemplateContent($variablesToBeReplaced=null);

    

}
