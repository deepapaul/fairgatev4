<?php
/**
 * FgTmThemeFontsRepository
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;

/**
 * FgTmThemeFontsRepository - Repository class for theme fonts
 *
 * FgTmThemeFontsRepository - Repository class for fairgate theme fonts configuration functionalities
 *
 * @package    CommonUtility
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgTmThemeFontsRepository extends EntityRepository
{

    /**
     * Function to update font selections for a specific theme configuration
     *
     * @param integer $configId theme configuration id
     * @param array   $data     font data needs to be updated
     * 
     * @return boolean
     */
    public function updateFontConfigurations($configId, $data)
    {
        $fgTmThemeConfigurationObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        foreach ($data as $fontRecord) {
            $fgTmThemeFontsObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeFonts')->find($fontRecord['id']);
            $fgTmThemeFontsObj
                ->setFontLabel($fontRecord['fontLabel'])
                ->setFontName($fontRecord['fontName'])
                ->setFontStrength($fontRecord['fontStrength'])
                ->setIsItalic((isset($fontRecord['isItalic']) ? 1 : 0))
                ->setIsUppercase((isset($fontRecord['isUcase']) ? 1 : 0))
                ->setConfiguration($fgTmThemeConfigurationObj);
            $this->_em->persist($fgTmThemeFontsObj);
        }
        $this->_em->flush();

        return true;
    }

    /**
     * Function to save new font selections for a specific theme configuration
     *
     * @param integer $configId theme configuration id
     * @param array   $data     font data needs to be saved
     * 
     * @return boolean
     */
    public function saveFontConfigurations($configId, $data)
    {
        $fgTmThemeConfigurationObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        foreach ($data['FONT_ID'] as $fontId) {
            $fgTmThemeFontsObj = new \Common\UtilityBundle\Entity\FgTmThemeFonts();
            $fgTmThemeFontsObj
                ->setFontLabel($data[$fontId . '_LABEL'])
                ->setFontName($data[$fontId . '_NAME'])
                ->setFontStrength($data[$fontId . '_STRENGTH'])
                ->setIsItalic((isset($data[$fontId . '_ITALIC']) ? 1 : 0))
                ->setIsUppercase((isset($data[$fontId . '_UCASE']) ? 1 : 0))
                ->setConfiguration($fgTmThemeConfigurationObj);
            $this->_em->persist($fgTmThemeFontsObj);
        }
        $this->_em->flush();

        return true;
    }

    /**
     * This function is used to get font configurations
     * 
     * @param integer $configId theme configuration id
     * 
     * @return array font configurations
     */
    public function getFontConfiguration($configId)
    {
        $fontObj = $this->createQueryBuilder('f')
            ->select("f.id as id, f.fontLabel as fontLabel, f.fontName as fontName, f.fontStrength as fontStrength, f.isItalic as isItalic, f.isUppercase as isUppercase")
            ->where('f.configuration = :configId')
            ->setParameter('configId', $configId);

        return $fontObj->getQuery()->getArrayResult();
    }
}
