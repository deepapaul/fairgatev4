<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgCnNewsletterContentServicesRepository
 *
 * This class is used for handling Newsletter Content Services
 *
 * @author pitsolutions.ch
 */
class FgCnNewsletterContentServicesRepository extends EntityRepository {
    
    /**
     * Method to remove services of particular content Id
     * @param type $contentId   content Id
     */
    public function removeServices($contentId) {        
        $results = $this->createQueryBuilder("S")              
            ->select("S.id as id")
            ->innerJoin('CommonUtilityBundle:FgCnNewsletterContent', "C" ,"WITH" , "C.id = S.content" )
            ->where("C.id = :content") 
            ->setParameters(array('content' => $contentId))            
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
     * Method to get service ids of newsletter content
     * @param int $contentId    Content id
     * @return string comma separated ids
     */
    public function getServicesofNewsletterContent($contentId) {
        $results = $this->createQueryBuilder("CS")              
            ->select("GROUP_CONCAT(S.id) as services")
            ->innerJoin('CommonUtilityBundle:FgSmServices', "S" ,"WITH" , "S.id = CS.service" )
            ->innerJoin('CommonUtilityBundle:FgCnNewsletterContent', "NC" ,"WITH" , "NC.id = CS.content" )
            ->where("NC.id = :content") 
            ->setParameters(array('content' => $contentId))            
            ->getQuery()->getArrayResult();
        
        return $results[0]['services'];
    }
}
