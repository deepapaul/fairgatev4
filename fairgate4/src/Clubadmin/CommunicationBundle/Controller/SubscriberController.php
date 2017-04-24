<?php

/**
 *
 * Subscriber controller
 *
 * @package    ClubadminContactBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\CommunicationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Clubadmin\Util\Contactlist;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\ContactBundle\Util\FgRecepientEmailValidator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Intl;

class SubscriberController extends FgController
{

    /**
     * Function Index action
     * @param type $subscriber - 'subscriber'
     * @return Template
     */
    public function indexAction($subscriber = 'subscriber')
    {
        $breadCrumb = array('breadcrumb_data' => array());
        $editUrl = $this->generateUrl('subscriber_edit', array('offset' => '0', 'subscriberid' => 'dummy'), true);
        $logUrl = $this->generateUrl('communication_log_listing', array('tab' => 'menu', 'offset' => '0', 'subscriber' => 'dummy'), true);
        $subscriberContacts = $this->getOwnSubscribersCount('1', '');
        $totalSubscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscribersCount($this->clubId, '');
        $data = array();
        if ($subscriber == 'subscriber') {
            $genderArr = array(array('id' => 'Male', 'title' => $this->get('translator')->trans('CM_MALE')),
                array('id' => 'Female', 'title' => $this->get('translator')->trans('CM_FEMALE'))
            );
            $salutationArr = array(array('id' => 'Formal', 'title' => $this->get('translator')->trans('CM_FORMAL')),
                array('id' => 'Informal', 'title' => $this->get('translator')->trans('CM_INFORMAL')));
            $langArray = $this->getLanguageArray();
            foreach ($langArray as $langCode => $langValue) {
                $resultLangArray[] = array('id' => $langCode, 'title' => $langValue);
            }
            $data[] = array('id' => 'Email', 'data-edit-type' => 'text');
            $data[] = array('id' => 'LastName', 'data-edit-type' => 'text');
            $data[] = array('id' => 'FirstName', 'data-edit-type' => 'text');
            $data[] = array('id' => 'Gender', 'data-edit-type' => 'select2', 'input' => $genderArr);
            $data[] = array('id' => 'Salutation', 'data-edit-type' => 'select2', 'input' => $salutationArr);
            $data[] = array('id' => 'Company', 'data-edit-type' => 'text');
            $data[] = array('id' => 'CorresLang', 'data-edit-type' => 'select2', 'input' => $resultLangArray);
        }
        $tabs = array(0 => 'subscribercontact',
            1 => 'owncontact'
        );
        $activetab = ($subscriber == 'contact') ? 'owncontact' : 'subscribercontact';
        $totalSubscribers = ($totalSubscribers ) ? $totalSubscribers : 0;

        $tabCount = array('owncontact' => $subscriberContacts,
            'subscribercontact' => $totalSubscribers
        );
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', $tabCount, $activetab);

        return $this->render('ClubadminCommunicationBundle:Subscriber:SubscriberList.html.twig', array('breadCrumb' => $breadCrumb, 'contactsCount' => $subscriberContacts, 'subscribers' => $totalSubscribers, 'subscrbertype' => $subscriber, 'editUrl' => $editUrl, 'logUrl' => $logUrl, 'data' => $data, 'tabs' => $tabsData));
    }

    /**
     * Function to get subscribers list
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getSubscribersListAction(Request $request)
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $order = $request->get('order');
        $columns = $request->get('columns');
        $search = $request->get('search');
        $this->session->set('subscriber_search', $search['value']);
        $orderAs = $order[0]['dir'];
        $this->session->set('subscriber_orderAs', $orderAs);
        $orderBy = $columns[$order[0]['column']]['name'];
        $this->session->set('subscriber_orderBy', $orderBy);
        $totalSubscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscribersCount($this->clubId, $search['value']);
        $logPath = $this->generateUrl('communication_log_listing', array('tab' => 'list', 'offset' => 'offset', 'subscriber' => 'subscriber'));
        $subscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscribersList($this->container, $this->clubId, $start, $length, $orderBy, $orderAs, $search['value'], $logPath, $this->get('translator'), '', '', $this->getLanguageArray());
        $return['aaData'] = $subscribers;
        $return["iTotalRecords"] = $totalSubscribers ? $totalSubscribers : 0;
        $return["iTotalDisplayRecords"] = $totalSubscribers ? $totalSubscribers : 0;

        return new JsonResponse($return);
    }

    /**
     * Function to get active contact subscribers
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getOwnSubscribersAction(Request $request)
    {
        $start = $request->get('start', 0);
        $length = $request->get('length', 10);
        $order = $request->get('order');
        $tableColumns = $request->get('columns');
        $emailField = $this->container->getParameter('system_field_primaryemail');
        $search = $request->get('search', '');
        $this->session->set('subscriber_search', $search['value']);
        $orderAs = $order[0]['dir'];
        $this->session->set('subscriber_orderAs', $orderAs);
        $orderBy = $tableColumns[$order[0]['column']]['name'];
        $this->session->set('subscriber_orderBy', $orderBy);
        $club = $this->get('club');
        $columns = $this->getColumnFieldQuery();
        $searchQuery = "1";
        if ($search['value'] != '') {
            $searchQuery = "( `{$this->container->getParameter('system_field_firstname')}` like :search OR "
                . " `{$this->container->getParameter('system_field_lastname')}` like :search OR"
                . " `{$this->container->getParameter('system_field_primaryemail')}` like :search OR"
                . " `{$this->container->getParameter('system_field_companyname')}` like :search)";
        }
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, 'contact');
        $contactlistClass->setColumns($columns);
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.is_subscriber=1 AND (TRIM(`$emailField`) != '' AND `$emailField` IS NOT NULL) AND $searchQuery";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->setLimit($start);
        $contactlistClass->setOffset($length);
        $contactlistClass->addOrderBy("(CASE WHEN " . $orderBy . " IS NULL then 3 WHEN " . $orderBy . "='' then 2 WHEN " . $orderBy . "='0000-00-00 00:00:00' then 1 ELSE 0 END),$orderBy $orderAs");
        $listquery = $contactlistClass->getResult();
        $fieldsArray = $this->conn->fetchAll($listquery, array('search' => '%' . $search['value'] . '%'));
        $subscribers = $this->createOwnSubscribersList($fieldsArray, $start);
        $totalSubscribers = $this->getOwnSubscribersCount($searchQuery, $search['value']);
        $return['aaData'] = $subscribers;
        $return["iTotalRecords"] = $totalSubscribers ? $totalSubscribers : 0;
        $return["iTotalDisplayRecords"] = $totalSubscribers ? $totalSubscribers : 0;

        return new JsonResponse($return);
    }

    /**
     * Function to get field name of columns
     *
     * @return array
     */
    private function getColumnFieldQuery()
    {
        $columns = array('fg_cm_contact.id');
        $columns[] = "`" . $this->container->getParameter('system_field_firstname') . "` as first_name";
        $columns[] = "`" . $this->container->getParameter('system_field_lastname') . "` as last_name";
        $columns[] = "`" . $this->container->getParameter('system_field_primaryemail') . "` as email";
        $columns[] = "`" . $this->container->getParameter('system_field_gender') . "` as gender";
        $columns[] = "`" . $this->container->getParameter('system_field_salutaion') . "` as salutation";
        $columns[] = "`" . $this->container->getParameter('system_field_companyname') . "` as company";
        $columns[] = "(select count(DISTINCT newsletter_id) FROM fg_cm_change_log WHERE fg_cm_contact.id = contact_id AND club_id = $this->clubId ) as newsletterCount";

        return $columns;
    }

    /**
     * Function to prepare array for datatable listing.
     *
     * @param type $subscribersArray subscriber details Array
     * @param type $offset offset
     * @param type $from ('exportown'/'')
     *
     * @return array
     */
    private function createOwnSubscribersList($subscribersArray, $offset, $from = '')
    {
        $subscribers = array();
        foreach ($subscribersArray as $key => $subscriber) {
            $logPath = $this->generateUrl('communication_log_own_contact_listing', array('offset' => $offset + $key, 'contact' => $subscriber['id']));
            $gender = (strtolower($subscriber['gender']) == 'male') ? $this->get('translator')->trans('CM_MALE') : $this->get('translator')->trans('CM_FEMALE');
            $salutation = (strtolower($subscriber['salutation']) == 'formal') ? $this->get('translator')->trans('CM_FORMAL') : $this->get('translator')->trans('CM_INFORMAL');
            if ($from == "exportown") {
                $subscribers[] = array($subscriber['id'], str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['email'])), str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['last_name'])), str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['first_name'])), $gender, $salutation, str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['company'])), $subscriber['newsletterCount'], $logPath = '');
            } else {
                $subscribers[] = array($subscriber['id'], str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['email'])), str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['last_name'])), str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['first_name'])), $gender, $salutation, str_replace("<", "&lt;", str_replace(">", "&gt;", $subscriber['company'])), $subscriber['newsletterCount'], $logPath);
            }
        }

        return $subscribers;
    }

    /**
     * Function to get active contact subscribers count
     *
     * @param type $searchQuery searchQuery
     * @param type $search search-key
     *
     * @return string
     */
    private function getOwnSubscribersCount($searchQuery, $search)
    {
        $emailField = $this->container->getParameter('system_field_primaryemail');
        $club = $this->get('club');
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, 'contact');
        $contactlistClass->setCount();
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.is_subscriber=1 AND (TRIM(`$emailField`) != '' AND `$emailField` IS NOT NULL) AND $searchQuery";
        $contactlistClass->addCondition($sWhere);
        $listquery = $contactlistClass->getResult();
        $subscribersCount = $this->conn->fetchAll($listquery, array('search' => '%' . $search . '%'));

        return $subscribersCount[0]['count'];
    }

    /**
     * Function to create Subscribers
     * @return Template
     */
    public function createAction(Request $request)
    {
        $fieldTitle = $this->getFieldTitle(0);
        $formValues = array();
        $form1 = $this->createForm(\Common\UtilityBundle\Form\FgSubscriberForm::class, null, array('custom_value' => array('fieldTitle' => $fieldTitle, 'formData' => $formValues)));
        $isError = 0;
        if ($request->getMethod() == 'POST') {
            $form1->handleRequest($request);
            if ($form1->isSubmitted()) {
                if ($form1->isValid()) {
                    $isError = 0;
                    $checked = $request->get('checked');
                    $formValues = $request->request->get($form1->getName());
//                    echo"<pre>";print_r($formValues);exit;
                    $this->updateSubscriberAction($formValues, 0);

                    if ($checked == 1) {
                        $redirect = $this->generateUrl('subscriber_create');
                    } else {
                        $redirect = $this->generateUrl('subscriber_list');
                    }

                    return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('SUBSCRIBER_SAVED')));
                } else {
                    $isError = 1;
                }
            }
        }
        $breadcrumb = array('back' => $this->generateUrl('subscriber_list'));
        $result = array(
            'form' => $form1->createView(),
            'breadCrumb' => $breadcrumb,
            'subscriberId' => 0,
            'isError' => $isError,
            'offset' => 0
        );

        return $this->render('ClubadminCommunicationBundle:Subscriber:createEditSubscriber.html.twig', $result);
    }

    /**
     * function to get language array.
     *
     * @return type
     */
    private function getLanguageArray()
    {
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $fieldLanguages = array();
        foreach ($this->clubLanguages as $shortName) {
            $fieldLanguages[$shortName] = $languages[$shortName];
        }

        return $fieldLanguages;
    }

    /**
     * Function to edit subscribers
     *
     * @param int $offset offset
     * @param int $subscriberid subscriber-id
     * @return template
     */
    public function editSubscriberAction(Request $request, $offset, $subscriberid)
    {
        /*
          no access for subscriberid
         */
        $subscriberAccess = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getAccess($subscriberid, $this->clubId);
        if ($subscriberAccess['count'] != 1) {
            //throw $this->createNotFoundException($this->clubTitle . ' has no access to this page');
            $accessCheckArray = array('from' => 'newsletter', 'isAccess' => 0);
            $allowedTabs = $this->fgpermission->checkAreaAccess($accessCheckArray);
        }
        $fieldTitle = $this->getFieldTitle($subscriberid);
        $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
        $formValues = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscriberDetails($subscriberid, $clubobj);
        $breadcrumb = array('back' => $this->generateUrl('subscriber_list'));
        $form1 = $this->createForm(\Common\UtilityBundle\Form\FgSubscriberForm::class, null, array('custom_value' => array('fieldTitle' => $fieldTitle, 'formData' => $formValues)));
        $isError = 0;
        if ($request->getMethod() == 'POST') {
            $form1->handleRequest($request);
            if ($form1->isSubmitted()) {
                if ($form1->isValid()) {
                    $isError = 0;
                    $formValuesSubmitted = $request->request->get($form1->getName());
                    $this->updateSubscriberAction($formValuesSubmitted, $subscriberid, $formValues);
                    $redirect = $this->generateUrl('subscriber_list');
                    return new JsonResponse(array('status' => 'SUCCESS', 'sync' => 1, 'redirect' => $redirect, 'flash' => $this->get('translator')->trans('SUBSCRIBER_UPDATED')));
                } else {
                    $isError = 1;
                }
            }
        }
        $result = array(
            'form' => $form1->createView(),
            'breadCrumb' => $breadcrumb,
            'subscriberId' => $subscriberid,
            'isError' => $isError,
            'offset' => $offset
        );

        return $this->render('ClubadminCommunicationBundle:Subscriber:createEditSubscriber.html.twig', $result);
    }

    /**
     * Function to get subscriber create/edit page field titles
     *
     * @param int $subscriberid
     * @return array
     */
    private function getFieldTitle($subscriberid)
    {
        $fieldArray = array();
        $fieldArray['LastName'] = $this->get('translator')->trans('SL_LAST_NAME');
        $fieldArray['FirstName'] = $this->get('translator')->trans('SL_FIRST_NAME');
        $fieldArray['Salutation'] = $this->get('translator')->trans('SL_SALUTATION');
        $salutation = array($this->get('translator')->trans('CM_INFORMAL') => 'Informal', $this->get('translator')->trans('CM_FORMAL') => 'Formal');
        $gender = array($this->get('translator')->trans('CM_MALE') => 'Male', $this->get('translator')->trans('CM_FEMALE') => 'Female');
        $fieldArray['Gender'] = $this->get('translator')->trans('SL_GENDER');
        $fieldArray['Email'] = $this->get('translator')->trans('SL_EMAIL');
        $fieldArray['Company'] = $this->get('translator')->trans('SL_COMPANY');
        $fieldArray['genderchoice'] = $gender;
        $fieldArray['salutationchoice'] = $salutation;
        $fieldArray['subscriberId'] = $subscriberid;
        $fieldArray['clOptions'] = $this->getLanguageArray();
        $fieldArray['CorresLang'] = $this->get('translator')->trans('CL_CORRESPOND_LANG');

        return $fieldArray;
    }

    /**
     * Function to update subscribers
     *
     * @param array $formValues
     * @param int $subscriberid
     * @param array $oldFormValues
     */
    private function updateSubscriberAction($formValues, $subscriberid, $oldFormValues = array())
    {
        $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
        $attributes = $formValues;
        $subscriberId = $subscriberid;
        $subscriber = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->find($subscriberId);
        $fgContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->contactId);
        if (!empty($subscriber)) {
            if (array_key_exists('isDeleted', $subscriber)) {
                //$this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->deleteSubscriber($subscriber);
            } else {
                $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->updateSubscriber($subscriber, $attributes, $oldFormValues, $fgContact, $clubobj);
            }
        } else {
            if (count($this->getLanguageArray()) == 1) {
                $attributes['CorresLang'] = $this->clubDefaultLang;
            }
            $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->newSubscriber($attributes, $clubobj, '', '', $fgContact);
        }
    }

    /**
     * Template for delete subscriber
     *
     * @return template
     */
    public function deleteSubsciberContactsAction(Request $request)
    {
        $actionType = $request->get('actionType');
        if ($actionType == 'subscriberdelete') {
            $subscriberDesc = 'CONFIRM_SUBSCRIBER_DELETE_DESC';
        }
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'subscriberDesc' => $this->get('translator')->trans($subscriberDesc));

        return $this->render('ClubadminCommunicationBundle:Subscriber:confirmDelete.html.twig', $return);
    }

    /**
     * Delete subscriber save
     *
     * @return JsonResponse
     */
    public function saveDeleteSubscriberAction(Request $request)
    {
        $selectedId = json_decode($request->get('selectedId', '0'));
        $fromPage = $request->get('fromPage', '');
        $actionType = $request->get('actionType', '');
        if ($request->getMethod() == 'POST') {
            $flashMsg = '';
            if (count($selectedId) > 0) {
                if ($actionType = 'subscriberdelete') {
                    $idCount = count($selectedId);
                    $deleteDetails = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->deleteSubscribers($selectedId, $this->clubId);
                    $flashMsg = ($idCount > 1) ? 'SUBSCRIBERS_DELETED_SUCCESSFULLY' : 'SUBSCRIBER_DELETED_SUCCESSFULLY';
                }
                if ($fromPage == 'subscriber_list') {
                    $ownContactCount = $this->getOwnSubscribersCount('1', '');
                    $subscriberCount = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscribersCount($this->clubId, '');
                    $totalCount = $ownContactCount + $subscriberCount;

                    return new JsonResponse(array('status' => 'SUCCESS', 'totalCount' => $totalCount, 'ownContactCount' => $ownContactCount, 'subscriberCount' => $subscriberCount, 'flash' => $this->get('translator')->trans($flashMsg), 'noparentload' => 1));
                }
            }
        }
    }

    /**
     * Template for export subscriber
     *
     * @return template
     */
    public function exportSubscribersAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'subscriberType' => 'subscriberexport');

        return $this->render('ClubadminCommunicationBundle:Subscriber:confirmExportSubscribers.html.twig', $return);
    }

    /**
     * Template for export subscriber
     *
     * @return template
     */
    public function exportOwnContactSubscribersAction(Request $request)
    {
        $actionType = $request->get('actionType');
        $selActionType = $request->get('selActionType') ? $request->get('selActionType') : '';
        $return = array('actionType' => $actionType, 'clubId' => $this->clubId, 'clubType' => $this->clubType, 'selActionType' => $selActionType, 'subscriberType' => 'owncontactexport');

        return $this->render('ClubadminCommunicationBundle:Subscriber:confirmExportSubscribers.html.twig', $return);
    }

    /**
     * Function to export ownsubscribers
     *
     * @param type $search serach key
     * @param type $orderBy orderBy
     * @param type $orderAs orderAs
     * @param type $selectedId array of selected id(s)
     * @return array
     */
    public function exportOwnSubscribers($search, $orderBy, $orderAs, $selectedId)
    {
        $emailField = $this->container->getParameter('system_field_primaryemail');
        $selectedIds = implode(',', $selectedId);
        $club = $this->get('club');
        $columns = $this->getColumnFieldQuery();
        $searchQuery = "1";
        if ($search != '') {
            $searchQuery = "( `{$this->container->getParameter('system_field_firstname')}` like :search OR "
                . " `{$this->container->getParameter('system_field_lastname')}` like :search OR"
                . " `{$this->container->getParameter('system_field_primaryemail')}` like :search OR"
                . " `{$this->container->getParameter('system_field_companyname')}` like :search)";
        }
        $subquery = (empty($selectedId)) ? " 1" : " fg_cm_contact.id IN ($selectedIds) ";
        $contactlistClass = new Contactlist($this->container, $this->contactId, $club, 'contact');
        $contactlistClass->setColumns($columns);
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $sWhere = " fg_cm_contact.is_subscriber=1 AND (TRIM(`$emailField`) != '' AND `$emailField` IS NOT NULL) AND $searchQuery";
        $contactlistClass->addCondition($sWhere);
        $contactlistClass->addCondition($subquery);
        $contactlistClass->setLimit(0);
        $contactlistClass->setOffset($length);
        $contactlistClass->addOrderBy("(CASE WHEN " . $orderBy . " IS NULL then 3 WHEN " . $orderBy . "='' then 2 WHEN " . $orderBy . "='0000-00-00 00:00:00' then 1 ELSE 0 END),$orderBy $orderAs");
        $listquery = $contactlistClass->getResult();

        $fieldsArray = $this->conn->fetchAll($listquery, array('search' => '%' . $search['value'] . '%'));
        $fieldsArray = $this->createOwnSubscribersList($fieldsArray, 0, "exportown");

        return $fieldsArray;
    }

    /**
     * eXPORT subscriber save
     *
     * @return download box
     */
    public function saveExportSubscribersAction(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set("memory_limit", "2000M");
        $selectedId = json_decode($request->get('selectedId', '0'));
        $selectedIds = implode(',', $selectedId);
        $actionType = $request->get('actionType', '');
        $subscriberType = $request->get('subscriberType', '');
        $csvType = $request->get('csvType', '');
        $search = $this->session->get('subscriber_search');
        $orderBy = $this->session->get('subscriber_orderBy');
        $orderAs = $this->session->get('subscriber_orderAs');

        $idCount = count($selectedId);
        if ($subscriberType == 'subscriberexport') {
            if ($idCount > 0) {
                $subscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscribersList($this->container, $this->clubId, $start = 0, $idCount, $orderBy, $orderAs, $search, $logPath = '', $this->get('translator'), $for = "exportselected", $selectedIds);
            } else {
                $subscribers = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->getSubscribersList($this->container, $this->clubId, $start = 0, $idCount, $orderBy, $orderAs, $search, $logPath = '', $this->get('translator'), $for = "exportall", $selectedIds);
            }
        } else if ($subscriberType == 'owncontactexport') {
            $subscribers = $this->exportOwnSubscribers($search, $orderBy, $orderAs, $selectedId);
        }
        $content = $this->generateCSV($subscribers, $subscriberType, $csvType);
        $date = date("Y-m-d");
        $time = date("H-i-s");
        $subscriberFilename = $this->get('translator')->trans('EXPORT_SUBSCRIBERS');
        $filename = $subscriberFilename . '_' . $date . '_' . $time . '.csv';
        $response = new Response();
        // prints the HTTP headers followed by the content
        $response->setContent(utf8_decode($content));
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->headers->set('Content-Transfer-Encoding', 'utf-8');

        return $response;
    }

    /**
     * Function to generate csv content
     *
     * @param type $subscribers
     * @param type $from
     * @param type $csvType
     * @return string
     */
    public function generateCSV($subscribers, $from, $csvType)
    {
        $colArray = array("0" => array(
                '0' => $this->get('translator')->trans('SL_ID'),
                '1' => $this->get('translator')->trans('SL_EMAIL'),
                '2' => $this->get('translator')->trans('SL_LAST_NAME'),
                '3' => $this->get('translator')->trans('SL_FIRST_NAME'),
                '4' => $this->get('translator')->trans('SL_GENDER'),
                '5' => $this->get('translator')->trans('SL_SALUTATION'),
                '6' => $this->get('translator')->trans('SL_COMPANY')
        ));
        if ($from == 'owncontactexport') {
            $colArray['0'] ['7'] = $this->get('translator')->trans('SL_RECEIVED');
        } else {
            $colArray['0'] ['7'] = $this->get('translator')->trans('SL_CREATED_AT');
            $colArray['0'] ['8'] = $this->get('translator')->trans('SL_EDITED_AT');
            $colArray['0'] ['9'] = $this->get('translator')->trans('SL_EDITED_BY');
            $colArray['0'] ['10'] = $this->get('translator')->trans('SL_RECEIVED');
            $colArray['0'] ['16'] = $this->get('translator')->trans('CL_CORRESPOND_LANG');
        }
        if ($csvType == "colonSep") {
            $seperator = ';';
        } elseif ($csvType == "commaSep") {
            $seperator = ",";
        }

        $finalArray = array_merge($colArray, $subscribers);
        $languageNameArray = FgUtility::getAllLanguageNames();
        
        foreach ($finalArray as $key => $value) {
            if($from != 'owncontactexport'){
                // insert $value[16] (Correspondence language) to $colArray['0'] ['7'] and rearrage every keys
                $value = array_merge(array_slice($value, 0, 7), array(7 => ($key == 0)?$value[16]:$languageNameArray[$value[16]]), array_slice ($value, 7 , 4));
            }
            $content .= '"' . implode('"' . $seperator . '"', $value) . '"';
            $content .= "\n";
        }

        return $content;
    }

    /**
     * Function for subscriber inline edit
     *
     * @return response
     */
    public function subscriberInlineEditAction(Request $request)
    {
        $output = array('valid' => 'false', 'msg' => 'EDIT_NOT_POSSIBLE');
        if ($request->getMethod() == 'POST') {
            $subscriberId = $request->get('rowId');
            $attributeId = $request->get('colId');
            $prevVal = $request->get('prevVal');
            $value = $request->get('value');
            $attribute = array($attributeId => $value);
            $oldValue = array($attributeId => $prevVal);
            $clubobj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
            $subscriber = $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->find($subscriberId);
            $fgContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->find($this->contactId);

            if ($attributeId == 'Email') {
                if ($value != '') {
                    $validatorObj = new FgRecepientEmailValidator($this->container, $value);
                    $output = $validatorObj->isValidEmail();
                    if ($output['valid'] == 'true') {
                        $result = $this->em->getRepository('CommonUtilityBundle:FgCmAttribute')->searchEmailExistAndIsMergable($this->container, '', $value, 0, $subscriberId, 'subscriber');
                        $emailExistFlag = count($result) > 0 ? 'true' : 'false';
                        if ($emailExistFlag != 'true') {
                            $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->updateSubscriber($subscriber, $attribute, $oldValue, $fgContact, $clubobj);
                            $output = array('valid' => 'true', 'msg' => $this->container->get('translator')->trans('SUBSCRIBER_UPDATED'));
                        } else {
                            $output = array('valid' => 'false', 'msg' => $this->container->get('translator')->trans('EMAIL_EXIST'));
                        }
                    }
                } else {
                    $output = array('valid' => 'false', 'msg' => $this->container->get('translator')->trans('REQUIRED'));
                }
            } else {
                $output = array('valid' => 'true', 'msg' => $this->container->get('translator')->trans('SUBSCRIBER_UPDATED'));
                $this->em->getRepository('CommonUtilityBundle:FgCnSubscriber')->updateSubscriber($subscriber, $attribute, $oldValue, $fgContact, $clubobj);
            }
        }

        return new JsonResponse($output);
    }
}
