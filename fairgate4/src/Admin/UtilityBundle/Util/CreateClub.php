<?php

namespace Admin\UtilityBundle\Util;

use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgLogHandler;
use Common\UtilityBundle\Util\FgFedMemberships;

/**
 * This class is used to handling club registration
 *
 * @author  pitsolutions.ch <pit@solutions.com>
 * @version Release: <v4>
 */
class CreateClub
{

    /**
     * $em
     * @var object entitymanager object
     */
    private $em;

    /**
     * $em
     * @var object Connection object
     */
    private $conn;

    /**
     * $em
     * @var object Connection object
     */
    private $clubData;

    /**
     * Constructor for initial setting
     *
     * @param type $container   container
     */
    public function __construct($container)
    {

        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->conn = $this->container->get('database_connection');
        $this->adminEm = $this->container->get("fg.admin.connection")->getAdminEntityManager();
        $this->adminConn = $this->adminEm->getConnection();
    }

    /**
     * Constructor for initial setting
     *
     * @param type $data  data
     * 
     * @return int clubId
     */
    public function save($data)
    {

        $this->setClubData($data);

        $this->adminConn->beginTransaction();

        try {

            $addressId = $this->saveClubAddress();
            $clubId = $this->insertClubDetails($addressId);
            $this->insertClubDetailsInExistingDb($clubId, $addressId);
            $this->clubData['clubId'] = $clubId;

            $this->saveLanguageSettings();
            $this->saveClubAttribute();
            $this->saveClubSettings();
            $this->createMasterTable();
            // $this->addMembership(); 

            $this->adminConn->commit();
        } catch (Exception $e) {
            // Rollback the failed transaction attempt
            $this->adminConn->rollback();
            throw $e;
        }

        return $clubId;
    }

    /**
     * This method is used to save club address details
     * 
     * @return int
     */
    private function saveClubAddress()
    {
        $query1 = 'INSERT INTO `fg_club_address` (`company`, `street`, `pobox`, `city`, `zipcode`, `state`, `country`, `language`, `created_at`, `updated_at`, `co`)
                VALUES (:company, :street, :pobox, :city, :zipcode, :state, :country, :language, :createdAt, :updatedAt, NULL)';

        $this->adminConn->executeQuery($query1, array('company' => 'company_test', 'street' => '', 'pobox' => '', 'city' => '', 'zipcode' => '', 'state' => '', 'country' => ''
            , 'language' => 'de', 'createdAt' => '', 'updatedAt' => ''));

        return $this->adminConn->lastInsertId();
    }
    /*
     * This method is used to save club address details
     * 
     */

    private function setClubData($data)
    {
        $this->clubData = $data;
    }

    /**
     * This method is used to save club details in admin Db
     * 
     * @param type $addressId Id of club address table
     * 
     * @return type
     */
    private function insertClubDetails($addressId)
    {
        $query2 = 'INSERT INTO `fg_club` (`parent_club_id`, `federation_id`, `sub_federation_id`, `is_federation`, `is_sub_federation`, `club_type`, 
                                        `subfed_level`, `title`, `url_identifier`, `website`, `year`, `correspondence_id`, `billing_id`, `is_active`,
                                        `responsible_contact_id`, `assignment_country`, `assignment_state`, `assignment_activity`,
                                        `assignment_subactivity`, `has_subfederation`, `created_at`, `club_creation_process`, `registration_token`,
                                        `hear_about_fairgate`, `number_of_contacts`)

                                VALUES (:parentClubId, :federationId, :subfederationId, :isFederation, :isSubFederation, :clubType,
                                        :subfedLevel, :clubTitle, :urlIdentifier, :website, :year, :corresAddressId, :billingId, :isActive,
                                        :responsibleContactId,  :assignmentCountry, :assignmentState, :assignmentActivity,
                                        :assignmentSubactivity, :hasSubfederation, :createdAt, :clubCreationProcess, :registrationYoken,
                                        :hearAboutFairgate, :numberOfContacts)';


        $this->adminConn->executeQuery($query2, array('parentClubId' => $this->clubData['parentClubId'], 'federationId' => $this->clubData['federationId'], 'subfederationId' => ($this->clubData['clubType'] == 'sub_federation_club' ? $this->clubData['parentClubId'] : 0),
            'isFederation' => ($this->clubData['clubType'] == 'federation' ? 1 : 0), 'isSubFederation' => ($this->clubData['clubType'] == 'sub_federation' ? 1 : 0), 'clubType' => $this->clubData['clubType'], 'subfedLevel' => ($this->clubData['clubType'] == 'sub_federation' ? 1 : 0), 'clubTitle' => $this->clubData['clubTitle'],
            'urlIdentifier' => $this->clubData['urlIdentifier'], 'website' => $this->clubData['website'], 'year' => $this->clubData['year'], 'corresAddressId' => $addressId, 'billingId' => $addressId, 'isActive' => $this->clubData['isActive'],
            'responsibleContactId' => $this->clubData['responsibleContactId'], 'assignmentCountry' => $this->clubData['assignmentCountry'], 'assignmentState' => $this->clubData['assignmentState'],
            'assignmentActivity' => $this->clubData['assignmentActivity'], 'assignmentSubactivity' => $this->clubData['assignmentSubactivity'], 'hasSubfederation' => $this->clubData['hasSubfederation'], 'createdAt' => 'now()',
            'clubCreationProcess' => 'Registration', 'registrationYoken' => $this->clubData['registrationYoken'], 'hearAboutFairgate' => $this->clubData['hearAboutFairgate'], 'numberOfContacts' => $this->clubData['numberOfContacts']
        ));
        $clubId = $this->adminConn->lastInsertId();
        $query3 = "INSERT INTO `fg_club_i18n` (`id`, `title_lang`, `lang`, `is_active`) VALUES ($clubId, '{$this->clubData['clubTitle']}', 'de', 1)";
        $this->adminConn->executeQuery($query3);


        return $clubId;
    }

    /**
     * This method is used to save club details in Existing Db
     * 
     * @param type $addressId Id from club address table
     * 
     * @return type
     */
    private function insertClubDetailsInExistingDb($clubId, $addressId)
    {
        $query2 = 'INSERT INTO `fg_club` (`id`, `parent_club_id`, `federation_id`, `sub_federation_id`, `is_federation`, `is_sub_federation`, `club_type`, 
                                        `subfed_level`, `title`, `url_identifier`,
                                          
                                         `has_subfederation`)

                                VALUES (:Id, :parentClubId, :federationId, :subfederationId, :isFederation, :isSubFederation, :clubType,
                                        :subfedLevel, :clubTitle, :urlIdentifier,
                                        
                                         :hasSubfederation)';


        $this->conn->executeQuery($query2, array('Id' => $clubId, 'parentClubId' => $this->clubData['parentClubId'], 'federationId' => $this->clubData['federationId'], 'subfederationId' => ($this->clubData['clubType'] == 'sub_federation_club' ? $this->clubData['parentClubId'] : 0),
            'isFederation' => ($this->clubData['clubType'] == 'federation' ? 1 : 0), 'isSubFederation' => ($this->clubData['clubType'] == 'sub_federation' ? 1 : 0), 'clubType' => $this->clubData['clubType'], 'subfedLevel' => ($this->clubData['clubType'] == 'sub_federation' ? 1 : 0), 'clubTitle' => $this->clubData['clubTitle'],
            'urlIdentifier' => $this->clubData['urlIdentifier']
            , 'hasSubfederation' => $this->clubData['hasSubfederation']
        ));
        //$newClub = $this->conn->lastInsertId();
        $query3 = "INSERT INTO `fg_club_i18n` (`id`, `title_lang`, `lang`, `is_active`) VALUES ($clubId, '{$this->clubData['clubTitle']}', 'de', 1)";
        $this->conn->executeQuery($query3);
        //return $newClub;
    }

    /**
     * This method is used to save language settings
     * 
     * @return void
     */
    public function saveLanguageSettings()
    {
        if ($this->clubData['clubType'] == 'federation' || $this->clubData['clubType'] == 'standard_club') {
            $query1 = "INSERT INTO `fg_club_language` (`club_id`, `correspondance_lang`, `system_lang`, `visible_for_club`, `date_format`, `time_format`, `thousand_separator`, `decimal_marker`) VALUES
                            ({$this->clubData['clubId']}, 'de', 'de', 1, 'dd.mm.YY', 'H:i', 'default', 'default');";
            $this->conn->executeQuery($query1);

            $languageId = $this->conn->lastInsertId();
            $query2 = "INSERT INTO `fg_club_language_settings` (`club_language_id`, `club_id`, `sort_order`, `is_active`) VALUES
                        ({$languageId}, {$this->clubData['clubId']}, 1, 1);";
            $this->conn->executeQuery($query2);
        } else {

            $query3 = "INSERT INTO `fg_club_language_settings` (`club_language_id`, `club_id`, `sort_order`, `is_active`) SELECT `club_language_id`, {$this->clubData['clubId']}, `sort_order`, `is_active` FROM fg_club_language_settings WHERE club_id = {$this->clubData['federationId']};";
            $this->conn->executeQuery($query3);
        }
    }

    /**
     * This method is used to save Club attributes
     * 
     * @return void
     */
    public function saveClubAttribute()
    {
        $query1 = "INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`, privacy_contact, is_confirm_contact, sort_order, is_required_type, profile_status) SELECT `attribute_id`, {$this->clubData['clubId']}, privacy_contact, is_confirm_contact, sort_order, is_required_type, profile_status  FROM fg_cm_club_attribute WHERE club_id = 1;";
        $this->conn->executeQuery($query1);

        if ($this->clubData['clubType'] == 'sub_federation' || $this->clubData['clubType'] == 'federation_club' || $this->clubData['clubType'] == 'sub_federation_club') {

            $query2 = "INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`) SELECT id, {$this->clubData['clubId']} FROM fg_cm_attribute WHERE club_id = {$this->clubData['federationId']};";
            $this->conn->executeQuery($query2);
        }

        if ($this->clubData['clubType'] == 'sub_federation_club') {
            $query3 = "INSERT INTO `fg_cm_club_attribute` (`attribute_id`, `club_id`) SELECT id, {$this->clubData['clubId']} FROM fg_cm_attribute WHERE club_id = parentClubId;";
            $this->conn->executeQuery($query3);
        }
    }

    /**
     * This method is used to save Club settings
     * 
     * @return void 
     */
    public function saveClubSettings()
    {

        $clubSettingsQry = "INSERT INTO `fg_club_settings` (`club_id`, `fiscal_year`, `currency`,`signature`) VALUES({$this->clubData['clubId']}, '{$this->clubData['fiscalYear']}', '{$this->clubData['currency']}','{$this->clubData['signature']}')";
        $this->conn->executeQuery($clubSettingsQry);

        $settingid = $this->conn->lastInsertId();
        $clubSettingsi18nQry = "INSERT INTO `fg_club_settings_i18n` (id, signature_lang,logo_lang,lang) VALUES ({$settingid}, '{$this->clubData['signature']}', NULL, '{$this->clubData['corresLang']}')";
        $this->conn->executeQuery($clubSettingsi18nQry);
    }

    /**
     * This method is used to create master table
     * 
     * @return void
     */
    private function createMasterTable()
    {

        if ($this->clubData['clubType'] == 'federation') {
            $createClubQry = "CREATE TABLE master_federation_{$this->clubData['clubId']} (`club_id` int(11), PRIMARY KEY (`fed_contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci);";
            $createClubQry .= "ALTER TABLE `master_federation_{$this->clubData['clubId']}` ADD FOREIGN KEY (`fed_contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION, ADD FOREIGN KEY (`club_id`) REFERENCES `fg_club`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
        } elseif ($this->clubData['clubType'] == 'sub_federation') {
            $createClubQry = "CREATE TABLE `master_federation_{$this->clubData['clubId']}`(`club_id` int(11), `contact_id` int(11),PRIMARY KEY (`contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $createClubQry .= "ALTER TABLE `master_federation_{$this->clubData['clubId']}` ADD FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION, ADD FOREIGN KEY (`club_id`) REFERENCES `fg_club`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
        } else {
            $createClubQry = "CREATE TABLE `master_club_{$this->clubData['clubId']}`(`contact_id` int(11), PRIMARY KEY (`contact_id`)) ENGINE = InnoDB DEFAULT  CHARACTER SET utf8 COLLATE utf8_general_ci;";
            $createClubQry .= "ALTER TABLE `master_club_{$this->clubData['clubId']}` ADD FOREIGN KEY (`contact_id`) REFERENCES `fg_cm_contact`(`id`) ON DELETE CASCADE ON UPDATE NO ACTION;";
        }

        $this->conn->executeQuery($createClubQry);
    }

    /**
     * Process on club confirmation
     *
     * @param type $container   container
     */
    public function saveDataOnClubConfirmation($clubId)
    {
        $this->conn->beginTransaction();
        try {
            $clubObj = $this->adminEm->getRepository('AdminUtilityBundle:FgClub')->find($clubId);
            $this->addClubMemberships($clubObj);
            $this->checkAssignClubMembershipToContact($clubObj);
            $this->checkAssignFedMembershipToContact($clubObj);
            $this->insertArticleSettingsOfClub($clubId);
            $this->insertThemeSettingsOfClub($clubId);
            
            $this->insertClubRolesAndCategory($clubId);

            $this->conn->commit();
        } catch (Exception $e) {
            // Rollback the failed transaction attempt
            $this->conn->rollback();
            throw $e;
        }

        //add modules to clubs
        $this->addModulesToClub($clubId);

        return $clubId;
    }
    
    /**
     * Insert category, roles and functions
     * 
     * @param int $clubId clubId
     */
    private function insertClubRolesAndCategory($clubId) {
        $catQry = "INSERT INTO `fg_rm_category` (`club_id`, `title`, `contact_assign`, `role_assign`, `function_assign`, `is_active`, `is_team`, `is_workgroup`, `sort_order`, `is_allowed_fedmember_subfed`, `is_allowed_fedmember_club`, `is_required_fedmember_subfed`, `is_required_fedmember_club`, `is_fed_category`) SELECT $clubId, `title`, `contact_assign`, `role_assign`, `function_assign`, `is_active`, `is_team`, `is_workgroup`, `sort_order`, `is_allowed_fedmember_subfed`, `is_allowed_fedmember_club`, `is_required_fedmember_subfed`, `is_required_fedmember_club`, `is_fed_category` FROM `fg_rm_category` WHERE club_id = 1;";
        $this->conn->executeQuery($catQry);
        
        $catI18nQry = "INSERT INTO `fg_rm_category_i18n` (`id`,`title_lang`,`lang`,`is_active`) SELECT rc.`id`, rc.`title`, cl.`correspondance_lang`, 1 FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_rm_category AS rc ON cls.club_id = rc.club_id WHERE cls.club_id =  $clubId ORDER BY cls.sort_order;";
        $this->conn->executeQuery($catI18nQry);
        
        $roleQry = "INSERT INTO `fg_rm_role` (`category_id`, `title`, `is_executive_board`, `sort_order`, `club_id`, `description`, `type`) SELECT id, 'Executive Board', 1, 1, $clubId, 'Executive Board', 'W' FROM `fg_rm_category` WHERE club_id = $clubId AND is_workgroup=1;";
        $this->conn->executeQuery($roleQry);
        
        $executiveBrdIdInserted = $this->conn->fetchAll("SELECT LAST_INSERT_ID() AS executiveBrdId");
        $executiveBrdId = $executiveBrdIdInserted[0]['executiveBrdId'];
        
        $roleI18nQry = "INSERT INTO `fg_rm_role_i18n` (`id`,`title_lang`, `description_lang`, `lang`,`is_active`) SELECT rmr.`id`, rmr.`title`, rmr.`description`, cl.`correspondance_lang`, 1 FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_rm_role AS rmr ON cls.club_id = rmr.club_id WHERE cls.club_id =  $clubId ORDER BY cls.sort_order; ";
        $this->conn->executeQuery($roleI18nQry);
        
        if($this->clubType == 'federation' || $this->clubType == 'standard_club') {
            $funQry = "INSERT INTO `fg_rm_function` (`category_id`, `title`, `sort_order`, is_federation) SELECT id, 'Präsident', 1, 0 FROM `fg_rm_category` WHERE club_id = $clubId AND is_workgroup=1;";
            $this->conn->executeQuery($funQry);
            
            $executiveBrdFunIdInserted = $this->conn->fetchAll("SELECT LAST_INSERT_ID() AS executiveBrdFunId;");
            $executiveBrdFunId = $executiveBrdFunIdInserted[0]['executiveBrdFunId'];
            
            $rolefunQry = "INSERT INTO fg_rm_role_function (role_id, function_id) VALUES($executiveBrdId, $executiveBrdFunId);";
            $this->conn->executeQuery($rolefunQry);
            
            //To DO
            if($this->clubType == 'federation') {
                
            }
        }        
	
    }

    /**
     * Method to insert article settings
     * 
     * @param int $clubId clubId
     */
    private function insertArticleSettingsOfClub($clubId)
    {

        $articleCatQry = "INSERT INTO fg_cms_article_category (title,sort_order,club_id) VALUES ('Kategorie',1,$clubId)";
        $this->conn->executeQuery($articleCatQry);

        $articleCatI18nQry = "INSERT INTO fg_cms_article_category_i18n (id,lang,title_lang) SELECT AC.id,'de',AC.title FROM fg_cms_article_category AC WHERE AC.club_id = $clubId LIMIT 1;";
        $this->conn->executeQuery($articleCatI18nQry);

        $articleClubSettingsQry = "INSERT INTO `fg_cms_article_clubsetting` (club_id, comment_active, show_multilanguage_version, timeperiod_start_day, timeperiod_start_month) VALUES ($clubId, '0', '0', '1', '1');";
        $this->conn->executeQuery($articleClubSettingsQry);
    }

    /**
     * Method to insert theme settings
     * 
     * @param int $clubId clubId
     */
    private function insertThemeSettingsOfClub($clubId)
    {
        $themeSettingsQry = "INSERT INTO `fg_tm_theme_configuration` (`club_id`, `theme_id`, `title`, `header_scrolling`, `created_at`, `updated_at`, `is_active`, `is_default`, `custom_css`, `created_by`, `updated_by`, `bg_image_selection`, `bg_slider_time`, `color_scheme_id`) VALUES  "
            . "($clubId, 1, 'Die Standard-Konfiguration', 0, NULL, NULL, 1, 1, NULL, 1, NULL, 'random', NULL, 1);";
        $this->conn->executeQuery($themeSettingsQry);

        $themeSettingsQry2 = "INSERT INTO `fg_tm_theme_configuration` (`club_id`, `theme_id`, `title`, `header_scrolling`, `created_at`, `updated_at`, `is_active`, `is_default`, `custom_css`, `created_by`, `updated_by`, `bg_image_selection`, `bg_slider_time`, `color_scheme_id`) VALUES "
            . " ($clubId, 2, 'Die Standard-Konfiguration 2', 0, NULL, NULL, 0, 1, NULL, 1, NULL, 'random', NULL, 5); ";
        $this->conn->executeQuery($themeSettingsQry2);
    }

    /**
     * Method to assign modules for club
     * 
     * @param int $clubId  clubId
     */
    private function addModulesToClub($clubId)
    {
        $modulesForTesingPeriod = $this->container->getParameter('modulesForTesingPeriod');
        foreach ($modulesForTesingPeriod as $moduleId) {
            $moduleInsertQry = "INSERT INTO `fg_mb_club_modules` (`id`, `club_id`, `signed_by`, `module_id`, `is_cost_onetime`, `cost_onetime`, `is_cost_yearly`, `cost_yearly`, `invoice_amount`, `signed_on`, `is_module_active`, `backend_terms`) "
                . "VALUES (NULL, '$clubId', '1', '$moduleId', NULL, NULL, NULL, NULL, NULL, now(), '1', '');";
            $this->adminConn->executeQuery($moduleInsertQry);
        }
    }

    /**
     * check c2 is mandatory and assign fed memberships to contact
     * 
     * @param object $clubObj      club doctrine object
     */
    private function checkAssignFedMembershipToContact($clubObj)
    {
        if ($this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club') {
            $federationId = $clubObj->getFederationId();
            $federationClubObj = $this->adminEm->getRepository('AdminUtilityBundle:FgClub')->find($federationId);
            $fedMembershipMandatory = $federationClubObj->getFedMembershipMandatory();
            if ($fedMembershipMandatory) { //c1 is on
                $this->assignFedMembershipToContact($clubObj, $federationId);
            }
        }
    }

    /**
     * Assign fed membership to contacts
     * 
     * @param object $clubObj      club doctrine object
     * @param int    $federationId federationId
     */
    private function assignFedMembershipToContact($clubObj, $federationId)
    {
        $clubId = $clubObj->getId();
        $memberships = $this->conn->fetchAll("SELECT `id`FROM `fg_cm_membership` WHERE `club_id` = $federationId ORDER BY `sort_order` ASC LIMIT 0,1");
        $membershipId = $memberships[0]['id'];
        $fgFedMembershipsObj = new FgFedMemberships($this->container);
        $contactId = $clubObj->getFairgateSolutionContact()->getId();
        $fgFedMembershipsObj->clubId = $clubId;
        $fgFedMembershipsObj->loggedContactId = $contactId;
        $fgFedMembershipsObj->federationId = $federationId;
        $fgFedMembershipsObj->processFedMembership($contactId, $membershipId);
    }

    /**
     * check c1 is on and assign club memberships to contact
     * 
     * @param object $clubObj club doctrine object
     */
    private function checkAssignClubMembershipToContact($clubObj)
    {
        if ($this->clubType == 'standard_club') {
            $federationClubMembershipAvailable = $clubObj->getClubMembershipAvailable();
        } else if ($this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club') {
            $federationId = $clubObj->getFederationId();
            $federationClubObj = $this->adminEm->getRepository('AdminUtilityBundle:FgClub')->find($federationId);
            $federationClubMembershipAvailable = $federationClubObj->getClubMembershipAvailable();
        }
        if ($federationClubMembershipAvailable) { //c1 is on
            $this->assignClubMembershipToContact($clubObj);
        }
    }
    /*
     * Assign memberships to club (add to fg_cm_membership, fg_cm_membership_i18n, fg_cm_membership_log),
     * set variable clubType, membershipId
     * 
     * @param object $clubObj club doctrine object
     */

    private function addClubMemberships($clubObj)
    {
        $clubId = $clubObj->getId();
        $clubType = $clubObj->getClubType();
        if ($clubType == 'federation' || $clubType == 'standard_club') {
            $clubMembershipQry = "INSERT INTO `fg_cm_membership` (`club_id`, `title`, `sort_order`) VALUES ($clubId, 'Aktivmitglied', 1); ";
            $this->conn->executeQuery($clubMembershipQry);
            $clubMembershipI18nQry = "INSERT INTO `fg_cm_membership_i18n` (`id`,`title_lang`, `lang`) SELECT cm.`id`, cm.`title`, cl.`correspondance_lang` FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_cm_membership AS cm ON cls.club_id = cm.club_id WHERE cls.club_id =  $clubId ORDER BY cls.sort_order;";
            $this->conn->executeQuery($clubMembershipI18nQry);
            $clubMembershipLogQry = "SELECT LAST_INSERT_ID() INTO @membershipId;"
                . "INSERT INTO fg_cm_membership_log (club_id, membership_id, date,kind,field,value_after,changed_by) VALUES ($clubId,@membershipId,now(),'data','Name','Aktivmitglied', 1);";
            $this->conn->executeQuery($clubMembershipLogQry);
            $membershipInserted = $this->conn->fetchAll("SELECT @membershipId as membershipId");
            $membershipId = $membershipInserted[0]['membershipId'];
        } else if ($clubType == 'sub_federation_club' || $clubType == 'federation_club') {
            $federationId = $clubObj->getFederationId();
            $federationClubObj = $this->adminEm->getRepository('AdminUtilityBundle:FgClub')->find($federationId);
            $federationClubMembershipAvailable = $federationClubObj->getClubMembershipAvailable();
            if ($federationClubMembershipAvailable) {
                $clubMembershipQry = "INSERT INTO `fg_cm_membership` (`club_id`, `title`, `sort_order`) VALUES ($clubId, 'Aktivmitglied', 1)";
                $this->conn->executeQuery($clubMembershipQry);
                $clubMembershipI18nQry = "INSERT INTO `fg_cm_membership_i18n` (`id`,`title_lang`, `lang`) SELECT cm.`id`, cm.`title`, cl.`correspondance_lang` FROM `fg_club_language_settings` AS cls INNER JOIN fg_club_language cl ON cl.id = cls.club_language_id LEFT JOIN fg_cm_membership AS cm ON cls.club_id = cm.club_id WHERE cls.club_id =  $clubId ORDER BY cls.sort_order;";
                $this->conn->executeQuery($clubMembershipI18nQry);
                $clubMembershipLogQry = "SELECT LAST_INSERT_ID() INTO @membershipId;"
                    . "INSERT INTO fg_cm_membership_log (club_id, membership_id, date,kind,field,value_after,changed_by) VALUES ($clubId,@membershipId,now(),'data','Name','Aktivmitglied', 1);";
                $this->conn->executeQuery($clubMembershipLogQry);
                $membershipInserted = $this->conn->fetchAll("SELECT @membershipId as membershipId");
                $membershipId = $membershipInserted[0]['membershipId'];
            }
        }
        $this->clubType = $clubType;
        $this->membershipId = $membershipId;
    }

    /**
     * Add club membership to contact and write log entry
     * 
     * @param object $clubObj      club doctrine object
     */
    private function assignClubMembershipToContact($clubObj)
    {
        $clubId = $clubObj->getId();
        $contactId = $clubObj->getFairgateSolutionContact()->getId();
        $joiningDate = date("d.m.Y");
        $contactName = $clubObj->getFairgateSolutionContact()->getName();
        FgSettings::setDateFormat('dd.mm.YY');
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateClubMembershipOfContact($contactId, $this->membershipId, $joiningDate, 'assign');
        $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->insertFedMembershipHistory($clubId, $contactId, $this->membershipId, $contactId, 'club', $joiningDate);
        $logHandlerObj = new FgLogHandler($this->container);
        $logHandlerObj->clubId = $clubId;
        $logArr[] = array('kind' => 'assigned contacts', 'membership_id' => $this->membershipId, 'value_after' => $contactName, 'contact_id' => $contactId, 'date' => $joiningDate);
        $logHandlerObj->processLogEntryAction('club_membership_assignment', 'fg_cm_membership_log', $logArr);
    }
}
