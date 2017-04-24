<?php

namespace Admin\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgSubsubcategorisation
 */
class FgSubsubcategorisation
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
     * @var \Admin\UtilityBundle\Entity\FgSubcategorisation
     */
    private $fgSubcategorisation;


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
     * @return FgSubsubcategorisation
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
     * Set fgSubcategorisation
     *
     * @param \Admin\UtilityBundle\Entity\FgSubcategorisation $fgSubcategorisation
     *
     * @return FgSubsubcategorisation
     */
    public function setFgSubcategorisation(\Admin\UtilityBundle\Entity\FgSubcategorisation $fgSubcategorisation = null)
    {
        $this->fgSubcategorisation = $fgSubcategorisation;

        return $this;

    }


    /**
     * Get fgSubcategorisation
     *
     * @return \Admin\UtilityBundle\Entity\FgSubcategorisation
     */
    public function getFgSubcategorisation()
    {
        return $this->fgSubcategorisation;

    }


}