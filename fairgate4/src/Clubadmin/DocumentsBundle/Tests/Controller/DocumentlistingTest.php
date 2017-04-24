<?php

namespace Clubadmin\DocumentsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


/**
 * To test bookmark controller
 *
 */
class DocumentlistingrTest extends WebTestCase {
  
    public $client;
    
    /**
    * Bookmark controller constructor
    */
    public function __construct()
    {
       $this->client = static::createClient();
    }
    
    /**
    * Function to test the bookmark list
    */
    public function testbookmarklist()
    {
       $crawler = $this->client->request('GET', '/document/club');
       $this->assertEquals(0, $crawler->filter('.DTFC_LeftWrapper')->count());
    }
    
  
    
}

?>
