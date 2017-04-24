<?php

namespace Website\CMSBundle\Util;

/**
 * Manage CMS page content functionalities
 *
 * @package         package
 * @subpackage      subpackage
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgPageContent
{

    private $em;
    private $clubId;

    /**
     * $club.
     *
     * @var Service {clubservice}
     */
    private $club;
    private $container;
    private $pageElements;
    private $pageElementsType;
    private $pageType;

    /**
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container   container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->clubId = $this->club->get('id');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     *
     * @param type $pageId
     * @return type
     */
    public function getContentElementData($pageId, $iswebsite = 0)
    {
        $clubDefaultLang = $this->club->get('default_lang');
        $pageContentDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageDetails($pageId, $clubDefaultLang, $this->clubId);

        return $this->formatContentElementStructure($pageId, $pageContentDetails, $iswebsite, $clubDefaultLang);
    }

    /**
     * Function to format elements
     *
     * @param int   $pageId             Page id
     * @param array $pageContentDetails Content details
     *
     * @return array
     */
    public function formatContentElementStructure($pageId, $pageContentDetails, $isWebsite = 0, $clubDefaultLang = '')
    {
        $previousContainerId = 0;
        $previousColumnId = 0;
        $previousBoxId = 0;
        $previousElementId = 0;
        $newPagecontentArray = array();
        $session = $this->container->get('session');
        $clipboardContentDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($this->clubId, $clubDefaultLang);
        $clipBoardElementId = array_column($clipboardContentDetails, 'elementId');
        $allContainerIdArray = array_column($pageContentDetails, 'containerId');
        $allColumnIdArray = array_column($pageContentDetails, 'columnId');
        $allBoxIdArray = array_column($pageContentDetails, 'boxId');
        $allElementIdArray = array_column($pageContentDetails, 'elementId');
        $pageContentIdArray = array('container' => $allContainerIdArray, 'column' => $allColumnIdArray, 'box' => $allBoxIdArray, 'element' => $allElementIdArray);
        $pageContentIdArray['element'] = array_merge($pageContentIdArray['element'], $clipBoardElementId);

        $session->set("cmsPageContentIdArray", $pageContentIdArray);
        if (count($pageContentDetails) > 0) {
            $newPagecontentArray = array("page" => array('id' => $pageId, 'title' => 'test'));
            //sidebar details
            $newPagecontentArray["sidebar"]['side'] = $pageContentDetails[0]['sidebarArea'];
            $newPagecontentArray["sidebar"]['type'] = $pageContentDetails[0]['sidebarType'];
            $newPagecontentArray["sidebar"]['size'] = $pageContentDetails[0]['sidebarType'];
            $newPagecontentArray['page']['title'] = $pageContentDetails[0]['pageTitle'];
            $newPagecontentArray['page']['pageType'] = $pageContentDetails[0]['pageType'];
            $this->pageType = $pageContentDetails[0]['pageType'];
            foreach ($pageContentDetails as $detailsValue) {
                //assign container details
                if ($previousContainerId != $detailsValue['containerId']) {
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['containerId'] = $detailsValue['containerId'];
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['sortOrder'] = $detailsValue['containerOrder'];
                }
                //assign column details
                if (($previousColumnId != $detailsValue['columnId']) && ($detailsValue['columnId'] != '')) {
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['columnId'] = $detailsValue['columnId'];
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['sortOrder'] = "{$detailsValue['columnOrder']}";
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['widthValue'] = "{$detailsValue['widthValue']}";
                }
                //assign box details
                if (($previousBoxId != $detailsValue['boxId']) && $detailsValue['boxId'] != '') {
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['boxId'] = $detailsValue['boxId'];
                    $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['sortOrder'] = $detailsValue['boxOrder'];
                }
                //assign element  details
                if (($previousElementId != $detailsValue['elementId']) && ($detailsValue['elementId'] != '')) {
                    //generate element array according to type
                    $this->generateElementArray($detailsValue, $pageId, $newPagecontentArray, $isWebsite);
                }
                $previousContainerId = $detailsValue['containerId'];
                $previousColumnId = $detailsValue['columnId'];
                $previousBoxId = $detailsValue['boxId'];
                $previousElementId = $detailsValue['elementId'];
                $this->pageElements [] = $detailsValue['elementId'];
                $this->pageElementsType[] = $detailsValue['elementType'];
            }
        }

        return $newPagecontentArray;
    }

    /**
     * Function used for preparing the element array
     *
     * @param array   $detailsValue        query results
     * @param integer $pageId              page id
     * @param object  $newPagecontentArray object of new ly created array
     * @param boolean $iswebsite           Is it is a request from website or not
     *
     */
    private function generateElementArray($detailsValue, $pageId, &$newPagecontentArray, $iswebsite = 0)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementId'] = $detailsValue['elementId'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['widthValue'] = $detailsValue['widthValue'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['sortOrder'] = $detailsValue['elementOrder'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementType'] = $detailsValue['elementType'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['logo'] = $detailsValue['logo'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['label'] = $detailsValue['label'];

        switch ($detailsValue['elementType']) {
            case "header":
                $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
                $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['headerElementSize'] = $detailsValue['headerElementSize'];
                $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = false;
                break;
            case "text":
                $this->getTextElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "image":
                $this->getImageElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "articles":
                $this->getArticleElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "calendar":
                $this->getCalendarElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "twitter":
                $this->getTwitterElementDetails($newPagecontentArray, $detailsValue);
                break;
            case "newsletter-archive":
                $this->getNlArchieveElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "sponsor-ads":
                $this->getSponsorAdsElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "login":
                $this->getLoginElementDetails($newPagecontentArray, $detailsValue, $pageId, $iswebsite);
                break;
            case "map":
                $this->getMapElementDetails($newPagecontentArray, $detailsValue);
                break;
            case "form":
                $this->getFormElementDetails($newPagecontentArray, $detailsValue, $iswebsite);
                break;
            case "contact-application-form":
                $this->getContactAppElementDetails($newPagecontentArray, $detailsValue, $iswebsite);
                break;
            case "supplementary-menu":
                $this->getSupplementaryElementDetails($newPagecontentArray, $detailsValue);
                break;
            case "contacts-table":
                $this->getContactTableElementDetails($newPagecontentArray, $detailsValue, $iswebsite);
                break;
            case "portrait-element":
                $this->getPortraitElementDetails($newPagecontentArray, $detailsValue, $iswebsite);
                break;
            case "newsletter-subscription":
                $this->getNlSubscriptionElementDetails($newPagecontentArray, $detailsValue, $iswebsite);
                break;
            default:
                $this->getIframeElementDetails($newPagecontentArray, $detailsValue);
                break;
        }
    }

    /**
     * This function is used to generate url path without club url identifier. The path is used to save in the database
     *
     * @param string  $pathName   Name of the path
     * @param boolean $domainFlag To check whether domain or not
     * @param array   $arguments  Arguments of the path
     *
     * @return string
     */
    private function domainUrlGenerator($pathName, $domainFlag, $arguments = array())
    {
        if ($domainFlag) {
            $routeName = $this->container->get('router')->generate($pathName, $arguments);
            $from = '/' . preg_quote('/' . $this->club->get('clubUrlIdentifier') . '/', '/') . '/';

            return preg_replace($from, '/', $routeName, 1);
        }
    }

    /**
     * function to get unassign page popup content data
     *
     * @param array $checkedIdArr
     * @param int $pageAssignment
     * @param array $popup
     *
     * @return array page popup content data
     */
    public function getPopupContentData($checkedIdArr, $pageAssignment, $popup, $trans)
    {
        $popup['footer'] = '';
        $totalCount = count($checkedIdArr);
        if ($totalCount > 1) {
            $popup['title'] = $trans->trans('MULTIPLE_UNASSIGN_PAGE_POPUP_TITLE');
            if ($pageAssignment != 0) {
                $popup['text'] = ($totalCount == $pageAssignment) ? $trans->trans('MULTIPLE_UNASSIGN_PAGE_POPUP_TEXT', array('%count%' => $popup['totalCount'])) : $trans->trans('MULTIPLE_UNASSIGN_PAGE_POPUP_TEXT_MIXED', array('%sucCount%' => $pageAssignment, '%count%' => $totalCount));
            } else {
                $popup['text'] = $trans->trans('MULTIPLE_NO_UNASSIGN_PAGE_POPUP_TEXT', array('%count%' => $popup['totalCount']));
                $popup['footer'] = 'okCancel';
            }
        } elseif ($totalCount == 1) {
            $popup['title'] = $trans->trans('SINGLE_UNASSIGN_PAGE_POPUP_TITLE');
            if ($pageAssignment != 0) {
                $popup['text'] = $trans->trans('SINGLE_UNASSIGN_PAGE_POPUP_TEXT');
            } else {
                $popup['text'] = $trans->trans('SINGLE_NO_UNASSIGN_PAGE_POPUP_TEXT');
                $popup['footer'] = 'okCancel';
            }
        }

        return $popup;
    }

    /**
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @return void
     */
    private function getTwitterElementDetails(&$newPagecontentArray, $detailsValue)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['accountName'] = $detailsValue['accountName'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['twitterContentHeight'] = $detailsValue['twitterContentHeight'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = false;
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['accountNameLang'] = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementI18n')->getTwitterTitleLang($detailsValue['elementId']);

        return;
    }

    /**
     * Get map element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @return void
     */
    private function getMapElementDetails(&$newPagecontentArray, $detailsValue)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['mapElementLongitude'] = $detailsValue['mapElementLongitude'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['mapElementLatitude'] = $detailsValue['mapElementLatitude'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['mapElementShowMarker'] = $detailsValue['mapElementShowMarker'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['mapElementHeight'] = $detailsValue['mapElementHeight'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['mapElementDisplayStyle'] = $detailsValue['mapElementDisplayStyle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['mapElementZoomValue'] = $detailsValue['mapElementZoomValue'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = false;

        return;
    }

    /**
     * Get Text element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getTextElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_preview_text_element', array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_text_element_data', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        }
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Image element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getImageElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['imageElementDisplayType'] = $detailsValue['imageElementDisplayType'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['imageElementSliderTime'] = $detailsValue['imageElementSliderTime'];
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_image_get_content_data', array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_get_image_details', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        }
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Article element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getArticleElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_article_get_content_data', array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_article_element_data', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        }
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Calendar element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getCalendarElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_calendar_get_content_data', array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_get_calendar_events', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        }
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Newslatter archieve element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getNlArchieveElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_newsletter_archive_data', array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_cms_newsletter_archive_data', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        }
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Sponsor Ads element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getSponsorAdsElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_sponsor_ad_get_content_data', array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_get_sponsor_data', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
        }
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Login element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getLoginElementDetails(&$newPagecontentArray, $detailsValue, $pageId, $iswebsite)
    {
        if ($iswebsite == 1) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_user_login', true, array('elementId' => $detailsValue['elementId'], 'pageId' => $pageId));
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
            $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = false;
        }
        return;
    }

    /**
     * Get Form element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getFormElementDetails(&$newPagecontentArray, $detailsValue, $iswebsite)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_page_get_form_element', array('elementId' => $detailsValue['elementId']));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_get_form_element', true, array('elementId' => $detailsValue['elementId']));
        }
        return;
    }

    /**
     * Get Contact Application Form element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $pageId              page id
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getContactAppElementDetails(&$newPagecontentArray, $detailsValue, $iswebsite)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['formId'] = $detailsValue['formId'];
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_page_get_form_element', array('elementId' => $detailsValue['elementId']));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_public_page_get_form_element', true, array('elementId' => $detailsValue['elementId']));
        }
        return;
    }

    /**
     * Get Supplementary element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @return void
     */
    private function getSupplementaryElementDetails(&$newPagecontentArray, $detailsValue)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['dataURL'] = $this->domainUrlGenerator('website_public_page_get_supplementary_element_data', true, array('currentNavigationId' => '**navid**', 'elementId' => '**dummy**'));
        $newPagecontentArray["page"]['ajaxElementsCount'] = $newPagecontentArray["page"]['ajaxElementsCount'] + 1;

        return;
    }

    /**
     * Get Contact Table element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getContactTableElementDetails(&$newPagecontentArray, $detailsValue, $iswebsite)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('contact_table_initial_data', array('elementId' => $detailsValue['elementId']));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('contact_table_initial_data', true, array('elementId' => $detailsValue['elementId']));
        }
        return;
    }

    /**
     * Get Portrait element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getPortraitElementDetails(&$newPagecontentArray, $detailsValue, $iswebsite)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('portrait_element_contact_details', array('elementId' => $detailsValue['elementId']));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('portrait_element_contact_details', true, array('elementId' => $detailsValue['elementId']));
        }

        return;
    }

    /**
     * Get Newsletter subscription element details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     * @param int       $iswebsite           is website
     * @return void
     */
    private function getNlSubscriptionElementDetails(&$newPagecontentArray, $detailsValue, $iswebsite)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['elementTitle'] = $detailsValue['elementTitle'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = true;
        if ($iswebsite == 0) {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->container->get('router')->generate('website_cms_subscriptionform_view', array('elementId' => $detailsValue['elementId']));
        } else {
            $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['ajaxURL'] = $this->domainUrlGenerator('website_subscriptionform_view', true, array('elementId' => $detailsValue['elementId']));
        }
        return;
    }

    /**
     * Get get Iframe Element Details
     *
     * @param pointer   $newPagecontentArray page content array
     * @param array     $detailsValue        page details
     *
     * @return void
     */
    private function getIframeElementDetails(&$newPagecontentArray, $detailsValue)
    {
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['iframeCode'] = $detailsValue['iframeElementCode'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['iframeUrl'] = $detailsValue['iframeElementUrl'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['iframeHeight'] = $detailsValue['iframeElementHeight'];
        $newPagecontentArray["page"]['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['box'][$detailsValue['boxId']]['Element'][$detailsValue['elementId']]['isAjax'] = false;

        return;
    }

    /**
     * function to get save page json content data
     *
     * @param int pageId Page id
     */
    public function saveJsonContent($pageId)
    {
        $iswebiste = 1;
        $pageContentDetails = $this->getContentElementData($pageId, $iswebiste);
        $elements = array_values(array_filter($this->pageElements));
        $elementType = array_values(array_filter($this->pageElementsType));
        $elementArray = array_combine($elements, $elementType);
        if ($this->pageType != 'sidebar' || $this->pageType != 'footer') {
            $metaImageDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentMedia')->getImageOGDetails($elements);
            $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->saveMetaDetails($pageId, json_encode($elementArray), json_encode($metaImageDetails));
        } else {
            $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->saveMetaDetails($pageId, json_encode($elementArray), '');
        }
        $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->saveContentJson($this->container,$pageId, json_encode($pageContentDetails),$this->pageType);
    }
}
