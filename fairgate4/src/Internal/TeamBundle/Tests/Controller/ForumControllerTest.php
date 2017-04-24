<?php

/**
 * Forum Controller Test.
 *
 * This controller is used to test forum controller.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace Internal\TeamBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test controller for forum
 */
class ForumControllerTest extends WebTestCase {
    
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
    
    public function testSaveTopicReplyAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('role' => '5671', 'grpType' => "team", 'topicId' => '87', "postArr" => json_encode(array("forum-post-text" => "SAMPLE TEST REPLY ")));
        $crawler = $client->request('POST', '/india/internal/forum/saveforumtopicreply', $postData) ;
        $json = ($client->getResponse()->getContent());
        //$this->assertInternalType('json', $json);
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response); 
    }
}