<?php

/**
 * FgSmAdAreaRepository.
 *
 * This class is basically used for creating and editing ads  in sponsor manager.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Repository\Pdo\SponsorPdo;
use Common\UtilityBundle\Util\FgUtility;

/**
 * FgSmAdAreaRepository.
 *
 * FgSmAdAreaRepository is being used in listing add areas
 */
class FgSmAdAreaRepository extends EntityRepository
{
    /**
     * Function to get system categories.
     *
     * @param int $clubId current club id
     */
    public function getAlladsCommonCategory($clubId)
    {
        $qb = $this->createQueryBuilder('s')
                ->select('s.title, s.id')
                ->where('s.club=:club')
                ->andWhere('s.isSystem=:isSystem')
                ->setParameter('club', $clubId)
                ->setParameter('isSystem', 1)
                ->setParameter('club', $clubId);

        $dataResult = $qb->getQuery()->getResult();

        return $dataResult;
    }

    /**
     * Function to create and edit ad categories from listing page.
     *
     * @param int   $clubId current club id
     * @param array $catArr ad area category details array
     */
    public function adscategorySave($clubId, $catArr)
    {
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        foreach ($catArr as $id => $data) {
            $categoryobj = $this->find($id);
            if ($data['is_deleted'] == 1) {
                $this->deleteCategory($categoryobj);
                continue;
            }
            if (empty($categoryobj)) {
                $categoryobj = new \Common\UtilityBundle\Entity\FgSmAdArea();
            }
            if (isset($data['title'])) {
                $categoryobj->setTitle($data['title']);
            }
            $categoryobj->setClub($clubobj);
            $categoryobj->setIsSystem(0);
            $this->_em->persist($categoryobj);
            $this->_em->flush();
        }
    }

    /**
     * Function to delete categories from listing page.
     *
     * @param object $catobj ad area category object
     */
    public function deleteCategory($catobj)
    {
        $this->_em->remove($catobj);
        $this->_em->flush();
    }

    /**
     * Function to get all ads category.
     *
     * @param int $clubId current club id
     *
     * @return array
     */
    public function getAlladsCategory($clubId)
    {
        $qb = $this->createQueryBuilder('s')
                ->select('s.title, s.id, s.isSystem')
                ->where('s.club=:club')
                ->andWhere('s.isSystem=:isSystem')
                ->orderBy('s.title', 'ASC')
                ->setParameter('isSystem', 0)
                ->setParameter('club', $clubId);
        $dataResult = $qb->getQuery()->getResult();
        $result = array();
        foreach ($dataResult as $arr) {
            if (count($arr) > 0) {
                $result['cat'.$arr['id']] = array('id' => $arr['id'], 'title' => $arr['title']);
            }
        }

        return $result;
    }

    /**
     * Method to all sponsor ad areas.
     *
     * @param int $clubId current clubId
     *
     * @return array
     */
    public function getAdAreas($clubId)
    {
        $qb = $this->createQueryBuilder('AdArea')
                ->select('AdArea.id as adId, AdArea.title as adTitle, AdArea.isSystem as isSystem')
                ->where('AdArea.club=:club')
                ->groupBy('AdArea.id')
                ->addOrderBy('AdArea.isSystem', 'DESC')
                ->addOrderBy('AdArea.title', 'ASC')
                ->setParameter('club', $clubId);
        $result = $qb->getQuery()->getArrayResult();

        return $result;
    }

    /**
     * Function to get details of previews of sponsor ads.
     *
     * @param string $services  comma separated services
     * @param int    $adArea    ad area id
     * @param int    $width     width
     * @param int    $clubId    current Club Id
     * @param int    $container container
     * @param int    $club      club service
     *
     * @return array
     */
    public function getDetailsOfSponsorAdPreview($services, $adArea, $width, $clubId, $container, $club)
    {
        $sponsorAds = array();     
        if ($services) {
            $serviceContacts = $this->_em->getRepository('CommonUtilityBundle:FgSmBookings')->getContactsofServices($services, $clubId);
            if ($serviceContacts) {
                $objSponsorPdo = new SponsorPdo($container);
                $sponsorAds = $objSponsorPdo->getSponsorAds($adArea, $serviceContacts, $clubId);
                //service for getting company logo path
                $fgAvatarService = $container->get('fg.avatar');
                for ($i = 0; $i < count($sponsorAds); ++$i) {
                    $filepath = ($width == 120) ? '/'.FgUtility::getUploadFilePath($clubId, 'ad', '150').'/' : (($width == 200) ? '/'.FgUtility::getUploadFilePath($clubId, 'ad', '250').'/' : '/'.FgUtility::getUploadFilePath($clubId, 'ad', $width).'/');
                    $sponsorAds[$i]['image'] = FgUtility::getFilePath($filepath, $sponsorAds[$i]['image']);
                    $sponsorAds[$i]['contactDetails'] = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getContactLogo($sponsorAds[$i]['contact'], $club, $container);
                    $companyLogoFolder = ($width == 150 || $width == 120) ?  'width_150' : 'original' ;
                    $sponsorAds[$i]['contactDetails']['companyLogo'] = ($sponsorAds[$i]['contactDetails']['companyLogo']) ? ('/'.$fgAvatarService->getUploadFilePath('companylogo', $companyLogoFolder).'/'.$sponsorAds[$i]['contactDetails']['companyLogo']) : '';
                    $sponsorAds[$i]['contactDetails']['website'] = $sponsorAds[$i]['contactDetails']['website'];
                    $sponsorAds[$i]['width'] = $width;
                }
            }
        }

        return $sponsorAds;
    }
     /**
     * Function to get data for  sponsor ads previews in website
     *
     * @param string $services  comma separated services
     * @param int    $adArea    ad area id
     * @param int    $clubId    current Club Id
     * @param int    $container container
     * @param int    $club      club service
     *
     * @return array
     */
    public function getAdPreviewDetailsOfSponsor($services, $adArea, $clubId, $container, $club)
    {
        
        $sponsorAds = array();
        if ($services) {
            $serviceContacts = $this->_em->getRepository('CommonUtilityBundle:FgSmBookings')->getContactsofServices($services, $clubId);
            if ($serviceContacts) {
                $objSponsorPdo = new SponsorPdo($container);
                $sponsorAds = $objSponsorPdo->getSponsorAds($adArea, $serviceContacts, $clubId);
                $sponsorAdsResult = array();
                //service for getting company logo path
                $fgAvatarService = $container->get('fg.avatar');
                $filepath = '/' . FgUtility::getUploadFilePath($clubId, 'ad', '150') . '/';
                $companyLogoFolder = 'width_150';
                for ($i = 0; $i < count($sponsorAds); ++$i) {
                    $contactDetails = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->getContactLogo($sponsorAds[$i]['contact'], $club, $container);
                    $index = strtolower(preg_replace('/[^a-zA-Z0-9\s.]/', '', $contactDetails['lastname'])).$i;
                    $sponsorAdsResult[$index] = $sponsorAds[$i];
                    $sponsorAdsResult[$index]['contactDetails'] = $contactDetails;
                    $sponsorAdsResult[$index]['image'] = FgUtility::getFilePath($filepath, $sponsorAds[$i]['image']);
                    $sponsorAdsResult[$index]['contactDetails']['companyLogo'] = ($contactDetails['companyLogo']) ? ('/' . $fgAvatarService->getUploadFilePath('companylogo', $companyLogoFolder) . '/' . $contactDetails['companyLogo']) : '';
                    $sponsorAdsResult[$index]['contactDetails']['website'] = $contactDetails['website'];
                }
                    
            }
        }
        ksort($sponsorAdsResult);
        
        return $sponsorAdsResult;
    }
}
