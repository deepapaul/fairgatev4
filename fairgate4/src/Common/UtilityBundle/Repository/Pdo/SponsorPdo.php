<?php

namespace Common\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * Used to handling different sponsor functions.
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 */
class SponsorPdo {

    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container) {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * Method to get details of all services of a particular club.
     *
     * @param int $clubId          current clubId
     * @param int $clubDefaultLang club language
     *
     * @return array
     */
    public function getSponsorsServices($clubId, $clubDefaultLang) {
        $query = "SELECT S.id as serviceId, IF( (SL.title_lang IS NULL OR SL.title_lang = '' ), S.title, SL.title_lang) as serviceTitle FROM fg_sm_services S LEFT JOIN fg_sm_services_i18n SL "
                . "ON SL.id = S.id and SL.lang = '$clubDefaultLang' WHERE S.club_id = $clubId  GROUP BY S.id ORDER BY serviceTitle ASC";
        $result = $this->conn->fetchAll($query);

        return $result;
    }

    /**
     * Function for updating the translation entry of a service.
     *
     * @param array $translationArray Translation data array for updating
     */
    public function updateServiceTranslation($translationArray) {
        $serviceId = $translationArray['id'];
        $lang = $translationArray['lang'];
        $title = isset($translationArray['title']) ? FgUtility::getSecuredData(stripslashes($translationArray['title']), $this->conn) : '';
       // $description = isset($translationArray['description']) ? FgUtility::getSecuredData(stripslashes($translationArray['description']), $this->conn) : '';
		$description = $translationArray['description'];
        $description = FgUtility::getSecuredDataString($description, $this->conn);
        $setString = "";
        $title = trim($title);
        if(!empty($title))
         $setString   .= " title_lang='$title' " ;
        if(!empty($title)&&!empty($description))
          $setString   .= " , ";  
        if(!empty($description))
         $setString   .= " description_lang='$description' " ;
        $setString = trim($setString);
        if(!empty($setString)){
            $sql = "UPDATE fg_sm_services_i18n set $setString WHERE id = '$serviceId' AND lang = '$lang'";
            $this->conn->executeQuery($sql);
        }
    }

    /**
     * Function for inserting the translation entries of a service.
     *
     * @param int    $serviceId       Service id
     * @param array  $translationData Translation data to be inserted
     * @param string $clubDefaultLang Club default language
     */
    public function insertServiceTranslation($serviceId, $translationData, $clubDefaultLang) {
        $defaultTitle = $translationData[$clubDefaultLang]['title'];
        $defaultDescription = $translationData[$clubDefaultLang]['description'];
        $insertQry = '';
        foreach ($translationData as $lang => $translationArray) {
            $translationArray['id'] = $serviceId;
            $translationArray['lang'] = $lang;
            $insertValues = "('$serviceId', '$lang', '" . FgUtility::getSecuredData(stripslashes($translationArray['title']), $this->conn) . "', '" . FgUtility::getSecuredData($translationArray['description'], $this->conn) . "', '1')";
            $insertQry .= ($insertQry == '') ? "INSERT INTO fg_sm_services_i18n (`id`,`lang`,`title_lang`,`description_lang`,`is_active`) VALUES $insertValues" : ",$insertValues";
        }
        if ($insertQry != '') {
            $this->conn->executeQuery($insertQry);
        }
    }

    /**
     * Function to check whether a contact is a sponsor or not.
     *
     * @param int    $contactId   Contact id
     * @param string $masterTable Master table name
     *
     * @return bool true/false   Whether the contact is sponsor or not.
     */
    public function isProspect($contactId, $masterTable, $clubObj) {
        $clubType = $clubObj->get("type");
        $clubId = $clubObj->get("id");
        switch ($clubType) {
            case 'federation':
                $this->from = " master_federation_$clubId as mc INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                $mainfgcontactIdField = 'mc.fed_contact_id';
                break;
            case 'sub_federation':
                $this->from = "master_federation_$clubId as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
                $mainfgcontactIdField = 'fg_cm_contact.subfed_contact_id';
                break;
            default:
                $this->from = "master_club_$clubId as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.id";
        }

        $sql = "SELECT fg_cm_contact.is_sponsor from {$this->from}"
                . " LEFT JOIN fg_sm_bookings ON (fg_sm_bookings.contact_id = fg_cm_contact.id AND fg_sm_bookings.is_deleted = 0 ) "
                . " WHERE fg_sm_bookings.contact_id IS NULL AND fg_cm_contact.id = $contactId AND fg_cm_contact.is_sponsor = 1 ";
        $result = $this->conn->fetchAll($sql);
        if (count($result) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to get service assignments of a contact.
     *
     * @param int $clubId  Club id
     * @param int $contact Contact id
     *
     * @return array $resultArray   Array of assignments.
     */
    public function getAllServiceAssignments($clubId, $contact) {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $resultArray = $this->conn->fetchAll("SELECT sc.id as catId,ss.id as serviceId,sb.id as bookingId,"
                . " sc.is_system,sc.title as catTitle, ss.title as serviceTitle, sb.payment_plan,DATE_FORMAT(sb.first_payment_date, '$dateFormat') AS firstPaymentDate, DATE_FORMAT(sb.last_payment_date, '$dateFormat') AS lastPaymentdate,"
                . " DATE_FORMAT(sb.begin_date, '$dateFormat') AS beginDate, DATE_FORMAT(sb.end_date, '$dateFormat') AS endDate,"
                . " IF(sb.payment_plan = 'regular' AND (sb.last_payment_date IS NULL OR sb.last_payment_date=''), getPaymentAmount(sb.amount,sb.discount_type,sb.discount), getTotalServiceAmount(sb.id,sb.first_payment_date,sb.last_payment_date)) AS finalAmount,"
                . " sb.begin_date,sb.end_date,sb.repetition_months,"
                . " getPaymentAmount(sb.amount,sb.discount_type,sb.discount) as amount,sb.discount_type,sb.discount"
                . " FROM fg_sm_category sc"
                . " LEFT JOIN fg_sm_services ss ON ss.club_id=$clubId AND ss.category_id=sc.id"
                . " LEFT JOIN fg_sm_bookings sb ON sb.category_id=sc.id AND sb.service_id=ss.id AND sb.club_id=$clubId AND sb.is_deleted = 0 AND sb.contact_id=$contact"
                . " WHERE sc.club_id=$clubId AND sb.contact_id=$contact "
                . " AND ( (sb.begin_date <= NOW() AND (sb.end_date >= NOW() OR sb.end_date IS NULL OR sb.end_date='' )) OR (sb.begin_date > NOW() AND (sb.end_date > NOW() OR sb.end_date IS NULL OR sb.end_date='' )) ) ");

        return $resultArray;
    }

    /**
     * Method to get ads of sponsor 
     *
     * @param int $adArea               ad area id
     * @param string $sponsors          comma separated sponsor ids
     * @param string $width             width of ad
     * @param int $clubId               current club Id
     *
     * @return array
     */
    public function getSponsorAds($adArea, $sponsors, $clubId) {

        $sql = "SELECT SA.image AS image, SA.url AS url, SA.is_default AS isDefault, C.id AS contact, SA.sort_order AS sortOrder "
                . "FROM fg_sm_sponsor_ads SA LEFT JOIN fg_sm_ad_area AD ON (SA.ad_area_id = AD.id) "
                . "INNER JOIN fg_cm_contact C ON (SA.contact_id = C.id) "
                . "WHERE C.id IN ($sponsors) "
                . "AND C.is_permanent_delete =0 AND C.is_sponsor = 1  "
                . "AND C.is_deleted=0  AND C.club_id=$clubId AND (C.main_club_id=$clubId OR C.fed_membership_cat_id IS NOT NULL) "
                . "AND C.is_draft=0 "
                . "AND SA.club_id = $clubId "
                . "AND (SA.ad_area_id = '$adArea' OR SA.ad_area_id IS NULL) "
                . "AND SA.sort_order IN (SELECT MAX(`sort_order`) FROM fg_sm_sponsor_ads SS WHERE SS.contact_id = SA.contact_id AND (SS.ad_area_id = '$adArea' OR SS.ad_area_id IS NULL) AND SA.club_id = $clubId  ) ";
        $resultArray = $this->conn->fetchAll($sql);

        return $resultArray;
    }

    /**
     * function to list the sponsor analysis service
     * @param date $startDate start Date
     * @param date $endDate end Date
     * @param int $clubId club Id
     * 
     * @return array
     */
    public function listSponsorService($startDate, $endDate, $clubId) {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $sql = "SELECT c.title as category,s.title as service, bo.category_id as catId, c.sort_order as cOrder, s.sort_order as sOrder,  "
                . "SUM(IF(p.amount IS NOT NULL,getPaymentAmount(p.amount,p.discount_type,p.discount),0)) AS amt,"
                . "GROUP_CONCAT(concat(contactName(bo.contact_id),' (',DATE_FORMAT(bo.begin_date,'$dateFormat'),' - ',IF((bo.end_date IS NULL),'...',DATE_FORMAT(bo.end_date,'$dateFormat')),')') order by contactName(bo.contact_id) SEPARATOR ' <br/> ') as sponsorDetails, "
                . "count(p.amount) as payments,count(DISTINCT(bo.contact_id)) as sponsors ,bo.begin_date,bo.end_date, "
                . "GROUP_CONCAT(concat('{\"date\":\"',DATE_FORMAT(p.date,'$dateFormat'),'\",\"amount\":', getPaymentAmount(p.amount,p.discount_type,p.discount),',\"name\": \"(',contactName(bo.contact_id),')\"}') order by p.date SEPARATOR ',') as paymentDetails "
                . "FROM fg_sm_services s "
                . "LEFT JOIN fg_sm_category c ON c.id = s.category_id "
                . "LEFT JOIN fg_sm_bookings bo ON bo.service_id = s.id "
                . "LEFT JOIN fg_sm_paymentplans p ON bo.id = p.booking_id AND (p.date >= '{$startDate}' AND p.date <='{$endDate}')"
                . " INNER JOIN fg_cm_contact co ON co.id=bo.contact_id "
                . "WHERE bo.club_id={$clubId} AND co.is_sponsor = 1 AND "
                . " bo.begin_date<= '{$endDate}' AND"
                . " (bo.end_date >= '{$startDate}' OR bo.end_date IS NULL  ) "
                . " AND (bo.begin_date <= now() AND (bo.end_date>= now() OR bo.end_date IS NULL )) "
                . "AND bo.is_deleted = 0 AND co.is_deleted = 0 AND co.is_permanent_delete =0 GROUP BY s.id ORDER BY c.sort_order,s.sort_order,sponsorDetails ASC";

        $result = $this->conn->executeQuery($sql)->fetchAll();
        return array('service' => $result);
    }

    /**
     * Function to get the sponsor count
     * @param  int $clubId clubId
     * @param  string $clubtype clubType
     * @param  array $typeArray array of type of sponsor whose count needs to be calculated
     * 
     * @return json array sponsor sidebar count array
     */
    public function getSidebarCount($clubId, $clubtype, $typeArray = array()) {

        $subquery = $this->subQuerySponsorType($clubId);
        $condition = $this->sidebarCountFromWhere($clubId, $clubtype);

        $masterField = ($clubtype == 'federation' ) ? "fed_contact_id" : 'contact_id';

        $filters['single_person'] = "(select count(DISTINCT(fg_cm_contact.id)) FROM {$condition['from']} {$condition['where']} AND fg_cm_contact.is_company=0 ) as single_person";
        $filters['company'] = "(select count(DISTINCT(fg_cm_contact.id)) FROM {$condition['from']} {$condition['where']} AND fg_cm_contact.is_company=1  ) as company";
        $filters['former_sponsor'] = "SUM((SELECT count(DISTINCT(BT.id)) FROM fg_cm_contact AS BT INNER JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.club_id=" . $clubId . " WHERE SB.contact_id=fg_cm_contact.id AND {$subquery} = 'former_sponsor' )) AS former_sponsor";
        $filters['active_sponsor'] = "SUM((SELECT count(DISTINCT(BT.id)) FROM fg_cm_contact AS BT INNER JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.club_id=" . $clubId . " WHERE SB.contact_id=fg_cm_contact.id AND {$subquery} = 'active_sponsor')) AS active_sponsor";
        $filters['future_sponsor'] = "SUM((SELECT count(DISTINCT(BT.id)) FROM fg_cm_contact AS BT INNER JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.club_id=" . $clubId . " WHERE SB.contact_id = fg_cm_contact.id AND {$subquery} = 'future_sponsor')) AS future_sponsor";
        $filters['prospect'] = "SUM(IF((select count(fg_sm_bookings.contact_id) from fg_sm_bookings where fg_sm_bookings.contact_id=fg_cm_contact.id AND fg_sm_bookings.is_deleted = 0 AND fg_cm_contact.club_id=" . $clubId . " )= 0,1,0)) as prospect ";

        if (!empty($typeArray)) {
            $subQ = '';
            foreach ($typeArray as $key => $value) {
                $subQ = $subQ . $filters[$value] . ",";
            }
            $subQ = rtrim($subQ, ',');
            $countQuery = "SELECT  {$subQ} FROM {$condition['from']} {$condition['where']} ";
        } else {
            $countQuery = "SELECT  {$filters['company']} , {$filters['single_person']} , {$filters['prospect']} , {$filters['active_sponsor']} , {$filters['future_sponsor']} ,{$filters['former_sponsor']} "
                    . " FROM {$condition['from']} {$condition['where']} ";
        }
        file_put_contents('q1.txt', $countQuery);
        $result = $this->conn->executeQuery($countQuery)->fetchAll();

        return $result;
    }

    /**
     * sub Query condition to find SponsorType for sidebar count
     * @param  int $clubId clubId
     * 
     * @return string subquery
     */
    private function subQuerySponsorType($clubId) {

        $subquery = " IF (fg_cm_contact.is_sponsor = 1,"
                . " IF((select count(fg_sm_bookings.contact_id) from fg_sm_bookings where fg_sm_bookings.contact_id=fg_cm_contact.id AND fg_sm_bookings.is_deleted = 0 )= 0,"
                . "'prospect',"
                . "(Select CASE"
                . " WHEN (fg_sm_bookings.begin_date > now() AND fg_sm_bookings.is_deleted = 0 AND fg_sm_bookings.club_id={$clubId}) THEN 'future_sponsor' "
                . " WHEN (fg_sm_bookings.begin_date <= now() AND fg_sm_bookings.is_deleted = 0 AND (fg_sm_bookings.end_date >= now() OR fg_sm_bookings.end_date IS NULL) AND fg_sm_bookings.club_id={$clubId}) THEN 'active_sponsor'"
                . " WHEN (fg_sm_bookings.end_date < now() AND fg_sm_bookings.is_deleted = 0 AND fg_sm_bookings.club_id={$clubId}) THEN 'former_sponsor' END AS Gsponsor FROM fg_sm_bookings WHERE fg_sm_bookings.contact_id=fg_cm_contact.id "
                . " ORDER BY  (CASE WHEN  Gsponsor='active_sponsor' then 1 WHEN Gsponsor='future_sponsor' THEN 2 WHEN Gsponsor='former_sponsor' then 3 ELSE 4 END) asc limit 0,1) ),"
                . "'' ) ";

        return $subquery;
    }

    /**
     * Outer wrapper condition subquery for sidebar count
     * @param  int    $clubId
     * @param  string $clubtype
     * 
     * @return string subquery fromand where wrapper condition
     */
    private function sidebarCountFromWhere($clubId, $clubtype) {
        $permenantDelete = "fg_cm_contact.is_deleted=0 AND fg_cm_contact.is_permanent_delete =0";
        $archiveCondition = "fg_cm_contact.is_sponsor = 1";
        switch ($clubtype) {
            case 'federation':
                $from = " master_federation_{$clubId} AS mc INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                $where = "WHERE {$permenantDelete} AND {$archiveCondition} AND fg_cm_contact.club_id = '{$clubId}' AND (fg_cm_contact.main_club_id = '{$clubId}' OR fg_cm_contact.fed_membership_cat_id IS NOT NULL  ) AND fg_cm_contact.is_fed_membership_confirmed='0'  AND fg_cm_contact.is_draft=0";
                break;
            case 'sub_federation':
                $from = "master_federation_{$clubId} AS mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
                $where = "WHERE {$permenantDelete} AND {$archiveCondition} AND fg_cm_contact.club_id = '{$clubId}' AND (fg_cm_contact.main_club_id = '{$clubId}' OR fg_cm_contact.fed_membership_cat_id IS NOT NULL ) AND fg_cm_contact.is_fed_membership_confirmed='0'  AND fg_cm_contact.is_draft=0";
                break;
            default:
                $where = "WHERE {$permenantDelete} AND {$archiveCondition} AND fg_cm_contact.club_id={$clubId}";
                $from = "master_club_{$clubId} AS mc INNER JOIN fg_cm_contact ON mc.contact_id = fg_cm_contact.id";
        }
        $from.= " INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id";
        return array('from' => $from, 'where' => $where);
    }

    /**
     * function to sponsor analysis sponsor listing
     * @param date $startDate startDate
     * @param date $endDate endDate
     * @param int $clubId clubId
     * 
     * @return array
     */
    public function sponsorAnalysisListing($startDate, $endDate, $clubId, $masterTable) {
        $active = '(bo.begin_date <= now() AND (bo.end_date>= now() OR bo.end_date IS NULL ) ';
        $former = 'OR bo.end_date <= now())';
        $sql = "SELECT co.is_company as company, bo.contact_id as contactId, c.title as category,s.title as service,s.id as serviceId, bo.category_id as catId,contactName(bo.contact_id) as contactName, "
                . "SUM(IF(p.amount IS NOT NULL,getPaymentAmount(p.amount,p.discount_type,p.discount),0)) AS amt, "
                . "IF(p.amount IS NOT NULL,GROUP_CONCAT(getPaymentAmount(p.amount,p.discount_type,p.discount)),0) as payment, "
                . "bo.begin_date,bo.end_date ,bo.service_id as serviceId,bo.contact_id as contactId "
                . "FROM fg_sm_services s "
                . "LEFT JOIN fg_sm_category c ON c.id = s.category_id "
                . "LEFT JOIN fg_sm_bookings bo ON bo.service_id = s.id "
                . "LEFT JOIN fg_sm_paymentplans p ON bo.id = p.booking_id AND (p.date >= '{$startDate}' AND p.date <='{$endDate}') "
                . " INNER JOIN fg_cm_contact co ON co.id=bo.contact_id "
                . " WHERE bo.club_id={$clubId} AND co.is_sponsor = 1 AND ("
                . " bo.begin_date<= '{$endDate}' AND"
                . " (bo.end_date >= '{$startDate}' OR bo.end_date IS NULL  ) AND "
                . "case when {$startDate} <= now() then {$active} {$former} else {$active})  end) "
                . "AND bo.is_deleted = 0 AND co.is_deleted = 0 AND co.is_permanent_delete =0 GROUP BY bo.contact_id,bo.service_id";

        $result = $this->conn->executeQuery($sql)->fetchAll();

        return array('sponsor' => $result);
    }

    /**
     * Function to get count of sponsor service -active assignments - sidebar/bookmark
     * @param  int   $clubId club Id
     * @param  array $serviceIds aray of service ids whose count needs to be found
     * 
     * @return array array with categoryId,serviceId,count of active assignments
     */
    public function sponsorServiceCount($clubId, $masterTable, $serviceIds = array(), $clubType) {
        $masterField = ($clubType == 'federation' ) ? "fed_contact_id" : 'contact_id';
        if ($clubType == 'federation') {
            $joiningField = "cc.fed_contact_id"; //fed_contact_id
        } else if ($clubType == 'sub_federation') {
            $joiningField = "cc.subfed_contact_id";
        } else {
            $joiningField = "cc.id"; //fed_contact_id
        }
        $sql = "SELECT DISTINCT(s.id) AS serviceId, c.id AS catId, b.id, "
                . "case when COUNT(b.id) is not null then COUNT(b.id) else 0 end as cnt "
                . "FROM fg_sm_category c "
                . "INNER JOIN fg_sm_services s ON s.category_id = c.id "
                . "LEFT JOIN fg_sm_bookings b ON b.service_id = s.id AND b.category_id = c.id AND b.is_deleted = 0 "
                . "AND b.begin_date <= now() AND (b.end_date >= now() OR b.end_date IS NULL) "
                . "AND b.club_id= $clubId "
                . " INNER JOIN fg_cm_contact cc ON cc.id=b.contact_id"
                . " INNER JOIN $masterTable mc ON  mc.$masterField= $joiningField"
                . " INNER JOIN master_system ms ON  ms.fed_contact_id = cc.fed_contact_id"
                . " WHERE c.club_id= $clubId AND cc.is_sponsor = 1 AND cc.is_deleted = 0 AND cc.is_permanent_delete =0";

        if (count($serviceIds) > 0) {
            $serviceIds = implode(',', $serviceIds);
            $sql .= " AND s.id IN ($serviceIds) GROUP BY s.id ";
            $dataResult = $this->conn->executeQuery($sql)->fetchAll();
            $result = array();
            foreach ($dataResult as $key => $value) {
                $result[$value['serviceId']] = $value['cnt'];
            }
            return $result;
        } else {
            $sql .= " GROUP BY s.id ";
            $dataResult = $this->conn->executeQuery($sql)->fetchAll();
            return $dataResult;
        }
    }

    /**
     * function for sponsor analysis sponsor pdf data
     * @param  date $startDate startDate
     * @param  date $endDate endDate
     * @param  int  $clubId clubId
     * 
     * @return array
     */
    public function sponsorAnalysisPdf($startDate, $endDate, $clubId, $masterTable,$clubType) {
        $masterField = ($clubType == 'federation' ) ? "fed_contact_id" : 'contact_id';
        $active = '(bo.begin_date <= now() AND (bo.end_date>= now() OR bo.end_date IS NULL ) ';
        $former = 'OR bo.end_date <= now())';

        $sql = "SELECT  contactName(bo.contact_id) as contactName, "
                . "SUM(IF(p.amount IS NOT NULL,getPaymentAmount(p.amount,p.discount_type,p.discount),0)) AS amt, "
                . "count(bo.id) as count, "
                . "bo.begin_date,bo.end_date, bo.contact_id as contactId "
                . "FROM fg_sm_services s "
                . "LEFT JOIN fg_sm_category c ON c.id = s.category_id "
                . "LEFT JOIN fg_sm_bookings bo ON bo.service_id = s.id "
                . "LEFT JOIN fg_sm_paymentplans p ON bo.id = p.booking_id AND (p.date >= '{$startDate}' AND p.date <='{$endDate}')"
                . " INNER JOIN {$masterTable} mc ON  mc.$masterField= bo.contact_id "
                . " INNER JOIN fg_cm_contact co ON co.id=mc.$masterField "
                . "WHERE bo.club_id={$clubId} AND co.is_sponsor = 1 AND ("
                . " bo.begin_date<= '{$endDate}' AND"
                . " (bo.end_date >= '{$startDate}' OR bo.end_date IS NULL  ) AND "
                . "case when {$startDate} <= now() then {$active} {$former} else {$active})  end) "
                . "AND bo.is_deleted = 0 AND co.is_deleted = 0 AND co.is_permanent_delete =0 GROUP BY bo.contact_id "
                . " ORDER BY contactName ASC ";

        $result = $this->conn->executeQuery($sql)->fetchAll();

        return array('sponsor' => $result);
    }

    /**
     * function to get  assignment overview count
     * @param  int   $clubId club id
     * 
     * @return array  assignment overview count        
     */
    public function assignmentOverviewCount($clubId, $clubtype) {
        $condition = $this->sidebarCountFromWhere($clubId, $clubtype);
        $active = " (Select count(DISTINCT b.id) FROM {$condition['from']} INNER JOIN fg_sm_bookings b ON b.contact_id = fg_cm_contact.id {$condition['where']} AND b.begin_date <= now() AND (b.end_date >= now() OR b.end_date IS NULL) AND b.club_id= $clubId  AND b.is_deleted= 0 ) as active_assignments";
        $former = " (Select count(DISTINCT b.id) FROM {$condition['from']} INNER JOIN fg_sm_bookings b ON b.contact_id = fg_cm_contact.id {$condition['where']} AND b.end_date <= now() AND b.club_id= $clubId AND b.is_deleted= 0) as former_assignments";
        $ended = " (Select count(DISTINCT b.id) FROM {$condition['from']} INNER JOIN fg_sm_bookings b ON b.contact_id = fg_cm_contact.id {$condition['where']} AND b.end_date <= now() AND b.is_skipped= 0 AND b.club_id= $clubId AND b.is_deleted= 0) as recently_ended ";
        $future = " (Select count(DISTINCT b.id)  FROM {$condition['from']} INNER JOIN fg_sm_bookings b ON b.contact_id = fg_cm_contact.id {$condition['where']} AND b.begin_date >= now() AND b.club_id= $clubId AND b.is_skipped= 0 AND b.is_deleted= 0) as future_assignments";
        $sql = "Select {$active}, {$former}, {$future}, {$ended}  limit 0,1";
        $result = $this->conn->executeQuery($sql)->fetchAll();

        return $result;
    }

}
