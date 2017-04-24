<?php
/**
 * FgTmThemeBgImagesRepository
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
 * FgTmThemeBgImagesRepository
 * 
 * Handles tm theme table insert, update, delete, get data
 */
class FgTmThemeBgImagesRepository extends EntityRepository
{

    /**
     * To get background image configurations
     * 
     * @param int    $configId Theme configuration id
     * @param string $type     Type of bg images - full_screen/original_size 	
     * 
     * @return array
     */
    public function getBgImageConfiguration($configId, $type = null)
    {
        $fontObj = $this->createQueryBuilder('b')
            ->select("b.id AS id, b.positionHorizontal AS positionHorizontal, b.positionVertical AS positionVertical, b.bgRepeat AS bgRepeat, b.isScrollable AS isScrollable, gi.filepath AS filePath, IDENTITY(gi.club) AS clubId")
            ->leftJoin('CommonUtilityBundle:FgGmItems', 'gi', 'WITH', 'gi.id = b.galleryItem')
            ->where('b.configuration = :configId')
            ->orderBy('b.sortOrder');
        if ($type) {
            $fontObj->andWhere('b.bgType = :type');
            $fontObj->setParameter('type', $type);
        }
        $fontObj->setParameter('configId', $configId);

        return $fontObj->getQuery()->getArrayResult();
    }
}
