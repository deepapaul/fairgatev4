<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgGmItemI18n
 */
class FgGmItemI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $descriptionLang;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmItems
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgGmItemI18n
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
     * Set descriptionLang
     *
     * @param string $descriptionLang
     *
     * @return FgGmItemI18n
     */
    public function setDescriptionLang($descriptionLang)
    {
        $this->descriptionLang = $descriptionLang;

        return $this;
    }

    /**
     * Get descriptionLang
     *
     * @return string
     */
    public function getDescriptionLang()
    {
        return $this->descriptionLang;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return FgGmItemI18n
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

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgGmItems $id
     *
     * @return FgGmItemI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgGmItems $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgGmItems
     */
    public function getId()
    {
        return $this->id;
    }
}

