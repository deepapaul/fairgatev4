<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnNewsletterContentServices
 */
class FgCnNewsletterContentServices
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterContent
     */
    private $content;

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
     * Set content
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterContent $content
     *
     * @return FgCnNewsletterContentServices
     */
    public function setContent(\Common\UtilityBundle\Entity\FgCnNewsletterContent $content = null)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterContent
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set service
     *
     * @param \Common\UtilityBundle\Entity\FgSmServices $service
     *
     * @return FgCnNewsletterContentServices
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

