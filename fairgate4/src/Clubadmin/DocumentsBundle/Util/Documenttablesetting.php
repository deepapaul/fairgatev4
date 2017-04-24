<?php

namespace Clubadmin\DocumentsBundle\Util;

use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * for find the club table columns
 */
class Documenttablesetting
{

    private $columnDatas;
    private $mysqlDateTimeFormat;
    private $club;
    private $container;
    private $conn;
    private $columnArray;
    private $key;
    private $assignedTo;

    /**
     * constructor
     *
     * @param type $columns tableColumns
     * @param type $club    service
     */
    public function __construct($container, $columns, $club, $doctype = 'CLUB', $assignedTo = '')
    {
        $this->columnDatas = $columns;
        $this->mysqlDateTimeFormat = FgSettings::getMysqlDateTimeFormat();
        $this->club = $club;
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->doctype = $doctype;
        $this->assignedTo = $assignedTo;
        if ($this->doctype == 'WORKGROUP') {
                $this->all = $this->container->get('translator')->trans('ALL_WORKGROUP');
        } elseif ($this->doctype == 'CONTACT') {
            $this->all = $this->container->get('translator')->trans('ALL_CONTACT');
        } elseif ($this->doctype == 'TEAM') {
            $terminologyService = $this->container->get('fairgate_terminology_service');
            $this->all = $this->container->get('translator')->trans('ALL_TEAM', array('%teams%' => $terminologyService->getTerminology('Team', $this->container->getParameter('plural'))));
        } else {
            $terminologyService = $this->container->get('fairgate_terminology_service');
            $clubsTerminology = $terminologyService->getTerminology('Club', $this->container->getParameter('plural'));
            $this->all = $this->container->get('translator')->trans('ALL')." ".$clubsTerminology.'|@|'.$this->container->get('translator')->trans('DOCUMENT_ALL_CLUBS_EXCEPT');
        }
    }

    /**
     * For collect  actual table columns
     * @return array
     */
    public function getDocColumns()
    {
        foreach ($this->columnDatas as $key => $columndata) {
            switch ($columndata['type']) {
                case "FO":
                    $this->fileOptionField($columndata, $key);
                    break;
                case "DO":
                    $this->dateOptionField($columndata, $key);

                    break;
                case "UO":
                    $this->userOptionField($columndata, $key);
                    break;

            }
        }

        return $this->columnArray;
    }

    /**
     * fileOptionField
     * @param type $columndata clubdatacolumn
     * @param type $key        index
     */
    public function fileOptionField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {
            case "SIZE":               
                $this->columnArray[$this->key] = " IF(fg_dm_version.size, IF(ROUND( (fg_dm_version.size/1048576), 2 ) < 0.1, ' < 0.10 MB', CONCAT(ROUND( (fg_dm_version.size/1048576), 2 ), ' MB' )) ,'') AS {$columndata['name']}";
                break;
            case "DESCRIPTION":
                $this->columnArray[$this->key] = "fdd.description  AS {$columndata['name']}";
                break;
            case "DEPOSITED_WITH":
                if ($this->doctype == 'CLUB') {
                    $clubCondition= ($this->club->get('type')== 'sub_federation') ? " AND( fc.id='{$this->club->get('id')}' OR fc.id IN(SELECT c.id FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := {$this->club->get('id')},@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id))":'';
                    $depositedwith = " CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                         . "(SELECT GROUP_CONCAT(COALESCE(NULLIF(fci18n.title_lang,''), fc.title) SEPARATOR '#') FROM fg_dm_assigment dda JOIN fg_club as fc ON fc.id=dda.club_id LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang = '{$this->club->get('default_lang')}') $clubCondition WHERE dda.document_id=fdd.id ) WHEN 'ALL' THEN "
                         . "IF((SELECT count(id) FROM fg_dm_assigment_exclude DAE2 WHERE DAE2.document_id=fdd.id) ,(SELECT CONCAT('ALLC#$this->all #',GROUP_CONCAT(COALESCE(NULLIF(fci18n.title_lang,''), fc.title) SEPARATOR '#')) FROM fg_dm_assigment_exclude dde JOIN fg_club as fc ON fc.id=dde.club_id LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang = '{$this->club->get('default_lang')}') $clubCondition WHERE dde.document_id=fdd.id ),'ALLC#$this->all ') "
                         . "ELSE '-' END";

                     $this->columnArray[$this->key] = " $depositedwith AS {$columndata['name']}";

                } elseif ($this->doctype == 'WORKGROUP') {
                    $depositedwith = " CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                            . "(SELECT GROUP_CONCAT(IF(fri18n.title_lang!='' AND fri18n.title_lang IS NOT NULL,fri18n.title_lang, fr.title) SEPARATOR '#' ) FROM fg_dm_assigment dda JOIN fg_rm_role fr ON fr.id=dda.role_id  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang='{$this->club->get("default_lang")}' AND fr.club_id='{$this->club->get('id')}' WHERE dda.document_id=fdd.id GROUP BY fr.club_id ) WHEN 'ALL' THEN "
                           // . "(SELECT GROUP_CONCAT(DISTINCT(IF(fri18n.title_lang!='' AND fri18n.title_lang IS NOT NULL,fri18n.title_lang, fr.title)) SEPARATOR ', ' ) FROM fg_rm_role fr LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang='{$this->club->get("default_lang")}' AND fr.category_id='{$this->club->get("club_workgroup_id")}'  ) ELSE '-' END";
                            . " '$this->all' ELSE '-' END";
                    $this->columnArray[$this->key] = " $depositedwith AS {$columndata['name']}";

                } elseif ($this->doctype == 'CONTACT') {
                    $firstname = "`" . $this->container->getParameter('system_field_firstname') . "`";
                    $lastname = "`" . $this->container->getParameter('system_field_lastname') . "`";

                    $depositedwith = " CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                            . "(SELECT GROUP_CONCAT(DISTINCT( CONCAT(contactName(fc.id),'|',fc.id)) SEPARATOR '# ' ) FROM fg_dm_assigment dda JOIN fg_cm_contact as fc ON fc.id=dda.contact_id  LEFT JOIN master_system ms ON ms.fed_contact_id=fc.id WHERE dda.document_id=fdd.id  GROUP BY dda.club_id ) WHEN 'ALL' THEN "
                            //. "(SELECT GROUP_CONCAT(DISTINCT( IF (fc.is_company=0 ,CONCAT(" . $lastname . ",' '," . $firstname . ",'|',fc.id ), CONCAT(`9`,'|',fc.id) )) SEPARATOR ', ' ) FROM  fg_cm_contact as fc JOIN master_system ms ON ms.contact_id=fc.id AND  fc.club_id='{$this->club->get('id')}' GROUP BY fc.club_id  ) ELSE '-' END";
                            ." '$this->all' ELSE '-' END";
                    $this->columnArray[$this->key] = " $depositedwith AS {$columndata['name']}";  
                } elseif ($this->doctype == 'TEAM') {                    
                    $depositedwith = " CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                            . "(SELECT GROUP_CONCAT(IF(fri18n.title_lang!='' AND fri18n.title_lang IS NOT NULL,fri18n.title_lang, fr.title) SEPARATOR '# ' ) FROM fg_dm_assigment dda JOIN fg_rm_role fr ON fr.id=dda.role_id  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang='{$this->club->get("default_lang")}' AND fr.club_id='{$this->club->get('id')}'  WHERE dda.document_id=fdd.id GROUP BY fr.club_id ) WHEN 'ALL' THEN "
                           // . "(SELECT GROUP_CONCAT(DISTINCT(IF(fri18n.title_lang!='' AND fri18n.title_lang IS NOT NULL,fri18n.title_lang, fr.title)) SEPARATOR ', ' ) FROM  fg_rm_role fr LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang='{$this->club->get("default_lang")}' WHERE fr.category_id='{$this->club->get("club_team_id")}'  ) ELSE '-' END";
                            ." '$this->all' ELSE '-' END";
                    $this->columnArray[$this->key] = " $depositedwith AS {$columndata['name']}";
                }
                break;
            case "DEPOSITED_WITH_ASSIGNED":
                if ($this->doctype == 'CLUB') {
                    $clubCondition = '';
                    if ($this->club->get('type') === 'sub_federation') {
                        $clubCondition =" AND( fc.id='{$this->club->get('id')}' OR fc.id IN(SELECT c.id FROM (SELECT  sublevelClubs(id) AS id, @level AS level FROM (SELECT  @start_with := {$this->club->get('id')},@id := @start_with,@level := 0) vars, fg_club WHERE @id IS NOT NULL) ho JOIN fg_club c ON c.id = ho.id))";
                    }
                    $depositedwith = " CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                     . "(SELECT GROUP_CONCAT(COALESCE(NULLIF(fci18n.title_lang,''), fc.title) SEPARATOR '#') FROM fg_dm_assigment dda JOIN fg_club as fc ON fc.id=dda.club_id LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang = '{$this->club->get('default_lang')}') $clubCondition WHERE dda.document_id=fdd.id AND dda.club_id != '$this->assignedTo') WHEN 'ALL' THEN "
                     . "IF((SELECT count(id) FROM fg_dm_assigment_exclude DAE2 WHERE DAE2.document_id=fdd.id) ,(SELECT CONCAT('ALLC#$this->all #',GROUP_CONCAT(COALESCE(NULLIF(fci18n.title_lang,''), fc.title) SEPARATOR '#')) FROM fg_dm_assigment_exclude dde JOIN fg_club as fc ON fc.id=dde.club_id LEFT JOIN fg_club_i18n fci18n ON (fci18n.id = fc.id AND fci18n.lang = '{$this->club->get('default_lang')}') $clubCondition WHERE dde.document_id=fdd.id ),'ALLC#$this->all ') "
                     . "ELSE '-' END";
                    $this->columnArray[$this->key] = " $depositedwith AS {$columndata['name']}";

                } elseif ($this->doctype == 'CONTACT') {
                    $firstname = "`" . $this->container->getParameter('system_field_firstname') . "`";
                    $lastname = "`" . $this->container->getParameter('system_field_lastname') . "`";
                    $depositedwith = " CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                            . "(SELECT GROUP_CONCAT(DISTINCT( IF (fc.is_company=0 ,CONCAT(" . $lastname . ",' '," . $firstname . ",'|',fc.id ), CONCAT(`9`,'|',fc.id) )) SEPARATOR '# ' ) FROM fg_dm_assigment dda JOIN fg_cm_contact as fc ON fc.id=dda.contact_id  LEFT JOIN master_system ms ON ms.fed_contact_id=fc.id WHERE dda.document_id=fdd.id AND ms.fed_contact_id != '$this->assignedTo'  GROUP BY dda.club_id ) WHEN 'ALL' THEN "
                            //. "(SELECT GROUP_CONCAT(DISTINCT( IF (fc.is_company=0 ,CONCAT(" . $lastname . ",' '," . $firstname . ",'|',fc.id ), CONCAT(`9`,'|',fc.id) )) SEPARATOR ', ' ) FROM  fg_cm_contact as fc JOIN master_system ms ON ms.contact_id=fc.id AND  fc.club_id='{$this->club->get('id')}' GROUP BY fc.club_id  ) ELSE '-' END";
                            ." '$this->all' ELSE '-' END";
                    $this->columnArray[$this->key] = " $depositedwith AS {$columndata['name']}";                    
                }
                break;
            case "VISIBLE_TO":
                if ($this->doctype == 'TEAM') {
                    $function =" (CASE WHEN (fdd.visible_for ='team_functions' ) THEN "
                            . "(SELECT GROUP_CONCAT(IF(frfi18n.title_lang!='' AND frfi18n.title_lang IS NOT NULL,frfi18n.title_lang, frf.title) SEPARATOR '# ' ) FROM fg_dm_team_functions ftf INNER JOIN fg_rm_function frf ON frf.id=ftf.function_id AND frf.category_id='{$this->club->get("club_team_id")}'  LEFT JOIN fg_rm_function_i18n frfi18n ON frfi18n.id=frf.id AND frfi18n.lang='{$this->club->get("default_lang")}' WHERE ftf.document_id=fdd.id GROUP BY frf.category_id )  "
                            . " ELSE '-' END ) ";

                    $this->columnArray[$this->key] = " fdd.visible_for AS {$columndata['name']}, $function AS FO_FUNCTIONS";
                    break;
                } elseif ($this->doctype == 'WORKGROUP') {
                    $this->columnArray[$this->key] = " fdd.visible_for AS {$columndata['name']}";
                } else {
                     $this->columnArray[$this->key] = " fdd.is_visible_to_contact AS {$columndata['name']}";
                }
                break;
            case 'ISPUBLIC':
                $this->columnArray[$this->key] = "fdd.is_publish_link  AS {$columndata['name']}";
                break;
            case "CATEGORY":
                $this->columnArray[$this->key] = "(select CONCAT((IF(fddci18n.title_lang!='' AND fddci18n.title_lang IS NOT NULL ,fddci18n.title_lang,fddc.title)),"
                    . "' - ',"
                    . "(IF(fddsi18n.title_lang!='' AND fddsi18n.title_lang IS NOT NULL ,fddsi18n.title_lang,fdds.title)))"
                    . "  FROM fg_dm_document_category fddc "
                    . "LEFT JOIN fg_dm_document_category_i18n fddci18n ON fddc.id=fddci18n.id AND fddci18n.lang='{$this->club->get("default_lang")}' "
                    . "INNER JOIN fg_dm_documents fd ON fddc.id=fd.category_id "
                    . "LEFT JOIN fg_dm_document_subcategory fdds ON fdds.id=fd.subcategory_id "
                    . "LEFT JOIN fg_dm_document_subcategory_i18n fddsi18n ON fddsi18n.id=fdds.id AND fddsi18n.lang='{$this->club->get("default_lang")}'"
                    . " where fd.id=fdd.id ) AS {$columndata['name']}";

                break;

        }
    }

    /**
     * For find the dateOptionField
     * @param type $columndata cluboptioncolumns
     * @param type $key        index
     */
    public function dateOptionField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {

            case "UPLOADED":
                $this->columnArray[$this->key] = " date_format(fg_dm_version.created_at,'{$this->mysqlDateTimeFormat}') AS {$columndata['name']}";
                break;
            case "LAST_UPDATED":
                $this->columnArray[$this->key] = " date_format(fg_dm_version.updated_at,'{$this->mysqlDateTimeFormat}') AS {$columndata['name']}";
                break;
        }
    }

    /**
     * For find actual clubsystem columns
     * @param type $columndata clubsystemfield
     * @param type $key        index
     */
    public function userOptionField($columndata, $key)
    {
        $this->key = $key;
        switch ($columndata['id']) {
            case "UPLOADED_BY":
                $this->columnArray[$this->key] = " contactName(fg_dm_version.created_by) AS {$columndata['name']}";
                break;
            case "UPDATED_BY":
                $this->columnArray[$this->key] = " contactName(fg_dm_version.updated_by) AS {$columndata['name']}";
                break;
            case "AUTHOR":
                $this->columnArray[$this->key] = " fdd.author AS {$columndata['name']}";
                break;
        }
    }
}
