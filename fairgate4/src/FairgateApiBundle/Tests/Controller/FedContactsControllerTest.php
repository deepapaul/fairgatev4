<?php

/**
 * FedContact Controller Test.
 *
 * This controller is used to test api controller.
 *
 * @author     pitsolutions.ch
 *
 * @version    Fairgate V4
 */
namespace FairgateApiBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Test controller for api
 */
class FedContactsControllerTest extends WebTestCase {
    
    public function setUp()
    {
        parent::setUp();

      
    }
    
    /**
     * @dataProvider listFedDataProvider
     */
    public function testgetFedContacts($data)
    {
        $client = WebTestCase::createClient();
        
        $client->request(
                'GET', '/api/contacts/fed/2/members?'.$data['query_string'], array(), array(), array("HTTP_ACCEPT" => "application/json;version=1.0",
                    "HTTP_Authorization" => "Basic " . base64_encode("superadmin" . ":" . "FGis2012dbVdW"), )
        ); 
        
       if ($data['expectedStatus'] == 200) {
             $content = json_decode($client->getResponse()->getContent());
           // $club = $content->club;
           // $this->assertEquals($club[0]->clubId, '1,3,4,7,8');
         
            
        }
    }

    /**
     * Data Provider for testgetFedContacts
     *
     * @return array data provider
     */
    public function listFedDataProvider()
    {
        return [
            [
                [
                    'query_string'   => 'sdate=2016-10-14 12:12:12',
                    'intensity'      => 2.3,
                    'expectedStatus' => 200,
                    'special'        => false,
                ],
            ],
            [
                [
                    'query_string'   => 'lmod=10',
                    'intensity'      => 2.3,
                    'expectedStatus' => 200,
                ],
            ],
            [
                [
                    'query_string'   => 'count=1',
                    'intensity'      => 2.3,
                    'expectedStatus' => 200,
                    'special'        => false,
                ],
            ],
            [
                [
                    'query_string'   => 'lmod=10&count=1',
                    'intensity'      => 2.3,
                    'expectedStatus' => 400,
                ],
            ],
        ];
    }
    
   
}