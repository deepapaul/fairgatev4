<?php

/**
 * FgCmsArticleClubsettingRepository to handle fg_cms_article and related tables.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;

/**
 * FgCmsArticleClubsettingRepository to handle functionalities related to fg_cms_article and other article tables.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleClubsettingRepository extends EntityRepository
{

    /**
     * Function to get the article settings of an article.
     *
     * @param int $clubId The id of the club
     *
     * @return array article settings data
     */
    public function getClubSettings($clubId)
    {
        $clubArticles = $this->createQueryBuilder('S')
            ->select('S.commentActive, S.showMultilanguageVersion, S.timeperiodStartDay, S.timeperiodStartMonth')
            ->where("S.club = $clubId");

        $clubSettings = $clubArticles->getQuery()->getResult();

        return empty($clubSettings) ? array() : $clubSettings[0];
    }

    /**
     * Function to get the club timeperiod.
     *
     * @param int $clubId    The id of the club
     * @param int $offset    Number of years from today to be considered
     * @param int $startYear Start year
     *
     * @return array club timeperiod data
     */
    public function getClubTimeperiod($clubId, $offset = 2, $startYear = 0)
    {
        $timeperiodDataArray = array();
        $clubArticles = $this->createQueryBuilder('S')
            ->select('S.timeperiodStartDay, S.timeperiodStartMonth')
            ->where("S.club = $clubId");
        $timeperiodData = $clubArticles->getQuery()->getResult();

        $interval = new \DateInterval('P1Y');

        if (count($timeperiodData) > 0) {
            $timeperiodData = $timeperiodData[0];
            $currentYear = date('Y') + $offset;
            $totyear = 0;
            //FAIR-2524
            if ($startYear != 0) {
                $totyear = date('Y') - $startYear;
            }
            $totyear = $totyear + 5;

            for ($i = 0; $i <= $totyear; $i++) {
                $dateString = ($currentYear - $i) . '-' . sprintf('%02d', $timeperiodData['timeperiodStartMonth']) . '-' . sprintf('%02d', $timeperiodData['timeperiodStartDay']) . ' 00:00:00';
                $currentTimePeriodStartObj = \DateTime::createFromFormat('Y-m-d H:i:s', $dateString);

                if ($timeperiodData['timeperiodStartMonth'] == 1 && $timeperiodData['timeperiodStartDay'] == 1) {
                    $currentTimePeriodEndObj = \DateTime::createFromFormat('Y-m-d H:i:s', (($currentYear - $i) + 1) . '-01-01  00:00:00');
                } else {
                    $currentTimePeriodStartObjClone = clone($currentTimePeriodStartObj);
                    $currentTimePeriodEndObj = $currentTimePeriodStartObjClone->add($interval);
                }
                $currentTimePeriodEndObj->sub(new \DateInterval('PT1S'));

                if ($currentTimePeriodStartObj->format('Y') == $currentTimePeriodEndObj->format('Y')) {
                    $label = $currentTimePeriodStartObj->format('Y');
                } else {
                    $label = $currentTimePeriodStartObj->format('Y') . '/' . $currentTimePeriodEndObj->format('y');
                }
                $timeperiodDataArray[] = array('label' => $label, 'start' => $currentTimePeriodStartObj->format('Y-m-d'), 'end' => $currentTimePeriodEndObj->format('Y-m-d'));
            }
        }

        return $timeperiodDataArray;
    }

    /**
     * Function to save the timeperiod data for a particular club for editorial.
     *
     * @param int   $clubId   current club id
     * @param array $timeData time period data array
     *
     * @return void
     */
    public function saveTimePeriod($clubId, $timeData)
    {
        $club = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $timePeriod = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->findOneBy(array('club' => $clubId));
        if (empty($timePeriod)) {
            $timePeriod = new \Common\UtilityBundle\Entity\FgCmsArticleClubsetting();
            $timePeriod->setCommentActive(1);
            $timePeriod->setShowMultilanguageVersion(1);
            $timePeriod->setClub($club);
        }
        $timePeriod->setTimeperiodStartDay($timeData['dayVal']);
        $timePeriod->setTimeperiodStartMonth($timeData['monthVal']);

        $this->_em->persist($timePeriod);
        $this->_em->flush();

        return;
    }

    /**
     * Function to get the current comments or multi language article settings of a club.
     *
     * @param int    $clubId       current club id
     * @param string $settingsType settings type - either comments or multi language
     *
     * @return int settings value
     */
    public function getArticleClubSettings($clubId, $settingsType)
    {
        $settingsSelector = ($settingsType == 'comments') ? 'S.commentActive ' : 'S.showMultilanguageVersion ';
        $settings = $this->createQueryBuilder('S')
            ->select($settingsSelector . 'AS settingsVal')
            ->where("S.club = $clubId");

        $result = $settings->getQuery()->getArrayResult();

        return empty($result) ? 0 : $result[0]['settingsVal'];
    }

    /**
     * Function to save the article settings data for a particular club.
     *
     * @param int    $clubId       current club id
     * @param int    $settingsData current settings data
     * @param string $settingsType settings type - either comments or multi language
     *
     * @return void
     */
    public function saveArticleSettings($clubId, $settingsData, $settingsType)
    {
        $settings = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->findOneBy(array('club' => $clubId));
        if (empty($settings)) {
            $settings = new \Common\UtilityBundle\Entity\FgCmsArticleClubsetting();
            $club = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
            ($settingsType == 'comments') ? $settings->setShowMultilanguageVersion(1) : $settings->setCommentActive(1);
            $settings->setClub($club);
        }
        ($settingsType == 'comments') ? $settings->setCommentActive($settingsData) : $settings->setShowMultilanguageVersion($settingsData);

        $this->_em->persist($settings);
        $this->_em->flush();

        return;
    }
}
