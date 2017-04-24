<?php

namespace Internal\GalleryBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Symfony\Component\HttpFoundation\File\File;

/**
 * This class is used to get the base query of events viewable to the logged in user 
 * based on his userrights, club and selected date span.
 */
class GalleryList
{

    private $container;
    private $club;
    private $contact;
    private $clubId;
    private $contactId;
    public $selectionFields = '';
    public $tableColumns = array();
    public $from;
    public $where = '';
    public $result;
    public $groupBy = '';
    public $orderBy = '';
    public $having = '';
    public $conn;
    public $mediaType = array();
    private $avastScan = false;

    /**
     * 
     * @param ContainerInterface $container Container object
     * @param string             $type  {gallery, sidebar}
     * @param date               $endDate    Interval end date
     * @param date               $strtDtTime Interval start date with time
     */
    public function __construct(ContainerInterface $container, $type = 'gallery')
    {
        $this->container = $container;
        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->clubId = $this->club->get('id');
        $this->contactId = $this->contact->get('id');
        $this->conn = $this->container->get('database_connection');
        $this->type = $type;
        $this->terminologyService = $this->container->get('fairgate_terminology_service');
    }

    /**
     * This function is used to add a column to the existing selected columns
     * 
     * @param string $column select statement
     * 
     * @return string $this->selectionFields
     */
    public function addColumn($column)
    {
        $this->selectionFields = ',`' . $column . '`';

        return $this->selectionFields;
    }

    /**
     * This function is used to add a column to the existing selected columns
     * 
     * @param string $column select statement
     * 
     * @return string $this->selectionFields
     */
    public function addHaving($having)
    {
        if ($having != '') {
            $this->having = $having;
        }
    }

    /**
     * This function is used to add the default columns and other area specific columns to select query
     * 
     * @param string $type The place for which the wrapper is been used {gallery, sidebar}
     * 
     * @return string $this->selectionFields
     */
    public function setColumns()
    {

        if ($this->type == 'sidebar') {
            $columnfields = self::getSidebarColumns();
        } else {
            $columnfields = self::getGalleryColumns();
        }

        $this->tableColumns = $columnfields;
        if (count($this->tableColumns) > 0) {
            $this->selectionFields = implode(', ', $columnfields);
        } else {
            $this->selectionFields = '*';
        }
    }

    /**
     * This function set the columns that need to be retirivied for gallery list
     * 
     * @return array $this->$columnfields
     */
    private function getGalleryColumns()
    {
        $columnfields = array();
        $columnfields['itemId'] = 'IT.id AS itemId';
        $columnfields['itemType'] = 'IT.type AS itemType';
        $columnfields['scope'] = 'IT.scope AS scope';
        $columnfields['filepath'] = 'IT.filepath AS filepath';
        $columnfields['fileSize'] = 'IT.file_size AS fileSize';
        $columnfields['videoThumbUrl'] = 'IT.video_thumb_url AS videoThumbUrl';
        $columnfields['itemDescription'] = 'COALESCE(ITi18.description_lang,IT.description) AS itemDescription';
        $columnfields['albumItemId'] = 'AIT.id AS albumItemId';
        $columnfields['albumItemSortOrder'] = 'AIT.sort_order AS albumItemSortOrder';
        $columnfields['albumItemIsCoverImage'] = 'AIT.is_cover_image AS albumItemIsCoverImage';
        $columnfields['albumId'] = 'A.id AS albumId';
        $columnfields['albumName'] = 'COALESCE(NULLIF(Ai18.name_lang, " "),A.name) AS albumName';
        $columnfields['albumType'] = 'G.type AS albumType';
        $columnfields['albumClub'] = 'G.club_id AS albumClub';
        $columnfields['albumRole'] = 'G.role_id AS albumRole';
        $columnfields['albumRoleName'] = 'R.title AS albumRoleName';
        $columnfields['albumParent'] = 'G.parent_id AS albumParent';
        $columnfields['albumSortOrder'] = 'G.sort_order AS albumSortOrder';
        $columnfields['itemSource'] = 'IT.source AS itemSource';
        $columnfields['roleSort'] = 'R.sort_order AS roleSort';
        $columnfields['catSort'] = 'TC.sort_order AS catSort';
        $columnfields['roleType'] = 'R.type AS roleType';

        return $columnfields;
    }

    /**
     * This function set the columns that need to be retirivied for sidebar
     * 
     * @return array $this->$columnfields
     */
    private function getSidebarColumns()
    {
        $columnfields = array();
        $columnfields['albumName'] = 'COALESCE(NULLIF(Ai18.name_lang, " "),A.name) AS albumName';
        $columnfields['clubName'] = "CL.title AS clubName";
        $columnfields['roleName'] = "CASE " .
            " WHEN (G.type = 'ROLE' AND R.type = 'T') THEN IF(RI18N.title_lang IS NOT NULL AND RI18N.title_lang != '', RI18N.title_lang, R.title)" .
            " WHEN (G.type = 'ROLE' AND R.type = 'W' AND R.is_executive_board = 1 ) THEN 'Executive Board'" .
            " WHEN (G.type = 'ROLE' AND R.type = 'W' AND R.is_executive_board != 1 ) THEN IF(RI18N.title_lang IS NOT NULL AND RI18N.title_lang != '', RI18N.title_lang, R.title)" .
            " END " .
            " AS roleName";
        $columnfields['albumType'] = 'G.type AS albumType';
        $columnfields['albumItemId'] = 'AIT.id AS albumItemId';
        $columnfields['albumId'] = 'A.id AS albumId';
        $columnfields['imageCount'] = '(COUNT(IT.id) + COALESCE(C_COUNT_DATA.C_COUNT,0)) AS imageCount';
        $columnfields['imageSubAlbumsCount'] = 'COALESCE(C_COUNT_DATA.C_COUNT,0) AS imageCountWithSub';
        $columnfields['albumParent'] = 'G.parent_id AS albumParent';
        $columnfields['albumRole'] = 'G.role_id AS albumRole';
        $columnfields['bookMarkId'] = 'B.id AS bookMarkId';
        $columnfields['bookMarkSort'] = 'B.sort_order AS bookMarkSort';
        $columnfields['albumSortOrder'] = 'G.sort_order AS albumSortOrder';

        $isCluborSuperAdmin = (count($this->contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isClubGalleryAdmin = in_array('ROLE_GALLERY', $this->contact->get('availableUserRights')) ? 1 : 0;
        $myAdminTeams = $this->getMyTeamsAndWorkgroups();
        if (count($myAdminTeams['ADMIN']) == 0) {
            $myAdminTeams['ADMIN'] = array(0);
        }
        $myAdminTeamString = implode(',', $myAdminTeams['ADMIN']);

        $columnfields['albumPrivilege'] = " CASE WHEN (G.role_id IN ($myAdminTeamString)) THEN 1 WHEN $isCluborSuperAdmin THEN 1 WHEN $isClubGalleryAdmin THEN 1 ELSE 0 END AS albumPrivilege";

        return $columnfields;
    }

    /**
     * This function is used for building the from condition
     * 
     * @param string $type The place for which the wrapper is been used {gallery, sidebar}
     * 
     * @return string $this->from From condition qry
     */
    public function setFrom()
    {
        if ($this->type == 'sidebar') {
            $this->from = self::getSidebarFrom();
        } else {
            $this->from = self::getGalleryFrom();
        }
        return $this->from;
    }

    /**
     * This function is used for building the from condition for the gallery list
     * 
     * @return string
     */
    private function getGalleryFrom()
    {
        $from = " fg_gm_items AS IT " .
            " LEFT JOIN fg_gm_item_i18n AS ITi18 ON (ITi18.id = IT.id AND ITi18.lang = '{$this->club->get("default_lang")}') " .
            " LEFT JOIN fg_gm_album_items AS AIT ON (AIT.items_id = IT.id) " .
            " LEFT JOIN fg_gm_album AS A ON (A.id = AIT.album_id) " .
            " LEFT JOIN fg_gm_album_i18n AS Ai18 ON (Ai18.id = A.id AND Ai18.lang = '{$this->club->get("default_lang")}') " .
            " LEFT JOIN fg_gm_gallery AS G ON (G.album_id= A.id) " .
            " LEFT JOIN fg_rm_role R ON (G.role_id = R.id) " .
            " LEFT JOIN fg_rm_category RC ON (R.category_id = RC.id) " .
            " LEFT JOIN fg_team_category AS TC ON (TC.id = R.team_category_id)";

        return $from;
    }

    /**
     * This function is used for building the from condition for the sidebar
     * 
     * @return string
     */
    private function getSidebarFrom()
    {
        $from = " fg_gm_album AS A " .
            " LEFT JOIN fg_gm_album_i18n AS Ai18 ON (Ai18.id = A.id AND Ai18.lang = '{$this->club->get("default_lang")}') " .
            " INNER JOIN fg_club AS CL ON (CL.id = A.club_id AND CL.id = {$this->clubId})" .
            " LEFT JOIN fg_gm_album_items AS AIT ON (AIT.album_id = A.id) " .
            " LEFT JOIN fg_gm_items AS IT ON (IT.id = AIT.items_id) " .
            " LEFT JOIN fg_gm_gallery AS G ON (G.album_id= A.id) " .
            " LEFT JOIN fg_gm_bookmarks AS B ON (B.album_id = A.id AND B.contact_id= {$this->contactId})" .
            " LEFT JOIN fg_rm_role R ON (G.role_id = R.id) " .
            " LEFT JOIN fg_rm_role_i18n AS RI18N ON (RI18N.id = R.id AND RI18N.lang = '{$this->club->get("default_lang")}')" .
            " LEFT JOIN fg_rm_category AS RC ON (RC.id = R.category_id) " .
            " LEFT JOIN fg_team_category AS TC ON (TC.id = R.team_category_id)" .
            " LEFT JOIN (" .
            " SELECT COUNT(C_AI.id) AS C_COUNT, C_G.parent_id AS albumId FROM fg_gm_gallery AS C_G" .
            " INNER JOIN fg_gm_album_items C_AI ON C_G.album_id = C_AI.album_id" .
            " WHERE C_G.club_id = {$this->clubId} GROUP BY C_G.parent_id) AS C_COUNT_DATA ON C_COUNT_DATA.albumId = G.album_id";
        return $from;
    }

    /**
     * This function is used to set the initial where condition
     * 
     */
    public function setCondition()
    {
        if ($this->type == 'sidebar') {
            $this->where = self::getSidebarWhereCondition();
        } else if ($this->type == 'media_browser') {
            $this->where = self::getBrowserWhereCondition();
        } else {
            $this->where = self::getGalleryWhereCondition();
        }
    }

    /**
     * This function is used to set the initial where condition for the gallery function
     * 
     */
    private function getGalleryWhereCondition()
    {
        $where = $this->getClubCondition();
        $where .= $this->getAlbumCondition();
        return $where;
    }

    /**
     * This function is used to set the initial where condition for the sidebar function
     * 
     */
    private function getSidebarWhereCondition()
    {
        $where = $this->getSidebarClubCondition();

        return $where;
    }

    /**
     * This function is used to set the initial where condition for the gallery function
     * 
     */
    private function getBrowserWhereCondition()
    {
        $where = $this->getClubCondition();
        $where .= $this->getMediaCondition();
        return $where;
    }

    /**
     * This function is used to set the club condition
     * 
     */
    private function getClubCondition()
    {
        $isAdmin = in_array('ROLE_GALLERY', $this->contact->get('availableUserRights')) ? 1 : 0;
        if ($isAdmin) {
            $roleCondition = " AND G.club_id  = {$this->clubId}";
        } else {
            $myGroups = $this->getMyTeamsAndWorkgroups();
            $myGroupsList = array_unique(array_merge($myGroups['MEMBER'], $myGroups['ADMIN']));
            if (count($myGroupsList) == 0)
                $myGroupsList = array(0);

            $roleCondition = " AND G.role_id IN (" . implode(',', $myGroupsList) . ") ";
        }
        $where = "( (IT.club_id = {$this->clubId} AND AIT.id IS NULL) OR ( (G.type = 'CLUB' AND G.club_id = {$this->clubId}) OR (G.type = 'ROLE' $roleCondition AND G.club_id = {$this->clubId}) ))";
        return $where;
    }

    /**
     * This function is used to set the club condition
     * 
     */
    private function getSidebarClubCondition()
    {
        $isAdmin = in_array('ROLE_GALLERY', $this->contact->get('availableUserRights')) ? 1 : 0;
        if ($isAdmin) {
            $roleCondition = " AND G.club_id  = {$this->clubId}";
        } else {
            $myGroups = $this->getMyTeamsAndWorkgroups();
            $myGroupsList = array_unique(array_merge($myGroups['MEMBER'], $myGroups['ADMIN']));
            if (count($myGroupsList) == 0)
                $myGroupsList = array(0);
            $roleCondition = " AND G.role_id IN (" . implode(',', $myGroupsList) . ") ";
        }
        $where = "( ( (G.type = 'CLUB' AND G.club_id = {$this->clubId}) OR (G.type = 'ROLE' $roleCondition  AND R.is_active=1 AND G.club_id = {$this->clubId}  ) ))";

        return $where;
    }

    /**
     * This function is used to select the selected albums 
     * 
     */
    private function getAlbumCondition()
    {
        if (empty($this->albumId)) {
            // Show all images 
            $where = "AND IT.source = 'gallery' AND (AIT.album_id IS NOT NULL )";
        } else if (is_int($this->albumId)) {
            //Show the selected album id
            $where = ' AND (AIT.album_id = ' . $this->albumId . ')';
        } else if (is_array($this->albumId) && (count($this->albumId) > 0)) {
            //show the selected album ids
            $where = " AND (AIT.album_id IN (" . implode(',', $this->albumId) . ") )";
        } else if ($this->albumId == 'NULL') {
            //show orphaned images
            $where = " AND (AIT.album_id IS NULL ) AND IT.source ='gallery'";
        } else if ($this->albumId == 'EXTERNAL') {
            //show external images
            $where = " AND IT.source !='gallery'";
        } else {
            $where = '';
        }
        return $where;
    }

    /**
     * This function is used to select the media type 
     * 
     */
    private function getMediaCondition()
    {
        if (empty($this->mediaType)) {
            // Show all media type 
            $where = '';
        } else {
            $where = " AND (IT.type IN ('" . implode(',', $this->mediaType) . "') )";
        }
        return $where;
    }

    /**
     * This function is used to get all teams and workgroups in which the logged in user have rights
     * 
     * @return array $myGroupsUnique My teams and workgroups
     */
    private function getMyTeamsAndWorkgroups()
    {
        $myAdminGroups = array();
        $myMemberGroups = array();
        $myGroups = array();

        $groupRights = $this->contact->get('clubRoleRightsGroupWise');

        if (isset($groupRights['ROLE_GROUP_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GROUP_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GROUP_ADMIN']['workgroups']);
        }
        if (isset($groupRights['ROLE_GALLERY_ADMIN']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GALLERY_ADMIN']['teams']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GALLERY_ADMIN']['teams']);
        }
        if (isset($groupRights['ROLE_GALLERY_ADMIN']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['ROLE_GALLERY_ADMIN']['workgroups']);
            $myAdminGroups = array_merge($myAdminGroups, $groupRights['ROLE_GALLERY_ADMIN']['workgroups']);
        }
        if (isset($groupRights['MEMBER']['teams'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['teams']);
        }
        if (isset($groupRights['MEMBER']['workgroups'])) {
            $myMemberGroups = array_merge($myMemberGroups, $groupRights['MEMBER']['workgroups']);
        }

        $myGroups['MEMBER'] = array_unique($myMemberGroups);
        $myGroups['ADMIN'] = array_unique($myAdminGroups);

        return $myGroups;
    }

    /**
     * This function is used to add a andWhere condition to the existing query.
     * 
     * @param string $condition Condition qry
     * 
     * @return string $this->where The where condition
     */
    public function addCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' AND (' . $condition . ' )';
        }

        return $this->where;
    }

    /**
     * This function is used to add a orWhere condition to the existing query.
     * 
     * @param string $condition Condition qry
     * 
     * @return string $this->where The where condition
     */
    public function orCondition($condition = '')
    {
        if ($condition != '') {
            $this->where .= ' OR (' . $condition . ' )';
        }

        return $this->where;
    }

    /**
     * This function is used to get the where condition
     * 
     * @return string $this->where The where condition
     */
    public function getCondition()
    {
        return $this->where;
    }

    /**
     * This function is used to add a order by condition to the qry.
     * 
     * @param string $orderColumn Qrder column
     */
    public function addOrderBy($orderColumn = '')
    {
        if ($orderColumn != '') {
            $this->orderBy = ' ' . $orderColumn;
        }
    }

    /**
     * This function is used to add a group by condition to the qry.
     * 
     * @param string $col
     */
    public function setGroupBy($column = '')
    {
        if ($column != '') {
            $this->groupBy = $column;
        }
    }

    /**
     * This function is used to get all gallery items.
     * 
     * @return string $this->result Final query string.
     */
    public function getMyImages()
    {
        $this->result = 'SELECT ' . $this->selectionFields . ' FROM ' . $this->from;
        $this->result .= ' WHERE ' . $this->where;

        if ($this->groupBy != '') {
            $this->result .= ' GROUP BY ' . $this->groupBy;
        }
        if ($this->having != '') {
            $this->result .= ' ' . $this->having;
        }
        if ($this->orderBy != '') {
            $this->result .= ' ORDER BY' . $this->orderBy;
        }
        return $this->result;
    }

    /**
     * This function is used to get all gallery items.
     * 
     * @return string $this->result Final query string.
     */
    public function getMySidebar()
    {
        $this->result = 'SELECT ' . $this->selectionFields . ' FROM ' . $this->from;
        $this->result .= ' WHERE ' . $this->where;

        if ($this->groupBy != '') {
            $this->result .= ' GROUP BY ' . $this->groupBy;
        }

        if ($this->orderBy != '') {
            $this->result .= ' ORDER BY' . $this->orderBy;
        }

        return $this->result;
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
     * The function to upload the file to the club gallery folder
     * 
     * @param array $galleryImgArr Image name
     * 
     */
    public function movetoclubgallery($galleryImgArr, $orgImgNameArr, $clubId, $imageDetails = array())
    {
        $uploadDirectory = FgUtility::getUploadDir() . "/";
        $this->dirCheck($uploadDirectory);
        $clubDirectory = $uploadDirectory . $clubId;
        $this->dirCheck($clubDirectory);
        $clubGalleryDirectory = $clubDirectory . '/gallery';
        $this->dirCheck($clubGalleryDirectory);
        //Original 
        $GalleryOriginalDirectory = $clubGalleryDirectory . '/original';
        $this->dirCheck($GalleryOriginalDirectory);
        //width1920*height1080
        $GalleryWidth1920Directory = $clubGalleryDirectory . '/width_1920';
        $this->dirCheck($GalleryWidth1920Directory);
        //width300*height300
        $GalleryWidth300Directory = $clubGalleryDirectory . '/width_300';
        $this->dirCheck($GalleryWidth300Directory);
        //width100*height100
        $GalleryWidth100Directory = $clubGalleryDirectory . '/width_100';
        $this->dirCheck($GalleryWidth100Directory);
        //width580*height580
        $GalleryWidth580Directory = $clubGalleryDirectory . '/width_580';
        $this->dirCheck($GalleryWidth580Directory);

        $GalleryWidth1140Directory = $clubGalleryDirectory . '/width_1140';
        $this->dirCheck($GalleryWidth1140Directory);
        
        //when the image is submitted with no change the image will not be there in the temp folder
        //beacuse it will already been moved common condition on edit
        $uploadedDirectory = $uploadDirectory . "/temp/";
        foreach ($galleryImgArr as $key => $document) {
            $newFileName = FgUtility::getFilename($GalleryOriginalDirectory, $orgImgNameArr[$key]);
            $doc = $uploadDirectory . 'temp/' . $document;
            $newDoc = $moveDoc = $newFileName;
//            $fileCheckStatus = $this->container->get('fg.avatar')->isForbidden($uploadedDirectory,$document);     
            if (file_exists($uploadedDirectory . $document)) {
                $imageDetails['fileName'][$key] = $newFileName;
                $attachmentObj = new File($uploadedDirectory . $document, false);

                copy($doc, $GalleryWidth1920Directory . '/' . $newDoc);
                copy($doc, $GalleryWidth300Directory . '/' . $newDoc);
                copy($doc, $GalleryWidth100Directory . '/' . $newDoc);
                copy($doc, $GalleryWidth580Directory . '/' . $newDoc);
                copy($doc, $GalleryWidth1140Directory . '/' . $newDoc);
                
                $attachmentObj->move($GalleryOriginalDirectory, $moveDoc);
            }
        }

        return $imageDetails;
    }

    /**
     * The function to check if directory exist else add a directory
     * 
     * @param string $directory Directory name
     * 
     */
    private function dirCheck($directory)
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}
