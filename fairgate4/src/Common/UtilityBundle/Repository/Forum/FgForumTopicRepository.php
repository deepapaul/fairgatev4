<?php

namespace Common\UtilityBundle\Repository\Forum;

use Doctrine\ORM\EntityRepository;
use \Common\UtilityBundle\Entity\FgForumTopic;
use Common\UtilityBundle\Util\FgSettings;

/**
 * FgForumTopicRepository
 *
 * @author pitsolutions
 */
class FgForumTopicRepository extends EntityRepository {

    
    /**
     * Function to save/update forum topic
     *
     * @param array   $forumArray   forum array
     * @param int     $clubId       current club id
     * @param int     $loginContact logged in contact id
     * @param int     $role         role id
     * 
     * @return null
     */
    public function saveNewTopic($forumArray, $clubId,$loginContact,$role) {
        if($forumArray['topic-title'] != '' || $forumArray['forum-post-text'] != '' ){
          
            $contactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($loginContact);
            $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            $roleobj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($role);
            $replies = isset($forumArray['topic-replies'])?$forumArray['topic-replies']:'allowed';
            $sticky = isset($forumArray['topic-sticky'])?$forumArray['topic-sticky']:0;

            $forumObj = new FgForumTopic();
            $forumObj->setClub($clubobj);
            $forumObj->setTitle($forumArray['topic-title']);
            $forumObj->setGroup($roleobj);
            $forumObj->setViews(0);
            $forumObj->setReplies($replies);
            $forumObj->setIsImportant($sticky);
            $forumObj->setCreatedAt(new \DateTime("now"));
            $forumObj->setCreatedBy($contactobj);
            $forumObj->setUpdatedAt(new \DateTime("now"));
            $forumObj->setUpdatedBy($contactobj);

            $this->_em->persist($forumObj);
            $this->_em->flush();

            $this->_em->getRepository('CommonUtilityBundle:FgForumTopicData')->saveNewTopicData($forumObj,$forumArray,$contactobj);
            $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->saveContactDetails($contactobj,$forumObj,0, true);

            return $forumObj->getId();
        }else{
            return false;
        }
    }
    
    /**
     * Function to get the details of a topic and its post
     * 
     * @param int $clubId  ClubId
     * @param int $topicId TopicId
     * @param int $page    Page number
     * @param int $limit   Posts per page
     * 
     * @return array $result Topic details 
     */
    public function getTopicDetails($clubId, $topicId, $page = 1, $limit = 20)
    {
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $offset = ($page == 1) ? 0 : $limit * ($page - 1);
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $doctrineConfig->addCustomStringFunction('contactNameNoSort', 'Common\UtilityBundle\Extensions\FetchContactNameNoSort');
        
        $postCount = $this->getEntityManager()->createQueryBuilder();
        $postCount->select('COUNT(td.id)')
                ->from('CommonUtilityBundle:FgForumTopicData', 'td')
                ->where("td.forumTopic=:topicId");
        
        $q = $this->createQueryBuilder('f')
                ->select("f.id as topicId, f.title, (CASE WHEN f.replies = 'allowed' THEN '1' ELSE '0' END) AS isRepliesAllowed, f.followTopic as isFollower, f.isImportant, f.isClosed, IDENTITY(f.group) AS roleId, d.postContent as content, (DATE_FORMAT(d.createdAt, '$datetimeFormat')) as createdDate, contactNameNoSort(d.createdBy 0) as createdBy, (DATE_FORMAT(d.updatedAt, '$datetimeFormat')) as updatedDate, contactNameNoSort(d.updatedBy 0) as updatedBy, d.uniquePostId as uniqueId, r.type as roleType, d.id as topicDataId")
                ->addSelect('(' . $postCount->getDQL() . ') as postCount')
                ->leftJoin('CommonUtilityBundle:FgForumTopicData', 'd', 'WITH', 'd.forumTopic = f.id')
                ->leftJoin('CommonUtilityBundle:FgRmRole', 'r', 'WITH', 'r.id = f.group AND r.isActive = 1 AND r.club = f.club')
                ->where('f.id = :topicId')
                ->andWhere('f.club = :clubId')
                ->orderBy('d.createdAt', ' ASC')
                ->setMaxResults($limit)
                ->setFirstResult($offset)
                ->setParameters(array('topicId' => $topicId, 'clubId' => $clubId));
        $result = $q->getQuery()->getArrayResult();

        return $result;
    }
    
    /**
     * list topic data
     * @param int $grpId   team/wg id
     * @param int $topicId topic id
     * @param int $clubId  club id
     * @return array
     */
    public function getListForumPost($grpId,$topicId,$clubId,$contactId){
        
         $q = $this->createQueryBuilder('f')
                 ->select('d.id,d.uniquePostId,cd.readAt,d.createdAt')
                 ->leftJoin('CommonUtilityBundle:FgForumTopicData', 'd','WITH','d.forumTopic = f.id')
                 ->leftJoin('CommonUtilityBundle:FgForumContactDetails', 'cd','WITH','cd.forumTopic = f.id AND cd.contact=:contactId')
                 ->where('f.id =:topicId')
                 ->andWhere('f.club =:clubId')
                 ->andWhere('f.group =:grpId')
                 ->orderBy('d.uniquePostId','ASC')
                 ->setParameters(array('topicId' => $topicId,'clubId' => $clubId,'grpId' =>$grpId,'contactId'=>$contactId ));
          $result = $q->getQuery()->getArrayResult();

        return $result;
                 
    }
    /**
     * To get active forum topics
     * @param int $clubId  club id
     * @return array
     */
    public function getActiveForums($clubId){
         $q = $this->getEntityManager()->createQueryBuilder();
                $q->select('rr.id')
                ->from('CommonUtilityBundle:FgRmRole', 'rr')
                ->where('rr.isDeactivatedForum =:isDeact')
                ->andWhere('rr.club =:clubId')
                ->setParameters(array('isDeact' => 0, 'clubId' => $clubId ));
         
          $result = $q->getQuery()->getArrayResult();
          return $result;
                 
    }
    /**
     * To active/deactivate forum
     * @param int $role roleid=team id/wg id
     * @param int $status 0/1
     * @return boolean
     */
    public function setActivateForum($role,$status){
        $forumObj = $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->find($role);
        $forumObj->setIsDeactivatedForum($status);
        $this->_em->persist($forumObj);
        $this->_em->flush();
    }
    /**
     * To check whether the forum is activated.
     * @param int $roleId roleid=team id/wg id
     * @return boolean
     */
    public function isActivatedForum($roleId){
        return $this->_em->getRepository('CommonUtilityBundle:FgRmRole')->getFieldForRoles($roleId,'is_deactivated_forum');
       
    }
    /**
     * To check whether the forum is activated.
     * @param int $roleId roleid=team id/wg id
     * @param int $clubId
     * @param int $contactId
     * @return boolean
     */
    public function isFollowTopic($groupId, $clubId, $contactId){
        $followObject = $this->getEntityManager()->createQueryBuilder();
        $followObject->select('COUNT(ff.id)')
                ->from('CommonUtilityBundle:FgForumFollowers', 'ff')
                ->where("ff.group=:grpId")
                ->andWhere('ff.club =:clubId')
                ->andWhere('ff.contact =:contactId')
                ->setParameters(array( 'grpId'=> $groupId, 'clubId' => $clubId, 'contactId' => $contactId ));
          $result = $followObject->getQuery()->getArrayResult();
          return $result;
        
    }
    
    
    /**
     * Method to add new topic reply
     * 
     * @param string $content   reply content
     * @param int    $topicId   Current topicId
     * @param int    $contactId Current contact-id
     * 
     * @return int
     */
    public function saveTopicReply($content, $topicId, $contactId) {
        $topicObj = $this->find($topicId);
        $topicReplies = $topicObj->getReplies();
        $topicIsClosed = $topicObj->getIsClosed();
        if($topicReplies == "allowed" && $topicIsClosed != 1) {
            $uniquePostId = $this->getUniquePostId($topicId);
            $contactobj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $topicDataObj = new \Common\UtilityBundle\Entity\FgForumTopicData();
            $topicDataObj->setForumTopic($topicObj);
            $topicDataObj->setPostContent($content);        
            $topicDataObj->setCreatedAt(new \DateTime("now"));
            $topicDataObj->setCreatedBy($contactobj);
            $topicDataObj->setUniquePostId($uniquePostId);

            $this->_em->persist($topicDataObj);
            $this->_em->flush();
            
            //update read-at of loggedin contact
            $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->updateReadAt($topicId, $contactId);            
            
            //update topic table
            if($topicDataObj->getUniquePostId()) {
                $topicObj->setUpdatedBy($contactobj);
                $topicObj->setUpdatedAt(new \DateTime("now"));
                $this->_em->flush();
            }

            return $topicDataObj->getUniquePostId();
        }        
    }
    
    /**
     * Method to get new unique post-id for a topic. It is max(uniqu-id) + 1
     * 
     * @param int $topicId
     * 
     * @return int
     */
    private function getUniquePostId($topicId) {
        $q = $this->createQueryBuilder('F')
                ->select('MAX(FD.uniquePostId) AS maxId')
                ->innerJoin('CommonUtilityBundle:FgForumTopicData', 'FD','WITH','F.id = FD.forumTopic')
                ->where('F.id =:topicId')               
                ->setParameters(array('topicId' => $topicId ));
        $result = $q->getQuery()->getArrayResult();
        $return = ($result[0]['maxId']) ? ($result[0]['maxId'] + 1) : 1;
         
        return $return;
    }
    
    /**
     * Method to update last_notification_send data after adding to notification spool, when a new reply is added
     * 
     * @param int $topicId   Topic-id
     * @param int $contactId Current contact-id
     */
    public function updateNotificationSendDate($topicId, $contactId) {
        $q = $this->createQueryBuilder('F')
                ->select('FC.id')
                ->innerJoin('CommonUtilityBundle:FgForumContactDetails', 'FC','WITH','F.id = FC.forumTopic')
                ->where('F.id =:topicId')   
                ->andWhere('FC.contact =:contactId')
                ->setParameters(array('topicId' => $topicId, 'contactId' => $contactId));
        $result = $q->getQuery()->getArrayResult();
        if (!empty($result)) {
            $topicContactObj = $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->find($result[0]['id']);
            if($topicContactObj) {
                $topicContactObj->setLastNotificationSend(new \DateTime("now"));
                $this->_em->flush();
            }
        }
    }

    /**
     * Function to remove forum topic data
     *
     * @param int     $topicContentId Topic content data id
     * 
     * @return null
     */
    public function removeForum($topicContentId) {
        $object = $this->find($topicContentId);
        $this->_em->remove($object);
        $this->_em->flush();
    }
    
    /**
     * Method to edit topic content
     * 
     * @param string $content   Content to edit
     * @param int    $dataId    Topic-content-id
     * @param int    $contactId Current contact Id
     * 
     * @return datetime
     */
    public function editTopicReply($content, $dataId, $contactId) {
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        $topicDataObj = $this->_em->getRepository('CommonUtilityBundle:FgForumTopicData')->find($dataId);
        $topicDataObj->setPostContent($content);
        $topicDataObj->setUpdatedBy($contactObj);
        $topicDataObj->setUpdatedAt(new \DateTime("now"));        
        $this->_em->flush();
        //update topic table
        if($topicDataObj->getForumTopic()) {
                $topicObj = $topicDataObj->getForumTopic();
                $topicObj->setUpdatedBy($contactObj);
                $topicObj->setUpdatedAt(new \DateTime("now"));
                $this->_em->flush();
        }
        $datetimeFormat = FgSettings::getPhpDateTimeFormat();
        
        return $topicDataObj->getUpdatedAt()->format($datetimeFormat);
    }
    
    /**
     * Topic settings menu
     *
     * @param int  $topicId    Forum topic Id
     * @param int  $checkedVal    Forum topic Checked Value
     * @param String  $chkType    Forum topic Checked Type
     * 
     * @return JsonResponse
     */
    public function settingEditForum($topicId, $checkedVal, $chkType)
    {
        $topicObj = $this->find($topicId);
        
        if($chkType == "isImportant") {
            $topicObj->setIsImportant($checkedVal);
        }
        else if($chkType == "isClosed") {
            $topicObj->setIsClosed($checkedVal);
            if($checkedVal == 1) {
                $topicObj->setIsImportant(0);
                $topicObj->setReplies('not_allowed');
            }
            else if($checkedVal == 0) {
                $topicObj->setReplies('allowed');
            }
        }
        
        $this->_em->persist($topicObj);
        $this->_em->flush();
    }
    
    /**
     * Topic Follow/Unfollow edit
     *
     * @param int  $topicId    Forum topic Id
     * @param int  $contactId  Current contact-id
     * @param int  $followVal  Forum topic follow/unfollow Value
     * 
     * @return JsonResponse
     */
    public function followerEditForum($topicId, $contactId, $followVal)
    {
        $q = $this->createQueryBuilder('FT')
                ->select('FC.id')
                ->innerJoin('CommonUtilityBundle:FgForumContactDetails', 'FC','WITH','FT.id = FC.forumTopic')
                ->where('FT.id =:topicId')   
                ->andWhere('FC.contact =:contactId')
                ->setParameters(array('topicId' => $topicId, 'contactId' => $contactId));
        $result = $q->getQuery()->getArrayResult();
        if(count($result) > 0) {
            $topicContactObj = $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->find($result[0]['id']);
            if($topicContactObj) {
                $topicContactObj->setIsNotificationEnabled($followVal);
                $this->_em->flush();
            }
        } else {
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $topicObj = $this->_em->getRepository('CommonUtilityBundle:FgForumTopic')->find($topicId);
            $this->_em->getRepository('CommonUtilityBundle:FgForumContactDetails')->saveContactDetails($contactObj,$topicObj,$followVal, false);            
        }
        
    }
    
    /**
     * Topic Replies Allowed/Not Allowed
     *
     * @param int  $topicId    Forum topic Id
     * @param string  $repliesData    Forum topic replies Allowed/Not Allowed
     * 
     * @return JsonResponse
     */
    public function forumRepliesEdit($topicId, $repliesData)
    {
        $topicObj = $this->find($topicId);
        
        $topicObj->setReplies($repliesData);
        
        $this->_em->persist($topicObj);
        $this->_em->flush();
    }
    
    /**
     * Method to know if forum is deactivated for that team
     * 
     * @param int $topicId Topic-id
     * 
     * @return boolean
     */
    public function isDeactivated($topicId) {
        $topicObj = $this->find($topicId);
        $isDeactivated = $topicObj->getGroup()->getIsDeactivatedForum();
        $return = ($isDeactivated == 1) ? true : false;
        
        return $return;        
    }
}

