<?php

/**
 * FgTmThemeRepository
 * 
 * @package 	CommonUtilityBundle
 * @subpackage 	Repository
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Common\UtilityBundle\Repository\Cms;

use Common\UtilityBundle\Entity\FgWebSettings;
use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgWebSettingsRepository to manage fg_web_settings table
 */
class FgWebSettingsRepository extends EntityRepository
{

    /**
     * Method to insert/update website settings
     * 
     * @param array  $dataArray data to insert 
     * @param object $container container object
     * 
     * @return int fg_web_settings table id
     */
    public function saveSettings($dataArray, $container)
    {
        $club = $container->get('club');
        $clubId = $club->get('id');
        $clubDefaultLanguage = $club->get('club_default_lang');
        $domainCacheKey = $club->get('clubCacheKey');        
        
        $webSettingsObj = $this->findOneBy(array('club' => $clubId));
        if (!empty($webSettingsObj)) {
            $webSettingsObj->setUpdatedAt(new \DateTime("now"));
            $webSettingsObj = $this->updateFields($webSettingsObj, $dataArray, $clubDefaultLanguage);
            $this->_em->flush();
        } else {
            $webSettingsObj = $this->insertSettings($clubId, $dataArray, $clubDefaultLanguage);
        }

        //insert descroption to language table
        if (isset($dataArray['siteDesc'])) {
            $this->_em->getRepository('CommonUtilityBundle:FgWebSettingsI18n')->updateDescription($webSettingsObj->getId(), $dataArray['siteDesc']);
        }

        //save default club language entries to main table. To handle scenarios when club default languages changes.
        $cmsPdo = new CmsPdo($container);
        $cmsPdo->saveWebSettingsDefaultLang($webSettingsObj->getId(), $clubDefaultLanguage);

        //Remove apc cache entries while updating websettings of the current club
        $cacheKeySplit = explode('{{cache_area}}', $domainCacheKey);
        $cacheKey = $cacheKeySplit[0].'websettings';
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheDriver->deleteByPrefix($cacheKey);
        
        return $webSettingsObj->getId();
    }

    /**
     * Method to insert website settings
     * 
     * @param int    $clubId              current clubId
     * @param array  $dataArray           data to insert
     * @param string $clubDefaultLanguage club DefaultLanguage 
     * 
     * @return object $webSettingsObj    
     */
    private function insertSettings($clubId, $dataArray, $clubDefaultLanguage)
    {
        //insert to table
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $webSettingsObj = new FgWebSettings();
        $webSettingsObj->setClub($clubObj);
        $webSettingsObj->setCreatedAt(new \DateTime("now"));
        $webSettingsObj = $this->updateFields($webSettingsObj, $dataArray, $clubDefaultLanguage);
        $this->_em->persist($webSettingsObj);
        $this->_em->flush();

        return $webSettingsObj;
    }

    /**
     * Method to update fields in website settings
     * 
     * @param object $webSettingsObj      current clubId
     * @param array  $dataArray           data to insert
     * @param string $clubDefaultLanguage club DefaultLanguage 
     * 
     * @return object $webSettingsObj 
     */
    private function updateFields($webSettingsObj, $dataArray, $clubDefaultLanguage)
    {
        if (isset($dataArray['logo_originalname'])) {
            $webSettingsObj->setDefaultLogo($dataArray['logo_originalname']);
        }
        if (isset($dataArray['ogimg_originalname'])) {
            $webSettingsObj->setFallbackImage($dataArray['ogimg_originalname']);
        }
        if (isset($dataArray['html_originalname'])) {
            $webSettingsObj->setDomainVerificationFilename($dataArray['html_originalname']);
        }
        if (isset($dataArray['favicon_originalname'])) {
            $webSettingsObj->setFavicon($dataArray['favicon_originalname']);
        }
        if (isset($dataArray['siteDesc']) && isset($dataArray['siteDesc'][$clubDefaultLanguage])) {
            $webSettingsObj->setSiteDescription($dataArray['siteDesc'][$clubDefaultLanguage]);
        }
        if (isset($dataArray['g-analytics'])) {
            $webSettingsObj->setGoogleAnalyticsTrackid($dataArray['g-analytics']);
        }

        return $webSettingsObj;
    }

    /**
     * Method to get websettings details
     * 
     * @param int     $clubId         ClubId
     * @param Integer $clubCacheKey   Cachekey used for caching
     * @param Integer $cacheLifeTime  Cache expiry time
     * @param Integer $cachingEnabled Cache enabled or not
     * 
     * @return array websettings details
     */
    public function getWebSettings($clubId, $clubCacheKey, $cacheLifeTime, $cachingEnabled)
    {
        $cacheKey = str_replace('{{cache_area}}', 'websettings', $clubCacheKey);
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $qb = $this->createQueryBuilder('WS');
        $qb->select('WS.id, WS.defaultLogo, WS.favicon, WS.fallbackImage, WS.siteDescription, WS.domainVerificationFilename, WS.googleAnalyticsTrackid, LANG.lang, LANG.descriptionLang ')
            ->leftJoin('CommonUtilityBundle:FgWebSettingsI18n', 'LANG', 'WITH', 'WS.id = LANG.settings')
            ->where('WS.club = :clubId')
            ->setParameter('clubId', $clubId);
       
        $webDetails = $cacheDriver->getCachedResult($qb, $cacheKey, $cacheLifeTime, $cachingEnabled);
        
        return $this->formatArray($webDetails);
    }

    /**
     * Method to get clubs with the domain verification file
     * 
     * @param int $clubId clubId
     * 
     * @return array
     */
    public function getNotEmptyDomainFiles()
    {
        $qb = $this->createQueryBuilder('WS');
        $qb->select('WS.id, IDENTITY(WS.club) AS clubId, WS.domainVerificationFilename')
            ->where('WS.domainVerificationFilename IS NOT NULL');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Method clear the domain files of the specified filed
     * 
     * @param array $clearSettingsIdArray The id of the club settings of which the domain has to be cleared
     * 
     * @return void
     */
    public function clearDomainFiles($clearSettingsIdArray)
    {
        foreach ($clearSettingsIdArray as $id) {
            $webSettingsObj = $this->_em->getRepository('CommonUtilityBundle:FgWebSettings')->find($id);
            $webSettingsObj->setDomainVerificationFilename(null);
            $this->_em->flush();
        }

        return $webSettingsObj;
    }

    /**
     * Method to format websettings array in specific format
     * 
     * @param array $webDetails web details
     * 
     * @return array formatted array
     */
    private function formatArray($webDetails)
    {
        $resultArray = array();
        if (count($webDetails)) {
            foreach ($webDetails as $webDetail) {
                $resultArray['defaultLogo'] = $webDetail['defaultLogo'];
                $resultArray['settingsId'] = $webDetail['id'];
                $resultArray['favicon'] = $webDetail['favicon'];
                $resultArray['fallbackImage'] = $webDetail['fallbackImage'];
                $resultArray['domainVerificationFilename'] = $webDetail['domainVerificationFilename'];
                $resultArray['googleAnalyticsTrackid'] = $webDetail['googleAnalyticsTrackid'];
                $resultArray['siteDescription']['default'] = $webDetail['siteDescription'];
                $resultArray['siteDescription'][$webDetail['lang']] = $webDetail['descriptionLang'];
            }
        } else {
            $resultArray = array('defaultLogo' => '', 'favicon' => '', 'fallbackImage' => '', 'domainVerificationFilename' => '', 'googleAnalyticsTrackid' => '', 'siteDescription' => array('default' => ''));
        }

        return $resultArray;
    }
}
