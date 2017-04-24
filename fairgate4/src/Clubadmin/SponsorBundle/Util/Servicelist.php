<?php

namespace Clubadmin\SponsorBundle\Util;

use Common\UtilityBundle\Util\FgUtility;

/**
 * To generate the query of servicelist details of  sponsor .
 *
 * @author pitsolutions.ch
 */
class Servicelist
{

    public $where;
    public $columns;
    public $serviceId = 0;
    public $bookedIds = '';
    public $selectionFields = '';
    public $tabType = 'active';
    public $serviceType = 'contact';
    public $search = '';
    public $orderBy;
    private $club;
    private $container;
    private $conn;
    private $fiscalYearDetails;
    private $clubId;
    private $clubtype;
    public $searchval = '';

    /**
     * @param object $container container object
     */
    public function __construct($container)
    {
        $this->where = '';
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->fiscalYearDetails = $this->club->getFiscalYear();
        $this->clubtype = $this->club->get('type');
    }

    /**
     * Set the column fields of table.
     *
     * @return String
     */
    public function setColumns()
    {
        $columns = $this->columns;
        $flag = 0;
        $firstfield = '';
        $key = 0;

        if (is_array($columns) && count($columns) > 0) {
            //concatanate two fields
            if (in_array('contactname', $columns)) {
                $key = array_search('contactname', $columns); //
                $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
                $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
                //$newfield= 'CONCAT_WS(" ",'.$firstname.','. $lastname.' ) as contactname';
                $newfield = 'contactName(fg_cm_contact.id) as contactname';

                $columns[$key] = $newfield;
                $flag = 1;
            }
            if (in_array('isMember', $columns)) {
                $key = array_search('isMember', $columns); //

                $columns[$key] = 'fg_cm_contact.fed_membership_cat_id AS Ismember';
            }
            if (in_array('gender', $columns)) {
                $key = array_search('gender', $columns); //

                $columns[$key] = 'ms.`' . $this->container->getParameter('system_field_gender') . '` AS Gender';
            }
            if (in_array('isCompany', $columns)) {
                $key = array_search('isCompany', $columns); //

                $columns[$key] = 'fg_cm_contact.is_company AS Iscompany ';
            }

            //To get federation membership id
            if (in_array('fedmembershipType', $columns)) {
                $key = array_search('fedmembershipType', $columns); //
                $columns[$key] = "(IF(fg_cm_contact.fed_membership_cat_id IS NOT NULL,fg_cm_contact.fed_membership_cat_id,0)) AS fedmembershipType";
            }
            //To get club membership id
            if (in_array('clubmembershipType', $columns)) {
                $key = array_search('clubmembershipType', $columns); //
                $columns[$key] = "(IF(fg_cm_contact.club_membership_cat_id IS NOT NULL,fg_cm_contact.club_membership_cat_id,0)) AS clubmembershipType";
            }
            if (in_array('isSponsor', $columns)) {
                $key = array_search('isSponsor', $columns); //
                $columns[$key] = 'fg_cm_contact.is_sponsor as sponsorFlag';
            }

            /* end dashboard* */

            if ($flag == 1) {
                $firstfield = $columns[$key];
                unset($columns[$key]);
            }
            $this->selectionFields = implode(',', $columns);

            if ($flag == 1) {
                $this->selectionFields = $firstfield . ',' . $this->selectionFields;
            }
            $this->selectionFields = str_replace('contactid', 'fg_cm_contact.id', $this->selectionFields);
            if (in_array('contactName', $columns)) {
                $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
                $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
                $newfield = 'IF (fg_cm_contact.is_company=0 ,CONCAT_WS(" ",' . $lastname . ',' . $firstname . ' ), `9` ) as contactName';
                $this->selectionFields = str_replace('contactName', $newfield, $this->selectionFields);
            }

            $this->selectionFields = ' fg_cm_contact.id, @MSB_BOOKING_ID := MSB.id, ' . $this->selectionFields;
        } else {
            $this->selectionFields = '*, @MSB_BOOKING_ID := MSB.id';
        }

        return $this->selectionFields;
    }

    /**
     * Set the count field of query.
     *
     * @return type
     */
    public function setCount()
    {
        $this->selectionFields = 'count(fg_cm_contact.id) as count';

        return $this->selectionFields;
    }

    /**
     * Set the from tables of query using clubtype.
     */
    public function setFrom()
    {

        $mainfgcontactIdField = 'fg_cm_contact.id';
        switch ($this->clubtype) {
            case 'federation':
                $this->from = " master_federation_{$this->clubId} as mc INNER JOIN fg_cm_contact on mc.fed_contact_id = fg_cm_contact.fed_contact_id";
                $mainfgcontactIdField = 'mc.fed_contact_id';
                break;
            case 'sub_federation':
                $this->from = "master_federation_{$this->clubId} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.subfed_contact_id";
                $mainfgcontactIdField = 'fg_cm_contact.subfed_contact_id';
                break;
            default:
                $this->from = "master_club_{$this->clubId} as mc INNER JOIN fg_cm_contact on mc.contact_id = fg_cm_contact.id";
        }

        $this->from.=" INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id";
        if ($this->tabType == 'active_assignments' || $this->tabType == 'future_assignments' || $this->tabType == 'former_assignments' || $this->tabType == 'recently_ended') {
            $this->from .= ' INNER JOIN fg_sm_bookings AS MSB ON MSB.contact_id=fg_cm_contact.id';
        } else {
            $this->from .= ' LEFT JOIN fg_sm_bookings AS MSB ON MSB.contact_id=fg_cm_contact.id';
        }
    }

    /**
     * To set the where condition.
     */
    public function setCondition()
    {
        $this->where = 'fg_cm_contact.is_sponsor = 1 ';
        if ($this->tabType != 'overview_past') {
            $this->where .= ' AND fg_cm_contact.is_deleted=0';
        }
        if ($this->bookedIds != '') {
            $this->where .= " AND MSB.id IN($this->bookedIds)";
        } elseif ($this->serviceId != 0) {
            $this->where .= " AND MSB.service_id=$this->serviceId";
        } elseif ($this->contactId != 0) {
            $this->where .= " AND  MSB.contact_id=$this->contactId";
        } else {
            $this->where .= ' AND MSB.is_deleted = 0 ';
        }
        //check the type of list
        switch ($this->tabType) {
            case 'past': case 'overview_past':case 'former_assignments':
                $this->where .= ' AND MSB.end_date <= now() AND MSB.is_deleted = 0';
                break;
            case 'future': case 'overview_future':case 'future_assignments':
                $this->where .= ' AND MSB.begin_date > now()  AND MSB.is_deleted = 0';
                break;
            case 'recently_ended':
                $this->where .= ' AND MSB.end_date <= now() AND MSB.is_skipped=0';
                break;
            default:
                $this->where .= " AND MSB.is_deleted = 0 AND MSB.begin_date <= now() AND (MSB.end_date > now() OR MSB.end_date IS NULL OR MSB.end_date = '')";
                break;
        }
        //set where condition for past/active/future/recent assignment section
        switch ($this->tabType) {
            case 'overview_past':case 'overview_future':case 'overview_active':case 'active_assignments':case 'future_assignments':case 'former_assignments':case 'recently_ended':
                $this->where .= " AND MSB.club_id={$this->clubId}";
                break;
        }
    }

    /**
     * To set the extra condition.
     */
    public function setExtraCondition()
    {
        $firstnameField = 'ms. `' . $this->container->getParameter('system_field_firstname') . '`';
        $lastnameFiled = 'ms. `' . $this->container->getParameter('system_field_lastname') . '`';

        $this->where .= " AND (MSB.end_date  LIKE  '%$this->search%' OR  MSB.begin_date LIKE  '%$this->search%' OR MSB.payment_plan LIKE  '%$this->search%'  OR $firstnameField LIKE  '%$this->search%' OR  $lastnameFiled LIKE  '%$this->search%')";
    }

    /**
     * To set the order by clause.
     */
    public function setOrderBy()
    {
        $this->where .= " ORDER BY $this->orderBy";
    }

    /**
     * To find the columns of a datatables.
     *
     * @return string colum data
     */
    public function getServicetableColumns()
    {
        switch ($this->tabType) {
            case 'past' :
                $jsoncolumns = '{"1":{"id":"payment_start_date","type":"SA","assign_type":"past","name":"SA_paymentstartdate","service_id":' . $this->serviceId . '},
            "2":{"id":"payment_end_date","type":"SA","assign_type":"past","name":"SA_paymentenddate","service_id":' . $this->serviceId . '},
            "3":{"id":"payment_plan","type":"SA","assign_type":"past","name":"SA_paymentplan","service_id":' . $this->serviceId . '},
            "4":{"id":"payments_curr","type":"SA","assign_type":"past","name":"SA_paymentCurr","individual":true,"service_id":' . $this->serviceId . '},
            "5":{"id":"total_payment","type":"SA","assign_type":"past","name":"SA_totalPayment","service_id":' . $this->serviceId . '},
            "6":{"id":"booking_id","type":"SA","assign_type":"future","name":"SA_bookingId","service_id":' . $this->serviceId . '}}';

                break;
            case 'future':
                $jsoncolumns = '{"1":{"id":"payment_start_date","type":"SA","assign_type":"future","name":"SA_paymentstartdate","service_id":' . $this->serviceId . '},
            "2":{"id":"payment_end_date","type":"SA","assign_type":"future","name":"SA_paymentenddate","service_id":' . $this->serviceId . '},
            "3":{"id":"payment_plan","type":"SA","assign_type":"future","name":"SA_paymentplan","service_id":' . $this->serviceId . '},
            "4":{"id":"next_payment_date","type":"SA","assign_type":"future","name":"SA_paymentDate","service_id":' . $this->serviceId . '},
            "5":{"id":"payments_curr","type":"SA","assign_type":"future","name":"SA_paymentCurr","individual":true,"service_id":' . $this->serviceId . '},
            "6":{"id":"payments_nex","type":"SA","assign_type":"future","name":"SA_paymentNext","individual":true,"service_id":' . $this->serviceId . '},
            "7":{"id":"total_payment","type":"SA","assign_type":"future","name":"SA_totalPayment","service_id":' . $this->serviceId . '},
            "8":{"id":"booking_id","type":"SA","assign_type":"future","name":"SA_bookingId","service_id":' . $this->serviceId . '}}';
                break;
            case 'overview_active': case 'overview_future':
                $assignType = ($this->tabType == 'overview_active') ? 'active' : 'future';
                $jsoncolumns = '{"1":{"id":"service_start_date","type":"SA","assign_type":"' . $assignType . '","name":"SA_startdate","service_id":' . $this->serviceId . '},
            "2":{"id":"service_end_date","type":"SA","assign_type":"' . $assignType . '","name":"SA_enddate","service_id":' . $this->serviceId . '},
            "3":{"id":"service_title","type":"SA","assign_type":"' . $assignType . '","name":"SA_serviceTitle","service_id":' . $this->serviceId . '},
            "4":{"id":"payment_plan","type":"SA","assign_type":"' . $assignType . '","name":"SA_paymentplan","service_id":' . $this->serviceId . '},
            "5":{"id":"next_payment_date","type":"SA","assign_type":"' . $assignType . '","name":"SA_nextPaymentDate","service_id":' . $this->serviceId . '},
            "6":{"id":"payments_nex","type":"SA","assign_type":"' . $assignType . '","name":"SA_paymentNex","individual":true,"service_id":' . $this->serviceId . '},
            "7":{"id":"payments_curr","type":"SA","assign_type":"' . $assignType . '","name":"SA_paymentCurr","individual":true,"service_id":' . $this->serviceId . '},
            "8":{"id":"booking_id","type":"SA","assign_type":"' . $assignType . '","name":"SA_bookingId","service_id":' . $this->serviceId . '},
            "9":{"id":"total_payment","type":"SA","assign_type":"' . $assignType . '","name":"SA_totalPayment","service_id":' . $this->serviceId . '},
            "10":{"id":"service_id","type":"SA","assign_type":"' . $assignType . '","name":"SA_serviceId","service_id":' . $this->serviceId . '},
            "11":{"id":"service_category","type":"SA","assign_type":"' . $assignType . '","name":"SA_serviceCatId","service_id":' . $this->serviceId . '}}';
                break;
            case 'overview_past':
                $jsoncolumns = '{"1":{"id":"service_start_date","type":"SA","assign_type":"past","name":"SA_startdate","service_id":' . $this->serviceId . '},
            "2":{"id":"service_end_date","type":"SA","assign_type":"past","name":"SA_enddate","service_id":' . $this->serviceId . '},
            "3":{"id":"service_title","type":"SA","assign_type":"past","name":"SA_serviceTitle","service_id":' . $this->serviceId . '},
            "4":{"id":"payment_plan","type":"SA","assign_type":"past","name":"SA_paymentplan","service_id":' . $this->serviceId . '},
            "6":{"id":"total_payment","type":"SA","assign_type":"past","name":"SA_totalPayment","service_id":' . $this->serviceId . '},
            "7":{"id":"booking_id","type":"SA","assign_type":"past","name":"SA_bookingId","service_id":' . $this->serviceId . '},
            "8":{"id":"service_id","type":"SA","assign_type":"past","name":"SA_serviceId","service_id":' . $this->serviceId . '},
            "9":{"id":"service_category","type":"SA","assign_type":"past","name":"SA_serviceCatId","service_id":' . $this->serviceId . '}}';
                break;
            case 'former_assignments':case 'recently_ended':
                $jsoncolumns = '{"1":{"id":"payment_start_date","type":"SA","assign_type":"active","name":"SA_paymentstartdate"},
            "2":{"id":"payment_end_date","type":"SA","assign_type":"active","name":"SA_paymentenddate"},
            "3":{"id":"service_name","type":"SA","assign_type":"active","name":"SA_serviceName"},
            "4":{"id":"payment_plan","type":"SA","assign_type":"active","name":"SA_paymentplan"},
            "5":{"id":"payments_curr","type":"SA","assign_type":"active","individual":true,"name":"SA_paymentCurr"},
            "6":{"id":"total_payment","type":"SA","assign_type":"active","name":"SA_totalPayment"},
            "7":{"id":"booking_id","type":"SA","assign_type":"active","name":"SA_bookingId"},
            "8":{"id":"service_id","type":"SA","assign_type":"active","name":"SA_serviceId"},
            "9":{"id":"service_type","type":"SA","assign_type":"active","name":"SA_service_type"},
            "10":{"id":"service_category","type":"SA","assign_type":"active","name":"SA_service_category"}}';

                break;
            case 'active_assignments':
                $jsoncolumns = '{"1":{"id":"payment_start_date","type":"SA","assign_type":"active","name":"SA_paymentstartdate"},
            "2":{"id":"payment_end_date","type":"SA","assign_type":"active","name":"SA_paymentenddate"},
            "3":{"id":"service_name","type":"SA","assign_type":"active","name":"SA_serviceName"},
            "4":{"id":"payment_plan","type":"SA","assign_type":"active","name":"SA_paymentplan"},
            "5":{"id":"next_payment_date","type":"SA","assign_type":"active","name":"SA_paymentDate"},
            "6":{"id":"payments_curr","type":"SA","individual":true,"assign_type":"active","name":"SA_paymentCurr"},
            "7":{"id":"payments_nex","type":"SA","assign_type":"active","individual":true,"name":"SA_paymentNext"},
            "8":{"id":"total_payment","type":"SA","assign_type":"active","name":"SA_totalPayment"},
            "9":{"id":"booking_id","type":"SA","assign_type":"active","name":"SA_bookingId"},
            "10":{"id":"service_id","type":"SA","assign_type":"active","name":"SA_serviceId"},
            "11":{"id":"service_type","type":"SA","assign_type":"active","name":"SA_service_type"},
            "12":{"id":"service_category","type":"SA","assign_type":"active","name":"SA_service_category"}}';
                break;
            case 'future_assignments':
                $jsoncolumns = '{"1":{"id":"payment_start_date","type":"SA","assign_type":"active","name":"SA_paymentstartdate"},
            "2":{"id":"payment_end_date","type":"SA","assign_type":"active","name":"SA_paymentenddate"},
            "3":{"id":"service_name","type":"SA","assign_type":"active","name":"SA_serviceName"},
            "4":{"id":"payment_plan","type":"SA","assign_type":"active","name":"SA_paymentplan"},
            "5":{"id":"next_payment_date","type":"SA","assign_type":"future","name":"SA_paymentDate"},
            "6":{"id":"payments_curr","individual":true,"type":"SA","assign_type":"active","name":"SA_paymentCurr"},
            "7":{"id":"payments_nex","individual":true, "type":"SA","assign_type":"active","name":"SA_paymentNext"},
            "8":{"id":"total_payment","type":"SA","assign_type":"active","name":"SA_totalPayment"},
            "9":{"id":"booking_id","type":"SA","assign_type":"active","name":"SA_bookingId"},
            "10":{"id":"service_id","type":"SA","assign_type":"active","name":"SA_serviceId"},
            "11":{"id":"service_type","type":"SA","assign_type":"active","name":"SA_service_type"},
            "12":{"id":"service_category","type":"SA","assign_type":"active","name":"SA_service_category"}}';
                break;
            default:
                $jsoncolumns = '{"1":{"id":"payment_start_date","type":"SA","assign_type":"active","name":"SA_paymentstartdate","service_id":"' . $this->serviceId . '"},
            "2":{"id":"payment_end_date","type":"SA","assign_type":"active","name":"SA_paymentenddate","service_id":"' . $this->serviceId . '"},
            "3":{"id":"payment_deposited_with","type":"SA","assign_type":"active","service_type":"' . $this->serviceType . '","name":"SA_depositedwith","service_id":"' . $this->serviceId . '"},
            "4":{"id":"payment_plan","type":"SA","assign_type":"active","name":"SA_paymentplan","service_id":"' . $this->serviceId . '"},
            "5":{"id":"next_payment_date","type":"SA","assign_type":"active","name":"SA_paymentDate","service_id":"' . $this->serviceId . '"},
            "6":{"id":"payments_curr","type":"SA","assign_type":"active","name":"SA_paymentCurr","individual":true,"service_id":"' . $this->serviceId . '"},
            "7":{"id":"payments_nex","type":"SA","assign_type":"active","name":"SA_paymentNext","individual":true,"service_id":"' . $this->serviceId . '"},
            "8":{"id":"total_payment","type":"SA","assign_type":"active","name":"SA_totalPayment","service_id":"' . $this->serviceId . '"},
            "9":{"id":"booking_id","type":"SA","assign_type":"future","name":"SA_bookingId","service_id":' . $this->serviceId . '}}';

                break;
        }

        return $jsoncolumns;
    }

    /**
     * To set the search consition.
     */
    public function setSearchCondition()
    {
        if ($this->searchval != '') {
            $searchVal = FgUtility::getSecuredDataString($this->searchval['value'], $this->conn);
            $this->where .= " contactname  LIKE '%" . $searchVal . "%'";
        }
    }

    /**
     * To get the final query string.
     *
     * @return result
     */
    public function getResult()
    {
        $this->result = 'SELECT ' . $this->selectionFields . ' FROM ' . $this->from;
        $this->result .= ' WHERE ' . $this->where;

        return $this->result;
    }
}
