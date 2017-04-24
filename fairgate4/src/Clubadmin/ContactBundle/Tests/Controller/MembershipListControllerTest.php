<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * function to check the mmeberlist
 */
class MembershiplistControllerTest extends WebTestCase
{
    /**
     * function to check the listing of membership list
     */
    public function testMembershiplist()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/MembershipListController/membershiplist');
        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
}
