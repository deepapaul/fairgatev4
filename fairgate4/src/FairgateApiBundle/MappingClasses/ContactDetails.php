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
 * ContactDetails
 *
 * This class is used to serialize result data to XML
 *
 * @XmlRoot("contactDetails")
 * @package    FairgateApiBundle
 * @subpackage MappingClasses
 * @author     Ravikumar P S
 * @version    v0
 */
class ContactDetails
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
     * @var int Contact id 
     */
    private $contactId;
    
    /**
     *
     * @SerializedName("clubId")
     */
    private $clubId;

    /**
     *
     * @var date createdAt
     */
    private $createdAt;

    /**
     *
     * @SerializedName("Name")
     */
    private $updatedAt;

    /**
     *
     * @SerializedName("Kontakt-ID")
     */
    private $kontaktId;
    
    /**
     *
     * @SerializedName("Vorname")
     */
    private $vorname;

    /**
     *
     * @SerializedName("Nachname")
     */
    private $nachname;

    /**
     *
     * @SerializedName("Geburtsdatum")
     */
    private $geburtsdatum;

    /**
     *
     * @SerializedName("Postfach")
     */
    private $postfach;

    /**
     *
     * @SerializedName("Geschlecht")
     */
    private $geschlecht;

    /**
     *
     * @SerializedName("E-Mail")
     */
    private $email;

    /**
     *
     * @SerializedName("Strasse")
     */
    private $strasse;

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
     *
     * @SerializedName("Telefon")
     */
    private $telefon;

    /**
     *
     * @SerializedName("Natel")
     */
    private $natel;

    /**
     *
     * @SerializedName("Magazin")
     */
    private $magazin;

    /**
     *
     * @SerializedName("Eintrittsdatum")
     */
    private $eintrittsdatum;

    /**
     *
     * @SerializedName("ExecutiveBoardFunctions")
     */
    private $executiveBoardFunctions;
    
    /**
     *
     * @SerializedName("PrimaereSportart")
     */
    private $primaereSportart;
    
    /**
     *
     * @SerializedName("SATUSMitgliedschaften")
     */
    private $satusMitgliedschaften;

    /**
     *
     * @SerializedName("ExpirationStatus")
     */
    private $expirationStatus;
   
function getPrimaereSportart()
    {
        return $this->primaereSportart;
    }

    function setPrimaereSportart($primaereSportart)
    {
        $this->primaereSportart = $primaereSportart;
    }
    
    function getSatusMitgliedschaften()
    {
        return $this->satusMitgliedschaften;
    }

    function setSatusMitgliedschaften($satusMitgliedschaften)
    {
        $this->satusMitgliedschaften = $satusMitgliedschaften;
    }
    
    function getExpirationStatus()
    {
        return $this->expirationStatus;
    }

    function setExpirationStatus($expirationStatus)
    {
        $this->expirationStatus = $expirationStatus;
    }

        /**
     * Constructor to set the array collection
     */
    public function __construct()
    {
        $this->id = new ArrayCollection();
    }

    public function getContactId()
    {
        return $this->contactId;
    }

    /**
     * clubId
     * 
     * @return int
     */
    public function getClubId()
    {
        return $this->clubId;
    }

    /**
     * createdAt
     * 
     * @return date
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * updatedAt
     * 
     * @return date
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * vorname
     * 
     * @return string
     */
    public function getVorname()
    {
        return $this->vorname;
    }

    /**
     * nachname
     * 
     * @return string
     */
    public function getNachname()
    {
        return $this->nachname;
    }

    /**
     * geburtsdatum
     * 
     * @return date
     */
    public function getGeburtsdatum()
    {
        return $this->geburtsdatum;
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
     * geschlecht
     * 
     * @return string
     */
    public function getGeschlecht()
    {
        return $this->geschlecht;
    }

    /**
     * email
     * 
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * telefon
     * 
     * @return string
     */
    public function getTelefon()
    {
        return $this->telefon;
    }

    /**
     * natel
     * 
     * @return string
     */
    public function getNatel()
    {
        return $this->natel;
    }

    /**
     * magazin
     * 
     * @return string
     */
    public function getMagazin()
    {
        return $this->magazin;
    }

    /**
     * eintrittsdatum
     * 
     * @return string
     */
    public function getEintrittsdatum()
    {
        return $this->eintrittsdatum;
    }

    /**
     * executiveBoardFunctions
     * 
     * @return string
     */
    public function getExecutiveBoardFunctions()
    {
        return $this->executiveBoardFunctions;
    }

    /**
     * 
     * @param int $contactId
     */
    public function setContactId($contactId)
    {
        $this->contactId = $contactId;
    }

    /**
     * 
     * @param int $clubId
     */
    public function setClubId($clubId)
    {
        $this->clubId = $clubId;
    }

    /**
     * 
     * @param date $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * 
     * @param date $updatedAt
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * 
     * @param string $vorname
     */
    public function setVorname($vorname)
    {
        $this->vorname = $vorname;
    }

    /**
     * 
     * @param string $nachname
     */
    public function setNachname($nachname)
    {
        $this->nachname = $nachname;
    }

    /**
     * 
     * @param date $geburtsdatum
     */
    public function setGeburtsdatum($geburtsdatum)
    {
        $this->geburtsdatum = $geburtsdatum;
    }

    /**
     * 
     * @param string $postfach
     */
    public function setPostfach($postfach)
    {
        $this->postfach = $postfach;
    }

    /**
     * 
     * @param string $geschlecht
     */
    public function setGeschlecht($geschlecht)
    {
        $this->geschlecht = $geschlecht;
    }

    /**
     * 
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * 
     * @param string $strasse
     */
    public function setStrasse($strasse)
    {
        $this->strasse = $strasse;
    }

    /**
     * 
     * @param string $plz
     */
    public function setPlz($plz)
    {
        $this->plz = $plz;
    }

    /**
     * 
     * @param string $ort
     */
    public function setOrt($ort)
    {
        $this->ort = $ort;
    }

    /**
     * 
     * @param string $telefon
     */
    public function setTelefon($telefon)
    {
        $this->telefon = $telefon;
    }

    /**
     * 
     * @param string $natel
     */
    public function setNatel($natel)
    {
        $this->natel = $natel;
    }

    /**
     * 
     * @param string $magazin
     */
    public function setMagazin($magazin)
    {
        $this->magazin = $magazin;
    }

    /**
     * 
     * @param string $eintrittsdatum
     */
    public function setEintrittsdatum($eintrittsdatum)
    {
        $this->eintrittsdatum = $eintrittsdatum;
    }

    /**
     * 
     * @param string $executiveBoardFunctions
     */
    public function setExecutiveBoardFunctions($executiveBoardFunctions)
    {
        $this->executiveBoardFunctions = $executiveBoardFunctions;
    }
    
    /**
     * 
     * @param int $kontaktId
     */
    public function setKontaktId($kontaktId)
    {
        $this->kontaktId = $kontaktId;
    }
}
