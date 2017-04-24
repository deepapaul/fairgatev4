<?php

namespace Common\UtilityBundle\Repository\Gallery;

use Common\UtilityBundle\Entity\FgGmBookmarks;
use Doctrine\ORM\EntityRepository;

/**
 * FgGmBookmarkRepository.
 *
 * @author pitsolutions
 */
class FgGmBookmarksRepository extends EntityRepository
{
    /**
     * Function to get bookmark List.
     *
     * @param int    $clubId    clubId
     * @param int    $contactId contactId
     * @param string $defLang   default language
     *
     * @return array
     */
    public function getGalleryBookmarks($clubId, $contactId, $defLang)
    {
        $result = $this->createQueryBuilder('bm')
                ->select('bm.id as id, clb.id as clubid, a.id as albumid,g.type as type ,g.parentId as parentId,Identity(g.role) as roleId,cn.id as contactid, bm.sortOrder as sortOrder,ci18.nameLang as title,a.name as albumname,COUNT(im.items) as imagecount')
                ->leftJoin('CommonUtilityBundle:FgGmGallery', 'g', 'WITH', 'bm.album = g.album')
                ->leftJoin('CommonUtilityBundle:FgGmAlbum', 'a', 'WITH', 'g.album = a.id')
                ->leftJoin('CommonUtilityBundle:FgGmAlbumI18n', 'ci18', 'WITH', '(a.id = ci18.id AND ci18.lang = :defLang)')
                ->leftJoin('CommonUtilityBundle:FgClub', 'clb', 'WITH', 'clb.id = bm.club')
                ->leftJoin('CommonUtilityBundle:FgCmContact', 'cn', 'WITH', 'cn.id = bm.contact')
                ->leftJoin('CommonUtilityBundle:FgGmAlbumItems', 'im', 'WITH', 'a.id = im.album')
                ->where('bm.club=:clubId')
                ->andWhere('bm.contact=:contactId')
                ->orderBy('bm.sortOrder')
                ->groupBy('g.album')
                ->setParameter('clubId', $clubId)
                ->setParameter('contactId', $contactId)
                ->setParameter('defLang', $defLang)
                ->getQuery()
                ->getResult();

        return $result;
    }

    /**
     * Function to update and delete bookmark from sorting page.
     *
     * @param array $bookmarkArr bookmark details array
     *
     * @return bool
     */
    public function updateDeleteBookmarkDetails($bookmarkArr)
    {
        foreach ($bookmarkArr as $id => $details) {
            $bookmarkObj = $this->find($id);
            if ($details['is_deleted'] == 1) {
                $this->deleteBookmark($bookmarkObj);
                continue;
            }
            if (isset($details['sort_order'])) {
                $bookmarkObj->setsortOrder($details['sort_order']);
            }
            $this->_em->persist($bookmarkObj);
            $this->_em->flush();
        }

        return true;
    }

    /**
     * Function to delete bookmark details from sorting page.
     *
     * @param object $bookmarkObj bookmark object
     *
     * @return bool
     */
    public function deleteBookmark($bookmarkObj)
    {
        $this->_em->remove($bookmarkObj);
        $this->_em->flush();
    }

    /**
     * function to update sidebar bookmark.
     *
     * @param int    $selectedId
     * @param string $type
     * @param int    $clubId
     * @param int    $contactId
     */
    public function updateBookmark($selectedId, $type, $clubId, $contactId)
    {
        $bookmarkObj = $this->findOneBy(array('club' => $clubId, 'album' => $selectedId, 'contact' => $contactId));

        if ($bookmarkObj != null) {
            $this->_em->remove($bookmarkObj);
        } else {
            $lastRow = $this->findOneBy(array('club' => $clubId, 'contact' => $contactId), array('sortOrder' => 'DESC'));
            $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $albumObj = $this->_em->getRepository('CommonUtilityBundle:FgGmAlbum')->find($selectedId);

            $bookmarkObj = new FgGmBookmarks();
            $bookmarkObj->setClub($clubObj)->setContact($contactObj)->setAlbum($albumObj);
            if ($lastRow) {
                $bookmarkObj->setSortOrder($lastRow->getsortOrder() + 1);
            } else {
                $bookmarkObj->setSortOrder(1);
            }

            $this->_em->persist($bookmarkObj);
        }
        $this->_em->flush();
    }

    /**
     * Function to last sort order of gallery bookmark.
     *
     * @param int $clubId    club Id
     * @param int $contactId contact Id
     *
     * @return int $finalsortOrder sort order
     */
    public function getBookmarkSortOrder($clubId, $contactId)
    {
        $sortorder = $this->createQueryBuilder('bm')
                ->select('MAX(bm.sortOrder)')
                ->where('bm.club=:clubId')
                ->andWhere('bm.contact=:contactId')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId));
        $result = $sortorder->getQuery()->getSingleScalarResult();

        $finalsortOrder = ($result) ? $result : 0;

        return $finalsortOrder;


    }
}
