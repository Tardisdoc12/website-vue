<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: favoris.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'favoris.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['POST',    '/favoris',              'mps_tools_add_favoris'],
        ['GET',     '/favoris',              'mps_tools_get_favoris'],
        ['GET',     '/favoris/(?P<id>\d+)',  'mps_tools_get_favoris_by_user'],
        ['DELETE',  '/favoris',              'mps_tools_delete_favoris'],
        ['DELETE',  '/favoris/(?P<id>\d+)',  'mps_tools_delete_all_by_favoris'],
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