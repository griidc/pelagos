<?php

namespace Pelagos;

class Persistance
{
    /**
     * Static function to create an entity manager.
     * This method creates an entity manager with the mappings defined in XML
     * and the database configuration loaded from /etc/opt/pelagos/db.ini
     */
    public static function createEntityManager()
    {
        // create Doctrine metadata configuration from mappings defined in XML
        $doctrine_config = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
            array(__DIR__.'/../../config/doctrine')
        );

        // get database configuration parameters from db.ini and set up Doctrine database configuration array
        $db_config = parse_ini_file('/etc/opt/pelagos/db.ini', true);
        $db_conn_info = $db_config['GOMRI_RW'];
        $doctrine_conn = array(
            'driver'   => 'pdo_pgsql',
            'user'     => $db_conn_info['username'],
            'password' => $db_conn_info['password'],
            'host'     => $db_conn_info['host'],
            'port'     => $db_conn_info['port'],
            'dbname'   => $db_conn_info['dbname'],
        );

        // create and return an entity manager
        return \Doctrine\ORM\EntityManager::create($doctrine_conn, $doctrine_config);
    }
}
