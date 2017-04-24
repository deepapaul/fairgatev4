<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ContactControllerTest extends WebTestCase {

    public function testLogin() {
        $client = static::createClient(); //array(), array(  'HTTP_HOST'       => 'localhost:8040',     ));
        $crawler = $client->request('GET', 'club111/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        // submit the login form
        $crawl = $client->submit($form);
//      $html = $crawl->html(); 
//      echo $html;
    }

    public function testPageLoad() {
              
        
        $client = static::createClient();

        $crawler = $client->request('GET', '/club111/backend/contact');
        $html = $crawler->html();
        echo $html;
        $this->assertTrue($crawler->filter('html:contains("Active Contacts")')->count() > 0);
       
       
    }
 public function pageUpdate() {
//        $client = static::createClient(array(), array('HTTP_HOST' => 'localhost:8040',));
//        $crawler = $client->request('POST', 'club111/backend/contact-fields/update', array('attributes' => '{"3":{"fields":{"72222":{"title":{"de":"test111"}}}}}')
//        );
//        echo $client->getRequest()->getUri();
//        $html = $crawler->html();
//        echo $html;
        // Assert that the "Content-Type" header is "application/json"
//        $this->assertTrue(
//            $client->getResponse()->headers->contains(
//                'Content-Type',
//                'application/json'
//            )
//        );
        //Assert that the response content success.
//        $this->assertRegExp('/SUCCESS/', $client->getResponse()->getContent());
//    }
    }


}
