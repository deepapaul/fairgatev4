<?php

/**
 * FgRmFunctionRepository
 *
 * This class is used for functions in role management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgRmFunctionRepository
 *
 * This class is used for listing, adding, updating and removing functions in role management.
 */
class FgRmFunctionRepository extends EntityRepository {

    /**
     * Function to get Executive Board Functions
     *
     * @param int    $clubId     Club Id
     * @param int    $crntclubId Contact Club Id
     * @param string $clubType   Club Type
     *
     * @return array $dataResult Result array of executive board functions.
     */
    public function getExecutiveBoardFunctions($clubId, $crntclubId, $clubType) {
        $crntclubId = intval($crntclubId);
        $conn = $this->getEntityManager()->getConnection();
        $clubType = FgUtility::getSecuredData($clubType, $conn);
        $executiveboardFunctonCount = $this->getSubQueryForFunctionCount('', '', $clubType, 1, $crntclubId);
        $resultArray = $this->createQueryBuilder('f')
                ->select("f.id AS fn_id, c.id AS fn_categoryId, r.id AS fn_roleId, "
                    . "f.title AS fn_title, f.isActive AS fn_isActive, f.sortOrder AS fn_sortOrder, "
                    . "f.isRequiredAssignment AS fn_isRequiredAssignment, fi18n.titleLang as fn_titleLang, fi18n.lang as fn_lang")
                ->addSelect('(' . $executiveboardFunctonCount->getDQL() . ') as fnCount');
        $resultArray->leftJoin('CommonUtilityBundle:FgRmFunctionI18n', 'fi18n', 'WITH', 'fi18n.id = f.id')
                ->leftJoin('CommonUtilityBundle:FgRmCategory', 'c', 'WITH', 'c.id = f.category')
                ->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', 'r.category = c.id')
                ->leftJoin('CommonUtilityBundle:FgRmRoleFunction', 'rf', 'WITH', 'rf.role = r.id AND rf.function = f.id')
                ->where('c.club=:clubId')
                ->andWhere('f.isFederation=1')
                ->andWhere("c.contactAssign = 'manual'")
                ->setParameter('clubId', $clubId);
        if ($clubType !== 'federation') {
            $resultArray->setParameter('assignedClubId', $crntclubId);
        }
        
        $dataResult = $resultArray->getQuery()->getArrayResult();

        return $dataResult;
    }

    /**
     * Function to get all the function ids of a category.
     *
     * @param int $categoryId Category Id
     *
     * @return array $functionIdArr Array of function ids.
     */
    public function getFunctionIds($categoryId) {
        $roleFunctions = $this->createQueryBuilder('f')
                ->select('f.id as functionId')
                ->where('f.category=:categoryId')
                ->setParameter('categoryId', $categoryId);

        $dataResult = $roleFunctions->getQuery()->getResult();

        $functionIdArr = array();
        foreach ($dataResult as $key => $valArray) {
            $functionIdArr[] = $valArray['functionId'];
        }

        return $functionIdArr;
    }

    /**
     * Function to get Ids of Club Executive Board Functions
     *
     * @param int     $clubId   Club Id
     * @param object  $conn     Connection Object
     * @param boolean $getTitle Flag to check whether to return function title or not.
     *
     * @return array $resultArray Function ids array
     */
    public function getClubExecBoardFunctionIds($clubId, $conn = '', $getTitle = false) {
        $clubId = intval($clubId);
        $hasConnAlready = $conn ? true : false;
        if (!$hasConnAlready) {
            $conn = $this->getEntityManager()->getConnection();
        }

        $resultArr = $conn->fetchAll("SELECT GROUP_CONCAT(f.id) AS functionIds, GROUP_CONCAT(f.title SEPARATOR '*#FN_TITLE#*') AS functionTitles "
            . "FROM `fg_rm_function` f "
            . "LEFT JOIN `fg_rm_category` c ON (c.id = f.category_id) "
            . "WHERE c.club_id = $clubId AND f.is_federation = 1 AND c.contact_assign = 'manual'");

        if (!$hasConnAlready) {
            $conn->close();
        }
        $functionIds = count($resultArr) ? explode(',', $resultArr['0']['functionIds']) : array();

        if ($getTitle) {
            $functionTitles = count($resultArr) ? explode('*#FN_TITLE#*', $resultArr['0']['functionTitles']) : array();
            $resultArray = array_combine($functionIds, $functionTitles);
        } else {
            $resultArray = $functionIds;
        }

        return $resultArray;
    }

    /**
     * Function to get All Club Executive Board Functions.
     *
     * @param int    $clubId      Club Id
     * @param string $defaultLang Default Language
     *
     * @return array $clubExBoardArray Array of Club Executive Board Functions.
     */
    public function getAllClubExecBoardFunctions($clubId, $defaultLang) {
        $conn = $this->getEntityManager()->getConnection();
        $clubId = intval($clubId);
        $defaultLang = FgUtility::getSecuredData($defaultLang, $conn);

        $clubExecutiveBoardSql = "SELECT rmf.id,'ceb_function' as categoryId,'FI' as itemType, rmf.is_required_assignment AS isRequired,IF(rmfi18n.title_lang IS NULL OR rmfi18n.title_lang='', rmf.title, rmfi18n.title_lang) AS title FROM fg_rm_category AS rmc INNER JOIN fg_rm_function AS rmf ON rmf.category_id = rmc.id LEFT JOIN fg_rm_function_i18n AS rmfi18n ON rmf.id = rmfi18n.id AND rmfi18n.lang = '$defaultLang' WHERE rmc.club_id = '$clubId' AND rmf.is_federation=1 AND rmf.is_active=1 AND rmc.contact_assign = 'manual'";
        $clubExBoardArray = $conn->fetchAll($clubExecutiveBoardSql);

        return $clubExBoardArray;
    }

    /**
     * Function to get Contact Count of Executive Board Functions of a Club.
     *
     * @param int $catId        Category Id
     * @param int $roleId       Role Id
     * @param int $clubId       Club Id
     * @param int $federationId Federation Id
     *
     * @return array $resultArray Result array of contact count of Club Executive Board Functions.
     */
    public function getRequiredExecBoardFunctionsCount($catId, $roleId, $clubId, $federationId) {
        $conn = $this->getEntityManager()->getConnection();
        $functionIdsQry = "SELECT GROUP_CONCAT(f.`id`) AS funIds FROM `fg_rm_function` f WHERE f.is_required_assignment=1 "
                . "AND f.category_id=(SELECT c.`id` FROM `fg_rm_category` c WHERE c.`club_id`=:federationId AND c.`is_workgroup`=1)";
        $functions = $conn->fetchAll($functionIdsQry, array('federationId' => $federationId));
        $functionIds = $functions[0]['funIds'];

        $execFuncCountArray = array();
        if ($functionIds != '') {
            $countSql = "SELECT crf.function_id, COUNT(rc.contact_id) AS contCount "
                    . "FROM `fg_rm_category_role_function` crf "
                    . "LEFT JOIN `fg_rm_role_contact` rc ON (rc.fg_rm_crf_id=crf.id) "
                    . "WHERE crf.category_id=:catId AND crf.role_id=:roleId AND crf.function_id IN ($functionIds) AND crf.club_id=:clubId "
                    . "GROUP BY crf.function_id";
            $execFuncCountArray = $conn->fetchAll($countSql, array('catId' => $catId, 'roleId' => $roleId, 'clubId' => $clubId));
        }

        $execBoardFunctions = ($functionIds != '') ? explode(',', $functionIds) : array();
        $resultArray = array();
        foreach ($execFuncCountArray as $execFuncCount) {
            $resultArray[$execFuncCount['function_id']] = $execFuncCount['contCount'];
            unset($execBoardFunctions[array_search($execFuncCount['function_id'], $execBoardFunctions)]);
        }
        foreach ($execBoardFunctions as $execBoardFunction) {
            $resultArray[$execBoardFunction] = 0;
        }
        $conn->close();

        return $resultArray;
    }

    /**
     * Function to get Club Executive Board Function Details.
     *
     * @param int    $clubId             Club Id
     * @param string $defaultLang        Default language of Club
     * @param object $clubHeirarchyArray Array of Club Heirarchy Ids
     * @param object $getFedFunOnly      return only federation function
     *
     * @return array $resultArray Array of Club Executive Board Function Details.
     */
    public function getExecBoardFunctionDetailsOfClub($clubId, $defaultLang, $clubHeirarchyArray, $getFedFunOnly = false) {
        $conn = $this->getEntityManager()->getConnection();
        $clubId = intval($clubId);
        $defaultLang = FgUtility::getSecuredData($defaultLang, $conn);
        $clubHeirarchy = implode(',', $clubHeirarchyArray);
        $fedFunCond = $getFedFunOnly ? "AND rmf.is_federation=1" : "";

        $clubExecutiveBoardSql = "SELECT rmf.id, IF(rmfi18n.title_lang IS NULL OR rmfi18n.title_lang='', rmf.title, rmfi18n.title_lang) AS title, rmf.is_federation AS isFederation, rmf.is_required_assignment AS isRequiredAssignment, "
                . "GROUP_CONCAT(rc.`contact_id`) AS asignedContactIds, GROUP_CONCAT(contactNameYOB(rc.`contact_id`) SEPARATOR '#*#CONT_NAME#*#') AS asignedContactNames "
                . "FROM fg_rm_category AS rmc "
                . "INNER JOIN fg_rm_role AS rmr ON rmr.category_id = rmc.id "
                . "LEFT JOIN fg_rm_role_function AS rmrf ON rmrf.role_id = rmr.id "
                . "LEFT JOIN fg_rm_function AS rmf ON rmf.id = rmrf.function_id AND ((rmc.club_id =:clubId AND rmf.is_federation=0) OR (rmc.club_id !=:clubId AND rmr.is_executive_board=1 AND rmf.is_federation = 1)) "
                . "LEFT JOIN fg_rm_function_i18n AS rmfi18n ON rmf.id = rmfi18n.id AND rmfi18n.lang = :defaultLang "
                . "LEFT JOIN `fg_rm_category_role_function` crf ON (crf.function_id=rmf.id AND crf.club_id=:clubId) "
                . "LEFT JOIN `fg_rm_role_contact` rc ON (crf.id = rc.fg_rm_crf_id) "
                . "WHERE ((rmc.club_id =:clubId) " . ($clubHeirarchy != '' ? "OR (rmc.club_id IN ($clubHeirarchy) AND (rmc.club_id !=:clubId AND rmc.is_fed_category = 0 AND rmf.is_federation = 1)) " : '') . ") "
                . "AND rmr.is_executive_board=1 AND rmf.is_active=1 AND rmc.is_active=1 $fedFunCond "
                . "GROUP BY rmf.id "
                . "ORDER BY rmf.is_federation DESC, rmf.sort_order ASC";
        $resultArray = $conn->fetchAll($clubExecutiveBoardSql, array('clubId' => $clubId, 'defaultLang' => $defaultLang));

        return $resultArray;
    }

    /**
     * Function to create a subquery for calculating function count.
     * Can only be used inside a main query like mysql function
     *
     * @param int $catId          Category Id
     * @param int $roleId         Role Id
     * @param int $clubType       Type of the logged current club
     * @param int $executiveboard Is executiveboard
     * @param int $clubId         Current club id
     *
     * @return array $functonCount Returns the subquery
     */
    public function getSubQueryForFunctionCount($catId, $roleId, $clubType, $executiveboard, $clubId) {
        $functonCount = $this->getEntityManager()->createQueryBuilder();
        $functonCount->select('COUNT(DISTINCT rc.contact) as cntCnt')
                ->from('CommonUtilityBundle:FgRmRoleContact', 'rc')
                ->leftJoin('CommonUtilityBundle:FgRmCategoryRoleFunction', 'crf', 'WITH', 'crf.id = rc.fgRmCrf')
                ->leftJoin('CommonUtilityBundle:FgCmContact', 'cc', 'WITH', 'rc.contact = cc.id')
                ->where("crf.function = f.id");
        $catId ? $functonCount->andWhere("crf.category =:categoryId") : '';
        $roleId ? $functonCount->andWhere("crf.role = r.id") : '';
        if (!$executiveboard) {
            if ($clubType === 'federation' || $clubType === 'sub_federation') {
                $clubIds = $this->_em->getRepository('CommonUtilityBundle:FgRmFunction')->getSubLevelClubIds($executiveboard, $clubId);
                if ($clubIds) {
                     $functonCount->andWhere('cc.isFedMembershipConfirmed=1');
                    $functonCount->andWhere($functonCount->expr()->orX($functonCount->expr()->in('cc.club', $clubIds), $functonCount->expr()->eq('cc.club', ':clubId')));
                }
            } else {
                $functonCount->andWhere('cc.club=:clubId');
            }
        } else {
            if ($clubType === 'federation') {
                $clubIds = $this->_em->getRepository('CommonUtilityBundle:FgRmFunction')->getSubLevelClubIds($executiveboard, $clubId);
                if ($clubIds) {
                    $functonCount->andWhere($functonCount->expr()->in('rc.assinedClub', $clubIds));
                }
            } else {
                $functonCount->andWhere('rc.assinedClub =:assignedClubId');
            }
        }
//echo $functonCount->getQuery()->getSQL();die;
        
         // $functonCount->andWhere('cc.isFedMembershipConfirmed=0');
        return $functonCount;
    }

    /**
     * Function to get the sublevel club ids.
     * If this is executiveboard it will exclude federation and subfederation ids
     *
     * @param int $isExecutiveboard Is this used with executiveboard or not
     * @param int $clubId           Current logged in club Id
     *
     * @return array $clubIds Returns the sublevel club ids
     */
    public function getSubLevelClubIds($isExecutiveboard, $clubId) {
        $andCond = '';
        if ($isExecutiveboard) {
            $andCond = "AND c.is_federation = 0 AND c.is_sub_federation = 0";
        }
        $conn = $this->getEntityManager()->getConnection();
        $subLevelClubIds = $conn->fetchAll("SELECT c.id FROM
                                                    (SELECT  sublevelClubs(id) AS id, @level AS level FROM
                                                            (SELECT  @start_with := $clubId,@id := @start_with,@level := 0) vars,
                                                    fg_club WHERE @id IS NOT NULL)
                                            ho JOIN fg_club c ON c.id = ho.id $andCond");
        $clubIds = FgUtility::getArrayFlatten($subLevelClubIds);

        return $clubIds;
    }

    /**
     * Function to get id,title of all functions in a category
     *
     * @param int     $categoryId      Category Id
     * @param string  $clubDefaultLang Club Default Language
     * @param boolean $getArrayResult  Whether to get resulting array or not
     * @param boolean $sortResult      Whether to get resulting array on the basis of sort order
     *
     * @return array $result
     */
    public function getAllTeamFunctionsOfAClub($categoryId = '', $clubDefaultLang, $getArrayResult = false, $sortResult = false) {
        $result = array();
        if ($categoryId != '') {
            $functionArr = $this->createQueryBuilder('f')
                    ->select("f.id as functionId, (CASE WHEN (fi18n.titleLang IS NULL OR fi18n.titleLang = '') THEN f.title ELSE fi18n.titleLang END) AS functionTitle")
                    ->leftJoin('CommonUtilityBundle:FgRmFunctionI18n', 'fi18n', 'WITH', "fi18n.id=f.id AND fi18n.lang='" . $clubDefaultLang . "'")
                    ->andWhere('f.category=:catId')
                    ->setParameter('catId', $categoryId);
            if ($sortResult) {
                $functionArr = $functionArr->orderBy('f.sortOrder', 'ASC');
            } else {
                $functionArr = $functionArr->orderBy('f.id', 'ASC');
            }
            $functionArr = $functionArr->getQuery()->getResult();
            if ($getArrayResult) {
                return $functionArr;
            }
            foreach ($functionArr as $function) {
                $result[$function['functionId']] = $function['functionTitle'];
            }
        }

        return $result;
    }

    /**
     * Function to get number of functions in a category
     *
     * @param int $categoryId Category Id
     *
     * @return Integer count
     */
    public function getTeamFunctionCount($categoryId) {
        $query = $this->createQueryBuilder('f')
                ->select("count(f.id) as functionCount")
                ->where('f.category=:catId')
                ->setParameter('catId', $categoryId);
        $result = $query->getQuery()->getResult();

        return $result[0]['functionCount'];
    }

}
