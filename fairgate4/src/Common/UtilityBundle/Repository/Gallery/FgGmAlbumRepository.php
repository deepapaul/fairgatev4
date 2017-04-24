<?php

namespace Common\UtilityBundle\Repository\Gallery;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\GalleryPdo;

/**
 * FgGmAlbumRepository.
 *
 * This class is used for handling album settings in gallery in internal area
 */
class FgGmAlbumRepository extends EntityRepository
{
    /**
     * Function to save album settings page.
     *
     * @param array          $dataArray       Data array
     * @param int            $clubId          Club Id
     * @param int            $contactId       Contact Id
     * @param integer/string $galleryType     Gallery type whether club or role id
     * @param int            $clubDefaultLang Club default language
     * @param array          $clubLanguages   Club languages
     *
     * @return query result or as processed array based on the $exec parameter
     */
    public function saveAlbumsettings($dataArray, $clubId, $contactId, $galleryType, $clubDefaultLang, $clubLanguages, $container)
    {
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $parentIdArray = array();
        foreach ($dataArray as $albumId => $data) {
            /* save album  table data */
            $albumObj = $this->find($albumId);
            if ($data['is_deleted'] == 1) {
                $this->deleteAlbum($albumObj, $albumId, $data['parentId'], $data['imageCount'], $container);
                continue;
            }
            if (empty($albumObj)) {
                $albumObj = new \Common\UtilityBundle\Entity\FgGmAlbum();
            }
            if (isset($data['title'][$clubDefaultLang])) {
                $albumObj->setName($data['title'][$clubDefaultLang]);
            }
            $albumObj->setClub($clubObj);
            $this->_em->persist($albumObj);
            $this->_em->flush();

            /* save album i18n table data */
            $currentAlbumId = $albumObj->getId();
            $parentIdArray[$albumId] = $currentAlbumId;
            $currentAlbumIdobj = $this->find($currentAlbumId);
            foreach ($clubLanguages as $lang) {
                $langdetails = $this->_em->getRepository('CommonUtilityBundle:FgGmAlbumI18n')->getAlbumi18nUpdateDetails($lang, $currentAlbumId);
                if (!empty($langdetails)) {
                    if (array_key_exists($lang, ($data['title']))) {
                        $this->_em->getRepository('CommonUtilityBundle:FgGmAlbumI18n')->updateSingleLang($currentAlbumId, $lang, $data['title'][$lang]);
                    }
                } else {
                 $title = $data['title'][$lang];
                 $this->_em->getRepository('CommonUtilityBundle:FgGmAlbumI18n')->insertAlbumLangDetails($currentAlbumId, $lang, $title, 1, $container);
                }
            }

            /* save gallery table data */
            if (isset($data['new'])) {
                $galleryObj = new \Common\UtilityBundle\Entity\FgGmGallery();
            } else {
                $galleryId = $this->_em->getRepository('CommonUtilityBundle:FgGmGallery')->getGalleryId($clubId, $currentAlbumId);
                $galleryObj = $this->_em->getRepository('CommonUtilityBundle:FgGmGallery')->findOneBy(array('id' => $galleryId));
            }

            if (isset($data['sortOrder'])) {
                $galleryObj->setSortOrder($data['sortOrder']);
            }
            ($data['parentId'] == 0) ? $galleryObj->setParentId(0) : $galleryObj->setParentId($parentIdArray[$data['parentId']]);
            ($galleryType == 'club') ? $galleryObj->setType('CLUB') : $galleryObj->setType('ROLE');
            if ($galleryType != 'club') {
                $roleObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($galleryType);
                $galleryObj->setRole($roleObj);
            }
            $galleryObj->setAlbum($currentAlbumIdobj);
            $galleryObj->setClub($clubObj);
            $this->_em->persist($galleryObj);

           /* save bookmark table data */
            if ($data['is_bookmarked'] == 1) {
                $lastInserted = $this->_em->getRepository('CommonUtilityBundle:FgGmBookmarks')->getBookmarkSortOrder($clubId, $contactId);
                $bookmark = $this->_em->getRepository('CommonUtilityBundle:FgGmBookmarks')->findOneBy(array('album' => $currentAlbumId));
                if (!empty($bookmark)) {
                    $bookmarkObj = $bookmark;
                } else {
                    $bookmarkObj = new \Common\UtilityBundle\Entity\FgGmBookmarks();
                }
                $bookmarkObj->setClub($clubObj);
                $bookmarkObj->setContact($contactObj);
                $bookmarkObj->setAlbum($currentAlbumIdobj);
                $bookmarkObj->setSortOrder($lastInserted + 1);
                $this->_em->persist($bookmarkObj);
            } elseif ($data['is_bookmarked'] == 0) {
                if (!isset($data['new'])) {
                    $bookmarkObj = $this->_em->getRepository('CommonUtilityBundle:FgGmBookmarks')->findOneBy(array('album' => $currentAlbumId)); 
                    if (!empty($bookmarkObj)) {
                        $this->_em->remove($bookmarkObj);
                    }
                }
            }

            $this->_em->flush();
        }
        $this->updateMainTable($clubDefaultLang, $clubId);
    }

    /**
     * Function to save album settings page.
     *
     * @param int            $clubId      Club Id
     * @param int            $contactId   Contact Id
     * @param integer/string $galleryType Gallery type whether club or role id
     *
     * @return query result or as processed array based on the $exec parameter
     */
    public function getAlbumdetails($clubId, $contactId, $galleryType)
    {
        $type = ($galleryType == 'club') ? 'CLUB' : 'ROLE';
        $roleCondition = ($type == 'ROLE') ? ' AND g.role='.$galleryType : '';
        $album = $this->createQueryBuilder('a')
                ->select('a.id, ai18.lang, ai18.nameLang,a.name as title, g.sortOrder, g.parentId, bm.id as bookmarkId')
                ->addSelect('(SELECT COUNT(distinct ai.id) FROM CommonUtilityBundle:FgGmAlbumItems ai LEFT JOIN CommonUtilityBundle:FgGmAlbum alb WITH (alb.id = ai.album)  WHERE ai.album = a.id) imageCount')
                ->addSelect('(SELECT COUNT(distinct gl.id) FROM CommonUtilityBundle:FgGmGallery gl LEFT JOIN CommonUtilityBundle:FgGmAlbum am WITH (am.id = gl.parentId)  WHERE gl.parentId = a.id) subAlbumCount')
                ->leftJoin('CommonUtilityBundle:FgGmAlbumI18n', 'ai18', 'WITH', 'ai18.id = a.id')
                ->leftJoin('CommonUtilityBundle:FgGmGallery', 'g', 'WITH', 'g.album = a.id')
                ->leftJoin('CommonUtilityBundle:FgGmBookmarks', 'bm', 'WITH', 'bm.club=:clubId AND bm.contact =:contactId AND bm.album=a.id')
                ->where('a.club=:clubId')
                ->andWhere('g.type=:type'.$roleCondition)
                ->addOrderBy('g.parentId')
                ->addOrderBy('g.sortOrder')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId, 'type' => $type));

        $dataResult = $album->getQuery()->getArrayResult();
        $result = array();
        foreach ($dataResult as $key => $arr) {
            $albumId = $arr['id'];
            $albumParentId = $arr['parentId'];

            //Changing the index to string to prevent the automatic sorting of the
            // data by the browser
            $albumIdMD5 = md5($arr['id']);
            $albumParentIdMd5 = md5($arr['parentId']);

            if ($albumParentId > 0) {
                if (!isset($result[$albumParentIdMd5]['children'][$albumIdMD5])) {
                    $result[$albumParentIdMd5]['children'][$albumIdMD5] = array('albumId' => $albumId, 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'], 'bookmarkId' => $arr['bookmarkId'], 'parentId' => $albumParentId, 'imageCount' => $arr['imageCount'], 'subAlbumCount' => $arr['subAlbumCount']);
                }

                $result[$albumParentIdMd5]['children'][$albumIdMD5]['titleLang'][$arr['lang']] = $arr['nameLang'];
            } else {
                if (!isset($result[$albumIdMD5])) {
                    $result[$albumIdMD5] = array('albumId' => $albumId, 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'], 'bookmarkId' => $arr['bookmarkId'], 'parentId' => $albumParentId, 'imageCount' => $arr['imageCount'], 'subAlbumCount' => $arr['subAlbumCount']);
                }

                $result[$albumIdMD5]['titleLang'][$arr['lang']] = $arr['nameLang'];
            }
        }

        return $result;
    }

    /**
     * Function to delete an album.
     *
     * @param int $albumObj   album object
     * @param int $albumId    album Id
     * @param int $parentId   gallery parent id
     * @param int $imageCount image count
     *
     * @return true
     */
    public function deleteAlbum($albumObj, $albumId, $parentId, $imageCount, $container)
    {
        if ($imageCount > 0) {
            if ($parentId > 0) {
                $this->_em->getRepository('CommonUtilityBundle:FgGmAlbumItems')->moveImagesFromAlbum($albumId, $parentId, 0, $container);
            } else {
                $this->_em->getRepository('CommonUtilityBundle:FgGmAlbumItems')->moveImagesFromAlbum($albumId, $parentId, 1, $container);
            }
        }
        $this->_em->remove($albumObj);
        $this->_em->flush();

        return true;
    }

    /**
     * Function to update maintable entry with clubdefault language entry
     *
     * @param int    $clubId  club id 
     * @param string $clubDefaultLang  Club default language
     * 
     * @return boolean
     */
    private function updateMainTable($clubDefaultLang, $clubId)
    {
        $mainFileds = array('name');
        $i18Fields = array('name_lang');
        $fieldsList = array('mainTable' => 'fg_gm_album',
            'i18nTable' => 'fg_gm_album_i18n',
            'mainField' => $mainFileds,
            'i18nFields' => $i18Fields
        );
        $where = 'A.club_id = ' . $clubId;
        $updateMainTable = FgUtility::updateDefaultTable($clubDefaultLang, $fieldsList, $where);
        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $conn->executeQuery($updateMainTable);

        return true;
    }
    /**
     * Function to get all club albums.
     * @param  string   $lang Language
     * @param int       $clubId      Club Id

     * @return query result or as processed array based on the $exec parameter
     */
    public function getClubAlbumdetails($clubId, $lang) {
        $album = $this->createQueryBuilder('a')
                ->select('a.id, ai18.lang, ai18.nameLang,a.name as title, g.sortOrder, g.parentId')
                ->leftJoin('CommonUtilityBundle:FgGmAlbumI18n', 'ai18', 'WITH', 'ai18.id = a.id AND ai18.lang = :lang')
                ->leftJoin('CommonUtilityBundle:FgGmGallery', 'g', 'WITH', 'g.album = a.id')
                ->where('a.club=:clubId')
                ->addOrderBy('g.parentId')
                ->addOrderBy('g.sortOrder')
                ->setParameters(array('clubId' => $clubId, 'lang' => $lang));


        return $album->getQuery()->getArrayResult();
    }

}
