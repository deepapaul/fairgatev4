<?php

namespace Clubadmin\SponsorBundle\Util;

use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * To handle the filter conditions of sponsorSponsorfilter.
 *
 * @author     Jinesh.m
 *
 * @version    Fairgate V4
 */
class Sponsorfilter
{
    private $where;
    private $filterDatas;
    private $mysqlDateFormat;
    private $club;
    private $container;
    private $conn;
    private $typeArray;
    private $numbertypeArray;
    private $fiscalYearDetails;

    /**
     * @param object $container   container object
     * @param array  $filterArray contain   filter details
     */
    public function __construct($container, $filterArray)
    {
        $this->filterDatas = $filterArray;
        $this->where = '';
        $this->iCount = 0;
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->conn = $this->container->get('database_connection');
        $this->typeArray = array('multiline', 'singleline', 'email', 'url', 'login email', 'number', 'number_Range');
        $this->numbertypeArray = array('number', 'number_Range');
        $this->fiscalYearDetails = $this->club->getFiscalYear();
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function To create where conditions.
     *
     * @return string where conditions
     */
    public function generateFilter()
    {
        foreach ($this->filterDatas as $filters) {
            $this->iCount++;
            if ($filters['entry'] == '' || $filters['condition'] == '') {
                continue;
            }
            if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
                $this->where .= ' (';
            } else {
                $this->where .= ' '.$filters['connector'].'( ';
            }
            //check the type of filter data
            switch ($filters['type']) {
                case 'SA':
                    $this->analysisfilter($filters);
                    break;
                case 'CO':
                    $this->contactoptionfilter($filters);
                    break;
                case 'CF':
                    $this->contactfieldfilter($filters);
                    break;
                case 'SS':
                    $this->servicefilter($filters);
                    break;
                case 'FI':
                    $this->federationfilter($filters);
                    break;
                case 'FM':
                    $this->membershipFilter($filters);
                    break;
                case 'CM':
                    $this->membershipFilter($filters);
                    break;    
            }
        }
        if ($this->iCount > 1) {
            $this->where = '( '.$this->where.' )';
        }

        return $this->where;
    }

    /**
     * For create contact  option wher condition.
     *
     * @param array $filters contactoption where conditions
     */
    private function contactoptionfilter($filters)
    {
        switch ($filters['entry']) {
            case 'contact_type':
                $this->where .= 'fg_cm_contact.is_company';
                if ($filters['input1'] == 'company') {
                    $this->where .= ($filters['condition'] == 'is') ? '=1)' : '=0)';
                } else {
                    $this->where .= ($filters['condition'] == 'is') ? '=0)' : '=1)';
                }
                break;
            case 'membership':
                 if ($filters['condition'] === 'is not') {
                     $this->where .= ($filters['input1'] == 'any') ? ' fg_cm_contact.club_membership_cat_id IS NULL)' : 'fg_cm_contact.club_membership_cat_id !='.$filters['input1'].')';
                 } else {
                     $this->where .= ($filters['input1'] == 'any') ? ' fg_cm_contact.club_membership_cat_id >0 )' : ' fg_cm_contact.club_membership_cat_id ='.$filters['input1'].')';
                 }
                break;
            case 'fed_membership':
                 if ($filters['condition'] == 'is not') {
                     $this->where .= ($filters['input1'] == 'any') ? ' fg_cm_contact.fed_membership_cat_id IS NULL)' : 'fg_cm_contact.fed_membership_cat_id !='.$filters['input1'].')';
                 } else {
                     $this->where .= ($filters['input1'] == 'any') ? ' fg_cm_contact.fed_membership_cat_id >0 )' : ' fg_cm_contact.fed_membership_cat_id ='.$filters['input1'].')';
                 }
                break;

            case 'sponsor' :
                $this->where .= $this->sponsortypeFilter($filters).' )';
                break;
        }
    }

    /**
     * For create analysis field where condition.
     *
     * @param array $filters analsis fields
     */
    public function analysisfilter($filters)
    {
        $where = '';
        switch ($filters['entry']) {
            case 'active_assignments':case 'future_assignments':case 'past_assignments':
                $where = $this->assignmentConditions($filters);
                break;
            case 'payments_curr':
                $where = $this->serviceamountFilter($filters, 'current');
                break;
            case 'payments_nex':
                $where = $this->serviceamountFilter($filters, 'next');
                break;
        }
        $this->where .= $where.' )';

        return $where;
    }

    /**
     * For create the contact filter where condition.
     *
     * @param array $filters contact filter values
     */
    private function contactfieldfilter($filters)
    {
        $exsql = 'SELECT input_type as input_type FROM fg_cm_attribute WHERE id ='.$filters['entry'];
        $entryType = $this->conn->executeQuery($exsql)->fetch();
        $this->conn->close();
        if ($filters['entry'] != '' && ($filters['condition'] == 'contains' || $filters['condition'] == 'contains not' || $filters['condition'] == 'begins with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'end with' || $filters['condition'] == 'end not with')) {
        } else {
            if ($entryType['input_type'] == 'date') {
                $betweenDate = " STR_TO_DATE('".FgUtility::getSecuredData($filters['input1'], $this->conn)."','".$this->mysqlDateFormat."') AND STR_TO_DATE('".FgUtility::getSecuredData($filters['input2'], $this->conn)."','".$this->mysqlDateFormat."')";
                $this->where .= ' `'.$filters['entry'].'` ';
                $filters['input1'] = $filters['input1'] != '' ? " STR_TO_DATE('".FgUtility::getSecuredData($filters['input1'], $this->conn)."', '".$this->mysqlDateFormat."')" : '';
                $filters['input2'] = $filters['input2'] != '' ? " STR_TO_DATE('".FgUtility::getSecuredData($filters['input2'], $this->conn)."', '".$this->mysqlDateFormat."')" : '';
            } elseif ($filters['data_type'] == 'select') {
                $this->where .= '';
            } elseif ($filters['data_type'] == 'number' || $filters['data_type'] == 'number_Range') {
                $filters['input1'] = str_replace(',', '.', $filters['input1']);
                $filters['input2'] = str_replace(',', '.', $filters['input2']);
                $this->where .= ' `'.$filters['entry'].'`';
            } else {
                $this->where .= ' `'.$filters['entry'].'`';
            }
        }

        //check if the entry is subfed field or fed field
        $additionalWhere = '';
        if (in_array($filters['entry'], $this->club->get('fedFields'))) {
            $additionalWhere = ' OR mf.contact_id IS NULL';
        } elseif (in_array($filters['entry'], $this->club->get('subFedFields'))) {
            $additionalWhere = ' OR msf.contact_id IS NULL';
        }
        switch ($filters['condition']) {
            case 'contains':
                $this->where .= ($filters['input1'] != '') ? ' `'.$filters['entry']."` LIKE '%".FgUtility::getSecuredData($filters['input1'], $this->conn)."%' )" : ' `'.$filters['entry'].'` IS NULL  OR `'.$filters['entry']."`='' $additionalWhere )";
                break;
            case 'contains not':
                $this->where .= ($filters['input1'] != '') ? ' `'.$filters['entry']."` NOT LIKE '%".FgUtility::getSecuredData($filters['input1'], $this->conn)."%'  OR  `".$filters['entry'].'` IS NULL )' : ' `'.$filters['entry'].'` IS NOT NULL AND `'.$filters['entry']."` !='')";
                break;
            case 'begins with':
                $this->where .= ($filters['input1'] != '') ? ' `'.$filters['entry']."` LIKE '".FgUtility::getSecuredData($filters['input1'], $this->conn)."%' )" : ' `'.$filters['entry'].'` IS NULL  OR `'.$filters['entry']."`='' $additionalWhere )";
                break;
            case 'begins not with':
                $this->where .= ($filters['input1'] != '') ? ' `'.$filters['entry']."` NOT LIKE '".FgUtility::getSecuredData($filters['input1'], $this->conn)."%'  OR  `".$filters['entry'].'` IS NULL )' : ' `'.$filters['entry'].'` IS NOT NULL AND `'.$filters['entry']."` !='')";
                break;
            case 'end with':
                $this->where .= ($filters['input1'] != '') ? ' `'.$filters['entry']."` LIKE '%".FgUtility::getSecuredData($filters['input1'], $this->conn)."' )" : ' `'.$filters['entry'].'` IS NULL  OR `'.$filters['entry']."`='' $additionalWhere )";
                break;
            case 'end not with':
                $this->where .= ($filters['input1'] != '') ? ' `'.$filters['entry']."` NOT LIKE '%".FgUtility::getSecuredData($filters['input1'], $this->conn)."'  OR  `".$filters['entry'].'` IS NULL )' : ' `'.$filters['entry'].'` IS NOT NULL AND `'.$filters['entry']."` !='')";
                break;
            case 'is':
                if ((in_array($entryType['input_type'], $this->typeArray)) && $filters['input1'] == '') {
                    $this->where .= " ='' OR `".$filters['entry'].'` IS NULL  OR `'.$filters['entry']."`='' OR `".$filters['entry']."`='0000-00-00' OR `".$filters['entry']."`='0000-00-00 00:00:00' $additionalWhere)";
                } elseif ($filters['input1'] != '') {
                    if (in_array($filters['data_type'], $this->numbertypeArray)) {
                        $this->where .= ' ='.FgUtility::getSecuredData($filters['input1'], $this->conn).' )';
                    } elseif ($filters['data_type'] == 'select') {
                        $this->where .= " CONCAT(';',`".$filters['entry']."`,';') LIKE '%;".FgUtility::getSecuredData($filters['input1'], $this->conn).";%' )";
                    } elseif ($filters['data_type'] == 'date') {
                        $this->where .= '= '.$filters['input1'].')';
                    } else {
                        $this->where .= " ='".FgUtility::getSecuredData($filters['input1'], $this->conn)."' )";
                    }
                } elseif ($filters['input1'] == '') {
                    $this->where .= " ='' OR `".$filters['entry'].'` IS NULL OR `'.$filters['entry']."`='0000-00-00' OR `".$filters['entry']."`='0000-00-00 00:00:00')";
                }

                break;
            case 'is not':
                if ((in_array($entryType['input_type'], $this->typeArray)) && $filters['input1'] == '') {
                    $this->where .= " !='' AND `".$filters['entry'].'` IS NOT NULL )';
                } elseif ($filters['input1'] == '') {
                    $this->where .= " !='' OR `".$filters['entry'].'` IS NOT NULL OR `'.$filters['entry']."`!='0000-00-00' OR `".$filters['entry']."`!='0000-00-00 00:00:00')";
                } elseif ($filters['input1'] != '') {
                    if (in_array($filters['data_type'], $this->numbertypeArray)) {
                        $this->where .= ' !='.FgUtility::getSecuredData($filters['input1'], $this->conn).')';
                    } elseif ($filters['data_type'] == 'select') {
                        $this->where .= " CONCAT(';',`".$filters['entry']."`,';') NOT LIKE '%;".FgUtility::getSecuredData($filters['input1'], $this->conn).";%' OR `".$filters['entry']."`='' OR `".$filters['entry'].'` IS NULL)';
                    } elseif ($filters['data_type'] == 'date') {
                        $this->where .= ' !='.$filters['input1'].')';
                    } else {
                        $this->where .= " !='".FgUtility::getSecuredData($filters['input1'], $this->conn)."')";
                    }
                }

                break;
            case 'is between':
                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where .= ($entryType['input_type'] == 'date') ? " !='' AND `".$filters['entry'].'` IS NOT NULL AND '.$filters['entry']."!='00.00.0000' AND ".$filters['entry']."!='0000-00-00 00:00:00')" : " !='' AND `".$filters['entry'].'` IS NOT NULL )';
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    $this->where .= ($entryType['input_type'] == 'date') ? ' >= '.$filters['input1'].')' : ' >= '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND (`'.$filters['entry'].'` IS NOT NULL OR `'.$filters['entry']."`!=''))";
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $this->where .= ($entryType['input_type'] == 'date') ? ' <= '.$filters['input2'].')' : ' <= '.FgUtility::getSecuredData($filters['input2'], $this->conn).' AND (`'.$filters['entry'].'` IS NOT NULL OR `'.$filters['entry']."`!=''))";
                } else {
                    if ($entryType['input_type'] == 'date') {
                        $this->where .= isset($betweenDate) ? " BETWEEN $betweenDate)" : ' BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                    } else {
                        $this->where .= ' BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                    }
                }
                break;
            default:
                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where .= ($entryType['input_type'] == 'date') ? " ='' OR `".$filters['entry'].'` IS NULL   OR `'.$filters['entry']."`='00.00.0000' OR `".$filters['entry']."`='0000-00-00 00:00:00' $additionalWhere)" : " ='' OR `".$filters['entry']."` IS NULL   $additionalWhere)";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    $this->where .= ($entryType['input_type'] == 'date') ? ' < '.$filters['input1'].')' : ' < '.FgUtility::getSecuredData($filters['input1'], $this->conn).' OR (`'.$filters['entry'].'` IS NULL OR `'.$filters['entry']."`='') $additionalWhere)";
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $this->where .= ($entryType['input_type'] == 'date') ? ' > '.$filters['input2'].')' : ' > '.FgUtility::getSecuredData($filters['input2'], $this->conn).' OR (`'.$filters['entry'].'` IS NULL OR `'.$filters['entry']."`='') $additionalWhere)";
                } else {
                    if ($entryType['input_type'] == 'date') {
                        $this->where .= isset($betweenDate) ? "NOT BETWEEN $betweenDate)" : ' NOT BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                    } else {
                        $this->where .= ' NOT BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                    }
                }

                break;
        }
    }

    /**
     * For create the service filter where condition.
     *
     * @param array $filters contact filter values
     */
    public function servicefilter($filters)
    {
        $where = $this->seviceConditions($filters);
        $this->where .= $where.')';

        return $where;
    }

    /**
     * To handle all past/active/future assignment conditions.
     *
     * @param array $filters contain filter details
     *
     * @return string
     */
    private function assignmentConditions($filters)
    {
        $filters['input1'] = (isset($filters['input1'])) ? $filters['input1'] : '';
        $filters['input2'] = (isset($filters['input2'])) ? $filters['input2'] : '';
        $where = '';
        //Field selection area
        switch ($filters['entry']) {
            case 'past_assignments':
                $filters['entry'] = '(SELECT count(BT.id) FROM fg_cm_contact AS BT INNER JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id').' WHERE SB.contact_id=fg_cm_contact.id AND SB.end_date <= now())';
                break;
            case 'active_assignments':
                $filters['entry'] = '(SELECT count(BT.id) FROM fg_cm_contact AS BT INNER JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id')." WHERE SB.contact_id=fg_cm_contact.id AND SB.begin_date <= now() AND (SB.end_date >= now() OR SB.end_date IS NULL OR SB.end_date = ''))";
                break;
            case 'future_assignments':
                $filters['entry'] = '(SELECT count(BT.id) FROM fg_cm_contact AS BT INNER JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id').' WHERE SB.contact_id = fg_cm_contact.id AND SB.begin_date > now())';
                break;
        }
        //condition setting area
        switch ($filters['condition']) {
            case 'is between':
                if ($filters['input1'] != '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn);
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    $where = $filters['entry'].' >='.FgUtility::getSecuredData($filters['input1'], $this->conn);
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' <='.FgUtility::getSecuredData($filters['input2'], $this->conn);
                } else {
                    $where = $filters['entry']." !=''";
                }
                break;
            case 'is not between':
                if ($filters['input1'] != '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' NOT BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn);
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    $where = $filters['entry'].' <'.FgUtility::getSecuredData($filters['input1'], $this->conn);
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' >'.FgUtility::getSecuredData($filters['input2'], $this->conn);
                } else {
                    $where = $filters['entry']." =''";
                }
                break;
            case 'is':
                if ($filters['input1'] != '') {
                    $where = $filters['entry'].' ='.FgUtility::getSecuredData($filters['input1'], $this->conn);
                } else {
                    $where = $filters['entry']." =''";
                }
                break;
            case 'is not':
                if ($filters['input1'] != '') {
                    $where = $filters['entry'].' !='.FgUtility::getSecuredData($filters['input1'], $this->conn);
                } else {
                    $where = $filters['entry']." !=''";
                }
                break;
        }

        return $where;
    }

    /**
     * To handle the conditions of past/future/active services.
     *
     * @param array $filters contain filter details
     *
     * @return string where condition
     */
    private function seviceConditions($filters)
    {
        $where = '';
        switch ($filters['condition']) {
            case 'has active assignments':
                $where = ' fg_cm_contact.id IN (SELECT BT.id FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND  SB.club_id='.$this->club->get('id')." WHERE SB.service_id={$filters['input1']} AND  SB.begin_date <= now() AND (SB.end_date >= now() OR SB.end_date IS NULL OR SB.end_date = ''))";
                break;
            case 'has no active assignment':
                $where = ' fg_cm_contact.id NOT IN (SELECT BT.id FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id')." WHERE SB.service_id={$filters['input1']} AND SB.begin_date <= now() AND (SB.end_date >= now() OR SB.end_date IS NULL OR SB.end_date = ''))";
                break;
            case 'has past assignment':
                $where = 'fg_cm_contact.id IN (SELECT BT.id FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id')." WHERE SB.service_id={$filters['input1']} AND SB.end_date <= now())";
                break;
            case 'has no past assignment':
                $where = 'fg_cm_contact.id NOT IN (SELECT BT.id FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id')."  WHERE SB.service_id={$filters['input1']} AND SB.end_date <= now())";
                break;
            case 'has future assignment':
                $where = 'fg_cm_contact.id IN (SELECT BT.id FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id')." WHERE SB.service_id={$filters['input1']} AND SB.begin_date > now())";
                break;
            case 'has no future assignment':
                $where = 'fg_cm_contact.id NOT IN (SELECT BT.id FROM fg_cm_contact AS BT LEFT JOIN fg_sm_bookings AS SB ON SB.contact_id = BT.id AND SB.is_deleted = 0 AND SB.club_id='.$this->club->get('id')." WHERE SB.service_id={$filters['input1']} AND SB.begin_date > now())";
                break;
        }

        return $where;
    }

    /**
     * set  where condition of past/active/future/prospect sponsor.
     *
     * @param array $filters contain filter details
     *
     * @return string where condition
     */
    private function sponsortypeFilter($filters)
    {
        $chk = ($filters['condition'] == 'is') ? '=' : '!=';
        $subquery = ' IF (fg_cm_contact.is_sponsor = 1,'
                .' IF((select count(fg_sm_bookings.contact_id) from fg_sm_bookings where fg_sm_bookings.contact_id=fg_cm_contact.id AND fg_sm_bookings.is_deleted = 0 )= 0,'
                ."'prospect',"
                .'(Select CASE'
                ." WHEN (fg_sm_bookings.begin_date > now() AND fg_sm_bookings.is_deleted = 0 AND fg_sm_bookings.club_id={$this->club->get('id')} AND fg_sm_bookings.contact_id=fg_cm_contact.id) THEN 'future_sponsor' "
                ." WHEN (fg_sm_bookings.begin_date <= now() AND fg_sm_bookings.is_deleted = 0 AND (fg_sm_bookings.end_date >= now() OR fg_sm_bookings.end_date IS NULL) AND fg_sm_bookings.club_id={$this->club->get('id')} AND fg_sm_bookings.contact_id=fg_cm_contact.id) THEN 'active_sponsor'"
                ." WHEN (fg_sm_bookings.end_date < now() AND fg_sm_bookings.is_deleted = 0 AND fg_sm_bookings.club_id={$this->club->get('id')} AND fg_sm_bookings.contact_id=fg_cm_contact.id) THEN 'former_sponsor' END AS Gsponsor FROM fg_sm_bookings WHERE fg_sm_bookings.contact_id=fg_cm_contact.id "
                ." ORDER BY  (CASE WHEN  Gsponsor='active_sponsor' then 1 WHEN Gsponsor='future_sponsor' THEN 2 WHEN Gsponsor='former_sponsor' then 3 ELSE 4 END) asc limit 0,1) ),"
                ."'' ) ";
                
        if ($filters['condition'] == 'is') {
            if ($filters['input1'] == 'any') {                           
                $this->where.=" $subquery  IN ('prospect', 'future_sponsor', 'active_sponsor', 'former_sponsor')  ";
            } else {
                $chk = '=';
                $this->where.=" $subquery  {$chk}'{$filters['input1']}' ";
            }
        }else{
            if ($filters['input1'] == 'any') {                           
                $this->where.=" $subquery  NOT IN ('prospect', 'future_sponsor', 'active_sponsor', 'former_sponsor') ";
            } else {
                $chk='!=';
                $this->where.=" $subquery  {$chk}'{$filters['input1']}' ";
            }
        }

        return $where;
    }

    /**
     * function to service amount calculation.
     *
     * @param array  $filters contain filter details
     * @param string $type    index   of     array
     *
     * @return string
     */
    private function serviceamountFilter($filters, $type)
    {
        $startYear = $this->fiscalYearDetails[$type]['start'];
        $endYear = $this->fiscalYearDetails[$type]['end'];
        $where = '';
        $filters['entry'] = ' fg_cm_contact.id IN (SELECT SB.contact_id FROM fg_sm_bookings AS SB INNER JOIN fg_sm_paymentplans AS PP ON PP.booking_id =SB.id  WHERE SB.club_id='.$this->club->get('id')." AND SB.contact_id = fg_cm_contact.id AND PP.date >='{$startYear}' AND PP.date <='{$endYear}' AND SB.payment_plan !='none' GROUP BY SB.contact_id  HAVING  SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount))";
        switch ($filters['condition']) {
            case 'is between':
                if ($filters['input1'] != '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    $where = $filters['entry'].' >='.FgUtility::getSecuredData($filters['input1'], $this->conn).')';
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' <='.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                } else {
                    $filters['entry'] = '(SELECT SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount)) FROM fg_sm_bookings AS SB INNER JOIN fg_sm_paymentplans AS PP ON PP.booking_id =SB.id  WHERE SB.club_id='.$this->club->get('id')." AND SB.contact_id = fg_cm_contact.id AND PP.date >='{$startYear}' AND PP.date <='{$endYear}' AND SB.payment_plan !='none' GROUP BY SB.contact_id)";
                    $where = $filters['entry'].' IS NOT NULL';
                }
                break;
            case 'is not between':
                if ($filters['input1'] != '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' NOT BETWEEN '.FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    $where = $filters['entry'].' <'.FgUtility::getSecuredData($filters['input1'], $this->conn).')';
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $where = $filters['entry'].' >'.FgUtility::getSecuredData($filters['input2'], $this->conn).')';
                } else {
                    $filters['entry'] = '(SELECT SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount)) FROM fg_sm_bookings AS SB INNER JOIN fg_sm_paymentplans AS PP ON PP.booking_id =SB.id  WHERE SB.club_id='.$this->club->get('id')." AND SB.contact_id = fg_cm_contact.id AND PP.date >='{$startYear}' AND PP.date <='{$endYear}' AND SB.payment_plan !='none' GROUP BY SB.contact_id)";
                    $where = $filters['entry'].' IS NULL';
                }
                break;
            case 'is':
                if ($filters['input1'] != '') {
                    $where = $filters['entry'].' ='.FgUtility::getSecuredData($filters['input1'], $this->conn).')';
                } else {
                    $filters['entry'] = '(SELECT SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount)) FROM fg_sm_bookings AS SB INNER JOIN fg_sm_paymentplans AS PP ON PP.booking_id =SB.id  WHERE SB.club_id='.$this->club->get('id')." AND SB.contact_id = fg_cm_contact.id AND PP.date >='{$startYear}' AND PP.date <='{$endYear}' AND SB.payment_plan !='none' GROUP BY SB.contact_id)";
                    $where = $filters['entry'].' IS NULL';
                }
                break;
            case 'is not':
                if ($filters['input1'] != '') {
                    $where = $filters['entry'].' !='.FgUtility::getSecuredData($filters['input1'], $this->conn).')';
                } else {
                    $filters['entry'] = '(SELECT SUM(getPaymentAmount(PP.amount,PP.discount_type,PP.discount)) FROM fg_sm_bookings AS SB INNER JOIN fg_sm_paymentplans AS PP ON PP.booking_id =SB.id  WHERE SB.club_id='.$this->club->get('id')." AND SB.contact_id = fg_cm_contact.id AND PP.date >='{$startYear}' AND PP.date <='{$endYear}' AND SB.payment_plan !='none' GROUP BY SB.contact_id)";
                    $where = $filters['entry'].' IS NOT NULL';
                }
                break;
        }

        return $where;
    }

    /**
     * function to get category/services of sponsoer- data.
     *
     * @param string $contacttype sponsor/contact
     *
     * @return array
     */
    public function sponsorServices($contacttype)
    {
        $filterData = array();
        $clubId = $this->container->get('club')->get('id');
        $defaultlang = $this->container->get('club')->get('default_lang');
        $sponsorCategory = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllSponsorCategory($clubId);
        $filterData['service']['id'] = 'SS';
        $filterData['service']['show_filter'] = 0;
        $filterData['service']['title'] = $this->container->get('translator')->trans('SM_SPONSOR_SERVICES');
        $filterData['service']['fixed_options'][0][] = array('id' => '', 'title' => '- '.$this->container->get('translator')->trans('SM_SELECT_CATEGORY').' -');
        $filterData['service']['fixed_options'][1][] = array('id' => '', 'title' => '- '.$this->container->get('translator')->trans('SM_SELECT_SERVICE').' -');
        $serviceCount = 0;
        if (count($sponsorCategory) > 0) {
            $iCount = 0;
            foreach ($sponsorCategory as $category) {
                //collect the service detials of the particular category
                $allServices = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllSponsorServicesBookmark($clubId, $category['categoryId'], $defaultlang);
                $filterData['service']['entry'][$iCount]['id'] = "{$category['categoryId']}";
                $filterData['service']['entry'][$iCount]['title'] = $category['catTitle'];
                $filterData['service']['entry'][$iCount]['type'] = 'assignments';
                $filterData['service']['entry'][$iCount]['input'] = array();
                if (!empty($allServices) && count($allServices) > 0) {
                    $filterData['service']['entry'][$iCount]['show_filter'] = 1;
                    $serviceCount = 0;
                    foreach ($allServices as $services) {
                        $filterData['service']['entry'][$iCount]['input'][$serviceCount] = $this->serviceSubarray($services);
                        $serviceCount++;
                    }
                } else {
                    $filterData['service']['entry'][$iCount]['show_filter'] = 0;
                }
                $iCount++;
            }
        } else {
            $filterData['service']['show_filter'] = 0;
            $filterData['service']['entry'] = array();
        }
        if (isset($serviceCount) && $serviceCount == 0) {
            $filterData['service']['show_filter'] = 0;
        } else {
            $filterData['service']['show_filter'] = 1;
        }
        $filterData['service']['show_filter'] = ($contacttype == 'archivedsponsor') ? 0 : 1;

        return $filterData['service'];
    }

    /**
     * function to get sponser analysis.
     *
     * @param String $contacttype sponsor/contact
     *
     * @return array
     */
    public function sponserAnalysis($contacttype)
    {
        //Sponsor Analysis
        $filterData = array();
        $filterData['SA']['title'] = $this->container->get('translator')->trans('SM_SPONSOR_ANALYSIS');
        $filterData['SA']['id'] = 'SA';
        $filterData['SA']['fixed_options'][0][] = array('id' => '', 'title' => '- '.$this->container->get('translator')->trans('SM_SELECT_CATEGORY').' -');
        if ($contacttype != 'archivedsponsor') {
            $filterData['SA']['entry'][] = array('id' => 'active_assignments', 'title' => $this->container->get('translator')->trans('SM_ACTIVE_ASSIGNMENTS'), 'type' => 'number');
            $filterData['SA']['entry'][] = array('id' => 'future_assignments', 'title' => $this->container->get('translator')->trans('SM_FUTURE_ASSIGNMENTS'), 'type' => 'number');
            $filterData['SA']['entry'][] = array('id' => 'past_assignments', 'title' => $this->container->get('translator')->trans('SM_PAST_ASSIGNMENTS'), 'type' => 'number');
        }

        $fiscalYear = $this->container->get('club')->getFiscalYear();
        $transCurr = $this->container->get('translator')->trans('SM_PAYMENTS_CURR');
        $title = str_replace('%yr%', $fiscalYear['current']['label'], $transCurr);
        $transNex = $this->container->get('translator')->trans('SM_PAYMENTS_NEX');
        $title1 = str_replace('%yr%', $fiscalYear['next']['label'], $transNex);
        $filterData['SA']['entry'][] = array('id' => 'payments_curr', 'title' => $title, 'type' => 'number');
        $filterData['SA']['entry'][] = array('id' => 'payments_nex', 'title' => $title1, 'type' => 'number');

        return $filterData['SA'];
    }

    /**
     * function to create subarray services.
     *
     * @param array $services services-array
     *
     * @return array subarray
     */
    private function serviceSubarray($services)
    {
        $filterData['type'] = 'select';
        $filterData['id'] = "{$services['id']}";
        $filterData['title'] = $services['title'];
        $filterData['itemType'] = 'service';
        $filterData['categoryId'] = "{$services['categoryId']}";
        $filterData['show_filter'] = 1;
        $filterData['draggable'] = $services['draggable'];
        $filterData['bookMarkId'] = $services['bookMarkId'];
        $filterData['functionAssign'] = $services['serviceType'];
        if ($services['serviceType'] == 'team') {
            $filterData['image'] = '<i class="fa fa-users"></i>';
        } elseif ($services['serviceType'] == 'contact') {
            $filterData['image'] = '<i class="fa fa-user"></i>';
        } else {
        };

        return $filterData;
    }

    /**
     * For create the federation where conditions.
     *
     * @param type $filters federation data
     */
    public function federationfilter($filters)
    {
        $connector = ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') ? '' : $filters['connector'];
        if ($filters['entry'] != '') {
            switch ($filters['entry']) {
                case 'club':
                    if ($filters['input1'] != 'any') {
                        $this->where .= ($connector.' fg_cm_contact.fed_contact_id '.($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ').'(Select dfc.fed_contact_id from fg_cm_contact dfc where dfc.club_id='.$filters['input1'].' and dfc.fed_contact_id=fg_cm_contact.fed_contact_id))');
                    } else {
                        $subQueryClubSql = ($filters['condition'] == 'is not' ? " AND ( (dfc.id = '".$this->club->get('id')."') OR (dfc.id != '".$this->club->get('id')."' AND (dfc.is_federation=1 OR dfc.is_sub_federation=1)) )" : ' AND dfc.is_federation=0 AND dfc.is_sub_federation=0');
                        $this->where .= ($connector."fg_cm_contact.club_id = (SELECT dfc.id FROM fg_club AS dfc WHERE dfc.id = fg_cm_contact.club_id $subQueryClubSql))");
                    }
                    break;
                case 'sub_federation':
                    if ($filters['input1'] != 'any') {
                        $this->where .= ($connector.' fg_cm_contact.fed_contact_id '.($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ').'(Select dfc.fed_contact_id from fg_cm_contact dfc where dfc.club_id='.$filters['input1'].' and dfc.fed_contact_id=fg_cm_contact.fed_contact_id))');
                    } else {
                        $subQueryClubSql = ($filters['condition'] == 'is not' ? " AND ( (dfc.id = '".$this->club->get('id')."') OR (dfc.id != '".$this->club->get('id')."' AND (dfc.is_federation=0 AND dfc.is_sub_federation=0)) )" : ' AND dfc.is_sub_federation=1');
                        $this->where .= ($connector."fg_cm_contact.club_id = (SELECT dfc.id FROM fg_club AS dfc WHERE dfc.id = fg_cm_contact.club_id $subQueryClubSql))");
                    }
                    break;
            }
        }
    }
    /**
     * @param array  $filters       filter conditions
     * @param string $betweenDate   between condition
     * @param type   $checkingField checking field
     * @param string $type          is/not
     */
    private function betweenCondition($filters, $betweenDate = '', $checkingField, $type = 'is')
    {
        if ($checkingField == 'date_Range') {
            $checkingField = 'date_range';
        }
        //both input1 and input2  are null
        $bothNullJoinCondition = ($type === 'is') ? 'AND ' : 'OR';
        //any one input field is null
        $secondValueNullCondition = ($type === 'is') ? '>=' : '<';
        //both input field are not null
        $firstValueNullCondition = ($type === 'is') ? '<=' : '>';
        //equal handler
        $equalCondition = ($type === 'is') ? '!=' : '=';
        //between handler
        $betweenCondition = ($type === 'is') ? 'BETWEEN' : 'NOT BETWEEN';
        $nullCondition = ($type == 'is') ? 'IS NOT NULL' : 'IS NULL';
//both conditions are null
        if ($filters['input1'] == '' && $filters['input2'] == '') {
            $this->where .= " {$equalCondition}'' {$bothNullJoinCondition} ".$filters['entry']." {$nullCondition} {$bothNullJoinCondition} ".$filters['entry']."{$equalCondition}'00.00.0000' {$bothNullJoinCondition} ".$filters['entry']."{$equalCondition}'0000-00-00 00:00:00' ))";
        } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
            //first input value is null
            $this->where .= ($checkingField == 'date_range' || $checkingField == 'date') ? " {$secondValueNullCondition} ".$filters['input1'].' ))' : " {$secondValueNullCondition} ".FgUtility::getSecuredData($filters['input1'], $this->conn).')'." {$bothNullJoinCondition} (".$filters['entry']." {$nullCondition} OR ".$filters['entry']."{$equalCondition}'')) ";
        } elseif ($filters['input1'] == '' && $filters['input2'] != '') { //second input value is null
            $this->where .= ($checkingField === 'date_range' || $checkingField === 'date') ? " {$firstValueNullCondition} ".$filters['input2'].'))' : " {$firstValueNullCondition}".FgUtility::getSecuredData($filters['input2'], $this->conn).')'." {$bothNullJoinCondition} (".$filters['entry']." {$nullCondition} OR ".$filters['entry']."{$equalCondition}'')) ";
        } else {
            $this->where .= isset($betweenDate) ? " {$betweenCondition} $betweenDate))" : " {$betweenCondition} ".FgUtility::getSecuredData($filters['input1'], $this->conn).' AND '.FgUtility::getSecuredData($filters['input2'], $this->conn).'))';
        }
    }
    
    /**
     * For create analysis field where condition.
     *
     * @param string $filters analsis fields
     */
    public function membershipFilter($filters)
    {
        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where .= ' (';
        } else {
            $this->where .= ' '.$filters['connector'].'( ';
        }
        $entry = '';
        switch ($filters['entry']) {
            case 'fed_membership':
                $this->where .= 'fg_cm_contact.fed_membership_cat_id';
                $entry = 'number';
                $filters['entry'] = ' fg_cm_contact.fed_membership_cat_id ';

                break;
            case 'club_membership':
            case 'membership':
                $this->where .= 'fg_cm_contact.club_membership_cat_id';
                $filters['entry'] = ' fg_cm_contact.club_membership_cat_id ';
                $entry = 'number';
                break;
            case 'CMjoining_date':
            case 'CMleaving_date':
            case 'CMfirst_joining_date':
            case 'FMjoining_date':
            case 'FMleaving_date':
            case 'FMfirst_joining_date':
                //remove first two character for identify the table fields
                $filters['entry'] = substr($filters['entry'], 2);
                //seperate the federation membership related fields and other levels
                $this->where .= ($filters['type'] == 'FM') ? '(SELECT date(DFC.'.$filters['entry'].') FROM fg_cm_contact AS DFC WHERE DFC.id=fg_cm_contact.fed_contact_id AND fg_cm_contact.fed_contact_id IS NOT NULL)' : 'date(fg_cm_contact.'.$filters['entry'].' )';
                $betweenDate = " STR_TO_DATE('".FgUtility::getSecuredData($filters['input1'], $this->conn, false, false)."','".$this->mysqlDateFormat."') AND STR_TO_DATE('".FgUtility::getSecuredData($filters['input2'], $this->conn, false, false)."','".$this->mysqlDateFormat."')";
                if ($filters['input1'] != '') {
                    $filters['input1'] = " STR_TO_DATE('".FgUtility::getSecuredData($filters['input1'], $this->conn, false, false)."', '".$this->mysqlDateFormat."')";
                } elseif ($filters['input2'] != '') {
                    $filters['input2'] = " STR_TO_DATE('".FgUtility::getSecuredData($filters['input2'], $this->conn, false, false)."', '".$this->mysqlDateFormat."')";
                }
                $entry = 'date_range';
                break;
        }

        if ($filters['condition'] != '') {
            if ($filters['condition'] == 'is between') {
                //handle between
                $this->betweenCondition($filters, $betweenDate, $entry, 'is');
            } elseif ($filters['condition'] == 'is not between') {
                //handle not between
                $this->betweenCondition($filters, $betweenDate, $entry, 'not');
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is') {
                if ($entry != 'number') {
                    $this->where .= "='0000-00-00'))";
                } elseif ($entry == 'number') {
                    $this->where .= '<=0 OR '.$filters['entry'].' IS NULL ))';
                }
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not') {
                if ($entry != 'number') {
                    $this->where .= "!='00.00.0000'))";
                } elseif ($entry == 'number') {
                    $this->where .= '>=0))';
                }
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is not') {
                //check  condition is any
                if ($filters['input1'] == 'any') {
                    $this->where .= ' IS NULL ))';
                } else {
                    $this->where .= ($filters['data_type'] == 'date') ? '!= '.$filters['input1'].'))' : "!='".FgUtility::getSecuredData($filters['input1'], $this->conn)."'))";
                }
            } else {
                if ($filters['input1'] == 'any') {
                    $this->where .= ' > 0))';
                } else {
                    $this->where .= ($filters['data_type'] == 'date') ? '= '.$filters['input1'].'))' : "='".FgUtility::getSecuredData($filters['input1'], $this->conn)."'))";
                }
            }
        }

    }
}
