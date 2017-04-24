<?php

namespace Clubadmin\ClubBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Common\UtilityBundle\Form\FgClubDataCategory;

class DataControllerTest extends WebTestCase
{
    public function testClubDataEdit()
    {
        $client = static::createClient();
        $this->logIn(); 
        //$crawler = $client->request('GET', '/federation/backend/settings/club/0/8514');
        $crawler = $client->request('GET', '/federation/backend/club/data/7/8516');
        //echo $html = $crawler->html();      
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Federation")')->count()
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

}
