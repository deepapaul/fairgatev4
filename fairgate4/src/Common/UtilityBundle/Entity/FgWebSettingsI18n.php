<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgWebSettingsI18n
 */
class FgWebSettingsI18n
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $descriptionLang;

    /**
     * @var string
     */
    private $lang;

    /**
     * @var \Common\UtilityBundle\Entity\FgWebSettings
     */
    private $settings;


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
     * Set descriptionLang
     *
     * @param string $descriptionLang
     * @return FgWebSettingsI18n
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
     * Set remove
     *
     * @param integer $remove
     * @return FgWebSettingsI18n
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    
        return $this;
    }

    /**
     * Get remove
     *
     * @return integer 
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set settings
     *
     * @param \Common\UtilityBundle\Entity\FgWebSettings $settings
     * @return FgWebSettingsI18n
     */
    public function setSettings(\Common\UtilityBundle\Entity\FgWebSettings $settings = null)
    {
        $this->settings = $settings;
    
        return $this;
    }

    /**
     * Get settings
     *
     * @return \Common\UtilityBundle\Entity\FgWebSettings 
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
