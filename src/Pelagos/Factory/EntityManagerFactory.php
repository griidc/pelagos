<?php

namespace Pelagos\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Types\Type;
use Pelagos\DoctrineExtensions\DBAL\Event\Listeners\PostgresSessionInit;

/**
 * A class to handle setting up persistence.
 */
class EntityManagerFactory
{
    /**
     * A static property to hold an instance of EntityManager configured for Pelagos.
     *
     * @var EntityManager
     */
    private static $entityManager;

    /**
     * Private constructor to prevent instantiation.
     */
    private function __construct()
    {
        // Do nothing.
    }

    /**
     * Static function to create an entity manager configure for Pelagos.
     *
     * This method creates an entity manager with the mappings defined in XML
     * and the database configuration loaded from /etc/opt/pelagos/db.ini
     *
     * If an EntityManager has already been created previously, it is returned.
     *
     * @return EntityManager The entity manager.
     */
    public static function create()
    {
        // If we have already created an entity manager.
        if (isset(self::$entityManager)) {
            // Just return it.
            return self::$entityManager;
        }
        // Create Doctrine metadata configuration from mappings defined in XML.
        $doctrineConfig = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
            array(__DIR__ . '/../../../config/doctrine'),
            true
        );

        // Get database configuration parameters from db.ini and set up Doctrine database configuration array.
        $dbConfig = parse_ini_file('/etc/opt/pelagos/db.ini', true);
        $dbConnInfo = $dbConfig['GOMRI_RW'];
        $doctrineConn = array(
            'driver'   => 'pdo_pgsql',
            'user'     => $dbConnInfo['username'],
            'password' => $dbConnInfo['password'],
            'host'     => $dbConnInfo['host'],
            'port'     => $dbConnInfo['port'],
            'dbname'   => $dbConnInfo['dbname'],
        );

        // Create an entity manager.
        self::$entityManager = EntityManager::create($doctrineConn, $doctrineConfig);
        // Register the PostgresSessionInit listener with session variables.
        self::$entityManager->getConnection()->getEventManager()->addEventSubscriber(
            new PostgresSessionInit(
                array(
                    'timezone' => 'UTC',
                    'intervalstyle' => 'iso_8601',
                )
            )
        );
        // If the custom type "token_use_type" has not already been added to the Doctrine type map.
        if (!Type::hasType('token_use_type')) {
            // Add the mapping.
            Type::addType('token_use_type', 'Pelagos\DoctrineExtensions\DBAL\Types\TokenUseType');
        }
        // Register token_use_type for use with our database platform.
        self::$entityManager
            ->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping(
                // Database type
                'token_use_type',
                // Doctrine type
                'token_use_type'
            );
        // If the custom type "interval" has not already been added to the Doctrine type map.
        if (!Type::hasType('interval')) {
            // Add the mapping.
            Type::addType('interval', 'Pelagos\DoctrineExtensions\DBAL\Types\IntervalType');
        }
        // Register interval for use with our database platform.
        self::$entityManager
            ->getConnection()
            ->getDatabasePlatform()
            ->registerDoctrineTypeMapping(
                // Database type
                'interval',
                // Doctrine type
                'interval'
            );
        // Return the entity manager configured for Pelagos.
        return self::$entityManager;
    }
}
