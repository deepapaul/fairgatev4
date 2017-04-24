<?php

/**
 * GotCourtsContactList
 *
 * This class is used to get contact details specific for GotCourts Api service
 *
 * @package    FairgateApiBundle
 * @subpackage Util
 * @author     Pits Solutions
 * @version    v0
 */
namespace FairgateApiBundle\Util;

use FairgateApiBundle\Util\GotCourtsApiDetails;
use Symfony\Component\Intl\Intl;

/**
 * This class is used to handle the Fairgate api details
 */
class GotCourtsContactList
{

    /**
     * @var object Container variable
     */
    public $container;

    /**
     * @var object the request object
     */
    private $request;

    /**
     * @var object entity manager variable
     */
    private $em;

    /**
     * @var String club id
     */
    private $clubId;

    /**
     * @var String club type
     */
    private $clubType;

    /**
     * @var String where condition
     */
    private $where = '';

    /**
     * @var String where condition for contact search
     */
    private $searchWhere = '';

    /**
     * @var String club default language 
     */
    private $clubDefLang = '';
    
    /**
     * Constructor of FgPageContainerDetails class.
     *
     * @param ContainerInterface    $container
     * @param int                   $clubId        Club id
     * @param string                $lang          Language from api request header
     */
    public function __construct($container, $clubId, $lang)
    {
        $this->container = $container;
        $this->clubId = $clubId;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->conn = $this->container->get('database_connection');
        $this->clubType = $this->getClubType($this->clubId);
        $this->clubDefLang = $lang;
    }

    /**
     * This function is used to set contact fields.
     * 
     * @return array Contact fields
     */
    private function getContactFields()
    {
        $fields = array();
        $fields['lastUpdate'] = '`c`.`last_updated`';
        $fields['playerCategory'] = '1';
        $fields['contactidhash'] = '1';
        $fields['firstName'] = '`ms`.`' . $this->container->getParameter('system_field_firstname') . '`';
        $fields['lastName'] = '`ms`.`' . $this->container->getParameter('system_field_lastname') . '`';
        $fields['gender'] = '`ms`.`' . $this->container->getParameter('system_field_gender') . '`';
        $fields['dob'] = '`ms`.`' . $this->container->getParameter('system_field_dob') . '`';
        $fields['email'] = '`ms`.`' . $this->container->getParameter('system_field_primaryemail') . '`';
        $fields['address1'] = 'IFNULL(`ms`.`' . $this->container->getParameter('system_field_corres_strasse') . '`, "")';
        $fields['address2'] = 'IFNULL(`ms`.`' . $this->container->getParameter('system_field_corres_postfach') . '`, "")';
        $fields['land'] = 'IFNULL(`ms`.`' . $this->container->getParameter('system_field_corres_land') . '`, "")';
        $fields['country'] = '1';
        $fields['town'] = 'IFNULL(`ms`.`' . $this->container->getParameter('system_field_corres_ort') . '`, "")';
        $fields['zipcode'] = 'IFNULL(`ms`.`' . $this->container->getParameter('system_field_corres_plz') . '`, "")';
        $fields['mobilePhone'] = 'IFNULL(`ms`.`' . $this->container->getParameter('system_field_mobile1') . '`, "")';

        $fields['fedMembership'] = "CASE WHEN (fmi18n.title_lang IS NULL OR fmi18n.title_lang = '') THEN fm.title ELSE fmi18n.title_lang END";
        $fields['clubMembership'] = "CASE WHEN (cmi18n.title_lang IS NULL OR cmi18n.title_lang = '') THEN cm.title ELSE cmi18n.title_lang END";
        $fields['id'] = '`c`.`id`';

        return $fields;
    }

    /**
     * This method is used to format contact fields.
     * 
     * @param  array $fields
     * 
     * @return array
     */
    private function selectFileds($fields)
    {
        $result = array();
        foreach ($fields as $key => $value) {
            $result[] = "$value AS $key";
        }
        return $result;
    }

    /**
     * This method is to fetch contact details from contact id
     * 
     * @param  int   $contactId Contact Id
     * 
     * @return array            Contact details
     */
    public function getContactDetails($contactId)
    {
        $fields = $this->getContactFields();
        $selectFields = implode(', ', $this->selectFileds($fields));
        $this->setWhereCondition();
        $sql = "SELECT $selectFields FROM `master_system` `ms`"
            . " INNER JOIN `fg_cm_contact` `c` ON `c`.`fed_contact_id` = `ms`.`fed_contact_id`"
            . " LEFT JOIN `fg_cm_membership` `fm` ON `fm`.`id` = `c`.`fed_membership_cat_id`"
            . " LEFT JOIN `fg_cm_membership_i18n` `fmi18n` ON `fmi18n`.`id` = `fm`.`id` AND `fmi18n`.`lang` = '$this->clubDefLang'"
            . " LEFT JOIN `fg_cm_membership` `cm` ON `cm`.`id` = `c`.`club_membership_cat_id`"
            . " LEFT JOIN `fg_cm_membership_i18n` `cmi18n` ON `cmi18n`.`id` = `cm`.`id` AND `cmi18n`.`lang` = '$this->clubDefLang'"
            . " WHERE " . $this->where
            . " AND `c`.`id` = $contactId";
        try {
            $contactDetails = $this->conn->fetchAll($sql);

            return $this->formatData($contactDetails);
        } catch (\Exception $e) {

            return;
        }
    }

    /**
     * This method is used to format contact details
     * 
     * @param array $data contact details to be formatted
     * 
     * @return array
     */
    private function formatData($data)
    {
        $countryList = Intl::getRegionBundle()->getCountryNames($this->clubDefLang);
        $gcApiDet = new GotCourtsApiDetails($this->container);
        $result = array();
        foreach ($data as $key => $value) {
            $result[$key] = $value;
            $result[$key]['contactidhash'] = $gcApiDet->anonymizeData($value['id']);
            $result[$key]['playerCategory'] = array();
            if ($value['fedMembership']) {
                $result[$key]['playerCategory'][]['categoryName'] = $value['fedMembership'];
            }
            if ($value['clubMembership']) {
                $result[$key]['playerCategory'][]['categoryName'] = $value['clubMembership'];
            }
            $dateObj = date_create_from_format('Y-m-d H:i:s', $value['lastUpdate']);
            $result[$key]['lastUpdate'] = date_format($dateObj, \DateTime::RFC2822);
            $dobDateObj = date_create_from_format('Y-m-d', $value['dob']);
            $result[$key]['dob'] = ($dobDateObj && date_format($dobDateObj, 'Y-m-d') === $value['dob']) ? date_format($dobDateObj, 'Y') : '';
            $result[$key]['country'] = $result[$key]['land'] ? $countryList[$result[$key]['land']] : '';
            unset($result[$key]['id']);
            unset($result[$key]['fedMembership']);
            unset($result[$key]['clubMembership']);
            unset($result[$key]['land']);
        }

        return $result;
    }

    /**
     * This method is used to set where condition to get active contacts.
     * 
     * @return void
     */
    private function setWhereCondition()
    {
        if ($this->clubType == 'federation' || $this->clubType == 'sub_federation') {
            $this->where = " `c`.is_deleted = 0 AND `c`.is_permanent_delete=0 AND `c`.club_id = '{$this->clubId}' AND (`c`.main_club_id = '{$this->clubId}' OR `c`.fed_membership_cat_id IS NOT NULL AND `c`.fed_membership_cat_id != '') AND (`c`.is_fed_membership_confirmed='0' OR (`c`.is_fed_membership_confirmed='1' AND `c`.old_fed_membership_id IS NOT NULL))";
        } else {
            $this->where = "( (`c`.is_deleted = 0 AND `c`.is_permanent_delete=0)) AND `c`.club_id = '{$this->clubId}'";
        }
        $this->where .= ' AND `c`.is_draft=0';
    }
    
    /**
     * This method is used to search contacts
     *
     * @param  array $searchVal Array of search values and key is field name.
     * 
     * @return array            Array of contact details
     */
    public function searchContact($searchVal)
    {
        $fields = $this->getContactFields();
        $selectFields = implode(', ', $this->selectFileds($fields));
        $this->setWhereCondition();
        $this->setSearchWhere($searchVal);
        $conn = $this->container->get('database_connection');
         $sql = "SELECT $selectFields FROM `master_system` `ms`"
            . " INNER JOIN `fg_cm_contact` `c` ON `c`.`fed_contact_id` = `ms`.`fed_contact_id`"
            . " LEFT JOIN `fg_cm_membership` `fm` ON `fm`.`id` = `c`.`fed_membership_cat_id`"
            . " LEFT JOIN `fg_cm_membership_i18n` `fmi18n` ON `fmi18n`.`id` = `fm`.`id` AND `fmi18n`.`lang` = '$this->clubDefLang'"
            . " LEFT JOIN `fg_cm_membership` `cm` ON `cm`.`id` = `c`.`club_membership_cat_id`"
            . " LEFT JOIN `fg_cm_membership_i18n` `cmi18n` ON `cmi18n`.`id` = `cm`.`id` AND `cmi18n`.`lang` = '$this->clubDefLang'"
            . " WHERE " . $this->where;
        if ($this->searchWhere) {
            $sql .= " AND " . $this->searchWhere;
        }

        try {
            $contactDetails = $conn->fetchAll($sql);
            return $this->formatData($contactDetails);
        } catch (\Exception $e) {

            return;
        }
    }

    /**
     * This methd is used to find whether the contact is admin or not.
     * It will check the contact has fed/club admin privilege.
     * 
     * @param int $clubId       Club id
     * @param int $contactId    Contact id
     * @param int $fedContactId Federation contact id
     * 
     * @return boolean
     */
    public function verifyMainAdmin($clubId, $contactId, $fedContactId)
    {
        $clubAdmins = $this->em->getRepository('CommonUtilityBundle:sfGuardGroup')->getClubAdmins($clubId, $this->conn);
        $clubObj = $this->em->getRepository('CommonUtilityBundle:fgClub')->find($clubId);
        $fedId = $clubObj->getFederationId();
        $fedAdmins = $this->em->getRepository('CommonUtilityBundle:sfGuardGroup')->getFedAdmins($fedId, $this->conn);

        if (in_array($contactId, $clubAdmins) || in_array($fedContactId, $fedAdmins)) {
            return true;
        }
        return false;
    }

    /**
     * This method is used to find the contact_id from email and last name
     * 
     * @param String $lastName Last name
     * @param String $email    Email
     * 
     * @return int|boolean
     */
    public function getContactFromLastNameAndEmail($lastName, $email)
    {
        $fields = $this->getContactFields();
        $this->setWhereCondition();
        $conn = $this->container->get('database_connection');
        $sql = "SELECT `c`.`id` AS contactId FROM `master_system` `ms`"
            . " INNER JOIN `fg_cm_contact` `c` ON `c`.`fed_contact_id` = `ms`.`fed_contact_id`"
            . " WHERE " . $this->where
            . " AND {$fields['lastName']} ='$lastName' "
            . " AND {$fields['email']} ='$email' ";
        $contactDetails = $conn->fetchAll($sql);

        return $contactDetails[0] ? $contactDetails[0]['contactId'] : false;
    }

    /**
     * This method is used to build where condition for search 
     * 
     * @param  array $searchVal Array of search values
     * 
     * @return string           Where condition for search.
     */
    private function setSearchWhere($searchVal)
    {
        $this->searchWhere = '';
        $contactFields = $this->getContactFields();
        if ($searchVal['lastName']) {
            $this->searchWhere = "( {$contactFields['firstName']} LIKE '%{$searchVal['firstName']}%' AND ";
            $this->searchWhere .= "{$contactFields['lastName']} LIKE '%{$searchVal['lastName']}%') ";
        }
        if ($searchVal['lastName'] && $searchVal['email']) {
            $this->searchWhere .= " OR ";
        }
        if($searchVal['email']){
            $this->searchWhere .= "{$contactFields['email']} = '{$searchVal['email']}'";
        }
        $this->searchWhere = $this->searchWhere ? "( {$this->searchWhere} )" : '';
    }

    /**
     * This method is used to get club type
     * 
     * @param  int  $clubId  Club id
     * 
     * @return string   
     */
    private function getClubType($clubId)
    {
        $clubObj = $this->em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);

        return $clubObj->getClubType();
    }
}
