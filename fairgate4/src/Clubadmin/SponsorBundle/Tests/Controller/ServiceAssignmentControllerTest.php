<?php

namespace Clubadmin\SponsorBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ServiceAssignmentControllerTest extends WebTestCase
{
    public function testStopServicePopup()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('actionType' => 'deleteserviceofsponsor', 'bookedIds' => "1021", 'pageType' => 'servicelist', 'selActionType' => 'selected');
        $crawler = $client->request('POST', '/india/backend/sponsor/stopservice/popup', $postData) ;      
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Delete assignment")')->count()
        );  
    }
    
    public function logIn()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/india/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        $crawl = $client->submit($form);         
        $html = $crawl->html();
    }
    
    public function testStopServiceAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('actionType' => 'deleteserviceofsponsor', 'selectedId' => "1107", 'pageType' => 'servicelist');
        $crawler = $client->request('POST', '/india/backend/sponsor/stopservice', $postData);          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);        
    }    
      
    
}
