<?php
// bootstrap.php

require_once 'vendor/autoload.php';

// flag used below for development mode 
$isDevMode = true;

// mapping via XML
$doctrineConfig = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(
    array(__DIR__ . '/config/doctrine'),
    $isDevMode
);

// postgres database configuration parameters
$dbConfig = parse_ini_file('/etc/opt/pelagos/db.ini', true);
$dbConnInfo = $dbConfig['GOMRI_RW'];
$doctrineConn = array(
    'driver' => 'pdo_pgsql',
    'user' => $dbConnInfo['username'],
    'password' => $dbConnInfo['password'],
    'host' => $dbConnInfo['host'],
    'port' => $dbConnInfo['port'],
    'dbname' => $dbConnInfo['dbname'],
);

// obtaining the entity manager
$entityManager = \Doctrine\ORM\EntityManager::create($doctrineConn, $doctrineConfig);
