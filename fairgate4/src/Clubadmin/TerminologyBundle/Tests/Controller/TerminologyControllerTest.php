<?php

namespace Clubadmin\TerminologyBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * terminology controller test case
 */
class TerminologyControllerTest extends WebTestCase
{
      public $client;
/**
 * terminology controller constructor
 */
      public function __construct()
      {
        $this->client = static::createClient();
      }
/**
 * Login test
 */
      public function testLogin()
      {
        $crawler = $this->client->request('GET', 'club111/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        // submit the login form
        $crawl = $this->client->submit($form);
        $html = $crawl->html();
        echo $html;
      }
/**
 * index page test
 */
     public function testIndex()
     {
        $crawler = $this->client->request('GET', '/terminologymanipulate');
        $this->assertTrue($crawler->filter('html:contains("Terminology")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Club")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("Team")')->count() > 0);

     }
/**
 * update test
 */
    public function testUpdate()
    {
      $crawler= $this->client->request('POST', '/update', array('attributes' => '{"2":{"Club":{"de":"My Club"}}}'));
      $this->assertRegExp('/SUCCESS/', $this->client->getResponse()->getContent());

      $crawler= $this->client->request('POST', '/update', array('attributes' => '{"3":{"Team":{"de":"My team"}}}'));
      $this->assertRegExp('/SUCCESS/', $this->client->getResponse()->getContent());
    }
}
