<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Filesystem\Filesystem;
use Clubadmin\Classes\FgImage;

/**
 * This repository is used for handling newsletter sidebar content services manipulation
 * @author pitsolutions.ch
 *
 */
class FgCnNewsletterSidebarServicesRepository extends EntityRepository {
    
    /**
     * Method to remove  services of particular sidebar content Id
     * @param type $sidebarcontentId   content Id
     */
    public function removeServices($sidebarcontentId) {        
        $results = $this->createQueryBuilder("S")              
            ->select("S.id as id")
            ->innerJoin('CommonUtilityBundle:FgCnNewsletterSidebar', "SC" ,"WITH" , "SC.id = S.newsletterSidebar" )
            ->where("SC.id = :sidebarcontent") 
            ->setParameters(array('sidebarcontent' => $sidebarcontentId))            
            ->getQuery()->getArrayResult();    
        
        $em = $this->getEntityManager();
        if($results) {
            foreach($results as $result) { 
                $serviceObj =  $this->find($result['id']);
                $em->remove($serviceObj);
                $em->flush();
            }
        }               
    }
    
    /**
     * Method to get service ids of newsletter sidebar content
     * @param int $sidebarcontentId    Content id
     * @return string comma separated ids
     */
    public function getServicesofSidebarContent($sidebarcontentId) {
        $results = $this->createQueryBuilder("CS")              
            ->select("GROUP_CONCAT(S.id) as services")
            ->innerJoin('CommonUtilityBundle:FgSmServices', "S" ,"WITH" , "S.id = CS.service" )
            ->innerJoin('CommonUtilityBundle:FgCnNewsletterSidebar', "NS" ,"WITH" , "NS.id = CS.newsletterSidebar" )
            ->where("NS.id = :sidebarcontentId") 
            ->setParameters(array('sidebarcontentId' => $sidebarcontentId))            
            ->getQuery()->getArrayResult();
        
        return $results[0]['services'];
    }
        
    
}
