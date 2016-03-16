<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

set_include_path('../../../share/php' . PATH_SEPARATOR . get_include_path());

// load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
$GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'] . '/db.ini', true);

// load Drupal functions
require_once 'drupal.php';
// load includes file dumper
require_once 'dumpIncludesFile.php';
// load database utilities
require_once 'DBUtils.php';
// load LDAP library
include_once 'ldap.php';

// initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

// add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigExtensions()
);

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_js("$env[SCRIPT_NAME]/includes/dataset-summary.js", array('type' => 'external'));
    return $app->render(
        'index.html',
        array(
            'metadata_reviewer' => memberHasApplicationRole($GLOBALS['user']->name, 'Metadata', 'reviewers'),
        )
    );
});

$app->get('/:udi/delete', function ($udi) use ($app) {
    if (!memberHasApplicationRole($GLOBALS['user']->name, 'Metadata', 'reviewers')) {
        drupal_set_message('Only Metadata Reviewers can delete datasets', 'error');
        return;
    }
    if (!preg_match('/^[A-Z][0-6]\.x[0-9]{3}\.[0-9]{3}:[0-9]{4}$/', $udi)) {
        drupal_set_message("$udi is not a valid udi", 'error');
        return;
    }
    if (preg_match('/^[A-Z][0-6]\.x[0-9]{3}\.[0-9]{3}:[0-9]{4}$/', $udi) == 1) {
        drupal_set_message("Deleted: $udi", 'status');
        $tableQueries = array(
            'datasets' => "DELETE FROM datasets WHERE dataset_udi = '$udi'",
            'metadata' => "DELETE FROM metadata WHERE registry_id like '$udi%'",
            'dataset2publication_link_table' => "DELETE FROM dataset2publication_link_table WHERE dataset_udi = '$udi'",
            'alt_datasets' => "DELETE FROM alt_datasets WHERE primary_udi = '$udi' OR alternate_udi = '$udi'",
            'doi_regs' => "DELETE FROM doi_regs WHERE url LIKE '%$udi%'",
        );
        $dbh = openDB('GOMRI_RO');
        foreach ($tableQueries as $table => $query) {
            $sth = $dbh->prepare($query);
            $sth->execute();
        }
        $sth = $dbh->prepare('SELECT MAX(registry_id) FROM registry WHERE dataset_udi = ?');
        $sth->execute(array($udi));
        $registryId = $sth->fetchColumn();
        $sth = $dbh->prepare('UPDATE registry SET dataset_download_status = \'PendingDeletion\' WHERE registry_id = ?');
        $sth->execute(array($registryId));
        $dbh = null;
        system($GLOBALS['config']['paths']['root'] . '/daemons/filer/trigger-filer');
    }
});

$app->get('/:udi(/:action)', function ($udi, $action = null) use ($app) {
    // Regexp check this flaming chainsaw juggling sword-swallowing dangerous beast!
    if (preg_match('/^[A-Z][0-6]\.x[0-9]{3}\.[0-9]{3}:[0-9]{4}$/', $udi) == 1) {
        $fileNamePrefix = preg_replace('/:/', '.', $udi) . '-' . date('Ymd');

        // Make unique between runs.
        $pid = getmypid();

        // Create directory for dumps.
        exec("mkdir /var/tmp/$fileNamePrefix.$pid");

        $tableQueries = array(
            'datasets' => "SELECT * FROM datasets WHERE dataset_udi = '$udi'",
            'registry' => "SELECT * FROM registry WHERE registry_id like '%$udi%' ORDER BY registry_id DESC",
            'metadata' => "SELECT * FROM metadata WHERE registry_id like '%$udi%' ORDER BY registry_id DESC",
            'dataset2publication_link_table' => "SELECT * FROM dataset2publication_link_table WHERE dataset_udi = '$udi'",
            'alt_datasets' => "SELECT * FROM alt_datasets WHERE primary_udi = '$udi' OR alternate_udi = '$udi'",
            'doi_regs' => "SELECT * FROM doi_regs WHERE url LIKE '%$udi%'",
        );

        $dbHost = $GLOBALS['db']['GOMRI_RO']['host'];
        $dbPort = $GLOBALS['db']['GOMRI_RO']['port'];
        $dbName = $GLOBALS['db']['GOMRI_RO']['dbname'];
        $dbUser = $GLOBALS['db']['GOMRI_RO']['username'];
        putenv('PGPASSWORD=' . $GLOBALS['db']['GOMRI_RO']['password']);

        foreach ($tableQueries as $table => $query) {
            $queryFilename = "/var/tmp/$fileNamePrefix.$pid/$table.sql";
            $resultFilename = "/var/tmp/$fileNamePrefix.$pid/$fileNamePrefix-$table.csv";
            $query = "\COPY ($query) TO '$resultFilename' WITH csv header";
            file_put_contents($queryFilename, $query);
            // in production, we should create a separate read/only account for this pgpass auth to work safer.
            exec("/usr/bin/psql -h $dbHost -p $dbPort -U $dbUser $dbName < $queryFilename");
            unlink($queryFilename);
        }

        if ($action == 'download') {
            exec("zip -rj /var/tmp/$fileNamePrefix.zip /var/tmp/$fileNamePrefix.$pid");
            // send zip to browser
            header('Content-Type: application/zip');
            header("Content-Disposition: attachment; filename=$fileNamePrefix.zip");
            readfile("/var/tmp/$fileNamePrefix.zip");
            unlink("/var/tmp/$fileNamePrefix.zip");
        } else {
            header('Content-Type: text/plain');
            foreach (array_keys($tableQueries) as $table) {
                echo "$fileNamePrefix-$table.csv\n";
                readfile("/var/tmp/$fileNamePrefix.$pid/$fileNamePrefix-$table.csv");
                echo "\n";
            }
        }
        exec("rm -rf /var/tmp/$fileNamePrefix.$pid");
    } else {
        echo 'not a valid udi';
    }
    drupal_exit();
});

$app->run();
