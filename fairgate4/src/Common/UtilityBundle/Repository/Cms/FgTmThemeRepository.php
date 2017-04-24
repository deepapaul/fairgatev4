<?php
/**
 * FgTmThemeRepository
 * 
 * @package 	name
 * @subpackage 	name
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgTmThemeRepository
 * 
 * Handles tm theme table insert, update, delete, get data
 */
class FgTmThemeRepository extends EntityRepository
{

    /**
     * get theme list
     * 
     * @return array
     */
    public function getAllThemes()
    {
        $themeResultArr = array();
        $themes = $this->createQueryBuilder('t')
            ->select("t.id as themeId, cs.id as colorId, cs.colorSchemes as colorSchemes, t.title as title, t.themeOptions as themeOptions")
            ->leftJoin('CommonUtilityBundle:FgTmThemeColorScheme', 'cs', 'WITH', 't.id = cs.theme')
            ->where('cs.isDefault = 1')
            ->andWhere('t.isActive = 1')
            ->orderBy('t.sortOrder');
        $themesResult = $themes->getQuery()->getArrayResult();
        foreach ($themesResult as $val) {
            $themeResultArr[$val['themeId']]['title'] = $val['title'];
            $themeResultArr[$val['themeId']]['themeOptions'] = json_decode($val['themeOptions'], true);
            $themeResultArr[$val['themeId']]['color'][$val['colorId']]['colorSchemes'] = json_decode($val['colorSchemes'], true);
        }
        return $themeResultArr;
    }
}
