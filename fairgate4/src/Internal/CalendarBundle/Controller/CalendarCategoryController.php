<?php

/**
 * Calendar Category Controller.
 *
 * This controller is used for category management.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class CalendarCategoryController extends Controller
{

    /**
     * Executes categories list, manage action. Function to list calendar categories.
     *
     * @return template
     */
    public function editcategoryAction()
    {
        $em = $this->getDoctrine()->getManager();
        $club = $this->get('club');
        $clubId = $club->get('id');
        $contactId = $this->get('contact')->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        $dataArray = $em->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->getCategoryList($clubId);
        $backLink = $this->generateUrl('internal_calendar_view');
        $breadCrumb = array(
            'breadcrumb_data' => array(),
            'back' => $backLink,
        );

        return $this->render('InternalCalendarBundle:Category:editcategory.html.twig', array('result_data' => $dataArray, 'clubDefaultLang' => $clubDefaultLang, 'clubLanguages' => $clubLanguages, 'breadCrumb' => $breadCrumb, 'backLink' => $backLink, 'clubId' => $clubId, 'contactId' => $contactId));
    }

    /**
     * Executes add category action
     * This Function is used to save newly created categories,updated  categories,changed sort orders.
     * Method also handles delete category actions.
     * $catArr   contains category name,id,sort order.
     *
     * @param Request $request Request object
     *
     * @return json response
     */
    public function addcategoryAction(Request $request)
    {
        $club = $this->get('club');
        $clubId = $club->get('id');
        $clubDefaultLang = $club->get('club_default_lang');
        $clubLanguages = $club->get('club_languages');
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $noParentLoad = $request->request->get('noParentLoad');
            $categoryArray = (array_key_exists('new_cat', $catArr)) ? $catArr['new_cat'] : $catArr;
            $result = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgEmCalendarCategory')->categorySave($clubId, $clubDefaultLang, $categoryArray, $clubLanguages, $this->container);
            $responseText = $this->get('translator')->trans('CALENDAR_CATEGORY_SAVE_MESSAGE');
            $return = array('status' => 'SUCCESS', 'flash' => $responseText, 'Catid' => $result['catId'], 'CatTitle' => $result['catTitle']);
            if ($noParentLoad) {
                $return['noparentload'] = true;
            } else {
                $return['sync'] = 1;
            }

            return new JsonResponse($return);
        }
    }
}
