<?php

/**
 * FgCmsPortraitContainer
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgCmsPortraitContainer - The wrapper class to handle change in contact portrait element container, column and portrais per row settings 
 * on page container resize and on updating per row settings in step 2
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgCmsPortraitContainer
{

    /**
     * The constructor function
     *
     * @param object $container container:\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * This function is used to resize the portrait element on container update
     * 
     * @param int $containerId The container id
     */
    public function adjustPortraitElementOnContainerResize($containerId)
    {
        $portraitDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->getPortraitElementsInAContainer($this->club->get('id'), $containerId);
        foreach ($portraitDetails as $portraitDetail) {
            $pageContainerSize = $portraitDetail['pageContainerSize'];
            $portraitsPerRow = $portraitDetail['portraitsPerRow'];
            $optimalPortraitContainerSize = intdiv($pageContainerSize, $portraitsPerRow);
            $maxPortraitContainerSize = ($optimalPortraitContainerSize > 0) ? $optimalPortraitContainerSize : 1;
            if (($portraitsPerRow * $portraitDetail['portraitContainerSize']) > $pageContainerSize) {
                if ($portraitDetail['portraitsPerRow'] !== 1) {
                    $newPortraitsPerRow = $this->getPortraitsPerRowForModifiedContainer($pageContainerSize, $maxPortraitContainerSize, $portraitsPerRow);
                    $this->updatePortraitsPerRow($portraitDetail['portraitId'], $newPortraitsPerRow);
                } else {
                    $this->resizePortraitContainerColumns($portraitDetail['portraitContainerId'], $maxPortraitContainerSize);
                }
            }
            if ($maxPortraitContainerSize !== $portraitDetail['portraitContainerSize']) {
                $this->updatePortraitContainerSize($portraitDetail['portraitId'], $maxPortraitContainerSize);
            }
        }
    }
 
    /**
     * This function is used to adjust portrait element on sidebar include and exclude action
     * 
     * @param array $containerIdsArr The container array
     */
    public function adjustPortraitElementOnSidebarIncludeExclude($containerIdsArr)
    {
        foreach ($containerIdsArr as $containerId) {
            $this->adjustPortraitElementOnContainerResize($containerId);
        }
    }
    
    /**
     * This function is used to get the portraits per row for the modified container
     * 
     * @param int $pageContainerSize        The page container size
     * @param int $maxPortraitContainerSize The maximum portrait container size
     * @param int $portraitsPerRow          The portraits per row
     * 
     * @return int $portraitsPerRow The final portraits per row settings
     */
    private function getPortraitsPerRowForModifiedContainer($pageContainerSize, $maxPortraitContainerSize, $portraitsPerRow)
    {
        do {
            $portraitsPerRow--;
            $newPortraitContainerSize = intdiv($pageContainerSize, $portraitsPerRow);
        } while (($newPortraitContainerSize > $maxPortraitContainerSize) && ($portraitsPerRow > 1));

        return $portraitsPerRow;
    }

    /**
     * This function is used to resize the portrait container columns
     * 
     * @param int $containerId The container id
     * @param int $columnSize  The column size
     */
    private function resizePortraitContainerColumns($containerId, $columnSize)
    {
        $columnDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->getPortraitContainerColumnDetails($containerId);
        $totalColumnSize = array_sum(array_column($columnDetails, 'portraitColumnSize'));
        if ($totalColumnSize > $columnSize) {
            //reduce the column number or size accordingly
            $this->reducePortraitContainerColumns($columnDetails, $totalColumnSize, $columnSize);
        }
    }

    /**
     * This function is used to reduce the number of portrait container columns
     * 
     * @param array $columnDetails   The column details array
     * @param int   $totalColumnSize The total column size
     * @param int   $maxColumnSize   The maximum column size
     */
    private function reducePortraitContainerColumns($columnDetails, $totalColumnSize, $maxColumnSize)
    {
        $columnCount = count($columnDetails);
        //if column count is 1 just reduce the column size
        if ($columnCount != 1) {
            foreach ($columnDetails as $columnIndex => $columnDetail) {
                $lastColumnSize = $columnDetail['portraitColumnSize'];
                while (($totalColumnSize > $maxColumnSize) && ($lastColumnSize > 0)) {
                    $lastColumnSize--;
                    $totalColumnSize--;
                    if ($lastColumnSize == 0) {
                        //move data to previous column only if last column was of size 1 ie. now 0
                        $this->moveAllDatasInLastColumnToPrevious($columnDetails, $columnIndex);
                    }
                }
                if ($totalColumnSize <= $maxColumnSize) {
                    $this->updatePortraitContainerColumnSize($columnDetail['portraitColumnId'], $lastColumnSize);
                    break;
                }
            }
        } else {
            $this->updatePortraitContainerColumnSize($columnDetails[0]['portraitColumnId'], $maxColumnSize);
        }
    }

    /**
     * This function is used to move all datas in last column to just previous column
     * 
     * @param array $columnDetails The column details array
     * @param int   $columnIndex   The column index
     */
    private function moveAllDatasInLastColumnToPrevious($columnDetails, $columnIndex)
    {
        //since columns are sorted by order DESC take index + 1
        $previousColumnIndex = $columnIndex + 1;
        $oldColumnId = $columnDetails[$columnIndex]['portraitColumnId'];
        $newColumnId = $columnDetails[$previousColumnIndex]['portraitColumnId'];
        //get max sort order in db
        $maxSortOrder = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->getMaxSortOrderInAPortaitColumn($newColumnId);

        //update table column entries in fg_cms_table columns with new column Id, while updating keep the sortorder as max(sortOrder) + 1
        if ($maxSortOrder) {
            $this->updatePortraitContainerColumnDatas($oldColumnId, $newColumnId, $maxSortOrder);
        }
        
        // delete the old column id.
        $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->deletePortraitContainerColumn($oldColumnId);
    }

    /**
     * This function is used to update the portraits per row 
     * 
     * @param int $portraitId      The portrait id
     * @param int $portraitsPerRow The portraits per row
     */
    private function updatePortraitsPerRow($portraitId, $portraitsPerRow)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->updatePortraitsPerRow($portraitId, $portraitsPerRow, $this->contact->get('id'));
    }

    /**
     * This function is used to update the portrait container size
     * 
     * @param int $portraitId            The portrait id
     * @param int $portraitContainerSize The portrait container size
     */
    private function updatePortraitContainerSize($portraitId, $portraitContainerSize)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainer')->updatePortraitContainerSize($portraitId, $portraitContainerSize);
    }

    /**
     * This function is used to update the portrait container column details
     * 
     * @param int $oldColumnId  The old column id
     * @param int $newColumnId  The new column id
     * @param int $maxSortOrder The maximum sort order
     */
    private function updatePortraitContainerColumnDatas($oldColumnId, $newColumnId, $maxSortOrder)
    {
        $cmsObj = new CmsPdo($this->container);
        $cmsObj->changePortraitContainerColumnForSelectedData($oldColumnId, $newColumnId, $maxSortOrder);
    }

    /**
     * This function is used to update the portrait container column size
     * 
     * @param int $portraitColumnId   The portrait column id
     * @param int $portraitColumnSize The portrait column size
     */
    private function updatePortraitContainerColumnSize($portraitColumnId, $portraitColumnSize)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->updatePortraitContainerColumnSize($portraitColumnId, $portraitColumnSize);
    }
    
    public function adjustPortraitDisplayOnPortraitsPerRowUpdate($portraitId)
    {
        $portraitDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->getPortraitDetails($portraitId);
        foreach ($portraitDetails as $portraitDetail) {
            $pageColumnSize = $portraitDetail['pageColumnSize'];
            $portraitsPerRow = $portraitDetail['portraitsPerRow'];
            $maxPortraitContainerSize = intdiv($pageColumnSize, $portraitsPerRow);
            if ($portraitDetail['portraitContainerSize'] > $maxPortraitContainerSize) {
                $this->resizePortraitContainerColumns($portraitDetail['portraitContainerId'], $maxPortraitContainerSize);
            }
            //update portrait container size to new value
            $this->updatePortraitContainerSize($portraitId, $maxPortraitContainerSize);
        }
    }
}
