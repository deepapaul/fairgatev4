<?php

/**
 * FormInquiriesSidebar.
 *
 * Class to get form inquiries sidebar data
 */
namespace Website\CMSBundle\Util;

/**
 *
 * @package 	Website
 * @subpackage 	CMS
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class FgFormInquiriesSidebar
{

    private $container;
    private $club;
    private $contact;
    private $clubId;
    private $contactId;
    private $sidebarFilterData = array();

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
        $this->clubDefaultLang = $this->club->get('club_default_lang');
        $this->clubLanguages = $this->club->get('club_languages');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to get sidebar filter data
     *
     * @return array Sidebar filter data array
     */
    public function getDataForSidebar()
    {
        $this->getOverview();
        $this->getActiveForms();
        $this->getClipboardForms();
        $this->getDeletedForms();

        return $this->sidebarFilterData;
    }

    /**
     * Method to get overview of form inquiries in sidebar.
     */
    private function getOverview()
    {
        $this->sidebarFilterData['Overview']['id'] = 'Overview';
        $this->sidebarFilterData['Overview']['title'] = $this->container->get('translator')->trans('CMS_SIDEBAR_OVERVIEW');
        $allInquiryCount = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElementFormInquiries')->getAllFormInquiryCount($this->clubId);
        $this->sidebarFilterData['Overview']['entry'][0] = array('id' => 'all_forms', 'title' => $this->container->get('translator')->trans('CMS_SIDEBAR_ALL_FORM_INQUIRY'), 'showCount' => true, 'count' => $allInquiryCount);
    }

    /**
     * Method to get the active forms of  current club for the sidebar.
     */
    private function getActiveForms()
    {
        $defLang = $this->club->get('default_lang');
        $activeFormDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getActiveFormElementDetails($this->clubId, $defLang);
        if (count($activeFormDetails) > 0) {
            $this->sidebarFilterData['Active']['id'] = 'Active';
            $this->sidebarFilterData['Active']['title'] = $this->container->get('translator')->trans('CMS_SIDEBAR_ACTIVE_FORMS');
            $this->sidebarFilterData['Active']['entry'] = $activeFormDetails;
        }
    }

    /**
     * Method to get the clipboard forms of  current club for the sidebar.
     */
    private function getClipboardForms()
    {
        $defLang = $this->club->get('default_lang');
        $clipboardFormDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getClipboardFormElementDetails($this->clubId, $defLang);
        if (count($clipboardFormDetails) > 0) {
            $this->sidebarFilterData['Clipboard']['id'] = 'Clipboard';
            $this->sidebarFilterData['Clipboard']['title'] = $this->container->get('translator')->trans('CMS_SIDEBAR_CLIPBOARD_FORMS');
            $this->sidebarFilterData['Clipboard']['entry'] = $clipboardFormDetails;
        }
    }

    /**
     * Method to get the deleted forms of  current club for the sidebar.
     */
    private function getDeletedForms()
    {
        $defLang = $this->club->get('default_lang');
        $deletdFormDetails = $this->em->getRepository('CommonUtilityBundle:FgCmsPageContentElement')->getDeletedFormElementDetails($this->clubId, $defLang);
        if (count($deletdFormDetails) > 0) {
            $this->sidebarFilterData['Deleted']['id'] = 'Deleted';
            $this->sidebarFilterData['Deleted']['title'] = $this->container->get('translator')->trans('CMS_SIDEBAR_DELETED_FORMS');
            $this->sidebarFilterData['Deleted']['entry'] = $deletdFormDetails;
        }
    }
}
