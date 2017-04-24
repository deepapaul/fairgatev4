<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;
/**
 * FgSmServicesRepository
 *
 * This class is used for handling sponsor services in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmServicesRepository extends EntityRepository
{
    private $insertDataArray = array();
    private $deleteServicesArray = array();
    private $updateDataArray = array();
    private $insertLogArray = array();
    private $addBookmarksArray = array();
    private $removeBookmarksArray = array();

    /**
     * Function to get services details of a sponsor service category.
     *
     * @param int $catId     Category Id
     * @param int $clubId    Club Id
     * @param int $contactId Contact Id
     *
     * @return array $result Resulting data array of services.
     */
    public function getServicesDataOfCategory($catId, $clubId, $contactId)
    {
        $currDate = date('Y-m-d H:i:s');

        $resData = $this->createQueryBuilder('s')
                ->select("s.id, s.title, s.description, s.serviceType AS serviceType, s.paymentPlan AS paymentPlan, s.repetitionMonths AS repetitionMonths,"
                        . "(SELECT COUNT(b.id) FROM CommonUtilityBundle:FgSmBookings b, CommonUtilityBundle:FgCmContact c1 WHERE   b.service=s.id AND c1.id=b.contact and c1.isSponsor = 1 AND c1.isDeleted = 0 AND c1.isPermanentDelete =0  AND b.isDeleted = 0 AND b.beginDate <= '$currDate' AND ((b.endDate >= '$currDate') OR (b.endDate IS NULL) OR (b.endDate=''))) AS assignmentCount,"
                        . "(SELECT COUNT(b2.id) FROM CommonUtilityBundle:FgSmBookings b2 WHERE b2.service=s.id AND b2.isDeleted = 0 ) AS allAssignments,"
                        . "s.price AS price, s.sortOrder AS sortOrder, sI18n.lang AS lang, sI18n.titleLang AS titleLang, sI18n.descriptionLang AS descriptionLang, bm.id AS bookMarkId")
                ->leftJoin('CommonUtilityBundle:FgSmServicesI18n', 'sI18n', 'WITH', 'sI18n.id = s.id')
                ->leftJoin('CommonUtilityBundle:FgSmBookmarks', 'bm', 'WITH', "bm.services = s.id AND bm.type='service' AND bm.club=:clubId AND bm.contact =:contactId")
                ->where('s.category=:catId')
                ->setParameters(array('catId' => $catId, 'clubId' => $clubId, 'contactId' => $contactId))
                ->getQuery()
                ->getArrayResult();

        $result = array();
      
        foreach ($resData as $res) {
            if (!isset($result[$res['sortOrder']])) {
                $result[$res['sortOrder']] = array('id' => $res['id'], 'title' => $res['title'], 'description' => $res['description'], 'assignmentCount' => $res['assignmentCount'], 'allAssignments' => $res['allAssignments'], 'paymentPlan' => $res['paymentPlan'], 'repetitionMonths' => $res['repetitionMonths'], 'price' => $res['price'], 'bookMarkId' => $res['bookMarkId'], 'serviceType' => $res['serviceType']);
            }
            $result[$res['sortOrder']]['titleLang'][$res['lang']] = $res['titleLang'];
            $result[$res['sortOrder']]['descriptionLang'][$res['lang']] = $res['descriptionLang'];
        }

        return $result;
    }

    /**
     * Function to update (Add, Edit, Delete) Sponsor Service Category data and Services.
     *
     * @param array  $dataArray        Array of data to be updated
     * @param int    $clubId           Club Id
     * @param int    $currContactId    Current Logged-in Contact Id
     * @param string $clubDefaultLang  Club Default Language
     * @param object $container        Container Object
     */
    public function updateSponsorServices($dataArray, $clubId, $currContactId, $clubDefaultLang, $container)
    {
        if (count($dataArray) > 0) {
            foreach ($dataArray as $currCatId => $serviceCatArray) {
                $catObj = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->find($currCatId);
                if ($catObj->getClub()->getId() == $clubId) {
                    if (isset($serviceCatArray['title'])) {
                        // Update service category title.
                        $title = stripslashes($serviceCatArray['title']);
                        $title = str_replace('"', '', $title);
                        $catObj->setTitle($title);
                        $this->_em->persist($catObj);
                        $this->_em->flush();
                    }
                    if (isset($serviceCatArray['service'])) {
                        foreach ($serviceCatArray['service'] as $serviceId => $serviceArray) {
                            if ($serviceId == 'new') {
                                // Add Sponsor Service.
                                $this->insertDataArray = $serviceArray;
                            } else {
                                $delFlag = isset($serviceArray['is_deleted']) ? $serviceArray['is_deleted'] : 0;
                                if (($delFlag == 1) || ($delFlag == '1')) {
                                    // Delete Sponsor Service.
                                    $this->deleteServicesArray[] = $serviceId;
                                } else {
                                    // Update Sponsor Service.
                                    $this->updateDataArray[$serviceId] = $serviceArray;
                                }
                            }
                        }
                    }
                }
            }
            // Execute Queries (Insert, Delete, Update).
            $this->executeQueries($clubId, $currContactId, $clubDefaultLang, $container, $currCatId);
        }
    }

    /**
     * Function to Execute Queries for Insertion, Deletion and Updation.
     *
     * @param int    $clubId           Club Id
     * @param int    $currContactId    Current Logged-in Contact Id
     * @param string $clubDefaultLang  Club Default Language
     * @param object $container        Container Object
     * @param int    $currCatId        Current category id
     */
    private function executeQueries($clubId, $currContactId, $clubDefaultLang, $container, $currCatId)
    {
        $translator = $container->get('translator');

        // Delete Recipients List.
        $this->executeDeleteQuery($clubId);

        // Update Recipients List.
        $this->executeUpdateQuery($clubDefaultLang, $translator, $clubId, $currCatId, $container);

        // Insert Recipients List.
        $this->executeInsertQuery($clubId, $clubDefaultLang, $translator, $currCatId, $container);

        // Insert log data for insert, update and delete.
        if (count($this->insertLogArray)) {
            $this->_em->getRepository('CommonUtilityBundle:FgSmServicesLog')->insertLogData($clubId, $currContactId, $this->insertLogArray);
        }
        // Add Bookmarks.
        if (count($this->addBookmarksArray)) {
            $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->createServiceBookmarks($this->addBookmarksArray, $clubId, $currContactId);
        }
        // Remove Bookmarks.
        if (count($this->removeBookmarksArray)) {
            $this->_em->getRepository('CommonUtilityBundle:FgSmBookmarks')->deleteServiceBookmarks($this->removeBookmarksArray, $clubId, $currContactId);
        }
    }

    /**
     * Function to Execute Delete Query.
     *
     * @param int $clubId Club Id
     */
    private function executeDeleteQuery($clubId)
    {
        if (count($this->deleteServicesArray)) {
            foreach ($this->deleteServicesArray as $delService) {
                $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->findOneBy(array('id' => $delService, 'club' => $clubId));
                if ($serviceObj) {
                    $this->_em->remove($serviceObj);
                }
            }
            $this->_em->flush();
        }
    }

    /**
     * Function to Execute Insert Query.
     *
     * @param int    $clubId                Club Id
     * @param string $clubDefaultLang       Club Default Language
     * @param object $translator            Translator Object
     * @param int    $currCatId             Current category id
     * @param object $container             Container Object
     */
    private function executeInsertQuery($clubId, $clubDefaultLang, $translator, $currCatId, $container = false)
    {
        $terminologyService = $container->get('fairgate_terminology_service');
        if (count($this->insertDataArray)) {
            $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            foreach ($this->insertDataArray as $tempId => $insertDataArr) {
                if (isset($insertDataArr['category_id'])) {
                    $serviceObj = $this->addService($insertDataArr, $clubId, $clubDefaultLang, $translator, $clubObj, $currCatId, $terminologyService, $container);
                    $newServiceId = $serviceObj->getId();
                    if ($newServiceId != '') {
                        if (isset($insertDataArr['book_marked'])) {
                            if ($insertDataArr['book_marked'] == '1') {
                                $this->addBookmarksArray[] = $newServiceId;
                            }
                        }
                        // Translation entries.
                        if (isset($insertDataArr['i18n'])) {
                            $this->_em->getRepository('CommonUtilityBundle:FgSmServicesI18n')->insertTranslationData($newServiceId, $insertDataArr['i18n'], $serviceObj, $clubDefaultLang, $container);
                        }
                    }
                }
            }
        }
    }

    /**
     * Function to add a service.
     *
     * @param array  $insertDataArr         Array of data to insert
     * @param int    $clubId                Club Id
     * @param string $clubDefaultLang       Club Default Language
     * @param object $translator            Translator Object
     * @param object $clubObj               Club Object
     * @param int    $currCatId             Current category id
     * @param object $terminologyService    Terminology Service
     * @param object $container             Container Object
     *
     * @return object $serviceObj Service Object
     */
    private function addService($insertDataArr, $clubId, $clubDefaultLang, $translator, $clubObj = false, $currCatId = '', $terminologyService = false, $container = false)
    {
        $catId = $insertDataArr['category_id'];
        $catObj = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->find($catId);
        if ($catObj->getClub()->getId() == $clubId) {
            if ($catId != $currCatId) {
                $sortOrder = $this->getServiceMaxSortorder($catId);
                $insertDataArr['sort_order'] = $sortOrder + 1;
            }
            $logArray = array();
            if (!$clubObj) {
                $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            }
            $serviceObj = new \Common\UtilityBundle\Entity\FgSmServices();
            $serviceObj->setClub($clubObj);
            $serviceObj->setCategory($catObj);
            $logArray[] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_CATEGORY'), 'value_before' => '-', 'value_after' => $catObj->getTitle());
            if (isset($insertDataArr['i18n'])) {
                $logData = $this->addTitleAndDescription($serviceObj, $insertDataArr, $clubDefaultLang, $translator);
                $logArray = array_merge($logArray, $logData);
            }
            if (isset($insertDataArr['service_type'])) {
                $serviceObj->setServiceType($insertDataArr['service_type']);
                $serviceVal = in_array($insertDataArr['service_type'], array('club', 'team')) ? '%' . $insertDataArr['service_type'] . '%' : $insertDataArr['service_type'];
                $serviceLogVal = ucfirst($translator->trans($serviceVal, array('%club%' => $terminologyService->getTerminology('Club', $container->getParameter('singular')), '%team%' => $terminologyService->getTerminology('Team', $container->getParameter('singular')))));
                $logArray[] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_TYPE'), 'value_before' => '-', 'value_after' => $serviceLogVal);
            }
            if (isset($insertDataArr['payment_plan'])) {
                $logData = $this->addPaymentPlan($serviceObj, $insertDataArr, $translator);
                $logArray = array_merge($logArray, $logData);
            }
            if (isset($insertDataArr['sort_order'])) {
                $serviceObj->setSortOrder($insertDataArr['sort_order']);
            }
            $this->_em->persist($serviceObj);
            $this->_em->flush();
            $newServiceId = $serviceObj->getId();
            if ($newServiceId != '') {
                $this->insertLogArray[$newServiceId] = $logArray;
            }

            return $serviceObj;
        }
    }

    /**
     * Function to set title and decription for a new service.
     *
     * @param object $serviceObj        Service object
     * @param array  $insertDataArr     Array for updating value
     * @param string $clubDefaultLang   Club Default Lang
     * @param object $translator        Translator object
     *
     * @return array $logArray Log data array.
     */
    private function addTitleAndDescription($serviceObj, $insertDataArr, $clubDefaultLang, $translator)
    { 
        $logArray = array();
        if (isset($insertDataArr['i18n'][$clubDefaultLang])) {
             if (isset($insertDataArr['i18n'][$clubDefaultLang]['title'])){
                $title =  stripslashes($insertDataArr['i18n'][$clubDefaultLang]['title']);
                $title = str_replace('"', '', $title);
                $serviceObj->setTitle($title);
                $logArray[] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_TITLE') . ' (' . $clubDefaultLang . ')', 'value_before' => '-', 'value_after' => $title);
            }
            if (isset($insertDataArr['i18n'][$clubDefaultLang]['description'])) {
                if ($insertDataArr['i18n'][$clubDefaultLang]['description'] != '') {
                    $serviceObj->setDescription($insertDataArr['i18n'][$clubDefaultLang]['description']);
                    $logArray[] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_DESCRIPTION') . ' (' . $clubDefaultLang . ')', 'value_before' => '-', 'value_after' => $insertDataArr['i18n'][$clubDefaultLang]['description']);
                }
            }
        }

        return $logArray;
    }

    /**
     * Function to set payment plan for a new service.
     *
     * @param object $serviceObj        Service object
     * @param array  $insertDataArr     Array for updating value
     * @param object $translator        Translator object
     *
     * @return array $logArray Log data array.
     */
    private function addPaymentPlan($serviceObj, $insertDataArr, $translator)
    {
        $logArray = array();
        $paymentPlan = $insertDataArr['payment_plan'];
        $serviceObj->setPaymentPlan($paymentPlan);
        $paymentPlanArray = array('regular' => 'SM_REGULAR', 'custom' => 'SM_CUSTOM', 'none' => 'SM_NONE');
        $logArray[] = array('kind' => 'data', 'field' => $translator->trans('LOG_SERVICE_PAYMENT_PLAN'), 'value_before' => '-', 'value_after' => $translator->trans($paymentPlanArray[$paymentPlan]));
        if (in_array($paymentPlan, array('regular', 'custom'))) {
            $serviceObj->setPrice($insertDataArr['price']);
            if ($paymentPlan == 'regular') {
                $serviceObj->setRepetitionMonths($insertDataArr['repetition_months']);
            }
        }

        return $logArray;
    }

    /**
     * Function to Execute Update Query.
     *
     * @param string $clubDefaultLang Club Default Language.
     * @param object $translator      Translator Object
     * @param int    $clubId          Club Id
     * @param int    $currCatId       Current category id
     * @param object $container       Container Object
     */
    private function executeUpdateQuery($clubDefaultLang, $translator, $clubId, $currCatId, $container)
    {
        if (count($this->updateDataArray)) {
            $sortOrderArray = array();
            foreach ($this->updateDataArray as $serviceId => $serviceData) {
                $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
                if ($serviceObj->getClub()->getId() == $clubId) {
                    if (isset($serviceData['i18n'])) {
                        $this->setTitleAndDescription($serviceId, $serviceObj, $serviceData, $clubDefaultLang, $translator, $container);
                    }
                    if (isset($serviceData['category_id'])) {
                        $sortOrderArray = $this->changeCategory($serviceId, $serviceObj, $serviceData, $currCatId, $clubId, $sortOrderArray, $translator);
                    }
                    if (isset($serviceData['payment_plan'])) {
                        $this->changePaymentPlan($serviceId, $serviceObj, $serviceData, $translator);
                    } else {
                        $this->setPriceAndMonth($serviceId, $serviceObj, $serviceData, $translator);
                    }
                    if (isset($serviceData['sort_order'])) {
                        $serviceObj->setSortOrder($serviceData['sort_order']);
                    }
                    if (isset($serviceData['book_marked'])) {
                        if ($serviceData['book_marked'] == '1') {
                            $this->addBookmarksArray[] = $serviceId;
                        } else {
                            $this->removeBookmarksArray[] = $serviceId;
                        }
                    }
                    $this->_em->persist($serviceObj);
                }
            }
            $this->_em->flush();
            $this->updateMainTable($clubDefaultLang,$clubId);     
        }
    }

    /**
     * Function to change price and repetition month of a service.
     *
     * @param int    $serviceId         Service id
     * @param object $serviceObj        Service object
     * @param array  $serviceData       Array of data to change
     * @param object $translator        Translator object
     */
    private function setPriceAndMonth($serviceId, $serviceObj, $serviceData, $translator)
    {
        if (isset($serviceData['price'])) {
            $serviceObj->setPrice($serviceData['price']);
        }
        if (isset($serviceData['repetition_months'])) {
            $serviceObj->setRepetitionMonths($serviceData['repetition_months']);
        }
    }

    /**
     * Function to change title and description of a service.
     *
     * @param int    $serviceId         Service id
     * @param object $serviceObj        Service object
     * @param array  $serviceData       Array of data to change
     * @param string $clubDefaultLang   Club default language
     * @param object $translator        Translator object
     * @param object $container         Container Object
     */
    private function setTitleAndDescription($serviceId, $serviceObj, $serviceData, $clubDefaultLang, $translator, $container)
    {
        foreach ($serviceData['i18n'] as $lang => $translationArray) {
            if($translationArray['title']!=''){
                $translationArray['title'] = stripslashes($translationArray['title']);
                $translationArray['title'] = str_replace('"', '',$translationArray['title'] );
            }
            if ($lang == $clubDefaultLang) {
                if (!empty($translationArray['title'])) {
                    $this->insertLogArray[$serviceId][] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_TITLE') . ' (' . $clubDefaultLang . ')', 'value_before' => $serviceObj->getTitle(), 'value_after' => $translationArray['title']);
                    $serviceObj->setTitle($translationArray['title']);
                }
                if (isset($translationArray['description'])) {
                    $this->insertLogArray[$serviceId][] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_DESCRIPTION') . ' (' . $clubDefaultLang . ')', 'value_before' => $serviceObj->getDescription(), 'value_after' => $translationArray['description']);
                    $serviceObj->setDescription($translationArray['description']);
                }
            }
            $translationArray['id'] = $serviceId;
            $translationArray['lang'] = $lang;
            $transObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServicesI18n')->updateTranslation($translationArray, $serviceObj, false, $container);
            $this->_em->persist($transObj);
        }
    }

    /**
     * Function to change category of a service.
     *
     * @param int    $serviceId         Service id
     * @param object $serviceObj        Service object
     * @param array  $serviceData       Array of data to change
     * @param int    $currCatId         Current category id
     * @param int    $clubId            Club id
     * @param array  $sortOrderArray    Sort order array of services in category
     * @param object $translator        Translator object
     *
     * @return array $sortOrderArray Sort order array of services in category.
     */
    private function changeCategory($serviceId, $serviceObj, $serviceData, $currCatId, $clubId, $sortOrderArray, $translator)
    {
        if ($serviceData['category_id'] != $currCatId) {
            if (array_key_exists($serviceData['category_id'], $sortOrderArray)) {
                $sortOrder = $sortOrderArray[$serviceData['category_id']];
            } else {
                $sortOrder = $this->getServiceMaxSortorder($serviceData['category_id']);
            }
            $serviceData['sort_order'] = $sortOrder + 1;
            $sortOrderArray[$serviceData['category_id']] = $sortOrder + 1;
        }
        $catObj = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->find($serviceData['category_id']);
        if ($catObj->getClub()->getId() == $clubId) {
            $catObjBefore = $this->_em->getRepository('CommonUtilityBundle:FgSmCategory')->find($serviceObj->getCategory()->getId());
            $serviceObj->setCategory($catObj);
            $serviceObj->setSortOrder($serviceData['sort_order']);
            $this->insertLogArray[$serviceId][] = array('kind' => 'data', 'field' => $translator->trans('SERVICE_CATEGORY'), 'value_before' => $catObjBefore->getTitle(), 'value_after' => $catObj->getTitle());
        }

        return $sortOrderArray;
    }

    /**
     * Function to change payment plan of a service.
     *
     * @param int    $serviceId         Service id
     * @param object $serviceObj        Service object
     * @param array  $serviceData       Array of data to change
     * @param object $translator        Translator object
     */
    private function changePaymentPlan($serviceId, $serviceObj, $serviceData, $translator)
    {
        $paymentPlan = $serviceData['payment_plan'];
        $paymentPlanArray = array('regular' => 'SM_REGULAR', 'custom' => 'SM_CUSTOM', 'none' => 'SM_NONE');
        $this->insertLogArray[$serviceId][] = array('kind' => 'data', 'field' => $translator->trans('LOG_SERVICE_PAYMENT_PLAN'), 'value_before' => $translator->trans($paymentPlanArray[$serviceObj->getPaymentPlan()]), 'value_after' => $translator->trans($paymentPlanArray[$paymentPlan]));
        $serviceObj->setPaymentPlan($paymentPlan);
        if (in_array($paymentPlan, array('regular', 'custom'))) {
            if (isset($serviceData['price'])) {
                $price=str_replace(FgSettings::getDecimalMarker(), '.', $serviceData['price']);
                $serviceObj->setPrice($price);
            }
            if (($paymentPlan == 'regular') && isset($serviceData['repetition_months'])) {
                $serviceObj->setRepetitionMonths($serviceData['repetition_months']);
            } else {
                $serviceObj->setRepetitionMonths(NULL);
            }
        } else {
            $serviceObj->setRepetitionMonths(NULL)->setPrice(NULL);
        }
    }

    /**
     * Function to get maximum sort order of services in a category.
     *
     * @param int $catId Category id
     *
     * @return string Sort order
     */
    public function getServiceMaxSortorder($catId)
    {
        $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->findOneBy(array('category' => $catId), array('sortOrder' => 'DESC'));
        if ($serviceObj){
            return $serviceObj->getSortOrder();
        } else {
            return '0';
        }
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
        $mainFileds = array('title', 'description');
        $i18Fields = array('title_lang', 'description_lang');
        $fieldsList = array('mainTable' => 'fg_sm_services',
            'i18nTable' => 'fg_sm_services_i18n',
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
}
