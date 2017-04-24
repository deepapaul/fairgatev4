<?php

namespace Clubadmin\DocumentsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


/**
 * To test bookmark controller
 *
 */
class MemberlistingTest extends WebTestCase {
  
    public $client;
    
    /**
    * Bookmark controller constructor
    */
    public function __construct()
    {
       $this->client = $this->createAuthorizedClient();
    }
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
    * Function to test the bookmark list
    */
    public function testmemberlist()
    {
       $crawler = $this->client->request('GET', '/teamdetailoverview');
       $this->assertEquals(0, $crawler->filter('.DTFC_LeftWrapper')->count());
    }      
    
}

?>
