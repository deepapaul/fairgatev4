<?php

namespace Internal\GeneralBundle\Util;

use Common\UtilityBundle\Util\FgSettings;

/**
 * Column settings for internal documents
 */
class InternalDocumentColumnSettings
{

    /**
     * Selected column settings
     * 
     * @var array 
     */
    private $columnDatas;

    /**
     * Mysql date time format
     * 
     * @var datetime 
     */
    private $mysqlDateTimeFormat;

    /**
     * Club object
     * 
     * @var object 
     */
    private $club;

    /**
     * Translator object
     * 
     * @var object 
     */
    private $translator;

    /**
     * Container object
     * 
     * @var object 
     */
    private $container;

    /**
     * Connection object
     * 
     * @var object 
     */
    private $conn;

    /**
     * Default columns array
     *
     * @var array 
     */
    private $columnArray;

    /**
     * Terminology service
     * 
     * @var service 
     */
    private $terminologyService;

    /**
     * Document type
     * 
     * @var string 
     */
    private $doctype;

    /**
     * 
     * @param object $container   Container object
     * @param array  $columns     Table columns
     * @param string $doctype     TEAM/WORKGROUP/ALL
     * @param string $docMenuType all/new/subcategory
     */
    public function __construct($container, $columns, $doctype = 'ALL')
    {
        $this->columnDatas = $columns;
        $this->mysqlDateTimeFormat = FgSettings::getMysqlDateTimeFormat();
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->translator = $this->container->get('translator');
        $this->terminologyService = $this->container->get('fairgate_terminology_service');
        $this->conn = $this->container->get('database_connection');
        $this->doctype = $doctype;
        $this->contact = $this->container->get('contact');
    }

    /**
     * To get the table columns query of Internal dTeam/Workgroup documents
     * 
     * @return array $this->columnArray 
     */
    public function getDocColumns()
    {
        //default columns needed always
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTitle = $terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
        $this->columnArray = array(' fdd.id as documentId', ' fdd.document_type as documentType', ' fdd.category_id as categoryId', ' fdd.subcategory_id as subCategoryId', ' fdd.club_id as clubId', ' fdv.id as versionId', ' IF(fdi18.name_lang IS NULL OR fdi18.name_lang="", fdd.name, fdi18.name_lang) AS documentName', ' (IF(fdcs.contact_id IS NULL, 1, 0)) AS isUnread', ' fdv.file as fileName');
        if ($this->doctype == 'TEAM' || $this->doctype == 'WORKGROUP') {
            $roleIdsArr = ($this->doctype == 'TEAM') ? array_keys($this->contact->get('teams')) : array_keys($this->contact->get('workgroups'));
            $roleIds = implode(',', $roleIdsArr);
            $this->columnArray['depositedRoleIds'] = " (CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                . "(SELECT GROUP_CONCAT(dda.role_id SEPARATOR '*##*') FROM fg_dm_assigment dda WHERE dda.document_id=fdd.id AND dda.role_id IN (" . $roleIds . ") GROUP BY fdd.id) "
                . "WHEN 'ALL' THEN 'ALL' ELSE '-' END) AS depositedRoleIds";
            $this->columnArray['visibleForRights'] = " fdd.visible_for AS visibleForRights";
            $this->columnArray['visibleFunctionIds'] = " GROUP_CONCAT(fdtf.function_id) AS visibleFunctionIds";
             $this->columnArray['isPublic'] = " fdd.is_publish_link AS isPublic";
        }
        $allWorkgroups = $this->translator->trans('ALL_WORKGROUP');
        $allTeams = $this->translator->trans('ALL_TEAM', array('%teams%' => $this->terminologyService->getTerminology('Team', $this->container->getParameter('plural'))));
        if ($this->doctype == 'ALL') {
            $personalText = $this->translator->trans('DOCUMENTS_SECTION_PERSONAL');
            $clubTerminology = $this->terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
            $documentSection = " (CASE WHEN fdd.document_type = 'CLUB' THEN '$clubTerminology' "
                . "WHEN fdd.document_type = 'TEAM' THEN (IF(fdd.deposited_with = 'ALL', '$allTeams', (SELECT GROUP_CONCAT(IF(fri18n.title_lang != '' AND fri18n.title_lang IS NOT NULL, fri18n.title_lang, fr.title) SEPARATOR '*##*' ) FROM fg_dm_assigment fda2 LEFT JOIN fg_rm_role fr ON fr.id = fda2.role_id LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id = fr.id AND fri18n.lang = '{$this->club->get("default_lang")}' WHERE fda2.document_id = fdd.id GROUP BY fdd.id))) "
                . "WHEN fdd.document_type = 'WORKGROUP' THEN (IF(fdd.deposited_with = 'ALL', '$allWorkgroups', (SELECT GROUP_CONCAT(IF(fr.is_executive_board = 1, '$executiveBoardTitle' , IF(fri18n.title_lang != '' AND fri18n.title_lang IS NOT NULL, fri18n.title_lang, fr.title)) SEPARATOR '*##*' ) FROM fg_dm_assigment fda2 LEFT JOIN fg_rm_role fr ON fr.id = fda2.role_id LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id = fr.id AND fri18n.lang = '{$this->club->get("default_lang")}' WHERE fda2.document_id = fdd.id GROUP BY fdd.id))) "
                . "ELSE '$personalText' END) AS documentSection";
            $this->columnArray['documentSection'] = $documentSection;
        }
        
        $this->loopDocColumnData($allTeams);
        
        return $this->columnArray;
    }
    
    /**
     * To get the table columns query
     * 
     * @param array $allTeams      Team Ids for getting the document
     * @param array $allWorkgroups Workgroup Ids for getting the document
     * 
     * @return array $this->columnArray 
     */
    private function loopDocColumnData($allTeams, $allWorkgroups) {
        foreach ($this->columnDatas as $key => $columndata) {

            switch ($columndata['id']) {
                case "SIZE":
                    $this->columnArray['fileSize'] = " fdv.size AS {$columndata['name']}";
                    break;
                case "ISPUBLIC":
                    $this->columnArray['isPublic'] = " fdd.is_publish_link  AS {$columndata['name']}";
                    $this->columnArray['isPublicOrg'] = " fdd.is_publish_link  AS isPublicOrg";
                    break;
                case "DESCRIPTION":
                    $this->columnArray['description'] = " fdd.description  AS {$columndata['name']}";
                    break;
                case "DEPOSITED_WITH":
                    $executiveboard = $this->terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'));
                    $all = ($this->doctype == 'TEAM') ? $allTeams : $allWorkgroups;
                    $depositedwith = " (CASE (fdd.deposited_with) WHEN 'SELECTED' THEN "
                        . "(SELECT GROUP_CONCAT(CASE (fr.is_executive_board) WHEN '1' THEN '$executiveboard '"
                        . " WHEN '0' THEN (IF(fri18n.title_lang != '' AND fri18n.title_lang IS NOT NULL, fri18n.title_lang, fr.title)) END SEPARATOR '*##*' ) FROM fg_dm_assigment dda LEFT JOIN fg_rm_role fr ON fr.id=dda.role_id  LEFT JOIN fg_rm_role_i18n fri18n ON fri18n.id=fr.id AND fri18n.lang='{$this->club->get("default_lang")}' WHERE dda.document_id=fdd.id GROUP BY fdd.id) "
                        . "WHEN 'ALL' THEN '$all' ELSE '-' END) AS {$columndata['name']}";
                    $this->columnArray['depositedWith'] = $depositedwith;
                    break;
                case "VISIBLE_TO":
                    if ($this->doctype == 'TEAM') {
                        $function = " (CASE WHEN (fdd.visible_for = 'team_functions') THEN "
                            . "(SELECT GROUP_CONCAT(IF(frfi18n.title_lang != '' AND frfi18n.title_lang IS NOT NULL, frfi18n.title_lang, frf.title) SEPARATOR '*##*' ) FROM fg_dm_team_functions ftf INNER JOIN fg_rm_function frf ON frf.id=ftf.function_id LEFT JOIN fg_rm_function_i18n frfi18n ON frfi18n.id=frf.id AND frfi18n.lang='{$this->club->get("default_lang")}' WHERE ftf.document_id=fdd.id GROUP BY frf.category_id)  "
                            . " ELSE '-' END) AS visibleToFunctions";
                        $this->columnArray['visibleToFunctions'] = $function;
                    }
                    $this->columnArray['visibleTo'] = " fdd.visible_for AS {$columndata['name']}";
                    break;
                case "CATEGORY":
                    $this->columnArray['category'] = " (select CONCAT((IF(fddci18n.title_lang != '' AND fddci18n.title_lang IS NOT NULL, fddci18n.title_lang, fddc.title)), "
                        . "'  -  ', "
                        . "(IF(fddsi18n.title_lang != '' AND fddsi18n.title_lang IS NOT NULL, fddsi18n.title_lang, fdds.title))) "
                        . "FROM fg_dm_document_category fddc "
                        . "LEFT JOIN fg_dm_document_category_i18n fddci18n ON fddc.id=fddci18n.id AND fddci18n.lang='{$this->club->get("default_lang")}' "
                        . "LEFT JOIN fg_dm_document_subcategory fdds ON fdds.category_id=fddc.id "
                        . "LEFT JOIN fg_dm_document_subcategory_i18n fddsi18n ON fddsi18n.id=fdds.id AND fddsi18n.lang='{$this->club->get("default_lang")}' "
                        . "WHERE fddc.id=fdd.category_id AND fdds.id=fdd.subcategory_id) AS {$columndata['name']}";
                    break;
                case "UPLOADED":
                    $this->columnArray['uploadedOn'] = " date_format(fdv.created_at, '" . $this->mysqlDateTimeFormat . "') AS {$columndata['name']}";
                    $this->columnArray['uploadedOnOrg'] = " fdv.created_at AS uploadedOnOrg";
                    break;
                case "LAST_UPDATED":
                    $this->columnArray['lastUpdated'] = " date_format(fdv.updated_at, '" . $this->mysqlDateTimeFormat . "') AS {$columndata['name']}";
                    $this->columnArray['lastUpdatedOrg'] = " fdv.updated_at AS lastUpdatedOrg";
                    break;
                case "UPLOADED_BY":
                    $this->columnArray['uploadedBy'] = " contactName(fdv.created_by) AS {$columndata['name']}";
                    break;
                case "UPDATED_BY":
                    $this->columnArray['updatedBy'] = " contactName(fdv.updated_by) AS {$columndata['name']}";
                    break;
                case "AUTHOR":
                    $this->columnArray['author'] = " fdd.author AS {$columndata['name']}";
                    break;
            }
        }
    }
}
