<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmChangeToconfirmFunctions
 */
class FgCmChangeToconfirmFunctions
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $actionType;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmChangeToconfirm
     */
    private $toconfirm;

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
     * Set actionType
     *
     * @param string $actionType
     * @return FgCmChangeToconfirmFunctions
     */
    public function setActionType($actionType)
    {
        $this->actionType = $actionType;
    
        return $this;
    }

    /**
     * Get actionType
     *
     * @return string 
     */
    public function getActionType()
    {
        return $this->actionType;
    }

    /**
     * Set toconfirm
     *
     * @param \Common\UtilityBundle\Entity\FgCmChangeToconfirm $toconfirm
     * @return FgCmChangeToconfirmFunctions
     */
    public function setToconfirm(\Common\UtilityBundle\Entity\FgCmChangeToconfirm $toconfirm = null)
    {
        $this->toconfirm = $toconfirm;
    
        return $this;
    }

    /**
     * Get toconfirm
     *
     * @return \Common\UtilityBundle\Entity\FgCmChangeToconfirm 
     */
    public function getToconfirm()
    {
        return $this->toconfirm;
    }

    /**
     * Set function
     *
     * @param \Common\UtilityBundle\Entity\FgRmFunction $function
     * @return FgCmChangeToconfirmFunctions
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
