<?php

/**
 * Clubs
 *
 * This class is used to serialize result data to XML
 *
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
namespace FairgateApiBundle\MappingClasses;

use FairgateApiBundle\MappingClasses\ContactDetails;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlAttributeMap;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;
use FairgateApiBundle\MappingClasses\Club;
/**
 * Clubs
 *
 * This class is used to serialize result data to XML
 *
 * @XmlRoot("clubs")
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
class Clubs
{
    /**
     * @XmlList(inline = true, entry = "club")
     */
    private $club;

    /**
     * Constructor to set the array collection
     */
    public function __construct()
    {
        $this->club = new ArrayCollection();
    }
    
    /**
     * Club
     * 
     * @return object
     */
    function getClub()
    {
        return $this->club;
    }

    /**
     * Club
     * 
     * @param Club $club
     */
    function setClub(Club $club)
    {
        $this->club[] = $club;
    }
}

