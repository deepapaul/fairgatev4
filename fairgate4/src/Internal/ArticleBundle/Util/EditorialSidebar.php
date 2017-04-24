<?php

/**
 * This class is used to get the base query fo creating the sidebar data.
 */
namespace Internal\ArticleBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Repository\Pdo\UtilPdo;

/**
 * This class is used to get the base query fo creating the sidebar data.
 * 
 * @package 	Internal
 * @subpackage 	Article
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
class EditorialSidebar
{

    /**
     * The container object
     * 
     * @var Object 
     */
    private $container;

    /**
     *
     * The club object
     * 
     * @var Object 
     */
    private $club;

    /**
     * The contact object
     * 
     * @var Object 
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
     * @param object $container ContainerInterface Container Object
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->clubId = $this->club->get('id');
        $this->contactId = $this->contact->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->terminologyService = $this->container->get('fairgate_terminology_service');
        $this->em = $this->container->get('doctrine')->getManager();

        $this->isMainAdmin = in_array('ROLE_ARTICLE', $this->contact->get('availableUserRights')) ? 1 : 0;
        $this->myRoles = $this->getMyTeamsAndWorkgroups();
        $this->myAdminRolesString = $this->getMyAdminRoleFormatted();

        $this->clubDefaultLanguage = $this->club->get('club_default_lang');

        $this->sidebarData = array();
    }

    /**
     * Function to get sidebar data.
     *
     * @return Array $sidebarData  Combine all data from category, area,  etc to get sidebar data.
     */
    public function getSidebarData()
    {
        return $this->getGeneralData() +
            $this->iterateAreas() +
            $this->iterateCategories() +
            $this->iterateTimeperiod() +
            $this->getArchivedData();
    }

    /**
     * Club Article Link + Teams + Workgroups + Without Assignments
     * Should include the corresponding counts.
     * If the type is editorial should return the Area that the user have admin privilage
     *
     * @return array Get all areas.
     */
    private function getAreaData()
    {
        $myAreas = array();
        $contact = $this->container->get('contact');
        $allTeams = $contact->get('teams');
        $allWorkgroups = $contact->get('workgroups');

        $isClubOrSuperAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isClubArticleAdmin = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;

        $myAreas['club'] = ucfirst($this->club->get('title'));
        if ($isClubOrSuperAdmin || $isClubArticleAdmin) {
            if ($isClubOrSuperAdmin) {
                $myAreas['teams'] = $allTeams;
                $myAreas['workgroups'] = $allWorkgroups;
            } else {
                $club = $this->container->get('club');
                $workgroupCatId = $club->get('club_workgroup_id');
                $teamCatId = $club->get('club_team_id');
                $assignedRoles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
                $myAreas['teams'] = $assignedRoles['teams'];
                $myAreas['workgroups'] = $assignedRoles['workgroups'];
            }
        } else {
            $myTeams = $myWorkgroups = $assignedTeams = $assignedWorkgroups = array();
            $groupRights = $contact->get('clubRoleRightsGroupWise');
            if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
                $myTeams = array_merge($myTeams, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            }
            if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
                $myWorkgroups = array_merge($myWorkgroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            }
            if (isset($groupRights['ROLE_ARTICLE_ADMIN']['teams'])) {
                $myTeams = array_merge($myTeams, $groupRights['ROLE_ARTICLE_ADMIN']['teams']);
            }
            if (isset($groupRights['ROLE_ARTICLE_ADMIN']['workgroups'])) {
                $myWorkgroups = array_merge($myWorkgroups, $groupRights['ROLE_ARTICLE_ADMIN']['workgroups']);
            }

            foreach ($myTeams as $val) {
                $assignedTeams[$val] = $allTeams[$val];
            }
            $myAreas['teams'] = $assignedTeams;

            foreach ($myWorkgroups as $val) {
                $assignedWorkgroups[$val] = $allWorkgroups[$val];
            }
            $myAreas['workgroups'] = $assignedWorkgroups;
        }
        $myAreas['wa'] = 'Without Assignment';

        return $myAreas;
    }

    /**
     * Club Article Count.
     * If the type is editorial should return the Area that the user have admin privilage
     *
     * @return Int Club article count
     */
    private function getClubArticleCount()
    {
        $adminRoles = $this->myRoles['ADMIN'];

        $clubArticleCount = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')
            ->getClubArticleCount($this->clubId, $this->clubDefaultLanguage, $adminRoles, $this->container);

        return $clubArticleCount[0]['clubArticleCount'];
    }

    /**
     * Categories + Without Categories.
     * If the type is article, should return the areas with articles only
     *
     * @return array category list with article count
     */
    private function getCategoryData()
    {
        return $this->em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')
                ->getArticleCategories($this->clubId, $this->clubDefaultLanguage);
    }

    /**
     * Categories + Without Categories.
     * If the type is article, should return the areas with articles only
     *
     * @return array Time period data with article count.
     */
    private function getTimeperiodData()
    {
        return $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')
                ->getTimeperiodArticle($this->clubId, $this->container->get('club')->get('clubHeirarchy'));
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

    /**
     * Function to execute a sql query.
     *
     * @param string $sql Sql query
     *
     * @return array $result Result array
     */
    public function executeQuery($sql)
    {
        $utilPdo = new UtilPdo($this->container);

        return $utilPdo->executeQuery($sql);
    }

    /**
     * Function to iterate Areas data.
     *
     * @return Array $result General details - All editable + My article
     */
    private function getGeneralData()
    {
        $result = array();
        $result['GEN']['id'] = 'GEN';
        $result['GEN']['title'] = $this->container->get('translator')->trans('ARTICLE_SB_GENERAL');
        $result['GEN']['entry'][0]['id'] = 'AEA';
        $result['GEN']['entry'][0]['title'] = $this->container->get('translator')->trans('ARTICLE_SB_ALL_EDITABLE_ARTICLES');
        $result['GEN']['entry'][0]['isArticle'] = 1;
        $result['GEN']['entry'][0]['itemType'] = 'GEN';
        $result['GEN']['entry'][1]['id'] = 'MA';
        $result['GEN']['entry'][1]['title'] = $this->container->get('translator')->trans('ARTICLE_SB_MY_ARTICLES');
        $result['GEN']['entry'][1]['isArticle'] = 1;
        $result['GEN']['entry'][1]['itemType'] = 'GEN';

        return $result;
    }

    /**
     * Function to iterate Areas data.
     *
     * @return Array $result Area details = club + team + wg + Without assignments
     */
    private function iterateAreas()
    {
        $result = array();
        $areaData = $this->getAreaData();
        $result['AREAS']['id'] = 'AREAS';
        $result['AREAS']['title'] = $this->container->get('translator')->trans('ARTICLE_AREAS');

        $index = 0;
        foreach ($areaData as $key => $areas) {
            if ($key == 'club' && $this->isMainAdmin) {
                $result['AREAS']['entry'][$index]['id'] = 'CLUB';
                $result['AREAS']['entry'][$index]['title'] = ucfirst($this->club->get('title'));
                $result['AREAS']['entry'][$index]['count'] = 0;
                $result['AREAS']['entry'][$index]['itemType'] = 'AREAS';
            } elseif ($key == 'teams' || $key == 'workgroups') {
                $itemType = 'AREAS';
                if (count($areas) > 0) {
                    $result['AREAS']['entry'][$index]['id'] = ($key == 'teams') ? 'TEAM' : 'WG';
                    $result['AREAS']['entry'][$index]['menuType'] = ($key == 'teams') ? 'TEAM' : 'WG';
                    $result['AREAS']['entry'][$index]['title'] = ($key == 'teams') ? ucfirst($this->terminologyService->getTerminology('Team', $this->container->getParameter('plural'))) : $this->container->get('translator')->trans('WORKGROUPS');
                    $roleIndex = 0;
                    foreach ($areas as $roleId => $role) {
                        $result['AREAS']['entry'][$index]['input'][$roleIndex]['id'] = $roleId;
                        $result['AREAS']['entry'][$index]['input'][$roleIndex]['title'] = $role;
                        $result['AREAS']['entry'][$index]['input'][$roleIndex]['isArticle'] = 1;
                        $result['AREAS']['entry'][$index]['input'][$roleIndex]['draggable'] = 1;
                        $result['AREAS']['entry'][$index]['input'][$roleIndex]['itemType'] = $itemType;
                        $result['AREAS']['entry'][$index]['input'][$roleIndex]['count'] = 0;
                        $roleIndex++;
                    }
                }
            } elseif ($key == 'wa' && $this->isMainAdmin) {
                $result['AREAS']['entry'][$index]['id'] = 'WA';
                $result['AREAS']['entry'][$index]['title'] = $this->container->get('translator')->trans('ARTICLE_SB_WITHOUT_ASSIGNMENTS');
                $result['AREAS']['entry'][$index]['count'] = 0;
                $result['AREAS']['entry'][$index]['itemType'] = 'AREAS';
                $result['AREAS']['entry'][$index]['menuType'] = 'WA';
                $result['AREAS']['entry'][$index]['hasWarning'] = 1;
            } else {
                
            }
            $index++;
        }

        return $result;
    }

    /**
     * Function to iterate Time period data.
     *
     * @return Array Time $result period details
     */
    private function iterateTimeperiod()
    {
        $result = array();
        $timeperiodData = $this->getTimeperiodData();
        $result['TIME']['id'] = 'TIME';
        $result['TIME']['menuType'] = 'TIME';
        $result['TIME']['title'] = $this->container->get('translator')->trans('ARTICLE_SB_TIME_PERIODS');
        $key = 0;
        foreach ($timeperiodData as $val) {
            if ($val['count'] > 0) {
                $id = $val['start'] . '__' . $val['end'];
                $result['TIME']['entry'][$key]['id'] = $id;
                $result['TIME']['entry'][$key]['title'] = $val['label'];
                $result['TIME']['entry'][$key]['count'] = 0;
                $result['TIME']['entry'][$key]['isArticle'] = 1;
                $result['TIME']['entry'][$key]['itemType'] = 'TIME';
                $key++;
            }
        }

        return $result;
    }

    /**
     * Function to iterate category data.
     *
     * @return Array $result category details for sidebar
     */
    private function iterateCategories()
    {
        $result = array();
        $categoryData = $this->getCategoryData();
        $result['CAT']['id'] = 'CAT';
        $result['CAT']['menuType'] = 'CAT';
        $result['CAT']['title'] = $this->container->get('translator')->trans('ARTICLE_CATEGORIES');

        foreach ($categoryData as $key => $val) {
            $result['CAT']['entry'][$key]['id'] = $val['id'];
            $result['CAT']['entry'][$key]['title'] = $val['title'];
            $result['CAT']['entry'][$key]['categoryId'] = $val['id'];
            $result['CAT']['entry'][$key]['count'] = 0;
            $result['CAT']['entry'][$key]['isArticle'] = 1;
            $result['CAT']['entry'][$key]['draggable'] = 1;
            $result['CAT']['entry'][$key]['itemType'] = 'CAT';
        }
        //make without category at the end of this array
        $index = count($categoryData);
        $result['CAT']['entry'][$index]['id'] = 'WA';
        $result['CAT']['entry'][$index]['title'] = $this->container->get('translator')->trans('ARTICLE_SB_WITHOUT_CATEGORY');
        $result['CAT']['entry'][$index]['count'] = 0;
        $result['CAT']['entry'][$index]['isArticle'] = 1;
        $result['CAT']['entry'][$index]['itemType'] = 'CAT';
        $result['CAT']['entry'][$index]['menuType'] = 'WC';
        $result['CAT']['entry'][$index]['hasWarning'] = 1;

        return $result;
    }

    /**
     * Function to iterate Archived data.
     *
     * @return Array $result Archived article details
     */
    private function getArchivedData()
    {
        $result = array();
        $result['ARCHIVE']['id'] = 'ARCHIVE';
        $result['ARCHIVE']['title'] = $this->container->get('translator')->trans('ARTICLE_SB_ARCHIVE');
        $result['ARCHIVE']['entry'][0]['id'] = 'ARCHIVE_ART';
        $result['ARCHIVE']['entry'][0]['title'] = $this->container->get('translator')->trans('ARTICLE_SB_ARCHIVED_ARTICLES');
        $result['ARCHIVE']['entry'][0]['isArticle'] = 1;
        $result['ARCHIVE']['entry'][0]['itemType'] = 'ARCHIVE';

        return $result;
    }
}
