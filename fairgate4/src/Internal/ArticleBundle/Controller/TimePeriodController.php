<?php

/**
 * TimePeriod Controller
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * TimePeriod Controller
 *
 * This controller was created for handling the time period functionality in Article area.
 *
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 *
 */
class TimePeriodController extends Controller
{

    /**
     * Function to show time period pop up
     *
     * @return object View Template Render Object
     */
    public function timePeriodPopupAction()
    {
        $em = $this->getDoctrine()->getManager();
        //global club article settings 
        $getGlobalClubSettings = $em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($this->container->get('club')->get('id'));
        //formatting the number to 2 digits with leading zeros
        $getGlobalClubSettings['timeperiodStartDay'] = sprintf("%02d", $getGlobalClubSettings['timeperiodStartDay']);
        $getGlobalClubSettings['timeperiodStartMonth'] = sprintf("%02d", $getGlobalClubSettings['timeperiodStartMonth']);

        return $this->render('InternalArticleBundle:TimePeriod:timePeriodPopup.html.twig', array('clubSettings' => $getGlobalClubSettings));
    }

    /**
     * Function to save time period data
     *
     * @return object JSON Response Object
     */
    public function timePeriodSaveAction()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $timeData = array('dayVal' => $request->get('dayVal'), 'monthVal' => $request->get('monthVal'));
        $clubId = $this->get('club')->get('id');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->saveTimePeriod($clubId, $timeData);
        //return data for sidebar
        //FAIR-2524
        $timePeriodReturn = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmsArticle')->getTimeperiodArticle($clubId, $this->get('club')->get('clubHeirarchy'));

        return new JsonResponse(array('status' => 'SUCCESS', 'result' => $timePeriodReturn, 'noparentload' => '1', 'flash' => $this->container->get('translator')->trans('ARTICLE_TIME_PERIOD_SAVE_SUCCESS_MESSAGE')));
    }
}
