<?php

/*
 * This is overiden from FOSUserBundle package.
 *
 */
namespace Internal\UserBundle\Security;

use FOS\UserBundle\Doctrine\UserManager as BaseUserManager1;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use Doctrine\Common\Persistence\ObjectManager;
/*
 * This overrides FOSUserBundle/UserManager class
 * and added custome functions
 *
 */
class UserManager extends BaseUserManager1 {
    /**
     * Password Updater.
     *
     * @var Object
     */
    private $passwordUpdater;
    /*
     * CanonicalFields Updater
     * @var Object
     */
    private $canonicalFieldsUpdater;

    /*
     *
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class)
    {
        file_put_contents('login.txt', 'Internal\UserBundle\Security::__construct', FILE_APPEND);
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);

        $this->passwordUpdater = $passwordUpdater;
        $this->canonicalFieldsUpdater = $canonicalFieldsUpdater;
    }

    /**
     * Function to find user by email and club
     *
     * @param string $club
     * @param string $email
     * @return User Object
     */
    public function findUserByEmailClub($club, $email) {
        file_put_contents('login.txt', 'Internal\UserBundle\Security::findUserByEmailClub', FILE_APPEND);
        return $this->repository->findOneBy(array('club' => $club, 'emailCanonical' => $this->canonicalFieldsUpdater->canonicalizeEmail($email)));
    }
    /**
     *
     * @param string $club
     * @param string $username
     * @return User Object
     */
    public function findUserByClub($club, $username)
    {
        file_put_contents('login.txt', 'Internal\UserBundle\Security::findUserByClub', FILE_APPEND);
        return $this->repository->findOneBy(array('club' => $club,'usernameCanonical' => $this->canonicalFieldsUpdater->canonicalizeUsername($username)));
    }

    /**
     * Function to find user by confirmation token and club
     *
     * @param string $token
     * @param string $club
     * @return User Object
     */
    public function findUserByConfirmationTokenAndClub($token, $club)
    {
        file_put_contents('login.txt', 'Internal\UserBundle\Security::findUserByConfirmationTokenAndClub', FILE_APPEND);
        return $this->repository->findOneBy(array('confirmationToken' => $token,'club' => $club));
    }

}
