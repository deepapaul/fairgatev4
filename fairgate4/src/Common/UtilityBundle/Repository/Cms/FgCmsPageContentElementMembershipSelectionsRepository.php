<?php
/**
 * FgCmsPageContentElementMembershipSelectionsRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsPageContentElementMembershipSelectionsRepository
 *
 * This class is used for handling CMS form field of type club-membership.
 */
class FgCmsPageContentElementMembershipSelectionsRepository extends EntityRepository
{

    /**
     * This function is used to delete all memberships chosen for a particular form field
     * 
     * @param int $fieldId Form field id
     */
    public function deleteAllMembershipsOfAField($fieldId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete('CommonUtilityBundle:FgCmsPageContentElementMembershipSelections', 'MS');
        $qb->where('MS.field = :fieldId');
        $qb->setParameter('fieldId', $fieldId);
        $query = $qb->getQuery();
        $query->execute();
    }

    /**
     * This function is used to insert all memberships chosen for a particular form field
     * 
     * @param array $clubMembershipsArr Array of club memberships
     * @param int   $fieldId            Form field id 
     */
    public function insertMembershipSelectionsOfAField($clubMembershipsArr, $fieldId)
    {
        $fieldObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPageContentElementFormFields', $fieldId);
        foreach ($clubMembershipsArr as $val) {
            $clubMembershipObj = $this->_em->getReference('CommonUtilityBundle:FgCmMembership', $val);
            $membershipSelectionObj = new \Common\UtilityBundle\Entity\FgCmsPageContentElementMembershipSelections();
            $membershipSelectionObj->setField($fieldObj);
            $membershipSelectionObj->setMembership($clubMembershipObj);
            $this->_em->persist($membershipSelectionObj);
        }
        $this->_em->flush();
    }
}
