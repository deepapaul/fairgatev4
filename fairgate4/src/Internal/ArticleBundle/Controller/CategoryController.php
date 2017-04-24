<?php

/**
 * Article Category Controller.
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Internal\ArticleBundle\Util\ArticlesList;
use Common\UtilityBundle\Util\FgPermissions;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * This controller is used for article category management.
 * 
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class CategoryController extends Controller
{

    /**
     * Executes categories list, manage action.
     *
     * Function to list article categories.
     *
     * @return object View Template Render Object
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $isAdmin = in_array('ROLE_ARTICLE', $this->container->get('contact')->get('availableUserRights')) ? 1 : 0;
        $permissionObj = new FgPermissions($this->container);
        $permissionObj->checkUserAccess('articleEditorialUserrights', $isAdmin);
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        $articleListObj = new ArticlesList($this->container, 'editorial');
        $editableArray = $articleListObj->getEditableArticleIds($clubId);
        $dataArray = $em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getCategoryList($clubId, $editableArray);
        $backLink = $this->generateUrl('internal_article_editorial_list');
        $breadCrumb = array(
            'back' => $backLink
        );

        return $this->render('InternalArticleBundle:Category:index.html.twig', array('result_data' => $dataArray, 'clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $clubLanguages, 'breadCrumb' => $breadCrumb, 'backLink' => $backLink, 'clubId' => $clubId, 'contactId' => $contactId));
    }

    /**
     * Function to save article categories.
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function savecategoryAction(Request $request)
    {
        $return = array();
        $club = $this->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $noParentLoad = $request->request->get('noParentLoad');
            $categoryArray = (array_key_exists('new_cat', $catArr)) ? $catArr['new_cat'] : $catArr;
            $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->saveCategory($clubId, $clubDefaultLang, $categoryArray, $clubLanguages);
            $responseText = $this->get('translator')->trans('ARTICLE_CATEGORY_SAVE_MESSAGE');
            $return = array('status' => 'SUCCESS', 'flash' => $responseText, 'Catid' => $result['catId'], 'CatTitle' => $result['catTitle']);
            if ($noParentLoad) {
                $return['noparentload'] = true;
            } else {
                $return['sync'] = 1;
            }
        }

        return new JsonResponse($return);
    }

    /**
     * Function to save article categories From sidebar.
     *
     * @param Request $request Request object
     *
     * @return object JSON Response Object
     */
    public function savecategoryFromSidebarAction(Request $request)
    {
        $club = $this->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        $categoryCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getCategoryCount($clubId);
        $categoryArray = array("0" =>
            array(
                'title' => array("$clubDefaultLang" => $request->get('value')),
                'is_deleted' => 0,
                'sort_order' => $categoryCount + 1
            )
        );

        $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->saveCategory($clubId, $clubDefaultLang, $categoryArray, $clubLanguages);
        $input[] = array('id' => $result['catId'], 'title' => $result['catTitle'], 'type' => 'select', 'count' => '0', 'draggable' => 1, 'draggableClass' => 'fg-dev-draggable', 'isArticle' => 1, 'itemType' => 'CAT');

        return new JsonResponse(array('input' => $input));
    }
}
