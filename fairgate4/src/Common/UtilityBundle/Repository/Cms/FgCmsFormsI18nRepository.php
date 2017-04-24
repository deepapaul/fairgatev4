<?php 
/**
 * FgCmsFormsI18nRepository.
 *
 * @package 	WebsiteCMSBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCmsFormsI18nRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgCmsFormsI18nRepository extends EntityRepository
{

    /**
     * Function to save the stage2 form field I18n options to the database
     * 
     * @param Int   $formOptionId   Form Option Id
     * @param Array $dataArray      Stage2 options dataarray
     * @param Array $clubLanguages  Club Language array
     * 
     * @return void
     */
    public function saveOptionI18nStage2($formOptionId, $dataArray, $clubLanguages, $conn)
    {
        foreach ($clubLanguages as $language) {

            $subjectValue = ($dataArray['subject'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['subject'][$language], $conn)) : '';
            $contentValue = ($dataArray['content'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['content'][$language], $conn)) : '';

            $query = "INSERT INTO fg_cms_forms_i18n (id,lang,confirmation_email_subject_lang,confirmation_email_content_lang) "
                . " VALUES ($formOptionId,'$language','$subjectValue','$contentValue') "
                . "ON DUPLICATE KEY UPDATE confirmation_email_subject_lang = VALUES(confirmation_email_subject_lang),confirmation_email_content_lang = VALUES(confirmation_email_content_lang)";

            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery($query);
        }

        return;
    }

    /**
     * Function to save the stage3 form field I18n options to the database
     * 
     * @param Int   $formOptionId   Form Option Id
     * @param Array $dataArray      Stage3 options dataarray
     * @param Array $clubLanguages  Club Language array
     * 
     * @return void
     */
    public function saveOptionI18nStage3($formOptionId, $dataArray, $clubLanguages, $conn)
    {
        foreach ($clubLanguages as $language) {

            $successMessageValue = ($dataArray['successmessage'][$language] != '') ? trim(FgUtility::getSecuredDataString($dataArray['successmessage'][$language], $conn)) : '';

            $query = "INSERT INTO fg_cms_forms_i18n (id,lang,success_message_lang) "
                . " VALUES ($formOptionId,'$language','$successMessageValue') "
                . "ON DUPLICATE KEY UPDATE success_message_lang = VALUES(success_message_lang)";

            $conn = $this->getEntityManager()->getConnection();
            $conn->executeQuery($query);
        }

        return;
    }

    /**
     * This function is used to save i18n values in stage 2 of contact application form set up wizard 
     * 
     * @param int   $formId        The form id
     * @param array $formData      The form details to be saved
     * @param array $clubLanguages Array of club languages
     */
    public function saveContactFormStage2I18n($formId, $formData, $clubLanguages)
    {
        $conn = $this->_em->getConnection();
        $formObj = $this->_em->getReference('CommonUtilityBundle:FgCmsForms', $formId);
        foreach ($clubLanguages as $language) {
            $confirmationEmailSubject = FgUtility::getSecuredDataString(trim($formData['confirmationEmailSubject'][$language]), $conn);
            $confirmationEmailContent = FgUtility::getSecuredDataString(trim($formData['confirmationEmailContent'][$language]), $conn);
            $acceptanceEmailSubject = FgUtility::getSecuredDataString(trim($formData['acceptanceEmailSubject'][$language]), $conn);
            $acceptanceEmailContent = FgUtility::getSecuredDataString(trim($formData['acceptanceEmailContent'][$language]), $conn);
            $dismissalEmailSubject = FgUtility::getSecuredDataString(trim($formData['dismissalEmailSubject'][$language]), $conn);
            $dismissalEmailContent = FgUtility::getSecuredDataString(trim($formData['dismissalEmailContent'][$language]), $conn);

            $formI18nObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsFormsI18n')->findOneBy(array('lang' => $language, 'id' => $formId));
            if (count($formI18nObj) == 0) {
                $formI18nObj = new \Common\UtilityBundle\Entity\FgCmsFormsI18n();
                $formI18nObj->setId($formObj);
                $formI18nObj->setLang($language);
                $formI18nObj->setConfirmationEmailSubjectLang($confirmationEmailSubject);
                $formI18nObj->setConfirmationEmailContentLang($confirmationEmailContent);
                $formI18nObj->setAcceptanceEmailSubjectLang($acceptanceEmailSubject);
                $formI18nObj->setAcceptanceEmailContentLang($acceptanceEmailContent);
                $formI18nObj->setDismissalEmailSubjectLang($dismissalEmailSubject);
                $formI18nObj->setDismissalEmailContentLang($dismissalEmailContent);
                $this->_em->persist($formI18nObj);
            } else {
                $paramsArray = array('confirmationEmailSubject' => $confirmationEmailSubject, 'confirmationEmailContent' => $confirmationEmailContent,
                    'acceptanceEmailSubject' => $acceptanceEmailSubject, 'acceptanceEmailContent' => $acceptanceEmailContent,
                    'dismissalEmailSubject' => $dismissalEmailSubject, 'dismissalEmailContent' => $dismissalEmailContent);
                $this->updateContactFormStage2I18n($formId, $language, $paramsArray);
            }
        }
        $this->_em->flush();
    }

    /**
     * This function is used to update i18n entries corresponding to a stage 2 contact application form
     * 
     * @param int    $formId    The form id
     * @param string $language  The language to be updated
     * @param array  $paramsArr The parameters to be bound to query
     */
    private function updateContactFormStage2I18n($formId, $language, $paramsArr)
    {
        $paramsArr['id'] = $formId;
        $paramsArr['lang'] = $language;
        $qb = $this->createQueryBuilder();
        $query = $qb->update('CommonUtilityBundle:FgCmsFormsI18n', 'fi18n')
            ->set('fi18n.confirmationEmailSubjectLang', ":confirmationEmailSubject")
            ->set('fi18n.confirmationEmailContentLang', ":confirmationEmailContent")
            ->set('fi18n.acceptanceEmailSubjectLang', ":acceptanceEmailSubject")
            ->set('fi18n.acceptanceEmailContentLang', ":acceptanceEmailContent")
            ->set('fi18n.dismissalEmailSubjectLang', ":dismissalEmailSubject")
            ->set('fi18n.dismissalEmailContentLang', ":dismissalEmailContent")
            ->where('fi18n.lang=:lang')
            ->andWhere('fi18n.id =:id')
            ->setParameters($paramsArr)
            ->getQuery();
        $query->execute();
    }

    /**
     * This function is used to save i18n values in stage 3 of contact application form set up wizard 
     * 
     * @param int   $formId        The form id
     * @param array $formData      The form details to be saved
     * @param array $clubLanguages Array of club languages
     */
    public function saveContactFormStage3I18n($formId, $formData, $clubLanguages)
    {
        $conn = $this->_em->getConnection();
        $formObj = $this->_em->getReference('CommonUtilityBundle:FgCmsForms', $formId);
        foreach ($clubLanguages as $language) {
            $successMessage = FgUtility::getSecuredDataString(trim($formData['successmessage'][$language]), $conn);
            $formI18nObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsFormsI18n')->findOneBy(array('lang' => $language, 'id' => $formId));
            if (empty($formI18nObj)) {
                $formI18nObj = new \Common\UtilityBundle\Entity\FgCmsFormsI18n();
                $formI18nObj->setId($formObj);
                $formI18nObj->setLang($language);
                $formI18nObj->setSuccessMessageLang($successMessage);
                $this->_em->persist($formI18nObj);
            } else {
                $this->updateContactFormStage3I18n($formId, $language, $successMessage);
            }
        }
        $this->_em->flush();
    }

    /**
     * This function is used to update i18n entries corresponding to a stage 3 contact application form
     * 
     * @param int    $formId         The form id
     * @param string $language       The language to be updated
     * @param string $successMessage The success message to be saved
     */
    private function updateContactFormStage3I18n($formId, $language, $successMessage)
    {
        $qb = $this->createQueryBuilder();
        $query = $qb->update('CommonUtilityBundle:FgCmsFormsI18n', 'fi18n')
            ->set('fi18n.successMessageLang', ":successMessageLang")
            ->where('fi18n.lang = :lang')
            ->andWhere('fi18n.id = :id')
            ->setParameters(array('id' => $formId, 'lang' => $language, 'successMessageLang' => $successMessage))
            ->getQuery();
        $query->execute();
    }
}