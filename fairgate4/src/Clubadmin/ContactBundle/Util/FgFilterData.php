<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Iterator\FgArrayIterator;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\SponsorBundle\Util\Sponsorfilter;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * This class is used for managing contact module - {filters, column setting, sidebar}, sponsor- filter
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class FgFilterData
{

    /**
     * $container
     *
     * @var object {container object}
     */
    private $container;

    /**
     * $club
     *
     * @var object {club object}
     */
    private $club;

    /**
     * clubId
     *
     * @var int ClubId
     */
    private $clubId;

    /**
     * federationId
     *
     * @var int federationId
     */
    private $federationId;

    /**
     * subFederationId
     *
     * @var int subFederationId
     */
    private $subFederationId;

    /**
     * $clubType
     *
     * @var int clubType
     */
    private $clubType;

    /**
     * contact
     *
     * @var object {contact object}
     */
    private $contact;

    /**
     * contactId
     *
     * @var int ContactId
     */
    private $contactId;

    /**
     * $em
     *
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * terminology array building
     *
     * @param object $container
     * @return array
     */
    private $terminologyArray = array();

    /**
     * translator object
     *
     * @var object
     */
    private $translator;

    /**
     * request object
     *
     * @var object
     */
    private $request;

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container, $request)
    {
        $this->request = $request;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubType = $this->club->get('type');
        $this->clubId = $this->club->get('id');
        $this->federationId = $this->club->get('federation_id');
        $this->subFederationId = $this->club->get('sub_federation_id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->container->get('contact')->get('id');

        $this->terminologyArray = $this->terminologyArray();
        $this->translator = $this->container->get('translator');

        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * terminology Array
     *
     * @return array
     */
    private function terminologyArray()
    {
        $terminologyArray = array();
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $terminologyArray['FedMembership'] = ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')));
        $terminologyArray['FedMembershipPlural'] = ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('plural')));
        $terminologyArray['subfederationTitlePlural'] = ucfirst($terminologyService->getTerminology('Sub-federation', $this->container->getParameter('plural')));
        $terminologyArray['teamterTitle'] = ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('plural')));
        $terminologyArray['executiveBoardTitle'] = ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));
        $terminologyArray['FederationMember'] = ucfirst($terminologyService->getTerminology('Federation member', $this->container->getParameter('plural')));
        $terminologyArray['clubTitle'] = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular')));

        return $terminologyArray;
    }

    /**
     * build the filter data
     *
     * @return array
     */
    public function buildFilterData()
    {
        /* Get all memberships */
        $objMembershipPdo = new membershipPdo($this->container);
        $rowMemberships = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId, $this->contactId);

        $memberships = $fedmembership = array();
        if ($this->clubType != 'standard_club') {
            if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') && $this->club->get('clubMembershipAvailable'))
                $memberships = $this->iterateMemberships($rowMemberships);

            $fedmembership = $this->iterateMemberships($rowMemberships, true);
        }else {
            $memberships = $this->iterateMemberships($rowMemberships);
        }

        $getFilterRole = $this->request->get('getFilterRole', 'true');
        $hasSponsorCriteria = $this->request->get('hasSponsorCriteria', 'false');
        $getFilterRoles = ($getFilterRole == 'true') ? true : false;

        $filterData = $this->membershipFields($fedmembership, $memberships);
        $filterData['CF'] = $this->contactField();
        $filterData['SI'] = $this->systemInfo();
        $filterData['CO'] = $this->contactOption();
        $filterData['CC'] = $this->connectionData();
        $filterData['AF'] = $this->analysisFields();
        $filterData = $this->rolesTeamWorkgroupFields($filterData, $getFilterRoles, $objMembershipPdo, $hasSponsorCriteria);
        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
            $filterData['FI'] = $this->federationInfo($hasSponsorCriteria);
        }
        $filterData['bookmark'] = $this->bookmarkFilter();
        $filterData['filter'] = $this->filterData();

        /* contact data collection */
        $contactData = $this->getContactFilterData($memberships, $fedmembership);
        $filterData['CN'] = $contactData['CN'];

        if ($hasSponsorCriteria == 'true' && in_array('sponsor', $this->club->get('bookedModulesDet'))) {
            $sponsorCriteria = $this->getSponsorCriteria();
            $filterData = array_merge($filterData, $sponsorCriteria);
        }

        return $filterData;
    }

    /**
     * build array for roles team workgroup
     *
     * @param array     $filterData         filter Data
     * @param array     $getFilterRoles     get Filter Roles
     * @param object    $objMembershipPdo   membership pdo object
     * @param boolean   $hasSponsorCriteria has Sponsor Criteria
     *
     * @return array
     */
    private function rolesTeamWorkgroupFields($filterData, $getFilterRoles, $objMembershipPdo, $hasSponsorCriteria)
    {
        /* collect team details */
        $allTeams = $objMembershipPdo->getAllTeamCategryDeatails($this->club);
        $teamSDetails = $this->iterateRoles($allTeams);
        $teamDetails = $this->iterateRolesWithAnyOption($teamSDetails);
        $teamArray = (count($allTeams) > 0 ? $teamDetails['TEAM'] : array('show_filter' => 0, 'entry' => array()));

        $teamArray['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_CATEGORY'));
        $teamArray['title'] = $this->terminologyArray['teamterTitle'];
        $teamArray['id'] = 'TEAM';
        $teamArray['functionCount'] = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getTeamFunctionCount($this->club->get('club_team_id'));

        $teamArray['fixed_options'][1][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_TEAM'));
        $teamArray['fixed_options'][2][] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_FUNCTION'));
        $teamArray['fixed_options'][2][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_FUNCTION'));
        /* Set fixed options for  ROLE array */
        $filterData['ROLES-' . $this->clubId] = $this->getFixedOptions($this->clubId, $this->clubType, 'ROLES-' . $this->clubId, 'ROLE', $this->translator->trans('CM_ROLES'));

        /* Get all club roles */
        $rowRoleArray = $objMembershipPdo->getAllCategoryRoleFunction($this->club, 'filteronly', $getFilterRoles, $this->terminologyArray['executiveBoardTitle']);
        $clubRoleSArray = $this->iterateRoles($rowRoleArray);
        $clubRoleArray = $this->iterateRolesWithAnyOption($clubRoleSArray);
        $this->calcRoleWgTeamArray($clubRoleArray, $filterData, $teamArray, $hasSponsorCriteria,$getFilterRoles);

        /* according to the team category and function , change the show-filter flag */
        if (count($filterData['TEAM']['entry']) > 0 && (isset($filterData['TEAM']['entry'][0]['input']) && count($filterData['TEAM']['entry'][0]['input']) > 0)) {
            $filterData['TEAM']['show_filter'] = 1;
        }

        return $filterData;
    }

    /**
     * sub calculation of team workgroup role
     *
     * @param array $clubRoleArray  club Role Array
     * @param array $filterData     filter Data
     *
     * @return array
     */
    private function calcRoleWgTeamArray($clubRoleArray, &$filterData, $teamArray, $hasSponsorCriteria, $getFilterRoles)
    {
        $cluHeirarchyDet = $this->club->get('clubHeirarchyDet');
        foreach ($clubRoleArray as $key => $roleArray) {
            $roleArray['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_CATEGORY'));
            /* Workgroup data */
            switch ($key) {
                case 'WORKGROUP':
                    if (!isset($filterData['FILTERROLES-' . $this->clubId])) {
                        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
                            if (!isset($filterData['FROLES-' . $this->clubId])) {
                                $filterData['FROLES-' . $this->clubId] = $this->createFedRoles($filterData);
                            }
                        }
                        if ($getFilterRoles) {
                            //echo "title:".$this->translator->trans('FILTER_ROLES')." #key:".'FILTERROLES-' . $this->clubId;
                            $filterData['FILTERROLES-' . $this->clubId] = $this->getFixedOptions($this->clubId, $this->clubType, 'FILTERROLES-' . $this->clubId, 'FILTERROLE', $this->translator->trans('FILTER_ROLES'));
                        }
                    }
                    $filterData['TEAM'] = $teamArray;
                    $roleArray['title'] = $this->translator->trans('CM_WORKGROUPS');
                    $roleArray['id'] = 'WORKGROUP';
                    $roleArray['entry'][0]['title'] = $this->translator->trans('CM_WORKGROUP_FILTER');
                    $roleArray['fixed_options'][1][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_WORKGROUP'));
                    break;
                default:
                    list($keyType, $roleClubId) = explode('-', $key);
                    /* Federation or sub federation roles data */
                    if ($keyType == 'FROLES') {
                        if (!isset($filterData['ROLES-' . $this->clubId])) {
                            $filterData['ROLES-' . $this->clubId] = $this->getFixedOptions($this->clubId, $this->clubType, 'ROLES-' . $this->clubId, 'ROLE', $this->translator->trans('CM_ROLES'));
                        }
                        $clubType = ($this->clubId == $roleClubId) ? $this->clubType : $cluHeirarchyDet[$roleClubId]['club_type'];
                        switch ($clubType) {
                            case 'federation':
                                $roleArray['title'] = ucfirst($this->translator->trans('CM_FEDERATION_ROLES'));
                                $roleArray['id'] = "FROLES-" . $roleClubId;
                                $roleArray['logo'] = FgUtility::getClubLogo($roleClubId, $this->em);
                                $roleArray['show_filter'] = 1;
                                break;
                            case 'sub_federation':
                                $roleArray['title'] = ucfirst($this->translator->trans('CM_SUB_FEDERATION_ROLES'));
                                $roleArray['id'] = "FROLES-" . $roleClubId;
                                $roleArray['logo'] = FgUtility::getClubLogo($roleClubId, $this->em);
                                $roleArray['show_filter'] = 1;
                                break;
                        }
                        /* Filter roles roles data */
                    } elseif ($keyType == 'FILTERROLES') {
                        if (!isset($filterData['FROLES-' . $this->clubId]) && ($this->clubType == 'federation' || $this->clubType == 'sub_federation')) {
                            $filterData['FROLES-' . $this->clubId] = $this->createFedRoles($filterData);
                        }
                        $roleArray['title'] = $this->translator->trans('FILTER_ROLES');
                        $roleArray['id'] = 'FILTERROLES-' . $roleClubId;
                    } else {
                        $roleArray['title'] = $this->translator->trans('CM_ROLES');
                        $roleArray['id'] = 'ROLES-' . $roleClubId;
                    }

                    $roleArray['fixed_options'][1][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_ROLE'));
                    break;
            }
            $roleArray['fixed_options'][2][] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_FUNCTION'));
            $roleArray['fixed_options'][2][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_FUNCTION'));
            $filterData[$key] = $roleArray;
        }
       
        if ($hasSponsorCriteria == 'true' && in_array('sponsor', $this->club->get('bookedModulesDet')) && !in_array($this->clubType, array('federation', 'sub_federation'))) {
            $filterData[$key]['has_separator'] = 1;
        }
    }

    /**
     * Function to iterate connection relations in a club
     *
     * @param Array $relations the array of contact relations
     *
     * @return array $resultArray
     */
    private function iterateRelations($relations)
    {
        $houshold = array();
        $other = array();
        foreach ($relations as $rowConnection) {
            if ($rowConnection['is_household'] == 1) {
                $houshold[] = array('id' => $rowConnection['id'], 'title' => $rowConnection['name']);
            }
            if ($rowConnection['is_other_personal'] == 1) {
                $other[] = array('id' => $rowConnection['id'], 'title' => $rowConnection['name']);
            }
        }
        $relationArray = array();
        $relationArray['household'] = $houshold;
        $relationArray['other'] = $other;

        return $relationArray;
    }

    /**
     * Function to iterate connection relations in a club *
     *
     * @param Array $rowClubs clubs
     *
     * @return array $resultArray
     */
    private function iterateClubs($rowClubs)
    {
        $subfederations = array();
        $clubs = array();
        foreach ($rowClubs as $rowClub) {
            if ($rowClub['is_sub_federation'] == 1) {
                $subfederations[] = array('id' => $rowClub['id'], 'title' => $rowClub['title']);
            } else {
                $clubs[] = array('id' => $rowClub['id'], 'title' => $rowClub['title']);
            }
        }
        $clubsArray = array();
        $clubsArray['subfederation'] = $subfederations;
        $clubsArray['club'] = $clubs;

        return $clubsArray;
    }

    /**
     * Function to iterate membership categories in a club *
     *
     * @param Array $rowMemberships memberships
     *
     * @return array $resultArray
     */
    private function iterateMemberships($rowMemberships, $isLogo = false)
    {
        $federationId = $this->clubType == "federation" ? $this->clubId : $this->federationId;
        $corr = $this->contact->get('corrLang');
        $memberships = array();
        $title = '';
        foreach ($rowMemberships as $id => $rowmembership) {
            if ($isLogo == true) {//fed membership
                if ($rowmembership['clubId'] == $federationId) {
                    $title = $rowmembership['allLanguages'][$corr]['titleLang'] != '' ? $rowmembership['allLanguages'][$corr]['titleLang'] : $rowmembership['membershipName']; //. ' <img class="fa-envelope-o" src=' . $logoPath . ' />'
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
     * Bookmark filter
     *
     * @return array
     */
    private function bookmarkFilter()
    {
        $filterData = array();
        /* Set the bookmark details in json array */
        $bookMarkArray = $this->getContactBookmarklist();
        $bookMarkDetails = $this->iterateBookmark($bookMarkArray);
        $filterData['bookmark']['show_filter'] = 0;
        $filterData['bookmark']['id'] = 'bookmark';
        $filterData['bookmark']['title'] = 'bookmark';
        $filterData['bookmark']['entry'] = $bookMarkDetails;

        return $filterData['bookmark'];
    }

    /**
     * filter data
     *
     * @return array
     */
    private function filterData()
    {
        $filterData = array();
        /* Set the filter value in the json array */
        $allSavedFilterArray = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSavedSidebarFilter($this->contactId, $this->clubId);
        $allSavedFilter = $this->iterateFilter($allSavedFilterArray);
        $filterData['filter']['show_filter'] = 0;
        $filterData['filter']['id'] = 'filter';
        $filterData['filter']['title'] = 'filter';
        $filterData['filter']['entry'] = $allSavedFilter;

        return $filterData['filter'];
    }

    /**
     * membership/fed membership
     *
     * @param array $fedmemberships         fedmemberships
     * @param array $memberships            memberships
     * @return array
     */
    private function membershipFields($fedmemberships, $memberships)
    {
        $filterData = array();
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
            $filterData['FM']['title'] = $this->terminologyArray['FedMembership'];
            $filterData['FM']['id'] = 'FM';
            $filterData['FM']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_OPTIONS'));
            $filterData['FM']['entry'][] = array('id' => 'fed_membership', 'name' => 'FMfed_membership', 'title' => $this->terminologyArray['FedMembershipPlural'], 'type' => 'select', 'input' => array_merge($comInput, $fedmemberships), 'shortTitle' => $this->terminologyArray['FedMembership']);
            $filterData['FM']['entry'][] = array('id' => 'FMfirst_joining_date', 'name' => 'FMfirst_joining_date', 'title' => $this->translator->trans('FM_FIRST_JOINING_DATE'), 'type' => 'date', 'shortTitle' => $this->translator->trans('FM_FIRST_JOINING_DATE_SHORTNAME'));
            $filterData['FM']['entry'][] = array('id' => 'FMjoining_date', 'name' => 'FMjoining_date', 'title' => $this->translator->trans('FM_JOINING_DATE'), 'type' => 'date', 'shortTitle' => $this->translator->trans('FM_JOINING_DATE'));
            $filterData['FM']['entry'][] = array('id' => 'FMleaving_date', 'name' => 'FMleaving_date', 'title' => $this->translator->trans('FM_LEAVING_DATE'), 'type' => 'date', 'shortTitle' => $this->translator->trans('FM_LEAVING_DATE'));
            $filterData['FM']['entry'][] = array('id' => 'FMfed_member_years', 'name' => 'fed_member_years', 'shortTitle' => $this->translator->trans('FM_MEMBERSHIP_YEARS'), 'title' => $this->translator->trans('MEMBERSHIP_YEARS'), 'type' => 'text', 'show_filter' => 0);
        }
        return $filterData;
    }

    /**
     * connection data for filter
     *
     * @return array
     */
    private function connectionData()
    {
        $filterData = array();
        /* Iterate Connection relations to format data to be used in filter */
        $rowRelationsArray = $this->em->getRepository('CommonUtilityBundle:FgCmRelation')->getAllRelations($this->clubId, $this->club->get('default_system_lang'));
        $relationsArray = $this->iterateRelations($rowRelationsArray);

        /* Connection data */
        $connectionsArray = array();
        $selectRelation = array(0 => array('id' => '', 'title' => $this->translator->trans('CM_SELECT_RELATION')));
        $hcInput = array();
        $hcInput[] = array('id' => 'household_contact', 'title' => $this->translator->trans('CM_HOUSEHOLD_CONTACT'));
        $connectionsArray[] = array('id' => 'household_contact', 'title' => $this->translator->trans('CM_HOUSEHOLD_CONTACT'), 'type' => 'select', 'input' => $hcInput);
        $hmcInput = array();
        $hmcInput[] = array('id' => 'main_contact', 'title' => $this->translator->trans('CM_MAIN_CONTACT'));
        $connectionsArray[] = array('id' => 'household_main_contact', 'title' => $this->translator->trans('CM_HOUSEHOLD_MAIN_CONTACT'), 'type' => 'select', 'input' => $hmcInput);
        /* Household relation */
        $connectionsArray[] = array('id' => 'household_relation', 'title' => $this->translator->trans('CM_HOUSEHOLD_RELATION'), 'type' => 'select', 'hidecolumn' => 1, 'input' => array_merge($selectRelation, $relationsArray['household']));
        /* Other relation */
        $connectionsArray[] = array('id' => 'other_relation', 'title' => $this->translator->trans('CM_OTHER_SINGLE_PERSON_RELATION'), 'type' => 'select', 'hidecolumn' => 1, 'input' => array_merge($selectRelation, $relationsArray['other']));
        /* company main contact */
        $cmcInput = array();
        $cmcInput[] = array('id' => 'main_contact', 'title' => $this->translator->trans('CM_MAIN_CONTACT'));
        $connectionsArray[] = array('id' => 'company_main_contact', 'title' => $this->translator->trans('CM_COMPANY_MAIN_CONTACT'), 'type' => 'select', 'input' => $cmcInput, 'hidecolumn' => 1);
        /* company function */
        $connectionsArray[] = array('id' => 'company_function', 'title' => $this->translator->trans('CM_COMPANY_FUNCTION'), 'type' => 'text', 'hidecolumn' => 1);
        /* Append Connection data to filter array */
        $filterData['CC']['title'] = $this->translator->trans('CM_CONTACT_CONNECTIONS');
        $filterData['CC']['id'] = 'CC';
        $filterData['CC']['has_separator'] = 1;
        $filterData['CC']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_CONNECTION'));
        $filterData['CC']['entry'] = $connectionsArray;

        return $filterData['CC'];
    }

    /**
     * contact options
     *
     * @param string $federationTerTitle federationTerTitle
     *
     * @return string
     */
    private function contactOption()
    {
        $filterData = array();

        /* Append Contact Options to filter array */
        $filterData['CO']['title'] = $this->translator->trans('CM_CONTACT_OPTIONS');
        $filterData['CO']['id'] = 'CO';
        $filterData['CO']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_OPTIONS'));
        $coInput = array();
        $coInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_VALUE'));
        $coInput[] = array('id' => 'single_person', 'title' => $this->translator->trans('CM_SINGLE_PERSON'));
        $coInput[] = array('id' => 'company', 'title' => $this->translator->trans('CM_COMPANY'));
        $filterData['CO']['entry'][] = array('id' => 'contact_type', 'title' => $this->translator->trans('CM_CONTACT_TYPE'), 'type' => 'select', 'input' => $coInput, 'hidecolumn' => 1);

        if ($this->clubType != 'federation' && $this->clubType != 'standard_club') {
            $fedMemberInput = array();
            $fedmemberInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_FED_MEMBER'));
            $fedmemberInput[] = array('id' => 'yes', 'title' => $this->translator->trans('CM_FED_MEMBER_YES'));
            $fedmemberInput[] = array('id' => 'no', 'title' => $this->translator->trans('CM_FED_MEMBER_NO'));
            $filterData['CO']['entry'][] = array('id' => 'fedmembership', 'title' => $this->terminologyArray['federationTerTitle'] . " " . $this->translator->trans('CM_MEMBERSHIP'), 'type' => 'select', 'input' => $fedmemberInput, 'hidecolumn' => 1);
        }
        if (in_array('communication', $this->club->get('bookedModulesDet'))) {
            $nlInput = array();
            $nlInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_VALUE'));
            $nlInput[] = array('id' => 'yes', 'title' => $this->translator->trans('YES'));
            $nlInput[] = array('id' => 'no', 'title' => $this->translator->trans('NO'));
            $filterData['CO']['entry'][] = array('id' => 'nlsubscription', 'title' => $this->translator->trans('NL_SUBSCRIPTION'), 'type' => 'select', 'input' => $nlInput);
        }
        if (in_array('sponsor', $this->club->get('bookedModulesDet'))) {
            $sponsorInput = array();
            $sponsorInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_VALUE'));
            $sponsorInput[] = array('id' => 'any', 'title' => $this->translator->trans('ANY_SPONSOR'));
            $sponsorInput[] = array('id' => 'prospect', 'title' => $this->translator->trans('PROSPECT'));
            $sponsorInput[] = array('id' => 'future_sponsor', 'title' => $this->translator->trans('FUTURE_SPONSOR'));
            $sponsorInput[] = array('id' => 'active_sponsor', 'title' => $this->translator->trans('ACTIVE_SPONSOR'));
            $sponsorInput[] = array('id' => 'former_sponsor', 'title' => $this->translator->trans('FORMER_SPONSOR'));
            $filterData['CO']['entry'][] = array('id' => 'sponsor', 'title' => $this->translator->trans('SPONSOR'), 'type' => 'select', 'input' => $sponsorInput);
        }
        if (in_array('frontend1', $this->club->get('bookedModulesDet'))) {
            $internalareaaccessInput = array();
            $internalareaaccessInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_VALUE'));
            $internalareaaccessInput[] = array('id' => 'yes', 'title' => $this->translator->trans('YES'));
            $internalareaaccessInput[] = array('id' => 'no', 'title' => $this->translator->trans('NO'));
            $filterData['CO']['entry'][] = array('id' => 'internalareaaccess', 'title' => $this->translator->trans('COLSETT_INTRANET_ACCESS'), 'type' => 'select', 'input' => $internalareaaccessInput);

            $internalareainvisibleInput = array();
            $internalareainvisibleInput[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_VALUE'));
            $internalareainvisibleInput[] = array('id' => 'yes', 'title' => $this->translator->trans('YES'));
            $internalareainvisibleInput[] = array('id' => 'no', 'title' => $this->translator->trans('NO'));
            $filterData['CO']['entry'][] = array('id' => 'internalareainvisible', 'title' => $this->translator->trans('COLSETT_INTERNAL_INVISIBLE'), 'type' => 'select', 'input' => $internalareainvisibleInput);
        }

        if (in_array('invoice', $this->club->get('bookedModulesDet'))) {
            $filterData['CO']['entry'][] = array('id' => 'dispatch_type_dun', 'title' => $this->translator->trans('DISPATCH_TYPE_DUNS'), 'type' => 'select', 'show_filter' => 0);
            $filterData['CO']['entry'][] = array('id' => 'dispatch_type_invoice', 'title' => $this->translator->trans('DISPATCH_TYPE_INVOICES'), 'type' => 'select', 'show_filter' => 0);
        }

        return $filterData['CO'];
    }

    /**
     * contact field and system info
     *
     * @return array
     */
    private function contactField()
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
            $filterData['CF']['entry'][] = array('id' => 'joining_date', 'title' => $this->translator->trans('CM_LATEST_JOINING_DATE'), 'shortName' => $this->translator->trans('CM_JOINING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0, 'hidecolumn' => 1);
            $filterData['CF']['entry'][] = array('id' => 'leaving_date', 'title' => $this->translator->trans('CM_LEAVING_DATE'), 'shortName' => $this->translator->trans('CM_LEAVING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0, 'hidecolumn' => 1);
        }
        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
            $filterData['CF']['entry'][] = array('id' => 'joining_date', 'title' => $this->translator->trans('CM_LATEST_JOINING_DATE'), 'shortName' => $this->translator->trans('CM_JOINING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0);
            $filterData['CF']['entry'][] = array('id' => 'leaving_date', 'title' => $this->translator->trans('CM_LEAVING_DATE'), 'shortName' => $this->translator->trans('CM_LEAVING_DATE'), 'type' => 'date', 'selectgroup' => 'Personal', 'data-edit-type' => 'date', 'show_filter' => 0);
        }
        $filterData['CF']['entry'][] = array('id' => 'profile_company_pic', 'title' => $this->translator->trans('CM_PROFILE_COMPANY_PIC'), 'show_filter' => 0, 'selectgroup' => $this->translator->trans('CM_PROFILE_IMG'));
        //Contact fields

        return $filterData['CF'];
    }

    /**
     * system infos
     *
     * @return array
     */
    private function systemInfo()
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
        $filterData['SI']['entry'][] = array('id' => 'last_invoice_sending', 'title' => $this->translator->trans('CONTACT_OVERVIEW_SETTINGS_LAST_INVOICE_SENDING'), 'type' => 'date', 'show_filter' => 0);

        return $filterData['SI'];
    }

    /**
     * get the club fields
     *
     * @return array
     */
    private function clubField()
    {
        /* Get all contact fields for a club */
        $nationality1 = $this->container->getParameter('system_field_nationality1');
        $nationality2 = $this->container->getParameter('system_field_nationality2');
        $correspondaceLand = $this->container->getParameter('system_field_corres_land');
        $invoiceLand = $this->container->getParameter('system_field_invoice_land');
        $corresLang = $this->container->getParameter('system_field_corress_lang');
        $gender = $this->container->getParameter('system_field_gender');
        $salutation = $this->container->getParameter('system_field_salutaion');
        $fieldLanguages = FgUtility::getClubLanguageNames($this->container->get('club')->get('club_languages'));
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

    /**
     * Method to get array of sponsor criteria options( sponsor analysys & sponsor services)
     *
     * @return array
     */
    private function getSponsorCriteria()
    {
        $result = array();
        $sponsorFilterObj = new Sponsorfilter($this->container, array());
        /* sponserAnalysis  */
        $result['SA'] = $sponsorFilterObj->sponserAnalysis('sponsor');
        /* sponser services  */
        /* option SS is added if sponsor categories with services are existing */
        $sponsorServices = $sponsorFilterObj->sponsorServices('sponsor');
        if (count($sponsorServices) > 0) {
            foreach ($sponsorServices['entry'] as $entry) {
                if (count($entry['input']) > 0) {
                    $result['SS'] = $sponsorServices;
                    break;
                }
            }
        }

        return $result;
    }

    /**
     * Function get all the bookmarks of user
     *
     * @return array
     */
    private function getContactBookmarklist()
    {

        $clubHeirarchy = $this->club->get('clubHeirarchy');
        $execBoardTerm = $this->terminologyArray['executiveBoardTitle'];
        $staticFilterTrans = $this->getStaticFilterTrans();
        $clubExecutiveBoardId = $this->club->get('club_executiveboard_id');
        $objClubPdo = new ClubPdo($this->container);
        $bookmarkDetails = $objClubPdo->getContactBookmarks($this->contactId, $this->clubId, $this->clubType, $clubHeirarchy, $clubExecutiveBoardId, $execBoardTerm, $staticFilterTrans, false, $this->federationId, $this->contact->get('corrLang'));

        return $bookmarkDetails;
    }

    /**
     * Function to get Translated terms of static filters(singleperson/company/member/sponsor
     *
     * @return Array Translated terms of static filters(singleperson/company/member/sponsor
     */
    private function getStaticFilterTrans()
    {

        return array(
            '1' => $this->translator->trans('SINGLE_PERSONE'),
            '2' => $this->translator->trans('CONTACT_PROPERTIES_COMPANIES'),
            '3' => $this->translator->trans('MEMBERS'),
            '4' => $this->terminologyArray['FederationMember']
        );
    }

    /**
     * For create the bookmark array from the bookmark query result
     *
     * @param array $bookmarkArray
     *
     * @return type
     */
    private function iterateBookmark($bookmarkArray)
    {
        $bookmarkDetails = array();
        if (is_array($bookmarkArray) && count($bookmarkArray) > 0) {
            $iCount = 0;
            foreach ($bookmarkArray as $bookmark) {
                //create an array for bookmark on sidebar
                $bookmarkDetails[$iCount]['bookmarkClass'] = 'bookmarked';
                $bookmarkDetails[$iCount]['bookMarkId'] = $bookmark['bookMarkIds'];
                $bookmarkDetails[$iCount]['categoryId'] = $bookmark['roleCategoryId'];
                $bookmarkDetails[$iCount]['contactId'] = $bookmark['contactId'];

                //set the corresponding bookmark value according to the type of bookmark
                switch ($bookmark['type']) {
                    case "filter":
                        $bookmarkDetails[$iCount]['id'] = $bookmark['filterId'];
                        $bookmarkDetails[$iCount]['itemType'] = 'filter';
                        $bookmarkDetails[$iCount]['title'] = $bookmark['filtertitle'];
                        $bookmarkDetails[$iCount]['staticFilter'] = $bookmark['staticFilter'];
                        $bookmarkDetails[$iCount]['menuItemId'] = 'bookmark_li_' . $bookmarkDetails[$iCount]['itemType'] . "_" . $bookmarkDetails[$iCount]['id'];
                        break;
                    case "membership":
                        $bookmarkDetails[$iCount]['id'] = $bookmark['membershipId'];
                        $bookmarkDetails[$iCount]['itemType'] = 'membership';
                        $bookmarkDetails[$iCount]['title'] = $bookmark['membershiptitle'];
                        $bookmarkDetails[$iCount]['menuItemId'] = 'bookmark_li_' . $bookmarkDetails[$iCount]['itemType'] . "_" . $bookmarkDetails[$iCount]['id'];
                        break;
                    case "fed_membership":
                        $bookmarkDetails[$iCount]['id'] = $bookmark['membershipId'];
                        $bookmarkDetails[$iCount]['itemType'] = 'fed_membership';
                        $bookmarkDetails[$iCount]['title'] = $bookmark['membershiptitle'];
                        $bookmarkDetails[$iCount]['menuItemId'] = 'bookmark_li_' . $bookmarkDetails[$iCount]['itemType'] . "_" . $bookmarkDetails[$iCount]['id'];
                        break;
                    case "role":
                        $bookmarkDetails[$iCount]['id'] = $bookmark['roleId'];
                        $bookmarkDetails[$iCount]['title'] = $bookmark['roletitle'];
                        $bookmarkDetails[$iCount]['menuItemId'] = 'bookmark_li_' . $bookmark['roleType'] . '_' . $bookmark['roleCategoryId'] . '_' . $bookmark['roleId'];
                        if ($bookmark['roleType'] == 'ROLES' || $bookmark['roleType'] == 'FROLES' || $bookmark['roleType'] == 'FILTERROLES') {
                            $bookmarkDetails[$iCount]['itemType'] = $bookmark['roleType'] . '-' . $bookmark['roleCatClubId'];
                        } else {
                            $bookmarkDetails[$iCount]['itemType'] = $bookmark['roleType'];
                        }
                        break;
                    default:
                        $bookmarkDetails[$iCount]['id'] = $bookmark['roleId'];
                        $bookmarkDetails[$iCount]['itemType'] = $bookmark['roleType'];
                        $bookmarkDetails[$iCount]['title'] = $bookmark['roletitle'];
                        $bookmarkDetails[$iCount]['menuItemId'] = 'bookmark_li_' . $bookmark['roleType'] . '_' . $bookmark['roleCategoryId'] . '_' . $bookmark['roleId'];
                        break;
                }

                $bookmarkDetails[$iCount]['sortOrder'] = $bookmark['sortOrder'];
                $bookmarkDetails[$iCount]['subCatClubId'] = $bookmark['roleCatClubId'];
                $bookmarkDetails[$iCount]['draggable'] = ($bookmark['draggable'] == 'DRAGGABLE') ? 1 : 0;
                $bookmarkDetails[$iCount]['filterData'] = ($bookmark['filterId'] != '') ? $bookmark['filterData'] : '';
                $iCount ++;
            }
        }

        return $bookmarkDetails;
    }

    /**
     * For iterate the filter query result
     *
     * @param array $filterArray
     *
     * @return type
     */
    private function iterateFilter($filterArray)
    {
        $filterDetails = array();
        if (is_array($filterArray) && count($filterArray) > 0) {
            $iCount = 0;
            foreach ($filterArray as $filter) {
                //create a array for create tbe sidebar of filter
                $filterDetails[$iCount]['bookmarkClass'] = 'fa-bookmark';
                $filterDetails[$iCount]['bookMarkId'] = $filter['bookmarkid'];
                $filterDetails[$iCount]['draggableClass'] = 'fg-dev-non-draggable';
                $filterDetails[$iCount]['isBroken'] = $filter['isBroken'];
                $filterDetails[$iCount]['itemType'] = 'filter';
                $filterDetails[$iCount]['title'] = $filter['filterName'];
                $filterDetails[$iCount]['menuItemId'] = 'filter_li_' . $filter['filterId'];
                $filterDetails[$iCount]['id'] = $filter['filterId'];
                $filterDetails[$iCount]['filterData'] = $filter['filterData'];
                $filterDetails[$iCount]['filterUsed'] = $filter['filterUsed'];
                $iCount ++;
            }
        }

        return $filterDetails;
    }

    /**
     * create static contact array
     *
     * @param array $memberships
     *
     * @return string
     */
    private function getContactFilterData($memberships, $fedmemberships)
    {
        $filterData = array();

        $filterData['CN']['id'] = 'CN';
        $filterData['CN']['show_filter'] = 0;
        $filterData['CN']['title'] = $this->translator->trans('SIDEBAR_MEMBERSHIPS');
        if ($this->clubType == 'standard_club') {
            $filterData['CN']['entry'][] = array('id' => 'membership', 'title' => $this->translator->trans('SIDEBAR_MEMBERSHIPS'), 'type' => 'select', 'input' => $memberships);
        } else {
            if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') && $this->club->get('clubMembershipAvailable'))
                $filterData['CN']['entry'][] = array('id' => 'membership', 'title' => $this->translator->trans('SIDEBAR_MEMBERSHIPS'), 'type' => 'select', 'input' => $memberships);
            $filterData['CN']['entry'][] = array('id' => 'fed_membership', 'title' => $this->terminologyArray['FedMembershipPlural'], 'hidecolumn' => 1, 'type' => 'select', 'input' => $fedmemberships);
        }
        return $filterData;
    }

    /**
     * Get fixed options for Filter Roles Selection
     *
     * @param integer $clubId           club Id
     * @param string  $clubType         club Type
     * @param string  $key              key
     * @param string  $type             type
     * @param string  $terminologyTerm  terminology Term
     * @param string  $title            title
     * @return array
     */
    private function getFixedOptions($clubId, $clubType, $key, $type = 'FROLE', $title = '')
    {
        $roleArray = array();
        if ($type == 'FROLE') {
            switch ($clubType) {
                case 'federation':
                    $title = $this->translator->trans('CM_FEDERATION_ROLES');
                    break;
                case 'sub_federation':
                    $title = ucfirst($this->translator->trans('CM_SUB_FEDERATION_ROLES'));
                    break;
            }
        }
        $roleArray['title'] = $title;
        $roleArray['id'] = $key;
        $roleArray['logo'] = FgUtility::getClubLogo($clubId, $this->em);
        $roleArray['fixed_options'][1][] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_ROLE'));
        $roleArray['fixed_options'][1][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_ROLE'));
        $roleArray['fixed_options'][2][] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_FUNCTION'));
        $roleArray['fixed_options'][2][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_FUNCTION'));
        $roleArray['entry'] = array();
        $roleArray['show_filter'] = 0;
        return $roleArray;
    }

    /**
     * Get fixed options for Filter Roles Selection
     *
     * @param array $filterData             built filter Data
     *
     * @return array
     */
    private function createFedRoles($filterData)
    {

        $fedArray = array();
        switch ($this->clubType) {
            case 'federation':
                if (!isset($filterData['FROLES-' . $this->clubId])) {
                    $fedArray = $this->getFixedOptions($this->clubId, $this->clubType, 'FROLES-' . $this->clubId, 'FROLE');
                }
                break;
            case 'sub_federation':
                if (!isset($filterData['FROLES-' . $this->clubId])) {
                    $fedArray = $this->getFixedOptions($this->clubId, $this->clubType, 'FROLES-' . $this->clubId, 'FROLE');
                }
                break;
        }
        return $fedArray;
    }

    /**
     * Analysis fields
     *
     * @return array
     */
    public function analysisFields()
    {
        $filterData = array();
        /* Append Analysis fields to filter array */
        $filterData['AF']['title'] = $this->translator->trans('CM_ANALYSIS_FIELDS');
        $filterData['AF']['id'] = 'AF';
        $filterData['AF']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_ANALYSIS_FIELD'));
        $filterData['AF']['entry'][] = array('id' => 'age', 'title' => $this->translator->trans('CM_AGE'), 'type' => 'number');
        $filterData['AF']['entry'][] = array('id' => 'birth_year', 'title' => $this->translator->trans('YEAR_OF_BIRTH'), 'type' => 'number');
        $filterData['AF']['entry'][] = array('id' => 'no_of_logins', 'title' => $this->translator->trans('CONTACT_OVERVIEW_SETTINGS_NUMBER_OF_LOGIN'), 'type' => 'number');
        $filterData['AF']['entry'][] = array('id' => 'documents', 'title' => $this->translator->trans('DOCUMENTS'), 'show_filter' => 0);
        $filterData['AF']['entry'][] = array('id' => 'salutation_text', 'title' => $this->translator->trans('SALUTATION_TEXT'), 'show_filter' => 0);
        $filterData['AF']['entry'][] = array('id' => 'notes', 'title' => $this->translator->trans('NOTES'), 'show_filter' => 0);

        return $filterData['AF'];
    }

    /**
     * Federation infos
     *
     * @param boolean $hasSponsorCriteria  has Sponsor Criteria
     *
     * @return array
     */
    private function federationInfo($hasSponsorCriteria)
    {
        $filterData = array();
        $bookedModulesDet = $this->club->get('bookedModulesDet');
        /* Federation or sub federation roles data */

        $clubPdo = new \Common\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
        $rowClubsArray = $clubPdo->getAllSubLevelData($this->clubId);
        $subClubsArray = $this->iterateClubs($rowClubsArray);
        /* Club executive board data */
        $federationId = ($this->federationId > 0 ? $this->federationId : $this->clubId);
        $clubExBoardArray = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getAllClubExecBoardFunctions($federationId, $this->clubDefaultLang);
        $filterData['FI']['id'] = 'FI';
        $filterData['FI']['title'] = ucfirst($this->translator->trans('CM_FEDERATION_INFOS'));
        if ($hasSponsorCriteria == 'true' && in_array('sponsor', $bookedModulesDet)) {
            $filterData['FI']['has_separator'] = 1;
        }
        $filterData['FI']['fixed_options'][0][] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_FED_INFO'));

        $clubs[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_CLUB'));
        $clubs[] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_CLUB'));
        $filterData['FI']['entry'][] = array('id' => 'club', 'title' => $this->terminologyArray['clubTitle'], 'type' => 'select', 'fixed-options' => $clubs, 'input' => $subClubsArray['club']);
        $ceb[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_FUNCTION'));
        $ceb[] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_FUNCTION'));
        array_unshift($clubExBoardArray, $ceb[0], $ceb[1]);
        $filterData['FI']['entry'][] = array('id' => 'ceb_function', 'title' => ucfirst($this->translator->trans('CM_CLUB_EXECUTIVE_BOARD_FUNCTION')), 'type' => 'select', 'fixed-options' => $ceb, 'input' => $clubExBoardArray);
        if ($this->clubType == 'federation' && $this->club->get('hasSubfederation') == 1) {
            $subfederations[] = array('id' => '', 'title' => $this->translator->trans('CM_SELECT_SUBFED'));
            $subfederations[] = array('id' => 'any', 'title' => $this->translator->trans('CM_ANY_SUBFED'));
            $filterData['FI']['entry'][] = array('id' => 'sub_federation', 'title' => ucfirst($this->terminologyArray['subfederationTitlePlural']), 'type' => 'select', 'fixed-options' => $subfederations, 'input' => $subClubsArray['subfederation']);
        }
        $filterData['FI']['entry'][] = array('id' => 'membership_years', 'title' => $this->translator->trans('MEMBERSHIP_YEARS'), 'type' => 'text', 'show_filter' => 0, 'hidecolumn' => 1);


        return $filterData['FI'];
    }

    /**
     * This function will add the option 'Any Team'/'Any Workgroup'/'Any Function' to the team list.
     * Earlier it was a fixed option. Now it is added to the team of each category
     *
     * @param array $roleDetails
     *
     * @return array
     */
    public function iterateRolesWithAnyOption($roleDetails)
    {
        foreach ($roleDetails as $type => $role) {
            foreach ($role['entry'] as $key => $roleCategory) {
                //get all functions in the category and add it.
                $functionArray = array();
                $addedIdArray = array();

                foreach ($roleCategory['input'] as $roleCategoryDetail) {
                    foreach ($roleCategoryDetail['input'] as $roleCategoryFunction) {
                        if (!in_array($roleCategoryFunction['id'], $addedIdArray)) {
                            $functionArray[] = array('id' => $roleCategoryFunction['id'], 'title' => $roleCategoryFunction['title']);
                            $addedIdArray[] = $roleCategoryFunction['id'];
                        }
                    }
                }

                if ($type == 'TEAM') {
                    $title = $this->translator->trans('CM_ANY_TEAM');
                } else if ($type == 'WORKGROUP') {
                    $title = $this->translator->trans('CM_ANY_WORKGROUP');
                } else {
                    $title = $this->translator->trans('CM_ANY_ROLE');
                }

                $tempArray = array('id' => 'any', 'type' => 'select', 'title' => $title, 'isRoleActive' => 1, 'bookMarkId' => '', 'categoryId' => $roleCategoryFunction['id'], 'itemType' => $roleCategoryFunction['itemType'], 'draggable' => 0, 'functionAssign' => $roleCategoryFunction['functionAssign'], 'input' => $functionArray);
                array_unshift($roleDetails[$type]['entry'][$key]['input'], $tempArray); //This will add the tempArray to the original list
            }
        }
        return $roleDetails;
    }

    /**
     * Iterate the roles, team and work group
     *
     * @param array $rowRoleArray rolearray
     *
     * @return array
     */
    public function iterateRoles($rowRoleArray)
    {
        $groupTitle = '';
        $categoryId = '';
        $categoryTitle = '';
        $roleId = '';
        $roleTitle = '';
        $isRoleActive = 0;
        $draggable = '';
        $roleBookmark = '';
        $roleCategoryId = '';
        $roleGroup = '';
        $categoryArray = array();
        $roleArray = array();
        $functionArray = array();
        $resultArray = array();
        $functionAssign = '';
        $rolefunctionAssign = '';
        $total = count($rowRoleArray);
        $count = 0;
        $showFilterGroup = 0;
        foreach ($rowRoleArray as $rowRole) {
            $count++;
            $fArray = ($rowRole['functionId'] != '') ? array('id' => $rowRole['functionId'], 'title' => $rowRole['functionTitle']) : array();
            $rArray = ($roleId != '') ? array('id' => $roleId, 'type' => 'select', 'title' => $roleTitle, 'isRoleActive' => $isRoleActive, 'bookMarkId' => $roleBookmark, 'category' => $rowRole['category'], 'categoryId' => $roleCategoryId, 'itemType' => $roleGroup, 'draggable' => $draggable, 'functionAssign' => $rolefunctionAssign) : array();
            $cArray = ($categoryId != '') ? array('id' => $categoryId, 'type' => 'select', 'title' => $categoryTitle, 'category' => $rowRole['category'], 'functionAssign' => $functionAssign, 'input' => array()) : array();
            /* Add to role array  */
            if (($count == $total) || ($roleId != $rowRole['roleId'])) {
                if (($count == $total) || $roleId != '') {
                    if (($count == $total)) {
                        if ($roleId != $rowRole['roleId']) {
                            if (count($functionArray) > 0) {
                                $rArray['input'] = $functionArray;
                            }
                            if (count($rArray) > 0) {
                                $roleArray[] = $rArray;
                                $functionArray = array();
                            }
                            if ($rowRole['roleId'] != '') {
                                $rArray = array('id' => $rowRole['roleId'], 'type' => 'select', 'title' => $rowRole['roleTitle'], 'bookMarkId' => $rowRole['bookMarkId'], 'categoryId' => $rowRole['categoryId'], 'itemType' => $rowRole['groupTitle'], 'draggable' => $rowRole['draggable'], 'functionAssign' => $rowRole['functionAssign'], 'isRoleActive' => $rowRole['isRoleActive']);
                            }
                            $functionArray = array();
                        }
                        if ($categoryId == $rowRole['categoryId']) {
                            $functionArray[] = array('id' => $rowRole['functionId'], 'title' => $rowRole['functionTitle']);
                        } else {
                            $rArray = array();
                            $functionArray = array();
                        }
                    }
                    if (count($functionArray) > 0) {
                        $rArray['input'] = $functionArray;
                    }
                    if (count($rArray) > 0) {
                        $roleArray[] = $rArray;
                        $functionArray = array();
                    }
                }
                if (count($fArray) > 0) {
                    $functionArray[] = $fArray;
                }
                $roleId = $rowRole['roleId'];
                $roleTitle = $rowRole['roleTitle'];
                $isRoleActive = $rowRole['isRoleActive'];
                $roleBookmark = $rowRole['bookMarkId'];
                $roleCategoryId = $rowRole['categoryId'];
                $roleGroup = $rowRole['groupTitle'];
                $draggable = $rowRole['draggable'];
                $rolefunctionAssign = $rowRole['functionAssign'];
            } elseif ($rowRole['functionId'] != '') {
                $functionArray[] = $fArray;
            }
            /* Add to category array  */
            if (($count == $total) || ($categoryId != $rowRole['categoryId'])) {
                if (($count == $total) || $categoryId != '') {
                    if (($count == $total)) {
                        if ($categoryId != $rowRole['categoryId'] && $groupTitle == $rowRole['groupTitle']) {
                            if (count($roleArray) > 0) {
                                $cArray['input'] = $roleArray;
                            }
                            if (count($cArray) > 0) {
                                if (count($cArray['input']) > 0) {
                                    $cArray['show_filter'] = 1;
                                    $showFilterGroup = 1;
                                } else {
                                    $cArray['show_filter'] = 0;
                                }
                                $categoryArray[] = $cArray;
                                $cArray = array();
                                $roleArray = array();
                                $functionArray = array();
                            }
                            $roleArray = array();
                            if ($rowRole['roleId'] != '') {
                                $roleArray[] = array('id' => $rowRole['roleId'], 'type' => 'select', 'title' => $rowRole['roleTitle'], 'isRoleActive' => $isRoleActive, 'bookMarkId' => $rowRole['bookMarkId'], 'categoryId' => $rowRole['categoryId'], 'category' => $rowRole['categoryId'], 'itemType' => $rowRole['groupTitle'], 'draggable' => $rowRole['draggable'], 'functionAssign' => $rowRole['functionAssign']);
                            }
                            if ($rowRole['categoryId'] != '') {
                                $cArray = array('id' => $rowRole['categoryId'], 'type' => 'select', 'title' => $rowRole['categoryTitle'], 'functionAssign' => $rowRole['functionAssign'], 'input' => $roleArray, 'show_filter' => (count($roleArray) > 0 ? 1 : 0));
                            }
                        }

                        $categoryId = $rowRole['categoryId'];
                        $categoryTitle = $rowRole['categoryTitle'];
                        $functionAssign = $rowRole['functionAssign'];
                    }
                    if (count($cArray) > 0) {
                        if (count($roleArray) > 0) {
                            $cArray['input'] = $roleArray;
                            $cArray['show_filter'] = 1;
                            $showFilterGroup = 1;
                        } else {
                            $cArray['show_filter'] = 0;
                        }
                        $categoryArray[] = $cArray;
                    }
                    $roleArray = array();
                }
                $categoryId = $rowRole['categoryId'];
                $categoryTitle = $rowRole['categoryTitle'];
                $functionAssign = $rowRole['functionAssign'];
            }
            /* TO group Array ROLES-{clubId}/FROLES-{clubId}/WORKGROUP/TEAM/FILTERROLES */
            if (($count == $total) || $groupTitle != $rowRole['groupTitle']) {

                if (($count == $total) || $groupTitle != '') {
                    if (($count == $total)) {
                        if ($groupTitle != $rowRole['groupTitle']) {
                            if ($count > 1) {
                                $gArray = array('title' => $groupTitle, 'show_filter' => $showFilterGroup);
                                if (count($categoryArray) > 0) {
                                    $gArray['entry'] = $categoryArray;
                                }
                                $resultArray[$groupTitle] = $gArray;
                                $showFilterGroup = 0;
                            }
                            $categoryArray = array();
                            $roleArray = array();
                            $functionArray = array();
                            $cArray = array('id' => $rowRole['categoryId'], 'type' => 'select', 'title' => $rowRole['categoryTitle'], 'category' => $rowRole['category'], 'functionAssign' => $rowRole['functionAssign'], 'input' => array(), 'show_filter' => (count($rArray) > 0 ? 1 : 0));
                            if ($rowRole['roleId'] != '') {
                                $rArray = array('id' => $rowRole['roleId'], 'type' => 'select', 'title' => $rowRole['roleTitle'], 'category' => $rowRole['category'], 'isRoleActive' => $isRoleActive, 'bookMarkId' => $rowRole['bookMarkId'], 'categoryId' => $rowRole['categoryId'], 'itemType' => $rowRole['groupTitle'], 'draggable' => $rowRole['draggable'], 'functionAssign' => $rowRole['functionAssign']);
                                if ($rowRole['functionId'] != '') {
                                    $fArray = array('id' => $rowRole['functionId'], 'title' => $rowRole['functionTitle'], 'category' => $rowRole['category'], 'categoryId' => $rowRole['categoryId'], 'itemType' => $rowRole['categoryTitle']);
                                    $rArray['input'][] = $fArray;
                                }
                                $cArray['input'][] = $rArray;
                                $cArray['show_filter'] = 1;
                            }
                            $categoryArray[] = $cArray;
                            if (count($cArray['input']) > 0) {
                                $showFilterGroup = 1;
                            }
                        }
                        $groupTitle = $rowRole['groupTitle'];
                    }
                    $gArray = array('title' => $groupTitle, 'show_filter' => $showFilterGroup);
                    if (count($categoryArray) > 0) {
                        $gArray['entry'] = $categoryArray;
                    }
                    $resultArray[$groupTitle] = $gArray;
                    $showFilterGroup = 0;
                    $categoryArray = array();
                }
                $groupTitle = $rowRole['groupTitle'];
            }
        }

        return $resultArray;
    }
}
