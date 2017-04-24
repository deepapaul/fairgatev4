<?php

namespace Internal\TeamBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * TeamActionmenuController.
 *
 * This class is used for handle the action menu functionalies
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class TeamActionmenuController extends FgController
{

    /**
     * To handle the add non existing contacts to corresponding functions.
     *
     * @param Request $request Request object
     * @param int    $roleId   roleid
     * @param string $roleType team/workgroup
     *
     * @return type
     */
    public function addTeammemberAction($roleId, $roleType)
    {
        $contactId = $this->container->get('contact')->get('id');
        $clubType = $this->container->get('club')->get('type');
        $categoryId = ($roleType == 'team') ? $this->container->get('club')->get('club_team_id') : $this->container->get('club')->get('club_workgroup_id');
        $assignedItems = ($roleType == 'team') ? $this->container->get('contact')->get('teams') : $this->container->get('contact')->get('workgroups');
        $assigneditemName = $assignedItems[$roleId];
        //collect the all function related to corrsponding team
        $clubIdArray = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subFederationId, 'clubType' => $this->clubType);
        $getAllCategoryRoleFunction = $this->em->getRepository('CommonUtilityBundle:FgRmCategory')->getAllCategoryRoleFunctionAssignment($this->conn, $clubIdArray, $this->clubDefaultLang);
        $roleObj = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->find($roleId);
        $executive = 0 ;
        if($roleObj->getIsExecutiveBoard() && ($roleType=='workgroup')){
            $executive = 1;
        }
       
        return $this->render('InternalTeamBundle:Roleassign:teamAdd.html.twig', array('itemName' => $assigneditemName, 'roleId' => $roleId, 'contactId' => $contactId, 'categoryId' => $categoryId,'clubType'=>$clubType, 'type' => $roleType,'executive'=>$executive, 'resultArray' => json_encode($getAllCategoryRoleFunction)));
    }

    /**
     * To get non existing contact names.
     *
     * @param int $roleId roleId
     *
     * @return JsonResponse
     */
    public function getNonmemberContactAction(Request $request, $roleId)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        $clubType = $this->container->get('club')->get('type');
        $where[] = 'dcrf.role_id =' . $roleId;
        $where[] = "dcrf.category_id = '" . $this->container->get('club')->get('club_team_id') . "'";
        $passedColumns = ' contactname(C.id) AS title';
        $exclude = '(SELECT drc.contact_id FROM fg_rm_role AS drr INNER JOIN fg_rm_category_role_function AS dcrf ON drr.id = dcrf.role_id  INNER JOIN fg_rm_role_contact AS drc ON dcrf.id=drc.fg_rm_crf_id WHERE (' . implode(' AND ', $where) . '))';
        $recipientList = $em->getRepository('CommonUtilityBundle:FgCmContact')
            ->getAutocompleteContacts($exclude, 2, '', $this->container, $clubId, $clubType, $passedColumns, $request->get('term'), true, 0, 0);

        return new JsonResponse($recipientList);
    }

    /**
     * To save the non existing contact to corresponding table.
     *
     * @return JsonResponse
     */
    public function savenonexistingContactAction()
    {
        $insertArray['userId'] = $this->container->get('contact')->get('id');
        $insertArray['clubId'] = $this->container->get('club')->get('id');
        $this->em->getRepository('CommonUtilityBundle:FgCmChangeToconfirm')->addNonExistingContact($insertArray, $this->request);
        $flashmessage = array('status' => true, 'flash' => $this->get('translator')->trans('ADD_SUCCESS'), 'noparentload' => true);

        return new JsonResponse($flashmessage);
    }
}
