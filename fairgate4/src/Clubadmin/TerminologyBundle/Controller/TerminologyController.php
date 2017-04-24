<?php

namespace Clubadmin\TerminologyBundle\Controller;

use Clubadmin\TerminologyBundle\Form\TerminologyContactForm;
use Common\UtilityBundle\Controller\FgController as ParentController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;

/**
 * TerminologyController
 *
 * This controller was created for handling Terminology terms
 *
 * @package    ClubadminTerminologyBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class TerminologyController extends ParentController
{

    /**
     * This function is used to display terminology main page
     *
     * @return template
     */
    public function indexAction()
    {
        $federation = $this->container->getParameter('federation');
        $club = $this->container->get('club');
        $bookedModulesDet = $club->get('bookedModulesDet');
        $hasSubfederation = $club->get('hasSubfederation');
        $fedId = ($this->clubType == $federation) ? $this->clubId : $this->federationId;
        $translator = $this->get('translator');
        $transarray = $this->translateFields();
        $clubLanguagesDetails = $club->get('club_languages_det');
        $clubLanguagesDet = array();
        foreach ($clubLanguagesDetails as $correspondanceLang => $rowLang) {
            $clubLanguagesDet[$correspondanceLang] = $rowLang['systemLang'];
        }
        //$clubLanguagesDet
        $frontendModuleArray = array('Federation membership', 'Team member', 'Team member picture', 'Team member profile', 'Team member list', 'Intranet', 'Gallery', 'Oganizer', 'Calender', 'Website');
        $terminologyindividualClubDetails = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->getTerminologyindividualclubDetails($this->clubId, $frontendModuleArray, $bookedModulesDet);
        $terminologydefaultClubDetails = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->getTerminologydefaultclubDetails($this->clubId, $frontendModuleArray, $bookedModulesDet, $this->clubDefaultSystemLang);
        $defaultClubForLanguages = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->getTerminologydefaultclubDetails($this->clubId, $frontendModuleArray, $bookedModulesDet, '');
        $terminologyindividualFedDetails = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->getTerminologyindividualFedDetails($fedId, $hasSubfederation);
        $terminologydefaultFedDetails = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->getTerminologydefaultFedDetails($fedId, $this->clubDefaultSystemLang, $hasSubfederation);
        $defaultFedForLanguages = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->getTerminologydefaultFedDetails($fedId, '', $hasSubfederation);

        $form = $this->createForm(TerminologyContactForm::class, null, array('custom_value' => array('individualclub' => $terminologyindividualClubDetails, 
            'defaultclub' => $terminologydefaultClubDetails, 
            'defaultClubForLanguage' => $defaultClubForLanguages, 
            'defaultFedForLanguage' => $defaultFedForLanguages, 
            'clubLanguages' => $this->clubLanguages, 
            'clubLanguagesDet' => $clubLanguagesDet, 'translator' => $translator, 
            'transarray' => $transarray, 'entityManager' => $this->em, 'clubId' => $this->clubId)));
        $formFed = $this->createForm(TerminologyContactForm::class, null, array('custom_value' => array('individualclub' => $terminologyindividualFedDetails, 
            'defaultclub' => $terminologydefaultFedDetails, 
            'defaultClubForLanguage' => $defaultClubForLanguages, 
            'defaultFedForLanguage' => $defaultFedForLanguages, 
            'clubLanguages' => $this->clubLanguages, 
            'clubLanguagesDet' => $clubLanguagesDet, 'translator' => $translator, 
            'transarray' => $transarray, 'entityManager' => $this->em, 'clubId' => $this->clubId)));
        $return = array('clubDefaultLang' => $this->clubDefaultLang,
            'clubLanguages' => $this->clubLanguages,
            'clubType' => $this->clubType,
            'details' => $terminologydefaultClubDetails,
            'feddetails' => $terminologydefaultFedDetails,
            'form1' => $formFed->createView(),
            'form' => $form->createView(),
            'federation' => $federation,
            'bookedModulesDet' => $bookedModulesDet,
            'frontendModuleArray' => $frontendModuleArray,
            'settings' => true, 'tabs' => $this->getTabs("terminology")
        );
        return $this->render('ClubadminGeneralBundle:Settings:Terminology.html.twig', $return);
    }

    /**
     * This function is used to insert or update terminology details
     *
     * @return JsonResponse
     */
    public function updateAction(Request $request)
    {
        $club = $this->container->get('club');
        $domainCacheKey = $club->get('clubCacheKey');
        if ($request->getMethod() == 'POST') {
            $attributes = json_decode($request->request->get('attributes'), true);
            if (count($attributes) > 0) {
                $fgClubTerminologyObj = $this->em->getRepository('CommonUtilityBundle:FgClubTerminology')->saveTerminology($attributes, $this->clubDefaultSystemLang, $this->clubId, $this->clubLanguages, $this->clubDefaultLang, $domainCacheKey,$this->container);
            }
        }
        return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('TERMINOLOGY_SAVED')));
    }

    /**
     * This function is used to return translate fields array
     *
     * @return JsonResponse
     */
    public function translateFields()
    {
        $transarray = array('TERMINOLOGY_CLUB' => "Club",
            'TERMINOLOGY_CLUBS' => "Clubs",
            'TERMINOLOGY_TEAM' => "Team",
            'TERMINOLOGY_TEAMS' => "Teams",
            'TERMINOLOGY_TEAMMEMBER' => "Team member",
            'TERMINOLOGY_TEAMMEMBERS' => "Team members",
            'TERMINOLOGY_TEAMMEMBERPICTURE' => "Team member picture",
            'TERMINOLOGY_TEAMMEMBERPICTURES' => "Team member pictures",
            'TERMINOLOGY_TEAMMEMBERPROFILE' => "Team member profile",
            'TERMINOLOGY_TEAMMEMBERPROFILES' => "Team member profiles",
            'TERMINOLOGY_TEAMMEMBERLIST' => "Team member list",
            'TERMINOLOGY_TEAMMEMBERLISTS' => "Team member lists",
            'TERMINOLOGY_INTRANET' => "Intranet",
            'TERMINOLOGY_GALLERY' => "Gallery",
            'TERMINOLOGY_GALLERIES' => "Galleries",
            'TERMINOLOGY_OGANIZER' => "Oganizer",
            'TERMINOLOGY_OGANIZERS' => "Oganizers",
            'TERMINOLOGY_CALENDER' => "Calender",
            'TERMINOLOGY_CALENDERS' => "Calenders",
            'TERMINOLOGY_WEBSITE' => "Website",
            'TERMINOLOGY_EXECUTIVEBOARD' => "Executive Board",
            'TERMINOLOGY_FEDERATION' => "Federation",
            'TERMINOLOGY_SUBFEDERATION' => "Sub-federation",
            'TERMINOLOGY_SUBFEDERATIONS' => "Sub-federations",
            'TERMINOLOGY_FEDERATIONMEMBER' => "Federation member",
            'TERMINOLOGY_FEDERATIONMEMBERS' => "Federation members",
            'TERMINOLOGY_FEDERATIONMEMBERSHIP' => "Fed membership",
            'TERMINOLOGY_FEDERATIONMEMBERSHIPS' => "Fed memberships",
        );
        return $transarray;
    }

    /**
     * Function to get settings tabs
     *
     * @return type
     */
    private function getTabs($activeTab)
    {

        $tabs = array('language', 'salutations', 'terminology', 'agelimits', 'misc');
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', $activeTab, "settings");
        return $tabsData;
    }
}
