<?php

/**
 * FgCmsPortraitDisplay
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgCmsPortraitDisplay - The wrapper class to handle functionalities on contact portrait element portrait display step 3
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgCmsPortraitDisplay
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
        $this->contactId = $this->contact->get('id');

        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * This function is used to save the portrait element display step 3
     * 
     * @param int   $tableId The table id
     * @param array $data    The data to save
     * 
     * @return boolean
     */
    public function savePortraitElementDisplay($tableId, $data)
    {
        $this->portraitId = $tableId;
        foreach ($data as $portraitContainerId => $portraitContainerArr) {
            if ($portraitContainerArr['is_deleted'] == 1) {
                $this->deletePortraitContainer($portraitContainerId);
                continue;
            }
            $portraitContainerId = $this->processPortraitContainer($portraitContainerId, $portraitContainerArr);
            foreach ($portraitContainerArr['column'] as $portraitContainerColumnId => $portraitContainerColumnArr) {
                if ($portraitContainerColumnArr['is_column_delete'] == 1) {
                    $this->deletePortraitContainerColumn($portraitContainerColumnId);
                    continue;
                }
                $portraitContainerColumnId = $this->processPortraitContainerColumn($portraitContainerId, $portraitContainerColumnId, $portraitContainerColumnArr);
                if (isset($portraitContainerColumnArr['data'])) {
                    foreach ($portraitContainerColumnArr['data'] as $columnType => $portraitData) {
                        $this->processPortraitColumnDatas($portraitContainerColumnId, $columnType, $portraitData);
                    }
                }
            }
        }
        $this->deleteTableColumnI18nEntries();
        $this->updateContactTable('stage3');

        return true;
    }

    /**
     * This function is used to create or update the portrait container details
     * 
     * @param int   $portraitContainerId   The portrait container id
     * @param array $portraitContainerData The portrait container data
     * 
     * @return int $portraitContainerId The portrait container id
     */
    private function processPortraitContainer($portraitContainerId, $portraitContainerData)
    {
        if (isset($portraitContainerData['size']) || isset($portraitContainerData['sortOrder'])) {
            $portraitContainerObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainer')->find($portraitContainerId);
            if (!empty($portraitContainerObj)) {
                $portraitContainerId = $this->updatePortraitContainer($portraitContainerId, $portraitContainerData);
            } else {
                $portraitContainerId = $this->createPortraitContainer($portraitContainerData);
            }
        }

        return $portraitContainerId;
    }

    /**
     * This function is used to create a portrait container
     * 
     * @param array $portraitContainerData The portrait container data
     * 
     * @return int $portraitContainerId The portrait container id
     */
    private function createPortraitContainer($portraitContainerData)
    {
        $portraitContainerId = $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainer')->createPortraitContainer($this->portraitId, $portraitContainerData);

        return $portraitContainerId;
    }

    /**
     * This function is used to update a portrait container
     * 
     * @param int   $portraitContainerId   The portrait container id
     * @param array $portraitContainerData The portrait container data
     * 
     * @return int $portraitContainerId The portrait container id
     */
    private function updatePortraitContainer($portraitContainerId, $portraitContainerData)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainer')->updatePortraitContainer($portraitContainerId, $portraitContainerData);

        return $portraitContainerId;
    }

    /**
     * This function is used to delete a portrait container
     * 
     * @param int $portraitContainerId The portrait container id
     */
    private function deletePortraitContainer($portraitContainerId)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainer')->deletePortraitContainer($portraitContainerId);
    }

    /**
     * This function is used to update the contact table
     * 
     * @param string $stage The wizard stage 
     */
    private function updateContactTable($stage = '')
    {
        if ($stage != '') {
            $this->em->getRepository('CommonUtilityBundle:FgCmsContactTable')->updateContactTableStage($this->portraitId, $this->contactId, $stage);
        }
    }

    /**
     * This function is used to update or create the portrait container column
     * 
     * @param int   $containerId The portrait container id
     * @param int   $columnId    The portrait container column id
     * @param array $columnData  The portrait container column data
     * 
     * @return int $portraitContainerColumnId The portrait container column id
     */
    private function processPortraitContainerColumn($containerId, $columnId, $columnData)
    {
        $containerColumnObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->find($columnId);
        if (!empty($containerColumnObj)) {
            $portraitContainerColumnId = $this->updatePortraitContainerColumn($columnId, $columnData);
        } else {
            $portraitContainerColumnId = $this->createPortraitContainerColumn($containerId, $columnData);
        }

        return $portraitContainerColumnId;
    }

    /**
     * This function is used to create a portrait container column 
     * 
     * @param int   $containerId The portrait container id
     * @param array $columnData  The portrait container column data
     * 
     * @return int $columnId The portrait container column id
     */
    private function createPortraitContainerColumn($containerId, $columnData)
    {
        $columnId = $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->createPortraitContainerColumn($containerId, $columnData);

        return $columnId;
    }

    /**
     * This function is used to update a portrait container column 
     * 
     * @param int   $columnId   The portrait container column id
     * @param array $columnData The portrait container column data
     * 
     * @return int $columnId The portrait container column id
     */
    private function updatePortraitContainerColumn($columnId, $columnData)
    {
        if (isset($columnData['columnSize']) || isset($columnData['sortOrder'])) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->updatePortraitContainerColumn($columnId, $columnData);
        }

        return $columnId;
    }

    /**
     * This function is used to delete a portrait container column 
     * 
     * @param int $columnId The portrait container column id
     */
    private function deletePortraitContainerColumn($columnId)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPortraitContainerColumn')->deletePortraitContainerColumn($columnId);
    }

    /**
     * This function is used to update or create or delete the portrait table column data
     * 
     * @param int    $portraitContainerColumnId The portrait container column id
     * @param string $columnType                The column type
     * @param array  $portraitColumnData        The portrait column data
     */
    private function processPortraitColumnDatas($portraitContainerColumnId, $columnType, $portraitColumnData)
    {
        foreach ($portraitColumnData as $columnDataId => $columnData) {
            if ($columnData['is_deleted'] == 1) {
                $this->deleteTableColumn($columnDataId);
                continue;
            }
            $this->processTableColumns($columnType, $columnDataId, $columnData, $portraitContainerColumnId);
        }
    }

    /**
     * This function is used to update or create or delete the portrait table column data
     * 
     * @param string $columnType                The column type
     * @param int    $columnDataId              The column data id
     * @param array  $columnData                The column data
     * @param int    $portraitContainerColumnId The portrait container column id
     */
    private function processTableColumns($columnType, $columnDataId, $columnData, $portraitContainerColumnId)
    {
        $tableColumnObj = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->find($columnDataId);
        if (!empty($tableColumnObj)) {
            $columnData['columnId'] = $portraitContainerColumnId;
            $tableColumnId = $this->updateTableColumn($columnType, $tableColumnObj, $columnData);
        } else {
            $tableColumnId = $this->createTableColumn($columnType, $columnData, $portraitContainerColumnId);
        }
        $this->insertOrUpdateTableColumnsI18n($tableColumnId, json_decode($columnData['label'], true));
    }

    /**
     * This function is used to create a portrait table column data
     * 
     * @param string $columnType                The column type
     * @param array  $tableColumnData           The table column data
     * @param int    $portraitContainerColumnId The portrait container column id
     * 
     * @return int $tableColumnId The table column id
     */
    private function createTableColumn($columnType, $tableColumnData, $portraitContainerColumnId)
    {
        $tableColumnId = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->createPortraitTableColumn($this->portraitId, $columnType, $tableColumnData, $portraitContainerColumnId, $this->club->get('id'), $this->club->get('club_default_lang'));

        return $tableColumnId;
    }

    /**
     * This function is used to update a portrait table column data
     * 
     * @param string $columnType      The column type
     * @param object $tableColumnObj  The table column object
     * @param array  $tableColumnData The table column data
     * 
     * @return int $tableColumnId The table column id
     */
    private function updateTableColumn($columnType, $tableColumnObj, $tableColumnData)
    {
        $tableColumnId = $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->updatePortraitTableColumn($columnType, $tableColumnObj, $tableColumnData, $this->club->get('id'), $this->club->get('club_default_lang'));

        return $tableColumnId;
    }

    /**
     * This function is used to delete a portrait table column data
     * 
     * @param int $tableColumnId The table column id
     */
    private function deleteTableColumn($tableColumnId)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableColumns')->deletePortraitTableColumn($tableColumnId);
    }

    /**
     * This function is used to insert or update the table column i18n entries
     * 
     * @param int   $tableColumnId The table column id
     * @param array $titleArr      The titles array with language as key
     */
    private function insertOrUpdateTableColumnsI18n($tableColumnId, $titleArr)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsContactTableColumnsI18n')->insertOrUpdateTableColumnsI18n($tableColumnId, $titleArr, $this->club->get('club_languages'));
    }

    /**
     * This function is used to delete the table column i18n entries
     */
    private function deleteTableColumnI18nEntries()
    {
        //delete all corresponding entries from i18n table which have is_deleted = 1 in main table
        $cmsObj = new CmsPdo($this->container);
        $cmsObj->deleteTableColumnI18nEntriesInATable($this->portraitId);
    }
}
