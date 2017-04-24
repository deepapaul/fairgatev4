<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterLog
 */
class FgCnNewsletterLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var string
     */
    private $subject;

    /**
     * @var string
     */
    private $template;

    /**
     * @var string
     */
    private $newsletterType;

    /**
     * @var integer
     */
    private $recepients;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    private $newsletter;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $sentBy;

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
     * Set date
     *
     * @param \DateTime $date
     * @return FgCnNewsletterLog
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
     * Set subject
     *
     * @param string $subject
     * @return FgCnNewsletterLog
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set template
     *
     * @param string $template
     * @return FgCnNewsletterLog
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set newsletterType
     *
     * @param string $newsletterType
     * @return FgCnNewsletterLog
     */
    public function setNewsletterType($newsletterType)
    {
        $this->newsletterType = $newsletterType;

        return $this;
    }

    /**
     * Get newsletterType
     *
     * @return string
     */
    public function getNewsletterType()
    {
        return $this->newsletterType;
    }

    /**
     * Set recepients
     *
     * @param integer $recepients
     * @return FgCnNewsletterLog
     */
    public function setRecepients($recepients)
    {
        $this->recepients = $recepients;

        return $this;
    }

    /**
     * Get recepients
     *
     * @return integer
     */
    public function getRecepients()
    {
        return $this->recepients;
    }

    /**
     * Set newsletter
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletter $newsletter
     * @return FgCnNewsletterLog
     */
    public function setNewsletter(\Common\UtilityBundle\Entity\FgCnNewsletter $newsletter = null)
    {
        $this->newsletter = $newsletter;

        return $this;
    }

    /**
     * Get newsletter
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletter
     */
    public function getNewsletter()
    {
        return $this->newsletter;
    }

    /**
     * Set sentBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $sentBy
     * @return FgCnNewsletterLog
     */
    public function setSentBy(\Common\UtilityBundle\Entity\FgCmContact $sentBy = null)
    {
        $this->sentBy = $sentBy;

        return $this;
    }

    /**
     * Get sentBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getSentBy()
    {
        return $this->sentBy;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return FgCnNewsletterLog
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