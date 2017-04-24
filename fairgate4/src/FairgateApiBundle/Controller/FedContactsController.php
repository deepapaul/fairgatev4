<?php

/**
 * FedContactsController
 *
 * This class is used to handle federaion API calls
 *
 * @package    FairgateApiBundle
 * @subpackage Controller
 * @author     Ravikumar P S
 * @version    v0
 */
namespace FairgateApiBundle\Controller;

use FairgateApiBundle\Util\FairagteApiDetails;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\FOSRestController;
use Common\UtilityBundle\Repository\Pdo\ApiPdo;

/**
 * This class is used to handle federaion API calls
 */
class FedContactsController extends FOSRestController
{

    /**
     * ### Example response for the federation contact details in JSON format ###
     *     [
     *      {
     *       "clubs":{
     *           "club": {
     *               "clubId": 25,
     *               "Name": "Twilight",
     *               "Strasse": "Kollam",
     *               "Postfach": "Postfach 501",
     *               "PLZ": "680854",
     *               "Ort": "kerala",
     *               "contactDetails": {
     *                   "1": {
     *                       "clubId": 25,
     *                       "createdAt": "2015-01-29T09:02:28+0530",
     *                       "updatedAt": "2016-01-29T09:02:28+0530",
     *                       "Vorname": "Jhonson",
     *                       "Nachname": "Smith",
     *                       "Geburtsdatum": "1954-07-24",
     *                       "Postfach": "Postfach 501",
     *                       "Geschlecht": "Male",
     *                       "EMail": "Jhonson.smith@twilight.ch",
     *                       "Strasse": "Kollam 1",
     *                       "PLZ": "3145",
     *                       "Ort": "Niederscherli",
     *                       "Telefon": "099 55 55 66",
     *                       "Natel": "099 55 55 66",
     *                       "Magazin": "Ja",
     *                       "Eintrittsdatum": "2015-02-04 08:48:42",
     *                       "ExecutiveBoardFunctions": "Präsident",
     *                       "PrimaereSportart": "ANDERE",
     *                       "TwilightMitgliedschaften": "Passiv",
     *                       "Kontakt-ID": 16237,
     *                       "contactId": "1"
     *                   }
     *               }
     *           }
     *       }
     *      }
     *     ]
     *
     * ### Example response for the federation contact details in XML format ###
     *     [
     *       <?xml version="1.0" encoding="UTF-8"?>
     *       <clubs>
     *           <club clubId="25">
     *               <Name><![CDATA[Twilight Gränichen]]></Name>
     *               <Strasse><![CDATA[Astonvilla]]></Strasse>
     *               <Postfach><![CDATA[Postfach 129]]></Postfach>
     *               <PLZ><![CDATA[5722]]></PLZ>
     *               <Ort><![CDATA[Gränichen]]></Ort>
     *               <contactDetails contactId="1">
     *                   <Kontakt-ID><![CDATA[16237]]></Kontakt-ID>
     *                   <Vorname><![CDATA[Jhonson]]></Vorname>
     *                   <Nachname><![CDATA[Smith]]></Nachname>
     *                   <Geburtsdatum><![CDATA[1954-07-24]]></Geburtsdatum>
     *                   <Postfach><![CDATA[Postfach 129]]></Postfach>
     *                   <Geschlecht><![CDATA[Male]]></Geschlecht>
     *                   <E-Mail><![CDATA[jhonson.smith@twilight.ch]]></E-Mail>
     *                   <Strasse><![CDATA[Scherlihalde 1]]></Strasse>
     *                   <PLZ><![CDATA[3145]]></PLZ>
     *                   <Ort><![CDATA[Niederscherli]]></Ort>
     *                   <Telefon><![CDATA[099 55 55 66]]></Telefon>
     *                   <Natel><![CDATA[099 55 55 66]]></Natel>
     *                   <Magazin><![CDATA[Ja]]></Magazin>
     *                   <Eintrittsdatum><![CDATA[2015-02-04 08:48:42]]></Eintrittsdatum>
     *                   <ExecutiveBoardFunctions><![CDATA[Präsident]]></ExecutiveBoardFunctions>
     *                   <PrimaereSportart><![CDATA[ANDERE]]></PrimaereSportart>
     *                   <TwilightMitgliedschaften>Abonnent</TwilightMitgliedschaften>
     *               </contactDetails>
     *           </club>
     *       </clubs>
     *     ]
     *
     * ### Example response of the federation api when given the count argument in JSON format ###
     *     [
     *       {
     *          "contactCount": "21"
     *       }
     *     ]
     *
     * ### Example response of the federation api when given the count argument in XML format ###
     *     [
     *       <entry>
     *           <![CDATA[21]]>
     *       </entry>
     *     ]
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Federation Contacts",
     *  description="Function is used to get first 20 contacts of the federation with id $fid last updated 20 contacts when given the lmode value.
      Also it is used for getting the total number of contacts and/or total number of contacts last updated according to the lmod value",
     *  requirements={
     *      {"name"="_format","dataType"="string","requirement"="json|xml|html","description"="Available API output formats"},
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"}
     *  },
     *  parameters={
     *      {"name"="lmod", "dataType"="integer", "required"=false, "description"="Limit to specify modification days"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="To specify the count of total contacts of the given clubid"},
     *      {"name"="lcrt", "dataType"="integer", "required"=false, "description"="Limit to specify creation date"},
     *      {"name"="sdate", "dataType"="datetime", "required"=false, "description"="Last modified datetime"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when no result of contacts",
     *      401="Returned when unauthorized access"
     *  }
     * )
     *
     * @return Response;
     */
    public function getFedContactsAction(Request $request, $fid)
    {
        $em = $this->container->get('doctrine')->getManager();
        $fairagteApiDetails = new FairagteApiDetails($this->container);

        $sfGuardUser = $fairagteApiDetails->authenticateUser($em, $request->headers->get('Authorization'), $this->container);
        $acceptType = $request->headers->get('Accept');
        if (!$sfGuardUser) {
            return $this->handleView($this->view('Unauthorized', 401));
        }
        $options = ['type' => 'Fed', 'fid' => $fid];
        $options['days'] = $request->get('lmod', false);
        $options['count'] = $request->get('count', false);
        $options['sdate'] = $request->get('sdate', false);
        $options['lcrt'] = $request->get('lcrt', false);
        $data = $this->handleFedClubData('getContactsofClub', $options);
        $results = $data['results'];
        
        if ($data['view'] == 'error') {
            return $this->handleView($this->view($data['message'], $data['header']));
        } else if (empty($results)) {
            return $this->handleView($this->view('No data found for the request', 200));
        }
        
        if ($data['view'] == 'contact') {
            $apiResult = $fairagteApiDetails->formatContactDetails($results, $acceptType, $options['days'], $options['sdate']);
            return $this->handleView($this->view($apiResult, 200));
        } else {
            return $this->handleView($this->view($data['results'], 200));
        }
    }

    /**
     * ### Example response for the signle federation contact details in JSON format ###
     *     [
     *      {
     *       "clubs":{
     *           "club": {
     *               "clubId": 25,
     *               "Name": "Twilight Gränichen",
     *               "Strasse": "Astonvilla",
     *               "Postfach": "Postfach 129",
     *               "PLZ": "5722",
     *               "Ort": "Gränichen",
     *               "contactDetails": {
     *                   "1": {
     *                       "clubId": 25,
     *                       "createdAt": "2015-01-29T09:02:28+0530",
     *                       "updatedAt": "2016-01-29T09:02:28+0530",
     *                       "Vorname": "Jhonson",
     *                       "Nachname": "Smith",
     *                       "Geburtsdatum": "1954-07-24",
     *                       "Postfach": "Postfach 129",
     *                       "Geschlecht": "Male",
     *                       "EMail": "jhonson.smith@twilight.ch",
     *                       "Strasse": "Scherlihalde 1",
     *                       "PLZ": "3145",
     *                       "Ort": "Niederscherli",
     *                       "Telefon": "099 55 55 66",
     *                       "Natel": "099 55 55 66",
     *                       "Magazin": "Ja",
     *                       "Eintrittsdatum": "2015-02-04 08:48:42",
     *                       "ExecutiveBoardFunctions": "Präsident",
     *                       "PrimaereSportart": "ANDERE",
     *                       "TwilightMitgliedschaften": "Passiv",
     *                       "Kontakt-ID: "16237",
     *                       "contactId": "1"
     *                   }
     *               }
     *           }
     *       }
     *      }
     *     ]
     *
     * ### Example response for the federation contact details in XML format ###
     *     [
     *       <?xml version="1.0" encoding="UTF-8"?>
     *       <clubs>
     *           <club clubId="25">
     *               <Name><![CDATA[Twilight Gränichen]]></Name>
     *               <Strasse><![CDATA[Astonvilla]]></Strasse>
     *               <Postfach><![CDATA[Postfach 129]]></Postfach>
     *               <PLZ><![CDATA[5722]]></PLZ>
     *               <Ort><![CDATA[Gränichen]]></Ort>
     *               <contactDetails contactId="1">
     *                   <Kontakt-ID><![CDATA[16237]]></Kontakt-ID>
     *                   <Vorname><![CDATA[Jhonson]]></Vorname>
     *                   <Nachname><![CDATA[Smith]]></Nachname>
     *                   <Geburtsdatum><![CDATA[1954-07-24]]></Geburtsdatum>
     *                   <Postfach><![CDATA[Postfach 129]]></Postfach>
     *                   <Geschlecht><![CDATA[Male]]></Geschlecht>
     *                   <E-Mail><![CDATA[jhonson.smith@twilight.ch]]></E-Mail>
     *                   <Strasse><![CDATA[Scherlihalde 1]]></Strasse>
     *                   <PLZ><![CDATA[3145]]></PLZ>
     *                   <Ort><![CDATA[Niederscherli]]></Ort>
     *                   <Telefon><![CDATA[099 55 55 66]]></Telefon>
     *                   <Natel><![CDATA[099 55 55 66]]></Natel>
     *                   <Magazin><![CDATA[Ja]]></Magazin>
     *                   <Eintrittsdatum><![CDATA[2015-02-04 08:48:42]]></Eintrittsdatum>
     *                   <ExecutiveBoardFunctions><![CDATA[Präsident]]></ExecutiveBoardFunctions>
     *                   <PrimaereSportart><![CDATA[ANDERE]]></PrimaereSportart>
     *                   <TwilightMitgliedschaften>Abonnent</TwilightMitgliedschaften>
     *               </contactDetails>
     *           </club>
     *       </clubs>
     *     ]
     * @ApiDoc(
     *  resource=true,
     *  section = "Single Federation Contact",
     *  description="This function is used to get a single federation contact with given club id and contact id",
     *  output = "\Symfony\Component\HttpFoundation\JsonResponse",
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="contactId", "dataType"="integer", "requirement"="\d+", "description"="Federation contact id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when no result of contacts",
     *      401="Returned when unauthorized access"
     *  }
     * )
     * @return Response;
     */
    public function getSingleFederationContactAction(Request $request, $fid, $contactId)
    {
        $apiPdo = new ApiPdo($this->container);
        $em = $this->container->get('doctrine')->getManager();
        $fairagteApiDetails = new FairagteApiDetails($this->container);

        $sfGuardUser = $fairagteApiDetails->authenticateUser($em, $request->headers->get('Authorization'));
        $acceptType = $request->headers->get('Accept');
        if (!$sfGuardUser) {
            return $this->handleView($this->view('Unauthorized', 401));
        }
        try {
            if ($fid != 2) {
                return $this->handleView($this->view('Forbidden Request', 403));
            } else if (!$fairagteApiDetails->validateFederation($fid)) {
                return $this->handleView($this->view('No data found for the request', 404));
            } else {
                $clubData = ['fId' => $fid];
                $results = $apiPdo->getContactsofClub($clubData, ['days' => '', 'resultCount' => '', 'contactId' => $contactId, 'countFlag' => '']);
                if (empty($results)) {
                    return $this->handleView($this->view('No data found for the request', 404));
                } else {
                    $apiResult = $fairagteApiDetails->formatContactDetails($results, $acceptType);
                    return $this->handleView($this->view($apiResult, 200));
                }
            }
        } catch (\Exception $ex) {
            return $this->handleView($this->view('Bad Request', 400));
        }
    }

    /**
     * ### Example response for the federation club contact details in JSON format ###
     *     [
     *      {
     *       "clubs":{
     *           "club": {
     *               "clubId": 25,
     *               "Name": "Twilight Gränichen",
     *               "Strasse": "Astonvilla",
     *               "Postfach": "Postfach 129",
     *               "PLZ": "5722",
     *               "Ort": "Gränichen",
     *               "contactDetails": {
     *                   "1": {
     *                       "clubId": 25,
     *                       "createdAt": "2015-01-29T09:02:28+0530",
     *                       "updatedAt": "2016-01-29T09:02:28+0530",
     *                       "Vorname": "Jhonson",
     *                       "Nachname": "Smith",
     *                       "Geburtsdatum": "1954-07-24",
     *                       "Postfach": "Postfach 129",
     *                       "Geschlecht": "Male",
     *                       "EMail": "jhonson.smith@twilight.ch",
     *                       "Strasse": "Scherlihalde 1",
     *                       "PLZ": "3145",
     *                       "Ort": "Niederscherli",
     *                       "Telefon": "099 55 55 66",
     *                       "Natel": "099 55 55 66",
     *                       "Magazin": "Ja",
     *                       "Eintrittsdatum": "2015-02-04 08:48:42",
     *                       "ExecutiveBoardFunctions": "Präsident",
     *                       "PrimaereSportart": "ANDERE",
     *                       "TwilightMitgliedschaften": "Passiv",
     *                       "Kontakt-ID": "16237",
     *                       "contactId": "1"
     *                   }
     *               }
     *           }
     *       }
     *      }
     *     ]
     *
     * ### Example response for the federation contact details in XML format ###
     *     [
     *       <?xml version="1.0" encoding="UTF-8"?>
     *       <clubs>
     *           <club clubId="25">
     *               <Name><![CDATA[Twilight Gränichen]]></Name>
     *               <Strasse><![CDATA[Astonvilla]]></Strasse>
     *               <Postfach><![CDATA[Postfach 129]]></Postfach>
     *               <PLZ><![CDATA[5722]]></PLZ>
     *               <Ort><![CDATA[Gränichen]]></Ort>
     *               <contactDetails contactId="1">
     *                   <Kontakt-ID><![CDATA[16237]]></Kontakt-ID>
     *                   <Vorname><![CDATA[Jhonson]]></Vorname>
     *                   <Nachname><![CDATA[Smith]]></Nachname>
     *                   <Geburtsdatum><![CDATA[1954-07-24]]></Geburtsdatum>
     *                   <Postfach><![CDATA[Postfach 129]]></Postfach>
     *                   <Geschlecht><![CDATA[Male]]></Geschlecht>
     *                   <E-Mail><![CDATA[jhonson.smith@twilight.ch]]></E-Mail>
     *                   <Strasse><![CDATA[Scherlihalde 1]]></Strasse>
     *                   <PLZ><![CDATA[3145]]></PLZ>
     *                   <Ort><![CDATA[Niederscherli]]></Ort>
     *                   <Telefon><![CDATA[099 55 55 66]]></Telefon>
     *                   <Natel><![CDATA[099 55 55 66]]></Natel>
     *                   <Magazin><![CDATA[Ja]]></Magazin>
     *                   <Eintrittsdatum><![CDATA[2015-02-04 08:48:42]]></Eintrittsdatum>
     *                   <ExecutiveBoardFunctions><![CDATA[Präsident]]></ExecutiveBoardFunctions>
     *                   <PrimaereSportart><![CDATA[ANDERE]]></PrimaereSportart>
     *                   <TwilightMitgliedschaften>Abonnent</TwilightMitgliedschaften>
     *               </contactDetails>
     *           </club>
     *       </clubs>
     *     ]
     *
     * ### Example response of the federation club api when given the count argument in JSON format ###
     *     [
     *       {
     *          "contactCount": "21"
     *       }
     *     ]
     *
     * ### Example response of the federation club api when given the count argument in XML format ###
     *     [
     *       <entry>
     *           <![CDATA[21]]>
     *       </entry>
     *     ]
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Federation club Contacts",
     *  description="Function is used to get first 20 contacts of the club with federation id $fid and last updated 20 contacts when given the lmode value.
      Also it is used for getting the total number of contacts and/or total number of contacts last updated according to the lmod value.
      Function returns the contact details of a standard club if we put fid as 0",
     *  output = "\Symfony\Component\HttpFoundation\JsonResponse",
     *  requirements={
     *      {"name"="_format","dataType"="string","requirement"="json|xml|html","description"="Available API output formats"},
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"}
     *  },
     *  parameters={
     *      {"name"="lmod", "dataType"="integer", "required"=false, "description"="Limit to specify modification days"},
     *      {"name"="count", "dataType"="integer", "required"=false, "description"="To specify the count of total contacts of the given clubid"},
     *      {"name"="lcrt", "dataType"="integer", "required"=false, "description"="Limit to specify creation date"},
     *      {"name"="sdate", "dataType"="datetime", "required"=false, "description"="Last modified datetime"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when no result of contacts",
     *      401="Returned when unauthorized access"
     *  }
     * )
     * @return Response;
     * )
     */
    public function getFedClubContactsAction(Request $request, $fid, $cid)
    {
        $em = $this->container->get('doctrine')->getManager();
        $fairagteApiDetails = new FairagteApiDetails($this->container);
        $sfGuardUser = $fairagteApiDetails->authenticateUser($em, $request->headers->get('Authorization'), $this->container);
        $acceptType = $request->headers->get('Accept');
        if (!$sfGuardUser) {
            return $this->handleView($this->view('Unauthorized', 401));
        }
        $options = ['type' => 'FedClub', 'fid' => $fid, 'cid' => $cid];
        $options['days'] = $request->get('lmod', false);
        $options['count'] = $request->get('count', false);
        $options['sdate'] = $request->get('sdate', false);
        $options['lcrt'] = $request->get('lcrt', false);
        $data = $this->handleFedClubData('getFedClubContacts', $options);
        $results = $data['results'];
        
        if ($data['view'] == 'error') {
            return $this->handleView($this->view($data['message'], $data['header']));
        } else if (empty($results)) {
            return $this->handleView($this->view('No data found for the request', 200));
        }
        
        if ($data['view'] == 'contact') {
            $apiResult = $fairagteApiDetails->formatContactDetails($data['results'], $acceptType, $options['days'], $options['sdate']);
        } else {
            return $this->handleView($this->view($data['results'], 200));
        }

        return $this->handleView($this->view($apiResult, 200));
    }

    /**
     * ### Example response for the signle federation club contact details in JSON format ###
     *     [
     *      {
     *       "clubs":{
     *           "club": {
     *               "clubId": 25,
     *               "Name": "Twilight Gränichen",
     *               "Strasse": "Astonvilla",
     *               "Postfach": "Postfach 129",
     *               "PLZ": "5722",
     *               "Ort": "Gränichen",
     *               "contactDetails": {
     *                   "1": {
     *                       "clubId": 25,
     *                       "createdAt": "2015-01-29T09:02:28+0530",
     *                       "updatedAt": "2016-01-29T09:02:28+0530",
     *                       "Vorname": "Johnson",
     *                       "Nachname": "Smith",
     *                       "Geburtsdatum": "1954-07-24",
     *                       "Postfach": "Postfach 129",
     *                       "Geschlecht": "Male",
     *                       "EMail": "Johnson.Smith@twilight.ch",
     *                       "Strasse": "Scherlihalde 1",
     *                       "PLZ": "3145",
     *                       "Ort": "Niederscherli",
     *                       "Telefon": "099 55 55 66",
     *                       "Natel": "099 55 55 66",
     *                       "Magazin": "Ja",
     *                       "Eintrittsdatum": "2015-02-04 08:48:42",
     *                       "ExecutiveBoardFunctions": "Präsident",
     *                       "PrimaereSportart": "ANDERE",
     *                       "TwilightMitgliedschaften": "Passiv"
     *                       "Kontakt-ID: "16237",
     *                       "contactId": "1"
     *                   }
     *               }
     *           }
     *       }
     *      }
     *     ]
     *
     * ### Example response for the federation contact details in XML format ###
     *     [
     *       <?xml version="1.0" encoding="UTF-8"?>
     *       <clubs>
     *           <club clubId="25">
     *               <Name><![CDATA[Twilight Gränichen]]></Name>
     *               <Strasse><![CDATA[Astonvilla]]></Strasse>
     *               <Postfach><![CDATA[Postfach 129]]></Postfach>
     *               <PLZ><![CDATA[5722]]></PLZ>
     *               <Ort><![CDATA[Gränichen]]></Ort>
     *               <contactDetails contactId="1">
     *                   <Kontakt-ID><![CDATA[16237]]></Kontakt-ID>
     *                   <Vorname><![CDATA[Jhonson]]></Vorname>
     *                   <Nachname><![CDATA[Smith]]></Nachname>
     *                   <Geburtsdatum><![CDATA[1954-07-24]]></Geburtsdatum>
     *                   <Postfach><![CDATA[Postfach 129]]></Postfach>
     *                   <Geschlecht><![CDATA[Male]]></Geschlecht>
     *                   <E-Mail><![CDATA[jhonson.smith@twilight.ch]]></E-Mail>
     *                   <Strasse><![CDATA[Scherlihalde 1]]></Strasse>
     *                   <PLZ><![CDATA[3145]]></PLZ>
     *                   <Ort><![CDATA[Niederscherli]]></Ort>
     *                   <Telefon><![CDATA[099 55 55 66]]></Telefon>
     *                   <Natel><![CDATA[099 55 55 66]]></Natel>
     *                   <Magazin><![CDATA[Ja]]></Magazin>
     *                   <Eintrittsdatum><![CDATA[2015-02-04 08:48:42]]></Eintrittsdatum>
     *                   <ExecutiveBoardFunctions><![CDATA[Präsident]]></ExecutiveBoardFunctions>
     *                   <PrimaereSportart><![CDATA[ANDERE]]></PrimaereSportart>
     *                   <TwilightMitgliedschaften>Abonnent</TwilightMitgliedschaften>
     *               </contactDetails>
     *           </club>
     *       </clubs>
     *     ]
     * @ApiDoc(
     *  resource=true,
     *  section = "Single Federation club Contact",
     *  description="This function is used to get a single federation club contact with given club id and contact id.
      Function also returns the single contact details of a standard club if we put fid as 0",
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"},
     *      {"name"="cid", "dataType"="integer", "requirement"="\d+", "description"="Club id"},
     *      {"name"="contactId", "dataType"="integer", "requirement"="\d+", "description"="Club contact id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when no result of contacts",
     *      401="Returned when unauthorized access"
     *  }
     * )
     * @return Response;
     */
    public function getSingleFedClubContactAction(Request $request, $fid, $cid, $contactId)
    {
        $apiPdo = new ApiPdo($this->container);
        $em = $this->container->get('doctrine')->getManager();
        $fairagteApiDetails = new FairagteApiDetails($this->container);

        $sfGuardUser = $fairagteApiDetails->authenticateUser($em, $request->headers->get('Authorization'), $this->container);
        $acceptType = $request->headers->get('Accept');
        if (!$sfGuardUser) {
            return $this->handleView($this->view('Unauthorized', 401));
        }
        
        try {
            if ($fid != 2) {
                return $this->handleView($this->view('Forbidden Request', 403));
            } else if (!$fairagteApiDetails->validateFederation($fid)) {
                return $this->handleView($this->view('No Result Found', 404));
            } else if (!$fairagteApiDetails->validateClub($cid, $fid)) {
                return $this->handleView($this->view('No Result Found', 404));
            } else {
                $clubData = ['fId' => $fid, 'cId' => $cid];
                $results = $apiPdo->getFedClubContacts($clubData, ['days' => '', 'resultCount' => '', 'contactId' => $contactId, 'countFlag' => '']);
                if (empty($results)) {
                    return $this->handleView($this->view('No data found for the request', 404));
                } else {
                    $apiResult = $fairagteApiDetails->formatContactDetails($results, $acceptType);
                    return $this->handleView($this->view($apiResult, 200));
                }
            }
        } catch (Exception $ex) {
            return $this->handleView($this->view('Bad Request', 400));
        }
    }

    /**
     * Function to get all contacts under the federation
     * 
     * @param Request $request Request object
     * @param int     $fid     Federation club id
     * 
     * @return JSON/XML
     */
    public function getAllFedContactsAction(Request $request, $fid)
    {
        $apiPdo = new ApiPdo($this->container);
        $em = $this->container->get('doctrine')->getManager();
        $fairagteApiDetails = new FairagteApiDetails($this->container);

        $sfGuardUser = $fairagteApiDetails->authenticateUser($em, $request->headers->get('Authorization'), $this->container);
        $acceptType = $request->headers->get('Accept');
        if (!$sfGuardUser) {
            return $this->handleView($this->view('Unauthorized', 401));
        }
        try {
            if ($fid != 2) {
                return $this->handleView($this->view('Forbidden Request', 403));
            } else if (!$fairagteApiDetails->validateFederation($fid)) {
                return $this->handleView($this->view('No Result Found', 404));
            } else {
                $clubData = ['fId' => $fid];
                $results = $apiPdo->getContactsofClub($clubData, ['days' => '', 'resultCount' => 'all']);

                if (empty($results)) {
                    return $this->handleView($this->view('No data found for the request', 404));
                } else {
                    $apiResult = $fairagteApiDetails->formatContactDetails($results, $acceptType);
                    return $this->handleView($this->view($apiResult, 200));
                }
            }
        } catch (Exception $ex) {
            return $this->handleView($this->view('Bad Request', 400));
        }
    }

    /**
     * ### Example response for the federation contact details in JSON format ###
     *     [
     *          {
     *              "federation": {
     *                  "federationId": 2,
     *                  "clubDetails": {
     *                      "3": {
     *                          "clubId": 3,
     *                          "Name": "Twilight Sportregion OST",
     *                          "Strasse": "Heumattstrasse 12",
     *                          "Postfach": "",
     *                          "PLZ": "8906",
     *                          "Ort": "Bonstetten"
     *                      }
     *                  }
     *              }
     *          }
     *     ]
     *
     * ### Example response for the federation contact details in XML format ###
     *     [
     *       <?xml version="1.0" encoding="UTF-8"?>
     *          <clubs>
     *               <federationId>2</federationId>
     *               <clubDetails clubId="3">
     *                   <Name>
     *                       <![CDATA[Twilight Sportregion OST]]>
     *                   </Name>
     *                   <Strasse>
     *                       <![CDATA[Heumattstrasse 12]]>
     *                   </Strasse>
     *                   <Postfach>
     *                       <![CDATA[]]>
     *                   </Postfach>
     *                   <PLZ>
     *                       <![CDATA[8906]]>
     *                   </PLZ>
     *                   <Ort>
     *                       <![CDATA[Bonstetten]]>
     *                   </Ort>
     *               </clubDetails>
     *          </clubs>
     *
     *     ]
     *
     * @ApiDoc(
     *  resource=true,
     *  section = "Federation clubs",
     *  description="Function is used to get all clubs under the federation with federation id",
     *  requirements={
     *      {"name"="fid", "dataType"="integer", "requirement"="\d+", "description"="Federation club id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when no result of contacts",
     *      401="Returned when unauthorized access"
     *  }
     * )
     *
     * @return Response;
     */
    public function getAllFedClubsAction(Request $request, $fid)
    {
        $apiPdo = new ApiPdo($this->container);
        $em = $this->container->get('doctrine')->getManager();
        $fairagteApiDetails = new FairagteApiDetails($this->container);

        $sfGuardUser = $fairagteApiDetails->authenticateUser($em, $request->headers->get('Authorization'), $this->container);
        $acceptType = $request->headers->get('Accept');
        if (!$sfGuardUser) {
            return $this->handleView($this->view('Unauthorized', 401));
        }
        try {
            if ($fid != 2) {
                return $this->handleView($this->view('Forbidden Request', 403));
            } else if (!$fairagteApiDetails->validateFederation($fid)) {
                return $this->handleView($this->view('No Result Found', 404));
            } else {
                $results = $apiPdo->getAllClubsUnderFederation($fid);

                if (empty($results)) {
                    return $this->handleView($this->view('No resultset found for the request', 404));
                } else {
                    $apiResult = $fairagteApiDetails->formatClubDetails($results, $acceptType, $fid);
                    return $this->handleView($this->view($apiResult, 200));
                }
            }

        } catch (Exception $ex) {
            return $this->handleView($this->view('Bad Request', 400));
        }
    }

    /**
     * 
     * @param string $functionName  The name of the function to be used
     * @param array  $options       The options array
     * 
     * @return array
     */
    private function handleFedClubData($functionName, $options)
    {
        $apiPdo = new ApiPdo($this->container);
        $fairagteApiDetails = new FairagteApiDetails($this->container);
        try {
            $sdate = $options['sdate'];
            $lcrt = $options['lcrt'];
            $days = $options['days'];
            $count = $options['count'];
            $clubData = ['fId' => $options['fid']];
            $data = [];
            if ($options['type'] == 'FedClub') {
                $clubData['cId'] = $options['cid'];
            }

            if ($options['fid'] != 2) {
                return ['view' => 'error', 'message' => 'Forbidden Request', 'header' => 403];
            }
            if (!$fairagteApiDetails->validateFederation($options['fid'])) {
                return ['view' => 'error', 'message' => 'No Result Found', 'header' => 404];
            }

            if ($options['type'] == 'FedClub' && !$fairagteApiDetails->validateClub($options['cid'], $options['fid'])) {
                return ['view' => 'error', 'message' => 'No Result Found', 'header' => 404];
            }

            $data['view'] = 'contact';
            if ($sdate) {
                if (!$fairagteApiDetails->validateDate($sdate)) {
                    $data = ['view' => 'error', 'message' => 'Invalid Date', 'header' => 500];
                } else {
                    $data['results'] = $apiPdo->$functionName($clubData, ['sdate' => $sdate, 'resultCount' => '', 'contactId' => '', 'countFlag' => '']);
                }
            } else if ($lcrt && $count) {
                if (!$fairagteApiDetails->validateDateCount($lcrt)) {
                    $data = ['view' => 'error', 'message' => 'Invalid input', 'header' => 500];
                } else {
                    $data['view'] = 'default';
                    $data['results'] = $apiPdo->$functionName($clubData, ['lcrt' => $lcrt, 'resultCount' => '', 'contactId' => '', 'countFlag' => 1]);
                }
            } else if ($lcrt) {
                $status = $fairagteApiDetails->validateDateCount($lcrt);
                if (!$fairagteApiDetails->validateDateCount($lcrt)) {
                    $data = ['view' => 'error', 'message' => 'Invalid input', 'header' => 500];
                } else {
                    $data['results'] = $apiPdo->$functionName($clubData, ['lcrt' => $lcrt, 'resultCount' => 50000, 'contactId' => '']);
                }
            } else if ($days && $count) {
                if (!$fairagteApiDetails->validateDateCount($days)) {
                    $data = ['view' => 'error', 'message' => 'Invalid input', 'header' => 500];
                } else {
                    $data['view'] = 'default';
                    $data['results'] = $apiPdo->$functionName($clubData, ['days' => $days, 'resultCount' => '', 'contactId' => '', 'countFlag' => 1]);
                }
            } else if ($days) {
                if (!$fairagteApiDetails->validateDateCount($days)) {
                    $data = ['view' => 'error', 'message' => 'Invalid input', 'header' => 500];
                } else {
                    $data['results'] = $apiPdo->$functionName($clubData, ['days' => $days, 'resultCount' => 50000, 'contactId' => '', 'countFlag' => '']);
                }
            } else if ($count) {
                $data['results'] = $apiPdo->$functionName($clubData, ['days' => '', 'resultCount' => '', 'contactId' => '', 'countFlag' => 1]);
                $data['view'] = 'default';
            } else {
                $data['results'] = $apiPdo->$functionName($clubData, ['days' => '', 'resultCount' => ($functionName == 'getContactsofClub') ? 50000 : 50000, 'contactId' => '', 'countFlag' => '']);
            }
            return $data;
        } catch (\Exception $e) {
            return $this->handleView($this->view('Bad Request', 400));
        }
    }
}
