<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 namespace Common\UtilityBundle\Repository;

 use Doctrine\ORM\EntityRepository;
 use Common\UtilityBundle\Util\FgUtility;
// use Doctrine\DBAL\Query\QueryBuilder;
/**
 * This repository is used for handling newsletter template functionality
 *
 *
 */
class FgCnNewsletterArticleDocumentsRepository extends EntityRepository
{
    /**
     * function toget the attachemnts of simple mail newsletter along with its size
     *
     * @param int $newsletterId the newsletter id
     * @param int $clubId the club id
     * @param object $container the container
     *
     * @return array of filename ande size
     */
     public function getSimpleNlAttachments($newsletterId, $clubId, $container)
     {
        $rootPath=FgUtility::getRootPath($container);
        $filenames = array();
        $query = $this->createQueryBuilder('d')
                ->select('FM.encryptedFilename AS fileName, d.title as fileTitle,d.id AS docId, FMV.size as fileSize,FM.virtualFilename as virtualname')
                ->leftJoin('d.fileManager', 'FM')
                ->leftJoin('FM.latestVersion', 'FMV')
                ->where('d.newsletter=:newsletterId')
                ->setParameter('newsletterId', $newsletterId);

        $result = $query->getQuery()->getResult();
        $i = 0;
        $size = '0 bytes';
        $communicationUploadFolder = FgUtility::getUploadFilePath($clubId,'communication');
        foreach ($result as $key => $val) {
            //$size = FgUtility::formatSizeUnits(filesize($rootPath.'/uploads/'.$clubId.'/communication/documents/newsletter_documents/'.$val['fileName']));
            $size = FgUtility::formatSizeUnits($val['fileSize']);
            $filenames[$i]['docId'] = $val['docId'];
            $filenames[$i]['name'] = $val['fileName'];
            $filenames[$i]['virtualname'] = $val['virtualname'];
            $filenames[$i]['fileTitle'] = $val['fileTitle'];
            $filenames[$i]['size'] = $size;
            $totalsize +=  $val['fileSize'];
           // $totalsize +=  filesize($rootPath.'/uploads/'.$clubId.'/communication/documents/newsletter_documents/'.$val['fileName']);
            $i++;
        }
        $filenames['totalsize'] = $totalsize;
        $filenames['formattedSize'] = FgUtility::formatSizeUnits($totalsize);

        return $filenames;
     }

     /**
      * Function to get simple mail attachments
      * @param type $newsletterId
      * @return type
      */
     public function getAttachmentsOfSimpleMail($newsletterId)
     {
        $query = $this->createQueryBuilder('d')
                ->select('FM.encryptedFilename AS filename, d.title, d.docType')
                ->leftJoin('d.fileManager', 'FM')
                ->where('d.newsletter=:newsletterId')
                ->orderBy('d.sortOrder', 'ASC')
                ->setParameter('newsletterId', $newsletterId);

        $result = $query->getQuery()->getResult();

        return $result;
     }
}


