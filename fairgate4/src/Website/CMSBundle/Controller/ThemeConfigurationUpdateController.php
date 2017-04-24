<?php

/**
 * ThemeConfigurationUpdateController
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;
use Website\CMSBundle\Util\FgCmsTheme;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * ThemeConfigurationUpdateController Controller to update theme configuration
 *
 * ThemeConfigurationUpdateController This controller will deal with theme configuration related functionalities
 *
 * @package         Website
 * @subpackage      CMSBundle
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class ThemeConfigurationUpdateController extends Controller {

    /**
     * This function is used to update the background image details
     * 
     * @param Request $request request object
     * @param Int     $configId configuration id
     * 
     * @return object View Template Render Object of background image  action
     */
    public function backgroundListAction(Request $request, $configId) {
        $em = $this->getDoctrine()->getManager();
        $configId = $request->get('configId');
        $viewParams = array();
        //club service
        $clubService = $this->container->get('club');
        $viewParams['clubId'] = $clubService->get('id');
        $configurationData = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getBackgroundDetails($configId, $viewParams['clubId']);
        $viewParams['backgroundDetails'] = $this->groupingConfigurationData($configurationData);
        $viewParams['tabs'] = $this->getTabDetails(array("background" => array('activeClass' => 'active')), $configId);
        $viewParams['uploadDir'] = FgUtility::getUploadDir();
        $viewParams['breadCrumb'] = array('back' => $this->generateUrl('website_theme_configuration_list'));
        $viewParams['pageTitle'] = $viewParams['backgroundDetails']['title'];
        $viewParams['configId'] = $configId;
        //select current themeid
        $themeConfigObj = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $viewParams['themeId'] = $themeConfigObj->getTheme()->getId();

        return $this->render('WebsiteCMSBundle:UpdateConfiguration:backgroundList.html.twig', $viewParams);
    }

    /**
     * This function will load the font selection data of a specific theme configuration
     *
     * @param object  $request  request object
     * @param Int     $configId theme configuration id
     *
     * @return object View Template Render Object of fontConfiguration action
     */
    public function fontConfigurationAction(Request $request, $configId) {
        $em = $this->getDoctrine()->getManager();
        $viewParams = array();
        $viewParams['configId'] = $configId;
        $clubId = $this->container->get('club')->get('id');
        $isAccessible = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->checkConfiginClub($configId, $clubId);
        if ($isAccessible == 0) {
            throw new AccessDeniedException();
        }
        $viewParams['breadCrumb'] = array('back' => $this->generateUrl('website_theme_configuration_list'));
        $viewParams['tabs'] = $this->getTabDetails(array("font" => array('activeClass' => 'active')), $configId);
        $themeConfigFonts = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeFontsForConfigId($configId);
        if (is_array($themeConfigFonts) && count($themeConfigFonts) > 0) {
            $viewParams['themeConfigFonts'] = $themeConfigFonts;
            $viewParams['defaultConfigFlag'] = 0;
            $viewParams['pageTitle'] = $themeConfigFonts[0]['configTitle'];
        } else {
            $themeData = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeDataForConfigId($configId);
            if (!is_null($themeData)) {
                $viewParams['pageTitle'] = $themeData['configTitle'];
                $themeOptionsArray = json_decode($themeData['themeOptions'], true);
                $count = 0;
                foreach ($themeOptionsArray['font_label'] as $key => $value) {
                    $viewParams['themeConfigFonts'][$count]['id'] = $count + 1;
                    $viewParams['themeConfigFonts'][$count]['fontLabel'] = $key;
                    $viewParams['themeConfigFonts'][$count]['fontName'] = $value['font_name'];
                    $viewParams['themeConfigFonts'][$count]['fontStrength'] = $value['font_strength'];
                    $viewParams['themeConfigFonts'][$count]['isItalic'] = $value['is_italic'];
                    $viewParams['themeConfigFonts'][$count]['isUppercase'] = $value['is_uppercase'];
                    $count++;
                }
            } else {
                $viewParams['themeConfigFonts'] = array();
            }
            $viewParams['defaultConfigFlag'] = 1;
        }
        //select current themeid
        $themeConfigObj = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $viewParams['themeId'] = $themeConfigObj->getTheme()->getId();

        return $this->render('WebsiteCMSBundle:UpdateConfiguration:fontConfiguration.html.twig', $viewParams);
    }

    /**
     * This function will save a new font selection data of a specific theme configuration
     *
     * @param object $request Request Object
     *
     * @return object JSON Response Object
     */
    public function fontConfigurationSaveAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $postData = $request->request->all();
        $configId = $postData['CMS_FONT_CONFIG_ID'];
        $clubId = $this->container->get('club')->get('id');
        $isAccessible = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->checkConfiginClub($configId, $clubId);
        if ($isAccessible == 0) {
            throw new AccessDeniedException();
        }
        $themeConfigFonts = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeFontsForConfigId($configId);
        if (is_array($themeConfigFonts) && count($themeConfigFonts) > 0) {
            $updateData = array();
            if ($postData['FONT_ID'][0] == '1') {
                foreach ($themeConfigFonts as $key => $themeConfigFontsArr) {
                    $key++;
                    $updateData[$key]['id'] = $themeConfigFontsArr['id'];
                    $updateData[$key]['fontLabel'] = $postData[$key . '_LABEL'];
                    $updateData[$key]['fontName'] = $postData[$key . '_NAME'];
                    $updateData[$key]['fontStrength'] = $postData[$key . '_STRENGTH'];
                    $updateData[$key]['isItalic'] = $postData[$key . '_ITALIC'];
                    $updateData[$key]['isUcase'] = $postData[$key . '_UCASE'];
                }
            } else {
                $key = 0;
                foreach ($themeConfigFonts as $themeConfigFontsArr) {
                    $key++;
                    $updateData[$key]['id'] = $themeConfigFontsArr['id'];
                    $updateData[$key]['fontLabel'] = $postData[$themeConfigFontsArr['id'] . '_LABEL'];
                    $updateData[$key]['fontName'] = $postData[$themeConfigFontsArr['id'] . '_NAME'];
                    $updateData[$key]['fontStrength'] = $postData[$themeConfigFontsArr['id'] . '_STRENGTH'];
                    $updateData[$key]['isItalic'] = $postData[$themeConfigFontsArr['id'] . '_ITALIC'];
                    $updateData[$key]['isUcase'] = $postData[$themeConfigFontsArr['id'] . '_UCASE'];
                }
            }
            $em->getRepository('CommonUtilityBundle:FgTmThemeFonts')->updateFontConfigurations($configId, $updateData);
        } else {
            $em->getRepository('CommonUtilityBundle:FgTmThemeFonts')->saveFontConfigurations($configId, $postData);
        }
        //To create dynamic theme css
        $cmsTheme = new FgCmsTheme($this->container);
        $cmsTheme->createCss($configId, 'theme');

        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('TM_CONFIG_FONT_SUCCESS_SAVE'), 'noparentload' => true));
    }

    /**
     *
     * This function will return the tab details of theme configuration section
     *
     * @param Array $activeArray active tab details
     * @param Int   $configId theme configuration id
     * 
     * @return Array array of tabs with details
     */
    private function getTabDetails($activeArray, $configId) {
        $tabs = [];

        $tabs['colors'] = array('text' => $this->get('translator')->trans('CMS_TM_TAB_COLOR'), 'url' => $this->generateUrl('website_theme_color_update', array('configId' => $configId)), 'name' => '', 'type' => 'colors', 'hrefLink' => true);
        $tabs['background'] = array('text' => $this->get('translator')->trans('CMS_TM_TAB_BACKGROUND'), 'url' => $this->generateUrl('website_theme_background_update', array('configId' => $configId)), 'name' => '', 'type' => 'background', 'hrefLink' => true);
        $tabs['header'] = array('text' => $this->get('translator')->trans('CMS_TM_TAB_HEADER'), 'url' => $this->generateUrl('website_theme_header_update', array('configId' => $configId)), 'name' => '', 'type' => 'header', 'hrefLink' => true);
        $tabs['font'] = array('text' => $this->get('translator')->trans('CMS_TM_TAB_FONTS'), 'url' => $this->generateUrl('website_theme_font_update', array('configId' => $configId)), 'name' => '', 'type' => 'font', 'hrefLink' => true);
        $tabs[key($activeArray)]['activeClass'] = $activeArray[key($activeArray)]['activeClass'];

        return $tabs;
    }

    /**
     * To create background configuration array
     * 
     * @param Array $configurationDatas background image configuration data
     * 
     * @return Array arranged array
     */
    private function groupingConfigurationData($configurationDatas) {
        $newConfigurationArray = [];
        $clubService = $this->container->get('club');
        $clubId = $clubService->get('id');
        foreach ($configurationDatas as $detailsKey => $detailsValue) {
            $newConfigurationArray['title'] = $detailsValue['title'];
            $newConfigurationArray['id'] = $detailsValue['configId'];
            $newConfigurationArray['bgImageSelection'] = $detailsValue['bgImageSelection'];
            $newConfigurationArray['bgSliderTime'] = $detailsValue['bgSliderTime'];

            $bgType = ($detailsValue['bgType'] == '') ? 'default' : $detailsValue['bgType'];
            $detailsValue['imgSrc'] = "/uploads/$clubId/gallery/width_300/" . $detailsValue["fileName"];
            $newConfigurationArray[$bgType][] = $detailsValue;
        }

        return $newConfigurationArray;
    }

    /**
     *
     * This function will list the color schemes of a particular theme configuration
     *
     * @param Int $configId theme configuration id
     * 
     * @return object View Template Render Object
     */
    public function listAllColorSchemesAction($configId) {
        $viewParams = array();
        $viewParams['configId'] = $configId;
        $clubId = $this->container->get('club')->get('id');
        $isAccessible = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->checkConfiginClub($configId, $clubId);
        $viewParams['configDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeDataForConfigId($themeConfId);
        if ($isAccessible == 0) {
            throw new AccessDeniedException();
        }
        $themeConfigObj = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $viewParams['pageTitle'] = $themeConfigObj->getTitle();
        $viewParams['breadCrumb'] = array('back' => $this->generateUrl('website_theme_configuration_list'), $configId);
        $viewParams['tabs'] = $this->getTabDetails(array("colors" => array('activeClass' => 'active')), $configId);
        $viewParams['themeId'] = $themeConfigObj->getTheme()->getId();

        return $this->render('WebsiteCMSBundle:UpdateConfiguration:listAllColorSchemes.html.twig', $viewParams);
    }

    /**
     *
     * This function will edit configuration title
     *
     * @param Array $request request object
     * 
     * @return JsonResponse saved data
     */
    public function pageTitleEditAction(Request $request) {
        $config = $request->get('config');
        $title = $request->get('title');
        $newTitle = FgUtility::getSecuredData($title, $this->getDoctrine()->getConnection());
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->changeConfigTitle($config, $newTitle);

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('TITLE_EDIT_SUCCESS')));
    }

    /**
     *
     * This function will return the listing of color schemes
     *
     * @param object $request request object
     * 
     * @return object json object
     */
    public function getColorSchemesListAction(Request $request) {
        $configId = $request->get('configId');
        $clubId = $this->container->get('club')->get('id');
        $colorsList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeColors($configId, $clubId, $this->container);

        return new JsonResponse($colorsList);
    }

    /**
     * Actions in color scheme
     * 
     * @param Request $request request
     * @param string  $type color type
     * 
     * @return JsonResponse json object
     */
    public function actionsInColorSchemeAction(Request $request, $type) {
        $color = $request->get('color');
        $config = $request->get('config');
        $clubId = $this->container->get('club')->get('id');
        if ($type === 'activate') {
            $contactId = $this->container->get('contact')->get('id');
            $activateColor = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->activateColorScheme($config, $color, $contactId, $this->container->get('club'));
            if ($activateColor) {
                $colorsList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeColors($config, $clubId, $this->container);
                $return = array('status' => 'SUCCESS', 'data' => $colorsList, 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_ACTIVATION_SUCCESS'));
            } else {
                $return = array('status' => 'FAILURE', 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_ACTIVATION_FAILED'));
            }
        } else if ($type === 'duplicate') {
            $colorSchemeData = $request->get('colorSchemeData');
            $themeId = $request->get('themeId');
            //save
            $duplicateColor = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->duplicateColorScheme($clubId, $themeId, $colorSchemeData);
            //Create color scheme css file
            $cmsTheme = new FgCmsTheme($this->container);
            $cmsTheme->createCss($config, 'color', $duplicateColor);
            if ($duplicateColor) {
                $colorsList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeColors($config, $clubId, $this->container);
                $return = array('status' => 'SUCCESS', 'data' => $colorsList, 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_DUPLICATE_SUCCESS'));
            } else {
                $return = array('status' => 'FAILURE', 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_DUPLICATE_FAILED'));
            }
        } else if ($type === 'delete') {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->deleteColorScheme($color);
            //Delete css file
            $cmsTheme = new FgCmsTheme($this->container);
            $cmsTheme->removeCssFile($config, 'color', $color);
            $colorsList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeColors($config, $clubId, $this->container);
            $return = array('status' => 'SUCCESS', 'data' => $colorsList, 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_DELETE_SUCCESS'));
        }

        return new JsonResponse($return);
    }

    /**
     * To save a new color scheme
     * 
     * @param Request $request request object
     * 
     * @return JsonResponse json object
     */
    public function colorSchemeCreateAction(Request $request) {
        $colorSchemeData = $request->get('colorSchemeData');
        $themeId = $request->get('themeId');
        $clubId = $this->container->get('club')->get('id');
        $config = $request->get('config');
        $flag = $request->get('flag');
        $colorId = '';
        if ($flag == 'edit') {
            $colorId = $request->get('colorId');
        }
        //save
        $createColor = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->duplicateColorScheme($clubId, $themeId, $colorSchemeData, $flag, $colorId);
        //To create dynamic color scheme css
        $cmsTheme = new FgCmsTheme($this->container);
        $cmsTheme->createCss($config, 'color', $createColor);
        if ($createColor) {
            $colorsList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getAllThemeColors($config, $clubId, $this->container);
            $return = array('status' => 'SUCCESS', 'data' => $colorsList, 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_' . strtoupper($flag) . '_SUCCESS'));
        } else {
            $return = array('status' => 'FAILURE', 'noparentload' => true, 'flash' => $this->get('translator')->trans('COLOR_SCHEME_' . strtoupper($flag) . '_FAILED'));
        }

        return new JsonResponse($return);
    }

    /**
     * To save the background image details
     * 
     * @param Request $request request object
     * 
     * @return JsonResponse saved details
     */
    public function backgroundSaveAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $imageDetails = json_decode($request->get('imageDetails'), true);
        $configId = $request->get('configId');
        $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->saveFullScreenDetails($imageDetails, $configId, $this->container);
        if (isset($imageDetails['backgroundimage']['fullscreen']["media"]["images"]['media'])) {
            $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->saveOriginalSizeDetails($imageDetails['backgroundimage']['fullscreen']["media"]["images"]['media'], $configId, $this->container->get('club'));
        }
        if (isset($imageDetails['backgroundimage']['original']["media"]["images"]['new'])) {
            $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->saveOriginalBgDetails($imageDetails['backgroundimage']['original']["media"]["images"]['new'], $configId, $this->container);
        }
        //Tab2 image add from gallery
        if (isset($imageDetails['backgroundimage']['original']["media"]["images"]['media'])) {
            $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->saveOriginalBgFromGallery($imageDetails['backgroundimage']['original']["media"]["images"]['media'], $configId, $this->container->get('club'));
        }
        //Tab2 image details update
        if (isset($imageDetails['backgroundimage']['original']["media"]["images"]['update'])) {
            $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->updateOriginalBgFromGallery($imageDetails['backgroundimage']['original']["media"]["images"]['update'], $configId, $this->container->get('club'));
        }
        //To create dynamic theme css
        $cmsTheme = new FgCmsTheme($this->container);
        $cmsTheme->createCss($configId, 'theme');
        $backgroundDetails = $this->backgroundtabData($configId);


        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->container->get('translator')->trans('THEME_BG_SAVE_SUCCESS_MESSAGE'), 'backgroundData' => $backgroundDetails));
    }

    /**
     * To gather background data
     * 
     * @param Int $configId theme configuration id
     * 
     * @return array background data
     */
    public function backgroundtabData($configId) {
        $clubService = $this->container->get('club');
        $clubId = $clubService->get('id');
        $em = $this->getDoctrine()->getManager();
        $configurationData = $em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getBackgroundDetails($configId, $clubId);

        return $this->groupingConfigurationData($configurationData);
    }

    /*
     * This function will save  the header  of a specific theme configuration
     * 
     * @param array Request $request request
     *
     * @return json $return save details
     */

    public function headerSaveAction(Request $request) {
        $data = $request->request->all();
        $clubId = $this->container->get('club')->get('id');
        $themeConfId = $data['configId'];
        $isAccessible = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->checkConfiginClub($themeConfId, $clubId);
        if ($isAccessible == 0) {
            throw new AccessDeniedException();
        }
        $configDetails = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeDataForConfigId($themeConfId);
        if ($configDetails['id'] == 1) {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->changeHeaderType($configDetails['id'], $themeConfId, array('scrolling' => $data['headerStyle']), $this->container->get('club'));
        } elseif ($configDetails['id'] == 2) {
            $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->changeHeaderType($configDetails['id'], $themeConfId, array('headoption' => $data['theme2head'], 'logoStyle' => $data['logoStyle']), $this->container->get('club'));
        }
        $this->get('fg.avatar')->moveWebsiteHeader($themeConfId, $data, $this->container->get('club')->get('id'), 1);
        $viewParams['themeConfigHeaderOptions'] = json_decode($configDetails['themeOptions'], true);
        $viewParams['themeConfigHeaderDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeHeaderDetails($themeConfId);

        $viewParams['savedConfig'] = array();
        if (is_array($viewParams['themeConfigHeaderDetails']) && count($viewParams['themeConfigHeaderDetails']) > 0) {
            $k = 0;
            $headerLables = $viewParams['themeConfigHeaderOptions']['headerImageLabels'];
            foreach ($headerLables as $headers) {
                $flag = 0;
                foreach ($viewParams['themeConfigHeaderDetails'] as $headerDetails) {
                    if (($headerDetails['headerLabel'] == $headers)) {
                        $viewParams['savedConfig'][$k] = $headerDetails;
                        $viewParams['savedConfig'][$k]['typeid'] = $k;
                        $flag = 1;
                    } else {
                        if ($flag == 0) {
                            $viewParams['savedConfig'][$k] = '';
                            $viewParams['savedConfig'][$k]['typeid'] = $k;
                        }
                    }
                }
                $k++;
            }
        }
        $viewParams['configDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeDataForConfigId($themeConfId);
        
        $return = array('status' => 'SUCCESS', 'noparentload' => true, 'viewParams' => $viewParams, 'flash' => $this->get('translator')->trans('HEADER_CONFIGURATION_SAVE_SUCCESS'));
        return new JsonResponse($return);
    }

    /*
     * This function will load the header  of a specific theme configuration
     * 
     * @param Int $configId theme configuration id
     *
     * @return object View Template Render Object of fontConfiguration action
     */

    public function headerEditAction($configId) {
        $breadCrumb = array('back' => $this->generateUrl('website_theme_configuration_list'));
        $viewParams = array('pageTitle' => 'CMS_TM_CONFIG_TITLE', 'configId' => $configId, 'createHeader' => 0, 'breadCrumb' => $breadCrumb, 'club_id' => $this->container->get('club')->get('id'));
        $viewParams['tabs'] = $this->getTabDetails(array("header" => array('activeClass' => 'active')), $configId);
        $clubId = $this->container->get('club')->get('id');
        $viewParams['configDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeDataForConfigId($configId);
        $viewParams['themeId'] = $viewParams['configDetails']['id'];
        $isAccessible = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->checkConfiginClub($configId, $clubId);
        if ($isAccessible == 0) {
            throw new AccessDeniedException();
        }
        $viewParams['themeConfigHeaderOptions'] = json_decode($viewParams['configDetails']['themeOptions'], true);
        $viewParams['pageTitle'] = $viewParams['configDetails']['configTitle'];
        $cmsThemeObj = new FgCmsTheme($this->container);
        $viewParams['headerLabels'] = $cmsThemeObj->getTranslatedHeaderLabels($viewParams['themeConfigHeaderOptions']['headerImageLabels']);
        $viewParams['themeConfigHeaderDetails'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->getThemeHeaderDetails($configId);

        $viewParams['savedConfig'] = array();
        if (is_array($viewParams['themeConfigHeaderDetails']) && count($viewParams['themeConfigHeaderDetails']) > 0) {
            $k = 0;
            $headerLables = $viewParams['themeConfigHeaderOptions']['headerImageLabels'];
            foreach ($headerLables as $headers) {
                $flag = 0;
                foreach ($viewParams['themeConfigHeaderDetails'] as $headerDetails) {
                    if (($headerDetails['headerLabel'] == $headers)) {
                        $viewParams['savedConfig'][$k] = $headerDetails;
                        $viewParams['savedConfig'][$k]['typeid'] = $k;
                        $flag = 1;
                    } else {
                        if ($flag == 0) {
                            $viewParams['savedConfig'][$k] = '';
                            $viewParams['savedConfig'][$k]['typeid'] = $k;
                        }
                    }
                }
                $k++;
            }
        }


        return $this->render('WebsiteCMSBundle:UpdateConfiguration:headerEditView.html.twig', $viewParams);
    }

}
