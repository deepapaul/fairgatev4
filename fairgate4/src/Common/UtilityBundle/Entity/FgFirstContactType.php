<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgFirstContactType
 */
class FgFirstContactType
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;


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
     * Set title
     *
     * @param string $title
     * @return FgFirstContactType
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }
}