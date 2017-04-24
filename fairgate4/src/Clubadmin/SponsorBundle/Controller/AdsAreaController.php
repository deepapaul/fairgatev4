<?php

namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * AdsArea Controller
 *
 * This controller was created for handling category in sponsor mangement
 *
 * @package    ClubadminSponsorBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
class AdsAreaController extends ParentController
{

    /**
     * Function is used for create and edit ads area
     *
     * @return template
     */
    public function editadsAreaAction()
    {
        $generalCategory = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAlladsCommonCategory($this->clubId);
        $dataArray = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getAlladsCategory($this->clubId);
        $generalCatTranslateArray = array('General' => $this->get('translator')->trans('SM_AD_AREA_GENERAL'));
        $settings = true;
        return $this->render('ClubadminSponsorBundle:AdsArea:editAdsArea.html.twig', array('result_data' => $dataArray, 'general_category' => $generalCategory, 'settings' => $settings, 'generalCatTrans' => $generalCatTranslateArray));
    }

    /**
     * Function is used for updating ads area
     *
     * @param Request $request Request object
     *
     * @return response
     */
    public function updateadsAreaAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $catArr = json_decode($request->request->get('catArr'), true);
            $dataArray = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->adscategorySave($this->clubId, $catArr);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('SM_AD_AREAS_UPDATED_SUCCESSFULLY')));
        }
    }

    /**
     * Function to get previews of sponsor ads
     *
     * @param Request $request Request object
     *
     * @return type
     */
    public function getAddPreviewsAction(Request $request)
    {
        $services = $request->get("services");
        $adArea = $request->get("adareas");
        $width = $request->get("width");
        $club = $this->get('club');
        $sponsorAds = $this->em->getRepository('CommonUtilityBundle:FgSmAdArea')->getDetailsOfSponsorAdPreview($services, $adArea, $width, $this->clubId, $this->container, $club);

        return $this->render('ClubadminSponsorBundle:AdsArea:sponsorAdsPreview.html.twig', array('sponsorAds' => $sponsorAds));
    }
}
