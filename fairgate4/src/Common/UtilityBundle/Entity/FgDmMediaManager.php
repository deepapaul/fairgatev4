<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgDmMediaManager
 */
class FgDmMediaManager
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $directory;

    /**
     * @var string
     */
    private $subDirectory;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var integer
     */
    private $size;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \DateTime
     */
    private $lastUpdated;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $author;


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
     * Set directory
     *
     * @param string $directory
     *
     * @return FgDmMediaManager
     */
    public function setDirectory($directory)
    {
        $this->directory = $directory;

        return $this;
    }

    /**
     * Get directory
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Set subDirectory
     *
     * @param string $subDirectory
     *
     * @return FgDmMediaManager
     */
    public function setSubDirectory($subDirectory)
    {
        $this->subDirectory = $subDirectory;

        return $this;
    }

    /**
     * Get subDirectory
     *
     * @return string
     */
    public function getSubDirectory()
    {
        return $this->subDirectory;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return FgDmMediaManager
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return FgDmMediaManager
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
     * Set size
     *
     * @param integer $size
     *
     * @return FgDmMediaManager
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return FgDmMediaManager
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set lastUpdated
     *
     * @param \DateTime $lastUpdated
     *
     * @return FgDmMediaManager
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;

        return $this;
    }

    /**
     * Get lastUpdated
     *
     * @return \DateTime
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgDmMediaManager
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
     * Set author
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $author
     *
     * @return FgDmMediaManager
     */
    public function setAuthor(\Common\UtilityBundle\Entity\FgCmContact $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getAuthor()
    {
        return $this->author;
    }
}

