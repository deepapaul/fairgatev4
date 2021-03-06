<?php

namespace Admin\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgClubLanguageSettings;

/**
 * FgClubLanguageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgClubLanguageSettingsRepository extends EntityRepository {

    /**
     * Function to update club language settings table when add/update language
     *
     * @param object  $club            club object
     * @param array   $formValue       formValue array
     * @param int     $objClubId       ClubId
     * @param stirng  $clubLanguageObj club-langauge
     * @param boolean $isNew           1/0
     * @param object  $container       container Object
     */
    public function updateClubLanguageSettings($club,$formValue,$objClubId,$clubLanguageObj,$isNew, $container) {
        if($isNew){
            $clubLangSettingsObj = new FgClubLanguageSettings();
            $clubPdo = new \Admin\UtilityBundle\Repository\Pdo\ClubPdo($container);
            $sublevelclubs = $clubPdo->getAllSubLevelData($club->get('id'));
            foreach($sublevelclubs as $key => $value){
                $clubLangObj = new FgClubLanguageSettings();
                $clubIdObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($value['id']);
                $clubLangObj->setClub($clubIdObj);
                $clubLangObj->setClubLanguage($clubLanguageObj);
                $clubLangObj->setIsActive(1);
                $clubLangObj->setSortOrder(50);
                $this->_em->persist($clubLangObj);
                $this->_em->flush();
            }
        } else {
            $clubSettingsArr = $this->findOneBy(array('club' => $club->get('id'),'clubLanguage'=>$clubLanguageObj->getId()));
            if ($clubSettingsArr) {
                $clubLangSettingsObj = $this->find($clubSettingsArr->getId());
            }
        }
        $clubLangSettingsObj->setClub($objClubId);
        $clubLangSettingsObj->setClubLanguage($clubLanguageObj);
        if(isset($formValue['isActive'])) {
            //$clubSettings = $this->findBy(array('club' => $club->get('id'),'isActive'=>1));
            $clubSettings=$this->getEntityManager()->getConnection()->fetchAll("SELECT COUNT(id) as count FROM fg_club_language_settings where is_active=1 AND club_id=".$club->get('id'));
            $isActive=($clubSettings[0]['count']>1)? $formValue['isActive']:1;
            $clubLangSettingsObj->setIsActive($isActive);
        }
        if(isset($formValue['sort'])) {
            $clubLangSettingsObj->setSortOrder($formValue['sort']);
        }
        $this->_em->persist($clubLangSettingsObj);
        $this->_em->flush();
    }

    /**
     * Function to get club active club languages
     *
     * @param integer $clubId club id
     * @param Integer $clubCacheKey  Cachekey used for caching
     * @param Integer $cacheLifeTime Cache expiry time
     *
     * @return array
     */
    public function getClubLanguages($clubId, $clubCacheKey, $cacheLifeTime, $cachingEnabled) {
        $cacheKey = str_replace('{{cache_area}}', 'club_language', $clubCacheKey);
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();        
        
        $langQuery = $this->createQueryBuilder("cls")
                   ->select('cl.correspondanceLang, cl.systemLang, cl.dateFormat, cl.timeFormat')
                   ->addSelect('cl.thousandSeparator, cl.decimalMarker, cls.sortOrder, c.settingsUpdated')
                   ->addSelect("CASE WHEN (ci18n.titleLang IS NULL OR ci18n.titleLang = '') THEN c.title ELSE ci18n.titleLang END as clubTitle")
                   ->addSelect("COALESCE(NULLIF(si18n.signatureLang,''), s.signature) as clubSignature, si18n.logoLang as clubLogo")
                   ->leftJoin('CommonUtilityBundle:FgClubLanguage', 'cl', 'WITH', 'cls.clubLanguage = cl.id')
                   ->leftJoin('CommonUtilityBundle:FgClub', 'c', 'WITH', 'c.id = cls.club')
                   ->leftJoin('CommonUtilityBundle:FgClubI18n', 'ci18n', 'WITH', 'c.id = ci18n.id AND ci18n.lang = cl.correspondanceLang')
                   ->innerJoin('CommonUtilityBundle:FgClubSettings', 's', 'WITH', 'c.id = s.club')
                   ->leftJoin('CommonUtilityBundle:FgClubSettingsI18n', 'si18n', 'WITH', 's.id = si18n.id AND si18n.lang = cl.correspondanceLang')
                   ->where('cls.club=:clubId')
                   ->andWhere('cls.isActive=1')
                   ->orderBy('cls.sortOrder, cl.id', 'ASC')
                   ->setParameter('clubId', $clubId);
        
        return $result = $cacheDriver->getCachedResult($langQuery, $cacheKey, $cacheLifeTime, $cachingEnabled);               
    }
}