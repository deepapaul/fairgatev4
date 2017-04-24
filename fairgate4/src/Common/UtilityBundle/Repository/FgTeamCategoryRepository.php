<?php

/**
 * FgTeamCategoryRepository
 *
 * This class is used for team category in role management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgTeamCategoryRepository
 *
 * This class is used for listing, adding, editing, deleting team category in role management.
 */
class FgTeamCategoryRepository extends EntityRepository {

    /**
     * Function to list the Team categories.
     *
     * @param int $clubId             The club id
     * @param int $catId              The category id
     * @param boolean $isExec         Flag to know whether the query should be executed
     * @param boolean $catSettings    Flag to know the function called from category or settings page
     * @param int $clubteamid         The club team id from fg_rm_category
     * @param boolean $getTitleDetail Flag to know whether to get title from i18
     *
     * @return array/string $result/$resultQry Returns array of team category details or query string depending on $isExec.
     */
    public function getTeamCatDetails($clubId, $catId = 0, $isExec = false, $catSettings = false, $clubteamid = 0, $getTitleDetail = false) {
        $clubId = intval($clubId);
        $clubteamid = intval($clubteamid);
        $orderBy = '';
        if ($catSettings) {
            $select = 'c.id, (c.title)title, c.sortOrder, ci18.titleLang, ci18.lang, rf.id as fun_id, rf.title as fun_title, rf.isVisible as fun_visible, fi18.titleLang as fun_titleLang, rf.sortOrder as fun_sortOrder, fi18.lang as fun_lang, rc.id as fd';
            $orderBy = ',rf.sortOrder';
        } else {
            if ($getTitleDetail) {
                $select = 'c.id, (c.title)title, c.sortOrder, ci18.titleLang, ci18.lang';
            } else {
                $select = 'c.id, (c.title)title, c.sortOrder';
            }
        }
        $resultQuery = $this->createQueryBuilder('c')
                ->select($select);

        if (!$catSettings) {
            $resultQuery = $resultQuery->addSelect("(SELECT COUNT(rr.id) FROM CommonUtilityBundle:FgRmRole rr WHERE rr.club = $clubId AND rr.teamCategory = c.id AND rr.type = 'T' )team_count");
        }
        $resultQuery = $resultQuery->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', "r.teamCategory = c.id  ");
        $resultQuery = $resultQuery->leftJoin('CommonUtilityBundle:FgTeamCategoryI18n', 'ci18', 'WITH', 'ci18.id = c.id');

        if ($catSettings) {
            $resultQuery = $resultQuery->leftJoin('CommonUtilityBundle:FgRmCategory ', 'rc', 'WITH', 'rc.id = r.category and rc.isTeam = 1');
            $resultQuery = $resultQuery->leftJoin('CommonUtilityBundle:FgRmFunction ', 'rf', 'WITH', 'rf.category = ' . $clubteamid);
            $resultQuery = $resultQuery->leftJoin('CommonUtilityBundle:FgRmFunctionI18n', 'fi18', 'WITH', 'fi18.id = rf.id');
        }
        $resultQuery = $resultQuery->where('c.club=:clubId');

        if ($catId > 0) {
            $resultQuery = $resultQuery->andWhere('c.id=:CategoryId');
        }
        $resultQuery = $resultQuery->setParameter('clubId', $clubId);

        if ($catId > 0) {
            $resultQuery = $resultQuery->setParameter('CategoryId', $catId);
        }
        $resultQuery = $resultQuery->orderBy("c.sortOrder $orderBy");

        if (!$catSettings) {
            if ($getTitleDetail) {
                $resultQuery = $resultQuery->groupBy('c.id,ci18.lang');
            } else {
                $resultQuery = $resultQuery->groupBy('c.id');
            }
        }

        $resultQry = $resultQuery->getQuery()->getArrayResult();

        if ($isExec) {
            $result = array();
            $id = '';
            foreach ($resultQry as $key => $val) {
                if (count($val) > 0) {
                    if ($val['id'] == $id) {
                        $result[$id]['titleLang'][$val['lang']] = $val['titleLang'];
                    } else {
                        $id = $val['id'];
                        $result[$id] = array('id' => $val['id'], 'title' => $val['title'], 'sortOrder' => $val['sortOrder'], 'team_count' => $val['team_count']);
                        $result[$id]['titleLang'][$val['lang']] = $val['titleLang'];
                    }
                    if ($val['fun_id']) {
                        $result['functions'][] = $val['fun_title'];
                        $result['team_functions'][$val['fun_id']]['f_id'] = $val['fun_id'];
                        $result['team_functions'][$val['fun_id']]['f_title'] = $val['fun_title'];
                        $result['team_functions'][$val['fun_id']]['f_visible'] = $val['fun_visible'];
                        $result['team_functions'][$val['fun_id']]['f_titleLang'][$val['fun_lang']] = $val['fun_titleLang'];
                        $result['team_functions'][$val['fun_id']]['f_sortOrder'] = $val['fun_sortOrder'];
                    }
                }
            }

            return $result;
        } else {

            return $resultQry;
        }
    }

    /**
     * Function get all teams based on club id.
     *
     * @param int $clubId     Club id
     * @param int $clubTeamId Club team id
     *
     * @return array $result Array of teams.
     */
    public function getAllTeam($clubId, $clubTeamId) {
        $clubId = intval($clubId);
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('getClubRoleCount', 'Common\UtilityBundle\Extensions\RoleCount');

        $roleCategory = $this->createQueryBuilder('tc')
                ->select("tc.title as teamCategoryTitle,getClubRoleCount(r.id, $clubId) as rolecount,tc.id as teamCatId,tc.sortOrder as catSortOrder,r.title as teamTitle,r.id as teamId,IDENTITY(bm.contact) as bookmarked")
                ->addSelect('(SELECT count(cf.id) FROM CommonUtilityBundle:FgRmFunction cf WHERE cf.category=:clubTeamId AND cf.isActive = 1) fnCount')
                ->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', 'r.teamCategory = tc.id')
                ->leftJoin('CommonUtilityBundle:FgCmBookmarks', 'bm', 'WITH', 'bm.role = r.id AND bm.club=:clubId')
                ->Where('tc.club=:clubId')
                ->orderBy('tc.sortOrder, r.sortOrder')
                ->setParameters(array('clubId' => $clubId, 'clubTeamId' => $clubTeamId));

        $result = $roleCategory->getQuery()->getResult();

        return $result;
    }

    /**
     * Function get all teams count details based on club id.
     *
     * @param int $clubId     Club id
     * @param int $clubTeamId Club team id
     *
     * @return array $result Array of teams.
     */
    public function getAllTeamCountDetails($clubId, $clubTeamId) {
        $clubId = intval($clubId);
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('getClubRoleCount', 'Common\UtilityBundle\Extensions\RoleCount');

        $roleCategory = $this->createQueryBuilder('tc')
                ->select("getClubRoleCount(r.id, $clubId) as rolecount,tc.id as roleCatId,r.id as roleId")
                ->addSelect('(SELECT count(cf.id) FROM CommonUtilityBundle:FgRmFunction cf WHERE cf.category=:clubTeamId AND cf.isActive = 1) fnCount')
                ->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', 'r.teamCategory = tc.id')
                ->Where('tc.club=:clubId')
                ->orderBy('tc.sortOrder, r.sortOrder')
                ->setParameters(array('clubId' => $clubId, 'clubTeamId' => $clubTeamId));

        $result = $roleCategory->getQuery()->getResult();

        return $result;
    }

}
