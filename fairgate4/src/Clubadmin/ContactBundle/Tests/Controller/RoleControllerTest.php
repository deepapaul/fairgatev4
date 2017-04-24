<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * function to test role controller
 */
class RoleControllerTest extends WebTestCase
{
    /**
     * function to check the edit category
     */
    public function testEditcategory()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/Role/Editcategory');
        $this->assertTrue($crawler->filter('html:contains("breadcrumb")')->count() > 0);
    }
    /**
     * function to test the update category
     */
    public function testUpdatecategory()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/Role/Updatecategory');
        $this->assertEquals(1, $crawler->filter('.fairgatedirty')->count());
    }
    /**
     * function to test the category listing
     */
    public function testCategorysettings()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/Role/Categorysettings');
        $this->assertTrue($crawler->filter('a:contains("prev")')->count() > 0);
        $this->assertTrue($crawler->filter('a:contains("next")')->count() > 0);
    }
    /**
     * function to test the role functions
     */
    public function testRolefunctiondata()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/Role/Rolefunctiondata');
        $this->assertGreaterThan(0, $crawler->filter('input:text')->count());
    }
    /**
     * function to the save functionlity of role
     */
    public function testSaverolefunction()
    {
        $client = static::createClient();
        $crawler = $client->request('POST', '/Role/Saverolefunction');
        $this->assertEquals(1, $crawler->filter('.fairgatedirty')->count());
    }
}
