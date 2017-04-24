<?php

/**
 * UtilPdo
 * 
 * This class is used for handling general query handling functionalities.
 */
namespace Common\UtilityBundle\Repository\Pdo;

/**
 * This class handles general query handling 
 */
class UtilPdo
{

    /**
     * Conatiner Object.
     *
     * @var object
     */
    protected $container;

    /**
     * Connection Object.
     *
     * @var object
     */
    protected $conn;

    /**
     * Constructor for initial setting.
     *
     * @param object $container Container Object
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->conn = $this->container->get('database_connection');
    }

    /**
     * This function is used to execute the passed query
     * 
     * @param string $query The query string
     * 
     * @return array $result The result set
     */
    public function executeQuery($query)
    {
        $result = array();
        if ($query != '') {
            $result = $this->conn->fetchAll($query);
        }

        return $result;
    }
}
