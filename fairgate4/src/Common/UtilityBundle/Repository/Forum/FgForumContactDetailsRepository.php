<?php

namespace Common\UtilityBundle\Repository\Forum;

use Doctrine\ORM\EntityRepository;
use \Common\UtilityBundle\Entity\FgForumContactDetails;

/**
 * FgForumContactDetailsRepository
 *
 * @author pitsolutions
 */
class FgForumContactDetailsRepository extends EntityRepository {
    
    /**
     * save contact details on create topic
     * @param object  $contactobj
     * @param object  $forumObj
     * @param boolean $isNotificationEnabled 
     * @param boolean $readAt 
     */
    public function saveContactDetails($contactobj,$forumObj, $isNotificationEnabled, $readAt){
        
        $forumCDObj = new FgForumContactDetails();
        $forumCDObj->setContact($contactobj);
        $forumCDObj->setForumTopic($forumObj);
        if($readAt == true) {           
            $forumCDObj->setReadAt(new \DateTime("now"));
        }        
        $forumCDObj->setIsNotificationEnabled($isNotificationEnabled);        
        $this->_em->persist($forumCDObj);
        $this->_em->flush();
        
    }
    
    /**
     * Method to update read-at field of a topic contact
     * 
     * @param int $topicId   Topic-id
     * @param int $contactId Contact-id
     */
    public function updateReadAt($topicId, $contactId) {
         $q = $this->createQueryBuilder('FC')
                ->select('FC.id as forumContactId')
                ->where('FC.forumTopic =:topicId')   
                ->andWhere('FC.contact =:contactId')
                ->setParameters(array('topicId' => $topicId, 'contactId' => $contactId));
        $result = $q->getQuery()->getArrayResult();
        if($result[0]['forumContactId']) {
            $forumContactObj = $this->find($result[0]['forumContactId']);
            $forumContactObj->setReadAt(new \DateTime("now"));
            $this->_em->flush();
        }
    }
    
    /**
     * Function to update last read time of topic for a contact
     * 
     * @param int $topicId   Topic Id
     * @param int $contactId Contact Id
     */
    public function updateLastReadTimeOfTopic($topicId, $contactId)
    {
        $contactDetail = $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->findOneBy(array('forumTopic' => $topicId, 'contact' => $contactId));
        if ($contactDetail) {
            $contactDetailId = $contactDetail->getId();
            $contactDetailObj = $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->find($contactDetailId);
            $contactDetailObj->setReadAt(new \DateTime("now"));
        } else {
            $contactDetailObj = new \Common\UtilityBundle\Entity\FgForumContactDetails();
            $topicObj = $this->_em->getRepository('CommonUtilityBundle:FgForumTopic')->find($topicId);
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $contactDetailObj->setForumTopic($topicObj);
            $contactDetailObj->setContact($contactObj);
            $contactDetailObj->setReadAt(new \DateTime("now"));
            $contactDetailObj->setIsNotificationEnabled(0);
        }
        $this->_em->persist($contactDetailObj);
        $this->_em->flush();
    }
}

