<?php

/**
 * Manage CMS Website container functionalities
 *
 *
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Util\FgCmsPortraitFrontend;

/**
 * Manage CMS theme container functionalities
 *
 *
 */
class FgWebsite
{

    /**
     * @var object Container variable
     */
    public $container;

    /**
     * @var object entity manager variable
     */
    private $em;

    /**
     * @var contact language if logged in else club default(superadmin/not logged)
     */
    private $contactLang;

    /**
     * club service
     * @var object 
     */
    private $clubService;

    /**
     * caching key
     * @var string 
     */
    private $cacheKey;

    /**
     * cache life time
     * @var type 
     */
    private $cacheLifeTime;

    /**
     * if caching enabled
     * @var int 
     */
    private $cachingEnabled;

    /**
     * contact id of logged in user
     * @var int 
     */
    private $contactId;

    /**
     * Constructor of FgCmsThemeContainerDetails class.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->clubService = $this->container->get('club');

        $this->contactId = $this->container->get('contact')->get('id');
        $superadmin = $this->container->get('contact')->get('isSuperAdmin');

        if (!$superadmin && $this->contactId) {
            // logged in contact and is not a superadmin
            $this->contactLang = $this->container->get('contact')->get('corrLang');
        } else {
            //is superadmin or is not logged in
            $this->contactLang = $this->clubService->get('default_lang');
        }


        $this->cacheKey = $this->clubService->get('clubCacheKey');
        $this->cacheLifeTime = $this->clubService->get('cacheLifeTime');
        $this->cachingEnabled = $this->container->getParameter('caching_enabled');
    }

    /**
     * Function to get all the details of a page
     *
     * @param Int $pageId PageId
     *
     * @return Array page details array
     */
    public function getPageDetails($pageId, $isfooter = true, $iscontent = true)
    {
        $jsonData = $jsonData['sidebar'] = $sidebarElements = $pageElements = $footerElements = array();
        $jsonData['sideCount'] = 0;
        $jsonData['pageElements'] = $jsonData['footerElements'] = $jsonData['sidebarElements'] = $jsonData['portraitElementSettings'] = '{}';
        //Get the footer details
        if ($isfooter == true) {
            $jsonData['footer'] = array();
            $footerObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->findOneBy(array('club' => $this->clubService->get('id'), 'type' => 'footer'));
            if ($footerObject) {
                $jsonData['footer'] = json_decode($footerObject->getPageContentJson(), true);
                $jsonData['footerId'] = $footerObject->getId();
                $jsonData['footerElements'] = ($footerObject->getPageElement() != "null") ? $footerObject->getPageElement() : '{}';
                $footerElements = $jsonData['footerElements'] == "null" ? array() : json_decode($jsonData['footerElements'], true);
            }
        }

        if ($pageId && $iscontent == true) {
            $pageObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
            $jsonData['page'] = json_decode($pageObject->getPageContentJson(), true);
            $jsonData['pageId'] = $pageObject->getId();
            $jsonData['hidePageTitle'] = ($pageObject->getHideTitle() == 1) ? 1 : 0;

            //Get the sidebar details
            if ($jsonData['page']['sidebar']['type'] != '') {
                $sidebarObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->findOneBy(array('club' => $this->clubService->get('id'), 'type' => 'sidebar', 'sidebarType' => $jsonData['page']['sidebar']['type']));
                $jsonData['sidebar'] = json_decode($sidebarObject->getPageContentJson(), true);
                $jsonData['sidebarId'] = $sidebarObject->getId();
                $jsonData['sidebarElements'] = ($sidebarObject->getPageElement() != "null" ) ? $sidebarObject->getPageElement() : '{}';
                $sidebarElements = $jsonData['sidebarElements'] == "null" ? array() : json_decode($jsonData['sidebarElements'], true);
            }

            //OG Graph Details
            $jsonData['og'] = $this->getOGDetails($pageObject);
            $jsonData['pageElements'] = ($pageObject->getPageElement() != "null") ? $pageObject->getPageElement() : '{}';
            $pageElements = $jsonData['pageElements'] == "null" ? array() : json_decode($jsonData['pageElements'], true);
        }

        if (count($jsonData['page']) == 0) {
            $jsonData['page']['page'] = array();
            $jsonData['page']['sidebar']['size'] = '';
        }

        $jsonData['googleCaptchaSitekey'] = $this->container->getParameter('googleCaptchaSitekey');
        $jsonData['sideCount'] = count($sidebarElements);
        $jsonData['pageElemCount'] = count($pageElements);
        $jsonData['pageElementsArray'] = $sidebarElements + $footerElements + $pageElements;
        $jsonData['ajaxUrl'] = $this->getAjaxUrls($jsonData['pageElementsArray']);
        $jsonData['navPath'] = $this->container->get('router')->generate('website_public_page_menus', array('menu' => '**dummy**'));
        
        if (in_array('portrait-element', $jsonData['pageElementsArray'])) {
            $jsonData['portraitElementSettings'] = $this->getPortraitElementTemplate($jsonData['pageElementsArray']);
        }

        return $jsonData;
    }

    private function getAjaxUrls($pageElements)
    {
        $urls = array();
        $elements = array_values(array_unique($pageElements, SORT_REGULAR));
        foreach ($elements as $value) {
            switch ($value) {
                case 'image':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_get_image_details');
                    break;
                case 'articles':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_article_element_data', array('elementId' => '#dummyElement#', 'pageId' => '#dummy#'));
                    break;
                case 'calendar':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_get_calendar_events', array('elementId' => '#dummyElement#', 'pageId' => '#dummy#'));
                    break;
                case 'contacts-table':
                    $urls[$value] = $this->container->get('router')->generate('contact_table_initial_data', array('elementId' => '#dummyElement#'));
                    break;
                case 'form':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_get_form_element', array('elementId' => '#dummyElement#'));
                    break;
                case 'newsletter-archive':
                    $urls[$value] = $this->container->get('router')->generate('website_cms_newsletter_archive_data', array('elementId' => '#dummyElement#', 'pageId' => '#dummy#'));
                    break;
                case 'newsletter-subscription':
                    $urls[$value] = $this->container->get('router')->generate('website_subscriptionform_view', array('elementId' => '#dummyElement#'));
                    break;
                case 'portrait-element':
                    $urls[$value] = $this->container->get('router')->generate('portrait_element_contact_details', array('elementId' => '#dummyElement#'));
                    break;
                case 'sponsor-ads':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_get_sponsor_data', array('elementId' => '#dummyElement#', 'pageId' => '#dummy#'));
                    break;
                case 'text':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_text_element_data');
                    break;
                case 'contact-application-form':
                    $urls[$value] = $this->container->get('router')->generate('website_public_page_get_form_element', array('elementId' => '#dummyElement#'));
                    break;
            }
        }

        return $urls;
    }

    /**
     * Function is used to get all on load elements data
     *
     * @param array $combinedE all element ids and type of a page (page+sidebar+footer)
     *
     * @return array
     */
    public function getOnPageLoadElementData($pageId, $navId, $combinedE)
    {
        $onLoadElementArray = array();
        $data = array();
        $loginHtml = '';
        foreach ($combinedE as $elementId => $type) {
            switch ($type) {
                case 'header':
                case 'iframe':
                case 'twitter':
                case 'map':
                    $onLoadElementArray[] = $elementId;
                    break;
                case 'supplementary-menu':
                    $data[$elementId]['htmlContent'] = $this->supplementaryElement($navId, $elementId);
                    $data[$elementId]['elementType'] = 'supplementary-menu';
                    $data[$elementId]['id'] = $elementId;
                    break;
                case 'login':
                    if ($loginHtml == '') {
                        $loginHtml = $this->loginElement();
                    }
                    $data[$elementId]['htmlContent'] = $loginHtml;
                    $data[$elementId]['elementType'] = 'login';
                    $data[$elementId]['id'] = $elementId;
            }
        }

        $onLoadData = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getOnLoadPageDetails($pageId, $onLoadElementArray, $this->contactLang, $this->clubService->get('id'), $this->cacheKey, $this->cacheLifeTime, $this->cachingEnabled);

        return $onLoadData + $data;
    }

    /**
     * supplementary element
     * 
     * @param int $navId        current navigation id
     * @param int $elementId    element id
     * 
     * @return template
     */
    private function supplementaryElement($navId, $elementId)
    {
        $websiteNavigationDetails = $this->clubService->get('navigationHeirarchy');
        $websiteNavDetails = array();
        if ($this->contactId) {
            $websiteNavDetails = $websiteNavigationDetails;
            unset($websiteNavDetails['publicPages']);
            unset($websiteNavDetails['publicPageUrl']);
        } else {
            $websiteNavDetails = $websiteNavigationDetails['publicPages'];
        }

        $navDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsNavigation')->getSupplementaryElementDetails($navId, $websiteNavDetails, $this->contactLang, $this->container);
        $navDetails['elementId'] = $elementId;

        return $this->container->get('templating')->render('WebsiteCMSBundle:Website:templateSupplementaryMenuElement.html.twig', $navDetails);
    }

    /**
     * Login element
     * 
     * @return template
     */
    private function loginElement()
    {
        if ($this->contactId && $this->container->get('security.token_storage')->getToken()) {
            $login['loggedIn'] = true;
            $login['contactName'] = $this->container->get('contact')->get('nameNoSort');
        } else {
            $login['loggedIn'] = false;
            if ($this->container->has('security.csrf.token_manager')) {
                $login['csrf_token'] = $this->container->get('security.csrf.token_manager')->getToken('authenticate')->getValue();
            } else {
                // BC for SF < 2.4
                $login['csrf_token'] = $this->container->has('form.csrf_provider') ? $this->container->get('form.csrf_provider')->generateCsrfToken('authenticate') : null;
            }
        }
        return $this->container->get('templating')->render('WebsiteCMSBundle:Website:templateLoginElement.html.twig', $login);
    }

    /**
     * Method to return parametes to build website layout (with header and footer without content area)
     *
     * @param array $retArray pagetitle and other custom parameters to build template content
     *
     * @return array of parameters (with custom parameters and parametrs to build header and footer)
     */
    public function getParametesForWebsiteLayout($retArray = array())
    {
        $clubId = $this->clubService->get('id');
        $getPageContentDetails = $this->getPageDetails(0, true, false);
        //set page title
        $getPageContentDetails['page']['page']['title'] = $retArray['pagetitle'];
        $backgroundPath = FgUtility::getUploadFilePath($clubId, 'cms_background_image', false, false);
        $cssPath = FgUtility::getUploadFilePath($clubId, 'cms_themecss', false, false);
        $colorSchemeClubId = $this->clubService->get('publicConfig')['colorSchemeClubId'];
        $colorCssPath = FgUtility::getUploadFilePath($colorSchemeClubId, 'cms_themecss', false, false);
        $parameters = array('menu' => '', 'cssPath' => $cssPath, 'colorCssPath' => $colorCssPath, 'pagecontentData' => $getPageContentDetails, 'backgroundPath' => $backgroundPath);
        $parameters['clubDefaultLang'] = $this->clubService->get('club_default_lang');

        return array_merge($parameters, $retArray);
    }

    /**
     * Function to get title of page title
     *
     * @param Int $pageId PageId
     *
     * @return Array page title
     */
    public function getPageTitle($pageId)
    {
        return $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getPageTitleFrontend($pageId, $this->contactId,$this->contactLang, $this->cacheKey, $this->cacheLifeTime, $this->cachingEnabled);
    }

    /**
     * Function to get title of page title
     *
     * @param string $pageTitle PageTitle
     *
     * @return Array meta tag and meta description
     */
    public function getMetaDetails($pageTitle)
    {
        $metaDescArray = $this->clubService->get('metaDescription');
        $lang = $this->clubService->get('default_lang');
        $metaDesc = '';
        if ($metaDescArray[$lang] != '') {
            $metaDesc = $metaDescArray[$lang];
        } else {
            $metaDesc = $metaDescArray['default'];
        }

        return array('metaTitle' => $pageTitle, 'metaDescription' => $metaDesc);
    }

    /**
     * Function to get og tag detail
     *
     * @param object $pageObject PageObject
     *
     * @return Array with ogtag Details
     */
    private function getOGDetails($pageObject)
    {
        $ogElementArray = array();
        $ogElementArray['elementIds'] = $pageObject->getPageElement();
        $ogElementArray['ogEnabled'] = 0;
        $ogElements = json_decode($ogElementArray['elementIds'], true);
        $ogrequired = array('image', 'text'); //Check for OGTag elements
        $values = array_values($ogElements);
        if (count(array_intersect(array_values($ogElements), $ogrequired)) > 0) {
            $ogElementArray['ogEnabled'] = 1;
        }
        $ogElementArray['imagePath'] = FgUtility::getBaseUrl($this->container) . '/uploads/' . $this->clubService->get('id') . '/gallery/original/';
        $ogElementArray['opengraph'] = json_decode($pageObject->getOpengraphDetails(), true);

        return $ogElementArray;
    }

    /**
     * Function to update Ajax Url
     *
     * @return  void
     */
    public function updateJsonPath()
    {
        $jsonDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getAllJsonPageData();
        foreach ($jsonDetails as $json) {
            $decodedJson = json_decode($json['pageContentJson'], true);
            array_walk_recursive($decodedJson, array($this, 'updateAjaxUrl'), $json['clubId']);
            $encodeJson = json_encode($decodedJson);
            $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->saveContentJson($this->container, $json['type'], $json['id'], $encodeJson);
        }

        return;
    }

    /**
     * This function is used to get portarit element container  template array.
     * @param array $pageElementArray page element list
     * 
     * @return array portrait element template
     */
    private function getPortraitElementTemplate($pageElementArray)
    {
        $portraitElemTemplate = '';
        foreach ($pageElementArray as $elementId => $value) {
            if ($value == 'portrait-element') {
                $portraitDetailsObj = new FgCmsPortraitFrontend($this->container);
                $portraitElemDetails = $portraitDetailsObj->getPortraitElementDetails($elementId);
                if ($portraitElemDetails['stage'] == 'stage4') {
                    $portraitElemTemplate[$elementId]['template'] = $this->container->get('templating')->render('WebsiteCMSBundle:ContactPortraitsElement:templatePortraitElement.html.twig', $portraitElemDetails);
                    $portraitElemTemplate[$elementId]['data'] = $portraitElemDetails;
                }
            }
        }

        return $portraitElemTemplate;
    }
}
