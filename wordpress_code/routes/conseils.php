<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: conseils.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'conseils.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['POST',    '/conseils',                                    'mps_tools__add_conseils'],
        ['GET',     '/conseils',                                    'mps_tools__get_conseils'],
        ['GET',     '/conseils/(?P<id>\d+)',                        'mps_tools__get_conseils_by_user'],
        ['DELETE',  '/conseils',                                    'mps_tools__delete_conseils'],
        ['DELETE',  '/conseils/(?P<user_id>\d+)/(?P<file_id>\d+)',  'mps_tools__delete_conseils_for_user'],
        ['DELETE',  '/conseils/(?P<id>\d+)',                        'mps_tools__delete_all_by_conseils']
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