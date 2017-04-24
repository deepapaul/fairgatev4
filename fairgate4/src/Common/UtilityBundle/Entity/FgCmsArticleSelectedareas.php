<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsArticleSelectedareas
 */
class FgCmsArticleSelectedareas
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var boolean
     */
    private $isClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $role;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticle
     */
    private $article;


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
     * Set isClub
     *
     * @param boolean $isClub
     * @return FgCmsArticleSelectedareas
     */
    public function setIsClub($isClub)
    {
        $this->isClub = $isClub;
    
        return $this;
    }

    /**
     * Get isClub
     *
     * @return boolean 
     */
    public function getIsClub()
    {
        return $this->isClub;
    }

    /**
     * Set role
     *
     * @param \Common\UtilityBundle\Entity\FgRmRole $role
     * @return FgCmsArticleSelectedareas
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
     * Set article
     *
     * @param \Common\UtilityBundle\Entity\FgCmsArticle $article
     * @return FgCmsArticleSelectedareas
     */
    public function setArticle(\Common\UtilityBundle\Entity\FgCmsArticle $article = null)
    {
        $this->article = $article;
    
        return $this;
    }

    /**
     * Get article
     *
     * @return \Common\UtilityBundle\Entity\FgCmsArticle 
     */
    public function getArticle()
    {
        return $this->article;
    }
}
