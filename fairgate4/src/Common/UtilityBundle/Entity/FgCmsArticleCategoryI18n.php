<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsArticleCategoryI18n
 */
class FgCmsArticleCategoryI18n
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
     * @var \Common\UtilityBundle\Entity\FgCmsArticleCategory
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmsArticleCategoryI18n
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
     * @return FgCmsArticleCategoryI18n
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
     * @param \Common\UtilityBundle\Entity\FgCmsArticleCategory $id
     * @return FgCmsArticleCategoryI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsArticleCategory $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsArticleCategory 
     */
    public function getId()
    {
        return $this->id;
    }
}
