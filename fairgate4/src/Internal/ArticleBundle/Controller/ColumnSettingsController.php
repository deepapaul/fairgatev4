<?php

/**
 * Article ColumnSettings Controller
 */
namespace Internal\ArticleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * This controller is used for handling column settings for editorial/archive listing pages
 *
 * @package    InternalArticleBundle
 * @subpackage Controller
 * @author     pitsolutions.ch
 * @version    Fairgate V4
 */
class ColumnSettingsController extends Controller
{

    /**
     * Function to display the column settings page for editorial and archive in internal area
     *
     * @return object View Template Render Object
     */
    public function indexAction()
    {
        $translatorService = $this->get('translator');
        $module = $this->container->get('request_stack')->getCurrentRequest()->get('moduleName');
        $titleText = $this->get('translator')->trans(strtoupper($module) . '_COLUMN_SETTINGS_TITLE');
        $fixedFields = $this->getFixedFields($translatorService);
        $clubId = $this->get('club')->get('id');
        $contactId = $this->get('contact')->get('id');
        $returnData = array('clubId' => $clubId, 'contactId' => $contactId);
        $breadCrumb = array(
            'back' => ($module == "editorial") ? $this->generateUrl('internal_article_editorial_list') : $this->generateUrl('internal_article_archive_list')
        );
        $defaultSettings = $this->container->getParameter('default_internal_article_table_settings');

        return $this->render('InternalArticleBundle:Columnsettings:index.html.twig', array('breadCrumb' => $breadCrumb, 'fixedFields' => $fixedFields, 'defaultSettings' => $defaultSettings, 'module' => $module, 'titletext' => $titleText, 'returnData' => $returnData));
    }

    /**
     * function for getting fixed fields
     * 
     * @param object $translator Translator object
     * 
     * @return object JSON Response Object
     */
    private function getFixedFields($translator)
    {
        $em = $this->getDoctrine()->getManager();
        $clubId = $this->container->get('club')->get('id');
        //global club comment settings 
        $getGlobalClubSettings = $em->getRepository('CommonUtilityBundle:FgCmsArticleClubsetting')->getClubSettings($clubId);
        $isCommentActive = $getGlobalClubSettings['commentActive'];
        $documentsData = array(
            'SETTINGS' => $this->getFilterDataOfSettingsType($translator),
            'EDITING' => $this->getFilterDataOfEditingType($translator),
            'CONTENT' => $this->getFilterDataOfContentType($translator, $isCommentActive)
        );

        return json_encode($documentsData);
    }

    /**
     * function to get collumnsettings data for settings column
     * 
     * @param object $translator Translator service
     * 
     * @return array filter data of settings column
     */
    private function getFilterDataOfSettingsType($translator)
    {
        return array(
            'id' => 'SETTINGS',
            'title' => $translator->trans('AR_SETTINGS'),
            'show_filter' => '1',
            'fixed_options' => array(
                '0' => array('0' => array('id' => '', 'title' => $translator->trans('SELECT_DEFAULT')))
            ),
            'entry' => array(
                '0' => array('id' => 'STATUS', 'title' => $translator->trans('AR_STATUS'), 'type' => 'text'),
                '1' => array('id' => 'PUBLICATION_DATE', 'title' => $translator->trans('AR_PUBLICATION_DATE'), 'type' => 'date'),
                '2' => array('id' => 'ARCHIVING_DATE', 'title' => $translator->trans('AR_ARCHIVING_DATE'), 'type' => 'date'),
                '3' => array('id' => 'AREAS', 'title' => $translator->trans('AR_AREAS'), 'type' => 'text'),
                '4' => array('id' => 'CATEGORIES', 'title' => $translator->trans('AR_CATEGORIES'), 'type' => 'text'),
                '5' => array('id' => 'AUTHOR', 'title' => $translator->trans('AR_AUTHOR'), 'type' => 'text'),
                '6' => array('id' => 'SCOPE', 'title' => $translator->trans('AR_SCOPE'), 'type' => 'text')
            )
        );
    }

    /**
     * function to get collumnsettings data for editing column
     * 
     * @param object $translator
     * 
     * @return array filter data of editing column
     */
    private function getFilterDataOfEditingType($translator)
    {

        return array(
            'id' => 'EDITING',
            'title' => $translator->trans('AR_EDITING'),
            'show_filter' => '1',
            'fixed_options' => array(
                '0' => array('0' => array('id' => '', 'title' => $translator->trans('SELECT_DEFAULT')))
            ),
            'entry' => array(
                '0' => array('id' => 'CREATED_AT', 'title' => $translator->trans('AR_CREATED_AT'), 'type' => 'date'),
                '1' => array('id' => 'CREATED_BY', 'title' => $translator->trans('AR_CREATED_BY'), 'type' => 'text'),
                '2' => array('id' => 'EDITED_AT', 'title' => $translator->trans('AR_EDITED_AT'), 'type' => 'date'),
                '3' => array('id' => 'EDITED_BY', 'title' => $translator->trans('AR_EDITED_BY'), 'type' => 'text')
            )
        );
    }

    /**
     * function to get collumnsettings data for content column
     * 
     * @param object $translator   Translator service
     * @param int $isCommentActive Comment active or not flag
     * 
     * @return array filter data of content column
     */
    private function getFilterDataOfContentType($translator, $isCommentActive)
    {

        $contentTypeData = array(
            'id' => 'CONTENT',
            'title' => $translator->trans('AR_CONTENT'),
            'show_filter' => '1',
            'fixed_options' => array(
                '0' => array('0' => array('id' => '', 'title' => $translator->trans('SELECT_DEFAULT')))
            ),
            'entry' => array(
                '0' => array('id' => 'IMAGE_VIDEOS', 'title' => $translator->trans('AR_IMAGE_VIDEOS'), 'type' => 'number'),
            )
        );
        if ($isCommentActive == 1) {
            $contentTypeData['entry'][] = array('id' => 'COMMENTS', 'title' => $translator->trans('AR_COMMENTS'), 'type' => 'text');
        }
        $contentTypeData['entry'][] = array('id' => 'LANGUAGES', 'title' => $translator->trans('AR_LANGUAGES'), 'type' => 'text');

        return $contentTypeData;
    }
}
