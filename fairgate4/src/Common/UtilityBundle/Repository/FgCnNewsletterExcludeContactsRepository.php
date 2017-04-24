<?php

/*
 * FgCnNewsletterExcludeContactsRepository
 *
 * This class is used for managing newsletter recipients exceptions.
 */
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This repository is used for handling exceptions of Newsletter/Simple Mail.
 */
class FgCnNewsletterExcludeContactsRepository extends EntityRepository
{

    /**
     * Function to get exceptions of a newsletter.
     *
     * @param int $newsletterId Newsletter Id.
     *
     * @return array $exceptions Exceptions Array.
     */
    public function getExceptionsOfNewsletter($newsletterId)
    {
        $exceptions = $this->createQueryBuilder('ec')
                ->select('ec.email AS emailId, ec.salutation')
                ->where('ec.newsletter=:newsletterId')
                ->setParameter('newsletterId', $newsletterId)
                ->getQuery()
                ->getArrayResult();

        return $exceptions;
    }

    /**
     * Function to add excluded data (email-salutation combination) of a newsletter.
     *
     * @param int    $newsletterId  Newsletter id
     * @param array  $excludedData  Excluded data array
     * @param object $newsletterObj Newsletter object
     */
    public function addNewsletterExludedData($newsletterId, $excludedData, $newsletterObj = null) {
        $em = $this->getEntityManager();
        if (!$newsletterObj) {
            $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        }
        foreach ($excludedData as $excludeData) {
            $excludeDataArr = explode('#', $excludeData);
            $exclDataObj = new \Common\UtilityBundle\Entity\FgCnNewsletterExcludeContacts();
            $exclDataObj->setNewsletter($newsletterObj);
            $exclDataObj->setEmail($excludeDataArr[0]);
            $exclDataObj->setSalutation($excludeDataArr[1]);
            $em->persist($exclDataObj);
        }
        $em->flush();
    }

    /**
     * Function to remove excluded data (email-salutation combination) of a newsletter.
     *
     * @param int   $newsletterId Newsletter id
     * @param array $excludedData Excluded data array
     */
    public function removeNewsletterExludedData($newsletterId, $excludedData) {
        $em = $this->getEntityManager();
        foreach ($excludedData as $excludeData) {
            $excludeDataArr = explode('#', $excludeData);
            $exclDataObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterExcludeContacts')->findOneBy(array('newsletter' => $newsletterId, 'email' => $excludeDataArr[0], 'salutation' => $excludeDataArr[1]));
            $em->remove($exclDataObj);
        }
        $em->flush();
    }

}