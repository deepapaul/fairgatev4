<?php

/*
 * This class the service to handle the DB connections to the FgAdmin Database.
 * 
 */
namespace Common\UtilityBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class the service to handle the DB connections to the FgAdmin Database.
 *
 * @author jinesh.m <jineshm.pitsolutions.com>
 */
class FgAdminConnection
{

    /**
     * @var object Container variable 
     */
    public $container;

    /**
     * @var object DB Manager 
     */
    private $adminDbManager;

    /**
     * @var object Entity Manager
     */
    private $adminEntityMgr;

    /**
     * @var object Entity Manager COnnection 
     */
    private $adminConnection;

    /**
     * Constructor of FgAdminConnection class.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->adminDbManager = $this->container->get('doctrine')->getManager('admin');
        $this->adminEntityMgr = $this->container->get('doctrine.orm.admin_entity_manager');
        $this->adminConnection = $this->adminEntityMgr->getConnection();
    }

    /**
     * The function will return the DbManager object
     * 
     * @return object
     */
    public function getAdminManager()
    {
        return $this->adminDbManager;
    }

    /**
     * The function will return the Entity Manger object
     * 
     * @return object
     */
    public function getAdminEntityManager()
    {
        return $this->adminEntityMgr;
    }

    /**
     * The function will return the Entity Manger connection object
     * 
     * @return object
     */
    public function getAdminConnection()
    {
        return $this->adminConnection;
    }
    /*
     * This function will execute the query in the admin DB
     * 
     * @param string $query     The query string to be executed
     * @return array
     */

    public function executeQuery($query)
    {
        $connection = $this->getAdminConnection();
        $statement = $connection->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}
