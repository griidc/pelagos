<?php

$GLOBALS['pelagos']['title'] = 'XML Validator';

$GLOBALS['libraries'] = parse_ini_file('/etc/opt/pelagos/libraries.ini',true);

require_once $GLOBALS['libraries']['Slim2']['include'];
\Slim\Slim::registerAutoloader();
require_once $GLOBALS['libraries']['Slim-Views']['include_Twig'];
# load Twig
require_once 'Twig/Autoloader.php';

$app = new \Slim\Slim(array(
                        'view' => new \Slim\Views\Twig(),
                        'debug' => true,
                        'log.level' => \Slim\Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->view->parserDirectory = $GLOBALS['libraries']['Twig']['directory'];

$app->get('/', function () use ($app) {

    $stash['validated'] = false;

    if ($app->request()->get('schema')) {
        $stash['schema'] = $app->request()->get('schema');
        switch ($app->request()->get('schema')) {
            case 'dataset_information':
                $schema = '/var/www/schema/dataset_information/dataset_information.xsd';
                $stash['xsd_url'] = 'http://data.gulfresearchinitiative.org/schema/dataset_information/dataset_information.xsd';
                break;
            case 'dataset_registry':
                $schema = '/var/www/schema/dataset_registry/dataset_registry.xsd';
                $stash['xsd_url'] = 'http://data.gulfresearchinitiative.org/schema/dataset_registry/dataset_registry.xsd';
                break;
            case 'url':
                if ($app->request()->get('xsd_url')) {
                    $schema = $app->request()->get('xsd_url');
                    $stash['xsd_url'] = $app->request()->get('xsd_url');
                }
                break;
        }
    }

    if ($app->request()->get('xml_url')) {
        if (isset($schema)) {
            libxml_use_internal_errors(true);
            $domdoc = new DOMDocument();
            $domdoc->load($app->request()->get('xml_url'));
            if (!$domdoc->schemaValidate($schema)) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                for ($i=0; $i<sizeof($errors); $i++) {
                    switch ($errors[$i]->level) {
                        case LIBXML_ERR_WARNING:
                            $errors[$i]->levelName = "Warning";
                            break;
                        case LIBXML_ERR_ERROR:
                            $errors[$i]->levelName = "Error";
                            break;
                        case LIBXML_ERR_FATAL:
                            $errors[$i]->levelName = "Fatal Error";
                            break;
                    }
                }
                $stash['errors'] = $errors;
            }
            $stash['validated'] = true;
        }
    }

    $stash['xml_url'] = $app->request()->get('xml_url');

    return $app->render('html/index.html',$stash);
});

$app->run();
