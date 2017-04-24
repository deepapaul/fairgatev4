<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgDmDocumentCategoryI18n
 */
class FgDmDocumentCategoryI18n
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
     * @var integer
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgDmDocumentCategoryI18n
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
     * @return FgDmDocumentCategoryI18n
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
     * @param integer $id
     * @return FgDmDocumentCategoryI18n
     */
    public function setId($id)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
