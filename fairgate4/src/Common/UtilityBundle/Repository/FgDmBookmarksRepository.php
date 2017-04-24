<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This repository is used for document bookmarks
 *
 * @author pitsolutions.ch
 */
class FgDmBookmarksRepository extends EntityRepository
{

    /**
     * Function to create and delete bookmark
     *
     * @param string $type          document type club/team/contact/workgroup
     * @param array  $selectedIdArr sub category id array
     * @param int    $clubId        current club Id
     * @param int    $contactId     login contact Id
     *
     * @return boolean
     */
    public function createDeletebookmark($type, $selectedIdArr = array(), $clubId, $contactId)
    {
        foreach ($selectedIdArr as $key => $selectedId) {
            if ($type && $selectedId) {
                $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgDmBookmarks')->findOneBy(array('club' => $clubId, 'contact' => $contactId), array('sortOrder' => 'DESC'));
                $bookmark = $this->_em->getRepository('CommonUtilityBundle:FgDmBookmarks')->findOneBy(array('type' => $type, 'subcategory' => $selectedId, 'club' => $clubId, 'contact' => $contactId));

                if (count($bookmark) > 0) {
                    $this->_em->remove($bookmark);
                } else {
                    $club = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
                    $contact = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
                    $subcategory = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentSubcategory', $selectedId);
                    $catId = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->getCategoryIdfromSubcatid($selectedId);
                    $category = $this->_em->getReference('CommonUtilityBundle:FgDmDocumentCategory', $catId);
                    $bookmark = new \Common\UtilityBundle\Entity\FgDmBookmarks();

                    $bookmark->setClub($club);
                    $bookmark->setContact($contact);
                    $bookmark->setType($type);
                    $bookmark->setCategory($category);
                    $bookmark->setSubcategory($subcategory);
                    if ($lastRow)
                        $bookmark->setSortOrder($lastRow->getsortOrder() + 1);
                    else
                        $bookmark->setSortOrder(1);
                    $this->_em->persist($bookmark);
                }
            }
        }
        $this->_em->flush();
    }

    /**
     * Function to get bookmark List
     *
     * @param int    $clubId   current club Id
     * @param int    contactId login contact Id
     * @param string $type     Document type club/team/contact/workgroup
     *
     * @return array
     */
    public function getDocumentBookmarks($clubId, $contactId, $type)
    {
        $addSelect = "(SELECT COUNT(d.id) FROM CommonUtilityBundle:FgDmDocuments d WHERE d.subcategory=sc.id AND d.club=:clubId) AS docCount";
        $depositedCount = "(SELECT COUNT(d2.id) FROM CommonUtilityBundle:FgDmDocuments d2 LEFT JOIN CommonUtilityBundle:FgDmAssigment asgmnt WITH (asgmnt.document=d2.id) "
                    . "WHERE d2.subcategory=sc.id AND ((d2.depositedWith='ALL') OR (asgmnt.club=:clubId))) AS depositedCount";
        $bookmark = $this->createQueryBuilder('b')
                ->select("b.id,b.type,b.sortOrder,sc.title,sc.id as subcatId,c.id as catId, IDENTITY(sc.club) AS subCatClubId")
                ->addSelect($addSelect)
                ->addSelect($depositedCount)
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', 'sc.id = b.subcategory')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentCategory', 'c', 'WITH', 'c.id = sc.category')
                ->where('b.club=:clubId')
                ->andWhere('b.contact=:contactId')
                ->andWhere('b.type=:type')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'type'=>$type))
                ->orderBy('b.sortOrder');
        $dataResult = $bookmark->getQuery()->getArrayResult();
        
        $bookMarksArray = array();
        foreach ($dataResult as $bookMark) {
            $documentCount = ($bookMark['subCatClubId'] == $clubId) ? $bookMark['docCount'] : $bookMark['depositedCount'];
            $bookMark['count'] = $documentCount;
            unset($bookMark['docCount']);
            unset($bookMark['depositedCount']);
            $bookMarksArray[] = $bookMark;
        }

        return $bookMarksArray;
    }

    /**
     * Function to update and delete bookmark from sorting page
     *
     * @param array $bookmarkDetails Details array for updation
     *
     * @return boolean
     */
    public function updateDeleteBookmarkDetails($bookmarkDetails)
    {
        foreach ($bookmarkDetails as $id => $details) {
            $bookmarkObj = $this->find($id);
            if ($details['is_deleted'] == 1) {
                $this->deleteBookmark($bookmarkObj);
                continue;
            }
            if (isset($details['sort_order'])) {
                $bookmarkObj->setSortOrder($details['sort_order']);
            }
            $this->_em->persist($bookmarkObj);
            $this->_em->flush();
        }
        
        return true;
    }

    /**
     * Function to delete bookmark details from sorting page
     *
     * @param object $bookmarkObj bookmark object
     *
     * @return boolean
     */
    public function deleteBookmark($bookmarkObj)
    {
        $this->_em->remove($bookmarkObj);
        $this->_em->flush();
    }

    /**
     * Function to get document bookmarks.
     *
     * @param int    $clubId    current club id
     * @param int    $contactId login contact id
     * @param string $type      Document type club/team/contact/workgroup
     * @param object $club      Club object
     *
     * @return array $bookMarks Resulting array of bookmarks.
     */
    public function getBookmarksOfDocument($clubId, $contactId, $type, $club)
    {
        $defaultLang = $club->get('default_lang');
        $clubHeirarchyArray = $club->get('clubHeirarchy');
        $heirarchyType = "";
        foreach ($clubHeirarchyArray as $key => $clubIdVal) {
            $heirarchyType .= "WHEN (sc.club = '$clubIdVal') THEN 'FDOCS-$clubIdVal' ";
        }
        $title = "(CASE WHEN (sci18n.titleLang IS NULL OR sci18n.titleLang='') THEN sc.title ELSE sci18n.titleLang END) AS title";
        $itemType = "(CASE WHEN (sc.club = '$clubId') THEN 'DOCS-$clubId' $heirarchyType ELSE '' END) AS itemType";
        $draggable = "(CASE WHEN (sc.club = '$clubId') THEN 1 ELSE 0 END) AS draggable";
        $addSelect = "(SELECT COUNT(d.id) FROM CommonUtilityBundle:FgDmDocuments d WHERE d.subcategory=sc.id AND d.club=:clubId) AS docCount";
        $depositedCount = "(SELECT COUNT(d2.id) FROM CommonUtilityBundle:FgDmDocuments d2 LEFT JOIN CommonUtilityBundle:FgDmAssigment asgmnt WITH (asgmnt.document=d2.id) "
                    . "WHERE d2.subcategory=sc.id AND ((d2.depositedWith='ALL') OR (asgmnt.club=:clubId))) AS depositedCount";

        $bookMarks = $this->createQueryBuilder('bm')
                ->select("bm.id AS bookMarkId, IDENTITY(bm.contact) AS contactId, $itemType, IDENTITY(bm.subcategory) AS id, bm.sortOrder AS sortOrder, $title, IDENTITY(bm.category) AS categoryId, IDENTITY(sc.club) AS subCatClubId, $draggable")
                ->addSelect($addSelect)
                ->addSelect($depositedCount)
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategory', 'sc', 'WITH', '(sc.id = bm.subcategory)')
                ->leftJoin('CommonUtilityBundle:FgDmDocumentSubcategoryI18n', 'sci18n', 'WITH', '((sci18n.id = sc.id) AND (sci18n.lang = :defaultLang))')
                ->where('bm.club = :clubId')
                ->andWhere('bm.contact = :contactId')
                ->andWhere('bm.type = :type')
                ->orderBy('bm.sortOrder', 'ASC')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'type' => $type, 'defaultLang' => $defaultLang))
                ->getQuery()
                ->getArrayResult();

        $bookMarksArray = array();
        foreach ($bookMarks as $bookMark) {
            $documentCount = ($bookMark['subCatClubId'] == $clubId) ? $bookMark['docCount'] : $bookMark['depositedCount'];
            $bookMark['count'] = $documentCount;
            unset($bookMark['docCount']);
            unset($bookMark['depositedCount']);
            $bookMarksArray[] = $bookMark;
        }

        return $bookMarksArray;
    }
    
}