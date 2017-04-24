<?php

/**
 * Manage CMS Theme container functionalities
 *
 *
 */
namespace Website\CMSBundle\Util;

use Website\CMSBundle\Util\FgCssDeployer;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Manage CMS theme container functionalities
 *
 * 
 */
class FgCmsTheme
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
     * Constructor of FgCmsThemeContainerDetails class.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to get Label For Themes
     *
     * @param array  $themeHeaders     themeList
     * @return boolean
     */
    public function getTranslatedHeaderLabels($themeHeaders)
    {
        $translatedLabels = array();
        $i = 0;
        foreach ($themeHeaders as $labels) {
            $translatedLabels[$i] = $this->container->get('translator')->trans($labels);
            $i++;
        }

        return $translatedLabels;
    }

    /**
     * This function is used to get theme configurations to create dynamic css
     * 
     * @param int $configId Theme configuration id
     * 
     * @return array Theme configuration details
     */
    public function getThemeConfiguration($configId)
    {
        $result = array();
        $themeConfigObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $themeObj = $themeConfigObj->getTheme();
        $result['theme'] = json_decode($themeObj->getThemeOptions(), true);
        $result['font'] = $this->getFontDetails($configId);
        if (count($result['font']) == 0) {
            $result['font'] = $this->formatFontArray($result['theme']['font_label']);
        }
        $bgImages = $this->em->getRepository('CommonUtilityBundle:FgTmThemeBgImages')->getBgImageConfiguration($configId, 'original_size');
        $result['bgImage'] = $this->formatBgImage($bgImages);
        $result['header'] = $this->em->getRepository('CommonUtilityBundle:FgTmThemeHeaders')->getHeaderDetails($configId);

        return $result;
    }

    /**
     * To format default font array
     * 
     * @param array $fontArray Font array
     * 
     * @return array formatted font
     */
    public function formatFontArray($fontArray)
    {
        $result = array();
        foreach ($fontArray as $key => $val) {
            $result[$key]['fontName'] = $val['font_name'];
            $result[$key]['fontStrength'] = $val['font_strength'];
            $result[$key]['isItalic'] = $val['is_italic'];
            $result[$key]['isUppercase'] = $val['is_uppercase'];
        }

        return $result;
    }

    /**
     * To format background image details array for css file
     * 
     * @param array $bgImageArray background image details
     * 
     * @return array
     */
    public function formatBgImage($bgImageArray)
    {
        $result = $bgImage = $bgPosition = $bgRepeat = $bgAttachment = array();
        $bgParams = array(
            'none' => 'no-repeat',
            'both' => 'repeat',
            'vertical' => 'repeat-y',
            'horizontal' => 'repeat-x',
        );
        foreach ($bgImageArray as $val) {
            $bgImage[] = 'url( "' . FgUtility::getUploadFilePath($val['clubId'], 'cms_dynamic_css') . $val['filePath'] . '" )';
            $bgPosition[] = $val['positionHorizontal'] . ' ' . $val['positionVertical'];
            $bgAttachment[] = $val['isScrollable'] == '1' ? 'scroll' : 'fixed';
            $bgRepeat[] = $bgParams[$val['bgRepeat']];
        }
        $result['bgImage'] = implode(',', $bgImage);
        $result['bgPosition'] = implode(',', $bgPosition);
        $result['bgAttachment'] = implode(',', $bgAttachment);
        $result['bgRepeat'] = implode(',', $bgRepeat);

        return $result;
    }

    /**
     * Function is used to get Font configuration

     * @param  type  $configId Configuration id
     * 
     * @return array Font configuration details
     */
    private function getFontDetails($configId)
    {
        $fontDetails = $this->em->getRepository('CommonUtilityBundle:FgTmThemeFonts')->getFontConfiguration($configId);
        $result = array();
        foreach ($fontDetails as $font) {
            $result[$font['fontLabel']] = $font;
        }

        return $result;
    }

    /**
     * This function is used to get color scheme configurations to create dynamic css
     * 
     * @param  int   $colorScemeId Theme color scheme id
     * 
     * @return array Color scheme configurations
     */
    public function getColorSchemeConfiguration($colorScemeId)
    {
        $colorSchemeObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->find($colorScemeId);

        return json_decode($colorSchemeObj->getColorSchemes(), true);
    }

    /**
     * This function is used to generate dynamic css for theme configuration and color scheme.
     *     
     * @param int  $configId        Theme Configuration id
     * @param type $type            Type of css file to be generated - theme/color 
     * @param int  $colorScemeId    Color scheme id
     * @param int  $routingFlag     Flag to identify whether invoked from routing
     * 
     * @return string New generated css filename
     */
    public function createCss($configId, $type, $colorScemeId = null, $routingFlag = 0)
    {
        $themeConfigObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $themeId = $themeConfigObj->getTheme()->getId();
        if ($type != 'color') {
            $elementsArray = $this->getThemeConfiguration($configId);
            $primaryId = $configId;
        } else {
            $elementsArray = $this->getColorSchemeConfiguration($colorScemeId);
            $primaryId = $colorScemeId;
        }
        $cssDeployer = new FgCssDeployer($this->container);
        $fileName = $cssDeployer->generateCss($type, $elementsArray, $themeId, $primaryId);

        //Remove apc cache entries while updating theme configuration
        $clubCacheKey = $this->container->get('club')->get('clubCacheKey');
        $cachingEnabled = $this->container->get('club')->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while updating theme configuration
        if ($routingFlag) {

            return $fileName;
        }
    }

    /**
     * Function to delete css file
     * 
     * @param int    $configId      Theme configuration Id
     * @param string $type          Type of css file to be deleted - theme/color
     * @param string $colorSchemeId Color scheme id
     * 
     * @return void Delete css file.
     */
    public function removeCssFile($configId, $type = 'theme', $colorSchemeId = '')
    {
        $themeConfigObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $themeId = $themeConfigObj->getTheme()->getId();
        $cmsFolder = FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'cms_themecss');
        $folder = "$cmsFolder/" . $themeId;
        $cssDeployer = new FgCssDeployer();
        if ($type == 'theme') {
            $cssDeployer->deletePreviousFiles($folder, $configId . '_theme_');
        } else {
            $cssDeployer->deletePreviousFiles($folder, $colorSchemeId . '_color_');
        }
    }
}
