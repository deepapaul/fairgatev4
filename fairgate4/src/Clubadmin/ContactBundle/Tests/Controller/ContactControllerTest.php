<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * function to test the contact pages
 */
class ContactControllerTest extends WebTestCase
{
    /**
     * function to check the login
     */
    public function testLogin()
    {
        $client = static::createClient(); //array(), array(  'HTTP_HOST'       => 'localhost:8040',     ));
        $crawler = $client->request('GET', 'club111/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        // submit the login form
        $crawl = $client->submit($form);
    }
    /**
     * function to check the page load
     */
    public function testPageLoad()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/club111/backend/contact');
        $html = $crawler->html();
        echo $html;
        $this->assertTrue($crawler->filter('html:contains("Active Contacts")')->count() > 0);
    }
}
