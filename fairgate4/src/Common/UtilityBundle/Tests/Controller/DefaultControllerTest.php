<?php
namespace Common\UtilityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * DefaultControllerTest
 *
 * This is th unit test class for DefaultControllerDefaultController
 * test methods are given below.
 */
class DefaultControllerTest extends WebTestCase
{
    /**
     * Test method for IndexAction
     *
     * @return boolean
     */
    public function testIndex()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/hello/Fabien');
        $this->assertTrue($crawler->filter('html:contains("Hello Fabien")')->count() > 0);

        return true;

    }
}