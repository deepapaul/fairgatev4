<?php

namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Category Controller
 *
 * This controller was created for handling category in sponsor mangement
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
class CategoryController extends ParentController
{

    /**
     * Function is used for create and edit categories
     *
     * @param \Symfony\Component\HttpFoundation\Request $request Request object
     *
     * @return template
     */
    public function editserviceCategoryAction(Request $request)
    {
        $session = $request->getSession();
        $session->set('sponsor_categorysettings_referrer', 'sponsorcategorylisting'); // For back link in service category settings page.

        $dataArray = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->getAllserviceCategories($this->clubId);
        $backLink = $this->generateUrl('clubadmin_sponsor_homepage');
        return $this->render('ClubadminSponsorBundle:Category:editserviceCategory.html.twig', array('result_data' => $dataArray, 'backlink' => $backLink));
    }

    /**
     * Function is used for updating service categories
     *
     * @return template
     */
    public function updateserviceCategoryAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $dataArray = $this->em->getRepository('CommonUtilityBundle:FgSmCategory')->categoryserviceSave($this->clubId, $catArr);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('SM_SERVICE_CATEGORIES_UPDATED_SUCCESSFULLY')));
        }
    }
}

?>
