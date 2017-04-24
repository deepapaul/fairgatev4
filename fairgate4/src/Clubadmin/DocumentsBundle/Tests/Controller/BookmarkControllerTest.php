<?php

namespace Clubadmin\DocumentsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * To test bookmark controller
 *
 */
class BookmarkControllerTest extends WebTestCase 
{

    /**
     * Function to create a authorize client
     *
     */
    protected function createAuthorizedClient() 
    {
        $client = static::createClient();
        $container = $client->getContainer();

        $session = $container->get('session');
        /** @var $userManager \FOS\UserBundle\Doctrine\UserManager */
        $userManager = $container->get('fos_user.user_manager');
        /** @var $loginManager \FOS\UserBundle\Security\LoginManager */
        $loginManager = $container->get('fos_user.security.login_manager');
        $firewallName = $container->getParameter('fos_user.firewall_name');

        $user = $userManager->findUserBy(array('username' => 'superadmin'));
        $loginManager->loginUser($firewallName, $user);

        // save the login token into the session
        $container->get('session')->set('_security_' . $firewallName, serialize($container->get('security.token_storage')->getToken()));
        $container->get('session')->save();
        // $client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));

        return $client;
    }

    /**
     * Constructor function to invoke client
     *
     */
    public function __construct() 
    {
        $this->client = $this->createAuthorizedClient();
    }

    /**
     * Function to test bookmark functionality
     *
     */
    public function testbookmark() 
    {
        /* Login is handled here */
        $crawler = $this->client->request('GET', '/federation/backend/signin');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        /* Ends here */

        /* Bookmark listing page is tested */
        $crawler = $this->client->request('GET', 'federation/backend/document/bookmark/team');
        $this->assertEquals(1, $crawler->filter('html:contains(".fairgatedirty")')->count());

        /* Bookmark entry is debookmarked and tested */
        $crawler = $this->client->request('POST', 'federation/backend/document/bookmarkupdate', array('bookmarkArr' => '{"553":{"is_deleted":1}}'));
        $this->assertRegExp('/SUCCESS/', $this->client->getResponse()->getContent());
        /* Ends here */

        /* Bookmark entries are sorted and tested*/
        $crawler = $this->client->request('POST', 'federation/backend/document/bookmarkupdate', array('bookmarkArr' => '{"550":{"sort_order":"3"},"553":{"sort_order":"4"},"554":{"sort_order":"2"}}'));
        $this->assertRegExp('/SUCCESS/', $this->client->getResponse()->getContent());
        /* Ends here */
    }

}

?>
