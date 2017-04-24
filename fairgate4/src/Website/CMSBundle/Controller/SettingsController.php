<?php

/**
 * SettingsController.
 */
namespace Website\CMSBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgIcoGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * SettingsController
 * 
 * This controller is used for manage website settings
 * 
 * @package 	Website
 * @subpackage 	CMSBundle
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
class SettingsController extends Controller
{

    /**
     * Function render settings page
     * 
     * @param object $request Request
     * 
     * @return object View Template Render Object
     */
    public function settingsViewAction(Request $request)
    {
        $returnArray['breadCrumb'] = array();
        $returnArray['favIconsGenerated'] = ($request->get('favicon') == 'true') ? 'SUCCESS' : (($request->get('favicon') == 'false') ? 'ERROR' : false);
        $returnArray['clubLanguageArr'] = $this->container->get('club')->get('club_languages');
        $returnArray['defaultClubLang'] = $this->container->get('club')->get('club_default_lang');
        $returnArray['clubLanguages'] = json_encode($this->container->get('club')->get('club_languages'));
        $returnArray['baseUrl'] = FgUtility::getBaseUrlForFavIcon($this->container);
        if ($request->isXmlHttpRequest()) {
            $returnArray['isAjax'] = true;
        } else {
            $returnArray['isAjax'] = false;
        }

        return $this->render('WebsiteCMSBundle:Settings:settingsView.html.twig', $returnArray);
    }

    /**
     * Function to get web settings
     * 
     * @return JsonResponse
     */
    public function getSettingsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $webSettings = $em->getRepository('CommonUtilityBundle:FgWebSettings')->getWebSettings($clubId, $club->get('clubCacheKey'), $club->get('cache_lifetime'));
        $webSettings['websettingsFolder'] = FgUtility::getUploadFilePath($clubId, 'cms_websettings');
        $webSettings['faviconsFolder'] = FgUtility::getUploadFilePath($clubId, 'cms_favicons');

        return new JsonResponse($webSettings);
    }

    /**
     * Function to save website settings
     * 
     * @return JsonResponse
     */
    public function saveSettingsAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $dataArray = json_decode($request->get('saveData'), true);
        //make directory if not exist
        $this->makeWebSettingsFolder();
        if (isset($dataArray['logo_originalname']) || isset($dataArray['ogimg_originalname']) || isset($dataArray['html_originalname']) || isset($dataArray['favicon_originalname'])) {
            //delete old images from respective folders
            $this->deleteOldImages($dataArray);
        }
        if (isset($dataArray['logo_name']) && isset($dataArray['logo_originalname'])) {
            //save image to web settings folder and rezises            
            $dataArray['logo_originalname'] = $this->saveImgToWebsettings($dataArray['logo_name'], $dataArray['logo_originalname'], 'default_logo');
        }
        if (isset($dataArray['ogimg_name']) && isset($dataArray['ogimg_originalname'])) {
            //save image to web settings folder and rezises           
            $dataArray['ogimg_originalname'] = $this->saveImgToWebsettings($dataArray['ogimg_name'], $dataArray['ogimg_originalname'], 'og_image');
        }
        if (isset($dataArray['html_name']) && isset($dataArray['html_originalname'])) {
            $this->saveDomainVerificationToWeb($dataArray['html_name'], $dataArray['html_originalname']);
        }
        if (isset($dataArray['favicon_name']) && isset($dataArray['favicon_originalname'])) {
            $tempDirectory = FgUtility::getUploadDir() . "/temp/";
            $faviconsFolder = FgUtility::getWebDir() . FgUtility::getUploadFilePath($this->container->get('club')->get('id'), 'cms_favicons') . "/";
            $this->resizeFavicons($dataArray['favicon_name'], $tempDirectory, $dataArray['favicon_originalname'], $faviconsFolder);
        }

        //save to table fg_web_settings
        $em = $this->getDoctrine()->getManager();
        $em->getRepository('CommonUtilityBundle:FgWebSettings')->saveSettings($dataArray, $this->container);

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' =>true, 'flash' => $this->get('translator')->trans('WEBSETTTINGS_SAVED_SUCCESS')));
    }

    /**
     * Function to save the url favisons from the faveicon generator
     * 
     * @return object View Template Render Object
     */
    public function saveFavIconAction(Request $request)
    {
        $result = $request->get('json_result');
        $status = 'false';
        if ($result != '') {

            $this->makeWebSettingsFolder();
            $resultArray = json_decode($result, true);
            $clubId = $this->container->get('club')->get('id');

            if ($resultArray['favicon_generation_result']['result']['status'] == 'success') {
                $status = 'true';
                $favIconApiLocation = $resultArray['favicon_generation_result']['favicon']['package_url'];
                $this->downloadAndSaveFavicons($favIconApiLocation, $clubId);

                //move original logo to settings folder
                if ($resultArray['favicon_generation_result']['custom_parameter'] != '') {
                    $fileDetailArray = explode('##__##', $resultArray['favicon_generation_result']['custom_parameter']);
                    if ($fileDetailArray[0] != '' && $fileDetailArray[1] != '') {
                        $websettingsFolder = FgUtility::getWebDir() . FgUtility::getUploadFilePath($clubId, 'cms_websettings') . '/' . $fileDetailArray[1];
                        $tempFolder = FgUtility::getUploadDir() . "/temp/" . $fileDetailArray[0];
                        rename($tempFolder, $websettingsFolder);

                        //save to table fg_web_settings
                        $dataArray = array('logo_originalname' => $fileDetailArray[1], 'favicon_originalname' => 'favicon.ico');
                        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgWebSettings')->saveSettings($dataArray, $this->container);
                    }
                }
            }
        }

        return $this->redirect($this->generateUrl('website_cms_settings', array('favicon' => $status)));
    }

    /**
     * Method to delete old images from respective folders, when updating
     * 
     * @param array $dataArray data to save
     */
    private function deleteOldImages($dataArray)
    {
        $club = $this->container->get('club');
        $clubId = $club->get('id');
        $em = $this->getDoctrine()->getManager();
        $webSettings = $em->getRepository('CommonUtilityBundle:FgWebSettings')->getWebSettings($clubId, $club->get('clubCacheKey'), $club->get('cache_lifetime'));
        $webDir = FgUtility::getWebDir();
        $websettingsFolder = FgUtility::getUploadFilePath($clubId, 'cms_websettings');
        //delete old default logo
        if (isset($dataArray['logo_originalname'])) {
            $this->deleteDefaultLogoImages($webSettings, $clubId, $webDir, $websettingsFolder);
        }
        //delete old OG fall back image
        if (isset($dataArray['ogimg_originalname'])) {
            $this->deleteFallbackImage($webSettings, $webDir, $websettingsFolder);
        }

        //delete old domain verification file
        if (isset($dataArray['html_originalname'])) {
            $this->deleteDomainVerificationFile($webSettings, $webDir);
        }

        //delete old favicon file (original file and ico file)
        if (isset($dataArray['favicon_originalname'])) {
            $this->deleteFavicons($clubId, $webDir);
        }
    }

    /**
     * Method to delete domain veification file from web directory
     * 
     * @param array  $webSettings current web settings data
     * @param string $webDir      web directory path
     */
    private function deleteDomainVerificationFile($webSettings, $webDir)
    {
        $this->unlinkFile($webDir . "/" . $webSettings['domainVerificationFilename']);
    }

    /**
     * Method to delete fall back image from web settings folder
     * 
     * @param array  $webSettings       current web settings data
     * @param string $webDir            web directory path
     * @param string $websettingsFolder websettings folder path
     */
    private function deleteFallbackImage($webSettings, $webDir, $websettingsFolder)
    {
        $this->unlinkFile($webDir . "$websettingsFolder/" . $webSettings['fallbackImage']);
    }

    /**
     * Method to delete favicons from favicons folder
     * 
     * @param int    $clubId clubId
     * @param string $webDir web directory path
     */
    private function deleteFavicons($clubId, $webDir)
    {
        $faviconFolder = FgUtility::getUploadFilePath($clubId, 'cms_favicons');
        //unlink all file from folder favicons
        array_map('unlink', glob($webDir . "$faviconFolder/*.*", GLOB_BRACE));
    }

    /**
     * Method to delete default logo images from websettings folder and default icons
     * 
     * @param array  $webSettings       current web settings data
     * @param int    $clubId            clubId
     * @param string $webDir            web directory path
     * @param string $websettingsFolder websettings folder path
     */
    private function deleteDefaultLogoImages($webSettings, $clubId, $webDir, $websettingsFolder)
    {
        $logoFile = $webDir . "$websettingsFolder/" . $webSettings['defaultLogo'];
        //unlink file from websettings folder
        $this->unlinkFile($logoFile);
        $defaultLogoFolder = FgUtility::getUploadFilePath($clubId, 'cms_default_logo');
        //unlink all file from folder default_logo
        array_map('unlink', glob($webDir . "$defaultLogoFolder/*.*", GLOB_BRACE));
    }

    /**
     * Method to unlink file after checking whether it exist
     * 
     * @param string $filePath filePath
     */
    private function unlinkFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    /**
     * Method to save domain verification file to web directory and remove from temp
     * 
     * @param string $tempName     temp name of uploaded file
     * @param string $originalName original name of uploaded file
     */
    private function saveDomainVerificationToWeb($tempName, $originalName)
    {
        $tempFilename = FgUtility::getUploadDir() . "/temp/$tempName";
        $webDir = FgUtility::getWebDir();
        if (file_exists($tempFilename)) {
            //move file from temp folder to web folder
            rename($tempFilename, "$webDir/$originalName");
        }
    }

    /**
     * Method to save default_logo and og_image to web settings folder and resize accordingly
     * 
     * @param string $tempName     temp name of uploaded file
     * @param string $originalName original name of uploaded file
     * @param string $type         default_logo/og_image
     * 
     * @return string $fileName name in which file is saved in folder
     */
    private function saveImgToWebsettings($tempName, $originalName, $type)
    {
        $uploadDirectory = FgUtility::getUploadDir() . "/";
        $webDir = FgUtility::getWebDir();
        $tempFilename = $uploadDirectory . "temp/$tempName";
        $clubId = $this->container->get('club')->get('id');
        $websettingsFolder = FgUtility::getUploadFilePath($clubId, 'cms_websettings');
        //If file already exist in the folder, append 1, 2 to name
        $fileName = FgUtility::getFilename($websettingsFolder, $originalName);
        $filePath = $webDir . "$websettingsFolder/" . $fileName;
        if (file_exists($tempFilename)) {

            //move file from temp folder to web_settings folder
            rename($tempFilename, $filePath);

            if ($type == 'default_logo') {
                $this->resizeDefaultLogo($filePath, $webDir, $clubId);
            }

            if ($type == 'og_image') {
                list($width, $height) = getimagesize($filePath);
                FgUtility::resizeFolderImages($filePath, $filePath, $width, $height, 1200, 630);
            }
        }

        return $fileName;
    }

    /**
     * Method to resize default logo to 4 specified seizes and names (http://192.168.0.252:8090/display/website/Website+Settings - 3 Version - A)
     * 
     * @param string $fileName logo file name
     * @param string $webDir   web diresctory folder
     * @param int    $clubId   clubId
     */
    private function resizeDefaultLogo($fileName, $webDir, $clubId)
    {
        //convert to android-chrome-192x192.png
        $savePath = $webDir . FgUtility::getUploadFilePath($clubId, 'cms_default_logo') . "/android-chrome-192x192.png";
        $this->resizeLogo($fileName, $savePath, 192, 192, 'transparent');

        //convert to andriod-chrome-384x384.png
        $savePath = $webDir . FgUtility::getUploadFilePath($clubId, 'cms_default_logo') . "/andriod-chrome-384x384.png";
        $this->resizeLogo($fileName, $savePath, 384, 384, 'transparent');

        //convert to  mstile-150x150.png
        $savePath = $webDir . FgUtility::getUploadFilePath($clubId, 'cms_default_logo') . "/mstile-150x150.png";
        $this->resizeLogo($fileName, $savePath, 270, 270, 'transparent');

        //convert to  apple-touch-icon.png ( with background white and remove transparency)
        $savePath = $webDir . FgUtility::getUploadFilePath($clubId, 'cms_default_logo') . "/apple-touch-icon.png";
        $this->resizeLogo($fileName, $savePath, 155, 180, 'white');
    }

    /**
     * Function to resize image and expand with given data with given data using shell command. 
     * 
     * @param string $imagePath       current image path
     * @param string $savePath        path to save
     * @param int    $resizeDimension dimension to resize
     * @param int    $expandDimension dimension to expand after resize( if img is rectangle, expand it to square by giving specified backgroud (transparent/white) )
     * @param string $background      transparent/white
     * 
     * @throws ProcessFailedException
     */
    private function resizeLogo($imagePath, $savePath, $resizeDimension, $expandDimension, $background)
    {

        list($orgWidth, $orgHeight) = getimagesize($imagePath);
        if (end(explode('.', $imagePath)) == 'gif') {
            $tempFileName = substr($imagePath, 0, strrpos($imagePath, ".gif")) . "temp.gif";
            $importCommand = "gm convert '" . $imagePath . "' -coalesce '" . $tempFileName . "'";
            $importCommand .= "; gm convert -background $background -gravity center -auto-orient -size " . $orgWidth . "x" . $orgHeight . " '" . $tempFileName . "' +dither -resize " . $resizeDimension . "x" . $resizeDimension . " -extent " . $expandDimension . 'x' . $expandDimension . " '" . $savePath . "';";
            $importCommand .= " rm -f '" . $tempFileName . "'";
        } else {
            $importCommand = "gm convert -background $background -gravity center -auto-orient '" . $imagePath . "' +dither -resize " . $resizeDimension . 'x' . $resizeDimension . " -extent " . $expandDimension . 'x' . $expandDimension . " '" . $savePath . "' ";
        }
        $process = new Process($importCommand);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Function to make required folders for web settings to save (if it not exist)
     */
    private function makeWebSettingsFolder()
    {
        $uploadDirectory = FgUtility::getUploadDir() . "/";
        $clubId = $this->container->get('club')->get('id');
        $this->makeDirectory($uploadDirectory . $clubId);
        $this->makeDirectory($uploadDirectory . $clubId . '/admin');
        $websettingsFolder = FgUtility::getWebDir() . FgUtility::getUploadFilePath($clubId, 'cms_websettings');
        $this->makeDirectory($websettingsFolder);
        $defaultLogoFolder = FgUtility::getWebDir() . FgUtility::getUploadFilePath($clubId, 'cms_default_logo');
        $this->makeDirectory($defaultLogoFolder);
        $faviconsFolder = FgUtility::getWebDir() . FgUtility::getUploadFilePath($clubId, 'cms_favicons');
        $this->makeDirectory($faviconsFolder);
    }

    /**
     * Method to make direcory, if not exist
     * 
     * @param string $direcoryName direcoryName
     */
    private function makeDirectory($direcoryName)
    {
        if (!is_dir($direcoryName)) {
            mkdir($direcoryName, 0777, true);
        }
    }

    /**
     * Function to save the url favisons and default logos from the faveicon generator
     * 
     * @return object View Template Render Object
     */
    /**
     * Function to save the url favisons and default logos from the faveicon generator
     * 
     * @param type $favIconApiLocation  The location returned by the realfavicongenerator api
     * @param type $clubId              The current club id
     * 
     * @return void
     */
    private function downloadAndSaveFavicons($favIconApiLocation, $clubId)
    {
        $defaultLogoPath = realpath('') . FgUtility::getUploadFilePath($clubId, 'cms_default_logo');
        $faviconPath = realpath('') . FgUtility::getUploadFilePath($clubId, 'cms_favicons');
        $favIconFile = rand(999, 9999) . '.zip';
        $favIconSavePath = str_replace('/', "\\", ($defaultLogoPath . $favIconFile));
        $file = fopen($favIconSavePath, "w+");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $favIconApiLocation);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FILE, $file);
        curl_exec($ch);
        curl_close($ch);
        fclose($file);

        $zip = new \ZipArchive;
        if ($zip->open($favIconSavePath) === true) {
            $zip->extractTo($defaultLogoPath);
            $zip->close();
            unlink($favIconSavePath);

            //move the favicons to the correct folers
            rename($defaultLogoPath . '/favicon.ico', $faviconPath . '/favicon.ico');
            rename($defaultLogoPath . '/favicon-16x16.png', $faviconPath . '/favicon-16x16.png');
            rename($defaultLogoPath . '/favicon-32x32.png', $faviconPath . '/favicon-32x32.png');

            //move the originally uploaded logo to websettings
            rename($defaultLogoPath . '/favicon.ico', $faviconPath . '/favicon.ico');
        }

        return;
    }

    /**
     * Method to create and resize the icon generators
     * 
     * @param string $imageName    Local name of the image
     * @param string $imagePath    current image path
     * @param string $originalName The name of the image the file is to be saved
     * @param string $savePath     path the image to be saved
     * 
     * @return void
     */
    private function resizeFavicons($imageName, $imagePath, $originalName, $savePath)
    {
        $imageLocation = $imagePath . $imageName;

        if (file_exists($imageLocation)) {
            $saveName = "favicon.ico";
            if (pathinfo($imageLocation, PATHINFO_EXTENSION) != 'ico') {
                //PNG, JPG, ICO, GIF files are resized and converted to an ICO file containing 16x16, 32x32 and 48x48 icon
                $icoGenerator = new FgIcoGenerator($imageLocation, array(array(16, 16), array(32, 32), array(48, 48)));
                $icoGenerator->save_ico($savePath . $saveName);
            } else {
                //copy file from temp folder to favicon folder
                copy($imageLocation, $savePath . $saveName);
            }

            $this->getImportCommand($imageLocation, $savePath);

            //Move the original file to the location
            rename($imageLocation, $savePath . $originalName);
        }

        return;
    }

    /**
     * Method to create and resize the icon generators
     * 
     * @param string $imageLocation     Local name of the image
     * @param string $savePath          The path where the images will be saved
     * 
     * @return void
     */
    private function getImportCommand($imageLocation, $savePath)
    {
        //PNG, JPG, ICO, GIF files are resized and converted to an PNG favicon-16x16.png and a favicon-32x32.png  
        list($orgWidth, $orgHeight) = getimagesize($imageLocation);
        $importCommand = " rm -f '" . $savePath . "favicon-16x16.png' " . $savePath . "favicon-32x32.png';";
        if (pathinfo($imageLocation, PATHINFO_EXTENSION) == 'gif') {
            $tempFileName = substr($imageLocation, 0, strrpos($imageLocation, ".gif")) . "temp.gif";
            $importCommand .= "gm convert '" . $imageLocation . "' -coalesce '" . $tempFileName . "';";
            $importCommand .= "gm convert -auto-orient -size " . $orgWidth . "x" . $orgHeight . " '" . $tempFileName . "' +dither -resize 16x16 'favicon-16x16.png';";
            $importCommand .= "gm convert -auto-orient -size " . $orgWidth . "x" . $orgHeight . " '" . $tempFileName . "' +dither -resize 32x32 'favicon-32x32.png';";
            $importCommand .= " rm -f '" . $tempFileName . "';";
        } else {
            $importCommand = "gm convert -auto-orient '" . $imageLocation . "' +dither -resize 16x16 '" . $savePath . "favicon-16x16.png';";
            $importCommand .= "gm convert -auto-orient '" . $imageLocation . "' +dither -resize 32x32 '" . $savePath . "favicon-32x32.png';";
        }

        $process = new Process($importCommand);
        $process->run();
    }
}
