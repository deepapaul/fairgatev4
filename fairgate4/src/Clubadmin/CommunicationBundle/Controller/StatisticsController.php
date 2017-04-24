<?php

/**
 * SettingsController
 *
 * This controller was created for displaying newsletter statistics and simple mail statistics
 *
 * @package    ClubadminCommunicationBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgSettings;

class StatisticsController extends FgController
{

    protected $filterType;
    protected $year;
    protected $dateFrom;
    protected $dateTo;

    /**
     * Method for displaying the statistics of newsletters
     * @return template
     */
    public function newsletterAction()
    {
        $currentYear = date("Y");
        $startYear = $currentYear - 5;
        $breadCrumb = array(
            'breadcrumb_data' => array()
        );

        return $this->render('ClubadminCommunicationBundle:Statistics:newsletter.html.twig', array(
                "currentYear" => $currentYear,
                "startYear" => $startYear,
                'breadCrumb' => $breadCrumb
        ));
    }

    /**
     * Method for getting the ajax response of the newsletters statistics
     * @param Request $request array of filter values
     * @return Response
     */
    public function newsletterAjaxAction(Request $request)
    {
        $clubId = $this->clubId;
        $this->filterType = $request->request->get('filtertype');
        $this->year = $request->request->get('year');
        $this->dateFrom = $request->request->get('date_from');
        $this->dateTo = $request->request->get('date_to');
        $this->setDates();
        $em = $this->getDoctrine()->getManager();
        $statistics = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')
            ->getNewsletterStatistics($clubId, $this->dateFrom, $this->dateTo);
        $return = json_encode($statistics[0]);

        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
    /*
     * Method for displaying the statistics of simple mail
     * @return template
     */

    public function simpleMailAction()
    {
        $currentYear = date("Y");
        $startYear = $currentYear - 5;
        $breadCrumb = array(
            'breadcrumb_data' => array()
        );

        return $this->render('ClubadminCommunicationBundle:Statistics:simplemail.html.twig', array(
                "currentYear" => $currentYear,
                "startYear" => $startYear,
                'breadCrumb' => $breadCrumb
        ));
    }
    /*
     * Method for getting the ajax response of the simple mail statistics
     * @return json array
     */

    public function simpleMailAjaxAction(Request $request)
    {
        $clubId = $this->clubId;
        $this->filterType = $request->request->get('filtertype');
        $this->year = $request->request->get('year');
        $this->dateFrom = $request->request->get('date_from');
        $this->dateTo = $request->request->get('date_to');
        $this->setDates();
        $em = $this->getDoctrine()->getManager();
        $statistics = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')
            ->getSimpleMailStatistics($clubId, $this->dateFrom, $this->dateTo);
        $return = json_encode($statistics[0]);

        return new Response($return, 200, array('Content-Type' => 'application/json'));
    }
    /*
     * Function for setting from date and to date
     * with respect to the filter type selected
     */

    public function setDates()
    {
        $format = FgSettings::getPhpDateFormat();
        if ($this->filterType === 'datebetween') {
            if ($this->dateFrom !== "") {
                $this->dateFrom = \DateTime::createFromFormat($format, $this->dateFrom)->format('Y-m-d');
            }
            if ($this->dateTo !== "") {
                $this->dateTo = \DateTime::createFromFormat($format, $this->dateTo)->format('Y-m-d');
            }
        } else if ($this->filterType === 'year') {
            if ($this->year !== "") {
                $this->dateFrom = $this->year . "-01-01";
                $this->dateTo = $this->year . "-12-31";
            }
        } else if ($this->filterType === 'entire') {
            $this->dateFrom = "";
            $this->dateTo = "";
        }
    }
}
