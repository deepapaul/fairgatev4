<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnSubscriberLog
 */
class FgCnSubscriberLog
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
    private $kind;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnSubscriber
     */
    private $subscriber;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

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
     * @return FgCnSubscriberLog
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
     * Set kind
     *
     * @param string $kind
     * @return FgCnSubscriberLog
     */
    public function setKind($kind)
    {
        $this->kind = $kind;
    
        return $this;
    }

    /**
     * Get kind
     *
     * @return string 
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Set field
     *
     * @param string $field
     * @return FgCnSubscriberLog
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
     * Set valueBefore
     *
     * @param string $valueBefore
     * @return FgCnSubscriberLog
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
     * @return FgCnSubscriberLog
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
     * Set subscriber
     *
     * @param \Common\UtilityBundle\Entity\FgCnSubscriber $subscriber
     * @return FgCnSubscriberLog
     */
    public function setSubscriber(\Common\UtilityBundle\Entity\FgCnSubscriber $subscriber = null)
    {
        $this->subscriber = $subscriber;
    
        return $this;
    }

    /**
     * Get subscriber
     *
     * @return \Common\UtilityBundle\Entity\FgCnSubscriber 
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnSubscriberLog
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
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     * @return FgCnSubscriberLog
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
    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;


    /**
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     * @return FgCnSubscriberLog
     */
    public function setNewsletter(\Common\UtilityBundle\Entity\FgCnNewsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;
    
        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletter 
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }
}