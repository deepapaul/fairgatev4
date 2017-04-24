<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmAttributesetI18n
 */
class FgCmAttributesetI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $title;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttributeset
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmAttributesetI18n
     */
    public function setLang($lang)
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Get lang
     *
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return FgCmAttributesetI18n
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttributeset $id
     * @return FgCmAttributesetI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmAttributeset $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttributeset
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $titleLang;


    /**
     * Set titleLang
     *
     * @param string $titleLang
     * @return FgCmAttributesetI18n
     */
    public function setTitleLang($titleLang)
    {
        $this->titleLang = $titleLang;

        return $this;
    }

    /**
     * Get titleLang
     *
     * @return string
     */
    public function getTitleLang()
    {
        return $this->titleLang;
    }
    /**
     * @var boolean
     */
    private $isActive;


    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmAttributesetI18n
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }
}
