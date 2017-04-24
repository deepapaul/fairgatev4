<?php

/**
 * Forum Controller Test.
 *
 * This controller is used to test calendar controller.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\CalendarBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test controller for calendar
 */
class CalendarControllerTest extends WebTestCase {
    
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
     * Method to test calendar events
     */
    public function testSaveTopicReplyAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('startDate' => '2015-11-29', 'endDate' => "2016-01-03", 'range' => 'month', "filter" => '[{"id":"1","type":"CA"},{"id":"2","type":"CA"},{"id":"110","type":"CA"},{"id":"111","type":"CA"},{"id":"112","type":"CA"},{"id":"116","type":"CA"},{"id":"117","type":"CA"},{"id":"132","type":"CA"},{"id":"139","type":"CA"},{"id":"140","type":"CA"},{"id":"143","type":"CA"},{"id":"148","type":"CA"},{"id":"223","type":"CA"},{"id":"238","type":"CA"},{"id":"250","type":"CA"}]');
        $crawler = $client->request('POST', '/india/internal/calendar/getEvents', $postData) ;
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
    }
}