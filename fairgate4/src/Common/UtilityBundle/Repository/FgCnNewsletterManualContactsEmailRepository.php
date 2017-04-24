<?php

/**
 * This class is used for handling email settings of newsletter in Communication module.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgCnNewsletterManualContactsEmailRepository
 *
 * This class is used for handling email settings of newsletter in Communication module.
 */
class FgCnNewsletterManualContactsEmailRepository extends EntityRepository
{

    /**
     * Function to get email settings of a particular newsletter.
     *
     * @param int $newsletterId  Newsletter Id
     *
     * @return array $emailSettings Array of email settings (main, substitute).
     */
    public function getEmailSettingsofNewsletter($newsletterId)
    {
        $emailsObj = $this->createQueryBuilder('nm')
                ->select('nm.selectionType, nm.emailType, IDENTITY(nm.emailField) AS emailField')
                ->where('nm.newsletter=:newsletterId')
                ->setParameter('newsletterId', $newsletterId)
                ->getQuery()
                ->getArrayResult();

        $emailSettings = array('main' => array(), 'substitute' => '');
        foreach ($emailsObj as $emailObj) {
            $emailVal = ($emailObj['emailType'] == 'contact_field') ? $emailObj['emailField'] : $emailObj['emailType'];
            if ($emailObj['selectionType'] == 'main') {
                if (!in_array($emailVal, $emailSettings['main'])) {
                    $emailSettings['main'][] = $emailVal;
                }
            } else if ($emailObj['selectionType'] == 'substitute') {
                $emailSettings['substitute'] = $emailVal;
            }
        }

        return $emailSettings;
    }

    /**
     * Function to add email settings of a newsletter.
     *
     * @param int    $newsletterId  Newsletter id
     * @param array  $emailSettings Email settings array
     * @param object $newsletterObj Newsletter object
     */
    public function addNewsletterEmailSettings($newsletterId, $emailSettings, $newsletterObj = null) {
        $em = $this->getEntityManager();
        if (!$newsletterObj) {
            $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        }
        foreach ($emailSettings as $selectionType => $emailSetting) {
            foreach ($emailSetting as $emailField) {
                $emailSettingObj = new \Common\UtilityBundle\Entity\FgCnNewsletterManualContactsEmail();
                $emailSettingObj->setNewsletter($newsletterObj);
                $emailSettingObj->setSelectionType($selectionType);
                if ($emailField == 'parent_email') {
                    $emailSettingObj->setEmailType('parent_email');
                } else {
                    $emailFieldObj = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($emailField);
                    $emailSettingObj->setEmailType('contact_field');
                    $emailSettingObj->setEmailField($emailFieldObj);
                }
                $em->persist($emailSettingObj);
            }
        }
        $em->flush();
    }

    /**
     * Function to remove email settings of a newsletter.
     *
     * @param int   $newsletterId  Newsletter id
     * @param array $emailSettings Email settings array
     */
    public function removeNewsletterEmailSettings($newsletterId, $emailSettings) {
        $em = $this->getEntityManager();
        foreach ($emailSettings as $selectionType => $emailSetting) {
            foreach ($emailSetting as $emailField) {
                if ($emailField == 'parent_email') {
                    $emailSettingObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->findOneBy(array('newsletter' => $newsletterId, 'selectionType' => $selectionType, 'emailType' => 'parent_email'));
                } else {
                    $emailSettingObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->findOneBy(array('newsletter' => $newsletterId, 'selectionType' => $selectionType, 'emailType' => 'contact_field', 'emailField' => $emailField));
                }
                $em->remove($emailSettingObj);
            }
        }
        $em->flush();
    }

}