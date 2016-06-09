<?php

/**
 * The front controller for the Drupal production environment (drupal_prod).
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\AppKernel;

umask(0002);

$loader = require __DIR__ . '/../app/autoload.php';

require_once __DIR__ . '/../app/AppKernel.php';
include_once __DIR__ . '/../var/bootstrap.php.cache';

$kernel = new AppKernel('drupal_prod', false);
$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);

require_once __DIR__ . '/../app/drupal_send_and_terminate.php';
