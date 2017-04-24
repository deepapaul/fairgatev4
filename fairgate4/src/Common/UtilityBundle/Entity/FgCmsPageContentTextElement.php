<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentTextElement
 */
class FgCmsPageContentTextElement
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $text;

    /**
     * @var string
     */
    private $position;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory
     */
    private $version;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    private $element;


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
     * Set text
     *
     * @param string $text
     *
     * @return FgCmsPageContentTextElement
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return FgCmsPageContentTextElement
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set version
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory $version
     *
     * @return FgCmsPageContentTextElement
     */
    public function setVersion(\Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory $version = null)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentTextElementHistory
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set element
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentElement $element
     *
     * @return FgCmsPageContentTextElement
     */
    public function setElement(\Common\UtilityBundle\Entity\FgCmsPageContentElement $element = null)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * Get element
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentElement
     */
    public function getElement()
    {
        return $this->element;
    }
}

