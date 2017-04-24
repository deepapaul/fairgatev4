<?php

namespace Common\UtilityBundle\Util;

use Clubadmin\ContactBundle\Util\FedMemApplication;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use Clubadmin\ContactBundle\Util\DuplicateContactDetails;
use Admin\UtilityBundle\Classes\SyncFgadmin;
use Common\UtilityBundle\Util\FgClubSyncDataToAdmin;

/**
 * FgFedMemberships.
 *
 * This class is used for federation membership assigning/changing/removal actions of a contact
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
class FgFedMemberships
{

    /**
     * Container object.
     *
     * @var object
     */
    private $container;

    /**
     * Entity Manager object.
     *
     * @var object
     */
    private $em;

    /**
     * Club object.
     *
     * @var object
     */
    private $club;

    /**
     * Contact object.
     *
     * @var object
     */
    private $contact;

    /**
     * Club id.
     *
     * @var int
     */
    public $clubId;

    /**
     * Federation Id.
     *
     * @var int
     */
    public $federationId;

    /**
     * Subfederation Id.
     *
     * @var int
     */
    private $subfederationId;

    /**
     * Contact id.
     *
     * @var int
     */
    private $contactId;

    /**
     * Fed Contact Id.
     *
     * @var int
     */
    private $fedContactId;

    /**
     * Subfed Contact Id.
     *
     * @var int
     */
    private $subFedContactId;

    /**
     * Fed or sub fed own contact flag.
     *
     * @var int
     */
    private $isFedOwnContact;

    /**
     * Contact system and federation details.
     *
     * @var int
     */
    private $contactData = array();

    /**
     * Newly assigned/changed membership id.
     *
     * @var array
     */
    private $newMembershipId;

    /**
     * Old membership id.
     *
     * @var int
     */
    private $oldMembershipId;

    /**
     * Membership assign/change/remove flag.
     *
     * @var string
     */
    private $membershipProcess = 'assign';

    /**
     * Fed membership customization C.2 value.
     *
     * @var int
     */
    private $fedMembershipMandatory;

    /**
     * Fed membership assign/change customization C.3 value.
     *
     * @var int
     */
    private $assignFedmembershipWithApplication;

    /**
     * Sharing of fed-member customization C.4 value.
     *
     * @var int
     */
    private $addExistingFedMemberClub;

    /**
     * User defined leaving date.
     */
    private $leavingDate = '';

    /**
     * Club Type
     *
     * @var string
     */
    private $clubType;

    /**
     * Merging flag
     *
     * @var int
     */
    private $isMerging = 0;

    /**
     * Constructor.
     *
     * @param array $container Container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->loggedContactId = $this->contact->get('id');
        $this->getClubDetails();
    }

    /**
     * This function is used to get some system field data and contact details of a contact.
     *
     * @param int $contactId ContactId
     *
     * @return int $isFedMembershipPending Whether the contact's federation membership is still pending for confirmation
     */
    private function getContactDetails($contactId)
    {
        $contactData = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->getContactAndFedMembershipDetails($contactId);
        $this->contactId = $contactData['id'];
        $this->fedContactId = $contactData['fedContactId'];
        $this->subFedContactId = $contactData['subfedContactId'];
        $this->oldMembershipId = $contactData['fedMembershipCatId'];
        $this->contactData = array('contactName' => $contactData['contactName'], 'primaryEmail' => $contactData['primaryEmail'], 'mergeToContact' => $contactData['mergeToContact'], 'allowMerging' => $contactData['allowMerging'], 'isCompany' => $contactData['isCompany'], 'oldFedMembershipId' => $contactData['oldFedMembershipId']);
        $this->isFedOwnContact = (($this->clubType == 'federation') || ($this->clubType == 'sub_federation')) ? 1 : 0;
        $this->assignFedmembershipWithApplication = (($this->clubType == 'sub_federation_club') || ($this->clubType == 'federation_club')) ? $this->assignFedmembershipWithApplication : 0;
        $isFedMembershipPending = (($this->oldMembershipId != null && $this->oldMembershipId != '' && $contactData['isFedMembershipConfirmed'] == '1')) ? 1 : 0;

        return $isFedMembershipPending;
    }

    /**
     * This function is used to get the club details and customization details.
     */
    private function getClubDetails()
    {
        $this->clubId = $this->club->get('id');
        $this->federationId = ($this->club->get('type') == 'federation') ? $this->clubId : $this->club->get('federation_id');
        $this->subfederationId = $this->club->get('sub_federation_id');
        $this->fedMembershipMandatory = $this->club->get('fedMembershipMandatory');
        $this->assignFedmembershipWithApplication = $this->club->get('assignFedmembershipWithApplication');
        $this->addExistingFedMemberClub = $this->club->get('addExistingFedMemberClub');
        $this->clubType = $this->club->get('type');
    }

    /**
     * This function is used to set the club details and customization details.
     * @param array $cluVar  clubdetails
     */
    public function setClubDetailsForExternalApplication($cluVar)
    {
        $this->clubId = $cluVar['id'];
        $this->federationId = $cluVar['federationId'];
        $this->subfederationId = $cluVar['sub_federation_id'];
        $this->clubType = $cluVar['clubType'];
        $this->fedMembershipMandatory = 1;
        $this->assignFedmembershipWithApplication = 0;
        $this->addExistingFedMemberClub = 1;
    }
    /**
     * This function is used to set the club details and customization details and share contact.
     * 
     * @param array $cluVar cuctumization array
     * @return bool
     */
    public function shareImportedContact($cluVar)
    {
        $this->clubId = $cluVar['id'];
        $this->federationId = $cluVar['federationId'];
        $this->subfederationId = $cluVar['sub_federation_id'];
        $this->clubType = $cluVar['clubType'];
        $this->fedContactId = $cluVar['fedContactId'];
        
        return $this->addAnExistingFedMemberToClubDirectly();
    }

    /**
     * This function is invoked when federation membership is assigned or changed for a contact.
     *
     * @param int $contactId    Contact Id
     * @param int $membershipId Membership Id
     */
    public function processFedMembership($contactId, $membershipId = null)
    {
        $isFedMembershipPending = $this->getContactDetails($contactId);
        $this->newMembershipId = $membershipId;
        if ($this->oldMembershipId == $this->newMembershipId) {
            return;
        }
        if (!$isFedMembershipPending) {
            if ($this->newMembershipId == null || $this->newMembershipId == '') {
                $this->membershipProcess = 'remove';
                $success = $this->removeFedMembership();
            } elseif ($this->oldMembershipId != null && $this->oldMembershipId != '') {
                //if the contact has a fed-membership change
                $this->membershipProcess = 'change';
                $this->changeFedMembership();
            } else {                
                //if the contact is assigned a fed-membership
                $this->assignFedMembership();
            }
        }         
        /** Sync Fed member count to the Admin DB **/
        $fgAdmin = new SyncFgadmin($this->container);
        $fgAdmin->syncFedMemberCount();
        /***********************************************/
        
        /** Update the subscriber count **/
        $clubSyncObject = new FgClubSyncDataToAdmin($this->container);
        $clubSyncObject->updateSubscriberCount($this->clubId)->updateAdminCount($this->clubId);
        /***********************************************/
    }

    /**
     * This function is used to check whether fed membership can be assigned to a contact.
     *
     * @return $fedMemberAlreadyExist Whether a fed member with same credentials exist already
     */
    private function checkWhetherFedMembershipCanBeAssigned()
    {
        $fedMemberAlreadyExist = 0;
        if ($this->contactData['primaryEmail'] != '' && $this->contactData['primaryEmail'] != null) {
            $fedMemberAlreadyExist = $this->em->getRepository('CommonUtilityBundle:MasterSystem')->checkForFedMembersWithSameEmail($this->federationId, $this->fedContactId, $this->contactData['primaryEmail']);
        }

        return $fedMemberAlreadyExist;
    }

    /**
     * This function is used to assign fed membership to a contact.
     */
    private function assignFedMembership()
    {
        $fedMemberAlreadyExist = $this->checkWhetherFedMembershipCanBeAssigned();
        $mergeToContactId = $this->contactData['mergeToContact'];
        $allowMerging = $this->contactData['allowMerging'];
        if ((!$fedMemberAlreadyExist && empty($mergeToContactId)) || ($this->assignFedmembershipWithApplication == 1 && !empty($mergeToContactId))) {            
            //check for customization C.3 only in sub_federation_club and federation_club levels
            if ($this->assignFedmembershipWithApplication) {
                $this->isMerging = (!empty($mergeToContactId)) ? 1 : 0;
                $this->assignFedMembershipWithApplication();
            } else {                
                $this->assignFedMembershipWithoutApplication();
            }
        } elseif ($this->assignFedmembershipWithApplication != 1) { //with out application
            if (!empty($mergeToContactId) && $allowMerging) {
                //with out application
                $merge = new FedMemApplication($this->container, $this->contactId, $this->fedContactId);
                $merge->mergeContact($mergeToContactId);
            }
        }
    }

    /**
     * This function is invoked when fed membership is to be assigned with application.
     */
    private function assignFedMembershipWithApplication()
    {
        $this->assignFedMembershipToContact();
        $this->createApplicationForFedMembershipConfirmation();
        $this->writeClubAssignment();
    }

    /**
     * This function is invoked when fed membership is to be assigned without application.
     */
    private function assignFedMembershipWithoutApplication()
    {
        $this->assignFedMembershipToContact();
        $this->writeFedMembershipLog();        
        $this->writeFedMembershipHistory();
        $this->writeClubAssignment();
    }

    /**
     * This function is invoked when fed membership is assigned to a contact.
     */
    private function assignFedMembershipToContact()
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateFedMembershipDetails($this->clubId, $this->fedContactId, $this->newMembershipId, $this->oldMembershipId, $this->isFedOwnContact, $this->assignFedmembershipWithApplication);
        //for club owned, fed owned and sub-fed owned contacts , only joining date log entries corresponding to fed entry is updated for fed membership
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateJoiningOrLeavingDatesOfFedMembership($this->fedContactId);
    }

    /**
     * This function is used to insert the federation membership log on assigning or changing fed membership.
     */
    private function writeFedMembershipLog()
    {
        $logArr = array();
        //insert to fg_cm_membership log
        if ($this->membershipProcess == 'assign' || $this->membershipProcess == 'change') {
            $valArr = array('kind' => 'assigned contacts', 'membership_id' => $this->newMembershipId, 'value_after' => $this->contactData['contactName'], 'contact_id' => $this->contactId);
            if ($this->leavingDate != '') {
                $valArr['date'] = $this->leavingDate;
            }
            $logArr[] = $valArr;
        }

        if ($this->membershipProcess == 'change' || $this->membershipProcess == 'remove') {
            $valArr = array('kind' => 'assigned contacts', 'membership_id' => $this->oldMembershipId, 'value_before' => $this->contactData['contactName'], 'contact_id' => $this->contactId);
            if ($this->leavingDate != '') {
                $valArr['date'] = $this->leavingDate;
            }
            $logArr[] = $valArr;
        }

        if (count($logArr) > 0) {
            $logHandlerObj = new FgLogHandler($this->container);            
            $logHandlerObj->clubId = $this->clubId;
            $logHandlerObj->processLogEntryAction('fedMembershipAssignment', 'fg_cm_membership_log', $logArr);
        }
    }

    /**
     * This function is used to insert fed membership history of a contact on fed membership assignment.
     */
    private function writeFedMembershipHistory()
    {
        //insert to fg_cm_membership history
        $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->insertFedMembershipHistory($this->federationId, $this->fedContactId, $this->newMembershipId, $this->loggedContactId);
    }

    /**
     * This function is used to update leaving date in fed membership history of a contact on changing or removing fed membership.
     */
    private function updateFedMembershipHistory()
    {
        //update leaving date in fg_cm_membership history
        $this->em->getRepository('CommonUtilityBundle:FgCmMembershipHistory')->updateFedMembershipHistory($this->fedContactId, $this->oldMembershipId, $this->leavingDate);
    }

    /**
     * This function is used to create application for confirmation of fed membership.
     */
    private function createApplicationForFedMembershipConfirmation()
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmFedmembershipConfirmationLog')->createApplicationForConfirmation($this->clubId, $this->fedContactId, $this->federationId, $this->loggedContactId, $this->oldMembershipId, $this->newMembershipId, $this->isMerging);
    }

    /**
     * This function is used to insert Club assignment for a contact.
     */
    private function writeClubAssignment()
    {
        if ($this->clubType == 'sub_federation_club' || $this->clubType == 'federation_club') {
            $isApproved = ($this->assignFedmembershipWithApplication) ? 0 : 1;
            $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->addClubAssignment($this->fedContactId, $this->clubId, $isApproved);
        }
    }

    /**
     * This function is used to change fed membership of a contact.
     */
    private function changeFedMembership()
    {
        if ($this->oldMembershipId != $this->newMembershipId) {
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateFedMembershipDetails($this->clubId, $this->fedContactId, $this->newMembershipId, $this->oldMembershipId, 0, $this->assignFedmembershipWithApplication);
            if ($this->assignFedmembershipWithApplication) {
                $this->createApplicationForFedMembershipConfirmation();
            } else {
                $this->writeFedMembershipLog();
                $this->updateFedMembershipHistory();
                $this->writeFedMembershipHistory();
            }
        }
    }

    /**
     * This function is used to remove the fed membership of a contact.
     */
    public function removeFedMembership($archiveFlag = 0)
    {
        $success = false;
        $this->em->getConnection()->beginTransaction();
        try {
            $isSharedInMultipleClubs = ($this->isFedOwnContact == 1) ? false : $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkWhetherContactIsSharedInMultipleClubs($this->fedContactId, $this->clubId);
            if ($isSharedInMultipleClubs) {
                if ($archiveFlag) {
                    $this->handlePendingFedMembershipApplications();
                }
                //make a copy of the contact in fed and subfed
                $newContactIds = $this->makeACopyOfContactInFederations();
            } else {
                $this->deleteFedMembershipSpecificDetailsOfContact();

                //for deleting the federation and sub-federation assignments
                $clubDetails = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subfederationId, 'clubType' => $this->clubType, 'defSysLang' => $this->club->get('default_system_lang'), 'defaultClubLang' => $this->club->get('default_lang'));
                $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->deleteFederationAssignmentOfContact($clubDetails, $this->contactId, $this->container, array('workgroup' => $this->container->get('translator')->trans('WORKGROUPS')));
            }
            //set FFM flag
            $contactDetailarray = array('FedContactId' => $this->fedContactId, 'SubfedContactId' => $this->subFedContactId);
            $contactPdo = new ContactPdo($this->container);
            $contactPdo->setFormerfederationflag($this->clubId, $contactDetailarray);

            $this->removeClubAssignment();
            //Exsisting main contact handling
            $newFedContactId = ($newContactIds['newFedContactId']) ? $newContactIds['newFedContactId'] : $this->fedContactId;
            $actionType = ($archiveFlag == 1 || ($archiveFlag == 0 && $this->fedMembershipMandatory)) ? 'archive' : 'remove';
            $contactPdo->sharedMainContactUpdation($this->fedContactId, $newFedContactId, $this->clubId, $actionType);

            if (isset($newContactIds)) {
                $this->fedContactId = $newContactIds['newFedContactId'];
                $this->subFedContactId = $newContactIds['newSubFedContactId'];
            }
            $this->writeFedMembershipLog();
            $this->updateFedMembershipHistory();
            $this->updateFedMembershipRemovalOfContact();

            if ($this->fedMembershipMandatory && $archiveFlag == 0) {
                $this->archiveContact();
            }
            $this->em->getConnection()->commit();
            $success = true;
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
//            echo $e->getMessage();
//            throw $e;
        }

        return $success;
    }

    /**
     * This function is used to remove all those data of a contact which
     * depends on fed membership.
     */
    private function deleteFedMembershipSpecificDetailsOfContact()
    {
        //For deleting the connections-federation
        $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->deleteFederationConnectionsOfContact($this->federationId, $this->fedContactId, $this->contactData['isCompany'], $this->container, $this->club, $this->loggedContactId, 'federation', $this->club->get('default_system_lang'));
        //For deleting user rights -federation
        $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->deleteFederationUserrightsOfContact($this->federationId, $this->fedContactId);
        //For deleting heirarchy level documents - federation
        $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->removeFedDocsForContact($this->federationId, $this->fedContactId);
        if ($this->subfederationId != 0) {
            //For deleting the connections-subfederation
            $this->em->getRepository('CommonUtilityBundle:FgCmLinkedcontact')->deleteFederationConnectionsOfContact($this->subfederationId, $this->subFedContactId, $this->contactData['isCompany'], $this->container, $this->club, $this->loggedContactId, 'sub_federation', $this->club->get('default_system_lang'));
            //For deleting user rights -subfederation
            $this->em->getRepository('CommonUtilityBundle:SfGuardGroup')->deleteFederationUserrightsOfContact($this->subfederationId, $this->subFedContactId);
            //For deleting heirarchy level documents - subfederation
            $this->em->getRepository('CommonUtilityBundle:FgDmAssigment')->removeFedDocsForContact($this->subfederationId, $this->subFedContactId);
        }
        if (!$this->isFedOwnContact) {
            $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->removePendingApplications($this->fedContactId);
        }
    }

    /**
     * This function is used to update the fed membership removal of contact (contact table entries).
     */
    private function updateFedMembershipRemovalOfContact()
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateFedMembershipRemovalDetails($this->fedContactId);
        //update leaving date of fed and sub-fed contact id entries only
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateJoiningOrLeavingDatesOfFedMembership($this->fedContactId, 'leavingDate', $this->leavingDate);
    }

    /**
     * This function is used to remove the club assignment.
     */
    private function removeClubAssignment()
    {
        if (!$this->isFedOwnContact) {
            $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->removeClubAssignment($this->fedContactId, $this->clubId);
        }
    }

    /**
     * This function is used for sharing an existing fed member to a club.
     */
    public function addExistingFedMember($fedContactId)
    {
        $this->fedContactId = $fedContactId;
        if ($this->addExistingFedMemberClub == 0) {
            return;
        }
        $success = false;
        if ($this->addExistingFedMemberClub == 1) {
            $success = $this->addAnExistingFedMemberToClubDirectly();
        } else {
            $pendingApplicationsCount = $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->getPendingApplicationsCount($fedContactId, $this->clubId);
            if ($pendingApplicationsCount == 0) {
                $this->em->getRepository('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog')->createApplicationForConfirmation($this->clubId, $fedContactId, $this->federationId, $this->loggedContactId);
                $success = true;
            }
        }

        return $success;
    }

    /**
     * This function is used to add an existing fed member to club.
     */
    private function addAnExistingFedMemberToClubDirectly()
    {
        $success = false;
        $this->em->getConnection()->beginTransaction();
        try {
            $email = $this->em->getRepository('CommonUtilityBundle:MasterSystem')->getPrimaryEmail($this->fedContactId);

            $sameEmailContactExist = ($email != '') ? $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkForContactsWithSameEmail($this->fedContactId, array($this->clubId, $this->subfederationId), $email) : 0;
            if ($sameEmailContactExist == 0) {
                if ($this->subfederationId) {
                    $this->createContactInSubFederation($email);
                }
                $this->createContactInClub($email);
                $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->addClubAssignment($this->fedContactId, $this->clubId);
                //last updated - fg_cm_contact
                $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($this->fedContactId, 'fedContact');
                $success = true;
            }
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
        }

        return $success;
    }

    /**
     * This function is used to create a contact in sub-federation.
     *
     * @param string $email           Email address
     * @param int    $subFederationId Sub federation id
     */
    private function createContactInSubFederation($email, $subFederationId = 0)
    {
        $subFederationId = ($subFederationId) ? $subFederationId : $this->subfederationId;
        $this->subFedContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkWhetherContactIsSharedInAClub($subFederationId, $this->fedContactId);
        //check whether contact exist in current sub-federation or not
        if (!$this->subFedContactId) {
            $this->subFedContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->insertContact($this->container, $subFederationId, $this->fedContactId, '', $email);
            $contactObj = $this->em->find('CommonUtilityBundle:FgCmContact', $this->subFedContactId);
            $contactObj->setSubfedContact($contactObj);
            $this->em->persist($contactObj);
            $this->em->flush();
            if ($email != '') {
                $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->insertSfGuardEntry($this->container, $this->federationId, $subFederationId, $this->fedContactId, $this->subFedContactId);
            }
            $this->em->getRepository('CommonUtilityBundle:FgCmContact')->insertToMasterTable($this->container, $subFederationId, $this->subFedContactId, 'federation');
        }
    }

    /**
     * This function is used to create a contact in master tables.
     *
     * @param string $email  Email address
     * @param int    $clubId ClubId
     */
    private function createContactInClub($email, $clubId = 0)
    {
        $clubId = ($clubId) ? $clubId : $this->clubId;
        $this->contactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->insertContact($this->container, $clubId, $this->fedContactId, $this->subFedContactId, $email);
        if ($email != '') {
            $this->em->getRepository('CommonUtilityBundle:SfGuardUser')->insertSfGuardEntry($this->container, $this->federationId, $clubId, $this->fedContactId, $this->contactId);
        }
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->insertToMasterTable($this->container, $clubId, $this->contactId);
    }

    /**
     * This function is used to confirm or discrd the club assignment application.
     *
     * @param array $confirmIds Array of confirm ids
     * @param int   $isConfirm  0 or 1
     *
     * @return bool $success Flag to identify whether process suceeded or not.
     */
    public function confirmOrDiscardClubAssignment($confirmIds, $isConfirm = 0)
    {
        $success = false;
        $this->em->getConnection()->beginTransaction();
        try {
            foreach ($confirmIds as $confirmId) {
                $confirmObj = $this->em->find('CommonUtilityBundle:FgCmClubAssignmentConfirmationLog', $confirmId);
                if ($isConfirm) {
                    $this->fedContactId = $confirmObj->getFedContact()->getId();
                    $email = $this->em->getRepository('CommonUtilityBundle:MasterSystem')->getPrimaryEmail($this->fedContactId);
                    $clubId = $confirmObj->getClub()->getId();
                    $subFederationId = $this->em->find('CommonUtilityBundle:FgClub', $clubId)->getSubFederationId();
                    $sameEmailContactExist = ($email != '') ? $this->em->getRepository('CommonUtilityBundle:FgCmContact')->checkForContactsWithSameEmail($this->fedContactId, array($clubId, $subFederationId), $email, $clubId) : 0;
                    if ($sameEmailContactExist == 0) {
                        if ($subFederationId) {
                            $this->createContactInSubFederation($email, $subFederationId);
                        }
                        $this->createContactInClub($email, $clubId);
                        $this->updateClubAssignmentApplicationStatus($confirmObj, 'CONFIRMED');
                        $this->em->getRepository('CommonUtilityBundle:FgClubAssignment')->addClubAssignment($this->fedContactId, $clubId);
                        //last updated - fg_cm_contact
                        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateLastUpdated($this->fedContactId, 'fedContact');
                        $success = true;
                    }
                } else {
                    $this->updateClubAssignmentApplicationStatus($confirmObj);
                    $success = true;
                }
            }
            $this->em->getConnection()->commit();
        } catch (\Exception $e) {
            $this->em->getConnection()->rollback();
//            echo $e->getMessage();
//            throw $e;
        }

        return $success;
    }

    /**
     * This function is used to update the club assignment application status.
     *
     * @param object $confirmObj    FgCmClubAssignmentConfirmationLog object
     * @param string $confirmStatus CONFIRMED/DECLINED
     */
    private function updateClubAssignmentApplicationStatus($confirmObj, $confirmStatus = 'DECLINED')
    {
        $loggedInContact = $this->contact->get('fedContactId');
        $contactObj = $this->em->getReference('CommonUtilityBundle:FgCmContact', $loggedInContact);

        $confirmObj->setDecidedDate(new \DateTime());
        $confirmObj->setDecidedBy($contactObj);
        $confirmObj->setStatus($confirmStatus);
        $this->em->persist($confirmObj);
        $this->em->flush();
    }

    /**
     * This function is used to copy a contact's data in federations.
     *
     * @return array $contactIdsArr Array of new contact ids.
     */
    private function makeACopyOfContactInFederations()
    {
        $newSubFedContactId = '';
        $isSharedInClubsUnderSubfed = 0;
        $contactPdo = new ContactPdo($this->container);

        //copy fg_cm_contct entry of federation
        $newFedContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->copyContactEntryInFederation($this->clubId, $this->fedContactId);
        //copy master_system entries
        $contactPdo->copyMasterSystemEntry($this->fedContactId, $newFedContactId);
        //copy sf_guard_user_entries
        $contactPdo->copySfGuardUserEntry($this->federationId, $this->fedContactId, $newFedContactId);
        //copy master_federation table entry
        $contactPdo->copyMasterFederationEntry($this->federationId, $this->fedContactId, $newFedContactId);
        $contactIdsArr = array('newFedContactId' => $newFedContactId);

        //update contact entry corresponding to club
        $contactObj = $this->em->find('CommonUtilityBundle:FgCmContact', $this->contactId);
        $clubObj = $this->em->getReference('CommonUtilityBundle:FgClub', $this->clubId);
        $contactObj->setMainClub($clubObj);
        $contactObj->setCreatedClub($clubObj);
        $fedContactObj = $this->em->getReference('CommonUtilityBundle:FgCmContact', $newFedContactId);
        $contactObj->setFedContact($fedContactObj);
        if ($this->subfederationId != 0) {
            //check whether contact is shared in another club under this sub-federation
            $isSharedInClubsUnderSubfed = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->isContactSharedInMultipleClubsUnderASubFed($this->subFedContactId);
            if ($isSharedInClubsUnderSubfed) {
                $newSubFedContactId = $this->em->getRepository('CommonUtilityBundle:FgCmContact')->copyContactEntryInFederation($this->clubId, $this->subFedContactId, $newFedContactId, 'sub_federation');
                $contactPdo->copySfGuardUserEntry($this->subfederationId, $this->subFedContactId, $newSubFedContactId);
                $contactPdo->copyMasterFederationEntry($this->subfederationId, $this->subFedContactId, $newSubFedContactId, 'sub_federation');
                $contactIdsArr['newSubFedContactId'] = $newSubFedContactId;

                $subfedContactObj = $this->em->find('CommonUtilityBundle:FgCmContact', $newSubFedContactId);
                $contactObj->setSubfedContact($subfedContactObj);
            } else {
                //update fed_contact_id of sub_fed_obj
                $subfedContactObj = $this->em->find('CommonUtilityBundle:FgCmContact', $this->subFedContactId);
                $subfedContactObj->setFedContact($fedContactObj);
                $this->em->persist($subfedContactObj);

                $contactIdsArr['newSubFedContactId'] = $this->subFedContactId;
            }
        }
        $this->em->persist($contactObj);
        $this->em->flush();

        $duplicateContactDetails = new DuplicateContactDetails($this->container, $this->container->get('database_connection'), $this->contactId, $this->fedContactId, $this->subFedContactId, $newFedContactId, $newSubFedContactId, $this->clubId);
        $duplicateContactDetails->generateDuplicateAssignments('fed');
        $duplicateContactDetails->generateDuplicateAssignmentLogs('fed');
        $duplicateContactDetails->generateDuplicateContactLogEntries('fed');
        $duplicateContactDetails->generateDuplicateConectionLogEntries();
        $duplicateContactDetails->generateDuplicateMembershipLogHistoryEntries('fed');

        //for deleting the federation assignments (role and function log will be copied only if copy of contact is not made in that level)
        $clubDetails = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subfederationId, 'clubType' => $this->clubType, 'defSysLang' => $this->club->get('default_system_lang'), 'defaultClubLang' => $this->club->get('default_lang'));
        $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->deleteFederationAssignmentOfContact($clubDetails, $this->contactId, $this->container, array('workgroup' => $this->container->get('translator')->trans('WORKGROUPS')), true, 'federation', 0);
        if ($newSubFedContactId != '') {
            $duplicateContactDetails->generateDuplicateAssignments('sub-fed');
            $duplicateContactDetails->generateDuplicateAssignmentLogs('sub-fed');
            $duplicateContactDetails->generateDuplicateContactLogEntries('sub-fed');
            $duplicateContactDetails->generateDuplicateMembershipLogHistoryEntries('sub-fed');
        }
        //for deleting the sub-federation assignments only when not shared in any clubs under that sub-federation or when sub-federation entry is copied
        if (($newSubFedContactId != '') || ($this->subfederationId != 0 && !$isSharedInClubsUnderSubfed)) {
            $insertRoleFunctionLog = ($newSubFedContactId != '') ? 0 : 1;
            $clubDetails = array('clubId' => $this->clubId, 'federationId' => $this->federationId, 'subFederationId' => $this->subfederationId, 'clubType' => $this->clubType, 'defSysLang' => $this->club->get('default_system_lang'), 'defaultClubLang' => $this->club->get('default_lang'));
            $this->em->getRepository('CommonUtilityBundle:FgRmRoleContact')->deleteFederationAssignmentOfContact($clubDetails, $this->contactId, $this->container, array('workgroup' => $this->container->get('translator')->trans('WORKGROUPS')), true, 'sub_federation', $insertRoleFunctionLog);
        }

        //update main club of other club contact entries also
        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->updateMainClubOfExistingContactEntries($this->fedContactId, $this->clubId);

        return $contactIdsArr;
    }

    /**
     * This function is used to remove fed membership before archiving a contact.
     *
     * @param int  $contactId   Contact id
     * @param date $leavingDate Leaving date
     *
     * @return bool $success true/false
     */
    public function removeFedMembershipBeforeArchiving($contactId, $leavingDate)
    {
        $isFedMembershipPending = $this->getContactDetails($contactId);
        if (($isFedMembershipPending && $this->contactData['oldFedMembershipId'] != '') || (!$isFedMembershipPending && $this->oldMembershipId != '')) {
            $this->membershipProcess = 'remove';
            $this->leavingDate = $leavingDate;
            $success = $this->removeFedMembership(1);

            return $success;
        }
    }

    /**
     * This function is used to archive a contact on removing fed membership with C.2 mandatory.
     */
    private function archiveContact()
    {
        $nowdate = strtotime(date('Y-m-d H:i:s'));
        $dateToday = date('Y-m-d H:i:s', $nowdate);
        $insert .= "( '" . $this->contactId . "','" . $this->clubId . "','" . $this->loggedContactId . "','" . $dateToday . "'),";
        $teamTerminology = ucfirst($this->container->get('fairgate_terminology_service')->getTerminology('Team', $this->container->getParameter('singular')));
        $clubHeirarchy = $this->club->get('clubHeirarchy');

        $this->em->getRepository('CommonUtilityBundle:FgCmContact')->archiveContact($this->contactId, $this->loggedContactId, $insert, $dateToday, $this->clubId, $this->container->getParameter('system_field_firstname'), $this->container->getParameter('system_field_lastname'), $this->container->getParameter('system_field_companyname'), $this->container->getParameter('system_field_dob'), $teamTerminology, $clubHeirarchy, $this->club->get('type'), $this->container->getParameter('system_field_primaryemail'), $this->container->getParameter('system_field_salutaion'), $this->container->getParameter('system_field_gender'), $this->container->getParameter('system_field_corress_lang'), date('Y-m-d H:i:s'));
    }

    /**
     * This function is used to discard pending fed membership applications on removing fed-membership.
     */
    private function handlePendingFedMembershipApplications()
    {
        //check whether there is any pending fedmembership application for contact from this club
        $pendingApplnConfirmId = $this->em->getRepository('CommonUtilityBundle:FgCmFedmembershipConfirmationLog')->getPendingApplications($this->fedContactId, $this->clubId);
        if ($pendingApplnConfirmId != 0) {
            //discard the fed membership application
            $fedApplication = new FedMemApplication($this->container);
            $fedApplication->discardFedMembership($pendingApplnConfirmId);
        }
    }
}
