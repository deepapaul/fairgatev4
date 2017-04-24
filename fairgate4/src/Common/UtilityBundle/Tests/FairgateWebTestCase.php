<?php

namespace Common\UtilityBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * This is a wraper class Syfony's WebTestCase extended for Pnn.
 *
 * @package Pnn/GeneralBundle/Test
 *
 * @author deepa.p <deepa@pitsolutions.com>
 * @version @version SVN: $Id$ 12.01.2016
 *
 * @concept Session handler using a PDO connection to read and write data
 */
class FairgateWebTestCase extends WebTestCase {

    /**
     *
     * @var object Mockclient object for testing
     */
    public $oClient = null;

    /**
     *
     * @var object Service Container
     */
    public $oContainer = null;

    /**
     * Set up function to initialize
     */
    public function setUp() {
        $this->oClient = static::createClient();
        $this->oContainer = $this->oClient->getContainer();
    }

    /**
     * Function to trigger login  - login as super admin - for testing secure pages
     *
     * @version SVN: $Id$ project listing 11.01.2016
     * #checked Deepa Paul 11.01.2016
     *
     */
    public function createAuthorizedClient() {
        /* Get service container */
        $this->oContainer = $this->oClient->getContainer();
        /* Get session object */
        $oSession = $this->oContainer->get('session');

        //$sFirewall = 'main';
        /* Get user object */
        $oUserManager = $this->oContainer->get('fos_user.user_manager');
        $oUser = $oUserManager->findUserBy(array('username' => 'superadmin'));

        //$oToken = new UsernamePasswordToken($oUser, 'superadmin', $sFirewall, array('ROLE_SUPER'));
        /* Trigger login for secure pages */
        $sFirewallName = $this->oContainer->getParameter('fos_user.firewall_name');
        $oLoginManager = $this->oContainer->get('fos_user.security.login_manager');
        $oLoginManager->loginUser($sFirewallName, $oUser);
        $this->oContainer->get('session')->set('_security_' . $sFirewallName, serialize($this->oContainer->get('security.token_storage')->getToken()));
        $this->oContainer->get('session')->save();

        $cookie = new Cookie($oSession->getName(), $oSession->getId());
        $this->oClient->getCookieJar()->set($cookie);
    }

}
