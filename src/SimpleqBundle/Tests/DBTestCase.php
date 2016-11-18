<?php

namespace simpleq\SimpleqBundle\Tests;

use BadMethodCallException;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PDO;
use PDOException;
use PHPUnit_Extensions_Database_DB_IDatabaseConnection;
use PHPUnit_Extensions_Database_TestCase;

abstract class DBTestCase extends PHPUnit_Extensions_Database_TestCase
{
    /**
     * @var PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    private static $connection;

    /**
     * @var Connection
     */
    private static $doctrineConnection;

    /**
     * Sets up the db connection
     */
    private function setUpConnection()
    {
        if (is_null(self::$connection)) {
            try {
                $pdo = $this->connect();
            } catch (PDOException $e) {
                $this->fail('Failed to connect to local MySQL server');

                return;
            }

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, 1);

            self::$connection = $this->createDefaultDBConnection($pdo);
            self::$doctrineConnection = DriverManager::getConnection(array('pdo' => $pdo));
        }
    }

    /**
     * Returns the test database connection.
     *
     * @throws BadMethodCallException
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    protected final function getConnection()
    {
        if (is_null(self::$connection)) {
            $this->setUpConnection();
        }

        return self::$connection;
    }

    /**
     * @return Connection
     */
    protected final function getDoctrineConnection()
    {
        if (is_null(self::$doctrineConnection)) {
            $this->setUpConnection();
        }

        return self::$doctrineConnection;
    }

    /**
     * @return PDO
     * @throws PDOException
     */
    protected function connect()
    {
        global $parameters;

        $dsn = sprintf(
            'sqlite:%s.db3',
            $parameters['simpleq_database_name']
        );

        $pdo = new PDO($dsn);

        return $pdo;
    }

    /**
     *
     */
    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::$connection = null;
    }
}