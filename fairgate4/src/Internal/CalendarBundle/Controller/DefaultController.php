<?php

namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Util\FgUtility;
use Internal\CalendarBundle\Util\CalendarFunctions;

class DefaultController extends Controller
{
    /**
     * Function to view Calendar.
     *
     * @return template
     */
    public function calendarViewAction()
    {
        $calendarFunctions = new CalendarFunctions($this->container);
        $club = $this->get('club');
        $clubDefaultLang = $club->get('club_default_lang');
        $defaultSysLang = $club->get('default_system_lang');
        //array of possible user rights
        $userRights = array('ROLE_GROUP', 'ROLE_CALENDAR', 'ROLE_CALENDAR_ADMIN', 'ROLE_GROUP_ADMIN', 'ROLE_USERS');
        //get all user rights of current user
        $allRights = $this->container->get('contact')->get('availableUserRights');
        $roleadminFlag = 0;
        //check if the user has team admin rights privilage
        if (in_array('ROLE_GROUP_ADMIN', $allRights)) {
            $roleadminFlag = 1;
        }

        $userrightsIntersect = array_intersect($userRights, $allRights);
        $adminFlag = 0;
        //check the user has superadmin privilage
        $isAdmin = ($this->container->get('contact')->get('isSuperAdmin') || (($this->container->get('contact')->get('isFedAdmin')) && ($this->container->get('club')->get('type') != 'federation'))) ? 1 : 0;
        if (count($userrightsIntersect) > 0 || $isAdmin == 1) {
            $adminFlag = 1;
        }
        $sidebarActionRights = 0;
        //check the user can show the filter action menus
        if (in_array('ROLE_USERS', $allRights) || in_array('ROLE_CALENDAR', $allRights) || $isAdmin == 1) {
            $sidebarActionRights = 1;
        }
        //set main action menu of the logged user
        $calendarImportEventsUrl = $this->generateUrl('calendar_import_events');
        $actionMenu = array('active' => $calendarFunctions->getActionMenu($calendarImportEventsUrl, $adminFlag));
        $breadCrumb = array('breadcrumb_data' => array());

        $clubHeirarchy = $this->container->get('club')->get('clubHeirarchyDet');
        $clubTitles = array();
        //set the club heirarchy
        foreach ($clubHeirarchy as $clubId => $clubArr) {
            $clubTitles[$clubId]['title'] = ucfirst($clubArr['title']);
            $clubTitles[$clubId]['clubType'] = $clubArr['club_type'];
        }
        $clubTitles[$this->container->get('club')->get('id')]['title'] = ucfirst($club->get('title'));
        $clubTitles[$this->container->get('club')->get('id')]['clubType'] = 'club';
        $sidebarOptions = $calendarFunctions->getSidebarOptions($this->getDoctrine()->getManager());
        $clubLogoUrl = FgUtility::getClubLogo('#dummy#', $this->getDoctrine()->getManager());
        $currentClubType = $this->container->get('club')->get('type');
        $areaExist = (count($sidebarOptions['general']) > 0 || ($sidebarOptions['eventsWithoutArea'] > 0) ) ? 1 : 0;
        $categoryExist = (count($sidebarOptions['category']) > 0) ? 1 : 0;
        $returnArray = array('actionMenu' => json_encode($actionMenu), 'breadCrumb' => $breadCrumb, 'adminFlag' => $adminFlag, 'sidebaractionMenuFlag' => $sidebarActionRights, 'roleadminFlag' => $roleadminFlag, 'clubDefaultLang' => $clubDefaultLang, 'defaultSysLang' => $defaultSysLang, 'clubTitles' => $clubTitles, 'clubLogoUrl' => $clubLogoUrl, 'sidebarOptions' => json_encode($sidebarOptions), 'clubType' => $currentClubType, 'contactId' => $this->container->get('contact')->get('id'), 'clubId' => $this->container->get('club')->get('id'), 'areaExist' => $areaExist, 'categoryExist' => $categoryExist);

        return $this->render('InternalCalendarBundle:Default:calendar.html.twig', $returnArray);
    }
}
