<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmRoleManualContacts
 */
class FgRmRoleManualContacts
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


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
     * Set type
     *
     * @param string $type
     *
     * @return FgRmRoleManualContacts
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;

    }


    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;

    }


    /**
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     *
     * @return FgRmRoleManualContacts
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


    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgRmRoleManualContacts
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;

    }


    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;

    }


}