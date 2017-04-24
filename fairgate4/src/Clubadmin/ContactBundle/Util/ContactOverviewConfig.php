<?php
namespace Clubadmin\ContactBundle\Util;

use Doctrine\ORM\EntityRepository;
use Clubadmin\Util\Contactlist;
use Clubadmin\Util\Tablesettings;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

/**
 * For handle the contact details view authntication
 *
 * @author PITSolutions <pit@solutions.com>
 */
class ContactOverviewConfig
{
    /**
     * $em
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $container
     * @var object {container object}
     */
    private $container;

    /**
     * $terminologyService
     * @var object {terminologyservice object}
     */
    private $terminologyService;

    /**
     * Constructor for initial setting
     *
     * @param type $container          Container Object
     * @param type $terminologyService Terminology service object
     */
    public function __construct($container, $terminologyService)
    {
        $this->container = $container;
        $this->terminologyService = $terminologyService;
        $this->em =  $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to get the federation informations
     *
     * @param array  $commonSortOrder Common sortorder
     * @param string $clubType        Club type
     *
     * @return array
     */
    public function federationInfoArray($commonSortOrder, $clubType)
    {
        $federationInfosArray = array();
        $fedMembershipArray = array('fieldType' => 'FI','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'fedmembership','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_MEMBERSHIP'));
        $clubmembershipArray =  array('fieldType' => 'FI','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'club','fieldName' => $this->terminologyService->getTerminology('Club', $this->container->getParameter('plural')));
        $executiveBoardArray = array('fieldType' => 'FI','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'ceb_function', 'fieldName' => ucfirst($this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_EXECUTIVE_BOARD', array('%executiveboard%' => $this->terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'))))));
        /* Checking whether current club is a federation.
         * If then the array structure is different than the normal and subfed.
         */
        switch ($clubType) {
            case 'federation':
                $federationInfosArray = array('federationInfo' => array('displayFlag' => 1,
                    'settingsType' => 'federationInfo',
                    'title' => ucfirst($this->terminologyService->getTerminology('Federation', $this->container->getParameter('singular'))) . $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_FEDERATION_INFOS'),
                    'sortorder' => $commonSortOrder, 'displayArea' => 'left',
                    'fields' => array(
                        'club' => $clubmembershipArray,
                        'sub_federation' => array('fieldType' => 'FI','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'sub_federation','fieldName' => ucfirst($this->terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')))),
                        'fedmembership' => $fedMembershipArray,
                        'ceb_function' => $executiveBoardArray,
                    )));
                break;
            case 'federation_club':
                $federationInfosArray = array('federationInfo' => array('displayFlag' => 1,
                    'settingsType' => 'federationInfo',
                    'title' => ucfirst($this->terminologyService->getTerminology('Federation', $this->container->getParameter('singular'))) . $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_FEDERATION_INFOS'),
                    'sortorder' => $commonSortOrder, 'displayArea' => 'left',
                    'fields' => array('club' => $clubmembershipArray, 'fedmembership' => $fedMembershipArray
                    )));
                break;
            case 'sub_federation':
                $federationInfosArray = array('federationInfo' => array('displayFlag' => 1,
                    'settingsType' => 'federationInfo',
                    'title' => ucfirst($this->terminologyService->getTerminology('Federation', $this->container->getParameter('singular'))) . $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_FEDERATION_INFOS'),
                    'sortorder' => $commonSortOrder, 'displayArea' => 'left',
                    'fields' => array('club' => $clubmembershipArray, 'fedmembership' => $fedMembershipArray,
                                      'sub_federation' => array('fieldType' => 'FI','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'sub_federation','fieldName' => ucfirst($this->terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular'))))
                    )));
                break;
            case 'sub_federation_club':
                $federationInfosArray = array('federationInfo' => array('displayFlag' => 1,
                    'settingsType' => 'federationInfo',
                    'title' => ucfirst($this->terminologyService->getTerminology('Federation', $this->container->getParameter('singular'))) . $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_FEDERATION_INFOS'),
                    'sortorder' => $commonSortOrder, 'displayArea' => 'left',
                    'fields' => array('club' => $clubmembershipArray, 'fedmembership' => $fedMembershipArray,
                                      'sub_federation' => array('fieldType' => 'FI','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'sub_federation','fieldName' => ucfirst($this->terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular'))))
                    )));
                break;
        }

        return $federationInfosArray;
    }

    /**
     * Function is used to get the formated contact fields details array for overview.
     * The result array contains the default display setting and sort values.
     * Also the function returns the common sort order values for the rest.
     *
     * @param array  $fieldDetails             Contact field array
     * @param string $commonSortOrder          Common sort value
     * @param string $defaultEnabledCategories Default enabled cat id
     * @param string $personalCategoryFieldIds Personal cat field id that should be enabled
     * @param string $clubLanguages            Language array
     *
     * @return array
     */
    public function contactFieldArray($fieldDetails, $commonSortOrder, $defaultEnabledCategories, $personalCategoryFieldIds, $clubLanguages)
    {
        $previousIndexVal = '';
        // Looping all the enabled categories from database to get a formated structure.
        // The sort order($commonSortOrder) should be the continuation of the previous one.
        foreach ($fieldDetails as $fieldIndex => $fieldVal) {
            if ($previousIndexVal != $fieldVal['attributeSetId']) {
                $commonSortOrder++;
            }

            // Checking the category is in the default enabled category array.
            if (in_array($fieldVal['attributeSetId'], $defaultEnabledCategories)) {
                $resultArray[$fieldVal['attributeSetId']]['displayFlag'] = 0;
            } else {
                //Checking whether the category  is a system category or not
                if ($fieldVal['catSystem'] == 1) {
                    $resultArray[$fieldVal['attributeSetId']]['displayFlag'] = 1;
                } else {
                    $resultArray[$fieldVal['attributeSetId']]['displayFlag'] = 0;
                }
            }
            $resultArray[$fieldVal['attributeSetId']]['is_active'] = 1;
            $resultArray[$fieldVal['attributeSetId']]['title'] = $fieldVal['title'];
            $resultArray[$fieldVal['attributeSetId']]['clubId'] = $fieldVal['club_id'];
            $resultArray[$fieldVal['attributeSetId']]['attributeSetId'] = $fieldVal['attributeSetId'];
            $resultArray[$fieldVal['attributeSetId']]['settingsType'] = 'categoryset';
            $resultArray[$fieldVal['attributeSetId']]['sortorder'] = $commonSortOrder;
            $resultArray[$fieldVal['attributeSetId']]['displayArea'] = 'left';
            if ($fieldVal['attributeId'] != '') {
                if ((($fieldVal['attributeId'] == $this->container->getParameter('system_field_corress_lang')) && (count($clubLanguages) > 1)) || ($fieldVal['attributeId'] != $this->container->getParameter('system_field_corress_lang'))) {
                    if (!in_array($fieldVal['attributeId'], $personalCategoryFieldIds)) {
                        if (in_array($fieldVal['attributeSetId'], $defaultEnabledCategories)) {
                            $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['displayFlag'] = 0;
                        } else {
                            if ($fieldVal['is_system_field'] == 1) {
                                $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['displayFlag'] = 1;
                            } else {
                                $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['displayFlag'] = 1;
                            }
                        }
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['emptyFlag'] = 0;
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['fieldType'] = 'CF';
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['fieldId'] = $fieldVal['attributeId'];
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['fieldName'] = $fieldVal['fieldnameShort'];
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['inputType'] = $fieldVal['inputType'];
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['is_active'] = 1;
                        $resultArray[$fieldVal['attributeSetId']]['fields'][$fieldVal['attributeId']]['itemSortOrder'] = intval($fieldVal['itemSortOrder']);
                        $previousIndexVal = $fieldVal['attributeSetId'];
                    }
                }
            }
        }
        $finalResultArray['resultArray']=$resultArray;
        $finalResultArray['commonSortOrder']=$commonSortOrder;

        return $finalResultArray;
    }

    /**
     * Function to get the formated membership field array
     * 
     * @param type $commonSortOrder Common sort order
     * @param type $fedFlag         Flag to confirm federation or club membership
     * 
     * @return array
     */
    public function getMembershipArray($commonSortOrder, $fedFlag)
    {
        $membershipText = ($fedFlag) ? 'fedmembership' : 'clubmembership';
        $membershipArray = array( $membershipText => array('displayFlag' => 1,
                'settingsType' => $membershipText,
                'title' => ($fedFlag) ? $this->terminologyService->getTerminology('Fed membership', $this->container->getParameter('plural')) : $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_MEMBERSHIPS') ,
                'sortorder' => ($fedFlag) ? $commonSortOrder + 1 : $commonSortOrder + 2,
                'displayArea' => 'right'));

        return $membershipArray;
    }

    /**
     * function to get the formated field array
     *
     * @param string $mainArray      Main Array name
     * @param string $mainArrayIndex Main array index
     * @param string $fieldKeyIndex  Field array index
     * @param string $fieldType      Type
     * @param string $displayFlag    Display flag
     * @param string $emptyFlag      Empty flag
     * @param string $fieldId        Filed id
     * @param string $clubId         Club id
     * @param string $fieldName      Field name
     *
     * @return array
     */
    public function getFieldContentArray($mainArray, $mainArrayIndex, $fieldKeyIndex, $fieldType, $displayFlag, $emptyFlag, $fieldId, $clubId, $fieldName)
    {
        $mainArray[$mainArrayIndex]['fields'][$fieldKeyIndex]['fieldType'] = $fieldType;
        $mainArray[$mainArrayIndex]['fields'][$fieldKeyIndex]['displayFlag'] = $displayFlag;
        $mainArray[$mainArrayIndex]['fields'][$fieldKeyIndex]['emptyFlag'] = $emptyFlag;
        $mainArray[$mainArrayIndex]['fields'][$fieldKeyIndex]['fieldId'] = $fieldId;
        $mainArray[$mainArrayIndex]['fields'][$fieldKeyIndex]['club_id'] = $clubId;
        $mainArray[$mainArrayIndex]['fields'][$fieldKeyIndex]['fieldName'] = $fieldName;

        return $mainArray;
    }

    /**
     * This function is used to get a formated array of all role categories including team and workgroup(assignments).
     * Sort order is passed to get the increased sort value in each category result array.
     *
     * @param string $commonSortOrder Common sort value
     * @param string $clubIdArray     Club array
     * @param string $conn            Connection variable
     * @param string $clubDefaultLang Default club language
     *
     * @return array
     */
    public function getRoleCategoryOverviewArray($commonSortOrder, $clubIdArray, $conn, $clubDefaultLang)
    {
        $club = $this->container->get('club');

        // Query for getting all category,role and function
        // The sort order($commonSortOrder) should be the continuation of the previous one.
        $getAllRoleCategories = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllRoleCategories($clubIdArray, $conn, $clubDefaultLang);
        $newRoleCategoryArray = array();
        $newRoleCategoryArray['roleCategory']['displayFlag'] = 1;
        $newRoleCategoryArray['roleCategory']['settingsType'] = 'roleCategory';
        $newRoleCategoryArray['roleCategory']['sortorder'] = $commonSortOrder + 3;
        $newRoleCategoryArray['roleCategory']['displayArea'] = 'right';
        $newRoleCategoryArray['roleCategory']['title'] = $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_ASSIGNMENTS');

        // Team and workgroup categories should display at the to of the assignment box in the overview.
        // Below is the static values for the both.
        $teamWorkgroupArray = array($club->get('club_team_id') => array('displayFlag' => 1, 'fieldId' => $club->get('club_team_id'), 'clubId' => $club->get('id'), 'fieldName' => ucfirst($this->terminologyService->getTerminology('Team', $this->container->getParameter('plural')))),
            $club->get('club_workgroup_id') => array('displayFlag' => 1, 'fieldId' => $club->get('club_workgroup_id'), 'clubId' => $club->get('id'), 'fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_WORKGROUPS')));

        foreach ($teamWorkgroupArray as $teamWorkgroupValue) {
            $newRoleCategoryArray = $this->getFieldContentArray($newRoleCategoryArray, 'roleCategory', $teamWorkgroupValue['fieldId'], 'RF', 1, 0, $teamWorkgroupValue['fieldId'], $teamWorkgroupValue['clubId'], $teamWorkgroupValue['fieldName']);
        }

        // Looping the category, role and function from database to generate a base structure
        foreach ($getAllRoleCategories as $catIndex => $catVal) {
            $fieldType = ($catVal['function_assign'] == 'none') ? 'R' : 'RF';
            $newRoleCategoryArray = $this->getFieldContentArray($newRoleCategoryArray, 'roleCategory', $catVal['rmCatId'], $fieldType, 0, 0, $catVal['rmCatId'], $catVal['clubId'], $catVal['rmCatTitle']);
            $newRoleCategoryArray['roleCategory']['fields'][$catVal['rmCatId']]['isFedCategory'] = $catVal['is_fed_category'];
        }

        return $newRoleCategoryArray;
    }

    /**
     * This is used to get the formated array of system info details in the predefined sort orderfunction to get the formated System info array
     *
     * @param string $commonSortOrder Common sort value
     * @param string $club            Club object
     * @param string $bookedModules   Booked modules array
     *
     * @return array
     */
    public function getSystemInfoArray($commonSortOrder, $club, $bookedModules)
    {
        $systemInfosArray = array('systemInfo' => array('displayFlag' => 1,
                'settingsType' => 'systemInfo',
                'title' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_SYSTEM_INFOS'),
                'sortorder' => $commonSortOrder + 4,
                'displayArea' => 'right',
                'fields' => array(
                    'contact_id' => array('fieldType' => 'G','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'contact_id','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_CONTACT_ID')),
                    'created_at' => array('fieldType' => 'G','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'created_at','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_CREATED_ON')),
                    'last_updated' => array('fieldType' => 'G','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'last_updated','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_LAST_CHANGE')),
                    'last_login' => array('fieldType' => 'G', 'displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'last_login','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_LAST_LOGIN')),
                    'no_of_logins' => array('fieldType' => 'G','displayFlag' => 1,'emptyFlag' => 0, 'fieldId' => 'no_of_logins','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_NUMBER_OF_LOGIN')),
                    'last_invoice_sending' => array('fieldType' => 'G','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'last_invoice_sending','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_LAST_INVOICE_SENDING'))
                    )));

        // The condition is used to unset the invoice section in the system info if the invoice module is not purchased by the club
                if (!in_array('invoice', $bookedModules)) {
                            unset($systemInfosArray['systemInfo']['fields']['last_invoice_sending']);
                }

                return $systemInfosArray;
    }

    /**
     * Connection details are formated in the desired format using the following function(main contact,household and other connections)
     *
     * @param string $commonSortOrder Common sort value
     *
     * @return array
     */
    public function getConnectionArray($commonSortOrder)
    {
        $connectionsArray = array('connections' => array('displayFlag' => 1,
                'settingsType' => 'connections',
                'title' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_CONNECTIONS'),
                'sortorder' => $commonSortOrder + 5,
                'displayArea' => 'right',
                'fields' => array(
                    'household_contact_withoutlink' => array('fieldType' => 'CN','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'household_contact_withoutlink','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_HOUSEHOLD')),
                    'mainContact' => array('fieldType' => 'CN','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'mainContact','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_MAIN_CONTACT')),
                    'otherConnections' => array('fieldType' => 'CN','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'otherConnections','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_OTHER_CONNECTIONS'))
                )));

                return $connectionsArray;
    }

    /**
     * To get correspondence and invoice address in the desired format for address block to display in the overview
     *
     * @param string $commonSortOrder Common sort value
     *
     * @return array
     */
    public function getFormatedAddressBlock($commonSortOrder)
    {
        $addressBlockArray = array('addressBlock' => array('displayFlag' => 1,
                'settingsType' => 'addressBlock',
                'title' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_ADDRESS_BLOCKS'),
                'sortorder' => $commonSortOrder + 6,
                'displayArea' => 'right',
                'fields' => array(
                    'correspondenceAddress' => array('fieldType' => 'AB','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'correspondenceAddress','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_CORRESPONDANCE_ADDRESS')),
                    'invoiceAddress' => array('fieldType' => 'AB','displayFlag' => 1,'emptyFlag' => 0,'fieldId' => 'invoiceAddress','fieldName' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_INVOICE_ADDRESS'))
                )));

                return $addressBlockArray;
    }

    /**
     * function to get the formated Sponsor details if the sponsor module is purchased.
     *
     * @param string $commonSortOrder Common sort value
     *
     * @return array
     */
    public function getSponsorDetailsArray($commonSortOrder)
    {
        $sponsoredArray = array('sponsored' => array('displayFlag' => 0,
                    'settingsType' => 'sponsored',
                    'title' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_SPONSORED_BY'),
                    'sortorder' => $commonSortOrder + 7,
                    'displayArea' => 'right'));

        return $sponsoredArray;
    }

    /**
     * Function is used for getting formated base structure of notes details
     *
     * @param string $commonSortOrder Common sort value
     *
     * @return array
     */
    public function getFormatedNotesDetails($commonSortOrder)
    {
        $notesArray = array('notes' => array('displayFlag' => 1,
                'settingsType' => 'notes',
                'title' => $this->container->get('translator')->trans('CONTACT_OVERVIEW_SETTINGS_NOTE'),
                'sortorder' => $commonSortOrder + 8,
                'displayArea' => 'right'));

        return $notesArray;
    }

    /**
     * Function is used to get all the array values to display in the contact overview settings page.
     * Compare the saved settings and the base settings structure.
     * @param array  $overviewSettings Overview settings array
     * @param string $finalArray       Final array
     *
     * @return template
     */
    public function overviewSettingsArrayLoop($overviewSettings, $finalArray)
    {
        $displayedArray = array();
        $previousSortOrder = 0;
        $i=1;

        /*
         * Looping the base overview array structure and comparing it with the saved overview settings.
         * If the category alteady exists in the saved settings, then generate a new array with the current saved settings.
         * If not, take the base configuration to build the new array.
         * The same is applicable in the case of contact fields also.
         */
        foreach ($finalArray as $settingsKey => $settingsVal) {
            if (array_key_exists($settingsKey, $overviewSettings)) {
                $displayedArray[$settingsKey]['displayFlag'] = $overviewSettings[$settingsKey]['displayFlag'];
                if (isset($settingsVal['attributeSetId'])) {
                    $displayedArray[$settingsKey]['attributeSetId'] = $overviewSettings[$settingsKey]['attributeSetId'];
                }
                $displayedArray[$settingsKey]['title'] = $settingsVal['title'];
                $displayedArray[$settingsKey]['clubId'] = $settingsVal['clubId'];
                $displayedArray[$settingsKey]['settingsType'] = $settingsVal['settingsType'];
                $displayedArray[$settingsKey]['sortorder'] = $overviewSettings[$settingsKey]['sortorder'];
                if ($previousSortOrder < $overviewSettings[$settingsKey]['sortorder']) {
                    $previousSortOrder = $overviewSettings[$settingsKey]['sortorder'];
                }
                $displayedArray[$settingsKey]['displayArea'] = $overviewSettings[$settingsKey]['displayArea'];

                //Checking if there any fields existing inside the categories.
                if (isset($settingsVal['fields'])) {
                    foreach ($settingsVal['fields'] as $fieldKey => $fieldVal) {
                        //Checking whether there is any new field inside an already existing category.
                        //If then the else case will trigger
                        if (array_key_exists($fieldKey, $overviewSettings[$settingsKey]['fields'])) {
                            $displayedArray[$settingsKey]['fields'][$fieldKey]['displayFlag'] = $overviewSettings[$settingsKey]['fields'][$fieldKey]['displayFlag'];
                            if (isset($overviewSettings[$settingsKey]['fields'][$fieldKey]['emptyFlag'])) {
                                $displayedArray[$settingsKey]['fields'][$fieldKey]['emptyFlag'] = $overviewSettings[$settingsKey]['fields'][$fieldKey]['emptyFlag'];
                            } else {
                                $displayedArray[$settingsKey]['fields'][$fieldKey]['emptyFlag'] = 0;
                            }
                            if (isset($overviewSettings[$settingsKey]['fields'][$fieldKey]['fieldId'])) {
                                $displayedArray[$settingsKey]['fields'][$fieldKey]['fieldId'] = $overviewSettings[$settingsKey]['fields'][$fieldKey]['fieldId'];
                            }
                            if (isset($settingsVal['fields'][$fieldKey]['itemSortOrder'])) {                              
                                $displayedArray[$settingsKey]['fields'][$fieldKey]['itemSortOrder'] = intval($settingsVal['fields'][$fieldKey]['itemSortOrder']);
                            }

                            $displayedArray[$settingsKey]['fields'][$fieldKey]['fieldName'] = $fieldVal['fieldName'];

                        } else {
                            $displayedArray[$settingsKey]['fields'][$fieldKey] = $fieldVal;
                            $displayedArray[$settingsKey]['fields'][$fieldKey]['displayFlag'] = $fieldVal['displayFlag'];
                        }
                    }
                }
            } else {
                // Inserting dummy sort value for categories which are not existing in the saved config.
                // This is to display the new category at the bottom of the overview settings page.
                if (isset($settingsVal['attributeSetId'])) {
                    $settingsVal['sortorder'] = 'dummySort_'.$i;
                    $settingsVal['displayFlag'] = $settingsVal['displayFlag'];
                    if (isset($settingsVal['fields'])) {
                        foreach ($settingsVal['fields'] as $resultValKey => $keyValue) {
                            $settingsVal['fields'][$resultValKey]['displayFlag'] = $keyValue['displayFlag'];
                        }
                    }
                }
                $displayedArray[$settingsKey] = $settingsVal;
                $i++;
            }
        }

        return $displayedArray;
    }

    /**
     * This section is used to get the address block values to display in the addressblock section.
     * This value will not get from the contact list data.
     * We need to append the result to the final overview data
     *
     * @param string $currentContactName Contact details
     * @param string $countryList        Country list
     *
     * @return array
     */
    public function getAddressBlockArray($currentContactName, $countryList)
    {
        $addressBlockCorrespondence=array();

        // Checking whether the contact is a company contact or not
        if ($currentContactName['is_company'] == 1) {
            $addressBlockCorrespondence['companyName']=$currentContactName['companyName'];

            // If the contact has a main contact, then need to display the main contact etails
            if ($currentContactName['has_main_contact_address']) {
                if ($currentContactName['gender']=='Male') {
                    $addressBlockCorrespondenceMainConatcName= $this->container->get('translator')->trans('CONTACT_OVERVIEW_ADD_BLOCK_MR').' '.$currentContactName['firstName'].' '.$currentContactName['lastName'];
                } elseif ($currentContactName['gender']=='Female') {
                    $addressBlockCorrespondenceMainConatcName= $this->container->get('translator')->trans('CONTACT_OVERVIEW_ADD_BLOCK_MRS').' '.$currentContactName['firstName'].' '.$currentContactName['lastName'];
                }
                $addressBlockCorrespondence['mainContactName']=$addressBlockCorrespondenceMainConatcName;
            }
        } else {
            $contactSalutation = $currentContactName['salutation'];
            $contactTitle='';

            // Need to diaply the first and last name only if the contact is a formal one
            if ($contactSalutation=='Formal') {
                $contactTitle=$currentContactName['title'];
                if ($contactTitle!='') {
                    $addressBlockCorrespondence['contactName'] = $contactTitle.' '.$currentContactName['firstName'].' '.$currentContactName['lastName'];
                } else {
                    $addressBlockCorrespondence['contactName'] = $currentContactName['firstName'].' '.$currentContactName['lastName'];
                }
            } else {
                $addressBlockCorrespondence['contactName'] = $currentContactName['firstName'].' '.$currentContactName['lastName'];
            }
        }

        // Getting other contact details to display in the address block
        $addressBlockCorrespondence['street'] = $currentContactName['corresStrasse'];
        $addressBlockCorrespondence['postfach'] = $currentContactName['corresPostfach'];
        if ($currentContactName['corresPlz'] != '') {
            $addressBlockCorrespondence['zipcodeOrt'] = $currentContactName['corresPlz'] . ' ' . $currentContactName['corresOrt'];
        } else {
            $addressBlockCorrespondence['zipcodeOrt'] = $currentContactName['corresOrt'];
        }
        $addressBlockCorrespondence['country'] = $countryList[$currentContactName['corresLand']];

        return json_encode($addressBlockCorrespondence);
    }

    /**
     * Function is used to get all notes of the contact
     * @param string $clubId  ClubId
     * @param string $contact Contact Id
     *
     * @return Array
     */
    public function getAllNotes($clubId, $contact)
    {
        $getAllNotes = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getAllNotes($clubId, $contact);
        $notesCnt = 0;
        $notesArray = array();
        foreach ($getAllNotes as $notes) {
            $contactName = $this->em->getRepository('CommonUtilityBundle:FgCmNotes')->getContactname($notes['contactId'], false);
            $notesArray[$notesCnt] = $notes;
            $notesArray[$notesCnt]['createdBy'] = $contactName;
            $notesCnt++;
        }

        $returnNotes['allNotes'] = $notesArray;
        $returnNotes['notesCnt'] = $notesCnt;

        return $returnNotes;
    }

    /**
     * Function is used to get all membership log datas
     * @param array  $clubId  ClubId
     * @param string $contact ContactId
     *
     * @return template
     */
    public function getAllMembershipLogs($clubId, $contact)
    {
        $club = $this->container->get('club');
        $clubHeirarchy = $club->get('clubHeirarchy');
        $getAllMembershipLogs = $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->getAllMembershipLogs($contact, $clubId, $clubHeirarchy,$club);
        $finalMembershipArray = array();
        foreach($getAllMembershipLogs as $key=>$val) {
            if($val['membershipType'] == 'federation') {
                $finalMembershipArray['fedmembership'][] = $getAllMembershipLogs[$key];
            } else if($val['membershipType'] == 'club') {
                $finalMembershipArray['clubmembership'][] = $getAllMembershipLogs[$key];
            }
        }
        $getAllMembershipLogs=json_encode($finalMembershipArray);

        return $getAllMembershipLogs;
    }

    /**
     * Function is used to generate other connections for the overview
     * @param string $contact               Contact id
     * @param string $clubId                Club id
     * @param string $clubDefaultSystemLang System language code
     *
     * @return template
     */
    public function getAllOtherConnections($contact, $clubId, $clubDefaultSystemLang)
    {
        // Getting other connections from database
        $getConnections = $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->getAllConnections($clubId, $contact, $clubDefaultSystemLang);
        $j = 0;
        $allOtherConnections = array();

        // Looping the array if it is not empty to generate the connections in the desired format
        if (!empty($getConnections)) {
            foreach ($getConnections as $otherConnections) {
                $contactName = $this->contactName($otherConnections['linked_contact_id']);
                $allOtherConnections[$j]['contactName'] = $contactName['contactName'];
                $allOtherConnections[$j]['overviewLink'] = $this->container->get('router')->generate('render_contact_overview', array('offset' => 0, 'contact' => $otherConnections['linked_contact_id']));
                if ($otherConnections['type'] == 'otherpersonal') {
                    if ($otherConnections['name'] != '') {
                        $allOtherConnections[$j]['connectionName'] = $otherConnections['name'];
                    } else {
                        $allOtherConnections[$j]['connectionName'] = $otherConnections['relation'];
                    }
                    $allOtherConnections[$j]['type'] = $otherConnections['type'];
                } elseif ($otherConnections['type'] == 'othercompany') {
                    $allOtherConnections[$j]['connectionName'] = $otherConnections['relation'];
                    $allOtherConnections[$j]['type'] = $otherConnections['type'];
                }
                $j++;
            }
        }
        $returnConnections['allOtherConnections'] = $allOtherConnections;
        $returnConnections['otherConnCnt'] = $j;

        return $returnConnections;
    }

    /**
     * Function is used to get all main contact connections
     * @param string $contact            Contact Id
     * @param string $currentContactName Contact details
     * @param string $clubId             Club Id
     *
     * @return template
     */
    public function getAllMainContactConnections($contact, $currentContactName, $clubId)
    {
        // Getting all main contact connections of the user from database
        $getAllMainContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllMainContact($this->container->get('club')->get('id'), $contact, $currentContactName);
        $j = 0;
        $allMainContacts = array();
        if (!empty($getAllMainContact)) {
            foreach ($getAllMainContact as $mainContact) {
                if ($mainContact['mainContactId'] != '') {
//                    $contactName = $this->contactName($mainContact['mainContactId']);
                    $allMainContacts[$j]['contactName'] = $mainContact['contactName'];
                    $contactObj = new ContactPdo($this->container);
                    $visible=$contactObj->getClubContactId($mainContact['fedContactId'], $clubId);
                    if($visible){
                        $allMainContacts[$j]['overviewLink'] = $this->container->get('router')->generate('render_contact_overview', array('offset' => 0, 'contact' => $visible['id']));
                    }
                    $allMainContacts[$j]['functionName'] = $mainContact['functionName'];

                    $j++;
                }
            }
        }

        $returnMainContact['allMainContacts'] = $allMainContacts;
        $returnMainContact['maincontCnt'] = $j;

        return $returnMainContact;
    }

    /**
     * Function is used to compare the base overview array with trhe saved array and generate the displayed overview structure
     * @param string $currentContactName Contact details
     * @param string $overviewSettings   Saved settings array
     * @param string $finalArray         Base overview array
     * @param string $isFedMembership    Federation membership flag
     * @param string $clubId             Club Id
     *
     * @return template
     */
    public function generateOverviewFromSettings($currentContactName, $overviewSettings, $finalArray, $isFedMembership, $clubId)
    {
        $systemCategoryCompany = $this->container->getParameter('system_category_company');
        $systemCategoryPersonal = $this->container->getParameter('system_category_personal');
        $contentArrayForQuery = array();

        // Removing company and personal details if the current contact is not a company and have a main contact
        if ($currentContactName['is_company'] != 1) {
            unset($overviewSettings[$systemCategoryCompany]);
            unset($finalArray[$systemCategoryCompany]);
        } else {
            if ($currentContactName['has_main_contact'] != 1) {
                unset($overviewSettings[$systemCategoryPersonal]);
                unset($finalArray[$systemCategoryPersonal]);
            }
        }
        $previousSortOrder = 0;
        $j=1;

        /*
         * Looping base overview array.
         * If the base array id exists in the saved settings array, then display flag and the sort order are taken from the saved settings
         * If not, check the display flag of the category in the base array and if it is ON, then appended it with the main display array.
         * The same think is applicable in the case of fields inside each category.
         * $contentArrayForQuery is generated inside the looping for generating a query to get the result values of each field inside the overview
         */
        foreach ($finalArray as $settingsKey => $settingsVal) {
            //Checking key exists in the saved settings
            if (array_key_exists($settingsKey, $overviewSettings)) {
                $dummyFinalArray = $finalArray[$settingsKey];
                if ($overviewSettings[$settingsKey]['displayFlag'] != 0) {
                    unset($dummyFinalArray['fields']);
                    $dummyFinalArray['displayFlag'] = $overviewSettings[$settingsKey]['displayFlag'];
                    $dummyFinalArray['attributeSetId'] = $overviewSettings[$settingsKey]['attributeSetId'];
                    $dummyFinalArray['sortorder'] = $overviewSettings[$settingsKey]['sortorder'];
                    $dummyFinalArray['displayArea'] = $overviewSettings[$settingsKey]['displayArea'];
                    if ($previousSortOrder < $overviewSettings[$settingsKey]['sortorder']) {
                        $previousSortOrder = $overviewSettings[$settingsKey]['sortorder'];
                    }

                    $contactOverview[$settingsKey] = $dummyFinalArray;
                    // Looping fields inside the category
                    foreach ($settingsVal['fields'] as $fieldKey => $fieldVal) {
                        // Checking if the field exists in the saved settings
                        if (array_key_exists($fieldKey, $overviewSettings[$settingsKey]['fields'])) {
                            // Checking if the display flag is set in the saved settings for the corresponding field
                            if ($overviewSettings[$settingsKey]['fields'][$fieldKey]['displayFlag'] != 0) {
                                if ((isset($fieldVal['isFedCategory']) && $fieldVal['isFedCategory'] == 1 && $currentContactName['fed_membership_cat_id'] != '') || (!isset($fieldVal['isFedCategory'])) || ($fieldVal['isFedCategory'] == 0)) {
                                    $i++;
                                    $contactOverview[$settingsKey]['fields'][$fieldKey] = $overviewSettings[$settingsKey]['fields'][$fieldKey];

                                    // Function to build final contact overview array from the above loop values
                                    $contactOverview = $this->buildOverviewContactArray($contactOverview, $settingsKey, $fieldKey, $fieldVal);

                                    // Function to build the query array for getting the field values
                                    $contentArrayForQuery = $this->buildOverviewQueryArray($contentArrayForQuery, $i, $fieldVal, $clubId);
                                    $contentArrayForQuery[$i]['name'] = $dummyFinalArray['settingsType'] . '_' . $fieldVal['fieldType'] . '_' . $fieldVal['fieldId'];
                                }
                            }
                        } else {
                            if ($fieldVal['displayFlag'] != 0) {
                                if ((isset($fieldVal['isFedCategory']) && $fieldVal['isFedCategory'] == 1 && $currentContactName['fed_membership_cat_id'] != '' && $isFedMembership['is_fed_category'] == 1) || (!isset($fieldVal['isFedCategory'])) || ($fieldVal['isFedCategory'] == 0)) {
                                    $i++;
                                    $contactOverview[$settingsKey]['fields'][$fieldKey] = $fieldVal;

                                    // Function to build final contact overview array from the above loop values
                                    $contactOverview = $this->buildOverviewContactArray($contactOverview, $settingsKey, $fieldKey, $fieldVal);

                                    // Function to build the query array for getting the field values
                                    $contentArrayForQuery = $this->buildOverviewQueryArray($contentArrayForQuery, $i, $fieldVal, $clubId);
                                    $contentArrayForQuery[$i]['name'] = $dummyFinalArray['settingsType'] . '_' . $fieldVal['fieldType'] . '_' . $fieldVal['fieldId'];
                                }
                            }
                        }
                    }
                }
            } else {
                // Looping all the categories wthich are not exists in the saved settings.
                // If the display flag is set, then it should display in the overview.
                // These are the new categories which are not saved in the overview settings
                if ($settingsVal['displayFlag'] != 0) {
                    $contactOverview[$settingsKey]['displayFlag'] = $settingsVal['displayFlag'];
                    $contactOverview[$settingsKey]['title'] = $settingsVal['title'];
                    $contactOverview[$settingsKey]['clubId'] = $settingsVal['clubId'];
                    $contactOverview[$settingsKey]['attributeSetId'] = $settingsVal['attributeSetId'];
                    $contactOverview[$settingsKey]['settingsType'] = $settingsVal['settingsType'];
                    $contactOverview[$settingsKey]['sortorder'] = 'dummy_'.$j;
                    $contactOverview[$settingsKey]['displayArea'] = $settingsVal['displayArea'];
                    $j++;

                    foreach ($settingsVal['fields'] as $fieldKey => $fieldVal) {
                        if ($fieldVal['displayFlag'] != 0) {
                            if ((isset($fieldVal['isFedCategory']) && $fieldVal['isFedCategory'] == 1 && $currentContactName['fed_membership_cat_id'] != '' && $isFedMembership['is_fed_category'] == 1) || (!isset($fieldVal['isFedCategory'])) || ($fieldVal['isFedCategory'] == 0)) {
                                $i++;
                                $contactOverview[$settingsKey]['fields'][$fieldKey] = $fieldVal;

                                // Function to build final contact overview array from the above loop values
                                $contactOverview = $this->buildOverviewContactArray($contactOverview, $settingsKey, $fieldKey, $fieldVal);

                                // Function to build the query array for getting the field values
                                $contentArrayForQuery = $this->buildOverviewQueryArray($contentArrayForQuery, $i, $fieldVal, $clubId);
                                $contentArrayForQuery[$i]['name'] = $settingsVal['settingsType'] . '_' . $fieldVal['fieldType'] . '_' . $fieldVal['fieldId'];
                            }
                        }

                    }
                }
            }
        }

        $returnArray['contactOverview'] = $contactOverview;
        $returnArray['contentArrayForQuery'] = $contentArrayForQuery;

        return $returnArray;
    }

    /**
     * Function is used to get the overview from the base structure if the saved settings is empty
     * @param string $currentContactName Contact details
     * @param string $finalArray         Final base array
     * @param string $isFedMembership    Federation membership flag
     * @param string $clubId             Club Id
     *
     * @return Array
     */
    public function generateOverviewFromBase($currentContactName, $finalArray, $isFedMembership, $clubId)
    {
        $systemCategoryCompany = $this->container->getParameter('system_category_company');
        $systemCategoryPersonal = $this->container->getParameter('system_category_personal');
        $contentArrayForQuery = array();

        if ($currentContactName['is_company'] != 1) {
            unset($finalArray[$systemCategoryCompany]);
        } else {
            if ($currentContactName['has_main_contact'] != 1) {
                unset($finalArray[$systemCategoryPersonal]);
            }
        }

        // Looping base overview structure
        foreach ($finalArray as $key => $final) {
            $dummyFinalArray = $final;

            //Checking whether the category display in the overview
            if ($final['displayFlag'] != 0) {
                unset($dummyFinalArray['fields']);
                $contactOverview[$key] = $dummyFinalArray;

                // Looping fields inside the category
                foreach ($final['fields'] as $fieldKey => $fieldVal) {
                    if ($fieldVal['displayFlag'] != 0) {
                        if ((isset($fieldVal['isFedCategory']) && $fieldVal['isFedCategory'] == 1 && $currentContactName['fed_membership_cat_id'] != '' && $isFedMembership['is_fed_category'] == 1) || (!isset($fieldVal['isFedCategory'])) || ($fieldVal['isFedCategory'] == 0)) {
                            $i++;
                            $contactOverview[$key]['fields'][$fieldKey] = $fieldVal;

                            // Function to build final contact overview array from the above loop values
                            $contactOverview = $this->buildOverviewContactArray($contactOverview, $key, $fieldKey, $fieldVal);

                            // Function to build the query array for getting the field values
                            $contentArrayForQuery = $this->buildOverviewQueryArray($contentArrayForQuery, $i, $fieldVal, $clubId);
                            $contentArrayForQuery[$i]['name'] = $final['settingsType'] . '_' . $fieldVal['fieldType'] . '_' . $fieldVal['fieldId'];
                        }
                    }
                }
            }
        }
        $returnArray['contactOverview'] = $contactOverview;
        $returnArray['contentArrayForQuery'] = $contentArrayForQuery;

        return $returnArray;
    }

    /**
     * Function to get contact name
     * @param int $contactId   Contact Id
     * @param int $contactType Contact type
     *
     * @return array
     */
    private function contactName($contactId, $contactType = 'contact')
    {
        $club = $this->container->get('club');
        $contactlistClass = new Contactlist($this->container, '', $club, $contactType);
        $contactlistClass->setColumns(array('contactName','isMemberTitle','isMember','gender', 'isCompany','fed_membership_cat_id','club_membership_cat_id','fg_cm_contact.is_sponsor','is_subscriber','fg_cm_contact.intranet_access','is_company','`72`'));
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.id = $contactId";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->addJoin(" LEFT JOIN fg_cm_membership on fg_cm_contact.fed_membership_cat_id= fg_cm_membership.id");
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->container->get('database_connection')->fetchAll($listquery);

        return $fieldsArray[0];
    }

    /**
     * Function is used to generate contact overview array for each inner loop
     * @param string $contactOverview Overview array
     * @param string $settingsKey     Settings key index value
     * @param string $fieldKey        Field value array
     * @param string $fieldVal        Field value array
     *
     * @return Array
     */
    private function buildOverviewContactArray($contactOverview, $settingsKey, $fieldKey, $fieldVal)
    {
        $contactOverview[$settingsKey]['fields'][$fieldKey]['fieldName'] = $fieldVal['fieldName'];
        $contactOverview[$settingsKey]['fields'][$fieldKey]['fieldType'] = $fieldVal['fieldType'];
        $contactOverview[$settingsKey]['fields'][$fieldKey]['inputType'] = $fieldVal['inputType'];
        $contactOverview[$settingsKey]['fields'][$fieldKey]['itemSortOrder'] = $fieldVal['itemSortOrder'];
        // profile pic/contact field image common function to get image path
        if($fieldVal['inputType'] =='fileupload' || $fieldVal['inputType'] =='imageupload'){
            $pathService = $this->container->get('fg.avatar');
            $imageContactfield =  $pathService->getContactfieldPath($fieldVal['fieldId']);
            $contactOverview[$settingsKey]['fields'][$fieldKey]['imagePath'] = $imageContactfield; 
        }

        return $contactOverview;

    }

    /**
     * Function is used to generate query array for each inner loop in overview
     * @param string $contentArrayForQuery Query array
     * @param string $i                    Counter variable
     * @param string $fieldVal             Field value array
     * @param string $clubId               ClubId
     *
     * @return Array
     */
    private function buildOverviewQueryArray($contentArrayForQuery, $i, $fieldVal, $clubId)
    {
        $contentArrayForQuery[$i]['type'] = $fieldVal['fieldType'];
        $contentArrayForQuery[$i]['id'] = $fieldVal['fieldId'];
        $contentArrayForQuery[$i]['club_id'] = $clubId;
        if ($fieldVal['fieldType'] == 'RF' || $fieldVal['fieldType'] == 'R' || $fieldVal['fieldType'] == 'SS') {
            $contentArrayForQuery[$i]['sub_ids'] = 'all';
            $contentArrayForQuery[$i]['club_id'] = $fieldVal['club_id'];
        }

        return $contentArrayForQuery;
    }

    /**
     * Function is used to call contact list class to generate and get the field values of each categories
     * @param string $contentArrayForQuery Array
     * @param string $contact              ContactId
     * @param string $contactId            Logged user id
     *
     * @return template
     */
    public function getContactFieldValuesForOverview($contentArrayForQuery, $contact, $contactId)
    {
        $club = $this->container->get('club');
        $table = new Tablesettings($this->container, $contentArrayForQuery, $club);
        $aColumns = $table->getColumns();
        $aColumns[] = 'contactname';
        $contactlistClass = new Contactlist($this->container, $contactId, $club);
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition('fg_cm_contact.id=' . $contact);
        $contactlistClass->setColumns($aColumns);
        $listquery = $contactlistClass->getResult();
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        return $contactlistDatas;
    }
    /**
     * Function is used to call contact list class For internal
     * @param string $contentArrayForQuery Array
     * @param string $contact              ContactId
     * @param string $contactId            Logged user id
     *
     * @return template
     */
    public function getInternalContactFieldValuesForOverview($contentArrayForQuery, $contact, $contactId)
    {
        $club = $this->container->get('club');
        $table = new Tablesettings($this->container, $contentArrayForQuery, $club);
        $aColumns = $table->getColumns();
        $contactlistClass = new Contactlist($this->container, $contactId, $club);
         $contactlistClass->contactType ='all';
        $contactlistClass->setCount();
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        $contactlistClass->addCondition('fg_cm_contact.id=' . $contact);
        $contactlistClass->setColumns($aColumns);
        $listquery = $contactlistClass->getResult();
        $contactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        return $contactlistDatas;
    }
    /**
     * Function is used to get all membership log datas
     * @param array  $clubId          ClubId
     * @param string $commonSortOrder Common Sort order
     *
     * @return template
     */
    public function getServiceAssignmentsArray($clubId, $commonSortOrder)
    {
        // Query for getting all categories
        // The sort order($commonSortOrder) should be the continuation of the previous one.
        $getAllSmCategories = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllSmCategories($clubId);
        $newSponsorCategoryArray = array();
        $newSponsorCategoryArray['serviceAssignment']['displayFlag'] = 1;
        $newSponsorCategoryArray['serviceAssignment']['settingsType'] = 'serviceAssignment';
        $newSponsorCategoryArray['serviceAssignment']['sortorder'] = $commonSortOrder + 1;
        $newSponsorCategoryArray['serviceAssignment']['displayArea'] = 'left';
        $newSponsorCategoryArray['serviceAssignment']['title'] = $this->container->get('translator')->trans('SPONSOR_OVERVIEW_SETTINGS_SERVICE_ASSIGNMENTS');

        foreach ($getAllSmCategories as $category) {
            $newSponsorCategoryArray = $this->getFieldContentArray($newSponsorCategoryArray, 'serviceAssignment', $category['categoryId'], 'SS', 1, 0, $category['categoryId'], $clubId, $category['catTitle']);
        }

        return $newSponsorCategoryArray;
    }

    /**
     * Function is used to get all service assignments for sponsor overviewas
     * @param string $conn    Connection Object
     * @param string $clubId  ClubId
     * @param string $sponsor Sponsor Id
     *
     * @return Array
     */
    public function getSponsorOverviewServiceAssignments($conn, $clubId, $sponsor)
    {
        $allServiceAssignments = json_encode($this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllServiceAssignments($conn, $clubId, $sponsor, $this->container));

        return $allServiceAssignments;
    }

    /**
     * This function is used to get the sponsor deatils of a particular contact
     * @param int $clubId  ClubId
     * @param int $contact Contactid
     *
     * @return json Array of sponsor's detail
     */
    public function getSponsorDetails($clubId, $contact)
    {
        $sponsoredByDetails = $this->em->getRepository('CommonUtilityBundle:FgSmBookingDeposited')->getAllServicesDetailsOfContact($clubId, $contact);

        return json_encode($sponsoredByDetails);
    }
}
