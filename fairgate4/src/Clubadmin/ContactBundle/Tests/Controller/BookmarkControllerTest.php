<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * Bookmark test case
 */
class BookmarkControllerTest extends WebTestCase
{
    /**
     * function to check the bookmark listing
     */
    public function testbookmarkList()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/bookmark/bookmarkList');
        $this->assertTrue($crawler->filter('html:contains("breadcrumb")')->count() > 0);
    }
}
