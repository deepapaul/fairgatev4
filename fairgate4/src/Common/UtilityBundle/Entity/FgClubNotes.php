<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubNotes
 */
class FgClubNotes
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $editedOn;

    /**
     * @var string
     */
    private $note;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdByContact;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $createdByClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $editedByClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $editedByContact;


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
     * Set editedOn
     *
     * @param \DateTime $editedOn
     * @return FgClubNotes
     */
    public function setEditedOn($editedOn)
    {
        $this->editedOn = $editedOn;

        return $this;
    }

    /**
     * Get editedOn
     *
     * @return \DateTime
     */
    public function getEditedOn()
    {
        return $this->editedOn;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return FgClubNotes
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return FgClubNotes
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgClubNotes
     */
    public function setClub(\Common\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set createdByContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdByContact
     * @return FgClubNotes
     */
    public function setCreatedByContact(\Common\UtilityBundle\Entity\FgCmContact $createdByContact = null)
    {
        $this->createdByContact = $createdByContact;

        return $this;
    }

    /**
     * Get createdByContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getCreatedByContact()
    {
        return $this->createdByContact;
    }

    /**
     * Set createdByClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $createdByClub
     * @return FgClubNotes
     */
    public function setCreatedByClub(\Common\UtilityBundle\Entity\FgClub $createdByClub = null)
    {
        $this->createdByClub = $createdByClub;

        return $this;
    }

    /**
     * Get createdByClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getCreatedByClub()
    {
        return $this->createdByClub;
    }

    /**
     * Set editedByClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $editedByClub
     * @return FgClubNotes
     */
    public function setEditedByClub(\Common\UtilityBundle\Entity\FgClub $editedByClub = null)
    {
        $this->editedByClub = $editedByClub;

        return $this;
    }

    /**
     * Get editedByClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getEditedByClub()
    {
        return $this->editedByClub;
    }

    /**
     * Set editedByContact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $editedByContact
     * @return FgClubNotes
     */
    public function setEditedByContact(\Common\UtilityBundle\Entity\FgCmContact $editedByContact = null)
    {
        $this->editedByContact = $editedByContact;

        return $this;
    }

    /**
     * Get editedByContact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getEditedByContact()
    {
        return $this->editedByContact;
    }
}
