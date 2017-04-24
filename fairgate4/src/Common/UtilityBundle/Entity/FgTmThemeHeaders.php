<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgTmThemeHeaders
 */
class FgTmThemeHeaders
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $headerLabel;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var \Common\UtilityBundle\Entity\FgTmThemeConfiguration
     */
    private $configuration;


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
     * Set headerLabel
     *
     * @param string $headerLabel
     * @return FgTmThemeHeaders
     */
    public function setHeaderLabel($headerLabel)
    {
        $this->headerLabel = $headerLabel;
    
        return $this;
    }

    /**
     * Get headerLabel
     *
     * @return string 
     */
    public function getHeaderLabel()
    {
        return $this->headerLabel;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     * @return FgTmThemeHeaders
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    
        return $this;
    }

    /**
     * Get fileName
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set configuration
     *
     * @param \Common\UtilityBundle\Entity\FgTmThemeConfiguration $configuration
     * @return FgTmThemeHeaders
     */
    public function setConfiguration(\Common\UtilityBundle\Entity\FgTmThemeConfiguration $configuration = null)
    {
        $this->configuration = $configuration;
    
        return $this;
    }

    /**
     * Get configuration
     *
     * @return \Common\UtilityBundle\Entity\FgTmThemeConfiguration 
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }
}
