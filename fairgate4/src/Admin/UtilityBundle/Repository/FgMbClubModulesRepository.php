<?php

/**
 * FgMbClubModulesRepository.
 */
namespace Admin\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgMbClubModulesRepository.
 *
 * This class was generated by the Doctrine ORM
 * repository methods below.
 */
class FgMbClubModulesRepository extends EntityRepository
{

    /**
     * Remove modules of expiresd clubs
     * 
     * @param array $clubIdsExpired Expired clubIds
     * @param array $modulesIds     Modules to be removed
     * 
     * @return boolean
     */
    public function removeModulesOfClubs($clubIdsExpired, $modulesIds)
    {
        $clubModuleObj = $this->createQueryBuilder('M')
            ->delete('AdminUtilityBundle:FgMbClubModules', 'M')
            ->where("M.club IN (:clubIds)")
            ->andWhere("M.module IN (:modulesIds)")
            ->setParameter('clubIds', $clubIdsExpired)
            ->setParameter('modulesIds', $modulesIds);

        $clubModuleObj->getQuery()->execute();

        return true;
    }
}
