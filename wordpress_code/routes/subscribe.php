<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: subscribe.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-20
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'subscribe.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['POST',      '/subscribe',                                     'mps_tools_create_subscribe'],
        ['POST',      '/subscribe/(?P<event_id>\d+)/(?P<user_id>\d+)/', 'mps_tools_update_subscribe'],
        ['DELETE',    '/subscribe/(?P<user_id>\d+)/(?P<event_id>\d+)',  'mps_tools_delete_subscribe']
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