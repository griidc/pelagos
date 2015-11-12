<?php

namespace Pelagos;

use Doctrine\ORM\EntityManager;
use Doctrine\DBAL\Event\Listeners\PostgresSessionInit;

/**
 * A class to handle setting up persistence.
 */
class Persistance
{
    /**
     * Static function to create an entity manager.
     *
     * This method creates an entity manager with the mappings defined in XML
     * and the database configuration loaded from /etc/opt/pelagos/db.ini
     *
     * @return EntityManager The entity manager.
     */
    public static function createEntityManager()
    {
        // Create Doctrine metadata configuration from mappings defined in XML.
        $doctrineConfig = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
            array(__DIR__ . '/../../config/doctrine'),
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
        $entityManager = EntityManager::create($doctrineConn, $doctrineConfig);
        // Register the PostgresSessionInit listener with session variables.
        $entityManager->getConnection()->getEventManager()->addEventSubscriber(
            new PostgresSessionInit(
                array(
                    'timezone' => 'UTC',
                    'intervalstyle' => 'iso_8601',
                )
            )
        );
        // Return the entity manager.
        return $entityManager;
    }
}
