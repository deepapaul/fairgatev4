<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgEmCalendarDetailsI18n
 */
class FgEmCalendarDetailsI18n
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
    private $descLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgEmCalendarDetails
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgEmCalendarDetailsI18n
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
     * @return FgEmCalendarDetailsI18n
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
     * Set descLang
     *
     * @param string $descLang
     * @return FgEmCalendarDetailsI18n
     */
    public function setDescLang($descLang)
    {
        $this->descLang = $descLang;
    
        return $this;
    }

    /**
     * Get descLang
     *
     * @return string 
     */
    public function getDescLang()
    {
        return $this->descLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgEmCalendarDetails $id
     * @return FgEmCalendarDetailsI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgEmCalendarDetails $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgEmCalendarDetails 
     */
    public function getId()
    {
        return $this->id;
    }
}
