<?php

namespace Clubadmin\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * For image resizing
 */
class FgImage
{

    private $image;
    private $imageType;
/**
 * For load the image
 *
 * @param string $filename filename
 */
    public function load($filename)
    {

        $imageInfo = getimagesize($filename);
        $this->imageType = $imageInfo[2];
        if ($this->imageType == IMAGETYPE_JPEG) {

            $this->image = imagecreatefromjpeg($filename);
        } elseif ($this->imageType == IMAGETYPE_GIF) {

            $this->image = imagecreatefromgif($filename);
        } elseif ($this->imageType == IMAGETYPE_PNG) {

            $this->image = imagecreatefrompng($filename);
        }
    }
/**
 * For save the image
 *
 * @param string  $filename    file name
 * @param int     $compression compression size
 * @param boolean $permissions permission
 */
    public function save($filename, $compression = 100, $permissions = null)
    {

        if ($this->imageType == IMAGETYPE_JPEG) {
            imagejpeg($this->image, $filename, $compression);
        } elseif ($this->imageType == IMAGETYPE_GIF) {

            imagegif($this->image, $filename);
        } elseif ($this->imageType == IMAGETYPE_PNG) {

            // need this for transparent png to work          
            imagealphablending($this->image, false);
            imagesavealpha($this->image,true);
            imagepng($this->image, $filename);
        }
        if ($permissions != null) {

            chmod($filename, $permissions);
        }
    }
/**
 * For get the image
 *
 * @param int $imageType image size
 */
    public function output($imageType = IMAGETYPE_JPEG)
    {

        if ($imageType == IMAGETYPE_JPEG) {
            imagejpeg($this->image);
        } elseif ($imageType == IMAGETYPE_GIF) {

            imagegif($this->image);
        } elseif ($imageType == IMAGETYPE_PNG) {

            imagepng($this->image);
        }
    }
/**
 * For get the width
 *
 * @return type
 */
    public function getWidth()
    {

        return imagesx($this->image);
    }
/**
 * For get the height of the image
 *
 * @return type
 */
    public function getHeight()
    {

        return imagesy($this->image);
    }
/**
 * For resize the height
 *
 * @param int $height height
 */
    public function resizeToHeight($height)
    {

        $ratio = $height / $this->getHeight();
        $width = $this->getWidth() * $ratio;
        $this->resize($width, $height);
    }
/**
 * For resize the width
 *
 * @param int $width width
 */
    public function resizeToWidth($width)
    {
        $ratio = $width / $this->getWidth();
        $height = $this->getheight() * $ratio;
        $this->resize($width, $height);
    }
/**
 * For scale the image
 *
 * @param int $scale scale size
 */
    public function scale($scale)
    {
        $width = $this->getWidth() * $scale / 100;
        $height = $this->getheight() * $scale / 100;
        $this->resize($width, $height);
    }
/**
 * For resize the image
 *
 * @param int $width  width
 * @param int $height height
 */
    public function resize($width, $height)
    {
        $newImage = imagecreatetruecolor($width, $height);
        /* Check if this image is PNG or GIF, then set if Transparent*/  
        if (($this->imageType == IMAGETYPE_GIF) || ($this->imageType==IMAGETYPE_PNG)){
            imagealphablending($newImage, false);
            imagesavealpha($newImage,true);
            $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
            imagefilledrectangle($newImage, 0, 0, $width, $height, $transparent);
        }
        imagecopyresampled($newImage, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $newImage;
    }
/**
 * For crope the image
 *
 * @param int $xRatio xratio
 * @param int $yRatio yratio
 */
    public function crop($xRatio, $yRatio)
    {
        $width = $this->getWidth();
        $height = $this->getheight();
        if (($width * $yRatio) / $xRatio < $height) {
            $cropHeight = ($width * $yRatio) / $xRatio;
            $cropWidth = $width;
        } else {
            $cropHeight = $height;
            $cropWidth = ($height * $xRatio) / $yRatio;
        }
        $xSrc = ($width - $cropWidth) / 2;
        $ySrc = ($height - $cropHeight) / 2;
        $orgWidth = $width;
        $orgHeight = $height;
        $newImage = imagecreatetruecolor($cropWidth, $cropHeight);
        imagecopyresampled($newImage, $this->image, 0, 0, $xSrc, $ySrc, $width, $height, $orgWidth, $orgHeight);
        $this->image = $newImage;
    }
/**
 * For crope and resize the images
 *
 * @param int $resizeWidth  width
 * @param int $resizeheight height
 */
    public function cropAndResize($resizeWidth, $resizeheight)
    {
        $this->crop($resizeWidth, $resizeheight);
        $this->resize($resizeWidth, $resizeheight);
    }

}