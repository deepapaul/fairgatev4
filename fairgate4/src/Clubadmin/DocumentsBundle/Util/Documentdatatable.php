<?php

namespace Clubadmin\DocumentsBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Util\FgUtility;

/**
 * Description of Documentdatatable
 *
 * @author jinesh.m
 */
class Documentdatatable {

    private $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container container
     * @param type                                                      $club      object of club details
     */
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * For iterate the contact list data
     * @param array $documentlistdatas contains the document data
     *
     * @return array 
     */
    public function iterateDataTableData($documentlistdatas, $doctype = 'CLUB') {
        $output['aaData'] = array();
        $request = $this->container->get('request_stack')->getCurrentRequest();
        foreach ($documentlistdatas as $doctKey => $documentlistdata) {
            // find the actual country from the country code
            foreach ($documentlistdata as $key => $cnFields) {
                //$documentlistdata[$key] = str_replace("<", "&lt;", str_replace(">", "&gt;", $cnFields));
                $documentlistdata[$key] = htmlentities($cnFields, ENT_NOQUOTES, "UTF-8");
                switch ($key) {
                    case "docuploaded":
                    case "last_updated":
                        if ($documentlistdata[$key] == '0000-00-00 00:00:00' || $documentlistdata[$key] == '0000-00-00 00:00:00' || $documentlistdata[$key] == null) {
                            $documentlistdata[$key] = "-";
                        } else {
                            $documentlistdata[$key] = $this->container->get('club')->formatDate($documentlistdata[$key],'date');
                        }
                        break;

                    case "docname":
                        if ($documentlistdata[$key] != '') {
                            $documentlistdata[$key . '_icon'] = FgUtility::getDocumentIcon($documentlistdata['fileName'], true);
                            $documentlistdata[$key . '_url'] = $this->container->get('router')->generate('document_download', array('docId' => $documentlistdata['documentId'], 'versionId' => $documentlistdata['versionId']));
                            switch ($doctype) {
                                case "CLUB":
                                    $documentlistdata['edit_url'] = $this->container->get('router')->generate('document_settings_club', array('documentId' => $documentlistdata['documentId'], 'offset' => $request->get('start') + $doctKey));
                                    break;
                                case "CONTACT":
                                    $documentlistdata['edit_url'] = $this->container->get('router')->generate('document_settings_contact', array('documentId' => $documentlistdata['documentId'], 'offset' => $request->get('start') + $doctKey));
                                    break;
                                case "TEAM":
                                    $documentlistdata['edit_url'] = $this->container->get('router')->generate('document_settings_team', array('documentId' => $documentlistdata['documentId'], 'offset' => $request->get('start') + $doctKey));
                                    break;
                                case "WORKGROUP":
                                    $documentlistdata['edit_url'] = $this->container->get('router')->generate('document_settings_workgroup', array('documentId' => $documentlistdata['documentId'], 'offset' => $request->get('start') + $doctKey));
                                    break;
                            }
                        }
                        break;

                    case "CO_FO_DEPOSITED_WITH_FOR_ASSIGNED":
                    case "CO_FO_DEPOSITED_WITH":
                        if ($documentlistdata[$key] != '') {
                            //#contactId is replaces with actual id
                            $documentlistdata[$key . '_URL'] = $this->container->get('router')->generate('render_contact_overview', array('offset' => 0, 'contact' => '#contactId'));
                        }
                        break;
                }
            }

            $output['aaData'][] = $documentlistdata;
        }

        return $output['aaData'];
    }
}
