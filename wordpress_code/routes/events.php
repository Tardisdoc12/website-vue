<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: events.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'events.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['GET',     '/events',                  'mps_tools_get_events'],
        ['GET',     '/events/(?P<user_id>\d+)', 'mps_tools_get_event_by_user_id'],
        ['GET',     '/events/(?P<id>\d+)',      'mps_tools_get_event_id'],
        ['GET',     '/events/post/(?P<id>\d+)', 'mps_tools_get_event_post_id'],
        ['POST',    '/events',                  'mps_tools_create_events'],
        ['PUT',     '/events/(?P<id>\d+)',      'mps_tools_update_event'],
        ['DELETE',  '/events/(?P<id>\d+)',      'mps_tools_delete_events'],
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