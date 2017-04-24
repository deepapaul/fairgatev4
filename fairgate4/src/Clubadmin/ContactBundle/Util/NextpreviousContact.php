<?php

namespace Clubadmin\ContactBundle\Util;

use Common\UtilityBundle\Util\NextpreviousBase;
use Clubadmin\ContactBundle\Util\ContactlistData;

/**
 * For handle the Next and previous functionality
 *
 * @author PITSolutions <pit@solutions.com>
 */
class NextpreviousContact extends NextpreviousBase
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
        $displayLength = $this->session->get('filteredContactDetailsDisplayLength');
        $iSortCol = $this->session->get('filteredContactDetailsiSortCol_0');
        $mDataProp = $this->session->get('filteredContactDetailsmDataProp');
        $sSortDir = $this->session->get('filteredContactDetailsSortDir_0');
        $filteredContactDetailsSearch = $this->session->get('filteredContactDetailsSearch');
        $filteredContactDetailsFilterdata = $this->session->get('filteredContactDetailsFilterdata');
        $aColumns = $this->session->get('columnsArray');
        $tableField = $this->session->get('tableField');
        $contactType = $this->session->get('contactType');
        //Session to check for new query call
        $sessionFlag = $this->session->get('flag');
        $nextPreviousContactListData = $this->session->get('nextPreviousContactListData');

        return array('nextPreviousContactListData' => $nextPreviousContactListData, 'sessionFlag' => $sessionFlag, 'contactType' => $contactType, 'tableField' => $tableField, 'aColumns' => $aColumns, 'displayLength' => $displayLength, 'iSortCol' => $iSortCol, 'mDataProp' => $mDataProp, 'sSortDir' => $sSortDir, 'filteredContactDetailsSearch' => $filteredContactDetailsSearch, 'filteredContactDetailsFilterdata' => $filteredContactDetailsFilterdata);
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
    public function nextPreviousContactData($contactId, $contact, $offset, $url, $param1, $param2, $flag = 0)
    {
        // Session values in the contact listing page
        $allSessionValues = $this->getAllSessionForNextPre();
        $currentIndexValue = $offset;

        // Set object variables from the session values
        $contactlistData = new ContactlistData($contactId, $this->container, $allSessionValues['contactType']);

        // Function to set $contactlistData object variables from session
        $this->setContactDataVariables($contactlistData, $allSessionValues['tableField'], $allSessionValues['aColumns'], $allSessionValues['filteredContactDetailsSearch'], $allSessionValues['filteredContactDetailsFilterdata'], $allSessionValues['iSortCol'], $allSessionValues['sSortDir'], $allSessionValues['mDataProp'], $allSessionValues['displayLength']);

        // Calling the Contact list function to get the same query used in the contact listing
        $listquery = $contactlistData->getContactData(true, 1, $currentIndexValue);
        $contactlistDatas = $this->checkNextPreviousFlag($flag, $allSessionValues['nextPreviousContactListData'], $contact, $allSessionValues['sessionFlag'], $listquery, 'nextPreviousContactListData', $this->session, $this->em);

        // Section for calculating next and previous five results and changing the links according to that
        $existFlag = 0;
        $pre = '';
        $next = '';
        $arrayCount = 0;
        foreach ($contactlistDatas as $key => $value) {
            $arrayCount++;
            if ($value['id'] == $contact) {
                $existFlag = 1;
                if (array_key_exists($key - 1, $contactlistDatas)) {
                    if ($offset != 0) {
                        $pre = $contactlistDatas[$key - 1]['id'];
                    }
                } else if ($currentIndexValue == 0) {
                    $pre = '';
                } else {
                    $result = $this->nextPreviousContactData($contactId, $contact, $offset, $url, $param1, $param2, $newIterationFlag = 1);

                    return $result;
                }
                if (array_key_exists($key + 1, $contactlistDatas)) {
                    $next = $contactlistDatas[$key + 1]['id'];
                } else if (count($contactlistDatas) < 10) {
                    $next = '';
                } else if (count($contactlistDatas) == $arrayCount) {
                    $next = '';
                } else {
                    $result = $this->nextPreviousContactData($contactId, $contact, $offset, $url, $param1, $param2, $newIterationFlag = 1);

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
