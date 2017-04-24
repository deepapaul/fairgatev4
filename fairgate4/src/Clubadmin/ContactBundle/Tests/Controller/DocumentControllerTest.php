<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DocumentControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/contact/documents/0/509787');
        $html = $crawler->html();
       // echo $html;
        $this->assertGreaterThan(
            0, $crawler->filter('html:contains("aarya zach")')->count()
        );    
    }
    
    public function testDocumentListingAjax()
    {
        $client = static::createClient();
        $this->logIn(); 
        $crawler = $client->request('GET', '/federation/backend/contact/documents/0/509787/ajax');  
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
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
