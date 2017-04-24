<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCnSmsContacts
 */
class FgCnSmsContacts
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $smsId;

    /**
     * @var integer
     */
    private $filterId;

    /**
     * @var integer
     */
    private $categoryId;

    /**
     * @var integer
     */
    private $roleId;

    /**
     * @var integer
     */
    private $functionId;

    /**
     * @var string
     */
    private $selectionType;


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
     * Set smsId
     *
     * @param integer $smsId
     *
     * @return FgCnSmsContacts
     */
    public function setSmsId($smsId)
    {
        $this->smsId = $smsId;

        return $this;
    }

    /**
     * Get smsId
     *
     * @return integer
     */
    public function getSmsId()
    {
        return $this->smsId;
    }

    /**
     * Set filterId
     *
     * @param integer $filterId
     *
     * @return FgCnSmsContacts
     */
    public function setFilterId($filterId)
    {
        $this->filterId = $filterId;

        return $this;
    }

    /**
     * Get filterId
     *
     * @return integer
     */
    public function getFilterId()
    {
        return $this->filterId;
    }

    /**
     * Set categoryId
     *
     * @param integer $categoryId
     *
     * @return FgCnSmsContacts
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get categoryId
     *
     * @return integer
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set roleId
     *
     * @param integer $roleId
     *
     * @return FgCnSmsContacts
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * Get roleId
     *
     * @return integer
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * Set functionId
     *
     * @param integer $functionId
     *
     * @return FgCnSmsContacts
     */
    public function setFunctionId($functionId)
    {
        $this->functionId = $functionId;

        return $this;
    }

    /**
     * Get functionId
     *
     * @return integer
     */
    public function getFunctionId()
    {
        return $this->functionId;
    }

    /**
     * Set selectionType
     *
     * @param string $selectionType
     *
     * @return FgCnSmsContacts
     */
    public function setSelectionType($selectionType)
    {
        $this->selectionType = $selectionType;

        return $this;
    }

    /**
     * Get selectionType
     *
     * @return string
     */
    public function getSelectionType()
    {
        return $this->selectionType;
    }
}

