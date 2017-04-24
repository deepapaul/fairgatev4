<?php

/**
 * ContactDetails
 *
 * This class is used to serialize result data to XML
 *
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
namespace FairgateApiBundle\MappingClasses;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlAttributeMap;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\SerializedName;

/**
 * ClubDetails
 *
 * This class is used to serialize result data to XML
 *
 * @XmlRoot("clubDetails")
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
class ClubDetails
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
     * @var int Club id
     */
    private $clubId;

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
     * Constructor to set the array collection
     */
    public function __construct()
    {
        $this->id = new ArrayCollection();
    }

    /**
     * name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * strasse
     *
     * @return string
     */
    public function getStrasse()
    {
        return $this->strasse;
    }

    /**
     * postfach
     *
     * @return string
     */
    public function getPostfach()
    {
        return $this->postfach;
    }

    /**
     * plz
     *
     * @return string
     */
    public function getPlz()
    {
        return $this->plz;
    }

    /**
     * ort
     *
     * @return string
     */
    public function getOrt()
    {
        return $this->ort;
    }

    /**
     * name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * strasse
     *
     * @param string $strasse
     */
    public function setStrasse($strasse)
    {
        $this->strasse = $strasse;
    }

    /**
     * postfach
     *
     * @param string $postfach
     */
    public function setPostfach($postfach)
    {
        $this->postfach = $postfach;
    }

    /**
     * plz
     *
     * @param string $plz
     */
    public function setPlz($plz)
    {
        $this->plz = $plz;
    }

    /**
     * ort
     *
     * @param string $ort
     */
    public function setOrt($ort)
    {
        $this->ort = $ort;
    }

    /**
     * federationId
     *
     * @return int
     */
    public function getClubId()
    {
        return $this->clubId;
    }

    /**
     * clubId
     *
     * @param int $clubId
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;
    }
}
