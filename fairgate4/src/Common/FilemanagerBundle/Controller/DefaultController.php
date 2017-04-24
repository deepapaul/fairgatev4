<?php

namespace Common\FilemanagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\FilemanagerBundle\Util\FgFileManager;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * The action to download the file added via file manager
     *
     * @param Request $request Request object
     *
     * @return type
     */
    public function downloadAction(Request $request)
    {
        $file = $request->get('file');
        $downloadType = $request->get('downloadtype');
        $fileObj = new FgFileManager($this->container);
        $fileLocation = $this->getDirectory();
        $response = $fileObj->downloadFileByName($file, $fileLocation, ($downloadType == 'inline') ? true : false);

        return $response;
    }

    /**
     * The action to download the file added via file manager
     *
     * @return JsonResponse
     */
    private function getDirectory()
    {
        $clubId = $this->container->get('club')->get('id');

        return $clubId . DIRECTORY_SEPARATOR . 'content' . DIRECTORY_SEPARATOR;
    }

    /**
     * json response total space utilised
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getTotalOverviewAction()
    {

        $finalArray = array();
        $translator = $this->get('translator');
        $trans = array('totalSpace' => $translator->trans('FILE_SPACE'), 'spaceUsed' => $translator->trans('FILE_SPACEUSED'));
        $filesSize = 0;
        $directoryStructure = $this->directoryStructure();
        foreach ($directoryStructure as $key => $path) {
            foreach ($path as $key1 => $subpath) {
                foreach (scandir($subpath) as $file) {
                    if ('.' === $file)
                        continue;
                    if ('..' === $file)
                        continue;
                    $filesSize += filesize($subpath . '/' . $file);
                }
            }
        }

        $diskTotal = $this->container->getParameter('club_total_space');
        $filesSize = round($filesSize / 1048576);
        $percentile = round(($filesSize / ($diskTotal)) * 100);
        if ($percentile > 85) {
            $color = '#cc3300';
        } elseif ($percentile > 70) {
            $color = '#F3D346';
        } else {
            $color = '#009900';
        }

        $finalArray[0] = array('label' => $trans['spaceUsed'], 'data' => $filesSize, 'color' => $color);
        $finalArray[1] = array('label' => $trans['totalSpace'], 'data' => $diskTotal - $filesSize, 'color' => '#f7f7f7');

        $subArray = array('percentile' => $percentile, 'diskTotal' => $diskTotal, 'filesSize' => $filesSize, 'color' => $color);

        return new JsonResponse(array('finalArray' => $finalArray, 'subArray' => $subArray));
    }

    /**
     * json response per section utilised
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSectionOverviewAction()
    {

        $finalArray = array();
        $filesSize = 0;
        $directoryStructure = $this->directoryStructure();

        foreach ($directoryStructure as $key => $path) {
            foreach ($path as $key1 => $subpath) {
                foreach (scandir($subpath) as $file) {
                    if ('.' === $file)
                        continue;
                    if ('..' === $file)
                        continue;
                    $filesSize += filesize($subpath . '/' . $file);
                }
            }
            //$filesSize = FgUtility::formatSizeUnits($filesSize);
            if (round($filesSize / 1048576) > 0)
                $finalArray[] = array('label' => $key, 'data' => round($filesSize / 1048576));
            $filesSize = 0;
        }

        return new JsonResponse($finalArray);
    }

    /**
     * directory structure
     *
     * @return array
     */
    public function directoryStructure()
    {
        $translator = $this->get('translator');
        $trans = array('admin' => $translator->trans('FILE_ADMIN'), 'contact' => $translator->trans('FILE_CONTACTS'),
            'documents' => $translator->trans('FILE_DOCUMENTS'), 'gallery' => $translator->trans('FILE_GALLERY'),
            'users' => $translator->trans('FILE_USERS'), 'content' => $translator->trans('FILE_CONTENT'));
        $club = $this->get('club');
        $clubId = $club->get('id');
        $rootPath = FgUtility::getRootPath($this->container);
        $path = array();
        $initialPath = $rootPath . "/uploads/$clubId";
        $path[$trans['admin']] ['clublogo'] = $initialPath . "/admin/clublogo";
        $path[$trans['admin']] ['invoice_header'] = $initialPath . "/admin/invoice_header";
        $path[$trans['admin']] ['newsletter_header'] = $initialPath . "/admin/newsletter_header";
        $path[$trans['admin']] ['website_bg'] = $initialPath . "/admin/website_bg";
        $path[$trans['admin']]['website_header'] = $initialPath . "/admin/website_header";
        $path[$trans['contact']]['ad'] = $initialPath . "/contact/ad/original";
        $path[$trans['contact']]['contactfield_file'] = $initialPath . "/contact/contactfield_file";
        $path[$trans['contact']]['contactfield_image'] = $initialPath . "/contact/contactfield_image";
        $path[$trans['contact']] ['profilepic'] = $initialPath . "/contact/profilepic/original";
        $path[$trans['contact']]['companylogo'] = $initialPath . "/contact/companylogo/original";
        $path[$trans['content']]['original'] = $initialPath . "/content";
        $path[$trans['documents']]['original'] = $initialPath . "/documents";
        $path[$trans['gallery']]['original'] = $initialPath . "/gallery/original";
        $path[$trans['users']]['messages'] = $initialPath . "/users/messages";
        $path[$trans['users']]['form_uploads'] = $initialPath . "/users/form_uploads";

        return $path;
    }
    
    /**
     * This method is used to download files from filemanager contact  
     * 
     * @param Request $request
     * @param type $folder folder name inside contact folder - contactfield_file/contact_application_file
     *
     *  @return void
     */
    public function downloadFromFolderAction($folder = 'contactfield_file', $file)
    {
        $fileObj = new FgFileManager($this->container);
        
        $clubId = $this->container->get('club')->get('id');

        $fileLocation = $clubId . DIRECTORY_SEPARATOR . 'contact' . DIRECTORY_SEPARATOR.$folder.DIRECTORY_SEPARATOR;
        
        $response = $fileObj->downloadFile($file, $file, $fileLocation);

        return $response;
    }
}
