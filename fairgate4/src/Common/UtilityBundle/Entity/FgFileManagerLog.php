<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgFileManagerLog
 */
class FgFileManagerLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $kind;

    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $valueAfter;

    /**
     * @var string
     */
    private $valueBefore;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;

    /**
     * @var \Common\UtilityBundle\Entity\FgFileManager
     */
    private $fileManager;


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
     * Set kind
     *
     * @param string $kind
     *
     * @return FgFileManagerLog
     */
    public function setKind($kind)
    {
        $this->kind = $kind;

        return $this;
    }

    /**
     * Get kind
     *
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Set field
     *
     * @param string $field
     *
     * @return FgFileManagerLog
     */
    public function setField($field)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set valueAfter
     *
     * @param string $valueAfter
     *
     * @return FgFileManagerLog
     */
    public function setValueAfter($valueAfter)
    {
        $this->valueAfter = $valueAfter;

        return $this;
    }

    /**
     * Get valueAfter
     *
     * @return string
     */
    public function getValueAfter()
    {
        return $this->valueAfter;
    }

    /**
     * Set valueBefore
     *
     * @param string $valueBefore
     *
     * @return FgFileManagerLog
     */
    public function setValueBefore($valueBefore)
    {
        $this->valueBefore = $valueBefore;

        return $this;
    }

    /**
     * Get valueBefore
     *
     * @return string
     */
    public function getValueBefore()
    {
        return $this->valueBefore;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FgFileManagerLog
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
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgFileManagerLog
     */
    public function setChangedBy(\Common\UtilityBundle\Entity\FgCmContact $changedBy = null)
    {
        $this->changedBy = $changedBy;

        return $this;
    }

    /**
     * Get changedBy
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getChangedBy()
    {
        return $this->changedBy;
    }

    /**
     * Set fileManager
     *
     * @param \Common\UtilityBundle\Entity\FgFileManager $fileManager
     *
     * @return FgFileManagerLog
     */
    public function setFileManager(\Common\UtilityBundle\Entity\FgFileManager $fileManager = null)
    {
        $this->fileManager = $fileManager;

        return $this;
    }

    /**
     * Get fileManager
     *
     * @return \Common\UtilityBundle\Entity\FgFileManager
     */
    public function getFileManager()
    {
        return $this->fileManager;
    }
}

