<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgSmCategory;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgSmCategoryRepository
 *
 * @author pitsolutions.ch
 */
class FgSmCategoryRepository extends EntityRepository {

    /**
     * get all sponsors category
     * @param int $clubId club id
     *
     * @return array
     */
    public function getAllSponsorCategory($clubId) {
        $qb = $this->createQueryBuilder('s')
                ->select('s.title as catTitle,s.id as categoryId')
                ->leftJoin('CommonUtilityBundle:FgSmServices', 'sm', 'WITH', 'sm.category = s.id')
                ->leftJoin('CommonUtilityBundle:FgSmBookmarks', 'sb', 'WITH', "sm.id=sb.services AND sb.type='service'")
                ->where('s.club=:club')
                ->orderBy('s.sortOrder', 'ASC')
                ->setParameter('club', $clubId)
                ->distinct();

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * To collect the services under a category and if bookmarked
     *
     * @param int $clubId       clubId
     * @param int $catId        catId
     * @param boolean $exec         exec
     * @param string $defaultlang contact default lang
     * 
     * @return array
     */
    public function getAllSponsorServicesBookmark($clubId, $catId, $defaultlang, $exec = true) {
        $qb = $this->createQueryBuilder('s')
                ->select("s.title as catTitle,s.sortOrder,s.id as categoryId,sm.sortOrder,sm.id as servicesId,COALESCE(NULLIF(si18.titleLang, ''),sm.title) as serviceTitle,sb.id as bookmarkId,sm.serviceType as serviceType")
                ->addSelect('(CASE WHEN sm.id IS NOT NULL THEN 1 ELSE 0 END) AS draggable')
                ->leftJoin('CommonUtilityBundle:FgSmServices', 'sm', 'WITH', 'sm.category = s.id')
                ->leftJoin('CommonUtilityBundle:FgSmServicesI18n', 'si18', 'WITH', "sm.id = si18.id AND si18.lang = '$defaultlang'")
                ->leftJoin('CommonUtilityBundle:FgSmBookmarks', 'sb', 'WITH', "sm.id=sb.services AND sb.type='service'")
                ->where('s.club=:club')
                ->andWhere('s.id=:categoryId')
                ->addOrderBy('s.sortOrder', 'ASC')
                ->addOrderBy('sm.sortOrder','ASC')
                ->setParameters(array('club' => $clubId, 'categoryId' => $catId));
        
        $result = $qb->getQuery()->getResult();
        if ($exec) {
            $resultFinal = array();
            $id = '';
            foreach ($result as $arr) {
                if (count($arr) > 0) {
                    if ($arr['servicesId'] != $id) {
                        $id = $arr['servicesId'];
                        $resultFinal[$id] = array('id' => $arr['servicesId'], 'title' => $arr['serviceTitle'], 'categoryId' => $arr['categoryId'], 'itemType' => 'service', 'bookMarkId' => $arr['bookmarkId'], 'draggable' => $arr['draggable'], 'serviceType' => $arr['serviceType']);
                    }
                }
            }
            return $resultFinal;
        } else {
            return $result;
        }
    }

    /**
     * Get all sponsor categories for overview settings
     * @param int $clubId club id
     * @return array
     */
    public function getAllSmCategories($clubId) {
        $qb = $this->createQueryBuilder('s')
                ->select('s.title as catTitle, s.id as categoryId, s.isSystem')
                ->where('s.club=:club')
                ->orderBy('s.sortOrder', 'ASC')
                ->setParameter('club', $clubId);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * function to create new category from sidebar
     * @param int $clubId club id
     * @param string $title  title
     * @return int sort order of recently inserted
     */
    public function saveCategorySidebar($clubId, $title) {
        $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->findOneBy(array('club' => $clubId), array('sortOrder' => 'DESC'));
        $club = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $category = new FgSmCategory();
        $category->setClub($club);
        $cat = str_replace('"', '', stripslashes($title));
        $category->setTitle($cat);
        $category->setisSystem(0);
        if ($lastRow) {
            $category->setSortOrder($lastRow->getsortOrder() + 1);
        } else {
            $category->setSortOrder(1);
        }
        $this->_em->persist($category);
        $this->_em->flush();

        return $category->getId();

    }

    /**
     * Function to create and edit categories from listing page
     *
     * @param int  $clubId club id
     * @param array $catArr  category array
     *
     * @return null
     */
    public function categoryserviceSave($clubId, $catArr) {

        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        foreach ($catArr as $id => $data) {
            $categoryobj = $this->find($id);
            if ($data['is_deleted'] == 1) {
                $this->deleteCategory($categoryobj);
                continue;
            }
            if (empty($categoryobj)) {
                $categoryobj = new \Common\UtilityBundle\Entity\FgSmCategory();
            }
            if (isset($data['title'])) {
                $serviceCat = str_replace('"', '', stripslashes($data['title']));
                $categoryobj->setTitle($serviceCat);
            }
            if (isset($data['sortOrder'])) {
                $categoryobj->setSortOrder($data['sortOrder']);
            }
            $categoryobj->setClub($clubobj);
            $categoryobj->setIsSystem(0);
            $this->_em->persist($categoryobj);
            $this->_em->flush();
        }
    }

    /**
     * Function to delete categories from listing page
     *
     * @param object  $catobj category object
     *
     * @return null
     */
    public function deleteCategory($catobj) {
        $this->_em->remove($catobj);
        $this->_em->flush();
    }

    /**
     * Function to list the sponsor categories
     *
     * @param int  $clubId club id
     *
     * @return array
     */
    public function getAllserviceCategories($clubId) {
        $qb = $this->createQueryBuilder('s')
                ->select('s.title, s.id, s.isSystem, s.sortOrder')
                ->addSelect("(SELECT count(d.id) FROM CommonUtilityBundle:FgSmServices d WHERE d.club= $clubId AND d.category = s.id ) serviceCount")
                ->addSelect("(SELECT count(b.id) FROM CommonUtilityBundle:FgSmBookings b WHERE b.club= $clubId AND b.category = s.id ) assignmentCount")
                ->where('s.club=:club')
                ->setParameter('club', $clubId);

        $dataResult = $qb->getQuery()->getResult();
        $result = array();
        foreach ($dataResult as $arr) {
            if (count($arr) > 0) {
                $result[$arr['id']] = array('id' => $arr['id'], 'title' => $arr['title'], 'sortOrder' => $arr['sortOrder'],
                    'isSystem' => $arr['isSystem'], 'serviceCount' => $arr['serviceCount'], 'assignmentCount' => $arr['assignmentCount']);
            }
        }

        return $result;
    }

    /**
     * Function to get category having services
     *
     * @param type $clubId
     * @param type $exec
     * @return type
     */
    public function getServicesWithCategory($clubId, $exec = true) {
        $qb = $this->createQueryBuilder('s')
                ->select('s.title as catTitle,s.id as categoryId,sm.serviceType,sm.title as serviceTitle,sm.id as servicesId,sm.price,sm.paymentPlan,sm.repetitionMonths')
                ->innerJoin('CommonUtilityBundle:FgSmServices', 'sm', 'WITH', 'sm.category = s.id')
                ->where('s.club=:club')
                ->orderBy('s.sortOrder,sm.sortOrder', 'ASC')
                ->setParameters(array('club' => $clubId));

        $result = $qb->getQuery()->getResult();
        if ($exec) {
            $resultFinal = array();
            foreach ($result as $arr) {
                if (count($arr) > 0) {
                    if (!isset($resultFinal["{$arr['categoryId']}"])) {
                        $resultFinal["{$arr['categoryId']}"] = array('categoryId' => $arr['categoryId'], 'catTitle' => $arr['catTitle'], 'serviceType' => $arr['serviceType']);
                    }
                    $resultFinal["{$arr['categoryId']}"]['services']["{$arr['servicesId']}"] = $arr;
                }
            }

            return $resultFinal;
        } else {

            return $result;
        }
    }

    /**
     * Get all sponsor services and its amount for sponsor overview
     * @param int    $conn         Connection Object
     * @param int    $clubId       Club id
     * @param int    $contact      Contact/Sponsor id
     * @param object $container    Container Object
     *
     * @return array
     */
    public function getAllServiceAssignments($conn, $clubId, $contact, $container) {
        $pdoClass = new SponsorPdo($container);
        $resultArray = $pdoClass->getAllServiceAssignments($clubId, $contact);

        return $resultArray;
    }

    /**
     * get services Category
     * @param int $clubId
     * @return array
     */
    public function serviceCategory($clubId){
        $qb = $this->createQueryBuilder('s')
                ->select('s.title as catTitle,s.id as categoryId,sm.title as serviceTitle,sm.id as servicesId')
                ->innerJoin('CommonUtilityBundle:FgSmServices', 'sm', 'WITH', 'sm.category = s.id')
                ->where('s.club=:club')
                ->groupBy('s.id,sm.id')
                ->orderBy('s.sortOrder,sm.sortOrder', 'ASC')
                ->setParameters(array('club' => $clubId));

        $result = $qb->getQuery()->getResult();
        return $result;
    }
    
    
     /**
     * function to create new category from sidebar
     * @param int $clubId club id
     * @param string $title  title
     * @return int sort order of recently inserted
     */
    public function createServiceSidebar($clubId,$catid, $title,$container,$contact,$terminologyService) {
        
        $lastRow = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->findOneBy(array('category' => $catid), array('sortOrder' => 'DESC'));
        $catObj = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->find($catid);
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $title = str_replace('"', '', stripslashes($title));
        $serviceObj = new \Common\UtilityBundle\Entity\FgSmServices();
        $serviceObj->setCategory($catObj);
        $serviceObj->setClub($clubObj);
        $serviceObj->setTitle($title);
        $serviceObj->setServiceType('club');
         $serviceObj->setPaymentPlan('none');
        if ($lastRow) {
            $serviceObj->setSortOrder($lastRow->getSortOrder()+1);
        } else {
            $serviceObj->setSortOrder(1);
        }
        
        $this->_em->persist($serviceObj);
        $this->_em->flush();
        // insert to i18 table
        $newServiceId = $serviceObj->getId();
        $clubDefLang = $container->get('club')->get('club_default_lang');
        $qry = "INSERT INTO fg_sm_services_i18n (`id`,`lang`,`title_lang`,`description_lang`,`is_active`) VALUES ($newServiceId,'$clubDefLang','$title','', 1)";
        $this->getEntityManager()->getConnection()->executeQuery($qry);
        $conn = $this->_em->getConnection();
        $title = FgUtility::getSecuredDataString($title, $conn);
        //log
        $logArr = array();
        $serviceId = $serviceObj->getId();
        $currentDate = date('Y-m-d H:i:s');
        $none = $container->get('translator')->trans('SM_NONE');
        $plan = $container->get('translator')->trans('LOG_SERVICE_PAYMENT_PLAN');
        $titleTrans = $container->get('translator')->trans('SERVICE_TITLE');
        $type = $container->get('translator')->trans('SERVICE_TYPE');
        $clubType = $terminologyService->getTerminology('Club', $container->getParameter('singular'));
        $clubDefaultLang = $container->get('club')->get('default_lang');
        $cat = $container->get('translator')->trans('SERVICE_CATEGORY');
        $catTitle = $catObj->getTitle();
        $catTitle = FgUtility::getSecuredDataString($catTitle, $conn);
        $logArr[] = "('$clubId','$serviceId','$currentDate','data','$plan', '-', '$none','$contact')";
        $logArr[] = "('$clubId','$serviceId','$currentDate','data','$titleTrans ($clubDefaultLang)', '-', '$title','$contact')";
        $logArr[] = "('$clubId','$serviceId','$currentDate','data','$cat',  '-',  '$catTitle','$contact')";
        $logArr[] = "('$clubId','$serviceId','$currentDate','data','$type',  '-',  '$clubType','$contact')";
        
        $sql = "INSERT INTO fg_sm_services_log(club_id, service_id, date, kind, field, value_before, value_after, changed_by) VALUES ".implode(',',$logArr);
        $this->getEntityManager()->getConnection()->executeQuery($sql);
        
        return $serviceObj->getId();

    }
}
