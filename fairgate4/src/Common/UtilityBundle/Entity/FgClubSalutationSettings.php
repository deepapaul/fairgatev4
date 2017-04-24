<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgClubSalutationSettings
 */
class FgClubSalutationSettings
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $femaleFormal;

    /**
     * @var string
     */
    private $femaleInformal;

    /**
     * @var string
     */
    private $maleFormal;

    /**
     * @var string
     */
    private $maleInformal;

    /**
     * @var string
     */
    private $familyFormal;

    /**
     * @var string
     */
    private $familyInformal;

    /**
     * @var string
     */
    private $companyNoMaincontact;

    /**
     * @var string
     */
    private $subscriber;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * Set femaleFormal
     *
     * @param string $femaleFormal
     *
     * @return FgClubSalutationSettings
     */
    public function setFemaleFormal($femaleFormal)
    {
        $this->femaleFormal = $femaleFormal;

        return $this;
    }

    /**
     * Get femaleFormal
     *
     * @return string
     */
    public function getFemaleFormal()
    {
        return $this->femaleFormal;
    }

    /**
     * Set femaleInformal
     *
     * @param string $femaleInformal
     *
     * @return FgClubSalutationSettings
     */
    public function setFemaleInformal($femaleInformal)
    {
        $this->femaleInformal = $femaleInformal;

        return $this;
    }

    /**
     * Get femaleInformal
     *
     * @return string
     */
    public function getFemaleInformal()
    {
        return $this->femaleInformal;
    }

    /**
     * Set maleFormal
     *
     * @param string $maleFormal
     *
     * @return FgClubSalutationSettings
     */
    public function setMaleFormal($maleFormal)
    {
        $this->maleFormal = $maleFormal;

        return $this;
    }

    /**
     * Get maleFormal
     *
     * @return string
     */
    public function getMaleFormal()
    {
        return $this->maleFormal;
    }

    /**
     * Set maleInformal
     *
     * @param string $maleInformal
     *
     * @return FgClubSalutationSettings
     */
    public function setMaleInformal($maleInformal)
    {
        $this->maleInformal = $maleInformal;

        return $this;
    }

    /**
     * Get maleInformal
     *
     * @return string
     */
    public function getMaleInformal()
    {
        return $this->maleInformal;
    }

    /**
     * Set familyFormal
     *
     * @param string $familyFormal
     *
     * @return FgClubSalutationSettings
     */
    public function setFamilyFormal($familyFormal)
    {
        $this->familyFormal = $familyFormal;

        return $this;
    }

    /**
     * Get familyFormal
     *
     * @return string
     */
    public function getFamilyFormal()
    {
        return $this->familyFormal;
    }

    /**
     * Set familyInformal
     *
     * @param string $familyInformal
     *
     * @return FgClubSalutationSettings
     */
    public function setFamilyInformal($familyInformal)
    {
        $this->familyInformal = $familyInformal;

        return $this;
    }

    /**
     * Get familyInformal
     *
     * @return string
     */
    public function getFamilyInformal()
    {
        return $this->familyInformal;
    }

    /**
     * Set companyNoMaincontact
     *
     * @param string $companyNoMaincontact
     *
     * @return FgClubSalutationSettings
     */
    public function setCompanyNoMaincontact($companyNoMaincontact)
    {
        $this->companyNoMaincontact = $companyNoMaincontact;

        return $this;
    }

    /**
     * Get companyNoMaincontact
     *
     * @return string
     */
    public function getCompanyNoMaincontact()
    {
        return $this->companyNoMaincontact;
    }

    /**
     * Set subscriber
     *
     * @param string $subscriber
     *
     * @return FgClubSalutationSettings
     */
    public function setSubscriber($subscriber)
    {
        $this->subscriber = $subscriber;

        return $this;
    }

    /**
     * Get subscriber
     *
     * @return string
     */
    public function getSubscriber()
    {
        return $this->subscriber;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgClubSalutationSettings
     */
    public function setClub(\Common\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }
}

