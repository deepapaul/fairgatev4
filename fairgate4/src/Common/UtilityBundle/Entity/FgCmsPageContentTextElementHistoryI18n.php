<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentTextElementHistoryI18n
 */
class FgCmsPageContentTextElementHistoryI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $textLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgCmsPageContentTextElementHistoryI18n
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
     * Set textLang
     *
     * @param string $textLang
     *
     * @return FgCmsPageContentTextElementHistoryI18n
     */
    public function setTextLang($textLang)
    {
        $this->textLang = $textLang;

        return $this;
    }

    /**
     * Get textLang
     *
     * @return string
     */
    public function getTextLang()
    {
        return $this->textLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory $id
     *
     * @return FgCmsPageContentTextElementHistoryI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory
     */
    public function getId()
    {
        return $this->id;
    }
}

