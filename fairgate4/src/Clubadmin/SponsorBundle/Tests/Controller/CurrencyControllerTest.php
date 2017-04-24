<?php

namespace Clubadmin\SponsorBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * To test bookmark controller
 *
 */
class CurrencyControllerTest extends WebTestCase 
{

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
    
    public function testCurrencySave(){
        
        $client = static::createClient();
        $this->logIn();
        $postData  = array('settingsId' => 32, 'currency' => "AFA", 'layout' => 'false');
        $crawler = $client->request('POST', '/india/backend/sponsor/currencysave',$postData ); 
        
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);        
    }
}

?>
