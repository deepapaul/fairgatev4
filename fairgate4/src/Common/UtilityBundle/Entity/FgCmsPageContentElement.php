<?php

namespace Common\UtilityBundle\Entity;

/**
 * FgCmsPageContentElement
 */
class FgCmsPageContentElement
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $sortOrder;

    /**
     * @var boolean
     */
    private $isDeleted = '0';

    /**
     * @var \DateTime
     */
    private $deletedAt;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $headerElementSize;

    /**
     * @var string
     */
    private $imageElementDisplayType;

    /**
     * @var integer
     */
    private $imageElementSliderTime;

    /**
     * @var string
     */
    private $imageElementClickType;

    /**
     * @var string
     */
    private $imageElementLinkOpentype;

    /**
     * @var float
     */
    private $mapElementLatitude;

    /**
     * @var float
     */
    private $mapElementLongitude;

    /**
     * @var boolean
     */
    private $mapElementShowMarker = '1';

    /**
     * @var integer
     */
    private $mapElementHeight;

    /**
     * @var string
     */
    private $mapElementDisplayStyle;

    /**
     * @var integer
     */
    private $mapElementZoomValue;

    /**
     * @var string
     */
    private $iframeElementCode;

    /**
     * @var string
     */
    private $iframeElementUrl;

    /**
     * @var integer
     */
    private $iframeElementHeight;

    /**
     * @var boolean
     */
    private $isAllCategory;

    /**
     * @var boolean
     */
    private $isAllArea;

    /**
     * @var string
     */
    private $sharedClub;

    /**
     * @var string
     */
    private $sponsorAdDisplayType;

    /**
     * @var integer
     */
    private $sponsorAdDisplayTime;

    /**
     * @var string
     */
    private $sponsorAdMaxWidth;

    /**
     * @var string
     */
    private $twitterDefaultAccount;

    /**
     * @var integer
     */
    private $twitterContentHeight;

    /**
     * @var string
     */
    private $articleDisplayType = 'listing';

    /**
     * @var integer
     */
    private $articlePerRow = '1';

    /**
     * @var integer
     */
    private $articleCount = '5';

    /**
     * @var string
     */
    private $articleSliderNavigation = 'none';

    /**
     * @var boolean
     */
    private $articleShowThumbImg = '0';

    /**
     * @var boolean
     */
    private $articleShowDate = '0';

    /**
     * @var boolean
     */
    private $articleShowCategory = '0';

    /**
     * @var boolean
     */
    private $articleShowArea = '0';

    /**
     * @var integer
     */
    private $articleRowsCount = '1';

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContentType
     */
    private $pageContentType;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsPageContainerBox
     */
    private $box;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $areaClub;

    /**
     * @var \Common\UtilityBundle\Entity\FgSmAdArea
     */
    private $sponsorAdArea;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsForms
     */
    private $form;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmsContactTable
     */
    private $table;


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
     * Set sortOrder
     *
     * @param integer $sortOrder
     *
     * @return FgCmsPageContentElement
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set isDeleted
     *
     * @param boolean $isDeleted
     *
     * @return FgCmsPageContentElement
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted
     *
     * @return boolean
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return FgCmsPageContentElement
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return FgCmsPageContentElement
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
     * Set headerElementSize
     *
     * @param string $headerElementSize
     *
     * @return FgCmsPageContentElement
     */
    public function setHeaderElementSize($headerElementSize)
    {
        $this->headerElementSize = $headerElementSize;

        return $this;
    }

    /**
     * Get headerElementSize
     *
     * @return string
     */
    public function getHeaderElementSize()
    {
        return $this->headerElementSize;
    }

    /**
     * Set imageElementDisplayType
     *
     * @param string $imageElementDisplayType
     *
     * @return FgCmsPageContentElement
     */
    public function setImageElementDisplayType($imageElementDisplayType)
    {
        $this->imageElementDisplayType = $imageElementDisplayType;

        return $this;
    }

    /**
     * Get imageElementDisplayType
     *
     * @return string
     */
    public function getImageElementDisplayType()
    {
        return $this->imageElementDisplayType;
    }

    /**
     * Set imageElementSliderTime
     *
     * @param integer $imageElementSliderTime
     *
     * @return FgCmsPageContentElement
     */
    public function setImageElementSliderTime($imageElementSliderTime)
    {
        $this->imageElementSliderTime = $imageElementSliderTime;

        return $this;
    }

    /**
     * Get imageElementSliderTime
     *
     * @return integer
     */
    public function getImageElementSliderTime()
    {
        return $this->imageElementSliderTime;
    }

    /**
     * Set imageElementClickType
     *
     * @param string $imageElementClickType
     *
     * @return FgCmsPageContentElement
     */
    public function setImageElementClickType($imageElementClickType)
    {
        $this->imageElementClickType = $imageElementClickType;

        return $this;
    }

    /**
     * Get imageElementClickType
     *
     * @return string
     */
    public function getImageElementClickType()
    {
        return $this->imageElementClickType;
    }

    /**
     * Set imageElementLinkOpentype
     *
     * @param string $imageElementLinkOpentype
     *
     * @return FgCmsPageContentElement
     */
    public function setImageElementLinkOpentype($imageElementLinkOpentype)
    {
        $this->imageElementLinkOpentype = $imageElementLinkOpentype;

        return $this;
    }

    /**
     * Get imageElementLinkOpentype
     *
     * @return string
     */
    public function getImageElementLinkOpentype()
    {
        return $this->imageElementLinkOpentype;
    }

    /**
     * Set mapElementLatitude
     *
     * @param float $mapElementLatitude
     *
     * @return FgCmsPageContentElement
     */
    public function setMapElementLatitude($mapElementLatitude)
    {
        $this->mapElementLatitude = $mapElementLatitude;

        return $this;
    }

    /**
     * Get mapElementLatitude
     *
     * @return float
     */
    public function getMapElementLatitude()
    {
        return $this->mapElementLatitude;
    }

    /**
     * Set mapElementLongitude
     *
     * @param float $mapElementLongitude
     *
     * @return FgCmsPageContentElement
     */
    public function setMapElementLongitude($mapElementLongitude)
    {
        $this->mapElementLongitude = $mapElementLongitude;

        return $this;
    }

    /**
     * Get mapElementLongitude
     *
     * @return float
     */
    public function getMapElementLongitude()
    {
        return $this->mapElementLongitude;
    }

    /**
     * Set mapElementShowMarker
     *
     * @param boolean $mapElementShowMarker
     *
     * @return FgCmsPageContentElement
     */
    public function setMapElementShowMarker($mapElementShowMarker)
    {
        $this->mapElementShowMarker = $mapElementShowMarker;

        return $this;
    }

    /**
     * Get mapElementShowMarker
     *
     * @return boolean
     */
    public function getMapElementShowMarker()
    {
        return $this->mapElementShowMarker;
    }

    /**
     * Set mapElementHeight
     *
     * @param integer $mapElementHeight
     *
     * @return FgCmsPageContentElement
     */
    public function setMapElementHeight($mapElementHeight)
    {
        $this->mapElementHeight = $mapElementHeight;

        return $this;
    }

    /**
     * Get mapElementHeight
     *
     * @return integer
     */
    public function getMapElementHeight()
    {
        return $this->mapElementHeight;
    }

    /**
     * Set mapElementDisplayStyle
     *
     * @param string $mapElementDisplayStyle
     *
     * @return FgCmsPageContentElement
     */
    public function setMapElementDisplayStyle($mapElementDisplayStyle)
    {
        $this->mapElementDisplayStyle = $mapElementDisplayStyle;

        return $this;
    }

    /**
     * Get mapElementDisplayStyle
     *
     * @return string
     */
    public function getMapElementDisplayStyle()
    {
        return $this->mapElementDisplayStyle;
    }

    /**
     * Set mapElementZoomValue
     *
     * @param integer $mapElementZoomValue
     *
     * @return FgCmsPageContentElement
     */
    public function setMapElementZoomValue($mapElementZoomValue)
    {
        $this->mapElementZoomValue = $mapElementZoomValue;

        return $this;
    }

    /**
     * Get mapElementZoomValue
     *
     * @return integer
     */
    public function getMapElementZoomValue()
    {
        return $this->mapElementZoomValue;
    }

    /**
     * Set iframeElementCode
     *
     * @param string $iframeElementCode
     *
     * @return FgCmsPageContentElement
     */
    public function setIframeElementCode($iframeElementCode)
    {
        $this->iframeElementCode = $iframeElementCode;

        return $this;
    }

    /**
     * Get iframeElementCode
     *
     * @return string
     */
    public function getIframeElementCode()
    {
        return $this->iframeElementCode;
    }

    /**
     * Set iframeElementUrl
     *
     * @param string $iframeElementUrl
     *
     * @return FgCmsPageContentElement
     */
    public function setIframeElementUrl($iframeElementUrl)
    {
        $this->iframeElementUrl = $iframeElementUrl;

        return $this;
    }

    /**
     * Get iframeElementUrl
     *
     * @return string
     */
    public function getIframeElementUrl()
    {
        return $this->iframeElementUrl;
    }

    /**
     * Set iframeElementHeight
     *
     * @param integer $iframeElementHeight
     *
     * @return FgCmsPageContentElement
     */
    public function setIframeElementHeight($iframeElementHeight)
    {
        $this->iframeElementHeight = $iframeElementHeight;

        return $this;
    }

    /**
     * Get iframeElementHeight
     *
     * @return integer
     */
    public function getIframeElementHeight()
    {
        return $this->iframeElementHeight;
    }

    /**
     * Set isAllCategory
     *
     * @param boolean $isAllCategory
     *
     * @return FgCmsPageContentElement
     */
    public function setIsAllCategory($isAllCategory)
    {
        $this->isAllCategory = $isAllCategory;

        return $this;
    }

    /**
     * Get isAllCategory
     *
     * @return boolean
     */
    public function getIsAllCategory()
    {
        return $this->isAllCategory;
    }

    /**
     * Set isAllArea
     *
     * @param boolean $isAllArea
     *
     * @return FgCmsPageContentElement
     */
    public function setIsAllArea($isAllArea)
    {
        $this->isAllArea = $isAllArea;

        return $this;
    }

    /**
     * Get isAllArea
     *
     * @return boolean
     */
    public function getIsAllArea()
    {
        return $this->isAllArea;
    }

    /**
     * Set sharedClub
     *
     * @param string $sharedClub
     *
     * @return FgCmsPageContentElement
     */
    public function setSharedClub($sharedClub)
    {
        $this->sharedClub = $sharedClub;

        return $this;
    }

    /**
     * Get sharedClub
     *
     * @return string
     */
    public function getSharedClub()
    {
        return $this->sharedClub;
    }

    /**
     * Set sponsorAdDisplayType
     *
     * @param string $sponsorAdDisplayType
     *
     * @return FgCmsPageContentElement
     */
    public function setSponsorAdDisplayType($sponsorAdDisplayType)
    {
        $this->sponsorAdDisplayType = $sponsorAdDisplayType;

        return $this;
    }

    /**
     * Get sponsorAdDisplayType
     *
     * @return string
     */
    public function getSponsorAdDisplayType()
    {
        return $this->sponsorAdDisplayType;
    }

    /**
     * Set sponsorAdDisplayTime
     *
     * @param integer $sponsorAdDisplayTime
     *
     * @return FgCmsPageContentElement
     */
    public function setSponsorAdDisplayTime($sponsorAdDisplayTime)
    {
        $this->sponsorAdDisplayTime = $sponsorAdDisplayTime;

        return $this;
    }

    /**
     * Get sponsorAdDisplayTime
     *
     * @return integer
     */
    public function getSponsorAdDisplayTime()
    {
        return $this->sponsorAdDisplayTime;
    }

    /**
     * Set sponsorAdMaxWidth
     *
     * @param string $sponsorAdMaxWidth
     *
     * @return FgCmsPageContentElement
     */
    public function setSponsorAdMaxWidth($sponsorAdMaxWidth)
    {
        $this->sponsorAdMaxWidth = $sponsorAdMaxWidth;

        return $this;
    }

    /**
     * Get sponsorAdMaxWidth
     *
     * @return string
     */
    public function getSponsorAdMaxWidth()
    {
        return $this->sponsorAdMaxWidth;
    }

    /**
     * Set twitterDefaultAccount
     *
     * @param string $twitterDefaultAccount
     *
     * @return FgCmsPageContentElement
     */
    public function setTwitterDefaultAccount($twitterDefaultAccount)
    {
        $this->twitterDefaultAccount = $twitterDefaultAccount;

        return $this;
    }

    /**
     * Get twitterDefaultAccount
     *
     * @return string
     */
    public function getTwitterDefaultAccount()
    {
        return $this->twitterDefaultAccount;
    }

    /**
     * Set twitterContentHeight
     *
     * @param integer $twitterContentHeight
     *
     * @return FgCmsPageContentElement
     */
    public function setTwitterContentHeight($twitterContentHeight)
    {
        $this->twitterContentHeight = $twitterContentHeight;

        return $this;
    }

    /**
     * Get twitterContentHeight
     *
     * @return integer
     */
    public function getTwitterContentHeight()
    {
        return $this->twitterContentHeight;
    }

    /**
     * Set articleDisplayType
     *
     * @param string $articleDisplayType
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleDisplayType($articleDisplayType)
    {
        $this->articleDisplayType = $articleDisplayType;

        return $this;
    }

    /**
     * Get articleDisplayType
     *
     * @return string
     */
    public function getArticleDisplayType()
    {
        return $this->articleDisplayType;
    }

    /**
     * Set articlePerRow
     *
     * @param integer $articlePerRow
     *
     * @return FgCmsPageContentElement
     */
    public function setArticlePerRow($articlePerRow)
    {
        $this->articlePerRow = $articlePerRow;

        return $this;
    }

    /**
     * Get articlePerRow
     *
     * @return integer
     */
    public function getArticlePerRow()
    {
        return $this->articlePerRow;
    }

    /**
     * Set articleCount
     *
     * @param integer $articleCount
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleCount($articleCount)
    {
        $this->articleCount = $articleCount;

        return $this;
    }

    /**
     * Get articleCount
     *
     * @return integer
     */
    public function getArticleCount()
    {
        return $this->articleCount;
    }

    /**
     * Set articleSliderNavigation
     *
     * @param string $articleSliderNavigation
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleSliderNavigation($articleSliderNavigation)
    {
        $this->articleSliderNavigation = $articleSliderNavigation;

        return $this;
    }

    /**
     * Get articleSliderNavigation
     *
     * @return string
     */
    public function getArticleSliderNavigation()
    {
        return $this->articleSliderNavigation;
    }

    /**
     * Set articleShowThumbImg
     *
     * @param boolean $articleShowThumbImg
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleShowThumbImg($articleShowThumbImg)
    {
        $this->articleShowThumbImg = $articleShowThumbImg;

        return $this;
    }

    /**
     * Get articleShowThumbImg
     *
     * @return boolean
     */
    public function getArticleShowThumbImg()
    {
        return $this->articleShowThumbImg;
    }

    /**
     * Set articleShowDate
     *
     * @param boolean $articleShowDate
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleShowDate($articleShowDate)
    {
        $this->articleShowDate = $articleShowDate;

        return $this;
    }

    /**
     * Get articleShowDate
     *
     * @return boolean
     */
    public function getArticleShowDate()
    {
        return $this->articleShowDate;
    }

    /**
     * Set articleShowCategory
     *
     * @param boolean $articleShowCategory
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleShowCategory($articleShowCategory)
    {
        $this->articleShowCategory = $articleShowCategory;

        return $this;
    }

    /**
     * Get articleShowCategory
     *
     * @return boolean
     */
    public function getArticleShowCategory()
    {
        return $this->articleShowCategory;
    }

    /**
     * Set articleShowArea
     *
     * @param boolean $articleShowArea
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleShowArea($articleShowArea)
    {
        $this->articleShowArea = $articleShowArea;

        return $this;
    }

    /**
     * Get articleShowArea
     *
     * @return boolean
     */
    public function getArticleShowArea()
    {
        return $this->articleShowArea;
    }

    /**
     * Set articleRowsCount
     *
     * @param integer $articleRowsCount
     *
     * @return FgCmsPageContentElement
     */
    public function setArticleRowsCount($articleRowsCount)
    {
        $this->articleRowsCount = $articleRowsCount;

        return $this;
    }

    /**
     * Get articleRowsCount
     *
     * @return integer
     */
    public function getArticleRowsCount()
    {
        return $this->articleRowsCount;
    }

    /**
     * Set pageContentType
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContentType $pageContentType
     *
     * @return FgCmsPageContentElement
     */
    public function setPageContentType(\Common\UtilityBundle\Entity\FgCmsPageContentType $pageContentType = null)
    {
        $this->pageContentType = $pageContentType;

        return $this;
    }

    /**
     * Get pageContentType
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContentType
     */
    public function getPageContentType()
    {
        return $this->pageContentType;
    }

    /**
     * Set box
     *
     * @param \Common\UtilityBundle\Entity\FgCmsPageContainerBox $box
     *
     * @return FgCmsPageContentElement
     */
    public function setBox(\Common\UtilityBundle\Entity\FgCmsPageContainerBox $box = null)
    {
        $this->box = $box;

        return $this;
    }

    /**
     * Get box
     *
     * @return \Common\UtilityBundle\Entity\FgCmsPageContainerBox
     */
    public function getBox()
    {
        return $this->box;
    }

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     *
     * @return FgCmsPageContentElement
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
     * Set areaClub
     *
     * @param \Common\UtilityBundle\Entity\FgClub $areaClub
     *
     * @return FgCmsPageContentElement
     */
    public function setAreaClub(\Common\UtilityBundle\Entity\FgClub $areaClub = null)
    {
        $this->areaClub = $areaClub;

        return $this;
    }

    /**
     * Get areaClub
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getAreaClub()
    {
        return $this->areaClub;
    }

    /**
     * Set sponsorAdArea
     *
     * @param \Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea
     *
     * @return FgCmsPageContentElement
     */
    public function setSponsorAdArea(\Common\UtilityBundle\Entity\FgSmAdArea $sponsorAdArea = null)
    {
        $this->sponsorAdArea = $sponsorAdArea;

        return $this;
    }

    /**
     * Get sponsorAdArea
     *
     * @return \Common\UtilityBundle\Entity\FgSmAdArea
     */
    public function getSponsorAdArea()
    {
        return $this->sponsorAdArea;
    }

    /**
     * Set form
     *
     * @param \Common\UtilityBundle\Entity\FgCmsForms $form
     *
     * @return FgCmsPageContentElement
     */
    public function setForm(\Common\UtilityBundle\Entity\FgCmsForms $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get form
     *
     * @return \Common\UtilityBundle\Entity\FgCmsForms
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * Set table
     *
     * @param \Common\UtilityBundle\Entity\FgCmsContactTable $table
     *
     * @return FgCmsPageContentElement
     */
    public function setTable(\Common\UtilityBundle\Entity\FgCmsContactTable $table = null)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get table
     *
     * @return \Common\UtilityBundle\Entity\FgCmsContactTable
     */
    public function getTable()
    {
        return $this->table;
    }
}

