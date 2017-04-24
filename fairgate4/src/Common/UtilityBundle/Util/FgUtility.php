<?php

/**
 * This file is part of the Symfony package.
 *
 * (c) pit solutions <pitsolutions.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Common\UtilityBundle\Util;

use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\File\File;
use Common\UtilityBundle\Util\FgSettings;
use Symfony\Component\Yaml\Parser;
use Common\FilemanagerBundle\Util\FileChecking;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

/**
 * Catch club identifier from the request and set club details in the request context.
 */
class FgUtility
{

    public static $ROOT_PATH;

    /**
     * Check file name exists in the uploading folder .If exists , returns new file name appending anumber(windows style) ...
     *
     * @param String $uploadPath the upload path
     * @param String $fileName   the filename
     *
     * @return String $fileName
     */
    public static function getFilename($uploadPath, $fileName)
    {
        $cpt = 1;
        $ext = (false === $pos = strrpos($fileName, '.')) ? '' : substr($fileName, $pos);
        $fileNameDestination = (false === $pos = strrpos($fileName, '.')) ? $fileName : substr($fileName, 0, $pos);
        //strip special characters from filename
        $removeChars = array("'", '"', '!', '$', '`', '~', '%', '^', '*', '+', '=', '|', "\\", '{', '}', '[', ']', ':', ';', '<', '>', '?', '/', ' ', '#', '&', ',');
        $fileNameSplCharsRemoved = str_replace($removeChars, '', $fileNameDestination);
        $fileName = $fileNameSplCharsRemoved . $ext;
        while (file_exists($uploadPath . '/' . $fileName)) {
            $fileName = $fileNameSplCharsRemoved . '(' . $cpt . ')' . $ext;
            $cpt++;
        }

        return $fileName;
    }

    /**
     * Get country names based on selected culture
     *
     * @return Array $countryList
     */
    public static function getCountryList()
    {
        $countryList = Intl::getRegionBundle()->getCountryNames();

        return $countryList;
    }

    /**
     * Get Club Language Fullnames based on selected culture
     *
     * @param Array $clubLanguages the shortnames of languages
     *
     * @return Array $countryList with shortnames => Fullnames  as key value pair
     */
    public static function getClubLanguageNames($clubLanguages)
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $rowLanguages = array();
        foreach ($clubLanguages as $shortName) {
            $rowLanguages[$shortName] = $languages[$shortName];
        }

        return $rowLanguages;
    }

    /**
     * Get Mysql Formated date
     *
     * @param string $datevalue the datevalue
     *
     * @return string
     */
    public static function getMysqlDate($datevalue)
    {
        $datevalue = strftime('%Y-%m-%d', strtotime($datevalue));

        return $datevalue;
    }

    /**
     * function to resize and  to copy the images
     *
     * @param url    $uploadPath the path of image origin
     * @param string $fileName   the filename
     * @param string $fieldType  the type
     */
    public static function getResizeImages($container, $uploadPath, $fileName, $fieldType = 'contact')
    {
        // For contact specific images -team picture,community picture and company logo.
        if ($fieldType == 'contact') {
            FgUtility::resizeContactImages($container, $uploadPath, $fileName);
        } else if ($fieldType == 'communication') {
            FgUtility::resizeCommunicationImages($container, $uploadPath, $fileName);
        }
    }

    /**
     * Get uploads folder
     *
     * @return String Upload folder path
     */
    public static function getUploadDir()
    {
        $path = str_replace('\\', '/', realpath(''));

        return $path . '/uploads';
    }

    /**
     * Get web folder
     *
     * @return String web folder path
     */
    public static function getWebDir()
    {
        return str_replace('\\', '/', realpath(''));
    }

    /**
     * Get country names based on our own order
     *
     * @return Array $countryList
     */
    public static function getCountryListchanged()
    {
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $country = array('CH' => $countryList['CH'], 'DE' => $countryList['DE'], 'AT' => $countryList['AT'], 'LI' => $countryList['LI']);
        unset($countryList['CH']);
        unset($countryList['DE']);
        unset($countryList['AT']);
        unset($countryList['LI']);

        return $country + $countryList;
    }
    /**
     * Get multidimentional array and flatten it
     *
     * @return Array (Flatten)
     */

    /**
     * Get multidimentional array and flatten it
     *
     * @param array $array  the array of multidimensional
     * @param array $result the result array
     *
     * @return array $result
     */
    public static function getArrayFlatten($array, $result = array(), $isValCheck = false)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = FgUtility::getArrayFlatten($value, $result, $isValCheck);
            } else if ($isValCheck) {
                if ($value) {
                    $result[] = $value;
                }
            } else {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Convert all applicable characters to HTML entities and escapes a string for use in a mysql_query
     * @param type $data
     * @param type $conn
     * @param type $quote
     * @param type $quoteFlag
     * @return type
     */
    public static function getSecuredData($data, $conn, $quote = false, $quoteFlag = true)
    {
        $strippedData = str_replace('<script', '<scri&nbsp;pt', $data);
        if ($strippedData != '' && $quoteFlag == true) {
            $strippedData = $conn->quote($strippedData);
        }
        if (!$quote) {
            $strippedData = str_replace("'", '', $strippedData);
        }
        return $strippedData;
    }

    /**
     * Convert all applicable characters to HTML entities and escapes a string for use in a mysql_query
     * @param string $data Data string
     * @param object $conn Connection object
     * 
     * @return String
     */
    public static function getSecuredDataString($data, $conn, $quote = false)
    {
        $strippedData = str_replace('<script', '<scri&nbsp;pt', $data);
        if ($strippedData != '') {
            $strippedData = $conn->quote($strippedData);
        }
        if (!$quote) {
            $strippedData = substr($strippedData, 1);
            $strippedData = substr_replace($strippedData, "", -1);
        }

        return $strippedData;
    }

    /**
     * Get all Language Fullnames based on selected culture
     *
     * @return Array $countryList with shortnames => Fullnames  as key value pair
     */
    public static function getAllLanguageNames()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $rowLanguages = array();
        $clubLanguages = array('sq', 'bs', 'bg', 'hr', 'cs', 'da', 'nl', 'en', 'fr', 'de', 'el', 'it', 'mk', 'no', 'pl', 'pt', 'rm', 'sr', 'sk', 'sl', 'es', 'sv', 'tr');
        foreach ($clubLanguages as $shortName) {
            $rowLanguages[$shortName] = $languages[$shortName];
        }
        asort($rowLanguages);
        return $rowLanguages;
    }

    /**
     * For find the search fields
     * @param type $fields fields
     *
     * @return string
     */
    public function getSearchFields($fields)
    {
        $searchColumns[] = 'fc.title';
        $searchColumns[] = "fc.email";
        $searchColumns[] = "fc.url_identifier";
        $searchColumns[] = "fc.website";
        return $searchColumns;
    }

    /**
     * FUNCTION TO GET THUMBNAIL AND VIDEO iframe CODE
     *
     * @param type $videoSerialized
     *
     * @return type
     */
    public static function getVideoUrlAndThumb($videoSerialized)
    {

        $videoDetails = unserialize(base64_decode($videoSerialized));
        if ($videoDetails['video_id'] != '') {
            if ($videoDetails['video_type'] == 'youtube') {
                $thumbPath = 'http://i3.ytimg.com/vi/' . $videoDetails['video_id'] . '/0.jpg';
                $url = '<iframe width="640" height="390" src="http://www.youtube.com/embed/' . $videoDetails['video_id'] . '?rel=0&wmode=transparent" frameborder="0" allowfullscreen></iframe>';
                $embed_url = 'http://www.youtube.com/embed/' . $videoDetails['video_id'] . '?rel=0&wmode=transparent';
            } else if ($videoDetails['video_type'] == 'vimeo') {
                $vimeo_details = self::getVimeoInfo($videoDetails['video_id']);
                $thumbPath = $vimeo_details['thumbnail_medium'];
                $url = '<iframe src="http://player.vimeo.com/video/' . $videoDetails['video_id'] . '" width="640" height="390" frameborder="0"></iframe>';
                $embed_url = 'http://player.vimeo.com/video/' . $videoDetails['video_id'];
            }
        } else {
            $thumbPath = "/templates/global/images/content_block/no_image/img_150x100.png";
            $url = '';
        }

        return array('thumbPath' => $thumbPath, 'url' => $url, 'embedUrl' => $embed_url);
    }

    /**
     * FUNCTION TO GET VIMEO VIDEO INFO FROM VIDEO ID
     * @param type $id
     * @return type
     */
    public static function getVimeoInfo($id)
    {
        if (!function_exists('curl_init'))
            die('CURL is not installed!');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://vimeo.com/api/v2/video/$id.php");
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $output = unserialize(curl_exec($ch));
        $output = $output[0];
        curl_close($ch);

        return $output;
    }
     /**
     * Function to get the base url
     * 
     * @param object $container Container Object
     * @param int    $clubId    Club id
     * @param type   $mode      Newsletter mode
     * 
     * @return string $baseUrl BaseUrl
     */
    public static function getBaseUrlForFavIcon($container, $clubId = '', $mode = '') {

        $em = $container->get('doctrine')->getManager();
        if ($clubId) {
            $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
            if ($checkClubHasDomain && ($mode == '')) {
                $returnDomain = $checkClubHasDomain['domain'];
            } else {
                $returnDomain = $container->getParameter('base_url');
            }
        } else {

            $request = $container->get('request_stack')->getCurrentRequest();
            if ($request->isSecure()) {
                $returnDomain = 'https://' . $request->getHttpHost();
            } else {
                $returnDomain = 'http://' . $request->getHttpHost();
            }
        }

        return $returnDomain;
    }

    /**
     * Function to get the base url
     * 
     * @param object $container Container Object
     * @param int    $clubId    Club id
     * @param type   $mode      Newsletter mode
     * 
     * @return string $baseUrl BaseUrl
     */
    public static function getBaseUrl($container, $clubId = '', $mode = '')
    {

        $em = $container->get('doctrine')->getManager();
        if ($clubId) {
            $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
            if ($checkClubHasDomain && ($mode == '')) {
                $returnDomain = $checkClubHasDomain['domain'];
            } else {
                $returnDomain = $container->getParameter('base_url');
            }
        } else {

            $request = $container->get('request_stack')->getCurrentRequest();
            $returnDomain = 'http://' . $request->getHttpHost();
        }

        return $returnDomain;
    }

    public static function getRootPath($container)
    {
        return $rootPath = $container->getParameter('kernel.root_dir') . '/../../web';
    }

    /**
     * Function to resize images of contact.
     *
     * @param object $container  The container object
     * @param int    $fieldId    The attribute ids of the image fields
     * @param url    $uploadPath The path of image origin
     * @param string $fileName   The filename
     */
    public static function resizeContactImages($container, $uploadPath, $fileName)
    {
        $uploadPath = self::$ROOT_PATH . '/../../web/' . $uploadPath;
        //largeImageWidth is 640
        $largeImageWidth = $container->getParameter('largeImageWidth');

        // Get image details.
        $imagePath = $uploadPath . "/original/" . $fileName;
        list($width, $height) = getimagesize($imagePath);

        //resize images in width_150 folder to 150, if width and height of image is > 150 
        $savePath = $uploadPath . "/width_150/" . $fileName;
        FgUtility::resizeFolderImages($imagePath, $savePath, $width, $height, 150, 150);
        //resize images in width_300 folder to 300, if width and height of image is > 300 
        $savePath = $uploadPath . "/width_300/" . $fileName;
        $container->get('fg.avatar')->createUploadDirectories($uploadPath . "/width_300");
        FgUtility::resizeFolderImages($imagePath, $savePath, $width, $height, 300, 300);
        //resize images in width_580 folder to 580, if width and height of image is > 580
        $savePath = $uploadPath . "/width_580/" . $fileName;
        $container->get('fg.avatar')->createUploadDirectories($uploadPath . "/width_580");
        FgUtility::resizeFolderImages($imagePath, $savePath, $width, $height, 580, 580);

        //resize images in width_65 folder, if width > 65 and height > 45
        $savePath = $uploadPath . "/width_65/" . $fileName;
        FgUtility::resizeFolderImages($imagePath, $savePath, $width, $height, 65, 45);

        //resize images in original folder to 640, if width and height of image is > 640 
        //original folder should be resized only at the end. Otherwise exit orientation will execute more than one time
        FgUtility::resizeFolderImages($imagePath, $imagePath, $width, $height, $largeImageWidth, $largeImageWidth);
    }

    /**
     * Method to resize images to specific dimensions according to conditions and save to the folder.
     * 
     * @param string $imagePath    current image path
     * @param string $savePath     path the image to be saved
     * @param int    $imageWidth   current image width
     * @param int    $imageHeight  current image height
     * @param int    $folderWidth  maximum width, the image to be saved in the folder
     * @param int    $folderHeight maximum height the image to be saved in the folder
     */
    public static function resizeFolderImages($imagePath, $savePath, $imageWidth, $imageHeight, $folderWidth, $folderHeight)
    {
        // When width is greater than folderWidth, resize to folderWidth in original folder
        if (($imageWidth > $folderWidth) && ($imageHeight > $folderHeight)) {
            //if width < height, resize width to folderWidth, otherwise resize height to folderWidth
            if ($imageWidth > $imageHeight) {
                //resize height to folderWidth and width to (w/h * folderWidth)
                $resizeWidth = (($imageWidth / $imageHeight) * $folderHeight);
                FgUtility::resizeImage($imagePath, $savePath, $resizeWidth, $folderHeight);
            } else {
                //resize width to folderWidth and height to (h/w * folderWidth)
                $resizeHeight = (($imageHeight / $imageWidth) * $folderWidth);
                FgUtility::resizeImage($imagePath, $savePath, $folderWidth, $resizeHeight);
            }
        } else {
            //simple copy the images to particular folders
            //copy($imagePath, $savePath);
            //resize to same width and height of the image for checking exif orientation
            FgUtility::resizeImage($imagePath, $savePath, $imageWidth, $imageHeight);
        }
    }

    /**
     * Function to resize images of communication.
     *
     * @param object $container  The container object
     * @param url    $uploadPath The path of image origin
     * @param string $fileName   The filename
     */
    public static function resizeCommunicationImages($container, $uploadPath, $fileName)
    {
        $templateLargeWidth = $container->getParameter('templatelargeImageWidth');
        // Get image details.
        $imagePath = $uploadPath . "/" . $fileName;
        list($width, $height, $type, $attr) = getimagesize($imagePath);

        if (($width > $templateLargeWidth)) {
            FgUtility::resizeImage($imagePath, $imagePath, $templateLargeWidth);
        }
    }

    /**
     * Function to resize image with given data.
     *
     * @param string $imagePath Original image path
     * @param string $savePath  Save image path
     * @param int $width        Resize width
     * @param int $height       Resize height
     */
    public static function resizeClubLogo($imagePath, $savePath, $maxWidth, $maxHeight)
    {

        //need to check if image

        list($orgWidth, $orgHeight, $type, $attr) = getimagesize($imagePath);
        $resultRatio = $maxWidth / $maxHeight;

        //if width is greater resize wrt to width
        if (($orgWidth / $orgHeight) > $resultRatio) {
            $newWidth = $maxWidth;
            $newHeight = ($orgHeight * $newWidth) / $orgWidth;
        } else {
            $newHeight = $maxHeight;
            $newWidth = ($orgWidth * $newHeight) / $orgHeight;
        }
        //die('>>>>>>>>'.$orgWidth.' - '.$orgHeight.' <<->> '.$newWidth.' - '.$newHeight .' : '.$resultRatio .' : '.($orgWidth/$orgHeight));
        //die('>>>>>>>>'.$imagePath.' - '.$savePath);

        FgUtility::resizeImage($imagePath, $savePath, $newWidth, $newHeight);
        unlink($imagePath);
    }

    /**
     * Function to resize image with given data with given data using shell command.
     *
     * @param string $imagePath Original image path
     * @param string $savePath  Save image path
     * @param int $width        Resize width
     * @param int $height       Resize height
     */
    public static function resizeImage($imagePath, $savePath, $width, $height = '')
    {
        list($orgWidth, $orgHeight, $type, $attr) = getimagesize($imagePath);
        if ($height == '') {
            $height = $orgHeight * ($width / $orgWidth);
        }
        //if width/height is greater than original width/heigh (no resizing needed in that case), resize to same width and height of the image for checking exif orientation
        if($height > $orgHeight || $width > $orgWidth) {
            $height = $orgHeight;
            $width = $orgWidth;
        }
        if (end(explode('.', $imagePath)) == 'gif') {
            $tempFileName = substr($imagePath, 0, strrpos($imagePath, ".gif")) . "temp.gif";
            $importCommand = "gm convert '" . $imagePath . "' -coalesce '" . $tempFileName . "'";
            $importCommand .= "; gm convert -auto-orient -size " . $orgWidth . "x" . $orgHeight . " '" . $tempFileName . "' +dither -resize " . $width . "x" . $height . " '" . $savePath . "';";
            $importCommand .= " rm -f '" . $tempFileName . "'";
        } else {
            $importCommand = "gm convert -auto-orient '" . $imagePath . "' +dither -resize " . $width . 'x' . $height . " '" . $savePath . "' ";
        }

        $process = new Process($importCommand);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * Function to resize image with given data and With Height.
     *
     * @param string $imagePath Original image path
     * @param string $savePath  Save image path
     * @param int $maxWidth        Resize width
     * @param int $maxHeight       Resize height
     */
    public static function getResizeDimension($imagePath, $maxWidth, $maxHeight)
    {
        //need to check if image

        $newHeight = $newWidth = 0;

        if (file_exists($imagePath)) {
            list($orgWidth, $orgHeight, $type, $attr) = getimagesize($imagePath);
            $resultRatio = $maxWidth / $maxHeight;

            if (($orgWidth > $orgHeight && $orgWidth < $maxWidth) || ( $orgWidth < $orgHeight && $orgHeight < $maxHeight))
                return array(intval($orgWidth), intval($orgHeight));

            //if width is greater resize wrt to width
            if (($orgWidth / $orgHeight) > $resultRatio) {
                $newWidth = $maxWidth;
                $newHeight = ($orgHeight * $newWidth) / $orgWidth;
                if ($newHeight < (2 / 3) * $maxHeight) {    //FAIR-1654
                    $newHeight = (2 / 3) * $maxHeight;
                    $newWidth = ($orgWidth / $orgHeight) * $newHeight;
                }
            } else {
                $newHeight = $maxHeight;
                $newWidth = ($orgWidth * $newHeight) / $orgHeight;
            }
            /* DO not resize images to alrger dimaension in any case */
            if ($newWidth > $orgWidth || $newHeight > $orgHeight) {
                $newWidth = $orgWidth;
                $newHeight = $orgHeight;
            }
        }
        return array(intval($newWidth), intval($newHeight));
    }

    /**
     * function to get the size formats
     *
     * @param int $bytes
     *
     * @return string
     */
    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2, FgSettings::getDecimalMarker(), FgSettings::getThousandSeperator()) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2, FgSettings::getDecimalMarker(), FgSettings::getThousandSeperator()) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2, FgSettings::getDecimalMarker(), FgSettings::getThousandSeperator()) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * function to get the size in bytes
     *
     * @param int $mbformat
     *
     * @return string
     */
    public static function mbtobyteConversion($mbformat)
    {
        $bytes = 1048576 * $mbformat;
        return $bytes;
    }

    /**
     * Function to get Logo
     *
     * @param int $clubId Club Id
     *
     * @return string
     */
    public static function getClubLogo($clubId, $entityManager)
    {
        if ($clubId != 0) {
            $fedIcon = $entityManager->getRepository('CommonUtilityBundle:FgClubSettings')->getFederationIcon($clubId);
            $iconPath = '/fgassets/global/img/fedicon.png';
            $filepath = 'uploads/' . $clubId . '/admin/federation_icon/';
            if (file_exists($filepath . $fedIcon) && $fedIcon != '') {
                $iconPath = "/uploads/$clubId/admin/federation_icon/" . $fedIcon;
            }

            return $iconPath;
        }
    }

    /**
     * function to get ducument icon based on doc type
     *
     * @param \Clubadmin\DocumentsBundle\Controller\type $filename
     * @return string
     * @param type $filename
     *
     * @return string
     */
    public function getDocumentIcon($filename, $getImage = false)
    {
        $filename = strtolower(end(explode('.', $filename)));
        $fileTypes = array();
        $fileTypes['docTypes'] = array('doc', 'docx', 'odt');
        $fileTypes['pdfTypes'] = array('pdf');
        $fileTypes['excelTypes'] = array('xls', 'xlsx');
        $fileTypes['powerType'] = array('ppt', 'pptx');
        $fileTypes['archiveType'] = array('zip', 'rar', 'tar', 'gz', '7z');
        $fileTypes['audioType'] = array('mp3', 'aac', 'amr', 'm4a', 'm4p', 'wma');
        $fileTypes['videoType'] = array('mp4', 'flv', 'mkv', 'avi', 'webm', 'vob', 'mov', 'wmv', 'm4v');
        $fileTypes['webTypes'] = array('html', 'htm');
        $fileTypes['textTypes'] = array('txt', 'rtf', 'log');
        $fileTypes['imgTypes'] = array('jpg', 'jpeg', 'png', 'gif', 'ico', 'bmp');
        if (in_array($filename, $fileTypes['docTypes'])) {
            return "fg-file-word";
        } else if (in_array($filename, $fileTypes['pdfTypes'])) {
            return 'fg-file-pdf';
        } else if (in_array($filename, $fileTypes['textTypes'])) {
            return 'fg-file-text';
        } else if (in_array($filename, $fileTypes['excelTypes'])) {
            return 'fg-file-excel';
        } else if (in_array($filename, $fileTypes['powerType'])) {
            return 'fg-file-powerpoint';
        } else if (in_array($filename, $fileTypes['archiveType'])) {
            return 'fg-file-zip';
        } else if (in_array($filename, $fileTypes['audioType'])) {
            return 'fg-file-sound';
        } else if (in_array($filename, $fileTypes['videoType'])) {
            return 'fg-file-video';
        } else if (in_array($filename, $fileTypes['webTypes'])) {
            return 'fg-file-code';
        } else if (in_array($filename, $fileTypes['imgTypes']) && $getImage == true) {
            return 'fg-file-photo';
        } else {
            return 'fg-file';
        }
    }

    /**
     * Alternative for php array_column function.
     * Adding our own function as our PHP version does not support array_column.
     *
     * @param  Array   $input
     * @param  string  $columnKey
     * @param  integer $indexKey
     *
     * @return Array
     */
    public function getArrayColumn(array $input, $columnKey, $indexKey = null)
    {
        $array = array();
        foreach ($input as $value) {
            if (!isset($value[$columnKey])) {
                trigger_error("Key \"$columnKey\" does not exist in array");
                return false;
            }
            if (is_null($indexKey)) {
                $array[] = $value[$columnKey];
            } else {
                if (!isset($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not exist in array");
                    return false;
                }
                if (!is_scalar($value[$indexKey])) {
                    trigger_error("Key \"$indexKey\" does not contain scalar value");
                    return false;
                }
                $array[$value[$indexKey]] = $value[$columnKey];
            }
        }
        return $array;
    }

    /**
     * Method for getting image path
     * @param type $path          path
     * @param type $fileName      filename
     * @return string
     */
    public static function getFilePath($path, $fileName)
    {
        return $path . $fileName;
    }

    /**
     * Function to get contact's image (company logo or profile picture)
     *
     * @param string $rootPath   Web Root Path
     * @param int    $clubId     Contact Club Id
     * @param string $imageName  Image Name
     * @param string $folder     Folder
     * @param string $type       Type
     * @param string $uploadPath UploadPath (If any Change)
     *
     * @return string $returnPath ImagePath
     */
    public static function getContactImage($rootPath, $clubId, $imageName, $folder = '', $type = '', $uploadPath = '')
    {
        $uploadPath = ($uploadPath != '') ? $uploadPath : 'uploads/' . $clubId . '/contact';
        $folderPath = ($folder != '') ? $folder . '/' : '';

        if (($imageName != '') && (file_exists($rootPath . '/' . $uploadPath . '/' . $folderPath . $imageName))) {
            $returnPath = '/' . $uploadPath . '/' . $folderPath . $imageName;
        } else {
            $returnPath = '';
        }

        return $returnPath;
    }

    /**
     * Function to dynamically generate urls with different club url_identifiers
     *
     * @param object $container     Container object
     * @param string $urlIdentifier Club url identifier
     * @param string $routeName     Symfony route name
     * @param array  $parameters
     *
     * @return string $url Generated url
     */
    public static function generateUrl($container, $urlIdentifier = '', $routeName = '', $parameters = array())
    {
        $baseUrl = self::getBaseUrl($container);
        if ($urlIdentifier != '')
            $parameters['url_identifier'] = $urlIdentifier;
        $routePath = $container->get('router')->generate($routeName, $parameters, false);
        $url = $baseUrl . $routePath;

        return $url;
    }

    /**
     * Function to dynamically generate urls with different club url_identifiers in case of club has hosts
     * 
     * @param object $container          Container object
     * @param string $urlIdentifier      Club url identifier
     * @param string $routeName          Symfony route name
     * @param array  $checkClubHasDomain Contacins club domain details
     * 
     * @return string $url Generated url
     */
    public static function generateUrlForHost($container, $urlIdentifier, $routeName, $checkClubHasDomain, $parameters = array())
    {
        $clubId = $container->get('club')->get('id');
        $domainFlag = 0;
        if ($checkClubHasDomain) {
            $domainFlag = 1;
            $baseUrl = $checkClubHasDomain['domain'];
        } else {
            $baseUrl = self::getBaseUrl($container, $clubId);
        }
        $routePath = $container->get('router')->generate($routeName, $parameters, false);
        if ($domainFlag) {
            $routePath = str_replace('/' . $urlIdentifier . '/', '/', $routePath);
        }
        $url = $baseUrl . $routePath;

        return $url;
    }

    /**
     * Function to dynamically generate urls with different club url_identifiers in case of club has hosts
     * 
     * @param object $container          Container object
     * @param string $routeName          Symfony route name
     * @param array  $parameters         Routing parameters  
     * 
     * @return string $url Generated url
     */
    public static function generateUrlForSharedClub($container, $routeName, $createdClub = 0, $parameters = array())
    {
        if (empty($createdClub)) {
            $clubId = $container->get('club')->get('id');
        } else {
            $clubId = $createdClub;
        }
        $club = $container->get('club');
        $currClubId = $club->get('id');
        $em = $container->get('doctrine')->getManager();
        $baseUrl = self::getBaseUrl($container);
        $routePath = $container->get('router')->generate($routeName, $parameters, false);
        if ($currClubId != $clubId) {
            $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            $clubUrl = $clubObj->getUrlIdentifier();
            $urlIdentifier = $club->get('url_identifier');
            $routePath = str_replace('/' . $urlIdentifier . '/', '/', $routePath);
            $routePath = '/' . $clubUrl . $routePath;
        }
        $url = $baseUrl . $routePath;

        return $url;
    }

    /**
     * Method to move attachments of a message
     *
     * @param array $uploadedAttachmentNames original names of uploaded files
     * @param array $uploadedAttachments     uploaded attachment names in temp folder (temporary names)
     *
     * @return array array of filenames uploaded (name after replace single quotes and appending 1,2,3 ..)
     */
    public static function moveMessageAttachments($uploadedAttachmentNames, $uploadedAttachments, $container)
    {
        //move the files to club folder
        $clubDirectory = 'uploads/' . $container->get('club')->get('id');
        if (!is_dir($clubDirectory)) {
            mkdir($clubDirectory, 0777, true);
        }
        $clubMessageDirectory = $clubDirectory . '/users/messages';
        if (!is_dir($clubMessageDirectory)) {
            mkdir($clubMessageDirectory, 0777, true);
        }
        $uploadedFileNames = array();
        foreach ($uploadedAttachments as $key => $attachment) {
            //when the image is submitted with no change the image will not be there in the temp folder
            //beacuse it will already been moved common condition on edit
            if (file_exists('uploads/temp/' . $attachment)) {
                $attachmentObj = new File('uploads/temp/' . $attachment, false);
                $attachmentName = FgUtility::getFilename($clubMessageDirectory, $uploadedAttachmentNames[$key]);
                $attachmentObj->move($clubMessageDirectory, $attachmentName);
                $uploadedFileNames[] = $attachmentName;
            }
        }

        return $uploadedFileNames;
    }

    /**
     * Method to get array of default languages
     *
     * @param object $container container-object
     *
     * @return array
     */
    public function getDefaultLanguages($container)
    {
        $resultArray = array();
        $defaultLanguages = $container->getParameter('defaultLanguages');
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        foreach ($defaultLanguages as $defaultLanguage) {
            $resultArray[$defaultLanguage] = $languages[$defaultLanguage];
        }
        asort($resultArray);
        return $resultArray;
    }

    /**
     * Method to generate the tab details array inorder to generate the tabs in page title bar
     * @param object       $container        container object
     * @param array        $tabs             tabs array
     * @param int          $offset           offset
     * @param int          $contact          contact id or sponsor  id
     * @param array or int $contCountDetails count details array or count value
     * @param string       $activeTab        current active tab
     * @param contact type $type             contact type
     *
     * @return array
     */
    public static function getTabsArrayDetails($container, $tabs, $offset, $contact, $contCountDetails, $activeTab, $type)
    {
        $finalTabsArray = array();
        foreach ($tabs as $key => $value) {

            $tabDetails = FgUtility::getTabDetailsData($container, $value, $offset, $contact, $contCountDetails, $type);
            $finalTabsArray[$key]['text'] = $tabDetails['title'];
            $finalTabsArray[$key]['url'] = $tabDetails['url'];
            $finalTabsArray[$key]['name'] = $tabDetails['name'];
            $finalTabsArray[$key]['activeClass'] = ($value == $activeTab) ? "active" : '';
            $finalTabsArray[$key]['count'] = $tabDetails['count'];
            $finalTabsArray[$key]['tabtype'] = $value;
            if ($type == "service") {
                $finalTabsArray[$key]['id'] = $tabDetails['id'];
                $finalTabsArray[$key]['countId'] = $tabDetails['countId'];
                $finalTabsArray[$key]['listId'] = $tabDetails['listId'];
            } elseif ($type == "confirmations") {
                $finalTabsArray[$key]['dataTabname'] = $tabDetails['dataTabname'];
                $finalTabsArray[$key]['dataDatatableid'] = $tabDetails['dataDatatableid'];
            } else if ($type == "fedapplication") {
                $finalTabsArray[$key]['dataTabname'] = $tabDetails['dataTabname'];
                $finalTabsArray[$key]['dataDatatableid'] = $tabDetails['dataDatatableid'];
            } elseif ($type == "importSponsor" || $type == "importContact") {
                $finalTabsArray[$key]['data_url'] = $tabDetails['data_url'];
            }
        }

        return $finalTabsArray;
    }

    /**
     * Method to build the tab data for each tab
     *
     * @param object       $container        container object
     * @param string       $tab              tab value
     * @param int          $offset           offset
     * @param int          $contact          contact id or sponsor  id
     * @param array or int $contCountDetails count details array or count value
     * @param contact type $type             contact type
     *
     * @return array
     */
    public static function getTabDetailsData($container, $tab, $offset, $contact, $contCountDetails, $type)
    {
        $tabDetailsArray = array();
        switch ($tab) {
            case 'overview':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_OVERVIEW');
                $tabDetailsArray['url'] = ($type == "contact" || $type == "archive") ? $container->get('router')->generate('render_contact_overview', array('offset' => $offset, 'contact' => $contact)) : (($type == "sponsor") ? $container->get('router')->generate('render_sponsor_overview', array('offset' => $offset, 'sponsor' => $contact)) : $container->get('router')->generate('club_overview', array('offset' => $offset, 'clubId' => $contact)));
                $tabDetailsArray['name'] = "fg-dev-overview-tab";
                $tabDetailsArray['count'] = '';
                break;

            case 'data':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_DATA');
                $tabDetailsArray['url'] = ($type == "contact" || $type == "archive" || $type == "formerfederationmember" ) ? $container->get('router')->generate('contact_data', array('offset' => $offset, 'contact' => $contact)) : (($type == "sponsor" || $type == "archivedsponsor") ? $container->get('router')->generate('sponsor_contact_data', array('offset' => $offset, 'contact' => $contact)) : $container->get('router')->generate('club_data', array('offset' => $offset, 'clubid' => $contact)));
                $tabDetailsArray['name'] = "fg-dev-data-tab";
                $tabDetailsArray['count'] = '';
                break;

            case 'connection':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_CONNECTION');
                $tabDetailsArray['url'] = ($type == "contact") ? $container->get('router')->generate('contact_connection', array('offset' => $offset, 'contact' => $contact)) : $container->get('router')->generate('sponsor_connection', array('offset' => $offset, 'contact' => $contact));
                $tabDetailsArray['name'] = "fg-dev-connection-tab";
                $tabDetailsArray['count'] = $contCountDetails['connectionCount'];
                break;

            case 'assignment':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_ASSIGNMENTS');
                $tabDetailsArray['url'] = ($type == "contact") ? $container->get('router')->generate('contact_assignments', array('offset' => $offset, 'contact' => $contact)) : $container->get('router')->generate('club_assignments', array('offset' => $offset, 'clubid' => $contact));
                $tabDetailsArray['name'] = "fg-dev-assignment-tab";
                $tabDetailsArray['count'] = $contCountDetails['asgmntsCount'];
                break;

            case 'note':
                $tabDetailsArray['title'] = $container->get('translator')->trans('NOTES');
                $tabDetailsArray['url'] = (($type == "contact") || ($type == "archive") || ($type == "formerfederationmember")) ? $container->get('router')->generate('contact_note', array('offset' => $offset, 'contactid' => $contact)) : ((($type == "sponsor") || ($type == "archivedsponsor")) ? $container->get('router')->generate('sponsor_note', array('offset' => $offset, 'contactid' => $contact)) : $container->get('router')->generate('club_note', array('offset' => $offset, 'clubid' => $contact)));
                $tabDetailsArray['name'] = "fg-dev-notes-tab";
                $tabDetailsArray['count'] = $contCountDetails['notesCount'];
                break;

            case 'userright':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_USERRIGHTS');
                $tabDetailsArray['url'] = $container->get('router')->generate('contact_user_rights', array('offset' => $offset, 'contact' => $contact));
                $tabDetailsArray['name'] = "fg-dev-userrights-tab";
                $tabDetailsArray['count'] = '';
                break;

            case 'document':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_DOCUMENTS');
                $tabDetailsArray['url'] = ($type == "contact") ? $container->get('router')->generate('contact_documents', array('offset' => $offset, 'contact' => $contact)) : (($type == "sponsor") ? $container->get('router')->generate('sponsor_documents', array('offset' => $offset, 'contact' => $contact)) : $container->get('router')->generate('club_documents', array('offset' => $offset, 'clubId' => $contact)));
                $tabDetailsArray['name'] = "fg-dev-documents-tab";
                $tabDetailsArray['count'] = $contCountDetails['documentsCount'];
                break;

            case 'log':
                $tabDetailsArray['title'] = $container->get('translator')->trans('LOG');
                $tabDetailsArray['url'] = ($type == "contact" || $type == "archive" || $type == "formerfederationmember") ? $container->get('router')->generate('log_listing', array('offset' => $offset, 'contact' => $contact)) : ((($type == "sponsor") || ($type == "archivedsponsor")) ? $container->get('router')->generate('sponsor_log_listing', array('offset' => $offset, 'contact' => $contact)) : $container->get('router')->generate('club_log', array('offset' => $offset, 'clubId' => $contact)));
                $tabDetailsArray['name'] = "fg-dev-log-tab";
                $tabDetailsArray['count'] = '';
                break;

            case 'services':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_SEVICES');
                $tabDetailsArray['url'] = $container->get('router')->generate('services_listing', array('offset' => $offset, 'contact' => $contact));
                $tabDetailsArray['name'] = "fg-dev-services-tab";
                $tabDetailsArray['count'] = $contCountDetails['servicesCount'];
                break;

            case 'ads':
                $tabDetailsArray['title'] = $container->get('translator')->trans('PANEL_TABS_ADS');
                $tabDetailsArray['url'] = $container->get('router')->generate('sponsor_ads', array('offset' => $offset, 'contact' => $contact));
                $tabDetailsArray['name'] = "fg-dev-ads-tab";
                $tabDetailsArray['count'] = $contCountDetails['adsCount'];
                break;

            case 'change_tab':
                $tabDetailsArray['title'] = $container->get('translator')->trans('CHANGE_TAB_TEXT');
                $tabDetailsArray['url'] = $container->get('router')->generate('confirmation_changes', array('type' => 'changes'));
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = $contCountDetails;
                break;

            case 'change_log':
                $tabDetailsArray['title'] = $container->get('translator')->trans('LOG_TAB_TEXT');
                $tabDetailsArray['url'] = $container->get('router')->generate('confirmation_changes', array('type' => 'log'));
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = "";
                break;

            case 'mutations_tab':
                $tabDetailsArray['title'] = $container->get('translator')->trans('MUTATION_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-list-table-div";
                $tabDetailsArray['name'] = "confirmations-list-table";
                $tabDetailsArray['count'] = $contCountDetails;
                $tabDetailsArray['dataTabname'] = "list";
                $tabDetailsArray['dataDatatableid'] = "confirmations-list-table";
                break;
            case 'mutations_log':
                $tabDetailsArray['title'] = $container->get('translator')->trans('MUTATION_LOG_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-log-table-div";
                $tabDetailsArray['name'] = "confirmations-log-table";
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['dataTabname'] = "log";
                $tabDetailsArray['dataDatatableid'] = "confirmations-log-table";
                break;

            case 'creations_tab':
                $tabDetailsArray['title'] = $container->get('translator')->trans('CREATION_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-list-table-div";
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = $contCountDetails;
                $tabDetailsArray['dataTabname'] = "list";
                $tabDetailsArray['dataDatatableid'] = "confirmations-list-table";
                break;

            case 'creations_log':
                $tabDetailsArray['title'] = $container->get('translator')->trans('CREATION_LOG_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-log-table-div";
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['dataTabname'] = "log";
                $tabDetailsArray['dataDatatableid'] = "confirmations-log-table";
                break;

            case 'subscribercontact':
                $tabDetailsArray['title'] = $container->get('translator')->trans('SUBSCRIBER_CONTACTS');
                $tabDetailsArray['url'] = $container->get('router')->generate('subscriber_list');
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = $contCountDetails['subscribercontact'];
                break;

            case 'owncontact':
                $tabDetailsArray['title'] = $container->get('translator')->trans('OWN_CONTACTS_WITH_SUBSCRIPTION');
                $tabDetailsArray['url'] = $container->get('router')->generate('subscriber_contact_list', array('subscriber' => 'contact'));
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = $contCountDetails['owncontact'];
                break;

            case 'newsletter_preview':
                $tabDetailsArray['title'] = $container->get('translator')->trans('MAILINGS_PREVIEW');
                $tabDetailsArray['url'] = ($type == "simplemail") ? $container->get('router')->generate('mailings_simplemail_preview', array('status' => $offset, 'id' => $contact)) : $container->get('router')->generate('mailings_newsletter_preview', array('status' => $offset, 'id' => $contact));
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = "";
                break;
            case 'recipients':
                $tabDetailsArray['title'] = $container->get('translator')->trans('MAILINGS_RECIPIENTS');
                $tabDetailsArray['url'] = ($type == "simplemail") ? $container->get('router')->generate('mailings_simplemail_recipients', array('status' => $offset, 'id' => $contact)) : $container->get('router')->generate('mailings_newsletter_recipients', array('status' => $offset, 'id' => $contact));
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = $contCountDetails;
                break;
            case 'language':
                $tabDetailsArray['title'] = $container->get('translator')->trans('GENERAL_SETTINGS_LANGUAGE');
                $tabDetailsArray['url'] = $container->get('router')->generate('settings_language');
                $tabDetailsArray['name'] = "fg-dev-settings-language-tab";
                $tabDetailsArray['count'] = '';
                break;
            case 'salutations':
                $tabDetailsArray['title'] = $container->get('translator')->trans('GENERAL_SETTINGS_SALUTATION');
                $tabDetailsArray['url'] = $container->get('router')->generate('settings_salutations');
                $tabDetailsArray['name'] = "fg-dev-settings-salutations-tab";
                $tabDetailsArray['count'] = '';
                break;
            case 'terminology':
                $tabDetailsArray['title'] = $container->get('translator')->trans('GENERAL_SETTINGS_TERMINOLOGY');
                $tabDetailsArray['url'] = $container->get('router')->generate('settings_terminology');
                $tabDetailsArray['name'] = "fg-dev-settings-terminology-tab";
                $tabDetailsArray['count'] = '';
                break;
            case 'agelimits':
                $tabDetailsArray['title'] = $container->get('translator')->trans('GENERAL_SETTINGS_AGELIMITS');
                $tabDetailsArray['url'] = $container->get('router')->generate('settings_agelimits');
                $tabDetailsArray['name'] = "fg-dev-settings-agelimits-tab";
                $tabDetailsArray['count'] = '';
                break;
            case 'groups':
                $tabDetailsArray['title'] = $container->get('translator')->trans('GENERAL_SETTINGS_GROUPS');
                $tabDetailsArray['url'] = $container->get('router')->generate('settings_groups');
                $tabDetailsArray['name'] = "fg-dev-settings-groups-tab";
                $tabDetailsArray['count'] = '';
                break;
            case 'misc':
                $tabDetailsArray['title'] = $container->get('translator')->trans('GENERAL_SETTINGS_MISC');
                $tabDetailsArray['url'] = $container->get('router')->generate('settings_misc');
                $tabDetailsArray['name'] = "fg-dev-settings-misc-tab";
                $tabDetailsArray['count'] = '';
                break;
            case 'activeservice':
                $tabDetailsArray['title'] = $container->get('translator')->trans('SM_ACTIVE_SERVICE');
                $tabDetailsArray['url'] = "#fg_dev_activeservice";
                $tabDetailsArray['id'] = "fg_dev_activeservice";
                $tabDetailsArray['count'] = 0;
                $tabDetailsArray['countId'] = "fg-active-service-count";
                $tabDetailsArray['listId'] = "activeservice-tab-li";
                break;

            case 'futureservice':
                $tabDetailsArray['title'] = $container->get('translator')->trans('SM_FUTURE_SERVICE');
                $tabDetailsArray['url'] = "#fg_dev_futureservice";
                $tabDetailsArray['id'] = "fg_dev_futureservice";
                $tabDetailsArray['count'] = 0;
                $tabDetailsArray['countId'] = "fg-future-service-count";
                $tabDetailsArray['listId'] = "futureservice-tab-li";
                break;
            case 'formerservice':

                $tabDetailsArray['title'] = $container->get('translator')->trans('SM_FORMER_SERVICE');
                $tabDetailsArray['url'] = "#fg_dev_formerservice";
                $tabDetailsArray['id'] = "fg_dev_formerservice";
                $tabDetailsArray['count'] = 0;
                $tabDetailsArray['countId'] = "fg-former-service-count";
                $tabDetailsArray['listId'] = "formerservice-tab-li";
                break;
            case 'backend':

                $tabDetailsArray['title'] = $container->get('translator')->trans('USER_RIGHTS_PAGE_ADMINISTRATION');
                $tabDetailsArray['url'] = $container->get('router')->generate('user_rights_page');
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "backend";
                break;
            case 'groupuserrights':

                $tabDetailsArray['title'] = $container->get('translator')->trans('USER_RIGHTS_PAGE_GROUP');
                $tabDetailsArray['url'] = $container->get('router')->generate('group_userrights_team');
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "groupuserrights";
                break;
            case 'new_import':

                $tabDetailsArray['title'] = $contCountDetails;
                $tabDetailsArray['url'] = ($type == "importSponsor") ? $container->get('router')->generate('sponsor_import_file') : $container->get('router')->generate('import_file');
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['data_url'] = "#tab_1_1";
                break;
            case 'existing_import':

                $tabDetailsArray['title'] = $container->get('translator')->trans('UPDATE_EXISTING_CONTACT');
                $tabDetailsArray['url'] = $container->get('router')->generate('import_update_file', array('type' => 'update'));
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['data_url'] = "#tab_1_2";
                break;
            case 'internal' :
                $tabDetailsArray['title'] = $container->get('translator')->trans('USER_RIGHTS_PAGE_FRONTEND');
                $tabDetailsArray['url'] = $container->get('router')->generate('internal_userrights');
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "internal";
                break;
            case 'applicationqueue':
            case 'mergeapplicationqueue':
                $tabDetailsArray['title'] = ($tab == 'mergeapplicationqueue') ? $container->get('translator')->trans('MERGE_APPLICATION_QUEUE_TAB_TEXT') : $container->get('translator')->trans('APPLICATION_QUEUE_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-list-table-div";
                $tabDetailsArray['name'] = "confirmations-list-table";
                $tabDetailsArray['count'] = $contCountDetails;
                $tabDetailsArray['dataTabname'] = "list";
                $tabDetailsArray['dataDatatableid'] = "confirmations-list-table";
                break;
            case 'applicationlog':
            case 'mergeapplicationlog':
                $tabDetailsArray['title'] = ($tab == 'mergeapplicationlog') ? $container->get('translator')->trans('MERGE_APPLICATION_LOG_TAB_TEXT') : $container->get('translator')->trans('APPLICATION_LOG_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-log-table-div";
                $tabDetailsArray['name'] = "confirmations-log-table";
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['dataTabname'] = "log";
                $tabDetailsArray['dataDatatableid'] = "confirmations-log-table";
                break;
            case 'clubassignmentapplicationqueue':
                $tabDetailsArray['title'] = $container->get('translator')->trans('APPLICATION_QUEUE_TAB');
                $tabDetailsArray['url'] = "#confirmations-list-table-div";
                $tabDetailsArray['name'] = "confirmations-list-table";
                $tabDetailsArray['count'] = $contCountDetails;
                $tabDetailsArray['dataTabname'] = "list";
                $tabDetailsArray['dataDatatableid'] = "confirmations-list-table";
                break;
            case 'clubassignmentapplicationlog':
                $tabDetailsArray['title'] = $container->get('translator')->trans('APPLICATION_LOG_TAB_TEXT');
                $tabDetailsArray['url'] = "#confirmations-log-table-div";
                $tabDetailsArray['name'] = "confirmations-log-table";
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['dataTabname'] = "log";
                $tabDetailsArray['dataDatatableid'] = "confirmations-log-table";
                break;
            case 'website' :
                $tabDetailsArray['title'] = $container->get('translator')->trans('USER_RIGHTS_PAGE_WEBSITE');
                $tabDetailsArray['url'] = $container->get('router')->generate('website_userrights');
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "website";
                break;
            case 'apilog' :
                $tabDetailsArray['title'] = $container->get('translator')->trans('GOTCOURTS_LOG');
                $tabDetailsArray['url'] = 'apiServiceLog';
                $tabDetailsArray['count'] = "";
                $tabDetailsArray['name'] = "fg-dev-settings-apilog-tab";
                break;
            default:
                $tabDetailsArray['title'] = '';
                $tabDetailsArray['url'] = '';
                $tabDetailsArray['name'] = "";
                $tabDetailsArray['count'] = '';
        }

        return $tabDetailsArray;
    }

    /**
     * Method to calculate the start date and end date of the event
     *
     * @param date   startdate
     *
     * @return array
     */
    public static function getStartAndEndDateOfEvent($startDateString = '')
    {
        $startDateString = ($startDateString == '') ? date('Y-m-d H:i:s') : $startDateString;
        $nextStartDate = date('Y-m-d', strtotime($startDateString));
        $startTime = date('H:i:s', strtotime($startDateString));
        $startTime = ($startTime != '00:00:00') ? $startTime : date("H:i:s");

        $hour = date('H', strtotime($startTime));
        $minute = date('i', strtotime($startTime));
        $nextStartMinute = ($minute > 30) ? 'H' : 30;
        if ($nextStartMinute == 'H') {
            $nextStartTime = date('H:00:00', strtotime($startTime . ' +1 hour'));
        } else {
            $nextStartTime = date('H:i:s', mktime($hour, $nextStartMinute, '0'));
        }
        $endTime = date('H:i:00', strtotime($nextStartTime . ' +1 hour'));

        if ($nextStartTime == '00:00:00') {
            $nextStartDate = date('Y-m-d', strtotime($startDateString . ' +1 hour'));
        }
        $newStartDate = date('Y-m-d H:i:s', strtotime($startDateString . ' +1 hour'));
        $endDate = date('Y-m-d', strtotime($newStartDate . ' +1 hour'));

        $dateRuleArray = array('start_date' => $nextStartDate, 'start_time' => $nextStartTime, 'end_date' => $endDate, 'end_time' => $endTime);
        //print_r($dateRuleArray);exit;

        return $dateRuleArray;
    }

    /**
     * Function to get locale default thousand and decimal marker
     * @param object $container
     * @return array
     */
    public static function getLocaleSettings($container)
    {
        $langDetails = FgSettings::getLocaleDetails();
        $langSettings = array();
        $none = $container->get('translator')->trans('GN_NONE');
        $space = $container->get('translator')->trans('GN_THIN_SPACE');
        $comma = $container->get('translator')->trans('GN_COMMA');
        $dot = $container->get('translator')->trans('GN_DOT');
        foreach ($langDetails as $lang => $value) {
            if ($value[2] == '') {
                $langSettings[$lang]['thousand'] = $none;
            } elseif ($value[2] == ' ') {
                $langSettings[$lang]['thousand'] = $space;
            } elseif ($value[2] == ',') {
                $langSettings[$lang]['thousand'] = $comma;
            } elseif ($value[2] == '.') {
                $langSettings[$lang]['thousand'] = $dot;
            }
            if ($value[3] == ',') {
                $langSettings[$lang]['decimal'] = $comma;
            } elseif ($value[3] == '.') {
                $langSettings[$lang]['decimal'] = $dot;
            }
        }

        return $langSettings;
    }

    /**
     *
     * @param type $clubId
     * @param type $fileType
     * @param type $resize
     * @param type $fileName
     * @return string
     */
    public static function getUploadFilePath($clubId, $fileType, $resize = false, $fileName = false)
    {
        switch ($fileType) {
            case 'profilepic':
                $uploadPath = 'uploads/' . $clubId . '/contact/profilepic';
                break;
            case 'companylogo':
                $uploadPath = 'uploads/' . $clubId . '/contact/companylogo';
                break;
            case 'contactfield_image':
                $uploadPath = 'uploads/' . $clubId . '/contact/contactfield_image';
                break;
            case 'contactfield_file':
                $uploadPath = 'uploads/' . $clubId . '/contact/contactfield_file';
                break;
            case 'contact_application_file':
                $uploadPath = 'uploads/' . $clubId . '/contact/contact_application_file';
                break;
            case 'clublogo':
                $uploadPath = 'uploads/' . $clubId . '/admin/clublogo';
                break;
            case 'newsletter_header':
                $uploadPath = 'uploads/' . $clubId . '/admin/newsletter_header';
                break;
            case 'messages':
                $uploadPath = 'uploads/' . $clubId . '/users/messages';
                break;
            case 'form_uploads':
                $uploadPath = 'uploads/' . $clubId . '/users/form_uploads';
                break;
            case 'ad':
                $uploadPath = 'uploads/' . $clubId . '/contact/ad';
                break;
            case 'communication':
                $uploadPath = 'uploads/' . $clubId . '/content';
                break;
            case 'communicationimages':
                $uploadPath = 'uploads/' . $clubId . '/gallery/original';
                break;
            case 'gallery':
                $uploadPath = 'uploads/' . $clubId . '/gallery';
                break;
            case 'cms_background_image':
                $uploadPath = 'uploads/' . $clubId . '/gallery/original';
                break;
            case 'cms_themecss':
                $uploadPath = 'club/' . $clubId . '/themes';
                break;
            case 'cms_header':
                $uploadPath = '/uploads/' . $clubId . '/admin/website_header';
                break;
            case 'cms_dynamic_css':
                $uploadPath = './../../../../uploads/' . $clubId . '/gallery/original/';
                break;
            case 'cms_default_logo':
                $uploadPath = '/uploads/' . $clubId . '/default_logo';
                break;
            case 'cms_websettings':
                $uploadPath = '/uploads/' . $clubId . '/admin/website_settings';
                break;
            case 'cms_favicons':
                $uploadPath = '/uploads/' . $clubId . '/favicons';
                break;
            case 'apple_touch_icon':
                $uploadPath = '/uploads/' . $clubId . '/default_logo';
                break;
            case 'documents':
                $uploadPath = '/uploads/' . $clubId . '/documents';
                break;
            case 'cms_header_up':
                $uploadPath = '/'.$clubId . '/admin/website_header';
                break;
            case 'cms_header1920':
                $uploadPath = '/'. $clubId . '/admin/website_header_1920';
                break;
            case 'cms_header1170':
                $uploadPath =  '/'.$clubId . '/admin/website_header_1170';
                break;
            case 'cms_portrait_placeholder':
                $uploadPath = '/uploads/' . $clubId . '/admin/website_portrait';
                break;
        }
        if ($resize) {
            $uploadPath .= '/' . $resize;
        }
        if ($fileName) {
            $uploadPath .= '/' . $fileName;
        }

        return $uploadPath;
    }

    /**
     * Function to generate FilemanagerInlineUrl
     * 
     * @param object $container Container object
     * @param string $file      virtual file name
     * @param int    $clubId    Club id
     * 
     * @return string $url Generated url
     */
    public static function generateFilemanagerInlineUrl($container, $file, $clubId = '', $mode)
    {

        $club = $container->get('club');
        $em = $container->get('doctrine')->getManager();
        $currClubId = $club->get('id');
        if ($mode != 'cron') {
            if ($currClubId != $clubId) {
                $baseUrl = self::getBaseUrl($container, $clubId);
            } else {
                $baseUrl = self::getBaseUrl($container);
            }
        } else {
            $baseUrlArr = self::getMainDomainUrl($container, $clubId, $mode);
            $baseUrl = $baseUrlArr['baseUrlWithUrlIdentifier'];
        }
        if ($file) {
            $routePath = $container->get('router')->generate('filemanager_inline', array('file' => $file), false);
            if ($mode == 'cron') { //Fix For Club Url Identifier
                $urlIdentifier = $club->get('url_identifier');
                $routePath = str_replace('/' . $urlIdentifier . '/', '/', $routePath);
            } elseif ($currClubId != $clubId) {
                $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
                $clubUrl = $clubObj->getUrlIdentifier();
                $urlIdentifier = $club->get('url_identifier');
                $routePath = str_replace('/' . $urlIdentifier . '/', '/', $routePath);
                $routePath = '/' . $clubUrl . $routePath;
            }

            return $baseUrl . $routePath;
        }
    }

    /**
     * Function to generate FilemanagerDownloadUrl
     * 
     * @param object $container Container object
     * @param string $file      virtual file name
     * @param int    $clubId    Club id
     * 
     * @return string $url Generated url
     */
    public static function FilemanagerDownloadUrl($container, $file, $clubId = '', $mode)
    {

        $baseUrlArray = self::generateUrlForCkeditor($container, $clubId, $mode);
        $baseUrl = $baseUrlArray['baseUrl'];
        if ($file) {
            $routePath = $container->get('router')->generate('filemanager_download', array('file' => $file), false);
            if ($mode == 'cron') { //Fix For Club Url Identifier
                $club = $container->get('club');
                $baseUrl = $baseUrlArray['baseUrlWithUrlIdentifier'];
                $urlIdentifier = $club->get('url_identifier');
                $routePath = str_replace('/' . $urlIdentifier . '/', '/', $routePath);
            }
            return $baseUrl . $routePath;
        }
    }

    /**
     * Function to generate array data from a yml file
     *
     * @param string $ymlPath yml file path
     *
     * @return array
     */
    public function generateYmlData($ymlPath)
    {
        $yaml = new Parser();
        // $ymlPath = getcwd() . '/../fairgate4/src/Common/HelpBundle/Resources/config/help.yml';
        $ymlData = $yaml->parse(file_get_contents($ymlPath));

        return $ymlData;
    }

    /**
     * Function to change encoding of file to utf8
     * Used for Import
     *
     * @param string $uploadDir
     * @param string $fileName
     *
     * @return string filename
     */
    public static function changeFileEncodingToUtf8($uploadDir, $fileName)
    {
        /* If the file is other than UTF-8 encoding change to UTF-8 */
        $fileContentString = file_get_contents($uploadDir . '/' . $fileName);
        $encodingList = array(
            'UTF-8', 'ASCII',
            'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5',
            'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10',
            'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16',
            'Windows-1251', 'Windows-1252', 'Windows-1254',
        );
        /* Detect encoding */
        $encoding = mb_detect_encoding($fileContentString, $encodingList, true);
        if ($encoding != 'UTF-8') {
            $now = time();
            $newFilename = $now . $fileName;
            /* Exceute shell script to convert the file to UTF-8 and creates a new file */
            exec('iconv -f ' . $encoding . ' -t UTF-8 ' . $uploadDir . '/' . $fileName . ' > ' . $uploadDir . '/' . $newFilename);
            unlink($uploadDir . '/' . $fileName);
            $fileName = $newFilename;
        }
        return $fileName;
    }

    /**
     * Method to get upload path of contact files/images
     *
     * @param int $clubId       Club-id
     * @param string $fieldType fileupload/imageupload
     * @param object $container container object
     *
     * @return string
     */
    public static function getUploadpath($clubId, $fieldType = 'fileupload', $container)
    {
        $subFolder = ($fieldType == 'fileupload') ? 'contactfield_file' : 'contactfield_image';
        $request = $container->get('request_stack')->getCurrentRequest();
        $httpHost = $request->getHttpHost();
        $path = $request->getUri();
        $url = explode("/", $path);
        $pathUrl = $url['0'] . '//' . $httpHost . '/' . FgUtility::getUploadFilePath($clubId, $subFolder);

        return $pathUrl;
    }

    /**
     * Get Query to update main table value with i18n of default language.
     * @param string $defLang
     * @param array  $fieldsList
     * @param string $where Extra condition
     * 
     * @return string Query string
     */
    public function updateDefaultTable($defLang, $fieldsList, $where = '1')
    {
        $mainTable = $fieldsList['mainTable'];
        $i18nTable = $fieldsList['i18nTable'];
        $mainField = $fieldsList['mainField'];
        $i18nFields = $fieldsList['i18nFields'];
        foreach ($i18nFields as $fieldName) {
            $i18nField[] = "AI.$fieldName";
        }
        foreach ($mainField as $fieldName) {
            $duplicateKey[] = "`$fieldName`=VALUES(`$fieldName`)";
        }


        return "INSERT INTO $mainTable (id," . implode(',', $mainField) . ")(SELECT A.id," . implode(',', $i18nField) . " FROM $mainTable A INNER JOIN $i18nTable AI ON A.id=AI.id AND AI.lang='$defLang' WHERE $where) ON DUPLICATE KEY UPDATE " . implode(',', $duplicateKey);
    }

    /**
     * function used to validate the url identifier
     * 
     * @param string $url
     * @param object $container
     * 
     * @return string validated url identifier
     */
    public static function urlIdentifierValidation($url, $container)
    {
        //change all upper to lower case
        $url = strtolower($url);        
        $restictArray = array('/sitemap.xml/');
        $replaceArray = array('sitemap');
        $url = preg_replace($restictArray, $replaceArray, $url, 1);
        //Umlauts handling
        $restictUmlautArray = array('/ö/', '/ü/', '/ä/', '/à/', '/é/', '/è/', '/â/', '/ô/');
        $replaceUmlautArray = array('oe', 'ue', 'ae', 'a', 'e', 'e', 'a', 'o');
        $url = preg_replace($restictUmlautArray, $replaceUmlautArray, $url);
        //Navigation menu identifier validations allow only a-z, A-Z, 0-9, '_' and '-' chars.
        //The blank spaces and other special characters will be replaced by '_'.
        $url = str_replace(' ', '_', $url);
        $url = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $url);
        //replace repeated underscores to single underscore
        $url = preg_replace("/(_)\\1+/", "$1", $url);

        return $url;
    }
    
    /**
     * function check whether Url Contains restricyed keywords
     * 
     * @param string $url
     * 
     * @return boolean contains or not
     */
    public static function hasUrlContainsRestrictKeywords($url)
    {
        return (in_array($url, array('backend', 'files', 'help', 'internal', 'public', 'website', 'externalApplication', 'externalApplicationSave', 'themepreview', 'sitemap', 'club', 'uploads'))) ? true : false;
    }

    /**
     * Function to generate baseUrl For CkEditor
     * @param object $container Container object
     * @param int    $clubId    Club id
     * @param iny $createdClub Created Club if Created Club url Needed (Currently using in Calendar ,Exisiting Article Newsletter)
     * 
     * @return array with baseurl and withClubidentifier
     */
    public static function generateUrlForCkeditor($container, $clubId, $mode, $createdClub = 0)
    {


        if ($mode == 'cron' && !empty($mode)) {
            $mode = 1;
        }

        $em = $container->get('doctrine')->getManager();
        $club = $container->get('club');
        if ($mode == 0) { #not Set Cron
            if (!empty($createdClub) && ($createdClub != $clubId)) {

                $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($createdClub);
                if ($checkClubHasDomain['domain']) {
                    $baseUrl = $checkClubHasDomain['domain'];
                    $baseUrlWithUrlIdentifier = $baseUrl;
                } else {
                    $baseUrl = self::getBaseUrl($container, $createdClub);
                    $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($createdClub);
                    $clubUrl = $clubObj->getUrlIdentifier();
                    $baseUrlWithUrlIdentifier = self::getBaseUrl($container, $createdClub) . '/' . $clubUrl;
                }
            } else {
                $baseUrl = self::getBaseUrl($container);
                $baseArray = parse_url($baseUrl);
                if ($baseArray['port']) // with port
                    $checkDomain = $baseArray['host'] . ':' . $baseArray['port'];
                else
                    $checkDomain = $baseArray['host'];

                $baseDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->getClubDetails($checkDomain);
                $baseUrlWithUrlIdentifier = $baseUrl;

                if (count($baseDomain) == 0) {
                    $baseUrlWithUrlIdentifier = self::getBaseUrl($container) . '/' . $club->get('clubUrlIdentifier');
                }
            }
        } else { #For Cron 
            // When main Is needed
            if (!empty($createdClub) && ($createdClub != $clubId)) {
                $clubId = $createdClub;
            }
            $arrayDomains = self::getMainDomainUrl($container, $clubId, $mode);
            $baseUrl = $arrayDomains['baseUrl'];
            $baseUrlWithUrlIdentifier = $arrayDomains['baseUrlWithUrlIdentifier'];
        }


        return array('baseUrl' => $baseUrl, 'baseUrlWithUrlIdentifier' => $baseUrlWithUrlIdentifier);
    }

    /**
     * Function to generate Main Domain Url //Used for Mail functions
     * @param object $container Container object
     * @param int    $clubId    Club id
     * 
     * @return array with baseurl and withClubidentifier
     */
    public static function getMainDomainUrl($container, $clubId, $mode = 0)
    {

        $em = $container->get('doctrine')->getManager();
        $club = $container->get('club');
        $checkClubHasDomain = $em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($clubId);
        if ($checkClubHasDomain) {
            $baseUrl = $checkClubHasDomain['domain'];
            $baseUrlWithUrlIdentifier = $baseUrl;
        } else {
            $clubUrl = $club->get('clubUrlIdentifier');
            if ($clubId != $club->get('id')) { #createdClub
                $clubObj = $em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
                $clubUrl = $clubObj->getUrlIdentifier();
            }
            $baseUrl = $container->getParameter('base_url');
            $baseUrlWithUrlIdentifier = $baseUrl . '/' . $clubUrl;
        }


        return array('baseUrl' => $baseUrl, 'baseUrlWithUrlIdentifier' => $baseUrlWithUrlIdentifier);
    }

    /**
     * Function to  correct the Url  in CkEditor For Domain
     * @param string     $str string to correct
     * @param object $container Container object
     * @param int    $clubId    Club id
     * 
     * @return array with baseurl and withClubidentifier
     */
    public static function correctCkEditorUrl($str, $container, $clubId, $mode = 0, $createdClub = 0)
    {

        if ($mode == 'cron' && !empty($mode))
            $mode = 1;
        else
            $mode = 0;
        if (strlen($str) > 0) {
            $urlImage = self::getImageOrAnchor($str, 'image');
            $imageStr = self::processUrl($urlImage, $str, $container, $clubId, $mode, $createdClub);
            $anchorArray = self::getImageOrAnchor($imageStr, 'anchor');
            $anchorStr = self::processUrl($anchorArray, $imageStr, $container, $clubId, $mode, $createdClub);
        } else {
            $anchorStr = $str;
        }


        return $anchorStr;
    }

    /**
     * Function to  proces the Urls in Ck Editor images and href
     * @param string     $str string to correct
     * @param object $container Container object
     * @param int    $clubId    Club id
     * 
     * @return string  replaced one
     */
    private static function processUrl($contentUrl, $str, $container, $clubId, $mode, $createdClub)
    {
        $i = 0;
        $newStr = $str;
        $urlArray = $contentUrl[0];
        $keyArray = array_values($contentUrl[1]);
        foreach ($urlArray as $strPath) {
            $newStr = self::replaceString($strPath, $keyArray[$i], $newStr, $container, $clubId, $mode, $createdClub);
            $i++;
        }

        return $newStr;
    }

    /**
     * Function to  get images and anchor tag urls from Ckeditor
     * @param string     $str string to correct
     * @param string  $tag tag identifier
     * @return array   with Url
     */
    private static function getImageOrAnchor($str, $tag)
    {
        $dom = new \domDocument;

        @$dom->loadHTML($str);
        //$dom->preserveWhiteSpace = false;
        $urlArray = $keyArray = $result = array();
        switch ($tag) {
            case 'image':
                $images = $dom->getElementsByTagName('img');

                foreach ($images as $image) {
                    $imageTag = $image->getAttribute('src');
                    $domain = strstr($imageTag, 'fgassets/assets', true);
                    $domainUrl = strstr($imageTag, 'files/filemanager/download', true);
                    if ($domain) {
                        $urlArray[] = $domain . 'fgassets/assets';
                        $keyArray[] = 'fgassets/assets';
                    } else if ($domainUrl) {
                        $urlArray[] = $domainUrl . 'files/filemanager/download';
                        $keyArray[] = 'files/filemanager/download';
                    }
                }

                $uniqueArr = array_unique($urlArray);
                $uniqKeyArr = array_unique($keyArray);
                $result[0] = $uniqueArr;
                $result[1] = $uniqKeyArr;
                return $result;
                break;
            case 'anchor':
                $images = $dom->getElementsByTagName('a');
                foreach ($images as $image) {
                    $imageTag = $image->getAttribute('href');
                    $domainUrl = strstr($imageTag, 'files/filemanager/download', true);
                    if ($domainUrl) {
                        $urlArray[] = $domainUrl . 'files/filemanager/download';
                        $keyArray[] = 'files/filemanager/download';
                    }
                }

                array_unique($urlArray);
                array_unique($keyArray);
                $result[0] = $urlArray;
                $result[1] = $keyArray;
                return $result;
                break;
        }
    }

    /**
     * Function to  replace the Host 
     * @param string     $needle  to be replaced string
     * @param string  $key  to identify its system urls
     *
     * @return string    string
     */
    public static function replaceString($needle, $key, $str, $container, $clubId, $mode, $createdClub)
    {

        $replacementArray = self::generateUrlForCkeditor($container, $clubId, $mode, $createdClub);
        if ($key == 'fgassets/assets') {
            $replacement = $replacementArray['baseUrl'] . "/" . $key;
        } else if ($key == 'files/filemanager/download') {
            $replacement = $replacementArray['baseUrlWithUrlIdentifier'] . "/" . $key;
        } else {
            if (strpos($needle, 'fgassets/assets') !== false) {
                $key = 'fgassets/assets';
                $replacement = $replacementArray['baseUrl'] . "/" . $key;
            } else if (strpos($needle, 'files/filemanager/download') !== false) {
                $key = 'files/filemanager/download';
                $replacement = $replacementArray['baseUrl'] . "/" . $key;
            }
        }

        if (strpos($needle, $key) !== false) {

            $newstr = str_replace($needle, $replacement, $str);
        }

        return $newstr;
    }
}
