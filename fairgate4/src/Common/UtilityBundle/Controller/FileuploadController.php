<?php

/**
 * DefaultController
 *
 * This controller is used to handle defaults functionalities
 *
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Common\UtilityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

/**
 * DefaultController
 *
 * DefaultController
 *
 * @package    CommonUtilityBundle
 * @subpackage Form
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FileuploadController extends Controller
{

    /**
     * This function is used to render the default template
     *
     * @return HTML
     */
    public function indexAction(Request $request)
    {
        $files = $request->files->get('fileupload');
        $uploadPath = 'uploads/temp';
        foreach ($files as $file) {
            $fileName = FgUtility::getFilename($uploadPath, $file->getClientOriginalName());
            if ($file->move($uploadPath, $fileName)) {
                return new JsonResponse(array('status' => 'success', 'filename' => $fileName));
            }
        }
        return new JsonResponse(array('status' => 'error'));
    }

    public function deleteTempFileAction(Request $request)
    {
        $uploadPath = 'uploads/temp';
        $filename = $request->request->get('filename');
        unlink($uploadPath . '/' . $filename);
        return new JsonResponse(array('status' => 'success'));
    }
}
