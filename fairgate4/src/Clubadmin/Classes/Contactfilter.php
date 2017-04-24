<?php

namespace Clubadmin\Classes;

use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\SponsorBundle\Util\Sponsorfilter;
use Common\UtilityBundle\Util\FgSettings;

/**
 * for create contact filter value
 */
class Contactfilter {

    //put your code here
    private $where;
    private $contact;
    private $filterDatas;
    private $joinarray = array();
    private $mysqlDateFormat;
    private $club;
    private $container;
    private $conn;
    private $typeArray;
    private $numbertypeArray;

    /**
     * @param type $contact     contact object
     * @param type $filterArray filterdata
     * @param type $club        service
     */
    public function __construct($container, $contact, $filterArray, $club) {

        $this->filterDatas = $filterArray;
        $this->contact = $contact;
        $this->where = '';
        $this->iCount = 0;
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->club = $club;
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');

        $this->typeArray = array('multiline', 'singleline', 'email', 'url', 'login email', 'number', 'number_Range');
        $this->numbertypeArray = array('number', 'number_Range');
    }

    /**
     * For create the where condition
     *
     * @return type
     */
    public function generateFilter() {

        foreach ($this->filterDatas as $filters) {
            $this->iCount++;

            foreach ($filters as $key => $filter) {

                list($filterType) = explode('-', $filter);

                //check the type of filter data
                switch ($filterType) {

                    case "SI" :

                        $this->systeminfofilter($filters);

                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "CO" :

                        $this->contactoptionfilter($filters);


                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "AF" :

                        $this->analysisfilter($filters);


                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "CF" :

                        $this->contactfieldfilter($filters);


                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "CC" :

                        $this->contactconnectionfilter($filters);

                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }
                        break;

                    case "ROLES" : case "FROLES": case "FILTERROLES":


                        $this->rolefilter($filters);


                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "TEAM" :

                        $this->teamfilter($filters);



                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "WORKGROUP" :

                        $this->workgroupfilter($filters);

                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "FI" :

                        $this->federationfilter($filters);


                        if ($this->iCount > 1) {

                            $this->where = "( " . $this->where . " )";
                        }

                        break;

                    case "SA":
                        $sponsorFilterObj = new Sponsorfilter($this->container, array());
                        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
                            $this->where.=" (";
                        } else {
                            $this->where.=" " . $filters['connector'] . "( ";
                        }

                        $this->where .= $sponsorFilterObj->analysisfilter($filters);
                        $this->where .= " AND fg_cm_contact.is_sponsor = 1 )";
                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }
                        break;

                    case "SS":
                        $sponsorFilterObj = new Sponsorfilter($this->container, array());
                        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
                            $this->where.=" (";
                        } else {
                            $this->where.=" " . $filters['connector'] . "( ";
                        }

                        $this->where .= $sponsorFilterObj->servicefilter($filters);
                        $this->where .= " AND fg_cm_contact.is_sponsor = 1 )";
                        if ($this->iCount > 1) {
                            $this->where = "( " . $this->where . " )";
                        }
                        break;
                }
            }
        }

        return $this->where;
    }

    /**
     * For create system info where condition
     * @param type $filters system filter values
     */
    public function systeminfofilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {
            if ($filters['entry'] == 'member_id') {
                $this->where.= 'fg_cm_contact.member_id';
            } elseif ($filters['entry'] == 'created_at') {
                $this->where.= "date(fg_cm_contact.created_at)";
            } elseif ($filters['entry'] == 'last_login') {
                $this->where.= "date(fg_cm_contact.last_login)";
            } else {
                $this->where.= "date(fg_cm_contact.last_updated)";
            }
        }
        if ($filters['data_type'] == 'date_Range' || $filters['data_type'] == 'date' ) {
            $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','".$this->mysqlDateFormat."')";
            if ($filters['input1'] != '') {
                $filters['input1'] = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "', '".$this->mysqlDateFormat."')";
            } else if (isset($filters['input2']) && $filters['input2'] != '') {
                $filters['input2'] = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "', '".$this->mysqlDateFormat."')";
            }
        }
        if ($filters['condition'] != '') {

            if ($filters['condition'] == 'is between') {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=" !='' AND " . $filters['entry'] . " IS NOT NULL AND " . $filters['entry'] . "!='00.00.0000' AND " . $filters['entry'] . "!='0000-00-00 00:00:00')";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    // $this->where.=" >= '" . FgUtility::getSecuredData($filters['input1']) . "')";
                    if ($filters['data_type'] == 'date_Range' || $filters['data_type'] == 'date') {
                        $this->where.=" >= " . $filters['input1'] . ")";
                    } else {
                        $this->where.=" >= " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    // $this->where.=" <= '" . FgUtility::getSecuredData($filters['input2']) . "')";
                    if ($filters['data_type'] == 'date_Range' || $filters['data_type'] == 'date') {
                        $this->where.=" <= " . $filters['input2'] . ")";
                    } else {
                        $this->where.=" <=" . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                    }
                } else {
                    $this->where .= isset($betweenDate) ? " BETWEEN $betweenDate)" : " BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                }
            } elseif ($filters['condition'] == 'is') {
                if ($filters['input1'] == '') {
                    $this->where.="='' OR " . $filters['entry'] . " IS NULL )";
                } else {
                    if ($filters['data_type'] == 'number') {
                        $this->where.="=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    } else if ($filters['data_type'] == 'date') {
                        $this->where.= "= " . $filters['input1'] . ")";
                    } else {
                        $this->where.="='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                    }
                }
            } elseif ($filters['condition'] == 'is not') {
                if ($filters['input1'] == '') {
                    $this->where.="!='' OR " . $filters['entry'] . " IS NOT NULL )";
                } else if ($filters['data_type'] == 'date') {
                    $this->where.= "!= " . $filters['input1'] . ")";
                } else {
                    $this->where.="!='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                }
            } else {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=" ='' OR " . $filters['entry'] . " IS NULL OR " . $filters['entry'] . "='00.00.0000' OR " . $filters['entry'] . "='0000-00-00 00:00:00')";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    //$this->where.=" < '" . FgUtility::getSecuredData($filters['input1']) . "')";
                    if ($filters['data_type'] == 'date_Range' || $filters['data_type'] == 'date') {
                        $this->where.= " < " . $filters['input1'] . ")";
                    } else {
                        $this->where.=$filters['entry'] . " < " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    // $this->where.=" > '" . FgUtility::getSecuredData($filters['input2']) . "')";
                    if ($filters['data_type'] == 'date_Range' || $filters['data_type'] == 'date') {
                        $this->where.= " > " . $filters['input2'] . ")";
                    } else {
                        $this->where.=$filters['entry'] . " > " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                    }
                } else {
                    // $this->where.=" NOT BETWEEN '" . FgUtility::getSecuredData($filters['input1']) . "' AND '" . FgUtility::getSecuredData($filters['input2']) . "')";
                    $this->where .= isset($betweenDate) ? "NOT BETWEEN $betweenDate)" : " NOT BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                }
            }
        }
    }

    /**
     * For create contact  option wher condition
     * @param type $filters contactoption where conditions
     */
    public function contactoptionfilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where.=" (";
        } else {
            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {
            if ($filters['entry'] == "contact_type") {
                $this->where.= 'fg_cm_contact.is_company';
            } elseif ($filters['entry'] == "membership") {
                $this->where.= 'fg_cm_contact.club_membership_cat_id';
            } elseif ($filters['entry'] == "fedmembership") {
                $this->where.= 'fg_cm_contact.fed_membership_cat_id';
            }elseif ($filters['entry'] == "nlsubscription") {
                $this->where.= 'fg_cm_contact.is_subscriber';
            }elseif ($filters['entry'] == "internalareaaccess") {
                $this->where.= 'fg_cm_contact.intranet_access';
            }elseif ($filters['entry'] == "internalareainvisible") {
                $this->where.= 'fg_cm_contact.is_stealth_mode';
            } elseif ($filters['entry'] == "fed_membership") {
                $this->where.= 'fg_cm_contact.fed_membership_cat_id';
            }
        }

        if ($filters['condition'] != '') {
            if ($filters['entry'] == "contact_type") {
                if ($filters['input1'] == 'company') {

                    if ($filters['condition'] == 'is') {

                        $this->where.="=1)";
                    } else {

                        $this->where.="=0)";
                    }
                } else {
                    if ($filters['condition'] == 'is') {
                        $this->where.="=0)";
                    } else {
                        $this->where.="=1)";
                    }
                }
            } elseif ($filters['entry'] == "fedmembership") {
                $subquery = "(SELECT id FROM fg_cm_membership WHERE fg_cm_membership.is_fed_category=1 and fg_cm_membership.club_id = " . $this->club->get('federation_id') . " )";
                $subquery1 = "(SELECT id FROM fg_cm_membership WHERE fg_cm_membership.is_fed_category=0 and fg_cm_membership.club_id = " . $this->club->get('id') . " )";
                if ($filters['condition'] == 'is not') {
                    if ($filters['input1'] == 'yes') {
                        $this->where.=" IN $subquery1 OR fg_cm_contact.fed_membership_cat_id IS NULL)";
                    } else {
                        $this->where.=" IN $subquery  )";
                    }
                } else {
                    if ($filters['input1'] == 'yes') {
                        $this->where.=" IN $subquery  )";
                    } else {
                        $this->where.=" IN $subquery1 OR fg_cm_contact.fed_membership_cat_id IS NULL)";
                    }
                }
            } elseif ($filters['entry'] == "nlsubscription" || $filters['entry'] == "internalareaaccess" || $filters['entry'] == "internalareainvisible") {
                if ($filters['condition'] == 'is not') {
                    $this->where.=($filters['input1'] == 'yes')? "=0)": "=1)";
                } else {
                    $this->where.=($filters['input1'] == 'yes')? "=1)": "=0)";
                }
            }elseif($filters['entry'] == "sponsor"){
               if ($filters['condition'] == 'is') {
                   $chk = '=';
               }else{
                   $chk='!=';
               }
                 /* SUBQUERY : get sponsor type
                  *     if condition :sponsor=1
                 *  true   if condition:there is entry in sm_bookings table
                 *              true: prospect
                 *              false: (active,future,former sponsor)
                 *  false  not sponsor
                 */
                   $subquery = " IF (fg_cm_contact.is_sponsor = 1,"
                            . " IF((select count(fg_sm_bookings.contact_id) from fg_sm_bookings where fg_sm_bookings.contact_id=fg_cm_contact.id AND fg_sm_bookings.is_deleted = 0 )= 0,"
                            . "'prospect',"
                            . "(Select CASE"
                                    . " WHEN (fg_sm_bookings.begin_date > now() AND fg_sm_bookings.is_deleted= 0 AND fg_sm_bookings.club_id={$this->club->get("id")}) THEN 'future_sponsor' "
                                    . " WHEN (fg_sm_bookings.begin_date <= now() AND fg_sm_bookings.is_deleted= 0 AND (fg_sm_bookings.end_date >= now() OR fg_sm_bookings.end_date IS NULL) AND fg_sm_bookings.club_id={$this->club->get("id")}) THEN 'active_sponsor'"
                                    . " WHEN (fg_sm_bookings.end_date < now() AND fg_sm_bookings.is_deleted= 0 AND fg_sm_bookings.club_id={$this->club->get("id")}) THEN 'former_sponsor' END AS Gsponsor FROM fg_sm_bookings WHERE fg_sm_bookings.contact_id=fg_cm_contact.id "
                                    . " ORDER BY  (CASE WHEN  Gsponsor='active_sponsor' then 1 WHEN Gsponsor='future_sponsor' THEN 2 WHEN Gsponsor='former_sponsor' then 3 ELSE 4 END) asc limit 0,1) ),"
                            . "'' ) ";

               $this->where.=" $subquery  {$chk}'{$filters['input1']}')";
            }
            else {
                if ($filters['condition'] == 'is not') {
                    if ($filters['input1'] == 'any') {
                        $this->where.=" IS NULL)";
                    } else {
                        $this->where.="!=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . " OR fg_cm_contact.club_membership_cat_id IS NULL) ";
                    }
                } elseif ($filters['condition'] == 'is') {
                    if ($filters['input1'] == 'any') {
                        $this->where.=" >0)";
                    } else {
                        $this->where.="=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    }
                }
            }
        }
    }

    /**
     * For create analysis field where condition
     * @param string $filters analsis fields
     */
    private function nlSubcriptionFilter() {

    }

    /**
     * For create analysis field where condition
     * @param string $filters analsis fields
     */
    public function analysisfilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }
        $entry = '';
        switch ($filters['entry']) {
            case '':
                break;
            case 'joining_date':
            case 'leaving_date':
                $this->where.= 'date(fg_cm_contact.' . $filters['entry'] . ' )';
                $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','".$this->mysqlDateFormat."')";
                if ($filters['input1'] != '') {
                    $filters['input1'] = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "', '".$this->mysqlDateFormat."')";
                } else if ($filters['input2'] != '') {
                    $filters['input2'] = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "', '".$this->mysqlDateFormat."')";
                }
                $entry = "date_range";
                break;
            case 'birth_year':
                $this->where.= " EXTRACT(YEAR FROM `" . $this->container->getParameter('system_field_dob') . "`) ";
                $filters['entry'] = " EXTRACT(YEAR FROM `" . $this->container->getParameter('system_field_dob') . "`) ";
                $entry = "number";
                break;
            case 'no_of_logins':
                $this->where.=" fg_cm_contact.login_count ";
                $filters['entry'] = " fg_cm_contact.login_count ";
                $entry = "number";
                break;
            case 'age':
                //convert a date field into age format
                $this->where.=" DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`4` )),'%y')";
                $filters['entry'] = "DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`4` )),'%y')";
                $entry = "number";
                break;
            default:
                $filters['entry'] = "DATE_FORMAT(FROM_DAYS(DATEDIFF(CURRENT_DATE,`4` )),'%y')";
                break;
        }

        if ($filters['condition'] != '') {
            if ($filters['condition'] == 'is between') {
                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=" !='' AND " . $filters['entry'] . " IS NOT NULL  AND " . $filters['entry'] . "!='00.00.0000' AND " . $filters['entry'] . "!='0000-00-00 00:00:00')";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    if ($entry == 'date_range') {
                        $this->where.= " > " . $filters['input1'] . ")";
                    } else {
                        $this->where.=" >= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    if ($entry == 'date_range') {
                        $this->where.= " < " . $filters['input2'] . ")";
                    } else {
                        $this->where.=" <= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                    }
                } else {
                    $this->where .= isset($betweenDate) ? " BETWEEN $betweenDate)" : " BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                }
            } elseif ($filters['condition'] == 'is not between') {
                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=" ='' OR " . $filters['entry'] . " IS NULL OR " . $filters['entry'] . "='00.00.0000' OR " . $filters['entry'] . "='0000-00-00 00:00:00')";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    if ($entry == 'date_range') {
                        $this->where.= " < " . $filters['input1'] . ")";
                    } else {
                        $this->where.=" < '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    if ($entry == 'date_range') {
                        $this->where.= " > " . $filters['input2'] . ")";
                    } else {
                        $this->where.=" > '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                    }
                } else {
                    $this->where .= isset($betweenDate) ? "NOT BETWEEN $betweenDate)" : " NOT BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                }
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is' && $entry != 'number') {
                $this->where.="='00.00.0000')";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not' && $entry != 'number' ) {
                $this->where.="!='00.00.0000')";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is' && $entry == 'number' ) {
                $this->where.="<0 || " . $filters['entry'] . " IS NULL )";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not' && $entry == 'number' ) {
                $this->where.=">=0)";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is not') {
                if ($filters['data_type'] == 'date') {
                    $this->where.= "!= " . $filters['input1'] . ")";
                } else {
                    $this->where.="!='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                }
            } else {
                if ($filters['data_type'] == 'date') {
                    $this->where.= "= " . $filters['input1'] . ")";
                } else {
                    $this->where.="='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                }
            }
        }
    }

    /**
     * For create the contact filter wher condition
     * @param type $filters contact filter values
     */
    public function contactfieldfilter($filters) {
        $exsql = "SELECT input_type as input_type FROM fg_cm_attribute WHERE id =" . $filters['entry'];
        $entryType = $this->conn->executeQuery($exsql)->fetch();
        $this->conn->close();

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '' && ($filters['condition'] == 'contains' || $filters['condition'] == 'contains not' || $filters['condition'] == 'begins with' || $filters['condition'] == 'begins not with' || $filters['condition'] == 'end with' || $filters['condition'] == 'end not with')) {

        } else {

            if ($entryType['input_type'] == 'date') {
                //$this->where.=" date_format(`" . $filters['entry'] . "`,'{$this->mysqlDateFormat}')";
                $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','".$this->mysqlDateFormat."') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','".$this->mysqlDateFormat."')";
                $this->where.=" `" . $filters['entry'] . "` ";
                $filters['input1'] = $filters['input1'] != '' ? " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "', '".$this->mysqlDateFormat."')" : '';
                $filters['input2'] = $filters['input2'] != '' ? " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "', '".$this->mysqlDateFormat."')" : '';
            } elseif ($filters['data_type'] == 'select') {
                $this->where.='';
            } elseif ($filters['data_type'] == 'number' || $filters['data_type'] == 'number_Range') {
                $filters['input1'] = str_replace(',', '.', $filters['input1']);
                $filters['input2'] = str_replace(',', '.', $filters['input2']);
                $this->where.=" `" . $filters['entry'] . "`";
            } else {

                $this->where.=" `" . $filters['entry'] . "`";
            }
        }

        //check if the entry is subfed field or fed field
        $additionalWhere = '';
        if (in_array($filters['entry'], $this->club->get('fedFields'))) {
            $additionalWhere = " OR mf.contact_id IS NULL";
        } elseif (in_array($filters['entry'], $this->club->get('subFedFields'))) {
            $additionalWhere = " OR msf.contact_id IS NULL";
        }

        if ($filters['condition'] != '') {

            if ($filters['condition'] == 'contains' && $filters['input1'] != '') {

                $this->where.=" `" . $filters['entry'] . "` LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['condition'] == 'contains not' && $filters['input1'] != '') {

                $this->where.=" `" . $filters['entry'] . "` NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%'  OR  `" . $filters['entry'] . "` IS NULL )";
            } elseif ($filters['condition'] == 'contains' && $filters['input1'] == '') {

                $this->where.=" `" . $filters['entry'] . "` IS NULL  OR `" . $filters['entry'] . "`='' $additionalWhere )";
            } elseif ($filters['condition'] == 'contains not' && $filters['input1'] == '') {

                $this->where.=" `" . $filters['entry'] . "` IS NOT NULL AND `" . $filters['entry'] . "` !='')"; //first start
            } elseif ($filters['condition'] == 'begins with' && $filters['input1'] != '') {

                $this->where.=" `" . $filters['entry'] . "` LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['condition'] == 'begins not with' && $filters['input1'] != '') {

                $this->where.=" `" . $filters['entry'] . "` NOT LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%'  OR  `" . $filters['entry'] . "` IS NULL )";
            } elseif ($filters['condition'] == 'begins with' && $filters['input1'] == '') {

                $this->where.=" `" . $filters['entry'] . "` IS NULL  OR `" . $filters['entry'] . "`='' $additionalWhere )";
            } elseif ($filters['condition'] == 'begins not with' && $filters['input1'] == '') {

                $this->where.=" `" . $filters['entry'] . "` IS NOT NULL AND `" . $filters['entry'] . "` !='')";
            } elseif ($filters['condition'] == 'end with' && $filters['input1'] != '') {

                $this->where.=" `" . $filters['entry'] . "` LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
            } elseif ($filters['condition'] == 'end not with' && $filters['input1'] != '') {

                $this->where.=" `" . $filters['entry'] . "` NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "'  OR  `" . $filters['entry'] . "` IS NULL )";
            } elseif ($filters['condition'] == 'end with' && $filters['input1'] == '') {

                $this->where.=" `" . $filters['entry'] . "` IS NULL  OR `" . $filters['entry'] . "`='' $additionalWhere )";
            } elseif ($filters['condition'] == 'end not with' && $filters['input1'] == '') {

                $this->where.=" `" . $filters['entry'] . "` IS NOT NULL AND `" . $filters['entry'] . "` !='')";
            } elseif ($filters['condition'] == 'is' && (in_array($entryType['input_type'], $this->typeArray)) && $filters['input1'] == '') {

                $this->where.=" ='' OR `" . $filters['entry'] . "` IS NULL  OR `" . $filters['entry'] . "`='' OR `" . $filters['entry'] . "`='0000-00-00' OR `" . $filters['entry'] . "`='0000-00-00 00:00:00' $additionalWhere)";
            } elseif ($filters['condition'] == 'is' && $filters['input1'] != '') {

                if (in_array($filters['data_type'], $this->numbertypeArray)) {

                    $this->where.=" =" . FgUtility::getSecuredData($filters['input1'], $this->conn) . " )";
                } elseif ($filters['data_type'] == 'select') {

                    $this->where.= " CONCAT(';',`" . $filters['entry'] . "`,';') LIKE '%;" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ";%' )";
                } else if ($filters['data_type'] == 'date') {
                    $this->where.= "= " . $filters['input1'] . ")";
                } else {

                    $this->where.=" ='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                }
            } elseif ($filters['condition'] == 'is' && $filters['input1'] == '') {

                $this->where.=" ='' OR `" . $filters['entry'] . "` IS NULL OR `" . $filters['entry'] . "`='0000-00-00' OR `" . $filters['entry'] . "`='0000-00-00 00:00:00')";
            } elseif ($filters['condition'] == 'is not' && (in_array($entryType['input_type'], $this->typeArray)) && $filters['input1'] == '') {

                $this->where.=" !='' AND `" . $filters['entry'] . "` IS NOT NULL )";
            } elseif ($filters['condition'] == 'is not' && $filters['input1'] == '') {

                $this->where.=" !='' OR `" . $filters['entry'] . "` IS NOT NULL OR `" . $filters['entry'] . "`!='0000-00-00' OR `" . $filters['entry'] . "`!='0000-00-00 00:00:00')";
            } elseif ($filters['condition'] == 'is not' && $filters['input1'] != '') {

                if (in_array($filters['data_type'], $this->numbertypeArray)) {

                    $this->where.=" !=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                } elseif ($filters['data_type'] == 'select') {

                    $this->where.= " CONCAT(';',`" . $filters['entry'] . "`,';') NOT LIKE '%;" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ";%' OR `" . $filters['entry'] . "`='' OR `" . $filters['entry'] . "` IS NULL)";
                } elseif ($filters['data_type'] == 'date') {

                    $this->where.=" !=" . $filters['input1'] . ")";
                } else {

                    $this->where.=" !='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                }
            } elseif ($filters['condition'] == 'is between') {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    if ($entryType['input_type'] == 'date') {
                        $this->where.=" !='' AND `" . $filters['entry'] . "` IS NOT NULL AND " . $filters['entry'] . "!='00.00.0000' AND " . $filters['entry'] . "!='0000-00-00 00:00:00')";
                    } else {
                        $this->where.=" !='' AND `" . $filters['entry'] . "` IS NOT NULL )";
                    }
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {

                    if ($entryType['input_type'] == 'date') {
                        $this->where.= " >= " . $filters['input1'] . ")";
                        // $this->where.=" >= '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input1']))) . "' AND (`" . $filters['entry'] . "` IS NOT NULL OR `" . $filters['entry'] . "`!=''))";
                    } else {

                        $this->where.=" >= " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND (`" . $filters['entry'] . "` IS NOT NULL OR `" . $filters['entry'] . "`!=''))";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {

                    if ($entryType['input_type'] == 'date') {
                        $this->where.= " <= " . $filters['input2'] . ")";
                        // $this->where.=" <= '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input2']))) . "' AND (`" . $filters['entry'] . "` IS NOT NULL OR `" . $filters['entry'] . "`!=''))";
                    } else {
                        $this->where.=" <= " . FgUtility::getSecuredData($filters['input2'], $this->conn) . " AND (`" . $filters['entry'] . "` IS NOT NULL OR `" . $filters['entry'] . "`!=''))";
                    }
                } else {
                    if ($entryType['input_type'] == 'date') {
                        $this->where .= isset($betweenDate) ? " BETWEEN $betweenDate)" : " BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                        //$this->where.=" BETWEEN '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input1']))) . "' AND '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input2']))) . "')";
                    } else {
                        $this->where.=" BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                    }
                }
            } else {

                if ($filters['input1'] == '' && $filters['input2'] == '') {


                    if ($entryType['input_type'] == 'date') {

                        $this->where.=" ='' OR `" . $filters['entry'] . "` IS NULL   OR `" . $filters['entry'] . "`='00.00.0000' OR `" . $filters['entry'] . "`='0000-00-00 00:00:00' $additionalWhere)";
                    } else {

                        $this->where.=" ='' OR `" . $filters['entry'] . "` IS NULL   $additionalWhere)";
                    }
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    if ($entryType['input_type'] == 'date') {
                        $this->where.= " < " . $filters['input1'] . ")";
                        // $this->where.=" < '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input1']))) . "' OR (`" . $filters['entry'] . "` IS NULL OR `" . $filters['entry'] . "`='') $additionalWhere)";
                    } else {
                        $this->where.=" < " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " OR (`" . $filters['entry'] . "` IS NULL OR `" . $filters['entry'] . "`='') $additionalWhere)";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    if ($entryType['input_type'] == 'date') {
                        $this->where.= " > " . $filters['input2'] . ")";
                        //$this->where.=" > '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input2']))) . "' OR (`" . $filters['entry'] . "` IS NULL OR `" . $filters['entry'] . "`='') $additionalWhere)";
                    } else {
                        $this->where.=" > " . FgUtility::getSecuredData($filters['input2'], $this->conn) . " OR (`" . $filters['entry'] . "` IS NULL OR `" . $filters['entry'] . "`='') $additionalWhere)";
                    }
                } else {
                    if ($entryType['input_type'] == 'date') {
                        $this->where .= isset($betweenDate) ? "NOT BETWEEN $betweenDate)" : " NOT BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                        //$this->where.=" NOT BETWEEN '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input1']))) . "' AND '" . FgUtility::getSecuredData(strftime('%Y-%m-%d', strtotime($filters['input2']))) . "')";
                    } else {
                        $this->where.=" NOT BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                    }
                }
            }
        }
    }

    /**
     * For create the connection where condition
     * @param type $filters connection filter values
     */
    public function contactconnectionfilter($filters) {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['condition'] != '') {

            if ($filters['condition'] == 'contains') {
                $this->where.=" fg_cm_contact.comp_def_contact_fun LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' ";
            } elseif ($filters['condition'] == 'contains not') {

                $this->where.=" fg_cm_contact.comp_def_contact_fun LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%'";
            } elseif ($filters['condition'] == 'begins with') {
                $this->where.=" fg_cm_contact.comp_def_contact_fun LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' ";
            } elseif ($filters['condition'] == 'begins not with') {
                $this->where.=" fg_cm_contact.comp_def_contact_fun NOT LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' ";
            } elseif ($filters['condition'] == 'end with') {
                $this->where.=" fg_cm_contact.comp_def_contact_fun LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' ";
            } elseif ($filters['condition'] == 'end not with') {
                $this->where.=" fg_cm_contact.comp_def_contact_fun NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' ";
            } elseif ($filters['condition'] == 'is') {

                if ($filters['entry'] == 'household_contact') {
                    $this->where.=" fg_cm_contact.id IN(SELECT dflc.linked_contact_id FROM fg_cm_linkedcontact AS dflc WHERE dflc.club_id='" . $this->club->get('id') . "' AND dflc.type='household') ";
                } elseif ($filters['entry'] == 'household_main_contact') {
                    $this->where.="( fg_cm_contact.is_household_head=1)";
                } elseif ($filters['entry'] == 'household_relation') {
                    $this->where.=" fg_cm_contact.id IN(SELECT dflc.linked_contact_id FROM fg_cm_linkedcontact AS dflc WHERE dflc.club_id='" . $this->club->get('id') . "' AND dflc.type='household' AND dflc.relation_id='" . $filters['input1'] . "') ";
                } elseif ($filters['entry'] == 'other_relation') {
                    $this->where.=" fg_cm_contact.id IN(SELECT dflc.linked_contact_id FROM fg_cm_linkedcontact AS dflc WHERE dflc.club_id='" . $this->club->get('id') . "' AND dflc.type='otherpersonal' AND dflc.relation_id='" . $filters['input1'] . "') ";
                } elseif ($filters['entry'] == 'company_main_contact') {

                    $this->where.=" fg_cm_contact.id IN (SELECT dfc.comp_def_contact from fg_cm_contact as dfc WHERE (dfc.club_id = '" . $this->club->get('id') . "' AND dfc.comp_def_contact IS NOT NULL))";
                }
            } elseif ($filters['condition'] == 'is not') {

                if ($filters['entry'] == 'household_contact') {
                    $this->where.=" fg_cm_contact.id NOT IN(SELECT dflc.linked_contact_id FROM fg_cm_linkedcontact AS dflc WHERE dflc.club_id='" . $this->club->get('id') . "' AND dflc.type='household') ";
                } elseif ($filters['entry'] == 'household_main_contact') {
                    $this->where.=" (fg_cm_contact.is_household_head !=1)";
                } elseif ($filters['entry'] == 'household_relation') {
                    $this->where.=" fg_cm_contact.id NOT IN(SELECT dflc.linked_contact_id FROM fg_cm_linkedcontact AS dflc WHERE dflc.club_id='" . $this->club->get('id') . "' AND dflc.type='household' AND dflc.relation_id='" . $filters['input1'] . "') ";
                } elseif ($filters['entry'] == 'other_relation') {
                    $this->where.=" fg_cm_contact.id NOT IN(SELECT dflc.linked_contact_id FROM fg_cm_linkedcontact AS dflc WHERE dflc.club_id='" . $this->club->get('id') . "' AND dflc.type='otherpersonal' AND dflc.relation_id='" . $filters['input1'] . "') ";
                } elseif ($filters['entry'] == 'company_main_contact') {
                    $this->where.=" fg_cm_contact.id NOT IN (SELECT dfc.comp_def_contact from fg_cm_contact as dfc WHERE (dfc.club_id = '" . $this->club->get('id') . "' AND dfc.comp_def_contact IS NOT NULL)) ";
                }
            }
            $this->where.=")";
        }
    }

    /**
     * For create the role filter where condition
     * @param type $filters role filter datas
     */
    public function rolefilter($filters) {
        $where = array();
        if ($filters['entry'] != '') {
            $where[] = "dcrf.category_id = '" . $filters['entry'] . "'";
            if ($filters['input1'] != 'any') {
                $where[] = "dcrf.role_id = '" . $filters['input1'] . "'";
                if (isset($filters['input2']) && $filters['input2'] != 'any') {
                    $where[] = "dcrf.function_id = '" . $filters['input2'] . "'";
                }
            }
        }

        if (count($where) > 0) {
            $connector = ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') ? '' : $filters['connector'];
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where.= ($connector . '( fg_cm_contact.id' . ($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ') . $subQuery . ')');
        }
    }

    /**
     * For create the workgroup where condition
     * @param type $filters workgroup data
     */
    public function workgroupfilter($filters) {
        $where = array();
        if ($filters['entry'] != '') {
            $where[] = "dcrf.category_id = '" . $filters['entry'] . "'";
            if ($filters['input1'] != 'any') {
                $where[] = "dcrf.role_id = '" . $filters['input1'] . "'";
                if ($filters['input2'] != 'any' && $filters['input2'] != '') {
                    $where[] = "dcrf.function_id = '" . $filters['input2'] . "'";
                }
            }
            $where[] = "drc.assined_club_id = '" . $this->club->get('id') . "'";
        }

        if (count($where) > 0) {
            $connector = ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') ? '' : $filters['connector'];
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where.= ($connector . '( fg_cm_contact.id' . ($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ') . $subQuery . ')');
        }
    }

    /**
     * For create team where condition
     * @param type $filters team filter data
     */
    public function teamfilter($filters) {
        $where = array();
        if ($filters['entry'] != '') {
            $where[] = "drr.team_category_id = '" . $filters['entry'] . "'";
            $where[] = "dcrf.category_id = '" . $this->club->get('club_team_id') . "'";
            if ($filters['input1'] != 'any') {
                $where[] = "dcrf.role_id = '" . $filters['input1'] . "'";
                if ($filters['input2'] != 'any' && $filters['input2'] != '') {
                    $where[] = "dcrf.function_id = '" . $filters['input2'] . "'";
                }
            }
        }

        if (count($where) > 0) {
            $connector = ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') ? '' : $filters['connector'];
            $subQuery = "(SELECT drc.contact_id FROM fg_rm_role AS drr INNER JOIN fg_rm_category_role_function AS dcrf ON drr.id = dcrf.role_id  INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (" . implode(' AND ', $where) . "))";
            $this->where.= ($connector . '( fg_cm_contact.id' . ($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ') . $subQuery . ')');
        }
    }

    /**
     * For create the federation where conditions
     * @param type $filters federation data
     */
    public function federationfilter($filters) {
        $connector = ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') ? '' : $filters['connector'];
        if ($filters['entry'] != '') {
            switch ($filters['entry']) {
                case 'club':
                    if ($filters['input1'] != 'any') {
                        $this->where.=($connector . " (fg_cm_contact.club_id " . ($filters['condition'] == 'is not' ? '!=' : '=') . " '" . $filters['input1'] . "')");
                    } else {
                        $subQueryClubSql = ($filters['condition'] == 'is not' ? " AND ( (dfc.id = '" . $this->club->get('id') . "') OR (dfc.id != '" . $this->club->get('id') . "' AND (dfc.is_federation=1 OR dfc.is_sub_federation=1)) )" : ' AND dfc.is_federation=0 AND dfc.is_sub_federation=0');
                        $this->where.=($connector . "(fg_cm_contact.club_id = (SELECT dfc.id FROM fg_club AS dfc WHERE dfc.id = fg_cm_contact.club_id $subQueryClubSql))");
                    }
                    break;
                case 'sub_federation':
                    if ($filters['input1'] != 'any') {
                        $this->where.=($connector . " (fg_cm_contact.club_id " . ($filters['condition'] == 'is not' ? '!=' : '=') . " '" . $filters['input1'] . "')");
                    } else {
                        $subQueryClubSql = ($filters['condition'] == 'is not' ? " AND ( (dfc.id = '" . $this->club->get('id') . "') OR (dfc.id != '" . $this->club->get('id') . "' AND (dfc.is_federation=0 AND dfc.is_sub_federation=0)) )" : ' AND dfc.is_sub_federation=1');
                        $this->where.=($connector . "(fg_cm_contact.club_id = (SELECT dfc.id FROM fg_club AS dfc WHERE dfc.id = fg_cm_contact.club_id $subQueryClubSql))");
                    }
                    break;
                case 'ceb_function':
                    if ($filters['input1'] != 'any') {
                        $subQuery = "(SELECT drc.contact_id FROM fg_rm_category_role_function AS dcrf INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id INNER JOIN fg_club AS fc ON fc.id = drc.assined_club_id WHERE dcrf.function_id='" . $filters['input1'] . "' AND drc.assined_club_id = fg_cm_contact.club_id AND fc.is_federation=0 AND fc.is_sub_federation=0)";
                        $this->where.= ($connector . '( fg_cm_contact.id' . ($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ') . $subQuery . ')');
                    } else {
                        $clubType = $this->club->get('type');
                        if ($clubType == 'federation') {
                            $clubId = $this->club->get('id');
                        } else {
                            $clubId = $this->club->get('federation_id');
                        }
                        $subQuery = "(SELECT drc.contact_id FROM fg_rm_category AS drcat INNER JOIN fg_rm_function AS drf ON (drcat.id = drf.category_id AND drcat.club_id='$clubId' AND drcat.is_workgroup=1 AND drf.is_federation=1 AND drf.is_active=1 )INNER JOIN fg_rm_category_role_function AS dcrf ON (drf.id = dcrf.function_id) INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id INNER JOIN fg_club AS fc ON fc.id = drc.assined_club_id WHERE drc.assined_club_id = fg_cm_contact.club_id AND fc.is_federation=0 AND fc.is_sub_federation=0 AND drcat.contact_assign= 'manual')";
                        $this->where.= ($connector . '( fg_cm_contact.id' . ($filters['condition'] == 'is not' ? ' NOT IN ' : ' IN ') . $subQuery . ')');
                    }
                    break;
            }
        }
    }

}
