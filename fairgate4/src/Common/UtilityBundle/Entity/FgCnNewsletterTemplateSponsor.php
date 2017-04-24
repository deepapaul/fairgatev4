<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterTemplateSponsor
 */
class FgCnNewsletterTemplateSponsor
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var string
     */
    private $position;

    /**
     * @var string
     */
    private $sponsorAdWidth;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmAdArea
     */
    private $sponsorAdArea;

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
     * Set title
     *
     * @param string $title
     * @return FgCnNewsletterTemplateSponsor
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCnNewsletterTemplateSponsor
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    
        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return FgCnNewsletterTemplateSponsor
     */
    public function setPosition($position)
    {
        $this->position = $position;
    
        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set sponsorAdWidth
     *
     * @param string $sponsorAdWidth
     * @return FgCnNewsletterTemplateSponsor
     */
    public function setSponsorAdWidth($sponsorAdWidth)
    {
        $this->sponsorAdWidth = $sponsorAdWidth;
    
        return $this;
    }

    /**
     * Get sponsorAdWidth
     *
     * @return string 
     */
    public function getSponsorAdWidth()
    {
        return $this->sponsorAdWidth;
    }

    /**
     * Set sponsorAdArea
     *
     * @param \Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea
     * @return FgCnNewsletterTemplateSponsor
     */
    public function setSponsorAdArea(\Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea = null)
    {
        $this->sponsorAdArea = $sponsorAdArea;
    
        return $this;
    }

    /**
     * Get sponsorAdArea
     *
     * @return \Common\UtilityBundle\Entity\FgSmAdArea 
     */
    public function getSponsorAdArea()
    {
        return $this->sponsorAdArea;
    }

    /**
     * Set template
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterTemplate $template
     * @return FgCnNewsletterTemplateSponsor
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