<?php

namespace Clubadmin\ContactBundle\Util;

/**
 * For handle the contact details view authntication.
 */
class ContactDetailsAccess {

    /**
     * $em.
     *
     * @var object {entitymanager object}
     */
    private $em;

    /**
     * $contactviewType.
     *
     * @var String {active\archive\formerfederationmem}
     */
    public $contactviewType = 'active';

    /**
     * $accessType.
     *
     * @var String {No access\readonly\write}
     */
    public $accessType;

    /**
     * $contactScope.
     *
     * @var String {own member\not own member}
     */
    public $contactScope;

    /**
     * $tabArray.
     *
     * @var Array {overview\data\connection\assignments\userrights\note\log}
     */
    public $tabArray = array();

    /**
     * $menuType.
     *
     * @var String {active\archive\formerfederationmem}
     */
    public $menuType;

    /**
     * $contactAccessPageArray.
     *
     * @var Array
     */
    public $contactAccessPageArray = array();
    private $contactId;

    /**
     * $club.
     *
     * @var Service {clubservice}
     */
    private $club;

    /**
     * $contactAccessPageArray.
     *
     * @var Array(active\archive\formerfedmem)
     */
    private $typeArray = array();
    private $container;
    private $session;

    /**
     * @var string
     */
    public $module;

    /**
     * Constructor for initial setting.
     *
     * @param int $contactId
     * @param object $container
     * @param string $module
     */
    public function __construct($contactId, $container, $module = 'contact') {
        $this->contactId = $contactId;
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->session = $this->container->get('session');
        $this->module = $module;
        if ($this->module === 'contact') {
            $contactTabsArr = array('overview', 'data', 'connection', 'assignment', 'note');
            if (in_array('clubAdmin', $this->club->get('allowedRights'))) {
                array_push($contactTabsArr, 'userright');
            }
            $archivedTabsArr = array('data', 'note', 'log');
        } elseif ($this->module === 'sponsor') {
            $contactTabsArr = (in_array('communication', $this->club->get('bookedModulesDet')) || in_array('frontend1', $this->club->get('bookedModulesDet'))) ? array('overview', 'data', 'services', 'ads', 'connection', 'note') : array('overview', 'data', 'services', 'connection', 'note');
            $archivedTabsArr = (in_array('communication', $this->club->get('bookedModulesDet')) || in_array('frontend1', $this->club->get('bookedModulesDet'))) ? array('data', 'services', 'ads', 'note', 'log') : array('data', 'services', 'note', 'log');
            // $contactTabsArr = array('overview', 'data', 'services', 'ads', 'connection', 'note');
            //$archivedTabsArr = array('data', 'services', 'ads', 'note','log');
        }
        if (in_array('document', $this->club->get('bookedModulesDet'))) {
            array_push($contactTabsArr, 'document');
//            if ($this->module === 'sponsor') {
//                array_push($archivedTabsArr, 'document');
//            }
        }
        if ($this->module === 'sponsor') {
            array_push($contactTabsArr, 'log');
            /* Since invoice is not done it is commented */
            // array_push($archivedTabsArr, 'invoices');
        } else {
            array_push($contactTabsArr, 'log');
        }
        $this->contactAccessPageArray = array('archive' => $archivedTabsArr, 'formerfedmember' => $archivedTabsArr, 'contact' => $contactTabsArr);
        $this->isContactAuthenticated();
        $this->setModuleMenu();
    }

    /**
     * For checking the authentication of contact.
     *
     * @return bool
     */
    private function isContactAuthenticated() {
        $contactDetails = $this->checkContactAuthentication();
        if (count($contactDetails) == 0) {
            $this->accessType = 'NO_ACCESS';
        } else {
            $this->contactScope = ($contactDetails[0]['created_club_id'] == $this->club->get('id')) ? 'OWN_MEMBER' : 'NOT_OWN_MEMBER';
            if ($contactDetails[0]['is_deleted'] == '1' && $contactDetails[0]['created_club_id'] == $contactDetails[0]['club_id']) {
                $this->typeArray[] = 'archive';
                $this->tabArray = $this->contactAccessPageArray['archive'];
            }
            if ($contactDetails[0]['is_former_fed_member'] == '1') {
                $this->typeArray[] = 'formerfederationmember';
                $this->tabArray = $this->contactAccessPageArray['formerfedmember'];
            }
            if (($contactDetails[0]['club_id'] == $contactDetails[0]['main_club_id'] || ($contactDetails[0]['fed_membership_cat_id'] > 0 && $contactDetails[0]['is_fed_membership_confirmed'] == '1') || (($contactDetails[0]['fed_membership_cat_id'] > 0 && $contactDetails[0]['is_fed_membership_confirmed'] == '0') || ($contactDetails[0]['old_fed_membership_id'] > 0 ))) && $contactDetails[0]['is_deleted'] == '0') {
                $this->typeArray[] = 'contact';
                $this->tabArray = $this->contactAccessPageArray['contact'];
            }
        }
        return $this->accessType;
    }

    /**
     * For collect all authenticated contact of particular fed/sbfed/club.
     *
     * @return array
     */
    private function checkContactAuthentication() {
        $allowedContact = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getAllAuthenticatedContact($this->club, $this->contactId, $this->module);

        return $allowedContact;
    }

    /**
     * set menu type.
     */
    private function setModuleMenu() {
        $moduleMenu = $this->session->get('moduleMenu_' . $this->club->get('id'));
        $this->menuType = $this->typeArray[0];
        if ($moduleMenu != '') {
            if (in_array($moduleMenu, $this->typeArray)) {
                $this->menuType = $moduleMenu;
            }
        }
        if (in_array('contact', $this->typeArray)) {
            $this->contactviewType = ($this->module == 'contact') ? 'contact' : 'sponsor';
        } elseif (in_array('archive', $this->typeArray)) {
            $this->contactviewType = 'archive';
        } else {
            $this->contactviewType = 'formerfederationmember';
        }
        $this->session->set('moduleMenu_' . $this->club->get('id'), $this->menuType);
    }

}
