<?php namespace Clubadmin\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;

/**
 * For set the contact fields.
 */
class Contactlist
{

    public $tableColumns = array();
    public $systemFields = array();
    public $fedFields;
    public $subFedfields;
    public $fgClubfields;
    public $tablejoin;
    public $orderBy;
    public $where;
    public $from;
    public $limit;
    public $offset;
    public $result;
    public $selectionFields = '';
    public $clubId;
    public $fedId;
    public $subfedId;
    public $clubtype;
    public $contactId;
    private $container;
    public $groupBy;
    public $contactType;
    private $conn;
    public $confirmedFlag = true;
    public $fedContactSelectionFlag = true;

    /**
     * For handle portrait element
     * @var boolean 
     */
    public $separateListing = false;

    /**
     * For handle portrait element
     * @var array 
     */
    public $separateListingDetails = '';

    /**
     * Separate listing group by 
     * @var type 
     */
    public $separateListGroupBy = '';

    /**
     * Separate listing where condition
     * @var type 
     */
    public $separateListWhere = '';
    private $club;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container   containre
     * @param type                                                      $contactId   contactId
     * @param type                                                      $club        object of club details
     * @param type                                                      $contactType type   of contact (archived, contact, formarfederation, both, all(all including sponsors), sponsor, archivedsponsor, allsponsor)
     */
    public function __construct(ContainerInterface $container, $contactId, $club, $contactType = 'contact')
    {
        $this->fedFields = array_keys($club->get('fedFields'));
        $this->subFedfields = array_keys($club->get('subFedFields'));
        $this->fgClubfields = array_keys($club->get('clubFields'));
        $this->subSubFedFields = array_keys($club->get('subSubFedFields'));
        $this->systemFields = array_keys($club->get('systemFields'));

        $this->clubId = $club->get('id');
        $this->fedId = $club->get('federation_id');
        $this->subfedId = $club->get('sub_federation_id');
        $this->contactId = $contactId;
        $this->container = $container;
        $this->clubtype = $club->get('type');
        $this->where = '';
        $this->contactType = $contactType;
        $this->conn = $this->container->get('database_connection');
        $this->club = $club;
    }

    /**
     * @param type $column
     *
     * @return type
     */
    public function addColumns($column)
    {
        $this->selectionFields = ',`' . $column . '`';

        return $this->selectionFields;
    }

    /**
     * @param type $columns fieldsarray
     *
     * @return type
     */
    public function setColumns($columns)
    {
        $this->tableColumns = $columns;
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
                $newfield = "(CASE WHEN (fg_cm_contact.is_company = 1) THEN IF ((fg_cm_contact.has_main_contact = 1), CONCAT(ms.`9`, ' (', ms.`23`, ' ', ms.`2`, ')'), ms.`9`) ELSE CONCAT(ms.`23`, ' ', ms.`2`) END) as contactname";

                $columns[$key] = $newfield;
                $flag = 1;
            }
            if (in_array('contactnamewithcomma', $columns)) {
                $key = array_search('contactnamewithcomma', $columns); //
                $newfield = "(CASE WHEN (fg_cm_contact.is_company = 1) THEN IF ((fg_cm_contact.has_main_contact = 1), CONCAT(ms.`9`, ' (', ms.`23`, ', ', ms.`2`, ')'), ms.`9`) ELSE CONCAT(ms.`23`, ', ', ms.`2`) END) as contactname";
                $columns[$key] = $newfield;
                $flag = 1;
            }
            if (in_array('isMember', $columns)) {
                $key = array_search('isMember', $columns); //

                $columns[$key] = '"ismember" AS Ismember';
            }
            if (in_array('isSponsor', $columns)) {
                $key = array_search('isSponsor', $columns); //

                $columns[$key] = 'fg_cm_contact.is_sponsor as sponsorFlag';
            }
            if (in_array('gender', $columns)) {
                $key = array_search('gender', $columns);
                $columns[$key] = 'ms.`' . $this->container->getParameter('system_field_gender') . '` AS Gender';
            }
            if (in_array('isCompany', $columns)) {
                $key = array_search('isCompany', $columns); //

                $columns[$key] = 'fg_cm_contact.is_company AS Iscompany ';
            }
            if (in_array('contactclubid', $columns)) {
                $key = array_search('contactclubid', $columns); //

                $columns[$key] = 'fg_cm_contact.club_id AS contactclubid ';
            }
            if (in_array('createdclubid', $columns)) {
                $key = array_search('createdclubid', $columns); //

                $columns[$key] = 'fg_cm_contact.created_club_id AS createdclubid ';
            }
            //To get federation membership id
            if (in_array('fedmembershipType', $columns)) {
                $key = array_search('fedmembershipType', $columns); //
                $columns[$key] = '(IF(fg_cm_contact.fed_membership_cat_id IS NOT NULL,fg_cm_contact.fed_membership_cat_id,0)) AS fedmembershipType';
            }
            //To get club membership id
            if (in_array('clubmembershipType', $columns)) {
                $key = array_search('clubmembershipType', $columns); //
                $columns[$key] = '(IF(fg_cm_contact.club_membership_cat_id IS NOT NULL,fg_cm_contact.club_membership_cat_id,0)) AS clubmembershipType';
            }

            if (in_array('compDefContact', $columns)) {
                $key = array_search('compDefContact', $columns); //
                $columns[$key] = 'fg_cm_contact.comp_def_contact AS compDefContact ';
            }
            if (in_array('hasMainContact', $columns)) {
                $key = array_search('hasMainContact', $columns); //
                $columns[$key] = 'fg_cm_contact.has_main_contact AS hasMainContact ';
            }
            if (in_array('sameInvoiceAddress', $columns)) {
                $key = array_search('sameInvoiceAddress', $columns); //
                $columns[$key] = 'fg_cm_contact.same_invoice_address AS sameInvoiceAddress ';
            }
            //To find Fed membership category id
            if (in_array('fedMembershipId', $columns)) {
                $key = array_search('fedMembershipId', $columns); //
                $columns[$key] = 'fg_cm_contact.fed_membership_cat_id AS fedMembershipId';
            }
            //To find Club membership category id
            if (in_array('clubMembershipId', $columns)) {
                $key = array_search('clubMembershipId', $columns); //
                $columns[$key] = 'fg_cm_contact.club_membership_cat_id AS clubMembershipId';
            }

            /* for dashboard * */
            if (in_array('isMemberTitle', $columns)) {
                $key = array_search('isMemberTitle', $columns); //
                $columns[$key] = 'fg_cm_membership.title AS membershipTitle  ';
            }

            if (in_array('genderCount', $columns)) {
                $key = array_search('genderCount', $columns); //
                $columns[$key] = '(select count(CASE ms.`' . $this->container->getParameter('system_field_gender') . "` when 'Male' then 1  end ))AS mcount,(select count(CASE ms.`" . $this->container->getParameter('system_field_gender') . "` when 'Female' then 1  end ) ) AS fcount ";
            }
            // To find fedmembership count
            if (in_array('membershipCatIdCount', $columns)) {
                $key = array_search('membershipCatIdCount', $columns); //
                $columns[$key] = '(count(fg_cm_contact.fed_membership_cat_id)) AS membershipCount';
            }
            // To find clubmembership count
            if (in_array('clubmembershipCatIdCount', $columns)) {
                $key = array_search('clubmembershipCatIdCount', $columns); //
                $columns[$key] = '(count(fg_cm_contact.club_membership_cat_id)) AS clubmembershipCatIdCount';
            }
            //To find fedmembership is approve or not
            if (in_array('fedmembershipApprove', $columns)) {
                $key = array_search('fedmembershipApprove', $columns); //
                $columns[$key] = ' fg_cm_contact.is_fed_membership_confirmed AS fedmembershipApprove';
            }
            if (in_array('CompanyPersonalCount', $columns)) {
                $key = array_search('CompanyPersonalCount', $columns); //
                $columns[$key] = "(select count(CASE fg_cm_contact.is_company when '0' then 1 end)) AS isPersonal, (select count(CASE fg_cm_contact.is_company when '1' then 1 end)) AS isCompany";
            }

            if (in_array('originCount', $columns)) {
                $key = array_search('originCount', $columns);
                $columns[$key] = '(count(ms.`' . $this->container->getParameter('system_field_corres_ort') . '` )) AS originCount';
            }
            if (in_array('corrsCity', $columns)) {
                $key = array_search('corrsCity', $columns); //
                $columns[$key] = 'ms.`' . $this->container->getParameter('system_field_corres_ort') . '` AS city ';
            }
            if (in_array('contactNameYOB', $columns)) {
                $key = array_search('contactNameYOB', $columns); //

                $columns[$key] = 'contactNameYOB(fg_cm_contact.id) as contactNameYOB ';
            }

            if (in_array('stealthMode', $columns)) {
                $key = array_search('stealthMode', $columns);
                $columns[$key] = 'fg_cm_contact.is_stealth_mode  AS stealthFlag';
            }

            if (in_array('clubTitle', $columns)) {
                $key = array_search('clubTitle', $columns);
                if ($this->clubtype == "federation") {
                    $columns[$key] = "(SELECT COALESCE(NULLIF(ci18n.title_lang, ''), c.title) FROM fg_club c LEFT JOIN fg_club_i18n ci18n ON (ci18n.id = c.id AND ci18n.lang = '" . $this->container->get('club')->get('default_lang') . "') WHERE c.id= fg_cm_contact.club_id AND c.is_federation=1 AND c.is_sub_federation=0) AS clubTitle";
                } else {
                    $columns[$key] = "(SELECT COALESCE(NULLIF(ci18n.title_lang, ''), c.title) FROM fg_club c LEFT JOIN fg_club_i18n ci18n ON (ci18n.id = c.id AND ci18n.lang = '" . $this->container->get('club')->get('default_lang') . "') WHERE c.id= fg_cm_contact.club_id AND c.is_federation=0) AS clubTitle";
                }
            }

            if (in_array('subFedTitle', $columns)) {
                $key = array_search('subFedTitle', $columns);
                $columns[$key] = "(SELECT COALESCE(NULLIF(ci18n.title_lang, ''), c.title) FROM fg_club c LEFT JOIN fg_club_i18n ci18n ON (ci18n.id = c.id AND ci18n.lang = '" . $this->container->get('club')->get('default_lang') . "') WHERE ((c.id = fg_cm_contact.club_id AND c.club_type='sub_federation') OR (c.id = (SELECT dfc.parent_club_id FROM fg_club AS dfc WHERE dfc.id=fg_cm_contact.club_id AND dfc.club_type='sub_federation_club')))) AS subFedTitle";
            }

            if (in_array('clubs', $columns)) {
                $key = array_search('clubs', $columns);
                $columns[$key] = "(SELECT GROUP_CONCAT(IF(cl.id = c.main_club_id, CONCAT(cl.id, '#mainclub#'), cl.id) SEPARATOR ', ') FROM fg_cm_contact c INNER JOIN fg_club cl ON (cl.id = c.club_id) WHERE c.fed_contact_id = fg_cm_contact.fed_contact_id AND cl.is_federation=0 AND cl.is_sub_federation=0) AS contactClubs";
            }

            if (in_array('subFederations', $columns)) {
                $key = array_search('subFederations', $columns);
                $columns[$key] = "(SELECT GROUP_CONCAT(cl.id SEPARATOR ', ') FROM fg_cm_contact c INNER JOIN fg_club cl ON (cl.id = c.club_id) WHERE c.fed_contact_id = fg_cm_contact.fed_contact_id AND cl.is_federation=0 AND cl.is_sub_federation=1) AS contactSubFederations";
            }

            if (in_array('fedicon', $columns)) {
                $key = array_search('fedicon', $columns);
                $fedId = ($this->clubtype == 'federation') ? $this->clubId : $this->fedId;
                $columns[$key] = '( SELECT federation_icon FROM fg_club_settings WHERE club_id =' . $fedId . ') AS fedicon ';
            }
            /* end dashboard* */
            $this->tableColumns = $columns;

            if ($flag == 1) {
                $firstfield = $columns[$key];
                unset($columns[$key]);
            }
            //check column array contain values
            if (count($columns) > 0) {
                $this->selectionFields = implode(',', $columns);
            }

            if ($flag == 1) {
                $this->selectionFields = $firstfield . ',' . $this->selectionFields;
            }
            $this->selectionFields = str_replace('clubId', 'fg_cm_contact.club_id as clubId', $this->selectionFields);
            $this->selectionFields = str_replace('mainClubId', 'fg_cm_contact.main_club_id as mainClubId', $this->selectionFields);
            $this->selectionFields = str_replace('contactid', 'fg_cm_contact.id', $this->selectionFields);
            if (in_array('contactName', $columns)) {
                $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
                $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
                //$newfield = 'IF (fg_cm_contact.is_company=0 ,CONCAT_WS(" ",' . $lastname . ',' . $firstname . ' ), `9` ) as contactName';
                $newfield = "(CASE WHEN (fg_cm_contact.is_company = 1) THEN IF ((fg_cm_contact.has_main_contact = 1), CONCAT(ms.`9`, ' (', ms.`23`, ' ', ms.`2`, ')'), ms.`9`) ELSE CONCAT(ms.`23`, ' ', ms.`2`) END) as contactName";
                $this->selectionFields = preg_replace('/(contactName),/', "$newfield,", $this->selectionFields);
            }
            if (in_array('contactNameWithComma', $columns)) {
                $firstname = '`' . $this->container->getParameter('system_field_firstname') . '`';
                $lastname = '`' . $this->container->getParameter('system_field_lastname') . '`';
                //$newfield = 'IF (fg_cm_contact.is_company=0 ,CONCAT_WS(" ",' . $lastname . ',' . $firstname . ' ), `9` ) as contactName';
                $newfield = "(CASE WHEN (fg_cm_contact.is_company = 1) THEN IF ((fg_cm_contact.has_main_contact = 1), CONCAT(ms.`9`, ' (', ms.`23`, ', ', ms.`2`, ')'), ms.`9`) ELSE CONCAT(ms.`23`, ', ', ms.`2`) END) as contactName";
                $this->selectionFields = preg_replace('/(contactNameWithComma),/', "$newfield,", $this->selectionFields);
            }
            if (in_array('is_household_head', $columns)) {
                $this->selectionFields = str_replace('is_household_head', 'fg_cm_contact.is_household_head', $this->selectionFields);
            }
            if (in_array('is_seperate_invoice', $columns)) {
                $this->selectionFields = str_replace('is_seperate_invoice', 'fg_cm_contact.is_seperate_invoice', $this->selectionFields);
            }
            if (!$this->fedContactSelectionFlag) {
                $this->selectionFields = ' fg_cm_contact.id,' . $this->selectionFields;
            } else {

                $contactId = ($this->separateListing) ? 'fg_cm_contact.id,' : 'fg_cm_contact.id,';
                $this->selectionFields = $contactId . 'fg_cm_contact.fed_contact_id,fg_cm_contact.subfed_contact_id,' . $this->selectionFields;
            }
        } else {
            $this->selectionFields = '*';
        }

        return $this->selectionFields;
    }

    /**
     * @return type
     */
    public function setCount()
    {
        $this->selectionFields = ($this->contactType == 'sponsor' || $this->contactType == 'archivedsponsor') ? 'count(distinct fg_cm_contact.id) as count' : 'count(fg_cm_contact.id) as count';

        return $this->selectionFields;
    }

    /**
     * @param type $type type of contact list
     *
     * @return type
     */
    public function setFrom($type = '')
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
        $this->from .= ' INNER JOIN master_system as ms on ms.fed_contact_id = fg_cm_contact.fed_contact_id';
        //only for service list
        if ($this->contactType == 'sponsor') {
            $this->from .= ' LEFT JOIN fg_sm_bookings AS MSB ON MSB.contact_id=fg_cm_contact.id';
        } elseif ($this->contactType == 'archivedsponsor') {
            $this->from .= ' LEFT JOIN fg_sm_bookings AS MSB ON MSB.contact_id=fg_cm_contact.id';
        }
        //For get the heirarchy level contact fields
        if ($type == '' || $type == '*') {
            $contactSystemfields = array_intersect($this->systemFields, $this->tableColumns);
            $fedearationFields = array_intersect($this->fedFields, $this->tableColumns);
            $subfederationFields = array_intersect($this->subFedfields, $this->tableColumns);
            $clubFields = array_intersect($this->fgClubfields, $this->tableColumns);
            //federation field is exist or not
            if (($this->clubtype != 'federation' && $this->fedId > 0) && isset($this->fedFields) && count($this->fedFields) > 0) {
                $this->from .= " LEFT JOIN master_federation_{$this->fedId} as mf on (mf.fed_contact_id = fg_cm_contact.fed_contact_id )";
            }
            //subfederation field is exist or not
            if (($this->clubtype != 'sub_federation' && $this->subfedId > 0) && isset($this->subFedfields) && count($this->subFedfields) > 0) {
                $this->from .= " LEFT JOIN master_federation_{$this->subfedId} as msf on (msf.contact_id =  fg_cm_contact.subfed_contact_id )";
            }
        }
        //For handle portrait element multi assignment separate listing 
        if ($this->separateListing) {
            $separateColumnDetails = $this->separateListingDetails;
            $teamQuery = '';
            $query = '';
            list($separateColumn, $columnId) = explode('_', $separateColumnDetails['separateListingColumn']);
            //set different scenarios 
            switch ($separateColumn) {
                case 'WF':  //Workgroup function                 
                    $categoryId = $this->club->get('club_workgroup_id');
                    $query = ' AND mcrf.category_id = ' . $categoryId . ' AND mcrf.role_id IN (' . $separateColumnDetails['separateListingFunc'] . ')';
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND dmcrf.category_id={$categoryId} AND drr.is_active = 1 AND dmcrf.role_id IN ( {$separateColumnDetails['separateListingFunc']} ))  ";
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrf.id';
                    break;
                case 'TF': // Team Function
                    $categoryId = $this->club->get('club_team_id');
                    $query = ' AND mcrf.category_id = ' . $categoryId;
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND dmcrf.category_id={$categoryId} AND drr.is_active = 1)  ";
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrf.id';
                    break;
                case 'CRF':
                case 'CSFRF':
                case 'CFRF' : // Common role function of club/subfederation/federation
                    $query = ' AND mcrf.category_id = ' . $separateColumnDetails['separateListingFunc'];
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId}  AND dmcrf.category_id={$separateColumnDetails['separateListingFunc']} AND drr.is_active = 1)";
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrf.id';
                    break;
                case 'IFRF':
                case 'ISFRF':
                case 'IRF' :     // Individual role function of club/subfederation/federation 
                    $query = ' AND mcrf.role_id = ' . $separateColumnDetails['separateListingFunc'];
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId}  AND drr.is_active = 1 AND dmcrf.role_id IN ( {$separateColumnDetails['separateListingFunc']} ))";
                    break;
                case 'WA':  //Workgroup assignments
                    $categoryId = $this->club->get('club_workgroup_id');
                    $query = " AND mcrf.category_id= " . $categoryId;
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND dmcrf.category_id={$categoryId} AND drr.is_active = 1)";
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrr.id';
                    break;
                case 'TA': // Team assignments
                    $categoryId = $this->club->get('club_team_id');
                    if ($separateColumnDetails['separateListingFunc'] != null) {
                        $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND dmcrf.category_id={$categoryId} AND drr.is_active = 1 AND dmcrf.function_id IN ({$separateColumnDetails['separateListingFunc']}))";
                    } else {
                        $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND dmcrf.category_id={$categoryId} AND drr.is_active = 1)  ";
                    }
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrr.id';
                    break;
                case 'RCA':
                case 'FRCA':
                case 'SFRCA' :
                    $categoryId = $separateColumnDetails['separateListingFunc'];
                    $query = " AND mcrf.category_id={$categoryId}";
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND dmcrf.category_id={$categoryId} AND drr.is_active = 1) ";
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrr.id';
                    break;
                case 'FRA':  //Filter role assignment
                    $query1 = " AND mrr.filter_id IS NOT NULL  ";
                    $teamQuery = " AND mrc.fg_rm_crf_id IN (SELECT dmcrf.id FROM fg_rm_category_role_function AS dmcrf INNER JOIN fg_rm_role AS drr ON drr.id= dmcrf.role_id WHERE dmcrf.club_id={$this->clubId} AND drr.filter_id IS NOT NULL) ";
                    $this->separateListGroupBy = 'fg_cm_contact.id,mrr.id';
                    break;
            }
            $this->from .= "  LEFT JOIN fg_rm_role_contact AS mrc ON mrc.contact_id = fg_cm_contact.id {$teamQuery}  LEFT JOIN fg_rm_category_role_function AS mcrf ON mrc.fg_rm_crf_id = mcrf.id {$query}  LEFT JOIN fg_rm_role AS mrr  ON mcrf.role_id= mrr.id AND mrr.is_active = 1 {$query1} LEFT JOIN fg_rm_role_i18n AS mrri18n ON mrri18n.id=mrr.id AND mrri18n.lang='{$this->container->get('club')->get('default_lang')}' LEFT JOIN fg_rm_function AS mrf ON mrf.id = mcrf.function_id LEFT JOIN fg_rm_function_i18n AS mrfi18n ON mrf.id=mrfi18n.id AND mrfi18n.lang='{$this->container->get('club')->get('default_lang')}'";
        }

        return $this->from;
    }

    /**
     * Set initial condition.
     */
    public function setCondition($tableSetting = 'Clubadmin')
    {
        switch ($this->contactType) {
            case 'archive':
                $archiveCondition = 'fg_cm_contact.is_deleted=1';
                break;
            case 'formerfederationmember':
                $archiveCondition = 'fg_cm_contact.is_former_fed_member=1 AND fg_cm_contact.club_id=' . $this->clubId;
                break;
            case 'sponsor':
                if ($tableSetting == 'Clubadmin')
                    $archiveCondition = 'fg_cm_contact.is_sponsor = 1 and fg_cm_contact.is_deleted = 0 ';
                else //webisite table setting - contact table setting element
                    $archiveCondition = 'fg_cm_contact.is_sponsor = 1 and fg_cm_contact.is_deleted = 0 and fg_cm_contact.is_stealth_mode = 0';
                break;
            case 'archivedsponsor':
                $archiveCondition = 'fg_cm_contact.is_sponsor = 1 and fg_cm_contact.is_deleted=1';
                break;
            case 'allsponsors':
                if ($this->clubtype == 'federation' || $this->clubtype == 'sub_federation') {
                    $this->where = " fg_cm_contact.is_sponsor = 1 AND (((fg_cm_contact.is_deleted = 0 AND fg_cm_contact.is_permanent_delete=0) AND (fg_cm_contact.club_id  = '{$this->clubId}' AND (fg_cm_contact.main_club_id = '{$this->clubId}' OR fg_cm_contact.fed_membership_cat_id IS NOT NULL AND fg_cm_contact.fed_membership_cat_id != ''))) ) AND (IF(fg_cm_contact.is_fed_membership_confirmed=1 AND fg_cm_contact.old_fed_membership_id IS NOT NULL,0 ,1))";
                } else {
                    $this->where = " fg_cm_contact.club_id  = '{$this->clubId}' AND fg_cm_contact.is_sponsor = 1 AND ( (fg_cm_contact.is_deleted = 0 AND fg_cm_contact.is_permanent_delete=0) OR (fg_cm_contact.is_deleted=1 AND fg_cm_contact.is_permanent_delete=0))";
                }
                $this->where .= ' AND fg_cm_contact.is_draft=0';
                break;
            case 'all':
                if ($this->clubtype == 'federation' || $this->clubtype == 'sub_federation') {
                    $this->where = " fg_cm_contact.is_deleted = 0 AND fg_cm_contact.is_permanent_delete=0 AND fg_cm_contact.club_id = '{$this->clubId}' AND (fg_cm_contact.main_club_id = '{$this->clubId}' OR fg_cm_contact.fed_membership_cat_id IS NOT NULL AND fg_cm_contact.fed_membership_cat_id != '') ";
                } else {
                    $this->where = '( (fg_cm_contact.is_deleted = 0 AND fg_cm_contact.is_permanent_delete=0) OR (fg_cm_contact.is_deleted=1 AND fg_cm_contact.is_permanent_delete=0))';
                }
                $this->where .= ' AND fg_cm_contact.is_draft=0';
                break;
            case 'editable':
                $archiveCondition = 'fg_cm_contact.is_permanent_delete=0';
                break;
            case 'fulldetails'://without consider the delete status
                if ($this->clubtype == 'federation' || $this->clubtype == 'sub_federation') {
                    $this->where = "  fg_cm_contact.is_permanent_delete=0 AND fg_cm_contact.club_id = '{$this->clubId}' AND (fg_cm_contact.main_club_id = '{$this->clubId}' OR fg_cm_contact.fed_membership_cat_id IS NOT NULL AND fg_cm_contact.fed_membership_cat_id != '') ";
                } else {
                    $this->where = ' fg_cm_contact.is_permanent_delete=0';
                }
                $this->where .= ' AND fg_cm_contact.is_draft=0';
            case 'allVisible':
                $this->where .= 'fg_cm_contact.is_permanent_delete=0';
                break;
            case 'noCondition'://handle permanent deleted contact
                $this->where .= ' 1=1';
                break;
            default:
                if ($tableSetting == 'Clubadmin')
                    $archiveCondition = 'fg_cm_contact.is_deleted=0';
                else //webisite table setting - contact table setting element
                    $archiveCondition = 'fg_cm_contact.is_deleted = 0 and fg_cm_contact.is_stealth_mode = 0';

                break;
        }
        if ($this->contactType != 'all' && $this->contactType != 'allsponsors' && $this->contactType != 'contactForConfirmation' && $this->contactType != 'fulldetails' && $this->contactType != 'allVisible' && $this->contactType != 'noCondition') {
            $permenantDelete = ($this->contactType == 'formerfederationmember') ? '1' : 'fg_cm_contact.is_permanent_delete=0';
            if ($this->clubtype == 'federation' || $this->clubtype == 'sub_federation') {
                //check if contact has approved fed membership
                $fedmemberApprovedConditions = ($this->confirmedFlag == true) ? " AND (fg_cm_contact.is_fed_membership_confirmed='0' OR (fg_cm_contact.is_fed_membership_confirmed='1' AND fg_cm_contact.old_fed_membership_id IS NOT NULL))  " : '';
                $this->where = ($this->contactType != 'formerfederationmember') ? $permenantDelete . " AND {$archiveCondition} AND fg_cm_contact.club_id={$this->clubId} AND (fg_cm_contact.main_club_id={$this->clubId} OR fg_cm_contact.fed_membership_cat_id IS NOT NULL){$fedmemberApprovedConditions}" : " {$archiveCondition} ";
            } else {
                $this->where = $permenantDelete . " AND {$archiveCondition} AND fg_cm_contact.club_id={$this->clubId}";
            }
            if ($this->contactType != 'draft') {
                $this->where .= ' AND fg_cm_contact.is_draft=0';
            }
        }
        //To manage query if $this->where =='', otherwise it shows sql error.
        if ($this->where == '') {
            $this->where = '1=1';
        }
    }

    /**
     * @param type $condition
     *
     * @return type
     */
    public function addCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' AND (' . $condition . ' )';
        }
        return $this->where;
    }

    /**
     * @param type $condition
     *
     * @return type
     */
    public function orCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' OR (' . $condition . ' )';
        }
        $this->where = '(' . $this->where . ')';

        return $this->where;
    }

    /**
     * @return condition
     */
    public function getCondition()
    {
        return $this->where;
    }

    /**
     * @param type $orderColumn column order
     *
     * @return type
     */
    public function addOrderBy($orderColumn = '')
    {
        if ($orderColumn != '') {
            $this->orderBy = ' ' . $orderColumn;
        }

        return $this->orderBy;
    }

    /**
     * @param type $limit
     *
     * @return type
     */
    public function setLimit($limit = '')
    {
        if ($limit != '') {
            $this->limit = ' LIMIT ' . FgUtility::getSecuredData($limit, $this->conn);
        }

        return $this->limit;
    }

    /**
     * @param type $offset offset
     */
    public function setOffset($offset = '')
    {
        if ($offset != '') {
            $this->offset = $offset;
        }
    }

    /**
     * @return groupby
     */
    public function setGroupBy($col = '')
    {
        if ($col != '') {
            $this->groupBy = $col;
        }

        return $this->groupBy;
    }

    /**
     * @param type $jointext join text
     */
    public function addJoin($jointext)
    {
        $this->from .= $jointext;
    }

    /**
     * @return result
     */
    public function getResult()
    {
        $this->result = 'SELECT ' . $this->selectionFields . ' FROM ' . $this->from;
        $this->result .= ' WHERE ' . $this->where;
        if ($this->setGroupBy() != '') {
            $this->result .= ' GROUP BY ' . $this->groupBy;
        }
        if ($this->addOrderBy() != '') {
            $this->result .= ' ORDER BY' . $this->orderBy;
        }
        if ($this->setLimit() != '') {
            $this->result .= $this->limit;
        }
        if ($this->offset != '') {
            $this->result .= ',' . $this->offset;
        }

        return $this->result;
    }
}
