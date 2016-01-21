<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpKernel\AppKernel;

umask(0000);

$loader = require __DIR__ . '/../app/autoload.php';

Debug::enable();

require_once __DIR__ . '/../app/AppKernel.php';

$kernel = new AppKernel('drupal_dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);

if (get_class($response) == 'Symfony\Component\HttpFoundation\BinaryFileResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    drupal_exit();
}

if (preg_match('/^Pelagos\\\\/', $request->attributes->get('_controller'))) {
    $content = $response->getContent();
    $newContent = preg_replace('/<\/body>/', '', $content);
    $response->setContent($newContent);
    $response->sendContent();
    $kernel->terminate($request, $response);
} else {
    $response->send();
    $kernel->terminate($request, $response);
    drupal_exit();
}
