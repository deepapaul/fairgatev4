<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmCategoryI18n
 */
class FgRmCategoryI18n
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
     * @var \Common\UtilityBundle\Entity\FgRmCategory
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgRmCategoryI18n
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
     * @return FgRmCategoryI18n
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
     *
     * @return FgRmCategoryI18n
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
     * @param \Common\UtilityBundle\Entity\FgRmCategory $id
     *
     * @return FgRmCategoryI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgRmCategory $id = null)
    {
        $this->id = $id;

        return $this;

    }


    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgRmCategory
     */
    public function getId()
    {
        return $this->id;

    }


}
