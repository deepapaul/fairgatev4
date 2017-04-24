<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgDmTeamFunctions
 */
class FgDmTeamFunctions
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $document;

    /**
     * @var \Common\UtilityBundle\Entity\FgRmFunction
     */
    private $function;


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
     * Set document
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $document
     *
     * @return FgDmTeamFunctions
     */
    public function setDocument(\Common\UtilityBundle\Entity\FgDmDocuments $document = null)
    {
        $this->document = $document;

        return $this;
    }

    /**
     * Get document
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocuments
     */
    public function getDocument()
    {
        return $this->document;
    }

    /**
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     *
     * @return FgDmTeamFunctions
     */
    public function setFunction(\Common\UtilityBundle\Entity\FgRmFunction $function = null)
    {
        $this->function = $function;

        return $this;
    }

    /**
     * Get function
     *
     * @return \Common\UtilityBundle\Entity\FgRmFunction
     */
    public function getFunction()
    {
        return $this->function;
    }
}

