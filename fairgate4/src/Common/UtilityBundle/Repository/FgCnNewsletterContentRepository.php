<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Filesystem\Filesystem;
use Common\UtilityBundle\Util\FgSettings;
use Common\FilemanagerBundle\Util\FileChecking;
use Internal\GalleryBundle\Util\GalleryList;
/**
 * This repository is used for handling newsletter content manipulation
 *
 *
 */
class FgCnNewsletterContentRepository extends EntityRepository {

    /**
     * Function to get newsletter content details
     * 
     * @param object $container       Container object
     * @param int    $clubId          ClubId
     * @param int    $newsletterId    NewsletterId
     * @param int    $templateId      TemplateId
     * @param string $mode            Preview/cron
     * @param int    $contactId       Contact id
     * @param string $clubDefaultLang Club default language
     *
     * @return array $result NewsletterContentArray
     */
    public function getNewsletterContentDetails($container, $clubId, $newsletterId, $templateId = 0, $mode = 'preview', $contactId = 0, $clubDefaultLang = 'de') {
        $club = $container->get('club');
        $bookedModuleDetails = $club->get('bookedModulesDet');
        $translator = $container->get('translator');
        $em = $container->get('doctrine')->getManager();
        $previewData = $newsletter = array();
        $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
        $getBaseUrls = $this->getBaseUrls($container, $checkClubHasDomain, $club, $mode, $clubId);
        $texttype = in_array('frontend1', $bookedModuleDetails) ? 'teaser' : 'fulltext';
        if ($newsletterId > 0) {
            $newsletterDetails = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getNewsletterDetailsForPreview($clubId, $newsletterId);
            reset($newsletterDetails);
            $key = key($newsletterDetails);
            $templateId = (!empty($newsletterDetails[$key][0]['template_id'])) ? $newsletterDetails[$key][0]['template_id'] : 0; 
            $newsletterId = empty($newsletterDetails[$key][0]['newsletterId']) ? 0 : $newsletterDetails[$key][0]['newsletterId'];
            $newsletter = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->newsletterPreviewArrayBulider($newsletterDetails, $container, false, $clubId, $club, $getBaseUrls['baseUrl'], $mode);
            //case when newsletter duplicated senddate is 00.00.0000
            if (date_create_from_format(FgSettings::getPhpDateFormat(), $newsletter['sendDate'])) {
                $date = new \DateTime(); 
                $sentDateTimestamp = $date->createFromFormat(FgSettings::getPhpDateFormat(), $newsletter['sendDate'])->format('u');
                if ($sentDateTimestamp <= 0) {
                    $newsletter['sendDate'] = $date->format(FgSettings::getPhpDateFormat());
                }
            }
        }
        if ($templateId > 0) {
            $previewDetails = $em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->edittemplatedetails($templateId, $clubId);
            $previewData['bgColor'] = $previewDetails['colorBg'];
            $previewData['textColor'] = $previewDetails['colorStdText'];
            $previewData['tocBgColor'] = $previewDetails['colorTocBg'];
            $previewData['tocTextColor'] = $previewDetails['colorTocText'];
            $previewData['titleTextColor'] = $previewDetails['colorTitleText'];
            $previewData['headerImage'] = $previewDetails['headerImage'];
            $previewData['imageFlag'] = 'new';
            $texttype = $previewDetails['articleDisplay'];
        } else {
            $previewData['bgColor'] = '#cccccc';
            $previewData['textColor'] = '#000000';
            $previewData['tocBgColor'] = '#0099cc';
            $previewData['tocTextColor'] = '#000000';
            $previewData['titleTextColor'] = '#000000';
            $previewData['headerImage'] = 'header_image.jpg';
            $previewData['imageFlag'] = 'default';
        }
        $previewData['clubId'] = $clubId;
        $previewData['clickText'] = $translator->trans('NEWS_LETTER_CLICK_MESSAGE');
        if (empty($newsletterId)) {
            if ($templateId > 0) {
                $salutation = ($previewDetails['salutationType'] == "INDIVIDUAL") ? $translator->trans('NL_PERSONAL_SALUTATION') : ( ($previewDetails['salutationType'] == "SAME") ? $previewDetails['salutation'] : "" );
            } else {
                $salutation = $translator->trans('NL_PERSONAL_SALUTATION');
            }
            $previewData['salutation'] = $salutation;
            $previewData['sendDate'] = date($club->get('phpdate'));
            $sponsorContents = $this->getTemplateSponsorContents($clubId, $templateId, $container, $club, $em);
            $previewData['contents'] = $this->getNewsletterContents($texttype, $container, $sponsorContents['CONTENT']);
            $previewData['sponsorAboveContents'] = $sponsorContents['ABOVE'];
            $previewData['sponsorBottomContents'] = $sponsorContents['BOTTOM'];
            $previewData['intro'] = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Praesent aliquam, justo convallis luctus rutrum, erat nulla fermentum diam, at nonummy quam ante acquam. Maecenas urna purus, fermentum id, molestie in,commodo porttitor, felis.';
            $previewData['closingWords'] = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Praesent aliquam, justo convallis luctus rutrum, erat nulla fermentum diam, at nonummy quam ante acquam. Maecenas urna purus, fermentum id, molestie in,commodo porttitor, felis.';
            $previewData['contactId'] = '';
            $previewData['tableOfContents'] = array(
                '1' => $translator->trans('NL_PREVIEW_TITLE_OF_TEXT_LEFT'),
                '2' => $translator->trans('NL_PREVIEW_TITLE_OF_TEXT_RIGHT'),
                '3' => $translator->trans('NL_PREVIEW_IMAGE')
            );
        }
        //to display sponsors in newsletter sidebar only if sponsor module is booked
        $previewData['sidebarSponsors'] = (in_array('sponsor', $bookedModuleDetails)) ? $em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebar')->getSidebarSponsors($newsletterId, $container) : array();
        $result = $previewData + $newsletter;
        $result['mode'] = $mode;
        if ($mode == 'cron') {
            $result['salutation'] = '@@#salutation#@@';
        } elseif ($mode == 'testmail') {
            $result['salutation'] = '@@#salutation#@@';
        } elseif (($mode == 'designpreview') && ($result['salutation'] == 'NL_PERSONAL_SALUTATION')) {
            $result['salutation'] = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText(0, $clubId, $container->get('club')->get('default_system_lang'), $clubDefaultLang);
        } elseif ($result['salutation'] == 'NL_PERSONAL_SALUTATION') {
            $result['salutation'] = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText($contactId, $clubId, $clubDefaultLang);
        }

        $result['actualPath'] = FgUtility::getBaseUrl($container, $clubId);

        $result['baseUrl'] = $getBaseUrls['baseUrl'];
        $result['baseUrlWithUrlIdentifier'] = $getBaseUrls['baseUrlWithUrlIdentifier'];
        $result['mainBaseUrl'] = FgUtility::getBaseUrl($container, $clubId, $mode);
        $result['isSponsorModuleBooked'] = (in_array('sponsor', $bookedModuleDetails)) ? true : false;
        
        return $result;
    }

    /**
     * Function to get the base urls according to domain conditions
     * 
     * @param Object $container          Container object
     * @param Array  $checkClubHasDomain Club domain details
     * @param Object $club               Club service object
     * @param string $mode               Preview/cron/testmail/designpreview
     * @param int    $clubId             Club id
     * 
     * @return array
     */
    public function getBaseUrls($container, $checkClubHasDomain, $club, $mode, $clubId) {
        switch (true) {
            case ($checkClubHasDomain && ($container->getParameter('kernel.environment') == 'domain') && $club->get('isMainDomain')) :
                $baseUrl = $checkClubHasDomain['domain'];
                $baseUrlWithUrlIdentifier = $checkClubHasDomain['domain'];
                break;
            case ($checkClubHasDomain && ($container->getParameter('kernel.environment') == 'domain') && !$club->get('isMainDomain')) :
                $baseUrl = 'http://' . $club->get('currentDomainName');
                $baseUrlWithUrlIdentifier = 'http://' . $club->get('currentDomainName');
                break;
            case ($checkClubHasDomain && ($container->getParameter('kernel.environment') != 'domain') && ($mode == 'preview' || $mode == 'testmail' || $mode == 'designpreview')) :
                $baseUrl = FgUtility::getBaseUrl($container, $clubId, $mode);
                $baseUrlWithUrlIdentifier = FgUtility::getBaseUrl($container, $clubId, $mode) . '/' . $club->get('clubUrlIdentifier');
                break;
            case ($checkClubHasDomain && ($container->getParameter('kernel.environment') != 'domain') && ($mode == 'cron')) :
                $baseUrl = $checkClubHasDomain['domain'];
                $baseUrlWithUrlIdentifier = $checkClubHasDomain['domain'];
                break;
            default:
                $baseUrl = FgUtility::getBaseUrl($container, $clubId);
                $baseUrlWithUrlIdentifier = FgUtility::getBaseUrl($container, $clubId) . '/' . $club->get('clubUrlIdentifier');
                break;
        }

        return array('baseUrl' => $baseUrl, 'baseUrlWithUrlIdentifier' => $baseUrlWithUrlIdentifier);
    }

    /**
     * Method to get details of template sponsor contents
     *
     * @param int    $clubId      current club id
     * @param int    $templateId  template Id
     * @param object $container   container object
     * @param object $club        club object
     * @param object $em          entity  manager object
     *
     * @return array
     */
    private function getTemplateSponsorContents($clubId, $templateId, $container, $club, $em) {
        $result = array("CONTENT" => array(), "ABOVE" => array(), "BOTTOM" => array());
        $templateSponsorContents = $em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplate')->getNewsletterTemplateSponsorContents($clubId, $templateId, true);
        foreach ($templateSponsorContents as $key => $templateSponsorContent) {
            $services = (count($templateSponsorContent['services']) > 0) ? implode(",", $templateSponsorContent['services']) : '';
            if ($services) {
                $templateSponsorContents[$key]['sponsorAds'] = $em->getRepository('CommonUtilityBundle:FgSmAdArea')->getDetailsOfSponsorAdPreview($services, $templateSponsorContent['sponsorAdArea'], $templateSponsorContent['sponsorAdWidth'], $clubId, $container, $club);
                $templateSponsorContents[$key]['type'] = "SPONSOR";
                $result[$templateSponsorContent['position']][] = $templateSponsorContents[$key];
            }
        }

        return $result;
    }

    /**
     * Function to get article content
     *
     * @param string $texttype        teaser/text
     * @param object $container       Container Object
     * @param array  $sponsorContents array of template sponsor contents
     *
     * @return array $newsletterContents
     */
    public function getNewsletterContents($texttype, $container, $sponsorContents) {
        $translator = $container->get('translator');
        if ($texttype == 'teaser') {
            $text = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Praesent aliquam, justo convallis luctus rutrum.....';
        } else {
            $text = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Praesent aliquam, justo convallis luctus rutrum, erat nulla fermentum diam, at nonummy quam ante ac quam. Maecenas urna purus, fermentum id, molestie in, commodo porttitor, felis. Nam blandit quam ut lacus. Quisque ornare risus quis ligula. Phasellus tristique purus a augue condimentum adipiscing. Aenean sagittis. Etiam leo pede, rhoncus venenatis, tristique in, vulputate at, odio. Donec et ipsum et sapien vehicula nonummy. Suspendisse potenti. Fusce varius urna id quam. Sed neque mi, varius eget, tincidunt nec, suscipit id, libero. In eget purus. Vestibulum ut nisl. Donec eu mi sed turpis feugiat feugiat. Integer turpis arcu, pellentesque eget, cursus et, fermentum ut, sapien. Fusce metus mi, eleifend sollicitudin, pellentesque eget, cursus et, fermentum ut, sapien. Fusce metus mi, eleifend sollicitudin, molestie id, varius et, nibh. Donec nec libero.';
        }
        $newsletterContents = array(
            '1' => array(
                'type' => 'team',
                'title' => $translator->trans('NL_PREVIEW_TITLE_OF_TEXT_LEFT'),
                'imgPosition' => 'LEFT',
                'text' => $text,
                'text_type' => $texttype,
                'document' => array(),
                'media' => array(
                    0 => array
                        (
                        'type' => 'image',
                        'media' => 'img.jpg',
                        'description' => '',
                        'mediaPath' => '/fgcustom/img/newsletter/',
                        'mediaOrgPath' => '/fgcustom/img/newsletter/',
                        'virtualFilePath' => '/fgcustom/img/newsletter/img.jpg'
                    ),
                    1 => Array
                        (
                        'type' => 'image',
                        'media' => 'img.jpg',
                        'description' => '',
                        'mediaPath' => '/fgcustom/img/newsletter/',
                        'mediaOrgPath' => '/fgcustom/img/newsletter/',
                        'virtualFilePath' => '/fgcustom/img/newsletter/img.jpg'
                    )
                )
            ),
            '2' => array(
                'type' => 'article',
                'title' => $translator->trans('NL_PREVIEW_TITLE_OF_TEXT_RIGHT'),
                'imgPosition' => 'RIGHT',
                'text' => $text,
                'text_type' => $texttype,
                'document' => array(),
                'media' => array(
                    0 => array
                        (
                        'type' => 'image',
                        'media' => 'img.jpg',
                        'description' => '',
                        'mediaPath' => '/fgcustom/img/newsletter/',
                        'mediaOrgPath' => '/fgcustom/img/newsletter/',
                        'virtualFilePath' => '/fgcustom/img/newsletter/img.jpg'
                    ),
                    1 => Array
                        (
                        'type' => 'image',
                        'media' => 'img.jpg',
                        'description' => '',
                        'mediaPath' => '/fgcustom/img/newsletter/',
                        'mediaOrgPath' => '/fgcustom/img/newsletter/',
                        'virtualFilePath' => '/fgcustom/img/newsletter/img.jpg'
                    )
                )
            ),
            '3' => array('type' => 'IMAGE', 'title' => 'Image', 'path' => '/templates/newsletter_themes/default/images/', 'image' => 'img_02.jpg', 'imageLink' => '')
        );

        $newsletterContentsArray = array_merge($newsletterContents, $sponsorContents);

        return $newsletterContentsArray;
    }

    /**
     * Function to save newsletter contents
     *
     * @param type $formArray
     * @param type $clubData
     * @param type $container
     * @return boolean
     */
    public function saveNewsletterContent($formArray, $clubData, $container) {

        $em = $this->getEntityManager();
        $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($clubData['newsletterId']);
        $totalCount = sizeof($formArray['image']) + sizeof($formArray['article']) + 1;
        foreach ($formArray as $type => $data) {
            foreach ($data as $contentId => $value) {
                switch ($type) {
                    //Save intro closing content
                    case 'intro': case 'closing';
                        $intro = $em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($contentId);
                        if (empty($intro)) {
                            $intro = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
                            $intro->setNewsletter($newsletterObj);
                            $intro->setContentType(strtoupper($type));
                        }
                        $intro->setSortOrder($value['sort']);
                        $intro->setIsActive($value['isActive']);
                        $intro->setIntroClosingWords(html_entity_decode($value['text'], ENT_QUOTES, 'UTF-8'));
                        $em->persist($intro);
                        $em->flush();
                        break;
                    //Save table of contents settings
                    case 'toc':
                        $tocActive = ($value['isActive'] == 1) ? 0 : 1;
                        $newsletterObj->setIsHideTableContents($tocActive);
                        $em->persist($newsletterObj);
                        $em->flush();
                        break;
                    //Save image content
                    case 'image':
                        $content = $em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($contentId);
                        if ($value['isDelete'] == 1) {
                            $em->remove($content);
                            $em->flush();
                            break;
                        }
                        if (empty($content)) {
                            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
                            $content->setNewsletter($newsletterObj);
                            $content->setContentType('IMAGE');
                        }
                        $content->setIsActive($value['isActive']);
                        $content->setImageLink($value['linkURL']);
                        if(is_array($value['images'])){
                            $valueArray = array_values($value['images']);
                           if (array_key_exists('galleryItemId', $valueArray[0])) {
                              $value['images']['itemsId'] =  $valueArray[0]['galleryItemId'];
                           }
                        }
                        if (!empty($value['images']['itemsId'])) { //case when browse from gallery
                            //CASE TO BE DONE IN FUTURE --------------------------------------------------
                            $galleryItemObj = $em->getRepository('CommonUtilityBundle:FgGmItems')->find($value['images']['itemsId']);
                            if ($galleryItemObj) {
                                $content->setItems($galleryItemObj);
                            }
                        } else if (!empty($value['images']['filename']) && $value['images']['filename'] != 'old') { //case when not browsing from file manager
                            $galleryItemObj = $this->uploadToGallery($value['images']['filename'], $clubData['clubId'], $container, 'newsletter-image');
                            $content->setItems($galleryItemObj);
                        }
                        $content->setSortOrder($value['sort']);
                        $em->persist($content);
                        $em->flush();
                        break;
                    //Save article contents
                    case 'article':
                        $content = $em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($contentId);
                        if ($value['isDelete'] == 1) {
                            if ($content) {
                                $em->remove($content);
                                $em->flush();
                            }
                            break;
                        }
                        if (empty($content)) {
                            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
                            $content->setNewsletter($newsletterObj);
                            $content->setContentType('ARTICLE');
                        }
                        $content->setIsActive($value['isActive']);
                        $content->setSortOrder($value['sort']);
                        $content->setPicturePosition(strtoupper($value['imgPostion']));
                        $em->persist($content);
                        $em->flush();
                        $article = $em->getRepository('CommonUtilityBundle:FgCnNewsletterArticle')->findOneByContent2($content->getId());
                        if (empty($article)) {
                            $article = new \Common\UtilityBundle\Entity\FgCnNewsletterArticle();
                            $article->setContent2($content);
                        }

                        $article->setTitle($value['title']);
                        $article->setTeaserText($value['teasertext']);
                        $article->setContent(html_entity_decode($value['text'], ENT_QUOTES, 'UTF-8'));
                        $em->persist($article);
                        $em->flush();
                        if (!empty($value['images'])) {
                            foreach ($value['images'] as $key => $image) {
                                if ($image['isDeleted']) {
                                    $articleMedia1 = $em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleMedia')->find($key);
                                    if ($articleMedia1) {
                                        $em->remove($articleMedia1);
                                        $em->flush();
                                    }
                                }
                                //case : only updating description on an existing one
                                if (empty($image['filename']) && empty($image['filemanagerId'])) {
                                    $articleMedia1 = $em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleMedia')->find($key);
                                    if ($articleMedia1) {
                                        $articleMedia1->setDescription($image['description']);
                                        if ($image['imgorder']) {
                                            $articleMedia1->setSortOrder($image['imgorder']);
                                        }
                                        $em->persist($articleMedia1);
                                        $em->flush();
                                    }
                                    continue;
                                }
                                //case: when broswe from gallery table
                                //CASE TO BE DONE IN FUTURE ----------------------------------------------------------
                                if (!empty($image['galleryItemId'])) {
                                    $galleryItemObj = $em->getRepository('CommonUtilityBundle:FgGmItems')->find($image['galleryItemId']);
                                    if ($galleryItemObj) {
                                        $articleMedia = new \Common\UtilityBundle\Entity\FgCnNewsletterArticleMedia();
                                        $articleMedia->setMediaType('image');
                                        $articleMedia->setArticle($article);
                                        $articleMedia->setGalleryItem($galleryItemObj);
                                        $articleMedia->setDescription($image['description']);
                                        if ($image['imgorder']) {
                                            $articleMedia->setSortOrder($image['imgorder']);
                                        }
                                        $em->persist($articleMedia);
                                        $em->flush();
                                    }
                                    continue;
                                }

                                //case: when uploading an image 
                                $galleryItemObj = $this->uploadToGallery($image['filename'], $clubData['clubId'], $container, 'newsletter-articleimage');
                                $articleMedia = new \Common\UtilityBundle\Entity\FgCnNewsletterArticleMedia();
                                $articleMedia->setMediaType('image');
                                $articleMedia->setArticle($article);
                                $articleMedia->setGalleryItem($galleryItemObj);

                                $articleMedia->setDescription($image['description']);
                                if ($image['imgorder']) {
                                    $articleMedia->setSortOrder($image['imgorder']);
                                }
                                $em->persist($articleMedia);
                                $em->flush();
                            }
                        }

                        //article attachments
                        if (!empty($value['attachments'])) {
                            $this->saveArticleAttachments($value['attachments'], $article, $container, $clubData['clubId']);
                        }
                        break;
                    case 'cms_article'://existing article
                        $content = $em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($contentId);
                        if ($value['isDelete'] == 1) {
                            if ($content) {
                                $em->remove($content);
                                $em->flush();
                            }
                            break;
                        }
                        if (empty($content)) {
                            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
                            $content->setNewsletter($newsletterObj);
                        }
                       
                        if(!empty($value['id'])){
                            $articleObj = $em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($value['id']);
                            $content->setArticle($articleObj);                        
                            $content->setArticleLang($value['lang']);
                            $content->setContentType('EXISTING_ARTICLE');
                            $content->setIsActive($value['isActive']);
                            $content->setSortOrder($value['sort']);
                            $content->setPicturePosition(strtoupper($value['imgPostion']));
                            $content->setIncludeAttachments($value['isTakeOver']);
                            $em->persist($content);
                            $em->flush();
                        }
                        
                        break;
                    //Save sponsor ads
                    case 'sponsor':
                    case 'sponsor_above':
                    case 'sponsor_bottom':
                        $content = $em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->find($contentId);
                        if ($value['isDelete'] == 1) {
                            $em->remove($content);
                            $em->flush();
                            break;
                        }
                        if (empty($content)) {
                            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
                            $content->setNewsletter($newsletterObj);
                            $contentType = ($type == 'sponsor_above') ? 'SPONSOR ABOVE' : (($type == 'sponsor_bottom') ? 'SPONSOR BOTTOM' : 'SPONSOR');
                            $content->setContentType($contentType);
                        }
                        $content->setSortOrder($value['sort']);
                        $content->setContentTitle($value['title']);
                        $content->setSponsorAdWidth($value['width']);
                        if (!empty($value['areas'])) {
                            $adAreaObj = $em->getRepository('CommonUtilityBundle:FgSmAdArea')->find($value['areas']);
                            $content->setSponsorAdArea($adAreaObj);
                        } else {
                            $content->setSponsorAdArea(null);
                        }
                        $content->setIsActive($value['isActive']);
                        $em->persist($content);
                        $em->flush();
                        //remove already assigned services
                        $em->getRepository('CommonUtilityBundle:FgCnNewsletterContentServices')->removeServices($content->getId());
                        //add new services
                        foreach ($value['services'] as $serviceId) {
                            $services = new \Common\UtilityBundle\Entity\FgCnNewsletterContentServices();
                            $services->setContent($content);
                            $serviceObj = $em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
                            $services->setService($serviceObj);
                            $em->persist($services);
                            $em->flush();
                        }
                        break;
                }
            }
        }
        if ($newsletterObj->getStep() < 3) {
            $club = $container->get('club');
            $step = in_array('sponsor', $club->get('bookedModulesDet')) ? 4 : 3;
            $newsletterObj->setStep($step);
            $em->persist($newsletterObj);
            $em->flush();
        }
        return true;
    }

    /**
     *  Method to upload file and save to gallery tables
     * 
     * @param string  $tempFileName   Filenme to upload
     * @param int     $clubId         current clubId
     * @param object  $container      container object
     * @param string  $source         source of galley item (newsletter-image/newsletter-articleimage)
     * @param array   $imageDetails   array
     * 
     * @return object $galleryItemObj
     */
    private function uploadToGallery($tempFileName, $clubId, $container, $source, $imageDetails = array()) {
        $galleryListObj = new GalleryList($container);
        $imageDetails = $galleryListObj->movetoclubgallery(array($tempFileName), array($tempFileName), $clubId);
        $em = $this->getEntityManager();
        $imageDetails['type'] = 'IMAGE';
        $imageDetails['imgCount'] = 1;
        $imageDetails['source'] = $source;
        $imageDetails['clubId'] = $clubId;
        $imageDetails['contactId'] = $container->get('contact')->get('id');
        $imageDetails['imgScope'][0] = 'PUBLIC';
        $imageDetails['uploadedImageId'][0] = 0;
        $galleryUploadFolder = FgUtility::getUploadFilePath($clubId, 'communicationimages');
        $fileName = $imageDetails['fileName'][0];
        $imageDetails['fileSize'][0] = filesize($galleryUploadFolder . "/" . $fileName);
        $galleryItemId = $em->getRepository('CommonUtilityBundle:FgGmItems')->saveGalleryImage($imageDetails, $container);

        $galleryItemObj = $em->getRepository('CommonUtilityBundle:FgGmItems')->find($galleryItemId[0]);

        return $galleryItemObj;
    }

    /**
     * Method to upload file and save to file manager table
     * 
     * @param string $tempFileName     temp filename of uploaded file
     * @param int    $clubId           current club-id
     * @param object $container        container object
     * @param string $type             NEWSLETTER/SIMPLE MAIL
     * @param string $fileNameOriginal for simplemail only
     * 
     * @return object $filemanagerObj saved filemanager Object
     */
    private function uploadToFilemanager($tempFileName, $clubId, $container, $type = 'NEWSLETTER', $fileNameOriginal = '') {
        $em = $this->getEntityManager();
        $communicationUploadFolder = FgUtility::getUploadFilePath($clubId, 'communication');
        if (!is_dir($communicationUploadFolder)) {
            mkdir($communicationUploadFolder, 0700);
        }
        $rootPath = FgUtility::getRootPath($container);
        if ($type == 'NEWSLETTER') {
            $fileName = FgUtility::getFilename("$rootPath/$communicationUploadFolder", $tempFileName);
        } else {
            $fileName = $fileNameOriginal;
        }

        //encrpt filename
        $fileCheck = new FileChecking($container);
        $fileCheck->filename = $fileName;
        $shaFilename = $fileCheck->sshNameConvertion();

        $fs = new Filesystem();
        $uploadPath = FgUtility::getUploadDir() . "/temp/";
        $fs->copy($uploadPath . $tempFileName, "$rootPath/$communicationUploadFolder/$shaFilename");
        unlink($uploadPath . $tempFileName);
        //save to file manager
        $filemanagerId = $this->saveToFileManager($container, $fileName, $shaFilename, "$rootPath/$communicationUploadFolder", $clubId, $type);
        $filemanagerObj = $em->getRepository('CommonUtilityBundle:FgFileManager')->find($filemanagerId[0]);

        return $filemanagerObj;
    }

    /**
     * Method to save aticle attachments
     * 
     * @param array  $attachments Attachments
     * @param object $article     Article object
     * @param object $container   container object
     * @param int    $clubId      clubId
     */
    private function saveArticleAttachments($attachments, $article, $container, $clubId) {
        $em = $this->getEntityManager();
        $sortOrder = 0;
        foreach ($attachments as $key => $attachment) {
            $sortOrder++;
            if ($attachment['isDeleted']) {
                $articleMedia1 = $em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleMedia')->find($key);
                if ($articleMedia1) {
                    $em->remove($articleMedia1);
                    $em->flush();
                }
                continue;
            }
            //case: when broswe from file manager table
            if (!empty($attachment['filemanagerId'])) {
                $filemanagerObj = $em->getRepository('CommonUtilityBundle:FgFileManager')->find($attachment['filemanagerId']);
                if ($filemanagerObj) {
                    $articleMedia = new \Common\UtilityBundle\Entity\FgCnNewsletterArticleMedia();
                    $articleMedia->setMediaType('attachments');
                    $articleMedia->setDescription('');
                    $articleMedia->setArticle($article);
                    $articleMedia->setFileManager($filemanagerObj);
                    $articleMedia->setSortOrder($sortOrder);
                    $em->persist($articleMedia);
                    $em->flush();
                }
                continue;
            }

            //case: when uploadiong an image
            if (!empty($attachment['filename'])) {
                $filemanagerObj = $this->uploadToFilemanager($attachment['filename'], $clubId, $container);

                $articleMedia = new \Common\UtilityBundle\Entity\FgCnNewsletterArticleMedia();
                $articleMedia->setMediaType('attachments');
                $articleMedia->setArticle($article);
                $articleMedia->setFileManager($filemanagerObj);
                $articleMedia->setDescription('');
                $articleMedia->setSortOrder($sortOrder);
                $em->persist($articleMedia);
                $em->flush();
            }
        }
    }

    /**
     * Method to save to filemanager table
     * 
     * @param object $container                 container object
     * @param string $fileName                  filename
     * @param string $shaFileName               encrypted filename
     * @param string $communicationUploadFolder 'ROOTPATH/uploads/clubId/content'
     * @param int    $clubId                    current clubId
     * @param string $module                    SIMPLE EMAIL / NEWSLETTER
     * 
     * return array inserted filemanagerIds
     */
    private function saveToFileManager($container, $fileName, $shaFileName, $communicationUploadFolder, $clubId, $module) {
        if (file_exists("$communicationUploadFolder/$shaFileName")) {
            $filesize = filesize("$communicationUploadFolder/$shaFileName");
        } else {
            $filesize = 0;
        }
        $filemanagerDetails = array('clubId' => $clubId, 'contactId' => $container->get('contact')->get('id'), 'fileCount' => 1, 'module' => $module,
            'fileName' => array($fileName),
            'shaFileName' => array($shaFileName),
            'randFileName' => array(date('YmdHis') . $fileName),
            'fileSize' => array($filesize)
        );
        $em = $this->getEntityManager();
        $filemanagerIds = $em->getRepository('CommonUtilityBundle:FgFileManager')->saveFilemanagerFile($filemanagerDetails, $container);

        return $filemanagerIds;
    }

    /**
     * function to save the simple newsletter-step 3
     *
     * @param array $formArray the array of inputs
     * @param array $clubData the array of clubdetails
     * @param object $container the contgainer
     *
     * @return boolean
     */
    public function saveSimplemailContent($formArray, $clubData, $container) {
        $em = $this->getEntityManager();
        $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($clubData['newsletterId']);
        $newsletterObj->setEmailContent(html_entity_decode($formArray['content'], ENT_QUOTES, 'UTF-8'));
        if ($newsletterObj->getStep() < 3) {
            $newsletterObj->setStep(5);
        }
        $em->persist($newsletterObj);
        $em->flush();

        $content = $em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->findOneByNewsletter($clubData['newsletterId']);
        if (!$content) {
            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterContent();
            $content->setNewsletter($newsletterObj);
        }
        $content->setSortOrder(1);
        $content->setIsActive(1);
        $content->setContentType('OTHER');
        $content->setIntroClosingWords(html_entity_decode($formArray['signature'], ENT_QUOTES, 'UTF-8'));
        $em->persist($content);
        $em->flush();
        $communicationUploadFolder = FgUtility::getUploadFilePath($clubData['clubId'], 'communication');



        if (!empty($formArray['images'])) {
            foreach ($formArray['images'] as $key => $value) {
                if ($value['filename'] != "") {
                    $fileName = $value['filename'];
                    $fileNameOriginal = FgUtility::getFilename("/$communicationUploadFolder/", $fileName);
                    if ($value['filemanagerId'] != '') { //case: when select attachments from file manager
                        $filemanagerObj = $em->getRepository('CommonUtilityBundle:FgFileManager')->find($value['filemanagerId']);
                        ;
                    } else { //case: when uploading new files
                        $filemanagerObj = $this->uploadToFilemanager($value['tmpFileName'], $clubData['clubId'], $container, 'SIMPLE EMAIL', $fileNameOriginal);
                    }

                    $articleMedia = new \Common\UtilityBundle\Entity\FgCnNewsletterArticleDocuments();
                    //$articleMedia->setFilename($fileNameOriginal);
                    $articleMedia->setTitle($fileNameOriginal);
                    $articleMedia->setNewsletter($newsletterObj);
                    $articleMedia->setSortOrder(1);
                    $articleMedia->setDocType('NEW');
                    $articleMedia->setCreatedAt(new \DateTime("now"));
                    $articleMedia->setFileManager($filemanagerObj);
                    $em->persist($articleMedia);
                } else {
                    if ($value['isDeleted']) {
                        $docId = $key;
                        $deleteObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletterArticleDocuments')->find($docId);
                        $em->remove($deleteObj);
                    }
                }
            }
            $em->flush();
        }


        return true;
    }

    /**
     * Function to get simple mail content details
     * @param object $container    Container object
     * @param int    $clubId       ClubId
     * @param int    $newsletterId NewsletterId
     * @param int    $contactId    CurrentContactId
     * @param string $clubTitle    ClubTitle
     * @param string $mode         preview/cron
     *
     * @return array $result NewsletterContentArray
     */
    public function getSimplemailContentDetails($container, $clubId, $newsletterId, $contactId = 0, $clubTitle, $mode = 'preview', $clubDefaultLang = 'de') {
        $em = $this->getEntityManager();
        $previewData = array();
        $club = $container->get('club');
        if ($newsletterId) {
            $newsletterData = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSimpleMailDetails($newsletterId, $clubId);

            $newsletterData['contactId'] = $contactId;
            $attachments = $mailAttachments = array();
            $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
            $getBaseUrls = $this->getBaseUrls($container, $checkClubHasDomain, $club, $mode, $clubId);
            $rootPath = FgUtility::getRootPath($container);
            $communicationUploadFolder = FgUtility::getUploadFilePath($clubId, 'communication');
            foreach ($newsletterData as $mailDoc) {
                $path = "/$communicationUploadFolder/";
                $filepath = $rootPath . "/$communicationUploadFolder/";
                if ($mailDoc['doc_type'] == 'APPEND') {
                    //TO BE DONE
                } elseif (!empty($mailDoc['filename'])) {
                    $title = $mailDoc['title'] ? $mailDoc['title'] : $mailDoc['filename'];
                    $fileName = $mailDoc['filename'];
                    $fileSrc = $filepath . $fileName;
                    $downloadPath = FgUtility::FilemanagerDownloadUrl($container, $mailDoc['virtualFilename'], $clubId, $mode);

                    if (is_file($filepath . $fileName) && file_exists($filepath . $fileName)) {
                        $attachments[] = array('filePath' => $downloadPath, 'fileTitle' => $title);
                        $mailAttachments[] = array('filePath' => $fileSrc, 'fileTitle' => $title);
                    }
                }
            }

            $previewData['attachments'] = $attachments;
            $previewData['mailAttachments'] = $mailAttachments;
            if ($newsletterData[0]) {
                $newsletterData[0]['email_content'] = FgUtility::correctCkEditorUrl($newsletterData[0]['email_content'], $container, $clubId, $mode);
                $previewData['content'] = $newsletterData[0]['email_content'];
                $salutation = '';
                if ($newsletterData[0]['salutation_type'] == 'INDIVIDUAL') {
                    $salutation = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText($contactId, $clubId, $clubDefaultLang);
                } elseif ($newsletterData[0]['salutation_type'] == 'SAME') {
                    $salutation = $newsletterData[0]['salutation'];
                }
                if (($mode == "testmail")) {
                    $salutation = '@@#salutation#@@';
                } elseif (($mode == 'designpreview') && ($newsletterData[0]['salutation_type'] == 'INDIVIDUAL')) {
                    $salutation = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->getSalutationText($contactId, $clubId, $clubDefaultLang);
                }
                $previewData['salutation'] = $salutation;
                $previewData['senderName'] = $newsletterData[0]['senderName'];
                $previewData['senderEmail'] = $newsletterData[0]['senderEmail'];
                $previewData['subject'] = $newsletterData[0]['subject'];
                $previewData['receiver'] = $em->getRepository('CommonUtilityBundle:FgCmContact')->getContactPrimaryEmail($contactId);
            }

            $previewData['mode'] = $mode;
            $previewData['title'] = $clubTitle;
            $previewData['baseUrl'] = $getBaseUrls['baseUrl'];
            $previewData['baseUrlWithUrlIdentifier'] = $getBaseUrls['baseUrlWithUrlIdentifier'];
            $previewData['mainBaseUrl'] = FgUtility::getBaseUrl($container, $clubId, $mode);
            $baseUrlArr = FgUtility::generateUrlForCkeditor($container, $clubId, $mode);
            $baseUrl = $baseUrlArr['baseUrl'];
            $clubObj = $container->get('club');
            $clubLogo = $clubObj->get('logo');
            if ($clubLogo == '' || !file_exists($rootPath . '/' . FgUtility::getUploadFilePath($clubId, 'clublogo', false, $clubLogo))) {
                $clubLogoUrl = '';
            } else {
                $clubLogoUrl = $baseUrl . '/' . FgUtility::getUploadFilePath($clubId, 'clublogo', false, $clubLogo);
            }
            $previewData['logoURL'] = $clubLogoUrl;
            $newsletterData[0]['signature'] = FgUtility::correctCkEditorUrl($newsletterData[0]['signature'], $container, $clubId, $mode);
            $previewData['signature'] = $newsletterData[0]['signature'];
        }


        return $previewData;
    }

    /**
     * Function to delete all sponsor contents in a newsletter
     *
     * @param int $newsletterId NewsletterId
     */
    public function deleteNewsletterSponsorContents($newsletterId) {
        $qb = $this->createQueryBuilder('cn');
        $q = $qb->delete('CommonUtilityBundle:FgCnNewsletterContent', 'cn')
                ->where($qb->expr()->eq('cn.newsletter', ':key'))
                ->andWhere($qb->expr()->orX($qb->expr()->eq('cn.contentType', ':spAbove'), $qb->expr()->eq('cn.contentType', ':sp'), $qb->expr()->eq('cn.contentType', ':spBottom')))
                ->setParameters(array('key' => $newsletterId, 'spAbove' => 'SPONSOR ABOVE', 'sp' => 'SPONSOR', 'spBottom' => 'SPONSOR BOTTOM'))
                ->getQuery();
        $q->execute();
    }

    /**
     * Function to get newsletter content ids for updating sort order
     *
     * @param int $newsletterId NewsletterId
     *
     * @return array $result Array of content ids
     */
    public function getOrderedNewsletterContentIds($newsletterId) {
        $resultQuery = $this->createQueryBuilder('CN')
                ->select("CN.id, (CASE WHEN CN.contentType = 'INTRO' THEN 1 WHEN CN.contentType = 'SPONSOR ABOVE' THEN 2 WHEN CN.contentType = 'SPONSOR' THEN 4 WHEN CN.contentType = 'CLOSING' THEN 5 WHEN CN.contentType = 'SPONSOR BOTTOM' THEN 6 ELSE 3 END) AS contentSortOrder")
                ->where('CN.newsletter = :newsletterId')
                ->orderBy("contentSortOrder ASC, CN.sortOrder", "ASC")
                ->setParameters(array('newsletterId' => $newsletterId));
        $result = $resultQuery->getQuery()->getArrayResult();

        return $result;
    }
}
