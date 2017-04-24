<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgForumTopicData
 */
class FgForumTopicData
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $postContent;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var integer
     */
    private $uniquePostId;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $updatedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $createdBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgForumTopic
     */
    private $forumTopic;


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
     * Set postContent
     *
     * @param string $postContent
     *
     * @return FgForumTopicData
     */
    public function setPostContent($postContent)
    {
        $this->postContent = $postContent;

        return $this;
    }

    /**
     * Get postContent
     *
     * @return string
     */
    public function getPostContent()
    {
        return $this->postContent;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return FgForumTopicData
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
     *
     * @return FgForumTopicData
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
     * Set uniquePostId
     *
     * @param integer $uniquePostId
     *
     * @return FgForumTopicData
     */
    public function setUniquePostId($uniquePostId)
    {
        $this->uniquePostId = $uniquePostId;

        return $this;
    }

    /**
     * Get uniquePostId
     *
     * @return integer
     */
    public function getUniquePostId()
    {
        return $this->uniquePostId;
    }

    /**
     * Set updatedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $updatedBy
     *
     * @return FgForumTopicData
     */
    public function setUpdatedBy(\Common\UtilityBundle\Entity\FgCmContact $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }

    /**
     * Set createdBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $createdBy
     *
     * @return FgForumTopicData
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
     * Set forumTopic
     *
     * @param \Common\UtilityBundle\Entity\FgForumTopic $forumTopic
     *
     * @return FgForumTopicData
     */
    public function setForumTopic(\Common\UtilityBundle\Entity\FgForumTopic $forumTopic = null)
    {
        $this->forumTopic = $forumTopic;

        return $this;
    }

    /**
     * Get forumTopic
     *
     * @return \Common\UtilityBundle\Entity\FgForumTopic
     */
    public function getForumTopic()
    {
        return $this->forumTopic;
    }
}

