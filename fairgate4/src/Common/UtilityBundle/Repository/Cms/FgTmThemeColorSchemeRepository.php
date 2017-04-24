<?php
/**
 * FgTmThemeColorSchemeRepository
 * 
 * @package 	name
 * @subpackage 	name
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
namespace Common\UtilityBundle\Repository\Cms;

use Common\UtilityBundle\Entity\FgTmThemeColorScheme;
use Doctrine\ORM\EntityRepository;

/**
 * FgTmThemeColorSchemeRepository
 * 
 * Handles tm theme color scheme table insert, update, delete, get data
 */
class FgTmThemeColorSchemeRepository extends EntityRepository
{

    /**
     * insert/duplicate color schemes
     * 
     * @param integer $clubId club id
     * @param integer $themeId theme id
     * @param array $colorSchemeData
     * @param string $flag
     * @param integer $color
     * 
     * @return integer
     */
    public function duplicateColorScheme($clubId, $themeId, $colorSchemeData, $flag = 'create', $color = '')
    {
        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $themeObj = $this->_em->getReference('CommonUtilityBundle:FgTmTheme', $themeId);
        if ($flag === 'create') {
            $themeColorSchemeObj = new FgTmThemeColorScheme();
            $themeColorSchemeObj->setTheme($themeObj);
            $themeColorSchemeObj->setIsDefault(0);
            $themeColorSchemeObj->setClub($clubObj);
        } else {
            $themeColorSchemeObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->find($color);
        }
        $themeColorSchemeObj->setColorSchemes($colorSchemeData);

        $this->_em->persist($themeColorSchemeObj);
        $this->_em->flush();

        return $themeColorSchemeObj->getId();
    }

    /**
     * delete color schemes
     * 
     * @param integer $color
     * @return
     */
    public function deleteColorScheme($color)
    {
        $themeColorSchemeObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->find($color);
        if ($themeColorSchemeObj) {
            $this->_em->remove($themeColorSchemeObj);
        }
        $this->_em->flush();

        return;
    }
}
