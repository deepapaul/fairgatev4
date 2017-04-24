<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgSmServicesI18n
 */
class FgSmServicesI18n
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
     * @var \Common\UtilityBundle\Entity\FgSmServices
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgSmServicesI18n
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
     * @return FgSmServicesI18n
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
     * @return FgSmServicesI18n
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
     * @return FgSmServicesI18n
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
     * @param \Common\UtilityBundle\Entity\FgSmServices $id
     *
     * @return FgSmServicesI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgSmServices $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgSmServices
     */
    public function getId()
    {
        return $this->id;
    }
}

