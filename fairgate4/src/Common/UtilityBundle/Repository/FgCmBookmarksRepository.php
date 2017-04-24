<?php

/**
 * FgCmBookmarksRepository
 *
 * This class is basically used for bookmark related functionalities in contact manager.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmBookmarks;

/**
 * FgCmBookmarksRepository
 *
 * FgClubBookmarksRepository is being used in listing bookmarks in sidebar
 * of various module as well as the handling bookmark click from various areas.
 */
class FgCmBookmarksRepository extends EntityRepository {

    /**
     * Function to get updatebookmark of a perticular Contact
     *
     * Function used to Edit/ Delete bookmark
     * @param $bookmarkArr contains the data related to the bookmark
     * @param $action Determines whether it is Edit/ Delete action performed
     * @return boolean true or false
     */
    public function updatebookmark($bookmarkArr, $tablename = 'fg_cm_bookmarks') {
        // GENERATE PDO QUERY FOR MULTIPLE UPDATE AND DELETE.
        $delQryStr = '';
        $updateQry = '';
        if (count($bookmarkArr) > 0) {
            foreach ($bookmarkArr as $bookmarkId => $data) {
                $updateQryStr = 'SET ';
                foreach ($data as $field => $value) {
                    if ($field == 'is_deleted') {
                        $delQryStr .= "$bookmarkId,";
                    } else {
                        $updateQryStr .= "$field = '$value',";
                    }
                }
                if ($updateQryStr !== 'SET ') {
                    $updateStr = rtrim($updateQryStr, ',');
                    $updateQry .= "UPDATE $tablename $updateStr WHERE id = $bookmarkId;";
                }
            }
        }
        //GENERATE PDO QUERY FOR MULTIPLE UPDATE AND DELETE
        if ($updateQry !== '' || $delQryStr != '') {
            $conn = $this->getEntityManager()->getConnection();
            try {
                $conn->beginTransaction();
                if ($updateQry !== '') {
                    $conn->executeUpdate($updateQry);
                }
                if ($delQryStr != '') {
                    $delArr = explode(',', rtrim($delQryStr, ','));
                    $stmt = $conn->executeQuery("DELETE FROM $tablename WHERE id IN (?)", array($delArr), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));
                }
                $conn->commit();
            } catch (Exception $ex) {
                $conn->rollback();
                echo "Failed: " . $ex->getMessage();
                throw $ex;
            }
            $conn->close();
        }
        return true;

    }

    /**
     * Function to get createbookmark of a perticular Contact
     *
     * Function used to create bookmark
     * @param $bookmarkArr contains the data related to the bookmark
     * @param $action Determines whether it is Edit/ Delete action performed
     */
    public function createDeletebookmark($typeData, $selectedIdArr = array(), $clubId, $contactId) {
        $typeArr = explode('-', $typeData);
        $roleArr = array('ROLES', 'FROLES', 'FILTERROLES', 'TEAM', 'WORKGROUP', 'FI');
        if (in_array($typeArr[0], $roleArr)) {
            $type = 'role';
        } else {
            $type = ($typeData == 'fed_membership')? 'membership':$typeData;
        }
        foreach ($selectedIdArr as $key => $selectedId) {
            if ($type && $selectedId) {
                $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgCmBookmarks')->findOneBy(array('club' => $clubId, 'contact' => $contactId), array('sortOrder' => 'DESC'));
                $bookmark = $this->_em->getRepository('CommonUtilityBundle:FgCmBookmarks')->findOneBy(array('type' => $type, $type => $selectedId, 'club' => $clubId, 'contact' => $contactId));
                if (count($bookmark) > 0) {
                    $this->_em->remove($bookmark);
                } else {
                    $club = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
                    $contact = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
                    $insertFlag = false;
                    $bookmark = new FgCmBookmarks();
                    $bookmark->setClub($club);
                    $bookmark->setContact($contact);
                    $bookmark->setType($type);
                    if ($lastRow)
                        $bookmark->setSortOrder($lastRow->getsortOrder() + 1);
                    else
                        $bookmark->setSortOrder(1);
                    if ($type == 'role') {
                        $role = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($selectedId);
                        if (count($role) > 0) {
                            $bookmark->setRole($role);
                            $insertFlag = true;
                        }
                    } else if ($type == 'membership') {
                        $membership = $this->_em->getRepository('CommonUtilityBundle:FgCmMembership')->find($selectedId);
                        if (count($membership) > 0) {
                            $bookmark->setMembership($membership);
                            $insertFlag = true;
                        }
                    } else if ($type == 'filter') {
                        $filter = $this->_em->getRepository('CommonUtilityBundle:FgFilter')->find($selectedId);
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

    /**
     * Function to getSubclubs of a perticular Contact
     *
     * Function used to create bookmark
     * @param $bookmarkArr contains the data related to the bookmark
     * @param $action Determines whether it is Edit/ Delete action performed
     */
    public function getSubclubs($clubType, $clubId) {
        if ($clubType == 'federation' || $clubType == 'sub_federation') {

            $conn = $this->getEntityManager()->getConnection();
            /* Get all the subfed and fed is to exclude in function count */
            $clubIds = '';
            $resultClubIds = $conn->fetchAll("SELECT c.id as ids FROM (SELECT sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with :='{$clubId}',@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) c1 JOIN fg_club c ON c.id = c1.id ");
            foreach ($resultClubIds as $key => $val) {
                $clubIdArr[] = $val['ids'];
                $clubIds = implode(',', $clubIdArr);
            }
            $ids = ($clubIds != '' ? $clubIds . ',' : '') . $clubId;
        } else {

            $ids = $clubId;
        }
        return $ids;

    }
}
