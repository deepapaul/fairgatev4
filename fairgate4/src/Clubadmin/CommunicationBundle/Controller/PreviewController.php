<?php

namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;
class PreviewController extends FgController
{

    /**
     * Function to show newsletter preview
     *
     * @Template("ClubadminCommunicationBundle:Preview:newsletter-preview.html.twig")
     */
    public function previewNewsletterAction(Request $request)
    {
        $templateId = 0;
        $newsletterId = $request->get('newsletterId', '0');
        $mode = $request->get('mode', 'preview');
        $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getNewsletterContentDetails($this->container, $this->clubId, $newsletterId, $templateId, $mode, $this->contactId, $this->clubDefaultSystemLang);

        return $result;
    }

    /**
     * Function to show template preview
     *
     * @Template("ClubadminCommunicationBundle:Preview:newsletter-preview.html.twig")
     */
    public function previewTemplateAction(Request $request)
    {
        $templateId = $request->get('templateid', '0');
        $newsletterId = 0;
        $mode = $request->get('mode', 'preview');
        $result = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getNewsletterContentDetails($this->container, $this->clubId, $newsletterId, $templateId, $mode, $this->contactId, $this->clubDefaultSystemLang);

        return $result;
    }

    /**
     * Function to show simplemail preview
     *
     * @Template("ClubadminCommunicationBundle:Preview:simpleMail-preview.html.twig")
     */
    public function previewSimpleMailAction(Request $request)
    {
        $newsletterId = $request->get('newsletterId', '0');
        $mode = $request->get('mode', 'preview');
        $previewData = array();
        $checkClubHasDomain = $this->em->getRepository('CommonUtilityBundle:FgDnClubDomains')->checkClubHasDomain($this->clubId);
        if ($checkClubHasDomain && ($this->container->getParameter('kernel.environment') == 'domain')) {
            $baseUrl = $checkClubHasDomain['domain'];
        } else {
            $baseUrl = FgUtility::getBaseUrl($this->container);
        }
        if ($newsletterId) {
            $previewData = $this->em->getRepository('CommonUtilityBundle:FgCnNewsletterContent')->getSimplemailContentDetails($this->container, $this->clubId, $newsletterId, $this->contactId, $this->clubTitle, $mode, $this->clubDefaultSystemLang);
        } else {//use email template
            $previewData['title'] = $request->get('title', $this->clubTitle);
            $previewData['content'] = $request->get('emailContent', '');
            $previewData['baseUrl'] = $baseUrl;
        }
        $clubObj = $this->container->get('club');
        $previewData['content'] =  FgUtility::correctCkEditorUrl($previewData['content'],$this->container,$clubObj->get('id'));
        $previewData['signature'] =  FgUtility::correctCkEditorUrl($previewData['signature'],$this->container,$clubObj->get('id'));
        
        $clubLogo = $clubObj->get('logo');
        $rootPath = FgUtility::getRootPath($this->container);
        $baseUrlArr  = FgUtility::generateUrlForCkeditor($this->container,$clubObj->get('id'),0);
        $baseUrl = $baseUrlArr['baseUrl'];
        if ($clubLogo == '' || !file_exists($rootPath . '/uploads/' . $clubObj->get('id') . '/admin/clublogo/' . $clubLogo)) {
            $clubLogoUrl = '';
        } else {
            $clubLogoUrl = $baseUrl . '/uploads/' . $clubObj->get('id') . '/admin/clublogo/' . $clubLogo;
        }
        $previewData['logoURL'] = $clubLogoUrl;

        return $previewData;
    }

    /**
     * To create an image with text
     *
     * @param type $width       image width
     * @param type $height      image height
     * @param type $text text
     *
     *  @return png image
     */
    public function creatImageAction($width, $height, $text)
    {
        $im = ImageCreate($width, $height);
        // white background and blue text
        $bg = ImageColorAllocate($im, 204, 204, 204);
        // grey border
        $border = ImageColorAllocate($im, 207, 199, 199);
        ImageRectangle($im, 0, 0, $width - 1, $height - 1, $border);
        $textcolor = ImageColorAllocate($im, 102, 102, 102);

        // Font Size
        $font = 3;
        $font_width = ImageFontWidth($font);
        $font_height = ImageFontHeight($font);

        $text_width = $font_width * strlen($text);

        // Position to align in center
        $position_center = ceil(($width - $text_width) / 2);

        $text_height = $font_height;

        // Position to align in abs middle
        $position_middle = ceil(($height - $text_height) / 2);


        $image_string = ImageString($im, $font, $position_center, $position_middle, $text, $textcolor);

        header("Content-type: image/png");
        ImagePNG($im);
        exit;
    }
}
