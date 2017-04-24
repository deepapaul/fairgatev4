<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmLinkedcontact
 */
class FgCmLinkedcontact
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $linkedContactId;

    /**
     * @var string
     */
    private $relation;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmRelation
     */
    private $relation2;


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
     * Set linkedContactId
     *
     * @param integer $linkedContactId
     * @return FgCmLinkedcontact
     */
    public function setLinkedContactId($linkedContactId)
    {
        $this->linkedContactId = $linkedContactId;

        return $this;
    }

    /**
     * Get linkedContactId
     *
     * @return integer
     */
    public function getLinkedContactId()
    {
        return $this->linkedContactId;
    }

    /**
     * Set relation
     *
     * @param string $relation
     * @return FgCmLinkedcontact
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get relation
     *
     * @return string
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return FgCmLinkedcontact
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgCmLinkedcontact
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

    /**
     * Set relation2
     *
     * @param \Common\UtilityBundle\Entity\FgCmRelation $relation2
     * @return FgCmLinkedcontact
     */
    public function setRelation2(\Common\UtilityBundle\Entity\FgCmRelation $relation2 = null)
    {
        $this->relation2 = $relation2;

        return $this;
    }

    /**
     * Get relation2
     *
     * @return \Common\UtilityBundle\Entity\FgCmRelation
     */
    public function getRelation2()
    {
        return $this->relation2;
    }
    /**
     * @var integer
     */
    private $clubId;


    /**
     * Set clubId
     *
     * @param integer $clubId
     * @return FgCmLinkedcontact
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;

        return $this;
    }

    /**
     * Get clubId
     *
     * @return integer
     */
    public function getClubId()
    {
        return $this->clubId;
    }
}