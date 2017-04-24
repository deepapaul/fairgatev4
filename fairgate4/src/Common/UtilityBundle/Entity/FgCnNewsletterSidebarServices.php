<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterSidebarServices
 */
class FgCnNewsletterSidebarServices
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterSidebar
     */
    private $newsletterSidebar;

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
     * Set newsletterSidebar
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterSidebar $newsletterSidebar
     * @return FgCnNewsletterSidebarServices
     */
    public function setNewsletterSidebar(\Common\UtilityBundle\Entity\FgCnNewsletterSidebar $newsletterSidebar = null)
    {
        $this->newsletterSidebar = $newsletterSidebar;
    
        return $this;
    }

    /**
     * Get newsletterSidebar
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterSidebar 
     */
    public function getNewsletterSidebar()
    {
        return $this->newsletterSidebar;
    }

    /**
     * Set service
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $service
     * @return FgCnNewsletterSidebarServices
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
