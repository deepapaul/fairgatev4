<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsNavigationI18n
 */
class FgCmsNavigationI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsNavigation
     */
    private $id;

    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmsNavigationI18n
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
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmsNavigation $id
     * @return FgCmsNavigationI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsNavigation $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsNavigation 
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string
     */
    private $titleLang;


    /**
     * Set titleLang
     *
     * @param string $titleLang
     * @return FgCmsNavigationI18n
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
}