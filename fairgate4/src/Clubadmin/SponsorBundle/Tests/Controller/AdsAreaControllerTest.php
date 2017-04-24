<?php

namespace Clubadmin\SponsorBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdsAreaControllerTest extends WebTestCase
{
    public function testGetAddPreviews()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('adareas' => '1', 'services' => "367,368,365,354,355,361,359,360,380", 'width' => '120');
        $crawler = $client->request('POST', '/india/backend/sponsor/add/previews', $postData) ;           
        $this->assertGreaterThan(
            0,
            $crawler->filter('div.fg-ad-preview-120')->count()
        );
    }
    public function testGetAddPreviews2()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('adareas' => '1', 'services' => "367,368,365,354,355,361,359,360,380", 'width' => '150');
        $crawler = $client->request('POST', '/india/backend/sponsor/add/previews', $postData) ;           
        $this->assertGreaterThan(
            0,
            $crawler->filter('div.fg-ad-preview-150')->count()
        );
    }
    public function testGetAddPreviews3()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('adareas' => '1', 'services' => "367,368,365,354,355,361,359,360,380", 'width' => '200');
        $crawler = $client->request('POST', '/india/backend/sponsor/add/previews', $postData) ;           
        $this->assertGreaterThan(
            0,
            $crawler->filter('div.fg-ad-preview-200')->count()
        );
    }
    public function testGetAddPreviews4()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('adareas' => '1', 'services' => "367,368,365,354,355,361,359,360,380", 'width' => '500');
        $crawler = $client->request('POST', '/india/backend/sponsor/add/previews', $postData) ;           
        $this->assertGreaterThan(
            0,
            $crawler->filter('div.fg-ad-preview-500')->count()
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
    
    
}
