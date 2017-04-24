<?php

/**
 * GotcourtsController
 *
 * This class is used to handle Gotcourts API service
 *
 * @package    FairgateApiBundle
 * @subpackage Controller
 * @author     Ravikumar P S
 * @version    v0
 */
namespace FairgateApiBundle\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use FairgateApiBundle\Util\GotCourtsApiDetails;
use Common\UtilityBundle\Repository\Pdo\ApiPdo;
use FairgateApiBundle\Util\GotCourtsContactList;

/**
 * This class is used to handle Gotcourts API service
 */
class GotcourtsController extends FOSRestController
{

    /**
     * ### Example response for the GotCourts API service activation request in JSON format ###
     *     [
     *      {'tokenhash'}
     *     ]
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Activate Club after Registration",
     *  description="This resource will activate the club for GotCourts API service if the club token is valid",
     *  views = {"gotcourts"},
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"}
     *  },
     *  parameters={
     *      {"name"="token", "dataType"="string", "required"=true, "description"="The token string for club registration"}
     *  },
     *  statusCodes={
     *      200 = "Returned when successful",
     *      404 = {
     *               "Federation not found",
     *               "Club not found"
     *              },
     *      401 = "Not authorized",
     *      403 = "Forbidden",
     *      409 = "Token already in use",
     *      500 = {
     *                  "1005: Token not found"
     *              }
     *  }
     * )
     *
     * @return Response;
     */
    public function activateAction(Request $request)
    {
        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container);
        $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
        if (!$gotCourtApiDetailsObj->validateXTenantToken($request->headers->get('X-Tenant-Token'))) {
            $responseArray = array('error' => 'Forbidden');
            $responseCode = 403;
        } else if (!$gotCourtApiDetailsObj->authenticateUser($request->headers->get('Authorization'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if (!$gotCourtApiDetailsObj->validateFederationId($request->get('fid'))) {
            $responseArray = array('error' => 'Federation not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateClubId($request->get('cid'))) {
            $responseArray = array('error' => 'Club not found');
            $responseCode = 404;
        } else {
            //need to validate the club token
            $tokenHash = $request->get('token');
            $gotcourtMailDetail = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourts')->getGotCourtsApiByStatus($clubId, 'generated');
            //Check if valid token
            if (is_array($gotcourtMailDetail) && $gotCourtApiDetailsObj->encryptApiToken($gotcourtMailDetail['apitoken']) == $tokenHash) {
                if ($gotcourtMailDetail['isActive'] == 0) {
                    $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourts')->updateGotCourtsApi($gotcourtMailDetail['gcApiId'], array(), 'registered');
                    $logArray = array();
                    $logArray[] = array('field' => 'status', 'value_after' => 'registered', 'value_before' => $gotcourtMailDetail['status']);
                    $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourtsLog')->saveServiceLog($logArray, $clubId, $gotcourtMailDetail['gcApiId'], NULL);
                    $responseArray = array($gotCourtApiDetailsObj->encryptApiToken($gotcourtMailDetail['apitoken']));
                    $responseCode = 200;
                } else {
                    $responseArray = array('error' => 'Token already in use');
                    $responseCode = 409;
                }
            } else {
                $responseArray = array('errorCode' => 1005, 'error' => 'Token not found');
                $responseCode = 500;
            }
        }

        $this->saveAccessLog($request, $responseArray, $responseCode, $clubId);
        return $this->handleView($this->view($responseArray, $responseCode));
    }

    /**
     * ### Example response for the GotCourts API service token verification request in JSON format ###
     *     []
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Verify Club Token",
     *  description="Verifies if the token has already been activated for this club, respectively if the clubToken is already in use for another club.",
     *  views = {"gotcourts"},
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"}
     *  },
     *  parameters={},
     *  statusCodes={
     *      200 = "Returned when successful",
     *      404 = {
     *               "Federation not found",
     *               "Club not found",
     *               "Token not found",
     *              },
     *      401 = "Not authorized",
     *      403 = "Forbidden",
     *      409 = "Token already in use"
     *  }
     * )
     *
     * @return Response;
     */
    public function verifytokenAction(Request $request)
    {
        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container);
        $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
        if (!$gotCourtApiDetailsObj->validateXTenantToken($request->headers->get('X-Tenant-Token'))) {
            $responseArray = array('error' => 'Forbidden');
            $responseCode = 403;
        } else if (!$gotCourtApiDetailsObj->authenticateUser($request->headers->get('Authorization'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if (!$gotCourtApiDetailsObj->validateFederationId($request->get('fid'))) {
            $responseArray = array('error' => 'Federation not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateClubId($request->get('cid'))) {
            $responseArray = array('error' => 'Club not found');
            $responseCode = 404;
        } else if ($request->headers->get('X-Client-Token') == '') {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else {
            // check if active for the club
            $token = $request->headers->get('X-Client-Token');
            $gotcourtsApiPublicKeys = $this->container->getParameter('gotcourtsApiPublicKeys');
            $tokenDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourts')->validateClientToken($token, $clubId, $gotcourtsApiPublicKeys['public_key'], $gotcourtsApiPublicKeys['iterations']);
            $gotcourtMailDetail = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourts')->getGotCourtsApiByStatus($clubId, 'generated');

            if ($tokenDetails) {
                //verified for the club
                $responseArray = array('error' => 'Token already in use');
                $responseCode = 409;
            } else if (is_array($gotcourtMailDetail) && $gotCourtApiDetailsObj->encryptApiToken($gotcourtMailDetail['apitoken']) == $token && $gotcourtMailDetail['status'] == 'generated') {
                $responseCode = 200;
            } else {
                $responseArray = array('error' => 'Token not found');
                $responseCode = 404;
            }
        }

        $this->saveAccessLog($request, $responseArray, $responseCode, $clubId);
        return $this->handleView($this->view($responseArray, $responseCode));
    }

    /**
     * ### Example response for the GotCourts API service player cateogry request ###
     *     []
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Get Player Categories",
     *  description="Selects all player-categories of the given club with the respective club id.",
     *  views = {"gotcourts"},
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"}
     *  },
     *  parameters={
     *      {"name"="lmod", "dataType"="integer", "required"=false, "description"="Limit to specify modification days"}
     *  },
     *  statusCodes={
     *      200 = "Returned when successful",
     *      404 = {
     *               "Federation not found",
     *               "Club not found"
     *              },
     *      401 = "Not authorized",
     *      403 = "Forbidden",
     *      500 = {
     *                  "1001: Lmod should be greater than zero and less than 360"
     *              }
     *  }
     * )
     *
     * @return Response;
     */
    public function getPlayercategoryAction(Request $request)
    {
        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container, $request->headers->get('Accept-Language'));
        $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
        $lmod = $request->get('lmod');
        if (!$gotCourtApiDetailsObj->validateXTenantToken($request->headers->get('X-Tenant-Token'))) {
            $responseArray = array('error' => 'Forbidden');
            $responseCode = 403;
        } else if (!$gotCourtApiDetailsObj->authenticateUser($request->headers->get('Authorization'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if (!$gotCourtApiDetailsObj->validateFederationId($request->get('fid'))) {
            $responseArray = array('error' => 'Federation not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateClubId($request->get('cid'))) {
            $responseArray = array('error' => 'Club not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateXClientToken($clubId, $request->headers->get('X-Client-Token'), false)) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if ($lmod !== null && !$gotCourtApiDetailsObj->validateLmod($lmod)) {
            $responseArray = array('errorCode' => 1001, 'error' => 'Lmod should be greater than zero and less than 360');
            $responseCode = 500;
        } else {
            //get player categories
            $apiPdo = new ApiPdo($this->container);
            $language = $gotCourtApiDetailsObj->getLanguage($clubId);

            $membershipCategories = $apiPdo->getPlayerCategories($clubId, $language, $lmod);
            $membershipArray = $this->iterateMemberships($membershipCategories, $language, $lmod);
            $responseArray = array('categories' => $membershipArray);
            $responseCode = 200;
        }

        $this->saveAccessLog($request, $responseArray, $responseCode, $clubId);
        return $this->handleView($this->view($responseArray, $responseCode));
    }

    /**
     * ### Example response for the GotCourts API service player cateogry request ###
     *     []
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Get Members by name/email-filter",
     *  description="Get all members that match the filter. The response shall contain an Array of all matched Members In case of no contact could be found based on the given filter, the contacts array may be empty.",
     *  views = {"gotcourts"},
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"}
     *  },
     *  parameters={
     *      {"name"="firstName", "dataType"="string", "required"=false, "description"="The first name to be searched"},
     *      {"name"="lastName", "dataType"="string", "required"=false, "description"="The last name to be searched"},
     *      {"name"="email", "dataType"="string", "required"=false, "description"="The email to be searched"}
     *  },
     *  statusCodes={
     *      200 = "Returned when successful",
     *      404 = {
     *               "Federation not found",
     *               "Club not found"
     *              },
     *      401 = "Not authorized",
     *      403 = "Forbidden",
     *      500 = {
     *                  "1002: Invalid email format",
     *                  "1003: Members cannot be searched with firstname only",
     *                  "1006: Members cannot be searched without search parameters"
     *              }
     *  }
     * )
     *
     * @return Response;
     */
    public function filterContactAction(Request $request)
    {
        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container, $request->headers->get('Accept-Language'));
        $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
        if (!$gotCourtApiDetailsObj->validateXTenantToken($request->headers->get('X-Tenant-Token'))) {
            $responseArray = array('error' => 'Forbidden');
            $responseCode = 403;
        } else if (!$gotCourtApiDetailsObj->authenticateUser($request->headers->get('Authorization'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if (!$gotCourtApiDetailsObj->validateFederationId($request->get('fid'))) {
            $responseArray = array('error' => 'Federation not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateClubId($request->get('cid'))) {
            $responseArray = array('error' => 'Club not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateXClientToken($clubId, $request->headers->get('X-Client-Token'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if ($request->get('email') != '' && $gotCourtApiDetailsObj->validateEmail($request->get('email')) == 'false') {
            $responseArray = array('errorCode' => 1002, 'error' => 'Invalid email format');
            $responseCode = 500;
        } else if ($request->get('firstName') != '' && $request->get('lastName') == '') {
            $responseArray = array('errorCode' => 1003, 'error' => 'Members cannot be searched with firstname only');
            $responseCode = 500;
        } else if ($request->get('lastName') != '' || $request->get('email') != '') {
            $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
            if ($request->get('lastName')) {
                $searchVal['firstName'] = $request->get('firstName');
                $searchVal['lastName'] = $request->get('lastName');
            }
            $searchVal['email'] = $request->get('email');
            $lang = $gotCourtApiDetailsObj->getLanguage($clubId);
            $gcContact = new GotCourtsContactList($this->container, $clubId, $lang);
            $contactDetalis = array('contacts' => $gcContact->searchContact($searchVal));
            $responseArray = $contactDetalis;
            $responseCode = 200;
        } else {
            $responseArray = array('errorCode' => 1006, 'error' => 'Members cannot be searched without search parameters');
            $responseCode = 500;
        }

        return $this->handleView($this->view($responseArray, $responseCode));
    }

    /**
     * ### Example response for the GotCourts API service request ###
     *     []
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Get Specific Member by ID",
     *  description="Get the contact details of a specific member of a specific club. The response shall at least contain the fields in the example below but is not limited to those fields. The result contains the contact object.",
     *  views = {"gotcourts"},
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"},
     *      {"name"="contactId", "dataType"="integer", "requirement"="\d+", "description"="Contact id"}
     *  },
     *  parameters={},
     *  statusCodes={
     *      200 = "Returned when successful",
     *      404 = {
     *               "Federation not found",
     *               "Club not found",
     *               "ContactId not found"
     *              },
     *      401 = "Not authorized",
     *      403 = "Forbidden"
     *  }
     * )
     *
     * @return Response;
     */
    public function getContactByIdAction(Request $request)
    {
        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container, $request->headers->get('Accept-Language'));
        $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
        if (!$gotCourtApiDetailsObj->validateXTenantToken($request->headers->get('X-Tenant-Token'))) {
            $responseArray = array('error' => 'Forbidden');
            $responseCode = 403;
        } else if (!$gotCourtApiDetailsObj->authenticateUser($request->headers->get('Authorization'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if (!$gotCourtApiDetailsObj->validateFederationId($request->get('fid'))) {
            $responseArray = array('error' => 'Federation not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateClubId($request->get('cid'))) {
            $responseArray = array('error' => 'Club not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateXClientToken($clubId, $request->headers->get('X-Client-Token'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else {
            $contactId = $gotCourtApiDetailsObj->deanonymizeData($request->get('contactId'));
            $lang = $gotCourtApiDetailsObj->getLanguage($clubId);
            $gcContact = new GotCourtsContactList($this->container, $clubId, $lang);
            $contactDetails = $gcContact->getContactDetails($contactId);
            if ($contactDetails[0]) {
                $responseCode = 200;
                $responseArray = $contactDetails[0];
            } else {
                $responseCode = 404;
                $responseArray = array('error' => 'ContactId not found');
            }
        }

        return $this->handleView($this->view($responseArray, $responseCode));
    }

    /**
     * ### Example response for the GotCourts API service varify main admin request ###
     *     []
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Verify Main Admin",
     *  description="This resource verifies if the contact given by the query string is the club admin on Fairgate. No result shall be given, just the response code.",
     *  views = {"gotcourts"},
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"}
     *  },
     *  parameters={
     *      {"name"="lastName", "dataType"="string", "required"=true, "description"="The last name of contact"},
     *      {"name"="email", "dataType"="string", "required"=true, "description"="The email of contact"}
     *  },
     *  statusCodes={
     *      200 = "Returned when successful",
     *      404 = {
     *               "Federation not found",
     *               "Club not found",
     *               "Mainadmin not found"
     *              },
     *      401 = "Not authorized",
     *      403 = "Forbidden",
     *      500 = {
     *                  "1002: Invalid email format",
     *                  "1008: Email is required",
     *                  "1009: Lastname is required"
     *              } 
     *  }
     * )
     *
     * @return Response;
     */
    public function verifyMainAdminAction(Request $request)
    {
        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container);
        $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
        if (!$gotCourtApiDetailsObj->validateXTenantToken($request->headers->get('X-Tenant-Token'))) {
            $responseArray = array('error' => 'Forbidden');
            $responseCode = 403;
        } else if (!$gotCourtApiDetailsObj->authenticateUser($request->headers->get('Authorization'))) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if (!$gotCourtApiDetailsObj->validateFederationId($request->get('fid'))) {
            $responseArray = array('error' => 'Federation not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateClubId($request->get('cid'))) {
            $responseArray = array('error' => 'Club not found');
            $responseCode = 404;
        } else if (!$gotCourtApiDetailsObj->validateXClientToken($clubId, $request->headers->get('X-Client-Token'), false)) {
            $responseArray = array('error' => 'Not authorized');
            $responseCode = 401;
        } else if ($request->get('email') == '') {
            $responseArray = array('errorCode' => 1008, 'error' => 'Email is required');
            $responseCode = 500;
        } else if ($request->get('lastName') == '') {
            $responseArray = array('errorCode' => 1009, 'error' => 'Lastname is required');
            $responseCode = 500;
        } else if ($request->get('email') != '' && $gotCourtApiDetailsObj->validateEmail($request->get('email')) == 'false') {
            $responseArray = array('errorCode' => 1002, 'error' => 'Invalid email format');
            $responseCode = 500;
        } else {
            $clubId = $gotCourtApiDetailsObj->deanonymizeData($request->get('cid'));
            $lastName = $request->get('lastName');
            $email = $request->get('email');
            $gcContact = new GotCourtsContactList($this->container, $clubId);
            //Check for contact in federation
            $contactId = $gcContact->getContactFromLastNameAndEmail($lastName, $email);

            $clubObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:fgClub')->find($clubId);
            $fedId = $clubObj->getFederationId();
            if ($clubObj->getClubType() == 'federation' || $clubObj->getClubType() == 'standard_club') {
                $fedId = $clubId;
            }
            $gcFedContact = new GotCourtsContactList($this->container, $fedId);
            //Check for contact in federation
            $fedContactId = $gcFedContact->getContactFromLastNameAndEmail($lastName, $email);
            if ($gcContact->verifyMainAdmin($clubId, $contactId, $fedContactId)) {
                $responseCode = 200;
            } else {
                $responseArray = array('error' => 'Mainadmin not found');
                $responseCode = 404;
            }
        }

        return $this->handleView($this->view($responseArray, $responseCode));
    }

    /**
     * The function to save the access log for GotCourts API
     * 
     * @param object    $requestObj     The symfony request object
     * @param array     $responseArray  The response of the api request
     * @param int       $responseCode      The error code of the api
     * 
     * @return void
     */
    private function saveAccessLog($requestObj, $responseArray, $responseCode, $clubId)
    {
        if ($responseCode != 200) {
            //Save the log to DB
            $logArray = $requestDetails = array();
            $requestDetails['X-Tenant-Token'] = $requestObj->headers->get('X-Tenant-Token');
            $requestDetails['X-Client-Token'] = $requestObj->headers->get('X-Client-Token');
            $logArray['requestDetails'] = json_encode($requestDetails);
            $logArray['responseDetails'] = json_encode($responseArray);
            $logArray['responseCode'] = $responseCode;
            $logArray['clientIp'] = $requestObj->getClientIp();
            $logArray['apiUrl'] = $requestObj->getRequestUri();
            $apiId = 2;
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiAccesslog')->saveAccessLog($logArray, $clubId, $apiId);
        }
    }

    /**
     * The function to iterate the membership categories and format the arrat
     * 
     * @param object    $membershipCategories     The array of memberships
     * @param string    $language                 The language the api requested
     * @param string    $lmod                     The lmod specified in the request
     * 
     * @return void
     */
    private function iterateMemberships($membershipCategories, $language, $lmod)
    {
        $membershipArray = array();

        $gotCourtApiDetailsObj = new GotCourtsApiDetails($this->container);
        foreach ($membershipCategories as $membership) {
            $tempArray = array();
            $tempArray['categoryidhash'] = $gotCourtApiDetailsObj->anonymizeData($membership['id']);
            $tempArray['categoryname'] = $membership['title'];

            $logData = $this->findLog($membership['logData'], $language);
            if (is_array($logData)) {
                $dateObj = date_create_from_format('Y-m-d H:i:s', $logData['date']);
                $tempArray['date'] = date_format($dateObj, \DateTime::RFC2822);
                $tempArray['value_before'] = $logData['value_before'];
                $tempArray['value_after'] = $logData['value_after'];
            }

            $membershipArray[] = $tempArray;
        }

        return $membershipArray;
    }

    /**
     * The function to find the log data for the membership category
     * 
     * @param string    $logData    The log data string
     * @param string    $language   The language string
     * 
     * @return void
     */
    private function findLog($logData, $language)
    {
        if ($logData == '' || $logData == '') {
            return;
        }

        $logsArray = explode('|--|', $logData);
        $logsDetailArray = array();

        //Loop the array create another array grouped by the log dat
        foreach ($logsArray as $logString) {
            $logEntryStringArray = explode('####', $logString);
            $dateKey = strtotime($logEntryStringArray[1]);
            $logLanguage = trim(str_ireplace(array('Name', '(', ')'), '', $logEntryStringArray[0]));
            $logLanguage = ($logLanguage == '') ? 'de' : $logLanguage;  //If no language is provided in the log set it to de
            $logsDetailArray[$dateKey][$logLanguage] = array('date' => $logEntryStringArray[1], 'value_before' => ($logEntryStringArray[2] == NULL)?'':$logEntryStringArray[2], 'value_after' => ($logEntryStringArray[3] == NULL)?'':$logEntryStringArray[3]);
        }

        if (count($logsDetailArray) == 0) {
            return false;
        } else {
            ksort($logsDetailArray);   //sort such that the last updated entry will come last
            // take the first entry of that array
            $lastUpdatedDetail = end($logsDetailArray);
            if ($lastUpdatedDetail[$language]['value_before'] == '') { //we can assueme that it is a newly created category
                return false;
            } else {
                return $lastUpdatedDetail[$language];
            }
        }
    }
}
