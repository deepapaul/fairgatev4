<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentElementSponsorServices
 */
class FgCmsPageContentElementSponsorServices
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $element;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmServices
     */
    private $service;


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
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     * @return FgCmsPageContentElementSponsorServices
     */
    public function setElement(\Common\UtilityBundle\Entity\FgCmsPageContentElement $element = null)
    {
        $this->element = $element;
    
        return $this;
    }

    /**
     * Get element
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElement 
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Set service
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $service
     * @return FgCmsPageContentElementSponsorServices
     */
    public function setService(\Common\UtilityBundle\Entity\FgSmServices $service = null)
    {
        $this->service = $service;
    
        return $this;
    }

    /**
     * Get service
     *
     * @return \Common\UtilityBundle\Entity\FgSmServices 
     */
    public function getService()
    {
        return $this->service;
    }
}
