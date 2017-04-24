<?php

/**
 * CalendarPdo
 *
 * This class is used for handling calendar section.
 *
 * @package    CommonUtilityBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */

namespace Common\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgSettings;
use Internal\CalendarBundle\Util\CalendarRecurrence;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\File\File;
use Common\FilemanagerBundle\Util\FileChecking;
/**
 * Description of MessagePdo
 *
 * @author pitsolutions.ch
 */
class CalendarPdo {

    /**
     * Connection variable
     */
    public $conn;

    /**
     * Container variable
     */
    public $container;
    /**
     * @var array form data to save
     */
    private $eventFormData=array();
    /**
     * @var array fg_em_calendar table values to insert
     */
    private $calValue = array();
    /**
     * @var array fg_em_calendar table query dulpicate values array
     */
    private $calValueDup = array();
    /**
     * @var array fg_em_calendar_details table values query
     */
    private $calDetailValue = array();
    /**
     * @var array fg_em_calendar_details table values array to bind query
     */
    private $calDetailSet = array();
    /**
     * @var array fg_em_calendar_details table values duplicate query
     */
    private $calDetailValueDup = array();
    /**
     * @var array fg_em_calendar_selected_areas table values to insert
     */
    private $calAreasValues=array();
    /**
     * @var array fg_em_calendar_selected_category table values to insert
     */
    private $calCategoryValues=array();
    /**
     * @var int fg_em_calendar_details_attachments table values to insert
     */
    private $caledarUploadValues=array();
    /**
     * @var array fg_em_calendar_details_i18n table values to insert
     */
    private $calDetailI18Value=array();
    /**
     * @var array fg_em_calendar_rules table values to insert
     */
    private $calRulesValues=array();
    /**
     * @var array fg_em_calendar_rules table values duplicate query
     */
    private $calRulesValueDup=array();
    /**
     * @var array fg_em_calendar_rules table values array to bind query
     */
    private $calRulesValueSet=array();
    /**
     * @var int fg_em_calendar_rules id
     */
    private $caledarRulesId=false;
    /**
     * @var int fg_em_calendar_details id
     */
    private $caledarDetailId =false;
    /**
     * @var int fg_em_calendar id
     */
    private $caledarId =false;
    /**
     * @var bool
     */
    private $isNonRelated=false;
    /**
     *
     * @var array following calendar detail id of repeated event
     */
    private $followingCalIds=array();
    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container) {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to save appointments
     *
     * @param type $requestData
     */
    public function saveAppointment($requestData){
        $this->eventFormData=$requestData;
        $clubDefLang=$this->container->get('club')->get('club_default_lang');
        $this->setCalendarDetailId();
        $this->setCalendarDefaultValues();
        $this->removeSpecialChars();
        foreach ($this->eventFormData as $key=>$eventData){
            switch($key){
                case 'title_lang': case 'desc_lang':
                    $this->setCalendarDetailI18nQuery($key,$eventData);
                    $fieldName=($key=='title_lang')? 'title':'description';
                    if(!empty($eventData[$clubDefLang])){
                        $this->setCalendarDetailQuery($fieldName, $eventData[$clubDefLang]);
                    }
                    
                    break;
                case 'start_date':
                    $dateFormat = $this->container->get('club')->get('phpdatetime');
                    if ($this->eventFormData['is_allday'] == '1') {
                        $defTime = $this->container->get('club')->formatDate('00:00:00', 'time', 'H:i:s');
                        $date = date_create_from_format($dateFormat, $eventData['date'] . ' ' . $defTime);
                    } else {
                        $date = date_create_from_format($dateFormat, $eventData['date']);
                    }
                    $startDate = date_format($date, 'Y-m-d H:i:s');
                    $this->setCalendarDetailQuery($key, $startDate);
                    if(!isset($this->eventFormData['end_date']) && !isset($this->eventFormData['calendar_detail_id'])){
                        if($this->eventFormData['is_allday']=='1'){
                            $endDate = date('Y-m-d H:i:s',  strtotime('tomorrow',strtotime($startDate))-1);
                        } else {
                            $endDate = date('Y-m-d H:i:s',  strtotime('+1 hour',strtotime($startDate)));
                        }
                        $this->setCalendarDetailQuery('end_date', $endDate);
                    }
                    break;
                case 'end_date':
                    if(!isset($this->eventFormData['calendar_detail_id'])){
                        $eventData['date']=(isset($eventData['date']))? $eventData['date']:$this->eventFormData['start_date']['date'];
                    }
                    $dateFormat=$this->container->get('club')->get('phpdatetime');
                    if ($this->eventFormData['is_allday'] == '1') {
                        $defTime = $this->container->get('club')->formatDate('23:59:59', 'time', 'H:i:s');
                        $date = date_create_from_format($dateFormat, $eventData['date'] . ' ' . $defTime);
                    } else {
                        $date = date_create_from_format($dateFormat, $eventData['date']);
                    }
                    $endDate = date_format($date, 'Y-m-d H:i:s');
                    $this->setCalendarDetailQuery($key, $endDate);
                    break;
                case 'repeat';
                    $this->handleAppointmentRepeat($eventData);
                    break;
                case 'share_with_lower'; case 'is_allday'; case 'scope';
                    $this->calValue[]="$key= '$eventData'";
                    $this->calValueDup[]="$key = VALUES($key)";
                    break;
                case 'categories';
                    $this->setEventCategories($eventData);
                    break;
                case 'areas';
                    $this->setEventAreas($eventData);
                    break;
                case 'is_show_in_googlemap'; case 'url'; case 'location'; case 'latitude'; case 'longitude';
                    $key=($key=='latitude' || $key=='longitude')? 'location_'.$key:$key;
                    $this->setCalendarDetailQuery($key, $eventData);
                    break;
                case 'fileupload';
                    $this->setAttachmentDetails($eventData);
                    break;
            }
        }
        $this->insertAppointmentData();

        return true;
    }
    /**
     * Function to add/update appointment data
     * @throws \Common\UtilityBundle\Repository\Pdo\Exception
     */
    private function insertAppointmentData(){
        try {
            $this->conn->beginTransaction();
            $this->insertCalendarRule();
            $this->insertCalendarAppointment();
            $this->insertcalendarDetails();
            $this->insertAppointmentArea();
            $this->insertAppointmentCategory();
            $this->insertCalendarDetailI18n();
            $this->insertUploadDetails();
            if($this->caledarId){
                $this->updateDefaultTable();
            }
            $this->conn->commit();
        } catch (Exception $ex) {
            $this->conn->rollback();
            throw $ex;
        }
    }
    /**
     * Function to insert/update appoinment into fg_em_calendar
     */
    private function insertCalendarAppointment(){
        if(count( $this->calValue) > 0){
            if($this->caledarRulesId){
                $this->calValue[]="calendar_rules_id= $this->caledarRulesId";
                $this->calValueDup[]="calendar_rules_id = VALUES(calendar_rules_id)";
            }
            if($this->caledarId){
                $this->calValue[]="id= '$this->caledarId'";
            }
           $calendarQuery="INSERT INTO fg_em_calendar SET " . implode(',', $this->calValue) . " ON DUPLICATE KEY UPDATE ".implode(',', $this->calValueDup);
           $this->conn->executeQuery($calendarQuery);
        }
        if(!$this->caledarId){
            $this->caledarId=$this->conn->lastInsertId();
            $this->calDetailValue[]="calendar_id=$this->caledarId";
            $this->calDetailValueDup[]="calendar_id= VALUES(calendar_id)";
        }
    }

    /**
     * Function to set calendar table default entries
     */
    private function setCalendarDefaultValues(){
        if($this->eventFormData['scope']=='GROUP'){
            $this->eventFormData['share_with_lower']=0;
        } elseif(isset($this->eventFormData['areas'])){
            if(!in_array('Club', $this->eventFormData['areas'])){
                $this->eventFormData['share_with_lower']=0;
            }
        }
        if(!$this->caledarDetailId){
            $this->calValue[]="club_id= ".$this->container->get('club')->get('id');
            $this->calValueDup[]="club_id = VALUES(club_id)";
            $this->calDetailValue[]=$this->calValue[]="created_at= NOW()";
            $this->calDetailValueDup[]=$this->calValueDup[]="created_at = VALUES(created_at)";
            $this->calDetailValue[]=$this->calValue[]="created_by= ".$this->container->get('contact')->get('id');
            $this->calDetailValueDup[]=$this->calValueDup[]="created_by = VALUES(created_by)";
        } else {
            $this->calDetailValue[]=$this->calValue[]="updated_at= NOW()";
            $this->calDetailValueDup[]=$this->calValueDup[]="updated_at = VALUES(updated_at)";
            $this->calDetailValue[]=$this->calValue[]="updated_by= ".$this->container->get('contact')->get('id');
            $this->calDetailValueDup[]=$this->calValueDup[]="updated_by = VALUES(updated_by)";
        }
    }
    /**
     * Function to insert calendar detail table entries
     */
    private function insertcalendarDetails(){
        if(count($this->calDetailValue) > 0){
            if($this->caledarDetailId){
                $this->calDetailValue[]="id= '#CALDETAILID#'";
            }
            $calendarDetailQuery="INSERT INTO fg_em_calendar_details SET " . implode(',', $this->calDetailValue). " ON DUPLICATE KEY UPDATE ".implode(',', $this->calDetailValueDup);
            $this->updateFollowingEvents($calendarDetailQuery,false,$this->calDetailSet);
            $calendarDetailQuery=str_replace('#CALDETAILID#', $this->caledarDetailId, $calendarDetailQuery);
           
            if($this->caledarDetailId) {
                // case:  detail row is updated
                // in case of editing the until-date of a repeating event to a date smaller than startdate, all entries in the detail table will be deleted.
                //So before updating the detail table, we should check whether the row is existing (for avoiding sql query exception)
                if($this->checkDetailRowExisting()) {
                    $this->conn->executeQuery($calendarDetailQuery,$this->calDetailSet);                    
                }
            } else {
                // case: new detail row is inserted
                $this->conn->executeQuery($calendarDetailQuery,$this->calDetailSet);   
                $this->caledarDetailId=$this->conn->lastInsertId();
            }
        }
    }
    
    /**
     * Method to check whether the row is existing (for avoiding sql query exception)
     * 
     * @return boolean existing ot not
     */
    private function checkDetailRowExisting() {
        $calendarIdsQuery="SELECT COUNT(`id`) as count FROM `fg_em_calendar_details` WHERE id='$this->caledarDetailId' ";            
        $existingDetailIdCount = $this->executeQuery($calendarIdsQuery);
        
        return ($existingDetailIdCount[0]['count'] > 0) ? true : false;        
    }
    
    /**
     * Insert calendar repeat rule entries
     */
    private function insertCalendarRule(){
        if(count($this->calRulesValues) > 0){
            $this->calRulesValues[] =($this->caledarRulesId) ? "id=$this->caledarRulesId":"id=''";            
            if($this->caledarRulesId) {
                $this->handleRuleVariables();                
            }
            $calendarRuleQuery="INSERT INTO fg_em_calendar_rules SET " . implode(',', $this->calRulesValues). " ON DUPLICATE KEY UPDATE ".implode(',', $this->calRulesValueDup); //exit;
            $this->conn->executeQuery($calendarRuleQuery,$this->calRulesValueSet);
            if(!$this->caledarRulesId){
                $this->caledarRulesId=$this->conn->lastInsertId();
            }
        }
    }
    
    /**
     * Method to handle rule variables in case of updating rules. Makes null the unused values according to condition
     * 
     * return void
     */
    private function handleRuleVariables() {
        $calRuleObj = $this->em->getRepository('CommonUtilityBundle:FgEmCalendarRules')->find($this->caledarRulesId);
        $frequency = ($this->calRulesValueSet['FREQ']) ? ($this->calRulesValueSet['FREQ']) : $calRuleObj->getFreq();        
        switch ($frequency) {
            case 'DAILY':
                $this->calRulesValueDup['BYDAY'] = "`BYDAY`= NULL";
                $this->calRulesValueDup['BYMONTHDAY'] = "`BYMONTHDAY`= NULL";
                $this->calRulesValueDup['BYMONTH'] = "`BYMONTH`= NULL";
                break;
            case 'WEEKLY':
                $this->calRulesValueDup['BYMONTHDAY'] = "`BYMONTHDAY`= NULL";
                $this->calRulesValueDup['BYMONTH'] = "`BYMONTH`= NULL";
                break;
            case 'MONTHLY':
                $this->calRulesValueDup['BYMONTH'] = "`BYMONTH`= NULL";
                if(isset($this->calRulesValueSet['BYDAY'])) {
                    $this->calRulesValueDup['BYMONTHDAY'] = "`BYMONTHDAY`= NULL";
                }
                if($this->calRulesValueSet['BYMONTHDAY'] != null) {
                    $this->calRulesValueDup['BYDAY'] = "`BYDAY`= NULL";
                }
                break;
            case 'YEARLY':
                if(isset($this->calRulesValueSet['BYDAY'])) {
                    $this->calRulesValueDup['BYMONTHDAY'] = "`BYMONTHDAY`= NULL";
                }
                if($this->calRulesValueSet['BYMONTHDAY'] != null) {
                    $this->calRulesValueDup['BYDAY'] = "`BYDAY`= NULL";
                }
                break;
            case 'default':                        
                break;  
        }
    }

    /**
     * Insert calendar detail i18n entries
     */
    private function insertCalendarDetailI18n(){
        if(count($this->calDetailI18Value) > 0 && $this->caledarDetailId){
            foreach ($this->calDetailI18Value as $lang=>$fieldDetails){
                $fieldNames="id,lang";
                $fieldValues="#CALDETAILID#,'$lang'";
                $fieldValueDup ='id=VALUES(id),lang=VALUES(lang)';
                foreach($fieldDetails as $fieldName=>$fieldValue){
                    $fieldNames .= ','.$fieldName;
                    $fieldValues .= ",:$fieldName";
                    $fieldValueDup .= ",$fieldName=VALUES($fieldName)";
                }
                $detailLangValue="INSERT INTO fg_em_calendar_details_i18n ($fieldNames) VALUES ($fieldValues) ON DUPLICATE KEY UPDATE $fieldValueDup";
                $this->updateFollowingEvents($detailLangValue,FALSE,$fieldDetails);
                $detailLangValue=str_replace('#CALDETAILID#', $this->caledarDetailId, $detailLangValue);                
                if((($this->checkDetailRowExisting()) && ($this->caledarDetailId)) ) {
                    $this->conn->executeQuery($detailLangValue,$fieldDetails);                   
                }              
            }
        }
    }
    /**
     * Function to insert appointment category
     */
    private function insertAppointmentCategory(){
        if(count($this->calCategoryValues) > 0 && $this->caledarDetailId){
            $categoryQuery="INSERT INTO fg_em_calendar_selected_categories (calendar_details_id,category_id) VALUES ".implode(',', $this->calCategoryValues);
            $categoryQuery1=str_replace('#CALDETAILID#', $this->caledarDetailId, $categoryQuery);
            $this->conn->executeQuery("DELETE FROM fg_em_calendar_selected_categories WHERE calendar_details_id=$this->caledarDetailId");
            $this->conn->executeQuery($categoryQuery1);
            $extraQuery="DELETE FROM fg_em_calendar_selected_categories WHERE calendar_details_id=#CALDETAILID#";
            $this->updateFollowingEvents($categoryQuery,$extraQuery);

        }
    }
    /**
     * Function to update following events related to current event
     * @param type $query
     * @param type $extraQuery
     * @param type $fieldDetails
     */
    private function updateFollowingEvents($query,$extraQuery=false,$fieldDetails=array()){
        if(count($this->followingCalIds)>0){
            foreach ($this->followingCalIds as $followingId){
                if(!empty($followingId['calDetailId'])){
                    $queryMain=str_replace('#CALDETAILID#', $followingId['calDetailId'], $query);
                    if($extraQuery){
                        $this->conn->executeQuery(str_replace('#CALDETAILID#', $followingId['calDetailId'], $extraQuery));
                    }
                    if(count($fieldDetails)>0){
                        $this->conn->executeQuery($queryMain,$fieldDetails);
                    } else {
                        $this->conn->executeQuery($queryMain);
                    }
                }
            }
        }
    }


    /**
     * Function to insert appointment area
     */
    private function insertAppointmentArea(){
        if(count($this->calAreasValues) > 0 && $this->caledarDetailId){
            $areaQuery="INSERT INTO fg_em_calendar_selected_areas (calendar_details_id,role_id,is_club) VALUES ".implode(',', $this->calAreasValues);
            $areaQuery1=str_replace('#CALDETAILID#', $this->caledarDetailId, $areaQuery);
            $this->conn->executeQuery("DELETE FROM fg_em_calendar_selected_areas WHERE calendar_details_id=$this->caledarDetailId");
            $this->conn->executeQuery($areaQuery1);
            $extraQuery = "DELETE FROM fg_em_calendar_selected_areas WHERE calendar_details_id=#CALDETAILID#";
            $this->updateFollowingEvents($areaQuery,$extraQuery);
        }
    }

    /**
     * Function to insert appointment area
     */
    private function insertUploadDetails(){
        if(count($this->caledarUploadValues['fileName']) > 0){
            $shaFilename = $this->movetoClubFilemanagerAction($this->caledarUploadValues['randFileName'], $this->caledarUploadValues['fileName']);
            $this->caledarUploadValues['shaFileName'] = $shaFilename;

            $newlyUploadedIds = array();
            $calendarAttachments = array();
            $newOldArray = array_values($this->caledarUploadValues['newold']);
            $newOldArrayKeys = array_keys($this->caledarUploadValues['newold']);

            foreach($this->caledarUploadValues['fileName'] as $key => $calendarValues){
                if($newOldArray[$key] === 'new'){
                   //push $newlyUploadedFiles
                    $newlyUploadedFilesToBeInserted['fileName'][] = $this->caledarUploadValues['fileName'][$key];
                    $newlyUploadedFilesToBeInserted['randFileName'][] = $this->caledarUploadValues['randFileName'][$key];
                    $newlyUploadedFilesToBeInserted['fileSize'][] = $this->caledarUploadValues['fileSize'][$key];
                    $newlyUploadedFilesToBeInserted['shaFileName'][] = $this->caledarUploadValues['shaFileName'][$key];
                } else if($newOldArray[$key] === 'server'){
                    $calendarAttachments[] = $newOldArrayKeys[$key];
                }
            }

            $newlyUploadedFilesToBeInserted['fileCount'] = count($newlyUploadedFilesToBeInserted['fileName']);
            $newlyUploadedFilesToBeInserted['clubId'] = $this->caledarUploadValues['clubId'];
            $newlyUploadedFilesToBeInserted['contactId'] = $this->caledarUploadValues['contactId'];
            $newlyUploadedFilesToBeInserted['module'] = $this->caledarUploadValues['module'];
            if(count($newlyUploadedFilesToBeInserted) > 0){
                $newlyUploadedIds = $this->em->getRepository('CommonUtilityBundle:FgFileManager')->saveFilemanagerFile($newlyUploadedFilesToBeInserted, $this->container);
            }
            $calendarAttachments = array_merge($calendarAttachments,$newlyUploadedIds);
        }
        //insert into fg_em_calendar_details_attachments
        $this->insCalendarAttach($calendarAttachments);
        //Delete from fg_em_calendar_details_attachments
        $deleteVals = array();
        foreach($this->caledarUploadValues['del'] as $key => $toDel){
            if($toDel==1)
                $deleteVals[$key] = $toDel;
        }
        $this->delCalendarAttach($deleteVals);
    }
    /**
     * The function to delete calendar attachments
     *
     * @param array $delValues calendar attachment ids
     *
     */
    private function delCalendarAttach($delValues) {
        if(count($delValues) > 0){
            $deleteString = implode(',', array_keys($delValues));
            $this->conn->executeQuery("DELETE FROM fg_em_calendar_details_attachments WHERE file_manager_id IN ($deleteString) AND calendar_detail_id = $this->caledarDetailId");
            if(count($this->followingCalIds)>0){
                foreach ($this->followingCalIds as $followingId){
                    if(!empty($followingId['calDetailId'])){
                        $this->conn->executeQuery("DELETE FROM fg_em_calendar_details_attachments WHERE file_manager_id IN ($deleteString) AND calendar_detail_id = $followingId[calDetailId]");
                    }
                }
            }
        }
    }
    /**
     * The function to insert into calendar attachments
     *
     * @param array $calendarAttachments calendar attachment ids
     *
     */
    private function insCalendarAttach($calendarAttachments) {
        if(count($calendarAttachments) > 0){
            $insertValues = "";
            foreach($calendarAttachments as $key => $fileManagerIds){
                $insertValues .= " ($this->caledarDetailId, $fileManagerIds), ";
            }

            $followingCalIds = array_diff($this->followingCalIds, $calendarAttachments);
            if(count($this->followingCalIds)>0){
                foreach($this->followingCalIds as $followingCalId){
                    foreach($calendarAttachments as $key => $fileManagerIds){
                        if($followingCalId[calDetailId] != $this->caledarDetailId)
                            $insertValues .= " ($followingCalId[calDetailId], $fileManagerIds), ";
                    }
                }
            }

            if($insertValues != ''){
                $insertValues = substr($insertValues, 0, -2);
                $this->conn->executeQuery("INSERT INTO fg_em_calendar_details_attachments (calendar_detail_id,file_manager_id) VALUES $insertValues");
            }
        }
    }
    /**
     * The function to upload the file to the club filemanager folder
     *
     * @param array $randFileNameArray File random name
     * @param array $fileNameArray File name
     *
     */
    private function movetoClubFilemanagerAction($randFileNameArray, $fileNameArray) {
        $uploadDirectory = 'uploads/';
        $this->dirCheck($uploadDirectory);
        $clubDirectory = $uploadDirectory . $this->container->get('club')->get('id');
        $this->dirCheck($clubDirectory);
        $clubFilemanagerDirectory = $clubDirectory . '/content';
        $this->dirCheck($clubFilemanagerDirectory);
        $newFileNameArray = array();
        foreach ($randFileNameArray as $key => $document) {
            $fileCheck = new FileChecking($this->container);
            $fileCheck->filename = mt_rand(9999, 999999). $fileNameArray[$key];
            $shaFilename = $fileCheck->sshNameConvertion();
            if (file_exists('uploads/temp/' . $document)) {
                $attachmentObj = new File('uploads/temp/' . $document, false);
                $attachmentObj->move($clubFilemanagerDirectory, $shaFilename);
            }
            $newFileNameArray[$key] = $shaFilename;
        }
        return $newFileNameArray;
    }

    /**
     * The function to check if directory exist else add a directory
     *
     * @param string $directory Directory name
     *
     */
    private function dirCheck($directory) {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * Function to i18n array for title and description
     * @param type $field
     */
    private function setCalendarDetailI18nQuery($field,$eventData){
        if($this->caledarDetailId){
            foreach ($eventData as $lang=>$eventDetails){
                $this->calDetailI18Value[$lang][$field]=$eventDetails;
            }
        } else {
            $clubLangs=$this->container->get('club')->get('club_languages');
            foreach ($clubLangs as $lang){
                $this->calDetailI18Value[$lang][$field]= (isset($eventData[$lang])) ? $eventData[$lang] :'';
            } 
        }
    }
    /**
     * Function to get appointment date formatted
     * @param string $dateTime
     * @param string $field
     * @return string
     */
    private function getEventDateFormated($dateTime,$field){
        $dateFormat=$this->container->get('club')->get('phpdatetime');
        if(!isset($dateTime['time']) || empty($dateTime['time'])){
            $defTime=($field=='start_date') ? '00:00:00':'23:59:59';
            $dateTime['time']= $this->container->get('club')->formatDate($defTime,'time','H:i:s');
        }
        $date = date_create_from_format($dateFormat, $dateTime['date'].' '.$dateTime['time']);
        $return = date_format($date, 'Y-m-d H:i:s');

        return $return;
    }
    /**
     * Function to build query to add appointment area
     * @param type $areas
     */
    private function setEventAreas($areas){
        if(is_array($areas)){
            foreach ($areas as $areaDetails){
                $this->calAreasValues[]=(strtolower($areaDetails)=='club') ? "(#CALDETAILID#,NULL,1)":"(#CALDETAILID#,$areaDetails,0)";
            }
        } else {
            $this->calAreasValues[]=(strtolower($areas)=='club') ? "(#CALDETAILID#,NULL,1)":"(#CALDETAILID#,$areas,0)";
        }
    }
    /**
     * Function to build query to add appointment file upload
     * @param type $uploadDet
     */
    private function setAttachmentDetails($uploadDet){
        if(count($uploadDet['name']) > 0){
            $fileName = implode(',', $uploadDet['name']);
            $this->caledarUploadValues['fileName']= explode(',', $fileName);
            $randName = implode(',', $uploadDet['randName']);
            $this->caledarUploadValues['randFileName']= explode(',', $randName);
            $size = implode(',', $uploadDet['size']);
            $this->caledarUploadValues['fileSize']= explode(',', $size);
            $this->caledarUploadValues['fileCount']= $uploadDet['fileCount'];
            $this->caledarUploadValues['clubId'] = $this->container->get('club')->get('id');
            $this->caledarUploadValues['contactId'] = $this->container->get('contact')->get('id');
            $this->caledarUploadValues['module'] = 'CALENDAR';
            $this->caledarUploadValues['newold'] = $uploadDet['newold'];
        }
        $this->caledarUploadValues['detId'] = $this->caledarDetailId;
        $this->caledarUploadValues['del'] = $uploadDet['del'];
    }
    /**
     * Function to build query to add appointment category
     * @param type $categories
     */
    private function setEventCategories($categories){
        foreach ($categories as $catId){
            $this->calCategoryValues[]= "(#CALDETAILID#,$catId)";
        }
    }
    /**
     *
     * @param type $fieldName
     * @param type $fieldvalue
     */
    private function setCalendarDetailQuery($fieldName,$fieldvalue){
        $this->calDetailValue[]="$fieldName= :$fieldName";
        $this->calDetailSet[":$fieldName"]=$fieldvalue;
        $this->calDetailValueDup[]="$fieldName = VALUES($fieldName)";
    }

    /**
     * Function to handle repeated event query
     * @param type $eventData
     */
    private function handleAppointmentRepeat($eventData){
        if($eventData['frequency']=='NEVER'){
            $this->calValue[]="is_repeat= 0";
            $this->calValueDup[]="is_repeat = VALUES(is_repeat)";
            if($this->caledarId){
                $this->caledarRulesId='NULL';
                $this->calValue[]="repeat_untill_date= NULL";
                $this->calValueDup[]="repeat_untill_date = VALUES(repeat_untill_date)";
            }
        } else {
            if(isset($eventData['frequency'])){
                $this->calValue[]="is_repeat= 1";
                $this->calValueDup[]="is_repeat = VALUES(is_repeat)";
                $this->calDetailValue[]="status= 0";
                $this->calDetailValueDup[]="status = VALUES(status)";
            }
            $this->handleCalendarRule($eventData);
            if(isset($eventData['until']) && $this->caledarDetailId && $this->eventFormData['edit_mode']=='all'){
                $untilDate=$this->getEventDateFormated(array('date'=>$eventData['until']), 'until');                
                if($untilDate) {
                    $this->calValue[]="repeat_untill_date= '$untilDate'";
                    $this->calValueDup[]="repeat_untill_date = VALUES(repeat_untill_date)";
                    $updateQuery="UPDATE fg_em_calendar_details SET untill='".$untilDate."' WHERE id={$this->caledarDetailId} ";
                    $this->conn->executeQuery($updateQuery);
                    $deleteQuery="DELETE FROM fg_em_calendar_details WHERE id={$this->caledarDetailId} AND start_date > '".$untilDate."'";
                    $this->conn->executeQuery($deleteQuery);
                } else { 
                    //case when until date set to null
                    $this->calValue[]="repeat_untill_date= NULL";
                    $this->calValueDup[]="repeat_untill_date = VALUES(repeat_untill_date)";
                    $updateQuery="UPDATE fg_em_calendar_details SET untill= NULL WHERE id={$this->caledarDetailId} ";
                    $this->conn->executeQuery($updateQuery);                    
                }               
            } elseif(isset($eventData['until']) && !empty($eventData['until'])){               
                $untilDate=$this->getEventDateFormated(array('date'=>$eventData['until']), 'until');
                $this->calValue[]="repeat_untill_date= '$untilDate'";
                $this->calValueDup[]="repeat_untill_date = VALUES(repeat_untill_date)";
                $this->setCalendarDetailQuery('untill', $untilDate);
            } elseif(isset($eventData['until']) && empty($eventData['until'])) { 
                //case when untill date set to null and edit_mode not equals 'all'                
                $this->calValue[]="repeat_untill_date= NULL";
                $this->calValueDup[]="repeat_untill_date = VALUES(repeat_untill_date)";
                $this->calDetailValue[]="untill = :untill";
                $this->calDetailSet[":untill"] = NULL;
                $this->calDetailValueDup[]="untill = NULL";
            }
        }
    }
    /**
     * Function to build query to add/update appointment rule
     *
     * @param type $eventData
     */
    private function handleCalendarRule($eventData){
        $keyName=array('frequency'=>'FREQ','intervel'=>'INTERVAL','weekly_byday'=>'BYDAY','bymonthday'=>'BYMONTHDAY','byday'=>'BYDAY','byday_interval'=>'BYDAY','bymonth'=>'BYMONTH');
        foreach($eventData as $key=>$value){            
            $field=$keyName[$key];
            if($key=='weekly_byday' ||$key=='bymonth'||$key=='bymonthday'){
                $value=implode(',',$value);
            } elseif($key=='byday_interval'||$key=='byday'){
                if(isset($eventData['byday'])){                    
                    $value="{$eventData['byday_interval']}{$eventData['byday']}";
                } else {
                    continue;
                }
            }
            if(!empty($field)){
                $this->calRulesValues[$field]="`$field`= :$field";
                $this->calRulesValueDup[$field]="`$field`=VALUES(`$field`)";
                $this->calRulesValueSet["$field"]=$value;                
            }
        }    
    }
    /**
     * Function to set calendar id for edit appointment
     * @param type $caledarDetailId
     */
    public function setCalendarDetailId($caledarDetailId=false){
        $this->caledarDetailId = (isset($this->eventFormData['calendar_detail_id']) && $this->eventFormData['edit_mode'] != 'duplicate') ? $this->eventFormData['calendar_detail_id']:$caledarDetailId;
        if($this->caledarDetailId) {
            $calendarIdArray=$this->executeQuery("SELECT CD.calendar_id,CR.id,C.is_allday,C.is_repeat,CD.start_date,CD.end_date,CD.untill,CR.FREQ,CR.BYDAY,CR.`INTERVAL`,CR.BYMONTHDAY,CR.BYMONTH FROM fg_em_calendar_details CD LEFT JOIN fg_em_calendar C ON C.id=CD.calendar_id LEFT JOIN fg_em_calendar_rules CR ON C.calendar_rules_id=CR.id where CD.id='{$this->caledarDetailId}'");
            $this->caledarId=$calendarIdArray[0]['calendar_id'];
            $calendarIdArray=$calendarIdArray[0];
            if($calendarIdArray['is_repeat']=='1'){
                $this->checkRelativity();
                if($calendarIdArray['is_allday']=='1'){
                    $this->setAllDayTime();
                }
                $recurrences=$this->getEventWithNextRecurrence($calendarIdArray);
                if($this->isNonRelated){
                    $this->makeEventNonRelated($calendarIdArray,$recurrences);
                } else { //related event
                    $this->makeRelatedEvent($calendarIdArray,$recurrences);
                }
            }
        }
    }
    /**
     * Function to handle non relative repeated event for 'current' and 'following'
     * @param array $calendarIdArray
     * @param array $recurrences
     */
    private function makeEventNonRelated($calendarIdArray,$recurrences){
        $untilTime=(empty($calendarIdArray['untill']))? strtotime($recurrences['recurrenceStartDate'])+1:strtotime($calendarIdArray['untill']);
        $newuntill=date('Y-m-d H:i:s',strtotime($this->eventFormData['edit_start_date'])-1);
        $updateDetailQuery="UPDATE fg_em_calendar_details SET untill='".$newuntill."' WHERE id={$this->caledarDetailId}";
        $updateMainQuery="UPDATE fg_em_calendar SET repeat_untill_date='".$newuntill."' WHERE id={$this->caledarId}";
        if($this->eventFormData['edit_mode']=='following'|| $this->eventFormData['edit_mode']=='all'){
            $deleteQuery="DELETE FROM fg_em_calendar_details WHERE calendar_id={$this->caledarId} ";
            $deleteQueryCond="AND start_date>='{$this->eventFormData['edit_start_date']}' AND status!=2";
            $lastDetail=$this->executeQuery("SELECT id FROM fg_em_calendar_details WHERE calendar_id={$this->caledarId} ORDER BY start_date desc limit 1");
            $this->caledarDetailId=$lastDetail[0]['id'];
            $this->createNewEvent();
            if($this->eventFormData['edit_mode']=='following'){
                $this->conn->executeQuery($deleteQuery.$deleteQueryCond); //delete event entries after current event
                //update old event untill date with current start_date-1
                $this->conn->executeQuery($updateDetailQuery);
                //update untill date in main table.
                $this->conn->executeQuery($updateMainQuery);
                //update newly created event with current start and end date
                $updateNewDetailQuery="UPDATE fg_em_calendar_details SET start_date='".$this->eventFormData['edit_start_date']."',end_date='".$this->eventFormData['edit_end_date']."' WHERE id={$this->caledarDetailId}";
                $this->conn->executeQuery($updateNewDetailQuery);
            } else {
                $this->updateTimeOfEvent($calendarIdArray);
                $this->conn->executeQuery($deleteQuery); //delete event entries
            }
        } else { //Non related for current event only
            //if not last event
            if($untilTime>strtotime($recurrences['recurrenceStartDate'])){
                //create new child event with old 'start' 'end' and 'untill' dates
                $this->createChildEvent($calendarIdArray);
                //update old event untill date with current start_date-1
                $this->conn->executeQuery($updateDetailQuery);
                //update newly created child event start and end dates to next recurrence dates.
                $this->updateEventWithNextRecurrence($recurrences);
                //create new non related event.
                $this->createNewEvent();
                    $untilSet=",untill='".$this->eventFormData['edit_end_date']."'";
            } else { //last non repeated event
                //Add deleted status to current event
                $updateQ="UPDATE fg_em_calendar_details SET status=2 WHERE id={$this->caledarDetailId}";
                //Duplicate current event
                $this->createNewEvent();
                $this->conn->executeQuery($updateQ);
                $untilSet="";
            }
            //update newly created event start and end date
            $updateNewDetailQuery="UPDATE fg_em_calendar_details SET start_date='".$this->eventFormData['edit_start_date']."',end_date='".$this->eventFormData['edit_end_date']."'$untilSet WHERE id={$this->caledarDetailId}";
            $this->conn->executeQuery($updateNewDetailQuery);
        }
    }
    /**
     * Function to handle 'all' and relative repeated event for 'current' and 'following'
     * @param type $calendarIdArray
     * @param type $recurrences
     */
    private function makeRelatedEvent($calendarIdArray,$recurrences){
        $untilTime=(empty($calendarIdArray['untill']))? strtotime($recurrences['recurrenceStartDate'])+1:strtotime($calendarIdArray['untill']);
        if($this->eventFormData['edit_mode']=='following'||($this->eventFormData['edit_mode']=='current') ) {
            //if mode is following set following event ids
            if($this->eventFormData['edit_mode']=='following'){
                $this->setEventIds();
            }
            //if not last event
            if($untilTime>strtotime($recurrences['recurrenceStartDate'])){
                $newuntill=date('Y-m-d H:i:s',strtotime($this->eventFormData['edit_start_date'])-1);
                if((strtotime($this->eventFormData['edit_start_date'])-1)>strtotime($calendarIdArray['start_date'])){
                    $updateDetailQuery="UPDATE fg_em_calendar_details SET untill='".$newuntill."' WHERE id={$this->caledarDetailId}";
                    $this->createChildEvent();
                    //update old untill date to selected start_date-1
                    $this->conn->executeQuery($updateDetailQuery);
                }
                if($this->eventFormData['edit_mode']=='following'){
                    $updateNewDetailQuery="UPDATE fg_em_calendar_details SET start_date='".$this->eventFormData['edit_start_date']."',end_date='".$this->eventFormData['edit_end_date']."' WHERE id={$this->caledarDetailId}";
                    $this->conn->executeQuery($updateNewDetailQuery);
                } else {//current
                    $this->updateEventWithNextRecurrence($recurrences);
                    //create new child event and update with currently selected event date
                    $this->createChildEvent();
                    $updateNewDetailQuery="UPDATE fg_em_calendar_details SET start_date='".$this->eventFormData['edit_start_date']."',end_date='".$this->eventFormData['edit_end_date']."',untill='".$this->eventFormData['edit_end_date']."',status=1 WHERE id={$this->caledarDetailId}";
                    $this->conn->executeQuery($updateNewDetailQuery);
                }
            }
        } elseif ($this->eventFormData['edit_mode']=='all') {
            if(isset($this->eventFormData['repeat'])){
                $this->caledarRulesId=$calendarIdArray['id'];
            }
            $this->setEventIds();
        }
    }

    /**
     * Update fg_em_calendar_details start and end date with next recurrence of selected date
     * @param type $recurrences
     */
    private function updateEventWithNextRecurrence($recurrences){
        //update newly created event with next event start and end date
        $updateNewDetailQuery="UPDATE fg_em_calendar_details SET start_date='".$recurrences['recurrenceStartDate']."',end_date='".$recurrences['recurrenceEndDate']."' WHERE id={$this->caledarDetailId}";
        $this->conn->executeQuery($updateNewDetailQuery);

    }
    /**
     * Function to update time when all events are selected
     * @param type $calendarIdArray
     */
    private function updateTimeOfEvent($calendarIdArray){
        $startField='start_date';
        $endField="end_date";
        if(isset($this->eventFormData['start_date']['time']) && !empty($this->eventFormData['start_date']['time'])){
            $timeFormat=$this->container->get('club')->get('phptime');
            $date = date_create_from_format($timeFormat, $this->eventFormData['start_date']['time']);
            $time = date_format($date, 'H:i:s');
            $startField="concat_ws(' ',date(start_date), '$time')";
            $endDate=$this->getEventDateFormated($this->eventFormData['start_date'],'start_date');
            $dateArray=  explode(' ', $this->eventFormData['edit_start_date']);
            //if date is not modified unset field
            if($endDate==$dateArray[0].' '.$time){
                unset($this->eventFormData['start_date']);
            }
        }
        if(isset($this->eventFormData['end_date']['time']) && !empty($this->eventFormData['end_date']['time'])){
            $timeFormat=$this->container->get('club')->get('phptime');
            $date = date_create_from_format($timeFormat, $this->eventFormData['end_date']['time']);
            $time = date_format($date, 'H:i:s');
            $endField="concat_ws(' ',date(end_date), '$time')";
            $endDate=$this->getEventDateFormated($this->eventFormData['end_date'],'end_date');
            $dateArray=  explode(' ', $this->eventFormData['edit_end_date']);
            //if date is not modified unset field
            if($endDate==$dateArray[0].' '.$time){
                unset($this->eventFormData['end_date']);
            }
        }
        $this->conn->executeQuery("INSERT INTO fg_em_calendar_details(id,start_date,end_date)(SELECT '{$this->caledarDetailId}',$startField,$endField FROM fg_em_calendar_details WHERE calendar_id={$calendarIdArray['calendar_id']} ORDER BY start_date asc limit 1) ON DUPLICATE KEY UPDATE `start_date`=VALUES(`start_date`),`end_date`=VALUES(`end_date`)");
    }

    /**
     * Update fg_em_calendar_details start and end date with next recurrence of selected date
     * @param type $calendarIdArray
     */
    private function getEventWithNextRecurrence($calendarIdArray){
        //update newly created event with next event start and end date
        $rule=$this->getCalendarRule($calendarIdArray);
        $calendarOccObj = new CalendarRecurrence($rule,$calendarIdArray['start_date'], $calendarIdArray['end_date']);// rule, startdate, enddate
        $recurrences = $calendarOccObj->getNextRecurrence($this->eventFormData['edit_start_date']);

        return $recurrences;

    }
    /**
     * create new non related event
     */
    public function createNewEvent(){
        $newEventRuleQuery="INSERT INTO fg_em_calendar_rules (FREQ,BYDAY,`INTERVAL`,BYMONTHDAY,BYMONTH) (SELECT CR.FREQ,CR.BYDAY,CR.`INTERVAL`,CR.BYMONTHDAY,CR.BYMONTH FROM fg_em_calendar_rules CR INNER JOIN fg_em_calendar C ON C.calendar_rules_id=CR.id WHERE C.id=$this->caledarId )  ";
        $this->conn->executeQuery($newEventRuleQuery);
        $this->caledarRulesId=$this->conn->lastInsertId();
        $newEventQuery="INSERT INTO fg_em_calendar (club_id,calendar_rules_id,scope,share_with_lower,is_allday,is_repeat,repeat_untill_date,created_at,updated_at,created_by,updated_by)"
        . " (SELECT club_id,'$this->caledarRulesId',scope,share_with_lower,is_allday,is_repeat,repeat_untill_date,created_at,updated_at,created_by,updated_by FROM fg_em_calendar WHERE id= $this->caledarId)";
        $this->conn->executeQuery($newEventQuery);
        $this->caledarId=$this->conn->lastInsertId();
        $this->createChildEvent();
        $this->conn->executeQuery("UPDATE fg_em_calendar_details SET calendar_id=$this->caledarId WHERE id=$this->caledarDetailId");
    }
    /**
     * set following event ids which is relational and grater than selected start date
     */
    private function setEventIds(){
        if(isset($this->eventFormData['edit_start_date']) && isset($this->caledarId)){
            $where= ($this->eventFormData['edit_mode']=='following') ? "start_date>'".$this->eventFormData['edit_start_date']."'":"1";
            $calendarIdsQuery="SELECT id as calDetailId FROM `fg_em_calendar_details` WHERE $where AND calendar_id='$this->caledarId' ";
            $this->followingCalIds = $this->executeQuery($calendarIdsQuery);
        }
    }
    /**
     * Function to set start and end date of currently selected event for allDay
     */
    private function setAllDayTime(){
        if(isset($this->eventFormData['edit_start_date'])){
            $this->eventFormData['edit_start_date'] = date('Y-m-d H:i:s',  strtotime('midnight',strtotime($this->eventFormData['edit_start_date'])));
        }
        if(isset($this->eventFormData['edit_end_date'])){
            $this->eventFormData['edit_end_date'] = date('Y-m-d H:i:s',  strtotime('tomorrow',strtotime($this->eventFormData['edit_end_date']))-1);
        }
    }

    /**
     * Get calender rule string
     * @param type $calendarIdArray
     * @return string
     */
    private function getCalendarRule($calendarIdArray){
        $rule=$delimiter='';
        if(!empty($calendarIdArray['FREQ'])){
           $rule .="FREQ=".$calendarIdArray['FREQ'].";";
        }
        if(!empty($calendarIdArray['BYDAY'])){
           $rule .= $delimiter."BYDAY=".$calendarIdArray['BYDAY'].";";
           $delimiter=',';
        }
        if(!empty($calendarIdArray['INTERVAL'])){
           $rule .= $delimiter."INTERVAL=".$calendarIdArray['INTERVAL'].";";
           $delimiter=',';
        }
        if(!empty($calendarIdArray['BYMONTHDAY'])){
           $rule .= $delimiter."BYMONTHDAY=".$calendarIdArray['BYMONTHDAY'].";";
           $delimiter=',';
        }
        if(!empty($calendarIdArray['BYMONTH'])){
           $rule .= $delimiter."BYMONTH=".$calendarIdArray['BYMONTH'].";";
        }

        return $rule;
    }
    /**
     * create related child event
     */
    private function createChildEvent(){
        $newDetailQuery="INSERT INTO fg_em_calendar_details (calendar_id,title,start_date,end_date,untill,location,location_latitude,location_longitude,is_show_in_googlemap,url,description,created_by,created_at,updated_at,updated_by,status)"
                . "(SELECT calendar_id,title,start_date,end_date,untill,location,location_latitude,location_longitude,is_show_in_googlemap,url,description,created_by,created_at,updated_at,updated_by,status FROM fg_em_calendar_details WHERE id=".$this->caledarDetailId.")";
        $this->conn->executeQuery($newDetailQuery);
        $this->caledarDetailId=$this->conn->lastInsertId();
        $areaQuery="INSERT INTO fg_em_calendar_selected_areas (calendar_details_id,role_id,is_club) (SELECT '{$this->caledarDetailId}',role_id,is_club FROM fg_em_calendar_selected_areas WHERE calendar_details_id=".$this->eventFormData['calendar_detail_id'].") ";
        $this->conn->executeQuery($areaQuery);
        $categoryQuery="INSERT INTO fg_em_calendar_selected_categories (calendar_details_id,category_id) (SELECT '{$this->caledarDetailId}',category_id FROM fg_em_calendar_selected_categories WHERE calendar_details_id=".$this->eventFormData['calendar_detail_id'].") ";
        $this->conn->executeQuery($categoryQuery);
        $updatei18n="INSERT INTO fg_em_calendar_details_i18n (id,title_lang,lang,desc_lang) (SELECT '{$this->caledarDetailId}',title_lang,lang,desc_lang FROM fg_em_calendar_details_i18n WHERE id=".$this->eventFormData['calendar_detail_id']." )";
        $this->conn->executeQuery($updatei18n);
        $updateAttachDet="INSERT INTO fg_em_calendar_details_attachments (calendar_detail_id,file_manager_id) (SELECT '{$this->caledarDetailId}',file_manager_id FROM fg_em_calendar_details_attachments WHERE calendar_detail_id=".$this->eventFormData['calendar_detail_id'].")";
        $this->conn->executeQuery($updateAttachDet);
    }

    /**
     * Function to check relativity
     */
    private function checkRelativity(){//start_date, end_date, repeat
        if(( isset($this->eventFormData['is_allday'])|| isset($this->eventFormData['start_date']) ||isset($this->eventFormData['end_date'])|| isset($this->eventFormData['repeat']))
            && ($this->caledarDetailId) ){
            $this->isNonRelated=true;
        }
    }
    /**
     * Function to execute a sql query
     *
     * @param string $sql Sql query
     *
     * @return array $result Result array
     */
    public function executeQuery($sql)
    {
        $result = array();
        if ($sql != '') {
            $result = $this->conn->fetchAll($sql);
        }

        return $result;
    }
    /**
     * Function to remove special characters
     *
     *@return null
     */
    private function removeSpecialChars(){
        if(!empty($this->eventFormData['desc_lang'])){
            foreach ($this->eventFormData['desc_lang'] as $key => $value){
                $rValue = str_replace("&#39;","'",$value);
                /* utf-8 is added with html_entity_decode to make sure utf-8 safe encoding */
                $this->eventFormData['desc_lang'][$key] = html_entity_decode($rValue,ENT_COMPAT | ENT_HTML401, 'UTF-8');
            }
        }
    }
    
    /**
     * Method to insert to fg_em_calendar_details_i18n when inserting to fg_em_calendar_details incase of deletion of one instance
     * 
     * @param int $oldEventDetailId
     * @param int $newEventDetailId
     * 
     * return null
     */
    public function insertToI18n($oldEventDetailId, $newEventDetailId) {        
        $oldEntries = $this->conn->fetchAll("SELECT * FROM fg_em_calendar_details_i18n WHERE id = $oldEventDetailId");        
        if(count($oldEntries) > 0) {            
            foreach($oldEntries as $oldEntry) {                
                $oldEntry['id'] = $newEventDetailId;
                $oldEntry['title_lang'] = (!$oldEntry['title_lang']) ? '' : $oldEntry['title_lang'];
                $oldEntry['desc_lang'] = (!$oldEntry['desc_lang']) ? '' : $oldEntry['desc_lang'];
                
                $insertQuery="INSERT INTO fg_em_calendar_details_i18n VALUES ( '" . implode("','", $oldEntry). "' ) ";  
                $this->conn->executeQuery($insertQuery);
            }
        } 
    }
    
    /**
     * Function to update main table title
     * @param string $tablename
     */
    private function updateDefaultTable(){
        $tablename = 'fg_em_calendar_details';
        $clubDefaultLang = $this->container->get('club')->get('club_default_lang');
        $fieldArray = array('mainTable'=>$tablename,'i18nTable'=>$tablename.'_i18n','mainField'=>array('title','description'),'i18nFields'=>array('title_lang','desc_lang'));
        $query = FgUtility::updateDefaultTable($clubDefaultLang, $fieldArray, ' A.calendar_id='.$this->caledarId );

        $this->conn->executeQuery($query);
    }
}

