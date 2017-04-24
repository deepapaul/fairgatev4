<?php

/**
 * ThemeConfigurationCreationController -- Controller to create theme configuration
 * 
 * @package 	name
 * @subpackage 	name
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Website\CMSBundle\Util\FgCmsTheme;
use Common\UtilityBundle\Util\FgUtility;


/**
 * ThemeConfigurationCreationController
 * 
 * Controller to create theme configuration
 */
class ThemeConfigurationCreationController extends Controller
{

    /**
     * This function is used to create theme configuration
     * 
     * @return object View Template Render Object
     */
    public function themeSelectionAction()
    {
        //theme configuration listing page
        $returnArray['breadCrumb'] = array('back' => $this->generateUrl('website_theme_configuration_list'));
        $headerLabels = array();
        $returnArray['themeList'] = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmTheme')->getAllThemes();
        $cmsThemeObj = new FgCmsTheme($this->container);
        foreach ($returnArray['themeList'] as $key => $themes) {
            $headerLabels[$key] = $cmsThemeObj->getTranslatedHeaderLabels($themes['themeOptions']['headerImageLabels']);
        }
        $returnArray['themeListJson'] = json_encode($returnArray['themeList']);
        $returnArray['headerLabels'] = $headerLabels;
        $returnArray['createHeader'] = 1;

        return $this->render('WebsiteCMSBundle:CreateConfiguration:themeSelection.html.twig', $returnArray);
    }

    /**
     * This function is used to save theme configuration
     * 
     * @return object View Template Render Object
     */
    public function saveThemeConfigurationAction(Request $request)
    {
        $data = $request->request->all();
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');
        $data['title'] = FgUtility::getSecuredData($data['title'], $this->getDoctrine()->getConnection());
        $themeConfId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->saveThemeConfiguation($contactId, $clubId, $data);
        $this->get('fg.avatar')->moveWebsiteHeader($themeConfId, $data, $this->container->get('club')->get('id'), 1);
        //To create dynamic theme and color scheme css
        $cmsTheme = new FgCmsTheme($this->container);
        $cmsTheme->createCss($themeConfId, 'theme');
        $colorSchemeId = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($themeConfId)->getColorScheme()->getId();
        $cmsTheme->createCss($themeConfId, 'color', $colorSchemeId);
        $return = array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('THEME_CONFIGURATION_SAVE_SUCCESS'));

        return new JsonResponse($return);
    }
}
