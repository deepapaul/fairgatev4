<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Filesystem\Filesystem;
use Clubadmin\Classes\FgImage;

/**
 * This repository is used for handling newsletter sidebar content manipulation
 * @author pitsolutions.ch
 *
 */
class FgCnNewsletterSidebarRepository extends EntityRepository {
    
    /**
     * Function to save newsletter sidebar contents
     *
     * @param array $formArray
     * @param array $clubData
     * @param object $container
     * @return boolean
     */
    public function saveSidebarContent($formArray, $clubData, $container) {
        $em = $this->getEntityManager();
        $newsletterObj = $em->getRepository('CommonUtilityBundle:FgCnNewsletter')->find($clubData['newsletterId']);       
        foreach ($formArray as $type => $data) {
            foreach ($data as $sidebarContentId => $value) {
                switch ($type) {                   
                    //Save sponsor content
                    case 'sponsor':
                        $content = $em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebar')->find($sidebarContentId);                         
                        if ($value['isDelete'] == 1) {                          
                            $em->remove($content);
                            $em->flush(); 
                            break;                            
                        }                        
                        if (empty($content)) {                            
                            $content = new \Common\UtilityBundle\Entity\FgCnNewsletterSidebar();
                            $content->setNewsletter($newsletterObj);                    
                        }                        
                        $content->setSortOrder($value['sort']);
                        $content->setTitle($value['title']); 
                        if(!empty($value['areas'])){
                            $adAreaObj = $em->getRepository('CommonUtilityBundle:FgSmAdArea')->find($value['areas']);
                            $content->setSponsorAdArea($adAreaObj);   
                        }else{
                             $content->setSponsorAdArea(null);
                        }
                        $em->persist($content);
                        $em->flush(); 
                        //remove already assigned services
                        $em->getRepository('CommonUtilityBundle:FgCnNewsletterSidebarServices')->removeServices($content->getId());
                        //add new services
                        foreach($value['services'] as $serviceId) {                            
                            $services = new \Common\UtilityBundle\Entity\FgCnNewsletterSidebarServices();
                            $services->setNewsletterSidebar($content);
                            $serviceObj = $em->getRepository('CommonUtilityBundle:FgSmServices')->find($serviceId);
                            $services->setService($serviceObj);                        
                            $em->persist($services);
                            $em->flush();                            
                        }
                        break;                        
                }
            }
        }
        if ($newsletterObj->getStep() < 3) {
            $club = $container->get('club');
            $step = in_array('sponsor', $club->get('bookedModulesDet')) ? 5 : 4;
            $newsletterObj->setStep($step);
            $em->persist($newsletterObj);
            $em->flush();
        }
        return true;
    }
    /**
     * Function to get service ids grouped by sidebar ids for displaying in newsletter sidebar
     * 
     * @param int $newsletterId NewsletterId
     * 
     * @return array $results Sidebar details array
     */
    public function getServicesForNewsletterSidebar($newsletterId)
    {
        $results = $this->createQueryBuilder("NS")              
            ->select("GROUP_CONCAT(NSS.service) AS serviceIds, IDENTITY(NS.sponsorAdArea) AS adAreaId, NS.title")
            ->innerJoin('CommonUtilityBundle:FgCnNewsletterSidebarServices', "NSS" ,"WITH" , "NS.id = NSS.newsletterSidebar")
            ->innerJoin('CommonUtilityBundle:FgSmServices', "S" ,"WITH" , "S.id = NSS.service")
            ->where("NS.newsletter = :newsletterId")   
            ->orderBy("NS.id", "ASC")
            ->groupBy("NS.id")
            ->setParameters(array('newsletterId' => $newsletterId))            
            ->getQuery()
            ->getArrayResult();
        
        return $results;
    }
    
    /**
     * Function to get sponsor details to be displayed in newsletter sidebar
     * 
     * @param int    $newsletterId NewsletterId
     * @param object $container    Container Object
     * 
     * @return array $sidebarSponsors Array of sponsordetails
     */
    public function getSidebarSponsors($newsletterId, $container)
    {
        $club = $container->get('club');
        $clubId = $club->get('id');
        $sidebarSponsors = array();            
        $adAreaServices = $this->getServicesForNewsletterSidebar($newsletterId);
        $width = 120;//fixed width for sponsor images in newsletter sidebar
        foreach ($adAreaServices as $key => $adAreaService) {
            $adDetails = $this->_em->getRepository('CommonUtilityBundle:FgSmAdArea')->getDetailsOfSponsorAdPreview($adAreaService['serviceIds'], $adAreaService['adAreaId'], $width, $clubId, $container, $club);
            $sidebarSponsors[$key]['title'] =  $adAreaService['title'];
            $sidebarSponsors[$key]['width'] =  $width;
            $sidebarSponsors[$key]['sponsors'] = $adDetails;
        }

        return $sidebarSponsors;
    }
}
