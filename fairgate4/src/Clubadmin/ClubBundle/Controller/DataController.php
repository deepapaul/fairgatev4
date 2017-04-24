<?php

/**
 * This controller was created for handling contact listing functionalities
 */
namespace Clubadmin\ClubBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Clubadmin\ClubBundle\Util\NextpreviousClub;
use Common\UtilityBundle\Util\FgUtility;
use Common\FilemanagerBundle\Util\FileChecking;
use Symfony\Component\HttpFoundation\Request;
use Admin\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * DataController used for managing club details
 *
 */
class DataController extends FgController
{

    /**
     * Function club data edit action
     * Action for both club data settings and club data edit
     * @param type $offset
     * @param type $clubid
     *
     * @Template("ClubadminClubBundle:Data:index.html.twig")
     */
    public function clubDataEditAction(Request $request, $offset, $clubid)
    {
        if ($clubid != $this->clubId) {
            $pageType = "ClubDataEdit";
        } else {
            $pageType = "ClubDataSettings";
        }
        //Checking !federation level && !sub-federation level && (if sub-federation not editting other club)
        if ($this->clubType != 'federation' && $this->clubType != 'sub_federation' && $pageType == "ClubDataEdit") {
            $permissionObj = $this->fgpermission;
            $permissionObj->checkClubAccess(0, "backend_club");
        }
        $clubPdo = new ClubPdo($this->container);
        //Checking (if sub-federation not editting other club)
        if ($pageType == "ClubDataEdit") {
            //check if clubid has access            
            $sublevelclubs = $clubPdo->getAllSubLevelData($this->clubId);
            $sublevelclub = array();
            foreach ($sublevelclubs as $key => $value) {
                $sublevelclub[$key] = $value['id'];
            }
            //security check
            $permissionObj = $this->fgpermission;
            $accessCheck = (!in_array($clubid, $sublevelclub)) ? 0 : 1;
            $permissionObj->checkClubAccess($accessCheck, "backend_club");
        }

        $formValues = array();
        $editData = $clubPdo->getClubData($clubid);        
        $contCountDetails = array();
        $editData['fedIcon_Visibility'] = (($this->clubType == 'federation' || $this->clubType == 'sub_federation') && ( $pageType == "ClubDataSettings")) ? 1 : 0;
        $clubSettingsData = $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->getClubSettingsDetail($clubid);
        
        if ($request->getMethod() == 'POST') {
            $formValues = $request->request->get('fg_club_category');
            $formValues['same_invoice_address'] = $request->get('same_invoice_address');
        }
        $fieldTitles = $this->getFieldTitle();
        $options['container'] = $this->container->getParameterBag();
        $options['title'] = $fieldTitles['title'];
        $options['logo'] = $editData['title'];
        
        $club = $this->container->get('club');
        $editData['signature'] = $clubSettingsData['clubsignature'];

        $options['logo_trans'] = $this->get('translator')->trans('CL_LOGO');
        $options['icon_trans'] = $this->get('translator')->trans('FED_LOGO_ICON');
        $domainCacheKey = $club->get('clubCacheKey'); 

        $form1 = $this->createForm(\Common\UtilityBundle\Form\FgClubDataCategory::class, null, array('custom_value' => array('submittedData' => $formValues, 'editData' => $editData, 'containerParameters' => $options, 'pageType' => $pageType, 'club' => $club)));                 
        $result = array('breadcrumb' => $breadcrumb, 'clubName' => $editData['title'], 'clubid' => $clubid, 'offset' => $offset, 'clublogo' => $club->get('logo'), 'fedicon' => $clubSettingsData['fedicon']);
       
        if ($pageType == "ClubDataEdit") {
            $breadcrumb = array('back' => '#');
            $nextprevious = new NextpreviousClub($this->container);
            $result['nextPreviousResultset'] = $nextprevious->nextPreviousClubData($this->contactId, $clubid, $offset, 'club_data', 'offset', 'clubid', $flag = 0);
        } else { //For settings page
            $breadcrumb = "";
        }
        if ($request->getMethod() == 'POST') {
            $form1->handleRequest($request);
            if ($form1->isSubmitted()) {
                if ($form1->isValid()) {
                    $formValues['same_invoice_address'] = $request->get('same_invoice_address');

                    $clubDefaultLanguage = $club->get('club_default_lang');
                    $editData['signature'] = $formValues['Notification']["signature_$clubDefaultLanguage"];
                    
                    $logoValues = $this->clubLogoUpload($request->get('club_uploaded_logo'), $clubid, $editData['i18n']['logo'],110, 70 ); 
                    $editData['logo'] = $logoValues[$clubDefaultLanguage];
                    
                    $feduploadedLogo = $request->get('fed_uploaded_logo');
                    if ($feduploadedLogo != '') {
                        //when the image is submitted with no change the image will not be there in the temp folder
                        //beacuse it will already been moved and resized. So we dont need to do any file operations,
                        //but the data need to be sent to the model, so to distinguish the "no change & no file" events
                        $this ->logoUpload($feduploadedLogo, $clubid,'federation', 22, 16, $editData );
                        
                    } else {
                        $editData['fed_icon'] = '';
                        $existingfileName = $clubSettingsData['fedicon'];
                        
                        $editData['is_FediconChanged'] = 1;
                        if ($existingfileName != '') {
                            $fedDirectory = 'uploads/' . $clubid . '/admin/federation_icon';
                            unlink($fedDirectory . '/' . $existingfileName);
                        }
                    }
                    
                    //Set the dummy data from the post data to the $formValues to mimick the old club data save implementtation
                    $formValues['system']['title'] = $formValues['system']["title_$clubDefaultLanguage"];
                    $formValues['Notification']['signature'] = $formValues['Notification']["signature_$clubDefaultLanguage"];
                    /********************************************************************
                    * FAIRDEV-336- Restrict club's changing of currency in club settings. 
                    ********************************************************************/
                    if($editData['sp_country']){
                        $formValues['Correspondence']['sp_country'] = $editData['sp_country'];
                    }
                    $clubPdo->saveClubData($formValues, $editData, $this->contactId, $fieldTitles, $domainCacheKey, $this->container);

                    //save club i18n values
                    $clubPdo->saveClubi18nData($formValues, $logoValues,$clubid,$club->get('club_languages'), $domainCacheKey);
                    
                    return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true,'flash' => $this->get('translator')->trans('CL_DATA_UPDATED')));
                } else {
                    $result['isError'] = true;
                }
            }
        }

        $result['editData'] = $editData;
        $result['form'] = $form1->createView();
        $result['documentsCount'] = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getCountOfAssignedClubDocuments('CLUB', $this->clubId, $clubid, $this->container);
        $assignmentCount = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClubClassAssignment')->assignmentCount($this->clubType, $this->conn, $clubid);
        $result['asgmntsCount'] = $assignmentCount;
        $result['notesCount'] = $this->em->getRepository('CommonUtilityBundle:FgClubNotes')->getNotesCount($clubid, $this->clubId);
        if (in_array('document', $club->get('bookedModulesDet'))) {
            $tabs = array(0 => "overview", 1 => "data", 2 => "assignment", 3 => "note", 4 => "document", 5 => "log");
        } else {
            $tabs = array(0 => "overview", 1 => "data", 2 => "assignment", 3 => "note", 4 => "log");
        }
        $contCountDetails['asgmntsCount'] = $assignmentCount;
        $contCountDetails['documentsCount'] = $result['documentsCount'];
        $contCountDetails['notesCount'] = $result['notesCount'];
        
        $result['clubLanguages'] = $club->get('club_languages');
        $result['clubDefaultLang'] = $club->get('club_default_lang');
        $result['pageTitle'] = $editData['title'];
        
        $result['tabs'] = FgUtility::getTabsArrayDetails($this->container, $tabs, $offset, $clubid, $contCountDetails, "data", "club");
        if ($pageType == "ClubDataSettings") { //For settings page
            $result['settings'] = true;
        }
        
        return $result;
    }

    /**
     * Function getFieldTitle
     *
     * @return type
     */
    private function getFieldTitle()
    {
        $terminologyService = $this->get('fairgate_terminology_service');
        $containerParameters = $this->container->getParameterBag();
        $fieldArray = array();
        $corr = $this->get('translator')->trans('CL_CORRESPONDENCE');
        $inv = $this->get('translator')->trans('CL_INVOICE');
        $fieldArray['sp_co'] = $this->get('translator')->trans('CL_CO') . " ($corr)";
        $fieldArray['sp_street'] = $this->get('translator')->trans('CL_STREET') . " ($corr)";
        $fieldArray['sp_pobox'] = $this->get('translator')->trans('CL_POST_BOX') . " ($corr)";
        $fieldArray['sp_zipcode'] = $this->get('translator')->trans('CL_ZIPCODE') . " ($corr)";
        $fieldArray['sp_city'] = $this->get('translator')->trans('CL_LOCATION') . " ($corr)";
        $fieldArray['sp_state'] = $this->get('translator')->trans('CL_STATE') . " ($corr)";
        $fieldArray['sp_country'] = $this->get('translator')->trans('CL_COUNTRY') . " ($corr)";
        $fieldArray['in_co'] = $this->get('translator')->trans('CL_CO') . " ($inv)";
        $fieldArray['in_street'] = $this->get('translator')->trans('CL_STREET') . " ($inv)";
        $fieldArray['in_pobox'] = $this->get('translator')->trans('CL_POST_BOX') . " ($inv)";
        $fieldArray['in_zipcode'] = $this->get('translator')->trans('CL_ZIPCODE') . " ($inv)";
        $fieldArray['in_city'] = $this->get('translator')->trans('CL_LOCATION') . " ($inv)";
        $fieldArray['in_state'] = $this->get('translator')->trans('CL_STATE') . " ($inv)";
        $fieldArray['in_country'] = $this->get('translator')->trans('CL_COUNTRY') . " ($inv)";
        $fieldArray['title'] = $this->get('translator')->trans('CL_CLUB_NAME', array('%club%' => $terminologyService->getTerminology('Club', $containerParameters->get('singular'))));
        $fieldArray['year'] = $this->get('translator')->trans('CL_YEAR');
        $fieldArray['website'] = $this->get('translator')->trans('CL_WEBSITE');
        $fieldArray['sp_lang'] = $this->get('translator')->trans('CL_CORRESPONDENCE_LANG');
        $fieldArray['email'] = $this->get('translator')->trans('CL_EMAIL');
        $fieldArray['club_number'] = $this->get('translator')->trans('CL_NUMBER', array('%club%' => $terminologyService->getTerminology('Club', $containerParameters->get('singular'))));

        return $fieldArray;
    }
    /**
     * To upload club/federation logo
     * @param String $logoName     logo name
     * @param Int    $clubid       club id
     * @param String $type         club type
     * @param Array  $data         club detail array
     * @param Int    $resizeWidth  resizing width
     * @param Int    $resizeHeight resizing height
     * @param Object $editData     edited data
     */
    private function logoUpload($logoName, $clubid, $type, $resizeWidth, $resizeHeight,&$editData )
    {     
        $club = $this->container->get('club');
        $uploadDirectory = FgUtility::getUploadDir();
         $uploadedDirectory = $uploadDirectory."/temp/";
        if($type=='federation') {
            $editData['fed_icon'] =  $logoName; 
        } else {
            $editData['logo'] = $logoName;  
        }
        if (file_exists($uploadedDirectory . $logoName)) {
            $logoNameDetails = explode('.', $logoName);
            //Move the file from temp to club folder
            $directory = ($type=='federation') ? $uploadDirectory.'/' . $clubid . '/admin/federation_icon' : $uploadDirectory.'/' . $clubid . '/admin/clublogo';
            if (!is_dir($uploadDirectory.'/' . $clubid)) {
                mkdir($uploadDirectory.'/' . $clubid, 0777, true);
            }
            if (!is_dir($uploadDirectory.'/' . $clubid . '/admin')) {
                mkdir($uploadDirectory.'/' . $clubid . '/admin', 0700);
            }
            if (!is_dir($directory)) {
                mkdir($directory, 0700);
            }
            $existingfileName = $club->get('federation_icon');
            if ($existingfileName != '') {
                unlink($directory . '/' . $existingfileName);
            }
            $fileCheck = new FileChecking($this->container);
            $uploadedLogo = $fileCheck->replaceSingleQuotes($logoName);
            if($type=='federation') {
              $editData['fed_icon'] =  $uploadedLogo;
              $editData['is_FediconChanged'] = 1;
            } else {
              $editData['logo'] = $uploadedLogo;  
            }
            //Resize the logo
            $fgUtility = new FgUtility();
            $fgUtility->resizeClubLogo($uploadedDirectory . $logoName, $directory . '/' . $uploadedLogo, $resizeWidth, $resizeHeight);
                            
        } 
    }
   
    
    /**
     * The function to upload the club logo to the database and move thef files accordingly
     * 
     * @param type $logoNameArray       The name of the logo for each language
     * @param type $clubId              The id of the club
     * @param type $existingLogoData    The array with the existing logo data
     * @param type $resizeWidth         The width to which the image needs to be resized
     * @param type $resizeHeight        The height to which the image needs to be resized
     */
    private function clubLogoUpload($logoNameArray, $clubId, $existingLogoData, $resizeWidth, $resizeHeight )
    {       
        $logoUploadDirectory = FgUtility::getUploadDir() . '/'. $clubId . '/admin/clublogo/';
        $tempUploadedDirectory = FgUtility::getUploadDir()."/temp/";
        
        $logoResultArray = array();
        foreach($logoNameArray as $language => $logo){
            if ($logo != '' && file_exists($tempUploadedDirectory . $logo)) {
                //means it is a new file
                 
                // need to create the destination folder
                if (!is_dir($logoUploadDirectory)) {
                    mkdir($logoUploadDirectory, 0777, true);
                }
            
                //need to cut the timestamp string & check if the name exists in the folder, and create the successive name
                if (strpos($logo, '_') === false) {
                    $logoUniqueName = FgUtility::getFilename($logoUploadDirectory, $logo);
                } else {
                    $logoUniqueName = FgUtility::getFilename($logoUploadDirectory, substr($logo, (strpos($logo, '_')+1)));
                }
                
                //move & resize the new file
                $fgUtility = new FgUtility();
                $fgUtility->resizeClubLogo($tempUploadedDirectory . $logo, $logoUploadDirectory . $logoUniqueName, $resizeWidth, $resizeHeight);
                
                //delete the old file
                if($existingLogoData[$language] != ''){
                    unlink($logoUploadDirectory . $existingLogoData[$language]);
                }
                
                $logoResultArray[$language] = $logoUniqueName;
            } else {
                //for old files
                $logoResultArray[$language] = $logo;
            }
        }
        
        return $logoResultArray;
    }
    
}
