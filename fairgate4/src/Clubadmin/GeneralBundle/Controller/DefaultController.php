<?php

namespace Clubadmin\GeneralBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Common\UtilityBundle\Util\FgUtility;

class DefaultController extends FgController
{

    public function indexAction($name)
    {
        return $this->render('ClubadminGeneralBundle:Default:index.html.twig', array('name' => $name));
    }

    /**
     * Function to display header menu in layout.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return JsonResponse Top navigation templates
     */
    public function headerAction(Request $request)
    {
        $contactService = $this->container->get('contact');
        $isSuperadmin = $contactService->get('isSuperAdmin');
        $module = $request->get('module');
        $level1 = $request->get('level1');
        $level2 = $request->get('level2');
        $moduleMenu = $request->get('moduleMenu');
        $hasCmsAccess = (in_array('cms', $contactService->get('allowedModules'))) ? true : false;
        $pageAccess = (in_array('page', $contactService->get('allowedModules'))) ? true : false;
        $counts = $this->getTopNavigationCounts();
        $helpymlData = FgUtility::generateYmlData(getcwd() . '/../fairgate4/src/Common/HelpBundle/Resources/config/help.yml');
        $clubService = $this->get('club');
        $clubSettings = array('c1' => $clubService->get('clubMembershipAvailable'));
        $helpUrls = $helpymlData['help'];
        if ($isSuperadmin) {
            $intranetAccess = true;
        } else {
            $intranetAccess = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkIntranetAccess($this->contactId);
        }
        $template1 = $this->renderView('::clubadmin/header.html.twig', array('moduleName' => $module, 'level1' => $level1, 'level2' => $level2, 'moduleMenu' => $moduleMenu, 'counts' => $counts, 'intranetAccess' => $intranetAccess, 'helpUrls' => $helpUrls, 'cmsAccess' => $hasCmsAccess, 'pageAccess' => $pageAccess,'clubSettings'=>$clubSettings));
        $template2 = $this->renderView('::clubadmin/header-menu.html.twig', array('moduleName' => $module, 'level1' => $level1, 'level2' => $level2, 'moduleMenu' => $moduleMenu, 'counts' => $counts, 'intranetAccess' => $intranetAccess, 'helpUrls' => $helpUrls, 'pageAccess' => $pageAccess,'clubSettings'=>$clubSettings));

        return new JsonResponse(array('template1' => htmlentities($template1, ENT_NOQUOTES, "UTF-8"), 'template2' => htmlentities($template2, ENT_NOQUOTES, "UTF-8")));
    }

    /**
     * Function to get top navigation counts.
     *
     * @return array $counts Counts array
     */
    private function getTopNavigationCounts()
    {
        $club = $this->get('club');
        $clubType = $club->get('type');
        $clubPdo = new \Common\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
        $navigCountRes = $clubPdo->getTopNavigationCount($clubType, $this->clubId, $club->get('clubHeirarchy'));       
        $externalapplicationCount = 0;
        if (in_array($this->clubId, $this->container->getParameter('external_application_clubids'))) {
            $externalapplicationCount = $this->em->getRepository('CommonUtilityBundle:FgExternalApplicationForm')->getExternalApplicationConfirmationCount($this->clubId);
        }
        $mergeApplicationCount = $appFormconfirmCount = 0;
        if (in_array($clubType, array('federation', 'federation_club', 'sub_federation_club'))) {
            $mergeApplicationCount = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getMergeApplicationsCount($this->federationId, $this->clubType, $this->clubId);
        }
        if (in_array($clubType, array('federation_club', 'sub_federation_club','standard_club'))) {
            $appFormconfirmCount = $this->em->getRepository('CommonUtilityBundle:FgCmsContactFormApplications')->getApplicationsToConfirm($this->clubId, true, array('PENDING'));
        }
        $totalConfirmations = $navigCountRes['confirmChanges'] + $navigCountRes['confirmMutations'] + $navigCountRes['confirmCreations']+$appFormconfirmCount;
        $counts = array(
            'activeContacts' => $navigCountRes['active'], 'archiveContacts' => $navigCountRes['archive'], 'subscriberCount' => $navigCountRes['subscriberCount'],
            'clubCount' => $navigCountRes['clubCount'],
            'teamDocCount' => $navigCountRes['teamDocCount'], 'workgroupDocCount' => $navigCountRes['workgroupDocCount'], 'contactDocCount' => $navigCountRes['contactDocCount'], 'clubDocCount' => $navigCountRes['clubDocCount'],
            'sponsorCount' => $navigCountRes['sponsorCount'], 'archivedSponsorCount' => $navigCountRes['archivedSponsorCount'],
            'confirmChanges' => $navigCountRes['confirmChanges'], 'confirmMutations' => $navigCountRes['confirmMutations'], 'confirmCreations' => $navigCountRes['confirmCreations'], 'totalConfirmations' => $totalConfirmations,
            'applicationConfirmCount' => $navigCountRes['applicationConfirmCount'],
            'externalapplicationCount' => $externalapplicationCount, 'mergeApplicationsCount' => $mergeApplicationCount,'appFormconfirmCount' => $appFormconfirmCount,
            'confirmappclubassignment' => $navigCountRes['confirmappclubassignment'], 'confirmChanges' => $navigCountRes['confirmChanges'], 'confirmMutations' => $navigCountRes['confirmMutations'], 'confirmCreations' => $navigCountRes['confirmCreations'],
            'totalAppConfirmation' => ($navigCountRes['applicationConfirmCount'] + $navigCountRes['confirmappclubassignment'] + $externalapplicationCount + $mergeApplicationCount + $appFormconfirmCount)
        );
        if ($clubType == 'federation' || ($clubType == 'sub_federation')) {
            $counts['formerfedContacts'] = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getFormerfederationmemberCount($this->clubId);
        }
        $this->setCountsInSession($counts);

        return $counts;
    }

    /**
     * Function to set top navigation counts in session.
     *
     * @param array  $counts Counts array
     */
    private function setCountsInSession($counts)
    {
        $session = $this->get('session');
        $session->set('navigationCounts', $counts);
    }
    
    /**
     * Page for clearing localstorage
     * 
     * @return object View Template Render Object
     */
    public function clearLocalstorageAction() {
        return $this->render('ClubadminGeneralBundle:Default:clearLocalstorage.html.twig');
    }
}
