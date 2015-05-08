<?php
// bootstrap.sqlite.php

require_once "vendor/autoload.php";

// flag used below for development mode
$isDevMode = true;


// mapping via XML
$config = \Doctrine\ORM\Tools\Setup::createXMLMetadataConfiguration(array(__DIR__."/config.sqlite/xml"), $isDevMode);

// mapping via yaml
//$config = \Doctrine\ORM\Tools\Setup::createYAMLMetadataConfiguration(array(__DIR__."/config.sqlite/yaml"), $isDevMode);


// sqlite database configuration parameters
$conn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);

// obtaining the entity manager
$entityManager = \Doctrine\ORM\EntityManager::create($conn, $config);
