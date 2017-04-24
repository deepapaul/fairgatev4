<?php

/**
 * FgRmRoleFunctionRepository
 *
 * This class is used for role functions in role management.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgRmRoleFunctionRepository
 *
 * This class is used for getting functions of role in role management.
 */
class FgRmRoleFunctionRepository extends EntityRepository
{

    /**
     * Function to get all the functions of corresponding roleId
     *
     * @param int $roleId Role id
     *
     * @return array $functionIdArr Array of function ids.
     */
    public function getFunctionIds($roleId)
    {
        $roleFunctions = $this->createQueryBuilder('rf')
                ->select('IDENTITY(rf.function) as functionId')
                ->where('rf.role=:roleId')
                ->setParameter('roleId', $roleId);

        $dataResult = $roleFunctions->getQuery()->getResult();

        $functionIdArr = array();
        foreach ($dataResult as $key => $valArray) {
            $functionIdArr[] = $valArray['functionId'];
        }

        return $functionIdArr;
    }

    /**
     * Function to get functions of a given role.
     *
     * @param int     $roleId          Role id
     * @param string  $clubDefaultLang Club default language
     * @param boolean $getArrayResult  Whether to get filtered result or not
     * @param boolean $sortResult      Whether to get result in sort order
     * @param boolean $remClubExecFuns Whether to avoid club executive board functions from result or not
     *
     * @return array $functionIdArr Function id array
     */
    public function getRoleFunctions($roleId, $clubDefaultLang, $getArrayResult = false, $sortResult = false, $remClubExecFuns = false)
    {
        $roleFunctions = $this->createQueryBuilder('rf')
                ->select("IDENTITY(rf.function) as functionId, (CASE WHEN (fi18n.titleLang IS NULL OR fi18n.titleLang = '') THEN f.title ELSE fi18n.titleLang END) AS functionTitle")
                ->leftJoin('CommonUtilityBundle:FgRmFunction', 'f', 'WITH', "f.id=rf.function")
                ->leftJoin('CommonUtilityBundle:FgRmFunctionI18n', 'fi18n', 'WITH', "fi18n.id=f.id AND fi18n.lang='" . $clubDefaultLang . "'")
                ->where('rf.role=:roleId')
                ->setParameter('roleId', $roleId);
        
        if ($remClubExecFuns) {
            $roleFunctions = $roleFunctions->andWhere('f.isFederation != 1');
        }

        if ($sortResult) {
            $roleFunctions = $roleFunctions->orderBy('f.sortOrder', 'ASC');
        }

        $dataResult = $roleFunctions->getQuery()->getResult();
        if ($getArrayResult) {
            return $dataResult;
        }
        $functionIdArr = array();
        foreach ($dataResult as $valArray) {
            $functionIdArr[$valArray['functionId']] = $valArray['functionTitle'];
        }

        return $functionIdArr;
    }

}
