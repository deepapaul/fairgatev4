<?php

namespace Clubadmin\DocumentsBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * SubCategoryController
 *
 * This controller was created for handling subcategory in document mangement
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class SubCategoryController extends ParentController
{

    /**
     * Function is used for create and edit subcategories
     *
     * @return template
     */
    public function editSubCategoryAction(Request $request)
    {
        $catId = $request->get('catId');
        $typeval = $request->get('level1');
        $catClubId = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getCategoryClubIdForAccessCheck($catId, strtoupper($typeval));
        $permissionObj = $this->fgpermission;
        $accessCheck = ($catClubId != $this->clubId) ? 0 : 1;
        $permissionObj->checkClubAccess($accessCheck, "backend_document_subcategory");
        $breadCrum = $this->getBreadCrumpSubCategory($typeval);
        $catName = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getCategoryTitle($this->clubId, $catId);
        $type = strtoupper($typeval);
        $dataArray = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->getsubCategoryList($this->clubId, $type, $catId);
        $breadCrumb = array(
            'back' => $breadCrum['Link']
        );

        return $this->render('ClubadminDocumentsBundle:SubCategory:editCategory.html.twig', array('result_data' => $dataArray, 'breadCrumb' => $breadCrumb, 'contactId' => $this->contactId, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages, 'catType' => $typeval, 'catId' => $catId, 'catName' => $catName, 'breadCrumbLink' => $breadCrum['Link'], 'breadcrumbText' => $breadCrum['Text']));
    }

    /**
     * Function is used for update subcategories
     *
     * @return response
     */
    public function updateSubCategoryAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $catType = $request->request->get('catType');
            $catId = $request->request->get('catId');
            $this->em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->subcategorySave($this->clubId, $this->get('club')->get('club_default_lang'), $catArr, $this->clubLanguages, $catId);
            $terminologyService = $this->get('fairgate_terminology_service');
            if ($catType == 'workgroup') {
                $responseText = $this->get('translator')->trans('WORKGROUP_DOCUMENT_SUBCATEGORY_UPDATE_MESSAGE');
            } elseif ($catType == 'contact') {
                $responseText = $this->get('translator')->trans('CONTACT_DOCUMENT_SUBCATEGORY_UPDATE_MESSAGE');
            } elseif ($catType == 'team') {
                $termTeam = $terminologyService->getTerminology('Team', $this->container->getParameter('singular'));
                $responseText = str_replace('%team%', ucfirst($termTeam), $this->get('translator')->trans('TEAM_DOCUMENT_SUBCATEGORY_UPDATE_MESSAGE'));
            } else {
                $termClub = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
                $responseText = str_replace('%club%', ucfirst($termClub), $this->get('translator')->trans('CLUB_DOCUMENT_SUBCATEGORY_UPDATE_MESSAGE'));
            }
            
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $responseText));
        }
    }
    /**
     * Function to get breadcrump setting of sub category settings page
     * 
     * @param string $docType Document type (club,contact,team,workgroup)
     * @return type
     */
    private function getBreadCrumpSubCategory($docType){
        $terminologyService = $this->get('fairgate_terminology_service');
        if ($docType == 'club') {
            $termClub = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
            $breadCrumb['Link'] = $this->generateUrl('category_edit_club');
            $breadCrumb['Text'] = str_replace('%club%', ucfirst($termClub), $this->get('translator')->trans('CLUB_DOCUMENT_CATEGORIES'));
        } elseif ($docType == 'team') {
            $termTeam = $terminologyService->getTerminology('Team', $this->container->getParameter('singular'));
            $breadCrumb['Link'] = $this->generateUrl('category_edit_team');
            $breadCrumb['Text'] = str_replace('%team%', ucfirst($termTeam), $this->get('translator')->trans('TEAM_DOCUMENT_CATEGORIES'));
        } elseif ($docType == 'workgroup') {
            $breadCrumb['Link'] = $this->generateUrl('category_edit_workgroup');
            $breadCrumb['Text'] = $this->get('translator')->trans('WORKGROUP_DOCUMENT_CATEGORIES');
        } else {
            $breadCrumb['Link'] = $this->generateUrl('category_edit_contact');
            $breadCrumb['Text'] = $this->get('translator')->trans('CONTACT_DOCUMENT_CATEGORIES');
        }
        
        return $breadCrumb;
    }
}
