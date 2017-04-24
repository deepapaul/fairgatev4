<?php

namespace Common\UtilityBundle\Util;


use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
/**
 * FgPermissons
 *
 * This is used for handling exception handling/redirection/userrights/access permissions
 *
 * @package    CommonUtilityBundle
 * @subpackage Util
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

class FgPermissions{
    
    public $clubUrlIdentifier;
    public $container;
    public $clubId;
    public $contactId;
    public $em;
    public $bookedModulesDet;
    public $applicationArea;
    /**
     * Constructor.
     *
     * @param array $container Container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->clubId = $this->container->get('club')->get('id');
        $this->clubUrlIdentifier = $this->container->get('club')->get('clubUrlIdentifier');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->applicationArea = $this->container->get('club')->get('applicationArea');
        $this->bookedModulesDet = $this->container->get('club')->get('bookedModulesDet');
        $this->contactId = $this->container->get('contact')->get('id');
       
       // $this->checkBasicAccess();
    }
    
    
    /**
     * Method to check access in frond end and backend, If not thow exception
     * 
     * @param string  $intranetAccess 1/0
     * @param boolean hasBackendAccess treu/false
     * 
     * @throws AccessDeniedException
     */    
    public function checkBasicAccess($intranetAccess, $hasBackendAccess){           
        if($this->applicationArea == 'internal' &&  (!in_array('frontend1', $this->bookedModulesDet ) || $intranetAccess != '1') ) { 
            //check for public url. '/internal/public/.*' is accessible for anonymous users
            $isPublic = $this->isPublicUrl();
            if(!$isPublic) { // If not public url, access is denied for users without intranet access
                throw new AccessDeniedException();
            }
        } 
        if($this->applicationArea == 'backend' && (! $hasBackendAccess)) {
            throw new AccessDeniedException();
        }
    }
    
    /**
     * Method to return whther the current url is public or not. For handling the case - '/internal/public/.*' is accessible for anonymous users
     * 
     * @return boolean $isPublicUrl
     */
    private function isPublicUrl() {        
        $baseUrl = $this->container->get('request_stack')->getCurrentRequest()->getUri(); 
        $urlContents = explode('/', $baseUrl);
        $indexOfApplicationArea = array_search($this->applicationArea, $urlContents);
        $urlKey = $urlContents[$indexOfApplicationArea + 1];
        $isPublicUrl = ($urlKey == 'public') ? true : false;
        
        return $isPublicUrl;
    }
    
    
    public function checkAreaAccess($input){
        switch($input['from']){
            case 'group':
                $this->checkClubAccess($input['id'],$input['type']);
                $access = $this->checkUserAccess($input['id'],$input['type'],$input['allowedRights']);
                break;
            case 'message':
                $this->checkClubAccess($input['is_existing'],$input['type']);
                $access = $this->checkUserAccess($input['has_access'],$input['type']);
                break;
            case 'overview':
            case 'memberlist':
            case 'forum':	
                $access = $this->checkUserAccess($input['from'],$input['type'],array('adminFlag'=>$input['adminflag'],'roleId'=>$input['groupid']));
                break;
            case 'messagewizard':
                $this->checkClubAccess($input['id'],$input['type']);
                $access = $this->checkUserAccess($input['id'],$input['type'],array('createdBy' => $input['createdBy'],'club' => $input['club']));
                break;
            case 'newsletter':
                //$check = $this->checkNewsletterAccess($input['newsletterId']);
                $this->checkClubAccess($input['isAccess']);
                
                break;
            case 'importShare':
                $isAccess = 0;
                if($this->container->get('contact')->get('isSuperAdmin') && $this->container->get('club')->get('type') == 'federation'){
                    $isAccess = true;
                }
                $this->checkClubAccess($isAccess);
                
                break;
            default:
                $this->checkClubAccess($input['from'],$input['type'],$input['message']);
        }
        return $access;
    }
    
    public function checkClubAccess($check,$type,$message=''){
        switch($type){
            case 'teams': 
            case 'workgroups':
            case 'communication':    
                $access = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->checkRole($check,$this->clubId);
                break;
            case 'message':
            case 'membershiplist':
                 $access = $check;
                break;
            case 'messagewizard':
                //$check - if club has access to this message 
                $access = ($check == null) ||($check == '') ?null:1;
                break;
            case 'newsletter':
                $access = $check;
                break;
            case 'backend_document_edit':
            default:
                 //$check - if club has access 
                $access = $check;
                break;
            
        }
        
        if(empty($access)){
          
             throw new NotFoundHttpException($message);
        }
        return true;
    }
    /**
     * Check Users Access 
     * @param   int     $check    can be the id to check or has access privilege
     * @param   string  $type       type of page
     * @return  type
     * @throws AccessDeniedException
     */
    public function checkUserAccess($check='',$type,$extra = array()){
        switch($type){
            case 'teams':
            case 'workgroups':
                if($check == 'overview' || $check == 'memberlist'){
                    //toCheck - from which page, type- team/workgroup
                    $access =  $this->getAllowedTabs($check,$type);
                } else if($check == 'forum') {
                     $allowedTab =  $this->getAllowedTabs($check,$type);
                     $access = $this->iterationFoumTabs($allowedTab, $extra['adminFlag']);            
                     $arrayKeys= array_keys($access);
                     if(count($access)<=0){
                       $access = array();  
                     } else if($extra['roleId']!='' && !in_array($extra['roleId'],$arrayKeys)){
                        throw new NotFoundHttpException();
                     }
                     
                } else{
                    //$toCheck -  grp id
                    $access = $this->getUsersRightsForAGrp($check,$type,$extra);
                }
                break;
                
            case 'message':
                //if $check == 1 =>is deleted message or $check == null ->contact nt a message receiver 
                $access = ($check == null)|| ($check == 1)?null:1;
                break;
            case 'messagewizard':
                    $access = ($extra['createdBy'] == $this->contactId ) && ($extra['club'] == $this->clubId )? 1:0;
                break;
            case 'no_access':
                $access = 0;
                break;
            default: 
                $access = $check;
                break;
                
        }   
        
        //if current user has no access 
        if (empty($access)) {
            throw new AccessDeniedException();
        }
        return $access;
    }
    
    /**
     * Get Logged users rights for particular group (team/workgroup/executive board) and check whether has access to resp page
     * @param int       $roleId                         role id
     * @param string    $type                           team/workgroup/executive board
     * @param array     $allowedRightsForCurrentPage    allowedRightsForCurrentPage
     * @return array return the rights common for logged contact and the rights allowed in resp page
     */
    private function getUsersRightsForAGrp($roleId,$type,$allowedRightsForCurrentPage){
        
        $mainAdminRightsForFrontend = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        $memberClubRoles = $this->container->get('contact')->get('memberClubRoles');
        $clubRoleRightsRoleWise = $this->container->get('contact')->get('clubRoleRightsRoleWise');
        $clubRoleRights = array();
        //Give group admin(team/workgroup) rights ifcurrent user is a club admin or super admin
        if (!empty($mainAdminRightsForFrontend)) {
            $clubRoleRights[] = 'ROLE_GROUP_ADMIN';
        }
         $getVisibleForForeignRoles = $this->em->getRepository("CommonUtilityBundle:FgRmRole")->getVisibleForForeignContactRoles($this->container->get('club')->get('id'),$this->container->get('contact')->get('corrLang'));
        //Check the current user is member of the selected club role
        if (in_array($roleId,  array_merge(array_keys($memberClubRoles[$type]+$getVisibleForForeignRoles[$type])))) {
            $clubRoleRights[] = 'MEMBER';
        }
        //$allClubRoleRights
        $loggedInUserRights = array_merge($clubRoleRights, (array) $clubRoleRightsRoleWise[$roleId]['rights']);
        
        $access = array_intersect($loggedInUserRights, $allowedRightsForCurrentPage);
        
        return $access;
    }
    
    
    
    /**
     * To remove all team/workgroup which has no access.
     *
     * @param String $page page type
     * @param String $type group type
     *
     * @return array
     */
    private function getAllowedTabs($page, $type = 'teams') {
        $assignedGroups = $this->container->get('contact')->get($type);
        $groupRights = $this->container->get('contact')->get('clubRoleRightsGroupWise');
        $allRights = array_keys($groupRights);
        $groups = array_keys($assignedGroups);
        $mainRights = $this->container->get('contact')->get('mainAdminRightsForFrontend');
        foreach ($groups as $group) {
            $flag = 0;
            if (!empty($mainRights)) {
                $flag = 1;
            } elseif (in_array('MEMBER', $allRights) && in_array($group, $groupRights['MEMBER'][$type])) {
                $flag = 1;
            } elseif (in_array('ROLE_GROUP_ADMIN', $allRights) && in_array($group, $groupRights['ROLE_GROUP_ADMIN'][$type])) {
                $flag = 1;
            } elseif (in_array('ROLE_FORUM_ADMIN', $allRights) && in_array($group, $groupRights['ROLE_FORUM_ADMIN'][$type])) {
                 $flag = 1;
            } elseif ($page == 'memberlist' && in_array('ROLE_CONTACT_ADMIN', $allRights) && in_array($group, $groupRights['ROLE_CONTACT_ADMIN'][$type])) {
                $flag = 1;
            }
            if ($flag == 0) {
                unset($assignedGroups[$group]);
            }
        }
        
        return $assignedGroups;
    }
        /**
     * Iterate forum list for tabs.
     *
     * @param int $id roleid
     *
     * @return template
     */
    private function iterationFoumTabs($allowedTabs, $isAdmin)
    {
        $clubId = $this->container->get('club')->get('id');
        $activeForums = $this->em->getRepository('CommonUtilityBundle:FgForumTopic')->getActiveForums($clubId);
        $activeForums = array_map(function ($a) { return $a['id'];}, $activeForums);
        $Grouprights = array_map(function($a){ return $a['rights'][0];}, $this->container->get('contact')->get('clubRoleRightsRoleWise'));
        $roles = array('ROLE_GROUP_ADMIN','ROLE_FORUM_ADMIN');
        $GrouprightsNew=array();
        foreach ($Grouprights as  $key=>$gts){
            if(in_array($gts, $roles)){
               $GrouprightsNew[$key] =  $gts;
            }
        }
        $GrouprightsKeys =array_keys($GrouprightsNew);
        
        $isAdmin = (count($this->container->get('contact')->get('mainAdminRightsForFrontend'))>0) ? 1 : 0;
        $newTabs = array();
        foreach ($allowedTabs as $key => $atab) {
            if ($isAdmin) {
                $newTabs[$key] = array('url' => '#','text' => $atab, 'isActive' => in_array($key, $activeForums), 'isAdmin'=>1);
            }else{
                if(in_array($key, $activeForums) ||  in_array($key, $GrouprightsKeys) ){
                    $newTabs[$key] = array('url' => '#','text' => $atab, 'isActive' => in_array($key, $activeForums), 'isAdmin'=>in_array($key, $GrouprightsKeys));
                }
            }

        }

        return $newTabs;
    }
    /**
     * Check Forum Access for top navigation 
     * @param   Array     $accessArray    (from, type, adminFlag)
     * @return  Array
     */
    public function checkForumAccess($accessArray) {
        $allowedTab =  $this->getAllowedTabs($accessArray['from'],$accessArray['type']);
        $access = $this->iterationFoumTabs($allowedTab, $accessArray['adminFlag']); 
        return $access;
    }
    /**
     * Check Document Access for downloadLink
     * @param   int     $docPublic  documentpublic 
     * @param   int     $contactId   contactid
     * @param   int     $visibleToContacts   visibleToContacts
     * @param   int     $docId   documentId
     * 
     * @return  boolean
     */
    public function checkDocumentAccess($docPublic, $contactId, $visibleToContacts , $docId) {
        $user_right = array('ROLE_DOCUMENT', 'ROLE_CONTACT', 'ROLE_SUPER', 'ROLE_USERS', 'ROLE_READONLY_CONTACT', 'ROLE_READONLY_SPONSOR', 'ROLE_FED_ADMIN' ,'ROLE_DOCUMENT_ADMIN');
        $contact = $this->container->get('contact');
        $groupRightsArray = $contact->get('availableUserRights');
        $documentDetails = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->getDocumentDetails($docId);
        $docAccess = false;
        if(!empty($contact->get('id'))){
         $docAccess = $this->em->getRepository('CommonUtilityBundle:FgDmDocuments')->checkDocumentAccessForUser($documentDetails[$docId], $this->container, $this->container->get('club')->get('id'));
        }
        
        if($docAccess==true || $docPublic==1){
           
          return true;
          
        }else if(($docPublic==0)&& empty($contactId))  {
             
           return false;
           
        }else if($docAccess==false){
            
            return false;
        }else if(!empty($contactId)&&($visibleToContacts==0)&&!array_intersect($user_right , $groupRightsArray)){
            
           return false;
        }
        
        return true;
    }

    /**
     * Check if Newsletter has Access in club 
     * @param   int     $newsletterId    can be the id to check or has access privilege
     * @return  type boolean
     */
    private function checkNewsletterAccess($newsletterId){
        $resNl = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewsletterSidebarContents($this->clubId, $newsletterId);
        return count($resNl)>0 ?1:0;
    }
    
}
