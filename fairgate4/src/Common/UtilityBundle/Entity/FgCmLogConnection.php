<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmLogConnection
 */
class FgCmLogConnection
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $connectionType;

    /**
     * @var string
     */
    private $relation;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $linkedContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $assignedClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;


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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FgCmLogConnection
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set connectionType
     *
     * @param string $connectionType
     *
     * @return FgCmLogConnection
     */
    public function setConnectionType($connectionType)
    {
        $this->connectionType = $connectionType;

        return $this;
    }

    /**
     * Get connectionType
     *
     * @return string
     */
    public function getConnectionType()
    {
        return $this->connectionType;
    }

    /**
     * Set relation
     *
     * @param string $relation
     *
     * @return FgCmLogConnection
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
     * Set valueBefore
     *
     * @param string $valueBefore
     *
     * @return FgCmLogConnection
     */
    public function setValueBefore($valueBefore)
    {
        $this->valueBefore = $valueBefore;

        return $this;
    }

    /**
     * Get valueBefore
     *
     * @return string
     */
    public function getValueBefore()
    {
        return $this->valueBefore;
    }

    /**
     * Set valueAfter
     *
     * @param string $valueAfter
     *
     * @return FgCmLogConnection
     */
    public function setValueAfter($valueAfter)
    {
        $this->valueAfter = $valueAfter;

        return $this;
    }

    /**
     * Get valueAfter
     *
     * @return string
     */
    public function getValueAfter()
    {
        return $this->valueAfter;
    }

    /**
     * Set linkedContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $linkedContact
     *
     * @return FgCmLogConnection
     */
    public function setLinkedContact(\Common\UtilityBundle\Entity\FgCmContact $linkedContact = null)
    {
        $this->linkedContact = $linkedContact;

        return $this;
    }

    /**
     * Get linkedContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getLinkedContact()
    {
        return $this->linkedContact;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgCmLogConnection
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
     * Set assignedClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $assignedClub
     *
     * @return FgCmLogConnection
     */
    public function setAssignedClub(\Common\UtilityBundle\Entity\FgClub $assignedClub = null)
    {
        $this->assignedClub = $assignedClub;

        return $this;
    }

    /**
     * Get assignedClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getAssignedClub()
    {
        return $this->assignedClub;
    }

    /**
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgCmLogConnection
     */
    public function setChangedBy(\Common\UtilityBundle\Entity\FgCmContact $changedBy = null)
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    /**
     * Get changedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getChangedBy()
    {
        return $this->changedBy;
    }
}

