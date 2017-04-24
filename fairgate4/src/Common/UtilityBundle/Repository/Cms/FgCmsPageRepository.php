<?php

/**
 * FgCmsPageRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmsPage;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgCmsPageRepository
 *
 * This class is used for handling CMS page details insert, update, delete functionalities.
 */
class FgCmsPageRepository extends EntityRepository
{

    /**
     * @var int $clubId Club Id
     */
    private $clubId;

    /**
     * @var int $contactId Contact id
     */
    private $contactId;

    /**
     * get page list of a club
     * @param int $clubId   clubId
     * @param string $lang  logged contact default lang
     * @return array
     */
    public function getPagesList($clubId, $lang)
    {
        $pages = $this->createQueryBuilder('c')
            ->select("c.id as roleId, case when ci18.titleLang = ' ' or ci18.titleLang is null  then c.title else ci18.titleLang end as rTitle")
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'ci18', 'WITH', 'ci18.id = c.id and ci18.lang=:lang')
            ->where('c.club=:clubId')
            ->andWhere('c.type IN (:page)')
            ->orderBy('rTitle')
            ->setParameters(array('clubId' => $clubId, 'lang' => $lang, 'page' => array('page', 'article', 'gallery', 'calendar')));

        return $pages->getQuery()->getArrayResult();
    }
    
   
    /**
     * This function is used to save page details
     *
     * @param Object $container Container object
     * @param array  $data      Data to be saved
     * @return int
     */
    public function createPage($container, $data)
    {
        $club = $container->get('club');
        $this->clubId = $club->get('id');
        $this->contactId = $container->get('contact')->get('id');
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $this->clubId);
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);
        $lang = $club->get('club_default_lang');

        $pageObj = new FgCmsPage();
        if (is_array($data['title'])) {
            $pageObj->setTitle($data['title'][$lang][0]);
        } else {
            $pageObj->setTitle($data['title']);
        }
        $pageObj->setClub($clubObj);
        $pageObj->setType($data['type']);

        if ($data['type'] == 'page') {
            $pageObj->setSidebarType($data['sidebarType']);
            $pageObj->setSidebarArea($data['sidebarArea']);
        }
        if ($data['isAllGalleries']) {
            $pageObj->setIsAllGalleries(1);
        }
        if ($data['type'] == 'sidebar' && $data['sidebarType'] != '') {
            $pageObj->setSidebarType($data['sidebarType']);
        }

        if (isset($data['hideTitle'])) {
            $pageObj->setHideTitle($data['hideTitle']);
        }

        // For handling article and calendar special pages
        if ($data['type'] == 'article' || $data['type'] == 'calendar') {
            $catTable = ($data['type'] == 'article') ? 'CommonUtilityBundle:FgCmsPageArticleCategories' : 'CommonUtilityBundle:FgCmsPageCalendarCategories';
            $sharedClubs = array();
            if ((isset($data['areas'])) && (in_array($this->clubId, $data['areas']))) {
                $pageObj->setAreaClub($this->clubId);
                unset($data['areas'][0]);
            } else {
                $pageObj->setAreaClub(null);
            }

            if (isset($data['isAllCat'])) {
                $pageObj->setIsAllCategory($data['isAllCat']);
            }
            if ($data['fedIdVal']) {
                $sharedClubs[] = $data['fedIdVal'];
            }
            if ($data['subFedIdVal']) {
                $sharedClubs[] = $data['subFedIdVal'];
            }

            if (!empty($sharedClubs)) {
                $pageObj->setSharedClub(implode(',', $sharedClubs));
            }

            if (isset($data['isAllArea'])) {
                $pageObj->setIsAllArea($data['isAllArea']);
            }
        }
        $pageObj->setCreatedAt(new \DateTime("now"));
        $pageObj->setCreatedBy($contactObj);
        $pageObj->setEditedAt(new \DateTime("now"));
        $pageObj->setEditedBy($contactObj);
        $pageObj->setContentUpdateTime(new \DateTime("now"));

        $this->_em->persist($pageObj);
        $this->_em->flush();
        $pageId = $pageObj->getId();
        //Insert entry to i18n table
        if ($data['type'] == 'page') {
            foreach ($data['title'] as $lang => $lnVal) {
                $titleLang = $lnVal[0];
                $pagei18nObj = new \Common\UtilityBundle\Entity\FgCmsPageI18n();
                $pagei18nObj->setId($pageObj);
                $pagei18nObj->setTitleLang($titleLang);
                $pagei18nObj->setLang($lang);
                $this->_em->persist($pagei18nObj);
                $this->_em->flush();
            }
        }

        if ($data['type'] == 'article' || $data['type'] == 'calendar') {
            if ((!empty($data['areas'])) && ($data['isAllArea'] == '')) {
                $this->_em->getRepository('CommonUtilityBundle:FgCmsPageAreas')->savePageAreas($pageId, $data['areas']);
            }
            if ((!empty($data['categories'])) && ($data['isAllCat'] == '')) {
                $this->_em->getRepository($catTable)->savePageCategories($pageId, $data['categories']);
            }
        }

        return $pageId;
    }
    
     /**
     * Remove cache
     * 
     * @param object $container container
     * @param int    $pageId    page Id
     */
    private function removeCache($container, $pageId){        
        $clubCacheKey = $container->get('club')->get('clubCacheKey');
        $cachingEnabled = $container->getParameter('caching_enabled');
        if ($cachingEnabled) {
            $cacheArea = 'cms';
            $trailingPrefix = $pageId.'_pagetitle';
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $cacheArea, $trailingPrefix);
        }   
    }


    /**
     * Function to save page details
     *
     * @param type $pageId      Page id
     * @param type $container   Container object
     * @param type $data        Data to be saved
     */
    public function savePageDetails($pageId, $container, $data)
    {
        $club = $container->get('club');
        $this->clubId = $club->get('id');
        $defaultLang = $club->get('club_default_lang');
        $this->contactId = $container->get('contact')->get('id');
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $this->contactId);
        $pageObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPage', $pageId);
        
        $this->removeCache($container, $pageId);
        
        $pageObj->setTitle($data['title'][$defaultLang]);
        $pageObj->setEditedAt(new \DateTime("now"));
        $pageObj->setEditedBy($contactObj);
        $pageObj->setContentUpdateTime(new \DateTime("now"));
        if ($data['type'] == 'calendar' || $data['type'] == 'article') {
            $catTable = ($data['type'] == 'article') ? 'CommonUtilityBundle:FgCmsPageArticleCategories' : 'CommonUtilityBundle:FgCmsPageCalendarCategories';
            if ($data['isAllCat'] == '') {
                $pageObj->setIsAllCategory('');
            }
            if ($data['isAllArea'] == '') {
                $pageObj->setIsAllArea('');
            }
            if (isset($data['isAllCat']) || empty($data['categories'])) {
                $this->_em->getRepository($catTable)->deleteExistingSpecialPageCategory($pageId);
            }
            if (isset($data['isAllArea']) || empty($data['areas'])) {
                $this->_em->getRepository('CommonUtilityBundle:FgCmsPageAreas')->deleteExistingSpecialPageArea($pageId);
            }
            if ((isset($data['areas'])) && (in_array($this->clubId, $data['areas']))) {
                $pageObj->setAreaClub($this->clubId);
                unset($data['areas'][0]);
            } else {
                $pageObj->setAreaClub(null);
            }
            $sharedClubs = array();
            if ($data['fedIdVal']) {
                $sharedClubs[] = $data['fedIdVal'];
            }
            if ($data['subFedIdVal']) {
                $sharedClubs[] = $data['subFedIdVal'];
            }
            if (!empty($sharedClubs)) {
                $pageObj->setSharedClub(implode(',', $sharedClubs));
            } else {
                $pageObj->setSharedClub('');
            }
            if (isset($data['isAllArea'])) {
                $pageObj->setisAllArea($data['isAllArea']);
            }
            if (isset($data['isAllCat'])) {
                $pageObj->setisAllCategory($data['isAllCat']);
            }
        }
        if ($data['isAllGalleries']) {
            $pageObj->setIsAllGalleries(1);
        } else {
            $pageObj->setIsAllGalleries(0);
        }

        if (isset($data['hideTitle'])) {
            $pageObj->setHideTitle($data['hideTitle']);
        }

        $this->_em->persist($pageObj);
        $this->_em->flush();

        if ($data['type'] == 'article' || $data['type'] == 'calendar') {
            if ((!empty($data['areas'])) && ($data['isAllArea'] == '')) {
                $this->_em->getRepository('CommonUtilityBundle:FgCmsPageAreas')->savePageAreas($pageId, $data['areas']);
            }
            if ((!empty($data['categories'])) && ($data['isAllCat'] == '')) {
                $this->_em->getRepository($catTable)->savePageCategories($pageId, $data['categories']);
            }
        }

        foreach ($data['title'] as $lang => $titleLang) {
            $pagei18nObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageI18n')->findOneBy(array('id' => $pageId, 'lang' => $lang));
            if ($pagei18nObj) {
                $this->_em->getRepository('CommonUtilityBundle:FgCmsPageI18n')->updatePageI18n($pageId, $lang, $titleLang);
            } else {
                $pagei18nObj = new \Common\UtilityBundle\Entity\FgCmsPageI18n();
                $pagei18nObj->setId($pageObj);
                $pagei18nObj->setTitleLang($titleLang);
                $pagei18nObj->setLang($lang);
                $this->_em->persist($pagei18nObj);
                $this->_em->flush();
            }
        }
        return;
    }

    /**
     * This function is used to get page details
     *
     * @param Int       $clubId
     * @param String    $lang
     * @param String    $type
     * @return Array    $dataResult
     */
    public function listPageDetails($clubId, $lang, $type = '', $userId = '')
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $doctrineConfig->addCustomStringFunction('GROUP_CONCAT', 'Common\UtilityBundle\Extensions\GroupConcat');
        $getTitleSql = ", (CASE WHEN(ci18n.titleLang IS NULL OR ci18n.titleLang = '') THEN c.title ELSE ci18n.titleLang END) as title";
        /* fair 2405 System testing issues in CMS -I issue 1 */
        $pages = $this->createQueryBuilder('c')
            ->select(" c.id as pageId $getTitleSql, DATE_FORMAT(c.editedAt, '$datetimeFormat') as lastEdited, contactName(c.editedBy) as editedBy, GROUP_CONCAT(cni18n.titleLang SEPARATOR '|&&&|' )as navTitle, checkActiveContact(c.editedBy, :clubId) as activeContactId, count(DISTINCT(cpce.id)) as ElementCount, GROUP_CONCAT(cn.id SEPARATOR ',' )as navIds, fcc.isStealthMode as isStealth, c.type as pageType,c.sidebarType as sidebarType,c.sidebarArea as sidebarArea")
            ->addSelect("(SELECT GROUP_CONCAT(u.contact SEPARATOR '*##*') FROM  CommonUtilityBundle:SfGuardUserPage up INNER JOIN CommonUtilityBundle:SfGuardUser u WITH up.user = u.id WHERE up.page = c.id GROUP BY up.page ) as pageAdmin")
            ->addSelect("(SELECT sb.id FROM  CommonUtilityBundle:FgCmsPage sb  WHERE sb.club ={$clubId} AND sb.sidebarType=sidebarType AND sb.type='sidebar' ) as sidebarId")
            ->addSelect("GROUP_CONCAT(distinct cpce.pageContentType ORDER BY cpce.pageContentType SEPARATOR '|&&&|' ) as elementTypes")
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'ci18n', 'WITH', 'ci18n.id = c.id AND ci18n.lang =:lang')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'cpc', 'WITH', 'cpc.page = c.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cpcc', 'WITH', 'cpcc.container = cpc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsNavigation', 'cn', 'WITH', 'cn.page = c.id')
            ->leftJoin('CommonUtilityBundle:FgCmsNavigationI18n', 'cni18n', 'WITH', 'cni18n.id = cn.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cpcb', 'WITH', 'cpcb.column = cpcc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'cpce', 'WITH', 'cpce.box = cpcb.id')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'fcc', 'WITH', 'fcc.id = c.editedBy')
            ->leftJoin('CommonUtilityBundle:SfGuardUserPage', 'sup', 'WITH', 'sup.page = c.id')
            ->where('c.club=:clubId')
            ->andWhere('c.type <>:footer')
            ->andWhere('c.type <>:sidebar');
        if ($type == 'pages_without_navigation') {
            $pages->andWhere('cn.page IS NULL');
        }
        if ($userId !== '') {
            $pages->andWhere('sup.user=:userId');
        }
        $pages->groupBy('c.id')
            ->orderBy('c.id', 'desc')
            ->setParameter('clubId', $clubId)
            ->setParameter('lang', $lang)
            ->setParameter('footer', 'footer')
            ->setParameter('sidebar', 'sidebar');
        if ($userId !== '') {
            $pages->setParameter('userId', $userId);
        }

        return $pages->getQuery()->getArrayResult();
    }

    /**
     * Function to delete CMS pages
     *
     * @param Array $deleteData
     * @param Object $container Container object
     *
     * @return boolean
     */
    public function deletePages($deleteData, $container)
    {
        $contactId = $container->get('contact')->get('id');
        $clubObj = $container->get('club');
        $lang = $container->get('club')->get('club_default_lang');
        $logArray = array();
        foreach ($deleteData as $pageId) {
            //Un-assign page from navigation
            $navDet = $this->_em->getRepository('CommonUtilityBundle:FgCmsNavigation')->checkPageAssignedToNavigation($pageId);
            $navIds = array_map(function ($a) {
                return $a['id'];
            }, $navDet);
            $this->_em->getRepository('CommonUtilityBundle:FgCmsNavigation')->unAssignPageFromNav($navIds, $container);
            //Remove all elements from the page
            $elementArray = $this->getPageDetails($pageId, $lang, $container->get('club')->get('id'));
            foreach ($elementArray as $element) {
                $elementId = $element['elementId'];
                if (isset($elementId)) {
                    $this->deleteElement($elementId);
                    $logArray[] = "('$elementId', '$pageId', 'element', 'deleted', '', '', now(), $contactId)";
                }
            }
            $cmsPdo = new CmsPdo($container);
            $cmsPdo->saveLog($logArray);
            //remove page
            $cmsPageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
            if ($cmsPageObj) {
                $this->_em->remove($cmsPageObj);
            }
        }
        $this->_em->flush();

        //Remove apc cache of navigation while deleting page and the corresponding navigation relation
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'club_navigation';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache of navigation while deleting page and the corresponding navigation relation
        return true;
    }

    /**
     * Function to soft delete content element
     *
     * @param Int $elementId container element id
     */
    public function deleteElement($elementId)
    {
        $qb = $this->createQueryBuilder();
        $query = $qb->update('CommonUtilityBundle:FgCmsPageContentElement', 'cpce')
            ->set('cpce.deletedAt', ":now")
            ->set('cpce.isDeleted', "1")
            ->set('cpce.box', 'NULL')
            ->where('cpce.id=:id')
            ->setParameter('id', $elementId)
            ->setParameter('now', new \DateTime("now"))
            ->getQuery();
        $query->execute();
    }

    /**
     * To get the all content detail of a page
     * @param integer $pageId id of page
     * @param string  $lang   language of club
     * @return type
     */
    public function getPageDetails($pageId, $lang, $clubId)
    {
        $pages = $this->createQueryBuilder('c')
            ->select("pc.id as containerId,c.type as pageType, case when ci18.titleLang = '' or ci18.titleLang is null  then c.title else ci18.titleLang end as pageTitle,case when cei18.twitterAccountnameLang = '' or cei18.twitterAccountnameLang is null  then ce.twitterDefaultAccount else cei18.twitterAccountnameLang end as accountName, ce.twitterContentHeight, cc.id as columnId,cb.id as boxId,ce.id as elementId,pc.sortOrder as containerOrder,cc.widthValue as widthValue,cc.sortOrder as columnOrder,cb.sortOrder as boxOrder,ce.sortOrder as elementOrder,ct.type as elementType,case when cei18.titleLang = '' or cei18.titleLang is null  then ce.title else cei18.titleLang end as elementTitle,ce.headerElementSize,c.sidebarType,c.sidebarArea,ce.isAllCategory,ce.isAllArea,ct.logoName as logo,ct.label")
            ->addSelect('ce.imageElementDisplayType,ce.imageElementSliderTime, IDENTITY(ce.form) as formId')
            ->addSelect('ce.mapElementLatitude,ce.mapElementLongitude,ce.mapElementShowMarker,ce.mapElementHeight,ce.mapElementDisplayStyle,ce.mapElementZoomValue')
            ->addSelect('ce.iframeElementCode,ce.iframeElementUrl,ce.iframeElementHeight')
            ->addSelect('IDENTITY(eac.category) as categoryId')
            ->addSelect('IDENTITY(cea.role) as roleId')
            ->addSelect('IDENTITY(ecc.category) as calendarCategory')
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'ci18', 'WITH', 'ci18.id = c.id AND ci18.lang=:lang')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = c.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementI18n', 'cei18', 'WITH', 'cei18.id = ce.id AND cei18.lang=:lang')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementCalendarCategory', 'ecc', 'WITH', 'ce.id = ecc.element')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementArticleCategory', 'eac', 'WITH', 'ce.id = eac.element')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementArea', 'cea', 'WITH', 'ce.id = cea.element')
            ->where('c.id=:pageId AND ce.deletedAt IS NULL')
            ->andWhere('c.club=:clubId')
            ->orderBy('pc.sortOrder')
            ->addOrderBy('cc.sortOrder', 'ASC')
            ->addOrderBy('cb.sortOrder', 'ASC')
            ->addOrderBy('ce.sortOrder', 'ASC')
            ->setParameters(array('lang' => $lang, 'pageId' => $pageId, 'clubId' => $clubId));

//        echo "<pre>";
//                print_r($pages->getQuery()->getArrayResult());die;
        return $pages->getQuery()->getArrayResult();
    }

    /**
     * This function is used to get the count of pages to be displayed in sidebar
     *
     * @param int $clubId ClubId
     *
     * @return array Array of count
     */
    public function getCountOfPagesForSidebar($clubId)
    {
        $pagesCount = $this->createQueryBuilder('p')
            ->select('COUNT(p.id) as unassignedPagesCount')
            ->addSelect('(SELECT COUNT(p1.id) FROM CommonUtilityBundle:FgCmsPage p1 WHERE p1.club = :clubId AND p1.type IN (:types)) AS allPagesCount')
            ->leftJoin('CommonUtilityBundle:FgCmsNavigation', 'n', 'WITH', 'n.page = p.id')
            ->where('p.club=:clubId')
            ->andWhere('n.page IS NULL')
            ->andWhere('p.type IN (:types)')
            ->setParameters(array('clubId' => $clubId, 'types' => array('page', 'gallery', 'article', 'calendar')))
            ->getQuery()
            ->getSingleResult();

        return (array('allPages' => $pagesCount['allPagesCount'], 'unassignedPages' => $pagesCount['unassignedPagesCount']));
    }

    /**
     * To get the all content detail of a page
     * @param integer $pageId id of page
     * @param string  $lang   language of club
     * @return type
     */
    public function getAllElementsOfaPage($pageId, $clubId, $containerId = '', $columnId = '', $boxId = '')
    {
        $qb = $this->createQueryBuilder('c');
        $qb->select("pc.id as containerId,cc.id as columnId,cb.id as boxId,ce.id as elementId,pc.sortOrder as containerOrder,cc.sortOrder as columnOrder,cb.sortOrder as boxOrder,ce.sortOrder as elementOrder")
            ->innerJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = c.id')
            ->innerJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->innerJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('c.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('c.club=:clubId');

        $setArray = array('pageId' => $pageId, 'clubId' => $clubId);
        if ($containerId != '') {
            $qb->andWhere('pc.id=:containerId');
            $setArray = array('pageId' => $pageId, 'clubId' => $clubId, 'containerId' => $containerId);
        }
        if ($columnId != '') {
            $qb->andWhere('cc.id=:columnId');
            $setArray = array('pageId' => $pageId, 'clubId' => $clubId, 'containerId' => $containerId, 'columnId' => $columnId);
        }
        if ($boxId != '') {
            $qb->andWhere('cb.id=:boxId');
            $setArray = array('pageId' => $pageId, 'clubId' => $clubId, 'containerId' => $containerId, 'columnId' => $columnId, 'boxId' => $boxId);
        }
        $qb->orderBy('pc.sortOrder')
            ->addOrderBy('cc.sortOrder', 'ASC')
            ->addOrderBy('cb.sortOrder', 'ASC')
            ->addOrderBy('ce.sortOrder', 'ASC');

        $qb->setParameters($setArray);

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Fuction is used to get page title
     *
     * @param type $pageId
     * @return type
     */
    public function getPageTite($pageId)
    {
        $pageObj = $this->createQueryBuilder('c')
            ->select("ci18.lang as lang, ci18.titleLang as titleLang, c.title as defaultTitle")
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'ci18', 'WITH', 'ci18.id = c.id')
            ->where('c.id=:pageId')
            ->setParameters(array('pageId' => $pageId));
        $dataResult = $pageObj->getQuery()->getArrayResult();
        $finalResult = array();
        $finalResult['default'] = $dataResult[0]['defaultTitle'];
        foreach ($dataResult as $data) {
            $finalResult[$data['lang']] = $data['titleLang'];
        }

        return $finalResult;
    }

    /**
     * get page title - display frontend
     * 
     * @param int       $pageId
     * @param string    $contactLang
     * @param int       $clubCacheKey
     * @param int       $cacheLifeTime
     * @param boolean   $cachingEnabled
     * 
     * @return string
     */
    public function getPageTitleFrontend($pageId, $contactId, $contactLang, $clubCacheKey, $cacheLifeTime, $cachingEnabled)
    {
        $prefixKey = str_replace('{{cache_area}}', 'cms', $clubCacheKey);
        if ($contactId){ 
            $cacheKey = $prefixKey.$pageId.'_pagetitle'.$contactId;
        }else{
            $cacheKey = $prefixKey.$pageId.'_pagetitle';
        }
        
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $pageObj = $this->createQueryBuilder('c')
            ->select("COALESCE(NULLIF(ci18.titleLang,'') , c.title) AS pageTitle")
            ->leftJoin('CommonUtilityBundle:FgCmsPageI18n', 'ci18', 'WITH', 'ci18.id = c.id AND ci18.lang = :contactLang')
            ->where('c.id=:pageId')
            ->setParameters(array('pageId' => $pageId, 'contactLang' => $contactLang));
        
        $dataResult = $cacheDriver->getCachedResult($pageObj, $cacheKey, $cacheLifeTime, $cachingEnabled);
        
        return $dataResult[0]['pageTitle'];
    }

    /**
     * This function is used to get global settings sidebar
     *
     * @param int $clubId ClubId
     * @param string $type Type
     *
     * @return array $sidebarResult Array
     */
    public function getGlobalSidebarSetting($clubId, $type = 'sidebar', $sidebarType = '')
    {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('CAST', 'Common\UtilityBundle\Extensions\CastFunction');
        $sidebarDet = $this->createQueryBuilder('p')
            ->select('p.id,p.title,p.type,p.pageContentJson,p.sidebarType ,p.sidebarArea, CAST(p.sidebarType AS CHAR)  AS sidebar')
            ->where('p.club=:clubId')
            ->andWhere('p.type=:type');
        if ($sidebarType != '') {
            $sidebarDet->andWhere('p.sidebarType=:sidebarType');
        }

        $sidebarDet->setParameters(array('clubId' => $clubId, 'type' => $type));
        if ($sidebarType != '') {
            $sidebarDet->setParameter('sidebarType', $sidebarType);
        }
        $sidebarDet->orderBy("sidebar", "DESC");

        return $sidebarDet->getQuery()->getArrayResult();
    }

    /**
     * This function is used to exclude page sidebar
     *
     * @param int $pageId PageId
     *
     * @return array $result Array of count
     */
    public function excludePageSidebar($pageId, $type, $area)
    {
        $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageObj->setSidebarType($type);
        $pageObj->setSidebarArea($area);
        $pageObj->setContentUpdateTime(new \DateTime("now"));
        $this->_em->persist($pageObj);
        $this->_em->flush();
    }

    /**
     * This function is used to get global fotter id
     * @param int $clubId ClubId
     * @return Int $footerId
     */
    public function getGlobalFooterId($clubId)
    {
        $footerDet = $this->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.club=:clubId')
            ->andWhere('p.type=:type')
            ->setParameters(array('clubId' => $clubId, 'type' => 'footer'));
        $footerResult = $footerDet->getQuery()->getArrayResult();

        return $footerResult[0]['id'];
    }

    /**
     * Function to update content_update_time
     *
     * @param int $pageId Page id
     *
     * @return boolean
     */
    public function saveContentUpdateTime($pageId)
    {
        $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageObj->setContentUpdateTime(new \DateTime("now"));
        $this->_em->persist($pageObj);
        $this->_em->flush();

        return true;
    }

    /**
     * Function to get the count of map element in a page
     *
     * @param int $pageId Page id
     *
     * @return int
     */
    public function getMapElementCountInPage($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as mapElementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ct.type=:contentType')
            ->setParameters(array('pageId' => $pageId, 'contentType' => 'map'));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['mapElementCount'];
    }

    /**
     * Function to get the count of twitter element in a page
     *
     * @param  int $pageId Page id
     *
     * @return int twitter Element Count
     */
    public function getTwitterElementCountInPage($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as twitterElementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ct.type=:contentType')
            ->setParameters(array('pageId' => $pageId, 'contentType' => 'twitter'));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['twitterElementCount'];
    }

    /**
     * Function to get the count of contact table element in a page
     *
     * @param int $pageId Page id
     *
     * @return int
     */
    public function getContactTableElementCountInPage($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as contactTableElementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ct.type=:contentType')
            ->setParameters(array('pageId' => $pageId, 'contentType' => 'contacts-table'));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['contactTableElementCount'];
    }

    /**
     * Function to get the count of map, login and form which is used to decide 
     * wheather to load the corresponding scripts and underscore templates
     *
     * @param int $pageId    Page id
     * @param int $sidebarId Current club sidebar Id
     * @param int $footerId  Current club footerId Id
     *
     * @return array count of map, login and form elements in this page
     */
    public function getPageElementCount($pageId, $sidebarId, $footerId)
    {
        $elementTypes = array(6, 7, 10, 11, 13, 14, 15, 16);
        if ($pageId) {
            $pageIds[] = $pageId;
        }
        if ($sidebarId) {
            $pageIds[] = $sidebarId;
        }
        if ($footerId) {
            $pageIds[] = $footerId;
        }
        $elementCountQuery = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as elementCount, ct.type")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id IN (:pageIds)')->andWhere('ce.deletedAt IS NULL')
            ->andWhere('cb.id IS NOT NULL')->andWhere('ce.pageContentType IN(:contentType)')->groupBy('ct.type')
            ->setParameters(array('pageIds' => $pageIds, 'contentType' => $elementTypes));
        $dataResult = $elementCountQuery->getQuery()->getArrayResult();
        $elementCountArr = array();
        if (count($dataResult)) {
            foreach ($dataResult as $key => $valArray) {
                $elementCountArr[$valArray['type']] = $valArray['elementCount'];
            }
        }

        return $elementCountArr;
    }

    /**
     * Function to get the count of login element in a page
     *
     * @param int $pageId Page id
     *
     * @return int
     */
    public function getLoginElementCountInPage($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as mapElementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ct.type=:contentType')
            ->setParameters(array('pageId' => $pageId, 'contentType' => 'login'));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['mapElementCount'];
    }

    /**
     * Function to get container column width
     *
     * @param int $pageId Page id
     * @param string $elementId element id string
     * @return int $dataResult container width
     */
    public function getPageContainerColumnWidth($pageId, $elementId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("cc.widthValue as widthValue")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ce.id=:elementId')
            ->setParameters(array('pageId' => $pageId, 'elementId' => $elementId));

        return $pages->getQuery()->getSingleScalarResult();
    }

    /**
     * Function to get page data
     * @param type $pageId
     * @return type
     */
    public function getPageData($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("p.id, p.title, p.type, p.isAllGalleries, IDENTITY(p.club) AS clubId")
            ->where('p.id=:pageId')
            ->setParameters(array('pageId' => $pageId));
        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0];
    }

    /**
     * Function to get calendar/Article Special page data
     *
     * @param int    $pageId      pageId
     * @param string $elementType element type-either article or calendar
     *
     * @return array
     */
    public function getCalendarAndArticlePageData($pageId, $elementType)
    {
        $catTable = ($elementType == 'article') ? 'CommonUtilityBundle:FgCmsPageArticleCategories' : 'CommonUtilityBundle:FgCmsPageCalendarCategories';
        $pages = $this->createQueryBuilder('p')
            ->select("GROUP_CONCAT( DISTINCT ar.role) AS areas, p.id as pageId, p.title, p.type, p.isAllCategory, p.isAllArea, p.sharedClub, p.areaClub, GROUP_CONCAT(c.category) as categories")
            ->leftJoin('CommonUtilityBundle:FgCmsPageAreas', 'ar', 'WITH', 'ar.page = p.id')
            ->leftJoin($catTable, 'c', 'WITH', 'c.page = p.id')
            ->where('p.id=:pageId')
            ->setParameters(array('pageId' => $pageId));
        $dataResult = $pages->getQuery()->getArrayResult();

        $finalResult = $dataResult[0];
        $categoryArray = array();
        $areaArray = array();
        if ($finalResult['areas'] || ($finalResult['areaClub']) || ($finalResult['isAllArea'] == '')) {
            $areaArray['areaIds'] = explode(',', $finalResult['areas']);
        } else {
            $areaArray['areaIds'] = array("ALL_AREAS");
        }
        if ($finalResult['categoryId'] || ($finalResult['isAllCategory'] == '')) {
            $categoryArray['catIds'] = explode(',', $finalResult['categories']);
        } else {
            $categoryArray['catIds'] = array("ALL_CATS");
        }
        if ($finalResult['sharedClub']) {
            $finalResult['sharedClubs'] = explode(',', $finalResult['sharedClub']);
        }

        return array_merge($categoryArray, $areaArray, $finalResult);
    }

    /**
     * Function to update json data
     *
     * @param int       $pageId          Page id
     * @param array     $pageContentJson Page content Json
     * @param string    $pageType        Page type
     *
     * @return boolean
     */
    public function saveContentJson($container, $pageId, $pageContentJson, $pageType)
    {
        $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageObj->setPageContentJson($pageContentJson);
        $this->_em->persist($pageObj);
        $this->_em->flush();
        
        $this->clearCmsCache($container, $pageType, $pageId);
        
        return true;
    }
    
    /**
     * clear cms cache 
     * 
     * @param object $container container
     * @param string $pageType  page Type
     * @param string $pageId    page Id
     */ 
    private function clearCmsCache($container, $pageType, $pageId){
        $cachingEnabled = $container->getParameter('caching_enabled');
        $clubCacheKey = $container->get('club')->get('clubCacheKey');
       //Remove apc cache while updating page-content-json 
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $prefixName = 'cms';
            if($pageType == 'sidebar' || $pageType == 'footer'){
                //If it is sidebar and footer need to delete the cache by prefix.                
                $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
            }else{
                //If it is cms page, delete the cache by pageId                               
                $cacheDeleteId = $pageId; 
                $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName,$cacheDeleteId);
            }
        }
    }

    /**
     * Function to update page ids
     *
     * @param int $pageId Page id
     * @param array $elementIdsJson Element id Json
     *
     * @return boolean
     */
    public function saveMetaDetails($pageId, $elementIdsJson, $metaJson)
    {
        $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageObj->setPageElement($elementIdsJson);
        if($metaJson!=''){
            $pageObj->setOpengraphDetails($metaJson);
        }
        $this->_em->persist($pageObj);
        $this->_em->flush();

        return true;
    }

    /**
     * hide or display page title
     *
     * @param int $pageId page id
     * @param int $status status- hide/show page
     * @return boolean
     */
    public function hideShowPageTitle($pageId, $status)
    {
        $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageObj->setHideTitle($status);
        $this->_em->persist($pageObj);
        $this->_em->flush();

        return true;
    }

    /**
     * Function to get the count of element in a page
     *
     * @param int $pageId Page id
     *
     * @return int
     */
    public function getElementCountInPage($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as pageElementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->setParameters(array('pageId' => $pageId));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['pageElementCount'];
    }

    /**
     * Function to get aricle or calendar element details for edit
     *
     * @param int    $pageId   page id
     *
     * @return array $returnArray return result
     */
    public function getArticlePageDetails($pageId)
    {
        $element = $this->createQueryBuilder('p')
            ->select("GROUP_CONCAT( DISTINCT ar.role) AS areas, GROUP_CONCAT(DISTINCT cat.category) as categoryIds, p.isAllArea, p.isAllCategory, p.sharedClub AS clubShared, p.areaClub AS areaClub")
            ->leftJoin('CommonUtilityBundle:FgCmsPageAreas', 'ar', 'WITH', 'ar.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageArticleCategories', 'cat', 'WITH', 'cat.page = p.id')
            ->where('p.id=:pageId')
            ->setParameters(array('pageId' => $pageId));

        $dataResult = $element->getQuery()->getArrayResult();
        $finalResult = $dataResult[0];

        $categoryArray = array();
        $areaArray = array();
        if ($finalResult['areas'] || ($finalResult['areaClub']) || ($finalResult['isAllArea'] == '')) {
            $areaArray['areaIds'] = explode(',', $finalResult['areas']);
        } else {
            $areaArray['areaIds'] = array("ALL_AREAS");
        }
        if ($finalResult['categoryIds'] || ($finalResult['isAllCategory'] == '')) {
            $categoryArray['catIds'] = explode(',', $finalResult['categoryIds']);
        } else {
            $categoryArray['catIds'] = array("ALL_CATS");
        }
        if ($finalResult['clubShared']) {
            $finalResult['sharedClub'] = explode(',', $finalResult['clubShared']);
        }
        return array_merge($categoryArray, $areaArray, $finalResult);
    }

    /**
     * Function to get the count of perticuler element in a page
     *
     * @param int $pageId Page id
     *
     * @return int
     */
    public function getElementTypeCountInPage($pageId, $elementType)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as elementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ct.type=:contentType')
            ->setParameters(array('pageId' => $pageId, 'contentType' => $elementType));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['elementCount'];
    }

    /**
     * To get the all content detail of a page
     *
     * @param integer $elementId id of element
     * @return type
     */
    public function getElementDetails($elementId)
    {
        $elementDetails = $this->createQueryBuilder('c')
            ->select("c.id AS pageId, pc.id AS containerId, cc.id AS columnId,cb.id AS boxId, ce.id AS elementId")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = c.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->where('ce.id=:elementId')
            ->setParameters(array('elementId' => $elementId));
        return $elementDetails->getQuery()->getArrayResult();
    }

    /**
     * Function to get special page calendar element details
     *
     * @param int    $pageId      page id
     *
     * @return array $returnArray return result
     */
    public function getSpecialPageCalendarDetails($pageId)
    {
        $element = $this->createQueryBuilder('p')
            ->select("GROUP_CONCAT( DISTINCT ar.role) AS areaIds, GROUP_CONCAT(DISTINCT cat.category) as categoryIds, p.isAllArea, p.isAllCategory, p.sharedClub AS sharedClub, p.areaClub AS areaClub")
            ->leftJoin('CommonUtilityBundle:FgCmsPageAreas', 'ar', 'WITH', 'ar.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageCalendarCategories', 'cat', 'WITH', 'cat.page = p.id')
            ->where('p.id=:pageId')
            ->setParameters(array('pageId' => $pageId));

        $dataResult = $element->getQuery()->getArrayResult();

        $dataResult[0]['areaFlag'] = 0;
        $dataResult[0]['categoryFlag'] = 0;
        $sharedAreas = $dataResult[0]['sharedClub'];
        if ($sharedAreas == '') {
            $areas = explode(',', $dataResult[0]['areaIds']);
            $club = explode(',', $dataResult[0]['areaClub']);
            if (count(array_filter(array_merge($club, $areas))) > 1 || $dataResult[0]['isAllArea'] == 1) {
                $dataResult[0]['areaFlag'] = 1;
            }
            if (count(array_filter(array_merge($club, $areas))) == 0 && $dataResult[0]['isAllArea'] == "") {
                // when no areas selected
                $dataResult[0]['areaFlag'] = 1;
            }
            $categories = explode(',', $dataResult[0]['categoryIds']);
            $catCount = ($dataResult[0]['categoryIds'] == '') ? 0 : count($categories);
            if ($catCount > 1 || $dataResult[0]['isAllCategory'] == 1) {
                $dataResult[0]['categoryFlag'] = 1;
            }
            if ($dataResult[0]['categoryIds'] == '' && $dataResult[0]['isAllCategory'] == "") {
                // when no category selected
                $dataResult[0]['categoryFlag'] = 1;
            }
        } else {
            $dataResult[0]['areaFlag'] = 1;
            $dataResult[0]['categoryFlag'] = 1;
        }

        return $dataResult[0];
    }

    /**
     * Function to Select page jsondata
     * @param type $pageId
     * 
     * @return type
     */
    public function getAllJsonPageData()
    {
        $pages = $this->createQueryBuilder('p')->select("p.id,IDENTITY(p.club) AS clubId,p.pageContentJson,p.type");
        
        return $pages->getQuery()->getArrayResult();
    }

    /**
     * Function to get the count of newsletter archive element in a page
     *
     * @param int $pageId Page id
     *
     * @return int
     */
    public function getNewsletterArchiveElementCountInPage($pageId)
    {
        $pages = $this->createQueryBuilder('p')
            ->select("COUNT(ce.id) as newsletterArchiveElementCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainer', 'pc', 'WITH', 'pc.page = p.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerColumn', 'cc', 'WITH', 'cc.container = pc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContainerBox', 'cb', 'WITH', 'cb.column = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'ce', 'WITH', 'ce.box = cb.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentType', 'ct', 'WITH', 'ct.id = ce.pageContentType')
            ->where('p.id=:pageId AND ce.deletedAt IS NULL AND cb.id IS NOT NULL  ')
            ->andWhere('ct.type=:contentType')
            ->setParameters(array('pageId' => $pageId, 'contentType' => 'newsletter-archive'));

        $dataResult = $pages->getQuery()->getArrayResult();

        return $dataResult[0]['newsletterArchiveElementCount'];
    }

    /**
     * Function to save the open graph details of a page
     *
     * @param int   $pageId             Page id
     * @param array $openGraphDetails   Open graph details of a page
     *
     * @return boolean
     */
    public function saveOpenGraphDetailsForPage($pageId, $openGraphDetails)
    {
        $pageObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageObj->setOpengraphDetails($openGraphDetails);
        $this->_em->persist($pageObj);
        $this->_em->flush();
        return true;
    }
    
    

    
    
}
