<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubI18n
 */
class FgClubI18n
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
     * @var \Admin\UtilityBundle\Entity\FgClub
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgClubI18n
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
     * @return FgClubI18n
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
     * @return FgClubI18n
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
     * @param \Admin\UtilityBundle\Entity\FgClub $id
     * @return FgClubI18n
     */
    public function setId(\Admin\UtilityBundle\Entity\FgClub $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Admin\UtilityBundle\Entity\FgClub
     */
    public function getId()
    {
        return $this->id;
    }
}
