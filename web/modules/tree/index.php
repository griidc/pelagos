<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';
require_once '/usr/local/share/Slim-Extras/Views/TwigView.php';

require_once '/usr/local/share/GRIIDC/php/drupal.php';
require_once '/usr/local/share/GRIIDC/php/dumpIncludesFile.php';
require_once '/usr/local/share/GRIIDC/php/rpis.php';
require_once '/usr/local/share/GRIIDC/php/datasets.php';

require_once 'lib/tree.php';

$GLOBALS['config'] = parse_ini_file('config.ini',true);

TwigView::$twigDirectory = $GLOBALS['config']['TwigView']['twigDirectory'];

require_once 'lib/Twig_Extensions_GRIIDC.php';

$app = new Slim(array(
                        'view' => new TwigView,
                        'debug' => true,
                        'log.level' => Slim_Log::DEBUG,
                        'log.enabled' => true
                     ));

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
    switch ($type) {
        case 'in':
            $institutions = getInstitutionDetails(getDBH('RPIS'));

            if (isset($stash['tree']['filter'])) {
                $dataset_filters = getDatasetFilters($stash['tree']);
                $stash['institutions'] = array();
                foreach ($institutions as $inst) {
                    $data_count = 0;
                    $projectFilter = array("institutionId=$inst[ID]");
                    if (isset($GLOBALS['config']['exclude']['projects'])) {
                        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                            $projectFilter[] = "projectId!=$exclude";
                        }
                    }
                    $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);
                    foreach ($projects as $project) {
                        $data_count += count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
                    }
                    if ($data_count > 0) {
                        $inst['dataset_count'] = $data_count;
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
            $stash['letters'] = getPeopleLI(getDBH('RPIS'));
            $app->render('json/letters.json',$stash);
            break;
        case 'ra':
            $fundFilter = array('fundId>6');
            if (isset($GLOBALS['config']['exclude']['funds'])) {
                foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                    $fundFilter[] = "fundId!=$exclude";
                }
            }

            $RFPS = getFundingSources(getDBH('RPIS'),$fundFilter);

            if (isset($stash['tree']['filter'])) {
                $dataset_filters = getDatasetFilters($stash['tree']);

                $fundFilter = array('fundId>0','fundId<7');
                if (isset($GLOBALS['config']['exclude']['funds'])) {
                    foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
                        $fundFilter[] = "fundId!=$exclude";
                    }
                }
                $YR1S = getFundingSources(getDBH('RPIS'),$fundFilter);

                $data_count = 0;
                foreach ($YR1S as $YR1) {
                    $projectFilter = array("fundSrc=$YR1[ID]");
                    if (isset($GLOBALS['config']['exclude']['projects'])) {
                        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                            $projectFilter[] = "projectId!=$exclude";
                        }
                    }
                    $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);
                    foreach ($projects as $project) {
                        $data_count += count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
                    }
                }
                if ($data_count > 0) {
                    $stash['YR1']['dataset_count'] = $data_count;
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
                    $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);
                    foreach ($projects as $project) {
                        $data_count += count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
                    }
                    if ($data_count > 0) {
                        $RFP['dataset_count'] = $data_count;
                        array_push($stash['RFPS'],$RFP);
                    }
                }

                $filters = array();
                if (isset($stash['tree']['filter'])) {
                    $filters[] = 'filter=%' . $stash['tree']['filter'] . '%';
                }
                $data_count = count_registered_datasets(getDBH('GOMRI'),array_merge($filters,array('registry_id=00%','dataset_download_status=done')));
                if ($data_count > 0) {
                    $stash['other_sources']['dataset_count'] = $data_count;
                }
                else {
                    $stash['tree']['show_other_sources'] = false;
                }
            }
            else {
                $stash['RFPS'] = $RFPS;
            }
            $app->render('json/research_awards.json',$stash);
            break;
    }
    exit;
});

$app->get('/json/ra/YR1.json', function () use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $fundFilter = array('fundId>0','fundId<7');
    if (isset($GLOBALS['config']['exclude']['funds'])) {
        foreach ($GLOBALS['config']['exclude']['funds'] as $exclude) {
            $fundFilter[] = "fundId!=$exclude";
        }
    }
    $YR1S = getFundingSources(getDBH('RPIS'),$fundFilter);

    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['YR1'] = array();
        foreach ($YR1S as $YR1) {
            $data_count = 0;
            $projectFilter = array("fundSrc=$YR1[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
            }
            if ($data_count > 0) {
                $YR1['dataset_count'] = $data_count;
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
    $stash['otherSources'] = array(array('ID' => '000', 'Name' => 'National Oceanographic Data Center',));
    if (isset($stash['tree']['filter'])) {
        $filters = array();
        if (isset($stash['tree']['filter'])) {
            $filters[] = 'filter=%' . $stash['tree']['filter'] . '%';
        }
        $stash['otherSources'][0]['dataset_count'] = count_registered_datasets(getDBH('GOMRI'),array_merge($filters,array('registry_id=00.x000%','dataset_download_status=done')));
    }
    $app->render('json/otherSources.json',$stash);
    exit;
});

$app->get('/json/re/:letter.json', function ($letter) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $people = getPeopleDetails(getDBH('RPIS'),array("lastName=$letter%"));

    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['people'] = array();
        foreach ($people as $person) {
            $data_count = 0;
            $projectFilter = array("peopleId=$person[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
            }
            if ($data_count > 0) {
                $person['dataset_count'] = $data_count;
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
    $institutions = getInstitutionDetails(getDBH('RPIS'),array("name=$letter%"));

    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['institutions'] = array();
        foreach ($institutions as $inst) {
            $data_count = 0;
            $projectFilter = array("institutionId=$inst[ID]");
            if (isset($GLOBALS['config']['exclude']['projects'])) {
                foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
                    $projectFilter[] = "projectId!=$exclude";
                }
            }
            $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);
            foreach ($projects as $project) {
                $data_count += count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
            }
            if ($data_count > 0) {
                $inst['dataset_count'] = $data_count;
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
    $projectFilter = array("fundSrc=$fundSrc");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);

    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['projects'] = array();
        foreach ($projects as $project) {
            $data_count = count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
            if ($data_count > 0) {
                $project['dataset_count'] = $data_count;
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
    $projectFilter = array("peopleId=$peopleId");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);


    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['projects'] = array();
        foreach ($projects as $project) {
            $data_count = count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
            if ($data_count > 0) {
                $project['dataset_count'] = $data_count;
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
    $projectFilter = array("institutionId=$institutionId");
    if (isset($GLOBALS['config']['exclude']['projects'])) {
        foreach ($GLOBALS['config']['exclude']['projects'] as $exclude) {
            $projectFilter[] = "projectId!=$exclude";
        }
    }
    $projects = getProjectDetails(getDBH('RPIS'),$projectFilter);

    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['projects'] = array();
        foreach ($projects as $project) {
            $data_count = count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$project[ID]")));
            if ($data_count > 0) {
                $project['dataset_count'] = $data_count;
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
    $filters = array("projectID=$projectId");
    $tasks = getTaskDetails(getDBH('RPIS'),$filters);
    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['tasks'] = array();
        foreach ($tasks as $task) {
            $data_count = count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("taskId=$task[ID]")));
            if ($data_count > 0) {
                $task['dataset_count'] = $data_count;
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
    $filters = array("projectID=$projectId","peopleId=$peopleId");
    $tasks = getTaskDetails(getDBH('RPIS'),$filters);
    if (isset($stash['tree']['filter'])) {
        $dataset_filters = getDatasetFilters($stash['tree']);
        $stash['tasks'] = array();
        foreach ($tasks as $task) {
            $data_count = count_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("taskId=$task[ID]")));
            if ($data_count > 0) {
                $task['dataset_count'] = $data_count;
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
    $stash['datasets'] = get_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("projectId=$projectId")));
    $app->render('json/datasets.json',$stash);
    exit;
});

$app->get('/json/:type/datasets/taskId/:taskId.json', function ($type,$taskId) use ($app) {
    $stash['tree'] = array_merge($GLOBALS['config']['tree'],json_decode($app->request()->get('tree'),true));
    $dataset_filters = getDatasetFilters($stash['tree']);
    $stash['datasets'] = get_identified_datasets(getDBH('GOMRI'),array_merge($dataset_filters,array("taskId=$taskId")));
    $app->render('json/datasets.json',$stash);
    exit;
});

$app->run();

?>
