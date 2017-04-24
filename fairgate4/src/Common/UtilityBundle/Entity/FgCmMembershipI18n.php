<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmMembershipI18n
 */
class FgCmMembershipI18n
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
     * @var \Common\UtilityBundle\Entity\FgCmMembership
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmMembershipI18n
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
     * @return FgCmMembershipI18n
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
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmMembership $id
     * @return FgCmMembershipI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmMembership $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmMembership
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var boolean
     */
    private $isActive;


    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgCmMembershipI18n
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
