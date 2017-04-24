<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgClubSalutationSettingsI18n
 */
class FgClubSalutationSettingsI18n
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $femaleFormalLang;

    /**
     * @var string
     */
    private $femaleInformalLang;

    /**
     * @var string
     */
    private $maleFormalLang;

    /**
     * @var string
     */
    private $maleInformalLang;

    /**
     * @var string
     */
    private $familyFormalLang;

    /**
     * @var string
     */
    private $familyInformalLang;

    /**
     * @var string
     */
    private $companyNoMaincontactLang;

    /**
     * @var string
     */
    private $subscriberLang;


    /**
     * Set id
     *
     * @param integer $id
     * @return FgClubSalutationSettingsI18n
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

    /**
     * Set lang
     *
     * @param string $lang
     * @return FgClubSalutationSettingsI18n
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
     * Set femaleFormalLang
     *
     * @param string $femaleFormalLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setFemaleFormalLang($femaleFormalLang)
    {
        $this->femaleFormalLang = $femaleFormalLang;
    
        return $this;
    }

    /**
     * Get femaleFormalLang
     *
     * @return string 
     */
    public function getFemaleFormalLang()
    {
        return $this->femaleFormalLang;
    }

    /**
     * Set femaleInformalLang
     *
     * @param string $femaleInformalLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setFemaleInformalLang($femaleInformalLang)
    {
        $this->femaleInformalLang = $femaleInformalLang;
    
        return $this;
    }

    /**
     * Get femaleInformalLang
     *
     * @return string 
     */
    public function getFemaleInformalLang()
    {
        return $this->femaleInformalLang;
    }

    /**
     * Set maleFormalLang
     *
     * @param string $maleFormalLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setMaleFormalLang($maleFormalLang)
    {
        $this->maleFormalLang = $maleFormalLang;
    
        return $this;
    }

    /**
     * Get maleFormalLang
     *
     * @return string 
     */
    public function getMaleFormalLang()
    {
        return $this->maleFormalLang;
    }

    /**
     * Set maleInformalLang
     *
     * @param string $maleInformalLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setMaleInformalLang($maleInformalLang)
    {
        $this->maleInformalLang = $maleInformalLang;
    
        return $this;
    }

    /**
     * Get maleInformalLang
     *
     * @return string 
     */
    public function getMaleInformalLang()
    {
        return $this->maleInformalLang;
    }

    /**
     * Set familyFormalLang
     *
     * @param string $familyFormalLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setFamilyFormalLang($familyFormalLang)
    {
        $this->familyFormalLang = $familyFormalLang;
    
        return $this;
    }

    /**
     * Get familyFormalLang
     *
     * @return string 
     */
    public function getFamilyFormalLang()
    {
        return $this->familyFormalLang;
    }

    /**
     * Set familyInformalLang
     *
     * @param string $familyInformalLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setFamilyInformalLang($familyInformalLang)
    {
        $this->familyInformalLang = $familyInformalLang;
    
        return $this;
    }

    /**
     * Get familyInformalLang
     *
     * @return string 
     */
    public function getFamilyInformalLang()
    {
        return $this->familyInformalLang;
    }

    /**
     * Set companyNoMaincontactLang
     *
     * @param string $companyNoMaincontactLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setCompanyNoMaincontactLang($companyNoMaincontactLang)
    {
        $this->companyNoMaincontactLang = $companyNoMaincontactLang;
    
        return $this;
    }

    /**
     * Get companyNoMaincontactLang
     *
     * @return string 
     */
    public function getCompanyNoMaincontactLang()
    {
        return $this->companyNoMaincontactLang;
    }

    /**
     * Set subscriberLang
     *
     * @param string $subscriberLang
     * @return FgClubSalutationSettingsI18n
     */
    public function setSubscriberLang($subscriberLang)
    {
        $this->subscriberLang = $subscriberLang;
    
        return $this;
    }

    /**
     * Get subscriberLang
     *
     * @return string 
     */
    public function getSubscriberLang()
    {
        return $this->subscriberLang;
    }
}
