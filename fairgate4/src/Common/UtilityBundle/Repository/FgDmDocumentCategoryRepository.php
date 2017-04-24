<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\DocumentsBundle\Util\DocumentCategory;

/**
 * This repository is used for document categories
 *
 * @author pitsolutions.ch
 */
class FgDmDocumentCategoryRepository extends EntityRepository {

    /**
     * Function to get document category List
     *
     * @param int    $clubId Club Id of document category
     * @param string $type   Document type CLUB/CONTACT/TEAM/WORKGROUP
     *
     * @return array
     */
    public function getCategoryList($clubId, $type)
    {
        $exec = true;
        $category = $this->createQueryBuilder('c')
                ->select("c.id,c.title,c.sortOrder, c.documentType,ci18.titleLang, ci18.lang")
                ->addSelect("(SELECT count(d.id) FROM CommonUtilityBundle:FgDmDocuments d WHERE d.club= $clubId AND d.category = c.id AND d.documentType=c.documentType) documentCount")
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategoryI18n', 'ci18', 'WITH', 'ci18.id = c.id')
                ->where('c.club=:clubId')
                ->andWhere('c.documentType=:type')
                ->setParameters(array('clubId' => $clubId, 'type' => $type));
        $dataResult = $category->getQuery()->getArrayResult();

        if ($exec) {
            $result = array();
            $id = '';
            foreach ($dataResult as $key => $arr) {
                if (count($arr) > 0) {
                    if ($arr['id'] == $id) {
                        $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
                    } else {
                        $id = $arr['id'];
                        $result[$id] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'],
                            'documentType' => $arr['documentType'], 'documentCount' => $arr['documentCount']);
                        $result[$id]['titleLang'][$arr['lang']] = $arr['titleLang'];
                    }
                }
            }

            return $result;
        } else {

            return $dataResult;
        }
    }

    /**
     * Function to save document category
     *
     * @param int    $clubId        Club Id of document category
     * @param string $defaultlang   Default languages of club
     * @param array  $catArr        Result array to save
     * @param array  $clubLanguages Club languages list
     * @param array  $getId         Whether to return id
     *
     * @return boolean
     */
    public function categorySave($clubId, $defaultlang, $catArr, $clubLanguages, $getId = false)
    {
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        foreach ($catArr as $id => $data) {

            $categoryobj = $this->find($id);
            if ($data['is_deleted'] == 1) {
                $this->deleteCategory($categoryobj);
                continue;
            }
            if (empty($categoryobj)) {
                $categoryobj = new \Common\UtilityBundle\Entity\FgDmDocumentCategory();
            }
            if (isset($data['title'][$defaultlang])) {
                $categoryobj->setTitle($data['title'][$defaultlang]);
            }
            if (isset($data['catType'])) {
                $categoryobj->setDocumentType($data['catType']);
            }

            if (isset($data['sortOrder'])) {
                $categoryobj->setSortOrder($data['sortOrder']);
            }
            $categoryobj->setClub($clubobj);
            $this->_em->persist($categoryobj);
            $this->_em->flush();
            $catId = $categoryobj->getId();

            foreach ($clubLanguages as $lang) {
                $langobj = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentCategoryI18n')->findOneBy(array('id' => $catId, 'lang' => $lang));
                if ($langobj) {
                    $title = (array_key_exists($lang, ($data['title']))) ? $data['title'][$lang] : $langobj->getTitleLang();
                } else {
                    $title = $data['title'][$lang];
                    $langobj = new \Common\UtilityBundle\Entity\FgDmDocumentCategoryI18n();
                }
                $langobj->setId($catId);
                $langobj->setLang($lang);
                $langobj->setTitleLang($title);
                $this->_em->persist($langobj);
            }
            $this->_em->flush();
        }
        $this->updateMainTable($defaultlang, $clubId);
        
        
        return ($getId) ? $catId : true;
    }

    /**
     * Function to delete a category
     *
     * @param object $catobj Category table object
     *
     */
    public function deleteCategory($catobj)
    {
        $this->_em->remove($catobj);
        $this->_em->flush();
    }

    /**
     * Function to get a category title
     *
     * @param int $clubId Club id of document
     * @param int $catId  Category id of document
     *
     * @return string
     */
    public function getCategoryTitle($clubId, $catId)
    {
        $categoryName = $this->createQueryBuilder('c')
                ->select("c.title")
                ->where('c.club=:clubId')
                ->andWhere('c.id=:catId')
                ->setParameters(array('clubId' => $clubId, 'catId' => $catId));
        $dataResult = $categoryName->getQuery()->getArrayResult();
        
        return $dataResult[0]['title'];
    }

    /**
     * Function to get all document categories and sub-categories including heirarchical level.
     * 
     * @param object $club      Club object
     * @param int    $clubId    Club id of document
     * @param string $type      Document type CLUB/CONTACT/TEAM/WORKGROUP
     * @param object $container Container object
     *
     * @return array $categoryData Array of document category details.
     */
    public function getAllCategoryDetails($club, $clubId, $type, $container)
    {
        $clubType = $club->get('type');
        $defaultLang = $club->get('default_lang');
        $clubHeirarchyArray = $club->get('clubHeirarchy');
        rsort($clubHeirarchyArray); // For listing in the order club docs, sub-federation docs, federation docs.
        $fedGroupSql = $fedSortSql = "";
        $sort = 1;
        foreach ($clubHeirarchyArray as $key => $clubIdVal) {
            $sort = $sort + 1;
            $fedSortSql .= "WHEN (c.club = '$clubIdVal') THEN $sort ";
            $fedGroupSql .= "WHEN (c.club = '$clubIdVal') THEN 'FDOCS-$clubIdVal' ";
        }
        $sortSql = "(CASE WHEN (c.club = '$clubId') THEN 1 $fedSortSql ELSE 0 END) AS sort";
        $groupSql = "(CASE WHEN (c.club = '$clubId') THEN 'DOCS-$clubId' $fedGroupSql ELSE '' END) AS groupTitle";
        $catTitle = "(CASE WHEN (ci18n.titleLang IS NULL OR ci18n.titleLang='') THEN c.title ELSE ci18n.titleLang END) AS catTitle";
        $subCatTitle = "(CASE WHEN (sci18n.titleLang IS NULL OR sci18n.titleLang='') THEN sc.title ELSE sci18n.titleLang END) AS subCatTitle";
        $bookMarkId = "bm.id AS bookMarkId";
        $addSelect = "(SELECT COUNT(d.id) FROM CommonUtilityBundle:FgDmDocuments d WHERE d.subcategory=sc.id AND d.club=:clubId) AS docCount";
        $depositedCount = "(SELECT COUNT(d2.id) FROM CommonUtilityBundle:FgDmDocuments d2 LEFT JOIN CommonUtilityBundle:FgDmAssigment asgmnt WITH (asgmnt.document=d2.id) "
                    . "WHERE d2.subcategory=sc.id AND ((d2.depositedWith='ALL' AND (d2.id NOT IN (SELECT IDENTITY(e.document) FROM CommonUtilityBundle:FgDmAssigmentExclude e WHERE e.document=d2.id AND e.club = $clubId))) OR (asgmnt.club=:clubId))) AS depositedCount";

        $categoryDetails = $this->createQueryBuilder('c')
                ->select("c.id AS catId, $catTitle, IDENTITY(c.club) AS clubId, sc.id AS subCatId, $subCatTitle, $sortSql, $groupSql, $bookMarkId")
                ->addSelect($addSelect)
                ->addSelect($depositedCount)
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategoryI18n', 'ci18n', 'WITH', '((ci18n.id = c.id) AND (ci18n.lang = :defaultLang))')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', '(sc.category = c.id)')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategoryI18n', 'sci18n', 'WITH', '((sci18n.id = sc.id) AND (sci18n.lang = :defaultLang))')
                ->leftJoin('CommonUtilityBundle:FgDmBookmarks', 'bm', 'WITH', '((bm.subcategory = sc.id) AND (bm.club = :clubId))')
                ->where('c.club=:clubId');
        if (!in_array($clubType, array('federation', 'standard_club')) && ($type == 'CLUB')) {
            $categoryDetails = $categoryDetails->orWhere('(c.club IN (:clubHeirarchies) AND c.club!=:clubId)');
        }
        $categoryDetails = $categoryDetails->andWhere('c.documentType=:documentType');
        if (!in_array($clubType, array('federation', 'standard_club')) && ($type == 'CLUB')) {
            $categoryDetails = $categoryDetails->setParameters(array('clubId' => $clubId, 'documentType' => $type, 'clubHeirarchies' => $clubHeirarchyArray, 'defaultLang' => $defaultLang));
        } else {
            $categoryDetails = $categoryDetails->setParameters(array('clubId' => $clubId, 'documentType' => $type, 'defaultLang' => $defaultLang));
        }
        $categoryDetails = $categoryDetails->orderBy('sort', 'ASC')
                            ->addOrderBy('c.sortOrder', 'ASC')
                            ->addOrderBy('sc.sortOrder', 'ASC')
                            ->getQuery()
                            ->getArrayResult();
        
        $docObj = new DocumentCategory($container, $type);
        $categoryData = $docObj->iterateDocumentCategories($categoryDetails);
        
        return $categoryData;
    }

    
    /**
     * Function to get document subcategories
     *
     * @param int    $clubId          ClubId
     * @param string $documentType    TEAM/WORKGROUP/CONTACT/CLUB
     * @param string $clubDefaultLang ClubDefaultLanguage
     *
     * @return array $catArr CategoryDetails
     */
    public function getDocumentSubCategories($clubId, $documentType = 'CLUB', $clubDefaultLang = 'de')
    {
        $catArr = array();
        $detailsArr = $this->createQueryBuilder('c')
                ->select("c.id AS catId, (CASE WHEN (ci18n.titleLang IS NULL OR ci18n.titleLang='') THEN c.title ELSE ci18n.titleLang END) AS catTitle, sc.id AS subCatId, (CASE WHEN (sci18n.titleLang IS NULL OR sci18n.titleLang='') THEN sc.title ELSE sci18n.titleLang END) AS subCatTitle")
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategoryI18n', 'ci18n', 'WITH', '((ci18n.id = c.id) AND (ci18n.lang = :clubDefaultLang))')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', '(sc.category = c.id)')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategoryI18n', 'sci18n', 'WITH', '((sci18n.id = sc.id) AND (sci18n.lang = :clubDefaultLang))')
                ->where('c.club = :clubId')
                ->andWhere('c.documentType = :documentType')
                ->setParameters(array('clubId' => $clubId, 'documentType' => $documentType, 'clubDefaultLang' => $clubDefaultLang))
                ->addOrderBy('c.sortOrder', 'ASC')
                ->addOrderBy('sc.sortOrder', 'ASC')
                ->getQuery()
                ->getArrayResult();
        $totalCats=count($detailsArr);
        foreach ($detailsArr as $detail) {
            if (isset($catArr[$detail['catId']])) {
                if ($detail['subCatId'] != '') {
                    $catArr[$detail['catId']]['values'][$detail['subCatId']] = $detail['subCatTitle'];
                }
            } else {
                if ($detail['subCatId'] != '') {
                    $catArr[$detail['catId']]['title'] = $detail['catTitle'];
                    $catArr[$detail['catId']]['values'][$detail['subCatId']] = $detail['subCatTitle'];
                    $catArr[$detail['catId']]['subCount']=$totalCats;
                }
            }
        }

        return $catArr;
    }

    /**
     * Function to get maximum sort order of document categories.
     *
     * @param int    $clubId  Document club id 
     * @param string $docType Document type TEAM/WORKGROUP/CONTACT/CLUB
     *
     * @return int $maxSortOrder Maximum Sort Order
     */
    public function getMaxSortOrderofCategories($clubId, $docType)
    {
        $maxSortData = $this->createQueryBuilder('c')
                ->select('MAX(c.sortOrder) AS sortOrder')
                ->where('c.club = :clubId')
                ->andWhere('c.documentType = :type')
                ->setParameters(array('clubId' => $clubId, 'type' => $docType))
                ->getQuery()
                ->getArrayResult();

        $maxSortOrder = $maxSortData[0]['sortOrder'];
        if ($maxSortOrder == '') {
            $maxSortOrder = '0';
        }

        return $maxSortOrder;
    }

    /**
     * Function to check whether subcategory is of the current club
     * 
     * @param int $subCategoryId Sub category Id
     *
     * @return array $catArr
     */
    public function getCategoryClubId($subCategoryId)
    {
        $catArr = $this->createQueryBuilder('c')
                ->select("c.id, IDENTITY(c.club) AS clubId")
                ->innerJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', '(sc.category = c.id)')
                ->where('sc.id = :subCategoryId')
                ->setParameters(array('subCategoryId' => $subCategoryId))
                ->getQuery()
                ->getArrayResult();

        return (count($catArr) > 0) ? $catArr[0] : array();
    }

    /**
     * Function to check whether category exist for currentclub of particular type
     * 
     * @param int    $clubId Current club id
     * @param string $type   Document type (club,contact,team,workgroup)
     * @return boolean
     */
    public function checkCategoryExist($clubId, $type)
    {
        $catArr = $this->createQueryBuilder('C')
                ->select("COUNT(SC.id)")
                ->innerJoin('CommonUtilityBundle:FgClub', 'CL', 'WITH', '(CL.id = C.club)')
                ->innerJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'SC', 'WITH', '(SC.category = C.id)')
                ->where('CL.id = :club')
                ->andWhere('C.documentType = :type')
                ->setParameters(array('club' => $clubId, 'type' => $type))
                ->getQuery()
                ->getArrayResult();
        
        return $catArr[0][1];
    }

    /**
     * Function to get all category/subcategory for dragdrop
     *
     * @param int    $clubId      Current club id
     * @param string $type        Document type (club,contact,team,workgroup)
     * @param string $defaultLang Default lang
     *
     * @return array
     */
    public function getAllCategoriesSubCategories($clubId, $type, $defaultLang){
        $category = $this->createQueryBuilder('c')
                ->select("c.id,sc.id as subId,c.sortOrder,sc.sortOrder as subSortOrder, c.documentType")
                ->addSelect('(CASE WHEN ci18.titleLang IS NOT NULL THEN ci18.titleLang ELSE c.title END) AS title')
                ->addSelect('(CASE WHEN sci18n.titleLang IS NOT NULL THEN sci18n.titleLang ELSE sc.title END) AS titleSub')
                ->addSelect("(SELECT count(d.id) FROM CommonUtilityBundle:FgDmDocuments d WHERE d.club= $clubId AND d.category = c.id AND d.documentType=c.documentType) documentCount")
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategoryI18n', 'ci18', 'WITH', '((ci18.id = c.id) AND (ci18.lang = :clubDefaultLang))')
                ->innerJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', '(sc.category = c.id)')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategoryI18n', 'sci18n', 'WITH', '((sci18n.id = sc.id) AND (sci18n.lang = :clubDefaultLang))')
                ->where('c.club=:clubId')
                ->andWhere('c.documentType=:type')
                ->addOrderBy('c.sortOrder', 'ASC')
                ->addOrderBy('sc.sortOrder', 'ASC')
                ->setParameters(array('clubId' => $clubId, 'type' => $type,'clubDefaultLang'=>$defaultLang));
        $dataResult = $category->getQuery()->getArrayResult();

        return $dataResult;

    }

    /**
     * Function to get all document categories and sub-categories including heirarchical level.
     *
     * @param object  $club               Club object
     * @param int     $clubId             Current club id
     * @param string  $documentType       Document type CONTACT/CLUB/WORKGROUP/TEAM
     * @param object  $container          Container object
     * @param boolean $isAdmin            Is admin for team/workgroup
     * @param array   $adminstrativeRoles TeamsIds/workgroupIds which the contact has administrative role
     * @param array   $memberRoles        TeamsIds/workgroupIds which the contact has member role
     * @param int     $currentRoleId      Current TeamsId/workgroupId  (For team documents and workgroup ducuments)
     *
     * @return array
     */
    public function getDocumentSidebarCategories($club, $clubId, $documentType, $container, $isAdmin, $adminstrativeRoles = array(), $memberRoles = array(), $currentRoleId = "")
    {
        $defaultLang = $club->get('default_lang');
        $parametersArray = array('clubId' => $clubId, 'documentType' => $documentType, 'defaultLang' => $defaultLang);
        $isFedMember = $container->get('contact')->get('isFedMember');
        //For federation members only, documents categories from hierarchy level is needed
        $clubHeirarchyArray = ($isFedMember == 1) ? $club->get('clubHeirarchy') : array();
        $teamCategory = $club->get('club_team_id');
        $contactId = $container->get('contact')->get('id');
        rsort($clubHeirarchyArray); // For listing in the order club docs, sub-federation docs, federation docs.
        $fedSortSql = $fedGroupSql = "";
        $categoryData = array();
        $sort = 1;
        foreach ($clubHeirarchyArray as $key => $clubIdVal) {
            $sort = $sort + 1;
            $fedSortSql .= "WHEN (c.club = '$clubIdVal') THEN $sort ";
            $fedGroupSql .= "WHEN (c.club = '$clubIdVal') THEN 'FDOCS-$clubIdVal' ";
        }
        $sortSql = "(CASE WHEN (c.club = '$clubId') THEN 1 $fedSortSql ELSE 0 END) AS sort";
        if($documentType == 'CLUB') {
            $groupSql = "(CASE WHEN (c.club = '$clubId') THEN 'DOCS-$clubId' $fedGroupSql ELSE '' END) AS groupTitle";
        } else {
            $groupSql = "(CASE WHEN (c.club = '$clubId') THEN '$documentType' ELSE '' END) AS groupTitle";
        }
        $catTitle = "(CASE WHEN (ci18n.titleLang IS NULL OR ci18n.titleLang='') THEN c.title ELSE ci18n.titleLang END) AS catTitle";
        $subCatTitle = "(CASE WHEN (sci18n.titleLang IS NULL OR sci18n.titleLang='') THEN sc.title ELSE sci18n.titleLang END) AS subCatTitle";

        $categoryDetails = $this->createQueryBuilder('c')
                ->select("c.id AS catId, $catTitle, IDENTITY(c.club) AS clubId, sc.id AS subCatId, $subCatTitle, $sortSql, $groupSql ")
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategoryI18n', 'ci18n', 'WITH', '((ci18n.id = c.id) AND (ci18n.lang = :defaultLang))')
                ->Join('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', '(sc.category = c.id)')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategoryI18n', 'sci18n', 'WITH', '((sci18n.id = sc.id) AND (sci18n.lang = :defaultLang))')
                ->leftJoin('CommonUtilityBundle:FgDmDocuments', 'doc', 'WITH', '(sc.id=doc.subcategory)')
                ->where('c.documentType = :documentType');
        if($documentType != 'CLUB') {
            $categoryDetails = $categoryDetails->andWhere('c.club=:clubId');
        }
        $categoryDetailsArray = array();
        $docObj = new DocumentCategory($container, $documentType);
        if($documentType == 'CONTACT') {
            $categoryDetailsArray = $docObj->getContactConditionForDocCategories($categoryDetails, $contactId);
        } else if($documentType == 'CLUB') {
            $categoryDetailsArray = $docObj->getClubConditionForDocCategories($categoryDetails, $clubHeirarchyArray);
        } else if($documentType == 'TEAM' && !$isAdmin) {
            $categoryDetailsArray = $docObj->getTeamConditionForDocCategories($categoryDetails, $adminstrativeRoles, $memberRoles, $contactId, $teamCategory, $currentRoleId);
        } else if($documentType == 'WORKGROUP' && !$isAdmin) {
            $categoryDetailsArray = $docObj->getWorkgroupConditionForDocCategories($categoryDetails, $adminstrativeRoles, $memberRoles, $currentRoleId);
        }
        if($categoryDetailsArray['categoryDetails']) {
            $categoryDetails = $categoryDetailsArray['categoryDetails'];
            $parametersArray = array_merge($parametersArray, $categoryDetailsArray['extraParams']);
        }
        $categoryDetails = $categoryDetails->setParameters($parametersArray);
        $categoryDetails = $categoryDetails->groupBy('sc.id')
                            ->orderBy('sort', 'ASC')
                            ->addOrderBy('c.sortOrder', 'ASC')
                            ->addOrderBy('sc.sortOrder', 'ASC')
                            ->getQuery()
                            ->getArrayResult();
        if(count($categoryDetails) != 0){
            $docObj = new DocumentCategory($container, $documentType);
            $categoryData = $docObj->iterateDocumentCategoriesForInternal($categoryDetails, $currentRoleId);
        }
        
        return $categoryData;
    }

     /**
     * Function to return the count of document categories for a particular club
     *
     * @param int             $clubId Current-club-id
     * @param string or array $type   Document type CONTACT/CLUB/WORKGROUP/TEAM
     *
     * @return int
     */
    public function getCategoryCountDetails($clubId, $type)
    {
        $catCount = $this->createQueryBuilder('C')
                ->select("COUNT(C.id)")
                ->innerJoin('CommonUtilityBundle:FgClub', 'CL', 'WITH', '(CL.id = C.club)')
                ->where('CL.id = :club')
                ->andWhere('C.documentType IN (:type)')
                ->setParameter('type', $type)
                ->setParameter('club', $clubId)
                ->getQuery()
                ->getSingleScalarResult();

        return $catCount;
    }


    /**
     * Function to return the club of a particular category for checking access rights
     *
     * @param int    $catId Category Id
     * @param string $type  Document type CONTACT/CLUB/WORKGROUP/TEAM
     *
     * @return int
     */
    public function getCategoryClubIdForAccessCheck($catId, $type)
    {

        $result = $this->createQueryBuilder('C')
                ->select("CL.id")
                ->leftJoin('CommonUtilityBundle:FgClub', 'CL', 'WITH', '(CL.id = C.club)')
                ->where('C.id = :catId')
                ->andWhere('C.documentType = :type')
                ->setParameters(array('catId' => $catId, 'type' => $type))
                ->getQuery()
                ->getArrayResult();

        return $result[0]['id'];
    }
    
    /**
     * Function to update maintable entry with clubdefault language entry
     *
     * @param string $clubDefaultLang  Club default language
     * @param int    $clubId           club id 
     * 
     * @return boolean
     */
    private function updateMainTable($clubDefaultLang, $clubId)
    {
        $mainFileds = array('title');
        $i18Fields = array('title_lang');
        $fieldsList = array( 'mainTable' => 'fg_dm_document_category',
            'i18nTable' => 'fg_dm_document_category_i18n',
            'mainField' => $mainFileds,
            'i18nFields' => $i18Fields
            
        );
        $where = 'A.club_id = '.$clubId;
        $updateMainTable = FgUtility::updateDefaultTable($clubDefaultLang, $fieldsList, $where);
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $conn->executeQuery($updateMainTable);
        
        return true;
    }
}