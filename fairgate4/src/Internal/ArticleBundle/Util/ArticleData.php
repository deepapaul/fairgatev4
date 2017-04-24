<?php

/**
 * The wrapper class to create the article data into an iteratable array
 *
 */
namespace Internal\ArticleBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * The wrapper class to create the article data into an iteratable array
 *
 * @package 	Internal
 * @subpackage 	Article
 * @author     	pitsolutions.ch
 * @version    	Fairgate V4
 *
 */
class ArticleData
{
    /**
     * The data array
     * 
     * @var array 
     */
    private $dataArray = array();
    
    /**
     * The club id
     * 
     * @var int 
     */
    private $clubId;
    
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
     * The club object
     * 
     * @var object 
     */
    private $club;
    
    /**
     * The club languages
     * 
     * @var array 
     */
    private $clubLanguages;

    /**
     * It can be club_default_language/contact default_language. From editorial it is club_default_language and from overview it is default_language.
     *
     * @var string
     */
    public $clubDefaultLanguage;

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
        $this->clubId = $this->club->get('id');
        $this->clubLanguages = $this->club->get('club_languages');
        $this->clubDefaultLanguage = $this->club->get('club_default_lang');
    }

    /**
     * Method to get all article data in required format array.
     *
     * @param int   $articleId The id of the article
     * @param array $sections  section array (default 4 sections) ('text', 'textversions','media', 'attachment', 'settings')
     *
     * @return array $this->dataArray return array in specific format
     */
    public function getArticleDatas($articleId, $sections = array('text', 'media', 'attachment', 'settings'))
    {
        $this->dataArray = array();
        foreach ($sections as $section) {
            switch ($section) {
                case 'text':
                    $this->getArticleText($articleId);
                    break;
                case 'media':
                    $this->getArticleMedia($articleId);
                    break;
                case 'attachment':
                    $this->getArticleAttachments($articleId);
                    break;
                case 'settings':
                    $this->getArticleSettings($articleId);
                    break;
                default:
                    break;
            }
        }

        return $this->dataArray;
    }

    /**
     * Method to get article text section data in required format array.
     *
     * @param int $articleId The id of the article
     * @param int $mode      The mode variable
     *
     * @return array $this->dataArray array of text section datas
     */
    public function getArticleText($articleId, $mode = 0)
    {
        $articleData = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleText($articleId);
        //text section
        if (count($articleData) > 0) {
            //text section
            foreach ($articleData as $data) {
                $lang = $data['lang'];
                $this->dataArray['article']['text'][$lang]['title'] = $data['titleLang'];
                $this->dataArray['article']['text'][$lang]['teaser'] = $data['teaserLang'];
                 //FAIR-2489
                $this->dataArray['article']['text'][$lang]['text'] = FgUtility::correctCkEditorUrl($data['textLang'], $this->container, $this->clubId, $mode);
           }
           
        
            
       
            $this->dataArray['article']['text']['default']['title'] = $articleData[0]['defaultTitle'];
            $this->dataArray['article']['text']['default']['teaser'] = $articleData[0]['defaultTeaser'];
            //FAIR-2489
            $this->dataArray['article']['text']['default']['text'] = FgUtility::correctCkEditorUrl($articleData['defaultText'], $this->container, $this->clubId, $mode);
        }

        return $this->dataArray;
    }

    /**
     * Method to get article media section data in required format array.
     *
     * @param int $articleId The id of the article
     *
     * @return array $this->dataArray array of media section datas
     */
    public function getArticleMedia($articleId)
    {
        $articleMedias = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleMedia($articleId);
        $articleObj = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->find($articleId);
        $clubId = $articleObj->getClub()->getId();
        $baseUrl = FgUtility::getBaseUrl($this->container);
        $imgSrc = FgUtility::getUploadFilePath($clubId, 'gallery');
        $this->dataArray['article']['media']['position'] = $articleMedias[0]['position'];
        $folderWidth = ($articleMedias[0]['position'] == 'top_slider' || $articleMedias[0]['position'] == 'bottom_slider' ) ? 'width_1140' : 'width_580';
        foreach ($articleMedias as $articleMedia) {
            if ($articleMedia['mediaId']) {
                $mediaLangArray = explode('|&&&|', $articleMedia['mediaLangArray']);
                $mediaDescArray = explode('|&&&|', $articleMedia['mediaDescArray']);
                if ($articleMedia['type'] == 'IMAGE') {
                    $this->dataArray['article']['media'][$articleMedia['sortOrder']] = array('mediaId' => $articleMedia['mediaId'], 'itemId' => $articleMedia['itemsId'], 'imgsrc' => $baseUrl . '/' . $imgSrc . '/' . $folderWidth . '/' . $articleMedia['mediaFileName'], 'imgsrc1920' => $baseUrl . '/' . $imgSrc . '/width_1920/' . $articleMedia['mediaFileName'], 'sortOrder' => $articleMedia['sortOrder'], 'size' => $articleMedia['mediaSize'], 'description' => array(), 'type' => 'images', 'imageName' => $articleMedia['mediaFileName']);
                } else if ($articleMedia['type'] == 'VIDEO') {
                    $this->dataArray['article']['media'][$articleMedia['sortOrder']] = array('mediaId' => $articleMedia['mediaId'], 'itemId' => $articleMedia['itemsId'], 'imgsrc' => $baseUrl . '/' . $imgSrc . '/' . $folderWidth . '/' . $articleMedia['filepath'], 'imgsrc1920' => $baseUrl . '/' . $imgSrc . '/width_1920/' . $articleMedia['filepath'], 'sortOrder' => $articleMedia['sortOrder'], 'videoThumbUrl' => $articleMedia['videoThumbUrl'], 'description' => array(), 'type' => 'videos', 'videoThumbImg' => $articleMedia['filepath'], 'videoUrl' => $articleMedia['videoThumbUrl']);
                }
                if ($articleMedia['mediaLangArray'] && count($mediaLangArray) > 0) {
                    foreach ($mediaLangArray as $keyLang => $lang) {
                        $this->dataArray['article']['media'][$articleMedia['sortOrder']]['description'][$lang] = $mediaDescArray[$keyLang];
                    }
                }
                $this->dataArray['article']['media'][$articleMedia['sortOrder']]['description']['default'] = $articleMedia['defaultDesc'];
            }
        }

        return $this->dataArray;
    }

    /**
     * Method to get article attachments section data in required format array.
     *
     * @param int $articleId The id of the article
     *
     * @return array $this->dataArray array of attachments section
     */
    public function getArticleAttachments($articleId)
    {
        $articleDatas = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleAttachments($articleId);
        //attachment section
        foreach ($articleDatas as $articleData) {
            if ($articleData['attachmentId']) {
                $this->dataArray['article']['attachment'][$articleData['attachmentId']] = array('attachmentId' => $articleData['attachmentId'], 'attachmentName' => $articleData['attachmentName'], 'attachmentSize' => $articleData['attachmentSize'], 'filemanagerId' => $articleData['filemanagerId'], 'virtualFilename' => $articleData['virtualFilename']);
            }
        }

        return $this->dataArray;
    }

    /**
     * Method to get article settings section data in required format array.
     *
     * @param int $articleId The id of the article
     *
     * @return array $this->dataArray array of settings section
     */
    public function getArticleSettings($articleId)
    {
        $terminologyService = $this->container->get('fairgate_terminology_service');
        $executiveBoardTerm = ucfirst($terminologyService->getTerminology('Executive Board', $this->container->getParameter('singular')));
        $articleData = $this->em->getRepository('CommonUtilityBundle:FgCmsArticle')->getArticleSettings($articleId, $this->clubDefaultLanguage, $executiveBoardTerm, ucfirst($this->club->get('title')));
        if (count($articleData) > 0) {
            $this->dataArray['article']['club'] = $articleData['club'];
            $this->dataArray['article']['isDraft'] = $articleData['isDraft'];

            $currDate = new \DateTime("now");
            //get draft/planned/archived/published
            $this->dataArray['article']['level'] = ($articleData['isDraft'] == 1) ? 'draft' : (($articleData['expiryDate'] != '' && $articleData['expiryDate'] < $currDate) ? 'archived' : (($articleData['publicationDate'] > $currDate) ? 'planned' : 'published'));
            //settings section
            $this->dataArray['article']['settings']['publicationdate'] = ($articleData['publicationDate'] != '') ? $articleData['publicationDate']->format(FgSettings::getPhpDateTimeFormat()) : '';
            $this->dataArray['article']['settings']['updatedOn'] = ($articleData['updatedOn'] != '') ? $articleData['updatedOn']->format(FgSettings::getPhpDateTimeFormat()) : '';
            $this->dataArray['article']['settings']['expirydate'] = ($articleData['expiryDate'] != '') ? $articleData['expiryDate']->format(FgSettings::getPhpDateTimeFormat()) : '';
            $articleData['areas'] = ($articleData['areaClub']) ? $articleData['areaClub'] . ',' . $articleData['areas'] : $articleData['areas'];
            $articleData['areaTitles'] = (($articleData['areaClub'] && ($articleData['areaTitles'])) ? $articleData['areaClub'] . ',' . $articleData['areaTitles'] : (($articleData['areaClub']) ? $articleData['areaClub'] : $articleData['areaTitles']));
            $this->dataArray['article']['settings']['areas'] = ($articleData['areas']) ? explode(',', $articleData['areas']) : array();
            $this->dataArray['article']['settings']['areaTitles'] = $articleData['areaTitles'];
            $this->dataArray['article']['settings']['categories'] = ($articleData['categories']) ? explode(',', $articleData['categories']) : array();
            $this->dataArray['article']['settings']['categoryTitles'] = $articleData['categoryTitles'];
            $this->dataArray['article']['settings']['categoryTitlesDef'] = $articleData['categoryTitlesDef'];
            $this->dataArray['article']['settings']['author'] = $articleData['author'];
            $this->dataArray['article']['settings']['scope'] = $articleData['scope'];
            $this->dataArray['article']['settings']['allowcomment'] = $articleData['allowcomment'];
            $this->dataArray['article']['settings']['share'] = $articleData['share'];
        }

        return $this->dataArray;
    }
}
