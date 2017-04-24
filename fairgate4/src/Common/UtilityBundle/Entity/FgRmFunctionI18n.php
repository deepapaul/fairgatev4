<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgRmFunctionI18n
 */
class FgRmFunctionI18n
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
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgRmFunctionI18n
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
     * @return FgRmFunctionI18n
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
     * @return FgRmFunctionI18n
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
     * @param \Common\UtilityBundle\Entity\FgRmFunction $id
     *
     * @return FgRmFunctionI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgRmFunction $id = null)
    {
        $this->id = $id;

        return $this;

    }


    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgRmFunction
     */
    public function getId()
    {
        return $this->id;

    }


}
