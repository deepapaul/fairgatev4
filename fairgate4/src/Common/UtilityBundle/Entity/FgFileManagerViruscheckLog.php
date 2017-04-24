<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgFileManagerViruscheckLog
 */
class FgFileManagerViruscheckLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $fileDetails;

    /**
     * @var \DateTime
     */
    private $requestSenton;

    /**
     * @var string
     */
    private $responseStatus;

    /**
     * @var \DateTime
     */
    private $responseReceivedon;

    /**
     * @var string
     */
    private $responseDetail;

    /**
     * @var string
     */
    private $avastscanOption;

    /**
     * @var \DateTime
     */
    private $logDate;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set fileName
     *
     * @param string $fileName
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set fileDetails
     *
     * @param string $fileDetails
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setFileDetails($fileDetails)
    {
        $this->fileDetails = $fileDetails;

        return $this;
    }

    /**
     * Get fileDetails
     *
     * @return string
     */
    public function getFileDetails()
    {
        return $this->fileDetails;
    }

    /**
     * Set requestSenton
     *
     * @param \DateTime $requestSenton
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setRequestSenton($requestSenton)
    {
        $this->requestSenton = $requestSenton;

        return $this;
    }

    /**
     * Get requestSenton
     *
     * @return \DateTime
     */
    public function getRequestSenton()
    {
        return $this->requestSenton;
    }

    /**
     * Set responseStatus
     *
     * @param string $responseStatus
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setResponseStatus($responseStatus)
    {
        $this->responseStatus = $responseStatus;

        return $this;
    }

    /**
     * Get responseStatus
     *
     * @return string
     */
    public function getResponseStatus()
    {
        return $this->responseStatus;
    }

    /**
     * Set responseReceivedon
     *
     * @param \DateTime $responseReceivedon
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setResponseReceivedon($responseReceivedon)
    {
        $this->responseReceivedon = $responseReceivedon;

        return $this;
    }

    /**
     * Get responseReceivedon
     *
     * @return \DateTime
     */
    public function getResponseReceivedon()
    {
        return $this->responseReceivedon;
    }

    /**
     * Set responseDetail
     *
     * @param string $responseDetail
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setResponseDetail($responseDetail)
    {
        $this->responseDetail = $responseDetail;

        return $this;
    }

    /**
     * Get responseDetail
     *
     * @return string
     */
    public function getResponseDetail()
    {
        return $this->responseDetail;
    }

    /**
     * Set avastscanOption
     *
     * @param string $avastscanOption
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setAvastscanOption($avastscanOption)
    {
        $this->avastscanOption = $avastscanOption;

        return $this;
    }

    /**
     * Get avastscanOption
     *
     * @return string
     */
    public function getAvastscanOption()
    {
        return $this->avastscanOption;
    }

    /**
     * Set logDate
     *
     * @param \DateTime $logDate
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setLogDate($logDate)
    {
        $this->logDate = $logDate;

        return $this;
    }

    /**
     * Get logDate
     *
     * @return \DateTime
     */
    public function getLogDate()
    {
        return $this->logDate;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setClub(\Common\UtilityBundle\Entity\FgClub $club = null)
    {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getClub()
    {
        return $this->club;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     *
     * @return FgFileManagerViruscheckLog
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact()
    {
        return $this->contact;
    }
}

