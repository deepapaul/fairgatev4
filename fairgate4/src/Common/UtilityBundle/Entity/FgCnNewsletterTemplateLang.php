<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterTemplateLang
 */
class FgCnNewsletterTemplateLang
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $languageCode;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterTemplate
     */
    private $template;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set languageCode
     *
     * @param string $languageCode
     * @return FgCnNewsletterTemplateLang
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;
    
        return $this;
    }

    /**
     * Get languageCode
     *
     * @return string 
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }

    /**
     * Set template
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterTemplate $template
     * @return FgCnNewsletterTemplateLang
     */
    public function setTemplate(\Common\UtilityBundle\Entity\FgCnNewsletterTemplate $template = null)
    {
        $this->template = $template;
    
        return $this;
    }

    /**
     * Get template
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterTemplate 
     */
    public function getTemplate()
    {
        return $this->template;
    }
}