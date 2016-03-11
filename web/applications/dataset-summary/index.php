<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Dataset Summary';

set_include_path("../../../share/php" . PATH_SEPARATOR . get_include_path());

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);
$GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/db.ini', true);

# load Drupal functions
require_once 'drupal.php';
# load database utilities
require_once 'DBUtils.php';

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

# add custom Twig extensions
$app->view->parserExtensions = array(
    new \Pelagos\TwigExtensions()
);

$app->get('/:udi', function ($udi) use ($app) {
    # Regexp check this flaming chainsaw juggling sword-swallowing dangerous beast!
    if (preg_match('/^[A-Z][0-6]\.x[0-9]{3}\.[0-9]{3}:[0-9]{4}$/', $udi) == 1) {
        $pid = getmypid();

        $tableQueries = array(
            'datasets' => "SELECT * FROM datasets WHERE dataset_udi = '$udi'",
            'registry' => "SELECT * FROM registry WHERE registry_id like '%$udi%' ORDER BY registry_id DESC",
            'metadata' => "SELECT * FROM metadata WHERE registry_id like '%$udi%' ORDER BY registry_id DESC",
            'dataset2publication_link_table' => "SELECT * FROM dataset2publication_link_table WHERE dataset_udi  = '$udi'",
            'alt_datasets' => "SELECT * FROM alt_datasets WHERE primary_udi = '$udi'",
            'doi_regs' => "SELECT * FROM doi_regs WHERE url LIKE '%$udi%'",
        );

        foreach ($tableQueries as $table => $query) {
            $queryFilename = "/var/tmp/$table-query.$pid";
            $resultFilename = "/var/tmp/$table-result.$pid";
            $query = "\COPY ($query) TO '$resultFilename' WITH csv header";
            file_put_contents($queryFilename, $query);
            sleep(1);
            exec("/usr/bin/psql -U gomri_user gomri < $queryFilename");
            unlink($queryFilename);
        }
    } else {
        print "not a valid udi";
    }
});

$app->run();
