<?php

/**
 * FgPageElement
 */
namespace Website\CMSBundle\Util;

use Internal\ArticleBundle\Util\ArticlesList;
use Internal\ArticleBundle\Util\ArticleSidebar;

/**
 * FgPageElement - short description
 *
 * FgPageElement - long description
 *
 * @package         package
 * @subpackage      subpackahe
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgPageElement
{

    /**
     * Constructor function
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container   container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to get all areas for article and calendar element for listing in dropdown
     *
     * @return array $areas areas array
     */
    public function getAllAreasForArticleAndCalendar()
    {
        $areas = array();
        $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
        $teamCatId = $this->container->get('club')->get('club_team_id');
        $roles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
        $areas['teams'] = $roles['teams'];
        $areas['workgroups'] = $roles['workgroups'];

        return $areas;
    }

    /**
     * Function to get all gallery list
     * @return array $formatArray
     */
    public function getGalleryList()
    {
        $club = ucfirst($this->container->get('club')->get('title'));
        $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
        $teamCatId = $this->container->get('club')->get('club_team_id');
        $roles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
        $clubRole = array('CG' => $this->container->get('translator')->trans('CLUB_GALLERY', array('%Club%' => $club)));

        return $clubRole + $roles['teams'] + $roles['workgroups'];
    }

    /**
     * Function to get all selected categories and areas for calendar element
     *
     * @param string  $selectedAreasandCategories
     * @return array $areasAndcategories
     */
    public function getAllcategoriesAndAreasForCalendar($selectedAreasandCategories)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $defLang = $club->get('club_default_lang');
        $categoryArray = array();
        $clubArray = array();
        $sharedClubArray = array();
        $areaArray = array();
        $workgroupCatId = $this->container->get('club')->get('club_workgroup_id');
        $teamCatId = $this->container->get('club')->get('club_team_id');
        $allAreaIds = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesIdsOfAClub($this->container, array($teamCatId, $workgroupCatId));
        if ($selectedAreasandCategories['areaIds']) {
            $activeArr = array_intersect(explode(",", $allAreaIds), explode(",", $selectedAreasandCategories['areaIds']));
            if ($activeArr) {
                $areaArray = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getRoleType(implode(",", $activeArr));
            }
        } elseif ($selectedAreasandCategories['isAllArea'] == 1) {
            $allAreaArray = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getRoleType($allAreaIds);
            $clubAreaArray = $this->getClubAreaArray();
            $areaArray = array_merge($allAreaArray, $clubAreaArray);
        } else {
            
        }
        if ($selectedAreasandCategories['areaClub']) {
            $clubArray = $this->getClubAreaArray();
        }
        if ($selectedAreasandCategories['sharedClub']) {
            $sharedClubArray = $this->getSharedAreaArray($selectedAreasandCategories['sharedClub']);
        }
        if ($selectedAreasandCategories['categoryIds']) {
            $categoryArray = $this->getCategoryArray($selectedAreasandCategories['categoryIds']);
        } elseif ($selectedAreasandCategories['isAllCategory'] == 1) {
            $cat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategories($clubId, $defLang);
            $catArr = array();
            foreach ($cat as $value) {
                $catArr[] = $value['id'];
            }
            if (count($catArr) > 0) {
                $catString = implode(",", $catArr);
                $categoryArray = $this->getCategoryArray($catString);
            }
        } else {
            
        }
        $areasAndcategories = array_merge($areaArray, $categoryArray, $clubArray, $sharedClubArray);
        if (count($categoryArray) == 0 && count($sharedClubArray) == 0) {
            $areasAndcategories = array(array('id' => 0, 'type' => 'team'), array('id' => 0, 'type' => 'workgroup'), array('id' => 0, 'type' => 'CLUB'), array('id' => 0, 'type' => 'CA'));
        }
        if (count($areaArray) == 0 && count($clubArray) == 0 && count($sharedClubArray) == 0) {
            $areasAndcategories = array(array('id' => 0, 'type' => 'team'), array('id' => 0, 'type' => 'workgroup'), array('id' => 0, 'type' => 'CLUB'), array('id' => 0, 'type' => 'CA'));
        }

        return $areasAndcategories;
    }

    /**
     * Function to get club details
     *
     *
     * @return array $clubArray
     */
    private function getClubAreaArray()
    {
        $clubArray = array(0 => array(
                'id' => $this->container->get('club')->get('id'),
                'type' => 'CLUB'
            )
        );
        return $clubArray;
    }

    /**
     * Function to get shared club ids and categories
     *
     * @param string $sharedAreas fed/subFed ids
     *
     * @return array $sharedClubArray shared club areas and category array
     */
    private function getSharedAreaArray($sharedAreas)
    {
        $sharedAreaArray = explode(',', $sharedAreas);
        $club = $this->container->get('club');
        $fedId = $club->get('federation_id');
        $fedCat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoryIds($fedId);
        if (count($sharedAreaArray) == 2) {
            $subFedId = $club->get('sub_federation_id');
            $subFedCat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoryIds($subFedId);
            $sharedClubArray = array(
                0 => array('id' => $fedId, 'type' => 'FED'),
                1 => array('id' => 'CA_federation', 'type' => 'CA_LEVELS', 'value' => $fedCat),
                2 => array('id' => $subFedId, 'type' => 'SUBFED'),
                3 => array('id' => 'CA_sub_federation', 'type' => 'CA_LEVELS', 'value' => $subFedCat),
            );
        } else {
            if (in_array($fedId, $sharedAreaArray)) {
                $sharedClubArray = array(
                    0 => array('id' => $fedId, 'type' => 'FED'),
                    1 => array('id' => 'CA_federation', 'type' => 'CA_LEVELS', 'value' => $fedCat)
                );
            } else {
                $subFedId = $club->get('sub_federation_id');
                $subFedCat = $this->container->get('doctrine')->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCalendarCategoryIds($subFedId);
                $sharedClubArray = array(
                    0 => array('id' => $subFedId, 'type' => 'SUBFED'),
                    1 => array('id' => 'CA_sub_federation', 'type' => 'CA_LEVELS', 'value' => $subFedCat)
                );
            }
        }

        return $sharedClubArray;
    }

    /**
     * Function to get all categories array
     *
     * @param string  $category
     * @return array $catTypeArray category array
     */
    private function getCategoryArray($category)
    {
        $categoryArray = explode(',', $category);
        $catTypeArray = array();
        foreach ($categoryArray as $keys => $val) {
            $catTypeArray[$keys]['id'] = $val;
            $catTypeArray[$keys]['type'] = 'CA';
        }

        return $catTypeArray;
    }

    /**
     * Function to get table columns for getting article data
     *
     * @param int    $id  element id/page id
     * @param int    $isPublic   flag to determine whether public page or not
     * @param int    $pageNo     Page Number
     * @param array  $filterArray   Filter Array
     * @param int    $pageNo   Page Number
     * @param int    $offset   Offset
     * @param int    $index   Pagination count
     * @param string $type   Element Type (element/page)
     *
     * @return array $articles article data
     */
    public function getCmsPageArticleElementData($id, $isPublic, $filterArray, $pageNo = 0, $offset = '', $index = 10, $type = 'element')
    {
        $tableColumns = $this->getTableColumns();
        $articleListObj = new ArticlesList($this->container, 'article', $isPublic);

        if ($type == 'element') {
            $selectedAreasandCategories = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getArticleElementDetails($id, 'article');
            $filterArray = $this->getFilterArrayForArticleElement($selectedAreasandCategories);
        } else if ($type == 'page') {
            $selectedAreasandCategories = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getArticlePageDetails($id, 'article');
            $filterArray = array_merge($filterArray, $this->getFilterArrayForArticleElement($selectedAreasandCategories));
        }

        if ($offset !== '') {
            $articleListObj->setOffset($offset, $index);
        } else {
            $articleListObj->setLimit($pageNo, $index);
        }
        $articleListObj->columnData = $tableColumns;
        $articleListObj->filterData = $filterArray;
        $articleListObj->setColumnData();
        $articleListObj->setColumnDataFrom();
        $articleListObj->setGroupBy();
        $articleListObj->addOrderBy();
        $articleListObj->addHaving(array("STATUS = 'published'"));

        return $articleListObj->getArticleData();
    }

    /**
     * Function to get table columns for getting article data
     *
     * @param int    $id  element id/page id
     *
     * @return array $articles article data
     */
    public function getCmsPageArticleTimeperiodData($id, $isPublic = true)
    {

        $articleListObj = new ArticlesList($this->container, 'article', $isPublic);
        $selectedAreasandCategories = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getArticlePageDetails($id, 'article');
        $articleListObj->filterData = $this->getFilterArrayForArticleElement($selectedAreasandCategories);
        $articleSidebarObj = new ArticleSidebar($this->container);
        return $articleSidebarObj->getTimePeriodsForWebsite($articleListObj->getMyVisibleArticleIdsWithFilter());
    }

    /**
     * Function to get table columns for getting article data
     *
     * @return array article listing fields columns
     */
    private function getTableColumns()
    {
        $colArr = array();
        $columns = array('PUBLICATION_DATE', 'ARCHIVING_DATE', 'AUTHOR', 'AREAS', 'CATEGORIES');

        //global club comment settings
        $getGlobalClubSettings = $this->em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($this->clubId);
        $isCommentActive = $getGlobalClubSettings['commentActive'];
        if ($isCommentActive) {
            $columns[] = 'COMMENTS';
        }
        foreach ($columns as $colValue) {
            $colArr[]['id'] = $colValue;
        }

        return $colArr;
    }

    /**
     * Function to get filter array for getting the articles of an article element
     *
     * @param array $selectedAreasandCategories  selected categories and areas of ana article element
     *
     * @return array $filterArray
     */
    private function getFilterArrayForArticleElement($selectedAreasandCategories)
    {

        $filterArray = array();

        //Generates filter array for selected areas
        if ($selectedAreasandCategories['areas']) {
            $workgroupCatId = $this->club->get('club_workgroup_id');
            $teamCatId = $this->club->get('club_team_id');
//          $roles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
            $roles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesIdsOfAClub($this->container, array($teamCatId, $workgroupCatId));
            $activeAreas = array_intersect(explode(',', $selectedAreasandCategories['areas']), explode(',', $roles));
            $areas = implode(',', $activeAreas);
            if ($areas) {
                $filterArray['AREAS'] = $areas;
            }
        }

        //Generates filter array for selected categories
        if ($selectedAreasandCategories['categoryIds']) {
            $categories = implode(',', $selectedAreasandCategories['catIds']);
            $filterArray['CATEGORIES'] = $categories;
        }

        //Generates filter array for all areas
        if ($selectedAreasandCategories['isAllArea'] == 1) {
            $filterArray['AREAS'] = $this->getAllAreasForArticleFilter();
            $filterArray['IS_CLUB'] = 1;
        }

        //Generates filter array for all categories
        if ($selectedAreasandCategories['isAllCategory'] == 1) {
            $filterArray['CATEGORIES'] = $this->getAllCategoriesForArticleFilter($this->clubId);
        }

        //Generates filter array when shared article check box is checked
        if ($selectedAreasandCategories['clubShared']) {
            $filterArray['AREA_CLUB'] = $selectedAreasandCategories['clubShared'];
            $filterArray['CAT_CLUB'] = $selectedAreasandCategories['clubShared'];
            if (!array_key_exists('AREAS', $filterArray)) {
                $filterArray['AREAS'] = 'NULL';
            }
            if (!array_key_exists('CATEGORIES', $filterArray)) {
                $filterArray['CATEGORIES'] = 'NULL';
            }
        }

        //Generates filter array when club is selected in area dropdown
        if ($selectedAreasandCategories['areaClub']) {
            $filterArray['IS_CLUB'] = 1;
            if (!$selectedAreasandCategories['areas']) {
                $filterArray['AREAS'] = 'NULL';
            }
        }

        if (!isset($filterArray['AREA_CLUB'])) {
            if (!isset($filterArray['CATEGORIES'])) {
                $filterArray = array('AREAS' => -1);
            }
            if (!isset($filterArray['AREAS']) && !isset($filterArray['IS_CLUB'])) {
                $filterArray = array('CATEGORIES' => -1);
            }
        }

        return $filterArray;
    }

    /**
     * Function to get data in filter for handling all areas
     *
     * @return string
     */
    private function getAllAreasForArticleFilter()
    {
        $workgroupCatId = $this->club->get('club_workgroup_id');
        $teamCatId = $this->club->get('club_team_id');
        $roles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
        $allRoles = array_merge(array_keys($roles['teams']), array_keys($roles['workgroups']));

        return implode(',', $allRoles);
    }

    /**
     * Function to get data in filter for handling all categories
     *
     * @param array $clubId  current club id
     *
     * @return string
     */
    private function getAllCategoriesForArticleFilter($clubId)
    {
        $clubDefaultLanguage = $this->club->get('club_default_lang');
        $allCategories = $this->em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($clubId, $clubDefaultLanguage);
        $catArray = array();
        foreach ($allCategories as $key => $value) {
            $catArray[$key] = $value['id'];
        }

        return implode(',', $catArray);
    }
}
