<?php

/**
 * GotCourtsApiDetails
 *
 * This class is used to handle the Fairgate api details specific to GotCourts Api service
 *
 * @package    FairgateApiBundle
 * @subpackage Util
 * @author     Pits Solutions
 * @version    v0
 */
namespace FairgateApiBundle\Util;

use FairgateApiBundle\Util\FairagteApiDetails;
use Clubadmin\ContactBundle\Util\FgRecepientEmailValidator;

/**
 * This class is used to handle the Fairgate api details
 */
class GotCourtsApiDetails
{

    /**
     * @var object Container variable
     */
    public $container;
    
    /**
     * @var string The language from Accept-header of the api request
     */
    private $acceptLanguage = '';
    
    /**
     * @var string The language to be considered in the Api request; request from Accept-header
     */
    private $language = '';

    /**
     * @var string The language to be considered when Accept-header is not set or not available in the club
     */
    private $defaultAcceptLanguage = 'de';

    /**
     * @var object entity manager variable
     */
    private $em;

    /**
     * @var string The api encryption method used
     */
    private $apiEncryptionMethod = 'aes-256-ctr';

    /**
     * @var string The api encryption key used
     */
    private $apiEncryptionKey;

    /**
     * @var string  The api encryption iv used
     */
    private $apiEncryptionIv;

    /**
     * @var string The api encryption key used for gotcourts token encryption/decryption
     */
    private $gotcourtsApiEncryptionKey;

    /**
     * @var int  The api hasing iterations
     */
    private $gotcourtsApiEncryptionIterations;

    /**
     * Constructor of FgPageContainerDetails class.
     *
     * @param ContainerInterface    $container
     * @param String                $acceptLanguage
     */
    
    
    public function __construct($container, $acceptLanguage = '')
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();

        //The secret key that will be used to anonymize/deanonymize the id's in the got courts api
        // This will be kept secret and will not be shared
        $secretKey = $this->container->getParameter('apiSecrets');
        $this->apiEncryptionKey = $secretKey['secret_key'];
        $this->apiEncryptionIv = $secretKey['secret_iv'];

        // The public key that will be used for hasing the club token
        // This key will be passed to the GoutCouts manually
        $gotcourtsApiPublicKeys = $this->container->getParameter('gotcourtsApiPublicKeys');
        $this->gotcourtsApiEncryptionKey = $gotcourtsApiPublicKeys['public_key'];
        $this->gotcourtsApiEncryptionIterations = $gotcourtsApiPublicKeys['iterations'];
        
        $this->acceptLanguage = $acceptLanguage;
    }

    /**
     * The function to authenticate the user credentials provided on the 'Authorization' HTTP header
     * 
     * @param string $authorizationHeader   The value that is set in the 'Authorization' HTTP header
     * 
     * @return boolean
     */
    public function authenticateUser($authorizationHeader)
    {
        $fairagteApiDetails = new FairagteApiDetails($this->container);
        $authenticated = $fairagteApiDetails->authenticateUser($this->em, $authorizationHeader, $this->container);
        return $authenticated;
    }

    /**
     * The function to validate the X-Tenant-Token from the request 'x-Tenant-Token' header
     * 
     * @param string $token The data from the 'x-Tenant-Token' HTTP header
     * 
     * @return boolean
     */
    public function validateXTenantToken($token)
    {
        if ($this->encryptApiToken($this->container->getParameter('xTenantToken')) == $token) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * The function to check if the federation is provided and actually a federation (Not Implemented)
     * 
     * @param string $federationId  The federation id
     * 
     * @return boolean
     */
    public function validateFederationId($federationId)
    {
        if ($federationId > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * The function to check if the club is provided and existing
     * 
     * @param string $anonymizedClubId The club id that is anonymized
     * 
     * @return boolean
     */
    public function validateClubId($anonymizedClubId)
    {
        $clubId = $this->deanonymizeData($anonymizedClubId);
        if (intval($clubId) > 0) {
            $clubObj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            if ($clubObj && $clubObj->getId() == $clubId) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * The function to check if the client token is valid
     * 
     * @param int     $clubId        The club id that has been deanonymized
     * @param string  $clientToken   The decrypted client token from 'x-Client-Token' HTTP header
     * @param boolean $isActive      Whether the club token is active or not 
     * 
     * @return boolean
     */
    public function validateXClientToken($clubId, $clientToken, $isActive = true)
    {
        $tokenDetails = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')
            ->validateClientToken($clientToken, $clubId, $this->gotcourtsApiEncryptionKey, $this->gotcourtsApiEncryptionIterations, $isActive);
        if ($tokenDetails) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * The function to check if the lmod passed is valid
     * 
     * @param int   $days
     * 
     * @param bool
     */
    public function validateLmod($days)
    {
        if ($days > 0 && $days <= 360) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * The function to check if the email passed is valid
     * 
     * @param string   $email   The email for validation
     * 
     * @param bool
     */
    public function validateEmail($email)
    {
        $validatorObj = new FgRecepientEmailValidator($this->container, $email);
        $output = $validatorObj->isValidEmail();

        return $output['valid'];
    }

    /**
     * The functcion to create a club token
     * 
     * @param int $clubId   The club id for which the token will be created
     * @return string
     */
    public function createClubToken($clubId)
    {
        $clubToken = $this->createPBKDFToken($clubId);
        return $clubToken;
    }

    /**
     * The function to anonymize data
     * 
     * @param string $data The data to be encrypted
     * @return string
     */
    public function anonymizeData($data)
    {
        return $this->encrypt($data, $this->apiEncryptionMethod, $this->apiEncryptionKey, $this->apiEncryptionIv);
    }

    /**
     * The function to decrypt data
     * 
     * @param string $encryptedData The data to be decrypted
     * @return string
     */
    public function deanonymizeData($encryptedData)
    {
        return $this->decrypt($encryptedData, $this->apiEncryptionMethod, $this->apiEncryptionKey, $this->apiEncryptionIv);
    }

    /**
     * The function to create the PBKDFToken for a club
     * 
     * @param string $clubId The club id for which the token will be created for
     * 
     * @return string
     */
    private function createPBKDFToken($clubId)
    {
        $encryptionKey = md5($clubId) + openssl_random_pseudo_bytes(16);
        $salt = openssl_random_pseudo_bytes(16);

        return hash_pbkdf2("sha384", $encryptionKey, $salt, 1000, 48);
    }

    /**
     * 
     * The function to encrypt the given data
     * 
     * @param string $data                    The data to be encrypted
     * @param string $apiEncryptionMethod     The encryption menthod used
     * @param string $apiEncryptionKey        The key used to encrypt
     * @param string $apiEncryptionIv         The initialization vector to be used
     * 
     * $return string
     */
    private function encrypt($data, $apiEncryptionMethod, $apiEncryptionKey, $apiEncryptionIv)
    {
        $encryptedString = openssl_encrypt($data, $apiEncryptionMethod, $apiEncryptionKey, 0, $apiEncryptionIv);
        return base64_encode($encryptedString);
    }

    /**
     * 
     * The function to decrypt the given data
     * 
     * @param string $encryptedData           The data to be decrypted
     * @param string $apiEncryptionMethod     The encryption menthod used
     * @param string $apiEncryptionKey        The key used to encrypt
     * @param string $apiEncryptionIv         The initialization vector to be used
     * 
     * $return string
     */
    private function decrypt($encryptedData, $apiEncryptionMethod, $apiEncryptionKey, $apiEncryptionIv)
    {
        $decryptedString = openssl_decrypt(base64_decode($encryptedData), $apiEncryptionMethod, $apiEncryptionKey, 0, $apiEncryptionIv);
        return $decryptedString;
    }

    /**
     * This method is used to fetch current GotCourt api details and find api booking the step.
     * 
     * @return array GotCourts api details with api booking step.
     */
    public function getGotCourtsApiDetails()
    {
        $clubId = $this->container->get('club')->get('id');
        $result = $gcApiDetails = array();
        $gcApiDetails = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->getGotCourtsApi($clubId);
        $result['tokenWithClub'] = '';
        if (empty($gcApiDetails)) {
            $result['step'] = 1;
        } elseif ($gcApiDetails['status'] == 'booked') {
            $result['step'] = 2;
        } elseif ($gcApiDetails['status'] == 'generated' && $gcApiDetails['isActive'] == '1') {
            $result['step'] = 4;
            $result['tokenWithClub'] = $gcApiDetails['apitoken'] . ':' . $this->anonymizeData($clubId);
        } elseif ($gcApiDetails['status'] == 'generated') {
            $result['step'] = 3;
            $result['tokenWithClub'] = $gcApiDetails['apitoken'] . ':' . $this->anonymizeData($clubId);
        } else {
            $result['step'] = 1;
        }

        return $gcApiDetails ? array_merge($gcApiDetails, $result) : $result;
    }

    /**
     * This method is used to check whether the loggedin user is main admin or not.
     * 
     * @return boolean
     */
    public function isMainAdmin()
    {
        $isSuperAdmin = $this->container->get('contact')->get('isSuperAdmin');
        $userRights = $this->container->get('contact')->get('allowedModules');
        if ($isSuperAdmin == 1 || in_array('clubAdmin', $userRights)) {
            return true;
        }
        return false;
    }

    /**
     * This method is used to encryot the club token using the public key
     * 
     * @param string $token The raw token string
     *
     * @return string
     */
    public function encryptApiToken($token)
    {
        return hash_pbkdf2("sha384", $token, $this->gotcourtsApiEncryptionKey, $this->gotcourtsApiEncryptionIterations);
    }
    
    
    /**
     * The function to get the language that needed to set for the api request
     * 
     * @param string $clubId             The id of the club to which the request has been sent
     * 
     * @return string
     */
    public function getLanguage($clubId)
    {
        $language = '';
        if ($this->language != '') {
            //If the language is already been set return it
            $language = $this->language;
        } else if ($this->acceptLanguage == '') {
            //If the 'Accept-Language' header is empty or not set return the default language
            $language = $this->defaultAcceptLanguage;
        } else {
            //Check if the 'Accept-Language' value is available for a club
            $clubSettings = $this->em->getRepository('CommonUtilityBundle:FgApiGotcourts')->getClubLanguages($clubId);
            $availableLanguages = array_column($clubSettings, 'correspondanceLang');
            if (in_array($this->acceptLanguage, $availableLanguages)) {
                // If the language is available for the club return the 'Accept-Language'
                $language = $this->acceptLanguage;
            } else {
                // If the language is not available for the club return the defaultAcceptLanguage
                $language = $this->defaultAcceptLanguage;
            }
        }
        
        $this->language = $language;
        return $this->language;
    }
}
