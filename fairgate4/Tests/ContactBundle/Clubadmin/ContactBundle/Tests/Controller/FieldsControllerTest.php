<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FieldsControllerTest extends WebTestCase {

    public function testIndex() {
        $client = static::createClient();//array(), array(  'HTTP_HOST'       => 'localhost:8040',     ));
        $crawler = $client->request('GET', 'club111/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        // submit the login form
        $crawl = $client->submit($form);
        $crawler = $client->request('GET', 'contact/fields');
//        //check whether the page contains the title
        $this->assertTrue($crawler->filter('html:contains(" Contact fields ")')->count() > 0);
        //check whether the first system category is sortable
        $this->assertEquals('row ', $crawler->filter('.contact_area .row[data-catsortorder=1]')->attr('class'));
        //check whether multi selecter id disabled for vorname
        $this->assertEquals($crawler->filter('#2 select.selectpicker')->attr('disabled'), 'disabled');
        //check whether the address tooltip is working
        $this->assertEquals($crawler->filter('#2 i.fa-envelope-o')->attr('data-html'), 'true');
    }

    public function testUpdate() {
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
        // Assert that the response content success.
//        $this->assertRegExp('/SUCCESS/', $client->getResponse()->getContent());
//    }
    }

}
