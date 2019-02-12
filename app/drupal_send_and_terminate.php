<?php

/**
 * Depending on the response type and the controller, conditionally terminate Drupal.
 */

if (get_class($response) == 'Symfony\Component\HttpFoundation\BinaryFileResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
}

if (get_class($response) == 'Symfony\Component\HttpFoundation\JsonResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
}

if (get_class($response) == 'Symfony\Component\HttpFoundation\StreamedResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
}

if (get_class($response) == 'Symfony\Component\HttpFoundation\RedirectResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
}

if (get_class($response) == 'Pelagos\Response\TerminateResponse') {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
}

if (preg_match(
    '/^Pelagos\\\\Bundle\\\\AppBundle\\\\Controller\\\\Api\\\\/',
    $request->attributes->get('_controller')
)
    ) {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
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
        'pelagos_app_ui_datadiscovery_count',
        'pelagos_app_ui_datadiscovery_showdetails',
        'pelagos_app_ui_mdapp_downloadmetadatafromdb',
        'pelagos_app_ui_mdapp_getlog',
        'pelagos_app_download_default',
        'pelagos_app_download_http',
        'pelagos_app_download_gridftp',
        'pelagos_app_gml_towkt',
        'pelagos_app_gml_fromwkt',
        'pelagos_metadata',
        'pelagos_app_ui_datasetrestrictions_post',
        'pelagos_app_gml_validategeometryfromwkt',
    )
)) {
    $response->send();
    $kernel->terminate($request, $response);
    exit();
}

// Make drupal return the response code we specify.
drupal_add_http_header('status', $response->getStatusCode());

if (preg_match('/^Pelagos\\\\/', $request->attributes->get('_controller'))
    or $request->attributes->get('_route') === null
    or $request->attributes->get('_route') === '_twig_error_test'
) {
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
