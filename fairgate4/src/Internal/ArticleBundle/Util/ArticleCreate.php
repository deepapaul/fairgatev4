<?php

/**
 * This class will validate the results article data to be inserted. Also it will save the details to the database.
 */
namespace Internal\ArticleBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\FilemanagerBundle\Util\FileUpload;
use Internal\GalleryBundle\Util\GalleryList;
use Common\UtilityBundle\Util\FgSettings;
use Common\UtilityBundle\Util\FgUtility;

/**
 * This class will validate the results article data to be inserted. Also it will save the details to the database.
 * 
 * @package 	Internal
 * @subpackage 	Article
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 * 
 */
class ArticleCreate
{
    /**
     * The container object
     * 
     * @var object 
     */
    private $container;
    
    /**
     * The entity manager object
     * 
     * @var object 
     */
    private $em;
    
    /**
     * The translator object
     * 
     * @var object 
     */
    private $translator;
    
    /**
     * The club object
     * 
     * @var object 
     */
    private $club;
    
    /**
     * The contact object
     * 
     * @var object 
     */
    private $contact;
    
    /**
     * The club id
     * 
     * @var int 
     */
    private $clubId;
    
    /**
     * The contact id
     * 
     * @var int 
     */
    private $contactId;
    
    /**
     * The club languages
     * 
     * @var array 
     */
    private $clubLanguages;
    
    /**
     * It can be club_default_language
     *
     * @var string
     */
    private $clubDefaultLanguage;
    
    /**
     * The data array
     * 
     * @var array 
     */
    private $dataArray;
    
    /**
     * The current article data
     * 
     * @var array 
     */
    private $currentArticleData;
    
    /**
     * The error message array
     * 
     * @var array 
     */
    private $errorMessageArray;

    /**
     * Construct method.
     *
     * @param object ContainerInterface $container Container object
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $this->em = $this->container->get('doctrine')->getManager();

        $this->club = $this->container->get('club');
        $this->contact = $this->container->get('contact');
        $this->translator = $this->container->get('translator');

        $this->clubId = $this->club->get('id');
        $this->contactId = $this->contact->get('id');

        $this->clubLanguages = $this->club->get('club_languages');
        $this->clubDefaultLanguage = $this->club->get('club_default_lang');
    }

    /**
     * The function to validate the article data.
     *
     * @return array $this->errorMessageArray error messages array
     */
    public function validateArticleData()
    {
        $articleData = $this->dataArray['article'];

        if (isset($articleData['settings'])) {
            $this->validateSettings($articleData['settings']);
        }

        if (isset($articleData['text'])) {
            $this->validateText($articleData['text']);
        }

        if (isset($articleData['media'])) {
            $this->validateMedia($articleData['media']);
        }

        if (isset($articleData['attachment'])) {
            $this->validateAttachment($articleData['attachment']);
        }

        return $this->errorMessageArray;
    }

    /**
     * The function to validate the article text data.
     *
     * @param array $textDataWithTranslation The array of error message
     */
    public function validateText($textDataWithTranslation)
    {
        $textData = $textDataWithTranslation[$this->clubDefaultLanguage];

        if (strlen($textData['title']) > 255) {
            $this->errorMessageArray['text']['title'][] = $this->translator->trans('ARTICLE_CREATE_TITLE_LONG');
        }
        if(mb_strlen($textData['teaser'], "utf-8") > 160){
            $this->errorMessageArray['text']['teaser'][] = $this->translator->trans('ARTICLE_CREATE_TEASER_LONG');
        }
    }

    /**
     * The function to validate the article media data.
     *
     * @param array $mediaData The array with image/video data
     */
    public function validateMedia($mediaData)
    {
        /////////////////////////////////////// Validate Image Data ///////////////////////////////////////////
        foreach ($mediaData['images']['new'] as $key => $imageData) {
            if ($imageData['fileName'] == '') {
                $this->errorMessageArray['media']['images']['new'][$key]['fileName'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_FILENAME_EMPTY');
            }
            if ($imageData['filepath'] == '') {
                $this->errorMessageArray['media']['images']['new'][$key]['filepath'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_FILE_EMPTY');
            }
        }
        foreach ($mediaData['images']['media'] as $key => $imageData) {
            if ($imageData['is_deleted'] != '' && $imageData['mediaid'] == '') {
                $this->errorMessageArray['media']['images'][$key]['itemid'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_FILE_EMPTY1');
            }
            if ($imageData['is_deleted'] == '' && ($imageData['mediaid'] == '' && $imageData['itemid'] == '')) {
                $this->errorMessageArray['media']['images'][$key]['itemid'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_FILE_EMPTY2');
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
        //////////////////////////////////////// Validate Video Data /////////////////////////////////////////////
        foreach ($mediaData['videos']['new'] as $key => $videoData) {
            if ($videoData['videoThumb'] == '') {
                $this->errorMessageArray['media']['videos']['new'][$key]['videoThumb'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_URL_EMPTY');
            }
            if ($videoData['videoThumbImg'] == '') {
                $this->errorMessageArray['media']['videos']['new'][$key]['videoThumbImg'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_THUMB_EMPTY');
            }
        }
        foreach ($mediaData['videos']['media'] as $key => $videoData) {
            if ($videoData['is_deleted'] != '' && $videoData['mediaid'] == '') {
                $this->errorMessageArray['media']['videos']['media'][$key]['id'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_FILE_EMPTY3');
            }
            if ($videoData['is_deleted'] == '' && ($videoData['mediaid'] == '' && $videoData['itemid'] == '')) {
                $this->errorMessageArray['media']['videos'][$key]['itemid'][] = $this->translator->trans('ARTICLE_CREATE_MEDIA_FILE_EMPTY4');
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    }

    /**
     * The function to validate the article attachment data.
     *
     * @param array $attachmentData The array with attachment data
     */
    public function validateAttachment($attachmentData)
    {
        /////////////////////////////////////// Validate Attachment Data ///////////////////////////////////////////
        foreach ($attachmentData['new'] as $key => $attachment) {
            if ($attachment['fileName'] == '') {
                $this->errorMessageArray['attachment']['new'][$key]['fileName'][] = $this->translator->trans('ARTICLE_CREATE_ATTACHMENT_FILENAME_EMPTY');
            }
            if ($attachment['randFileName'] == '') {
                $this->errorMessageArray['attachment']['new'][$key]['randFileName'][] = $this->translator->trans('ARTICLE_CREATE_ATTACHMENT_FILE_EMPTY');
            }
        }

        foreach ($attachmentData['filemanager'] as $key => $attachment) {
            if ($attachment['fileid'] == '') {
                $this->errorMessageArray['attachment']['filemanager'][$key]['fileid'][] = $this->translator->trans('ARTICLE_CREATE_ATTACHMENT_FILE_EMPTY');
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////
    }

    /**
     * The function to validate the settings.
     *
     * @param array $settingsData The array with settings data
     */
    public function validateSettings($settingsData)
    {
        if (isset($settingsData['categories']) && count($settingsData['categories']) == 0) {
            $this->errorMessageArray['settings']['categories'][] = $this->translator->trans('ARTICLE_CREATE_CATEGORY_EMPTY');
        }
        if (isset($settingsData['areas']) && count($settingsData['areas']) == 0) {
            $this->errorMessageArray['settings']['areas'][] = $this->translator->trans('ARTICLE_CREATE_AREA_EMPTY');
        }
    }

    /**
     * The function to save the article to the database.
     * If $articleId is specified the fucntion will edit the corresponding article
     * 
     * @param int|string $articleId The id of the article
     * 
     * @return int $articleId saved article id
     */
    public function saveArticle($articleId)
    {
        $articleData = $this->dataArray['article'];
        if (isset($articleData['settings'])) {
            $articleId = $this->saveSettings($articleData['settings'], $articleId);
        }

        if (isset($articleData['text'])) {
            $this->saveText($articleData['text'], $articleId);
        }

        if (isset($articleData['media'])) {
            $this->saveMedia($articleData['media'], $articleId);
        }

        if (isset($articleData['attachment'])) {
            $this->saveAttachment($articleData['attachment'], $articleId);
        }
        $this->saveDefaultLang($articleId);

        return $articleId;
    }

    /**
     * Method to save default club language entries to main table.
     * To handle scenarios when club default languages changes.
     *
     * @param int $articleId The id of the articleId
     */
    private function saveDefaultLang($articleId)
    {
        $this->em->getRepository('CommonUtilityBundle:FgCmsArticleText')
            ->saveDefaultLang($articleId, $this->clubDefaultLanguage);
        $this->em->getRepository('CommonUtilityBundle:FgGmItems')
            ->saveDefaultLang($this->container, $articleId, $this->clubDefaultLanguage);
    }

    /**
     * The function to save the article text to the database.
     *
     * @param array $textArray article text array
     * @param int   $articleId article Id
     * 
     * @return void
     */
    public function saveText($textArray, $articleId)
    {
        $textVersionId = $this->em->getRepository('CommonUtilityBundle:FgCmsArticleText')
            ->saveArticleText($textArray, $this->contactId, $articleId, $this->clubDefaultLanguage);

        //Save to i18n table
        $this->em->getRepository('CommonUtilityBundle:FgCmsArticleTextI18n')
            ->saveArticleTexti18n($textArray, $textVersionId, $this->clubLanguages);

        //Update the article table
        $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')
            ->saveArticleTextVersion($textVersionId, $articleId);

        //Save position
        return;
    }

    /**
     * The function to save the article media to the database.
     * This function will add the new data that is specified in ['new'] key. The data will added to items table
     * The media data that was added in the ['media'] key will be either deleted or edited.
     *
     * @param array $mediaArray article media array
     * @param int   $articleId  articleId
     */
    public function saveMedia($mediaArray, $articleId)
    {
        $deletedMediaArray = $mediaItemArray = array();

        foreach ($mediaArray as $type => $media) {
            foreach ($media as $items) {
                foreach ($items as $eachItem) {
                    $eachItem['source'] = 'article';
                    if (isset($eachItem['is_deleted']) && $eachItem['is_deleted'] == 1) {
                        //media to be deleted
                        $deletedMediaArray[] = $eachItem['mediaid'];
                    } else {
                        $eachItem['type'] = ($type == 'images') ? 'IMAGE' : 'VIDEO';
                        $eachItem = $this->writeVideoImageToTemp($eachItem);                        
                        $mediaItemArray[] = $eachItem;
                    }
                }
            }
        }

        $uploadedFileDetails = $this->moveMediaFileToFolder($this->container, $mediaItemArray, $this->clubId);
        //update filepath in $mediaItemArray
        foreach ($uploadedFileDetails['fileName'] as $key => $uploadedFilePath) {
            $mediaItemArray[$key]['filepath'] = $uploadedFilePath;
        }

        $articleMediaArray = $this->em->getRepository('CommonUtilityBundle:FgGmItems')
            ->saveMediaItem($this->container, $mediaItemArray, $this->clubId, $this->contactId, $this->clubLanguages, $this->clubDefaultLanguage);

        if (count($articleMediaArray) > 0) {
            //article media insertion
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticleMedia')->saveArticleMedia($articleMediaArray, $articleId);
        }
        //update sort-order in article media
        $this->em->getRepository('CommonUtilityBundle:FgCmsArticleMedia')->updateArticleMediaSortOrder($mediaItemArray);

        if (count($deletedMediaArray) > 0) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticleMedia')->removeArticleAttachment($deletedMediaArray);
        }
        if (isset($mediaArray['position'])) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->saveArticlePosition($mediaArray['position'], $articleId);
        }
    }
    
    /**
     * Method to check whether the type is video, then upload it to temp folder
     * 
     * @param array $eachItem uploaded media details
     * 
     * @return array uploaded media details with video image name
     */
    private function writeVideoImageToTemp($eachItem) {
        if ($eachItem['type'] == 'VIDEO') {
            //write video image to temp folder
            $imageExtension = end(explode('.', $eachItem['videoThumbImg']));
            $content = file_get_contents($eachItem['videoThumbImg']);
            $fileName = md5(rand()) . '.' . $imageExtension;
            $fp = fopen('uploads/temp/' . $fileName, 'w');
            fwrite($fp, $content);
            fclose($fp);
            $eachItem['videoThumbImg'] = $fileName;
        }
        
        return $eachItem;         
    }

    /**
     * The function to save the article attachment to the database.
     * This function will add the new data that is specified in ['new'] key. The data will added to filemanager table
     * The media data that was added in the ['filemanager'] key will updated
     * The media data that was added in the ['article'] key will deleted.
     *
     * @param array $attachmentArray attachmentArray
     * @param int   $articleId       articleId
     */
    public function saveAttachment($attachmentArray, $articleId)
    {
        $attachmentIdArray = $attachmentIdToBeDeletedId = array();
        $newFileArray = array();
        foreach ($attachmentArray as $fileStatus => $attachments) {
            foreach ($attachments as $attachment) {
                if ($fileStatus == 'new') {
                    $newFileArray['fileName'][] = $attachment['fileName'];
                    $newFileArray['randFileName'][] = $attachment['randFileName'];
                } else if ($fileStatus == 'filemanager') {
                    $attachmentIdArray[] = $attachment['fileid'];
                } else if ($fileStatus == 'article') {
                    $attachmentIdToBeDeletedId[] = $attachment['attachmentid'];
                }
            }
        }

        if (count($newFileArray) > 0) {
            $newFileArray['module'] = 'ARTICLE';
            $fileUploadObj = new FileUpload($this->container);
            $fileManagerDetails = $fileUploadObj->movetoClubFilemanagerAction($newFileArray);
            $newFileManagerIdArray = $fileUploadObj->saveToFilemanager($fileManagerDetails);
            $attachmentIdArray = array_merge($attachmentIdArray, $newFileManagerIdArray);
        }

        //insert to article table attachment
        if (count($attachmentIdArray) > 0) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticleAttachments')
                ->saveArticleAttachment($attachmentIdArray, $articleId);
        }

        //insert to article table attachment
        if (count($attachmentIdToBeDeletedId) > 0) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticleAttachments')
                ->removeArticleAttachment($attachmentIdToBeDeletedId, $articleId);
        }
    }

    /**
     * The function to save the article settings data.
     * This function will add the new data that is specified in ['new'] key. The data will added to filemanager table
     * The media data that was added in the ['filemanager'] key will updated
     * The media data that was added in the ['article'] key will deleted.
     *
     * @param array      $settingsArray settingsArray
     * @param int|string $articleId     articleId
     *
     * @return int $articleId article-id
     */
    public function saveSettings($settingsArray, $articleId = '')
    {
        $articleId = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')
            ->saveArticle($settingsArray, $this->clubId, $this->contactId, $articleId, $settingsArray['isDraft']);

        //save areas
        if (isset($settingsArray['areas'])) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticleSelectedareas')
                ->saveArticleAreas($settingsArray['areas'], $articleId);
        }
        //save categories
        if (isset($settingsArray['categories'])) {
            $this->em->getRepository('CommonUtilityBundle:FgCmsArticleSelectedcategories')
                ->saveArticleCategories($settingsArray['categories'], $articleId);
        }

        return $articleId;
    }

    /**
     * The function to get the article data before save
     * The data from this array will be used compare and identify the changes to save the log.
     *
     * @param int/string $articleId articleId
     */
    public function beginLog($articleId)
    {
        if ($articleId != '') {
            //Get data from wrapper
            $articleObj = new ArticleData($this->container);
            $this->currentArticleData = $articleObj->getArticleDatas($articleId);
        }
    }

    /**
     * The function will save the media from the temp to to the club folder.
     *
     * @param object $container      container object
     * @param array  $mediaItemArray mediaItemArray
     * @param int    $clubId         Current clubId
     *
     * @return array uploadedFileDetails
     */
    private function moveMediaFileToFolder($container, $mediaItemArray, $clubId)
    {
        foreach ($mediaItemArray as $media) {
            if ($media['itemid'] == '' && $media['type'] == 'IMAGE') {
                $galleryImgArr[] = $media['filepath'];
                $orgImgNameArr[] = $media['fileName'];
            }
            if ($media['type'] == 'VIDEO' && ($media['videoThumbImg'])) {
                $galleryImgArr[] = $media['videoThumbImg'];
                $orgImgNameArr[] = $media['videoThumbImg'];
            }
        }
        $galleryListObj = new GalleryList($container, 'article');

        return $galleryListObj->movetoclubgallery($galleryImgArr, $orgImgNameArr, $clubId);
    }

    /**
     * This function is to get my club, teams, workgroup to be listed in areas dropdown in all article section.
     *
     * @return array $myAreas array of clubtitle,teams and workgroups
     */
    public function getMyClubAndTeamsAndWorkgroups()
    {
        $myAreas = array();
        $contact = $this->container->get('contact');
        $allTeams = $contact->get('teams');
        $allWorkgroups = $contact->get('workgroups');
        $club = $this->container->get('club');

        $isClubOrSuperAdmin = (count($contact->get('mainAdminRightsForFrontend')) > 0) ? 1 : 0;
        $isClubArticleAdmin = in_array('ROLE_ARTICLE', $contact->get('allRights')) ? 1 : 0;

        if ($isClubOrSuperAdmin || $isClubArticleAdmin) {
            $myAreas['club'] = ucfirst($club->get('title'));
            if ($isClubOrSuperAdmin) {
                $myAreas['teams'] = $allTeams;
                $myAreas['workgroups'] = $allWorkgroups;
            } else {

                $workgroupCatId = $club->get('club_workgroup_id');
                $teamCatId = $club->get('club_team_id');
                $assignedRoles = $this->em->getRepository('CommonUtilityBundle:FgRmRole')->getAllActiveRolesOfAClub($this->container, array($teamCatId, $workgroupCatId));
                $myAreas['teams'] = $assignedRoles['teams'];
                $myAreas['workgroups'] = $assignedRoles['workgroups'];
            }
        } else {
            $myTeams = $myWorkgroups = $assignedTeams = $assignedWorkgroups = array();

            $clubRoleRightsGroupWise = $this->contact->get('clubRoleRightsGroupWise');
            $roleTeamAdmins = ($clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['teams']) ? $clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['teams'] : array();
            $roleWorkgroupAdmins = ($clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['workgroups']) ? $clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['workgroups'] : array();
            $roleTeamArticleAdmins = ($clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['teams']) ? $clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['teams'] : array();
            $roleWorkgroupArticleAdmins = ($clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['workgroups']) ? $clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['workgroups'] : array();

            //role Ids of contact
            $myTeams = array_merge($roleTeamAdmins, $roleTeamArticleAdmins);
            $myWorkgroups = array_merge($roleWorkgroupAdmins, $roleWorkgroupArticleAdmins);

            foreach ($myTeams as $val) {
                $assignedTeams[$val] = $allTeams[$val];
            }
            $myAreas['teams'] = $assignedTeams;

            foreach ($myWorkgroups as $val) {
                $assignedWorkgroups[$val] = $allWorkgroups[$val];
            }
            $myAreas['workgroups'] = $assignedWorkgroups;
        }

        return $myAreas;
    }

    /**
     * Method to get all article categories to list.
     *
     * @return array all article categories
     */
    public function getAllArticleCategories()
    {
        return $this->em->getRepository('CommonUtilityBundle:FgCmsArticleCategory')->getArticleCategories($this->clubId, $this->clubDefaultLanguage);
    }

    /**
     * Method to check whether the user has right to access create/edit.
     *
     * @param int $articleId          articleId
     * @param int $isClubOrSuperAdmin flag 0/1
     * @param int $isClubArticleAdmin flag 0/1
     * @param int $isGroupAdmin       flag 0/1
     *
     * @return int flag 0/1
     */
    public function checkRightsForEdit($articleId, $isClubOrSuperAdmin, $isClubArticleAdmin, $isGroupAdmin)
    {
        if ($articleId) {
            $articleObj = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
            if (!$articleObj) {

                return 0;
            } else if ($articleObj->getClub()->getId() != $this->clubId) { //if article not belogs to that club
                return 0;
            }
        }

        if ($isClubOrSuperAdmin == '1' || $isClubArticleAdmin == '1') {
            $hasRight = 1;
        } elseif ($isGroupAdmin == '1') {
            if ($articleId != '') {  //case edit
                //role Ids of article
                $areasOfArticle = $this->em->getRepository('CommonUtilityBundle:FgCmsArticleSelectedareas')->getRolesOfArticle($articleId);
                $rolesOfArticle = $areasOfArticle['roles'];
                $clubRoleRightsGroupWise = $this->contact->get('clubRoleRightsGroupWise');
                $roleTeamAdmins = ($clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['teams']) ? $clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['teams'] : array();
                $roleWorkgroupAdmins = ($clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['workgroups']) ? $clubRoleRightsGroupWise['ROLE_GROUP_ADMIN']['workgroups'] : array();
                $roleTeamArticleAdmins = ($clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['teams']) ? $clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['teams'] : array();
                $roleWorkgroupArticleAdmins = ($clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['workgroups']) ? $clubRoleRightsGroupWise['ROLE_ARTICLE_ADMIN']['workgroups'] : array();
                //role Ids of contact
                $roleIdsOfGroupAdmin = array_merge($roleTeamAdmins, $roleWorkgroupAdmins, $roleTeamArticleAdmins, $roleWorkgroupArticleAdmins);
                //roleIds which article assigned to, but the user has no permission. Also article with 'club' area selected is also not accessible for one who is not super/club/article admin
                $nonAccessibleRoleIds = array_diff($rolesOfArticle, $roleIdsOfGroupAdmin);
                $hasRight = (count($nonAccessibleRoleIds) > 0 || ($areasOfArticle['isClub'])) ? 0 : 1;
            } else { //case create
                $hasRight = 1;
            }
        } else {
            $hasRight = 0;
        }

        return $hasRight;
    }

    /**
     * Method to get club settings.
     *
     * @return array array of global club settings
     */
    public function getArticleClubSettings()
    {
        return $this->em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($this->clubId);
    }

    /**
     * Function to set paramter dataArray.
     *
     * @param array $dataArray array to set
     */
    public function setDataArray($dataArray)
    {
        $this->dataArray = $dataArray;
    }

    /**
     * The function to save the article log data.
     *
     * This function will compare the data with the initail set data to identify the chages
     *
     * @param int $articleId articleId
     */
    public function saveLog($articleId)
    {
        $articleObj = new ArticleData($this->container);
        $newArticleData = $articleObj->getArticleDatas($articleId);
        $toBeLoggedFields = array('comments', 'scope', 'author', 'area', 'category', 'expiry_date', 'publication_date', 'media', 'attachment');
        $event = ($this->currentArticleData == '') ? 'create' : 'edit';
        $logArray = array();
        $conn = $this->em->getConnection();
        foreach ($toBeLoggedFields as $field) {
            switch ($field) {
                case 'comments':
                    $commentString = array('' => $this->translator->trans('ARTICLE_NO'), 0 => $this->translator->trans('ARTICLE_NO'), 1 => $this->translator->trans('ARTICLE_COMMENT_LOGIN_USER'), 2 => $this->translator->trans('ARTICLE_COMMENT_EVERYBODY'));
                    $newValue = $commentString[$newArticleData['article']['settings']['allowcomment']];
                    $oldValue = ($event == 'create') ? '' : $commentString[$this->currentArticleData['article']['settings']['allowcomment']];
                    if ($newValue != $oldValue) {
                        $logArray[] = "($this->clubId,$articleId,now(),'comments','data','$newValue','$oldValue',$this->contactId)";
                    }
                    break;
                case 'scope':
                    $scopeString = array('PUBLIC' => $this->translator->trans('SCOPE_PUBLIC'), 'INTERNAL' => $this->translator->trans('SCOPE_INTERNAL'));
                    $newValue = $scopeString[$newArticleData['article']['settings']['scope']];
                    $oldValue = ($event == 'create') ? '' : $scopeString[$this->currentArticleData['article']['settings']['scope']];
                    if ($newValue != $oldValue) {
                        $logArray[] = "($this->clubId,$articleId,now(),'scope','data','$newValue','$oldValue',$this->contactId)";
                    }
                    break;
                case 'author':
                    $newValue = $newArticleData['article']['settings']['author'];
                    $oldValue = $this->currentArticleData['article']['settings']['author'];
                    if ($newValue != $oldValue) {
                        $logArray[] = "($this->clubId,$articleId,now(),'author','data','$newValue','$oldValue',$this->contactId)";
                    }
                    break;
                case 'expiry_date':
                    $newValue = $newArticleData['article']['settings']['expirydate'];
                    $oldValue = $this->currentArticleData['article']['settings']['expirydate'];
                    $archivedText = $this->translator->trans('ARTICLE_ARCHIVED');
                    $activeText = $this->translator->trans('ARTICLE_ACTIVE');

                    $newStatus = $activeText;
                    $oldStatus = $activeText;
                    if ($newValue != $oldValue) {
                        if ($newValue != '') {
                            $newValueObj = \DateTime::createFromFormat(FgSettings::$PHP_DATETIME_FORMAT, $newValue);
                            $newValueFormatted = $newValueObj->format('Y-m-d H:i:s');
                            $newValueObjNow = date_create('NOW');
                            if ($newValueObj < $newValueObjNow) {
                                $newStatus = $archivedText;
                            }
                        }
                        if ($oldValue != '') {
                            $oldValueObj = \DateTime::createFromFormat(FgSettings::$PHP_DATETIME_FORMAT, $oldValue);
                            $oldValueFormatted = $oldValueObj->format('Y-m-d H:i:s');
                            $oldValueObjNow = date_create('NOW');
                            if ($oldValueObj < $oldValueObjNow) {
                                $oldStatus = $archivedText;
                            }
                        }
                        $logArray[] = "($this->clubId,$articleId,now(),'expiry_date','data','$newValueFormatted','$oldValueFormatted',$this->contactId)";
                    }
                    if ($event == 'create') {
                        $logArray[] = "($this->clubId,$articleId,now(),'status','data','$newStatus','',$this->contactId)";
                    } else {
                        if ($newStatus != $oldStatus) {
                            $logArray[] = "($this->clubId,$articleId,now(),'status','data','$newStatus','$oldStatus',$this->contactId)";
                        }
                    }
                    break;
                case 'publication_date':
                    $newValue = $newArticleData['article']['settings']['publicationdate'];
                    $oldValue = $this->currentArticleData['article']['settings']['publicationdate'];
                    if ($newValue != $oldValue) {
                        if ($newValue != '') {
                            $newValueObj = \DateTime::createFromFormat(FgSettings::$PHP_DATETIME_FORMAT, $newValue);
                            $newValueFormatted = $newValueObj->format('Y-m-d H:i:s');
                        }
                        if ($oldValue != '') {
                            $oldValueObj = \DateTime::createFromFormat(FgSettings::$PHP_DATETIME_FORMAT, $oldValue);
                            $oldValueFormatted = $oldValueObj->format('Y-m-d H:i:s');
                        }
                        $logArray[] = "($this->clubId,$articleId,now(),'publication_date','data','$newValueFormatted','$oldValueFormatted',$this->contactId)";
                    }
                    break;
                case 'area':
                    $newValue = $newArticleData['article']['settings']['areaTitles'];
                    $oldValue = $this->currentArticleData['article']['settings']['areaTitles'];
                    $newValue = FgUtility::getSecuredDataString($newValue, $conn);
                    $oldValue = FgUtility::getSecuredDataString($oldValue, $conn);
                    if ($newValue != $oldValue) {
                        $logArray[] = "($this->clubId,$articleId,now(),'areas','area','$newValue','$oldValue',$this->contactId)";
                    }
                    break;
                case 'category':
                    $newValue = $newArticleData['article']['settings']['categoryTitles'];
                    $oldValue = $this->currentArticleData['article']['settings']['categoryTitles'];
                    $newValue = FgUtility::getSecuredDataString($newValue, $conn);
                    $oldValue = FgUtility::getSecuredDataString($oldValue, $conn);
                    if ($newValue != $oldValue) {
                        $logArray[] = "($this->clubId,$articleId,now(),'category','category','$newValue','$oldValue',$this->contactId)";
                    }
                    break;
                case 'attachment':
                    $newAttachmentArray = $newArticleData['article']['attachment'];
                    $oldAttachmentArray = $this->currentArticleData['article']['attachment'];
                    if ($event == 'create') {
                        $newValue = '';
                        foreach ($newAttachmentArray as $newAttachment) {
                            $newValue .= $newAttachment['attachmentName'] . ',';
                        }
                        $newValue = substr($newValue, 0, -1);
                        if ($newValue != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'attachmentname','attachment','$newValue','$oldValue',$this->contactId)";
                        }
                    }
                    if ($event == 'edit') {
                        $removedAttachments = array_diff_key($oldAttachmentArray, $newAttachmentArray);
                        foreach ($removedAttachments as $attachment) {
                            $removedAttachmentName .= $attachment['attachmentName'] . ',';
                        }
                        $removedAttachmentName = substr($removedAttachmentName, 0, -1);
                        if ($removedAttachmentName != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'attachmentname','attachment','','$removedAttachmentName',$this->contactId)";
                        }

                        $newlyAttachments = array_diff_key($newAttachmentArray, $oldAttachmentArray);
                        foreach ($newlyAttachments as $attachment) {
                            $videoUrl .= $attachment['attachmentName'] . ',';
                        }
                        $newlyAttachmentName = substr($newlyAttachmentName, 0, -1);
                        if ($newlyAttachmentName != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'attachmentname','attachment','$newlyAttachmentName','',$this->contactId)";
                        }
                    }
                    break;
                case 'media':
                    $newMediaDetail = $newArticleData['article']['media'];
                    $oldMediaArray = $this->currentArticleData['article']['media'];
                    unset($newMediaDetail['position']);
                    unset($oldMediaArray['position']);

                    if ($event == 'create') {
                        foreach ($newMediaDetail as $media) {
                            $imageName .= ($media['type'] == 'images') ? $media['imageName'] . ',' : '';
                            $videoUrl .= ($media['type'] == 'videos') ? $media['videoThumbUrl'] . ',' : '';
                        }

                        $imageName = substr($imageName, 0, -1);
                        $videoUrl = substr($videoUrl, 0, -1);
                        if ($imageName != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'imagename','image','$imageName','',$this->contactId)";
                        }

                        if ($videoUrl != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'videourl','video','$videoUrl','',$this->contactId)";
                        }
                    }
                    if ($event == 'edit') {
                        $removedMedia = array_diff_key($oldMediaArray, $newMediaDetail);
                        foreach ($removedMedia as $media) {
                            $removedImageName .= ($media['type'] == 'images') ? $media['imageName'] . ',' : '';
                            $removedVideoUrl .= ($media['type'] == 'videos') ? $media['videoThumbUrl'] . ',' : '';
                        }
                        $removedImageName = substr($removedImageName, 0, -1);
                        $removedVideoUrl = substr($removedVideoUrl, 0, -1);
                        if ($removedImageName != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'imagename','image','','$removedImageName',$this->contactId)";
                        }

                        if ($removedVideoUrl != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'videourl','video','','$removedVideoUrl',$this->contactId)";
                        }

                        $newlyAddedMedia = array_diff_key($newMediaDetail, $oldMediaArray);
                        foreach ($newlyAddedMedia as $media) {
                            $newlyAddedImageName .= ($media['type'] == 'images') ? $media['imageName'] . ',' : '';
                            $newlyAddedVideoUrl .= ($media['type'] == 'videos') ? $media['videoThumbUrl'] . ',' : '';
                        }
                        $newlyAddedImageName = substr($newlyAddedImageName, 0, -1);
                        $newlyAddedVideoUrl = substr($newlyAddedVideoUrl, 0, -1);
                        if ($newlyAddedImageName != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'imagename','image','$newlyAddedImageName','',$this->contactId)";
                        }

                        if ($newlyAddedVideoUrl != '') {
                            $logArray[] = "($this->clubId,$articleId,now(),'videourl','video','$newlyAddedVideoUrl','',$this->contactId)";
                        }
                    }
                    break;
            }
        }

        $this->em->getRepository('CommonUtilityBundle:FgCmsArticleLog')->saveLog($logArray);
    }
}
