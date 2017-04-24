<?php

/**
 * FgContactTable
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Repository\Pdo\ClubPdo;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgContactTable - The wrapper class to handle functionalities on contact table elements wizard step 1 and 2
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgContactTable
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
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->em = $this->container->get('doctrine')->getManager();
        $this->translator = $this->container->get('translator');
    }

    /**
     * Function to save the contact table element data to the DB
     *
     * @param array $dataArray The table data + contact table element data to be inserted
     *
     * @return int $tableId Table Id
     */
    public function saveContactTableStage1($dataArray)
    {
        //save the contact table details

        $tableId = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->saveContactTableStage1($dataArray, $this->clubId, $this->contactId);
        $dataArray['tableId'] = $tableId;
        //create a new contact table elelemnt
        if ($dataArray['event'] == 'create') {
            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->reOrderSortPosition($dataArray['boxId'], $dataArray['sortOrder']);
            $elementId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->saveContactTableElement($dataArray, $this->clubId);

            /* Log Entry */
            $pageId = $dataArray['pageId'];
            $defaultClubLang = $this->club->get('club_default_lang');
            $pageTitles = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTite($pageId);
            $pageTitle = ($pageTitles['default'] == 'footer') ? $this->translator->trans('TOP_NAV_CMS_FOOTER') : (($pageTitles['default'] == 'sidebar') ? $this->translator->trans('CMS_SIDEBAR') : (isset($pageTitles[$defaultClubLang]) ? $pageTitles[$defaultClubLang] : $pageTitles[$this->club->get('club_default_sys_lang')]));
            $logArray[] = "('$elementId', '$pageId', 'page', 'added', '', '$pageTitle', now(), $this->contactId)";
            $logArray[] = "('$elementId', '$pageId', 'element', 'added', '', '', now(), $this->contactId)";
        } else {
            $tableObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->findOneBy(array('table' => $dataArray['tableId']));
            $elementId = $tableObj->getId();
            $pageId = $tableObj->getBox()->getColumn()->getContainer()->getPage()->getId();
            $logArray[] = "('$elementId', '$pageId', 'element', 'changed', '', '', now(), $this->contactId)";
        }

        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        return $tableId;
    }

    /**
     * This function is used to build the json for contact table element wizard stage 2
     * 
     * @return array $tableColumns The column options data
     */
    public function getContactTableColumnOptions()
    {
        $tableColumns = array();
        $clubLangDetails = $this->container->get('club')->get('club_languages_det');
        $clubDefaultSysLang = $this->club->get('default_system_lang');

        $clubPdoObj = new ClubPdo($this->container);
        $terminologyArr = $clubPdoObj->getTerminologiesForContactTable(array('Executive Board', 'Team', 'Club', 'Sub-federation'));

        $staticTableColumns = $this->getStaticTableColumns();
        $contactFieldsArr = $this->getContactFieldDetails();
        $teamAndWorkgroupDetails = $this->getTeamAndWorkgroupDetails($terminologyArr);
        $roleCategoryDetails = $this->getRoleCategoryDetails();

        $contactPdoObj = new ContactPdo($this->container);

        foreach ($staticTableColumns as $val) {
            switch ($val) {
                case 'CONTACT_NAME':
                    foreach ($clubLangDetails as $lang => $detail) {
                        $tableColumns[$val]['fieldName'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_CONTACT_NAME', array(), 'messages', $detail['systemLang']);
                    }
                    break;

                case 'CONTACT_FIELD':
                    $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_CONTACT_FIELD', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_CONTACT_FIELD_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['fieldValue'] = $contactFieldsArr;
                    break;

                case 'MEMBERSHIP_INFO':
                case 'FED_MEMBERSHIP_INFO':
                    $tableColumns[$val]['fieldName'] = ($val == 'MEMBERSHIP_INFO') ? $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_MEMBERSHIP_INFO', array(), 'messages', $clubDefaultSysLang) : $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FED_MEMBERSHIP_INFO', array(), 'messages', $clubDefaultSysLang);
                    foreach ($clubLangDetails as $lang => $detail) {
                        $tableColumns[$val]['fieldValue']['membership']['attrNameLang'][$lang] = ($val == 'MEMBERSHIP_INFO') ? $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_MEMBERSHIP', array(), 'messages', $detail['systemLang']) : $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FED_MEMBERSHIP', array(), 'messages', $detail['systemLang']);
                        $tableColumns[$val]['fieldValue']['member_years']['attrNameLang'][$lang] = ($val == 'MEMBERSHIP_INFO') ? $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_MEMBER_YEARS', array(), 'messages', $detail['systemLang']) : $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FED_MEMBER_YEARS', array(), 'messages', $detail['systemLang']);
                    }
                    $tableColumns[$val]['fieldValue']['membership']['attrName'] = ($val == 'MEMBERSHIP_INFO') ? $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_MEMBERSHIP', array(), 'messages', $clubDefaultSysLang) : $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FED_MEMBERSHIP', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['fieldValue']['membership']['attrId'] = 'membership';
                    $tableColumns[$val]['fieldValue']['membership']['attrType'] = 'membership';
                    $tableColumns[$val]['fieldValue']['membership']['attrSortorder'] = 1;
                    $tableColumns[$val]['fieldValue']['member_years']['attrName'] = ($val == 'MEMBERSHIP_INFO') ? $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_MEMBER_YEARS', array(), 'messages', $clubDefaultSysLang) : $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FED_MEMBER_YEARS', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['fieldValue']['member_years']['attrId'] = 'member_years';
                    $tableColumns[$val]['fieldValue']['member_years']['attrType'] = 'member_years';
                    $tableColumns[$val]['fieldValue']['member_years']['attrSortorder'] = 2;
                    $tableColumns[$val]['defaultOption'] = ($val == 'MEMBERSHIP_INFO') ? $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_MEMBERSHIP_INFO_DEFAULT', array(), 'messages', $clubDefaultSysLang) : $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_FED_MEMBERSHIP_INFO_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                    break;

                case 'FEDERATION_INFO':
                    $tableColumns[$val] = $this->getFederationInfoColumnOptions($terminologyArr);
                    break;

                case 'ANALYSIS_FIELD':
                    $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_ANALYSIS_FIELD', array(), 'messages', $clubDefaultSysLang);
                    foreach ($clubLangDetails as $lang => $detail) {
                        $tableColumns[$val]['fieldValue']['age']['attrNameLang'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_AGE', array(), 'messages', $detail['systemLang']);
                        $tableColumns[$val]['fieldValue']['birth_year']['attrNameLang'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_YEAR_OF_BIRTH', array(), 'messages', $detail['systemLang']);
                    }
                    $tableColumns[$val]['fieldValue']['age']['attrName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_AGE', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['fieldValue']['age']['attrId'] = 'age';
                    $tableColumns[$val]['fieldValue']['age']['attrType'] = 'age';
                    $tableColumns[$val]['fieldValue']['age']['attrSortorder'] = 1;
                    $tableColumns[$val]['fieldValue']['birth_year']['attrName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_YEAR_OF_BIRTH', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['fieldValue']['birth_year']['attrId'] = 'birth_year';
                    $tableColumns[$val]['fieldValue']['birth_year']['attrType'] = 'birth_year';
                    $tableColumns[$val]['fieldValue']['birth_year']['attrSortorder'] = 2;
                    $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_ANALYSIS_FIELD_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                    break;

                case 'TEAM_ASSIGNMENTS':
                    if (!count($teamAndWorkgroupDetails['teamFunctionsArr'])) {
                        continue;
                    }
                    foreach ($clubLangDetails as $lang => $detail) {
                        $term = $this->getTerminologyTerm('Team', $lang, $detail['systemLang'], $terminologyArr);
                        $fieldName = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_TEAM_ASSIGNMENTS', array('%Team%' => ucfirst($term)), 'messages', $detail['systemLang']);
                        $tableColumns[$val]['fieldName'][$lang] = $fieldName;
                    }
                    $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_TEAM_FUNCTIONS_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['teamFunctions'] = $teamAndWorkgroupDetails['teamFunctionsArr'];
                    break;

                case 'TEAM_FUNCTIONS':
                    if (!count($teamAndWorkgroupDetails['teamFunctionsArr'])) {
                        continue;
                    }
                    foreach ($clubLangDetails as $lang => $detail) {
                        $term = $this->getTerminologyTerm('Team', $lang, $detail['systemLang'], $terminologyArr);
                        $fieldName = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_TEAM_FUNCTIONS', array('%Team%' => ucfirst($term)), 'messages', $detail['systemLang']);
                        $tableColumns[$val]['fieldName'][$lang] = $fieldName;
                    }
                    break;

                case 'WORKGROUP_ASSIGNMENTS':
                    foreach ($clubLangDetails as $lang => $detail) {
                        $tableColumns[$val]['fieldName'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_WORKGROUP_ASSIGNMENTS', array(), 'messages', $detail['systemLang']);
                    }
                    break;

                case 'FILTER_ROLE_ASSIGNMENTS':
                    $filterRoleDetails = $contactPdoObj->getRoleCategoriesCountContactTableColumns(true);
                    if (!$filterRoleDetails[0]['roleCatCount']) {
                        continue;
                    }
                    foreach ($clubLangDetails as $lang => $detail) {
                        $tableColumns[$val]['fieldName'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FILTER_ROLE_ASSIGNMENTS', array(), 'messages', $detail['systemLang']);
                    }
                    break;

                case 'ROLE_CATEGORY_ASSIGNMENTS':
                    if (count($roleCategoryDetails['roleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_ROLE_CATEGORY_ASSIGNMENTS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_ROLE_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['roleCategoryArr'];
                    }
                    break;

                case 'FED_ROLE_CATEGORY_ASSIGNMENTS':
                    if (count($roleCategoryDetails['fedRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FED_ROLE_CATEGORY_ASSIGNMENTS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_FED_ROLE_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['fedRoleCategoryArr'];
                    }
                    break;

                case 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS':
                    if (count($roleCategoryDetails['subFedRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SUB_FED_ROLE_CATEGORY_ASSIGNMENTS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_SUB_FED_ROLE_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['subFedRoleCategoryArr'];
                    }
                    break;

                case 'COMMON_ROLE_FUNCTIONS':
                    if (count($roleCategoryDetails['commonRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_COMMON_ROLE_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_ROLE_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['commonRoleCategoryArr'];
                    }
                    break;

                case 'COMMON_FED_ROLE_FUNCTIONS':
                    if (count($roleCategoryDetails['commonFedRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_COMMON_FED_ROLE_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_FED_ROLE_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['commonFedRoleCategoryArr'];
                    }
                    break;

                case 'COMMON_SUB_FED_ROLE_FUNCTIONS':
                    if (count($roleCategoryDetails['commonSubFedRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_COMMON_SUB_FED_ROLE_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_SUB_FED_ROLE_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['commonSubFedRoleCategoryArr'];
                    }
                    break;

                case 'WORKGROUP_FUNCTIONS':
                    if (count($teamAndWorkgroupDetails['workgroupsArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_WORKGROUP_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_WORKGROUP_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $teamAndWorkgroupDetails['workgroupsArr'];
                    }
                    break;

                case 'INDIVIDUAL_ROLE_FUNCTIONS':
                    if (count($roleCategoryDetails['individualRoleArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_INDIVIDUAL_ROLE_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_ROLE_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['individualRoleArr'];
                    }
                    break;

                case 'INDIVIDUAL_FED_ROLE_FUNCTIONS':
                    if (count($roleCategoryDetails['individualFedRoleArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_INDIVIDUAL_FED_ROLE_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_FED_ROLE_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['individualFedRoleArr'];
                    }
                    break;

                case 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS':
                    if (count($roleCategoryDetails['individualSubFedRoleArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_SUB_FED_ROLE_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['individualSubFedRoleArr'];
                    }
                    break;

                default:
                    break;
            }
        }

        return $tableColumns;
    }

    /**
     * This function is used to build the json for contact table element wizard stage 3
     * 
     * @return array $tableColumns The column options data
     */
    public function getContactTableFilterOptions()
    {
        $tableColumns = array();
        $clubLangDetails = $this->container->get('club')->get('club_languages_det');
        $clubDefaultSysLang = $this->club->get('default_system_lang');
        $club = $this->container->get('club');

        $staticTableFilter = $this->getStaticTableFilter();
        $contactFieldsArr = $this->getContactFieldDetails(array('select', 'checkbox', 'radio'));
        
        $clubPdoObj = new ClubPdo($this->container);
        $terminologyArr = $clubPdoObj->getTerminologiesForContactTable(array('Federation', 'Team', 'Fed membership', 'Sub-federation','Executive Board'));
        
        $teamAndWorkgroupDetails = $this->getTeamAndWorkgroupDetails($terminologyArr);
        $roleCategoryDetails = $this->getRoleCategoryDetails();

        $contactPdoObj = new ContactPdo($this->container);
        
        foreach ($staticTableFilter as $val) {
            switch ($val) {
                case 'CONTACT_FIELD':
                    $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_CONTACT_FIELD', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_SELECT_CONTACT_FIELD_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                    $tableColumns[$val]['fieldValue'] = $contactFieldsArr;
                    break;
                case 'MEMBERSHIPS':
                    $tableColumns[$val]['fieldName'] = $this->translator->trans('CLUB_MEMBERSHIP', array(), 'messages', $clubDefaultSysLang);
                    foreach ($clubLangDetails as $lang => $detail) {
                        $tableColumns[$val]['labelLang'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_CLUB_MEMBERSHIP_LABEL', array(), 'messages', $detail['systemLang']);
                    }
                    break;
                case 'FED_MEMBERSHIPS':
                    $tableColumns[$val]['fieldName'] = $this->getTerminologyTerm('Fed memberships', $lang, $clubDefaultSysLang, $terminologyArr);
                    foreach ($clubLangDetails as $lang => $detail) {
                        $terminologyValue = $this->getTerminologyTerm('Fed memberships', $lang, $detail['systemLang'], $terminologyArr);
                        $tableColumns[$val]['labelLang'][$lang] = str_replace('#FEDERATION_MEMBERSHIP#', $terminologyValue, $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FED_MEMBERSHIP_LABEL', array(), 'messages', $detail['systemLang']));
                    }
                    break;
                case 'WORKGROUPS':
                    if (count($teamAndWorkgroupDetails['workgroupsArr'])) {
                        $allOption['f-0'] = array('attrId' => 'ALL', 'attrSortOrder' => 0, 'attrName' => $this->translator->trans('CMS_CONTACT_TABLE_FILTER_WORKGROUP_ALL'), 'attrNameLang' => array());
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_WORKGROUP', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_WORKGROUP_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $allOption + $teamAndWorkgroupDetails['workgroupsArr'];

                        foreach ($clubLangDetails as $lang => $detail) {
                            $tableColumns[$val]['labelLang'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_WORKGROUP_LABEL', array(), 'messages', $detail['systemLang']);
                        }
                    }
                    break;
                case 'FILTER_ROLES':
                    $filterRoleDetails = $contactPdoObj->getRoleCategoriesForContactTableColumns(true);
                    if (count($filterRoleDetails) > 0) {
                        $allOption['f-0'] = array('attrId' => 'ALL', 'attrSortOrder' => 0, 'attrName' => $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FILTERROLE_ALL'), 'attrNameLang' => array());
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FILTERROLE', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FILTERROLE_DEFAULT', array(), 'messages', $clubDefaultSysLang);

                        $filterRoleFormatted = array();
                        foreach ($filterRoleDetails as $filterRole) {
                            $filterRoleFormatted['f-' . $filterRole['roleId']]['attrId'] = $filterRole['roleId'];
                            $filterRoleFormatted['f-' . $filterRole['roleId']]['attrSortOrder'] = $filterRole['roleSortOrder'];
                            $filterRoleFormatted['f-' . $filterRole['roleId']]['attrName'] = $filterRole['roleTitle'];
                            $filterRoleFormatted['f-' . $filterRole['roleId']]['attrNameLang'][$filterRole['roleLang']] = $filterRole['roleTitleLang'];
                        }
                        $tableColumns[$val]['fieldValue'] = $allOption + $filterRoleFormatted;

                        foreach ($clubLangDetails as $lang => $detail) {
                            $tableColumns[$val]['labelLang'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FILTERROLE_LABEL', array(), 'messages', $detail['systemLang']);
                        }
                    }
                    break;
                case 'TEAM_CATEGORY':
                    //get team category
                    $club = $this->container->get('club');
                    $teamCategories = $this->em->getRepository('CommonUtilityBundle:FgTeamCategory')->getTeamCatDetails($club->get('id'), '', true, '', $club->get('club_team_id'), true);

                    if (count($teamCategories) > 0) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_TEAM_CATEGORY');
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $teamCategoriesFormatted = array();
                        foreach ($teamCategories as $teamCategory) {
                            $teamCategoriesFormatted['tc-' . $teamCategory['id']]['attrId'] = $teamCategory['id'];
                            $teamCategoriesFormatted['tc-' . $teamCategory['id']]['attrSortOrder'] = $teamCategory['sortOrder'];
                            $teamCategoriesFormatted['tc-' . $teamCategory['id']]['attrName'] = $teamCategory['title'];
                            $teamCategoriesFormatted['tc-' . $teamCategory['id']]['attrNameLang'] = $teamCategory['titleLang'];
                        }
                        $tableColumns[$val]['fieldValue'] = $teamCategoriesFormatted;

                        foreach ($clubLangDetails as $lang => $detail) {
                            $terminologyValue = $this->getTerminologyTerm('Teams', $lang, $detail['systemLang'], $terminologyArr);
                            $tableColumns[$val]['labelLang'][$lang] = str_replace('#TEAM#', $terminologyValue, $this->translator->trans('CMS_CONTACT_TABLE_FILTER_TEAM_LABEL', array(), 'messages', $detail['systemLang']));
                        }
                    }

                    break;
                case 'ROLE_CATEGORY':
                    if (count($roleCategoryDetails['roleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_ROLE_CATEGORY', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['roleCategoryArr'];
                        foreach ($clubLangDetails as $lang => $detail) {
                            $tableColumns[$val]['labelLang'][$lang] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_ROLE_LABEL', array(), 'messages', $detail['systemLang']);
                        }
                    }

                    break;
                case 'FED_ROLE_CATEGORY':
                    if (count($roleCategoryDetails['fedRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FED_ROLE_CATEGORY', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['fedRoleCategoryArr'];

                        foreach ($clubLangDetails as $lang => $detail) {
                            $terminologyValue = $this->getTerminologyTerm('Federation', $lang, $detail['systemLang'], $terminologyArr);
                            $tableColumns[$val]['labelLang'][$lang] = str_replace('#FEDERATION#', $terminologyValue, $this->translator->trans('CMS_CONTACT_TABLE_FILTER_FED_ROLE_LABEL', array(), 'messages', $detail['systemLang']));
                        }
                    }

                    break;
                case 'SUBFED_ROLE_CATEGORY':
                    if (count($roleCategoryDetails['subFedRoleCategoryArr'])) {
                        $tableColumns[$val]['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_SUB_FED_ROLE_CATEGORY', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_FILTER_CATEGORY_DEFAULT', array(), 'messages', $clubDefaultSysLang);
                        $tableColumns[$val]['fieldValue'] = $roleCategoryDetails['subFedRoleCategoryArr'];

                        foreach ($clubLangDetails as $lang => $detail) {
                            $terminologyValue = $this->getTerminologyTerm('Sub-federation', $lang, $detail['systemLang'], $terminologyArr);
                            $tableColumns[$val]['labelLang'][$lang] = str_replace('#SUBFEDERATION#', $terminologyValue, $this->translator->trans('CMS_CONTACT_TABLE_FILTER_SUBFED_ROLE_LABEL', array(), 'messages', $detail['systemLang']));
                        }
                    }

                    break;

                default:
                    break;
            }
        }

        return $tableColumns;
    }

    /**
     * This function is used to get the static table columns for wizard stage 2
     * 
     * @return array $staticTableColumns Array of column data
     */
    private function getStaticTableColumns()
    {
        $clubMembershipAvailable = $this->club->get('clubMembershipAvailable');
        $staticMandatoryTableColumns = array(
            0 => 'CONTACT_NAME',
            1 => 'CONTACT_FIELD',
            5 => 'ANALYSIS_FIELD',
            6 => 'TEAM_ASSIGNMENTS',
            7 => 'TEAM_FUNCTIONS',
            8 => 'WORKGROUP_ASSIGNMENTS',
            9 => 'WORKGROUP_FUNCTIONS',
            10 => 'ROLE_CATEGORY_ASSIGNMENTS',
            11 => 'COMMON_ROLE_FUNCTIONS',
            12 => 'INDIVIDUAL_ROLE_FUNCTIONS',
            13 => 'FILTER_ROLE_ASSIGNMENTS',
        );
        switch ($this->club->get('type')) {
            case 'federation':
                $additionalTableColumns = array(
                    3 => 'FED_MEMBERSHIP_INFO',
                    4 => 'FEDERATION_INFO',
                    14 => 'FED_ROLE_CATEGORY_ASSIGNMENTS',
                    15 => 'COMMON_FED_ROLE_FUNCTIONS',
                    16 => 'INDIVIDUAL_FED_ROLE_FUNCTIONS',
                );
                break;
            case 'sub_federation':
                $additionalTableColumns = array(
                    4 => 'FEDERATION_INFO',
                    14 => 'FED_ROLE_CATEGORY_ASSIGNMENTS',
                    15 => 'COMMON_FED_ROLE_FUNCTIONS',
                    16 => 'INDIVIDUAL_FED_ROLE_FUNCTIONS',
                    17 => 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS',
                    18 => 'COMMON_SUB_FED_ROLE_FUNCTIONS',
                    19 => 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS'
                );
                break;
            case 'federation_club':
                $additionalTableColumns = array(
                    14 => 'FED_ROLE_CATEGORY_ASSIGNMENTS',
                    15 => 'COMMON_FED_ROLE_FUNCTIONS',
                    16 => 'INDIVIDUAL_FED_ROLE_FUNCTIONS',
                );
                if ($clubMembershipAvailable) {
                    $additionalTableColumns[2] = 'MEMBERSHIP_INFO';
                }
                break;
            case 'sub_federation_club':
                $additionalTableColumns = array(
                    14 => 'FED_ROLE_CATEGORY_ASSIGNMENTS',
                    15 => 'COMMON_FED_ROLE_FUNCTIONS',
                    16 => 'INDIVIDUAL_FED_ROLE_FUNCTIONS',
                    17 => 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS',
                    18 => 'COMMON_SUB_FED_ROLE_FUNCTIONS',
                    19 => 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS'
                );
                if ($clubMembershipAvailable) {
                    $additionalTableColumns[2] = 'MEMBERSHIP_INFO';
                }
                break;
            case 'standard_club':
                $additionalTableColumns = array(
                    2 => 'MEMBERSHIP_INFO',
                );
                break;
            default:
                $additionalTableColumns = array();
                break;
        }
        $staticTableColumns = array_replace($staticMandatoryTableColumns, $additionalTableColumns);
        ksort($staticTableColumns);

        return $staticTableColumns;
    }

    /**
     * This function is used to get the static table columns for wizard stage 3
     * 
     * @return array $staticTableFilter Array of column data
     */
    private function getStaticTableFilter()
    {
        $clubMembershipAvailable = $this->club->get('clubMembershipAvailable');
        $tableFilterOptions = array(
            1 => 'CONTACT_FIELD',
            4 => 'WORKGROUPS',
            5 => 'FILTER_ROLES',
            6 => 'TEAM_CATEGORY',
            7 => 'ROLE_CATEGORY',
        );

        switch ($this->club->get('type')) {
            case 'federation':
                $tableFilterOptions[3] = 'FED_MEMBERSHIPS';
                $tableFilterOptions[8] = 'FED_ROLE_CATEGORY';
                break;
            case 'sub_federation':
                $tableFilterOptions[3] = 'FED_MEMBERSHIPS';
                $tableFilterOptions[8] = 'FED_ROLE_CATEGORY';
                $tableFilterOptions[9] = 'SUBFED_ROLE_CATEGORY';
                break;
            case 'federation_club':
                if ($clubMembershipAvailable) {
                    $tableFilterOptions[2] = 'MEMBERSHIPS';
                }
                $tableFilterOptions[3] = 'FED_MEMBERSHIPS';
                $tableFilterOptions[9] = 'FED_ROLE_CATEGORY';
                break;
            case 'sub_federation_club':
                if ($clubMembershipAvailable) {
                    $tableFilterOptions[2] = 'MEMBERSHIPS';
                }
                $tableFilterOptions[3] = 'FED_MEMBERSHIPS';
                $tableFilterOptions[9] = 'SUBFED_ROLE_CATEGORY';
                $tableFilterOptions[8] = 'FED_ROLE_CATEGORY';
                break;
            case 'standard_club':
                if ($clubMembershipAvailable) {
                    $tableFilterOptions[2] = 'MEMBERSHIPS';
                }
                break;
            default:
                break;
        }

        ksort($tableFilterOptions);

        return $tableFilterOptions;
    }

    /**
     * This function is used to get all available contact field's details
     * 
     * @param array     $includedType          If specified the function will only return the contact fields with this type (user for stage3)
     * 
     * @return array $contactFieldsArr Array of contact filed details
     */
    private function getContactFieldDetails($includedType = array())
    {
        $contactPdoObj = new ContactPdo($this->container);
        $contactFields = $contactPdoObj->getContactFieldsForContactTableColumns();
        $systemCorresFields = $this->container->getParameter('system_correspondance_fields');
        $systemInvoiceFields = $this->container->getParameter('system_invoice_fields');

        $contactFieldsArr = array();
        foreach ($contactFields as $contactField) {
            if (count($includedType) > 0 && !in_array($contactField['type'], $includedType)) {
                continue;
            }
            $key = 'c-' . $contactField['catId'];
            $addressType = ($contactField['addres_type'] == 'both' && $contactField['isSystemField']) ? ((in_array($contactField['id'], $systemCorresFields)) ? 'correspondance' : ((in_array($contactField['id'], $systemInvoiceFields)) ? 'invoice' : $contactField['addres_type'])) : $contactField['addres_type'];
            $contactFieldsArr[$key]['catName'] = $contactField['selectgroup'];
            $contactFieldsArr[$key]['catId'] = $contactField['catId'];
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['attrId'] = $contactField['id'];
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['fieldName'] = $contactField['fieldName'];
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['fieldNameLang'][$contactField['attrLang']] = $contactField['fieldNameLang'];
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['attrName'] = $this->removeCorrespondenceAdressSuffix($contactField['id'], $contactField['shortName']);
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['attrNameLang'][$contactField['attrLang']] = $this->removeCorrespondenceAdressSuffix($contactField['id'], $contactField['shortNameLang']);
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['attrType'] = $contactField['type'];
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['attrSortOrder'] = $contactField['sort'];
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['addressType'] = $addressType;
            $contactFieldsArr[$key]['attrDetails']['a-' . $contactField['id']]['isSystemField'] = $contactField['isSystemField'];
        }

        return $contactFieldsArr;
    }
    
    private function removeCorrespondenceAdressSuffix($attributeId, $text) {
        $removeStringsArray = array('(corr.)', '(Korr.)');
        if(in_array($attributeId, $this->container->getParameter('system_correspondance_fields'))) {
            return str_replace($removeStringsArray, '', $text);
        } else {
            
            return $text;
        }
    }

    /**
     * This function is used to get role category details of all roles available in a club/federation/sub-federation
     * 
     * @return array $result Array of role category details
     */
    private function getRoleCategoryDetails()
    {
        $contactPdoObj = new ContactPdo($this->container);
        $roleCategoryDetails = $contactPdoObj->getRoleCategoriesForContactTableColumns();

        $federationId = $this->club->get('federation_id');
        $subFederationId = $this->club->get('sub_federation_id');

        $roleCategoryArr = array();
        $individualRoleArr = array();
        $commonRoleCategoryArr = array();

        $fedRoleCategoryArr = array();
        $individualFedRoleArr = array();
        $commonFedRoleCategoryArr = array();

        $subFedRoleCategoryArr = array();
        $individualSubFedRoleArr = array();
        $commonSubFedRoleCategoryArr = array();

        foreach ($roleCategoryDetails as $roleCategoryDetail) {
            $key = 'r-' . $roleCategoryDetail['roleCatId'];

            if (($roleCategoryDetail['clubId'] == $this->clubId) && !$roleCategoryDetail['isFedCategory']) {
                //role categories
                $roleCategoryArr[$key]['attrId'] = $roleCategoryDetail['roleCatId'];
                $roleCategoryArr[$key]['attrName'] = $roleCategoryDetail['roleCatTitle'];
                $roleCategoryArr[$key]['attrNameLang'][$roleCategoryDetail['roleCatLang']] = $roleCategoryDetail['roleCatTitleLang'];
                $roleCategoryArr[$key]['attrSortorder'] = $roleCategoryDetail['roleCatSortOrder'];
                //role categories with common functions
                if ($roleCategoryDetail['functionAssignType'] == 'same') {
                    $commonRoleCategoryArr[$key]['attrId'] = $roleCategoryDetail['roleCatId'];
                    $commonRoleCategoryArr[$key]['attrName'] = $roleCategoryDetail['roleCatTitle'];
                    $commonRoleCategoryArr[$key]['attrNameLang'][$roleCategoryDetail['roleCatLang']] = $roleCategoryDetail['roleCatTitleLang'];
                    $commonRoleCategoryArr[$key]['attrSortorder'] = $roleCategoryDetail['roleCatSortOrder'];
                }
                //roles with individual functions
                if ($roleCategoryDetail['functionAssignType'] == 'individual') {
                    $key = 'r-' . $roleCategoryDetail['roleId'];
                    $individualRoleArr[$key]['attrId'] = $roleCategoryDetail['roleId'];
                    $individualRoleArr[$key]['attrName'] = $roleCategoryDetail['roleTitle'];
                    $individualRoleArr[$key]['attrNameLang'][$roleCategoryDetail['roleLang']] = $roleCategoryDetail['roleTitleLang'];
                    $individualRoleArr[$key]['attrSortorder'] = $roleCategoryDetail['roleSortOrder'];
                }
            }

            if ((($roleCategoryDetail['clubId'] == $federationId) && $roleCategoryDetail['isFedCategory']) ||
                (($roleCategoryDetail['clubId'] == $this->clubId) && ($this->club->get('type') == 'federation') && $roleCategoryDetail['isFedCategory'])) {
                //fed role categories
                $fedRoleCategoryArr[$key]['attrId'] = $roleCategoryDetail['roleCatId'];
                $fedRoleCategoryArr[$key]['attrName'] = $roleCategoryDetail['roleCatTitle'];
                $fedRoleCategoryArr[$key]['attrNameLang'][$roleCategoryDetail['roleCatLang']] = $roleCategoryDetail['roleCatTitleLang'];
                $fedRoleCategoryArr[$key]['attrSortorder'] = $roleCategoryDetail['roleCatSortOrder'];
                //fed role categories with common functions
                if ($roleCategoryDetail['functionAssignType'] == 'same') {
                    $commonFedRoleCategoryArr[$key]['attrId'] = $roleCategoryDetail['roleCatId'];
                    $commonFedRoleCategoryArr[$key]['attrName'] = $roleCategoryDetail['roleCatTitle'];
                    $commonFedRoleCategoryArr[$key]['attrNameLang'][$roleCategoryDetail['roleCatLang']] = $roleCategoryDetail['roleCatTitleLang'];
                    $commonFedRoleCategoryArr[$key]['attrSortorder'] = $roleCategoryDetail['roleCatSortOrder'];
                }
                //fed roles with individual functions
                if ($roleCategoryDetail['functionAssignType'] == 'individual') {
                    $key = 'r-' . $roleCategoryDetail['roleId'];
                    $individualFedRoleArr[$key]['attrId'] = $roleCategoryDetail['roleId'];
                    $individualFedRoleArr[$key]['attrName'] = $roleCategoryDetail['roleTitle'];
                    $individualFedRoleArr[$key]['attrNameLang'][$roleCategoryDetail['roleLang']] = $roleCategoryDetail['roleTitleLang'];
                    $individualFedRoleArr[$key]['attrSortorder'] = $roleCategoryDetail['roleSortOrder'];
                }
            }

            if ((($roleCategoryDetail['clubId'] == $subFederationId) && $roleCategoryDetail['isFedCategory']) ||
                (($roleCategoryDetail['clubId'] == $this->clubId) && ($this->club->get('type') == 'sub_federation') && $roleCategoryDetail['isFedCategory'])) {
                //sub fed role categories
                $subFedRoleCategoryArr[$key]['attrId'] = $roleCategoryDetail['roleCatId'];
                $subFedRoleCategoryArr[$key]['attrName'] = $roleCategoryDetail['roleCatTitle'];
                $subFedRoleCategoryArr[$key]['attrNameLang'][$roleCategoryDetail['roleCatLang']] = $roleCategoryDetail['roleCatTitleLang'];
                $subFedRoleCategoryArr[$key]['attrSortorder'] = $roleCategoryDetail['roleCatSortOrder'];
                //sub fed role categories with common functions
                if ($roleCategoryDetail['functionAssignType'] == 'same') {
                    $commonSubFedRoleCategoryArr[$key]['attrId'] = $roleCategoryDetail['roleCatId'];
                    $commonSubFedRoleCategoryArr[$key]['attrName'] = $roleCategoryDetail['roleCatTitle'];
                    $commonSubFedRoleCategoryArr[$key]['attrNameLang'][$roleCategoryDetail['roleCatLang']] = $roleCategoryDetail['roleCatTitleLang'];
                    $commonSubFedRoleCategoryArr[$key]['attrSortorder'] = $roleCategoryDetail['roleCatSortOrder'];
                }
                //sub fed roles with individual functions
                if ($roleCategoryDetail['functionAssignType'] == 'individual') {
                    $key = 'r-' . $roleCategoryDetail['roleId'];
                    $individualSubFedRoleArr[$key]['attrId'] = $roleCategoryDetail['roleId'];
                    $individualSubFedRoleArr[$key]['attrName'] = $roleCategoryDetail['roleTitle'];
                    $individualSubFedRoleArr[$key]['attrNameLang'][$roleCategoryDetail['roleLang']] = $roleCategoryDetail['roleTitleLang'];
                    $individualSubFedRoleArr[$key]['attrSortorder'] = $roleCategoryDetail['roleSortOrder'];
                }
            }
        }

        $result = array(
            'roleCategoryArr' => $roleCategoryArr,
            'fedRoleCategoryArr' => $fedRoleCategoryArr,
            'subFedRoleCategoryArr' => $subFedRoleCategoryArr,
            'commonRoleCategoryArr' => $commonRoleCategoryArr,
            'commonFedRoleCategoryArr' => $commonFedRoleCategoryArr,
            'commonSubFedRoleCategoryArr' => $commonSubFedRoleCategoryArr,
            'individualRoleArr' => $individualRoleArr,
            'individualFedRoleArr' => $individualFedRoleArr,
            'individualSubFedRoleArr' => $individualSubFedRoleArr
        );

        return $result;
    }

    /**
     * This function is used to get the team functions and workgroups available in a club/federation/sub_federation
     * 
     * @return array Array of team functions and workgroup details
     */
    private function getTeamAndWorkgroupDetails($terminologyArr)
    {
        $teamFunctionsArr = array();
        $workgroupsArr = array();

        $contactPdoObj = new ContactPdo($this->container);
        $teamAndWorkgroupDetails = $contactPdoObj->getTeamAndWorkgroupDetailsForContactTableColumns();

        foreach ($teamAndWorkgroupDetails as $teamAndWorkgroupDetail) {
            if ($teamAndWorkgroupDetail['roleType'] == 'T') {
                $key = 't-' . $teamAndWorkgroupDetail['functionId'];
                $teamFunctionsArr[$key]['attrId'] = $teamAndWorkgroupDetail['functionId'];
                $teamFunctionsArr[$key]['attrName'] = $teamAndWorkgroupDetail['functionTitle'];
                $teamFunctionsArr[$key]['attrSortOrder'] = $teamAndWorkgroupDetail['functionSortOrder'];
            }
            if ($teamAndWorkgroupDetail['roleType'] == 'W') {
                $key = 'f-' . $teamAndWorkgroupDetail['roleId'];
                $workgroupsArr[$key]['attrId'] = $teamAndWorkgroupDetail['roleId'];
                $workgroupsArr[$key]['attrSortOrder'] = $teamAndWorkgroupDetail['roleSortOrder'];
                $workgroupsArr[$key]['attrName'] = $teamAndWorkgroupDetail['roleTitle'];
                if ((!array_key_exists('attrNameLang', $workgroupsArr[$key])) && $teamAndWorkgroupDetail['isExeBoard']) {
                    $workgroupsArr[$key]['attrNameLang'] = $this->getExecutiveBoardColumnHeadings($terminologyArr);
                    continue;
                }
                if (!$teamAndWorkgroupDetail['isExeBoard']) {
                    $workgroupsArr[$key]['attrName'] = $teamAndWorkgroupDetail['roleTitle'];
                    $workgroupsArr[$key]['attrNameLang'][$teamAndWorkgroupDetail['roleLang']] = $teamAndWorkgroupDetail['roleTitleLang'];
                }
            }
        }
        
        return array('workgroupsArr' => $workgroupsArr, 'teamFunctionsArr' => $teamFunctionsArr);
    }

    /**
     * This function is used to get the executive board column headings in all club languages
     * 
     * @param array $terminologyArr Arraty of club and default terminology terms
     * 
     * @return array $executiveBoardTitlesArr Array of titles
     */
    private function getExecutiveBoardColumnHeadings($terminologyArr)
    {
        $executiveBoardTitlesArr = array();
        $clubLangDetails = $this->club->get('club_languages_det');
        foreach ($clubLangDetails as $lang => $detail) {
            $executiveBoardTitlesArr[$lang] = $this->getTerminologyTerm('Executive Board', $lang, $detail['systemLang'], $terminologyArr);
        }

        return $executiveBoardTitlesArr;
    }

    /**
     * This function is used to get the federation info column options
     * 
     * @param array $terminologyArr Terminology terms
     *  
     * @return array $columnOptions The options of federation info
     */
    private function getFederationInfoColumnOptions($terminologyArr)
    {
        $columnOptions = array();
        $clubLangDetails = $this->club->get('club_languages_det');
        $clubDefaultSysLang = $this->club->get('default_system_lang');
        $clubDefaultLang = $this->club->get('default_lang');
        $hasSubFederation = $this->club->get('hasSubfederation');
        $clubType = $this->club->get('type');

        $columnOptions['fieldName'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_FEDERATION_INFO', array(), 'messages', $clubDefaultSysLang);
        foreach ($clubLangDetails as $lang => $detail) {
            $columnOptions['fieldValue']['clubs']['attrNameLang'][$lang] = ucfirst($this->getTerminologyTerm('Clubs', $lang, $detail['systemLang'], $terminologyArr));
            if ($clubType == 'federation') {
                $executiveBoardTerm = $this->getTerminologyTerm('Executive Board', $lang, $detail['systemLang'], $terminologyArr);
                $clubExecutiveBoardFunctionsTerm = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_CLUB_EXECUTIVE_BOARD_FUNCTIONS', array('%Clubs%' => ucfirst($columnOptions['fieldValue']['clubs']['attrNameLang'][$lang]), '%executive board%' => $executiveBoardTerm), 'messages', $detail['systemLang']);
                $columnOptions['fieldValue']['clubs_executive_board_functions']['attrNameLang'][$lang] = $clubExecutiveBoardFunctionsTerm;
                if ($hasSubFederation) {
                    $columnOptions['fieldValue']['sub_federations']['attrNameLang'][$lang] = ucfirst($this->getTerminologyTerm('Sub-federations', $lang, $detail['systemLang'], $terminologyArr));
                }
            }
        }
        $columnOptions['fieldValue']['clubs']['attrName'] = $columnOptions['fieldValue']['clubs']['attrNameLang'][$clubDefaultLang];
        $columnOptions['fieldValue']['clubs']['attrId'] = 'clubs';
        $columnOptions['fieldValue']['clubs']['attrType'] = 'clubs';
        $columnOptions['fieldValue']['clubs']['attrSortorder'] = 1;
        if ($clubType == 'federation') {
            $columnOptions['fieldValue']['clubs']['clubExecValues'] = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getExecutiveBoardFunctions($this->club->get('id'), $this->club->get('id'), $clubType);
        }
        if ($clubType == 'federation') {
            $columnOptions['fieldValue']['clubs_executive_board_functions']['attrName'] = $columnOptions['fieldValue']['clubs_executive_board_functions']['attrNameLang'][$clubDefaultLang];
            $columnOptions['fieldValue']['clubs_executive_board_functions']['attrId'] = 'clubs_executive_board_functions';
            $columnOptions['fieldValue']['clubs_executive_board_functions']['attrType'] = 'clubs_executive_board_functions';
            $columnOptions['fieldValue']['clubs_executive_board_functions']['attrSortorder'] = 2;
            if ($hasSubFederation) {
                $columnOptions['fieldValue']['sub_federations']['attrName'] = $columnOptions['fieldValue']['sub_federations']['attrNameLang'][$clubDefaultLang];
                $columnOptions['fieldValue']['sub_federations']['attrId'] = 'sub_federations';
                $columnOptions['fieldValue']['sub_federations']['attrType'] = 'sub_federations';
                $columnOptions['fieldValue']['sub_federations']['attrSortorder'] = 3;
            }
        }
        $columnOptions['defaultOption'] = $this->translator->trans('CMS_CONTACT_TABLE_COLUMN_SELECT_FEDERATION_INFO_DEFAULT', array(), 'messages', $clubDefaultSysLang);

        return $columnOptions;
    }

    /**
     * This function is used to get the terminology of a passed term
     * 
     * @param string $term           The term whose terminology is to be returned         
     * @param string $lang           The correspondence language
     * @param string $sysLang        The default system language
     * @param array  $terminologyArr The terminology values in db.
     * 
     * @return string The terminology term
     */
    private function getTerminologyTerm($term, $lang, $sysLang, $terminologyArr)
    {
        $federationId = $this->club->get('federation_id');
        $defaultTerm = ($federationId != 0 && $federationId != $this->clubId) ? (isset($terminologyArr[$term][$federationId]['termLang'][$lang]) && ($terminologyArr[$term][$federationId]['termLang'][$lang] != '') ? $terminologyArr[$term][$federationId]['termLang'][$lang] : $terminologyArr[$term][1]['termLang'][$sysLang]) : $terminologyArr[$term][1]['termLang'][$sysLang];

        return (isset($terminologyArr[$term][$this->clubId]['termLang'][$lang]) && ($terminologyArr[$term][$this->clubId]['termLang'][$lang] != '')) ? $terminologyArr[$term][$this->clubId]['termLang'][$lang] : $defaultTerm;
    }
}
