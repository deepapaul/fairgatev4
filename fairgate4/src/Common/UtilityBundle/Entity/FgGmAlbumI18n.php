<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgGmAlbumI18n
 */
class FgGmAlbumI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $nameLang;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var \Common\UtilityBundle\Entity\FgGmAlbum
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgGmAlbumI18n
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
     * Set nameLang
     *
     * @param string $nameLang
     * @return FgGmAlbumI18n
     */
    public function setNameLang($nameLang)
    {
        $this->nameLang = $nameLang;
    
        return $this;
    }

    /**
     * Get nameLang
     *
     * @return string 
     */
    public function getNameLang()
    {
        return $this->nameLang;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return FgGmAlbumI18n
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    
        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgGmAlbum $id
     * @return FgGmAlbumI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgGmAlbum $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgGmAlbum 
     */
    public function getId()
    {
        return $this->id;
    }
}
