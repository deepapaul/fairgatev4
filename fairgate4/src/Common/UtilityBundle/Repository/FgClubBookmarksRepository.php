<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgClubBookmarks;

/**
 * FgClubBookmarksRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgClubBookmarksRepository extends EntityRepository {

   

    /**
     * Function used to create bookmark
     *
     * @param type $type          type
     * @param type $selectedIdArr selecteted id array
     * @param type $clubId        club id
     * @param type $contactId     contact id
     */
    public function handleBookmark($type, $selectedIdArr, $clubId, $contactId) {
        foreach ($selectedIdArr as $key => $selectedId) {
            if ($type && $selectedId) {
                $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgClubBookmarks')->findOneBy(array('club' => $clubId, 'contact' => $contactId), array('sortOrder' => 'DESC'));
                $bookmark = $this->_em->getRepository('CommonUtilityBundle:FgClubBookmarks')->findOneBy(array('type' => $type, $type => $selectedId, 'club' => $clubId, 'contact' => $contactId));
                if (count($bookmark) > 0) {
                    $this->_em->remove($bookmark);
                } else {
                    $club = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
                    $contact = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                    $insertFlag = false;
                    $bookmark = new FgClubBookmarks();
                    $bookmark->setClub($club);
                    $bookmark->setContact($contact);
                    $bookmark->setType($type);
                    if ($lastRow) {
                        $bookmark->setSortOrder($lastRow->getsortOrder() + 1);
                    } else {
                        $bookmark->setSortOrder(1);
                    }
                    if ($type == 'class') {
                        $class = $this->_em->getRepository('CommonUtilityBundle:FgClubClass')->find($selectedId);
                        if (count($class) > 0) {
                            $bookmark->setClass($class);
                            $insertFlag = true;
                        }
                    } else if ($type == 'subfed') {
                        $subfed = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($selectedId);
                        if (count($subfed) > 0) {
                            $bookmark->setSubFed($subfed);
                            $insertFlag = true;
                        }
                    } else if ($type == 'filter') {
                        $filter = $this->_em->getRepository('CommonUtilityBundle:FgClubFilter')->find($selectedId);
                        if (count($filter) > 0) {
                            $bookmark->setFilter($filter);
                            $insertFlag = true;
                        }
                    }
                    if ($insertFlag) {
                        $this->_em->persist($bookmark);
                    }
                }
            }
        }
        $this->_em->flush();
    }
}
