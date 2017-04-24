<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnSmsLang
 */
class FgCnSmsLang
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $smsId;

    /**
     * @var string
     */
    private $languageCode;


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
     * Set smsId
     *
     * @param integer $smsId
     *
     * @return FgCnSmsLang
     */
    public function setSmsId($smsId)
    {
        $this->smsId = $smsId;

        return $this;
    }

    /**
     * Get smsId
     *
     * @return integer
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * Set languageCode
     *
     * @param string $languageCode
     *
     * @return FgCnSmsLang
     */
    public function setLanguageCode($languageCode)
    {
        $this->languageCode = $languageCode;

        return $this;
    }

    /**
     * Get languageCode
     *
     * @return string
     */
    public function getLanguageCode()
    {
        return $this->languageCode;
    }
}

