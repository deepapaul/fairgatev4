<?php

namespace Common\UtilityBundle\Repository\Calendar;

use Doctrine\ORM\EntityRepository;

/**
 * FgEmCalendarRepository
 *
 * @author pitsolutions
 */
class FgEmCalendarRepository extends EntityRepository {

    public function getRoleDetails( $container) {
        $clubId = $container->get('club')->get('id');
        $$clubDefaultLang = $container->get('club')->get('default_system_lang');
        $joinCondition = $this->getCluDetails();
        $roleDetails = $this->createQueryBuilder('c')
                ->select('r.id as roleId, r.type as roleType')
                ->leftJoin('CommonUtilityBundle:FgEmCalendarSelectedAreas', 'ca', 'WITH', 'c.id=ca.calendar')
                ->leftJoin('CommonUtilityBundle:FgRmRole', 'rr', 'WITH', 'rr.id=ca.role')
                ->leftJoin('CommonUtilityBundle:FgRmCategory', 'rc', 'WITH', 'rr.category=rc.id')
                ->leftJoin('CommonUtilityBundle:FgRmRolei18n', 'rri18n', 'WITH', 'rr.id=rri18n.id')
                ->where('c.club=:clubId')
                ->andWhere('cci18n.lang=:language')
                ->orWhere('c.shareWithLower=1 AND ' . $joinCondition)
                ->setParameter('language', $clubDefaultLang)
                ->setParameter('clubId', $clubId);
        $dataResult = $roleDetails->getQuery()->getResult();
        return $dataResult;
    }

    public function getCategoryDetails($container) {
        $$clubDefaultLang = $container->get('club')->get('default_system_lang');
        $clubId = $container->get('club')->get('id');
        $joinCondition = $this->getCluDetails();
        $categoryDetails = $this->createQueryBuilder('c')
                ->select("cc.id as categoryId, (CASE WHEN (cci18n.titleLang IS NULL OR cci18n.titleLang = '') THEN cc.title ELSE cci18n.titleLang END) AS categoryTitle")
                ->leftJoin('CommonUtilityBundle:FgEmCalendarSelectedCategories', 'sc', 'WITH', 'c.id=sc.calendar')
                ->leftJoin('CommonUtilityBundle:FgEmCalendarCategory', 'cc', 'WITH', 'cc.id=sc.category')
                ->leftJoin('CommonUtilityBundle:FgEmCalendarCategoryi18n', 'cci18n', 'WITH', 'cc.id=rri18n.id')
                ->where('c.club=:clubId')
                ->andWhere('cci18n.lang=:language')
                ->orWhere('c.shareWithLower=1 AND ' . $joinCondition)
                ->setParameter('language', $clubDefaultLang)
                ->setParameter('clubId', $clubId);

        $dataResult = $categoryDetails->getQuery()->getResult();
        return $dataResult;
    }

    private function getCluDetails($container) {

        $clubType = $container->get('club')->get("type");
        $joinCondition = '';
        $fedId = $container->get('club')->get("federation_id");
        $subfedId = $container->get('club')->get("sub_federation_id");
        switch ($clubType) {
            case 'federation_club':
                $joinCondition = "c.club=" . $fedId;
                break;
            case 'sub_federation':
                $joinCondition = "c.club=" . $fedId;
                break;
            case 'sub_federation_club':
                $joinCondition = "(c.club=" . $subfedId . " OR c.club=" . $fedId . ")";
                break;
        }

        return $joinCondition;
    }

    /**
     * This function is to get my club, teams, workgroup to be listed in edit/import/multiedit areas dropdown
     * 
     * @param object $container Container object
     * 
     * @return array $myAreas
     */
    public function getMyClubAndTeamsAndWorkgroups($container)
    {
        $club = $container->get('club');
        $myAreas = array();
        $contact = $container->get('contact');
        
        $allTeams = $contact->get('teams');
        $allWorkgroups = $contact->get('workgroups');
                
        $isCluborSuperAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0 ;
        $isClubCalendarAdmin = in_array('ROLE_CALENDAR', $contact->get('allRights')) ? 1 : 0;

        if ($isCluborSuperAdmin || $isClubCalendarAdmin) {
            $myAreas['club'] = ucfirst($club->get('title'));
            if ($isCluborSuperAdmin) {
                $myAreas['teams'] = $allTeams;
                $myAreas['workgroups'] = $allWorkgroups;
            } else {
                $workgroupCatId = $club->get('club_workgroup_id');
                $teamCatId = $club->get('club_team_id');
                $assignedRoles = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($container, array($teamCatId, $workgroupCatId));
                $myAreas['teams'] = $assignedRoles['teams'];
                $myAreas['workgroups'] = $assignedRoles['workgroups'];
            }
        } else {
            $myTeams = $myWorkgroups = $assignedTeams = $assignedWorkgroups = array();
            $groupRights = $contact->get('clubRoleRightsGroupWise');
            if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
                $myTeams = array_merge($myTeams, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            }
            if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
                $myWorkgroups = array_merge($myWorkgroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            }
            if (isset($groupRights['ROLE_CALENDAR_ADMIN']['teams'])) {
                $myTeams = array_merge($myTeams, $groupRights['ROLE_CALENDAR_ADMIN']['teams']);
            }
            if (isset($groupRights['ROLE_CALENDAR_ADMIN']['workgroups'])) {
                $myWorkgroups = array_merge($myWorkgroups, $groupRights['ROLE_CALENDAR_ADMIN']['workgroups']);
            }
            foreach ($myTeams as $val) {
                $assignedTeams[$val] = $allTeams[$val];
            }
            $myAreas['teams'] = $assignedTeams;

            foreach ($myWorkgroups as $val) {
                $assignedWorkgroups[$val] = $allWorkgroups[$val];
            }
            $myAreas['workgroups'] = $assignedWorkgroups;
        }

        return $myAreas;
    }
    
    /**
     * This function is used to check whether the logged in contact has edit rights for this event
     * 
     * @param object $container Container object
     * @param int    $clubId    Club Id of event
     * @param int    $isClub    Is Club selected in areas
     * @param array  $roleIds   Roles selected in areas
     * 
     * @return int $hasEditRights 0/1
     */
    public function checkHasEditRights($container, $clubId, $isClub = 0, $roleIds = array())
    {
        $hasEditRights = 0;
        if ($clubId == $container->get('club')->get('id')) {
            $myAreas = $this->getMyClubAndTeamsAndWorkgroups($container);
            $hasEditRights = (array_key_exists('club', $myAreas)) ? 1 : 0;  
            if (!$hasEditRights) {
                if ($isClub) {
                    $hasEditRights = (array_key_exists('club', $myAreas)) ? 1 : 0; 
                } else {
                    if (count($roleIds) > 0) {
                        $myRoleIds = (count($myAreas['teams']) > 0) ? array_keys($myAreas['teams']) : array();
                        $myRoleIds = (count($myAreas['workgroups']) > 0) ? array_merge($myRoleIds, array_keys($myAreas['workgroups'])) : $myRoleIds;
                        $myRoleIdsWithinScope = array_intersect($roleIds, $myRoleIds);
                        $hasEditRights = (count($myRoleIdsWithinScope) == count($roleIds)) ? 1 : 0;
                    }
                }
            }
        }

        return $hasEditRights;
    }
    
    /**
     * This function is used to get the count of events without area in a particular club
     * 
     * @param int $clubId ClubId
     * 
     * @return array Count Array
     */
    public function getCountOfEventsWithoutArea($clubId)
    {
        $calendarQry = $this->createQueryBuilder('C')
                ->select('COUNT(DISTINCT C.id) as eventsWithoutArea')
                ->innerJoin('CommonUtilityBundle:FgEmCalendarDetails', 'CD', 'WITH', 'CD.calendar = C.id AND CD.status != 2 ')
                ->leftJoin('CommonUtilityBundle:FgEmCalendarSelectedAreas', 'CSA', 'WITH', 'CSA.calendarDetails = CD.id')
                ->where('C.club=:clubId')
                ->andWhere('CSA.role IS NULL')
                ->andWhere('CSA.isClub = 0 OR CSA.isClub IS NULL')
                ->setParameter('clubId', $clubId);
        $result = $calendarQry->getQuery()->getResult();

        return $result[0];
    }
    
    /**
     * delete calendar obj if no entries in detail table
     * 
     * @param obj $event
     * 
     * @return boolean
     */
    public function deleteCalendarWithNoDetail($event){
        $qry = $this->createQueryBuilder('c')
                    ->select('cd.id as id')
                    ->leftJoin('CommonUtilityBundle:FgEmCalendarDetails', 'cd', 'WITH', '(c.id = cd.calendar)')
                    ->where('c.id = :eventId')
                    ->setParameter('eventId', $event->eventId);
        $result = $qry->getQuery()->getResult();
        if(!$result[0]['id']){ //if id is NULL ie; no entries in dtail table
            $calendarObj = $this->find($event->eventId);
            if($calendarObj) {
                $this->_em->remove($calendarObj);
                $this->_em->flush();
            }
        }
       
      return true ; 
    }
    /**
     * Function to check calendar visibility in a club
     * 
     * @param obj $container container
     * 
     * @return boolean
     */
    public function checkCalendarVisibility($container)
    {
        $clubType = $container->get('club')->get("type");
        $clubId = $container->get('club')->get("id");
        $qry = $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as eventIds')
            ->where('c.club = ' . $clubId);
        if ($clubType != 'federation' && $clubType != 'standard_club') {
            $clubHeirarchy = implode(',', $container->get('club')->get('clubHeirarchy'));
            $joinCondition = 'c.club IN (' . $clubHeirarchy . ') AND c.shareWithLower= 1';
            $qry->orWhere($joinCondition);
        }
        $result = $qry->getQuery()->getArrayResult();

        return $result[0]['eventIds'];
    }

    /**
     * Function to check shared events count of a club
     * 
     * @param int $clubId club id
     * 
     * @return int shared events count
     */
    public function checkForSharedEvents($clubId)
    {

        $qry = $this->createQueryBuilder('c')
            ->select('COUNT(c.id) as eventIds')
            ->where('c.club = ' . $clubId . ' AND c.shareWithLower=1');
        $result = $qry->getQuery()->getArrayResult();

        return $result[0]['eventIds'];
    }
}
