<?php

namespace Internal\GalleryBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    /**
     * Method to get login
     */
    public function logIn()
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/india/internal/signin');
        // select the form and fill in some values
        $form = $crawler->selectButton('Login')->form();
        $form['_username'] = 'superadmin';
        $form['_password'] = 'test';
        $crawl = $client->submit($form);         
        $html = $crawl->html();
    }
    
    /**
     * Method to test change scope action
     */
    public function testChangeScopeAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579,580,581', 'scope' => "INTERNAL");
        $crawler = $client->request('POST', '/india/internal/gallery/changeScope', $postData);          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);        
    } 
    
    /**
     * Method to test remove item action
     */
    public function testRemoveItemAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579,580,581');
        $crawler = $client->request('POST', '/india/internal/gallery/removeitem', $postData);          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);        
    } 
    
    /**
     * Method to test delete item action
     */
    public function testDeleteItemAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579,580,581');
        $crawler = $client->request('POST', '/india/internal/gallery/deleteitem', $postData);          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);        
    } 
    
    /**
     * Method to test delete item action
     */
    public function testMoveItemAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579,580,581', 'albumId' => 866);
        $crawler = $client->request('POST', '/india/internal/gallery/moveitem', $postData);          
        $json = ($client->getResponse()->getContent());
        $response = json_decode($json, true);
        $this->assertInternalType('array', $response);
        $this->assertNotSame(null, $response);
        $this->assertArrayHasKey('flash', $response);        
    } 
    
    /*
     * Method to test confirmation popup on move item
     */
    public function testModalPopupOnMoveAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579', 'modalType' => "MOVETO_ALBUM", 'selected' => 'selected', 'params' => array('albumName' => 'album1'));
        $crawler = $client->request('POST', '/india/internal/gallery/confirmation', $postData) ;           
        $this->assertGreaterThan(
            0,
            $crawler->filter('div.s2id_movegallery')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Move image from album1 to...")')->count()
        );  
    }
    
    /*
     * Method to test confirmation popup on change scope
     */
    public function testModalPopupOnChangeScopeAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579', 'modalType' => "CHANGE_SCOPE", 'selected' => 'selected', 'params' => array('currentScope' => 'INTERNAL'));
        $crawler = $client->request('POST', '/india/internal/gallery/confirmation', $postData) ;           
        $this->assertGreaterThan(
            0,
            $crawler->filter('div.radio-list')->count()
        );
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Change scope of the image/video ")')->count()
        );  
    }
    
    /*
     * Method to test confirmation popup on change scope
     */
    public function testModalPopupOnRemoveScopeAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579', 'modalType' => "REMOVE_IMAGE", 'selected' => 'selected', 'params' => array('albumName' => 'album1'));
        $crawler = $client->request('POST', '/india/internal/gallery/confirmation', $postData) ;
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Remove image/video from album1")')->count()
        );  
    }
    
    /*
     * Method to test confirmation popup on change scope
     */
    public function testModalPopupOnDeleteScopeAction()
    {  
        $client = static::createClient();
        $this->logIn(); 
        $postData  = array('checkedIds' => '579', 'modalType' => "DELETE_IMAGE", 'selected' => 'selected' );
        $crawler = $client->request('POST', '/india/internal/gallery/confirmation', $postData) ;
        $this->assertGreaterThan(
            0,
            $crawler->filter('html:contains("Delete image ")')->count()
        );  
    }
    
}
