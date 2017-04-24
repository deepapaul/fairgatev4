<?php
namespace Common\UtilityBundle\Util;


/**
 * For handle the next previous base functions
 *
 * @author PITSolutions <pit@solutions.com>
 */
class NextpreviousBase
{
    /**
     * Function to check flags and session data of already saved listing data. Otherwise need to call the query again to get the data
     * 
     * @param int    $flag                        Flag to check recursive call
     * @param Array  $nextPreviousContactListData Contact data from session
     * @param int    $contact                     Contact id
     * @param int    $sessionFlag                 Flag value in the session
     * @param int    $listquery                   Generated query
     * @param String $nextPreDataSessionName      Next previous data session name
     * @param Object $session                     Session object
     * @param Object $em                          Entity object
     *
     * @return array
     */
    public function checkNextPreviousFlag($flag, $nextPreviousContactListData, $contact, $sessionFlag, $listquery, $nextPreDataSessionName, $session, $em) {
        $callQueryFlag = 1;

        // Flag to indicate that the function is called inside the same function recursively.
        if ($flag == 0) {

            // Checking whether the contact list data already exists in the session with the same contact id.
            // If then no need to call the query again
            if (isset($nextPreviousContactListData) && !empty($nextPreviousContactListData)) {
                foreach ($nextPreviousContactListData as $val) {
                    if ($val['id'] == $contact) {
                        $callQueryFlag = 0;
                        $contactlistDatas = $nextPreviousContactListData;
                    }
                }
            }
        }

        // Checking whether to call the query again to get the contact data
        if ($callQueryFlag == 1 || $sessionFlag == 1) {
            $contactlistDatas = $em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($listquery);
            $session->set($nextPreDataSessionName, $contactlistDatas);
        }

        return $contactlistDatas;
    }

    /**
     * Function to set session values to the contact data object variables
     * 
     * @param int $listDataObject                   Contact list object
     * @param int $tableField                       Table field session value
     * @param int $aColumns                         Columns of listing
     * @param int $filteredContactDetailsSearch     Search value of listing
     * @param int $filteredContactDetailsFilterdata Filter value in listing
     * @param int $iSortCol                         Sort column
     * @param int $sSortDir                         Sort type
     * @param int $mDataProp                        Sort data
     * @param int $displayLength                    Length of listing data
     */
    public function setContactDataVariables($listDataObject, $tableField, $aColumns, $filteredContactDetailsSearch, $filteredContactDetailsFilterdata, $iSortCol, $sSortDir, $mDataProp, $displayLength) {
        $listDataObject->tableFieldValues = $tableField;
        $listDataObject->aoColumns = $aColumns;
        $listDataObject->searchval = $filteredContactDetailsSearch;
        $listDataObject->filterValue = $filteredContactDetailsFilterdata;
        $listDataObject->roleFilter = '';
        $listDataObject->sortColumnValue = $iSortCol;
        $listDataObject->sSortDir = $sSortDir;
        $listDataObject->dataTableColumnData[$listDataObject->sortColumnValue[0]['column']]['data'] = $mDataProp;
        $listDataObject->displayLength = $displayLength;
    }
}
