<?php
/**
 * FgTmThemeConfigurationRepository
 */
namespace Common\UtilityBundle\Repository\Cms;

use Doctrine\ORM\EntityRepository;
use Internal\GalleryBundle\Util\GalleryList;
use Common\UtilityBundle\Repository\Pdo\CmsPdo;

/**
 * FgTmThemeConfigurationRepository - Repository class for theme configuration
 *
 * FgTmThemeConfigurationRepository - Repository class for fairgate theme configuration functionalities
 *
 * @package         CommonUtility
 * @subpackage      Repository
 * @author          pitsolutions.ch
 * @version         Fairgate V4
 */
class FgTmThemeConfigurationRepository extends EntityRepository
{

    /**
     *
     * Function to theme's general default data for a specified theme configuration id
     *
     * @param integer $configId - theme configuration id
     *
     * @return array|null - basic data of a theme
     */
    public function getThemeDataForConfigId($configId)
    {
        return $this->createQueryBuilder('tc')
                ->select("t.id, t.title, t.themeOptions,tc.headerScrolling, tc.title as configTitle , tc.headerPosition ,tc.headerLogoPosition ")
                ->innerJoin('tc.theme', 't')
                ->where('tc.id=:configId')
                ->setParameters(array('configId' => $configId))
                ->getQuery()
                ->getOneOrNullResult();
    }

    /**
     *
     * Function to get all font selections and its details for a specified theme configuration id
     *
     * @param integer $configId - theme configuration id
     * @return array - fonts data
     */
    public function getAllThemeFontsForConfigId($configId)
    {
        return $this->createQueryBuilder('tc')
                ->select("tf.id, tf.fontLabel, tf.fontName, tf.fontStrength, tf.isItalic, tf.isUppercase, tc.title as configTitle")
                ->innerJoin('CommonUtilityBundle:FgTmThemeFonts', 'tf', 'WITH', 'tf.configuration = tc.id')
                ->where('tc.id=:configId')
                ->setParameters(array('configId' => $configId))
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * Function to get all theme configurations
     * @param int $clubId
     * @param int $configId
     * @return array
     */
    public function getThemeConfigurations($clubId, $configId = '')
    {
        $config = $this->createQueryBuilder('C')
            ->select("C.id AS id, C.title AS title, C.isActive AS isActive, T.title AS themeTitle, CASE WHEN C.isDefault = 1 THEN 1 ELSE 0 END  AS isDefault")
            ->leftJoin('CommonUtilityBundle:FgTmTheme', 'T', 'WITH', 'T.id = C.theme');
        if ($configId !== '') {
            $config->where('C.club = :clubId')
                ->andWhere('C.id = :configId')
                ->setParameters(array('clubId' => $clubId, 'configId' => $configId));
        } else {
            $config->where('C.club = :clubId')
                ->setParameters(array('clubId' => $clubId));
        }
        $config->andWhere('C.isDeleted = 0')
            ->orderBy('isDefault', 'DESC')
            ->addOrderBy('C.title', 'ASC');

        return $config->getQuery()->getArrayResult();
    }

    /**
     * Function is used to duplicate theme configuration.
     * @param type $configId
     * @param type $container
     */
    public function duplicateThemeConfig($configId, $container)
    {
        $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        $copyOf = $container->get('translator')->trans('DUPLICATE_TEMPLATE_COPY');
        $newTitle = "$copyOf " . $configObj->getTitle();
        $newConfigObj = clone $configObj;
        $newConfigObj->setTitle($newTitle);
        $newConfigObj->setIsActive(0);
        $newConfigObj->setIsDefault(0);
        $this->_em->persist($newConfigObj);
        $this->_em->flush();
        $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->duplicateThemeHeader($configId, $newConfigObj);
        $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->duplicateThemeFont($configId, $newConfigObj);
        $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->duplicateThemeBgImages($configId, $newConfigObj);


        return $newConfigObj->getId();
    }

    /**
     * Function is used to duplicate theme header.
     * @param type $configId
     * @param type $newConfigObj
     */
    public function duplicateThemeHeader($configId, $newConfigObj)
    {
        $allHeaderObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeHeaders')->findBy(array('configuration' => $configId));
        foreach ($allHeaderObj as $headerObj) {
            if ($headerObj) {
                $newHeaderObj = clone $headerObj;
                $newHeaderObj->setConfiguration($newConfigObj);
                $this->_em->persist($newHeaderObj);
                $this->_em->flush();
            }
        }
    }

    /**
     * Function is used to duplicate theme font.
     * @param type $configId
     * @param type $newConfigObj
     */
    public function duplicateThemeFont($configId, $newConfigObj)
    {
        $allFontObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeFonts')->findBy(array('configuration' => $configId));
        foreach ($allFontObj as $fontObj) {
            if ($fontObj) {
                $newFontObj = clone $fontObj;
                $newFontObj->setConfiguration($newConfigObj);
                $this->_em->persist($newFontObj);
                $this->_em->flush();
            }
        }
    }

    /**
     * Function is used to duplicate theme BgImage.
     * @param type $configId
     * @param type $newConfigObj
     */
    public function duplicateThemeBgImages($configId, $newConfigObj)
    {
        $allBgObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeBgImages')->findBy(array('configuration' => $configId));
        foreach ($allBgObj as $bgObj) {
            if ($bgObj) {
                $newBgObj = clone $bgObj;
                $newBgObj->setConfiguration($newConfigObj);
                $this->_em->persist($newBgObj);
                $this->_em->flush();
            }
        }
    }

    /**
     * Function to delete configuration
     * @param type $id config Id
     */
    public function deleteThemeConfig($id)
    {
        $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($id);
        $flag = 0;
        $themeId = $configObj->getTheme();
        $colorSchemeId = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeColorScheme')->findOneBy(array('theme' => $themeId))->getId();
        $colorSchemeObj = $this->_em->getReference('CommonUtilityBundle:FgTmThemeColorScheme', $colorSchemeId);
        if ($configObj) {
            if ($configObj->getIsActive() != 1) {
                $configObj->setIsDeleted(1);
                $configObj->setColorScheme($colorSchemeObj);
                $this->_em->persist($configObj);
                $this->_em->flush();
                $flag = 1;
            }
        }
        
        return $flag;
    }

    /**
     * get theme list
     *
     * @return array
     */
    public function getBackgroundDetails($configurationId, $clubId)
    {
        $details = $this->createQueryBuilder('tc')
            ->select("tc.title as title, bi.bgType,g.id as galleryId,bi.sortOrder,tc.bgImageSelection,tc.bgSliderTime,tc.id as configId,bi.id as itemId,g.fileSize,g.fileName,bi.bgRepeat,bi.isScrollable,bi.positionVertical,bi.positionHorizontal")
            ->leftJoin('CommonUtilityBundle:FgTmThemeBgImages', 'bi', 'WITH', 'bi.configuration = tc.id')
            ->leftJoin('CommonUtilityBundle:FgGmItems', 'g', 'WITH', 'g.id = bi.galleryItem')
            ->where('tc.club=:club AND tc.id=:id')
            ->orderBy('bi.sortOrder', 'asc')
            ->setParameters(array('club' => $clubId, 'id' => $configurationId));

        return $details->getQuery()->getArrayResult();
    }

    /**
     * This function is used to activate a theme configuration
     * @param integer $configId
     * @param integer $clubId
     */
    public function activateThemeConfiguration($configId, $clubId, $clubObj)
    {
        $flag = 0;
        $allConfigObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->findBy(array('club' => $clubId));
        $selectedConfig = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        //checking whether the configuration to activate is a deleted one
        if (!empty($allConfigObj) && $selectedConfig->getIsDeleted() != 1) {
            foreach ($allConfigObj as $configObj) {
                if ($configId == $configObj->getId()) {
                    $configObj->setIsActive(1);
                } else {
                    $configObj->setIsActive(0);
                }
            }
            $this->_em->flush();

            //Remove apc cache entries while activating theme config
            $clubCacheKey = $clubObj->get('clubCacheKey');
            $cachingEnabled = $clubObj->get('caching_enabled');
            $prefixName = 'theme_config';
            if ($cachingEnabled) {
                $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
                $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
            }
            //Remove apc cache entries while activating theme config
            $flag = 1;
        }
        
        return $flag;
    }

    /**
     * get color scheme list
     *
     * @param integer $configId - theme configuration id
     * @param integer $clubId - club id
     * @param object $container - container object of the club
     *
     * @return array
     */
    public function getAllThemeColors($configId, $clubId, $container)
    {
        $colorList = $this->createQueryBuilder('tc')
            ->select("tcs.id, IDENTITY(tcs.theme) as themeId, IDENTITY(tc.colorScheme) as colorSchemeId, tcs.colorSchemes, tcs.isDefault")
            ->addSelect("(SELECT COUNT(IDENTITY(tc2.colorScheme)) FROM CommonUtilityBundle:FgTmThemeConfiguration tc2 WHERE tc2.colorScheme = tcs.id AND tc2.isDeleted = 0) AS colorSchemeCount")
            ->leftJoin('CommonUtilityBundle:FgTmThemeColorScheme', 'tcs', 'WITH', 'tcs.theme = tc.theme')
            ->where('tc.id=:configId')
            ->andWhere('tcs.club=:clubId OR tcs.isDefault=1')
            ->setParameters(array('configId' => $configId, 'clubId' => $clubId))
            ->getQuery()
            ->getArrayResult();
        $finalColorArr = array();
        foreach ($colorList as $val) {
            $colorSchemes = json_decode($val['colorSchemes'], true);
            $finalColorArr[$val['id']]['themeId'] = $val['themeId'];
            $finalColorArr[$val['id']]['colorSchemeId'] = $val['colorSchemeId'];
            foreach ($colorSchemes as $colorKey => $colorVal) {
                $finalColorArr[$val['id']]['colorSchemes'][$colorKey]['title'] = $container->get('translator')->trans($colorKey);
                $finalColorArr[$val['id']]['colorSchemes'][$colorKey]['value'] = $colorVal;
            }
            $finalColorArr[$val['id']]['idDefault'] = $val['isDefault'];
            $finalColorArr[$val['id']]['colorSchemeCount'] = $val['colorSchemeCount'];
        }

        return $finalColorArr;
    }

    /**
     * save theme configuration
     *
     * @param interger $contactId - contact id
     * @param interger $clubId - club id
     * @param array $themeConfigData - theme configuration data
     * @return integer
     */
    public function saveThemeConfiguation($contactId, $clubId, $themeConfigData)
    {

        $clubObj = $this->_em->getReference('CommonUtilityBundle:FgClub', $clubId);
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        $themeObj = $this->_em->getReference('CommonUtilityBundle:FgTmTheme', $themeConfigData['themeId']);
        $colorSchemeObj = $this->_em->getReference('CommonUtilityBundle:FgTmThemeColorScheme', $themeConfigData['colorScemeId']);
        
        $themeConfObj = new \Common\UtilityBundle\Entity\FgTmThemeConfiguration();
        $themeConfObj->setClub($clubObj);
        $themeConfObj->setTheme($themeObj);
        $themeConfObj->setTitle($themeConfigData['title']);
        if($themeConfigData['themeId']==1){
           $themeConfObj->setHeaderScrolling($themeConfigData['headerStyle']); 
        }elseif($themeConfigData['themeId']==2){
            $themeConfObj->setHeaderPosition($themeConfigData['theme2head']);                
            $themeConfObj->setHeaderLogoPosition($themeConfigData['logoStyle']);
        }
        
        $themeConfObj->setIsActive(0);
        $themeConfObj->setColorScheme($colorSchemeObj);
        $themeConfObj->setCreatedAt(new \DateTime("now"));
        $themeConfObj->setCreatedBy($contactObj);
        $themeConfObj->setIsDeleted(0);

        $this->_em->persist($themeConfObj);
        $this->_em->flush();

        return $themeConfObj->getId();
    }

    /**
     * activate color in theme configuration
     *
     * @param interger $config    config id
     * @param interger $color     color scheme id
     * @param interger $contactId contact id
     * @param Object   $clubObj   club object which contains the caching parameters
     *
     * @return interger
     */
    public function activateColorScheme($config, $color, $contactId, $clubObj)
    {
        $contactObj = $this->_em->getReference('CommonUtilityBundle:FgCmContact', $contactId);
        $colorSchemeObj = $this->_em->getReference('CommonUtilityBundle:FgTmThemeColorScheme', $color);
        $themeConfigObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($config);
        $themeConfigObj->setColorScheme($colorSchemeObj);
        $themeConfigObj->setUpdatedAt(new \DateTime("now"));
        $themeConfigObj->setUpdatedBy($contactObj);

        $this->_em->persist($themeConfigObj);
        $this->_em->flush();

        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme

        return $themeConfigObj->getId();
    }

    /**
     * To change the configuration title
     *
     * @param integer $config theme configuration id
     * @param string  $title  title of the configuration tobe updated
     */
    public function changeConfigTitle($config, $title)
    {
        $themeConfigObj = $this->find($config);
        $themeConfigObj->setTitle($title);

        $this->_em->persist($themeConfigObj);
        $this->_em->flush();

        return;
    }

    /**
     * To save the full size image data
     *
     * @param array $imageDetails uploaded image details
     * @param integer $configId theme configuration id
     * @param object $container container
     */
    public function saveFullScreenDetails($imageDetails, $configId, $container)
    {
        $clubId = $container->get('club')->get('id');
        $contact = $container->get('contact'); //contact Obj
        $contactId = $contact->get('id');
        //Basic details save
        $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        if ($configObj && isset($imageDetails['backgroundimage']['fullscreen']['config'])) {
            $configObj->setBgImageSelection($imageDetails['backgroundimage']['fullscreen']['config'][$configId]["type"]);
            if ($imageDetails['backgroundimage']['fullscreen']['config'][$configId]["value"]) {
                $configObj->setBgSliderTime($imageDetails['backgroundimage']['fullscreen']['config'][$configId]["value"]);
            }
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $configObj->setUpdatedAt(new \DateTime('now'));
            $configObj->setUpdatedBy($contactObj);
            $this->_em->persist($configObj);
            $this->_em->flush();
        }

        //Fullscreen image save

        if (isset($imageDetails['backgroundimage']['fullscreen']["media"]["images"]['new'])) {
            $uploadedFileDetails = $this->moveMediaFileToFolder($container, $imageDetails['backgroundimage']['fullscreen']["media"]["images"]['new'], $clubId);

            foreach ($uploadedFileDetails as $values) {
                $itemDetails['type'] = 'IMAGE';
                $itemDetails['filepath'] = $values['filepath'];
                $itemDetails['fileName'] = $values['filepath'];
                $itemDetails['source'] = "cmsimageelement";
                $galleryId = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->insertNewItem($container, $itemDetails, $clubId, $contactId, '', 'cms_background_image');

                $backgroundObj = new \Common\UtilityBundle\Entity\FgTmThemeBgImages();
                $backgroundObj->setSortOrder($values['sort_order']);

                $galleryObj = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->find($galleryId);
                $backgroundObj->setGalleryItem($galleryObj);
                $backgroundObj->setConfiguration($configObj);

                $backgroundObj->setBgType('full_screen');
                $backgroundObj->setIsScrollable(0);
                $backgroundObj->setBgRepeat(null);
                $backgroundObj->setPositionVertical(null);
                $backgroundObj->setPositionHorizontal(null);
                $this->_em->persist($backgroundObj);
                $this->_em->flush();
            }
        }

        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $container->get('club')->get('clubCacheKey');
        $cachingEnabled = $container->get('club')->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme
    }

    /**
     * To move temporary file from temp folder to gallery folder of corresponding club
     *
     * @param object $container container
     * @param array $mediaItemArray uploades image item array
     * @param integer $clubId logged club id
     *
     * @return array moved image details
     */
    private function moveMediaFileToFolder($container, $mediaItemArray, $clubId)
    {
        $newImageArray = [];
        foreach ($mediaItemArray as $key => $values) {
            $galleryImgArr[] = $values['filepath'];
            $orgImgNameArr[] = $values['fileName'];
            $newImageArray[] = $values;
        }
        $galleryListObj = new GalleryList($container, 'gallery');
        $uploadedFileDetails = $galleryListObj->movetoclubgallery($galleryImgArr, $orgImgNameArr, $clubId);
        //update filepath in $mediaItemArray
        foreach ($uploadedFileDetails['fileName'] as $key => $uploadedFilePath) {
            $newImageArray[$key]['filepath'] = $uploadedFilePath;
        }

        return $newImageArray;
    }

    /**
     * To save the gallery image for original size bg
     *
     * @param array  $imageDetails updated image details
     * @param int    $configId     theme configuaration id club
     * @param Object $clubObj      clubObject
     */
    public function saveOriginalSizeDetails($imageDetails, $configId, $clubObj)
    {
        $allimageDetails = isset($imageDetails['update']) ? $imageDetails['update'] : $imageDetails;
        foreach ($allimageDetails as $key => $values) {
            $backgroundObj = '';
            //for update check
            if (isset($imageDetails['update'])) {
                $backgroundObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeBgImages')->find($key);
            }
            if ($backgroundObj != '') {
                //for delete check
                if (isset($values['is_deleted'])) {
                    $this->_em->remove($backgroundObj);
                } else {
                    //sort order updation
                    $backgroundObj->setSortOrder($values['sort_order']);
                    $this->_em->persist($backgroundObj);
                }
            } else {
                //new item creation
                $this->saveGalleryItems($values, 'full_screen', $configId);
            }
            $this->_em->flush();
        }
        //FAIR-2656 @added for Image Not Saving from gallery browser
        if (isset($imageDetails['update'])) {
            $browseimageDetails = $imageDetails;
            unset($browseimageDetails['update']);
            foreach ($browseimageDetails as $key => $values) {
                $this->saveGalleryItems($values, 'full_screen', $configId);
            }
        }
        //FAIR-2656
        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme
    }

    /**
     * To save the gallery browser image for original size bg
     *
     * @param array  $imageDetails updated image details
     * @param string $type      fullimage/orginal image
     * @param int    $configId     theme configuaration id club
     * 
     */
    private function saveGalleryItems($values, $type, $configId)
    {
        if ($values['itemid'] != '') {
            $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
            $backgroundObj = new \Common\UtilityBundle\Entity\FgTmThemeBgImages();
            $backgroundObj->setSortOrder($values['sort_order']);
            $galleryObj = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->find($values['itemid']);
            $backgroundObj->setGalleryItem($galleryObj);
            $backgroundObj->setConfiguration($configObj);
            $backgroundObj->setBgType($type);
            $backgroundObj->setIsScrollable(0);
            $backgroundObj->setBgRepeat(null);
            $backgroundObj->setPositionVertical(null);
            $backgroundObj->setPositionHorizontal(null);
            $this->_em->persist($backgroundObj);
        }
    }

    /**
     * To save the new uploaded images
     *
     * @param array  $imageDetails updated image details
     * @param int    $configId     theme configuaration id club
     * @param object $container    container
     */
    public function saveOriginalBgDetails($imageDetails, $configId, $container)
    {
        $clubObj = $container->get('club');
        $clubId = $clubObj->get('id');
        $contact = $container->get('contact'); //contact Obj
        $contactId = $contact->get('id');
        //Basic details save
        $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);

        //Fullscreen image save
        $uploadedFileDetails = $this->moveMediaFileToFolder($container, $imageDetails, $clubId);
        foreach ($uploadedFileDetails as $values) {
            $itemDetails['type'] = 'IMAGE';
            $itemDetails['filepath'] = $values['filepath'];
            $itemDetails['fileName'] = $values['filepath'];
            $itemDetails['source'] = "gallery";
            $galleryId = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->insertNewItem($container, $itemDetails, $clubId, $contactId, '', 'cms_background_image');
            $backgroundObj = new \Common\UtilityBundle\Entity\FgTmThemeBgImages();
            $backgroundObj->setSortOrder($values['sort_order']);
            $galleryObj = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->find($galleryId);
            $backgroundObj->setGalleryItem($galleryObj);
            $backgroundObj->setConfiguration($configObj);

            $backgroundObj->setBgType('original_size');
            $backgroundObj->setIsScrollable($values['scrolling']);
            $backgroundObj->setBgRepeat($values['repeat']);
            $backgroundObj->setPositionVertical($values['positionVertical']);
            $backgroundObj->setPositionHorizontal($values['positionHorizontal']);
            $this->_em->persist($backgroundObj);
            $this->_em->flush();
        }

        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme
    }

    /**
     * To save the gallery image for original size bg
     *
     * @param array  $imageDetails updated image details
     * @param int    $configId     theme configuaration id club
     * @param Object $clubObj      clubObject
     */
    public function saveOriginalBgFromGallery($imageDetails, $configId, $clubObj)
    {
        $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
        //Fullscreen image save
        foreach ($imageDetails as $values) {
            $backgroundObj = new \Common\UtilityBundle\Entity\FgTmThemeBgImages();
            $backgroundObj->setSortOrder($values['sort_order']);
            $galleryObj = $this->_em->getRepository('CommonUtilityBundle:FgGmItems')->find($values['itemid']);
            $backgroundObj->setGalleryItem($galleryObj);
            $backgroundObj->setConfiguration($configObj);
            $backgroundObj->setBgType('original_size');
            $backgroundObj->setIsScrollable($values['scrolling']);
            $backgroundObj->setBgRepeat($values['repeat']);
            $backgroundObj->setPositionVertical($values['positionVertical']);
            $backgroundObj->setPositionHorizontal($values['positionHorizontal']);
            $this->_em->persist($backgroundObj);
            $this->_em->flush();
        }

        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme
    }

    /**
     * To update bg image data
     *
     * @param array   $imageDetails updated image details
     * @param integer $configId     theme configuaration id club
     * @param Object  $clubObj      clubObject
     */
    public function updateOriginalBgFromGallery($imageDetails, $configId, $clubObj)
    {

        foreach ($imageDetails as $key => $values) {
            $backgroundObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeBgImages')->find($key);
            if ($backgroundObj != '') {
                //for delete check
                if ($values['is_deleted'] == 1) {
                    $this->_em->remove($backgroundObj);
                } else {
                    $configObj = $this->_em->getRepository('CommonUtilityBundle:FgTmThemeConfiguration')->find($configId);
                    $backgroundObj->setConfiguration($configObj);
                    //for update the selected fields
                    foreach ($values as $field => $val) {
                        switch ($field) {
                            case 'positionHorizontal':
                                $backgroundObj->setPositionHorizontal($values['positionHorizontal']);
                                break;
                            case 'scrolling':
                                $backgroundObj->setIsScrollable($values['scrolling']);
                                break;
                            case 'repeat':
                                $backgroundObj->setBgRepeat($values['repeat']);
                                break;
                            case 'positionVertical':
                                $backgroundObj->setPositionVertical($values['positionVertical']);
                                break;
                            default:
                                $backgroundObj->setSortOrder($values['sort_order']);
                                break;
                        }
                    }

                    $this->_em->persist($backgroundObj);
                }
                $this->_em->flush();
            }
        }

        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme
    }

    /**
     * Function to get all header details for a specified theme configuration
     * @param integer $configId - theme configuration id
     *
     * @return array - header data
     */
    public function getThemeHeaderDetails($configId)
    {
        return $this->createQueryBuilder('tc')
                ->select("th.id , th.headerLabel,th.fileName,tc.id as configId")
                ->innerJoin('CommonUtilityBundle:FgTmThemeHeaders', 'th', 'WITH', 'th.configuration = tc.id')
                ->where('tc.id=:configId')
                ->setParameters(array('configId' => $configId))
                ->getQuery()
                ->getArrayResult();
    }

    /**
     * Function to change header type scrolling or sticky
     * @param integer $configId - theme configuration id
     * @param integer $configId - theme configuration id
     * @param integer $type - scrolling 1,sticky 0
     *
     * @return boolean
     */
    public function changeHeaderType($themeId, $config, $options, $clubObj)
    {
        $themeConfigObj = $this->find($config);
        if($themeId==1){
            if($options['scrolling']!='')
                $themeConfigObj->setHeaderScrolling($options['scrolling']);
        }else if($themeId==2){
            if($options['headoption']!='')
                $themeConfigObj->setHeaderPosition($options['headoption']);
            if($options['logoStyle']!='')
                $themeConfigObj->setHeaderLogoPosition($options['logoStyle']);
        }
        

        $this->_em->persist($themeConfigObj);
        $this->_em->flush();

        //Remove apc cache entries while activating color scheme
        $clubCacheKey = $clubObj->get('clubCacheKey');
        $cachingEnabled = $clubObj->get('caching_enabled');
        $prefixName = 'theme_config';
        if ($cachingEnabled) {
            $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
            $cacheDriver->setPrefixValueForDelete($clubCacheKey, $prefixName);
        }
        //Remove apc cache entries while activating color scheme

        return true;
    }

    /**
     * Function to check config exist in club
     * @param integer $configId - theme configuration id
     * @param integer $clubId - scrolling 1,sticky 0
     *
     * @return integer
     */
    public function checkConfiginClub($configId, $clubId)
    {
        $configCount = $this->createQueryBuilder('tc')
            ->select("COUNT(tc.id)")
            ->where('tc.id=:configId')
            ->andWhere('tc.club=:clubId')
            ->setParameters(array('configId' => $configId, 'clubId' => $clubId))
            ->getQuery()
            ->getSingleResult();

        return $configCount[1];
    }

    /**
     * Get All Active Theme Configuration
     *
     * @param object  $conn            Connection object
     * @param object  $container       Container
     * @param int     $clubId          Club Id
     * @param Integer $clubCacheKey    Cachekey used for caching
     * @param Integer $cacheLifeTime   Cache expiry time
     * @param boolean $cachingEnabled  Enable/Disable setting of caching
     * @param int     $configId        Congif id
     * @return type
     */
    public function getAllActiveThemeConfig($conn, $container, $clubId, $clubCacheKey, $cacheLifeTime, $cachingEnabled, $configId = '')
    {
        $cacheKey = str_replace('{{cache_area}}', 'theme_config', $clubCacheKey);
        $cacheDriver = $this->_em->getConfiguration()->getResultCacheImpl();
        $qb = $this->createQueryBuilder('C')
            ->select("TC.cssFilename as cssColorScheme, IDENTITY(TC.club) as colorSchemeClubId, IDENTITY(C.theme) as theme, C.id AS themeConfigId, IDENTITY(C.colorScheme) AS colorSchemeId")
            ->innerJoin('CommonUtilityBundle:FgTmTheme', 'T', 'WITH', 'C.theme = T.id')
            ->innerJoin('CommonUtilityBundle:FgTmThemeColorScheme', 'TC', 'WITH', 'C.colorScheme = TC.id')
            ->where('C.club =:clubId ')
            ->andWhere('C.isDeleted = 0 ');

        if ($configId != '') {
            $qb->andWhere('C.id = ' . $configId);
        } else {
            $qb->andWhere('C.isActive = 1');
        }
        $qb->setParameters(array('clubId' => $clubId));
        //$result =  $qb->getQuery()->getResult();
        $result = $cacheDriver->getCachedResult($qb, $cacheKey, $cacheLifeTime, $cachingEnabled);

        /* get the header configs  */
        $headerConfigs = $this->headerConfig($cacheKey, $cacheLifeTime, $cachingEnabled, $cacheDriver, $clubId, $configId, $result[0]['theme']);               
        /* get the background configs  */
        $result3 = $this->backgroundConfig($clubId, $cacheKey, $cacheLifeTime, $cachingEnabled, $cacheDriver, $configId);

        $finalResult = array();
        $finalResult['theme'] = $result[0]['theme'];
        $finalResult['header_options'] = $this->getHeaderOptions($result[0]['theme'], $headerConfigs);  
        $finalResult['bg_options'] = $result3;
        $finalResult['cssColorScheme'] = $result[0]['cssColorScheme'];
        $finalResult['cssFile'] = $headerConfigs['cssFile'];
        $finalResult['colorSchemeClubId'] = $result[0]['colorSchemeClubId'];
        $finalResult['themeConfigId'] = $result[0]['themeConfigId'];
        $finalResult['colorSchemeId'] = $result[0]['colorSchemeId'];

        return $finalResult;
    }
    
    /**
     * Method to get header options according to theme
     * 
     * @param int   $themeId themeId 
     * @param array $result  header details
     * 
     * @return array of header_label, type, headerPosition, headerLogoPosition
     */
    private function getHeaderOptions($themeId, $result)
    {
        $resultArray['header_label'] = $result['header_options'];
        switch ($themeId) {
            case 1;
                $resultArray['type'] = $result['header_type'];
                break;
            case 2;
                $resultArray['type'] = 'sticky';
                $resultArray['headerPosition'] = $result['headerPosition'];
                $resultArray['headerLogoPosition'] = $result['headerLogoPosition'];
                break;
        }

        return $resultArray;
    }
    
    /**
     * Get the header configuration
     *
     * @param int     $cacheKey       Cachekey used for caching
     * @param Integer $cacheLifeTime  Cache expiry time
     * @param Boolean $cachingEnabled Cache expiry time
     * @param object  $cacheDriver    Cache driver object
     * @param int     $clubId         Id of the logged in club
     * @param int     $configId       Config id
     * @param int     $themeId        Theme Id
     *
     * @return array header configuration data
     */    
    private function headerConfig($cacheKey, $cacheLifeTime, $cachingEnabled, $cacheDriver, $clubId, $configId = '', $themeId)
    {
        /**
         * get the header configs
         */
        $joinCondition = ($configId != '') ? $configId : "C.id";
        switch ($themeId) {                        
            case 2:
                $headerQry = "C.headerPosition as headerPosition, C.headerLogoPosition as headerLogoPosition";
                break;   
            case 1:
            default:
                $headerQry = "CASE WHEN C.headerScrolling=1 THEN 'scroll' ELSE 'sticky' END as header_type";
                break;                
        }
        
        $qb = $this->createQueryBuilder('C')
            ->select(" $headerQry, C.cssFilename as cssFile, H.headerLabel, H.fileName ")
            ->leftJoin('CommonUtilityBundle:FgTmThemeHeaders', 'H', 'WITH', "H.configuration = $joinCondition")
            ->where('C.club =:clubId ')
            ->andWhere('C.isDeleted = 0');
        if($configId != '') {
            $qb->andWhere("C.id = $configId ");
        } else {
            $qb->andWhere("C.isActive = 1");
        }
        $qb->setParameters(array('clubId' => $clubId));
        $result1 = $cacheDriver->getCachedResult($qb, $cacheKey, $cacheLifeTime, $cachingEnabled); 
        $headerConfig = $this->formatHeaderConfig($result1, $themeId);
            
        return $headerConfig;
    }
    
    /**
     * Method to format header configuration array
     * 
     * @param array $headerConfigs header configuration array
     * @param int   $themeId       themeId
     * 
     * @return array of formatted header configuration
     */
    private function formatHeaderConfig($headerConfigs, $themeId) {
        $returnArray['header_options'] = '';
        foreach($headerConfigs as $headerConfig) {
            switch ($themeId) {                        
                case 2:
                    $returnArray['headerPosition'] = $headerConfig['headerPosition'];
                    $returnArray['headerLogoPosition'] = $headerConfig['headerLogoPosition'];
                    break;   
                case 1:
                default:
                    $returnArray['header_type'] = $headerConfig['header_type'];
                    break;                
            }            
            $returnArray['cssFile'] = $headerConfig['cssFile'];
            if($headerConfig['headerLabel'] && $headerConfig['fileName']) {
                $returnArray['header_options'][$headerConfig['headerLabel']] = array('file_name' => $headerConfig['fileName']);
            }  
        }
        
        return $returnArray;
    }

    /**
     * background config
     *
     * @param int     $clubId         Id of the logged in club
     * @param int     $cacheKey       Cachekey used for caching
     * @param Integer $cacheLifeTime  Cache expiry time
     * @param Boolean $cachingEnabled Cache expiry time
     * @param int     $configId       Config id
     *
     * @return array background configuration data
     */
    private function backgroundConfig($clubId, $cacheKey, $cacheLifeTime, $cachingEnabled, $cacheDriver, $configId = '')
    {
        /**
         * get the background configs
         */
        $qb = $this->createQueryBuilder('C')
            ->select("C.bgImageSelection as bg_image_selection, GI.filepath as filepath, I.isScrollable as is_scrollable, C.bgSliderTime as bg_slider_time,I.bgType as bg_type,I.sortOrder as sort_order,I.positionHorizontal as position_horizontal,I.positionVertical as position_vertical,I.bgRepeat as bg_repeat")
            ->leftJoin('CommonUtilityBundle:FgTmThemeBgImages', 'I', 'WITH', 'I.configuration = C.id')
            ->leftJoin('CommonUtilityBundle:FgGmItems', 'GI', 'WITH', 'GI.id = I.galleryItem')
            ->where('C.club =:clubId ')
            ->andWhere('C.isDeleted = 0')
            ->orderBy('I.sortOrder');

        if ($configId != '') {
            $qb->andWhere('C.id = ' . $configId);
        } else {
            $qb->andWhere('C.isActive = 1');
        }
        $qb->setParameters(array('clubId' => $clubId));
        $result2 = $cacheDriver->getCachedResult($qb, $cacheKey, $cacheLifeTime, $cachingEnabled);

        $result3 = array();
        foreach ($result2 as $val) {
            if ($val['bg_type'] == 'full_screen' && ($val['bg_image_selection'] == 'random' || $val['bg_image_selection'] == 'random_start_image')) {
                $result3[$val['bg_type']][rand(0, 100)] = $val;
            } else {
                $result3[$val['bg_type']][] = $val;
            }
        }

        return $result3;
    }
    
}
