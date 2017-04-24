<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageI18n
 */
class FgCmsPageI18n
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
     * @var \Common\UtilityBundle\Entity\FgCmsPage
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgCmsPageI18n
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
     * @return FgCmsPageI18n
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
     * @param \Common\UtilityBundle\Entity\FgCmsPage $id
     *
     * @return FgCmsPageI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsPage $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPage
     */
    public function getId()
    {
        return $this->id;
    }
}

