<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCmsFormsI18n
 */
class FgCmsFormsI18n
{
    /**
     * @var string
     */
    private $lang;

    /**
     * @var string
     */
    private $confirmationEmailSubjectLang;

    /**
     * @var string
     */
    private $confirmationEmailContentLang;

    /**
     * @var string
     */
    private $acceptanceEmailSubjectLang;

    /**
     * @var string
     */
    private $acceptanceEmailContentLang;

    /**
     * @var string
     */
    private $dismissalEmailSubjectLang;

    /**
     * @var string
     */
    private $dismissalEmailContentLang;

    /**
     * @var string
     */
    private $successMessageLang;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsForms
     */
    private $id;


    /**
     * Set lang
     *
     * @param string $lang
     * @return FgCmsFormsI18n
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    
        return $this;
    }

    /**
     * Get lang
     *
     * @return string 
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * Set confirmationEmailSubjectLang
     *
     * @param string $confirmationEmailSubjectLang
     * @return FgCmsFormsI18n
     */
    public function setConfirmationEmailSubjectLang($confirmationEmailSubjectLang)
    {
        $this->confirmationEmailSubjectLang = $confirmationEmailSubjectLang;
    
        return $this;
    }

    /**
     * Get confirmationEmailSubjectLang
     *
     * @return string 
     */
    public function getConfirmationEmailSubjectLang()
    {
        return $this->confirmationEmailSubjectLang;
    }

    /**
     * Set confirmationEmailContentLang
     *
     * @param string $confirmationEmailContentLang
     * @return FgCmsFormsI18n
     */
    public function setConfirmationEmailContentLang($confirmationEmailContentLang)
    {
        $this->confirmationEmailContentLang = $confirmationEmailContentLang;
    
        return $this;
    }

    /**
     * Get confirmationEmailContentLang
     *
     * @return string 
     */
    public function getConfirmationEmailContentLang()
    {
        return $this->confirmationEmailContentLang;
    }

    /**
     * Set acceptanceEmailSubjectLang
     *
     * @param string $acceptanceEmailSubjectLang
     * @return FgCmsFormsI18n
     */
    public function setAcceptanceEmailSubjectLang($acceptanceEmailSubjectLang)
    {
        $this->acceptanceEmailSubjectLang = $acceptanceEmailSubjectLang;
    
        return $this;
    }

    /**
     * Get acceptanceEmailSubjectLang
     *
     * @return string 
     */
    public function getAcceptanceEmailSubjectLang()
    {
        return $this->acceptanceEmailSubjectLang;
    }

    /**
     * Set acceptanceEmailContentLang
     *
     * @param string $acceptanceEmailContentLang
     * @return FgCmsFormsI18n
     */
    public function setAcceptanceEmailContentLang($acceptanceEmailContentLang)
    {
        $this->acceptanceEmailContentLang = $acceptanceEmailContentLang;
    
        return $this;
    }

    /**
     * Get acceptanceEmailContentLang
     *
     * @return string 
     */
    public function getAcceptanceEmailContentLang()
    {
        return $this->acceptanceEmailContentLang;
    }

    /**
     * Set dismissalEmailSubjectLang
     *
     * @param string $dismissalEmailSubjectLang
     * @return FgCmsFormsI18n
     */
    public function setDismissalEmailSubjectLang($dismissalEmailSubjectLang)
    {
        $this->dismissalEmailSubjectLang = $dismissalEmailSubjectLang;
    
        return $this;
    }

    /**
     * Get dismissalEmailSubjectLang
     *
     * @return string 
     */
    public function getDismissalEmailSubjectLang()
    {
        return $this->dismissalEmailSubjectLang;
    }

    /**
     * Set dismissalEmailContentLang
     *
     * @param string $dismissalEmailContentLang
     * @return FgCmsFormsI18n
     */
    public function setDismissalEmailContentLang($dismissalEmailContentLang)
    {
        $this->dismissalEmailContentLang = $dismissalEmailContentLang;
    
        return $this;
    }

    /**
     * Get dismissalEmailContentLang
     *
     * @return string 
     */
    public function getDismissalEmailContentLang()
    {
        return $this->dismissalEmailContentLang;
    }

    /**
     * Set successMessageLang
     *
     * @param string $successMessageLang
     * @return FgCmsFormsI18n
     */
    public function setSuccessMessageLang($successMessageLang)
    {
        $this->successMessageLang = $successMessageLang;
    
        return $this;
    }

    /**
     * Get successMessageLang
     *
     * @return string 
     */
    public function getSuccessMessageLang()
    {
        return $this->successMessageLang;
    }

    /**
     * Set id
     *
     * @param \Common\UtilityBundle\Entity\FgCmsForms $id
     * @return FgCmsFormsI18n
     */
    public function setId(\Common\UtilityBundle\Entity\FgCmsForms $id = null)
    {
        $this->id = $id;
    
        return $this;
    }

    /**
     * Get id
     *
     * @return \Common\UtilityBundle\Entity\FgCmsForms 
     */
    public function getId()
    {
        return $this->id;
    }
}