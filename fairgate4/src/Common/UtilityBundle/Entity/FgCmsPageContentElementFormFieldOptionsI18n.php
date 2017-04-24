<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentElementFormFieldOptionsI18n
 */
class FgCmsPageContentElementFormFieldOptionsI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $selectionValueNameLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFieldOptions
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     *
     * @return FgCmsPageContentElementFormFieldOptionsI18n
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
     * Set selectionValueNameLang
     *
     * @param string $selectionValueNameLang
     *
     * @return FgCmsPageContentElementFormFieldOptionsI18n
     */
    public function setSelectionValueNameLang($selectionValueNameLang)
    {
        $this->selectionValueNameLang = $selectionValueNameLang;

        return $this;
    }

    /**
     * Get selectionValueNameLang
     *
     * @return string
     */
    public function getSelectionValueNameLang()
    {
        return $this->selectionValueNameLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFieldOptions $id
     *
     * @return FgCmsPageContentElementFormFieldOptionsI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsPageContentElementFormFieldOptions $id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFieldOptions
     */
    public function getId()
    {
        return $this->id;
    }
}

