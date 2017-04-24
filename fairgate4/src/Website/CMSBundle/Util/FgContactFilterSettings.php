<?php

/**
 * FgCmsPortrait
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Iterator\FgArrayIterator;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ContactBundle\Util\FgFilterData;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

/**
 * FgCmsPortrait - The wrapper class to handle functionalities on contact portrait  elements wizard steps
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgContactFilterSettings
{

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubType = $this->club->get('type');
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->em = $this->container->get('doctrine')->getManager();
        $this->translator = $this->container->get('translator');
    }

    /**
     * This method is used to fetch filter data for contact table
     *
     * @param int $tableId Contact table id
     *
     * @return array $filterData
     */
    public function getFilterData($tableId)
    {
        $clubLanguage = $this->club->get('club_default_lang');
        $federationId = $this->club->get('federation_id');
        $subFederationId = $this->club->get('sub_federation_id');
        $filterData = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableFilter')->getTablefilterData($tableId, $clubLanguage);
        $allFilterData = $this->getAllFilterDetails();
        $result = array();
        foreach ($filterData as $data) {
            $sortOrder = $data['sortOrder'];
            switch ($data['filterType']) {
                case 'contact_field' :
                    $contactFieldIds = array_column($allFilterData['CF']['entry'], 'id');
                    $columnKey = in_array($data['attribute'], $contactFieldIds) ? array_search($data['attribute'], $contactFieldIds) : '';
                    if ($columnKey !== '') {
                        $result[$sortOrder]['value'] = $allFilterData['CF']['entry'][$columnKey];
                        $result[$sortOrder]['value']['title'] = $data['title'];
                        $result[$sortOrder]['value']['type'] = 'CF';
                    }
                    break;
                case 'fed_memberships' :
                case 'memberships' :
                    $fType = $data['filterType'] == 'memberships' ? 'CM' : 'FM';
                    if ($allFilterData[$fType]['entry']) {
                        $result[$sortOrder]['value'] = $allFilterData[$fType]['entry'];
                        $result[$sortOrder]['value']['title'] = $data['title'];
                        $result[$sortOrder]['value']['type'] = $fType;
                    }
                    break;
                case 'workgroups' :
                    $fType = $data['filterType'] == 'filter_roles' ? 'FILTERROLES-' . $this->clubId : 'WORKGROUP';
                    if ($data['filterSubtype'] == 'ALL') {
                        $result[$sortOrder]['value'] = $allFilterData['WORKGROUP']['entry'][0];
                        $result[$sortOrder]['value']['title'] = $data['title'];
                        $result[$sortOrder]['value']['type'] = $fType;
                    } else {
                        $subTypeIds = explode(',', $data['filterSubtype']);
                        $wgData = $allFilterData[$fType]['entry'][0];
                        $wgData['input'] = $this->getSelectedWorkgroups($wgData['input'], $subTypeIds);
                        $result[$sortOrder]['value'] = $wgData;
                        $result[$sortOrder]['value']['title'] = $data['title'];
                        $result[$sortOrder]['value']['type'] = $fType;
                    }
                    break;
                case 'filter_roles' :
                    $fType = 'FILTERROLES-' . $this->clubId;
                    if ($data['filterSubtype'] == 'ALL') {
                        $result[$sortOrder]['value']['input'] = $this->getSelectedFilterRoles($allFilterData[$fType]['entry'], 'ALL');
                    } else {
                        $subTypeIds = explode(',', $data['filterSubtype']);
                        $result[$sortOrder]['value']['input'] = $this->getSelectedFilterRoles($allFilterData[$fType]['entry'], $subTypeIds);
                    }
                    $result[$sortOrder]['value']['title'] = $data['title'];
                    $result[$sortOrder]['value']['type'] = $fType;
                    break;
                case 'team_category' :
                case 'role_category' :
                case 'fed_role_category' :
                case 'subfed_role_category' :
                    if ($data['filterType'] == 'role_category') {
                        $fType = 'ROLES-' . $this->clubId;
                    } elseif ($data['filterType'] == 'fed_role_category') {
                        $fType = 'FROLES-' . $federationId;
                    } elseif ($data['filterType'] == 'subfed_role_category') {
                        $fType = 'FROLES-' . $subFederationId;
                    } else {
                        $fType = 'TEAM';
                    }
                    $teamCatData = $allFilterData[$fType]['entry'];
                    $teamCatId = $data['filterSubtype'];
                    $selectedTeams = $this->getSelectedTeams($teamCatData, $teamCatId);
                    if ($selectedTeams) {
                        $result[$sortOrder]['value'] = $selectedTeams;
                        $result[$sortOrder]['value']['title'] = $data['title'];
                        $result[$sortOrder]['value']['type'] = $fType;
                    }
                    break;
            }
        }

        return $result;
    }

    /**
     * This method is used to fetch all filter details.
     *
     * @return array
     */
    private function getAllFilterDetails()
    {
        $objMembershipPdo = new membershipPdo($this->container);
        $federationId = $this->club->get('federation_id');
        $subFederationId = $this->club->get('sub_federation_id');
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        $getFilterRoles = true;
        $filterClass = new FgFilterData($this->container);
        
        /* Get all club roles, filter roles and workgroups */
        $rowRoleArray = $objMembershipPdo->getAllCategoryRoleFunction($this->club, 'filteronly', $getFilterRoles, $executiveBoardTitle);
        $clubRoleArray = $filterClass->iterateRoles($rowRoleArray);
        /* Get all teams */
        $allTeams = $objMembershipPdo->getAllTeamCategryDeatails($this->club);
        $teamDetails = $filterClass->iterateRoles($allTeams);

        
        $rowMemberships = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $subFederationId, $federationId, 0);
        $fedmembership = $this->iterateMemberships($rowMemberships, true);

        $memberships = $fedmembership = array();
        if ($this->clubType != 'standard_club') {
            if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') && $this->club->get('clubMembershipAvailable')) {
                $memberships = $this->iterateMemberships($rowMemberships);
            }

            $fedmembership = $this->iterateMemberships($rowMemberships, true);
        } else {
            $memberships = $this->iterateMemberships($rowMemberships);
        }

        $filterData = $this->membershipFields($terminologyService, $fedmembership, $memberships);
        $filterData['CF'] = $this->contactField();
        $filterData['SI'] = $this->systemInfo();
        $filterData['AF'] = $filterClass->analysisFields();
        $filterData = array_merge($filterData, $clubRoleArray);
        $filterData = array_merge($filterData, $teamDetails);

        return $filterData;
    }

    /**
     * This method is used to filter selected worgroup details.
     *
     * @param array $wgData All workgroup filter data
     * @param array $selectedIds seleceted workgroup ids
     *
     * @return array filtered workgroup data
     */
    private function getSelectedWorkgroups($wgData, $selectedIds)
    {
        $result = array();
        foreach ($selectedIds as $id) {
            $key = array_search($id, array_column($wgData, 'id'));
            $result[$key] = $wgData[$key];
        }

        return $result;
    }

    /**
     * This method is used to filter selected filter role details.
     *
     * @param array $filterRoleData All filter role data
     * @param array $selectedIds seleceted filterrole ids
     *
     * @return array filtered workgroup data
     */
    private function getSelectedFilterRoles($filterRoleData, $selectedIds)
    {
        $frData = array();
        foreach ($filterRoleData as $fEntry) {
            $frData = array_merge($frData, $fEntry['input']);
        }
        if ($selectedIds == 'ALL') {
            return $frData;
        }
        $result = array();
        foreach ($selectedIds as $id) {
            $key = array_search($id, array_column($frData, 'id'));
            $result[$key] = $frData[$key];
        }

        return $result;
    }

    /**
     * This method is used to filter selected worgroup details.
     *
     * @param type $teamData All team filter data
     * @param type $teamCatId selected team category id
     *
     * @return array filtered team data
     */
    private function getSelectedTeams($teamData, $teamCatId)
    {
        $key = array_search($teamCatId, array_column($teamData, 'id'));

        return $teamData[$key];
    }

    /**
     * Function to iterate membership categories in a club *
     *
     * @param Array   $rowMemberships memberships
     * @param boolean $isLogo         Flag for logo
     *
     * @return array $resultArray
     */
    public function iterateMemberships($rowMemberships, $isLogo = false)
    {
        $fedId = $this->club->get('federation_id');
        $federationId = $this->clubType == "federation" ? $this->clubId : $fedId;
        $corr = $this->contact->get('corrLang');
        $memberships = array();
        $title = '';
        foreach ($rowMemberships as $id => $rowmembership) {
            if ($isLogo == true) {//fed membership
                if ($rowmembership['clubId'] == $federationId) {
                    $title = $rowmembership['allLanguages'][$corr]['titleLang'] != '' ? $rowmembership['allLanguages'][$corr]['titleLang'] : $rowmembership['membershipName'];
                    $memberships[] = array('id' => $id, 'title' => $title, 'itemType' => 'fed_membership', 'bookMarkId' => $rowmembership['bookmarkId'], 'draggable' => 1);
                }
            } else {//club membership
                if ($rowmembership['clubId'] == $this->clubId) {
                    $title = $rowmembership['allLanguages'][$corr]['titleLang'] != '' ? $rowmembership['allLanguages'][$corr]['titleLang'] : $rowmembership['membershipName'];
                    $memberships[] = array('id' => $id, 'title' => $title, 'itemType' => 'membership', 'bookMarkId' => $rowmembership['bookmarkId'], 'draggable' => 1);
                }
            }
        }

        return $memberships;
    }

    /**
     * membership/fed membership
     *
     * @param object $terminologyService    terminology Service
     * @param array $fedmemberships         fedmemberships
     * @param array $memberships            memberships
     * @return array
     */
    public function membershipFields($terminologyService, $fedmemberships, $memberships)
    {
        $filterData = array();
        $federationTerTitle = $terminologyService->getTerminology('Federation', $this->container->getParameter('singular'));
        if ($this->clubType == 'standard_club' || $this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club') {
            if ($this->club->get('clubMembershipAvailable') || $this->clubType == 'standard_club') {
                $comInput = array();
                $comInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_MEMBER'));
                $comInput[] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_MEMBER'));
                $filterData['CM']['title'] = $this->translator->trans('CM_MEMBERSHIP');
                $filterData['CM']['id'] = 'CM';
                $filterData['CM']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_OPTIONS'));
                $filterData['CM']['entry'][] = array('id' => 'membership', 'name' => 'CMmembership', 'title' => $this->translator->trans('CM_MEMBERSHIP'), 'type' => 'select', 'input' => array_merge($comInput, $memberships), 'shortTitle' => $this->translator->trans('CM_MEMBERSHIP'));
                $filterData['CM']['entry'][] = array('id' => 'CMfirst_joining_date', 'name' => 'CMfirst_joining_date', 'title' => $this->translator->trans('CM_FIRST_JOINING_DATE'), 'type' => 'date', 'shortTitle' => $this->translator->trans('CM_FIRST_JOINING_DATE_SHORTNAME'));
                $filterData['CM']['entry'][] = array('id' => 'CMjoining_date', 'name' => 'CMjoining_date', 'title' => $this->translator->trans('CM_LATEST_JOINING_DATE'), 'type' => 'date', 'shortTitle' => $this->translator->trans('CM_LATEST_JOINING_DATE'));
                $filterData['CM']['entry'][] = array('id' => 'CMleaving_date', 'name' => 'CMleaving_date', 'title' => $this->translator->trans('CM_LEAVING_DATE'), 'type' => 'date', 'shortTitle' => $this->translator->trans('CM_LEAVING_DATE'));
                $filterData['CM']['entry'][] = array('id' => 'CMclub_member_years', 'name' => 'club_member_years', 'shortTitle' => $this->translator->trans('MEMBERSHIP_YEARS'), 'title' => $this->translator->trans('MEMBERSHIP_YEARS'), 'type' => 'text', 'show_filter' => 0);
            }
        }
        if ($this->clubType != 'standard_club') {
            $comInput = array();
            $comInput[] = array('id' => '', 'title' => $this->translator->trans('FM_SELECT_MEMBER'));
            $comInput[] = array('id' => 'any', 'title' => $this->translator->trans('FM_ANY_MEMBER'));
            $filterData['FM']['title'] = ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')));
            $filterData['FM']['id'] = 'FM';
            $filterData['FM']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_OPTIONS'));
            $filterData['FM']['entry'][] = array('id' => 'fed_membership', 'name' => 'FMfed_membership', 'title' => ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('plural'))), 'type' => 'select', 'input' => array_merge($comInput, $fedmemberships), 'shortTitle' => ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular'))));
            $filterData['FM']['entry'][] = array('id' => 'FMfirst_joining_date', 'name' => 'FMfirst_joining_date', 'title' => $this->translator->trans('FM_FIRST_JOINING_DATE', array('%federation%' => $federationTerTitle)), 'type' => 'date', 'shortTitle' => $this->translator->trans('FM_FIRST_JOINING_DATE_SHORTNAME', array('%federation%' => $federationTerTitle)));
            $filterData['FM']['entry'][] = array('id' => 'FMjoining_date', 'name' => 'FMjoining_date', 'title' => $this->translator->trans('FM_JOINING_DATE', array('%federation%' => $federationTerTitle)), 'type' => 'date', 'shortTitle' => $this->translator->trans('FM_JOINING_DATE', array('%federation%' => $federationTerTitle)));
            $filterData['FM']['entry'][] = array('id' => 'FMleaving_date', 'name' => 'FMleaving_date', 'title' => $this->translator->trans('FM_LEAVING_DATE', array('%federation%' => $federationTerTitle)), 'type' => 'date', 'shortTitle' => $this->translator->trans('FM_LEAVING_DATE', array('%federation%' => $federationTerTitle)));
            $filterData['FM']['entry'][] = array('id' => 'FMfed_member_years', 'name' => 'fed_member_years', 'shortTitle' => $this->translator->trans('FM_MEMBERSHIP_YEARS', array('%federation%' => ucfirst($federationTerTitle))), 'title' => $this->translator->trans('MEMBERSHIP_YEARS'), 'type' => 'text', 'show_filter' => 0);
        }
        return $filterData;
    }

    /**
     * contact field and system info
     * @return array
     */
    public function contactField()
    {
        $filterData = array();
        /* Append Formated Contact fields to filter array */
        $filterData['CF']['title'] = $this->translator->trans('CM_CONTACT_FIELDS');
        $filterData['CF']['id'] = 'CF';
        $filterData['CF']['has_separator'] = 1;
        $filterData['CF']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_CONTACT_FIELD'));
        $filterData['CF']['fixed_options'][1][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_VALUE'));
        $filterData['CF']['entry'] = $this->clubField();
        // pass additional data for inline editing
        if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') && $this->club->get('clubMembershipAvailable')) {
            $filterData['CF']['entry'][] = array('id' => 'joining_date', 'title' => $this->translator->trans('CM_LATEST_JOINING_DATE'), 'shortName' => $this->translator->trans('CM_JOINING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0);
            $filterData['CF']['entry'][] = array('id' => 'leaving_date', 'title' => $this->translator->trans('CM_LEAVING_DATE'), 'shortName' => $this->translator->trans('CM_LEAVING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0);
        }
        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
            $filterData['CF']['entry'][] = array('id' => 'joining_date', 'title' => $this->translator->trans('CM_LATEST_JOINING_DATE'), 'shortName' => $this->translator->trans('CM_JOINING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0);
            $filterData['CF']['entry'][] = array('id' => 'leaving_date', 'title' => $this->translator->trans('CM_LEAVING_DATE'), 'shortName' => $this->translator->trans('CM_LEAVING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0);
        }
        //Contact fields

        return $filterData['CF'];
    }

    public function systemInfo()
    {
        $filterData = array();
        /* Append System infos to filter array */
        $filterData['SI']['title'] = $this->translator->trans('CM_SYSTEM_INFOS');
        $filterData['SI']['id'] = 'SI';
        $filterData['SI']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_INFORMTAION_FIELD'));
        $filterData['SI']['entry'][] = array('id' => 'member_id', 'title' => $this->translator->trans('CM_CONTACT_ID'), 'type' => 'number');
        $filterData['SI']['entry'][] = array('id' => 'created_at', 'title' => $this->translator->trans('CM_CREATED_ON'), 'type' => 'date');
        $filterData['SI']['entry'][] = array('id' => 'last_updated', 'title' => $this->translator->trans('CM_LAST_UPDATED'), 'type' => 'date');
        $filterData['SI']['entry'][] = array('id' => 'last_login', 'title' => $this->translator->trans('CONTACT_OVERVIEW_SETTINGS_LAST_LOGIN'), 'type' => 'date');

        return $filterData['SI'];
    }

    /**
     * get the club fields
     * @return array
     */
    private function clubField()
    {
        /* Get all contact fields for a club */
        $this->clubLanguages = $this->club->get('club_languages');
        $nationality1 = $this->container->getParameter('system_field_nationality1');
        $nationality2 = $this->container->getParameter('system_field_nationality2');
        $correspondaceLand = $this->container->getParameter('system_field_corres_land');
        $invoiceLand = $this->container->getParameter('system_field_invoice_land');
        $corresLang = $this->container->getParameter('system_field_corress_lang');
        $gender = $this->container->getParameter('system_field_gender');
        $salutation = $this->container->getParameter('system_field_salutaion');
        $fieldLanguages = FgUtility::getClubLanguageNames($this->clubLanguages);
        $countryList = FgUtility::getCountryListchanged();
        $rowFieldsArray = $this->club->get('contactFields');
        /* Contact field Saluations values with translation */
        $salutationTrans = array();
        $salutationTrans[] = array('id' => 'Informal', 'title' => $this->translator->trans('CM_INFORMAL'));
        $salutationTrans[] = array('id' => 'Formal', 'title' => $this->translator->trans('CM_FORMAL'));


        /* Contact field Gender values with translation */
        $genderTrans = array();
        $genderTrans[] = array('id' => 'Male', 'title' => $this->translator->trans('CM_MALE'));
        $genderTrans[] = array('id' => 'Female', 'title' => $this->translator->trans('CM_FEMALE'));
        /* Iterate contact field resultset to format data to be used in filter */
        $iterator = new \RecursiveArrayIterator($rowFieldsArray);
        $newiterator = new FgArrayIterator($iterator);
        $newiterator->translator = $this->translator;
        $newiterator->correspondancelang = $corresLang;
        $newiterator->nationality1 = $nationality1;
        $newiterator->nationality2 = $nationality2;
        $newiterator->correspondaceLand = $correspondaceLand;
        $newiterator->invoiceLand = $invoiceLand;
        $newiterator->gender = $gender;
        $newiterator->salutation = $salutation;
        $newiterator->genderTrans = $genderTrans;
        $newiterator->salutationTrans = $salutationTrans;
        foreach ($fieldLanguages as $lanKey => $lanValue) {
            $newiterator->clubLanguages[] = array('id' => $lanKey, 'title' => $lanValue);
        }
        foreach ($countryList as $cnKey => $cnValue) {
            $newiterator->countryList[] = array('id' => $cnKey, 'title' => $cnValue);
        }
        $newiterator->filterType = 'contactFields';
        /** This is an empty iterator  to iterate FgArrayIterator * */
        foreach ($newiterator as $key => $item) {
            //iterate
        }
        $clubFieldsArray = $newiterator->getResult();

        return $clubFieldsArray;
    }
}
