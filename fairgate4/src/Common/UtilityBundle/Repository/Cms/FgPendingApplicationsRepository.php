<?php

/**
 * FgPendingApplicationsRepository.
 *
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgPendingApplicationRepository
 *
 * The repository class to manage the pending application table
 */
class FgPendingApplicationsRepository extends EntityRepository
{

    /**
     * Function to save the pending application data
     * 
     * @param Array $dataArray      Pending application data
     * 
     * @return void
     */
    public function savePendingApplicationData($dataArray)
    {
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $dataArray['clubId']);

        $pendingApplicationObj = new \Common\UtilityBundle\Entity\FgPendingApplications();
        $pendingApplicationObj->setClub($clubObj);
        $pendingApplicationObj->setUniqueId($dataArray['uniqueId']);
        $pendingApplicationObj->setType($dataArray['type']);
        $pendingApplicationObj->setJsonData($dataArray['jsonData']);
        $pendingApplicationObj->setCreatedAt(new \DateTime("now"));
        $this->_em->persist($pendingApplicationObj);
        $this->_em->flush();
        return $pendingApplicationObj->getId();
    }

    /**
     * Function to get the pending application data
     * 
     * @param int    $clubId    The id of the current club
     * @param string $code      The activation code for the request
     * @param string $type      The pending application type
     * 
     * @return void
     */
    public function getPendingApplicationData($clubId, $code, $type)
    {
        $applicationObj = $this->createQueryBuilder('p')
            ->select('p.jsonData AS subscriberData')
            ->where('p.uniqueId=:code')
            ->andWhere('p.club=:clubId')
            ->andWhere('p.type=:type')
            ->setParameters(array('code' => $code, 'clubId' => $clubId, 'type' => $type))
            ->getQuery();
        return $applicationObj->getOneOrNullResult();
    }
}
