<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubClassificationI18n
 */
class FgClubClassificationI18n
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
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Admin\UtilityBundle\Entity\FgClubClassification
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgClubClassificationI18n
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
     * @return FgClubClassificationI18n
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
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgClubClassificationI18n
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
     * @param \Admin\UtilityBundle\Entity\FgClubClassification $id
     * @return FgClubClassificationI18n
     */
    public function setId(\Admin\UtilityBundle\Entity\FgClubClassification $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Admin\UtilityBundle\Entity\FgClubClassification
     */
    public function getId()
    {
        return $this->id;
    }
}
