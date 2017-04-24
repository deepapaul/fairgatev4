<?php

/**
 * Overview Controller Test.
 *
 * This controller is used to test Overview controller.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\ProfileBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test controller for overview
 */
class OverviewControllerTest extends WebTestCase {
    
    /**
     * Method to login
     */
    public function logIn() {
        $client = static::createClient();
        $crawler = $client->request('GET', '/india/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        $crawl = $client->submit($form);         
        $html = $crawl->html();
    }
    
    /**
     * Method to test forum listing in overview page
     */
    public function testForumlistingOverviewAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('POST', '/india/internal/forumlisting') ;
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('forums', $response); 
    }
}