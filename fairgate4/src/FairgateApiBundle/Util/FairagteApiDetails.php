<?php

/**
 * FairagteApiDetails
 *
 * This class is used to handle the Fairgate api details
 *
 * @package    FairgateApiBundle
 * @subpackage Util
 * @author     Ravikumar P S
 * @version    v0
 */
namespace FairgateApiBundle\Util;

use FairgateApiBundle\MappingClasses\Club;
use FairgateApiBundle\MappingClasses\ContactDetails;
use FairgateApiBundle\MappingClasses\Federation;
use FairgateApiBundle\MappingClasses\ClubDetails;
use FairgateApiBundle\MappingClasses\Clubs;

/**
 * This class is used to handle the Fairgate api details
 */
class FairagteApiDetails
{

    /**
     * @var object Container variable
     */
    public $container;

    /**
     * @var object entity manager variable
     */
    private $em;

    /**
     * Constructor of FgPageContainerDetails class.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function is used to retrive formatted array structure
     *
     * @param array  $results    Array containing contact details of a club
     * @param string $acceptType Requested result format type
     * @param int    $days       Number of days
     * @param string $sdate      Date value
     *
     * @return array
     */
    public function formatContactDetails($results, $acceptType, $days = '', $sdate = '')
    {
        $apiColumns = $this->em->getRepository('CommonUtilityBundle:FgApiColumns')->getApiFieldNames();
        $returnArray = $this->getFormatedArrayForJson($apiColumns, $results);

        if ($acceptType == 'application/xml') {
            return $this->getSerializedXmlStructure($returnArray, $days, $sdate);
        } else {
            return $returnArray;
        }
    }

    /**
     * Function is used for getting serialized XML structure for all the APIs
     *
     * @param array  $return Array Structured array result
     * @param int    $days   Days count
     * @param string $sdate  Date
     *
     * @return Clubs
     */
    private function getSerializedXmlStructure($returnArray, $days, $sdate)
    {
        $clubs = new Clubs();
        foreach ($returnArray['clubs']['club'] as $key => $val) {
            $club = new Club();
            $idArray = array('clubId' => $key);
            $club->setId($idArray);
            $club->setName($val['Name']);
            $club->setStrasse($val['Strasse']);
            $club->setPostfach($val['Postfach']);
            $club->setPlz($val['PLZ']);
            $club->setOrt($val['Ort']);
            foreach ($val['contactDetails'] as $contactKey => $contactVal) {
                $contactDetails = new ContactDetails();
                $idArray = array('contactId' => $contactVal['contactId']);
                $contactDetails->setId($idArray);
                $contactDetails->setKontaktId($contactVal['KontaktID']);
                $contactDetails->setVorname($contactVal['Vorname']);
                $contactDetails->setNachname($contactVal['Nachname']);
                $contactDetails->setGeburtsdatum($contactVal['Geburtsdatum']);
                $contactDetails->setPostfach($contactVal['Postfach']);
                $contactDetails->setGeschlecht($contactVal['Geschlecht']);
                $contactDetails->setEmail($contactVal['EMail']);
                $contactDetails->setStrasse($contactVal['Strasse']);
                $contactDetails->setPlz($contactVal['PLZ']);
                $contactDetails->setOrt($contactVal['Ort']);
                $contactDetails->setTelefon($contactVal['Telefon']);
                $contactDetails->setNatel($contactVal['Natel']);
                $contactDetails->setMagazin($contactVal['Magazin']);
                $contactDetails->setEintrittsdatum($contactVal['Eintrittsdatum']);
                $contactDetails->setExecutiveBoardFunctions($contactVal['ExecutiveBoardFunctions']);
                $contactDetails->setPrimaereSportart($contactVal['PrimaereSportart']);
                $contactDetails->setSatusMitgliedschaften($contactVal['SATUSMitgliedschaften']);
                if ($days != '' || $sdate != '') {
                    $contactDetails->setExpirationStatus($contactVal['ExpirationStatus']);
                }
                $club->setContactDetails($contactDetails);
            }
            $clubs->setClub($club);
        }

        return $clubs;
    }

    /**
     * Function is used to generate JSON array structure for all the APIS
     *
     * @param array $apiColumns Pre-defined API columns
     * @param array $results    API result data
     *
     * @return array
     */
    private function getFormatedArrayForJson($apiColumns, $results)
    {
        foreach ($apiColumns as $columns) {
            foreach ($results as $value) {
                
                if(!isset($returnArray['clubs']['club'][$value['clubId']]['clubId'])){
                    $returnArray['clubs']['club'][$value['clubId']]['clubId'] = $value['clubId'];
                    $returnArray['clubs']['club'][$value['clubId']]['Name'] = $value['clubName'];
                    $returnArray['clubs']['club'][$value['clubId']]['Strasse'] = $value['clubStrasse'];
                    $returnArray['clubs']['club'][$value['clubId']]['Postfach'] = $value['clubPostfach'];
                    $returnArray['clubs']['club'][$value['clubId']]['PLZ'] = $value['clubPLZ'];
                    $returnArray['clubs']['club'][$value['clubId']]['Ort'] = $value['clubOrt'];
                }

                $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']]['contactId'] = $value['contactId'];
                $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']]['clubId'] = $value['clubId'];
                $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']]['createdAt'] = $value['created_at'];
                $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']]['updatedAt'] = $value['last_updated'];
                if (array_key_exists($columns['fieldName'], $value)) {
                    $value['Strasse'] = str_replace("\r\n", '', $value['Strasse']);
                    $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']][$columns['fieldName']] = ($value[$columns['fieldName']]) ? $value[$columns['fieldName']] : '';
                    if ($value['PrimaereSportart'] == '') {
                        $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']]['PrimaereSportart'] = '';
                    }
                    if ($value['ExecutiveBoardFunctions'] == '') {
                        $returnArray['clubs']['club'][$value['clubId']]['contactDetails'][$value['contactId']]['ExecutiveBoardFunctions'] = '';
                    }
                }
            }
        }

        return $returnArray;
    }

    /**
     * Function is used to retrive formatted array structure for club details api
     *
     * @param array  $results    Array containing club details of a federation
     * @param string $acceptType Requested result format type
     *
     * @return array
     */
    public function formatClubDetails($results, $acceptType, $fid)
    {
        $returnArray = array();
        foreach ($results as $value) {
            $returnArray['federation']['federationId'] = $fid;
            $returnArray['federation']['clubDetails'][$value['clubId']]['clubId'] = $value['clubId'];
            $returnArray['federation']['clubDetails'][$value['clubId']]['Name'] = $value['clubName'];
            $returnArray['federation']['clubDetails'][$value['clubId']]['Strasse'] = $value['clubStrasse'];
            $returnArray['federation']['clubDetails'][$value['clubId']]['Postfach'] = $value['clubPostfach'];
            $returnArray['federation']['clubDetails'][$value['clubId']]['PLZ'] = $value['clubPLZ'];
            $returnArray['federation']['clubDetails'][$value['clubId']]['Ort'] = $value['clubOrt'];
        }

        if ($acceptType == 'application/xml') {
            return $this->serializedClubDetailsXml($returnArray);
        }

        return $returnArray;
    }

    /**
     * Function to serialize club details data to XML
     *
     * @param array $returnArray Result data
     *
     * @return Federation
     */
    private function serializedClubDetailsXml($returnArray)
    {
        $federation = new Federation();
        $federation->setFederationId($returnArray['federation']['federationId']);
        foreach ($returnArray['federation']['clubDetails'] as $key => $clubData) {
            $clubDetails = new ClubDetails();
            $idArray = array('clubId' => $clubData['clubId']);
            $clubDetails->setId($idArray);
            $clubDetails->setName($clubData['Name']);
            $clubDetails->setStrasse($clubData['Strasse']);
            $clubDetails->setPostfach($clubData['Postfach']);
            $clubDetails->setPlz($clubData['PLZ']);
            $clubDetails->setOrt($clubData['Ort']);
            $federation->setClubDetails($clubDetails);
        }

        return $federation;
    }

    /**
     * Function to validate number of days
     *
     * @param int $days Number of days
     * @param int $min  Min value
     * @param int $max  Max value
     *
     * @return boolean
     */
    public function validateDateCount($days, $min = 1, $max = 360)
    {
        if (($min <= $days) && ($days <= $max)) {
            return true;
        }

        return false;
    }

    /**
     * Function to verify  date
     *
     * @param string $date date
     * @param string $format dateFormat
     *
     * @return boolean
     */
    public function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    /**
     * Function is used to check whether the user is authenticated or not
     *
     * @param object $em        Entity manager
     * @param string $authCode  Authentication token
     * @param object $container Container Object
     *
     * @return boolean/object
     */
    public function authenticateUser($em, $authCode, $container)
    {
        $getEncryptedCode = $this->generateEncryptDecrypt('encrypt', $authCode, $authCode, $container);
        $result = $em->getRepository('CommonUtilityBundle:SfGuardUser')->findSfGuardUserWithAuthCode($getEncryptedCode);
        if ($result) {
            $isApiAdmin = $em->getRepository('CommonUtilityBundle:SfGuardGroup')->isApiAdmin($em->getConnection(), $result['id']);

            return $return = ($isApiAdmin) ? true : false;
        } else {

            return false;
        }
    }

    /**
     * Function is used to encrypt or decrypt authCode
     *
     * @param string $action    encrypt/decrypt
     * @param string $data      Authentication token/Encrypted String
     * @param object $authCode  Authentication token
     * @param object $container Container Object
     *
     * @return string
     */
    public function generateEncryptDecrypt($action, $data, $authCode, $container)
    {
        $output = false;
        $keyfromData = substr($authCode, 6);
        $apiSecrets = array('secret_key' => '8f,q14OSN/b5vq)', 'secret_iv' => '7A6B1w-6Z+4&WF2C:kPW86gmw,yn)5');
        $secret_key = $apiSecrets['secret_key'];
        $secret_key = $secret_key . $keyfromData;
        $secret_iv = $apiSecrets['secret_iv'];
        $key = hash('sha256', $secret_key);
        $iv = substr(hash('sha256', $secret_iv), 0, 16);
        if ($action == 'encrypt') {
            $output = openssl_encrypt($data, "AES-256-CBC", $key, 0, $iv);
            $output = base64_encode($output);
        } else if ($action == 'decrypt') {
            $output = openssl_decrypt(base64_decode($data), $encrypt_method, $key, 0, $iv);
        }

        return $output;
    }
    
    /**
     * Function to verify if the provided id is a federation
     *
     * @param integer $federation The federation id
     *
     * @return boolean
     */
    public function validateFederation($federation)
    {
        $clubObj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($federation);
        if($clubObj->getIsFederation() == 1){
            return true;
        } else {
            return false;
        }
    }
   
    /* Function to verify if the provided id is a club under the federation
     *
     * @param integer $club         The club id
     * @param integer $federation   The federation id
     *
     * @return boolean
     */
    public function validateClub($club, $federation)
    {
        $clubObj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($club);
        if($clubObj->getFederationId() == $federation){
            return true;
        } else {
            return false;
        }
    }
}
