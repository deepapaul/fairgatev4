<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsArticleTextI18n
 */
class FgCmsArticleTextI18n
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
     * @var string
     */
    private $teaserLang;

    /**
     * @var string
     */
    private $textLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsArticleText
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmsArticleTextI18n
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
     * @return FgCmsArticleTextI18n
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
     * Set teaserLang
     *
     * @param string $teaserLang
     * @return FgCmsArticleTextI18n
     */
    public function setTeaserLang($teaserLang)
    {
        $this->teaserLang = $teaserLang;
    
        return $this;
    }

    /**
     * Get teaserLang
     *
     * @return string 
     */
    public function getTeaserLang()
    {
        return $this->teaserLang;
    }

    /**
     * Set textLang
     *
     * @param string $textLang
     * @return FgCmsArticleTextI18n
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
     * @param \Common\UtilityBundle\Entity\FgCmsArticleText $id
     * @return FgCmsArticleTextI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsArticleText $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsArticleText 
     */
    public function getId()
    {
        return $this->id;
    }
}
