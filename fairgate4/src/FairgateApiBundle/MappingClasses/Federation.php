<?php

/**
 * Contacts
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
 * Federation
 *
 * This class is used to serialize result data to XML
 *
 * @XmlRoot("federation")
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
class Federation
{

    /**
     *
     * @SerializedName("federationId")
     */
    private $federationId;

    /**
     * @XmlList(inline = true, entry = "clubDetails")
     */
    private $clubDetails;

    /**
     * Constructor to set the array collection
     */
    public function __construct()
    {
        $this->clubDetails = new ArrayCollection();
    }

    /**
     * ClubDetails
     *
     * @return object
     */
    public function getClubDetails()
    {
        return $this->clubDetails;
    }

    /**
     * ClubDetails
     *
     * @param ClubDetails $clubDetails
     */
    public function setClubDetails(ClubDetails $clubDetails)
    {
        $this->clubDetails[] = $clubDetails;
    }

    /**
     * federationId
     *
     * @return int
     */
    public function getFederationId()
    {
        return $this->federationId;
    }

    /**
     * federationId
     *
     * @param int $federationId
     */
    public function setFederationId($federationId)
    {
        $this->federationId = $federationId;
    }
}
