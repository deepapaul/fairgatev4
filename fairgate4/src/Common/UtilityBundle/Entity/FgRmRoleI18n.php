<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgRmRoleI18n
 */
class FgRmRoleI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $titleLang;

    /**
     * @var string
     */
    private $descriptionLang;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmRole
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgRmRoleI18n
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
     * Set titleLang
     *
     * @param string $titleLang
     *
     * @return FgRmRoleI18n
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
     * Set descriptionLang
     *
     * @param string $descriptionLang
     *
     * @return FgRmRoleI18n
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
     * @return FgRmRoleI18n
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
     * @param \Common\UtilityBundle\Entity\FgRmRole $id
     *
     * @return FgRmRoleI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgRmRole $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgRmRole
     */
    public function getId()
    {
        return $this->id;
    }
}

