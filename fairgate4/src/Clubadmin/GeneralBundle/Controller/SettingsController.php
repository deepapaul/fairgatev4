<?php

/**
 * SettingsController
 * This controller is used to handle club settings
 * @package    CommonUtilityBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
namespace Clubadmin\GeneralBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\Request;
use FairgateApiBundle\Util\GotCourtsApiDetails;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgPermissions;

class SettingsController extends FgController
{

    /**
     * This function is used to render the language tab
     * @return HTML
     */
    public function indexAction()
    {

        $responseArray = array();
        $responseArray['tabs'] = $this->getTabs("language");
        $responseArray['languages'] = \Common\UtilityBundle\Util\FgUtility::getAllLanguageNames();
        $responseArray['langSettings'] = FgUtility::getLocaleSettings($this->container);
        $responseArray['name'] = $this->get('contact')->get('name');
        $syslangArray = $this->em->getRepository('CommonUtilityBundle:FgClubLanguage')->getSystemLangOfCorr($this->clubId, $this->get('contact')->get('corrLang'));
        $responseArray['corrLang'] = Intl::getLanguageBundle()->getLanguageName($syslangArray);
        $responseArray['systemLangs'] = \Common\UtilityBundle\Util\FgUtility::getDefaultLanguages($this->container);
        $responseArray['deleteFlag'] = ($this->get('club')->get('type') == 'federation' || $this->get('club')->get('type') == 'standard_club') ? 1 : 0;
        $pdo = new ContactPdo($this->container);
        if ($this->get('contact')->get('isSuperAdmin') == true) {
            $responseArray['perSysLang'] = 'default';
            $responseArray['perSessionHide'] = true;
        } else {
            $contact = $this->get('contact')->get('id');
            $clubTable = $this->get('club')->get('clubTable');

            $responseArray['perSysLang'] = $pdo->getContactSystemLanguage($contact, $clubTable);
        }
        $responseArray['isEditable'] = ($this->get('club')->get('type') == 'federation' || $this->get('club')->get('type') == 'standard_club') ? true : false;
        return $this->render('ClubadminGeneralBundle:Settings:index.html.twig', $responseArray);
    }

    /**
     * Function to handle misc action
     *
     * @return type
     */
    public function miscAction()
    {
        $club = $this->container->get('club');
        $responseArray = array();
        $responseArray['tabs'] = $this->getTabs("misc");
        $responseArray['clubDefaultSubscription'] = $this->adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->getDefaultSubscription($this->clubId);
        $responseArray['details'] = $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->getClubSettingsDetail($this->clubId, $club->get('default_lang'));
        $responseArray['currencies'] = Intl::getCurrencyBundle()->getCurrencyNames();
        $responseArray['settings'] = true;
        $gcApiUtil = new GotCourtsApiDetails($this->container);
        $responseArray['gcApiDetails'] = $gcApiUtil->getGotCourtsApiDetails();
        $responseArray['isAdmin'] = $gcApiUtil->isMainAdmin();

        return $this->render('ClubadminGeneralBundle:Settings:misc.html.twig', $responseArray);
    }

    /**
     * This method is used show GotCourts log listing 
     * 
     * @return void
     */
    public function apiServiceLogAction()
    {
        $gcApiUtil = new GotCourtsApiDetails($this->container);
        $permissionObj = new FgPermissions($this->container);
        if (!$gcApiUtil->isMainAdmin()) {
            $permissionObj->checkUserAccess(0, 'no_access');
        }
        $responseArray = array();
        $responseArray['tabs'] = $this->getTabs("apilog");
        return $this->render('ClubadminGeneralBundle:Settings:apiServiceLog.html.twig', $responseArray);
    }

    /**
     * This method is used to get gotcourts api log data 
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function getGotCourtsApiLogAction(Request $request)
    {
        $clubObj = $this->container->get('club');

        $startDate = $request->get('startDate');
        if ($startDate != '') {
            $date = new \DateTime();
            $filterArray['startDate'] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $startDate)->format('Y-m-d');
            $filterArray['startDate'] = $filterArray['startDate'] . ' 00:00:00';
        }

        $endDate = $request->get('endDate');
        if ($endDate != '') {
            $date = new \DateTime();
            $filterArray['endDate'] = $date->createFromFormat(FgSettings::getPhpDateFormat(), $endDate)->format('Y-m-d');
            $filterArray['endDate'] = $filterArray['endDate'] . ' 23:59:59';
        }

        $logList = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourtsLog')->getServiceLog($filterArray, $clubObj);
        $totalCount = $this->getDoctrine()->getManager()->getRepository('CommonUtilityBundle:FgApiGotcourtsLog')->getServiceLogCount($filterArray, $clubObj->get('id'));

        $return['aaData'] = $logList;
        $return['iTotalDisplayRecords'] = $totalCount['totalCount'];
        $return['iTotalRecords'] = $totalCount['totalCount'];

        return new JsonResponse($return);
    }

    /**
     * Function to handle misc save
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function miscSaveAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $currency = $request->request->get('currency');
            $settingsId = $request->request->get('settingsId');
            $club = $this->get('club');
            $domainCacheKey = $club->get('clubCacheKey');
            /********************************************************************
            * FAIRDEV-336- Restrict club's changing of currency in club settings. 
            ********************************************************************/           
            $clubSettingsObj = $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->find($settingsId);
            if(!$clubSettingsObj || $clubSettingsObj->getCurrency() == ''){
                $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->clubCurrencySave($this->clubId, $currency, $settingsId, $domainCacheKey);
            }
            
            $defaultSubArr = array('fg-dev-default-subscription' => $request->request->get('fg-dev-default-subscription', 0));
            $this->em->getRepository('CommonUtilityBundle:FgClub')->saveDefaultSubscription($this->clubId, $defaultSubArr);
            $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
            $clubPdo->saveSettingsUpdatedDate($this->clubId);

            return new JsonResponse(array('status' => 'SUCCESS', 'noreload' => true, 'flash' => $this->get('translator')->trans('SETTINGS_MISC_UPDATED')));
        }
    }

    /**
     * This action is used for saving the age limit settings
     *
     *
     *
     * @return Template
     */
    public function agelimitsAction()
    {
        $responseArray = array();
        $clubSettingsObj = $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->findOneBy(array('club' => $this->clubId));
        $responseArray['defaultMajorityAgeLimit'] = ($clubSettingsObj->getMajorityAge() != '') ? $clubSettingsObj->getMajorityAge() : 18;
        $responseArray['defaultMinorityAgeLimit'] = ($clubSettingsObj->getProfileAccessAge() != '') ? $clubSettingsObj->getProfileAccessAge() : 16;
        $responseArray['tabs'] = $this->getTabs("agelimits");
        return $this->render('ClubadminGeneralBundle:Settings:agelimits.html.twig', $responseArray);
    }

    /**
     * Function to update the majority age and profile access age settings of a club
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function updateContactAgeLimitsAction(Request $request)
    {
        $defaultMajorityAgeLimit = $request->get('majorityAge');
        $defaultMinorityAgeLimit = $request->get('minorityAge');
        $this->em->getRepository('CommonUtilityBundle:FgClubSettings')->updateAgeLimitSettings($this->clubId, $defaultMajorityAgeLimit, $defaultMinorityAgeLimit);

        return new JsonResponse(array('status' => 'SUCCESS', 'noparentload' => true, 'flash' => $this->get('translator')->trans('AGE_LIMIT_UPDATE_SUCCESS_MSG')));
    }

    /**
     * Get club languages array
     *
     * @return JsonResponse
     */
    public function getLanguageAction()
    {
        $languages = $this->em->getRepository('CommonUtilityBundle:FgClubLanguage')->getAllClubLanguages($this->get('club'));
        if (count($languages) > 0) {
            $languages[0]['languages'] = \Common\UtilityBundle\Util\FgUtility::getAllLanguageNames();
            $languages[0]['langSettings'] = FgUtility::getLocaleSettings($this->container);
        }

        return new JsonResponse($languages);
    }

    /**
     * Function to contact count with correspondence lang
     * @return JsonResponse
     */
    public function checkContactWithCorrLangAction()
    {
        $contacts = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactCountByCorrLang($this->container);

        return new JsonResponse($contacts);
    }

    /**
     * Function to save language settings
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function languagesaveAction(Request $request)
    {
        $saveData = $this->checkLangUnique(json_decode($request->request->get('saveData'), true));
        $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
        $clubPdo->saveSettingsUpdatedDate($this->clubId);
        $this->em->getRepository('CommonUtilityBundle:FgClubLanguage')->updateLanguages($saveData['langs'], $this->container);
        if (!$this->get('contact')->get('isSuperAdmin') && !empty($saveData['personalLanguage'])) {
            $pdo = new ContactPdo($this->container);
            $pdo->updateContactSystemLanguage($this->get('contact')->get('id'), $this->get('club')->get('clubTable'), $saveData['personalLanguage']);
        }

        //Remove apc cache entries while updating the data
        $club = $this->container->get('club');
        $domainCacheKey = $club->get('clubCacheKey');
        $cacheKey = str_replace('{{cache_area}}', 'club_language', $domainCacheKey);
        $cacheDriver = $this->em->getConfiguration()->getResultCacheImpl();
        $cacheDriver->deleteByPrefix($cacheKey);

        return new JsonResponse(array('status' => true, 'noparentload' => true, 'flash' => $this->get('translator')->trans('LANGUAGE_UPDATE_SUCCESS_MSG')));
    }

    /**
     * This method is used to filter unique duplicated languages from form data.
     * 
     * @param type $data input form data
     * 
     * @return type
     */
    private function checkLangUnique($data)
    {
        $lang = array();
        $result = array();
        $languages = $this->em->getRepository('CommonUtilityBundle:FgClubLanguage')->getAllClubLanguages($this->get('club'));
        $corresLangs = array_column($languages, 'correspondanceLang');
        $corresLangIds = array_column($languages, 'id');
        foreach ($data['langs'] as $key => $val) {
            //To remove duplicate languages
            if ($val['isDeleted'] == 1 || in_array($key, $corresLangIds) || (!in_array($val['language'], $lang) && !in_array($val['language'], $corresLangs))) {
                $result[$key] = $val;
            }
            $lang[] = $val['language'];
        }

        return array('langs' => $result);
    }

    /**
     * The salutation listing page
     *
     * @param Request $request Request Object
     *
     * @return Template
     */
    public function salutationsAction(Request $request)
    {
        $rowDefaultSalutation = $this->em->getRepository('CommonUtilityBundle:FgClubSalutationSettings')->getDefaultSalutation();
        $defaultSalutation = $this->getDefaultSalutationLangWise($rowDefaultSalutation);
        $bookedModuleDetails = $this->get('club')->get('bookedModulesDet');
        $clublanguageDetails = $this->get('club')->get('club_languages_det');
        $formArray = array();
        $languages = array();
        $id = '';

        $clubSalutation = $this->em->getRepository('CommonUtilityBundle:FgClubSalutationSettings')->getClubSalutation($this->clubId);
        foreach ($clubSalutation as $clubSal) {
            if (!in_array($clubSal['lang'], $this->clubLanguages)) {
                continue;
            }
            $id = $clubSal['id'];
            $languages[] = $clubSal['lang'];
            $formArray['female']['formal'][$clubSal['lang']] = $clubSal['femaleFormal'];
            $formArray['female']['informal'][$clubSal['lang']] = $clubSal['femaleInformal'];
            $formArray['male']['formal'][$clubSal['lang']] = $clubSal['maleFormal'];
            $formArray['male']['informal'][$clubSal['lang']] = $clubSal['maleInformal'];
            $formArray['family']['formal'][$clubSal['lang']] = $clubSal['familyFormal'];
            $formArray['family']['informal'][$clubSal['lang']] = $clubSal['familyInformal'];
            $formArray['company_nomain_contact'][$clubSal['lang']] = $clubSal['companyNoMain'];
            $formArray['subscriber'][$clubSal['lang']] = $clubSal['subscriber'];
        }
        $languages = array_diff($this->clubLanguages, $languages);
        foreach ($languages as $lang) {
            $formArray['female']['formal'][$lang] = '';
            $formArray['female']['informal'][$lang] = '';
            $formArray['male']['formal'][$lang] = '';
            $formArray['male']['informal'][$lang] = '';
            $formArray['family']['formal'][$lang] = '';
            $formArray['family']['informal'][$lang] = '';
            $formArray['company_nomain_contact'][$lang] = '';
            $formArray['subscriber'][$lang] = '';
        }

        return $this->render('ClubadminGeneralBundle:Settings:salutations.html.twig', array('id' => $id,
                'rowSalutation' => $formArray,
                'default' => $defaultSalutation,
                'clubLanguages' => $this->clubLanguages,
                'clubDefaultLang' => $this->clubDefaultLang,
                'clublanguageDetails' => $clublanguageDetails,
                'bookedModule' => $bookedModuleDetails,
                'tabs' => $this->getTabs("salutations"),
                'settings' => true));
    }

    /**
     * Function to manipulate setting for system languague to show in place holder
     *
     * @return Template
     */
    private function getDefaultSalutationLangWise($defaultSalutation)
    {
        $defaultFormArray = array();
        foreach ($defaultSalutation as $defaultSal) {
            $defaultFormArray['femaleFormal'][$defaultSal['lang']] = $defaultSal['femaleFormal'];
            $defaultFormArray['femaleInformal'][$defaultSal['lang']] = $defaultSal['femaleInformal'];
            $defaultFormArray['maleFormal'][$defaultSal['lang']] = $defaultSal['maleFormal'];
            $defaultFormArray['maleInformal'][$defaultSal['lang']] = $defaultSal['maleInformal'];
            $defaultFormArray['family']['formal'][$defaultSal['lang']] = $defaultSal['familyFormal'];
            $defaultFormArray['familyFormal'][$defaultSal['lang']] = $defaultSal['familyInformal'];
            $defaultFormArray['familyInformal'][$defaultSal['lang']] = $defaultSal['familyInformal'];
            $defaultFormArray['companyNoMain'][$defaultSal['lang']] = $defaultSal['companyNoMain'];
            $defaultFormArray['subscriber'][$defaultSal['lang']] = $defaultSal['subscriber'];
        }
        return $defaultFormArray;
    }

    /**
     * The salutation listing page
     *
     * @param Request $request Request Object
     *
     * @return JsonResponse
     */
    public function salutationssaveAction(Request $request)
    {
        $bookedModuleDetails = $this->get('club')->get('bookedModulesDet');

        if ($request->getMethod() == 'POST') {
            $id = $request->request->get('id', '');
            $sal = $request->request->get('salutation');
            $fgClub = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($this->clubId);
            $fgClubSalutation = $this->em->getRepository('CommonUtilityBundle:FgClubSalutationSettings')->find($id);
            if ($fgClubSalutation) {
                
            } else {
                $fgClubSalutation = new \Common\UtilityBundle\Entity\FgClubSalutationSettings();
            }
            $fgClubSalutation->setClub($fgClub);
            $fgClubSalutation->setFemaleFormal(is_null($sal[$this->clubDefaultLang]['femaleFormal']) ? '' : $sal[$this->clubDefaultLang]['femaleFormal']);
            $fgClubSalutation->setFemaleInformal(is_null($sal[$this->clubDefaultLang]['femaleInformal']) ? '' : $sal[$this->clubDefaultLang]['femaleInformal']);
            $fgClubSalutation->setMaleFormal(is_null($sal[$this->clubDefaultLang]['maleFormal']) ? '' : $sal[$this->clubDefaultLang]['maleFormal']);
            $fgClubSalutation->setMaleInformal(is_null($sal[$this->clubDefaultLang]['maleInformal']) ? '' : $sal[$this->clubDefaultLang]['maleInformal']);
            /* Sets the family salutation only if invoice is booked */
            if (in_array('invoice', $bookedModuleDetails)) {
                $fgClubSalutation->setFamilyFormal(is_null($sal[$this->clubDefaultLang]['familyFormal']) ? '' : $sal[$this->clubDefaultLang]['familyFormal']);
                $fgClubSalutation->setFamilyInformal(is_null($sal[$this->clubDefaultLang]['familyInformal']) ? '' : $sal[$this->clubDefaultLang]['familyInformal']);
            } else {
                $fgClubSalutation->setFamilyFormal('');
                $fgClubSalutation->setFamilyInformal('');
            }
            /* Ends here */
            $fgClubSalutation->setCompanyNoMaincontact(is_null($sal[$this->clubDefaultLang]['company_nomain_contact']) ? '' : $sal[$this->clubDefaultLang]['company_nomain_contact']);
            $fgClubSalutation->setSubscriber(is_null($sal[$this->clubDefaultLang]['subscriber']) ? '' : $sal[$this->clubDefaultLang]['subscriber']);
            $this->em->persist($fgClubSalutation);
            $this->em->flush();
            $id = $fgClubSalutation->getId();

            foreach ($this->clubLanguages as $lang) {
                $fgClubSalutationi18n = $this->em->getRepository('CommonUtilityBundle:FgClubSalutationSettingsI18n')->findOneBy(array('id' => $id, 'lang' => $lang));

                if ($fgClubSalutationi18n) {
                    
                } else {
                    $fgClubSalutationi18n = new \Common\UtilityBundle\Entity\FgClubSalutationSettingsI18n();
                }
                $fgClubSalutationi18n->setId($id);
                $fgClubSalutationi18n->setLang($lang);
                $fgClubSalutationi18n->setFemaleFormalLang(is_null($sal[$lang]['femaleFormal']) ? '' : $sal[$lang]['femaleFormal']);
                $fgClubSalutationi18n->setFemaleInformalLang(is_null($sal[$lang]['femaleInformal']) ? '' : $sal[$lang]['femaleInformal']);
                $fgClubSalutationi18n->setMaleFormalLang(is_null($sal[$lang]['maleFormal']) ? '' : $sal[$lang]['maleFormal']);
                $fgClubSalutationi18n->setMaleInformalLang(is_null($sal[$lang]['maleInformal']) ? '' : $sal[$lang]['maleInformal']);
                /* Sets the family salutation only if invoice is booked */
                if (in_array('invoice', $bookedModuleDetails)) {
                    $fgClubSalutationi18n->setFamilyFormalLang(is_null($sal[$lang]['familyFormal']) ? '' : $sal[$lang]['familyFormal']);
                    $fgClubSalutationi18n->setFamilyInformalLang(is_null($sal[$lang]['familyInformal']) ? '' : $sal[$lang]['familyInformal']);
                } else {
                    $fgClubSalutationi18n->setFamilyFormalLang('');
                    $fgClubSalutationi18n->setFamilyInformalLang('');
                }
                /* Ends here */
                $fgClubSalutationi18n->setCompanyNoMaincontactLang(is_null($sal[$lang]['company_nomain_contact']) ? '' : $sal[$lang]['company_nomain_contact']);
                $fgClubSalutationi18n->setSubscriberLang(is_null($sal[$lang]['subscriber']) ? '' : $sal[$lang]['subscriber']);
                $this->em->persist($fgClubSalutationi18n);
            }
            $this->em->flush();

            $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($this->container);
            $clubPdo->saveSettingsUpdatedDate($this->clubId);

            return new JsonResponse(array('status' => 'SUCCESS', 'flash' => $this->get('translator')->trans('SALUTATION_UPDATE_SUCCESS')));
        }
    }

    /**
     * This function is used to render the groups template
     * @return HTML
     */
    public function groupsAction()
    {
        $responseArray = array();
        $responseArray['tabs'] = $this->getTabs("groups");

        throw $this->createNotFoundException('NO ACCESS');
    }

    private function getTabs($activeTab)
    {

        $tabs = array('language',
            'salutations',
            'terminology',
            'agelimits',
            'misc',
            'apilog');
        $tabsData = FgUtility::getTabsArrayDetails($this->container, $tabs, '', '', '', $activeTab, "settings");
        return $tabsData;
    }
}
