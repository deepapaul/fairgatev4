<?php

namespace Clubadmin\SponsorBundle\Controller;

use Common\UtilityBundle\Controller\FgController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Common\UtilityBundle\Iterator\FgArrayIterator;
use Common\UtilityBundle\Util\FgUtility;
use Clubadmin\SponsorBundle\Util\Sponsorfilter;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Clubadmin\Util\Contactlist;
use Clubadmin\Classes\Contactfilter;
use Symfony\Component\HttpFoundation\Response;
use Common\UtilityBundle\Repository\Pdo\membershipPdo;

class FilterController extends FgController
{
    /**
     *  Function to handle the all field data.
     *
     * @param String $contacttype type of contact
     *
     * @return JsonResponse
     */
    public function filterDataAction($contacttype)
    {
        $filterData = array();
        $filterData = $this->membershipFields();
        //Contact fields
        $filterData['CF']['title'] = $this->get('translator')->trans('CM_CONTACT_FIELDS');
        $filterData['CF']['id'] = 'CF';
        $filterData['CF']['show_filter'] = 1;
        $filterData['CF']['fixed_options'][0][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_CONTACT_FIELD'));
        $filterData['CF']['fixed_options'][1][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_VALUE'));
        $filterData['CF']['entry'] = $this->contactFieldArray();

        $filterData['G']['id'] = 'G';
        $filterData['G']['show_filter'] = 0;
        $filterData['G']['entry'] = array(
            '0' => array('id' => 'profile_company_pic',
                'title' => $this->get('translator')->trans('CM_PROFILE_COMPANY_PIC'),
                'selectgroup' => $this->get('translator')->trans('CM_PROFILE_IMG'),
        ), );

        $sponsorFilterObj = new Sponsorfilter($this->container, array());
        /* sponserAnalysis  */
        $filterData['SA'] = $sponsorFilterObj->sponserAnalysis($contacttype);

        $contactData = $this->getSidebarSponsor();
        $filterData['bookmark']['show_filter'] = 0;
        $filterData['bookmark']['id'] = 'bookmark';
        $filterData['bookmark']['entry'] = $contactData['bookmark']['entry'];

        $filterData['CN']['id'] = 'CN';
        $filterData['CN']['show_filter'] = 0;
        $filterData['CN']['title'] = $this->get('translator')->trans('SIDEBAR_CONTACTS');
        $filterData['CN']['entry'] = $contactData['CN']['entry'];

        $filterData['CO'] = $this->contactOptions();
        $filterData['SS'] = $sponsorFilterObj->sponsorServices($contacttype);
        $filterData['AO'] = $contactData['AO'];
        $filterData['AO']['show_filter'] = 0;

        /* Sponsor Saved Filters */
        $allSavedFilterArray = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSavedSidebarFilter($this->contactId, $this->clubId, 'sponsor');
        $allSavedFilter = $this->iterateFilter($allSavedFilterArray);
        $filterData['filter']['show_filter'] = 0;
        $filterData['filter']['id'] = 'filter';
        $filterData['filter']['title'] = 'filter';
        $filterData['filter']['entry'] = $allSavedFilter;

        return new JsonResponse($filterData);
    }

    /**
     * create static sidebar sponser array.
     *
     * @param array $memberships
     *
     * @return string
     */
    private function getSidebarSponsor()
    {
        $filterData = array();
        $typeArray = array();
        $bookmarkDetails = $this->em->getRepository('CommonUtilityBundle:FgSmBookmarks')->getSponsorBookmark($this->contactId, $this->clubId, $this->container);
        $bookmark ['prospect'] = $bookmark ['future_sponsor'] = $bookmark ['active_assignments'] = $bookmark ['future_assignments'] = $bookmark ['recently_ended'] = $bookmark ['former_assignments'] = $bookmark ['active_sponsor'] = $bookmark ['former_sponsor'] = $bookmark['single_person'] = $bookmark ['company'] = '';
        foreach ($bookmarkDetails as $key => $value) {
            switch ($value['itemType']) {
                case 'prospect': $bookmark ['prospect'] = $value['bookMarkId'];
                    break;
                case 'future_sponsor': $bookmark ['future_sponsor'] = $value['bookMarkId'];
                    break;
                case 'active_sponsor': $bookmark ['active_sponsor'] = $value['bookMarkId'];
                    break;
                case 'former_sponsor': $bookmark ['former_sponsor'] = $value['bookMarkId'];
                    break;
                case 'single_person': $bookmark ['single_person'] = $value['bookMarkId'];
                    break;
                case 'company': $bookmark ['company'] = $value['bookMarkId'];
                    break;
            }
            switch ($value['id']) {
                case 'active_assignments': $bookmark ['active_assignments'] = $value['bookMarkId'];
                    break;
                case 'future_assignments': $bookmark ['future_assignments'] = $value['bookMarkId'];
                    break;
                case 'recently_ended': $bookmark ['recently_ended'] = $value['bookMarkId'];
                    break;
                case 'former_assignments': $bookmark ['former_assignments'] = $value['bookMarkId'];
                    break;
            }
            if ($value['itemType'] == 'filter') {
                $bookmarkDetails[$key]['id'] = $value['filterId'];
                $bookmarkDetails[$key]['title'] = $value['filterTitle'];
                $bookmarkDetails[$key]['menuItemId'] = 'bookmark_li_filter_'.$value['filterId'];
            }
        }

        $filterData['bookmark']['entry'] = $bookmarkDetails;
        $typeArray[] = array('id' => 'prospect', 'title' => $this->get('translator')->trans('PROSPECTS'), 'type' => 'number', 'itemType' => 'prospect', 'bookMarkId' => $bookmark ['prospect'], 'image' => '<i class="fa fg-star-o"></i>');
        $typeArray[] = array('id' => 'future_sponsor', 'title' => $this->get('translator')->trans('FUTURE_SPONSORS'), 'itemType' => 'future_sponsor', 'bookMarkId' => $bookmark ['future_sponsor'], 'image' => '<i class="fa fg-star"></i>');
        $typeArray[] = array('id' => 'active_sponsor', 'title' => $this->get('translator')->trans('ACTIVE_SPONSORS'), 'itemType' => 'active_sponsor', 'bookMarkId' => $bookmark ['active_sponsor'], 'image' => '<i class="fa fg-star"></i>');
        $typeArray[] = array('id' => 'former_sponsor', 'title' => $this->get('translator')->trans('FORMER_SPONSORS'), 'itemType' => 'former_sponsor', 'bookMarkId' => $bookmark ['former_sponsor'], 'image' => '<i class="fa fg-star-half"></i>');
        $typeArray1[] = array('id' => 'single_person', 'title' => $this->get('translator')->trans('SIDEBAR_SINGLE_PERSON'), 'itemType' => 'single_person', 'bookMarkId' => $bookmark ['single_person'], 'image' => '<i class="fa  fa-user"></i>');
        $typeArray1[] = array('id' => 'company', 'title' => $this->get('translator')->trans('SIDEBAR_COMPANIES'), 'itemType' => 'company', 'bookMarkId' => $bookmark ['company'], 'image' => '<i class="fa fa-building-o"></i>');

        $filterData['CN']['entry'][0] = array('id' => 'sponsor_type', 'title' => $this->get('translator')->trans('SIDEBAR_SPONSOR_STATUS'), 'type' => 'select', 'input' => $typeArray);
        $filterData['CN']['entry'][1] = array('id' => 'contact_type', 'title' => $this->get('translator')->trans('SIDEBAR_CONTACT_TYPE'), 'type' => 'select', 'input' => $typeArray1);

        return $filterData;
    }

    /**
     * Function to get the club fields.
     *
     * @return array
     */
    private function contactFieldArray()
    {
        //Get all contact fields for a club
        $fieldLanguages = FgUtility::getClubLanguageNames($this->clubLanguages);
        $countryList = FgUtility::getCountryListchanged();
        $club = $this->get('club');
        $rowFieldsArray = $club->get('contactFields');
        $salutationTrans = $genderTrans = array();
        $salutationTrans[] = array('id' => 'Informal', 'title' => $this->get('translator')->trans('CM_INFORMAL'));
        $salutationTrans[] = array('id' => 'Formal', 'title' => $this->get('translator')->trans('CM_FORMAL'));
        $genderTrans[] = array('id' => 'Male', 'title' => $this->get('translator')->trans('CM_MALE'));
        $genderTrans[] = array('id' => 'Female', 'title' => $this->get('translator')->trans('CM_FEMALE'));
        $iterator = new \RecursiveArrayIterator($rowFieldsArray);
        $newiterator = new FgArrayIterator($iterator);
        $newiterator->translator = $this->get('translator');
        $newiterator->correspondancelang = $this->container->getParameter('system_field_corress_lang');
        $newiterator->nationality1 = $this->container->getParameter('system_field_nationality1');
        $newiterator->nationality2 = $this->container->getParameter('system_field_nationality2');
        $newiterator->nationality1 = $this->container->getParameter('system_field_team_picture');
        $newiterator->nationality2 = $this->container->getParameter('system_field_communitypicture');
        $newiterator->correspondaceLand = $this->container->getParameter('system_field_corres_land');
        $newiterator->invoiceLand = $this->container->getParameter('system_field_invoice_land');
        $newiterator->gender = $this->container->getParameter('system_field_gender');
        $newiterator->salutation = $this->container->getParameter('system_field_salutaion');
        $newiterator->genderTrans = $genderTrans;
        $newiterator->salutationTrans = $salutationTrans;
        foreach ($fieldLanguages as $lanKey => $lanValue) {
            $newiterator->clubLanguages[] = array('id' => $lanKey, 'title' => $lanValue);
        }
        foreach ($countryList as $cnKey => $cnValue) {
            $newiterator->countryList[] = array('id' => $cnKey, 'title' => $cnValue);
        }
        $newiterator->filterType = 'contactFields';
        /* This is an empty iterator  to iterate FgArrayIterator * */
        foreach ($newiterator as $key => $item) {
            //iterate
        }
        $clubFieldsArray = $newiterator->getResult();

        return $clubFieldsArray;
    }
    
    /**
     * membership/fed membership
     * 
     * @param object $terminologyService    terminology Service
     * @param array $fedmemberships         fedmemberships
     * @param array $memberships            memberships
     * @return array
     */
    private function membershipFields(){
          $club = $this->get('club');
        $terminologyService = $this->get('fairgate_terminology_service');
        /* Get all memberships */
        $objMembershipPdo = new membershipPdo($this->container);
        $rowMemberships = $objMembershipPdo->getMemberships($this->clubType, $this->clubId, $this->subFederationId, $this->federationId, $this->contactId);

        $memberships = $fedmembership = array();
        if ($this->clubType != 'standard_club') {
            if (($this->clubType != 'federation' && $this->clubType != 'sub_federation') && $club->get('clubMembershipAvailable')) {
                $memberships = $this->iterateMemberships($rowMemberships);
            }
            $fedmembership = $this->iterateMemberships($rowMemberships, true);
        } else {
            $memberships = $this->iterateMemberships($rowMemberships);
        }
        
        $filterData = array();
        $comInput = array();
        $comInput[] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_MEMBER'));
        $comInput[] = array('id' => 'any', 'title' => $this->get('translator')->trans('CM_ANY_MEMBER'));
        $federationTerTitle = $terminologyService->getTerminology('Federation', $this->container->getParameter('singular'));
        if($this->clubType == 'standard_club' || $this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club'){
            if($club->get('clubMembershipAvailable')){
                $filterData['CM']['title'] = $this->get('translator')->trans('CM_MEMBERSHIP');
                $filterData['CM']['id'] = 'CM';
                $filterData['CM']['fixed_options'][0][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_OPTIONS'));
                $filterData['CM']['entry'][] = array('id' => 'membership', 'title' => $this->get('translator')->trans('CM_MEMBERSHIP'), 'type' => 'select', 'input' => array_merge($comInput, $memberships),'shortTitle'=>$this->get('translator')->trans('CM_MEMBERSHIP'));
                $filterData['CM']['entry'][] = array('id' => 'CMfirst_joining_date', 'title' => $this->get('translator')->trans('CM_FIRST_JOINING_DATE'), 'type' => 'date','shortTitle'=>$this->get('translator')->trans('CM_FIRST_JOINING_DATE_SHORTNAME'));
                $filterData['CM']['entry'][] = array('id' => 'CMjoining_date', 'title' => $this->get('translator')->trans('CM_LATEST_JOINING_DATE'), 'type' => 'date','shortTitle'=>$this->get('translator')->trans('CM_LATEST_JOINING_DATE'));
                $filterData['CM']['entry'][] = array('id' => 'CMleaving_date', 'title' => $this->get('translator')->trans('CM_LEAVING_DATE'), 'type' => 'date','shortTitle'=>$this->get('translator')->trans('CM_LEAVING_DATE'));
            }
        }
        if ($this->clubType != 'standard_club' )   {
            $filterData['FM']['title'] =  ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('singular')));
            $filterData['FM']['id'] = 'FM';
            $filterData['FM']['fixed_options'][0][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_OPTIONS'));
            $filterData['FM']['entry'][] = array('id' => 'fed_membership', 'title' => ucfirst($terminologyService->getTerminology('Fed membership', $this->container->getParameter('plural'))), 'type' => 'select', 'input' => array_merge($comInput, $fedmembership));
            $filterData['FM']['entry'][] = array('id' => 'FMfirst_joining_date', 'title' => $this->get('translator')->trans('FM_FIRST_JOINING_DATE',array('%federation%' => $federationTerTitle)), 'type' => 'date','shortTitle'=>$this->get('translator')->trans('FM_FIRST_JOINING_DATE_SHORTNAME', array('%federation%' => $federationTerTitle)));
            $filterData['FM']['entry'][] = array('id' => 'FMjoining_date', 'title' => $this->get('translator')->trans('FM_JOINING_DATE',array('%federation%' => $federationTerTitle)), 'type' => 'date','shortTitle'=>$this->get('translator')->trans('FM_JOINING_DATE', array('%federation%' => $federationTerTitle)));
            $filterData['FM']['entry'][] = array('id' => 'FMleaving_date', 'title' => $this->get('translator')->trans('FM_LEAVING_DATE',array('%federation%' => $federationTerTitle)), 'type' => 'date','shortTitle'=>$this->get('translator')->trans('FM_LEAVING_DATE', array('%federation%' => $federationTerTitle)));
        }   
        return $filterData;
    }

    /**
     * function to get contact options.
     *
     * @return array
     */
    private function contactOptions()
    {
        //Contact Options
        $filterData = array();
        $filterData['CO']['title'] = $this->get('translator')->trans('CM_CONTACT_OPTIONS');
        $filterData['CO']['id'] = 'CO';
        $filterData['CO']['show_filter'] = 1;
        /* Membership setting */
        $comInput = array();
        $comInput[] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_MEMBER'));
        $comInput[] = array('id' => 'any', 'title' => $this->get('translator')->trans('CM_ANY_MEMBER'));
        $filterData['CO']['fixed_options'][0][] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_OPTIONS'));
        $coInput = array();
        $coInput[] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_VALUE'));
        $coInput[] = array('id' => 'single_person', 'title' => $this->get('translator')->trans('CM_SINGLE_PERSON'));
        $coInput[] = array('id' => 'company', 'title' => $this->get('translator')->trans('CM_COMPANY'));
        $filterData['CO']['entry'][] = array('id' => 'contact_type', 'title' => $this->get('translator')->trans('CM_CONTACT_TYPE'), 'type' => 'select', 'input' => $coInput);

        $sponsorInput = array();
        $sponsorInput[] = array('id' => '', 'title' => $this->get('translator')->trans('CM_SELECT_VALUE'));
        $sponsorInput[] = array('id' => 'any', 'title' => $this->get('translator')->trans('ANY_SPONSOR'));
        $sponsorInput[] = array('id' => 'prospect', 'title' => $this->get('translator')->trans('PROSPECTS'));
        $sponsorInput[] = array('id' => 'future_sponsor', 'title' => $this->get('translator')->trans('FUTURE_SPONSORS'));
        $sponsorInput[] = array('id' => 'active_sponsor', 'title' => $this->get('translator')->trans('ACTIVE_SPONSORS'));
        $sponsorInput[] = array('id' => 'former_sponsor', 'title' => $this->get('translator')->trans('FORMER_SPONSORS'));
        $filterData['CO']['entry'][] = array('id' => 'sponsor', 'title' => $this->get('translator')->trans('SIDEBAR_SPONSORS'), 'type' => 'select', 'input' => $sponsorInput);

        return $filterData['CO'];
    }

    /**
     * Function to get saved filters of sponsor module.
     *
     * @Template("ClubadminSponsorBundle:Filter:savedfilter.html.twig")
     *
     * @return array Data array
     */
    public function savedfilterAction()
    {
        return array('clubId' => $this->clubId, 'contactId' => $this->contactId);
    }

    /**
     * Function to propagate data for sponsor saved filters.
     *
     * @return JsonResponse
     */
    public function getSponsorSavedFiltersAction()
    {
        $allSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSavedSidebarFilter($this->contactId, $this->clubId, 'sponsor');

        return new JsonResponse($allSavedFilter);
    }
    
    /**
     * Function to propagate data for sponsor saved filters.
     *
     * @return JsonResponse
     */
    public function getContactSavedFiltersAction()
    {
        $allSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSavedSidebarFilter($this->contactId, $this->clubId, 'general');

        return new JsonResponse($allSavedFilter);
    }

    /**
     * Function to get data of a single filter.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse Data of single filter.
     */
    public function sidebarSingleFilterAction(Request $request)
    {
        $id = $request->get('id');
        $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSingleSavedSidebarFilter($id, $this->contactId, $this->clubId, 'sponsor');

        return new JsonResponse(array('singleSavedFilter' => $singleSavedFilter));
    }

    /**
     * For iterating the filter query result.
     *
     * @param array $filterArray Filter array
     *
     * @return array $filterDetails Result array
     */
    private function iterateFilter($filterArray)
    {
        $filterDetails = array();
        if (is_array($filterArray) && count($filterArray) > 0) {
            $iCount = 0;
            foreach ($filterArray as $filter) {
                //create a array for create tbe sidebar of filter
                $filterDetails[$iCount]['bookmarkClass'] = 'fa-bookmark';
                $filterDetails[$iCount]['bookMarkId'] = $filter['bookmarkid'];
                $filterDetails[$iCount]['draggableClass'] = 'fg-dev-non-draggable';
                $filterDetails[$iCount]['isBroken'] = $filter['isBroken'];
                $filterDetails[$iCount]['itemType'] = 'filter';
                $filterDetails[$iCount]['title'] = $filter['filterName'];
                $filterDetails[$iCount]['menuItemId'] = 'filter_li_'.$filter['filterId'];
                $filterDetails[$iCount]['id'] = $filter['filterId'];
                $filterDetails[$iCount]['filterData'] = $filter['filterData'];
                $filterDetails[$iCount]['filterUsed'] = $filter['filterUsed'];
                $iCount++;
            }
        }

        return $filterDetails;
    }

    /**
     * Function to get the count of sponsor filter.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return Nill
     */
    public function sidebarSponsorFilterCountAction(Request $request)
    {
        try {
            $id = $request->get('filterId', '0');
            //call a service for collect all relevant data related to the club
            $club = $this->get('club');
            $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->getSingleSavedSidebarFilter($id, $this->contactId, $this->clubId, 'sponsor');
            $filterdata = $singleSavedFilter[0]['filterData'];
            $contactlistClass = new Contactlist($this->container, $this->contactId, $club, 'sponsor');
            $contactlistClass->setCount();
            $contactlistClass->setFrom();
            $contactlistClass->setCondition();

            $filterarr = json_decode($filterdata, true);
            $filter = array_shift($filterarr);
            $filterObj = new Contactfilter($this->container, $contactlistClass, $filter, $club);
            $sWhere .= ' '.$filterObj->generateFilter();
            $contactlistClass->addCondition($sWhere);

            //call query for collect the data
            $totallistquery = $contactlistClass->getResult();

            $totalcontactlistDatas = $this->em->getRepository('CommonUtilityBundle:FgCmMembership')->getContactList($totallistquery);

            return new Response($totalcontactlistDatas[0]['count']);
        } catch (\Doctrine\DBAL\DBALException $e) {
            $singleSavedFilter = $this->em->getRepository('CommonUtilityBundle:FgFilter')->updateBorkenFilter($id, '1');

            return new Response(-1);
        }
    }

    /**
     * Function to iterate membership categories in a club *.
     *
     * @param Array $rowMemberships memberships
     *
     * @return array $resultArray
     */
    public function iterateMemberships($rowMemberships, $isLogo = false)
    {
        $federationId = $this->clubType == 'federation' ? $this->clubId : $this->federationId;
        $corr = $this->get('contact')->get('corrLang');
        $memberships = array();
        $title = '';
        foreach ($rowMemberships as $id => $rowmembership) {
            if ($isLogo == true) {
                //fed membership
                if ($rowmembership['clubId'] == $federationId) {
                    $title = $rowmembership['allLanguages'][$corr]['titleLang'] != '' ? $rowmembership['allLanguages'][$corr]['titleLang'] : $rowmembership['membershipName']; //. ' <img class="fa-envelope-o" src=' . $logoPath . ' />'
                    $memberships[] = array('id' => $id, 'title' => $title, 'itemType' => 'fed_membership', 'bookMarkId' => $rowmembership['bookmarkId'], 'draggable' => 1);
                }
            } else {
                //club membership
                if ($rowmembership['clubId'] == $this->clubId) {
                    $title = $rowmembership['allLanguages'][$corr]['titleLang'] != '' ? $rowmembership['allLanguages'][$corr]['titleLang'] : $rowmembership['membershipName'];
                    $memberships[] = array('id' => $id, 'title' => $title, 'itemType' => 'membership', 'bookMarkId' => $rowmembership['bookmarkId'], 'draggable' => 1);
                }
            }
        }

        return $memberships;
    }
}
