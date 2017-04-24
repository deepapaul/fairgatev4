<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgClubLogNotes
 */
class FgClubLogNotes
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
    private $type;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $noteClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $noteContact;

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
     * @return FgClubLogNotes
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
     * Set type
     *
     * @param string $type
     *
     * @return FgClubLogNotes
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
     * Set valueBefore
     *
     * @param string $valueBefore
     *
     * @return FgClubLogNotes
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
     * @return FgClubLogNotes
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
     * Set noteClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $noteClub
     *
     * @return FgClubLogNotes
     */
    public function setNoteClub(\Common\UtilityBundle\Entity\FgClub $noteClub = null)
    {
        $this->noteClub = $noteClub;

        return $this;
    }

    /**
     * Get noteClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getNoteClub()
    {
        return $this->noteClub;
    }

    /**
     * Set noteContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $noteContact
     *
     * @return FgClubLogNotes
     */
    public function setNoteContact(\Common\UtilityBundle\Entity\FgCmContact $noteContact = null)
    {
        $this->noteContact = $noteContact;

        return $this;
    }

    /**
     * Get noteContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getNoteContact()
    {
        return $this->noteContact;
    }

    /**
     * Set assignedClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $assignedClub
     *
     * @return FgClubLogNotes
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
     * @return FgClubLogNotes
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

