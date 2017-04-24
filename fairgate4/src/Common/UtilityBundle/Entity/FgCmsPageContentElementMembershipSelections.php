<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentElementMembershipSelections
 */
class FgCmsPageContentElementMembershipSelections
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields
     */
    private $field;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $membership;


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
     * Set field
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields $field
     *
     * @return FgCmsPageContentElementMembershipSelections
     */
    public function setField(\Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields $field = null)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set membership
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $membership
     *
     * @return FgCmsPageContentElementMembershipSelections
     */
    public function setMembership(\Common\UtilityBundle\Entity\FgCmMembership $membership = null)
    {
        $this->membership = $membership;

        return $this;
    }

    /**
     * Get membership
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getMembership()
    {
        return $this->membership;
    }
}

