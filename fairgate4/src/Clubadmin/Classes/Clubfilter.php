<?php

namespace Clubadmin\Classes;

use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contactfilter
 *
 * @author Jinesh.m <jinesh.m@pitsolutions.com>
 */
class Clubfilter
{

    //put your code here
    private $where;
    private $contact;
    private $filterDatas;
    private $mysqlDateFormat;
    private $club;
    private $container;
    private $conn;
    private $typeArray;
    private $numbertypeArray;

    /**
     * class for create filter field
     * @param type $contact     contactid
     * @param type $filterArray array
     * @param type $club        clubobject
     */
    public function __construct($container, $contact, $filterArray, $club)
    {

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
     * get the filter
     *
     * @return type
     */
    public function generateClubFilter()
    {

        foreach ($this->filterDatas as $filters) {
            $this->iCount++;
            $filterType =  $filters['type'];
            
            list($filterType) = explode('-', $filters['type']);
           
            switch ($filterType) {

                case "SI" :

                    $this->systeminfofilter($filters);

                    if ($this->iCount > 1) {
                        $this->where = "( " . $this->where . " )";
                    }

                    break;

                case "CO" :

                    $this->cluboptionfilter($filters);


                    if ($this->iCount > 1) {

                        $this->where = "( " . $this->where . " )";
                    }

                    break;

                case "CD" :

                    $this->clubdatafilter($filters);


                    if ($this->iCount > 1) {

                        $this->where = "( " . $this->where . " )";
                    }

                    break;

                case "class" :


                    $this->classificationfilter($filters);


                    if ($this->iCount > 1) {

                        $this->where = "( " . $this->where . " )";
                    }

                    break;

                case "AF" :


                    $this->additionalFieldfilter($filters);


                    if ($this->iCount > 1) {

                        $this->where = "( " . $this->where . " )";
                    }

                    break;
                }
            
        }

        return $this->where;
    }

    /**
     * system information filter
     *
     * @param type $filters
     */
    public function systeminfofilter($filters)
    {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {
            if ($filters['entry'] == 'CLUB_ID') {
                $filters['entry'] = 'fc.id';
            } elseif ($filters['entry'] == 'FED_MEMBERS') {
                //check if the club is federation or sub federation
                if ($this->club->get("type") == 'federation') {
                    $clubId = $this->club->get("id");
                } elseif ($this->club->get("type") == 'sub_federation') {

                    $clubId = $this->club->get("federation_id");
                }
                //$filters['entry'] = "(SELECT COUNT(fg_cm_contact.id) FROM fg_cm_contact WHERE fg_cm_contact.club_id=fc.id AND fg_cm_contact.membership_cat_id IN( SELECT id FROM fg_cm_membership WHERE fg_cm_membership.club_id={$clubId} AND fg_cm_membership.is_fed_category=1) )";
                $filters['entry'] = "10";
            } elseif ($filters['entry'] == 'LAST_CONTACT_EDIT') {
                $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','" . $this->mysqlDateFormat . "') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','" . $this->mysqlDateFormat . "')";
                if ($filters['input1'] != '') {
                    $filters['input1'] = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "', '" . $this->mysqlDateFormat . "')";
                } else if ($filters['input2'] != '') {
                    $filters['input2'] = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "', '" . $this->mysqlDateFormat . "')";
                }

                $dateSelect = ($filters['condition'] == 'is' || $filters['condition'] == 'is not') ? 'date(fg_cm_change_log.date)' : 'date(fg_cm_change_log.date)';
                //$filters['entry'] = "(SELECT $dateSelect  FROM fg_club INNER JOIN fg_cm_contact ON fg_club.id=fg_cm_contact.club_id INNER JOIN fg_cm_change_log ON fg_cm_change_log.contact_id=fg_cm_contact.id WHERE fc.id=fg_cm_contact.club_id AND fg_cm_change_log.is_confirmed != 0 ORDER BY fg_cm_change_log.id desc LIMIT 0,1)";
                $filters['entry'] = "date('2017-07-07 08:10:20')";
            } elseif ($filters['entry'] == 'LAST_ADMIN_LOGIN') {

                $filters['condition'] = '';
                $this->where.=" 1=1)";
            }
        }

        if ($filters['condition'] != '') {

            if ($filters['condition'] == 'is between') {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NOT NULL )";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    if ($filters['data_type'] == 'date_Range') {
                        $this->where.=$filters['entry'] . " >= " . $filters['input1'] . ")";
                    } else {
                        $this->where.=$filters['entry'] . " >= " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {


                    if ($filters['data_type'] == 'date_Range') {
                        $this->where.=$filters['entry'] . " <= " . $filters['input2'] . ")";
                    } else {
                        $this->where.=$filters['entry'] . " <= " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                    }
                } else {
                    $this->where .= isset($betweenDate) ? $filters['entry'] . " BETWEEN $betweenDate)" : $filters['entry'] . " BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                }
            } elseif ($filters['condition'] == 'is') {

                if ($filters['input1'] == '') {
                    $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NULL )";
                } else {
                    if ($filters['data_type'] == 'number') {
                        $this->where.=$filters['entry'] . "= " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    } else if ($filters['data_type'] == 'date') {
                        $this->where.= $filters['entry'] . "= " . $filters['input1'] . ")";
                    } else {
                        $this->where.= $filters['entry'] . "= " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    }
                }
            } elseif ($filters['condition'] == 'is not') {
                if ($filters['input1'] == '') {
                    $this->where.=$filters['entry'] . "!='' OR " . $filters['entry'] . " IS NOT NULL )";
                } else if ($filters['data_type'] == 'date') {
                    $this->where.= $filters['entry'] . " != " . $filters['input1'] . ")";
                } else {
                    $this->where.= $filters['entry'] . " != " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                }
            } else {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NULL )";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    if ($filters['data_type'] == 'date_Range') {
                        $this->where.=$filters['entry'] . " < " . $filters['input1'] . ")";
                    } else {
                        $this->where.=$filters['entry'] . " < " . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    if ($filters['data_type'] == 'date_Range') {
                        $this->where.=$filters['entry'] . " > " . $filters['input2'] . ")";
                    } else {
                        $this->where.=$filters['entry'] . " > " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                    }
                } else {
                    $this->where .= isset($betweenDate) ? $filters['entry'] . "NOT BETWEEN $betweenDate)" : $filters['entry'] . " NOT BETWEEN " . FgUtility::getSecuredData($filters['input1'], $this->conn) . " AND " . FgUtility::getSecuredData($filters['input2'], $this->conn) . ")";
                }
            }
        }
    }

    /**
     * club option filter
     * @param type $filters
     */
    public function cluboptionfilter($filters)
    {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {
            $this->where.=" (";
        } else {
            $this->where.=" " . $filters['connector'] . "( ";
        }
        $fieldAnySub = $filters['entry'];
        if ($filters['entry'] != '') {
            if ($filters['entry'] == "subfed") {

                if ($this->club->get("type") == 'federation') {
                    $filters['entry'] = "(SELECT a.id FROM fg_club AS a WHERE a.id = fc.parent_club_id AND fc.club_type='sub_federation_club')";
                } else {
                    $filters['entry'] = "'-'";
                }
            }
        }

        if ($filters['condition'] != '') {

            if ($filters['condition'] == 'is not') {
                if ($filters['input1'] == 'any') {
                    if ($fieldAnySub == "subfed") {
                        $this->where.=" club_type='federation_club' )";
                    } else {
                        $this->where.=" IS NULL)";
                    }
                } else {
                    $this->where.=$filters['entry'] . "!=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . " ) ";
                }
            } elseif ($filters['condition'] == 'is') {
                if ($filters['input1'] == 'any') {
                    $this->where.=$filters['entry'] . " >0)";
                } else {
                    $this->where.=$filters['entry'] . "=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                }
            }
        }
    }

    /**
     * club data filter
     *
     * @param type $filters
     */
    public function clubdatafilter($filters)
    {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {

            switch ($filters['entry']) {
                case "created_at" :
                    if ($filters['condition'] == 'is' || $filters['condition'] == 'is not') {
                        $filters['entry'] = "date_format(fc.created_at,'" . $this->mysqlDateFormat . "')";
                    } else {
                        $filters['entry'] = "fc.created_at";
                    }
                    $betweenDate = " STR_TO_DATE('" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "','" . $this->mysqlDateFormat . "') AND STR_TO_DATE('" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "','" . $this->mysqlDateFormat . "')";
                    break;
                case "email" :
                    $filters['entry'] = "fc.email";
                    break;
                case "C_co" :
                    //fg_club_address
                    $filters['entry'] = "( SELECT dca.co FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id)";
                    break;
                case "I_co" :
                    //fg_club_address
                    $filters['entry'] = "( SELECT dca.co FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id WHERE dfc.id=fc.id)";
                    break;

                case "I_street" :
                    $filters['entry'] = "( SELECT dca.street FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id)";
                    break;
                case "C_street" :
                    $filters['entry'] = "( SELECT dca.street FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "I_pobox" :
                    $filters['entry'] = "( SELECT dca.pobox FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id)";
                    break;
                case "C_pobox" :
                    $filters['entry'] = "( SELECT dca.pobox FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "I_city" :
                    $filters['entry'] = "( SELECT dca.city FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "C_city" :
                    $filters['entry'] = "( SELECT dca.city FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "I_zipcode" :
                    $filters['entry'] = "( SELECT dca.zipcode FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "C_zipcode" :
                    $filters['entry'] = "( SELECT dca.zipcode FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "I_state" :
                    $filters['entry'] = "( SELECT dca.state FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "C_state" :
                    $filters['entry'] = "( SELECT dca.state FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "I_country" :
                    $filters['entry'] = "( SELECT dca.country FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id)";
                    break;

                case "C_country" :
                    $filters['entry'] = "( SELECT dca.country FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id  )";
                    break;
                case "language" :
                    $filters['entry'] = "( SELECT dca.language FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id )";
                    break;
                case "url_identifier" :
                    $filters['entry'] = "fc.url_identifier";
                    break;
                case "website" :
                    $filters['entry'] = "fc.website";
                    break;
                case "number" :
                    $filters['entry'] = "fc.club_number";
                    break;
            }
        }

        if ($filters['condition'] != '') {

            if ($filters['condition'] == 'is between') {

                //$this->where.=" BETWEEN '" . FgUtility::getSecuredData($filters['input1']) . "' AND '" . FgUtility::getSecuredData($filters['input2']) . "')";

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    if ($filters['entry'] == 'fc.club_number') {
                        $this->where.=$filters['entry'] . " IS NOT NULL )";
                    } else {
                        //handle the age area
                        $this->where.=$filters['entry'] . " !='' OR " . $filters['entry'] . " IS NOT NULL AND " . $filters['entry'] . "!='00.00.0000' AND " . $filters['entry'] . "!='0000-00-00 00:00:00')";
                    }
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {

                    $this->where.=$filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {

                    $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                } else {
                    if ($filters['entry'] == 'fc.club_number') {
                        $inputswap = $filters['input1'];
                        if ($filters['input1'] > $filters['input2']) {
                            $filters['input1'] = $filters['input2'];
                            $filters['input2'] = $inputswap;
                        }
                        $this->where.=" (" . $filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND " . $filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "') )";
                    } else {
                        $this->where .= isset($betweenDate) ? $filters['entry'] . " BETWEEN $betweenDate)" : $filters['entry'] . " BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                    }
                }
            } elseif ($filters['condition'] == 'is not between') {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    if ($filters['entry'] == 'fc.club_number') {
                        $this->where.=$filters['entry'] . " IS NULL )";
                    } else {
                        $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NULL )";
                    }
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {
                    if ($filters['entry'] == 'fc.club_number') {
                        $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' OR " . $filters['entry'] . " IS NULL )";
                    } else {
                        $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
                    }
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {
                    $this->where.=$filters['entry'] . " > '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                } else {
                    if ($filters['entry'] == 'fc.club_number') {
                        $inputswap = $filters['input1'];
                        if ($filters['input1'] > $filters['input2']) {
                            $filters['input1'] = $filters['input2'];
                            $filters['input2'] = $inputswap;
                        }
                        $this->where.=" (" . $filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' OR " . $filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')  OR " . $filters['entry'] . " IS NULL )";
                    } else {
                        $this->where .= isset($betweenDate) ? $filters['entry'] . " NOT BETWEEN $betweenDate)" : $filters['entry'] . " NOT BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                    }
                }

                //$this->where.=" BETWEEN " . FgUtility::getSecuredData($filters['input1']) . " AND " . FgUtility::getSecuredData($filters['input2']) . ")";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is' && $filters['entry'] == 'fc.club_number') {
                $this->where.= $filters['entry'] . " IS NULL )";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not' && $filters['entry'] == 'fc.club_number') {
                $this->where.=$filters['entry'] . " IS NOT NULL )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is not' && $filters['entry'] == 'fc.club_number') {
                $this->where.=$filters['entry'] . "!='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' OR " . $filters['entry'] . " IS NULL )";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is' && $filters['entry'] != 'fc.created_at') {
                $this->where.=$filters['entry'] . "='00.00.0000')";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'is not' && $filters['entry'] != 'fc.created_at') {
                $this->where.=$filters['entry'] . "!='00.00.0000')";
            } elseif ($filters['input1'] == '' && $filters['entry'] == 'fc.created_at' && $filters['condition'] == 'is') {
                $this->where.=$filters['entry'] . "<0 || " . $filters['entry'] . " IS NULL )";
            } elseif ($filters['input1'] == '' && $filters['entry'] == 'fc.created_at' && $filters['condition'] == 'is not') {
                $this->where.=$filters['entry'] . ">=0)";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is not') {
                $this->where.=$filters['entry'] . "!='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'is') {
                $this->where.=$filters['entry'] . "='" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
            } //start
            elseif ($filters['input1'] != '' && $filters['condition'] == 'begins with') {
                $this->where.=" " . $filters['entry'] . " LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'begins not with') {

                $this->where.=" " . $filters['entry'] . " NOT LIKE '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'end with') {

                $this->where.=" " . $filters['entry'] . "  LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
            } elseif ($filters['input1'] != '' && $filters['condition'] == 'end not with') {

                $this->where.=" " . $filters['entry'] . " NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' )";
            } elseif ($filters['input1'] == '' && ( $filters['condition'] == 'end with' || $filters['condition'] == 'begins with' )) {

                $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
            } elseif ($filters['input1'] == '' && ( $filters['condition'] == 'end not with' || $filters['condition'] == 'begins not with' )) {

                $this->where.=" " . $filters['entry'] . " !='' AND " . $filters['entry'] . " IS NOT NULL  )";
            }
            //end
            elseif ($filters['input1'] != '' && $filters['condition'] == 'contains not') {
                $this->where.=" " . $filters['entry'] . " NOT LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
            } elseif ($filters['input1'] == '' && $filters['condition'] == 'contains not') {
                $this->where.=" " . $filters['entry'] . " !='' AND " . $filters['entry'] . " IS NOT NULL  )";
            } else {
                if ($filters['input1'] != '') {
                    $this->where.=" " . $filters['entry'] . " LIKE '%" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "%' )";
                } else {
                    $this->where.=" " . $filters['entry'] . " ='' OR " . $filters['entry'] . " IS  NULL  )";
                }
            }
        }
    }

    /**
     * classification filter
     *
     * @param type $filters
     */
    public function classificationfilter($filters)
    {
        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }
        if ($filters['condition'] == 'is') {
            if ($filters['input1'] != 'any') {
                $this->where.=" fc.id IN(SELECT fca.club_id FROM  fg_club_class_assignment AS fca WHERE fca.class_id=" . $filters['input1'] . ")) ";
            } else {
                $this->where.=" fc.id IN(SELECT fca.club_id FROM  fg_club_class_assignment AS fca INNER JOIN fg_club_class AS fcc ON fca.class_id=fcc.id  WHERE fcc.classification_id=" . $filters['entry'] . ")) ";
            }
        } else {
            if ($filters['input1'] != 'any') {
                $this->where.=" fc.id NOT IN(SELECT fca.club_id FROM  fg_club_class_assignment AS fca WHERE fca.class_id=" . $filters['input1'] . ")) ";
            } else {
                $this->where.=" fc.id NOT IN(SELECT fca.club_id FROM  fg_club_class_assignment AS fca INNER JOIN fg_club_class AS fcc ON fca.class_id=fcc.id  WHERE fcc.classification_id=" . $filters['entry'] . ")) ";
            }
        }
    }

    /**
     * For handle the additional field
     *
     * @param type $filters
     */
    public function additionalFieldFilter($filters)
    {

        if ($filters['connector'] == '' || is_null($filters['connector']) || $filters['connector'] == 'null') {

            $this->where.=" (";
        } else {

            $this->where.=" " . $filters['connector'] . "( ";
        }

        if ($filters['entry'] != '') {

            if ($filters['entry'] == 'Notes') {
                $filters['entry'] = "(SELECT COUNT(fg_club_notes.id) FROM fg_club_notes WHERE fg_club_notes.club_id=fc.id AND fg_club_notes.created_by_club={$this->club->get("id")})";
            }
        }

        if ($filters['condition'] != '') {


            if ($filters['condition'] == 'is between') {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NOT NULL )";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {

                    $this->where.=$filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {

                    $this->where.=$filters['entry'] . " <= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                } else {

                    $this->where .= isset($betweenDate) ? $filters['entry'] . " BETWEEN $betweenDate)" : $filters['entry'] . " BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                }
            } elseif ($filters['condition'] == 'is') {

                if ($filters['input1'] == '') {
                    $this->where.=$filters['entry'] . "='' OR " . $filters['entry'] . " IS NULL OR " . $filters['entry'] . "=0)";
                } else {
                    if ($filters['data_type'] == 'number') {
                        $this->where.=$filters['entry'] . "=" . FgUtility::getSecuredData($filters['input1'], $this->conn) . ")";
                    } else {
                        $this->where.= $filters['entry'] . "= '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                    }
                }
            } elseif ($filters['condition'] == 'is not') {
                if ($filters['input1'] == '') {
                    $this->where.=$filters['entry'] . "!='' OR " . $filters['entry'] . " IS NOT NULL )";
                } else {
                    $this->where.= $filters['entry'] . " != '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                }
            } else {

                if ($filters['input1'] == '' && $filters['input2'] == '') {
                    $this->where.=$filters['entry'] . " ='' OR " . $filters['entry'] . " IS NULL )";
                } elseif ($filters['input1'] != '' && $filters['input2'] == '') {

                    $this->where.=$filters['entry'] . " < '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "')";
                } elseif ($filters['input1'] == '' && $filters['input2'] != '') {

                    $this->where.=$filters['entry'] . " >= '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                } else {

                    $this->where .= isset($betweenDate) ? $filters['entry'] . "NOT BETWEEN $betweenDate)" : $filters['entry'] . " NOT BETWEEN '" . FgUtility::getSecuredData($filters['input1'], $this->conn) . "' AND '" . FgUtility::getSecuredData($filters['input2'], $this->conn) . "')";
                }
            }
        }
    }
}
