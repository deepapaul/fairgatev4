<?php

namespace Common\HelpBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{

    /**
     * Function to show quick-start window.
     *
     * @return template
     */
    public function indexAction(Request $request)
    {
        $currentModule = $request->get('module');
        $modules = array(
            'contact' => array('name' => 'contact', 'id' => '', 'text' => $this->get('translator')->trans('HELP_TAB_CONTACT'), 'activeClass' => '', 'isVisible' => true),
            'document' => array('name' => 'document', 'id' => '', 'text' => $this->get('translator')->trans('HELP_TAB_DOCUMENT'), 'activeClass' => ''),
            'communication' => array('name' => 'communication', 'id' => '', 'text' => $this->get('translator')->trans('HELP_TAB_COMMUNICATION'), 'activeClass' => ''),
            'sponsor' => array('name' => 'sponsor', 'id' => '', 'text' => $this->get('translator')->trans('HELP_TAB_SPONSOR'), 'activeClass' => ''),
        );
        $allowedModules = $this->container->get('contact')->get('allowedModules');

        if (in_array('readonly_contact', $allowedModules)) {
            $allowedModules[] = 'contact';
        }
        if (in_array('readonly_sponsor', $allowedModules)) {
            $allowedModules[] = 'sponsor';
        }

        $tabs = array_intersect_key($modules, array_flip($allowedModules));
        $tabs = $tabs + array('welcome' => array('name' => 'welcome', 'id' => '', 'text' => $this->get('translator')->trans('HELP_TAB_WELCOME'), 'activeClass' => ''));
        if (!in_array($currentModule, array_keys($modules))) {
            $currentModule = 'welcome';
        }
        $tabs[$currentModule]['activeClass'] = 'active';
        $newTabs = $this->filterDirectories($tabs);
        $defaultLng = $this->container->get('club')->get('default_system_lang');
        $overviews = array();
        $rootPath = $this->get('kernel')->getRootDir() . "/../src/Common/HelpBundle/Resources/views/";   
        //Check whether club is in testing period, If yes, return th remaining days of testing period else return false
        $remainingDays = $this->checkClubInTestingPeriod();
        $isRegistrationPeriod = 0; 
        foreach ($newTabs as $key => $value) {
            $twigPath = "CommonHelpBundle:$key:overview/$defaultLng.html.twig";
            if (!file_exists($rootPath . $key . '/overview/' . $defaultLng . '.html.twig')) {
                $twigPath = "CommonHelpBundle:$key:overview/en.html.twig";
            }
            //replace $twigPath for welcome tab if the club is in testing period
            if($remainingDays && $key == 'welcome') {
                $twigPath = "CommonHelpBundle:$key:overview/registration.html.twig";  
                $datas =  array('username' => $this->container->get('contact')->get('nameNoSort'), 'remainingDays' => $remainingDays);
                $isRegistrationPeriod = 1;
            } else {
                $datas = array();
            }
            $overviews[] = array(
                'twigPath' => $twigPath,
                'name' => $value['name'],
                'isVisible' => $value['isVisible'],
                'datas' => $datas
            );
        }

        $helpymlData = FgUtility::generateYmlData(getcwd() . '/../fairgate4/src/Common/HelpBundle/Resources/config/help.yml');
        $helpUrls = $helpymlData['help'];

        //current status of the visibility (will be true when once shown or dont show checked)
        $windowVisibility = $this->container->get('contact')->get('windowVisibilty');
        $quickwindowVisibilty = $this->container->get('contact')->get('quickwindowVisibilty');
        $quickwindowVisibiltyValue = ($quickwindowVisibilty) ? 'checked' : 'not_checked';
        $contactId = $this->container->get('contact')->get('id');

        $this->markQuickwindowShown();

        return $this->render('CommonHelpBundle:Default:quickWindow.html.twig', array('tabs' => $newTabs, 'isRegistrationPeriod' => $isRegistrationPeriod ,'url' => '', 'type' => '', 'overviews' => $overviews, 'windowVisibility' => $windowVisibility, 'currentModule' => $currentModule, 'helpUrls' => $helpUrls, 'quickwindowVisibilty' => $quickwindowVisibiltyValue, 'contactId' => $contactId));
    }
    
    /**
     * Check whether club is in testing period, If yes, return th remaining days of testing period else return false
     *
     * @return boolean|int
     */
    private function checkClubInTestingPeriod() {
        $adminEntityManager = $this->container->get('fg.admin.connection')->getAdminManager();
        $clubObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($this->container->get('club')->get('id'));
        if($clubObj->getClubCreationProcess() == 'Registration' && ($clubObj->getContractStartDate())) {            
            $expiringDate = $clubObj->getContractStartDate()->modify('+30 day');
            $todaysDate = new \DateTime("now");
            $interval = $todaysDate->diff($expiringDate);
            $remainingDays = $interval->format('%a');
        } else {
            $remainingDays = false;
        }
        
        return $remainingDays;
    }

    /**
     * Function to load the .
     *
     * @return template
     */
    public function resourceAction(Request $request)
    {
        $currentModule = $request->get('module');

        //current status of the visibility (will be true when once shown or dont show checked)
        $windowVisibility = $this->container->get('contact')->get('windowVisibilty');
        $quickwindowVisibilty = $this->container->get('contact')->get('quickwindowVisibilty');
        $quickwindowVisibiltyValue = ($quickwindowVisibilty) ? 'checked' : 'not_checked';
        $contactId = $this->container->get('contact')->get('id');

        return $this->render('CommonHelpBundle:Default:resource.html.twig', array('windowVisibility' => $windowVisibility, 'currentModule' => $currentModule, 'quickwindowVisibilty' => $quickwindowVisibiltyValue, 'contactId' => $contactId));
    }

    /**
     * Function to Check directories whether it exists
     *
     * @params Array $tabs
     *
     * @return Array $data
     */
    private function filterDirectories($tabs)
    {
        $rootPath = $this->get('kernel')->getRootDir() . "/../src/Common/HelpBundle/Resources/views/";
        $data = array();
        if (!empty($tabs)) {
            foreach ($tabs as $key => $tab) {
                if (is_dir($rootPath . $key)) {
                    $data[$key] = $tab;
                }
            }
        }

        return $data;
    }

    /**
     * Function to set visiblity of quick window
     *
     * Function enable Dont show quickwindow action
     *
     * @return response
     */
    public function quickWindowVisibilityAction(Request $request)
    {
        $checkedValue = $request->get('checkedValue', '1');
        $contactId = $this->container->get('contact')->get('id');
        $isSuperAdmin = $this->container->get('contact')->get('isSuperAdmin');
        $clubId = ($isSuperAdmin) ? 1 : $this->container->get('club')->get('id');
        $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgCmContact')
            ->quickWindowVisibility($contactId, $clubId, $checkedValue);

        return new JsonResponse(array('status' => 'SUCCESS'));
    }

    /**
     * Function to get quick-start window content deatails.
     *
     * @return template
     */
    public function getHelpContentDetailsAction(Request $request)
    {
        $target = $request->get('target', 'overview');
        $tab = $request->get('tab');
        $defaultLng = $this->container->get('club')->get('default_system_lang');
        $rootPath = $this->get('kernel')->getRootDir() . "/../src/Common/HelpBundle/Resources/views/";
        if (!file_exists($rootPath . '/' . $tab . '/' . $target . '/' . $defaultLng . '.html.twig')) {
            $defaultLng = 'en';
        }

        return $this->render("CommonHelpBundle:$tab:$target/$defaultLng.html.twig");
    }

    /**
     * Function to list display youtube playlists videos of a channel
     *
     * @return template
     */
    public function playlistVideoAction()
    {
        return $this->render('CommonHelpBundle:Default:videos.html.twig');
    }

    /**
     * Used for setting quick window visibility parameter
     *
     * @param $parameter
     */
    private function markQuickwindowShown()
    {
        $session = $this->container->get('session');
        $clubId = $this->container->get('club')->get('id');
        $contactId = $this->container->get('contact')->get('id');

        $session->set('windowVisibility_' . $clubId . '_' . $contactId, true);
        return;
    }
}
