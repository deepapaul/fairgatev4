<?php
/**
 * FgCmsPageContentElementFormInquiriesRepository
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgCmsPageContentElementFormInquiriesRepository - to handle methods of from inquiries
 *
 * @package         package
 * @subpackage      subpackahe
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgCmsPageContentElementFormInquiriesRepository extends EntityRepository
{

    /**
     * Method to check whether a form inquiry exist in that club
     *
     * @param int $clubId clubId
     *
     * @return boolean true/false
     */
    public function hasFormInquiries($clubId)
    {
        $pages = $this->createQueryBuilder('FI')
            ->select("COUNT(FI.id) as inquiryCount")
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.id = FI.element ')
            ->innerJoin('CommonUtilityBundle:FgClub', 'CLUB', 'WITH', 'CLUB.id = E.club ')
            ->where('CLUB.id = :clubId')
            ->setParameters(array('clubId' => $clubId));
        $result = $pages->getQuery()->getSingleResult();

        return ($result['inquiryCount'] == 0) ? false : true;
    }

    /**
     * Method to get list of form inquiries
     *
     * @param int      $clubId      clubId
     * @param string   $guestTrans  translation of guest
     * @param int|null $elementId   formId (if elementId, get results of particluar form)
     * @param string   $contactLang contact-correspondence-language
     *
     * @return array of form inquiries
     */
    public function getFormInquiries($clubId, $guestTrans, $elementId, $contactLang)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('checkActiveContact', 'Common\UtilityBundle\Extensions\CheckActiveContact');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');

        $parameters = array('clubId' => $clubId, 'contactLang' => $contactLang);
        $pages = $this->createQueryBuilder('FI')
            ->select("FI.id as fiId, F.title AS formTitle,DATE_FORMAT(FI.createdAt, '$datetimeFormat') as createdAt, E.id as elementId, IDENTITY(FI.contact) as contactId, CheckActiveContact(FI.contact, :clubId) as activeContactId, CASE WHEN(FI.contact IS NULL OR FI.contact = '') THEN '$guestTrans' ELSE contactName(IDENTITY(FI.contact))  END as contactName, FI.formData, fcc.isStealthMode as isStealth, "
                . "CASE WHEN (IDENTITY(E.box) IS NOT NULL AND IDENTITY(E.box) != '' AND E.isDeleted = 0 AND E.deletedAt IS NULL) THEN '1' ELSE '0' END as isActive")
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.id = FI.element ')
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = E.form ')
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElementI18n', 'ELANG', 'WITH', 'E.id = ELANG.id AND ELANG.lang = :contactLang ')
            ->innerJoin('CommonUtilityBundle:FgClub', 'CLUB', 'WITH', 'CLUB.id = E.club ')
            ->leftJoin('CommonUtilityBundle:FgCmContact', 'fcc', 'WITH', 'fcc.id = FI.contact')
            ->where('CLUB.id = :clubId');
        if ($elementId) {
            $pages->andWhere('E.id = :elementId');
            $parameters['elementId'] = $elementId;
        }
        $pages->setParameters($parameters);

        return $pages->getQuery()->getArrayResult();
    }

    /**
     * Method to delete form inquiries
     *
     * @param array $inquiries form inquiry ids
     * @param array $elementId form element id
     *
     * @return void
     */
    public function deleteFormInquiries($inquiries, $elementId)
    {
        if (count($inquiries) > 0) {
            foreach ($inquiries as $id) {
                $formInquiriesObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->find($id);
                if ($formInquiriesObj) {
                    $this->_em->remove($formInquiriesObj);
                }
            }
            $this->_em->flush();
        } else {
            $qb = $this->getEntityManager()->createQueryBuilder();
            $qb->delete('CommonUtilityBundle:FgCmsPageContentElementFormInquiries', 'EF');
            $qb->where('EF.element = :elementId');
            $qb->setParameter('elementId', $elementId);
            $query = $qb->getQuery();
            $query->execute();
        }
    }

    /**
     * Method to get all form inquiry count
     *
     * @param  int $clubId current club id
     *
     * @return int $count all inquiry count of a club
     */
    public function getAllFormInquiryCount($clubId)
    {
        $contentTypeObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsPageContentType')->findOneBy(array('type' => 'form'));
        $contentTypeId = $contentTypeObj->getId();

        $inquiryCount = $this->createQueryBuilder('i')
            ->select("COUNT(i.id) as inquiryCount")
            ->leftJoin('CommonUtilityBundle:FgCmsPageContentElement', 'c', 'WITH', 'i.element = c.id')
            ->where('c.club=:clubId AND c.pageContentType =:contentTypeId ')
            ->setParameters(array('clubId' => $clubId, 'contentTypeId' => $contentTypeId));

        return $inquiryCount->getQuery()->getSingleScalarResult();
    }

    /**
     * Method to get list of form inquiries datas (only filled datas from user side) and form title
     *
     * @param int      $clubId      clubId
     * @param int|null $elementId   formId (if elementId, get results of particluar form)
     * @param string   $contactLang contact-correspondence-language
     * @param string   $inquiryIds  comma separated inquiry Ids
     *
     * @return array of form inquiries
     */
    public function getFormInquiryDatas($clubId, $elementId, $contactLang, $inquiryIds = '')
    {
        $pages = $this->createQueryBuilder('FI')
            ->select("FI.formData, F.title as formTitle ")
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.id = FI.element ')
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'F', 'WITH', 'F.id = E.form ')
            ->innerJoin('CommonUtilityBundle:FgClub', 'CLUB', 'WITH', 'CLUB.id = E.club ')
            ->where('CLUB.id = :clubId AND E.id = :elementId');
        if ($inquiryIds) {
            $pages->andWhere("FI.id IN ($inquiryIds) ");
        }
        $pages->setParameters(array('clubId' => $clubId, 'elementId' => $elementId));
        return $pages->getQuery()->getArrayResult();
    }

    /**
     * Function to save form inquiry
     * @param string $inquiry   JSON string of form inquiry
     * @param int    $elementId Element id
     * @param int    $contactId Contact id
     *
     * @return int inquiry Id
     */
    public function saveFormInquiry($inquiry, $elementId, $contactId = 0)
    {
        $elementIdObj = $this->_em->getReference('CommonUtilityBundle:FgCmsPageContentElement', $elementId);
        $formInquiry = new \Common\UtilityBundle\Entity\FgCmsPageContentElementFormInquiries();
        $formInquiry->setElement($elementIdObj);
        $formInquiry->setFormData($inquiry);
        $formInquiry->setCreatedAt(new \DateTime("now"));
        if ($contactId) {
            $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
            $formInquiry->setContact($contactObj);
        }
        $this->_em->persist($formInquiry);
        $this->_em->flush();

        return $formInquiry->getId();
    }

    /**
     * Method to get array of form title in different language, inquiry time stamp, inquired username
     *
     * @param int      $clubId      clubId
     * @param string   $guestTrans  translation of guest
     * @param int      $inquiryId   inquiryId
     *
     * @return array with keys (formTitle, timestamp, contactName)
     */
    public function getFormInquiryDetails($clubId, $guestTrans, $inquiryId)
    {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');
        $pages = $this->createQueryBuilder('FI')
            ->select("FI.id as FiId, FORM.title as defaultTitle, FI.createdAt, IDENTITY(FI.contact) as contactId, "
                . "CASE WHEN(FI.contact IS NULL OR FI.contact = '') THEN '$guestTrans' ELSE contactName(IDENTITY(FI.contact))  END as contactName ")
            ->innerJoin('CommonUtilityBundle:FgCmsPageContentElement', 'E', 'WITH', 'E.id = FI.element ')
            ->innerJoin('CommonUtilityBundle:FgCmsForms', 'FORM', 'WITH', 'FORM.id = E.form')
            ->where('E.club = :clubId AND FI.id = :inquiryId');
        $pages->setParameters(array('clubId' => $clubId, 'inquiryId' => $inquiryId));

        $inquiryDetails = $pages->getQuery()->getArrayResult();
        $resultArray = array();
        foreach ($inquiryDetails as $inquiryDetail) {
            $resultArray['formTitle']['default'] = $inquiryDetail['defaultTitle'];
            $resultArray['timestamp'] = $inquiryDetail['createdAt']->format('Y-m-d H:i:s');
            $resultArray['contactName'] = $inquiryDetail['contactName'];
        }

        return $resultArray;
    }
}
