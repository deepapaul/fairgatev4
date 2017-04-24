<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgMessageEmailFields
 */
class FgMessageEmailFields
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $attributeType;

    /**
     * @var \Common\UtilityBundle\Entity\FgMessageReceivers
     */
    private $receivers;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmAttribute
     */
    private $attribute;


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
     * Set attributeType
     *
     * @param string $attributeType
     *
     * @return FgMessageEmailFields
     */
    public function setAttributeType($attributeType)
    {
        $this->attributeType = $attributeType;

        return $this;
    }

    /**
     * Get attributeType
     *
     * @return string
     */
    public function getAttributeType()
    {
        return $this->attributeType;
    }

    /**
     * Set receivers
     *
     * @param \Common\UtilityBundle\Entity\FgMessageReceivers $receivers
     *
     * @return FgMessageEmailFields
     */
    public function setReceivers(\Common\UtilityBundle\Entity\FgMessageReceivers $receivers = null)
    {
        $this->receivers = $receivers;

        return $this;
    }

    /**
     * Get receivers
     *
     * @return \Common\UtilityBundle\Entity\FgMessageReceivers
     */
    public function getReceivers()
    {
        return $this->receivers;
    }

    /**
     * Set attribute
     *
     * @param \Common\UtilityBundle\Entity\FgCmAttribute $attribute
     *
     * @return FgMessageEmailFields
     */
    public function setAttribute(\Common\UtilityBundle\Entity\FgCmAttribute $attribute = null)
    {
        $this->attribute = $attribute;

        return $this;
    }

    /**
     * Get attribute
     *
     * @return \Common\UtilityBundle\Entity\FgCmAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}

