<?php

namespace Clubadmin\Classes;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Clubdatatable
 *
 * @author jinesh.m
 */
class Clubdatatable
{

    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * For iterate the club list data
     * @param array $clublistDatas
     *
     * @return type
     */
    public function iterateDataTableData($clublistDatas)
    {
        $output['aaData'] = array();
        $countryList = Intl::getRegionBundle()->getCountryNames();
        $languages = Intl::getLanguageBundle()->getLanguageNames();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        

        foreach ($clublistDatas as $clubKey => $clublistData) {

            foreach ($clublistData as $key => $cnFields) {
                $clublistData[$key] = str_replace("<", "&lt;", str_replace(">", "&gt;", $cnFields));
            }
            $clublistData['clubname_url'] = $this->container->get('router')->generate('club_overview', array('offset' => $request->get('start') + $clubKey, 'clubId' => $clublistData['id']));
            $clublistData['edit'] = '';
            if ($clublistData['SILAST_CONTACT_EDIT'] != '') {
                $clublistData['SILAST_CONTACT_EDIT'] = $this->container->get('club')->formatDate($clublistData['SILAST_CONTACT_EDIT'], 'date');
            }
            if ($clublistData['SILAST_ADMIN_LOGIN'] != '') {
                $clublistData['SILAST_ADMIN_LOGIN'] = $this->container->get('club')->formatDate($clublistData['SILAST_ADMIN_LOGIN'], 'date');
            }
            if ($clublistData['CF_language'] != '') {
                $shortkey = $clublistData['CF_language'];
                $clublistData['CF_language'] = $languages[$shortkey];
            }
            if ($clublistData['CF_C_country'] != '') {
                $shortCode = $clublistData['CF_C_country'];
                $clublistData['CF_C_country'] = $countryList[$shortCode];
            }
            if ($clublistData['CF_I_country'] != '') {
                $shortCode = $clublistData['CF_I_country'];
                $clublistData['CF_I_country'] = $countryList[$shortCode];
            }
            if ($clublistData['AFNotes'] > 0) {
                $clublistData['AFNotes_url'] = $this->container->get('router')->generate('club_note', array('offset' => $request->get('start') + $clubKey, 'clubid' => $clublistData['id']));
            }
            if ($clublistData['AFDocuments'] > 0) {
                $clublistData['AFDocuments_url'] = $this->container->get('router')->generate('club_documents', array('offset' => $request->get('start') + $clubKey, 'clubId' => $clublistData['id']));
            }

            $output['aaData'][] = $clublistData;
        }

        return $output['aaData'];
    }
}
