<?php

namespace Clubadmin\SponsorBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SponsorControllerTest extends WebTestCase
{
    public function testRemoveProspectsPopup()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/sponsor/removeProspectsPopup?contactids=543761');          
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Are you sure to remove the prospect")')->count()
        );  
    }
    
    public function logIn()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/federation/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Einloggen')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        $crawl = $client->submit($form);         
        $html = $crawl->html();
    }
    
    public function testAssignContactstoSponsors()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/swisstennis/backend/sponsor/assignContactstoSponsors?contactIds=507732');          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);
        $this->assertEquals('Prospects added successfully',$response['flash']);
        
    }
    
    public function testRemoveProspects()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/swisstennis/backend/sponsor/removeProspects?contactids=544819');          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);
        
    }        
    
}

