<?php

namespace Clubadmin\NotesBundle\Tests\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * default test controller
 */
class DefaultControllerTest extends WebTestCase
{

    protected $client;
/**
 * default controller constructor
 */
    public function __construct()
    {
        $this->client = static::createClient(); //array(), array(  'HTTP_HOST'       => 'localhost:8040',     ));
        $crawler = $this->client->request('GET', 'club111/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        // submit the login form
        $crawl = $this->client->submit($form);

    }
/**
 * contact note update
 */
    public function testcontactnoteupdate()
    {
        $crawler = $this->client->request('POST', 'contactnoteupdate/1/651', array('attributes' => '{"new_1405595136939":"test value"}'));
        $this->assertRegExp('/SUCCESS/', $this->client->getResponse()->getContent());

    }
/**
 * contact note
 */
    public function testcontactnote()
    {
        $crawler = $this->client->request('GET', 'contactnote/1');
        $this->assertTrue($crawler->filter('html:contains(" Fairgate A G ")')->count() > 0);
        $this->assertTrue($crawler->filter('html:contains("<div class="timeline-show-text pull-left pagshow">Showing 1 to  1  of 5 entries</div>")')->count() > 0);

    }

}
