<?php

/*
 * This class to sync the data from the fairgate DB to FAdmin DB
 * 
 */
namespace Common\UtilityBundle\Util;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This class to sync the data from the fairgate DB to FAdmin DB.
 *
 * @author pitsolutions <pitsolutions.ch>
 */
class FgMySqlSyncData
{

    /**
     * @var object Container variable 
     */
    public $container;

    /**
     * @var object Connection variable 
     */
    public $connection;

    /**
     * @var object The DB Manager Object 
     */
    public $adminDbManager;

    /**
     * @var object The entity manager object
     */
    public $adminEntityMgr;

    /**
     * @var object The admin connection object
     */
    public $adminConnection;

    /**
     * Constructor of FgAdminConnection class.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        
        $this->connection = $this->container->get("fg.admin.connection");
        $this->adminDbManager = $this->connection->getAdminManager();
        $this->adminEntityMgr = $this->connection->getAdminEntityManager();
        $this->adminConnection = $this->connection->getAdminConnection();
    }

    /*
     * This function will execute the query in the admin DB
     * 
     * @param string $query     The query string to be executed
     * @return array
     */

    public function executeQuery($query)
    {
        $statement = $this->adminConnection->prepare($query);
        $statement->execute();
        return;
    }
}
