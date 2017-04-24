<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Entity\FgCnNewsletterLog;
use Common\UtilityBundle\Entity\FgCnNewsletterPublishLang;
use Common\UtilityBundle\Util\FgSettings;
use Internal\ArticleBundle\Util\ArticleData;

/**
 * This repository is used for handling newsletter functionality
 *
 *
 */
class FgCnNewsletterRepository extends EntityRepository {

    /**
     * Function to get newsletter contents for preview
     *
     * @param int $clubId clubId
     * @param int $newsletterId newsletterId
     *
     * @return array
     */
    public function getNewsletterDetailsForPreview($clubId, $newsletterId, $showInactive = false) {
        $conn = $this->getEntityManager()->getConnection();
        $dateFormat = FgSettings::getMysqlDateFormat();
        $showInActiveQuery = $showInactive ? "1" : "NC.is_active=1";
        $sql = "SELECT NC.id as contentId, N.step,NC.article_id ,NC.article_lang,NC.include_attachments as  isTakeover , N.publish_type, DATE_FORMAT(N.send_date,'$dateFormat') as sendDate,N.sender_name as senderName,N.sender_email as senderEmail,N.subject,N.club_id,N.id as newsletterId,N.template_id,N.salutation_type,N.salutation,N.is_hide_table_contents,"
                . "NC.content_type,NC.id as contentId,NC.picture_position,NC.image_link, NC.intro_closing_words,NC.is_active as isActive,NC.sort_order,"
                . "NA.title as articleTitle,NA.id as articleId,NA.teaser_text,NA.content,"
                . "NAM.gallery_item_id,NAM.id as mediaId,NAM.description,NAM.media_type, "
                . "CASE WHEN NAM.media_type = 'attachments' THEN FM.encrypted_filename ELSE ITNAM.filepath END as media_text, "
                . "FM.virtual_filename as virtualname, FMV.filename as articleAttachmentName, "
                . "CASE WHEN NAM.media_type = 'attachments' THEN FMV.size ELSE ITNAM.file_size END as articlefileSize,  " //NAM.media_text,                
                . "ITNC.filepath as image_path, ITNC.file_size as imagefileSize, "   //NC.image_path,
                . "NAD.doc_type,NAD.id as docId,NAD.filename,NAD.title, "
                . "NC.sponsor_ad_area_id, NC.sponsor_ad_width, NC.content_title, "
                . "C.has_nl_fairgatelogo as hasFairgateLogo "
                . "FROM fg_cn_newsletter N "
                . "LEFT JOIN fg_cn_newsletter_content NC ON NC.newsletter_id=N.id AND $showInActiveQuery "
                . "LEFT JOIN fg_cn_newsletter_article NA ON NA.content_id=NC.id "
                . "LEFT JOIN fg_cn_newsletter_article_media NAM ON NAM.article_id=NA.id "
                . "LEFT JOIN fg_file_manager FM ON FM.id = NAM.file_manager_id "
                . "LEFT JOIN fg_file_manager_version FMV ON FMV.id = FM.latest_version_id "
                . "LEFT JOIN fg_gm_items ITNC ON ITNC.id = NC.items_id "
                . "LEFT JOIN fg_gm_items ITNAM ON ITNAM.id = NAM.gallery_item_id "
                . "LEFT JOIN fg_cn_newsletter_article_documents NAD ON NAD.article_id=NA.id "
                . "LEFT JOIN fg_club C ON C.id=N.club_id "
                . "WHERE N.id=:newsletterId AND N.club_id=:clubId ORDER BY NC.sort_order,FMV.filename,NAD.sort_order ASC";
        $stmt = $conn->executeQuery($sql, array('newsletterId' => $newsletterId, 'clubId' => $clubId));
        $result = $stmt->fetchAll(\PDO::FETCH_GROUP);
        
        foreach ($result as $key => $value) {
            for ($i = 0; $i < count($result[$key]); $i++) {
                if ($result[$key][$i]['content_type'] == "SPONSOR" || $result[$key][$i]['content_type'] == "SPONSOR ABOVE" || $result[$key][$i]['content_type'] == "SPONSOR BOTTOM") {
                    $result[$key][$i]['services'] = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContentServices')->getServicesofNewsletterContent($result[$key][$i]['contentId']);
                }
            }
        }

        return $result;
    }

    /**
     * Function to build newsletter preview array
     * 
     * @param array   $newsletterDetails Newsletter content details
     * @param obj     $container         Conatiner object
     * @param boolean $contentOnly       true for create/edit newsletter, false for preview
     * @param int     $clubId            current clubId
     * @param obj     $club              club service
     * @param string  $baseUrl           Base url according to domain
     * @param string  $mode              Mode of newsletter(preview/cron/testmail)
     * 
     * @return string 
     */
    public function newsletterPreviewArrayBulider($newsletterDetails, $container, $contentOnly = false, $clubId, $club, $baseUrl = '', $mode = '') {
        $result = $contentsArray = $tableOfContent = $staticContent = array();
        if ($mode != 'cron') {
            $baseUrl = FgUtility::getBaseUrl($container);
        } else {
            $baseUrlArray = FgUtility::getMainDomainUrl($container, $clubId, $mode);
            $baseUrl = $baseUrlArray['baseUrl'];
        }
        
        $communicationUploadFolder = FgUtility::getUploadFilePath($clubId, 'communication');
        foreach ($newsletterDetails as $contentId => $newsletterContentArray) {
            $result['templateId'] = $newsletterContentArray[0]['template_id'];
            $result['hasFairgateLogo'] = $newsletterContentArray[0]['hasFairgateLogo'];
            $result['step'] = $newsletterContentArray[0]['step'];
            $result['publishType'] = $newsletterContentArray[0]['publish_type'];
            $result['sendDate'] = $newsletterContentArray[0]['sendDate'];
            $clubId = $newsletterContentArray[0]['club_id'];
            $salutation = '';
            if ($newsletterContentArray[0]['salutation_type'] == 'INDIVIDUAL') {
                $salutation = 'NL_PERSONAL_SALUTATION';
            } elseif ($newsletterContentArray[0]['salutation_type'] == 'SAME') {
                $salutation = $newsletterContentArray[0]['salutation'];
            }
            $result['salutation'] = $salutation;
            $content = array();
            foreach ($newsletterContentArray as $newsletterContent) {
                $conteId = $tableContent = '';
                switch ($newsletterContent['content_type']) {
                    case 'INTRO':
                        $introText = ($contentOnly) ? $newsletterContent['intro_closing_words'] : $newsletterContent['intro_closing_words'];
                        $introText = FgUtility::correctCkEditorUrl($introText, $container, $clubId, $mode);
                        if ($contentOnly) {
                            $staticContent['intro'] = array('type' => 'INTRO', 'text' => $introText, 'id' => $newsletterContent['contentId'], 'isActive' => $newsletterContent['isActive']);
                            $tocIsActive = ($newsletterContent['is_hide_table_contents'] == 0) ? 1 : 0;
                            $staticContent['TOC'] = array('type' => 'TABLEOFCONTENT', 'id' => 'TABLEOFCONTENT', 'isActive' => $tocIsActive);
                        } else {
                            $result['intro'] = $introText;
                        }
                        break;

                    case 'CLOSING':
                        $closingText = ($contentOnly) ? $newsletterContent['intro_closing_words'] : $newsletterContent['intro_closing_words'];
                        $closingText = FgUtility::correctCkEditorUrl($closingText, $container, $clubId, $mode);
                        if ($contentOnly) {
                            $staticContent['closing'] = array('type' => 'CLOSING', 'text' => $closingText, 'id' => $newsletterContent['contentId'], 'isActive' => $newsletterContent['isActive']);
                        } else {
                            $result['closingWords'] = $closingText;
                        }
                        break;

                    case 'ARTICLE':
                        if (empty($content)) {
                            $content['type'] = $newsletterContent['content_type'];
                            $content['title'] = $newsletterContent['articleTitle'];
                            $content['imgPosition'] = $newsletterContent['picture_position'];
                            $content['text'] = ($contentOnly) ? $newsletterContent['content'] : $newsletterContent['content'];
                            $content['text'] = FgUtility::correctCkEditorUrl($content['text'], $container, $clubId, $mode);
                            $content['teaserText'] = $newsletterContent['teaser_text'];
                            $content['id'] = $newsletterContent['contentId'];
                            $content['isActive'] = $newsletterContent['isActive'];
                            $content['sortOrder'] = $newsletterContent['sort_order'];
                            //$content['text_type'] = $newsletterContent['picture_position'];
                        }
                        if (!empty($newsletterContent['docId'])) { //attachment
                            $doc_type = $newsletterContent['doc_type'];
                            if ($doc_type == 'NEW') {
                                $content['document']['filename'] = $newsletterContent['filename'];
                                $content['document']['doc_path'] = "/uploads/$clubId/documents/newsletter_documents/";
                                break;
                            }
                        } elseif (!empty($newsletterContent['mediaId'])) { //media
                            if ($newsletterContent['media_type'] == 'image') {

                                $content['media'][] = array('type' => $newsletterContent['media_type'],
                                    'media' => $newsletterContent['media_text'],
                                    'mediaId' => $newsletterContent['mediaId'],
                                    'description' => $newsletterContent['description'],
                                    'mediaPath' => $baseUrl . "/uploads/$clubId/gallery/width_300/",
                                    'mediaOrgPath' => $baseUrl . "/uploads/$clubId/gallery/width_1920/",
                                    'size' => FgUtility::formatSizeUnits($newsletterContent['articlefileSize']));
                            }
                            if ($newsletterContent['media_type'] == 'attachments') {
                                
                                $content['mediaAttachments'][] = array('type' => $newsletterContent['media_type'],
                                    'media' => $newsletterContent['media_text'],
                                    'mediaId' => $newsletterContent['mediaId'],
                                    'mediaPath' => "/$communicationUploadFolder/",
                                    'mediaOrgPath' => "/$communicationUploadFolder/",
                                    'virtualFilePath' => FgUtility::generateFilemanagerInlineUrl($container, $newsletterContent['virtualname'], $clubId, $mode),
                                    'size' => FgUtility::formatSizeUnits($newsletterContent['articlefileSize']),
                                    'attachmentName' => $newsletterContent['articleAttachmentName']);
                            }
                        }

                        break;
                    case 'EXISTING_ARTICLE':
                        $content['type'] = $newsletterContent['content_type'];
                        $content['article_id'] = $newsletterContent['article_id'];
                        $content['article_lang'] = $articleLang = $newsletterContent['article_lang'];                        
                        $content['imgPosition'] = $newsletterContent['picture_position'];
                        $articleObj = new ArticleData($container);
                        $dataText = $articleObj->getArticleText($content['article_id'],$mode);
                        $articleExistObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($content['article_id']);
                        $createdClub = $articleExistObj->getClub()->getId();
                        $content['title'] = $newsletterContent['articleTitle'] = ($dataText['article']['text'][$articleLang]['title']) ? $dataText['article']['text'][$articleLang]['title'] : $dataText['article']['text']['default']['title'];
                        $articleText = ($dataText['article']['text'][$articleLang]['text']) ? $dataText['article']['text'][$articleLang]['text'] : $dataText['article']['text']['default']['text'];                                                
                        $content['text'] = FgUtility::correctCkEditorUrl($articleText, $container, $clubId, $mode, $createdClub);
                        $content['teaserText'] = ($dataText['article']['text'][$articleLang]['teaser']) ? $dataText['article']['text'][$articleLang]['teaser'] : $dataText['article']['text']['default']['teaser'];
                        $content['id'] = $newsletterContent['contentId'];
                        $content['isActive'] = $newsletterContent['isActive'];
                        $content['isTakeover'] = $newsletterContent['isTakeover'];
                        $content['sortOrder'] = $newsletterContent['sort_order'];
                        $content['imageCount'] = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleMedia')
                                ->getCountOfMedia($content['article_id'], 'image');
                        $content['attachmentCount'] = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleAttachments')
                                ->getCountOfAttachments($content['article_id']);
                        //autocomplete
                        $selected = array();
                        $selected[0]['id'] = $content['article_id'];
                        $selected[0]['title'] = ($dataText['article']['text'][$articleLang]['title']) ? $dataText['article']['text'][$articleLang]['title'] : $dataText['article']['text']['default']['title'];
                        $content['selected'] = json_encode($selected);
                        //to handle shared articles
                        if ($mode != 'cron') {
                            if ($createdClub == $clubId) {
                                $baseUrl = FgUtility::getBaseUrl($container);
                            } else {
                                $baseUrl = FgUtility::getBaseUrl($container, $createdClub);
                            }
                        } else {
                            $baseUrlArray = FgUtility::getMainDomainUrl($container, $createdClub, $mode);
                            $baseUrl = $baseUrlArray['baseUrl'];
                        }
                        //to handle shared articles
                        if ($newsletterContent['picture_position'] != 'none' && $content['imageCount'] > 0) {
                            $articleImage = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleMedia($content['article_id'], 'image');
                            foreach ($articleImage as $imageDet) {
                                $content['media'][] = array('type' => 'image', 'mediaId' => $imageDet['mediaId'],
                                    'media' => $imageDet['mediaFileName'],
                                    'description' => $imageDet['defaultDesc'], 'size' => $imageDet['mediaSize'],
                                    'mediaPath' => $baseUrl . "/uploads/$createdClub/gallery/width_300/",
                                    'mediaOrgPath' => $baseUrl . "/uploads/$createdClub/gallery/width_1920/",
                                );
                            }
                        }
                        if ($newsletterContent['isTakeover'] == 1 && $content['attachmentCount'] > 0) {
                            $attachmentData = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleAttachments($content['article_id']);
                            foreach ($attachmentData as $attachmentDet) {
                                $content['mediaAttachments'][] = array('type' => 'attachments',
                                    'mediaId' => $attachmentDet['attachmentId'],
                                    'media' => $attachmentDet['encryptedFilename'],
                                    'mediaPath' => "/$communicationUploadFolder/",
                                    'mediaOrgPath' => "/$communicationUploadFolder/",
                                    'virtualFilePath' => FgUtility::generateFilemanagerInlineUrl($container, $attachmentDet['virtualFilename'], $createdClub, $mode),
                                    'size' => FgUtility::formatSizeUnits($attachmentDet['attachmentSize']),
                                    'attachmentName' => $attachmentDet['attachmentName']);
                            }
                        }

                       break;
                    case 'SPONSOR':
                        if (empty($content)) {
                            $content['type'] = $newsletterContent['content_type'];
                            $content['contentType'] = 'sponsor';
                            $content['title'] = $newsletterContent['content_title'];
                            $content['sponsorAdWidth'] = $newsletterContent['sponsor_ad_width'];
                            $content['sponsorAdArea'] = $newsletterContent['sponsor_ad_area_id'];
                            $content['id'] = $newsletterContent['contentId'];
                            $content['isActive'] = $newsletterContent['isActive'];
                            $content['sortOrder'] = $newsletterContent['sort_order'];
                            $content['services'] = explode(",", $newsletterContent['services']);
                            $content['sponsorAds'] = $this->_em->getRepository('CommonUtilityBundle:FgSmAdArea')->getDetailsOfSponsorAdPreview($newsletterContent['services'], $newsletterContent['sponsor_ad_area_id'], $newsletterContent['sponsor_ad_width'], $clubId, $container, $club);
                        }
                        break;

                    case 'SPONSOR ABOVE':
                    case 'SPONSOR BOTTOM':
                        $sponserContent = array();
                        $sponserContent['type'] = $newsletterContent['content_type'];
                        $sponserContent['contentType'] = ($newsletterContent['content_type'] == 'SPONSOR ABOVE') ? 'sponsor_above' : 'sponsor_bottom';
                        $sponserContent['title'] = $newsletterContent['content_title'];
                        $sponserContent['sponsorAdWidth'] = $newsletterContent['sponsor_ad_width'];
                        $sponserContent['sponsorAdArea'] = $newsletterContent['sponsor_ad_area_id'];
                        $sponserContent['id'] = $newsletterContent['contentId'];
                        $sponserContent['isActive'] = $newsletterContent['isActive'];
                        $sponserContent['sortOrder'] = $newsletterContent['sort_order'];
                        $sponserContent['services'] = explode(",", $newsletterContent['services']);
                        $sponserContent['sponsorAds'] = $this->_em->getRepository('CommonUtilityBundle:FgSmAdArea')->getDetailsOfSponsorAdPreview($newsletterContent['services'], $newsletterContent['sponsor_ad_area_id'], $newsletterContent['sponsor_ad_width'], $clubId, $container, $club);
                        $contentType = (($newsletterContent['content_type'] == 'SPONSOR ABOVE') ? 'sponsorAboveContents' : 'sponsorBottomContents');
                        if (!$contentOnly) {
                            $result[$contentType][] = $sponserContent;
                        } else {
                            $staticContent[$newsletterContent['content_type']][] = $sponserContent;
                        }
                        break;

                    case 'TEAM NEWS':case 'NEWS':
                        break;

                    case 'IMAGE':
                        $content['type'] = $newsletterContent['content_type'];
                        $content['title'] = $newsletterContent['articleTitle'];
                        $content['path'] = $baseUrl . "/uploads/$clubId/gallery/width_580/";
                        $content['image'] = $newsletterContent['image_path'];
                        $content['imageLink'] = $newsletterContent['image_link'];
                        $content['id'] = $newsletterContent['contentId'];
                        $content['isActive'] = $newsletterContent['isActive'];
                        $content['sortOrder'] = $newsletterContent['sort_order'];
                        $content['size'] = FgUtility::formatSizeUnits($newsletterContent['imagefileSize']);
                        break;
                }

                if (in_array($newsletterContent['content_type'], array('ARTICLE', 'TEAM NEWS', 'NEWS', 'EXISTING_ARTICLE'))) {
                    $tableContent = $newsletterContent['articleTitle'];
                    $conteId = $newsletterContent['contentId'];
                }
            }
            if ($newsletterContentArray[0]['is_hide_table_contents'] == 0 && !empty($tableContent)) {
                $tableOfContent[$conteId] = $tableContent;
            }
            if (!empty($content)) {
                $contentsArray[] = $content;
            }
        }
        if ($contentOnly) {
            if ($result['step'] == 2) {
                $defaultSponsorDetails = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getDefaultNewsletterTemplateSponsorContents($clubId, $result['templateId']);
                array_unshift($contentsArray, isset($staticContent['TOC']) ? $staticContent['TOC'] : array('type' => 'TABLEOFCONTENT', 'id' => 'TABLEOFCONTENT', 'isActive' => 1) );
                if (count($defaultSponsorDetails['SPONSOR ABOVE']) > 0) {
                    $sponsorAboveDetails = array_reverse($defaultSponsorDetails['SPONSOR ABOVE']);
                    foreach ($sponsorAboveDetails as $key => $val) {
                        array_unshift($contentsArray, $val);
                    }
                }
                array_unshift($contentsArray, isset($staticContent['intro']) ? $staticContent['intro'] : array('type' => 'INTRO', 'text' => '', 'id' => 'intro', 'isActive' => 1));
                array_unshift($contentsArray, array('type' => 'SALUTATION', 'text' => $salutation, 'id' => 'salutation'));
                $contentsArray[] = isset($staticContent['ARTICLE']) ? $staticContent['ARTICLE'] : array('type' => 'ARTICLE', 'text' => '', 'id' => 'article', 'isActive' => 1, 'sortOrder' => 1);
                $sortOrder = 2;
                if (count($defaultSponsorDetails['SPONSOR']) > 0) {
                    foreach ($defaultSponsorDetails['SPONSOR'] as $key => $val) {
                        $val['sortOrder'] = $sortOrder;
                        $sortOrder++;
                        $contentsArray[] = $val;
                    }
                }
                $contentsArray[] = isset($staticContent['closing']) ? $staticContent['closing'] : array('type' => 'CLOSING', 'text' => '', 'id' => 'closing', 'isActive' => 1);
                if (count($defaultSponsorDetails['SPONSOR BOTTOM']) > 0) {
                    foreach ($defaultSponsorDetails['SPONSOR BOTTOM'] as $key => $val) {
                        $contentsArray[] = $val;
                    }
                }
            } else {
                array_unshift($contentsArray, isset($staticContent['TOC']) ? $staticContent['TOC'] : array('type' => 'TABLEOFCONTENT', 'id' => 'TABLEOFCONTENT', 'isActive' => 1) );
                if (count($staticContent['SPONSOR ABOVE']) > 0) {
                    $tempArr = array_reverse($staticContent['SPONSOR ABOVE']);
                    foreach ($tempArr as $key => $val) {
                        array_unshift($contentsArray, $val);
                    }
                }
                array_unshift($contentsArray, isset($staticContent['intro']) ? $staticContent['intro'] : array('type' => 'INTRO', 'text' => '', 'id' => 'intro', 'isActive' => 1));
                array_unshift($contentsArray, array('type' => 'SALUTATION', 'text' => $salutation, 'id' => 'salutation'));

                $contentsArray[] = isset($staticContent['closing']) ? $staticContent['closing'] : array('type' => 'CLOSING', 'text' => '', 'id' => 'closing', 'isActive' => 1);
                if (count($staticContent['SPONSOR BOTTOM']) > 0) {
                    foreach ($staticContent['SPONSOR BOTTOM'] as $key => $val) {
                        $contentsArray[] = $val;
                    }
                }
            }
            $result = $contentsArray;
        } else {
            $result['tableOfContents'] = $tableOfContent;
            $result['contents'] = $contentsArray;
        }
        

        return $result;
    }

    /**
     * function to get the details to display in newsletter preview
     *
     * @param string $type the newsletter type(simple mail or newsletter)
     * @param int $newsletterId the newsletter id
     * @param int $clubId the club id
     *
     * @return array
     */
    public function getPreviewDetails($type, $newsletterId, $clubId) {

        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $dateTimeFormat = FgSettings::getMysqlDateTimeFormat();

        $results = $this->createQueryBuilder('n')
                ->select("n.id as newsletterId, n.status, n.subject as subject, n.senderName as senderName, n.publishType as publishType, n.senderEmail as senderEmail, n.newsletterType as newsletterType, n.isDisplayInArchive as isPublishInArchive, (DATE_FORMAT(n.sendDate, '$dateTimeFormat')) as sendDate, n.resentStatus as isResent")
                ->addSelect('(SELECT COUNT(r.isBounced) FROM CommonUtilityBundle:FgCnNewsletterReceiverLog r WHERE r.newsletter =:newsletterId AND r.isBounced = 1) AS bounceEmailCount')
                ->addSelect('(SELECT COUNT(r1.id) FROM CommonUtilityBundle:FgCnNewsletterReceiverLog r1 WHERE r1.newsletter =:newsletterId) AS recepientsCount');
        if ($type == "simplemail") {
            $results->addSelect("(SELECT GROUP_CONCAT(d.filename) FROM CommonUtilityBundle:FgCnNewsletterArticleDocuments d WHERE
			d.newsletter =:newsletterId)attachments");
        }
        $results->where('n.club=:clubId')
                ->andWhere('n.id=:newsletterId')
                ->setParameter('clubId', $clubId)
                ->setParameter('newsletterId', $newsletterId);

        $qryResults = $results->getQuery()
                ->getResult();


        return $qryResults[0];
    }

    /**
     * Function to delete Newsletter./ simple mail Draft
     *
     * @param int $selectedId
     * @param int $clubId
     * @return boolean
     */
    public function deleteNewsletterSimpleMailDraft($selectedId, $clubId, $newsletterType, $container) {
        $deleteObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->findOneBy(array('id' => $selectedId, 'newsletterType' => $newsletterType, 'club' => $clubId));
        $this->_em->remove($deleteObj);
        $this->_em->flush();
    }

    /**
     * function to move Scheduled To Draft
     * @param type $selectedId
     * @param type $clubId
     * @param type $type
     * @param type $contact
     * @return boolean
     */
    public function moveScheduledToDraft($selectedId, $clubId, $type, $contact) {

        $currDate = new \DateTime("now");
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contact);
        $moveObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->findOneBy(array('newsletterType' => $type, 'status' => 'scheduled', 'club' => $clubId, 'id' => $selectedId));
        $moveObj->setStatus('draft')
                ->setLastUpdated($currDate)
                ->setUpdatedBy($contactObj)
                ->setSendDate(new \DateTime(date('0000-00-00 00:00:00')));
        $this->_em->persist($moveObj);
        $this->_em->flush();
        return true;
    }

    /**
     * function to get Count
     *
     * @return DQL
     */
    public function getSubQueryForSendingCount() {
        $functonCount = $this->getEntityManager()->createQueryBuilder();
        $functonCount->select('COUNT(nrl.id) as rCount')
                ->from('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'nrl')
                ->where("nrl.newsletter=n.id");

        return $functonCount;
    }

    /**
     * function to get Count
     *
     * @return DQL
     */
    public function getSubQueryForBouncedCount() {
        $functonCount = $this->getEntityManager()->createQueryBuilder();
        $functonCount->select('COUNT(fcnl.isBounced) as bounceCount')
                ->from('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'fcnl')
                ->where("fcnl.newsletter=n.id")
                ->andWhere("fcnl.club=:clubId")
                ->andWhere("fcnl.isBounced=1");

        return $functonCount;
    }

    /**
     * function to get Count
     *
     * @return DQL
     */
    public function getSubQueryForReceiverCount() {
        $functonCount = $this->getEntityManager()->createQueryBuilder();
        $functonCount->select('COUNT(rl.id) as rCount')
                ->from('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'rl')
                ->where("rl.newsletter=n.id")
                ->andWhere("rl.club=:clubId");

        return $functonCount;
    }

    /**
     * function to get Count
     *
     * @return DQL
     */
    public function getSubQueryForSendCount() {
        $functonCount = $this->getEntityManager()->createQueryBuilder();
        $functonCount->select('COUNT(fl.isSent) as sendCount')
                ->from('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'fl')
                ->where("fl.newsletter=n.id")
                ->andWhere("fl.club=:clubId");

        return $functonCount;
    }

    /**
     * function to get Count
     *
     * @return DQL
     */
    public function getSubQueryForOpenCount() {
        $functonCount = $this->getEntityManager()->createQueryBuilder();
        $functonCount->select('COUNT(lr.openedAt) as opCount')
                ->from('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'lr')
                ->where("lr.newsletter=n.id")
                ->andWhere("lr.club=:clubId")
                ->andWhere("lr.openedAt IS NOT NULL");

        return $functonCount;
    }

    /**
     * Function to get newsletter status
     *
     * @param Integer $clubId             Club id
     * @param Integer $type               Type
     * @param Integer $start              Start
     * @param Integer $length             Length
     * @param Integer $newsletterType     Type
     * @param Integer $deletePath         Delete path
     * @param Integer $moveDraftPath      Move path
     * @param Integer $duplicatePath      Duplicate path
     * @param Integer $mailingsPreview    Preview path
     * @param Integer $mailingsRecipients Recipients path
     *
     * @return array
     */
    public function getNewslettersList($container, $clubId, $type, $start = 0, $length = 50, $newsletterType, $deletePath, $moveDraftPath, $duplicatePath, $mailingsPreview, $mailingsRecipients, $mailingsEdit) {
        $translator = $container->get('translator');
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactName', 'Common\UtilityBundle\Extensions\FetchContactName');

        $bounceCount = $this->getSubQueryForBouncedCount();
        $receiverCount = $this->getSubQueryForReceiverCount();
        $sendCount = $this->getSubQueryForSendCount();
        $openCount = $this->getSubQueryForOpenCount();

        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();

        $results = $this->createQueryBuilder('n')
                ->select("n.id , n.subject,(DATE_FORMAT(n.sendDate, '$datetimeFormat')) as send_date,n.publishType as publish_type,n.senderEmail as sender_email, n.senderName as sender_name,(DATE_FORMAT(n.lastUpdated, '$datetimeFormat')) as last_updated,(contactName(n.updatedBy)) as updated_by,  (DATE_FORMAT(n.createdAt, '$datetimeFormat')) as created_at,(contactName(n.createdBy)) as created_by, n.recepientCount as recepient_count, n.resentStatus as resend_status");

        if ($type == 'sent') {
            $results->addSelect('(' . $bounceCount->getDQL() . ') as is_bounced');
            $results->addSelect('(' . $receiverCount->getDQL() . ') as receicerCount');
            $results->addSelect('(' . $sendCount->getDQL() . ') as is_sent');
            $results->addSelect('(' . $openCount->getDQL() . ') as openCount');
        }

        $results->where('n.club=:clubId')
                ->andWhere('n.status=:type')
                ->andWhere('n.newsletterType=:newsletterType')
                ->setParameter('clubId', $clubId)
                ->setParameter('type', $type)
                ->setParameter('newsletterType', $newsletterType);

        $qryResults = $results->getQuery()->getResult();
        foreach ($qryResults as $key => $newsletter) {
            if ($newsletterType == 'GENERAL') {
                $newsletterType = 'newsletter';
            } else if ($newsletterType == 'EMAIL') {
                $newsletterType = 'simplemail';
            }
            if ($newsletter['publish_type'] == 'MANDATORY') {
                $publishType = $translator->trans('MAILINGS_MANDATORY');
            } else if ($newsletter['publish_type'] == 'SUBSCRIPTION') {
                $publishType = $translator->trans('MAILINGS_NON_MANDATORY');
            }
            $deletePathNew = str_replace("_ID_", $newsletter['id'], str_replace("type", $newsletterType, $deletePath));
            $moveDraftPathNew = str_replace("_ID_", $newsletter['id'], str_replace("type", $newsletterType, $moveDraftPath));
            $duplicatePathNew = str_replace("_ID_", $newsletter['id'], str_replace("type", $newsletterType, $duplicatePath));
            $mailingsPreviewNew = str_replace("_ID_", $newsletter['id'], str_replace("status", $type, $mailingsPreview));
            $mailingsRecipientsNew = str_replace("_ID_", $newsletter['id'], str_replace("status", $type, $mailingsRecipients));
            $mailingsEditNew = str_replace("_ID_", $newsletter['id'], $mailingsEdit);

            $subject = str_replace("<", "&lt;", str_replace(">", "&gt;", $newsletter['subject']));
            $senderName = str_replace("<", "&lt;", str_replace(">", "&gt;", $newsletter['sender_name']));

            switch ($type) {
                case 'draft':
                    $templateArray[] = array($newsletter['last_updated'], $newsletter['updated_by'], $subject, $newsletter['created_at'], $newsletter['created_by'], $deletePathNew, $mailingsEditNew);
                    break;
                case 'scheduled':
                    $templateArray[] = array($newsletter['send_date'], $newsletter['updated_by'], $subject, $publishType, $newsletter['sender_email'], $newsletter['recepient_count'], $moveDraftPathNew, $mailingsPreviewNew, $mailingsRecipientsNew, 'id' => $newsletter['id']);
                    break;
                case 'sending':
                    $templateArray[] = array($newsletter['send_date'], $newsletter['updated_by'], $subject, $publishType, $newsletter['sender_email'], $newsletter['recepient_count'], $duplicatePathNew, $mailingsPreviewNew, $mailingsRecipientsNew, 'id' => $newsletter['id']);
                    break;
                case 'sent':
                    $totalCount = $newsletter['receicerCount'];
                    $openCount = $newsletter['openCount'];
                    $openedPercentage = ($totalCount > 0) ? round(($openCount / $totalCount) * 100) : 0;
                    $templateArray[] = array($newsletter['send_date'], $newsletter['updated_by'], $subject, $publishType, $newsletter['sender_email'], $newsletter['receicerCount'], $newsletter['is_bounced'], $newsletter['openCount'], $openedPercentage, $duplicatePathNew, $mailingsPreviewNew, $mailingsRecipientsNew, $newsletter['resend_status'], 'id' => $newsletter['id']);
                    break;
            }
        }

        return $templateArray;
    }

    /**
     * Function to get newsletter count
     *
     * @param Integer $clubId Club id
     * @param Integer $type   Type
     *
     * @return array
     */
    public function getMailingsNewsletterCount($clubId, $type, $mail, $search = '') {
        $resultQuery = $this->createQueryBuilder('n')
                ->select('count(n.id)')
                ->where('n.club=:clubId')
                ->andWhere('n.status=:type')
                ->andWhere('n.newsletterType=:email')
                ->andWhere('n.senderEmail like :search OR n.subject like :search')
                ->setParameter('clubId', $clubId)
                ->setParameter('type', $type)
                ->setParameter('email', $mail)
                ->setParameter('search', '%' . $search . "%");
        $result = $resultQuery->getQuery()->getSingleScalarResult();

        return ($result) ? $result : false;
    }

    /**
     * function to get the newsletter details for reciepients
     *
     * @param string $type the type (simplemail or newsletter)
     * @param int $newsletterId the newsletter id
     * @param int $clubId the club id
     */
    public function getRecepientDetails($type, $newsletterId, $clubId) {
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');

        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();

        $result = $this->createQueryBuilder('n')
                ->select(" n.id as newsletterId, n.status, (DATE_FORMAT(n.sendDate, '$datetimeFormat')) as sendDate, n.subject as subject,n.senderName as senderName, n.resentStatus as isResent, n.publishType as publishType, n.isSubscriberSelection AS isSubscribersInclude")
                ->addSelect("(SELECT GROUP_CONCAT(l.languageCode) FROM CommonUtilityBundle:FgCnNewsletterPublishLang l  WHERE l.newsletter =:newsletterId) AS language")
                ->addSelect('(SELECT COUNT(r.isBounced) FROM CommonUtilityBundle:FgCnNewsletterReceiverLog r WHERE r.newsletter =:newsletterId AND r.isBounced = 1) AS bounceEmailCount')
                ->addSelect('(SELECT COUNT(r1.id) FROM CommonUtilityBundle:FgCnNewsletterReceiverLog r1 WHERE r1.newsletter =:newsletterId) AS recepientsCount')
                ->where("n.club=:clubId")
                ->andWhere("n.id=:newsletterId")
                ->setParameter("clubId", $clubId)
                ->setParameter("newsletterId", $newsletterId)
                ->orderBy("n.id");
        $results = $result->getQuery()->getResult();

        return $results[0];
    }

    /**
     * function to get the simple mail details
     *
     * @param int $newsletterId the newsletter id
     * @param int $clubId the club id
     *
     * @return array
     */
    public function getSimpleMailDetails($newsletterId, $clubId) {
        $conn = $this->getEntityManager()->getConnection();
        $dateFormat = FgSettings::getMysqlDateTimeFormat();

        $sql = "SELECT n.id as newsletterId,DATE_FORMAT(n.send_date,'$dateFormat') as sendDate,n.subject,n.sender_name as senderName, "
                . "n.sender_email as senderEmail, n.newsletter_type, n.is_display_in_archive,n.salutation_type,n.salutation,"
                . "n.email_content,d.doc_type,d.documents_id,d.title,c.intro_closing_words AS signature,"
                . "FM.encrypted_filename as filename, FM.virtual_filename as virtualFilename  " //d.filename,
                . "FROM fg_cn_newsletter n "
                . "LEFT JOIN fg_cn_newsletter_article_documents d ON n.id = d.newsletter_id "
                . "LEFT JOIN fg_file_manager FM ON FM.id = d.file_manager_id "
                . "LEFT JOIN fg_cn_newsletter_content c ON (c.newsletter_id = n.id AND c.content_type='OTHER') "
                . "WHERE n.id=:newsletterId AND n.club_id=:clubId AND n.newsletter_type='EMAIL' order by d.sort_order ASC";

        $result = $conn->fetchAll($sql, array('newsletterId' => $newsletterId, 'clubId' => $clubId));

        return $result;
    }

    /**
     * function to duplicate newletter/simplemail
     *
     * @param int $newsletterId Newsletter Id
     * @param string $type Type:NL/SM
     * @param int $contactId contact Id
     * @param int $club_id ClubId
     * @param object $container
     */
    public function duplicate($newsletterId, $type, $contactId, $club_id, $container) {
        $rootPath = FgUtility::getRootPath($container);
        $currDate = new \DateTime("now");
        $newsletter = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $createdBy = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $newsletterTitle = $newsletter->getSubject();
        $newTitle = $container->get('translator')->trans('Copy_of_%a%', array('%a%' => $newsletterTitle));
        $fieldArray = array('Status' => 'draft', 'Subject' => $newTitle, 'LastUpdated' => $currDate,
            'SendDate' => new \DateTime(date('0000-00-00 00:00:00')), 'CreatedAt' => $currDate,
            'CreatedBy' => $createdBy, 'UpdatedBy' => $createdBy, 'Step' => '5', 'lastSpoolContactId' => '0', 'RecepientCount' => '0',
            'IsCron' => '0', 'NewsletterContent' => null, 'IsRecepientUpdated' => '0', 'ResentStatus' => '0'
        );

        $newNewsletterId = $this->doCopy('FgCnNewsletter', $newsletterId, $fieldArray);
        $newNewsletterObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newNewsletterId);

        $duplicateLang = $this->duplicatePublishLang($newsletterId, $newNewsletterObj);
        $duplicateExcludeContact = $this->duplicateExcludeContacts($newsletterId, $newNewsletterObj);
        $duplicateManualContact = $this->duplicateManualContacts($newsletterId, $newNewsletterObj);
        $duplicateManualContactEmail = $this->duplicateManualContactEmail($newsletterId, $newNewsletterObj);

        /* content area */
        $contents = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->findByNewsletter($newsletterId);

        if ($contents) {
            foreach ($contents as $key => $content) {

                $contentId = $content->getId();
                $contentType = $content->getContentType(); //['content_type'];
                $galleryItemObj = $content->getItems();

                $fieldArray = ($contentType != 'IMAGE') ? array('Newsletter' => $newNewsletterObj) : array('Newsletter' => $newNewsletterObj, 'Items' => $galleryItemObj);
                $newContentId = $this->doCopy('FgCnNewsletterContent', $contentId, $fieldArray);

                /* content article duplication */
                if ($contentType == 'ARTICLE') {
                    $articles = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterArticle')->findByContent2($contentId);
                    $newContentObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($newContentId);

                    if ($articles) {
                        foreach ($articles as $article) {
                            $articleId = $article->getId();
                            $fieldArray = array('Content2' => $newContentObj);
                            $new_article_id = $this->doCopy('FgCnNewsletterArticle', $articleId, $fieldArray);
                            $newArticleObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterArticle')->find($new_article_id);
                            $article_documents = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleDocuments')->findByArticle($articleId);
                            if ($article_documents) {
                                $duplicateArticleDocument = $this->duplicateArticleDocument($article_documents, $newNewsletterObj, $newArticleObj, $currDate);
                            }

                            /* article media duplication */
                            $article_medias = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleMedia')->findByArticle($articleId);
                            if ($article_medias) {
                                foreach ($article_medias as $article_media) {
                                    $duplicateArticleMedia = $this->duplicateArticleMedia($article_media, $newArticleObj);
                                }
                            }
                        }
                    }
                }
                /* sponsor ad duplication */
                if ($contentType == 'SPONSOR' || $contentType == 'SPONSOR ABOVE' || $contentType == 'SPONSOR BOTTOM') {
                    $this->duplicateNewsletterContentServices($contentId, $newContentId);
                }
            }
        }

        // For duplicating sidebar contents
        $this->duplicateNewsletterSidebarContents($newsletterId, $newNewsletterObj);

        if ($type == 'EMAIL') {
            $article_documents = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleDocuments')->findByNewsletter($newsletterId);
            if ($article_documents) {
                $duplicateArticleDocument = $this->duplicateArticleDocument($article_documents, $newNewsletterObj, null, $currDate);
            }
        }

        return true;
    }

    /**
     * Method for duplicating newsletter content services (in step 3)
     * @param int $contentId    Primary Id of newsletter content
     * @param obj $newContentId New duplicated content object
     * @return null
     */
    private function duplicateNewsletterContentServices($contentId, $newContentId) {
        $contentServices = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContentServices')->findByContent($contentId);
        $newContentObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($newContentId);

        if ($contentServices) {
            foreach ($contentServices as $contentService) {
                $contentServiceId = $contentService->getId();
                $contentServiceServiceObj = $contentService->getService();
                $fieldArray = array('Content' => $newContentObj, "Service" => $contentServiceServiceObj);
                $this->doCopy('FgCnNewsletterContentServices', $contentServiceId, $fieldArray);
            }
        }
    }

    /**
     * Method for duplicating newsletter sidebar and its services (in step 4)
     * @param int $newsletterId     Primary Id of Newsletter
     * @param obj $newNewsletterObj New duplicated newsletter object
     * @return null
     */
    private function duplicateNewsletterSidebarContents($newsletterId, $newNewsletterObj) {
        /* sidebar area */
        $sidebarContents = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebar')->findByNewsletter($newsletterId);

        if ($sidebarContents) {
            foreach ($sidebarContents as $sidebarContent) {
                $sidebarContentId = $sidebarContent->getId();

                $fieldArray = array('Newsletter' => $newNewsletterObj);
                $newSidebarContentId = $this->doCopy('FgCnNewsletterSidebar', $sidebarContentId, $fieldArray);

                /* sidebar sponsor ad duplication */
                $sidebarServices = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebarServices')->findByNewsletterSidebar($sidebarContentId);
                $newSidebarObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebar')->find($newSidebarContentId);

                if ($sidebarServices) {
                    foreach ($sidebarServices as $sidebarService) {
                        $sidebarServiceId = $sidebarService->getId();
                        $sidebarServiceServiceObj = $sidebarService->getService();
                        $fieldArray = array('NewsletterSidebar' => $newSidebarObj, "Service" => $sidebarServiceServiceObj);
                        $this->doCopy('FgCnNewsletterSidebarServices', $sidebarServiceId, $fieldArray);
                    }
                }
            }
        }
    }

    /**
     * function to duplicate Publish Lang
     * @param int $newsletterId Newsltter Id
     * @param object $newNewsletterObj New Newsltter Object
     * @return boolean
     */
    public function duplicatePublishLang($newsletterId, $newNewsletterObj) {
        /* publish language duplication */
        $langIds = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterPublishLang')->findByNewsletter($newsletterId);
        $fieldArray = array('Newsletter' => $newNewsletterObj);
        foreach ($langIds as $langId) {
            $langI = $langId->getId();
            $newLangId = $this->doCopy('FgCnNewsletterPublishLang', $langI, $fieldArray);
        }
        return true;
    }

    /**
     * function to duplicate Exclude Contacts
     * @param int $newsletterId newsltter Id
     * @param object $newNewsletterObj New Newsletter Object
     * @return boolean
     */
    public function duplicateExcludeContacts($newsletterId, $newNewsletterObj) {
        /* exclude contacts */
        $excludeContactIds = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterExcludeContacts')->findByNewsletter($newsletterId);
        $fieldArray = array('Newsletter' => $newNewsletterObj);
        foreach ($excludeContactIds as $excludeContactId) {
            $excludeContact = $excludeContactId->getId();
            $newExcludeContactId = $this->doCopy('FgCnNewsletterExcludeContacts', $excludeContact, $fieldArray);
        }
        return true;
    }

    /**
     * function to duplicate Manual Contacts
     * @param int $newsletterId Newstter Id
     * @param object $newNewsletterObj New newsletter Object
     * @return boolean
     */
    public function duplicateManualContacts($newsletterId, $newNewsletterObj) {
        /* manual contacts */
        $manualContactIds = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->findByNewsletter($newsletterId);
        $fieldArray = array('Newsletter' => $newNewsletterObj);
        foreach ($manualContactIds as $manualContactId) {
            $manualContact = $manualContactId->getId();
            $newManualContactId = $this->doCopy('FgCnNewsletterManualContacts', $manualContact, $fieldArray);
        }
        return true;
    }

    /**
     * function to duplicate Manual Contacts Email
     * @param int $newsletterId Newletter Id
     * @param Object $newNewsletterObj New Newsltter Object
     * @return boolean
     */
    public function duplicateManualContactEmail($newsletterId, $newNewsletterObj) {
        /* manual contacts email */
        $manualContactEmailIds = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->findByNewsletter($newsletterId);
        $fieldArray = array('Newsletter' => $newNewsletterObj);
        foreach ($manualContactEmailIds as $manualContactEmailId) {
            $manualContactEmail = $manualContactEmailId->getId();
            $newManualContactEmailId = $this->doCopy('FgCnNewsletterManualContactsEmail', $manualContactEmail, $fieldArray);
        }
        return true;
    }

    /**
     * function to duplicate Article Media
     * @param object $article_media Article Media
     * @param object $newArticleObj New Article Obj
     * 
     * @return boolean
     */
    public function duplicateArticleMedia($article_media, $newArticleObj) {
        $article_media_id = $article_media->getId();
        $fieldArray = array('Article' => $newArticleObj);
        $fileManagerObj = $article_media->getFileManager();
        if ($fileManagerObj) {
            $fieldArray['FileManager'] = $fileManagerObj;
        }
        $galleryItemObj = $article_media->getGalleryItem();
        if ($galleryItemObj) {
            $fieldArray['GalleryItem'] = $galleryItemObj;
        }
        $new_article_media_id = $this->doCopy('FgCnNewsletterArticleMedia', $article_media_id, $fieldArray);

        return true;
    }

    /**
     * function to duplicate Article Document
     * @param object $article_documents Article Documents
     * @param object $newNewsletterObj Newsltter Object
     * @param object $newArticleObj new newsletter object
     * @param date $currDate current date
     * @return boolean
     */
    public function duplicateArticleDocument($article_documents, $newNewsletterObj, $newArticleObj, $currDate) {
        foreach ($article_documents as $article_document) {
            $article_document_id = $article_document->getId();
            $filemanagerObj = $article_document->getFileManager();
            $fieldArray = array('Newsletter' => $newNewsletterObj, 'Article' => $newArticleObj, 'FileManager' => $filemanagerObj, 'CreatedAt' => $currDate);
            $new_article_document_id = $this->doCopy('FgCnNewsletterArticleDocuments', $article_document_id, $fieldArray);
        }
        return true;
    }

    /**
     * Executes doCopy action
     *
     * @param string $table_name
     * @param integer $primary_id
     * @param array $field_arr
     *
     * @return type integer $new_id
     */
    private function doCopy($table_name, $primary_id, $field_arr) {

        $repository = 'CommonUtilityBundle:' . $table_name;
        $obj = $this->_em->getRepository($repository)->find($primary_id);
        $newObj = clone $obj;

        foreach ($field_arr as $field => $field_value) {
            $setVar = "set" . $field;
            $newObj->$setVar($field_value);
        }
        $this->_em->persist($newObj);
        $this->_em->flush();
        $new_id = $newObj->getId();

        return $new_id;
    }

    /**
     * function to get all the newsletter ids based on club id and the status
     *
     * @param string $type the type (newsletter or simplemail)
     * @param string $status the status (drafts,scheduled,sending and sent)
     * @param int $clubId the club id
     *
     * @return array
     */
    public function getAllNewsletterIdsForStatus($type, $status, $clubId) {
        if ($type == 'simplemail') {
            $newsletterType = "EMAIL";
        } else {
            $newsletterType = "GENERAL";
        }
        $resultQuery = $this->createQueryBuilder('n')
                ->select('n.id')
                ->where('n.club=:clubId')
                ->andWhere('n.status=:status')
                ->andWhere('n.newsletterType=:newsletterType')
                ->setParameter('clubId', $clubId)
                ->setParameter('status', $status)
                ->setParameter('newsletterType', $newsletterType);
        $result = $resultQuery->getQuery()->getResult();

        return $result;
    }

    /**
     * function to update the resend status
     *
     * @param type $newsletterId the newsletter id
     */
    public function updateResendStatus($newsletterId) {
        $qb = $this->createQueryBuilder();
        $q = $qb->update('CommonUtilityBundle:FgCnNewsletter', 'n')
                ->set('n.resentStatus', '1')
                ->where('n.resentStatus = 0')
                ->andWhere('n.id =:newsletterId')
                ->setParameter('newsletterId', $newsletterId)
                ->getQuery();

        $p = $q->execute();
    }

    /**
     * Function to get scheduled newsletters for sending
     *
     * @return array $result NewsletterArray
     */
    public function getScheduledNewsletters() {
        $now = date('Y-m-d H:i:s');
        $resultQuery = $this->createQueryBuilder('n')
                ->select('n.id,IDENTITY(n.club) AS clubId, IDENTITY(n.template) AS templateId, n.subject, n.sendDate, n.status, n.publishType, n.newsletterType, IDENTITY(n.recepientList) AS recipientListId')
                ->where('n.status=:status1 OR n.status=:status2')
                ->andWhere('n.sendDate <= :now')
                ->andWhere('n.isCron = :isCron')
                ->andWhere('n.lastSpoolContactId = 0')
                ->orderBy('n.sendDate', 'ASC')
                ->setParameters(array('isCron' => 0, 'now' => $now, 'status1' => 'scheduled', 'status2' => 'sending'));
        $result = $resultQuery->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Function to get exceptions data (included & excluded) of a newsletter.
     *
     * @param int $newsletterId Newsletter Id
     *
     * @return array $resultData Result data array
     */
    public function getExceptionsOfNewsletter($newsletterId) {
        $conn = $this->getEntityManager()->getConnection();
        $details = $conn->executeQuery("SELECT nl.id, GROUP_CONCAT(DISTINCT mc.contact_id) AS includedContacts, GROUP_CONCAT(DISTINCT CONCAT(ec.email,'#',ec.salutation)) AS excludedData "
                        . "FROM `fg_cn_newsletter` nl "
                        . "LEFT JOIN `fg_cn_newsletter_manual_contacts` mc ON (mc.newsletter_id=nl.id) "
                        . "LEFT JOIN `fg_cn_newsletter_exclude_contacts` ec ON (ec.newsletter_id=nl.id) "
                        . "WHERE nl.id=:newsletterId", array('newsletterId' => $newsletterId)
                )->fetchAll();
        $resultData = $details[0];

        return $resultData;
    }

    /**
     * Function to save data of step2 of newsletter wizard.
     *
     * @param int    $newsletterId   Newsletter id
     * @param array  $dataArray      Array of data to save
     * @param object $container      Array of data to save
     * @param int    $currContactId  Array of data to save
     * @param int    $clubId         Array of data to save
     * @param string $clubSystemLang Club default system language
     */
    public function saveNewsletterWizardStep2($newsletterId, $dataArray, $container, $currContactId, $clubId, $clubSystemLang = 'de') {
        if (count($dataArray) > 0) {
            $em = $this->getEntityManager();
            $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
            $newsletterClubId = $newsletterObj->getClub()->getId();
            if ($newsletterClubId == $clubId) {
                if (isset($dataArray['recipientListId'])) {
                    if ($dataArray['recipientListId'] == '') {
                        $recipientObj = NULL;
                    } else {
                        $recipientObj = $em->getRepository('CommonUtilityBundle:FgCnRecepients')->find($dataArray['recipientListId']);
                    }
                    $newsletterObj->setRecepientList($recipientObj);
                }
                if (isset($dataArray['include_subscribers'])) {
                    $newsletterObj->setIsSubscriberSelection($dataArray['include_subscribers']);
                }
                if (isset($dataArray['include_formermembers'])) {
                    $newsletterObj->setIncludeFormerMembers($dataArray['include_formermembers']);
                }
                $currContactObj = $em->getRepository('CommonUtilityBundle:FgCmContact')->find($currContactId);
                $newsletterObj->setUpdatedBy($currContactObj);
                $newsletterObj->setLastUpdated(new \DateTime("now"));
                if ($newsletterObj->getStep() < 2) {
                    $newsletterObj->setStep('2');
                }
                $em->persist($newsletterObj);
                $em->flush();
                $this->saveNewsletterExceptions($newsletterId, $dataArray, $newsletterObj);
                $this->saveNewsletterEmailSettings($newsletterId, $dataArray, $newsletterObj);
                $this->updateNewsletterRecipientsCount($newsletterId, $clubId, $container, $currContactId, $newsletterObj, $clubSystemLang);
            }
        }
    }

    /**
     * Function for saving exceptions data (included & excluded) of a newsletter.
     *
     * @param int    $newsletterId  Newsletter Id
     * @param array  $dataArray     Data array
     * @param object $newsletterObj Newsletter object
     */
    private function saveNewsletterExceptions($newsletterId, $dataArray, $newsletterObj) {
        if (isset($dataArray['includedContacts']) || isset($dataArray['excludedData'])) {
            $em = $this->getEntityManager();
            $addIncludedConts = array();
            $remIncludedConts = array();
            $addExcludedData = array();
            $remExcludedData = array();
            $exceptions = $this->getExceptionsOfNewsletter($newsletterId);
            if (isset($dataArray['includedContacts'])) {
                $currInclConts = ($exceptions['includedContacts'] == '') ? array() : explode(',', $exceptions['includedContacts']);
                $addIncludedConts = array_diff($dataArray['includedContacts'], $currInclConts);
                $remIncludedConts = array_diff($currInclConts, $dataArray['includedContacts']);
            }
            if (isset($dataArray['excludedData'])) {
                $currExclData = ($exceptions['excludedData'] == '') ? array() : explode(',', $exceptions['excludedData']);
                $passedExclData = ($dataArray['excludedData'] == '') ? array() : explode(',', $dataArray['excludedData']);
                $addExcludedData = array_diff($passedExclData, $currExclData);
                $remExcludedData = array_diff($currExclData, $passedExclData);
            }
            // Add manual contacts.
            if (count($addIncludedConts)) {
                $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->addNewsletterManualContacts($newsletterId, $addIncludedConts, $newsletterObj);
            }
            // Remove manual contacts.
            if (count($remIncludedConts)) {
                $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContacts')->removeNewsletterManualContacts($newsletterId, $remIncludedConts);
            }
            // Add excluded data.
            if (count($addExcludedData)) {
                $em->getRepository('CommonUtilityBundle:FgCnNewsletterExcludeContacts')->addNewsletterExludedData($newsletterId, $addExcludedData, $newsletterObj);
            }
            // Remove excluded data.
            if (count($remExcludedData)) {
                $em->getRepository('CommonUtilityBundle:FgCnNewsletterExcludeContacts')->removeNewsletterExludedData($newsletterId, $remExcludedData);
            }
        }
    }

    /**
     * Function for saving email settings (main & substitute) of a newsletter.
     *
     * @param int    $newsletterId  Newsletter id
     * @param array  $dataArray     Data array
     * @param object $newsletterObj Newsletter object
     */
    private function saveNewsletterEmailSettings($newsletterId, $dataArray, $newsletterObj) {
        if (isset($dataArray['mainEmails']) || isset($dataArray['substituteEmail'])) {
            $em = $this->getEntityManager();
            $addEmailSettings = array();
            $remEmailSettings = array();
            $currEmailSettings = $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->getEmailSettingsofNewsletter($newsletterId);
            if (isset($dataArray['mainEmails'])) {
                $addEmailSettings['main'] = array_diff($dataArray['mainEmails'], $currEmailSettings['main']);
                $remEmailSettings['main'] = array_diff($currEmailSettings['main'], $dataArray['mainEmails']);
            }
            if (isset($dataArray['substituteEmail'])) {
                if ($dataArray['substituteEmail'] == '') {
                    if ($currEmailSettings['substitute'] != '') {
                        $remEmailSettings['substitute'] = array($currEmailSettings['substitute']);
                    }
                } else {
                    if ($currEmailSettings['substitute'] == '') {
                        // Insert substitute email.
                        $addEmailSettings['substitute'] = array($dataArray['substituteEmail']);
                    } else {
                        // Update substitute email.
                        $substituteObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->findOneBy(array('newsletter' => $newsletterId, 'selectionType' => 'substitute'));
                        if ($dataArray['substituteEmail'] == 'parent_email') {
                            $substituteObj->setEmailType('parent_email');
                            $substituteObj->setEmailField(NULL);
                        } else {
                            $emailFieldObj = $em->getRepository('CommonUtilityBundle:FgCmAttribute')->find($dataArray['substituteEmail']);
                            $substituteObj->setEmailType('contact_field');
                            $substituteObj->setEmailField($emailFieldObj);
                        }
                        $em->persist($substituteObj);
                        $em->flush();
                    }
                }
            }
            // Add email settings.
            if (count($addEmailSettings)) {
                $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->addNewsletterEmailSettings($newsletterId, $addEmailSettings, $newsletterObj);
            }
            // Remove email settings.
            if (count($remEmailSettings)) {
                $em->getRepository('CommonUtilityBundle:FgCnNewsletterManualContactsEmail')->removeNewsletterEmailSettings($newsletterId, $remEmailSettings);
            }
        }
    }

    public function editnewsletterdetails($newsletterid, $clubId) {

        $query = $this->createQueryBuilder('nl')
                ->select('nl.subject', 'nl.senderName', 'nl.senderEmail', 'nl.salutationType', 'nl.salutation', 'nl.newsletterType', 'nl.publishType', 'nl.step', 'tmp.id AS templateId')
                ->leftJoin('nl.club', 'clb')
                ->leftJoin('nl.template', 'tmp')
                ->where('clb.id=:clubId')
                ->andWhere('nl.id=:id')
                ->setParameter('clubId', $clubId)
                ->setParameter('id', $newsletterid);

        $result = $query->getQuery()->getResult();

        $languageArray = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterPublishLang')->getNewsletterLanguageData($newsletterid);

        $finalLangArray['language'] = $languageArray;
        $finalLangArray['selectedlanguageCount'] = count($languageArray);
        $finalArray = array_merge($finalLangArray, $result[0]);

        return $finalArray;
    }

    /**
     * For collect the saved newsletter data
     * @param type $newsletterId
     */
    public function getNewsletterSettingFlags($newsletterId, $clubId) {
        $resultQuery = $this->createQueryBuilder('n')
                ->select('n.isSubscriberSelection', 'n.includeFormerMembers', 'rl.id', 'n.publishType', 'n.step', 'n.newsletterType', 'n.salutationType', 'n.salutation')
                ->leftJoin('n.recepientList', 'rl')
                ->leftJoin('n.club', 'cl')
                ->where('n.id=:newsletterId')
                ->andWhere('cl.id=:clubId')
                ->setParameter('newsletterId', $newsletterId)
                ->setParameter('clubId', $clubId);
        $result = $resultQuery->getQuery()->getResult();

        return $result;
    }

    /**
     * function to get the newsletter ids for switching from previous and receipients
     *
     * @param int $clubId the club id
     * @param string $type the newsletter type(draft,planned,sending and sent)
     * @param string $orderBy the order by field
     * @param string $orderAs the order as
     * @param string $newsletterType the newsletter type (newsletter or simple mail)
     * @param string $search the string which search for in sent
     *
     * @return array
     */
    public function getNewslettersNextPrev($clubId, $type, $orderBy = 'sendDate', $orderAs = 'desc', $newsletterType, $search = '') {
        $sendingCount = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSubQueryForSendingCount();
        $receiverCount = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSubQueryForReceiverCount();
        $openCount = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSubQueryForOpenCount();
        if ($newsletterType == 'newsletter') {
            $newsletterType = 'GENERAL';
        } else {
            $newsletterType = 'EMAIL';
        }
        $results = $this->createQueryBuilder('n')
                ->select("n.id");
        if ($orderBy != "openedAt") {
            $results->addSelect("(CASE WHEN n." . $orderBy . " IS NULL then 3 WHEN n." . $orderBy . "='' then 2 WHEN n." . $orderBy . "='0000-00-00 00:00:00' then 1 ELSE 0 END),n." . $orderBy . " AS HIDDEN ORD ");
        }
        if ($type == 'sending') {
            $results->addSelect('(' . $sendingCount->getDQL() . ') as recepientCount');
        } else if ($type == 'sent') {
            $results->addSelect('(' . $receiverCount->getDQL() . ') as recepientCount');
            $results->addSelect('(' . $openCount->getDQL() . ') as openedAt');
        }
        $results->where('n.club=:clubId')
                ->andWhere('n.status=:type')
                ->andWhere('n.newsletterType=:newsletterType');
        if ($type == 'sent') {
            $results->andWhere('n.senderEmail like :search OR n.subject like :search');
        }
        if ($orderBy != "openedAt") {
            $results->orderBy('ORD', $orderAs);
        } else {
            $results->orderBy('openedAt', $orderAs);
        }
        $results->setParameter('clubId', $clubId)
                ->setParameter('type', $type)
                ->setParameter('newsletterType', $newsletterType);
        if ($type == 'sent') {
            $results->setParameter('search', '%' . $search . "%");
        }

        $qryResults = $results->getQuery()->getResult();

        return $qryResults;
    }

    /**
     * function to delete language details
     *
     * @param int $newsletterId tnewsletterId
     *
     * @return array
     */
    public function deletelangData($newsletterId) {
        $conn = $this->getEntityManager()->getConnection();
        $conn->delete('fg_cn_newsletter_publish_lang', array('newsletter_id' => $newsletterId));
        return true;
    }

    /**
     * Update template id newsletter
     * @param obj $objNewsletter newsltter obj
     * @param obj $contactobj contact obj
     */
    public function updateTemplate($objNewsletter, $contactobj, $step) {
        $currDate = new \DateTime("now");
        $objNewsletter->setUpdatedBy($contactobj);
        $objNewsletter->setLastUpdated($currDate);
        $objNewsletter->setStep($step);
        $this->_em->persist($objNewsletter);
        $this->_em->flush();
    }

    /**
     * function to update send date
     *
     * @param obj $rowNewsletter
     * @param string $sendingType
     * @param date $sendingTime
     * @param int $contactId
     */
    public function updateNlSmSending($rowNewsletter, $sendingType, $sendingTime, $display, $contactId) {
        $currDate = new \DateTime("now");
        $contactId = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $rowNewsletter->setUpdatedBy($contactId);
        $rowNewsletter->setLastUpdated($currDate);
        $newsletterType = $rowNewsletter->getNewsletterType();
        $sendMode = $rowNewsletter->getSendMode();
        $sendDate = $rowNewsletter->getSendDate();
        if ($sendingType == "option1") {
            $rowNewsletter->setStatus('sending');
            $rowNewsletter->setLastSpoolContactId(0);
            $rowNewsletter->setsendDate($currDate);
        } else {
            $date = new \DateTime();
            $sendingTimeObj = $date->createFromFormat(FgSettings::getPhpDateTimeFormat(), $sendingTime);
            if ($sendingTimeObj->format('U') > strtotime(date('Y-m-d H:i:s'))) {
                $sendingTime = $sendingTimeObj->format('Y-m-d H:i:s');
                $rowNewsletter->setStatus('scheduled');
                $rowNewsletter->setSendDate(new \DateTime($sendingTime));
            } else {
                $rowNewsletter->setStatus('sending');
                $rowNewsletter->setLastSpoolContactId(0);
                $rowNewsletter->setsendDate($currDate);
            }
        }
        $rowNewsletter->setStep('6');
        $rowNewsletter->setIsDisplayInArchive($display);
        $this->_em->persist($rowNewsletter);
        $this->_em->flush();
    }

    /**
     * Function to update unique recipients count of newsletter.
     *
     * @param int    $newsletterId   Newsletter id
     * @param int    $clubId         Club id
     * @param object $container      Container object
     * @param int    $currContactId  Current contact id
     * @param object $newsletterObj  Newsletter object
     * @param string $clubSystemLang Club default system language
     */
    public function updateNewsletterRecipientsCount($newsletterId, $clubId, $container, $currContactId, $newsletterObj = null, $clubSystemLang = 'de') {
        if (!$newsletterObj) {
            $newsletterObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        }
        $type = ($newsletterObj->getPublishType() == 'MANDATORY') ? 'mandatory' : 'nonmandatory';
        $status = $newsletterObj->getStatus();
        $recipientsCount = $this->_em->getRepository('CommonUtilityBundle:FgCnRecepients')->getNewsletterRecipients($type, $status, $newsletterId, $clubId, $container, $currContactId, false, false, array(), true, $clubSystemLang);
        $newsletterObj->setRecepientCount($recipientsCount);
        $this->_em->persist($newsletterObj);
        $this->_em->flush();
    }

    /**
     * Function to update newsletter/simple mail status and insert log entries
     */
    public function updateStatusOfSendCompletedNewsletters() {
        $now = date('Y-m-d H:i:s');
        $subquery = $this->getCountOfNewslettersInSpool();
        $resultQuery = $this->createQueryBuilder('n')
                ->select('n.id')
                ->addSelect('(' . $subquery->getDQL() . ') AS spoolCount')
                ->where('n.step != 0')
                ->andWhere('n.status=:status')
                ->andWhere('n.sendDate <= :now')
                ->andWhere('n.lastSpoolContactId =-1')
                ->orderBy('n.sendDate', 'ASC')
                ->setParameters(array('now' => $now, 'status' => 'sending'));
        $result = $resultQuery->getQuery()->getArrayResult();

        foreach ($result as $res) {
            if ($res['spoolCount'] == 0) {
                $this->updateNewsletterStatus($res['id']);
                $this->insertContactCommunicationLog($res['id']);
                $this->insertSubscriberCommunicationLog($res['id']);
            }
        }
    }

    /**
     * Function to get count of mails in spool
     */
    protected function getCountOfNewslettersInSpool() {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(m.id) AS scount')
                ->from('CommonUtilityBundle:FgMailMessage', 'm')
                ->where('m.newsletter = n.id');

        return $query;
    }

    /**
     * Function to update newsletter status to sent
     * @param int $newsletterId
     */
    protected function updateNewsletterStatus($newsletterId) {
        $recipientCount = $this->getRecipientCountOfNewsletter($newsletterId);
        $newsletterObj = $this->find($newsletterId);
        $newsletterObj->setRecepientCount($recipientCount);
        $newsletterObj->setStatus('sent');
        $this->getEntityManager()->flush();
        $this->insertNewsletterLog($newsletterId, $recipientCount);
    }

    /**
     * Function to insert newsletter log
     * @param int $newsletterId   NewsletterId
     * @param int $recipientCount RecipientCount
     */
    protected function insertNewsletterLog($newsletterId, $recipientCount) {
        $newsletterObj = $this->find($newsletterId);
        $clubId = $newsletterObj->getClub();
        $date = $newsletterObj->getSendDate();
        $newsletterType = ($newsletterObj->getNewsletterType() == 'GENERAL') ? ($newsletterObj->getPublishType()) : 'SIMPLE EMAIL';
        $sentBy = $newsletterObj->getUpdatedBy();
        $subject = $newsletterObj->getSubject();
        $templateName = '';
        if ($newsletterObj->getNewsletterType() == 'GENERAL') {
            $templateId = $newsletterObj->getTemplate()->getId();
            $templateName = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->find($templateId)->getTitle();
        }

        $obj = new FgCnNewsletterLog();
        $obj->setClub($clubId);
        $obj->setDate($date);
        $obj->setNewsletter($newsletterObj);
        $obj->setRecepients($recipientCount);
        $obj->setNewsletterType($newsletterType);
        $obj->setSentBy($sentBy);
        $obj->setSubject($subject);
        $obj->setTemplate($templateName);

        $this->_em->persist($obj);
        $this->_em->flush();
    }

    /**
     * Function to get count of newsletter recipients
     * @param int $newsletterId
     *
     * @return int $count RecipientCount
     */
    protected function getRecipientCountOfNewsletter($newsletterId) {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(r.id) AS receiverCount')
                ->from('CommonUtilityBundle:FgCnNewsletterReceiverLog', 'r')
                ->where('r.newsletter = :newsletterId')
                ->andWhere('r.isSent=1')
                ->setParameter('newsletterId', $newsletterId);
        $count = $query->getQuery()->getSingleScalarResult();

        return $count;
    }

    /**
     * Function to insert contacts communication log
     * @param int $newsletterId newsletterId
     */
    protected function insertContactCommunicationLog($newsletterId) {
        $conn = $this->getEntityManager()->getConnection();
        $subQry = $conn->executeQuery("SELECT (GROUP_CONCAT(DISTINCT `contact_id` SEPARATOR ','))INTO @contactIds FROM `fg_cn_newsletter_receiver_log` WHERE `newsletter_id` = " . $newsletterId . " AND `contact_id` != '';");
        $qry = $conn->executeQuery("INSERT INTO `fg_cm_change_log`(`contact_id`, `club_id`, `date`, `kind`, `changed_by`, `newsletter_id`) "
                . "SELECT DISTINCT `fg_cn_newsletter_receiver_log`.`contact_id`,`fg_cn_newsletter_receiver_log`.`club_id`, `fg_cn_newsletter`.`send_date` AS sendDate, 'communication', `fg_cn_newsletter`.`updated_by`, `fg_cn_newsletter_receiver_log`.`newsletter_id` "
                . "FROM `fg_cn_newsletter_receiver_log` "
                . "LEFT JOIN `fg_cn_newsletter` ON `fg_cn_newsletter_receiver_log`.`newsletter_id` = `fg_cn_newsletter`.`id`"
                . "WHERE `newsletter_id`=" . $newsletterId . " AND `is_sent`=1 AND "
                . "FIND_IN_SET(`fg_cn_newsletter_receiver_log`.`contact_id`, @contactIds)");
    }

    /**
     *
     * @param int $clubId clubId
     * @param date $dateFrom dateFrom
     * @param date $dateTo dateTo
     * @return array
     */
    public function getNewsletterStatistics($clubId, $dateFrom = "", $dateTo = "") {
        $conn = $this->getEntityManager()->getConnection();
        $newsletterCondition = " AND club_id = $clubId ";
        $logCondition = " AND LOG.club_id = $clubId AND ( NEWSLETTER.`newsletter_type` = 'MANDATORY' OR NEWSLETTER.`newsletter_type` = 'SUBSCRIPTION' ) ";
        if ($dateFrom !== "") {
            $newsletterCondition .= " AND DATE(`date`) >= '" . $dateFrom . "' ";
            $logCondition .= " AND DATE(NEWSLETTER.`date`) >= '" . $dateFrom . "' ";
        }
        if ($dateTo !== "") {
            $newsletterCondition .= " AND DATE(`date`) <= '" . $dateTo . "' ";
            $logCondition .= " AND DATE(NEWSLETTER.`date`) <= '" . $dateTo . "' ";
        }
        $resultData = $conn->executeQuery("SELECT *, "
                        . "IF(TOTAL_RECEPIENTS = 0,'0',ROUND(TOTAL_OPENINGS * 100/ TOTAL_RECEPIENTS)) AS OPENING_PERCENT,"
                        . "IF(MANDATORY_COUNT = 0,'0',ROUND(MANDATORY_RECEPIENTS / MANDATORY_COUNT))  AS MANDATORY_RECEPIENT_PER_NEWSLETTER, "
                        . "IF(SUBSCRIPTION_COUNT = 0,'0',ROUND(SUBSCRIPTION_RECEPIENTS / SUBSCRIPTION_COUNT)) AS SUBSCRIPTION_RECEPIENT_PER_NEWSLETTER,"
                        . "IF(TOTAL_COUNT = 0,'0',ROUND(TOTAL_RECEPIENTS / TOTAL_COUNT)) AS TOTAL_RECEPIENT_PER_NEWSLETTER, "
                        . "IF(TOTAL_RECEPIENTS = 0,'0',ROUND(TOTAL_OPENINGS/TOTAL_COUNT)) TOTAL_OPENING_PER_NEWSLETTER, "
                        . "IF(TOTAL_RECEPIENTS = 0,'0',ROUND(TOTAL_OPENINGS/TOTAL_RECEPIENTS * 100)) RECEPIENT_PER_NEWSLETTER_OPENING_PERCENT "
                        . " FROM "
                        . "( SELECT MANDATORY_COUNT, SUBSCRIPTION_COUNT, TOTAL_COUNT, "
                        . "IF(MANDATORY_RECEPIENTS IS NULL,'0',MANDATORY_RECEPIENTS) MANDATORY_RECEPIENTS, IF(SUBSCRIPTION_RECEPIENTS IS NULL,'0',SUBSCRIPTION_RECEPIENTS) SUBSCRIPTION_RECEPIENTS, IF(TOTAL_RECEPIENTS IS NULL,'0',TOTAL_RECEPIENTS) TOTAL_RECEPIENTS, "
                        . "SUM(openings) AS TOTAL_OPENINGS FROM "
                        . "( SELECT IF(opened_at IS NULL,'0','1') openings,  "
                        . "( SELECT COUNT(*) FROM fg_cn_newsletter_log WHERE newsletter_type = 'MANDATORY' $newsletterCondition ) MANDATORY_COUNT, "
                        . "( SELECT COUNT(*) FROM fg_cn_newsletter_log WHERE newsletter_type = 'SUBSCRIPTION' $newsletterCondition ) SUBSCRIPTION_COUNT, "
                        . "( SELECT COUNT(*) FROM fg_cn_newsletter_log WHERE (newsletter_type = 'SUBSCRIPTION' OR newsletter_type = 'MANDATORY') $newsletterCondition  ) TOTAL_COUNT, "
                        . "( SELECT SUM(recepients) FROM fg_cn_newsletter_log WHERE newsletter_type = 'MANDATORY' $newsletterCondition ) MANDATORY_RECEPIENTS, "
                        . "( SELECT SUM(recepients) FROM fg_cn_newsletter_log WHERE newsletter_type = 'SUBSCRIPTION' $newsletterCondition ) SUBSCRIPTION_RECEPIENTS, "
                        . "( SELECT SUM(recepients) FROM fg_cn_newsletter_log WHERE (newsletter_type = 'SUBSCRIPTION' OR newsletter_type = 'MANDATORY') $newsletterCondition  ) TOTAL_RECEPIENTS "
                        . "FROM fg_cn_newsletter_log NEWSLETTER "
                        . " LEFT JOIN  fg_cn_newsletter_receiver_log LOG "
                        . " ON NEWSLETTER.newsletter_id = LOG.newsletter_id AND LOG.opened_at IS NOT NULL  $logCondition "
                        . ") TAB1 "
                        . ")TAB"
                )->fetchAll();
        return $resultData;
    }

    /**
     * Function to insert subscriber communication log
     * @param int $newsletterId
     */
    protected function insertSubscriberCommunicationLog($newsletterId) {
        $conn = $this->getEntityManager()->getConnection();
        $qry = $conn->executeQuery("INSERT INTO `fg_cn_subscriber_log`(`subscriber_id`, `club_id`, `newsletter_id`, `date`, `kind`, `changed_by`) "
                . "SELECT `fg_cn_newsletter_receiver_log`.`subscriber_id`,`fg_cn_newsletter_receiver_log`.`club_id`, `fg_cn_newsletter_receiver_log`.`newsletter_id`, `fg_cn_newsletter`.`send_date` AS sendDate, 'communication', `fg_cn_newsletter`.`updated_by` "
                . "FROM `fg_cn_newsletter_receiver_log` "
                . "LEFT JOIN `fg_cn_newsletter` ON `fg_cn_newsletter_receiver_log`.`newsletter_id` = `fg_cn_newsletter`.`id`"
                . "WHERE `newsletter_id`=" . $newsletterId . " AND `is_sent`=1 AND "
                . "`fg_cn_newsletter_receiver_log`.`subscriber_id` IN (SELECT `subscriber_id` FROM `fg_cn_newsletter_receiver_log` WHERE `newsletter_id` = " . $newsletterId . " AND `subscriber_id` IS NOT NULL)");
    }

    public function getSimpleMailStatistics($clubId, $dateFrom = "", $dateTo = "") {
        $conn = $this->getEntityManager()->getConnection();
        $newsletterCondition = " AND club_id = $clubId ";
        if ($dateFrom !== "") {
            $newsletterCondition .= " AND DATE(`date`) >= '" . $dateFrom . "' ";
        }
        if ($dateTo !== "") {
            $newsletterCondition .= " AND DATE(`date`) <= '" . $dateTo . "' ";
        }
        $resultData = $conn->executeQuery("SELECT *, "
                        . "IF(TOTAL_COUNT = 0,'0',TRUNCATE((TOTAL_RECEPIENTS / TOTAL_COUNT) , 1)) AS TOTAL_RECEPIENT_PER_NEWSLETTER "
                        . "FROM ("
                        . "SELECT COUNT(*) TOTAL_COUNT, IF(SUM(recepients) IS NULL, 0, SUM(recepients) ) TOTAL_RECEPIENTS FROM fg_cn_newsletter_log WHERE newsletter_type = 'SIMPLE EMAIL' $newsletterCondition"
                        . ") TAB"
                )->fetchAll();
        return $resultData;
    }

    /**
     * This function is used to get the salutation of a contact/subscriber
     * 
     * @param int    $contactId       The contact id
     * @param int    $clubId          The club id
     * @param string $clubSystemLang  The club default system language
     * @param string $clubDefaultLang The club default language
     * 
     * @return string The salutation text
     */
    public function getSalutationText($contactId, $clubId, $clubSystemLang, $clubDefaultLang = 'NULL') {
        $conn = $this->getEntityManager()->getConnection();
        if ($contactId == 0) {
            $result = $conn->executeQuery("select subscriberSalutationText($contactId, $clubId, '$clubSystemLang', '$clubDefaultLang') as salutation")->fetchAll();
        } else {
            $result = $conn->executeQuery("select salutationText($contactId, $clubId, '$clubSystemLang', NULL) as salutation")->fetchAll();
        }
        return $result[0]['salutation'];
    }

    /**
     * Function to create and update step 1 of newsletter and simplemail
     *
     * @param object $newsletter     Newsletter object
     * @param int    $newsletterId   Newsletter Id
     * @param array  $formData       Form data
     * @param int    $clubId         Club Id
     * @param int    $contactId      Current Contact Id
     * @param array  $languages      Language array
     *
     * @return int
     */
    public function generalStepSave($newsletter, $newsletterId, $formData, $clubId, $contactId, $languages) {
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $contactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $newTemplate = false;
        if ($formData['newsletterType'] == "GENERAL") {
            $templateObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->find($formData['templateId']);
            $templateLastUpdated = $templateObj->getLastUpdated();
            $oldTemplateId = ($newsletter->getTemplate()) ? $newsletter->getTemplate()->getId() : 0;
            //new template data to be used only if 1. templatre is changed 2. old template has been updated and step 1 is saved.
            $newTemplate = ($newsletterId == 0) ? false : ((($formData['templateId'] != $oldTemplateId) || ($oldTemplateId == 0) || ($templateLastUpdated > $newsletter->getTemplateUpdated())) ? true : false);
        }
        if ($newsletterId == 0) {
            $newsletter->setCreatedBy($contactobj);
            $newsletter->setCreatedAt(new \DateTime("now"));
            $newsletter->setStep(1);
            $newsletter->setIsSubscriberSelection(1);
            $newsletter->setRecepientCount(0);
            $newsletter->setIncludeFormerMembers(0);
            $newsletter->setClub($clubobj);
        }
        $newsletter->setSubject($formData['subject']);
        $newsletter->setSenderName($formData['senderName']);
        $newsletter->setSenderEmail($formData['Email']);
        $newsletter->setSalutationType($formData['salutationType']);
        $newsletter->setSalutation($formData['salutation']);
        $newsletter->setNewsletterType($formData['newsletterType']);
        $newsletter->setLastUpdated(new \DateTime("now"));
        $newsletter->setUpdatedBy($contactobj);
        $newsletter->setSendMode('IMMEDIATE');
        $newsletter->setSendDate(new \DateTime(date('0000-00-00 00:00:00')));
        $newsletter->setIsDisplayInArchive(0);
        $newsletter->setPublishType($formData['publishType']);
        $newsletter->setStatus('draft');
        $newsletter->setLanguageSelection($formData['languageSelected']);
        $newsletter->setLastSpoolContactId(0);
        $newsletter->setLastContactId(0);
        $newsletter->setLastSpoolAdminReceiverId(0);
        $newsletter->setIsCron(0);
        $newsletter->setReceiverType('SELECTED');
        $newsletter->setIsRecepientUpdated(0);
        $newsletter->setResentStatus(0);
        if ($formData['newsletterType'] == "GENERAL") {
            $newsletter->setTemplate($templateObj);
        }
        $this->_em->persist($newsletter);
        $this->_em->flush();


        $newsletterId = $newsletter->getId();
        $step = $newsletter->getStep();
        if (($formData['newsletterType'] == "GENERAL") && ($step >= 3) && $newTemplate) {
            //delete already saved sponsor contents and insert new sponsor contents if any based on selected template.
            $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->deleteNewsletterSponsorContents($newsletterId);
            $this->saveDefaultSponsorContents($clubId, $newsletterId, $formData['templateId']);
            //update template updated date in newsletter table
            $newsletter->setTemplateUpdated(new \DateTime("now"));
            $this->_em->persist($newsletter);
        }
        $newsletterObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $deleteExisting = $this->deletelangData($newsletterId);
        foreach ($languages as $lang) {
            $pubLang = new FgCnNewsletterPublishLang();
            $pubLang->setLanguageCode($lang);
            $pubLang->setNewsletter($newsletterObj);
            $this->_em->persist($pubLang);
        }
        $this->_em->flush();


        return $newsletterId;
    }

    /**
     * Function to save newsletter sponsor contents corresponding to selected template
     * 
     * @param int $clubId       ClubId
     * @param int $newsletterId NewsletterId
     * @param int $templateId   TemplateId
     */
    public function saveDefaultSponsorContents($clubId, $newsletterId, $templateId) {
        //save default sponsor contents
        $newsletterObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($newsletterId);
        $sponsorDetails = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getNewsletterTemplateSponsorContents($clubId, $templateId, false);
        foreach ($sponsorDetails as $key => $sponsorDetail) {
            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
            $content->setNewsletter($newsletterObj);
            $contentType = ($sponsorDetail['position'] == 'ABOVE') ? 'SPONSOR ABOVE' : (($sponsorDetail['position'] == 'BOTTOM') ? 'SPONSOR BOTTOM' : 'SPONSOR');
            $content->setContentType($contentType);
            $content->setSortOrder($sponsorDetail['sortOrder']);
            $content->setContentTitle($sponsorDetail['title']);
            $content->setSponsorAdWidth($sponsorDetail['sponsorAdWidth']);
            if (!empty($sponsorDetail['sponsorAdArea'])) {
                $adAreaObj = $this->_em->getRepository('CommonUtilityBundle:FgSmAdArea')->find($sponsorDetail['sponsorAdArea']);
                $content->setSponsorAdArea($adAreaObj);
            }
            $content->setIsActive(1);
            $this->_em->persist($content);
            $this->_em->flush();
            //add new services
            $serviceIds = explode(',', $sponsorDetail['services']);
            foreach ($serviceIds as $serviceId) {
                $services = new \Common\UtilityBundle\Entity\FgCnNewsletterContentServices();
                $services->setContent($content);
                $serviceObj = $this->_em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
                $services->setService($serviceObj);
                $this->_em->persist($services);
            }
        }
        $this->_em->flush();

        //update sort order of all contents in the order INTRO, SPONSOR ABOVE, ALL OTHER CONTENTS, SPONSOR, CLOSING, SPONSOR BOTTOM
        $newsletterContents = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getOrderedNewsletterContentIds($newsletterId);
        $sortOrder = 1;
        foreach ($newsletterContents as $key => $newsletterContent) {
            $contentObj = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($newsletterContent['id']);
            $contentObj->setSortOrder($sortOrder);
            $sortOrder++;
            $this->_em->persist($contentObj);
        }
        $this->_em->flush();
    }

    /**
     * function to get the email fields of newsletter for recepients
     *
     * @param int $id thre newsletter id
     * @param int $clubId the club id
     *
     * @return array
     */
    public function getEmailFieldsofRecepients($id, $clubId, $container) {
        $manualemailFields = array();
        $manualsubFields = array();
        $recepientsemailFields = array();
        $recepientssubFields = array();
        $return = array();
        $maunalField = '';
        $maunalsubField = '';
        $recepientField = '';
        $recepientsubField = '';
        $translator = $container->get('translator');
        $manual = $translator->trans('NL_MANUAL_RECEPIENTS');

        $result = $this->createQueryBuilder('n')
                ->select(" n.id as newsletterId")
                ->addSelect("(SELECT GROUP_CONCAT(ca.fieldname) FROM CommonUtilityBundle:FgCnNewsletterManualContactsEmail ne left join CommonUtilityBundle:FgCmAttribute ca with ca.id = ne.emailField where ne.selectionType = 'main' and ne.newsletter =:newsletterId group by n.id )manualemailFields")
                ->addSelect("(select GROUP_CONCAT(ca1.fieldname) FROM CommonUtilityBundle:FgCnNewsletterManualContactsEmail ne1 left join CommonUtilityBundle:FgCmAttribute ca1 with ca1.id = ne1.emailField where ne1.selectionType = 'substitute' and ne1.newsletter =:newsletterId group by n.id)manualsubFields")
                ->addSelect("(SELECT GROUP_CONCAT(ca2.fieldname) FROM CommonUtilityBundle:FgCnRecepientsEmail re  left join CommonUtilityBundle:FgCmAttribute ca2  with ca2.id = re.emailField where re.selectionType = 'main' and re.recepientList = n.recepientList group by n.id)recepiengtemailFields ")
                ->addSelect("(SELECT GROUP_CONCAT(ca3.fieldname) FROM CommonUtilityBundle:FgCnRecepientsEmail re1  left join CommonUtilityBundle:FgCmAttribute ca3  with ca3.id = re1.emailField where re1.selectionType = 'substitute' and re1.recepientList = n.recepientList group by n.id)recepiengtsubFields ")
                ->where("n.club=:clubId")
                ->andWhere("n.id=:newsletterId")
                ->setParameter("clubId", $clubId)
                ->setParameter("newsletterId", $id)
                ->orderBy("n.id");
        $results = $result->getQuery()->getResult();

        if ($results[0]['manualemailFields'] != "") {
            $manualemailFields = explode(',', $results[0]['manualemailFields']);
        }
        if ($results[0]['manualsubFields'] != "") {
            $manualsubFields = explode(',', $results[0]['manualsubFields']);
        }
        if ($results[0]['recepiengtemailFields'] != "") {
            $recepientsemailFields = explode(',', $results[0]['recepiengtemailFields']);
        }
        if ($results[0]['recepiengtsubFields'] != "") {
            $recepientssubFields = explode(',', $results[0]['recepiengtsubFields']);
        }
        $maunalField = implode(', ', $manualemailFields);
        $maunalsubField = implode(', ', $manualsubFields);
        $recepientField = implode(', ', $recepientsemailFields);
        $recepientsubField = implode(', ', $recepientssubFields);

        if ($maunalField != "") {
            $return['emailField'] = $recepientField . $manual . $maunalField . ')';
        } else {
            $return['emailField'] = $recepientField;
        }

        if ($maunalsubField != "") {
            $return['subField'] = $recepientsubField . $manual . $maunalsubField . ')';
        } else {
            $return['subField'] = $recepientsubField;
        }

        return $return;
    }

    /**
     * Function to get newsletter sidebar contents for preview
     *
     * @param int $clubId clubId
     * @param int $newsletterId newsletterId
     *
     * @return array
     */
    public function getNewsletterSidebarContents($clubId, $newsletterId) {
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT NS.id as contentId, N.step, N.publish_type, N.club_id,N.id as newsletterId,N.template_id, "
                . "NS.id as contentId,  NS.sort_order,"
                . "NS.sponsor_ad_area_id, NS.title "
                . "FROM fg_cn_newsletter N "
                . "LEFT JOIN fg_cn_newsletter_sidebar NS ON NS.newsletter_id=N.id  "
                . "WHERE N.id=:newsletterId AND N.club_id=:clubId ORDER BY NS.sort_order ASC";
        $stmt = $conn->executeQuery($sql, array('newsletterId' => $newsletterId, 'clubId' => $clubId));
        $result = $stmt->fetchAll(\PDO::FETCH_GROUP);
        foreach ($result as $key => $value) {
            for ($i = 0; $i < count($result[$key]); $i++) {
                $result[$key][$i]['services'] = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebarServices')->getServicesofSidebarContent($result[$key][$i]['contentId']);
            }
        }

        return $result;
    }

    /**
     * Function to build newsletter sidebar content preview array
     *
     * @param array $newsletterDetails
     * @param obj $container        Conatiner object
     * @param int $clubId        current clubId
     * @param obj $club         club service
     * 
     * @return type
     */
    public function newsletterSidebarPreviewArrayBulider($newsletterDetails, $container, $clubId, $club) {
        $result = $contentsArray = array();
        foreach ($newsletterDetails as $contentId => $newsletterContentArray) {
            $result['step'] = $newsletterContentArray[0]['step'];
            $result['publishType'] = $newsletterContentArray[0]['publish_type'];
            $clubId = $newsletterContentArray[0]['club_id'];
            $content = array();
            foreach ($newsletterContentArray as $newsletterContent) {
                if (empty($content)) {
                    $content['title'] = $newsletterContent['title'];
                    $content['sponsorAdArea'] = $newsletterContent['sponsor_ad_area_id'];
                    $content['id'] = $newsletterContent['contentId'];
                    $content['sortOrder'] = $newsletterContent['sort_order'];
                    $content['services'] = explode(",", $newsletterContent['services']);
                }
            }
            if (!empty($content)) {
                $contentsArray[] = $content;
            }
        }
        $result = $contentsArray;

        return $result;
    }

    /**
     * 
     * @param string $status status of mail
     * @return array stucked mail details
     */
    public function stuckedNewsletterDetails($status) {
        $conn = $this->getEntityManager()->getConnection();
        $resultData = $conn->executeQuery("SELECT cl.title AS clubTitle,(SELECT sum(dn.recepient_count) FROM `fg_cn_newsletter` dn WHERE dn.status='" . $status . "' AND dn.send_date <  DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -12 HOUR) AND  dn.club_id=n.club_id)  as recepientCount, cl.url_identifier AS clubUrl,(Select count(ns.id) FROM fg_cn_newsletter ns WHERE ns.status='" . $status . "' AND ns.send_date <  DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -12 HOUR) AND  ns.club_id=n.club_id) stuckcount  FROM `fg_cn_newsletter` n LEFT JOIN fg_club cl ON cl.id=n.club_id"
                        . " WHERE n.status ='" . $status . "' AND n.send_date < DATE_ADD(CURRENT_TIMESTAMP, INTERVAL -12 HOUR) GROUP BY n.club_id")->fetchAll();

        return $resultData;
    }

    /**
     * Function to get the details of newsletter archive element to display in website
     * 
     * @param int    $clubId             Club id
     * @param string $status             Newsletter status
     * @param int    $isDisplayInArchive Display flag
     * 
     * @return array
     */
    public function getNewsletterForWebsiteArchive($clubId, $status = 'sent',$isDisplayInArchive = 1)
    {
        $results = $this->createQueryBuilder('n')
                ->select("n.id, n.sendDate as date, n.subject as title")
                ->where('n.club=:clubId')
                ->andWhere('n.status=:status')
                ->andWhere('n.isDisplayInArchive=:isDisplayInArchive')
                ->orderBy("n.sendDate","DESC")
                ->setParameter('clubId', $clubId)
                ->setParameter('status', $status)
                ->setParameter('isDisplayInArchive', $isDisplayInArchive);

        return $results->getQuery()->getResult();
    }

}
