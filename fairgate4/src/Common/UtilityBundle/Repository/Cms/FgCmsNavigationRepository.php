<?php
/**
 * FgCmsNavigationRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsNavigation;
use Common\UtilityBundle\Entity\FgCmsNavigationI18n;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgCmsNavigationRepository
 *
 * This class is used for handling navigation insert, update, delete functionalities.
 */
class FgCmsNavigationRepository extends EntityRepository
{

    /**
     * @var string $clubDefaultLang Club default language
     */
    private $clubDefaultLang = 'en';

    /**
     * @var int $clubId Club Id
     */
    private $clubId;

    /**
     * @var int $contactId Contact id
     */
    private $contactId;

    /**
     * @var array $tempNavIds Mapping array of temporary ids and actual ids
     */
    private $tempNavIds = array();

    /**
     * @var array $parentNavIds Mapping array of ids and temporary parent ids
     */
    private $parentNavIds = array();

    /**
     * This function is used to get details of all navigation points
     *
     * @param object $container   Container
     * @param int    $clubId      Club id
     * @param string $contactLang Contact correspondence language
     * @param boolean $isAssignedCheck page assignment check
     * @param int  $isaddtional  additional navigation check
     * 
     * @return array $result The result set
     */
    public function getNavigationDetails($container, $club, $clubId, $contactLang, $isAssignedCheck = false, $displaySettings = false, $contactId = 1, $isaddtional = null)
    {
        $q = $this->createQueryBuilder('n')
            ->select("n.id, IDENTITY(n.parent) AS parentId, n.title, n.isActive, n.navigationUrl,n.isPublic, n.sortOrder, n.externalLink, IDENTITY(n.page) AS pageId, n.type as pageType, 0 AS isNew, ni18n.titleLang, ni18n.lang, (CASE WHEN (pi18n.titleLang != '' AND pi18n.titleLang IS NOT NULL) THEN pi18n.titleLang ELSE p.title END) AS pageTitle")
            ->addSelect("(SELECT COUNT(n2.id) FROM CommonUtilityBundle:FgCmsNavigation n2 WHERE n2.parent=n.id) AS subMenuCount")
            ->leftJoin('CommonUtilityBundle:FgCmsNavigationI18n', 'ni18n', 'WITH', 'ni18n.id = n.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPage', 'p', 'WITH', 'p.id = n.page')
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'pi18n', 'WITH', 'pi18n.id = p.id AND pi18n.lang = :contactLang')
            ->where('n.club= :clubId');
        if ($isAssignedCheck) {
            $q->andWhere('n.page is not NULL');
        }

        if ($isaddtional !== null) {

            $q->andWhere('n.isAdditional=' . $isaddtional);
        }
        if ($displaySettings) {
            $q->andWhere('n.isActive=1');
        }
        if ($contactId == 0) {
            $q->andWhere('n.isPublic=1');
        }
        $q->addOrderBy('n.sortOrder', 'ASC')
            ->addOrderBy('n.parent', 'ASC')
            ->setParameters(array('clubId' => $clubId, 'contactLang' => $contactLang));

        $navArr = $q->getQuery()->getArrayResult();
        $baseUrlArray = FgUtility::generateUrlForCkeditor($container ,$clubId ,0 );
        $baseUrl= $baseUrlArray['baseUrlWithUrlIdentifier'];
        $result = array();
        foreach ($navArr as $arr) {
            $id = $arr['id'];
            if (isset($result[$id])) {
                $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
            } else {
                $lang = $arr['lang'];
                $titleLang = $arr['titleLang'];
                unset($arr['titleLang']);
                unset($arr['lang']);
                $arr['titleLang'][$lang] = $titleLang;
                $result[$id] = $arr;
            }
            $result[$id]['baseUrl'] = $baseUrl;
        }

        //Remove apc cache entries while updating cms navigations
        $this->navClearCache($club, $container);
        //Remove apc cache entries while updating cms navigations

        return $result;
    }

    /**
     * This function is used to update the navigation point details
     *
     * @param object $container Container object
     * @param array  $dataArray Data to be saved
     * @param int  $isaddtional  additional navigation check
     */
    public function saveNavigationDetails($container, $dataArray, $isAdditional)
    {
        $club = $container->get('club');
        $this->clubId = $club->get('id');
        $this->contactId = $container->get('contact')->get('id');
        $this->clubDefaultLang = $club->get('club_default_lang');
        if (count($dataArray) > 0) {
            foreach ($dataArray as $navId => $navData) {
                $title = $navData['title'][$this->clubDefaultLang];
                $navigationObj = $this->find($navId);
                if ($navData['is_deleted'] == 1) {
                    $this->deleteNavigation($navigationObj);
                    continue;
                }
                //unassign page from navigation
                if ($navData['unAssign'] == 1) {
                    $this->unAssignPageFromNav($navId, $container);
                }
                if (empty($navigationObj)) {
                    if (trim($title) != '') {  //Checking WhiteSpace Entry
                        $this->insertNavigation($navId, $navData, $container, $isAdditional);
                    }
                } else {
                    $this->updateNavigation($navigationObj, $navData, $container);
                }
            }
            $this->updateParentIds();
            $this->_em->flush();

            //update all main table navigation title entries of this club with the value in club default lang
            $cmsPdo = new CmsPdo($container);
            $cmsPdo->updateNavigationTitle(array('clubId' => $this->clubId, 'clubDefaultLang' => $this->clubDefaultLang));

            //Remove apc cache entries while updating cms navigations
            $this->navClearCache($club, $container);
            //Remove apc cache entries while updating cms navigations
        }
    }
    /**
     *  @ApiDoc(
     *  resource=true,
     *  section = "User",
     *  description="Action to get user details.",
     *  output = "\Symfony\Component\HttpFoundation\JsonResponse",
     *  parameters={
     *  {"name"="user_id", "dataType"="integer", "required"=false, "description"="Id of a specific user"},
     *  {"name"="email_d", "dataType"="string", "required"=false, "description"="Unique email id of the user"},
     *  })
     */

    /**
     * This function is used to insert a navigation point data
     *
     * @param timestamp $tempNavId The temporary
     * @param array     $data
     * @param object    container object
     * @param int  $isaddtional  additional navigation check
     */
    private function insertNavigation($tempNavId, $data, $container, $isAdditional)
    {
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $this->clubId);
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);

        $conn = $this->_em->getConnection();
        $title = FgUtility::getSecuredData($data['title'][$this->clubDefaultLang], $conn, false);
        $navigationObj = new FgCmsNavigation();
        $navigationObj->setTitle($title);
        $navigationObj->setClub($clubObj);
        $navigationObj->setIsActive($data['isActive']);
        $navigationObj->setSortOrder($data['sortOrder']);
        $navigationObj->setIsPublic($data['isPublic']);
        $navigationObj->setIsAdditional($isAdditional);
        $navigationObj->setCreatedBy($contactObj);
        $navigationObj->setCreatedAt(new \DateTime("now"));
        if (isset($data['parentId'])) {
            $parentId = $this->getParentId($data['parentId']);
            $navObj = $this->find($parentId);
            $navigationObj->setParent($navObj);
        }
        //FAIR-2330 -- CMS::Navigation management - urlIdentifier - validation
        $navIdentifier = FgUtility::urlIdentifierValidation($title, $container);
        //If same identifier exists for the club
        $i = 1;
        $navIdentifier = $this->checkUrlAlreadyExist($navIdentifier, $navIdentifier, $i);
        $navigationObj->setNavigationUrl($navIdentifier);
        $this->_em->persist($navigationObj);
        $this->_em->flush();

        $navigationId = $navigationObj->getId();
        $this->tempNavIds[$tempNavId] = $navigationId;
        if (isset($data['parentId']) && $data['parentId'] != '1') {
            $this->parentNavIds[$navigationId] = $data['parentId'];
        }
        if (isset($data['title'])) {
            $this->saveNavigationI18n($navigationId, $data['title']);
        }
    }

    /**
     * This function is used for updating navigation point
     *
     * @param object $navigationObj Navigation object
     * @param array  $data          Data set
     */
    private function updateNavigation($navigationObj, $data, $container)
    {
        unset($data['is_deleted']);
        if (count($data) > 0) {
            $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);
            if (isset($data['title'][$this->clubDefaultLang])) {
                $title = $data['title'][$this->clubDefaultLang];
                $navigationObj->setTitle($title);
                //FAIR-2330 -- CMS::Navigation management - urlIdentifier - validation
                $navIdentifier = FgUtility::urlIdentifierValidation($title, $container);
                //If same identifier exists for the club
                $i = 1;
                $navIdentifier = $this->checkUrlAlreadyExist($navIdentifier, $navIdentifier, $i, $navigationObj->getId());
                $navigationObj->setNavigationUrl($navIdentifier);
            }
            if (isset($data['isActive'])) {
                $navigationObj->setIsActive($data['isActive']);
            }
            if (isset($data['sortOrder'])) {
                $navigationObj->setSortOrder($data['sortOrder']);
            }
            if (isset($data['isPublic'])) {
                $navigationObj->setIsPublic($data['isPublic']);
            }
            $navigationObj->setEditedBy($contactObj);
            $navigationObj->setEditedAt(new \DateTime("now"));
            if (isset($data['parentId'])) {
                $parentId = $this->getParentId($data['parentId']);
                $navObj = $this->find($parentId);
                $navigationObj->setParent($navObj);
                if ($data['parentId'] != '1') {
                    $this->parentNavIds[$navigationObj->getId()] = $data['parentId'];
                }
            }
            $this->_em->persist($navigationObj);
            if (isset($data['title'])) {
                $this->saveNavigationI18n($navigationObj->getId(), $data['title']);
            }
        }
    }

    /**
     * function to check if url identifier already exist
     *
     * @param string  $url
     * @param string  $urlToCheck
     * @param integer $i
     *
     * @return string final url identifier
     */
    private function checkUrlAlreadyExist($url, $urlToCheck, $i, $navId = 0)
    {
        //repository function to check if url identifier already exist
        $urlExist = $this->isUniqueNavMenuIdentifier($urlToCheck, $navId);
        $urlContainsRestrictKeywords = FgUtility::hasUrlContainsRestrictKeywords($url);
        //checking if url already exist or url contains restrict keyswords at first time($i = 1)
        if ($urlExist || (($urlContainsRestrictKeywords) && ($i == 1))) {
            $newUrl = $url . $i;
            $i = $i + 1;
            $urlToCheck = $this->checkUrlAlreadyExist($url, $newUrl, $i);
        }

        return $urlToCheck;
    }

    /**
     * This function is used to check whether the manu identifier is unique or not
     *
     * @param string $identifier Nav identifier
     *
     * @return int $isUnique Whether identifier is unique or not 0/1
     */
    private function isUniqueNavMenuIdentifier($identifier, $navId = 0)
    {
        $data = array('clubId' => $this->clubId, 'url' => $identifier);
        $qs = $this->createQueryBuilder('n')
            ->select('n.navigationUrl')
            ->where('n.club =:clubId')
            ->andWhere('n.navigationUrl =:url');
        if ($navId != 0) {
            $qs->andWhere('n.id != :id');
            $data['id'] = $navId;
        }

        $qs->setParameters($data);

        if (count($qs->getQuery()->getArrayResult())) {
            $isUnique = 1;
        }

        return $isUnique;
    }

    /**
     * This function is used to get the parent id of a navigation
     *
     * @param int $navId Navigation id
     *
     * @return int $parentId id or null if new entry
     */
    private function getParentId($navId)
    {
        $navObj = $this->find($navId);
        if (empty($navObj)) {
            if (array_key_exists($navId, $this->tempNavIds)) {
                $parentId = $this->tempNavIds[$navId];
            }
        } else {
            $parentId = $navId;
        }

        return $parentId;
    }

    /**
     * This function is used to update the relation between nav menus and their parents
     */
    private function updateParentIds()
    {
        foreach ($this->parentNavIds as $navId => $tempParentId) {
            $parentId = $this->tempNavIds[$tempParentId];
            if ($parentId !== null) {
                $navParentObj = $this->find($parentId);
                $navObj = $this->find($navId);
                $navObj->setParent($navParentObj);
            }
        }
    }

    /**
     * This function is used to save the navigation points i18n data
     *
     * @param int   $navId     Navigation id
     * @param array $titleLang Array of titles in all languages
     */
    private function saveNavigationI18n($navId, $titleLang)
    {
        $navigationMainObj = $this->_em->getReference('CommonUtilityBundle:FgCmsNavigation', $navId);
        $conn = $this->_em->getConnection();
        foreach ($titleLang as $lang => $title) {
            $title = FgUtility::getSecuredData($title, $conn, false);
            $navigationI18nObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsNavigationI18n')->findOneBy(array('lang' => $lang, 'id' => $navId));
            if (empty($navigationI18nObj)) {
                $navigationI18nObj = new FgCmsNavigationI18n();
                $navigationI18nObj->setId($navigationMainObj);
                $navigationI18nObj->setLang($lang);
                $navigationI18nObj->setTitleLang($title);
                $this->_em->persist($navigationI18nObj);
            } else {
                $this->_em->getRepository('CommonUtilityBundle:FgCmsNavigationI18n')->updateNavigationI18n($navId, $lang, $title);
            }

            $this->_em->flush();
        }
    }

    /**
     * This function is used to delete a navigation point
     *
     * @param object $navigationObj
     */
    public function deleteNavigation($navigationObj)
    {
        $this->_em->remove($navigationObj);
    }

    /**
     * This function is used to assign page to navigation
     *
     * @param object $container symfony container
     * @param int    $navId     Navigation Id
     * @param int    $pageId    Page Id
     * @param string $module    modulename
     *
     * @return int $isAssigned if page is assigned to navigation or not
     */
    public function savePageToNavigation($container, $navId, $pageId, $module)
    {
        $isAssigned = 0;
        $this->contactId = $container->get('contact')->get('id');
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);
        $navigationObj = $this->find($navId);

        if (!empty($navigationObj)) {
            $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
            if ($module == 'existing' || $module == 'duplicate') {
                $navigationObj->setType('page');
                $navigationObj->setPage($pageObj);
            } elseif ($module == 'gallery' || $module == 'calendar' || $module == 'article') {
                $navigationObj->setType($module);
                $navigationObj->setPage($pageObj);
            } elseif ($module == 'external' || $module == 'editExternal') {
                $navigationObj->setType('external');
                $navigationObj->setExternalLink($pageId);
            } elseif ($module == 'delete') {
                $navigationObj->setType(null);
                $navigationObj->setExternalLink(null);
            }
            $navigationObj->setEditedBy($contactObj);
            $navigationObj->setEditedAt(new \DateTime("now"));
            $this->_em->persist($navigationObj);
            $this->_em->flush();
            $isAssigned = 1;

            //Remove apc cache entries while updating cms navigations
            $this->navClearCache($container->get('club'), $container);
            //Remove apc cache entries while updating cms navigations
        }

        return $isAssigned;
    }

    /**
     * This function is used to check whether page is assigned to navigation
     *
     * @param int  $pageId Page Id
     *
     * @return array $assignedArr array of navigation point ids
     */
    public function checkPageAssignedToNavigation($pageId)
    {
        $q = $this->createQueryBuilder('n')
            ->select('n.id')
            ->where('n.page= :pageId')
            ->setParameter('pageId', $pageId);

        return $q->getQuery()->getArrayResult();
    }

    /**
     * This function is used to unassign pages from navigation
     *
     * @param array  $navIds navigation Id
     * @param object  $container
     */
    public function unAssignPageFromNav($navIds, $container)
    {
        $dateTime = new \DateTime("now");
        $this->contactId = $container->get('contact')->get('id');
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);
        $qb = $this->createQueryBuilder();
        $q = $qb->update('CommonUtilityBundle:FgCmsNavigation', 'n')
            ->set('n.page', 'NULL')
            ->set('n.type', 'NULL')
            ->set('n.editedBy', ':editedBy')
            ->set('n.editedAt', ':editedAt')
            ->where('n.id IN (:navId)')
            ->setParameter('editedBy', $contactObj)
            ->setParameter('editedAt', $dateTime)
            ->setParameter('navId', $navIds)
            ->getQuery();
        $q->execute();

        //Remove apc cache entries while updating cms navigations
        $this->navClearCache($container->get('club'), $container);
        //Remove apc cache entries while updating cms navigations
    }

    /**
     * This function is used to build the sidebar navigation menus
     *
     * @param object  $container  Container object
     * @param boolean $formatFlag Whether to format the result apt for building sidebar
     * @param int  $isaddtional  additional navigation check
     *
     * @return array Data to build sidebar
     */
    public function getSidebarNavigationMainMenus($container, $formatFlag = true, $isAdditional = null)
    {
        $club = $container->get('club');
        $clubId = $club->get('id');
        $contactLang = $club->get('default_lang');

        $q = $this->createQueryBuilder('n')
            ->select("n.id, 'MM' as itemType, IDENTITY(n.parent) AS parentId, (CASE WHEN (ni18n.titleLang != '' AND ni18n.titleLang IS NOT NULL) "
                . "THEN ni18n.titleLang ELSE n.title END) AS title, IDENTITY(n.page) AS pageId, n.type AS pageType, "
                . "(CASE WHEN (n.externalLink IS NOT NULL AND n.externalLink != '') THEN 1 ELSE 0 END) AS hasExLink, n.externalLink, n.isActive, "
                . "n.isPublic, n.sortOrder, (CASE WHEN (pageLang.titleLang != '' AND pageLang.titleLang IS NOT NULL) "
                . "THEN pageLang.titleLang ELSE page.title END) AS pageTitle ")
            ->leftJoin('CommonUtilityBundle:FgCmsNavigationI18n', 'ni18n', 'WITH', 'ni18n.id = n.id AND ni18n.lang = :contactLang')
            ->leftJoin('CommonUtilityBundle:FgCmsPage', 'page', 'WITH', 'page.id = n.page ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'pageLang', 'WITH', 'page.id = pageLang.id AND pageLang.lang = :contactLang')
            ->where('n.club= :clubId');
        if ($isAdditional !== null) 
        {
            $q->andWhere('n.isAdditional =' . $isAdditional);
        }

        $q->addOrderBy('n.parent', 'ASC')
            ->addOrderBy('n.sortOrder', 'ASC')
            ->setParameters(array('clubId' => $clubId, 'contactLang' => $contactLang));
        $navArr = $q->getQuery()->getArrayResult();

        return (($formatFlag) ? $this->formatResultForSidebar($navArr) : $navArr);
    }

    /**
     * This function is used to format the result apt for sidebar
     *
     * @param array $result The result set
     *
     * @return array Formatted result set
     */
    private function formatResultForSidebar($result)
    {
        $menuIds = array_column($result, 'id');
        $menuDetails = array_combine($menuIds, $result);

        return $this->getFormattedMenuDetails($menuDetails);
    }

    /**
     * This function is used to format the result apt for sidebar
     *
     * @param array $navigationArr Input flat result
     *
     * @return array $treeNavArr Output tree result
     */
    private function getFormattedMenuDetails($navigationArr)
    {
        $flatNavArr = array();
        $treeNavArr = array();

        foreach ($navigationArr as $childId => $childDetails) {
            $parentId = $childDetails['parentId'];
            if (!isset($flatNavArr[$childId])) {
                $flatNavArr[$childId] = $childDetails;
            } else {
                $flatNavArr[$childId] = array_merge($childDetails, $flatNavArr[$childId]);
            }
            if ($parentId != 1) {
                $flatNavArr[$parentId]['input'][] = & $flatNavArr[$childId];
            } else {
                $treeNavArr[] = & $flatNavArr[$childId];
            }
        }

        return $treeNavArr;
    }

    /**
     * This function is used to get the menu assignment details
     *
     * @param int    $clubId      Club id
     * @param string $contactLang Contact correspondence lang
     *
     * @return array The result details
     */
    public function getMenuAssignmentDetails($clubId, $contactLang)
    {
        $q = $this->createQueryBuilder('n')
            ->select("n.id, IDENTITY(n.page) AS pageId, n.type AS pageType, (CASE WHEN (n.externalLink IS NOT NULL AND n.externalLink != '') THEN 1 ELSE 0 END) AS hasExLink "
                . ",(CASE WHEN (pageLang.titleLang != '' AND pageLang.titleLang IS NOT NULL) THEN pageLang.titleLang ELSE page.title END) AS pageTitle ")
            ->leftJoin('CommonUtilityBundle:FgCmsPage', 'page', 'WITH', 'page.id = n.page ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'pageLang', 'WITH', "page.id = pageLang.id AND pageLang.lang = '$contactLang'")
            ->where('n.club= :clubId')
            ->addOrderBy('n.parent', 'ASC')
            ->addOrderBy('n.sortOrder', 'ASC')
            ->setParameters(array('clubId' => $clubId));

        return $q->getQuery()->getArrayResult();
    }

    /**
     * Function to check whether the navigation point exists or not
     *
     * @param int    $clubId     Club id
     * @param string $url        Navigation url identifier
     * @param int    $activeFlag Flag status
     *
     * @return boolean
     */
    public function checkNavigationUrlExists($clubId, $url, $activeFlag = 1)
    {
        $qs = $this->createQueryBuilder('n')
            ->select('n.navigationUrl')
            ->where('n.club =:clubId')
            ->andWhere('n.navigationUrl =:url')
            ->andWhere('n.isActive =:active')
            ->setParameters(array('clubId' => $clubId, 'url' => $url, 'active' => $activeFlag));

        if (count($qs->getQuery()->getArrayResult())) {
            return true;
        }

        return false;
    }

    /**
     * Function to get the page details using url identifier
     *
     * @param int    $clubId     Club id
     * @param string $url        Navigation url identifier
     * @param int    $activeFlag Flag status
     *
     * @return boolean
     */
    public function getPageDetails($clubId, $url, $activeFlag = 1)
    {
        $qs = $this->createQueryBuilder('n')
            ->select('p.id', 'n.type')
            ->leftJoin('n.page', 'p')
            ->where('n.club =:clubId')
            ->andWhere('n.navigationUrl =:url')
            ->andWhere('n.isActive =:active')
            ->andWhere('n.page IS NOT NULL')
            ->setParameters(array('clubId' => $clubId, 'url' => $url, 'active' => $activeFlag));

        $pageDetails = $qs->getQuery()->getArrayResult();

        if (count($pageDetails)) {
            return $pageDetails;
        } else {
            return false;
        }
    }

    /**
     * This function is used to get supplementary menu details
     *
     * @param int     $navigationId             Navigation id
     * @param array   $navigationHeirarchy      navigation Heirarchy
     * @param object  $container                Container object
     *
     * @return array  $resultArr     navigation detailos array
     */
    public function getSupplementaryElementDetails($navigationId, $navigationHeirarchy, $contactLang, $container)
    {
        $club = $container->get('club');
        $clubLang = $club->get('club_default_lang');
        $pkey = $this->calcSuppleParentKey($navigationId, $navigationHeirarchy);
        if ($pkey < 0) {
            $navigationHeirarchy = array();
        }
        $baseUrlArray = FgUtility::generateUrlForCkeditor($container ,$club->get('id') ,0 );
        $baseUrl= $baseUrlArray['baseUrlWithUrlIdentifier'];
        $navDet = $flag = array();
        $parentIdKey = -1;

        foreach ($navigationHeirarchy as $key => $value) {
            if ($key > $pkey) {
                //ACCORDING to current array structure - break point => next level1 parent menu comes only after our required parent child combo

                if ($value['parent_id'] == 1) {
                    break;
                }

                switch ($value['level']) {
                    case 2: //if level 2 menu item
                        $parentId = $value['id'];
                        $parentIdKey = 'E_' . $value['id'];
                        $navDet["$parentIdKey"]['parentId'] = $parentId;
                        $navDet["$parentIdKey"]['parentTitle'] = (!empty($value['langTitle'][$contactLang]) && $value['langTitle'][$contactLang]['title_lang'] != '') ? $value['langTitle'][$contactLang]['title_lang'] : ((!empty($value['langTitle'][$clubLang]) && $value['langTitle'][$clubLang]['title_lang'] != '') ? $value['langTitle'][$clubLang]['title_lang'] : $value['title']);
                        $navDet["$parentIdKey"]['parentUrlType'] = ($value['external_link'] != '') ? 'external' : 'pageassigned';
                        //if page not assigned and not external link..then provided url will be null
                        $navDet["$parentIdKey"]['parentUrl'] = ($value['page_id'] == '') ? $value['external_link'] : $baseUrl . '/' . $value['navigation_url'];
                        $navDet["$parentIdKey"]['navigationUrl'] = $baseUrl . '/' .$value['navigation_url'];
                        $flag["$parentIdKey"] = ($navDet["$parentIdKey"]['parentUrl'] == '') ? true : false;
                        break;
                    case 3:
                        //level3 menu item - FAIR 2427 Hide menu items that have no content and no sub-item with content
                        if ($parentId == $value['parent_id'] && ($value['external_link'] != '' || $value['page_id'] != '')) {
                            $childId = $value['id'];
                            $childKey = 'E_' . $value['id'];
                            $navDet["$parentIdKey"]['child']["$childKey"]['id'] = $childId;
                            $navDet["$parentIdKey"]['child']["$childKey"]['title'] = (!empty($value['langTitle'][$contactLang]) && $value['langTitle'][$contactLang]['title_lang'] != '') ? $value['langTitle'][$contactLang]['title_lang'] : ((!empty($value['langTitle'][$clubLang]) && $value['langTitle'][$clubLang]['title_lang'] != '') ? $value['langTitle'][$clubLang]['title_lang'] : $value['title']);
                            $navDet["$parentIdKey"]['child']["$childKey"]['childUrlType'] = ($value['external_link'] != '') ? 'external' : 'pageassigned';
                            //if page is not assigned and there is no external link..then provided url will be null
                            $navDet["$parentIdKey"]['child']["$childKey"]['url'] = ($value['page_id'] == '') ? $value['external_link'] : $baseUrl . '/' . $value['navigation_url'];
                            $navDet["$parentIdKey"]['child']["$childKey"]['navigationUrl'] = $baseUrl . '/' .$value['navigation_url'];
                            if (($flag["$parentIdKey"] == true) && ($navDet["$parentIdKey"]['child']["$childKey"]['url'] != '')) {
                                //FAIR-2366 If parent menu has no pages assigned, then the page assigned of first child menu or external link of first child menu should be provided to the parent.
                                $navDet["$parentIdKey"]['parentUrlType'] = $navDet["$parentIdKey"]['child']["$childKey"]['childUrlType'];
                                $navDet["$parentIdKey"]['parentUrl'] = $navDet["$parentIdKey"]['child']["$childKey"]['url'];
                                $flag["$parentIdKey"] = false;
                            }
                            break;
                        }
                }
            }
        }
        $navDet = $this->unsetArrayWithNoUrl($navDet);

        return array('navDet' => $navDet, 'active' => 'E_' . $navigationId);
    }

    /**
     * function to unset array with no url
     * @param array $navDet navigation Detetails
     * 
     * @return array
     */
    private function unsetArrayWithNoUrl($navDet)
    {
        //unset previously build array if parent has no url
        //FAIR 2427 Hide menu items that have no content and no sub-item with content
        return array_filter($navDet, function($item) {
            return count($item) == count(array_filter($item));
        });
    }

    /**
     * calculate Supplementary Parent Key
     * @param int    $navigationId
     * @param array  $navigationHeirarchy
     *
     * @return int   Parent Key
     */
    private function calcSuppleParentKey($navigationId, $navigationHeirarchy)
    {
        $navIdArray = array_column($navigationHeirarchy, 'id');
        $navKey = array_search($navigationId, $navIdArray);
        if ($navKey > -1) {
            $parentId = $navigationHeirarchy[$navKey]['parent_id'];
            if ($navigationHeirarchy[$navKey]['level'] == 3) {
                $parentSecKey = array_search($parentId, $navIdArray);
                if ($parentSecKey > -1) {
                    $parentId = $navigationHeirarchy[$parentSecKey]['parent_id'];
                    $pKey = array_search($parentId, $navIdArray);
                    return ($pKey > -1) ? $pKey : -1;
                } else {
                    return -1;
                }
            } elseif ($navigationHeirarchy[$navKey]['level'] == 2) {
                $pKey = array_search($parentId, $navIdArray);
                return ($pKey > -1) ? $pKey : -1;
            } else {
                $pKey = array_search($navigationId, $navIdArray);
                return ($pKey > -1) ? $pKey : -1;
            }
        } else {
            return -1;
        }
    }

    /**
     * This function is used to get details of additional navigation points
     *
     * @param int    $clubId      Club id
     * @param int    $contactId   Contact id
     * @param string $contactLang Contact correspondence language
     * @param object $container   Container object
     * @param string $menu        Navigation url  
     * 
     * @return array $result The result set
     */
    public function getAddtionalNavigationDetails($clubId, $contactId, $contactLang, $container, $menu = null)
    {
        
        $domainCacheKey = $container->getParameter('database_name');
        $cacheLifeTime = $container->getParameter('cache_lifetime');
        $cachingEnabled = $container->getParameter('caching_enabled');
        $cacheKey = $domainCacheKey . '_navigation' . $clubId;
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $q = $this->createQueryBuilder('n')
            ->select("n.id,n.isAdditional, IDENTITY(n.parent) AS parentId, (CASE WHEN (ni18n.titleLang != '' AND ni18n.titleLang IS NOT NULL) THEN ni18n.titleLang ELSE n.title END) AS title , n.isActive, n.navigationUrl,n.isPublic, n.sortOrder, n.externalLink, IDENTITY(n.page) AS pageId, n.type as pageType, 0 AS isNew")
            ->leftJoin('CommonUtilityBundle:FgCmsNavigationI18n', 'ni18n', 'WITH', 'ni18n.id = n.id AND ni18n.lang = :contactLang')
            ->leftJoin('CommonUtilityBundle:FgCmsPage', 'p', 'WITH', 'p.id = n.page')
            ->where('n.club= :clubId')
            ->andWhere("(n.page is not NULL OR n.type='external')")
            ->andWhere('n.isAdditional = 1')
            ->andWhere('n.isActive=1');
        if ($contactId == 0) {
            $q->andWhere('n.isPublic=1');
        }
        if ($menu !== null) {
            $q->andWhere('n.navigationUrl= :menu');
        }
        $q->addOrderBy('n.sortOrder', 'ASC')
            ->setParameters(array('clubId' => $clubId, 'contactLang' => $contactLang));
        if ($menu !== null) {
            $q->setParameter('menu', $menu);
        }

        return $cacheDriver->getCachedResult($q, $cacheKey, $cacheLifeTime, $cachingEnabled);
    }

    /**
     * clear caching of navigation menus.
     * Remove apc cache entries while updating cms navigations.
     *
     * @param Object $clubObj Object of the current club
     *
     */
    private function navClearCache($clubObj, $container)
    {
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $domainCacheKey = $container->getParameter('database_name') . '_navigation';
        $prefixName = 'navigation';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
            $cacheDriver->deleteByPrefix($domainCacheKey);
        }
    }
    

    /**
     * 
     * @param string    $navigationUrl
     * @param int       $clubId
     * @param string    $contactLang
     */
    public function getPageTitleForNavigation($navigationUrl, $clubId, $contactLang){
        
        $pageObj = $this->createQueryBuilder('n')
            ->select("(CASE WHEN (pi18.titleLang != '' AND pi18.titleLang IS NOT NULL) THEN pi18.titleLang ELSE p.title END) AS pageTitle")
            ->innerJoin('CommonUtilityBundle:FgCmsPage', 'p', 'WITH', 'p.id = n.page')
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'pi18', 'WITH', 'pi18.id = p.id AND pi18.lang = :contactLang')
            ->where('n.navigationUrl =:navigationUrl AND n.club =:clubId')
            ->setParameters(array('navigationUrl' => $navigationUrl, 'clubId' => $clubId, 'contactLang' => $contactLang));
        $dataResult = $pageObj->getQuery()->getOneOrNullResult();

        return $dataResult['pageTitle'];
    }
}
