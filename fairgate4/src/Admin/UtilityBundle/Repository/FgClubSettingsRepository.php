<?php

namespace Admin\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * This repository is used for club settings
 *
 * @author pitsolutions.ch
 */
class FgClubSettingsRepository extends EntityRepository
{

    /**
     * Function to save and edit currency details for a particular club
     *
     * @param int    $clubId         current club id
     * @param string $currency       currency code
     * @param int    $id             club-settings id
     * @param int    $domainCacheKey domain Cache Key for clearing cache
     *
     * @return null
     */
    public function clubCurrencySave($clubId, $currency, $id, $domainCacheKey)
    {
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $categoryobj = $this->find($id);
        if (empty($categoryobj)) {
            $categoryobj = new \Admin\UtilityBundle\Entity\FgClubSettings();
        }
        $categoryobj->setCurrency($currency);
        $categoryobj->setCurrencyPosition("left");
        $categoryobj->setClub($clubobj);
        $this->_em->persist($categoryobj);
        $this->_em->flush();

        //Remove apc cache entries while updating the data
        $cacheKeySplit = explode('{{cache_area}}', $domainCacheKey);
        $cacheKey = $cacheKeySplit[0].'clubdetails';
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheDriver->deleteByPrefix($cacheKey);
    }

    /**
     * Function to get club currency details
     *
     * @param int    $clubId   current club id
     *
     * @return array
     */
    public function getClubSettingsDetail($clubId, $defaultLang)
    { 
        $qb = $this->createQueryBuilder('s')
                ->select('s.currency , s.id as settingsId,s.federationIcon as fedicon, s.currencyPosition as position')
                ->addSelect('CASE WHEN si18n.signatureLang IS NULL THEN s.signature ELSE si18n.signatureLang as clubsignature')
                ->addSelect('CASE WHEN si18n.logoLang IS NULL THEN s.logo ELSE si18n.logoLang as clublogo')
                ->leftJoin('CommonUtilityBundle:FgClubSettingsI18n', 'si18n', 'WITH', 's.id = si18n.id AND si18n.lang = :defaultLang')
                ->where('s.club=:club')
                ->setParameters(array('club'=>$clubId, 'defaultLang' => $defaultLang));
        $result = $qb->getQuery()->getResult();        
        
        return $result[0];
    }

    /**
     * Function to update age limit settings of a club
     *
     * @param int $clubId           ClubId
     * @param int $majorityAge      Majority Age
     * @param int $profileAccessAge Maximum age of child above which parent can't access child's profile
     */
    public function updateAgeLimitSettings($clubId, $majorityAge, $profileAccessAge)
    {
        if (($majorityAge >= 0) && ($profileAccessAge >= 0) && ($majorityAge < 100) && ($profileAccessAge < 100)) {
            $clubSettingsArr = $this->findOneBy(array('club' => $clubId));
            if ($clubSettingsArr) {
                $clubSettingsObj = $this->find($clubSettingsArr->getId());
            } else {
                $clubSettingsObj = new \Admin\UtilityBundle\Entity\FgClubSettings();
                $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
                $clubSettingsObj->setClub($clubObj);
            }
            $clubSettingsObj->setMajorityAge($majorityAge);
            $clubSettingsObj->setProfileAccessAge($profileAccessAge);
            $this->_em->persist($clubSettingsObj);
            $this->_em->flush();
        }
    }
    /**
     * Function to get federation /subfederation icon
     *
     * @param int    $clubId   fed or sub fed id
     *
     * @return array
     */
    public function getFederationIcon($clubId)
    {
        
        $qb = $this->createQueryBuilder('s')
                ->select('s.federationIcon as fedicon')
                ->where('s.club=:club')
                ->setParameter('club', $clubId);
        $result = $qb->getQuery()->getSingleScalarResult();

        return $result;
    }

}
