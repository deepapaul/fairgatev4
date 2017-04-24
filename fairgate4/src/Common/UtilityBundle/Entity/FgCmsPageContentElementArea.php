<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentElementArea
 */
class FgCmsPageContentElementArea
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
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;


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
     *
     * @return FgCmsPageContentElementArea
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
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgCmsPageContentElementArea
     */
    public function setRole(\Common\UtilityBundle\Entity\FgRmRole $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getRole()
    {
        return $this->role;
    }
}

