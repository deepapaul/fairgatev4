<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgDmDocumentLog
 */
class FgDmDocumentLog
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $documentType;

    /**
     * @var \DateTime
     */
    private $date;

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
     * @var string
     */
    private $valueBeforeId;

    /**
     * @var string
     */
    private $valueAfterId;

    /**
     * @var \Common\UtilityBundle\Entity\FgDmDocuments
     */
    private $documents;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $changedBy;


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
     * Set documentType
     *
     * @param string $documentType
     *
     * @return FgDmDocumentLog
     */
    public function setDocumentType($documentType)
    {
        $this->documentType = $documentType;

        return $this;
    }

    /**
     * Get documentType
     *
     * @return string
     */
    public function getDocumentType()
    {
        return $this->documentType;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return FgDmDocumentLog
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
     * Set kind
     *
     * @param string $kind
     *
     * @return FgDmDocumentLog
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
     * @return FgDmDocumentLog
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
     * @return FgDmDocumentLog
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
     * @return FgDmDocumentLog
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
     * Set valueBeforeId
     *
     * @param string $valueBeforeId
     *
     * @return FgDmDocumentLog
     */
    public function setValueBeforeId($valueBeforeId)
    {
        $this->valueBeforeId = $valueBeforeId;

        return $this;
    }

    /**
     * Get valueBeforeId
     *
     * @return string
     */
    public function getValueBeforeId()
    {
        return $this->valueBeforeId;
    }

    /**
     * Set valueAfterId
     *
     * @param string $valueAfterId
     *
     * @return FgDmDocumentLog
     */
    public function setValueAfterId($valueAfterId)
    {
        $this->valueAfterId = $valueAfterId;

        return $this;
    }

    /**
     * Get valueAfterId
     *
     * @return string
     */
    public function getValueAfterId()
    {
        return $this->valueAfterId;
    }

    /**
     * Set documents
     *
     * @param \Common\UtilityBundle\Entity\FgDmDocuments $documents
     *
     * @return FgDmDocumentLog
     */
    public function setDocuments(\Common\UtilityBundle\Entity\FgDmDocuments $documents = null)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get documents
     *
     * @return \Common\UtilityBundle\Entity\FgDmDocuments
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgDmDocumentLog
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
     * Set changedBy
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $changedBy
     *
     * @return FgDmDocumentLog
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
}

