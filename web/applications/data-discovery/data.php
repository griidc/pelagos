<?php

require_once '/usr/local/share/Slim/Slim/Slim.php';

$app = new Slim();

$app->get('/(:filter)', function ($filter = null) use ($app) {
    $env = $app->environment();
    if (!is_null($filter)) {
        header("Location: /data-discovery?filter=$filter");
        exit;
    }
});

$app->run();

?>
