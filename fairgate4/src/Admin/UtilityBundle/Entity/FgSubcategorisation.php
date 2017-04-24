<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgSubcategorisation
 */
class FgSubcategorisation
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $subsubcategoryName;

    /**
     * @var \Admin\UtilityBundle\Entity\FgCategorisation
     */
    private $fgCategorisation;


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
     * Set subsubcategoryName
     *
     * @param string $subsubcategoryName
     *
     * @return FgSubcategorisation
     */
    public function setSubsubcategoryName($subsubcategoryName)
    {
        $this->subsubcategoryName = $subsubcategoryName;

        return $this;

    }


    /**
     * Get subsubcategoryName
     *
     * @return string
     */
    public function getSubsubcategoryName()
    {
        return $this->subsubcategoryName;

    }


    /**
     * Set fgCategorisation
     *
     * @param \Admin\UtilityBundle\Entity\FgCategorisation $fgCategorisation
     *
     * @return FgSubcategorisation
     */
    public function setFgCategorisation(\Admin\UtilityBundle\Entity\FgCategorisation $fgCategorisation = null)
    {
        $this->fgCategorisation = $fgCategorisation;

        return $this;

    }


    /**
     * Get fgCategorisation
     *
     * @return \Admin\UtilityBundle\Entity\FgCategorisation
     */
    public function getFgCategorisation()
    {
        return $this->fgCategorisation;

    }


}