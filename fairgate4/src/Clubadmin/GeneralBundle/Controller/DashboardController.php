<?php

/**
 * DashboardController
 * This controller is used to handle defaults functionalities in dashboard
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

namespace Clubadmin\GeneralBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;

class DashboardController extends FgController {

    /**
     * This function is used to render the default template
     * @return HTML
     */
    public function indexAction() {   
        $noDataMsg = $this->container->get('translator')->trans('DB_NODATA');
        $bookedModulesDet = $this->bookedModulesDet;
        $club = $this->get('club');
        $userRights = $club->get('allowedRights');
        $person = $this->container->get('translator')->trans('DASHBOARD_PERSON');
        $persons = $this->container->get('translator')->trans('DASHBOARD_PERSONS');
        return $this->render('ClubadminGeneralBundle:Dashboard:index.html.twig', array("noDataMsg" => $noDataMsg, "contactName" => $this->contactNameNoSort, "bookedModulesDet" => $bookedModulesDet, "person" => $person, "persons" => $persons, 'userRights' => $userRights));
    }

    /**
     * Function to get news Feed
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFeedAction() {
        //get the feed url and blog url from setings.yml
        $feed_url = $this->container->getParameter('fgV4_news_rss_feed_url');
        $blog_url = $this->container->getParameter('fgV4_blog_url');
        $content = file_get_contents($feed_url);
        $x = new \SimpleXmlElement($content);
        //get the feed data info
        $feed = $x->channel->item;
        $data = array();
        $rssCount = 0;
        //requirement: latest 5 feed display
        foreach ($feed as $entry) {
            if ($rssCount < 5) {
                $subArray = array();
                $date = date('Y-m-d H:i:s', strtotime($entry->pubDate));
                $subArray['date'] = $this->container->get('club')->formatDate($date,'date');
                $subArray['link'] = (string) $entry->link;
                $subArray['title'] = (string) $entry->title;
                $subArray['blog_url'] = $blog_url;
                $subArray['guid'] = (string) $entry->guid;
                $data[] = $subArray;
            }
            $rssCount = $rssCount + 1;
        }

        return new JsonResponse($data);
    }

    /**
     * Function to find gender percentile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getGenderPercentileAction() {
        $club = $this->get('club');
        $data = array();
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setColumns(array('genderCount'));
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->setCondition();
       // $sWhere = "fg_cm_contact.membership_cat_id IS NOT NULL or fg_cm_contact.membership_cat_id !='' ";
       // echo $this->clubType;
        if($this->clubType=="federation"||$this->clubType=="sub_federation")
        {
          $contactlistClass->addCondition("(fg_cm_contact.is_fed_membership_confirmed='0' or(fg_cm_contact.old_fed_membership_id is not null and fg_cm_contact.is_fed_membership_confirmed='1' ) ) ");
          $sWhere = " (fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='')  " ; 
        }
        else
        {
            $sWhere = " (fg_cm_contact.club_membership_cat_id IS NOT NULL or fg_cm_contact.club_membership_cat_id !='') "
                    . "or (fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='' and fg_cm_contact.is_fed_membership_confirmed=0 )";
                   
        }
        $contactlistClass->addCondition($sWhere);
        $sWhere = "ms.`" . $this->container->getParameter('system_field_gender') . "` IS NOT NULL or ms.`" . $this->container->getParameter('system_field_gender') . "` !=' '";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        //echo "<br>";
        //echo $listquery;die;
        $genderDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        $male = $this->container->get('translator')->trans('CM_MALE');
        $female = $this->container->get('translator')->trans('CM_FEMALE');
        if ($genderDatas[0]['mcount'] != 0 || $genderDatas[0]['fcount'] != 0) {
            $data = array(array('label' => $male, "data" => $genderDatas[0]['mcount']),
                array("label" => $female, "data" => $genderDatas[0]['fcount']));
        }

        return new JsonResponse($data);
    }

    /**
     * Function to find membership percentile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getMembershipPercentileAction() {
        $club = $this->get('club');
        //active membership cat id
        $membershipDataCount = $this->membershipDataCount($club);
        $nonMembershipDataCount = $this->nonMembershipDataCount($club);
        foreach ($membershipDataCount as $value) {
            $dataMembershipData[$value['membershipTitle']] = $value['membershipCount'];
        }
        arsort($dataMembershipData);
        $data = $this->getMembershipPercentileArray($dataMembershipData, $nonMembershipDataCount, 'federation');

        return new JsonResponse($data);
    }

     /**
     * Function to find membership percentile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getClubMembershipPercentileAction() {
        //echo $this->clubType;
        if($this->clubType=="sub_federation_club"|| $this->clubType=="federation_club" || $this->clubType=="standard_club" )
        {
            $club = $this->get('club');
            //active membership cat id
            $membershipDataCount = $this->clubmembershipDataCount($club);
            $nonMembershipDataCount = $this->noclubMembershipDataCount($club);
            foreach ($membershipDataCount as $value) {
                $dataMembershipData[$value['membershipTitle']] = $value['clubmembershipCatIdCount'];
            }
            arsort($dataMembershipData);
            $data = $this->getMembershipPercentileArray($dataMembershipData, $nonMembershipDataCount, 'club');
        }
        else {
            $data = array();
        }
        return new JsonResponse($data);
    }
    
    
    /**
     * function to get array of label and data of membership percentile for drawing pie chart
     * @param $dataMembershipData membership data
     * @param $nonMembershipDataCount non-membership data count
     * @param $type type of memebrship
     * @return Array
     */
    private function getMembershipPercentileArray($dataMembershipData, $nonMembershipDataCount, $type) {
        $data = array();
        $terminologyService = $this->get('fairgate_terminology_service');
        $membershipCount = count($dataMembershipData);
        $limit = 0;
        $other = 0;
        foreach ($dataMembershipData as $key => $value) {
            if (($limit < 5) && ($limit <= $membershipCount - 1)) {
                array_push($data, array('label' => $key, "data" => $value));
            } else {
                $other = $other + $value;
            }
            $limit = $limit + 1;
        }
        if ($other != 0) {
            array_push($data, array('label' => $this->container->get('translator')->trans('DB_OTHER'), "data" => $other));
        }
        if ($nonMembershipDataCount) {
            $title = ($type=='federation') ? $this->container->get('translator')->trans('DB_WITHOUT_FEDMEMBSERSHIP',array('%fed_membership%' => $terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')))) : $this->container->get('translator')->trans('DB_WITHOUT_CLUBMEMBSERSHIP');
            $nonFedMembership = $nonMembershipDataCount[0]['isCompany']+$nonMembershipDataCount[0]['isPersonal'];
            array_push($data, array('label' => $title, "data" => (string)$nonFedMembership));
        }

        return $data;
    }
    
    private function  clubmembershipDataCount($club)
    {
        //echo 123;
        //echo "<br>";
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setColumns(array('isMemberTitle', 'clubmembershipCatIdCount')); //
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->addJoin(" LEFT JOIN fg_cm_membership on fg_cm_contact.club_membership_cat_id= fg_cm_membership.id");
        $contactlistClass->setCondition();
        $sWhere = "(fg_cm_contact.club_membership_cat_id IS NOT NULL or fg_cm_contact.club_membership_cat_id !='')  ";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->setGroupBy(" fg_cm_contact.club_membership_cat_id");
        $listquery = $contactlistClass->getResult();
        //echo $listquery;die;
        $membershipDataCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        return $membershipDataCount;
        
    }
    
    
    /**
     * function to find the nonmembership personal,company count
     * @param type $club
     * @return type
     */
    private function noclubMembershipDataCount($club) {
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setColumns(array('CompanyPersonalCount')); //
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->setCondition();
        $sWhere = "fg_cm_contact.club_membership_cat_id IS NULL or fg_cm_contact.club_membership_cat_id ='' ";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->setGroupBy(" fg_cm_contact.club_membership_cat_id");
        $listquery = $contactlistClass->getResult();
        $nonMembershipDataCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        return $nonMembershipDataCount;
    }
    
    /**
     * function to find membership data count
     * @param type $club
     * @return type
     */
    private function membershipDataCount($club) {
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setColumns(array('isMemberTitle', 'membershipCatIdCount')); //
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->addJoin(" LEFT JOIN fg_cm_membership on fg_cm_contact.fed_membership_cat_id= fg_cm_membership.id");
        $contactlistClass->setCondition();
        $sWhere = "(fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='' )and (fg_cm_contact.is_fed_membership_confirmed='0' or(fg_cm_contact.old_fed_membership_id is not null and fg_cm_contact.is_fed_membership_confirmed='1' ) ) ";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->setGroupBy(" fg_cm_contact.fed_membership_cat_id");
        $listquery = $contactlistClass->getResult();
       //echo $listquery;die;
        $membershipDataCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        return $membershipDataCount;
    }

    /**
     * function to find the nonmembership personal,company count
     * @param type $club
     * @return type
     */
    private function nonMembershipDataCount($club) {
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setColumns(array('CompanyPersonalCount')); //
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->setCondition();
        $sWhere = "fg_cm_contact.fed_membership_cat_id IS NULL or fg_cm_contact.fed_membership_cat_id ='' ";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->setGroupBy(" fg_cm_contact.fed_membership_cat_id");
        $listquery = $contactlistClass->getResult();
        $nonMembershipDataCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        return $nonMembershipDataCount;
    }

    /**
     * function to get origin percentile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOriginPercentileAction() {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setColumns(array('originCount', 'corrsCity')); //
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->setCondition();
        if($this->clubType=="federation"||$this->clubType=="sub_federation")
        {
          $contactlistClass->addCondition("(fg_cm_contact.is_fed_membership_confirmed='0' or(fg_cm_contact.old_fed_membership_id is not null and fg_cm_contact.is_fed_membership_confirmed='1' ) )");
          $sWhere = " (fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='')  " ; 
        }
        else
        {
            $sWhere = " (fg_cm_contact.club_membership_cat_id IS NOT NULL or fg_cm_contact.club_membership_cat_id !='') "
                    . "or (fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='' and fg_cm_contact.is_fed_membership_confirmed=0 )";
                   
        }
        
        //$sWhere = "fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='' ";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->setGroupBy(" ms.`" . $this->container->getParameter('system_field_corres_ort') . "` ");
        $listquery = $contactlistClass->getResult(); 
         // echo $listquery;die;
        $originCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        $data = $this->calculateOrigin($originCount);

        return new JsonResponse($data);
    }

    /**
     * get count of active membership
     * @return type
     */
    private function getActiveMembershipCount() {
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, $contactType = 'contact');
        $contactlistClass->setCount(); //
        $contactlistClass->setFrom($this->clubType);
        $contactlistClass->setCondition();
        if($this->clubType=="federation"||$this->clubType=="sub_federation")
        {
          $contactlistClass->addCondition("(fg_cm_contact.is_fed_membership_confirmed='0' or(fg_cm_contact.old_fed_membership_id is not null and fg_cm_contact.is_fed_membership_confirmed='1' ) ) ");
          $sWhere = " (fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='')  " ; 
        }
        else
        {
            $sWhere = " (fg_cm_contact.club_membership_cat_id IS NOT NULL or fg_cm_contact.club_membership_cat_id !='') "
                    . "or (fg_cm_contact.fed_membership_cat_id IS NOT NULL or fg_cm_contact.fed_membership_cat_id !='' and fg_cm_contact.is_fed_membership_confirmed=0 )";
                   
        }
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $membershipCount = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
        return $membershipCount[0]['count'];
    }

    /**
     * function to calculate origin
     * @param type $originCount
     * @return array
     */
    private function calculateOrigin($originCount) {
        $activeMembershipCount = $this->getActiveMembershipCount();
        $data = array();
        $other = 0;
        $otherLabel = $this->container->get('translator')->trans('DB_OTHER');
        $notSpecified = $this->container->get('translator')->trans('DB_NOTSPECIFIED');
        foreach ($originCount as $key => $value) {
            $dataOrigin[$value['city']] = $value['originCount'];
        }
        arsort($dataOrigin);
        $cityCount = count($dataOrigin);
        $limit = -1;
        $countCity = 0;
       
        foreach ($dataOrigin as $key => $value) {
            if ($key != '') {
                if (($limit < 5) && ($limit < $cityCount - 1)) {
                    array_push($data, array('label' => $key, "data" => $value));
                } else {
                    $other = $other + $value;
                }
                $countCity += $value;
                $limit = $limit + 1;
            }
        }
       // print_r($data);
        if ($other != 0) {
            array_push($data, array('label' => $otherLabel, "data" => $other));
        }
       
        if ($countCity > 0) {
            
            array_push($data, array('label' => $notSpecified, "data" => $activeMembershipCount - $countCity));
        }
       
         
        return $data;
    }

    /*
     * Function for getting newsletter datas in dashboard for stacked flot chart
     * @return Json array
     */

    public function getNewsletterStackedChartAction() {
        $return = array();
        $bookedModulesDet = $this->bookedModulesDet;
        if (in_array("communication", $bookedModulesDet)) {
            $em = $this->getDoctrine()->getManager();
            $results = $em->getRepository('CommonUtilityBundle:FgCnNewsletterLog')
                    ->getNewsletterRecipientsAndOpenings($this->clubId);
            $return = $this->getJsonResult($results);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        return new JsonResponse($return);
    }

    /*
     * Function for getting simplemail datas in dashboard for stacked flot chart
     * @return Json array
     */
    public function getSimplemailStackedChartAction() {
        $return = array();
        $bookedModulesDet = $this->bookedModulesDet;
        if (in_array("communication", $bookedModulesDet)) {
            $em = $this->getDoctrine()->getManager();
            $results = $em->getRepository('CommonUtilityBundle:FgCnNewsletterLog')
                    ->getSimplemailRecipientsAndOpenings($this->clubId);
            $return = $this->getJsonResult($results);
            return new Response($return, 200, array('Content-Type' => 'application/json'));
        }

        return new JsonResponse($return);
    }

    /*
     * Function for getting the next 7 bithday details of active contacts
     * @return Json array
     */
    public function getNextBirthdaysAction() {
        $contactPdo = new ContactPdo($this->container);
        $nextBirthDays = $contactPdo->getNextBirthDaysFromContactListBackend($this->container);
        
        $textShowAll = $this->get('translator')->trans('DASHBOARD_SHOW_ALL');
        $textShowLess = $this->get('translator')->trans('DASHBOARD_SHOW_LESS');
        $return = array("birthdayDetails" => $nextBirthDays, "textShowAll" => $textShowAll, "textShowLess" => $textShowLess );

        return new JsonResponse($return);
    }

    /*
     * Function to return json data from result array
     * param $results  Result array from db
     * $return  Json data
     */
    public function getJsonResult($results) {      
        $club = $this->get('club');
        if (is_array($results)) {
            $count = 0;
            $notOpenedDatas = array();
            $openingsDatas = array();
            $newsletterDates = array();
            for ($i = (count($results) - 1); $i >= 0; $i--) {
                $result = $results[$i];
                array_push($notOpenedDatas, array($count, ($result['recepients'] - $result['openings'])));
                array_push($openingsDatas, array($count, $result['openings']));
                $sentdate = $result['sentdate']->format($club->get('phpdate'));
                array_push($newsletterDates, array($count, $sentdate));
                $count++;
            }
        }
        $numberOpenings = $this->get('translator')->trans('DASHBOARD_NUMBER_OPENINGS');
        $numberRecipients = $this->get('translator')->trans('DASHBOARD_NUMBER_NOT_OPENED');
        $returnArray = array("stackedData" => array(array("label" => $numberOpenings, "data" => $openingsDatas),
                array("label" => $numberRecipients, "data" => $notOpenedDatas)),
            "xTicks" => array("ticks" => $newsletterDates));

        return json_encode($returnArray);
    }

    

    /**
     * function to find year of birth percentile
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getYearOfBirthPercentileAction() {
        $club = $this->get('club');
        $container = $this->container;
        $contactlistClass = new Contactlist($container, '', $club);
        $result = $this->getBirthdayYears($contactlistClass, $container->getParameter('system_field_dob'));
        $birthdayYears = $result['birthYearArray'];
        $returnArray = array("barData" => $birthdayYears);

        return new JsonResponse($returnArray);
    }

    /**
     * function to get birth year
     * @param type $contactlistClassObj
     * @param type $dob
     * @return type
     */
    public function getBirthdayYears($contactlistClassObj, $dob) {
        $resultData = $this->getYOBCount($contactlistClassObj, $dob);
        $birthYearArray = array();
        $lastYear = $resultData[0]['birthyear'];
        $firstYear = $resultData[(count($resultData) - 1)]['birthyear'];
        $yearRangeFlag = (($lastYear - $firstYear) > 100) ? 1 : 0;
        if ($resultData) {
            foreach ($resultData as $result) {
                $birthYearArray[$result['birthyear']] = $result['birthCounts'];
            }
        }
        if ($lastYear > $firstYear) {
            for ($i = $lastYear; $i >= $firstYear; $i--) {
                $birthYearArray[$i] = ($birthYearArray[$i]) ? $birthYearArray[$i] : 0;
            }
        }
        krsort($birthYearArray);

        return array('birthYearArray' => $birthYearArray, 'yearRangeFlag' => $yearRangeFlag);
    }

    /**
     * function to return array of active
     * @param type $contactlistClassObj contact obj
     * @param type $dob dobfield
     * @param type $yearRangeMin min year
     * @param type $yearRangeMax max year
     * @return array
     */
    public function getYOBCount($contactlistClassObj, $dob, $yearRangeMin = "", $yearRangeMax = "") {
        $contactlistClass = $contactlistClassObj;
        $contactlistClass->setColumns(array("DATE_FORMAT(`" . $dob . "`,'%Y') as birthyear, count(`" . $dob . "`) as birthCounts"));
        $contactlistClass->setFrom();
        $contactlistClass->setCondition();
        if ($yearRangeMin && $yearRangeMax) {
            $contactlistClass->addCondition(" DATE_FORMAT(`" . $dob . "`,'%Y') >= $yearRangeMin AND DATE_FORMAT(`" . $dob . "`,'%Y') <= $yearRangeMax");
        } else {
            $contactlistClass->addCondition("`" . $dob . "` IS NOT NULL AND DATE(`" . $dob . "`) != '0000-00-00' ");
        }
        $contactlistClass->addCondition('fg_cm_contact.is_company = 0');
        $contactlistClass->setGroupBy('birthyear');
        if ($yearRangeMin && $yearRangeMax) {
            $contactlistClass->groupBy = "";
        }
        $contactlistClass->addOrderBy('birthyear DESC');
        $listquery = $contactlistClass->getResult();
        $result = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);

        return $result;
    }

}
