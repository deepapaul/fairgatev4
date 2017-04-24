<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * function to test the initial page of ocntact
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * function to test the index page of contact
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/hello/Fabien');
        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);
    }
}
