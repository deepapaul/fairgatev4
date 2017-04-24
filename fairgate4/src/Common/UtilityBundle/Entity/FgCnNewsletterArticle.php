<?php

namespace Common\UtilityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * FgCnNewsletterArticle
 */
class FgCnNewsletterArticle
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $teaserText;

    /**
     * @var string
     */
    private $content;

    /**
     * @var \Common\UtilityBundle\Entity\FgCnNewsletterContent
     */
    private $content2;


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
     * Set title
     *
     * @param string $title
     * @return FgCnNewsletterArticle
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set teaserText
     *
     * @param string $teaserText
     * @return FgCnNewsletterArticle
     */
    public function setTeaserText($teaserText)
    {
        $this->teaserText = $teaserText;

        return $this;
    }

    /**
     * Get teaserText
     *
     * @return string
     */
    public function getTeaserText()
    {
        return $this->teaserText;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return FgCnNewsletterArticle
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set content2
     *
     * @param \Common\UtilityBundle\Entity\FgCnNewsletterContent $content2
     * @return FgCnNewsletterArticle
     */
    public function setContent2(\Common\UtilityBundle\Entity\FgCnNewsletterContent $content2 = null)
    {
        $this->content2 = $content2;

        return $this;
    }

    /**
     * Get content2
     *
     * @return \Common\UtilityBundle\Entity\FgCnNewsletterContent
     */
    public function getContent2()
    {
        return $this->content2;
    }
}