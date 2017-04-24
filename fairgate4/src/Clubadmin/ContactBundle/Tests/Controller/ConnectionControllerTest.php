<?php

namespace Clubadmin\ContactBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
/**
 * To test the COnnection controller
 */
class ConnectionControllerTest extends WebTestCase
{
    /**
     * function to test the login of users
     */
    public function testLogin()
    {
        $client = static::createClient(); //array(), array(  'HTTP_HOST'       => 'localhost:8040',     ));
        $crawler = $client->request('GET', 'club111/backend/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        // submit the login form
        $crawl = $client->submit($form);
    }
    /**
     * function to test the contact connection
     */
    public function testConnection()
    {

        $client = static::createClient();
        $crawler = $client->request('GET', '/club111/backend/connection/435595');
        $this->assertTrue($crawler->filter('html:contains("Household")')->count() > 0);
    }
    /**
     * function to check a connection is correctly added to the contact
     */
    public function testUpdateConnectionsAction()
    {
        $client = static::createClient();
        $this->testLogin(); 
        $crawler = $client->request('POST', '/club111/backend/contact/updateconnections');
        $this->assertEquals(1, $crawler->filter('.fairgatedirty')->count());
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('json', $response);
        $this->assertArrayHasKey('status', $response); 
        $this->assertArrayHasKey('flash', $response); 
    }
    /**
     * function to test connecting two contact
     */
    public function testConnectcontactsAction()
    {
        $client = static::createClient();
        $this->testLogin(); 
        $postData  = array('contactData' => '549621%#-#%0%#-#%T%C3%BCrkylmaz,%20Kubilay%#-#%651*#*##*#*544787%#-#%1%#-#%test77%20priyesh66%#-#%651');
        $crawler = $client->request('POST', '/club111/backend/contact/connectcontacts', $postData) ;
        $json = ($client->getResponse()->getContent());
        //$this->assertInternalType('json', $json);
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('cont1DataArr', $response); 
        $this->assertArrayHasKey('cont2DataArr', $response); 
        $this->assertArrayHasKey('type', $response); 
        $this->assertArrayHasKey('relationsArray', $response); 
        $this->assertArrayHasKey('householdContacts', $response); 
        $this->assertArrayHasKey('disableFields', $response); 
    }
}
