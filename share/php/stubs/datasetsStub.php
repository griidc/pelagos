<?php

function getIdentifiedDatasetsByProjectId(PDO $pdo, $projectId)
{
    $value = rand(50,100);
    $sleep = rand(0,500000);
    usleep($sleep);
    return $value;
}

function getRegisteredDatasetsByProjectId(PDO $pdo, $projectId)
{
    $value = rand(25,50);
    $sleep = rand(0,500000);
    usleep($sleep);
    return $value;
}

function getAvailableDatasetsByProjectId(PDO $pdo, $projectId)
{
    $value = rand(0,25);
    $sleep = rand(0,500000);
    usleep($sleep);
    return $value;
}

function getProjectIdFromUdi($dbh, $udi)
{
    switch ($udi) {
        case 'R1.x100.001:0001':
            return 100;
        case 'R1.x200.002:0001':
            return 200;
        case 'R1.x300.003:0001':
            return 300;
    }
    return null;
}
