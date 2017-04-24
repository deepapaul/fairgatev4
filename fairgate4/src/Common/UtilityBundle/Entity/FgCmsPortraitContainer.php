<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPortraitContainer
 */
class FgCmsPortraitContainer
{

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsContactTable
     */
    private $portrait;

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
     * Set size
     *
     * @param integer $size
     * @return FgCmsPortraitContainer
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer 
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return FgCmsPortraitContainer
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
     * Set portrait
     *
     * @param \Common\UtilityBundle\Entity\FgCmsContactTable $portrait
     * @return FgCmsPortraitContainer
     */
    public function setPortrait(\Common\UtilityBundle\Entity\FgCmsContactTable $portrait = null)
    {
        $this->portrait = $portrait;

        return $this;
    }

    /**
     * Get portrait
     *
     * @return \Common\UtilityBundle\Entity\FgCmsContactTable 
     */
    public function getPortrait()
    {
        return $this->portrait;
    }
}
