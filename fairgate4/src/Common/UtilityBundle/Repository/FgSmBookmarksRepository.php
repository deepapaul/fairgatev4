<?php

/**
 * FgSmBookmarksRepository
 *
 * This class is basically used for bookmark related functionalities in sponsor manager.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgSmBookmarks;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;

/**
 * FgSmBookmarksRepository
 *
 * FgSmBookmarksRepository is being used in listing bookmarks in sidebar
 * of various module as well as the handling bookmark click from various areas.
 */
class FgSmBookmarksRepository extends EntityRepository {

    /**
     * function to get sponsors bookmark
     * @param inte $contactId contact id
     * @param int $clubId club id
     * @param obj $container container
     * @return array bookmark array
     */
    public function getSponsorBookmark($contactId, $clubId,$container) {
        $qb = $this->createQueryBuilder('b')
                ->select('b.id as bookMarkId,b.type as itemType,b.sortOrder as sortOrder,IDENTITY(b.contact) as contactId, IDENTITY(s.category) AS categoryId,s.serviceType AS serviceType, f.id AS filterId, f.name AS filterTitle, f.filterData AS filterData')
                ->addSelect('(CASE WHEN b.services IS NOT NULL THEN 1 ELSE 0 END) AS draggable')
                ->addSelect('(CASE WHEN b.services IS NOT NULL THEN s.title ELSE b.type END) AS title')
                ->addSelect('(CASE WHEN b.services IS NOT NULL THEN s.id ELSE b.type END) AS id')
                ->leftJoin('CommonUtilityBundle:FgSmServices', 's', 'WITH', 'b.services=s.id')
                ->leftJoin('CommonUtilityBundle:FgFilter', 'f', 'WITH', 'f.id = b.filter')
                ->where('b.contact=:contactId AND b.club=:clubId')
                ->orderBy('b.sortOrder', 'ASC')
                ->setParameters(array('contactId' => $contactId, 'clubId' => $clubId));
        $result = $qb->getQuery()->getResult();
        foreach($result as $key=>$value){
            switch($value['title']){
                case 'prospect':
                    $result[$key]['title']= $container->get('translator')->trans('PROSPECTS');
                    $result[$key]['image']='<i class="fa fg-star-o"></i>';break;
                case 'future_sponsor':
                   $result[$key]['title']= $container->get('translator')->trans('FUTURE_SPONSORS');
                    $result[$key]['image']='<i class="fa fg-star"></i>';break;
                case 'active_sponsor':
                    $result[$key]['title']= $container->get('translator')->trans('ACTIVE_SPONSORS');
                    $result[$key]['image']='<i class="fa fg-star"></i>';break;
                case 'former_sponsor':
                    $result[$key]['title']= $container->get('translator')->trans('FORMER_SPONSORS');
                    $result[$key]['image']='<i class="fa fg-star-half"></i>';break;
                case 'single_person':
                   $result[$key]['title']= $container->get('translator')->trans('SIDEBAR_SINGLE_PERSON');
                    $result[$key]['image']='<i class="fa fa-user"></i>';break;
                case 'company':
                   $result[$key]['title']= $container->get('translator')->trans('SIDEBAR_COMPANIES');
                    $result[$key]['image']='<i class="fa fa-building-o"></i>';break;
                case 'active_assignments':
                    $result[$key]['itemType']= 'overview';
                    $result[$key]['title']= $container->get('translator')->trans('ACTIVE_ASSIGNMENTS');break;
                case 'former_assignments':$result[$key]['itemType']= 'overview';
                    $result[$key]['title']= $container->get('translator')->trans('FORMER_ASSIGNMENTS');break;
                case 'future_assignments':$result[$key]['itemType']= 'overview';
                   $result[$key]['title']= $container->get('translator')->trans('FUTURE_ASSIGNMENTS');break;
                case 'recently_ended':$result[$key]['itemType']= 'overview';
                   $result[$key]['title']= $container->get('translator')->trans('RECENTLY_ENDED');break;
            }
            switch($value['serviceType']){
                case 'contact':$result[$key]['image']='<i class="fa fa-user"></i>';break;
                case 'team':$result[$key]['image']='<i class="fa fa-users"></i>';break;
            }
        }

        return $result;
    }

    /**
     * Function to update and delete bookmark fro msorting page
     * @param enum $type type of bookmark enum
     * @param int $selectedId  service id
     * @param int $clubId clubid
     * @param int $contactId contact id
     */
    public function createDeletebookmark($type, $selectedId, $clubId, $contactId) {
        if (($type != 'service') && ($type != 'filter')){
            $selectedId = null;
        }
        $selectedIds = is_array($selectedId) ? $selectedId : array($selectedId);
        foreach ($selectedIds as $selectedId) {
            $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->findOneBy(array('club' => $clubId, 'contact' => $contactId), array('sortOrder' => 'DESC'));
            if ($type == 'filter') {
                $bookmark = $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->findOneBy(array('type' => 'filter', 'filter' => $selectedId, 'club' => $clubId, 'contact' => $contactId));
            } else {
                $bookmark = $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->findOneBy(array('type' => $type, 'services' => $selectedId, 'club' => $clubId, 'contact' => $contactId));
            }
            if (count($bookmark) > 0) {
                $this->_em->remove($bookmark);
            } else {
                $club = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
                $contact = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
                $bookmark = new FgSmBookmarks();
                $bookmark->setClub($club);
                $bookmark->setContact($contact);
                $bookmark->setType($type);
                if ($type == 'filter') {
                    $filter = $this->_em->getRepository('CommonUtilityBundle:FgFilter')->find($selectedId);
                    $bookmark->setFilter($filter);
                } else {
                    $services = ($selectedId != null) ? $this->_em->getReference('CommonUtilityBundle:FgSmServices', $selectedId) : null;
                    $bookmark->setServices($services);
                }
                if ($lastRow)
                    $bookmark->setSortOrder($lastRow->getsortOrder() + 1);
                else
                    $bookmark->setSortOrder(1);
                $this->_em->persist($bookmark);
            }
            $this->_em->flush();
        }
    }

    /**
     * Function to update and delete sponsor bookmarks from sorting page
     *
     * @param array $bookmarkDetails details array
     *
     * @return boolean
     */
    public function updateDeleteBookmarkDetails($bookmarkDetails) {
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
     * Function to delete sponsor bookmark details from sorting page
     *
     * @param object $bookmarkObj bookmark object
     *
     * @return boolean
     */
    public function deleteBookmark($bookmarkObj) {
        $this->_em->remove($bookmarkObj);
        $this->_em->flush();
    }

    /**
     * Function to get bookmark details for sorting page
     *
     * @param int $clubId     clubId
     * @param int $contactId  contactId
     *
     * @return array
     */
    public function getSponsorBookmarkslisting($clubId, $contactId,$clubtype,$container) {

        $bookmark = $this->createQueryBuilder('b')
                ->select("b.id,b.type,b.sortOrder,(CASE WHEN ((f.id IS NULL) OR (f.id = '')) THEN sc.title ELSE f.name END) AS title,sc.id as subcatId,c.id as catId, sc.serviceType as sType, f.id AS filterId, f.filterData AS filterData")
                ->leftJoin('CommonUtilityBundle:FgSmServices', 'sc', 'WITH', 'sc.id = b.services')
                ->leftJoin('CommonUtilityBundle:FgSmCategory', 'c', 'WITH', 'c.id = sc.category')
                ->leftJoin('CommonUtilityBundle:FgFilter', 'f', 'WITH', 'f.id = b.filter')
                ->where('b.club=:clubId')
                ->andWhere('b.contact=:contactId')
                ->setParameters(array('clubId' => $clubId, 'contactId' => $contactId))
                ->orderBy('b.sortOrder');
        $dataResult = $bookmark->getQuery()->getArrayResult();
        $dataResult = $this->calculateCount($dataResult,$clubId,$clubtype,$container);
        
        return $dataResult;
    }
    
    /**
     * sub function to calculate bookmark count
     * @param array $dataResult bookmark result array
     * @param int $clubId clubId
     * @param int $clubtype clubtype
     * @return array final result array with count
     */
    private function calculateCount($dataResult,$clubId,$clubtype,$container){
        $subcatIds = $type = array();
        $club = $container->get('club');
        $masterTable = $club->get('clubTable');
        
        foreach($dataResult as $key => $value){
            if($value['subcatId']!='' || $value['subcatId']!= null  ){
              array_push($subcatIds, $value['subcatId']);
            }
            if($value['type'] == 'former_sponsor' || $value['type']=='active_sponsor' ||$value['type'] == 'future_sponsor' || $value['type']=='prospect'){
              array_push($type,$value['type']);
            }
        }
        $sponsorPdo = new SponsorPdo($container);
        $svCount = $sponsorPdo->sponsorServiceCount($clubId,$masterTable,$subcatIds,$clubtype);
        
        $pdo = new SponsorPdo($container);
        $typeCount = $pdo->getSidebarCount($clubId,$clubtype,$type);
        
        $pdo = new SponsorPdo($container);
        $typeCount1 = $pdo->assignmentOverviewCount($clubId,$clubtype);
              
        foreach($dataResult as $key => $value){
            if(array_key_exists($value['subcatId'],$svCount) ){
                $dataResult[$key]['count'] = $svCount[$value['subcatId']];
            }
            if(array_key_exists($value['type'],$typeCount[0]    )){
                 $dataResult[$key]['count'] = $typeCount[0][$value['type']];
            }
            if(array_key_exists($value['type'],$typeCount1[0]    )){
                 $dataResult[$key]['count'] = $typeCount1[0][$value['type']];
            }
        }
               
        return $dataResult;
    }

    /**
     * Function to create bookmarks for a given set of services.
     *
     * @param array $serviceIdArray Array of service ids
     * @param int   $clubId         Club id
     * @param int   $contactId      Contact id
     */
    public function createServiceBookmarks($serviceIdArray, $clubId, $contactId)
    {
        $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->findOneBy(array('club' => $clubId, 'contact' => $contactId), array('sortOrder' => 'DESC'));
        $club = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $contact = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        foreach ($serviceIdArray as $serviceId) {
            $service = $this->_em->getReference('CommonUtilityBundle:FgSmServices', $serviceId);

            $bookmark = new FgSmBookmarks();
            $bookmark->setClub($club);
            $bookmark->setContact($contact);
            $bookmark->setType('service');
            $bookmark->setServices($service);
            if ($lastRow) {
                $bookmark->setSortOrder($lastRow->getsortOrder() + 1);
            } else {
                $bookmark->setSortOrder(1);
            }
            $this->_em->persist($bookmark);
        }
        $this->_em->flush();
    }

    /**
     * Function to delete bookmarks of a given set of services.
     *
     * @param array $serviceIdArray Array of service ids
     * @param int   $clubId         Club id
     * @param int   $contactId      Contact id
     */
    public function deleteServiceBookmarks($serviceIdArray, $clubId, $contactId)
    {
        foreach ($serviceIdArray as $serviceId) {
            $bookmarkObj = $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->findOneBy(array('type' => 'service', 'services' => $serviceId, 'club' => $clubId, 'contact' => $contactId));
            $this->_em->remove($bookmarkObj);
        }
        $this->_em->flush();
    }

}
