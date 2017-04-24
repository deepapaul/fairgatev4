<?php

/**
 * Create css for each theme and color 
 * @package 	name
 * @subpackage 	name
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Util\FgUtility;

/**
 * FgCssDeployer
 * 
 * Create dynamic css for each theme and color 
 */
class FgCssDeployer
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
     * @var array $elementsArray 
     */
    private $elementsArray;

    /**
     * Constructor of FgCssDeployer class.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Constructor of FgCssDeployer class.
     *
     * @param string             $type          color/theme
     * @param array              $elementsArray elements to build css
     * @param array              $themeId       theme Id
     * @param array              $primaryId     configurationId/colorSchemeId
     */
    public function generateCss($type, $elementsArray, $themeId, $primaryId)
    {
        $this->em = $this->container->get('doctrine')->getManager();
        $this->elementsArray = $elementsArray;
        $this->clubId = $this->container->get('club')->get('id');
        $this->themeId = $themeId;
        $this->type = $type;
        if ($type == 'color') {
            $this->colorSchemeId = $primaryId;
        } else {
            $this->configurationId = $primaryId;
        }
        //generate css file
        $this->createCssFile();
        //update css filename in db
        $this->updateCssFilename();

        return $this->cssFileName;
    }

    /**
     * Method to generate css file and set its name to variable $cssFileName
     * 
     * @return void
     */
    private function createCssFile()
    {
        $currentTimestamp = date('YmdHis') . rand(0, 99);
        $clubId = $this->getClubId();

        $cmsFolder = FgUtility::getUploadFilePath($clubId, 'cms_themecss');
        $themeFiles = $this->container->getParameter('themeFiles');
        $folder = "$cmsFolder/" . $this->themeId;
        if (!is_dir($folder)) {
            mkdir($folder, 0777, true);
        }
        if ($this->type != 'color') { //for configuration
            $this->getFontFamilyArray();
            $this->elementsArray['themeId'] = $this->themeId;       
            $viewPage = $themeFiles['themeChangedFiles']['theme'.$this->themeId]['themeConfCssTemplate'];                      
            $cssContent = $this->container->get('templating')->render($viewPage, $this->elementsArray);
            $fileName = $this->configurationId . '_theme_' . $currentTimestamp . '.css';
            //delete previous files            
            $this->deletePreviousFiles($folder, $this->configurationId . '_theme_');
        } else { //for color scheme
            $this->elementsArray['themeId'] = $this->themeId;            
            $viewPage = $themeFiles['themeChangedFiles']['theme'.$this->themeId]['colorSchemeCssTemplate'];          
            $cssContent = $this->container->get('templating')->render($viewPage, $this->elementsArray);
            $fileName = $this->colorSchemeId . '_color_' . $currentTimestamp . '.css';
            //delete previous files
            $this->deletePreviousFiles($folder, $this->colorSchemeId . '_color_');
        }

        file_put_contents($folder . '/' . $fileName, $cssContent);

        $this->cssFileName = $fileName;
    }

    /**
     * Method to get font famileis as an array and append it to elementsArray. used for importing google fonts
     */
    private function getFontFamilyArray()
    {
        $fontsArray = array();
        foreach ($this->elementsArray['font'] as $key => $fonts) {
            $fontName = str_replace(' ', '+', $fonts['fontName']);
            $fontName .= ($fonts['isItalic']) ? ':400,400i' : ':400';
            if ($key == 'TM_MAIN_TEXT') {
                $fontName = str_replace(' ', '+', $fonts['fontName']) . ':300,300i,400,400i,700,700i';
            } else if ($fonts['fontStrength'] == 'lighter') {
                $fontName .= ($fonts['isItalic']) ? ',300,300i' : ',300';
            } else if ($fonts['fontStrength'] == 'bold') {
                $fontName .= ($fonts['isItalic']) ? ',700,700i' : ',700';
            }

            if ($fontName && !in_array($fontName, $fontsArray)) {
                $fontsArray[] = $fontName;
            }
        }
        $this->elementsArray['fontsArray'] = $fontsArray;
    }

    /**
     * Method to delete previous files
     * 
     * @param string $folder   foldername
     * @param string $fileName filestructure name to delete
     * 
     * @return void
     */
    public function deletePreviousFiles($folder, $fileName)
    {
        $previousFiles = glob("$folder/$fileName*.css");
        foreach ($previousFiles as $previousFile) {
            unlink($previousFile);
        }
    }

    /**
     * Method to update new css filename
     * 
     * @return void
     */
    private function updateCssFilename()
    {
        if ($this->cssFileName) {
            if ($this->type != 'color') {
                $confObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($this->configurationId);
                $confObj->setCssFilename($this->cssFileName);
            } else {
                $colorSchemeObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->find($this->colorSchemeId);
                $colorSchemeObj->setCssFilename($this->cssFileName);
            }
            $this->em->flush();
        }
    }

    /**
     * Method to get clubId from table FgTmThemeConfiguration/FgTmThemeColorScheme based on configuration or color
     * 
     * @return int clubId
     */
    private function getClubId()
    {
        if ($this->type != 'color') {
            $confObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($this->configurationId);

            return $confObj->getClub()->getId();
        } else {
            $colorSchemeObj = $this->em->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->find($this->colorSchemeId);

            return $colorSchemeObj->getClub()->getId();
        }
    }
}
