<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterTemplateServices
 */
class FgCnNewsletterTemplateServices
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterTemplateSponsor
     */
    private $templateSponsor;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmServices
     */
    private $services;


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
     * Set templateSponsor
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterTemplateSponsor $templateSponsor
     * @return FgCnNewsletterTemplateServices
     */
    public function setTemplateSponsor(\Common\UtilityBundle\Entity\FgCnNewsletterTemplateSponsor $templateSponsor = null)
    {
        $this->templateSponsor = $templateSponsor;
    
        return $this;
    }

    /**
     * Get templateSponsor
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterTemplateSponsor 
     */
    public function getTemplateSponsor()
    {
        return $this->templateSponsor;
    }

    /**
     * Set services
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $services
     * @return FgCnNewsletterTemplateServices
     */
    public function setServices(\Common\UtilityBundle\Entity\FgSmServices $services = null)
    {
        $this->services = $services;
    
        return $this;
    }

    /**
     * Get services
     *
     * @return \Common\UtilityBundle\Entity\FgSmServices 
     */
    public function getServices()
    {
        return $this->services;
    }
}