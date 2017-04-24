<?php

/**
 * FgCmsArticleRepository for managing the fg_cms_article table.
 */
namespace Common\UtilityBundle\Repository\Article;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Util\FgSettings;
use Internal\ArticleBundle\Util\ArticlesList;

/**
 * FgCmsArticleRepository for managing the fg_cms_article table.
 *
 * @package    InternalArticleBundle
 * @subpackage Repository
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class FgCmsArticleRepository extends EntityRepository
{

    /**
     * Function to create the entry for article.
     *
     * @param array      $articleSettingsArray The array with the details to be saved
     * @param int        $clubId               current club id
     * @param int        $contactId            current user id
     * @param int|string $articleId            The id of the article, when given that article will be updated
     * @param int        $isDraft              The flag to specify if the article is created as a draft
     *
     * @return int Article Id
     */
    public function saveArticle($articleSettingsArray, $clubId, $contactId, $articleId, $isDraft)
    {
        $dateFormat = FgSettings::getPhpDateTimeFormat();
        if ($articleId == '') {
            $articleObj = new \Common\UtilityBundle\Entity\FgCmsArticle();
            $articleObj->setClub($this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId));
            $articleObj->setCreatedBy($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId));
            $articleObj->setCreatedOn(new \DateTime('now'));
            $articleObj->setPosition('left_column');
            $articleObj->setScope(($articleSettingsArray['scope']) ? $articleSettingsArray['scope'] : 'PUBLIC');
            $articleObj->setCommentAllow(isset($articleSettingsArray['allowcomment']) ? $articleSettingsArray['allowcomment'] : 0);
            (!isset($articleSettingsArray['publication'])) ? $articleObj->setPublicationDate(new \DateTime('now')) : '';
            $articleObj->setShareWithLower(($articleSettingsArray['share']) ? $articleSettingsArray['share'] : 0);
        } else {
            $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
            $articleObj->setUpdatedBy($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId));
            $articleObj->setUpdatedOn(new \DateTime('now'));
            ($articleSettingsArray['scope']) ? $articleObj->setScope($articleSettingsArray['scope']) : '';
            (isset($articleSettingsArray['allowcomment'])) ? $articleObj->setCommentAllow($articleSettingsArray['allowcomment']) : '';
            ($articleSettingsArray['share']) ? $articleObj->setShareWithLower($articleSettingsArray['share']) : '';
        }
        if ($articleSettingsArray['publicationdate']) {
            $publishedDateObj = (\DateTime::createFromFormat($dateFormat, $articleSettingsArray['publicationdate'])) ? (\DateTime::createFromFormat($dateFormat, $articleSettingsArray['publicationdate'])) : new \DateTime('now');
            $articleObj->setPublicationDate($publishedDateObj);
        }

        ($articleSettingsArray['publication'] == 'now') ? $articleObj->setPublicationDate(new \DateTime('now')) : '';
        if ($articleSettingsArray['archive'] == 'never') {
            $articleObj->setExpiryDate(null);
        } else {
            if (isset($articleSettingsArray['expirydate'])) {
                $expiryDateObj = (\DateTime::createFromFormat($dateFormat, $articleSettingsArray['expirydate'])) ? (\DateTime::createFromFormat($dateFormat, $articleSettingsArray['expirydate'])) : null;
                $articleObj->setExpiryDate($expiryDateObj);
            }
        }
        //set archived
        if ((isset($articleSettingsArray['archive']) || isset($articleSettingsArray['expirydate'])) && ($articleObj->getExpiryDate() != null && $articleObj->getExpiryDate() != '') && ($articleObj->getExpiryDate() < new \DateTime('now'))) {
            $articleObj->setArchivedBy($this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId));
        } elseif ((isset($articleSettingsArray['archive']) || isset($articleSettingsArray['expirydate'])) && ($articleObj->getExpiryDate() == null || $articleObj->getExpiryDate() == '' || ($articleObj->getExpiryDate() > new \DateTime('now')))) {
            $articleObj->setArchivedBy(null);
        }

        ($articleSettingsArray['author']) ? $articleObj->setAuthor($articleSettingsArray['author']) : '';

        if ($isDraft >= 0) {
            $articleObj->setIsDraft($isDraft);
        }
        $articleObj->setIsDeleted(0);

        $this->_em->persist($articleObj);
        $this->_em->flush();

        return $articleObj->getId();
    }

    /**
     * Function to update the article version id.
     *
     * @param int $textVersionId The version of the text
     * @param int $articleId     The id of the article, when given that article will be updated
     *
     * @return void
     */
    public function saveArticleTextVersion($textVersionId, $articleId)
    {
        $textVersionObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleText')->find($textVersionId);
        $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
        $articleObj->setTextversion($textVersionObj);
        $this->_em->persist($articleObj);
        $this->_em->flush();

        return;
    }

    /**
     * Method to get all article media details.
     *
     * @param int    $articleId article Id
     * @param string $type      type of article
     *
     * @return array article media details array
     */
    public function getArticleMedia($articleId, $type = '')
    {
        $qb = $this->createQueryBuilder('A')
            ->select('AM.id AS mediaId, AM.sortOrder AS sortOrder, GI.id AS itemsId, GI.fileSize as mediaSize, GI.filepath as mediaFileName, A.position, '
                . 'GI.type, GI.filepath, GI.videoThumbUrl, GI.description as defaultDesc, '
                . "GROUP_CONCAT(GIL.lang SEPARATOR '|&&&|' ) as mediaLangArray, "
                . "GROUP_CONCAT(GIL.descriptionLang SEPARATOR '|&&&|' ) as mediaDescArray ")
            ->leftJoin('CommonUtilityBundle:FgCmsArticleMedia', 'AM', 'WITH', 'AM.article = A.id')
            ->leftJoin('CommonUtilityBundle:FgGmItems', 'GI', 'WITH', 'AM.items = GI.id')
            ->leftJoin('CommonUtilityBundle:FgGmItemI18n', 'GIL', 'WITH', 'GIL.id = GI.id')
            ->where('A.id=:articleId')
            ->orderBy('AM.sortOrder', 'ASC')
            ->groupBy('AM.id')
            ->setParameters(array('articleId' => $articleId));
        if ($type != '') {
            $qb->andWhere('GI.type=:type')->setParameter('type', $type);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Function to get all articles that I can edit.
     *
     * @param int $clubId             The id of the club
     * @param int $contactId          The id of the logged in contacts
     * @param int $myAdminRolesString The id of the roles where the logged in user is admin
     * @param int $isMainAdmin        The flag to identify if the user is admin or not
     * @param int $createdBy          The id of the logged in contacts
     *
     * @return array Article list array that I can edit
     */
    public function getEditableArticles($clubId, $contactId, $myAdminRolesString, $isMainAdmin, $createdBy)
    {
        if ($createdBy != '') {
            $createdByString = "AND A.createdBy = $contactId";
        } else {
            $createdByString = '';
        }

        $columns = array(
            'A.id AS articleId',
            "(CASE WHEN ( (GROUP_CONCAT(A_SEL.role ORDER BY A_SEL.role DESC ) = '$myAdminRolesString') OR ($isMainAdmin = 1)) THEN 1 ELSE 0 END) AS isEditable",
        );
        $editableArticles = $this->createQueryBuilder('A')
            ->select(implode(',', $columns))
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedareas', 'A_SEL', 'WITH', "(A.id = A_SEL.article AND A.club = $clubId $createdByString)")
            ->groupBy('A_SEL.article')
            ->having('isEditable = 1');

        return $editableArticles->getQuery()->getResult();
    }

    /**
     * Function to get the entire sidebar count of articles (General, Area, Category and Archived).
     *
     * @param obj $container The container interface object
     *
     * @return array Array of sidebar count details
     */
    public function getArticleSidebarCount($container)
    {
        $sidebarCount = array();
        $clubId = $container->get('club')->get('id');
        $contactId = $container->get('contact')->get('id');
        $isAdmin = in_array('ROLE_ARTICLE', $container->get('contact')->get('availableUserRights')) ? 1 : 0;
        $articlesList = new ArticlesList($container);
        //First get all the editable article Ids of a logged in user
        $editableArticleIds = $articlesList->getEditableArticleIds($clubId, true);
        $editableArtIds = implode(',', $editableArticleIds);
        if ($editableArtIds) {
            //Get the sidebar area article count based on the editable article
            $roleArticleCountArr = $this->getRoleArticleCount($editableArtIds, $clubId);
            //Get the sidebar category article count based on the editable article
            $sidebarCatCountArray = $this->getArticleCategoryCount($editableArtIds, $clubId, $isAdmin);
            //Get the sidebar category article count based on the editable article
            $sidebarTimeperiodCountArray = $this->getTimeperiodArticleCount($editableArtIds, $clubId, $container->get('club')->get('clubHeirarchy'));
            //Get the sidebar General and Archive block article count based on the editable article
            $sidebarGeneralCountArray = $this->getGeneralArticleCount($editableArtIds, $clubId, $contactId);
            //Merge all the sidebar blocks counts taken separately and return as a one array
            $sidebarCount = array_merge($roleArticleCountArr, $sidebarCatCountArray, $sidebarTimeperiodCountArray, $sidebarGeneralCountArray);
        }

        return $sidebarCount;
    }

    /**
     * Function to get the count of articles that have been added to the provided roles + Role Name + Role Id.
     *
     * @param array $editableArtIds Array of article that can be edited by the logged in user
     * @param int   $clubId         The id of the club
     *
     * @return array Array of sidebar count of the area article
     */
    private function getRoleArticleCount($editableArtIds, $clubId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $editableCondition = $editableArtIds ? "AND aas.article_id IN ($editableArtIds)" : '';
        $articleCountSql = "SELECT '' as 'categoryId',
							(CASE WHEN aas.is_club = 1 THEN 'CLUB'
                                 WHEN aas.role_id IS NULL THEN 'WA'
                                 ELSE aas.role_id END) as subCatId,
							(CASE
                                WHEN aas.is_club = 1 THEN COUNT(aas.is_club)
                                WHEN aas.role_id IS NULL THEN COUNT(a.id)
                                ELSE COUNT(aas.role_id) END) as sidebarCount,
							'AREAS' as 'dataType',
							'show' as 'action'
                            FROM fg_cms_article a
                            LEFT JOIN fg_cms_article_selectedareas aas ON aas.article_id = a.id $editableCondition
                            WHERE a.club_id = $clubId AND (a.expiry_date IS NULL OR a.expiry_date >= NOW())
                            GROUP BY aas.role_id, aas.is_club";

        return $conn->fetchAll($articleCountSql);
    }

    /**
     * Function to get the count of articles that have been added for category.
     *
     * @param array $editableArtIds Array of article that can be edited by the logged in user
     * @param int   $clubId         The id of the club
     * @param bool  $isAdmin        Logged in user is clubadmin/superadmin/fedadmin/articleadmin
     *
     * @return array Array of sidebar count of the category article
     */
    private function getArticleCategoryCount($editableArtIds, $clubId, $isAdmin)
    {
        $conn = $this->getEntityManager()->getConnection();
        $editableCondition = $editableArtIds ? "AND A_SEL_CAT.article_id IN ($editableArtIds)" : '';
        $queryString = "SELECT
						'' as 'categoryId',
						CASE WHEN (A_SEL_CAT.category_id IS NULL AND $isAdmin) THEN 'WA' ELSE A_SEL_CAT.category_id END as subCatId,
						CASE WHEN (A_SEL_CAT.category_id IS NULL AND $isAdmin) THEN count('WA') ELSE COUNT(A_SEL_CAT.category_id) END as sidebarCount,
						'CAT' as 'dataType',
						'show' as 'action'
						FROM fg_cms_article A
                        LEFT JOIN fg_cms_article_selectedcategories A_SEL_CAT ON A.id = A_SEL_CAT.article_id $editableCondition
                        WHERE A.club_id = $clubId AND (A.expiry_date IS NULL OR A.expiry_date >= NOW()) GROUP BY A_SEL_CAT.category_id";

        return $conn->fetchAll($queryString);
    }

    /**
     * Function to get the count of articles that have been added for each timeperiod.
     *
     * @param array $editableArtIds Array of article that can be edited by the logged in user
     * @param int   $clubId         The id of the club
     *
     * @return array Array of sidebar timeperiod count of article
     */
    private function getTimeperiodArticleCount($editableArtIds, $clubId, $clubHeirarchy)
    {
        $conn = $this->getEntityManager()->getConnection();
        $timePeriodCountArray = array();
        $yearFrom = $this->getArticleSortByYear($clubId, $clubHeirarchy);
        $timePeriodArray = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubTimeperiod($clubId, 2, $yearFrom);
        $editableCondition = $editableArtIds ? "AND A.id IN ($editableArtIds)" : '';
        if (count($timePeriodArray) > 0) {
            foreach ($timePeriodArray as $key => $timePeriod) {
                $timePeriodString[] = "SUM(CASE WHEN A.publication_date BETWEEN '" . $timePeriod['start'] . ' 00:00:00' . "' AND '" . $timePeriod['end'] . ' 23:59:59' . "' THEN 1 ELSE 0 END) AS T_" . $key;
            }
            $timePeriodQuery = implode(',', $timePeriodString);
            $clubArticles = "SELECT
                $timePeriodQuery
                FROM fg_cms_article A
                WHERE A.club_id = $clubId AND (A.expiry_date IS NULL OR A.expiry_date >= NOW()) $editableCondition";
            $timePeriodResultArray = $conn->fetchAll($clubArticles);
            foreach ($timePeriodArray as $key => $timePeriod) {
                $timePeriodCountArray[$key]['categoryId'] = '';
                $timePeriodCountArray[$key]['dataType'] = 'TIME';
                $timePeriodCountArray[$key]['action'] = 'show';
                $timePeriodCountArray[$key]['subCatId'] = $timePeriod['start'] . '__' . $timePeriod['end'];
                $timePeriodCountArray[$key]['sidebarCount'] = ($timePeriodResultArray[0]['T_' . $key]) ? $timePeriodResultArray[0]['T_' . $key] : 0;
            }
        }

        return $timePeriodCountArray;
    }

    /**
     * Get the sidebar General and Archive block article count based on the editable article.
     *
     * @param array $editableArtIds Array of article that can be edited by the logged in user
     * @param int   $clubId         The id of the club
     * @param int   $contactId      The id of the logged in user
     *
     * @return array array of general article count details
     */
    private function getGeneralArticleCount($editableArtIds, $clubId, $contactId)
    {
        $conn = $this->getEntityManager()->getConnection();
        $editableCondition = $editableArtIds ? "AND A.id IN ($editableArtIds)" : '';
        $generalArticleCount = array();
        $count = 0;
        $queryString = "SELECT
                        SUM( CASE WHEN A.expiry_date IS NULL OR A.expiry_date >= NOW() THEN 1 ELSE 0 END) AS AEA,
                        SUM( CASE WHEN (A.expiry_date IS NULL OR A.expiry_date >= NOW()) AND (A.created_by = $contactId) THEN 1 ELSE 0 END) AS MA,
						SUM( CASE WHEN (A.expiry_date <= NOW()) THEN 1 ELSE 0 END) AS ARCHIVE_ART
						FROM fg_cms_article A
                        WHERE A.club_id = $clubId AND A.is_deleted = 0 $editableCondition";
        $articleGeneralCountArray = $conn->fetchAll($queryString);
        $generalArticleCount[0] = array('categoryId' => '', 'dataType' => 'GEN', 'action' => 'show');
        foreach ($articleGeneralCountArray[0] as $key => $value) {
            $generalArticleCount[$count]['categoryId'] = '';
            $generalArticleCount[$count]['dataType'] = ($key == 'ARCHIVE_ART') ? 'ARCHIVE' : 'GEN';
            $generalArticleCount[$count]['action'] = 'show';
            $generalArticleCount[$count]['subCatId'] = $key;
            $generalArticleCount[$count]['sidebarCount'] = $value ? $value : 0;
            $count++;
        }

        return $generalArticleCount;
    }

    /**
     * Function to get the count of articles that have been added for each timeperiod.
     *
     * @param int $clubId        The id of club
     * @param int $clubhierarchy Club heirarchy array
     *
     * @return array  time period article array
     */
    public function getTimeperiodArticle($clubId, $clubhierarchy)
    {
        $result = array();
        $yearFrom = $this->getArticleSortByYear($clubId, $clubhierarchy);
        $timePeriodArray = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubTimeperiod($clubId, 2, $yearFrom);
        if (count($timePeriodArray) > 0) {
            foreach ($timePeriodArray as $key => $timePeriod) {
                $timePeriodQueryString[] = "SUM(CASE WHEN A.publicationDate BETWEEN '" . $timePeriod['start'] . ' 00:00:00' . "' AND '" . $timePeriod['end'] . ' 23:59:59' . "' THEN 1 ELSE 0 END) AS T_" . $key;
            }
            $clubArticles = $this->createQueryBuilder('A')
                ->select(implode(',', $timePeriodQueryString))
                ->where("A.club = $clubId")
                ->andWhere("(A.expiryDate > 'now()' OR A.expiryDate IS NULL)");

            $result = $clubArticles->getQuery()->getResult();
            foreach ($timePeriodArray as $key => $timePeriod) {
                $timePeriodArray[$key]['count'] = $result[0]['T_' . $key];
            }
        }

        return $timePeriodArray;
    }

    /**
     * Method to save article position.
     *
     * @param string     $position  position
     * @param int|string $articleId article Id
     *
     * @return void
     */
    public function saveArticlePosition($position, $articleId)
    {
        $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
        if ($articleObj) {
            (isset($position)) ? $articleObj->setPosition($position) : '';
            $this->_em->flush();
        }

        return;
    }

    /**
     * Method to archive article.
     *
     * @param array  $archiveData article details data
     * @param object $container   container interface object
     *
     * @return boolean true or false
     */
    public function archiveArticles($archiveData, $container)
    {
        $currDate = new \DateTime('now');
        $currentDate = $currDate->format('Y-m-d H:i:s');
        $clubId = $container->get('club')->get('id');
        $contactId = $container->get('contact')->get('id');
        $archived = $container->get('translator')->trans('ARTICLE_ARCHIVED');
        $active = $container->get('translator')->trans('ARTICLE_ACTIVE');

        foreach ($archiveData as $value) {
            $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($value);
            $articleId = $value;
            $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
            $expiryDate = (is_object($articleObj->getExpiryDate())) ? $articleObj->getExpiryDate()->format('Y-m-d H:i:s') : '';
            if ($articleObj) {
                $articleObj->setExpiryDate($currDate);
                $articleObj->setArchivedBy($contactObj);
                $articleObj->setUpdatedBy($contactObj);
                $articleObj->setUpdatedOn($currDate);
                $this->_em->persist($articleObj);
                $this->_em->flush();
                $logArray[] = "($clubId,$articleId,now(),'status','data','$archived','$active',$contactId)";
                $logArray[] = "($clubId,$articleId,now(),'expiry_date','data','$currentDate','$expiryDate',$contactId)";
            }
        }
        $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleLog')->saveLog($logArray);

        return true;
    }

    /**
     * Method to get article attachments.
     *
     * @param int $articleId article Id
     *
     * @return array array of article attachments
     */
    public function getArticleAttachments($articleId)
    {
        $qb = $this->createQueryBuilder('A')
            ->select('ATT.id as attachmentId, FV.filename as attachmentName, FV.size as attachmentSize, FILE.id as filemanagerId, FILE.virtualFilename as virtualFilename,FILE.encryptedFilename as encryptedFilename ')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleAttachments', 'ATT', 'WITH', 'ATT.article = A.id')
            ->leftJoin('CommonUtilityBundle:FgFileManager', 'FILE', 'WITH', 'ATT.filemanager = FILE.id')
            ->leftJoin('CommonUtilityBundle:FgFileManagerVersion', 'FV', 'WITH', 'FV.fileManager = FILE.id')
            ->where('A.id=:articleId')
            ->orderBy('LOWER(FV.filename)', 'ASC')
            ->setParameters(array('articleId' => $articleId));

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Method to get all article settings datas.
     *
     * @param int    $articleId           article Id
     * @param string $clubDefaultLanguage Club Langage
     * @param string $executiveBoardTerm  executive board terminology
     * @param string $clubTitle           ClubTitle
     *
     * @return array article settings data array.
     */
    public function getArticleSettings($articleId, $clubDefaultLanguage, $executiveBoardTerm, $clubTitle)
    {

        $areaTitleQuery = "(SELECT GROUP_CONCAT((CASE WHEN R.is_executive_board=1 THEN '" . $executiveBoardTerm . "' ELSE COALESCE(NULLIF(RI18N.title_lang, ''), R.title) END) ORDER BY RC.sort_order,TC.sort_order,R.sort_order SEPARATOR ',') AS areaTitles
                           FROM fg_cms_article_selectedareas A_SEL_AREA
                           LEFT JOIN fg_rm_role R ON A_SEL_AREA.role_id = R.id
                           LEFT JOIN fg_team_category AS TC ON R.team_category_id = TC.id
                           LEFT JOIN fg_rm_category AS RC ON RC.id = R.category_id
                           LEFT JOIN fg_rm_role_i18n AS RI18N ON RI18N.id = R.id AND RI18N.lang = '$clubDefaultLanguage'
                           WHERE A_SEL_AREA.article_id = '$articleId' AND R.is_active = 1) ";


        $categoryTitleQuery = "(SELECT GROUP_CONCAT(COALESCE(NULLIF(ACATI18n.title_lang, ''), ACAT.title))  as categoryTitles
                               FROM fg_cms_article_selectedcategories ASCAT
                               LEFT JOIN fg_cms_article_category ACAT ON ASCAT.category_id = ACAT.id
                               LEFT JOIN fg_cms_article_category_i18n ACATI18n ON ACATI18n.id = ACAT.id AND ACATI18n.lang = '$clubDefaultLanguage'
                               WHERE ASCAT.article_id = '$articleId')";


        $conn = $this->_em->getConnection();
        $areaQuery = $conn->query($areaTitleQuery);
        $categoryQuery = $conn->query($categoryTitleQuery);
        $areaResult = $areaQuery->fetchAll();
        $categoryResult = $categoryQuery->fetchAll();

        $qb = $this->createQueryBuilder('A')
            ->select('IDENTITY(A.club) AS club, A.isDraft, '
                . 'A.publicationDate, '
                . 'A.expiryDate, A.author, A.scope, A.commentAllow as allowcomment, '
                . 'A.createdOn,  A.updatedOn, A.shareWithLower as share, '
                . 'GROUP_CONCAT(ROLE.id) as areas, '
                . "CASE WHEN (SUM(DISTINCT SAREA.isClub) > 0) THEN '" . $clubTitle . "' ELSE '' END as  areaClub, "
                . 'GROUP_CONCAT(DISTINCT CAT.id) as categories, '
                . 'GROUP_CONCAT(DISTINCT CAT.title) as categoryTitlesDef, '
                . ' A.id as articleId ')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedareas', 'SAREA', 'WITH', 'SAREA.article = A.id')
            ->leftJoin('CommonUtilityBundle:FgRmRole', 'ROLE', 'WITH', 'SAREA.role = ROLE.id')
            ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'ROLEI18n', 'WITH', '(ROLEI18n.id = ROLE.id AND ROLEI18n.lang=:clubDefaultLanguage)')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedcategories', 'SCAT', 'WITH', 'SCAT.article = A.id')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleCategory', 'CAT', 'WITH', 'SCAT.category = CAT.id')
            ->where('A.id=:articleId')
            ->setParameters(array('articleId' => $articleId, 'clubDefaultLanguage' => $clubDefaultLanguage));

        $results = $qb->getQuery()->getArrayResult();
        if (!empty($areaResult)) {
            $results[0]['areaTitles'] = $areaResult[0]['areaTitles'];
        } else {
            $results[0]['areaTitles'] = '';
        }
        if (!empty($categoryResult)) {
            $results[0]['categoryTitles'] = $categoryResult[0]['categoryTitles'];
        } else {
            $results[0]['categoryTitles'] = '';
        }

        return (!empty($results)) ? $results[0] : array();
    }

    /**
     * Method to get all article text datas.
     *
     * @param int $articleId article Id
     *
     * @return array article text data array
     */
    public function getArticleText($articleId)
    {
        $qb = $this->createQueryBuilder('A')
                ->select(" ATL.lang as lang,ATL.titleLang  as titleLang, "
                        . "ATL.teaserLang as teaserLang, "
                        . "ATL.textLang  as textLang, "
                        . 'AT.title as defaultTitle, AT.teaser as defaultTeaser, AT.text as defaultText, '
                        . ' A.id as articleId ')
                ->innerJoin('CommonUtilityBundle:FgCmsArticleText', 'AT', 'WITH', 'AT.id = A.textversion')
                ->leftJoin('CommonUtilityBundle:FgCmsArticleTextI18n', 'ATL', 'WITH', 'ATL.id = AT.id')
                ->where('A.id=:articleId')
                ->setParameters(array('articleId' => $articleId));
        $results = $qb->getQuery()->getArrayResult();
     

        return (!empty($results)) ? $results : array();
    }

    /**
     * Method to delete article.
     *
     * @param array  $deleteData article details array
     * @param object $container  container interface object
     *
     * @return boolean true or false
     */
    public function deleteArticles($deleteData, $container)
    {
        $clubId = $container->get('club')->get('id');
        $contactId = $container->get('contact')->get('id');
        $deleted = $container->get('translator')->trans('ARTICLE_DELETED');
        foreach ($deleteData as $value) {
            $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($value);
            $articleId = $value;
            if ($articleObj) {
                $articleObj->setIsDeleted(1);
                $this->_em->persist($articleObj);
                $logArray[] = "($clubId,$articleId,now(),'status','data','$deleted','',$contactId)";
            }
        }
        $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleLog')->saveLog($logArray);
        $this->_em->flush();

        return true;
    }

    /**
     * Method to reactivate article.
     *
     * @param array  $reactivateData article details
     * @param object $container      container interface object
     *
     * @return boolean true or false
     */
    public function reactivateArticles($reactivateData, $container)
    {
        $clubId = $container->get('club')->get('id');
        $contactId = $container->get('contact')->get('id');
        $reactivated = $container->get('translator')->trans('ARTICLE_REACTIVATED');
        $archived = $container->get('translator')->trans('ARTICLE_ARCHIVED');
        $currDate = new \DateTime('now');
        $currentDate = $currDate->format('Y-m-d H:i:s');
        foreach ($reactivateData as $value) {
            $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($value);
            $articleId = $value;
            $articleExpiryDate = $articleObj->getExpiryDate();
            $expiryDate = (is_object($articleExpiryDate)) ? $articleExpiryDate->format('Y-m-d H:i:s') : '';
            if ($articleObj) {
                $query = 'UPDATE fg_cms_article C '
                    . 'SET C.expiry_date = NULL,C.updated_by = ' . $contactId . ', C.archived_by = NULL, C.updated_on = "' . $currentDate . '"'
                    . ' WHERE C.id = ' . $value;
                $conn = $this->getEntityManager()->getConnection();
                $conn->executeQuery($query);
                $logArray[] = "($clubId,$articleId,now(),'status','data','$reactivated','$archived',$contactId)";
                $logArray[] = "($clubId,$articleId,now(),'expiry_date','data','','$expiryDate',$contactId)";
            }
        }
        $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleLog')->saveLog($logArray);

        return true;
    }

    /**
     * Get created by contact for article filter.
     *
     * @param int $clubId The club id
     * @param int $term   The string to compare names
     *
     * @return array Created by contact list
     */
    public function getCreatedByContacts($clubId, $term)
    {
        $qb = $this->createQueryBuilder('A')
            ->select('AT.id,contactName(AT.id) as title ')
            ->innerJoin('CommonUtilityBundle:FgCmContact', 'AT', 'WITH', 'A.createdBy = AT.id')
            ->innerJoin('CommonUtilityBundle:MasterSystem', 'M', 'WITH', 'M.fedContact = AT.fedContact')
            ->where('( M.firstName LIKE :term OR M.lastName LIKE :term OR M.companyName LIKE :term ) AND AT.club=:clubId ')
            ->setParameters(array('term' => $term . '%', 'clubId' => $clubId))
            ->groupBy('AT.fedContact');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * The function that will return the count of articles for each timeperiod in the article sidebar.
     *
     * @param int    $currentClubId       club id of current club
     * @param array  $clubHierarchy       club hierarchy array
     * @param string $clubDefaultLanguage default club language
     * @param string $roleString          role string
     * @param int    $isAdmin             admin check flag
     * @param string $articleIds          Comma separated article ids
     *
     * @return array Array of timeperios with counts
     */
    public function getTimeperiodForArticles($currentClubId, $clubHierarchy, $clubDefaultLanguage, $roleString = '', $isAdmin = 0, $articleIds = '')
    {
        $yearFrom = $this->getArticleSortByYear($currentClubId, $clubHierarchy);
        $timePeriodArray = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubTimeperiod($currentClubId, 2, $yearFrom);

        $timePeriodCountArray = array();
        if (count($timePeriodArray) > 0) {
            foreach ($timePeriodArray as $key => $timePeriod) {
                $timePeriodQueryString[] = "SUM(CASE WHEN A.publicationDate BETWEEN '" . $timePeriod['start'] . "' AND '" . $timePeriod['end'] . "' THEN 1 ELSE 0 END) AS T_" . $key;
            }

            $qb = $this->createQueryBuilder('A')
                ->select(implode(',', $timePeriodQueryString))
                ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedareas', 'A_SEL_AREA', 'WITH', 'A_SEL_AREA.article = A.id');

            $whereString = " (A.club = $currentClubId AND ( (A_SEL_AREA.role IN ('$roleString') OR ($isAdmin=1)) OR A.scope = 'public' OR A_SEL_AREA.isClub = 1)  )";
            if (count($clubHierarchy) > 0) {
                $whereString .= ' OR (A.club IN (' . implode(',', $clubHierarchy) . ') AND (A.shareWithLower = 1 AND A_SEL_AREA.isClub = 1))';
            }

            if ($articleIds != '') {
                $whereString .= ' AND A.id IN (' . $articleIds . ')';
            }

            $qb->where($whereString);
            $timePeriodCountResult = $qb->getQuery()->getOneOrNullResult();

            $first = true;
            foreach ($timePeriodArray as $key => $timePeriod) {
                $label = 'T_' . $key;
                if ($timePeriodCountResult[$label] > 0) {
                    $label = $timePeriod['label'];
                    $currentYear = ($first) ? 'yes' : 'no';
                    $first = false;
                    $timePeriodCountArray[] = array(
                        'start' => $timePeriod['start'],
                        'end' => $timePeriod['end'],
                        'label' => $label,
                        'currentyear' => $currentYear,);
                }
            }
        }


        return $timePeriodCountArray;
    }

    /**
     * Function to get all article Categories of a club having article.
     *
     * @param int    $clubId     Club id
     * @param string $defLang    Default Language
     * @param bool   $isParent   is $clubId is curren club id. (true for higher levels)
     * @param string $myArticles My article ids
     *
     * @return array array of article Categories
     */
    public function getCategoriesWithArticle($clubId, $defLang, $isParent = false, $myArticles = '')
    {
        $qb = $this->createQueryBuilder('C')
            ->select("DISTINCT (CASE WHEN cc.id IS NULL THEN 'WA' ELSE cc.id END) as id, (CASE WHEN (ccl.titleLang IS NULL OR ccl.titleLang = '') THEN cc.title ELSE ccl.titleLang END) as title,(CASE WHEN cc.sortOrder IS NULL THEN 1 ELSE 0 END) as sOrder")
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedcategories', 'SC', 'WITH', '( SC.article = C.id )')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleCategory', 'cc', 'WITH', 'SC.category = cc.id')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleCategoryI18n', 'ccl', 'WITH', '(cc.id = ccl.id AND ccl.lang = :defLang)')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedareas', 'A_SEL_AREA', 'WITH', 'A_SEL_AREA.article = C.id')
            ->leftJoin('CommonUtilityBundle:FgRmRole', 'ROLE', 'WITH', 'A_SEL_AREA.role = ROLE.id');

        if ($isParent == 1) {
            $qb = $qb->where("(C.club = $clubId AND C.id IN ($myArticles) )");
        } else {
            $qb = $qb->where("(C.club = $clubId AND  C.id IN ($myArticles) )");
        }

        return $qb->orderBy('sOrder,cc.sortOrder')
                ->setParameter('defLang', $defLang)
                ->groupBy('cc.id')
                ->getQuery()->getResult();
    }

    /**
     * The function will return the team/workgroup data to the article sidebar.
     *
     * @param int    $clubId              club id
     * @param string $clubDefaultLanguage Default Club Language
     * @param string $roleString          role string
     * @param bool   $isAdmin             admin check flag
     * @param bool   $isParent            is $clubId is curren club id. (true for higher levels)
     * @param string $myArticles          comma separated article ids
     *
     * @return array article list with roles
     */
    public function getRolesWithArticles($clubId, $clubDefaultLanguage, $roleString = '', $isAdmin = 0, $isParent = 0, $myArticles = '')
    {
        $qb = $this->createQueryBuilder('A')
            ->select("COUNT(A.id) AS articleCount, ROLE.id AS roleId, ROLE.type AS type, COALESCE(NULLIF(ROLEI18n.titleLang, ''), ROLE.title) AS title, C.id as clubId,(C.clubType) AS clubType")
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedareas', 'A_SEL_AREA', 'WITH', 'A_SEL_AREA.article = A.id')
            ->leftJoin('CommonUtilityBundle:FgRmRole', 'ROLE', 'WITH', 'A_SEL_AREA.role = ROLE.id')
            ->leftJoin('CommonUtilityBundle:FgRmRoleI18n', 'ROLEI18n', 'WITH', '(ROLEI18n.id = ROLE.id AND ROLEI18n.lang=:clubDefaultLanguage)')
            ->leftJoin('CommonUtilityBundle:FgClub', 'C', 'WITH', '(C.id =:club)')
            ->setParameters(array('clubDefaultLanguage' => $clubDefaultLanguage, 'club' => $clubId))
            ->groupBy('A_SEL_AREA.role');

        if ($isParent == 1) {
            $qb->where("(A.club = $clubId AND A.id IN ($myArticles) )");
        } else {
            $qb->where("(A.club = $clubId AND A.id IN ($myArticles)  )");
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * The function will return the team/workgroup data to the article sidebar.
     *
     * @param int $clubId club id
     *
     * @return array article area count data
     */
    public function getArticleCountWithoutArea($clubId)
    {
        $qb = $this->createQueryBuilder('A')
            ->select('COUNT(A.id) AS articleCount')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleSelectedareas', 'A_SEL_AREA', 'WITH', 'A_SEL_AREA.article = A.id')
            ->where("(A.club = $clubId AND A_SEL_AREA.id IS NULL)");

        return $qb->getQuery()->getResult();
    }

    /**
     * Method to save article status.
     *
     * @param int $articleId article Id
     * @param int $status    draft status
     *
     * @return void
     */
    public function updateArticleStatus($articleId, $status = 0)
    {
        $articleObj = $this->_em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
        if ($articleObj) {
            $articleObj->setIsDraft($status);
            $this->_em->flush();
        }

        return;
    }

    /**
     * The function will return the get lower level shared articles for cms article element
     *
     * @param int $clubId club id
     *
     * @return array Count array of lower level shared articles
     */
    public function getLowerLevelSharedArticleCount($clubId)
    {
        $qb = $this->createQueryBuilder('A')
            ->select('COUNT(A.id) AS articleCount')
            ->where("(A.club = $clubId AND A.isDraft=0)")
            ->andWhere("(A.expiryDate > 'now()' OR A.expiryDate IS NULL)")
            ->andWhere("A.publicationDate <= 'now()'")
            ->andWhere("A.shareWithLower = 1");

        $result = $qb->getQuery()->getResult();

        return $result[0]['articleCount'];
    }

    /**
     * The function to get article count of a club for cms article element
     *
     * @param object $container container interface object
     *
     * @return int article count of club
     */
    public function getArticleCountOfClubForCms($container)
    {
        $clubType = $container->get('club')->get("type");
        $clubId = $container->get('club')->get("id");

        $qry = $this->createQueryBuilder('a')
            ->select('COUNT(a.id) as articleCount')
            ->where('a.club = ' . $clubId);
        if ($clubType != 'federation' && $clubType != 'standard_club') {
            $clubHierarchy = implode(',', $container->get('club')->get("clubHeirarchy"));
            $joinCondition = 'a.club IN (' . $clubHierarchy . ') AND a.shareWithLower= 1';
            $qry->orWhere($joinCondition);
        }
        $qry->andWhere(' a.isDeleted != 1');
        $result = $qry->getQuery()->getResult();

        return $result[0]['articleCount'];
    }

    /**
     * The function will return the get first  article based on year
     *
     * @param int   $clubId        club id
     * @param array $clubHierarchy club hierarchy array
     *
     * @return int  The article publicationDate
     */
    public function getArticleSortByYear($clubId, $clubHierarchy)
    {
        $whereString .= "A.club = $clubId OR (A.club IN (' . implode(',', $clubHierarchy) . ') AND (A.shareWithLower = 1 ))";
        $datetimeFormat = FgSettings::getMysqlDateTimeFormat();
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $qb = $this->createQueryBuilder('A')
            ->select("DATE_FORMAT(A.publicationDate, '%Y') as publicationDate ")
            ->where("(A.expiryDate > 'now()' OR A.expiryDate IS NULL)")
            ->andWhere("A.publicationDate <= 'now()'")
            ->andWhere("A.isDeleted !=1")
            ->andWhere($whereString)
            ->orderBy('A.publicationDate', 'ASC')
            ->setMaxResults(1);


        $result = $qb->getQuery()->getResult();
        
        return $result[0]['publicationDate'];



    }

    /**
     * The function will return the articles titles for Newsletter
     *
     * @param object $container Container object
     * @param string $term      search keyword
     *
     * @return array $result Array of newsletter articles
     */
    public function getNewsletterArticles($container, $term = '')
    {
        $datetimeFormat = FgSettings::getMysqlDateFormat();
        $clubId = $container->get('club')->get("id");
        $clubLanguages = $container->get('club')->get("club_languages");
        $doctrineConfig = $this->getEntityManager()->getConfiguration();
        $doctrineConfig->addCustomStringFunction('DATE_FORMAT', 'Common\UtilityBundle\Extensions\DateFormat');
        $clubHierarchy = implode(',', $container->get('club')->get("clubHeirarchy"));
        $whereString = "A.club = $clubId OR (A.club IN ($clubHierarchy) AND (A.shareWithLower = 1 ))";
        $clubType = $container->get('club')->get("type");
        $currDate = new \DateTime('now');
        $archivedate = $currDate->format('Y-m-d H:i:s');
        //clubs with more than 1 lnguage should show language in auto complete
        if (count($clubLanguages) > 1) {
            $titleSql = "CASE WHEN (ATL.titleLang IS NULL OR ATL.titleLang = '') THEN AT.title ELSE ATL.titleLang END AS articleTitle, ATL.lang, DATE_FORMAT(A.publicationDate, '$datetimeFormat') as publicationDate,CONCAT(CASE WHEN (ATL.titleLang IS NULL OR ATL.titleLang = '') THEN AT.title ELSE ATL.titleLang END, ' (', DATE_FORMAT(A.publicationDate, '$datetimeFormat'), ', ',UPPER(ATL.lang),') ')  As title ";
        } else {
            $titleSql = "CASE WHEN (ATL.titleLang IS NULL OR ATL.titleLang = '') THEN AT.title ELSE ATL.titleLang END AS articleTitle, ATL.lang, DATE_FORMAT(A.publicationDate, '$datetimeFormat') as publicationDate,CONCAT(CASE WHEN (ATL.titleLang IS NULL OR ATL.titleLang = '') THEN AT.title ELSE ATL.titleLang END, ' (', DATE_FORMAT(A.publicationDate, '$datetimeFormat'), ') ')  As title ";
        }

        $qb = $this->createQueryBuilder('A')
            ->select("DISTINCT CONCAT(A.id,'-',ATL.lang) AS id, A.id as articleId, $titleSql ")
            ->innerJoin('CommonUtilityBundle:FgCmsArticleText', 'AT', 'WITH', 'AT.id = A.textversion')
            ->leftJoin('CommonUtilityBundle:FgCmsArticleTextI18n', 'ATL', 'WITH', 'AT.id = ATL.id')
            ->where("A.isDeleted !=1")
            ->andWhere("(A.expiryDate   > '$archivedate ' OR A.expiryDate IS NULL )");
        if ($clubType != 'federation' && $clubType != 'standard_club') {//Shared With Lower Levels
            $qb->andWhere($whereString);
        } else {
            $qb->andWhere('A.club = ' . $clubId);
        }
        $qb->orderBy('A.publicationDate', 'ASC');
        $qb->addOrderBy('ATL.lang', 'ASC');
        if ($term != '') {
            $qb->andWhere("ATL.titleLang LIKE :term  ")
                ->setParameter('term', $term . '%');
        }

        
       return $qb->getQuery()->getResult();
    }
}
