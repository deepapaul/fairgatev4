<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Internal\UserBundle\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;
use FOS\UserBundle\Model\User;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Propel\User as PropelUser;
use Common\UtilityBundle\Repository\Pdo\ContactPdo;
use FOS\UserBundle\Security\UserProvider as BaseUserProvider;

class UserProvider extends BaseUserProvider implements UserProviderInterface
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername($username)
    {
        global $kernel;
        if ('AppCache' == get_class($kernel)) {
            $kernel = $kernel->getKernel();
        }

        $container = $kernel->getContainer();
        $club = $container->get('club');
        $session = $container->get('session');
        $user = $this->findUserUsername($username);
        $ContactPdo = new ContactPdo($container);
        if (null != $user) {
            //checking if superadmin
            $superAdminFlag = $user->getIsSuperAdmin();
            if ($superAdminFlag != 1) {
                $user = $this->findUserClub($username, $club->get('id'));                
                if (!$user) {
                    $user = $this->findUserClub($username, $club->get('federation_id'));
                    $fedAdminFlag = ($user) ? $ContactPdo->checkFedAdminContact($user->getId(), $club->get('id'), $club->get('federation_id')) : false;
                    if (!$fedAdminFlag || !$user) {
                        throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
                    }
                } else {
                    $fedAdminFlag = $ContactPdo->checkFedAdminContact($user->getId(), $club->get('id'), $club->get('federation_id'));                 
                }
                
                //If the club is in 'Registered' status only superadmin and fedadmin can login
                //If the club is in 'Confirmed' status only superadmin/fedadmin/maincontact can login
                //If the club is in 'Active' status every user can login
                $adminEntityManager = $container->get('fg.admin.connection')->getAdminManager();
                $clubObj = $adminEntityManager->getRepository('AdminUtilityBundle:FgClub')->find($club->get('id'));
                $clubStatus = $clubObj->getStatus();
                $clubsMainContact = ($clubObj->getFairgateSolutionContact()) ? $clubObj->getFairgateSolutionContact()->getId() : null;
                $loggedContact = $user->getContact()->getId();
                if( (!$fedAdminFlag && $clubStatus != 'Active' && ($clubsMainContact != $loggedContact)) || 
                    (!$fedAdminFlag && $clubStatus == 'Registered') ) {
                    throw new \Exception('HAS_NO_PERMISSION'); 
                }

                //Block user if Intranet access is 0
                $intranetAccess = $user->getContact()->getIntranetAccess();

                $lastLoginObj = $user->getContact()->getLastLogin();
                $lastLogin = ($lastLoginObj instanceof \DateTime) ? $lastLoginObj->format('Y-m-d H:i:s') : null;
                $applicationArea = $club->get('applicationArea');
                //checking whether fisrt login
                //checking Intranet access of user. He can login only if it is '1'
                if ($intranetAccess != 1 && $applicationArea === 'internal') {
                    throw new \Exception('HAS_NO_INTRANET_ACCESS');
                }
                if ($lastLogin === null && ($clubsMainContact != $loggedContact)) { //if the user is not activated his account (first time login) (except for club's main contact)
                    throw new \Exception('LOGIN_ACCOUNT_NOT_ACTIVATED');
                }
            }
        } else {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(SecurityUserInterface $user)
    {
        if (!$user instanceof User && !$user instanceof PropelUser) {
            throw new UnsupportedUserException(sprintf('Expected an instance of FOS\UserBundle\Model\User, but got "%s".', get_class($user)));
        }

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of %s, but got "%s".', $this->userManager->getClass(), get_class($user)));
        }

        if (null === $reloadedUser = $this->userManager->findUserBy(array('id' => $user->getId()))) {
            throw new UsernameNotFoundException(sprintf('User with ID "%d" could not be reloaded.', $user->getId()));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass($class)
    {
        $userClass = $this->userManager->getClass();

        return $userClass === $class || is_subclass_of($class, $userClass);
    }

    /**
     * Finds a user by username.
     *
     * This method is meant to be an extension point for child classes.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    protected function findUserUsername($username)
    {
        return $this->userManager->findUserByUsername($username);
    }

    /**
     * Finds a user by username.
     *
     * This method is meant to be an extension point for child classes.
     *
     * @param string $username
     *
     * @return UserInterface|null
     */
    protected function findUserClub($username, $club)
    {
        return $this->userManager->findUserByClub($club, $username);
    }
}
