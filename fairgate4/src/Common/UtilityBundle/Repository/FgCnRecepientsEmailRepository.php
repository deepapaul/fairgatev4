<?php

/**
 * This class is used for handling email settings of recipients in Communication module.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgCnRecepientsEmailRepository
 *
 * This class is used for handling email settings of recipients in Communication module.
 */
class FgCnRecepientsEmailRepository extends EntityRepository
{
/**
 * For collecting the email fields
 * @param type $filterId
 *
 * @return array
 */
public function getReciepientemailfields($filterId) {
    $qb = $this->createQueryBuilder('rse')
                ->select('rse.id','rse.emailType','rse.selectionType','ef.id as fieldId')
                ->leftJoin('rse.recepientList','rs')
                ->leftJoin('rse.emailField','ef')
                ->where('rs.id=:filterId')
                ->setParameter('filterId', $filterId);

         $result = $qb->getQuery()->getResult();

         return $result;
    }

    /**
     * Function to get email settings of a particular recipient list.
     *
     * @param int $recipientId  Recipient List Id
     *
     * @return array $emailSettings Array of email settings (main, substitute).
     */
    public function getEmailSettingsofRecipients($recipientId)
    {
        $emailsObj = $this->createQueryBuilder('rm')
                ->select('rm.selectionType, rm.emailType, IDENTITY(rm.emailField) AS emailField')
                ->where('rm.recepientList=:recepientId')
                ->setParameter('recepientId', $recipientId)
                ->getQuery()
                ->getArrayResult();

        $emailSettings = array('main' => '', 'substitute' => '');
        foreach ($emailsObj as $emailObj) {
            if ($emailObj['selectionType'] == 'main') {
                if ($emailSettings['main'] != '') {
                    $emailSettings['main'] .= ',';
                }
                $emailSettings['main'] .= ($emailObj['emailType'] == 'contact_field') ? $emailObj['emailField'] : $emailObj['emailType'];
            } else if ($emailObj['selectionType'] == 'substitute') {
                $emailSettings['substitute'] = ($emailObj['emailType'] == 'contact_field') ? $emailObj['emailField'] : $emailObj['emailType'];
            }
        }

        return $emailSettings;
    }

}