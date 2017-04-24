<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgClubSettingsI18n
 */
class FgClubSettingsI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $signatureLang;

    /**
     * @var string
     */
    private $logoLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgClubSettings
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgClubSettingsI18n
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
     * Set signatureLang
     *
     * @param string $signatureLang
     *
     * @return FgClubSettingsI18n
     */
    public function setSignatureLang($signatureLang)
    {
        $this->signatureLang = $signatureLang;

        return $this;
    }

    /**
     * Get signatureLang
     *
     * @return string
     */
    public function getSignatureLang()
    {
        return $this->signatureLang;
    }

    /**
     * Set logoLang
     *
     * @param string $logoLang
     *
     * @return FgClubSettingsI18n
     */
    public function setLogoLang($logoLang)
    {
        $this->logoLang = $logoLang;

        return $this;
    }

    /**
     * Get logoLang
     *
     * @return string
     */
    public function getLogoLang()
    {
        return $this->logoLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgClubSettings $id
     *
     * @return FgClubSettingsI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgClubSettings $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgClubSettings
     */
    public function getId()
    {
        return $this->id;
    }
}

