<?php
/**
 * FgCmsFormsRepository.
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
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmsFormsRepository
 *
 */
class FgCmsFormsRepository extends EntityRepository
{
    /*
     * Function to save the form element stage1 options data to the database
     * 
     * @param string  $formTitle The stage3 data that needed to be inserted/updated
     * @param object  $clubObj The club service object
     * @param object  $contactObj The contact service object
     * 
     * @return int 
     */

    public function saveFormStage1($formTitle, $clubId, $contactId, $formId)
    {
        if ($formId != '') {
            $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
            $formObj->setUpdatedBy($this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId));
            $formObj->setUpdatedAt(new \DateTime('now'));
        } else {
            $formObj = new \Common\UtilityBundle\Entity\FgCmsForms();
            $formObj->setClub($this->_em->getReference('CommonUtilityBundle:FgClub', $clubId));
            $formObj->setFormType('form_field');
            $formObj->setFormStage('stage1');
            $formObj->setCreatedBy($this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId));
            $formObj->setCreatedAt(new \DateTime('now'));
            $formObj->setIsAcceptanceEmailActive(0);
            $formObj->setIsDismissalEmailActive(0);
            $formObj->setIsActive(1);
            $formObj->setIsDeleted(0);
        }

        $formObj->setTitle($formTitle);
        $this->_em->persist($formObj);
        $this->_em->flush();
        return $formObj->getId();
    }
    /*
     * Function to save the form element stage2 options data to the database
     * 
     * @param array  $formData The stage3 data that needed to be inserted/updated
     * @param int    $formId The id of the form that needed to the updated
     * @param object $clubObj The club service object
     * 
     * @return int 
     */

    public function saveFormStage2($formData, $formId, $clubObj)
    {
        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        $clubDefaultLang = $clubObj->get('club_default_lang');
        $clubLanguages = $clubObj->get('club_languages');
        $dataArray = $formData[$formId];


        if ($formObj == '') {
            $formObj = new \Common\UtilityBundle\Entity\FgCmsForms();
        }

        $subject = FgUtility::getSecuredDataString(trim($dataArray['subject'][$clubDefaultLang]), $this->_em->getConnection());
        $content = FgUtility::getSecuredDataString(trim($dataArray['content'][$clubDefaultLang]), $this->_em->getConnection());

        $formObj->setConfirmationEmailSender($dataArray['senderemail']);
        $formObj->setConfirmationEmailSubject($subject);
        $formObj->setConfirmationEmailContent($content);
        $formObj->setNotificationEmailRecipients($dataArray['recipients']);
        $this->_em->persist($formObj);
        $this->_em->flush();

        $formOptionId = $formObj->getId();

        //insert/update the I18n
        $this->_em->getRepository('CommonUtilityBundle:FgCmsFormsI18n')->saveOptionI18nStage2($formOptionId, $dataArray, $clubLanguages, $this->_em->getConnection());

        return $formId;
    }
    /*
     * Function to save the form element stage3 options data to the database
     * 
     * @param array  $formData The stage3 data that needed to be inserted/updated
     * @param int    $formId The id of the form that needed to the updated
     * @param object $clubObj The club service object
     * 
     * @return int 
     */

    public function saveFormStage3($formData, $formId, $clubObj)
    {
        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        $clubDefaultLang = $clubObj->get('club_default_lang');
        $clubLanguages = $clubObj->get('club_languages');
        $dataArray = $formData[$formId];

        if ($formObj == '') {
            $formObj = new \Common\UtilityBundle\Entity\FgCmsForms();
        }

        $successMessage = FgUtility::getSecuredDataString(trim($dataArray['successmessage'][$clubDefaultLang]), $this->_em->getConnection());

        $formObj->setCompletionPromptSuccessMessage($successMessage);
        $formOptionId = $formObj->getId();

        $this->_em->persist($formObj);
        $this->_em->flush();

        //insert/update the I18n
        if ($formOptionId != '') {
            $this->_em->getRepository('CommonUtilityBundle:FgCmsFormsI18n')->saveOptionI18nStage3($formOptionId, $dataArray, $clubLanguages, $this->_em->getConnection());
        }

        return $formId;
    }
    /*
     * Function to get the form stage 2 details
     * 
     * @param int    $formId The id of the form that the data is to be tetirvied
     * @param object $container The container Object
     * @param object $clubObj The club service object
     * 
     * @return array 
     */

    public function getFormOptions($formId, $container, $clubObj)
    {
        $dataArray = array();
        $selectArray = array();
        $selectArray['confirmationEmailSender'] = 'F.confirmationEmailSender';
        $selectArray['acceptanceEmailSender'] = 'F.acceptanceEmailSender';
        $selectArray['dismissalEmailSender'] = 'F.dismissalEmailSender';
        $selectArray['notificationEmailRecipients'] = 'F.notificationEmailRecipients';
        $selectArray['isAcceptanceEmailActive'] = 'F.isAcceptanceEmailActive';
        $selectArray['isDismissalEmailActive'] = 'F.isDismissalEmailActive';
        $selectArray['confirmationEmailSubject'] = 'F.confirmationEmailSubject AS confirmationEmailSubject';
        $selectArray['confirmationEmailSubjectLang'] = 'FI18N.confirmationEmailSubjectLang AS confirmationEmailSubjectLang';
        $selectArray['confirmationEmailContent'] = 'F.confirmationEmailContent AS confirmationEmailContent';
        $selectArray['confirmationEmailContentLang'] = 'FI18N.confirmationEmailContentLang AS confirmationEmailContentLang';
        $selectArray['acceptanceEmailSubject'] = 'F.acceptanceEmailSubject AS acceptanceEmailSubject';
        $selectArray['acceptanceEmailSubjectLang'] = 'FI18N.acceptanceEmailSubjectLang AS acceptanceEmailSubjectLang';
        $selectArray['acceptanceEmailContent'] = 'F.acceptanceEmailContent AS acceptanceEmailContent';
        $selectArray['acceptanceEmailContentLang'] = 'FI18N.acceptanceEmailContentLang AS acceptanceEmailContentLang';
        $selectArray['dismissalEmailSubject'] = 'F.dismissalEmailSubject AS dismissalEmailSubject';
        $selectArray['dismissalEmailSubjectLang'] = 'FI18N.dismissalEmailSubjectLang AS dismissalEmailSubjectLang';
        $selectArray['dismissalEmailContent'] = 'F.dismissalEmailContent AS dismissalEmailContent';
        $selectArray['dismissalEmailContentLang'] = 'FI18N.dismissalEmailContentLang AS dismissalEmailContentLang';
        $selectArray['completionPromptSuccessMessage'] = 'F.completionPromptSuccessMessage AS completionPromptSuccessMessage';
        $selectArray['completionPromptSuccessMessageLang'] = 'FI18N.successMessageLang AS completionPromptSuccessMessageLang';
        $selectArray['lang'] = 'FI18N.lang AS lang';

        $query = $this->createQueryBuilder('F')
            ->select(implode(',', $selectArray))
            ->leftJoin('CommonUtilityBundle:FgCmsFormsI18n', 'FI18N', 'WITH', 'F.id = FI18N.id')
            ->where('F.id =:formId')
            ->setParameter('formId', $formId);
        $resultArray = $query->getQuery()->getArrayResult();

        foreach ($resultArray as $result) {
            $dataArray['senderemail'] = $result['confirmationEmailSender'];
            $dataArray['recipients'] = $result['notificationEmailRecipients'];
            $dataArray['subject'][$result['lang']] = $result['confirmationEmailSubjectLang'];
            $dataArray['subject']['default'] = $result['confirmationEmailSubject'];
            $dataArray['content'][$result['lang']] = $result['confirmationEmailContentLang'];
            $dataArray['content']['default'] = $result['confirmationEmailContent'];
            $dataArray['successmessagemain'] = $result['completionPromptSuccessMessage'];
            $dataArray['successmessage'][$result['lang']] = $result['completionPromptSuccessMessageLang'];
        }

        if ($dataArray['recipients'] != '') {
            $dataArray['recipientlist'] = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($dataArray['recipients'], '', $clubObj, $container);
        }
        return $dataArray;
    }

    /**
     * Function to check if the for form with same name already exists
     * 
     * @param type $formType The form type
     * @param type $clubId The club Id
     * @param type $title The title of the form
     * @param type $excludeFormId The id of the form which is to be excluded(on edit)
     *
     * return int
     */
    public function checkIfNameExists($formType, $clubId, $title, $excludeFormId = '')
    {
        $qb = $this->createQueryBuilder('F');
        $qb->select('COUNT(F.id) as duplicateCount')
            ->where('F.formType=:formType')
            ->andWhere('LOWER(F.title)=:title')
            ->andWhere('F.club=:clubId')
            ->setParameters(array('formType' => $formType, 'clubId' => $clubId, 'title' => mb_strtolower(trim($title), 'UTF-8')));

        if ($excludeFormId != '') {
            $qb->andWhere('F.id!=:excludeFormId')
                ->setParameter('excludeFormId', $excludeFormId);
        }
        $duplicate = $qb->getQuery()->getOneOrNullResult();
        return $duplicate['duplicateCount'];
    }

    /**
     * Function to get all existing forms
     * @param type $formType The form type
     * @param type $clubId The id of the current club
     *
     * return array
     */
    public function getExistingForms($formType, $clubId)
    {
        $qb = $this->createQueryBuilder('F');
        $qb->select('F.id as formId, F.title as title, CE.isDeleted as isDeleted')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'CE', 'WITH', 'CE.form = F.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormInquiries', 'FI', 'WITH', 'FI.element = CE.id')
            ->where('F.formType=:formType')
            ->andWhere('F.club=:clubId')
            ->andWhere('F.formStage=:stage')
            ->having('CE.isDeleted=0 OR (CE.isDeleted=1 AND count(FI.id) >0)')
            ->setParameters(array('formType' => $formType, 'clubId' => $clubId, 'stage' => 'stage3'))
            ->groupBy('F.id')
            ->orderBy('F.title');
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * active forms titles to be listed in dropdown - stage3 with form type contact field
     * 
     * @param int        $clubId    club Id
     * @param boolean    $countFlag whether to display count Flag
     * 
     * @return array/int depending on flag
     */
    public function activeContactFormAppList($clubId, $countFlag)
    {

        $qs = $this->createQueryBuilder('f')
            ->select('f.title as title,f.id as id')
            ->where('f.club =:clubId')
            ->andWhere('f.formType = :formtype')
            ->andWhere('f.isActive = 1')
            ->andWhere('f.isDeleted = 0')
            ->andWhere('f.formStage = :stage')
            ->setParameters(array('clubId' => $clubId, 'formtype' => 'contact_field', 'stage' => 'stage3'))
            ->orderBy('f.title')
            ->getQuery()
            ->getArrayResult();

        $result = $qs; //array_column($qs, 'title');

        if ($countFlag) {
            $count = count($result);
            if ($count == 1) {
                $result = array('id' => $qs[0]['id'], 'count' => $count);
            } else {
                $result = array('id' => '', 'count' => $count);
            }
        }
        return $result;
    }

    /**
     * This function is used to get contact application forms.
     * 
     * @param int       $clubId         club id
     * @param string    $lang           club language
     * 
     * @return array contact application form details
     */
    public function getContactApplicationFormList($clubId, $lang, $frontEnd = '')
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $qb = $this->createQueryBuilder('c');
        $qb->select("c.id AS id, c.title as title, c.isActive as isActive, contactName(c.updatedBy) as updatedBy, checkActiveContact(c.updatedBy, :clubId) as activeContactId, DATE_FORMAT(c.updatedAt, '$datetimeFormat') as lastUpdated, c.contactFormType AS contactFormType, c.formStage AS formStage")
            ->leftJoin('CommonUtilityBundle:FgCmsFormsI18n', 'ci18', 'WITH', 'ci18.id = c.id AND ci18.id =:lang')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'fcc', 'WITH', 'fcc.id = c.updatedBy')
            ->where('c.club=:clubId')
            ->andWhere('c.formType=:formType')
            ->andWhere('c.isDeleted != 1')
            ->andWhere('c.isDeleted != 1')
            ->orderBy('c.title', 'asc');
        if ($frontEnd) {
            $qb->andWhere("c.formStage = 'stage3'");
            $qb->andWhere("c.isActive = '1'");
        }
        $qb->setParameters(array('clubId' => $clubId, 'lang' => $lang, 'formType' => 'contact_field'));
        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Function to get the form elements with all details
     * @param int    $formId       Form element id
     * @param int    $forFrontView For front end or not
     *
     * @return array
     */
    public function getFormDetails($formId, $forFrontView = 0)
    {
        $selectArray = array();
        $selectArray['formName'] = 'F.title AS formName';
        $selectArray['formStage'] = 'F.formStage AS formStage';
        $selectArray['formClub'] = 'IDENTITY(F.club) AS formClub';
        $selectArray['formElementId'] = 'E.id AS formElementId';
        $selectArray['formFieldId'] = 'EFF.id AS formFieldId';
        $selectArray['formId'] = 'F.id AS formId';
        $selectArray['isFieldHiddenWithDefaultValue'] = 'EFF.isFieldHiddenWithDefaultValue AS isFieldHiddenWithDefaultValue';
        $selectArray['fieldName'] = "CASE WHEN EFF.formFieldType = 'contact' THEN CA.fieldname ELSE EFF.fieldname END AS fieldName";
        $selectArray['fieldCategory'] = 'EFF.formFieldType as fieldCategory'; //'contact', 'form', 'club-membership'
        $selectArray['fieldType'] = "CASE WHEN EFF.formFieldType = 'contact' THEN CA.inputType ELSE EFF.fieldType END AS fieldType";
        $selectArray['attributeId'] = "CASE WHEN EFF.formFieldType = 'contact' THEN IDENTITY(EFF.attribute) ELSE '' END AS attributeId";
        $selectArray['attributeSetId'] = "CASE WHEN EFF.formFieldType = 'contact' THEN IDENTITY(CA.attributeset) ELSE '' END AS attributeSetId";
        $selectArray['predefinedValue'] = " EFF.predefinedValue AS predefinedValue";
        $selectArray['placeholder'] = 'EFF.placeholderValue AS placeholder';
        $selectArray['tooltipValue'] = 'EFF.tooltipValue AS tooltipValue';
        $selectArray['isRequired'] = 'EFF.isRequired AS isRequired';
        $selectArray['formElementSortOrder'] = 'EFF.sortOrder AS formElementSortOrder';
        $selectArray['formElementIsActive'] = 'EFF.isActive AS formElementIsActive';
        $selectArray['formElementIsDeleted'] = 'EFF.isDeleted AS formElementIsDeleted';
        $selectArray['numberMinValue'] = 'EFF.numberMinValue AS numberMinValue';
        $selectArray['numberMaxValue'] = 'EFF.numberMaxValue AS numberMaxValue';
        $selectArray['numberStepValue'] = 'EFF.numberStepValue AS numberStepValue';
        $selectArray['dateMin'] = 'EFF.dateMin AS dateMin';
        $selectArray['dateMax'] = 'EFF.dateMax AS dateMax';
        $selectArray['showSelectionValuesInline'] = 'EFF.showSelectionValuesInline AS showSelectionValuesInline';
        $selectArray['isMultiSelectable'] = 'EFF.isMultiSelectable AS isMultiSelectable';
        $selectArray['useMailForNotification'] = 'EFF.useMailForNotification AS useMailForNotification';
        $selectArray['fieldLang'] = 'EFFI18n.lang AS fieldLang';
        $selectArray['fieldNameI18n'] = "CASE WHEN EFF.formFieldType = 'contact' THEN CAI18n.fieldnameLang ELSE EFFI18n.fieldnameLang END AS fieldNameI18n";
        $selectArray['predefinedValueI18n'] = 'EFFI18n.predefinedValueLang AS predefinedValueI18n';
        $selectArray['placeholderValueI18n'] = 'EFFI18n.placeholderValueLang AS placeholderValueI18n';
        $selectArray['tooltipValueI18n'] = 'EFFI18n.tooltipValueLang AS tooltipValueI18n';
        $selectArray['optionId'] = 'EFFO.id AS optionId';
        $selectArray['formElementOptionIsActive'] = 'EFFO.isActive AS formElementOptionIsActive';
        $selectArray['formElementOptionIsDeleted'] = 'EFFO.isDeleted AS formElementOptionIsDeleted';
        $selectArray['formElementSelectionValueName'] = 'EFFO.selectionValueName AS formElementSelectionValueName';
        $selectArray['formElementOptionSortOrder'] = 'EFFO.sortOrder AS formElementOptionSortOrder';
        $selectArray['optionLang'] = 'EFFOI18n.lang AS optionLang';
        $selectArray['formElementSelectionValueNameI18n'] = 'EFFOI18n.selectionValueNameLang AS formElementSelectionValueNameI18n';
        $selectArray['formattributeId'] = 'IDENTITY(EFF.attribute) AS attribute';
        $selectArray['isFieldHiddenWithDefaultValue'] = 'EFF.isFieldHiddenWithDefaultValue AS isFieldHiddenWithDefaultValue';
        $selectArray['clubMembershipId'] = 'CM.id AS clubMembershipId';
        $selectArray['clubMembershipTitle'] = 'CM.title AS clubMembershipTitle';
        $selectArray['clubMembershipSortOrder'] = 'CM.sortOrder AS clubMembershipSortOrder';
        $selectArray['clubMembershipSelection'] = 'EFF.clubMembershipSelection AS clubMembershipSelection';
        $selectArray['defaultClubMembership'] = 'IDENTITY(EFF.defaultClubMembership) AS defaultClubMembership';
        $selectArray['mandatoryPredefinedValue'] = 'CA.predefinedValue AS mandatoryPredefinedValue';
        $qb = $this->createQueryBuilder('F');
        $qb->select(implode(',', $selectArray))
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFields', 'EFF', 'WITH', 'EFF.form = F.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.form = F.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n', 'EFFI18n', 'WITH', "EFFI18n.id = EFF.id")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions', 'EFFO', 'WITH', ($forFrontView) ? 'EFFO.field = EFF.id' . " AND EFFO.isActive=1 AND EFFO.isDeleted=0 " : 'EFFO.field = EFF.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n', 'EFFOI18n', 'WITH', "EFFOI18n.id = EFFO.id")
            ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'CA', 'WITH', 'CA.id = EFF.attribute')
            ->leftJoin('CommonUtilityBundle:FgCmAttributeI18n', 'CAI18n', 'WITH', "CAI18n.id = CA.id and CAI18n.lang = EFFI18n.lang")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementMembershipSelections', 'EMS', 'WITH', 'EMS.field = EFF.id')
            ->leftJoin('CommonUtilityBundle:FgCmMembership', 'CM', 'WITH', 'CM.id = EMS.membership')
            ->where('F.id=:formId')
            ->andWhere('EFF.isDeleted = 0 OR EFF.isDeleted IS NULL')
            ->andWhere('(EFFO.isDeleted = 0 OR EFFO.isDeleted IS NULL)');
        if ($forFrontView == '1' || $forFrontView == '2') {
            $qb->andWhere("(EFF.isActive=1 OR EFF.isActive IS NULL) AND (EFFO.isActive=1 OR EFFO.isActive IS NULL)");
        }
        if ($forFrontView == '2') {
            $qb->andWhere("F.formStage = 'stage3'");
        }


        $qb->orderBy('EFFO.sortOrder', 'ASC')
            ->orderBy('EFF.sortOrder', 'ASC')
            ->setParameters(array('formId' => $formId));

        return $qb->getQuery()->getArrayResult();
    }

    /**
     *
     * The function to save the form element to the DB
     *
     * @param int    $formId The id of the form that has been edited
     * @param string $stage  The stage
     *
     * @return int
     */
    public function saveFormElementStage($formId, $stage)
    {
        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if ($formObj != '') {
            $currentStage = $formObj->getFormStage();
            //Need to check 'stage1' and 'stage2'
            //The logic is to replace the 'stage' substring from the data and check it
            if (str_replace('stage', '', $currentStage) < str_replace('stage', '', $stage)) {
                $formObj->setFormStage($stage);
                $this->_em->persist($formObj);
                $this->_em->flush();
            }
        }
        return;
    }

    /**
     * This function is used to activate/deactivate contact application form
     * 
     * @param integer $formId contact application form id
     * 
     * @return array  $returnArray    array with form active details 
     */
    public function activateConatactApplicationForm($formId)
    {
        $contactAppFormObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);

        if ($contactAppFormObj->getIsActive() == 0) {
            $contactAppFormObj->setIsActive(1);
        } else {
            $contactAppFormObj->setIsActive(0);
        }

        $this->_em->flush();
        $returnArray['stage'] = $contactAppFormObj->getFormStage();
        $returnArray['isActive'] =  $contactAppFormObj->getIsActive();
        $returnArray['formId'] =  $formId;
        
        return $returnArray;
    }

    /**
     * This function is used to delete contact application form
     * 
     * @param integer $formId contact application form id
     * 
     * @return void 
     */
    public function deleteConatactApplicationForm($formId)
    {
        $contactAppFormObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        $qb = $this->_em->createQueryBuilder();
        $qb->delete('CommonUtilityBundle:FgCmsPageContentElement', 'ce');
        $qb->where('ce.form = :formId');
        $qb->setParameter('formId', $formId);
        $query = $qb->getQuery();
        $query->execute();
        if ($contactAppFormObj) {
            $contactAppFormObj->setIsDeleted(1);
            $this->_em->persist($contactAppFormObj);
            $this->_em->flush();
        }
    }

    /**
     * This function is used to save stage 2 of contact application form set up wizard 
     * 
     * @param int   $formId   The form id
     * @param array $formData Form details to be saved
     */
    public function saveContactFormStage2($formId, $formData)
    {
        $clubDefaultLang = $formData['clubDefaultLang'];
        $confirmationEmailSubject = FgUtility::getSecuredDataString(trim($formData['confirmationEmailSubject'][$clubDefaultLang]), $this->_em->getConnection());
        $confirmationEmailContent = FgUtility::getSecuredDataString(trim($formData['confirmationEmailContent'][$clubDefaultLang]), $this->_em->getConnection());
        $acceptanceEmailSubject = FgUtility::getSecuredDataString(trim($formData['acceptanceEmailSubject'][$clubDefaultLang]), $this->_em->getConnection());
        $acceptanceEmailContent = FgUtility::getSecuredDataString(trim($formData['acceptanceEmailContent'][$clubDefaultLang]), $this->_em->getConnection());
        $dismissalEmailSubject = FgUtility::getSecuredDataString(trim($formData['dismissalEmailSubject'][$clubDefaultLang]), $this->_em->getConnection());
        $dismissalEmailContent = FgUtility::getSecuredDataString(trim($formData['dismissalEmailContent'][$clubDefaultLang]), $this->_em->getConnection());
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $formData['contactId']);

        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if ($formObj != '') {
            $formObj->setConfirmationEmailSender($formData['confirmationEmailSender']);
            $formObj->setConfirmationEmailSubject($confirmationEmailSubject);
            $formObj->setConfirmationEmailContent($confirmationEmailContent);
            $formObj->setNotificationEmailRecipients($formData['notificationEmailRecipients']);
            $formObj->setAcceptanceEmailSender($formData['acceptanceEmailSender']);
            $formObj->setAcceptanceEmailSubject($acceptanceEmailSubject);
            $formObj->setAcceptanceEmailContent($acceptanceEmailContent);
            $formObj->setIsAcceptanceEmailActive($formData['deactivateAcceptanceEmail'] ? 0 : 1);
            $formObj->setDismissalEmailSender($formData['dismissalEmailSender']);
            $formObj->setDismissalEmailSubject($dismissalEmailSubject);
            $formObj->setDismissalEmailContent($dismissalEmailContent);
            $formObj->setIsDismissalEmailActive($formData['deactivateDismissalEmail'] ? 0 : 1);
            $formObj->setUpdatedAt(new \DateTime("now"));
            $formObj->setUpdatedBy($contactObj);
            $this->_em->persist($formObj);
            $this->_em->flush();
        }
    }

    /**
     * This function is used to save stage 3 of contact application form setup wizard
     * 
     * @param int    $formId    The form id
     * @param string $message   Success message to be saved
     * @param int    $contactId The contact id
     */
    public function saveContactFormStage3($formId, $message, $contactId)
    {
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        if (!empty($formObj)) {
            $successMessage = FgUtility::getSecuredDataString(trim($message), $this->_em->getConnection());
            $formObj->setCompletionPromptSuccessMessage($successMessage);
            $formObj->setUpdatedAt(new \DateTime("now"));
            $formObj->setUpdatedBy($contactObj);
            $formObj->setIsActive(1);
            $this->_em->persist($formObj);
            $this->_em->flush();
        }
    }

    /**
     * This function is used to duplicate the contact form data
     *  
     * @param int   $formId    Form id
     * @param int   $contactId Contact id
     * @param array $params    Params array
     * 
     * @return int New form object's id 
     */
    public function duplicateContactFormData($formId, $contactId, $params)
    {
        $formObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsForms')->find($formId);
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        $newFormObj = clone $formObj;
        $newFormTitle = $this->getNewFormTitle($formObj->getClub()->getId(), $formObj->getTitle(), $params['copyOfTrans']);
        $newFormObj->setTitle($newFormTitle);
        $isActive = ($formObj->getFormStage() == 'stage3') ? $formObj->getIsActive() : 0;
        $newFormObj->setIsActive($isActive);
        $newFormObj->setIsDeleted(0);
        $newFormObj->setCreatedAt(new \DateTime("now"));
        $newFormObj->setCreatedBy($contactObj);
        $newFormObj->setUpdatedAt(new \DateTime("now"));
        $newFormObj->setUpdatedBy($contactObj);
        $this->_em->persist($newFormObj);
        $this->_em->flush();

        return $newFormObj->getId();
    }

    /**
     * This function is used to get the new unique form name
     * 
     * @param int    $clubId Club id
     * @param string $title  Form title
     * @param string $copyOf Translation of 'Copy of'
     * 
     * @return string $newTitle New form title
     */
    private function getNewFormTitle($clubId, $title, $copyOf)
    {
        $isUnique = 0;
        $newTitle = '';
        while ($isUnique != 1) {
            $title = ($newTitle != '') ? $newTitle : $title;
            $newTitle = "$copyOf " . $title;
            $isUnique = $this->checkWhetherFormNameIsUniqueInAClub($clubId, $newTitle);
        }

        return $newTitle;
    }

    /**
     * This function is used to check whether the form name is unique in a club
     * 
     * @param int    $clubId   Club id
     * @param string $formName Form name
     * @param int    $formId   Form id to exclude unique checking
     * 
     * @return int Forms name is unique or not
     */
    public function checkWhetherFormNameIsUniqueInAClub($clubId, $formName, $formId = 0)
    {
        $params = array('clubId' => $clubId, 'formtype' => 'contact_field', 'newFormName' => strtolower($formName));
        $qb = $this->createQueryBuilder('f');
        $formCount = $qb->select('count(f.id)')
            ->where('f.club = :clubId')
            ->andWhere('f.formType = :formtype')
            ->andWhere('f.isDeleted = 0')
            ->andWhere($qb->expr()->eq('lower(f.title)', ':newFormName'));
        if ($formId != 0) {
            $params['formId'] = $formId;
            $formCount = $formCount->andWhere('f.id != :formId');
        }
        $formCount = $formCount->setParameters($params)
            ->getQuery()
            ->getSingleScalarResult();

        return ($formCount == 0) ? 1 : 0;
    }
    /*
     * Function to save the contact form primary data to the database
     * 
     * @param string  $formTitle The form name data that needed to be inserted/updated
     * @param object  $clubObj The club service object
     * @param object  $contactObj The contact service object
     * @param string  $contactType Type of contact form
     * 
     * @return int 
     */

    public function saveContactForm($formTitle, $clubId, $contactId, $contactType)
    {
        $formObj = new \Common\UtilityBundle\Entity\FgCmsForms();
        $formObj->setClub($this->_em->getReference('CommonUtilityBundle:FgClub', $clubId));
        $formObj->setUpdatedBy($this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId));
        $formObj->setUpdatedAt(new \DateTime('now'));
        $formObj->setCreatedBy($this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId));
        $formObj->setCreatedAt(new \DateTime('now'));
        $formObj->setTitle($formTitle);
        $formObj->setContactFormType($contactType);
        $formObj->setFormStage('stage0');
        $formObj->setFormType('contact_field');

        $formObj->setIsAcceptanceEmailActive(1);
        $formObj->setIsDismissalEmailActive(1);
        $formObj->setIsDeleted(0);
        $formObj->setIsActive(0);
        $this->_em->persist($formObj);
        $this->_em->flush();
        return $formObj->getId();
    }

    /**
     * To find the details of contact form
     * @param integer $formId id of contact form
     * @param integer $clubId id of club id
     * 
     * @return array contact form details
     */
    public function getContactFormDetails($formId, $clubId)
    {
        $qb = $this->createQueryBuilder('F');
        $qb->select('F.id', 'F.title', 'F.contactFormType', 'F.formStage')
            ->where('F.id=:formId')
            ->andWhere('F.club=:clubId')
            ->setParameters(array('formId' => $formId, 'clubId' => $clubId));

        $result = $qb->getQuery()->getArrayResult();
        return $result[0];
    }

    /**
     * Function to get the form elements with all details
     * @param int       $formId         Form element id
     * @param int       $forFrontView   For front end or not
     *
     * @return array
     */
    public function getContactFormFullData($formId, $forFrontView = 0)
    {

        $selectArray = array();
        $selectArray['formName'] = 'F.title AS formName';
        $selectArray['formStage'] = 'F.formStage AS formStage';
        $selectArray['formClub'] = 'IDENTITY(F.club) AS formClub';
        $selectArray['formFieldId'] = 'EFF.id AS formFieldId';
        $selectArray['formId'] = 'F.id AS formId';
        $selectArray['fieldName'] = 'EFF.fieldname AS fieldName';
        $selectArray['fieldType'] = 'EFF.fieldType AS fieldType';
        $selectArray['predefinedValue'] = " EFF.predefinedValue AS predefinedValue";
        $selectArray['placeholder'] = 'EFF.placeholderValue AS placeholder';
        $selectArray['tooltipValue'] = 'EFF.tooltipValue AS tooltipValue';
        $selectArray['isRequired'] = 'EFF.isRequired AS isRequired';
        $selectArray['formElementSortOrder'] = 'EFF.sortOrder AS formElementSortOrder';
        $selectArray['formElementIsActive'] = 'EFF.isActive AS formElementIsActive';
        $selectArray['formElementIsDeleted'] = 'EFF.isDeleted AS formElementIsDeleted';
        $selectArray['numberMinValue'] = 'EFF.numberMinValue AS numberMinValue';
        $selectArray['numberMaxValue'] = 'EFF.numberMaxValue AS numberMaxValue';
        $selectArray['numberStepValue'] = 'EFF.numberStepValue AS numberStepValue';
        $selectArray['dateMin'] = 'EFF.dateMin AS dateMin';
        $selectArray['dateMax'] = 'EFF.dateMax AS dateMax';
        $selectArray['showSelectionValuesInline'] = 'EFF.showSelectionValuesInline AS showSelectionValuesInline';
        $selectArray['isMultiSelectable'] = 'EFF.isMultiSelectable AS isMultiSelectable';
        $selectArray['useMailForNotification'] = 'EFF.useMailForNotification AS useMailForNotification';
        $selectArray['fieldLang'] = 'EFFI18n.lang AS fieldLang';
        $selectArray['fieldNameI18n'] = 'EFFI18n.fieldnameLang AS fieldNameI18n';
        $selectArray['predefinedValueI18n'] = 'EFFI18n.predefinedValueLang AS predefinedValueI18n';
        $selectArray['placeholderValueI18n'] = 'EFFI18n.placeholderValueLang AS placeholderValueI18n';
        $selectArray['tooltipValueI18n'] = 'EFFI18n.tooltipValueLang AS tooltipValueI18n';
        $selectArray['optionId'] = 'EFFO.id AS optionId';
        $selectArray['formElementOptionIsActive'] = 'EFFO.isActive AS formElementOptionIsActive';
        $selectArray['formElementOptionIsDeleted'] = 'EFFO.isDeleted AS formElementOptionIsDeleted';
        $selectArray['formElementSelectionValueName'] = 'EFFO.selectionValueName AS formElementSelectionValueName';
        $selectArray['formElementOptionSortOrder'] = 'EFFO.sortOrder AS formElementOptionSortOrder';
        $selectArray['optionLang'] = 'EFFOI18n.lang AS optionLang';
        $selectArray['formElementSelectionValueNameI18n'] = 'EFFOI18n.selectionValueNameLang AS formElementSelectionValueNameI18n';
        $selectArray['formattributeId'] = 'IDENTITY(EFF.attribute) AS attribute';
        $selectArray['isFieldHiddenWithDefaultValue'] = 'EFF.isFieldHiddenWithDefaultValue AS isFieldHiddenWithDefaultValue';
        $selectArray['fieldnameLang'] = 'AT.fieldname AS fieldnameLang';
        $selectArray['contactFormType'] = 'F.contactFormType AS contactFormType';
        $selectArray['mandatoryInputType'] = 'AT.inputType AS mandatoryInputType';
        $selectArray['mandatoryPredefinedValue'] = 'AT.predefinedValue AS mandatoryPredefinedValue';
        $selectArray['formFieldType'] = 'EFF.formFieldType AS formFieldType';
        $selectArray['clubMembershipSelection'] = 'EFF.clubMembershipSelection AS clubMembershipSelection';
        $selectArray['defaultClubMembership'] = 'IDENTITY(EFF.defaultClubMembership) AS defaultClubMembership';
        $selectArray['clubMembershipId'] = '(SELECT GROUP_CONCAT( DISTINCT DCM.membership)  FROM  CommonUtilityBundle:FgCmsPageContentElementMembershipSelections DCM WHERE DCM.field=EMS.field) AS clubMembershipId';
        $selectArray['clubMembershipTitle'] = 'CM.title AS clubMembershipTitle';
        $selectArray['clubMembershipSortOrder'] = 'CM.sortOrder AS clubMembershipSortOrder';
        $selectArray['fieldAttributeSetId'] = 'IDENTITY(AT.attributeset) AS fieldAttributeSetId';

        $qb = $this->createQueryBuilder('F');
        $qb->select(implode(',', $selectArray))
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElementFormFields', 'EFF', 'WITH', 'EFF.form = F.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldsI18n', 'EFFI18n', 'WITH', "EFFI18n.id = EFF.id")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptions', 'EFFO', 'WITH', ($forFrontView) ? 'EFFO.field = EFF.id' . " AND EFFO.isActive=1 AND EFFO.isDeleted=0 " : 'EFFO.field = EFF.id')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementFormFieldOptionsI18n', 'EFFOI18n', 'WITH', "EFFOI18n.id = EFFO.id")
            ->leftJoin('CommonUtilityBundle:FgCmAttribute', 'AT', 'WITH', "AT.id = EFF.attribute")
            ->leftJoin('CommonUtilityBundle:FgCmAttributeI18n', 'ATI18n', 'WITH', "ATI18n.id = AT.id")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementMembershipSelections', 'EMS', 'WITH', 'EMS.field = EFF.id')
            ->leftJoin('CommonUtilityBundle:FgCmMembership', 'CM', 'WITH', 'CM.id = EMS.membership')
            ->where('F.id=:formId')
            ->andWhere('EFF.isDeleted = 0')
            ->andWhere('(EFFO.isDeleted = 0 OR EFFO.isDeleted IS NULL)');
        if ($forFrontView == '1' || $forFrontView == '2') {
            $qb->andWhere("EFF.isActive=1 AND (EFFO.isActive=1 OR EFFO.isActive IS NULL)");
        }
        if ($forFrontView == '2') {
            $qb->andWhere("F.formStage = 'stage3'");
        }


        $qb->orderBy('EFFO.sortOrder', 'ASC')
            ->orderBy('EFF.sortOrder', 'ASC')
            ->setParameters(array('formId' => $formId));

        return $qb->getQuery()->getArrayResult();
    }

    public function getContactApplicationFormMailSettings($formId, $container, $clubObj)
    {
        $dataArray = array();
        $selectArray = array();
        $selectArray['confirmationEmailSender'] = 'F.confirmationEmailSender';
        $selectArray['acceptanceEmailSender'] = 'F.acceptanceEmailSender';
        $selectArray['dismissalEmailSender'] = 'F.dismissalEmailSender';

        $selectArray['notificationEmailRecipients'] = 'F.notificationEmailRecipients';

        $selectArray['isAcceptanceEmailActive'] = 'F.isAcceptanceEmailActive';
        $selectArray['isDismissalEmailActive'] = 'F.isDismissalEmailActive';

        $selectArray['confirmationEmailSubject'] = 'F.confirmationEmailSubject AS confirmationEmailSubject';
        $selectArray['confirmationEmailSubjectLang'] = 'FI18N.confirmationEmailSubjectLang AS confirmationEmailSubjectLang';

        $selectArray['confirmationEmailContent'] = 'F.confirmationEmailContent AS confirmationEmailContent';
        $selectArray['confirmationEmailContentLang'] = 'FI18N.confirmationEmailContentLang AS confirmationEmailContentLang';

        $selectArray['acceptanceEmailSubject'] = 'F.acceptanceEmailSubject AS acceptanceEmailSubject';
        $selectArray['acceptanceEmailSubjectLang'] = 'FI18N.acceptanceEmailSubjectLang AS acceptanceEmailSubjectLang';

        $selectArray['acceptanceEmailContent'] = 'F.acceptanceEmailContent AS acceptanceEmailContent';
        $selectArray['acceptanceEmailContentLang'] = 'FI18N.acceptanceEmailContentLang AS acceptanceEmailContentLang';

        $selectArray['dismissalEmailSubject'] = 'F.dismissalEmailSubject AS dismissalEmailSubject';
        $selectArray['dismissalEmailSubjectLang'] = 'FI18N.dismissalEmailSubjectLang AS dismissalEmailSubjectLang';

        $selectArray['dismissalEmailContent'] = 'F.dismissalEmailContent AS dismissalEmailContent';
        $selectArray['dismissalEmailContentLang'] = 'FI18N.dismissalEmailContentLang AS dismissalEmailContentLang';

        $selectArray['completionPromptSuccessMessage'] = 'F.completionPromptSuccessMessage AS completionPromptSuccessMessage';
        $selectArray['completionPromptSuccessMessageLang'] = 'FI18N.successMessageLang AS completionPromptSuccessMessageLang';

        $selectArray['lang'] = 'FI18N.lang AS lang';

        $query = $this->createQueryBuilder('F')
            ->select(implode(',', $selectArray))
            ->leftJoin('CommonUtilityBundle:FgCmsFormsI18n', 'FI18N', 'WITH', 'F.id = FI18N.id')
            ->where('F.id =:formId')
            ->setParameter('formId', $formId);
        $resultArray = $query->getQuery()->getArrayResult();

        foreach ($resultArray as $result) {
            $dataArray['confirmationSenderEmail'] = $result['confirmationEmailSender'];
            $dataArray['acceptanceSenderEmail'] = $result['acceptanceEmailSender'];
            $dataArray['dismissalSenderEmail'] = $result['dismissalEmailSender'];
            $dataArray['recipients'] = $result['notificationEmailRecipients'];
            $dataArray['confirmationSubject'][$result['lang']] = $result['confirmationEmailSubjectLang'];
            $dataArray['confirmationSubject']['default'] = $result['confirmationEmailSubject'];
            $dataArray['confirmationContent'][$result['lang']] = $result['confirmationEmailContentLang'];
            $dataArray['confirmationContent']['default'] = $result['confirmationEmailContent'];
            $dataArray['acceptanceSubject'][$result['lang']] = $result['acceptanceEmailSubjectLang'];
            $dataArray['acceptanceSubject']['default'] = $result['acceptanceEmailSubject'];
            $dataArray['acceptanceContent'][$result['lang']] = $result['acceptanceEmailContentLang'];
            $dataArray['acceptanceContent']['default'] = $result['acceptanceEmailContent'];
            $dataArray['isAcceptanceEmailActive'] = ($result['isAcceptanceEmailActive'] ? 1 : 0);
            $dataArray['dismissalSubject'][$result['lang']] = $result['dismissalEmailSubjectLang'];
            $dataArray['dismissalSubject']['default'] = $result['dismissalEmailSubject'];
            $dataArray['dismissalContent'][$result['lang']] = $result['dismissalEmailContentLang'];
            $dataArray['dismissalContent']['default'] = $result['dismissalEmailContent'];
            $dataArray['isDismissalEmailActive'] = ($result['isDismissalEmailActive'] ? 1 : 0);
            $dataArray['successmessage'][$result['lang']] = $result['completionPromptSuccessMessageLang'];
        }
        if ($dataArray['recipients'] != '') {
            $dataArray['recipientlist'] = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getContactName($dataArray['recipients'], '', $clubObj, $container);
        }

        return $dataArray;
    }
}
