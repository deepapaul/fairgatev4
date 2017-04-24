<?php

namespace Common\UtilityBundle\Repository\Message;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgMessage;
use Common\UtilityBundle\Util\FgSettings;
use Common\FilemanagerBundle\Util\FileChecking;
/**
 * FgMessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgMessageRepository extends EntityRepository {

     /**
     * Method to insert the message details
      * 
     * @param array $messageDetailArray 
      * 
     * @return int
     */
    public function insertMessageStep1($messageDetailArray) {
        
        $messageObj = new FgMessage();
        $messageObj->setClub($messageDetailArray['clubobj']);
        $messageObj->setSenderEmail($messageDetailArray['senderemail']);
        $messageObj->setMessageType($messageDetailArray['messagetype']);
        $messageObj->setStep($messageDetailArray['step']);
        $messageObj->setGroupType($messageDetailArray['grouptype']);
        $messageObj->setCreatedAt(new \DateTime("now"));
        $messageObj->setCreatedBy($messageDetailArray['createdby']);
        if(isset($messageDetailArray['isDraft'])) {           
            $messageObj->setIsDraft($messageDetailArray['isDraft']);
        } else {             
            $messageObj->setIsDraft(1);
        }
        if($messageDetailArray['subject']) {
            $messageObj->setSubject($messageDetailArray['subject']);
        }
        if(isset($messageDetailArray['parentMsgId'])) {
            $messageObj->setParentId($messageDetailArray['parentMsgId']);
        }
               
        $this->_em->persist($messageObj);
        $this->_em->flush();
        
        return $messageObj->getId();
    }
    
    /**
     * Method to update the message details
     * 
     * @param array $messageDetailArray 
     * 
     * @return int
     */
    public function updateMessageStep1($messageDetailArray) {
        
       $messageObj = $this->_em->getRepository('CommonUtilityBundle:FgMessage')->find($messageDetailArray['messageid']);
       $messageObj->setSenderEmail($messageDetailArray['senderemail']);
       $messageObj->setMessageType($messageDetailArray['messagetype']);
       $messageObj->setGroupType($messageDetailArray['grouptype']);
       
       $this->_em->persist($messageObj);
       $this->_em->flush();
       
       return ;
    }
    
     /**
     * Function to get the message details by Id
      * 
     * @param int $messageId 
      * 
     * @return int
     */
    public function getMessageById($messageId,$clubId) {
        
        $qb = $this->createQueryBuilder('m')
                    ->select('m.id,IDENTITY(m.club) AS club,m.senderEmail,m.messageType,m.groupType,m.subject,m.step,m.isDraft,m.parentId,m.messageType,IDENTITY(m.createdBy) AS createdBy')
                    ->where('m.id=:messageid')
                ->andWhere('m.club=:clubId')
                    ->setParameters(array('messageid'=> $messageId,'clubId'=>$clubId));
        
        $result = $qb->getQuery()->getOneOrNullResult();
        
        return $result;
    }
    
    
    /**
     * Method to update the message details
     * 
     * @param array $messageDetailArray 
     * 
     * @return void
     */
    public function updateMessageStep2($messageDetailArray) {
        
        $messageObj = $this->_em->getRepository('CommonUtilityBundle:FgMessage')->find($messageDetailArray['id']);
        $messageObj->setSubject($messageDetailArray['subject']);
        $messageObj->setUpdateBy($messageDetailArray['createdby']);
        
        if(isset($messageDetailArray['step']))
            $messageObj->setStep($messageDetailArray['step']);

        $this->_em->persist($messageObj);
        $this->_em->flush();
        
        return;
    }

    /**
     * Method to add reply to a message
     * 
     * @param string $message             Message Content
     * @param int    $messageId           Parent Message Id 
     * @param int    $contactId           Contact Id 
     * @param int    $receiversCount      Receiver's count(excluding sender)
     * @param array  $uploadedAttachments Attachment names of message (filenames after replace single quotes and appending 1,2,3 ..))
     * @param array  $attachmentsSizes    Size of attachments
     * 
     * @return array of new messageId and dataId
     */
    public function addReply($message, $messageId, $contactId, $receiversCount, $uploadedAttachments, $attachmentsSizes, $container) {
        $messageObj = $this->find($messageId);
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $parentMessageObj = $this->_em->getRepository('CommonUtilityBundle:FgMessage')->findBy(array("parentId" => $messageId, "createdBy" => $contactId));        
        $isParentMsgExist = ($parentMessageObj) ? true : false;
        if($messageObj) {
            // renaming filename by removing single quotes and space by (-)
            $messageType = $messageObj->getMessageType();
            $groupType = $messageObj->getGroupType();
            if( $messageType === "GROUP" || 
                    ( $messageType === "PERSONAL" && $groupType === "CONTACT" && $receiversCount == "1" ) ||
                    ($messageType === "PERSONAL" && $isParentMsgExist === true ) ) {                        
                $return['messageId'] = $messageId;
                //insert to fg_message_data
                $messageDataArray = array("messageObj" => $messageObj, "contactObj" => $contactObj, "message" => $message );
                $messageDataObj = $this->insertMessageData($messageDataArray);
                $return['dataId'] = $messageDataObj->getId();
                $return['time'] = $messageDataObj->getUpdatedAt()->format('H:i');
                $return['subject'] = $messageObj->getSubject();
                $return['senderEmail'] = $messageObj->getSenderEmail();
                
                //insert the attachements of the data                
                if(count($uploadedAttachments) > 0){                   
                    $this->_em->getRepository('CommonUtilityBundle:FgMessageAttachments')->insertMessageAttachment($messageDataObj, $uploadedAttachments, $attachmentsSizes);
                }
                
                //update fg_message
                $messageObj->setUpdateBy($contactObj);
                $messageObj->setUpdatedAt($messageDataObj->getUpdatedAt());
                $this->_em->flush();
                
                //update fg_message_receivers
                $msgReceiverObjs = $this->_em->getRepository('CommonUtilityBundle:FgMessageReceivers')->findByMessage($messageId);
                foreach($msgReceiverObjs as $msgReceiverObj) {
                    $receiverId = $msgReceiverObj->getContact()->getId();
                    if($receiverId != $contactId) {
                        $msgReceiverObj->setUnreadCount( $msgReceiverObj->getUnreadCount()+ 1 );
                        $msgReceiverObj->setIsDeleted( 0 );
                    }
                }
                $this->_em->flush();
                
            } else {  //case PERSONAL
                //insert to fg_message                    
                $newMessageArray = array("clubobj" => $messageObj->getClub(),
                                            "senderemail" => $messageObj->getSenderEmail(),
                                            "messagetype" => "PERSONAL",
                                            "grouptype" => "CONTACT",
                                            "step" => $messageObj->getStep(),
                                            "isDraft" => 0,
                                            "subject" => $messageObj->getSubject(),
                                            "parentMsgId" => $messageId,
                                            "createdby" => $contactObj
                    );                                                    
                $newMessageId = $this->insertMessageStep1($newMessageArray);
                $newMessageObj = $this->find($newMessageId);
                $return['messageId'] = $newMessageId;
                //insert to fg_message_data
                $messageDataArray = array("messageObj" => $newMessageObj, "contactObj" => $contactObj, "message" => $message, "dateObj" => $newMessageObj->getCreatedAt() );
                $messageDataObj = $this->insertMessageData($messageDataArray);
                $return['dataId'] = $messageDataObj->getId();
                $return['time'] = $messageDataObj->getUpdatedAt()->format('H:i');
                $return['subject'] = $newMessageObj->getSubject();
                $return['senderEmail'] = $newMessageObj->getSenderEmail();
                
                //insert the attachements of the data                
                if(count($uploadedAttachments) > 0){                   
                    $this->_em->getRepository('CommonUtilityBundle:FgMessageAttachments')->insertMessageAttachment($messageDataObj, $uploadedAttachments, $attachmentsSizes);
                }
                
                //insert to fg_message_receivers
                $messageReceiverArray[0] = array("messageObj" => $newMessageObj, "contactObj" => $contactObj);
                $messageReceiverArray[1] = array("messageObj" => $newMessageObj, "contactObj" => $messageObj->getCreatedBy(), "unreadCount" => 0 );
                $this->insertMessageReceivers($messageReceiverArray);
                
                //insert to fg_message_email_fields
                $this->_em->getRepository('CommonUtilityBundle:FgMessageReceivers')->copyEmailFields($messageId, $newMessageId, array($contactId, $messageObj->getCreatedBy()->getId()));
                
                //delete first message from current contact
                $msgReceiverObjTodelete = $this->_em->getRepository('CommonUtilityBundle:FgMessageReceivers')->findOneBy(array("message" => $messageId, "contact" => $contactId));
                $msgReceiverObjTodelete->setIsDeleted(1);
                $this->_em->flush();
            }
        }
        
        return $return;
    }        
    
    /**
     * Method to insert message data
     * 
     * @param array $messageDataArray message data details to insert
     * 
     * @return \Common\UtilityBundle\Entity\FgMessageData
     */
    public function insertMessageData($messageDataArray) {        
        $messageDataObj = new \Common\UtilityBundle\Entity\FgMessageData();
        $messageDataObj->setMessage2($messageDataArray['messageObj']);
        $messageDataObj->setSender($messageDataArray['contactObj']);
        $messageDataObj->setMessage($messageDataArray['message']);
        if($messageDataArray['dateObj']) {
            $messageDataObj->setUpdatedAt($messageDataArray['dateObj']);  
        } else {
            $messageDataObj->setUpdatedAt(new \DateTime("now"));  
        }                      

        $this->_em->persist($messageDataObj);
        $this->_em->flush();
        
        return $messageDataObj;
    }
    
    /**
     * Method to insert message receiver
     * 
     * @param two dimensional array $messageDatasArray message details to insert     
     */
    private function insertMessageReceivers($messageDatasArray) {               
        
        foreach($messageDatasArray as $messageDataArray) {   
            $messageReceiversObj = new \Common\UtilityBundle\Entity\FgMessageReceivers();
            $messageReceiversObj->setContact($messageDataArray['contactObj']);
            $messageReceiversObj->setMessage($messageDataArray['messageObj']);

            $unreadCount = isset($messageDataArray['unreadCount'])? $messageDataArray['unreadCount'] : 0;
            $messageReceiversObj->setUnreadCount($unreadCount);  

            $isDeleted = isset($messageDataArray['isDeleted'])? $messageDataArray['isDeleted'] : 0;
            $messageReceiversObj->setIsDeleted($isDeleted);  

            $isNotificationEnabled = isset($messageDataArray['isNotificationEnabled'])? $messageDataArray['isNotificationEnabled'] : 1;
            $messageReceiversObj->setIsNotificationEnabled($isNotificationEnabled);                                       

            $this->_em->persist($messageReceiversObj);            
        }        
        $this->_em->flush();
    }
    
    /**
     * Method to update the message details
     * 
     * @param array $messageDetailArray 
     * 
     * @return void
     */
    public function updateMessageSending($messageId) {
        
        $messageObj = $this->_em->getRepository('CommonUtilityBundle:FgMessage')->find($messageId);
        $messageObj->setIsDraft('0');
        $messageObj->setStep('3');

        $this->_em->persist($messageObj);
        $this->_em->flush();
        
        return;
    }
    
    /**
     * Method to get count of message datas of a message(including parent message) excluding a particular contact
     *  
     * @param int $messageId Message Id
     * @param int $contactId ContactId
     * 
     * @return int messagesCount
     */
    public function getMessagesCount($messageId, $contactId) {
        
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $dateFormat = FgSettings::getMysqlDateFormat();
        $qb = $this->createQueryBuilder('M')  
                ->select(" (COUNT(DISTINCT D.id)) as messagesCount, IDENTITY(M.createdBy) as createdBy, DATE_FORMAT( R.readAt,'$dateFormat' ) as readAt")
                ->innerJoin("CommonUtilityBundle:FgMessageData", "D", "WITH", "( D.message2 = M.id OR D.message2 = M.parentId) ")
                ->innerJoin("CommonUtilityBundle:FgCmContact", "C", "WITH", " D.sender = C.id " )
                ->innerJoin("CommonUtilityBundle:FgMessageReceivers","R", "WITH","M.id = R.message ")
                ->where('( M.id = :messageId AND C.id != :contactId )')
                ->setParameters(array(":messageId" => $messageId, ":contactId" => $contactId ));
        $result = $qb->getQuery()->getArrayResult();
        
        return $result;
    }
    
    /**
     * Method to get total count of drafts of a contact
     * 
     * @param int    $contactId  ContactId
     * @param int    $clubId     clubId

     * @return int 
     */
    public function getContactDraftsCount($contactId, $clubId) {              
        $qb = $this->createQueryBuilder('M')
                    ->select('(COUNT(DISTINCT M.id)) as totalCount')
                    ->where('M.createdBy = :contactId AND (M.isDraft = 1) ')
                    ->andWhere('M.club = :club')
                    ->setParameters(array('contactId' => $contactId, 'club' => $clubId));
        
       $result = $qb->getQuery()->execute();
       
       return $result[0]['totalCount'];       
    }
    
    /**
     * Function to delete the messages and related details on delete from draft
     * 
     * @param int $messageId 
     * 
     * @return int
     */
    public function deleteMessage($messageId) {
        $id =  preg_replace('/[^0-9\-]/', '', $messageId);
        $qb = $this->createQueryBuilder('m')
                    ->delete()
                    ->where('m.id=:messageid')
                    ->setParameter('messageid', $id);
       $result = $qb->getQuery()->execute();
       return $result;
    }
    
    /**
     * Method to get count of messages of a contact
     *
     * @param int    $contactId        contactId
     * @param int    $clubId           clubId
     * @param int    $unreadCountFlag  unreadCountFlag
     *
     * @return int
    */
    public function getContactMessagesCount($contactId, $clubId, $unreadCountFlag = 0) {
        $qb = $this->createQueryBuilder('M');
                    $qb->select("count(DISTINCT M.id) as totalCount");
                    $qb->join("CommonUtilityBundle:FgMessageReceivers", "R", "WITH", "M.id = R.message");
                    $qb->join("CommonUtilityBundle:FgMessageData", "D", "WITH", "M.id = D.message2");
                    $qb->join("CommonUtilityBundle:FgCmContact", "CON", "WITH", "CON.id = M.createdBy");
                    $qb->where('R.contact = :contactId');
                    if($unreadCountFlag == 1){
                        $qb->andWhere('R.unreadCount > 0 OR R.readAt is NULL');
                    }
                    $qb->andWhere("M.isDraft != 1 AND M.club = :clubId AND (R.isDeleted IS NULL OR R.isDeleted = '0')");
                    $qb->setParameters(array("contactId" => $contactId, "clubId" => $clubId ));
                   
        $result = $qb->getQuery()->getArrayResult();
        
        return $result[0]['totalCount'];
    }
}