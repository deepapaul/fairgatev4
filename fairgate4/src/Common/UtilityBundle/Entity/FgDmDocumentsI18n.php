<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgDmDocumentsI18n
 */
class FgDmDocumentsI18n
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
     * @var string
     */
    private $descriptionLang;

    /**
     * @var string
     */
    private $authorLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgDmDocumentsI18n
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
     * @return FgDmDocumentsI18n
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
     * Set descriptionLang
     *
     * @param string $descriptionLang
     * @return FgDmDocumentsI18n
     */
    public function setDescriptionLang($descriptionLang)
    {
        $this->descriptionLang = $descriptionLang;
    
        return $this;
    }

    /**
     * Get descriptionLang
     *
     * @return string 
     */
    public function getDescriptionLang()
    {
        return $this->descriptionLang;
    }

    /**
     * Set authorLang
     *
     * @param string $authorLang
     * @return FgDmDocumentsI18n
     */
    public function setAuthorLang($authorLang)
    {
        $this->authorLang = $authorLang;
    
        return $this;
    }

    /**
     * Get authorLang
     *
     * @return string 
     */
    public function getAuthorLang()
    {
        return $this->authorLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $id
     * @return FgDmDocumentsI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgDmDocuments $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocuments 
     */
    public function getId()
    {
        return $this->id;
    }
}
