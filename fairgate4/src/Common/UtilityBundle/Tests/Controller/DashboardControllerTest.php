<?php

namespace Common\UtilityBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChartControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/');
        $html = $crawler->html();
        //echo $html;
        $this->assertGreaterThan(
            0, $crawler->filter('html:contains("Willkommen, Fairgate AG")')->count()
        );    
    }
    
    public function testGetNextBirthdays()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/dashboard/nextbirthdays');  
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        //echo "<pre>"; print_r($response); echo "</pre>";
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertGreaterThan(0,count($response));
    }
    
    public function testGetSimplemailStackedChart()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/dashboard/simplemail');  
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        //echo "<pre>"; print_r($response); echo "</pre>";
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertGreaterThan(0,count($response));
    }
    
    public function testGetNewsletterStackedChart()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/dashboard/newsletter');  
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        //echo "<pre>"; print_r($response); echo "</pre>";
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertGreaterThan(0,count($response));
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

}
