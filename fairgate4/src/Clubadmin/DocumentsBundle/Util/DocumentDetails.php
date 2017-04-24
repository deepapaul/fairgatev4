<?php

namespace Clubadmin\DocumentsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgLogHandler;

/**
 * Utility function to handle document 
 */
class DocumentDetails {

    protected $em;
    private $container;
    private $clubId;
    private $conn;
    private $documentLogArr = array();
    private $translator;
    /**
     * Class DocumentDetails constructor
     * 
     * @param object $container ContainerInterface
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->_em = $this->container->get('doctrine')->getManager();
        $this->translator = $this->container->get('translator');
        $this->terminologyService = $this->container->get('fairgate_terminology_service');
    }
        
    /**
     * Function to get filter data of 'FILE' type.
     *
     * @param string $type            Document type
     * @param int    $clubTeamId      Team Category id
     * @param int    $clubWorkgroupId Workgroup Category id
     * @param string $clubDefaultLang Default Language
     * @param string $clubType        Club type
     * @param object $club            Club object
     *
     * @return array $fileTypeData Result filter data of 'FILE' type.
     */
    public function getFilterDataofFileType($type, $clubTeamId, $clubWorkgroupId, $clubDefaultLang, $clubType, $club)
    {
        $bookedModuleDetails = $club->get('bookedModulesDet');
        
        $inputArray = array('0' => array('id' => '', 'title' => ucfirst($this->translator->trans('SELECT_DEFAULT'))), '1' => array('id' => '1', 'title' => $this->translator->trans('YES')), '2' => array('id' => '0', 'title' => $this->translator->trans('NO')));
        $publicArray = array('0' => array('id' => '', 'title' => ucfirst($this->translator->trans('SELECT_DEFAULT'))), '1' => array('id' => '1', 'title' => $this->translator->trans('YES')), '2' => array('id' => '0', 'title' => $this->translator->trans('NO')));
        $depositedArray = array();
        if ($type == 'CLUB') {
            $visibleToTitle = ucfirst($this->translator->trans('CONTACTDOCUMENT_VISIBLE_TO_CONTACT'));
            $depositedType = 'text';
        } else if ($type == 'TEAM') {
            $visibleToTitle = ucfirst($this->translator->trans('DC_DOCUMENT_VISIBLE_TO'));
            $inputArray = array(
                '0' => array('id' => '', 'title' => ucfirst($this->translator->trans('SELECT_DEFAULT'))),
                '1' => array('id' => 'team', 'title' => ucfirst($this->translator->trans('DM_WHOLE_TEAMS', array('%team%' => $this->terminologyService->getTerminology('Team', $this->container->getParameter('singular')))))),
                '2' => array('id' => 'team_functions', 'title' => ucfirst($this->translator->trans('DM_TEAM_AND_FUNCTIONS', array('%team%' => $this->terminologyService->getTerminology('Team', $this->container->getParameter('singular'))))), 'type' => 'select', 'input' => $this->getTeamFunctions($clubTeamId, $clubDefaultLang)),
                '3' => array('id' => 'team_admin', 'title' => ucfirst($this->translator->trans('DM_TEAM_ADMINS', array('%team%' => $this->terminologyService->getTerminology('Team', $this->container->getParameter('singular')))))),
                '4' => array('id' => 'club_contact_admin', 'title' => ucfirst($this->translator->trans('DM_CLUB_ADMINS_DOC_ADMIN', array('%Club%' => $this->terminologyService->getTerminology('Club', $this->container->getParameter('singular'))))))
            );
            $depositedType = 'select';
            $depositedArray = $this->getTeamsOrWorkgroups('team', $clubTeamId, $clubDefaultLang, true, $this->translator, $this->terminologyService, $this->container);
        } else if ($type == 'WORKGROUP') {
            $visibleToTitle = ucfirst($this->translator->trans('DM_VISIBLE_TO_WORKGROUP'));
            $inputArray = array(
                '0' => array('id' => '', 'title' => ucfirst($this->translator->trans('SELECT_DEFAULT'))),
                '1' => array('id' => 'workgroup', 'title' => ucfirst($this->translator->trans('DM_WORKGROUP_CONTACTS_AND_ADMIN'))),
                '2' => array('id' => 'workgroup_admin', 'title' => ucfirst($this->translator->trans('DM_WORKGROUP_ADMIN'))),
                '3' => array('id' => 'main_document_admin', 'title' => ucfirst($this->translator->trans('DM_WORKGROUP_MAIN_AND_DOC_ADMIN'))),
            );
            $depositedType = 'select';
            $depositedArray = $this->getTeamsOrWorkgroups('workgroup', $clubWorkgroupId, $clubDefaultLang, true, $this->translator, $this->terminologyService, $this->container);
        } else if ($type == 'CONTACT') {
            $visibleToTitle = ucfirst($this->translator->trans('CONTACTDOCUMENT_VISIBLE_TO_CONTACT'));
            $depositedType = 'text';
        }
        $fileTypeData = array(
            'id' => 'FILE',
            'title' => ucfirst($this->translator->trans('DM_FILE')),
            'show_filter' => '1',
            'fixed_options' => array(
                '0' => array('0' => array('id' => '', 'title' => ucfirst($this->translator->trans('SELECT_DEFAULT')))),
                '2' => array(
                    '0' => array('id' => '', 'title' => $this->translator->trans('DOCUMENT_SELECT_FUNCTION')),
                    '1' => array('id' => 'any', 'title' => $this->translator->trans('DOCUMENT_ANY_FUNCTION'))
                )
            ),
            'entry' => array(
                '0' => array('id' => 'SIZE', 'title' => ucfirst($this->translator->trans('DM_SIZE')), 'type' => 'number', 'show_filter' => 0),
                '1' => array('id' => 'CATEGORY', 'title' => ucfirst($this->translator->trans('DM_DOCUMENT_CATEGORY')), 'type' => 'text', 'show_filter' => 0),
                '2' => array('id' => 'DESCRIPTION', 'title' => ucfirst($this->translator->trans('DM_DESCRIPTION')), 'type' => 'text')
            )
        );
        $ispublic = ucfirst($this->translator->trans('DM_PUBLIC_VISIBILITY'));
        if (in_array('frontend1', $bookedModuleDetails)) {
            $fileTypeData['entry']['3'] = array('id' => 'VISIBLE_TO', 'title' => $visibleToTitle, 'type' => 'select', 'input' => $inputArray);
            $fileTypeData['entry']['4'] = array('id' => 'ISPUBLIC', 'title' => $ispublic, 'type' => 'select', 'input' => $publicArray);
        }
        $showDeposited = true;
        if (($type == 'CLUB') && in_array($clubType, array('sub_federation_club', 'federation_club', 'standard_club'))) {
            $showDeposited = false;
        }
        if ($showDeposited) {
            $fileTypeData['entry']['5'] = array('id' => 'DEPOSITED_WITH', 'title' => ucfirst($this->translator->trans('DM_DEPOSITED_WITH')), 'type' => $depositedType, 'input' => $depositedArray);
        }

        return $fileTypeData;
    }
    
    /**
     * Function to get teams/workgroups.
     *
     * @param int    $type            Whether team/workgroup
     * @param int    $catId           Category id
     * @param string $clubDefaultLang Club Default Language
     * @param object $getFilterRoles  Whether to get roles for filter data
     *
     * @return array $roles Roles array.
     */
    private function getTeamsOrWorkgroups($type, $catId, $clubDefaultLang, $getFilterRoles = false)
    {
        $rolesArray = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamsOrWorkgroupsForDocumentsDepositedOption($type, $catId, $clubDefaultLang, true, true, $this->container);
        $roles = array();
        if ($getFilterRoles) {
            if ($type == 'team') {
                $roles = array(
                    '0' => array('id' => '', 'title' => ucfirst($this->translator->trans('DOCUMENT_SELECT_TEAM', array('%team%' => $this->terminologyService->getTerminology('Team', $this->container->getParameter('singular')))))),
                    '1' => array('id' => 'any', 'title' => ucfirst($this->translator->trans('DOCUMENT_ANY_TEAM', array('%team%' => $this->terminologyService->getTerminology('Team', $this->container->getParameter('singular'))))))
                );
            } else if ($type == 'workgroup') {
                $roles = array(
                    '0' => array('id' => '', 'title' => ucfirst($this->translator->trans('DOCUMENT_SELECT_WORKGROUP'))),
                    '1' => array('id' => 'any', 'title' => ucfirst($this->translator->trans('DOCUMENT_ANY_WORKGROUP')))
                );
            }
        }
        foreach ($rolesArray as $roleArray) {
            $roles[] = array('id' => $roleArray['roleId'], 'title' => $roleArray['roleTitle']);
        }

        return $roles;
    }
    
    /**
     * Function to get team functions.
     *
     * @param int    $clubTeamId      Team Category id
     * @param string $clubDefaultLang Default Language
     *
     * @return array $functions Functions array.
     */
    private function getTeamFunctions($clubTeamId, $clubDefaultLang)
    {
        $functionsArray = $this->_em->getRepository('CommonUtilityBundle:FgRmFunction')->getAllTeamFunctionsOfAClub($clubTeamId, $clubDefaultLang, true, true);
        $functions = array();
        foreach ($functionsArray as $functionArray) {
            $functions[] = array('id' => $functionArray['functionId'], 'title' => $functionArray['functionTitle']);
        }

        return $functions;
    }
    
    /**
     * Function to get document column data for file type.
     *
     * @param object $translator translator object
     * @param string $type       Document type club/team/contact/workgroup
     *
     * @return array $fileTypeData  file type data
     */
    public function getDocColumnsForFileType($type)
    {
        if ($type == 'TEAM') {
            $visibleToTitle = ucfirst($this->translator->trans('DC_DOCUMENT_VISIBLE_TO'));
            $depositedType = 'select';
        } else if ($type == 'WORKGROUP') {
            $visibleToTitle = ucfirst($this->translator->trans('DM_VISIBLE_TO_WORKGROUP'));
            $depositedType = 'select';
        }

        $fileTypeData = array(
            'id' => 'FILE',
            'title' => ucfirst($this->translator->trans('DM_FILE')),
            'entry' => array(
                '0' => array('id' => 'SIZE', 'title' => ucfirst($this->translator->trans('DM_SIZE')), 'type' => 'number', 'show_filter' => 0),
                '1' => array('id' => 'CATEGORY', 'title' => ucfirst($this->translator->trans('DM_DOCUMENT_CATEGORY')), 'type' => 'text', 'show_filter' => 0),
                '2' => array('id' => 'DESCRIPTION', 'title' => ucfirst($this->translator->trans('DM_DESCRIPTION')), 'type' => 'text')
            )
        );

        $fileTypeData['entry']['3'] = array('id' => 'VISIBLE_TO', 'title' => $visibleToTitle, 'type' => 'select');
        $fileTypeData['entry']['4'] = array('id' => 'ISPUBLIC', 'title' => ucfirst($this->translator->trans('DM_PUBLIC_VISIBILITY')), 'type' => 'select');
        $fileTypeData['entry']['5'] = array('id' => 'DEPOSITED_WITH', 'title' => ucfirst($this->translator->trans('DM_DEPOSITED_WITH')), 'type' => $depositedType);

        return $fileTypeData;
    }
    
    /**
     * Function to get filter data of 'DATE' type.
     *
     * @param object $translator Translator object.
     *
     * @return array $dateTypeData Result filter data of 'DATE' type.
     */
    public function getFilterDataofDateType()
    {
        $dateTypeData = array(
            'id' => 'DATE',
            'title' => $this->translator->trans('DM_DATE'),
            'show_filter' => '1',
            'fixed_options' => array(
                '0' => array('0' => array('id' => '', 'title' => $this->translator->trans('SELECT_DEFAULT')))
            ),
            'entry' => array(
                '0' => array('id' => 'UPLOADED', 'title' => $this->translator->trans('DM_UPLOADED'), 'type' => 'date'),
                '1' => array('id' => 'LAST_UPDATED', 'title' => $this->translator->trans('DM_UPDATED'), 'type' => 'date')
            )
        );

        return $dateTypeData;
    }
    
    /**
     * Function to get filter data of 'USER' type.
     *
     * @param object $translator Translator object.
     *
     * @return array $userTypeData Result filter data of 'USER' type.
     */
    public function getFilterDataofUserType()
    {
        $userTypeData = array(
            'id' => 'USER',
            'title' => $this->translator->trans('DM_USER'),
            'show_filter' => '1',
            'fixed_options' => array(
                '0' => array('0' => array('id' => '', 'title' => $this->translator->trans('SELECT_DEFAULT')))
            ),
            'entry' => array(
                '0' => array('id' => 'UPLOADED_BY', 'title' => $this->translator->trans('DM_UPLOADED_BY'), 'type' => 'text'),
                '1' => array('id' => 'UPDATED_BY', 'title' => $this->translator->trans('DM_UPDATED_BY'), 'type' => 'text'),
                '2' => array('id' => 'AUTHOR', 'title' => $this->translator->trans('DM_AUTHOR'), 'type' => 'text')
            )
        );

        return $userTypeData;
    }
    
    /**
     * Function to get query for documents search from topnavigation autocomplete
     * 
     * @param objetc $documentlistClass Object of documents-list class
     * @param int    $clubId            current club-id
     * @param array  $clubHeirarchy     club-heirarchy array
     * @param string $searchTerm        search term
     * 
     * @return string
     */
    public function getQueryForDocumentSearch($documentlistClass, $clubId, $clubHeirarchy, $searchTerm)
    {
        $aColumns = array('docname', 'fdd.id as documentId', " fdd.document_type as Type ");
        $documentlistClass->clubId = $clubId;
        $documentlistClass->clubHeirarchy = $clubHeirarchy;
        $documentlistClass->setColumns($aColumns);
        $documentlistClass->setFrom();
        $documentlistClass->setCondition();
        //$documentlistClass->addCondition( "fdd.deposited_with != 'NONE'");
        $documentlistClass->addOrderBy(" docname ASC ");
        $totallistquery = $documentlistClass->getResult();
        $finalQuery = "SELECT documentId as id, CONCAT(`docname`, ' (',LCASE(`Type`),' docs)') as title, `Type` as type  FROM ( $totallistquery ) TAB WHERE `docname` like '$searchTerm%' ";

        return $finalQuery;
    }
    
    /**
     * Function to move uploaded files from temp to document folder
     *
     * @param string $tmpFileName Temporary filename
     * @param int    $clubId      ClubId
     *
     * @return array $fileArr Uploaded files details
     */
    public function moveFileToDocumentsFolder($tmpFileName, $clubId)
    {
        $rootPath = FgUtility::getRootPath($this->container);
        $uploadPath = $rootPath . '/uploads/' . $clubId . '/documents/';
        $tmpFileNameDetails = explode('-', $tmpFileName);
        unset($tmpFileNameDetails[0]);
        $tmpFileNameArr = array_values($tmpFileNameDetails);
        $tmpFileNameNew = implode('-', $tmpFileNameArr);
        $fileSize = filesize($rootPath . '/uploads/temp/' . $tmpFileName);
        $fileName = FgUtility::getFilename($uploadPath, $tmpFileNameNew);
        $fs = new Filesystem();
        $fs->copy($rootPath . '/uploads/temp/' . $tmpFileName, $uploadPath . $fileName);
        unlink($rootPath . '/uploads/temp/' . $tmpFileName);
        $fileArr = array('fileName' => $fileName, 'fileSize' => $fileSize);

        return $fileArr;
    }
    
    /**
     * Function to populate document data log entries
     *
     * @param object $docObj            Document Object
     * @param int    $isNew             Whether insert/edit
     * @param array  $dataArr           Data value to update/create
     * @param int    $isFrontend1Booked Is frontend 1 module booked 0/1
     */
    public function populateDocumentDataLogEntries($docObj, $isNew = 0, $dataArr = array(), $isFrontend1Booked = 0)
    {
        $dataLogArr = array('Document name', 'Category', 'Description', 'Author' , 'isPublic');
        if ((($dataArr['docType'] == 'club') || ($dataArr['docType'] == 'contact')) && ($isFrontend1Booked)) {
            $dataLogArr[] = 'Visible for contacts';
        }
        if ($dataArr['docType'] == 'contact') {
            $dataLogArr[] = 'Filter';
        }
        $kind = 'data';
        foreach ($dataLogArr as $key => $field) {
            switch ($field) {
                case 'Document name':
                    $valueBefore = ($isNew == 1) ? '' : $docObj->getName();
                    $valueAfter = str_replace('<script', '<scri&nbsp;pt', $dataArr['name']);
                    break;
                case 'Category':
                    $oldCategory = ($isNew == 1) ? '' : $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->find($docObj->getCategory()->getId());
                    $oldSubCategory = ($isNew == 1) ? '' : $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->find($docObj->getSubcategory()->getId());
                    $valueBefore = ($isNew == 1) ? '' : ($oldCategory->getTitle() . ' - ' . $oldSubCategory->getTitle());
                    $category = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->find($dataArr['categoryId']);
                    $subCategory = $this->_em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->find($dataArr['subCategoryId']);
                    $valueAfter = ($category->getTitle() . ' - ' . $subCategory->getTitle());
                    break;
                case 'Description':
                    $valueBefore = ($isNew == 1) ? '' : $docObj->getDescription();
                    $valueAfter = str_replace('<script', '<scri&nbsp;pt', $dataArr['description']);
                    break;
                case 'Author':
                    $valueBefore = ($isNew == 1) ? '' : $docObj->getAuthor();
                    $valueAfter = str_replace('<script', '<scri&nbsp;pt', $dataArr['author']);
                    break;
                case 'Visible for contacts':
                    $kind = 'visible_for_contact';
                    $valueBefore = ($isNew == 1) ? '' : $docObj->getIsVisibleToContact();
                    $valueAfter = $dataArr['isVisible'];
                    break;
                case 'isPublic':
                    $kind = 'ispublic';
                    $valuePublish =  $this->translator->trans('OFF') ;
                    if($docObj->getIsPublishLink()==1){
                       $valuePublish =  $this->translator->trans('ON'); 
                    }
                    $valueAfterPublish  = $this->translator->trans('OFF') ;
                    if($dataArr['isPublic'] ==1){
                       $valueAfterPublish =  $this->translator->trans('ON'); 
                    }
                    $valueBefore = ($isNew == 1) ? '' : $valuePublish;
                    $valueAfter = $valueAfterPublish;
                    break;
                case 'Filter':
                    $kind = 'filter';
                    $valueBefore = ($isNew == 1) ? '' : $docObj->getFilter();
                    $valueAfter = $dataArr['filterData'];
                    break;
            }
            $valueBefore = ($kind != 'filter') ? str_replace("'", '', $valueBefore) : $valueBefore;
            $valueAfter = ($kind != 'filter') ? str_replace("'", '', $valueAfter) : $valueAfter;
            if ($valueBefore != $valueAfter) {
                $this->documentLogArr[] = array($kind, $field, $valueBefore, $valueAfter, "", "");
            }
        }
    }
    /**
     * Function to set variable documentLogArr
     * @param array $documentLogArr Array to set as documentLogArr
     */
    public function setDocumentLogArr($documentLogArr = array()){
        $this->documentLogArr = $documentLogArr;
    }
    /**
     * Function to insert deposited with and visible for log entries
     *
     * @param string $option         Visible for/deposited with
     * @param string $valueBefore    Value Before
     * @param string $valueAfter     Value After
     * @param string $valueBeforeIds Value Before Ids
     * @param string $valueAfterIds  Value After Ids
     */
    public function populateDocumentAssignmentLogEntries($option = '', $valueBefore = '', $valueAfter = '', $valueBeforeIds = '', $valueAfterIds = '')
    {
        $field = ($option == 'visible_for') ? 'Visible for contacts' : 'Deposited with';
        if (($valueBefore != $valueAfter) || ($valueBeforeIds != $valueAfterIds)) {
            $this->documentLogArr[] = array($option, $field, $valueBefore, $valueAfter, $valueBeforeIds, $valueAfterIds);
        }
    }
    
    /**
     * Function to populate contact include and exclude log entries
     *
     * @param int   $documentId          Document Id
     * @param int   $isNew               Whether Create/edit
     * @param array $includedContactsArr Included contactids
     * @param array $excludedContactsArr Excluded contactids
     */
    public function populateContactIncludeAndExcludeLogEntries($documentId, $isNew = 1, $includedContactsArr = array(), $excludedContactsArr = array())
    {
        //insert included contacts log entry
        $includedContactsValidArr = array_diff($includedContactsArr, $excludedContactsArr);
        $includedContactsFiltered = array_filter($includedContactsValidArr);
        $includedContacts = array_values($includedContactsFiltered);
        $lastLogEntry = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getLastLogEntry($documentId, 'included');
        $valueBeforeIds = ($isNew == 1) ? '' : ((count($lastLogEntry) > 0) ? $lastLogEntry['valueAfterId'] : '');
        $valueAfterIds = implode(',', $includedContacts);
        if ($valueBeforeIds != $valueAfterIds) {
            $this->documentLogArr[] = array('included', 'Included contacts', '', '', $valueBeforeIds, $valueAfterIds);
        }
        //insert excluded contacts log entry
        $lastLogEntryExcluded = $this->_em->getRepository('CommonUtilityBundle:FgDmAssigment')->getLastLogEntry($documentId, 'excluded');
        $valueBeforeIdsExcluded = ($isNew == 1) ? '' : ((count($lastLogEntryExcluded) > 0) ? $lastLogEntryExcluded['valueAfterId'] : '');
        $excludedContactsFiltered = array_filter($excludedContactsArr);
        $excludedContacts = array_values($excludedContactsFiltered);
        $valueAfterIdsExcluded = implode(',', $excludedContacts);
        if ($valueBeforeIdsExcluded != $valueAfterIdsExcluded) {
            $this->documentLogArr[] = array('excluded', 'Excluded contacts', '', '', $valueBeforeIdsExcluded, $valueAfterIdsExcluded);
        }
    }
    
    /**
     * Function to insert document log entries
     *
     * @param string $documentType DocumentType
     * @param int    $documentId   DocumentId
     * @param int    $contactId    ContactId
     */
    public function insertDocumentLogEntries($documentType, $documentId, $contactId)
    {
        if (count($this->documentLogArr) > 0) {
            $docLogData = array();
            //LOOPING VALUES
            foreach ($this->documentLogArr as $val) {
                if (count($this->documentLogArr)) {
                    $docLogData[] = array('document_type' => $documentType, 'documents_id' => $documentId, 'kind' => $val[0], 'field' => $val[1], 'value_after' => $val[3], 'value_before' => $val[2], 'value_before_id' => $val[4], 'value_after_id' => $val[5]);
                }
            }
            if(count($docLogData) > 0) {
                $logHandle = new FgLogHandler($this->container);
                $logHandle->processLogEntryAction('contactOverview_document', 'fg_dm_document_log', $docLogData);
            }
        }
    }
    
    /**
     * Function to get my accessible teams and workgroups in document scenario
     * The function to used to get the teams/workgroups that the user can upload,edit or view documents
     * ie Superadmin/Clubadmin/Team Document Admin/Team Admin
     *
     * @param object  $contact            Contact object
     * @param string  $documentType       Team or workgroup
     * @param boolean $includeMembersFlag Whether to include member teams or member workgroups too
     *
     * @return array $myDocumentRoles My teams/workgroups
     */
    public function getMyDocumentRoles($documentType = 'team', $includeMembersFlag = false)
    {
        $contact = $this->container->get('contact');
        $myDocumentRoles = array();
        $isClubAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        //get all teams or workgroups of which the logged in user is a admin
        $groupRights = $contact->get('clubRoleRightsGroupWise');
        if ($documentType == 'team') {
            $allTeams = $contact->get('teams');
            //for clubadmin, superadmin always deposited with all teams will be there.
            if (!$isClubAdmin) {
                $myTeams = (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) ? $groupRights['ROLE_GROUP_ADMIN']['teams'] : array();
                if (isset($groupRights['ROLE_DOCUMENT_ADMIN']['teams'])) {
                    $myTeams = array_merge($myTeams, $groupRights['ROLE_DOCUMENT_ADMIN']['teams']);
                }
                if (isset($groupRights['MEMBER']['teams']) && $includeMembersFlag) {
                    $myTeams = array_merge($myTeams, $groupRights['MEMBER']['teams']);
                }
                foreach ($myTeams as $val) {
                    $myDocumentRoles[$val] = $allTeams[$val];
                }
            } else {
                $myDocumentRoles = $allTeams;
            }
        } elseif ($documentType == 'workgroup') {
            $allWorkgroups = $contact->get('workgroups');
            //for clubadmin, superadmin always deposited with all workgroups will be there.
            if (!$isClubAdmin) {
                $myWorkgroups = (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) ? $groupRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
                if (isset($groupRights['ROLE_DOCUMENT_ADMIN']['workgroups'])) {
                    $myWorkgroups = array_merge($myWorkgroups, $groupRights['ROLE_DOCUMENT_ADMIN']['workgroups']);
                }
                if (isset($groupRights['MEMBER']['workgroups']) && $includeMembersFlag) {
                    $myWorkgroups = array_merge($myWorkgroups, $groupRights['MEMBER']['workgroups']);
                }
                foreach ($myWorkgroups as $val) {
                    $myDocumentRoles[$val] = $allWorkgroups[$val];
                }
            } else {
                $myDocumentRoles = $allWorkgroups;
            }
        }

        return $myDocumentRoles;
    }
    
    /**
     * Function to create upload folder for documents
     *
     * @param int $clubId ClubId
     */
    public function createDocumentsUploadFolder($clubId)
    {
        if (!is_dir('uploads/' . $clubId)) {
            mkdir('uploads/' . $clubId, 0700);
        }
        if (!is_dir('uploads/' . $clubId . '/documents')) {
            mkdir('uploads/' . $clubId . '/documents', 0700);
        }
    }
}