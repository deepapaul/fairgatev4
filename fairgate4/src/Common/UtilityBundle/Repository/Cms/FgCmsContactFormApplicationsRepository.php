<?php
/**
 * FgCmsContactFormApplicationsRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmsContactFormApplicationsRepository
 *
 */
class FgCmsContactFormApplicationsRepository extends EntityRepository
{

    /**
     * get the list of application to confirm
     * 
     * @param int        $clubId     clubId
     * @param boolean    $countFlag  whether to calculate count Flag
     * @param string     $status     status
     * 
     * @return array/int
     */
    public function getApplicationsToConfirm($clubId, $countFlag, $status)
    {

        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $qs = $this->createQueryBuilder('c')
            ->select("DATE_FORMAT(c.decisionDate, '$datetimeFormat') as decisionDate,c.status, c.id as appId, f.isDeleted as formDeleted, c.formData,"
                . "DATE_FORMAT(c.createdAt, '$datetimeFormat') as createdAt,IDENTITY(c.clubContact) as appContact, "
                . "c.contactName as name,contactName(IDENTITY(c.clubContact)) as contactName,"
                . "contactName(IDENTITY(c.decidedBy)) as decidedBy,c.id as confirmId,f.title, IDENTITY(c.form) as formId, "
                . "f.contactFormType, CheckActiveContact(c.clubContact, :clubId) as activeAppContact, "
                . "(CASE WHEN IDENTITY(fc.clubMembershipCat) IS NOT NULL THEN IDENTITY(fc.clubMembershipCat) ELSE 0 END) AS clubmembershipType, "
                . "ms.gender AS gender, "
                . "(CASE WHEN fc.isCompany = true THEN 1 ELSE 0 END) AS isCompany, "
                . "CheckActiveContact(c.decidedBy, :clubId) as activeContactDecided")
            ->leftJoin('CommonUtilityBundle:FgCmsForms', 'f', 'WITH', 'f.id = c.form')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'fc', 'WITH', 'fc.id = IDENTITY(c.clubContact)')
            ->leftJoin('CommonUtilityBundle:MasterSystem', 'ms', 'WITH', 'ms.fedContact = fc.fedContact')
            ->where('f.club =:clubId')
            ->andWhere('f.formType = :formtype')
            ->andWhere('f.formStage = :stage')
            ->andWhere("c.status IN (:status)")
            ->setParameters(array('clubId' => $clubId, 'formtype' => 'contact_field', 'stage' => 'stage3', 'status' => $status))
            ->getQuery()
            ->getArrayResult();

        if ($countFlag) {
            $qs = count($qs);
        }
        return $qs; //
    }

    /**
     * This function is used to save contact application data.
     * 
     * @param json      $formData       JSON string of form data
     * @param int       $formId         Contact form id 
     * @param string    $contactName    Contact name
     * @param int       $clubContactId  Club contact id
     * 
     * @return int  contact form application id
     */
    public function saveFormDetails($formData, $formId, $contactName, $clubContactId)
    {
        $formObj = $this->_em->getReference('CommonUtilityBundle:FgCmsForms', $formId);
        $clubContactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $clubContactId);
        $contactForm = new \Common\UtilityBundle\Entity\FgCmsContactFormApplications();
        $contactForm->setContactName($contactName);
        $contactForm->setClubContact($clubContactObj);
        $contactForm->setForm($formObj);
        $contactForm->setFormData($formData);
        $contactForm->setStatus('PENDING');
        $contactForm->setCreatedAt(new \DateTime("now"));
        $this->_em->persist($contactForm);
        $this->_em->flush();

        return $contactForm->getId();
    }

    /**
     * Function to get the contacts added in a list of confirmation requests.
     *
     * @param array $selectedConfirmationIds
     *
     * @return array $resultArray Array of contacts
     */
    public function getContactInConfirmation($selectedConfirmationIds)
    {
        $result = $this->createQueryBuilder('ch')
            ->select("ch.id AS id, IDENTITY(ch.clubContact) AS contactid, contactname(ch.clubContact) AS name, "
                . "IDENTITY(ch.decidedBy) AS decidedBy")
            ->where('ch.id IN( :selectedConfirmationIds)')
            ->setParameters(array('selectedConfirmationIds' => $selectedConfirmationIds))
            ->groupBy('contactid');
        $resultantArrayObj = $result->getQuery()->getResult();
        foreach ($resultantArrayObj as $resultant) {
            $resultantArray[$resultant['contactid']] = array($resultant['name'], $resultant['decidedBy'], $resultant['id']);
        }

        return $resultantArray;
    }

    /**
     * Function to discard the selected mutations or creations
     *
     * @param int    $clubId      ClubId
     * @param int    $contactId   ContactId
     * @param array  $selectedIds Selected Confirm Ids
     * @param string $page        Creations or mutations
     */
    public function discardSelectedConfirmations($contactId, $selectedIds)
    {
        $this->updateAppStatus($contactId, $selectedIds, 'discard');
        $this->removeApplicationFiles($selectedIds);
        $qs = $this->createQueryBuilder('c')
            ->select("IDENTITY(c.clubContact) as appContact ")
            ->where("c.id IN (:ids)")
            ->setParameters(array('ids' => $selectedIds))
            ->getQuery()
            ->getArrayResult();
        $qs = array_column($qs, 'appContact');

        foreach ($qs as $key => $contact) {
            $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contact);
            $fedContactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->findOneBy(array('fedContact' => $contactObj->getFedContact()->getId()));
            $this->_em->remove($fedContactObj);
        }
        $this->_em->flush();
    }

    /**
     * Function to confirm the selected mutations or creations
     *
     * @param int    $contactId   ContactId
     * @param array  $selectedIds Selected Confirm Ids
     */
    public function confirmSelectedConfirmations($contactId, $selectedIds)
    {
        $this->updateAppStatus($contactId, $selectedIds, 'confirm');

        if (count($selectedIds) > 0) {
            $selectedIdss = implode(',', $selectedIds);
            $qb = $this->createQueryBuilder('a');
            $qb = $qb->select('IDENTITY(c.fedContact) as fedContact, IDENTITY(a.clubContact) as id ')
                ->leftJoin('CommonUtilityBundle:FgCmContact', 'c', 'WITH', 'c.id = a.clubContact')
                ->where('a.id IN (' . $selectedIdss . ')')
                ->getQuery()
                ->getArrayResult();
            $fedcontactIds = array_column($qb, 'fedContact');
            $contactIds = array_column($qb, 'id');

            $q = $this->createQueryBuilder()
                ->update('CommonUtilityBundle:FgCmContact', 'c')
                ->set('c.isDraft', '0')
                ->set('c.createdAt', ':now')
                ->set('c.lastUpdated', ':now')
                ->where('c.fedContact IN ( :contactIds)')
                ->setParameters(array('contactIds' => $fedcontactIds, 'now' => date('Y-m-d H:i:s')))
                ->getQuery()
                ->execute();

            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery('UPDATE fg_cm_change_log c LEFT JOIN fg_cm_contact cc ON cc.id = c.contact_id SET c.changed_by = ' . $contactId . " WHERE cc.fed_contact_id IN ('" . implode("','", $fedcontactIds) . "')");

            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery('UPDATE fg_cm_membership_history h LEFT JOIN fg_cm_contact cc ON cc.id = h.contact_id SET h.changed_by = ' . $contactId . " WHERE cc.fed_contact_id IN ('" . implode("','", $fedcontactIds) . "')");

            return $contactIds;
        }
    }

    /**
     * Function to log confirmation/discard the selected creations 
     *
     * @param int    $clubId      ClubId
     * @param int    $contactId   ContactId
     * @param array  $selectedIds Selected Confirm Ids
     * @param string $action      confirm or discard
     */
    private function updateAppStatus($contactId, $selectedIds, $action)
    {
        //update confirm status 
        $confirmStatus = ($action == 'confirm') ? 'CONFIRMED' : 'DISMISSED';

        $qb = $this->createQueryBuilder()
            ->update('CommonUtilityBundle:FgCmsContactFormApplications', 'c')
            ->set('c.status', ':confirmStatus')
            ->set('c.decisionDate', ':now')
            ->set('c.decidedBy', ':contact')
            ->where('c.id IN ( :confirmIds )')
            ->setParameters(array('contact' => $contactId, 'confirmIds' => $selectedIds, 'confirmStatus' => $confirmStatus, 'now' => date('Y-m-d H:i:s')))
            ->getQuery()
            ->execute();
    }

    /**
     * This method is used to remove contact application files.
     * 
     * @param array $selectedIds array of ids
     * 
     * @return void 
     */
    private function removeApplicationFiles($selectedIds)
    {
        foreach ($selectedIds as $id) {
            $contactFormObj = $this->_em->getReference('CommonUtilityBundle:FgCmsContactFormApplications', $id);
            $formData = json_decode($contactFormObj->getFormData(), true);
            $clubId = $contactFormObj->getForm()->getClub()->getId();
            $contactFiles = $this->getFileNames($formData, 'contact');
            $formFiles = $this->getFileNames($formData, 'form');
            $this->unLinkFiles($contactFiles, 'contact_form', $clubId);
            $this->unLinkFiles($formFiles, 'form', $clubId);
        }
    }

    /**
     * Method to get saved names of uploaded files
     * 
     * @param array  $formData form-Data
     * @param string $type     contact/form
     * 
     * @return array of original filenames
     */
    private function getFileNames($formData, $type)
    {
        $fileFields = $formData[$type]['files'];
        $returnArray = array();
        foreach (array_keys($fileFields) as $fieldId) {
            $returnArray[$fieldId] = $formData[$type][$fieldId]['fileNameNew'];
        }

        return $returnArray;
    }

    /**
     * This method is used to remove files from folder;
     * 
     * @param array $files array of file names
     * @param string $type form field type 
     * @param int $clubId club id
     * 
     * @return void
     */
    private function unLinkFiles($files, $type, $clubId)
    {
        $source = ($type == 'contact_form') ? 'contactfield_file' : 'contact_application_file';
        $formUploadFolder = FgUtility::getUploadFilePath($clubId, $source);
        foreach ($files as $fieldId => $fileName) {
            if ($type == 'contact_form') {
                $attrObj = $this->_em->getReference('CommonUtilityBundle:FgCmAttribute', $fieldId);
                $source = ($attrObj->getInputType() == 'imageupload') ? 'contactfield_image' : 'contactfield_file';
                $clubId = $attrObj->getClub()->getId();
                $formUploadFolder = FgUtility::getUploadFilePath($clubId, $source);
            }
            unlink($formUploadFolder . '/' . $fileName);
        }
    }
}
