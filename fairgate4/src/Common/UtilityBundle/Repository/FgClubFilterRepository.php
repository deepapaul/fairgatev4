<?php

/**
 * FgClubFilterRepository
 *
 * This class is used for filtering clubs in club management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgClubFilter;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * FgClubFilterRepository
 *
 * This class is used for filtering clubs and for listing saved filters in club management.
 */
class FgClubFilterRepository extends EntityRepository {

    /**
     * Function to get Saved Filters for a particular Contact in Club Overview.
     *
     * @param int $contactId Contact Id
     * @param int $clubid    Club id
     *
     * @return array $dataResult Result array of saved filters.
     */
    public function getSideBarSavedFilter($contactId, $clubid) {
        $queryBuilder = $this->createQueryBuilder('f')
                ->select("f.id AS id, f.name AS title, bm.id as bookMarkId, f.isBroken as isBroken, 'filter' AS itemType")
                ->leftJoin('CommonUtilityBundle:FgClubBookmarks', 'bm', 'WITH', '(bm.contact = :userId AND bm.club =:clubid AND bm.filter=f.id)')
                ->orderBy('f.sortOrder')
                ->Where('f.club=:clubid');

        $queryBuilder->setParameter('userId', $contactId)
                ->setParameter('clubid', $clubid);

        $dataResult = $queryBuilder->getQuery()->getArrayResult();

        return $dataResult;
    }

    /**
     * Function to save Club Filters.
     *
     * @param array $filterdata Array of passed parameters.
     *
     * @return array $returnArray Result array containing type of operation and last inserted id.
     */
    public function saveClubFilter($filterdata,$container) {
        $clubId = intval($filterdata['clubId']);
        $contactId = intval($filterdata['contactId']);
        $conn = $this->getEntityManager()->getConnection();
        $name = FgUtility::getSecuredData($filterdata['name'], $conn);
        $jString = FgUtility::getSecuredData($filterdata['jString'], $conn);

      
       $fnCnt= $this->createQueryBuilder('FI')
            ->select("COUNT(FI.id) as cnt")
            ->where('FI.name =:name')
            ->andWhere('FI.contact=:contactId')
            ->andWhere('FI.club =:clubId')    
            ->setParameters(array('name'=>$name,'contactId' => $contactId,'clubId' => $clubId))
            ->getQuery()->getSingleResult();    
    
         $clubObj = new ClubPdo($container);
          if ($fnCnt['cnt'] <= 0){
           $operation = 'INSERT';
            $clubObj->saveClubFilter($name,$contactId,$clubId,$jString,$operation);
        } else {
          $operation = 'UDATE';
            $clubObj->saveClubFilter($name,$contactId,$clubId,$jString,$operation);
        }
       
         $filterId = $this->createQueryBuilder('FIL')
            ->select("FIL.id as lastid")
            ->where('FIL.name =:name')
            ->andWhere('FIL.contact=:contactId')
            ->andWhere('FIL.club =:clubId')    
            ->setParameters(array('name'=>$name,'contactId' => $contactId,'clubId' => $clubId))
            ->orderBy('FIL.id', 'ASC')
            ->getQuery()->getSingleResult();

         $lastId = $filterId['lastid'];
       $returnArray = array('operation' => $operation, 'last_id' => $lastId);
 
        return $returnArray;
    }

    /**
     * Function to get details of a single saved filter.
     *
     * @param int $id        Id
     * @param int $contactId Contact Id
     * @param int $clubId    Club Id
     *
     * @return array $dataResult Result array of details of saved filter.
     */
    public function getSingleSavedClubSidebarFilter($id, $contactId, $clubId) {
        $queryBuilder = $this->createQueryBuilder('f')
            ->select('f.id as filterId, f.name as filterName, IDENTITY(f.tableAttributes) as filterTableAttributes, bm.id as bookmarkid, f.filterData as filterData')
            ->leftJoin('CommonUtilityBundle:FgClubBookmarks', 'bm', 'WITH', '(bm.filter=f.id AND bm.contact = :contactId)')
            ->where('f.id=:id AND (f.club=:clubId OR f.club=1)');

        $queryBuilder->setParameter('id', $id)
                ->setParameter('clubId', $clubId)
                ->setParameter('contactId', $contactId);

        $dataResult = $queryBuilder->getQuery()->getArrayResult();

        return $dataResult;
    }
    
    /**
     * Function to get Saved Filters of a particular Contact for listing in Sidebar.
     *
     * @param int $contactId Contact Id
     * @param int $clubid    Club id
     *
     * @return array $dataResult Result array of sidebar filters.
     */
    public function getSavedClubSidebarFilter($contactId, $clubid) {
        $queryBuilder = $this->createQueryBuilder('f')
                ->select('f.id as id, f.name as title,f.filterData as filterData, IDENTITY(f.tableAttributes) as filterTableAttributes,bm.id as bookmarkid, f.isBroken as isBroken')
                ->leftJoin('CommonUtilityBundle:FgClubBookmarks', 'bm', 'WITH', '(bm.contact = :userId AND bm.club =:clubid AND bm.filter=f.id)')
                ->orderBy('f.sortOrder')
                ->where('f.club=:clubid');

        $queryBuilder->setParameter('userId', $contactId)
                ->setParameter('clubid', $clubid);

        $dataResult = $queryBuilder->getQuery()->getArrayResult();

        return $dataResult;
    }

    /**
     * Function to update Broken Filters of Club
     *
     * @param int     $id       Filter Id
     * @param boolean $isBroken Parameter to indicate whether the filter is broken or not.
     *
     * @return boolean true Successful updation.
     */
    public function updateClubBorkenFilter($id, $isBroken) {
        $id = intval($id);
        $isBroken = intval($isBroken);
        $conn = $this->getEntityManager();
        $filterObj = $conn->getRepository('CommonUtilityBundle:FgClubFilter')->find($id);
        $filterObj->setIsBroken($isBroken);
        $filterObj->setUpdatedAt(new \DateTime("now"));
        $this->_em->persist($filterObj);
        $this->_em->flush();

        return true;
    }

}
