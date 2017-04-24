<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgFileManager
 */
class FgFileManager
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $virtualFilename;

    /**
     * @var string
     */
    private $encryptedFilename;

    /**
     * @var boolean
     */
    private $isRemoved;

    /**
     * @var string
     */
    private $source;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgFileManagerVersion
     */
    private $latestVersion;


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
     * Set virtualFilename
     *
     * @param string $virtualFilename
     *
     * @return FgFileManager
     */
    public function setVirtualFilename($virtualFilename)
    {
        $this->virtualFilename = $virtualFilename;

        return $this;
    }

    /**
     * Get virtualFilename
     *
     * @return string
     */
    public function getVirtualFilename()
    {
        return $this->virtualFilename;
    }

    /**
     * Set encryptedFilename
     *
     * @param string $encryptedFilename
     *
     * @return FgFileManager
     */
    public function setEncryptedFilename($encryptedFilename)
    {
        $this->encryptedFilename = $encryptedFilename;

        return $this;
    }

    /**
     * Get encryptedFilename
     *
     * @return string
     */
    public function getEncryptedFilename()
    {
        return $this->encryptedFilename;
    }

    /**
     * Set isRemoved
     *
     * @param boolean $isRemoved
     *
     * @return FgFileManager
     */
    public function setIsRemoved($isRemoved)
    {
        $this->isRemoved = $isRemoved;

        return $this;
    }

    /**
     * Get isRemoved
     *
     * @return boolean
     */
    public function getIsRemoved()
    {
        return $this->isRemoved;
    }

    /**
     * Set source
     *
     * @param string $source
     *
     * @return FgFileManager
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgFileManager
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

    /**
     * Set latestVersion
     *
     * @param \Common\UtilityBundle\Entity\FgFileManagerVersion $latestVersion
     *
     * @return FgFileManager
     */
    public function setLatestVersion(\Common\UtilityBundle\Entity\FgFileManagerVersion $latestVersion = null)
    {
        $this->latestVersion = $latestVersion;

        return $this;
    }

    /**
     * Get latestVersion
     *
     * @return \Common\UtilityBundle\Entity\FgFileManagerVersion
     */
    public function getLatestVersion()
    {
        return $this->latestVersion;
    }
}

