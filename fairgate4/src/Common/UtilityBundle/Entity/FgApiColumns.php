<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgApiColumns
 */
class FgApiColumns
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fieldName;

    /**
     * @var \Common\UtilityBundle\Entity\FgApis
     */
    private $apiType;


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
     * Set fieldName
     *
     * @param string $fieldName
     *
     * @return FgApiColumns
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get fieldName
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set apiType
     *
     * @param \Common\UtilityBundle\Entity\FgApis $apiType
     *
     * @return FgApiColumns
     */
    public function setApiType(\Common\UtilityBundle\Entity\FgApis $apiType = null)
    {
        $this->apiType = $apiType;

        return $this;
    }

    /**
     * Get apiType
     *
     * @return \Common\UtilityBundle\Entity\FgApis
     */
    public function getApiType()
    {
        return $this->apiType;
    }
}

