<?php

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Common\UtilityBundle\Entity\FgClubTerminology;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Repository\Pdo\ClubPdo;

/**
 * Description of FgClubTerminologyRepository
 *
 */
class FgClubTerminologyRepository extends EntityRepository {

    /**
     * get Terminology form Details
     *
     * @param type $clubIdentifier
     *
     * @return type
     */
    public function getTerminologyformDetails($clubIdentifier) {
        $qb = $this->createQueryBuilder('ca')
                ->select('ca.id', 'ca.defaultSingularTerm', 'ca.defaultPluralTerm', 'ca.singular', 'ca.plural')
                ->where('ca.club=:clubId')
                ->setParameter('clubId', $clubIdentifier);
        $result = $qb->getQuery()->getResult();

        return $result;
    }   

    /**
     * get Terminology update Details
     *
     * @param type $clubId      Club id
     * @param type $defsingular default singular
     *
     * @return type
     */
    public function getTerminologyupdateDetails($clubId, $defsingular) {
        $qb = $this->createQueryBuilder('ca')
                ->select('ca.id', 'ca.defaultSingularTerm', 'ca.defaultPluralTerm', 'ca.singular', 'ca.plural')
                ->where('ca.club=:clubId')
                ->andWhere('ca.defaultSingularTerm=:defsingular')
                ->setParameter('clubId', $clubId)
                ->setParameter('defsingular', $defsingular);
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * get Terminology individual club Details
     *
     * @param Int   $clubid              Club id
     * @param Array $frontendModuleArray All frontend Module array
     * @param Array $bookedModulesDet    Booked module array
     *
     * @return type
     */
    public function getTerminologyindividualclubDetails($clubid, $frontendModuleArray, $bookedModulesDet) {
        $frontendModules = "('" . join("','", $frontendModuleArray) . "')";
        $condition = "";
        if (is_array($bookedModulesDet)) {
            if (!in_array('frontend1', $bookedModulesDet)) {
                $condition = " AND fg1.defaultSingularTerm NOT IN $frontendModules ";
            }
        }     
        
        $qb = $this->createQueryBuilder('fg1')
                ->select("fg1.id, fgi18.lang,fg1.defaultSingularTerm AS defaultSingularTerm, fg1.defaultPluralTerm AS defaultPluralTerm, "
                        . "CASE WHEN fgi18.singularLang IS NULL THEN fg1.singular ELSE fgi18.singularLang END as singular, "
                        . "CASE WHEN fgi18.pluralLang IS NULL THEN fg1.plural ELSE fgi18.pluralLang END as plural " )
                ->leftJoin('CommonUtilityBundle:FgClubTerminologyI18n', 'fgi18', 'WITH', 'fg1.id = fgi18.id')
                ->where("fg1.club =:clubid and fg1.isFederation=0 $condition ")
                ->setParameters(array('clubid' => $clubid));
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to get Terminology default club Details
     *
     * @param Int    $clubid              Club id
     * @param Array  $frontendModuleArray All frontend Module array
     * @param Array  $bookedModulesDet    Booked module array
     * @param string $clubSystemLang      Club default system language
     *
     * @return Array
     */
    public function getTerminologydefaultclubDetails($clubid, $frontendModuleArray, $bookedModulesDet, $clubSystemLang = '') {
        $frontendModules = "('" . join("','", $frontendModuleArray) . "')";
        $condition = "";
        if (!in_array('frontend1', $bookedModulesDet)) {
            $condition .= " AND fg1.defaultSingularTerm NOT IN $frontendModules ";
        }
        $condition .= " AND fg1.defaultSingularTerm != 'Intranet'";
        $systemLangCondition =  $clubSystemLang !='' ? " AND fg1i18.lang = '$clubSystemLang'" : '';
        
        $qb = $this->createQueryBuilder('fg1')
                ->select("fg1.id,fg1.defaultSingularTerm AS defaultSingularTerm, fg1i18.lang, fg1.defaultPluralTerm AS defaultPluralTerm, "
                        . " CASE WHEN ((fg1i18.singularLang IS NULL) or (fg1i18.singularLang='')) THEN (fg1.singular) ELSE (fg1i18.singularLang) END AS singular, "
                        . " CASE WHEN ((fg1i18.pluralLang IS NULL) or (fg1i18.pluralLang='')) THEN (fg1.plural) ELSE (fg1i18.pluralLang) END  AS plural")
                ->leftJoin('CommonUtilityBundle:FgClubTerminologyI18n', 'fg1i18', 'WITH', "fg1.id = fg1i18.id $systemLangCondition ")
                ->where("fg1.club =1 and fg1.isFederation=0 $condition ");
        if($clubSystemLang !='') {
            $qb->orderBy("fg1i18.lang", "ASC");
        }
                
        $result = $qb->getQuery()->getResult();
        
        return $result;
    }

    /**
     *
     *
     * @param type $clubid
     *
     * @return type
     */

    /**
     * Function to get  Terminology individual Fed Details
     *
     * @param int  $clubid           Id of club
     * @param bool $hasSubfederation Whether the club has subfederation under that
     * @return type
     */
    public function getTerminologyindividualFedDetails($clubid, $hasSubfederation) {
        $sqlSubfederationCondition = ($hasSubfederation == 0 ? " AND fg1.defaultSingularTerm <> 'Sub-federation'" : '');
        
        $qb = $this->createQueryBuilder('fg1')
                ->select("fg1.id,fgi18.lang,fg1.defaultSingularTerm AS defaultSingularTerm, fg1.defaultPluralTerm AS defaultPluralTerm, "
                        . " CASE WHEN fgi18.singularLang IS NULL THEN fg1.singular ELSE fgi18.singularLang END AS singular, "
                        . " CASE WHEN fgi18.pluralLang IS NULL THEN fg1.plural ELSE fgi18.pluralLang END AS plural ")
                ->leftJoin('CommonUtilityBundle:FgClubTerminologyI18n', 'fgi18', 'WITH', "fg1.id = fgi18.id ")
                ->where("fg1.club =:clubid and fg1.isFederation=1 $sqlSubfederationCondition ")
                ->setParameters(array('clubid' => $clubid));
        $result = $qb->getQuery()->getResult();
        
        return $result;
    }

    /**
     * Function to get get Terminology default Fed details
     *
     * @param type   $clubid
     * @param string $clubSystemLang Club default system language
     * @param bool $hasSubfederation Whether the club has subfederation under that
     *
     * @return type
     */
    public function getTerminologydefaultFedDetails($clubid, $clubSystemLang = '', $hasSubfederation) {
        $systemLangCondition =  $clubSystemLang !='' ? " AND fg1i18.lang = '$clubSystemLang'" : '';
        $sqlSubfederationCondition = ($hasSubfederation == 0 ? " AND fg1.defaultSingularTerm <> 'Sub-federation'" : '');       
        
        $qb = $this->createQueryBuilder('fg1')
                ->select("fg1.id, fg1i18.lang,fg1.defaultSingularTerm AS defaultSingularTerm, fg1.defaultPluralTerm AS defaultPluralTerm, "
                        . " CASE WHEN ((fg1i18.singularLang IS NULL) or (fg1i18.singularLang='')) THEN (fg1.singular) ELSE (fg1i18.singularLang) END AS singular, "
                        . " CASE WHEN ((fg1i18.pluralLang IS NULL) or (fg1i18.pluralLang='')) THEN (fg1.plural) ELSE (fg1i18.pluralLang) END AS plural ")                        
                ->leftJoin('CommonUtilityBundle:FgClubTerminologyI18n', 'fg1i18', 'WITH', "fg1i18.id = fg1.id $systemLangCondition ")
                ->where("fg1.club =1 and fg1.isFederation=1 $sqlSubfederationCondition ");
        if($clubSystemLang !='') {
            $qb->orderBy("fg1i18.lang", "ASC");
        }
        $result = $qb->getQuery()->getResult();
                
        return $result;
    }    

    /**
     * Function to get federation details of a terminology term
     *
     * @param type $defSingular default singular
     *
     * @return type
     */
    private function getFedidDetails($defSingular) {
        $result = $this->createQueryBuilder('ft')
                ->select("CASE WHEN ft.isFederation = 1 THEN 1 ELSE 0 END as is_federation ")                        
                ->leftJoin('CommonUtilityBundle:FgClub', 'fg', 'WITH', "fg.id= ft.club ")
                ->where("ft.club=1 AND ft.defaultSingularTerm= :defSingular ")
                ->setParameters(array('defSingular' => $defSingular))
                ->getQuery()->getResult();
                 
        return $result[0];
    }

    /**
     * Function to save and update terminology details of a club
     *
     * @param array  $attributes            terminology data array
     * @param string $clubDefaultSystemLang default system language
     * @param int    $clubId                current club id
     * @param array  $clubLanguages         club languages array
     * @param string $clubDefaultLang       default club language
     * @param string $domainCacheKey        cache key based on club heirarchy
     *
     * @return null
     */
    public function saveTerminology($attributes, $clubDefaultSystemLang, $clubId, $clubLanguages, $clubDefaultLang, $domainCacheKey,$container) {
        $conn = $this->getEntityManager()->getConnection();
        $clubobj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);

        foreach ($attributes as $key => $value) {

            $fgClubTerminologyI18nObj = $this->_em->getRepository('CommonUtilityBundle:FgClubTerminologyI18n')->getTerminologyi18nupdateDetails($clubDefaultSystemLang, $key);
            $fgClubTerminologyObj = $this->find($key);
            $defSingularTerm = $fgClubTerminologyObj->getDefaultSingularTerm();
            $defSingular = count($fgClubTerminologyI18nObj) ? $fgClubTerminologyI18nObj[0]['singularLang'] : $fgClubTerminologyObj->getSingular();

            $defPluralTerm = $fgClubTerminologyObj->getDefaultPluralTerm();
            $defPlural = count($fgClubTerminologyI18nObj) ? $fgClubTerminologyI18nObj[0]['pluralLang'] : $fgClubTerminologyObj->getPlural();

            $terminologyupdateDetails = $this->getTerminologyupdateDetails($clubId, $defSingularTerm);

            if (count($terminologyupdateDetails) > 0) {
                $terminologyId = $terminologyupdateDetails[0]['id'];
                $terminology = $this->find($terminologyId);
            } else {
                $terminology = new FgClubTerminology();
            }

            $terminology->setDefaultSingularTerm($defSingularTerm);
            $terminology->setDefaultPluralTerm($defPluralTerm);

            if (isset($value[$defSingularTerm])) {
                if (array_key_exists($clubDefaultLang, $value[$defSingularTerm])) {
                    $singular = FgUtility::getSecuredData($value[$defSingularTerm][$clubDefaultLang], $conn);
                    $terminology->setSingular($singular);
                }
            }
            if ($singular == '') {
                $terminology->setSingular($defSingular);
            }
            if (isset($value[$defPluralTerm])) {
                if (array_key_exists($clubDefaultLang, $value[$defPluralTerm])) {
                    $plural = FgUtility::getSecuredData($value[$defPluralTerm][$clubDefaultLang], $conn);
                    $terminology->setPlural($plural);
                }
            }

            if ($plural == '') {
                $terminology->setPlural($defPlural);
            }
            $defSing = $terminology->getDefaultSingularTerm();
            $fedIdDetails = $this->getFedidDetails($defSing);
            $fedId = $fedIdDetails['is_federation'];
            $terminology->setSortOrder(0);
            $terminology->setIsFederation($fedId);
            $terminology->setClub($clubobj);
            $this->_em->persist($terminology);
            $this->_em->flush();
            $termId = $terminology->getId();
            foreach ($clubLanguages as $lang) {
                $singularLangVal = "";
                $pluralLangVal = "";
                if (array_key_exists($lang, $value[$defSingularTerm])) {
                    $singularLangVal = FgUtility::getSecuredData($value[$defSingularTerm][$lang], $conn);
                    $postLang = $lang;
                    if ($singularLangVal == "") {
                        $singularLangVal = "null";
                    }
                }
                if (array_key_exists($lang, $value[$defPluralTerm])) {

                    $pluralLangVal = FgUtility::getSecuredData($value[$defPluralTerm][$lang], $conn);
                    $postLang = $lang;
                    if ($pluralLangVal == "") {
                        $pluralLangVal = "null";
                    }
                }
                    $clubPdo = new ClubPdo($container);
                if ($singularLangVal != "" || $pluralLangVal != "") {
                    $terminologyi18nupdateDetails = $this->_em->getRepository('CommonUtilityBundle:FgClubTerminologyI18n')->getTerminologyi18nupdateDetails($postLang, $termId);
                    if (count($terminologyi18nupdateDetails) > 0) {
                        if ($singularLangVal == "") {
                            $singularLangVal = $terminologyi18nupdateDetails[0]['singularLang'];
                        }
                        if ($singularLangVal == "null") {
                            $singularLangVal = "";
                        }
                        if ($pluralLangVal == "") {
                            $pluralLangVal = $terminologyi18nupdateDetails[0]['pluralLang'];
                        }
                        if ($pluralLangVal == "null") {
                            $pluralLangVal = "";
                        }
                        $termi18nid = $terminologyi18nupdateDetails[0]['id'];
                        
                        $terminologyi18n = $clubPdo->updateterminology($termi18nid, $postLang, $singularLangVal, $pluralLangVal, $isActive = 0);

                    } else {
//                        $terminologyi18n = $this->_em->getRepository('CommonUtilityBundle:FgClubTerminologyI18n')->insertterminology($termId, $singularLangVal, $pluralLangVal, $postLang, $isActive = 0);
                          $terminologyi18n = $clubPdo->insertterminology($termId, $singularLangVal, $pluralLangVal, $postLang, $isActive = 0); 
                    }
                }
            }
        }
        //Remove apc cache entries while updating the data
        $cacheKey = str_replace('{{cache_area}}', 'terminology', $domainCacheKey);
        $translationTerminologyCacheKey = str_replace('{{cache_area}}', 'translationTerminology', $domainCacheKey);
        $terminologyTermsCacheKey = str_replace('{{cache_area}}', 'terminologyTerms', $domainCacheKey);
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $cacheDriver->deleteByPrefix($cacheKey);
        $cacheDriver->deleteByPrefix($translationTerminologyCacheKey);
        $cacheDriver->deleteByPrefix($terminologyTermsCacheKey);
    }

}
