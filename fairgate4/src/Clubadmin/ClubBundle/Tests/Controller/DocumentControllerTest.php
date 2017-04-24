<?php

namespace Clubadmin\ClubBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentControllerTest extends WebTestCase
{
    public function testListDocuments()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/club/documents/1/8554');
        $html = $crawler->html();
        // echo $html;
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("FC Sion II-Sub FED CLUB")')->count()
        );        
    }
    
    public function testClubDocumentListingAjax()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/club/documents/1/8554/ajax');  
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        //echo "<pre>"; print_r($response['iTotalRecords']); echo "<pre>";
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('iTotalRecords', $response);
        $this->assertGreaterThan(0,$response['iTotalRecords']);
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
