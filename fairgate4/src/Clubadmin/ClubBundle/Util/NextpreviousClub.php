<?php

namespace Clubadmin\ClubBundle\Util;

use Common\UtilityBundle\Util\NextpreviousBase;
use Clubadmin\ClubBundle\Util\ClublistData;

/**
 * For handle the Next and previous functionality
 *
 * @author PITSolutions <pit@solutions.com>
 */
class NextpreviousClub extends NextpreviousBase
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
    private function getAllSessionForNextPreClub()
    {

        $displayLength = $this->session->get('filteredClubDetailsDisplayLength');
        $iSortCol = $this->session->get('filteredClubDetailsiSortCol_0');
        $mDataProp = $this->session->get('filteredClubDetailsmDataProp');
        $sSortDir = $this->session->get('filteredClubDetailsSortDir_0');
        $filteredClubDetailsSearch = $this->session->get('filteredClubDetailsSearch');
        $filteredClubDetailsFilterdata = $this->session->get('filteredClubDetailsFilterdata');
        $aColumns = $this->session->get('clubcolumnsArray');
        $tableField = $this->session->get('clubtableField');
        //Session to check for new query call
        $sessionFlag = $this->session->get('clubflag');
        $nextPreviousClubListData = $this->session->get('nextPreviousClubListData');

        return array('nextPreviousClubListData' => $nextPreviousClubListData, 'sessionFlag' => $sessionFlag, 'tableField' => $tableField, 'aColumns' => $aColumns, 'displayLength' => $displayLength, 'iSortCol' => $iSortCol, 'mDataProp' => $mDataProp, 'sSortDir' => $sSortDir, 'filteredClubDetailsSearch' => $filteredClubDetailsSearch, 'filteredClubDetailsFilterdata' => $filteredClubDetailsFilterdata);
    }

    /**
     * Function of next and previous buttons in the header
     *
     * @param int $club   Club Id
     * @param int $offset Offset value
     * @param int $url    Current url
     * @param int $param1 Url param
     * @param int $param2 Url param
     * @param int $flag   Flag
     *
     * @return array
     */
    public function nextPreviousClubData($contactId, $club, $offset, $url, $param1, $param2, $flag = 0)
    {
        // Session values in the club listing page
        $allSessionValues = $this->getAllSessionForNextPreClub();
        $currentIndexValue = $offset;

        // Set object variables from the session values
        $clublistData = new ClublistData($contactId, $this->container);

        // Function to set $clublistDatas object variables from session
        $this->setContactDataVariables($clublistData, $allSessionValues['tableField'], $allSessionValues['aColumns'], $allSessionValues['filteredClubDetailsSearch'], $allSessionValues['filteredClubDetailsFilterdata'], $allSessionValues['iSortCol'], $allSessionValues['sSortDir'], $allSessionValues['mDataProp'], $allSessionValues['displayLength']);

        // Calling the club list function to get the same query used in the club listing
        $listquery = $clublistData->getClubData(true, 1, $currentIndexValue);
//pass container instead of entity managet to call service
        $clublistDatas = $this->checkNextPreviousFlag($flag, $allSessionValues['nextPreviousClubListData'], $club, $allSessionValues['sessionFlag'], $listquery, 'nextPreviousClubListData', $this->session, $this->container,true);

        // Section for calculating next and previous five results and changing the links according to that
        $existFlag = 0;
        $pre = '';
        $next = '';
        $arrayCount = 0;
        foreach ($clublistDatas as $key => $value) {
            $arrayCount++;
            if ($value['id'] == $club) {
                $existFlag = 1;
                if (array_key_exists($key - 1, $clublistDatas)) {
                    if ($offset != 0) {
                        $pre = $clublistDatas[$key - 1]['id'];
                    }
                } else if ($currentIndexValue == 0) {
                    $pre = '';
                } else {
                    $result = $this->nextPreviousClubData($contactId, $club, $offset, $url, $param1, $param2, $newIterationFlag = 1);

                    return $result;
                }
                if (array_key_exists($key + 1, $clublistDatas)) {
                    $next = $clublistDatas[$key + 1]['id'];
                } else if (count($clublistDatas) < 10) {
                    $next = '';
                } else if (count($clublistDatas) == $arrayCount) {
                    $next = '';
                } else {
                    $result = $this->nextPreviousClubData($contactId, $club, $offset, $url, $param1, $param2, $newIterationFlag = 1);

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
