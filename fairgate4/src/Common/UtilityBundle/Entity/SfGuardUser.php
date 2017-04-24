<?php

// src/Acme/UserBundle/Entity/User.php

namespace Common\UtilityBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use Symfony\Component\Security\Core\Role\RoleInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * SfGuardUser
 */
class SfGuardUser extends BaseUser {

    protected $id;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $lastName;

    /**
     * @var string
     */
    private $algorithm;

    /**
     * @var boolean
     */
    private $isActive;

    /**
     * @var boolean
     */
    private $isSuperAdmin;

    /**
     * @var boolean
     */
    private $hasFullPermission;

    /**
     * @var \DateTime
     */
    private $createdAt;

    /**
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @var boolean
     */
    private $isSecurityAdmin;

    /**
     * @var \DateTime
     */
    private $lastReminder;

    /**
     * @var \Common\UtilityBundle\Entity\FgCmContact
     */
    private $contact;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $group;

    /**
     * @var integer
     */
    private $isReadonlyAdmin;
	
    /**
     * @var integer
     */
    private $isTeamAdmin;
	
    /**
     * @var integer
     */
    private $isTeamSectionAdmin;	
    
    /**
     * @var string
     */
    private $authCode;
    /**
     * Constructor
     */
    public function __construct() {
        $this->group = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return SfGuardUser
     */
    public function setFirstName($firstName) {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName() {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return SfGuardUser
     */
    public function setLastName($lastName) {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName() {
        return $this->lastName;
    }

    /**
     * Set algorithm
     *
     * @param string $algorithm
     * @return SfGuardUser
     */
    public function setAlgorithm($algorithm) {
        $this->algorithm = $algorithm;

        return $this;
    }

    /**
     * Get algorithm
     *
     * @return string
     */
    public function getAlgorithm() {
        return $this->algorithm;
    }

    /**
     * Set isActive
     *
     * @param boolean $isActive
     * @return SfGuardUser
     */
    public function setIsActive($isActive) {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive() {
        return $this->isActive;
    }

    /**
     * Set isSuperAdmin
     *
     * @param boolean $isSuperAdmin
     * @return SfGuardUser
     */
    public function setIsSuperAdmin($isSuperAdmin) {
        $this->isSuperAdmin = $isSuperAdmin;

        return $this;
    }

    /**
     * Get isSuperAdmin
     *
     * @return boolean
     */
    public function getIsSuperAdmin() {
        return $this->isSuperAdmin;
    }

    /**
     * Set hasFullPermission
     *
     * @param boolean $hasFullPermission
     * @return SfGuardUser
     */
    public function setHasFullPermission($hasFullPermission) {
        $this->hasFullPermission = $hasFullPermission;

        return $this;
    }

    /**
     * Get hasFullPermission
     *
     * @return boolean
     */
    public function getHasFullPermission() {
        return $this->hasFullPermission;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return SfGuardUser
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     * @return SfGuardUser
     */
    public function setUpdatedAt($updatedAt) {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * Set isSecurityAdmin
     *
     * @param boolean $isSecurityAdmin
     * @return SfGuardUser
     */
    public function setIsSecurityAdmin($isSecurityAdmin) {
        $this->isSecurityAdmin = $isSecurityAdmin;

        return $this;
    }

    /**
     * Get isSecurityAdmin
     *
     * @return boolean
     */
    public function getIsSecurityAdmin() {
        return $this->isSecurityAdmin;
    }

    /**
     * Set lastReminder
     *
     * @param \DateTime $lastReminder
     * @return SfGuardUser
     */
    public function setLastReminder($lastReminder) {
        $this->lastReminder = $lastReminder;

        return $this;
    }

    /**
     * Get lastReminder
     *
     * @return \DateTime
     */
    public function getLastReminder() {
        return $this->lastReminder;
    }

    /**
     * Set contact
     *
     * @param \Common\UtilityBundle\Entity\FgCmContact $contact
     * @return SfGuardUser
     */
    public function setContact(\Common\UtilityBundle\Entity\FgCmContact $contact = null) {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return \Common\UtilityBundle\Entity\FgCmContact
     */
    public function getContact() {
        return $this->contact;
    }

    /**
     * @ORM\ManyToMany(targetEntity="Common\UtilityBundle\Entity\SfGuardGroup")
     * @ORM\JoinTable(name="sf_guard_user_group",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    public function getGroupNames() {

    }

    /**
     * @var \Common\UtilityBundle\Entity\FgClub
     */
    private $club;

    /**
     * Set club
     *
     * @param \Common\UtilityBundle\Entity\FgClub $club
     * @return SfGuardUser
     */
    public function setClub(\Common\UtilityBundle\Entity\FgClub $club = null) {
        $this->club = $club;

        return $this;
    }

    /**
     * Get club
     *
     * @return \Common\UtilityBundle\Entity\FgClub
     */
    public function getClub() {
        return $this->club;
    }

    /**
     * Set isReadonlyAdmin
     *
     * @param boolean $isReadonlyAdmin
     * @return SfGuardUser
     */
    public function setIsReadonlyAdmin($isReadonlyAdmin) {
        $this->isReadonlyAdmin = $isReadonlyAdmin;

        return $this;
    }

    /**
     * Get isReadonlyAdmin
     *
     * @return boolean
     */
    public function getIsReadonlyAdmin() {
        return $this->isReadonlyAdmin;
    }
    /**
     * Set isTeamAdmin
     *
     * @param boolean $isTeamAdmin
     * @return SfGuardUser
     */
    public function setIsTeamAdmin($isTeamAdmin) {
        $this->isTeamAdmin = $isTeamAdmin;

        return $this;
    }

    /**
     * Get isTeamAdmin
     *
     * @return boolean
     */
    public function getIsTeamAdmin() {
        return $this->isTeamAdmin;
    }	
    /**
     * Set isTeamSectionAdmin
     *
     * @param boolean $isTeamSectionAdmin
     * @return SfGuardUser
     */
    public function setIsTeamSectionAdmin($isTeamSectionAdmin) {
        $this->isTeamSectionAdmin = $isTeamSectionAdmin;

        return $this;
    }

    /**
     * Get isTeamSectionAdmin
     *
     * @return boolean
     */
    public function getIsTeamSectionAdmin() {
        return $this->isTeamSectionAdmin;
    }	
    
    /**
     * Get authCode
     *
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * Set authCode
     *
     * @param string $authCode
     * @return SfGuardUser
     */
    public function setAuthCode($authCode)
    {
        $this->authCode = $authCode;
    }
}
