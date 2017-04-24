<?php

namespace Internal\GeneralBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller is used for Dashboard management.
 * 
 * @package    DashboardController
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */

class DashboardController extends Controller
{

    public function indexAction()
    {
        return $this->render('InternalGeneralBundle:Dashboard:index.html.twig');
    }

    /**
     * Function to chanage uploaded file location for sent newsletter.
     * Newsletter header images and newsletter full images are saved inside communication folder.
     * So for sent newsletter, check whether it is existing in gallery(ie: full images). If it not take it from admin/newsletter_header (ie: header images).
     *
     * @param Request $request Request object
     *
     * @return Response
     */
    public function changeFilePathAction(Request $request)
    {
        $reqUri = urldecode($request->getRequestUri());
        if (strpos($reqUri, '171X114') !== false || strpos($reqUri, 'article_images') !== false) { // if uploaded image is resized(171X114)
            $fileLocation = str_replace('communication/article_images', 'gallery', $reqUri);
        } else {
            $fileLocation = str_replace('communication', 'gallery/original', $reqUri);
        }
        $rootPath = FgUtility::getRootPath($this->container);

        if (file_exists($rootPath . $fileLocation) && !is_dir($fileLocation)) {
            $response = new BinaryFileResponse($rootPath . $fileLocation);
            // you can modify headers here, before returning
            return $response;
        } else {
            // for handling newsletter header images
            $fileLocation = str_replace('communication', 'admin/newsletter_header', $reqUri);
            if (file_exists($rootPath . $fileLocation) && !is_dir($fileLocation)) {
                $response = new BinaryFileResponse($rootPath . $fileLocation);
                // you can modify headers here, before returning
                return $response;
            }
        }
        exit();
    }

    /**
     * This function is used to handle displaying of sponsor content's company logo's for already sent newsletters.
     * From now onwards a contacts company logo is uploaded against the federation's club id.
     *
     * @param Request $request  Request object
     * @param int     $clubId   Club id
     *
     * @return BinaryFileResponse
     */
    public function handleCompanyLogoUrlAction(Request $request, $clubId)
    {
        $reqUri = urldecode($request->getRequestUri());

        //company logo is saved under the current federation's folder from now onwards
        $federationId = $this->container->get('club')->get('federation_id');
        $companyLogoAccountedClubId = ($federationId > 0) ? $federationId : $clubId;
        $fileLocation = str_replace('/' . $clubId . '/', '/' . $companyLogoAccountedClubId . '/', $reqUri);

        $rootPath = FgUtility::getRootPath($this->container);
        $file = $rootPath . $fileLocation;

        if (file_exists($file) && !is_dir($fileLocation)) {
            $response = new BinaryFileResponse($file);
            // you can modify headers here, before returning
            return $response;
        }
        exit();
    }
}