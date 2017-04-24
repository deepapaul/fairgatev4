<?php

/**
 * MessagePdo
 *
 * This class is used for handling message section.
 *
 * @package    CommonUtilityBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */

namespace Common\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgSettings;

/**
 * Description of MessagePdo
 *
 * @author pitsolutions.ch
 */
class MessagePdo {

    /**
     * Connection variable
     */
    public $conn;

    /**
     * Container variable
     */
    public $container;

    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container) {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Method to get messages of a contact to list in the inbox
     * @param int    $contactId        contactId
     * @param int    $clubId           clubId
     * @param string $profileImgField  Profileimage system field name
     * @param string $companyLogoField Company logo field
     * @param string $masterTable      Master table of club
     * @param int    $offset           Offset value
     * @param int    $limit            Limit of records to show
     * @return array
     */
    public function geMessagesOfContact($contactId, $clubId, $profileImgField, $companyLogoField, $masterTable, $offset=0, $limit=50,$clubType) {

        $onlyCompanyName = 0;
        $contact_field = ($clubType == 'federation') ? 'MAS.fed_contact_id' : 'MAS.contact_id';
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();

        $sql = "SELECT M.id as messageId, M.created_by,R.read_at as readAt,M.subject, contactNameNoSort(M.created_by, $onlyCompanyName) as senderName, CON_C.is_stealth_mode,CON_C.created_club_id as senderCreatedClub,CON_U.created_club_id as updatedCreatedClub, M.created_by,CON_C.fed_contact_id as createdFedId,CON_U.fed_contact_id as updatedFedId, "
                . "DATE_FORMAT( M.created_at,'$datetimeFormat' ) AS createdAt, "
                . "GROUP_CONCAT(DISTINCT D.id order by D.updated_at DESC) as messageContents, "
                . "(COUNT(DISTINCT D.id) - 1) as repliesCount, "
                . "CON_C.is_company as isCompanySender, CON_U.is_company as isCompanyUpdated, "
                . "CON_C.club_id as senderClub, CON_U.club_id as updatedClub, "
                . "CASE WHEN (CON_C.is_company = 1 ) THEN MS_C.$companyLogoField ELSE MS_C.$profileImgField END as senderProfileImg, "
                . "CASE WHEN (CON_U.is_company = 1 ) THEN MS_U.$companyLogoField ELSE MS_U.$profileImgField END as updatedProfileImg, "
                . "CASE WHEN (M.update_by IS NULL OR M.update_by = '') THEN '' ELSE contactNameNoSort(M.update_by, $onlyCompanyName) END as updatedBy, "
                . "CASE WHEN (M.updated_at IS NULL ) THEN '' ELSE DATE_FORMAT( M.updated_at, '$datetimeFormat' ) END as updatedAt, "
                . "CASE WHEN (R.unread_count IS NULL ) THEN '0' ELSE R.unread_count END as unreadCount, "
                . "CASE WHEN (R.is_notification_enabled IS NULL ) THEN '0' ELSE R.is_notification_enabled END as notification, "
                . "CASE WHEN (M.updated_at IS NULL ) THEN M.created_at ELSE M.updated_at END as orderDate  " //FOR order
                . "FROM fg_message M "
                . "JOIN fg_message_receivers R ON M.id = R.message_id "
                . "JOIN fg_message_data D ON M.id = D.message_id "
                . "JOIN fg_cm_contact CON_C ON CON_C.id = M.created_by "
                . "LEFT JOIN fg_cm_contact CON_U ON CON_U.id = M.update_by "
                . "JOIN master_system MS_C ON MS_C.fed_contact_id = CON_C.fed_contact_id "
                . "LEFT JOIN $masterTable MAS ON $contact_field = CON_C.fed_contact_id "
                . "LEFT JOIN master_system MS_U ON MS_U.fed_contact_id = CON_U.fed_contact_id "
                . "WHERE R.contact_id = :contactId  "
                . "AND M.is_draft != 1 AND M.club_id = :clubId AND (R.is_deleted IS NULL OR R.is_deleted = '0') "
                . "GROUP BY M.id "
                . "ORDER BY orderDate DESC, M.id DESC "
                . "LIMIT $offset, $limit";
        $result = $this->conn->fetchAll($sql, array(":contactId" => $contactId, ":clubId" => $clubId ));

        return $result;
    }

    /**
     * Method to get conversation thread
     * @param int    $messageId        messadeId(primary id fg_message)
     * @param int    $clubId           Club Id
     * @param int    $contactId        Contact Id
     * @param string $profileImgField  Profileimage system field name
     * @param string $companyLogoField Company logo field
     * @param int    $offset           Offset for pagination
     * @param int    $limit            Limit for pagination
     * @param string $currentDateTime  Current datetime
     * return array
     */
    public function geConversation($messageId, $clubId, $contactId, $profileImgField, $companyLogoField, $offset, $limit, $currentDateTime) {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $timeFormat = FgSettings::getMysqlTimeFormat();
        $sql = "SELECT D.*, "
                . "CASE WHEN (CON.is_company = 1 ) THEN contactNameNoSort(D.sender_id,1) ELSE contactNameNoSort(D.sender_id,0) END as senderName,CON.created_club_id as senderCreatedClub,"
                . "DATE_FORMAT( D.updated_at,'$dateFormat' ) AS msgDate, DATE_FORMAT( D.updated_at,'$timeFormat' ) AS msgTime, "
                . "CON.is_company as isCompanySender, CON.club_id as senderClub,"
                . "GROUP_CONCAT(DISTINCT A.file order by A.id ASC) as attachments, "
                . "CASE WHEN (CON.is_company = 1 ) THEN MS.$companyLogoField ELSE MS.$profileImgField END as senderProfileImg "
                . "FROM fg_message M "
                . "INNER JOIN fg_message_data D ON (M.id = D.message_id OR M.parent_id = D.message_id) "
                . "INNER JOIN fg_cm_contact CON ON CON.id = D.sender_id "
                . "INNER JOIN master_system MS ON MS.fed_contact_id = CON.fed_contact_id "
                . "INNER JOIN fg_message_receivers R ON M.id = R.message_id "
                . "LEFT JOIN fg_message_attachments A ON A.message_data_id = D.id "
                . "WHERE M.id = :messageId AND M.club_id = :clubId "
                . "AND R.contact_id = :contactId AND M.is_draft != 1 "
                . "AND (R.is_deleted IS NULL OR R.is_deleted = '0') "
                . "AND D.updated_at <= :currentDateTime "
                . "GROUP BY D.id "
                . "ORDER BY D.updated_at DESC, D.id DESC "
                . "LIMIT $offset, $limit ";
        $result = $this->conn->fetchAll($sql, array(":messageId" => $messageId, ":clubId" => $clubId, ":contactId" => $contactId, ":currentDateTime" => $currentDateTime ));

        return $result;
    }

    /**
     * Method to get headers of conversation
     * @param int    $messageId           Message Id
     * @param int    $clubId              Club Id
     * @param int    $contactId           Contact Id
     * @param string $groupTitle          Subtitle for group message
     * @param string $personalTitle       Subtitle for personal message
     * @param string $executiveBoardTitle Terminology term for executive board
     * return array
     */
    public function geConversationHeaders($messageId, $clubId, $contactId, $groupTitle, $personalTitle, $executiveBoardTitle ) {
        $sql = "SELECT M.id,R.id as receiversId,M.subject, M.group_type, M.message_type, GROUP_CONCAT(DISTINCT R.contact_id) as receivers, "
                . "(COUNT(DISTINCT R.contact_id)) as receiversCount, "
                . "CASE "
                . "  WHEN M.group_type = 'CONTACT' THEN "
                . "    GROUP_CONCAT(DISTINCT contactNameNoSort(R.contact_id, 1) SEPARATOR ', ') "
                . "  ELSE "
                . "    GROUP_CONCAT(DISTINCT CASE WHEN ROLE.is_executive_board = 1 THEN '$executiveBoardTitle' ELSE ROLE.title END SEPARATOR ', ' ) "
                . "END as receiverNames, "
                . "CASE "
                . "  WHEN M.group_type = 'CONTACT' THEN "
                . "    CASE "
                . "      WHEN (COUNT(DISTINCT R.contact_id)) = '1' THEN '$groupTitle' "
                . "      ELSE "
                . "        CASE "
                . "          WHEN M.message_type = 'PERSONAL' THEN '$personalTitle' "
                . "          ELSE '$groupTitle' "
                . "        END "
                . "    END "
                . "  ELSE "
                . "    CASE "
                . "      WHEN M.message_type = 'PERSONAL' THEN '$personalTitle' "
                . "      ELSE '$groupTitle' "
                . "    END "
                . "END as subtitle, "
                . "M.created_by as createdBy, "
                . "CASE WHEN (M.message_type = 'PERSONAL' AND (COUNT(DISTINCT R.contact_id)) = '1') THEN contactNameNoSort(R.contact_id, 1) "
                . "     ELSE CASE WHEN M.message_type = 'PERSONAL' THEN contactNameNoSort(M.created_by, 1) END "
                . " END as messageToContact, "
                . "(SELECT (COUNT(DISTINCT D.id)) FROM fg_message MSG INNER JOIN fg_message_data D ON (MSG.id = D.message_id OR MSG.parent_id = D.message_id) WHERE MSG.id =:messageId  ) AS totalMessages, "
                . "(SELECT CASE WHEN (REC.is_deleted IS NULL ) THEN NULL ELSE REC.is_deleted END FROM fg_message MSG2 INNER JOIN fg_message_receivers REC ON (MSG2.id = REC.message_id AND REC.contact_id = :contactId) WHERE MSG2.id =:messageId GROUP BY MSG2.id ) AS isDeleted "
                . "FROM fg_message M "
                . "LEFT JOIN fg_message_receivers R ON (M.id = R.message_id AND R.contact_id != :contactId ) "
                . "LEFT JOIN fg_message_group G ON G.message_id = M.id "
                . "LEFT JOIN fg_rm_role ROLE ON G.role_id = ROLE.id "
                . "WHERE M.id = :messageId AND M.club_id = :clubId "
                . "AND M.is_draft != 1 ";
        $result = $this->conn->fetchAll($sql, array(":messageId" => $messageId, ":clubId" => $clubId, ":contactId" => $contactId ));

        return $result[0];
    }

     /**
     * Method to get unread messages of contact (from dashboard)
     * @param int    $messageId        messadeId(primary id fg_message)
     * @param int    $clubId           Club Id
     * @param int    $contactId        Contact Id
     * @param string $profileImgField  Profileimage system field name
     * @param string $companyLogoField Company logo field
     * @param string $clubTable        Club master table
     *
     * return array
     */
    public function geUnreadMessagesOfContact($contactId, $clubId, $profileImgField, $companyLogoField, $clubTable='',$clubType) {

        $dateFormat = FgSettings::getMysqlDateFormat();
        $timeFormat = FgSettings::getMysqlTimeFormat();
        $mainfgcontactIdField = 'mc.contact_id';
        $contIdField = 'id';
        switch ($clubType) {
            case 'federation':
                $fromContact = " INNER JOIN fg_cm_contact CON_C on  CON_C.fed_contact_id =  D.sender_id ";
                $mainfgcontactIdField = 'mc.fed_contact_id';
                $contIdField = 'fed_contact_id';
                break;
            case 'sub_federation':
                $fromContact = " INNER JOIN fg_cm_contact CON_C on CON_C.subfed_contact_id = D.sender_id ";
                $contIdField = 'subfed_contact_id';
                break;
            default:
                $fromContact = " INNER JOIN fg_cm_contact  CON_C on CON_C.id =  D.sender_id ";
        }
        $sql = "SELECT D.message, DATE_FORMAT( D.updated_at,'$dateFormat' ) AS msgDate, DATE_FORMAT( D.updated_at,'$timeFormat' ) AS msgTime, "
                . "M.*, contactNameNoSort(D.sender_id, 0) as senderName, R.is_deleted, "
                . "GROUP_CONCAT(DISTINCT D.id order by D.updated_at DESC) as messageContents, "
                . "(COUNT(DISTINCT D.id) - 1) as repliesCount, "
                . "CON_C.is_company as isCompanySender, CON_U.is_company as isCompanyUpdated, "
                . "CON_C.club_id as senderClub, CON_U.club_id as updatedClub, "
                . "CASE WHEN (CON_C.is_company = 1 ) THEN MS_C.$companyLogoField ELSE MS_C.$profileImgField END as senderProfileImg, "
                . "CASE WHEN (CON_U.is_company = 1 ) THEN MS_U.$companyLogoField ELSE MS_U.$profileImgField END as updatedProfileImg, "
                . "CASE WHEN (M.update_by IS NULL OR M.update_by = '') THEN '' ELSE contactName(M.update_by) END as updatedBy, "
                . "CASE WHEN (M.updated_at IS NULL ) THEN '' ELSE M.updated_at END as updatedAt, "
                . "CASE WHEN (M.update_by IS NULL OR M.update_by = '') THEN M.created_by ELSE M.update_by END as contactId, "
                . "R.unread_count as unreadCount, R.is_notification_enabled as notification,"
                . "CON_C.is_stealth_mode AS stealthmode,"
                . "CASE WHEN (M.updated_at IS NULL ) THEN M.created_at ELSE M.updated_at END as orderDate  " //FOR order
                . "FROM fg_message M "
                . "JOIN fg_message_receivers R ON M.id = R.message_id "
                . "JOIN fg_message_data D ON M.id = D.message_id "
                . "LEFT JOIN fg_cm_contact CON_UP ON CON_UP.id = D.sender_id "
                . "LEFT JOIN fg_cm_contact CON_UPP ON CON_UPP.id = M.update_by "
                . "JOIN master_system MS_C ON MS_C.fed_contact_id = CON_UP.fed_contact_id "
                . "LEFT JOIN master_system MS_U ON MS_U.fed_contact_id = CON_UPP.fed_contact_id "
                . "LEFT JOIN $clubTable mc ON $mainfgcontactIdField = D.sender_id "
                    . "$fromContact"
               // . "JOIN fg_cm_contact CON_C ON CON_C.id = D.sender_id "
                . "LEFT JOIN fg_cm_contact CON_U ON CON_U.$contIdField = M.created_by "
                . "WHERE R.contact_id = :contactId  "
                . "AND M.is_draft != 1 AND M.club_id = :clubId AND (R.is_deleted IS NULL OR R.is_deleted = '0') "
                . "AND (R.unread_count > 0  OR R.read_at IS NULL)"
                . "GROUP BY M.id "
                . "ORDER BY orderDate DESC, M.id DESC";


        $result = $this->conn->fetchAll($sql, array(":contactId" => $contactId, ":clubId" => $clubId ));

        return $result;
    }

    /**
     * Function to save message receivers and email fields before sending message
     * 
     * @param array  $contactsData                  Contact attribute array from message step 3
     * @param array  $attrbutes                     Email attributes
     * @param int    $messageId                     Message id
     * @param array  $messageDetail                 Message detail array
     * @param object $club                          Club object
     * @param int    $contactId                     contact-id
     * 
     * @throws \Common\UtilityBundle\Repository\Pdo\Exception
     */
    public function saveSendingMessage($contactsData,$attrbutes,$messageId,$messageDetail,$club,$contactId){
        $receiverQuery = $emailFieldsQuery =array();
        $groupType = $messageDetail['groupType'];
        $subject=$messageDetail['subject'];
        $from=$messageDetail['senderEmail'];
        array_push($attrbutes, 'parent');
        $conn = $this->conn;
        try {
            foreach ($contactsData as $contactAttrs){
                if($groupType != 'CONTACT' && $contactId != $contactAttrs['id']){
                    $readAt = ($contactAttrs['id'] == $contactId)?"'".date('Y-m-d H:i:s')."'":'NULL';
                    $receiverQuery[$contactAttrs['id']]="({$contactAttrs['id']},{$messageId},0,0,1,$readAt)";
                }                
                $emailTemplate = $contactAttrs['template'];
                                
                foreach($attrbutes as $key=>$emailAttr){
                    //add email field if attribute is selected and email is not null
                    if($contactAttrs[$emailAttr.'_checked']==1 && !empty($contactAttrs[$emailAttr])){
                        $attrType= 'SELF';
                        if($emailAttr=='parent'){
                            $attrType= 'PARENT';
                            $parentEmails =  explode(',', $contactAttrs[$emailAttr]);
                            $emailAttr='3';
                            foreach($parentEmails as $parentEmail){
                                $this->em->getRepository('CommonUtilityBundle:FgNotificationSpool')->addNotificationEntries($parentEmail,$emailTemplate,$subject,$from);
                            }
                        } else {
                            $this->em->getRepository('CommonUtilityBundle:FgNotificationSpool')->addNotificationEntries($contactAttrs[$emailAttr],$emailTemplate,$subject,$from);
                        }
                        $emailFieldsQuery[]="(SELECT $emailAttr,'$attrType',mr.id FROM fg_message_receivers mr where mr.message_id=$messageId AND mr.contact_id={$contactAttrs['id']} )";
                    }
                }
            }
            // for inserting the senders email fields with default values as 'primary_email' & 'SELF'
            $emailFieldsQuery[]="(SELECT '3','SELF',mr.id FROM fg_message_receivers mr where mr.message_id=$messageId AND mr.contact_id=$contactId )";

            $conn->beginTransaction();
            if(count($receiverQuery)>0){
                $conn->executeQuery("INSERT INTO fg_message_receivers (contact_id,message_id,is_deleted,unread_count,is_notification_enabled,read_at) VALUES ".  implode(',', $receiverQuery));
            }
            if(count($emailFieldsQuery)>0){
                $emailQuery="INSERT INTO fg_message_email_fields (attribute_id,attribute_type,receivers_id) ";
                foreach($emailFieldsQuery as $emailQueryValues) {
                    $conn->executeQuery($emailQuery.$emailQueryValues);
                }
            }
            $conn->commit();
        } catch (Exception $ex) {
                $conn->rollback();
                throw $ex;
            }
    }

    /**
     * Method to get drafts of contact
     * @param int    $contactId           contactId
     * @param int    $clubId              clubId
     * @param string $executiveBoardTitle Terminology term for executive board
     * @param int    $offset              Offset value
     * @param int    $limit               Limit of records to show
     * @return array
     */
    public function getDraftsOfContact($contactId, $clubId, $executiveBoardTitle, $offset=0, $limit=50) { 
        $dateFormat = FgSettings::getMysqlDateFormat();    
        $sql = "SELECT M.id as messageId, M.step, "
                . "CASE WHEN (M.subject IS NULL ) THEN '' ELSE M.subject END as subject, "
                . "DATE_FORMAT( M.created_at,'$dateFormat' ) AS createdAt, "
                . "CASE "
                . "  WHEN M.group_type = 'CONTACT' THEN "
                . "    GROUP_CONCAT(DISTINCT contactNameNoSort(R.contact_id, 0)  SEPARATOR ', ') "
                . "  ELSE "
                . "    GROUP_CONCAT(DISTINCT CASE WHEN ROLE.is_executive_board = 1 THEN '$executiveBoardTitle' ELSE ROLE.title END  SEPARATOR ', ' ) "
                . "END as receiverNames "
                . "FROM fg_message M "
                . "LEFT JOIN fg_message_receivers R ON ( M.id = R.message_id AND R.contact_id != :contactId ) "
                . "LEFT JOIN fg_message_group G ON G.message_id = M.id "
                . "LEFT JOIN fg_rm_role ROLE ON G.role_id = ROLE.id "
                . "WHERE  M.is_draft = 1 "
                . "AND M.created_by = :contactId  AND M.club_id = :clubId  "
                . "GROUP BY M.id "
                . "ORDER BY M.created_at DESC, M.id DESC "
                . "LIMIT $offset, $limit";
        $result = $this->conn->fetchAll($sql, array(":contactId" => $contactId, ":clubId" => $clubId ));

        return $result;
    }

    /**
     * Method to get array of 'to' emails to send notification on reply to message
     * @param int    $messageId        messageId
     * @param int    $contactId        contactId
     * @param int    $clubId           clubId
     * @param array  $emailFields      Club email fields
     * @param object $contactlistClass contactlistClass
     * @return array  with key as contact id and value as email
     */
    public function getEmailsForNotification($messageId, $contactId, $clubId, $emailFields, $contactlistClass) {
        // To get emailfields values of all contacts in the club
        $emailFieldsString = "`".implode("`,`",$emailFields)."`";
        $fields = explode(",",$emailFieldsString);
        $fields[] = "(SELECT GROUP_CONCAT(s.`3`) FROM master_system s LEFT JOIN fg_cm_linkedcontact lc ON lc.linked_contact_id=s.fed_contact_id AND lc.relation_id=2 AND lc.type='household' AND lc.club_id=".$clubId." WHERE lc.contact_id=ms.fed_contact_id) as parent";
        $contactlistClass->setColumns($fields);
        $contactlistClass->setFrom('*');
        $contactlistClass->setCondition();
        $contactlistClass->addJoin(" inner join fg_message_receivers MSG_REC ON MSG_REC.contact_id = fg_cm_contact.id ");
        $contactlistClass->addCondition(" MSG_REC.message_id = $messageId ");
        $listquery = $contactlistClass->getResult();
        $stmt = $this->conn->executeQuery($listquery);
        $fieldsArray = $stmt->fetchAll(\PDO::FETCH_GROUP);

        //To get comma separrated contacts for each email field
        $contactsOfEmailFields = $this->em->getRepository('CommonUtilityBundle:FgMessageReceivers')->getContactsOfEmailFields($messageId, $emailFields, $contactId);

        $resultArray = array();
        foreach($contactsOfEmailFields as $contactsOfEmailField) {
            $contacts = $contactsOfEmailField['contacts'];
            $attribute  = ($contactsOfEmailField['attributeType'] == "SELF")?  $contactsOfEmailField['attr'] : 'parent';
            if($contacts) {
                foreach(explode(",", $contacts) as $contact) {
                    $resultArray[$contact] = $fieldsArray[$contact][0][$attribute];
                }
            }
        }

        return $resultArray;
    }

    /**
     * Remove contact from a team. It can be a draft contact or a club contact
     * If it is a draft contact we need to remove the draft contact itself.
     * But if it is a club contact we need to
     *
     * @param string $contact   contact ids for removal
     * @param int    $roleId    selected team or workgroup id where the member is belongs to
     * @param int    $roleCatId main team/workgroup category of the club
     * @param int    $clubId    current club id
     * @param int    $contactId current contact id
     *
     * @return array
     */
    public function removeTeamMember($contact, $roleId, $roleCatId, $clubId, $contactId){
        //echo $roleCatId.'<br>'; 4
        //echo $roleId.'<br>';2760
        $sql = "SELECT is_draft, id FROM fg_cm_contact WHERE id IN($contact);";
        $isDraft = $this->conn->fetchAll($sql);
        $delDraftquery = '';
        $delExistingContact = '';
        $currentContactId = $contactId;
        foreach ($isDraft as $key => $val) {
            $contactId = $val['id'];
            //If it is a draft contact we just need to delete the contact entry and the rest of the data will get cascaded.
            if ($val['is_draft'] == 1) {
                    $delDraftquery .= "DELETE FROM fg_cm_contact WHERE id = $contactId;";
            } else { //Need to remove the assignment entry as we ll as insert an entry for confirmation
                $changesQuery = "SELECT count(id) as changeCount FROM fg_cm_change_toconfirm WHERE contact_id = $contactId AND role_id = $roleId AND confirm_status = 'NONE';";
                $data = $this->conn->fetchAll($changesQuery);

                if ($data[0]['changeCount'] >= 1) {
                    $delExistingContact .= "DELETE FROM fg_cm_change_toconfirm WHERE contact_id = $contactId AND role_id = $roleId AND confirm_status = 'NONE';";
                }

                $assignmentQuery = "SELECT crf.function_id as functionId, rc.id as roleContactId FROM fg_rm_category_role_function crf "
                    . "INNER JOIN fg_rm_role_contact rc ON rc.fg_rm_crf_id = crf.id "
                    . "WHERE crf.category_id = $roleCatId AND crf.role_id = $roleId AND crf.club_id = $clubId and rc.contact_id = $contactId;";

                $assignmentData = $this->conn->fetchAll($assignmentQuery);
                if (count($assignmentData) > 0) {
                    $changeSql = "INSERT INTO fg_cm_change_toconfirm (contact_id, role_id, date, changed_by, club_id, type) VALUES ($contactId, $roleId, now(), $currentContactId, $clubId, 'mutation');";
                    //echo $changeSql.'<br>';
                    $this->conn->executeQuery($changeSql);
                    $confirmId = $this->conn->lastInsertId();
                    foreach ($assignmentData as $assignmentKey => $assignmentVal) {
                        $functionId = $assignmentVal['functionId'];
                        $roleContactId = $assignmentVal['roleContactId'];
                        $changeFnSql = "INSERT INTO fg_cm_change_toconfirm_functions (toconfirm_id, function_id, action_type) VALUES ($confirmId, $functionId, 'REMOVED');";

                        //echo $changeFnSql.'<br>';
                        $this->conn->executeQuery($changeFnSql);
                        $roleContactSql = "UPDATE fg_rm_role_contact SET is_removed = 1 WHERE id = $roleContactId;";

                        //echo $roleContactSql.'<br>';
                        $this->conn->executeQuery($roleContactSql);
                    }
                }
            }
        }

        //Deleting draft/existing contacts
        $deleteQuery = $delDraftquery.$delExistingContact;
        if ($deleteQuery !== '') {
            $this->conn->executeQuery($deleteQuery);
        }
    }
    /**
     * Function to get the reciepients names of a message
     *
     * @param int $messageId
     *
     * @return string
     */
    public function getMessageReceiverNames($messageId){
        $result = $this->conn->fetchAll("SELECT contactNameNoSort(mr.contact_id,1) as contacts FROM fg_message_receivers mr LEFT JOIN fg_message m ON m.id=mr.message_id where mr.message_id=$messageId AND m.created_by !=mr.contact_id");

        return $result[0];
    }
}

