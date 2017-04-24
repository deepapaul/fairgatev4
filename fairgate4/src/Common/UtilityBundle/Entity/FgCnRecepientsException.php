<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnRecepientsException
 */
class FgCnRecepientsException
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnRecepients
     */
    private $recepientList;


    /**
     * Set id
     *
     * @param integer $id
     * @return FgCnRecepientsException
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
     * Set type
     *
     * @param string $type
     * @return FgCnRecepientsException
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
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return FgCnRecepientsException
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;
    
        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set recepientList
     *
     * @param \Common\UtilityBundle\Entity\FgCnRecepients $recepientList
     * @return FgCnRecepientsException
     */
    public function setRecepientList(\Common\UtilityBundle\Entity\FgCnRecepients $recepientList = null)
    {
        $this->recepientList = $recepientList;
    
        return $this;
    }

    /**
     * Get recepientList
     *
     * @return \Common\UtilityBundle\Entity\FgCnRecepients 
     */
    public function getRecepientList()
    {
        return $this->recepientList;
    }
}