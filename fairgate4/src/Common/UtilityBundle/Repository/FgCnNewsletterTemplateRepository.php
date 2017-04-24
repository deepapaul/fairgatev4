<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Common\UtilityBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Common\UtilityBundle\Entity\FgCnNewsletterTemplate;
use Common\UtilityBundle\Util\FgUtility;
use Common\UtilityBundle\Util\FgSettings;

/**
 * This repository is used for handling newsletter template functionality
 */
class FgCnNewsletterTemplateRepository extends EntityRepository {
    
    /**
     * Check whether Template exist in club
     *
     * @param int   $templateId    Template id
     * @param int   $clubId Club id
     *
     * @return int  $result
     */
    public function checkTemplateExist($templateId, $clubId) {
        $resultQuery = $this->createQueryBuilder('nt')
                ->select('count(nt.id)')
                ->where('nt.id=:id')
                ->andWhere('nt.club=:clubId')
                ->setParameter('id', $templateId)
                ->setParameter('clubId', $clubId)
                ->getQuery()
                ->getResult();

        return $resultQuery[0][1];
    }
    
    /**
     * Create a template
     *
     * @param int   $clubId    Club id
     * @param int   $contactId Contact id
     * @param array $formdata  Form data
     *
     * @return int  Last insertId
     */
    public function createtemplate($clubId, $contactId, $formdata) {
        $datetime = date("Y-m-d H:i:s");  
        $templateObj = new FgCnNewsletterTemplate();   
        $clubObj = $this->_em->getRepository('CommonUtilityBundle:FgClub')->find($clubId);
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        if($templateObj) {
            $templateObj->setTitle($formdata['template_name'])
                    ->setClub($clubObj)
                    ->setHeaderImage($formdata['picture_88'])
                    ->setArticleDisplay($formdata['displayType'])
                    ->setColorBg($formdata['background']) 
                    ->setColorTocBg($formdata['background_table'])
                    ->setColorStdText($formdata['general_text'])
                    ->setColorTocText($formdata['text_table'])
                    ->setColorTitleText($formdata['heading_text'])
                    ->setCreatedOn(new \DateTime($datetime))
                    ->setCreatedBy($contactObj)
                    ->setSenderName(trim($formdata['sender_name']))
                    ->setSenderEmail(trim($formdata['sender_email']))
                    ->setSalutationType($formdata['salutation_type'])
                    ->setSalutation($formdata['salutation'])
                    ->setLanguageSelection($formdata['language_selection'])
                    ;
            $this->_em->persist($templateObj);
            $this->_em->flush();
            $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplateLang')->updateTemplateLanguage($templateObj, $formdata['language']);
            if(count($formdata['sponsor']) > 0) {
                $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplateSponsor')->updateTemplateSponsor($templateObj, $formdata['sponsor']);
            }
        }

        return $templateObj->getId();
    }

    /**
     * Update a template
     *   
     * @param type $contactId  Contact id
     * @param int  $formdata   Form data
     * @param int  $templateid Template id
     *
     * @return type
     */
    public function updatetemplate($contactId, $formdata, $templateid) {        
        $datetime = date("Y-m-d H:i:s");        
        $templateObj = $this->find($templateid);
        $contactObj = $this->_em->getRepository('CommonUtilityBundle:FgCmContact')->find($contactId);
        if($templateObj) {
            if(isset($formdata['template_name'])) {
                $templateObj->setTitle($formdata['template_name']);
            }
            if(isset($formdata['picture_88'])) {
                $templateObj->setHeaderImage($formdata['picture_88']);
            }
            if(isset($formdata['displayType'])) {
                $templateObj->setArticleDisplay($formdata['displayType']);
            }
            if(isset($formdata['background'])) {
                $templateObj->setColorBg($formdata['background']);
            }
            if(isset($formdata['background_table'])) {
                $templateObj->setColorTocBg($formdata['background_table']);
            }
            if(isset($formdata['general_text'])) {
                $templateObj->setColorStdText($formdata['general_text']);
            }
            if(isset($formdata['text_table'])) {
                $templateObj->setColorTocText($formdata['text_table']);
            }
            if(isset($formdata['heading_text'])) {
                $templateObj->setColorTitleText($formdata['heading_text']);
            }            
            if(array_key_exists('sender_name', $formdata)) { 
                $templateObj->setSenderName(trim($formdata['sender_name']));
            }            
            if(array_key_exists('sender_email', $formdata)) { 
                $templateObj->setSenderEmail(trim($formdata['sender_email']));
            }
            if(isset($formdata['salutation_type'])) {
                $templateObj->setSalutationType($formdata['salutation_type']);
            }            
            if(array_key_exists('salutation', $formdata)) { 
                $templateObj->setSalutation($formdata['salutation']);
            }
            if(isset($formdata['language_selection'])) {
                $templateObj->setLanguageSelection($formdata['language_selection']);
            }
            $templateObj->setLastUpdated(new \DateTime($datetime));
            $templateObj->setEditedBy($contactObj);
            $this->_em->flush();                     
            if(array_key_exists('language', $formdata)) {                
                $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplateLang')->updateTemplateLanguage($templateObj, $formdata['language']);
            } 
            if(isset($formdata['sponsor'])) {
                $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplateSponsor')->updateTemplateSponsor($templateObj, $formdata['sponsor']);
            }
        }
        
        return true;
    }

    /**
     * Edit a template detailsm 
     *
     * @param int  $templateid Template id
     * @param type $clubId     Club id
     *
     * @return Array
     */
    public function edittemplatedetails($templateid, $clubId) {
        $query = $this->createQueryBuilder('nt')
                ->select('nt.title', 'nt.id', 'nt.headerImage', 'nt.articleDisplay', 'nt.colorBg', 'nt.colorTocBg', 'nt.colorStdText', 'nt.colorTocText', 'nt.colorTitleText', 
                        'nt.senderName', 'nt.senderEmail', 'nt.salutationType', 'nt.salutation', 'nt.languageSelection' )
                ->addSelect("GROUP_CONCAT(lang.languageCode) AS languages ")
                ->leftJoin('nt.club', 'clb')
                ->leftJoin('CommonUtilityBundle:FgCnNewsletterTemplateLang', 'lang', "WITH", 'lang.template = nt.id')
                ->where('clb.id=:clubId')
                ->andWhere('nt.id=:id')
                ->setParameter('clubId', $clubId)
                ->setParameter('id', $templateid);

        $result = $query->getQuery()->getResult();   
        if(count($result) > 0) {
            $result[0]['language'] = explode(",", $result[0]['languages']);
            $result[0]['selectedlanguageCount'] = count($result[0]['language']);        
        }         

        return $result[0];
    }

    /**
     * Template details for listing
     *
     * @param type $clubId Club id
     *
     * @return Array
     */
    public function listtemplatedetails($clubId) {

        $query = $this->createQueryBuilder('nt')
                ->select('nt.title', 'nt.id', 'nt.headerImage', 'nt.articleDisplay')
                ->where('nt.club=:clubid')
                ->setParameter('clubid', $clubId);
        $result = $query->getQuery()->getResult();

        return $result;
    }

    /**
     * Template count details
     *
     * @param type $clubId Club id
     *
     * @return Array
     */
    public function getTemplateCount($clubId) {
        $resultQuery = $this->createQueryBuilder('nt')
                ->select('count(nt.id)')
                ->where('nt.club=:clubId')
                ->setParameter('clubId', $clubId);

        $result = $resultQuery->getQuery()->getSingleScalarResult();

        return ($result) ? $result : false;
    }

    /**
     * Template listing details for datatable
     *
     * @param type    $clubId    Club id
     * @param int     $start     Start offset
     * @param int     $length    Limit
     * @param string  $orderBy   Order parameter
     * @param string  $orderAs   Order parameter
     * @param string  $editpath  Edit path
     * @param boolean $limitFlag limit flag whether limit condition is neeeded 
     *
     * @return Array
     */
    public function getTemplateList($clubId, $start = 0, $length = 50, $orderBy = 'title', $orderAs = 'asc', $editpath = '', $limitFlag=false) {
        $dateFormat = FgSettings::getMysqlDateFormat();
        $conn = $this->getEntityManager()->getConnection();
        $limitCondition = ($limitFlag) ? '' : "limit $start,$length";
        $templateQuery = "SELECT nt.id,nt.title,DATE_FORMAT(nt.created_on,'$dateFormat') as created_on,"
                . "DATE_FORMAT(nt.last_updated,'$dateFormat') as last_updated,contactName(nt.edited_by) as edited_by"
                . " FROM fg_cn_newsletter_template nt "
                . "WHERE nt.club_id= $clubId  order by (CASE WHEN " . $orderBy . " IS NULL then 3 WHEN " . $orderBy . "='' then 2 WHEN " . $orderBy . "='0000-00-00 00:00:00' then 1 ELSE 0 END),".'nt.'."$orderBy $orderAs $limitCondition ";

        $result = $conn->fetchAll($templateQuery);

        foreach ($result as $key => $template) {
            $path = str_replace("id", $template['id'], $editpath);
            $title = str_replace("<", "&lt;", str_replace(">", "&gt;", $template['title']));
            $templateArray[] = array($template['id'], $title, $template['created_on'], $template['last_updated'], $template['edited_by'], $path);
        }

        return (count($templateArray) > 0) ? $templateArray : array();
    }

    /**
     * Template duplicate
     *
     * @param int    $templateId Start offset
     * @param string $copyText   Copy text
     * @param int    $clubId     Club Id
     * @param int    $contactId  Contact-Id
     *
     * @return type
     */
    public function getDuplicateDetails($templateId, $copyText, $clubId, $contactId) {           
        $templateObj = $this->find($templateId);
        $insertArray = array();
        if($templateObj) {
            $insertArray["template_name"] = $copyText . ' ' .$templateObj->getTitle();
            $filename = $templateObj->getHeaderImage();
            $newsletterHeaderUploadFolder = FgUtility::getUploadFilePath($templateObj->getClub()->getId(),'newsletter_header');
            $uploadPath = "$newsletterHeaderUploadFolder/";
            if (is_file($uploadPath . $filename)) {
                $fileName = FgUtility::getFilename($uploadPath, $filename);
                copy($uploadPath . $filename, $uploadPath . $fileName);
            }
            $insertArray["picture_88"] = $fileName;
            $insertArray["displayType"] = $templateObj->getArticleDisplay();
            $insertArray["background"] = $templateObj->getColorBg();
            $insertArray["background_table"] = $templateObj->getColorTocBg();
            $insertArray["general_text"] = $templateObj->getColorStdText();
            $insertArray["text_table"] = $templateObj->getColorTocText();
            $insertArray["heading_text"] = $templateObj->getColorTitleText();
            $insertArray["sender_name"] = $templateObj->getSenderName();
            $insertArray["sender_email"] = $templateObj->getSenderEmail();
            $insertArray["salutation_type"] = $templateObj->getSalutationType();
            $insertArray["salutation"] = $templateObj->getSalutation();
            $insertArray["language_selection"] = $templateObj->getLanguageSelection();            
            $languages = $this->_em->getRepository('CommonUtilityBundle:FgCnNewsletterTemplateLang')->getTemplateLanguages($templateId);
            if($languages) {
                $insertArray["language"] = explode(",", $languages);
            }   
            $sponsors = $this->getNewsletterTemplateSponsorContents($clubId, $templateId);       
            if($sponsors) {
                $sponsorContent = array();                
                foreach($sponsors as $key => $sponsor) {
                    $datetime = date("YmdHis").$key;
                    $sponsorContent[$datetime]["adarea"] = $sponsor['sponsorAdArea'];
                    $sponsorContent[$datetime]["sort_order"] = $sponsor['sortOrder'];
                    $sponsorContent[$datetime]["width"] = $sponsor['sponsorAdWidth'];
                    $sponsorContent[$datetime]["title"] = $sponsor['title'];
                    $sponsorContent[$datetime]["position"] = $sponsor['position'];
                    $sponsorContent[$datetime]["services"] = $sponsor['services'];
                }
                
                $insertArray["sponsor"] = $sponsorContent;
            }
            
            $this->createtemplate($clubId, $contactId, $insertArray);
        }
        
        return true;
   }

    /**
     * Template delete
     *
     * @param Array $selectedId Selected id
     * @param type  $clubId     Club Id
     *
     * @return type
     */
    public function deleteTemplate($selectedId, $clubId) {
        $conn = $this->getEntityManager()->getConnection();
        $templateId = implode(',', $selectedId);
        $newsletterHeaderUploadFolder = FgUtility::getUploadFilePath($clubId,'newsletter_header');
        $imagesRemoveSelect = "SELECT header_image,club_id FROM fg_cn_newsletter_template WHERE id IN ($templateId)";
        $result = $conn->fetchAll($imagesRemoveSelect);
        $uploadPath = "$newsletterHeaderUploadFolder/";

        foreach ($result as $remove) {
            unlink($uploadPath . $remove['header_image']);
        }

        $deleteQuery = "DELETE FROM fg_cn_newsletter_template WHERE id IN ($templateId) ";
        $conn->executeQuery($deleteQuery);

        return true;
    }
    
    /**
     * Function to get template sponsor contents for preview
     *
     * @param int     $clubId      ClubId
     * @param int     $templateId  TemplateId
     * @param boolean $foreachFlag Whether to return looped result or not
     *
     * @return array
     */
    public function getNewsletterTemplateSponsorContents($clubId, $templateId, $foreachFlag = true) {       
        $resultQuery = $this->createQueryBuilder('T')
                ->select("S.id, S.title, S.sortOrder, S.position, IDENTITY(S.sponsorAdArea) as sponsorAdArea, S.sponsorAdWidth, GROUP_CONCAT(DISTINCT SERVICE.id) as services ")
                ->join("CommonUtilityBundle:FgCnNewsletterTemplateSponsor", "S", "WITH", "S.template = T.id" )
                ->leftJoin("CommonUtilityBundle:FgCnNewsletterTemplateServices", "SER", "WITH", "SER.templateSponsor = S.id" )
                ->leftJoin("CommonUtilityBundle:FgSmServices", "SERVICE", "WITH", "SER.services = SERVICE.id" )                
                ->where('T.club=:clubId')
                ->andWhere("T.id = :templateId")
                ->groupBy("S.id")
                ->orderBy("S.sortOrder", "ASC")
                ->setParameters(array('clubId' => $clubId, "templateId" => $templateId));
        $result = $resultQuery->getQuery()->getArrayResult();
        if ($foreachFlag) {
            foreach($result as $key => $value) {
                $result[$key]['services'] = explode(",", $result[$key]['services']);
            }
        }
        
        return $result;
    }
    
    /**
     * Function to get default template sponsor contents for create newsletter step 3
     *
     * @param int $clubId     clubId
     * @param int $templateId templateId
     *
     * @return array $sponsorContents Sponsor details
     */
    public function getDefaultNewsletterTemplateSponsorContents($clubId, $templateId)
    {       
        $resultQuery = $this->createQueryBuilder('T')
                ->select("(CASE WHEN S.position = 'ABOVE' THEN 'SPONSOR ABOVE' WHEN S.position = 'BOTTOM' THEN 'SPONSOR BOTTOM' ELSE 'SPONSOR' END) AS type, (CASE WHEN S.position = 'ABOVE' THEN 'sponsor_above' WHEN S.position = 'BOTTOM' THEN 'sponsor_bottom' ELSE 'sponsor' END) AS contentType, S.id, 1 AS isActive, S.title, S.sortOrder, S.position, IDENTITY(S.sponsorAdArea) as sponsorAdArea, S.sponsorAdWidth, GROUP_CONCAT(DISTINCT SERVICE.id) as services, '' AS sponsorAds ")
                ->join("CommonUtilityBundle:FgCnNewsletterTemplateSponsor", "S", "WITH", "S.template = T.id" )
                ->leftJoin("CommonUtilityBundle:FgCnNewsletterTemplateServices", "SER", "WITH", "SER.templateSponsor = S.id" )
                ->leftJoin("CommonUtilityBundle:FgSmServices", "SERVICE", "WITH", "SER.services = SERVICE.id" )                
                ->where('T.club=:clubId')
                ->andWhere("T.id = :templateId")
                ->groupBy("S.id")
                ->orderBy("S.sortOrder", "ASC")
                ->setParameters(array('clubId' => $clubId, "templateId" => $templateId));
        $result = $resultQuery->getQuery()->getArrayResult();

        $sponsorContents = array();
        $randomId = rand(888888888888, 999999999999);
        foreach($result as $key => $value) {
            $value['id'] = $randomId++;
            $value['services'] = explode(",", $value['services']);
            $sponsorContents[$value['type']][] = $value;
        }

        return $sponsorContents;
    }
}

