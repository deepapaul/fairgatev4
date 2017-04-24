<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RoleControllerTest extends WebTestCase
{
    public function testEditcategory()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/Role/Editcategory');

        $this->assertTrue($crawler->filter('html:contains("breadcrumb")')->count() > 0);
    }

    public function testUpdatecategory()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/Role/Updatecategory');

        $this->assertEquals(1, $crawler->filter('.fairgatedirty')->count());
    }

    public function testCategorysettings()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/Role/Categorysettings');

        $this->assertTrue($crawler->filter('a:contains("prev")')->count() > 0);
        $this->assertTrue($crawler->filter('a:contains("next")')->count() > 0);
    }

    public function testRolefunctiondata()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/Role/Rolefunctiondata');

        $this->assertGreaterThan(0, $crawler->filter('input:text')->count());
    }

    public function testSaverolefunction()
    {
        $client = static::createClient();

        $crawler = $client->request('POST', '/Role/Saverolefunction');

        $this->assertEquals(1, $crawler->filter('.fairgatedirty')->count());
    }
}
