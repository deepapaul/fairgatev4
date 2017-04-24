<?php

namespace Common\UtilityBundle\Listener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Common\UtilityBundle\Util\FgPermissions;

/**
 * FgContactListener.
 *
 * This is the service class of club which set contact details
 *
 * @author     PIT Solutions <pitsolutions.ch>
 *
 * @version    Fairgate V4
 */
class FgContactListener
{

    /**
     * Service Container.
     *
     * @var Obeject
     */
    private $container;

    /**
     * Router object.
     *
     * @var Object
     */
    private $router;

    /**
     * Parameter Array.
     *
     * @var Array
     */
    public $parameters = array();

    /**
     * Parameter Array.
     *
     * @var Array
     */
    public $session = array();

    /**
     * Entity manager.
     *
     * @var Array
     */
    private $em;

    /**
     * Response event.
     *
     * @var Object
     */
    private $responseEvent;

    /**
     * Constructor.
     *
     * @param array $container Container
     */
    public function __construct(ContainerInterface $container, $router)
    {
        $this->container = $container;
        $this->session = $this->container->get('session');
        $this->router = $router;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->adminEntityManager = $this->container->get("fg.admin.connection")->getAdminEntityManager();
    }

    /**
     * Method to set parametes of contact and to trigger custom logout.
     *
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $this->responseEvent = $event;
        $contactId = $this->session->get('loggedClubUserId', 0);

        if ($contactId && $this->container->get('security.token_storage')->getToken()) {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            if ($user) {
                $this->setContactParameters($user);
                //check whether club switched, if yes do login trigger
                $club = $this->container->get('club');
                $this->checkIfClubSwitched($event, $club->get('applicationArea'), $user);
            }
        }
        $this->setUserCookieForLang();
    }

    /**
     * Method to set contact parameters, roles and uerrights.
     *
     * @param object $user sf-guard user object
     */
    public function setContactParameters($user)
    {
        $club = $this->container->get('club');
        $applicationArea = $club->get('applicationArea');  //internal/backend

        $contactDetails = $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->getContactDetails($user->getId());

        $club->setContactId($contactDetails['id']);
        $this->parameters['id'] = $contactDetails['id'];
        $this->parameters['name'] = $contactDetails['contactname'];
        $this->parameters['nameNoSort'] = $contactDetails['contactnamenosort'];
        $this->parameters['email'] = $contactDetails['email'];
        $this->parameters['isCompany'] = $contactDetails['isCompany'];
        $this->parameters['contactClub'] = $user->getContact()->getClub()->getId();
        $this->parameters['createdClub'] = $user->getContact()->getcreatedClub()->getId();
        $this->parameters['isStealthMode'] = $user->getContact()->getIsStealthMode();
        $this->parameters['isFedMember'] = $contactDetails['isFedCategory'];
        $this->parameters['fedContactId'] = $contactDetails['fedContactId'];
        $this->parameters['subfedContactId'] = $contactDetails['subfedContactId'];
        $contactData = $this->setContactDetails($club, $contactDetails['id'], $user->getRoles());
        $this->parameters['corrLang'] = (isset($contactData['default_lang'])) ? $contactData['default_lang'] : '';
        $this->parameters['accessibleClubs'] = $contactData['accessibleClubs'];
        $this->parameters['isFedAdmin'] = false;

        //Club of contact
        $this->setUserRoles($user->getRoles());
        //set all teams and workgroups and role userrights of the logged in contact
        if ($applicationArea != 'backend') {
            $this->setMyRoles($user);
        }
        $this->checkAccess($user->getContact()->getIntranetAccess());
    }

    /**
     * Method check whether club switched, if yes do login trigger, if the user have access in that club or make log out.
     *
     * @param object $event           GetResponseEvent object
     * @param string $applicationArea internal/backend
     * @param object $user            sf-guard user object
     */
    private function checkIfClubSwitched($event, $applicationArea, $user)
    {
        //Generate path according to application area
        $request = $event->getRequest();
        $currenturl = $request->getRequestUri();
        $logoutPath = ($applicationArea === 'internal') ? $this->router->generate('internal_user_security_logout') : $this->router->generate('fairgate_user_security_logout');
        if ($currenturl != $logoutPath) {  //if current url is logout, customLogoutTrigger is not required
            $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->customLogoutTrigger($this->container, $this->session, $logoutPath, $request, $user);
        }
    }

    /**
     * Method to check access of user in backend/frontend.
     *
     * @param bool $intranetAccess intranetAccess value of current contact
     */
    private function checkAccess($intranetAccess)
    {
        $FgPermissionsObj = new FgPermissions($this->container);
        $FgPermissionsObj->checkBasicAccess($intranetAccess, $this->parameters['hasBackendAccess']);
    }

    /**
     * Function to get paramters of a contact.
     *
     * @param type $name Name
     *
     * @return String
     */
    public function get($name)
    {
        return $this->parameters[$name];
    }

    /**
     * Function to set paramters of a contact.
     *
     * @param string $title parameter of the menu for settings
     * @param string $value value of the parameter for setting
     *
     * @return String
     */
    public function set($title, $value)
    {
        $this->parameters[$title] = $value;
    }

    /**
     * Function to set user roles according to booked modules.
     *
     * @param int $userRoleArr Logged user role array
     */
    private function setUserRoles($userRoleArr)
    {
        $accessRoles = array('ROLE_COMMUNICATION', 'ROLE_DOCUMENT', 'ROLE_CONTACT', 'ROLE_READONLY_CONTACT', 'ROLE_SPONSOR', 'ROLE_READONLY_SPONSOR', 'ROLE_USERS', 'ROLE_SUPER', 'ROLE_FED_ADMIN', 'ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN', 'ROLE_CALENDAR_ADMIN', 'ROLE_CALENDAR', 'ROLE_GALLERY_ADMIN', 'ROLE_GALLERY', 'ROLE_ARTICLE_ADMIN', 'ROLE_ARTICLE', 'ROLE_CMS_ADMIN', 'ROLE_PAGE_ADMIN');
        $internalOnlyAccessRoles = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN', 'ROLE_CALENDAR_ADMIN', 'ROLE_CALENDAR', 'ROLE_GALLERY_ADMIN', 'ROLE_GALLERY', 'ROLE_ARTICLE_ADMIN', 'ROLE_ARTICLE', 'ROLE_CMS_ADMIN', 'ROLE_PAGE_ADMIN');
        //All Rights which have full access everywhere in frontend
        $clubAdminRightsForFrontend = array('ROLE_USERS', 'ROLE_SUPER', 'ROLE_FED_ADMIN');
        $this->parameters['hasBackendAccess'] = false;
        $this->parameters['isSuperAdmin'] = false;
        $moduleRights = array(
            'communication' => 'ROLE_COMMUNICATION',
            'document' => 'ROLE_DOCUMENT',
            'contact' => 'ROLE_CONTACT',
            'readonly_contact' => 'ROLE_READONLY_CONTACT',
            'sponsor' => 'ROLE_SPONSOR',
            'readonly_sponsor' => 'ROLE_READONLY_SPONSOR',
            'gallery' => 'ROLE_GALLERY',
            'calendar' => 'ROLE_CALENDAR',
            'article' => 'ROLE_ARTICLE',
            'cms' => 'ROLE_CMS_ADMIN',
            'page' => 'ROLE_PAGE_ADMIN'
        );
        if (in_array('ROLE_SUPER', $userRoleArr)) {
            $allowedModuleRights = array_keys($moduleRights);
            $allowedModuleRights[] = 'clubAdmin';
            $availableUserRights = $accessRoles;
            $this->parameters['isSuperAdmin'] = true;
        } elseif (in_array('ROLE_USERS', $userRoleArr)) {
            $allowedModuleRights = array_keys($moduleRights);
            $availableUserRights = $accessRoles;
            $allowedModuleRights[] = 'clubAdmin';
        } elseif (in_array('ROLE_FED_ADMIN', $userRoleArr)) {
            $allowedModuleRights = array_keys($moduleRights);
            $allowedModuleRights[] = 'clubAdmin';
            $availableUserRights = $accessRoles;
            $this->parameters['isFedAdmin'] = true;
        } else {
            $allowedModuleRights = array_keys(array_intersect($moduleRights, $userRoleArr));
            $availableUserRights = array_intersect($accessRoles, $userRoleArr);
        }
        $backendAccessRoles = array_diff($accessRoles, $internalOnlyAccessRoles);

        if (count(array_intersect($backendAccessRoles, $userRoleArr)) > 0) {
            $this->parameters['hasBackendAccess'] = true;
        }
        //All allowed modules for the current user
        $this->parameters['allowedModules'] = $allowedModuleRights;
        $this->parameters['availableUserRights'] = $availableUserRights;
        //All user rights of the current user
        $this->parameters['allRights'] = $userRoleArr;
        //Rights which have full access everywhere in frontend for the current user
        $this->parameters['mainAdminRightsForFrontend'] = array_intersect($clubAdminRightsForFrontend, $userRoleArr);
    }

    /**
     * Function to set roles and group rights of logged in contact.
     *
     * @param object $user sfGuardUser object
     */
    private function setMyRoles($user)
    {
        $club = $this->container->get('club');
        $workgroupCatId = $club->get('club_workgroup_id');
        $teamCatId = $club->get('club_team_id');
        $contactId = $this->parameters['id'];

        $teams = $workgroups = $groupRights = $rightsPerClubRole = $assignedRoles = array();
        //To get teams and administrator teams
        $adminTeams = array();
        $adminWorkgroups = array();
        // if have any group rights in a club role, then that club role is also viewable
        $groupAdminRights = array('ROLE_GROUP_ADMIN', 'ROLE_CONTACT_ADMIN', 'ROLE_FORUM_ADMIN', 'ROLE_DOCUMENT_ADMIN', 'ROLE_CALENDAR_ADMIN', 'ROLE_GALLERY_ADMIN', 'ROLE_ARTICLE_ADMIN');
        if (count(array_intersect($groupAdminRights, $this->parameters['allRights'])) > 0) {
            $adminRoles = $this->em->getRepository('CommonUtilityBundle:SfGuardUserTeam')->getAllRolesUnderMyOwnership($this->container, $user->getId());
            $adminTeams = $adminRoles['rolesWithAccess']['teams'];
            $adminWorkgroups = $adminRoles['rolesWithAccess']['workgroups'];
            $groupRights = $adminRoles['rolesWithRights'];
            $rightsPerClubRole = $adminRoles['rightsPerClubRole'];
        }

        // all roles of which I am a member
        $assignedRoles = $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->getAllRolesOfAContact($this->container, $contactId);

        $getVisibleForForeignRoles = $this->em->getRepository("CommonUtilityBundle:FgRmRole")->getVisibleForForeignContactRoles($club->get('id'), $this->parameters['corrLang']);
        $groupRights['MEMBER'] = array('teams' => array_keys($assignedRoles['teams'] + $getVisibleForForeignRoles['teams']), 'workgroups' => array_keys($assignedRoles['workgroups'] + $getVisibleForForeignRoles['workgroups']));

        // if superadmin or clubadmin then all active teams and workgroups are available
        if (in_array('clubAdmin', $this->parameters['allowedModules'])) {
            $assignedRoles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
            $teams = $assignedRoles['teams'];
            $workgroups = $assignedRoles['workgroups'];
        } else {
            $teamIds = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getTeamIds($teamCatId);
            $assignedRoles['teams'] = $this->sortRoles($assignedRoles['teams'], $teamIds);
            $adminTeams = $this->sortRoles($adminTeams, $teamIds);
            $getVisibleForForeignRoles['teams'] = $this->sortRoles($getVisibleForForeignRoles['teams'], $teamIds);
            $allTeamsExcludeForeignContactVisibility = $adminTeams + $assignedRoles['teams'];
            $teams = $adminTeams + $assignedRoles['teams'] + $getVisibleForForeignRoles['teams'];

            $wrkGrpIds = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getRoleIds($workgroupCatId, true);
            $teamsExcludeForeignContactVisibility = $allTeamsExcludeForeignContactVisibility;
            $adminWorkgroups = $this->sortRoles($adminWorkgroups, $wrkGrpIds);
            $assignedRoles['workgroups'] = $this->sortRoles($assignedRoles['workgroups'], $wrkGrpIds);
            $allWGExcludeForeignContactVisibility = $adminWorkgroups + $assignedRoles['workgroups'];
            $getVisibleForForeignRoles['workgroups'] = $this->sortRoles($getVisibleForForeignRoles['workgroups'], $wrkGrpIds);
            $workgroups = $adminWorkgroups + $assignedRoles['workgroups'] + $getVisibleForForeignRoles['workgroups'];
            $workgroupsExcludeForeignContactVisibility = $allWGExcludeForeignContactVisibility;
        }

        //all teams viewable for a logged in contact- is member or is admin
        $this->parameters['teamsExcludeForeignContactVisibility'] = (isset($teamsExcludeForeignContactVisibility)) ? $teamsExcludeForeignContactVisibility : '';
        //all workgroups viewable for a logged in contact- is member or is admin
        $this->parameters['workgroupsExcludeForeignContactVisibility'] = (isset($workgroupsExcludeForeignContactVisibility)) ? $workgroupsExcludeForeignContactVisibility : '';
        //all teams viewable for a logged in contact- is member or is admin  or team is visible for foreign contact
        $this->parameters['teams'] = $teams;
        //all workgroups viewable for a logged in contact- is member or is admin  or workgroup is visible for foreign contact
        $this->parameters['workgroups'] = $workgroups;
        //User rights for club roles
        $this->parameters['clubRoleRightsGroupWise'] = $groupRights;
        //Roles (team/workgroup) with specific rights
        $this->parameters['clubRoleRightsRoleWise'] = $rightsPerClubRole;
        //All club roles(team/workgroup) where the current user is a member
        $this->parameters['memberClubRoles'] = $assignedRoles;
    }

    /**
     * Function to get roles in order.
     *
     * @param array $allRoles All roles
     * @param array $roleIds  Role ids in order
     *
     * @return array $roles Sorted array of roles
     */
    private function sortRoles($allRoles, $roleIds)
    {
        $roles = array();
        foreach ($roleIds as $roleId) {
            if (isset($allRoles[$roleId])) {
                $roles[$roleId] = $allRoles[$roleId];
            }
        }

        return $roles;
    }

    /**
     * Function to get all rights rights of the current user for a club Role.
     *
     * @param int  $roleId      Id of club role
     * @param bool $showNoAcces Boolean value to decide whether show no access or not
     *
     * @return array Array of rights of the given team for the selected role
     */
    public function checkClubRoleRights($roleId, $showNoAcces = true)
    {
        $clubRoleRights = array();
        //Give group admin(team/workgroup) rights ifcurrent user is a club admin or super admin
        if (!empty($this->parameters['mainAdminRightsForFrontend'])) {
            $clubRoleRights[] = 'ROLE_GROUP_ADMIN';
        }
        $club = $this->container->get('club');
        $getVisibleForForeignRoles = $this->em->getRepository("CommonUtilityBundle:FgRmRole")->getVisibleForForeignContactRoles($club->get('id'), $this->parameters['corrLang']);
        //Check the current user is member of the selected club role
        if (in_array($roleId, array_merge(array_keys($this->parameters['memberClubRoles']['teams'] + $getVisibleForForeignRoles['teams']), array_keys($this->parameters['memberClubRoles']['workgroups'] + $getVisibleForForeignRoles['workgroups'])))) {
            $clubRoleRights[] = 'MEMBER';
        }
        $allClubRoleRights = array_merge($clubRoleRights, (array) $this->parameters['clubRoleRightsRoleWise'][$roleId]['rights']);

        //if current user has no access and need to show no access
        if (empty($allClubRoleRights) && $showNoAcces) {
            throw new AccessDeniedException();
        }

        return $allClubRoleRights;
    }

    /**
     * Function to set contact details (system lang, corres. lang and accessible clubs) and set locale settings.
     *
     * @param object $club
     * @param int    $contactId
     * @param array  $userRoles
     *
     * @return array Return contact details
     */
    private function setContactDetails($club, $contactId, $userRoles)
    {
        $rowContactLocale = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactData(array($contactId));
        if (count($rowContactLocale) > 0) {
            $cookieDetail = $this->getCookieValue();

            //If logged in user is superadmin, club locale shoud retain. So no need to set contact locale
            if ((!in_array('ROLE_SUPER', $userRoles)) && ($cookieDetail['cookieset'] == 0)) {
                //set contact locale

                $contactLocale = $this->setContactLocale($this->container, $this->responseEvent->getRequest(), $rowContactLocale);
                if (count($contactLocale) > 0) {

                    $club->setParameters($contactLocale);
                }
            }

            // set accessible clubs
            $accessibleClubs = array();
            if ($rowContactLocale[0]['accessibleClubs']) {
                $accessibleClubsArray = explode(',', $rowContactLocale[0]['accessibleClubs']);
                $accessibleClubContactsArray = explode(',', $rowContactLocale[0]['accessibleClubContacts']);
                $accessibleTypesArray = explode(',', $rowContactLocale[0]['accessibleTypes']);
                for ($i = 0; $i < count($accessibleClubsArray); $i++) {
                    $accessibleClubs[$accessibleClubsArray[$i]] = array('contactId' => $accessibleClubContactsArray[$i], 'clubType' => $accessibleTypesArray[$i]);
                }
            }
            $contactLocale['accessibleClubs'] = $accessibleClubs;
            $this->setQuickWindowParameter($rowContactLocale[0]['quickwindowVisibilty']);

            return $contactLocale;
        }
    }

    /**
     * Method to set locale with respect to a particular contact.
     *
     * @param object $container        containet object
     * @param object $request          request object
     * @param array  $rowContactLocale array of (id, default_lang, default_system_lang)
     * @param bool   $isCron           From cron or not
     * @param integer $cookieSet       Set Cookie (1,0)
     *
     * @return array $contactLocale contact locale details
     */
    public function setContactLocale($container, $request, $rowContactLocale, $isCron = false, $cookieSet = 0)
    {
        $contactLocale = $rowContactLocale[0];
        unset($contactLocale['id']);
        $club = $container->get('club');
        $clubLanguages = $club->get('club_languages');
        $clubLanguagesDet = $club->get('club_languages_det');
        $contactCorrespondanceLang = $rowContactLocale[0]['default_lang'];
        $contactSytemLang = $rowContactLocale[0]['default_system_lang'];
        $defaultCorrepondanceLang = $club->get('default_lang');
        // If the correspondance language of the conntact is there in the club languages list, set that language as correspondance language
        if ($cookieSet == 1) {
            $club->setLocaleSettings($clubLanguagesDet[$contactCorrespondanceLang]);
        }

        if (in_array($contactCorrespondanceLang, $clubLanguages)) {
            // If sytem language is set as default , set that to system lanugage of the correspondance language
            if ($contactSytemLang == 'default' || $contactSytemLang == '') {
                $contactLocale['default_system_lang'] = $clubLanguagesDet[$contactCorrespondanceLang]['systemLang'];
            }
            if ($contactCorrespondanceLang != $defaultCorrepondanceLang) {
                $club->setLocaleSettings($clubLanguagesDet[$contactCorrespondanceLang]);
            }
            //if correspondane language of the contact is not exist and system language is set to fefault value, set club default correpondance language and system language
        } else {
            $contactLocale['default_lang'] = $club->get('default_lang');
            if ($contactSytemLang == 'default' || $contactSytemLang == '') {
                $contactLocale['default_system_lang'] = $club->get('default_system_lang');
            }
        }

        //To reflect translation in user's system lanuage
        $container->get('translator')->setLocale($contactLocale['default_system_lang']);
        //request object not available
        if ($request !== null) {
            $request->setLocale($contactLocale['default_system_lang']);
        }
        \Locale::setDefault($contactLocale['default_system_lang']);
        /* SET TERMINOLOGY SETTINGS ACCORDING TO LANGUGAGE SETTINGS OF THE LOGGED IN USER */
        if (!$isCron) {
            $container->get('fairgate_terminology_service')->setDefault($club);
        }

        return $contactLocale;
    }

    /**
     * Used for setting quick window visibility parameter.
     *
     * @param $parameter
     */
    private function setQuickWindowParameter($quickwindowVisibilty)
    {
        $session = $this->container->get('session');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');

        $alreadyShown = ($session->get('windowVisibility_' . $clubId . '_' . $contactId) == null) ? 0 : 1;
        if ($alreadyShown || !$quickwindowVisibilty || $contactId == 1) {
            $this->parameters['windowVisibilty'] = 0;
        } else {
            $this->parameters['windowVisibilty'] = 1;
        }
        $this->parameters['quickwindowVisibilty'] = $quickwindowVisibilty;

        return;
    }

    /**
     * Used for setting  user language from cookie
     *
     * @param $parameter
     */
    private function setUserCookieForLang()
    {
        $club = $this->container->get('club');
        $clubLangDet = $club->get('club_languages_det');
        $cookieDetail = $this->getCookieValue();
        if ($cookieDetail['cookieset'] == 1) {
            $cookieLang = $cookieDetail['lang'];
            $cookieSystemLang = $clubLangDet[$cookieLang]['systemLang'];
            ////set locale with respect to cookie
            $demoArray = array(array('default_lang' => $cookieLang, 'default_system_lang' => $cookieSystemLang));
            $cookieLocale = $this->setContactLocale($this->container, $this->responseEvent->getRequest(), $demoArray, false, 1);
            if (count($cookieLocale) > 0) {
                $cookieLocale['club_default_lang'] = $cookieLang;
                $club->setParameters($cookieLocale);
            }
        }

        //To set the club TITLE, SIGNATURE based on default language
        $this->setClubParamsOnLangauge($club);

        return;
    }

    /**
     * Function used to get cookie value and checking
     *
     * @param array
     */
    private function getCookieValue()
    {
        $club = $this->container->get('club');
        $langCookie = 'fg_website_lang_' . $club->get('id');
        $applicationArea = $club->get('applicationArea');
        $clubLanguages = $club->get('club_languages');
        $clubLangDet = $club->get('club_languages_det');
        $clubFrontend = $club->get('isWebsiteFrontend');
        $cookies = $this->container->get('request_stack')->getCurrentRequest()->cookies;
        $cookieArray['lang'] = $cookies->get($langCookie);
        $cookieArray['cookieset'] = 0;
        if (($cookies->get($langCookie)) && in_array($cookieArray['lang'], $clubLanguages) && in_array($cookieArray['lang'], $clubLanguages) && $clubFrontend && ($applicationArea != 'internal' && $applicationArea != 'help' && $applicationArea != 'files' && $applicationArea != 'backend' && $applicationArea != 'website')) {
            $cookieArray['cookieset'] = 1;
        }

        return $cookieArray;
    }

    /**
     * Function to set the title/signature/logo of the club based on the contact/club default language
     * It depends on whether they are logged in or not. Also it set the club log and signature details
     *
     * @param object $club club container
     * 
     */
    public function setClubParamsOnLangauge($club)
    {
        //Set the language title of club based on the club default language or the logged in contacts correspondence language
        $clubLangDetails = $club->get('club_details');
        $club->set('title', $clubLangDetails[$club->get('default_lang')]['title']);

        //Set the signature and club logo based on the club default language or the logged in contacts correspondence language  
        if ($clubLangDetails[$club->get('default_lang')]['signature']) {
            $club->set('signature', $clubLangDetails[$club->get('default_lang')]['signature']);
        } else if (!$club->get('signature')) {
            $club->set('signature', $this->container->get('translator')->trans('CL_SIG_DEF') . PHP_EOL . $club->get('title'));
        }

        if ($clubLangDetails[$club->get('default_lang')]['logo']) {
            $club->set('logo', $clubLangDetails[$club->get('default_lang')]['logo']);
        } else {
            //in mails when iterating through contacts, logo can be changed. so to reset the logo
            $club->set('logo', $clubLangDetails[$club->get('club_default_lang')]['logo']);
        }
        //Set the club clubHeirarchyDet parameter     
        $clubsHeirarchyDetails = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->getClubsValues($club->get('clubHeirarchy'), $club->get('default_lang'), $club->get('clubCacheKey'), $club->get('cacheLifeTime'), $club->get('caching_enabled'));
        foreach ($clubsHeirarchyDetails as $key => $clubsHeirarchy) {
            $clubHeirarchyDet[$clubsHeirarchy['id']] = array('title' => $clubsHeirarchy['title'], 'club_type' => $clubsHeirarchy['clubType']);
        }
        $club->set('clubHeirarchyDet', $clubHeirarchyDet);
    }
}
