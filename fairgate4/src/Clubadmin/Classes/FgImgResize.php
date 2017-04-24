<?php

namespace Clubadmin\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * For image resize
 */
class FgImgResize
{

    public $img;
    public $dim=array();
    public $path=array();
    public $fName;
    public $originalWidth;
    public $originalHeight;
    public $originalImageGd;
    public $destination;

    const GALLERY_IMAGE='1280*1280';
    const GALLERY_MEDIA='550*550';
    const NEWS_SLIDE_WIDGET='450*300';
    const GALLERY_WIDGET='300*200';
    const NEWS_OVERVIEW='171*114';
    const GALLERY_WIDGET_PREVIEW='150*100';
    const NEWS_WIDGET='99*66';
    const CONTENT_EDIT='78*52';
    const REARRANGE_POPUP='72*72';

    /**
     * @param <string> $filename       the path of the file  that have to be croped or resized
     * @param <string> $outputFilename the name of the output file
     */
    public function setImage($filename,$outputFilename='')
    {

        $this->img=$filename;
        if ($outputFilename=='') {
            $this->fName=basename($filename);
        } else {
            $this->fName=$outputFilename;
        }
        $originalImageSize = getimagesize($filename);
        $mimeType=$originalImageSize['mime'];

        if ($mimeType=='image/jpeg') {
                $this->originalImageGd = imagecreatefromjpeg($filename);
        }
        if ($mimeType=='image/gif') {
                $this->originalImageGd = imagecreatefromgif($filename);
        }
        if ($mimeType=='image/png') {
                $this->originalImageGd = imagecreatefrompng($filename);
        }


        $this->originalWidth = $originalImageSize[0];
        $this->originalHeight = $originalImageSize[1];

    }

    /**
     * @param <string> $dim      dimensions on which the image is croped /resized
     * @param <string> $savepath destination path for the croped/resized image
     */
    public function setParams($dim, $savepath)
    {
        $this->dim[]=$dim;
        $this->path[]=$savepath;
    }

    /**
     * The cropping /resizing process starts here
     */
    public function process()
    {
        $cnt=count($this->dim);
        for ($i=0; $i<$cnt; $i++) {
            if ($this->dim[$i]==self::GALLERY_IMAGE || $this->dim[$i]==self::GALLERY_MEDIA) {
               $type='resize';
            } else {
                $type='crop';
            }
            $this->cropImg($this->path[$i], $this->dim[$i], $type);
        }
    }

    /**
     *It's a private function do the cropping/resize function
     * @param <string> $destination destination path of modified image
     * @param <string> $dimension   dimensions on which the image is croped /resized
     * @param <string> $type        resize/crop
     */
    private function cropImg($destination,$dimension,$type='resize')
    {

        $this->destination=$destination;
        $dimArr=explode('*', $dimension);
        $this->cropWidth=$dimArr[0];
        $this->cropHeight=$dimArr[1];

        $croppedImageGd = imagecreatetruecolor($this->cropWidth, $this->cropHeight);
        if ($type=='resize') {
            $this->createImage($croppedImageGd, 0, 0, $this->originalWidth, $this->originalHeight);
        } else {
            if ($this->cropWidth < $this->originalWidth  || $this->cropHeight < $this->originalHeight) {
                    $ratio = max($this->cropWidth/$this->originalWidth, $this->cropHeight/$this->originalHeight);
                    $newXAxis = ($this->originalWidth - $this->cropWidth / $ratio) / 2;
                    $newYAxis = ($this->originalHeight - $this->cropHeight / $ratio) / 2;
                    $newWidth = $this->cropWidth / $ratio;
                    $newHeight = $this->cropHeight / $ratio;
                    $this->createImage($croppedImageGd, $newXAxis, $newYAxis, $newWidth, $newHeight);
            } else {
                    $this->createImage($croppedImageGd, 0, 0, $this->cropWidth, $this->cropHeight);
            }
        }
    }
    /**
     * It's a private function which create the modified image
     * @param <string> $croppedImageGd new image resource link
     * @param <int>    $intWidth       x co-ordinate value for old image
     * @param <int>    $intHeight      y co-ordinate value for old image
     * @param <int>    $tmpWidth       width of old image
     * @param <int>    $tmpHeight      height of old image
     */
    private function createImage($croppedImageGd,$intWidth,$intHeight,$tmpWidth, $tmpHeight)
    {

        imagecopyresampled($croppedImageGd, $this->originalImageGd, 0, 0, $intWidth, $intHeight, $this->cropWidth, $this->cropHeight, $tmpWidth, $tmpHeight);
        $dest =$this->destination.'/'.$this->fName; //edit filename for cropped image
        try {
            imagejpeg($croppedImageGd, $dest, 100);
        } catch (Exception $e) {
            echo "Error on image creation";
        }
    }
}