<?php

/**
 * AreaColoursController.
 *
 * This controller used for managing the area colors page for internal calendar area
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AreaColoursController extends Controller
{

    /**
     * Function is used for showing area colours page also edit and create colors.
     *
     * @return template
     */
    public function indexAction()
    {
        $contact = $this->get('contact');
        $teams = $contact->get('teams');
        $workgroups = $contact->get('workgroups');
        $club = $this->get('club');
        $data = array('teamId' => $club->get('club_team_id'),'workgroupId' => $club->get('club_workgroup_id'), 'clubId' => $club->get('id'), 'defaultColorCodeFedLevel' => $this->container->getParameter('defaultColorFedLevel'), 'defaultColorCodeClub' => $this->container->getParameter('defaultColorClubOrRole'), 'clubType' => $club->get('type'), 'clubTitle' => $club->get('title'), 'fedId' => $club->get('federation_id'), 'subFedId' => $club->get('sub_federation_id'));
        $isClubCalendarAdmin = in_array('ROLE_CALENDAR', $contact->get('availableUserRights')) ? 1 : 0;
        if ($isClubCalendarAdmin == 1) {
            $assignedRoles = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($data['teamId'], $data['workgroupId']));
        }
        $clubHer = $club->get('clubHeirarchyDet');
        foreach($clubHer as $key => $value){
            $data[$value['club_type']] = $value['title'];
        }
        $data['teams'] = ($isClubCalendarAdmin == 1) ? $assignedRoles['teams'] : $teams;
        $data['workgroups'] = ($isClubCalendarAdmin == 1) ? $assignedRoles['workgroups'] : $workgroups;
        $data['breadCrumb'] = array('back' => $this->generateUrl('internal_calendar_view'));
        $data['colorCode'] = ($isClubCalendarAdmin == 1) ? $this->getTeamsandWorkgroupDatas($assignedRoles['teams'], $assignedRoles['workgroups']) : $this->getTeamsandWorkgroupDatas($teams, $workgroups);
        $data['subLevelColorCodes'] = $this->getsubLevelsColorCodeData($data['clubType'], $data['clubId'], $data['subFedId'], $data['fedId']);
        $data['categoryColourCodes'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmCategory')->getCategoryColorCodes(array($data['teamId'], $data['workgroupId']));

        return $this->render('InternalCalendarBundle:AreaColours:index.html.twig', $data);
    }

    /**
     * Method to get teams and workgroup color codes.
     *
     * @param int $teams      all teams accessible to logged in contact
     * @param int $workgroups all workgroups accessible to logged in contact
     *
     * @return array
     */
    public function getTeamsandWorkgroupDatas($teams, $workgroups)
    {
        $colourCodeDetails = array();
        $teamIds = array_keys($teams);
        $workgroupIds = array_keys($workgroups);
        $roleIds = array_merge($teamIds, $workgroupIds);
        if (!empty($roleIds)) {
            $colourCodeDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->getAllRolesColorCode($roleIds);
        }

        return $colourCodeDetails;
    }

    /**
     * Function is used for saving area colours data.
     *
     * @param Request $request Request object
     *
     * @return JsonResponse
     */
    public function saveColoursAction(Request $request)
    {
        $colourCodeArray = json_decode($request->request->get('colorArr'), true);
        $clubId = $this->get('club')->get('id');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgRmRole')->saveCalendarColourCodes($colourCodeArray, $clubId);

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('AREA_COLORS_SAVE_SUCCESS_TEXT'), 'sync' => 1));
    }

    /**
     * Method to get color codes for sub level clubs.
     *
     * @param string $clubType Current club type
     * @param int    $clubId   Current club id
     * @param int    $subFedId Sub federation Id
     * @param int    $fedId    Federation Id
     *
     * @return array
     */
    public function getsubLevelsColorCodeData($clubType, $clubId, $subFedId, $fedId)
    {
        switch ($clubType) {
            case'federation': case 'standard_club':
                $clubIdsArray = array($clubId);
                break;
            case 'sub_federation_club':
                $clubIdsArray = array($clubId, $subFedId, $fedId);
                break;
            case 'sub_federation': case 'federation_club':
                $clubIdsArray = array($clubId, $fedId);
                break;
        }
        $subLevelsColourCodeDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgClub')->getsubLevelsColorCode($clubIdsArray);

        return $subLevelsColourCodeDetails;
    }
}
