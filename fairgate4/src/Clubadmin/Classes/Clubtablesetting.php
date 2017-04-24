<?php

namespace Clubadmin\Classes;

use Common\UtilityBundle\Util\FgSettings;

/**
 * for find the club table columns
 */
class Clubtablesetting
{

    private $columnDatas;
    private $mysqlDateFormat;
    private $mysqlDateTimeFormat;
    private $club;
    private $container;
    private $conn;
    private $columnArray;
    private $key;
    private $changeflag;

    /**
     * constructor
     *
     * @param type $columns tableColumns
     * @param type $club    service
     */
    public function __construct($container, $columns, $club)
    {
        $this->columnDatas = $columns;
        $this->mysqlDateFormat = FgSettings::getMysqlDateFormat();
        $this->mysqlDateTimeFormat = FgSettings::getMysqlDateTimeFormat();
        $this->club = $club;
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * For collect  actual table columns
     * @return type
     */
    public function getClubColumns()
    {
        foreach ($this->columnDatas as $key => $columndata) {
            switch ($columndata['type']) {
                case "CF" :
                    if ($this->changeflag == 1) {
                        $key = $key + 1;
                    }
                    $this->clubdataField($columndata, $key);
                    break;
                case "SI" :
                    if ($this->changeflag == 1) {
                        $key = $key + 1;
                    }
                    $this->clubsystemField($columndata, $key);

                    break;
                case "CO" :
                    if ($this->changeflag == 1) {
                        $key = $key + 1;
                    }
                    $this->clubOptionField($columndata, $key);
                    break;
                case "CL" :
                    if ($this->changeflag == 1) {
                        $key = $key + 1;
                    }
                    $this->classificationField($columndata, $key);
                    break;
                case "AF" :
                    if ($this->changeflag == 1) {
                        $key = $key + 1;
                    }
                    $this->additionalField($columndata, $key);
                    break;
            }
        }

        return $this->columnArray;
    }

    /**
     * @param type $columndata clubdatacolumn
     * @param type $key        index
     */
    public function clubdataField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {
            case "created_at" :
                $this->columnArray[$this->key] = "date_format(fc.{$columndata['id']},'{$this->mysqlDateTimeFormat}') AS {$columndata['name']}";
                break;
            case "email" :
                $this->columnArray[$this->key] = "fc.{$columndata['id']} AS {$columndata['name']}";
                break;
            case "C_co" :
                //fg_club_address
                $this->columnArray[$this->key] = "( SELECT dca.co FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;
            case "I_co" :
                //fg_club_address
                $this->columnArray[$this->key] = "( SELECT dca.co FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "I_street" :
                $this->columnArray[$this->key] = "( SELECT dca.street FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;
            case "C_street" :
                $this->columnArray[$this->key] = "( SELECT dca.street FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id ) AS {$columndata['name']}";
                break;

            case "I_pobox" :
                $this->columnArray[$this->key] = "( SELECT dca.pobox FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;
            case "C_pobox" :
                $this->columnArray[$this->key] = "( SELECT dca.pobox FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "I_city" :
                $this->columnArray[$this->key] = "( SELECT dca.city FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "C_city" :
                $this->columnArray[$this->key] = "( SELECT dca.city FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "I_zipcode" :
                $this->columnArray[$this->key] = "( SELECT dca.zipcode FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "C_zipcode" :
                $this->columnArray[$this->key] = "( SELECT dca.zipcode FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "I_state" :
                $this->columnArray[$this->key] = "( SELECT dca.state FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "C_state" :
                $this->columnArray[$this->key] = "( SELECT dca.state FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "I_country" :
                $this->columnArray[$this->key] = "( SELECT dca.country FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.billing_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;

            case "C_country" :
                $this->columnArray[$this->key] = "( SELECT dca.country FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;
            case "clubname" :
                $this->columnArray[$this->key] = "COALESCE(NULLIF(fci18n.title_lang,''), fc.title) AS clubname";
                break;
            case "language" :
                $this->columnArray[$this->key] = "( SELECT dca.language FROM fg_club AS dfc LEFT JOIN fg_club_address as dca on dfc.correspondence_id=dca.id WHERE dfc.id=fc.id) AS {$columndata['name']}";
                break;
            case "url_identifier" :
                $this->columnArray[$this->key] = "fc.url_identifier AS {$columndata['name']}";
                break;
            case "website" :
                $this->columnArray[$this->key] = "fc.website AS {$columndata['name']}";
                break;
            case "establish" :
                $this->columnArray[$this->key] = "fc.year AS {$columndata['name']}";
                break;
            case "number" :
                $this->columnArray[$this->key] = "fc.club_number AS {$columndata['name']}";
                break;
        }
    }

    /**
     * For find the club option field
     * @param type $columndata cluboptioncolumns
     * @param type $key        index
     */
    public function clubOptionField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {
            case 'subfed':
                if ($this->club->get("type") == 'federation') {
                    $this->columnArray[$this->key] = "IF(fc.club_type='sub_federation_club', (SELECT COALESCE(NULLIF(ai18n.title_lang,''), a.title) FROM fg_club AS a LEFT JOIN fg_club_i18n ai18n ON (ai18n.id = a.id AND ai18n.lang = '{$this->container->get('club')->get('default_lang')}') WHERE a.id = fc.parent_club_id), '') AS {$columndata['name']}";
                } else {
                    $this->columnArray[$this->key] = "'-' AS {$columndata['name']}";
                }

                break;
            case "dispatch_type_invoice" :
                //dispatch_type_invoice
                $this->columnArray[$this->key] = "'-' AS {$columndata['name']}";
                break;
            case "dispatch_type_dun" :
                //dispatch_type_dun
                $this->columnArray[$this->key] = "'-' AS {$columndata['name']}";
                break;
        }
    }

    /**
     * For find actual clubsystem columns
     * @param type $columndata clubsystemfield
     * @param type $key        index
     */
    public function clubsystemField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {
            case "CLUB_ID" :
                $this->columnArray[$this->key] = " fc.id AS {$columndata['name']}";
                break;
            case "FED_MEMBERS" :
                $clubId = $this->getFederationId($this->club->get("type"));
                //stored procedure-get federation count
                //$this->columnArray[$this->key] = "getFedMemberCount(fc.id ,{$clubId}) AS {$columndata['name']} ";
                $this->columnArray[$this->key] = "fc.fedmember_count AS {$columndata['name']} ";
                break;
            case "LAST_CONTACT_EDIT" :
                $clubId = $this->getFederationId($this->club->get("type"));
                //$this->columnArray[$this->key] = "date_format( getLastContactUpdate(fc.id ,{$clubId}),'{$this->mysqlDateFormat}') AS {$columndata['name']}";
                $this->columnArray[$this->key] = "date_format( fc.last_contact_updated,'{$this->mysqlDateFormat}')  AS {$columndata['name']}";
                break;
            case "LAST_ADMIN_LOGIN" :
                //$this->columnArray[$this->key] = "(SELECT date_format(u.last_login,'{$this->mysqlDateFormat}')  FROM `sf_guard_user_group`ug left join sf_guard_user u on ug.user_id=u.id WHERE ug.`group_id` = 2 and u.club_id=fc.id order by u.last_login desc limit 0,1 ) AS {$columndata['name']}";
                $this->columnArray[$this->key] = "date_format(fc.last_admin_login,'{$this->mysqlDateFormat}') AS {$columndata['name']}";
                break;
            //for club overview
            case "OWN_FED_MEMBERS" :
                $clubId = $this->getFederationId($this->club->get("type"));
                //$this->columnArray[$this->key] = "(SELECT COUNT(fg_cm_contact.id) FROM fg_cm_contact WHERE fg_cm_contact.club_id=fc.id and fg_cm_contact.fed_membership_cat_id is not null and fg_cm_contact.is_fed_membership_confirmed=0) AS {$columndata['name']}";
                $this->columnArray[$this->key] = "'0' AS {$columndata['name']}";
                break;
        }
    }

    /**
     * for find the classification fields
     * @param type $columndata classification columns
     * @param type $key        index
     */
    public function classificationField($columndata, $key)
    {
        $this->key = $key;
        $query = '';
        $classificationId = $columndata['id'];

        if ($columndata['sub_ids'] != 'all') {
            $query = " AND fcl.id IN ({$columndata['sub_ids']}) ";
        }
        $this->columnArray[$this->key] = "(SELECT GROUP_CONCAT(fcl.title SEPARATOR ';' )   FROM fg_club_classification AS fcc LEFT JOIN fg_club_class AS fcl ON fcl.classification_id= fcc.id {$query}
                                                    LEFT JOIN fg_club_class_assignment AS fca ON fca.class_id = fcl.id  AND fcc.id= {$classificationId}  WHERE fc.id=fca.club_id AND fca.club_id IN (SELECT c.id FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := {$this->club->get("id")},@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id)  GROUP BY fca.class_id) AS {$columndata['name']}";
    }

    /**
     * For find the actual additional field columns
     * @param array $columndata additional columns
     * @param int   $key        index
     */
    public function additionalField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {
            case "Notes" :
                if($this->club->get("type")=='federation') {
                  $this->columnArray[$this->key] =   "fc.fed_note_count AS {$columndata['name']}";  
                } else if($this->club->get("type")=='sub_federation') {
                   $this->columnArray[$this->key] =   "fc.subfed_note_count AS {$columndata['name']}"; 
                }
                
                break;
            case "Documents" :
                $this->columnArray[$this->key] = "fc.document_count AS {$columndata['name']}";
                break;
        }
    }

    /**
     * For get the federation id from the club type
     * @param type $clubtype
     *
     * @return type
     */
    private function getFederationId($clubtype)
    {

        return $clubtype == 'federation' ? $this->club->get("id") : $this->club->get("federation_id");
    }
}
