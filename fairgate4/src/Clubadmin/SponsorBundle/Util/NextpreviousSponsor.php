<?php

namespace Clubadmin\SponsorBundle\Util;

use Common\UtilityBundle\Util\NextpreviousBase;
use Clubadmin\ContactBundle\Util\ContactlistData;

/**
 * For handle the Next and previous functionality
 *
 * @author PITSolutions <pit@solutions.com>
 */
class NextpreviousSponsor extends NextpreviousBase
{

    /**
     * $em
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $container
     * @var object {container object}
     */
    private $container;

    /**
     * $session
     * @var object {Session object}
     */
    private $session;

    /**
     * Constructor for initial setting
     *
     * @param type $container          Container Object
     * @param type $terminologyService Terminology service object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->session = $this->container->get('session');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function getting session values for next and previous
     *
     * @return array
     */
    private function getAllSessionForNextPre()
    {
        $displayLength = $this->session->get('filteredSponsorDetailsDisplayLength');
        $iSortCol = $this->session->get('filteredSponsorDetailsiSortCol_0');
        $mDataProp = $this->session->get('filteredSponsorDetailsmDataProp');
        $sSortDir = $this->session->get('filteredSponsorDetailsSortDir_0');
        $filteredSponsorDetailsSearch = $this->session->get('filteredSponsorDetailsSearch');
        $filteredSponsorDetailsFilterdata = $this->session->get('filteredSponsorDetailsFilterdata');
        $aColumns = $this->session->get('sponsorcolumnsArray');
        $tableField = $this->session->get('sponsortableField');
        $contactType = $this->session->get('contactType');
        //Session to check for new query call
        $sessionFlag = $this->session->get('sponsorflag');
        $nextPreviousSponsorListData = $this->session->get('nextPreviousSponsorListData');

        return array('nextPreviousSponsorListData' => $nextPreviousSponsorListData, 'sessionFlag' => $sessionFlag, 'contactType' => $contactType, 'tableField' => $tableField, 'aColumns' => $aColumns, 'displayLength' => $displayLength, 'iSortCol' => $iSortCol, 'mDataProp' => $mDataProp, 'sSortDir' => $sSortDir, 'filteredSponsorDetailsSearch' => $filteredSponsorDetailsSearch, 'filteredSponsorDetailsFilterdata' => $filteredSponsorDetailsFilterdata);
    }

    /**
     * Function of next and previous buttons in the header
     *
     * @param int $contact Contact Id
     * @param int $offset  Offset value
     * @param int $url     Current url
     * @param int $param1  Url param
     * @param int $param2  Url param
     * @param int $flag    Flag
     *
     * @return array
     */
    public function nextPreviousSponsorData($contactId, $contact, $offset, $url, $param1, $param2, $flag = 0)
    {
        // Session values in the contact listing page
        $allSessionValues = $this->getAllSessionForNextPre();
        $currentIndexValue = $offset;

        // Set object variables from the session values
        $sponsorlistData = new ContactlistData($contactId, $this->container, $allSessionValues['contactType']);

        // Function to set $contactlistData object variables from session
        $this->setContactDataVariables($sponsorlistData, $allSessionValues['tableField'], $allSessionValues['aColumns'], $allSessionValues['filteredSponsorDetailsSearch'], $allSessionValues['filteredSponsorDetailsFilterdata'], $allSessionValues['iSortCol'], $allSessionValues['sSortDir'], $allSessionValues['mDataProp'], $allSessionValues['displayLength']);

        // Calling the Contact list function to get the same query used in the contact listing
        $listquery = $sponsorlistData->getContactData(true, 1, $currentIndexValue);
        $sponsorlistDatas = $this->checkNextPreviousFlag($flag, $allSessionValues['nextPreviousSponsorListData'], $contact, $allSessionValues['sessionFlag'], $listquery, 'nextPreviousSponsorListData', $this->session, $this->em);

        // Section for calculating next and previous five results and changing the links according to that
        $existFlag = 0;
        $pre = '';
        $next = '';
        $arrayCount = 0;
        foreach ($sponsorlistDatas as $key => $value) {
            $arrayCount++;
            if ($value['id'] == $contact) {
                $existFlag = 1;
                if (array_key_exists($key - 1, $sponsorlistDatas)) {
                    if ($offset != 0) {
                        $pre = $sponsorlistDatas[$key - 1]['id'];
                    }
                } else if ($currentIndexValue == 0) {
                    $pre = '';
                } else {
                    $result = $this->nextPreviousBtnAction($contact, $offset, $url, $param1, $param2, $newIterationFlag = 1);

                    return $result;
                }
                if (array_key_exists($key + 1, $sponsorlistDatas)) {
                    $next = $sponsorlistDatas[$key + 1]['id'];
                } else if (count($sponsorlistDatas) < 10) {
                    $next = '';
                } else if (count($sponsorlistDatas) == $arrayCount) {
                    $next = '';
                } else {
                    $result = $this->nextPreviousBtnAction($contact, $offset, $url, $param1, $param2, $newIterationFlag = 1);

                    return $result;
                }
            }
        }

        // Returning the next and previous links to the corresponding page
        $paginationValue['previous'] = $pre;
        $paginationValue['next'] = $next;
        $paginationValue['url'] = $url;
        $paginationValue['param1'] = $param1;
        $paginationValue['param2'] = $param2;

        return $paginationValue;
    }
}
