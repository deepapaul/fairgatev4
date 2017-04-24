<?php

namespace Clubadmin\DocumentsBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;


/**
 * To test SubCategory Controller
 *
 */
class SubCategoryControllerTest extends WebTestCase {
  
    public $client;
    
    /**
    * Subcategory controller constructor
    */
    public function __construct()
    {
       $this->client = static::createClient();
    }
    
    /**
    * Function to test the edit category
    */
    public function testEditcategory()
    {
        $crawler = $this->client->request('GET', '/document/subcategory/club/191');
        $this->assertTrue($crawler->filter('.fairgatedirty')->count() >= 0);
    }
    
    /**
    * function to test the add category
    */
    public function testAddcategory()
    {
        $crawler = $this->client->request('POST', '/document/subcategory/club/191');
        
       /* Checking whether a new row is added or not on clicking the add sub category button */
        $this->assertTrue($crawler->filter('.addednew')->count() >= 0);
      /* Ends here */
        
      /* Checking  document count link is not produced  on clicking the add sub category button */
        $this->assertCount(0, $crawler->filter('html:contains("fg-dev-manage")'));
      /* Ends here */
        
    }
    
}

?>
