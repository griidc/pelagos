<?php

/**
 * The front controller for the Drupal development environment (drupal_dev).
 */

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
$response->send();
$kernel->terminate($request, $response);
