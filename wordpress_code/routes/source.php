<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: source.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-20
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'source.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['GET',                       '/subcategories',              'mps_tools_get_subcategories'],
        ['GET',                       '/sources',                    'mps_tools_get_sources'],
        ['GET',                       '/exercices',                  'mps_tools_get_sources_exercice'],
        ['POST',                      '/subcategories',              'mps_tools_add_subcategories'],
        ['POST',                      '/sources',                    'mps_tools_add_sources'],
        ['DELETE',                    '/subcategories/(?P<id>\d+)',  'mps_tools_rm_subcategories'],
        ['DELETE',                    '/sources/(?P<id>\d+)',        'mps_tools_rm_sources'],
        [WP_REST_Server::EDITABLE,    '/sources/(?P<id>\d+)',        'mps_tools_update_sources'],
    ];

    foreach ($routes as [$method, $path, $callback]) {
        register_rest_route('vue-plugin/v1', $path, [
            'methods'             => $method,
            'callback'            => $callback,
            'permission_callback' => 'mps_tools_verify_csrf_and_jwt',
        ]);
    }
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------