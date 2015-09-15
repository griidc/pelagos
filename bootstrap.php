<?php
// bootstrap.php

require_once "vendor/autoload.php";

// flag used below for development mode 
$isDevMode = true;

// mapping via XML
$doctrine_config = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
    array(__DIR__."/config/doctrine"),
    $isDevMode
);

// postgres database configuration parameters
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

// obtaining the entity manager
$entityManager = \Doctrine\ORM\EntityManager::create($doctrine_conn, $doctrine_config);
