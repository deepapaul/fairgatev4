<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookmarkControllerTest extends WebTestCase
{
    public function testMembershiplist()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/MembershipListController/membershiplist');

        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
    
    
}
