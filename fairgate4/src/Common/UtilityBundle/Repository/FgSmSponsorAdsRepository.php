<?php
namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\Filesystem\Filesystem;
use Clubadmin\Classes\FgImage;
/**
 * FgSmSponsorAdsRepository
 *
 * This class is used for handling sponsor Ads in Sponsors Administration.
 *
 * @author pitsolutions.ch
 */
class FgSmSponsorAdsRepository extends EntityRepository {
    private $insertDataArray = array();
    private $deleteAdsArray = array();
    private $updateAdsArray = array();

    /**
     * Method to get count of sponser ads
     * @param int $clubId  current club Id
     * @param int $contact contactId
     * @return int
     */
    public function getCountOfSponsorAds($clubId, $contact) {
        $resultQuery = $this->createQueryBuilder('SA')
                ->select('COUNT(SA.id) as adsCount')
//                ->innerJoin("CommonUtilityBundle:FgSmAdArea", "A", "WITH", "SA.adArea = A.id")
                ->where("SA.contact = :contact")
                ->andWhere('SA.isDefault = 0  OR SA.isDefault IS NULL')
                ->andWhere('SA.club = :clubId');
        $resultQuery->setParameters(array('contact' => $contact, 'clubId' => $clubId));
        $results = $resultQuery->getQuery()->getArrayResult();

        return $results[0]['adsCount'];
    }

    /**
     * Function for inserting default sponsor ads on creating a sponsor.
     *
     * @param int $clubId    Club id
     * @param int $contactId Contact id (Array of contact ids if more than 1 sponsor)
     */
    public function insertDefaultSponsorAds($clubId, $contactId)
    {
        $contactIds = is_array($contactId) ? $contactId : array($contactId);
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        foreach ($contactIds as $contactIdVal) {
            $existingObj = $this->_em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->findOneBy(array('club' => $clubId, 'contact' => $contactId, 'isDefault' => '1', 'sortOrder' => '1'));
            if (!$existingObj) {
                $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactIdVal);
                $sponsorAd = new \Common\UtilityBundle\Entity\FgSmSponsorAds();
                $sponsorAd->setClub($clubObj)
                        ->setContact($contactObj)
                        ->setIsDefault('1')
                        ->setSortOrder('1');
                $this->_em->persist($sponsorAd);
            }
        }
        $this->_em->flush();
    }

    /**
     * Function to get ads of a sponsor.
     *
     * @param int  $contactId     Contact id
     * @param int  $clubId        Club id
     * @param bool $getSortedData Whether to get sorted data or not
     *
     * @return array Result array of sponsor ads.
     */
    public function getSponsorAds($contactId, $clubId, $getSortedData = false)
    {
        $result = $this->createQueryBuilder('sa')
                ->select('sa.id, sa.image, sa.description, sa.url, IDENTITY(sa.adArea) AS adAreaId, sa.imageSize, sa.isDefault, sa.sortOrder')
                ->where("sa.contact = :contactId")
                ->andWhere('sa.club = :clubId')
                ->orderBy('sa.sortOrder', 'DESC')
                ->setParameters(array('contactId' => $contactId, 'clubId' => $clubId))
                ->getQuery()
                ->getArrayResult();

        $resultData = array();
        if ($getSortedData) {
            $maxSortOrder = 1;
            foreach ($result as $key => $resultVal) {
                if ($key == '0') {
                    $maxSortOrder = $resultVal['sortOrder'];
                }
                $resultData[$maxSortOrder - $resultVal['sortOrder']] = $resultVal;
            }
        }

        return $getSortedData ? $resultData : $result;
    }

    /**
     * Function used for updating (add, edit, delete) ads of a sponsor.
     *
     * @param array  $dataArray Array of data to be updated
     * @param int    $clubId    Club id
     * @param int    $sponsorId Sponsor id
     * @param object $container Container object
     */
    public function updateSponsorAds($dataArray, $clubId, $sponsorId, $container)
    {
        if (count($dataArray) > 0) {
            foreach ($dataArray as $adId => $dataArr) {
                if ($adId == 'new') {
                    // Add Sponsor Ads.
                    $this->insertDataArray = $dataArr;
                } else {
                    $delFlag = isset($dataArr['is_deleted']) ? $dataArr['is_deleted'] : 0;
                    if (($delFlag == 1) || ($delFlag == '1')) {
                        // Delete Sponsor Ads.
                        $this->deleteAdsArray[] = $adId;
                    } else {
                        // Update Sponsor Ads.
                        $this->updateAdsArray[$adId] = $dataArr;
                    }
                }
            }
            // Execute Queries (Insert, Delete, Update).
            $this->executeQueries($clubId, $sponsorId, $container);
        }
    }

    /**
     * Function for executing the queries.
     *
     * @param int    $clubId    Club id
     * @param int    $sponsorId Sponsor id
     * @param object $container Container object
     */
    private function executeQueries($clubId, $sponsorId, $container)
    {
        // Delete Sponsor Ads.
        $this->executeDeleteQuery($clubId, $sponsorId, $container);

        // Update Sponsor Ads.
        $this->executeUpdateQuery($clubId, $sponsorId);

        // Create folders
        $this->createFolders($clubId);

        // Insert Sponsor Ads.
        $this->executeInsertQuery($clubId, $sponsorId, $container);
    }

    /**
     * Function for deleting sponsor ads.
     *
     * @param int    $clubId    Club id
     * @param int    $sponsorId Sponsor id
     * @param object $container Container object
     */
    private function executeDeleteQuery($clubId, $sponsorId, $container)
    {
        if (count($this->deleteAdsArray)) {
            foreach ($this->deleteAdsArray as $delId) {
                $adsObj = $this->_em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->find($delId);
                if ($adsObj) {
                    // Check whether the ad is of current club and sponsor.
                    if (($adsObj->getClub()->getId() == $clubId) && ($adsObj->getContact()->getId() == $sponsorId)) {
                        $this->removeFiles($adsObj->getImage(), $clubId, $container);
                        $this->_em->remove($adsObj);
                    }
                }
            }
            $this->_em->flush();
        }
    }

    /**
     * Function for updating sponsor ads.
     *
     * @param int $clubId    Club id
     * @param int $sponsorId Sponsor id
     */
    private function executeUpdateQuery($clubId, $sponsorId)
    {
        if (count($this->updateAdsArray)) {
            foreach ($this->updateAdsArray as $adId => $updateData) {
                $adObj = $this->_em->getRepository('CommonUtilityBundle:FgSmSponsorAds')->find($adId);
                // Check whether the ad is of current club and sponsor.
                if (($adObj->getClub()->getId() == $clubId) && ($adObj->getContact()->getId() == $sponsorId)) {
                    if (isset($updateData['description'])) {
                        $adObj->setDescription($updateData['description']);
                    }
                    if (isset($updateData['url'])) {
                        $adObj->setUrl($updateData['url']);
                    }
                    if (isset($updateData['ad_area_id'])) {
                        $adAreaObj = ($updateData['ad_area_id'] == '') ? NULL : $this->_em->getReference('CommonUtilityBundle:FgSmAdArea', $updateData['ad_area_id']);
                        $adObj->setAdArea($adAreaObj);
                    }
                    if (isset($updateData['sort_order'])) {
                        $adObj->setSortOrder($updateData['sort_order']);
                    }
                    $this->_em->persist($adObj);
                }
            }
            $this->_em->flush();
        }
    }

    /**
     * Function for inserting sponsor ads.
     *
     * @param int    $clubId    Club id
     * @param int    $sponsorId Sponsor id
     * @param object $container Container object
     */
    private function executeInsertQuery($clubId, $sponsorId, $container)
    {
        if (count($this->insertDataArray)) {
            $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($sponsorId);
            foreach ($this->insertDataArray as $tempId => $insertDataArr) {
                $adsObj = new \Common\UtilityBundle\Entity\FgSmSponsorAds();
                $adsObj->setClub($clubObj);
                $adsObj->setContact($contactObj);
                $this->insertAdData($adsObj, $insertDataArr, $tempId, $clubId, $container);
                $this->_em->persist($adsObj);
            }
            $this->_em->flush();
        }
    }

    /**
     * Function for inserting data.
     *
     * @param object $adsObj        Ads object
     * @param array  $insertDataArr Array of data to be inserted
     * @param int    $tempId        Temporary id of new data
     * @param int    $clubId        Club id
     * @param object $container     Container object
     */
    private function insertAdData($adsObj, $insertDataArr, $tempId, $clubId, $container)
    {
        if (isset($insertDataArr['description'])) {
            $adsObj->setDescription($insertDataArr['description']);
        }
        if (isset($insertDataArr['url'])) {
            $adsObj->setUrl($insertDataArr['url']);
        }
        if (isset($insertDataArr['ad_area_id'])) {
            $adAreaObj = ($insertDataArr['ad_area_id'] == '') ? NULL : $this->_em->getReference('CommonUtilityBundle:FgSmAdArea', $insertDataArr['ad_area_id']);
            $adsObj->setAdArea($adAreaObj);
        }
        if (isset($insertDataArr['sort_order'])) {
            $adsObj->setSortOrder($insertDataArr['sort_order']);
        }
        if (isset($insertDataArr['image'])) {
            $rootPath = FgUtility::getRootPath($container);
            $fileName= FgUtility::getFilename("$rootPath/".FgUtility::getUploadFilePath($clubId,'ad','original'),$insertDataArr['image']);
            $adsObj->setImage($fileName);
            $adsObj->setImageSize($insertDataArr['image_size']);
            $this->saveAndResizeImage($tempId.'-'.$insertDataArr['oldimage_name'], $fileName, $clubId, $container);
        }
    }

    /**
     * Function for saving and resizing sponsor ad images in different folders.
     *
     * @param string $tempFileName Temporary file name which the file is saved
     * @param string $fileName     Name to which the file is saved
     * @param int    $clubId       Club id
     * @param object $container    Container object
     */
    private function saveAndResizeImage($tempFileName, $fileName, $clubId, $container)
    {
        $rootPath = FgUtility::getRootPath($container);
        $fs = new Filesystem();
        $fs->copy("$rootPath/uploads/temp/$tempFileName", "$rootPath/".FgUtility::getUploadFilePath($clubId,'ad','original',$fileName));
        $imageInfo = getimagesize("$rootPath/uploads/temp/$tempFileName");
        $imageWidth = $imageInfo[0];
        $widthArray = array('150', '250', '500', '1100');
        foreach ($widthArray as $width) {
            $fs->copy("$rootPath/uploads/temp/$tempFileName", "$rootPath/".FgUtility::getUploadFilePath($clubId,'ad',$width,$fileName));
            if ($imageWidth > $width) {
                $image = new FgImage();
                $image->load("$rootPath/".FgUtility::getUploadFilePath($clubId,'ad',$width,$fileName));
                $image->resizeToWidth($width);
                $image->save("$rootPath/".FgUtility::getUploadFilePath($clubId,'ad',$width,$fileName));
            }
        }
    }

    /**
     * Function for creating the folders of sponsor ads if not exists.
     *
     * @param int $clubId Club id
     */
    private function createFolders($clubId)
    {
        $path=FgUtility::getUploadFilePath($clubId,'ad');
        if (!is_dir($path)) {
            mkdir($path, 0700,true);
        }
        $widthArray = array('original','150', '250', '500', '1100');
        foreach ($widthArray as $width) {
            $path=FgUtility::getUploadFilePath($clubId,'ad',$width);
            if (!is_dir($path)) {
                mkdir($path, 0700,true);
            }
        }
    }

    /**
     * Function for removing sponsor ad images from different folders.
     *
     * @param string $fileName  Name of file to be deleted
     * @param int    $clubId    Club id
     * @param object $container Container object
     */
    private function removeFiles($fileName, $clubId, $container)
    {
        $rootPath = FgUtility::getRootPath($container);
        $widthArray = array('original','150', '250', '500', '1100');
        foreach ($widthArray as $width) {
            $uploadDir= $rootPath . '/'.FgUtility::getUploadFilePath($clubId,'ad',$width,$fileName);
            if (file_exists($uploadDir)) {
                unlink($uploadDir);
            }
        }
    }

}
