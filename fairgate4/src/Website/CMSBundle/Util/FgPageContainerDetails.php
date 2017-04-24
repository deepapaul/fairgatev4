<?php

/**
 * FgPageContainerDetails
 */
namespace Website\CMSBundle\Util;

use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Website\CMSBundle\Util\FgPageContent;
use Website\CMSBundle\Util\FgCmsPortraitContainer;
use Website\CMSBundle\Util\FgCmsArticleContainer;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * Manage CMS page content container functionalities
 *
 * @package         package
 * @subpackage      subpackage
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgPageContainerDetails
{

    /**
     * @var object Container variable
     */
    public $container;

    /**
     * @var object entity manager variable
     */
    private $em;

    /**
     * Constructor of FgPageContainerDetails class.
     *
     * @param ContainerInterface $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to save default container settings for a page while creating anew page/new container
     *
     * @param type $pageId             Id of the page where the container is going to be creatd
     * @param type $containerSortOrder Sort order
     * @param type $columnCount        Column count
     *
     * @return boolean
     */
    public function setDefaultContainerSettings($pageId, $containerSortOrder, $columnCount)
    {
        $pageContainerId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainer')->insertNewContainer($pageId, $containerSortOrder);
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->insertContainerColumns($pageContainerId, $columnCount, '', 1);

        return $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerBox')->getContainerDetails($pageContainerId, $containerSortOrder);
    }

    /**
     * To add columns inside a container
     *
     * @param type $containerId Container Id
     * @param type $columnValue Column details
     *
     * @return boolean
     */
    public function increaseColumnCount($containerId, $columnValue)
    {
        $neededCount = $columnValue['addCount'];
        $currentTotalWidth = $columnValue['currentTotalWidth'];
        $availableWidth = $columnValue['totalWidth'];
        $currentColumnCount = $columnValue['currentColumnCount'];

        if (($currentTotalWidth + $neededCount) <= $availableWidth) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->insertContainerColumns($containerId, $neededCount, $currentColumnCount);
        } else {
            $columnDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->getContainerColumnDetails($containerId, 'DESC');
            foreach ($columnDetails as $columns) {
                $columnObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->find($columns['columnId']);
                $j = 0;
                if ($columns['widthValue'] > 1) {
                    for ($i = $columns['widthValue']; $i > 1; $i--) {
                        $j = $i - 1;
                        $currentTotalWidth = $currentTotalWidth - 1;
                        if ($availableWidth == ($currentTotalWidth + $neededCount)) {
                            $breakFlag = 1;
                            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->insertContainerColumns($containerId, $neededCount, $currentColumnCount);
                            break;
                        }
                    }
                    $columnObj->setWidthValue($j);
                    $this->em->persist($columnObj);
                    $this->em->flush();
                }
                if ($breakFlag) {
                    break;
                }
            }
        }

        return true;
    }

    /**
     * Function to delete content elements and insert log entries of element
     *
     * @param Array $elementArray Element array
     * @param Int   $pageId       Page Id
     * @param Int   $contactId    Contact Id
     *
     * @return boolean
     */
    public function deleteContentElement($elementArray, $pageId, $contactId)
    {
        $pageObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageTitle = $pageObj->getTitle();
        foreach ($elementArray as $element) {
            $elementId = $element;
            if (is_array($element)) {
                $elementId = $element['elementId'];
            }

            if ($elementId != '') {
                $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->deleteElement($elementId);
                $logArray[] = "('$elementId', '$pageId', 'page', 'deleted', '$pageTitle', '', now(), $contactId)";
                $logArray[] = "('$elementId', '$pageId', 'element', 'deleted', '', '', now(), $contactId)";
            }
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        return true;
    }

    /**
     * Function to decrease column count of a controller
     *
     * @param Int   $containerId Container id
     * @param Array $columnValue Column Details
     *
     * @return boolean
     */
    public function decreaseColumnCount($containerId, $columnValue)
    {
        $columnWithBoxDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->getContainerBoxDetails($containerId);
        $loopCount = 1;
        foreach ($columnWithBoxDetails as $boxDetailsKey => $boxDetailsValue) {

            if ($loopCount == $columnValue['newCount']) {
                $lastBoxId = key(array_slice($boxDetailsValue['box'], -1, 1, true));
                $lastBoxSortOrder = $boxDetailsValue['box'][$lastBoxId]['boxOrder'];
                $newColumnObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->find($boxDetailsKey);
            }
            if ($loopCount > $columnValue['newCount']) {
                $lastBoxSortOrder = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainer')->deleteColumnAndMoveBoxes($boxDetailsValue['box'], $boxDetailsKey, $newColumnObject, $lastBoxSortOrder);
            }
            $loopCount++;
        }

        return true;
    }

    /**
     * Function to sort/move container box to another container column
     *
     * @param array $sortBoxDetails Box details
     *
     * @return boolean
     */
    public function sortContainerBox($sortBoxDetails)
    {
        $sortBoxDetails['sortOrder'] = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerBox')->updateBoxSortPostion($sortBoxDetails['fromColumn'], $sortBoxDetails['toColumn'], $sortBoxDetails['sortOrder'], $sortBoxDetails['currentSortOrder']);
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerBox')->moveBoxAndUpdatePosition($sortBoxDetails);
        $pdo = new ContactPdo($this->container);
        $pdo->reorderSortPosition('fg_cms_page_container_box', 'column_id', $sortBoxDetails['fromColumn'], 'sort_order');

        return true;
    }

    /**
     * Function to sort/move elementsfrom one box to another box
     *
     * @param array $sortElementDetails Element details
     *
     * @return boolean
     */
    public function sortContainerElement($sortElementDetails)
    {
        $sortElementDetails['sortOrder'] = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->updateElementSortPostion($sortElementDetails['toBox'], $sortElementDetails['sortOrder'], $sortElementDetails['fromBox'], $sortElementDetails['currentSortOrder']);
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->moveElementsAndUpdatePosition($sortElementDetails);
        $pdo = new ContactPdo($this->container);
        $pdo->reorderSortPosition('fg_cms_page_content_element', 'box_id', $sortElementDetails['fromBox'], 'sort_order');

        return true;
    }

    /**
     * Function to remove elements to clipboard
     *
     * @param array $elementArray Element details
     * @param int $pageId         Page id
     * @param int $contactId      Logged contact id
     *
     * @return boolean
     */
    public function removeElements($elementArray, $pageId, $contactId)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->removeElementsToClipboard($elementArray);
        $pageObj = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
        $pageTitle = $pageObj->getTitle();
        $logArray = array();
        foreach ($elementArray as $element) {
            $logArray[] = "('$element', '$pageId', 'page', 'deleted', '$pageTitle', '', now(), $contactId)";
        }
        $cmsPdo = new CmsPdo($this->container);
        $cmsPdo->saveLog($logArray);

        return true;
    }

    /**
     * A common function to save all page contant details
     *
     * @param array $pageContentArray Content details
     * @param array $pageDetails      Current page details array
     *
     * @return array
     */
    public function commonSavePageContentDetails($pageContentArray, $pageDetails)
    {
        $clubService = $this->container->get('club');
        $session = $this->container->get('session');
        $pageContentObj = new FgPageContent($this->container);
        $clubDefaultLang = $clubService->get('club_default_lang');
        $returnArray = $pageDetails;
        foreach ($pageContentArray['page'] as $pageId => $pageValue) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->saveContentUpdateTime($pageId);
            $pageObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->find($pageId);
            $session->set("lastCmsPageEditTime_" + $pageId, $pageObject->getContentUpdateTime()->format('Y-m-d H:i:s'));
            if (isset($pageValue['container'])) {
                // Looping each container
                foreach ($pageValue['container'] as $containerKey => $containerValue) {
                    // Checking whether the post array container new container details
                    if ($containerKey == 'new') {
                        $newInsertedContainerDetails = $this->addNewPageContainer($pageId, $containerValue);
                        $pageDetails['page']['container'] = ($pageDetails['page']['container'] + $newInsertedContainerDetails['container']);
                        // Returns full page details
                        $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'newContainer', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_CREATE_CONTAINER_SUCCESS'));
                        // Condition satisfies if a container is deleted
                    } elseif ($containerKey == 'delete') {
                        $containerId = $containerValue['id'];
                        $existsFlag = $this->checkExistanceOfId($session, 'container', $containerId);
                        if (!$existsFlag) {
                            $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'), 'errorArray' => array());
                            break;
                        }
                        //Remove all elements from the page
                        $elementArray = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getAllElementsOfaPage($pageId, $this->container->get('club')->get('id'), $containerId);
                        $this->deleteContentElement($elementArray, $pageId, $this->container->get('contact')->get('id'));

                        //remove container
                        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainer')->removeContainer($containerId);

                        //Reorder position of container inside page
                        $this->resetSortOrder('fg_cms_page_container', 'page_id', $pageId, 'sort_order');
                        $pageDetails = $pageContentObj->getContentElementData($pageId);
                        // In case of delete, no need to return page details
                        $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'deleteContainer', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_DELETE_CONTAINER_SUCCESS'), 'existsFlag' => $existsFlag);
                    } else {
                        $containerId = $containerKey;
                        $existsFlag = $this->checkExistanceOfId($session, 'container', $containerId);
                        if (!$existsFlag) {
                            $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                            break;
                        }
                        // Checking of container sorting
                        if (isset($containerValue['sortOrder'])) {
                            $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainer')->sortPageContainers($pageValue['container']);
                            $pageDetails = $pageContentObj->getContentElementData($pageId);
                            $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'sortContainer', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_SORT_CONTAINER_SUCCESS'));
                        }
                        if (isset($containerValue['column'])) {
                            // Looping each column
                            foreach ($containerValue['column'] as $columnKey => $columnValue) {
                                // Checking whether a new column is added
                                if ($columnKey == 'new') {
                                    $this->increaseColumnCount($containerId, $columnValue);
                                    $pageDetails = $pageContentObj->getContentElementData($pageId);
                                    // Returning full page details
                                    $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'newColumn', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_COLUMN_COUNT_CHANGE_SUCCESS'));
                                    // Checking column deletion
                                } elseif ($columnKey == 'delete') {
                                    $this->decreaseColumnCount($containerId, $columnValue);
                                    $pageDetails = $pageContentObj->getContentElementData($pageId);
                                    // Returning full page details
                                    $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'deleteColumn', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_COLUMN_COUNT_CHANGE_SUCCESS'));
                                } else {
                                    $columnId = $columnKey;
                                    $existsFlag = $this->checkExistanceOfId($session, 'column', $columnId);
                                    if (!$existsFlag) {
                                        $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                                        break;
                                    }
                                    // Changing column width
                                    if (isset($columnValue['newWidth'])) {
                                        $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->changeWidthValue($columnId, $columnValue['newWidth']);
                                        $pageDetails['page']['container'][$containerId]['columns'][$columnId]['widthValue'] = $columnValue['newWidth'];
                                        $successMessage = ($columnValue['type'] == 'inc') ? $this->container->get('translator')->trans('CMS_WIDTH_INCREASE_COLUMN_SUCCESS') : $this->container->get('translator')->trans('CMS_WIDTH_DECREASE_COLUMN_SUCCESS');
                                        // Returning full page details
                                        $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'columnWidthChange', 'data' => $pageDetails, 'flash' => $successMessage);
                                    }
                                    if (isset($columnValue['box'])) {
                                        foreach ($columnValue['box'] as $boxKey => $boxValue) {
                                            $columnObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->find($columnId);
                                            // Adding new box
                                            if ($boxKey == 'new') {
                                                $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerBox')->addNewContainerBox($columnObject, $boxValue['sortOrder']);
                                                $pageDetails = $pageContentObj->getContentElementData($pageId);
                                                // Returning full page details
                                                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'newBox', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_CREATE_BOX_SUCCESS'));
                                            } elseif ($boxKey == 'delete') {
                                                $existsFlag = $this->checkExistanceOfId($session, 'box', $boxValue['id']);
                                                if (!$existsFlag) {
                                                    $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                                                    break;
                                                }
                                                $elementArray = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->getAllElementsOfaPage($pageId, $this->container->get('club')->get('id'), $containerId, $columnId, $boxValue['id']);
                                                $this->deleteContentElement($elementArray, $pageId, $this->container->get('contact')->get('id'));

                                                //Delete a box
                                                $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerBox')->removeContainerBox($boxValue['id']);

                                                //Reorder position of boxes inside column
                                                $this->resetSortOrder('fg_cms_page_container_box', 'column_id', $columnId, 'sort_order');
                                                $pageDetails = $pageContentObj->getContentElementData($pageId);
                                                // In case of box deletion, no need to return page details
                                                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'deleteBox', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_DELETE_BOX_SUCCESS'));
                                            } else {

                                            }
                                        }
                                    }
                                }
                            }
                            //any change in column sizes should adjust the portrait element size settings if any within that column.
                            $fgCmsPortraitContainer = new FgCmsPortraitContainer($this->container);
                            $fgCmsPortraitContainer->adjustPortraitElementOnContainerResize($containerId);
                            $fgCmsArticleContainer = new FgCmsArticleContainer($this->container);
                            $fgCmsArticleContainer->adjustArticleElementOnContainerResize($containerId);
                        }
                    }
                }
            }

            // Checking box sorting
            if (isset($pageValue['sortBox'])) {
                $existsFlag = $this->checkExistanceOfId($session, 'box', $pageValue['sortBox']['boxId']);
                $existsFlag1 = $this->checkExistanceOfId($session, 'column', $pageValue['sortBox']['fromColumn']);
                $existsFlag2 = $this->checkExistanceOfId($session, 'column', $pageValue['sortBox']['toColumn']);
                if (!($existsFlag && $existsFlag1 && $existsFlag2)) {
                    $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                    break;
                }
                $this->sortContainerBox($pageValue['sortBox']);
                $pageDetails = $pageContentObj->getContentElementData($pageId);
                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'sortBox', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_SORT_BOX_SUCCESS'));
            }

            // Checking element sorting
            if (isset($pageValue['sortElement'])) {
                $existsFlag = $this->checkExistanceOfId($session, 'element', $pageValue['sortElement']['elementId']);
                $existsFlag1 = $this->checkExistanceOfId($session, 'box', $pageValue['sortElement']['fromBox']);
                $existsFlag2 = $this->checkExistanceOfId($session, 'box', $pageValue['sortElement']['toBox']);
                if (!($existsFlag && $existsFlag1 && $existsFlag2)) {
                    $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                    break;
                }
                $this->sortContainerElement($pageValue['sortElement']);
                $pageDetails = $pageContentObj->getContentElementData($pageId);
                $flash = ($pageValue['sortElement']['toBox'] == $pageValue['sortElement']['fromBox']) ? 'CMS_SORT_ELEMENT_SUCCESS' : 'CMS_MOVE_ELEMENT_SUCCESS';
                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'sortElement', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans($flash));
            }

            // Removing element to clipboard
            if (isset($pageValue['removeElement'])) {
                $existsFlag = $this->checkExistanceOfId($session, 'element', $pageValue['removeElement']['elementId']);
                $existsFlag1 = $this->checkExistanceOfId($session, 'box', $pageValue['removeElement']['boxId']);
                if (!($existsFlag && $existsFlag1)) {
                    $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                    break;
                }
                $boxId = $pageValue['removeElement']['boxId'];
                unset($pageValue['removeElement']['boxId']);
                $details = $this->removeElement($pageValue['removeElement'], $boxId, $pageId, $clubDefaultLang, $pageContentObj, $session);
                $pageDetails =  $details['pageDetails'];   
                $clipboardContentDetails = $details['clipboardContentDetails'];
                
                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'remove', 'data' => $pageDetails, 'clipboardData' => $clipboardContentDetails, 'flash' => $this->container->get('translator')->trans('CMS_REMOVE_ELEMENT_SUCCESS'));
            }
            
            // Removing more than one elements to clipboard
            if (isset($pageValue['removeElements'])) {
                foreach($pageValue['removeElements'] as $key => $removeElement) {
                    $existsFlag = $this->checkExistanceOfId($session, 'element', $removeElement['elementId']);
                    $existsFlag1 = $this->checkExistanceOfId($session, 'box', $removeElement['boxId']);
                    if (!($existsFlag && $existsFlag1)) {
                        $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                        break;
                    }
                    $boxId = $pageValue['removeElements'][$key]['boxId'];
                    unset($pageValue['removeElements'][$key]['boxId']);
                    $details = $this->removeElement($pageValue['removeElements'][$key], $boxId,$pageId, $clubDefaultLang, $pageContentObj, $session);
                    $pageDetails =  $details['pageDetails'];   
                    $clipboardContentDetails = $details['clipboardContentDetails'];
                }
                
                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'remove', 'data' => $pageDetails, 'clipboardData' => $clipboardContentDetails, 'flash' => $this->container->get('translator')->trans('CMS_REMOVE_ELEMENT_SUCCESS'));
            }

            // Deleting an element
            if (isset($pageValue['deleteElement'])) {
                $existsFlag = $this->checkExistanceOfId($session, 'element', $pageValue['deleteElement']['elementId']);
                if (!($existsFlag)) {
                    $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                    break;
                }
                $elementObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->find($pageValue['deleteElement']['elementId']);
                $boxId = $elementObject->getBox()->getId();
                $this->deleteContentElement($pageValue['deleteElement'], $pageId, $this->container->get('contact')->get('id'));

                //Reorder position of boxes inside column
                $this->resetSortOrder('fg_cms_page_content_element', 'box_id', $boxId, 'sort_order');
                $pageDetails = $pageContentObj->getContentElementData($pageId);
                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'deleteElement', 'data' => $pageDetails, 'flash' => $this->container->get('translator')->trans('CMS_DELETE_ELEMENT_SUCCESS'));
            }

            // Checking box sorting
            if (isset($pageValue['moveFromClipboard'])) {
                $existsFlag = $this->checkExistanceOfId($session, 'element', $pageValue['moveFromClipboard']['elementId']);
                $existsFlag1 = $this->checkExistanceOfId($session, 'box', $pageValue['moveFromClipboard']['boxId']);
                if (!($existsFlag && $existsFlag1)) {
                    $returnArray = array('redirect' => $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId), false), 'status' => 'ERROR', 'flash' => $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED'));
                    break;
                }
                $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->moveElementFromClipboard($this->container, $pageId, $pageValue['moveFromClipboard'], $this->container->get('contact')->get('id'));

                $pageContentDetails = $pageContentObj->getContentElementData($pageId);
                $clipboardContentDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($clubService->get('id'), $clubDefaultLang);
                $returnArray = array('status' => true, 'noparentload' => true, 'type' => 'moveFromClipboard', 'clipboardData' => $clipboardContentDetails, 'data' => $pageContentDetails, 'flash' => $this->container->get('translator')->trans('CMS_MOVE_FROM_CLIPBOARD_SUCCESS'));
            }
        }

        return $returnArray;
    }
    
    /**
     * Method to remove lement to clipboard and save log
     * 
     * @param array  $removeElement   array('elementId' => $elementId)
     * @param int    $pageId          pageId
     * @param string $clubDefaultLang club's default language
     * @param object $pageContentObj  object of FgPageContent
     * @param object $session         session object
     * 
     * @return array of (pageDetails, clipboardContentDetails)
     */
    function removeElement($removeElement, $boxId, $pageId, $clubDefaultLang, $pageContentObj, $session)
    {
        $this->removeElements($removeElement, $pageId, $this->container->get('contact')->get('id'));

        //Reorder position of boxes inside column
        $this->resetSortOrder('fg_cms_page_content_element', 'box_id', $boxId, 'sort_order');
        $clipboardContentDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardDetails($this->container->get('club')->get('id'), $clubDefaultLang);
        $pageDetails = $pageContentObj->getContentElementData($pageId);
        $clipBoardElementId = array_column($clipboardContentDetails, 'elementId');
        $pageContentIdArray = $session->get('cmsPageContentIdArray');
        $pageContentIdArray['element'] = array_merge($pageContentIdArray['element'], $clipBoardElementId);
        $session->set("cmsPageContentIdArray", $pageContentIdArray);

        return array('pageDetails' => $pageDetails, 'clipboardContentDetails' => $clipboardContentDetails);
    }

    /**
     * Function to check the existance of all ids
     *
     * @param Object $session Session object
     * @param string $type    Type of id
     * @param int    $id      Id of item
     *
     * @return Boolean
     */
    private function checkExistanceOfId($session, $type, $id)
    {
        $pageContentIdArray = $session->get('cmsPageContentIdArray');

        switch ($type) {
            case 'container' :
                return $this->checkExistancePart($pageContentIdArray, 'container', $id);
            case 'column' :
                return $this->checkExistancePart($pageContentIdArray, 'column', $id);
            case 'box' :
                return $this->checkExistancePart($pageContentIdArray, 'box', $id);
            case 'element' :
                return $this->checkExistancePart($pageContentIdArray, 'element', $id);
            default :
                break;
        }
    }

    /**
     * Function to check the existance of all ids
     *
     * @param array  $pageContentIdArray Page content details
     * @param string $type               Type of id
     * @param int    $id                 Id of item
     *
     * @return boolean
     */
    private function checkExistancePart($pageContentIdArray, $type, $id)
    {
        $allIdArray = $pageContentIdArray[$type];
        if (in_array($id, $allIdArray)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Function to generate failure array
     *
     * @param int $pageId Page id
     *
     * @return array
     */
    public function generateFailureArray($pageId)
    {
        $returnArray['redirect'] = $this->container->get('router')->generate('website_cms_page_edit', array('pageId' => $pageId));
        $returnArray['status'] = 'FAILURE';
        $returnArray['flash'] = $this->container->get('translator')->trans('CMS_PAGE_NOT_SAVED');

        return $returnArray;
    }

    /**
     * Function for add new page container
     *
     * @param int   $pageId         Page Id
     * @param array $containerValue Container details
     *
     * @return array
     */
    public function addNewPageContainer($pageId, $containerValue)
    {
        $pageContainerId = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainer')->insertNewContainer($pageId, $containerValue['sortOrder']);
        $neededColumnCount = $containerValue['columnCount'];
        $totalWidth = $containerValue['totalWidth'];
        $j = 1;
        if ($neededColumnCount >= 1) {
            for ($i = $neededColumnCount; $i > 0; $i--) {
                $widthValue = ceil($totalWidth / $neededColumnCount);
                $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->addNewCustomColumn($pageContainerId, $j, $widthValue);
                $totalWidth = $totalWidth - $widthValue;
                $neededColumnCount--;
                $j++;
            }
        }

        return $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerBox')->getContainerDetails($pageContainerId, $containerValue['sortOrder']);
    }

    /**
     * Function to reorder sort value after deletion of a row
     *
     * @param String $tableName      Table name of sort table
     * @param String $joinFieldName  Field name of table
     * @param String $joinFieldValue Field value
     * @param String $sortField      Sort field name
     *
     * @return boolean
     */
    private function resetSortOrder($tableName, $joinFieldName, $joinFieldValue, $sortField)
    {
        $pdo = new ContactPdo($this->container);
        $pdo->reorderSortPosition($tableName, $joinFieldName, $joinFieldValue, $sortField);

        return true;
    }

    /**
     * Function to decrease column count of a Sidebar controller
     *
     * @param Int   $containerId Container id
     * @param Array $columnValue Column Details
     *
     * @return boolean
     */
    public function decreaseColumnCountSidebar($containerId, $columnValue)
    {
        $columnWithBoxDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->getContainerBoxDetails($containerId);

        $i = 0;
        foreach ($columnWithBoxDetails as $boxDetailsKey => $boxDetailsValue) {
            $i++;
            $widthValue = $boxDetailsValue['widthValue'] + $widthValue;

            if ($widthValue <= $columnValue) {
                $lastBoxId = key(array_slice($boxDetailsValue['box'], -1, 1, true));
                $lastBoxSortOrder = $boxDetailsValue['box'][$lastBoxId]['boxOrder'];
                $newColumnObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->find($boxDetailsKey);
            }
            if ($i == 1 && $widthValue > $columnValue) {
                $newWidth = $widthValue - ($widthValue - $columnValue);
                $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->changeWidthValue($boxDetailsKey, $newWidth);
                $lastBoxId = key(array_slice($boxDetailsValue['box'], -1, 1, true));
                $lastBoxSortOrder = $boxDetailsValue['box'][$lastBoxId]['boxOrder'];
                $newColumnObject = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainerColumn')->find($boxDetailsKey);
                $widthValue = $newWidth;
            }
            if ($widthValue > $columnValue) {
                $lastBoxSortOrder = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContainer')->deleteColumnAndMoveBoxes($boxDetailsValue['box'], $boxDetailsKey, $newColumnObject, $lastBoxSortOrder);
            }
        }

        return true;
    }

    /**
     * Function to set new page details
     *
     * @param string $type          Page type
     * @param string $title         Page title
     * @param array  $otherdDetails Other details if any
     *
     * @return Boolean
     */
    public function setNewPage($type, $title, $otherdDetails = '')
    {
        $data['type'] = $type;
        $data['title'] = $title;
        $data['hideTitle'] = 0;
        if (isset($otherdDetails['sidebarType'])) {
            $data['sidebarType'] = $otherdDetails['sidebarType'];
        }
        $sidebarId = $this->em->getRepository('CommonUtilityBundle:FgCmsPage')->createPage($this->container, $data);
        $this->setDefaultContainerSettings($sidebarId, 1, 1);
        //Save Page Content Json
        $pageContentObj = new FgPageContent($this->container);
        $pageContentObj->saveJsonContent($sidebarId);

        return true;
    }
}
