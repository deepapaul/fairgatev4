<?php

namespace Admin\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * FgClubSalutationSettingsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class FgClubSalutationSettingsRepository extends EntityRepository
{

    /**
     * This function is used to get the salutation for individual club
     *
     * @param int $clubId Club id
     *
     * @return $result Array The result set
     */
    public function getClubSalutation($clubId)
    {
        $langQuery = $this->createQueryBuilder("cls")
            ->select('cls.id,cl18n.lang,cl18n.femaleFormalLang AS femaleFormal, cl18n.maleInformalLang AS maleInformal,'
                . 'cl18n.femaleInformalLang AS femaleInformal,cl18n.maleFormalLang AS maleFormal,'
                . 'cl18n.familyFormalLang AS familyFormal, cl18n.familyInformalLang  AS familyInformal,'
                . 'cl18n.companyNoMaincontactLang  AS companyNoMain, cl18n.subscriberLang  AS subscriber')
            ->leftJoin('CommonUtilityBundle:FgClubSalutationSettingsI18n', 'cl18n', 'WITH', 'cls.id = cl18n.id')
            ->where('cls.club=:clubId')
            ->setParameter('clubId', $clubId);
        $result = $langQuery->getQuery()->getResult();


        return $result;
    }

    /**
     * Function to get default salutation
     *
     * @return Array $result The result set
     */
    public function getDefaultSalutation()
    {
        $langQuery = $this->createQueryBuilder("cls")
            ->select("cls.id,cl18n.lang, CASE WHEN ( cl18n.femaleFormalLang IS NULL  OR cl18n.femaleFormalLang ='' ) THEN cls.femaleFormal  ELSE cl18n.femaleFormalLang END AS femaleFormal ,"
                . "CASE WHEN ( cl18n.femaleInformalLang IS NULL  OR cl18n.femaleInformalLang ='' ) THEN cls.femaleInformal  ELSE cl18n.femaleInformalLang END AS femaleInformal,"
                . " CASE WHEN ( cl18n.maleInformalLang IS NULL  OR cl18n.maleInformalLang ='' ) THEN cls.maleInformal  ELSE cl18n.maleInformalLang END AS maleInformal,"
                . "CASE WHEN ( cl18n.maleFormalLang IS NULL  OR cl18n.maleFormalLang ='' ) THEN cls.maleFormal ELSE cl18n.maleFormalLang END AS maleFormal,"
                . "CASE WHEN ( cl18n.familyFormalLang IS NULL  OR cl18n.familyFormalLang ='' ) THEN cls.familyFormal ELSE cl18n.familyFormalLang END AS familyFormal,"
                . "CASE WHEN ( cl18n.familyInformalLang IS NULL  OR cl18n.familyInformalLang ='' ) THEN cls.familyInformal  ELSE cl18n.familyInformalLang END AS familyInformal,"
                . "CASE WHEN ( cl18n.companyNoMaincontactLang IS NULL  OR cl18n.companyNoMaincontactLang ='' ) THEN cls.companyNoMaincontact  ELSE cl18n.companyNoMaincontactLang END AS companyNoMain,"
                . "CASE WHEN ( cl18n.subscriberLang IS NULL  OR cl18n.subscriberLang ='' ) THEN cls.subscriber ELSE cl18n.subscriberLang END AS subscriber"
            )
            ->leftJoin('CommonUtilityBundle:FgClubSalutationSettingsI18n', 'cl18n', 'WITH', 'cls.id = cl18n.id')
            ->where('cls.club=1')
            ->orderBy('cl18n.lang', 'ASC');
        $result = $langQuery->getQuery()->getResult();

        return $result;
    }

    /**
     * Function to get the salutation for website newsletter subscription mail
     * 
     * @param object $container The container object
     *
     * @return Array The result set
     */
    public function getSalutationForNewletterSubscription($container)
    {

        $clubId = $container->get('club')->get('id');
        $clubCorrespondanceLanguage = $container->get('club')->get('default_lang');
        $clubSystemLanguage = $container->get('club')->get('default_system_lang');
        $clubIdArray = array($clubId, 1);
        $select = $this->createQueryBuilder("CS")
            ->select(
                "COALESCE(NULLIF(CSi18N.femaleFormalLang,''), NULLIF(CS_DEFi18N.femaleFormalLang,''),NULLIF(CS_DEF.femaleFormal,'')) AS femaleFormal," .
                "COALESCE(NULLIF(CSi18N.femaleInformalLang,''), NULLIF(CS_DEFi18N.femaleInformalLang,''),NULLIF(CS_DEF.femaleInformal,'')) AS femaleInformal," .
                "COALESCE(NULLIF(CSi18N.maleFormalLang,''), NULLIF(CS_DEFi18N.maleFormalLang,''),NULLIF(CS_DEF.maleFormal,'')) AS maleFormal," .
                "COALESCE(NULLIF(CSi18N.maleInformalLang,''), NULLIF(CS_DEFi18N.maleInformalLang,''),NULLIF(CS_DEF.maleInformal,'')) AS maleInformal"
            )
            ->leftJoin('CommonUtilityBundle:FgClubSalutationSettingsI18n', 'CSi18N', 'WITH', "CS.id = CSi18N.id AND CSi18N.lang = '$clubCorrespondanceLanguage'")
            ->leftJoin('CommonUtilityBundle:FgClubSalutationSettings', 'CS_DEF', 'WITH', 'CS_DEF.club = 1')
            ->leftJoin('CommonUtilityBundle:FgClubSalutationSettingsI18n', 'CS_DEFi18N', 'WITH', "CS_DEF.id = CS_DEFi18N.id AND CS_DEFi18N.lang = '$clubSystemLanguage'")
            ->where('CS.club IN(:club)')
            ->orderBy('CS.club', 'DESC')
            ->setParameter('club', $clubIdArray);
        $result = $select->getQuery()->getResult();

        return $result[0];
    }
}
