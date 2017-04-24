<?php

/**
 * ArticleSidebar.
 *
 * Class to get article sidebar data
 */
namespace Internal\ArticleBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class to get article sidebar data
 *
 * @package 	Internal
 * @subpackage 	Article
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class ArticleSidebar
{
    /**
     * The container object
     * 
     * @var object 
     */
    private $container;
    
    /**
     * The club object
     * 
     * @var object 
     */
    private $club;
    
    /**
     * The contact object
     * 
     * @var object 
     */
    private $contact;
    
    /**
     * The club id
     * 
     * @var int 
     */
    private $clubId;
    
    /**
     * The contact id
     * 
     * @var int 
     */
    private $contactId;
    
    /**
     * The sidebar filter data
     * 
     * @var array 
     */
    private $sidebarFilterData = array();

    /**
     * Class Constructer function.
     *
     * @param ContainerInterface $container Container object
     * @param string             $myArticles
     */
    public function __construct(ContainerInterface $container, $myArticles = '')
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->clubId = $this->club->get('id');
        $this->contactId = $this->contact->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->terminologyService = $this->container->get('fairgate_terminology_service');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->myArticles = $myArticles;
        $this->isMainAdmin = in_array('ROLE_ARTICLE', $this->contact->get('availableUserRights')) ? 1 : 0;
        $this->myRoles = $this->getMyTeamsAndWorkgroups();
        $this->myAdminRolesString = $this->getMyAdminRoleFormatted();
        $this->clubDefaultLanguage = $this->club->get('club_default_lang');
        $this->sidebarData = array();
    }

    /**
     * Function to get sidebar filter date.
     *
     * @return array Sidebar filter data array
     */
    public function getDataForSidebar()
    {
        $this->getAreasForSidebar();
        $this->getCategoriesForSidebar();
        $this->getTimePeriods();

        return $this->sidebarFilterData;
    }

    /**
     * Function to set time perid data.
     */
    public function getTimePeriodsForWebsite($articleIds)
    {
        $defLang = $this->club->get('default_lang');
        return $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getTimeperiodForArticles($this->clubId, $this->club->get('clubHeirarchy'), $defLang, $this->myAdminRolesString, $this->isMainAdmin, $articleIds);
    }

    /**
     * Method get the areas of current club for the sidebar.
     */
    private function getAreasForSidebar()
    {
        $index = count($this->sidebarFilterData);
        $workgroupTitle = $this->container->get('translator')->trans('WORKGROUPS');
        $teamTitle = ucfirst($this->terminologyService->getTerminology('Team', $this->container->getParameter('plural')));
        $defLang = $this->club->get('default_lang');
        $key = 0;
        $clubDataArray = $roleDataArray = array();
        //get the areas to be displayed
        $myOwnAreas = array_keys($this->contact->get('teamsExcludeForeignContactVisibility') + $this->contact->get('workgroupsExcludeForeignContactVisibility'));
        foreach ($this->club->get('clubHeirarchy') as $club => $clubArr) {
            $results = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getRolesWithArticles($club, $defLang, $this->myAdminRolesString, $this->isMainAdmin, 1, $this->myArticles);
            if (count($results) > 0) {
                $federationTerm = ucfirst($clubArr['title']);
                $index = count($clubDataArray) + 1;
                $clubDataArray[$index] = array('title' => $federationTerm, 'id' => $club, 'type' => 'FED');
            }
        }
        $rolesOfCurrent = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getRolesWithArticles($this->clubId, $defLang, $this->myAdminRolesString, $this->isMainAdmin, 0, $this->myArticles);
        foreach ($rolesOfCurrent as $result) {
            if ($result['roleId'] != '') {
                $roleDataArray[$result['type']][] = array('title' => $result['title'], 'id' => $result['roleId'], 'own' => (in_array($result['roleId'], $myOwnAreas) ? true : false));
            } else {
                //is club condition
                $clubDataArray[0] = array('title' => ucfirst($this->club->get('title')), 'id' => $this->clubId, 'type' => 'CLUB');
            }
        }
        $withOutRoles = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleCountWithoutArea($this->clubId);
        if ($withOutRoles[0]['articleCount'] > 0) {
            $this->sidebarFilterData['eventsWithoutArea'] = $withOutRoles[0]['articleCount'];
        }

        if (count($roleDataArray['T']) > 0) {
            $key = count($clubDataArray) + 1;
            $clubDataArray[$key]['title'] = $teamTitle;
            $clubDataArray[$key]['id'] = 'team';
            $clubDataArray[$key]['categoryType'] = 'group';
            $clubDataArray[$key]['subItems'] = $roleDataArray['T'];
        }

        if (count($roleDataArray['W']) > 0) {
            $key = count($clubDataArray) + 1;
            $clubDataArray[$key]['title'] = $workgroupTitle;
            $clubDataArray[$key]['id'] = 'workgroup';
            $clubDataArray[$key]['categoryType'] = 'group';
            $clubDataArray[$key]['subItems'] = $roleDataArray['W'];
        }
        ksort($clubDataArray);

        $this->sidebarFilterData['general'] = $clubDataArray;
    }

    /**
     * Method to get the category for the sidebar.
     */
    private function getCategoriesForSidebar()
    {
        $clubId = $this->club->get('id');
        $defLang = $this->club->get('default_lang');

        $clubHeirarchyDet = $this->club->get('clubHeirarchyDet');
        $currentClubType = $this->club->get('type');
        //Get the categories of each clubs in the hierarchy
        foreach ($clubHeirarchyDet as $club => $clubDetail) {
            $articleCategories = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getCategoriesWithArticle($club, $defLang, 1, $this->myArticles);
            if (count($articleCategories) > 0) {
                $index = count($this->sidebarFilterData['category']);
                $this->sidebarFilterData['category'][$index]['title'] = ucfirst($clubDetail['title']);
                $this->sidebarFilterData['category'][$index]['type'] = $clubDetail['club_type'];
                $this->sidebarFilterData['category'][$index]['id'] = $club;
                //get the categories for the club
                $this->sidebarFilterData['category'][$index]['subItems'] = $articleCategories;
            }
        }

        //Get categories in my club
        //get the categories for the club
        $articleCategories = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getCategoriesWithArticle($clubId, $defLang, false, $this->myArticles);
        if (count($articleCategories) > 0) {
            $lastCat = ($articleCategories[count($articleCategories) - 1]['id'] == 'WA') ? array_pop($articleCategories) : false;
            $index = count($this->sidebarFilterData['category']);
            $this->sidebarFilterData['category'][$index]['title'] = ucfirst($this->club->get('title'));
            $this->sidebarFilterData['category'][$index]['type'] = $currentClubType;
            $this->sidebarFilterData['category'][$index]['id'] = $clubId;
            $this->sidebarFilterData['category'][$index]['subItems'] = $articleCategories;
            if ($lastCat) {
                $index = count($this->sidebarFilterData['category']);
                $this->sidebarFilterData['category'][$index]['type'] = 'CA';
                $this->sidebarFilterData['category'][$index]['id'] = 'WA';
            }
        }
    }

    /**
     * Function to set time perid data.
     */
    private function getTimePeriods()
    {
        $defLang = $this->club->get('default_lang');
        $rolesOfCurrent = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getTimeperiodForArticles($this->clubId, $this->club->get('clubHeirarchy'), $defLang, $this->myAdminRolesString, $this->isMainAdmin);
        $this->sidebarFilterData['years'] = $rolesOfCurrent;
    }

    /**
     * This function is used to get all teams and workgroups in which the logged in user have rights.
     *
     * @return array $myGroups My teams and workgroups
     */
    private function getMyTeamsAndWorkgroups()
    {
        $myAdminGroups = $myMemberGroups = $myGroups = array();

        $groupRights = $this->contact->get('clubRoleRightsGroupWise');

        if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
        }
        if (isset($groupRights['ROLE_ARTICLE_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_ARTICLE_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_ARTICLE_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_ARTICLE_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_ARTICLE_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_ARTICLE_ADMIN']['workgroups']);
        }
        if (isset($groupRights['MEMBER']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['teams']);
        }
        if (isset($groupRights['MEMBER']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['workgroups']);
        }

        $myGroups['MEMBER'] = array_unique($myMemberGroups);
        $myGroups['ADMIN'] = array_unique($myAdminGroups);

        return $myGroups;
    }

    /**
     * Function to format.
     *
     * @return string $myAdminRolesString The string with admin roles
     */
    public function getMyAdminRoleFormatted()
    {
        $myRoles = $this->myRoles;
        $myAdminRoles = $myRoles['ADMIN'];
        ksort($myAdminRoles);

        return implode(',', $myAdminRoles);
    }
}
