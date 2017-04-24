<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsPageContentElementI18n
 */
class FgCmsPageContentElementI18n
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
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmsPageContentElementI18n
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
     * @return FgCmsPageContentElementI18n
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
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $id
     * @return FgCmsPageContentElementI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsPageContentElement $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElement 
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $twitterAccountnameLang;


    /**
     * Set twitterAccountnameLang
     *
     * @param string $twitterAccountnameLang
     * @return FgCmsPageContentElementI18n
     */
    public function setTwitterAccountnameLang($twitterAccountnameLang)
    {
        $this->twitterAccountnameLang = $twitterAccountnameLang;
    
        return $this;
}

    /**
     * Get twitterAccountnameLang
     *
     * @return string 
     */
    public function getTwitterAccountnameLang()
    {
        return $this->twitterAccountnameLang;
    }
}
