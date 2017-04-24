<?php

/**
 * Club
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

/**
 * Club
 *
 * This class is used to serialize result data to XML
 *
 * @XmlRoot("club")
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
class Club
{
    /** @XmlAttributeMap */
    private $id;
    
    function getId()
    {
        return $this->id;
    }

    function setId($id)
    {
        $this->id = $id;
    }

    
    /**
     *
     * @SerializedName("Name")
     */
    private $name;
    
    /**
     *
     * @SerializedName("Strasse")
     */
    private $strasse;
    
    /**
     *
     * @SerializedName("Postfach")
     */
    private $postfach;
    
    /**
     *
     * @SerializedName("PLZ")
     */
    private $plz;
    
    /**
     *
     * @SerializedName("Ort")
     */
    private $ort;
    
    /**
     * @XmlList(inline = true, entry = "contactDetails")
     */
    private $contactDetails;

    /**
     * Constructor to set the array collection
     */
    public function __construct()
    {
        $this->contactDetails = new ArrayCollection();
        $this->id = new ArrayCollection();
    }

    /**
     * ContactDetails
     * 
     * @return object
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * ContactDetails
     * 
     * @param ContactDetails $contactDetails
     */
    public function setContactDetails(ContactDetails $contactDetails)
    {
        $this->contactDetails[] = $contactDetails;
    }

    function getName()
    {
        return $this->name;
    }

     function getStrasse()
    {
        return $this->strasse;
    }

     function getPostfach()
    {
        return $this->postfach;
    }

     function getPlz()
    {
        return $this->plz;
    }

     function getOrt()
    {
        return $this->ort;
    }

     function setName($name)
    {
        $this->name = $name;
    }

     function setStrasse($strasse)
    {
        $this->strasse = $strasse;
    }

     function setPostfach($postfach)
    {
        $this->postfach = $postfach;
    }

     function setPlz($plz)
    {
        $this->plz = $plz;
    }

     function setOrt($ort)
    {
        $this->ort = $ort;
    }
}

