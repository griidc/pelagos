<?php

/**
 * Depending on the response type and the controller, conditionally terminate Drupal.
 */

if (get_class($response) == 'Symfony\Component\HttpFoundation\BinaryFileResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    drupal_exit();
}

if (preg_match('/^Pelagos\\\\Bundle\\\\AppBundle\\\\Controller\\\\Api\\\\/', $request->attributes->get('_controller'))) {
    $response->send();
    $kernel->terminate($request, $response);
    drupal_exit();
}

if (in_array(
    $request->attributes->get('_route'),
    array(
        'pelagos_app_ui_datasetmonitoring_allresearchgroup',
        'pelagos_app_ui_datasetmonitoring_researchgroup',
        'pelagos_app_ui_datasetmonitoring_researcher',
        'pelagos_app_ui_datasetmonitoring_datasetdetails',
        'pelagos_app_ui_dataland_metadata',
        'pelagos_app_ui_dataland_download',
        'pelagos_app_ui_stats_getdatasetsizeranges',
        'pelagos_app_ui_stats_getdatasetovertime',
        'pelagos_app_ui_stats_getsummaryofrecords',
        'pelagos_app_ui_datadiscovery_datasets',
        'pelagos_app_ui_datadiscovery_showdetails',
        'pelagos_app_ui_mdapp_downloadmetadatafromdb',
        'pelagos_app_ui_mdapp_getlog',
        'pelagos_app_download_default',
        'pelagos_app_download_http',
        'pelagos_app_download_gridftp',
    )
)) {
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
    exit();
}
