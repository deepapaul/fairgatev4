<?php

namespace Common\UtilityBundle\Tests\Repository;

use Common\UtilityBundle\Repository\FgCmAttributesetRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * For handle the cm attributeset
 */
class FgCmAttributesetRepositoryTest extends WebTestCase
{
    /**
     * @var \Blogger\BlogBundle\Repository\BlogRepository
     */
    private $fgCmAttributeset;
    private $container;
    /**
     * set up cm attributes
     */
    public function setUp()
    {
        $kernel = static::createKernel();
        $kernel->boot();
        $this->container = $kernel->getContainer();
        $this->fgCmAttributeset = $kernel->getContainer()
                                       ->get('doctrine.orm.entity_manager')
                                       ->getRepository('CommonUtilityBundle:FgCmAttributeset');
    }
/**
 * get all club contact fields
 */
    public function testGetAllClubContactFields()
    {
        $conn = $this->container->get('database_connection');
        //check whether the function working for sub federation club
        $clubIdArray = array('clubId' => 651, 'federationId' => 608,
            'subFederationId' => 609, 'clubType' => 'sub_federation_club', 'correspondanceCategory' => 2, 'invoiceCategory' => 137);
        $tags = $this->fgCmAttributeset->getAllClubContactFields($clubIdArray, $conn);
        $this->assertTrue(count($tags) > 1);
        //check whether the function working for sub federation
        $clubIdArray = array('clubId' => 609, 'federationId' => 608,
            'subFederationId' => 0, 'clubType' => 'sub_federation', 'correspondanceCategory' => 2, 'invoiceCategory' => 137);
        $tags = $this->fgCmAttributeset->getAllClubContactFields($clubIdArray, $conn);
        $this->assertTrue(count($tags) > 1);

    }


}