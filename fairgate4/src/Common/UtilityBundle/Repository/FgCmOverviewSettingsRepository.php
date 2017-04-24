<?php

/**
 * FgCmOverviewSettingsRepository
 *
 * This class is used for overview settings in contact administration.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmOverviewSettingsRepository
 *
 * This class is used for getting and updating overview settings in contact administration.
 */
class FgCmOverviewSettingsRepository extends EntityRepository
{

    /**
     * Function to get Contact Overview Settings.
     *
     * @param int $clubId Club Id.
     * @param int $type   Type of overview
     *
     * @return array $resultArray Overview Settings Array.
     */
    public function getOverviewSettings($clubId, $type) {

        $qb = $this->createQueryBuilder('os')
                ->select('os.id as settingsId, os.settings')
                ->where('os.club=:clubId')
                ->andWhere('os.type=:type')
                ->setParameter('clubId', $clubId)
                ->setParameter('type', $type);

        $result = $qb->getQuery()->getResult();
        $resultArray = $result[0];

        return $resultArray;
    }

}
