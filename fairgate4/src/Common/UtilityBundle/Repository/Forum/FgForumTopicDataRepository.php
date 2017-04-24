<?php

namespace Common\UtilityBundle\Repository\Forum;

use Doctrine\ORM\EntityRepository;
use \Common\UtilityBundle\Entity\FgForumTopicData;

/**
 * FgCmNotesRepository
 *
 * @author 
 */
class FgForumTopicDataRepository extends EntityRepository {

    
    /**
     * Function to save/update forum topic
     *
     * @param object  $forumObj   forumObj
     * @param array     $forumArray forumArray
     * @param obj     $contactobj logged in contact id obj
     * 
     * @return null
     */
    public function saveNewTopicData($forumObj,$forumArray,$contactobj) {
       
            $forumObjData = new FgForumTopicData();
            $forumObjData->setForumTopic($forumObj);
            $forumObjData->setPostContent($forumArray['forum-post-text']);
            $forumObjData->setUniquePostId(1);
            $forumObjData->setCreatedAt(new \DateTime("now"));
            $forumObjData->setCreatedBy($contactobj);
            
            $this->_em->persist($forumObjData);
            $this->_em->flush();
        
    }
    
    /**
     * Function to remove forum topic data
     *
     * @param int     $topicContentId Topic content data id
     * @param int     $contactId Topic id
     * 
     * @return null
     */
    public function removeTopicContent($topicContentId, $contactId) {
        $object = $this->find($topicContentId);
        $forumTopicObj = $object->getForumTopic();
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        if($forumTopicObj) {
            $forumTopicObj->setUpdatedAt(new \DateTime("now"));
            $forumTopicObj->setUpdatedBy($contactObj);
        }
        $this->_em->remove($object);
        $this->_em->flush();
    }

}

