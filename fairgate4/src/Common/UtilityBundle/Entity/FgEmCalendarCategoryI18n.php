<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgEmCalendarCategoryI18n
 */
class FgEmCalendarCategoryI18n
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
     * @var \Common\UtilityBundle\Entity\FgEmCalendarCategory
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgEmCalendarCategoryI18n
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
     * @return FgEmCalendarCategoryI18n
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
     * @param \Common\UtilityBundle\Entity\FgEmCalendarCategory $id
     *
     * @return FgEmCalendarCategoryI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgEmCalendarCategory $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarCategory
     */
    public function getId()
    {
        return $this->id;
    }
}

