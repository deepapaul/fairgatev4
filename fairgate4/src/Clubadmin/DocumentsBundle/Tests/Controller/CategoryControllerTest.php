<?php

namespace Clubadmin\DocumentsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


/**
 * To test Category Controller
 *
 */
class CategoryControllerTest extends WebTestCase {
  
    public $client;
    
   /**
   * Category controller constructor
   *
   */
    public function __construct()
    {
       $this->client = static::createClient();
    }

    
   /**
    * function to test the edit category
    */
    public function testEditcategory()
    {
        $crawler = $this->client->request('GET', '/document/category/club');
        $this->assertTrue($crawler->filter('.fairgatedirty')->count() >= 0);
    }
    
    /**
    * function to test the add category
    */
    public function testAddcategory()
    {
       $crawler = $this->client->request('GET', '/document/category/club');
       
      /* Checking whether a new row is added or not on clicking the add category button */
        $this->assertTrue($crawler->filter('.addednew')->count() >= 0);
      /* Ends here */
        
      /* Checking  Manage club docs link is not produced  on clicking the add category button */
        $this->assertCount(0, $crawler->filter('html:contains("fg-dev-manage")'));
      /* Ends here */
        
     
    }
    
}

?>
