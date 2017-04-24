<?php
/**
 * This class is used for handling assignments to be confirmed by administrator.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCmChangeToconfirmFunctions;

/**
 * FgCmChangeToconfirmFunctionsRepository
 *
 * This class is used for handling assignments to be confirmed by administrator.
 */
class FgCmChangeToconfirmFunctionsRepository extends EntityRepository
{

    /**
     * Function to save mutation functions.
     *
     * @param object $mutationObj Mutation object
     * @param int    $functionId  Function id
     * @param string $actionType  Action type (ADDED/REMOVED)
     */
    public function saveMutationFunctions($mutationObj, $functionId, $actionType = 'ADDED')
    {
        $functionObj = $this->_em->getReference('CommonUtilityBundle:FgRmFunction', $functionId);

        $mutationFunObj = new FgCmChangeToconfirmFunctions();
        $mutationFunObj->setToconfirm($mutationObj);
        $mutationFunObj->setFunction($functionObj);
        $mutationFunObj->setActionType($actionType);

        $this->_em->persist($mutationFunObj);
    }

    /**
     * Function to get added mutation functions of a contact for a given role.
     *
     * @param int    $contactId Contact id
     * @param int    $roleId    Role id
     * @param int    $clubId    Club id
     * @param int    $changedBy Changed by contact
     * @param string $type      Type (mutation/creation)
     *
     * @return array $functionIds Array of function ids
     */
    public function getMutationFunctions($contactId, $roleId, $clubId, $changedBy, $type)
    {
        $funsObj = $this->createQueryBuilder('chf')
                ->select("IDENTITY(chf.function) AS functionId")
                ->leftJoin("CommonUtilityBundle:FgCmChangeToconfirm", "ch", "WITH", "ch.id = chf.toconfirm")
                ->where('ch.contact = :contactId')
                ->andWhere('ch.roleId = :roleId')
                ->andWhere('ch.club = :clubId')
                ->andWhere('ch.changedBy = :changedBy')
                ->andWhere('ch.type = :type')
                ->andWhere('ch.confirmStatus = :confirmStatus')
                ->andWhere('chf.actionType = :actionType')
                ->setParameters(array('contactId' => $contactId, 'roleId' => $roleId, 'clubId' => $clubId, 'changedBy' => $changedBy, 'type' => $type, 'confirmStatus' => 'NONE', 'actionType' => 'ADDED'))
                ->groupBy('functionId')
                ->getQuery()
                ->getResult();

        $functionIds = array();
        foreach ($funsObj as $funObj) {
            $functionIds[] = $funObj['functionId'];
        }

        return $functionIds;
    }

}