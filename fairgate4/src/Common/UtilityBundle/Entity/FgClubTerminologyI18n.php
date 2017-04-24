<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubTerminologyI18n
 */
class FgClubTerminologyI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $singularLang;

    /**
     * @var string
     */
    private $pluralLang;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubTerminology
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgClubTerminologyI18n
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
     * Set singularLang
     *
     * @param string $singularLang
     * @return FgClubTerminologyI18n
     */
    public function setSingularLang($singularLang)
    {
        $this->singularLang = $singularLang;

        return $this;
    }

    /**
     * Get singularLang
     *
     * @return string
     */
    public function getSingularLang()
    {
        return $this->singularLang;
    }

    /**
     * Set pluralLang
     *
     * @param string $pluralLang
     * @return FgClubTerminologyI18n
     */
    public function setPluralLang($pluralLang)
    {
        $this->pluralLang = $pluralLang;

        return $this;
    }

    /**
     * Get pluralLang
     *
     * @return string
     */
    public function getPluralLang()
    {
        return $this->pluralLang;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgClubTerminologyI18n
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
     * @param \Common\UtilityBundle\Entity\FgClubTerminology $id
     * @return FgClubTerminologyI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgClubTerminology $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgClubTerminology
     */
    public function getId()
    {
        return $this->id;
    }
}
