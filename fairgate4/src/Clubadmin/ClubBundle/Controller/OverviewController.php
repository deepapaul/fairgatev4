<?php

namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Clubadmin\Classes\Clublist;
use Clubadmin\Classes\Clubtablesetting;
use Symfony\Component\Intl\Intl;
use Clubadmin\ClubBundle\Util\NextpreviousClub;
use Common\UtilityBundle\Util\FgUtility;
use Admin\UtilityBundle\Repository\Pdo\ClubPdo;


/**
 * Club oview Controller
 *
 * @package    ClubadminClubBundle
 * @subpackage Controller
 * @author     neethu.mg
 * @version    Fairgate V4
 */
class OverviewController extends FgController
{

    /**
     * Pre execute function to allow access to federation and subfed clubs only
     * @return Exception
     */
    public function preExecute()
    {
        parent::preExecute();

        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation') {
            throw $this->createNotFoundException($this->get('translator')->trans('%CLUBNAME%_HAVE_NO_ACCESS_TO_PAGE', array('%CLUBNAME%' => $this->clubTitle)));
        }

    }

   /**
    * Function is used to display club overview
     *
    * @param int $offset
    * @param type $clubId
    * @return template
    * @throws not found exception
    */
    public function indexAction($offset,$clubId)
    {
        $club = $this->get('club');
        //check if clubId has access
        $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
        $sublevelclubs = $clubPdo->getAllSubLevelData($this->clubId);
        $sublevelclub = array();
        $contCountDetails = array();
        foreach($sublevelclubs as $key => $value){
            if($clubId == $value['id'])
            {
                $sublevelclub[$key] = $value['id'];
                $sublevelclub['is_sub_federation'] = $value['is_sub_federation'];
            }
        }
        //security check        
        $permissionObj = $this->fgpermission;
        $accessCheck = (!in_array($clubId, $sublevelclub)) ? 0 : 1;
        $permissionObj->checkClubAccess($accessCheck,"backend_club");

        //end check if clubid has access
        $assignmentCount = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubClassAssignment')->assignmentCount($this->clubType, $this->conn, $clubId);
        
        $breadCrumb = array('back' => '#');
        //$limit=3 as per requirement
        $getAllNotes = json_encode($this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesDetails(0, $limit=3, $clubId, $this->clubId));
       $clubName = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->getClubname($clubId, $club->get('default_lang'));
        $CfCiCoFields = $this->overviewField($clubId);
        //$activeContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->activeContactsCount($clubId,$sublevelclub['is_sub_federation']) ;
        $activeContact = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($clubId);
        //$groupId=2 clubadministrator
        $clubAdministratorsCount = $this->em->getRepository('CommonUtilityBundle:sfGuardGroup')->clubAdministratorsCount($clubId,$groupId=2);
        $clubArray = array('clubId' => $this->clubId,'federationId' => $this->federationId,'clubType' => $this->clubType,'defaultClubLang'=>$this->clubDefaultLang);
        $clubObj = new ClubPdo($this->container);
	    $assignment = json_encode($clubObj->getAllAssignedAssignments($clubArray, $this->conn, $clubId,$sortOrder='CL'));        
        
        $nextprevious = new NextpreviousClub($this->container);
        $nextPreviousResultset = $nextprevious->nextPreviousClubData($this->contactId,$clubId, $offset, 'club_overview', 'offset', 'clubId', $flag = 0);

        $terminologyService = $this->get('fairgate_terminology_service');
        $terminologyTerms = json_encode(array ('club' =>ucfirst($terminologyService->getTerminology('Club', $this->container->getParameter('singular'))),
                                                'executive_board' =>ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular'))),
                                               'subfederation' =>ucfirst($terminologyService->getTerminology('Sub-federation', $this->container->getParameter('singular'))),
                                               'federation' =>  ucfirst($terminologyService->getTerminology('Federation', $this->container->getParameter('singular'))) ));

        // Get Club Executive Board Functions and its Members
        $parentClubId = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($clubId)->getParentClubId();
        $clubHeirarchyArray = array($parentClubId, $this->clubId);
        if ($this->federationId != 0) {
            $clubHeirarchyArray[] = $this->federationId;
        }
        $clubHeirarchyArray = array_unique($clubHeirarchyArray);
        $notesCount = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesCount($clubId, $this->clubId);
        $clubExecBoardData = $this->em->getRepository('CommonUtilityBundle:FgRmFunction')->getExecBoardFunctionDetailsOfClub($clubId, $this->clubDefaultLang, $clubHeirarchyArray, true);
        $documentsCount = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getCountOfAssignedClubDocuments('CLUB', $this->clubId, $clubId, $this->container);
        
        if(in_array('document', $this->get('club')->get('bookedModulesDet'))){
            $tabs = array(0 =>"overview", 1 =>"data", 2=>"assignment", 3=> "note", 4=>"document", 5=>"log"); 
        }else {
            $tabs = array(0 =>"overview", 1 =>"data", 2=>"assignment", 3=> "note", 4=>"log");
        }
   
        $contCountDetails['asgmntsCount'] = $assignmentCount;
        $contCountDetails['documentsCount'] = $documentsCount;
        $contCountDetails['notesCount'] = $notesCount;
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $clubId, $contCountDetails, "overview","club");
        
        return $this->render('ClubadminClubBundle:Overview:clubOverview.html.twig', array('breadCrumb' => $breadCrumb,'clubId' =>$clubId,'clubName' => $clubName[0]['title'],'offset' => $offset,'getAllNotes'=>$getAllNotes,'overviewContent' => $CfCiCoFields,
                                                                                          'terminologyTerms' => $terminologyTerms,'clubAdmin'=>$clubAdministratorsCount[0]['count'],'nextPreviousResultset'=>$nextPreviousResultset,
                                                                                          'assignment' => $assignment,'clubType' => $this->clubType,'overviewClubType' => $sublevelclub['is_sub_federation'], 'activeContact' => $activeContact->getActiveContactCount(),'documentsCount' => $documentsCount ,'clubExecBoardData' => json_encode($clubExecBoardData),'asgmntsCount'=>$assignmentCount,'notesCount'=>$notesCount, 'tabs'=> $tabsData));
    }

    /**
     *
     * @param  int    $clubId
     *
     * @return Array $clublistDatas
     *
     */
    public function overviewField($clubId){

        $jsonData = $this->arrayStructure();
        $club = $this->get('club');
        $tabledatas = json_decode($jsonData, true);
        $table = new Clubtablesetting($this->container, $tabledatas, $club);
        $aColumns = $table->getClubColumns();
        $clublistClass = new Clublist($this->container, $club);
        $clublistClass->setCount();
        $clublistClass->addtColumns(' club_number as CF_number');
        $clublistClass->setFrom();
        $clublistClass->setCondition();
        $sWhere = " fc.id ='$clubId'";
        $clublistClass->addCondition($sWhere);
        $clublistClass->setColumns($aColumns);
        $listquery = $clublistClass->getResult(); 
        $clublistDatas = $this->container->get('fg.admin.connection')->executeQuery($listquery);
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $clublistDatas[0]['CF_C_country'] = ($clublistDatas[0]['CF_C_country'] != '') ? $countryList[$clublistDatas[0]['CF_C_country']]:'';
        $clublistDatas[0]['CF_I_country'] = ($clublistDatas[0]['CF_I_country'] != '') ? $countryList[$clublistDatas[0]['CF_I_country']]:'';
        $clublistDatas[0]['CF_language'] = ($clublistDatas[0]['CF_language'] != '') ? $languages[$clublistDatas[0]['CF_language']]:'';
        $clublistDatas[0]['CF_created_at'] = $this->get('club')->formatDate($clublistDatas[0]['CF_created_at'],'date','d.m.Y H:i');  
        //FAIR-2489
        $checkClubHasDomain = $this->em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
        $clublistDatas[0]['CF_domain_name'] = ($checkClubHasDomain) ? $checkClubHasDomain['domain'] : 0;
        $clublistDatas[0]['CF_base_url'] = $this->container->getParameter('base_url');
        $clubPdo = new \Common\UtilityBundle\Repository\Pdo\ClubPdo($this->container);  
        // Own fed membership count of a club
        $clublistDatas[0]['SIOWN_FED_MEMBERS'] = $clubPdo->getOwnFedMemberCount($club->get('id'));        
        $clublistDatas = json_encode($clublistDatas);
        return $clublistDatas;

    }//end overviewfield()

    public function arrayStructure(){
        //array structure for building from clubtablesettings
        $fieldStructure = array( "1" => array('id' =>'C_co','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_co'),
                                        array('id' =>'C_street','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_street'),
                                        array('id' =>'C_pobox','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_pobox'),
                                        array('id' =>'C_city','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_city'),
                                        array('id' =>'C_zipcode','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_zipcode'),
                                        array('id' =>'C_state','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_state'),
                                        array('id' =>'C_country','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_C_country'),
                                        array('id' =>'I_co','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_co'),
                                        array('id' =>'I_street','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_street'),
                                        array('id' =>'I_pobox','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_pobox'),
                                        array('id' =>'I_city','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_city'),
                                        array('id' =>'I_zipcode','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_zipcode'),
                                        array('id' =>'I_state','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_state'),
                                        array('id' =>'I_country','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_I_country'),
                                        array('id' =>'website','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_website'),
                                        array('id' =>'email','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_email'),
                                        array('id' =>'created_at','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_created_at'),
                                        array('id' =>'language','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_language'),
                                        array('id' =>'url_identifier','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_url_identifier'),
                                        array('id' =>'LAST_CONTACT_EDIT','type' =>'SI','club_id' => $this->clubId,'name'=>'SILAST_CONTACT_EDIT'),
                                        array('id' =>'LAST_ADMIN_LOGIN','type' =>'SI','club_id' => $this->clubId,'name'=>'SILAST_ADMIN_LOGIN'),
                                        array('id' =>'FED_MEMBERS','type' =>'SI','club_id' => $this->clubId,'name'=>'SIFED_MEMBERS'),
                                        array('id' =>'OWN_FED_MEMBERS','type' =>'SI','club_id' => $this->clubId,'name'=>'SIOWN_FED_MEMBERS'),
                                        array('id' =>'CLUB_ID','type' =>'SI','club_id' => $this->clubId,'name'=>'SICLUB_ID'),
                                        array('id' =>'establish','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_establish'),
                                        array('id' =>'number','type' =>'CF','club_id' => $this->clubId,'name'=>'CF_number'),
                                        array('id' =>'subfed','type' =>'CO','club_id' => $this->clubId,'name'=>'COsubfed'));
        $jsonData = json_encode($fieldStructure);
        return $jsonData;
    }//end arrayStructure()

}