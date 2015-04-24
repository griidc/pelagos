<?php

$comp = new \Pelagos\Component();

require_once "ldap.php";
require_once "lib/PLinker/Storage.php";
require_once "lib/PLinker/Publink.php";

global $quit;
$quit = false;

$comp->slim->get('/', function () use ($comp) {
    $GLOBALS['pelagos']['title'] = 'Publink Service';
    print 'This service creates associations between datasets and publications.';
});

$comp->slim->map('/', function () use ($comp) {
    global $quit;
    $quit = true;
    $HTTPStatus = new \Pelagos\HTTPStatus(400, 'No parameters provided');
    $comp->slim->response->headers->set('Content-Type', 'application/json');
    $comp->slim->response->status($HTTPStatus->code);
    $comp->slim->response->setBody($HTTPStatus->asJSON());
    return;
})->via('LINK', 'DELETE');

$comp->slim->map('/:udi(/)', function ($udi) use ($comp) {
    global $quit;
    $quit = true;
    $HTTPStatus = new \Pelagos\HTTPStatus(400, 'No DOI provided');
    $comp->slim->response->headers->set('Content-Type', 'application/json');
    $comp->slim->response->status($HTTPStatus->code);
    $comp->slim->response->setBody($HTTPStatus->asJSON());
    return;
})->via('LINK', 'DELETE');

$comp->slim->map('/:udi/:doi+', function ($udi, $doiArray) use ($comp) {
    global $user;
    global $quit;
    if (!isset($user->name)) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(401, 'Login Required to use this feature.');
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    // check for valid format of UDI
    if (preg_match('/(?:Y1|R[1-9])\.x\d{3}\.\d{3}:\d{4}/', $udi) == 0) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid UDI format");
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    $doi = join('/', $doiArray);
    // check for valid format of doi
    if (preg_match('/^10\..*\/.*$/', $doi) == 0) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid doi format");
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    $Publink = new \PLinker\Publink;
    try {
        $Publink->createLink($udi, $doi, $user->name);
    } catch (\Exception $ee) {
        $quit = true;
        $code = 0;
        if ($ee->getMessage() == 'Record Does not exist in publication table') {
            $code = 417;
        } elseif ($ee->getMessage() == 'A link has already been established between '
        . 'the given dataset and publication.') {
            $code = 403;
        }
        $HTTPStatus = new \Pelagos\HTTPStatus($code, $ee->getMessage());
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }
    // if successful
    $HTTPStatus = new \Pelagos\HTTPStatus(200, "A Link has been successfully created "
    . "between dataset $udi and publication $doi.");
    $comp->slim->response->headers->set('Content-Type', 'application/json');
    $comp->slim->response->status($HTTPStatus->code);
    $comp->slim->response->setBody($HTTPStatus->asJSON());

    $quit = true;
})->via('LINK');

$comp->slim->map('/:udi/:doiShoulder/:doiBody(/)', function ($udi, $doiShoulder, $doiBody) use ($comp) {
    global $user;
    global $quit;
    if (!isset($user->name)) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(401, 'Login Required to use this feature.');
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    // check for valid format of UDI
    if (preg_match('/(?:Y1|R[1-9])\.x\d{3}\.\d{3}:\d{4}/', $udi) == 0) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid UDI format");
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    $doi = $doiShoulder.'/'.$doiBody;
    // check for valid format of doi
    if (preg_match('/^10\..*\/.*$/', $doi) == 0) {
        $quit = true;
        $HTTPStatus = new \Pelagos\HTTPStatus(400, "Invalid doi format");
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }

    $Publink = new \PLinker\Publink;
    try {
        $Publink->removeLink($udi, $doi, $user->name);
    } catch (\PDOException $ee) {
        $quit = true;
        $code = 500;
        $HTTPStatus = new \Pelagos\HTTPStatus($code, $ee->getMessage());
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    } catch (\Exception $ee) {
        $quit = true;
        $code = 0;
        if ($ee->getMessage() == 'A link between the given doi and UDI does not exist.') {
            $code = 417;
        }
        $HTTPStatus = new \Pelagos\HTTPStatus($code, $ee->getMessage());
        $comp->slim->response->headers->set('Content-Type', 'application/json');
        $comp->slim->response->status($HTTPStatus->code);
        $comp->slim->response->setBody($HTTPStatus->asJSON());
        return;
    }
    // if successful
    $HTTPStatus = new \Pelagos\HTTPStatus(200, "The link between dataset "
        .$udi." and publication $doi has been removed.");
    $comp->slim->response->headers->set('Content-Type', 'application/json');
    $comp->slim->response->status($HTTPStatus->code);
    $comp->slim->response->setBody($HTTPStatus->asJSON());

    $quit = true;
})->via('DELETE');

$comp->slim->run();

if ($quit) {
    $comp->quit();
}
