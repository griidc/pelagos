<?php

$GLOBALS['libraries'] = parse_ini_file('/etc/griidc/libraries.ini',true);

require_once $GLOBALS['libraries']['Slim2']['include'];
\Slim\Slim::registerAutoloader();
require_once $GLOBALS['libraries']['Slim-Views']['include_Twig'];
# load Twig
require_once 'Twig/Autoloader.php';

require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/db-utils.lib.php';
require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/drupal.php';
require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/dumpIncludesFile.php';
require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/rpis.php';

$GLOBALS['config'] = parse_ini_file('config.ini',true);

$GLOBALS['sizes'] = array(
    'KB' => 1000,
    'MB' => pow(1000,2),
    'GB' => pow(1000,3),
    'TB' => pow(1000,4)
);

$GLOBALS['size_ranges'] = array(
    array(
        'label' => '< 1 MB',
        'color' => '#c6c8f9',
        'range0' => '< ' . $GLOBALS['sizes']['MB']
    ),
    array(
        'label' => '1 MB - 100 MB',
        'color' => '#88F',
        'range0' => '>= ' . $GLOBALS['sizes']['MB'],
        'range1' => '< ' . ($GLOBALS['sizes']['MB'] * 100)
    ),
    array(
        'label' => '100 MB - 1 GB',
        'color' => '#90c593',
        'range0' => '>= ' . ($GLOBALS['sizes']['MB'] * 100),
        'range1' => '< ' . ($GLOBALS['sizes']['GB'])
    ),
    array(
        'label' => '1 GB - 100 GB',
        'color' => 'yellow',
        'range0' => '>= ' . ($GLOBALS['sizes']['GB']),
        'range1' => '< ' . ($GLOBALS['sizes']['GB'] * 100)
    ),
    array(
        'label' => '100 GB - 1 TB',
        'color' => '#f6d493',
        'range0' => '>= ' . ($GLOBALS['sizes']['GB'] * 100),
        'range1' => '< ' . ($GLOBALS['sizes']['TB'])
    ),
    array(
        'label' => '> 1 TB',
        'color' => '#f6b4b5',
        'range0' => '>= ' . ($GLOBALS['sizes']['TB'])
    )
);

$GLOBALS['categories'] = array(
    'dataset_for' => array(
        'id' => 'dataset_for',
        'categories' => array(
            'ecological_biological' => array(
                'id' => 'dataset_for_ecological_biological',
                'label' => 'Ecological/Biological',
                'query' => "dataset_for LIKE 'Ecological/Biological|%'"
            ),
            'dataset_for_chemical' => array(
                'id' => 'dataset_for_chemical',
                'label' => 'Chemical',
                'query' => "dataset_for LIKE '%|Chemical|%'"
            ),
            'dataset_for_economics' => array(
                'id' => 'dataset_for_economics',
                'label' => 'Economics',
                'query' => "dataset_for LIKE '%|Economics|%'"
            ),
            'dataset_for_physical_oceanography' => array(
                'id' => 'dataset_for_physical_oceanography',
                'label' => 'Physical Oceanography',
                'query' => "dataset_for LIKE '%|Physical Oceanographical|%'"
            ),
            'dataset_for_human_health' => array(
                'id' => 'dataset_for_human_health',
                'label' => 'Human Health',
                'query' => "dataset_for LIKE '%|Human Health|%'"
            ),
            'dataset_for_atmospheric' => array(
                'id' => 'dataset_for_atmospheric',
                'label' => 'Atmospheric',
                'query' => "dataset_for LIKE '%|Atmospheric|%'"
            ),
            'dataset_for_social_cultural_political' => array(
                'id' => 'dataset_for_social_cultural_political',
                'label' => 'Social/Cultural/Political',
                'query' => "dataset_for LIKE '%|Social/Cultural/Political|%'"
            ),
            'dataset_for_others' => array(
                'id' => 'dataset_for_others',
                'label' => 'Others',
                'query' => "dataset_for NOT LIKE '%|'"
            )
        )
    ),
    'dataset_type' => array(
        'id' => 'dataset_type',
        'categories' => array(
            'dataset_type_structured_file' => array(
                'id' => 'dataset_type_structured_file',
                'label' => 'Structured Text File (CSV, TSV)',
                'query' => "dataset_type LIKE 'Structured, Generic Text/ASCII File (CSV, TSV)|%'"
            ),
            'dataset_type_unstructured_file' => array(
                'id' => 'dataset_type_unstructured_file',
                'label' => 'Unstructured Text File (TXT)',
                'query' => "dataset_type LIKE '%|Unstructured, Generic Text/ASCII File (TXT)|%'"
            ),
            'dataset_type_video' => array(
                'id' => 'dataset_type_video',
                'label' => 'Video',
                'query' => "dataset_type LIKE '%|Video|%'"
            ),
            'dataset_type_images' => array(
                'id' => 'dataset_type_images',
                'label' => 'Images',
                'query' => "dataset_type LIKE '%|Images|%'"
            ),
            'dataset_type_netcdf' => array(
                'id' => 'dataset_type_netcdf',
                'label' => 'CDF/netCDF',
                'query' => "dataset_type LIKE '%|CDF/netCDF|%'"
            ),
            'dataset_type_gml_xml' => array(
                'id' => 'dataset_type_gml_xml',
                'label' => 'Structured, GML/XML-base',
                'query' => "dataset_type LIKE '%|GML/XML Structured|%'"
            ),
            'dataset_type_others' => array(
                'id' => 'dataset_type_others',
                'label' => 'Others',
                'query' => "dataset_type NOT LIKE '%|'"
            )
        )
    ),
    'dataset_procedure' => array(
        'id' => 'dataset_procedure',
        'categories' => array(
            'dataset_procedure_field_sampling' => array(
                'id' => 'dataset_procedure_field_sampling',
                'label' => 'Field Sampling',
                'query' => "approach LIKE 'Field Sampling|%'"
            ),
            'dataset_procedure_literature_based' => array(
                'id' => 'dataset_procedure_literature_based',
                'label' => 'Literature Based',
                'query' => "approach LIKE '%|Literature Based|%'"
            ),
            'dataset_procedure_simulated_generated' => array(
                'id' => 'dataset_procedure_simulated_generated',
                'label' => 'Simulated/Generated',
                'query' => "approach LIKE '%|Simulated or Generated|%'"
            ),
            'dataset_procedure_remote_sensing' => array(
                'id' => 'dataset_procedure_remote_sensing',
                'label' => 'Remote Sensing',
                'query' => "approach LIKE '%|Remote Sensing|%'"
            ),
            'dataset_procedure_labratory' => array(
                'id' => 'dataset_procedure_labratory',
                'label' => 'Laboratory',
                'query' => "approach LIKE '%|Labratory|%'"
            ),
            'dataset_procedure_others' => array(
                'id' => 'dataset_procedure_others',
                'label' => 'Others',
                'query' => "approach NOT LIKE '%|'"
            )
        )
    )
);

$app = new \Slim\Slim(array(
                        'view' => new \Slim\Views\Twig(),
                        'debug' => true,
                        'log.level' => \Slim\Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->view->parserDirectory = $GLOBALS['libraries']['Twig']['directory'];

$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    $stash['funds'] = getFundingSources(getDBH('RPIS'));
    $stash['projects'] = getProjectDetails(getDBH('RPIS'),array("fundsrc=7"));
    header('Content-type: text/javascript');
    $app->render("js/$name.js",$stash);
    exit;
});

$app->get('/css/:name.css', function ($name) use ($app) {
    header('Content-type: text/css');
    $app->render("css/$name.css");
    exit;
});

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_library('system', 'ui.tabs');
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/stats.css",array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.min.js',array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.resize.min.js',array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.time.min.js',array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.pie.min.js',array('type'=>'external'));
    drupal_add_js('//www.google.com/jsapi',array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/includes/js/jquery.flot.barnumbers.js",array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/includes/js/stats.js",array('type'=>'external'));

    $stash = array();
    $stash['total_capacity_tb'] = round($GLOBALS['config']['stats']['total_capacity'] / pow(1000,4),0);
    $stash['categories'] = $GLOBALS['categories'];
    $stash['categories_columns'] = 4;
    $stash['size_ranges'] = $GLOBALS['size_ranges'];
    $stash['size_range_colors'] = array();
    foreach ($GLOBALS['size_ranges'] as $range) {
        $stash['size_range_colors'][] = $range['color'];
    }

    $dbh = OpenDB('RIS_RO');
    $sth = $dbh->prepare('SELECT * FROM
                          (SELECT COUNT(*) AS projects_count FROM Programs) AS T1,
                          (SELECT COUNT(*) AS tasks_count FROM Projects) AS T2,
                          (SELECT COUNT(*) AS researchers_count FROM People) AS T3,
                          (SELECT COUNT(*) AS institutions_count FROM Institutions) AS T4,
                          (SELECT COUNT(DISTINCT Institution_Country) AS countries_count FROM Institutions) AS T5
                        ');
    $sth->execute();
    $ris = $sth->fetchAll();
    $stash['ris'] = $ris[0];

    return $app->render('html/index.html',$stash);
});

$app->get('/total-records-over-time', function () use ($app) {
    $env = $app->environment();
    drupal_add_css("$env[SCRIPT_NAME]/includes/css/stats.css",array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.min.js',array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.resize.min.js',array('type'=>'external'));
    drupal_add_js('//cdnjs.cloudflare.com/ajax/libs/flot/0.8.2/jquery.flot.time.min.js',array('type'=>'external'));
    drupal_add_js('//www.google.com/jsapi',array('type'=>'external'));
    drupal_add_js("$env[SCRIPT_NAME]/includes/js/stats.js",array('type'=>'external'));
    $stash = array('page' => 'overview','type' => 'total-records-over-time');
    return $app->render('html/index.html',$stash);
});

$app->get('/data/overview/summary-of-records', function () use ($app) {
    $sor_data = array();

    $dbh = OpenDB('GOMRI_RO');

    $bars = array( 'barWidth' => 0.8 );

    $SQL = "SELECT COUNT(*) FROM datasets WHERE dataset_udi NOT LIKE '00%' AND status > 1";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $count = $sth->fetchColumn();
    $sor_data[] = array(
        'label' => 'Datasets Identified',
        'data' => array(array(0.1,$count)),
        'bars' => $bars
    );

    $SQL = "SELECT COUNT(*) FROM registry_view WHERE registry_id NOT LIKE '00%' AND url_data IS NOT NULL";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $count = $sth->fetchColumn();
    $sor_data[] = array(
        'label' => 'Datasets Registered',
        'data' => array(array(1,$count)),
        'bars' => $bars
    );

    $SQL = "SELECT COUNT(*) FROM registry_view WHERE registry_id NOT LIKE '00%' AND metadata_dl_status = 'Completed'";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $count = $sth->fetchColumn();
    $sor_data[] = array(
        'label' => 'Metadata Submitted',
        'data' => array(array(1.9,$count)),
        'bars' => $bars
    );

    $SQL = "SELECT COUNT(*) FROM doi_regs WHERE approved = 't'";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $count = $sth->fetchColumn();
    $sor_data[] = array(
        'label' => 'DOIs Issued',
        'data' => array(array(2.8,$count)),
        'bars' => $bars
    );

    print json_encode(array(
        'page' => 'overview',
        'section' => 'summary-of-records',
        'data' => $sor_data
    ));

    drupal_exit();
});

$app->get('/data/overview/total-records-over-time', function () use ($app) {
    $trot_data = array();
    $dbh = OpenDB('GOMRI_RO');

    $identifications = array( 'label' => 'Total Datasets Identified', 'data' => array() );
    $SQL = "SELECT row_number() OVER(ORDER BY dataset_uid) AS count, dataset_uid::INT8 * 1000 AS ts FROM datasets WHERE status > 1;";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $rows = $sth->fetchAll();
    foreach ($rows as $row) {
        $identifications['data'][] = array($row['ts'],$row['count']);
    }
    $identifications['data'][] = array(time()*1000,$rows[count($rows)-1]['count']);
    $trot_data[] = $identifications;

    $registrations = array( 'label' => 'Total Datasets Registered', 'data' => array() );
    $SQL = "SELECT row_number() OVER(ORDER BY submittimestamp) AS count,
                   extract(epoch from submittimestamp) * 1000 AS ts
            FROM registry
            WHERE registry_id IN (SELECT min_id FROM (SELECT SUBSTRING(registry_id FROM 1 FOR 16) AS udi,
                                         MIN(registry_id) AS min_id
                                  FROM registry
                                  WHERE registry_id NOT LIKE '00%' AND url_data IS NOT NULL
                                  GROUP BY udi
                                  ORDER BY udi) AS dataset_udi)
            ORDER BY submittimestamp;";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $rows = $sth->fetchAll();
    foreach ($rows as $row) {
        $registrations['data'][] = array($row['ts'],$row['count']);
    }
    $registrations['data'][] = array(time()*1000,$rows[count($rows)-1]['count']);
    $trot_data[] = $registrations;

    $metadata = array( 'label' => 'Total Metadata Submitted', 'data' => array() );
    $SQL = "SELECT row_number() OVER(ORDER BY submittimestamp) AS count,
                   extract(epoch from submittimestamp) * 1000 AS ts
            FROM registry
            WHERE registry_id IN (SELECT min_id FROM (SELECT SUBSTRING(registry_id FROM 1 FOR 16) AS udi,
                                         MIN(registry_id) AS min_id
                                  FROM registry
                                  WHERE registry_id NOT LIKE '00%' AND metadata_dl_status = 'Completed'
                                  GROUP BY udi
                                  ORDER BY udi) AS dataset_udi)
            ORDER BY submittimestamp;";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $rows = $sth->fetchAll();
    foreach ($rows as $row) {
        $metadata['data'][] = array($row['ts'],$row['count']);
    }
    $metadata['data'][] = array(time()*1000,$rows[count($rows)-1]['count']);
    $trot_data[] = $metadata;
    print json_encode(array(
        'page' => 'overview',
        'section' => 'total-records-over-time',
        'data' => $trot_data
    ));
    drupal_exit();
});

$app->get('/data/overview/total-size-over-time', function () use ($app) {
    $tsot_data = array();
    $dbh = OpenDB('GOMRI_RO');
    $registrations = array( 'label' => 'Total Size of All Datasets Stored at GRIIDC (TB)', 'data' => array() );
    $SQL = "SELECT r1.submittimestamp, r1.dataset_download_size, SUM(r2.dataset_download_size)/1000/1000/1000/1000 AS sum,
                   extract(epoch from r1.submittimestamp) * 1000 AS ts
            FROM 
            (SELECT * FROM registry
                WHERE registry_id IN (SELECT min_id FROM (SELECT SUBSTRING(registry_id FROM 1 FOR 16) AS udi,
                                             MIN(registry_id) AS min_id
                                  FROM registry
                                  WHERE registry_id NOT LIKE '00%' AND dataset_download_status = 'Completed'
                                  GROUP BY udi
                                  ORDER BY udi) AS dataset_udi)
            ) r1
            INNER JOIN
            (SELECT * FROM registry
                WHERE registry_id IN (SELECT min_id FROM (SELECT SUBSTRING(registry_id FROM 1 FOR 16) AS udi,
                                             MIN(registry_id) AS min_id
                                  FROM registry
                                  WHERE registry_id NOT LIKE '00%' AND dataset_download_status = 'Completed'
                                  GROUP BY udi
                                  ORDER BY udi) AS dataset_udi)
            ) r2
            ON r1.submittimestamp >= r2.submittimestamp
            GROUP BY r1.submittimestamp, r1.dataset_download_size
            ORDER BY r1.submittimestamp;";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $rows = $sth->fetchAll();
    foreach ($rows as $row) {
        $registrations['data'][] = array($row['ts'],$row['sum']);
    }
    if (count($rows) > 0) {
        $registrations['data'][] = array(time()*1000,$rows[count($rows)-1]['sum']);
    }
    $tsot_data[] = $registrations;
    print json_encode(array(
        'page' => 'overview',
        'section' => 'total-size-over-time',
        'data' => $tsot_data
    ));
    drupal_exit();
});

$app->get('/data/overview/dataset-size-ranges', function () use ($app) {
    $size_ranges_data = array();
    $dbh = OpenDB('GOMRI_RO');
    foreach ($GLOBALS['size_ranges'] AS $range) {
        $SQL = "SELECT COUNT(*)
                FROM registry_view
                WHERE dataset_download_size $range[range0]";
        if (array_key_exists('range1',$range)) {
            $SQL .= " AND dataset_download_size $range[range1]";
        }
        $sth = $dbh->prepare($SQL);
        $sth->execute();
        $count = $sth->fetchColumn();
        $size_ranges_data[] = $count;
    }
    print json_encode(array(
        'page' => 'overview',
        'section' => 'dataset-size-ranges',
        'data' => $size_ranges_data
    ));
    drupal_exit();
});

$app->get('/data/overview/researcher_map', function () use ($app) {
    $dbh = OpenDB('RIS_RO');
    $sth = $dbh->prepare('SELECT Country_Name, COUNT(*) AS count FROM Institutions JOIN People ON People.People_Institution = Institutions.Institution_ID JOIN Country ON Country.Country_Abbr = Institutions.Institution_Country GROUP BY Country_Name;');
    $sth->execute();
    $result = $sth->fetchAll();
    $countries = array(array('Country','Researchers'));
    foreach ($result as $row) {
        $countries[] = array($row['Country_Name'],intval($row['count']));
    }
    print json_encode($countries);
    drupal_exit();
});

$app->get('/data/overview/system-capacity', function () use ($app) {
    $capacity_data = array();
    $dbh = OpenDB('GOMRI_RO');
    $SQL = "SELECT SUM(dataset_download_size) FROM registry_view WHERE registry_id NOT LIKE '00%'";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $size_bytes = $sth->fetchColumn();
    $size_tb = round($size_bytes / pow(1000,4),1);
    $capacity_data = array(
        array(
            'label' => 'Used Space',
            'data' => $size_tb
        ),
        array(
            'label' => 'Available Space',
            'data' => round($GLOBALS['config']['stats']['total_capacity'] / pow(1000,4),1) - $size_tb
        )
    );
    print json_encode(array(
        'page' => 'overview',
        'section' => 'system-capacity',
        'data' => $capacity_data
    ));
    drupal_exit();
});

$app->run();

?>
