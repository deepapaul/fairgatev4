<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgMessage
 */
class FgMessage
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $answerTo;

    /**
     * @var string
     */
    private $messageType;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * Set answerTo
     *
     * @param string $answerTo
     * @return FgMessage
     */
    public function setAnswerTo($answerTo)
    {
        $this->answerTo = $answerTo;

        return $this;
    }

    /**
     * Get answerTo
     *
     * @return string
     */
    public function getAnswerTo()
    {
        return $this->answerTo;
    }

    /**
     * Set messageType
     *
     * @param string $messageType
     * @return FgMessage
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * Get messageType
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgMessage
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
     * @var string
     */
    private $senderEmail;

    /**
     * @var string
     */
    private $groupType;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var integer
     */
    private $step;

    /**
     * @var integer
     */
    private $isDraft;

    /**
     * @var integer
     */
    private $parentId;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updateBy;


    /**
     * Set senderEmail
     *
     * @param string $senderEmail
     * @return FgMessage
     */
    public function setSenderEmail($senderEmail)
    {
        $this->senderEmail = $senderEmail;
    
        return $this;
    }

    /**
     * Get senderEmail
     *
     * @return string 
     */
    public function getSenderEmail()
    {
        return $this->senderEmail;
    }

    /**
     * Set groupType
     *
     * @param string $groupType
     * @return FgMessage
     */
    public function setGroupType($groupType)
    {
        $this->groupType = $groupType;
    
        return $this;
    }

    /**
     * Get groupType
     *
     * @return string 
     */
    public function getGroupType()
    {
        return $this->groupType;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return FgMessage
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    
        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set step
     *
     * @param integer $step
     * @return FgMessage
     */
    public function setStep($step)
    {
        $this->step = $step;
    
        return $this;
    }

    /**
     * Get step
     *
     * @return integer 
     */
    public function getStep()
    {
        return $this->step;
    }

    /**
     * Set isDraft
     *
     * @param integer $isDraft
     * @return FgMessage
     */
    public function setIsDraft($isDraft)
    {
        $this->isDraft = $isDraft;
    
        return $this;
    }

    /**
     * Get isDraft
     *
     * @return integer 
     */
    public function getIsDraft()
    {
        return $this->isDraft;
    }

    /**
     * Set parentId
     *
     * @param integer $parentId
     * @return FgMessage
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    
        return $this;
    }

    /**
     * Get parentId
     *
     * @return integer 
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return FgMessage
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return FgMessage
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    
        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime 
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     * @return FgMessage
     */
    public function setCreatedBy(\Common\UtilityBundle\Entity\FgCmContact $createdBy = null)
    {
        $this->createdBy = $createdBy;
    
        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updateBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updateBy
     * @return FgMessage
     */
    public function setUpdateBy(\Common\UtilityBundle\Entity\FgCmContact $updateBy = null)
    {
        $this->updateBy = $updateBy;
    
        return $this;
    }

    /**
     * Get updateBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getUpdateBy()
    {
        return $this->updateBy;
    }
}