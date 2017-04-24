<?php

/**
 * FgCmsPortrait
 */
namespace Website\CMSBundle\Util;

/**
 * FgCmsPortrait - The wrapper class to handle functionalities on contact portrait  elements wizard steps
 *
 * @package         Website
 * @subpackage      CMS
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgCmsPortrait
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
        $this->clubId = $this->club->get('id');

        $this->contact = $this->container->get('contact');
        $this->contactId = $this->contact->get('id');

        $this->em = $this->container->get('doctrine')->getManager();
        $this->translator = $this->container->get('translator');
    }

    /**
     * Function to format data
     * 
     * @param int   $portraitId             portrait element  id
     * @param array $containerDetails Content details
     * 
     * @return array
     */
    public function formatContainerData($portraitId, $containerDetails)
    {
        $previousContainerId = 0;
        $previousColumnId = 0;
        $previousDataId = 0;
        $newPortraitContainerArray = array();
        if (count($containerDetails) > 0) {
            $newPortraitContainerArray = array("portraitElement" => array('id' => $portraitId));
            //sidebar details

            $newPortraitContainerArray['portraitElement']['portraitPerRow'] = $containerDetails[0]['portraitPerRow'];
            $newPortraitContainerArray['portraitElement']['tableSearch'] = $containerDetails[0]['tableSearch'];
            $newPortraitContainerArray['portraitElement']['rowPerpage'] = $containerDetails[0]['rowPerpage'];
            $newPortraitContainerArray['portraitElement']['elementId'] = $containerDetails[0]['elementId'];
            $newPortraitContainerArray['portraitElement']['columnWidth'] = $containerDetails[0]['columnWidth'];
            $newPortraitContainerArray['portraitElement']['quotient'] = floor($containerDetails[0]['columnWidth'] / $containerDetails[0]['portraitPerRow']);
            $newPortraitContainerArray['portraitElement']['remainder'] = ceil($containerDetails[0]['columnWidth'] % $containerDetails[0]['portraitPerRow']);
            $newPortraitContainerArray['portraitElement']['filterData'] = $this->getFilterData($containerDetails[0]['portraitId']);
            $newPortraitContainerArray['portraitElement']['filter'] = (count($newPortraitContainerArray['portraitElement']['filterData']) > 0) ? true : false;
            $newPortraitContainerArray['portraitElement']['boxId'] = $containerDetails[0]['boxId'];
            $newPortraitContainerArray['portraitElement']['stage'] = $containerDetails[0]['stage'];
            foreach ($containerDetails as $key => $detailsValue) {
                //assign container details
                if ($previousContainerId != $detailsValue['containerId']) {
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['containerId'] = $detailsValue['containerId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['sortOrder'] = "{$detailsValue['containerSortOrder']}";
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['actionType'] = 'edit';
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columnSize'] = $detailsValue['containerColumnSize'];
                }
                //assign column details
                if (($previousColumnId != $detailsValue['columnId']) && ($detailsValue['columnId'] != '')) {
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['columnId'] = $detailsValue['columnId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['columnSize'] = intval($detailsValue['columnSize']) * 2;

                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['gridSize'] = "{$detailsValue['columnSize']}";
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['container'] = $detailsValue['containerId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['sortOrder'] = "{$detailsValue['columnSortOrder']}";
                }
                //assign data details
                if ($detailsValue['dataId'] != '') {
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['type'] = 'edit';
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['containerId'] = $detailsValue['containerId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['columnId'] = $detailsValue['columnId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['dataId'] = $detailsValue['dataId'];

                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['selectedField'] = $detailsValue['attributeId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['selectedFieldType'] = strtoupper($detailsValue['columnType']);
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['fieldType'] = "{$detailsValue['fieldType']}";
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['roleId'] = $detailsValue['role'];

                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['roleCategoryId'] = $detailsValue['roleCategory'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['columnSubType'] = $detailsValue['columnSubType'];
                    if ($detailsValue['functionIds'] != '') {
                        $functionArray = explode(',', $detailsValue['functionIds']);
                        $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['functionIds'] = $functionArray;
                    }

                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['sortOrder'] = $detailsValue['dataSortOrder'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['fieldDisplayType'] = $detailsValue['fieldDisplayType'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['lineBreakBefore'] = "{$detailsValue['lineBreakBefore']}";

                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['emptyValueDisplay'] = $detailsValue['emptyValueDisplay'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['separateListing'] = $detailsValue['separateListing'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['placeholderImage'] = $detailsValue['profileImage'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['separateListing'] = $detailsValue['separateListing'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['profileImage'] = $detailsValue['profileImage'];

                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['catId'] = $detailsValue['catId'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['columnSubType'] = $detailsValue['columnSubType'];
                    $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['label'][$detailsValue['lang']] = $detailsValue['labelTitle'];
                    if ($detailsValue['columnType'] == 'contact_field') {
                        $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['fieldType'] = $this->getInputType($detailsValue);
                    } else {
                        $newPortraitContainerArray['portraitElement']['container'][$detailsValue['containerId']]['columns'][$detailsValue['columnId']]['data'][$detailsValue['dataId']]['fieldType'] = $this->getInputType($detailsValue);
                    }
                }

                $previousContainerId = $detailsValue['containerId'];
                $previousColumnId = $detailsValue['columnId'];
                $previousDataId = $detailsValue['dataId'];
            }
        }

        return $newPortraitContainerArray;
    }

    /**
     * To get the type of field
     * @param array $detailsValue contain field details
     * @return string type of field
     */
    private function getInputType($detailsValue)
    {
        $returnValue = '';
        if ($detailsValue['columnType'] == 'contact_field') {
            $returnValue = $detailsValue['fieldType'];
        } else {
            switch (strtoupper($detailsValue['columnType'])) {
                case 'TEAM_FUNCTIONS':
                case 'WORKGROUP_ASSIGNMENTS':
                case 'FILTER_ROLE_ASSIGNMENTS':
                case 'WORKGROUP_FUNCTIONS':
                case 'ROLE_CATEGORY_ASSIGNMENTS':
                case 'FED_ROLE_CATEGORY_ASSIGNMENTS':
                case 'COMMON_ROLE_FUNCTIONS':
                case 'COMMON_FED_ROLE_FUNCTIONS':
                case 'INDIVIDUAL_ROLE_FUNCTIONS':
                case 'INDIVIDUAL_FED_ROLE_FUNCTIONS':
                case 'SUB_FED_ROLE_CATEGORY_ASSIGNMENTS':
                case 'INDIVIDUAL_SUB_FED_ROLE_FUNCTIONS':
                case 'COMMON_SUB_FED_ROLE_FUNCTIONS':
                case "TEAM_ASSIGNMENTS":
                    $returnValue = 'multiple';
                    break;
            }
        }
        return $returnValue;
    }

    /**
     * This function is used to get the formatted filter data
     * 
     * @param int $tableId The table id
     * 
     * @return array $filterSettings The filter settings array
     */
    public function getFilterData($tableId)
    {
        $fgContactFilterSettings = new FgContactFilterSettings($this->container);
        $filterSettings = $fgContactFilterSettings->getFilterData($tableId);

        return $filterSettings;
    }
}
