<?php

namespace Clubadmin\DocumentsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * CategoryController
 *
 * This controller was created for handling category in document mangement
 *
 * @package    ClubadminDocumentsBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class CategoryController extends ParentController
{

    /**
     * Function to render categories create and edit page
     *
     * @param Request $request Request object
     * 
     * @return template
     */
    public function editCategoryAction(Request $request)
    {
        $typeval = $request->get('level1');
        if ($typeval == 'club') {
            $backLink = $this->generateUrl('club_documents_listing');
        } elseif ($typeval == 'team') {
            $backLink = $this->generateUrl('team_documents_listing');
        } elseif ($typeval == 'workgroup') {
            $backLink = $this->generateUrl('workgroup_documents_listing');
        } else {
            $backLink = $this->generateUrl('contact_documents_listing');
        }
        $type = strtoupper($typeval);
        $dataArray = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getCategoryList($this->clubId, $type);
        $breadCrumb = array(
            'back' => $backLink
        );

        return $this->render('ClubadminDocumentsBundle:Category:editCategory.html.twig', array('result_data' => $dataArray, 'breadCrumb' => $breadCrumb, 'clubDefaultLang' => $this->get('club')->get('club_default_lang'), 'clubLanguages' => $this->clubLanguages, 'catType' => $type, 'type' => $typeval, 'backLink' => $backLink));
    }

    /**
     * Function is used for update categories
     *
     * @param Request $request Request object
     * 
     * @return JSON
     */
    public function updateCategoryAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $catType = $request->get('cat_type', '');
            $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->categorySave($this->clubId, $this->get('club')->get('club_default_lang'), $catArr, $this->clubLanguages);
            $terminologyService = $this->get('fairgate_terminology_service');
            if ($catType == 'workgroup') {
                $responseText = $this->get('translator')->trans('WORKGROUP_DOCUMENT_CATEGORY_UPDATE_MESSAGE');
            } elseif ($catType == 'contact') {
                $responseText = $this->get('translator')->trans('CONTACT_DOCUMENT_CATEGORY_UPDATE_MESSAGE');
            } elseif ($catType == 'team') {
                $termTeam = $terminologyService->getTerminology('Team', $this->container->getParameter('singular'));
                $responseText = str_replace('%team%', ucfirst($termTeam), $this->get('translator')->trans('TEAM_DOCUMENT_CATEGORY_UPDATE_MESSAGE'));
            } else {
                $termClub = $terminologyService->getTerminology('Club', $this->container->getParameter('singular'));
                $responseText = str_replace('%club%', ucfirst($termClub), $this->get('translator')->trans('CLUB_DOCUMENT_CATEGORY_UPDATE_MESSAGE'));
            }
            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $responseText));
        }
    }

    /**
     * Method to create category and its subcategory
     * 
     * @param Request $request Request object
     * 
     * @return JsonResponse
     */
    public function addCategoryAction(Request $request)
    {
        $type = $request->get('type');
        $category = $request->get('category');
        $subcategory = $request->get('subcategory');
        if ($category && $subcategory) {
            $dataCategoryArray = array('0' => array('title' => array($this->clubDefaultLang => $category), 'catType' => $type, 'sortOrder' => 1));
            $categoryId = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->categorySave($this->clubId, $this->clubDefaultLang, $dataCategoryArray, $this->clubLanguages, true);
            $items = array("id" => "$categoryId", "title" => $category, "type" => "select");

            if ($categoryId) {
                $dataSubCategoryArray = array('0' => array('title' => array($this->clubDefaultLang => $subcategory), 'catType' => $type, 'sortOrder' => 1));
                $lastInsertedId = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentSubcategory')->subcategorySave($this->clubId, $this->clubDefaultLang, $dataSubCategoryArray, $this->clubLanguages, $categoryId, true);
                $status = ($lastInsertedId) ? "SUCCESS" : "";
                $input = array("id" => "$lastInsertedId", "title" => $subcategory, "categoryId" => "$categoryId", "itemType" => "DOCS-" . $this->clubId, "count" => 0, "bookMarkId" => "", "type" => "select", "draggable" => 1);
                $items['input'] = array($input);
            }
        }
        if ($status === "SUCCESS") {
            return new JsonResponse(array('status' => $status, "items" => array("items" => array($items)), "parentId" => "DOCS-" . $this->clubId,
                "parentMenuId" => "DOCS-" . $this->clubId . "_" . $categoryId, 'flash' => $this->get('translator')->trans('DOCUMENT_CATEGORY_SAVED')));
        }
    }

    /**
     * Function to show add category/subcategory popup if no category existing in a perticular document type(club,contact.team,workgroup)
     * 
     * @param Request $request Request Object with document type to check
     * 
     * @return JsonResponse/template
     */
    public function categoryAddPageAction(Request $request)
    {
        $type = $request->get('type');
        $categoryExist = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->checkCategoryExist($this->clubId, $type);
        $titleText = $this->get('translator')->trans('DOCUMENT_ADDCATEGORY_HEADER');
        $titleDesc = $this->get('translator')->trans('DOCUMENT_ADDCATEGORY_DESC');
        $return = array('titleText' => $titleText, 'titleDesc' => $titleDesc, 'type' => $type);
        if ($categoryExist > 0) {
            
            return new JsonResponse(array('status' => 'EXIST'));
        } else {
            
            return $this->render('ClubadminDocumentsBundle:Category:addCategoryPopup.html.twig', $return);
        }
    }

    /**
     * Function to get category and subcategory list for drop down values
     * 
     * @param string $typeval Document type (club,contact,team,workgroup)
     * 
     * @return JsonResponse
     */
    public function getDropdownValuesAction($typeval)
    {
        $type = strtoupper($typeval);
        $dataArray = $this->em->getRepository('CommonUtilityBundle:FgDmDocumentCategory')->getAllCategoriesSubCategories($this->clubId, $type, $this->defaultLang);

        return new JsonResponse(array('resultArray' => $dataArray));
    }
}
