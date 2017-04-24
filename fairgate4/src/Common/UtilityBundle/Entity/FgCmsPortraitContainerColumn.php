<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPortraitContainerColumn
 */
class FgCmsPortraitContainerColumn
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
     * @var \Common\UtilityBundle\Entity\FgCmsPortraitContainer
     */
    private $container;


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
     *
     * @return FgCmsPortraitContainerColumn
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
     *
     * @return FgCmsPortraitContainerColumn
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
     * Set container
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPortraitContainer $container
     *
     * @return FgCmsPortraitContainerColumn
     */
    public function setContainer(\Common\UtilityBundle\Entity\FgCmsPortraitContainer $container = null)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get container
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPortraitContainer
     */
    public function getContainer()
    {
        return $this->container;
    }
}

