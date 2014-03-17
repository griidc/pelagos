<?php

$GLOBALS['libraries'] = parse_ini_file('/etc/griidc/libraries.ini',true);

require_once $GLOBALS['libraries']['Slim2']['include'];
\Slim\Slim::registerAutoloader();
require_once $GLOBALS['libraries']['Slim-Views']['include_Twig'];

$app = new \Slim\Slim(array(
                        'view' => new \Slim\Views\Twig(),
                        'debug' => true,
                        'log.level' => \Slim\Log::DEBUG,
                        'log.enabled' => true
                     ));

$app->view->parserDirectory = $GLOBALS['libraries']['Twig']['directory'];

$app->get('/:schema_name', function ($schema_name) use ($app) {

    $stash['schema_field'] = true;
    $stash['validated'] = false;

    if (isset($schema_name) and $schema_name != '') {
        switch ($schema_name) {
            case 'dataset_information':
                $schema = '/var/www/schema/dataset_information/dataset_information.xsd';
                $stash['schema'] = 'GRIIDC Dataset Information';
                $stash['xsdurl'] = 'http://data.gulfresearchinitiative.org/schema/dataset_information/dataset_information.xsd';
                break;
            case 'dataset_registry':
                $schema = '/var/www/schema/dataset_registry/dataset_registry.xsd';
                $stash['schema'] = 'GRIIDC Dataset Registry';
                $stash['xsdurl'] = 'http://data.gulfresearchinitiative.org/schema/dataset_registry/dataset_registry.xsd';
                break;
        }
        $stash['schema_field'] = false;
    }
    elseif ($app->request()->get('xsdurl')) {
        $schema = $app->request()->get('xsdurl');
        $stash['xsdurl'] = $app->request()->get('xsdurl');
    }

    if ($app->request()->get('xmlurl')) {
        if (isset($schema)) {
            libxml_use_internal_errors(true);
            $domdoc = new DOMDocument();
            $domdoc->load($app->request()->get('xmlurl'));
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

    $stash['xmlurl'] = $app->request()->get('xmlurl');

    return $app->render('html/index.html',$stash);
})->conditions(array('schema_name' => '.*'));

$app->run();

?>
