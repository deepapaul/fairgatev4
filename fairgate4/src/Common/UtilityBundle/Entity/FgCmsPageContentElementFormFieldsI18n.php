<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentElementFormFieldsI18n
 */
class FgCmsPageContentElementFormFieldsI18n
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
    private $predefinedValueLang;

    /**
     * @var string
     */
    private $placeholderValueLang;

    /**
     * @var string
     */
    private $tooltipValueLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmsPageContentElementFormFieldsI18n
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
     * @return FgCmsPageContentElementFormFieldsI18n
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
     * Set predefinedValueLang
     *
     * @param string $predefinedValueLang
     * @return FgCmsPageContentElementFormFieldsI18n
     */
    public function setPredefinedValueLang($predefinedValueLang)
    {
        $this->predefinedValueLang = $predefinedValueLang;
    
        return $this;
    }

    /**
     * Get predefinedValueLang
     *
     * @return string 
     */
    public function getPredefinedValueLang()
    {
        return $this->predefinedValueLang;
    }

    /**
     * Set placeholderLang
     *
     * @param string $placeholderValueLang
     * @return FgCmsPageContentElementFormFieldsI18n
     */
    public function setPlaceholderValueLang($placeholderValueLang)
    {
        $this->placeholderValueLang = $placeholderValueLang;
    
        return $this;
    }

    /**
     * Get placeholderLang
     *
     * @return string 
     */
    public function getPlaceholderValueLang()
    {
        return $this->placeholderValueLang;
    }

    /**
     * Set tooltipValueLang
     *
     * @param string $tooltipValueLang
     * @return FgCmsPageContentElementFormFieldsI18n
     */
    public function setTooltipValueLang($tooltipValueLang)
    {
        $this->tooltipValueLang = $tooltipValueLang;
    
        return $this;
    }

    /**
     * Get tooltipValueLang
     *
     * @return string 
     */
    public function getTooltipValueLang()
    {
        return $this->tooltipValueLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields $id
     * @return FgCmsPageContentElementFormFieldsI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElementFormFields 
     */
    public function getId()
    {
        return $this->id;
    }
}
