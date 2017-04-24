<?php

namespace Common\UtilityBundle\Repository\Forum;

use Doctrine\ORM\EntityRepository;
use \Common\UtilityBundle\Entity\FgForumFollowers;
use Common\UtilityBundle\Util\FgSettings;
use \Common\UtilityBundle\Entity\FgCmContact;
/**
 * FgForumFollowersRepository
 *
 * @author 
 */
class FgForumFollowersRepository extends EntityRepository {

    
    /**
     * To add forum follower
     * @param int $groupId 
     * @param int $clubId
     * @param int $contactId
     */
    public function addForumFollower($groupId, $clubId, $contactId ) {
        $logQuery="INSERT INTO fg_forum_followers (club_id, group_id, contact_id,is_follow_forum) values($clubId, $groupId, $contactId, 1)";
        $this->getEntityManager()->getConnection()->executeQuery($logQuery);
    }
    /**
     * To remove forum follower
     * @param int $groupId 
     * @param int $clubId
     * @param int $contactId
     */
    public function removeForumFollower($groupId, $clubId, $contactId ) {
        $logQuery="DELETE FROM fg_forum_followers WHERE club_id='$clubId' AND group_id='$groupId' AND contact_id='$contactId'";
        $this->getEntityManager()->getConnection()->executeQuery($logQuery);
    }
    
    
}

