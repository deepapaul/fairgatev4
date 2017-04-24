<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmAttributeI18n
 */
class FgCmAttributeI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $fieldnameLang;

    /**
     * @var string
     */
    private $fieldnameShortLang;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgCmAttributeI18n
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
     * Set fieldnameLang
     *
     * @param string $fieldnameLang
     *
     * @return FgCmAttributeI18n
     */
    public function setFieldnameLang($fieldnameLang)
    {
        $this->fieldnameLang = $fieldnameLang;

        return $this;
    }

    /**
     * Get fieldnameLang
     *
     * @return string
     */
    public function getFieldnameLang()
    {
        return $this->fieldnameLang;
    }

    /**
     * Set fieldnameShortLang
     *
     * @param string $fieldnameShortLang
     *
     * @return FgCmAttributeI18n
     */
    public function setFieldnameShortLang($fieldnameShortLang)
    {
        $this->fieldnameShortLang = $fieldnameShortLang;

        return $this;
    }

    /**
     * Get fieldnameShortLang
     *
     * @return string
     */
    public function getFieldnameShortLang()
    {
        return $this->fieldnameShortLang;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     *
     * @return FgCmAttributeI18n
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
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $id
     *
     * @return FgCmAttributeI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmAttribute $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getId()
    {
        return $this->id;
    }
}

