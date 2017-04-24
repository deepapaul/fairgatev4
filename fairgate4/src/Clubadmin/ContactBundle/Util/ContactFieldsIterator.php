<?php

namespace Clubadmin\ContactBundle\Util;

/**
 * Used to save contact field details
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 *
 * @version Release: <v4>
 */
class ContactFieldsIterator
{

    private $tableValues = array();
    private $attributes = array();
    private $categoryQuerySet = array();
    private $categoryi18nQuerySet = array();
    private $atttributeQuerySet = array();
    private $atttributei18nQuerySet = array();
    private $atttributeRequiredQuerySet = array();
    private $container;
    private $conn;
    private $clubId;
    private $em;
    private $systemCategoryPersonal;
    private $systemCategoryCompany;
    private $systmPersonaBothlFields;
    private $clubDefaultLang;
    private $clubLanguages;
    private $random;

    /**
     * Constructor for initial setting.
     *
     * @param object $container  Container Object
     * @param array  $attributes attribute array
     * @param int    $random     random number
     */
    public function __construct($container, $attributes, $random)
    {
        $this->attributes = $attributes;
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->clubId = $this->container->get('club')->get('id');
        $this->systemCategoryPersonal = $this->container->getParameter('system_category_personal');
        $this->systemCategoryCompany = $this->container->getParameter('system_category_company');
        $this->systmPersonaBothlFields = $this->container->getParameter('system_personal_both');
        $this->clubDefaultLang = $this->container->get('club')->get('club_default_lang');
        $this->clubLanguages = $this->container->get('club')->get('club_languages');
        $this->em = $this->container->get('doctrine')->getManager();
        $this->random = $random;
    }

    /**
     * Function to iterate contact field categories
     *
     * @return void
     */
    private function iterateCategory()
    {
        foreach ($this->attributes as $catId => $catDetails) {
            $categoryTitle = '';
            $categoryType = is_numeric($catId) ? 'old' : 'new'; //new/old
            $categorySort = (isset($catDetails['sort']) && $catDetails['sort'] != '') ? "'" . (int) $catDetails['sort'] . "'" : 'NULL';
            //Update category details
            $isCatDeleted = (isset($catDetails['isDeleted']) && $catDetails['isDeleted'] != '') ? "'" . (int) $catDetails['isDeleted'] . "'" : 'NULL';
            if ($categoryType == 'new' && $isCatDeleted == "'1'") {
                continue;
            }
            $catTitleArray = isset($catDetails['title']) ? $catDetails['title'] : array();
            foreach ($catTitleArray as $lang => $title) {
                $title = $this->conn->quote(trim($title));
                if ($lang == $this->clubDefaultLang) {
                    $categoryTitle = $title;
                }
                $lang = $this->conn->quote(trim($lang));
                $title = ($title != '' ? $title : 'NULL');
                $this->categoryi18nQuerySet[] = "('', '$catId', $lang, $title, '$this->random')";
            }
            $categoryTitle = ($categoryTitle != '' ? $categoryTitle : 'NULL');

            $this->categoryQuerySet[] = "('', '$catId', '$categoryType', $categoryTitle, $categorySort, $isCatDeleted, '{$this->clubId}', '$this->random')";
            $fieldArray = isset($catDetails['fields']) ? $catDetails['fields'] : array();
            // function to iterate contact fields
            $this->iterateFields($fieldArray, $catId);
        }
        // function to set table values to be saved
        $this->setTableValues();
    }

    /**
     * Function to iterate contact fields
     * 
     * @param  array  $fieldArray contact filed array
     * @param  int    $catId      contact field category id 
     * 
     * @return void
     */
    private function iterateFields($fieldArray, $catId)
    {
        foreach ($fieldArray as $fieldId => $fieldDetails) {
            $isFieldDeleted = isset($fieldDetails['isDeleted']) ? "'" . $fieldDetails['isDeleted'] . "'" : 'NULL';
            $fieldSort = (isset($fieldDetails['sort']) && $fieldDetails['sort'] != '') ? $fieldDetails['sort'] : 'NULL';
            $fieldType = is_numeric($fieldId) ? 'old' : 'new';
            $fieldCatId = (isset($fieldDetails['categoryId']) && $fieldDetails['categoryId'] != '') ? $fieldDetails['categoryId'] : 'NULL';
            $usedFor = (isset($fieldDetails['usedFor']) && $fieldDetails['usedFor'] != '') ? "'" . $fieldDetails['usedFor'] . "'" : 'NULL';
            $fieldInputType = (isset($fieldDetails['fieldType']) && $fieldDetails['fieldType'] != '') ? "'" . $fieldDetails['fieldType'] . "'" : 'NULL';
            $fieldValues = (isset($fieldDetails['fieldValue']) && $fieldDetails['fieldValue'] != '') ? "'" . str_replace(',', ';', $fieldDetails['fieldValue']) . "'" : 'NULL';
            $value = (isset($fieldDetails['fieldValue']) && $fieldDetails['fieldValue'] != '') ? $fieldDetails['fieldValue'] : '';
            $availableFor = (isset($fieldDetails['availableFor']) && $fieldDetails['availableFor'] != '') ? $fieldDetails['availableFor'] : '';
            if (($fieldType == 'new' && $isFieldDeleted == "'1'") || (in_array($fieldInputType, $fieldInputType) && $value == '')) {
                continue;
            }
            $fieldName = '';
            $shortFieldName = '';
            // function to set whether the filed is required
            $requiredType = $this->setRequiredAttribute($fieldDetails, $fieldId);
            //set contact fields availability
            list($isCompany, $isPersonal, $fieldCatId) = $this->setFieldAvailability($availableFor, $fieldCatId, $catId, $fieldId);
            $isSingleEdit = isset($fieldDetails['isMultiEdit']) ? ($fieldDetails['isMultiEdit'] == '1' ? "'0'" : "'1'") : 'NULL';
            /* set availbaility for contact and team */
            list($availabilityContact, $availabilityGroupAdmin, $isConfirmContact, $isConfirmTeamAdmin) = $this->setContactAndTeamAvailability($fieldDetails);
            /* set settings for privacy  and fed/club */
            list($isSetPrivacyItself, $privacyContact, $availabilitySubFed, $subFedRequired, $availabilityClub, $clubRequired) = $this->setPrivacyAndClubSettings($fieldDetails);
            $isFieldActive = isset($fieldDetails['isActive']) ? "'" . $fieldDetails['isActive'] . "'" : 'NULL';
            /* set tikle od contact field and translations */
            $fieldTitleArray = isset($fieldDetails['title']) ? $fieldDetails['title'] : array();
            array_walk($fieldTitleArray, 'trim_value');
            $fieldShortNameArray = isset($fieldDetails['shortname']) ? $fieldDetails['shortname'] : array();
            array_walk($fieldShortNameArray, 'trim_value');
            $defautFieldNameEntries = isset($fieldTitleArray[$this->clubDefaultLang]) ? $fieldTitleArray[$this->clubDefaultLang] : false;
            if ($fieldType == 'new') {
                $fieldTitleDetails = $this->setNewContactFieldTitle($fieldName, $shortFieldName, $fieldInputType, $defautFieldNameEntries, $fieldTitleArray, $fieldShortNameArray);
                if (false === $fieldTitleDetails) {
                    continue;
                } else {
                    list($fieldName, $shortFieldName, $fieldTitleArray, $fieldShortNameArray) = $fieldTitleDetails;
                }
            }
            list($fieldName, $shortFieldName) = $this->iterateFieldI18nValues($fieldTitleArray, $fieldShortNameArray, $fieldName, $shortFieldName, $fieldId);

            $this->atttributeQuerySet[] = "('', '$fieldId','$catId','$fieldType', $fieldCatId, $fieldName, $shortFieldName ,$fieldInputType, "
                . "$isCompany, $isPersonal, $fieldValues, $isSingleEdit, $subFedRequired,$clubRequired,$usedFor, $availabilityContact,"
                . "$isSetPrivacyItself,$privacyContact,$availabilityGroupAdmin,$isConfirmContact,$requiredType, $isConfirmTeamAdmin,"
                . "$fieldSort, $isFieldDeleted,$isFieldActive, '{$this->clubId}',$availabilitySubFed,$availabilityClub, '$this->random')";
            //deactivate the fields from the contact application form
            //checking needed for deleting a contact field from contact application form while swithing  availability  single person to company and fro
            $this->fieldDeactivateAndRemove($isFieldActive, $fieldId, $availableFor, $fieldType);
        }
    }

    /**
     * Function to set fields availability
     * 
     * @param   string  $availableFor contact filed array
     * @param   int     $fieldCatId   contact field id 
     * @param   int     $catId        field category id 
     * @param   int     $fieldId      field id
     * 
     * @return array    field visibility
     */
    private function setFieldAvailability($availableFor, $fieldCatId, $catId, $fieldId)
    {
        $isCompany = 'NULL';
        $isPersonal = 'NULL';
        if ($availableFor != '') {
            switch ($availableFor) {
                case 'singleperson':
                    $isCompany = "'0'";
                    $isPersonal = "'1'";
                    break;
                case 'company':
                    $isCompany = "'1'";
                    $isPersonal = "'0'";
                    break;
                case 'both':
                    $isCompany = "'1'";
                    $isPersonal = "'1'";
                    break;
            }
        }
        if ($fieldCatId != 'NULL') {
            if ($fieldCatId != $catId) {
                list($isCompany, $isPersonal) = $this->getSystemFieldAvailability($fieldCatId, $fieldId, $isCompany, $isPersonal);
            }
            $fieldCatId = "'" . $fieldCatId . "'";
        } else {
            list($isCompany, $isPersonal) = $this->getSystemFieldAvailability($catId, $fieldId, $isCompany, $isPersonal);
        }

        return array($isCompany, $isPersonal, $fieldCatId);
    }

    /**
     * function to set system field availability
     * 
     * @param int      $fieldCatId  field category id
     * @param int      $fieldId     field  id
     * @param string   $isCompany   field  id
     * @param string   $isPersonal  field  id
     * 
     * return array company/personal availability
     */
    private function getSystemFieldAvailability($fieldCatId, $fieldId, $isCompany, $isPersonal)
    {
        if ($fieldCatId == $this->systemCategoryPersonal) {
            if (in_array($fieldId, $this->systmPersonaBothlFields)) {
                $isCompany = "'1'";
                $isPersonal = "'1'";
            } else {
                $isCompany = "'0'";
                $isPersonal = "'1'";
            }
        } elseif ($fieldCatId == $this->systemCategoryCompany) {
            $isCompany = "'1'";
            $isPersonal = "'0'";
        }

        return array($isCompany, $isPersonal);
    }

    /**
     * Function to set contact and field availability
     * 
     * @param  array  $fieldDetails 
     * 
     * @return array  contact and team avilability
     */
    private function setContactAndTeamAvailability($fieldDetails)
    {
        $availabilityContact = isset($fieldDetails['availabilityContact']) ? "'" . $fieldDetails['availabilityContact'] . "'" : 'NULL';
        $availabilityGroupAdmin = isset($fieldDetails['availabilityGroupadmin']) ? "'" . $fieldDetails['availabilityGroupadmin'] . "'" : 'NULL';
        if ($availabilityContact == 'visible' || $availabilityContact == 'not_available') {
            $isConfirmContact = "'0'";
        } else {
            $isConfirmContact = isset($fieldDetails['isConfirmContact']) ? "'" . $fieldDetails['isConfirmContact'] . "'" : 'NULL';
        }
        /* set availbaility for group admin */
        if ($availabilityGroupAdmin == 'visible' || $availabilityGroupAdmin == 'not_available') {
            $isConfirmTeamAdmin = "'0'";
        } else {
            $isConfirmTeamAdmin = isset($fieldDetails['isConfirmTeamadmin']) ? "'" . $fieldDetails['isConfirmTeamadmin'] . "'" : 'NULL';
        }

        return array($availabilityContact, $availabilityGroupAdmin, $isConfirmContact, $isConfirmTeamAdmin);
    }

    /**
     * Function to set contact and field availability
     * 
     * @param  array  $fieldDetails contact field details
     * 
     * @return array  privacy and club/fed settings
     */
    private function setPrivacyAndClubSettings($fieldDetails)
    {
        /* set profile options */
        $isSetPrivacyItself = isset($fieldDetails['isSetPrivacyItself']) ? "'" . $fieldDetails['isSetPrivacyItself'] . "'" : 'NULL';
        $privacyContact = isset($fieldDetails['privacyContact']) ? "'" . $fieldDetails['privacyContact'] . "'" : 'NULL';

        /* set federation settings */
        $availabilitySubFed = isset($fieldDetails['availabilitySubfed']) ? "'" . $fieldDetails['availabilitySubfed'] . "'" : 'NULL';
        $subFedRequired = (isset($fieldDetails['isSubFedRequired'])) ? $fieldDetails['isSubFedRequired'] : 'NULL';
        /* --------------------------------------------------------------------------------------- */

        /* set club settings */
        $availabilityClub = isset($fieldDetails['availabilityClub']) ? "'" . $fieldDetails['availabilityClub'] . "'" : 'NULL';
        $clubRequired = (isset($fieldDetails['isClubRequired'])) ? $fieldDetails['isClubRequired'] : 'NULL';
        /* --------------------------------------------------------------------------------------- */
        return array($isSetPrivacyItself, $privacyContact, $availabilitySubFed, $subFedRequired, $availabilityClub, $clubRequired);
    }

    /**
     * Function to set contact field title
     * 
     * @param string $fieldName              filed name
     * @param string $shortFieldName         field short name
     * @param string $fieldInputType         input field type
     * @param string $defautFieldNameEntries default field name
     * @param array $fieldTitleArray         field title array
     * @param array $fieldShortNameArray     field short title array
     * 
     * @return array/boolean
     */
    private function setNewContactFieldTitle($fieldName, $shortFieldName, $fieldInputType, $defautFieldNameEntries, $fieldTitleArray, $fieldShortNameArray)
    {
        if (($fieldInputType == "'select'" || $fieldInputType == "'checkbox'") && $fieldValues == 'NULL') {
            return false;
        }
        // Field title is mandatory. If empty, will not be inserted
        if (!$defautFieldNameEntries) {
            return false;
        } else {
            $fieldName = trim($fieldTitleArray[$this->clubDefaultLang]);
            if ($fieldName == '') {
                return false;
            }
            if (!isset($fieldShortNameArray[$this->clubDefaultLang])) {
                $fieldShortNameArray[$this->clubDefaultLang] = $fieldName;
                $shortFieldName = $fieldName;
            } else {
                $shortFieldName = trim($fieldShortNameArray[$this->clubDefaultLang]);
                if ($shortFieldName == '') {
                    $shortFieldName = $fieldName;
                }
            }
        }

        return array($fieldName, $shortFieldName, $fieldTitleArray, $fieldShortNameArray);
    }

    /**
     * Function to set required attribute of fields
     * 
     * @param  array   $fieldDetails         contact field details
     * @param  int     $fieldId              contact field id
     * 
     * @return string  field required type 
     */
    private function setRequiredAttribute($fieldDetails, $fieldId)
    {
        $requiredType = 'NULL';
        $requiredMembership = array();
        $requiredTypes = array('not_required', 'all_contacts', 'all_club_members', 'all_fed_members', 'selected_members');
        if (isset($fieldDetails['required'])) {
            $requiredType = $fieldDetails['required'][0];
            if (!in_array($requiredType, $requiredTypes)) {
                $requiredType = 'selected_members';
                $requiredMembership = $fieldDetails['required'];
            }
            $requiredType = "'" . $requiredType . "'";
        } elseif (array_key_exists('required', $fieldDetails) && is_null($fieldDetails['required'])) {
            $requiredType = "'not_required'";
        }
        foreach ($requiredMembership as $membershipId) {
            $this->atttributeRequiredQuerySet[] = "('', '$fieldId', '{$this->clubId}', '{$membershipId}', '$this->random')";
        }

        return $requiredType;
    }

    /**
     * Function to iterate field i18 title values
     * 
     * @param array   $fieldTitleArray       contact filed title array
     * @param array   $fieldShortNameArray   contact field short name array 
     * @param string  $fieldName             contact field name
     * @param int     $fieldId               contact field id
     * 
     * @return array field name and short name
     */
    private function iterateFieldI18nValues($fieldTitleArray, $fieldShortNameArray, $fieldName, $shortFieldName, $fieldId)
    {
        foreach ($this->clubLanguages as $lang) {
            if (isset($fieldTitleArray[$lang]) && isset($fieldShortNameArray[$lang])) {
                $fieldNameLang = $this->conn->quote($fieldTitleArray[$lang]);
                $fieldShortNameLang = $this->conn->quote(trim($fieldShortNameArray[$lang]));
                if ($lang == $this->clubDefaultLang) {
                    $fieldName = $fieldNameLang;
                    $shortFieldName = $fieldShortNameLang;
                }
                $fieldNameLang = ($fieldNameLang != '' ? $fieldNameLang : 'NULL');
                $fieldShortNameLang = ($fieldShortNameLang != '' ? $fieldShortNameLang : 'NULL');
                $this->atttributei18nQuerySet[] = "('', '$fieldId', '$lang', $fieldNameLang, $fieldShortNameLang, '$this->random')";
            } elseif (array_key_exists($lang, $fieldTitleArray)) {
                $fieldNameLang = $this->conn->quote(trim($fieldTitleArray[$lang]));
                if ($lang == $this->clubDefaultLang) {
                    $fieldName = $fieldNameLang;
                }
                $fieldNameLang = ($fieldNameLang != '' ? $fieldNameLang : 'NULL');
                $this->atttributei18nQuerySet[] = "('','$fieldId', '$lang', $fieldNameLang, NULL, '$this->random')";
            } elseif (array_key_exists($lang, $fieldShortNameArray)) {
                $fieldShortNameLang = $this->conn->quote(trim($fieldShortNameArray[$lang]));
                if ($lang == $this->clubDefaultLang) {
                    $shortFieldName = $fieldShortNameLang;
                }
                $fieldShortNameLang = ($fieldShortNameLang != '' ? $fieldShortNameLang : 'NULL');
                $this->atttributei18nQuerySet[] = "('','$fieldId', '$lang', NULL, $fieldShortNameLang, '$this->random')";
            }
        }
        $fieldName = ($fieldName != '' ? $fieldName : 'NULL');
        $shortFieldName = ($shortFieldName != '' ? $shortFieldName : 'NULL');

        return array($fieldName, $shortFieldName);
    }

    /**
     * Function to deactivate and Remove contact field field 
     * 
     * @param string    $isFieldActive      check whether filed is active
     * @param int       $fieldId            contact field id
     * @param string    $availableFor       filed availability
     * @param string    $fieldType          contact field type
     * 
     * return void
     */
    private function fieldDeactivateAndRemove($isFieldActive, $fieldId, $availableFor, $fieldType)
    {
        //deactivate the fields from the contact application form
        if ($isFieldActive == "'0'") {
            $this->em->getRepository("CommonUtilityBundle:FgCmsPageContentElementFormFields")->deactivateContactFields($fieldId);
        }
        //checking needed for deleting a contact field from contact application form while swithing  availability  single person to company and fro
        if ($availableFor == 'singleperson' && $fieldType == 'old') {
            $this->em->getRepository("CommonUtilityBundle:FgCmsPageContentElementFormFields")->removeContactFields('company', $fieldId);
        } else if ($availableFor == 'company' && $fieldType == 'old') {
            $this->em->getRepository("CommonUtilityBundle:FgCmsPageContentElementFormFields")->removeContactFields('singleperson', $fieldId);
        }
    }

    /**
     * Function to set table values 
     * 
     * @return void
     */
    private function setTableValues()
    {
        $this->tableValues["fg_temp_attributeset"] = $this->categoryQuerySet;
        $this->tableValues["fg_temp_attributeset_i18n"] = $this->categoryi18nQuerySet;
        $this->tableValues["fg_temp_attribute"] = $this->atttributeQuerySet;
        $this->tableValues["fg_temp_attribute_i18n"] = $this->atttributei18nQuerySet;
        $this->tableValues["fg_temp_attribute_required"] = $this->atttributeRequiredQuerySet;
    }

    /**
     * Function to get table values to save contact fileds
     * 
     * @return array table values
     */
    public function getTableValues()
    {
        $this->iterateCategory();

        return $this->tableValues;
    }
}
