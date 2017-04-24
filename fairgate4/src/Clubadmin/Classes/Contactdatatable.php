<?php

namespace Clubadmin\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgUtility;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Contactdatatable.
 *
 * @author jinesh.m
 */
class Contactdatatable
{
    private $club;
    private $container;
    private $request;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container container
     * @param type                                                      $club      object of club details
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $requestStack = $this->container->get('request_stack');
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * For iterate the contact list data.
     *
     * @param array $contactlistDatas
     * @param array $specialFieldsArray country_fields
     * @param array $tabledatas
     *
     * @return type
     */
    public function iterateDataTableData($contactlistDatas, $specialFieldsArray, $tabledatas,$tableSetting = 'clubadmin')
    {
        $output['aaData'] = array();
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $contactType = $this->container->get('session')->get('contactType');
        //service for contact field/profile image path
        $pathService = $this->container->get('fg.avatar');
        
        foreach ($contactlistDatas as $contactKey => $contactlistData) {
            // find the actual country from the country code
            foreach ($contactlistData as $key => $cnFields) {
                $contactlistData[$key] = str_replace('<', '&lt;', str_replace('>', '&gt;', $cnFields));
                /* handle cms country -portrait*/
                if($tableSetting == 'website'){
                    if (strpos($key, '_fieldId') !== false) {  // FAIRDEV-303 Contact list are not listing in some scenarios [ handy value 076 in_array issue fix ]
                        if (in_array($contactlistData[$key], $specialFieldsArray)) {
                            if ($cnFields != '') {
                                list($text1, $val1) = explode('_fieldId', $key);
                                $shortCode = strtoupper($contactlistData[$text1]);
                                $contactlistData[$text1] = $countryList[$shortCode];
                                $contactlistData[$text1 . '_original'] = $shortCode;
                            }
                        }
                        if($this->container->getParameter('system_field_corress_lang') == $contactlistData[$key]){
                             //For find the language from the short key
                            if ($contactlistData[$key] != '') {
                                list($text1, $val1) = explode('_fieldId', $key);
                                $shortkey = strtolower($contactlistData[$text1]);
                                $contactlistData[$text1] = $languages[$shortkey];
                                $contactlistData[$text1 . '_original'] = $shortkey;
                            }
                        }
                    }
                }
                /* end handle cms country -portrait*/
                list($text, $val) = explode('CF_', $key);
                if (in_array($val, $specialFieldsArray)) {
                    if ($cnFields != '') {
                        $shortCode = strtoupper($contactlistData[$key]);
                        $contactlistData[$key] = $countryList[$shortCode];
                        $contactlistData[$key . '_original'] = $shortCode;
                    }
                }
                switch($key){
                    case 'CF_' . $this->container->getParameter('system_field_corress_lang'):
                        //For find the language from the short key
                        if ($contactlistData[$key] != '') {
                            $shortkey = strtolower($contactlistData[$key]);
                            $contactlistData[$key] = $languages[$shortkey];
                            $contactlistData[$key . '_original'] = $shortkey;
                        }
                        break;
                    case 'CF_' . $this->container->getParameter('system_field_gender'):
                        if ($contactlistData[$key] != '') {
                            $contactlistData[$key . '_original'] = $contactlistData[$key];
                            if (strtolower($contactlistData[$key]) == 'male') {
                                $contactlistData[$key] = $this->container->get('translator')->trans('CM_MALE');
                            } else {
                                $contactlistData[$key] = $this->container->get('translator')->trans('CM_FEMALE');
                            }
                        }
                        break;
                    case 'CF_' . $this->container->getParameter('system_field_salutaion'):
                        if ($contactlistData[$key] != '') {
                            $contactlistData[$key . '_original'] = $contactlistData[$key];
                            if (strtolower($contactlistData[$key]) == 'formal') {
                                $contactlistData[$key] = $this->container->get('translator')->trans('CM_FORMAL');
                            } else {
                                $contactlistData[$key] = $this->container->get('translator')->trans('CM_INFORMAL');
                            }
                        }
                        break;
                    case 'CNhousehold_contact':
                        if ($contactlistData[$key] != '') {
                            $textContact = '';
                            $pipeSeperatorArray = explode(';', $contactlistData[$key]);
                            $contactlistData[$key . '_jsonarray'] = $pipeSeperatorArray;
                            //#contactId is replaces with actual id
                            $contactlistData[$key . '_url'] = $this->container->get('router')->generate('render_contact_overview', array('offset' => 0, 'contact' => '#contactId'));
                        }
                        break;
                    case 'Gprofile_company_pic':
                        if ($contactlistData[$key] != '') {
                            $contactlistData[$key] = $pathService->getAvatar($contactlistData['id']);
                            $contactlistData[$key . 'Exists'] = file_exists(str_replace('\\', '/', realpath('')).$pathService->getAvatar($contactlistData['id'],'',true));
                        }else{
                            $contactlistData[$key . 'Exists'] = false;
                        }
                        break;
                    case 'Gnotes':
                        if ($contactlistData[$key] > 0) {
                            $contactlistData[$key . '_url'] = $this->container->get('router')->generate('contact_note', array('offset' => $this->request->get('start') + $contactKey, 'contactid' => $contactlistData['id']));
                        }
                        break;
                    case 'Gdocuments':
                        if ($contactlistData[$key] > 0) {
                            $contactlistData[$key . '_url'] = $this->container->get('router')->generate('contact_documents', array('offset' => $this->request->get('start') + $contactKey, 'contact' => $contactlistData['id']));
                        }
                        break;
                    case 'Gage':
                        if ($contactlistData[$key] <= 0) {
                            $contactlistData[$key] = '-';
                        }
                        break;
                    case 'FImembership_years':
                    case 'CMclub_member_years':
                    case 'FMfed_member_years':
                        $contactlistData[$key] = ($contactlistData[$key] == '0.0') ? '0' : $this->container->get('club')->formatNumber($contactlistData[$key],1);
                        break;
                    case 'FIclub':
                        $myarr = explode(', ', $contactlistData[$key]);
                        for ($loc = 0; $loc < sizeof($myarr); $loc++) {
                            if (sizeof($myarr) == 1) {
                                $myarr[$loc] = str_replace('#mainclub#', '', $myarr[$loc]);
                            } else {
                                $myarr[$loc] = str_replace('#mainclub#', '<i class="fa  fa-star text-yellow"></i>', $myarr[$loc]);
                            }
                        }
                        $contactlistData['FIclub'] = implode($myarr, ', '); 
                        break;
                        
                }
                
                //null date checking field array
                $nullDateCheckingArray = array('Gcreated_at', 'Glast_updated', 'archived_on', 'resigned_on', 'Glast_login', 'FMfirst_joining_date', 'FMjoining_date', 'FMleaving_date', 'CMfirst_joining_date', 'CMjoining_date', 'CMleaving_date');
                // For handle the date related fields in this area
                if (in_array($key, $nullDateCheckingArray)) {
                    if ($contactlistData[$key] == '0000-00-00 00:00:00' || $contactlistData[$key] == '0000-00-00' || $contactlistData[$key] == null) {
                        $contactlistData[$key] = '-';
                    } else {
                        $contactlistData[$key] = $this->container->get('club')->formatDate($contactlistData[$key], 'date');
                    }
                }
            }
            //check for find the type of contact field
            $allContactFiledsData = $this->club->get('allContactFields');

            if (is_array($tabledatas) && count($tabledatas) > 0) {
                foreach ($tabledatas as $key => $contactFields) {
                    if (array_key_exists($contactFields['id'], $allContactFiledsData)) {
                        switch ($allContactFiledsData[$contactFields['id']]['type']) {

                            case 'date':
                                if ($contactlistData['CF_' . $contactFields['id']] == '' || $contactlistData['CF_' . $contactFields['id']] == '0000-00-00' || $contactlistData['CF_' . $contactFields['id']] == '0000-00-00 00:00:00') {
                                    $contactlistData['CF_' . $contactFields['id']] = '-';
                                } else {
                                    $contactlistData['CF_' . $contactFields['id']] = $this->container->get('club')->formatDate($contactlistData['CF_' . $contactFields['id']], 'date', 'Y-m-d');
                                }

                                break;
                            case 'multiline':
                                if ($contactlistData['CF_' . $contactFields['id']] == '') {
                                    $contactlistData['CF_' . $contactFields['id']] = '-';
                                }
                                break;
                            case 'number':
                                if ($contactlistData['CF_' . $contactFields['id']] == '') {
                                    $contactlistData['CF_' . $contactFields['id']] = '-';
                                } else {
                                    $contactlistData['CF_' . $contactFields['id']] = $this->container->get('club')->formatNumber($contactlistData['CF_' . $contactFields['id']]);
                                }
                                break;
                        }
                    }
                }
            }

            if ($contactType == 'sponsor' || $contactType == 'archivedsponsor') {
                $contactlistData['edit_url'] = $this->container->get('router')->generate('edit_sponsor', array('contact' => $contactlistData['id']));
                //set  url of booking edit
                $contactlistData['assignment_edit_url'] = isset($contactlistData['SA_paymentstartdate']) ? $this->container->get('router')->generate('sponsor_edit_booking', array('bookingId' => $contactlistData['SA_bookingId'])) : '';
            } else {
                $contactlistData['edit_url'] = $this->container->get('router')->generate('edit_contact', array('contact' => $contactlistData['id']));
            }
            $sponsorIcon = false;
            $contactlistData['SponsorIcon'] = $sponsorIcon;
            if ($functionTypePostValue != 'none') {
                $contactlistData['Function'] = $contactlistData['Function'];
            } else {
                $contactlistData['Function'] = '-';
            }
            //to find contact name click url
            $clickUrl = $this->getContactnameUrl($contactType, $contactKey, $contactlistData, $tableSetting);

            $contactlistData['click_url'] = $clickUrl;
            $memType = is_null($contactlistData['membershipType']) ? '0' : $contactlistData['membershipType'];
            $contactlistData['edit'] = $memType;
            $output['aaData'][] = $contactlistData;
        }
            
        return $output['aaData'];
    }

    /**
     * To find the url for contact name in datatable.
     *
     * @param string $contactType     type of contact
     * @param int    $contactKey      index key
     * @param array  $contactlistData result array
     *
     * @return string
     */
    private function getContactnameUrl($contactType, $contactKey, $contactlistData, $tableSetting)
    {
        switch ($contactType) {
            case 'sponsor':
                if($tableSetting == 'website') {
                    //to find contact name click url
                    $url = $this->container->get('router')->generate('internal_community_profile', array('contactId' => $contactlistData['id']));
                }else{
                    $url = $this->container->get('router')->generate('render_sponsor_overview', array('offset' => $this->request->get('start') + $contactKey, 'sponsor' => $contactlistData['id']));
                }
                break;
            case 'archivedsponsor':
                $url = $this->container->get('router')->generate('sponsor_contact_data', array('offset' => $this->request->get('start') + $contactKey, 'contact' => $contactlistData['id']));
                break;
            case 'contact':
                if($tableSetting == 'website') {
                    //to find contact name click url
                    $url = $this->container->get('router')->generate('internal_community_profile', array('contactId' => $contactlistData['id']));
                }else{
                    $url = $this->container->get('router')->generate('render_contact_overview', array('offset' => $this->request->get('start') + $contactKey, 'contact' => $contactlistData['id']));
                }
                break;
            default:
                $url = $this->container->get('router')->generate('contact_data', array('offset' => $this->request->get('start') + $contactKey, 'contact' => $contactlistData['id']));
                break;
        }

        return $url;
    }
}
