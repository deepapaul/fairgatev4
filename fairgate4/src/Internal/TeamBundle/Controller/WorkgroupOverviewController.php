<?php

namespace Internal\TeamBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Common\UtilityBundle\Util\FgPermissions;

class WorkgroupOverviewController extends FgController
{

    /**
     * Function to handle team overview page display.
     *
     * @return template
     */
    public function workgroupOverviewAction()
    {
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'memberlist', 'type' => 'workgroups');
        $allowedTabs = $permissionObj->checkAreaAccess($accessCheckArray);

        return $this->render('InternalTeamBundle:TeamOverview:teamOverview.html.twig', array('clubId' => $this->clubId, 'contactId' => $this->contactId, 'tabs' => $allowedTabs, 'teamCount' => count($allowedTabs), 'type' => 'workgroup', 'url' => $this->generateUrl('get_team_overview_content', array('id' => 'dummyId', 'type' => 'workgroup'))));
    }

    /**
     * Action to handle display details of workfroup.
     *
     * @return template
     */
    public function workgroupDetailOverviewAction()
    {
        $permissionObj = new FgPermissions($this->container);
        $accessCheckArray = array('from' => 'memberlist', 'type' => 'workgroups');
        $allowedTabs = $permissionObj->checkAreaAccess($accessCheckArray);

        $contactId = $this->container->get('contact')->get('id');
        $workgroupId = $this->container->get('club')->get('club_workgroup_id');
        $teamId = $this->container->get('club')->get('club_team_id');
        $defaultColumns = $this->container->getParameter('default_team_table_settings');
        $corrAddrCatId = $this->container->getParameter('system_category_address');
        $invAddrCatId = $this->container->getParameter('system_category_invoice');
        $corrAddrFieldIds = $invAddrFieldIds = array();
        $contactFields = $this->container->get('club')->get('contactFields');
        $columnsUrl = $this->generateUrl('get_workgroup_member_columnsettings');
        foreach ($contactFields as $contactField) {
            if ($contactField['catId'] == $corrAddrCatId) {
                $corrAddrFieldIds[] = $contactField['id'];
            } elseif ($contactField['catId'] == $invAddrCatId) {
                $invAddrFieldIds[] = $contactField['id'];
            }
        }

        return $this->render('InternalTeamBundle:TeamOverview:teamdetailOverview.html.twig', array('contactId' => $contactId, 'tabs' => $allowedTabs, 'teamCount' => count($allowedTabs), 'type' => 'workgroup', 'url' => $this->generateUrl('get_member_data', array('memberId' => 'dummyId', 'memberCategory' => 'workgroup')), 'clubteamId' => $teamId, 'clubworkgroupId' => $workgroupId, 'clubId' => $this->clubId, 'defaultSetting' => $defaultColumns, 'corrAddrFieldIds' => $corrAddrFieldIds, 'invAddrFieldIds' => $invAddrFieldIds, 'columnsUrl' => $columnsUrl));
    }
}
