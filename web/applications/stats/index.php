<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'System Statistics (as of ' . date('F j, Y') . ')';

$GLOBALS['libraries'] = parse_ini_file('/etc/griidc/libraries.ini',true);

require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/db-utils.lib.php';
require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/drupal.php';
require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/dumpIncludesFile.php';
require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/rpis.php';
#require_once $GLOBALS['libraries']['GRIIDC']['directory'].'/php/datasets.php';
require_once '../../../share/php/datasets.php';

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

$app = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig()
    )
);

$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
    $app->view()->appendData(array('base_path' => $GLOBALS['pelagos']['base_path']));
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
                          (SELECT COUNT(*) AS projects_count FROM Programs WHERE Program_ID < 700) AS T1,
                          (SELECT COUNT(*) AS tasks_count FROM Projects) AS T2,
                          (SELECT COUNT(*) AS researchers_count FROM People WHERE People_ID < 9000) AS T3,
                          (SELECT COUNT(*) AS institutions_count FROM Institutions WHERE Institution_ID < 1000) AS T4,
                          (SELECT COUNT(DISTINCT Institution_Country) AS countries_count FROM Institutions WHERE Institution_ID < 1000) AS T5
                        ');
    $sth->execute();
    $ris = $sth->fetchAll();
    $stash['ris'] = $ris[0];
    $sth = null;
    $dbh = null;

    $gomri_dbh = OpenDB("GOMRI_RO");
    $stash['datasetCount']=count_registered_datasets($gomri_dbh, array("udi != F%"));
    $gomri_dbh = null;

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

    $bars = array( 'barWidth' => 0.7 );

    # called from datasets.php library
    # 0 = unsubmitted, 1 = Submitted (locked), 2 = Approved
    $countIdentified = count_identified_datasets($dbh,array("status>1", "udi != F%"));
    $countAvailable = count_registered_datasets($dbh,array("availability=available", "udi != F%"));
    $sor_data[] = array(
        'label' => 'Datasets In Development',
        'data' => array(array(.225,$countIdentified-$countAvailable)),
        'bars' => $bars
    );

    $sor_data[] = array(
        'label' => 'Datasets Available',
        'data' => array(array(1.15,$countAvailable)),
        'bars' => $bars
    );

    $SQL = "SELECT COUNT(*) FROM doi_regs WHERE approved = 't'";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $count = $sth->fetchColumn();
    $sth=null;
    $sor_data[] = array(
        'label' => 'DOIs Issued',
        'data' => array(array(2.075,$count)),
        'bars' => $bars
    );

    print json_encode(array(
        'page' => 'overview',
        'section' => 'summary-of-records',
        'data' => $sor_data
    ));

    $dbh=null;
    drupal_exit();
});

$app->get('/data/overview/total-records-over-time', function () use ($app) {
    $trot_data = array();
    $dbh = OpenDB('GOMRI_RO');

    $identifications = array( 'label' => 'Identified', 'data' => array() );
    $SQL = "SELECT row_number() OVER(ORDER BY dataset_uid) AS count, dataset_uid::INT8 * 1000 AS ts FROM datasets WHERE status > 1;";
    $sth = $dbh->prepare($SQL);
    $sth->execute();
    $rows = $sth->fetchAll();
    foreach ($rows as $row) {
        $identifications['data'][] = array($row['ts'],$row['count']);
    }
    $identifications['data'][] = array(time()*1000,$rows[count($rows)-1]['count']);
    $trot_data[] = $identifications;

    $registrations = array( 'label' => 'Registered', 'data' => array() );
    $SQL = "SELECT row_number() OVER(ORDER BY submittimestamp) AS count,
                   extract(epoch from submittimestamp) * 1000 AS ts
            FROM registry
            WHERE registry_id IN (SELECT min_id FROM (SELECT SUBSTRING(registry_id FROM 1 FOR 16) AS udi,
                                         MIN(registry_id) AS min_id
                                  FROM registry
                                  WHERE
                                      registry_id NOT LIKE '00%'
                                      AND registry_id NOT LIKE 'F%'
                                      AND url_data IS NOT NULL
                                      AND registry_id NOT LIKE 'F%'
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

    $metadata = array( 'label' => 'Available', 'data' => array() );
    $SQL = "SELECT row_number() OVER(ORDER BY submittimestamp) AS count,
                   extract(epoch from submittimestamp) * 1000 AS ts
            FROM registry
            WHERE registry_id IN (SELECT min_id FROM (SELECT SUBSTRING(registry_id FROM 1 FOR 16) AS udi,
                                         MIN(registry_id) AS min_id
                                  FROM registry
                                  WHERE
                                        registry_id NOT LIKE '00%'
                                        AND registry_id NOT LIKE 'F%'
                                        AND metadata_status = 'Accepted'
                                        AND access_status = 'None'
                                        AND url_data IS NOT null
                                        AND (dataset_download_status = 'Completed' OR dataset_download_status = 'RemotelyHosted')
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

$app->get('/data/overview/dataset-size-ranges', function () use ($app) {
    $size_ranges_data = array();
    $dbh = OpenDB('GOMRI_RO');
    $i=0;
    foreach ($GLOBALS['size_ranges'] AS $range) {
        $SQL = "SELECT COUNT(*)
                FROM registry_view
                WHERE
                    registry_id NOT LIKE 'F%'
                    AND dataset_download_size $range[range0]";
        if (array_key_exists('range1',$range)) {
            $SQL .= " AND dataset_download_size $range[range1]";
        }
        $sth = $dbh->prepare($SQL);
        $sth->execute();
        $count = $sth->fetchColumn();
        $size_ranges_data[] = array("label" => "$range[label]", "data" => array(array($i*0.971+0.171,$count)), "bars" => array("barWidth" => 0.8));
        $i++;
    }
    print json_encode(array(
        'page' => 'overview',
        'section' => 'dataset-size-ranges',
        'data' => $size_ranges_data
    ));
    drupal_exit();
});

$app->run();
