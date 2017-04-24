<?php

namespace Clubadmin\DocumentsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Utility function to handle document category
 */
class DocumentCategory {

    protected $em;
    private $container;
    private $club;
    private $clubId;
    private $clubtype;
    private $conn;
    private $docType;
    private $session;
    private $contact;
    private $contactId;
    /**
     * Constructor of DocumentCategory
     * 
     * @param object $container ContainerInterface
     * @param string $docType Document type CLUB/CONTACT/TEAM/WORKGROUP
     */
    public function __construct(ContainerInterface $container, $docType) {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get("id");
        $this->clubtype = $this->club->get("type");
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->docType = $docType;
        $this->session = $this->container->get('session');
        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');
    }
        
    /**
     * Function to iterate document category details array to form heirarchical array of documents category details.
     *
     * @param array  $categoryDetails Array of document category details
     *
     * @return array
     */
    public function iterateDocumentCategories($categoryDetails)
    {
        $translator = $this->container->get('translator');
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $categoryData = $catDataArray = $showFilterArray = array();
        $cnt = $prevCatId = 0;
        $prevGroupTitle = '';
        $totalCount = count($categoryDetails);
        foreach ($categoryDetails as $categoryDetail) {
            $cnt++;
            if (($categoryDetail['catId'] != $prevCatId) && ($prevCatId != 0)) {
                if (!isset($categoryData['DOCS-' . $this->club->get('id')])) {
                    $categoryData['DOCS-' . $this->club->get('id')]['entry'] = array();
                }
                $categoryData[$prevGroupTitle]['entry'][] = $catDataArray;
                $catDataArray = array();
            }
            $catDataArray['id'] = '' . $categoryDetail['catId'] . '';
            $catDataArray['type'] = 'select';
            $catDataArray['title'] = $categoryDetail['catTitle'];
            $isDraggable    = ($categoryDetail['groupTitle']=='DOCS-' . $this->club->get('id')) ? 1:0;

            if ($categoryDetail['subCatId'] != '') {
                $documentCount = ($categoryDetail['clubId'] == $this->club->get('id')) ? $categoryDetail['docCount'] : $categoryDetail['depositedCount'];
                $catDataArray['input'][] = array('id' => '' . $categoryDetail['subCatId'] . '', 'categoryId' => '' . $categoryDetail['catId'] . '', 'type' => 'select', 'title' => $categoryDetail['subCatTitle'], 'itemType' => $categoryDetail['groupTitle'], 'count' => $documentCount, 'show_filter' => 1, 'bookMarkId' => $categoryDetail['bookMarkId'],'draggable'=>$isDraggable);
                $showFilterArray[] = $categoryDetail['groupTitle'];
            } else {
                $catDataArray['input'] = array();
                $catDataArray['show_filter'] = 0;
            }
            if ($cnt == $totalCount) {
                if (!isset($categoryData['DOCS-' . $this->club->get('id')])) {
                    $categoryData['DOCS-' . $this->club->get('id')]['entry'] = array();
                }
                $categoryData[$categoryDetail['groupTitle']]['entry'][] = $catDataArray;
            }
            $prevCatId = $categoryDetail['catId'];
            $prevGroupTitle = $categoryDetail['groupTitle'];
        }
        if (!isset($categoryData['DOCS-' . $this->club->get('id')])) {
            $categoryData['DOCS-' . $this->club->get('id')]['entry'] = array();
        }
        $fixedOptions = array(
            '0' => array('0' => array('id' => '', 'title' => "- " . $translator->trans('DOCUMENT_SELECT_CATEGORY') . " -")),
            '1' => array( '0' => array('id' => 'any', 'title' => $translator->trans('DOCUMENT_ANY_SUBCATEGORY')),
                          '1' => array('id' => '', 'title' => $translator->trans('DOCUMENT_SELECT_SUBCATEGORY')))    );
        if ($this->docType == 'CLUB') {
            if ($this->clubtype == 'federation') {
                $docSectionTitle = ucfirst($translator->trans('FEDERATION_DOCS', array('%federation%' => $terminologyService->getTerminology('Federation', $this->container->getParameter('singular')))));
            } else if ($this->clubtype == 'sub_federation') {
                $docSectionTitle = ucfirst($translator->trans('SUBFEDERATION_DOCS', array('%subfederation%' => $terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')))));
            } else {
                $docSectionTitle = ucfirst($translator->trans('CLUB_DOCS', array('%club%' => $terminologyService->getTerminology('Club', $this->container->getParameter('singular')))));
            }
        } else if ($this->docType == 'TEAM') {
            $docSectionTitle = ucfirst($translator->trans('TEAM_DOCS', array('%team%' => $terminologyService->getTerminology('Team', $this->container->getParameter('plural')))));
        } else if ($this->docType == 'WORKGROUP') {
            $docSectionTitle = ucfirst($translator->trans('WORKGROUP_DOCS'));
        } else if ($this->docType == 'CONTACT') {
            $docSectionTitle = ucfirst($translator->trans('CONTACT_DOCS'));
        }
        if (isset($categoryData['DOCS-' . $this->club->get('id')])) {
            $categoryData['DOCS-' . $this->club->get('id')]['id'] = 'DOCS-' . $this->club->get('id');
            $categoryData['DOCS-' . $this->club->get('id')]['show_filter'] = in_array('DOCS-' . $this->club->get('id'), $showFilterArray) ? 1 : 0;
            $categoryData['DOCS-' . $this->club->get('id')]['title'] = $docSectionTitle;
            $categoryData['DOCS-' . $this->club->get('id')]['fixed_options'] = $fixedOptions;
        }
        if (isset($categoryData['FDOCS-' . $this->club->get('sub_federation_id')])) {
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['id'] = 'FDOCS-' . $this->club->get('sub_federation_id');
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['show_filter'] = in_array('FDOCS-' . $this->club->get('sub_federation_id'), $showFilterArray) ? 1 : 0;
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['title'] = ucfirst($translator->trans('SUBFEDERATION_DOCS', array('%subfederation%' => $terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')))));
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['fixed_options'] = $fixedOptions;
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['logo'] = FgUtility::getClubLogo($this->club->get('sub_federation_id'), $this->em);
        }
        if (isset($categoryData['FDOCS-' . $this->club->get('federation_id')])) {
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['id'] = 'FDOCS-' . $this->club->get('federation_id');
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['show_filter'] = in_array('FDOCS-' . $this->club->get('federation_id'), $showFilterArray) ? 1 : 0;
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['title'] = ucfirst($translator->trans('FEDERATION_DOCS', array('%federation%' => $terminologyService->getTerminology('Federation', $this->container->getParameter('singular')))));
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['fixed_options'] = $fixedOptions;
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['logo'] = FgUtility::getClubLogo($this->club->get('federation_id'), $this->em);
        }

        return $categoryData;
    }
    
    /**
     * Function to iterate document category details array to form heirarchical array of documents for internal sidebar.
     *
     * @param array $categoryDetails Array of document category details
     * @param int   $currentRoleId   Current workgroup/team Id (null in case of personal documents)
     *
     * @return array
     */
    public function iterateDocumentCategoriesForInternal($categoryDetails, $currentRoleId)
    {
        $translator = $this->container->get('translator');
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $typeTitles = $categoryData = $catDataArray = $showFilterArray = array();
        $prevCatId = $cnt = 0;
        $prevGroupTitle = '';
        $totalCount = count($categoryDetails);
        foreach ($categoryDetails as $categoryDetail) {
            $typeTitle = ($this->docType == "CLUB") ? $categoryDetail['groupTitle'] : $this->docType;
            if(!in_array($typeTitle, $typeTitles)) {
                $typeTitles[] = $typeTitle;
            }
            $cnt++;
            if (($categoryDetail['catId'] != $prevCatId) && ($prevCatId != 0)) {
                if (!isset($categoryData[$typeTitle])) {
                    $categoryData[$typeTitle]['entry'] = array();
                }
                $categoryData[$prevGroupTitle]['entry'][] = $catDataArray;
                $catDataArray = array();
            }
            $catDataArray['id'] = '' . $categoryDetail['catId'] . '';
            $catDataArray['title'] = $categoryDetail['catTitle'];
            $isDraggable    = ($categoryDetail['groupTitle'] == $typeTitle ) ? 1:0;

            if ($categoryDetail['subCatId'] != '') {
                $catDataArray['input'][] = array('id' => '' . $categoryDetail['subCatId'] . '', 'categoryId' => '' . $categoryDetail['catId'] . '',  'title' => $categoryDetail['subCatTitle'], 'itemType' => $this->docType, 'draggable'=>$isDraggable);
                $showFilterArray[] = $categoryDetail['groupTitle'];
            } else {
                $catDataArray['input'] = array();
            }
            if ($cnt == $totalCount) {
                if (!isset($categoryData[$typeTitle])) {
                    $categoryData[$typeTitle]['entry'] = array();
                }
                $categoryData[$categoryDetail['groupTitle']]['entry'][] = $catDataArray;
            }
            $prevCatId = $categoryDetail['catId'];
            $prevGroupTitle = $categoryDetail['groupTitle'];
        }
        if (!isset($categoryData[$typeTitle])) {
            $categoryData[$typeTitle]['entry'] = array();
        }
        if ($this->docType == 'CLUB') {
            if ($this->clubtype == 'federation') {
                $docSectionTitle = ucfirst($terminologyService->getTerminology('Federation', $this->container->getParameter('singular')));
            } else if ($this->clubtype == 'sub_federation') {
                $docSectionTitle = ucfirst($terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')));
            } else {
                $docSectionTitle = ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular')));
            }
        } else if ($this->docType == 'TEAM') {
            $docSectionTitle = ($currentRoleId == "") ? ucfirst($terminologyService->getTerminology('Team', $this->container->getParameter('plural'))) : ucfirst($translator->trans('DOC_CATEGORIES'));
        } else if ($this->docType == 'WORKGROUP') {
            $docSectionTitle = ($currentRoleId == "") ? ucfirst($translator->trans('DOC_WORKGROUPS')) : ucfirst($translator->trans('DOC_CATEGORIES'));
        } else if ($this->docType == 'CONTACT') {
            $docSectionTitle = ucfirst($translator->trans('DOC_PERSONAL'));
        }

        foreach($typeTitles as $typeTitleName) {
            if (isset($categoryData[$typeTitleName])) {
                $categoryData[$typeTitleName]['id'] = $typeTitleName;
                $categoryData[$typeTitleName]['title'] = $docSectionTitle;
            }
        }

        if (isset($categoryData['FDOCS-' . $this->club->get('sub_federation_id')])) {  //for type = CLUB only
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['id'] = 'FDOCS-' . $this->club->get('sub_federation_id');
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['title'] = ucfirst($terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular')));
            $categoryData['FDOCS-' . $this->club->get('sub_federation_id')]['logo'] = FgUtility::getClubLogo($this->club->get('sub_federation_id'), $this->em);

        }
        if (isset($categoryData['FDOCS-' . $this->club->get('federation_id')])) {  //for type = CLUB only
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['id'] = 'FDOCS-' . $this->club->get('federation_id');
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['title'] = ucfirst($terminologyService->getTerminology('Federation', $this->container->getParameter('singular')));
            $categoryData['FDOCS-' . $this->club->get('federation_id')]['logo'] = FgUtility::getClubLogo($this->club->get('federation_id'), $this->em);
        }
        return $categoryData;
    }
    
    /**
     * Method to get conditions for 'CONTACT' documents to list categories in case of non admin
     *
     * @param object $categoryDetails Query builder object
     * @param int    $contactId       Current contact Id
     *
     * @return array
     */
    public function getContactConditionForDocCategories ($categoryDetails, $contactId) {
        $categoryObj = $categoryDetails->andWhere('doc.id IS NOT NULL AND doc.isVisibleToContact = 1 ')
                                ->andWhere("( (doc.id NOT IN ( SELECT CDD.id FROM CommonUtilityBundle:FgDmDocuments CDD JOIN CommonUtilityBundle:FgDmAssigmentExclude CDA WITH CDA.document = CDD.id WHERE CDA.contact = :contactId ) ) "
                                        . "AND (doc.id IN (SELECT CDD2.id FROM CommonUtilityBundle:FgDmDocuments CDD2 JOIN CommonUtilityBundle:FgDmAssigment CDA2 WITH CDA2.document=CDD2.id WHERE CDA2.documentType = :documentType AND CDA2.contact = :contactId) ) )");

        return array("categoryDetails" => $categoryObj, "extraParams" => array("contactId" => $contactId) );
    }
    
    /**
     * Method to get conditions for 'CLUB' documents to list categories in case of non admin
     *
     * @param object $categoryDetails    Query builder object
     * @param string $clubType           federation/standard_club/subfederation etc
     * @param array  $clubHeirarchyArray club hierarchies excluding current club
     *
     * @return array
     */
    public function getClubConditionForDocCategories($categoryDetails, $clubHeirarchyArray) {
        $extraParams = array();
        $categoryDetails = $categoryDetails->andWhere('doc.isVisibleToContact = 1 ')
                            ->andWhere("( (doc.depositedWith='ALL' AND (doc.id NOT IN (SELECT CDD.id FROM CommonUtilityBundle:FgDmDocuments CDD JOIN CommonUtilityBundle:FgDmAssigmentExclude CDA WITH CDA.document = CDD.id WHERE CDA.club = :clubId )) ) "
                                    . "OR (doc.depositedWith='SELECTED' AND  doc.id IN (SELECT CDD2.id FROM CommonUtilityBundle:FgDmDocuments CDD2 JOIN CommonUtilityBundle:FgDmAssigment CDA2 WITH CDA2.document=CDD2.id WHERE CDA2.documentType = :documentType AND CDA2.club = :clubId) )"
                                    . "OR (doc.depositedWith='NONE' AND  doc.club = :clubId ) )");
        if ( !in_array($this->clubType, array('federation', 'standard_club')) && count($clubHeirarchyArray) > 0 ) {
            $categoryDetails = $categoryDetails->andWhere('( c.club=:clubId OR c.club IN (:clubHeirarchies) )');
            $extraParams = array( "clubHeirarchies" => $clubHeirarchyArray);
        } else {
            $categoryDetails = $categoryDetails->andWhere(' c.club=:clubId ');
        }

        return array("categoryDetails" => $categoryDetails, "extraParams" => $extraParams );
    }
    
    /**
     * Method to get conditions for 'TEAM' documents to list categories in case of non admin
     *
     * @param object $categoryDetails    Query builder object
     * @param array  $adminstrativeRoles team ids which the contact has administrative roles
     * @param array  $memberRoles        team ids which the contact has member roles
     * @param int    $contactId          current contact Id
     * @param int    $teamCategory       team category (get from settings)
     * @param int    $currentRoleId      current team Id
     *
     * @return array
     */
    public function getTeamConditionForDocCategories($categoryDetails, $adminstrativeRoles, $memberRoles, $contactId, $teamCategory, $currentRoleId = "") {
        $extraParams = array();
        if(count($adminstrativeRoles) > 0 || count($memberRoles) > 0) {
            $groups = ((count($adminstrativeRoles) > 0 && $currentRoleId=="") || (in_array($currentRoleId, $adminstrativeRoles)) ) ? "'team','team_admin'" : "'team'";
            $roleTeamSql = "doc_ass3.role IN (:rolesTeam) ";
            $roleTeamAdminSql = "doc_ass4.role IN (:rolesTeamAdmin) ";
            $extraParams["rolesTeam"] = ($currentRoleId)? array($currentRoleId) : array_merge($adminstrativeRoles, $memberRoles);
            //if !($currentRoleId) then $adminstrativeRoles else ( if ($currentRoleId in $adminstrativeRoles) then $currentRoleId else empty array )
            $extraParams['rolesTeamAdmin'] = (!$currentRoleId)? ( ($adminstrativeRoles) ? $adminstrativeRoles : array(0) ) : (in_array($currentRoleId, $adminstrativeRoles) ? array($currentRoleId) : array(0)) ;

            if( ( count($adminstrativeRoles) > 0 && $currentRoleId == "") || (in_array($currentRoleId, $adminstrativeRoles)) ) { //in case team_functions and team admin
                $functionDQL = "";
                $assignmentFunctionDQL = "SELECT dm_doc2.id FROM CommonUtilityBundle:FgDmDocuments dm_doc2 JOIN CommonUtilityBundle:FgDmAssigment doc_ass WITH (doc_ass.document = dm_doc2.id AND doc_ass.documentType = :documentType AND doc_ass.role IN (:roles) )";
                $extraParams["roles"] = ($currentRoleId)? array($currentRoleId) : $adminstrativeRoles;
            }  else { //in case team_functions and team member
                $functionDQL = " AND doc.id IN (SELECT dm_doc.id FROM CommonUtilityBundle:FgDmDocuments dm_doc JOIN CommonUtilityBundle:FgDmTeamFunctions tm_fn WITH  tm_fn.document = dm_doc.id JOIN CommonUtilityBundle:FgRmCategoryRoleFunction cat_role_fun WITH (cat_role_fun.function = tm_fn.function AND cat_role_fun.category = :teamCategory) JOIN CommonUtilityBundle:FgRmRoleContact role_cont WITH (cat_role_fun.id = role_cont.fgRmCrf AND role_cont.contact = :contactId ) )";
                $functionDQL2 = "SELECT dm_doc2.id FROM CommonUtilityBundle:FgDmDocuments dm_doc2 JOIN CommonUtilityBundle:FgDmTeamFunctions tm_fn2 WITH  tm_fn2.document = dm_doc2.id JOIN CommonUtilityBundle:FgRmCategoryRoleFunction cat_role_fun2 WITH (cat_role_fun2.function = tm_fn2.function AND cat_role_fun2.category = :teamCategory) JOIN CommonUtilityBundle:FgRmRoleContact role_cont2 WITH (cat_role_fun2.id = role_cont2.fgRmCrf AND role_cont2.contact = :contactId ) ";

                $assignmentFunctionDQL = $functionDQL2." JOIN CommonUtilityBundle:FgDmAssigment doc_ass WITH (doc_ass.document = dm_doc2.id AND doc_ass.documentType = :documentType AND doc_ass.role = cat_role_fun2.role  AND doc_ass.role IN (:roles) )";
                $extraParams["roles"] = ($currentRoleId)? array($currentRoleId) : $memberRoles;
                $extraParams["contactId"] = $contactId;
                $extraParams["teamCategory"] = $teamCategory;
            }
        }

        // visibleFor = 'team'
        $assignmentFunctionDQL2 = "SELECT dm_doc3.id FROM CommonUtilityBundle:FgDmDocuments dm_doc3 JOIN CommonUtilityBundle:FgDmAssigment doc_ass3 WITH (doc_ass3.document = dm_doc3.id AND doc_ass3.documentType = :documentType AND ( $roleTeamSql ) )";
        // visibleFor = 'team_admin'
        $assignmentFunctionDQL3 = "SELECT dm_doc4.id FROM CommonUtilityBundle:FgDmDocuments dm_doc4 JOIN CommonUtilityBundle:FgDmAssigment doc_ass4 WITH (doc_ass4.document = dm_doc4.id AND doc_ass4.documentType = :documentType AND ( $roleTeamAdminSql ) )";

        $categoryDetails = $categoryDetails->andWhere(" ( doc.depositedWith='ALL' AND doc.visibleFor IN ($groups)  ) OR "
                . " ( doc.depositedWith='ALL' AND doc.visibleFor ='team_functions'   $functionDQL  ) OR "
                . " ( doc.depositedWith='SELECTED' AND doc.visibleFor IN ('team_functions') AND doc.id IN  ( $assignmentFunctionDQL ) ) OR "
                . " ( doc.depositedWith='SELECTED' AND ("
                . "doc.visibleFor = 'team'  AND doc.id IN  ( $assignmentFunctionDQL2 ) OR "
                . "doc.visibleFor = 'team_admin'  AND doc.id IN  ( $assignmentFunctionDQL3 ) "
                . "))"
            )  ;

        return array("categoryDetails" => $categoryDetails, "extraParams" => $extraParams );
    }
    
    /**
     * Method to get conditions for 'WORKGROUP' documents to list categories in case of non admin
     *
     * @param object $categoryDetails    Query builder object
     * @param array  $adminstrativeRoles workgroup ids which the contact has administrative roles
     * @param array  $memberRoles        workgroup ids which the contact has member roles
     * @param int    $currentRoleId      current workgroup id
     *
     * @return array
     */
    public function getWorkgroupConditionForDocCategories($categoryDetails, $adminstrativeRoles, $memberRoles, $currentRoleId = "") {
        $extraParams = array();
        if(count($adminstrativeRoles) > 0 || count($memberRoles) > 0) {
            $groups = ((count($adminstrativeRoles) > 0 && $currentRoleId=="") || (in_array($currentRoleId, $adminstrativeRoles)) ) ? "'workgroup','workgroup_admin'" : "'workgroup'";
            $roleWorkgroupSql = "doc_ass3.role IN (:rolesWorkgroup) ";
            $roleWorkgroupAdminSql = "doc_ass4.role IN (:rolesWorkgroupAdmin) ";
            $extraParams["rolesWorkgroup"] = ($currentRoleId)? array($currentRoleId) : array_merge($adminstrativeRoles, $memberRoles);
            //if !($currentRoleId) then $adminstrativeWorkgroups else ( if ($currentRoleId in $adminstrativeWorkgroups) then $currentRoleId else empty array )
            $extraParams["rolesWorkgroupAdmin"] = (!$currentRoleId)? ( ($adminstrativeRoles) ? $adminstrativeRoles : array(0) )  : (in_array($currentRoleId, $adminstrativeRoles) ? array($currentRoleId) : array(0)) ;
        }
        $assignmentFunctionDQL2 = "SELECT dm_doc3.id FROM CommonUtilityBundle:FgDmDocuments dm_doc3 JOIN CommonUtilityBundle:FgDmAssigment doc_ass3 WITH (doc_ass3.document = dm_doc3.id AND doc_ass3.documentType = :documentType AND ( $roleWorkgroupSql ) )";
        $assignmentFunctionDQL3 = "SELECT dm_doc4.id FROM CommonUtilityBundle:FgDmDocuments dm_doc4 JOIN CommonUtilityBundle:FgDmAssigment doc_ass4 WITH (doc_ass4.document = dm_doc4.id AND doc_ass4.documentType = :documentType AND ( $roleWorkgroupAdminSql ) )";

        $categoryDetails = $categoryDetails->andWhere(" ( doc.depositedWith='ALL' AND doc.visibleFor IN ($groups)  ) "
                . " OR ( doc.depositedWith='SELECTED' AND ( "
                . " doc.visibleFor = 'workgroup'  AND doc.id IN  ( $assignmentFunctionDQL2 ) OR "
                . " doc.visibleFor = 'workgroup_admin'  AND doc.id IN  ( $assignmentFunctionDQL3 )"
                . " ) )"
            )  ;

        return array("categoryDetails" => $categoryDetails, "extraParams" => $extraParams );
    }
}