<?php
/**
 * GalleyPdo
 *
 * This class is used for handling gallery section.
 *
 * @package    CommonUtilityBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 *
 */
namespace Common\UtilityBundle\Repository\Pdo;

use Common\UtilityBundle\Util\FgSettings;

/**
 * Description of GalleryPdo
 *
 * @author pitsolutions.ch
 */
class GalleryPdo
{

    /**
     * Connection variable
     */
    public $conn;

    /**
     * Container variable
     */
    public $container;

    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
        $this->em = $this->container->get('doctrine')->getManager();
    }

    /**
     * Function to execute a sql query
     * 
     * @param string $sql Sql query
     * 
     * @return array $result Result array
     */
    public function executeQuery($sql)
    {
        $result = array();
        if ($sql != '') {
            $result = $this->conn->fetchAll($sql);
        }

        return $result;
    }

    /**
     * To update the fg item description 
     * @param integer $articleId  id of article
     * @param string $clubDefaultLanguage club default language
     */
    public function updateItemDescription($articleId, $clubDefaultLanguage)
    {
        $sql = "UPDATE fg_gm_items GI INNER JOIN fg_gm_item_i18n GIL ON (GIL.id = GI.id AND GIL.lang = :clubDefaultLanguage) " .
            "INNER JOIN fg_cms_article_media AM ON (AM.items_id = GI.id AND AM.article_id = :articleId) " .
            "SET GI.description = GIL.description_lang ";
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('articleId', $articleId);
        $stmt->bindValue('clubDefaultLanguage', $clubDefaultLanguage);
        $stmt->execute();
    }

    /**
     * To update the  description of  fgitem's  in i18n table
     * @param string $desc
     * @param integer $itemId
     * @param string $lang
     */
    public function updateItemDescriptioniI18n($desc, $itemId, $lang)
    {
        $qry = "INSERT INTO fg_gm_item_i18n(id, lang, description_lang, is_active) VALUES ($itemId, '$lang', '$desc', 1) "
            . "ON DUPLICATE KEY UPDATE id = $itemId, lang = '$lang', description_lang = '$desc'";
        $this->em->getConnection()->executeQuery($qry);
    }

    /**
     * To update the sort order of item
     * @param string  $albumItemIdsString
     * @param integer $albumId
     * @param integer $itemCount
     * @param integer $sortPosition
     */
    public function updateItemSortOrder($albumItemIdsString, $albumId, $itemCount, $sortPosition)
    {
        $conn = $this->em->getConnection();        
        $sortQuery = "SET @a = 0; UPDATE `fg_gm_album_items` AI SET AI.`sort_order` = @a:=@a+1  WHERE album_id = $albumId AND id NOT IN($albumItemIdsString) ORDER BY sort_order ASC; ";        
        $sortQuery .= " UPDATE `fg_gm_album_items` SET `sort_order`=`sort_order`+ $itemCount WHERE `sort_order`>=$sortPosition AND `album_id`=$albumId; ";
        $i = $sortPosition;
        $albumItemIds = explode(',', $albumItemIdsString);
        foreach ($albumItemIds as $itemId) {
            $sortQuery .= " UPDATE `fg_gm_album_items` SET `sort_order`=$i WHERE `id`=$itemId ; ";
            $i++;
        }        
        $conn->executeQuery($sortQuery);
    }

    /**
     * To move image from album
     * @param integer $albumId id of the album
     * @param integer $parentId parent id of the image
     * @param integer $deleteFlag delete flag
     */
    public function moveImagesFromAlbum($albumId, $parentId, $deleteFlag)
    {
        $query = ($deleteFlag == 1) ? "DELETE FROM fg_gm_album_items  WHERE album_id=$albumId" : "UPDATE fg_gm_album_items ai SET ai.album_id=$parentId WHERE ai.album_id =$albumId";
        $conn = $this->em->getConnection();
        $conn->executeQuery($query);
    }

    /**
     * To insertalbum language details
     * @param integer $albumId id of the album
     * @param string $lang language
     * @param string $title title of the album
     * @param integer $isActive state of album
     */
    public function insertAlbumLangDetails($albumId, $lang, $title, $isActive)
    {
        $qry = "INSERT INTO fg_gm_album_i18n (id, lang, name_lang, is_active)
                 VALUES($albumId, '$lang', '$title', $isActive)";
        $this->em->getConnection()->executeQuery($qry);
    }

    /**
     * To reorder the album item
     * @param integer $albumId id of the album
     * @param string $orderBy order by string
     */
    public function reorderAllAlbumItems($albumId, $orderBy)
    {
        $query = "SET @a = 0; UPDATE `fg_gm_album_items` SET `sort_order` = @a:=@a+1  WHERE album_id = $albumId  $orderBy";
        $this->em->getConnection()->executeQuery($query);
    }
}
