<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgApiGotcourtsLog
 */
class FgApiGotcourtsLog
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
    private $field;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgApiGotcourts
     */
    private $gotcourt;

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
     * @return FgApiGotcourtsLog
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
     * Set field
     *
     * @param string $field
     * @return FgApiGotcourtsLog
     */
    public function setField($field)
    {
        $this->field = $field;
    
        return $this;
    }

    /**
     * Get field
     *
     * @return string 
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set valueAfter
     *
     * @param string $valueAfter
     * @return FgApiGotcourtsLog
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
     * Set valueBefore
     *
     * @param string $valueBefore
     * @return FgApiGotcourtsLog
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
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgApiGotcourtsLog
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
     * Set gotcourt
     *
     * @param \Common\UtilityBundle\Entity\FgApiGotcourts $gotcourt
     * @return FgApiGotcourtsLog
     */
    public function setGotcourt(\Common\UtilityBundle\Entity\FgApiGotcourts $gotcourt = null)
    {
        $this->gotcourt = $gotcourt;
    
        return $this;
    }

    /**
     * Get gotcourt
     *
     * @return \Common\UtilityBundle\Entity\FgApiGotcourts 
     */
    public function getGotcourt()
    {
        return $this->gotcourt;
    }

    /**
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     * @return FgApiGotcourtsLog
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
