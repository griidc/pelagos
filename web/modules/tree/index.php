<?php
// @codingStandardsIgnoreFile

require_once __DIR__.'/../../../vendor/autoload.php';

$GLOBALS['pelagos']['title'] = 'Organizational Tree';

# load global pelagos config
$GLOBALS['config'] = parse_ini_file('/etc/opt/pelagos.ini', true);

# load Common library from global share
require_once($GLOBALS['config']['paths']['share'].'/php/Common.php');

# check for local config file
if (file_exists('config.ini')) {
    # merge local config with global config
    $GLOBALS['config'] = configMerge($GLOBALS['config'], parse_ini_file('config.ini', true));
}

# load library info
$GLOBALS['libraries'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/libraries.ini',true);

# load database connection info
$GLOBALS['db'] = parse_ini_file($GLOBALS['config']['paths']['conf'].'/db.ini',true);

# add pelagos/share/php to the include path
#set_include_path(get_include_path() . PATH_SEPARATOR . $GLOBALS['config']['paths']['share'] . '/php');
set_include_path('../../../share/php' . PATH_SEPARATOR . get_include_path());

# load Drupal functions
require_once 'drupal.php';
# load includes file dumper
require_once 'dumpIncludesFile.php';
# load RIS query functions
require_once 'rpis.php';
# load dataset query functions
require_once 'datasets.php';
# load database utilities
require_once 'DBUtils.php';

# load tree library
require_once 'lib/tree.php';

# initialize Slim
$app = new \Slim\Slim(array('view' => new \Slim\Views\Twig()));

# add custom Twig extensions
$app->view->parserExtensions = array( new \Pelagos\TwigExtensions() );

# define baseUrl for use in templates
$app->hook('slim.before', function () use ($app) {
    $env = $app->environment();
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $app->view()->appendData(array('baseUrl' => "$protocol$env[SERVER_NAME]$env[SCRIPT_NAME]"));
});

$app->get('/includes/:file', 'dumpIncludesFile')->conditions(array('file' => '.+'));

$app->get('/js/:name.js', function ($name) use ($app) {
    $stash['tree'] = $GLOBALS['config']['tree'];
    if ($app->request()->get('tree')) {
        $stash['tree'] = array_merge($stash['tree'],json_decode($app->request()->get('tree'),true));
    }
    header('Content-type: text/javascript');
    $app->render("js/$name.js",$stash);
    exit;
});

$app->get('/', function () use ($app) {
    $env = $app->environment();
    drupal_add_js("$env[SCRIPT_NAME]/js/tree.js",array('type'=>'external'));
    return $app->render('index.html');
});

$app->get('/json/:type.json', function ($type) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    switch ($type) {
        case 'in':
            $institutions = getInstitutionDetails($RIS_DBH);

            if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
                array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
                $dataset_filters = getDatasetFilters($stash['tree']);
                $GOMRI_DBH = openDB('GOMRI_RO');
                $stash['institutions'] = array();
                foreach ($institutions as $inst) {
                    $data_count = 0;
                    $projectFilter = array("institutionId=$inst[ID]");
                    if (isset($GLOBALS['config']['exclude']['projects'])) {
                        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                            $projectFilter[] = "projectId!=$exclude";
                        }
                    }
                    $projects = getProjectDetails($RIS_DBH,$projectFilter);
                    foreach ($projects as $project) {
                        $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
                    }
                    if ($data_count > 0) {
                        if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                            $inst['dataset_count'] = $data_count;
                        }
                        array_push($stash['institutions'],$inst);
                    }
                }
            }
            else {
                $stash['institutions'] = $institutions;
            }

            $app->render('json/institutions.json',$stash);
            break;
        case 're':
            $stash['letters'] = getPeopleLI($RIS_DBH);
            $app->render('json/letters.json',$stash);
            break;
        case 'ra':
            $fundFilter = array('fundId>6','fundId<100');
            if (isset($GLOBALS['config']['exclude']['funds'])) {
                foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                    $fundFilter[] = "fundId!=$exclude";
                }
            }

            $RFPS = getFundingSources($RIS_DBH,$fundFilter);

            if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
                array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
                $dataset_filters = getDatasetFilters($stash['tree']);
                $GOMRI_DBH = openDB('GOMRI_RO');

                $fundFilter = array('fundId>0','fundId<7');
                if (isset($GLOBALS['config']['exclude']['funds'])) {
                    foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                        $fundFilter[] = "fundId!=$exclude";
                    }
                }
                $YR1S = getFundingSources($RIS_DBH,$fundFilter);

                $data_count = 0;
                foreach ($YR1S as $YR1) {
                    $projectFilter = array("fundSrc=$YR1[ID]");
                    if (isset($GLOBALS['config']['exclude']['projects'])) {
                        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                            $projectFilter[] = "projectId!=$exclude";
                        }
                    }
                    $projects = getProjectDetails($RIS_DBH,$projectFilter);
                    foreach ($projects as $project) {
                        $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
                    }
                }
                if ($data_count > 0) {
                    if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                        $stash['YR1']['dataset_count'] = $data_count;
                    }
                }
                else {
                    $stash['YR1']['hide'] = true;
                }

                $stash['RFPS'] = array();
                foreach ($RFPS as $RFP) {
                    $data_count = 0;
                    $projectFilter = array("fundSrc=$RFP[ID]");
                    if (isset($GLOBALS['config']['exclude']['projects'])) {
                        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                            $projectFilter[] = "projectId!=$exclude";
                        }
                    }
                    $projects = getProjectDetails($RIS_DBH,$projectFilter);
                    foreach ($projects as $project) {
                        $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
                    }
                    if ($data_count > 0) {
                        if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                            $RFP['dataset_count'] = $data_count;
                        }
                        array_push($stash['RFPS'],$RFP);
                    }
                }

                $data_count = count_identified_datasets($GOMRI_DBH,array('funding_envelope>=700'),$stash['tree']['filter']);
                if ($data_count > 0) {
                    if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                        $stash['other_sources']['dataset_count'] = $data_count;
                    }
                }
                else {
                    $stash['tree']['show_other_sources'] = false;
                }
            }
            else {
                $stash['RFPS'] = $RFPS;
            }
            if (array_key_exists('yr1_top', $GLOBALS['config']['tree'])
                and $GLOBALS['config']['tree']['yr1_top']) {
                $stash['YR1']['top'] = true;
            }

            $app->render('json/research_awards.json',$stash);
            break;
    }
    exit;
});

$app->get('/json/ra/YR1.json', function () use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $fundFilter = array('fundId>0','fundId<7');
    if (isset($GLOBALS['config']['exclude']['funds'])) {
        foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
            $fundFilter[] = "fundId!=$exclude";
        }
    }
    $YR1S = getFundingSources($RIS_DBH,$fundFilter);

    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['YR1'] = array();
        foreach ($YR1S as $YR1) {
            $data_count = 0;
            $projectFilter = array("fundSrc=$YR1[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails($RIS_DBH,$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            }
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $YR1['dataset_count'] = $data_count;
                }
                array_push($stash['YR1'],$YR1);
            }
        }
    }
    else {
        $stash['YR1'] = $YR1S;
    }

    $app->render('json/YR1.json',$stash);
    exit;
});

$app->get('/json/ra/otherSources.json', function () use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $fundFilter = array('fundId>99');
    if (isset($GLOBALS['config']['exclude']['funds'])) {
        foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
            $fundFilter[] = "fundId!=$exclude";
        }
    }
    $others = getFundingSources($RIS_DBH,$fundFilter);

    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['otherSources'] = array();
        foreach ($others as $other) {
            $data_count = 0;
            $projectFilter = array("fundSrc=$other[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails($RIS_DBH,$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            }
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $other['dataset_count'] = $data_count;
                }
                array_push($stash['otherSources'],$other);
            }
        }
    }
    else {
        $stash['otherSources'] = $others;
    }

    $app->render('json/otherSources.json',$stash);
    exit;
});

$app->get('/json/re/:letter.json', function ($letter) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $people = getPeopleList($RIS_DBH,array("lastName=$letter%"));

    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['people'] = array();
        foreach ($people as $person) {
            $data_count = 0;
            $projectFilter = array("peopleId=$person[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails($RIS_DBH,$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            }
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $person['dataset_count'] = $data_count;
                }
                array_push($stash['people'],$person);
            }
        }
    }
    else {
        $stash['people'] = $people;
    }

    $app->render('json/researchers.json',$stash);
    exit;
});

$app->get('/json/in/:letter.json', function ($letter) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $institutions = getInstitutionDetails($RIS_DBH,array("name=$letter%"));

    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['institutions'] = array();
        foreach ($institutions as $inst) {
            $data_count = 0;
            $projectFilter = array("institutionId=$inst[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails($RIS_DBH,$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            }
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $inst['dataset_count'] = $data_count;
                }
                array_push($stash['institutions'],$inst);
            }
        }
    }
    else {
        $stash['institutions'] = $institutions;
    }

    $app->render('json/institutions.json',$stash);
    exit;
});

$app->get('/json/:type/projects/fundSrc/:fundSrc.json', function ($type,$fundSrc) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $projectFilter = array("fundSrc=$fundSrc");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $projects = getProjectDetails($RIS_DBH,$projectFilter);

    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['projects'] = array();
        foreach ($projects as $project) {
            $data_count = count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $project['dataset_count'] = $data_count;
                }
                array_push($stash['projects'],$project);
            }
        }
    }
    else {
        $stash['projects'] = $projects;
    }

    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/json/:type/projects/peopleId/:peopleId.json', function ($type,$peopleId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $projectFilter = array("peopleId=$peopleId");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $projects = getProjectDetails($RIS_DBH,$projectFilter);


    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['projects'] = array();
        foreach ($projects as $project) {
            $data_count = count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $project['dataset_count'] = $data_count;
                }
                array_push($stash['projects'],$project);
            }
        }
    }
    else {
        $stash['projects'] = $projects;
    }


    $stash['peopleId'] = $peopleId;
    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/json/:type/projects/institutionId/:institutionId.json', function ($type,$institutionId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $projectFilter = array("institutionId=$institutionId");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $projects = getProjectDetails($RIS_DBH,$projectFilter);

    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['projects'] = array();
        foreach ($projects as $project) {
            $data_count = count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$project[ID]")),$stash['tree']['filter']);
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $project['dataset_count'] = $data_count;
                }
                array_push($stash['projects'],$project);
            }
        }
    }
    else {
        $stash['projects'] = $projects;
    }

    $app->render('json/projects.json',$stash);
    exit;
});

$app->get('/json/:type/tasks/projectId/:projectId.json', function ($type,$projectId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $filters = array("projectID=$projectId");
    $tasks = getTaskDetails($RIS_DBH,$filters);
    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['tasks'] = array();
        foreach ($tasks as $task) {
            $data_count = count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("taskId=$task[ID]")),$stash['tree']['filter']);
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $task['dataset_count'] = $data_count;
                }
                array_push($stash['tasks'],$task);
            }
        }
    }
    else {
        $stash['tasks'] = $tasks;
    }
    $app->render('json/tasks.json',$stash);
    exit;
});

$app->get('/json/:type/tasks/projectId/peopleId/:projectId/:peopleId.json', function ($type,$projectId,$peopleId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $RIS_DBH = openDB('RIS_RO');
    $filters = array("projectID=$projectId","peopleId=$peopleId");
    $tasks = getTaskDetails($RIS_DBH,$filters);
    if (array_key_exists('filter',$stash['tree']) and !empty($stash['tree']['filter']) or
        array_key_exists('geo_filter',$stash['tree']) and !empty($stash['tree']['geo_filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $GOMRI_DBH = openDB('GOMRI_RO');
        $stash['tasks'] = array();
        foreach ($tasks as $task) {
            $data_count = count_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("taskId=$task[ID]")),$stash['tree']['filter']);
            if ($data_count > 0) {
                if ($GLOBALS['config']['tree']['show_counts'] == 1) {
                    $task['dataset_count'] = $data_count;
                }
                array_push($stash['tasks'],$task);
            }
        }
    }
    else {
        $stash['tasks'] = $tasks;
    }
    $app->render('json/tasks.json',$stash);
    exit;
});

$app->get('/json/:type/datasets/projectId/:projectId.json', function ($type,$projectId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $dataset_filters = getDatasetFilters($stash['tree']);
    $GOMRI_DBH = openDB('GOMRI_RO');
    $stash['datasets'] = get_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("projectId=$projectId")));
    $app->render('json/datasets.json',$stash);
    exit;
});

$app->get('/json/:type/datasets/taskId/:taskId.json', function ($type,$taskId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $dataset_filters = getDatasetFilters($stash['tree']);
    $GOMRI_DBH = openDB('GOMRI_RO');
    $stash['datasets'] = get_identified_datasets($GOMRI_DBH,array_merge($dataset_filters,array("taskId=$taskId")));
    $app->render('json/datasets.json',$stash);
    exit;
});

$app->run();
