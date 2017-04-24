<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnNewsletterSidebar
 */
class FgCnNewsletterSidebar
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
     * @var \Common\UtilityBundle\Entity\FgSmAdArea
     */
    private $sponsorAdArea;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;


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
     *
     * @return FgCnNewsletterSidebar
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
     *
     * @return FgCnNewsletterSidebar
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
     * Set sponsorAdArea
     *
     * @param \Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea
     *
     * @return FgCnNewsletterSidebar
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
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     *
     * @return FgCnNewsletterSidebar
     */
    public function setNewsletter(\Common\UtilityBundle\Entity\FgCnNewsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
}

