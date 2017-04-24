<?php

namespace Internal\GeneralBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * For building document query in internal area
 */
class InternalDocumentList
{

    /**
     * Where condition
     * 
     * @var string 
     */
    private $where;

    /**
     * Club Id
     *
     * @var int 
     */
    private $clubId;

    /**
     * Clubtype federation/subfederation
     * 
     * @var string 
     */
    public $clubtype;

    /**
     * Container object
     * 
     * @var object 
     */
    private $container;

    /**
     * Club service
     * 
     * @var object 
     */
    private $club;

    /**
     * Contact service
     *
     * @var object 
     */
    private $contact;

    /**
     * Document type(TEAM/WORKGROUP/ALL)
     *
     * @var string 
     */
    private $documentType;

    /**
     * Club heirarchy
     * 
     * @var array 
     */
    public $clubHeirarchy;

    /**
     * Contact id
     * 
     * @var int 
     */
    public $contactId;

    /**
     * Admin team ids
     * 
     * @var array 
     */
    private $adminTeamIds = array();

    /**
     * Member team ids
     * 
     * @var array 
     */
    private $memberTeamIds = array();

    /**
     * Admin workgroup ids
     *
     * @var array 
     */
    private $adminWorkgroupIds = array();

    /**
     * Member workgroup ids
     *
     * @var array 
     */
    private $memberWorkgroupIds = array();

    /**
     * Club admin flag
     * 
     * @var int 
     */
    private $isClubAdmin = 0;

    /**
     * Group rights
     *
     * @var array 
     */
    private $groupRights = array();

    /**
     * Federation member flag
     * 
     * @var int 
     */
    private $isFedMember = 0;

    /**
     * Role id
     * 
     * @var string 
     */
    private $roleId = '';

    /**
     * Team member flag
     * 
     * @var int 
     */
    private $isTeamMember = 0;

    /**
     * Team admin flag
     * 
     * @var int 
     */
    private $isTeamAdmin = 0;

    /**
     * Workgroup member flag
     * 
     * @var int 
     */
    private $isWorkgroupMember = 0;

    /**
     * Workgroup admin flag
     * 
     * @var int 
     */
    private $isWorkgroupAdmin = 0;

    /**
     * Flag to identify from role tab count section
     * 
     * @var boolean 
     */
    private $isRoleTab = false;

    /**
     * Function to construct Internal document list object
     * 
     * @param ContainerInterface $container    Container object
     * @param string             $documentType Document type
     * @param int                $roleId       RoleId
     * @param boolean            $isRoleTab    To get role tab count or not
     */
    public function __construct(ContainerInterface $container, $documentType = 'ALL', $roleId = '', $isRoleTab = false)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get("id");
        $this->clubtype = $this->club->get("type");
        $this->where = '';
        $this->documentType = $documentType;
        $this->clubHeirarchy = $this->club->get('clubHeirarchy');
        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');
        $this->adminTeamIds = array();
        $this->memberTeamIds = array();
        $this->adminWorkgroupIds = array();
        $this->memberWorkgroupIds = array();
        $this->isClubAdmin = (in_array('clubAdmin', $this->contact->get('allowedModules'))) ? 1 : 0;
        $this->groupRights = $this->contact->get('clubRoleRightsGroupWise');
        $this->isFedMember = $this->contact->get('isFedMember');
        $this->roleId = $roleId;
        $this->isRoleTab = $isRoleTab;
        $this->setMyGroupRights();
    }

    /**
     * Function to set my team and workgroup group rights
     */
    private function setMyGroupRights()
    {
        if (count($this->groupRights) > 0) {
            $this->adminTeamIds = (isset($this->groupRights['ROLE_GROUP_ADMIN']['teams'])) ? $this->groupRights['ROLE_GROUP_ADMIN']['teams'] : array();
            if (isset($this->groupRights['ROLE_DOCUMENT_ADMIN']['teams'])) {
                $this->adminTeamIds = array_merge($this->adminTeamIds, $this->groupRights['ROLE_DOCUMENT_ADMIN']['teams']);
            }
            $this->adminWorkgroupIds = (isset($this->groupRights['ROLE_GROUP_ADMIN']['workgroups'])) ? $this->groupRights['ROLE_GROUP_ADMIN']['workgroups'] : array();
            if (isset($this->groupRights['ROLE_DOCUMENT_ADMIN']['workgroups'])) {
                $this->adminWorkgroupIds = array_merge($this->adminWorkgroupIds, $this->groupRights['ROLE_DOCUMENT_ADMIN']['workgroups']);
            }
            $this->memberTeamIds = (isset($this->groupRights['MEMBER']['teams'])) ? $this->groupRights['MEMBER']['teams'] : array();
            $this->memberWorkgroupIds = (isset($this->groupRights['MEMBER']['workgroups'])) ? $this->groupRights['MEMBER']['workgroups'] : array();
        }
        if ($this->isClubAdmin) {
            $this->adminTeamIds = array_keys($this->contact->get('teams'));
            $this->adminWorkgroupIds = array_keys($this->contact->get('workgroups'));
        }
        $this->isTeamMember = ($this->documentType == 'TEAM') ? ((in_array($this->roleId, $this->memberTeamIds)) ? 1 : 0) : ((count($this->memberTeamIds) > 0) ? 1 : 0);
        $this->isTeamAdmin = ($this->documentType == 'TEAM') ? ((in_array($this->roleId, $this->adminTeamIds)) ? 1 : $this->isClubAdmin) : ((count($this->adminTeamIds) > 0) ? 1 : 0);
        $this->isWorkgroupMember = ($this->documentType == 'WORKGROUP') ? ((in_array($this->roleId, $this->memberWorkgroupIds)) ? 1 : 0) : ((count($this->memberWorkgroupIds) > 0) ? 1 : 0);
        $this->isWorkgroupAdmin = ($this->documentType == 'WORKGROUP') ? ((in_array($this->roleId, $this->adminWorkgroupIds)) ? 1 : $this->isClubAdmin) : ((count($this->adminWorkgroupIds) > 0) ? 1 : 0);
    }

    /**
     * Function to get team admin condition
     * 
     * @return string
     */
    private function getTeamAdminCondition()
    {
        if ($this->roleId != '') {
            $teamCondition = "(fdd.deposited_with = 'ALL' AND fdd.visible_for != 'club_contact_admin') ";
            $teamCondition .= "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for != 'club_contact_admin' AND fda.role_id = " . $this->roleId . ")";
        } else {
            $teamCondition = ($this->isRoleTab) ? "(fdd.deposited_with = 'ALL' AND fdd.visible_for != 'club_contact_admin' AND frm.id IN (" . implode(',', $this->adminTeamIds) . ")) " : "(fdd.deposited_with = 'ALL' AND fdd.visible_for != 'club_contact_admin') ";
            $teamCondition .= (count($this->adminTeamIds) > 0) ? "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for != 'club_contact_admin' AND fda.role_id IN (" . implode(',', $this->adminTeamIds) . "))" : "0";
        }

        return $teamCondition;
    }

    /**
     * Function to get team member condition
     * 
     * @return string
     */
    private function getTeamMemberCondition()
    {
        if ($this->roleId != '') {
            $teamCondition = "(fdd.deposited_with = 'ALL' AND fdd.visible_for = 'team') ";
            $teamCondition .= "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for = 'team' AND fda.role_id = " . $this->roleId . ")";
        } else {
            $teamCondition = ($this->isRoleTab) ? "(fdd.deposited_with = 'ALL' AND fdd.visible_for = 'team' AND frm.id IN (" . implode(',', $this->memberTeamIds) . ")) " : "(fdd.deposited_with = 'ALL' AND fdd.visible_for = 'team') ";
            $teamCondition .= (count($this->memberTeamIds) > 0) ? "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for = 'team' AND fda.role_id IN (" . implode(',', $this->memberTeamIds) . "))" : "0";
        }

        return $teamCondition;
    }

    /**
     * Function to get team function condition
     * 
     * @return string
     */
    private function getTeamFunctionCondition()
    {
        $clubTeamCatId = $this->club->get('club_team_id');
        if ($this->roleId != '') {
            $teamCondition .= "(IF((fdd.deposited_with = 'SELECTED'), fda.role_id = $this->roleId, 1) AND fdd.visible_for = 'team_functions' AND fdtf.function_id IN ("
                . "(SELECT crf.function_id from fg_rm_category_role_function crf LEFT JOIN fg_rm_role_contact rc ON (crf.id = rc.fg_rm_crf_id AND rc.assined_club_id = $this->clubId) WHERE crf.category_id = $clubTeamCatId AND IF((fdd.deposited_with = 'SELECTED'), crf.role_id = $this->roleId, 1) AND rc.contact_id = $this->contactId)))";
        } else {
            $teamCondition .= (count($this->memberTeamIds) > 0) ? "(IF((fdd.deposited_with = 'SELECTED'), fda.role_id IN (" . implode(',', $this->memberTeamIds) . "), 1) AND fdd.visible_for = 'team_functions' AND fdtf.function_id IN ("
                . "(SELECT crf.function_id from fg_rm_category_role_function crf LEFT JOIN fg_rm_role_contact rc ON (crf.id = rc.fg_rm_crf_id AND rc.assined_club_id = $this->clubId) WHERE crf.category_id = $clubTeamCatId AND IF((fdd.deposited_with = 'SELECTED'), crf.role_id = fda.role_id, 1) AND rc.contact_id = $this->contactId)))" : "0";
        }

        return $teamCondition;
    }

    /**
     * Function to get workgroup admin condition
     * 
     * @return string
     */
    private function getWorkgroupAdminCondition()
    {
        if ($this->roleId != '') {
            $workgroupCondition = "(fdd.deposited_with = 'ALL' AND fdd.visible_for != 'main_document_admin') ";
            $workgroupCondition .= "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for != 'main_document_admin' AND fda.role_id = $this->roleId)";
        } else {
            $workgroupCondition = ($this->isRoleTab) ? "(fdd.deposited_with = 'ALL' AND fdd.visible_for != 'main_document_admin' AND frm.id IN (" . implode(',', $this->adminWorkgroupIds) . ")) " : "(fdd.deposited_with = 'ALL' AND fdd.visible_for != 'main_document_admin') ";
            $workgroupCondition .= (count($this->adminWorkgroupIds) > 0) ? "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for != 'main_document_admin' AND fda.role_id IN (" . implode(',', $this->adminWorkgroupIds) . "))" : "0";
        }

        return $workgroupCondition;
    }

    /**
     * Function to get workgroup member condition
     * 
     * @return string
     */
    private function getWorkgroupMemberCondition()
    {
        if ($this->roleId != '') {
            $workgroupCondition = "(fdd.deposited_with = 'ALL' AND fdd.visible_for = 'workgroup') ";
            $workgroupCondition .= "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for = 'workgroup' AND fda.role_id = $this->roleId)";
        } else {
            $workgroupCondition = ($this->isRoleTab) ? "(fdd.deposited_with = 'ALL' AND fdd.visible_for = 'workgroup' AND frm.id IN (" . implode(',', $this->memberWorkgroupIds) . ")) " : "(fdd.deposited_with = 'ALL' AND fdd.visible_for = 'workgroup') ";
            $workgroupCondition .= (count($this->memberWorkgroupIds) > 0) ? "OR (fdd.deposited_with = 'SELECTED' AND fdd.visible_for = 'workgroup' AND fda.role_id IN (" . implode(',', $this->memberWorkgroupIds) . "))" : "0";
        }

        return $workgroupCondition;
    }

    /**
     * Function to get team documents condition
     * 
     * @return string
     */
    private function getTeamCondition()
    {
        if ($this->roleId != '') {
            $teamCondition = ($this->isTeamAdmin) ? $this->getTeamAdminCondition() : ($this->isTeamMember ? ($this->getTeamMemberCondition() . ' OR ' . $this->getTeamFunctionCondition()) : '0');
        } else {
            $teamCondition = '';
            if ($this->isTeamAdmin) {
                $teamCondition .= $this->getTeamAdminCondition();
            }
            if ($this->isTeamMember) {
                $teamCondition .= ($teamCondition != '') ? ' OR ' : '';
                $teamCondition .= $this->getTeamMemberCondition() . ' OR ';
                $teamCondition .= $this->getTeamFunctionCondition();
            }
            $teamCondition = ($teamCondition != '') ? $teamCondition : '0';
        }

        return "(" . $teamCondition . ")";
    }

    /**
     * Function to get workgroup documents condition
     * 
     * @return string
     */
    private function getWorkgroupCondition()
    {
        if ($this->roleId != '') {
            $workgroupCondition = ($this->isWorkgroupAdmin) ? $this->getWorkgroupAdminCondition() : ($this->isWorkgroupMember ? $this->getWorkgroupMemberCondition() : '0');
        } else {
            $workgroupCondition = '';
            if ($this->isWorkgroupAdmin) {
                $workgroupCondition = $this->getWorkgroupAdminCondition();
            }
            if ($this->isWorkgroupMember) {
                $workgroupCondition .= ($workgroupCondition != '') ? ' OR ' : '';
                $workgroupCondition .= $this->getWorkgroupMemberCondition();
            }
            $workgroupCondition = ($workgroupCondition != '') ? $workgroupCondition : '0';
        }

        return "(" . $workgroupCondition . ")";
    }

    /**
     * Function to get club documents condition
     * 
     * @return string $clubCondition
     */
    private function getClubCondition()
    {
        if ($this->isFedMember) {
            //possible club documents include those created by current club and its upper heirarchy only if a federation member.
            $clubHeirarchyArr = $this->clubHeirarchy;
            $clubHeirarchyArr[] = $this->clubId;
            $clubHeirarchyIds = implode(',', $clubHeirarchyArr);
        } else {
            $clubHeirarchyIds = $this->clubId;
        }
        $clubCondition = "((fdd.deposited_with = 'ALL' AND fdd.club_id NOT IN (select fdae2.club_id FROM fg_dm_assigment_exclude fdae2 where fdae2.document_id = fdd.id AND fdae2.club_id = $this->clubId)) "
            . "OR (fdd.deposited_with = 'SELECTED' AND fda.club_id = $this->clubId) OR (fdd.deposited_with = 'NONE' AND fdd.club_id = $this->clubId)) ";
        $clubCondition .= ($this->isFedMember) ? "AND fdd.club_id IN (" . $clubHeirarchyIds . ")" : "AND fdd.club_id = $this->clubId";

        return $clubCondition;
    }

    /**
     * Function to get personal overview where condition
     * 
     * @return string $this->where Personal overview where condition
     */
    public function getPersonalOverviewCondition()
    {
        $clubCondition = $this->getClubCondition();
        $teamCondition = $this->getTeamCondition();
        $workgroupCondition = $this->getWorkgroupCondition();

        $this->where = "CASE "
            . "WHEN fdd.document_type = 'CLUB' THEN "
            . "fdd.is_visible_to_contact = 1 AND $clubCondition "
            . "WHEN fdd.document_type = 'CONTACT' THEN "
            . "fdd.club_id = $this->clubId AND fda.contact_id = $this->contactId AND fdd.is_visible_to_contact = 1 AND fdd.id NOT IN (SELECT document_id FROM fg_dm_assigment_exclude WHERE contact_id = $this->contactId) "
            . "WHEN fdd.document_type = 'TEAM' THEN "
            . "fdd.club_id = $this->clubId AND fdd.deposited_with != 'NONE' AND $teamCondition "
            . "ELSE "
            . "fdd.club_id = $this->clubId AND fdd.deposited_with != 'NONE' AND $workgroupCondition "
            . "END ";

        return $this->where;
    }

    /**
     * Function to get team overview where condition
     * 
     * @return string $this->where Team overview where condition
     */
    public function getTeamOverviewCondition()
    {
        $teamCondition = $this->getTeamCondition();
        $this->where = "fdd.club_id = $this->clubId AND fdd.deposited_with != 'NONE' AND fdd.document_type = 'TEAM' AND $teamCondition ";

        return $this->where;
    }

    /**
     * Function to get workgroup overview where condition
     * 
     * @return string $this->where Workgroup overview where condition
     */
    public function getWorkgroupOverviewCondition()
    {
        $workgroupCondition = $this->getWorkgroupCondition();
        $this->where = "fdd.club_id = $this->clubId AND fdd.deposited_with != 'NONE' AND fdd.document_type = 'WORKGROUP' AND $workgroupCondition ";

        return $this->where;
    }

    /**
     * Function to get my admin role ids
     * 
     * @param string $roleType team/workgroup
     * 
     * @return array $myRoles Role ids
     */
    public function getMyAdminRoleIds($roleType = 'WORKGROUP')
    {
        $myRoles = ($roleType == 'TEAM') ? $this->adminTeamIds : $this->adminWorkgroupIds;

        return $myRoles;
    }

    /**
     * Function to get my member role ids
     * 
     * @param string $roleType team/workgroup
     * 
     * @return array $myRoles Role ids
     */
    public function getMyMemberRoleIds($roleType = 'WORKGROUP')
    {
        $myRoles = ($roleType == 'TEAM') ? $this->memberTeamIds : $this->memberWorkgroupIds;

        return $myRoles;
    }
}
