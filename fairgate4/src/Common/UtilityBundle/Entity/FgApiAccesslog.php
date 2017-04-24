<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgApiAccesslog
 */
class FgApiAccesslog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $requestDetail;

    /**
     * @var string
     */
    private $requestClientip;

    /**
     * @var string
     */
    private $responseDetail;
    
    /**
     * @var string
     */
    private $responseCode;

    /**
     * @var \Common\UtilityBundle\Entity\FgApis
     */
    private $api;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;


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
     * Set apiUrl
     *
     * @param string $apiUrl
     * @return FgApiAccesslog
     */
    public function setApiUrl($apiUrl)
    {
        $this->apiUrl = $apiUrl;
    
        return $this;
    }

    /**
     * Get apiUrl
     *
     * @return string 
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return FgApiAccesslog
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set requestDetail
     *
     * @param string $requestDetail
     * @return FgApiAccesslog
     */
    public function setRequestDetail($requestDetail)
    {
        $this->requestDetail = $requestDetail;
    
        return $this;
    }

    /**
     * Get requestDetail
     *
     * @return string 
     */
    public function getRequestDetail()
    {
        return $this->requestDetail;
    }

    /**
     * Set requestClientip
     *
     * @param string $requestClientip
     * @return FgApiAccesslog
     */
    public function setRequestClientip($requestClientip)
    {
        $this->requestClientip = $requestClientip;
    
        return $this;
    }

    /**
     * Get requestClientip
     *
     * @return string 
     */
    public function getRequestClientip()
    {
        return $this->requestClientip;
    }

    /**
     * Set responseDetail
     *
     * @param string $responseDetail
     * @return FgApiAccesslog
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
     * Set responseCode
     *
     * @param string responseCode
     * @return FgApiAccesslog
     */
    public function setResponseCode($responseCode)
    {
        $this->responseCode = $responseCode;
    
        return $this;
    }

    /**
     * Get responseCode
     *
     * @return string 
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Set api
     *
     * @param \Common\UtilityBundle\Entity\FgApis $api
     * @return FgApiAccesslog
     */
    public function setApi(\Common\UtilityBundle\Entity\FgApis $api = null)
    {
        $this->api = $api;
    
        return $this;
    }

    /**
     * Get api
     *
     * @return \Common\UtilityBundle\Entity\FgApis 
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgApiAccesslog
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
}
