<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgNotificationSpool
 */
class FgNotificationSpool
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $notificationType;

    /**
     * @var string
     */
    private $email;

    /**
     * @var string
     */
    private $templateContent;


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
     * Set notificationType
     *
     * @param string $notificationType
     * @return FgNotificationSpool
     */
    public function setNotificationType($notificationType)
    {
        $this->notificationType = $notificationType;
    
        return $this;
    }

    /**
     * Get notificationType
     *
     * @return string 
     */
    public function getNotificationType()
    {
        return $this->notificationType;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return FgNotificationSpool
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set templateContent
     *
     * @param string $templateContent
     * @return FgNotificationSpool
     */
    public function setTemplateContent($templateContent)
    {
        $this->templateContent = $templateContent;
    
        return $this;
    }

    /**
     * Get templateContent
     *
     * @return string 
     */
    public function getTemplateContent()
    {
        return $this->templateContent;
    }
}
