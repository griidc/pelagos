<?php
// @codingStandardsIgnoreFile

require_once __DIR__ . '/../../../vendor/autoload.php';

$comp = new \Pelagos\Component();

$slim = new \Slim\Slim(
    array(
        'view' => new \Slim\Views\Twig(),
    )
);

$slim->get('/', function () {
    $GLOBALS['pelagos']['title'] = 'Citation Service';
    print 'This is a citation service...';
});

$slim->get('/publication(/)', function () use ($comp) {
    header('Content-Type:application/json');
    $status = new \Pelagos\HTTPStatus(400, 'No DOI provided.');
    http_response_code($status->getCode());
    print $status->asJSON();
    $comp->quit();
});

$slim->get('/publication/:doi+', function ($doi) use ($comp) {
    header('Content-Type:application/json');
    $pub = new \Pelagos\Entity\Publication(join('/', $doi));
    $citation = $pub->getCitation();
    if ($citation === null) {
        $status = $pub->pullCitation('apa');
        if ($status->getCode() == 200) {
            print $pub->getCitation()->asJSON();
        } else {
            http_response_code($status->getCode());
            print $status->asJSON();
        }
        $comp->quit();
    }
    print $citation->asJSON();
    $comp->quit();
});

/**
 *  router for /dataset/udi
 * where udi is a real udi in the form Y1.xnnn.nnn:nnnn.
 * Get a registered dataset for the udi provided
 */
$slim->get('/dataset/:udi', function ($udi) use ($comp) {
    header('Content-Type:application/json');
    require_once './lib/Dataset.php';
    $ds = new \Citation\Dataset();
    try {
        $citation = $ds->getRegisteredDatasetCitation($udi);
        print $citation->asJSON();
    } catch (\Citation\InvalidUdiException $e) {
        $status = new \Pelagos\HTTPStatus(400, $e->getMessage());
        http_response_code($status->getCode());
        print $status->asJSON();
    } catch (\Citation\NoRegisteredDatasetException $e) {
        $status = new \Pelagos\HTTPStatus(400, $e->getMessage());
        http_response_code($status->getCode());
        print $status->asJSON();
    }
    $comp->quit();
});

/**
 *  router for /dataset/udi
 * where udi is a real udi in the form Y1.xnnn.nnn:nnnn.
 * Get a registered dataset for the udi provided
 */
$slim->get('/dataset(/)', function () use ($comp) {
    header('Content-Type:application/json');
    http_response_code(400);
    $status = new \Pelagos\HTTPStatus(400, 'Error - No UDI provided.');
    print $status->asJSON();
    $comp->quit();
});

$slim->run();
